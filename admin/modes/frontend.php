<?php
if(@$login->isAuthorized()){
?>
    <h2>Frontend settings</h2>

    <?php
    if($_POST['language']){
        $config->iniSet('language', $_POST['language']);
        echo "<strong>Updated</strong> The config was successfully updated";
    } else {
    ?>

    <form action="?p=frontend" method="post">
        <p><strong>Path</strong> (Full path to /frontend, e.g /home/2/n/user/www/frontend)<br />
        <input type="text" size="40" name="path" value="<?php echo FRONTEND_PATH; ?>"></p>

        <p><strong>Default language</strong><br />
        <select name="language">
        <?php
        if ($handle = opendir(FRONTEND_PATH . '/lang')) {
            while (false !== ($file = readdir($handle))) {
                // just to make sure no thumbs.db etc gets interpreted as a new language.
                if(preg_match('/^lang_([a-z]*).php$/', $file, $matches)){
                    include FRONTEND_PATH . '/lang/' . $file;
                    if(strlen($lang['lang']) > 0){
                        if(LANGUAGE == $matches[1]){
                            $selected = 'selected="selected"';
                        }
                        echo "\t<option value=\"". $matches[1] ."\" $selected>" . $lang['lang'] . "</option>\n";
                    }
                    $lang['lang'] = $selected = '';
                }
            }
        closedir($handle);
        }
        ?>
        </select></p>
        
        <strong>Table settings</strong>
        <input type="submit" value="Submit">
    </form>
<?php 
    }
} //endif 
?>