<?php
/*
	**************************************************************
	* codalyzer
	* -  Player table
	**************************************************************
*/	
	echo '<h1>'. $lang['h_players'] .'</h1>';
	
    $query = "SELECT *, round(((kills-deaths)/games),1) as avgdiff, round(kills/(deaths+1),2) as ratio, round(kills/(games+1),2) as kpg,
             round(((SELECT count('') FROM kills WHERE killerID = p.id AND k_team != c_team AND location = 'head')*100/kills), 2) as hspercentage,
             (SELECT elo FROM players WHERE playerID = p.id  AND elo IS NOT NULL ORDER BY gid DESC LIMIT 1) AS elo,
             (SELECT elo FROM players WHERE playerID = p.id  AND elo IS NOT NULL ORDER BY gid DESC LIMIT 1,1) AS prevelo
             FROM profiles AS p WHERE (deaths > 0 OR kills > 0)";	     

	$players = new orderedtable($query, 1);
	$players->setClass('summary');
	$players->setUrl(URL_BASE . 'mode=players');
	$players->setLimit(50); 	
    $players->setOrderBy('elo'); 	
    $players->setOrder('DESC'); 	
	$columndata =           array('handle' 		=> array (array('handle' => 1), $lang['th_player'], "15%", 0, URL_BASE . "mode=profile&h=*id*"),
								 'kills' 		=> array (array('kills' => 1, 'suicides' => 0, 'deaths' => 0), $lang['th_kills'], "6%"),
								 'deaths' 		=> array (array('deaths' => 1, 'suicides' => 0, 'kills' => 0), $lang['th_deaths'], "6%"),
								 'suicides' 	=> array (array('suicides' => 1), $lang['th_suicides'], "6%"),	
								 'games' 		=> array (array('games' => 1), $lang['th_games'], "6%"),	
								 'ratio' 		=> array (array('ratio' => 1), $lang['abb_kpd'], "6%"),	
								 'hspercentage' => array (array('hspercentage' => 1), $lang['abb_hspercentage'], "6%"),	
								 'kpg' 			=> array (array('kpg' => 1), $lang['abb_kpg'], "6%"),	
								 'avgdiff' 		=> array (array('avgdiff' => 1, 'elo' => 1), $lang['abb_avgdiffr'], "6%"),	
								 'elo' 			=> array (array('elo' => 1, '(elo-prevelo)' => 1), $lang['th_elo'], "6%", '', '', 'prevelo'),									 
								);		
    
    $players->setColumnData($columndata);
	$totalsql = "SELECT COUNT('') AS c FROM profiles WHERE (deaths > 0 OR kills > 0)";
	$totalrow = $db->singleRow($totalsql);
	$players->setTotalRows($totalrow['c']);
						
	$players->printTable();
	echo $players->pageSelector();
?>

