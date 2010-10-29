<?php
require_once('toolbox.php');

class games extends toolbox {
	public function addNewRound($matches, $gid) {
		$ts = $this->inSeconds($matches[1]);
		$type = $matches[2];
		$map = $matches[3];

		$lastGame = $this->lastGameData($gid);
	
		if(is_numeric($gid)) {
			if($lastGame['type'] != 'dm') {
				if($lastGame['type'] == $type && $lastGame['map'] == $map && $lastGame['stop'] == 0) {
					database::getInstance()->sqlResult("UPDATE games SET stop=0 WHERE gid=\"$gid\"");
					return 0;
				} 
			}
		} else {
			$this->reportError('init', 'unknow gid');
			return 0;
		}
		
		database::getInstance()->sqlResult("
			INSERT INTO games (start, type, map)
			VALUES(\"$ts\", \"$type\", \"$map\")");
	}

	public function lastGameData($gid) {
		$row = database::getInstance()->singleRow(
			"SELECT type, map, stop FROM games WHERE gid=\"$gid\"");

		return $row;
	}

	public function lastGid() {
		$row = database::getInstance()->singleRow(
			"SELECT id FROM games ORDER BY id DESC LIMIT 1");

		return $row['id'];
	}
}

?>
