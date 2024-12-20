<?php
require_once('base_editor.php');
/**
 *	League.
 *	The primary controller for League manipulation and details.
 *	@author			Jeff Fox
 *	@dateCreated	04/04/10
 *	@lastModified	04/18/20
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

	var $tradeLists = array();
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
		// UPDATE - 1.0.5
		// PRIVATE LEAGUE CHECK
		// IF WE HAVE A LEAGUE ID AND THE LEAGUE TURNS OUT TO BE PRIVATE, CHECK IF THE CURRENT USER
		// HAS ACCESS AND IF NOT, ROUTE TO A PRIVATE LEAGUE VIEW
		$this->getURIData();
		if (($this->uri->segment(2) != 'leagueContact' && $this->uri->segment(2) != 'requestTeam' && $this->uri->segment(2) != 'requestResponse' && $this->uri->segment(2) != 'privateLeague') && isset($this->uriVars['id'])) {

			$this->load->model($this->modelName,'dataModel');
			$this->dataModel->load($this->uriVars['id']);

			if ($this->dataModel->access_type == -1) {
				$isAdmin = ($this->params['accessLevel'] == ACCESS_ADMINISTRATE) ? true: false;
				$isCommish = $this->dataModel->userIsCommish($this->params['currUser']) ? true: false;
				if (!$isAdmin && !$isCommish) {
					if (!$this->params['loggedIn'] || ($this->params['loggedIn'] && !$this->dataModel->isLeagueMember($this->params['currUser']))) {
						redirect('/league/privateLeague/'.$this->uriVars['id']);
					}
				}
			}
		}
		$this->views['HOME'] = 'league/league_home';
		$this->views['EDIT'] = 'league/league_editor';
		$this->views['VIEW'] = 'league/league_info';
		$this->views['FAIL'] = 'league/league_message';
		$this->views['SUCCESS'] = 'league/league_message';
		$this->views['PENDING'] = 'content_pending';
		$this->views['ADMIN'] = 'league/league_admin';
		$this->views['RULES'] = 'league/league_rules';
		$this->views['RESULTS'] = 'league/league_results';
		$this->views['STANDINGS_HEADTOHEAD'] = 'league/league_standings_h2h';
		$this->views['STANDINGS_ROTISSERIE'] = 'league/league_standings_rot';
		$this->views['SCHEDULE'] = 'league/league_schedule';
		$this->views['SCHEDULE_EDIT'] = 'league/league_schedule_edit';
		$this->views['TRANSACTIONS'] = 'league/league_transactions';
		$this->views['AVATAR'] = 'league/league_avatar';
		$this->views['TEAM_ADMIN'] = 'league/league_team_admin';
		$this->views['INVITE'] = 'league/league_invite_owner';
		$this->views['INVITES'] = 'league/league_invite_list';
		$this->views['WAIVER_CLAIMS'] = 'league/league_waiver_claims';
		$this->views['TRADES'] = 'league/league_trades';
		$this->views['REVIEW_SETTINGS'] = 'league/league_config_review';
		$this->views['LEAGUE_LIST'] = 'league/league_listing';
		$this->views['TEAM_REQUEST'] = 'league/league_team_request';
		$this->views['CONTACT_FORM'] = 'league/league_contact';
		$this->views['TEAM_ROSTERS'] = 'league/league_team_rosters'; // PROD 1.0.3

		$this->lang->load('league');

		// EDIT 0.6 BETA
		// TRADE PSUEDO CRON TASKS
		// TEST FOR EXPIRING TRADES, EXPIRING LEAGUE PROTESTS AND ALERT COMMISSIONER TO TRADES REQUIRING
		// COMMISSIONER APPROVAL
		if ($this->params['config']['useTrades'] == 1 && isset($this->uriVars['id'])) {
			if (!isset($this->dataModel)) {
				$this->load->model($this->modelName,'dataModel');
			} // END if
			$curr_period = $this->getScoringPeriod();
			// COMMISH APPROVAL ALERT
			$this->load->model('team_model');
			if ($this->params['loggedIn'] && $this->params['config']['approvalType'] == 1 && $this->params['currUser'] == $this->dataModel->getCommissionerId()) {
				$this->tradeLists['forAppproval'] = $this->team_model->getTradesForScoringPeriod($this->uriVars['id'], $curr_period['id'], false, false, false, false, TRADE_PENDING_COMMISH_APPROVAL);
			} // END if
			// LEAGUE APPROVAL
			if ($this->params['config']['approvalType'] == 2 && $this->params['config']['protestPeriodDays'] > 0) {
				// 1.0.3 PROD, added an actual CRON like function to approve trades past review period
				$this->team_model->checkTradesInApprovalState($curr_period['id'], $this->params['config']['protestPeriodDays']);
				$this->tradeLists['inLeagueReview'] = $this->dataModel->getTradesInLeagueReview($curr_period['id'],$this->uriVars['id'],$this->params['config']['protestPeriodDays']);
			} // END if
			$this->data['tradeLists'] = $this->tradeLists;
			// TRADES EXPIRATION
			if ($this->params['config']['tradesExpire'] == 1) {
				$this->league_model->expireOldTrades($this->uriVars['id'], false, $this->debug);
			} // END if
		} // END if

		$this->debug = false;
	}
	/*---------------------------------------
	/	CONTROLLER SUBMISSION HANDLERS
	/--------------------------------------*/
	/**
	 *	PRIVATE LEAGUES.
	 */
	public function privateLeague() {
		$this->makeNav(true);
		$this->data['subTitle'] = "Private League";
		$mess = str_replace('[LOGIN_URL]',anchor('/user/login','log in'),$this->lang->line('private_league_access'));
		$this->data['theContent'] = str_replace('[CONTACT_URL]',anchor('/league/leagueContact/'.$this->uriVars['id'],'league commissioner'),$mess);
		$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
	    $this->displayView();
	}
	/**
	 *	INDEX.
	 *	The default handler when the controller is called.
	 *	Checks for an existing auth session, and if found,
	 *	redirects to the dashboard. Otherwise, it redirects
	 *	to the login.
	 */
	public function index() {
		redirect('league/home/');
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
		$owners = $this->dataModel->getOwnerIds();
		$this->data['isOwner'] = in_array($this->params['currUser'],$owners);

		$this->data['league_id'] = $this->dataModel->id;
		$this->params['subTitle'] = $this->dataModel->league_name;
		
		$scoring_type = $this->dataModel->getScoringType();
		if (!function_exists('getScoringPeriod')) {
			$this->load->helper('admin');
		}
		$scoring_type = $this->dataModel->getScoringType();
		$total_periods = 0;
		if ($scoring_type == LEAGUE_SCORING_HEADTOHEAD) {
			$total_periods = intval($this->dataModel->regular_scoring_periods)+ intval($this->dataModel->playoff_rounds);
		} else {
			$total_periods = getScoringPeriodCount();
		}
		$configPeriodId = intval($this->params['config']['current_period']);	

		if ($configPeriodId == 1) {
			$curr_period_id = 1;
		} else if ($configPeriodId > 1 && $configPeriodId <= $total_periods) {
			$curr_period_id = $configPeriodId-1;
		} else {
			$curr_period_id = $total_periods;
		}
		$curr_period = getScoringPeriod($curr_period_id);
		$this->data['configPeriodId'] = $configPeriodId;
		$this->data['curr_period_id'] = $curr_period_id;
		$this->data['total_periods'] = $total_periods;
		$leagueStandings = $this->dataModel->getLeagueStandings($curr_period_id);

		if ($scoring_type == LEAGUE_SCORING_HEADTOHEAD) {
			$this->data['thisItem']['divisions'] = $leagueStandings;
		} else {
			$this->data['thisItem']['teams'] = $leagueStandings ;
		}
		$this->data['scoring_type']=$scoring_type;

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
		$this->data['thisItem']['league_status'] = $this->dataModel->league_status;
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
		$this->data['currUser'] = $this->params['currUser'];
		$this->data['isLeagueMember'] = $this->dataModel->isLeagueMember($this->params['currUser'], $this->dataModel->id);

		// WAIVERS AND WAIVER ORDER
		$this->data['useWaivers'] = $this->params['config']['useWaivers'];
		if ($this->data['useWaivers'] == 1) {
			$this->data['waiverOrder'] = $this->team_model->getWaiverOrder($this->dataModel->id);
		}
		$this->data['current_period'] = $this->params['config']['current_period'];
		
		// EDIT 1.0.3 PROD - PLAYOFFS BANNER
		$playoffArr = array();
		if ($scoring_type == LEAGUE_SCORING_HEADTOHEAD) {
			if (inPlayoffPeriod($configPeriodId, $this->dataModel->id)) {
				$playoffArr['inPlayoffs']= 1;
				$playoffArr['league_year'] = date('Y', strtotime($curr_period['date_start']));
				$playoffArr['league_name'] = $this->dataModel->league_name;
			}
			if ($configPeriodId == $this->dataModel->regular_scoring_periods && intval($this->dataModel->playoff_rounds) > 0) {
				$playoffArr['playoffsNext'] = 1;
				$playoffArr['playoffsTrades'] = $this->dataModel->allow_playoff_trades;
			}
			$playoffArr['playoffsTrans'] = $this->dataModel->allow_playoff_trans;
		}
		$this->data['playoffs'] = $playoffArr;

		// EDIT 1.1.1 Add a header when the season is over
		if (!function_exists('getFantasyStatus')){
			$this->load->helper('general');
		}
		$status = getFantasyStatus();
		$this->data['fantasyStatus'] = getFantasyStatusLabel($status);
		$this->data['fantasyStatusID'] = $status;

		$this->params['content'] = $this->load->view($this->views['HOME'], $this->data, true);
		$this->makeNav();
		$this->params['pageType'] = PAGE_FORM;
		$this->displayView();
	}
	/**
	 *	ADMIN.
	 *	Draws the League Admin Dashboard
	 *
	 */
	public function admin() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->load->model($this->modelName,'dataModel');
			
			if ($this->dataModel->commissioner_id == $this->params['currUser'] || $this->params['accessLevel'] == ACCESS_ADMINISTRATE) {
				$this->load->model('draft_model');
				$this->dataModel->load($this->uriVars['id']);
				$this->data['leeague_admin_intro_str'] = $this->lang->line('leeague_admin_intro_str');
				$this->data['subTitle'] = "League Admin";
				$this->data['league_id'] = $this->uriVars['id'];
				$this->data['draftStatus'] = $this->draft_model->getDraftStatus($this->dataModel->id);
				$this->data['draftEnabled'] = $this->draft_model->getDraftEnabled($this->dataModel->id);
				$this->data['draftTimer'] = $this->draft_model->timerEnable;
				$this->data['debug'] = $this->debug;
				$this->data['scoring_type'] = $this->dataModel->getScoringType();

				$this->load->model('team_model');
				// Data for Alert Notifications
				$this->data['waiver_claims'] = count($this->dataModel->getWaiverClaims());
				$this->data['trades'] = count($this->team_model->getPendingTrades($this->dataModel->id));
				$invites = count($this->dataModel->getLeagueInvites(true));
				$requests = count($this->dataModel->getLeagueRequests(true));
				$this->data['invites_requets'] = $invites + $requests;
				// ROSTER ALERTS
				$scoring_period = getCurrentScoringPeriod($this->data['league_info']->current_date);
				$rosterValidation = $this->dataModel->validateRosters($scoring_period, $this->uriVars['id']);
				$rosterIssues = 0;
				if ($this->dataModel->errorCode == 1) {
					foreach($rosterValidation as $status) {
						if ($status['rosterValid'] == -1) {
							$rosterIssues++;
						}
					}
				}
				$this->data['rosterIssues'] = $rosterIssues;
				// WAIVERS AND WAIVER ORDER
				$this->data['useWaivers'] = $this->params['config']['useWaivers'];
				$this->data['waiverOrder'] = $this->team_model->getWaiverOrder($this->dataModel->id);
				$this->data['current_period'] = $this->params['config']['current_period'];


				$currDate = strtotime($this->data['league_info']->current_date);
				$startDate = strtotime($this->data['league_info']->start_date);
				$firstPeriodStart = strtotime($startDate);
				$this->data['preseason'] = ($currDate <= $startDate && $currDate<=$firstPeriodStart);

				$this->makeNav();
				$this->params['content'] = $this->load->view($this->views['ADMIN'], $this->data, true);
			} else {
				$this->data['subTitle'] = "Unauthorized Access";
				$this->data['theContent'] = '<span class="error">You are not authorized to access this page.</span>';
				$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
			}
			$this->params['pageType'] = PAGE_FORM;
			$this->displayView();
		} else {
	        $this->session->set_userdata('loginRedirect',current_url());
			redirect('user/login');
	    }
	}
	/**
	 * TEAM ROSTERS STATUS
	 * Show a listing of the teams in the league and their roster status
	 *
	 * 	@return void
	 *	@since	1.0.3 PROD

	 */
	public function rosters() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->load->model($this->modelName,'dataModel');
			$this->dataModel->load($this->uriVars['id']);
			if ($this->dataModel->commissioner_id == $this->params['currUser'] || $this->params['accessLevel'] == ACCESS_ADMINISTRATE) {
				$this->data['league_id'] = $this->uriVars['id'];
				if (!function_exists('getScoringPeriod')) {
					$this->load->helper('admin');
				}
				if (isset($this->uriVars['period_id'])) {
					$curr_period_id = $this->uriVars['period_id'];
				} else {
					$curr_period_id = $this->params['config']['current_period'];
				}
				$curr_period = getScoringPeriod($curr_period_id);
				$this->data['rosterStatus'] = $this->dataModel->validateRosters($curr_period, $this->data['league_id']);
				$this->data['statusMessage'] = $this->dataModel->statusMess;

				$this->makeNav();
				$this->data['subTitle'] = "League Roster Status";
				$this->params['content'] = $this->load->view($this->views['TEAM_ROSTERS'], $this->data, true);
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
	 *  CONTACT COMMISSIONER FORM
	 *
	 * 	@return void
	 *	@since	0.6 beta
	 **/
	function leagueContact() {
		$this->makeNav();
		$this->getURIData();
		$this->load->model($this->modelName,'dataModel');
		if ($this->input->post('submitted')) {
			$league_id = $this->input->post('id');
		} else if (isset($this->uriVars['id'])) {
			$league_id = $this->uriVars['id'];
		}
		$this->dataModel->load($league_id);

		$this->params['subTitle'] = $this->data['subTitle'] = $this->lang->line('league_contact_title');
		$this->data['theContent'] = str_replace('[LEAGUE_NAME]',$this->dataModel->league_name,$this->lang->line('league_contact_body'));

		$this->form_validation->set_rules('name', 'Name', 'required|trim');
		$this->form_validation->set_rules('email', 'E-Mail Address (optional)', 'trim|valid_email');
		$this->form_validation->set_rules('subject', 'Subject', 'required|trim');
		$this->form_validation->set_rules('details', 'Message Body', 'required|trim');
		$this->data['league_id'] = $this->dataModel->id;
		if ($this->form_validation->run() == false) {

			$this->data['input'] = $this->input;
			$this->data['config'] = $this->params['config'];
			$this->params['content'] = $this->load->view($this->views['CONTACT_FORM'], $this->data, true);
			$this->params['pageType'] = PAGE_FORM;
			$this->displayView();
		} else {
			// GET COMMISH EMAIL
			$outMess = "";
			$data = array('leagueName'=>$this->dataModel->league_name,'name'=>$this->input->post('name'),'email'=>$this->input->post('email'),
						  'details'=>$this->input->post('details'));

			$message = $this->load->view('email_templates/league_contact', $data, true);

			$toMail = $this->user_auth_model->getEmail($this->dataModel->commissioner_id);
			if (isset($toMail) && !empty($toMail)) {
				if (ENVIRONMENT == "production") {
					$this->outMess = "Email sending to the Commissioner.";
					$sent = sendEmail($toMail, $this->input->post('email'), $this->input->post('name'),
									  $this->input->post('subject'), $message, "Site Admin", 'email_contact_');
				} else {
					$sent = true;
				}
				if ($sent) {
					$outMess = "Thank you. Your submission has been sent successfully.<p />";
					if ($this->debug) {
						$outMess .= "<h3>Technical Details</h3>
						<b>To:</b> ".$toMail."<br />\n
						<b>Here are the details of the submission:</b><p />
						<b>From:</b> ".$this->input->post('name')."<br />
						<b>Subject:</b> ".$this->input->post('subject')."<p />
						<b>Details:</b> ".$this->input->post('details');
						$outMess .= $this->outMess;
					} // END if
				} else {
					$outMess  = "There was a problem with your submission. The email could not be sent at this time. Please try again later.";
					if ($this->debug) { if (!empty($this->outMess)) $outMess .= $this->outMess; }
				} // END if
			} else {
				$outMess  = "There was a problem with your submission. A recipient email address could not be found.";
			} // END if
			$this->data['theContent'] = $outMess;
			$this->params['content'] = $this->load->view($this->views['SUCCESS'], $this->data, true);
	   		$this->displayView();
		} // END if
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

			if ($this->dataModel->commissioner_id == $this->params['currUser'] || $this->params['accessLevel'] == ACCESS_ADMINISTRATE) {
				$this->data['subTitle'] = "Initialize Draft";
				$this->draft_model->load($this->uriVars['id'], 'league_id');
				$this->draft_model->createDraftOrder($this->dataModel->getTeamDetails(), $this->dataModel->id, false, $this->debug);
				$this->draft_model->draftSchedule($this->dataModel->getTeamDetails(), $this->dataModel->id, false, $this->debug);
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

			if ($this->dataModel->commissioner_id == $this->params['currUser'] || $this->params['accessLevel'] == ACCESS_ADMINISTRATE) {
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
	 *	UPDATE DRAFT SCHEDULE.
	 *	Resets a draft entry to it's intial var values for a league draft
	 *
	 */
	public function updateDraftSchedule() {
		if ($this->params['loggedIn']) {
			$this->getURIData();

			$this->load->model($this->modelName,'dataModel');
			$this->load->model('draft_model');
			$this->dataModel->load($this->uriVars['id']);

			if ($this->dataModel->commissioner_id == $this->params['currUser'] || $this->params['accessLevel'] == ACCESS_ADMINISTRATE) {
				$this->data['subTitle'] = "League Admin";
				if($this->draft_model->draftSchedule($this->dataModel->getTeamDetails(), $this->dataModel->id, false, $this->debug)) {
					$this->session->set_flashdata('message', '<span class="success">Draft schedule has been successfully updated.</span>');
				} else {
					$this->session->set_flashdata('message', '<span class="error">Draft schedule could not be updated at this time..</span>');
				}
				redirect('league/admin/'.$this->dataModel->id);
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
	/*-----------------------------------------------------------------------------
	/
	/	TEAM REQUESTS
	/
	/-----------------------------------------------------------------------------*/
	/**
	 *	REQUEST A TEAM.
	 *	Show a list of leagues that are 1) have teams without owners and 2) are open to requests from
	 *	site members.
	 *
	 *	@since			0.5 Beta
	 *  @changelog		1.1 PROD	- Improved Request flow and messaging
	 *
	 */
	public function requestTeam() {
		if ($this->params['loggedIn']) {
			$this->init();
			$this->getURIData();
			$this->loadData();
			$userMessage = '';
			if ($this->dataModel->id != -1) {
				$this->form_validation->set_rules('team_id', 'Team Selection', 'required|trim');
				$this->form_validation->set_rules('message', 'Message to Commissioner', 'trim|max_length[1000]');
				$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
				if ($this->form_validation->run() == true) {
					$success = $this->dataModel->teamRequest($this->input->post('team_id'),$this->params['currUser']);
					if ($success) {
						if (!isset($this->team_model)) {
							$this->load->model('team_model');
						}
						$msg = $this->lang->line('email_league_team_request');
						$msg = str_replace('[REQUESTED_TEAM_NAME]', $this->team_model->getTeamName($this->input->post('team_id')), $msg);
						$msg = str_replace('[COMMISH]', getUsername($this->dataModel->commissioner_id), $msg);
						$msg = str_replace('[USERNAME]', getUsername($this->params['currUser']), $msg);
						$userMessage = $this->input->post('message');
						$userMessage = ((!empty($userMessage)) ? str_replace('\n', "<br>",$userMessage):"");
						$userMessage = ((!empty($userMessage)) ? str_replace('[MESSAGE]', $userMessage, $this->lang->line('general_message_template')) : "");
						$msg = str_replace('[MESSAGE]', $userMessage, $msg);
						$msg = str_replace('[REQUEST_ADMIN_URL]', anchor('/league/leagueInvites/'.$this->dataModel->id,'League Invitiation/Request Admin Page'), $msg);
						$msg = str_replace('[LEAGUE_NAME]', $this->league_model->league_name,$msg);
						$data['messageBody']= $msg;
						//print("email template path = ".$this->config->item('email_templates')."<br />");
						$data['leagueName'] = $this->dataModel->league_name;
						$data['title'] = $this->lang->line('email_league_team_request_title');
						$message = $this->load->view($this->config->item('email_templates').'general_template', $data, true);

						$subject 	 = $this->dataModel->league_name. " Team Request";

						$success = sendEmail($this->user_auth_model->getEmail($this->dataModel->commissioner_id),
										 $this->user_auth_model->getEmail($this->params['config']['primary_contact']),
										 $this->params['config']['site_name']." Adminstrator",
				             			 $subject, $message,'','email_team_request_');


						$outMess = str_replace('[LEAGUE_NAME]',$this->dataModel->league_name,$this->lang->line('league_finder_request_success'));
						$this->session->set_flashdata('message', '<span class="success">'.$outMess.'</span>');
						redirect('league/leagueList/');
					} else {
						if ($this->dataModel->errorCode != -1) {
							$this->session->set_flashdata('message', '<span class="error">An error occured submitting your request: '.$this->dataModel->statusMess.'</span>');
							redirect('league/leagueList/');
						}
					}
				}
				$this->data['subTitle'] = $this->lang->line('league_finder_request_title');
				$this->data['scoring_type'] = $this->dataModel->getScoringType();
				$this->data['league_id'] = $this->dataModel->id;
				if ($this->data['scoring_type'] == LEAGUE_SCORING_HEADTOHEAD) {
					$this->data['thisItem']['divisions'] = $this->dataModel->getFullLeageDetails(false, true);
				} else {
					$this->data['thisItem']['teams'] = $this->dataModel->getTeamDetails(false,false,true);
				}
				$this->data['invites'] = $this->dataModel->getLeagueInvites(true);
				$this->data['requests'] = $this->dataModel->getLeagueRequests(true);

				$this->data['league_finder_intro_str'] = $this->lang->line('league_finder_request_inst');
				$this->params['content'] = $this->load->view($this->views['TEAM_REQUEST'], $this->data, true);
				$this->params['pageType'] = PAGE_FORM;
				$this->makeNav();
				$this->displayView();
			} else {
				$this->session->set_flashdata('message', '<span class="error">'.$this->lang->line('league_finder_request_no_id').'</span>');
				redirect('league/leagueList/');
			}

		} else {
	        $this->session->set_userdata('loginRedirect',current_url());
			redirect('user/login');
	    }
	}
	/**------------------------------------------------------------------------------------------
	 * 
	 * 	WITHDRAW REQUEST
	 *  A function that allows a user to withdraw a team request.
	 * 
	 * 	@since	1.1 PROD
	 * 
	 ------------------------------------------------------------------------------------------*/
	public function withdrawRequest() {
		if ($this->params['loggedIn']) {
			$this->init();
			$request_id = -1;
			$$league_id = -1;
			$request_type = false;
			$request_reponse = '';
			$this->loadData();
			if ($this->input->post('submitted')) {
				$request_id = $this->input->post('request_id') ? $this->input->post('request_id') : -1;
				$request_type = $this->input->post('type_id') ? $this->input->post('type_id') : false;
				$request_reponse = $this->input->post('reqMessage') ? $this->input->post('reqMessage') : "";
				$league_id = $this->input->post('league_id') ? $this->input->post('league_id') : -1;
			}

			$this->dataModel->load($league_id);
			// CONTINUE ONLY IF WE HAVE A VALID REQUEST ID AND RESPONSE TYPE
			if ($request_id != -1 && $league_id != -1 && $request_type !== false) {
				/* Get INVITE DETAILS */
				$requestObj = $this->dataModel->getLeagueRequest($request_id);

				if ($requestObj->status_id == REQUEST_STATUS_PENDING) {

					$changes = $this->dataModel->updateRequest($request_id, REQUEST_STATUS_WITHDRAWN);
					if ($changes == 0) {
						$error = true;
						$message = 'The invitation was not able to be withdrawn at this time.';
						if ($this->dataModel->errorCode != -1) {
							$message .= '<br />'.$this->dataModel->statusMess;
						} // END if
					} else {
						
						$this->load->model('team_model');
						$this->team_model->load($requestObj->team_id);
						$teamName = $this->team_model->teamname." ".$this->team_model->teamnick;
								
						$leagueName = $this->dataModel->league_name;
						$commishId = $this->dataModel->commissioner_id;
						if ($commishId == -1) {
							$commishId = $this->params['config']['primary_contact'];
						}
						$username = getUsername($requestObj->user_id);

						$message = 'The invitation has been marked as <b>withdrawn</b>.';

						// SEND EMAIL NOTITICATION TO THE INVITEE
						$msg = $this->lang->line('email_league_request_withdrawn');
						$msg .= $this->lang->line('email_footer_admin');
						$msg = str_replace('[TEAM_NAME]', $teamName, $msg);
						$msg = str_replace('[COMMISH]', getUsername($commishId), $msg);
						$msg = str_replace('[USERNAME]', $username , $msg);
						$msg = str_replace('[LEAGUE_NAME]', $leagueName,$msg);
						$msg = str_replace('[WEB_SITE]', '<a href="'.$this->params['config']['fantasy_web_root'].'">'.$this->params['config']['site_name'].'</a>',$msg);
						if (!empty($request_reponse)) {
							$msg = str_replace('[MESSAGE]', "'.$username .' said: '".$request_reponse."'<br />",$msg);
						} // END if
						$msg = str_replace('[SITE_NAME]', $this->params['config']['site_name'],$msg);
						$data['messageBody']= $msg;
						$data['leagueName'] = $leagueName;
						$data['title'] = $this->lang->line('email_league_request_withdrawn_title');
						$data['title'] = str_replace('[TEAM_NAME]', $teamName, $data['title']);
						$eMessage = $this->load->view($this->config->item('email_templates').'general_template', $data, true);

						$subject = $data['title'];

						$this->load->model('user_auth_model');
						
						$error = !sendEmail($this->user_auth_model->getEmail($commishId), $this->user_auth_model->getEmail($requestObj->user_id),
											$this->params['config']['site_name']." Adminstrator",
											$subject, $eMessage, $username,'email_team_request_withdrawl_');

						if ($error) {
							$class = 'error';
							$message .= '<br />An e-mail message to the Commissioner failed to send, however. It might be best to send an email to '.$this->user_auth_model->getEmail($commishId).' to let them know.';
						} else {
							$class = 'success';
							$message .= '<br />An e-mail message was sent to the Commissioner informing them the request has been withdrawn.';
						} // END if
					} // END if
				} else {
					$class = 'error';
					$message = "The Request to ".getUsername($commishId)." for team ".$teamName." is not an active Request. No action can be taken at this time.<br /><br />";
				} // END if
				$this->session->set_flashdata('message', '<span class="'.$class.'">'.$message.'</span>');
				//echo('<span class="'.$class.'">'.$message.'</span><br/>');
				redirect('league/leagueList');
			} else {
				$this->data['subTitle'] = "An error has occured";
				$this->data['theContent'] = '<span class="error">Required parameters were missing. The request cannot be completed at this time.</span>';
				$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
				$this->displayView();
			}
		} else {
			$this->session->set_userdata('loginRedirect',current_url());
			redirect('user/login');
		}
	}
	/**
	 *	TEAM REQUEST RESPONSE.
	 *	Handles the commissioners response to the team request. Response types 1 and -1 require the user to
	 *	be a commisioner or admin.<br />
	 *	<br />
	 *	<i>Edit 0.6</i> - Added ability to post to this method via GET or POST and commissioner to add a ressponse when
	 *	denying a reuqest.
	 *
	 *	REQUIRED URI VAR PARAMS:
	 *	@param	$request_id			The team request ID
	 *	@param	#$request_type		The reponse type.
	 *	<ul>
	 *		<li><b>1</b> 	- Accepted
	 *		<li><b>-1</b> 	- Denied
	 *		<li><b>2</b>	- Withdrawn by user
	 *	</ul>
	 *	@param	$request_reponse	Commissioners message to the requerster (Optional)
	 *
	 *	@since	0.5 beta
	 *
	 */
	public function requestResponse() {
		if ($this->params['loggedIn']) {
			$this->init();
			$request_id = -1;
			$request_type = false;
			$request_reponse = '';
			// GET REQUEST INFORMATION FROM THE APPROPRIATE INPUT SOURCE
			// CONVERT INPUT DATA TO DATA VARS
			if ($this->input->post('submitted')) {
				$request_id = $this->input->post('request_id') ? $this->input->post('request_id') : -1;
				$request_type = $this->input->post('type') ? $this->input->post('type') : false;
				$request_reponse = $this->input->post('message') ? $this->input->post('message') : "";
				$league_id = $this->input->post('league_id') ? $this->input->post('league_id') : -1;
			} else {
				$this->getURIData();
				$request_id = ((isset($this->uriVars['request_id'])) ? $this->uriVars['request_id'] : -1);
				$request_type = ((isset($this->uriVars['type'])) ? $this->uriVars['type'] : false);
				$league_id = ((isset($this->uriVars['league_id'])) ? $this->uriVars['league_id'] : false);
				$request_reponse = "";
			}
			$this->dataModel->load($league_id);
			// CONTINUE ONLY IF WE HAVE A VALID REQUEST ID AND RESPONSE TYPE
			if ($request_id != -1 && $request_type !== false) {
				if (($request_type == REQUEST_STATUS_WITHDRAWN) || ($request_type != REQUEST_STATUS_WITHDRAWN && $this->dataModel->commissioner_id == $this->params['currUser'] || $this->params['accessLevel'] == ACCESS_ADMINISTRATE)) {
					if ($this->dataModel->id != -1) {
						$targetUri = 'league/leagueInvites/'.$this->dataModel->id;
						$requestArr = $this->dataModel->getLeagueRequests(false, false, $request_id);
						$commish_id = $this->dataModel->commissioner_id;
						if ($commish_id == -1) {
							$commish_id = $this->params['config']['primary_contact'];
						}
						if (is_array($requestArr) && sizeof($requestArr) > 0) {
							$request = $requestArr[0];
						}
						$success = $this->dataModel->updateRequest($request_id, $request_type);
						if ($success) {
							// MESSAGE THE USER
							if (!isset($this->team_model)) {
								$this->load->model('team_model');
							}
							$outMess = "";
							$to = $this->user_auth_model->getEmail($request['user_id']);
							$footer = 'email_footer_commish';
							
							switch ($request_type) {
								case REQUEST_STATUS_ACCEPTED:
									$msg = $this->lang->line('email_league_team_request_accepted');
									$data['title'] = $this->lang->line('email_league_team_request_accepted_title');
									$outMess .= "The user has been assigned as the owner of this team successfully. ";
									$request_reponse = "The Commissioner said: &quot;".$request_reponse."&quot;";
									break;
								case REQUEST_STATUS_DENIED:
									$msg = $this->lang->line('email_league_team_request_denied');
									$data['title'] = $this->lang->line('email_league_team_request_denied_title');
									$outMess .= "The users request has been denied. ";
									$request_reponse = "The Commissioner said: &quot;".$request_reponse."&quot;";
									break;
								case REQUEST_STATUS_WITHDRAWN:
									$msg = $this->lang->line('email_league_team_request_withdrawn');
									$data['title'] = $this->lang->line('email_league_team_request_denied_title');
									$outMess .= "The team request has been successfully withdrawn. ";
									$to = $this->user_auth_model->getEmail($commish_id);
									$targetUri = '/user/profile';
									$request_reponse = getUsername($request['user_id'])." said: &quot;".$request_reponse."&quot;";
									$footer = 'email_footer_admin';
									break;
							}
							$msg .= $this->lang->line($footer);
							$msg = str_replace('[COMMISH]', getUsername($commish_id), $msg);
							$msg = str_replace('[TEAM_HOME_URL]', anchor('/team/info/'.$request['team_id'],'managing your team'),$msg);
							$msg = str_replace('[USERNAME]', getUsername($request['user_id']), $msg);
							$msg = str_replace('[TEAM_NAME]', $this->team_model->getTeamName($request['team_id']),$msg);
							$msg = str_replace('[MESSAGE]', $request_reponse, $msg);
							$msg = str_replace('[LEAGUE_NAME]', $this->dataModel->league_name,$msg);
							$msg = str_replace('[SITE_NAME]', $this->params['config']['site_name'],$msg);
							$data['messageBody']= $msg;
							$data['leagueName'] = $this->dataModel->league_name;
							$message = $this->load->view($this->config->item('email_templates').'general_template', $data, true);
							$subject 	 = $this->dataModel->league_name. " Team Request Response";
							$emailSent = sendEmail($to,$this->user_auth_model->getEmail($this->params['config']['primary_contact']),
											 $this->params['config']['site_name']." Adminstrator",
											 $subject, $message,'','email_team_request_resp');

							if($emailSent) {
								switch ($request_type) {
									case REQUEST_STATUS_ACCEPTED:
									case REQUEST_STATUS_DENIED:
										$outMess .= $this->lang->line('league_webpage_request_response_commish_decision');
										break;
									case REQUEST_STATUS_WITHDRAWN:
										$outMess .= $this->lang->line('league_webpage_request_response_user_decision');
										break;
								}
							} else {
								$outMess .= $this->lang->line('league_request_response_no_email_notfication');
							}
							$this->session->set_flashdata('message', '<span class="success">'.$outMess.'</span>');
						} else {
							if ($this->dataModel->errorCode != -1) {
								$this->session->set_flashdata('message', '<span class="error">An error occured submitting your response: '.$this->dataModel->statusMess.'</span>');
							}
						}
						redirect($targetUri);
					} else {
						$this->data['subTitle'] = "An error has occured.";
						$this->data['theContent'] = '<span class="error">'.$this->lang->line('league_finder_request_no_id').'</span>';
						$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
						$this->displayView();
					}
				} else {
					$this->data['subTitle'] = "Unauthorized Access";
					$this->data['theContent'] = '<span class="error">You are not authorized to access this page.</span>';
					$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
					$this->displayView();
				}
			} else {
				$this->data['subTitle'] = "An error has occured";
				$this->data['theContent'] = '<span class="error">Required parameters were missing. The request cannot be completed at this time.</span>';
				$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
				$this->displayView();
			}
		} else {
	        $this->session->set_userdata('loginRedirect',current_url());
			redirect('user/login');
	    }
	}
	/**
	 * 	CLEAR TEAM REQUEST QUEUE.
	 * 	This function clears the team request queue for the given league.
	 *
	 * 	@since	0.6 beta
	 * 	@see	models->league_model->removeTeamRequests()
	 */
	public function clearRequestQueue() {
		if ($this->params['loggedIn']) {
			$this->init();
			$this->loadData();
			if ($this->dataModel->id != -1) {
				if ($this->dataModel->commissioner_id == $this->params['currUser'] || $this->params['accessLevel'] == ACCESS_ADMINISTRATE) {
					$this->session->set_flashdata('message', '<p class="success">Team Request operation completed successfully. '.$this->dataModel->removeTeamRequests().' records were removed.</p>');
					redirect('league/leagueInvites/'.$this->dataModel->id);
				} else {
					$this->data['subTitle'] = "Unauthorized Access";
					$this->data['theContent'] = '<span class="error">You are not authorized to access this page.</span>';
					$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
					$this->displayView();
				}
			} else {
				$this->data['subTitle'] = "An error has occured.";
				$this->data['theContent'] = '<span class="error">'.$this->lang->line('league_finder_request_no_id').'</span>';
				$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
				$this->displayView();
			}
		} else {
			$this->session->set_userdata('loginRedirect',current_url());
			redirect('user/login');
		}
	}
	/*---------------------------------------------------------------------------
	/
	/	LEAGUE ADMIN
	/
	/--------------------------------------------------------------------------*/
	/**
	 *	TEAM ADMIN.
	 *	Draws and accepts changes to the team structure from the team admin screen.
	 *
	 *	@version 1.0 - Edit 1.0.5, changed array feeding the list based on scoring type
	 *
	 */
	public function teamAdmin() {
		if ($this->params['loggedIn']) {
			$this->init();
			$this->getURIData();
			$this->loadData();

			if ($this->dataModel->commissioner_id == $this->params['currUser'] || $this->params['accessLevel'] == ACCESS_ADMINISTRATE) {

				$this->data['scoring_type'] = $this->dataModel->getScoringType();


				if (!isset($this->team_model)) {
					$this->load->model('team_model');
				} // END if
				$teamList = $this->dataModel->getTeamIdList();
				foreach($teamList as $team_id) {
					$this->form_validation->set_rules($team_id.'_teamname', 'Team '.$team_id.' team name', 'required|trim');
					$this->form_validation->set_rules($team_id.'_teamnick', 'Team '.$team_id.' nick name', 'required|trim');
					if ($this->data['scoring_type'] == LEAGUE_SCORING_HEADTOHEAD) {
						$this->form_validation->set_rules($team_id.'_division_id', 'Team '.$team_id.' nick name', 'required');
					}
				} // END foreach
				$this->form_validation->set_error_delimiters('<p class="error">', '</p>');

				if ($this->form_validation->run() == false) {

					if ($this->data['scoring_type'] == LEAGUE_SCORING_HEADTOHEAD) {
						$this->data['thisItem']['divisions'] = $this->dataModel->getFullLeageDetails();
					} else {
						$this->data['thisItem']['teams'] = $this->dataModel->getTeamDetails();
					}
					$this->data['invites'] = $this->dataModel->getLeagueInvites(true);
					$this->data['requests'] = $this->dataModel->getLeagueRequests(true);
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
						$this->data['invites'] = $this->dataModel->getLeagueInvites(true);
						$this->data['requests'] = $this->dataModel->getLeagueRequests(true);

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
	/*---------------------------------------------------------------------------
	/
	/	LEAGUE WEB PAGES
	/
	/--------------------------------------------------------------------------*/
	/**
	 *	AVATAR.
	 *	Draws the avatar editor page
	 *
	 */
	public function avatar() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->loadData();

			if ($this->dataModel->commissioner_id == $this->params['currUser'] || $this->params['accessLevel'] == ACCESS_ADMINISTRATE) {
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
							$change = $this->dataModel->applyData($this->input, $this->params['league_id']);
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
	/**
	 *	LEAGUE LISTING.
	 *	Shows a list of leagues that are on the site. Replaces both the League Search and joinleague
	 *  screens
	 *
	 *	@since	1.0.3 PROD
	 *  
	 *
	 */
	public function leagueList() {
		$this->init();
		$this->data['league_list_intro_str'] = $this->lang->line('league_list_intro_str');
		$this->data['subTitle'] = $this->lang->line('league_list_title');
		$userVar = (isset($this->params['currUser']) && $this->params['currUser'] != -1) ? $this->params['currUser'] : false;
		$curr_period_id = 1;
		if ($this->params['config']['current_period'] > 1) {
			$curr_period_id = $this->params['config']['current_period'];
		}
		$this->data['curr_period_id'] = $curr_period_id;
		$this->data['leagueName'] =  $this->ootp_league_model->name;
		$this->data['current_year'] =  date('Y',(strtotime($this->ootp_league_model->current_date)));
		$this->data['league_list'] = $this->dataModel->getLeagueList($userVar, true, true);
		
		// EDIT 1.1.1 Add a header when the season is over
		if (!function_exists('getFantasyStatus')){
			$this->load->helper('general');
		}
		$status = getFantasyStatus();
		$this->data['fantasyStatus'] = getFantasyStatusLabel($status);
		$this->data['fantasyStatusID'] = $status;

		$this->data['loggedIn'] =$this->params['loggedIn'];
		$this->makeNav();
		$this->params['pageType'] = PAGE_FORM;
		$this->params['content'] = $this->load->view($this->views['LEAGUE_LIST'], $this->data, true);
		$this->displayView();
	}
	/**
	 *	LEAGUE RULES
	 *	Draws the current rules for the league
	 */
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

		$leagueType = loadSimpleDataList('leagueType');
		$this->data['scoring_type_str'] = $leagueType[$this->dataModel->league_type];


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
		$this->data['allow_playoff_trans'] = $this->dataModel->allow_playoff_trans;
		$this->data['allow_playoff_trades'] = $this->dataModel->allow_playoff_trades;
		$this->data['playoffRounds'] = $this->dataModel->playoff_rounds;
		$this->data['scorePeriods'] = $this->dataModel->regular_scoring_periods;
		$this->data['scoring_type'] = $this->dataModel->getScoringType();


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
		$scoring_type = $this->dataModel->getScoringType();
		$total_periods = 0;
		if ($scoring_type == LEAGUE_SCORING_HEADTOHEAD) {
			$total_periods = intval($this->dataModel->regular_scoring_periods)+ intval($this->dataModel->playoff_rounds);
		} else {
			$total_periods = getScoringPeriodCount();
		}
		$configPeriodId = intval($this->params['config']['current_period']);	

		if (isset($this->uriVars['period_id'])) {
			$curr_period_id = $this->uriVars['period_id'];
		} else {
			if ($configPeriodId == 1) {
				$curr_period_id = 1;
			} else if ($configPeriodId > 1 && $configPeriodId <= $total_periods) {
                $curr_period_id = $configPeriodId-1;
            } else {
                $curr_period_id = $total_periods;
            }
		}
		$curr_period = getScoringPeriod($curr_period_id);
		$this->data['curr_period'] = $curr_period_id;
		$this->data['avail_periods'] = $this->dataModel->getAvailableStandingsPeriods();

		$this->data['league_start'] = $this->params['config']['season_start'];
		$league_start_str = str_replace('[START_DATE]', date('m/d/Y',strtotime($this->params['config']['season_start'])), $this->lang->line('league_start_standings'));
		$this->data['start_str'] = str_replace('[GAME_YEAR]', date('Y',strtotime($this->ootp_league_model->start_date)), $league_start_str);
		$leagueStandings = $this->dataModel->getLeagueStandings($curr_period_id);
		$view = "";

		if ($scoring_type == LEAGUE_SCORING_HEADTOHEAD) {
			$this->data['thisItem']['divisions'] = $leagueStandings;
			$view = $this->views['STANDINGS_HEADTOHEAD'];
		} else {
			$this->data['thisItem']['teams'] = $leagueStandings;
			$this->data['thisItem']['rules'] = $this->dataModel->getScoringRules();
			$view = $this->views['STANDINGS_ROTISSERIE'];
		}
		$this->data['thisItem']['league_name'] = $this->dataModel->league_name;
		$this->makeNav();
		$this->params['content'] = $this->load->view($view, $this->data, true);
	    $this->displayView();
	}
	/**
	 *	LEAGUE RESULTS
	 *	Draws head to head games results for the league
	 */
	public function results() {
		$this->getURIData();
		$this->data['subTitle'] = "Game Results";
		$this->load->model($this->modelName,'dataModel');
		$this->dataModel->load($this->uriVars['id']);
		$this->data['league_id'] = $this->uriVars['id'];

		$this->load->model('team_model');

		$scoring_type = $this->dataModel->getScoringType();
		if (!function_exists('getScoringPeriod')) {
			$this->load->helper('admin');
		}
		$scoring_type = $this->dataModel->getScoringType();
		$total_periods = 0;
		if ($scoring_type == LEAGUE_SCORING_HEADTOHEAD) {
			$total_periods = intval($this->dataModel->regular_scoring_periods)+ intval($this->dataModel->playoff_rounds);
		} else {
			$total_periods = getScoringPeriodCount();
		}
		$configPeriodId = intval($this->params['config']['current_period']);	

		if (isset($this->uriVars['period_id'])) {
			$curr_period_id = $this->uriVars['period_id'];
		} else {
			if ($configPeriodId == 1) {
				$curr_period_id = 1;
			} else if ($configPeriodId > 1 && $configPeriodId <= $total_periods) {
                $curr_period_id = $configPeriodId-1;
            } else {
                $curr_period_id = $total_periods;
            }
		}
		$curr_period = getScoringPeriod($curr_period_id);
		$this->data['configPeriodId'] = $configPeriodId;

		$this->data['curr_period'] = $curr_period_id;

		$teams = $this->dataModel->getTeamIdList();
		$excludeList = array();
		foreach($teams as $team_id) {
			if (!$this->dataModel->validateRoster($this->team_model->getBasicRoster($curr_period_id,$team_id))) {
				array_push($excludeList,$team_id);
			}
		}
		//echo("Size of exclude list = ".count($excludeList)."<br />");
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
					$game_id = $id;
					break;
				 }
			}
			$game_display_data = $this->dataModel->loadGameData($game_id, $this->team_model,$excludeList);
		}

		// EDIT 1.0.3 PROD
		$this->data['owned_team'] = $this->team_model->getTeamByOwnerId($this->params['currUser']);

		$this->data['curr_period'] = $curr_period;
		$this->data['avail_periods'] = $this->dataModel->getAvailableScoringPeriods();
		$this->data['games'] = $games;
		$this->data['game_data'] = $game_display_data;
		$this->makeNav();
		$this->params['pageType'] = PAGE_FORM;
		$this->params['content'] = $this->load->view($this->views['RESULTS'], $this->data, true);
	    $this->displayView();
	}
	/**
	 *	LEAGUE SCHEDULE
	 *	Draws the current schedule for the selected head-to-head league
	 */
	public function schedule() {
		$this->getURIData();
		$this->data['subTitle'] = "League Schedule";
		$this->load->model($this->modelName,'dataModel');
		$this->dataModel->load($this->uriVars['id']);

		$this->data['isCommish'] = $this->dataModel->userIsCommish($this->params['currUser']);
		$this->data['isAdmin'] = $this->params['loggedIn'] && $this->params['accessLevel'] == ACCESS_ADMINISTRATE;

		// EDIT - 0.3 BETA  - SCHEUDLE EDITING CAPABILITIES
		if ($this->data['isCommish'] || $this->data['isAdmin']) {
			if (!function_exists('getCurrentScoringPeriod')) {
				$this->load->helper('admin');
			}
			if (isset($this->uriVars['period_id'])) {
				$curr_period_id = 	$this->uriVars['period_id'];
			} else {
				$curr_period_id = intval($this->params['config']['current_period']);
			}
			$this->data['curr_period'] = $curr_period_id;
			$this->data['avail_periods'] = $this->dataModel->getAvailableScoringPeriods();
			$this->data['max_reg_period'] = intval($this->dataModel->regular_scoring_periods);
			// EDIT 1.0.3 PROD - ACCOUNT FOR PLAYOFFS ENDING BEFORE FINAL SIM IS PROCESSED
			$this->data['total_periods'] = intval($this->dataModel->regular_scoring_periods) + intval($this->dataModel->playoff_rounds);
			$reverse = false;
			$baseTime = strtotime(EMPTY_DATE_STR);
			$timeStart = strtotime($this->ootp_league_model->start_date." 00:00:00");
			$timeCurr = strtotime($this->ootp_league_model->current_date." 00:00:00");

			if ($timeStart < $baseTime) { $reverse = true;}

			$this->data['schedleEdit'] = (($reverse) ? ($timeStart <= $timeCurr) : ($timeStart >= $timeCurr));
			$this->data['playoffEdit'] = (($reverse) ? (($timeStart > $timeCurr) && ($curr_period_id > $this->data['max_reg_period'] && $curr_period_id <= $this->data['total_periods'])) : (($timeStart <= $timeCurr) && ($curr_period_id > $this->data['max_reg_period'] && $curr_period_id <= $this->data['total_periods'])));
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
	 *	@since	0.3 BETA
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
	/**
	 *	LEAGUE TRANSACTIONS
	 *	Displays the transactions for the League.
	 */
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
	/**
	 * 	AFTER ADD.
	 * 	Executes after the new record has been successfuly added.
	 *
	 */
	public function afterAdd() {

		if (!isset($this->division_model)) {
			$this->load->model('division_model');
		}
		// ASSURE THERE ARE NO OTHER DIVISIONS FOR THIS LEAGUE PRIOR TO CREATING NEW ONES
		$this->division_model->clearDivisions($this->dataModel->id);

		// CREATE TWO DIVISIONS FOR THIS LEAGU
		// EDIT 1.0.6, ONLY DO THIS IF IT IS A HEAD-TO-HEAD LEAGUE
		$div1Id = -1;
		$div2Id = -1;
		if ($this->dataModel->league_type == LEAGUE_SCORING_HEADTOHEAD) {
			$divIds = $this->division_model->createDivisionsByArray(array(array("division_name"=>"Division A"),
							array("division_name"=>"Division B")),$this->dataModel->id);
			$div1Id = $divIds[0];
			$div2Id = $divIds[1];
		}

		if (!isset($this->team_model)) {
			$this->load->model('team_model');
		}
		$this->team_model->deleteTeams($this->dataModel->id);

		$teamsAdded = 0;
		$teamList = array();
		for ($i = 0; $i < $this->dataModel->max_teams; $i++) {
			$divId = -1;
			$teamData = array("teamname"=>"Team ".strtoupper(chr(64+($i+1))));
			if ($this->dataModel->league_type == LEAGUE_SCORING_HEADTOHEAD) {
				if ($i < ($this->dataModel->max_teams / 2)) {
					$divId = $div1Id;
				} else {
					$divId = $div2Id;
				}
				$teamData = $teamData + array("division_id"=>$divId);
			}
			if ($i == 0) { $teamData = $teamData + array("owner_id"=>$this->params['currUser']); }

			array_push($teamList,$teamData);
		}
		$teamIds = $this->team_model->createTeamsByArray($teamList,$this->dataModel->id);
		$teamsAdded = sizeof($teamIds);

		if ($teamsAdded < $this->dataModel->max_teams) {
			$this->outMess .= "Error adding teams. ".$teamsAdded." were added, ".$this->dataModel->max_teams." were required.";
		}

		if (!isset($this->draft_model)) {
			$this->load->model('draft_model');
		}
		$this->draft_model->setDraftDefaults($this->dataModel->id);
		return true;
	}
	/**
	* 	BEFORE DELETE.
	* 	Executes before a record has been successfuly deleted.
	*
	*/
	public function beforeDelete() {

		//LOAD REQUIRED MODELS
		if (!isset($this->team_model)) {
			$this->load->model('team_model');
		}
		if (!isset($this->draft_model)) {
			$this->load->model('draft_model');
		}
		if (!isset($this->news_model)) {
			$this->load->model('news_model');
		}
		// DELETE ALL TEAM SCORING
		$this->dataModel->deleteScoring($this->dataModel->id);
		// DELETE DRAFT SETTINGS
		$this->draft_model->deleteDraftSettings($this->dataModel->id);
		// DELETE DRAFT
		$this->draft_model->deleteCurrentDraft($this->dataModel->id);
		// DELETE ALL DRAFT LISTS
		$this->draft_model->deleteAllDraftLists($this->dataModel->id);
		// DELETE TRANSACTIONS
		$this->dataModel->deleteTransactions($this->dataModel->id);
		// DELETE ROSTERS
		$this->dataModel->deleteRosters($this->dataModel->id);
		// DELETE TRADES
		$this->dataModel->deleteTrades($this->dataModel->id);
		// DELETE ALL TEAM WAIVER CLAIMS
		$this->dataModel->deleteWaiverClaims($this->dataModel->id);
		// DELETE REQUESTS
		$this->dataModel->deleteTeamRequests($this->dataModel->id);
		// DELETE INVITES
		$this->dataModel->deleteTeamInvites($this->dataModel->id);
		// DELETE NEWS
		$this->news_model->deleteNews(NEWS_LEAGUE,$this->dataModel->id);

		// DELETE HEAD-TO-HEAD SPECIFIC DATA IF THAT TYPE OF LEAGUE
		if ($this->dataModel->getScoringType($this->dataModel->id) == LEAGUE_SCORING_HEADTOHEAD) {
			if (!isset($this->division_model)) {
				$this->load->model('division_model');
			}
			// DELETE DIVISIONS
			$this->division_model->clearDivisions($this->dataModel->id);
			// DELETE GAMES
			$this->dataModel->deleteSchedule($this->dataModel->id);
			// DELETE ALL TEAM RECORDS
			$this->dataModel->deleteRecords($this->dataModel->id);
		}
		// DELETE TEAMS
		$this->team_model->deleteTeams($this->dataModel->id);
		// DELETE AVATAR
		if (!empty($this->dataModel->avatar)) {
			@unlink(PATH_LEAGUES_AVATAR_WRITE.$this->dataModel->avatar);
		}
		return true;
	}

	public function autoDraftLeague() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->data['subTitle'] = "League Auto Draft";
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
	/**
	 * 	CHANGE COMMISSIONER
	 * 	Changes the Commissioner ID for a League
	 *
	 */
	public function changeCommissioner() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->loadData();
			if ((isset($this->uriVars['owner_id']) && !empty($this->uriVars['owner_id']) && $this->uriVars['owner_id'] != -1) &&($this->params['accessLevel'] == ACCESS_ADMINISTRATE || $this->params['currUser'] == $this->dataModel->commissioner_id)) {
				$this->dataModel->commissioner_id = $this->uriVars['owner_id'];
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
	/*--------------------------------------------------------------------------------
	/
	/	TEAM INVITATIONS
	/
	/-------------------------------------------------------------------------------*/
	/**
	 * 	INVITE OWNER
	 * 	Executes a team invitation from a League Commissioner
	 * 
	 * @see	$this->teamAdmin
	 */
	public function inviteOwner() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->loadData();
			if (!isset($this->uriVars['sent'])) {
				$error = false;
				if (!isset($this->user_auth_model)) {
					$this->load->model('user_auth_model');
				}
				$this->params['subTitle'] = $this->data['subTitle'] = "Invite New Owner";
				$this->data['owner_id'] = (isset($this->uriVars['owner_id'])) ? $this->uriVars['owner_id'] : '';
				$this->data['team_id'] = (isset($this->uriVars['team_id'])) ? $this->uriVars['team_id'] : '';
				$this->data['sent'] = (isset($this->uriVars['sent'])) ? true : false;

				$this->form_validation->set_rules('email', 'Email Address', 'valid_email');
				$this->form_validation->set_rules('inviteMessage', 'Invitation message', 'required');
				// ASSURE COMMISH PICKED A USER OR ENTERED AN EMAIL
				$userEmail = '';
				if ($this->input->post('email')) {
					$userEmail = $this->input->post('email');
				} else if ($this->input->post('user_id') && $this->input->post('user_id') != -1) {
					$userEmail = $this->user_auth_model->getEmail($this->input->post('user_id'));
				}

				if ($this->form_validation->run() == false || ($this->form_validation->run() == true && $this->input->post('submitted') == 1 && empty($userEmail))) {
					$this->data['customError'] = '';
					if ($this->input->post('submitted') == 1 && empty($userEmail)) {
						$this->data['customError'] = "<br />You must entier enter an e-mail address OR select a site member to complete your invitation.";
					}
					$this->data['input'] = $this->input;
					$this->data['league_id'] = $this->dataModel->id;
					// GET LIST OF SITE MEMBERS WHO DON'T OWN A TEAM IN THIS LEAGUE
					
					$users = $this->user_auth_model->getUserList(-1, true);
					$owners = $this->dataModel->getOwnerIds($this->data['league_id']);
					//echo(" = ".$."<br />");
					$displayableUsers = array('-1'=>'Select Site Member');
					foreach($users as $user) {
						$found = false;
						foreach($owners as $owner) {
							if ($owner == $user['id']) {
								$found = true;
								break;
							}
						}
						if (!$found)
							$displayableUsers[$user['id']] = $user['username'];
					}
					$this->data['availableUsers'] = $displayableUsers;
					$this->data['defaultMessage'] = str_replace('[LEAGUE_NAME]',$this->dataModel->league_name,$this->lang->line('league_invite_default'));
					$this->params['content'] = $this->load->view($this->views['INVITE'], $this->data, true);
					$this->params['pageType'] = PAGE_FORM;
					$this->makeNav();
					$this->displayView();
				} else {

					// TEST email is not registered as a team owner already
					$found = false;
					$prevTeamId = -1;
					$prevTeam = '';
					$owners = $this->dataModel->getDetailedOwnerInfo($this->dataModel->id);
					foreach($owners as $team_id => $owner_info) {
						if ($owner_info['email'] == $userEmail) {
							$found = true;
							$prevTeam = $owner_info['teamname']." ".$owner_info['teamnick'];
							$prevTeamId = $team_id;
							break;
						}
					}
					if (!$found) {
						// Check that an existing Invitation is not waiting in PENDING status
						$userInvites = $this->dataModel->getLeagueInvites(true, $this->dataModel->id, $userEmail);
						$count = sizeof($userInvites);
						if ($count == 0) {

							$confirmStr  = substr(md5(time().$userEmail),0,16);
							$confirmKey  = substr(md5($userEmail.time()),0,8);
							$email_mesg	 = "To ".$userEmail.",";
							$email_mesg	.= $this->input->post('inviteMessage');
							$link 		 = $this->params['config']['fantasy_web_root'].'user/inviteResponse/email/'.urlencode($userEmail).'/team_id/'.$this->data['team_id'].'/league_id/'.$this->dataModel->id.'/ck/'.md5($confirmStr.$confirmKey);
							$email_mesg	.= '<p><a href="'.$link.'/ct/'.INVITE_STATUS_ACCEPTED.'">Accept the invitation</a> <br /><br />';
							$email_mesg	.= '<p><a href="'.$link.'/ct/'.INVITE_STATUS_DECLINED.'">Decline the invitation</a> <br /><br />';
							$subject 	 = $this->dataModel->league_name. " Owner Invitation";
							
							$success = sendEmail($userEmail,
											$this->user_auth_model->getEmail($this->dataModel->commissioner_id),
											$this->dataModel->league_name." Commissioner",
											$subject, $email_mesg,'','email_lg_invite_');
							if (!$success) {
								$error = true;
								$message = 'The email message failed to send.';
							}
						} else {
							$error = true;
							$message = 'An invitation is already pending for '.$userEmail.'. They must decline the current invitation before another can be sent.<p />View a complete list of '.anchor('league/leagueInvites/'.$this->dataModel->id,'pending invitiations').'.';
						}
					} else {
						$error = true;
						$message = 'The e-mail address <b>'.$userEmail.'</b> is already an owner in your League of the <i>'.anchor('/team/info/'.$prevTeamId, $prevTeam).'</i>. Only one team is allowed per owner per League.';
					}
					if (!$error) {
						$inviteData = array('to_email'=>$userEmail,'from_id'=>$this->dataModel->commissioner_id,
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
	/**
	 * 	LEAGUE INVITES
	 * 	Returns the list of invites
	 * 
	 * @see	$this->teamAdmin
	 */
	public function leagueInvites() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->loadData();
			if (isset($this->uriVars['league_id']) && $this->uriVars['league_id'] != -1) {
				$this->dataModel->load($this->uriVars['league_id']);
			}
			if ($this->params['accessLevel'] == ACCESS_ADMINISTRATE || $this->params['currUser'] == $this->dataModel->commissioner_id) {

				$pendingOnly = true;
				$this->data['show_all'] = -1;
				if (isset($this->uriVars['show_all']) && $this->uriVars['show_all'] == 1) {
					$pendingOnly = false;
					$this->data['show_all'] = $this->uriVars['show_all'] ;
				}
				$this->data['thisItem']['invites'] = $this->dataModel->getLeagueInvites($pendingOnly);
				$this->data['thisItem']['requests'] = $this->dataModel->getLeagueRequests($pendingOnly);
				$this->data['league_id'] = $this->dataModel->id;
				$this->data['subTitle'] = 'Invites and Requests';
				$this->params['content'] = $this->load->view($this->views['INVITES'], $this->data, true);
				$this->params['pageType'] = PAGE_FORM;
				$this->makeNav();
				$this->displayView();
			} else {
				$error = true;
				$this->params['subTitle'] = $this->data['subTitle'] = "Unauthorized Access";
				$message = '<span class="error">You do not have sufficient privlidges to access the requested information.</span>';
				$this->data['theContent'] = $message;
				$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
				$this->makeNav();
				$this->displayView();
			}
		} else {
	        $this->session->set_flashdata('loginRedirect',current_url());
			redirect('user/login');
	    }
	}
	public function resendInvitation() {
		if ($this->params['loggedIn']) {
			
			$this->data['invite_id'] = isset($this->uriVars['invite_id']) ? $this->uriVars['invite_id'] : -1;
			$this->data['league_id'] = isset($this->uriVars['league_id']) ? $this->uriVars['league_id'] : -1;

			if ($this->params['accessLevel'] == ACCESS_ADMINISTRATE || $this->params['currUser'] == $this->dataModel->commissioner_id) {
				
				$this->dataModel->load($this->data['league_id']);

				if ($this->data['invite_id'] != -1 && $this->data['league_id'] != -1) {
	
					/* Get INVITE DETAILS */
					$inviteObj = $this->dataModel->getLeagueInvite($this->data['invite_id']);

					if ($inviteObj->id != -1) {
						$confirmStr  = $inviteObj->confirm_str;
						$confirmKey  = $inviteObj->confirm_key;
						$email_mesg	 = "To ".$inviteObj->to_email.",<br />\n\r";
						$email_mesg	.= str_replace('[LEAGUE_NAME]',$this->dataModel->league_name,$this->lang->line('league_invite_default'));;
						$link 		 = $this->params['config']['fantasy_web_root'].'user/inviteResponse/email/'.urlencode($inviteObj->to_email).'/team_id/'.$inviteObj->team_id.'/league_id/'.$inviteObj->league_id.'/ck/'.md5($inviteObj->confirm_str.$inviteObj->confirm_key);
						$email_mesg	.= '<p><a href="'.$link.'/ct/1">Accept the invitation</a> <br /><br />';
						$email_mesg	.= '<p><a href="'.$link.'/ct/-1">Decline the invitation</a> <br /><br />';
						$subject 	 = $this->dataModel->league_name. " Owner Invitation";
						
						$success = sendEmail($userEmail,
										 $this->user_auth_model->getEmail($this->dataModel->commissioner_id),
										 $this->dataModel->league_name." Commissioner",
				             			 $subject, $email_mesg,'','email_lg_invite_');
						if (!$success) {
							$error = true;
							$message = '<span class="error">The email message failed to resend.</span>';
						} else {
							$message = '<span class="success">Your invitation was resent successfully.</span>';
						}
						$this->session->set_flashdata('message',$message);
					} else {
						$message = "The invitation could not be loaded. Check the link to assure there are no errors.";
						if ($inviteObj->errorCode != -1) {
							$message .= "Error: ".$inviteObj->statusMess."<br /><br />";
						}
						$this->session->set_flashdata('message', '<span class="error">'.$message.'</span>');
					}
				} else {
					$this->session->set_flashdata('message', '<span class="error">Required data parameters were not recieved. Please check the link and try again.</span>');
				}
				redirect('league/leagueInvites/'.$this->dataModel->id);
			} else {
				$this->params['subTitle'] = $this->data['subTitle'] = "Unauthorized Access";
				$message = '<span class="error">You do not have sufficient privlidges to access the requested information.</span>';
				$this->data['theContent'] = $message;
				$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
				$this->makeNav();
				$this->displayView();
			}
		} else {
	        $this->session->set_flashdata('loginRedirect',current_url());
			redirect('user/login');
	    }
	}
	/**------------------------------------------------------------------------------------------
	 * 
	 * 	WITHDRAW INVITIATION
	 *  A function that allows a Leage Commissioner to withdraw a team invite.
	 * 
	 *  @param	invite_id		(int)	The invite ID
	 *  @param	league_id		(int)	The League ID for messaging purposes
	 *  @param	invMessage		(int)	A message from the Commissioner to the user
	 * 
	 * 	@since	1.1 PROD
	 * 
	 ------------------------------------------------------------------------------------------*/
	public function withdrawInvitation() {
		
		if ($this->params['loggedIn']) {
			
			$this->data['invite_id'] = $this->input->post('invite_id') ? $this->input->post('invite_id') : -1;
			$this->data['league_id'] = $this->input->post('league_id') ? $this->input->post('league_id'): -1;

			if ($this->data['invite_id'] != -1 && $this->data['league_id'] != -1) {

				$this->dataModel->load($this->data['league_id']);

				if ($this->params['accessLevel'] == ACCESS_ADMINISTRATE || $this->params['currUser'] == $this->dataModel->commissioner_id) {
					
					/* Get INVITE DETAILS */
					$inviteObj = $this->dataModel->getLeagueInvite($this->data['invite_id']);

					if ($inviteObj->status_id == INVITE_STATUS_PENDING) {

						$changes = $this->dataModel->updateInvitation($this->data['invite_id'], INVITE_STATUS_WITHDRAWN);
						if ($changes == 0) {
							$error = true;
							$message = 'The invitation was not able to be withdrawn at this time.';
							if ($this->dataModel->errorCode != -1) {
								$message .= '<br />'.$this->dataModel->statusMess;
							} // END if
						} else {
							$respMessage = $this->input->post('invMessage') ? $this->input->post('invMessage') : '';
					
							$this->load->model('team_model');
							$this->team_model->load($inviteObj->team_id);
							$teamName = $this->team_model->teamname." ".$this->team_model->teamnick;
									
							$leagueName = $this->dataModel->league_name;
							$commishId = $this->dataModel->commissioner_id;

							$message = 'The invitation has been marked as <b>withdrawn</b>.';

							// SEND EMAIL NOTITICATION TO THE INVITEE
							$msg = $this->lang->line('email_league_invite_withdrawn');
							$msg = str_replace('[TEAM_NAME]', $teamName, $msg);
							$msg = str_replace('[COMMISH]', getUsername($commishId), $msg);
							$msg = str_replace('[EMAIL]', $inviteObj->to_email, $msg);
							$msg = str_replace('[LEAGUE_NAME]', $leagueName,$msg);
							$msg = str_replace('[WEB_SITE]', '<a href="'.$this->params['config']['fantasy_web_root'].'">'.$this->params['config']['site_name'].'</a>',$msg);
							if (!empty($respMessage)) {
								$msg = str_replace('[MESSAGE]', "The Commissioner said: '".$respMessage."'<br />",$msg);
							} // END if
							$data['messageBody']= $msg;
							$data['leagueName'] = $leagueName;
							$data['title'] = $this->lang->line('email_league_invite_withdrawn_title');
							$data['title'] = str_replace('[TEAM_NAME]', $teamName, $data['title']);
							$eMessage = $this->load->view($this->config->item('email_templates').'general_template', $data, true);

							$subject = $data['title'];

							$this->load->model('user_auth_model');
							
							$error = !sendEmail($inviteObj->to_email, $this->user_auth_model->getEmail($commishId),
											$this->params['config']['site_name']." Adminstrator",
											$subject, $eMessage, getUsername($commishId),'email_team_invite_withdrawl_');

							if ($error) {
								$class = 'error';
								$message .= '<br />An e-mail message to the user failed to send, however. It might be best to send an email to '.$inviteObj->to_email.' to let them know.';
							} else {
								$class = 'success';
								$message .= '<br />An e-mail message was sent to '.$inviteObj->to_email.' informing them the invitation has been withdrawn.';
							} // END if
						} // END if
					} else {
						$class = 'error';
						$message = "The invitation to ".$inviteObj->to_email." for team ".$teamName." is not a pending invitation. No action can be taken at this time.<br /><br />";
					} // END if
					$this->session->set_flashdata('message', '<span class="'.$class.'">'.$message.'</span>');
					//echo('<span class="'.$class.'">'.$message.'</span><br/>');
					redirect('league/leagueInvites/'.$this->data['league_id']);
				} else {
					$error = true;
					$this->params['subTitle'] = $this->data['subTitle'] = "Unauthorized Access";
					$message = '<span class="error">You do not have sufficient privlidges to access the requested information.</span>';
					$this->data['theContent'] = $message;
					$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
					$this->makeNav();
					$this->displayView();
				} // END if
			} else {
				$message = "A required invitation and/or League ID was not received.<br /><br />";
				$this->session->set_flashdata('message', '<span class="error">'.$message.'</span>');
				redirect('league/leagueInvites/'.$this->data['league_id']);
			} // END if
		} else {
			$this->session->set_flashdata('loginRedirect',current_url());
			redirect('user/login');
		} // END if
	} // END function

	/*----------------------------------------------------------------------------
	/
	/	WAIVER CLAIMS
	/
	/---------------------------------------------------------------------------*/
	/**
	 * 	WAIVER CLAIMS
	 * 	Returns the list of invites
	 * 
	 * @see	$this->teamAdmin
	 */
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
	/**---------------------------------------------------------
	 * 	REMOVE FROM WAIVERS
	 * 	Removes a selected player from the wai8ver wire. This 
	 *  would be due to a roster error in which the player has 
	 *  been reinstated to a team by the commissioner.
	 * 
	 */
	public function removeFromWaivers() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			if ($this->params['accessLevel'] == ACCESS_ADMINISTRATE || $this->params['currUser'] == $this->dataModel->commissioner_id) {
				if (isset($this->uriVars['league_id']) && isset($this->uriVars['player_id'])) {
					$this->dataModel->removeFromWaivers($this>uriVars['player_id'], $this->uriVars['league_id']);
					$error = false;
					$message = '<span class="success">Waiver status entry and waiver claims for this player have been removed.</span>';
				} else {
					$error = true;
					$message = '<span class="error">A required player identifier was not found. Please go back and try the operation again or contact the site adminitrator to report the problem.</span>';
				}
			} else {
				$error = true;
				$message = '<span class="error">You do not have sufficient privlidges to access the requested information.</span>';
			}
			$this->session->set_flashdata('message', $message);
			redirect('league/waiverClaims/'.$this->uriVars['league_id']);
		} else {
	        $this->session->set_flashdata('loginRedirect',current_url());
			redirect('user/login');
	    }

	}
	public function removeClaim() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->loadData();
			if ($this->dataModel->id == -1) {
				$this->dataModel->load($this->uriVars['league_id']);
			}
			if ($this->params['accessLevel'] == ACCESS_ADMINISTRATE || $this->params['currUser'] == $this->dataModel->commissioner_id) {
				if (isset($this->uriVars['claim_id'])) {
					// EDIT 1.0.5 -
					// SQL function moved to League Model. Added claim return object to return
					// relevant claim details so email can be sent to the user
					$claim = $this->dataModel->denyWaiverClaim($this->uriVars['claim_id']);

					// EDIT 1.0.5 -
					// Inform the team owner that their claim has been denied by the commisioner
					$msg = $this->lang->line('league_waiver_claim_denied');
					$msg .= $this->lang->line('email_footer');
					$msg = str_replace('[COMMISH]', getUsername($this->dataModel->commissioner_id), $msg);
					if (!isset($this->team_model)) {
						$this->load->model('team_model');
					} // END if
					$ownerId = $this->team_model->getTeamOwnerId($claim['team_id']);
					$username = getUsername($ownerId);
					$msg = str_replace('[USERNAME]', $username, $msg);
					if (!isset($this->player_model)) {
						$this->load->model('player_model');
					} // END if
					$msg = str_replace('[PLAYER_NAME]', $claim['player_name'], $msg);
					$msg = str_replace('[PERIOD]', $claim['waiver_period'], $msg);
					$msg = str_replace('[LEAGUE_NAME]', $this->dataModel->league_name,$msg);
					$data['messageBody']= $msg;
					//print("email template path = ".$this->config->item('email_templates')."<br />");
					$data['leagueName'] = $this->dataModel->league_name;
					$data['title'] = $this->lang->line('league_waiver_claim_denied_title');
					$emailMessage = $this->load->view($this->config->item('email_templates').'general_template', $data, true);
					// SEND TO TEAM
					## Generate Subject Line
					$subject= str_replace('[LEAGUE_NAME]',$this->dataModel->league_name,$this->lang->line('league_waiver_claim_denied_title'));
					$mailTo = getEmail($ownerId);

					if ((!empty($mailTo)) && (!empty($emailMessage))) {
						$emailSend = sendEmail($mailTo,$this->user_auth_model->getEmail($this->params['config']['primary_contact']),
						$this->params['config']['site_name']." Administrator",$subject,$emailMessage,'','league_waiver_denial_');
					}
					unset($data['title']);
					unset($data['messageBody']);
					unset($subject);
					unset($emailMessage);
					/*-----------------------------------------
					/
					/	END MESSAGING BLOCK
					/
					/-----------------------------------------*/
					$message = $this->lang->line('league_waiver_claim_denied_response');
					$message = str_replace('[USERNAME]', $username, $message);
					$message = str_replace('[PLAYER_NAME]', $claim['player_name'], $message);
					$message = '<span class="success">'.$message.'</span>';
				} else {
					$error = true;
					$message = '<span class="error">A required claim identifier was not found. Please go back and try the operation again or contact the site adminitrator to report the problem.</span>';
				}
			} else {
				$error = true;
				$message = '<span class="error">You do not have sufficient privlidges to perform the requested action.</span>';
			}
			$this->session->set_flashdata('message', $message);
			redirect('league/waiverClaims/'.$this->dataModel->id);
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
					// EDIT 1.0.6 - GET OWNER ID FOR TEAM
					$owner_id = $this->team_model->getTeamOwnerId();
					// REMOVE OWNER
					$this->team_model->owner_id = -1;
					$this->team_model->save();
					$message = '<span class="success">The owner has been successfully removed from the selected team.</span>';

					// EDIT 0.6 - REMOVE ANY PENDING REQUESTS FROM OR INVITES TO THIS OWNER
					// EDIT 1.1 PROD - Changes to REMOVE from DELETE to keep request history
					$this->dataModel->removeTeamInvites(false, $owner_id);
					$this->dataModel->removeTeamRequests(false, $owner_id);

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
					$startIndex = ($limit * ($pageId - 1))-1;
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
					$this->data['trades'] = $this->team_model->getAllTrades($this->dataModel->id, true, $this->data['limit'],$this->data['startIndex']);
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
	/
	/	PRIVATE/PROTECTED FUNCTIONS
	/
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
		if ($this->input->post('request_id')) {
			$this->uriVars['request_id'] = $this->input->post('request_id');
		} // END if
		if ($this->input->post('claim_id')) {
			$this->uriVars['claim_id'] = $this->input->post('claim_id');
		} // END if

	}
	protected function makeForm() {
		
		// WE can always EDIT an existign League, but there are restrcitctions for ADDING Leagues
		if ((!isset($this->mode) || (isset($this->mode) && $this->mode != 'edit')) && $this->data['accessLevel'] < ACCESS_ADMINISTRATE) {
			if (getFantasyStatus() != 1) {
				$this->outMess = '<span class="warn">Leagues cannot be created anymore once a Fantasy Season has begun play. Please wait until the current Fantasy Season has concluded to try and create a new League.</span>';	
				$this->status = 1;
			} else {
				if ($this->params['config']['users_create_leagues'] != 1) {
					$this->outMess = '<span class="error">We\'re sorry. Members of this site are not allowed to create their own Leagues. Only an Administrator can create new Leagues.</span>';	
					$this->status = 1;
				} else {
					$leagueCount = $this->dataModel->userLeagueCount($this->params['currUser']);
					if ($leagueCount > 0 && $leagueCount >= $this->params['config']['max_user_leagues']) {
						$this->outMess = '<span class="error">You have already created <b>'.$leagueCount.'</b> Leagues on this site. Users are allowed to create only <b>'.$this->params['config']['max_user_leagues'].'</b> Leagues by ther Administrator.</span>';	
						$this->status = 1;
					} // END if
				} // END if
			} // END if
		} // END if
		if (!function_exists('getScoringPeriodCount')) {
			$this->load->helper('admin');
		}
		if (sizeof($this->ootp_league_model->getMissingTables()) > 0 || getScoringPeriodCount() == 0) {
			$this->outMess = '<span class="error">Leagues cannot be created until the Site Adminstrator has completed the intial setup of the site. Please try again soon.</span>';	
			$this->status = 1;
		} // END if
		if ($this->status == -1) {

			$form = new Form();

			$form->open('/'.$this->_NAME.'/submit/','detailsForm|detailsForm');

			$form->fieldset('<h3>League Details</h3>');

			$form->text('league_name','League Name','required|trim',($this->input->post('league_name')) ? $this->input->post('league_name') : $this->dataModel->league_name,array("class"=>"longtext"));
			$form->br();
			$form->textarea('description','Description:','',($this->input->post('description')) ? $this->input->post('description') : $this->dataModel->description,array('rows'=>5,'cols'=>65));
			$form->br();
			$responses[] = array('1','Yes');
			$responses[] = array('-1','No');
			$form->fieldset('',array('class'=>'radioGroup'));
			$form->radiogroup ('accept_requests',$responses,'Accept Public Team Requests',($this->input->post('accept_requests') ? $this->input->post('accept_requests') : $this->dataModel->accept_requests),'required');
			$form->fieldset();
			$form->select('access_type|access_type',loadSimpleDataList('accessType'),'Access Type',($this->input->post('access_type')) ? $this->input->post('access_type') : $this->dataModel->access_type,'required');
			$form->br();
			if ($this->mode != 'edit' || $this->data['accessLevel'] == ACCESS_ADMINISTRATE) {
				$league_type = ($this->input->post('league_type')) ? $this->input->post('league_type') : $this->dataModel->league_type;
				$form->select('league_type|league_type',listLeagueTypes(true,true),'Scoring System',$league_type,'required');
				$this->data['league_type'] = $league_type;
				$form->br();
				$form->select('max_teams|max_teams',array(8=>8,10=>10,12=>12),'No. of Teams',($this->input->post('max_teams')) ? $this->input->post('max_teams') : $this->dataModel->max_teams,'required');
				$form->br();
				if ($this->data['accessLevel'] == ACCESS_ADMINISTRATE) {
					$form->select('league_status|league_status',loadSimpleDataList('leagueStatus'),'Status',($this->input->post('league_status')) ? $this->input->post('league_status') : $this->dataModel->league_status,'required');
					$form->br();
				}
				$form->space();
				$form->fieldset('<h3>Head To Head Options</h3>',array('id'=>'optHeadToHead'));
				$form->select('games_per_team|games_per_team',array(1=>1,2=>2,3=>3),'Games per team',($this->input->post('games_per_team')) ? $this->input->post('games_per_team') : $this->dataModel->games_per_team);
				$form->nobr();
				$form->html('<div style-"display:inline-block;margin-top:4px;">Per Scoring Period</div>');
				$form->br();
				if (!function_exists('getScoringPeriods')) {
					$this->load->helper('admin');
				}
				$periods = getScoringPeriods();
				$periodCount = sizeof($periods);
				for ($i = (sizeof($periods) - 1); $i > 0; $i--) {
					if ($periods[$i]['date_start'] == $periods[$i]['date_end']) {
						$periodCount--;
					} else {
						break;
					}
				}
				$periods_choices = array();
				$counter = $periodCount -2;
				while ($counter > $periodCount - 7) {
					$periods_choices = $periods_choices + array($counter => $counter);
					$counter--;
				}
				$form->select('regular_scoring_periods|regular_scoring_periods',$periods_choices,'Playoffs begin in week',($this->input->post('regular_scoring_periods')) ? $this->input->post('regular_scoring_periods') : $this->dataModel->regular_scoring_periods);
				$form->nobr();
				$form->html('<div style-"display:inline-block;margin-top:4px;">Scoring Periods Available: '.$periodCount.'</div>');
				$form->html('<script> var scoring_periods_available = '.$periodCount.';</script>');
				$form->br();
				$form->select('playoff_rounds|playoff_rounds',array(1=>1,2=>2,3=>3),'Playoff Rounds',($this->input->post('playoff_rounds')) ? $this->input->post('playoff_rounds') : $this->dataModel->playoff_rounds);
				$form->fieldset('',array('class'=>'radioGroup','id'=>'optPlayoffTrans'));
				$form->radiogroup ('allow_playoff_trans',$responses,'Allow Add/Drops during Playoffs',($this->input->post('allow_playoff_trans') ? $this->input->post('allow_playoff_trans') : $this->dataModel->allow_playoff_trans));
				$form->fieldset('',array('class'=>'radioGroup','id'=>'optPlayoffTrades'));
				$form->radiogroup ('allow_playoff_trades',$responses,'Allow Trades during Playoffs',($this->input->post('allow_playoff_trades') ? $this->input->post('allow_playoff_trades') : $this->dataModel->allow_playoff_trades));
				$form->br();
			}
			$form->fieldset('',array('class'=>'button_bar'));
			$form->button('Delete','delete','button',array('class'=>'sitebtn register'));
			$form->nobr();
			$form->span(' ','style="margin-right:8px;display:inline;"');
			$form->button('Cancel','cancel','button',array('class'=>'sitebtn lineup'));
			$form->nobr();
			$form->span(' ','style="margin-right:8px;display:inline;"');
			$form->submit('Submit','submit',array('class'=>'sitebtn create_league'));
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
	}
	function configInfo() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$this->data['subTitle'] = "League Settings";
			$this->load->model($this->modelName,'dataModel');
			$this->dataModel->load($this->uriVars['id']);

			$this->data['isCommish'] = $this->dataModel->userIsCommish($this->params['currUser']);

			if ($this->data['isCommish']) {

				$this->data['thisItem']['avatar'] = $this->dataModel->avatar;
				$this->data['thisItem']['league_name'] = $this->dataModel->league_name;
				$this->data['thisItem']['league_id'] = $this->dataModel->id;
				$this->data['thisItem']['description'] = $this->dataModel->description;
				$this->data['thisItem']['max_teams'] = $this->dataModel->max_teams;
				$this->data['thisItem']['accept_requests'] = $this->dataModel->accept_requests;
				$accessType = loadSimpleDataList('accessType');
				$this->data['thisItem']['access_type'] = $accessType[$this->dataModel->access_type];
				$leagueType = loadSimpleDataList('leagueType');
				$this->data['thisItem']['league_type'] = $leagueType[$this->dataModel->league_type];
				$commishName = '';
				$this->db->select('firstName, lastName');
				$this->db->where('userId',$this->dataModel->commissioner_id);
				$query = $this->db->get('users_meta');
				if ($query->num_rows() > 0) {
					$row = $query->row();
					$commishName = (!empty($row->firstName) && $row->lastName != -1)  ? $row->firstName." ".$row->lastName : '';
				} // END if
				$query->free_result();
				$this->data['thisItem']['commissioner'] = $commishName;
				$this->data['thisItem']['commissioner_id'] = $this->dataModel->commissioner_id;

				// HEAD TO HEAD ONLY
				$this->data['thisItem']['games_per_team'] = $this->dataModel->games_per_team;
				$this->data['thisItem']['playoff_rounds'] = $this->dataModel->playoff_rounds;
				$this->data['thisItem']['regular_scoring_periods'] = $this->dataModel->regular_scoring_periods;

				$this->data['scoring_type'] = $this->dataModel->getScoringType();
				$this->params['pageType'] = PAGE_FORM;
				$this->params['content'] = $this->load->view($this->views['REVIEW_SETTINGS'], $this->data, true);
			} else {
				$this->data['subTitle'] = "Unauthorized Access";
				$this->data['theContent'] = '<span class="error">You are not authorized to access this page.</span>';
				$this->params['content'] = $this->load->view($this->views['FAIL'], $this->data, true);
			}
			$this->makeNav();
			$this->displayView();
		} else {
	        $this->session->set_userdata('loginRedirect',current_url());
			redirect('user/login');
	    }
	}
	protected function showInfo() {
		$this->data['thisItem']['avatar'] = $this->dataModel->avatar;
		$this->data['thisItem']['league_name'] = $this->dataModel->league_name;
		$this->data['thisItem']['description'] = $this->dataModel->description;
		$this->data['scoring_type'] = $this->dataModel->getScoringType();
		if ($this->data['scoring_type'] == LEAGUE_SCORING_HEADTOHEAD) {
			$this->data['thisItem']['divisions'] = $this->dataModel->getFullLeageDetails();
		} else {
			$this->data['thisItem']['teams'] = $this->dataModel->getTeamDetails();
		}

		$this->data['hasAccess'] = (isset($this->params['currUser']) && $this->params['currUser'] != -1) ? $this->dataModel->isLeagueMember($this->params['currUser']) : false;


		$this->params['subTitle'] = "Fantasy League Overview";

		$this->makeNav();

		parent::showInfo();
	}
	protected function makeNav($private = false) {
		$admin = false;
		$scoring_type = $this->dataModel->getScoringType();
		if ($this->params['loggedIn'] && ($this->params['currUser'] == $this->dataModel->commissioner_id || $this->params['accessLevel'] == ACCESS_ADMINISTRATE)){
			$admin = true;
		}
		array_push($this->params['subNavSection'],league_nav($this->dataModel->id, $this->dataModel->league_name,$admin,false,$this->dataModel->getScoringType(),$private));
	}
	/*----------------------------------------------------
	/
	/	DEPRECATED FUNCTIONS
	/
	/---------------------------------------------------*/
	/**
	 *	FIND A LEAGUE.
	 *	Show a list of leagues that are 1) have teams without owners and 2) are open to requests from
	 *	site members.
	 *
	 *	@since	1.0.5
	 *	@deprecated 1.0.3 PROD onward, Use this->leaguelist() now insead
	 *
	 */
	public function joinleague() {
		$this->init();
		$this->data['league_finder_intro_str'] = $this->lang->line('league_finder_intro_str');
		$this->data['subTitle'] = $this->lang->line('league_finder_title');
		$userVar = (isset($this->params['currUser']) && $this->params['currUser'] != -1) ? $this->params['currUser'] : false;
		$this->data['league_list'] = $this->dataModel->getOpenLeagues($userVar);
		$this->makeNav();
		$this->params['content'] = $this->load->view($this->views['LEAGUE_LIST'], $this->data, true);
		$this->displayView();
	}
	
}
/* End of file league.php */
/* Location: ./application/controllers/league.php */
