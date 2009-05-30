<?php
/*
	**************************************************************
	* codalyzer
	* -  Displays a single weapon
	**************************************************************
*/ 

if(is_numeric($_GET['w'])){
    $weaponid = $_GET['w'];
    $sql = "SELECT * FROM weapons WHERE id='$weaponid'";
    $row = $db->singleRow($sql);
    if(count($row) > 0){
        // checks if we're dealing with a weapon with an attachment
        if(strlen($row['attachments']) > 0){
            // IF a weapon has attachments, the request can't be from the form
            $att[] = $row['name'];
            $sql = "SELECT * FROM weapons WHERE id='". $row['mother'] ."'";
            $row = $db->singleRow($sql);
        }
        if(count($row) > 0){
            $weaponid = $row['id'];
            echo "<h1>" . $lang['h_weapon'] . ": " . $row['full'] . "</h1>";       

            $attachmentsql = "SELECT attachments, name FROM weapons WHERE mother='" . $row['id'] . "' OR id='". $row['id'] ."' ORDER BY attachments ASC";
            $result = $db->sqlResult($attachmentsql);
            
            if(mysql_num_rows($result) > 0){
                echo '<form action="" method="GET">';
                echo '<input type="hidden" name="mode" value="weapon">';
                echo '<input type="hidden" name="w" value="'. $weaponid  .'">';
                
                 // makes sure we don't lose any variables from the query string
                foreach($_GET as $key => $value){
                    if(ereg('^att_([0-9]*)$', $key) && ereg('^[_a-z0-9]*$', $value)){
                        $att[] = $value;
                    } elseif($key != 'w' && $key != 'mode' && $key != 'update') {
                        echo '<input type="hidden" name="'. $key .'" value="'. $value .'">';                         
                    }
                }          

                echo '<table class="summary" width="100%"><tr><th width="30%" class="verticalth">'. $lang['m_attachments'] .'</th><td>';

                $a = 0;
                $i = 0;
                while($line = mysql_fetch_assoc($result)){
                    // renames NULL to "none"...
                    if(strlen($line['attachments']) == 0){
                        $line['attachments'] = "None";
                    }
                    // if count($att) > 0, we only want some mods of the weapon
                    if(count($att) > 0){
                        if(in_array($line['name'], $att)){
                            $checked = 'checked = "yes"';
                            $weaponsql .= ($a > 0) ? " OR " : '';
                            $weaponsql .= 'weapon="' . $line['name'] . '"'; 
                            $a++;
                        }
                    } else {
                        $checked = 'checked = "yes"';
                        $weaponsql .= ($a > 0) ? " OR " : '';
                        $weaponsql .= ' weapon="' . $line['name'] . '"'; 
                        $a++;
                    }
                    
                    $attatchmentname = isset($lang['at_'.$line['attachments']]) ? $lang['at_'.$line['attachments']] : $line['attachments'];
                    echo '<input type="checkbox" name="att_'. $i .'" value="'. $line['name'] .'" '. $checked .'>' . ucfirst($attatchmentname);
                    $checked = '';
                    $i++;
                }
                
                echo '<td><input type="submit" value="Update" name="update"></td>';
                echo '</td></tr></table></form>';
            }
  
            // WHO HAS THE MOST KILLS WITH THIS WEAPON?                    
            echo "<h2>" . $lang['tt_mostkillswith'] . " " . $row['full'] . "</h2>";
            
            if(!DISTINGUISH_BY_HASH){
                $sql = "SELECT handle, COUNT('') as killcount, (COUNT('')/profiles.kills*100) AS percentage FROM kills, games, profiles
                        WHERE killer != corpse AND kills.gid = games.id AND games.version='". $row['version'] ."' AND ($weaponsql) AND profiles.handle=kills.killer GROUP BY killer";
            } else {
                $sql = "SELECT profiles.id, handle, hash, COUNT('') as killcount, (COUNT('')/profiles.kills*100) AS percentage FROM kills, games, profiles
                        WHERE k_hash != c_hash AND kills.gid = games.id AND games.version='". $row['version'] ."' AND ($weaponsql) AND profiles.hash=k_hash GROUP BY k_hash";
            }

            $mostkillswith = new orderedtable($sql, 1);               
            $mostkillswith->setClass('summary');
            $mostkillswith->setUrl(URL_BASE . 'mode=weapon&w=' . $weaponid);		 
            $mostkillswith->setLimit(20);  
            $mostkillswith->setOrderBy('killcount');
            $mostkillswithcoldata =       array('handle' 		=> array (array('killer' => 1), $lang['th_player'], "30%", 0, URL_BASE . "mode=profile&amp;h="),
                                                'killcount' 	=> array (array('killcount' => 1, 'percentage' => 1), $lang['th_kills'], "30%"),
                                                'percentage' 	=> array (array('percentage' => 1), $lang['th_percentage'], "30%")
                                                );	
            if(DISTINGUISH_BY_HASH){
                $mostkillswithcoldata['handle'][4] .= "*id*";
            }
            $mostkillswith->setColumnData($mostkillswithcoldata);                
            $mostkillswith->printTable();

            // WHO HAS THE MOST DEATHS WITH THIS WEAPON?                    
            echo "<h2>" . $lang['tt_mostdeathsby'] . " " . $row['full'] . "</h2>";
            

            if(!DISTINGUISH_BY_HASH){
                $sql = "SELECT handle, COUNT('') as deathcount, (COUNT('')/profiles.deaths*100) AS percentage FROM kills, games, profiles
                        WHERE killer != corpse AND kills.gid = games.id AND games.version='". $row['version'] ."' AND ($weaponsql) AND profiles.handle=kills.corpse GROUP BY corpse";
            } else {
                $sql = "SELECT profiles.id, handle, hash, COUNT('') as deathcount, (COUNT('')/profiles.deaths*100) AS percentage FROM kills, games, profiles
                        WHERE k_hash != c_hash AND kills.gid = games.id AND games.version='". $row['version'] ."' AND ($weaponsql) AND profiles.hash=c_hash GROUP BY c_hash";
            }

            $mostdeathsby = new orderedtable($sql, 1);               
            $mostdeathsby->setClass('summary');
            $mostdeathsby->setUrl(URL_BASE . 'mode=weapon&w=' . $weaponid);		 
            $mostdeathsby->setLimit(20);  
            $mostdeathsby->setOrderBy('deathcount');
            $mostdeathsbycoldata =        array('handle' 		=> array (array('corpse' => 1), $lang['th_player'], "30%", 0, URL_BASE . "mode=profile&amp;h="),
                                                'deathcount' 	=> array (array('deathcount' => 1, 'percentage' => 1), $lang['th_deaths'], "30%"),
                                                'percentage' 	=> array (array('percentage' => 1), $lang['th_percentage'], "30%")
                                                );	
            if(DISTINGUISH_BY_HASH){
                $mostdeathsbycoldata['handle'][4] .= "*id*";
            }
            $mostdeathsby->setColumnData($mostdeathsbycoldata);                
            $mostdeathsby->printTable();     
        }
    }
}

?>