<?php
/*
	**************************************************************
	* codalyzer
	* - Log out
	**************************************************************
*/

session_start();

include '../../classes/config.php';
$config = new config('../../../config.ini');

include '../../classes/database.php';
include '../../classes/authentication.php';

$db = database::getInstance();
$login = new authentication('../index.php');

$login->logOut();

?>