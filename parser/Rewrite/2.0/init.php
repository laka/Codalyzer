<?php

class init {
	public function __construct() {

	}

	public function preParse() {

	}

	public function codalyze() {

	}

	public function postParse() {

	}
} 

$start = microtime(true);
require('core.php');

$parser = new parser('xxx', 'all');

$end = microtime(true);
$total_time = round($end-$start, 4);

echo "-- generated in $total_time secs \o/\n";
?>
