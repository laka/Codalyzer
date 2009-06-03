<?php
/*
	**************************************************************
	* codalyzer
	* -  Right column
	**************************************************************
*/

switch($_GET['mode']){
    case 'profile':
        include FRONTEND_PATH . '/inc/aliastable.php';
    
        echo "<h2>". $lang['tt_development'] ."</h2>";
        echo "<p>" . $lang['m_elodesc'] . "</p>";
        
        if(strlen($_GET['h']) > 0){
            $lookup = $db->sqlQuote($_GET['h']);
            if(!DISTINGUISH_BY_HASH){
                $query	= "SELECT id FROM profiles WHERE handle='$lookup'";
            } else {
                $query	= "SELECT id FROM profiles WHERE id='$lookup'";    
            }
            $row = $db->singleRow($query);
            if(strlen($row['id']) > 0){
                if(!DISTINGUISH_BY_HASH){
                    echo "<p><img src=\"" .FRONTEND_URL . "graphs/development.php?h=". urlencode($lookup) ."\"></p>";
                } else {
                    echo "<p><img src=\"" .FRONTEND_URL . "graphs/development.php?h=". $lookup ."\"></p>";
                }
            }
        }
        
        echo "<h2>". $lang['tt_hitdiagram'] ."</h2>";
        include FRONTEND_PATH . '/inc/hitdiagram.php';
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