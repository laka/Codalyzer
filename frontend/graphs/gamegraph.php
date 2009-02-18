<?php
/*
	**************************************************************
	* codalyzer
	* -   Gamegraph
	**************************************************************
*/

include '../classes/config.php';
$config = new config('../../config.ini');

include '../inc/header.php';
include '../classes/graph.php';

header ('Content-type: image/png');

$colors = array('f0c04c', 'e4e4e4', '6077c3', 'da6060', '9cd69a', '88b822', 'ff00c0', 'ff0000', '22ffee');

if($_GET['gid']){
	if(is_numeric($_GET['gid'])){
		$sql = "SELECT id, start, stop FROM games where id='{$_GET['gid']}'";
		$gamedata = $db->singleRow($sql);		
	}
	if(count($gamedata['id']) == 1){
		$gid = $gamedata['id'];
		
		// decides when the game ended
		$start = $gamedata['start'];
		if($gamedata['stop'] == '00:00') {
			$sql = "SELECT ts FROM kills WHERE gid='$gid ' ORDER BY gid DESC LIMIT 1";
			$lastts = $db->singleRow($sql);
			$stop = $lastts['ts'];
		} else {
			$stop = $gamedata['stop'];
		}
		// looks up the 10 best players from this game
		$sql = "SELECT killer FROM kills WHERE gid = '$gid' AND killer != '' AND killer != corpse GROUP BY killer ORDER BY count('') DESC LIMIT 10";
		$res = $db->sqlResult($sql);
		
		$i = 0;
		// loops through the player list
		while($line = mysql_fetch_assoc($res)){
			$graphdata[$i][0] = $line['killer'];
			$graphdata[$i][1] = $colors[$i];
			$sql = "SELECT ts FROM kills WHERE killer =  '" . $db->sqlQuote($line['killer']) . "' AND gid='$gid' AND k_team != c_team ORDER BY ts asc";
			$playerres = $db->sqlResult($sql);
			$a = 0;
			// makes start point for the graphs (in the origin)
			$graphdata[$i][2][] = 0;
			$graphdata[$i][2][] = 0;
			$prevy = 0;	
			while($kill = mysql_fetch_assoc($playerres)){
				$a++;		
				// the next two lines are to prevent the points from being connected
				$graphdata[$i][2][] = $kill['ts'] - $start;				
				$graphdata[$i][2][] = $prevy;				
				
				// the x-value is the time of the kill
				$graphdata[$i][2][] = $kill['ts'] - $start;
				// the y-value is the number of kills at that time
				$graphdata[$i][2][] = $a;
				$prevy = $a;
			}
			$graphdata[$i][2][] = $stop-$start;
			$graphdata[$i][2][] = $a;
			$i++;
			$kills[] = $a;
		}
		
	$yvalues = max($kills)+1;
	if(max($kills) > 20){
		$yvalues = 20;
	}
	$t = new graph($graphdata, 790, 300+30*count($graphdata));
	$t->valuesx = 0;
	$t->simplify = 0;
	$t->numvaluesy = $yvalues;
	$t->squarebkg = '313738';			
	$t->bkg = '212526';			
	$t->decimalsvaluesy = 0;
	$t->valuesywidth = 25;
	$t->createGraph();

			
	}
	else {
		echo "<h1>Error</h1><p>The game was not found</p>";
	}
} else {
	echo "<h1>Error</h1><p>No game specified</p>";
}

?>
