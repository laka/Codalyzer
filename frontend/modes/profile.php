<?php
/*
	**************************************************************
	* codalyzer
	* -  Player profile
	**************************************************************
*/	

if(strlen($_GET['h']) > 0){
    // gets data from the profiles, players and streak tables
    $handle = $db->sqlQuote($_GET['h']);
    $sql = "SELECT *, ROUND((kills/(deaths+1)),2) as kpd,
            (SELECT elo FROM players WHERE handle = '$handle' ORDER BY gid DESC LIMIT 1) AS elo, 
            (SELECT elo FROM players WHERE handle = '$handle' ORDER BY gid DESC LIMIT 1,1) AS prevelo,
            (SELECT streak FROM streaks WHERE handle = '$handle' AND type='kill' ORDER BY streak DESC LIMIT 1) as killstreak,
            (SELECT streak FROM streaks WHERE handle = '$handle' AND type='death' ORDER BY streak DESC LIMIT 1) as deathstreak
            FROM profiles where handle='$handle' LIMIT 1";
    $data = $db->singleRow ($sql);

    // this functionality is almost the same as the compare-function in orderedtable
	$diff = $data['elo'] - $data['prevelo'];
	if($diff > 0)
		$change = 'up';
	elseif($diff < 0)
		$change = 'down';
	elseif(($diff == $data['elo']) || ($diff == 0))
		$change = 'statusquo';	    
    
    // prints out main data...
    if(count($data) > 0 && class_exists(orderedtable)){
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
        // THE ONLY DIFFERENCE BETWEEN THESE TWO IS THE ORDER..
        echo "<table width=\"100%\">\n<tr>\n<td valign=\"top\" width=\"50%\">";
        echo "<h2>" . $lang['tt_easiestpreys'] . "</h2>";
            $query = "SELECT corpse, count('')/(SELECT count('') FROM kills WHERE corpse = k.killer AND killer = k.corpse) AS ratio, 
                      count('') AS k, (SELECT count( '' ) FROM kills WHERE corpse = k.killer AND killer = k.corpse) AS d,
                      round((count('')*100/{$data['kills']}),2) as percentage
                      FROM kills AS k WHERE killer = '$handle' GROUP BY corpse HAVING k>10";
            $easiestprey = new orderedtable($query, 1);
            $easiestprey->setUrl("?mode=profile&h=".urlencode($handle));
            $easiestprey->setUrlVars(array('mode', 'h'));
            $easiestprey->setOrderBy('ratio');
            $easiestprey->setOrder('DESC');
            $easiestprey->setLimit('15');
            $easiestprey->setWidth('100%');
            $easiestprey->setClass('summary');
            $easiestprey->setColumndata(array('corpse' => array (array('corpse' => 1), $lang['th_player'], "40%", 0, "?mode=profile&h="),
                                            'ratio' => array (array('ratio' => 1, 'k' => 1), $lang['abb_kpd'], "15%"),
                                            'k' => array (array('k' => 1, 'd' => 0), $lang['abb_kills'], "15%"),
                                            'd' => array (array('d' => 1, 'k' => 0), $lang['abb_deaths'], "15%"),
                                            'percentage' => array (array('percentage' => 1, 'k' => 1), $lang['abb_percentage'], "15%")                                                
                                       ));		
            $easiestprey->printTable();	
        echo "</td>\n<td valign=\"top\" width=\"50%\">\n";
        echo "<h2>" . $lang['tt_worstenemies'] . "</h2>";
            $query = "SELECT corpse, count('')/(SELECT count('') FROM kills WHERE corpse = k.killer AND killer = k.corpse) AS ratio, 
                      count('') AS k, (SELECT count( '' ) FROM kills WHERE corpse = k.killer AND killer = k.corpse) AS d,
                      round((count('')*100/{$data['kills']}),2) as percentage
                      FROM kills AS k WHERE killer = '$handle' GROUP BY corpse HAVING k>10";

            $worstenemy = new orderedtable($query, 1);
            $worstenemy->setUrl("?mode=profile&h=".urlencode($handle));
            $worstenemy->setUrlVars(array('mode', 'h'));
            $worstenemy->setOrderBy('ratio');
            $worstenemy->setOrder('ASC');
            $worstenemy->setLimit('15');
            $worstenemy->setWidth('100%');
            $worstenemy->setClass('summary');
            $worstenemy->setColumndata(array('corpse' => array (array('corpse' => 1), $lang['th_player'], "40%", 0, "?mode=profile&h="),
                                            'ratio' => array (array('ratio' => 1, 'k' => 1), $lang['abb_kpd'], "15%"),
                                            'k' => array (array('k' => 1, 'd' => 0), $lang['abb_kills'], "15%"),
                                            'd' => array (array('d' => 1, 'k' => 0), $lang['abb_deaths'], "15%"),
                                            'percentage' => array (array('percentage' => 1, 'k' => 1), $lang['abb_percentage'], "15%")                                                
                                       ));		
            $worstenemy->printTable();       
        echo "</tr>\n</table>";
        
        // FAVORITE WEAPON - MOST COMMON DEATH
        echo "<table width=\"100%\">\n<tr>\n<td valign=\"top\" width=\"50%\">";
        echo "<h2>" . $lang['tt_favoriteweapons'] . "</h2>";
            $query = "SELECT weapon, CONCAT_WS(' with ', (SELECT full FROM weapons WHERE name=t.weapon LIMIT 1), (SELECT attachments FROM weapons WHERE name=t.weapon LIMIT 1)) AS weaponfull, 
                      count('') AS k, round(count('')*100/{$data['kills']},2) as percentage FROM kills as t WHERE killer='$handle' AND corpse != '$handle' GROUP BY weapon";
            $favoriteweapon = new orderedtable($query, 1);
            $favoriteweapon->setUrl("?mode=profile&h=".urlencode($handle));
            $favoriteweapon->setUrlVars(array('mode', 'h'));
            $favoriteweapon->setOrderBy('k');
            $favoriteweapon->setOrder('DESC');
            $favoriteweapon->setLimit('15');
            $favoriteweapon->setWidth('100%');
            $favoriteweapon->setClass('summary');
            $favoriteweapon->setColumndata(array(
                                            'weaponfull' => array(array('weaponfull'=>1), $lang['th_weapon'], '40%', 0, "?mode=weapons&w=*weapon*"),
                                            'k' => array(array('k'=>1), $lang['th_kills'], '30%'),
                                            'percentage' => array(array('percentage'=>1), $lang['abb_percentage'], '30%'),
                                           ));
            $favoriteweapon->printTable();	
        echo "</td>\n<td valign=\"top\">\n";  
        echo "<h2>" . $lang['tt_frequentdeaths'] . "</h2>";
            $query = "SELECT weapon, CONCAT_WS(' with ', (SELECT full FROM weapons WHERE name=t.weapon LIMIT 1), (SELECT attachments FROM weapons WHERE name=t.weapon LIMIT 1)) AS weaponfull, 
                      count('') AS d, round(count('')*100/{$data['deaths']},2) as percentage FROM kills as t WHERE corpse='$handle' AND killer != '$handle' GROUP BY weapon";
            $frequentdeath = new orderedtable($query, 1);
            $frequentdeath->setUrl("?mode=profile&h=".urlencode($handle));
            $frequentdeath->setUrlVars(array('mode', 'h'));
            $frequentdeath->setOrderBy('d');
            $frequentdeath->setOrder('DESC');
            $frequentdeath->setLimit('15');
            $frequentdeath->setWidth('100%');
            $frequentdeath->setClass('summary');
            $frequentdeath->setColumndata(array(
                                            'weaponfull' => array(array('weaponfull'=>1), $lang['th_weapon'], '40%', 0, "?mode=weapons&w=*weapon*"),
                                            'd' => array(array('d'=>1), $lang['th_deaths'], '30%'),
                                            'percentage' => array(array('percentage'=>1), $lang['abb_percentage'], '30%'),
                                           ));
            $frequentdeath->printTable();	
        echo "</tr>\n</table>";        
        
        // RANDOM CHAT MESSAGES - LAST GAMES
        echo "<table width=\"100%\">\n<tr>\n<td valign=\"top\" width=\"50%\">";
        echo "<h2>" . $lang['tt_randomquotes'] . "</h2>";
            $query = "SELECT quote, gid FROM quotes WHERE (handle='$handle' AND length(quote)>5)";
            $randomchat = new orderedtable($query, 1);
            $randomchat->setUrl("?mode=profile&h=".urlencode($handle));
            $randomchat->setUrlVars(array('mode', 'h'));
            $randomchat->setOrderBy('RAND()');
            $randomchat->setLimit('10');
            $randomchat->setWidth('100%');
            $randomchat->setClass('summary');
            $randomchat->setColumnData(array(
                                        'quote' => array(array('quote'=>1), $lang['th_quote'], "70%"),
                                        'gid' =>   array(array('gid'=>1), $lang['th_gid'], "30%", 0, "?mode=single&amp;gid=")
                                        ));

            $randomchat->printTable();	
        echo "</td>\n<td valign=\"top\">\n";  
        echo "<h2>" . $lang['tt_lastgames'] . "</h2>";
            $query = "SELECT gid, map, type FROM players, games WHERE handle='$handle' AND players.gid=games.id";
            $lastgames = new orderedtable($query, 1);
            $lastgames->setUrl("?mode=profile&h=".urlencode($handle));
            $lastgames->setUrlVars(array('mode', 'h'));
            $lastgames->setOrderBy('gid');
            $lastgames->setOrder('DESC');
            $lastgames->setLimit('10');
            $lastgames->setWidth('100%');
            $lastgames->setClass('summary');
            $lastgames->setColumnData(array(
                                        'map' => array(array('map'=>1), $lang['th_map'], "50%"),
                                        'type' => array(array('type'=>1), $lang['th_mode'], "25%"),
                                        'gid' =>   array(array('gid'=>1), $lang['th_gid'], "25%", 0, "?mode=single&amp;gid=")
                                        ));

            $lastgames->printTable();	
        echo "</tr>\n</table>";             
        
        
    } else {
        echo "<strong>Error: </strong>Could not get player data.";
    }
}
?>