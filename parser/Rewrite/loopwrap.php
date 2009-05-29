<?php
require_once('toolbox.php');

class loopwrap extends toolbox {
	private $db;
	public function __construct() {
		$this->db = database::getInstance();
	}
	public function gamesLoop() {
		$result = database::getInstance()->sqlResult(
			"SELECT id FROM games WHERE parsed != 1 ORDER BY id ASC");
		
		while($row = mysql_fetch_assoc($result)) {
			# Remove short games
			$this->cleanUpGames($row['id']);
		}
	}
	public function playersLoop() {
		$result = database::getInstance()->sqlResult(
			"SELECT DISTINCT hash AS puid FROM players ORDER BY id ASC");
		
		while($row = mysql_fetch_assoc($result)) {
			# Create a profile for the player
			$this->makePlayerProfile($row['puid']);
		
			# Add stats to the profile
			$this->addProfileData($row['puid']);
			
			# Fetch all aliases of the player
			$this->getEveryAlias($row['puid']);
		}
	}
}

?>