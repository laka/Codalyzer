<?php
/*
	**************************************************************
	* codalyzer
	* -  Last games table
	**************************************************************
*/
if(is_object($db) && class_exists(orderedtable)){
	$query 	= "SELECT id, map, type, (SELECT count(distinct handle) FROM players WHERE players.gid =  games.id) as t, stop, start FROM games WHERE (SELECT count('') from kills where gid=games.id) >= 5 ORDER BY id desc";
	$tenlast = new orderedtable($query);
	$tenlast->setWidth('100%');
	$tenlast->setClass( CLASS_LAST_GAMES );
	$tenlast->setLimit( NUM_LAST_GAMES ); 
	$tenlast->setColumndata(	array('map' 	=> array (array('map' => 1), $lang['th_map'], "40%", 0, "?mode=map&m="),
								 'type' 		=> array (array('type' => 1), $lang['th_mode'], "20%"),
								 't' 			=> array (array('t' => 1), $lang['th_players'], "20%"),
								 'id' 			=> array (array('id' => 1), $lang['th_id'], "20%", 0, "?mode=single&amp;gid=")	
								));			
	$tenlast->printTable();
}	
?>	