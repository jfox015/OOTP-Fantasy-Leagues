<?php
/**
 *	Admin Access.
 *	The primary controller for the Admin Section.
 *	@author			Jeff Fox
 *	@dateCreated	11/13/09
 *	@lastModified	06/15/20
 *
 */
class admin extends MY_Controller {

	/*--------------------------------
	/	VARIABLES
	/-------------------------------*/

	var $_NAME = 'admin';
	/*--------------------------------
	/	C'TOR
	/-------------------------------*/
	/**
	 *	Creates a new instance of admin.
	 */
	public function admin() {
		parent::MY_Controller();
		$this->views['PENDING'] = 'content_pending';
		$this->views['DASHBOARD'] = 'admin/dashboard';
		$this->views['LIST_FILES'] = 'admin/file_list';
		$this->views['START_SEASON'] = 'admin/start_season';
		$this->views['CONFIG_GAME'] = 'admin/config_game';
		$this->views['CONFIG_FANTASY'] = 'admin/config_fantasy';
		$this->views['CONFIG_SOCIAL'] = 'admin/config_social';
		$this->views['CONFIG_INFO'] = 'admin/config_info';
		$this->views['CONFIG_ROSTERS'] = 'admin/config_rosters';
		$this->views['CONFIG_SCORING_RULES'] = 'admin/config_scoring_rules';
		$this->views['CONFIG_SCORING_PERIODS'] = 'admin/config_scoring_periods';
		$this->views['CONFIG_SCORING_PERIODS_EDIT'] = 'admin/config_scoring_periods_edit';
		$this->views['CONFIG_OOTP'] = 'admin/config_ootp';
		$this->views['CONFIG_ABOUT'] = 'admin/config_about';
		$this->views['CONFIG_SECURITY'] = 'admin/config_security';
		$this->views['SIM_SUMMARY'] = 'admin/sim_summary';
		$this->views['FILE_UPLOADS'] = 'admin/config_uploads';
		$this->views['ACTIVATE_USERS'] = 'admin/activate_user_list';
		$this->views['MESSAGE'] = 'admin/admin_message';

		$this->load->helper('admin');
		$this->lang->load('admin');
		$this->enqueStyle('jquery.ui.css');

		$this->debug = false;
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
		$this->data['loggedIn'] = $this->params['loggedIn'];
		$this->displayView();
		if (!$this->data['loggedIn']) {
			redirect('user/login');
		} else {
			redirect('admin/dashboard');
		}
	}
	/**
	 *	DASHBOARD.
	 *	Calls to index for auth verification.
	 */
	public function dashboard() {
		$this->data['message'] = '';
		$this->data['loggedIn'] = $this->auth->logged_in();
		if ($this->data['loggedIn'] && $this->data['accessLevel'] == ACCESS_ADMINISTRATE) {
			if ($this->auth->load_user()) {
				$this->data['name'] = $this->params['name'];
				$this->data['email'] = $this->user_auth_model->email;
			} else {
				if ($this->auth->get_status_code() != 0) {
					$this->data['message'] = "The server replied with the following status: ".$this->auth->get_status_message();
				}
			} // END if

			if (!isset($this->player_model)) {
				$this->load->model('player_model');
			}
			$this->data['playerCount'] = $this->player_model->getPlayerCount();

			$this->data['missingTables'] = $this->ootp_league_model->getMissingTables();

			$this->data['leagues'] = $this->league_model->getLeagues(-1);

			$this->data['in_season'] = intval($this->ootp_league_model->in_season());

			$this->data['currPeriod'] = getCurrentScoringPeriod($this->ootp_league_model->current_date);

            $this->data['currPeriodConfig'] = getScoringPeriod($this->params['config']['current_period']);
            if (isset($this->data['configCurrPeriodStart'])) {
				$this->data['configCurrPeriodStart'] = strtotime($this->data['currPeriodConfig']['date_start']." 00:00:00");
			} else {
				$this->data['configCurrPeriodStart'] = strtotime($this->ootp_league_model->current_date);
			}
            $this->data['nextPeriodConfig'] = getScoringPeriod(($this->params['config']['current_period']+1));
			if (isset($this->data['configCurrPeriodEnd'])) {
				$this->data['configCurrPeriodEnd'] = strtotime($this->data['nextPeriodConfig']['date_end']." 00:00:00");
			} else {
				$this->data['configCurrPeriodEnd'] = strtotime($this->ootp_league_model->current_date);
			}
			$this->data['periodCount'] = getScoringPeriodCount();
			if (!function_exists('getSQLFileList')) {
				$this->load->helper('config');
			}
			$fileList = getSQLFileList($this->params['config']['sql_file_path']);
			$this->data['missingFiles'] = $this->ootp_league_model->validateLoadedSQLFiles($fileList);

			//-------------------------------------------------------------
			// UPDATE VERSION 0.2 beta
			//-------------------------------------------------------------
			// UPDATE CHECKING
			// CHECKS IF UPDATE CONSTANTS ARE DEFINED AND IF UPDATED FILES ARE
			// IN THE INSTALL DIRECTORY FOR INSTALATION
			$web_version = array();
			if ((!defined('ENVIRONMENT') || (defined('ENVIRONMENT') && ENVIRONMENT != 'development')) &&
				  defined('PATH_INSTALL')) {
				if (defined('MAIN_INSTALL_FILE') && file_exists(PATH_INSTALL.MAIN_INSTALL_FILE)) {
					$this->data['installWarning'] = true;
					$this->data['install_message'] = $this->lang->line('install_warning');
				}
				if (defined('DB_UPDATE_FILE') && file_exists(PATH_INSTALL.DB_UPDATE_FILE)) {
					$this->data['dataUpdate'] = true;
				}
				// CHECK FOR DB CONNECTION FILE
				if (defined('DB_CONNECTION_FILE') && !file_exists($this->params['config']['sql_file_path']."/".DB_CONNECTION_FILE)) {
					$this->data['db_file_update'] = true;
				}

				if ((defined('CONFIG_UPDATE_FILE') && file_exists(PATH_INSTALL.CONFIG_UPDATE_FILE)) ||
					(defined('CONSTANTS_UPDATE_FILE') && file_exists(PATH_INSTALL.CONSTANTS_UPDATE_FILE)) ||
					(defined('DATA_CONFIG_UPDATE_FILE') && file_exists(DATA_CONFIG_UPDATE_FILE))) {
					$this->data['configUpdate'] = true;
				}
			}
			//  END 0.2 MODS
			
			//-------------------------------------------------------------
			// UPDATE VERSION 0.3 beta
			//-------------------------------------------------------------
			// VERSION CHECK AND VERIFICATION
			$this->data['version_check'] = getLatestModVersion($this->debug);

			// TEST FOR FANTASY SET UP
			// THIS IS REQUIRED FOR LEAGUE TO SCHEUDLE THEIR FANTASY DRAFT
			if ((empty($this->params['config']['season_start']) || $this->params['config']['season_start'] == EMPTY_DATE_STR) || (empty($this->params['config']['draft_period']) || $this->params['config']['draft_period'] == EMPTY_DATE_STR.":".EMPTY_DATE_STR)) {
				$this->data['settingsError'] = str_replace('[FANTASY_SETTINGS_URL]',$this->params['config']['fantasy_web_root'].'admin/configFantasy',$this->lang->line('admin_error_fantasy_settings'));
			}
			//  END 0.3 MODS
			
			//-------------------------------------------------------------
			// UPDATE VERSION 0.4 beta
			//-------------------------------------------------------------
			// VERSION CHECK AND VERIFICATION
			$this->data['summary_size'] = getSimSummaries(true);
			//  END 0.4 MODS

			//-------------------------------------------------------------
			// UPDATE VERSION 1.0.3 PROD
			//-------------------------------------------------------------
			// GET USERS REQUIRING ACTIVATION COUNT
			if (!isset($this->user_auth_model)) {
				$this->load->model('user_auth_model');
			}
			$this->data['requiring_activation'] = $this->user_auth_model->getAdminActivationCount();
			// NEW FIELDS FOR SITE READINLESS LISTING
			$this->data['members'] = $this->user_auth_model->getUserList();
			// LEAGUE READINESS CHECKS
			$currDate = strtotime($league_info->current_date);
			$startDate = strtotime($league_info->start_date);
			$firstPeriodStart = strtotime($startDate);
			$preseason = ($currDate <= $startDate && $currDate<=$firstPeriodStart);
			$leagueCount = count($this->data['leagues']);
			$activeleagueCount = 0;
			$missingGames = 0;
			$invalidRosters = array();
			$draftsNotSet = array();
			$draftsInProgress = array();
			$draftsNotFinished = array();
			$draftsCompleted = 0;
			$ratingsCount = 0;
			$rotisserieCount = 0;
			if ($leagueCount > 0) {
				if (!isset($this->draft_model)) {
					$this->load->model('draft_model');
				}
				foreach($this->data['leagues'] as $id => $details) {
					if ($details['league_type'] != LEAGUE_SCORING_HEADTOHEAD) $rotisserieCount++;
					$draftStatus = intval($this->draft_model->getDraftStatus($id));
					if ($preseason) {
						// PRE-SEASON
						if ($details['league_type'] == LEAGUE_SCORING_HEADTOHEAD) {
							$gamesNeeded = (($details['max_teams']/2)*$details['games_per_team'])*$details['regular_scoring_periods'];
							$gameCount = $this->league_model->getLeagueGameCount($id);
							if ($gameCount < $gamesNeeded) {
								$missingGames++;
							}
						}
						// TEST DRAFT STATUS
						if ($details['league_status'] == 1) {
							$activeleagueCount++;
							switch ($draftStatus) {
								case 1:
								case 2:
								case 3:
									$draftsInProgress[$id] = $details['league_name'];
									break;
								case 4:
									$draftsNotFinished[$id] = $details['league_name'];
									break;
								case 5:
									$draftsCompleted++;
									break;
								case -1:
								default:
									$draftsNotSet[$id] = $details['league_name'];
									break;
							} // END switch
						}
					} else {
						// REGULAR SEASON

					}
					// IF DRAFT IS COMPLETE, VALIDATE ROSTERS
					if ($draftStatus == 5 && $details['league_status'] == 1 && $this->ootp_league_model->league_id != -1) {
						$this->league_model->errorCode = -1;
						$game_date = $this->league_model->getGameDateForLeague($id, false, $this->params['config']['simType']);
						$this->league_model->validateRosters($this->data['currPeriodConfig'], $id, false, $game_date);
						if ($this->league_model->errorCode == 1) $invalidRosters[$id] = $details['league_name'];
					}
				}
			}
			$this->data['activeleagueCount'] = $activeleagueCount;
			$this->data['leagueCount'] = $leagueCount;
			$this->data['missingGames'] = $missingGames;
			$this->data['invalidRosters'] = $invalidRosters;
			$this->data['draftsNotSet'] = $draftsNotSet;
			$this->data['draftsInProgress'] = $draftsInProgress;
			$this->data['draftsNotFinished'] = $draftsNotFinished;
			$this->data['draftsCompleted'] = $draftsCompleted;
			$this->data['rotisserieCount'] = $rotisserieCount;
			if (!isset($this->player_model)) {
				$this->load->model('player_model');
			}
			$this->data['ratingsCount'] = $this->player_model->getPlayersHaveRatings();
			// Pre and Regualr Season operations markers
			$this->data['ratings_run'] = $this->params['config']['ratings_run'];
			$this->data['player_update_run'] = $this->params['config']['player_update_run'];
			$this->data['update_eligible_run'] = $this->params['config']['update_eligible_run'];
			$this->data['useWaivers'] = $this->params['config']['useWaivers'];
			$this->data['waivers_processed'] = $this->params['config']['waivers_processed'];
			$this->data['completed'] = $completed = getFantasyStatus() >= 3;

			$this->data['rosterAutoLock'] = $this->params['config']['autoLockRosters'];
			$this->data['rostersLocked'] = $this->params['config']['rostersLocked'];
			$this->data['autoLockSyncwithSims'] = $this->params['config']['autoLockSyncwithSims'];
			$this->data['autoLockSyncwithSims'] = $this->params['config']['autoLockSyncwithSims'];
			$this->data['simUploadTimeStart'] = $this->params['config']['simUploadTimeStart'];
			$this->data['simUploadTimeEnd'] = $this->params['config']['simUploadTimeEnd'];
			$this->data['simDays'] = $this->params['config']['simDays'];
			$this->data['autoLockStart'] = $this->params['config']['autoLockStart'];
			$this->data['autoLockEnd'] = $this->params['config']['autoLockEnd'];
			$this->data['autoLockDays'] = $this->params['config']['autoLockDays'];

			//  END 1.0.3 PROD MODS

			$this->params['content'] = $this->load->view($this->views['DASHBOARD'], $this->data, true);
			$this->params['subTitle'] = "Welcome to OOTP Fantasy Leagues";
			$this->params['pageType'] = PAGE_FORM;
			$this->displayView();
		} else {
			redirect('user/login');
		} // END if
	}
	function configInfo() {
		if (!$this->params['loggedIn'] || $this->params['accessLevel'] < ACCESS_ADMINISTRATE) {
			$this->session->set_flashdata('loginRedirect',current_url());
			redirect('user/login');
		} else {
			$fields = array('Fantasy Settings'=>array('seasonStart'=>'Season Start',
			'sim_length' => 'Sim length',
			'default_scoring_periods' => 'Default Scoring Periods',
			/*'useWaivers' => 'Waivers Enabled?',
			'useTrades' => 'Trading Enabled?',
			'tradesExpire' => 'Trade offers Can Expire',
			'defaultExpiration' => 'Default Expiration (in Days)?',
			'approvalType' => 'Trade Approval Type',
			'minProtests' => 'Min # Protest to void trade?',
			'protestPeriodDays' => 'Protest Period (in Days) '
			*/
			),
			'Draft Settings'=>array('draftPeriod'=>'Draft Period',
			'draft_rounds_min' => 'Minimum Draft Rounds',
			'draft_rounds_max' => 'Maximum Draft Rounds'),
			//'Roster Settings'=>array('min_game_current' => 'Eligibility: Games This Season',
			//'min_game_last' => 'Eligibility: Games Last Season')
			);
			$this->data['fields'] = $fields;

			$gameStart = $this->params['config']['season_start'];
			if ($gameStart == EMPTY_DATE_STR) {
				$gameStart = date('Y-m-d',time()+(60*60*24*7));
			}
			$this->data['season_start'] = date('m/d/Y',strtotime($gameStart));
			$draftDates = explode(":",$this->params['config']['draft_period']);
			$draftStart = $draftDates[0];
			$draftEnd = $draftDates[1];
			if ($draftStart == EMPTY_DATE_STR) {
				$draftStart = date('Y-m-d',time()+(60*60*24*3));
			}
			if ($draftEnd == EMPTY_DATE_STR) {
				$draftEnd = date('Y-m-d',time()+(60*60*24*6));
			}
			$this->data['draft_start'] = date('m/d/Y',strtotime($draftStart));
			$this->data['draft_end'] = date('m/d/Y',strtotime($draftEnd));

			$this->data['subTitle'] = "Settings";
			$this->params['content'] = $this->load->view($this->views['CONFIG_INFO'], $this->data, true);
			$this->params['subTitle'] = "Review Settings";
			$this->params['pageType'] = PAGE_FORM;
			$this->displayView();
		}
	}
	/**
	 *	GAME CONFIG.
	 *	Sets the game start date and drafting period dates.
	 */
	function configGame() {
		if (!$this->params['loggedIn'] || $this->params['accessLevel'] < ACCESS_ADMINISTRATE) {
			$this->session->set_flashdata('loginRedirect',current_url());
			redirect('user/login');
		} else {
			$exceptions = array('google_analytics_tracking_id','stats_lab_url');
			$fields = array('site_name' =>  'Site Name',
			'ootp_league_name' => 'OOTP League Name',
			'ootp_league_abbr' => 'OOTP League Abbreviation',
			'ootp_league_id' => 'OOTP League ID',
			'ootp_version' => 'OOTP Game Version',
			'fantasy_web_root' => 'Fantasy League Root URL',
			'ootp_html_report_path' => 'HTML Reports URL',
			'ootp_html_report_links' => 'Show OOTP HTML Reports',
			'sql_file_path' => 'MySQL File Load Path',
			'ootp_html_report_root' => 'HTML Report File Path',
			'max_sql_file_size' => 'Max SQL File Size',
			'limit_load_all_sql' => 'Limit &quot;Load All Files&quot;?',
			'google_analytics_enable' => 'Google Analytics Tracking',
			'google_analytics_tracking_id' => 'Google Analytics Tracking Code',
			'stats_lab_compatible' => 'StatsLab Compatibility Mode',
			'stats_lab_url' => 'StatsLab URL',
			'primary_contact' => 'Primary Contact',
			'emailAdminOnReg' => 'E-Mail Admin on Registration',
			'timezone' => 'Timezone',
			'user_activation_method' => 'User Activiation Method');
			foreach($fields as $field => $label) {
				if (!in_array($field,$exceptions)) {
					$this->form_validation->set_rules($field, $label, 'required');
				}
			}
			$this->form_validation->set_error_delimiters('<span class="error">', '</span>');
			if ($this->form_validation->run() == false) {

				$this->data['adminList'] = $this->user_auth_model->getAdmninUsers();
				//echo("Form validation fail.<br />");
				$this->data['outMess'] = '';
				$this->data['input'] = $this->input;
				$this->data['subTitle'] = "General Details";
				$this->params['content'] = $this->load->view($this->views['CONFIG_GAME'], $this->data, true);
				$this->params['subTitle'] = "Edit Settings";
				$this->params['pageType'] = PAGE_FORM;
				$this->displayView();
			} else {
				$configArr = array();
				foreach($fields as $field => $label) {
					$value= ($this->input->post($field)) ? $this->input->post($field) : '';
					if (strpos($value,"\\\\")) {
						$value = stripslashes($value); // END if
					} // END if
					$configArr = $configArr + array($field=>$value);
				}
				$change = update_config_by_array($configArr);
				if ($change) {
					$this->session->set_flashdata('message', '<span class="success">All settings were successfully updated.</span>');
					redirect('admin/dashboard');
				} else {
					$message = '<span class="error">Settings update failed.</span>';
					$this->data['outMess'] = $message;
					$this->data['subTitle'] = "General Details";
					$this->data['input'] = $this->input;
					$this->params['content'] = $this->load->view($this->views['CONFIG_GAME'], $this->data, true);
					$this->params['subTitle'] = "Edit Settings";
					$this->params['pageType'] = PAGE_FORM;
					$this->displayView();
				}
			}
		}
	}
	/**
	 *	SOCIAL CONFIG.
	 *	Sets the sites social media sharing options.
	 */
	function configSocial() {
		if (!$this->params['loggedIn'] || $this->params['accessLevel'] < ACCESS_ADMINISTRATE) {
			$this->session->set_flashdata('loginRedirect',current_url());
			redirect('user/login');
		} else {
			$fields = array('sharing_enabled' =>  'Social Media Sharing Options',
			'share_addtoany' => 'Add To Any');
			foreach($fields as $field => $label) {
				$this->form_validation->set_rules($field, $label, 'required');
			}
			$this->form_validation->set_error_delimiters('<span class="error">', '</span>');
			if ($this->form_validation->run() == false) {
				$this->data['outMess'] = '';
				$this->data['input'] = $this->input;
				$this->data['subTitle'] = "Social Media Sharing Options";
				$this->params['content'] = $this->load->view($this->views['CONFIG_SOCIAL'], $this->data, true);
				$this->params['subTitle'] = "Edit Settings";
				$this->params['pageType'] = PAGE_FORM;
				$this->displayView();
			} else {
				$configArr = array();
				foreach($fields as $field => $label) {
					$value= ($this->input->post($field)) ? $this->input->post($field) : '';
					$configArr = $configArr + array($field=>$value);
				}
				$change = update_config_by_array($configArr);
				if ($change) {
					$this->session->set_flashdata('message', '<span class="success">All settings were successfully updated.</span>');
					redirect('admin/dashboard');
				} else {
					$message = '<span class="error">Settings update failed.</span>';
					$this->data['outMess'] = $message;
					$this->data['subTitle'] = "Social Media Sharing Options";
					$this->data['input'] = $this->input;
					$this->params['content'] = $this->load->view($this->views['CONFIG_SOCIAL'], $this->data, true);
					$this->params['subTitle'] = "Edit Settings";
					$this->params['pageType'] = PAGE_FORM;
					$this->displayView();
				}
			}
		}
	}
	/**
	 *	SOCIAL CONFIG.
	 *	Sets the sites social media sharing options.
	 */
	function configOOTP() {
		if (!$this->params['loggedIn'] || $this->params['accessLevel'] < ACCESS_ADMINISTRATE) {
			$this->session->set_flashdata('loginRedirect',current_url());
			redirect('user/login');
		} else {

			$fields = array('start_date' =>  'League Start Date',
			'current_date' => 'Current League Date',
			'current_period' => 'Current Fantasy Scoring Period');
			foreach($fields as $field => $label) {
				$this->form_validation->set_rules($field, $label, 'required');
			} // END foreach
			$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

			$start_date = $this->ootp_league_model->start_date;
			$this->data['start_date'] = date('m/d/Y',strtotime($start_date));
			$current_date = $this->ootp_league_model->current_date;
			$this->data['current_date'] = date('m/d/Y',strtotime($current_date));

			if ($this->form_validation->run() == false) {

				$this->data['outMess'] = '';
				$this->data['input'] = $this->input;
				$this->data['subTitle'] = "OOTP Config Options";
				$this->params['content'] = $this->load->view($this->views['CONFIG_OOTP'], $this->data, true);
				$this->params['subTitle'] = "Edit Settings";
				$this->params['pageType'] = PAGE_FORM;
				$this->displayView();
			} else {
				$message = "";
				$change = update_config('current_period',$this->input->post('current_period'));
				if ($change) {
					$this->ootp_league_model->applyData($this->input);
					$change = $this->ootp_league_model->writeConfigDates(date('Y-m-d',strtotime($this->input->post('start_date'))),date('Y-m-d',strtotime($this->input->post('current_date'))),
																		 $this->params['accessLevel'] == ACCESS_ADMINISTRATE);
				} else {
					$message .= '<span class="error">Settings update failed.</span><br />Config option <em>current_period</em> could not be updated';
				}
				if ($change) {
					$this->session->set_flashdata('message', '<span class="success">All settings were successfully updated.</span>');
					redirect('admin/dashboard');
				} else {
					if (empty($message)) {
						$message = '<span class="error">Settings update failed.</span>';
					} else {
						$message .= "<br />".$this->ootp_league_model->statusMess;
					}
					$this->data['outMess'] = $message;
					$this->data['input'] = $this->input;
					$this->data['subTitle'] = "Fantasy Details";
					$this->params['content'] = $this->load->view($this->views['CONFIG_OOTP'], $this->data, true);
					$this->params['subTitle'] = "Start Season";
					$this->params['pageType'] = PAGE_FORM;
					$this->displayView();
				}
			} // END if
		} // END if
	} // END function
	/**
	 *	ABOUT CONFIG.
	 *	Allows admin to edit the site's custom about page content.
	 */
	function configAbout() {
		if (!$this->params['loggedIn'] || $this->params['accessLevel'] < ACCESS_ADMINISTRATE) {
			$this->session->set_flashdata('loginRedirect',current_url());
			redirect('user/login');
		} else {

			$fields = array('aboutHTML' =>  'About HTML Content');
			foreach($fields as $field => $label) {
				$this->form_validation->set_rules($field, $label, 'required');
			} // END foreach
			$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

			if (!function_exists('read_file')) {
				$this->load->helper('file');
			}
			$message = "";
			$error = "";
			$aboutHTML = "";
			try {
				$aboutHTML = read_file(DIR_WRITE_PATH.URL_PATH_SEPERATOR."application".URL_PATH_SEPERATOR."views".URL_PATH_SEPERATOR.ABOUT_HTML_FILE);
			}
			catch (Exception $error) { }
			if (!$error && $this->form_validation->run() == false) {

				$this->data['outMess'] = '';
				$this->data['input'] = $this->input;
				$this->data['aboutHTML'] = $aboutHTML;
				$this->data['subTitle'] = "Edit About Site Content";
				$this->params['content'] = $this->load->view($this->views['CONFIG_ABOUT'], $this->data, true);
				$this->params['subTitle'] = "OOTP Config Options";
				$this->params['pageType'] = PAGE_FORM;
				$this->displayView();
			} else {
				$change = write_file(DIR_WRITE_PATH.URL_PATH_SEPERATOR."application".URL_PATH_SEPERATOR."views".URL_PATH_SEPERATOR.ABOUT_HTML_FILE,$this->input->post('aboutHTML'));
				if ($change) {
					$this->session->set_flashdata('message', '<span class="success">About page content successfully updated.</span>');
					redirect('admin/dashboard');
				} else {
					if (empty($message)) {
						$message = '<span class="error">About content update failed.</span>';
					} else {
						$message .= "<br />".$this->ootp_league_model->statusMess;
					}
					$this->data['outMess'] = $message;
					$this->data['input'] = $this->input;
					$this->data['aboutHTML'] = $aboutHTML;
					$this->data['subTitle'] = "OOTP Config Options";
					$this->params['content'] = $this->load->view($this->views['CONFIG_ABOUT'], $this->data, true);
					$this->params['subTitle'] = "Edit About Site Content";
					$this->params['pageType'] = PAGE_FORM;
					$this->displayView();
				}
			} // END if
		} // END if
	} // END function
	/**
	 *	FANTASY CONFIG.
	 *	Sets the game start date and drafting period dates.
	 */
	function configFantasy() {
		if (!$this->params['loggedIn'] || $this->params['accessLevel'] < ACCESS_ADMINISTRATE) {
			$this->session->set_flashdata('loginRedirect',current_url());
			redirect('user/login');
		} else {
			$this->form_validation->set_rules('season_start', 'Season Start Date', 'required');
			$this->form_validation->set_rules('draft_start', 'Draft Period Start Date', 'required');
			$this->form_validation->set_rules('draft_end', 'Draft Period End Date', 'required');
			$this->form_validation->set_rules('simUploadTimeStart', 'Sim Upload Start Time', 'required');
			$this->form_validation->set_rules('simUploadTimeEnd', 'Sim Upload End Time', 'required');
			$this->form_validation->set_rules('draft_end', 'Draft Period End Date', 'required');
			$this->form_validation->set_rules('simDays', 'Sims occur on Days', 'required');
			$fields = array('sim_length' => 'Sim length',
			'default_scoring_periods' => 'Default Scoring Periods',
			//'useWaivers' => 'Waivers Enabled?',
			//'useTrades' => 'Trading Enabled?',
			//'tradesExpire' => 'Trade offers Can Expire',
			//'approvalType' => 'Trade Approval Type',
			//'min_game_current' => 'Eligibility: Games This Season',
			//'min_game_last' => 'Eligibility: Games Last Season',
			'draft_rounds_min' => 'Minimum Draft Rounds',
			'draft_rounds_max' => 'Maximum Draft Rounds',
			'restrict_admin_leagues' => 'Restrict # of Admin Leagues',
			'users_create_leagues' => 'Users can create leagues',
			'max_user_leagues' => 'Max # of user leagues',
			'autoLockRosters' => 'Auto Lock Rosters',
			'autoLockSyncwithSims' => 'Lock during Sim uploads',
			'simType' => 'Sim Type',
			);
			foreach($fields as $field => $label) {
				$this->form_validation->set_rules($field, $label, 'required|trim|number');
			}
			$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

			if ($this->form_validation->run() == false) {
				/*------------------------------------------------------------------
				/
				/	DRAW FORM
				/
				/-----------------------------------------------------------------*/
				$gameStart = $this->params['config']['season_start'];
				if ($gameStart == EMPTY_DATE_STR) {
					$gameStart = date('Y-m-d',time()+(60*60*24*7));
				}
				$this->data['season_start'] = date('m/d/Y',strtotime($gameStart));
				$draftDates = explode(":",$this->params['config']['draft_period']);
				$draftStart = $draftDates[0];
				$draftEnd = $draftDates[1];
				if ($draftStart == EMPTY_DATE_STR) {
					$draftStart = date('Y-m-d',time()+(60*60*24*3));
				}
				if ($draftEnd == EMPTY_DATE_STR) {
					$draftEnd = date('Y-m-d',time()+(60*60*24*6));
				}
				$this->data['draft_start'] = date('m/d/Y',strtotime($draftStart));
				$this->data['draft_end'] = date('m/d/Y',strtotime($draftEnd));

				// EDIT 1.2 PROD, ADD SIM DETAIL AND AUTO LOCK SUPPORT
				$simDayStr = $this->params['config']['simDays'];
				$simDays = array();
				if (strpos($simDayStr, ',') != 0) $simDays = explode(",", $simDayStr);
				else array_push($simDays, $simDayStr);
				$lockDayStr = $this->params['config']['autoLockDays'];
				$lockDays = array();
				if (strpos($lockDayStr, ',') != 0) $lockDays = explode(",", $lockDayStr);
				else array_push($lockDays, $lockDayStr);
				$this->data['simDays'] = $simDays;
				$this->data['lockDays'] = $lockDays;

				$this->data['simUploadTimeStart'] = $this->params['config']['simUploadTimeStart'];
				$this->data['simUploadTimeEnd'] = $this->params['config']['simUploadTimeEnd'];
				$this->data['autoLockStart'] = $this->params['config']['autoLockStart'];
				$this->data['autoLockEnd'] = $this->params['config']['autoLockEnd'];

				$this->data['outMess'] = '';
				$this->data['input'] = $this->input;
				$this->data['subTitle'] = "Fantasy Details";
				$this->params['content'] = $this->load->view($this->views['CONFIG_FANTASY'], $this->data, true);
				$this->params['subTitle'] = "Fantasy Settings";
				$this->params['pageType'] = PAGE_FORM;
				$this->displayView();

			} else {
				/*------------------------------------------------------------------
				/
				/	FORM SUBMISSION
				/
				/-----------------------------------------------------------------*/
				$change = false;
				$change = update_config('season_start',date('Y-m-d',strtotime($this->input->post('season_start'))));
				$change = update_config('draft_period',date('Y-m-d',strtotime($this->input->post('draft_start'))).":".date('Y-m-d',strtotime($this->input->post('draft_end'))));
				$change = update_config('simUploadTimeStart',$this->input->post('simUploadTimeStart'));
				$change = update_config('simUploadTimeEnd',$this->input->post('simUploadTimeEnd'));
				$simDayStr = '';
				$simDays = $this->input->post('simDays');
				if (sizeof($simDays) > 1) {
					foreach($simDays as $day) {
						if (!empty($simDayStr)) $simDayStr .= ',';
						$simDayStr .= $day;
					}
				}
				$change = update_config('simDays',$simDayStr);
				$change = update_config('autoLockStart',$this->input->post('autoLockStart'));
				$change = update_config('autoLockEnd',$this->input->post('autoLockEnd'));
				$lockDayStr = '';
				$lockDays = $this->input->post('autoLockDays');
				if (sizeof($lockDays) > 1) {
					foreach($lockDays as $day) {
						if (!empty($lockDayStr)) $lockDayStr .= ',';
						$lockDayStr .= $day;
					}
				}
				$change = update_config('autoLockDays',$lockDayStr);
				
				$configArr = array();
				foreach($fields as $field => $label) {
					$value= ($this->input->post($field)) ? intval($this->input->post($field)) : 0;
					$configArr = $configArr + array($field=>$value);
				}
				$change = update_config_by_array($configArr);
				if ($change) {
					$this->session->set_flashdata('message', '<span class="success">All settings were successfully updated.</span>');
					redirect('admin/dashboard');
				} else {
					$message = '<span class="error">Settings update failed.</span>';
					$this->data['outMess'] = $message;
					$this->data['outMess'] = '';
					$this->data['input'] = $this->input;
					$this->data['subTitle'] = "Fantasy Details";
					$this->params['content'] = $this->load->view($this->views['CONFIG_FANTASY'], $this->data, true);
					$this->params['subTitle'] = "Fantasy Settings";
					$this->params['pageType'] = PAGE_FORM;
					$this->displayView();
				}
			}
		}
	}
	/**
	 *	ROSTER CONFIG.
	 *	Sets the default set of Roster Rules used as a template for new leagues.
	 */
	function configRosters() {
		if (!$this->params['loggedIn'] || $this->params['accessLevel'] < ACCESS_ADMINISTRATE) {
			$this->session->set_flashdata('loginRedirect',current_url());
			redirect('user/login');
		} else {
			$this->form_validation->set_rules('pos0', 'First Active Position', 'required');
			$this->form_validation->set_rules('min0', 'First Active Minimum', 'required');
			$this->form_validation->set_rules('max0', 'First Active Maximum', 'required');
			if (!isset($this->league_model)) {
			$this->load->model('league_model');
			} // END if
			$this->data['rosters'] = $this->league_model->getRosterRules(-1);

			if ($this->form_validation->run() == false) {
				$this->data['outMess'] = '';
				$this->data['input'] = $this->input;
				$this->data['subTitle'] = "Configure Default Roster Rules";
				$this->params['content'] = $this->load->view($this->views['CONFIG_ROSTERS'], $this->data, true);
				$this->params['subTitle'] = "Fantasy Settings";
				$this->params['pageType'] = PAGE_FORM;
				$this->displayView();
			} else {
				if (!isset($this->league_model)) {
					$this->load->model('league_model');
				} // END if
				$change = $this->league_model->setRosterRules($this->input, -1);
				if ($change) {
					$this->session->set_flashdata('message', '<span class="success">All settings were successfully updated.</span>');
					redirect('admin/dashboard');
				} else {
					$message = '<span class="error">Settings update failed.</span>';
					$this->data['outMess'] = $message;
					$this->data['outMess'] = '';
					$this->data['input'] = $this->input;
					$this->data['subTitle'] = "Configure Default Roster Rules";
					$this->params['content'] = $this->load->view($this->views['CONFIG_ROSTERS'], $this->data, true);
					$this->params['subTitle'] = "Fantasy Settings";
					$this->params['pageType'] = PAGE_FORM;
					$this->displayView();
				} // END if
			} // END if
		} // END if
	} // END function

	/**
	 *	SCORING RULES CONFIG.
	 *	Sets the games scoring rules.
	 */
	function configScoringRules() {
		if (!$this->params['loggedIn'] || $this->params['accessLevel'] < ACCESS_ADMINISTRATE) {
			$this->session->set_flashdata('loginRedirect',current_url());
			redirect('user/login');
		} else {
			$this->getURIData();

			$this->form_validation->set_rules('batting_type_0', 'First Batting Category', 'required');
			$this->form_validation->set_rules('pitching_type_0', 'First Pitching Category', 'required');
			$this->form_validation->set_rules('scoring_type', 'Scoring Type', 'required');

			if (!isset($this->league_model)) {
				$this->load->model('league_model');
			}
			if (isset($this->uriVars['scoring_type']) && !empty($this->uriVars['scoring_type']) && $this->uriVars['scoring_type'] != -1) {
				$scoring_type = $this->uriVars['scoring_type'];
			} else {
				$scoring_type = LEAGUE_SCORING_ROTO;
			}
			if ($scoring_type == LEAGUE_SCORING_HEADTOHEAD) {
				$this->form_validation->set_rules('batting_value_0', 'First Batting Value', 'required');
				$this->form_validation->set_rules('pitching_value_0', 'First Pitching Value', 'required');
			}
			$scoringRules = $this->league_model->getScoringRules(-1,$scoring_type);
			if (isset($scoringRules['batting'])) {
				$this->data['scoring_batting']=	$scoringRules['batting'];
			}
			if (isset($scoringRules['pitching'])) {
				$this->data['scoring_pitching'] = $scoringRules['pitching'];
			}
			$scoring_types = loadSimpleDataList('leagueType','','ASC','Scoring Type');

			if ($this->form_validation->run() == false) {
				$this->data['outMess'] = '';
				$this->data['input'] = $this->input;
				$this->data['scoring_types'] = $scoring_types;
				$this->data['scoring_type'] = $scoring_type;
				$this->data['subTitle'] = "Configure Default Scoring Rules";
				$this->params['content'] = $this->load->view($this->views['CONFIG_SCORING_RULES'], $this->data, true);
				$this->params['subTitle'] = "Fantasy Settings";
				$this->params['pageType'] = PAGE_FORM;
				$this->displayView();
			} else {
				if (!isset($this->league_model)) {
					$this->load->model('league_model');
				} // END if
				$change = $this->league_model->setScoringRules($this->input, -1);
				if ($change) {
					$this->session->set_flashdata('message', '<span class="success">All settings were successfully updated.</span>');
					redirect('admin/configScoringRules');
				} else {
					$message = '<span class="error">Settings update failed.</span>';
					$this->data['outMess'] = $message;
					$this->data['scoring_types'] = $scoring_types;
					$this->data['input'] = $this->input;
					$this->data['scoring_type'] = $scoring_type;
					$this->data['subTitle'] = "Configure Default Scoring Rules";
					$this->params['content'] = $this->load->view($this->views['CONFIG_SCORING_RULES'], $this->data, true);
					$this->params['subTitle'] = "Fantasy Settings";
					$this->params['pageType'] = PAGE_FORM;
					$this->displayView();
				}
			}
		}
	}


	/**
	 *	SCORING PERIODS CONFIG.
	 *	List the games scoring periods.
	 */
	function configScoringPeriods() {
		if (!$this->params['loggedIn'] || $this->params['accessLevel'] < ACCESS_ADMINISTRATE) {
			$this->session->set_flashdata('loginRedirect',current_url());
			redirect('user/login');
		} else {
			$this->data['scoring_edit'] = $this->ootp_league_model->current_date <= $this->ootp_league_model->start_date;
			$this->data['periods'] = getScoringPeriods();
			$this->data['outMess'] = '';
			$this->data['input'] = $this->input;
			$this->data['subTitle'] = "Scoring Periods";
			$this->params['content'] = $this->load->view($this->views['CONFIG_SCORING_PERIODS'], $this->data, true);
			$this->params['subTitle'] =  "Review Settings";
			$this->params['pageType'] = PAGE_FORM;
			$this->displayView();
		}

	}
	/**
	 *	EDIT SCORING PERIODS CONFIG.
	 *	Edits the games scoring periods.
	 */
	function configScoringPeriodsEdit() {
		if (!$this->params['loggedIn'] || $this->params['accessLevel'] < ACCESS_ADMINISTRATE) {
			$this->session->set_flashdata('loginRedirect',current_url());
			redirect('user/login');
		} else {
			$this->getURIData();
			if (!isset($this->uriVars['period_id'])) {
				$message = '<span class="error">A required scoring period id paremeter was not received. Please go back to the previous page and try submitting again.</span>';
				$this->data['outMess'] = $message;
				$this->data['input'] = $this->input;
				$this->data['subTitle'] = "Edit Scoring Periods";
				$this->params['content'] = $this->load->view($this->views['CONFIG_SCORING_RULES'], $this->data, true);
				$this->params['subTitle'] = "Edit Settings";
				$this->params['pageType'] = PAGE_FORM;
				$this->displayView();
			} else {

				$this->data['period_id'] = $this->uriVars['period_id'];
				$scoring_period = getScoringPeriod($this->uriVars['period_id']);
				$this->data['date_start'] = date('m/d/Y',strtotime($scoring_period['date_start']));
				$this->data['date_end'] = date('m/d/Y',strtotime($scoring_period['date_end']));

				$this->form_validation->set_rules('period_id', 'Scoring Period ID', 'required');
				$this->form_validation->set_rules('date_start', 'Period Start Date', 'required');
				$this->form_validation->set_rules('date_end', 'Period End Date', 'required');
				$this->form_validation->set_rules('submitted', 'Form Submitted', 'required');

				if ($this->form_validation->run() == false) {
					$this->data['outMess'] = '';
					$this->data['input'] = $this->input;
					$this->data['subTitle'] = "Edit Scoring Periods";
					$this->params['content'] = $this->load->view($this->views['CONFIG_SCORING_PERIODS_EDIT'], $this->data, true);
					$this->params['subTitle'] =  "Edit Settings";
					$this->params['pageType'] = PAGE_FORM;
					$this->displayView();
				} else {
					$this->db->set('date_start',date('Y-m-d',strtotime($this->input->post('date_start'))));
					$this->db->set('date_end',date('Y-m-d',strtotime($this->input->post('date_end'))));
					$this->db->where("id",$this->data['period_id']);
					$this->db->update('fantasy_scoring_periods');
					$change = ($this->db->affected_rows() > 0);
					if ($change) {
						$this->session->set_flashdata('message', '<span class="success">All settings were successfully updated.</span>');
						redirect('admin/configScoringPeriods');
					} else {
						$message = '<span class="error">Settings update failed.</span>';
						$this->data['outMess'] = $message;
						$this->data['input'] = $this->input;
						$this->data['subTitle'] = "Edit Scoring Periods";
						$this->params['content'] = $this->load->view($this->views['CONFIG_SCORING_PERIODS_EDIT'], $this->data, true);
						$this->params['subTitle'] =  "Edit Settings";
						$this->params['pageType'] = PAGE_FORM;
						$this->displayView();
					}
				}
			}
		}
	}
	/**
	 * UPLOAD FILE CONFIG
	 *
	 */
	public function uploadFiles() {
		if (!$this->params['loggedIn'] || $this->params['accessLevel'] < ACCESS_ADMINISTRATE) {
			$this->session->set_flashdata('loginRedirect',current_url());
			redirect('user/login');
		} else {
			$this->data['outMess'] = '';
			$this->data['input'] = $this->input;
			$this->data['subTitle'] = "Upload SQL Data Files";
			$this->params['content'] = $this->load->view($this->views['FILE_UPLOADS'], $this->data, true);
			$this->params['subTitle'] =  "Admin Tools";
			$this->params['pageType'] = PAGE_FORM;
			$this->displayView();
		}
	}
	/**
	 *	ACTIVATE USER
	 *	Converts a user waiting for admin activation to active
	 *
	 *	@param	$this->uriVars['user_id']	User ID
	 *
	 *	Redirects to userActivations()  on success
	 *
	 *	@since	0.5
	 */
	public function activateUser() {
		if (!$this->params['loggedIn'] || $this->params['accessLevel'] < ACCESS_ADMINISTRATE) {
			$this->session->set_flashdata('loginRedirect',current_url());
			redirect('user/login');
		} else {
			$this->getURIData();
			$supressEmail = (isset($this->uriVars['noEmail']) && !empty($this->uriVars['noEmail']) && $this->uriVars['noEmail'] == 1) ? true : false;
			if (isset($this->uriVars['user_id']) && !empty($this->uriVars['user_id']) && $this->uriVars['user_id'] != -1) {
				if ($this->auth->adminActivate($this->uriVars['user_id'],$this->params['currUser'],$supressEmail)) {
					$this->session->set_flashdata('message', '<span class="success">The user has been activated.</span>');
				} else {
					$this->session->set_flashdata('error', '<span class="error">The user was not activated. Error: '.$this->user_auth_model->statusMess.'</span>');
				} //NED if
			} else {
				$this->session->set_flashdata('message', '<span class="error">No User ID was recieved.</span>');
			}
			$url = 'admin/userActivations';
			if (isset($this->uriVars['returnPage']) && !empty($this->uriVars['returnPage'])) {
				$url = str_replace("_","/",$this->uriVars['returnPage']);
			}
			redirect($url);
		}
	}
	/**
	 *	DEACTIVATE USER
	 *	Converts an active user to deactive status.
	 *
	 *	@param	$this->uriVars['user_id']	User ID
	 *
	 *	Redirects to userActivations()  on success
	 *
	 *	@since	0.5
	 */
	public function deactivateUser() {
		if (!$this->params['loggedIn'] || $this->params['accessLevel'] < ACCESS_ADMINISTRATE) {
			$this->session->set_flashdata('loginRedirect',current_url());
			redirect('user/login');
		} else {
			$this->getURIData();
			if (isset($this->uriVars['user_id']) && !empty($this->uriVars['user_id']) && $this->uriVars['user_id'] != -1) {
				if ($this->auth->adminDeactivate($this->uriVars['user_id'],$this->params['currUser'])) {
					$this->session->set_flashdata('message', '<span class="success">The user has been deactivated.</span>');
				} else {
					$this->session->set_flashdata('error', '<span class="error">The user was not deactivated. Error: '.$this->user_auth_model->statusMess.'</span>');
				} //NED if
			} else {
				$this->session->set_flashdata('message', '<span class="error">No User ID was recieved.</span>');
			}
			$url = 'admin/userActivations';
			if (isset($this->uriVars['returnPage']) && !empty($this->uriVars['returnPage'])) {
				$url = str_replace("_","/",$this->uriVars['returnPage']);
			}
			redirect($url);
		}
	}
	/**
	 *	SET USER LOCK STATUS
	 *	CUpdates a users locked accoutn status.
	 *
	 *	@param	$this->uriVars['user_id']	User ID
	 *	@param	$this->uriVars['status']	Updated lock status
	 *
	 *	Redirects to dashboard()  on success
	 *
	 *	@since	0.6
	 */
	public function setUserLockStatus() {
		if (!$this->params['loggedIn'] || $this->params['accessLevel'] < ACCESS_ADMINISTRATE) {
			$this->session->set_flashdata('loginRedirect',current_url());
			redirect('user/login');
		} else {
			$this->getURIData();
			if (isset($this->uriVars['user_id']) && !empty($this->uriVars['user_id']) && $this->uriVars['user_id'] != -1 &&
			isset($this->uriVars['status']) && !empty($this->uriVars['status'])) {
				if ($this->auth->setLockStatus(getUsername($this->uriVars['user_id']),$this->uriVars['status'])) {
					$this->session->set_flashdata('message', '<span class="success">The users locked status has been successfully updated.</span>');
				} else {
					$this->session->set_flashdata('error', '<span class="error">The users lock status was not updated. Error: '.$this->user_auth_model->statusMess.'</span>');
				} //NED if
			} else {
				$this->session->set_flashdata('message', '<span class="error">No User ID was recieved.</span>');
			}
			$url = 'admin/dashboard';
			if (isset($this->uriVars['returnPage']) && !empty($this->uriVars['returnPage'])) {
				$url = str_replace("_","/",$this->uriVars['returnPage']);
			}
			redirect($url);
		}
	}
	/**
	 *	USER ACTIVATIONS
	 *	Lists all users awaiting activation.
	 *
	 *	@since	0.5
	 */
	public function userActivations() {
		if (!$this->params['loggedIn'] || $this->params['accessLevel'] < ACCESS_ADMINISTRATE) {
			$this->session->set_flashdata('loginRedirect',current_url());
			redirect('user/login');
		} else {
			$this->data['activations'] = $this->user_auth_model->getAdminActivations();
			$this->params['subTitle'] = $this->data['subTitle'] = "Activate Users";
			$this->params['content'] = $this->load->view($this->views['ACTIVATE_USERS'], $this->data, true);
			$this->params['pageType'] = PAGE_FORM;
			$this->displayView();
		}
	}
	/**
	 *	AVATAR
	 *	Update the team's avatar.
	 */
	public function uploadFile() {
		if (!$this->params['loggedIn'] || $this->params['accessLevel'] < ACCESS_ADMINISTRATE) {
			$this->session->set_flashdata('loginRedirect',current_url());
			redirect('user/login');
		} else {
			if (!($this->input->post('submitted')) || ($this->input->post('submitted') && !isset($_FILES['dataFile']['name']))) {
				if ($this->input->post('submitted') && !isset($_FILES['dataFile']['name'])) {
					$fv = & _get_validation_object();
					$fv->setError('dataFile','The date file field is required.');
				}
				$this->data['outMess']= "Errors were found.";
				$this->data['subTitle'] = "Upload Files";
				$this->params['subTitle'] =  "Admin Tools";
				$this->params['pageType'] = PAGE_FORM;
				$this->params['content'] = $this->load->view($this->views['FILE_UPLOADS'], $this->data, true);
				$this->displayView();
			} else {
				if (!(strpos($_FILES['dataFile']['name'],'.zip'))) {
					$fv = & _get_validation_object();
					$fv->setError('dataFile','The file selected is not a valid zip file.');
					$this->data['subTitle'] = "Upload Files";
					$this->params['subTitle'] =  "Admin Tools";
					$this->params['content'] = $this->load->view($this->views['FILE_UPLOADS'], $this->data, true);
					$this->params['pageType'] = PAGE_FORM;
					$this->displayView();
				} else {
					if ($_FILES['dataFile']['error'] === UPLOAD_ERR_OK) {
						$this->load->helper(array('form', 'url'));
						$config = array();
						$config['upload_path'] = PATH_ATTACHMENTS_WRITE;
						$config['allowed_types'] = 'zip';
						$config['overwrite']	= true;
						$this->load->library('upload',$config);
						$change = $this->upload->do_upload('dataFile');
						if ($change) {
							$this->session->set_flashdata('message', '<p class="success">The data file has been successfully uploaded.</p>');
							redirect('team/info/'.$this->dataModel->id);
						} else {
							$message = '<p class="error">Data File Upload Failed.';
							$message .= '</p >';
							$this->session->set_flashdata('message', $message);
							redirect('team/avatar');
						}
					} else {
						throw new UploadException($_FILES['dataFile']['error']);
					}
				}
			}
		}
	}

	public function configUploadFile() {

		if (!$this->params['loggedIn'] || $this->params['accessLevel'] < ACCESS_ADMINISTRATE) {
			$this->session->set_flashdata('loginRedirect',current_url());
			redirect('user/login');
		} else {

			$this->form_validation->set_rules('deflate', 'Deflate After Upload', 'required');

			if ($this->form_validation->run() == false) {
				$this->data['outMess'] = "Required fields were missing.";
				$this->data['subTitle'] = "Upload Files";
				$this->params['content'] = $this->load->view($this->views['FILE_UPLOADS'], $this->data, true);
				$this->params['subTitle'] =  "Admin Tools";
				$this->params['pageType'] = PAGE_FORM;
				$this->displayView();
			} else {
				$error = false;
				$errorStr = "";
				$hasFile = false;
				$this->data['outMess'] = '';
				$uploadSuccess = false;
				if (isset($_FILES['dataFiles']['name']) && !empty($_FILES['dataFiles']['name'])) {

					$maxinbytes = return_bytes(ini_get('post_max_size'));
					if ($_FILES['dataFiles']['size'] > $maxinbytes) {
						$error = true;
						$this->data['outMess'] .= 'The zip uploaded exceeds the allowable file size of '.$maxinbytes.'.<br />';
					} else {
						$target_file_name = DIR_WRITE_PATH.PATH_ATTACHMENTS_WRITE.$_FILES['dataFiles']['name'];

						if (move_uploaded_file($_FILES['dataFiles']['tmp_name'], $target_file_name)) {
							chmod($target_file_name,0755);
							$this->data['outMess'] .= "File upload completed successfully.<br />";

							if ($this->input->post('deflate')) {
								$def = $this->input->post('deflate');
								if ($def == 1) {
									$this->load->library('unzip');
									try {
										$this->unzip->extract($target_file_name,$this->params['config']['sql_file_path']);
									} catch (Exception $e) {
										$error = true;
										$this->data['outMess'] .= $e."<br />";
									}
								} // END if
							} // END if
						} else {
							$error = true;
							$this->data['outMess'] .= "The file upload process did not complete successfully. The file ".basename( $_FILES['dataFiles']['name'])." could not be saved on the server.<br />";
						} // END if
					}
				} else {
					$error = true;
					$this->data['outMess'] .= "No files were selected for uploading.<br />";
				}
				if ($error) {
					$this->data['outMess'] = '<span class="error">'.$this->data['outMess'].'</span>';
					$this->data['subTitle'] = "Upload Files";
					$this->params['content'] = $this->load->view($this->views['FILE_UPLOADS'], $this->data, true);
					$this->params['subTitle'] =  "Admin Tools";
					$this->params['pageType'] = PAGE_FORM;
					$this->displayView();
				} else {
					$this->session->set_flashdata('message', '<span class="success">All uploads completed successfully.</span>');
					redirect('admin/dashboard');
				} // END if
			} // END if
		} // END if
	}
	public function getInfo() {
			$this->data['subTitle'] =  "PHP Info";
			$this->data['theContent'] =  phpinfo();
			$this->params['content'] = $this->load->view($this->views['MESSAGE'], $this->data, true);
			$this->params['subTitle'] =  "Admin Info";
			$this->displayView();
	}
	public function uploadProgress() {
		$this->getURIData();
		if (isset($this->uriVars['key_id']) && !empty($this->uriVars['key_id']) && $this->uriVars['key_id'] != -1) {
			$status = apc_fetch('upload_'.$_GET['progress_key']);
			if ($status['total'] ==0 ) {
				$total = "1";
			} else {
				$total = $status['current']/$status['total']*100;
			}
		} else {
			$total = -1;
		}
		$this->output->set_header('Content-type: text/plain');
		$this->output->set_output($total);
	}

	/**
	 *	SIM SUMMARIES.
	 *	List the games scoring periods.
	 *
	 *	@since 	0.4
	 */
	public function simSummaries() {
		if (!$this->params['loggedIn'] || $this->params['accessLevel'] < ACCESS_ADMINISTRATE) {
			$this->session->set_flashdata('loginRedirect',current_url());
			redirect('user/login');
		} else {
			$this->data['summaries'] = getSimSummaries();;
			$this->data['outMess'] = '';
			$this->data['input'] = $this->input;
			$this->data['subTitle'] = "Sim Summaries";
			$this->enqueStyle('list_picker.css');
			$this->params['content'] = $this->load->view($this->views['SIM_SUMMARY'], $this->data, true);
			$this->params['subTitle'] =  "Admin Logs";
			$this->params['pageType'] = PAGE_FORM;
			$this->displayView();
		}
	}
	public function loadSummary() {

		$this->getURIData();

		$status = '';
		$result = '';
		$code = -1;

		if (isset($this->uriVars['summary_id']) || $this->uriVars['summary_id'] != -1) {
			$summary = loadSimSummary($this->uriVars['summary_id']);

			if (sizeof($summary) > 0) {
				$result .= '{"id":"'.$summary->id.'","sim_date":"'.date('Y-m-d h-i-s-A',strtotime($summary->sim_date)).'","scoring_period_id":"'.$summary->scoring_period_id.'","sim_result":"'.$summary->sim_result;
				$result .= '","process_time":"'.$summary->process_time.'","sim_summary":"'.urlencode($summary->sim_summary).'","comments":"'.urlencode($summary->comments).'"}';
				$status .= "OK";
				$code = 200;
			}
		}
		if (strlen($result) == 0) {
			$status .= "notice:No summary data was found";
			$code = 201;
		}
		$result = '{ "result": { "items": ['.$result.']},"code":"'.$code.'","status": "'.$status.'"}';

		$this->output->set_header('Content-type: application/json');
		$this->output->set_output($result);
	}
	function listSQLFiles() {
		if (!$this->params['loggedIn'] || $this->params['accessLevel'] < ACCESS_ADMINISTRATE) {
			$this->session->set_flashdata('loginRedirect',current_url());
			redirect('user/login');
		} else {
			$this->getURIData();
			if (!function_exists('getSQLFileList')) {
				$this->load->helper('config');
			}
			$fileList = getSQLFileList($this->params['config']['sql_file_path']);

			//-------------------------------------------------------------
			// UPDATE VERSION 0.3
			//-------------------------------------------------------------
			// CHECK FOR DB CONNECTION FILE
			if (defined('DB_CONNECTION_FILE') && !file_exists($this->params['config']['sql_file_path']."/".DB_CONNECTION_FILE)) {
				if (!function_exists('read_file')) { $this->load->helper('file'); }
				$this->data['db_file_update'] = updateDBFile((($this->params['config']['stats_lab_compatible'] == 1)?true:false),$this->params['config']['sql_file_path']);
			}
			// END 0.3 MODS

			$this->data['fileList'] = $fileList;
			$this->data['missingFiles'] = $this->ootp_league_model->validateLoadedSQLFiles($fileList);
			$this->data['requiredTables'] = $this->ootp_league_model->requiredTables;
			$this->data['subTitle'] = "Load Individual SQL Tables";
			$this->params['content'] = $this->load->view($this->views['LIST_FILES'], $this->data, true);
			$this->params['subTitle'] = "Database Tools";

			$this->params['pageType'] = PAGE_FORM;
			$this->displayView();
		}
	}

	function closeSeason() {
		$this->params['subTitle'] = "Close out your season";
		$this->params['content'] = $this->load->view($this->views['PENDING'], $this->data, true);
	    $this->displayView();
	}
	function archiveSeason() {
		$this->params['subTitle'] = "Archive results";
		$this->params['content'] = $this->load->view($this->views['PENDING'], $this->data, true);
	    $this->displayView();
	}
	/*-----------------------------------------------------
	/	ADMIN AJAX FUNCTIONS
	/----------------------------------------------------*/
	/**
	 *	DATA UPDATE.
	 *	LOADS AND EXECUTES AN SQL UPDATE
	 */
	public function configUpdate() {
		$result = '';
		$status = 'OK';

		$site_directory = str_replace("admin/configUpdate","",$_SERVER['REQUEST_URI']);
		//echo("Site directory = ".$site_directory."<br />");

		if (!function_exists('read_file')) {
			$this->load->helper('file');
		}

		// SELECT THE FILES TO UPDATE
		if (file_exists(PATH_INSTALL.CONFIG_UPDATE_FILE)) {
			$fcf = read_file(PATH_INSTALL.CONFIG_UPDATE_FILE);
			$fcf = str_replace("[SITE_PATH]",SITE_URL,$fcf);
			write_file('application/config/config.php', $fcf);
			unset($fcf);
			chmod('application/config/config.php', 0666);
			$config_write = true;
		}
		if (file_exists(PATH_INSTALL.CONSTANTS_UPDATE_FILE)) {
			// OPEN THE FILE AND REPLACE THE DYNAMIC CONTENT WITH REAL VALUES
			$fcs = read_file(PATH_INSTALL.CONSTANTS_UPDATE_FILE);
			$fcs = str_replace("[WEB_SITE_URL]",SITE_URL,$fcs);
			$fcs = str_replace("[SITE_DIRECTORY]",DIR_APP_ROOT,$fcs);
			$fcs = str_replace("[HTML_ROOT]",DIR_WRITE_PATH,$fcs);
			write_file('./application/config/constants.php', $fcs);
			unset($fcs);
			chmod('./application/config/constants.php', 0666);
		}
		if (file_exists(PATH_INSTALL.CONSTANTS_UPDATE_FILE)) {
			unlink(PATH_INSTALL.CONSTANTS_UPDATE_FILE);
		}
		if (file_exists(PATH_INSTALL.CONFIG_UPDATE_FILE)) {
			unlink(PATH_INSTALL.CONFIG_UPDATE_FILE);
		}
		if (file_exists(PATH_INSTALL)) {
			rmdir(PATH_INSTALL);
		}
		$code = 200;
		$result = "success";
		$status = "OK";
		$result = '{"result":"'.$result.'","code":"'.$code.'","status":"'.$status.'"}';
		$this->output->set_header('Content-type: application/json');
		$this->output->set_output($result);
	}

	// The following function are called from the dashboard and
	// perform specific tasks. Each returns a status as an AJAX
	// response object
	/**
	 *	DATA UPDATE.
	 *	LOADS AND EXECUTES AN SQL UPDATE
	 */
	public function dataUpdate() {
		$result = '';
		$status = '';
		if (!function_exists('loadSQLFiles')) {
			$this->load->helper('config');
		}
		$mess = loadDataUpdate($this->params['config']['sql_file_path'],PATH_INSTALL.DB_UPDATE_FILE);
		$status = $mess;
		if ($mess != "OK") {
			$code = 300;
			$result = "error";
			$status .= " The update could not be completed.";
		} else {
			if (file_exists(PATH_INSTALL.DB_UPDATE_FILE)) {
				unlink(PATH_INSTALL.DB_UPDATE_FILE);
			}
			$code = 200;
			$result = "success";
		}
		$result = '{"result":"'.$result.'","code":"'.$code.'","status":"'.$status.'"}';
		$this->output->set_header('Content-type: application/json');
		$this->output->set_output($result);
	}

	/**
	 *	AVAILABLE PLAYERS.
	 */
	public function availablePlayers() {
		$result = '';
		$status = '';
		// CHECK FOR DUPLICATE
		if (!function_exists('get_available_players')) {
			$this->load->helper('roster');
		}
		$mess = get_available_players($this->params['config']['ootp_league_id']);
		if (!$mess == "OK") {
			$status = "error:".$mess;
		} else {
			$status = "OK";
		}
		$code = 200;

		$result = '{"result":"'.$mess.'","code":"'.$code.'","status":"'.$status.'"}';
		$this->output->set_header('Content-type: application/json');
		$this->output->set_output($result);
	}
	/**
	 *	UPDATE PLAYERS.
	 */
	public function updatePlayers() {
		$result = '';
		$status = '';
		// CHECK FOR DUPLICATE
		if (!function_exists('get_available_players')) {
			$this->load->helper('roster');
		}
		$mess = update_player_availability($this->params['config']['ootp_league_id']);
		if (!$mess == "OK") {
			$status = "error:".$mess;
		} else {
			$status = "OK";
			update_config('player_update_run',1);
		}
		$code = 200;
		//$this->data['message'] = $status;
		//$this->dashboard();
		$result = '{"result":"'.$mess.'","code":"'.$code.'","status":"'.$status.'"}';
		$this->output->set_header('Content-type: application/json');
		$this->output->set_output($result);
	}
	/**
	 *	POSITION Eligibility.
	 */
	function eligibility() {
		$result = '';
		$status = '';
		$error = false;
		// CHECK FOR DUPLICATE
		if (!function_exists('position_eligibility')) {
			$this->load->helper('roster');
		}
		$this->load->model('league_model');
		$leagues = $this->league_model->getLeagues();
		if (sizeof($leagues) > 0) {
			position_eligibility($leagues, $this->params['config']['ootp_league_id']);
		} else {
			$error = true;
			$status = "No Leagues Found!";
		}
		if (!$error) {
			$status = "OK";
			update_config('update_eligible_run',1);
		}
		$code = 200;

		$result = '{"result":"'.$mess.'","code":"'.$code.'","status":"'.$status.'"}';
		$this->output->set_header('Content-type: application/json');
		$this->output->set_output($result);
	}
	/**
	 *	PLAYER RATINGS.
	 */
	function playerRatings() {
		$result = '';
		$status = '';
		// CHECK FOR DUPLICATE
		$this->load->model('player_model');
		
		$statsRange = 1; // 1 = CURRENT SEASON, -1 == LAST SEASON
		$statsParam = getCurrentScoringPeriod($this->ootp_league_model->current_date);
		
		$currDate = strtotime ($this->ootp_league_model->current_date." ".EMPTY_TIME_STR);
		$startDate = strtotime ($this->ootp_league_model->start_date." ".EMPTY_TIME_STR);
		if ($currDate <= $startDate) {
			$statsRange = -1;
			$statsParam = (date("Y", $currDate) - 1);
		}
		$resp = $this->player_model->updatePlayerRatings(15,$statsRange, $statsParam ,$this->params['config']['ootp_league_id']);

		if (is_array($resp)) {
			$rslt = $resp[0];
			$mess = $resp[1];

		} else {
			$rslt = $resp;
			$mess = "";
		}
		if ($rslt == -1) {
			$status = "error:An error occured during processing.";
			$outMess = "An error occured during processing.";
		} else {
			//print($mess);
			// TODO - LOG SUMMARY SOMEWHERE
			$status = "OK";
			$outMess = "Ratings updated successfully.";
			update_config('ratings_run',1);
		}
		$code = 200;

		$result = '{"result":"'.$outMess.'","code":"'.$code.'","status":"'.$status.'"}';
		$this->output->set_header('Content-type: application/json');
		$this->output->set_output($result);
	}

	public function copyRosters() {
		$this->getURIData();
		$result = '';
		$this->load->model('league_model');
		if (isset($this->uriVars['osp']))
			$old_scoring_period = $this->uriVars['osp'];
		if (isset($this->uriVars['nsp']))
			$new_scoring_period = $this->uriVars['nsp'];
		if (isset($this->uriVars['league_id']))
			$league_id = $this->uriVars['league_id'];

		$status = $this->league_model->copyRosters($old_scoring_period, $new_scoring_period, $league_id);
		if ($status === false) {
			$code = 300;
			$result = "error";
			$status .= " The roster update could not be completed.";
		} else {
			$code = 200;
			$result = "success";
		}
		$result = '{"result":"'.$result.'","code":"'.$code.'","status":"'.$status.'"}';
		$this->output->set_header('Content-type: application/json');
		$this->output->set_output($result);
	}
	/**
	 *	CREATE SCORING SCHEDULE.
	 */
	function scoringSchedule() {
		// DIVIDE THE LEAGUE GAME SCHEDULE STARTING AT THE LEAGUE DATE BY THE SIM/PERIODS
		$result = '';
		$status = '';
		// CHECK FOR DUPLICATE
		$mess = createScoringSchedule($this->params['config']['ootp_league_id'],$this->params['config']['sim_length']);
		if ($mess != "OK") {
			$status = "error:".$mess;
		} else {
			$status = "OK";
		}
		$code = 200;

		$result = '{"result":"'.$mess.'","code":"'.$code.'","status":"'.$status.'"}';
		$this->output->set_header('Content-type: application/json');
		$this->output->set_output($result);
	}
	/**
	 *	MANUALY PROCCESS WAIVERS
	 */
	function manualWaivers() {
		// DIVIDE THE LEAGUE GAME SCHEDULE STARTING AT THE LEAGUE DATE BY THE SIM/PERIODS
		$result = '';
		$status = '';
		$mess = "OK";
		$summary = "";
		// CHECK FOR DUPLICATE
		$score_period = getCurrentScoringPeriod($this->ootp_league_model->current_date);
		$summary .= "Processing waivers for period ".$score_period ['id']."<br />";
		/*------------------------------
		/	LOAD LEAGUES
		/---------------------------*/
		$this->data['leagues'] = $this->league_model->getLeagues(-1);
		$summary .= "Leagues Loaded = ".sizeof($this->data['leagues'])."<br />";
		/*------------------------------
		/	PROCESS WAIVERS PER LEAGUE
		/-----------------------------*/
		$processCount = 0;
		foreach($this->data['leagues'] as $id => $details) {
			$summary .= "Processing for League = ".$details['league_name']."<br />";
			$waiverSuccess = $this->league_model->processWaivers($score_period['id']+1, $id, 'previous');
			if (!$waiverSuccess && $this->league_model->errorCode != -1) {
				$mess = $this->league_model->statusMess;
				$summary .= "Result = <b>failure</b><br />";
				break;
			}
			$summary .= "Result = <b>success</b><br />";
			$summary .= $this->league_model->statusMess."<br />";
			$processCount++;
		}

		if ($mess != "OK") {
			$status = "error:".$mess;
		} else {
			updateScoringPeriodWaivers($score_period['id']);
			$status = "OK";
		}
		$code = 200;
		$summary .= "Waivers processing completed<br />";
		$summary .= $processCount." league processing completed successully<br />";
		if (!function_exists('write_file')) {
			$this->load->helper('file');
		} // END if
		write_file(PATH_MEDIA_WRITE.'/waiver_summary'.$score_period['id'].'.html',$summary);
		$result = '{"result":"'.$mess.'","code":"'.$code.'","status":"'.$status.'"}';
		$this->output->set_header('Content-type: application/json');
		$this->output->set_output($result);
	}

	/**
	 *	RESET SEASON DATA.
	 *  CAUTION: THIS WILL WIPE OUT ALL SEASON WIDE DATA AND REST THE MOD BACK TO
	 *	PRE_SEASON STATUS.
	 */
	function resetSeason() {
		$result = '';
		$status = '';
		$mess = reset_transactions();
		$mess = reset_sim_summary();
		$mess = reset_player_data();
		$mess = reset_team_data();
		$mess = reset_league_data();
		$mess = reset_draft();
		$mess = reset_scoring();
		$mess = reset_scoring_periods();
		update_config('current_period',1);
		update_config('last_sql_load_time',date('Y-m-d',(strtotime(date('Y-m-d'))-(60*60*24))));
		update_config('last_process_time','1970-01-01 00:00:00');
		update_config('player_update_run','-1');
		update_config('update_eligible_run','-1');
		update_config('ratings_run','-1');
		reset_ootp_league($this->params['config']['ootp_league_id']);
		if (!$mess) {
			$status = "error:".$mess;
		} else {
			$status = "OK";
		}
		$code = 200;
		$result = '{"result":"Complete","code":"'.$code.'","status":"'.$status.'"}';
		$this->output->set_header('Content-type: application/json');
		$this->output->set_output($result);
	}
	/**
	 *	RESET RECENT SIM DATA.
	 *  CAUTION: THIS WILL ERASE ALL THE DATA FROM THE PREVIOUS SIM AND RESET BACK ONE
	 *  SCORING PERIOD.
	 */
	function resetSim() {
		$result = '';
		$status = '';
		if (!function_exists('reset_sim')) {
			$this->load->helper('admin');
		}
		$mess = reset_sim($this->params['config']['current_period']);
		update_config('current_period',$this->params['config']['current_period'] - 1);
		update_config('last_process_time','1970-1-1 00:00:00');
		if (!$mess) {
			$status = "error:".$mess;
		} else {
			$status = "OK";
		}
		$code = 200;
		$result = '{"result":"Complete","code":"'.$code.'","status":"'.$status.'"}';
		$this->output->set_header('Content-type: application/json');
		$this->output->set_output($result);
	}

	/**
	 *	GENERATE LEAGUE SCHEDULES.
	 *	Creates game schedule for head to head scoring leagues.
	 */
	function generateSchedules() {
		// DIVIDE THE LEAGUE GAME SCHEDULE STARTING AT THE LEAGUE DATE BY THE SIM/PERIODS
		$result = '';
		$status = '';
		// CHECK FOR DUPLICATE
		$mess = // UPDATE PLAYER SCORING FOR THIS PERIOD
		$this->data['leagues'] = $this->league_model->getLeagues(-1);
		$error = false;
		foreach($this->data['leagues'] as $id => $details) {
			if ($details['league_type'] == LEAGUE_SCORING_HEADTOHEAD) {
				$this->league_model->load($id);
				$mess = $this->league_model->createLeagueSchedule();
			} else {
				$mess = true;
			}
		}
		if (!$mess) {
			$status = "error:".$mess;
		} else {
			$status = "OK";
		}
		$code = 200;
		$result = '{"result":"Complete","code":"'.$code.'","status":"'.$status.'"}';
		$this->output->set_header('Content-type: application/json');
		$this->output->set_output($result);
	}
    /**
     * Uninstall Database Tables
     * @since   1.0.1
     */
    function uninstall() {
        $mess = loadDataUpdate($this->params['config']['sql_file_path'],DIR_WRITE_PATH.'application/config/db_uninstall.sql');
        if (strpos($mess,"error")) {
            $status = $mess;
        } else {
            $status = "OK";
        }
        $code = 200;
        $result = '{"result":"'.$mess.'","code":"'.$code.'","status":"'.$status.'"}';
        $this->output->set_header('Content-type: application/json');
        $this->output->set_output($result);
    }
	/**
	 *	LOAD SQL DATA TABLE(S)
	 */
	function loadSQLFiles() {
		$this->getURIData();
		if (!function_exists('loadSQLFiles')) {
			$this->load->helper('config');
		}

		if (isset($this->uriVars['loadList']) && sizeof($this->uriVars['loadList']) > 0) {
			$fileList = $this->uriVars['loadList'];
		} else if (isset($this->uriVars['filename']) && !empty($this->uriVars['filename'])) {
			$fileList = array($this->uriVars['filename']);
		} else if (isset($this->params['config']['limit_load_all_sql']) && $this->params['config']['limit_load_all_sql'] == 1) {
			$fileList = $this->ootp_league_model->getRequiredSQLFiles();
		} else {
			$fileList = getSQLFileList($this->params['config']['sql_file_path'],strtotime($this->params['config']['last_sql_load_time']));
		}
		$filesLoaded = array();
		$mess = loadSQLFiles($this->params['config']['sql_file_path'],strtotime($this->params['config']['last_sql_load_time']), $fileList);
		if (!is_array($mess) || (is_array($mess) && sizeof($mess) == 0)) {
			if (is_array($mess)) {
				$status = "An error occured processing the SQL files.";
			} else {
				$status = "error: ".$mess;
			}
		} else {
			$status = "OK";
			if (is_array($mess)) {
				$filesLoaded = $mess;
			}
			update_config('last_sql_load_time',date('Y-m-d h:m:s'));
		}
		if (isset($this->uriVars['returnPage'])) {
			if ($this->uriVars['returnPage'] == "file_list") {
				$this->data['filesLoaded'] = $filesLoaded;
				$this->listSQLFiles();
			}
		} else {
			$code = 200;
			$result = '{"result":"'.$mess.'","code":"'.$code.'","status":"'.$status.'"}';
			$this->output->set_header('Content-type: application/json');
			$this->output->set_output($result);
		}
	}
	/**
	 *	SPLIT SQL DATA FILE.
	 */
	function splitSQLFile() {
		$this->getURIData();
		if (!function_exists('splitFiles')) {
			$this->load->helper('config');
		}
		$mess = splitFiles($this->params['config']['sql_file_path'],$this->uriVars['filename'], $this->params['config']['max_sql_file_size']);
		if ($mess != "OK") {
			$status = "error:".$mess;
		} else {
			$status = "OK";
		}
		$code = 200;
		$result = '{"result":"'.$mess.'","code":"'.$code.'","status":"'.$status.'"}';
		$this->output->set_header('Content-type: application/json');
		$this->output->set_output($result);
	}
	/**
	 *	PROCESS SIM RESULTS.
	 */
	function processSim() {

		$this->benchmark->mark('sim_start');
		$comments = "";
		$error = false;
		$mess = '';
		$warn = "";
		$summary = str_replace('[TIME_START]',date('m/d/Y h:i:s A'),$this->lang->line('sim_process_start'));
		//-------------------------------------
		// ADVANCE SCORING PERIOD
		// CHECK FOR DUPLICATE
		//-------------------------------------
		$score_period = getCurrentScoringPeriod($this->ootp_league_model->current_date);
		if ($this->ootp_league_model->current_date == $score_period['date_start']) {
			$score_period = getCurrentScoringPeriod(date('Y-m-d',strtotime($this->ootp_league_model->current_date) - (60*60*24)));
		}
		$summary .= str_replace('[PERIOD_ID]',$score_period['id'],$this->lang->line('sim_period_id'));

		/*------------------------------
		/	UPDATE PLAYER SCORING
		/---------------------------*/
		$this->load->model('player_model');
		$summary .= $this->player_model->updatePlayerScoring($score_period);

		/*------------------------------
		/	LOAD LEAGUES
		/---------------------------*/
		$this->data['leagues'] = $this->league_model->getLeagues(-1);

		/*------------------------------
		/	UPDATE LEAGUE SCORING
		/-----------------------------*/
		$typeRot = 0;
		$waiversRun = false;
		$waiversCount = 0;
		foreach($this->data['leagues'] as $id => $details) {
			// EDIT 1.0.3 PROD, check if were in the legal scoring window. H2H Leagues that end before the final periods should
			// not be run.
			$totalPeriods = 0;
			if ($details['league_type'] == LEAGUE_SCORING_HEADTOHEAD && $details['regular_scoring_periods'] > 0) 
				$totalPeriods = intval($details['regular_scoring_periods']) + intval($details['playoff_rounds']);
			if ($details['league_type'] != LEAGUE_SCORING_HEADTOHEAD || ($totalPeriods > 0 && intval($score_period['id']) <= $totalPeriods)) {
				$summary .= $this->league_model->updateLeagueScoring($score_period, $id, $this->params['config']['ootp_league_id']);
				if ($this->league_model->errorCode != -1) {
					$mess =  $this->league_model->statusMess;
					$summary .= $this->lang->line('sim_include_errors');
					$summary .= "<ul>".$mess."</ul>";
				}
			}
			if ($details['league_type'] != LEAGUE_SCORING_HEADTOHEAD) {
				$typeRot++;
			}
			$waiversSettings =  $this->league_model->getLeagueWaiversSettings($id);
			if ((isset($waiversSettings['useWaivers']) && $waiversSettings['useWaivers'] == 1)) {
				$waiversCount++;
				$waiversRun = true;
			}
		}
		$simResult = 1;
		// UPDATE THE MAIN CONFIG
		if ($error) {
			$status = "error:".$mess;
			$simResult = 2;
			$code = 301;
			$mess = $this->lang->line('sim_ajax_error');
		} else {
			$code = 200;
			if (!empty($warn)) {
				$status = $warn;
			} else {
				$status = "OK";
			}
			$mess = $this->lang->line('sim_ajax_success');
			update_config('last_process_time',date('Y-m-d h:m:s'));
			update_config('current_period',($score_period['id']+1));
			if ($waiversCount > 0 && $waiversRun) {
				update_config('waivers_processed','1');
			}
			update_config('player_update_run','-1');
			update_config('update_eligible_run','-1');
			if ($typeRot > 0)
				update_config('ratings_run','-1');
		}
		/*------------------------------
		/	CLOSE THE BENCHMARK
		/-----------------------------*/
		$this->benchmark->mark('sim_end');
		$sim_time = $this->benchmark->elapsed_time('sim_start', 'sim_end');
		$summary .= str_replace('[SIM_TIME]',$sim_time,$this->lang->line('sim_process_finished'));
		$summary = str_replace('[TIME_END]',date('m/d/Y h:i:s A'),$summary);
		/*------------------------------
		/	LOG THIS SIMS SUMMARY
		/-----------------------------*/
		save_sim_summary($score_period['id'],$simResult,$sim_time,$summary,$comments);
		$result = '{"result":"'.$mess.'","code":"'.$code.'","status":"'.$status.'"}';
		$this->output->set_header('Content-type: application/json');
		$this->output->set_output($result);

	}
	/**
	 *	GET URI DATA.
	 *	Parses out an id or other parameters from the uri string
	 *
	 */
	protected function getURIData() {
		parent::getURIData();
		if ($this->input->post('filename')) {
			$this->uriVars['filename'] = $this->input->post('filename');
		} // END if
		if ($this->input->post('delete')) {
			$this->uriVars['delete'] = $this->input->post('delete');
		} // END if
		if ($this->input->post('loadList')) {
			$this->uriVars['loadList'] = $this->input->post('loadList');
		} // END if
		if ($this->input->post('returnPage')) {
			$this->uriVars['returnPage'] = $this->input->post('returnPage');
		} // END if
		if ($this->input->post('period_id')) {
			$this->uriVars['period_id'] = $this->input->post('period_id');
		} // END if
		if ($this->input->post('summary_id')) {
			$this->uriVars['summary_id'] = $this->input->post('summary_id');
		} // END if
		if ($this->input->post('key_id')) {
			$this->uriVars['key_id'] = $this->input->post('key_id');
		} // END if
		if ($this->input->post('scoring_type')) {
			$this->uriVars['scoring_type'] = $this->input->post('scoring_type');
		} // END if
		if ($this->input->post('user_id')) {
			$this->uriVars['user_id'] = $this->input->post('user_id');
		} // END if
		if ($this->input->post('status')) {
			$this->uriVars['status'] = $this->input->post('status');
		} // END if
	}
	/*------------------------------------
	/	DEPRECATED
	/-----------------------------------*/
	/**
	 *	SECURITY CONFIG.
	 *	Updates the sites security and spam protection settings.
	 *
	 *	@since 0.6
	 *	@deprecated 	1.0.3 PROD	- Recaptcha v1 no longer supported by Google
	 *
	 */
	/*function configSecurity() {
		if (!$this->params['loggedIn'] || $this->params['accessLevel'] < ACCESS_ADMINISTRATE) {
			$this->session->set_flashdata('loginRedirect',current_url());
			redirect('user/login');
		} else {
			$this->form_validation->set_rules('security_enabled', 'Security Enabled', 'required');
			$this->form_validation->set_rules('security_type', 'Anti Spam Countermeasure Type', 'required');
			$fields = array('recaptcha_key_public' => 'reCAPTCHA public key',
				'recaptcha_key_private' => 'reCAPTCHA private key',
				'recaptcha_theme' => 'reCAPTCHA Theme',
				'recaptcha_lang' => 'reCAPTCHA Language',
				'recaptcha_compliant' => 'reCAPTCHA Standards Compliance Mode',
				'security_class' => 'Security Class');
			foreach($fields as $field => $label) {
				$this->form_validation->set_rules($field, $label, 'trim');
			}
			$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

			if ($this->form_validation->run() == false) {
					$this->data['outMess'] = '';
				$this->data['input'] = $this->input;
				$this->data['subTitle'] = "Site Security Settings";
				$this->params['content'] = $this->load->view($this->views['CONFIG_SECURITY'], $this->data, true);
				$this->params['subTitle'] = "OOTP Config Options";
				$this->params['pageType'] = PAGE_FORM;
				$this->displayView();
			} else {
				$change = false;
				$configArr = array();
				$change = update_config('security_enabled',$this->input->post('security_enabled'));
				$change = update_config('security_type',$this->input->post('security_type'));
				foreach($fields as $field => $label) {
					$value= ($this->input->post($field)) ? $this->input->post($field) : '';
					$configArr = $configArr + array($field=>$value);
					//echo($field." = ".$value."<br />");
				}
				$change = update_config_by_array($configArr);
				if ($change) {
					$this->session->set_flashdata('message', '<span class="success">All settings were successfully updated.</span>');
					redirect('admin/dashboard');
				} else {
					$message = '<span class="error">Settings update failed.</span>';
					$this->data['outMess'] = $message;
					$this->data['outMess'] = '';
					$this->data['input'] = $this->input;
					$this->data['subTitle'] = "Site Security Settings";
					$this->params['content'] = $this->load->view($this->views['CONFIG_SECURITY'], $this->data, true);
					$this->params['subTitle'] = "OOTP Config Options";
					$this->params['pageType'] = PAGE_FORM;
					$this->displayView();
				}
			}
		}
	}*/
}
/* End of file admin.php */
/* Location: ./application/controllers/admin.php */