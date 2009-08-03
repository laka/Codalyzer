<?php
	
require_once('toolbox.php');

class streaks extends toolbox {
	private $db, $kills, $deaths;
	public function __construct() {
		$this->db = database::getInstance();
	}
	
	public function killStreak($gid) {
		echo "-- COLLECTING STREAKS IN GAME $gid\n";
		$pids = database::getInstance()->sqlResult(
			"SELECT DISTINCT playerID AS player FROM players WHERE gid=\"$gid\"");
		
		while($pid = mysql_fetch_assoc($pids)) {
			$kills = 0;
			$deaths = 0;
			$player = $this->getPlayerHandleByID($pid[player]);
			echo "Checking kill streak for <$player>\n";
			$killz = database::getInstance()->sqlResult(
				"SELECT killerID AS killer, corpseID AS corpse FROM kills WHERE gid=\"$gid\"");
			
			while($players = mysql_fetch_assoc($killz)) {
				$rows = $this->rowCount($gid);
				
				if($pid[player] == $players[killer] && $pid[player] != $players[corpse]) {
					$kills++;
					echo "\tHe KILLED somebody\n";
					$current_deaths = $this->getPlayerStreak($pid[player], 'death');
					if(is_numeric($current_deaths)) { 
						if($current_deaths < $kills) {
							database::getInstance()->sqlResult(
								"UPDATE streaks SET streak=\"$deaths\", gid=\"$gid\" WHERE playerID=\"$pid[player]\" AND type=\"death\"");
						}
					} else {
						database::getInstance()->sqlResult(
							"INSERT INTO streaks (gid, type, playerID, streak) VALUES(\"$gid\", \"death\", \"$pid[player]\", \"$deaths\")");
					}
					$deaths = 0;
				}
				if($pid[player] == $players[corpse]) {
					$deaths++;
					echo "\tHe DIED, streak ended. Landed on $kills\n";
					$current_kills = $this->getPlayerStreak($pid[player], 'kill');
					if(is_numeric($current_kills)) { 
						if($current_kills < $kills) {
							database::getInstance()->sqlResult(
								"UPDATE streaks SET streak=\"$kills\", gid=\"$gid\" WHERE playerID=\"$pid[player]\" AND type=\"kill\"");
						}
					} else {
						database::getInstance()->sqlResult(
							"INSERT INTO streaks (gid, type, playerID, streak) VALUES(\"$gid\", \"kill\", \"$pid[player]\", \"$kills\")");
					}
					$kills = 0;
				}
			}
		}
	}
	
	private function rowCount($gid) {
		$row = database::getInstance()->singleRow(
			"SELECT COUNT(id) AS num FROM kills WHERE gid=\"$gid\"");
		return $row[num];
	}
	
	private function getPlayerStreak($pid, $type) {
		$row = database::getInstance()->singleRow(
			"SELECT streak FROM streaks WHERE playerID=\"$pid\" AND type=\"$type\"");
		return $row[streak];
	}	
}

?>
