<?php

class parser {
	public function __construct($logfile, $mode) {
		$this->re = new regex();
	
		if(!is_array($logfile)) {
			$logfile = file($logfile);
		}
		$regexes = $this->re->fetchRegexes();
		
		foreach($logfile as $nr => $line) {
			foreach($regexes as $id => $r) {
				if(preg_match($r, $line, $matches)) {
					$m = $this->re->getRegexMethod($id);
					$this->$m($matches);
				}
			}
		}
	}
}

?>