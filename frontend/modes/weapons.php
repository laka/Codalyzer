<?php
/*
	**************************************************************
	* codalyzer
	* -  Weapon table
	**************************************************************
*/

    // WE PROBABLY NEED SOME DISTINCTION  BETWEEN THE DIFFERENT VERSIONS HERE
    // AND -- IT DOES ONLY LIST WEAPONS WITH RELATED KILLS (NOT PRI TO FIX)

	echo '<h1>'. $lang['h_weapons'] .'</h1>';
    
    $query = "SELECT full, mother, SUM(killz) AS totalkills, category, id, version FROM ( 
              SELECT weapons.id, weapons.full, weapons.category, weapons.mother, weapons.version, COUNT('') AS killz
              FROM kills, weapons, games WHERE weapons.name = kills.weapon AND games.id = kills.gid AND games.version = weapons.version
              GROUP BY weapons.name, weapons.version ORDER BY full, attachments ASC) AS allvariants GROUP BY mother";
    
	$weapons = new orderedtable($query, 1);
	$weapons->setClass('summary');
	$weapons->setUrl('?mode=weapons');    
    
	$weapons->setLimit(50); 	
    $weapons->setOrderBy('category'); 	
    $weapons->setOrder('ASC'); 	
	$weapons->setColumnData(array('full' 	=> array (array('full' => 1), $lang['th_weapon'], "30%", 0, URL_BASE . "mode=weapon&w=*mother*"),
								  'category' => array (array('category' => 1, 'full' => 1), $lang['th_weapontype'], "30%"),			
                                  'totalkills' => array (array('totalkills' => 1, 'full' => 1), $lang['th_kills'], "30%"),	
								));		

	$totalsql = "SELECT COUNT('') AS c FROM weapons WHERE mother IS NULL";
	$totalrow = $db->singleRow($totalsql);
	$weapons->setTotalRows($totalrow['c']);
    
	$weapons->printTable();
	echo $weapons->pageSelector();	    
?>