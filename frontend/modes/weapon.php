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
            echo "<h1>" . $lang['h_weapon'] . ": " . $row['full'] . "</h1>";       
            
                $attachmentsql = "SELECT attachments, name FROM weapons WHERE mother='" . $row['name'] . "'";
                $result = $db->sqlResult($attachmentsql);
                $weaponsql = 'weapon="' . $row['name'] . '"';
                if(mysql_num_rows($result) > 0){
                    echo '<select multiple="multiple">';
                    echo '<option selected="selected">None</option>';
                    
                    // SOME MECHANISM RETURNS AN ARRAY OF THE SELECTED ELEMENTS...
                    // ... (this is probaby an ajax job)
                    
                    while($line = mysql_fetch_assoc($result)){
                        echo '<option selected="selected" value="' . $line['name'] . '">' . $line['attachments'] . '</option>';
                        $weaponsql .= ' OR weapon="' . $line['name'] . '"';  
                    }
                    echo '</select>';
                }
      
                // WHO HAS THE MOST KILLS WITH THIS WEAPON?                    
            	echo "<h2>" . $lang['tt_mostkillswith'] . " " . $row['full'] . "</h2>";
                
                $sql = "SELECT killer, COUNT('') as killcount, (COUNT('')/(SELECT kills FROM profiles WHERE handle=kills.killer LIMIT 1)*100) AS percentage FROM kills 
                        WHERE killer != corpse AND $weaponsql GROUP BY killer";
                                        
                $mostkillswith = new orderedtable($sql, 1);               
                $mostkillswith->setClass('summary');
                $mostkillswith->setUrl('?mode=weapon&w=' . $weaponid);		 
                $mostkillswith->setLimit(20);  
                $mostkillswith->setOrderBy('killcount');
                $mostkillswith->setColumnData(array('killer' 		=> array (array('killer' => 1), $lang['th_player'], "30%", 0, "?mode=profile&amp;h="),
                                                    'killcount' 	=> array (array('killcount' => 1, 'percentage' => 1), $lang['th_kills'], "30%"),
                                                    'percentage' 	=> array (array('percentage' => 1), $lang['th_percentage'], "30%")
                                                    ));	
                                
                $mostkillswith->printTable();

                // WHO HAS THE MOST DEATHS WITH THIS WEAPON?                    
            	echo "<h2>" . $lang['tt_mostdeathsby'] . " " . $row['full'] . "</h2>";
                
                $sql = "SELECT corpse, COUNT('') as deathcount, (COUNT('')/(SELECT deaths FROM profiles WHERE handle=kills.corpse LIMIT 1)*100) AS percentage FROM kills 
                        WHERE killer != corpse AND $weaponsql GROUP BY corpse";
                                        
                $mostdeathsby = new orderedtable($sql, 1);               
                $mostdeathsby->setClass('summary');
                $mostdeathsby->setUrl('?mode=weapon&w=' . $weaponid);		 
                $mostdeathsby->setLimit(20);  
                $mostdeathsby->setOrderBy('deathcount');
                $mostdeathsby->setColumnData(array('corpse' 		=> array (array('corpse' => 1), $lang['th_player'], "30%", 0, "?mode=profile&amp;h="),
                                                    'deathcount' 	=> array (array('deathcount' => 1, 'percentage' => 1), $lang['th_deaths'], "30%"),
                                                    'percentage' 	=> array (array('percentage' => 1), $lang['th_percentage'], "30%")
                                                    ));	
                                
                $mostdeathsby->printTable();                
        }
    }

?>