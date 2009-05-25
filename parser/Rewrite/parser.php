<?php
require_once('load.php');

$logfile  = new logfile('testlog');
$toolbox  = new toolbox();
$logarray = $logfile->returnArray();
$gamename = $logfile->getGameName();

$game = new game($gamename);
$damage = new damage();
$limit = 'all';
$toolbox->truncateTables($limit);

foreach ($logarray as $linenr => $line) {
	# Fetch last game id
	$gid = $toolbox->lastGid();
	
	# Strip trailing chars
	$line = $toolbox->sanitizeString($line);
	
	if(preg_match($regexlib[$gamename]["newGame"]["all"], $line, $matches)) {
		$game->addNewGame($matches, $gid);
	}
	elseif(preg_match($regexlib[$gamename]["joinPlayer"]["all"], $line, $matches)) {
		$game->addNewPlayer($matches, $gid);
	}
	elseif(preg_match($regexlib[$gamename]["damageHit"]["all"], $line, $matches)) {
		$damage->addHit($matches, $gid);
	}
	elseif(preg_match($regexlib[$gamename]["kills"]["all"], $line, $matches)) {
		$damage->addKill($matches, $gid);
	}
}

?>
