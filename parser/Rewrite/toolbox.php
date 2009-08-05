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
	public function playerInGame($type, $sql, $gid) {
		$result = database::getInstance()->sqlResult(
			"SELECT id FROM players WHERE $type=\"$sql\" AND gid=\"$gid\"");
		return mysql_num_rows($result);
	}
	
	# Returns a players profile ID
	public function getPlayerIDByHash($hash) {
		$row = database::getInstance()->singleRow(
			"SELECT id FROM profiles WHERE hash=\"$hash\"");
		return $row[id];
	}
	
	# Returns a players hash 
	public function getPlayerHashByID($id) {
		$row = database::getInstance()->singleRow(
			"SELECT hash FROM profiles WHERE id=\"$id\"");
		return $row[hash];
	}
	
	# Returns a players handle
	public function getPlayerHandleByID($id) {
		$row = database::getInstance()->singleRow(
			"SELECT handle FROM profiles WHERE id=\"$id\"");
		return $row[handle];
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
	
	# Put the player on the team parsed from damage hits or kills
	public function addTeamMember($handle, $team, $gid) {
		database::getInstance()->sqlResult(
			"UPDATE players SET team=\"$team\" WHERE handle=\"$handle\" AND gid=\"$gid\"");
	}
	
	/* Profile & Alias functions
	--------------------------------------------------------------------------------------------------------*/

	# Grab the clan tag off the player handle
	public function addClanTag($hash) {
		$pid = $this->getPlayerIDByHash($hash);
		$handle = $this->getPlayerHandleByID($pid);
		
		if(preg_match('/(\w+).*?(\w+)$/', $handle, $match)) {
			$clan = $match[1];
			database::getInstance()->sqlResult(
				"UPDATE profiles SET clan=\"$clan\" WHERE id=\"$pid\"");
		}
	}
	
	# Add aliases ($owner = hash)
	public function addNewAlias($alias, $owner, $gid) {
		if(strlen($alias) < 2) {
			$this->addWarning('addNewAlias', 'missing alias', "$owner (gid: $gid)");
			return 0;
		}
		$alias = database::getInstance()->sqlQuote($alias);
		$result = database::getInstance()->sqlResult(
			"SELECT id FROM alias WHERE owner=\"$owner\" AND alias=\"$alias\"");
		if(mysql_num_rows($result) == 0) {
			database::getInstance()->sqlResult(
				"INSERT INTO alias (owner, alias) VALUES(\"$owner\", \"$alias\")");
		}
		if(is_numeric($gid)) {
			$row = database::getInstance()->singleRow(
				"SELECT handle AS old_handle FROM players WHERE hash=\"$owner\" AND gid=\"$gid\"");
			
			database::getInstance()->sqlResult(
				"UPDATE players SET handle=\"$alias\" WHERE hash=\"$owner\" AND gid=\"$gid\"");
			
			$this->addNewAlias($row[old_handle], $owner, '');
		}
	}
	
	# Add all aliases of a player
	public function getEveryAlias($hash) {
		$playerID = $this->getPlayerIDByHash($hash);
		$most_used = $this->mostUsedHandle($hash);
		$result = database::getInstance()->sqlResult(
			"SELECT DISTINCT handle AS alias FROM players WHERE playerID=\"$playerID\" AND handle != \"$most_used\" ORDER BY id");
		
		while($row = mysql_fetch_assoc($result)) {
			$this->addNewAlias($row['alias'], $hash, '');
		}
	}
	
	# Create a player profile
	public function makePlayerProfile($hash, $player) {
		$result = database::getInstance()->sqlResult(
			"SELECT id FROM profiles WHERE hash=\"$hash\"");
		if(mysql_num_rows($result)) {
			$this->setMostUsedHandle($hash);
		} else {
			$handle = $this->mostUsedHandle($hash);
			if(strlen($handle) == 0) { 
				$handle = $player; 
			}
			database::getInstance()->sqlResult(
				"INSERT INTO profiles (handle, hash) VALUES(\"$handle\", \"$hash\")");
		}
	}
	
	# Add stats to a players profile
	public function addProfileData($hash) {
		$this->sumPlayerKills($hash);
		$this->sumPlayerDeaths($hash);
		$this->sumPlayerSuicides($hash);
		$this->sumPlayerGames($hash);
		$this->getPlayerElo($hash, 'add');
		$this->addClanTag($hash);
	}
	
	# Update a profile handle to the most used one
	public function setMostUsedHandle($hash) {
		$most_used = $this->mostUsedHandle($hash);
		database::getInstance()->sqlResult(
			"UPDATE profiles SET handle=\"$most_used\" WHERE hash=\"$hash\"");
	}
	
	# Fetch the players most used alias based on hash
	public function mostUsedHandle($hash) {
		$playerID = $this->getPlayerIDByHash($hash);
		$row = database::getInstance()->singleRow(
			"SELECT handle, COUNT(handle) AS num FROM players WHERE playerID=\"$playerID\" GROUP BY handle ORDER BY num DESC LIMIT 1");
		
		return $row[handle];
	}
	
	/* Player stats functions
	--------------------------------------------------------------------------------------------------------*/
	
	# Sum all kills of a player
	public function sumPlayerKills($hash) {
		$playerID = $this->getPlayerIDByHash($hash);
		$row = database::getInstance()->singleRow(
			"SELECT COUNT(*) AS sum FROM kills WHERE killerID=\"$playerID\" AND corpseID!=\"$playerID\"");
		database::getInstance()->sqlResult(
			"UPDATE profiles SET kills=\"$row[sum]\" WHERE id=\"$playerID\"");
	}
	
	# Sum all deaths of a player
	public function sumPlayerDeaths($hash) {
		$playerID = $this->getPlayerIDByHash($hash);
		$row = database::getInstance()->singleRow(
			"SELECT COUNT(*) AS sum FROM kills WHERE corpseID=\"$playerID\" AND killerID!=\"$playerID\"");
		database::getInstance()->sqlResult(
			"UPDATE profiles SET deaths=\"$row[sum]\" WHERE id=\"$playerID\"");
	}
	
	# Sum all suicides of a player
	public function sumPlayerSuicides($hash) {
		$playerID = $this->getPlayerIDByHash($hash);
		$row = database::getInstance()->singleRow(
			"SELECT COUNT(*) AS sum FROM kills WHERE killerID=\"$playerID\" AND corpseID=\"$playerID\"");
		database::getInstance()->sqlResult(
			"UPDATE profiles SET suicides=\"$row[sum]\" WHERE id=\"$playerID\"");
	}
	
	# Sum all games of a player
	public function sumPlayerGames($hash) {
		$playerID = $this->getPlayerIDByHash($hash);
		$row = database::getInstance()->singleRow(
			"SELECT COUNT(DISTINCT gid) AS sum FROM players WHERE playerID=\"$playerID\"");
		database::getInstance()->sqlResult(
			"UPDATE profiles SET games=\"$row[sum]\" WHERE id=\"$playerID\"");
	}
	
	# Get a players last ELO-rating
	public function getPlayerElo($hash, $method) {
		$playerID = $this->getPlayerIDByHash($hash);
		$row = database::getInstance()->singleRow(
			"SELECT elo FROM players WHERE playerID=\"$playerID\" AND elo IS NOT NULL ORDER BY id DESC LIMIT 1");
		
		$elo = ($row[elo]) ? $row[elo] : 1000;
		
		if($method == 'return') {
			return $elo;
		} else {
			database::getInstance()->sqlResult(
				"UPDATE profiles SET elo=\"$elo\" WHERE id=\"$playerID\"");
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
		database::getInstance()->sqlResult("TRUNCATE TABLE streaks");
		database::getInstance()->sqlResult("TRUNCATE TABLE warnings");
	}
	
	# Optimize tables
	public function optimizeTables($limit) {
		database::getInstance()->sqlResult("OPTIMIZE TABLE games");
		database::getInstance()->sqlResult("OPTIMIZE TABLE players");
		database::getInstance()->sqlResult("OPTIMIZE TABLE kills");
		database::getInstance()->sqlResult("OPTIMIZE TABLE hits");
		database::getInstance()->sqlResult("OPTIMIZE TABLE quotes");
		database::getInstance()->sqlResult("OPTIMIZE TABLE alias");
		database::getInstance()->sqlResult("OPTIMIZE TABLE actions");
		database::getInstance()->sqlResult("OPTIMIZE TABLE profiles");
		database::getInstance()->sqlResult("OPTIMIZE TABLE streaks");
	}
	
	/* Cleanup functions
	--------------------------------------------------------------------------------------------------------*/
	
	# Delete profiles with 0 k&d and games <= 1
	public function cleanUpProfiles() {
		$result = database::getInstance()->sqlResult(
			"SELECT id FROM profiles WHERE (kills=0 OR deaths=0) AND games <= 1");
		
		while($row = mysql_fetch_assoc($result)) {
			$this->addWarning('cleanUpProfiles', 'player dropped', $row[id]);
			database::getInstance()->sqlResult("DELETE FROM profiles WHERE id=\"$row[id]\"");
			database::getInstance()->sqlResult("DELETE FROM players WHERE playerID=\"$row[id]\"");
		}
	}
	
	# Find games with kills < 5 or players <= 1 
	public function cleanUpGames($gid) {
		$row = database::getInstance()->singleRow(
			"SELECT id, 
				(SELECT COUNT(*) FROM kills WHERE gid=a.id AND killerID!=corpseID) AS kills, 
				(SELECT COUNT(*) FROM players WHERE gid=a.id) AS players 
			FROM games AS a WHERE id=\"$gid\"");
		
		if($row[kills] < 5) {
			#$this->addWarning('cleanUpGames', 'game dropped', $gid);
			$this->dropGame($gid);
		} 
		elseif($row[players] <= 1) {
			#$this->addWarning('cleanUpGames', 'game dropped', $gid);
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

	# Adjust game duration (start/stop)
	public function adjustGameDuration($gid, $limit) {
		$start = database::getInstance()->singleRow(
			"SELECT ts FROM kills WHERE killerID!=corpseID AND gid=\"$gid\" ORDER BY id ASC LIMIT $limit,1");
		$stop = database::getInstance()->singleRow(
			"SELECT ts FROM kills WHERE killerID!=corpseID AND gid=\"$gid\" ORDER BY id DESC LIMIT $limit,1");
		
		$duration = round(($stop[ts] - $start[ts])/60);
		
		# Should we have a $max_duration?
		if($duration > 60) {
			++$limit;
			$this->adjustGameDuration($gid, $limit);
			$this->addWarning('adjustGameDuration', 'max duration', $gid);
		} elseif($duration < 2) {
			$this->dropGame($gid);
		} else {
			database::getInstance()->sqlResult(
				"UPDATE games SET start=\"$start[ts]\", stop=\"$stop[ts]\" WHERE id=\"$gid\"");
		}
	}
	
	/* Error handling
	--------------------------------------------------------------------------------------------------------*/
	
	# Report warnings 
	public function addWarning($via, $warning, $dump) {
		database::getInstance()->sqlResult(
			"INSERT INTO warnings (ts, via, warning, dump) VALUES(NOW(), \"$via\(\)\", \"$warning\", \"$dump\")");
	}
	
	# Simple die function
	public function dieYoung($msg, $scope) {
		exit("$msg\n");
	}
}

?>
