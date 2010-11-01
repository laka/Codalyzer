<?php
if(@$login->isAuthorized()){

    $newgames_sql = "SELECT '' FROM games WHERE parsed=0";
    if(mysql_num_rows($db->sqlResult($sql)) > 0){
        echo "UPARSEDE GAMES!!!";
    }
}
?>