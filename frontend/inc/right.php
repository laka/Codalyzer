<?php
/*
	**************************************************************
	* codalyzer
	* -  Right column
	**************************************************************
*/

switch($_GET['mode']){
    case 'profile':
        echo '<div class="tenmargin">';
        echo "<h2>Development</h2>";
        echo "<p>" . $lang['m_elodesc'] . "</p>";
        echo "<p><img src=\"graphs/development.php?h=". urlencode($data['handle']) ."\"></p>";
        echo '</div>';
    break;
    
    default:
        echo '<div class="tenmargin">';
        echo '<form action="index.php'. $querystring .'" method="post" name="selectors">';
        echo '<div class="rightform">';
        
        // server selector
        echo '<div class="rightformelement"><strong>Server</strong><br>';
        include FRONTEND_PATH . '/inc/serverselect.php';    
        echo '</div>';
        
        // language selector
        echo '<div class="rightformelement"><strong>Language</strong><br>';
        include FRONTEND_PATH . '/inc/langselect.php';  
        echo '</div>';
        
        echo '</div>';        
        echo '</form>';
        
        // best players table
        echo '<h2>'. NUM_BEST_PLAYERS .' best players</h2>';
        include FRONTEND_PATH . '/inc/bestplayers.php';
        
        // last games table
        echo '<h2>'. NUM_LAST_GAMES .' last games</h2>';
        include FRONTEND_PATH . '/inc/lastgames.php';
        
        // server status table
        echo '<h2>Server status</h2>';
        include FRONTEND_PATH . '/inc/serverstatus.php';
        echo '</div>';
    break;
}
?>