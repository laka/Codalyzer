<?php
require_once('codalyzer.php');

$logfile = new logfile('testlog');
$logarray = $logfile->returnArray();
$gamename = $logfile->getGameName();

$game 	 = new game($gamename);
$toolbox = new toolbox();
$damage  = new damage();
$handler = new handler();

$toolbox->truncateTables('all');

foreach ($logarray as $linenr => $line) {
	
	# Fetch last game id
	$gid = $toolbox->lastGid();
	
	# Strip trailing chars
	$line = $toolbox->sanitizeString($line);
	
	/* Add new games
	--------------------------------------------------------------------------------------------------------*/
	if(preg_match($regexlib[$gamename]["newGame"]["all"], $line, $matches)) {
		$game->addNewGame($matches, $gid);
	}
	/* Add new players
	--------------------------------------------------------------------------------------------------------*/
	elseif(preg_match($regexlib[$gamename]["joinPlayer"]["all"], $line, $matches)) {
		$game->addNewPlayer($matches, $gid);
	}
	/* Add join team
	--------------------------------------------------------------------------------------------------------*/
	elseif(preg_match($regexlib[$gamename]["joinTeam"]["all"], $line, $matches)) {
		$game->addTeamMember($matches, $gid);
	}
	/* Add new hits
	--------------------------------------------------------------------------------------------------------*/
	elseif(preg_match($regexlib[$gamename]["damageHit"]["all"], $line, $matches)) {
		$damage->addHit($matches, $gid);
	}
	/* Add new kills
	--------------------------------------------------------------------------------------------------------*/
	elseif(preg_match($regexlib[$gamename]["kills"]["all"], $line, $matches)) {
		$damage->addKill($matches, $gid);
	}
	/* Add new quotes
	--------------------------------------------------------------------------------------------------------*/
	elseif(preg_match($regexlib[$gamename]["quotes"]["all"], $line, $matches)) {
		$handler->addQuote($matches, $gid);
	}
	/* Add new actions
	--------------------------------------------------------------------------------------------------------*/
	elseif(preg_match($regexlib[$gamename]["actions"]['cod40'], $line, $matches)) {
		$handler->addPlayerAction($matches, $gid);
	}
	/* Add team score
	--------------------------------------------------------------------------------------------------------*/
	elseif(preg_match($regexlib[$gamename]["teamScore"]['all'], $line, $matches)) {
		$handler->addTeamScore($matches, $gid);
	}
	/* Add game stop time
	--------------------------------------------------------------------------------------------------------*/
	elseif(preg_match($regexlib[$gamename]["gameStopTime"]['all'], $line, $matches)) {
		$handler->addGameStopTime($matches, $gid);
	}
	/* Add confirmed shutdown of a game
	--------------------------------------------------------------------------------------------------------*/
	elseif(preg_match($regexlib[$gamename]["exitLevel"]['all'], $line, $matches)) {
		$handler->addExitGame($gid);
	}
	/* Add game round count
	--------------------------------------------------------------------------------------------------------*/
	elseif(preg_match($regexlib[$gamename]["roundStart"]['all'], $line, $matches)) {
		$handler->addRoundCount($matches, $gid);
	}
}

$loop = new loopwrap();
$loop->playersLoop();

?>
	