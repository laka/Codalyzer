<?php
if(@$login->isAuthorized()){
    echo "<h2>System configuration</h2>";
    $updated = FALSE;
    if($_POST['mysqlhost'] && $_POST['mysqluser'] && $_POST['mysqlpassword'] && $_POST['mysqldatabase']
		&& $_POST['ftphost'] && $_POST['ftpuser'] && $_POST['ftppass'] && $_POST['ftppath'] && $_POST['logfile']) {
        // The form has been submitted..
        $mysqlhost = addslashes($_POST['mysqlhost']);
        $mysqluser = addslashes($_POST['mysqluser']);
        $mysqlpassword = addslashes($_POST['mysqlpassword']);
        $mysqldatabase = addslashes($_POST['mysqldatabase']);
		$ftphost = addslashes($_POST['ftphost']);
		$ftpuser = addslashes($_POST['ftpuser']);
		$ftppass = addslashes($_POST['ftppass']);
		$ftppath = addslashes($_POST['ftppath']);
		$logfile = addslashes($_POST['logfile']);


        if(!@mysql_connect($mysqlhost, $mysqluser, $mysqlpassword) || !@mysql_select_db($mysqldatabase)){
            echo "<strong>Error:</strong> Could not connect to the database with the submitted connection information, did not update config.";
        } else {
            $filename = '../config.php';
            $handle = fopen($filename, 'w');
            $configfile = "<?php\ndefine('MYSQL_HOST', '$mysqlhost');\ndefine('MYSQL_USER', '$mysqluser');\ndefine('MYSQL_PASS', '$mysqlpassword');\ndefine('MYSQL_DB', '$mysqldatabase');\ndefine('FTP_HOST', '$ftphost');\ndefine('FTP_USER', '$ftpuser');\ndefine('FTP_PASS', '$ftppass');\ndefine('FTP_PATH', '$ftppath');\ndefine('LOGFILE', '$logfile');\n?>\n";
            if (fwrite($handle,$configfile)) {
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

    <form action="?p=config" method="post">
        <h3>MySQL connection</h3>
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
        

        <h3>FTP settings</h3>
		 <p><strong>FTP host</strong><br />
            <input type="text" name="ftphost" size="40" value="<?php echo FTP_HOST; ?>">
        </p>

        <p><strong>FTP username</strong><br />
            <input type="text" name="ftpuser" size="40" value="<?php echo FTP_USER; ?>">
        </p>
        
        <p><strong>FTP password</strong><br />
            <input type="password" name="ftppass" size="40" value="<?php echo FTP_PASS; ?>">
        </p>    
        
        <p><strong>FTP path</strong><br />
            <input type="text" name="ftppath" size="40" value="<?php echo FTP_PATH; ?>">
        </p>       
        

        <h3>Logfile settings</h3>
		 <p><strong>Logfile</strong><br />
            <input type="text" name="logfile" size="40" value="<?php echo LOGFILE; ?>">
        </p>


        <h3>Paths</h3>
        <p><strong>Frontend path</strong> (Full path to /frontend, e.g /home/2/n/user/www/frontend)<br />
        <input type="text" size="40" name="path" value="<?php echo FRONTEND_PATH; ?>"></p>
        
        <p><strong>Base path</strong> (Full path to /frontend, e.g /home/2/n/user/www/frontend)<br />
        <input type="text" size="40" name="path" value="<?php echo BASE_PATH; ?>"></p>

        <input type="submit" value="Submit">
        <input type="submit" value="Update">
    </form>
    <?php 
    } // endif  
}
?>
