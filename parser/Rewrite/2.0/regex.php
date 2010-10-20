<?php

class regex {
	// Class constructor
	public function __construct() {
		require_once('database.php');
		$this->db = database::getInstance();
	}
	
	// Return all regexes within given site
	public function fetchRegexes() {
		$res = $this->db->sqlResult("SELECT id, regex FROM regexes");
		if(!$res) { 
			die('Invalid query: ' . mysql_error());
		}
		while($row = mysql_fetch_assoc($res)) {
			$regex[$row['id']] = $row['regex'];
		}
		return $regex;
	}
	
	// Return function name for given regex
	public function getRegexMethod($regex) {
		$row = $this->db->singleRow("SELECT ident FROM regexes WHERE id=\"$regex\"");
		return $row['ident'];
	}
	
	// Add new regex 
	public function addRegex($regex) {
	
	}
	
	// Edit regex
	public function editRegex($regex) {
	
	}
	
	// Delete regex
	public function dropRegex($regex) {
	
	}
	
	// List all regexes within given scope
	public function listRegex($scope) {
	
	}
}

?>

