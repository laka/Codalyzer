<?php

class kills {
	public function addNewKill($matches, $gid) {
		$ts = inSeconds($matches[1]);
		$corpse = $this->getPlayerID(substr($matches[2], -8));
		$killer = $this->getPlayerID(substr($matches[5], -8));
		$weapon = $this->getWeaponAbbr($matches[8], $matches[10]);

		if(!$this->playerInGame($corpse, $gid)) {
			$this->addNewAlias(								
	}
}

?>
