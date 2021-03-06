<?php
/*
	**************************************************************
	* codalyzer
	* -  Basic SQL-functions
	**************************************************************
*/

class database
{
    // Hold an instance of the class
    private static $instance;
	private $connection;
    
    // A private constructor; prevents direct creation of object
    private function __construct() 
    {
		$this->connection = @mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS)
            or die ('<strong>Error:</strong> Could not connect to the database.');
		mysql_select_db(MYSQL_DB, $this->connection);
        mysql_set_charset('utf8', $this->connection); 
    }

    // The singleton method
    public static function getInstance() 
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }
        return self::$instance;
    }
    
	public function sqlQuote($value)
	{
		if(get_magic_quotes_gpc()){
			$value = stripslashes( $value );
		}
	    //check if this function exists
	    if(function_exists("mysql_real_escape_string")){
			$value = mysql_real_escape_string($value);
		} else {
			$value = addslashes($value);
		}
		return $value;
	}	
	
    public function sqlResult($sql)
    {
		return mysql_query($sql);
    }
    
    public function isPlayer($lookup)
    {
        if(strlen($lookup) > 0){
            $lookup = $this->sqlQuote($lookup);
            if(!DISTINGUISH_BY_HASH){
                $query = "SELECT id FROM profiles WHERE handle='$lookup'";
            } else {
                $query = "SELECT id FROM profiles WHERE id='$lookup'";
            }
            $row = $this->singleRow($query);
        }
		return $row['id'];
    }    
	
    public function singleRow($sql)
    {
        if($result = self::sqlResult($sql))
            return mysql_fetch_assoc($result);
        else 
            return array();
    }	
}

?>