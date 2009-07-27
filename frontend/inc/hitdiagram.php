<?php
/*
	**************************************************************
	* codalyzer
	* -  Hitdiagram
	**************************************************************
*/
if(strlen($_GET['h']) > 0){
    // validates input from $_GET
    $getid = $db->sqlQuote($_GET['h']);
    $sql = "SELECT id FROM profiles where id='$getid' LIMIT 1";  
    $data = $db->singleRow ($sql);
    
    if(is_numeric($data['id'])){        
        $uid = $db->sqlQuote($data['id']);
        
        // a list of all weapons this player has used
        $query = "SELECT mother, (SELECT full FROM weapons WHERE id=w.mother) as weaponfull FROM weapons as w, kills WHERE killerID='$uid' 
                  AND corpseID != '$uid' AND w.name=kills.weapon GROUP BY mother";

        $result = $db->sqlResult($query);
        
        // prints out the form
        echo '<form name="weaponselector" id="weaponselector">';
        echo '<select name="weapon" id="weapon"><option value="0">All weapons</option>';
        while ($row = mysql_fetch_assoc($result)) {		
            echo '<option value="'. $row['mother'] .'">' . $row['weaponfull'] . '</option>';
        }
        echo "</select></form>";

        // initial table values ( all weapons)
        $sql = "SELECT SUM(num) AS antall, location FROM (SELECT location, COUNT('') AS num FROM kills WHERE killerID = '$uid' GROUP BY location UNION 
                SELECT location, COUNT('')  AS num FROM hits WHERE hitmanID = '$uid' GROUP BY location) AS tabeller GROUP BY location ORDER BY antall DESC";        
    
        $hitpoint_result = mysql_query($sql);	

        // creates a query string for the graph, and prepares the data for the table
        while ($row = mysql_fetch_assoc($hitpoint_result)) {
            $hitdata[$row['location']] = $row['antall'];
            $hits .= "{$row['location']},{$row['antall']},";
            $total += $row['antall'];
        }				
                
        echo "<img src=\"". FRONTEND_URL ."/graphs/hitgraph.php?h=$uid&amp;t=". urlencode($hits) ."\" id=\"hitdiagram\">";
        
        echo "<table class=\"tiny\" id=\"hittable\"><tr><th>". $lang['th_location'] ."</th><th>". $lang['th_hits'] ."</th><th>". $lang['abb_percentage'] ."</th></tr>";
        $a = 0;
        foreach($hitdata as $location => $hits){	
            if($a == 10){
                break;
            }
            $percentage = round(($hits/($total+1))*100,2);
            echo "<tr><td>$location</td><td>$hits</td><td>$percentage%</td></tr>";
            $a++;
        }						
        echo "</table>";
        include FRONTEND_PATH . '/js/ajaxfetcher.php';
    }
}
?>