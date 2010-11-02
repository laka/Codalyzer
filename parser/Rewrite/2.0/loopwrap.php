<?php

class loopWrap {
	public function __construct() {
		$this->players = new players();
		$this->ratings = new ratings();
	}

	public function games() {
		$result = database::getInstance()->sqlResult(
			"SELECT id FROM games ORDER BY id ASC");
		
		while($row = mysql_fetch_assoc($result)) {
			$this->ratings->matchRank($row[id]);
		}
	}

	public function players() {
		$result = database::getInstance()->sqlResult(
			"SELECT DISTINCT hash AS hash FROM profiles ORDER BY id ASC");
		
		while($row = mysql_fetch_assoc($result)) {
			$this->players->addProfileData($row[hash]);
		}
	}

}

?>
