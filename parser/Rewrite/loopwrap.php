<?php
require_once('toolbox.php');

class loopwrap extends toolbox {
	private $db;
	public function __construct() {
		$this->db = database::getInstance();
	}
	public function gamesLoop() {
		$result = database::getInstance()->sqlResult(
			"SELECT id FROM games AS gid WHERE parsed != 1 ORDER BY id ASC");
		
		while($row = mysql_fetch_assoc($result)) {
		
		}
	}
	public function playersLoop() {
		$result = database::getInstance()->sqlResult(
			"SELECT DISTINCT hash AS puid ORDER BY id ASC");
		
		while($row = mysql_fetch_assoc($result)) {
	
		}
	}
}

?>