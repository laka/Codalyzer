<?php

class players {
	public function addPlayer($matches, $gid) {
		$handle = $matches[3];
		$ts = $this->inSeconds($matches[1]);

		if(strlen($matches[2]) > 8) {
			$hash = substr($matches[2], -8);
		} else {
			$this->newWarning('addPlayer', 'NO_HASH', $ts);
		}
		
		$this->makeProfile($hash, $handle);
		$playerID = $this->getPlayerID($hash);
		
		if(!$this->playerInGame($handle, $gid)) {
			database::getInstance()->sqlResult("
				INSERT INTO players (gid, ts, playerID, handle)
				VALUES(\"$gid\", \"$ts\", \"$playerID\", \"$handle\")
			");
		}
	}
	
	public function addTeam($matches, $gid) {
		$handle = $matches[1];
		$team = $matches[2];

		database::getInstance()->sqlResult("
			UPDATE players SET team=\"$team\" WHERE handle=\"$handle\" AND gid=\"$gid\"
		");
	}
	
	public function addAction($matches, $gid) {
		$ts = $this->inSeconds($matches[1]);
		$playerID = $this->getPlayerID($matches[3]);
		$action = $matches[2];		

		database::getInstance()->sqlResult("
			INSERT INTO actions (gid, ts, action, playerID)
			VALUES(\"$gid\", \"$ts\", \"$action\", \"$playerID\"
		");	
	}
	
	public function addClanTag($hash) {
		$playerID = $this->getPlayerID($hash);
		$handle = $this->getPlayerHandle($playerID);
		
		if(preg_match('/^\w+$/', $handle, $lone)) {
			$clan = 'none';
		} else {
			if(preg_match('/(\w+).*?(\w+)$/', $handle, $match)) {
				$clan = $match[1];
			} else {
				$clan = 'unknown';
			}
		}

		database::getInstance()->sqlResult("
			UPDATE profiles SET clan=\"$clan\" WHERE id=\"$playerID\"
		");
	}
	
	public function addQuote($matches, $gid) {
		$ts = $this->inSeconds($matches[1]);
		$hash = substr($matches[2], -8);
		$playerID = $this->getPlayerID($hash);
		$quote = $matches[4];
		
		database::getInstance()->sqlResult("
			INSERT INTO quotes (gid, ts, quote, playerID)
			VALUES(\"$gid\", \"$ts\", \"$quote\", \"$playerID\")
		");
	}
	
	public function addNewAlias($alias, $owner, $gid) {
		if(strlen($alias) > 0) {
			$alias = database::getInstance()->sqlQuote($alias);
			$exist = database::getInstance()->sqlResult("
				SELECT id FROM alias WHERE owner=\"$owner\" AND alias=\"$alias\"
			");

			if(mysql_num_rows($exist) == 0) {
				database::getInstance()->sqlResult("
					INSERT INTO alias (owner, alias)
					VALUES(\"$owner\", \"$alias\")
				");
			}
			if(is_numeric($gid)) {
				$row = database::getInstance()->singleRow("
					SELECT handle AS old_handle FROM players WHERE hash=\"$owner\" AND gid=\"$gid\"
				");
				
				database::getInstance()->sqlResult("
					UPDATE players SET handle=\"$alias\" WHERE hash=\"$owner\" AND gid=\"$gid\"
				");
				
				$this->addNewAlias($row[old_handle], $owner, '');
			}
		}		
	}
	
	public function getEveryAlias($hash) {
		$playerID = $this->getPlayerID($hash);
		$aliases = database::getInstance()->sqlResult("
			SELECT DISTINCT handle AS alias FROM players WHERE playerID=\"$playerID\" ORDER BY id
		");
		
		while($row = mysql_fetch_assoc($aliases)) {
			$this->addNewAlias($row['alias'], $hash, '');
		}
	}
	
	public function makeProfile($hash, $handle) {
		$exists = database::getInstance()->sqlResult("
			SELECT id FROM profiles WHERE hash=\"$hash\"
		");
		if(mysql_num_rows($exists)) {
			$this->setMostUsedHandle($hash);
		} else {
			database::getInstance()->sqlResult("
				INSERT INTO profiles (handle, hash)
				VALUES(\"$handle\", \"$hash\")
			");				
		}
	}
	
	public function addProfileData($hash) {
		$this->sumKills($hash);
		$this->sumDeaths($hash);
		$this->sumSuicides($hash);
		$this->sumGames($hash);
		$this->playerELO($hash);
		$this->addClanTag($hash);
	}
	
	public function sumKills($hash) {
		$playerID = $this->getPlayerIDByHash($hash);
		$row = database::getInstance()->singleRow("
			SELECT COUNT(*) AS sum FROM kills WHERE killerID=\"$playerID\" AND corpseID!=\"$playerID\"
		");
		database::getInstance()->sqlResult("
			UPDATE profiles SET kills=\"$row[sum]\" WHERE id=\"$playerID\"
		");
	}
	
	public function sumDeaths($hash) {
	
	}
	
	public function sumSuicides($hash) {
	
	}
	
	public function sumGames($hash) {
	
	}
	
	public function playerELO($hash) {
		
	}
	
	public function getPlayerID($hash) {
		
	}
	
	public function playerInGame($handle) {
	
	}
}

?>
