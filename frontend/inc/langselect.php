<?php
/*
	**************************************************************
	* codalyzer
	* -  Language selector
	**************************************************************
*/

// Fetches query string parameters
$i = 0;
foreach($_GET as $key => $value){
    // fetches only variables that makes no bugs in the HTML
    if(ereg('^([0-9a-z]*)$', $key) && ereg('^[_a-z0-9]*$', $value)){
        $querystring .= ($i == 0) ? '?' : '&amp;';
        $querystring .= $key . '=' . $value;
        $i++;
    }
}

echo '<form action="index.php'. $querystring .'" method="post" name="langSelect"><select name="language" onchange="document.langSelect.submit();">';

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
