<?php
/*
	**************************************************************
	* codalyzer
	* - Administration section
	**************************************************************
*/

session_start();

include '../classes/config.php';
$config = new config('../../config.ini');

include '../classes/database.php';
include '../classes/authentication.php';

$db = database::getInstance();
$login = new authentication('index.php');
?>

<html>
    <head>
        <title>codalyzer Admin</title>
        <meta http-equiv="content-type" content="text/html; charset=utf-8">
        <link href="style.css" rel="stylesheet" type="text/css">

    </head>
    
    <body>
        <h1>codalyzer Admin</h1>
        <?php
            if($login->isAuthorized()){
                include 'inc/nav.php';
                switch ($_GET['p']){
                    case 'parser':
                        include 'modes/parser.php';
                        break;
                    case 'frontend':
                        include 'modes/frontend.php'; 
                        break;                        
                    case 'database':
                        include 'modes/database.php';
                        break;                        
                    case 'users':
                        include 'modes/users.php';    
                        break;                                                           
                    case 'main':
                    default:
                        include 'modes/main.php';        
                        break;                               
                }
            } else {
                $login->printForm('index.php');
            }
        ?>
    </body>
</html>