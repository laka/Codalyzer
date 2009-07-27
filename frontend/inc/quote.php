<?php
/*
	**************************************************************
	* codalyzer
	* -  Random quote
	**************************************************************
*/
if(is_object($db)){	
	do{
		$sql = 'SELECT handle, quote FROM quotes, profiles WHERE LENGTH(quote) > 10 AND profiles.id = playerID ORDER BY RAND() LIMIT 1';
		$row = database::getInstance()->singleRow($sql);	
	} while (ereg('QUICKMESSAGE', $row['quote'])); 
	
	echo "<div class=\"quote\">" . $row['quote'] . "</div>";
	echo "<div class=\"quoted\">" . $row['handle'] . "</div>";		
}	
?>	