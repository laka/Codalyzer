<?php
/*
	**************************************************************
	* codalyzer
	* -  Header file
	**************************************************************
*/

$db = database::getInstance();

// fetches config data from the database
$sql = "SELECT * FROM config";
$result = $db->sqlResult($sql);
while($line = mysql_fetch_assoc($result)){
    define(strtoupper($line['key']), $line['value']);
}

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
?>