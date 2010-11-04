<?php
/*
	**************************************************************
	* codalyzer
	* - Log out
	**************************************************************
*/

session_start();

include '../../config.php';
include '../../shared/database.php';
include '../../shared/authentication.php';

$db = database::getInstance();
$login = new authentication('../index.php');

$login->logOut();

?>