<?php

class init {
	public function __construct() {

	}

	public function parse($log) {
		$parser = new parser($log);
	}

	public function confirm() {
		$confirm = new confirm();
	}

	public function codalyze() {
		$loopwrap = new loopWrap();
	}

	public function postWork() {

	}
} 

?>
