<?php
/**
 *	LEAGUE MODEL CLASS.
 *
 *	The League Model is the powerhouse of the Fantasy process. It manages many of the
 *  admin functionality and provides tools and methods to run the league.
 *	@author			Jeff Fox (Github ID: jfox015)
 *	@version		1.0.4
 *  @lastModified	04/17/20
 *
*/
require_once('./application/libraries/CompiledStats.php');
class league_model extends base_model {

	/*--------------------------------
	/	VARIABLES
	/-------------------------------*/
	/**
	 *	SLUG.
	 *	@var $_NAME:String
	 */
	var $_NAME = 'league_model';
	/**
	 *	LEAGUE NAME.
	 *	@var $league_name:String
	 */
	var $league_name  = '';
	/**
	 *	LEAGUE DESCRIPTION.
	 *	@var $description:String
	 */
	var $description  = '';
	/**
	 *	LEAGUE SCORING TYPE.
	 *	@var $league_type:Int
	 */
	var $league_type  = -1;
	/**
	 *	# OF GANES PLAYED PER TEAM PER WEEK (Head-to-Head Scoring).
	 *	@var $games_per_team:Int
	 */
	var $games_per_team = 0;
	/**
	 *	# OF GANES PLAYED PER TEAM PER WEEK (Head-to-Head Scoring).
	 *	@var $access_type:Int
	 */
	var $access_type = -1;
	/**
	 *	AVATAR.
	 *	@var $avatar:String
	 */
	var $avatar = '';
	/**
	 *	LEAGUE STATUS.
	 *	@var $league_status:Int
	 */
	var $league_status = 1;
	/**
	 *	COMMISSIONER ID.
	 *	@var $commissioner_id:Int
	 */
	var $commissioner_id = -1;
	/**
	 *	# OF REGULAR (NON-PLAYOFF) SCORING PERIODS.
	 *	@var $regular_scoring_periods:Int
	 */
	var $regular_scoring_periods = 0;
	/**
	 *	# OF PLAYOFF ROUNDS.
	 *	@var $playoff_rounds:Int
	 */
	var $playoff_rounds = 0;
	/**
	 *	MAX # OF TEAMS FOR LEAGUE.
	 *	@var $max_teams:Int
	 */
	var $max_teams = 0;
	/**
	 *	ACCEPT TEAM REQUEST.
	 *	@var $accept_requests:Int
	 */
	var $accept_requests = 0;
	/**
	 *	COMPILED STATS.
	 *	@var $compiledStats:Array
	 */
	var $compiledStats = array();
	/**
	 *	STAT FIELD LIST.
	 *	@var $columnList:Array
	 */
	var $columnList = array();
	/**
	 *	SQL COLUMN NAMES.
	 *	@var $sqlColNames:Array
	 */
	var $sqlColNames = array();

	/*---------------------------------------------
	/
	/	C'TOR
	/	Creates a new instance of league_model
	/
	/---------------------------------------------*/
	function league_model() {
		parent::__construct();

		$this->tblName = 'fantasy_leagues';
		$this->tables['GAMES'] = 'fantasy_leagues_games';
		$this->tables['TRANSACTIONS'] = 'fantasy_transactions';
		$this->tables['WAIVERS'] = 'fantasy_players_waivers';
		$this->tables['WAIVER_CLAIMS'] = 'fantasy_teams_waiver_claims';
		$this->tables['TEAMS'] = 'fantasy_teams';
		$this->tables['ROSTERS'] = 'fantasy_rosters';
		$this->tables['TEAMS_RECORD'] = 'fantasy_teams_record';
		$this->tables['TEAMS_SCORING'] = 'fantasy_teams_scoring';
		$this->tables['ROSTER_RULES'] = 'fantasy_roster_rules';
		$this->tables['SCORING_RULES_BATTING'] = 'fantasy_leagues_scoring_batting';
		$this->tables['SCORING_RULES_PITCHING'] = 'fantasy_leagues_scoring_pitching';
		$this->tables['TEAM_INVITES'] = 'fantasy_invites';
		$this->tables['TEAM_REQUESTS'] = 'fantasy_leagues_requests';
		$this->tables['TEAM_TRADES'] = 'fantasy_teams_trades';
		$this->tables['TRADES'] = 'fantasy_teams_trades';
		$this->tables['TRADES_STATUS'] = 'fantasy_teams_trades_status';
		$this->tables['PLAYERS'] = 'fantasy_players';

		$this->fieldList = array('league_name','description','league_type','games_per_team','access_type','league_status','regular_scoring_periods','max_teams','playoff_rounds','accept_requests');
		$this->conditionList = array('avatarFile','new_commisioner');
		$this->readOnlyList = array('avatar','commissioner_id');
		$this->textList = array('description');

		$this->columns_select = array('id','league_type','description','league_name','max_teams','access_type','avatar','commissioner_id');

		$this->addSearchFilter('league_type','Scoring Type','leagueType','leagueType');
		$this->addSearchFilter('access_type','Public/Private','accessType','accessType');

		parent::_init();
	}
	/*--------------------------------------------------
	/
	/	PUBLIC FUNCTIONS
	/
	/-------------------------------------------------*/
	/**
	 * 	APPLY DATA.
	 *
	 *	Applies custom data values to the object.
	 *
	 * 	@return 	TRUE on success, FALSE on failure
	 *
	 */
	public function applyData($input,$userId = -1) {
		$success = parent::applyData($input,$userId);
		if ($success) {
			if ($input->post('new_commisioner')) {
				if ($this->ownerCanBeCommish($input->post('new_commisioner'))) {
					$this->commissioner_id = $input->post('new_commisioner');
				}
			}
			if (isset($_FILES['avatarFile']['name']) && !empty($_FILES['avatarFile']['name'])) {
				$success = $this->uploadFile('avatar',PATH_LEAGUES_AVATAR_WRITE,$input,'avatar',$this->league_name);
			}
		}
		return $success;
	}
	/**
	* 	DELETE ROSTERS.
	* 	<p>
	* 	Deletes all rosters for the specified league_id. If no id is passed, the current league id of the loaded bbject is used.
	*	</p>
	*	<p><b>NOTE:</b> To delete rosters for a given team, use the team_model->deleteRosters function instead.
	*	</p>
	* 	@param	$league_id		{int}	The League Id
	* 	@return					{Boolean}	TRUE on success
	*
	* 	@since	1.0.6
	*  	@access	public
	*  	@see	application -> models -> team_model -> deleteRosters
	*/
	public function deleteRosters($league_id = false, $scoring_period_id = false) {

		if ($league_id === false) { $league_id = $this->id; }

		$this->db->where('league_id',$league_id);
		if ($scoring_period_id !== false) {
			$this->db->where('scoring_period_id',$scoring_period_id);
		}
		$this->db->delete($this->tables['ROSTERS']);

		return true;
	}
	/**
	* 	DELETE TEAM RECORDS.
	* 	<p>
	* 	Deletes all records for the specified league_id. If no id is passed, the current league id of the loaded bbject is used.
	*	</p>
	*	<p><b>NOTE:</b> To records trades for a given team, use the team_model->deleteRecords function instead.
	*	</p>
	* 	@param	$league_id		{int}	The League Id
	* 	@return					{Boolean}	TRUE on success
	*
	* 	@since	1.0.6
	*  	@access	public
	*  	@see	application -> models -> team_model -> deleteRecords
	*/
	public function deleteRecords($league_id = false) {

		return $this->deleteLeagueData($this->tables['TEAMS_RECORD'],$league_id);

	}
	/**
	* 	DELETE SCHEDULE.
	* 	<p>
	* 	Deletes all schedules for the specified league_id. If no id is passed, the current league id of the loaded object is used.
	*	</p>
	*	<p><b>NOTE:</b> Because scheduling involves more than one team per game, there is no equivilent function for removing scheduled
	*	games for individual teams in the team_model.
	*	</p>
	* 	@param	$league_id		{int}	The League Id
	* 	@return					{Boolean}	TRUE on success
	*
	* 	@since	1.0.6
	*  	@access	public
	*/
	public function deleteSchedule($league_id = false) {

		return $this->deleteLeagueData($this->tables['GAMES'],$league_id);

	}
	/**
	* 	DELETE TEAM SCORING.
	* 	<p>
	* 	Deletes all scoring for the specified league_id. If no id is passed, the current league id of the loaded bbject is used.
	*	</p>
	*	<p><b>NOTE:</b> To delete scoring for a given team, use the team_model->deleteScoring function instead.
	*	</p>
	* 	@param	$league_id		{int}	The League Id
	* 	@return					{Boolean}	TRUE on success
	*
	* 	@since	1.0.6
	*  	@access	public
	*  	@see	application -> models -> team_model -> deleteRecords
	*/
	public function deleteScoring($league_id = false) {

		return $this->deleteLeagueData($this->tables['TEAMS_SCORING'],$league_id);

	}

	/**
	* 	DELETE TRADES.
	* 	<p>
	* 	Deletes all trades for the specified league_id. If no id is passed, the current league id of the loaded bbject is used.
	*	</p>
	*	<p><b>NOTE:</b> To delete trades for a given team, use the team_model->deleteTrades function instead.
	*	</p>
	* 	@param	$league_id		{int}	The League Id
	* 	@return					{Boolean}	TRUE on success
	*
	* 	@since	1.0.6
	*  	@access	public
	*  	@see	application -> models -> team_model -> deleteTrades
	*/
	public function deleteTrades($league_id = false) {

		return $this->deleteLeagueData($this->tables['TEAM_TRADES'],$league_id);

	}

	/**
	* 	DELETE TRANSACTIONS.
	* 	<p>
	* 	Deletes all transactions for the specified league_id. If no id is passed, the current league id of the loaded bbject is used.
	*	</p>
	*	<p><b>NOTE:</b> To delete transactions for a given team, use the team_model->deleteTransactions function instead.
	*	</p>
	* 	@param	$league_id		{int}	The League Id
	* 	@return					{Boolean}	TRUE on success
	*
	* 	@since	1.0.6
	*  	@access	public
	*  	@see	application -> models -> team_model -> deleteTransactions
	*/
	public function deleteTransactions($league_id = false) {

		return $this->deleteLeagueData($this->tables['TRANSACTIONS'],$league_id);

	}
	/**
	* 	DELETE WAIVER CLAIMS.
	* 	<p>
	* 	Deletes all waiver claims for the specified league_id. If no id is passed, the current league id of the loaded bbject is used.
	*	</p>
	*	<p><b>NOTE:</b> To delete waiver claims for a given team, use the team_model->deleteWaiverClaims function instead.
	*	</p>
	* 	@param	$league_id		{int}	The League Id
	* 	@return					{Boolean}	TRUE on success
	*
	* 	@since	1.0.6
	*  	@access	public
	*  	@see	application -> models -> team_model -> deleteWaiverClaims
	*/
	public function deleteWaiverClaims($league_id = false) {

		return $this->deleteLeagueData($this->tables['WAIVER_CLAIMS'],$league_id);

	}

	// SPECIAL QUERIES
	/**
	* 	GET LEAGUE NAME.
	* 	<p>
	* 	Return the name of the league specified (or leaded).</p>
	* 	@param	$league_id		{int}		The League Id
	* 	@return					{String}	League Name
	*
	* 	@since	1.0
	*  	@access	public
	*/
	public function getLeagueName($league_id = false) {

		if ($league_id === false) { $league_id = $this->id; }
		$this->db->select('league_name');
		$this->db->from($this->tblName);
		$this->db->where("id",$league_id);
		$league_name = '';
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$row = $query->row();
			$league_name = $row->league_name;
		}
		$query->free_result();
		return $league_name;
	}
	/**
	* 	HAS TEAMS.
	* 	<p>Return true if the league contains teams and FALSE if not.</p>
	* 	@param	$league_id		{int}		The League Id
	* 	@return					{Boolean}	TRUE if teams exist, FALSE if no
	*
	* 	@since	1.0
	*  	@access	public
	*/
	public function hasTeams($league_id = false) {
		if ($league_id === false) { $league_id = $this->id; }
		$this->db->select('id');
		$this->db->from($this->tables['TEAMS']);
		$this->db->where("league_id",$league_id);
		$count = $this->db->count_all_results();
		//echo("League ".$league_id." team count = ".$count."<br />");
		if ($count != 0) {
			return true;
		} else {
			return false;
		}
	}
	/**
	* 	HAS VALID ROSTERS.
	* 	<p>Simple test if teams in a legaue have players on rosters. it does not, however,
    *	test if they are valid against a league roster rules.</p>
	* 	@param	$league_id		{int}		The League Id
	* 	@return					{Boolean}	TRUE if teams have rosters, FALSE if no
	*
	* 	@since	1.0
	*  	@access	public
	*/
	public function hasValidRosters($league_id = false) {

		// VALIDATE ROSTER COUNTS
		if ($league_id === false) { $league_id = $this->id; }
		$this->db->select('fantasy_rosters.player_id');
		$this->db->from($this->tables['TEAMS']);
		$this->db->join('fantasy_rosters','fantasy_rosters.team_id = fantasy_teams.id','left');
		$this->db->where("fantasy_teams.league_id",$league_id);
		$count = $this->db->count_all_results();
		if ($count != 0) {
			return true;
		} else {
			return false;
		}
	}
	/**
	* 	GET SCORING TYPE.
	* 	<p>Returns the leagues scoring type.</p>
	* 	@param	$league_id		{int}		The League Id
	* 	@return					{int}		Scoring type ID
	*
	* 	@since	1.0.5
	*  	@access	public
	*/
	public function getScoringType($league_id = false) {
		if ($league_id === false && $this->id != -1) {
			return $this->league_type;
		} else {
			$type = -1;
			$this->db->select('league_type');
			$this->db->from($this->tblName);
			$this->db->where("id",$league_id);
			$query = $this->db->get();
			if ($query->num_rows() > 0) {
				$row = $query->row();
				$type = $row->league_type;
			}
			$query->free_result();
			return $type;
		}
	}
	/**
	* 	OWNER CAN BE COMMISSIONER.
	* 	<p>Tests if the passed user ID can be commissioner of the specified (or leaded) league (I.E. if they are already
	*	commissioner or not).</p>
	* 	@param	$userId			{int}			The user Id
	* 	@param	$league_id		{int}			OPTIONAL - The League Id
	* 	@return					{Boolean}		TRUE if user can be commissioner, FALSE if not
	*
	* 	@since	1.0
	*  	@access	public
	*/
	protected function ownerCanBeCommish($userId = false, $league_id = false) {

		if ($userId === false) { return false; }
		if ($league_id === false) { $league_id = $this->id; }

		if ($userId != $this->commissioner_id && !$this->userIsCommish($userId)) {
			return true;
		} else {
			return false;
		}
	}
	/**
	* 	USER IS COMMISSIONER.
	* 	<p>Tests f the passed user ID is the leagues commissioner.</p>
	* 	@param	$userId			{int}			The user Id
	* 	@param	$league_id		{int}			OPTIONAL - The League Id
	* 	@return					{Boolean}		TRUE if user is commissioner, FALSE if not
	*
	* 	@since	1.0
	*  	@access	public
	*/
	public function userIsCommish($userId = false, $league_id = false) {

		if ($userId === false) { return false; }

		$this->db->select('id');
		$this->db->from($this->tblName);
		$this->db->where("commissioner_id",$userId);
		if ($league_id !== false) {
			$this->db->where("id",$league_id);
		}
		$count = $this->db->count_all_results();
		if ($count != 0) {
			return true;
		} else {
			return false;
		}
	}
	/**
	* 	GET COMMISSIONER ID.
	* 	<p>Returns a leagues commissioenr ID.</p>
	* 	@param	$league_id		{int}		The League Id
	* 	@return					{int}		Commissioenr ID, -1 if not found
	*
	* 	@since	1.0.6
	*  	@access	public
	*/
	public function getCommissionerId($userId = false, $league_id = false) {

		if ($league_id === false) { $league_id = $this->id; }

		$commishId = -1;

		$this->db->select('commissioner_id');
		$this->db->where("id",$league_id);
		$query = $this->db->get($this->tblName);
		if ($query->num_rows() > 0) {
			$row = $query->row();
			$commishId = $row->commissioner_id;
		}
		$query->free_result();
		return $commishId;
	}
	/**
	* 	GET OWNER IDS.
	* 	<p>Returns a list of user ids who own teams in the passed (or loaded) league.</p>
	* 	@param	$league_id		{int}			OPTIONAL - The League Id
	* 	@return					{Boolean}		TRUE if user is commissioner, FALSE if not
	*
	* 	@since	1.0
	*  	@access	public
	*/
	public function getOwnerIds($league_id = false) {

		$owners = array();
		if ($league_id === false) { $league_id = $this->id; }

		$this->db->select('owner_id');
		$this->db->from($this->tables['TEAMS']);
		$this->db->where("league_id",$league_id);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				array_push($owners,$row->owner_id);
			}
		}
		$query->free_result();
		return $owners;
	}
	/**
	 * 	GET OWNER INFO.
	 * 	<p>Returns an array of owner details including the owners id, first and last name.</p>
	 *	<p><b>NOTE:</b> The <code>$showTeam</code> arg can be used to return the team name instead of owner name. The owner ID is
	 *	returned regardless.</p>
	 * 	@param	$league_id		{int}			OPTIONAL - The League Id
	 * 	@param	$showTeam		{Boolean}		TRUE ot show the team name, FALSE to show owner name
	 * 	@return					{Boolean}		TRUE if user is commissioner, FALSE if not
	 *
	 * 	@since	1.0
	 *  @access	public
	 */
	public function getOwnerInfo($league_id = false, $showTeam = false) {

		$owners = array();
		if ($league_id === false) { $league_id = $this->id; }
		$this->db->select('fantasy_teams.id, teamname, teamnick, firstName, lastName');
		$this->db->from($this->tables['TEAMS']);
		$this->db->join('users_meta','users_meta.userId = fantasy_teams.owner_id');
		$this->db->where("league_id",$league_id);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				$ownerName = $row->firstName." ".$row->lastName;
				if ($showTeam) {
					$ownerName .= " - ".$row->teamname." ".$row->teamnick;
				}
				$owners = $owners + array($row->id=>$ownerName);
			}
		}
		$query->free_result();
		return $owners;
	}
	public function getDetailedOwnerInfo($league_id = false, $showTeam = false) {

		$owners = array();
		if ($league_id === false) { $league_id = $this->id; }
		$this->db->select('fantasy_teams.id as team_id, teamname, teamnick, users_core.id as owner_id, email, username');
		$this->db->from($this->tables['TEAMS']);
		$this->db->join('users_core','users_core.id = fantasy_teams.owner_id');
		$this->db->where("league_id",$league_id);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {

				$owners = $owners + array($row->team_id=>array('owner_id'=>$row->owner_id,'email'=>$row->email, 'username'=>$row->username,
                                                               'teamname'=>$row->teamname,'teamnick'=>$row->teamnick));
			}
		}
		$query->free_result();
		return $owners;
	}
	public function userHasAccess($user_id = false, $league_id = false) {

		$access = false;
		if ($user_id === false || $user_id == -1) { return false; }
		if ($league_id === false) { $league_id = $this->id; }

		//print "user id = ".$user_id."<ber />";
		$ownerIds = $this->getOwnerIds($league_id);
		//print "has access? = ".(in_array($user_id,$ownerIds) ? "yes":"no")."<ber />";
		$access = (sizeof($ownerIds) > 0 && in_array($user_id,$ownerIds));
		return $access;

	}

	/*----------------------------------------------------------------------
	/
	/	INVITES AND REQUESTS
	/
	/----------------------------------------------------------------------*/


	public function getLeagueInvites($onlyPending = false, $league_id = false) {

		$invites = array();
		if ($league_id === false) { $league_id = $this->id; }

		$this->db->select('to_email, send_date, team_id, teamname, teamnick, requestStatus');
		$this->db->from($this->tables['TEAM_INVITES']);
		$this->db->join('fantasy_teams','fantasy_teams.id = fantasy_invites.team_id','right outer');
		$this->db->join('fantasy_leagues_requests_status','fantasy_leagues_requests_status.id = fantasy_invites.status_id','right outer');
		$this->db->where("fantasy_invites.league_id",$league_id);
		if ($onlyPending !== false) {
			$this->db->where('status_id', REQUEST_STATUS_PENDING);
		}
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				array_push($invites,array('to_email'=>$row->to_email, 'send_date'=>$row->send_date,
										  'team_id'=>$row->team_id,'team'=>$row->teamname." ".$row->teamnick));
			}
		}
		$query->free_result();
		return $invites;
	}
	public function getLeagueRequests($onlyPending = false, $league_id = false, $request_id = false) {

		$requests = array();
		if ($league_id === false) { $league_id = $this->id; }

		$this->db->select($this->tables['TEAM_REQUESTS'].'.id, user_id, username, date_requested, team_id, teamname, teamnick, requestStatus');
		$this->db->from($this->tables['TEAM_REQUESTS']);
		$this->db->join('users_core','users_core.id = fantasy_leagues_requests.user_id','right outer');
		$this->db->join('fantasy_teams','fantasy_teams.id = fantasy_leagues_requests.team_id','right outer');
		$this->db->join('fantasy_leagues_requests_status','fantasy_leagues_requests_status.id = fantasy_leagues_requests.status_id','right outer');
		$this->db->where($this->tables['TEAM_REQUESTS'].'.league_id',$league_id);
		if ($request_id !== false) {
			$this->db->where($this->tables['TEAM_REQUESTS'].'.id',$request_id);
		}
		if ($onlyPending !== false) {
			$this->db->where('status_id', REQUEST_STATUS_PENDING);
		}
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				array_push($requests,array('id'=>$row->id,'user_id'=>$row->user_id, 'username'=>$row->username,'date_requested'=>$row->date_requested,
										  'team_id'=>$row->team_id,'team'=>$row->teamname." ".$row->teamnick));
			}
		}
		$query->free_result();
		return $requests;
	}
	public function teamRequest($team_id = false, $user_id = false, $league_id = false) {
		if ($league_id === false) {
			$league_id = $this->id;
		}
		if ($team_id === false || $user_id === false) {
			return false;
		}

		$this->lang->load('league');
		$this->db->select('id, status_id');
		//$this->db->where('team_id',$team_id);
		$this->db->where('user_id',$user_id);
		$this->db->where('league_id',$league_id);
		$this->db->where('(status_id = '.REQUEST_STATUS_PENDING.' OR status_id = '.REQUEST_STATUS_ACCEPTED.' OR status_id = '.REQUEST_STATUS_DENIED.')');
		$query = $this->db->get($this->tables['TEAM_REQUESTS']);
		if ($query->num_rows() > 0) {
			$row = $query->row();
			$this->errorCode = 2;
			switch($row->status_id) {
				case REQUEST_STATUS_PENDING:
					$mess = $this->lang->line('league_request_status_pending');
					break;
				case REQUEST_STATUS_ACCEPTED:
					$mess = $this->lang->line('league_request_status_accepted');
					break;
				case REQUEST_STATUS_DENIED:
					$mess = $this->lang->line('league_request_status_denied');
					break;
			}
			$this->statusMess = $mess;
			return false;
		}
		$requestData = array('team_id'=>$team_id,'user_id'=>$user_id,'league_id'=>$league_id);
		$this->db->insert('fantasy_leagues_requests',$requestData);
		if ($this->db->affected_rows() == 0) {
			$this->errorCode = 1;
			$this->statusMess = 'The request data was not saved to the database.';
			return false;
		}
		return true;
	}

	public function updateRequest($request_id = false, $response = false, $league_id = false) {

		if ($league_id === false) {
			$league_id = $this->id;
		}
		if ($request_id === false || $response === false) {
		return false;
		}

		$this->db->select('*');
		$this->db->where('id',$request_id);
		$query = $this->db->get($this->tables['TEAM_REQUESTS']);

		if ($query->num_rows() == 0) {
			$this->errorCode = 1;
			$this->statusMess = 'No request matching the passed ID was found in the system.';
			return false;
		} else {
			$row = $query->row();
			$cleanDb = true;
			$newStatus = 0;;
			switch($response) {
				case 1:
					$data = array('owner_id'=>$row->user_id);
					$this->db->where('id',$row->team_id);
					$this->db->update($this->tables['TEAMS'],$data);
					if ($this->db->affected_rows() == 0) {
						$this->errorCode = 2;
						$this->statusMess = 'The team owner update could not be saved to the database.';
						return false;
					}
					$newStatus = REQUEST_STATUS_ACCEPTED;
					break;
				case 2:
					$newStatus = REQUEST_STATUS_WITHDRAWN;
					break;
				case -1:
					$newStatus = REQUEST_STATUS_DENIED;
					break;
				case 3:
					$newStatus = REQUEST_STATUS_REMOVED;
					break;
				default:
					$newStatus = REQUEST_STATUS_UNKNOWN;
				break;
			} // END switch
			$this->db->flush_cache();
			$this->db->where('id',$request_id);
			$this->db->update('fantasy_leagues_requests',array('status_id'=>$newStatus));
			if ($this->db->affected_rows() == 0) {
				$this->errorCode = 3;
				$this->statusMess = 'The update could not be saved at this time.';
				return false;
			} // END if
		} // END if
		return true;
	}
	/**
	 * 	DELETE TEAM REQUESTS.
	 * 	This function clear the team request queue for a given league. It can be filtered down to an individual team or
	 * 	user as well.
	 *
	 * 	@param	$league_id		(int)	The league identifier
	 * 	@param	$user_id		(int)	OPTIONAL user identifier
	 * 	@param	$team_id		(int)	OPTIONAL Team identifier
	 * 	@return					(int)	Affected Row count
	 *
	 * 	@since	1.0.6
	 * 	@see	controllers->league->clearRequestQueue()
	 */
	public function deleteTeamRequests($league_id = false, $user_id = false, $team_id = false) {
		if ($league_id === false) { $league_id = $this->id; }

		$this->db->where("league_id",$league_id);
		if ($user_id !== false) {
			$this->db->where("user_id",$user_id);
		}
		if ($team_id !== false) {
			$this->db->where("team_id",$team_id);
		}
		$this->db->delete($this->tables['TEAM_REQUESTS']);
		return $this->db->affected_rows();
	}
	/**
	* 	DELETE TEAM INVITES.
	* 	This function clear the team invitiations for a given league. It can be filtered down to an individual team or
	* 	user as well.
	*
	* 	@param	$league_id		(int)	The league identifier
	* 	@param	$user_id		(int)	OPTIONAL user identifier
	* 	@param	$team_id		(int)	OPTIONAL Team identifier
	* 	@return					(int)	Affected Row count
	*
	* 	@since	1.0.6
	* 	@see	controllers->league->clearRequestQueue()
	*/
	public function deleteTeamInvites($league_id = false, $user_id = false, $team_id = false) {
		if ($league_id === false) { $league_id = $this->id; }
		$userMail = '';
		if ($user_id !== false) {
			$userMail = getEmail($user_id);
		}

		$this->db->flush_cache();
		$this->db->where("league_id",$league_id);

		if (!empty($userMail)) {
			$this->db->where("to_email",$userMail);
		}
		if ($team_id !== false) {
			$this->db->where("team_id",$team_id);
		}
		$this->db->delete($this->tables['TEAM_INVITES']);
		return true;
	}

	/**
	 *	GET LEAGUES.
	 *	Returns a list of public leagues.
	 *  @param	$league_id - If not specified, no league filter is applied.
	 *	@param	$type - 1 = Public, -1 = all (admin only)
	 *	@return	array of league information
	 */
	public function getLeagues($league_id = false, $type=1) {
		$leagues = array();
		$this->db->select($this->tblName.'.id, league_name, description, avatar, shortDesc, commissioner_id, access_type, league_type, leagueType');
		$this->db->join('fantasy_leagues_types','fantasy_leagues_types.id = '.$this->tblName.'.league_type','left');
		if ($type != -1) $this->db->where('access_type',1);
		$query = $this->db->get($this->tblName);
		//echo("getLeagues, query->num_rows = ".$query->num_rows()."<br />");
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$commish = resolveUsername($row->commissioner_id);
				$leagues = $leagues + array($row->id=>array('league_name'=>$row->league_name,'avatar'=>$row->avatar,
															'commissioner_id'=>$row->commissioner_id,'commissioner'=>$commish,
															'league_type_desc'=>$row->shortDesc,'league_type_lbl'=>$row->leagueType,'league_type'=>$row->league_type,'description'=>$row->description,
															'access_type'=>$row->access_type));
			}
		}
		$query->free_result();
		//echo($this->db->last_query()."<br />");
		return $leagues;
	}
	protected function loadLeagueTeams($league_id = false) {
		if ($league_id === false) { $league_id = $this->id; }
		$teamNames = array();
		$this->db->select("id, teamname, teamnick");
		$this->db->where("league_id",$league_id);
		$query = $this->db->get($this->tables['TEAMS']);
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				$teamNames[$row->id] = $row->teamname." ".$row->teamnick;
			}
		}
		$query->free_result();
		return $teamNames;
	}
	public function loadGameData($game_id = false, $team_model, $excludeList = array(), $league_id = false, $debug = false) {

		if ($league_id === false) { $league_id = $this->id; }
		if ($game_id === false) return false;
		// FIRST GET THE TEAMS INVOLVED
		$teams = array();
		$scoring_period = 0;
		$this->db->select('home_team_id, away_team_id, scoring_period_id');
		$this->db->where('id',$game_id);
		$query = $this->db->get('fantasy_leagues_games');
		if ($query->num_rows() > 0) {
			$row = $query->row();
			$teams = array('home'=>$row->home_team_id, 'away'=>$row->away_team_id);
			$scoring_period = $row->scoring_period_id;
		}
		$query->free_result();

		// LOAD RELEVANT SCORING CATEGORIES
		$scoring_rules = $this->getScoringRules($this->id);

		// NOW GET EACH TEAMS ROSTERS
		$rosters = array('home'=>array(),'away'=>array());
		foreach ($teams as $key => $team_id) {
			$statsCompiled = array(1=>array(), -1=>array());
			// GET ACTIVE BATTERS
			if ($team_model->load($team_id)) {
				$team_data = array('id'=>$team_id,'team_name'=>$team_model->teamname." ".$team_model->teamnick,
								   'players_active'=>array(),'players_reserve'=>array(),'stats_active'=>'','stats_reserve'=>'');
				$statuses = array(1, -1,2);
				foreach ($statuses as $status) {
					$player_list = array();
					$players = $team_model->getBatters($scoring_period, false,$status) + $team_model->getPitchers($scoring_period, false,$status);
					foreach ($players as $player_id => $player_data) {
						//echo("PLayer position = ".$player_data['player_position']."<br />");
						if ($player_data['player_position'] != 1) {
							$type = "batting";
							$pos = $player_data['player_position'] ;
						} else {
							$type = "pitching";
							$pos = $player_data['player_role'];
						}
						// GET PLAYER DATA
						$select = "";
						foreach($scoring_rules[$type] as $cat => $val) {
							if ($select != '') { $select.=","; } // END if
							$select .= strtolower(get_ll_cat($cat, true));
						}
						// SUBQUERY FOR FANTASY TOTALS
						$select .= ",(SELECT total FROM fantasy_players_scoring WHERE player_id = ".intval($player_data['id'])." AND
									league_id = ".intval($league_id)." AND scoring_period_id = ".intval($scoring_period)." AND
									scoring_type = ".$scoring_rules['scoring_type'].") AS total ";

						// GET ALL PLAYERS SCORING FOR TEAMS ROSTER
						$player_stats = array();
						$this->db->flush_cache();
						$this->db->select($select);
						$this->db->where("player_id",intval($player_data['id']));
						$this->db->where("scoring_period_id",intval($scoring_period));
						$query = $this->db->get("fantasy_players_compiled_".$type);
						//echo($this->db->last_query()."<br />");
						if ($query->num_rows() > 0) {
							$player_stats = $query->row();
						} // END if
						$query->free_result();
						$pRow = false;
						$stats = "";
						$total = 0;
						if (sizeof($excludeList) == 0 || (sizeof($excludeList) > 0 && !in_array($team_id,$excludeList))) {
							if (sizeof($player_stats) > 0) {
								//$pRow = $player_stats->row();
								$colCount = 0;
								foreach($scoring_rules[$type] as $cat => $val) {
									$colName = strtolower(get_ll_cat($cat, true));
									if ($player_stats->$colName != 0) {
										if (!empty($stats)) $stats .= ", ";
										$stats .= $player_stats->$colName." ".strtoupper(get_ll_cat($cat));
										//if (($colCount+1) != sizeof($scoring_rules[$type])) { $stats.=", "; }
										$useStatus = $status;
										if ($status == 2) {
											$useStatus = -1;
										}
										if (isset($statsCompiled[$useStatus][$cat])) {
											$statsCompiled[$useStatus][$cat] += $player_stats->$colName;
										} else {
											$statsCompiled[$useStatus][$cat] = $player_stats->$colName;
										}
									}
									$colCount++;
								}
								$total = $player_stats->total;
							}
							//$pQuery->free_result();
						}
						$player_list = $player_list + array($player_data['id']=>array('name'=>$player_data['first_name']." ".$player_data['last_name'],
																			  'stats'=>$stats,'total'=>$total,'position'=>$pos,
																			  'injury_is_injured'=>$player_data['injury_is_injured'],
																			  'injury_dl_left'=>$player_data['injury_dl_left'], 'injury_left'=>$player_data['injury_left'], 'injury_dtd_injury'=>$player_data['injury_dtd_injury'],
																			  'injury_id'=>$player_data['injury_id'],'injury_career_ending'=>$player_data['injury_career_ending']));
					}
					if ($status == 1) $team_data['players_active'] = $player_list;
					else $team_data['players_reserve'] = $team_data['players_reserve'] + $player_list;
				}
				foreach ($statsCompiled as $statusType => $compiled_stats) {
					$statsStr = "";
					foreach ($compiled_stats as $cat => $value) {
						if (!empty($statsStr)) $statsStr .= ", ";
						$statsStr .= $value." ".strtoupper(get_ll_cat($cat));
					}
					if ($statusType == 1) { $typeStr = 'stats_active'; } else { $typeStr = 'stats_reserve'; }
					$team_data[$typeStr] = $statsStr;
				}
				$rosters[$key] = $team_data;
			}
		}
		return $rosters;
	}

	public function getMemberCount($league_id = false) {
		if ($league_id === false) { $league_id = $this->id; }
		$count = 0;
		$this->db->where('league_id',$league_id);
		$this->db->from($this->tables['TEAMS']);
		$count = $this->db->count_all_results();
		return $count;
	}
	public function getDivisionNames($league_id = false) {
		if ($league_id === false) { $league_id = $this->id; }
		$divisions = array();
		$this->db->select('id, division_name');
		$this->db->where('league_id',$league_id);
		$query = $this->db->get('fantasy_divisions');
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$divisions = $divisions + array($row->id=>$row->division_name);
			}
		}
		$query->free_result();
		return $divisions;
	}
	public function getTeamDetails($league_id = false, $selectBox = false, $noOwner = false) {

		if ($league_id === false) { $league_id = $this->id; }

		$this->db->select($this->tables['TEAMS'].'.id, teamname, teamnick, '.$this->tables['TEAMS'].'.avatar, owner_id, firstName, lastName, email');
		$this->db->join('users_core','users_core.id = '.$this->tables['TEAMS'].'.owner_id','left');
		$this->db->join('users_meta','users_meta.userId = '.$this->tables['TEAMS'].'.owner_id','left');
		$this->db->where('league_id',$league_id);
		if ($noOwner !== false) {
			$this->db->where('(owner_id = 0 OR owner_id = -1)');
		}
		$this->db->order_by('id','asc');
		$query2 = $this->db->get($this->tables['TEAMS']);
		$teams = array();
		if ($selectBox != false) { $teams = array('-1'=>""); }
		if ($query2->num_rows() > 0) {
			//echo("Teams for league".$league_id." = <br/>");
			foreach ($query2->result() as $trow) {
				if ($selectBox != false) {
					$teams = $teams + array($trow->id=>$trow->teamname." ".$trow->teamnick);
				} else {
					$ownerName = $trow->firstName." ".$trow->lastName;
					if ($trow->owner_id != -1 && $trow->owner_id == $this->commissioner_id) {
						$ownerName .= " (Commisioner)";
					}
					$teams = $teams + array($trow->id=>array('teamname'=>$trow->teamname,'teamnick'=>$trow->teamnick,'avatar'=>$trow->avatar,
													     'owner_id'=>$trow->owner_id,'owner_name'=>$ownerName,'owner_email'=>$trow->email));
				}
			}
		}
		$query2->free_result();
		return $teams;
	}
	public function getTeamIdList($league_id = false) {
		if ($league_id === false) { $league_id = $this->id; }
		$this->db->select('id');
		$this->db->where('league_id',$league_id);
		$this->db->order_by('id','asc');
		$query2 = $this->db->get($this->tables['TEAMS']);
		$teams = array();
		if ($query2->num_rows() > 0) {
			foreach ($query2->result() as $trow) {
				array_push($teams, $trow->id);
			}
		}
		$query2->free_result();
		return $teams;
	}

	public function getFullLeageDetails($league_id = false, $noOwner = false) {
		if ($league_id === false) { $league_id = $this->id; }
		$divisions = array();
		$this->db->select('id, division_name');
		$this->db->where('league_id',$league_id);
		$this->db->order_by('division_name','asc');
		$query = $this->db->get('fantasy_divisions');
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$this->db->flush_cache();
				$this->db->select('fantasy_teams.id, teamname,teamnick,fantasy_teams.owner_id,firstName, lastName, email,fantasy_teams.avatar,auto_draft,auto_list,auto_round_x');
				$this->db->join('users_core','users_core.id = fantasy_teams.owner_id','left');
				$this->db->join('users_meta','users_meta.userId = fantasy_teams.owner_id','left');
				$this->db->where('league_id',$league_id);
				$this->db->where('division_id',$row->id);
				if ($noOwner !== false) {
					$this->db->where('(owner_id = 0 OR owner_id = -1)');
				}
				$this->db->order_by('teamname, teamnick','asc');
				$query2 = $this->db->get($this->tables['TEAMS']);
				$teams = array();
				if ($query2->num_rows() > 0) {
					foreach ($query2->result() as $trow) {
						$ownerName = $trow->firstName." ".$trow->lastName;
						if ($trow->owner_id == $this->commissioner_id) {
							$ownerName .= " (Commisioner)";
						}
						$teams = $teams + array($trow->id=>array('teamname'=>$trow->teamname,'teamnick'=>$trow->teamnick,
																'owner_id'=>$trow->owner_id,'owner_name'=>$ownerName ,
																'owner_aim'=>'','owner_email'=>$trow->email,
																'avatar'=>$trow->avatar,'auto_draft'=>$trow->auto_draft,
																'auto_list'=>$trow->auto_list,'auto_round_x'=>$trow->auto_round_x));
					}
				}
				$query2->free_result();
				$divisions = $divisions + array($row->id=>array('division_name'=>$row->division_name,'teams'=>$teams));
			}
		}
		$query->free_result();
		return $divisions;
	}

	public function getLeagueStandings($curr_period_id = false,$league_id = false) {
		if ($league_id === false) { $league_id = $this->id; }

		$scoring_type = $this->getScoringType();

		switch ($scoring_type) {
			case LEAGUE_SCORING_ROTO:
			case LEAGUE_SCORING_ROTO_5X5:
			case LEAGUE_SCORING_ROTO_PLUS:
				$rules = $this->getScoringRules();
				$teams = array();
				$this->db->flush_cache();
				$this->db->select($this->tables['TEAMS'].'.id, teamname, teamnick, avatar, value_0 as val_0, value_1 as val_1, value_2 as val_2, value_3 as val_3, value_4 as val_4,
				value_5 as val_5, value_6 as val_6, value_7 as val_7, value_8 as val_8, value_9 as val_9, value_10 as val_10, value_11 as val_11, total as total');
				$this->db->join($this->tables['TEAMS'],'fantasy_teams_scoring.team_id = '.$this->tables['TEAMS'].'.id','right outer');
				$this->db->where('fantasy_teams.league_id',$league_id);
				if ($curr_period_id !== false) {
					$this->db->where('scoring_period_id',$curr_period_id);
				} // END if
				$this->db->order_by('total','desc');
				$query = $this->db->get('fantasy_teams_scoring');
				//print($this->db->last_query()."<br />");
				$teams = array();
				if ($query->num_rows() > 0) {
					foreach ($query->result() as $row) {
						$catCount = 0;
						$types = array('batting','pitching');
						foreach($types as $type) {
							foreach($rules[$type] as $cat => $val) {
								$colName = 'val_'.$catCount;
								if (!isset($row->$colName)) {

								}
							}
						}
						$teams = $teams + array($row->id=>array('teamname'=>$row->teamname,'teamnick'=>$row->teamnick,'avatar'=>$row->avatar,
																'value_0' => $row->val_0, 'value_1' => $row->val_1, 'value_2' => $row->val_2, 'value_3' => $row->val_3, 'value_4' => $row->val_4,
																'value_5' => $row->val_5, 'value_6' => $row->val_6, 'value_7' => $row->val_7, 'value_8' => $row->val_8, 'value_9' => $row->val_9,
																'value_10' => $row->val_10, 'value_11' => $row->val_11, 'total' => $row->total));
					}
				}
				$query->free_result();
				return $teams;
				break;

			case LEAGUE_SCORING_HEADTOHEAD:
			default:
				$divisions = array();
				$this->db->select('id, division_name');
				$this->db->where('league_id',$league_id);
				$query = $this->db->get('fantasy_divisions');
				if ($query->num_rows() > 0) {
					foreach ($query->result() as $row) {
						$this->db->flush_cache();
						$this->db->select('fantasy_teams.id, teamname, teamnick, avatar, g, w, l, pct');
						$this->db->join('fantasy_teams_record','fantasy_teams_record.team_id = fantasy_teams.id','left');
						$this->db->where('fantasy_teams.league_id',$league_id);
						$this->db->where('fantasy_teams.division_id',$row->id);
						if ($curr_period_id !== false) {
							$this->db->where('scoring_period_id',$curr_period_id);
						} // END if
						$this->db->order_by('pct','desc');
						$query2 = $this->db->get($this->tables['TEAMS']);

						$teams = array();
						if ($query2->num_rows() > 0) {
							foreach ($query2->result() as $trow) {
								$teams = $teams + array($trow->id=>array('teamname'=>$trow->teamname,'teamnick'=>$trow->teamnick,'avatar'=>$trow->avatar,
																		'g'=>$trow->g,'w'=>$trow->w,'l'=>$trow->l,'pct'=>$trow->pct));
							}
						}
						$query2->free_result();
						$divisions = $divisions + array($row->id=>array('division_name'=>$row->division_name,'teams'=>$teams));
					}
				}
				$query->free_result();
				return $divisions;
				break;
		}
	}

	/**
	 *	CREATE LEAGUE SCHEDULE.
	 *	Builds a scheudle for all teams based on the number of teams, number of scoring periods
	 *	and the number of games per team.
	 *  @param	$league_id - If not specified, no league filter is applied.
	 *	@return	"OK" on success, false on failure
	 */
	public function createLeagueSchedule($league_id = false) {

		if ($league_id === false) { $league_id = $this->id; }

		// GET ALL TEAMS
		$teams = array();
		$this->db->select("id");
		$this->db->where("league_id",$league_id);
		$query = $this->db->get($this->tables['TEAMS']);
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				array_push($teams,$row->id);
			}
		}
		$query->free_result();

		// DELETE ALL GAMES FOR THIS LEAGUE IF THEY EXIST
		$this->db->flush_cache();
		$this->db->where('league_id',$league_id);
		$this->db->delete('fantasy_leagues_games');

		$matchups = sizeof($teams) * $this->games_per_team;

		//echo("matchups per period = ".$matchups."<br />");

		for ($s = 1; $s < ($this->regular_scoring_periods +1); $s++) {

			$data = array();
			//echo("Scoring period ".$s."<br />");
			$teamCount = 0;
			$periodDone = false;
			$gamesPerTeam = array();
			$type = 0;
			for ($p = 0; $p < $matchups; $p++) {


				$team_id = $teams[intval(rand(0, (sizeof($teams)-1)))];
				if (isset($gamesPerTeam[$team_id]) && $gamesPerTeam[$team_id] >= $this->games_per_team) { }
				else {

					// PICK THREE RANDOM OPPONENTS FOR THE CURRENT TEAM
					// ALTERNATE HOME AND AWAY
					$type = ($type == 0) ? 1 : 0;
					$tries = 0;
					while (true) {
						$opponent = $teams[intval(rand(0, (sizeof($teams)-1)))];
						//echo("Opponent = ".$opponent."<br />");
						if ($opponent != $team_id) {
							$gamesForTeam = isset($gamesPerTeam[$opponent]) ? $gamesPerTeam[$opponent] : 0;
							if ($gamesForTeam < $this->games_per_team) {
								if ($type == 0) {
									$home_team_id = $team_id;
									$away_team_id = $opponent;
								} else {
									$home_team_id = $opponent;
									$away_team_id = $team_id;
								} // END if
								array_push($data,array('league_id'=>$league_id,'home_team_id'=>$home_team_id,
											  'away_team_id'=>$away_team_id,'scoring_period_id'=>$s));

								if (isset($gamesPerTeam[$opponent]))
									$gamesPerTeam[$opponent] = $gamesPerTeam[$opponent] + 1;
								else
									$gamesPerTeam[$opponent] = 1; // END if

								if (isset($gamesPerTeam[$team_id]))
									$gamesPerTeam[$team_id] = $gamesPerTeam[$team_id] + 1;
								else
									$gamesPerTeam[$team_id] = 1; // END if
								break;
							} // END if
						} // END if
						$tries++;
						if ($tries > 500) {
							//echo("In loop = true");
							//echo("Current team id = ".$team_id."<br />");
							//echo("Has any games? ".(isset($gamesPerTeam[$team_id]) ? "true" : "false")."<br />");
							// ATEMPT A MATCHUP MANUALLY
							if (isset($gamesPerTeam[$team_id])) {
								//echo("Games for team ".$team_id." = ".$gamesPerTeam[$team_id]."<br />");
								if ($gamesPerTeam[$team_id] < $this->games_per_team) {
								// FIND ANOTHER TEAM UNDER THE MINIMUM
									foreach($gamesPerTeam as $team_id_val => $game_count) {
										if ($game_count < $this->games_per_team) {
											if ($type == 0) {
												$home_team_id = $team_id;
												$away_team_id = $team_id_val;
											} else {
												$home_team_id = $team_id_val;
												$away_team_id = $team_id;
											} // END if
											array_push($data,array('league_id'=>$league_id,'home_team_id'=>$home_team_id,
											 'away_team_id'=>$away_team_id,'scoring_period_id'=>$s));
											if (isset($gamesPerTeam[$home_team_id]))
												$gamesPerTeam[$home_team_id] = $gamesPerTeam[$home_team_id] + 1;
											else
												$gamesPerTeam[$home_team_id] = 1; // END if
											if (isset($gamesPerTeam[$away_team_id]))
												$gamesPerTeam[$away_team_id] = $gamesPerTeam[$away_team_id] + 1;
											else
												$gamesPerTeam[$away_team_id] = 1; // END if
											break;
										}
									}
								}
							} else {
								//echo("No matchups.<br />");
								// THIS TEAM HAS NO MATCHUPS THIS PERIOD
								// BREAK UP ANOTHER GAME AND GIVE THE OPPOENTS TO THIS TEAM
								$gameData = array_shift($data);
								//echo("# of games saved = ".sizeof($data)."<br />");
								//echo("fields in gameData = ".sizeof($gameData)."<br />");
								$home_team_id = $gameData['home_team_id'];
								$away_team_id = $gameData['away_team_id'];
								//echo("home_team_id = ".$home_team_id."<br />");
								//echo("away_team_id = ".$away_team_id."<br />");

								array_push($data,array('league_id'=>$league_id,'home_team_id'=>$team_id,
									  'away_team_id'=>$home_team_id,'scoring_period_id'=>$s));
								array_push($data,array('league_id'=>$league_id,'home_team_id'=>$away_team_id,
											  'away_team_id'=>$team_id,'scoring_period_id'=>$s));
								if (!isset($gamesPerTeam[$team_id]))
									$gamesPerTeam[$team_id] = 2; // END if

							}
							$periodDone = true; break;
						}
					} // END while
					if ($periodDone) { break; }
				} // END if
			} // END for
			$totalGames = 0;
			if (sizeof($gamesPerTeam) > 0) {
				foreach($gamesPerTeam as $team_id_val => $game_count) {
					//echo($team_id_val ." has ".$game_count." games this period.<br />");
					$totalGames += $game_count;
				}
			}
			$loops = 0;
			while (true) {
				//echo("Total games before correct for period = ".$totalGames."<br />");
				//echo("Total games less than matchups? = ".(($totalGames < $matchups) ? "true" : "false")."<br />");
				if ($totalGames < $matchups) {
					if (sizeof($gamesPerTeam) > 0 && sizeof($gamesPerTeam) == sizeof($teams)) {
						$team1Id = -1;
						// FIND A TEAM UNDER THE MINIMUM
						foreach($gamesPerTeam as $team_id_val => $game_count) {
							if ($game_count < $this->games_per_team) {
								$team1Id = $team_id_val;
								break;
							}
						}
						//echo("team under the minimum ".$team1Id." = ".$game_count."<br />");
						// FIND ANOTHER TEAM UNDER THE MINIMUM
						foreach($gamesPerTeam as $team_id_val => $game_count) {
							if ($team_id_val != $team1Id && $game_count < $this->games_per_team) {
								//echo("second under the minimum ".$team_id_val." = ".$game_count."<br />");

								$type = ($type == 0) ? 1 : 0;
								if ($type == 0) {
									$home_team_id = $team1Id;
									$away_team_id = $team_id_val;
								} else {
									$home_team_id = $team_id_val;
									$away_team_id = $team1Id;
								} // END if
								array_push($data,array('league_id'=>$league_id,'home_team_id'=>$home_team_id,
								 'away_team_id'=>$away_team_id,'scoring_period_id'=>$s));
								$gamesPerTeam[$home_team_id] = $gamesPerTeam[$home_team_id] + 1;
								$gamesPerTeam[$away_team_id] = $gamesPerTeam[$away_team_id] + 1;
								$totalGames += 2;
							}
						}
					} else {
						$team1Id = -1;
						// FIND A TEAM WITH NO GAMES ENTRY
						foreach($teams as $team_id_val) {
							if (!isset($gamesPerTeam[$team_id_val])) {
								$team1Id = $team_id_val;
								break;
							} // END if
						} // END foreach
						//echo("Team missing games = ".$team1Id."<br />");
						// THIS TEAM HAS NO MATCHUPS THIS PERIOD
						// BREAK UP ANOTHER GAME AND GIVE THE OPPOENTS TO THIS TEAM
						$gameData = array_shift($data);
						//echo("# of games saved = ".sizeof($data)."<br />");
						//echo("fields in gameData = ".sizeof($gameData)."<br />");
						$home_team_id = $gameData['home_team_id'];
						$away_team_id = $gameData['away_team_id'];
						//echo("home_team_id = ".$home_team_id."<br />");
						//echo("away_team_id = ".$away_team_id."<br />");
						array_push($data,array('league_id'=>$league_id,'home_team_id'=>$team1Id,
									  'away_team_id'=>$home_team_id,'scoring_period_id'=>$s));

						array_push($data,array('league_id'=>$league_id,'home_team_id'=>$away_team_id,
									  'away_team_id'=>$team1Id,'scoring_period_id'=>$s));
						if (!isset($gamesPerTeam[$team1Id]))
							$gamesPerTeam[$team1Id] = 2; // END if
						$totalGames += 2;
					}
				} else {
					break;
				}
				$loops++;
				if ($loops == 100) { break; } // END if
			}
			$totalGames = 0;
			if (sizeof($gamesPerTeam) > 0) {
				foreach($gamesPerTeam as $team_id_val => $game_count) {
					$totalGames += $game_count;
				} // END foreach
			} // END if
			foreach($data as $query) {
				$this->db->flush_cache();
				$this->db->insert($this->tables['GAMES'],$query);
			} // END foreach
		} // END for
		return "OK";
	} // END function

	/**
	 *	GET LEAGUE SCHEDULE.
	 *	Returns either the entire schdule for the specified league OR only games for a
	 *	specific team (if specified).
	 *  @param	$team_id - If not specified, the schedule for the entire league is returned.
	 *  @param	$excludeScores - Set to TRUE to not return score information
	 *  @param	$league_id - If not specified, no league filter is applied.
	 *	@return	schedule array, false on failure
	 */
	public function getLeagueSchedule($team_id = false, $excludeScores = false, $league_id = false) {

		if ($league_id === false) { $league_id = $this->id; }

		$teamNames = $this->loadLeagueTeams($league_id);

		$schedule = array();
		$score_period_id = 0;
		$this->db->flush_cache();
		$this->db->select('away_team_id, away_team_score, home_team_score, home_team_id,  scoring_period_id');
		$this->db->where('league_id',$league_id);
		if ($team_id != false) {
			$this->db->where('(home_team_id = '.$team_id.' OR away_team_id = '.$team_id.')');
		}
		$this->db->order_by('scoring_period_id','asc');
		$query = $this->db->get($this->tables['GAMES']);
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				if ($score_period_id != $row->scoring_period_id) {
					$score_period_id = $row->scoring_period_id;
					$schedule[$score_period_id] = array();
				}
				$homeTeam = $teamNames[$row->home_team_id];
				$awayTeam = $teamNames[$row->away_team_id];
				$stats = array('home_team'=>$homeTeam, 'away_team'=>$awayTeam);
				if (!$excludeScores && (isset($row->home_team_score) && isset($row->away_team_score))) {
					$stats = $stats + array('home_team_score'=>$row->home_team_score,'away_team_score'=>$row->away_team_score);
				}
				array_push($schedule[$score_period_id],$stats);
			}
		}
		$query->free_result();
		return $schedule;
	}

	/**
	 *	GET LEAGUE TRANSACTIONS.
	 *	Returns either the entire schdule for the specified league OR only games for a
	 *	specific team (if specified).
	 *  @param	$team_id - If not specified, the schedule for the entire league is returned.
	 *  @param	$excludeScores - Set to TRUE to not return score information
	 *  @param	$league_id - If not specified, no league filter is applied.
	 *	@return	schedule array, false on failure
	 */
	public function getLeagueTransactions($limit = -1, $startIndex = 0, $team_id = false, $league_id = false) {

		if ($league_id === false) { $league_id = $this->id; }

		$transactions = array();
		$teamNames = $this->loadLeagueTeams($league_id);

		$this->db->select("trans_date, team_id, added, dropped, claimed, tradedTo, tradedFrom, trade_team_id, trans_owner, effective");
		$this->db->where("league_id",$league_id);
		if ($team_id !== false) {
			$this->db->where('team_id',$team_id);
		}
		if ($limit != -1 && $startIndex == 0) {
			$this->db->limit($limit);
		} else if ($limit != -1 && $startIndex > 0) {
			$this->db->limit($startIndex,$limit);
		}
		$this->db->order_by('trans_date','desc');
		$query = $this->db->get($this->tables['TRANSACTIONS']);
		if ($query->num_rows() > 0) {
			$transTypes = array('added','dropped','claimed','tradedTo','tradedFrom');

			if (!function_exists('getFantasyPlayersDetails')) {
				$this->load->helper('roster');
			}
			foreach($query->result() as $row) {
				$transArrays = array();
				foreach ($transTypes as $field) {
					//echo($field."<br />");
					$transArrays[$field] = array();
					if (isset($row->$field) && !empty($row->$field) && strpos($row->$field,":")) {
						$fieldData = unserialize($row->$field);
						if (is_array($fieldData) && sizeof($fieldData) > 0) {
							//echo("size of ".$field." data = ".sizeof($fieldData)."<br />");
							$playerDetails = getFantasyPlayersDetails($fieldData);
							foreach ($fieldData as $playerId) {
								//echo($field." player id = ".$playerId."<br />");
								$transStr = '';
								if (isset($playerDetails[$playerId])) {
									$pos = $playerDetails[$playerId]['position'];
									if ($pos == 1) { $pos = $playerDetails[$playerId]['role']; }
									$transStr .= get_pos($pos);
									$transStr .= "&nbsp; ".anchor('/players/info/league_id/'.$league_id.'/player_id/'.$playerId,$playerDetails[$playerId]['first_name']." ".$playerDetails[$playerId]['last_name']);
								} // END if
								//echo($transStr."<br />");
								if (!empty($transStr)) { array_push($transArrays[$field], $transStr); }
							} // END foreach
						} // END if
					} // END if
				} // END foreach
				if (!function_exists('getScoringPeriodByDate')) {
					$this->load->helper('admin');
				}
				$trade_team_name = "";
				if ($row->trade_team_id > 0) {
					$trade_team_name = $teamNames[$row->trade_team_id];
				}
				array_push($transactions,array('trans_date'=>$row->trans_date, 'team_id'=>$row->team_id,
													  'added'=>$transArrays['added'], 'dropped'=>$transArrays['dropped'],
													  'claimed'=>$transArrays['claimed'], 'tradedTo'=>$transArrays['tradedTo'], 'tradedFrom'=>$transArrays['tradedFrom'],
													  'trans_owner'=>$row->trans_owner, 'effective'=>$row->effective,
													  'trade_team_id'=>$row->trade_team_id,'trade_team_name'=>$trade_team_name));
			}
		}
		$query->free_result();
		unset($query);
		return $transactions;
	}
	/**
	 *	GET OPEN LEAGUES.
	 *	Returns pending waiver claims for the specified league.
	 *  @param	$team_id - If not specified, the schedule for the entire league is returned.
	 *  @param	$league_id - If not specified, no league filter is applied.
	 *	@return	schedule array, false on failure
	 */
	public function getOpenLeagues($user_id = false) {

		$leagues = array();
		$select = $this->tblName.'.id,league_name,commissioner_id,username,leagueType, max_teams, (SELECT COUNT(id) FROM fantasy_teams WHERE league_id = '.$this->tblName.'.id AND (owner_id = 0 OR owner_id = -1)) as openCount';
		if ($user_id !== false) {
			$select .=  ', (SELECT COUNT(id) FROM fantasy_teams WHERE league_id = '.$this->tblName.'.id AND owner_id = '.$user_id.') as teamsOwned';
		}
		$this->db->select($select);
		$this->db->join("fantasy_leagues_types","fantasy_leagues_types.id = ".$this->tblName.".league_type", "left");
		$this->db->join("users_core","users_core.id = ".$this->tblName.".commissioner_id", "left");
		$this->db->where('accept_requests',1);
		$this->db->where('league_status',1);
		$query = $this->db->get($this->tblName);
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				if ($row->openCount > 0 && ($user_id === false || ($user_id !== false && isset($row->teamsOwned) && $row->teamsOwned == 0))) {
					array_push($leagues,array('id'=>$row->id,'league_name'=>$row->league_name, 'max_teams'=>$row->max_teams,
										 'leagueType'=>$row->leagueType, 'openings'=>$row->openCount,
										 'commissioner_id'=>$row->commissioner_id,'commissioner_name'=>$row->username));

				} // END if
			} // END foreach
		} // END if
		$query->free_result();
		unset($query);
		return $leagues;
	}
	/**
	 *	GET WAIVER CLAIMS.
	 *	Returns pending waiver claims for the specified league.
	 *  @param	$team_id - If not specified, the schedule for the entire league is returned.
	 *  @param	$league_id - If not specified, no league filter is applied.
	 *	@return	schedule array, false on failure
	 */
	public function getWaiverClaims($limit = -1, $startIndex = 0, $team_id = false, $league_id = false) {

		if ($league_id === false) { $league_id = $this->id; }

		$claims = array();
		$this->db->select($this->tables['WAIVER_CLAIMS'].".id, ".$this->tables['WAIVER_CLAIMS'].".team_id, teamname, teamnick, ".$this->tables['WAIVER_CLAIMS'].".player_id, first_name, last_name, waiver_period");
		$this->db->join("fantasy_teams","fantasy_teams.id = ".$this->tables['WAIVER_CLAIMS'].".team_id", "left");
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
		$this->db->order_by('waiver_period, teamname, last_name','asc');
		$query = $this->db->get($this->tables['WAIVER_CLAIMS']);
		//echo($this->db->last_query()."<br />");
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				array_push($claims,array('id'=>$row->id,'team_id'=>$row->team_id, 'teamname'=>$row->teamname." ".$row->teamnick,
										 'player_id'=>$row->player_id, 'player_name'=>$row->first_name." ".$row->last_name,
										 'waiver_period'=>$row->waiver_period));
			}
		}
		$query->free_result();
		unset($query);
		return $claims;
	}
	/**
	 *	GET SCORING RULES.
	 *	Returns the scoring rules for a specific league (if they exist) or the
	 *	global scoring rules in they don't
	 *  @param	$league_id - If not specified, no league filter is applied.
	 *	@return	Rules array on success, false on failure
	 */
	public function getScoringRules($league_id = false, $scoring_type = false, $returnDefault = true) {

		if ($league_id === false) { $league_id = $this->id; } // END if
		if ($scoring_type === false) { $scoring_type = $this->league_type; } // END if

		$rules = array('batting'=>array(),'pitching'=>array());
		$default = false;
		foreach($rules as $key => $data) {
			// TEST for custom scoring rules
			// if not present, use the default rules for the league (league_id 0)
			$this->db->where('league_id',$league_id);
			$this->db->where('scoring_type',$scoring_type);
			$this->db->from('fantasy_leagues_scoring_'.$key);
			$count = $this->db->count_all_results();
			if ($count == 0 && $returnDefault === true) {
				$league_id = 0;
			} // END if
			$this->db->select('*');
			$this->db->where('league_id',$league_id);
			$this->db->where('scoring_type',$scoring_type);
			$query = $this->db->get('fantasy_leagues_scoring_'.$key);
			if ($query->num_rows() > 0) {
				$score_type =
				$cats = array();
				foreach ($query->result() as $row) {
					for ($i = 0; $i < 12; $i++) {
						$columnT = "type_".$i;
						$columnV = "value_".$i;
						if ($row->$columnT != -1) {
							$cats = $cats + array($row->$columnT=>$row->$columnV);
						} // END if
					} // END for
				} // END foreach
				$rules[$key] = $cats;
			} // END if
			$query->free_result();
			unset($query);
		} // END foreach
		$rules['league_id'] = $league_id;
		$rules['scoring_type'] = $scoring_type;
		return $rules;
	}

	/**
	 * SET SCORING RULES
	 * This function accepts a form input object and applies the passed values to
	 * the scoring rules tables.
	 * @param	$input		CodeIgniter form input object
	 * @param	$league_id 	Optional league ID. Defaults to "0" if no id is passed.
	 */
	public function setScoringRules($input, $league_id = false) {

		if ($league_id === false) { $league_id = 0; }

		$this->db->where('league_id', $league_id);
		$this->db->where('scoring_type', $input->post('scoring_type'));
		$this->db->delete($this->tables['SCORING_RULES_BATTING']);
		$this->db->where('league_id', $league_id);
		$this->db->where('scoring_type', $input->post('scoring_type'));
		$this->db->delete($this->tables['SCORING_RULES_PITCHING']);

		$types = array('batting','pitching');
		foreach($types as $type) {
			$lineCount = 0;
			$data = array('league_id'=>$league_id,'scoring_type'=>$input->post('scoring_type'));
			while ($lineCount < 11) {
				if ($input->post($type.'_type_'.$lineCount) && $input->post($type.'_type_'.$lineCount) != -1) {
					$data = $data + array('type_'.$lineCount=>$input->post($type.'_type_'.$lineCount),
								  'value_'.$lineCount=>$input->post($type.'_value_'.$lineCount));
				}
				$lineCount++;
			}
			$this->db->insert($this->tables['SCORING_RULES_'.strtoupper($type)],$data);
			//echo($this->db->last_query()."<br />");
		}
		return true;
	}

	public function getAllScoringRulesforSim($league_id = false) {

		$scoring_rules = array();
		$scoring_types = loadSimpleDataList('leagueType');
		// ASSEMBLE UNIQUE SCORING RULES FOR LEAGUES
		if ($league_id !== false) {
			$scoring_list = array();
			foreach($scoring_types as $typeId => $typeName) {
				$scoring_list[$typeId] = $this->getScoringRules($league_id,$typeId);
			}
			$scoring_rules[$league_id] = $scoring_list;
		} else {
			$leagues = $this->getLeagues();
			//echo($this->_NAME." = ".$this->db->last_query()."<br />");
			$returnDefault = true;
			foreach ($leagues as $id => $data) {
				$scoring_list = array();
				foreach($scoring_types as $typeId => $typeName) {
					if (isset($scoring_rules[0]) && sizeof($scoring_rules[0]) > 0) {
						$returnDefault = false;
					}
					$scoring_list[$typeId] = $this->getScoringRules($id,$typeId,$returnDefault);
				}
				if ($scoring_list !== false) {
					$scoring_rules = $scoring_rules + array($id=>$scoring_list);
				}
			}
		}
		return $scoring_rules;
	}
	/**
	 *	GET Roster RULES.
	 *	Returns the roster rules for a specific league (if they exist) or the
	 *	global roster rules in they don't
	 *  @param	$league_id - If not specified, no league filter is applied.
	 *	@return	Rules array on success, false on failure
	 */
	public function getRosterRules($league_id = false) {
		if ($league_id === false) { $league_id = $this->id; }
		$rules = array();
		$this->db->select();
		$this->db->from('fantasy_roster_rules');
		$this->db->where('league_id',$league_id);
		if ($this->db->count_all_results() == 0) {
			$league_id = 0;
			$this->db->where('league_id',$league_id);
		}
		$this->db->select();
		$this->db->order_by('position', 'asc');
		$query = $this->db->get('fantasy_roster_rules');
		//echo($this->db->last_query()."<br />");
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				//echo($row->position."<br />");
				$rules = $rules + array($row->position=>array('position'=>$row->position,
										'active_min'=>$row->active_min,'active_max'=>$row->active_max));

			}
		}
		return $rules;
	}
	/**
	 * SET ROSTER RULES
	 * This function accepts a form input object and applies the passed values to
	 * the roster rules table.
	 * @param	$input		CodeIgniter form input object
	 * @param	$league_id 	Optional league ID. Defaults to "0" if no id is passed.
	 */
	public function setRosterRules($input, $league_id = false) {
		if ($league_id === false) { $league_id = 0; }

		$this->db->where('league_id', $league_id);
		$this->db->delete($this->tables['ROSTER_RULES']);

		$lineCount = 0;
		while ($lineCount < 10) {
			$data = array();
			if ($input->post('pos'.$lineCount) && $input->post('pos'.$lineCount) != -1) {
				$data = array('league_id'=>$league_id,
							  'position'=>$input->post('pos'.$lineCount),
							  'active_min'=>$input->post('min'.$lineCount),
							  'active_max'=>$input->post('max'.$lineCount));
				$this->db->insert($this->tables['ROSTER_RULES'],$data);
			}
			$lineCount++;
		}
		$types = array(100=>'active', 101=>'reserve',102=>'injured');
		foreach($types as $code => $label) {
			$this->db->insert($this->tables['ROSTER_RULES'],array('league_id'=>$league_id,
								  'position'=>$code,
								  'active_min'=>$input->post('total_'.$label.'_min'),
								  'active_max'=>$input->post('total_'.$label.'_max')));
		}
		return true;
	}
	/**
	 * 	VALIDATE ROSTER
	 * 
	 * 	This function tests the provided roster against the leagues roster rules.
	 * 	@param	$roster		Team Roster Array
	 * 	@param	$league_id 	Optional league ID. Defaults to model value if no id is passed.
	 * 	@return	{Boolean} 	TRUE if valid, FALSE if not
	 * 	@since 	1.0
	 * 
	 */
	public function validateRoster($roster,$league_id = false) {

		//echo("Validate Roser<br />");
		$valid = true;
		if ($league_id === false) { $league_id = $this->id; }
		$errors = "";

		$rules = $this->getRosterRules($league_id);
		$errCount = 0;
		$activePos = array();
		$activeCount = 0;
		$reserveCount = 0;
		$injuredCount = 0;
		//echo("Roster size = ".sizeof($roster)."<br />");
		foreach($roster as $player_info) {
			//echo("Player ".$player_info['player_name']."<br />");
			if ($player_info['player_status'] == 1) {
				if ($player_info['player_position'] != 1) {
					$pos = $player_info['player_position'];
				} else {
					$pos = $player_info['player_role'];
				}
				//echo("Player pos = ".get_pos($pos)."<br />");

				if (!isset($activePos[$pos])) {
					$activePos[$pos] = 1;
				} else {
					$activePos[$pos] += 1;
				}
				$activeCount++;
			} else if ($player_info['player_status'] == -1) {
				$reserveCount++;
			} else if ($player_info['player_status'] == 2) {
				$injuredCount++;
			}
		}
		foreach($rules as $position => $ruleData) {
			if ($position < 100) {
				if (isset($activePos[$position])) {
					if ($activePos[$position] < $ruleData['active_min']) {
						//echo("Count for ".strtoupper(get_pos($position))." is below the minimum of ".$ruleData['active_min']."<br />");
						$valid = false;
						if ($errors != '') { $errors .= "<br />"; }
						$errors .= "The position ".strtoupper(get_pos($position))." has ".$activePos[$position]." active players. At least ".$ruleData['active_min']." ".($ruleData['active_min']>1 ? "are" : "is")." required.";
						$errCount++;
					} else if ($activePos[$position] > $ruleData['active_max']) {
						$valid = false;
						if ($errors != '') { $errors .= "<br />"; }
						$errors .= "The position ".strtoupper(get_pos($position))." has ".$activePos[$position]." active players. At most, ".$ruleData['active_max']." ".($ruleData['active_min']>1 ? "are" : "is")." allowed.";
						$errCount++;
					}
				} else {
					if ($ruleData['active_min'] > 0) {
						$valid = false;
						if ($errors != '') { $errors .= "<br />"; }
						$errors .= "The position ".strtoupper(get_pos($position))." has 0 active players. At least ".$ruleData['active_min']." ".($ruleData['active_min']>1 ? "are" : "is")." required.";
						$errCount++;
					}
				}
			} else {
				if ($position == 100) {
					//$active = sizeof($activeCount);
					if ($activeCount < $ruleData['active_min']) {
						$valid = false;
						if ($errors != '') { $errors .= "<br />"; }
						$errors .= "There are only ".$activeCount." active players. At least ".$ruleData['active_min']." ".($ruleData['active_min']>1 ? "are" : "is")." required.";
						$errCount++;

					} else if ($activeCount > $ruleData['active_max']) {
						$valid = false;
						if ($errors != '') { $errors .= "<br />"; }
						$errors .= "There are ".$activeCount." active players. At most, ".$ruleData['active_max']." ".($ruleData['active_max']>1 ? "are" : "is")." allowed.";
						$errCount++;
					}
				}
				if ($position == 101) {;
					if ($reserveCount < $ruleData['active_min']) {
						$valid = false;
						if ($errors != '') { $errors .= "<br />"; }
						$errors .= "There are only ".$reserveCount." reserve players. At least ".$ruleData['active_min']." ".($ruleData['active_min']>1 ? "are" : "is")." required.";
						$errCount++;
					} else if ($reserveCount > $ruleData['active_max']) {
						$valid = false;
						if ($errors != '') { $errors .= "<br />"; }
						$errors .= "There are ".$reserveCount." reserve players. At most, ".$ruleData['active_max']." ".($ruleData['active_max']>1 ? "are" : "is")." allowed.";
						$errCount++;
					}
				}
				if ($position == 102) {;
					if ($injuredCount < $ruleData['active_min']) {
						$valid = false;
						if ($errors != '') { $errors .= "<br />"; }
						$errors .= "There are only ".$injuredCount." inured reserrve players. At least ".$ruleData['active_min']." ".($ruleData['active_min']>1 ? "are" : "is")." required.";
						$errCount++;
					} else if ($injuredCount > $ruleData['active_max']) {
						$valid = false;
						if ($errors != '') { $errors .= "<br />"; }
						$errors .= "There are ".$injuredCount." injured reserve players. At most, ".$ruleData['active_max']." ".($ruleData['active_max']>1 ? "are" : "is")." allowed.";
						$errCount++;
					}
				}
			}
		}
		if (!$valid) {
			$this->errorCode = 1;
		}
		$this->$errorCount = $errCount;
		$this->statusMess = $errors;
		return $valid;
	}
	/**
	 * 	VALIDATE LEAGUE ROSTERS
	 * 	
	 * 	Validate the rosters of all teams in a league. 
	 * 	Mainly a simple helper that calls validateRoster() for the passed teams.
	 * 
	 *	@param	$period_id		{Array}	
	 *	@param	$league_id		{Array}	
	 *	@param	$excludeList	{Array}		Array of treams to exclude from validation
	 *	@return					{Array}		Array of validation reports for each team
	 *	@since	1.0.3 PROD
	 */
	public function validateRosters($period_id = false, $league_id = false, $excludeList = array()) {
		
		if ($league_id === false) { $league_id = $this->id; }
		if ($period_id === false) { $period_id = 1; }
		
		$validation = array();
		if ($league_id === false) { $league_id = $this->id; }
		$message = "";
		$allValid = true;
		if (!function_exists('getBasicRoster')) {
			$this->load->helper('roster');
		}
		$teams = $this->getTeamDetails($league_id);
		if (sizeof($teams) > 0) {
			foreach($teams as $team_id => $details) {
				if (!in_array($team_id, $excludeList)) {
					$roster = getBasicRoster($team_id, $period_id);
					$valid = $this->validateRoster($roster, $league_id);
					array_push($validation, array('team_id'=>$team_id, 'details'=>$details, 'rosterValid'=>(($valid)?1:-1), 'issueCount'=>$this->$errorCount, 'validationDetails'=>$this->statusMess));
					if (!$valid) $allValid = false;
				} else {
					array_push($validation, array('team_id'=>$team_id, 'details'=>$details, 'rosterValid'=>100, 'issueCount'=>0, 'validationDetails'=>'Validation Skipped'));
				}
			}
		} else {
			array_push($validation, array('team_id'=>'-1', NULL, -1, 1, "The League has no teams."));
		}
		if (!$allValid) {
			$this->errorCode = 1;
			$this->$errorCount = $errorCount;
			$this->statusMess = "error|One or more rosters are currently invalid!";
		} else {
			$this->statusMess = "success|All rosters are currently valid!";
		}
		return $validation;
	}
	/**
	 * 	GET GAMES FOR PERIOD
	 * 	
	 * 	Returns the games for a league for the passed scoring period
	 * 
	 *	@param	$period_id		{Int}		The scoring period ID
	 *	@param	$excludeList	{Array}		Sets game count to 0 for any passed IDs
	 *	@param	$league_id		{Int}		Optional league ID. Defaults to model value if no id is passed.
	 *	@return					{Array}		An array of games
	 *	@since	1.0
	 *
	 *
	 */
	public function getGamesForPeriod($period_id = false, $excludeList = array(), $league_id = false) {

		if ($period_id === false) { $period_id = 1; }
		if ($league_id === false) { $league_id = $this->id; }
		$games = array();
		$this->db->select('id, home_team_id, home_team_score, away_team_id, away_team_score');
		$this->db->where('league_id',intval($league_id));
		$this->db->where('scoring_period_id',intval($period_id));
		$query = $this->db->get('fantasy_leagues_games');
		//print($this->db->last_query()."<br />");
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				// RESOLVE TEAM NAMES
				$homeTeamName = resolveTeamName($row->home_team_id);
				$awayTeamName = resolveTeamName($row->away_team_id);
				$home_score = $row->home_team_score;
				$away_score = $row->away_team_score;
				if (sizeof($excludeList) > 0) {
					if (in_array($row->home_team_id, $excludeList)) {
						$home_score = 0;
					}
					if (in_array($row->away_team_id, $excludeList	)) {
						$away_score = 0;
					}
				}
				$games = $games + array($row->id=>array('home_team_id'=>$row->home_team_id, 'home_team_name'=>$homeTeamName, 'home_team_score'=>$home_score,
														'away_team_id'=>$row->away_team_id, 'away_team_name'=>$awayTeamName, 'away_team_score'=>$away_score));
			}
		}
		$query->free_result();
		return $games;
	}
	public function getAvailableScoringPeriods($league_id = false) {
		if ($league_id === false) { $league_id = $this->id; }
		$periods = array();

		$this->db->flush_cache();
		$this->db->select('scoring_period_id');
		$this->db->where('league_id',$league_id);
		$this->db->group_by('scoring_period_id');
		$query = $this->db->get('fantasy_players_scoring');
		if ($query->num_rows() == 0) {
			$league_id = 0;
		}
		$query->free_result();
		$this->db->flush_cache();
		$this->db->select('scoring_period_id');
		$this->db->where('league_id',$league_id);
		$this->db->group_by('scoring_period_id');
		$query = $this->db->get('fantasy_players_scoring');
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				array_push($periods,$row->scoring_period_id);
			}
		}
		$query->free_result();
		asort($periods);
		return $periods;
	}
	public function getAvailableRosterPeriods($league_id = false) {
		if ($league_id === false) { $league_id = $this->id; }
		$periods = array();

		$this->db->flush_cache();
		$this->db->select('scoring_period_id');
		$this->db->where('league_id',$league_id);
		$this->db->group_by('scoring_period_id');
		$query = $this->db->get('fantasy_rosters');
		if ($query->num_rows() == 0) {
			$league_id = 0;
		}
		$query->free_result();
		$this->db->flush_cache();
		$this->db->select('scoring_period_id');
		$this->db->where('league_id',$league_id);
		$this->db->group_by('scoring_period_id');
		$query = $this->db->get('fantasy_rosters');
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				array_push($periods,$row->scoring_period_id);
			}
		}
		$query->free_result();
		asort($periods);
		return $periods;
	}
	public function getAvailableStandingsPeriods($league_id = false) {
		if ($league_id === false) { $league_id = $this->id; }
		$periods = array();
		$this->db->flush_cache();
		$this->db->select('scoring_period_id');
		$this->db->where('league_id',$league_id);
		$this->db->group_by('scoring_period_id');
		$query = $this->db->get('fantasy_teams_record');
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				array_push($periods,$row->scoring_period_id);
			}
		}
		$query->free_result();
		asort($periods);
		return $periods;
	}

	/*--------------------------------------------------------------------------------------------
	/
	/
	/	SIM PROCESSING SUPPORT
	/
	/
	/--------------------------------------------------------------------------------------------*/

	/**
	 *	COPY ROSTERS.
	 *	Copies all rosters for a league from the current scoring period to a new one.
	 *
	 *	@param	$old_scoring_period		{int}		Old Scoring period ID
	 *	@param	$new_scoring_period		{int}		New Scoring period ID
	 *	@param	$league_id				{int}		League ID
	 *	@return							{Boolean}	TRUE on success, FALSE on error
	 *
	 *	@since	1.0
	 *	@access	public
	 *
	 */
	public function copyRosters($old_scoring_period, $new_scoring_period, $league_id = false) {

		if ($league_id === false) { $league_id = $this->id; } // END if

		$this->db->select('*');
		$this->db->from('fantasy_rosters');
		$this->db->where('league_id',$league_id);
		$this->db->where('scoring_period_id',$old_scoring_period);
		$pQuery = $this->db->get();
		if ($pQuery->num_rows() > 0) {
			foreach($pQuery->result() as $row) {
				$data = array('league_id'=>$league_id, 'team_id'=>$row->team_id, 'player_id'=>$row->player_id,
							  'player_position'=>$row->player_position, 'scoring_period_id'=>$new_scoring_period, 'player_role'=>$row->player_role, 'player_status'=>$row->player_status);
				$this->db->insert('fantasy_rosters',$data);
			} // END foreach
		} // END if
		$pQuery->free_result();
		return true;
	}
	/**
	 *	UPDATE LEAGUE SCORING
	 *
	 *	@param	$scoring_period	The scoring period to update against
	 *	@param	$league_id		The league to process, defaults to $this->id if no value passed
	 *	@param	$ootp_league_id	OOTP League ID value, defaults to 100 if no value passed
	 *	@return					Summary String
	 *	@since					1.0
	 *	@version				1.1 (Revised OOTPFL 1.0.4)
	 *
	 */
	public function updateLeagueScoring($scoring_period, $league_id = false, $ootp_league_id = 100) {

		$error = false;
		if ($league_id === false) { $id = $this->id; } // END if

		/*--------------------------------------
		/	1.0 SET BASIC VARS
		/-------------------------------------*/
		$league_name = $this->getLeagueName($league_id);

		$scoring_type = $this->getScoringType($league_id);
		/*--------------------------------------
		/	1.1 LOAD LANGUAGE IF NOT ALREADY EXISTS
		/-------------------------------------*/
		$noteam = $this->lang->line('sim_no_teams');
		if (empty($noteam)) {
			$this->lang->load('admin');
		} // END if
		unset($noteam);

		$summary = str_replace('[LEAGUE_NAME]',$league_name,$this->lang->line('sim_league_processing'));
		/*------------------------------------------------
		/	2.0 PROCEED IF THE LEAGUE IS POPULATED
		/-----------------------------------------------*/
		if ($this->hasTeams($league_id)) {

			/*----------------------------------------------------
			/	2.1	GET TEAM LIST & DETAILS
			/---------------------------------------------------*/
			$teams = $this->getTeamDetails($league_id);

			$summary .= str_replace('[TEAM_COUNT]',sizeof($teams),$this->lang->line('sim_team_count'));

			/*----------------------------------------------------
			/	2.2 VALIDATE ROSTERS AND GENERATE EXCLUSION LISTS
			/---------------------------------------------------*/
			if (!function_exists('getBasicRoster')) {
				$this->load->helper('roster');
			} // END if
			$excludeList = array();
			$valSum = "";
			foreach($teams as $team_id => $teamData) {
				if (!$this->validateRoster(getBasicRoster($team_id, $scoring_period), $league_id )) {
					array_push($excludeList,$team_id);
					$valSum .= str_replace('[LEAGUE_NAME]',$league_name,$this->lang->line('sim_roster_validation_error'));
					$valSum = str_replace('[TEAM_NAME]',$teamData['teamname']." ".$teamData['teamnick'],$valSum);
				} // END if
			} // END foreach
			if (!empty($valSum)) {
				$summary .= $this->lang->line('sim_roster_validation_title').$valSum.$this->lang->line('sim_roster_validation_postfix');
			} // END if

			/*--------------------------------------
			/	2.3 SCORING AND RECORDS
			/-------------------------------------*/
			$scoring_type = $this->getScoringType($league_id);
			$scoring_rules = $this->getScoringRules($league_id,$scoring_type);
			$this->loadPlayerScoring($scoring_period,$league_id, $scoring_rules, $scoring_type);
			$summary .= $this->lang->line('sim_process_h2h');
			// IF RUNNING ON THE FINAL DAY OF THE SIM
			$summary .= $this->updateTeamScoring($scoring_period, $league_id, $excludeList, $scoring_rules, $scoring_type);
			$summary .= $this->updateTeamRecords($scoring_period, $league_id, $excludeList, $scoring_rules, $scoring_type);

			/*--------------------------------------
			/	2.4 ROSTERS
			/-------------------------------------*/
			// COPY CURRENT ROSTERS TO NEXT SCORING PERIOD
			$summary .= $this->lang->line('sim_process_copy_rosters');
			$this->copyRosters($scoring_period['id'], ($scoring_period['id'] + 1), $league_id);

			/*--------------------------------------
			/	2.5 TRADES
			/-------------------------------------*/
			// IF TRADE EXPIRATIONS ARE ENABLED, PROCESS EXPIRING TRADES
			if ((isset($this->params['config']['useTrades']) && $this->params['config']['useTrades'] == 1 && $this->params['config']['tradesExpire'] == 1)) {
				$summary .= $this->lang->line('sim_process_trades');
				$summary .= $this->expireOldTrades($league_id, true, $this->debug);
			} // END if
			// INCREMENT REMAINING OFFERED TRADES  FOR THE SCORING PERIOD TO THE NEXT ONE
			$summary .= $this->lang->line('sim_increment_trades');
			$summary .= $this->incrementTrades($scoring_period['id'], $league_id, 'same', $this->debug);

			/*--------------------------------------
			/	2.6 WAIVERS
			/-------------------------------------*/
			// IF ENABLED, PROCESS WAIVERS
			if ((isset($this->params['config']['useWaivers']) && $this->params['config']['useWaivers'] == 1)) {
				$summary .= $this->lang->line('sim_process_waivers');
				$summary .= $this->processWaivers(($scoring_period['id'] + 1), $league_id, 'same', $this->debug);
			} // END if
		} else {
			$this->errorCode = 1;
			$summary .= $this->lang->line('sim_no_teams');
		} // END if

		/*--------------------------------------
		/	3.0	APPLY SUMMARY AND RETURN
		/-------------------------------------*/
		// APPLY SUMMARY TO RETURN ADMIN CONTROLLER
		if ($this->errorCode == -1) {
			$summary = $this->lang->line('success').$summary;
		} else {
			$summary = $this->lang->line('error').$summary;
		} // END if
		return $summary;
	}
	/**
	 *	LOAD PLAYER SCORING.
	 *	This function loads the compiled scoring results from the players copiled stats tables and stores them to local member
	 *	variables for use in later processing. This uses memory, but saves calling the stats from the DB for each subsequent
	 *	league being processed.
	 *
	 *	This function will also compile head to head points for all active players across all head-to head leagues.
	 *
	 *	@param	$scoring_period	The scoring period to update against
	 *	@param	$league_id		The league to process, defaults to $this->id if no value passed
	 *	@param	$excludeList	A list of team IDs that are excempt from scoring updates due to an illegal roster
	 *	@param	$scoring_rules	Array of scoring categories and values
	 *	@param	$scoring_type	The leagues scoring type (rotisserie or head to head)
	 *	@param	$debug			Trace debugging flag
	 *	@return					Summary String
	 *	@since					1.0.6
	 *	@version				1.0
	 *
	 */
	protected function loadPlayerScoring($scoring_period = false, $league_id = false, $scoring_rules = false, $scoring_type = false, $debug = false) {

		if ($league_id === false) { $league_id = $this->id; }

		if ($scoring_period === false) return false;
		//------------------------------------
		// 	2.2 LOAD COMPILED SCORING STATS
		// 	EXACT STAT CATEGORIES TO LOAD IS
		//	BASED ON LEAGUE SCORING RULES
		//-----------------------------------
		if (sizeof($this->compiledStats) == 0) {
			$this->compiledStats = array('batting'=>array(),'pitching'=>array());
		}
		if (sizeof($this->columnList) == 0) {
			$this->columnList = array('batting'=>array(),'pitching'=>array());
		}
		if (sizeof($this->sqlColNames) == 0) {
			$this->sqlColNames = array('batting'=>array(),'pitching'=>array());
		}
		$playerTypes = array('batting','pitching');
		foreach($playerTypes as $playerType) {
			if (sizeof($this->compiledStats[$playerType]) == 0) {
				$select = "*";
				// GET ALL PLAYERS SCORING, LIMITED TO JUST ROSTER PLAYERS FOR ROTISSERIE
				$player_stats = array();
				$this->db->flush_cache();
				$this->db->select($select);
				$this->db->where("scoring_period_id",intval($scoring_period['id']));
				$query = $this->db->get("fantasy_players_compiled_".$playerType);
				if ($debug) {
					echo($this->db->last_query()."<br />");
				}
				if ($query->num_rows() > 0) {
					$this->columnList[$playerType] = $query->list_fields();
					foreach($query->result() as $row) {
						$this->compiledStats[$playerType][$row->player_id] = $row;
					}
				} // END if
				$query->free_result();
			}
			if ($scoring_type == LEAGUE_SCORING_HEADTOHEAD) {
				foreach($this->compiledStats[$playerType] as $player_id => $statsRow) {
					$totalVal = 0;
					foreach($scoring_rules[$playerType] as $cat => $val) {
						$colName = strtolower(get_ll_cat($cat, true));
						if (isset($statsRow->$colName)) {
							if (isset($score_vals[$cat])) {
								$score_vals[$cat] += $statsRow->$colName;
							} else {
								$score_vals[$cat] = $statsRow->$colName;
							} // END if
							$totalVal += ($statsRow->$colName * $val);
						} // END if
					} // END foreach
					// APPLY VALUES TO THE STATS AND SAVE THEM TO THE PLAYERS SCORING TABLE
					$this->db->flush_cache();
					$this->db->select('id');
					$this->db->where('player_id',$player_id);
					$this->db->where('scoring_period_id',$scoring_period['id']);
					$this->db->where('league_id',$league_id);
					$this->db->where('scoring_type',$scoring_type);
					$tQuery = $this->db->get('fantasy_players_scoring');
					$data = array();
					$data['total'] = $totalVal;
					if ($tQuery->num_rows() == 0) {
						$this->db->flush_cache();
						$data['player_id'] = $player_id;
						$data['scoring_period_id'] = $scoring_period['id'];
						$data['scoring_type'] = $scoring_type;
						$data['league_id'] = $league_id;
						$this->db->insert('fantasy_players_scoring',$data);
					} else {
						$this->db->flush_cache();
						$this->db->where('player_id',$player_id);
						$this->db->where('scoring_period_id',$scoring_period['id']);
						$this->db->where('league_id',$league_id);
						$this->db->where('scoring_type',$scoring_type);
						$this->db->update('fantasy_players_scoring',$data);
					} // END if
					unset($data);
					$tQuery->free_result();
				}
			}
		}
		return true;
	}
	/**
	 *	UPDATE TEAM SCORING
	 *
	 *	@param	$scoring_period	The scoring period to update against
	 *	@param	$league_id		The league to process, defaults to $this->id if no value passed
	 *	@param	$excludeList	A list of team IDs that are excempt from scoring updates due to an illegal roster
	 *	@param	$scoring_rules	Array of scoring categories and values
	 *	@param	$scoring_type	The leagues scoring type (rotisserie or head to head)
	 *	@param	$debug			Trace debugging flag
	 *	@return					Summary String
	 *	@since					1.0.4
	 *	@version				1.0
	 *
	 */
	public function updateTeamScoring($scoring_period, $league_id = false, $excludeList = array(), $scoring_rules = false, $scoring_type = false, $debug = false) {

		if ($league_id === false) { $league_id = $this->id; }

		$summary = $this->lang->line('sim_process_scoring');
		$summary .= "League Scoring Type = ".$scoring_type."<br />";
		/*---------------------------------------
		/
		/	1.0 LOAD TEAMS
		/
		/--------------------------------------*/
		$teams = $this->getTeamIdList($league_id);
		if (sizeof($teams) > 0) {
			$summary .= str_replace('[TEAM_COUNT]',sizeof($teams),$this->lang->line('sim_process_scoring_teams'));
			$summary = str_replace('[LEAGUE_ID]',$league_id,$summary);
			/*---------------------------------------
			/
			/	1.1 TEAM LOOP
			/
			/--------------------------------------*/
			foreach($teams as $team_id) {
				$teamRoster = array();
				$team_score = 0;
				$score_vals = array();
				//echo("Team Id = ".$team_id."<br />");
				$team_stats = array('batting'=>array(),'pitching'=>array());
				//------------------------------------
				// 1.1.1 GET PLAYERS FOR TEAM
				//-----------------------------------
				// DIVIDE ROSTER ARRAY INTO BATTERS AND PITCHERS
				// REQUIRED FOR CONVERTING SCORING RULES TO SCORING RESULTS
				// ONLE GET ROSTERS AND CORES IF THIS TEAM IF IT HAS A VALID ROSTER
				if (sizeof($excludeList) == 0 || (sizeof($excludeList) > 0  && !in_array($team_id, $excludeList))) {
					$this->db->select("player_id,player_position");
					$this->db->where("team_id",intval($team_id));
					$this->db->where("player_status",1);
					$this->db->where("scoring_period_id",intval($scoring_period['id']));
					$query = $this->db->get("fantasy_rosters");
					if ($query->num_rows() > 0) {
						foreach($query->result() as $row) {
							$type = "batting";
							if ($row->player_position == 1) {
								$type = "pitching";
							}
							if (isset($this->compiledStats[$type][$row->player_id])) {
								array_push($team_stats[$type],$this->compiledStats[$type][$row->player_id]);
							}
						} // END foreach
					} // END if
					$query->free_result();
				} // END if sizeof($excludeList)

				$playerTypes = array('batting','pitching');
				foreach($playerTypes as $playerType) {
					//------------------------------------
					// 	2.3 PROCESS STATS VS SCORING RULES
					//-----------------------------------
					foreach($team_stats[$playerType] as $row) {
						//$totalVal = 0;
						switch ($scoring_type) {
							case LEAGUE_SCORING_ROTO:
							case LEAGUE_SCORING_ROTO_5X5:
							case LEAGUE_SCORING_ROTO_PLUS:
								foreach($this->columnList[$playerType] as $field) {
									if (isset($score_vals[$field])) {
										$score_vals[$field] += $row->$field;
									} else {
										$score_vals[$field] = $row->$field;
									} // END if
								} // END foreach
								break;
							case LEAGUE_SCORING_HEADTOHEAD:
								default:
								foreach($scoring_rules[$playerType] as $cat => $val) {
									$colName = strtolower(get_ll_cat($cat, true));
									if (isset($row->$colName)) {
										if (isset($score_vals[$cat])) {
											$score_vals[$cat] += $row->$colName;
										} else {
											$score_vals[$cat] = $row->$colName;
										} // END if
										// UPDATE THE PLAYERS SCORING TOTAL
										$team_score += $row->$colName * $val;
									} // END if
								} // END foreach
								break;
						} // END switch
					} // END foreach $player_stats
				} // END foreach $playerTypes
				//------------------------------------
				// 	2.4 UPDATE TEAM SCORING RESULTS
				//-----------------------------------
				switch ($scoring_type) {
					case LEAGUE_SCORING_ROTO:
					case LEAGUE_SCORING_ROTO_5X5:
					case LEAGUE_SCORING_ROTO_PLUS:
						// EDIT - 1.0.6
						$teamStatObj = new CompiledStats();
						// FIRST LOAD EXISTING STATS, IF THEY EXIST
						$this->db->flush_cache();
						$this->db->select("stats_compiled");
						$this->db->where('team_id',$team_id);
						$use_period = $scoring_period['id'];
						if ($scoring_period['id'] > 1) {
							$use_period = $scoring_period['id'] - 1;
						}
						$this->db->where('scoring_period_id',$use_period);
						$this->db->where('league_id',$league_id);
						$tQuery = $this->db->get('fantasy_teams_scoring');
						$teamStats = NULL;
						if ($tQuery->num_rows() > 0) {
							$row = $tQuery->row();
							$teamStatObj->unserialize($row->stats_compiled);
						}
						$tQuery->free_result();
						// SAVE COMPILED TEAM STATS TO A COMPILED STAT OBJECT FOR LATER USE
						$teamStatObj->updateStats($score_vals);

						// APPLY VALUES TO THE STATS AND SAVE THEM TO THE TEAM SCORING TABLE
						$colCount = 0;
						$team_vals = array();
						if (sizeof($score_vals) > 0) {
							$player_types = array('batting','pitching');
							foreach ($player_types as $type) {
								foreach($scoring_rules[$type] as $cat => $val) {
									$team_vals['value_'.$colCount] = $teamStatObj->getCompiledStats($cat);
									$colCount++;
								} // END foreach
								if ($colCount > 0 && $colCount < 6) { $colCount = 6; }
							} // END foreach
							// SAVE VALUES TO THE DB FOR THIS TEAM
						} else {
							for ($z = 0; $z < 12; $z++) {
								$team_vals['value_'.$z] = 0;
							}
						}
						$team_vals['stats_compiled'] = $teamStatObj->serialize();
						if (sizeof($team_vals) > 0) {
							$this->db->flush_cache();
							$this->db->select('id');
							$this->db->where('team_id',$team_id);
							$this->db->where('scoring_period_id',$scoring_period['id']);
							$this->db->where('league_id',$league_id);
							$tQuery = $this->db->get('fantasy_teams_scoring');
							if ($tQuery->num_rows() == 0) {
								$this->db->flush_cache();
								$team_vals['team_id'] = $team_id;
								$team_vals['scoring_period_id'] = $scoring_period['id'];
								$team_vals['league_id'] = $league_id;
								$this->db->insert('fantasy_teams_scoring',$team_vals);
							} else {
								$this->db->flush_cache();
								$this->db->where('team_id',$team_id);
								$this->db->where('scoring_period_id',$scoring_period['id']);
								$this->db->where('league_id',$league_id);
								$this->db->update('fantasy_teams_scoring',$team_vals);
							} // END if
							$tQuery->free_result();
						} // END if
						break;
					case LEAGUE_SCORING_HEADTOHEAD:
					default:
						// LOOK UP AND UPDATE THE SCORES OF ANY GAMES THIS TEAM IS PLAYING IN
						if ($team_score != 0) {
							$this->db->flush_cache();
							$this->db->select('id, away_team_id, home_team_id');
							$this->db->where('(away_team_id = '.$team_id.' OR home_team_id = '.$team_id.')');
							$this->db->where('scoring_period_id',$scoring_period['id']);
							$this->db->where('league_id',$league_id );
							$query = $this->db->get('fantasy_leagues_games');
							if ($query->num_rows() > 0) {
								foreach($query->result() as $row) {
									$score = array();
									if ($row->away_team_id == $team_id) {
										$col = 'away_team';
									} else {
										$col = 'home_team';
									} // END if
									//echo("update col = ".$col."<br />");
									$score[$col.'_score'] = $team_score;
									$this->db->flush_cache();
									$this->db->where('id',$row->id);
									$this->db->update('fantasy_leagues_games',$score);
									//echo($this->db->last_query()."<br />");
								} // END foreach
							} // END if
							$query->free_result();
						} // END if
						break;
				} // END switch
			} // END foreach teams
		} // END if sizeof($teams) > 0
		return $summary;
	}
	/**
	 *	UPDATE TEAM RECORDS
	 *
	 *	@param	$scoring_period	The scoring period to update against
	 *	@param	$league_id		The league to process, defaults to $this->id if no value passed
	 *	@param	$excludeList	A list of team IDs that are excempt from scoring updates due to an illegal roster
	 *	@param	$scoring_rules	Array of scoring categories and values
	 *	@param	$scoring_type	The leagues scoring type (rotisserie or head to head)
	 *	@param	$debug			Trace debugging flag
	 *	@return					Summary String
	 *	@since					1.0.4
	 *	@version				1.0
	 *
	 */
	public function updateTeamRecords($scoring_period, $league_id = false, $excludeList = array(), $scoring_rules = false, $scoring_type = false, $debug = false) {

		if ($league_id === false) { $league_id = $this->id; }

		$summary = $this->lang->line('sim_process_records');
		// GET ALL TEAMS
		$teams = array();
		$point_max = 0;
		switch ($scoring_type) {
			case LEAGUE_SCORING_ROTO:
			case LEAGUE_SCORING_ROTO_5X5:
			case LEAGUE_SCORING_ROTO_PLUS:
				$fields = array();
				$this->db->select();
				$this->db->where("league_id",$league_id);
				$this->db->where("scoring_period_id",$scoring_period['id']);
				$query = $this->db->get($this->tables['TEAMS_SCORING']);
				if ($query->num_rows() > 0) {
					$fields  = $query->list_fields();
				} // END if
				$point_max = $query->num_rows() - sizeof($excludeList);
				$query->free_result();
				//compile batting stats
				//print("Compiling team standings for league ".$league_id."<br />");
				//print("point_max = ".$point_max."<br />");
				$i = 0;
				$types = array('batting','pitching');
				foreach($types as $type) {
					foreach ($scoring_rules[$type] as $cat => $val) {
						$order = 'desc';
						switch ($cat) {
							case 4:
							case 30:
							case 36:
							case 37:
							case 39:
							case 40:
							case 41:
							case 42:
							case 50:
							case 59:
							case 60:
							case 61:
								$order = 'asc';
								break;
							default:
								break;
						}
						$point_count = $point_max;
						$this->db->select('team_id');
						$this->db->where("value_".$i." <> -1");
						$this->db->where("league_id",$league_id);
						$this->db->order_by("value_".$i, $order);
						$query = $this->db->get($this->tables['TEAMS_SCORING']);
						//print($this->db->last_query()."<br />");
						//print("num results = ".$query->num_rows()."<br />");
						if ($query->num_rows() > 0) {
							foreach ($query->result() as $row) {
								if (isset($teams[$row->team_id])) {
									$teams[$row->team_id] += $point_count;
								} else {
									$teams[$row->team_id] = $point_count;
								} // END if
								// No negative numbers
								if ($point_count > 0)
									$point_count--;
							} // END foreach
						} // END if
						$query->free_result();
						$i++;
					}
					if ($i < 6) { $i = 6; }
				}
				//foreach ($fields as $field) {
					//if (strpos($field,'value_') !== false) {
						// BUILD QUERY TO RANK TEAM IDS BY THIS FIELD
					//} // END if
				//} // END switch
				foreach ($teams as $id => $total) {
					$this->db->flush_cache();
					$this->db->set('total',$total);
					$this->db->where('team_id',$id);
					$this->db->where("league_id",$league_id);
					$this->db->where("scoring_period_id",$scoring_period['id']);
					$this->db->update($this->tables['TEAMS_SCORING']);
					//print($this->db->last_query()."<br />");
				} // END foreach
				break;
			case LEAGUE_SCORING_HEADTOHEAD:
			default:
				$this->db->select("fantasy_teams.id, g, w, l");
				$this->db->join("fantasy_teams_record","fantasy_teams_record.team_id = fantasy_teams.id","left");
				$this->db->where("fantasy_teams.league_id",$league_id);
				$query = $this->db->get($this->tables['TEAMS']);
				if ($query->num_rows() > 0) {
					foreach($query->result() as $row) {
						$games = 0;
						$wins = 0;
						$losses = 0;
						$this->db->flush_cache();
						$this->db->select('id, away_team_id, away_team_score, home_team_id, home_team_score');
						$this->db->where('(away_team_id = '.$row->id.' OR home_team_id = '.$row->id.')');
						$this->db->where('scoring_period_id',$scoring_period['id']);
						$this->db->where('league_id',$league_id );
						$gQuery = $this->db->get('fantasy_leagues_games');
						if ($gQuery->num_rows() > 0) {
								foreach($gQuery->result() as $gRow) {
								if ($gRow->away_team_id == $row->id) {
									$teamScore = $gRow->away_team_score;
									$oppScore = $gRow->home_team_score;
								} else {
									$teamScore = $gRow->home_team_score;
									$oppScore = $gRow->away_team_score;
								}
								if ($teamScore > $oppScore) {
									$wins++;
								} else {
									$losses++;
								}
								$games++;
							}
						}
						$gQuery->free_result();
						$games += $row->g;
						$wins += $row->w;
						$losses += $row->l;
						$perc = 0;
						if ($games > 0) {
							$perc = ($wins/$games);
						}
						$data = array("w"=>$wins,"l"=>$losses,"g"=>$games,'pct'=>$perc);

						$this->db->flush_cache();
						$this->db->select('id');
						$this->db->where('team_id',$row->id);
						$this->db->where("league_id",$league_id);
						$this->db->where("scoring_period_id",$scoring_period['id']);
						$tQuery = $this->db->get('fantasy_teams_record');
						if ($tQuery->num_rows() == 0) {
							$this->db->flush_cache();
							$data['team_id'] = $row->id;
							$data['league_id'] = $league_id;
							$data['scoring_period_id'] = $scoring_period['id'];
							$this->db->insert('fantasy_teams_record',$data);
						} else {
							$this->db->flush_cache();
							$this->db->where('team_id',$row->id);
							$this->db->where("league_id",$league_id);
							$this->db->where("scoring_period_id",$scoring_period['id']);
							$this->db->update('fantasy_teams_record',$data);
						}
					}
				}
				$query->free_result();
				break;
		}
		return $summary;
	}
	/**
	 * 	GET TRADES IN LEAGUE REVIEW.
	 *	Retrieves a list of all trades under league review It retreives trades within their active review period
	 *	and approves all trade that have passed their review period deadline.
	 *	@param	$period_id		{int}		The scoring period to process waivers for.
	 *  @param	$league_id		{int}		If not specified, no league filter is applied.
	 *	@param	$expireDays		{int}		Number of days in the league review period, tested vs the trade response date
	 *	@param	$rosterPeriod	{int}		Whether to apply this function to the period specified in period_id or the next (period_id + 1)
	 *  @param	$debug			{Boolean}	TRUE to enabled tracing, FALSE to disable
	 *	@return					{Boolean}	TRUE on success, FALSE on error
	 *
	 *	@since	1.0.7
     *	@access	public
	 *
	 */
	public function getTradesInLeagueReview($period_id = false, $league_id = false, $expireDays = false, $rosterPeriod = 'same', $debug = false) {

		if ($period_id === false || $expireDays === false) {
			$this->errorCode = 1;
            $this->statusMess = "Period ID or protest expiration days parameters were not received.";
			return false;
		} // END if
		if ($league_id === false) { $league_id = $this->id; } // END if

		$tradeList = array();
        $this->db->select('id, send_players, receive_players, team_1_id, team_2_id, response_date');
        $this->db->where('league_id',$league_id);
        $this->db->where('status',TRADE_PENDING_LEAGUE_APPROVAL);
		$query = $this->db->get($this->tables['TRADES']);
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
				$expireDate = (strtotime($row->response_date) + ((60*60*24) * $expireDays));
				if (time() < $expireDate) {
					array_push($tradeList,$row);
				} else {
					if (!function_exists('processTrade')) { $this->load->helper('roster'); }
					$processResponse = processTrade($row->id, TRADE_APPROVED, $this->lang->line('league_protest_expired_trade_approved'),$league_id);
					if ($processResponse) {
						$offeringTeamOwner = getTeamOwnerId($row->team_1_id);
						$receivingTeamOwner = getTeamOwnerId($row->team_2_id);
						$commishId = $this->getCommissionerId($league_id);

						logTransaction(NULL, NULL, NULL, $row->send_players, $row->receive_players,
									   $commishId, $offeringTeamOwner,false, $period_id,
									   $league_id, $row->team_1_id, $offeringTeamOwner, $row->team_2_id);

						logTransaction(NULL, NULL, NULL, $row->receive_players, $row->send_players,
									   $commishId, $receivingTeamOwner, false, $period_id,
									   $league_id, $row->team_2_id, $offeringTeamOwner, $row->team_1_id);

						$types = array('offering','receiving');
						$tradeTypes = loadSimpleDataList('tradeStatus');
						foreach($types as $type) {
							if ($type == 'offering') {
								$msg = $this->lang->line('league_trade_approved_league_offering_team');
								$msg = str_replace('[ACCEPTING_TEAM_NAME]', getTeamName($row->team_2_id), $msg);
								$msg = str_replace('[USERNAME]', getUsername($offeringTeamOwner), $msg);
								$msg = str_replace('[URL_LINEUP]', anchor('/team/info/'.$row->team_1_id,'adjust your lineup'),$msg);
								$email = getEmail($offeringTeamOwner);
							} else {
								$msg = $this->lang->line('league_trade_approved_league_accepting_team');
								$msg = str_replace('[OFFERING_TEAM_NAME]', getTeamName($row->team_1_id), $msg);
								$msg = str_replace('[USERNAME]', getUsername($receivingTeamOwner), $msg);
								$msg = str_replace('[URL_LINEUP]', anchor('/team/info/'.$row->team_2_id,'adjust your lineup'),$msg);
								$email = getEmail($receivingTeamOwner);
							} // END if
							$msg .= $this->lang->line('email_footer');
							$msg = str_replace('[COMMENTS]', $this->lang->line('league_protest_expired_trade_approved'),$msg);
							$msg = str_replace('[LEAGUE_NAME]', $this->league_model->league_name,$msg);
							$data['messageBody']= $msg;
							//print("email template path = ".$this->config->item('email_templates')."<br />");
							$data['leagueName'] = $this->getLeagueName($league_id);
							$data['title'] = $this->lang->line('league_email_title_trade_response');
							$message = $this->load->view($this->config->item('email_templates').'general_template', $data, true);
							// SEND MESSAGES
							// SEND TO TEAM ONE
							$error = !sendEmail($email,getEmail($this->params['config']['primary_contact']),
							$this->params['config']['site_name']." Administrator",$this->getLeagueName($league_id).' Fantasy League - Trade Update - Offer '.$tradeTypes[TRADE_COMPLETED],
							$message,'','email_trd_');
						} // END foreach
					} // END if
				} // END if
			} // END foreach
        } else {
            $this->errorCode = 2;
            $this->statusMess = "No trades under league review were found.";
        } // END if
        $query->free_result();
        return $tradeList;
	}

	/**
	 *	INCREMENT TRADES.
	 *	Sets all offered trades scoring period to +1.
	 *	@param	$period_id		{int}		The scoring period to process waivers for.
	 *  @param	$league_id		{int}		If not specified, no league filter is applied.
	 *	@param	$rosterPeriod	{int}		Whether to apply this function to the period specified in period_id or the next (period_id + 1)
	 *  @param	$debug			{Boolean}	TRUE to enabled tracing, FALSE to disable
	 *	@return					{Boolean}	TRUE on success, FALSE on error
	 *
	 *	@since	1.0.6
     *	@access	public
	 *
	 */
	public function incrementTrades($period_id = false, $league_id = false, $rosterPeriod = 'same', $debug = false) {

		if ($period_id === false) { return false; }
		if ($league_id === false) { $league_id = $this->id; }

		$error = false;
		$summary = '';
		// COLLECT TRADES THAT ARE IN OFFERED STATE FOR THIS SCORING PERIOD
		$this->db->select($this->tables['TRADES'].".id");
		$this->db->where($this->tables['TRADES'].".league_id",$league_id);
		$this->db->where("(".$this->tables['TRADES'].".status = ".TRADE_OFFERED." OR ".$this->tables['TRADES'].".status =".TRADE_PENDING_COMMISH_APPROVAL
        ." OR ".$this->tables['TRADES'].".status =".TRADE_PENDING_LEAGUE_APPROVAL.")");
		$this->db->where($this->tables['TRADES'].".in_period",$period_id);
		$query = $this->db->get($this->tables['TRADES']);
		if ($debug) {
			print($this->db->last_query()."<br />");
		}
		$summary .= str_replace('[COUNT]',$query->num_rows(),$this->lang->line('sim_process_trades_to_increment_count'));
		if ($query->num_rows() > 0) {
            $trade_id_str = "(";
            foreach ($query->result() as $row) {
                if ($trade_id_str != "(") { $trade_id_str .= ","; }
                $trade_id_str .= $row->id;
            }
            $rowCount = 0;
            $trade_id_str .= ")";
            if ($trade_id_str != "()") {
                // UPDATE THE TRADE
                $this->db->where('id IN '.$trade_id_str);
                $this->db->set('in_period',($period_id+1));
                $this->db->update($this->tables['TRADES']);
                if ($debug) {
                    print($this->db->last_query()."<br />");
                }
                $rowCount = $this->db->affected_rows();
            }
            $dbSummary = str_replace('[COUNT]',$rowCount,$this->lang->line('sim_increment_trades_count'));
            $summary .= str_replace('[PERIOD_ID]',($period_id+1),$dbSummary);
		}
		$query->free_result();
		if ($error) {
			$summary .= $this->lang->line('sim_increment_trades_error');
		}
		return $summary;
	}
	/**
	 *	EXPIRE OLD TRADES.
	 *	Sets all trades that were effective in the specified period_id to <code>EXPIRED</code> status.
	 *	@param	$period_id		{int}		The scoring period to process waivers for.
	 *  @param	$league_id		{int}		If not specified, no league filter is applied.
	 *	@param	$rosterPeriod	{int}		Whether to apply this function to the period specified in period_id or the next (period_id + 1)
	 *  @param	$debug			{Boolean}	TRUE to enabled tracing, FALSE to disable
	 *	@return					{Boolean}	TRUE on success, FALSE on error
	 *
	 *	@since	1.0.6
     *	@access	public
	 *
	 */
	public function expireOldTrades($league_id = false, $processSimExpirations = true, $debug = false) {

		if ($league_id === false) { $league_id = $this->id; } // END if

		$error = false;
		$summary = '';
		// COLLECT TRADES THAT ARE IN OFFERED STATE FOR THIS SCORING PERIOD
		$this->db->select($this->tables['TRADES'].".id, offer_date, status, team_1_id, team_2_id, tradeStatus, in_period, expiration_days");
		$this->db->join($this->tables['TRADES_STATUS'],$this->tables['TRADES_STATUS'].".id = ".$this->tables['TRADES'].".status", "right outer");
		$this->db->where($this->tables['TRADES'].".league_id",$league_id);
		$this->db->where($this->tables['TRADES'].".status",TRADE_OFFERED);
		$this->db->where($this->tables['TRADES'].".expiration_days > -1"); // IGNORE TRADES MARKED AS NON-EXPIRING
		if ($processSimExpirations === false) {
			$this->db->where($this->tables['TRADES'].".expiration_days < 500"); // IGNORE NEXT SIM TRADES FOR CRON-ESQUE CHECKS
		}
		$query = $this->db->get($this->tables['TRADES']);
		if ($debug) {
			print($this->db->last_query()."<br />");
        } // END if
		$summary .= str_replace('[COUNT]',$query->num_rows(),$this->lang->line('sim_process_trades_to_expire_count'));
		if ($query->num_rows() > 0) {

			$ownersByTeam = $this->getDetailedOwnerInfo($league_id);

			$this->lang->load('team');
			$trade_id_str = "(";
			$owner_info = array('receiving'=>array(),'offering' => array());

			foreach ($query->result() as $row) {
                $expireDate = strtotime(EMPTY_DATE_TIME_STR);
                if ($row->expiration_days < 500) {
                    $expireDate = (strtotime($row->offer_date) + ((60*60*24) * $row->expiration_days));
                }
                if ($row->expiration_days == 500 || $expireDate > time()) {
                    if ($trade_id_str != "(") { $trade_id_str .= ","; } // END if
                    $trade_id_str .= $row->id;
                    array_push($owner_info['offering'],array("email"=>$ownersByTeam[$row->team_1_id]['email'],'username'=>$ownersByTeam[$row->team_1_id]['username'],
                                                            "receiving_team"=>$ownersByTeam[$row->team_2_id]['teamname']. " ".$ownersByTeam[$row->team_2_id]['teamnick']));
                    array_push($owner_info['receiving'],array("email"=>$ownersByTeam[$row->team_2_id]['email'],'username'=>$ownersByTeam[$row->team_2_id]['username'],
                                                        "offering_team"=>$ownersByTeam[$row->team_1_id]['teamname']. " ".$ownersByTeam[$row->team_1_id]['teamnick']));
                }
			} // END foreach
			$trade_id_str .= ")";
            if ($trade_id_str != "()") {
                // UPDATE THE TRADE
                $this->db->where('id IN '.$trade_id_str);
                $this->db->set('status',TRADE_EXPIRED);
                $this->db->set('response_date',date('Y-m-d h:m:s',time()));
                if (!empty($comments)) {
                    $this->db->set('response',$this->lang->line('team_trade_auto_expired'));
                } // END if
                $this->db->update($this->tables['TRADES']);
                $summary .= str_replace('[COUNT]',$this->db->affected_rows(),$this->lang->line('sim_process_trades_count'));

                if ($debug) {
                    print($this->db->last_query()."<br />");
                } // END if
                $tradeTypes = loadSimpleDataList('tradeStatus');

                $emailCount = 0;
                foreach ($owner_info as $infoType => $infoData) {

                    foreach ($infoData as $ownerData) {
                        $msg = '';
                        switch ($infoType) {
                            case 'offering':
                                $msg = $this->lang->line('team_trade_expired_offering_team').$this->lang->line('email_footer');
                                $msg = str_replace('[ACCEPTING_TEAM_NAME]', $ownerData['receiving_team'], $msg);
                                break;
                            case 'receiving':
                                $msg = $this->lang->line('team_trade_expired').$this->lang->line('email_footer');
                                $msg = str_replace('[OFFERING_TEAM_NAME]', $ownerData['offering_team'], $msg);
                                break;
                            default:
                                $msg = $this->lang->line('team_trade_expired').$this->lang->line('email_footer');
                                $msg = str_replace('[ACCEPTING_TEAM_NAME]', 'Unknown Team', $msg);
                                $msg = str_replace('[OFFERING_TEAM_NAME]', 'Unknown Team', $msg);
                                break;
                        } // END switch
                        $msg = str_replace('[USERNAME]', $ownerData['username'], $msg);
                        $data['messageBody']= $msg;
                        $data['leagueName'] = $this->league_name;
                        $data['title'] = $this->lang->line('team_email_title_trade_response');
                        $message = $this->load->view($this->config->item('email_templates').'general_template', $data, true);
                        // SEND MESSAGES
                        $error = !sendEmail($ownerData['email'],getEmail($this->params['config']['primary_contact']),
                        $this->params['config']['site_name']." Administrator",$this->league_name.' Fantasy League - Trade Update - Offer '.$tradeTypes[TRADE_EXPIRED],
                        $message,'','email_trd_msg_');
                        $emailCount++;
                    } // END foreach
                } // END foreach

                $summary .= str_replace('[COUNT]',$emailCount,$this->lang->line('sim_process_trades_emails'));
            }
		} // END if
		$query->free_result();
		if ($error) {
			$summary .= $this->lang->line('sim_process_trades_error');
		} // END if
		return $summary;
	}
	/**
	 *	PROCESS WAIVERS.
	 *	Processes pending waiver claims for the specified league and clears waivers for the selected scoring period.
	 *  @param	$period_id		{int}		The scoring period to process waivers for.
	 *  @param	$league_id		{int}		If not specified, no league filter is applied.
	 *  @param	$rosterPeriod	{int}		Whether to apply this function to the period specified in period_id or the next (period_id + 1)
	 *  @param	$debug			{Boolean}	TRUE tO enabled tracing, FALSE to disable
	 *	@return					{Boolean}	TRUE on success, FALSE on error
	 *
	 *	@since	1.0
     *	@access	public
	 */
	public function processWaivers($period_id = false, $league_id = false, $rosterPeriod = 'same', $debug = false) {

		if ($period_id === false) { return; }
		if ($league_id === false) { $league_id = $this->id; }

		$summary = '';
		// GET LEAGUE TEAM ID LIST
		if (!function_exists('getPlayersOnWaivers')) {
			$this->load->helper('roster');
		}
		$playersOnWaivers = getPlayersOnWaivers($period_id, $league_id);
		$claims = $this->getWaiverClaims(-1,0,false,$league_id);
		$waiverOrder = getWaiverOrder($league_id, true);

		//if ($debug) {
			$summary .= "# of players on waivers = ".sizeof($playersOnWaivers)."<br />";
			$summary .= "# of claims by teams = ".sizeof($claims)."<br />";
			$summary .= "waiver order = ".sizeof($waiverOrder)."<br />";
		//}

		foreach($playersOnWaivers as $player) {
			// SEE IF THERE IS A WAIVER CLAIM FOR THIS PLAYER
			$numClaims = 0;
			$claimList = array();
			$claimCount = 1;
			foreach($claims as $claim) {

				$summary .= "claim ".$claimCount." team = ".$claim['team_id'].", player = ".$claim['player_id']."<br />";

				if ($claim['player_id'] == $player['player_id']) {
					// CLAIMS FOUND
					$numClaims++;
					array_push($claimList, $claim['team_id']);
				}
			} // END foreach
			$summary .= "current player = ".$player['player_id']."<br />";
			$summary .= "# of claims for player ".$player['player_id']." = ".$numClaims."<br />";
			if ($numClaims > 0) {
				$index = 0;
				foreach($waiverOrder as $team_id) {
					if (in_array($team_id, $claimList)) {
						//if ($debug) {
							$summary .= "claim found for player ".$player['player_id']." by team = ".$team_id."<br />";
						//}

						// CLAIM THIS PLAYER FOR TEAM
						$this->db->set('team_id',$team_id);
						$this->db->set('player_id',$player['player_id']);
						$this->db->set('league_id',$league_id);
						if ($player['position'] == 7 || $player['position'] == 8 || $player['position'] == 9) { $player['position'] = 20; }
						if ($player['role'] == 13) { $player['role'] = 12; }
						$this->db->set('player_position',$player['position']);
						$this->db->set('player_role',$player['role']);
						if ($rosterPeriod != 'same') {
							$period_id--;
						} // END if
						$this->db->set('scoring_period_id',$period_id);
						$this->db->insert('fantasy_rosters');

						if (!function_exists('updateOwnership')) {
							$this->load->helper('roster');
						} // END if
						$ownership = updateOwnership($player['player_id']);
						$pData = array('own'=>$ownership[0],'start'=>$ownership[1]);
						$this->db->flush_cache();
						$this->db->where('id',$claim['player_id']);
						$this->db->update('fantasy_players',$pData);

						// LOG THE TRANSACTION
						logTransaction(NULL, NULL, array($player['player_id']),NULL, NULL, -1,1, false,$period_id,$league_id,$team_id,-1);

						// REMOVE TEAM FROM WAIVER ORDER ARRAY AND PUT IT AT THE END
						$waiveTeam = array_splice($waiverOrder,$index, 1);
						array_push($waiverOrder,$waiveTeam[0]);
						break;
					} // END if
					$index++;
				} // END foreach
				// REMOVE ALL WAIVER CLAIMS FOR THIS PLAYER
				if (!$debug) {
					$this->db->where('player_id',$player['player_id']);
					$this->db->where('league_id',$league_id);
					$this->db->delete($this->tables['WAIVER_CLAIMS']);
				} // END if
				$summary .= "cleared = ".$this->db->affected_rows()."  of ".$numClaims." waiver claims for this player<br />";
			} // END if
			// REMOVE PLAYER FROM WAIVERS
			if (!$debug) {$this->db->where('player_id',$player['player_id']);
				$this->db->where('league_id',$league_id);
				$this->db->where('waiver_period',$period_id);
				$this->db->delete($this->tables['WAIVERS']);
			} // END if
			$summary .= "cleared = ".$this->db->affected_rows()." of ".$numClaims." waiver records for player ".$player['player_id']."<br />";
		}
		// UPDATE THE WAIVER ORDER OF THE TEAMS IN THE LEAGUE
		$waiverList = array();
		$rank = 1;
		$summary .= "New waiver order:<br />";
		foreach($waiverOrder as $waiveTeam) {
			$this->db->set('waiver_rank',$rank);
			$this->db->where('id',$waiveTeam);
			$this->db->update($this->tables['TEAMS']);
			//if ($debug) {
				$summary .= $rank." = ".$waiveTeam.'<br />';
			//}
			$rank++;
		}
		return $summary;
	}
	/**
	 *	DENY WAIVER CLAIM.
	 *	Called when a league commissioner denies a wa9iver claim.
	 *
	 *	@param		$claim_id	(Integer)	The waiver claim ID. Function returns false if not passed.
	 *	@return					{Array}		Claim object, false on failure
	 *
	 *	@since	1.0.5
	 *	@access	public
	 */
	public function denyWaiverClaim($claim_id = false) {
		if ($claim_id === false) { return false; }
		$claim = false;
		$this->db->select($this->tables['WAIVER_CLAIMS'].".id, ".$this->tables['WAIVER_CLAIMS'].".team_id, teamname, teamnick, ".$this->tables['WAIVER_CLAIMS'].".player_id, first_name, last_name, waiver_period");
		$this->db->join("fantasy_teams","fantasy_teams.id = ".$this->tables['WAIVER_CLAIMS'].".team_id", "left");
		$this->db->join("fantasy_players","fantasy_players.id = ".$this->tables['WAIVER_CLAIMS'].".player_id", "left");
		$this->db->join("fantasy_players_waivers","fantasy_players_waivers.player_id = fantasy_players.id", "right outer");
		$this->db->join("players","fantasy_players.player_id = players.player_id", "right outer");
		$this->db->where($this->tables['WAIVER_CLAIMS'].".id",$claim_id);
		$query = $this->db->get($this->tables['WAIVER_CLAIMS']);
		if ($query->num_rows() > 0) {
			$row = $query->row();
			$claim = array('id'=>$row->id,'team_id'=>$row->team_id, 'teamname'=>$row->teamname." ".$row->teamnick,
										 'player_id'=>$row->player_id, 'player_name'=>$row->first_name." ".$row->last_name,
										 'waiver_period'=>$row->waiver_period);
			$query->free_result();
			$this->db->where("id",$claim_id);
			$this->db->delete($this->tables['WAIVER_CLAIMS']);
		}
		$query->free_result();
		unset($query);
		return $claim;
	}

	/*------------------------------------------------------------------
	/
	/	PRIVATE/PROTECTED FUNCTIONS
	/
	/-----------------------------------------------------------------*/
	protected function deleteLeagueData($table = false, $league_id = false) {

		if ($table === false) { return false; }
		if ($league_id === false) { $league_id = $this->id; }

		$this->db->where('league_id',$league_id);
		$this->db->delete($table);

		return true;
	}
	/*---------------------------------------
	/	DEPRECATED FUNCTIONS
	/--------------------------------------*/
	/**
	 * 	AUTO DRAFT.
	 *  Runs automatic roster drafts for a given league.
	 *
	 *	@param	$max_rounds 	Maximum number of rounds to run
	 *	@param	$curr_year		The current league year
	 *	@param	$league_id		The fatntasy league ID, defaults to $id property if nothing is passed
	 *	@return					Error string or "OK" on success
	 *	@deprecated
	 */
	public function _auto_draft($max_rounds,$curr_year, $league_id = false) {
		$errors = "";
		if ($league_id === false) { $league_id = $this->id; }

		// GET ALL TEAMS
		$teams = array();
		$this->db->select("id");
		$this->db->where("league_id",$league_id);
		$query = $this->db->get($this->tables['TEAMS']);
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				array_push($teams,$row->id);
				$this->db->where("team_id",$row->id);
				$this->db->delete("fantasy_rosters");
				//echo("Roster rows deleted = ".$this->db->affected_rows()."<br />");
			}
		}

		$last_year = date('Y',strtotime($curr_year)-(60*60*24*365));
		$query->free_result();
		$pos_batters = array(2,3,4,5,6,7,8,9,25);
		for ($i = 0; $i < sizeof($pos_batters); $i++) {
			$batters = array();
			if ($pos_batters[$i] == 25) {
				$sqlPos = $pos_batters[rand(0,(sizeof($pos_batters)-2))];
				$pickedBatters = array();
				$this->db->select("player_id");
				$this->db->where('league_id',$league_id);
				$query = $this->db->get("fantasy_rosters");
				if ($query->num_rows() > 0) {
					foreach($query->result() as $row) {
						array_push($pickedBatters,$row->player_id);
					}
				}
				$query->free_result();
			} else {
				$sqlPos = $pos_batters[$i];
			}
			$this->db->select("fantasy_players.id");
			$this->db->join("players_career_batting_stats",'players_career_batting_stats.player_id = fantasy_players.player_id','left');
			$this->db->join("players",'players.player_id = fantasy_players.player_id','left');
			$this->db->where("(fantasy_players.player_status = 1 OR fantasy_players.player_status = 3)");
			$this->db->where('players.position',$sqlPos);
			$this->db->where('players.retired',0);
			$this->db->where('players_career_batting_stats.year',$last_year);
			if(isset($pickedBatters) && !empty($pickedBatters)) $this->db->where_not_in('fantasy_players.id',$pickedBatters);
			$this->db->order_by("players_career_batting_stats.war",'desc');
			$query = $this->db->get("fantasy_players");

			//echo("sql = ".$this->db->last_query()."<br />");
			if ($query->num_rows() > 0) {
				$count = 0;
				foreach($query->result() as $row) {
					array_push($batters,$row->id);
					//echo("batter id = ".$row->id."<br />");
					$count++;
					if ($count >= sizeof($teams)) break;
				}
				shuffle($batters);
				foreach($teams as $team_id) {
					if ($pos_batters[$i] == 7 || $pos_batters[$i] == 8 || $pos_batters[$i] == 9) {
						$pos = 20;
					} else {
						$pos = $pos_batters[$i];
					}
					$data = array('player_id'=>$batters[0],'league_id'=>$league_id,'team_id'=>$team_id,'scoring_period_id'=>1,'player_position'=>$pos,
								  'player_role'=>-1,'player_status'=>1);
					$this->db->insert('fantasy_rosters',$data);
					array_shift($batters);
				}
			}
		}
		$pos_pitchers = array(11=>5,12=>2);
		foreach ($pos_pitchers as $pos => $draftCount) {
			$pitchers = array();
			$this->db->select("fantasy_players.id");
			$this->db->join("players_career_pitching_stats",'players_career_pitching_stats.player_id = fantasy_players.player_id','left');
			$this->db->join("players",'players.player_id = fantasy_players.player_id','left');
			$this->db->where("fantasy_players.player_status",1);
			$this->db->where('players.position',1);
			if ($pos == 12) {
				$this->db->where('players.role',13);
			} else {
				$this->db->where('players.role',$pos);
			}
			$this->db->where('players.retired',0);
			$this->db->where('players_career_pitching_stats.year',$last_year);
			$this->db->order_by("players_career_pitching_stats.war",'desc');
			$query = $this->db->get("fantasy_players");
			//echo("last query = ".$this->db->last_query()."<br />");
			if ($query->num_rows() > 0) {
				$count = 0;
				foreach($query->result() as $row) {
					array_push($pitchers,$row->id);
					$count++;
					if ($count >= (sizeof($teams)*$draftCount)) break;
				}
				shuffle($pitchers);
				for ($i = 0; $i < $draftCount; $i++) {
					foreach($teams as $team_id) {
						$data = array('player_id'=>$pitchers[0],'league_id'=>$league_id,'team_id'=>$team_id,'scoring_period_id'=>1,'player_position'=>1,
									  'player_role'=>$pos,'player_status'=>1);
						$this->db->insert('fantasy_rosters',$data);
						array_shift($pitchers);
					}
				}
			}
		}
		if (empty($errors)) $errors = "OK"; else  $errors = $errors;
		return $errors;
	}
	/**
	 * 	UPDATE LEAGUE SCORING
	 *  Runs scoring against each leagues scoring rules for all players.
	 *
	 *	@param	$scoring_period The scoring period to compile
	 *	@param	$league_id		The fatntasy league ID, defaults to $id property if nothing is passed
	 *	@param	$ootp_league_id	The OOTP League ID to run stats from
	 *	@return	TRUE on success, FALSE on ERROR
	 *	@deprecated
	 */
	private function _updateLeagueScoring($scoring_period, $excludeList = array(), $league_id = false) {

		if ($league_id === false) { $league_id = $this->id; }

		// LOAD RELEVANT SCORING CATEGORIES
		$rules = $this->getScoringRules($league_id);

		if (isset($rules) && sizeof($rules) > 0) {

			// UPDATE SCORING FOR ALL PLAYERS FOR THIS PERIOD
			$player_list = array();
			$this->db->flush_cache();
			$this->db->select("fantasy_players.id, fantasy_players.player_id, position, role, player_status");
			$this->db->join("players","players.player_id = fantasy_players.player_id","left");
			$this->db->where("player_status",1);
			$query = $this->db->get("fantasy_players");
			//echo($this->db->last_query()."<br />");
			if ($query->num_rows() > 0) {
				//echo("Number of players found = ".$query->num_rows()."<br />");
				foreach($query->result() as $row) {
					// BUILD QUERY TO PULL CURRENT GAME DATA FOR THIS PLAYER
					if ($row->position != 1) {
						$type = "batting";
						$table = "players_game_batting";
					} else {
						$type = "pitching";
						$table = "players_game_pitching_stats";
					}
					$select = "";
					foreach($rules[$type] as $cat => $val) {
						if ($select != '') { $select.=","; }
						$select .= strtolower(get_ll_cat($cat, true));
					}
					$this->db->flush_cache();
					$this->db->select($select);
					$this->db->join($table,'games.game_id = '.$table.'.game_id','left');
					$this->db->where($table.'.player_id',$row->player_id);
					$this->db->where("DATEDIFF('".$scoring_period['date_start']."',games.date)<=",0);
					$this->db->where("DATEDIFF('".$scoring_period['date_end']."',games.date)>=",0);
					$gQuery = $this->db->get('games');
					//echo("Num of games found for player ".$row->player_id." = ".$gQuery->num_rows() .", status = ".$row->player_status."	<br/>");
					//echo($this->db->last_query()."<br />");
					if ($gQuery->num_rows() > 0) {

						$score_vals = array();
						$totalVal = 0;
						foreach ($gQuery->result() as $sRow) {
							$colCount = 0;
							// APPLY VALUES TO THE STATS AND SAVE THEM TO THE SCORING TABLE
							foreach($rules[$type] as $cat => $val) {
								$fVal = 0;
								$colName = strtolower(get_ll_cat($cat, true));
								if (isset($score_vals['value_'.$colCount])) {
									$score_vals['value_'.$colCount] += $sRow->$colName;
								} else {
									$score_vals['value_'.$colCount] = $sRow->$colName;
								}
								if ($sRow->$colName != 0) {
									$totalVal += $sRow->$colName * $val;
								}
								$colCount++;
							}
						}
						$score_vals['total'] = $totalVal;
						//echo("Player ".$row->player_id." total = ".$totalVal.", status = ".$row->player_status."	<br/>");
						//if ($row->player_status == 1) { $team_score += $totalVal; }
						//echo("Team ".$team_id." total = ".$team_score."<br/>");
						if (sizeof($score_vals) > 0) {
							$this->db->flush_cache();
							$this->db->select('id');
							$this->db->where('player_id',$row->id);
							$this->db->where('scoring_period_id',$scoring_period['id']);
							$this->db->where('league_id',$rules['league_id']);
							$tQuery = $this->db->get('fantasy_players_scoring');
							if ($tQuery->num_rows() == 0) {
								$this->db->flush_cache();
								$score_vals['player_id'] = $row->id;
								$score_vals['scoring_period_id'] = $scoring_period['id'];
								$score_vals['league_id'] = $rules['league_id'];
								$this->db->insert('fantasy_players_scoring',$score_vals);
							} else {
								$this->db->flush_cache();
								$this->db->where('player_id',$row->id);
								$this->db->where('scoring_period_id',$scoring_period['id']);
								$this->db->where('league_id',$rules['league_id']);
								$this->db->update('fantasy_players_scoring',$score_vals);
							}
							$tQuery->free_result();
						}
					}
					$gQuery->free_result();
				}
			}
		}
		// GET ALL TEAMS
		$teams = array();
		$this->db->select("id");
		$this->db->where("league_id",$league_id);
		$query = $this->db->get($this->tables['TEAMS']);
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				array_push($teams,$row->id);
			}
		}
		$query->free_result();
		foreach($teams as $team_id) {
			//echo("Team Id = ".$team_id."<br />");

			// GET PLAYERS FOR TEAM
			$teamRoster = array();
			$team_score = 0;
			// ONLY GET ROSTERS AND CORES IF THIS TEAM IF IT HAS VALID ROSTERS
			if (sizeof($excludeList) == 0 || (sizeof($excludeList) > 0  && !in_array($team_id, $excludeList))) {
				$this->db->select("player_id");
				$this->db->where("team_id",intval($team_id));
				$this->db->where("player_status",1);
				$this->db->where("scoring_period_id",intval($scoring_period['id']));
				$query = $this->db->get("fantasy_rosters");
				if ($query->num_rows() > 0) {
					foreach($query->result() as $row) {
						array_push($teamRoster,$row->player_id);
					}
				}
				$query->free_result();

				$this->db->flush_cache();
				$this->db->distinct();
				$this->db->where_in("player_id",$teamRoster);
				$this->db->where("fantasy_players_scoring.scoring_period_id",intval($scoring_period['id']));
				$query = $this->db->get("fantasy_players_scoring");
				//echo($this->db->last_query()."<br />");
					if ($query->num_rows() > 0) {
					foreach($query->result() as $row) {
						$team_score += $row->total;
					}
				}
				$query->free_result();
			}
			// LOOK UP AND UPDATE THE SCORES OF ANY GAMES THIS TEAM IS PLAYING IN
			$this->db->flush_cache();
			$this->db->select('id, away_team_id, home_team_id');
			$this->db->where('(away_team_id = '.$team_id.' OR home_team_id = '.$team_id.')');
			$this->db->where('scoring_period_id',$scoring_period['id']);
			$this->db->where('league_id',$league_id );
			$query = $this->db->get('fantasy_leagues_games');
			//echo($this->db->last_query()."<br />");
			//echo("scoring period id = ".$scoring_period['id']."<br />");
			//echo("Number of games found for team ".$team_id." = ".$query->num_rows()."<br />");
			if ($query->num_rows() > 0) {
				foreach($query->result() as $row) {
					$score = array();
					if ($row->away_team_id == $team_id) {
						$col = 'away_team';
					} else {
						$col = 'home_team';
					}
					//echo("update col = ".$col."<br />");
					$score[$col.'_score'] = $team_score;
					$this->db->flush_cache();
					$this->db->where('id',$row->id);
					$this->db->update('fantasy_leagues_games',$score);
					//echo($this->db->last_query()."<br />");
				}
			}
			$query->free_result();
		}
		return false;
	}
}
