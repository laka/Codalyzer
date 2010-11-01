<?php
echo "<h1>Add matchinfo</h1>";

$link = mysql_connect('mysql.stud.ntnu.no', 'jussimik', 'codstats123');
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
mysql_select_db('jussimik_codalyzer');

if(is_numeric($_GET['gid'])){
    $gid = mysql_real_escape_string($_GET['gid']);
    
    // fetches already stored matchinfo:
    $matchinfo_sql = "select * from matchinfo where gid='$gid'";
    $matchinfo_res = mysql_query($matchinfo_sql);
    $matchinfo_row = mysql_fetch_assoc($matchinfo_res);
    
    echo '<form action="?p=editmatchinfo" method="post">';
    echo '<input type="hidden" name="gid" value="'.$gid.'">';
    
    echo '<table class="datatable">';
    
    echo '<tr><th>Axis score</th><td>';
    echo '<input type="text" name="axisscore" value="'.$matchinfo_row['axisscore'].'">';
    echo '</td></tr>';
      
    echo '<tr><th>Allies score</th><td>';
    echo '<input type="text" name="alliesscore" value="'.$matchinfo_row['alliesscore'].'">';
    echo '</td></tr>';
    
    echo '<tr><th>Axis clan</th><td>';
    echo '<input type="text" name="axisclan" value="'.$matchinfo_row['axisclan'].'">';
    echo '</td></tr>';
    
    echo '<tr><th>Allies clan</th><td>';
    echo '<input type="text" name="alliesclan" value="'.$matchinfo_row['alliesclan'].'">';
    echo '</td></tr>';
    
    echo '<tr><th>Clanbase Match ID</th><td>';
    echo '<input type="text" name="cbid" value="'.$matchinfo_row['cbid'].'">';
    echo '</td></tr>';
      
    echo '<tr><th>Comment</th><td>';
    echo '<textarea name="note">'.$matchinfo_row['note'].'</textarea>';
    echo '</td></tr>';
    
    echo '</table>';
    
    echo '<br><input type="submit" name="update" value="Update database!">';   
    echo '</form>';
} elseif(isset($_POST['gid']) && isset($_POST['update'])){
    if( (is_numeric($_POST['axisscore']) || $_POST['axisscore'] == '') &&
        (is_numeric($_POST['alliesscore']) || $_POST['alliesscore'] == '') &&
        (strlen($_POST['axisclan']) < 40 ) && 
        (strlen($_POST['alliesclan']) < 40 ) &&
        (is_numeric($_POST['cbid']) || $_POST['cbid'] == '')   
      ){
            $axisscore = mysql_real_escape_string($_POST['axisscore']);
            $alliesscore = mysql_real_escape_string($_POST['alliesscore']);
            $axisclan = mysql_real_escape_string($_POST['axisclan']);
            $alliesclan = mysql_real_escape_string($_POST['alliesclan']);
            $cbid = mysql_real_escape_string($_POST['cbid']);
            $note = mysql_real_escape_string($_POST['note']);
            $gid = mysql_real_escape_string($_POST['gid']);
            
            // checks if this match already has info in the database...
            $hasinfo_sql = "SELECT count('') FROM matchinfo WHERE gid=$gid";
            $hasinfo_result = mysql_query($hasinfo_sql);
            $hasinfo_row = mysql_fetch_row($hasinfo_result);
            
            if($hasinfo_row[0] != 0){
                $updateinfo_sql = "UPDATE matchinfo SET axisscore='$axisscore', alliesscore='$alliesscore', axisclan = '$axisclan', alliesclan = '$alliesclan', cbid = '$cbid', note='$note' WHERE gid=$gid";
            } else {
                $updateinfo_sql = "INSERT INTO matchinfo (gid, axisscore, alliesscore, axisclan, alliesclan, cbid, note) VALUES ('$gid', '$axisscore', '$alliesscore', '$axisclan', '$alliesclan', '$cbid' , '$note')";
            }
            
            mysql_query($updateinfo_sql);
            
            echo "Updated";
            echo '<br><a href="index.php">Unconfirmed matches</a>';
      } else {
        echo "Invalid input";
      }

}

?>