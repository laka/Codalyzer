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
            
			$query = "SELECT profiles.id, profiles.handle, d.d, COUNT('') AS k, (COUNT('')/d.d) as ratio, d.gamesplayed, (COUNT('')/gamesplayed) as kpg FROM kills, games, profiles 
					  JOIN (SELECT profiles.id, COUNT('') AS d, numplayedtable.gamesplayed FROM kills, games, profiles JOIN 
					  (SELECT profiles.id, COUNT('') as gamesplayed FROM profiles, players, games WHERE profiles.id=players.playerID AND games.id=players.gid AND 
					  games.map='". $mapname ."' GROUP BY profiles.id) AS numplayedtable ON profiles.id = numplayedtable.id WHERE profiles.id=kills.corpseID AND kills.gid=games.id 
					  AND games.map='". $mapname ."' GROUP BY profiles.id) AS d ON profiles.id=d.id WHERE profiles.id=kills.killerID AND kills.gid=games.id 
					  AND games.map='". $mapname ."' AND kills.killerID != kills.corpseID AND gamesplayed>0 GROUP BY profiles.id";

            $mapstats = new orderedtable($query, 1);               
            $mapstats->setClass('summary');
            $mapstats->setUrl(URL_BASE . 'mode=map&w=' . $mapname);		 
            $mapstats->setLimit(50);  
            $mapstats->setOrderBy('ratio');
            $mapcoldata =            array('handle' 	=> array (array('handle' => 1), $lang['th_player'], "18%", 0, URL_BASE . "mode=profile&amp;h="),
                                           'k' 	        => array (array('k' => 1, 'd' => 0), $lang['th_kills'], "18%"),
                                           'd' 	        => array (array('d' => 1, 'k' => 0), $lang['th_deaths'], "18%"),
                                           'ratio' 	    => array (array('ratio' => 1, 'k' => 1), $lang['abb_kpd'], "18%"),
                                           'kpg' 	    => array (array('kpg' => 1, 'k' => 1), $lang['abb_kpg'], "18%"),
                                           'gamesplayed' 	=> array (array('gamesplayed' => 1), $lang['th_games'], "18%")
                                           );
            if(DISTINGUISH_BY_HASH){                                           
                $mapcoldata['handle'][4] .= "*id*";
            }
            $mapstats->setColumnData($mapcoldata);             
            $mapstats->printTable();                      
        }
    }
}

?>