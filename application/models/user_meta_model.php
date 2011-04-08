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
	
	/*--------------------------------------
	/	C'TOR
	/	Creates a new instance of user_meta_model
	/-------------------------------------*/
	function user_meta_model() {
		parent::__construct();

		$this->tblName = $this->tables['users_meta'];

		$this->fieldList = array('firstName', 'lastName', 'nickName', 'city', 'state', 'country', 'zipCode', 'title', 'bio', 'gender');
		$this->conditionList = array('birthDay','birthMonth','birthYear','avatarFile');
		$this->readOnlyList = array('userId','dateOfBirth', 'avatar', 'custom');  
		$this->uniqueField = 'userId';
		$this->joinCode = "M";
		
		$this->columns_select = array('id','userId','firstName','lastName','nickName','dateOfBirth','gender','country');
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
	public function getUserLeagueCount($userId = false) {
		
		if ($userId === false) $userId = $this->userId;
		
		$teamList = array();
		$this->db->select('id');
		$this->db->from('fantasy_leagues');
		$this->db->where('commissioner_id', $userId);
		return $this->db->count_all_results();
	}
	
	public function getUserTeams($league_id = false, $userId = false) {
		
		if ($userId === false) $userId = $this->userId;
		
		$teamList = array();
		$this->db->select('fantasy_teams.id, teamname, teamnick, fantasy_teams.avatar, fantasy_teams.league_id, league_name, league_type, commissioner_id,w,l,pct,gb,fantasy_teams_scoring.total');
		$this->db->join('fantasy_leagues','fantasy_leagues.id = fantasy_teams.league_id', 'left');
		$this->db->join('fantasy_teams_record','fantasy_teams_record.team_id = fantasy_teams.id', 'left');
		$this->db->join('fantasy_teams_scoring','fantasy_teams_scoring.team_id = fantasy_teams.id', 'left');
		if ($league_id !== false) {
			$this->db->where('fantasy_teams.league_id', $league_id);
		}
		$this->db->where('owner_id', $userId);
		$query = $this->db->get('fantasy_teams');
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
	
	public function getUserTeamIds($league_id = false, $userId = false) {
		
		if ($userId === false) $userId = $this->userId;
		$teamIds = array();
		
		$teams = $this->getUserTeams($league_id,$userId);
		if (sizeof($teams) > 0) {
			foreach($teams as $row) {
				//echo("Team id = ".$row['id']."<br />");
				array_push($teamIds,$row['id']);
			}
		}
		return $teamIds;
	}
	
	public function getUserDrafts() {
		
		$draftList = array();
		$this->db->select('league_id');
		$this->db->where('owner_id', $this->userId);
		$query = $this->db->get('fantasy_teams');
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
					$this->db->select('fantasy_draft.*, teamname, teamnick');
					$this->db->join('fantasy_teams','fantasy_teams.id = fantasy_draft.team_id','left');
					$this->db->where('fantasy_draft.league_id', $row->league_id);
					$this->db->order_by('pick_overall');
					$query3 = $this->db->get('fantasy_draft');
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
