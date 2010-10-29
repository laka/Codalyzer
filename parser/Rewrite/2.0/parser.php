<?php

class parser {
	public function __construct($logfile, $mode) {
		$this->re = new regex();
		$this->games = new games(); 
		$this->players = new players();
	
		if(!is_array($logfile)) {
			$logfile = file($logfile);
		}
		$regexes = $this->re->fetchRegexes();
		
		foreach($logfile as $nr => $line) {
			$gid = $this->games->lastGid();

			foreach($regexes as $id => $r) {
				if(preg_match("/$r/", $line, $matches)) {
					$m = $this->re->getRegexMethod($id);
					$this->$m['class']->$m['id']($matches, $gid);
					continue;
				}
			}
		}
	}
}

?>
