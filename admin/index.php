<?php
/*
	**************************************************************
	* codalyzer
	* - Administration section
	**************************************************************
*/

session_start();

include '../config.php';

include '../shared/authentication.php';
$login = new authentication('index.php');

// fetches config data from the database, we don't use the database-class here, since we aren't able to include it some times without knowing BASE_PATH.
$connection = @mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS) or die ('<strong>Error:</strong> Could not connect to the database.');
mysql_select_db(MYSQL_DB, $connection);

$sql = "SELECT * FROM config";
$result = mysql_query($sql) or die("<p><strong>Codalyzer error: </strong>Could not get config settings from MySQL server.</p>");
while($line = mysql_fetch_assoc($result)){
    define(strtoupper($line['ckey']), $line['value']);
}

// includes the database class
include BASE_PATH . '/shared/database.php';
$db = database::getInstance();

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
                    case 'regex':
                        include 'modes/regex-support.php';
                        break;                        
                    case 'users':
                        include 'modes/users.php';    
                        break;           
                    case 'editmatchinfo':
                        include 'modes/editmatchinfo.php';    
                        break;  
                    case 'editmatchdata':
                        include 'modes/editmatchdata.php';    
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
