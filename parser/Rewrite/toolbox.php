<?php

class toolbox {
	private $db;
	public function __construct() {
		$this->db = database::getInstance();
	}
	
	# Returns gamedata upon request
	public function gameData($data, $gid) {
		$row = database::getInstance()->singleRow(
			"SELECT $data FROM games WHERE id=\"$gid\"");
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
		$row = database::getInstance()->singleRow(
			"SELECT id FROM games ORDER BY id DESC LIMIT 1");
		return $row['id'];
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
	
	# Returns true if x player is playing
	public function playerInGame($handle, $gid) {
		$result = database::getInstance()->sqlResult(
			"SELECT id FROM players WHERE handle=\"$handle\" AND gid=\"$gid\"");
		return mysql_num_rows($result);
	}
	
	# Returns an abbr weapon name based on modification
	public function weaponMods($weapon, $mod) {
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

	# Change the handle of a player
	public function setNewHandle($handle, $hash, $gid) {
		$row = database::getInstance()->singleRow(
			"SELECT handle AS old_handle FROM players WHERE hash=\"$hash\" AND gid=\"$gid\"");
		
			database::getInstance()->sqlResult(
				"UPDATE players SET handle=\"$handle\" WHERE hash=\"$hash\" AND gid=\"$gid\"");
			database::getInstance()->sqlResult(
				"UPDATE kills SET killer=\"$handle\" WHERE killer=\"$row[old_handle]\" AND gid=\"$gid\"");
			database::getInstance()->sqlResult(
				"UPDATE kills SET corpse=\"$handle\" WHERE corpse=\"$row[old_handle]\" AND gid=\"$gid\"");
			database::getInstance()->sqlResult(
				"UPDATE hits SET hitman=\"$handle\" WHERE hitman=\"$row[old_handle]\" AND gid=\"$gid\"");
			database::getInstance()->sqlResult(
				"UPDATE hits SET wounded=\"$handle\" WHERE wounded=\"$row[old_handle]\" AND gid=\"$gid\"");
	}
	
	# Truncate tables
	public function truncateTables($limit) {
		database::getInstance()->sqlResult("TRUNCATE TABLE games");
		database::getInstance()->sqlResult("TRUNCATE TABLE players");
		database::getInstance()->sqlResult("TRUNCATE TABLE kills");
		database::getInstance()->sqlResult("TRUNCATE TABLE hits");
		database::getInstance()->sqlResult("TRUNCATE TABLE quotes");
		database::getInstance()->sqlResult("TRUNCATE TABLE alias");
		database::getInstance()->sqlResult("TRUNCATE TABLE actions");
	}
	
	# Put the player on the team parsed from damage hits or kills
	public function addTeamMember($handle, $team, $gid) {
		database::getInstance()->sqlResult(
			"UPDATE players SET team=\"$team\" WHERE handle=\"$handle\" AND gid=\"$gid\"");
	}
	
	# Add aliases ($owner = hash)
	public function addNewAlias($alias, $owner) {
		$result = database::getInstance()->sqlResult(
			"SELECT id FROM alias WHERE owner=\"$owner\" AND alias=\"$alias\"");
		if(mysql_num_rows($result)) {
			return 0;
		} else {
			database::getInstance()->sqlResult(
				"INSERT INTO alias (owner, alias) VALUES(\"$owner\", \"$alias\")");
		}
	}
}

?>
