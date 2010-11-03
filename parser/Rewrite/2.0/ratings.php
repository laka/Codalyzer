<?php

class ratings {
	public function __construct() {
		$this->db = database::getInstance();
	}
	public function matchRank($gid) {
		$sql = "SELECT playerID, handle,
       			(SELECT COUNT('') FROM kills WHERE killerID=p.playerID AND gid=p.gid AND killerID != corpseID) AS kills,
       			(SELECT COUNT('') FROM kills WHERE corpseID=p.playerID AND gid=p.gid) AS deaths
       			FROM players as p WHERE gid=$gid";

		$mostkills = 0;
		$bestkd = 0;

		$result = $this->db->sqlResult($sql) or die(mysql_error());

		// finner max k/d og max kills
		while($row = mysql_fetch_assoc($result)){
		    if($row['kills'] > $mostkills){
        		$mostkills = $row['kills'];
		    }
		    if($row['kills']/($row['deaths']+1) > $bestkd){
        		$bestkd = $row['kills']/($row['deaths']+1);
		    }
		}

		mysql_data_seek ($result , 0);

		// regner ut hvor mye hver spiller har i forhold til max...
		while($row = mysql_fetch_assoc($result)){
		    $kdratio[$row['playerID']] = ($row['kills']/($row['deaths']+1)) / (1 + $bestkd);
		    $killsratio[$row['playerID']] = log($row['kills'] + 1)/log($mostkills + 1.01);
		}

		foreach($kdratio as $player=>$ratio){
		   	$terningkast = round(3*$ratio + 3*$killsratio[$player],2);
			$this->db->sqlResult("UPDATE players SET matchrank=\"$terningkast\" WHERE playerID=\"$player\" AND gid=\"$gid\"");
            
            // oppdaterer også gjennomsnittsmatchranken hans
            $ranks_sql = "SELECT matchrank FROM players WHERE playerID =$player ORDER BY gid DESC LIMIT 0, 15";
            $ranks_result = $this->db->sqlResult($ranks_sql);
            $den = 0;
            $coeff = 15;
            $sum = 0;
            while($ranks_row = mysql_fetch_assoc($ranks_result)){
                $sum += $coeff * $ranks_row['matchrank'];
                $den += $coeff;
                $coeff--;
            }
            $avg = $sum/$den;
 			$this->db->sqlResult("UPDATE profiles SET matchrank='$avg' WHERE id='$player'");  
		}
	}
}


?>
