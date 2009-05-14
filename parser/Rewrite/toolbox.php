<?php

class toolbox {
	private $db;
	public $mods;

	public function __construct() {
		$this->db = database::getInstance();
	}	
	# Returns gamedata upon request
	public function gameData($data, $gid) {
		$row = database::getInstance()->singleRow("SELECT $data FROM games WHERE id=\"$gid\"");
		return $row[$data];
	}
	# Returns timestamp (min:sec) converted to seconds 
	public function ts2seconds($ts) {
		$t = explode(':', $ts);
		return ($t[0]*60 + $t[1]); 
	}
	# Returns version based on name
	public function getVersion($game) {
		$versions = array(
			'call of duty' => 10,
			'cod:united offensive' => 15,
			'call of duty 4' => 40,
			'Call of Duty: World at War' => 50,
		);
		return $versions[strtolower($game)];
	}
	# Returns the last game id in the games-table
	public function lastGid() {
		$row = database::getInstance()->singleRow("SELECT id FROM games ORDER BY id DESC LIMIT 1");
		return $row['id'];
	}
	# Returns true if x player is playing
	public function playerInGame($hash, $handle, $gid) {
		$result = database::getInstance()->sqlResult("SELECT id FROM players WHERE handle=\"$handle\" AND gid=\"$gid\"");
		return mysql_num_rows($result);
	}
	# Returns a sanitized string
	public function sanitizeString($input) {
		$input = preg_replace('/MOD_/','',$input);
		$input = preg_replace('//','',$input);
		$input = preg_replace('//','',$input);
		$input = preg_replace('/\s+$/','',$input);
		$input = preg_replace('/^\s+/','',$input);
		$input = preg_replace('/QUICKMESSAGE_.*/','',$input);
		$input = preg_replace('/deserteaglegold_mp/','deserteagle_mp',$input);
		return $input;
	}
	# Returns an abbr weapon name based on modification
	public function weaponMods($mod) {
		$mods = array(
			'GRENADE_SPLASH EXPLOSIVE' => 'grenade_dh',
			'MELEE' => 'knife',
			'EXPLOSIVE' => 'bomb',
		);
		return $mods[$mod];
	}
}

?>
