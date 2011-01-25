<?php
/**
 *	Admin Access.
 *	The primary controller for the Admin Section.
 *	@author			Jeff Fox
 *	@dateCreated	11/13/09
 *	@lastModified	08/07/10
 *
 */
class players extends MY_Controller {

	/*--------------------------------
	/	VARIABLES
	/-------------------------------*/
	/**
	 *	SLUG.
	 *	@var $_NAME:Text
	 */
	var $_NAME = 'players';
	/*--------------------------------
	/	C'TOR
	/-------------------------------*/
	/**
	 *	Creates a new instance of players.
	 */
	public function players() { 
		parent::MY_Controller();
		$this->views['MESSAGE'] = 'player/player_message';
		$this->views['INFO'] = 'player/player_info';
		$this->views['STATS'] = 'player/player_stats';
		$this->enqueScript('sorttable.js');
	}
	/*--------------------------------
	/	PUBLIC FUNCTIONS
	/-------------------------------*/
	/**
	 *	INDEX.
	 *	The default handler when the controller is called.
	 *	Checks for an existing auth session, and if found,
	 *	redirects to the dashboard. Otherwise, it redirects 
	 *	to the login.
	 */
	public function index() {
		$this->params['content'] = $this->load->view($this->views['PENDING'], $this->data, true);
	    $this->displayView();
	}
	
	public function stats() {
		
		$this->enqueScript(JS_JQUERY);
		$this->getURIData();
		$this->load->model('player_model','dataModel');
		
		//-----------------------------------------------------------------------
		// UPDATE 1.0.3
		// TEST TO ASSURE PLAYERS HAVE BEEN IMPORTED BEFORE DISPLAYING THE PAGE
		//-----------------------------------------------------------------------
		if ($this->dataModel->getPlayerCount() > 0) {
			$this->data['years'] = $this->ootp_league_model->getAllSeasons();
			if (isset($this->uriVars['year'])) {
				$this->data['lgyear'] = $this->uriVars['year'];
			} else {
				$currDate = strtotime($this->ootp_league_model->current_date);
				$startDate = strtotime($this->ootp_league_model->start_date);
				if ($currDate <= $startDate) {
					$this->data['lgyear'] = (intval($this->data['years'][0]));
				} else {
					$this->data['lgyear'] = date('Y',$currDate);
				}
			}
			//echo("League year = ".$this->data['lgyear']."<br />");
			$player_type = 1;
			if(isset($this->uriVars['player_type'])) $player_type = $this->uriVars['player_type'];
			$this->data['player_type'] = $player_type;
			
			$roster_status = -1;
			if(isset($this->uriVars['roster_status'])) $roster_status = $this->uriVars['roster_status'];
			$this->data['roster_status'] = $roster_status;
			
			$position_type = -1;
			if(isset($this->uriVars['position_type'])) $position_type = $this->uriVars['position_type'];
			$this->data['position_type'] = $position_type;
			
			$this->data['role_type'] = $role_type = (isset($this->uriVars['role_type'])) ? $this->uriVars['role_type'] : -1;	
			
			$this->data['limit'] = $limit = (isset($this->uriVars['limit'])) ? $this->uriVars['limit'] : -1;
			
			$this->data['pageId'] = $pageId = (isset($this->uriVars['pageId'])) ? $this->uriVars['pageId'] : 1;	
			
			$startIdx = 0;
			if ($limit != -1) {
				$startIdx = ($limit * ($pageId - 1))-1;
			}
			if ($startIdx < 0) { $startIdx = 0; }
			$this->data['startIdx'] = $startIdx;
			
			$league_id = -1;
			if(isset($this->uriVars['league_id'])) {
				$league_id = $this->uriVars['league_id'];
			} else {
				$league_id = $this->session->userdata('league_id');
				if (!isset($league_id)) {
					$league_id = -1;
				}
			}
			$this->data['team_list'] = array();
			$this->data['scoring_rules'] = $rules = $this->league_model->getScoringRules(0);
			if ($league_id != -1) {
				$this->data['team_list'] = $this->league_model->getTeamDetails($league_id);
				if ($this->params['loggedIn']) {
					$this->data['userTeamId'] = $this->user_meta_model->getUserTeamIds($league_id,$this->params['currUser']);
				}
				$rules = $this->league_model->getScoringRules($league_id);
				if (sizeof($rules) == 0) {
					$rules = $this->league_model->getScoringRules(0);
				}
			}
			$this->data['league_id'] = $league_id;
			
			// RAW Record Count total with no limit
			$this->data['recCount'] = sizeof($this->dataModel->getFantasyStats(true,$this->params['config']['ootp_league_id'], $this->data['lgyear'], $this->data['player_type'], $this->data['position_type'],  $this->data['role_type'], $this->params['config']['current_period'], $this->data['roster_status'], -1, 0, $league_id, $this->params['config']['current_period'],$rules));
			// Actual results with limit applied
			$player_stats = $this->dataModel->getFantasyStats(false,$this->params['config']['ootp_league_id'], $this->data['lgyear'], $this->data['player_type'], $this->data['position_type'],  $this->data['role_type'], $this->params['config']['current_period'], $this->data['roster_status'], $limit, $startIdx, $league_id, $this->params['config']['current_period'],$rules);
			$this->data['pageCount'] = 1;
			if ($limit != -1) {
				$this->data['pageCount'] = intval($this->data['recCount'] / $limit);
			}
			if ($this->data['pageCount'] < 1) { $this->data['pageCount'] = 1; }
			if ($player_type == 1) {
				$this->data['title'] = $this->data['lgyear']." "."Batting";
			} else {
				$this->data['title'] = $this->data['lgyear']." "."Pitching";
			}
			$this->data['colnames']=player_stat_column_headers($player_type, QUERY_STANDARD, true);
			$this->data['fields']=player_stat_fields_list($player_type, QUERY_STANDARD, true);
			
			if (!function_exists('getSignedPlayerTeamIdsByLeague')) {
				$this->load->helper('roster');
			}
			$this->data['player_teams'] = getSignedPlayerTeamIdsByLeague($league_id, $this->params['config']['current_period']);
			
			$this->data['player_stats'] = formatStatsForDisplay($player_stats, $this->data['fields'], $this->params['config'],$league_id,$this->data['player_teams'],$this->data['team_list']);
			$this->data['formatted_stats'] = $this->load->view($this->views['STATS_TABLE'], $this->data, true);

			$this->params['subTitle'] = $this->data['subTitle'] = "Player Stats";
			$this->params['pageType'] = PAGE_FORM;
			$this->params['content'] = $this->load->view($this->views['STATS'], $this->data, true);
		} else {
			$this->params['subTitle'] = $this->data['subTitle'] = "Player Stats Error";
			$this->data['theContent'] = $this->lang->line('players_stats_no_players_error');
			$this->params['content'] = $this->load->view($this->views['MESSAGE'], $this->data, true);	
		}
	   	$this->makeNav();
		
		$this->displayView();
	}
	
	public function getInfo() {
		$this->init();
		$this->getURIData();
		$status = "";
		$code = 0;
		$result = "";
		if (isset($this->uriVars['player_id'])) {
			$result = $this->loadPlayerInfo($this->uriVars['player_id'], true);
			if (!empty($result)) {
				$status .= "OK";
				$code = 200;
				$result =  '{ items: ['.$result.']}';
			} else {
				$status .= "error:No Player information was returned";
				$code = 201;
				$result = '""';
			}
		} else {
			$status .= "error:Player Identifier Missing";
			$code = 203;
			$result = '""';
		}
		$result = '{ result: '.$result.',code:"'.$code.'",status: "'.$status.'"}';
		$this->output->set_header('Content-type: application/json'); 
		$this->output->set_output($result);
	}
	public function loadPlayerInfo($player_id = false, $return = false) {
		
		$status = "";
		$code = 0;
		if (!isset($this->player_model)) {
			$this->load->model('player_model');
		}
		$data = $this->player_model->getPlayerDetails($player_id);
		$result = "";
		if (sizeof($data) > 0) {
			$result .= '{"id":"'.$data['id'].'","player_id":"'.$data['player_id'].'","player_name":"'.$data['first_name'].' '.$data['last_name'].'","team_id":"'.$data['team_id'].'","team_name":"'.$data['team_name'].' '.$data['teamNickname'].'","pos":"'.makeElidgibilityString($data['positions']).'","position":"'.get_pos($data['position']).'","role":"'.get_pos($data['role']).'"}';
		}
		return $result;
	}
	
	public function info() {
		$this->getURIData();
		$this->load->model('player_model','dataModel');
		
		$pid = false;
		if (isset($this->uriVars['player_id']) && !empty($this->uriVars['player_id'])) {
			$pid = $this->uriVars['player_id'];
		} else if (isset($this->uriVars['id']) && !empty($this->uriVars['id'])) {
			$pid = $this->uriVars['id'];
		}
		if (!$pid === false && $pid != -1) {
			$this->dataModel->load($pid);
			
			$this->data['teamList'] = getOOTPTeams($this->params['config']['ootp_league_id'], false);
			$this->data['thisItem'] = $this->dataModel->getPlayerDetails($pid);
			
			$this->load->model('news_model');
			$this->data['playerNews'] = $this->news_model->getNewsByParams(NEWS_PLAYER,$pid,1);	
			
			if (!function_exists('getScoringPeriodCount')) {
				$this->load->helper('admin');
			}
			$this->data['scoringPeriods'] = getScoringPeriodCount();
			$this->data['currentScoringPeriod'] = getCurrentScoringPeriod($this->ootp_league_model->current_date);
			$this->data['playerPoints'] = $this->dataModel->getPlayerScoring(0,$pid);
			$this->data['pointsMax'] = $this->dataModel->getHighestScoring(0,$pid);
			
			$yearList = $this->ootp_league_model->getAllSeasons();
			$this->data['statYear'] = date('Y',strtotime($this->ootp_league_model->current_date));
			$this->data['playerStats'] = $this->dataModel->getCurrentStats($this->params['config']['ootp_league_id'],$this->data['statYear']);
			
			$this->data['awards'] = $this->dataModel->getPlayerAwards($this->params['config']['ootp_league_id']);
			$this->data['awardName'] = getLeagueAwardsNames($this->params['config']['ootp_league_id']);

			$this->data['recentGames'] = $this->dataModel->getRecentGameStats($this->params['config']['ootp_league_id'],$this->ootp_league_model->current_date,date('Y',strtotime($this->ootp_league_model->current_date)));
			$this->data['upcomingGames'] = getPlayerSchedules(array(0=>array($pid=>array('team_id'=>$this->data['thisItem']['team_id'],'position'=>$this->data['thisItem']['position'],'role'=>$this->data['thisItem']['role']))),$this->ootp_league_model->current_date, $this->params['config']['sim_length']);
			
			$this->data['careerStats'] = $this->dataModel->getCareerStats($this->params['config']['ootp_league_id']);
			$this->data['teams'] = getOOTPTeamAbbrs($this->params['config']['ootp_league_id'],date('Y',strtotime($this->ootp_league_model->current_date)));
			$this->data['year'] = date('Y',strtotime($this->ootp_league_model->current_date));
			
			// LEAGUE SPECIFIC INFO IF LEAGUE_ID EXISTS
			$league_id = -1;
			if(isset($this->uriVars['league_id'])) {
				$league_id = $this->uriVars['league_id'];
			} else {
				$league_id = $this->session->userdata('league_id');
				if (!isset($league_id)) {
					$league_id = -1;
				}
			}
			$this->data['league_id'] = $league_id;
			$this->data['current_team'] = array('id'=>-1);
			if (isset($league_id) && !empty($league_id) && $league_id != -1) {
				// GET PLAYERS CURRENT TEAM
				$this->data['current_team'] = $this->dataModel->getFantasyTeam($pid, $this->data['currentScoringPeriod']['id']);
				$this->data['league_name'] = $this->league_model->getLeagueName($league_id);
				if ($this->params['loggedIn']) {
					$this->data['userTeamId'] = $this->user_meta_model->getUserTeamIds($league_id,$this->params['currUser']);
				}
				$this->data['isCommish'] = $this->league_model->userIsCommish($this->params['currUser'],$league_id); 
				$this->data['isAdmin'] = $this->data['loggedIn'] && $this->data['accessLevel'] == ACCESS_ADMINISTRATE; 
				
				$this->data['team_list'] = $this->league_model->getTeamDetails($league_id);
				
				$this->data['draftStatus'] =  -1;
				$this->data['draftEligible'] = -1;
				$this->data['listEligible'] = -1;
				$this->data['draftEnabled'] = -1;
				$this->data['draftCompleted'] = -1;
				if (!isset($this->draft_model)) {
					$this->load->model("draft_model");
				}
				$this->draft_model->load($league_id,'league_id',true);
				$pick_id = 0;
				$pick_team_id = -1;
				if ($this->draft_model->id != -1) {
					$this->data['draftCompleted'] = $this->draft_model->completed;
					$this->data['draftEnabled'] = $this->draft_model->draftEnable;
					$this->data['draftStatus'] = $this->draft_model->getDraftStatus();
					$this->data['draftEligible'] = ($this->draft_model->getDraftElidgibility($pid)) ? 1 : -1;
					// GET CURRENT DRAFT PICK<br />
					$pick = $this->draft_model->getCurrentPick();
					if ($pick) {
						$pick_id = $pick['pick_overall'];
						$pick_team_id = $pick['team_id'];
					}
					if ($this->params['loggedIn']) {
						$userTeams = $this->user_meta_model->getUserTeamIds($this->draft_model->league_id,$this->params['currUser']);
						$this->data['user_team_id'] = $userTeams[0];
						$this->data['listEligible'] = ($this->draft_model->playerInUserList($pid, $this->draft_model->getUserPicks($this->params['currUser']))) ? -1 : 1;
					}
				}
				$this->data['pick_id'] = $pick_id;
				$this->data['pick_team_id'] = $pick_team_id;
				$this->data['current_team'] = $this->dataModel->getFantasyTeam($pid, $this->data['currentScoringPeriod']['id']);
				
				// UPDATE 1.0.16 - JF
				// WAIVERS
				// WAIVERS STATUS AND INFO
				$this->data['useWaivers'] = (isset($this->params['config']['useWaivers']) && $this->params['config']['useWaivers'] == 1) ? 1 : -1;
				if ($this->data['useWaivers'] == 1) {
					$this->data['waiverStatus'] = $this->dataModel->getWaiverStatus($league_id);
					$this->data['waiverClaims'] = $this->dataModel->getWaiverClaims($league_id);
				}
			}
			
			$this->params['subTitle'] = "Player Details for ".$this->data['thisItem']['first_name']." ".$this->data['thisItem']['last_name'];
			$this->params['content'] = $this->load->view($this->views['INFO'], $this->data, true);
		} else {
			$this->params['subTitle'] = $this->data['subTitle'] = "Player not found";
			$this->data['theContent'] = "We're sorry. The player you are looking for could not be found. It's possible the player was removed, retired or had a career ending injury.";
			$this->params['content'] = $this->load->view($this->views['MESSAGE'], $this->data, true);
		}
		$this->makeNav();
		$this->params['pageType'] = PAGE_FORM;
		$this->displayView();
	}
	/**
	 *	GET URI DATA.
	 *	Parses out an id or other parameters from the uri string
	 *
	 */
	protected function getURIData() {
		parent::getURIData();
		if ($this->input->post('player_id')) {
			$this->uriVars['player_id'] = $this->input->post('player_id');
		} // END if
		if ($this->input->post('year')) {
			$this->uriVars['year'] = $this->input->post('year');
		} // END if
		if ($this->input->post('player_type')) {
			$this->uriVars['player_type'] = $this->input->post('player_type');
		} // END if
		if ($this->input->post('roster_status')) {
			$this->uriVars['roster_status'] = $this->input->post('roster_status');
		} // END if
		if ($this->input->post('position_type')) {
			$this->uriVars['position_type'] = $this->input->post('position_type');
		} // END if
		if ($this->input->post('role_type')) {
			$this->uriVars['role_type'] = $this->input->post('role_type');
		} // END if
		if ($this->input->post('league_id')) {
			$this->uriVars['league_id'] = $this->input->post('league_id');
		} // END if
		if ($this->input->post('limit')) {
			$this->uriVars['limit'] = $this->input->post('limit');
		} // END if
		if ($this->input->post('startIdx')) {
			$this->uriVars['startIdx'] = $this->input->post('startIdx');
		} // END if
		if ($this->input->post('pageId')) {
			$this->uriVars['pageId'] = $this->input->post('pageId');
		} // END if
		if ($this->input->post('uid')) {
			$this->uriVars['uid'] = $this->input->post('uid');
		} // END if
	}
	protected function makeNav() {
		if (isset($this->uriVars['league_id']) && !empty($this->uriVars['league_id']) && $this->uriVars['league_id'] != -1) {
			if (!isset($this->league_model)) {
				$this->load->model('league_model');
			}
			$this->league_model->load($this->uriVars['league_id']);
			if ($this->league_model->id != -1) {
				$league_name = $this->league_model->league_name;
			} else {
				$league_name = "Unknown League";
			}
			$lg_admin = false;
			if (isset($this->params['currUser']) && ($this->params['currUser'] == $this->league_model->commissioner_id || $this->params['accessLevel'] == ACCESS_ADMINISTRATE)) {
				$lg_admin = true;
			}
			array_push($this->params['subNavSection'], league_nav($this->uriVars['league_id'], $league_name,$lg_admin));
		}
		
		array_push($this->params['subNavSection'],player_nav());
	}
}
/* End of file players.php */
/* Location: ./application/controllers/players.php */