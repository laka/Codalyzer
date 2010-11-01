<?php
if(@$login->isAuthorized()){

    echo "<h2>Welcome to codalyzer Admin</h2>";

    $newgames_sql = "SELECT '' FROM games WHERE parsed=0";
    if(mysql_num_rows($db->sqlResult($sql)) > 0){
        echo "<h3>Unconfirmed matches</h3>";

        $games_sql = "SELECT games.id, games.timefetched, games.type, games.map, SUM(rounds.kills) as kills  FROM games,rounds WHERE parsed=0 AND rounds.gid=games.id GROUP BY rounds.gid";
        $games_res = $db->sqlResult($games_sql);

        echo '<form action="index.php">';
        echo '<table width="100%" class="datatable">';
        echo "<tr><th>ID</th><th>Time fetched</th><th>Game type</th><th>Map</th><th>Total kills</th><th>Edit</th><th>Edit match data</th><th>Confirm game</th></tr>";

        while($games_row = mysql_fetch_assoc($games_res)){
            echo "<tr><td>{$games_row['id']}</td><td>{$games_row['timefetched']}</td><td>{$games_row['type']}</td><td>{$games_row['map']}</td><td>{$games_row['kills']}</td><td>";
            echo "<a href=\"?p=editmatchdata&amp;gid={$games_row['id']}\">Edit</a>";
            echo "</td><td><a href=\"?p=editmatchinfo&amp;gid={$games_row['id']}\">Edit</a></td><td><input type=\"checkbox\"></td></tr>\n";
        }
        echo "</table>";

        echo '<br><input type="submit" value="Update database!">';

        echo "</form>";
    }
}


?>