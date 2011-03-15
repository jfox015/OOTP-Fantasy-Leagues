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
		$this->tables['PLAYERS'] = 'fantasy_players';
		$this->tables['OOTP_PLAYERS'] = 'players';
		$this->tables['TRANSACTIONS'] = 'fantasy_transactions';
		$this->tables['SCORING'] = 'fantasy_players_scoring';
		$this->tables['WAIVERS'] = 'fantasy_players_waivers';
		$this->tables['WAIVER_CLAIMS'] = 'fantasy_teams_waiver_claims';
		$this->tables['TRADES'] = 'fantasy_teams_trades';
		$this->tables['TRADES_STATUS'] = 'fantasy_teams_trades_status';
		$this->tables['TRADE_PROTESTS'] = 'fantasy_teams_trade_protests';
		
		$this->fieldList = array('league_id','division_id','teamname','teamnick','owner_id','auto_draft','auto_list','auto_round_x');
		$this->conditionList = array('avatarFile');
		$this->readOnlyList = array('avatar');  
		$this->textList = array('teamname','teamnick');  
		
		$this->columns_select = array('id','avatar','league_id','division_id','teamname','teamnick','owner_id');
		
		$this->lang->load('team');
		parent::_init();
	}
	/*--------------------------------------------------
	/
	/	PUBLIC FUNCTIONS
	/
	/-------------------------------------------------*/
	/**
	 * APPLY DATA.
	 * Overrides the default applyData function. Saves specific team data
	 * @param	$input		CodeIgniter input object
	 * @param 	$userId 	The current user ID (OPTIONAL)
	 * @return	TRUE on success, FALSE on error
	 * 
	 */
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
	/*-------------------------------------
	/
	/
	/	TRANSACTIONS
	/
	/
	/------------------------------------*/
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
		
		//echo("single transaction logged.<br />");
		return true;
	}
	/**
	 * LOG TRANSACTION
	 * Enter description here ...
	 * @param $added
	 * @param $dropped
	 * @param $claimed
	 * @param $tradedTo
	 * @param $tradedFrom
	 * @param $commish_id
	 * @param $currUser
	 * @param $isAdmin
	 * @param $effective
	 * @param $league_id
	 * @param $team_id
	 * @param $owner_id
	 * @param $trade_team_id
	 */
	public function logTransaction($added = array(), $dropped = array(), $claimed = array(), $tradedTo = array(), $tradedFrom = array(),
									$commish_id = false, $currUser = false, $isAdmin = false, $effective = -1, 
									$league_id = false, $team_id = false, $owner_id = false, $trade_team_id = -1) {
		
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
					  'league_id'=> $league_id,'trans_owner'=>$trans_owner, 'effective'=>$effective,'trade_team_id'=>$trade_team_id);
		
		$this->db->insert($this->tables['TRANSACTIONS'],$data);
		
		//echo("transaction logged.<br />");
		return true;
	}
	/*----------------------------------------
	/
	/
	/	TRADES
	/
	/	Added to 1.0.4
	?
	/
	----------------------------------------*/
	/**
	 * UPDATE TRADE
	 * Updates the record for a trade after it has been processed.
	 * @param $trade_id			The trade record ID
	 * @param $status			The trade status type
	 * @param $comments			(OPTIONAL) Response comments
	 */
	public function updateTrade($trade_id, $status, $comments = "") {
		
		if (!isset($trade_id) || !isset($status)) return false;
		// UPDATE THE TRADE 
		$this->db->where('id',$trade_id);
		$this->db->set('status',$status);
		$this->db->set('response_date',date('Y-m-d h:m:s',time()));
		if (!empty($comments)) {
			$this->db->set('response',$comments);
		}
		$this->db->update($this->tables['TRADES']);
		return true;
	}
	/**
	 * MAKE TRADE OFFER
	 * Registeres a new trade offer in the TEAM_TRADES table.
	 * @param $sendPlayers		Array of player Ids to be sent
	 * @param $team2Id			The ID of the team the offer is for
	 * @param $recievePlayers	Array of player Ids to be recieved
	 * @param $teamid			The ID of the team making the offer. Uses $this->id if FALSE
	 * @param $leagueId			The league id. Uses $this->$league_id if FALSE
	 */
	public function makeTradeOffer($sendPlayers, $team2Id, $recievePlayers, $scoring_period_id, $comments = false, $prevTradeId = false, 
									$expiresIn = false,$defaultExpiration = 1, $league_id = false, $team_id = false) {
		
		//print($defaultExpiration."<br />");
		if (sizeof($sendPlayers) == 0 && sizeof($recievePlayers) == 0 && !isset($team2Id) &&!isset($scoring_period_id)) return;
		
		if ($league_id === false) $league_id = $this->league_id;
		if ($team_id === false) $team_id = $this->id;
		$expireDate = EMPTY_DATE_TIME_STR;
		$day = 60*60*24;
		if ($expiresIn === false || $expiresIn == -1) {
			$expiresIn = $defaultExpiration;
		}
		$expireDate = strtotime(date('m/d/Y 00:00:00')) + ($day * $expiresIn);	
		$data = array('team_1_id' =>$team_id, 'send_players' => serialize($sendPlayers), 'team_2_id' => $team2Id,'receive_players' => serialize($recievePlayers),
					  'status'=>1, 'league_id'=> $league_id,'comments'=>$comments, 'previous_trade_id'=>$prevTradeId,
					  'expiration_date'=>date('Y-m-d h:m:s', $expireDate),'in_period'=>intval($scoring_period_id));
		
		$this->db->insert($this->tables['TRADES'],$data);
		
		return true;
	}
	/**
	 * PROCESS TRADE
	 * This function handles all trades reponses submitted on the site. It switches on the response, takes 
	 * approiate roster actions and logs transactions (if applicable) then composes and send messages to 
	 * the users involved.
	 * @param $trade_id			The trade record ID
	 * @param $status			The trade status type
	 * @param $comments			(OPTIONAL) Response comments
	 */
	public function processTrade($trade_id, $status, $comments = ""){
		
		$outMess = "";

		if (!isset($trade_id) || !isset($status)) return false;
		
		if (!function_exists('updateOwnership')) {
			$this->load->helper('roster');
		}
		// LOAD THE TRADE DATA
		$trade = $this->getTrade($trade_id);
		if (is_array($trade) && sizeof($trade) > 0) {
			// CHANGE PLAYERS ROSTERS AND UPDATE OWNERSHIP
			$playerTypes = array('send_players'=>'team_2_id','receive_players'=>'team_1_id');
			foreach($playerTypes as $tmpType => $team) {
				$players = $trade[$tmpType];
				if (is_array($players) && sizeof($players) > 0){
					foreach($players as $playerStr) {
						$tmpPlayer = explode("_",$playerStr);
						//print('TMP Player str = '.$playerStr.'<br />');
						$this->db->flush_cache();
						$this->db->where('player_id',$tmpPlayer[0]);
						if ($tmpPlayer[1] == "LF" || $tmpPlayer[1] == "CF" || $tmpPlayer[1] == "RF") {
							$tmpPlayer[1] = "OF";
						}
						$this->db->set('player_position',get_pos_num($tmpPlayer[1]));
						if ($tmpPlayer[2] == "CL") {
							$tmpPlayer[2] = "MR";
						}
						$this->db->set('player_role',get_pos_num($tmpPlayer[2]));
						$this->db->set('player_status',-1);
						$this->db->set('team_id',$trade[$team]);
						$this->db->update($this->tables['ROSTERS']);
						//print($this->db->last_query()."<br />");
						$ownership = updateOwnership($tmpPlayer[0]);
						$pData = array('own'=>$ownership[0],'start'=>$ownership[1]);
						$this->db->flush_cache();
						$this->db->where('id',$tmpPlayer[0]);
						$this->db->update('fantasy_players',$pData); 
					} // END foreach
				} else {
					if ($tmpType == "send_players") { $lbl = "sent"; } else { $lbl = "received"; }
					$outMess .= "No players to be ".$lbl." could be found.";
				} // END if
			} // END foreach
			return $this->updateTrade($trade_id, TRADE_COMPLETED, $comments);
		} else {
			return $outMess;
		}
	}
	/**
	 * LOG TRADE PROTESTS
	 * Logs a trade protest to the TRADE_PROTESTS table. Also checks to assure that an existing protest 
	 * for the trade ID does not exist and returns FALSE if so.
	 * @param $trade_id			The ID of the team making the offer. REQUIRED
	 * @param $league_id		The league id. Uses $this->$league_id if FALSE
	 * @param $limit			The limit on rows to return. No limit if =1
	 * @param $startIndex		The first row to return. Stars with first row if 0.
	 */
	public function logTradeProtest($trade_id, $team_id = false, $comments = "", $league_id = false){
				
		if (!isset($trade_id)) return false;
		
		if ($team_id === false) $team_id = $this->team_id;
		if ($league_id === false) $league_id = $this->league_id;
		
		// TEST for existing protest. just in case
		$this->db->select('id');
		$this->db->from($this->tables['TRADE_PROTESTS']);
		$this->db->where('protest_team_id', $team_id);
		$this->db->where('trade_id', $trade_id);
		$this->db->where('league_id', $league_id);
		$query = $this->db->get();
		
		if ($query->num_rows() > 0) {
			return false;
		}
		$query->free_result();
		
		$data = array('protest_team_id' =>$team_id, 'trade_id' =>  $trade_id, 'league_id' => $league_id, 'comments'=>$comments);
		$this->db->insert($this->tables['TRADE_PROTESTS'],$data);
		
		return true;
	}
	/**
	 * GET TRADE PROTESTS
	 * Retrieves trade protests from the TRADE_PROTESTS table.
	 * @param $trade_id			The ID of the team making the offer. REQUIRED
	 * @param $league_id		The league id. Uses $this->$league_id if FALSE
	 * @param $limit			The limit on rows to return. No limit if =1
	 * @param $startIndex		The first row to return. Stars with first row if 0.
	 */
	public function getTradeProtests($league_id = false, $trade_id = false, $limit = -1, $startIndex = 0) {
		
		if ($league_id === false) $league_id = $this->league_id;
		
		$protests = array();
		$this->db->select($this->tables['TRADE_PROTESTS'].'.id, trade_id, protest_team_id, teamname, teamnick, owner_id, protest_date, comments');
		
		$this->db->join($this->tblName,$this->tblName.".id = ".$this->tables['TRADE_PROTESTS'].".protest_team_id", "right outer");
		
		if ($trade_id !== false && $trade_id != -1) {
			$this->db->where('trade_id',$trade_id);
		}
		if ($league_id !== false && $league_id != -1) {
			$this->db->where($this->tables['TRADE_PROTESTS'].'.league_id',$league_id);
		}
		if ($limit != -1 && $startIndex == 0) {
			$this->db->limit($limit);
		} else if ($limit != -1 && $startIndex > 0) {
			$this->db->limit($startIndex,$limit);
		}
		$this->db->order_by('protest_date','desc');
		$query = $this->db->get($this->tables['TRADE_PROTESTS']);
		
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				$ownerStr = getUsername($row->owner_id);
				array_push($protests,array('id'=>$row->id, 'trade_id'=>$row->trade_id, 'protest_date'=>$row->protest_date, 'team_id'=>$row->protest_team_id, 
										   'team_name'=>$row->teamname." ".$row->teamnick, 'owner'=>$ownerStr, 'comments'=>$row->comments));
			} // END foreach
		} // END if
		$query->free_result();
		//print($this->db->last_query()."<br />");
		return $protests;
	}
	/**
	 * GET PENDING TRADES
	 * Retrieves trade data from the TRADES table for all OFFERED trades.
	 * @param $league_id		The league id. Uses $this->$league_id if FALSE
	 * @param $team_id			The ID of the team making the offer. Ommited if FALSE
	 * @param $limit			The limit on rows to return. No limit if =1
	 * @param $startIndex		The first row to return. Stars with first row if 0.
	 */
	public function getPendingTrades($league_id = false, $team_id = false, $team_2_id = false, $exclude_team_id = false, $countProtests = false, $status = false, $limit = -1, $startIndex = 0) {
		if ($league_id === false) $league_id = $this->league_id;
		return $this->getTradeData($league_id, $team_id, $team_2_id, false, $status, $exclude_team_id, $countProtests, $limit, $startIndex);
	}
	
	/**
	 * GET COMPLETED TRADES
	 * Retrieves trade data from the TRADES table for all COMPLETED trades.
	 * @param $league_id		The league id. Uses $this->$league_id if FALSE
	 * @param $team_id			The ID of the team making the offer. Ommited if FALSE
	 * @param $limit			The limit on rows to return. No limit if =1
	 * @param $startIndex		The first row to return. Stars with first row if 0.
	 */
	public function getCompletedTrades($league_id = false, $team_id = false, $team_2_id = false, $exclude_team_id = false, $countProtests = false, $limit = -1, $startIndex = 0) {
		if ($league_id === false) $league_id = $this->league_id;
		return $this->getTradeData($league_id, $team_id, $team_2_id, false, TRADE_COMPLETED, $exclude_team_id, $countProtests, $limit, $startIndex);
	}
	/**
	 * GET ALL TRADES
	 * Retrieves trade data from the TRADES table for all COMPLETED trades.
	 * @param $league_id		The league id. Uses $this->$league_id if FALSE
	 * @param $team_id			The ID of the team making the offer. Ommited if FALSE
	 * @param $limit			The limit on rows to return. No limit if =1
	 * @param $startIndex		The first row to return. Stars with first row if 0.
	 */
	public function getAllTrades($league_id = false, $countProtests = false, $limit = -1, $startIndex = 0) {
		if ($league_id === false) $league_id = $this->league_id;
		return $this->getTradeData($league_id, false, false, false, 100, false, $countProtests, $limit, $startIndex);
	}
	
	/**
	 * GET TRADE
	 * Retrieves trade data from the TRADES table.
	 * @param 	$trade_id			The trade id.
	 * @return						Array of trade data for the requested trade ID
	 */
	public function getTrade($trade_id = false) {
		$trade = array();
		
		if ($trade_id === false) return false;
		
		$this->db->select($this->tables['TRADES'].".id, offer_date, status, team_1_id, send_players, receive_players, team_2_id, tradeStatus, in_period, previous_trade_id, expiration_date, comments"); 
		$this->db->join($this->tables['TRADES_STATUS'],$this->tables['TRADES_STATUS'].".id = ".$this->tables['TRADES'].".status", "right outer");
		$this->db->where($this->tables['TRADES'].".id",$trade_id);
		$query = $this->db->get($this->tables['TRADES']);
		
		if ($query->num_rows() > 0) {
			$row = $query->row();
			$team_1_name = $this->getTeamName($row->team_1_id);
			$team_2_name = $this->getTeamName($row->team_2_id);
			$trade = array('trade_id'=>$row->id, 'offer_date'=>$row->offer_date, 'team_1_name'=>$team_1_name,'team_1_id'=>$row->team_1_id, 
													  'send_players'=>unserialize($row->send_players), 'receive_players'=>unserialize($row->receive_players), 
													  'team_2_name'=>$team_2_name,'team_2_id'=>$row->team_2_id, 'previous_trade_id'=>$row->previous_trade_id, 'in_period'=>$row->in_period,
													  'status'=>$row->status,'comments'=>$row->comments,'expiration_date'=>$row->expiration_date);
		}
		$query->free_result();
		return $trade;
	}
	/**
	 * GET TRADE DATA
	 * Retrieves trade data from the TRADES table.
	 * @param $league_id		The league id. Uses $this->$league_id if FALSE
	 * @param $team_id			The ID of the team making the offer. Ommited if FALSE
	 * @param $status			The trade status to return. oOmitted if FALSE
	 * @param $limit			The limit on rows to return. No limit if =1
	 * @param $startIndex		The first row to return. Stars with first row if 0.
	 * @see						getCompletedTrades(), getPendingTrades()
	 * 
	 */
	public function getTradeData($league_id, $team_id = false, $team_2_id = false, $trade_id = false, $status = false, $exclude_team_id = false, $countProtests = false, $limit = -1, $startIndex = 0) {
		
		if ($league_id === false) { $league_id = $this->league_id; }
		
		$trades = array();
		$selectStr = $this->tables['TRADES'].".id, offer_date, status, team_1_id, send_players, receive_players, team_2_id, tradeStatus, in_period, previous_trade_id, expiration_date, ".$this->tables['TRADES'].".comments, response"; 
		if ($countProtests === true) {
			$selectStr .= ",(SELECT COUNT(".$this->tables['TRADE_PROTESTS'].".id) FROM ".$this->tables['TRADE_PROTESTS']." WHERE ".$this->tables['TRADE_PROTESTS'].".trade_id = ".$this->tables['TRADES'].".id) as protest_count";
		}
		$this->db->select($selectStr);
		$this->db->join($this->tables['TRADES_STATUS'],$this->tables['TRADES_STATUS'].".id = ".$this->tables['TRADES'].".status", "right outer");
		
		$this->db->where($this->tables['TRADES'].".league_id",$league_id);
		
		if ($team_id !== false) {
			$this->db->where('team_1_id',$team_id);
		}
		if ($team_2_id !== false) {
			$this->db->where('team_2_id',$team_2_id);
		}
		if ($status !== false && $status != 100) {
			$this->db->where('status',$status);
		} else {
			if ($status != 100) {
				$this->db->where('status = '.TRADE_OFFERED.' OR status = '.TRADE_PENDING_LEAGUE_APPROVAL.' OR status = '.TRADE_PENDING_COMMISH_APPROVAL);
			}
		}
		if ($exclude_team_id !== false) {
			$this->db->where_not_in('team_1_id',$exclude_team_id);
			$this->db->where_not_in('team_2_id',$exclude_team_id);						 
		}
		if ($trade_id !== false) {
			$this->db->where($this->tables['TRADES'].".id",$trade_id);
		}
		if ($limit != -1 && $startIndex == 0) {
			$this->db->limit($limit);
		} else if ($limit != -1 && $startIndex > 0) {
			$this->db->limit($startIndex,$limit);
		}
		$this->db->order_by('offer_date','desc');
		$query = $this->db->get($this->tables['TRADES']);
		//print($this->db->last_query()."<br />");
		if ($query->num_rows() > 0) {
			$playerTypes = array('send_players','receive_players');
			if (!function_exists('getFantasyPlayersDetails')) {
				$this->load->helper('roster');
			}
			foreach($query->result() as $row) {
				$playerArrays = array();
				foreach ($playerTypes as $field) {
					//echo($field."<br />");
					$playerArrays[$field] = array();
					if (isset($row->$field) && !empty($row->$field) && strpos($row->$field,":")) {
						$fieldData = unserialize($row->$field); 
						if (is_array($fieldData) && sizeof($fieldData) > 0) {
							//echo("size of ".$field." data = ".sizeof($fieldData)."<br />");
							$playerDetails = getFantasyPlayersDetails($fieldData);
							foreach ($fieldData as $playerId) {
								//echo($field." player id = ".$playerId."<br />");
								$playerStr = '';
								if (isset($playerDetails[$playerId])) {
									$pos = $playerDetails[$playerId]['position'];
									if ($pos == 1) { $pos = $playerDetails[$playerId]['role']; }
									$playerStr .= get_pos($pos);
									$playerStr .= "&nbsp; ".anchor('/players/info/league_id/'.$league_id.'/player_id/'.$playerId,$playerDetails[$playerId]['first_name']." ".$playerDetails[$playerId]['last_name']);
								} // END if
								//echo($transStr."<br />");
								if (!empty($playerStr)) { array_push($playerArrays[$field], $playerStr); } 
							} // END foreach
						} // END if
					} // END if
				} // END foreach
				// RESOLVE OTHER TEAM NAME
				$team_1_name = $this->getTeamName($row->team_1_id);
				$team_2_name = $this->getTeamName($row->team_2_id);
				$protestCount = 0;
				if ($countProtests === true && isset($row->protest_count)) {
					$protestCount = $row->protest_count;
				}
				array_push($trades,array('trade_id'=>$row->id, 'offer_date'=>$row->offer_date, 'team_1_name'=>$team_1_name,'team_1_id'=>$row->team_1_id, 
													  'send_players'=>$playerArrays['send_players'], 'receive_players'=>$playerArrays['receive_players'], 
													  'team_2_name'=>$team_2_name,'team_2_id'=>$row->team_2_id, 'previous_trade_id'=>$row->previous_trade_id, 'in_period'=>$row->in_period,
													  'status'=>$row->status,'comments'=>$row->comments,'expiration_date'=>$row->expiration_date,'protest_count'=>$protestCount,'response'=>$row->response));			}
		}
		$query->free_result();
		
		return $trades;
	}
	/*-----------------------------------------------
	/	STATS
	/----------------------------------------------*/
	public function getTeamStats($countOnly = false, $team_id = false, $player_type=1, $position_type = -1,  
								 $role_type = -1, $stats_range = 1, $scoring_period_id = -1, $min_var = 0, $limit = -1, $startIndex = 0, $ootp_league_id = false, $ootp_league_date = false, $rules = array(),
								 $includeList = array(), $searchType = 'all', $searchParam = -1) {	
		$stats = array();
		$players = array();
		if ($team_id === false) $team_id = $this->team_id;
		
		//echo("include list= ".sizeof($includeList)."<br />");
		if (sizeof($includeList) > 0) {
			$players = $includeList;
		} else {
			if ($player_type == 1) {
				$players = $this->getBatters($scoring_period_id, $team_id, -999);
			} else {
				$players = $this->getPitchers($scoring_period_id, $team_id, -999);
			}
		}
		$this->db->flush_cache();
		$sql = 'SELECT fantasy_players.id, "add", fantasy_players.id,fantasy_players.positions, players.player_id, players.position as position, players.role as role, players.first_name, players.last_name, players.injury_is_injured, players.injury_dtd_injury, players.injury_career_ending, players.injury_dl_left, players.injury_left, players.injury_id,';		
		$where = '';
		if ($player_type == 1) {
			if ($stats_range == 4) {
				$sql .= player_stat_query_builder(1, QUERY_STANDARD, $rules, false)." ";
			} else {
				$sql .= player_stat_query_builder(1, QUERY_STANDARD, $rules)." ";
			}
			$tblName = "players_career_batting_stats";
			$where = "AND players.position <> 1 ";
			if (!empty($position_type) && $position_type != -1) {
				if ($position_type == 20) {
					$where.="AND (players.position = 7 OR players.position = 8 OR players.position = 9) ";
				} else {
					$where.="AND players.position = ".$position_type." ";
				}
			}
			$order = 'ab';
			if ($min_var != 0) {
				$where .= 'AND '.$tblName.'.ab >= '.$min_var." ";
			}
		} else {
			if ($stats_range == 4) {
				$sql .= player_stat_query_builder(2, QUERY_STANDARD, $rules, false)." ";
			} else {
				$sql .= player_stat_query_builder(2, QUERY_STANDARD, $rules)." ";
			}
			$tblName = "players_career_pitching_stats";
			$where = " ";
			if (!empty($role_type) && $role_type != -1) {
				$where.="AND players.role = ".$role_type." ";
			}
			$order = 'ip';
			if ($min_var != 0) {
				$where .= 'AND '.$tblName.'.ip >= '.$min_var." ";
			}
		}
		$sql .= ' FROM players';
		$sql .= ' LEFT JOIN fantasy_players ON fantasy_players.player_id = players.player_id';
		$sql .= ' LEFT JOIN '.$tblName.' ON players.player_id = '.$tblName.'.player_id';
		$sql .= ' WHERE '.$tblName.'.split_id = 1 AND '.$tblName.'.level_id = 1';
		$sql .= ' AND players.retired = 0';
		$playerStr = "(";
		if (sizeof($players) > 0) {
			foreach ($players as $id => $data) {
				if ($playerStr != "(") { $playerStr .= ","; }
				$playerStr .= $id;
			}
		}
		$playerStr .= ")";
		if ($playerStr != "()") {
			$sql .= ' AND players.player_id IN '.$playerStr;
		}
		$year_time = (60*60*24*365);
		//echo($this->_NAME." league date = ".$ootp_league_date."<br />");
		if ($ootp_league_date === false || $ootp_league_date == EMPTY_DATE_STR) {
			$base_year = time();
		} else {
			$base_year = strtotime($ootp_league_date);
		}
		if ($stats_range != 4) {
			$sql .= ' AND '.$tblName.'.year = '.date('Y',$base_year-($year_time * $stats_range));
		} else {
			$sql .= ' AND ('.$tblName.'.year = '.date('Y',$base_year-($year_time))." OR ".$tblName.'.year = '.date('Y',time()-($year_time * 2))." OR ".$tblName.'.year = '.date('Y',time()-($year_time * 3)).")";
		}
		if (!empty($where)) {
			$sql .= " ".$where;
		}
		$sql.=' GROUP BY '.$tblName.'.player_id';
		if (sizeof($rules) > 0) {
			$order = 'fpts';	
		}
		$sql.=" ORDER BY ".$order." DESC ";
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
		return $stats;
	}
	/*----------------------------------------
	/	DRAFT
	----------------------------------------*/
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
	/*----------------------------------------
	/	ROSTERS
	----------------------------------------*/
	/**
	 * GET PLAYER ROSTER STATUS
	 * Enter description here ...
	 * @param  $player_id		
	 * @param  $score_period
	 * @param  $team_id
	 */
	public function getPlayerRosterStatus($player_id = false,$score_period = false, $team_id = false) {
		$status = array();
		$code = -1;
		$message = "";
		$count = 0;
		
		if ($player_id === false || $score_period === false || $team_id === false) { return false; }
		
		$this->db->flush_cache();
		$name = "[Name not found]";// GET PLAYER NAME
		$this->db->select('first_name, last_name');
		$this->db->where('id',$player_id);
		$this->db->join($this->tables['OOTP_PLAYERS'],$this->tables['OOTP_PLAYERS'].".player_id = ".$this->tables['PLAYERS'].".player_id");
		$query = $this->db->get($this->tables['PLAYERS']);
		if ($query->num_rows() > 0) {
			$row = $query->row();
			$name = $row->first_name." ".$row->last_name;
		}
		$query->free_result();
		
		$this->db->flush_cache();
		$this->db->where('player_id',$player_id);
		$this->db->where('team_id',$team_id);
		$this->db->where('scoring_period_id',$score_period);
		$count = $this->db->count_all_results($this->tables['ROSTERS']);
		if ($count > 0) {
			$code = 200;
			$message = "OK";
		} else {
			$code = 404;
			$message = "The player ".$name." is not on the current roster.";
		}
		return array('code'=>$code,'message'=>$message);
	}
	/**
	 * GET PLAYERS ROSTER STATUS
	 * Tests and array of player ids against their roster status.
	 * @param $players
	 * @param $score_period
	 * @param $team_id
	 */
	public function getPlayersRosterStatus($players,$score_period, $team_id) {
		$status = array();
		if ($players === false || !is_array($players) || sizeof($players) <= 0 ||
		$score_period === false || $team_id === false) { return false; }
		
		foreach($players as $player_id) {
			array_push($status,$this->getPlayerRosterStatus($player_id,$score_period,$team_id));
		}
		return $status;
	}
	public function saveRosterChanges($roster,$score_period, $team_id = false) {
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
	public function applyRosterChanges($input, $score_period, $team_id = false) {
		
		if (!isset($input)) return; 
		
		if ($team_id === false) { $team_id = $this->id; }
		
		$roster = $this->dataModel->getBasicRoster($score_period);
		$new_roster = array();
		
		foreach($roster as $player_info) {
			// CHECK FOR STATUS UPDATE
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
		}
		return $new_roster;
	}
	/*----------------------------------------
	/	WAIVERS
	----------------------------------------*/
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
	/*-------------------------------------
	/ 	GENERAL TEAM DATA FUNCTIONS
	/------------------------------------*/
	public function getTeamOwnerId($team_id = FALSE) {
		if ($team_id === false) { $team_id = $this->id; }
		
		$ownerId = FALSE;
		$this->db->select('owner_id');
		$this->db->where('id',$team_id);
		$query = $this->db->get($this->tblName);
		if ($query->num_rows() > 0) {
			$row = $query->row();
			$ownerId = $row->owner_id;
		}
		$query->free_result();
		return $ownerId;
	}
	public function getTeamName($team_id = FALSE) {
		if ($team_id === false) { $team_id = $this->id; }
		
		$teamName = FALSE;
		$this->db->select('teamname, teamnick');
		$this->db->where('id',$team_id);
		$query = $this->db->get($this->tblName);
		if ($query->num_rows() > 0) {
			$row = $query->row();
			$teamName = $row->teamname." ".$row->teamnick;
		}
		$query->free_result();
		return $teamName;
	}
	
	public function getAvatar($team_id = FALSE) {
		if ($team_id === false) { $team_id = $this->id; }
		
		$avatar = FALSE;
		$this->db->select('avatar');
		$this->db->where('id',$team_id);
		$query = $this->db->get($this->tblName);
		if ($query->num_rows() > 0) {
			$row = $query->row();
			$avatar = $row->avatar;
		}
		$query->free_result();
		return $avatar;
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
		$query = $this->db->get($this->tables['ROSTERS']);
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
		$query = $this->db->get($this->tables['ROSTERS']);
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
	
	/*public function getTeamStats($playerIds = array(), $order_by = 'total', $scoring_period_id = false, $league_id = false) {
		
		$stats = array();
		if ($league_id === false) { $league_id = $this->league_id; }
		
		$select = '';
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
		$query->free_result();
		return $stats;
	}*/
	/*---------------------------------------
	/	PRIVATE/PROTECTED FUNCTIONS
	/--------------------------------------*/
}  