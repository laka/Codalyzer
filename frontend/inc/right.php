<?php
/*
	**************************************************************
	* codalyzer
	* -  Right column
	**************************************************************
*/

switch($_GET['mode']){
    case 'profile':
        echo "<h2>Development</h2>";
        echo "<p>" . $lang['m_elodesc'] . "</p>";
        echo "<p><img src=\"graphs/development.php?h=". urlencode($data['handle']) ."\"></p>";
    break;
    
    default:
        // best players table
        echo '<h2>'. NUM_BEST_PLAYERS .' best players</h2>';
        include FRONTEND_PATH . '/inc/bestplayers.php';
        
        // last games table
        echo '<h2>'. NUM_LAST_GAMES .' last games</h2>';
        include FRONTEND_PATH . '/inc/lastgames.php';
        
        // server status table
        echo '<h2>Server status</h2>';
        include FRONTEND_PATH . '/inc/serverstatus.php';
    break;
}
?>