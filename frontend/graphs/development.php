<?php
/*
	**************************************************************
	* codalyzer
	* - Elo development curve
	**************************************************************
*/

include '../../config.php';
include '../classes/graph.php';
include '../inc/header.php';

header ('Content-type: image/png');

// validates the given user id
if($db->isPlayer($_GET['h']) > 0){
    if(!DISTINGUISH_BY_HASH){
        $sql = "SELECT gid, elo FROM players WHERE handle='{$_GET['h']}' AND elo IS NOT NULL ORDER BY gid ASC";
    } else {
        $sql = "SELECT gid, players.elo FROM players, profiles WHERE players.hash=profiles.hash AND profiles.id={$_GET['h']} AND players.elo IS NOT NULL ORDER BY gid ASC";
    }
    $result = $db->sqlResult($sql);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $elod[] = $row['gid'];		
        if($row['elo'] == NULL){
            $row['elo'] = 1000;
        }
        $elod[] = $row['elo'];			
    }

    $data[0] = array($_GET['h'], "ffffff", $elod);
    $t = new graph($data, ELODEV_WIDTH, ELODEV_HEIGHT);

    $t->gridy=1;
    $t->gridx=0;
    $t->simplify = 1;
    if(count($elod) > 800){
        $t->simplify = 1;	
    }
    $t->legend=0;
    $t->valuesy = 1;
    $t->valuesx = 0;
	$t->squarebkg = ELODEV_SQUAREBKG;			
	$t->bkg = ELODEV_BKG;	
    $t->numvaluesy = 5;
    $t->decimalsvaluesy = 1;
    $t->valuesywidth = 40;
    $t->createGraph(); 
}
?>