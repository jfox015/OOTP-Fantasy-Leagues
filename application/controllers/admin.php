<?php
/**
 *	Admin Access.
 *	The primary controller for the Admin Section.
 *	@author			Jeff Fox
 *	@dateCreated	11/13/09
 *	@lastModified	08/16/10
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
		$this->views['SIM_SUMMARY'] = 'admin/sim_summary';
		$this->views['FILE_UPLOADS'] = 'admin/config_uploads';
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
			
			$this->data['leagues'] = $this->league_model->getLeagues($this->params['config']['ootp_league_id'], -1);
			
			$this->data['in_season'] = $this->ootp_league_model->in_season();

			$this->data['currPeriod'] = getCurrentScoringPeriod($this->ootp_league_model->current_date);
			
			$this->data['periodCount'] = getScoringPeriodCount();
			if (!function_exists('getSQLFileList')) {
				$this->load->helper('config');
			}
			$fileList = getSQLFileList($this->params['config']['sql_file_path']);
			$this->data['missingFiles'] = $this->ootp_league_model->validateLoadedSQLFiles($fileList);
			
			//-------------------------------------------------------------
			// UPDATE VERSION 1.0.2
			//-------------------------------------------------------------
			// UPDATE CHECKING
			// CHECKS IF UPDATE CONSTANTS ARE DEFINED AND IF UPDATED FILES ARE 
			// IN THE INSTALL DIRECTORY FOR INSTALATION
			$web_version = array();
			if ((!defined('ENV') || (defined('ENV') && ENV != 'dev')) && defined('PATH_INSTALL')) {
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
			//  END 1.0.2 MODS
			//-------------------------------------------------------------
			// UPDATE VERSION 1.0.3
			//-------------------------------------------------------------
			// VERSION CHECK AND VERIFICATION		
			$this->data['version_check'] = getLatestModVersion($this->debug);
			
			// TEST FOR FANTASY SET UP
			// THIS IS REQUIRED FOR LEAGUE TO SCHEUDLE THEIR FANTASY DRAFT
			if ((empty($this->params['config']['season_start']) || $this->params['config']['season_start'] == EMPTY_DATE_STR) || (empty($this->params['config']['draft_period']) || $this->params['config']['draft_period'] == EMPTY_DATE_STR.":".EMPTY_DATE_STR)) {
				$this->data['settingsError'] = str_replace('[FANTASY_SETTINGS_URL]',$this->params['config']['fantasy_web_root'].'admin/configFantasy',$this->lang->line('admin_error_fantasy_settings'));
			}
			//  END 1.0.3 MODS
			//-------------------------------------------------------------
			// UPDATE VERSION 1.0.4
			//-------------------------------------------------------------
			// VERSION CHECK AND VERIFICATION		
			$this->data['summary_size'] = getSimSummaries(true);
			//  END 1.0.4 MODS

			
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
			$fields = array('General Settings'=>array('site_name' =>'Site Name',
			'ootp_league_name' => 'OOTP League Name',
			'ootp_league_abbr' => 'OOTP League Abbreviation',
			'ootp_league_id' => 'OOTP League ID',
			'google_analytics_enable' => 'Google Analytics Tracking',
			'google_analytics_tracking_id' => 'Google Analytics Tracking Code',
			'stats_lab_compatible' => 'Stats Lab Compatibility Mode',
			'restrict_admin_leagues' => 'Restrict # of Admin Leagues',
			'users_create_leagues' => 'Users can create leagues',
			'max_user_leagues' => 'Max # of user leagues',
			'primary_contact' => 'Primary Contact'),
			'File Settings'=>array('fantasy_web_root' => 'Fantasy League Root URL',
			'ootp_html_report_path' => 'HTML Reports URL',
			'sql_file_path' => 'MySQL File Load Path',
			'ootp_html_report_root' => 'HTML Report File Path',
			'max_sql_file_size' => 'Max SQL File Size',
			'limit_load_all_sql' => 'Limit &quot;Load All Files&quot; to only required?'),
			'Fantasy Settings'=>array('seasonStart'=>'Season Start',
			'sim_length' => 'Sim length',
			'default_scoring_periods' => 'Default Scoring Periods',
			'useWaivers' => 'Waivers Enabled?',
			'useTrades' => 'Trading Enabled?',
			'tradesExpire' => 'Trade offers Can Expire',
			'defaultExpiration' => 'Default Expiration (in Days)?',
			'approvalType' => 'Trade Approval Type',
			'minProtests' => 'Min # Protest to void trade?',
			'protestPeriodDays' => 'Protest Period (in Days) '),
			'Draft Settings'=>array('draftPeriod'=>'Draft Period',
			'draft_rounds_min' => 'Minimum Draft Rounds',
			'draft_rounds_max' => 'Maximum Draft Rounds'),
			'Roster Settings'=>array('min_game_current' => 'Eligibility: Games This Season',
			'min_game_last' => 'Eligibility: Games Last Season',
			'active_max' => 'Active Roster Max',
			'reserve_max' => 'Reserve Roster Max',
			'injured_max' => 'Injured Roster Max'));
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
			$exceptions = array('google_analytics_tracking_id');
			$fields = array('site_name' =>  'Site Name',
			'ootp_league_name' => 'OOTP League Name',
			'ootp_league_abbr' => 'OOTP League Abbreviation',
			'ootp_league_id' => 'OOTP League ID',
			'fantasy_web_root' => 'Fantasy League Root URL',
			'ootp_html_report_path' => 'HTML Reports URL',
			'sql_file_path' => 'MySQL File Load Path',
			'ootp_html_report_root' => 'HTML Report File Path',
			'max_sql_file_size' => 'Max SQL File Size',
			'limit_load_all_sql' => 'Limit &quot;Load All Files&quot;?',
			'google_analytics_enable' => 'Google Analytics Tracking',
			'google_analytics_tracking_id' => 'Google Analytics Tracking Code',
			'stats_lab_compatible' => 'Stats Lab Compatibility Mode',
			'primary_contact' => 'Primary Contact');
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
				//echo("Form validation pass.<br />");
				$configArr = array();
				foreach($fields as $field => $label) {
					$value= ($this->input->post($field)) ? $this->input->post($field) : '';
					if (strpos($value,"\\\\")) {
						$value = stripslashes($value); // END if
					} // END if
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
			'share_facebook' => 'Facebook',
			'share_twitter' => 'Twitter',
			'share_digg' => 'Digg',
			'share_stumble' => 'Stumbleupon',
			'share_addtoany' => 'Add To Any');
			foreach($fields as $field => $label) {
				$this->form_validation->set_rules($field, $label, 'required');
			}
			$this->form_validation->set_error_delimiters('<span class="error">', '</span>');
			if ($this->form_validation->run() == false) {
				//echo("Form validation fail.<br />");
				$this->data['outMess'] = '';
				$this->data['input'] = $this->input;
				$this->data['subTitle'] = "Social Media Sharing Options";
				$this->params['content'] = $this->load->view($this->views['CONFIG_SOCIAL'], $this->data, true);
				$this->params['subTitle'] = "Edit Settings";
				$this->params['pageType'] = PAGE_FORM;
				$this->displayView();
			} else {
				//echo("Form validation pass.<br />");
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
			$fields = array('sim_length' => 'Sim length',
			'default_scoring_periods' => 'Default Scoring Periods',
			'useWaivers' => 'Waivers Enabled?',
			'useTrades' => 'Trading Enabled?',
			'tradesExpire' => 'Trade offers Can Expire',
			'approvalType' => 'Trade Approval Type',
			'draft_rounds_min' => 'Minimum Draft Rounds',
			'draft_rounds_max' => 'Maximum Draft Rounds',
			'min_game_current' => 'Eligibility: Games This Season',
			'min_game_last' => 'Eligibility: Games Last Season',
			'active_max' => 'Active Roster Max',
			'reserve_max' => 'Reserve Roster Max',
			'injured_max' => 'Injured Roster Max',
			'restrict_admin_leagues' => 'Restrict # of Admin Leagues',
			'users_create_leagues' => 'Users can create leagues',
			'max_user_leagues' => 'Max # of user leagues');
			foreach($fields as $field => $label) {
				$this->form_validation->set_rules($field, $label, 'required|trim|number');
			}
			$this->form_validation->set_error_delimiters('<span class="error">', '</span>');
			
			if ($this->form_validation->run() == false) {
				
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
				$this->data['outMess'] = '';
				$this->data['input'] = $this->input;
				$this->data['subTitle'] = "Fantasy Details";
				$this->params['content'] = $this->load->view($this->views['CONFIG_FANTASY'], $this->data, true);
				$this->params['subTitle'] = "Fantasy Settings";
				$this->params['pageType'] = PAGE_FORM;
				$this->displayView();
			} else {
				$change = false;
				$change = update_config('season_start',date('Y-m-d',strtotime($this->input->post('season_start'))));
				$change = update_config('draft_period',date('Y-m-d',strtotime($this->input->post('draft_start'))).":".date('Y-m-d',strtotime($this->input->post('draft_end'))));
				$configArr = array();
				foreach($fields as $field => $label) {
					$value= ($this->input->post($field)) ? intval($this->input->post($field)) : 0;
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
	 *	Sets the game start date and drafting period dates.
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
			$this->data['rosters'] = $this->league_model->getRosterRules();
			
			if ($this->form_validation->run() == false) {
				$this->data['outMess'] = '';
				$this->data['input'] = $this->input;
				$this->data['subTitle'] = "Configure Roster Rules";
				$this->params['content'] = $this->load->view($this->views['CONFIG_ROSTERS'], $this->data, true);
				$this->params['subTitle'] = "Fantasy Settings";
				$this->params['pageType'] = PAGE_FORM;
				$this->displayView();
			} else {
				if (!isset($this->league_model)) {
					$this->load->model('league_model');
				} // END if
				$change = $this->league_model->setRosterRules($this->input);
				if ($change) {
					$this->session->set_flashdata('message', '<span class="success">All settings were successfully updated.</span>');
					redirect('admin/dashboard');
				} else {
					$message = '<span class="error">Settings update failed.</span>';
					$this->data['outMess'] = $message;
					$this->data['outMess'] = '';
					$this->data['input'] = $this->input;
					$this->data['subTitle'] = "Configure Roster Rules";
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
			$scoringRules = $this->league_model->getScoringRules(0,$scoring_type);
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
				$this->data['subTitle'] = "Configure Scoring Rules";
				$this->params['content'] = $this->load->view($this->views['CONFIG_SCORING_RULES'], $this->data, true);
				$this->params['subTitle'] = "Fantasy Settings";
				$this->params['pageType'] = PAGE_FORM;
				$this->displayView();
			} else {
				if (!isset($this->league_model)) {
					$this->load->model('league_model');
				} // END if
				$change = $this->league_model->setScoringRules($this->input);
				if ($change) {
					$this->session->set_flashdata('message', '<span class="success">All settings were successfully updated.</span>');
					redirect('admin/dashboard');
				} else {
					$message = '<span class="error">Settings update failed.</span>';
					$this->data['outMess'] = $message;
					$this->data['scoring_types'] = $scoring_types;
					$this->data['input'] = $this->input;
					$this->data['scoring_type'] = $scoring_type;
					$this->data['subTitle'] = "Configure Scoring Rules";
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
				$uploadSuccess = false;
				if (isset($_FILES['dataFiles']['name']) && !empty($_FILES['dataFiles']['name'])) {
					
					$this->load->helper(array('form', 'url'));
					$config = array();
					$config['upload_path'] = PATH_ATTACHMENTS_WRITE;
					$config['allowed_types'] = 'zip';
					$config['overwrite']	= true;
					$this->load->library('upload');
					$this->upload->initialize($config);
					$uploadSuccess = $this->upload->do_upload('dataFiles');
					
					if (!$uploadSuccess) {
						$error = true;
						$this->data['outMess']= '<span class="error">'.$this->upload->display_errors().'</span>';
					} else {
						if ($this->input->post('deflate')) {
							$def = $this->input->post('deflate');
							if ($def == 1) {
								$this->load->library('unzip');
								$type = $this->input->post('fileType');
								$path = $this->params['config']['sql_file_path'];
								/*switch($type) {
									case 1:
										$path = $this->params['config']['sql_file_path'];
										break;
									case 2:
										$path = $this->params['config']['ootp_html_report_root'];
										break;	
								}*/
								$this->unzip->extract(PATH_ATTACHMENTS_WRITE.$_FILES['dataFiles']['name'],$path);
							} // END if
						} // END if
					} // END if
				} else {
					$error = true;
					$this->data['outMess'] = "No files were selected for uploading.";
				}
				if ($error) {
					$this->data['subTitle'] = "Upload Files";
					$this->params['content'] = $this->load->view($this->views['FILE_UPLOADS'], $this->data, true);
					$this->params['subTitle'] =  "Admin Tools";
					$this->params['pageType'] = PAGE_FORM;
					$this->displayView();
				} else {
					$data = array('upload_data' => $this->upload->data());
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
	 *	@since 	1.0.4
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
		$result = '{ result: { items: ['.$result.']},code:"'.$code.'",status: "'.$status.'"}';
		
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
			// UPDATE VERSION 1.0.3
			//-------------------------------------------------------------
			// CHECK FOR DB CONNECTION FILE		
			if (defined('DB_CONNECTION_FILE') && !file_exists($this->params['config']['sql_file_path']."/".DB_CONNECTION_FILE)) {
				if (!function_exists('read_file')) { $this->load->helper('file'); }
				$this->data['db_file_update'] = updateDBFile((($this->params['config']['stats_lab_compatible'] == 1)?true:false),$this->params['config']['sql_file_path']);
			}
			// END 1.0.3 MODS
			
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
		$result = '{result:"'.$result.'",code:"'.$code.'",status:"'.$status.'"}';
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
		$result = '{result:"'.$result.'",code:"'.$code.'",status:"'.$status.'"}';
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

		$result = '{result:"'.$mess.'",code:"'.$code.'",status:"'.$status.'"}';
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
		}
		$code = 200;
		//$this->data['message'] = $status;
		//$this->dashboard();
		$result = '{result:"'.$mess.'",code:"'.$code.'",status:"'.$status.'"}';
		$this->output->set_header('Content-type: application/json'); 
		$this->output->set_output($result);
	}
	/**
	 *	POSITION ELIDGIBILITY.
	 */
	function elidgibility() {
		$result = '';
		$status = '';
		// CHECK FOR DUPLICATE
		if (!function_exists('position_elidgibility')) {
			$this->load->helper('roster');
		}
		$mess = position_elidgibility($this->params['config']['ootp_league_id']);
		if ($mess != "OK") {
			$status = "error:".$mess;
		} else {
			$status = "OK";
		}
		$code = 200;

		$result = '{result:"'.$mess.'",code:"'.$code.'",status:"'.$status.'"}';
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
		$mess = $this->player_model->updatePlayerRatings(15,getCurrentScoringPeriod($this->ootp_league_model->current_date),$this->params['config']['ootp_league_id']);
		
		
		if (empty($mess)) {
			$status = "error:An error occured during processing.";
		} else {
			print($mess);
			$status = "OK";
		}
		$code = 200;

		$result = '{result:"'.$mess.'",code:"'.$code.'",status:"'.$status.'"}';
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

		$result = '{result:"'.$mess.'",code:"'.$code.'",status:"'.$status.'"}';
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
		update_config('current_period',1);
		update_config('last_sql_load_time',date('Y-m-d',(strtotime(date('Y-m-d'))-(60*60*24))));
		update_config('last_process_time','1970-1-1 00:00:00');
		reset_ootp_league($this->params['config']['ootp_league_id']);
		if (!$mess) {
			$status = "error:".$mess;
		} else {
			$status = "OK";
		}
		$code = 200;
		$result = '{result:"Complete",code:"'.$code.'",status:"'.$status.'"}';
		$this->output->set_header('Content-type: application/json'); 
		$this->output->set_output($result);
	}
	/**
	 *	RESET RECENT SIM DATA.
	 *  CAUTION: THIS WILL WIPE OUT ALL SEASON WIDE DATA AND REST THE MOD BACK TO 
	 *	PRE_SEASON STATUS.
	 */
	function resetSim() {
		$result = '';
		$status = '';
		$mess = reset_sim($this->params['config']['current_period']);
		update_config('current_period',$this->params['config']['current_period'] - 1);
		update_config('last_process_time','1970-1-1 00:00:00');
		if (!$mess) {
			$status = "error:".$mess;
		} else {
			$status = "OK";
		}
		$code = 200;
		$result = '{result:"Complete",code:"'.$code.'",status:"'.$status.'"}';
		$this->output->set_header('Content-type: application/json'); 
		$this->output->set_output($result);
	}
	
	/**
	 *	GENERATE LEAGUE SCHEDULES.
	 */
	function generateSchedules() {
		// DIVIDE THE LEAGUE GAME SCHEDULE STARTING AT THE LEAGUE DATE BY THE SIM/PERIODS
		$result = '';
		$status = '';
		// CHECK FOR DUPLICATE
		$mess = // UPDATE PLAYER SCORING FOR THIS PERIOD
		$this->data['leagues'] = $this->league_model->getLeagues($this->params['config']['ootp_league_id'],-1);
		$error = false;
		foreach($this->data['leagues'] as $id => $details) {
			$this->league_model->load($id);
			$mess = $this->league_model->createLeagueSchedule();
		}
		if (!$mess) {
			$status = "error:".$mess;
		} else {
			$status = "OK";
		}
		$code = 200;
		$result = '{result:"Complete",code:"'.$code.'",status:"'.$status.'"}';
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
			$result = '{result:"'.$mess.'",code:"'.$code.'",status:"'.$status.'"}';
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
		$result = '{result:"'.$mess.'",code:"'.$code.'",status:"'.$status.'"}';
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
		$this->data['leagues'] = $this->league_model->getLeagues($this->params['config']['ootp_league_id'],-1);
		
		/*------------------------------
		/	UPDATE LEAGUE SCORING
		/-----------------------------*/
		foreach($this->data['leagues'] as $id => $details) {
			$summary .= $this->league_model->updateLeagueScoring($score_period, $id, $this->params['config']['ootp_league_id']);
			if ($this->league_model->errorCode != -1) {
				$mess =  $this->league_model->statusMess;
				break;
			}
		}
		if (!empty($mess)) {
			$summary .= $this->lang->line('sim_include_errors');
			$summary .= "<ul>".$mess."</ul>";
		} // END if
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
		$result = '{result:"'.$mess.'",code:"'.$code.'",status:"'.$status.'"}';
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
	}
}
/* End of file admin.php */
/* Location: ./application/controllers/admin.php */