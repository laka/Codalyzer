<?php
/*
	**************************************************************
	* codalyzer
	* -  Header file
	**************************************************************
*/

// handles language changes
if($_POST['language']){
    $_POST['language'] = trim(addslashes($_POST['language']));
    if(is_file(FRONTEND_PATH . '/lang/lang_' . strtolower($_POST['language']) . '.php')){
        $_SESSION['language'] = strtolower($_POST['language']);
    }
} 

// includes default language if a langauge session does not exist.
if(is_file(FRONTEND_PATH . '/lang/lang_' . $_SESSION['language'] . '.php')){
    include FRONTEND_PATH . '/lang/lang_' . $_SESSION['language'] . '.php';
} else {
    include FRONTEND_PATH . '/lang/lang_' . strtolower(LANGUAGE) . '.php';
}

include FRONTEND_PATH . '/classes/database.php';	
include FRONTEND_PATH . '/classes/orderedtable.php';
$db = database::getInstance();

?>