<?php
/*
	**************************************************************
	* codalyzer
	* -  Map table
	**************************************************************
*/

	echo '<h1>'. $lang['h_maps'] .'</h1>';
    
    $query = "SELECT map, COUNT('') AS numgames FROM games GROUP BY map";
    
	$maps = new orderedtable($query, 1);
	$maps->setClass('summary');
	$maps->setUrl(URL_BASE . 'mode=maps');    
    
	$maps->setLimit(50); 	
    $maps->setOrderBy('map'); 	
    $maps->setOrder('ASC'); 	
	$maps->setColumnData(array('map' 	 => array (array('map' => 1), $lang['th_map'], "30%", 0, URL_BASE . "mode=map&m="),		
                              'numgames' => array (array('numgames' => 1, 'map' => 1), $lang['th_games'], "30%"),	
								));		

    $totalsql = "SELECT COUNT(DISTINCT map) AS nummaps FROM games";
	$totalrow = $db->singleRow($totalsql);
	$maps->setTotalRows($totalrow['nummaps']);
    
	$maps->printTable();
	echo $maps->pageSelector();	    
?>