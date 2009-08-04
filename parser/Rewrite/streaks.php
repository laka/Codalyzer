<?php
	
require_once('toolbox.php');

class streaks extends toolbox {
	private $db, $kills, $deaths;
	public function __construct() {
		$this->db = database::getInstance();
	}
	
	public function killDeathStreak($gid) {
		#echo "-- COLLECTING STREAKS IN GAME $gid\n";
		$pids = database::getInstance()->sqlResult(
			"SELECT DISTINCT playerID AS player FROM players WHERE gid=\"$gid\"");
		
		while($pid = mysql_fetch_assoc($pids)) {
			$kills = 0;
			$deaths = 0;
			$lines = 0;
			#$player = $this->getPlayerHandleByID($pid[player]);
			#echo "Checking kill streak for <$player>\n";
			$killz = database::getInstance()->sqlResult(
				"SELECT killerID AS killer, corpseID AS corpse FROM kills WHERE gid=\"$gid\"");
			
			while($players = mysql_fetch_assoc($killz)) {
				$lines++;
				$rows = $this->rowCount($gid);
				
				if($pid[player] == $players[killer] && $pid[player] != $players[corpse]) {
					$kills++;
					#echo "\tHe KILLED somebody, streak ended. Landed on $deaths\n";
					$this->setStreak($deaths, $gid, $pid[player], 'death');
					$deaths = 0;
				}
				if($pid[player] == $players[corpse]) {
					$deaths++;
					#echo "\tHe DIED, streak ended. Landed on $kills\n";
					$this->setStreak($kills, $gid, $pid[player], 'kill');
					$kills = 0;
				}
				if($lines == $rows) {
					#echo "\t\tGame over, streaks updated ($kills/$deaths, $gid, $pid[player])\n";
					$this->setStreak($kills, $gid, $pid[player], 'kill');
					$this->setStreak($deaths, $gid, $pid[player], 'death');
				}
			}
		}
	}
	
	private function setStreak($streak, $gid, $pid, $type) {
		$current = $this->getPlayerStreak($pid, $type);
		if(is_numeric($current)) {
			if($current < $streak) {
				database::getInstance()->sqlResult(
					"UPDATE streaks SET streak=\"$streak\", gid=\"$gid\" WHERE playerID=\"$pid\" AND type=\"$type\"");
			}
		} else {
			database::getInstance()->sqlResult(
				"INSERT INTO streaks (gid, type, playerID, streak) VALUES(\"$gid\", \"$type\", \"$pid\", \"$streak\")");
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
