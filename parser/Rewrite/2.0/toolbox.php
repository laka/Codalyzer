<?php

class toolbox {
	public function inSeconds($ts) {
		$t = explode(':', $ts);
		return ($t[0]*60 + $t[1]);
	}

	public function reportError($type, $msg, $ts) {

	}

	public function chomp($array) {
		foreach($array as $s) {
			$s = trim($s);
			$clean[] = $s;
		}
		return $clean; 
	}
}

?>
