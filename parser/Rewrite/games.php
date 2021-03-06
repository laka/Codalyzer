<?php

require_once('toolbox.php');

class game extends toolbox {
	private $game;
	
	/* Class constructor
	--------------------------------------------------------------------------------------------------------*/
	public function __construct($game) {
		# Check for game support
		# NOTE: We will probably be keeping all supported games in a table
		# But this is mainly a Call of Duty parser 
		$supported = array('cod');
		if(in_array($game, $supported)) {
			$this->game = $game;
		} else {
			# Add some custom exceptions here
		}
	}

	/* Add a new game ($matches: [1] timestamp [2] modification [3] gametype [4] game version [5] map)
	--------------------------------------------------------------------------------------------------------*/
	public function addNewGame($matches, $gid) {
		# Convert timestamp to seconds	
		$matches[1] = $this->ts2seconds($matches[1]); 
	
		# Grabbing the game modification
		$matches[2] = preg_replace
			('/^.*fs_game\\\\.*\/(.*?)\\\\g_compass.*$/i',"$1", $matches[2]);
		
		# We do currently only support pam4
		if(preg_match('/pam/i', $matches[2])) {
			$matches[2] = 'pam4';
		} else { 
			$matches[2] = 'none';
		}
		
		if(strlen($matches[3]) < 2) { $this->addWarning('addNewGame', 'unknown gametype', $matches[3]); }
		if(strlen($matches[5]) < 5) { $this->addWarning('addNewGame', 'unknown map', $matches[5]); }
		
		# Translate cod name to game version
		$matches[4] = $this->getVersion($matches[4]);

		# NOTE: We should have a 5th checkpoint (issue 27)
		# Check to see if we have an ongoing game
		if(is_numeric($gid)) {
			# Check for modes where InitGame restarts
			if(preg_match('/sd|sab|koth|dom/', $this->gameData('type', $gid))) {
				if(($this->gameData('map', $gid) == $matches[5]) && 
					($this->gameData('type', $gid) == $matches[3]) &&
					($this->gameData('finish', $gid) == 0)) {
					# We assume the same game is ongoing when the above conditions are met
						database::getInstance()->sqlResult("UPDATE games SET stop=0 WHERE gid=\"$gid\"");
						return 0;
				}
			}
		} else {
			$result = database::getInstance()->sqlResult("SELECT id FROM games");
			if(mysql_num_rows($result)) {
				$this->dieYoung('INIT ERROR: No GAME ID defined', 'all');
			}
		}
		
		# If it's a new game - insert the data accordingly
		database::getInstance()->sqlResult("INSERT INTO games (timeparsed, start, mods, type, version, map)
			VALUES(NOW(), \"$matches[1]\", \"$matches[2]\", \"$matches[3]\", \"$matches[4]\", \"$matches[5]\")");
	}
	
	/* Add players to a game ($matches: [1] timestamp [2] hash [3] handle)
	--------------------------------------------------------------------------------------------------------*/
	public function addNewPlayer($matches, $gid) {
		# Get correct hash/pid depending on game version
		if(strlen($matches[2]) > 0) {
			$pid = explode(';', $matches[2]);
			if(strlen($pid[0]) > 7) {
				$matches[2] = $pid[0];
				# Leaving 8 last chars of the hash
				$matches[2] = substr($matches[2], -8);
			} else {
				$matches[2] = $pid[1];
			}
		} else {
			# Skipping player since we have no hash 
			$this->addWarning('addNewPlayer', 'missing hash', $matches[3]);
			return 0;
		}
		
		# Convert timestamp to seconds	
		$matches[1] = $this->ts2seconds($matches[1]); 
		
		# Create a profile for the player
		$this->makePlayerProfile($matches[2], $matches[3]);
		
		# Get player ID
		$playerID = $this->getPlayerIDByHash($matches[2]);
		
		# Is the player allready in the game?
		if(!$this->playerInGame('handle', $matches[3], $gid)) {
			database::getInstance()->sqlResult("INSERT INTO players (gid, ts, playerID, handle)
				VALUES(\"$gid\", \"$matches[1]\", \"$playerID\", \"$matches[3]\")");
		}	
	}
	
	/* Add team to a player ($matches: [1] timestamp [2] hash [3] team [4] handle)
	--------------------------------------------------------------------------------------------------------*/
	public function addTeamMember($matches, $gid) {
		# Skip the player if no hash exists
		if(strlen($matches[2]) < 8) {
			return 0;
		}
		# Convert timestamp to seconds	
		$matches[1] = $this->ts2seconds($matches[1]);
		
		# Leaving 8 last chars of the hash
		$matches[2] = substr($matches[2], -8);

		# We don't need to assign new teams if the game allready is running
		if($this->gameData('rcount', $gid) > 5) {
			return 0;
		} else {
			# We flip the teams because they change side after 10 
			# rounds - so we get the last team the player was on
			$matches[3] = ($matches[3] == 'axis') ? 'allies' : 'axis';
			
			database::getInstance()->sqlResult("UPDATE players SET team=\"$matches[3]\" 
				WHERE handle=\"$matches[4]\" AND gid=\"$gid\"");
		}		
	}
}

?>
