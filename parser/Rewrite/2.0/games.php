<?php
require_once('toolbox.php');

class games extends toolbox {
	public function addNewRound($matches, $gid) {
		$matches = $this->chomp($matches);
		$last    = $this->lastGameData($gid);
		$ts      = $this->inSeconds($matches[1]);
		$type    = $matches[2];
		$map     = $matches[3];

		if(is_numeric($gid)) {
			if($last['type'] != 'dm') {
				if($last['type'] == $type && $last['map'] == $map && $last['stop'] == 0) {
					database::getInstance()->sqlResult("UPDATE games SET stop=0 WHERE gid=\"$gid\"");
					$this->trackRounds($gid, $ts);
					return 0;
				} 
			}
		} else {
			$result = database::getInstance()->sqlResult("SELECT id FROM games");
			if(mysql_num_rows($result)) {
				$this->reportError('init', 'unknow gid', $ts);
				return 0;
			} else {

			}
		}

		database::getInstance()->sqlResult("
			INSERT INTO games (start, type, map)
			VALUES(\"$ts\", \"$type\", \"$map\")");

		$this->trackRounds($gid, $ts);
	}

	public function lastGameData($gid) {
		$row = database::getInstance()->singleRow(
			"SELECT type, map, stop FROM games WHERE id=\"$gid\"");

		return $row;
	}

	public function lastGid() {
		$row = database::getInstance()->singleRow(
			"SELECT id FROM games ORDER BY id DESC LIMIT 1");

		return $row['id'];
	}

	public function gameOver($matches, $gid) {
		$stop = $this->inSeconds($matches[1]);
		$round = database::getInstance()->singleRow(
			"SELECT round FROM rounds WHERE gid=\"$gid\" ORDER BY id DESC LIMIT 1");
		$r = $round['round'];

		echo "$gid - $r<br>";
		$row = database::getInstance()->singleRow(
			"SELECT ts FROM rounds WHERE gid=\"$gid\" AND round=\"$round\"");
	


		echo "$stop - $start<br>";
		#$duration = ($stop - $start['ts']);
		
		echo $duration . "<br>";

		database::getInstance()->sqlResult("
			UPDATE rounds SET duration=\"$duration\" WHERE gid=\"$gid\ AND round=\"$round\"");
		
	}

	public function lastRound($gid) {
		$row = database::getInstance()->singleRow(
			"SELECT round FROM rounds WHERE gid=\"$gid\" ORDER BY id DESC LIMIT 1");
	
		return ++$row['round'];
	}

	public function trackRounds($gid, $ts) {
		$gid2 = $this->lastGid();	
		$round   = $this->lastRound($gid2);

		database::getInstance()->sqlResult("
			INSERT INTO rounds (gid, ts, round)
			VALUES(\"$gid2\", \"$ts\", \"$round\")");
	}
}

?>
