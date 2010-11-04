<?php
if(@$login->isAuthorized()){

    echo "<h2>Welcome to codalyzer Admin</h2>";

    $newgames_sql = "SELECT '' FROM games WHERE confirmed=0";
    if(mysql_num_rows($db->sqlResult($newgames_sql)) > 0){
        echo "<h3>Unconfirmed matches</h3>";

        $games_sql = "SELECT games.id, games.timefetched, games.type, games.map, games.flag, SUM(rounds.kills) as kills  FROM games,rounds WHERE games.confirmed=0 AND rounds.gid=games.id GROUP BY rounds.gid";
        $games_res = $db->sqlResult($games_sql);

        echo '<form action="index.php" method="post">';
        echo '<table width="100%" class="datatable">';
        echo "<tr><th>ID</th><th>Time fetched</th><th>Game type</th><th>Map</th><th>Total kills</th><th>Edit</th><th>Edit match data</th><th>Confirm game</th><th>Drop game</th><th>Flag</th></tr>";

        while($games_row = mysql_fetch_assoc($games_res)){
			if(empty($games_row['flag'])) { $games_row['flag'] = 'OK'; }

            echo "<tr><td>{$games_row['id']}</td><td>{$games_row['timefetched']}</td><td>{$games_row['type']}</td><td>{$games_row['map']}</td><td>{$games_row['kills']}</td><td>";
            echo "<a href=\"?p=editmatchdata&amp;gid={$games_row['id']}\">Edit</a>";
            echo "</td><td><a href=\"?p=editmatchinfo&amp;gid={$games_row['id']}\">Edit</a></td><td><input type=\"radio\" value=\"0\" name=\"drop[{$games_row['id']}]\" checked></td>";
			echo "<td><input type=\"radio\" value=\"1\" name=\"drop[{$games_row['id']}]\"></td>\n";
			echo "<td align=\"center\" class=\"{$games_row['flag']}\">{$games_row['flag']}</td></tr>\n";
        }
        echo "</table>";
        echo '<br><input type="submit" value="Update database!" name="run">';
		echo '<input type="submit" value="Download and sync latest logfile" name="download">';
		echo '</form>';
    } else {
        echo "<h3>No unconfirmed matches in the database</h3>";
		echo '<form action="index.php" method="post">';
		echo '<input type="submit" value="Download and sync latest logfile" name="download">';
		echo '</form>';
    }

	require_once('parser/2.0/core-withoutdb.php');
	// Parser interaction 
	if(isset($_POST['run'])) {	
		$games = new games();
		$looop = new loopWrap();

		$num_sql = "SELECT id FROM games";
		$num_result = mysql_query($num_sql) or die("Something went wrong when the rounds table was fetched.");	
		
		while($num_row = mysql_fetch_assoc($num_result)){
        	if($_POST['drop'][$num_row['id']] == 0){
				$SQL = "UPDATE games SET confirmed='1' WHERE id = '{$num_row['id']}'";
            	mysql_query($SQL) or die("Something went wrong when round ".$num_row['id']." was updated");
        	}
			elseif($_POST['drop'][$num_row['id']] == 1){
				$games->dropGame($num_row['id']);
			}
    	}
		$games->dropRounds();
		$looop->games();
		$looop->players();
		mysql_query("TRUNCATE TABLE rounds");
		header('Location: /jussimik/codalyzer/admin/ ');
	} 
	elseif(isset($_POST['download'])) {
		#$log = new logfile();
		#$log->fetchLog();
	}
}

?>

