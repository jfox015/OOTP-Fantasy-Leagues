<?php
/**
 *	TEAM MODEL CLASS.
 *
 *	@author			Jeff Fox (Github ID: jfox015)
 *	@version		1.0.6.127
 *	@since			1.0
 *
*/
class team_model extends base_model {
	/*--------------------------------
	/	VARIABLES
	/-------------------------------*/
	/**
	 *	SLUG.
	 *	@var $_NAME:Text
	 */
	var $_NAME = 'team_model';
	/**
	 *	LEAGUE ID
	 *	@var	$league_id	Int
	 */
	var $league_id = -1;
	/**
	 *	DIVISION ID
	 *	@var	$division_id	Int
	 */
	var $division_id = -1;
	/**
	 *	TEAM NAME
	 *	@var	$teamname	String
	 */
	var $teamname = '';
	/**
	 *	TEAM NICK NAME
	 *	@var	$teamnick	String
	 */
	var $teamnick = '';
	/**
	 *	TEAM OWNER ID
	 *	References from users_core
	 *	@var	$owner_id	Int
	 */
	var $owner_id = -1;
	/**
	 *	TEAM AVATAR PATH
	 *	Path to the avatar graphic
	 *	@var	$avatar	String
	 */
	var $avatar = '';
	/**
	 *	AUTO DRAFT
	 *	Auto draft setting
	 *	@var	$auto_draft	Int
	 */
	var $auto_draft = -1;
	/**
	 *	AUTO LIST
	 *	Auto list setting for draft
	 *	@var	$auto_list	Int
	 */
	var $auto_list = -1;
	/**
	 *	DRAFT AUTOMATICVALLY AFTER ROUND
	 *	Auto draft enabling setting for draft
	 *	@var	$auto_round_x	Int
	 */
	var $auto_round_x = -1;
	/*---------------------------------------------
	/
	/	C'TOR
	/	Creates a new instance of team_model
	/
	/---------------------------------------------*/
	function team_model() {
		parent::__construct();

		$this->tblName = 'fantasy_teams';
		$this->tables['ROSTERS'] = 'fantasy_rosters';
		$this->tables['PLAYERS'] = 'fantasy_players';
		$this->tables['LEAGUES'] = 'fantasy_leagues';
		$this->tables['DIVISIONS'] = 'fantasy_divisions';
		$this->tables['OOTP_PLAYERS'] = 'players';
		$this->tables['TRANSACTIONS'] = 'fantasy_transactions';
		$this->tables['SCORING'] = 'fantasy_players_scoring';
		$this->tables['WAIVERS'] = 'fantasy_players_waivers';
		$this->tables['WAIVER_CLAIMS'] = 'fantasy_teams_waiver_claims';
		$this->tables['TRADES'] = 'fantasy_teams_trades';
		$this->tables['TRADES_STATUS'] = 'fantasy_teams_trades_status';
		$this->tables['TRADE_PROTESTS'] = 'fantasy_teams_trade_protests';
		$this->tables['TEAM_RECORDS'] = 'fantasy_teams_record';

		$this->fieldList = array('league_id','division_id','teamname','teamnick','owner_id','auto_draft','auto_list','auto_round_x');
		$this->conditionList = array('avatarFile');
		$this->readOnlyList = array('avatar');

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
	public function applyData($input,$team_id = -1) {
		//print("Model team name = ".$this->teamname." ".$this->teamnick."<br />");
		$success = parent::applyData($input,$team_id);
		if ($success) {
			if (isset($_FILES['avatarFile']['name']) && !empty($_FILES['avatarFile']['name'])) {
				$success = $this->uploadFile('avatar',PATH_TEAMS_AVATAR_WRITE,$input,'avatar',$this->teamname);
				//print("Model team name = ".$this->teamname." ".$this->teamnick."<br />");
			}
			if ($input->post('auto_round_x') && $input->post('auto_round_x') == -1) {
				$this->auto_round_x = 0;
			}
		}
		return $success;
	}
	/**
	* 	DELETE ROSTERS.
	* 	<p>
	* 	Deletes all rosters for the specified league_id. If no id is passed, the current league id of the loaded bbject is used.
	*	</p>
	*	<p><b>NOTE:</b> To delete waiver claims for a given league, use the league_model->deleteRosters function instead.
	*	</p>
	* 	@param	$league_id		{int}	The Team Id
	* 	@return					{Boolean}	TRUE on success
	*
	* 	@since	1.0.6
	*  	@access	public
	*  	@see	application -> models -> league_model -> deleteRosters
	*/
	public function deleteRosters($team_id = false, $scoring_period_id = false) {

		if ($team_id === false) {
			$team_id = $this->id;
		}

		$this->db->where('team_id',$team_id);
		if ($scoring_period_id !== false) {
		$this->db->where('scoring_period_id',$scoring_period_id);
		}
		$this->db->delete($this->tables['ROSTERS']);

		return true;
	}
	/**
	* 	DELETE TEAM RECORDS.
	* 	<p>
	* 	Deletes all records for the specified team_id. If no id is passed, the current league id of the loaded bbject is used.
	*	</p>
	*	<p><b>NOTE:</b> To delete waiver claims for a given league, use the league_model->deleteRecords function instead.
	*	</p>
	* 	@param	$team_id		{int}	The Team Id
	* 	@return					{Boolean}	TRUE on success
	*
	* 	@since	1.0.6
	*  	@access	public
	*  	@see	application -> models -> league_model -> deleteRecords
	*/
	public function deleteRecords($team_id = false) {

		return $this->deleteTeamData($this->tables['TEAMS_RECORD'],$team_id);

	}
	/**
	* 	DELETE TEAM SCORING.
	* 	<p>
	* 	Deletes all scoring for the specified team_id. If no id is passed, the current league id of the loaded bbject is used.
	*	</p>
	*	<p><b>NOTE:</b> To delete waiver claims for a given league, use the league_model->deleteScoring function instead.
	*	</p>
	* 	@param	$team_id		{int}	The Team Id
	* 	@return					{Boolean}	TRUE on success
	*
	* 	@since	1.0.6
	*  	@access	public
	*  	@see	application -> models -> league_model -> deleteRecords
	*/
	public function deleteScoring($team_id = false) {

		return $this->deleteTeamData($this->tables['TEAMS_SCORING'],$team_id);

	}

	/**
	* 	DELETE TRADES.
	* 	<p>
	* 	Deletes all trades for the specified team_id. If no id is passed, the current league id of the loaded bbject is used.
	*	</p>
	*	<p><b>NOTE:</b> To delete waiver claims for a given league, use the league_model->deleteTrades function instead.
	*	</p>
	* 	@param	$team_id		{int}	The Team Id
	* 	@return					{Boolean}	TRUE on success
	*
	* 	@since	1.0.6
	*  	@access	public
	*  	@see	application -> models -> league_model -> deleteTrades
	*/
	public function deleteTrades($team_id = false) {

		return $this->deleteTeamData($this->tables['TEAM_TRADES'],$team_id);

	}

	/**
	* 	DELETE TRANSACTIONS.
	* 	<p>
	* 	Deletes all transactions for the specified team_id. If no id is passed, the current league id of the loaded bbject is used.
	*	</p>
	*	<p><b>NOTE:</b> To delete waiver claims for a given league, use the league_model->deleteTransactions function instead.
	*	</p>
	* 	@param	$team_id		{int}	The Team Id
	* 	@return					{Boolean}	TRUE on success
	*
	* 	@since	1.0.6
	*  	@access	public
	*  	@see	application -> models -> league_model -> deleteTransactions
	*/
	public function deleteTransactions($team_id = false) {

		return $this->deleteTeamData($this->tables['TRANSACTIONS'],$team_id);

	}
	/**
	* 	DELETE WAIVER CLAIMS.
	* 	<p>
	* 	Deletes all waiver claims for the specified team_id. If no id is passed, the current league id of the loaded bbject is used.
	*	</p>
	*	<p><b>NOTE:</b> To delete waiver claims for a given league, use the league_model->deleteWaiverClaims function instead.
	*	</p>
	* 	@param	$team_id		{int}	The Team Id
	* 	@return					{Boolean}	TRUE on success
	*
	* 	@since	1.0.6
	*  	@access	public
	*  	@see	application -> models -> league_model -> deleteWaiverClaims
	*/
	public function deleteWaiverClaims($team_id = false) {

		return $this->deleteTeamData($this->tables['WAIVER_CLAIMS'],$team_id);

	}
	/*-------------------------------------
	/
	/
	/	TRANSACTIONS
	/
	/
	/------------------------------------*/
	/**
	 *	LOG SINGLE TRANSACTION
	 *	@param	$player_id	The player involved int he transaction
	 *	@param	$trans_type	Transaction type
	 *	@param	$commish_id	League commisioner ID
	 *	@param	$currUser	Current User ID
	 *	@param	$isAdmin	Is user an admin?
	 *	@param	$league_id	League Id
	 *	@param	$team_id	Team involved in transaction
	 *	@param	$owner_id	Team owner ID
	 *	@param	$team_id_2	Second team ID (for Trades)
	 *	@return			`	TRUE ON SUCCESS, FALSE on Error
	 *	@since				1.0
	 */
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
	/	Added to 1.0.5
	?
	/
	----------------------------------------*/
	public function getIsTradePastExpiration($trade_id = false) {

        if (!isset($trade_id)) return false;

        $this->db->select('offer_date, expiration_days');
        $this->db->where('id',$trade_id);
        $this->db->where('status',TRADE_OFFERED);
		$query = $this->db->get($this->tables['TRADES']);
        if ($query->num_rows() > 0) {
            $row = $query->row();
            if ($row->expiration_days > -1 && $row->expiration_days < 500) {
                 $expireDate = (strtotime($row->offer_date) + ((60*60*24) * $row->expiration_days));
                if ($expireDate > time()) {
                    return true;
                }
            }
        } else {
            $this->errorCode = 1;
            $this->statusMess = "Trade ID ".$trade_id." not found.";
        }
        $query->free_result();
        return false;
	}
	/**
	 * CHECK TRADES IN APPROVAL STATE
	 * Runs a check on trades that are waiting for League Approval. If the offer review period is at or past the current
	 * number of days available for LEAGUE protest, the trade is marked as appproved and updated.
	 * @param $scoring_period_id			The Scorign Period for trades
	 * @param $protestPeriodDays			An override for the default days
	 * @return								[Boolean] TRUE On success
	 * 
	 * @since 1.0.3 PROD
	 * 
	 */
	public function checkTradesInApprovalState($scoring_period_id = false, $protestPeriodDays = TRADE_MAX_EXPIRATION_DAYS) {
		
		if ($scoring_period_id === false) {
			$curr_period = $this->getScoringPeriod();
			$scoring_period_id = $curr_period['id'];
		}
		
		$day = 60*60*28;
        $this->db->select("id, league_id, offer_date, expiration_days");
        $this->db->where('in_period',$scoring_period_id);
		$this->db->where('status',TRADE_PENDING_LEAGUE_APPROVAL);
		$this->db->where("DATEDIFF ('".date('Y-m-d H:m:s',(time()))."',offer_date)>=",intval($protestPeriodDays));
		$query = $this->db->get($this->tables['TRADES']);
		if ($query->num_rows() > 0) {
            foreach($query->result() as $row) {
				$this->processTrade($row->id, TRADE_COMPLETED, "Passed League Protest Deadline", $row->league_id);
			}
		}
		$query->free_result();
        return true;
	}
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
	public function makeTradeOffer($sendPlayers, $team2Id, $receivePlayers, $scoring_period_id, $comments = false, $prevTradeId = false,
									$expiresIn = false,$defaultExpiration = 1, $league_id = false, $team_id = false) {

		//print($defaultExpiration."<br />");
		if (sizeof($sendPlayers) == 0 && sizeof($receivePlayers) == 0 && !isset($team2Id) &&!isset($scoring_period_id)) return;

		if ($league_id === false) $league_id = $this->league_id;
		if ($team_id === false) $team_id = $this->id;
		//$expireDate = EMPTY_DATE_TIME_STR;
		//$day = 60*60*24;
		if ($expiresIn === false) {
			$expiresIn = $defaultExpiration;
		}
        /*if ($expiresIn == -1 || $expiresIn == 500) {
            $expireDate = $expiresIn;
        } else {
		   $expireDate = date('Y-m-d h:i:s',(strtotime(date('Y-m-d 00:00:00')) + ($day * $expiresIn)));
		*/
		// REMOVE ERRANT SEMICOLAN IN MULTIPLE TRADE DEALS
		$sendPlayerArr = array();
		foreach ($sendPlayers  as $playerStr) {
			$tmpPlayer = explode("_",$playerStr);
			if (strpos($tmpPlayer[0],";") !== false) {
				$idStr = explode(";",$tmpPlayer[0]);
				$tmpPlayer[0] = $idStr[0];
			}
			//echo("sending player = ".$tmpPlayer[0]."<br />");
			array_push($sendPlayerArr, implode("_",$tmpPlayer));
		}
		$receivePlayerArr = array();
		foreach ($receivePlayers  as $playerStr) {
			$tmpPlayer = explode("_",$playerStr);
			if (strpos($tmpPlayer[0],";") !== false) {
				$idStr = explode(";",$tmpPlayer[0]);
				$tmpPlayer[0] = $idStr[0];
			}
			//echo("receiving player = ".$tmpPlayer[0]."<br />");
			array_push($receivePlayerArr, implode("_",$tmpPlayer));
		}
		$data = array('team_1_id' =>$team_id, 'send_players' => serialize($sendPlayerArr), 'team_2_id' => $team2Id,'receive_players' => serialize($receivePlayerArr),
					  'status'=>1, 'league_id'=> $league_id,'comments'=>$comments, 'previous_trade_id'=>$prevTradeId,
					  'expiration_days'=>$expiresIn,'in_period'=>intval($scoring_period_id));

		$this->db->insert($this->tables['TRADES'],$data);

		return $this->db->insert_id();
	}
	/**
	 * PROCESS TRADE
	 * This function handles all trades repsonses submitted on the site. It switches on the response, takes
	 * approiate roster actions and logs transactions (if applicable) then composes and send messages to
	 * the users involved.
	 * @param $trade_id			The trade record ID
	 * @param $status			The trade status type
	 * @param $comments			(OPTIONAL) Response comments
	 */
	public function processTrade($trade_id, $status, $comments = "", $league_id = false){

		$outMess = "";

		if (!isset($trade_id) || !isset($status)) return false;
        if ($league_id === false) { $league_id = $this->league_id;}

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
						// REMOVE ERRANT SEMICOLAN IN MULTIPLE TRADE DEALS
						if (strpos($tmpPlayer[0],";") !== false) {
							$idStr = explode(";",$tmpPlayer[0]);
							$tmpPlayer[0] = $idStr[0];
						}	
						//print('TMP Player str = '.$playerStr.'<br />');
						$this->db->flush_cache();
						$this->db->where('player_id',$tmpPlayer[0]);
						$this->db->where('league_id',$league_id);
						$this->db->where('scoring_period_id',$trade['in_period']);
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
		//print($this->db->last_query()."<br />");
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				$ownerStr = getUsername($row->owner_id);
				array_push($protests,array('id'=>$row->id, 'trade_id'=>$row->trade_id, 'protest_date'=>$row->protest_date, 'team_id'=>$row->protest_team_id,
										   'team_name'=>$row->teamname." ".$row->teamnick, 'owner'=>$ownerStr, 'comments'=>$row->comments));
			} // END foreach
		} // END if
		$query->free_result();
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
		return $this->getTradeData($league_id, $team_id, $team_2_id, false, $status, $exclude_team_id, $countProtests, false, $limit, $startIndex);
	}
	/**
	 * GET TRADES FOR SCORING PERIOD
	 * Retrieves trade data from the TRADES table for all trades matching the current scoring period id.
	 * @param $league_id		The league id. Uses $this->$league_id if FALSE
	 * @param $team_id			The ID of the team making the offer. Ommited if FALSE
	 * @param $limit			The limit on rows to return. No limit if =1
	 * @param $startIndex		The first row to return. Stars with first row if 0.
	 */
	public function getTradesForScoringPeriod($league_id = false, $scoring_period_id = -1, $team_id = false, $team_2_id = false, $exclude_team_id = false, $countProtests = false, $status = 100, $limit = -1, $startIndex = 0) {
		if ($league_id === false) $league_id = $this->league_id;
		$trades = $this->getTradeData($league_id, $team_id, false, false, $status, $exclude_team_id, $countProtests, $scoring_period_id, $limit, $startIndex);
		// 1.0.3 Fix - PREVENT CALLING THE SAME QUERY TWICE WHEN GETTING TRADES NOT FOR THE CURRENT TEAM BUT FOR LEAGUE
		if (!$team_id === false || !$team_2_id === false)
			$moreTrades = $this->getTradeData($league_id, false, $team_id, false, $status, $exclude_team_id, $countProtests, $scoring_period_id, $limit, $startIndex);
		// EDIT 1.0.3
		// FIXED INCOMING OFFERS NOT BEING APPENDED TO THE FIRST LIST
		if (sizeof($moreTrades) > 0) {
			foreach($moreTrades as $mtrade) {
				$found = false;
				foreach($trades as $tradeData) {
					if ($mtrade['id'] == $tradeData['id']) {
						$found = true;
						break;
					} // END if
				} // END foreach($trades 
				if (!$found) 
					array_push($trades, $mtrade); // END if
			} // END foreach($moreTrades 
		} // END if
        return $trades;
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
		return $this->getTradeData($league_id, $team_id, $team_2_id, false, TRADE_COMPLETED, $exclude_team_id, $countProtests, false, $limit, $startIndex);
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
		return $this->getTradeData($league_id, false, false, false, 100, false, $countProtests, false, $limit, $startIndex);
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

		$this->db->select($this->tables['TRADES'].".id, offer_date, status, team_1_id, send_players, receive_players, team_2_id, tradeStatus, in_period, previous_trade_id, expiration_days, comments, response_date");
		$this->db->join($this->tables['TRADES_STATUS'],$this->tables['TRADES_STATUS'].".id = ".$this->tables['TRADES'].".status", "right outer");
		$this->db->where($this->tables['TRADES'].".id",$trade_id);
		$query = $this->db->get($this->tables['TRADES']);

		if ($query->num_rows() > 0) {
			$row = $query->row();
			$team_1_name = $this->getTeamName($row->team_1_id);
			$team_2_name = $this->getTeamName($row->team_2_id);
			// FIX ERRNAT SEMI COLAN ISSUE
			$sendPlayerArr = array();
			$sendPlayers = unserialize($row->send_players);
			foreach($sendPlayers as $playerStr) {
				$tmpPlayer = explode("_",$playerStr);
				if (strpos($tmpPlayer[0],";") !== false) {
					$idStr = explode(";",$tmpPlayer[0]);
					$tmpPlayer[0] = $idStr[0];
				}
				array_push($sendPlayerArr, implode("_",$tmpPlayer));
			}
			$receivePlayerArr = array();
			$receive_players = unserialize($row->receive_players);
			foreach($receive_players as $playerStr) {
				$tmpPlayer = explode("_",$playerStr);
				if (strpos($tmpPlayer[0],";") !== false) {
					$idStr = explode(";",$tmpPlayer[0]);
					$tmpPlayer[0] = $idStr[0];
				}
				array_push($receivePlayerArr, implode("_",$tmpPlayer));
			}
			$trade = array('trade_id'=>$row->id, 'offer_date'=>$row->offer_date, 'team_1_name'=>$team_1_name,'team_1_id'=>$row->team_1_id,
													  'send_players'=>$sendPlayerArr, 'receive_players'=>$receivePlayerArr,
													  'team_2_name'=>$team_2_name,'team_2_id'=>$row->team_2_id, 'previous_trade_id'=>$row->previous_trade_id, 'in_period'=>$row->in_period,
													  'status'=>$row->status,'tradeStatus'=>$row->tradeStatus,'comments'=>$row->comments,'expiration_days'=>$row->expiration_days,
													  'response_date'=>$row->response_date);
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
	public function getTradeData($league_id, $team_id = false, $team_2_id = false, $trade_id = false, $status = false, $exclude_team_id = false, $countProtests = false, $scoring_period_id = false, $limit = -1, $startIndex = 0) {

		if ($league_id === false) { $league_id = $this->league_id; }

		$trades = array();
		$selectStr = $this->tables['TRADES'].".id, offer_date, status, team_1_id, send_players, receive_players, team_2_id, tradeStatus, ".$this->tables['TRADES'].".status, in_period, previous_trade_id, expiration_days, ".$this->tables['TRADES'].".comments, response";
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
        if ($scoring_period_id !== false) {
			$this->db->where('in_period',$scoring_period_id);
		}
		if ($status !== false && $status != 100) {
			$this->db->where('status',$status);
		} else {
			if ($status != 100) {
				$this->db->where('(status = '.TRADE_OFFERED.' OR status = '.TRADE_PENDING_LEAGUE_APPROVAL.' OR status = '.TRADE_PENDING_COMMISH_APPROVAL.")");
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
							// SANITIZE ERRANT SEMICOLANS FROM PLAYER IDS
							$playerDataArr = array();
							foreach($fieldData as $key => $plyr) {
								$playerData = explode("_",$plyr);
								if (strpos($playerData[0],";") !== false) {
									$idStr = explode(";",$playerData[0]);
									$playerData[0] = $idStr[0];
								}
								array_push($playerDataArr, implode("_",$playerData));
							}
							$playerDetails = getFantasyPlayersDetails($playerDataArr);
							foreach ($playerDataArr as $playerId) {
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
													  'status'=>$row->status,'tradeStatus'=>$row->tradeStatus, 'comments'=>$row->comments,'expiration_days'=>$row->expiration_days,'protest_count'=>$protestCount,'response'=>$row->response));			}
		}
		$query->free_result();
		return $trades;
	}
	/*-----------------------------------------------
	/	STATS
	/----------------------------------------------*/
	public function getTeamStats($countOnly = false, $team_id = false, $player_type=1, $position_type = -1,
								 $role_type = -1, $stats_range = 1, $scoring_period_id = -1, $min_var = 0, $limit = -1, $startIndex = 0, $ootp_league_id = false, $ootp_league_date = false, $rules = array(),
								 $includeList = array(), $searchType = 'all', $searchParam = -1, $sortOrder = false) {
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
		$sql = 'SELECT fantasy_players.id, "add", fantasy_players.id,fantasy_players.positions, players.player_id, players.position as position, players.role as role, players.first_name, players.last_name, players.injury_is_injured, players.injury_dtd_injury, players.injury_career_ending, players.injury_dl_left, players.injury_left, players.injury_id,rating,';
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
			$order = ($sortOrder !== false) ? $sortOrder : 'ab';
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
			$order = ($sortOrder !== false) ? $sortOrder : 'ip';
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
		//echo($this->_NAME." stats_range = ".$stats_range."<br />");
		if ($ootp_league_date === false || $ootp_league_date == EMPTY_DATE_STR) {
			$base_year = time();
		} else {
			$base_year = strtotime($ootp_league_date);
		}
		if ($stats_range != 4) {
			if($stats_range > 0) {
				$base_year = $base_year-($year_time * $stats_range);
			}
			$sql .= ' AND '.$tblName.'.year = '.date('Y',$base_year);
		} else {
			$sql .= ' AND ('.$tblName.'.year = '.date('Y',$base_year-($year_time))." OR ".$tblName.'.year = '.date('Y',time()-($year_time * 2))." OR ".$tblName.'.year = '.date('Y',time()-($year_time * 3)).")";
		}
		if (!empty($where)) {
			$sql .= " ".$where;
		}
		$sql.=' GROUP BY '.$tblName.'.player_id';
		if (sizeof($rules) > 0 && isset($rules['scoring_type']) && $rules['scoring_type'] == LEAGUE_SCORING_HEADTOHEAD) {
			$order = 'fpts';
		} else {
			$order = 'rating';
		}
		$sql.=" ORDER BY ".$order." DESC ";
		if ($limit != -1 && $startIndex == 0) {
			$sql.="LIMIT ".$limit;
		} else if ($limit != -1 && $startIndex > 0) {
			$sql.="LIMIT ".$startIndex.", ".$limit;
		}
		//echo("sql = ".$sql."<br />");
		$query = $this->db->query($sql);
		//echo($this->db->last_query()."<br />");
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
	/
	/	ROSTERS
	/
	----------------------------------------*/
	/**
	 * GET PLAYER ROSTER STATUS
	 * Enter description here ...
	 * @param  $player_id
	 * @param  $score_period
	 * @param  $team_id
	 */
	public function getPlayerRosterStatus($player_id = false,$score_period_id = false, $team_id = false) {
		$status = array();
		$code = -1;
		$message = "";
		$count = 0;

		if ($player_id === false || $score_period_id === false || $team_id === false) { return false; }

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
		$this->db->where('scoring_period_id',$score_period_id);
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
	 * @param $score_period_id
	 * @param $team_id
	 */
	public function getPlayersRosterStatus($players,$score_period_id, $team_id) {
		$status = array();
		if ($players === false || !is_array($players) || sizeof($players) <= 0 ||
		$score_period_id === false || $team_id === false) { return false; }

		foreach($players as $player_id) {
			array_push($status,$this->getPlayerRosterStatus($player_id,$score_period_id,$team_id));
		}
		return $status;
	}
	/**
	 * SAVE ROSTER CHANGES
	 * Saves the passed roster to the database.
	 * 
	 * @param $roster
	 * @param $score_period
	 * @param $team_id
	 * 
	 */
	public function saveRosterChanges($roster,$score_period, $team_id = false) {
		$success = true;
		if (!isset($roster) || sizeof($roster) == 0) return;

		if ($team_id === false) { $team_id = $this->id; }

		foreach($roster as $player_info) {
			$id = $player_info['id'];
			array_shift($player_info);
			$this->db->where('player_id',$id);
			$this->db->where('team_id',$team_id);
			$this->db->where('scoring_period_id',$score_period['id']);
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
	/**
	 * APPLY ROSTER CHANGES
	 * Checks changes to player status and adjust roster position based on those changes.
	 * 
	 * @param $roster
	 * @param $score_period
	 * @param $team_id
	 * 
	 */
	public function applyRosterChanges($input, $score_period, $team_id = false) {

		if (!isset($input)) return;

		if ($team_id === false) { $team_id = $this->id; }

		$roster = $this->dataModel->getBasicRoster($score_period['id']);
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
	/
	/	WAIVERS
	/
	/---------------------------------------*/
	/**
	 * GET WAIVER ORDER
	 * Returns the team order for waivers for the specified league (or default league).
	 * 
	 * @param $league_id
	 * @param $idOnly
	 * 
	 */
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
	 *  @param	$limit - Number of claims to limit transaction to
	 *  @param	$startIndex - Start Index for pagination
	 *  @param	$team_id - Team ID
	 *  @param	$league_id - League ID
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
	/
	/ 	GENERAL TEAM DATA FUNCTIONS
	/
	/------------------------------------*/
	/**
	 * 	GET TEAMS FOR LEAGUE.
	 * 	Returns all team IDs for the specified league_id. If no id is passed, the current league id of the loaded team is used.
	 *
	 * 	@param	$league_id		{int}	The League Id
	 * 	@return					{Array}	Array of team IDs
	 *
	 *  @since	1.0.6
	 *  @access	public
	 */
	public function getTeamsForLeague($league_id = false) {

		if ($league_id === false) { $league_id = $this->league_id; }

		$teamList = array();
		$this->db->select('id');
		$this->db->where('league_id',$league_id);
		$query = $this->db->get($this->tblName);
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				array_push($teamList,$row->id);
			}
		}
		$query->free_result();
		return $teamList;

	}
	/**
	 * 	GET TEAM BY OWNER ID.
	 * 	Returns the team ID owned by the passed user.
	 *
	 * 	@param	$user_id		{int}	The User Id
	 * 	@param	$league_id		{int}	The League Id
	 * 	@return					{int}   The team Id, FALSE if no team found
	 *
	 *  @since	1.0.3 PROD
	 *  @access	public
	 */
	public function getTeamByOwnerId($ownerId = false, $league_id = false) {

		if ($ownerId === false) { return false; }
		if ($league_id === false) { $league_id = $this->league_id; }

		$team_id = false;
		$this->db->select('id');
		$this->db->where('league_id',$league_id);
		$this->db->where('owner_id',$ownerId);
		$query = $this->db->get($this->tblName);
		if ($query->num_rows() > 0) {
			$row = $query->row();
			$team_id = $row->id;
		}
		$query->free_result();
		return $team_id;

	}
	/**
	* 	CREATE TEAM.
	*  	Creates a new team for the specified league id.  This function returns the ID of the newly
	*  	created team on success and false if required data is missing.
	*
	*  @param	$teamname			{String} 	The Team Name
	*  @param	$teamnick			{String} 	The team nick name. Create one ranomly if no argument is passed.
	*  @param	$league_id			{int}		League ID
	*  @param	$division_id		{int}		(optional) Division ID. Defaults to -1 if not specified
	*  @return						{int}		The newly created team ID
	*
	*  @since 	1.0.6
	*  @access	public
	*/
	public function createTeam($teamname = false, $teamnick = false, $league_id = false, $division_id = false, $owner_id = false) {

		if ($teamname === false) {
			$this->errorCode = 1;
			$this->statusMess = "A team name and nickname are required but one or more were not recieved.";
			return false;
		} // END if

		if ($teamnick === false || empty($teamnick)) $teamnick = getRandomTeamNickname();  // END if

		if ($league_id === false) $league_id = $this->league_id;  // END if

		$data = array('teamname'=>$teamname,'teamnick'=>$teamnick,'league_id'=>$league_id);

		if ($division_id !== false) {
			$data = $data + array('division_id'=>$division_id);
		} // END if

		if ($owner_id !== false) {
			$data = $data + array('owner_id'=>$owner_id);
		} else {
			$data = $data + array('owner_id'=>-1);
		} // END if

		$this->db->insert($this->tblName,$data);

		return $this->db->insert_id();
	}
	/**
	 * 	CREATE TEAMS BY ARRAY.
	 *  Accepts an array with up to fours params:
	 *  <ul>
	 *  	<li>teamname (Required)</li>
	 *  	<li>teamnick (Optioanl)</li>
	 *  	<li>league_id (Optional)</li>
	 *  	<li>division_id (Optional)</li>
	 *  </ul>
	 *  <p>
	 *  <i>Example:</i><br />
	 *  <code>
	 *  $team_list = array('teamname'=>'Fox','teamnick'=>'Trot','league_id'=>2);
	 *  createTeamsByArray($team_list);
	 *  </code>
	 *  </p>
	 *  <p>
	 *  The league_id param can either be passed as an argument to the function or as a property of the array. The function
	 *  defaults to the league ID arg if no league_id is passed in the array.
	 *	</p>
	 *  This function returns false if all league_ids are -1.
	 *	</p>
	 *
	 *  @param	$team_list			{Array} 	Array of team data (See format)
	 *  @param	$league_id			{int}		League ID (Optional, can be passed per division item)
	 *  @return						{Array} 	Array of created team Ids
	 *
	 *  @since 	1.0.6
	 *  @access	public
	 */
	public function createTeamsByArray($team_list = false, $league_id = false) {

		if ($league_id === false) $league_id = $this->league_id;  // END if

		if ($team_list === false) {
			$this->errorCode = 1;
			$this->statusMess = "No team list was received.";
			return false;
		} // END if

		if (!is_array($team_list) || sizeof($team_list) < 1) {
			$this->errorCode = 2;
			$this->statusMess = "The team_list argument received was not a valid array or contained no items.";
			return false;
		} // END if

		$teamIds = array();
		$teamname = '';
		$teamnick = '';
		$division_id = -1;
        $div_array = array();
		// Loop through array and add each division
		foreach($team_list as $team_array) {
			if (isset($team_array['teamname'])) {
				$teamname = $team_array['teamname'];
			} // END if
			if (isset($team_array['teamnick'])) {
				$teamnick = $team_array['teamnick'];
			} // END if
			if (isset($team_array['league_id'])) {
				$league_id = $team_array['league_id'];
			} // END if
			if (isset($team_array['division_id'])) {
				$division_id = $team_array['division_id'];
			} // END if
			if (!isset($team_array['owner_id'])) {
				$owner_id = -1;
			} else {
				$owner_id = $team_array['owner_id'];
			}// END if

			$thisTeamId = $this->createTeam($teamname,$teamnick,$league_id,$division_id,$owner_id);
			if ($thisTeamId && $thisTeamId != -1) {
				array_push($teamIds, $thisTeamId);
			} else {
				$this->errorCode = 4;
				$this->statusMess = "The division ".$teamname." was not added successfully. Error: ".$this->statusMess;
				break;
			} // END if
		} // END foreach
		return $teamIds;
	}
	/**
	 * 	GET TEAM OWNER ID.
	 * 	Returns the owner ID of the passed team owner.
	 *
	 * 	@param	$team_id		{int}	The Team Id
	 * 	@return					{Array}	Array of team IDs
	 *
	 *  @since	1.0
	 *  @access	public
	 */
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
	/**
	 * 	GET TEAM NAME.
	 * 	Utility function that returns the complete team name as <code>{TEAMNAME NICKNAME}</code>.
	 *
	 * 	@param	$team_id		{int}		The Team Id
	 * 	@return					{String}	Team name
	 *
	 *  @since	1.0
	 *  @access	public
	 */
	public function getTeamName($team_id = FALSE) {
		if ($team_id === false) { $team_id = $this->id; } // END if

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
	/**
	 * 	GET TEAM AVATAR.
	 * 	Utility function that returns the team image name as <code>{IMAGENAME.EXT}</code>.
	 *
	 * 	@param	$team_id		{int}		The Team Id
	 * 	@return					{String}	Team name
	 *
	 *  @since	1.0
	 *  @access	public
	 */
	public function getAvatar($team_id = FALSE) {
		if ($team_id === false) { $team_id = $this->id; } // END if

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
	/**
	 *
	 *
	 *
	 */
    public function getGamesPlayedByRoster($team_id = false, $scoring_period_id = false, $ootp_league_id = 100, $year = false, $league_id = false) {

        if ($team_id === false) { $team_id = $this->id; } // END if
        if ($year === false) { $year = date('Y'); } // END if
		if ($league_id === false) { $league_id = $this->league_id; } // END if

        $game_stats= array();
        $player_list=$this->getBasicRoster($scoring_period_id,$team_id,true);
        foreach($player_list as $data) {
            $player_stats = array('player_id'=>$data['player_id'],"player_name" =>$data['player_name'],'role'=>$data['player_role'],'position'=>$data['player_position']);
            if ($data['player_position'] != 1) {
				$u_game_count = 0;
				$of_game_count = 0;
				$if_game_count = 0;
				$mi_game_count = 0;
				$ci_game_count = 0;
				$this->db->flush_cache();
				$this->db->select("g, position");
				$this->db->from("players_career_fielding_stats");
				$this->db->where("player_id",$data['ootp_player_id']);
				$this->db->where("level_id",1);
				$this->db->where("league_id",$ootp_league_id);
				$this->db->where("year",$year);
				$gquery = $this->db->get();
				//print("batters: ".$this->db->last_query()."<br />");
				if ($gquery->num_rows() > 0) {
                    foreach ($gquery->result() as $grow) {
						$u_game_count += $grow->g;
						if ($grow->position == 3 || $grow->position == 4 || $grow->position == 5 || $grow->position == 6) {
							$if_game_count += $grow->g;
							if($grow->position == 4 || $grow->position == 5) {
								$mi_game_count += $grow->g;
							} else {
								$ci_game_count += $grow->g;
							}
						}
						if ($grow->position == 7 || $grow->position == 8 || $grow->position == 9) {
                            $of_game_count += $grow->g;
						} else {
							$player_stats = $player_stats + array($grow->position=>$grow->g);
						} // END if
					} // END foreach
				} // END if
				$gquery->free_result();
				if ($u_game_count > 0) { $player_stats = $player_stats + array(get_pos_num('U')=>$u_game_count); } // END if
				if ($of_game_count > 0) { $player_stats = $player_stats + array(get_pos_num('OF')=>$of_game_count); } // END if
			} else {
				$this->db->flush_cache();
				$this->db->select("g, gs");
				$this->db->from("players_career_pitching_stats");
				$this->db->where("player_id",$data['ootp_player_id']);
				$this->db->where("level_id",1);
				$this->db->where("split_id",1);
				$this->db->where("league_id",$ootp_league_id);
				$this->db->where("year",$year);
				$gquery = $this->db->get();
				if ($gquery->num_rows() > 0) {
					foreach ($gquery->result() as $grow) {
                        $player_stats = $player_stats + array(11=>$grow->gs);
                        if ($grow->gs > 0 && $grow->gs != $grow->g) {
                            $gDiff = $grow->g - $grow->gs;
                        } else {
                            $gDiff = $grow->g;
                        } // END if
                        $player_stats = $player_stats + array(12=>$gDiff);
					} // END foreach
				} // END if
				$gquery->free_result();
			} // END if
			asort($player_stats);
			array_push($game_stats,$player_stats);
		} // END foreach
		$this->db->flush_cache();
        return $game_stats;
	}
	/**
	 *	GET RECENT GAMES.
	 *	Returns a list of recent games for Head to Head Leagues.
	 *
	 *  @param	$score_period - The scoring period 
	 *  @param	$team_id - (OPTIONAL) - uses loaded Model ID if false
	 *	@return	{Array} of games
	 * 	@since 	1.0.3
	 */
	public function getRecentGames($score_period = -1, $team_id = false) {
		
		if ($team_id === false) { $team_id = $this->id; }
		$games = array();
		$sql = "SELECT flg.id, flg.home_team_id, flg.away_team_id, flg.home_team_score, flg.away_team_score ";
		$sql .= "FROM fantasy_leagues_games as flg ";
		$sql .= "WHERE (flg.home_team_id = ".$team_id." OR flg.away_team_id = ".$team_id.") AND (flg.home_team_score != 0 OR flg.away_team_score != 0)";
		if ($score_period != -1) {
			$sql .= " AND flg.scoring_period_id = ".$score_period;
		} // END if
		$query = $this->db->query($sql);
		//echo($this->db->last_query()."<br />");
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$outCome = -1;
				if ($row->away_team_id == $team_id) {
					$teamLoc = 'away';
					$opponentId = $row->home_team_id;
					if ($row->away_team_score > $row->home_team_score) { $outCome = 1; }
				} else {
					$teamLoc = 'home';
					$opponentId = $row->away_team_id;
					if ($row->home_team_score > $row->away_team_score) { $outCome = 1; }
				} // END if
				array_push($games, array("game_id" => $row->id, "opp_team_id"=>$opponentId,"teamname"=>$this->getTeamName($opponentId), "avatar"=>$this->getAvatar($opponentId),
									'team_loc'=> $teamLoc, 'away_team_score'=>$row->away_team_score, 'home_team_score' => $row->home_team_score, 'outcome' => $outCome));
			} // END foreach
		} // END if
		$query->free_result();
		return $games;
	}
	/**
	 *	GET UPCOMING GAMES.
	 *	Returns the next upcoming games for a team in a Head to Head League.
	 *
	 *  @param	$score_period - The scoring period 
	 *  @param	$team_id - (OPTIONAL)
	  *	@return	{Array} of games
	 * 	@since 	1.0.3
	 */
	public function getUpcomingGames($score_period = -1, $team_id = false) {
		
		if ($team_id === false) { $team_id = $this->id; }
		$opponents = array();
		$opponentId = -1;
		$sql = "SELECT flg.home_team_id, flg.away_team_id ";
		$sql .= "FROM fantasy_leagues_games as flg ";
		$sql .= "WHERE (flg.home_team_id = ".$team_id." OR flg.away_team_id = ".$team_id.") AND (flg.home_team_score = 0 OR flg.away_team_score = 0)";
		if ($score_period != -1) {
			$sql .= " AND flg.scoring_period_id = ".$score_period;
		} // END if
		$query = $this->db->query($sql);
		//echo($this->db->last_query()."<br />");
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$opponentId = ($row->away_team_id == $team_id) ? $row->home_team_id : $row->away_team_id;
				array_push($opponents, array("opp_team_id"=>$opponentId,"teamname"=>$this->getTeamName($opponentId), "avatar"=>$this->getAvatar($opponentId)));
			}
		} // END if
		$query->free_result();
		return $opponents;
	}
	/**
	 *	GET TEAM RECORD.
	 *	Returns the next upcoming games for a team in a Head to Head League.
	 *
	 *  @param	$score_period - The scoring period 
	 *  @param	$team_id - (OPTIONAL)
	  *	@return	{Array} of games
	 * 	@since 	1.0.3
	 */
	public function getTeamRecord($team_id = false, $year = false) {
		
		if ($team_id === false) { $team_id = $this->id; }
		if ($year === false) { $year = date("Y"); }

		$record = array();
		$sql = "SELECT w, l, pos, pct, gb, streak, magic_number, fd.division_name ";
		$sql .= "FROM ".$this->tables['TEAM_RECORDS']." ";
		$sql .= "LEFT JOIN fantasy_teams as ft ON ft.id = fantasy_teams_record.team_id ";
		$sql .= "LEFT JOIN fantasy_divisions fd ON fd.id = ft.id ";
		$sql .= "WHERE team_id = ".$team_id." AND year = ".$year;
		$query = $this->db->query($sql);
		//echo($this->db->last_query()."<br />");
		if ($query->num_rows() > 0) {
			$row = $query->row();
			foreach ($query->result() as $row) {
				$record = array("w"=>$row->w,"l"=>$row->l,"pos"=>$row->pos,"streak"=>$row->streak,"gb"=>$row->gb,"pct"=>$row->pct,
				"magic_number"=>$row->magic_number,"division_name"=>$row->division_name);
			}
		} // END if
		$query->free_result();
		return $record;
	}
	/**
	 *	GET BASIC ROSTER.
	 *	Returns a list of players for the specified team (or models loaded team if FALSE). 
	 *	Scoring period ID gets the list for the current or requested scoring period.
	 *	Extended IDs returns player IDs for rendering clickable links.
	 *
	 *  @param	$score_period - The scoring period 
	 *  @param	$team_id - (OPTIONAL)
	 *  @param	$extendedIds - (OPTIONAL) Adds Fnatasy Player ID and OOTP Player IDs for linking to player profiles
	 *	@return	array of pitchers
	 */
	public function getBasicRoster($score_period = -1, $team_id = false, $extendedIds = false) {

		if ($team_id === false) { $team_id = $this->id; }
		$roster = array();
		//echo("this team Id = ".$team_id."<br />");
		$sql = "SELECT player_id FROM fantasy_rosters WHERE team_id = ".$team_id;
		if ($score_period != -1) {
			$sql .= " AND scoring_period_id = ".$score_period;
		} // END if
		$query = $this->db->query($sql);
		$playerIds = array();
		foreach ($query->result() as $row) {
			array_push($playerIds,$row->player_id);
		} // END foreach
		//echo("getBasicRoster last SQL pre ids = ".$this->db->last_query()."<br />");
		$query->free_result();
		//echo("Player ids = ".sizeof($playerIds)."<br />");
		if (sizeof($playerIds) > 0) {
			$this->db->select('fantasy_players.id, fantasy_rosters.player_id, fantasy_players.player_id AS ootp_player_id, first_name, last_name, fantasy_rosters.player_position, fantasy_rosters.player_role, fantasy_rosters.player_status');
			$this->db->join('fantasy_rosters','fantasy_players.id = fantasy_rosters.player_id','left');
			$this->db->join('players','players.player_id = fantasy_players.player_id','right outer');
			$this->db->where_in("fantasy_players.id",$playerIds);
			$this->db->where('fantasy_rosters.team_id',$team_id);
			if ($score_period != -1) {
				$this->db->where('fantasy_rosters.scoring_period_id',$score_period);
			} // END if
			$this->db->order_by('player_position, player_role');
			$query = $this->db->get('fantasy_players');
			//echo($this->db->last_query()."<br />");
			if ($query->num_rows() > 0) {
				foreach ($query->result() as $row) {
					$tmpData = array('player_name'=>$row->first_name." ".$row->last_name,'id'=>$row->player_id,'player_position'=>$row->player_position,'player_role'=>$row->player_role,
												'player_status'=>$row->player_status);
					if ($extendedIds !== false) {
						$tmpData = $tmpData + array('player_id'=>$row->id,'ootp_player_id'=>$row->ootp_player_id);
					}
					array_push($roster,$tmpData);
				} // END foreach
			} // END if
			$query->free_result();
		} // END if
		//echo("Team, get roster, size of roster = ".sizeof($roster)."<br />");
		return $roster;
	}
	/*------------------------------------------------------
	/
	/
	// SPECIAL QUERIES
	/
	/
	/------------------------------------------------------*/
	/**
	 *	GET TEAM DETAILS.
	 *	Returns the details for the given team.
	 *  @param	$team_id - (OPTIONAL)
	 *	@return	array of pitchers
	 */
	public function getTeamDetails($team_id = false) {
		
		if ($team_id === false && $this->id != -1) { $team_id = $this->id; }
		if ($team_id === false) { return false; }
		
		$team = array();
		$this->db->select($this->tblName.'.id,'.$this->tblName.'.league_id,league_name,division_id,division_name, teamname,teamnick,owner_id,'.$this->tblName.'.avatar,firstName, lastName, username');
		$this->db->join($this->tables['LEAGUES'], $this->tables['LEAGUES'].'.id = '.$this->tblName.'.league_id','left');
		$this->db->join($this->tables['DIVISIONS'], $this->tables['DIVISIONS'].'.id = '.$this->tblName.'.division_id','left');
		$this->db->join('users_core','users_core.id = '.$this->tblName.'.owner_id','left');
		$this->db->join('users_meta','users_meta.userId = '.$this->tblName.'.owner_id','left');
		$this->db->where($this->tblName.'.id', $team_id);
		$query = $this->db->get($this->tblName);
		//print($this->db->last_query()."<br />");
		if ($query->num_rows() > 0) {
			$team = $query->row_array();
			$owner = (!empty($team['firstName']) && !empty($team['lastName'])) ? $team['firstName']." ".$team['lastName'] : $team['username'];
			$team['owner'] = $owner;
		}
		$query->free_result();
		return $team;
	}
	/**
	 *	GET BATTERS.
	 *	Returns a list of batters for the specified team. Scoring period ID gets the list fot eh current or requested scoring period.
	 *  @param	$scoring_period - The scoring period 
	 *  @param	$team_id - (OPTIONAL)
	 *  @param	$status - (OPTIONAL)
	 *	@return	array of pitchers
	 */
	public function getBatters($scoring_period = -1, $team_id = false, $status = 1) {

		if ($team_id === false && $this->id != -1) { $team_id = $this->id; } // END if

		$players = array();
		$this->db->select('fantasy_players.id, fantasy_players.player_id, first_name, last_name, position, teams.abbr, players.team_id, players.injury_is_injured, players.injury_dtd_injury, players.injury_career_ending, players.injury_dl_left, players.injury_left, players.injury_id, player_position, players.team_id, fantasy_players.positions, fantasy_players.player_status, own, own_last, start, start_last');
		$this->db->join('fantasy_players','fantasy_players.id = fantasy_rosters.player_id','left');
		$this->db->join('players','players.player_id = fantasy_players.player_id','right outer');
		$this->db->join('teams','teams.team_id = players.team_id','right outer');
		$this->db->where('fantasy_rosters.team_id',$team_id);
		$this->db->where('fantasy_rosters.player_position <>', 1);
		if ($status != -999) {
			$this->db->where('fantasy_rosters.player_status',$status);
		} // END if
		if ($scoring_period != -1) {
			$this->db->where('fantasy_rosters.scoring_period_id',$scoring_period);
		} // END if
		$this->db->group_by('fantasy_rosters.player_id');
		$this->db->order_by('fantasy_rosters.player_position');
		$query = $this->db->get($this->tables['ROSTERS']);
		//echo($this->db->last_query()."<br />");
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$players = $players + array($row->player_id=>array('id'=>$row->id,'first_name'=>$row->first_name, 'last_name'=>$row->last_name,
																  'position'=>$row->position, 'team_id'=>$row->team_id,'team_abbr'=>$row->abbr,
																  'player_position'=>$row->player_position,'injury_is_injured'=>$row->injury_is_injured,
																  'injury_dl_left'=>$row->injury_dl_left, 'injury_left'=>$row->injury_left, 'injury_dtd_injury'=>$row->injury_dtd_injury,
																  'injury_id'=>$row->injury_id,'injury_career_ending'=>$row->injury_career_ending,
																  'positions'=>$row->positions, 'player_status'=>$row->player_status, 'own_last'=>$row->own_last,
									   							  'start_last'=>$row->start_last,'own'=>$row->own,'start'=>$row->start));
			} // END foreach
		} // END if
		$query->free_result();
		return $players;
	}

	/**
	 *	GET PITCHERS.
	 *	Returns a list of pitchers for the specified team. Scoring period ID gets the list fot eh current or requested scoring period.
	 *  @param	$scoring_period - The scoring period 
	 *  @param	$team_id - (OPTIONAL)
	 *  @param	$status - (OPTIONAL)
	 *	@return	array of pitchers
	 */
	public function getPitchers($scoring_period = -1, $team_id = false, $status = 1) {
		if ($team_id === false && $this->id != -1) { $team_id = $this->id; }

		$players = array();
		$this->db->select('fantasy_players.id, fantasy_players.player_id, first_name, last_name, position, players.team_id,players.role,teams.abbr, players.injury_is_injured, players.injury_dtd_injury, players.injury_career_ending, players.injury_dl_left, players.injury_left, players.injury_id, player_position, player_role, fantasy_players.positions, fantasy_players.player_status, own, own_last, start, start_last');
		$this->db->join('fantasy_players','fantasy_players.id = fantasy_rosters.player_id','left');
		$this->db->join('players','players.player_id = fantasy_players.player_id','right outer');
		$this->db->join('teams','teams.team_id = players.team_id','left');
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
		//echo($this->db->last_query()."<br />");
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
	 *	GET COMPLETE ROSTER.
	 *  @param	$scoring_period - The scoring period 
	 *  @param	$team_id - (OPTIONAL)
	 *	@return	array of pitchers and batters
	 */
	public function getCompleteRoster($scoring_period, $team_id = false) {
		if ($team_id === false) { $team_id = $this->id; }

		return array($this->getBatters($scoring_period, $team_id)+$this->getPitchers($scoring_period, $team_id),
				    $this->getBatters($scoring_period, $team_id,-1)+$this->getPitchers($scoring_period, $team_id, -1),
					$this->getBatters($scoring_period, $team_id,2)+$this->getPitchers($scoring_period, $team_id, 2));

	}
	/*---------------------------------------------------------
	/
	/	DATA OPERATIONS
	/
	/--------------------------------------------------------*/

	/**
	 * 	DELETE TEAMS.
	 * 	Deletes all teams for the specified league_id. If no id is passed, the current league id of the loaded team is used.
	 *
	 * 	@param	$league_id		{int}	The League Id
	 * 	@return					{Boolean}	TRUE on success
	 *
	 *  @since	1.0.6
	 *  @access	public
	 */
	public function deleteTeams($league_id = false) {

		if ($league_id === false) { $league_id = $this->league_id; }

		//DELETE AVATARS
		$team_ids = $this->getTeamsForLeague($league_id);
		foreach ($team_ids as $team_id) {
			$avtr = $this->getAvatar($team_id);
			if (!empty($avtr)) {
				@unlink(PATH_TEAMS_AVATAR_WRITE.$avtr);
			}
		}
		$this->db->where('league_id',$league_id);
		$this->db->delete($this->tblName);
		return true;
	}
	/*---------------------------------------
	/
	/	PRIVATE/PROTECTED FUNCTIONS
	/
	/--------------------------------------*/
	/**
	 *	DELETE TEAM DATA.
	 *  @param	$table - Table name for delete 
	 *  @param	$team_id 
	 *	@return	boolean TRUE if successful, FALSE if not
	 */
	protected function deleteTeamData($table = false, $team_id = false) {

		if ($table === false) {
			return false;
		}
		if ($team_id === false) {
			$team_id = $this->id;
		}

		$this->db->where('team_id',$team_id);
		$this->db->delete($table);

		return true;
	}
}