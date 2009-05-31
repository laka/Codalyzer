<?php
/*
	**************************************************************
	* codalyzer
	* -  List of a players aliases
	**************************************************************
*/
if(strlen($_GET['h']) > 0 && DISTINGUISH_BY_HASH){
    // validates input from $_GET
    $getid = $db->sqlQuote($_GET['h']);
    $sql = "SELECT hash FROM profiles where id='$getid' LIMIT 1";  
    $data = $db->singleRow ($sql);
    
    if(strlen($data['hash']) > 0){        
        $hash = $db->sqlQuote($data['hash']);
        $sql = "SELECT alias FROM alias WHERE owner='$hash'";
        $result = $db->sqlResult($sql);
        if(mysql_num_rows($result) > 0){
            echo '<table class="'. CLASS_ALIASES .'"><tr><th>'. $lang['tt_aliases'] .'</th></th>';
            while($row = mysql_fetch_assoc($result)){
                echo '<tr><td>'. $row['alias'] .'</td></tr>';
            }
            echo '</table>';
        }
    }
}
?>