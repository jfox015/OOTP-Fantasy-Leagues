<?php
/**
 *	USER META MODEL CLASS.
 *	This class is a companion model to the USer Auth model. it provides extended meta infomration
 *	in the form of name, address, and other personal, no authoritative information.
 *
 *	@author			Jeff Fox <jfox015 (at) gmail (dot) com>
 *  @copyright   	(c)2009-11 Jeff Fox/Aeolian Digital Studios
 *	@version		1.0
 *
*/
class user_meta_model extends base_model {

	/*--------------------------------
	/	VARIABLES
	/-------------------------------*/
	/**
	 *	SLUG.
	 *	@var $_NAME:Text
	 */
	var $_NAME = 'user_meta_model';

	var $userId = -1;
	var $firstName = '';
	var $lastName = '';
	var $nickName = '';
	var $city = '';
	var $state = '';
	var $country = -1;
	var $zipCode = '';
	var $title = '';
	var $bio = '';
	var $dateOfBirth = EMPTY_DATE_STR;
	var $gender = '';
	var $avatar = '';
	var $custom = '';
	/**
	* 	Timezone
	*
	*  @var timezone:Int
	*  @since	1.0.6
	*
	*/
	var $timezone = '';

	/*--------------------------------------
	/	C'TOR
	/	Creates a new instance of user_meta_model
	/-------------------------------------*/
	function user_meta_model() {
		parent::__construct();

		$this->tblName = $this->tables['users_meta'];
		$this->tables['LEAGUES'] = 'fantasy_leagues';
		$this->tables['TEAMS'] = 'fantasy_teams';
		$this->tables['DRAFT'] = 'fantasy_draft';
		$this->tables['INVITES'] = 'fantasy_invites';
		$this->tables['REQUESTS'] = 'fantasy_leagues_requests';
		$this->tables['TRADES'] = 'fantasy_teams_trades';


		$this->fieldList = array('firstName', 'lastName', 'nickName', 'city', 'state', 'country', 'zipCode', 'title', 'bio', 'gender','timezone');
		$this->conditionList = array('birthDay','birthMonth','birthYear','avatarFile');
		$this->readOnlyList = array('userId','dateOfBirth', 'avatar', 'custom');
		$this->uniqueField = 'userId';
		$this->joinCode = "M";
		$this->textList = array('nickName');

		$this->columns_select = array($this->tblName.'.id','userId','firstName','lastName','nickName','dateOfBirth','gender','country');
		$this->columns_text_search = array('firstName','lastName','nickName','bio','title');
		$this->columns_alpha_search = array('lastName');

		$this->addSearchFilter('country','Country','cntryName','cntryName');

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
			$this->dateModified = date('Y-m-d h:m:s');
			if ($userId != -1) {
				$this->lastModifiedBy = $userId;
			} // END if
			if ($input->post('birthMonth') && $input->post('birthDay') && $input->post('birthYear'))
				$this->dateOfBirth = date('Y-m-d',strtotime($input->post('birthYear')."-".$input->post('birthMonth')."-".$input->post('birthDay')));  // END if
			if (isset($_FILES['avatarFile']['name']) && !empty($_FILES['avatarFile']['name'])) {
				$success = $this->uploadFile('avatar',PATH_USERS_AVATAR_WRITE,$input,'avatar',$this->userId.$this->lastName);
			}
		}
		return $success;
	}
	public function getTimezone($userId = false) {

		if ($userId === false) { $userId = $this->userId; }
		$query = $this->db->select('timezone')
                   	   ->where('userId', $userId)
                       ->limit(1)
                   	   ->get($this->tblName);
		$result = $query->row();

        if ($query->num_rows() > 0) {
			return $result->timezone;
		} else {
			$this->errorCode = 1;
			$this->statusMess = "No user matching id pass was found in the system.";
			return false;
		}
	}
	public function getUserLeagueCount($userId = false) {

		if ($userId === false) $userId = $this->userId;

		$teamList = array();
		$this->db->select('id');
		$this->db->from($this->tables['LEAGUES']);
		$this->db->where('commissioner_id', $userId);
		return $this->db->count_all_results();
	}

	public function getUserTeams($league_id = false, $userId = false, $scoring_period_id = false) {

		if ($userId === false) $userId = $this->userId;

		$teamList = array();
		$select = $this->tables['TEAMS'].'.id, teamname, teamnick, fantasy_teams.avatar, fantasy_teams.league_id, league_name, league_type, commissioner_id';
		$this->db->join($this->tables['LEAGUES'],$this->tables['LEAGUES'].'.id = fantasy_teams.league_id', 'left');
		if ($league_id !== false) {
			$this->db->where($this->tables['TEAMS'].'.league_id', $league_id);
		}
        if ($scoring_period_id !== false) {
			$select .= ',w,l,pct,gb,fantasy_teams_scoring.total';
			$this->db->join('fantasy_teams_record','fantasy_teams_record.team_id = fantasy_teams.id', 'left');
			$this->db->join('fantasy_teams_scoring','fantasy_teams_scoring.team_id = fantasy_teams.id', 'left');
			$this->db->where('(fantasy_teams_scoring.scoring_period_id = '.$scoring_period_id.' OR fantasy_teams_record.scoring_period_id = '.$scoring_period_id.")");
		}
		$this->db->select($select);
		$this->db->where('owner_id', $userId);
		$query = $this->db->get($this->tables['TEAMS']);
		//echo($this->db->last_query()."<br />");
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				array_push($teamList,array('id'=>$row->id, 'teamname'=>$row->teamname,'teamnick'=>$row->teamnick,'avatar'=>$row->avatar,
										   'league_id'=>$row->league_id,'league_name'=>$row->league_name,'league_type'=>$row->league_type,
										   'commissioner_id'=>$row->commissioner_id,'w'=>$row->w,'l'=>$row->l,'pct'=>$row->pct,'gb'=>$row->gb,
										   'total'=>$row->total));
			}
		}
		return $teamList;
	}

	public function getUserTeamIds($league_id = false, $userId = false, $scoring_period_id = false) {

		if ($userId === false) $userId = $this->userId;
		$teamIds = array();

		$teams = $this->getUserTeams($league_id,$userId,$scoring_period_id);
		if (sizeof($teams) > 0) {
			foreach($teams as $row) {
				//echo("Team id = ".$row['id']."<br />");
				array_push($teamIds,$row['id']);
			}
		}
		return $teamIds;
	}
	public function getUserLeagueIds($userId = false, $scoring_period_id = false) {

		if ($userId === false) $userId = $this->userId;
		$leagueIds = array();

		$teams = $this->getUserTeams(false,$userId,$scoring_period_id);
		if (sizeof($teams) > 0) {
			foreach($teams as $row) {
				array_push($leagueIds,$row['league_id']);
			}
		}
		return $leagueIds;
	}
	public function getTeamInvites($userId = false) {

		$invites = array();
		if ($userId === false) { $userId = $this->userId; }
		if ($userId == -1) {
			$this->errorCode = 1;
			$this->statusMess = "No user Id was found.";
		} else {
			$userEmail = getEmail($userId);

			$this->db->flush_cache();
			$this->db->select($this->tables['INVITES'].'.id,'.$this->tables['INVITES'].'.league_id, league_name, avatar, username, team_id, confirm_str, confirm_key,send_date ');
			$this->db->join($this->tables['LEAGUES'],$this->tables['LEAGUES'].'.id = '.$this->tables['INVITES'].'.league_id', 'left');
			$this->db->join($this->tables['users_core'],$this->tables['users_core'].'.id = '.$this->tables['INVITES'].'.from_id', 'left');
			$this->db->where('to_email',$userEmail);
			$this->db->where('status_id',REQUEST_STATUS_PENDING);
			$query = $this->db->get($this->tables['INVITES']);

			if ($query->num_rows() == 0) {
				$this->errorCode = 1;
				$this->statusMess = "No team invites were found.";
			} else {
				foreach ($query->result() as $row) {
					array_push($invites, array('id'=>$row->id,'ck'=>md5($row->confirm_str.$row->confirm_key),
											   'league_id'=>$row->league_id,'team_id'=>$row->team_id,
											   'league_name'=>$row->league_name,'avatar'=>$row->avatar,
											   'username'=>$row->username,'send_date '=>$row->send_date));

				}
			}
		}
		return $invites;
	}
	public function getTeamRequests($userId = false) {

		$requests = array();
		if ($userId === false) { $userId = $this->userId; }
		if ($userId == -1) {
			$this->errorCode = 1;
			$this->statusMess = "No user Id was recieved.";
		} else {

			$this->db->flush_cache();
			$this->db->select($this->tables['REQUESTS'].'.id,'.$this->tables['REQUESTS'].'.league_id, date_requested, league_name, '.$this->tables['TEAMS'].'.avatar, team_id, teamname, teamnick');
			$this->db->join($this->tables['LEAGUES'],$this->tables['LEAGUES'].'.id = '.$this->tables['REQUESTS'].'.league_id', 'left');
			$this->db->join($this->tables['TEAMS'],$this->tables['TEAMS'].'.id = '.$this->tables['REQUESTS'].'.team_id', 'left');
			$this->db->where('user_id',$userId);
			$this->db->where('status_id',REQUEST_STATUS_PENDING);
			$query = $this->db->get($this->tables['REQUESTS']);

			if ($query->num_rows() == 0) {
				$this->errorCode = 1;
				$this->statusMess = "No team requests were found.";
			} else {
				foreach ($query->result() as $row) {
					array_push($requests, array('id'=>$row->id,'league_id'=>$row->league_id,'league_name'=>$row->league_name,'team_id'=>$row->team_id,
											   'team'=>$row->teamname.' '.$row->teamnick,
											   'avatar'=>$row->avatar,'date_requested'=>$row->date_requested));

				}
			}
		}
		return $requests;
	}
    /**
     * GET USER TRADE OFFERS.
     * Retrieves a list of all trade offers for this user.
     *
     * @param       bool    $userId
     * @param       bool    $scoring_period_id
     * @param       bool    $team_id
     * @param       bool    $league_id
     * @param       bool    $debug
     * @return      array   List of offers index by ($league_id => $array($offers))
     *
     * @since       1.0.6
     * @access      public
     */
    public function getTradeOffers($userId = false, $scoring_period_id = false, $team_id = false, $league_id = false, $debug = false) {

        $tradeOfferList = array();
        if ($userId === false) { $userId = $this->userId; }
		if ($userId == -1) {
			$this->errorCode = 1;
			$this->statusMess = "No user Id was received.";
		} else {
            $team_list = array();
            if ($team_id !== false) {
                array_push($team_list,$team_id);
            } else {
                $team_list = $this->getUserTeamIds($league_id,$userId,$scoring_period_id);
            }
            if (sizeof($team_list) > 0) {
				$this->db->select($this->tables['TRADES'].'.id, team_1_id, teamname, teamnick, team_2_id, status, 	tradeStatus, '.$this->tables['TRADES'].'.league_id, offer_date, expiration_days');
				$this->db->join($this->tables['TEAMS'],$this->tables['TRADES'].'.team_1_id = '.$this->tables['TEAMS'].'.id','right outer');
				$this->db->join('fantasy_teams_trades_status','fantasy_teams_trades_status.id = '.$this->tables['TRADES'].'.status','right outer');
				$teamListStr = "(";
				foreach ($team_list as $id) {
					if ($teamListStr != "(") { $teamListStr .= ","; }
					$teamListStr .= $id;
				}
				$teamListStr .= ")";
				$this->db->where('team_2_id IN '.$teamListStr);
				$this->db->where('('.$this->tables['TRADES'].'.status = '.TRADE_OFFERED.' OR '.$this->tables['TRADES'].'.status = '.TRADE_PENDING_LEAGUE_APPROVAL.' OR '.$this->tables['TRADES'].'.status = '.TRADE_PENDING_COMMISH_APPROVAL.')');
				if ($scoring_period_id !== false) {
					$this->db->where('in_period',$scoring_period_id+1);
				}
				$this->db->orderBy($this->tables['TRADES'].'.league_id','asc');
				$query = $this->db->get($this->tables['TRADES']);
				if ($debug === true) { print($this->db->last_query()."<br />"); }
				if ($query->num_rows() > 0) {
					$curr_league = -1;
					$offers = array();
					$itemCount = 0;
					foreach($query->result() as $row) {
						if ($curr_league == -1 || ($curr_league != -1 && $curr_league != $row->league_id)) {
							if (sizeof($offers) > 0) { $tradeOfferList = $tradeOfferList + array($curr_league => $offers);  $offers = array(); }
							$curr_league = $row->league_id;
						}
						array_push($offers,array('trade_id'=>$row->id,'team_1_id'=>$row->team_1_id,'team_2_id'=>$row->team_2_id,'teamname'=>$row->teamname,'teamnick'=>$row->teamnick,
													'offer_date'=>$row->offer_date,'status'=>$row->status,'tradeStatus'=>$row->tradeStatus, 'expiration_days'=>$row->expiration_days));
						if ($query->num_rows() == 1) { $tradeOfferList = $tradeOfferList + array($curr_league => $offers);  $offers = array(); }
					} // END foreach
				} // END if
			} // END if
        } // END if
		return $tradeOfferList;
	}
	/**
     * GET LEAGUE TRADES FOR REVIEW.
     * Retrieves a list of all trades awaiting league approval that the user can review.
     *
     * @param       bool    $userId
     * @param       bool    $scoring_period_id
     * @param       bool    $team_id
     * @param       bool    $league_id
     * @param       bool    $debug
     * @return      array   List of offers index by ($league_id => $array($offers))
     *
     * @since       1.0.6
     * @access      public
     */
    public function getTradesForReview($userId = false, $scoring_period_id = false, $team_id = false, $league_id = false, $debug = false) {
		$tradeReviewList = array();
		if ($userId === false) { $userId = $this->userId; }
		if ($userId == -1) {
			$this->errorCode = 1;
			$this->statusMess = "No user Id was received.";
		} else {
            $team_list = array();
			$league_list = array();
            if ($league_id !== false) {
                array_push($league_list,$league_id);
            } else {
                $league_list = $this->getUserLeagueIds($userId);
            }
			$team_list = array();
            if ($team_id !== false) {
                array_push($team_list,$team_id);
            } else {
                $team_list = $this->getUserTeamIds(false,$userId,$scoring_period_id);
			}
			if (sizeof($league_list) > 0) {
				$this->db->select($this->tables['TRADES'].'.id, team_1_id, teamname, teamnick, team_2_id, status, tradeStatus, '.$this->tables['TRADES'].'.league_id, offer_date, response_date, expiration_days');
				$this->db->join($this->tables['TEAMS'],$this->tables['TRADES'].'.team_1_id = '.$this->tables['TEAMS'].'.id','right outer');
				$this->db->join('fantasy_teams_trades_status','fantasy_teams_trades_status.id = '.$this->tables['TRADES'].'.status','right outer');
				$leagueListStr = "(";
				foreach ($league_list as $id) {
					if ($leagueListStr != "(") { $leagueListStr .= ","; }
					$leagueListStr .= $id;
				}
				$leagueListStr .= ")";
				$this->db->where($this->tables['TRADES'].'.league_id IN '.$leagueListStr);
				$teamListStr = "(";
				foreach ($team_list as $id) {
					if ($teamListStr != "(") { $teamListStr .= ","; }
					$teamListStr .= $id;
				}
				$teamListStr .= ")";
				$this->db->where('team_1_id NOT IN '.$teamListStr);
				$this->db->where('team_2_id NOT IN '.$teamListStr);
				$this->db->where($this->tables['TRADES'].'.status',TRADE_PENDING_LEAGUE_APPROVAL);
				if ($scoring_period_id !== false) {
					$this->db->where('in_period',$scoring_period_id+1);
				}
				$this->db->orderBy($this->tables['TRADES'].'.league_id','asc');
				$query = $this->db->get($this->tables['TRADES']);
				//echo($this->db->last_query()."<br />");
				if (!function_exists('getTeamName')) {
					$this->load->helper('roster');
				}
				
				if ($debug === true) { print($this->db->last_query()."<br />"); }
				if ($query->num_rows() > 0) {
					$curr_league = -1;
					$trades = array();
					$itemCount = 0;
					foreach($query->result() as $row) {
						if ($curr_league == -1 || ($curr_league != -1 && $curr_league != $row->league_id)) {
							if (sizeof($trades) > 0) { $tradeReviewList = $tradeReviewList + array($curr_league => $trades);  $trades = array(); }
							$curr_league = $row->league_id;
						}
						array_push($trades,array('trade_id'=>$row->id,'team_1_id'=>$row->team_1_id,'team_2_id'=>$row->team_2_id,'team_1_name'=>getTeamName($row->team_1_id),'team_2_name'=>getTeamName($row->team_2_id),
													'offer_date'=>$row->offer_date,'response_date'=>$row->response_date,'status'=>$row->status,'tradeStatus'=>$row->tradeStatus, 'expiration_days'=>$row->expiration_days));
						if ($query->num_rows() == 1) { $tradeReviewList = $tradeReviewList + array($curr_league => $trades);  $trades = array(); }
					} // END foreach
				} // END if
			} // END if
        } // END if
		return $tradeReviewList;
		
	}
	
	public function getUserDrafts() {

		$draftList = array();
		$this->db->select('league_id');
		$this->db->where('owner_id', $this->userId);
		$query = $this->db->get($this->tables['TEAMS']);
		$leagues = array();
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				// GET ACTIVE DRAFT STATUS
				$details = array();
				$completed = false;
				$this->db->select('draftDate, completed');
				$this->db->where('league_id', $row->league_id);
				$query2 = $this->db->get('fantasy_draft_config');
				if ($query2->num_rows() > 0) {
					$row2 = $query2->row();
					//$today = strtotime(date('Y-m-d'));
					//$started = ($today >= strtotime($row2->draftDate));
					$completed = ($row2->completed == 1) ? true : false;
				}
				$query2->free_result();
				if ($completed) { continue;}
				else {
					// GET CURRENT PICK
					$this->db->select($this->tables['DRAFT'].'.*, teamname, teamnick');
					$this->db->join($this->tables['TEAMS'],$this->tables['TEAMS'].'.id = '.$this->tables['DRAFT'].'.team_id','left');
					$this->db->where($this->tables['DRAFT'].'.league_id', $row->league_id);
					$this->db->order_by('pick_overall');
					$query3 = $this->db->get($this->tables['DRAFT']);
					if ($query3->num_rows() > 0) {
						foreach($query3->result_array() as $drow) {
							if (!isset($drow['player_id']) || empty($drow['player_id'])) {
								$details = $drow;
								break;
							}
						}
					}
					$query3->free_result();
				}
				if(sizeof($details) > 0) {
					$draftList = $draftList + array($row->league_id => $details);
				}
			}
		}
		return $draftList;
	}

}
