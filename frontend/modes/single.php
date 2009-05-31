<?php
/*
	**************************************************************
	* codalyzer
	* -  Prints out the results for a given game
	**************************************************************
*/

	include FRONTEND_PATH . '/classes/resultlist.php';
    echo "<h1>" . $lang['h_gamesummary'] . "</h1>";
        
	// validates game id from url
	if(is_numeric($_GET['gid'])){
		$sql = "SELECT id, (SELECT count('') from kills where gid=games.id) as kills FROM games where id='{$_GET['gid']}' LIMIT 1";	
		$row = $db->singleRow($sql);		
	} else {
        // variable set to TRUE if this is the first page a visistor sees...
		$firstpage = 1;	
	}
	// if either game id is not specified, or the given id does not exist, we try to find one ourselves
	if((!is_numeric($_GET['gid'])) || (count($row) != 2) || ($row['kills'] <= 4)){	
		$sql = "SELECT id FROM games WHERE (SELECT count('') from kills where gid=games.id) >= 5 ORDER BY id DESC LIMIT 1";
		$row = $row = $db->singleRow($sql);
		$gid = $row['id'];
	} else {
		$gid = $_GET['gid'];
	}

    if(is_numeric($gid)){
        //  we try to decide if the game is ongoing
        $sql = "SELECT *, (SELECT COUNT(DISTINCT handle) FROM players WHERE players.gid =  games.id) AS t FROM games WHERE id = '$gid' LIMIT 1";
        $row = $db->singleRow($sql);	
        if($row['stop'] != '00:00') {
            $duration = round((($row['stop']-$row['start'])/60),0) . ' min';
        } else {
            $duration = 'Live!';
            $state = 'active';
        }
        $id = $row['id'];
        
        // PREVIOUS GAME
        $sql = "SELECT id FROM games WHERE id<$gid ORDER BY id DESC LIMIT 1";
        $prev = $db->singleRow($sql);
        if(is_numeric($prev['id'])){
            echo "<a href=\"" .URL_BASE . "mode=single&amp;gid={$prev['id']}\">< Prev</a>";
        }
        
        // NEXT GAME
        $sql = "SELECT id FROM games WHERE id>$gid ORDER BY id ASC LIMIT 1";
        $next = $db->singleRow($sql);
        if(is_numeric($next['id'])){
            echo " <a href=\"" .URL_BASE . "mode=single&amp;gid={$next['id']}\">Next ></a>";
        }
        
        
        echo "<table class=\"summary\" width=\"100%\"><tr><th width=\"25%\">" . $lang['th_map'] . ":</th><th width=\"25%\">" . $lang['th_mode'] . ":</th><th width=\"25%\">" . $lang['th_players'] . ":</th><th width=\"25%\">" . $lang['th_duration'] . "</th></tr>";
        echo "<tr class=\"keynumbers\"><td width=\"25%\"><a href=\"" .URL_BASE . "mode=map&amp;m={$row['map']}\">{$row['map']}</a></td><td width=\"25%\">{$row['type']}</td><td width=\"25%\">{$row['t']}</td><td width=\"25%\">$duration</td></tr></table>";
        
        // prints out resultlists
        $result = new resultlist($gid);
        $result->printResults();
            
        // prints out actions table    
            
        // prints out the development graph
        echo "<h2>" . $lang['tt_development'] . "</h2>";
        echo "<img src=\"". FRONTEND_URL ."graphs/gamegraph.php?gid=$gid\" alt=\"" . $lang['tt_development'] . "\">";		
    } else {
        echo "<p><strong>Error:</strong> No games in the database.</p>";
    }
?>
