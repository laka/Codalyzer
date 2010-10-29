<?php

class database {
	// Hold an instance of the class
	private static $instance;
	private $connection;
	
	// Class constructor prevents direct creation of object
	private function __construct() {
		require_once('secrets.php');
		$this->connection = mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS) 
			or die ('Could not connect to database ');

		mysql_select_db(MYSQL_DB, $this->connection);
		mysql_set_charset('utf8', $this->connection);
	}
	
	// The Singleton method
	public static function getInstance() {
		if(!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c;
		}
		return self::$instance;
	}
	
	// Escape queries accordingly
	public function sqlQuote($value) {
		if(get_magic_quotes_gpc()) {
			$value = stripslashes($value);
		}
		if(function_exists("mysql_real_escape_string")) {
			$value = mysql_real_escape_string($value);
		} else {
			$value = addslashes($value);
		}
		return $value;
	}
	
	// Return query result
	public function sqlResult($sql) {
		return mysql_query($sql);
	}
	
	// Return single row
	public function singleRow($sql) {
		if($result = self::sqlResult($sql)) {
			return mysql_fetch_assoc($result);
		} else {
			return array();
		}
	}
}

?>
