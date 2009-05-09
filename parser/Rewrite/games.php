<?php
require_once('toolbox.php');

class game extends toolbox {
	private $game;

	public function __construct($game) {
		# Check for game support
		$supported = array('cod');
		if(in_array($game, $supported)) {
			$this->game = $game;
		} else {
			# Add some custom exceptions
		}
	}

	# $matches:
	#	[1] timestamp
	#	[2] modification
	#	[3] gametype
	#	[4] game version
	#   [5] map
	public function addNewGame($matches) {
		if($this->game == 'cod') {
			# Fetch the last game id in the database
			$gid = $this->lastGid();				

			# Grabbing the game modification
			$matches[2] = preg_replace
				('/^.*fs_game\\\\.*\/(.*?)\\\\g_compass.*$/i',"$1", $matches[2]);
			
			# Translate cod name to game version
			$matches[4] = $this->getVersion($matches[4]);

			# Convert timestamp to seconds	
			$matches[1] = $this->ts2seconds($matches[1]); 
			
			# Check to see if we have an ongoing game
			if(is_numeric($gid)) {
				# Check for modes where InitGame restarts
				if(preg_match('/sd|sab/', $this->gameData('type', $gid))) {
					if(($this->gameData('map', $gid) == $matches[5]) && 
					   ($this->gameData('type', $gid) == $matches[3]) &&
					   ($this->gameData('finish', $gid) == 0)) {
						# We assume the same game is ongoing
							mysql_query("UPDATE games SET stop=0 WHERE gid=\"$gid\"");
							return 0;
					}
				}
			}
			# If it's a new game - insert the data accordingly
			mysql_query("INSERT INTO games (start, mods, type, version, map)
				VALUES(\"$matches[1]\", \"$matches[2]\", \"$matches[3]\", \"$matches[4]\", \"$matches[5]\")");
		}
	}	
}

?>
