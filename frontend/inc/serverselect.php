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

    echo '<select name="server" onchange="document.selectors.submit();">';
    
    while($line = mysql_fetch_assoc($result)){
        if($line['id'] == $_SESSION['server']){
            $selected = ' selected="selected"';
        } else {
            $selected = '';
        }
        echo '<option value="'. $line['id'] .'" '. $selected .'> '. $line['name'] . '</option>';
    }
    echo '</select>';
}
?>
