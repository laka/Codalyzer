<?php
/*
	**************************************************************
	* codalyzer
	* -  Top ten player development graph
	**************************************************************
*/

include '../config.php';
include '../inc/header.php';
include '../classes/graph.php';

header ('Content-type: image/png');

$query = "SELECT handle, elo, id FROM players as p WHERE gid=(SELECT gid FROM players WHERE handle=p.handle ORDER BY gid DESC LIMIT 1) ORDER BY elo DESC LIMIT 5";
$result = mysql_query($query);

$i = 0;
while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$sql = "SELECT gid, elo FROM players WHERE handle='{$line['handle']}' AND elo IS NOT NULL ORDER BY gid ASC";
	$res = mysql_query($sql);
	$elod[$i][0] = $line['handle'];
	while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
		$elod[$i][2][] = $row['gid'];		
		if($row['elo'] == NULL){
			$row['elo'] = 1000;
		}
		$elod[$i][2][] = $row['elo'];			
	}
	$i++;

}

$i = 0;
$colors = array('f0c04c', 'e4e4e4', '6077c3', 'da6060', '9cd69a');
while(count($elod[$i][2]) > 0){
	$elod[$i][1] = $colors[$i];
	$i++;
}

$t = new graph($elod, 250, 300);
$t->simplify = true;
$t->smoothpasses = 5;
$t->decimalsvaluesy = 0; 
$t->valuesx = 0; 
$t->createGraph(); 


?>