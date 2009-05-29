<?php

class toolbox {
	private $db;
	public function __construct() {
		$this->db = database::getInstance();
	}
	
	/* Lookup functions
	--------------------------------------------------------------------------------------------------------*/
	
	# Returns gamedata upon request
	public function gameData($data, $gid) {
		$row = database::getInstance()->singleRow(
			"SELECT $data FROM games WHERE id=\"$gid\"");
		return $row[$data];
	}
	
	# Returns true if x player is playing
	public function playerInGame($handle, $gid) {
		$result = database::getInstance()->sqlResult(
			"SELECT id FROM players WHERE handle=\"$handle\" AND gid=\"$gid\"");
		return mysql_num_rows($result);
	}
	
	/* Convert functions
	--------------------------------------------------------------------------------------------------------*/
	
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
	
	/* Handle functions
	--------------------------------------------------------------------------------------------------------*/

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
	
	# Put the player on the team parsed from damage hits or kills
	public function addTeamMember($handle, $team, $gid) {
		database::getInstance()->sqlResult(
			"UPDATE players SET team=\"$team\" WHERE handle=\"$handle\" AND gid=\"$gid\"");
	}
	
	/* Profile & Alias functions
	--------------------------------------------------------------------------------------------------------*/
	
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
	
	# Add all aliases of a player
	public function getEveryAlias($puid) {
		$most_used = $this->mostUsedHandle($puid);
		$result = database::getInstance()->sqlResult(
			"SELECT DISTINCT handle AS alias FROM players WHERE hash=\"$puid\" AND handle != \"$most_used\" ORDER BY id");
		
		while($row = mysql_fetch_assoc($result)) {
			$this->addNewAlias($row['alias'], $puid);
		}
	}
	
	# Create a player profile
	public function makePlayerProfile($puid) {
		$result = database::getInstance()->sqlResult(
			"SELECT id FROM profiles WHERE hash=\"$puid\"");
		if(mysql_num_rows($result)) {
			$this->setMostUsedHandle($puid);
		} else {
			$handle = $this->mostUsedHandle($puid);
			database::getInstance()->sqlResult(
				"INSERT INTO profiles (handle, hash) VALUES(\"$handle\", \"$puid\")");
		}
	}
	
	# Add stats to a players profile
	public function addProfileData($puid) {
		$this->sumPlayerKills($puid);
		$this->sumPlayerDeaths($puid);
		$this->sumPlayerSuicides($puid);
		$this->sumPlayerGames($puid);
		$this->getPlayerElo($puid);
		$this->getPlayerClan($puid);
	}
	
	# Update a profile handle to the most used one
	public function setMostUsedHandle($puid) {
		$most_used = $this->mostUsedHandle($puid);
		database::getInstance()->sqlResult(
			"UPDATE profiles SET handle=\"$most_used\" WHERE hash=\"$puid\"");
	}
	
	# Fetch the players most used alias based on hash
	public function mostUsedHandle($puid) {
		$row = database::getInstance()->singleRow(
			"SELECT handle, COUNT(handle) AS num FROM players WHERE hash=\"$puid\" GROUP BY handle ORDER BY num DESC LIMIT 1");
		return $row['handle'];
	}
	
	/* Player stats functions
	--------------------------------------------------------------------------------------------------------*/
	
	# Sum all kills of a player
	public function sumPlayerKills($puid) {
		
	}
	
	/* Database functions
	--------------------------------------------------------------------------------------------------------*/
	
	# Truncate tables
	public function truncateTables($limit) {
		database::getInstance()->sqlResult("TRUNCATE TABLE games");
		database::getInstance()->sqlResult("TRUNCATE TABLE players");
		database::getInstance()->sqlResult("TRUNCATE TABLE kills");
		database::getInstance()->sqlResult("TRUNCATE TABLE hits");
		database::getInstance()->sqlResult("TRUNCATE TABLE quotes");
		database::getInstance()->sqlResult("TRUNCATE TABLE alias");
		database::getInstance()->sqlResult("TRUNCATE TABLE actions");
		database::getInstance()->sqlResult("TRUNCATE TABLE profiles");
	}
}

?>
