<?php
require_once('load.php');

$logfile  = new logfile('testlog');
$logarray = $logfile->returnArray();
$gamename = $logfile->getGameName();

$game = new game($gamename);

foreach ($logarray as $linenr => $line) {
	if(preg_match($regexlib[$gamename]["newGame"]["all"], $line, $matches)) {
		$game->addNewGame($matches);

	}
	if(preg_match($regexlib[$gamename]["joinPlayer"]["all"], $line, $matches)) {

	}
}

?>
