<?php

class toolbox {
	public function inSeconds($ts) {
		$t = explode(':', $ts);
		return ($t[0]*60 + $t[1]);
	}

	public function reportError($type, $msg, $ts) {

	}

	public function chomp($array) {
		foreach($array as $s) {
			$s = trim($s);
			$clean[] = $s;
		}
		return $clean; 
	}

	public function weaponAbbr($weapon, $mod) {
		$mods = array(
			'GRENADE_SPLASH EXPLOSIVE' => 'grenade_dh',
			'MELEE' => 'knife',
			'EXPLOSIVE' => 'bomb',
		);
		
		if(array_key_exists($mod, $mods)) {
			return $mods[$mod];
		} else { 
			return $weapon;
		}
	}
}

?>
