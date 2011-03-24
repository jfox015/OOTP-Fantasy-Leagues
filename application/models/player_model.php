<?php
/**
 *	PLAYER MODEL CLASS.
 *	
 *	@author			Jeff Fox <jfox015 (at) gmail (dot) com>
 *  @copyright   	(c)2009-11 Jeff Fox/Aeolian Digital Studios
 *	@version		1.0
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
	
	var $player_id = -1;
	var $player_status = -1;
	
	function player_model() {
		parent::__construct();
		
		$this->tblName = 'fantasy_players';
		$this->tables['ROSTERS'] = 'fantasy_rosters';
		$this->tables['WAIVERS'] = 'fantasy_players_waivers';
		$this->tables['WAIVER_CLAIMS'] = 'fantasy_teams_waiver_claims';
		$this->tables['FANTASY_TEAMS'] = 'fantasy_teams';
		
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
	public function getPlayerDetails($player_id = false) {
		
		if ($player_id === false) { return; }
		$details = array();
		
		$this->db->select('fantasy_players.id,fantasy_players.player_id,players.first_name,players.last_name,players.nick_name as playerNickname,teams.team_id, teams.name AS team_name, teams.nickName as teamNickname, positions, position,role, date_of_birth,weight,height,bats,throws,draft_year,draft_round,draft_pick,draft_team_id,retired,
						  injury_is_injured, injury_dtd_injury, injury_career_ending, injury_dl_left, injury_left, injury_id, logo_file, players.city_of_birth_id, age, own, own_last, start, start_last');
		$this->db->join('players','players.player_id = fantasy_players.player_id','left');
		$this->db->join('teams','teams.team_id = players.team_id','right outer');
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
				$this->db->select('cities.name as birthCity, cities.region as birthRegion, nations.short_name as birthNation');
				$this->db->join('nations','nations.nation_id = cities.nation_id','right outer');
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
				$this->db->where('player_id',$player_id);
				$this->db->where('league_id',$league_id);
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
	
	public function getFantasyTeam($player_id = false, $scoring_period_id = false) {
		
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
	public function getActiveOOTPPlayers($league_id = false,$searchType = 'all', $searchParam = false,$current_scoring_period = 1, $nonFreeAgents = array()) {
		return $this->getOOTPPlayers($league_id,$searchType, $searchParam,$current_scoring_period, $nonFreeAgents,1);
	}
	public function getOOTPPlayers($league_id = false,$searchType = 'all', $searchParam = false,$current_scoring_period = 1, $nonFreeAgents = array(), 
								  $playerStatus = false, $selectBox = false) {
		$players = array();
			
		$this->db->select('fantasy_players.id,fantasy_players.player_id, fantasy_players.player_status, first_name, last_name, fantasy_players.positions, players.position, players.role, players.injury_is_injured, players.injury_dtd_injury, players.injury_career_ending, players.injury_dl_left, players.injury_left, players.injury_id, ');
		$this->db->from('fantasy_players');
		$this->db->join('players','players.player_id = fantasy_players.player_id','left');
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
		if ($playerStatus  !== false) {
			$this->db->where('fantasy_players.player_status',$playerStatus);
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
	 *	@since	1.0.1.9
	 *	@param	$playerType			1= Batters, 2 = Pitchers
	 *	@parsm	$scoring_period		Scoring Period Array object
	 *	@param	$rules				Scoring Rules Array
	 *	@param	$players			Players List Array, generated by TeamModel->GetBatters() or TeamModel->getPitchers()
	 *	@param	$excludeList		Array of Player Ids to exclude
	 *	@param	$batting_sort		Batting Stats sort column
	 *	@param	$pitching_sort 		Pitching Stats sort column
	 *	@return						Stats array object
	 *	@see	TeamModel
	 *	
	 */
	public function getStatsforPeriod($playerType = 1, $scoring_period = array(), $rules = array(), 
									  $players = array(),$excludeList = array(), $searchType = 'all', $searchParam = false,
									  $query_type = QUERY_STANDARD, $stats_range = -1, $limit = -1, $startIndex = 0, 
									  $batting_sort = false, $pitching_sort = false) {
		$stats = array();
	
		$playerList = "(";
		if (is_array($players) && sizeof($players) > 0) {
			foreach($players as $player_id => $playerData) {
				if ($playerList != "(") { $playerList .= ","; }
				$playerList .= $player_id;
			}
		}
		$playerList .= ")";
				
		$excludeLostStr = "(";
		if (is_array($excludeList) && sizeof($excludeList) > 0) {
			foreach($excludeList as $player_id) {
				if ($excludeLostStr != "(") { $excludeLostStr .= ","; }
				$excludeLostStr .= $player_id;
			}
		}
		$excludeLostStr .= ")";		
				
		$order = 'fpts';
		// BUILD QUERY TO PULL CURRENT GAME DATA FOR THIS PLAYER
		$sql = 'SELECT fantasy_players.id, fantasy_players.positions, players.position, players.role, players.player_id ,first_name, last_name,players.injury_is_injured, players.injury_dtd_injury, players.injury_career_ending, players.injury_dl_left, players.injury_left, players.injury_id,';
		$sql .= player_stat_query_builder($playerType, $query_type, $rules);
		if ($playerType == 1) {
			$sql .= ",players.position as pos ";
			$tblName = 'players_game_batting';
			$posType = 'players.position';
			if ($batting_sort !== false) $order = $batting_sort;
		} else {
			$sql .= ",players.role as pos ";
			$tblName = 'players_game_pitching_stats';
			$posType = 'players.role';
			if ($pitching_sort !== false) $order = $pitching_sort;
		}
		$sql .= "FROM games ";
		$sql .= 'LEFT JOIN '.$tblName.' ON games.game_id = '.$tblName.'.game_id ';
		$sql .= 'RIGHT OUTER JOIN players ON players.player_id = '.$tblName.'.player_id ';
		$sql .= 'RIGHT OUTER JOIN fantasy_players ON players.player_id = fantasy_players.player_id ';
		if (sizeof($scoring_period) > 0 && $stats_range == -1) {
			$sql .= "WHERE DATEDIFF('".$scoring_period['date_start']."',games.date)<= 0 ";
			$sql .= "AND DATEDIFF('".$scoring_period['date_end']."',games.date)>= 0 ";
		} else if (sizeof($scoring_period) == 0 && $stats_range != -1) {
			$year_time = (60*60*24*365);
			if ($stats_range != 4) {
				$sql .= ' AND games.year = '.date('Y',time()-($year_time * $stats_range));
			} else {
				$sql .= ' AND (games.year = '.date('Y',time()-($year_time)).' OR games.year = '.date('Y',time()-($year_time * 2)).' OR games.year = '.date('Y',time()-($year_time * 3)).")";
			}
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
			$sql .= "AND ".$tblName.".player_id IN ".$playerList.' ';
		}
		if ($excludeLostStr != "()") {
			$sql .= "AND ".$tblName.".player_id NOT IN ".$excludeLostStr.' ';
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
		$sql="SELECT 'add','teamname', fantasy_players.id,fantasy_players.positions, players.player_id, players.position as position, players.role as role, players.first_name, players.last_name, players.injury_is_injured, players.injury_dtd_injury, players.injury_career_ending, players.injury_dl_left, players.injury_left, players.injury_id,";
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
			$order = 'fpts';	
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
	 *	UPDATE PLAYER SCORING
	 *	Accepts a set of scoring rules (stat categories and pfantasy pont values for h2h leagues) and 
	 *	processes them for insertion into the players_scoring table. This function used to live in the 
	 *	league_model but was moved to the more logical location for 1.0.4.
	 *
	 *	@param	$scoring_rules		Array of scoring rules to process against
	 *	@param	$scoring_period		Scoring Period object
	 *	@param	$ootp_league_id		OOTP League ID value, defaults to 100 if no value passed
	 *	@return						Summary String
	 *	@since						1.0.4
	 *	@version					1.1 (Revised OOTPFL 1.0.4)
	 *	@see						Controller->Admin->processSim()
	 *	@see						Models->League_Model->updateLeagueScoring()
	 *
	 */
	public function updatePlayerScoring($scoring_rules = false, $scoring_period = false, $ootp_league_id = 100) {
		
		if (($scoring_rules === false|| sizeof($scoring_rules) < 1) || 
			($scoring_period === false|| sizeof($scoring_period) < 1)) { return false; }
		
		$this->lang->load('admin');
		
		$player_list = $this->getActiveOOTPPlayers();
		$summary = $this->lang->line('sim_player_scoring');
		
		// CREATE AND STORE SELECT ARRAY
		$selectArr = array();
		$statsTypes = array(1=>'batting',2=>'pitching');
		foreach ($statsTypes as $typeId => $type) {
			$stats = get_stats_for_scoring($typeId);
			$select = "player_id";
			foreach($stats as $id => $val) {
				if ($select != '') { $select.=","; } // END if
				$select .= strtolower(get_ll_cat($id, true));
			} // END foreach
			$selectArr = $selectArr + array($typeId=>$select);
		} // END foreach
		
		if (sizeof($player_list) > 0) {
			$ruleType = "batting";
			$summary .= str_replace('[PLAYER_COUNT]',sizeof($player_list),$this->lang->line('sim_player_count'));										
			$processCount = 0;
			foreach($player_list as $row) {
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
				$this->db->join($table,'games.game_id = '.$table.'.game_id','left');
				$this->db->where($table.'.player_id',$row['player_id']);
				$this->db->where("DATEDIFF('".$scoring_period['date_start']."',games.date)<=",0);
				$this->db->where("DATEDIFF('".$scoring_period['date_end']."',games.date)>=",0);
				$query = $this->db->get('games');
				//$summary .= "Num of games found for player ".$row['first_name']." ".$row['last_name']." = ".$query->num_rows() .", status = ".$row['player_status']."<br/>";
				//echo($this->db->last_query()."<br />");
				if ($query->num_rows() > 0) {
					$game_list = $query->result();
				} // END if
				$query->free_result(); 
				if (sizeof($game_list) > 0) {
					foreach ($scoring_rules as $id => $rules) {
						$score_vals = array();
						$totalVal = 0;
						foreach ($game_list as $sRow) {
							$colCount = 0;
							//$summary .= "ruleType = ".$ruleType." , rules[".$ruleType."] is set? ".(isset($rules[$ruleType]) ? "true" : "false")."<br />";
							// APPLY VALUES TO THE STATS AND SAVE THEM TO THE SCORING TABLE
							foreach($rules[$ruleType] as $cat => $val) {
								$fVal = 0;
								$colName = strtolower(get_ll_cat($cat, true));
								if (isset($score_vals['value_'.$colCount])) {
									$score_vals['value_'.$colCount] += $sRow->$colName;
								} else {
									$score_vals['value_'.$colCount] = $sRow->$colName;
								} // END if
								if ($sRow->$colName != 0) {
									$totalVal += $sRow->$colName * $val;
								} // END if
								$colCount++;
							} // END foreach
						} // END foreach
						$score_vals['total'] = $totalVal;
						//$summary .= "Player ".$row['player_id']." total = ".$totalVal.", status = ".$row['player_status']."	<br/>";
						//if ($row->player_status == 1) { $team_score += $totalVal; }
						//echo("Team ".$team_id." total = ".$team_score."<br/>");
						if (sizeof($score_vals) > 0) {
							$this->db->flush_cache();
							$this->db->select('id');
							$this->db->where('player_id',$row['id']);
							$this->db->where('scoring_period_id',$scoring_period['id']);
							$this->db->where('league_id',$rules['league_id']);
							$this->db->where('scoring_type',$rules['scoring_type']);
							$tQuery = $this->db->get('fantasy_players_scoring');
							if ($tQuery->num_rows() == 0) {
								$this->db->flush_cache();
								$score_vals['player_id'] = $row['id'];
								$score_vals['scoring_period_id'] = $scoring_period['id'];
								$score_vals['scoring_type'] = $rules['scoring_type'];
								$score_vals['league_id'] = $rules['league_id'];
								$this->db->insert('fantasy_players_scoring',$score_vals);
							} else {
								$this->db->flush_cache();
								$this->db->where('player_id',$row['id']);
								$this->db->where('scoring_period_id',$scoring_period['id']);
								$this->db->where('league_id',$rules['league_id']);
								$this->db->where('scoring_type',$rules['scoring_type']);
								$this->db->update('fantasy_players_scoring',$score_vals);
							} // END if
							$tQuery->free_result();
						} // END if
					} // END foreach
					$processCount++;
				}
			} // END foreach
			$summary .= str_replace('[PLAYER_COUNT]',$processCount,$this->lang->line('sim_players_processed_result'));										
		} else {
			$this->errorCode = 2;
			$this->statusMess = "Mo players were found.";
			return false;
		} // END if
		
		//print ($this->_NAME.", summary= '".$summary."'<br />");
		return $summary;
	}
	
	
	public function getCareerStats($ootp_league_id, $player_id = false) {
		if ($player_id === false) { $player_id = $this->player_id; }
		
		$career_stats = array();
		// GET PLAYER POSITION
		$pos = $this->getOOTPPlayerPosition();
		
		$this->db->flush_cache();
		if ($pos == 1) {
			$sql="SELECT pcp.year,pcp.team_id,g,gs,w,l,s,(ip*3+ipf)/3 as ip,ha,r,er,hra,bb,k,hld,cg,sho,ab,sf,vorp";
			$sql.=",bf,pi,qs,gf,gb,fb,wp,bk,svo,bs";     ## Expanded Stats
			$sql.=" FROM players_career_pitching_stats as pcp WHERE player_id=$player_id";
			$sql.=" AND league_id=$ootp_league_id AND split_id=1";
			$sql.=" ORDER BY pcp.year;";
		} else {
			$sql="SELECT pcb.year,pcb.team_id,g,ab,h,d,t,hr,rbi,r,bb,hp,sh,sf,k,sb,cs,pa,vorp";
			$sql.=",pitches_seen,ibb,gdp";
			$sql.=" FROM players_career_batting_stats as pcb WHERE player_id=$player_id";
			$sql.=" AND league_id=$ootp_league_id AND split_id=1";
			$sql.=" ORDER BY pcb.year;";
		}
		$query = $this->db->query($sql);
		if ($query->num_rows > 0) {
			$fields = $query->list_fields();
			foreach($query->result() as $row) {
				$year = array();
				foreach($fields as $field) {
					$year[$field] = $row->$field;
				}
				array_push($career_stats,$year);
			}
		}
		$query->free_result();
		return $career_stats;
	}
	
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
				}
				
				switch ($awid) {
					case 4: $poy[$yr]=1; break;
					case 5: $boy[$yr]=1; break;
					case 6: $roy[$yr]=1; break;
					case 7: $gg[$yr][$pos]=1; break;
					case 9: $as[$yr]=1; break;
				}
				$cnt++;
				$prevAW=$awid;
				
				
			}
			$awards['byYear'] = $awardsByYear;
			$awards['poy'] = $poy;
			$awards['boy'] = $boy;
			$awards['roy'] = $roy;
			$awards['gg'] = $gg;
			$awards['as'] = $as;
		}
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
		$this->db->join('games',$table.".game_id = games.game_id",'left');
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
	
	/*---------------------------------------
	/	PRIVATE/PROTECTED FUNCTIONS
	/--------------------------------------*/
}  