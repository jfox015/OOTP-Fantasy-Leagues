<?php
/**
 *	Team.
 *	The primary controller for Team manipulation and details.
 *	@author			Jeff Fox
 *	@dateCreated	04/04/10
 *	@lastModified	04/23/20
 *
 */
require_once('base_editor.php');
class team extends BaseEditor {

	/*--------------------------------
	/	VARIABLES
	/-------------------------------*/
	/**
	 *	SLUG.
	 *	@var $_NAME:Text
	 */
	var $_NAME = 'team';
	/**
	 *	SCORING PERIOD.
	 *	The current scoring period object.
	 *	@var $scoring_period:Array
	 */
	var $scoring_period = array();
	/**
	 *	RULES.
	 *	Array of rules for the league.
	 *	@var $rules:Array
	 */
	var $rules = array();
	/**
	 *	User Waivers.
	 *	TRUE if waivers enabled, FALSE if not
	 *	@var $userWaivers:Boolean
	 */
	var $userWaivers = false;
	/*--------------------------------
	/	C'TOR
	/-------------------------------*/
	/**
	 *	Creates a new instance of team.
	 */
	public function team() {
		parent::BaseEditor();
	}
	/**
	 *	INDEX.
	 *	The default handler when the controller is called.
	 *	Checks for an existing auth session, and if found,
	 *	redirects to the dashboard. Otherwise, it redirects 
	 *	to the login.
	 */
	public function index() {
		redirect('search/teams/');
	}
	/*---------------------------------------
	/	CONTROLLER SUBMISSION HANDLERS
	/--------------------------------------*/
	/**
	 *	INIT.
	 *	Overrides the default init function. Sets page views and sets default values.
	 */
	function init() {
		parent::init();
		$this->modelName = 'team_model';
		
		$this->getURIData();
		if (!$this->isAjax() && isset($this->uriVars['id'])) {
			$this->load->model($this->modelName,'dataModel');
			$this->dataModel->load($this->uriVars['id']);
			$this->load->model('league_model');
			$this->league_model->load($this->dataModel->league_id);
			
			if ($this->league_model->id != -1 && $this->league_model->access_type == -1) {
				$isAdmin = ($this->params['accessLevel'] == ACCESS_ADMINISTRATE) ? true: false;
				$isCommish = ($this->league_model->userIsCommish($this->params['currUser'])) ? true: false;
				if (!$isAdmin && !$isCommish) {
					if (!$this->league_model->isLeagueMember($this->params['currUser'])) {
						redirect('/league/privateLeague/'.$this->uriVars['id']);
					}
				}
			}
		}
		$this->views['EDIT'] = 'team/team_editor';
		$this->views['VIEW'] = 'team/team_info';
		$this->views['FAIL'] = 'team/team_message';
		$this->views['SUCCESS'] = 'team/team_message';
		$this->views['ADMIN'] = 'team/team_admin';
		$this->views['ADD_DROP'] = 'team/team_add_drop';
		$this->views['AVATAR'] = 'team/team_avatar';
		$this->views['STATS'] = 'team/team_stats';
		$this->views['TRANSACTIONS'] = 'team/team_transactions';
		$this->views['TRADE'] = 'team/team_trade';
		$this->views['TRADE_REVIEW'] = 'team/team_trade_review';
		$this->views['TRADE_HISTORY'] = 'team/team_trade_history';
		$this->views['ELIGIBILITY'] = 'team/team_eligibility';
		$this->views['LINEUP'] = 'team/team_lineup';
		//$this->views['STARTERS'] = 'team/team_starters';
		$this->debug = false;
		$this->useWaivers = (isset($this->params['config']['useWaivers']) && $this->params['config']['useWaivers'] == 1) ? true : false;
	}
	/**
	 *	ADMIN.
	 *	Calls the admin interface for teams.
	 */
	public function admin() {
		$this->getURIData();
		$this->data['subTitle'] = "Team Admin";
		$this->load->model($this->modelName,'dataModel');
		$this->dataModel->load($this->uriVars['id']);
		$this->data['team_id'] = $this->uriVars['id'];
		$this->data['league_id'] = $this->dataModel->league_id;
		$this->makeNav();
		$this->params['content'] = $this->load->view($this->views['ADMIN'], $this->data, true);
	    $this->displayView();	
	}
	/**
	 *	AVATAR
	 *	Update the team's avatar.
	 */
	public function avatar() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->loadData();
			$this->data['avatar'] = $this->dataModel->avatar;
			$this->data['team_id'] = $this->dataModel->id;
			$this->data['teamname'] = $this->dataModel->teamname;
			$this->data['teamnick'] = $this->dataModel->teamnick;
			$this->data['subTitle'] = 'Edit Team Avatar';
			
			if (!($this->input->post('submitted')) || ($this->input->post('submitted') && !isset($_FILES['avatarFile']['name']))) {
				if ($this->input->post('submitted') && !isset($_FILES['avatarFile']['name'])) {
					$fv = & _get_validation_object();
					$fv->setError('avatarFile','The avatar File field is required.');
				}
				$this->params['content'] = $this->load->view($this->views['AVATAR'], $this->data, true);
				$this->params['pageType'] = PAGE_FORM;
				$this->displayView();
			} else {
				if (!(strpos($_FILES['avatarFile']['name'],'.jpg') || !strpos($_FILES['avatarFile']['name'],'.jpeg') || !strpos($_FILES['avatarFile']['name'],'.gif') || !strpos($_FILES['avatarFile']['name'],'.png'))) {
					$fv = & _get_validation_object();
					$fv->setError('avatarFile','The file selected is not a valid image file.');  
					$this->params['content'] = $this->load->view($this->views['AVATAR'], $this->data, true);
					$this->params['pageType'] = PAGE_FORM;
					$this->displayView();
				} else {
					if ($_FILES['avatarFile']['error'] === UPLOAD_ERR_OK) {
						$change = $this->dataModel->applyData($this->input, $this->data['team_id']); 
						if ($change) {
							$this->dataModel->save();
							$this->session->set_flashdata('message', '<p class="success">The image has been successfully updated.</p>');
							redirect('team/info/'.$this->dataModel->id);
						} else {
							$message = '<p class="error">Avatar Change Failed.';
							$message .= '</p >';
							$this->session->set_flashdata('message', $message);
							redirect('team/avatar');
						}
					} else {
						throw new UploadException($_FILES['avatarFiles']['error']);
					} // END if
				} // END if
			} // END if
		} else {
	        $this->session->set_flashdata('loginRedirect',current_url());	
			redirect('user/login');
	    } // END if
	}
	/**
	 *	REMOVE AVATAR
	 *	Remove the team's avatar.
	 */
	public function removeAvatar() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->loadData();
			if ($this->dataModel->id != -1) {
				$success = $this->dataModel->deleteFile('avatar',PATH_TEAMS_AVATAR_WRITE,true);
			}
			if ($success) {
				$this->session->set_flashdata('message', '<p class="success">The image has been successfully deleted.</p>');
				redirect('team/info/'.$this->dataModel->id);
			} else {
				$message = '<p class="error">Avatar Delete Failed.';
				$message .= '<b>'.$this->dataModel->statusMess.'</b>';
				$message .= '</p >';
				$this->session->set_flashdata('message', $message);
				redirect('team/avatar');
			}
		}
	}
	/*-----------------------------------------------------------
	/
	/	LINEUP
	/
	/ 	@since 1.0
	/	Moved from /team/info/  to new function in PROD 1.0.3
	/-----------------------------------------------------------*/
	public function lineup() {
		
		$this->getURIData();
		$this->load->model($this->modelName,'dataModel');
		$this->dataModel->load($this->uriVars['id']);
		$this->data['team_id'] = $this->uriVars['id'];
		$this->data['league_id'] = $this->dataModel->league_id;
		
		if (isset($this->data['team_id'])) { 
		
			if (!function_exists('getScoringPeriod')) {
				$this->load->helper('admin');
			}

			if (isset($this->uriVars['period_id'])) {
				$curr_period_id = $this->uriVars['period_id'];
				$curr_period = getScoringPeriod($curr_period_id);
			} else {
				$curr_period = $this->getScoringPeriod();
				$curr_period_id = $curr_period['id'];
			}
			
			$this->data['curr_period'] = $curr_period_id;
			
			$players = $this->dataModel->getCompleteRoster($curr_period_id);
			
			if (!isset($this->league_model)) {
				$this->load->model('league_model');
				$this->league_model->load($this->dataModel->league_id);
			}
			$this->league_model->load($this->dataModel->league_id);
			$this->data['avail_periods'] = $this->league_model->getAvailableRosterPeriods();
			
			// Setup header Data
			$this->data['thisItem']['league_id'] = $this->dataModel->league_id;
			$this->data['thisItem']['team_id'] = $this->dataModel->id;
			$this->data['thisItem']['teamname'] = $this->dataModel->teamname;
			$this->data['thisItem']['teamnick'] = $this->dataModel->teamnick;
			$this->data['thisItem']['avatar'] = $this->dataModel->avatar;
			$isAdmin = ($this->params['accessLevel'] == ACCESS_ADMINISTRATE) ? true: false;
			$isCommish = ($this->league_model->userIsCommish($this->params['currUser'])) ? true: false;
		
			if ($this->params['loggedIn'] && ($this->dataModel->owner_id == $this->params['currUser'] || $isAdmin || $isCommish)) {
				if (!$this->league_model->validateRoster($this->dataModel->getBasicRoster($curr_period_id))) {
					$this->data['message'] = "<b>Team Roster is currently illegal! The team will score 0 points until roster errors are corrected.</b><br /><br />".$this->league_model->statusMess;
					$this->data['messageType'] = 'error';
				}
			}
			if ($this->params['loggedIn']) {
				$this->data['thisItem']['userTeamId'] = $this->user_meta_model->getUserTeamIds($this->dataModel->league_id,$this->params['currUser']);
			}
			$this->data['thisItem']['team_list'] = getOOTPTeams($this->params['config']['ootp_league_id'],false);
			
			if (isset($this->data['thisItem']['league_id']) && $this->data['thisItem']['league_id'] != -1) {
				$this->data['thisItem']['fantasy_teams'] = getFantasyTeams($this->data['thisItem']['league_id']);
			}
			$this->data['thisItem']['visible_week'] = getVisibleDays($curr_period['date_start'],$this->params['config']['sim_length']);
			
			$this->data['thisItem']['schedules'] = getPlayerSchedules($players,$curr_period['date_start'],$this->params['config']['sim_length']);
			
			$this->data['thisItem']['owner_name'] = resolveOwnerName($this->dataModel->owner_id);
			$this->data['thisItem']['owner_id'] = $this->dataModel->owner_id;
			
			$divisionName = '';
			$divisionsList = listLeagueDivisions($this->dataModel->id,false);
			foreach($divisionsList as $key => $value) {
				if ($this->dataModel->division_id == $key) {
					$divisionName = $value;
					break;
				}
			}
			/*---------------------------------------------------
			/	EDIT - 1.0.3 PROD
			/	INCLUDE CURRENT PLAYER STATS ON LINEUP PAGE
			/--------------------------------------------------*/
			$this->prepForQuery();
			if (!isset($this->uriVars['stats_range']) || empty($this->uriVars['stats_range'])) {
				$this->data['stats_range'] = 0;
			} else {
				$this->data['stats_range'] = $this->uriVars['stats_range'];
			} // END if
			$date1 = new DateTime($this->ootp_league_model->current_date);
			$date2 = new DateTime($this->ootp_league_model->start_date);

			if ($date1 <= $date2 || $this->data['curr_period'] <= 1) {
				$this->data['stats_range'] = 1;	
			} // END if
			$periodForQuery = $this->data['curr_period'];
			if ($this->data['stats_range'] != 0) {
				$periodForQuery = -1;
			} // END if
			
			$formattedStats = array();
			$stats['pitchers'] = $this->dataModel->getTeamStats(false,$this->data['team_id'], 2, NULL,NULL,$this->data['stats_range'],$periodForQuery,0,-1,0,$this->ootp_league_model->league_id,$this->ootp_league_model->current_date,$this->rules);
			$this->data['colnames']['pitchers']=player_stat_column_headers(2, QUERY_COMPACT, $this->rules['scoring_type'] == LEAGUE_SCORING_HEADTOHEAD, true, false, false, false, $this->rules['scoring_type'] != LEAGUE_SCORING_HEADTOHEAD);
			$this->data['fields'] = player_stat_fields_list(2, QUERY_COMPACT, $this->rules['scoring_type'] == LEAGUE_SCORING_HEADTOHEAD, true, false, false, false, $this->rules['scoring_type'] != LEAGUE_SCORING_HEADTOHEAD, false, true);
			$this->data['formattedStats']['pitchers'] = formatStatsForDisplay($stats['pitchers'], $this->data['fields'], $this->params['config'],$this->data['league_id'], NULL, NULL, false, true);
			
			// BATTERS
			$stats['batters'] = $this->dataModel->getTeamStats(false,$this->data['team_id'], 1, NULL,NULL,$this->data['stats_range'],$periodForQuery,0,-1,0,$this->ootp_league_model->league_id,$this->ootp_league_model->current_date,$this->rules);
			$this->data['colnames']['batters']=player_stat_column_headers(1, QUERY_COMPACT, $this->rules['scoring_type'] == LEAGUE_SCORING_HEADTOHEAD, true, false, false, false, $this->rules['scoring_type'] != LEAGUE_SCORING_HEADTOHEAD);
			$this->data['fields'] = player_stat_fields_list(1, QUERY_COMPACT, $this->rules['scoring_type'] == LEAGUE_SCORING_HEADTOHEAD, true, false, false, false, $this->rules['scoring_type'] != LEAGUE_SCORING_HEADTOHEAD, false, true);
			$this->data['formattedStats']['batters'] = formatStatsForDisplay($stats['batters'], $this->data['fields'], $this->params['config'],$this->data['league_id'], NULL, NULL, false, true);
			
			$players = $this->addStatsToPlayerList($players, $this->data['formattedStats']['pitchers']);
			$players = $this->addStatsToPlayerList($players, $this->data['formattedStats']['batters']);

			if (isset($players[0])) {
				$this->data['thisItem']['players_active'] =	$players[0];
			} 
			if (isset($players[1])) {
				$this->data['thisItem']['players_reserve'] = $players[1];
			}
			if (isset($players[2])) {
				$this->data['thisItem']['players_injured'] = $players[2];
			}
			
			$this->data['showAdmin'] = (($this->params['currUser'] == $this->dataModel->owner_id && $curr_period_id == $this->params['config']['current_period']) || $this->params['accessLevel'] == ACCESS_ADMINISTRATE) ? true : false;
			$this->params['content'] = $this->load->view($this->views['LINEUP'], $this->data, true);
			$this->params['pageType'] = PAGE_FORM;
		} else {
			$message = "error:Required params missing";
			$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
		}  // END if if (isset($this->uriVars['team_id']))

		$this->makeNav();
		$this->displayView();

	}
	/*-------------------------------------------
	/
	/	TRADES
	/
	/	Added to version 1.0.4
	/------------------------------------------*/
	/**
	 *	TRADE
	 *	Main function to draw the trade page. This not only draws the normal trade
	 *	editor screen, buit also access trade ID and URI Data vars to prepopulate 
	 *	the trade fields with players
	 *	
	 *	@since	1.0.4
	 */
	public function trade() {
		$this->getURIData();
		
		$this->enqueStyle('list_picker.css');
		
		$this->load->model($this->modelName,'dataModel');
		$this->dataModel->load($this->uriVars['id']);
		$this->data['team_id'] = $this->uriVars['id'];
		$this->data['team_id2'] = -1;
		$this->data['league_id'] = $this->dataModel->league_id;
		
		$this->params['subTitle'] = "Trades";
		$this->data['subTitle'] = "Trade";
		// GET DRAFT STATUS
		$this->load->model('draft_model');
		$this->draft_model->load($this->dataModel->league_id,'league_id');
		
		if (!isset($this->league_model)) { $this->load->model('league_model'); }
		$this->league_model->load($this->dataModel->league_id);
		$isAdmin = ($this->params['accessLevel'] == ACCESS_ADMINISTRATE) ? true: false;
		$isCommish = ($this->league_model->userIsCommish($this->params['currUser'])) ? true: false;
				
		if (!$this->params['loggedIn'] || ($this->dataModel->owner_id != $this->params['currUser'] && (!$isAdmin && !$isCommish))) {
			$this->data['theContent'] = "<b>ERROR</b><br /><br />This page is accessible only by the owner of this team.";
			$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
		} else if ($this->draft_model->completed != 1) {
			$this->data['theContent'] = "<b>ERROR</b><br /><br />Your league has not yet completed it's draft. This page will become available once the draft has been completed.";
			$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
		} else {
			
			$this->data['players'] = $this->dataModel->getBasicRoster($this->params['config']['current_period']);
			$this->data['team_name'] = $this->dataModel->teamname." ".$this->dataModel->teamnick;
			
			$this->data['scoring_period'] = $this->getScoringPeriod();
			$this->data['scoring_periods'] = getAvailableScoringPeriods($this->data['league_id']);
			
			if (isset($this->data['league_id']) && $this->data['league_id'] != -1) {
				$this->data['fantasy_teams'] = getFantasyTeams($this->data['league_id']);
			} // END if
			$sendList = array();
			$receiveList = array();
			/*-----------------------------------------
			/	 LOAD PREVIOUS TRADE DATA
			/----------------------------------------*/
			// RESPOND TO DIFFERENT WAYS TO PULL EXISTING TRADE DATA
			if (isset($this->uriVars['tradeTo']) || isset($this->uriVars['tradeFrom'])) {
				$sendList['all'] = (isset($this->uriVars['tradeTo'])) ? explode("&",$this->uriVars['tradeTo']) : array();
				$receiveList['all'] = (isset($this->uriVars['tradeFrom'])) ? explode("&",$this->uriVars['tradeFrom']) : array();
			} else if ((isset($this->uriVars['trade_id']) && $this->uriVars['trade_id'] != -1) || 
						(isset($this->uriVars['prev_trade_id']) && $this->uriVars['prev_trade_id'] != -1)) {
				$use_trade_id = -1;
				$trade_type = 1;
				if (isset($this->uriVars['trade_id']) && $this->uriVars['trade_id'] != -1) {
					$use_trade_id = $this->uriVars['trade_id'];
				} else if (isset($this->uriVars['prev_trade_id']) && $this->uriVars['prev_trade_id'] != -1) {
					$use_trade_id = $this->uriVars['prev_trade_id'];
					$trade_type = 2;
				} // END if
				//print("trade id = ".$use_trade_id."<br />");
				if ($use_trade_id != -1) {
                    // EXPIRATION CHECK FOR EXISTING TRADES ONLY
                    if ($this->params['config']['tradesExpire'] == 1) {
                        if ($this->dataModel->getIsTradePastExpiration($use_trade_id)) {
                            $this->dataModel->updateTrade($use_trade_id, TRADE_EXPIRED, $this->lang->line('team_trade_auto_expired'));
                        } // END if
                    } // END if
					// PULL PLAYER LISTS FROM DATABASE	
					$trade = $this->dataModel->getTrade($use_trade_id);
					if (sizeof($trade) > 0) {
						if ($trade_type == 1) {
							$sendList['all'] = $trade['send_players'];
							$receiveList['all'] = $trade['receive_players'];
						} else {
							$sendList['all'] = $trade['receive_players'];
							$receiveList['all'] = $trade['send_players'];
						} // END if
						$this->data['team_id2'] = $trade['team_1_id'];
					} // END if
				} // END if
			} // END if
			
			if (isset($this->uriVars['team_id2']) && !empty($this->uriVars['team_id2']) && $this->uriVars['team_id2'] != -1) {
				$this->data['team_id2'] = $this->uriVars['team_id2'];
			} else {
				if ($this->data['team_id2'] == -1 && isset($this->data['fantasy_teams']) && sizeof($this->data['fantasy_teams']) > 0) {
					foreach($this->data['fantasy_teams'] as $id => $teamName) {
						if (!empty($id) && $id != " ") {
							if ($id != $this->data['team_id']) {
								$this->data['team_id2'] = $id;
								break;
							} // END if
						} // END if
					} // END foreach
				} // END if
			} // END if
			$this->prepForQuery();
			if (!isset($this->uriVars['stats_range']) || empty($this->uriVars['stats_range'])) {
				$this->data['stats_range'] = -1;
			} else {
				$this->data['stats_range'] = $this->uriVars['stats_range'];
			} // END if
			$date1 = new DateTime($this->ootp_league_model->current_date);
			$date2 = new DateTime($this->ootp_league_model->start_date);
			
			$curr_period = $this->getScoringPeriod();
			$curr_period_id = $curr_period['id'];
			if ($date1 <= $date2 || $curr_period_id <= 1) {
				$this->data['stats_range'] = 1;	
			} // END if
			$periodForQuery = $curr_period_id;
			if ($this->data['stats_range'] != -1) {
				$periodForQuery = -1;
			} // END if
			if (!isset($this->uriVars['stats_source']) || !empty($this->uriVars['stats_source'])) {
				$this->data['stats_source'] = "sp_all";
			} else {
				$this->data['stats_source'] = $this->uriVars['stats_source'];
			} // END if
			
			if (!isset($this->uriVars['type']) || empty($this->uriVars['type'])) {
				$this->data['type'] = 1;
			} else {
				$this->data['type'] = $this->uriVars['type'];
			} // END if
			$this->data['title'] = array();
			$this->data['formatted_stats'] = array();
			$this->data['limit'] = -1;
			$this->data['startIndex'] = 0;
			$this->data['showTeam'] = -1;
			$this->data['showTrans'] = 1;
			//print ("this->uriVars['stats_source'] = ".$this->uriVars['stats_source']."<br />");
			//print ("this->uriVars['stats_range'] = ".$this->uriVars['stats_range']."<br />");
			//print ("periodForQuery = ".$periodForQuery."<br />");
			$stats['pitchers'] = $this->dataModel->getTeamStats(false,$this->data['team_id2'], 2, NULL,NULL,$this->data['stats_range'],$periodForQuery,0,-1,0,$this->ootp_league_model->league_id,$this->ootp_league_model->current_date,$this->rules);
			$this->data['list_title'] = "Pitching";
			$this->data['colnames']=player_stat_column_headers(2, QUERY_BASIC, $this->rules['scoring_type'] == LEAGUE_SCORING_HEADTOHEAD, false, true, false, false, $this->rules['scoring_type'] != LEAGUE_SCORING_HEADTOHEAD);
			$this->data['fields'] = player_stat_fields_list(2, QUERY_BASIC, $this->rules['scoring_type'] == LEAGUE_SCORING_HEADTOHEAD, false, true, false, false, $this->rules['scoring_type'] != LEAGUE_SCORING_HEADTOHEAD);
			$this->data['player_stats'] = formatStatsForDisplay($stats['pitchers'], $this->data['fields'], $this->params['config'],$this->data['league_id'], NULL, NULL, false, true);
			$this->data['formatted_stats']['pitchers'] = $this->load->view($this->views['STATS_TABLE'], $this->data, true);
			// BATTERS
			$stats['batters'] = $this->dataModel->getTeamStats(false,$this->data['team_id2'], 1, NULL,NULL,$this->data['stats_range'],$periodForQuery,0,-1,0,$this->ootp_league_model->league_id,$this->ootp_league_model->current_date,$this->rules);
			$this->data['list_title'] = "Batting";
			$this->data['colnames']=player_stat_column_headers(1, QUERY_BASIC, $this->rules['scoring_type'] == LEAGUE_SCORING_HEADTOHEAD, false, true, false, false, $this->rules['scoring_type'] != LEAGUE_SCORING_HEADTOHEAD);
			$this->data['fields'] = player_stat_fields_list(1, QUERY_BASIC, $this->rules['scoring_type'] == LEAGUE_SCORING_HEADTOHEAD, false, true, false, false, $this->rules['scoring_type'] != LEAGUE_SCORING_HEADTOHEAD);
			$this->data['player_stats'] = formatStatsForDisplay($stats['batters'], $this->data['fields'], $this->params['config'],$this->data['league_id'], NULL, NULL, false, true);
			$this->data['formatted_stats']['batters']= $this->load->view($this->views['STATS_TABLE'], $this->data, true);
			
			$this->load->model('player_model');
			if(isset($sendList['all']) && sizeof($sendList['all']) > 0) { 
				$sendList['players'] = array();
				foreach($sendList['all'] as $data) {
					$tmpPlayer = explode("_",$data);
					if (strpos($tmpPlayer[0],";") !== false) {
						$idStr = explode(";",$tmpPlayer[0]);
						$tmpPlayer[0] = $idStr[0];
					}
					$playerData = $this->player_model->getPlayerDetails($tmpPlayer[0]);
					array_push($sendList['players'], $playerData);
				} // END foreach
				$this->data['sendList'] = $sendList['players'];
			}
			if (isset($receiveList['all']) && sizeof($receiveList['all']) > 0) {
				$receiveList['players'] = array(); 
				foreach($receiveList['all'] as $data) {
					$tmpPlayer = explode("_",$data);
					if (strpos($tmpPlayer[0],";") !== false) {
						$idStr = explode(";",$tmpPlayer[0]);
						$tmpPlayer[0] = $idStr[0];
					}
					$playerData = $this->player_model->getPlayerDetails($tmpPlayer[0]);
					array_push($receiveList['players'], $playerData);
				} // END foreach
				$this->data['receiveList'] = $receiveList['players'];
			} // END if
			
			// GET USER INITIATED TRADES AND OFFERED TRADES
			$this->data['allowProtests'] = ($this->params['config']['approvalType'] == 2) ? true : false;
            $tradeList = $this->dataModel->getTradesForScoringPeriod($this->data['league_id'],$this->data['scoring_period']['id'], $this->data['team_id'], false, false,$this->data['allowProtests']);
            $trades = array('offered'=>array(),'incoming'=>array(),'approvals'=>array(),'completed'=>array(),'protests'=>array(),'other'=>array());

            if (sizeof($tradeList) > 0) {
                foreach ($tradeList as $tradeData) {
                    if ($tradeData['status'] == TRADE_OFFERED) {
                        if ($tradeData['team_1_id'] == $this->data['team_id']) {
                            array_push($trades['offered'], $tradeData);
                        } else if ($tradeData['team_2_id'] == $this->data['team_id']) {
                            array_push($trades['incoming'], $tradeData);
                        }
                    } else if ($tradeData['status'] == TRADE_PENDING_LEAGUE_APPROVAL) {
                        array_push($trades['approvals'], $tradeData);
                    } else if ($tradeData['status'] == TRADE_COMPLETED) {
                         array_push($trades['completed'], $tradeData);
                    } else {
                        array_push($trades['other'], $tradeData);
                    }
                }
            }
            //$trades['offered'] = $this->dataModel->getPendingTrades($this->data['league_id'],$this->data['team_id'], false, false,$this->data['allowProtests']);
			//$trades['incoming'] = $this->dataModel->getPendingTrades($this->data['league_id'],false,$this->data['team_id'],false,$this->data['allowProtests']);
			// GET PROTEST COUNT
			if ($this->data['allowProtests']) {
				$protests = $this->dataModel->getTradesForScoringPeriod($this->data['league_id'],$this->data['scoring_period']['id'], false, false,$this->data['team_id'], $this->data['allowProtests']);
				foreach($protests as $tradeData) {
					if ($tradeData['status'] == TRADE_PENDING_LEAGUE_APPROVAL) {
						array_push($trades['protests'], $tradeData);
					}
				}
				if (sizeof($trades['protests']) > 0) {
					$this->data['protests'] = $this->dataModel->getTradeProtests($this->data['league_id']);
				}
			}
            $this->data['teamTrades'] = $trades;
			$this->params['content'] = $this->load->view($this->views['TRADE'], $this->data, true);
			$this->params['pageType'] = PAGE_FORM;
			
		} // END if
		$this->makeNav();
		$this->displayView();
	}
	/**
	 * 	TRADE RESPONSE
	 * 	CATCH ALL FOR TRADE RESPONSES
	 * 	@param	trade_id		The Trade ID value
	 * 	@param	type			The trade response type (Accepted, Rejected, etc.)
	 * 	@param	team_id			The ID of the team making the response
	 * 	@param	display_page	(OPTIONAL) Resulting View page. Passed only from non-AJAX submissions
	 * 	@param	comments		(OPTIONAL) Trade comments or reponse
	 *	
	 *	@since	1.0.4
	 */
	public function tradeResponse() {
		
		$this->getURIData();
		$outType = 1;
		// DEFAULT VARS
		$code = -1;
		$status = "";
		$result = "{";
		$error = false;
		$this->data['comments'] = "";
		$this->data['prevTradeId'] = -1;
		$this->data['display_page'] = $this->views['SUCCESS'];
		
		// CONVERT INPUT DATA TO DATA VARS
		if ($this->input->post('submitted')) {
			$this->data['trade_id'] = $this->input->post('trade_id') ? $this->input->post('trade_id') : -1;
			$this->data['type'] = $this->input->post('type') ? $this->input->post('type') : -1;
			$this->data['team_id'] = $this->input->post('id') ? $this->input->post('id') : -1;
			$this->data['display_page'] = $this->input->post('referrer') ? $this->input->post('referrer') : $this->views['SUCCESS'];
			$this->data['comments'] = $this->input->post('comments') ? $this->input->post('comments') : "";
			$this->data['prevTradeId'] = $this->input->post('prevTradeId') ? $this->input->post('prevTradeId') : -1;
			$outType = 2;
		} else {
			$this->getURIData();
			$this->data['trade_id'] = (isset($this->uriVars['trade_id'])) ? $this->uriVars['trade_id'] : -1;
			$this->data['type'] = (isset($this->uriVars['type'])) ? $this->uriVars['type'] : -1;
			$this->data['team_id'] = (isset($this->uriVars['id'])) ? $this->uriVars['id'] : -1;
			$this->data['prevTradeId'] = (isset($this->uriVars['prev_trade_id'])) ? $this->uriVars['prev_trade_id'] : -1;
		} // END if
		
		// VERIFY MINIMUM VARS HAVE VALUES
		if ($this->data['trade_id'] != -1 && $this->data['type'] != -1 && $this->data['team_id'] != -1) {
            // LOAD MODELS
			$this->load->model($this->modelName,'dataModel');

            // EXPIRATION CHECK FOR EXISTING TRADES ONLY
            if ($this->params['config']['tradesExpire'] == 1) {
                if ($this->dataModel->getIsTradePastExpiration($this->data['trade_id'])) {
                    $this->data['type'] = TRADE_EXPIRED;
                }
            }

			$this->dataModel->load($this->data['team_id']);

			$this->data['league_id'] = $this->dataModel->league_id;
			if (!isset($this->league_model)) {
				$this->load->model('league_model');
			}
			$this->league_model->load($this->data['league_id']);
			
			$this->data['scoring_period'] = $this->getScoringPeriod();
			
			$msg = "";
			$trade = $this->dataModel->getTrade($this->data['trade_id']);

			$recipient_id = $trade['team_1_id'];
			if ($this->data['type'] == TRADE_ACCEPTED) {
				if ($this->data['scoring_period']['id'] == $trade['in_period']) {
					$rosterMessages = $this->verifyRostersForTrade($trade['team_1_id'], $trade['send_players'], $trade['team_2_id'], $trade['receive_players'], $trade['in_period']);
					if (empty($rosterMessages)) {
						if ($this->params['config']['approvalType'] == -1) {
							$processResponse = $this->dataModel->processTrade($this->data['trade_id'],$this->data['type'],$this->data['comments']);
							if ($processResponse) {
								$msg = $this->lang->line('team_trade_accepted_no_approvals');
								$this->dataModel->logTransaction(NULL, NULL, NULL, $trade['send_players'], $trade['receive_players'],
													  $this->league_model->commissioner_id, $this->params['currUser'],
													  $this->params['accessLevel'] == ACCESS_ADMINISTRATE, $this->data['scoring_period']['id'],
													  $this->dataModel->league_id, $trade['team_1_id'], $this->dataModel->getTeamOwnerId($trade['team_1_id']), $trade['team_2_id']);
								$this->dataModel->logTransaction(NULL, NULL, NULL, $trade['receive_players'], $trade['send_players'],
													  $this->league_model->commissioner_id, $this->params['currUser'],
													  $this->params['accessLevel'] == ACCESS_ADMINISTRATE, $this->data['scoring_period']['id'],
													  $this->dataModel->league_id, $trade['team_2_id'], $this->dataModel->getTeamOwnerId($trade['team_2_id']), $trade['team_1_id']);
								$code = 200;
								$status = $this->lang->line('team_trade_processed');
							} else {
								$code = 301;
								$status = $processResponse;
							} // END if
						} else {
							if ($this->params['config']['approvalType'] == 2) {
								$this->data['type'] = TRADE_PENDING_LEAGUE_APPROVAL;
								$msg = $this->lang->line('team_trade_pending_league_approval');
								$approver = "League";
							} else if ($this->params['config']['approvalType'] == 1) {
								$this->data['type'] = TRADE_PENDING_COMMISH_APPROVAL;
								$msg = $this->lang->line('team_trade_pending_commish_approval');
								$approver = "commissioner";
							}
							$error = $this->dataModel->updateTrade($this->data['trade_id'], $this->data['type'], $this->data['comments']);
							$code = 200;
							$status = str_replace('[APPROVER_TYPE]',$approver,$this->lang->line('team_trade_pendsing_approval'));
						}
					} else {
						$error = true;
						$code = 301;
						$status = $rosterMessages;
					} // END if
				} else {
					$error = true;
					$code = 301;
					$status = $this->lang->line('team_trade_invalid');
					$this->dataModel->updateTrade($this->data['trade_id'],TRADE_INVALID,$status);
				} // END if
			} else {
				$updateDb = true;
				$tradeApproved = false;
				switch ($this->data['type']) {		
				// REJECTED BY OWNER
					// REJECTED BUT WITH A COUNTER OFFER
					case TRADE_REJECTED_COUNTER:
						$msg = $this->lang->line('team_trade_rejected_counter');
						$this->data['prevTradeId'] = $this->data['trade_id'];
						break;
					case TRADE_REJECTED_OWNER:
						$msg = $this->lang->line('team_trade_rejected_owner');
						break;
					// RETRACTED
					case TRADE_RETRACTED:
						$msg = $this->lang->line('team_trade_retracted');
						$recipient_id = $trade['team_2_id'];
						break;
					// REMOVED BY ADMIN
					case TRADE_REMOVED:
						$msg = $this->lang->line('team_trade_removed');
						break;
					// TRADE EXPIRED
					case TRADE_EXPIRED:
						$msg = $this->lang->line('team_trade_expired');
						break;
					// INVLALID TRADE
					case TRADE_INVALID:
						$msg = $this->lang->line('team_trade_invalid');
						break;
					// TRADE APPROVED
					case TRADE_APPROVED:
						$msg = $this->lang->line('team_trade_approved');
						$tradeApproved = true;
						break;
					// REJECTED BY COMMISIONER
					case TRADE_REJECTED_COMMISH:
						$msg = $this->lang->line('team_trade_rejected_commish');
						break;
					// TRADE PROTEST
					case TRADE_PROTEST:
						// TEST IF THE PROTEST PERIOD IS STILL ACTIVE
                        $protestStart = strtotime($trade['response_date']);
                        $protestEnd = $protestStart + ((60*60*24) * $this->params['config']['protestPeriodDays']);
                        if (time() < $protestEnd) {
                            if ($this->dataModel->logTradeProtest($this->data['trade_id'],$this->data['team_id'], $this->data['comments'])) {
								$protestCount = $this->dataModel->getTradeProtests(false,$this->data['trade_id']);
								if (sizeof($protestCount) >= $this->params['config']['minProtests']) {
                                    $this->data['type'] = TRADE_REJECTED_LEAGUE;
                                    $this->data['comments'] = "The trade was rejected by the league.";
                                    $msg = $this->lang->line('team_trade_rejected_league');
                                } else {
                                    $msg = $this->lang->line('team_trade_protest_logged');
                                    $this->data['type'] = TRADE_PENDING_LEAGUE_APPROVAL;
                                    $updateDb = false;
                                }
                            }
                        } else {
							$protestCount = $this->dataModel->getTradeProtests(false,$this->data['trade_id']);
							if (sizeof($protestCount) < $this->params['config']['minProtests']) {
								$tradeApproved = true;
								$this->data['type'] = TRADE_APPROVED;
								$updateDb = false;
							}
						}
						break;
					default:
						break;	
				} // END switch
				if ($tradeApproved) {
					$processResponse = $this->dataModel->processTrade($this->data['trade_id'],$this->data['type'],$this->data['comments']);
					if ($processResponse) {
						$this->dataModel->logTransaction(NULL, NULL, NULL, $trade['send_players'], $trade['receive_players'],
						$this->league_model->commissioner_id, $this->params['currUser'],
						$this->params['accessLevel'] == ACCESS_ADMINISTRATE, $this->data['scoring_period']['id'],
						$this->dataModel->league_id, $trade['team_1_id'], $this->dataModel->getTeamOwnerId($trade['team_1_id']), $trade['team_2_id']);
						
						$this->dataModel->logTransaction(NULL, NULL, NULL, $trade['receive_players'], $trade['send_players'],
						$this->league_model->commissioner_id, $this->params['currUser'],
						$this->params['accessLevel'] == ACCESS_ADMINISTRATE, $this->data['scoring_period']['id'],
						$this->dataModel->league_id, $trade['team_2_id'], $this->dataModel->getTeamOwnerId($trade['team_2_id']), $trade['team_1_id']);
					}
				}
				if ($updateDb === true) {
					$error = !$this->dataModel->updateTrade($this->data['trade_id'], $this->data['type'], $this->data['comments']);
				}
				if (!$error) {
					$code = 200;
					$status = "Update completed";
				} else {
					$code = 301;
					$status = "An error occured saving the update.";
				}
			} // END if (response type)
			if ($error === false && !empty($msg)) {
				$msg .= $this->lang->line('email_footer');
				$msg = str_replace('[ACCEPTING_TEAM_NAME]', $this->dataModel->getTeamName($trade['team_2_id']), $msg);
				$msg = str_replace('[OFFERING_TEAM_NAME]', $this->dataModel->getTeamName($trade['team_1_id']), $msg);
				$msg = str_replace('[USERNAME]', getUsername($this->dataModel->getTeamOwnerId($trade['team_1_id'])), $msg);
				$msg = str_replace('[COMMENTS]', $this->data['comments'],$msg);
				$msg = str_replace('[URL_LINEUP]', anchor('/team/info/'.$trade['team_1_id'],'adjust your lineup'),$msg);
				$msg = str_replace('[LEAGUE_NAME]', $this->league_model->league_name,$msg);
				$data['messageBody']= $msg;
				//print("email template path = ".$this->config->item('email_templates')."<br />");
				$data['leagueName'] = $this->league_model->league_name;
				$data['title'] = $this->lang->line('team_email_title_trade_response');
				$message = $this->load->view($this->config->item('email_templates').'general_template', $data, true);
				// SEND MESSAGES
				// SEND TO TEAM ONE
				$tradeTypes = loadSimpleDataList('tradeStatus');
				$error = !sendEmail($this->user_auth_model->getEmail($this->dataModel->getTeamOwnerId()),$this->user_auth_model->getEmail($this->params['config']['primary_contact']),
				$this->params['config']['site_name']." Administrator",$this->league_model->league_name.' Fantasy League - Trade Update - Offer '.$tradeTypes[$this->data['type']],
				$message,'','email_trd_');
			} // END if (messaging)
		} else {
			$error = true;
			$code = 404;
			$status = "Required parameters were missing.";
		} // END if
		if ($this->data['type'] == TRADE_REJECTED_COUNTER && $this->data['prevTradeId'] != -1) {
			redirect('/team/trade/id/'.$this->data['team_id'].'/prev_trade_id/'.$this->data['prevTradeId']);
		}
		if ($outType == 1) {
			if ($error) { $status = "error:".$status; }
			$result .= '"result":"OK","code":"'.$code.'","status":"'.$status.'"}';
			$this->output->set_header('Content-type: application/json'); 
			$this->output->set_output($result);
		} else {
			
			$this->data['message'] = $this->data['theContent'] = $status;
			$this->params['subTitle'] = "Team Trades";
			$this->data['subTitle'] = "Trade Response";
			$this->params['content'] = $this->load->view($this->data['display_page'], $this->data, true);
			$this->params['pageType'] = PAGE_FORM;
			$this->makeNav();
			$this->displayView();
		} // END if
	}
	/**
	 * 	TRADE OFFER
	 * 	Submits a trade offer to the DB
	 *	
	 *	@since	1.0.4
	 */
	public function tradeOffer() {
		$this->getURIData();
		$this->load->model($this->modelName,'dataModel');
		
		$error = false;
		$result = '{';
		$code= -1;
		$status = '';
		$responseType = 1;
		$displayPage = $this->views['TRADE'];
		
		$sendPlayers = array();
		$team2Id = -1;
		$recievePlayers = array();
		$comments = '';
		$expiresIn = $this->params['config']['defaultExpiration'];
		$prevTradeId = false;
		$league_id = false;
		$team_id = -1;
		$message = "";
		
		$message_list_missing = "The listing of players to be exchanged is missing.";
		
		$this->data['scoring_period'] = $this->getScoringPeriod();
		
		if ($this->input->post('submitted')) {
			$responseType = 2;
			$displayPage = $this->views['TRADE_REVIEW'];
			$this->data['subTitle'] = "Review Trade";
			$this->form_validation->set_rules('id', 'Team id', 'required');
			$this->form_validation->set_rules('team_id2', 'Recieving Team ID', 'required');
			$this->form_validation->set_rules('tradeTo', 'Players to Send', 'required');
			$this->form_validation->set_rules('tradeFrom', 'Players to Recieve', 'required');
			if ($this->form_validation->run() == false) {
				$error = true;
				$message = $message_list_missing;
			} else {
				$sendList = explode("&",$this->input->post('tradeTo'));
				$receiveList = explode("&",$this->input->post('tradeFrom'));
				$this->uriVars['team_id'] = $team_id = $this->input->post('id');
				$team2Id = $this->input->post('team_id2');
				$comments = $this->input->post('comments');
				$expiresIn = $this->input->post('expiresIn');
				$prevTradeId = $this->input->post('prevTradeId');
			}
		} else {
			if (isset($this->uriVars['team_id']) && isset($this->uriVars['team_id2']) &&(isset($this->uriVars['tradeTo']) 
			|| isset($this->uriVars['tradeFrom']))) {
				$sendList = explode("&",$this->uriVars['tradeTo']);
				$receiveList = explode("&",$this->uriVars['tradeFrom']);
				$this->uriVars['team_id'] = $team_id = $this->uriVars['team_id'];
				$team2Id = $this->uriVars['team_id2'];
				if (isset($this->uriVars['prevTradeId'])) {
					$prevTradeId = $this->uriVars['prevTradeId'];
				}
				if (isset($this->uriVars['expiresIn'])) {
					$expiresIn = $this->uriVars['expiresIn'];
				}
			} else {
				$error = true;
				$message = $message_list_missing;
			}
	
		} // END if
		if (!$error) {
			$this->dataModel->load($team_id);
			// TEST ALL PLAYERS ROSTER STATUS
			$rosterMessages = $this->verifyRostersForTrade($team_id, $sendList, $team2Id, $receiveList, $this->data['scoring_period']['id']);
			if (empty($rosterMessages)) {
				$trade_id = $this->dataModel->makeTradeOffer($sendList, $team2Id, $receiveList, $this->data['scoring_period']['id'], $comments, $prevTradeId,$expiresIn,$this->params['config']['defaultExpiration']);
				
				// SEND OFFER EMAILS
				
				if (!isset($this->player_model)) {
					$this->load->model('player_model');
				}
				// PLAYER LISTS
				$send_player_str = "";
				foreach($sendList as $rawInfo) {
                    //print ($rawInfo."<br />");
					$playerDetails = explode("_",$rawInfo);
					$id = $playerDetails[0];
					$id = str_replace(";","",$id);
					if (strpos($id,";") !== false) {
						$idStr = explode(";",$id);
						$id = $idStr[0];
					}
					$playerName = $this->player_model->getPlayerName($id);
				    if (!empty($send_player_str)) { $send_player_str .= "<br />"; }
					$send_player_str .= anchor('/players/info/player_id/'.$id.'/league_id/'.$this->dataModel->league_id,$playerName);
					if ($playerDetails[1] != 'P') {
						$pos = $playerDetails[1];
					} else {
						$pos = $playerDetails[2];
					}
					$send_player_str .= ", ".$pos;
                }
				$recieve_player_str = "";
				foreach($receiveList as $rawInfo) {
					//print ($rawInfo."<br />");
                    $playerDetails = explode("_",$rawInfo);
					$id = $playerDetails[0];
					//echo("raw receive player id before explode = ".$id."<br />");
					$id = str_replace(";","",$id);
					if (strpos($id,";") !== false) {
						$idStr = explode(";",$id);
						$id = $idStr[0];
					}
					//echo("receive player id after explode = ".$id."<br />");
					$playerName = $this->player_model->getPlayerName($id);
				    if (!empty($recieve_player_str)) { $recieve_player_str .= "<br />"; }
					$recieve_player_str .= anchor('/players/info/player_id/'.$id.'/league_id/'.$this->dataModel->league_id,$playerName);
					if ($playerDetails[1] != 'P') {
						$pos = $playerDetails[1];
					} else {
						$pos = $playerDetails[2];
					}
					$recieve_player_str .= ", ".$pos;
                }
				
				// RECIEPT MESSAGE
				$msg = $this->lang->line('team_trade_offer');
				$msg .= $this->lang->line('email_footer');
				$msg = str_replace('[ACCEPTING_TEAM_NAME]', $this->dataModel->getTeamName($team2Id), $msg);
				$msg = str_replace('[OFFERING_TEAM_NAME]', $this->dataModel->getTeamName($team_id), $msg);
				$msg = str_replace('[SEND_PLAYERS]', $send_player_str,$msg);
				$msg = str_replace('[RECEIVE_PLAYERS]', $recieve_player_str,$msg);
				$msg = str_replace('[USERNAME]', getUsername($this->dataModel->getTeamOwnerId($team2Id)), $msg);
				$msg = str_replace('[COMMENTS]', $comments,$msg);
				$msg = str_replace('[TRADE_REVIEW_URL]', anchor('/team/tradeReview/team_id/'.$team2Id.'/league_id/'.$this->dataModel->league_id.'/trans_type/2/trade_id/'.$trade_id,'Review the Trade offer'),$msg);
				$expireStr = "";
				if ($this->params['config']['tradesExpire'] == 1) {
					if ($expiresIn == -1) {
						$expireStr = $this->lang->line('team_trade_expires_message_to_none');
					} else if ($expiresIn == 500) {
						$expireStr = $this->lang->line('team_trade_expires_message_to_next_sim');
					} else {
						$expireStr = str_replace('[EXPIRES]',$expiresIn,$this->lang->line('team_trade_expires_message_to'));
					}
				}
				$msg = str_replace('[EXPIRES]',$expireStr,$msg);

				$msg = str_replace('[LEAGUE_NAME]', $this->league_model->getLeagueName($this->dataModel->league_id),$msg);
				$data['messageBody']= $msg;
				$data['leagueName'] = $this->league_model->league_name;
				$data['title'] = $this->lang->line('team_email_title_trade_offer');
				$emailMess = $this->load->view($this->config->item('email_templates').'general_template', $data, true);
				// SEND TO TEAM ONE
				$subject = str_replace('[LEAGUE_NAME]',$this->league_model->getLeagueName($this->dataModel->league_id),$this->lang->line('team_trade_email_subject_offer_to'));
				$error = !sendEmail($this->user_auth_model->getEmail($this->dataModel->getTeamOwnerId($team2Id)),$this->user_auth_model->getEmail($this->dataModel->getTeamOwnerId($team_id)),
				getUsername($this->dataModel->getTeamOwnerId($team_id)),$subject,$emailMess, '','email_trd_offer_');

				unset($data['messageBody']);
				unset($emailMess);
				unset($msg);
				
				// OFFER CONFIRMATION MESSAGE
				$msg = $this->lang->line('team_trade_offer_confirm');
				$msg .= $this->lang->line('email_footer');
				$msg = str_replace('[ACCEPTING_TEAM_NAME]', $this->dataModel->getTeamName($team2Id), $msg);
				$msg = str_replace('[USERNAME]', getUsername($this->dataModel->getTeamOwnerId($team_id)), $msg);
				$msg = str_replace('[SEND_PLAYERS]', $send_player_str,$msg);
				$msg = str_replace('[RECEIVE_PLAYERS]', $recieve_player_str,$msg);
				$msg = str_replace('[TRADE_REVIEW_URL]', anchor('/team/tradeReview/team_id/'.$team_id.'/league_id/'.$this->dataModel->league_id.'/trans_type/3/trade_id/'.$trade_id,'Trade Review Page'),$msg);
				$expireStr = "";
				if ($this->params['config']['tradesExpire'] == 1) {
					if ($expiresIn == -1) {
						$expireStr = $this->lang->line('team_trade_expires_message_from_none');
					} else if ($expiresIn == 500) {
						$expireStr = $this->lang->line('team_trade_expires_message_from_next_sim');
					} else {
						$expireStr = str_replace('[EXPIRES]',$expiresIn,$this->lang->line('team_trade_expires_message_from'));
					}
				}
				$msg = str_replace('[EXPIRES]',$expireStr,$msg);
				$msg = str_replace('[LEAGUE_NAME]', $this->league_model->getLeagueName($this->dataModel->league_id),$msg);
				$data['messageBody'] = $msg;
				$emailMess = $this->load->view($this->config->item('email_templates').'general_template', $data, true);
				// SEND TO TEAM ONE
				$subject = str_replace('[LEAGUE_NAME]',$this->league_model->getLeagueName($this->dataModel->league_id),$this->lang->line('team_trade_email_subject_offer_from'));
				$error = !sendEmail($this->user_auth_model->getEmail($this->dataModel->getTeamOwnerId()),$this->user_auth_model->getEmail($this->params['config']['primary_contact']),
				$this->params['config']['site_name']." Administrator",$subject,$emailMess,'','/email_trd_confirm_');

				$message .= str_replace('[ACCEPTING_TEAM_NAME]',$this->dataModel->getTeamName($team2Id),$this->lang->line('team_trade_offer_submitted'));
				
			} else {
				$error = true;
				$message = str_replace('[ROSTER_MESSAGES]',$rosterMessages,$this->lang->line('team_trade_offer_roster_error'));
			}
		}
		if ($responseType == 1) {
			if (!$error) {
				$status = $message;
				$code = 200;
			} else {
				$status = "error:".$message;
				$code = 301;
			}
			$result .= '"result":"OK","code":"'.$code.'","status":"'.$status.'"}';
			$this->output->set_header('Content-type: application/json'); 
			$this->output->set_output($result);
		} else {
			$this->data['message'] = $message;
			$this->params['subTitle'] = "Team Trades";
			$this->params['content'] = $this->load->view($displayPage, $this->data, true);
			$this->params['pageType'] = PAGE_FORM;
			$this->makeNav();
			$this->displayView();
		}
	}
	/**
	 * 	TRADE REVIEW
	 * 	Allows users to review trade details and, if status permits, take actions
	 *	
	 *	@since	1.0.4
	 */
	public function tradeReview() {
		$this->getURIData();
		$this->load->model($this->modelName,'dataModel');

		$this->params['subTitle'] = "Team Trades";
		
		if (isset($this->uriVars['team_id'])) { 
			
			$this->dataModel->load($this->uriVars['team_id']);
			$this->data['team_id'] = $this->uriVars['team_id'];
			$this->data['league_id'] = $this->dataModel->league_id;
			
			$this->data['trade_id'] = (isset($this->uriVars['trade_id'])) ? $this->uriVars['trade_id'] : -1;
			
			$sendList = array();
			$receiveList = array();
			$this->data['tradeTo'] = array();
			$this->data['tradeFrom'] = array();
			
			if ($this->data['trade_id'] != -1) {
                // EXPIRATION CHECK FOR EXISTING TRADES ONLY
                if ($this->params['config']['tradesExpire'] == 1) {
                    if ($this->dataModel->getIsTradePastExpiration($this->data['trade_id'])) {
                        $this->dataModel->updateTrade($this->data['trade_id'], TRADE_EXPIRED, $this->lang->line('team_trade_auto_expired'));
                    }
                }
				$trade = $this->dataModel->getTrade($this->data['trade_id']);
				$sendList['all'] = $this->data['tradeTo'] = $trade['send_players'];
				$receiveList['all'] = $this->data['tradeFrom'] = $trade['receive_players'];
			} else { 
				$this->data['tradeTo'] = (isset($this->uriVars['tradeTo'])) ? $this->uriVars['tradeTo'] : "";
				$this->data['tradeFrom'] = (isset($this->uriVars['tradeFrom'])) ? $this->uriVars['tradeFrom'] : "";
				$sendList['all'] = explode("&",$this->data['tradeTo']);
				$receiveList['all'] = explode("&",$this->data['tradeFrom']);
			}
			
			$this->dataModel->load($this->uriVars['team_id']);

			if (!isset($this->params['currUser'])) {
				$this->params['currUser'] = (!empty($this->user_auth_model->id)) ? $this->user_auth_model->id : -1;
			}
			$this->data['trans_type'] = (isset($this->uriVars['trans_type'])) ? $this->uriVars['trans_type'] : 1;
			
			$sendList['ids'] = array();
			$receiveList['ids'] = array();
			foreach($sendList['all'] as $data) {
				$tmpPlayer = explode("_",$data);
				if (strpos($tmpPlayer[0],";") !== false) {
					$idStr = explode(";",$tmpPlayer[0]);
					$tmpPlayer[0] = $idStr[0];
				}
				array_push($sendList['ids'], $tmpPlayer[0]);
			}
			foreach($receiveList['all'] as $data) {
				$tmpPlayer = explode("_",$data);
				if (strpos($tmpPlayer[0],";") !== false) {
					$idStr = explode(";",$tmpPlayer[0]);
					$tmpPlayer[0] = $idStr[0];
				}
				array_push($receiveList['ids'], $tmpPlayer[0]);
			}
			// DETEMRINE WHICH PLAYERS FROM EACH LIST ARE PITCHERS AND BATTERS
			$sendList['pitchers'] = array();
			$receiveList['pitchers'] = array(); 
			$sendList['batters'] = array(); 
			$receiveList['batters'] = array(); 
			
			$this->load->model('player_model');
			foreach($sendList['ids'] as $playerId) {
				$playerData = $this->player_model->getPlayerDetails($playerId);
				//echo("Send player id = ".$playerId.", playerData['position'] ".$playerData['position']."<br />");
				if ($playerData['position'] == 1) {
					$sendList['pitchers'] = $sendList['pitchers'] + array($playerData['player_id']=>array());
				} else {
					$sendList['batters'] = $sendList['batters'] + array($playerData['player_id']=>array());
				}
			}
			
			foreach($receiveList['ids'] as $playerId) {
				$playerData = $this->player_model->getPlayerDetails($playerId);
				//echo("recieve player id = ".$playerId.", playerData['position'] ".$playerData['position']."<br />");
				if ($playerData['position'] == 1) {
					$receiveList['pitchers'] = $receiveList['pitchers'] + array($playerData['player_id']=>array());
				} else {
					$receiveList['batters'] = $receiveList['batters'] + array($playerData['player_id']=>array());
				}
			}
			$this->data['scoring_period'] = $this->getScoringPeriod();
			$this->data['scoring_periods'] = getAvailableScoringPeriods($this->data['league_id']);
			
			// GET STATS FOR PLAYERS IN TRADE OFFER
			$this->prepForQuery();
			if (!isset($this->uriVars['stats_range']) || empty($this->uriVars['stats_range'])) {
				$this->data['stats_range'] = 0;
			} else {
				$this->data['stats_range'] = $this->uriVars['stats_range'];
			}
			if ($this->ootp_league_model->current_date < $this->ootp_league_model->start_date || sizeof($this->data['scoring_periods']) < 1) {
				$this->data['stats_range'] = 1;	
			}
			if (!isset($this->uriVars['stats_source']) || !empty($this->uriVars['stats_source'])) {
				$this->data['stats_source'] = "sp_all";
			} else {
				$this->data['stats_source'] = $this->uriVars['stats_source'];
			}
			$periodForQuery = $this->data['scoring_period']['id'];
			if ($this->data['stats_range'] != 0) {
				$periodForQuery = -1;
			}
			
			// TEAM META DATA FOR DISPLAY
			$this->data['team_id1'] = -1;
			$this->data['team_name1'] = "";
			$this->data['team_avatar1'] = "";
			$this->data['team_id2'] = -1;
			$this->data['team_name2'] = "";
			$this->data['team_avatar2'] = "";
			if (isset($this->uriVars['team_id1']) && !empty($this->uriVars['team_id1']) && $this->uriVars['team_id1'] != -1) {
				$this->data['team_id1'] = $this->uriVars['team_id1'];
				$this->data['team_name1'] = $this->dataModel->getTeamName($this->data['team_id1']);
				$this->data['team_avatar1'] = $this->dataModel->getAvatar($this->data['team_id1']);
			} else if (isset($trade) && isset($trade['trade_id'])) {
				$this->data['team_id1'] = $trade['team_1_id'];
                $this->data['team_name1'] = $this->dataModel->getTeamName($trade['team_1_id']);
				$this->data['team_avatar1'] = $this->dataModel->getAvatar($trade['team_1_id']);
			}
			if (isset($this->uriVars['team_id2']) && !empty($this->uriVars['team_id2']) && $this->uriVars['team_id2'] != -1) {
				$this->data['team_id2'] = $this->uriVars['team_id2'];
				$this->data['team_name2'] = $this->dataModel->getTeamName($this->data['team_id2']);
				$this->data['team_avatar2'] = $this->dataModel->getAvatar($this->data['team_id2']);
			} else if (isset($trade) && isset($trade['trade_id'])) {
				$this->data['team_id2'] = $trade['team_2_id'];
                $this->data['team_name2'] = $this->dataModel->getTeamName($trade['team_2_id']);
				$this->data['team_avatar2'] = $this->dataModel->getAvatar($trade['team_2_id']);
			}
			
			$this->data['limit'] = -1;
			$this->data['startIdx'] = 0;
			$this->data['showTeam'] = -1;
			$this->data['title']['pitchers'] = "Pitching";
			$this->data['colnames']=player_stat_column_headers(2, QUERY_STANDARD, $this->rules['scoring_type'] == LEAGUE_SCORING_HEADTOHEAD, false, false, false, false, $this->rules['scoring_type'] != LEAGUE_SCORING_HEADTOHEAD);
			$this->data['fields'] = player_stat_fields_list(2, QUERY_STANDARD, $this->rules['scoring_type'] == LEAGUE_SCORING_HEADTOHEAD, false, false, false, false, $this->rules['scoring_type'] != LEAGUE_SCORING_HEADTOHEAD);
			if (sizeof($receiveList['pitchers']) > 0) {
				$stats['team_id2']['pitchers'] = $this->dataModel->getTeamStats(false,$this->data['team_id2'], 2, NULL,NULL,$this->data['stats_range'],$periodForQuery,0,-1,0,$this->ootp_league_model->league_id,$this->ootp_league_model->current_date,$this->rules,$receiveList['pitchers']);
				$this->data['player_stats'] = formatStatsForDisplay($stats['team_id2']['pitchers'], $this->data['fields'], $this->params['config'],$this->data['league_id']);
				$this->data['formatted_stats']['team_id2']['pitchers'] = $this->load->view($this->views['STATS_TABLE'], $this->data, true);
			}
			if (sizeof($sendList['pitchers']) > 0) {
				$stats['team_id1']['pitchers'] = $this->dataModel->getTeamStats(false,$this->data['team_id2'], 2, NULL,NULL,$this->data['stats_range'],$periodForQuery,0,-1,0,$this->ootp_league_model->league_id,$this->ootp_league_model->current_date,$this->rules,$sendList['pitchers']);
				$this->data['player_stats'] = formatStatsForDisplay($stats['team_id1']['pitchers'], $this->data['fields'], $this->params['config'],$this->data['league_id']);
				$this->data['formatted_stats']['team_id1']['pitchers'] = $this->load->view($this->views['STATS_TABLE'], $this->data, true);
			}
			// BATTERS
			$this->data['title']['batters']  = "Batting";
			$this->data['colnames']=player_stat_column_headers(1, QUERY_STANDARD, $this->rules['scoring_type'] == LEAGUE_SCORING_HEADTOHEAD, false, false, false, false, $this->rules['scoring_type'] != LEAGUE_SCORING_HEADTOHEAD);
			$this->data['fields'] = player_stat_fields_list(1, QUERY_STANDARD, $this->rules['scoring_type'] == LEAGUE_SCORING_HEADTOHEAD, false, false, false, false, $this->rules['scoring_type'] != LEAGUE_SCORING_HEADTOHEAD);
			
			if (sizeof($receiveList['batters']) > 0) {
				$stats['team_id2']['batters'] = $this->dataModel->getTeamStats(false,$this->data['team_id1'], 1, NULL,NULL,$this->data['stats_range'],$periodForQuery,0,-1,0,$this->ootp_league_model->league_id,$this->ootp_league_model->current_date,$this->rules,$receiveList['batters']);
				$this->data['player_stats'] = formatStatsForDisplay($stats['team_id2']['batters'], $this->data['fields'], $this->params['config'],$this->data['league_id']);
				$this->data['formatted_stats']['team_id2']['batters']= $this->load->view($this->views['STATS_TABLE'], $this->data, true);
			}
			// BATTERS
			if (sizeof($sendList['batters']) > 0) {
				$stats['team_id1']['batters'] = $this->dataModel->getTeamStats(false,$this->data['team_id1'], 1, NULL,NULL,$this->data['stats_range'],$periodForQuery,0,-1,0,$this->ootp_league_model->league_id,$this->ootp_league_model->current_date,$this->rules,$sendList['batters']);
				$this->data['player_stats'] = formatStatsForDisplay($stats['team_id1']['batters'], $this->data['fields'], $this->params['config'],$this->data['league_id']);
				$this->data['formatted_stats']['team_id1']['batters']= $this->load->view($this->views['STATS_TABLE'], $this->data, true);
			}
			
			if ($this->data['trans_type'] == 4 || $this->data['trans_type'] == 5) {
				$this->data['protests'] = $this->dataModel->getTradeProtests($this->data['league_id']);
			}
			$this->data['comments']  = (isset($trade['comments'])) ? $trade['comments'] : "";
			$this->data['response']  = (isset($trade['response'])) ? $trade['response'] : "";
			$this->data['status']  = (isset($trade['status'])) ? $trade['status'] : -1;
            $this->data['statusStr']  = (isset($trade['tradeStatus'])) ? $trade['tradeStatus'] : "";
            $this->data['expiration_days']  = (isset($trade['expiration_days'])) ? $trade['expiration_days'] : -1;
            $this->data['offer_date']  = (isset($trade['offer_date'])) ? $trade['offer_date'] : EMPTY_DATE_TIME_STR;

			$this->data['subTitle'] = "Review Trade";
			$this->params['content'] = $this->load->view($this->views['TRADE_REVIEW'], $this->data, true);
			$this->params['pageType'] = PAGE_FORM;
		} else {
			$message = "error:Required params missing";
			$this->data['subTitle'] = "Trade Error";
			$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
		}
		$this->makeNav();
		$this->displayView();
	}
	/*-------------------------------------------
	/
	/	TRANSACTIONS
	/
	/------------------------------------------*/
	/**
	 * TRANSACTIONS
	 * Draws the main transaction summary for the current team
	 */
	public function transactions() {
		$this->getURIData();
		$this->load->model($this->modelName,'dataModel');
		$team_id =-1;
		if(isset($this->uriVars['id'])) {
			$league_id = $this->uriVars['id'];
		} else if (isset($this->uriVars['team_id'])) {
			$team_id =$this->uriVars['team_id'];
		}
		$this->data['team_id'] = $team_id;
		$this->dataModel->load($team_id);
		$this->league_model->load($this->dataModel->league_id);
		$this->data['league_id'] = $this->dataModel->league_id;
		
		if (isset($this->data['league_id']) && $this->data['league_id'] != -1) {
			$this->data['thisItem']['fantasy_teams'] = getFantasyTeams($this->dataModel->league_id);
		}
		$this->data['limit'] = $limit = (isset($this->uriVars['limit'])) ? $this->uriVars['limit'] : 20;
		$this->data['pageId'] = $pageId = (isset($this->uriVars['pageId'])) ? $this->uriVars['pageId'] : 1;																			   
		
		$startIndex = 0;
		if ($limit != -1) {
			$startIndex = ($limit * ( - 1))-1;
		}
		if ($startIndex < 0) { $startIndex = 0; }
		$this->data['startIndex'] = $startIndex;
		$this->data['thisItem']['teamList'] = $this->league_model->getTeamDetails();
		$this->data['recCount'] = sizeof($this->league_model->getLeagueTransactions(-1, 0,$this->dataModel->id,$this->dataModel->league_id));
		$this->data['thisItem']['transactions'] = $this->league_model->getLeagueTransactions($this->data['limit'],$this->data['startIndex'],$this->dataModel->id,$this->dataModel->league_id);
		//echo("Transaction count = ".sizeof($this->data['thisItem']['transactions'])."<br />");
		$this->data['pageCount'] = 1;
		if ($limit != -1) {
			$this->data['pageCount'] = intval($this->data['recCount'] / $limit);
		}
		if ($this->data['pageCount'] < 1) { $this->data['pageCount'] = 1; }
		$this->params['subTitle'] = "Transactions";
		
		$this->data['thisItem']['subTitle'] = $this->dataModel->teamname." ".$this->dataModel->teamnick." Transactions";
			
		
		$this->makeNav();
		$this->data['transaction_summary'] = $this->load->view($this->views['TRANSACTION_SUMMARY'], $this->data, true);
		$this->params['content'] = $this->load->view($this->views['TRANSACTIONS'], $this->data, true);
	    $this->params['pageType'] = PAGE_FORM;
		$this->displayView();	
	}
	/**
	 * PROCESS TRANSACTION
	 * Enter description here ...
	 */
	public function processTransaction() {
		$this->getURIData();
		$this->load->model($this->modelName,'dataModel');
		
		if (!function_exists('getCurrentScoringPeriod')) {
			$this->load->helper('admin');
		}
		$error = false;
		$result = '{';
		$code= -1;
		$status = '';
		if (isset($this->uriVars['team_id']) && (isset($this->uriVars['add']) 
			|| isset($this->uriVars['drop']))) {
			
			$this->dataModel->load($this->uriVars['team_id']);
			
			if (!isset($this->params['currUser'])) {
				$this->params['currUser'] = (!empty($this->user_auth_model->id)) ? $this->user_auth_model->id : -1;
			}
			$addList = (isset($this->uriVars['add'])) ? explode("&",$this->uriVars['add']) : array();
			$dropList = (isset($this->uriVars['drop'])) ? explode("&",$this->uriVars['drop']) : array();
			//$tradeToList = isset($this->uriVars['tradeTo']) ? (strpos($this->uriVars['tradeTo'],"&") ? explode("&",$this->uriVars['tradeTo']) : array()) : '';	
			//$tradeFromList = isset($this->uriVars['tradeFrom']) ? (strpos($this->uriVars['tradeFrom'],"&") ? explode("&",$this->uriVars['tradeFrom']) : array()) : '';																  
																		  
			$teamRoster = $this->dataModel->getBasicRoster($this->params['config']['current_period']);
			
			$addSQL = array();
			$waiverSQL = array();
			$alreadyOnWaivers = array();
			$addStatusStr = "";
			//echo("Size of add list = ".sizeof($addList)."<br />");
			foreach($addList as $player) {
				$addError = false;
				$props = explode("_",$player);
				//echo("Player id = ".$props[0]."<br />");
				//echo("Size of teamRoster = ".sizeof($teamRoster)."<br />");
				if ($props[0] != -1) {
					foreach($teamRoster as $teamPlayer) {
						if ($props[0] == $teamPlayer['id']) {
							$addError = true;
							if (empty($addStatusStr)) { $addStatusStr = "addError:"; }
							if ($addStatusStr != "addError:") { $addStatusStr .= ","; }
							$addStatusStr .= $props[0];
							break;
						}
					}
					//echo("on team roster? = ".(($addError) ? 'true' : 'false')."<br />");
					if (!$addError) {
						$onWaivers = -1;
						$waiverClaims = array();
						if ($this->useWaivers) {
							// CHECK IF PLAYER IS ON WAIVERS
							if (!isset($this->player_model)) {
								$this->load->model('player_model');
							}
							$onWaivers = $this->player_model->getWaiverStatus($this->dataModel->league_id,$props[0]);
							$waiverClaims = $this->player_model->getWaiverClaims($this->dataModel->league_id,$props[0]);
						}
						//echo("on waivers? = ".$onWaivers."<br />");
						if ($onWaivers == -1) {
							$pos = get_pos_num($props[1]);
							if ($pos == 7 || $pos == 8 || $pos == 9) { $pos = 20; }
							else if ($pos == 13) { $pos = 12; }
							array_push($addSQL, array('player_id'=>$props[0],'league_id'=>$this->dataModel->league_id,
													  'team_id'=>$this->dataModel->id,'scoring_period_id'=>$this->params['config']['current_period'],
													  'player_position'=>$pos,'player_role'=>get_pos_num($props[2]),'player_status'=>-1));	
						} else {
							// CHECK FOR EXISTING CLAIM
							if (sizeof($waiverClaims) == 0 || (sizeof($waiverClaims) > 0 && !in_array($this->dataModel->id,$waiverClaims))) {
								// CREATE A WAIVER CLAIM	
								array_push($waiverSQL, array('player_id'=>$props[0],'league_id'=>$this->dataModel->league_id,
														 'team_id'=>$this->dataModel->id,'owner_id'=>$this->params['currUser']));
							} else {
								array_push($alreadyOnWaivers, $props[0]);
							} // END if
						} // END if
					} // END if
				} // END if
			} // END foreach
			//echo("Size of addSQL = ".sizeof($addSQL)."<br />");
			//echo("addStatusStr = '".$addStatusStr."'<br />");
			//echo("Size of drop list = ".sizeof($dropList)."<br />");
			$dropSQL = array();
			foreach($dropList as $player) {
				$props = explode("_",$player);
				if ($props[0] != -1) {
					array_push($dropSQL, array('player_id'=>$props[0],'team_id'=>$this->dataModel->id,
											   'scoring_period_id'=>$this->params['config']['current_period']));
				} // END if
			} // END foreach
			
			if (!function_exists('updateOwnership')) {
				$this->load->helper('roster');
			} // END if
			//echo("Size of dropSQL = ".sizeof($dropSQL)."<br />");
			if (empty($addStatusStr)) {
				$playersAdded = array();
				foreach($addSQL as $data) {
					$this->db->insert('fantasy_rosters',$data);
					if ($this->db->affected_rows() == 1) {
						array_push($playersAdded,$data['player_id']);
					}
					$ownership = updateOwnership($data['player_id']);
					$pData = array('own'=>$ownership[0],'start'=>$ownership[1]);
					$this->db->flush_cache();
					$this->db->where('id',$data['player_id']);
					$this->db->update('fantasy_players',$pData); 
				} // END foreach
				$waiverClaims = array();
				foreach($waiverSQL as $data) {
					$this->db->insert('fantasy_teams_waiver_claims',$data);
					if ($this->db->affected_rows() == 1) {
						array_push($waiverClaims,$data['player_id']);
					}
				}
				$playersDropped = array();
				foreach($dropSQL as $data) {
					$this->db->delete('fantasy_rosters',$data);
					if ($this->db->affected_rows() == 1) {
						array_push($playersDropped,$data['player_id']);
					}
					// IF WAIVER ENABLED, PUT PLAYER ON WAIVERS
					if ($this->useWaivers) {
						$waiverData = array('player_id'=>$data['player_id'],'league_id'=>$this->dataModel->league_id,
											'waiver_period'=>$this->params['config']['current_period']+1);
						$this->db->insert('fantasy_players_waivers',$waiverData);
					}
					$ownership = updateOwnership($data['player_id']);
					$pData = array('own'=>$ownership[0],'start'=>$ownership[1]);
					$this->db->flush_cache();
					$this->db->where('id',$data['player_id']);
					$this->db->update('fantasy_players',$pData); 
				}
				
				// LOG THE TRANSACTION
				$this->league_model->load($this->dataModel->league_id);
				$this->dataModel->logTransaction($playersAdded, $playersDropped, NULL, NULL, NULL, $this->league_model->commissioner_id, 
												 $this->params['currUser'], $this->params['accessLevel'] == ACCESS_ADMINISTRATE,
												 $this->params['config']['current_period']);
				
				if ($this->useWaivers) {
					if (!isset($this->player_model)) {
						$this->load->model('player_model');
					}
					 if (sizeof($waiverClaims) > 0) {
						$status = "notice:Your transaction was completed. Waivers claims were made for the following players ";
						$playerStr = "";
						foreach($waiverClaims as $playerId) {
							if (!empty($playerStr)) { $playerStr .= ";"; }
							$playerData = $this->player_model->getPlayerDetails($playerId);
							$playerStr .= get_pos($playerData['position'])." ".$playerData['first_name']." ".$playerData['last_name'];
						}
						$status .= $playerStr;
					}
					if (sizeof($alreadyOnWaivers) > 0) {
						if (empty($status)) { $status = "notice:Your transaction was completed. "; }
						$status .= "You already have waiver claims pending for the following players ";
						$playerStr = "";
						foreach($alreadyOnWaivers as $playerId) {
							if (!empty($playerStr)) { $playerStr .= ";"; }
							$playerData = $this->player_model->getPlayerDetails($playerId);
							$playerStr .= get_pos($playerData['position'])." ".$playerData['first_name']." ".$playerData['last_name'];
						}
						$status .= $playerStr;
					}
				}
				if (empty($status)) { $status = "OK"; }
				//echo("status = '".$status."'<br />");
				$code = 200;
			} else {
				$error = true;
				$code = 301;
				$status = $addStatusStr;
			}
		} else {
			$status = "error:Required params missing";
			$code = 501;
			$error = true;
		}
		if (!$error) {
			$result = $this->refreshPlayerList().',';
		}
		$result.='"code":"'.$code.'","status":"'.$status.'","testing":"this"}';
		$this->output->set_header('Content-type: application/json');
		$this->output->set_output($result);
	}
	/**
	 * 
	 *	SET LINEUP.
	 *	Calls the add/drop page interface for teams.
	 *
	 */
	public function setLineup() {
		$this->getURIData();
		$this->data['subTitle'] = "Set lineup";
		$this->load->model($this->modelName,'dataModel');
		$this->dataModel->load($this->uriVars['id']);
		$this->data['league_id'] = $this->dataModel->league_id;
		
		$error = false;
		$rosterError = false;
		$roster = $this->dataModel->applyRosterChanges($this->input,$this->getScoringPeriod(),$this->dataModel->id);
		if (!isset($roster)) {
			$error = true;
		} else {
			if (!$this->league_model->validateRoster($roster,$this->dataModel->league_id)) {
				$rosterError = $this->league_model->statusMess;
			} // END if
			$error = !$this->dataModel->saveRosterChanges($roster,$this->getScoringPeriod());
			
		} // END if
		if ($error || $rosterError) {
			if ($rosterError) { $error = "<b>Your Rosters are currently illegal! Your team will score 0 points until roster errors are corrected.</b>".$rosterError; } 
			$this->data['message'] = $error;
			$this->data['messageType'] = 'error';
		} else {
			$this->data['message'] = "Your lineups have been successfully updated.";
			$this->data['messageType'] = 'success';
		} // END if
		//$this->session->set_flashdata('message', '<span class="'.$this->data['messageType'].'">'.$this->data['message'].'</span>');
		redirect('team/lineup/'.$this->uriVars['id']);
	}
	
	/**
	 *	Add/DROP Page.
	 *	Calls the add/drop page interface for teams.
	 */
	public function addDrop() {
		$this->getURIData();
		
		$this->enqueStyle('list_picker.css');
		
		$this->load->model($this->modelName,'dataModel');
		$this->dataModel->load($this->uriVars['id']);
		$this->data['team_id'] = $this->uriVars['id'];
		$this->data['league_id'] = $this->dataModel->league_id;
		
		// GET DRAFT STATUS
		$this->load->model('draft_model');
		$this->draft_model->load($this->dataModel->league_id,'league_id');
		$this->data['subTitle'] = "Add/Drop Players";
		
		if (!isset($this->league_model)) { $this->load->model('league_model'); }
		$this->league_model->load($this->dataModel->league_id);
		$isAdmin = ($this->params['accessLevel'] == ACCESS_ADMINISTRATE) ? true: false;
		$isCommish = ($this->league_model->userIsCommish($this->params['currUser'])) ? true: false;
		
		if (!$this->params['loggedIn'] || ($this->dataModel->owner_id != $this->params['currUser'] && (!$isAdmin && !$isCommish))) {
			$this->data['theContent'] = "<b>ERROR</b><br /><br />This page is accessible only by the owner of this team.";
			$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
		} else if ($this->draft_model->completed != 1) {
			$this->data['theContent'] = "<b>ERROR</b><br /><br />Your league has not yet completed it's draft. This page will become available once the draft has been completed.";
			$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
		} else {

			if (!function_exists('getCurrentScoringPeriod')) {
				$this->load->helper('admin');
			} // END if
			$this->data['players'] = $this->dataModel->getBasicRoster($this->params['config']['current_period']);
			$this->data['team_name'] = $this->dataModel->teamname." ".$this->dataModel->teamnick;
			
			$this->data['scoring_period'] = $this->getScoringPeriod();

            //print("Scoring Period id = ".$this->data['scoring_period']['id']."<br />")
			$returnVar= 'playerList';
			$this->data['list_type'] = (isset($this->uriVars['list_type'])) ? $this->uriVars['list_type'] : 2;
			if (isset($this->data['list_type']) && $this->data['list_type'] == 2) {
				$returnVar= 'formatted_stats';
			} // END if
			if(isset($this->params['config']['useWaivers']) && $this->params['config']['useWaivers'] == 1) {
				$this->data['waiver_order'] = $this->dataModel->getWaiverOrder();
				$this->data['waiver_claims'] = $this->dataModel->getWaiverClaims();
			} // END if
			
			$this->data[$returnVar] = $this->pullList(true,$this->dataModel->league_id, $this->data['list_type']);
			$this->data['league_id'] = $this->dataModel->league_id;
			$this->params['pageType'] = PAGE_FORM;
			$this->params['content'] = $this->load->view($this->views['ADD_DROP'], $this->data, true);
		} // END if
		$this->makeNav();
		$this->displayView();
	}
	public function removeClaim() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->loadData();
			if (isset($this->uriVars['id'])) {
				$this->db->where('id',$this->uriVars['id']);
				$this->db->delete('fantasy_teams_waiver_claims');
				$message = '<span class="success">The selected waiver claim has been successfully removed.</span>';
			} else {
				$error = true;
				$message = '<span class="error">A required claim identifier was not found. Please go back and try the operation again or contact the site adminitrator to report the problem.</span>';
			} // END if
			$this->session->set_flashdata('message', $message);
			redirect('team/addDrop/id/'.$this->uriVars['team_id']);
		} else {
	        $this->session->set_flashdata('loginRedirect',current_url());	
			redirect('user/login');
	    }	 // END if
	}
	
	public function administrativeAdd() {
		$this->getURIData();
		if (!function_exists('getCurrentScoringPeriod')) {
			$this->load->helper('admin');
		} // END if
		$result = '';
		$code= -1;
		$status = '';
		if (!isset($this->uriVars['team_id']) || !isset($this->uriVars['player_id']) 
			|| !isset($this->uriVars['curr_team']) || !isset($this->uriVars['league_id'])) {
			$status = "error:Required params missing.";
			$code = 501;
		} else {
			// LOG THE DROP TRANSACTION
			if ($this->uriVars['curr_team'] != -1) {
				$this->dataModel->logSingleTransaction($this->uriVars['player_id'], TRANS_TYPE_DROP, $this->league_model->commissioner_id, 
											 $this->params['currUser'], $this->params['accessLevel'] == ACCESS_ADMINISTRATE,
											 $this->uriVars['league_id'],$this->uriVars['curr_team']);
			} // END if
			$update = false;
			$this->db->flush_cache();
			$this->db->select('id');
			$this->db->where('league_id',$this->uriVars['league_id']);
			$this->db->where('player_id',$this->uriVars['player_id']);
			$query = $this->db->get('fantasy_rosters');
			if ($query->num_rows() > 0) {
				 // END if$update = true;
			}
			$query->free_result();
			
			$this->db->flush_cache();
			$this->db->set('team_id',$this->uriVars['team_id']);
			if ($update) {
				$this->db->where('league_id',$this->uriVars['league_id']);
				$this->db->where('player_id',$this->uriVars['player_id']);
				if ($this->uriVars['curr_team'] != -1) {
					$this->db->where('team_id',$this->uriVars['curr_team']);
				} // END if
				$this->db->where('scoring_period_id',$this->params['config']['current_period']);
				$this->db->update('fantasy_rosters');
			} else {
				$this->db->set('league_id',$this->uriVars['league_id']);
				$this->db->set('player_id',$this->uriVars['player_id']);
				$this->db->set('scoring_period_id',$this->params['config']['current_period']);
				$this->db->insert('fantasy_rosters');
			} // END if
			if ($this->db->affected_rows() > 0) {
				if ($this->uriVars['curr_team'] != -1) {
					$this->dataModel->logSingleTransaction($this->uriVars['player_id'], TRANS_TYPE_ADD, $this->league_model->commissioner_id, 
												 $this->params['currUser'], $this->params['accessLevel'] == ACCESS_ADMINISTRATE,
												 $this->uriVars['league_id'],$this->uriVars['team_id']);
				} // END if
			} // END if
			$status = "OK";
			$code = 200;
			$result .= '"result":"OK","code":"'.$code.'","status":"'.$status.'"}';
		}
		$this->output->set_header('Content-type: application/json'); 
		$this->output->set_output($result);
	}
	public function addAndDisplay() {
		
		$this->getURIData();
		if (!function_exists('getCurrentScoringPeriod')) {
			$this->load->helper('admin');
		}
		$result = '';
		$code= -1;
		$status = '';
		
		if (!isset($this->uriVars['team_id']) || !isset($this->uriVars['player_id']) 
			|| !isset($this->uriVars['position'])|| !isset($this->uriVars['role'])) {
			$status = "error:Required params missing";
			$code = 501;
		} else {
			//echo("team_id = ".$this->uriVars['team_id'].", player_id = ".$this->uriVars['player_id']."<br />");
			$onWaivers = -1;
			$waiverClaims = array();
			// IF ENABLED, CHECK WAIVER STATUS
			//echo("use waivers? ".$this->useWaivers."<br />");
			if ($this->useWaivers) {
				// CHECK IF PLAYER IS ON WAIVERS
				if (!isset($this->player_model)) {
					$this->load->model('player_model');
				}
				$onWaivers = $this->player_model->getWaiverStatus($this->uriVars['league_id'],$this->uriVars['player_id']);
				$waiverClaims = $this->player_model->getWaiverClaims($this->uriVars['league_id'],$this->uriVars['player_id']);
			}
			//echo("on waivers? ".$onWaivers."<br />");
			
			if ($this->useWaivers && $onWaivers != -1) {
				// CHECK FOR EXISTING CLAIM
				if (sizeof($waiverClaims) == 0 || (sizeof($waiverClaims) > 0 && !in_array($this->dataModel->id,$waiverClaims))) {
					$this->db->set('team_id',$this->uriVars['team_id']);
					$this->db->set('player_id',$this->uriVars['player_id']);
					$this->db->set('league_id',$this->uriVars['league_id']);
					$this->db->set('owner_id',$this->params['currUser']);
					$this->db->insert('fantasy_teams_waiver_claims');
					$status = 'notice:The player is currently on waivers. A claim has been submitted for your team. It will be processed in waiver period '.$onWaivers;
				} else {
					$status = 'notice:You have already placed a waiver claim for this player. It will be processed in waiver period '.$onWaivers;
				}
				$code = 200;
				$result = '{"code":"'.$code.'","status":"'.$status.'"}';
			} else {
				// CHECK FOR DUPLICATE
				$this->db->select('id');
				$this->db->from('fantasy_rosters');
				$this->db->where('team_id',$this->uriVars['team_id']);
				$this->db->where('player_id',$this->uriVars['player_id']);
				$this->db->where('scoring_period_id',$this->params['config']['current_period']);
				$this->db->limit(1);
				$query = $this->db->get();
				if ($query->num_rows() > 0) {
					$status = "notice:The player is already on this team!";
					$code = 200;
				} else {
					$query->free_result();
					$this->db->flush_cache();
					if (isset($this->uriVars['league_id']) && !empty($this->uriVars['league_id']) && $this->uriVars['league_id'] != -1) {
						$this->db->set('league_id',$this->uriVars['league_id']);
					}
					$this->db->set('team_id',$this->uriVars['team_id']);
					$this->db->set('player_id',$this->uriVars['player_id']);
					$this->db->set('player_position',$this->uriVars['position']);
					$this->db->set('player_role',$this->uriVars['role']);
					$this->db->set('scoring_period_id',$this->params['config']['current_period']);
					$this->db->insert('fantasy_rosters');
					
					if (!function_exists('updateOwnership')) {
						$this->load->helper('roster');
					}
					$ownership = updateOwnership($this->uriVars['player_id']);
					$pData = array('own'=>$ownership[0],'start'=>$ownership[1]);
					$this->db->flush_cache();
					$this->db->where('id',$this->uriVars['player_id']);
					$this->db->update('fantasy_players',$pData); 
					
					// LOG THE TRANSACTION
					$this->dataModel->load($this->uriVars['team_id']);
					$this->league_model->load($this->dataModel->league_id);
					$this->dataModel->logTransaction(array($this->uriVars['player_id']), NULL, NULL, NULL, NULL, $this->league_model->commissioner_id, 
													 $this->params['currUser'], $this->params['accessLevel'] == ACCESS_ADMINISTRATE,
													 $this->params['config']['current_period']);
					
					$status = "OK";
					$code = 200;
				}
				$result = $this->refreshPlayerList().',"code":"'.$code.'","status":"'.$status.'"}';
			}
		}
		$this->output->set_header('Content-type: application/json'); 
		$this->output->set_output($result);
	}
	public function removeAndDisplay() {
		$this->getURIData();
		$result = '';
		$status = '';
		if (!function_exists('getCurrentScoringPeriod')) {
			$this->load->helper('admin');
		}
		if (!isset($this->uriVars['team_id']) || !isset($this->uriVars['player_id'])) {
			$status = "error: Required params missing";
			$code = 501;
		} else {
			// CHECK FOR DUPLICATE
			$this->db->where('team_id',$this->uriVars['team_id']);
			$this->db->where('player_id',$this->uriVars['player_id']);
			$this->db->where('scoring_period_id',$this->params['config']['current_period']);
			$this->db->delete('fantasy_rosters');
			
			// IF WAIVER ENABLED, PUT PLAYER ON WAIVERS
			if ($this->useWaivers) {
				$this->dataModel->load($this->uriVars['team_id']);
				$waiverData = array('player_id'=>$this->uriVars['player_id'],'league_id'=>$this->dataModel->league_id,
									'waiver_period'=>$this->params['config']['current_period']+1);
				$this->db->insert('fantasy_players_waivers',$waiverData);
			}
					
			if (!function_exists('updateOwnership')) {
				$this->load->helper('roster');
			}
			$ownership = updateOwnership($this->uriVars['player_id']);
			$pData = array('own'=>$ownership[0],'start'=>$ownership[1]);
			$this->db->flush_cache();
			$this->db->where('id',$this->uriVars['player_id']);
			$this->db->update('fantasy_players',$pData);
			
			// LOG THE TRANSACTION
			$this->dataModel->load($this->uriVars['team_id']);
			$this->league_model->load($this->dataModel->league_id);
			$this->dataModel->logTransaction(NULL, array($this->uriVars['player_id']), NULL, NULL, NULL, $this->league_model->commissioner_id, 
												 $this->params['currUser'], $this->params['accessLevel'] == ACCESS_ADMINISTRATE,
												 $this->params['config']['current_period']);
					
			$status = "OK";
			$code = 200;
		}
		
		$result = $this->refreshPlayerList().',"code":"'.$code.'","status":"'.$status.'"}';
		$this->output->set_header('Content-type: application/json'); 
		$this->output->set_output($result);
	}

	public function pullList($returnArray = false, $league_id = false, $list_type = 1) {
		$this->getURIData();
		$result = "";
		if (!isset($this->uriVars['league_id']) && $league_id != false) {
			$this->uriVars['league_id'] = $league_id;
		}
		if (!isset($this->uriVars['type']) || empty($this->uriVars['type'])) {
			$this->uriVars['type'] = "pos";
		}
		if (!isset($this->uriVars['param']) || empty($this->uriVars['param'])) {
			$this->uriVars['param'] = 2;
		}
		if (!isset($this->uriVars['list_type']) || empty($this->uriVars['list_type'])) {
			$this->uriVars['list_type'] = $list_type;
		}
		if (!isset($this->uriVars['limit']) || empty($this->uriVars['limit'])) {
			$this->uriVars['limit'] = -1;
		}
		if (!isset($this->uriVars['startIndex']) || empty($this->uriVars['startIndex'])) {
			$this->uriVars['startIndex'] = 0;
		}
		if (!isset($this->uriVars['pageId']) || empty($this->uriVars['pageId'])) {
			$this->uriVars['pageId'] = 1;
		}
		
		$this->data['list_type'] = $this->uriVars['list_type'];
		if (!function_exists('getFilteredFreeAgents')) {
			$this->load->helper('roster');
		}
		if ($this->uriVars['list_type'] == 2) {
			$this->prepForQuery();
			$player_type = 1;
			$title = "Batters";
			if ($this->uriVars['param'] == 11 || $this->uriVars['param'] == 12 || $this->uriVars['param'] == 13) {
				$player_type = 2;
				$title = "Pitchers";
			}
			$this->data['title']=$title;
			$this->data['limit']=$this->uriVars['limit'];
			$this->data['startIndex']=$this->uriVars['startIndex'];
			$this->rules = $this->league_model->getScoringRules($this->uriVars['league_id'],$this->league_model->getScoringType($this->uriVars['league_id']),true);
			$this->data['colnames']=player_stat_column_headers($player_type, QUERY_BASIC, $this->rules['scoring_type'] == LEAGUE_SCORING_HEADTOHEAD, false, true, false, false, $this->rules['scoring_type'] != LEAGUE_SCORING_HEADTOHEAD);
			$this->data['fields'] = player_stat_fields_list($player_type, QUERY_BASIC, $this->rules['scoring_type'] == LEAGUE_SCORING_HEADTOHEAD, false, true, false, false, $this->rules['scoring_type'] != LEAGUE_SCORING_HEADTOHEAD);
		} else {
			$this->data['fields'] = array('id','player_name','pos','position','role','injury_is_injured','injury_dl_left');
		}		
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
		
		$results = getFreeAgentList($this->uriVars['league_id'], $this->uriVars['type'], 
								   $this->uriVars['param'], $this->params['config']['current_period'], 
								   $this->uriVars['list_type'],true,$this->scoring_period,$this->rules,
								   $this->uriVars['limit'],$this->uriVars['startIndex'], 
								   $this->params['config']['ootp_league_id'],$this->data['lgyear']);
		//echo("size of resault = ".sizeof($results)."<br />");
		if ($this->uriVars['list_type'] == 2) {
			$statsOnly = false;
			$showTrans = true;
			if ($returnArray !== true) {
				$statsOnly = true;
				$showTrans = false;
			}
			//echo("returnArray = ".((!$returnArray) ? "true" : "false")."<br />");
			//echo("stats only = ".(($statsOnly) ? "true" : "false")."<br />");
			//echo("showTrans = ".(($showTrans) ? "true" : "false")."<br />");
			$stats_results = formatStatsForDisplay($results, $this->data['fields'], $this->params['config'],$this->uriVars['league_id'],NULL,NULL,$statsOnly, $showTrans);
			if ($returnArray === true) {
				$this->data['player_stats'] = $stats_results;
				$this->data['recCount'] = sizeof(getFreeAgentList($this->uriVars['league_id'], $this->uriVars['type'], 
									   $this->uriVars['param'], $this->params['config']['current_period'], 
									   $this->uriVars['list_type'],true,$this->scoring_period,$this->rules,
									   -1,0,$this->params['config']['ootp_league_id'],true));
				$this->data['pageCount'] = 1;
				$this->data['pageId'] = $this->uriVars['pageId'];
				if ($this->uriVars['limit'] != -1) {
					$this->data['pageCount'] = intval($this->data['recCount'] / $this->uriVars['limit']);
				}
				$this->data['league_id'] = $this->uriVars['league_id'];
				$this->data['showTeam'] = -1;
				$this->data['showTrans'] = 1;			
				$stats_results = $this->load->view($this->views['STATS_TABLE'], $this->data, true);
			}
		} else {
			$stats_results = $results;
		}
		if ($returnArray === true) {
			return $stats_results;
		} else {
			$status = '';
			if (isset($stats_results) && sizeof($stats_results) > 0) {
				foreach ($stats_results as $row) {
					if ($result != '') { $result .= ","; }
					$result .= '{';
					$tmpResult = '';
					foreach($row as $key => $value) {
						if ($this->uriVars['list_type'] == 1 && !strpos($key,"injury")) {
							if ($key == "positions") {
								$value = makeElidgibilityString($value);
							}
							if ($key == "pos") {
								$value = get_pos($value);
							}
						}
						if ($tmpResult != '') { $tmpResult .= ','; }  // END if
						$tmpResult .= '"'.$key.'":"'.$value.'"';
					}
					// MAKE INJURY STRING
					$injStatus = "";
					if (isset($row['injury_is_injured']) && $row['injury_is_injured'] == 1) {
						$injStatus = makeInjuryStatusString($row);
					}
					$tmpResult .= ',"injStatus":"'.$injStatus.'"';	   
					$result .= $tmpResult.'}';
				} // END foreach
				if ($this->uriVars['list_type'] == 2) {
					$code = 300;
					$status .= 'stats:'.$this->data['colnames'].':';
				} else {
					$code = 200;
					$status .= "OK";
				}
			} // END if
			if (strlen($result) == 0) {
				$status .= "notice:No players found";
				$code = 201;
			} // END if
			$result = '{ "result": { "items": ['.$result.']},"code":"'.$code.'","status": "'.$status.'"}';
			$this->output->set_header('Content-type: application/json'); 
			$this->output->set_output($result);
		}
	}
	/**
	 *	PLAYER ELIGIBILITY.
	 *	Pulls an array of player info and the number of games played per position. 
	 *
	 *	@since	1.0.6
	 *	@access	public
	 *
	 */
	public function eligibility() {
		
		$this->getURIData();
		$this->data['subTitle'] = "Position Eligibility";
		$this->load->model($this->modelName,'dataModel');
		
		$team_id = -1;
		if (isset($this->uriVars['id']) && !empty($this->uriVars['id']) && $this->uriVars['id'] != -1) {
			$team_id = $this->uriVars['id'];
		} else if (isset($this->uriVars['team_id']) && !empty($this->uriVars['team_id']) && $this->uriVars['team_id'] != -1) {
			$team_id = $this->uriVars['team_id'];
		} 
		$this->dataModel->load($team_id);
		$this->data['team_id'] = $this->dataModel->id;
		$this->data['teamname'] = $this->dataModel->teamname;
		$this->data['teamnick'] = $this->dataModel->teamnick;
		$this->data['avatar'] = $this->dataModel->avatar;
		$this->data['team_id'] = $team_id;	
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
		$this->data['league_id']  = $this->dataModel->league_id;
		
		if (!isset($this->league_model)) {
			$this->load->model('league_model');
		}
		$this->league_model->load($this->data['league_id']);
		$rules = array();
		if ($this->league_model->id != -1) {
			$tmprules = $this->league_model->getRosterRules();
			if (sizeof($tmprules) > 0) {
				foreach($tmprules as $ruleId => $ruleData) {
					if ($ruleId < 100) {
						$rules = $rules + array($ruleId =>$ruleData);
					}
				}
			}
		}
		$this->data['roster_rules'] = $rules;
		$this->data['scoring_period'] = $this->getScoringPeriod();
		$this->data['player_eligibility'] = $this->dataModel->getGamesPlayedByRoster($this->data['team_id'],$this->data['scoring_period']['id'],$this->ootp_league_model->league_id,$this->data['lgyear']);
		$this->data['thisItem']['fantasy_teams'] = getFantasyTeams($this->data['league_id']);
		
		$this->makeNav();
		$this->params['pageType'] = PAGE_FORM;
		$this->params['subTitle'] = $this->dataModel->teamname." ".$this->dataModel->teamnick;
		$this->params['content'] = $this->load->view($this->views['ELIGIBILITY'], $this->data, true);
	    $this->displayView();
	}

	/**
	 *	TEAM STATS.
	 *	Displays a table of stats for the current team. 
	 *
	 *	@since	1.0
	 *	@access	public
	 * 	@param	id 		Team ID
	 *  @param	year	The year to display stats for
	 */
	public function stats() {
		
		$this->getURIData();
		$this->data['subTitle'] = "Set lineup";
		$this->load->model($this->modelName,'dataModel');
		
		$team_id = -1;
		if (isset($this->uriVars['id']) && !empty($this->uriVars['id']) && $this->uriVars['id'] != -1) {
			$team_id = $this->uriVars['id'];
		} else if (isset($this->uriVars['team_id']) && !empty($this->uriVars['team_id']) && $this->uriVars['team_id'] != -1) {
			$team_id = $this->uriVars['team_id'];
		} 
		$this->dataModel->load($team_id);
		$this->data['thisItem']['team_id'] = $this->dataModel->id;
		$this->data['thisItem']['teamname'] = $this->dataModel->teamname;
		$this->data['thisItem']['teamnick'] = $this->dataModel->teamnick;
		$this->data['thisItem']['avatar'] = $this->dataModel->avatar;
		$this->data['team_id'] = $team_id;	
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
		$this->data['year'] = $this->data['lgyear'];
		$this->data['league_id']  = $this->dataModel->league_id;
		
		
		$this->prepForQuery();
		
		$this->data['batters'] = $this->dataModel->getBatters(-1, false, -999);
		$this->data['pitchers'] = $this->dataModel->getPitchers(-1, false, -999);
		
		if (sizeof($this->data['batters']) > 0 && sizeof($this->data['pitchers']) > 0) {
		
			$stats['batters'] = $this->player_model->getStatsforPeriod(1, $this->scoring_period, $this->rules,$this->data['batters']);
			$stats['pitchers'] = $this->player_model->getStatsforPeriod(2, $this->scoring_period, $this->rules,$this->data['pitchers']);
			
			$this->data['title'] = array();
			$this->data['formatted_stats'] = array();
			$this->data['limit'] = -1;
			$this->data['startIndex'] = 0;
			
			$this->data['title']['batters'] = "Batting";
			$this->data['colnames']=player_stat_column_headers(1, QUERY_STANDARD, $this->rules['scoring_type'] == LEAGUE_SCORING_HEADTOHEAD, false, false, false, false, $this->rules['scoring_type'] != LEAGUE_SCORING_HEADTOHEAD);
			$this->data['fields'] = player_stat_fields_list(1, QUERY_STANDARD, $this->rules['scoring_type'] == LEAGUE_SCORING_HEADTOHEAD, false, false, false, false, $this->rules['scoring_type'] != LEAGUE_SCORING_HEADTOHEAD);
			$this->data['player_stats'] = formatStatsForDisplay($stats['batters'], $this->data['fields'], $this->params['config'],$this->data['league_id']);
			$this->data['showTeam'] = -1;
			$this->data['formatted_stats']['batters'] = $this->load->view($this->views['STATS_TABLE'], $this->data, true);
	
			$this->data['title']['pitchers'] = "Pitching";
			$this->data['colnames']=player_stat_column_headers(2, QUERY_STANDARD, $this->rules['scoring_type'] == LEAGUE_SCORING_HEADTOHEAD, false, false, false, false, $this->rules['scoring_type'] != LEAGUE_SCORING_HEADTOHEAD);
			$this->data['fields'] = player_stat_fields_list(2, QUERY_STANDARD, $this->rules['scoring_type'] == LEAGUE_SCORING_HEADTOHEAD, false, false, false, false, $this->rules['scoring_type'] != LEAGUE_SCORING_HEADTOHEAD);
			$this->data['player_stats'] = formatStatsForDisplay($stats['pitchers'], $this->data['fields'], $this->params['config'],$this->data['league_id']);
			$this->data['formatted_stats']['pitchers'] = $this->load->view($this->views['STATS_TABLE'], $this->data, true);
		} else {
			$this->data['message']= "The ".$this->dataModel->teamname." roster is incomplete. No stats are available at this time.";
		}
		$this->data['league_id'] = $this->dataModel->league_id;
		if (isset($this->data['league_id']) && $this->data['league_id'] != -1) {
			$this->data['thisItem']['fantasy_teams'] = getFantasyTeams($this->data['league_id']);
		}
		$this->makeNav();
		$this->params['pageType'] = PAGE_FORM;
		$this->params['subTitle'] = $this->data['subTitle'] = $this->dataModel->teamname." ".$this->dataModel->teamnick." Stats";
		$this->params['content'] = $this->load->view($this->views['STATS'], $this->data, true);
	    $this->displayView();
	}
	
	/*--------------------------------------------
	/
	/	PROTECTED/PRIVATE FUNCTIONS
	/
	/-------------------------------------------*/
	/**
	 *	
	 *	ADD STATS TO PLAYERS LISt
	 *	This function takes stats created by $this->dataModel->getTeamStats() and
	 *	adds them to the Players array as a new 'stats' item.
	 *
	 * 	@param $players	{Array}		Array of players in [id] => {data Array} format
	 *  @param $stats 	{Array}		Array of stats arrays
	 *  @return			{Array}		A copy of the original players array with a stats entry for each matching player
	 *	
	 *	@since	1.0.4
	 */
	protected function addStatsToPlayerList($players = false, $stats = false) {

		if ($players === false || $stats === false) { 
			return false; 
		} else {
			$new_types = array();
			foreach($players as $types => $player_list) {
				$new_players = array();
				$stats4Check = $stats;
				foreach($player_list as $player_id => $player_data) {
					$index = 0;
					$found = false;
					foreach($stats4Check as $statArray) {
						if ($statArray['player_id'] == $player_id) {
							array_splice($statArray, 0, 2);
							$player_data['stats'] = $statArray;
							array_splice($stats4Check, $index, 1);
							break;
						}
						$index++;
					}
					$new_players[$player_id] = $player_data;
				}
				$new_types[$types] = $new_players;
			}
			return $new_types;
		}
	}
	/**
	 *	VERIFY ROSTERS FOR TRADE.
	 *	Checks that players involved in a trade are actually still on the applicable teams active rosters
	 *
	 *	@param	$team_id				The primary trade team ID
	 *	@param	$sendList				Array of players to be sent
	 *	@param	$team2Id				The second team in the trade ID
	 *	@param	$receiveList			Array of players to be received
	 *	@param	$scoring_period_id		Scoring period ID
	 *	@return	$return					Emptry String on success, Message string on error	
	 *	@since							1.0.4 Beta
	 *	@see							tradeResponse
	 */
	protected function verifyRostersForTrade($team_id, $sendList, $team2Id, $receiveList, $scoring_period_id) {
		
		$rosterMessages = "";
		$sendIds = array();
		foreach($sendList as $data) {
			$tmpPlayer = explode("_",$data);
			array_push($sendIds,$tmpPlayer[0]);
		}
		$receiveListIds = array();
		foreach($receiveList as $data) {
			$tmpPlayer = explode("_",$data);
			array_push($receiveListIds,$tmpPlayer[0]);
		}
		$sendRosterStatus = $this->dataModel->getPlayersRosterStatus($sendIds,$scoring_period_id, $team_id);
		foreach($sendRosterStatus as $status) {
			if($status['code'] == 404) {
				$rosterMessages .= $this->dataModel->getTeamName($team_id).": ".$status['message']."<br />";
			}
		}
		$recieveRosterStatus = $this->dataModel->getPlayersRosterStatus($receiveListIds,$scoring_period_id,$team2Id);
		foreach($recieveRosterStatus as $status) {
			if($status['code'] == 404) {
				$rosterMessages .= $this->dataModel->getTeamName($team2Id).": ".$status['message']."<br />";
			}
		}
		return $rosterMessages;
	}
	/**
	 *	PREP FOR QUERY.
	 *	Used during stat generation operations to gather required resource before submitting the 
	 *	query to the DB.
	 *
	 */
	protected function prepForQuery() {
		$this->data['scoring_rules'] = $this->league_model->getScoringRules(0);
		if (!function_exists('getAvailableScoringPeriods')) {
			$this->load->helper('admin');
		}
		$this->data['scoring_periods'] = getAvailableScoringPeriods();
		
		$scoring_period_id = -1;
		if (isset($this->uriVars['scoring_period_id'])) {
			$scoring_period_id = $this->uriVars['scoring_period_id'];
		}
		$this->data['stat_source'] = 'ootp';
		if (isset($this->uriVars['stat_source'])) {
			$this->data['stat_source'] = $this->uriVars['stat_source'];
		}
		if ($this->data['stat_source'] != 'ootp' && $this->data['stat_source'] != 'sp_all') {
			$spid = explode("_",$this->data['stat_source']);
			if (sizeof($spid) > 0) { $scoring_period_id = $spid[1]; }
		}
		$this->scoring_period = array('id'=>-1,'date_start'=>$this->ootp_league_model->start_date,'date_end'=>$this->ootp_league_model->current_date);
		if ($scoring_period_id != -1) {
			$this->scoring_period = $this->data['scoring_periods'][$scoring_period_id-1];
		}
		$this->rules = $this->league_model->getScoringRules($this->dataModel->league_id,$this->league_model->getScoringType($this->dataModel->league_id));
		if (sizeof($this->rules) == 0) {
			$this->rules = $this->league_model->getScoringRules(0);
		}
		
		if (!isset($this->player_model)) {
			$this->load->model('player_model');
		}
	}
	/**
	 *	REFRESH PLAYER LIST.
	 *	Loads an updated roster list for the current team
	 *
	 */
	protected function refreshPlayerList() {
		$result = '';
		if (!function_exists('getCurrentScoringPeriod')) {
			$this->load->helper('admin');
		}
		
		$this->load->model($this->modelName,'dataModel');
		if ($this->dataModel->load($this->uriVars['team_id'])) {
			$players = $this->dataModel->getBasicRoster($this->params['config']['current_period']);
			foreach ($players as $player) {
				if (!empty($result)) { $result .= ','; }
				$pos = '';
				if ($player['player_position'] != 1) {
					$pos = get_pos($player['player_position']); 
				} else {
					$pos = get_pos($player['player_role']); 
				}
				$result .= '{"id":"'.$player['id'].'","player_name":"'.$pos." ".$player['player_name'].'"}';
			}
		}
		$result = '{ "result": { "items": ['.$result.']}';
		return $result;
	}
	/**
	 *	GET URI DATA.
	 *	Parses out an id or other parameters from the uri string
	 *
	 */
	protected function getURIData() {
		parent::getURIData();
		if ($this->input->post('team_id')) {
			$this->uriVars['team_id'] = $this->input->post('team_id');
		} // END if
		if ($this->input->post('curr_team')) {
			$this->uriVars['curr_team'] = $this->input->post('curr_team');
		} // END if
		if ($this->input->post('team_id2')) {
			$this->uriVars['team_id2'] = $this->input->post('team_id2');
		} // END if
		if ($this->input->post('player_id')) {
			$this->uriVars['player_id'] = $this->input->post('player_id');
		} // END if
		if ($this->input->post('league_id')) {
			$this->uriVars['league_id'] = $this->input->post('league_id');
		} // END if
		if ($this->input->post('type')) {
			$this->uriVars['type'] = $this->input->post('type');
		} // END if
		if ($this->input->post('param')) {
			$this->uriVars['param'] = $this->input->post('param');
		} // END if
		if ($this->input->post('position')) {
			$this->uriVars['position'] = $this->input->post('position');
		} // END if
		if ($this->input->post('role')) {
			$this->uriVars['role'] = $this->input->post('role');
		} // END if
		if ($this->input->post('scoring_period_id')) {
			$this->uriVars['scoring_period_id'] = $this->input->post('scoring_period_id');
		} // END if
		if ($this->input->post('stat_source')) {
			$this->uriVars['stat_source'] = $this->input->post('stat_source');
		} // END if
		if ($this->input->post('stats_range')) {
			$this->uriVars['stats_range'] = $this->input->post('stats_range');
		} // END if
		if ($this->input->post('list_type')) {
			$this->uriVars['list_type'] = $this->input->post('list_type');
		} // END if
		if ($this->input->post('add')) {
			$this->uriVars['add'] = $this->input->post('add');
		} // END if
		if ($this->input->post('drop')) {
			$this->uriVars['drop'] = $this->input->post('drop');
		} // END if
		if ($this->input->post('tradeTo')) {
			$this->uriVars['tradeTo'] = $this->input->post('tradeTo');
		} // END if
		if ($this->input->post('tradeFrom')) {
			$this->uriVars['tradeFrom'] = $this->input->post('tradeFrom');
		} // END if
		if ($this->input->post('limit')) {
			$this->uriVars['limit'] = $this->input->post('limit');
		} // END if
		if ($this->input->post('startIndex')) {
			$this->uriVars['startIndex'] = $this->input->post('startIndex');
		} // END if
		if ($this->input->post('pageId')) {
			$this->uriVars['pageId'] = $this->input->post('pageId');
		} // END if
		if ($this->input->post('uid')) {
			$this->uriVars['uid'] = $this->input->post('uid');
		} // END if
		if ($this->input->post('trade_id')) {
			$this->uriVars['trade_id'] = $this->input->post('trade_id');
		} // END if
		if ($this->input->post('trans_type')) {
			$this->uriVars['trans_type'] = $this->input->post('trans_type');
		} // END if
		if ($this->input->post('prev_trade_id')) {
			$this->uriVars['prev_trade_id'] = $this->input->post('prev_trade_id');
		} // END if
		if ($this->input->post('expiresIn')) {
			$this->uriVars['expiresIn'] = $this->input->post('expiresIn');
		} // END if
		
	}
	
	protected function makeForm() {
		$form = new Form();
		
		$form->open('/'.$this->_NAME.'/submit/','detailsForm|detailsForm');
		
		$form->fieldset('Team Details');
		
		$form->text('teamname','Team Name','required|trim',($this->input->post('teamname')) ? $this->input->post('teamname') : $this->dataModel->teamname,array('class','first'));
		$form->br();
		$form->text('teamnick','Nick Name','required|trim',($this->input->post('teamnick')) ? $this->input->post('teamnick') : $this->dataModel->teamnick,array('class','first'));
		$form->br();
		if (!isset($this->league_model)) {
			$this->load->model('league_model');
		}
		$this->league_model->load($this->data['league_id']);
		$scoring_type = $this->league_model->getScoringType();
		if ($this->params['accessLevel'] == ACCESS_ADMINISTRATE) {
			if ($scoring_type == LEAGUE_SCORING_HEADTOHEAD) {
				$form->select('division_id|division_id',listLeagueDivisions($this->dataModel->league_id),'Division',($this->input->post('division_id')) ? $this->input->post('division_id') : $this->dataModel->division_id);
				$form->br();
			} else {
				$form->hidden('division_id',-1);
			}
		}
		$form->fieldset('Draft Settings');
		$responses[] = array('1','Yes');
		$responses[] = array('-1','No');
		$form->fieldset('',array('class'=>'radioGroup'));
		$form->radiogroup ('auto_draft',$responses,'Auto Draft',($this->input->post('auto_draft') ? $this->input->post('auto_draft') : $this->dataModel->auto_draft));
		$form->space();
		$form->fieldset('',array('class'=>'radioGroup'));
		$form->radiogroup ('auto_list',$responses,'Use Draft List',($this->input->post('auto_list') ? $this->input->post('auto_list') : $this->dataModel->auto_list));
		$form->space();
		$form->text('auto_round_x','Auto Draft After Round','number',($this->input->post('auto_round_x')) ? $this->input->post('auto_round_x') : $this->dataModel->auto_round_x);
		$form->span('Set to -1 to disable',array('class'=>'field_caption'));
		$form->space();
		$form->fieldset('',array('class'=>'button_bar'));
		$form->span(' ','style="margin-right:8px;display:inline;"');
		$form->button('Cancel','cancel','button',array('class'=>'button'));
		$form->nobr();
		$form->span(' ','style="margin-right:8px;display:inline;"');
		$form->submit('Submit');
		$form->hidden('submitted',1);
		if ($this->recordId != -1) {
			$form->hidden('mode','edit');
			$form->hidden('id',$this->recordId);
		} else {
			$form->hidden('mode','add');
		}
		$this->form = $form;
		$this->data['form'] = $form->get();
		
		$this->makeNav();
		
	}
	/*----------------------------------------------------------
	/	
	/	SHOW INFO
	/	Team Home Page
	/	Overrides the BaseEditor->showInfo method.
	/	Displayed the Lineup screen since the beggining. 
	/
	/	Since 1.0.3 PROD
	/	@param $template	{String}	A template override if provided
	/	
	/----------------------------------------------------------*/
	protected function showInfo($template = false) {

		$this->data['thisItem']['team_id'] = $this->dataModel->id;
		$this->data['thisItem']['avatar'] = $this->dataModel->avatar;
		$this->data['thisItem']['teamname'] = $this->dataModel->teamname." ".$this->dataModel->teamnick;		
		$this->data['thisItem']['owner_name'] = resolveOwnerName($this->dataModel->owner_id);

		$this->params['subTitle'] = $this->data['thisItem']['teamname'];

		/*--------------------------------------
		/	GET TEAM NEWS
		/-------------------------------------*/
		// GET LATEST NEWS ARTICLE FOR THIS TEAM
		$this->load->model('news_model');
		$news = $this->news_model->getNewsByParams(NEWS_TEAM,$this->dataModel->id);
		if (isset($news) && sizeof($news) > 0) {
			foreach($news as $newsData) {
				$this->data['newsId'] = $newsData['id'];
				$this->data['newsTitle'] = $newsData['news_subject'];
				$this->data['newsBody'] = $newsData['news_body'];
				$this->data['newsImage'] = $newsData['image'];
				$this->data['newsDate'] = $newsData['news_date'];
				$authorName = '';
				$this->db->select('firstName, lastName');
				$this->db->where('userId',$newsData['author_id']);
				$query = $this->db->get('users_meta');
				if ($query->num_rows() > 0) {
					$row = $query->row();
					$authorName = (!empty($row->firstName) && $row->lastName != -1)  ? $row->firstName." ".$row->lastName : 'Unknown Author';
				} // END if
				$query->free_result();
				$this->data['author'] = $authorName;
				break;
			} // END foreach
		}

		if (!function_exists('getScoringPeriod')) {
			$this->load->helper('admin');
		}

		$curr_period_id = 1;
		if (isset($this->uriVars['period_id'])) {
			$curr_period_id = $this->uriVars['period_id'];
			$curr_period = getScoringPeriod($curr_period_id);
		} else {
			$curr_period = $this->getScoringPeriod();
			$curr_period_id = $curr_period['id'];
		}
		$this->data['curr_period'] = $curr_period_id;

		$this->data['league_id']  = $this->dataModel->league_id;
		if (!isset($this->league_model)) {
			$this->load->model('league_model');
		}
		$this->league_model->load($this->data['league_id']);
		
		$this->data['thisItem']['teamList'] = $this->league_model->getTeamDetails();
		$this->data['thisItem']['transactions'] = $this->league_model->getLeagueTransactions(5,0,$this->data['thisItem']['team_id'],$this->data['league_id']);
		$this->data['showEffective']  = -1;
		$this->data['limit']  = -1;
		$this->data['pageCount']  = -1;
		$this->data['recCount']  = -1;
		$this->data['transaction_summary'] = $this->load->view($this->views['TRANSACTION_SUMMARY'], $this->data, true);

		/*---------------------------
		/	GET TOP PLAYERS DATA
		/--------------------------*/
		$scoring_type = $this->league_model->getScoringType();
		$this->rules = $this->league_model->getScoringRules($this->dataModel->league_id,$this->league_model->getScoringType($this->dataModel->league_id));
		if (sizeof($this->rules) == 0) {
			$this->rules = $this->league_model->getScoringRules(0);
		}

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
		$this->data['year'] = $this->data['lgyear'];
		$this->data['league_id']  = $this->dataModel->league_id;

		$this->prepForQuery();
		
		$this->data['batters'] = $this->dataModel->getBatters(-1, false, -999);
		$this->data['pitchers'] = $this->dataModel->getPitchers(-1, false, -999);
		
		if (sizeof($this->data['batters']) > 0 && sizeof($this->data['pitchers']) > 0) {
			$sort = ($scoring_type == LEAGUE_SCORING_HEADTOHEAD) ? 'fpts' : 'rating';
			$stats['batters'] = $this->player_model->getStatsforPeriod(1, $this->scoring_period, $this->rules, $this->data['batters'], null,
			'all', false, QUERY_BASIC, -1, 3, 0, $sort);
			$stats['pitchers'] = $this->player_model->getStatsforPeriod(2, $this->scoring_period, $this->rules, $this->data['pitchers'], null,
			'all', false, QUERY_BASIC, -1, 3, 0,false, $sort);

			$this->data['limit'] = 3;
			$this->data['startIndex'] = 0;
			
			$this->data['batting_fields'] = player_stat_fields_list(1, QUERY_BASIC, $scoring_type == LEAGUE_SCORING_HEADTOHEAD, false, false, false, false, $scoring_type != LEAGUE_SCORING_HEADTOHEAD);	
			$this->data['batter_stats'] = formatStatsForDisplay($stats['batters'], $this->data['batting_fields'], $this->params['config'],$this->data['league_id']);
			
			$this->data['pitcher_fields'] = player_stat_fields_list(2, QUERY_BASIC, $scoring_type == LEAGUE_SCORING_HEADTOHEAD, false, false, false, false, $scoring_type != LEAGUE_SCORING_HEADTOHEAD);
			$this->data['pitcher_stats'] = formatStatsForDisplay($stats['pitchers'], $this->data['pitcher_fields'], $this->params['config'],$this->data['league_id']);
			
		} else {
			$this->data['message']= "The ".$this->dataModel->teamname." roster is incomplete. No stats are available at this time.";
		}
		$this->data['scoring_type'] = $scoring_type;
		// GET UPCOMING GAMES
		$this->data['upcomingOpponent'] = $this->dataModel->getUpcomingGames($curr_period_id);
		// GET MOST RECENT GAMES
		if ($curr_period_id > 1) {
			$curr_period_id = $curr_period_id-1;
		} else {
			$curr_period_id = 1;
		}
		$this->data['standings'] = $this->league_model->getLeagueStandings($curr_period_id);
		
		$this->data['gamePeriod'] = $curr_period_id;
		$this->data['recentGames'] = $this->dataModel->getRecentGames($curr_period_id);

		$isAdmin = ($this->params['accessLevel'] == ACCESS_ADMINISTRATE) ? true: false;
		$isCommish = ($this->league_model->userIsCommish($this->params['currUser'])) ? true: false;
		
		$this->data['isOwner'] = ($this->params['loggedIn'] && ($this->dataModel->owner_id == $this->params['currUser'] || ($isAdmin || $isCommish)));

		if ($this->data['isOwner']) {
			$this->data['userTrades'] = $this->user_meta_model->getTradeOffers($this->params['currUser'],$curr_period_id, $this->dataModel->id);
			if ($this->params['config']['approvalType'] == 2) {
				$this->data['tradesForReview'] = $this->user_meta_model->getTradesForReview($this->params['currUser'],$curr_period_id, $this->dataModel->id);
			}
		}

		// ROSTER STATUS BOX
		if ($this->data['isOwner']) {
			if (!$this->league_model->validateRoster($this->dataModel->getBasicRoster($curr_period_id))) {
				$this->data['message'] = "<b>Your Rosters are currently illegal! Your team will score 0 points until roster errors are corrected.</b><br />".$this->league_model->statusMess;
				$this->data['messageType'] = 'error';
			} else {
				$this->data['message'] = "Your roster is currently valid!";
				$this->data['messageType'] = 'success';
			} // END if
		}

		// INJURED PLAYERS
		$this->data['injured_list'] = $this->player_model->getInjuredPlayers($curr_period, $this->dataModel->id);
		$this->makeNav();

		parent::showInfo();
	}
	/**
	 *	MAKE NAV BAR
	 *
	 */
	protected function makeNav() {
		$navs = array();
		if (!isset($this->league_model)) {
			$this->load->model('league_model');
		}
		$scoring_type = LEAGUE_SCORING_ROTO;
		$this->league_model->load($this->dataModel->league_id);
		if ($this->league_model->id != -1) {
			$league_name = $this->league_model->league_name;
			$scoring_type = $this->league_model->getScoringType();
		} else {
			$league_name = "Unknown League";
		}
		$lg_admin = false;
		if (isset($this->params['currUser']) && ($this->params['currUser'] == $this->league_model->commissioner_id || $this->params['accessLevel'] == ACCESS_ADMINISTRATE)) {
			$lg_admin = true;
		}
		array_push($this->params['subNavSection'], league_nav($this->dataModel->league_id, $league_name,$lg_admin,true,$scoring_type));
		
		$tm_admin = false;
		if (isset($this->params['currUser']) && ($this->params['currUser'] == $this->dataModel->owner_id || $this->params['accessLevel'] == ACCESS_ADMINISTRATE)) {
			$tm_admin = true;
		}
		array_push($this->params['subNavSection'],team_nav($this->dataModel->id,$this->dataModel->teamname." ".$this->dataModel->teamnick, $tm_admin, (($this->params['config']['useTrades'] == 1)?true:false)));
	}
}
/* End of file team.php */
/* Location: ./application/controllers/team.php */