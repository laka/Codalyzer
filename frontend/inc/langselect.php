<?php
/*
	**************************************************************
	* codalyzer
	* -  Language selector
	**************************************************************
*/

echo '<form action="index.php" method="post" name="langSelect"><select name="language" onchange="document.langSelect.submit();">';
if ($handle = opendir(FRONTEND_PATH . '/lang')) {
    while (false !== ($file = readdir($handle))) {
        // just to make sure no thumbs.db etc gets interpreted as a new language.
        if(preg_match('/^lang_([a-z]*).php$/', $file, $matches)){
            include FRONTEND_PATH . '/lang/' . $file;
            if(strlen($lang['lang']) > 0){
                if((LANGUAGE == $matches[1] && !isset($_SESSION['language'])) || $_SESSION['language'] == $matches[1]){
                    $selected = 'selected="selected"';
                }
                echo "\t<option value=\"". $matches[1] ."\" $selected>" . $lang['lang'] . "</option>\n";
            }
            $lang['lang'] = $selected = '';
        }
    }
closedir($handle);
}
echo '</select></form>';
?>
