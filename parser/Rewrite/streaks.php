<?php
	
require_once('toolbox.php');

class streaks extends toolbox {
	private $db;
	private $lc = 0;
	private $kills = 1; 
	
	public function __construct() {
		$this->db = database::getInstance();
		$this->kills = $kills;
		$this->lc = $lc;
	}
	
	public function killStreak($gid) {
		$result = database::getInstance()->sqlResult(
			"SELECT DISTINCT playerID AS pid FROM players WHERE gid=\"$gid\"");
		
		while($row = mysql_fetch_assoc($result)) {
			
		}
	}
	public function deathStreak($gid) {
		$result = database::getInstance()->sqlResult(
			"SELECT DISTINCT playerID AS pid FROM players WHERE gid=\"$gid\"");
		
		while($row = mysql_fetch_assoc($result)) {
			
		}
	}
}

?>