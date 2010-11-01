<?php

class damages extends toolbox {
	public function __construct() {
		$this->players = new players();
		$this->games = new games();
	} 	
	public function addDamage($matches, $gid) {
		$matches  = $this->chomp($matches);	
		$round    = $this->games->currentRound($gid);
		$wounded  = $this->players->getPlayerID(substr($matches[2], -8));
		$hitman   = $this->players->getPlayerID(substr($matches[5], -8));
		$ts 	  = $this->inSeconds($matches[1]);
		$mod 	  = preg_replace('/MOD_/', '', $matches[10]);
		$weapon   = $this->weaponAbbr($matches[8], $mod);
		$damage   = $matches[9];
		$location = $matches[11];
		$w_team   = $matches[3];
		$h_team   = $matches[6];		

		if(empty($hitman)) {
			$hitman = $wounded;
			$h_team = $w_team;
		}	

		$this->players->addTeamMember($wounded, $w_team, $gid);
		$this->players->addTeamMember($hitman, $h_team, $gid);

		database::getInstance()->sqlResult(
			"INSERT INTO hits (gid, round, ts, woundedID, w_team, hitmanID, h_team, damage, weapon, mods, location) 
			VALUES(\"$gid\", \"$round\", \"$ts\", \"$wounded\", \"$w_team\", \"$hitman\", \"$h_team\", \"$damage\", \"$weapon\", \"$mod\", \"$location\")");
	}
}

?>
