<?php

class logfile {
	private $logfile;

	public function __construct($logfile) {
		$this->logfile = $logfile;		
	}

	public function returnArray() {
		return file($this->logfile);
	}

	public function getGameName() {
		return 'cod';
	}
}

?>
