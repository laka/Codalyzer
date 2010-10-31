<?php
require_once('toolbox.php');

class games extends toolbox {
	public function addNewRound($matches, $gid) {
		$matches = $this->chomp($matches);
		$round   = $this->lastRound($gid);
		$last    = $this->lastGameData($gid);
		$ts      = $this->inSeconds($matches[1]);
		$type    = $matches[2];
		$map     = $matches[3];

		if(is_numeric($gid)) {
			if($last['type'] != 'dm') {
				if($last['type'] == $type && $last['map'] == $map && $last['stop'] == 0) {
					database::getInstance()->sqlResult("UPDATE games SET stop=0 WHERE gid=\"$gid\"");
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
	
		$gid = $this->lastGid();
		database::getInstance()->sqlResult("
			INSERT INTO rounds (gid, ts, round)
			VALUES(\"$gid\", \"$ts\", \"$round\")");
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

	}

	public function lastRound($gid) {
		$row = database::getInstance()->singleRow(
		"SELECT round FROM rounds WHERE gid=\"$gid\" ORDER BY id DESC LIMIT 1");
	
		return ++$row['round'];
	}
}

?>
