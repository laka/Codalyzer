<?php

require_once('toolbox.php');
require_once('ratings.php');
require_once('streaks.php');
require_once('awards.php');

class loopwrap extends toolbox {
	private $db, $rating, $streaks, $awards;
	
	public function __construct() {
		$this->db = database::getInstance();
		$this->streaks = new streaks();
		$this->rating = new rating();
		$this->awards = new awards();
	}
	
	public function gamesLoop() {
		$result = database::getInstance()->sqlResult(
			"SELECT id FROM games WHERE parsed != 1 ORDER BY id ASC");
		
		while($row = mysql_fetch_assoc($result)) {
			# Remove short games
			if($this->cleanUpGames($row[id])) {
				continue;
			}
			# Run our ELO-rating system
			$this->rating->eloRating($row[id]);
			
			# Measure kill and death streaks
			$this->streaks->killDeathStreak($row[id]);
			
			# Hand out awards \o/
			$this->awards->mostKillsInGame($row[id]);
			
			# Fix start&stop times
			$this->adjustGameDuration($row[id], 0);
		}
	}
	
	public function playersLoop() {
		$result = database::getInstance()->sqlResult(
			"SELECT DISTINCT hash AS hash FROM profiles ORDER BY id ASC");
		
		while($row = mysql_fetch_assoc($result)) {
			# Add stats to the profile
			$this->addProfileData($row[hash]);
			
			# Fetch all aliases of the player
			$this->getEveryAlias($row[hash]);
		}
	}
}

?>