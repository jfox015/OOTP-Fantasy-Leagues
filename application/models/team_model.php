<?php
/**
 *	TEAM MODEL CLASS.
 *	
 *	@author		Jeff Fox
 *	@version	1.0
 *
*/
class team_model extends base_model {

	var $_NAME = 'team_model';
	
	var $league_id = -1;
	var $division_id = -1;
	var $teamname = '';
	var $teamnick = '';
	var $owner_id = -1;
	var $avatar = '';
	var $auto_draft = -1;
	var $auto_list = -1;
	var $auto_round_x = -1;
	function team_model() {
		parent::__construct();
		
		$this->tblName = 'fantasy_teams';
		$this->tables['ROSTERS'] = 'fantasy_rosters';
		$this->tables['TRANSACTIONS'] = 'fantasy_transactions';
		$this->tables['SCORING'] = 'fantasy_players_scoring';
		$this->tables['WAIVERS'] = 'fantasy_players_waivers';
		$this->tables['WAIVER_CLAIMS'] = 'fantasy_teams_waiver_claims';
		
		$this->fieldList = array('league_id','division_id','teamname','teamnick','owner_id','auto_draft','auto_list','auto_round_x');
		$this->conditionList = array('avatarFile');
		$this->readOnlyList = array('avatar');  
		$this->textList = array('teamname','teamnick');  
		
		$this->columns_select = array('id','avatar','league_id','division_id','teamname','teamnick','owner_id');
			
		parent::_init();
	}
	/*--------------------------------------------------
	/
	/	PUBLIC FUNCTIONS
	/
	/-------------------------------------------------*/
	public function applyData($input,$userId = -1) {
		$success = parent::applyData($input,$userId);
		if ($success) {
			if (isset($_FILES['avatarFile']['name']) && !empty($_FILES['avatarFile']['name'])) { 
				$success = $this->uploadFile('avatar',PATH_TEAMS_AVATAR_WRITE,$input,'avatar',$this->teamname);
			}
			if ($input->post('auto_round_x') && $input->post('auto_round_x') == -1) {
				$this->auto_round_x = 0;
			}
		}
		return $success;
	}
	public function applyRosterChanges($input, $score_period, $team_id = false) {
		
		if (!isset($input)) return; 
		
		if ($team_id === false) { $team_id = $this->id; }
		
		$roster = $this->dataModel->getBasicRoster($score_period);
		$new_roster = array();
		
		foreach($roster as $player_info) {
			// CHECK FOR STATUS UPDATE
			//echo("Player ".$player_info['id'].", posted status = ".$input->post('status_'.$player_info['id']).", status = ".$player_info['player_status']."<br />");
			if ($input->post('status_'.$player_info['id']) && $input->post('status_'.$player_info['id']) != $player_info['player_status']) {
				$player_info['player_status'] = $input->post('status_'.$player_info['id']);
			}
			if ($player_info['player_position'] == 1) {
				if ($input->post('role_'.$player_info['id']) && $input->post('role_'.$player_info['id']) != $player_info['player_role']) {
					$player_info['player_role'] = $input->post('role_'.$player_info['id']);
				}
			} else {
				if ($input->post('position_'.$player_info['id']) && $input->post('position_'.$player_info['id']) != $player_info['player_position']) {
					$player_info['player_position'] = $input->post('position_'.$player_info['id']);
				}
			}
			array_shift($player_info);
			array_push($new_roster,$player_info);
			//echo("Player update for player ".$player_info['id'].", position = ".$player_info['player_position'].", role = ".$player_info['player_role'].", status = ".$player_info['player_status']."<br />");
		}
		return $new_roster;
	}
	public function logSingleTransaction($player_id = false, $trans_type = false, $commish_id = false, 
										 $currUser = false, $isAdmin = false, $league_id = false, 
										 $team_id = false, $owner_id = false, $team_id_2 = false) {
		
		if ($commish_id === false || $player_id === false || $currUser === false || $trans_type === false) return;
		
		if ($league_id === false) $league_id = $this->league_id;
		if ($team_id === false) $team_id = $this->id;
		if ($owner_id === false) $owner_id = $this->owner_id;
		
		if ($currUser == $owner_id) {
			$trans_owner = TRANS_OWNER_OWNER;
		} else if ($currUser != $owner_id && $currUser == $commish_id) {
			$trans_owner = TRANS_OWNER_COMMISH;
		} else if ($currUser != $owner_id && $currUser != $commish_id && $isAdmin) {
			$trans_owner = TRANS_OWNER_ADMIN;
		} else {
			$trans_owner = TRANS_OWNER_OTHER;
		}
		switch ($trans_type) {
			case TRANS_TYPE_ADD:
				$field = 'added';
				break;
			case TRANS_TYPE_DROP:
				$field = 'dropped';
				break;
			case TRANS_TYPE_TRADE_TO:
				$field = 'tradedTo';
				break;
			case TRANS_TYPE_TRADE_FROM:
				$field = 'tradedFrom';
				break;
		}
		$playerStr = serialize(array($player_id));
		$data = array('team_id' =>$team_id, 'owner_id' => $owner_id, $field => $playerStr,
					  'league_id'=> $league_id, 'trans_owner'=>$trans_owner);
		
		$this->db->insert($this->tables['TRANSACTIONS'],$data);
		
		//echo("transaction logged.<br />");
		return true;
	}
	public function logTransaction($added = array(), $dropped = array(), $claimed = array(), $tradedTo = array(), $tradedFrom = array(),
									$commish_id = false, $currUser = false, $isAdmin = false, $effective = -1, 
									$league_id = false, $team_id = false, $owner_id = false) {
		
		if (sizeof($added) == 0 && sizeof($dropped) == 0 && sizeof($claimed) == 0 && sizeof($tradedTo) == 0 && sizeof($tradedFrom) == 0) return;
		
		if ($commish_id === false || $currUser === false) return;
		
		if ($league_id === false) $league_id = $this->league_id;
		if ($team_id === false) $team_id = $this->id;
		if ($owner_id === false) $owner_id = $this->owner_id;
		
		if ($currUser == $owner_id) {
			$trans_owner = TRANS_OWNER_OWNER;
		} else if ($currUser != $owner_id && $currUser == $commish_id) {
			$trans_owner = TRANS_OWNER_COMMISH;
		} else if ($currUser != $owner_id && $currUser != $commish_id && $isAdmin) {
			$trans_owner = TRANS_OWNER_ADMIN;
		} else {
			$trans_owner = TRANS_OWNER_OTHER;
		}
		
		$addedStr = '';
		if (sizeof($added) > 0) {
			$addedStr = serialize($added);
		}
		$droppedStr = '';
		if (sizeof($dropped) > 0) {
			$droppedStr = serialize($dropped);
		}
		$claimedStr = '';
		if (sizeof($claimed) > 0) {
			$claimedStr = serialize($claimed);
		}
		$tradedToStr = '';
		if (sizeof($tradedTo) > 0) {
			$tradedToStr = serialize($tradedTo);
		}
		$tradedFromStr = '';
		if (sizeof($tradedFrom) > 0) {
			$tradedFromStr = serialize($tradedFrom);
		}
		
		$data = array('team_id' =>$team_id, 'owner_id' => $owner_id, 'added' => $addedStr,'dropped' => $droppedStr,
					  'claimed'=>$claimedStr, 'tradedTo' => $tradedToStr,'tradedFrom' => $tradedFromStr,
					  'league_id'=> $league_id,'trans_owner'=>$trans_owner, 'effective'=>$effective);
		
		$this->db->insert($this->tables['TRANSACTIONS'],$data);
		
		//echo("transaction logged.<br />");
		return true;
	}
	public function setAutoDraft($autoDraft) {
		$this->auto_draft = ($autoDraft) ? 1 : 0;
		$this->save();
	}
	public function setAutoList($autoList) {
		$this->auto_list = ($autoList) ? 1 : 0;
		$this->save();
	}
	public function setLeagueAutoDraft($league_id,$autoDraft) {
		$this->db->set('auto_draft',($autoDraft) ? 1 : 0);
		$this->db->where('league_id',$league_id);
		$this->db->update($this->tblName);
		$this->statusMess = $this->db->affected_rows()." teams uppdated.";
		return true;
	}
	public function setLeagueAutoList($league_id,$autoList) {
		$this->db->set('auto_list',($autoList) ? 1 : 0);
		$this->db->where('league_id',$league_id);
		$this->db->update($this->tblName);
		$this->statusMess = $this->db->affected_rows()." teams uppdated.";
		return true;
	}
	
	public function saveRosterChanges($roster,$score_period, $team_id = false) {
		
		//echo("Saving rosters");
		$success = true;
		if (!isset($roster) || sizeof($roster) == 0) return; 
		
		if ($team_id === false) { $team_id = $this->id; }
		
		foreach($roster as $player_info) {
			$id = $player_info['id'];
			array_shift($player_info);
			$this->db->where('player_id',$id);
			$this->db->where('team_id',$team_id);
			$this->db->where('scoring_period_id',$score_period);
			$this->db->update('fantasy_rosters',$player_info);
			if (!function_exists('updateOwnership')) {
				$this->load->helper('roster');
			}
			$ownership = updateOwnership($id);
			$pData = array('own'=>$ownership[0],'start'=>$ownership[1]);
			$this->db->flush_cache();
			$this->db->where('id',$id);
			$this->db->update('fantasy_players',$pData); 
		}
		return $success;
	}
	
	public function getWaiverOrder($league_id = false, $idOnly = false) {
		if ($league_id === false) { $league_id = $this->league_id; }
		
		$order = array();
		$this->db->select('id, teamname, teamnick, waiver_rank');
		$this->db->where('league_id',$league_id);
		$this->db->order_by('waiver_rank ASC');
		$query = $this->db->get($this->tblName);
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				if ($idOnly) {
					array_push($order,$row->id);
				} else {
					array_push($order,array('id'=>$row->id,'teamname'=>$row->teamname, 'teamnick'=>$row->teamnick,
											'waiver_rank'=>$row->waiver_rank));
				}
			}
		}
		$query->free_result();
		return $order;
	}
	/**
	 *	GET WAIVER CLAIMS.
	 *	Returns pending waiver claims for the specified league.
	 *  @param	$team_id - If not specified, the schedule for the entire league is returned.
	 *  @param	$league_id - If not specified, no league filter is applied.
	 *	@return	schedule array, false on failure
	 */
	public function getWaiverClaims($limit = -1, $startIndex = 0, $team_id = false, $league_id = false) {
		
		if ($team_id === false) { $team_id = $this->id; }
		if ($league_id === false) { $league_id = $this->league_id; }
		
		$claims = array();
		$this->db->select($this->tables['WAIVER_CLAIMS'].".id, ".$this->tables['WAIVER_CLAIMS'].".player_id, first_name, last_name, position, role, waiver_period"); 
		$this->db->join("fantasy_players","fantasy_players.id = ".$this->tables['WAIVER_CLAIMS'].".player_id", "left");
		$this->db->join("fantasy_players_waivers","fantasy_players_waivers.player_id = fantasy_players.id", "right outer");
		$this->db->join("players","fantasy_players.player_id = players.player_id", "right outer");
		$this->db->where($this->tables['WAIVER_CLAIMS'].".league_id",$league_id);
		if ($team_id !== false) {
			$this->db->where($this->tables['WAIVER_CLAIMS'].'.team_id',$team_id);
		}
		if ($limit != -1 && $startIndex == 0) {
			$this->db->limit($limit);
		} else if ($limit != -1 && $startIndex > 0) {
			$this->db->limit($startIndex,$limit);
		}
		$this->db->order_by('waiver_period, last_name','asc');
		$query = $this->db->get($this->tables['WAIVER_CLAIMS']);
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				array_push($claims,array('id'=>$row->id,'player_id'=>$row->player_id, 'player_name'=>$row->first_name." ".$row->last_name, 
										 'position'=>$row->position,'role'=>$row->role, 'waiver_period'=>$row->waiver_period));
			}
		}
		$query->free_result();
		return $claims;
	}
	public function getBasicRoster($score_period = -1, $team_id = false) {
		
		if ($team_id === false) { $team_id = $this->id; }
		$roster = array();
		//echo("this team Id = ".$team_id."<br />");
		$sql = "SELECT player_id FROM fantasy_rosters WHERE team_id = ".$team_id;
		if ($score_period != -1) {
			$sql .= " AND scoring_period_id = ".$score_period;
		}
		$query = $this->db->query($sql);
		$playerIds = array();
		foreach ($query->result() as $row) {
			array_push($playerIds,$row->player_id);
		}
		//echo("last SQL = ".$this->db->last_query()."<br />");
		$query->free_result();
		//echo("Player ids = ".sizeof($playerIds)."<br />");
		if (sizeof($playerIds) > 0) {
			$this->db->select('fantasy_rosters.player_id, first_name, last_name, fantasy_rosters.player_position, fantasy_rosters.player_role, fantasy_rosters.player_status');
			$this->db->join('fantasy_rosters','fantasy_players.id = fantasy_rosters.player_id','left');
			$this->db->join('players','players.player_id = fantasy_players.player_id','right outer');
			$this->db->where_in("fantasy_players.id",$playerIds);
			$this->db->where('fantasy_rosters.team_id',$team_id);
			if ($score_period != -1) {
				$this->db->where('fantasy_rosters.scoring_period_id',$score_period);
			}
			$this->db->order_by('player_position, player_role');
			$query = $this->db->get('fantasy_players');
			//echo("last SQL = ".$this->db->last_query()."<br />");
			if ($query->num_rows() > 0) {
				foreach ($query->result() as $row) {
					array_push($roster,array('player_name'=>$row->first_name." ".$row->last_name,'id'=>$row->player_id,'player_position'=>$row->player_position,'player_role'=>$row->player_role,
												'player_status'=>$row->player_status));
				}
			}
			$query->free_result();
		}
		//echo("Team, get roster, size of roster = ".sizeof($roster)."<br />");
		return $roster;
	}
	
	// SPECIAL QUERIES
	public function getBatters($scoring_period = -1, $team_id = false, $status = 1) {
		
		if ($team_id === false && $this->id != -1) { $team_id = $this->id; }
		
		$players = array();
		$this->db->select('fantasy_players.id, fantasy_players.player_id, first_name, last_name, position, teams.abbr, players.team_id, players.injury_is_injured, players.injury_dtd_injury, players.injury_career_ending, players.injury_dl_left, players.injury_left, players.injury_id, player_position, players.team_id, fantasy_players.positions, fantasy_players.player_status, own, own_last, start, start_last');
		$this->db->join('fantasy_players','fantasy_players.id = fantasy_rosters.player_id','left');
		$this->db->join('players','players.player_id = fantasy_players.player_id','right outer');
		$this->db->join('teams','teams.team_id = players.team_id','right outer');
		$this->db->where('fantasy_rosters.team_id',$team_id);
		$this->db->where('fantasy_rosters.player_position <>', 1);
		if ($status != -999) {
			$this->db->where('fantasy_rosters.player_status',$status);
		}
		if ($scoring_period != -1) {
			$this->db->where('fantasy_rosters.scoring_period_id',$scoring_period);
		}
		$this->db->group_by('fantasy_rosters.player_id');
		$this->db->order_by('fantasy_rosters.player_position');
		$query = $this->db->get('fantasy_rosters');
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$players = $players + array($row->player_id=>array('id'=>$row->id,'first_name'=>$row->first_name, 'last_name'=>$row->last_name,
																  'position'=>$row->position, 'team_id'=>$row->team_id,'team_abbr'=>$row->abbr,
																  'player_position'=>$row->player_position,'injury_is_injured'=>$row->injury_is_injured,
																  'injury_dl_left'=>$row->injury_dl_left, 'injury_left'=>$row->injury_left, 'injury_dtd_injury'=>$row->injury_dtd_injury,
																  'injury_id'=>$row->injury_id,'injury_career_ending'=>$row->injury_career_ending,
																  'positions'=>$row->positions, 'player_status'=>$row->player_status, 'own_last'=>$row->own_last,
									   							  'start_last'=>$row->start_last,'own'=>$row->own,'start'=>$row->start));
			}
		}
		$query->free_result();
		return $players;
	}
	public function getPitchers($scoring_period = -1, $team_id = false, $status = 1) {
		if ($team_id === false && $this->id != -1) { $team_id = $this->id; }
		
		$players = array();
		$this->db->select('fantasy_players.id, fantasy_players.player_id, first_name, last_name, position, players.team_id,players.role,teams.abbr, players.injury_is_injured, players.injury_dtd_injury, players.injury_career_ending, players.injury_dl_left, players.injury_left, players.injury_id, player_position, player_role, fantasy_players.positions, fantasy_players.player_status, own, own_last, start, start_last');
		$this->db->join('fantasy_players','fantasy_players.id = fantasy_rosters.player_id','left');
		$this->db->join('players','players.player_id = fantasy_players.player_id','right outer');
		$this->db->join('teams','teams.team_id = players.team_id','right outer');
		$this->db->where('fantasy_rosters.team_id',$team_id);
		$this->db->where('fantasy_rosters.player_position', 1);
		if ($status != -999) {
			$this->db->where('fantasy_rosters.player_status',$status);
		}
		if ($scoring_period != -1) {
			$this->db->where('fantasy_rosters.scoring_period_id',$scoring_period);
		}
		$this->db->group_by('fantasy_rosters.player_id');
		$this->db->order_by('fantasy_rosters.player_role');
		$query = $this->db->get('fantasy_rosters');
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$players = $players + array($row->player_id=>array('id'=>$row->id,'first_name'=>$row->first_name, 'last_name'=>$row->last_name,
																  'position'=>$row->position, 'role'=>$row->role, 'team_id'=>$row->team_id,'team_abbr'=>$row->abbr,
																  'player_position'=>$row->player_position,'player_role'=>$row->player_role,'injury_is_injured'=>$row->injury_is_injured,
																  'injury_dl_left'=>$row->injury_dl_left, 'injury_left'=>$row->injury_left, 'injury_dtd_injury'=>$row->injury_dtd_injury,
																  'injury_id'=>$row->injury_id,'injury_career_ending'=>$row->injury_career_ending,
																  'positions'=>$row->positions, 'player_status'=>$row->player_status, 'own_last'=>$row->own_last,
									   							  'start_last'=>$row->start_last,'own'=>$row->own,'start'=>$row->start));
			}
		}
		$query->free_result();
		return $players;
	}
	/**
	 *	Returns a list of public leagues.
	 *  @param	$league_id - If not specified, no league filter is applied.
	 *	@return	array of league information
	 */
	public function getCompleteRoster($scoring_period, $team_id = false) {
		if ($team_id === false) { $team_id = $this->id; }
		
		return array($this->getBatters($scoring_period, $team_id)+$this->getPitchers($scoring_period, $team_id),
				    $this->getBatters($scoring_period, $team_id,-1)+$this->getPitchers($scoring_period, $team_id, -1),
					$this->getBatters($scoring_period, $team_id,2)+$this->getPitchers($scoring_period, $team_id, 2));
				
	}
	
	public function getTeamStats($playerIds = array(), $order_by = 'total', $scoring_period_id = false, $league_id = false) {
		
		$stats = array();
		if ($league_id === false) { $league_id = $this->league_id; }
		
		/*$select = '';
		for ($i = 0; $i < 12; $i++) {
			if (!empty($select)) { $select .= ","; }
			if ($scoring_period_id === false) { $select .= 'SUM('; } 
			$select .= 'value_'.$i;
			if ($scoring_period_id === false) { $select .= ') as value_'.$i; } 
		}
		$select .= ',';
		if ($scoring_period_id === false) { $select .= 'SUM('; }
		$select .= 'total';
		if ($scoring_period_id === false) { $select .= ') as total'; } 
		$select .= ','.$this->tables['SCORING'].'.player_id';
		
		$this->db->select($select);
		$this->db->from($this->tables['SCORING']);
		
		if ($scoring_period_id != -1) {
			$this->db->where('scoring_period_id', $scoring_period_id);
		}
		$players = "(";
		foreach ($playerIds as $id => $data) {
			if ($players != "(") { $players .= ","; }
			$players .= $data['id'];
		}
		$players .= ")";
		$this->db->where($this->tables['SCORING'].".player_id IN ".$players);
		$this->db->order_by($order_by.' DESC');
		
		$query = $this->db->get();
		echo("sql = ".$this->db->last_query()."<br />");
		if ($query->num_rows() > 0) {
			foreach ($query->result_array() as $row) {
				array_push($stats, $row);
			}
		}
		$query->free_result();*/
		return $stats;
	}
	/*---------------------------------------
	/	PRIVATE/PROTECTED FUNCTIONS
	/--------------------------------------*/
}  