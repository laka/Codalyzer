<?php
require_once('toolbox.php');

class damages extends toolbox {
	
	# $matches:
	#	[1] timestamp
	#	[2] w_team 
	#	[3] wounded
	#	[4] h_team
	#	[5] hitman
	#	[6] weapon
	#	[7] damage
	#	[8] modification
	#	[9] location
	public function addKill($matches, $gid) {
		# Convert timestamps to seconds
		$matches[1] = $this->ts2seconds($matches[1]);
		
		# Strip trailing chars
		$matches[3] = $this->sanitizeString($matches[3]);
		$matches[5] = $this->sanitizeString($matches[5]);
		$matches[6] = $this->sanitizeString($matches[6]);
		$matches[8] = $this->sanitizeString($matches[8]);
		$matches[9] = $this->sanitizeString($matches[9]);
		
		# Fix world inflicts 
		if(empty($matches[3])) { 
			$matches[3] = $matches[5];
		}
		if(empty($matches[5])) { 
			$matches[5] = $matches[3];
		}
		
		# Convert mods to weapons 
		if(array_key_exists($matches[8], $this->mods)) {
			$matches[6] = $this->weaponMods($matches[8]);
		}
	} 
	
	# $matches:
	#	[1] timestamp
	#	[2] c_team 
	#	[3] corpse
	#	[4] k_team
	#	[5] killer
	#	[6] weapon
	#	[7] damage
	#	[8] modification
	#	[9] location
	public function addHit($matches, $gid) {
		
	}
}