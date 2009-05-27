<?php
/*
	**************************************************************
	* codalyzer
	* -  Best players table
	**************************************************************
*/
if(is_object($db) && class_exists(orderedtable)){	
    $query	= 'SELECT handle, (SELECT elo FROM players WHERE handle = p.handle AND elo IS NOT NULL ORDER BY gid DESC LIMIT 1 ) AS elo FROM profiles AS p ORDER BY elo DESC';
    $tenbest = new orderedtable($query);
    $tenbest->setWidth('100%');
    $tenbest->setClass( CLASS_BEST_PLAYERS );
    $tenbest->setLimit( NUM_BEST_PLAYERS ); 
    $tenbest->setColumndata(array('handle' 	=> array (array('handle' => 1), $lang['th_player'], "70%", 0, URL_BASE ."mode=profile&amp;h="),
                                      'elo' => array (array('elo' => 1, 'deaths' => 0), $lang['th_elo'], "30%")								
                                  ));		
    $tenbest->printTable();
}	
?>	