<?php
require_once('load.php');

$logfile  = new logfile('testlog');
$toolbox  = new toolbox();
$logarray = $logfile->returnArray();
$gamename = $logfile->getGameName();

$game = new game($gamename);

foreach ($logarray as $linenr => $line) {
	$gid = $toolbox->lastGid();
		
	if(preg_match($regexlib[$gamename]["newGame"]["all"], $line, $matches)) {
		$game->addNewGame($matches, $gid);
	}
	if(preg_match($regexlib[$gamename]["joinPlayer"]["all"], $line, $matches)) {
		$game->addNewPlayer($matches, $gid);
	}
}

?>
