<?php
/*
	**************************************************************
	* codalyzer
	* -  Server status table
	**************************************************************
*/
    
if(is_object($db) && class_exists(orderedtable)){
    $query 	= "SELECT status, map FROM server";
    $row = database::getInstance()->singleRow($query);

    if($row['status'] == 'Online'){
        echo '<p><img src="img/online.gif" alt="online"> ' . $lang['m_onlineat'] .' <a href="'. URL_BASE .'mode=map&m='.$row['map'].'">'.$row['map'].'</a></p>';
        
        $detailsSQL = "SELECT id, handle, score, ping FROM active";
        $serverstatus->setColumndata(array('ID'     => array (array('id' => 1), $lang['th_id'], "10%"),
                                     'handle'       => array (array('handle' => 1), $lang['th_player'], "50%", '', URL_BASE . 'mode=map&m='),
                                     'score'        => array (array('score' => 1), $lang['th_score'], "20%"),
                                     'ping'         => array (array('ping' => 1), $lang['th_ping'], "20%")	
                                    ));	        
        $serverstatus = new orderedtable($detailsSQL);
        $serverstatus->setOrderBy('score');
        $serverstatus->setClass( CLASS_SERVERSTATUS );
        $serverstatus->setLimit( NUM_ACTIVE_PLAYERS );         
        $serverstatus->printTable();
    } else {
		echo '<p><img src="img/offline.gif" alt="offline"> ' . $lang['m_offline'] . '</p>';
	}
}	
?>