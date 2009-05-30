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
	public function playerInGame($hash, $gid) {
		$result = database::getInstance()->sqlResult(
			"SELECT id FROM players WHERE hash=\"$hash\" AND gid=\"$gid\"");
		return mysql_num_rows($result);
	}
	
	/* Convert functions
	--------------------------------------------------------------------------------------------------------*/
	
	# Common round function
	public function round($num, $dec) {
		#$number = $num || 0;
		#$dec = 10 ($dec || 0);
		#return int($dec * $number + .5 * ($number <=> 0)) / $dec;
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
		$this->getPlayerElo($puid, 'add');
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
		$row = database::getInstance()->singleRow(
			"SELECT COUNT(*) AS sum FROM kills WHERE k_hash=\"$puid\" AND c_hash!=\"$puid\"");
		database::getInstance()->sqlResult(
			"UPDATE profiles SET kills=\"$row[sum]\" WHERE hash=\"$puid\"");
	}
	
	# Sum all deaths of a player
	public function sumPlayerDeaths($puid) {
		$row = database::getInstance()->singleRow(
			"SELECT COUNT(*) AS sum FROM kills WHERE c_hash=\"$puid\" AND k_hash!=\"$puid\"");
		database::getInstance()->sqlResult(
			"UPDATE profiles SET deaths=\"$row[sum]\" WHERE hash=\"$puid\"");
	}
	
	# Sum all suicides of a player
	public function sumPlayerSuicides($puid) {
		$row = database::getInstance()->singleRow(
			"SELECT COUNT(*) AS sum FROM kills WHERE k_hash=\"$puid\" AND c_hash=\"$puid\"");
		database::getInstance()->sqlResult(
			"UPDATE profiles SET suicides=\"$row[sum]\" WHERE hash=\"$puid\"");
	}
	
	# Sum all games of a player
	public function sumPlayerGames($puid) {
		$row = database::getInstance()->singleRow(
			"SELECT COUNT(DISTINCT gid) AS sum FROM players WHERE hash=\"$puid\"");
		database::getInstance()->sqlResult(
			"UPDATE profiles SET games=\"$row[sum]\" WHERE hash=\"$puid\"");
	}
	
	# Get a players last ELO-rating
	public function getPlayerElo($puid, $method) {
		$row = database::getInstance()->singleRow(
			"SELECT elo FROM players WHERE hash=\"$puid\" AND elo IS NOT NULL ORDER BY id DESC LIMIT 1");
		
		$elo = ($row[elo]) ? $row[elo] : 1000;
		
		if($method == 'return') {
			return $elo;
		} else {
			database::getInstance()->sqlResult(
				"UPDATE profiles SET elo=\"$elo\" WHERE hash=\"$puid\"");
		}
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
	
	/* Cleanup functions
	--------------------------------------------------------------------------------------------------------*/
	
	# Delete profiles with 0 k&d and games <= 1
	public function cleanUpProfiles() {
		$result = database::getInstance()->sqlResult(
			"SELECT hash FROM profiles WHERE (kills=0 OR deaths=0) AND games <= 1");
		
		while($row = mysql_fetch_assoc($result)) {
			database::getInstance()->sqlResult("DELETE FROM profiles WHERE hash=\"$row[hash]\"");
			database::getInstance()->sqlResult("DELETE FROM players WHERE hash=\"$row[hash]\"");
		}
	}
	
	# Find games with kills < 5 or players <= 1 
	public function cleanUpGames($gid) {
		$row = database::getInstance()->singleRow(
			"SELECT id, 
				(SELECT COUNT(*) FROM kills WHERE gid=a.id) AS kills, 
				(SELECT COUNT(*) FROM players WHERE gid=a.id) AS players 
			FROM games AS a WHERE id=\"$gid\"");
		
		if($row[kills] < 5) {
			$this->dropGame($gid);
		} 
		elseif($row[players] <= 1) {
			$this->dropGame($gid);
		}
	}
	
	# ..and then delete them
	public function dropGame($gid) {
		database::getInstance()->sqlResult("DELETE FROM games WHERE id=\"$gid\"");
		database::getInstance()->sqlResult("DELETE FROM actions WHERE gid=\"$gid\"");
		database::getInstance()->sqlResult("DELETE FROM kills WHERE gid=\"$gid\"");
		database::getInstance()->sqlResult("DELETE FROM hits WHERE gid=\"$gid\"");
		database::getInstance()->sqlResult("DELETE FROM quotes WHERE gid=\"$gid\"");
		database::getInstance()->sqlResult("DELETE FROM players WHERE gid=\"$gid\"");
	}
}

?>
