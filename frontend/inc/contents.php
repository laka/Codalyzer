<?php
/*
	**************************************************************
	* codalyzer
	* -  Main contents
	**************************************************************
*/

$dir = PATH . '/modes/';
switch ($_GET['mode']) {
    case 'profile':
        include_once $dir.'profile.php';
        break;
    case 'weapons':
        include_once $dir.'weapons.php';
        break;
    case 'players':
        include_once $dir.'players.php';
        break;
    case 'awards':
        include_once $dir.'awards.php';
        break;
    case 'rounds':
        include_once $dir.'rounds.php';
        break;
    case 'maps':
        include_once $dir.'maps.php';
        break;
    case 'modes':
        include_once $dir.'modes.php';
        break;
    case 'todo':
        include_once $dir.'todo.php';
        break;			
    case 'weapon':
        include_once $dir.'weapon.php';
        break;			
    case 'map':
        include_once $dir.'map.php';
        break;			
    case 'editprofile':
        include_once $dir.'editprofile.php';
        break;		
    case 'compare':
        include_once $dir.'compare.php';
        break;		
    case 'todo':
        include_once $dir.'todo.php';
        break;		
    case 'award':
        include_once $dir.'award.php';
        break;							
    default:
        include_once $dir.'single.php';
    }
?>	
