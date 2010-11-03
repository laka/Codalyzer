<?php

if(@$login->isAuthorized()){
    echo "<h2>Parser control</h2>";
?> 

<form action="?p=parser" method="post">
  <input type="submit" value="Preparse" name="preparse">
  <input type="submit" value="Truncate tables" name="truncate">
</form>

<?php

if(isset($_POST['preparse'])) {
	$start = microtime(true);

	require_once('parser/2.0/core-withoutdb.php');
	$p = new parser('parser/2.0/games_mp.log', 'all');

	$end = microtime(true);
	$total_time = round($end-$start, 4);
	mysql_query("INSERT INTO runtimes (runtime, date) VALUES(\"$total_time\", NOW())");
	

} elseif(isset($_POST['truncate'])) {
	$start = microtime(true);

	mysql_query("TRUNCATE TABLE games");
	mysql_query("TRUNCATE TABLE rounds");
	mysql_query("TRUNCATE TABLE players");
	mysql_query("TRUNCATE TABLE kills");
	mysql_query("TRUNCATE TABLE hits");
	mysql_query("TRUNCATE TABLE profiles");
	mysql_query("TRUNCATE TABLE matchinfo");
    
	$end = microtime(true);
	$total_time = round($end-$start, 4);
}
	
echo "<p>Generated in: $total_time secs\n</p>";

$count = mysql_query("SELECT COUNT(id) AS c FROM runtimes");
$row = mysql_fetch_row($count);
$range = $row[0] - 50;

$result = mysql_query("SELECT id,runtime FROM runtimes ORDER BY id LIMIT $range,$row[0] ");

while($row = mysql_fetch_assoc($result)) {
	$time = $row['runtime'];
	$data .= "$time" . ",";
}

$data = preg_replace('/,$/','',$data);

echo "<img src=\"http://chart.apis.google.com/chart?chxr=0,0,30&chxt=y&chbh=a&chs=500x150&cht=bvg&chco=A2C180,3D7930&chds=0,30&chd=t1:$data&chdl=Seconds&chtt=Runtime+statistics\" width=\"500\" height=\"150\" alt=\"Runtime statistics\" />";

$result = mysql_query("SELECT (SELECT COUNT(id) FROM games) AS games, (SELECT COUNT(id) FROM profiles) AS players, (SELECT COUNT(id) FROM kills) AS kills, (SELECT COUNT(id) FROM hits) AS hits");
$row = mysql_fetch_row($result);

echo "<p><h4>$row[0]</h4> games<h4>$row[1]</h4> players<h4>$row[2]</h4> kills<h4>$row[3]</h4> hits</p>";

} // endif 

?>

