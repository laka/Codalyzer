<?php
/*
	**************************************************************
	* codalyzer
	* - Fetches hits for the hit diagram,
	**************************************************************
*/

include '../../config.php';
include '../classes/graph.php';
include 'header.php';

$uid = $db->sqlQuote($_GET['h']);
$wid = $db->sqlQuote($_GET['w']);

if(is_numeric($uid) && is_numeric($wid)){
    $wksql = " AND weapons.mother='$wid' AND weapons.name=kills.weapon ";
    $whsql = " AND weapons.mother='$wid' AND weapons.name=hits.weapon ";
    if($wid == '0'){
        $wksql = '';
        $whsql = '';
    }

    $sql = "SELECT SUM(num) AS antall, location FROM (SELECT location, COUNT('') AS num FROM kills, weapons, profiles WHERE profiles.id=$uid $wksql 
            GROUP BY location UNION SELECT location, COUNT('') AS num FROM hits, weapons, profiles WHERE profiles.id=$uid AND
            hitmanID=profiles.id $whsql GROUP BY location) AS tabeller GROUP BY location ORDER BY antall DESC";	

    $result = mysql_query($sql);
    while($row = mysql_fetch_assoc($result)){
        $total += $row['antall'];
        $hits .= "{$row['location']},{$row['antall']},";
    }
}

echo "hits='$hits';\n";
echo "total='$total';\n";
?>