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
		$stop  = $this->inSeconds($matches[1]);
		$round = $this->currentRound($gid);
		$start = database::getInstance()->singleRow(
			"SELECT ts FROM rounds WHERE gid=\"$gid\" AND round=\"$round\"");

		$duration = $stop - $start['ts'];

		$kills = database::getInstance()->singleRow(
			"SELECT COUNT(id) AS c FROM kills WHERE gid=\"$gid\" AND round=\"$round\"");

		database::getInstance()->sqlResult(
			"UPDATE rounds SET duration=\"$duration\", kills=\"$kills[c]\" WHERE gid=\"$gid\" AND round=\"$round\"");				
	}

	public function nextRound($gid) {
		$row = database::getInstance()->singleRow(
			"SELECT round FROM rounds WHERE gid=\"$gid\" ORDER BY id DESC LIMIT 1");
	
		return ++$row['round'];
	}


	public function currentRound($gid) {
		$row = database::getInstance()->singleRow(
			"SELECT round AS c FROM rounds WHERE gid=\"$gid\" ORDER BY id DESC LIMIT 1");
	
		return $row['c'];
	}


	public function trackRounds($gid, $ts) {
		$gid2 = $this->lastGid();	
		$round   = $this->nextRound($gid2);

		database::getInstance()->sqlResult("
			INSERT INTO rounds (gid, ts, round)
			VALUES(\"$gid2\", \"$ts\", \"$round\")");
	}
}

?>
