<?php
require_once('toolbox.php');

class handler extends toolbox {
	public function addQuote($matches, $gid) {
		# Convert timestamp to seconds	
		$matches[1] = $this->ts2seconds($matches[1]); 
		
		database::getInstance()->sqlResult("INSERT INTO quotes (gid, ts, handle, quote) 
			VALUES(\"$gid\", \"$matches[1]\", \"$matches[2]\", \"$matches[3]\")");
	}
	public function addAction($matches, $gid) {
		# Convert timestamp to seconds	
		$matches[1] = $this->ts2seconds($matches[1]); 
		
		database::getInstance()->sqlResult("INSERT INTO actions (gid, ts, action, handle) 
			VALUES(\"$gid\", \"$matches[1]\", \"$matches[2]\", \"$matches[3]\")");
	}
}

?>