<?php
/*
	**************************************************************
	* codalyzer
	* -  Make resultlists
	**************************************************************
	
*/

class resultlist extends orderedtable {
	private $gamedata, $playerdata, $gid, $teams, $actions, $axisheader, $alliesheader, $query, $lang, $teamdata;

	public function __construct($gid) {	
        // gets language data from the outside...
        global $lang;
        $this->lang = $lang;
    
        // assigns a letter to each new table (static variable)
		$this->assignLetter ();
		if(is_numeric($gid)){
			$this->gid = $gid;
		}
		
		$sql = "SELECT * FROM games WHERE id={$this->gid}";
		$this->gamedata = database::getInstance()->singleRow ($sql);	
		$this->setClass('summary');	
		$this->setUrl(URL_BASE . "mode=single&amp;gid={$this->gid}");
		$this->setUrlVars(array('mode', 'gid'));
		$this->setTotalSum(1);

		switch($this->gamedata['type']){
			case 'dm':
				$this->teams 	= 0;
				$this->actions	= 0;
			break;
			case 'war':
				$this->teams 	= 1;
				$this->actions	= 0;
			break;	
			default:
				$this->teams 	= 1;
				$this->actions	= 1;
		}

        switch($this->gamedata['mods']){
            case 'pam4':
            case 'promod':
                $this->teamdata = 1;
            break;
            default:
                $this->teamdata = 0;
        }
	}

	private function setFunctiondata($team = 'axis'){
		$result = database::getInstance()->sqlResult($sql);
        // For modes with no teams
		if(!$this->teams){
            $this->query = "SELECT playerID, handle, IFNULL(elo,(SELECT elo FROM players WHERE playerID=p.playerID AND gid<{$this->gid} AND elo IS NOT NULL ORDER BY gid DESC LIMIT 1)) as elo,
                                    (SELECT COUNT('') FROM kills WHERE killerID=p.playerID AND gid=p.gid AND killerID != corpseID) AS kills,
                                    (SELECT COUNT('') FROM kills WHERE corpseID=p.playerID AND gid=p.gid AND killerID != corpseID) AS deaths,
                                    (SELECT COUNT('') FROM kills WHERE corpseID=p.playerID AND killerID = corpseID AND gid=p.gid) AS suicides,
                                    IFNULL(elo-IFNULL((SELECT elo FROM players WHERE playerID=p.playerID AND gid<{$this->gid} AND elo IS NOT NULL ORDER BY gid DESC LIMIT 1),1000),0) as elodiff
                                    FROM players as p WHERE gid={$this->gid}";

			$thiscoldata =       array('handle' 	=>  array (array('handle' => 1), $this->lang['th_player'], "40%", 'totalstring', URL_BASE .'mode=profile&amp;h='),
                                       'kills' 	    =>  array (array('kills' => 1, 'suicides' => 0, 'deaths' => 0), $this->lang['abb_kills'], "12%","sum"),
								       'deaths' 	=>  array (array('deaths' => 1, 'sucides' => 0, 'kills' => 0), $this->lang['abb_deaths'], "12%","sum"),
									   'suicides' 	=>  array (array('suicides' => 1), $this->lang['abb_suicides'], "12%", "sum"),
									   'elo' 		=>  array (array('elo' => 1), $this->lang['th_elo'], "12%", "avg"),
                                       'elodiff'    =>  array (array('elodiff' => 1), "+", "15%", "sum", '', '0')
									   );
            $thiscoldata['handle'][4] .= "*playerID*";
            $this->setColumnData($thiscoldata);
            
		} else {
            if($this->teamdata){
                $teamdataquery = "(SELECT COUNT('') FROM kills WHERE killerID=p.playerID AND killerID != corpseID  AND k_team = c_team AND gid=p.gid) AS teamkills,";                
            }
            
            $this->query = "SELECT playerID, handle, IFNULL(elo,(SELECT elo FROM players WHERE playerID=p.playerID AND gid<{$this->gid} AND elo IS NOT NULL ORDER BY gid DESC LIMIT 1)) as elo,
                    (SELECT COUNT('') FROM kills WHERE killerID=p.playerID AND gid=p.gid AND killerID != corpseID) AS kills,
                    (SELECT COUNT('') FROM kills WHERE corpseID=p.playerID AND gid=p.gid AND killerID != corpseID) AS deaths,
                    (SELECT COUNT('') FROM kills WHERE corpseID=p.playerID AND killerID = corpseID AND gid=p.gid) AS suicides, $teamdataquery
                    (SELECT COUNT('') FROM actions WHERE playerID=p.playerID AND gid=p.gid) AS actions,
                    IFNULL(elo-IFNULL((SELECT elo FROM players WHERE playerID=p.playerID AND gid<{$this->gid} AND elo IS NOT NULL ORDER BY gid DESC LIMIT 1),1000),0) as elodiff
                    FROM players as p WHERE gid={$this->gid} AND team='$team'";     

            // for mods where teamdata is logged for each kill
            if($this->teamdata){
               $thiscoldata =     array('handle' 	=>  array (array('handle' => 1), $this->lang['th_player'], "28%", 'totalstring', URL_BASE .'mode=profile&amp;h='),
                                        'kills' 	=>  array (array('kills' => 1, 'suicides' => 0, 'deaths' => 0), $this->lang['abb_kills'], "8%","sum"),
                                        'deaths' 	=>  array (array('deaths' => 1, 'suicides' => 0, 'kills' => 0), $this->lang['abb_deaths'], "8%","sum"),
                                        'suicides' 	=>  array (array('suicides' => 1), $this->lang['abb_suicides'], "7%", "sum"),
                                        'teamkills' =>  array (array('teamkills' => 1), $this->lang['abb_teamkills'], "7%", "sum"),
                                        'actions' 	=>  array (array('actions' => 1), $this->lang['abb_actions'], "7%", "sum"),
                                        'elo' 		=>  array (array('elo' => 1), $this->lang['th_elo'], "12%", "avg"),
                                        'elodiff'   =>  array (array('elodiff' => 1), "+", "15%", "sum", '', '0')
                                        );
                $thiscoldata['handle'][4] .= "*playerID*";
                $this->setColumnData($thiscoldata);
            } else {
                // for mods where teamdata is not logged for each kill (teamkills are ignored)   
                if($this->gamedata['type'] != 'war'){
                    $thiscoldata =    array('handle' 	=>  array (array('handle' => 1), $this->lang['th_player'], "28%", 'totalstring', URL_BASE .'mode=profile&amp;h='),
                                            'kills' 	=>  array (array('kills' => 1, 'suicides' => 0, 'deaths' => 0), $this->lang['abb_kills'], "8%","sum"),
                                            'deaths' 	=>  array (array('deaths' => 1, 'suicides' => 0, 'kills' => 0), $this->lang['abb_deaths'], "8%","sum"),
                                            'suicides' 	=>  array (array('suicides' => 1), $this->lang['abb_suicides'], "7%", "sum"),
                                            'actions' 	=>  array (array('actions' => 1), $this->lang['abb_actions'], "7%", "sum"),
                                            'elo' 		=>  array (array('elo' => 1), $this->lang['th_elo'], "12%", "avg"),
                                            'elodiff'   =>  array (array('elodiff' => 1), "+", "15%", "sum", '', '0')
                                            );
                } else {
                    $thiscoldata =    array('handle' 	=>  array (array('handle' => 1), $this->lang['th_player'], "28%", 'totalstring', URL_BASE .'mode=profile&amp;h='),
                                            'kills' 	=>  array (array('kills' => 1, 'suicides' => 0, 'deaths' => 0), $this->lang['abb_kills'], "8%","sum"),
                                            'deaths' 	=>  array (array('deaths' => 1, 'suicides' => 0, 'kills' => 0), $this->lang['abb_deaths'], "8%","sum"),
                                            'suicides' 	=>  array (array('suicides' => 1), $this->lang['abb_suicides'], "8%", "sum"),
                                            'elo' 		=>  array (array('elo' => 1), $this->lang['th_elo'], "12%", "avg"),
                                            'elodiff'   =>  array (array('elodiff' => 1), "+", "13%", "sum", '', '0')
                                            );
                }
                $thiscoldata['handle'][4] .= "*playerID*";
                $this->setColumnData($thiscoldata);
            }
		}	
		// runs the constructor of orderedtable, and sets the query and makes the table sortable
		parent::__construct($this->query, 1);
	}
	
	public function printResultHeader (){
		if($this->teams == 1){
			$axis 	= array ('mp_showdown' => 'opfor', 'mp_backlot' => 'opfor', 'mp_bloc' => 'spetsnaz', 'mp_countdown'=>'opfor');	
			$allies = array ('mp_showdown' => 'usmc', 'mp_backlot' => 'usmc', 'mp_bloc' => 'sas', 'mp_countdown'=>'usmc');	
			$names 	= array ('opfor' => 'OpFor', 'spetsnaz' => 'Spetsnaz', 'usmc' => 'US Marines', 'sas' => 'S.A.S.');
			(array_key_exists($row['map'], $allies)) ? $ally = $allies[$row['map']] : $ally = 'sas';
			(array_key_exists($row['map'], $axis)) ? $axe = $axis[$row['map']] : $axe = 'opfor';
			
			$this->alliesheader = "<div class=\"allies\"><div class=\"teamname\">{$names[$ally]}</div>{$this->gamedata['alliesscore']}</div>";
			$this->axisheader = "<div class=\"axis\"><div class=\"teamname\">{$names[$axe]}</div>{$this->gamedata['axisscore']}</div>";
            echo "<table width=\"100%\">\n<tr>\n<td width=\"50%\">{$this->alliesheader}</td>\n<td width=\"50%\">{$this->axisheader}</td>\n</table>\n";
		} 
	}
	
	public function printResults(){
		if($this->teams){
			$this->printResultHeader ();
			echo "<table width=\"100%\">\n\t<tr valign=\"top\">\n\t\t<td width=\"50%\">\n";
            
            // allies
			$this->setFunctiondata('allies');
            $this->setOrderBy('kills');       
            $this->setOrder('DESC');            
			$this->printTable();

			echo "\t\t</td>\n\t\t<td width=\"50%\">\n";
			
            // axis
			$this->assignLetter ();
			$this->setFunctiondata('axis');
            $this->setOrderBy('kills');
            $this->setOrder('DESC');
			$this->printTable();
            
			echo "\t\t</td>\n\t</tr>\n</table>";
		} else {
			echo "<table width=\"100%\">\n\t<tr>\n\t\t<td>\n";
			$this->setFunctiondata();
            $this->setOrderBy('kills');
            $this->setOrder('DESC');      
			$this->printTable();		      
			echo "\t\t</td>\n\t</tr>\n</table>";		
		}
	}
}
?>