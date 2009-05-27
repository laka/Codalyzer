<?php
/*
	**************************************************************
	* codalyzer
	* -  Last games table
	**************************************************************
*/
if(is_object($db) && class_exists(orderedtable)){
	$query 	= "SELECT id, map, type, (SELECT COUNT(DISTINCT handle) FROM players WHERE players.gid =  games.id) AS t, stop, start FROM games 
               WHERE (SELECT COUNT('') from kills WHERE gid=games.id) >= 5 ORDER BY id DESC";
	$tenlast = new orderedtable($query);
	$tenlast->setWidth('100%');
	$tenlast->setClass( CLASS_LAST_GAMES );
	$tenlast->setLimit( NUM_LAST_GAMES ); 
	$tenlast->setColumndata(	array('map' 	=> array (array('map' => 1), $lang['th_map'], "40%", 0, URL_BASE ."mode=map&amp;m="),
								 'type' 		=> array (array('type' => 1), $lang['th_mode'], "20%"),
								 't' 			=> array (array('t' => 1), $lang['th_players'], "20%"),
								 'id' 			=> array (array('id' => 1), $lang['th_id'], "20%", 0, URL_BASE ."mode=single&amp;gid=")	
								));			
	$tenlast->printTable();
}	
?>	