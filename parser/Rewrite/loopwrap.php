<?php
require_once('toolbox.php');
require_once('ratings.php');

class loopwrap extends toolbox {
	private $db, $rating;
	
	public function __construct() {
		$this->db = database::getInstance();
		$this->rating = new rating();
	}
	public function gamesLoop() {
		$result = database::getInstance()->sqlResult(
			"SELECT id FROM games WHERE parsed != 1 ORDER BY id ASC");
		
		while($row = mysql_fetch_assoc($result)) {
			# Remove short games
			$this->cleanUpGames($row['id']);
			
			# Run our ELO-rating system
			$this->rating->eloRating($row['id']);
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