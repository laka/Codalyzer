<?php

class players {
	public function addPlayer($matches, $gid) {
		$handle = $matches[3];
		$ts = $this->inSeconds($matches[1]);
		if(strlen($matches[2]) > 8) {
			$hash = substr($matches[2], -8);
		} else {
			$this->newWarning('addPlayer', 'NO_HASH', $ts);
		}
		
		$this->makeProfile($hash, $handle);
		$playerID = $this->getPlayerID($hash);
		
		if(!$this->playerInGame($handle, $gid)) {
			database::getInstance()->sqlResult("
				INSERT INTO players (gid, ts, playerID, handle)
				VALUES(\"$gid\", \"$ts\", \"$playerID\", \"$handle\")
			");
		}
	}
	
	public function addTeam($matches, $gid) {
		$handle = $matches[1];
		$team = $matches[2];

		database::getInstance()->sqlResult("
			UPDATE players 
			SET team=\"$team\"
			WHERE handle=\"$handle\"
			AND gid=\"$gid\"
		");
	}
	
	public function addAction($matches, $gid) {
	
	}
	
	public function addClanTag($matches, $gid) {
	
	}
	
	public function addQuote($matches, $gid) {
	
	}
	
	public function addNewAlias($alias, $owner, $gid) {
		
	}
	
	public function getEveryAlias($hash) {
	
	}
	
	public function makeProfile($hash, $handle) {
	
	}
	
	public function addProfileData($hash) {
	
	}
	
	public function sumKills($hash) {
	
	}
	
	public function sumDeaths($hash) {
	
	}
	
	public function sumSuicides($hash) {
	
	}
	
	public function sumGames($hash) {
	
	}
	
	public function playerELO($hash) {
		
	}
	
	public function getPlayerID($hash) {
		
	}
	
	public function playerInGame($handle) {
	
	}
}

?>
