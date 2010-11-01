<?php
/*
	**************************************************************
	* codalyzer
	* - Log out
	**************************************************************
*/

session_start();

include '../../../config.php';
include '../../classes/database.php';
include '../../classes/authentication.php';

$db = database::getInstance();
$login = new authentication('../index.php');

$login->logOut();

?>