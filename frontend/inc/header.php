<?php
/*
	**************************************************************
	* codalyzer
	* -  Header file
	**************************************************************
*/

include FRONTEND_PATH . '/classes/database.php';	
include FRONTEND_PATH . '/classes/orderedtable.php';
include FRONTEND_PATH . '/lang/lang_' . strtolower(LANGUAGE) . '.php';
$db = database::getInstance();

?>