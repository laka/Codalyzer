<?php

class kills extends toolbox {
	public function __construct() {
		require_once('players.php');
		$this->players = new players();
	}

	public function addKill($matches, $gid) {
		$matches = $this->chomp($matches);
		$ts = $this->inSeconds($matches[1]);
		$corpse = $this->players->getPlayerID(substr($matches[2], -8));
		$killer = $this->players->getPlayerID(substr($matches[4], -8));
		
		database::getInstance()->sqlResult(
			"INSERT INTO kills(gid, ts, killerID, corpseID) 
			VALUES(\"$gid\", \"$ts\", \"$killer\", \"$corpse\")");
	}
}

?>
