<?php

class toolbox {
	private $db;

	public function __construct() {
		$this->db = database::getInstance();
	}	
	public function gameData($data, $gid) {
		$row = database::getInstance()->singleRow("SELECT $data FROM games WHERE id=\"$gid\"");
		return $row[$data];
	}
	public function ts2seconds($ts) {
		$t = explode(':', $ts);
		return ($t[0]*60 + $t[1]); 
	}
	public function getVersion($game) {
		$versions = array(
			'call of duty' => 10,
			'cod:united offensive' => 15,
			'call of duty 4' => 40,
			'Call of Duty: World at War' => 50,
		);
		return $versions[strtolower($game)];
	}
	public function lastGid() {
		$row = database::getInstance()->singleRow("SELECT id FROM games ORDER BY id DESC LIMIT 1");
		return $row['id'];
	}
	public function playerInGame($hash, $handle, $gid) {
		$result = database::getInstance()->sqlResult("SELECT id FROM players WHERE handle=\"$handle\" AND gid=\"$gid\"");
		return mysql_num_rows($result);
	}
	public function sanitizeString($input) {
		$input = preg_replace('//','',$input);
		$input = preg_replace('//','',$input);
		$input = preg_replace('/\s+$/','',$input);
		$input = preg_replace('/^\s+/','',$input);
		$input = preg_replace('/QUICKMESSAGE_.*/','',$input);			
	}
}

?>
