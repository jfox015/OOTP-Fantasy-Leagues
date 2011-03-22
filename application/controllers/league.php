<?php
require_once('base_editor.php');
/**
 *	League.
 *	The primary controller for League manipulation and details.
 *	@author			Jeff Fox
 *	@dateCreated	04/04/10
 *	@lastModified	03/15/11
 *
 */
class league extends BaseEditor {

	/*--------------------------------
	/	VARIABLES
	/-------------------------------*/
	/**
	 *	SLUG.
	 *	@var $_NAME:Text
	 */
	var $_NAME = 'league';
	/*--------------------------------
	/	C'TOR
	/-------------------------------*/
	/**
	 *	Creates a new instance of league.
	 */
	public function league() {
		parent::BaseEditor();
	}
	/**
	 *	INIT.
	 *	Overrides the default init function. Sets page views and sets default values.
	 */
	function init() {
		parent::init();
		$this->modelName = 'league_model';
		
		$this->views['HOME'] = 'league/league_home';
		$this->views['EDIT'] = 'league/league_editor';
		$this->views['VIEW'] = 'league/league_info';
		$this->views['FAIL'] = 'league/league_message';
		$this->views['SUCCESS'] = 'league/league_message';
		$this->views['PENDING'] = 'content_pending';
		$this->views['ADMIN'] = 'league/league_admin';
		$this->views['RULES'] = 'league/league_rules';
		$this->views['RESULTS'] = 'league/league_results';
		$this->views['STANDINGS'] = 'league/league_standings';
		$this->views['SCHEDULE'] = 'league/league_schedule';
		$this->views['SCHEDULE_EDIT'] = 'league/league_schedule_edit';
		$this->views['TRANSACTIONS'] = 'league/league_transactions';
		$this->views['AVATAR'] = 'league/league_avatar';
		$this->views['TEAM_ADMIN'] = 'league/league_team_admin';
		$this->views['INVITE'] = 'league/league_invite_owner';
		$this->views['INVITES'] = 'league/league_invite_list';
		$this->views['WAIVER_CLAIMS'] = 'league/league_waiver_claims';
		$this->views['TRADES'] = 'league/league_trades';
		$this->debug = false;
	}
	/*---------------------------------------
	/	CONTROLLER SUBMISSION HANDLERS
	/--------------------------------------*/
	/**
	 *	INDEX.
	 *	The default handler when the controller is called.
	 *	Checks for an existing auth session, and if found,
	 *	redirects to the dashboard. Otherwise, it redirects 
	 *	to the login.
	 */
	public function index() {
		redirect('search/leagues/');
	}
	/**
	 *	HOME PAGE.
	 */
	public function home() {
		
		$this->enqueStyle('content.css');
		$this->getURIData();
		$this->loadData();
		
		$this->load->model('team_model');
		if (!function_exists('getCurrentScoringPeriod')) {
			$this->load->helper('admin');
		}
		$curr_period = $this->getScoringPeriod();
		if (!empty($curr_period)) {
			$curr_period_id = $curr_period['id'];
			$this->data['curr_period'] = $curr_period;
			// GET GAMES
			$curr_period_id -= 1;
			$games = $this->dataModel->getGamesForPeriod($curr_period_id);
			if (!is_array($games) || sizeof($games) == 0) {
				$games = $this->dataModel->getGamesForPeriod($curr_period_id);
			}
			$this->data['gameList'] = $games;
			$this->data['curr_period_id'] = $curr_period_id;
		}
		$owners = $this->dataModel->getOwnerIds();
		$this->data['isOwner'] = in_array($this->params['currUser'],$owners);
		
		$this->data['league_id'] = $this->dataModel->id;
		$this->params['subTitle'] = $this->dataModel->league_name;
		
		
		$this->data['thisItem']['divisions'] = $this->dataModel->getLeagueStandings();
		
		// DRAFT DASHBOARD 
		$session_auth = $this->session->userdata($this->config->item('session_auth'));
		if ($session_auth && $this->user_meta_model->load($session_auth,'userId')) {
			$userDrafts = $this->user_meta_model->getUserDrafts();
			if (sizeof($userDrafts) > 0) {
				if (!isset($this->draft_model)) {
					$this->load->model('draft_model');
				} // END if
				$userDrafts[$this->dataModel->id]['draftStatus'] = $this->draft_model->getDraftStatus($this->dataModel->id);
				$this->data['userDrafts'] = $userDrafts;
				$this->data['draftDate'] = $this->draft_model->draftDate;
			} // END if
			$userTeams = $this->user_meta_model->getUserTeamIds($this->dataModel->id,$this->params['currUser']);
			if (sizeof($userTeams) > 0) {
				$this->data['user_team_id'] = $userTeams[0];
			} // END if
		} // END if
		
		// GET LATEST NEWS ARTICLE FOR THIS LEAGUE
		$this->load->model('news_model');
		$news = $this->news_model->getNewsByParams(NEWS_LEAGUE,$this->dataModel->id);
		if (isset($news) && sizeof($news) > 0) {
			foreach($news as $newsData) {
				$this->data['newsId'] = $newsData['id'];
				$this->data['subTitle'] = $newsData['news_subject'];
				$this->data['newsBody'] = $newsData['news_body'];
				$this->data['newsImage'] = $newsData['image'];
				$this->data['newsDate'] = $newsData['news_date'];
				break;
			} // END foreach
		} else {
			$this->data['subTitle'] = $this->params['subTitle']. " Home";
		} // END if
		// Setup header Data
		$this->data['thisItem']['teamList'] = $this->dataModel->getTeamDetails();
		$this->data['thisItem']['transactions'] = $this->dataModel->getLeagueTransactions(5);
		$this->data['showEffective']  = -1;
		$this->data['limit']  = -1;
		$this->data['pageCount']  = -1;
		$this->data['recCount']  = -1;
		$this->data['transaction_summary'] = $this->load->view($this->views['TRANSACTION_SUMMARY'], $this->data, true);
		
		$this->data['thisItem']['league_name'] = $this->dataModel->league_name;
		$this->data['thisItem']['description'] = $this->dataModel->description; 
		$this->data['thisItem']['memberCount'] = $this->dataModel->getMemberCount();
		
		$statusStr = '';
		$statusList = loadSimpleDataList('accessType');
		foreach($statusList as $key => $value) {
			if ($this->dataModel->access_type == $key) {
				$statusStr = $value;
				break;
			} // END if
		} // END foreach
		$this->data['thisItem']['accessType'] = $statusStr;	
		
		$leagueTypeStr = '';
		$leagueTypeList = loadSimpleDataList('leagueType');
		foreach($leagueTypeList as $key => $value) {
			if ($this->dataModel->league_type == $key) {
				$leagueTypeStr = $value;
				break;
			} // END if
		} // END foreach
		$this->data['thisItem']['leagueType'] = $leagueTypeStr;
		
		$statusTypeStr = '';
		$statusTypeList = loadSimpleDataList('leagueStatus');
		foreach($statusTypeList as $key => $value) {
			if ($this->dataModel->league_status == $key) {
				$statusTypeStr = $value;
				break;
			} // END if
		} // END foreach
		$this->data['thisItem']['statusType'] = $statusTypeStr;
		
		$this->data['thisItem']['avatar'] = $this->dataModel->avatar;
		
		$this->data['thisItem']['commissionerId'] = $this->dataModel->commissioner_id;
		
		$commishName = '';
		$this->db->select('firstName, lastName');
		$this->db->where('userId',$this->dataModel->commissioner_id);
		$query = $this->db->get('users_meta');
		if ($query->num_rows() > 0) {
			$row = $query->row();
			$commishName = (!empty($row->firstName) && $row->lastName != -1)  ? $row->firstName." ".$row->lastName : '';
		} // END if
		$query->free_result();
		
		$this->data['thisItem']['commissionerName'] = $commishName;
		
		$this->params['content'] = $this->load->view($this->views['HOME'], $this->data, true);
		$this->makeNav();
		$this->params['pageType'] = PAGE_FORM;
		$this->displayView();
	}
	/**
	 *	ADMIN.
	 *	Draws the LEague Admin Dashboard
	 *
	 */
	public function admin() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->load->model($this->modelName,'dataModel');
			$this->load->model('draft_model');
			$this->dataModel->load($this->uriVars['id']);
			if ($this->dataModel->commissioner_id == $this->params['currUser']) {
				$this->data['subTitle'] = "League Admin";
				$this->data['league_id'] = $this->uriVars['id'];
				$this->data['draftStatus'] = $this->draft_model->getDraftStatus($this->dataModel->id);
				$this->data['draftEnabled'] = $this->draft_model->getDraftEnabled($this->dataModel->id);
				$this->data['debug'] = $this->debug;
				$this->makeNav();
				$this->params['content'] = $this->load->view($this->views['ADMIN'], $this->data, true);
			} else {
				$this->data['subTitle'] = "Unauthorized Access";
				$this->data['theContent'] = '<span class="error">You are not authorized to access this page.</span>';
				$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
			}
			$this->displayView();
		} else {
	        $this->session->set_userdata('loginRedirect',current_url());	
			redirect('user/login');
	    }
	}
	/**
	 *	SELECT.
	 *	A function to select a league and set it to a codeigniter session var
	 *	allowing for peristant access of the league ID
	 *
	 */
	public function select() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->load->model($this->modelName,'dataModel');
			$this->dataModel->load($this->uriVars['id']);
			
			// SET LEAGUE ID TO SESSION
			$this->session->set_userdata('league_id',$this->dataModel->id);
			// GET USERS TEAM FOR THIS LEAGUE
			$this->session->set_userdata('team_id',$this->uriVars['team_id']);
			
			redirect('/league/home/'.$this->uriVars['id']);
		} else {
	        $this->session->set_userdata('loginRedirect',current_url());	
			redirect('user/login');
	    }
	}
	/**
	 *	INITALIZE DRAFT.
	 *	Creates a draft entry and sets intial var values for a league draft
	 *
	 */
	public function initlaizeDraft() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			
			$this->load->model($this->modelName,'dataModel');
			$this->load->model('draft_model');
			$this->dataModel->load($this->uriVars['id']);
			
			if ($this->dataModel->commissioner_id == $this->params['currUser']) {
				$this->data['subTitle'] = "Initialize Draft";
				$this->draft_model->load($this->uriVars['id'], 'league_id');
				$this->draft_model->sheduleDraft($this->dataModel->getTeamDetails(), $this->dataModel->id, false, $this->debug);
				$this->draft_model->save();
				$this->admin();
			} else {
				$this->data['subTitle'] = "Unauthorized Access";
				$this->data['theContent'] = '<span class="error">You are not authorized to access this page.</span>';
				$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
				$this->displayView();
			}
		} else {
	        $this->session->set_userdata('loginRedirect',current_url());	
			redirect('user/login');
	    }
	}
	/**
	 *	RESET DRAFT.
	 *	Resets a draft entry to it's intial var values for a league draft
	 *
	 */
	public function resetDraft() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			
			$this->load->model($this->modelName,'dataModel');
			$this->load->model('draft_model');
			$this->dataModel->load($this->uriVars['id']);
			
			if ($this->dataModel->commissioner_id == $this->params['currUser']) {
				$this->data['subTitle'] = "League Admin";
				$this->draft_model->deleteCurrentDraft($this->dataModel->id);
				$this->admin();
			} else {
				$this->data['subTitle'] = "Unauthorized Access";
				$this->data['theContent'] = '<span class="error">You are not authorized to access this page.</span>';
				$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
				$this->displayView();
			}
		} else {
	        $this->session->set_userdata('loginRedirect',current_url());	
			redirect('user/login');
	    }
	}
	/**
	 *	AVATAR.
	 *	Draws the avatar editor page
	 *
	 */
	public function avatar() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->loadData();
			
			if ($this->dataModel->commissioner_id == $this->params['currUser']) {
				$this->data['avatar'] = $this->dataModel->avatar;
				$this->data['league_id'] = $this->dataModel->id;
				$this->data['leagueName'] = $this->dataModel->league_name;
				$this->data['subTitle'] = 'Edit League Avatar';
				
				//echo("Submitted = ".(($this->input->post('submitted')) ? 'true':'false')."<br />");
				if (!($this->input->post('submitted')) || ($this->input->post('submitted') && !isset($_FILES['avatarFile']['name']))) {
					if ($this->input->post('submitted') && !isset($_FILES['avatarFile']['name'])) {
						$fv = & _get_validation_object();
						$fv->setError('avatarFile','The avatar File field is required.');
					}
					$this->params['content'] = $this->load->view($this->views['AVATAR'], $this->data, true);
					$this->params['pageType'] = PAGE_FORM;
					$this->displayView();
				} else {
					if (!(strpos($_FILES['avatarFile']['name'],'.jpg') || strpos($_FILES['avatarFile']['name'],'.jpeg') || strpos($_FILES['avatarFile']['name'],'.gif') || strpos($_FILES['avatarFile']['name'],'.png'))) {
						$fv = & _get_validation_object();
						$fv->setError('avatarFile','The file selected is not a valid image file.');  
						$this->params['content'] = $this->load->view($this->views['AVATAR'], $this->data, true);
						$this->params['pageType'] = PAGE_FORM;
						$this->displayView();
					} else {
						if ($_FILES['avatarFile']['error'] === UPLOAD_ERR_OK) {
							$change = $this->dataModel->applyData($this->input, $this->params['currUser']); 
							if ($change) {
								$this->dataModel->save();
								$this->session->set_flashdata('message', '<p class="success">The image has been successfully updated.</p>');
								redirect('league/info/'.$this->dataModel->id);
							} else {
								$message = '<p class="error">Avatar Change Failed. '.$this->dataModel->statusMess;
								$message .= '</p >';
								$this->data['theContent'] = $message;
								$this->makeNav();
								$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
								$this->displayView();
								//$this->session->set_flashdata('message', $message);
								//redirect('league/avatar');
							}
						} else {
							throw new UploadException($_FILES['avatarFiles']['error']);
						}
					}
				}
			} else {
				$this->data['subTitle'] = "Unauthorized Access";
				$this->data['theContent'] = '<span class="error">You are not authorized to access this page.</span>';
				$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
				$this->displayView();
			}
		} else {
	        $this->session->set_userdata('loginRedirect',current_url());	
			redirect('user/login');
	    }
	}
	public function teamAdmin() {
		if ($this->params['loggedIn']) {
			$this->init();
			$this->getURIData();
			$this->loadData();
			
			if ($this->dataModel->commissioner_id == $this->params['currUser']) {
				if (!isset($this->team_model)) {
					$this->load->model('team_model');
				} // END if
				$teamList = $this->dataModel->getTeamIdList();
				foreach($teamList as $team_id) {
					$this->form_validation->set_rules($team_id.'_teamname', 'Team '.$team_id.' team name', 'required|trim');
					$this->form_validation->set_rules($team_id.'_teamnick', 'Team '.$team_id.' nick name', 'required|trim');
					$this->form_validation->set_rules($team_id.'_division_id', 'Team '.$team_id.' nick name', 'required');
				} // END foreach
				$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
				
				if ($this->form_validation->run() == false) {
					$this->data['thisItem']['divisions'] = $this->dataModel->getFullLeageDetails();
					$this->params['subTitle'] = "League Admin";
					$this->data['subTitle'] = "Edit Teams for ".$this->dataModel->league_name;
					$this->data['league_id'] = $this->dataModel->id;
					$this->data['input'] = $this->input;
					$this->data['config'] = $this->params['config'];
					$this->data['commish_id'] = $this->dataModel->commissioner_id;
					$this->data['max_teams'] = $this->dataModel->max_teams;
					
					$this->params['content'] = $this->load->view($this->views['TEAM_ADMIN'], $this->data, true);
					$this->makeNav();
					$this->params['pageType'] = PAGE_FORM;
					$this->displayView();
				} else {
					$error = false;
					// GET LIST OF TEAM IDs FOR THI LEAGUE
					foreach($teamList as $team_id) {
						if ($this->input->post($team_id."_teamname")) {
							$this->db->set('teamname',$this->input->post($team_id."_teamname"));
						}
						if ($this->input->post($team_id."_teamnick")) {
							$this->db->set('teamnick',$this->input->post($team_id."_teamnick"));
						}
						if ($this->input->post($team_id."_division_id")) {
							$this->db->set('division_id',intval($this->input->post($team_id."_division_id")));
						}						
						$this->db->where('id',intval($team_id));
						$this->db->update('fantasy_teams');
					}
					if ($error) {
						$message = '<p class="error">Team Updates Failed.';
						if ($this->team_model->errorCode != 0) {
							$message .= 'An error has occured. Error: "'.$this->team_model->statusMess.'"</p>';
						}
						$message .= '</p >';
						$this->data['message'] = $message;
						$this->data['thisItem']['divisions'] = $this->dataModel->getFullLeageDetails();
						$this->params['subTitle'] = "League Admin";
						$this->data['subTitle'] = "Edit Teams for ".$this->dataModel->league_name;
						$this->data['league_id'] = $this->dataModel->id;
						$this->data['input'] = $this->input;
						$this->data['config'] = $this->params['config'];
						$this->data['commish_id'] = $this->dataModel->commissioner_id;
						$this->data['max_teams'] = $this->dataModel->max_teams;
						$this->params['content'] = $this->load->view($this->views['TEAM_ADMIN'], $this->data, true);
						$this->makeNav();
						$this->params['pageType'] = PAGE_FORM;
						$this->displayView();
					} else {
						$this->session->set_flashdata('message', '<p class="success">Teams Update successful.</p>');
						redirect('league/admin/'.$this->dataModel->id);
					}
				}
			} else {
				$this->data['subTitle'] = "Unauthorized Access";
				$this->data['theContent'] = '<span class="error">You are not authorized to access this page.</span>';
				$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
				$this->displayView();
			}
		} else {
	        $this->session->set_userdata('loginRedirect',current_url());	
			redirect('user/login');
	    }
	}
	public function rules() {
		$this->getURIData();
		$this->data['subTitle'] = "League Rules";
		$this->load->model($this->modelName,'dataModel');
		$this->dataModel->load($this->uriVars['id']);
		$this->data['league_id'] = $this->uriVars['id'];
		$scoringRules = $this->dataModel->getScoringRules();
		if (isset($scoringRules['batting'])) {
			$this->data['scoring_batting']=	$scoringRules['batting'];
		} 
		if (isset($scoringRules['pitching'])) {
			$this->data['scoring_pitching'] = $scoringRules['pitching'];
		} 
		$this->data['rosters'] = $this->dataModel->getRosterRules();
		
		// DRAFT DETAILS
		if (!isset($this->draft_model)) {
			$this->load->model('draft_model');
		}
		$this->draft_model->load($this->dataModel->id, 'league_id', true);
		if (isset($this->draft_model->draftDate) && !empty($this->draft_model->draftDate)) {
			$this->data['draftDate'] = $this->draft_model->draftDate;
		} else {
			$this->data['draftDate'] = -1;
		}
		if (isset($this->draft_model->nRounds) && $this->draft_model->nRounds != -1) {
			$this->data['draftRounds'] = $this->draft_model->nRounds;
		} else {
			$this->data['draftRounds'] = 0;
		}
		$this->data['draftTimer'] = $this->draft_model->timerEnable;
		$this->data['playoffRounds'] = $this->dataModel->playoff_rounds;
		$this->data['scorePeriods'] = $this->dataModel->regular_scoring_periods;
		
		
		$this->makeNav();
		$this->params['content'] = $this->load->view($this->views['RULES'], $this->data, true);
	    $this->displayView();	
	}
	/**
	 *	LEAGUE STANDINGS
	 *	Draws the current standings for the league
	 */
	public function standings() {
		$this->getURIData();
		$this->data['subTitle'] = "League Standings";
		$this->load->model($this->modelName,'dataModel');
		$this->dataModel->load($this->uriVars['id']);
		$this->data['league_id'] = $this->uriVars['id'];
		
		if (!function_exists('getScoringPeriod')) {
			$this->load->helper('admin');
		}
		if (isset($this->uriVars['period_id'])) {
			$curr_period_id = $this->uriVars['period_id'];
		} else {
			$curr_period_id = $this->params['config']['current_period'] - 1;
		}
		$curr_period = getScoringPeriod($curr_period_id);
		$this->data['curr_period'] = $curr_period_id;
		$this->data['avail_periods'] = $this->dataModel->getAvailableStandingsPeriods();
		$this->data['thisItem']['divisions'] = $this->dataModel->getLeagueStandings();
		$this->data['thisItem']['league_name'] = $this->dataModel->league_name;
		$this->makeNav();
		$this->params['content'] = $this->load->view($this->views['STANDINGS'], $this->data, true);
	    $this->displayView();	
	}
	/**
	 *	LEAGUE STANDINGS
	 *	Draws head to head games results for the league
	 */
	public function results() {
		$this->getURIData();
		$this->data['subTitle'] = "Game Results";
		$this->load->model($this->modelName,'dataModel');
		$this->dataModel->load($this->uriVars['id']);
		$this->data['league_id'] = $this->uriVars['id'];
		
		$this->load->model('team_model');
		
		if (!function_exists('getCurrentScoringPeriod')) {
			$this->load->helper('admin');
		}
		if (isset($this->uriVars['period_id'])) {
			$curr_period_id = 	$this->uriVars['period_id'];
		} else {
			$curr_period_id = $this->params['config']['current_period'];
		}
		$curr_period = getScoringPeriod($curr_period_id);
		$this->data['curr_period'] = $curr_period_id;
		
		$teams = $this->dataModel->getTeamIdList();
		$excludeList = array();
		foreach($teams as $team_id) {
			if (!$this->dataModel->validateRoster($this->team_model->getBasicRoster($curr_period_id,$team_id))) {
				array_push($excludeList,$team_id);
			}
		}
			
		// GET GAMES
		$games = $this->dataModel->getGamesForPeriod($curr_period_id,$excludeList);
		if (!is_array($games) || sizeof($games) == 0) {
			$curr_period_id -= 1;
			$games = $this->dataModel->getGamesForPeriod($curr_period_id,$excludeList);
		}
		
		$game_display_data = array();
		if (sizeof($games) > 0) {
			if (isset($this->uriVars['game_id']) && (!empty($this->uriVars['game_id']) && $this->uriVars['game_id'] != -1)) {
				$game_id = $this->uriVars['game_id'];
			} else {
				 foreach($games as $id => $data) {
					$game_id =  $id;
					break;
				 }
			}
			$game_display_data = $this->dataModel->loadGameData($game_id, $this->team_model,$excludeList);
		}
		
		$this->data['curr_period'] = $curr_period;
		$this->data['avail_periods'] = $this->dataModel->getAvailableScoringPeriods();
		$this->data['games'] = $games;
		$this->data['game_data'] = $game_display_data;
		$this->makeNav();
		$this->params['pageType'] = PAGE_FORM;
		$this->params['content'] = $this->load->view($this->views['RESULTS'], $this->data, true);
	    $this->displayView();
	}
	public function schedule() {
		$this->getURIData();
		$this->data['subTitle'] = "League Schedule";
		$this->load->model($this->modelName,'dataModel');
		$this->dataModel->load($this->uriVars['id']);
		
		$this->data['isCommish'] = $this->dataModel->userIsCommish($this->params['currUser']); 
		$this->data['isAdmin'] = $this->params['loggedIn'] && $this->params['accessLevel'] == ACCESS_ADMINISTRATE; 
		
		// EDIT - 1.0.3 - SCHEUDLE EITING CAPABILITIES
		if ($this->data['isCommish'] || $this->data['isAdmin']) {
			if (!function_exists('getCurrentScoringPeriod')) {
				$this->load->helper('admin');
			}
			if (isset($this->uriVars['period_id'])) {
				$curr_period_id = 	$this->uriVars['period_id'];
			} else {
				$curr_period_id = $this->params['config']['current_period'];
			}
			$this->data['curr_period'] = $curr_period_id;
			$this->data['avail_periods'] = $this->dataModel->getAvailableScoringPeriods();
			$this->data['max_reg_period'] = $this->dataModel->regular_scoring_periods;
			
			$reverse = false;
			$baseTime = strtotime(EMPTY_DATE_STR);
			$timeStart = strtotime($this->ootp_league_model->start_date." 00:00:00");
			$timeCurr = strtotime($this->ootp_league_model->current_date." 00:00:00");
			
			if ($timeStart < $baseTime) { $reverse = true;}

			$this->data['schedleEdit'] = (($reverse) ? ($timeStart <= $timeCurr) : ($timeStart >= $timeCurr));
			$this->data['playoffEdit'] = (($reverse) ? (($timeStart > $timeCurr) && ($curr_period_id > $this->data['max_reg_period'])) : (($timeStart <= $timeCurr) && ($curr_period_id > $this->data['max_reg_period'])));
		}
		
		$this->data['league_id'] = $this->uriVars['id'];
		$this->data['thisItem']['schedule'] = $this->dataModel->getLeagueSchedule();
		$this->data['thisItem']['league_name'] = $this->dataModel->league_name;
		$this->makeNav();
		$this->params['pageType'] = PAGE_FORM;
		$this->params['content'] = $this->load->view($this->views['SCHEDULE'], $this->data, true);
	    $this->displayView();	
	}
	/**
	 * 	EDIT LEAGUE SCHEDULE
	 *
	 *	This function allows for the league commisioner to edit the schedule for a given 
	 *  scoring period.
	 *
	 *	REQUIRED URI VAR PARAMS:
	 *	@param	$league_id	The league ID
	 *	@param	#period_id	The scoring period id to be edited.
	 *
	 *	@since	1.0.3
	 */
	public function scheduleEdit() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->data['subTitle'] = "Edit Schedule";
			$this->load->model($this->modelName,'dataModel');
			$this->dataModel->load($this->uriVars['league_id']);
			
			$this->data['isCommish'] = $this->dataModel->userIsCommish($this->params['currUser']); 
			//$this->data['isAdmin'] = $this->params['loggedIn'] && $this->params['accessLevel'] == ACCESS_ADMINISTRATE; 

			if ($this->data['isCommish']) {
				if (!isset($this->team_model)) {
					$this->load->model('team_model');
				} // END if
				$this->data['teamList'] = $this->dataModel->getTeamDetails($this->uriVars['league_id'], true);
				$this->data['gameList'] = $this->dataModel->getGamesForPeriod($this->uriVars['period_id']);
				$this->data['period_id' ] = $this->uriVars['period_id'];
				$this->data['max_games'] = (sizeof($this->data['teamList']) * $this->dataModel->games_per_team) /2;
				
				$gameIds = array();
				foreach($this->data['gameList'] as $gameId => $data) {
					array_push($gameIds, $gameId);
				} // END foreach
				if (sizeof($this->data['gameList']) < $this->data['max_games']) {
					$diff = $this->data['max_games'] - sizeof($this->data['gameList']);
					for ($i = 1; $i < ($diff); $i++) {
						array_push($gameIds, "n_".$i);
					} // END for
				} // END if
				if (sizeof($this->data['gameList']) == 0) {
					$this->form_validation->set_rules('n_1_home', 'First Home Team', 'required|trim');
					$this->form_validation->set_rules('n_1_away', 'First Away Team', 'required|trim');
					$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
				}
				$this->form_validation->set_rules('submitted', 'Form Submission', 'required');
					
				if ($this->form_validation->run() == false) {
					//echo("Validation Fail");
					$this->params['subTitle'] = "Edit Schedule";
					$this->data['subTitle'] = "Edit Schedule for ".$this->dataModel->league_name;
					$this->data['league_id'] = $this->dataModel->id;
					$this->data['period_id'] = $this->uriVars['period_id'];
					$this->data['input'] = $this->input;
					$this->data['config'] = $this->params['config'];
					$this->data['gameIds'] = $gameIds;
					$this->params['content'] = $this->load->view($this->views['SCHEDULE_EDIT'], $this->data, true);
					$this->makeNav();
					$this->params['pageType'] = PAGE_FORM;
					$this->displayView();
				} else {
					//echo("Validation Pass");
					$error = false;
					$errorStr = ""; 
					// GET LIST OF TEAM IDs FOR THI LEAGUE
					foreach($gameIds as $game_id) {
						$this->db->flush_cache();
						$write2DB = false;
						if (($this->input->post($game_id."_home") && $this->input->post($game_id."_home") != -1) && 
							($this->input->post($game_id."_away") && $this->input->post($game_id."_away") != -1)) {
							$this->db->set('home_team_id',$this->input->post($game_id."_home"));
							$this->db->set('away_team_id',$this->input->post($game_id."_away"));
							$write2DB = true;
						}
						if ($write2DB) {
							if (strpos($game_id,"n_") === false) {				
								$this->db->where('league_id',$this->dataModel->id);
								$this->db->where('id',$game_id);
								$this->db->update('fantasy_leagues_games');
							} else {
								$this->db->set('league_id',$this->dataModel->id);
								$this->db->set('scoring_period_id',$this->uriVars['period_id']);
								$this->db->insert('fantasy_leagues_games');
								if ($this->db->affected_rows() == 0) {
									$error = true;
									$errorStr = "The game ".$game_id." could not be written to the database.";
								} // END if
							} // END if
						} // END if
					} // END foreach
					if ($error) {
						$message = '<p class="error">Schedule editing Failed.';
						if ($this->team_model->errorCode != 0) {
							$message .= 'An error has occured. Error: "'.$this->team_model->statusMess.'"</p>';
						} // END if
						$message .= '</p >';
						$this->data['message'] = $message;
						$this->params['subTitle'] = "Edit Schedule";
						$this->data['subTitle'] = "Edit Schedule for ".$this->dataModel->league_name;
						$this->data['league_id'] = $this->dataModel->id;
						$this->data['period_id'] = $this->uriVars['period_id'];
						$this->data['input'] = $this->input;
						$this->data['config'] = $this->params['config'];
						$this->data['gameIds'] = $gameIds;
						$this->params['content'] = $this->load->view($this->views['SCHEDULE_EDIT'], $this->data, true);
						$this->makeNav();
						$this->params['pageType'] = PAGE_FORM;
						$this->displayView();
					} else {
						$this->session->set_flashdata('message', '<p class="success">Schedule Update successful.</p>');
						redirect('league/schedule/'.$this->dataModel->id);
					} // END if
				} // END if
			} else {
				$this->data['subTitle'] = "Unauthorized Access";
				$this->data['theContent'] = '<span class="error">You are not authorized to access this page.</span>';
				$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
				$this->displayView();
			} // END if
		} else {
	        $this->session->set_userdata('loginRedirect',current_url());	
			redirect('user/login');
	    } // END if
	}
	
	public function transactions() {
		$this->getURIData();
		$this->data['subTitle'] = "League Transactions";
		$this->load->model($this->modelName,'dataModel');
		$league_id =-1;
		if(isset($this->uriVars['id'])) {
			$league_id = $this->uriVars['id'];
		} else if (isset($this->uriVars['league_id'])) {
			$league_id =$this->uriVars['league_id'];
		}
		$this->data['league_id'] = $league_id;
		$this->dataModel->load($league_id);
		
		$this->data['limit'] = $limit = (isset($this->uriVars['limit'])) ? $this->uriVars['limit'] : 20;
		$this->data['pageId'] = $pageId = (isset($this->uriVars['pageId'])) ? $this->uriVars['pageId'] : 1;																			   
		
		$startIndex = 0;
		if ($limit != -1) {
			$startIndex = ($limit * ( - 1))-1;
		}
		if ($startIndex < 0) { $startIndex = 0; }
		$this->data['startIndex'] = $startIndex;
		
		$this->data['thisItem']['teamList'] = $this->dataModel->getTeamDetails();
		$this->data['recCount'] = sizeof($this->dataModel->getLeagueTransactions(-1, 0));
		$this->data['thisItem']['transactions'] = $this->dataModel->getLeagueTransactions($this->data['limit'],$this->data['startIndex']);
		//echo("Transaction count = ".sizeof($this->data['thisItem']['transactions'])."<br />");
		$this->data['pageCount'] = 1;
		if ($limit != -1) {
			$this->data['pageCount'] = intval($this->data['recCount'] / $limit);
		}
		if ($this->data['pageCount'] < 1) { $this->data['pageCount'] = 1; }
		$this->data['thisItem']['league_name'] = $this->dataModel->league_name;
		
		$this->data['thisItem']['subTitle'] = $this->dataModel->league_name." Transactions";
		
		if (!isset($this->player_model)) {
			
		} // END if
							
		$this->makeNav();
		$this->data['transaction_summary'] = $this->load->view($this->views['TRANSACTION_SUMMARY'], $this->data, true);
		$this->params['content'] = $this->load->view($this->views['TRANSACTIONS'], $this->data, true);
	    $this->params['pageType'] = PAGE_FORM;
		$this->displayView();	
	}
	public function pending() {
		$this->getURIData();
		$this->params['subTitle'] = "League Rules";
		$this->load->model($this->modelName,'dataModel');
		$this->dataModel->load($this->uriVars['id']);
		$this->makeNav();
		$this->params['content'] = $this->load->view($this->views['PENDING'], $this->data, true);
	    $this->displayView();	
	}
	
	/*---------------------------------------
	/	CONTROLLER FUNCTIONS
	/	THESE FUNCTION ARE CALLED BY LEAGUE 
	/ 	PAGES AND RESOLVE BACK TO OTHER 
	/	HANDLERS.
	/--------------------------------------*/
	public function afterAdd() {
		// ASSURE THERE ARE NO OTHER DIVISIONS FOR THIS LEAGUE PRIOR TO CREATING NEW ONES
		$this->db->where('league_id',$this->dataModel->id);
		$this->db->delete("fantasy_divisions");
		
		// CREATE TWO DIVISIONS FOR THIS LEAGUE
		$data = array(array("league_id"=>$this->dataModel->id,"division_name"=>"Division A"),
						array("league_id"=>$this->dataModel->id,"division_name"=>"Division B"));
		
		$this->db->insert("fantasy_divisions",$data[0]);
		$div1Id = $this->db->insert_id();
		$this->db->insert("fantasy_divisions",$data[1]);
		$div2Id = $this->db->insert_id();
		
		$this->db->where('league_id',$this->dataModel->id);
		$this->db->delete("fantasy_teams");
		$teamsAdded = 0;
		for ($i = 0; $i < $this->dataModel->max_teams; $i++) {
			if ($i < ($this->dataModel->max_teams / 2)) {
				$divId = $div1Id;
			} else {
				$divId = $div2Id;
			}
			$teamData = array("teamname"=>"Team ".strtoupper(chr(64+($i+1))),"teamnick"=>getRandomTeamNickname(),
							  "division_id"=>$divId,"league_id"=>$this->dataModel->id);
			if ($i == 0) { $teamData = $teamData + array("owner_id"=>$this->params['currUser']); }
			$this->db->insert("fantasy_teams",$teamData);
			$teamsAdded += $this->db->affected_rows();
		}
		if (!$teamsAdded == $this->dataModel->max_teams) {
			$this->outMess .= "Error adding teams. ".$teamsAdded." were added, ".$this->dataModel->max_teams." were required.";
		}
		
		if (!isset($this->draft_model)) {
			$this->load->model('draft_model');
		}
		$this->draft_model->setDraftDefaults($this->dataModel->id);
		return true;
	}
	public function autoDraftLeague() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->data['subTitle'] = "League Rules";
			$this->load->model($this->modelName,'dataModel');
			$this->dataModel->load($this->uriVars['id']);
			$this->data['league_id'] = $this->uriVars['id'];
			
			$success = $this->dataModel->auto_draft($this->params['config']['draft_rounds_max'],date('Y',strtotime($this->params['league_info']->current_date)));
			
			$this->load->model('draft_model');
			$this->draft_model->load($this->uriVars['id'],'league_id');
			$this->draft_model->completed = 1;
			$this->draft_model->save();
			
			$this->makeNav();
			$this->params['message'] = $success;
			$this->session->set_flashdata('message',$this->params['message']);	
			redirect('league/admin/'.$this->uriVars['id']);
		} else {
	        $this->session->set_userdata('loginRedirect',current_url());	
			redirect('user/login');
	    }
	}
	public function changeCommissioner() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->loadData();
			if ($this->params['accessLevel'] == ACCESS_ADMINISTRATE || $this->params['currUser'] == $this->dataModel->commissioner_id) {
				$this->dataModel->commissioner_id = $this->params['currUser'];
				$this->dataModel->save();
				$message = '<p class="success">Commissioer ID has been changed.';
			} else {
				$message = '<p class="error">You do not have sufficient privlidges to perform the requested action.';
				$message .= '<b>'.$this->dataModel->statusMess.'</b>';
				$message .= '</p >';
			}
			$this->session->set_flashdata('message', $message);
			redirect('league/teamAdmin/'.$this->dataModel->id);
		} else {
	        $this->session->set_flashdata('loginRedirect',current_url());	
			redirect('user/login');
	    }	
	}
	public function inviteOwner() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->loadData();
			if (!isset($this->uriVars['sent'])) {	
				$error = false;
				$this->params['subTitle'] = $this->data['subTitle'] = "Invite New Owner";
				$this->data['owner_id'] = (isset($this->uriVars['owner_id'])) ? $this->uriVars['owner_id'] : '';
				$this->data['team_id'] = (isset($this->uriVars['team_id'])) ? $this->uriVars['team_id'] : '';
				$this->data['sent'] = (isset($this->uriVars['sent'])) ? true : false;
				
				$this->form_validation->set_rules('email', 'Email Address', 'required|valid_email');
				$this->form_validation->set_rules('inviteMessage', 'Invitation message', 'required');
				if ($this->form_validation->run() == false) {
					$this->data['input'] = $this->input;
					$this->data['league_id'] = $this->dataModel->id;
					$this->data['defaultMessage'] = str_replace('[LEAGUE_NAME]',$this->dataModel->league_name,$this->lang->line('league_invite_default'));
					$this->params['content'] = $this->load->view($this->views['INVITE'], $this->data, true);
					$this->params['pageType'] = PAGE_FORM;
					$this->makeNav();
					$this->displayView();
				} else {
					$this->db->select('id');
					$this->db->where('to_email',$this->input->post('email'));
					$this->db->where('league_id',$this->dataModel->id);
					$this->db->from('fantasy_invites');
					$count = $this->db->count_all_results();
					if ($count == 0) {
						
						$confirmStr  = substr(md5(time().$this->input->post('email')),0,16);
						$confirmKey  = substr(md5($this->config->item('password_crypt')),0,8);
						$email_mesg	 = "To ".$this->input->post('email').",";
						$email_mesg	.= $this->input->post('inviteMessage');
						$link 		 = $this->params['config']['fantasy_web_root'].'user/inviteResponse/email/'.urlencode($this->input->post('email')).'/team_id/'.$this->data['team_id'].'/league_id/'.$this->dataModel->id.'/ck/'.md5($confirmStr.$confirmKey);
						$email_mesg	.= '<p><a href="'.$link.'/ct/1">Accept the invitation</a> <br /><br />';
						$email_mesg	.= '<p><a href="'.$link.'/ct/-1">Decline the invitation</a> <br /><br />';
						$subject 	 = $this->dataModel->league_name. " Owner Invitation";
						$headers  	 = "MIME-Version: 1.0\r\n";
						$headers 	.= "Content-type: text/html; charset=iso-8859-1\r\n";
						$headers 	.= "To: ".$this->input->post('email')." \r\n";
						$headers 	.= "From: ".$this->dataModel->league_name." \r\n";
						if (defined('ENV') && ENV != "dev") {
							$success = mail($this->input->post('email'),$subject,$email_mesg,$headers);
						} else {
							$success = true;
							if ($this->debug === true) { $message = $email_mesg; }
						}
						if (!$success) {
							$error = true;
							$message = 'The mail message failed to send.';
						}
					} else {
						$error = true;
						$message = 'An invitation is already pending for '.$this->input->post('email').'. They must decline the current invitation before another can be sent.<p />View a complete list of '.anchor('league/leagueInvites/'.$this->dataModel->id,'pending invitiations').'.';
					}
					if (!$error) {
						$inviteData = array('to_email'=>$this->input->post('email'),'from_id'=>$this->dataModel->commissioner_id,
											'league_id'=>$this->dataModel->id, 'team_id'=>$this->input->post('team_id'),
											'confirm_str'=>$confirmStr,'confirm_key'=>$confirmKey);
						$this->db->insert('fantasy_invites',$inviteData);
						if ($this->db->affected_rows() == 0) {
							$error = true;
							$message = 'The invitation data was not saved to the database.';
						}
					}
					if (!$error) {
						$outMess = '<span class="success">Owner Invitation has been sent successfully</span><br /><br />';
						if($this->debug && !empty($message)) {
							$outMess .= $message."<br />".$link.'/accept/1';
							$this->data['subTitle'] = "Invitation Sent";
							$this->data['theContent'] = $outMess;
							$this->params['content'] = $this->load->view($this->views['SUCCESS'], $this->data, true);
							$this->makeNav();
							$this->displayView();
						} else {
							$this->session->set_flashdata('message', "The invitation was sent successfully.");
							redirect('/league/inviteOwner/id/'.$this->dataModel->id.'/sent/1');
						}
					} else {
						$message = "The invite could not be sent. Error: ".$message."<br /><br />";
						$this->session->set_flashdata('message', '<span class="error">'.$message.'</span>');
						redirect('league/inviteOwner/id/'.$this->dataModel->id.'/owner_id/'.$this->data['owner_id']);
					}
				}
			} else {
				$this->data['subTitle'] = "Invitation Sent";
				$this->data['theContent'] = "Your invitation was sent successfully. Return to the ".anchor('league/teamAdmin/'.$this->dataModel->id,'team admin screen').".";
				$this->params['content'] = $this->load->view($this->views['SUCCESS'], $this->data, true);
				$this->makeNav();
				$this->displayView();
			}
		} else {
	        $this->session->set_flashdata('loginRedirect',current_url());	
			redirect('user/login');
	    }	
	}
	public function leagueInvites() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->loadData();
			if ($this->params['accessLevel'] == ACCESS_ADMINISTRATE || $this->params['currUser'] == $this->dataModel->commissioner_id) {
				$this->data['thisItem']['invites'] = $this->dataModel->getLeagueInvites();
				$this->data['subTitle'] = 'Pending Invitiations';
				$this->params['content'] = $this->load->view($this->views['INVITES'], $this->data, true);
				$this->makeNav();
				$this->displayView();
			} else {
				$error = true;
				$message = '<span class="error">You do not have sufficient privlidges to access the requested information.</span>';
				$this->params['theContent'] = $message;
				$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
				$this->makeNav();
				$this->displayView();
			}
		} else {
	        $this->session->set_flashdata('loginRedirect',current_url());	
			redirect('user/login');
	    }	
	}
	public function waiverClaims() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->loadData();
			if ($this->params['accessLevel'] == ACCESS_ADMINISTRATE || $this->params['currUser'] == $this->dataModel->commissioner_id) {
				$this->data['league_id'] = $this->dataModel->id;
				$this->data['thisItem']['claims'] = $this->dataModel->getWaiverClaims();
				$this->data['subTitle'] = 'Pending Waiver Claims';
				$this->params['content'] = $this->load->view($this->views['WAIVER_CLAIMS'], $this->data, true);
				$this->makeNav();
				$this->displayView();
			} else {
				$error = true;
				$message = '<span class="error">You do not have sufficient privlidges to access the requested information.</span>';
				$this->params['theContent'] = $message;
				$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
				$this->makeNav();
				$this->displayView();
			}
		} else {
	        $this->session->set_flashdata('loginRedirect',current_url());	
			redirect('user/login');
	    }	
	}
	public function removeClaim() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->loadData();
			if ($this->params['accessLevel'] == ACCESS_ADMINISTRATE || $this->params['currUser'] == $this->dataModel->commissioner_id) {
				if (isset($this->uriVars['id'])) {
					$this->db->where('id',$this->uriVars['id']);
					$this->db->delete('fantasy_teams_waiver_claims');
					$message = '<span class="success">The claim has been successfully removed.</span>';
				} else {
					$error = true;
					$message = '<span class="error">A required claim identifier was not found. Please go back and try the operation again or contact the site adminitrator to report the problem.</span>';
				}
			} else {
				$error = true;
				$message = '<span class="error">You do not have sufficient privlidges to perform the requested action.</span>';
			}
			$this->session->set_flashdata('message', $message);
			redirect('league/waiverClaims/league_id/'.$this->uriVars['league_id']);
		} else {
	        $this->session->set_flashdata('loginRedirect',current_url());	
			redirect('user/login');
	    }	
	}
	public function removeOwner() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->loadData();
			if ($this->params['accessLevel'] == ACCESS_ADMINISTRATE || $this->params['currUser'] == $this->dataModel->commissioner_id) {
				if (isset($this->uriVars['team_id'])) {
					$this->load->model('team_model');
					$this->team_model->load($this->uriVars['team_id']);
					$this->team_model->owner_id = -1;
					$this->team_model->save();
					$message = '<span class="success">The owner has been successfully removed from the selected team.</span>';
				} else {
					$error = true;
					$message = '<span class="error">A required team identifier was not found. Please go back and try the operation again or contact the site adminitrator to report the problem.</span>';
				}
			} else {
				$error = true;
				$message = '<span class="error">You do not have sufficient privlidges to perform the requested action.</span>';
			}
			$this->session->set_flashdata('message', $message);
			redirect('league/teamAdmin/'.$this->dataModel->id);
		} else {
	        $this->session->set_flashdata('loginRedirect',current_url());	
			redirect('user/login');
	    }	
	}
	public function removeAvatar() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->loadData();
			if ($this->dataModel->id != -1) {
				$success = $this->dataModel->deleteFile('avatar',PATH_LEAGUES_AVATAR_WRITE,true);
			}
			if ($success) {
				$this->session->set_flashdata('message', '<p class="success">The image has been successfully deleted.</p>');
				redirect('league/info/'.$this->dataModel->id);
			} else {
				$message = '<p class="error">Avatar Delete Failed.';
				$message .= '<b>'.$this->dataModel->statusMess.'</b>';
				$message .= '</p >';
				$this->session->set_flashdata('message', $message);
				redirect('league/avatar');
			}
		} else {
	        $this->session->set_flashdata('loginRedirect',current_url());	
			redirect('user/login');
	    }
	}
	public function tradeReview() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			if (isset($this->uriVars['league_id'])) { $this->uriVars['id'] = $this->uriVars['league_id']; }
			$this->loadData();
			if ($this->params['accessLevel'] == ACCESS_ADMINISTRATE || $this->params['currUser'] == $this->dataModel->commissioner_id) {
				$this->data['type'] = $limit = (isset($this->uriVars['type'])) ? $this->uriVars['type'] : 1;
				$this->data['limit'] = $limit = (isset($this->uriVars['limit'])) ? $this->uriVars['limit'] : DEFAULT_RESULTS_COUNT;
				$this->data['pageId'] = $pageId = (isset($this->uriVars['pageId'])) ? $this->uriVars['pageId'] : 1;
				$startIndex = 0;
				if ($limit != -1) {
					$startIndex = ($limit * ( - 1))-1;
				}
				if ($startIndex < 0) { $startIndex = 0; }
				$this->data['startIndex'] = $startIndex;
				$this->load->model('team_model');
				
				$this->data['league_id'] = $this->dataModel->id;
				if ($this->data['type'] == 1) {
					$tradeLabel = "Pending ";
					$this->data['trades'] = $this->team_model->getPendingTrades($this->dataModel->id, false, false, false, true, false, $this->data['limit'],$this->data['startIndex']);
				} else {
					$tradeLabel = "All ";
					$this->data['trades'] = $this->team_model->getAllTrades($this->dataModel->id, false, false, false, true, false, $this->data['limit'],$this->data['startIndex']);
				}
				$this->data['tradeStatus'] = loadSimpleDataList('tradeStatus');
				//print($this->db->last_query()."<br />");
				$this->data['subTitle'] = $tradeLabel.' Trades';
				$this->params['content'] = $this->load->view($this->views['TRADES'], $this->data, true);
				$this->makeNav();
				$this->displayView();
			} else {
				$error = true;
				$message = '<span class="error">You do not have sufficient privlidges to access the requested information.</span>';
				$this->params['theContent'] = $message;
				$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
				$this->makeNav();
				$this->displayView();
			}
		} else {
	        $this->session->set_flashdata('loginRedirect',current_url());	
			redirect('user/login');
	    }
	}
	/*--------------------------------
	/	PRIVATE/PROTECTED FUNCTIONS
	/-------------------------------*/	
	/**
	 *	GET URI DATA.
	 *	Parses out an id or other parameters from the uri string
	 *
	 */
	protected function getURIData() {
		parent::getURIData();
		if ($this->input->post('league_id')) {
			$this->uriVars['league_id'] = $this->input->post('league_id');
		} // END if
		if ($this->input->post('period_id')) {
			$this->uriVars['period_id'] = $this->input->post('period_id');
		} // END if
		if ($this->input->post('game_id')) {
			$this->uriVars['game_id'] = $this->input->post('game_id');
		} // END if
		if ($this->input->post('team_id')) {
			$this->uriVars['team_id'] = $this->input->post('team_id');
		} // END if
		if ($this->input->post('owner_id')) {
			$this->uriVars['owner_id'] = $this->input->post('owner_id');
		} // END if
		if ($this->input->post('sent')) {
			$this->uriVars['sent'] = $this->input->post('sent');
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
		if ($this->input->post('type')) {
			$this->uriVars['type'] = $this->input->post('type');
		} // END if
		
	}
	protected function makeForm() {
		$form = new Form();
		
		$form->open('/'.$this->_NAME.'/submit/','detailsForm|detailsForm');
		
		$form->fieldset('League Details');
		
		$form->text('league_name','League Name','required|trim',($this->input->post('league_name')) ? $this->input->post('league_name') : $this->dataModel->league_name,array('class','first longText'));
		$form->br();
		$form->textarea('description','Description:','',($this->input->post('description')) ? $this->input->post('description') : $this->dataModel->description,array('rows'=>5,'cols'=>65));
		$form->br();
		$form->select('access_type|access_type',loadSimpleDataList('accessType'),'Access Type',($this->input->post('access_type')) ? $this->input->post('access_type') : $this->dataModel->access_type,'required');
		$form->br();
		$form->select('league_type|league_type',listLeagueTypes(true,true),'Scoring System',($this->input->post('league_type')) ? $this->input->post('league_type') : $this->dataModel->league_type,'required');
		$form->br();
		$form->select('max_teams|max_teams',array(8=>8,10=>10,12=>12),'No. of Teams',($this->input->post('max_teams')) ? $this->input->post('max_teams') : $this->dataModel->max_teams,'required');
		$form->br();
		if ($this->data['accessLevel'] == ACCESS_ADMINISTRATE) {
			$form->select('league_status|league_status',loadSimpleDataList('leagueStatus'),'Status',($this->input->post('league_status')) ? $this->input->post('league_status') : $this->dataModel->league_status,'required');
			$form->br();
		}
		$form->space();
		$form->fieldset('Head To Head Options');
		$form->select('games_per_team|games_per_team',array(1=>1,2=>2,3=>3),'Games per team',($this->input->post('games_per_team')) ? $this->input->post('games_per_team') : $this->dataModel->games_per_team);
		$form->br();
		$form->select('regular_scoring_periods|regular_scoring_periods',array(24=>25,23=>24,22=>23,21=>22,20=>21),'Playoffs begin in week',($this->input->post('regular_scoring_periods')) ? $this->input->post('regular_scoring_periods') : $this->dataModel->regular_scoring_periods);
		$form->br();
		$form->select('playoff_rounds|playoff_rounds',array(1=>1,2=>2,3=>3),'Playoff Rounds',($this->input->post('playoff_rounds')) ? $this->input->post('playoff_rounds') : $this->dataModel->playoff_rounds);
		
		$form->fieldset('',array('class'=>'button_bar'));
		$form->button('Delete','delete','button',array('class'=>'button'));	
		$form->nobr();
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
			$form->hidden('new_commisioner',$this->params['currUser']);
		}
		$this->form = $form;
		$this->data['form'] = $form->get();
		
		$this->makeNav();
	}
	protected function showInfo() {
		$this->data['thisItem']['league_name'] = $this->dataModel->league_name;
		$this->data['thisItem']['description'] = $this->dataModel->description; 
		
		$this->data['thisItem']['divisions'] = $this->dataModel->getFullLeageDetails();
		
		$this->params['subTitle'] = "Fantasy League Overview";
		
		$this->makeNav();
		
		parent::showInfo();
	}
	protected function makeNav() {
		$admin = false;
		if (isset($this->params['currUser']) && ($this->params['currUser'] == $this->dataModel->commissioner_id || $this->params['accessLevel'] == ACCESS_ADMINISTRATE)){
			$admin = true;
		}
		array_push($this->params['subNavSection'],league_nav($this->dataModel->id, $this->dataModel->league_name,$admin));
	}
}
/* End of file league.php */
/* Location: ./application/controllers/league.php */ 