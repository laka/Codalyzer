<?php
/*
	**************************************************************
	* codalyzer
	* -   Gamegraph
	**************************************************************
*/

include '../../config.php';
include '../classes/graph.php';
include '../inc/header.php';

//header ('Content-type: image/png');

$colors = array('f0c04c', 'e4e4e4', '6077c3', 'da6060', '9cd69a', '88b822', 'ff00c0', 'ff0000', '22ffee');

if($_GET['gid']){
	if(is_numeric($_GET['gid'])){
		$sql = "SELECT id, start, stop, type FROM games where id='{$_GET['gid']}'";
		$gamedata = $db->singleRow($sql);		
	}
	if(count($gamedata['id']) == 1){
		$gid = $gamedata['id'];
		
		// decides when the game ended
		$start = $gamedata['start'];
		if($gamedata['stop'] == '00:00') {
			$sql = "SELECT ts FROM kills WHERE gid='$gid' ORDER BY gid DESC LIMIT 1";
			$lastts = $db->singleRow($sql);
			$stop = $lastts['ts'];
		} else {
			$stop = $gamedata['stop'];
		}
        
        // this block of code is shared with resultlist.php
        switch($gamedata['type']){
			case 'dm':
				$teams 	    = 0;
				$actions	= 0;
			break;
			case 'tdm':
				$teams 	    = 1;
				$actions	= 0;
			break;	
			default:
				$teams 	    = 1;
				$actions	= 1;
		}
     
        switch($gametype['mods']){
            case 'pam40':
            case 'promod':
                $teamsql = "AND k_team != c_team";
            break;
            default:
                "";
        }     

		// looks up the 10 best players from this game
        $sql = "SELECT killerID, handle FROM profiles, kills WHERE gid = '$gid' AND killerID != '' AND killerID != corpseID AND killerID = profiles.id
                GROUP BY killerID ORDER BY COUNT('') DESC LIMIT 10";

		$res = $db->sqlResult($sql);
		
		$i = 0;
		// loops through the player list
		while($line = mysql_fetch_assoc($res)){
			$graphdata[$i][0] = $line['handle'];
			$graphdata[$i][1] = $colors[$i];

            $sql = "SELECT ts FROM kills WHERE killerID =  '" . $db->sqlQuote($line['killerID']) . "' AND gid='$gid' AND killerID != corpseID $teamsql ORDER BY ts ASC";                        

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
    //print_r($graphdata);
	$t = new graph($graphdata, GAMEGRAPH_WIDTH, 300+30*count($graphdata));
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
