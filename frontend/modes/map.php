<?php
/*
	**************************************************************
	* codalyzer
	* -  Single map
	**************************************************************
*/

if(strlen($_GET['m']) > 0){
    if(ereg("^[a-z0-9_]*", $_GET['m'])){
        $sql = "SELECT COUNT('') AS numgames FROM games WHERE map='". $_GET['m'] . "'";
        $row = $db->singleRow($sql);
        if($row['numgames'] > 0){
            $mapname = $_GET['m'];
            echo "<h1>". $lang['h_map'] . ": " . $mapname . "</h1>";  
            
            // THIS QUERY REALLY SHOULD BE REWRITTEN WITHOUT THE TWO SUBQUERIES
            $query = "SELECT profiles.handle, d.d, COUNT('') AS k, (COUNT('')/d.d) as ratio, (SELECT COUNT('') AS g FROM players, games 
                      WHERE games.map='". $mapname ."' AND players.gid=games.id AND players.handle=profiles.handle) AS played, (COUNT('')/(SELECT COUNT('') AS g FROM players, games 
                      WHERE games.map='". $mapname ."' AND players.gid=games.id AND players.handle=profiles.handle)) as kpg FROM kills, games, profiles 
                      JOIN (SELECT profiles.handle, COUNT('') AS d FROM profiles, kills, games WHERE profiles.handle=kills.corpse AND kills.gid=games.id AND games.map='". $mapname ."' 
                      GROUP BY profiles.handle) AS d ON profiles.handle=d.handle WHERE profiles.handle=kills.killer AND kills.gid=games.id AND games.map='". $mapname ."' 
                      AND kills.killer != kills.corpse GROUP BY profiles.handle HAVING played>3";
                      
            $mapstats = new orderedtable($query, 1);               
            $mapstats->setClass('summary');
            $mapstats->setUrl('?mode=map&w=' . $mapname);		 
            $mapstats->setLimit(50);  
            $mapstats->setOrderBy('ratio');
            $mapstats->setColumnData(array('handle' 	=> array (array('handle' => 1), $lang['th_player'], "18%", 0, "?mode=profile&amp;h="),
                                           'k' 	        => array (array('k' => 1, 'd' => 0), $lang['th_kills'], "18%"),
                                           'd' 	        => array (array('d' => 1, 'k' => 0), $lang['th_deaths'], "18%"),
                                           'ratio' 	    => array (array('ratio' => 1, 'k' => 1), $lang['abb_kpd'], "18%"),
                                           'kpg' 	    => array (array('kpg' => 1, 'k' => 1), $lang['abb_kpg'], "18%"),
                                           'played' 	=> array (array('played' => 1), $lang['th_games'], "18%")
                                           ));	
                            
            $mapstats->printTable();                      
        }
    }
}

?>