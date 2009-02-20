<?php
if(@$login->isAuthorized()){
    echo "<h2>Database</h2>";
    $updated = FALSE;
    if($_POST['mysqlhost'] && $_POST['mysqluser'] && $_POST['mysqlpassword'] && $_POST['mysqldatabase']){
        // The form has been submitted..
        if (!get_magic_quotes_gpc()) {
            $_POST['mysqlhost'] = addslashes($_POST['mysqlhost']);
            $_POST['mysqluser'] = addslashes($_POST['mysqluser']);
            $_POST['mysqlpassword'] = addslashes($_POST['mysqlpassword']);
            $_POST['mysqldatabase'] = addslashes($_POST['mysqldatabase']);
        }
        if(!@mysql_connect($_POST['mysqlhost'], $_POST['mysqluser'], $_POST['mysqlpassword']) || !@mysql_select_db($_POST['mysqldatabase'])){
            echo "<strong>Error:</strong> Could not connect to the database with the submitted connection information, did not update config.";
        } else {
            if($config->iniSet('mysql_host', $_POST['mysqlhost'])
               && $config->iniSet('mysql_user', $_POST['mysqluser'])
               && $config->iniSet('mysql_pass', $_POST['mysqlpassword'])
               && $config->iniSet('mysql_db', $_POST['mysqldatabase']))
            {
                echo "<strong>Updated:</strong> The config file is now updated.";
                $updated = TRUE;                
            } else {
                echo "<strong>Error:</strong> Could not write to the config file.";
                $updated = FALSE;    
            }
        }  
    }
    if(!$updated){
    ?>

    <form action="?p=database" method="post">
        <p><strong>MySQL host</strong><br />
            <input type="text" name="mysqlhost" size="40" value="<?php echo MYSQL_HOST; ?>">
        </p>

        <p><strong>MySQL username</strong><br />
            <input type="text" name="mysqluser" size="40" value="<?php echo MYSQL_USER; ?>">
        </p>
        
        <p><strong>MySQL password</strong><br />
            <input type="password" name="mysqlpassword" size="40" value="<?php echo MYSQL_PASS; ?>">
        </p>    
        
        <p><strong>MySQL database</strong><br />
            <input type="text" name="mysqldatabase" size="40" value="<?php echo MYSQL_DB; ?>">
        </p>       
        
        <input type="submit" value="Update">
    </form>
    <?php 
    } // endif  
}
?>