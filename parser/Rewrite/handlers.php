<?php

require_once('toolbox.php');

class handler extends toolbox {
	private $db;
	public function __construct() {
		$this->db = database::getInstance();
	}
	public function addQuote($matches, $gid) {
		# Convert timestamp to seconds	
		$matches[1] = $this->ts2seconds($matches[1]); 
		
		# Leaving 8 last chars of the hash
		$matches[2] = substr($matches[2], -8);
		
		# Fetch player ID
		$playerID = $this->getPlayerIDByHash($matches[2]);
		
		database::getInstance()->sqlResult("INSERT INTO quotes (gid, ts, playerID, quote) 
			VALUES(\"$gid\", \"$matches[1]\", \"$playerID\", \"$matches[4]\")");
	}
	public function addPlayerAction($matches, $gid) {
		# Convert timestamp to seconds	
		$matches[1] = $this->ts2seconds($matches[1]); 
		
		# Fetch player ID
		$playerID = $this->getPlayerIDByHash($matches[3]);
		
		$matches[3] = preg_replace('/axis;|allies;/','',$matches[3]);
		
		database::getInstance()->sqlResult("INSERT INTO actions (gid, ts, action, playerID) 
			VALUES(\"$gid\", \"$matches[1]\", \"$matches[2]\", \"$playerID\")");
	}
	public function addTeamScore($matches, $gid) {
		$highscore = max($matches[2], $matches[3]);
		$lowscore = min($matches[2], $matches[3]);
		
		if($matches[1] == 'axis') {
			$axisscore = $highscore;
			$alliesscore = $lowscore;
		} else {
			$alliesscore = $highscore;
			$axisscore = $lowscore;
		}
		
		database::getInstance()->sqlResult(
			"UPDATE games SET axisscore=\"$axisscore\", alliesscore=\"$alliesscore\" WHERE id=\"$gid\"");
		
		$this->addExitGame($gid);
	}
	public function addGameStopTime($matches, $gid) {
		# Convert timestamp to seconds	
		$matches[1] = $this->ts2seconds($matches[1]); 
		
		database::getInstance()->sqlResult(
			"UPDATE games SET stop=\"$matches[1]\" WHERE id=\"$gid\"");
	}
	public function addExitGame($gid) {
		database::getInstance()->sqlResult(
			"UPDATE games SET finish=\"1\" WHERE id=\"$gid\"");
	}
	public function addRoundCount($matches, $gid) {
		if($matches[1] == '20') {
			$this->addExitGame($gid);
		}
		database::getInstance()->sqlResult(
			"UPDATE games SET rcount=\"$matches[1]\" WHERE id=\"$gid\"");
	}
}

?>