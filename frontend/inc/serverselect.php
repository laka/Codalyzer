<?php
/*
	**************************************************************
	* codalyzer
	* -  Server selector
	**************************************************************
*/

$sql = "SELECT id, name, selected FROM servers";
$result = $db->sqlResult($sql);

if(mysql_num_rows($result) > 0){
    if(count($_GET) > 0 || strlen(QUERY_STRING_FIRST_ELEMENT) > 0){
        // Fetches query string parameters
        if(strlen(QUERY_STRING_FIRST_ELEMENT) > 0){
            $l_querystring[] = QUERY_STRING_FIRST_ELEMENT;
        }

        foreach($_GET as $key => $value){
            // fetches only variables that makes no bugs in the HTML
            if(ereg('^([0-9a-z]*)$', $key) && ereg('^[_a-z0-9]*$', $value)){
                $cand = (count($l_querystring) == 0) ? QUERY_STRING_FIRST_SEPARATOR : '&amp;';
                $cand .= $key . '=' . $value;
                // cumbersome method to prevent the query string from growing...
                if(substr($cand, 5) != substr(QUERY_STRING_FIRST_ELEMENT,1)){
                    $l_querystring[$i] = $cand;
                    $i++;    
                }
            }
        }
        $lqstring = @implode('', $l_querystring);    
    }
    
    
    echo '<form action="index.php'. $lqstring .'" method="post" name="serverselect">';    
    echo '<select name="server" onchange="document.serverselect.submit();">';
    
    while($line = mysql_fetch_assoc($result)){
        if($line['id'] == $_SESSION['server']){
            $selected = ' selected="selected"';
        } else {
            $selected = '';
        }
        echo '<option value="'. $line['id'] .'" '. $selected .'> '. $line['name'] . '</option>';
    }
    echo '</select>';
    echo '</form>';
}
?>
