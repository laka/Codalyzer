<?php

class kills extends toolbox {
	public function __construct() {
		$this->players = new players();
		$this->games = new games();
	}

	public function addKill($matches, $gid) {
		$matches  = $this->chomp($matches);	
		$round    = $this->games->currentRound($gid);
		$corpse   = $this->players->getPlayerID(substr($matches[2], -8));
		$killer   = $this->players->getPlayerID(substr($matches[4], -8));
		$ts 	  = $this->inSeconds($matches[1]);
		$damage   = $matches[7];
		$weapon   = $matches[6];
		$mod 	  = preg_replace('/MOD_/', '', $matches[8]);
		$location =	$matches[9];

		if(empty($killer)) {
			$killer = $corpse;
		}	
	
		database::getInstance()->sqlResult(
			"INSERT INTO kills(gid, round, ts, killerID, corpseID, damage, weapon, mods, location) 
			VALUES(\"$gid\", \"$round\", \"$ts\", \"$killer\", \"$corpse\", \"$damage\", \"$weapon\", \"$mod\", \"$location\")");
	}
}

?>
