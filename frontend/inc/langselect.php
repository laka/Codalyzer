<?php
/*
	**************************************************************
	* codalyzer
	* -  Language selector
	**************************************************************
*/

// Fetches query string parameters
if(count($_GET) > 0 || strlen(QUERY_STRING_FIRST_ELEMENT) > 0){
    if(strlen(QUERY_STRING_FIRST_ELEMENT) > 0){
        $s_querystring[] = QUERY_STRING_FIRST_ELEMENT;
    }

    foreach($_GET as $key => $value){
        $key = stripslashes($key);
        $value = stripslashes($value);
            
        $cand = (count($s_querystring) == 0) ? QUERY_STRING_FIRST_SEPARATOR : '&amp;';
        $cand .= urlencode($key) . '=' . urlencode($value);
        // cumbersome method to prevent the query string from growing...
        if(substr($cand, 5) != substr(QUERY_STRING_FIRST_ELEMENT,1)){
            $s_querystring[$i] = $cand;
            $i++;    
        }    
    }
    $sqstring = @implode('', $s_querystring);
}

echo '<form action="'. $sqstring .'" method="post" name="langselect">';    
echo '<select name="language" onchange="document.langselect.submit();">';

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
echo '</select>';
echo '</form>';
?>
