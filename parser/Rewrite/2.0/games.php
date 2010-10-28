<?php

class games {
	public function addNewGame($matches, $gid) {
		$ts = $this->inSeconds($matches[1]);
		$lastGame = lastGameData($gid);
	
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
}

?>
