<?php
require_once('toolbox.php');

class handler extends toolbox {
	public function addQuote($matches, $gid) {
		# Convert timestamp to seconds	
		$matches[1] = $this->ts2seconds($matches[1]); 
		
		database::getInstance()->sqlResult("INSERT INTO quotes (gid, ts, handle, quote) 
			VALUES(\"$gid\", \"$matches[1]\", \"$matches[2]\", \"$matches[3]\")");
	}
	public function addPlayerAction($matches, $gid) {
		# Convert timestamp to seconds	
		$matches[1] = $this->ts2seconds($matches[1]); 
		
		database::getInstance()->sqlResult("INSERT INTO actions (gid, ts, action, handle) 
			VALUES(\"$gid\", \"$matches[1]\", \"$matches[2]\", \"$matches[3]\")");
	}
	public function addTeamScore($matches, $gid) {
		$highscore = max($matches[2], $matches[3]);
		$lowbirdzz = min($matches[2], $matches[3]);
		
		if($matches[1] == 'axis') {
			$axisscore = $highscore;
			$alliesscore = $lowbirdzz;
		} else {
			$alliesscore = $highscore;
			$axisscore = $lowbirdzz;
		}
		
		database::getInstance()->sqlResult("UPDATE games SET axisscore=\"$axisscore\", 
			alliesscore=\"$alliesscore\" WHERE id=\"$gid\"");
	}
	public function addGameStopTime($matches, $gid) {
		database::getInstance()->sqlResult("UPDATE games SET stop=\"$matches[1]\" WHERE id=\"$gid\"");
	}
	public function addExitGame($matches, $gid) {
		database::getInstance()->sqlResult("UPDATE games SET finish=\"1\" WHERE id=\"$gid\"");
	}
}

?>