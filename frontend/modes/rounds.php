<?php
/*
	**************************************************************
	* codalyzer
	* -  Player table
	**************************************************************
*/	
	echo '<h1>' . $lang['h_rounds'] .'</h1>';
	$query 	= "SELECT id, map, type, ROUND((stop-start)/60) as duration, (SELECT count(distinct handle) FROM players WHERE players.gid = games.id) as players FROM games";
	$rounds = new orderedtable($query, 1);
    
	$rounds->setClass('summary');
	$rounds->setUrl('?mode=rounds');		 
	$rounds->setLimit(50); 
    $rounds->setOrderBy('id'); 
    $rounds->setOrder('DESC'); 

	$rounds->setColumnData(array('id' 			=> array (array('id' => 0), $lang['th_id'], "5%", 0, "?mode=single&amp;gid="),
								 'map' 			=> array (array('map' => 1), $lang['th_map'], "20%"),
								 'type' 		=> array (array('type' => 1), $lang['th_mode'], "20%"),
								 'duration' 	=> array (array('duration' => 1), $lang['th_duration'] . ' ' . $lang['m_minutes'], "20%"),	
								 'players' 		=> array (array('players' => 1), $lang['th_players'], "20%")
								));		
				
	$totalsql = "select count('') as c from games WHERE (SELECT count('') from kills where gid=games.id) >= 5";
	$totalrow = $db->singleRow($totalsql);
	$rounds->setTotalRows($totalrow['c']);
						
	$rounds->printTable();
	echo $rounds->pageSelector();	
?>