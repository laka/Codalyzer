<?php
require_once('toolbox.php');

class games extends toolbox {
	public function __construct() {
		$this->db = database::getInstance();
	}
	public function addNewRound($matches, $gid) {
		$matches   = $this->chomp($matches);
		$lastGame  = $this->lastGameData($gid);
		$lastRound = $this->lastRoundData($gid);
		$ts        = $this->inSeconds($matches[1]);
		$type      = $matches[2];
		$map       = $matches[3];
		$new 	   = false;

		if(is_numeric($gid)) {
			if($lastGame['type'] != 'dm') {
				if($lastGame['type'] == $type && $lastGame['map'] == $map && $lastGame['stop'] == 0) {
					if($lastRound['round'] > 22 && $lastRound['duration'] > 150) {
						if($this->isTwoInOne($gid)) {
							$new = true;
						}
					}
					if(!$new) {
						database::getInstance()->sqlResult("UPDATE games SET stop=0 WHERE gid=\"$gid\"");
						$this->trackRounds($gid, $ts);
						return 0;
					}
				} 
			}
		} else {
			$result = database::getInstance()->sqlResult("SELECT id FROM games");
			if(mysql_num_rows($result)) {
				$this->reportError('init', 'unknow gid', $ts);
				return 0;
			} else {

			}
		}

		database::getInstance()->sqlResult("
			INSERT INTO games (start, type, map)
			VALUES(\"$ts\", \"$type\", \"$map\")");

		$this->checkGameFlags($gid);
		$this->trackRounds($gid, $ts);
	}

	public function checkGameFlags($gid) {
		$data = database::getInstance()->singleRow(
			"SELECT id, 
				(SELECT COUNT(*) FROM kills WHERE gid=a.id AND killerID!=corpseID) as kills, 
				(SELECT COUNT(*) FROM players WHERE gid=a.id) AS players,
				(SELECT COUNT(*) FROM rounds WHERE gid=a.id) AS rounds 
			FROM games AS a WHERE id=\"$gid\"");

		if($data[kills] <= 2 || $data[players] <= 1) {
			#$this->db->sqlResult("UPDATE games SET flag=\"e\" WHERE id=\"$gid\"");
			$this->dropGame($gid);
		} elseif($data[rounds] > 30) {
			$this->db->sqlResult("UPDATE games SET flag=\"d\" WHERE id=\"$gid\"");
		}
	}

	public function lastGameData($gid) {
		$row = database::getInstance()->singleRow(
			"SELECT type, map, stop FROM games WHERE id=\"$gid\"");

		return $row;
	}

	public function lastRoundData($gid) {
		$row = database::getInstance()->singleRow(
			"SELECT ts, round, kills, duration FROM rounds WHERE gid=\"$gid\" ORDER BY id DESC LIMIT 1");

		return $row;
	}

	public function isTwoInOne($gid) {
		$first = database::getInstance()->singleRow(
			"SELECT ts FROM players WHERE gid=\"$gid\" ORDER BY id ASC LIMIT 1");

		$last = database::getInstance()->singleRow(
			"SELECT ts FROM players WHERE gid=\"$gid\" ORDER BY id DESC LIMIT 1");

		if(($last['ts'] - $first['ts']) > 600) {
			$this->deletePlayer($first['ts'], $gid, 'b');
			return 1;
		} else {
			return 0;
		}
	}

	public function deletePlayer($ts, $gid, $cause) {
		if($cause == 'b') {
			$last = database::getInstance()->singleRow(
				"SELECT id, ts FROM players WHERE gid=\"$gid\" ORDER BY id DESC LIMIT 1");
		
			if(($last['ts'] - $ts) > 600) {
				database::getInstance()->sqlResult(
					"DELETE FROM players WHERE id=\"$last[id]\"");
				$this->deletePlayer($ts, $gid, 'b');
			}
		}
	}

	public function lastGid() {
		$row = database::getInstance()->singleRow(
			"SELECT id FROM games ORDER BY id DESC LIMIT 1");

		return $row['id'];
	}

	public function gameOver($matches, $gid) {
		$flag = '';
		$stop  = $this->inSeconds($matches[1]);
		$round = $this->currentRound($gid);
		$game    = $this->lastGameData($gid);
		$start = database::getInstance()->singleRow(
			"SELECT ts FROM rounds WHERE gid=\"$gid\" AND round=\"$round\"");

		$duration = $stop - $start['ts'];

		$kills = database::getInstance()->singleRow(
			"SELECT COUNT(id) AS c FROM kills WHERE gid=\"$gid\" AND round=\"$round\"");

		if($game['type'] == 'sd') {
			if($kills[c] == 0) {
				$flag = 'e';
			} elseif($kills[c] > 10) {
				$flag = 'k';
			} else {
				if($round < 3) {
					if($duration > 150) {
						$flag = 'w';	
					} elseif($kills[c] > 10 || $kills[c] <= 1) {
						$flag = 'w';
					}
				} elseif($round > 10 && $round < 15) {
					if($duration > 150 || $kills[c] == 0) {
						$flag = 's';
					}
				} elseif($round > 22) {
					if($duration > 150 || $kills[c] <= 1 || $kills[c] > 10) {
						$flag = 't';
					}
				}
			}
		}

		database::getInstance()->sqlResult(
			"UPDATE rounds SET duration=\"$duration\", kills=\"$kills[c]\", flag=\"$flag\" WHERE gid=\"$gid\" AND round=\"$round\"");
	}

	public function nextRound($gid) {
		$row = database::getInstance()->singleRow(
			"SELECT round FROM rounds WHERE gid=\"$gid\" ORDER BY id DESC LIMIT 1");
	
		return ++$row['round'];
	}

	public function currentRound($gid) {
		$row = database::getInstance()->singleRow(
			"SELECT round AS c FROM rounds WHERE gid=\"$gid\" ORDER BY id DESC LIMIT 1");
	
		return $row['c'];
	}

	public function trackRounds($gid, $ts) {
		$gid2 = $this->lastGid();	
		$round   = $this->nextRound($gid2);

		database::getInstance()->sqlResult("
			INSERT INTO rounds (gid, ts, round)
			VALUES(\"$gid2\", \"$ts\", \"$round\")");
	}

	public function dropRounds() {	
		$result = $this->db->sqlResult(
			"SELECT gid, round FROM rounds WHERE confirmed=\"0\"");

		while($row = mysql_fetch_assoc($result)) {
			$this->db->sqlResult("DELETE FROM kills WHERE gid=\"$row[gid]\" AND round=\"$row[round]\"");
			$this->db->sqlResult("DELETE FROM hits WHERE gid=\"$row[gid]\" AND round=\"$row[round]\"");
			$this->db->sqlResult("DELETE FROM rounds WHERE gid=\"$row[gid]\" AND round=\"$row[round]\"");
		} 								
	}

	public function dropGame($gid) {
		$this->db->sqlResult("DELETE FROM games WHERE id=\"$gid\"");
		$this->db->sqlResult("DELETE FROM players WHERE gid=\"$gid\"");
		$this->db->sqlResult("DELETE FROM kills WHERE gid=\"$gid\"");
		$this->db->sqlResult("DELETE FROM hits WHERE gid=\"$gid\"");
		$this->db->sqlResult("DELETE FROM rounds WHERE gid=\"$gid\"");
	}

	public function confirmRound($gid) {
		$this->db->sqlResult("DELETE FROM rounds WHERE gid=\"$gid\"");
	}
}

?>
