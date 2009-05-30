<?php
require_once('toolbox.php');

class rating extends toolbox {
	public function eloRating($gid) {
		$result = database::getInstance()->sqlResult(
			"SELECT k_hash, c_hash FROM kills WHERE gid=\"$gid\" ORDER BY id");
		
		$scores = array();
		while($row = mysql_fetch_assoc($result)) {
			if(!isset($scores[$row['k_hash']])) {
				$scores[$row['k_hash']] = $this->getPlayerElo($row['k_hash'], 'return');
			}
			if(!isset($scores[$row['c_hash']])) {
				$scores[$row['c_hash']] = $this->getPlayerElo($row['c_hash'], 'return');
			}
			
			$change =  1/(1+pow(10,(($scores[$row['k_hash']] - $scores[$row['c_hash']])/400)));
			
			if($row['c_hash'] != $row['k_hash']) {
				$scores[$row['k_hash']] += $change;
				$scores[$row['c_hash']] -= $change;
			}
		}
		while(list($key, $value) = each($scores)) {
			$value = round($value, 2);
			database::getInstance()->sqlResult(
				"UPDATE players SET elo=\"$value\" WHERE hash=\"$key\" AND gid=\"$gid\"");
		}
	}
}

?>