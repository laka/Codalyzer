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
        echo '<h2>Select language</h2>';
        include FRONTEND_PATH . '/inc/langselect.php';
        echo '<h2>10 best players</h2>';
        include FRONTEND_PATH . '/inc/bestplayers.php';
        echo '<h2>10 last games</h2>';
        include FRONTEND_PATH . '/inc/lastgames.php';
        echo '<h2>Server status</h2>';
        include FRONTEND_PATH . '/inc/serverstatus.php';
    break;
}
?>