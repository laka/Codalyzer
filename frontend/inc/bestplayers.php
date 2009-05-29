<?php
/*
	**************************************************************
	* codalyzer
	* -  Best players table
	**************************************************************
*/
if(is_object($db) && class_exists(orderedtable)){
    if(!DISTINGUISH_BY_HASH){
        $query	= 'SELECT handle, (SELECT elo FROM players WHERE handle = p.handle AND elo IS NOT NULL ORDER BY gid DESC LIMIT 1 ) AS elo FROM profiles AS p ORDER BY elo DESC';
    } else {
        $query	= 'SELECT hash, handle, (SELECT elo FROM players WHERE hash = p.hash AND elo IS NOT NULL ORDER BY gid DESC LIMIT 1 ) AS elo FROM profiles AS p ORDER BY elo DESC';    
    }
    $tenbest = new orderedtable($query);
    $tenbest->setWidth('100%');
    $tenbest->setClass( CLASS_BEST_PLAYERS );
    $tenbest->setLimit( NUM_BEST_PLAYERS ); 
    $tenbestcoldata =       array('handle' 	=> array (array('handle' => 1), $lang['th_player'], "70%", 0, URL_BASE ."mode=profile&amp;h="),
                                      'elo' => array (array('elo' => 1, 'deaths' => 0), $lang['th_elo'], "30%")								
                                  );		
    if(DISTINGUISH_BY_HASH){
        $tenbestcoldata['handle'][4] .= "*hash*";
    }       
    $tenbest->setColumndata($tenbestcoldata);
    $tenbest->printTable();
}	
?>	