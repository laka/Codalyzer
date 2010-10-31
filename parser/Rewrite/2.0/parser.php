<?php

class parser {
	public function __construct($logfile, $mode) {
		$this->games   = new games(); 
		$this->players = new players();
		$this->kills   = new kills();
		$this->damages  = new damages();
		$this->regex   = new regex();
	
		if(!is_array($logfile)) {
			$logfile = file($logfile);
		}
		$regexes = $this->regex->fetchRegexes();
		
		foreach($logfile as $nr => $line) {
			$gid = $this->games->lastGid();

			foreach($regexes as $id => $r) {
				if(preg_match("/$r/", $line, $matches)) {
					$m = $this->regex->getRegexMethod($id);
					$this->$m['class']->$m['id']($matches, $gid);
					continue;
				}
			}
		}
	}
}

?>
