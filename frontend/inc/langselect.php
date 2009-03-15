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
            if(strlen($matches[1]) > 0){
                if((LANGUAGE == $matches[1] && !isset($_SESSION['language'])) || $_SESSION['language'] == $matches[1]){
                    $selected = 'selected="selected"';
                }
                echo "\t<option value=\"". $matches[1] ."\" $selected>" . ucfirst($matches[1]) . "</option>\n";
            }
            $selected = '';
        }
    }
closedir($handle);
}
echo '</select></form>';
?>
