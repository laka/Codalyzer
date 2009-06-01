<?php

require_once('toolbox.php');

class damage extends toolbox {
	/*	Add a hit 
	 $matches: 
		[1] timestamp 
		[2] w_hash
		[3] w_team
		[4] wounded
		[5] h_hash
		[6] h_team 
		[7] hitman 
		[8] weapon 
		[9] damage 
		[10] modification 
		[11] location
	--------------------------------------------------------------------------------------------------------*/
	public function addHit($matches, $gid) {
		
		# Convert timestamps to seconds
		$matches[1] = $this->ts2seconds($matches[1]);
		
		# Leaving 8 last chars of the hash
		$matches[2] = substr($matches[2], -8);
		$matches[5] = substr($matches[5], -8);
		
		# Convert mods to weapons 
		$matches[8] = $this->weaponMods($matches[8], $matches[10]);
		
		# Fix world inflicts 
		if(empty($matches[7])) { 
			$matches[7] = $matches[4];
			$matches[5] = $matches[2];
			$matches[6] = $matches[3];
		}
		
		# Assign teams to players unless the game is running a modification
		if($this->gameData('mods', $gid) == 'none') {
			$this->addTeamMember($matches[4], $matches[3], $gid);	
			$this->addTeamMember($matches[7], $matches[6], $gid);
		}
		
		if(!$this->playerInGame('handle', $matches[4], $gid)) {
			$this->addNewAlias($matches[4], $matches[2], $gid);
		}
		if(!$this->playerInGame('handle', $matches[7], $gid)) {
			$this->addNewAlias($matches[7], $matches[5], $gid);
		}
			
		database::getInstance()->sqlResult(
			"INSERT INTO hits (gid, ts, hitman, h_hash, h_team, wounded, w_hash, w_team, damage, weapon, mods, location)
			VALUES(\"$gid\", \"$matches[1]\", \"$matches[7]\", \"$matches[5]\", \"$matches[6]\", \"$matches[4]\", 
					\"$matches[2]\", \"$matches[3]\", \"$matches[9]\", \"$matches[8]\", \"$matches[10]\", \"$matches[11]\")");
	}
	
	/* Add a kill 
	 $matches: 
		[1] timestamp 
		[2] c_hash 
	 	[3] c_team 
		[4] corpse
		[5] k_hash
		[6] k_team 
		[7] killer 
		[8] weapon 
		[9] damage 
		[10] modification 
		[11] location)
	--------------------------------------------------------------------------------------------------------*/
	public function addKill($matches, $gid) {
		# Convert timestamps to seconds
		$matches[1] = $this->ts2seconds($matches[1]);
		
		# Leaving 8 last chars of the hash
		$matches[2] = substr($matches[2], -8);
		$matches[5] = substr($matches[5], -8);
		
		# Fix world inflicts 
		if(empty($matches[7])) { 
			$matches[7] = $matches[4];
			$matches[5] = $matches[2];
			$matches[6] = $matches[3];
		}		
		
		# Convert mods to weapons 
		$matches[8] = $this->weaponMods($matches[8], $matches[10]);
		
		if(!$this->playerInGame('handle', $matches[4], $gid)) {
			$this->addNewAlias($matches[4], $matches[2], $gid);
		}
		if(!$this->playerInGame('handle', $matches[7], $gid)) {
			$this->addNewAlias($matches[7], $matches[5], $gid);
		}
		
		database::getInstance()->sqlResult(
			"INSERT INTO kills (gid, ts, killer, k_hash, k_team, corpse, c_hash, c_team, damage, weapon, mods, location)
			VALUES(\"$gid\", \"$matches[1]\", \"$matches[7]\", \"$matches[5]\", \"$matches[6]\", \"$matches[4]\", 
					\"$matches[2]\", \"$matches[3]\", \"$matches[9]\", \"$matches[8]\", \"$matches[10]\", \"$matches[11]\")");
	} 
}

?>
