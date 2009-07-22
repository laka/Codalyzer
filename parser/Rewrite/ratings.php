<?php

require_once('toolbox.php');

class rating extends toolbox {
	public function eloRating($gid) {
		$result = database::getInstance()->sqlResult(
			"SELECT killerID, corpseID FROM kills WHERE gid=\"$gid\" ORDER BY id");
		
		$scores = array();
		while($row = mysql_fetch_assoc($result)) {
			if(!isset($scores[$row['killerID']])) {
				$scores[$row['killerID']] = $this->getPlayerElo($row['killerID'], 'return');
			}
			if(!isset($scores[$row['corpseID']])) {
				$scores[$row['corpseID']] = $this->getPlayerElo($row['corpseID'], 'return');
			}
			
			$change =  1/(1+pow(10,(($scores[$row['killerID']] - $scores[$row['corpseID']])/400)));
			
			if($row['corpseID'] != $row['killerID']) {
				$scores[$row['killerID']] += $change;
				$scores[$row['corpseID']] -= $change;
			}
		}
		while(list($key, $value) = each($scores)) {
			$key = $this->getPlayerHash($key);
			$value = round($value, 2);
			database::getInstance()->sqlResult(
				"UPDATE players SET elo=\"$value\" WHERE hash=\"$key\" AND gid=\"$gid\"");
		}
	}
}

?>