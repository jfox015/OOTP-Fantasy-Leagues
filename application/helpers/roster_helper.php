<?php
function getFantasyPlayersDetails($players = array()) {
	$ci =& get_instance();
	$ci->load->model('player_model');
	$requestArray = array();
	foreach($players as $id) {
		array_push($requestArray,array('player_id'=>$id));
	}
	return $ci->player_model->getPlayersDetails($requestArray);
}
function getPlayersOnWaivers($period_id = false, $league_id = false) {
	$ci =& get_instance();
	$ci->load->model('player_model');
	return $ci->player_model->getPlayersOnWaivers($period_id, $league_id);
}
function getWaiverOrder($league_id = false, $idOnly = false) {
	$ci =& get_instance();
	$ci->load->model('team_model');
	return $ci->team_model->getWaiverOrder($league_id, $idOnly);
}
function logTransaction($added = array(), $dropped = array(), $claimed = array(), $tradedTo = array(), $tradedFrom = array(),
									$commish_id = false, $currUser = false, $isAdmin = false, $effective = -1, 
									$league_id = false, $team_id = false, $owner_id = false) {
	$ci =& get_instance();
	$ci->load->model('team_model');
	return $ci->team_model->logTransaction($added, $dropped, $claimed, $tradedTo, $tradedFrom,$commish_id, $currUser, 
										   $isAdmin, $effective, $league_id, $team_id, $owner_id);
}
function getBasicRoster($team_id = false, $score_period = false) {
	$ci =& get_instance();
	$ci->load->model('team_model');
	return $ci->team_model->getBasicRoster($score_period['id'],$team_id);
}
	
/*---------------------------------------------------------
/	GET AVAILABLE PLAYERS
/----------------------------------------------------------
/ Populates the fantasy players table with aavilable players from the main league.
*/
function get_available_players($league_id = false) {
    $errors = "";
	
	if ($league_id === false) { 
		$errors = "No League Id";
	} else {
		$ci =& get_instance();
		// GET all orgs for this league
		$teamList = getOOTPTeams($league_id,false);
		$player_list = array();
		$ci->db->flush_cache();
		$ci->db->select("player_id,injury_is_injured,team_id, position, role");
		$ci->db->from("players");
		$whereClause = "(";
		foreach ($teamList as $id => $data) {
			if ($whereClause != "(") {
				$whereClause .= ' OR ';
			}
			$whereClause .= 'organization_id ='.$id;
		}
		$whereClause .= ")";
		$ci->db->where($whereClause);
		$ci->db->where("retired",0);
		$ci->db->where("free_agent",0);
		$ci->db->order_by("player_id");
		$query = $ci->db->get();
		//echo("Result size = ".$query->num_rows()."<br />");
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				array_push($player_list,array('player_id'=>$row->player_id,'team_id'=>$row->team_id,'status'=>$row->injury_is_injured,'position'=>$row->position,'role'=>$row->role));
			}
		}
		if (sizeof($player_list) > 0) {
			// CLEAR PLAYERS TABLE
			
			$ci->db->flush_cache();
			$ci->db->query('TRUNCATE TABLE fantasy_players'); 
			
			foreach ($player_list as $player) {
				$status = 1;
				if ($player['status'] == 1) {
					$status = 3;
				}
				$teamFound = false;
				foreach($teamList as $id => $data) {
					if ($player['team_id'] == $id) {
						$teamFound = true;
						break;
					}
				}
				if (!$teamFound)
					$status = 2;
				$positions = array();
				if ($player['position'] != 1) {
					$thisPos = $player['position'];
					if ($player['position'] == 7 || $player['position'] == 8 || $player['position'] == 9) {
						$thisPos = 20;
					}
					$positions = array($thisPos,25);
				} else {
					$thisPos = $player['role'];
					if ($player['role'] == 13) { $thisPos = 12; }
					$positions = array($thisPos);
				}
				$ci->db->flush_cache();
				$ci->db->insert('fantasy_players', array('player_id'=>$player['player_id'],'player_status'=>$status, 'positions'=>serialize($positions))); 
				if ($ci->db->affected_rows() == 0) {
					$errors .= "insert of player_id ".$player['player_id']." failed.<br />\n";
				}
			}
		} else {
			$errors .= "No players were found.";
		}
	}
	if (empty($errors)) $errors = "OK"; else  $errors = $errors;
	return $errors;
}

/*---------------------------------------------------------
/	SIMPLE FREE AGENT LIST
/----------------------------------------------------------
*/
if (!function_exists('getNonFreeAgentsByLeague')) {
	function getNonFreeAgentsByLeague($league_id = false, $scoring_period = 1) {
		
		if ($league_id === false) return;
		$errors = "";
		$ci =& get_instance();
		//echo("scoring_period = ".$scoring_period."<br />");
		$query = $ci->db->query('SELECT player_id FROM fantasy_rosters WHERE player_id IS NOT NULL AND league_id = '.$league_id.' AND scoring_period_id = '.$scoring_period);
		$nonFreeAgents = array();
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				array_push($nonFreeAgents,$row->player_id);
			}
		}
		$query->free_result();
		return $nonFreeAgents;
	}
}
if (!function_exists('getSignedPlayerTeamIdsByLeague')) {
	function getSignedPlayerTeamIdsByLeague($league_id = false, $scoring_period = 1) {
		
		if ($league_id === false) return;
		$errors = "";
		$ci =& get_instance();
		$query = $ci->db->query('SELECT player_id, team_id FROM fantasy_rosters WHERE player_id IS NOT NULL AND league_id = '.$league_id.' AND scoring_period_id = '.$scoring_period);
		$playerTeamIds = array();
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$playerTeamIds = $playerTeamIds + array($row->player_id => $row->team_id);
			}
		}
		$query->free_result();
		return $playerTeamIds;
	}
}

if (!function_exists('getDraftedPlayersByLeague')) {
	function getDraftedPlayersByLeague($league_id = false) {
		
		if ($league_id === false) return;
		$errors = "";
		$ci =& get_instance();
		$query = $ci->db->query('SELECT player_id FROM fantasy_draft WHERE player_id IS NOT NULL AND league_id = '.$league_id);
		$draftedPlayers = array();
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				array_push($draftedPlayers,$row->player_id);
			}
		}
		$query->free_result();
		return $draftedPlayers;
	}
}
if (!function_exists('getFreeAgentList')) {
	function getFreeAgentList($league_id = false, $searchType = 'all', $searchParam = false, 
							  $current_scoring_period = 1, $list_type = 1, $return = false,
							  $scoring_period = array(), $rules = array(),$limit = -1, 
							  $startIndex = 0, $ootp_league_id = -1, $lgyear = false,$countOnly = false) {
		
		if ($league_id === false) return;
		$errors = "";
		$ci =& get_instance();
		$ci->load->model('player_model');
		
		if (intval($list_type) == 1) {
			$query = $ci->player_model->getActiveOOTPPayers($league_id, $searchType, $searchParam, $current_scoring_period, getNonFreeAgentsByLeague($league_id,$current_scoring_period));
		} else {
			$player_type = -1;
			$position_type = -1;
			$role_type = -1;
			if ($searchType == 'pos') {
				if ($searchParam >= 11 && $searchParam <= 13) {
					$player_type = 2;
					$position_type = 1;
					$role_type = $searchParam;
				} else {
					$player_type = 1;
					$position_type = $searchParam;
					$role_type = -1;
				}
			}
			$query = $ci->player_model->getFantasyStats($countOnly, $ootp_league_id, $lgyear, $player_type, $position_type,  $role_type, $current_scoring_period, -1, $limit, $startIndex, $league_id, $scoring_period, $rules, $searchType, $searchParam);
		} // END if
		return $query;
	} // END function
} // END if
/*---------------------------------------------------------
/	POSITION ELIDGIBILITY
/----------------------------------------------------------
/ UPDATES THE POSITIONS A PLAYER CAN BE USED IN N A FANTASY ROSTER
*/
function position_elidgibility($league_id = false,$min_game_current = 5, $min_game_last = 20, $season_status = "regular") {
	$errors = "";
	$ci =& get_instance();
	$ci->db->select('id, player_id, positions');
	$ci->db->where('player_status',1);
	$ci->db->from('fantasy_players');
	$query = $ci->db->get();
	$player_count = 0;
	$players = array();
	if ($query->num_rows() > 0) {
		foreach($query->result() as $row) {
			array_push($players,$row);
		}
	}
	$query->free_result();
	foreach($players as $row) {
		$positions = unserialize($row->positions);
		if ($positions[0] == 11 || $positions[0] == 12 || $positions[0] == 13) {
			$row->position = 1;
			$row->role = $positions[0];
		} else {
			$thisPos = $positions[0];
			if ($thisPos == 7 || $thisPos == 8 || $thisPos == 9) {
				$thisPos = 20;
			}
			$row->position = $thisPos;
			$row->role = -1;
		}	
		$lgDetails = getOOTPLeagueDetails($league_id);
		$league_yr_time = strtotime($lgDetails->current_date);
		$league_yr_last = $league_yr_time - (60*60*24*365);
		$league_yr = date('Y',$league_yr_time);
		$last_yr = date('Y',$league_yr_last);

		// NOW LOOKUP PREVIOUS YEARS POSITIONS USING FIELDING STATS IF THEY EXIST
		$years = array($league_yr, $last_yr);
		$count = 0;
		foreach($years as $year) {
			if ($count == 0) {
				$lvl = $min_game_current; 
			} else {
				$lvl = $min_game_last;
			}
			if ($row->position != 1) {
				$ci->db->flush_cache();
				$ci->db->select("g, position");
				$ci->db->from("players_career_fielding_stats");
				$ci->db->where("player_id",$row->player_id);
				$ci->db->where("level_id",1);
				$ci->db->where("league_id",$league_id);
				$ci->db->where("year",$year);
				$gquery = $ci->db->get();
				if ($gquery->num_rows() > 0) {
					foreach ($gquery->result() as $grow) {
						if ($grow->g >= $lvl) {
							$thisPos = $grow->position;
							if ($grow->position == 7 || $grow->position == 8 || $grow->position == 9) {
								$thisPos = 20;
							}
							if (!in_array($thisPos,$positions)) {
								array_push($positions,$thisPos);
							}
						}
					}
				}
				$gquery->free_result();
			} else {
				$ci->db->flush_cache();
				$ci->db->select("g, gs");
				$ci->db->from("players_career_pitching_stats");
				$ci->db->where("player_id",$row->player_id);
				$ci->db->where("level_id",1);
				$ci->db->where("split_id",1);
				$ci->db->where("league_id",$league_id);
				$ci->db->where("year",$year);
				$gquery = $ci->db->get();
				if ($gquery->num_rows() > 0) {
					foreach ($gquery->result() as $grow) {
						if ($row->role == 11) {
							$gDiff = ($grow->g - $grow->gs);
							if ($gDiff >= $lvl) {
								if (!in_array(12,$positions)) {
									array_push($positions,12);
								}
							}
						} else if ($row->role == 12 || $row->role == 13) {
							if ($grow->gs >= $lvl) {
								if (!in_array(11,$positions)) {
									array_push($positions,11);
								}
							}
						}
					}
				}
				$gquery->free_result();
			}
			$count++;
		}
		asort($positions);
		$ci->db->flush_cache();
		$pData = array('positions'=>serialize($positions));
		$ci->db->where('id',$row->id);
		$ci->db->update('fantasy_players',$pData);
		$player_count++;
	}
	if (empty($errors)) $errors = "OK"; else  $errors = $errors;
	return $errors;
}
function update_player_availability($league_id = false) {
    $errors = "";
	if ($league_id === false) { 
		$errors = "No League Id";
	} else {
		$ci =& get_instance();
		// GET ALL PLAYERS
		$ci->db->select('id,player_id, player_status, own, start');
		$ci->db->from('fantasy_players');
		$query = $ci->db->get();
		
		if ($query->num_rows() > 0) {
			$teamList = getOOTPTeams($league_id,false);
			foreach($query->result() as $row) {
				$ci->db->flush_cache();
				$ci->db->select("injury_is_injured, injury_dl_left, team_id, retired");
				$ci->db->from("players");
				$ci->db->where("player_id",$row->player_id);
				$query = $ci->db->get();
				if ($query->num_rows() > 0) {
					$player_row = $query->row();
					$status = 1;
					if ($player_row->injury_is_injured == 1 && $player_row->injury_dl_left > 0) {
						$status = 3;
					}
					$teamFound = false;
					foreach($teamList as $id => $data) {
						if ($player_row->team_id == $id) {
							$teamFound = true;
							break;
						}
					}
					if (!$teamFound)
						$status = 2;
					if ($player_row->retired == 1) {
						$status = 4;
					}
				}
				// GET OWNERSHIP
				$ownership = updateOwnership($row->id);
				$own = $ownership[0];
				$start = $ownership[1];
				$pData = array('player_status'=>$status, 'own_last'=>$row->own,
							   'start_last'=>$row->start,'own'=>$own,'start'=>$start);
				$ci->db->flush_cache();
				$ci->db->where('id',$row->id);
				$ci->db->update('fantasy_players',$pData); 
			}
		}
	}
	if (empty($errors)) $errors = "OK"; else  $errors = $errors;
	return $errors;
}
function updateOwnership($playerId = false) {
	
	$ci =& get_instance();
	
	// GET LEAGUE COUNT
	$ci->db->select('id');
	$ci->db->from('fantasy_leagues');
	$ci->db->where('league_status',1);
	$league_count = $ci->db->count_all_results();
	
	$own = 0;
	$start = 0;
	$ci->db->select("team_id, player_status");
	$ci->db->from("fantasy_rosters");
	$ci->db->group_by("league_id");
	$ci->db->where("player_id",$playerId);
	$rQuery = $ci->db->get();
	foreach($rQuery->result() as $rRow) {
		$own++;
		if ($rRow->player_status == 1) {
			$start++;
		}
	}
	if ($league_count > 0) {
		$own = ($own / $league_count) * 100;
		$start = ($start / $league_count) * 100;
	}
	return array($own, $start);
}
?>
