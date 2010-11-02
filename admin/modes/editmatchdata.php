<?php
echo "<h1>Edit match</h1>";

$link = mysql_connect('mysql.stud.ntnu.no', 'jussimik', 'codstats123');
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
mysql_select_db('jussimik_codalyzer');

if(is_numeric($_GET['gid'])){
    $gid = mysql_real_escape_string($_GET['gid']);
    
    $rounds_sql = "SELECT rounds.confirmed, rounds.id, round, kills, duration FROM games,rounds WHERE games.confirmed=0 AND rounds.gid=games.id AND gid=$gid";
    $rounds_res = mysql_query($rounds_sql);

    echo '<form action="?p=editmatchdata" method="post"><table class="datatable">';
    echo '<input type="hidden" name="gid" value="'.$gid.'">';
   
    // round list
    echo '<h2>Rounds</h2>';
    
    echo '<tr><th>Round #</th><th>Duration</th><th>Kills</th><th>Don\'t parse</th></tr>';
    while($rounds_row = mysql_fetch_assoc($rounds_res)){
        if($rounds_row['confirmed'] == '0'){
            $c = 'checked="checked"';
        }
        echo '<tr>';
        echo "<tr><td>{$rounds_row['round']}</td><td>{$rounds_row['duration']}</td><td>{$rounds_row['kills']}</td><td><input type=\"checkbox\" name=\"delete[{$rounds_row['id']}]\" $c></td></tr>";
        echo '</tr>';
        $c = 0;
    }
    echo "</table>";

    $gametype_sql = "SELECT type FROM games WHERE id=$gid";
    $gametype_res = mysql_query($gametype_sql);
    $gametype_row = mysql_fetch_assoc($gametype_res);
    
    if($gametype_row['type'] != 'dm'){
        // teams
        echo '<h2>Teams</h2>';
        
        echo '<h3>Axis</h3>';
        echo '<table class="datatable">';
        echo '<tr><th>Player</th><th>Switch team</th></tr>';
        $axis_sql = "select id, playerID, handle from players where team='axis' and gid=$gid";
        $axis_res = mysql_query($axis_sql);
        while($axis_row = mysql_fetch_assoc($axis_res)){
            echo "<tr><td>{$axis_row['handle']}</td><td><input type=\"checkbox\" name=\"axis_switch[{$axis_row['id']}]\"></td></tr>";
        }
        echo '</table>';
        
        echo '<h3>Allies</h3>';
        echo '<table class="datatable">';
        echo '<tr><th>Player</th><th>Switch team</th></tr>';
        $allies_sql = "select id, playerID, handle from players where team='allies' and gid=$gid";
        $allies_res = mysql_query($allies_sql);
        while($allies_row = mysql_fetch_assoc($allies_res)){
            echo "<tr><td>{$allies_row['handle']}</td><td><input type=\"checkbox\" name=\"allies_switch[{$allies_row['id']}]\"></td></tr>";
        }
        echo '</table>';
    }

    echo '<br><input type="submit" name="update" value="Update">';

    echo "</form>";
} elseif(isset($_POST['gid']) && isset($_POST['update'])){
    $gid = mysql_real_escape_string($_POST['gid']);
    
    // fetches round ids
    $num_sql = "SELECT id FROM rounds WHERE gid=$gid";
    $num_result = mysql_query($num_sql) or die("Something went wrong when the rounds table was fetched.");

    // loops through the ids and updates the ones that are changed
    while($num_row = mysql_fetch_assoc($num_result)){
        if($_POST['delete'][$num_row['id']]){
            $SQL = "UPDATE rounds SET confirmed='0' WHERE id = '{$num_row['id']}'";
            mysql_query($SQL) or die("Something went wrong when round ".$num_row['id']." was updated");
        } else {
            $SQL = "UPDATE rounds SET confirmed='1' WHERE id = '{$num_row['id']}'";
            mysql_query($SQL) or die("Something went wrong when round ".$num_row['id']." was updated");
        }
    }
    
    if(count($_POST['axis_switch']) > 0){
        foreach($_POST['axis_switch'] as $id => $on){
            $SQL = "UPDATE players SET team='allies' WHERE id = '$id'";
            mysql_query($SQL) or die("Something went wrong when playerID ".$id." was updated");
        }
    }
    if(count($_POST['allies_switch']) > 0){
        foreach($_POST['allies_switch'] as $id => $on){
            $SQL = "UPDATE players SET team='axis' WHERE id = '$id'";
            mysql_query($SQL) or die("Something went wrong when playerID ".$id." was updated");
        }
    }    
    
    echo "Updated";
    echo '<br><a href="index.php">Unconfirmed matches</a>';
} else {
    echo "Invalid request";
}
?>
