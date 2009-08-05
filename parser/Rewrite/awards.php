<?php
	
require_once('toolbox.php');

class awards extends toolbox {
	private $db;
	
	public function __construct() {
		$this->db = database::getInstance();
	}
	
	public function mostKillsInGame($gid) {
		$row = database::getInstance()->singleRow(
			"SELECT DISTINCT playerID AS winner,
				(SELECT COUNT(id) FROM kills WHERE killerID=p.playerID 
					AND corpseID!=p.playerID
					AND gid=\"$gid\") AS kills
				FROM players AS p 
				WHERE gid=\"$gid\" 
				ORDER BY kills DESC 
				LIMIT 1");
		
		database::getInstance()->sqlResult(
			"UPDATE games SET winner=\"$row[winner]\" WHERE id=\"$gid\"");
	}
}
	
?>