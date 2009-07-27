<?php
/*
	**************************************************************
	* codalyzer
	* -  Player profile
	**************************************************************
*/	

if(strlen($_GET['h']) > 0){
    // gets data from the profiles, players and streak tables
    $id = $db->sqlQuote($_GET['h']);
    
    $sql = "SELECT *, ROUND((kills/(deaths+1)),2) as kpd,
        (SELECT elo FROM players WHERE playerID = profiles.id ORDER BY gid DESC LIMIT 1) AS elo, 
        (SELECT elo FROM players WHERE playerID = profiles.id ORDER BY gid DESC LIMIT 1,1) AS prevelo,
        (SELECT streak FROM streaks WHERE playerID = profiles.id AND type='kill' ORDER BY streak DESC LIMIT 1) as killstreak,
        (SELECT streak FROM streaks WHERE playerID = profiles.id AND type='death' ORDER BY streak DESC LIMIT 1) as deathstreak
        FROM profiles WHERE id='$id' LIMIT 1";  

    $data = $db->singleRow ($sql);
    $handle = $db->sqlQuote($data['handle']);
    
    // this functionality is almost the same as the compare-function in orderedtable
	$diff = $data['elo'] - $data['prevelo'];
	if($diff > 0)
		$change = 'up';
	elseif($diff < 0)
		$change = 'down';
	elseif(($diff == $data['elo']) || ($diff == 0))
		$change = 'statusquo';	    
    // prints out main data...
    if(count($data) > 1 && class_exists(orderedtable)){
        echo '<h1>' . $lang['h_profile'] .': '. $data['handle'] .'</h1>';     
        echo '<table class="summary" width="100%">';
        echo '<tr>';
        echo '<th width="12%">'. $lang['th_games'] .'</th>';
        echo '<th width="12%">'. $lang['th_kills'] .'</th>';
        echo '<th width="12%">'. $lang['th_deaths'] .'</th>';
        echo '<th width="12%">'. $lang['th_suicides'] .'</th>';
        echo '<th width="12%">'. $lang['th_killstreak'] .'</th>';
        echo '<th width="12%">'. $lang['th_deathstreak'] .'</th>	';	
        echo '<th width="12%">'. $lang['abb_kpd'] .'</th>';
        echo '<th width="15%">'. $lang['th_elo'] .'</th>';
        echo '</tr>'; 
        echo '<tr class="keynumbers">';
        echo '<td width="12%">'. $data['games'] .'</td>';
        echo '<td width="12%">'. $data['kills'] .'</td>';
        echo '<td width="12%">'. $data['deaths'] .'</td>';
        echo '<td width="12%">'. $data['suicides'] .'</td>';
        echo '<td width="12%">'. $data['killstreak'] .'</td>';
        echo '<td width="12%">'. $data['deathstreak'] .'</td>';		
        echo '<td width="12%">'. $data['kpd'] .'</td>';
        echo '<td width="15%"><img src="img/' . $change. '.gif"> '. $data['elo'] .'</td>';
        echo '</tr>';	
        echo '</table>';        
        
        // table data
        
        // AWARDS SHOULD BE HERE
        
        // EASIEST PREY - WORST ENEMY - UGLY NESTED TABLES, SHOULD MAYBE BE DIVs

        echo "<table width=\"100%\">\n<tr>\n<td valign=\"top\" width=\"50%\">";
        echo "<h2>" . $lang['tt_easiestpreys'] . "</h2>";
	  
			$query = "SELECT k.corpseID, d.handle as corpse, d.deathcount, round(COUNT('')*100/". $data['kills'] .",2) as percentage, (COUNT('')/d.deathcount) AS ratio, COUNT('') AS killcount FROM kills as k 
					  LEFT JOIN (SELECT killerID, profiles.handle, COUNT('') AS deathcount FROM kills LEFT JOIN profiles ON killerID=profiles.id WHERE corpseID=". $data['id'] ." GROUP BY killerID
					  ) as d ON d.killerID=k.corpseID WHERE k.killerID=". $data['id'] ." GROUP BY k.corpseID HAVING percentage > 0.4";

            $easiestprey = new orderedtable($query, 1);
            $easiestprey->setUrl(URL_BASE . "mode=profile&amp;h=".urlencode($id));
            $easiestprey->setUrlVars(array('mode', 'h'));
            $easiestprey->setOrderBy('ratio');
            $easiestprey->setOrder('DESC');
            $easiestprey->setLimit('15');
            $easiestprey->setWidth('100%');
            $easiestprey->setClass('summary');
            $preycoldata =            array('corpse' => array (array('corpse' => 1), $lang['th_player'], "40%", 0, URL_BASE . "mode=profile&amp;h=*corpseID*"),
                                            'ratio' => array (array('ratio' => 1, 'killcount' => 1), $lang['abb_kpd'], "15%"),
                                            'killcount' => array (array('killcount' => 1, 'deathcount' => 0), $lang['abb_kills'], "15%"),
                                            'deathcount' => array (array('deathcount' => 1, 'killcount' => 0), $lang['abb_deaths'], "15%"),
                                            'percentage' => array (array('percentage' => 1, 'killcount' => 1), $lang['abb_percentage'], "15%")                                                
                                       );

            $easiestprey->setColumndata($preycoldata);
            $easiestprey->printTable();	
            
        echo "</td>\n<td valign=\"top\" width=\"50%\">\n";
        echo "<h2>" . $lang['tt_worstenemies'] . "</h2>"; 
			$query = "SELECT killerID, k.handle as killer, COUNT('') AS deathcount, k.killcount, (k.killcount/COUNT('')) as ratio, ROUND(COUNT('')*100/". $data['deaths'] .",2) as percentage FROM kills as d
                      LEFT JOIN (SELECT corpseID, profiles.handle, COUNT('') AS killcount FROM kills LEFT JOIN profiles ON corpseID=profiles.id WHERE killerID='". $data['id'] ."' GROUP BY corpseID
                      ) as k ON d.killerID=k.corpseID WHERE d.corpseID='". $data['id'] ."' GROUP BY killerID HAVING percentage > 0.4";

            $worstenemy = new orderedtable($query, 1);
            $worstenemy->setUrl(URL_BASE . "mode=profile&amp;h=".urlencode($id));
            $worstenemy->setUrlVars(array('mode', 'h'));
            $worstenemy->setOrderBy('ratio');
            $worstenemy->setOrder('ASC');
            $worstenemy->setLimit('15');
            $worstenemy->setWidth('100%');
            $worstenemy->setClass('summary');
            $enemycoldata =           array('killer' => array (array('killer' => 1), $lang['th_player'], "40%", 0, URL_BASE . "mode=profile&amp;h=*killerID*"),
                                            'ratio' => array (array('ratio' => 1, 'killcount' => 1), $lang['abb_kpd'], "15%"),
                                            'killcount' => array (array('killcount' => 1, 'deathcount' => 0), $lang['abb_kills'], "15%"),
                                            'deathcount' => array (array('deathcount' => 1, 'killcount' => 0), $lang['abb_deaths'], "15%"),
                                            'percentage' => array (array('percentage' => 1, 'killcount' => 1), $lang['abb_percentage'], "15%")                                                
                                       );		
            $worstenemy->setColumndata($enemycoldata);                       
            $worstenemy->printTable();       
        echo "</tr>\n</table>";

        // FAVORITE WEAPON - MOST COMMON DEATH
        echo "<table width=\"100%\">\n<tr>\n<td valign=\"top\" width=\"50%\">";
        echo "<h2>" . $lang['tt_favoriteweapons'] . "</h2>";

            $query = "SELECT weapon, attachments, full, CONCAT_WS(' with ', full, attachments) as weaponfull, mother, weapons.id, COUNT('') as k, round(count('')*100/{$data['kills']},2) as percentage
                      FROM weapons, kills, games WHERE kills.weapon=weapons.name AND kills.gid=games.id AND games.version=weapons.version AND killerID='". $data['id'] ."' AND corpseID != '". $data['id'] ."' GROUP BY weapon";            

            $favoriteweapon = new orderedtable($query, 1);
            $favoriteweapon->setUrl(URL_BASE . "mode=profile&amp;h=".urlencode($id));
            $favoriteweapon->setUrlVars(array('mode', 'h'));
            $favoriteweapon->setOrderBy('k');
            $favoriteweapon->setOrder('DESC');
            $favoriteweapon->setLimit('15');
            $favoriteweapon->setWidth('100%');
            $favoriteweapon->setClass('summary');
            $favoriteweapon->setColumndata(array(
                                            'weaponfull' => array(array('weaponfull'=>1), $lang['th_weapon'], '40%', 0, URL_BASE . "mode=weapon&amp;w=*id*"),
                                            'k' => array(array('k'=>1), $lang['th_kills'], '30%'),
                                            'percentage' => array(array('percentage'=>1), $lang['abb_percentage'], '30%'),
                                           ));
            $favoriteweapon->printTable();	
            
        echo "</td>\n<td valign=\"top\">\n";  
        echo "<h2>" . $lang['tt_frequentdeaths'] . "</h2>";

            $query = "SELECT weapon, attachments, full, CONCAT_WS(' with ', full, attachments) as weaponfull, mother, weapons.id, COUNT('') as d, round(count('')*100/{$data['deaths']},2) as percentage
                    FROM weapons, kills WHERE kills.weapon=weapons.name AND corpseID='". $data['id'] ."' AND killerID != '". $data['id'] ."' GROUP BY weapon";            

            $frequentdeath = new orderedtable($query, 1);
            $frequentdeath->setUrl(URL_BASE . "mode=profile&amp;h=".urlencode($id));
            $frequentdeath->setUrlVars(array('mode', 'h'));
            $frequentdeath->setOrderBy('d');
            $frequentdeath->setOrder('DESC');
            $frequentdeath->setLimit('15');
            $frequentdeath->setWidth('100%');
            $frequentdeath->setClass('summary');
            $frequentdeath->setColumndata(array(
                                            'weaponfull' => array(array('weaponfull'=>1), $lang['th_weapon'], '40%', 0, URL_BASE . "mode=weapon&w=*id*"),
                                            'd' => array(array('d'=>1), $lang['th_deaths'], '30%'),
                                            'percentage' => array(array('percentage'=>1), $lang['abb_percentage'], '30%'),
                                           ));
            $frequentdeath->printTable();	
        echo "</tr>\n</table>";        
        
        // RANDOM CHAT MESSAGES - LAST GAMES
        echo "<table width=\"100%\">\n<tr>\n<td valign=\"top\" width=\"50%\">";
        echo "<h2>" . $lang['tt_randomquotes'] . "</h2>";
            
            $query = "SELECT quote, gid FROM quotes WHERE (playerID='". $data['id'] ."' AND length(quote)>5)";
            
            $randomchat = new orderedtable($query, 0);
            $randomchat->setUrl(URL_BASE . "mode=profile&amp;h=".urlencode($id));
            $randomchat->setUrlVars(array('mode', 'h'));
            $randomchat->setOrderBy('RAND()');
            $randomchat->setLimit('10');
            $randomchat->setWidth('100%');
            $randomchat->setClass('summary');
            $randomchat->setColumnData(array(
                                        'quote' => array(array('quote'=>1), $lang['th_quote'], "70%"),
                                        'gid' =>   array(array('gid'=>1), $lang['th_gid'], "30%", 0, URL_BASE . "mode=single&amp;gid=")
                                        ));

            $randomchat->printTable();	
        echo "</td>\n<td valign=\"top\">\n";  
        echo "<h2>" . $lang['tt_lastgames'] . "</h2>";
        
            $query = "SELECT gid, map, type FROM players, games WHERE playerID='". $data['id'] ."' AND players.gid=games.id";

            $lastgames = new orderedtable($query, 0);
            $lastgames->setUrl(URL_BASE . "mode=profile&amp;h=".urlencode($id));
            $lastgames->setUrlVars(array('mode', 'h'));
            $lastgames->setOrderBy('gid');
            $lastgames->setOrder('DESC');
            $lastgames->setLimit('10');
            $lastgames->setWidth('100%');
            $lastgames->setClass('summary');
            $lastgames->setColumnData(array(
                                        'map' => array(array('map'=>1), $lang['th_map'], "50%", 0, URL_BASE . "mode=map&amp;m="),
                                        'type' => array(array('type'=>1), $lang['th_mode'], "25%"),
                                        'gid' =>   array(array('gid'=>1), $lang['th_gid'], "25%", 0, URL_BASE . "mode=single&amp;gid=")
                                        ));

            $lastgames->printTable();	
        echo "</tr>\n</table>";       


        // BRIGHTEST MOMENTS - DARKEST  MOMENTS
        echo "<table width=\"100%\">\n<tr>\n<td valign=\"top\" width=\"50%\">";
        echo "<h2>" . $lang['tt_brightestmom'] . "</h2>";
            mysql_query("SET@prev=1000");
            $query = "SELECT gid, @prev as previous, (elo-@prev) as elochange, @prev:=elo as elo FROM players WHERE playerID = '". $data['id'] ."' AND elo IS NOT NULL";

            $brightest = new orderedtable($query);
            $brightest->setUrl("?mode=profile&amp;h=".urlencode($id));
            $brightest->setUrlVars(array('mode', 'h'));
            $brightest->setOrderBy('elochange');
            $brightest->setOrder('DESC');
            $brightest->setLimit('10');
            $brightest->setWidth('100%');
            $brightest->setClass('summary');
            $brightest->setColumnData(array(
                                        'gid' => array(array('map'=>1), $lang['th_gid'], "50%", 0, URL_BASE . "mode=single&amp;gid="),
                                        'elo' => array(array('elo'=>1), $lang['th_elo'], "25%"),
                                        'elochange' =>   array(array('elochange'=>1), $lang['th_elochange'], "25%", 0, "", '0')
                                        ));

            $brightest->printTable();	
        echo "</td>\n<td valign=\"top\">\n";  
        echo "<h2>" . $lang['tt_darkestmoments'] . "</h2>";
            mysql_query("SET@prev=1000");

            $query = "SELECT gid, @prev as previous, (elo-@prev) as elochange, @prev:=elo as elo FROM players WHERE playerID = '". $data['id'] ."' AND elo IS NOT NULL";

            $darkest = new orderedtable($query);
            $darkest->setUrl(URL_BASE . "mode=profile&amp;h=".urlencode($id));
            $darkest->setUrlVars(array('mode', 'h'));
            $darkest->setOrderBy('elochange');
            $darkest->setOrder('ASC');
            $darkest->setLimit('10');
            $darkest->setWidth('100%');
            $darkest->setClass('summary');
            $darkest->setColumnData(array(
                                        'gid' => array(array('map'=>1), $lang['th_gid'], "50%", 0, URL_BASE . "mode=single&amp;gid="),
                                        'elo' => array(array('elo'=>1), $lang['th_elo'], "25%"),
                                        'elochange' =>   array(array('elochange'=>1), $lang['th_elochange'], "25%", 0, "", '0')
                                        ));

            $darkest->printTable();
        echo "</tr>\n</table>";           
        
        
    } else {
        echo "<strong>Error: </strong>Could not get player data.";
    }
}
?>
