<?php
/*
	**************************************************************
	* codalyzer
	* -  Header file
	**************************************************************
*/

// fetches config data from the database, we don't use the database-class here, since we aren't able to include it some times without knowing FRONTEND_PATH.
$connection = @mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS) or die ('<strong>Error:</strong> Could not connect to the database.');
mysql_select_db(MYSQL_DB, $connection);

$sql = "SELECT * FROM config";
$result = mysql_query($sql);
while($line = mysql_fetch_assoc($result)){
    define(strtoupper($line['key']), $line['value']);
}

// includes the database class
include FRONTEND_PATH . '/classes/database.php';
$db = database::getInstance();

// includes the orderedtable class
include FRONTEND_PATH . '/classes/orderedtable.php';

// handles language changes
if($_POST['language']){
    $_POST['language'] = trim(addslashes($_POST['language']));
    if(is_file(FRONTEND_PATH . '/lang/lang_' . strtolower($_POST['language']) . '.php')){
        $_SESSION['language'] = strtolower($_POST['language']);
    }
} 
// handles server changes
if(is_numeric($_POST['server'])){
    $serverid = $_POST['server'];
    $sql = "SELECT COUNT('') as k FROM servers WHERE id='$serverid'";
    $result = $db->sqlResult($sql);
    if(mysql_num_rows($result) == 1){     
        $_SESSION['server'] = $serverid;
    }     
} elseif(!is_numeric($_SESSION['server'])) {
    $sql = "SELECT id, name, selected FROM servers WHERE selected=1 LIMIT 1";
    $result = $db->sqlResult($sql);
    if(mysql_num_rows($result) > 0){     
        while($line = mysql_fetch_assoc($result)){
            $_SESSION['server'] = $line['id'];
        }
    }    
}

// writes the part of the queries that chooses which server to take data from
$serverq .= "games.server = " . $_SESSION['server'];
define("SERVER_QUERY", '('.$serverq.')');

// includes default language if a langauge session does not exist.
if(is_file(FRONTEND_PATH . '/lang/lang_' . $_SESSION['language'] . '.php')){
    include FRONTEND_PATH . '/lang/lang_' . $_SESSION['language'] . '.php';
} else {
    include FRONTEND_PATH . '/lang/lang_' . strtolower(LANGUAGE) . '.php';
}

// defines a url base for all links
define("URL_BASE", QUERY_STRING_FIRST_ELEMENT . QUERY_STRING_FIRST_SEPARATOR);
?>