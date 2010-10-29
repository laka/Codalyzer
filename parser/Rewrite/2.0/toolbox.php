<?php

class toolbox {
	public function inSeconds($ts) {
		$t = explode(':', $ts);
		return ($t[0]*60 + $t[1]);
	}

	public function reportError($type, $msg) {

	}
}

?>
