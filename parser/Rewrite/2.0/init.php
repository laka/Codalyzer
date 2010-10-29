<?php
$start = microtime(true);
require('core.php');

$parser = new parser('games_mp.log', 'all');

$end = microtime(true);
$total_time = round($end-$start, 4);

echo "-- generated in $total_time secs \o/\n";
?>
