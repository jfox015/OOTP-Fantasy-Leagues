<?php
    /**
     *	PLAYER MODEL CLASS.
     *
     *	@author			Jeff Fox (Github ID: jfox015)
	 *	@version		1.1.1
	 *  @lastModified	12/18/24
     *
     */
class player_model extends base_model {

    /*--------------------------------
     /	VARIABLES
     /-------------------------------*/
    /**
     *	SLUG.
     *	@var $_NAME:Text
     */
    var $_NAME = 'player_model';

    /**
     *	PLAYER ID.
     *	@var $player_id:Int
     */
    var $player_id = -1;
    /**
	 *	PLAYER ROSTER STATUS.
	 *	@var $player_status:Int
	 */
	var $player_status = -1;
	/*---------------------------------------------
	/
	/	C'TOR
	/	Creates a new instance of player_model
	/
	/---------------------------------------------*/
	function player_model() {
		parent::__construct();

		$this->tblName = 'fantasy_players';
		$this->tables['ROSTERS'] = 'fantasy_rosters';
		$this->tables['WAIVERS'] = 'fantasy_players_waivers';
		$this->tables['WAIVER_CLAIMS'] = 'fantasy_teams_waiver_claims';
		$this->tables['FANTASY_TEAMS'] = 'fantasy_teams';
		$this->tables['OOTP_GAMES'] = 'games';
		$this->tables['OOTP_STARTERS'] = 'projected_starting_pitchers';
		$this->tables['OOTP_TEAMS'] = 'teams';
		$this->tables['COMPILED_TEAM_STATS_BATTING'] = 'fantasy_teams_players_compiled_batting';
		$this->tables['COMPILED_TEAM_STATS_PITCHING'] = 'fantasy_teams_players_compiled_pitching';


		$this->fieldList = array('player_id','player_status');
		$this->conditionList = array();
		$this->readOnlyList = array();

		$this->columns_select = array('id','player_id','team_id','player_status','elidgibility','player_position','player_role');

		parent::_init();
	}
	/*--------------------------------------------------
	/
	/	PUBLIC FUNCTIONS
	/
	/-------------------------------------------------*/
	// SPECIAL QUERIES
	/**
	 * 	GET PLAYER CONUNT.
	 *	Test function to assure that players have been imported from the OOTP players file
	 * 	into the fantasy database.
	 *
	 *	@since	1.0.3
	 */
	public function getPlayerCount() {
		$this->db->select('id');
		$this->db->from('fantasy_players');
		$count = $this->db->count_all_results();
		return $count;
	}

	public function getStartingPitchers($playerList = false) {
		
		//if ($league_id === false) { return; }
		
		$playerInStr = "";
		$teamList = "";
		if ($playerList !== false && sizeof($playerList) > 0) {
			$playerInStr = "(";
			$teamList = "(";
			foreach($playerList as $player_id => $playerInfo) {
				
				if ($playerInStr != "(") $playerInStr .= ",";
				$playerInStr .= $player_id;
				
				if ($teamList != "(") $teamList .= ",";
				$teamList .= $playerInfo['team_id'];
			}
			$playerInStr .= ")";
			$teamList .= ")";
		}
		$result = -1;
		$details = array();
		$this->db->select();
		$where = "";
		if ($playerList !== false) {
			if (!empty($playerInStr)) {
				$where =  '(starter_0 IN '.$playerInStr.' OR starter_1 IN '.$playerInStr;
				$where .= ' OR starter_2 IN '.$playerInStr. 'OR starter_3 IN '.$playerInStr;
				$where .= ' OR starter_4 IN '.$playerInStr. 'OR starter_5 IN '.$playerInStr;
				$where .= ' OR starter_6 IN '.$playerInStr. 'OR starter_7 IN '.$playerInStr.')';
			}
			if (!empty($teamList)) {
				$where .= ' AND team_id IN '.$teamList;
			}
		}
		$this->db->where($where);
		$query = $this->db->get($this->tables['OOTP_STARTERS']);
		//echo($this->db->last_query()."<br />");
		if ($query->num_rows > 0) {
			foreach($query->result() as $row) {
				array_push($details, array('team_id'=>$row->team_id, 'starter_0'=>$row->starter_0,
					'starter_1'=>$row->starter_1,'starter_2'=>$row->starter_2,'starter_3'=>$row->starter_4,
					'starter_4'=>$row->starter_5,'starter_5'=>$row->starter_5,'starter_6'=>$row->starter_6,
					'starter_7'=>$row->starter_7));
			}
		}
		$query->free_result();
		$result = $details;
		return $result;
	}
	/**
	 * 	GET PLAYER BASICS
	 * 	A stripped version of getPlayerDetails that returns:
	 * 	<ul>
	 * 		<li>Fantasy ID</li>
	 * 		<li>OOTP Player ID</li>
	 * 		<li>First Name</li>
	 * 		<li>Last name</li>
	 * 		<li>Position</li>
	 * 		<li>Role</li>
	 * 	</ul>
	 *  @param	$player_id		{int}		Fantasy Player ID
	 *  @return					{Array}		Array of Player Details
	 * 	@since	1.0.3 PROD
	 */
	public function getPlayerBasics($player_id = false) {
		if ($player_id === false) { return; }
		$details = array();
		$this->db->select('fantasy_players.id,fantasy_players.player_id,players.first_name,players.last_name,position,role');
		$this->db->join('players','players.player_id = fantasy_players.player_id','left');
		$this->db->where('fantasy_players.id',$player_id);
		$query = $this->db->get('fantasy_players');	
		if ($query->num_rows() > 0) {
			$details = $query->row_array();
		} else {
			$details['id'] = $details['player_id'] = -1;
			$details['first_name'] = "Not";
			$details['last_name'] = "Found";
		}
		$query->free_result();
		return $details;
	}
	/**
	 * 	GET PLAYER POSITIONS
	 * 	A function to return basic details including positions. Acceptrs a single player or sirng of player Ids

	 *  @param	$player_id		{int}		Fantasy Player ID
	 *  @return					{Array}		Array of Player Details
	 * 	@since	1.0.3 PROD
	 */
	public function getPlayerPositions($player_id = false, $playersStr = false) {
		if ($player_id === false && $playersStr === false) { return; }
		$details = array();
		$this->db->select('fantasy_players.id,fantasy_players.player_id,players.first_name,players.last_name,position,role');
		$this->db->join('players','players.player_id = fantasy_players.player_id','left');
		if ($player_id !== false) {
			$this->db->where('fantasy_players.id',$player_id);
		} else  if ($playersStr !== false) {
			$this->db->where("fantasy_players.id IN (".$playersStr.")");
		}
		$query = $this->db->get('fantasy_players');	
		if ($query->num_rows() > 0) {
			foreach($query->result_array() as $row) {
				array_push($details, $row);
			}
		}
		$query->free_result();
		return $details;
	}

	public function getPlayerDetails($player_id = false, $ootp_ver = OOTP_CURRENT_VERSION) {

		if ($player_id === false) { return; }
		$details = array();

		$this->db->select('fantasy_players.id,fantasy_players.player_id,players.first_name,players.last_name,players.nick_name as playerNickname,teams.team_id, teams.name AS team_name, teams.nickName as teamNickname, positions, position,role, date_of_birth,weight,height,bats,throws,draft_year,draft_round,draft_pick,draft_team_id,retired,
						  injury_is_injured, injury_dtd_injury, injury_career_ending, injury_dl_left, injury_left, injury_id, logo_file_name, players.city_of_birth_id, age, own, own_last, start, start_last,player_status');
		$this->db->join('players','players.player_id = fantasy_players.player_id','left');
		$this->db->join('teams','teams.team_id = players.team_id','left');
		$this->db->where('fantasy_players.id',$player_id);
		$query = $this->db->get('fantasy_players');

		if ($query->num_rows() > 0) {
			$details = $query->row_array();
		}
		if (sizeof($details) > 0) {
			$birthCity = '';
			$birthRegion = '';
			$birthNation = '';

			if (isset($details['city_of_birth_id']) && $details['city_of_birth_id'] != 0) {
                $select = 'cities.name as birthCity, nations.short_name as birthNation';
                /* UPDATE 1.0.1
				 * 	OOTP 12 removed the cities.region column, use states.name for 12 onward >
				 */
				if ($ootp_ver < 12) {
                    $select .= ',cities.region as birthRegion';
                } else {
                    $select .= ',states.name as birthRegion';
                }
				$this->db->select($select);
				$this->db->join('nations','nations.nation_id = cities.nation_id','right outer');
                if ($ootp_ver >= 12) {
                    $this->db->join('states','states.state_id = cities.state_id','right outer');
                }
				$this->db->where('cities.city_id',$details['city_of_birth_id']);
				$cQuery = $this->db->get('cities');
				if ($cQuery->num_rows() > 0) {
					$cRow = $cQuery->row();
					$birthCity = $cRow->birthCity;
					$birthRegion = $cRow->birthRegion;
					$birthNation = $cRow->birthNation;
				}
				$cQuery->free_result();
			} else {
				$birthCity = 'Unknown';
				$birthNation = 'N/A';
			}
			$details = $details + array('birthCity'=>$birthCity,'birthRegion'=>$birthRegion,'birthNation'=>$birthNation);
			$query->free_result();
		} else {
			$details['id'] = $details['player_id'] = -1;
			$details['first_name'] = "Not";
			$details['last_name'] = "Found";
		}
		//echo($this->db->last_query()."<br />");
		return $details;
    }
	public function getPlayersDetails($players = array()) {

		if (sizeof($players) == 0) { return; }
		$playersInfo = array();

		foreach($players as $row) {
			$playersInfo = $playersInfo + array($row['player_id'] => $this->getPlayerDetails($row['player_id']));
		}
		//echo($this->db->last_query()."<br />");
		return $playersInfo;
	}

	public function getWaiverStatus($league_id, $player_id = false) {

		if ($player_id === false) { $player_id = $this->id; }

		$this->db->select('waiver_period');
		$this->db->where('player_id',$player_id);
		$this->db->where('league_id',$league_id);
		$query = $this->db->get($this->tables['WAIVERS']);
		//echo($this->db->last_query()."<br />");
		if ($query->num_rows > 0) {
			$row = $query->row();
			return $row->waiver_period;
		} else {
			return -1;
		}
	}

	public function getWaiverClaims($league_id, $player_id = false) {

		if ($league_id === false) { return; }
		if ($player_id === false) { $player_id = $this->id; }

		$teams = array();
		$this->db->select('team_id');
		$this->db->where('player_id',$player_id);
		$this->db->where('league_id',$league_id);
		$query = $this->db->get($this->tables['WAIVER_CLAIMS']);
		//echo($this->db->last_query()."<br />");
		if ($query->num_rows > 0) {
			foreach($query->result() as $row) {
				array_push($teams, $row->team_id);
			}
		}
		$query->free_result();
		return $teams;
	}

	public function getPlayersOnWaivers($period_id = false, $league_id = false, $idsOnly = false) {

		if ($period_id === false || $league_id === false) { return; }

		$players = array();
		$this->db->select($this->tables['WAIVERS'].'.player_id, first_name, last_name, position, role');
		$this->db->join("fantasy_players","fantasy_players.id = ".$this->tables['WAIVERS'].".player_id", "left");
		$this->db->join("players","fantasy_players.player_id = players.player_id", "right outer");
		$this->db->where($this->tables['WAIVERS'].'.league_id',$league_id);
		$this->db->where('waiver_period',$period_id);
		$query = $this->db->get($this->tables['WAIVERS']);
		if ($query->num_rows > 0) {
			foreach($query->result() as $row) {
				if ($idsOnly === false) {
					array_push($players, array('player_id'=>$row->player_id, 'player_name'=>$row->first_name." ".$row->last_name,
										  	'position'=>$row->position, 'role'=>$row->role));
				} else {
					array_push($players, $row->player_id);
				}
			}
		}
		$query->free_result();
		return $players;
	}

	public function getPlayerNews($player_id = false) {

		if ($player_id === false) { return; }
		$news = array();

		$this->db->select('id,news_date,news_subject,news_body,fantasy_analysis');
		$this->db->where('type_id',3);
		$this->db->where('var_id',$player_id);
		$query = $this->db->get('fantasy_news');

		if ($query->num_rows() > 0) {
			$news = $query->row_array();
		}

		$query->free_result();
		return $news;
    }
	public function getHighestScoring($league_id, $player_id = false) {
		if ($player_id === false) { $player_id = $this->player_id; }

		$scores = array();
		$this->db->select('total');
		$this->db->from('fantasy_players_scoring');
		$this->db->where('player_id',$player_id);
		$this->db->where('league_id',$league_id);
		$this->db->order_by('total','desc');
		$query = $this->db->get();
		if ($query->num_rows > 0) {
			foreach($query->result() as $row) {
				array_push($scores,intval($row->total));
			}
			if (sizeof($scores) > 0) rsort($scores,SORT_NUMERIC);
			return ($scores[0]);
		} else {
			return false;
		}
	}
	public function getPlayerScoring($league_id, $player_id = false) {
		if ($player_id === false) { $player_id = $this->player_id; }

		$count = 0;
		$playerPoints = array();
		// GET # of scoring periods
		$this->db->select('id');
		$this->db->from('fantasy_scoring_periods');
		$count = $this->db->count_all_results();

		if ($count > 0) {
			for ($i = 1; $i < $count; $i++) {
				$this->db->select('total, scoring_period_id');
				$this->db->from('fantasy_players_scoring');
				$this->db->where('player_id',intval($player_id));
				$this->db->where('league_id',intval($league_id));
				$this->db->where('scoring_period_id',$i);
				$query = $this->db->get();
				if ($query->num_rows > 0) {
					$row = $query->row();
					$playerPoints[$i] = $row->total;
				} else {
					$playerPoints[$i] = 0;
				}
			}
		}
		return $playerPoints;
	}
	public function getPlayerName($player_id = false) {

		if ($player_id === false) { $player_id = $this->player_id; }

		$name = "";
		// GET PLAYER POSITION
		$this->db->select('first_name, last_name');
		$this->db->join("players","fantasy_players.player_id = players.player_id", "right outer");
		$this->db->where('id',$player_id);
		$query = $this->db->get($this->tblName);
		if ($query->num_rows() > 0) {
			$row = $query->row();
			$name = $row->first_name." ".$row->last_name;
		}
		$query->free_result();
		return $name;

	}
	public function getOOTPPlayerPosition($player_id = false) {

		if ($player_id === false) { $player_id = $this->player_id; }

		$pos = -1;
		// GET PLAYER POSITION
		$this->db->select('position');
		$this->db->from('players');
		$this->db->where('player_id',$player_id);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$row = $query->row();
			$pos = $row->position;
		}
		$query->free_result();
		return $pos;

	}
	/**
	 * 	GET INJURED PLAYERS.
	 * 	Returns a list of player details and injury data for injured players for the given team.
	 *
	 * 	@param	$score_period			{int}		The Scoring Period
	 * 	@param	$user_id				{int}		The Fantasy teams ID
	 * 	@param	$idsOnly				{Boolean}	TRUE For ID's Oonly, FALSE for full details
	 * 	@param	$league_id				{int}		The Fantasy League Id
	 * 	@return							{Array}   	Array of Player Details
	 *
	 *  @since	1.0.3 PROD
	 *  @access	public
	 */
	public function getInjuredPlayers($score_period = false, $team_id = false, $idsOnly = false) {

		if ($score_period === false) { $score_period = array('id'=>1) ; }
		
		$playerIds = "";
		$players = array();
		if ($team_id !== false) {
			if (!function_exists('getBasicRoster')) {
				$this->load->helper('roster');
			}
			$roster = getBasicRoster($team_id, $score_period);
			foreach($roster as $player) {
				if ($playerIds != "") $playerIds .= ",";
				$playerIds .= $player['id'];
			}
		}
		// ASSURE THERE ARE PLAYERS ON THE TEAMS ROSTER TO CHECK
		if (!empty($playerIds)) {
			$sql = "SELECT fp.id, fp.player_id,first_name,last_name,positions,injury_is_injured,injury_dtd_injury,injury_id,injury_career_ending,injury_dl_left,injury_left,is_on_dl,is_on_dl60,is_active,is_on_secondary,dl_days_this_year ";
			$sql .= "FROM fantasy_players as fp ";
			$sql .= "LEFT JOIN players as p ON p.player_id = fp.player_id ";
			$sql .= "LEFT JOIN players_roster_status as prs ON prs.player_id = fp.player_id ";
			$sql .= "WHERE p.player_id=prs.player_id ";
			if ($team_id !== false) {
				$sql .= "AND fp.id IN (".$playerIds.") ";
			}
			$sql .= "AND (injury_is_injured=1 OR is_on_dl=1 OR is_on_dl60=1)";
			$query = $this->db->query($sql);
			//echo($this->db->last_query()."<br />");
			if ($query->num_rows() > 0) {
				foreach($query->result() as $row) {
					if ($idsOnly === false) {
						array_push($players, array('id'=>$row->id,'player_id'=>$row->player_id, 'player_name'=>$row->first_name." ".$row->last_name,
												'positions'=>$row->positions,'injury_is_injured'=>$row->injury_is_injured, 'injury_dtd_injury'=>$row->injury_dtd_injury,
												'injury_career_ending'=>$row->injury_career_ending, 'injury_dl_left'=>$row->injury_dl_left,
												'is_on_dl'=>$row->is_on_dl, 'is_on_dl60'=>$row->is_on_dl60,'is_active'=>$row->is_active, 'is_on_secondary'=>$row->is_on_secondary, 
												'dl_days_this_year'=>$row->dl_days_this_year,'injury_id'=>$row->injury_id
												)
								);
					} else {
						array_push($players, $row->id);
					}
				}
			}
			$query->free_result();
		}
		return $players;
	}
	public function getOOTPTeam($player_id = false) {

		if ($player_id === false) { $player_id = $this->player_id; }

		$team_id = -1;
		// GET PLAYER POSITION
		$this->db->select('team_id');
		$this->db->from('players');
		$this->db->where('player_id',$player_id);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$row = $query->row();
			$team_id = $row->team_id;
		}
		$query->free_result();
		return $team_id;

	}

	public function getFantasyTeam($player_id = false, $scoring_period_id = false, $league_id = false) {

		if ($player_id === false) { return; }

		$team = array();
		// GET PLAYER POSITION
		$this->db->select($this->tables['FANTASY_TEAMS'].'.id, teamname, teamnick, avatar, owner_id');
		$this->db->from($this->tables['ROSTERS']);
		$this->db->join($this->tables['FANTASY_TEAMS'],$this->tables['FANTASY_TEAMS'].'.id = '.$this->tables['ROSTERS'].'.team_id');
		$this->db->where($this->tables['ROSTERS'].'.player_id',intval($player_id));
		if ($scoring_period_id !== false) {
			$this->db->where($this->tables['ROSTERS'].'.scoring_period_id',intval($scoring_period_id));
		}
		if ($league_id !== false) {
			$this->db->where($this->tables['ROSTERS'].'.league_id',intval($league_id));
		}
		$query = $this->db->get();
		//echo("Last Query = ".$this->db->last_query()."<br />");
		if ($query->num_rows() > 0) {
			$row = $query->row();
			$team = array('id'=>$row->id, 'teamname'=>$row->teamname.' '.$row->teamnick,
						  'avatar'=>$row->avatar,'owner_id'=>$row->owner_id);
		} else {
			$team = array('id'=>-1, 'teamname'=>'Free Agent','avatar'=>'');
		}
		$query->free_result();
		return $team;
	}
	/**
	 * 	GET ACTIVE OOTP PLAYERS
	 * 	Facade function for getOOTPPlayers that adds the requirement that players be active
	 * 	
	 */
	public function getActiveOOTPPlayers($league_id = false,$searchType = 'all', $searchParam = false,$current_scoring_period = 1, $nonFreeAgents = array()) {
		return $this->getOOTPPlayers($league_id,$searchType, $searchParam,$current_scoring_period, $nonFreeAgents, 1);
	}
	/**
	 * 	GET OOTP PLAYERS
	 * 	Queries the OOTP "players" table for all players in the game
	 */
	public function getOOTPPlayers($league_id = false,$searchType = 'all', $searchParam = false,$current_scoring_period = 1, $nonFreeAgents = array(),
								  $playerStatus = false, $selectBox = false) {
		$players = array();

		//$this->db->select('fantasy_players.id,fantasy_players.player_id, fantasy_players.player_status, first_name, last_name, fantasy_players.positions, players.position, players.role, players.injury_is_injured, players.injury_dtd_injury, players.injury_career_ending, players.injury_dl_left, players.injury_left, players.injury_id, ');
		$this->db->select('fantasy_players.id,fantasy_players.player_id, fantasy_players.player_status, first_name, last_name, fantasy_players.positions, players.position, players.role, players.injury_is_injured, players.injury_dtd_injury, players.injury_career_ending, players.injury_dl_left, players.injury_left, players.injury_id, players.team_id, is_on_dl, is_on_dl60, is_active, own, start');
		$this->db->from('fantasy_players');
		$this->db->join('players','players.player_id = fantasy_players.player_id','left');
		$this->db->join('players_roster_status','players_roster_status.player_id = fantasy_players.player_id','left');
		switch ($searchType) {
			case 'alpha':
				$this->db->like('players.last_name', $searchParam, 'after');
				break;
			case 'pos':
				$col = "position";
				if ($searchParam == 11 || $searchParam == 12 || $searchParam == 13) {
					$col = "role";
				}
				if ($searchParam == 20) {
					$this->db->where('(players.position = 7 OR players.position = 8 OR players.position = 9)');
				} else if ($searchParam == 12 || $searchParam == 13) {
					$this->db->where('(players.role = 12 OR players.role = 13)');
				} else {
					$this->db->where('players.'.$col, $searchParam);
				}
				break;
			case 'all':
			default:
				break;
		} // END switch
		if ($playerStatus !== false) {
			$this->db->where('fantasy_players.player_status',intval($playerStatus));
		}
		$this->db->where('players.retired',0);
		if (isset($nonFreeAgents) && sizeof($nonFreeAgents) > 0) {
			$this->db->where_not_in('fantasy_players.id',$nonFreeAgents);
		}
		$this->db->order_by('players.last_name','asc');
		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			$fields = $query->list_fields();
			if ($selectBox === true) {
				$players = array(-1=>"Select Player");
			}
			foreach ($query->result() as $row) {
				$tmpPos = "";
				if ($row->position == 1) {
					$tmpPos = $row->role;
				} else {
					$tmpPos = $row->position;
				}
				if ($selectBox === false) {
					$player = array();
					foreach($fields as $field) {
						$player[$field] = $row->$field;
					}
					$player['player_name'] = $row->first_name." ".$row->last_name;
					$player['pos'] = $tmpPos;
					array_push($players,$player);
				} else {
					$players = $players + array($row->id=>$row->last_name.", ".$row->first_name." - ".get_pos($tmpPos));
				}
			}
		}
		return $players;
	}

	/**
	 *	GET PLAYER STATS FOR PERIOD
	 *	Returns player stats from OOTP game data based on a fixed time period or a year.
	 *	@since	1.0.1
	 *	@param	$playerType			1= Batters, 2 = Pitchers
	 *	@param	$scoring_period		Scoring Period Array object
	 *	@param	$rules				Scoring Rules Array
	 *	@param	$players			Players List Array, generated by TeamModel->GetBatters() or TeamModel->getPitchers()
	 *	@param	$excludeList		Array of Player Ids to exclude
	 *	@param	$searchType			Alpha numerica, position based or all
	 *	@param	$searchParam		Secondary search such as a letter for alpah or role number for position
	 *	@param	$query_type			Which query type from the general helper stats table
	 *	@param	$stats_range		Number of days to pull stats for. 
	 *	@param	$limit				Limit on results, <code>-1</code> for no limit
	 *	@param	$startIndex			The first record number to begin from, used for pagination
	 *	@param	$batting_sort		Batting Stats sort column
	 *	@param	$pitching_sort 		Pitching Stats sort column
	 *	@return						Stats array object
	 *	@see	TeamModel
	 *
	 */
	public function getStatsforPeriod($playerType = 1, $scoring_period = array(), $rules = array(),
									  $players = array(),$excludeList = array(), $lgYear = false, $searchType = 'all', $searchParam = false,
									  $query_type = QUERY_STANDARD, $stats_range = -1, $limit = -1, $startIndex = 0,
									  $batting_sort = false, $pitching_sort = false) {
		$stats = array();

		$playerList = "(";
		if (is_array($players) && sizeof($players) > 0) {
			foreach($players as $id=>$player_id) {
				if ($playerList != "(") { $playerList .= ","; }
				$playerList .= $player_id;
			}
		}
		$playerList .= ")";

		$excludeListStr = "(";
		if (is_array($excludeList) && sizeof($excludeList) > 0) {
			foreach($excludeList as $player_id) {
				if ($excludeListStr != "(") { $excludeListStr .= ","; }
				$excludeListStr .= $player_id;
			}
		}
		$excludeListStr .= ")";

		// BUILD QUERY TO PULL CURRENT GAME DATA FOR THIS PLAYER
		$sql = 'SELECT fantasy_players.id, fantasy_players.positions, players.position, players.role, players.player_id ,first_name, last_name, players.injury_is_injured, players.injury_dtd_injury, players.injury_career_ending, players.injury_dl_left, players.injury_left, players.injury_id,rating,';
		$sql .= player_stat_query_builder($playerType, $query_type, $rules);
		if ($playerType == 1) {
			$sql .= ",players.position as pos ";
			$tblName = 'players_game_batting';
			$posType = 'players.position';
			$order = 'ab';
			if ($batting_sort !== false) $order = $batting_sort; 
		} else {
			$sql .= ",players.role as pos ";
			$tblName = 'players_game_pitching_stats';
			$posType = 'players.role';
			$order = 'ip';
			if ($pitching_sort !== false) $order = $pitching_sort;
		}
		$sql .= "FROM games ";
		$sql .= 'LEFT JOIN '.$tblName.' ON games.game_id = '.$tblName.'.game_id ';
		$sql .= 'RIGHT OUTER JOIN players ON players.player_id = '.$tblName.'.player_id ';
		$sql .= 'RIGHT OUTER JOIN fantasy_players ON players.player_id = fantasy_players.player_id ';
		if (sizeof($rules) > 0 && isset($rules['scoring_type']) && $rules['scoring_type'] == LEAGUE_SCORING_HEADTOHEAD) {
			$order = 'fpts';
		}
		if (sizeof($scoring_period) > 0 && $stats_range == -1) {
			$sql .= "WHERE DATEDIFF('".$scoring_period['date_start']."',games.date)<= 0 ";
			$sql .= "AND DATEDIFF('".$scoring_period['date_end']."',games.date)>= 0 ";
		}
		switch ($searchType) {
			case 'alpha':
				$sql .= ' AND players.last_name LIKE "'.$searchParam.'%" ';
				break;
			case 'pos':
				$col = "position";
				if ($searchParam == 11 || $searchParam == 12 || $searchParam == 13) {
					$col = "role";
				}
				if ($searchParam == 20) {
					$sql .= ' AND (players.position = 7 OR players.position = 8 OR players.position = 9) ';
				} else if ($searchParam == 12 || $searchParam == 13) {
					$sql .= ' AND (players.role = 12 OR players.role = 13) ';
				} else {
					$sql .= ' AND players.'.$col.' = '.$searchParam." ";
				}
				break;
			case 'all':
			default:
				break;
		} // END switch
		if ($playerList != "()") {
			$sql .= " AND ".$tblName.".player_id IN ".$playerList.' ';
		}
		if ($excludeListStr != "()") {
			$sql .= " AND ".$tblName.".player_id NOT IN ".$excludeListStr.' ';
		}
		$sql .= "GROUP BY ".$tblName.'.player_id ';
		$sql .= "ORDER BY ".$order." DESC ";
		if ($limit != -1 && $startIndex == 0) {
			$sql.="LIMIT ".$limit;
		} else if ($limit != -1 && $startIndex > 0) {
			$sql.="LIMIT ".$startIndex.", ".$limit;
		}
		$gQuery = $this->db->query($sql);
		//echo($sql."<br />");
		if ($gQuery->num_rows() > 0) {
			$fields = $gQuery->list_fields();
			foreach ($gQuery->result() as $sRow) {
				$player = array();
				foreach($fields as $field) {
					$player[$field] = $sRow->$field;
				}
				$player['player_name'] = $sRow->first_name." ".$sRow->last_name;
				if ($sRow->position == 1) {
					$player['pos'] = $sRow->role;
				} else {
					$player['pos'] = $sRow->position;
				}
				array_push($stats,$player);
			}
		}
		$gQuery->free_result();
		return $stats;
	}

	function getStatsForSeason($player_type = 1, $rules = array(),$players = array(),$excludeList = array(), 
							   $ootp_league_date = false, $stats_range = -1, $ootp_league_id = -1, $limit = -1, $startIndex = 0,
							   $batting_sort = false, $pitching_sort = false) {

		$stats = array();

		$playerList = "(";
		if (is_array($players) && sizeof($players) > 0) {
			foreach($players as $id=>$player_id) {
				if ($playerList != "(") { $playerList .= ","; }
				$playerList .= $player_id;
			}
		}
		$playerList .= ")";

		$excludeListStr = "(";
		if (is_array($excludeList) && sizeof($excludeList) > 0) {
			foreach($excludeList as $player_id) {
				if ($excludeListStr != "(") { $excludeListStr .= ","; }
				$excludeListStr .= $player_id;
			}
		}
		$excludeListStr .= ")"; 
		$where = '';
		
		$sql = 'SELECT fantasy_players.id, "add", age, throws, bats, fantasy_players.id,fantasy_players.positions, players.player_id, players.position as position, players.role as role, players.first_name, players.last_name, players.injury_is_injured, players.injury_dtd_injury, players.injury_career_ending, players.injury_dl_left, players.injury_left, players.injury_id,';		
		if ($player_type == 1) {
			if ($stats_range == '3yr_avg') {
				$sql .= player_stat_query_builder(1, QUERY_STANDARD, $rules, false)." ";
			} else {
				$sql .= player_stat_query_builder(1, QUERY_STANDARD, $rules)." ";
			}
			$tblName = "players_career_batting_stats";
			$where = "AND players.position <> 1 AND players_career_batting_stats.ab > 0 ";
			if (!empty($position_type) && $position_type != -1) {
				if ($position_type == 20) {
					$where.="AND (players.position = 7 OR players.position = 8 OR players.position = 9) ";
				} else {
					$where.="AND players.position = ".$position_type." ";
				}
			}
			$orderBy = 'ab';
		} else {
			if ($stats_range == '3yr_avg') {
				$sql .= player_stat_query_builder(2, QUERY_STANDARD, $rules, false)." ";
			} else {
				$sql .= player_stat_query_builder(2, QUERY_STANDARD, $rules)." ";
			}
			$tblName = "players_career_pitching_stats";
			$where = "AND players_career_pitching_stats.ip > 0 ";
			if (!empty($role_type) && $role_type != -1) {
				$where.="AND players.role = ".$role_type." ";
			}
			$orderBy = 'ip';
		}	
		$sql .= ' FROM '.$tblName;
		$sql .= ' LEFT JOIN fantasy_players ON fantasy_players.player_id = '.$tblName.'.player_id';
		$sql .= ' LEFT JOIN players ON players.player_id = '.$tblName.'.player_id';
		$sql .= ' WHERE '.$tblName.'.league_id = '.$ootp_league_id.' AND players.retired = 0';
		$sql .= ' AND '.$tblName.'.split_id = 1 AND '.$tblName.'.level_id = 1';

		if ($playerList != "()") {
			$sql .= " AND ".$tblName.".player_id IN ".$playerList.' ';
		}
		if ($excludeListStr != "()") {
			$sql .= " AND ".$tblName.".player_id NOT IN ".$excludeListStr.' ';
		}
		$year_time = (60*60*24*365);
		if ($ootp_league_date === false || $ootp_league_date == EMPTY_DATE_STR) {
			$base_year = time();
		} else {
			$base_year = strtotime($ootp_league_date);
		}
		if ($stats_range != 4) {
			if ($stats_range == -1) {
				$sql .= ' AND '.$tblName.'.year = '.date('Y',$base_year);
			} else {
				$sql .= ' AND '.$tblName.'.year = '.date('Y',$base_year-($year_time * $stats_range));
			}
		} else {
			$sql .= ' AND ('.$tblName.'.year = '.date('Y',$base_year-($year_time))." OR ".$tblName.'.year = '.date('Y',time()-($year_time * 2))." OR ".$tblName.'.year = '.date('Y',time()-($year_time * 3)).")";
		}
		if (!empty($where)) {
			$sql .= " ".$where;
		} // END if (!empty($where))

		$sql.=' GROUP BY '.$tblName.'.player_id';
		if (sizeof($rules) > 0 && isset($rules['scoring_type']) && $rules['scoring_type'] == LEAGUE_SCORING_HEADTOHEAD) {
			$orderBy = 'fpts';	
			$order = "DESC";
		} else {
			$orderBy = 'rating';	
			$order = "DESC";
		}
		$sql.=" ORDER BY ".$orderBy." ".$order." ";
		if ($limit != -1 && $startIndex == 0) {
			$sql.="LIMIT ".$limit;
		} else if ($limit != -1 && $startIndex > 0) {
			$sql.="LIMIT ".$startIndex.", ".$limit;
		}
		//echo("sql = ".$sql."<br />");
		$query = $this->db->query($sql);
		$fields = $query->list_fields();
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$player = array();
				foreach($fields as $field) {
					$player[$field] = $row->$field;
				}
				$player['player_name'] = $row->first_name." ".$row->last_name;
				if ($row->position == 1) {
					$player['pos'] = $row->role;
				} else {
					$player['pos'] = $row->position;
				}
				array_push($stats,$player);
			}
		}
		$query->free_result();
		return $stats;
	}

	public function getFantasyStats($countOnly = false, $ootp_league_id, $year, $player_type=1, $position_type = -1,
									$role_type = -1, $scoring_period_id = false, $roster_status = -1, $limit = -1,
									$startIndex = 0, $league_id = false, $game_score_period = false, $rules = array(),
									$searchType = 'all', $searchParam = -1) {

		$nonFreeAgents = array();
		$playersOnWaivers = $this->getPlayersOnWaivers($scoring_period_id+1, $league_id, true);
		if ($roster_status == -1 && $league_id !== false && $league_id != -1) {
				if (!function_exists('getNonFreeAgentsByLeague')) {
				$this->load->helper('roster');
			}
			if ($roster_status == -1) {
				$nonFreeAgents = getNonFreeAgentsByLeague($league_id,  $scoring_period_id);

			}
		} else if ($roster_status == 2 && $league_id !== false && $league_id != -1) {
			if (!function_exists('getDraftedPlayersByLeague')) {
				$this->load->helper('roster');
			}
			if ($roster_status == -1) {
				$nonFreeAgents = getDraftedPlayersByLeague($league_id);
			}
		}

		$notAFreeAgentStr = "(";
		if (sizeof($nonFreeAgents) > 0) {
			foreach ($nonFreeAgents as $id) {
				if ($notAFreeAgentStr != "(") { $notAFreeAgentStr .= ","; }
				$notAFreeAgentStr .= $id;
			}
		}
		$notAFreeAgentStr .= ")";

		$waiverWireStr = "(";
		if ($roster_status == 3 && $league_id !== false && $league_id != -1 && sizeof($playersOnWaivers) > 0) {
			foreach ($playersOnWaivers as $id) {
				if ($waiverWireStr != "(") { $waiverWireStr .= ","; }
				$waiverWireStr .= $id;
			}
		}
		if ($roster_status == 3 && $waiverWireStr == "(") { $waiverWireStr .= "''"; }
		$waiverWireStr .= ")";

		//echo("Size of Rules = ".sizeof($rules)."<br />");
		$this->db->flush_cache();
		$sql="SELECT 'add','teamname', fantasy_players.id,fantasy_players.positions, players.player_id, players.position as position, players.role as role, players.first_name, players.last_name, players.injury_is_injured, players.injury_dtd_injury, players.injury_career_ending, players.injury_dl_left, players.injury_left, players.injury_id,rating,";
		if ($player_type == 2) {
			$sql .= player_stat_query_builder(2, QUERY_STANDARD, $rules)." ";

			$tblName = "players_career_pitching_stats";
			$where = "AND players_career_pitching_stats.ip > 0 ";
			if (!empty($role_type) && $role_type != -1) {
				$where.="AND players.role = ".$role_type." ";
			}
			$order = 'ip';
		} else {
			$sql .= player_stat_query_builder(1, QUERY_STANDARD, $rules)." ";
			$tblName = "players_career_batting_stats";
			$where = "AND players.position <> 1 AND players_career_batting_stats.ab > 0 ";
			if (!empty($position_type) && $position_type != -1) {
				if ($position_type == 20) {
					$where.="AND (players.position = 7 OR players.position = 8 OR players.position = 9) ";
				} else {
					$where.="AND players.position = ".$position_type." ";
				}
			}
			$order = 'ab';
		}
		$sql.="FROM fantasy_players
		LEFT JOIN players ON players.player_id = fantasy_players.player_id
		LEFT JOIN ".$tblName." ON players.player_id = ".$tblName.".player_id ";
		$sql.="WHERE ".$tblName.".league_id = $ootp_league_id
		AND ".$tblName.".split_id = 1
		AND ".$tblName.".year = $year
		AND ".$tblName.".level_id = 1 ";
		switch ($searchType) {
			case 'alpha':
				$sql .= ' AND players.last_name LIKE "'.$searchParam.'%" ';
				break;
			case 'pos':
				$col = "position";
				if ($searchParam == 11 || $searchParam == 12 || $searchParam == 13) {
					$col = "role";
				}
				if ($searchParam == 20) {
					$sql .= ' AND (players.position = 7 OR players.position = 8 OR players.position = 9) ';
				} else if ($searchParam == 12 || $searchParam == 13) {
					$sql .= ' AND (players.role = 12 OR players.role = 13) ';
				} else {
					$sql .= ' AND players.'.$col.' = '.$searchParam." ";
				}
				break;
			case 'all':
			default:
				break;
		} // END switch
		$sql .= $where;
		if ($notAFreeAgentStr != "()") {
			$sql .= ' AND fantasy_players.id NOT IN '.$notAFreeAgentStr." ";
		}
		if ($roster_status == 3) {
			$sql .= ' AND fantasy_players.id IN '.$waiverWireStr." ";
		}
		$sql.="GROUP BY players.player_id ";
		if (sizeof($rules) > 0) {
			if (isset($rules['scoring_type']) && $rules['scoring_type'] == LEAGUE_SCORING_HEADTOHEAD) {
				$order = 'fpts';
			} else {
				$order = 'rating';
			}
		}
		$sql.="ORDER BY ".$order." DESC ";
		if ($limit != -1 && $startIndex == 0) {
			$sql.="LIMIT ".$limit;
		} else if ($limit != -1 && $startIndex > 0) {
			$sql.="LIMIT ".$startIndex.", ".$limit;
		}
		//echo("SQL = ".$sql."<br />");
		$query = $this->db->query($sql);

		$fantasy_stats = array();
		if ($query->num_rows > 0) {
			$fields = $query->list_fields();
			foreach($query->result() as $row) {
				$player = array();
				foreach($fields as $field) {
					$player[$field] = $row->$field;
				}
				$player['player_name'] = $row->first_name." ".$row->last_name;
				//echo($row->id." in array, player on waivers? ".(in_array($row->id,$playersOnWaivers)?'true':'false')."<br/>");
				if ((isset($playersOnWaivers) && sizeof($playersOnWaivers) > 0) && in_array($row->id,$playersOnWaivers)) {
					$player['on_waivers'] = 1;
				}
				if ($row->position == 1) {
					$player['pos'] = $row->role;
				} else {
					$player['pos'] = $row->position;
				}
				array_push($fantasy_stats,$player);
			}
		}
		$query->free_result();
		return $fantasy_stats;
	}
	/**
	 *	UPDATE PLAYER RATINGS.
	 *	Loads players statistical performance for the period in days specififed to compute the average
	 *	and standard population deviation values. Then, all players compiled stats for the given
	 *	period are loaded and rated based on coparison to these averages and stored in the
	 *	fantasy_players table.
	 *
	 *	@param	$ratingsPeriod		Number of days back to rate stats
	 *	@param	$stats_period		1 = CURRENT SEASOn, =1 = LAST SEASON
	 *	@param	$scoring_period		Scoring Period object
	 *	@param	$ootp_league_id		OOTP League ID value, defaults to 100 if no value passed
	 *	@return						Summary String
	 *	@since						1.0.4
	 *	@version					1.0
	 *	@see						Controller->Admin->playerRatings()
	 *
	 */
	public function updatePlayerRatings($ratingsPeriod = 15, $stats_period = 1, $scoring_period = false, $ootp_league_id = 100) {
		
		if (($scoring_period === false || ($stats_period == 1 && sizeof($scoring_period) < 1))) { return false; } // END if

		$this->lang->load('admin');
		/*--------------------------------------
		/
		/	1.0 ARRAY PREP
		/
		/-------------------------------------*/
		$error = false;
		/*--------------------------------------
		/	1.1 GET PLAYERS ARRAY
		/-------------------------------------*/
		$player_list = $this->getActiveOOTPPlayers();
		$summary = $this->lang->line('sim_player_ratings');
		/*--------------------------------------
		/	1.2 DEFINE RATING PERIOD
		/-------------------------------------*/
		// IF the season has started, usen current years game stats
		if ($stats_period == 1) {
			$day = 60*60*24;
			$period_start = date('Y-m-d',((strtotime($scoring_period['date_end']))-($day*$ratingsPeriod)));
		} else {
			// OTHER WISE, use last years stats
			$year = $scoring_period;
		}
		
		$statsTypes = array(1=>'batting',2=>'pitching');
		$statCats = array();
		$ratingsCats = array();

		$period_str = str_replace('[START_DATE]',$period_start,$this->lang->line('sim_player_rating_period'));
		$period_str = str_replace('[END_DATE]',$scoring_period['date_end'],$period_str);
		$summary .= str_replace('[DAYS]',$ratingsPeriod,$period_str);
		/*--------------------------------------
		/
		/	2.0 STAT AVG,STDDEV LOOP
		/
		/-------------------------------------*/
		if (sizeof($player_list) > 0) {
			$summary .= str_replace('[PLAYER_COUNT]',sizeof($player_list),$this->lang->line('sim_player_rating_count'));
			$processCount = 0;
			/*-------------------------------------------
			/	2.1 BUOLD LIST OF ACTIVE PLAYERS
			/-------------------------------------------*/
			$players_str = "(";
			foreach($player_list as $row) {
				if ($players_str != "(") { $players_str .= ","; }
				$players_str .= $row['player_id'];
			}
			$players_str .= ")";
			/*-------------------------------
			/	2.2 SWITCH ON PLAYER TYPE
			/------------------------------*/
			$statTotals = array(1=>array(),2=>array());
			$statSummaries = array(1=>array(),2=>array());
			$summary .= $this->lang->line('sim_player_rating_statload');

			foreach ($statsTypes as $typeId => $type) {
				if ($typeId == 1) {
					if ($stats_period == 1) { $table = "players_game_batting"; } else {  $table = "players_career_batting_stats"; }
					$qualifier = "ab";
					$minQualify = 3.1;
				} else {
					if ($stats_period == 1) { $table = "players_game_pitching_stats"; } else {  $table = "players_career_pitching_stats"; }
					$qualifier = "ip";
					$minQualify = 1;
				} // END if
				/*-------------------------------
				/	2.2.1 INDIVIDUAL STAT LOOP
				/------------------------------*/
				// BUILD QUERY TO PULL CURRENT GAME DATA FOR THIS PLAYER
				$ratingsCats = $ratingsCats + array($typeId => get_stats_for_ratings($typeId));
				$localStats = array();
				$statSum = "";
				foreach($ratingsCats[$typeId] as $id => $val) {
					$statSum .= "<b>Stat = ".$val."</b><br />";
					if ($stats_period == 1) {
						$tmpSelect = 'games.date, ';
					} else {
						$tmpSelect = '';
					}
					$id = intval($id);
					$stat = '';
					// FILTER OUT COMPILED STATS LIKE AVG, ERA AND WHIP
					switch($typeId) {
						case 1:
							if ($id <= 17 || $id >= 26) {
								$stat = strtolower(get_ll_cat($id, true));
							} // END if
							break;
						case 2:
							if ($id <= 39 || $id >= 43) {
								$stat = strtolower(get_ll_cat($id, true));
							} // END if
							break;
						default:
							break;
					} // END switch
					if (!empty($stat)) { 
						if ($stats_period == 1) {
							$tmpSelect .= 'SUM(g) as sum_g, SUM('.$stat.') as sum_'.$stat.', SUM('.$qualifier.') as sum_'.$qualifier; 
						} else {
							$tmpSelect .= 'g as sum_g, '.$stat.' as sum_'.$stat.', '.$qualifier.' as sum_'.$qualifier; 
						}
					}
					/*-----------------------------------------
					/	2.2.1.1 EXECUTE THE QUERY FOR THIS STAT
					/----------------------------------------*/
					$this->db->flush_cache();
					$this->db->select($tmpSelect);
					$this->db->where($table.'.player_id IN '.$players_str);
					if ($stats_period == 1) {
						$this->db->join($table,'games.game_id = '.$table.'.game_id','left');
						$this->db->where("DATEDIFF('".$period_start."',games.date)<=",0);
						$this->db->where("DATEDIFF('".$scoring_period['date_end']."',games.date)>=",0);
						$this->db->group_by($table.'.player_id');
					} else {
						$this->db->where("year",$year);
					}

					$this->db->order_by($table.'.player_id', 'asc');
					if ($stats_period == 1) {
						$query = $this->db->get($this->tables['OOTP_GAMES']); 
					} else {
						$query = $this->db->get($table);
					}
					//echo($this->db->last_query()."<br />");
					if ($query->num_rows() > 0) {
						$statCount = 0;
						$statTotal = 0;
						$statStr = 'sum_'.$stat;
						$statQalifier = 'sum_'.$qualifier;
						$statArr = array();
						foreach($query->result() as $row) {
							if (($row->$statQalifier / $row->sum_g) > $minQualify) {
								array_push($statArr,$row->$statStr);
							}
						}
						$statAvg = average($statArr);
						$statSum .= $stat." total = ".$statTotal."<br />";
						$statSum .= $stat." AVG = ".sprintf('%.3f',$statAvg)." (".$statTotal."/".$statCount.")<br />";
						$stdDevTotal = 0;
						$statDev = deviation($statArr);
						if ($statDev < 0) { $statDev = -$statDev; }
						$statSum .= $stat." STDDEV = ".$statDev."<br />";
					} // END if
					$localStats[$stat] = array('avg'=>$statAvg,'stddev'=>$statDev);
					$query->free_result();
					$statSum .= $statCount." Player Statistics met the qualified minimum.<br />";
				}
				$statSummaries[$typeId] = $statSum;
				$statTotals[$typeId] = $localStats;
			}
			$statTotalStr = str_replace('[BATTING_STAT_COUNT]',sizeof($statTotals[1]),$this->lang->line('sim_player_rating_statcount'));
			$summary .= str_replace('[PITCHING_STAT_COUNT]',sizeof($statTotals[2]),$statTotalStr);
			$summary .= "Batting Stat Details:<br />".$statSummaries[1];
			$summary .= "Pitching Stat Details:<br />".$statSummaries[2];

			$summary .= $this->lang->line('sim_players_rating_processing');
			foreach($player_list as $row) {
				$playerSum = "";
				if ($row['position'] != 1) {
					$type = 1;
					if ($stats_period == 1) { $table = "players_game_batting"; } else {  $table = "players_career_batting_stats"; }
					$qualifier = "ab";
				} else {
					$type = 2;
					if ($stats_period == 1) { $table = "players_game_pitching_stats"; } else {  $table = "players_career_pitching_stats"; }
					$qualifier = "ip";
				} // END if

				if ($stats_period == 1) {
					$select = $table.'.player_id,SUM('.$qualifier.') as sum_'.$qualifier.','; 
				} else {
					$select = $table.'.player_id,'.$qualifier.' as sum_'.$qualifier.',';
				}
				foreach($ratingsCats[$type] as $id => $val) {
					$stat = "";
					$id = intval($id);
					switch($type) {
						case 1:
							if ($id <= 17 || $id >= 26) {
								$tmpStat = strtolower(get_ll_cat($id, true));
								if ($stats_period == 1) { $stat = "SUM(".$tmpStat.") as sum_".$tmpStat; } else { $stat = "".$tmpStat." as sum_".$tmpStat; }
							} // END if
							break;
						case 2:
							if ($id <= 39 || $id >= 43) {
								$tmpStat = strtolower(get_ll_cat($id, true));
								if ($stats_period == 1) { $stat = "SUM(".$tmpStat.") as sum_".$tmpStat; } else { $stat = "".$tmpStat." as sum_".$tmpStat; }
							} // END if
							break;
						default:
							break;
					} // END switch
					if (!empty($stat)) {
						if ($select != '') { $select.=","; } // END if
						$select .= $stat;
					} // END if
				} // END foreach

				$this->db->select($select);
				$this->db->where($table.'.player_id', $row['player_id']);
				if ($stats_period == 1) {
					$this->db->join($table,'games.game_id = '.$table.'.game_id','left');
					$this->db->where("DATEDIFF('".$period_start."',games.date)<=",0);
					$this->db->where("DATEDIFF('".$scoring_period['date_end']."',games.date)>=",0);
					$this->db->group_by($table.'.player_id');
				} else {
					$this->db->where("year",$year);
				}
				$this->db->order_by($table.'.'.$qualifier,'desc');
				if ($stats_period == 1) {
					$query = $this->db->get($this->tables['OOTP_GAMES']); 
				} else {
					$query = $this->db->get($table);
				}
				$statCount = 0;
				$rating = 0;
				if ($query->num_rows() > 0) {
					$pRow = $query->row();
					$tmpQulaify = "sum_".$qualifier;
					// ONLY PROCESS THIS PLAYER IS THERE ARE GOING TO BE STATS TO PROCESS
					if ($pRow->$tmpQulaify > 0) {
						foreach($ratingsCats[$type] as $id => $val) {
							$stat = strtolower(get_ll_cat($id, true));
							$tmpStat = "sum_".$stat;
							// SKIP PLAYERS WITH NO APPEARENCES IN PLAY
							$negative = false;
							if (($type == 1 && $id == 4) || ($type == 2 && $id == 36) || ($type == 2 && $id == 37)) {
								$negative = true;
							}
							$rawRating = $pRow->$tmpStat - $statTotals[$type][$stat]['avg'];
							if ($statTotals[$type][$stat]['stddev'] != 0) {
								$upRating = $rawRating / $statTotals[$type][$stat]['stddev'];
								//print("rawRating /stdev = ".$upRating." (".$rawRating." / ".$statTotals[$type][$stat]['stddev'].")<br />");
							} else {
								$upRating = $rawRating;
							}
							if ($negative) {
								$rating -= $upRating;
							} else {
								$rating += $upRating;
							}
							$statCount++;
						}
					}
				}
				$query->free_result();
				// GET THE AVERAGE OVERALL RATING
				//if ($rating != 0 && $statCount != 0) {
				//	$rating = $rating / $statCount;
				//}
				// SAVE THE UPDATED RATING
				$this->db->flush_cache();
				$data = array('rating'=>$rating);
				$this->db->where('player_id',$row['player_id']);
				$this->db->update($this->tblName,$data);
				$processCount++;
			}
			$result = 1;
			$summary .= str_replace('[PLAYER_COUNT]',$processCount,$this->lang->line('sim_players_rating_result'));

		} else {
			$result = -1;
			$summary .= $this->lang->line('sim_players_rating_no_players');
		}
		//print("<br />".$summary."<br />");
		return array($result,$summary);
	}
	/**
	 *	UPDATE PLAYER SCORING
	 *	Loads all players for the games and processes their game stats for insertion
	 * 	into the players_compiled stats tables. This function used to live in the
	 *	league_model but was moved to the more logical location for 1.0.4 with all
	 *	scoring rules specific functionality removed.
	 *
	 *	@param	$scoring_period		Scoring Period object
	 *	@param	$ootp_league_id		OOTP League ID value, defaults to 100 if no value passed
	 *	@return						Summary String
	 *	@since						1.0.4
	 *	@version					1.1 (Revised OOTPFL 1.0.4)
	 *	@see						Controller->Admin->processSim()
	 *	@see						Models->League_Model->updateLeagueScoring()
	 *
	 */
	public function updatePlayerScoring($scoring_period = false, $ootp_league_id = 100) {

		if (($scoring_period === false|| sizeof($scoring_period) < 1)) { return false; } // END if

		$this->lang->load('admin');

		/*--------------------------------------
		/
		/	1.0 ARRAY PREP
		/
		/-------------------------------------*/

		/*--------------------------------------
		/	1.1 GET PLAYERS
		/-------------------------------------*/
		//$player_list = $this->getActiveOOTPPlayers(false, 'all', false, $scoring_period['id']);

		$player_list = $this->getOOTPPlayers();
		//echo("Size of player list = ".sizeof($player_list)."<br />");
		$summary = $this->lang->line('sim_player_scoring');

		/*--------------------------------------
		/	1.2 CREATE AND STORE SQL SELECT VALUES
		/-------------------------------------*/
		$selectArr = array();
		$statsTypes = array(1=>'batting',2=>'pitching');
		$statCats = array(); 
		$statsToQuery = array(1=>array(),2=>array());
		foreach ($statsTypes as $typeId => $type) {
			$statCats = $statCats + array($typeId => get_stats_for_scoring($typeId));
			$select = "player_id";
			foreach($statCats[$typeId] as $id => $val) {
				$stat = "";
				$id = intval($id);
				switch($typeId) {
					case 1:
						if ($id <= 17 || $id >= 26) {
							$stat = strtolower(get_ll_cat($id, true));
							$statsToQuery[$typeId] = $statsToQuery[$typeId] + array($id=>$stat);
						} // END if
						break;
					case 2:
						if ($id <= 39 || $id >= 43) {
							$stat = strtolower(get_ll_cat($id, true));
							$statsToQuery[$typeId] = $statsToQuery[$typeId] + array($id=>$stat);
						} // END if
						break;
					default:
						break;
				} // END switch
				if (!empty($stat)) {
					if ($select != '') { $select.=","; } // END if
					$select .= $stat;
				} // END if
			} // END foreach
			$selectArr = $selectArr + array($typeId=>$select);
		} // END foreach
		if (!function_exists('getOOTPTeams')) {
			$this->load->helper('datalist');
		}
		$ootpTeamList = getOOTPTeams($ootp_league_id,false);
		//echo("sizeof ootp team list = ".sizeof($ootpTeamList)."<br />");
		
		// GET LEAGUE COUNT
		$this->db->select('id');
		$this->db->from('fantasy_leagues');
		$this->db->where('league_status',1);
		$league_count = $this->db->count_all_results();
		//echo("Number of fantasy leagues = ".$league_count."<br />");
		//echo("Size of player list to review = ".sizeof($player_list)."<br />");
		/*--------------------------------------
		/
		/	2.0 PLAYER LOOP
		/
		/-------------------------------------*/
		if (sizeof($player_list) > 0) {
			$ruleType = "batting";
			$summary .= str_replace('[PLAYER_COUNT]',sizeof($player_list),$this->lang->line('sim_player_count'));
			$processCount = 0;
			foreach($player_list as $row) {
				//echo("*** STARTING PROCESSING FOR PLAYER ".$row['id']." ***<br />");
				//echo("PLAYER ".$row['id']." current STATS = ".$row['player_status']."<br />");
				
				/*-------------------------------------------------------------------
				/
				/	2.1 PLAYER SCORING for the SIM
				/
				/-------------------------------------------------------------------*/
				$score_vals = array();
				// GET ALL THE TEAMS THIS PLAYER HAS BEEN ROSTERED ON THIS SEASON
				$allTeamList = $this->getAllPlayersTeams($row['id']);
				//echo("Number of teams player has been rostered on for season = ".sizeof($allTeamList)."<br />");
				if (sizeof($allTeamList) > 0) {
					// GET ALL TEAMS THIS PLAYER IS ROSTERED ON THIS SCORING PERIOD
					$activeTeamList = $this->getCurrentPlayersTeams($row['id'], $scoring_period['id']);
					//echo("Number of ACTIVE team player appears on for season = ".sizeof($activeTeamList)."<br />");
					// ONLY GET DATA FOR PLAYER IF THEIR CURRENT STATUS IS ACTIVE OR DL (I.E. Might be active at some point)
					if (($row['player_status'] == 1 || $row['player_status'] == 3) && sizeof($activeTeamList) > 0) {
						/*-------------------------------
						/	2.1.1 GET GAME DATA
						/------------------------------*/
						// BUILD QUERY TO PULL CURRENT GAME DATA FOR THIS PLAYER
						$game_list = array();
						if ($row['position'] != 1) {
							$type = 1;
							$ruleType = "batting";
							$table = "players_game_batting";
						} else {
							$type = 2;
							$ruleType = "pitching";
							$table = "players_game_pitching_stats";
						} // END if
						$this->db->flush_cache();
						$this->db->select($selectArr[$type]);
						//echo("Stats select query for ".$type." = ".$selectArr[$type]);
						$this->db->join($table,'games.game_id = '.$table.'.game_id','left');
						$this->db->where($table.'.player_id',$row['player_id']);
						$this->db->where("DATEDIFF('".$scoring_period['date_start']."',games.date)<=",0);
						$this->db->where("DATEDIFF('".$scoring_period['date_end']."',games.date)>=",0);
						$this->db->where($table.'.level_id', 1);
						$query = $this->db->get($this->tables['OOTP_GAMES']);
						//echo("SQL Query = <br />");
						//echo("Last Query = ".$this->db->last_query()."<br />");
						$summary .= "Num of games found for player ".$row['first_name']." ".$row['last_name']." = ".$query->num_rows() .", status = ".$row['player_status']."<br/>";
						//echo("Num of games found for player ".$row['first_name']." ".$row['last_name']." = ".$query->num_rows() .", status = ".$row['player_status']."<br/>");
						if ($query->num_rows() > 0) {
							$game_list = $query->result();
						} // END if
						$query->free_result();
						/*-------------------------------
						/	2.1.2 COMPILE THE STATS
						/------------------------------*/
						if (sizeof($game_list) > 0) {
							
							$gameCount = 0;
							foreach ($game_list as $sRow) {
							// EDIT - 1.0.4 - ROTISSERIE SCORING UPDATES
							// REVAMPING THE WAY WE THINK ABOUT PLAYER SCORING
							// INSTEAD OF APPLYING LEAGUE SCORING AT THIS LEVEL, WE'LL SIMPLY PULL AND
							// COMPILE PLAYER STATS FOR THIS SCORING PERIOD
								foreach ($statsToQuery[$type] as $id => $stat) {
									$colName = strtolower(get_ll_cat($id, true));
									if (isset($score_vals[$colName])) {
										$score_vals[$colName] += $sRow->$colName;
									} else {
										$score_vals[$colName] = $sRow->$colName;
									} // END if
								} // END foreach
								$gameCount++;
							} // END foreach
							/*-------------------------------
							/	2.1.2.1 SAVE COMPILED STATS
							/------------------------------*/
							if (sizeof($score_vals) > 0) {
								if ($row['position'] != 1) $score_vals['g'] = $gameCount;
								/*echo("Compiled stats for player ".$row['id']."<br />");
								foreach($score_vals as $key => $val) {
									echo($key." = ".$val."<br />");
								}*/
								//echo("Player ".$row['id']." has ".sizeof($score_vals)." stats for this period<br />");
								// SET THIS PERIODS COMPILED STATS FOR SAVING FOR TEAMS
								$this->db->flush_cache();
								$this->db->select('id');
								$this->db->where('player_id',$row['id']);
								$this->db->where('scoring_period_id',$scoring_period['id']);
								$tQuery = $this->db->get('fantasy_players_compiled_'.$ruleType);
								if ($tQuery->num_rows() == 0) {
									$this->db->flush_cache();
									$score_vals['player_id'] = $row['id'];
									$score_vals['scoring_period_id'] = $scoring_period['id'];
									$this->db->insert('fantasy_players_compiled_'.$ruleType,$score_vals);
								} else {
									$this->db->flush_cache();
									$this->db->where('player_id',$row['id']);
									$this->db->where('scoring_period_id',$scoring_period['id']);
									$this->db->update('fantasy_players_compiled_'.$ruleType,$score_vals);
								} // END if
								$tQuery->free_result();
							} // END if
						} // END if sizeof($game_list) > 0
					} // END if ($row->player_status == 1) {
					/*----------------------------------------------------------
					/	2.1.3 SAVE COMPILED STATS FOR PLAYER FOR THEIR TEAMS
					/----------------------------------------------------------*/
					//echo("Active team list for player ".$row['id']." = ".sizeof($activeTeamList)."<br />");
					// GET ALL SAVED COMPILED STATS FOR THE PLAYER
					$prevTeamStats = $this->getPlayerCompiledStats($row['id'], $scoring_period['id'], $row['position']);
					//echo("Size of TEAM STATS list for player ".$row['id']." = ".sizeof($prevTeamStats)."<br />");
					if ($row['position'] != 1) {
						$table = $this->tables['COMPILED_TEAM_STATS_BATTING'];
					} else {
						$table = $this->tables['COMPILED_TEAM_STATS_PITCHING'];
					} // END if
					/*----------------------------------------------------------
					/	2.1.4 LOOP THROUGH ALL THE PLAYERS FANTASY TEAMS
					/---------------------------------------------------------*/
					//echo("Number of players teams they appear on for season = ".sizeof($allTeamList)."<br />");
					foreach ($allTeamList as $listItem) {
						$newTeamStats = array();
						/*----------------------------------------------------------------------
						/	2.1.4.1 LOAD THE PREVIOUS STATS TO NEW STATS ARRAY FOR CURRENT TEAM
						/----------------------------------------------------------------------*/
						//echo("Number of players teams they appear on for season = ".sizeof($allTeamList)."<br />");
						if(sizeof($prevTeamStats) > 0) {
							foreach($prevTeamStats as $teamId => $teamData) {
								if ($teamId = $listItem['team_id']){
									foreach($teamData as $item) {
										foreach ($item as $key=>$value) {
											if ($key != "id") $newTeamStats[$key] = $value;
										}
									} // END foreach
									break;
								} // END if
							} // END foreach
						}
						/*------------------------------------------------------------------------------
						/	2.1.4.2 IF PLAYER IS ACTIVE ON THIS TEAM, ADD THIS PERIODS STATS RESULTS
						/------------------------------------------------------------------------------*/
						//echo("Checking active team stats");
						if(sizeof($activeTeamList) > 0) {
							foreach($activeTeamList as $tmpTeam) {
								if ($tmpTeam['team_id'] == $listItem['team_id'] && $tmpTeam['player_status'] == 1){
									if (sizeof($score_vals) > 0) {
										foreach ($statsToQuery[$type] as $id => $stat) {
											$colName = strtolower(get_ll_cat($id, true));
											if (isset($newTeamStats[$colName])) {
												$newTeamStats[$colName] += $score_vals[$colName];
											} else {
												$newTeamStats[$colName] = $score_vals[$colName];
											} // END if
										} // END foreach
									} // END if
									break;
								} // END if
							} // END foreach
						}
						//echo("Updated compiled newTeamStats for player = ".$row['id']."<br />");
						/*foreach($newTeamStats as $key => $val) {
							echo($key." = ".$val."<br />");
						}*/
						/*------------------------------------------------------------------------------
						/	2.1.4.3 SAVE COMPILED STATS VALUE FOR THIS TEAM
						/------------------------------------------------------------------------------*/
						//echo("Saving new compiled stats for player".$row['id']."<br />");
						$this->db->flush_cache();
						$this->db->select('id');
						$this->db->where('player_id',$row['id']);
						$this->db->where('team_id',$listItem['team_id']);
						$this->db->where('league_id',$listItem['league_id']);
						$this->db->where('scoring_period_id',($scoring_period['id']));
						$psQuery = $this->db->get($table);
						if ($psQuery->num_rows() > 0) {
							$row = $psQuery->row();
							$this->db->flush_cache();
							$this->db->where('id',$row->id);
							$this->db->update($table,$newTeamStats);
						// NO ENTRY FOR THIS TEAM SO ADD IT
						} else {
							$this->db->flush_cache();
							$newTeamStats['player_id'] = $row['id'];
							$newTeamStats['team_id'] = $listItem['team_id'];
							$newTeamStats['league_id'] = $listItem['league_id'];
							$newTeamStats['scoring_period_id'] = $scoring_period['id'];
							$newTeamStats['ootp_player_id'] = $row['player_id'];
							$this->db->insert($table,$newTeamStats);
						} // END if
						$psQuery->free_result();
						//echo("Stats saved<br />");
					} // END foreach ($allTeamList as $listItem) 
				} // END if (sizeof($allTeamList) > 0)
				/*-------------------------------------------------------------------------
				/	2.2 - UPDATE PLAYER ROSTER STATUS
				/	New to 1.1.1, moved from roster_helper -> update_player_availability()
				/	CHECKS THE PLAYERS TEAM, INJURY AND RETIREMENT STATUS AND UPDATES
				/-------------------------------------------------------------------------*/
				//echo("Updating Status<br />");
				$status = $row['is_active'] == 1 ? 1 : -1;
				$teamFound = false;
				foreach($ootpTeamList as $id => $data) {
					if ($row['team_id'] == $id) {
						$teamFound = true;
						break;
					}
				}
				if (!$teamFound)
					$status = 2;
				if($teamFound && $status == -1) {
					$status = 5;
				}
				if ($row['injury_is_injured'] == 1 && ($row['is_on_dl'] == 1 || $row['is_on_dl60'] == 1 || $row['injury_dl_left'] > 0 || $row['injury_career_ending'] == 1)) {
					$status = 3;
				}
				if ($row['retired'] == 1) {
					$status = 4;
				}
				//echo("Set status for player= ".$row['id']." to ".$status."<br />");
				/*-------------------------------------------------------------------------
				/	2.3 - PLAYER OWNERSHIP
				/	New to 1.1.1, moved from roster_helper -> updateOwnership()
				/	UPDATES OWN AND START VALUES FOR THE PLAYER BASED ON ROSTERS
				/-------------------------------------------------------------------------*/
				//echo("UPdating player ownership in league<br />");
				$own = 0;
				$start = 0;
				if(sizeof($activeTeamList) > 0) {
					foreach($activeTeamList as $tmpTeam) {
						$own++;
						if( $tmpTeam['player_status'] == 1) {
							$start++;
						}
					}
				}
				if ($league_count > 0) {
					$own = ($own == $league_count) ? 100 : (($own / $league_count) * 100);
					if ($own > 100) $own = 100;
					$start = ($start == $league_count) ? 100 : (($start / $league_count) * 100);
					if ($start > 100) $start = 100;
				}
				//echo("Updated ownership for player ".$row['id']." own = ".$own.", start = ".$start."<br />");
				/*-------------------------------------------------------------------------
				/	2.4 SAVE UPDATED PLAYER DETAILS TO FANTASY PLAYERS TABLE
				/-------------------------------------------------------------------------*/
				$pData = array('player_status'=>$status, 'own_last'=>$row['own'],
							   'start_last'=>$row['start'],'own'=>$own,'start'=>$start);
				//$this->db->flush_cache();
				$this->db->where('id',$row['id']);
				$this->db->update('fantasy_players',$pData); 
				//echo("Status updates for player ".$row['id']." written sucessfully.<br />");
				//echo("----------------------------------<br />");
				/*------------------------------------
				/	2.1.5 Advance Count
				/-----------------------------------*/
				$processCount++;
			} // END foreach ($player_list as $row)
			$summary .= str_replace('[PLAYER_COUNT]',$processCount,$this->lang->line('sim_players_processed_result'));
		} else {
			/*-------------------------------
			/	2.2 HANDLE NO PLAYERS ERROR
			/------------------------------*/
			$this->errorCode = 2;
			$this->statusMess = "No players were found.";
			return false;
		} // END if sizeof($player_list) > 0
		return $summary;
	}

	/**
	 * 	GET ALL PLAYERS TEAMS
	 * 	Return the list of ALL teams the player has been rostered on for the season, 
	 * 	regardless of Scoring period id
	 * 
	 *  @param		player_id	Players Fantasy Player ID
	 *  @return 	array		List of players team and league IDs
	 * 	@since		1.1.1
	 */
	public function getAllPlayersTeams($player_id = false) {
		if ($player_id === false)  return false; 

		$teamList = array();
		$this->db->flush_cache();
		$this->db->select('team_id,league_id');
		$this->db->where('player_id', $player_id);
		$this->db->group_by('team_id');
		$query = $this->db->get($this->tables['ROSTERS']);
		//echo("Last Query = ".$this->db->last_query()."<br />");
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				array_push($teamList, array('team_id'=>$row->team_id, 'league_id'=>$row->league_id));
			}
		} // END if
		$query->free_result();
		return $teamList;
	}

	/**
	 * 	GET ACTIVE PLAYERS TEAMS
	 * 	Return the list of ACTIVE teams the player has been rostered on for the season, 
	 * 	based on the current Scoring period id
	 * 
	 *  @param		player_id			Players Fantasy Player ID
	 *  @param		scoring_period_id	Current Scoring Period ID
	 *  @return 	array				List of players team and league IDs
	 * 	@since		1.1.1
	 */
	public function getCurrentPlayersTeams($player_id = false, $scoring_period_id = false) {
		if ($player_id === false)  return false; 

		$teamList = array();
		$this->db->flush_cache();
		$this->db->select('team_id,league_id, player_status');
		$this->db->where('player_id', $player_id);
		$this->db->where('scoring_period_id', $scoring_period_id);
		//$this->db->where('player_status', 1);
		$query = $this->db->get($this->tables['ROSTERS']);
		//echo("Last Query = ".$this->db->last_query()."<br />");
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				array_push($teamList, array('team_id'=>$row->team_id, 'league_id'=>$row->league_id, 'player_status'=>$row->player_status));
			}
		} // END if
		$query->free_result();
		return $teamList;
	}

	/**
	 * 	GET PLAYERS COMPILED STATS
	 * 	Return the list of ACTIVE teams the player has been rostered on for the season, 
	 * 	based on the current Scoring period id
	 * 
	 *  @param		player_id			Players Fantasy Player ID
	 *  @param		scoring_period_id	Current Scoring Period ID
	 *  @return 	array				List of players team and league IDs
	 * 	@since		1.1.1
	 */
	public function getPlayerCompiledStats($player_id = false, $scoring_period_id = false, $position = 1) {
		if ($player_id === false)  return false; 

		//echo("Getting previous stats for Player ".$player_id."'s teams<br />");
		$teamStats = array();
		$this->db->flush_cache();
		$this->db->where('player_id', $player_id);
		$this->db->where('scoring_period_id', $scoring_period_id - 1);
		if ($position != 1) {
			$table = $this->tables['COMPILED_TEAM_STATS_BATTING'];
		} else {
			$table = $this->tables['COMPILED_TEAM_STATS_PITCHING'];
		} // END if
		$query = $this->db->get($table);
		//echo("Stats Query = ".$this->db->last_query()."<br />");
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				$tmpStats = array();
				$teamId = -1;
				foreach($row as $key => $val) {
					$tmpStats[$key] = $val;
					if ($key == "team_id") { $teamId = $val; }
				}
				array_push($teamStats, array($teamId => $tmpStats));
			}
		} // END if
		$query->free_result();
		//echo("Number of teams returned = ".sizeof($teamStats)."<br />");
		return $teamStats;
	}

	/**
	 *	GET CAREER STATS.
	 *	Returns players career statistics.
	 *
	 *	@param	$ootp_league_id		OOTP League ID value
	 *	@param	$player_id			Player Id, defaults to current player id if empty
	 *	@return						Stat Array
	 *	@since						1.0
	 *	@version					1.0.2
	 *
	 */
	public function getCareerStats($ootp_league_id, $player_id = false) {
		if ($player_id === false) { $player_id = $this->player_id; }

		$career_stats = array();
		// GET PLAYER POSITION
		$pos = $this->getOOTPPlayerPosition();

		$this->db->flush_cache();
		if ($pos == 1) {
			$sql="SELECT pcp.year,pcp.team_id,g,gs,w,l,s,(ip*3+ipf)/3 as ip,ha,r,er,hra,bb,k,hld,cg,sho,ab,sf,war";
			$sql.=",bf,pi,qs,gf,gb,fb,wp,bk,svo,bs";     ## Expanded Stats
			$sql.=" FROM players_career_pitching_stats as pcp WHERE player_id=$player_id";
			$sql.=" AND league_id=$ootp_league_id AND split_id=1";
			$sql.=" ORDER BY pcp.year;";
		} else {
			$sql="SELECT pcb.year,pcb.team_id,g,ab,h,d,t,hr,rbi,r,bb,hp,sh,sf,k,sb,cs,pa,war";
			$sql.=",pitches_seen,ibb,gdp";
			$sql.=" FROM players_career_batting_stats as pcb WHERE player_id=$player_id";
			$sql.=" AND league_id=$ootp_league_id AND split_id=1";
			$sql.=" ORDER BY pcb.year;";
		} // END if
		$query = $this->db->query($sql);
		if ($query->num_rows > 0) {
			$fields = $query->list_fields();
			foreach($query->result() as $row) {
				$year = array();
				foreach($fields as $field) {
					$year[$field] = $row->$field;
				} // END foreach
				array_push($career_stats,$year);
			} // END foreach
		} // END if
		$query->free_result();
		return $career_stats;
	}
	/**
	 *	GET PLAYER AWARDS.
	 *	Returns all awards won by the players broken out by award type.
	 *
	 *	@param	$ootp_league_id		OOTP League ID value
	 *	@param	$player_id			Player Id, defaults to current player id if empty
	 *	@return						Award Array
	 *	@since						1.0
	 *	@version					1.0.1
	 *
	 */
	public function getPlayerAwards($ootp_league_id, $player_id = false) {

		if ($player_id === false) { $player_id = $this->player_id; }

		$awards = array();
		$this->db->select("award_id,year,position");
		$this->db->from('players_awards');
		$this->db->where('league_id',$ootp_league_id);
		$this->db->where('player_id',$player_id);
		$this->db->where_in('award_id',array(4,5,6,7,9));
		$this->db->order_by('award_id','award_id,year,position');
		$query = $this->db->get();
		$prevAW=-1;
		$cnt=0;
		if ($query->num_rows > 0) {
			$awardsByYear = array();
			$poy = array();
			$boy = array();
			$roy = array();
			$gg = array();
			$as = array();
			foreach($query->result_array() as $row) {
				$awid=$row['award_id'];
				$yr=$row['year'];
				$pos=$row['position'];
				if ($prevAW!=$awid) {
					$awardsByYear[$awid]=$yr;
				} else {
					$awardsByYear[$awid]=$awardsByYear[$awid].", ".$yr;
				} // END if

				switch ($awid) {
					case 4: $poy[$yr]=1; break;
					case 5: $boy[$yr]=1; break;
					case 6: $roy[$yr]=1; break;
					case 7: $gg[$yr][$pos]=1; break;
					case 9: $as[$yr]=1; break;
				} // END switch
				$cnt++;
				$prevAW=$awid;


			} // END foreach
			$awards['byYear'] = $awardsByYear;
			$awards['poy'] = $poy;
			$awards['boy'] = $boy;
			$awards['roy'] = $roy;
			$awards['gg'] = $gg;
			$awards['as'] = $as;
		} // END if
		return $awards;
	}
	public function getRecentGameStats($ootp_league_id, $last_date, $lgyear, $days = 7, $player_id = false) {

		if ($player_id === false) { $player_id = $this->player_id; }

		// GET ALL TEAMS
		$teams = array();
		$this->db->select("team_id, abbr");
		$this->db->where("league_id",$ootp_league_id);
		$query = $this->db->get("teams");
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				$teams[$row->team_id] = $row->abbr;
			}
		}
		$query->free_result();

		$stats = array();
		$pos = 0;
		$team_id = $this->getOOTPTeam();
		// GET PLAYER POSITION
		$pos = $this->getOOTPPlayerPosition();

		$select = "games.date,games.home_team,games.away_team,";
		$this->db->flush_cache();
		if ($pos == 1) {
			$this->db->select($select.'w,l,s,ip,ha,er,bb,k');
			$table = 'players_game_pitching_stats';
		} else {
			$this->db->select($select.'ab,r,h,hr,rbi,bb,sb');
			$table = 'players_game_batting';
		}
		$this->db->from($table);
		$this->db->join($this->tables['OOTP_GAMES'],$table.".game_id = games.game_id",'left');
		$this->db->where($table.'.player_id',$player_id);
		$this->db->where($table.'.year',$lgyear);
		$this->db->where($table.'.level_id',1);
		$this->db->where('games.game_type',0);
		$this->db->where("(games.home_team = ".$team_id." OR games.away_team = ".$team_id.")");
		$this->db->where("DATEDIFF('".$last_date."',games.date) > ",0);
		$this->db->order_by("games.date",'desc');
		$query = $this->db->get();
		$fields = $query->list_fields();
		if ($query->num_rows() > 0) {
			$count = 0;
			foreach($query->result() as $row) {
				$game = array();
				foreach($fields as $field) {
					if ($field != 'home_team' && $field != 'away_team') {
						$game[$field] = $row->$field;
					}
				}
				if ($row->home_team == $team_id) {
					if (isset($teams[$row->away_team])) {
						$game['opp'] = $teams[$row->away_team];
					} else {
						$game['opp'] = "?";
					}
				} else if ($row->away_team == $team_id) {
					if (isset($teams[$row->home_team])) {
						$game['opp'] = $teams[$row->home_team];
					} else {
						$game['opp'] = "?";
					}
				} else {
					$game['opp'] = "?";
				}
				array_push($stats,$game);
				$count++;
				if ($count >= $days) break;
			}
		}
		return $stats;

	}
	public function getCurrentStats($ootp_league_id, $lgyear, $player_id = false) {
		if ($player_id === false) { $player_id = $this->player_id; }

		$stats = array();
		$pos = 0;
		// GET PLAYER POSITION
		$pos = $this->getOOTPPlayerPosition();

		$this->db->flush_cache();
		if ($pos == 1) {
			$this->db->select('ip,w,l,s,k,bb,er,ha,hra');
			$this->db->from('players_career_pitching_stats');
		} else {
			$this->db->select('ab,r,hr,rbi,bb,k,sb,h,d,t');
			$this->db->from('players_career_batting_stats');
		}
		$this->db->where('player_id',$player_id);
		$this->db->where('split_id',1);
		$this->db->where('year',$lgyear);
		$this->db->where('league_id',$ootp_league_id);
		$this->db->where('level_id',1);
		$query = $this->db->get();
		$fields = $query->list_fields();
		if ($query->num_rows() > 0) {
			$row = $query->row();
			foreach($fields as $field) {
				$stats[$field] = $row->$field;
			}
		}
		return $stats;
	}
	/**
	 *  GET PLAYERS HAVE RATINGS
	 * 	Tests if Player Ratings have been run by counting records not
	 *  equal to 0 meaning ratigns have been run. It could also mean 
	 *  that there are no stats to run ratings against.
	 * 
	 * 	@return		{int} 	Count of records <> 0
	 *  @since		1.0.3 PROD
	 *  
	 */
	public function getPlayersHaveRatings() {
		
		$count = 0;

		$this->db->flush_cache();
		$this->db->select('COUNT(id) as playerCount');
		$this->db->where('rating <> 0');
		$query = $this->db->get($this->tblName);
		if ($query->num_rows() > 0) {
			$row = $query->row();
			$count = $row->playerCount;
		}
		return $count;
	}
}
