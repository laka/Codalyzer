<?php

class parser {
	public function __construct($gamename) {
		$this->game	   = new game($gamename);
		$this->toolbox = new toolbox();
		$this->handler = new handler();
		$this->damage  = new damage();
	}
	
	public function parse($logarray, $regexlib, $gamename) {
		foreach ($logarray as $linenr => $line) {
	
			# Fetch last game id
			$gid = $this->toolbox->lastGid();
	
			# Strip trailing chars
			$line = $this->toolbox->sanitizeString($line);
	
			/* Add new games
			--------------------------------------------------------------------------------------------------------*/
			if(preg_match($regexlib[$gamename]["newGame"]["all"], $line, $matches)) {
				$this->game->addNewGame($matches, $gid);
			}
			/* Add new players
			--------------------------------------------------------------------------------------------------------*/
			elseif(preg_match($regexlib[$gamename]["joinPlayer"]["all"], $line, $matches)) {
				$this->game->addNewPlayer($matches, $gid);
			}
			/* Add join team
			--------------------------------------------------------------------------------------------------------*/
			elseif(preg_match($regexlib[$gamename]["joinTeam"]["all"], $line, $matches)) {
				$this->game->addTeamMember($matches, $gid);
			}
			/* Add new hits
			--------------------------------------------------------------------------------------------------------*/
			elseif(preg_match($regexlib[$gamename]["damageHit"]["all"], $line, $matches)) {
				$this->damage->addHit($matches, $gid);
			}
			/* Add new kills
			--------------------------------------------------------------------------------------------------------*/
			elseif(preg_match($regexlib[$gamename]["kills"]["all"], $line, $matches)) {
				$this->damage->addKill($matches, $gid);
			}
			/* Add new quotes
			--------------------------------------------------------------------------------------------------------*/
			elseif(preg_match($regexlib[$gamename]["quotes"]["all"], $line, $matches)) {
				$this->handler->addQuote($matches, $gid);
			}
			/* Add new actions
			--------------------------------------------------------------------------------------------------------*/
			elseif(preg_match($regexlib[$gamename]["actions"]['cod40'], $line, $matches)) {
				$this->handler->addPlayerAction($matches, $gid);
			}
			/* Add team score
			--------------------------------------------------------------------------------------------------------*/
			elseif(preg_match($regexlib[$gamename]["teamScore"]['all'], $line, $matches)) {
				$this->handler->addTeamScore($matches, $gid);
			}
			/* Add game stop time
			--------------------------------------------------------------------------------------------------------*/
			elseif(preg_match($regexlib[$gamename]["gameStopTime"]['all'], $line, $matches)) {
				$this->handler->addGameStopTime($matches, $gid);
			}
			/* Add confirmed shutdown of a game
			--------------------------------------------------------------------------------------------------------*/
			elseif(preg_match($regexlib[$gamename]["exitLevel"]['all'], $line, $matches)) {
				$this->handler->addExitGame($gid);
			}
			/* Add game round count
			--------------------------------------------------------------------------------------------------------*/
			elseif(preg_match($regexlib[$gamename]["roundStart"]['all'], $line, $matches)) {
				$this->handler->addRoundCount($matches, $gid);
			}
		}
	}
}
		
?>
	