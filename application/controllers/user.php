<?php
/**
 *	USER.
 *	The primary controller for user profiles and perosnal user account management and functionality.
 *
 *	Based on the Redux Authentication 2 library by Mathews Davies.
 *	 
 *	@author			Jeff Fox
 *	@dateCreated	04/04/10
 *	@lastModified	08/10/11
 *
 */
class user extends MY_Controller {
	
	/*--------------------------------
	/	VARIABLES
	/-------------------------------*/
	/**
	 *	SLUG.
	 *	@var $_NAME:Text
	 */
	var $_NAME = 'user';
	/*-------------------------------------------
	/
	/	 SITE SPECIFIC METHODS
	/
	/------------------------------------------*/
	/**
	 *	GET URI DATA.
	 *	Parses out an id or other parameters from the uri string
	 *
	 */
	protected function getURIData() {
		parent::getURIData();
		if ($this->input->post('mode')) {
			$this->uriVars['mode'] = $this->input->post('mode');
		} // END if
		if ($this->input->post('id')) {
			$this->uriVars['id'] = $this->input->post('id');
		} // END if
		if ($this->input->post('userId')) {
			$this->uriVars['userId'] = $this->input->post('userId');
		} // END if
		if ($this->input->post('league_id')) {
			$this->uriVars['league_id'] = $this->input->post('league_id');
		} // END if
		if ($this->input->post('team_id')) {
			$this->uriVars['team_id'] = $this->input->post('team_id');
		} // END if
		if ($this->input->post('ck')) {
			$this->uriVars['ck'] = $this->input->post('ck');
		} // END if
		if ($this->input->post('ct')) {
			$this->uriVars['ct'] = $this->input->post('ct');
		} // END if
		if ($this->input->post('chlg')) {
			$this->uriVars['chlg'] = $this->input->post('chlg');
		} // END if
		if ($this->input->post('resp')) {
			$this->uriVars['resp'] = $this->input->post('resp');
		} // END if
		
	}
	/**
	 *	MAKE NAV BAR
	 *
	 */
	protected function makeNav() {
		$loggedIn = $this->params['loggedIn'];
		array_push($this->params['subNavSection'],user_nav($loggedIn, $this->user_meta_model->firstName." ".$this->user_meta_model->lastName));
	}
	/**
	 *	CREATE LEAGUE
	 *	Runs a check of league limits and either allows or dienies a user to build a new league.
	 *
	 *	@since		1.0
	 *	@updated	1.0.3
	 */
	public function createLeague() {
		$addleague = false;
		if (!isset($this->league_model)) {
			$this->load->model('league_model');
		}
		/*-------------------------------------------------
		/ UPDATE 1.0.3 - LEGAUE RESTRICTION TEST
		/ UPDATED TO ALLOW FOR AMDINS TO OWN UNLIMITED LEAGUES AND TO SET A NUMBER OF LEAGUES PER USER
		/------------------------------------------------*/
		$isAdmin = $this->params['accessLevel'] == ACCESS_ADMINISTRATE;
		$session_auth = $this->session->userdata($this->config->item('session_auth'));
		$this->user_meta_model->load($session_auth,'userId');
		$leagueCount = $this->user_meta_model->getUserLeagueCount();
		if ($isAdmin && ($this->params['config']['restrict_admin_leagues'] == -1 || ($this->params['config']['restrict_admin_leagues'] == 1 && $this->params['config']['max_user_leagues'] < $leagueCount))) {
			$addleague = true;
		} else if ($this->params['config']['users_create_leagues'] == 1) {
			//echo("Max league count = ".$this->params['config']['max_user_leagues']."<br />");
			//echo("User league count = ".$leagueCount."<br />");
			if ($leagueCount < $this->params['config']['max_user_leagues']) {
				$addleague = true;
			} else {
				$plural = "s";
				if ($this->params['config']['max_user_leagues'] == 1) { $plural = ""; }
				$mess = str_replace('[MAX_LEAGUES]',$this->params['config']['max_user_leagues'],$this->lang->line('user_too_many_leagues'));
				$mess = str_replace('[PLURAL]',$plural,$mess);
				$mess = str_replace('[CONTACT_URL]',anchor('/about/contact/','contact the game owner'),$mess);
				$this->data['theContent'] = $mess;
			}
		} else if ($this->params['config']['users_create_leagues'] != 1) {
			$this->data['theContent'] = $this->lang->line('no_user_leagues');
		} // END if
		if ($addleague) {
			redirect('league/submit/mode/add');
		} else {
			$this->params['subTitle'] = $this->data['subTitle'] ="Create League";
			$this->params['content'] = $this->load->view($this->views['MESSAGE'], $this->data, true);
			$this->makeNav();
			$this->displayView();
		}
	}	
	/**
	 * 	INVITE RESPONSE.
	 * 	A function that validates a response from an invitation response and handles the users choice or
	 *	faults on an error.
	 *
	 *  @changelog	1.1 PROD - Updated process to no longer delete invites but simply change the status. Also added 
	 *  missing email notifications to League Commissioner when invites are accepted or declined or the user already 
	 *  owns a team so the request gets removed.
	 *
	 * 	@since	1.0
	 **/
	public function inviteResponse() {
		$error = false;
		$updateDb = false;
		$url = '';
		$message = '';
		$inviteId = -1;
		$inviteObj = NULL;
		$accetped = false;
		$dbData = array();
		$commishId = -1;
		$subject = '';
		$eMessage = '';
		$commishName = '';
		$emailType = '';
		
		$this->getURIData();
		if (((isset($this->uriVars['league_id']) && isset($this->uriVars['email'])) || isset($this->uriVars['id'])) && isset($this->uriVars['ck'])) {
			$this->db->select('*');
			if (isset($this->uriVars['league_id']) && isset($this->uriVars['email'])) {
				$this->db->where('league_id',$this->uriVars['league_id']);
				$this->db->where('to_email',$this->uriVars['email']);
			} else if (isset($this->uriVars['id'])) {
				$this->db->where('id',$this->uriVars['id']);
			}
			$query = $this->db->get('fantasy_invites');
			if ($query->num_rows() > 0) {
				$inviteObj  = $query->row();
				$inviteId = $inviteObj->id;
			}
			$query->free_result();
		}
		if ($inviteId  == -1) {
			$error = true;
			$message = "An error occured when processing your invitation response. The invitation ID code could not be found in our records. Please contact the league commissioner to request a new invitation be sent or for help with this error.";
		} else {
			if ($inviteObj->status_id == INVITE_STATUS_PENDING) {
				if ($this->params['loggedIn']) {
					// VALIDITY CHECK, prevent spam bots
					$confirm = md5($inviteObj->confirm_str.$inviteObj->confirm_key);
					if ($confirm == $this->uriVars['ck']) {
						$this->load->model('team_model');
						$this->team_model->load($inviteObj->team_id);
						$teamName = $this->team_model->teamname." ".$this->team_model->teamnick;
								
						if (!isset($this->league_model))
							$this->load->model('league_model');
						$leagueName = $this->league_model->getLeagueName($this->uriVars['league_id']);
						$commishId = $this->league_model->getCommissionerId($this->uriVars['league_id']);
						// IF NO COMMISSIONER ASSIGNED, SEND TO SITE ADMIN
						if ($commishId == -1) {
							$commishId = $this->params['config']['primary_contact'];
						}
						$commishName = getUsername($commishId);
								
						if (isset($this->uriVars['ct']) && $this->uriVars['ct'] == 1) {
							/*-------------------------------------------------------
							/	INVITATION ACCEPTED
							/------------------------------------------------------*/
							$owners = $this->league_model->getOwnerIds($inviteObj->league_id);
							if (!in_array($this->params['currUser'],$owners)) {
								/*-------------------------------------------------------
								/	INVITEE OWNS NO TEAM
								/------------------------------------------------------*/
								//echo("Loading team ".$inviteObj->team_id."<br />");
								//echo("setting owner id ".$this->params['currUser']."<br />");
								$this->team_model->owner_id = $this->params['currUser'];
								$this->team_model->save();
								//echo("Team owner id = ".$this->team_model->owner_id."<br />");
								$message = 'You have been set as the owner of the '.$this->team_model->teamname.'. You can now visit your '.anchor('/team/info/'.$inviteObj->team_id,'teams page').' and begin managing your team.';
								$url = 'user/profile/view/';
								$updateDb = true;
								$dbData = array('status_id', INVITE_STATUS_ACCEPTED);

								$accetped = true;
								
								// SEND NOTIFCATION EMAIL TO COMMISSIONER
								$msg = $this->lang->line('email_league_invite_accept');
								$msg = str_replace('[TEAM_NAME]', $teamName, $msg);
								$msg = str_replace('[COMMISH]', getUsername($commishId), $msg);
								$msg = str_replace('[EMAIL]', $this->uriVars['email'], $msg);
								$msg = str_replace('[LEAGUE_NAME]', $leagueName,$msg);
								$data['messageBody']= $msg;
								//print("email template path = ".$this->config->item('email_templates')."<br />");
								$data['leagueName'] = $leagueName;
								$data['title'] = $this->lang->line('email_league_invite_accept_title');
								$data['title'] = str_replace('[TEAM_NAME]', $teamName, $data['title']);
								$eMessage = $this->load->view($this->config->item('email_templates').'general_template', $data, true);
	
								$subject = $data['title'];
								$emailType = 'email_team_invite_accepted_';
	
							} else {
								/*-------------------------------------------------------
								/	INVITEE ALREADY OWNS A TEAM
								/------------------------------------------------------*/
								$error = true;
								$message = "<b>Invite Error</b><br /><br />We see that you already own a team in this league. You are not allowed to own more than one team in a league at a time.";
								$updateDb = true;
								$dbData = array('status_id', INVITE_STATUS_REMOVED);

								// SEND NOTIFCATION EMAIL TO COMMISSIONER
								$msg = $this->lang->line('email_league_invite_duplicate');
								$msg = str_replace('[TEAM_NAME]', $teamName, $msg);
								$msg = str_replace('[COMMISH]', getUsername($commishId), $msg);
								$msg = str_replace('[EMAIL]', $this->uriVars['email'], $msg);
								$msg = str_replace('[LEAGUE_NAME]', $leagueName,$msg);
								$data['messageBody']= $msg;
								$data['leagueName'] = $leagueName;
								$data['title'] = $this->lang->line('email_league_invite_duplicate_title');
								$data['title'] = str_replace('[TEAM_NAME]', $teamName, $data['title']);
								$eMessage = $this->load->view($this->config->item('email_templates').'general_template', $data, true);
	
								$subject = $data['title'];
								$emailType = 'email_team_invite_accepted_';
							}
						} else if (isset($this->uriVars['ct']) && $this->uriVars['ct'] == -1) {
							/*-------------------------------------------------------
							/	INVITATION DECLINED
							/------------------------------------------------------*/
							$message = 'You have chosen to decline this invitation. We\'re sorry you decided not to join. An email has been sent to the league commissioner to inform them of your choice.';
							$updateDb = true;
							$dbData = array('status_id', INVITE_STATUS_DECLINED);

							// SEND NOTIFCATION EMAIL TO COMMISSIONER
							$msg = $this->lang->line('email_league_invite_decline');
							$msg = str_replace('[TEAM_NAME]', $teamName, $msg);
							$msg = str_replace('[COMMISH]', getUsername($commishId), $msg);
							$msg = str_replace('[EMAIL]', $this->uriVars['email'], $msg);
							$msg = str_replace('[LEAGUE_NAME]', $leagueName,$msg);
							$data['messageBody']= $msg;
							//print("email template path = ".$this->config->item('email_templates')."<br />");
							$data['leagueName'] = $leagueName;
							$data['title'] = $this->lang->line('email_league_invite_decline_title');
							$data['title'] = str_replace('[TEAM_NAME]', $teamName, $data['title']);
							$eMessage = $this->load->view($this->config->item('email_templates').'general_template', $data, true);
	
							$subject = $data['title'];
							$emailType = 'email_team_invite_declined_';

						} else {
							$error = true;
							$message = "A required confirmation parameter was not recieved. Please contact the league commissioer to let them know if this issue.";
						}
					} else {
						$message = 'A required validation key did not match that in our records. Your invitation could not be validated at this time.';
						$error = true;
					}
				} else {
					$this->session->set_userdata('inviteId',$inviteObj->id);
					$this->session->set_userdata('confirmKey',$this->uriVars['ck']);
					$this->session->set_userdata('confirmType',$this->uriVars['ct']);
					$this->session->set_userdata('loginRedirect',current_url());	
					$this->session->set_flashdata('message', '<p class="notice">'.$this->lang->line('league_invite_response_not_logged_in').'</p>');		
					redirect('user/login');
				}
			} else {
				$message = 'This invitation has already been responded to. No futher action can be taken at this time.';
				$error = true;
			}
		}
		if ($sendEmail) {
			$this->load->model('user_auth_model');
			$error = !sendEmail($this->user_auth_model->getEmail($commishId),
								$this->user_auth_model->getEmail($this->params['config']['primary_contact']),
								$this->params['config']['site_name']." Adminstrator",
								$subject, $eMessage, $commishName, $emailType);
		}
		if ($updateDb) {
			$this->db->flush_cache();
			$this->db->where('id',$inviteObj->id);
			$this->db->update('fantasy_invites', $dbData);	
			
			$this->session->unset_userdata('inviteId');
			$this->session->unset_userdata('confirmKey');
			$this->session->unset_userdata('confirmType');
		}
		if (!$error) {
			$message = '<span class="success">'.$message .'</span>';
			if ($accetped) { 
				$message .= '<br /><br />'.anchor('/team/info/'.$inviteObj->team_id,'Go to your team page').'<br /><br />';
			}
		} else {
			$message = '<span class="error">'.$message .'</span >';
		}
		$this->data['subTitle'] = 'Team Invitation Response';
		$this->data['theContent'] = $message;
		$this->params['content'] = $this->load->view($this->views['MESSAGE'], $this->data, true);
		$this->displayView();
	}
	/*-------------------------------------------
	/
	/	 STATIC CLASS METHODS
	/
	/------------------------------------------*/
	
	/*--------------------------------
	/	C'TOR
	/-------------------------------*/
	/**
	 *	Creates a new instance of user.
	 */
	function user() {
		parent::MY_Controller();
		
		$this->views['REGISTER'] = DIR_VIEWS_USERS.'register';
		$this->views['LOGIN'] = DIR_VIEWS_USERS.'login';
		$this->views['CHANGE_PASSWORD'] = DIR_VIEWS_USERS.'change_password';
		$this->views['PROFILE'] = DIR_VIEWS_USERS.'profile_info';
		$this->views['PROFILE_EDIT'] = DIR_VIEWS_USERS.'profile_edit';
		$this->views['PROFILE_PICK'] = DIR_VIEWS_USERS.'profile_pick';
		$this->views['ACTIVATE'] = DIR_VIEWS_USERS.'activate';
		$this->views['ACTIVATE_RESEND'] = DIR_VIEWS_USERS.'activate_resend';
		$this->views['ACCOUNT'] = DIR_VIEWS_USERS.'account';
		$this->views['ACCOUNT_EDIT'] = DIR_VIEWS_USERS.'account_edit';
		$this->views['AVATAR_UPLOAD'] = DIR_VIEWS_USERS.'profile_avatar';
		$this->views['PENDING'] = 'content_pending';
		$this->views['MESSAGE'] = DIR_VIEWS_USERS.'user_message';
		$this->views['FORGOT_PASSWORD'] = DIR_VIEWS_USERS.'forgotten_password';
		$this->views['FORGOT_PASSWORD_VERIFY'] = DIR_VIEWS_USERS.'forgotten_password_verify';
		
		$this->enqueStyle('content.css');
		$this->debug = false;
	}
	/**
	 *	INDEX.
	 *	The default handler when the controller is called.
	 *	Checks for an existing auth session, and if found,
	 *	redirects to the dashboard. Otherwise, it redirects 
	 *	to the login.
	 */
	function index() {
		redirect('user/profile');
	}	
	/*---------------------------------------
	/	CONTROLLER SUBMISSION HANDLERS
	/--------------------------------------*/
	/**
	 * Account
	 *
	 * @return void 
	 **/
	public function account() {
		if ($this->params['loggedIn']) {
	        $this->getURIData();
			$func = "view";
			if (isset($this->uriVars['id'])) {
				$func = $this->uriVars['id'];
			} else if (isset($this->uriVars['mode'])) {
				$func = $this->uriVars['mode'];
			} // END if
			$this->data['account'] = $this->auth->accountDetails();
			/*--------------------------------------
			/	View the account details
			/-------------------------------------*/
			if ($func == "view") {
				$levelStr = '';
				if ($this->data['account']->levelId != -1 && $this->data['account']->levelId != 0) {
					$levelList = loadSimpleDataList('userLevel');
					foreach($levelList as $key => $value) {
						if ($this->data['account']->levelId == $key) {
							$levelStr = "L".$key." - ".$value;
							break;
						} // END if
					} // END foreach
				} // END if
				$this->data['account']->userLevel = $levelStr;
				
				$typeStr = '';
				if ($this->data['account']->typeId != -1 && $this->data['account']->typeId != 0) {
					$typeList = loadSimpleDataList('userType');
					foreach($typeList as $key => $value) {
						if ($this->data['account']->typeId == $key) {
							$typeStr = $value;
							break;
						} // END if
					} // END foreach
				} // END if
				$this->data['account']->userType = $typeStr;
				
				$accessStr = '';
				if ($this->data['account']->accessId != -1 && $this->data['account']->accessId != 0) {
					$accessList = loadSimpleDataList('accessLevel');
					foreach($accessList as $key => $value) {
						if ($this->data['account']->accessId == $key) {
							$accessStr = "L".$key." - ".$value;
							break;
						} // END if
					} // END foreach
				} // END if
				$this->data['account']->accessLevel = $accessStr;
				$this->params['subTitle'] = $this->data['subTitle'] = 'Account Details';
				$this->params['content'] = $this->load->view($this->views['ACCOUNT'], $this->data, true);
				$this->makeNav();
				$this->displayView();
			/*--------------------------------------
			/	Edit the account details
			/-------------------------------------*/
			} else {
				$this->form_validation->set_rules('email', 'Email Address', 'required|trim|valid_email');
				$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
				
				if ($this->form_validation->run() == false) {
					$this->params['subTitle'] = $this->data['subTitle'] = 'Edit Account';
					$this->data['input'] = $this->input;
					$this->params['content'] = $this->load->view($this->views['ACCOUNT_EDIT'], $this->data, true);
					$this->params['pageType'] = PAGE_FORM;
					$this->makeNav();
					$this->displayView();
				} else {
					if ($this->input->post('email') != $this->data['account']->email) {
						$this->db->select('email')->from('users_core')->where('email',$this->input->post('email'));
						$query = $this->db->get();
						if ($query->num_rows() != 0) {
							$this->session->set_flashdata('message', '<p class="error">Account Update Failed. The email address <b>'.$this->input->post('email').'</b> is already in use. Please choose a different e-mail address.</p>');
							redirect('user/account/edit');
						} // END if
					} // END if
					$session_auth = $this->session->userdata($this->config->item('session_auth'));
					$change = $this->auth->account_update($this->input,$session_auth);
					if ($change) {
						$this->session->set_flashdata('message', '<p class="success">Your account details were successfully changed.</p>');
						redirect('user/account');
					} else {
						$message = '<p class="error">Account Update Failed.';
						if ($this->auth->get_status_code() != 0) {
							$message .= ' '.$this->auth->get_status_message().'</p>';
						} // END if
						$message .= '</p >';
						$this->session->set_flashdata('message', $message);
						redirect('user/account/edit');
					} // END if
				} // END if
			} // END if
	    } else {
	        $this->session->set_flashdata('loginRedirect',current_url());	
			redirect('user/login');
	    } // END if
	}
	/**
	 * Avatar
	 *
	 * @return void 
	 **/
	public function avatar() {
		if ($this->params['loggedIn']) {
			$session_auth = $this->session->userdata($this->config->item('session_auth'));
			$this->user_meta_model->load($session_auth,'userId');
			$this->data['avatar'] = $this->user_meta_model->avatar;
			$this->data['subTitle'] = 'Update Avatar';
			if (!($this->input->post('submitted')) || ($this->input->post('submitted') && !isset($_FILES['avatarFile']['name']))) {
				if ($this->input->post('submitted') && !isset($_FILES['avatarFile']['name'])) {
					$fv = & _get_validation_object();
					$fv->setError('avatarFile','The Avatar File field is required.');
				} // END if
				$this->params['content'] = $this->load->view($this->views['AVATAR_UPLOAD'], $this->data, true);
				$this->params['pageType'] = PAGE_FORM;
				$this->makeNav();
				$this->displayView();
			} else {
				$change = $this->user_meta_model->applyData($this->input, $this->params['currUser']); 
				if ($change) {
					$this->user_meta_model->save();
					$this->session->set_flashdata('message', '<p class="success">Your avatar has been successfully updated.</p>');
					redirect('user/profile');
				} else {
					$message = '<p class="error">Avatar Change Failed.';
					if ($this->auth->get_status_code() != 0) {
						$message .= ' '.$this->auth->get_status_message().'</p>';
					} // END if
					$message .= '</p >';
					$this->session->set_flashdata('message', $message);
					redirect('user/avatar');
				} // END if
			} // END if
		} else {
	        $this->session->set_flashdata('loginRedirect',current_url());	
			redirect('user/login');
	    } // END if
	}
	/**
	 * Preferences
	 * Placeholder for futre preferences editor and viewa.
	 * @return void
	 **/
	public function preferences() {
		if ($this->params['loggedIn']) {
	      	$this->params['content'] = $this->load->view('content_pending', null, true);
	        $this->makeNav();
			$this->displayView();
	    } else {
	        $this->session->set_flashdata('loginRedirect',current_url());	
			redirect('user/login');
	    }
	}
	/**
	 * change password
	 *
	 * @return void
	 **/
	function change_password() {	    
		if ($this->params['loggedIn']) {
			$this->form_validation->set_rules('old', 'Old password', 'required');
			$this->form_validation->set_rules('new', 'New Password', 'required|matches[new_repeat]');
			$this->form_validation->set_rules('new_repeat', 'Repeat New Password', 'required');

			if ($this->form_validation->run() == false) {
				$this->params['subTitle'] = "Profile";
				$this->params['content'] = $this->load->view($this->views['CHANGE_PASSWORD'], null, true);
				$this->params['pageType'] = PAGE_FORM;
				$this->makeNav();
				$this->displayView();
			} else {
				
				$session_auth = $this->session->userdata($this->config->item('session_auth'));
				$change = $this->auth->change_password($session_auth, $this->input->post('old'), $this->input->post('new'));
			
				if ($change) {
					$this->session->set_flashdata('message', '<p class="success">Your password was successfully changed.</p>');
					redirect('user/account');
				} else {
					$message = '<p class="error">Password Change Failed.';
					if ($this->auth->get_status_code() != 0) {
						$message .= ' '.$this->auth->get_status_message().'</p>';
					}
					$message .= '</p >';
					$this->session->set_flashdata('message', $message);
					redirect('user/change_password');
				}
			}
		} else {
	        $this->session->set_flashdata('loginRedirect',current_url());	
			redirect('user/login');
	    }
	}
	/**
	 * 	ACTIVATION
	 *
	 * @return void
	 **/
	public function activate() {
	    $this->getURIData();
		$code = '';
		$this->params['subTitle'] = "Account Activation";
		if (isset($this->uriVars['code']) && !empty($this->uriVars['code'])) {
			$code = $this->uriVars['code'];
		} else {
			$this->form_validation->set_rules('code', 'Verification Code', 'required');
			if ($this->form_validation->run() == false) {
				$this->params['content'] = "Account Activation";
				$this->params['content'] = $this->load->view($this->views['ACTIVATE'], $this->data, true);
	       		$this->params['pageType']= PAGE_FORM;
				$this->makeNav();
				$this->displayView();
			} else {
				$code = $this->input->post('code');
			}
		}
		if (!empty($code)) {
			$activated = $this->auth->activate($code);
			if ($activated) {
				$message = '<span class="success">Congratulations. Your account has been activated. You may now login and begin using the site.</span>';
				$this->session->set_flashdata('message', $message);
	        	redirect('/user/login');
			} else {
				$this->data['outMess'] = '<span class="error">Your membership could not be activated at this time due to the following reason: '.$this->auth->get_status_message().'. Please check your code and try again or '.anchor('/about/contact','contact the site administrator').' for help</span>';
				$this->params['content'] = $this->load->view($this->views['ACTIVATE'], $this->data, true);
	       		$this->params['pageType'] = PAGE_FORM;
				$this->makeNav();
				$this->displayView();
			}
		}
	}
	/**
	 * 	RESEND ACTIVATION CODE BY EMAIL.
	 * 	Allows a user to have their activation code emailed to them again.
	 *
	 *	
	 **/
	function resend_activation() {
	    $this->makeNav();
		$this->params['subTitle'] = $this->lang->line('user_missing_activation_title');
		$this->form_validation->set_rules('email', 'Email Address', 'required|valid_email|max_length[500]');
	    if ($this->form_validation->run() == false) {
	       	$this->data['subTitle'] = $this->lang->line('user_missing_activation_title');
			$this->data['theContent'] = str_replace('[SITE_URL]',$this->params['config']['fantasy_web_root'],$this->lang->line('user_missing_activation'));
		   	$this->params['content'] = $this->load->view($this->views['ACTIVATE_RESEND'], $this->data, true);
	        $this->params['pageType'] = PAGE_FORM;
			$this->displayView();
	    } else {
	        $email = $this->input->post('email');
			$sent = $this->auth->resend_activation($email,$this->debug);
			if ($sent) {
				$this->session->set_flashdata('message', '<span class="success">An email has been sent to '.$email.'. Please check your inbox.</span>');
	            redirect('/user/login');
			} else {
				$message = '<span class="error"><strong>An error has occured.</strong><br />';
				if ($this->auth->get_status_code() != 0) {
					$message .= ' The email failed to send for the following reason:<br />'.$this->auth->get_status_message();
				}
				$message .= '</span>';
	            $this->data['subTitle'] = $this->lang->line('user_missing_activation_title');
				$this->data['theContent'] = $message;
				$this->params['content'] = $this->load->view($this->views['ACTIVATE_RESEND'], $this->data, true);
				$this->params['pageType'] = PAGE_FORM;
				$this->displayView();
			}
	    }
	}
	/**
	 * forgotten password
	 *
	 * @return void
	 **/
	function forgotten_password() {
	    $this->makeNav();
		$this->params['subTitle'] = "Forgot Password";
		$this->form_validation->set_rules('email', 'Email Address', 'required|valid_email|max_length[500]');
	    if ($this->form_validation->run() == false) {
	       	$this->data['subTitle'] = $this->lang->line('user_forgotpass_title');
			$this->data['theContent'] = str_replace('[SITE_URL]',$this->params['config']['fantasy_web_root'],$this->lang->line('user_forgotpass_instruct'));
		   	$this->params['content'] = $this->load->view($this->views['FORGOT_PASSWORD'], $this->data, true);
	        $this->params['pageType'] = PAGE_FORM;
	    } else {
	        $email = $this->input->post('email');
			$forgotten = $this->auth->forgotten_password($email,$this->debug);
			if ($forgotten) {
				$this->session->set_flashdata('message', '<span class="success">An email has been sent to '.$email.'. Please check your inbox.</span>');
	            redirect('/user/login');
			} else {
				$message = '<span class="error"><strong>An error has occured.</strong><br />The email failed to send.';
				if ($this->auth->get_status_code() != 0) {
					$message .= ' '.$this->auth->get_status_message();
				}
				$message .= '</span>';
	            $this->data['subTitle'] = $this->lang->line('user_forgotpass_title');
				$this->data['theContent'] = $message;
				$this->params['content'] = $this->load->view($this->views['FORGOT_PASSWORD'], $this->data, true);
				$this->params['pageType'] = PAGE_FORM;
			}
	    }
		$this->displayView();
	}
	/**
	 * forgotten_password_verify
	 *
	 * @return void
	 **/
	public function forgotten_password_verify() {
	    $this->getURIData();
		$code = '';
		$this->params['subTitle'] = "New Password Verification";
		if (isset($this->uriVars['code']) && !empty($this->uriVars['code'])) {
			$code = $this->uriVars['code'];
		} else {
			$this->form_validation->set_rules('code', 'Verification Code', 'required');
			if ($this->form_validation->run() == false) {
				$this->params['content'] = $this->load->view($this->views['FORGOT_PASSWORD_VERIFY'], $this->data, true);
	       		$this->params['pageType']= PAGE_FORM;
				$this->makeNav();
				$this->displayView();
			} else {
				$code = $this->input->post('code');
			}
		}
		if (!empty($code)) {
			$forgotten = $this->auth->forgotten_password_complete($code);
			if ($forgotten) {
				$message = '<span class="success">A new password has been generated for you. and sent to your email address. Please check your inbox.</span>';
			} else {
				$message = '<span class="error">The verification email failed to send, pleaee try again or contact the site administrator for assistance.</span>';
			}
			$this->session->set_flashdata('message', $message);
	        redirect('/user/login');
		}
	}
	/**
	 * login
	 *
	 * @return void
	 **/
	function login() {
	   
	   	if ($this->data['loggedIn']) {
			if ($this->data['accessLevel'] < ACCESS_ADMINISTRATE) {
				redirect('user');
			} else {
				redirect('admin');
			}
		} else {
			$this->form_validation->set_rules('username', 'Username', 'required');
			$this->form_validation->set_rules('password', 'Password', 'required');
			if ($this->form_validation->run() == false) {
				$this->data['authenticationType'] = $this->params['config']['user_activation_method'];
				if ($this->data['authenticationType'] == 1) {
					$this->data['activate_str'] = str_replace('[ACCOUNT_ACTIVATE_URL]',anchor('/user/activate','Account Activation'),$this->lang->line('user_login_activate_email'));
					$this->data['activate_str'] = str_replace('[ACTIVATE_RESEND_URL]',anchor('/user/resend_activation','Activation Code Request'),$this->data['activate_str']);
				}
				$this->data['input'] = $this->input;
				$this->params['content'] = $this->load->view($this->views['LOGIN'], $this->data, true);
				$this->params['subTitle'] = "User Login";
				$this->params['pageType'] = PAGE_FORM;
				$this->makeNav();
				$this->displayView();
			} else {
				if ($this->auth->login($this->input->post('username'), $this->input->post('password'))) {
					$inviteId = $this->session->userdata('inviteId');
					$redirect = $this->session->userdata('loginRedirect');
					if (isset($inviteId) && !empty($inviteId)) {
						redirect('/user/inviteResponse/id/'.$inviteId."/ck/".$this->session->userdata('confirmKey')."/ct/".$this->session->userdata('confirmType'));
					} else if (isset($redirect) && !empty($redirect)) {
						$this->session->unset_userdata('loginRedirect');
						redirect($redirect);
					} else {
						redirect('user');
					}
				} else {
					$message = '';
					if ($this->auth->get_status_code() != 0) {
						$message = '<span class="error">'.$this->auth->get_status_message().' Please try again.</span>';
					}
					$this->session->set_flashdata('message', $message);
					redirect('user/login');
				} // END if
			} // END if
		}
	}
	/**
	 * 	USER LOGOUT
	 *
	 *	
	 **/
	function logout($endSession = true) {
		$this->auth->logout($endSession);
		redirect('user');
	}
	/**
	 * 	USER PROFILE
	 *
	 *	
	 **/
	public function profile() {
		$this->getURIData();
		$func = "view";
		if (isset($this->uriVars['mode'])) {
			$func = $this->uriVars['mode'];
		} // END if	
		if (!isset($this->uriVars['mode']) && (isset($this->uriVars['id']) && ($this->uriVars['id'] == 'view' || $this->uriVars['id'] == 'edit'))) {
			$func = $this->uriVars['id'];
		} // END if	
		$view = $this->views['PROFILE'];
        $userTeams = array();
        $currPeriod = false;
        if (strtotime($this->ootp_league_model->current_date) > strtotime($this->ootp_league_model->start_date)) {
            $currPeriod = ($this->params['config']['current_period']- 1);
        } // END if	
        if (isset($this->params['userTeams'])) {
			$userTeams = $this->params['userTeams'];
        } else {
            $userTeams = $this->user_meta_model->getUserTeams(false, false, $currPeriod);
        } // END if	

		if ($func == "view") {
			$view = $this->views['PROFILE'];
			$session_auth = $this->session->userdata($this->config->item('session_auth'));
			if ($session_auth && $this->user_meta_model->load($session_auth,'userId')) {
				$this->data['invites'] = $this->user_meta_model->getTeamInvites();
				$this->data['requests'] = $this->user_meta_model->getTeamRequests();
				$this->data['profile'] = $this->user_meta_model->profile();
				$this->data['thisItem']['userTeams'] = $userTeams;
				$this->data['dateCreated'] = $this->user_auth_model->dateCreated;
				$this->data['dateModified'] = $this->user_auth_model->dateModified;
				$this->data['myProfile'] = true;
				$this->data['subTitle'] = 'Your Profile';
				$this->data['userId'] = $this->user_meta_model->userId;
			} else {
				redirect('user/login');
			} // END if	
			if (!isset($this->data['profile'])) {
				$this->session->set_flashdata('message', '<span class="error">An error occured loading your profile information.</span>');
			} else {
				$countryStr = '';
				if (isset($this->data['profile']->country) && $this->data['profile']->country != -1 && $this->data['profile']->country != 0) {
					$countryList = loadCountries();
					foreach($countryList as $key => $value) {
						if ($this->data['profile']->country == $key) {
							$countryStr = $value;
							break;
						} // END if	
					} // END foreach	
				} // END if	
				$this->data['countryStr'] = $countryStr;
								
				$userDrafts = $this->user_meta_model->getUserDrafts();
				
				if (!isset($this->draft_model)) {
					$this->load->model('draft_model');
				} // END if	
				foreach($this->data['userTeams'] as $data) {
					$userDrafts[$data['league_id']]['draftStatus'] = $this->draft_model->getDraftStatus($data['league_id']);
					$userDrafts[$data['league_id']]['draftDate'] = $this->draft_model->getDraftDate($data['league_id']);
				} // END foreach	
				$this->data['userDrafts'] = $userDrafts;
				
				// EDIT 1.0.3 PROD - PLAYOFFS BANNER
				$this->data['playoffs'] = array();
				
					
				$league_list = $this->user_meta_model->getUserLeagueIds(false,$currPeriod);
				if (sizeof($league_list) > 0) {
					foreach($league_list as $league_id) {
						// EDIT 0.6 RC1
						// TEST FOR TRADES UNDER LEAGUE REVIEW STATUS
						// THIS BLOCK NOT ONLY RETRIEVES THE LIST OF TRADES IN REVIEW, BUT ALSO PSUEDO CRONS
						// THEM AND APPROVES TRADES WHERE THE PROTEST PERIOD HAS EXPRIRED
						if ($this->params['config']['useTrades'] == 1) {
							if ($this->params['config']['approvalType'] == 2) {
								$this->league_model->getTradesInLeagueReview($currPeriod,$league_id,$this->params['config']['protestPeriodDays'], true);
							} // END if	
							// TRADES EXPIRATION
							if ($this->params['config']['tradesExpire'] == 1) {
								$this->league_model->expireOldTrades($league_id, false, $this->debug);
							} // END if	
						}
						if ($this->league_model->getScoringType($league_id) == LEAGUE_SCORING_HEADTOHEAD) {
							$playoffArr = array();
							if (inPlayoffPeriod($this->params['config']['current_period'], $league_id)) {
								$curr_period = $this->getScoringPeriod();
								$playoffArr['inPlayoffs']= 1;
								$playoffArr['league_year']=date('Y', strtotime($curr_period['date_start']));
								$playoffArr['league_name'] = $this->league_model->getLeagueName($league_id);
							}
							$playoffSettings = $this->league_model->getPlayoffSettings($league_id);
							if ($this->params['config']['current_period']== $playoffSettings['regular_scoring_periods'] && $playoffSettings['playoff_rounds'] > 0) {
								$playoffArr['playoffsNext'] = 1;
								$playoffArr['playoffsTrans'] = $playoffSettings['allow_playoff_trans'];
								$playoffArr['playoffsTrades'] = $playoffSettings['allow_playoff_trades'];
							}
							if (sizeof($playoffArr) > 0) $this->data['playoffs'][$league_id] = $playoffArr;
						}
						// PLAYOFF ROSTER ALERT MESSAGE	
					} // END foreach		
				} // END if
				$this->data['userTrades'] = $this->user_meta_model->getTradeOffers(false,$currPeriod);
				if ($this->params['config']['approvalType'] == 2) {
					$this->data['tradesForReview'] = $this->user_meta_model->getTradesForReview(false,$currPeriod);
				}
			} // END if	
			$this->params['content'] = $this->load->view($view, $this->data, true);
			$this->params['pageType'] = PAGE_FORM;
			$this->makeNav();
			$this->displayView();
		} else {
			$id = $this->uriVars['id'];
			if ($this->params['loggedIn']) {
				$session_auth = $this->session->userdata($this->config->item('session_auth'));
				$this->user_meta_model->load($session_auth,'userId');
				$this->data['profile'] = $this->user_meta_model->profile();
				$this->form_validation->set_rules('firstName', 'First Name', 'required|trim');
				$this->form_validation->set_rules('lastName', 'Last Name', 'required|trim');
				$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
				
				if ($this->form_validation->run() == false) {
					$this->data['subTitle'] = 'Edit Profile';
					$this->data['input'] = $this->input;
					$this->params['content'] = $this->load->view($this->views['PROFILE_EDIT'], $this->data, true);
					$this->params['pageType'] = PAGE_FORM;
					$this->makeNav();
					$this->displayView();
				} else {
					$this->user_meta_model->applyData($this->input,$this->params['currUser']);
					$change =$this->user_meta_model->save();
					if ($change) {
						$this->session->set_flashdata('message', '<p class="success">Your profile has been successfully updated.</p>');
						redirect('/user/profile/');
					} else {
						$message = '<p class="error">Profile Update Failed.';
						if ($this->auth->get_status_code() != 0) {
							$message .= ' '.$this->auth->get_status_message().'</p>';
						}
						$message .= '</p >';
						$this->session->set_flashdata('message', $message);
						redirect('/user/profile/edit');
					} // END if	
				} // END if	
			} else {
	       		$this->session->set_flashdata('loginRedirect',current_url());	
				redirect('user/login');
	   		} // END if	
		} // END if	
	}
	/**
	 * 	PUBLIC PROFILES.
	 * 	Lets users browse public user profiles.
	 *
	 *	@since			1.0
	 * 	@access			public
	 *
	 * 
	 **/
	public function profiles() {
		if ($this->params['loggedIn']) {
			$this->getURIData();
			$view = $this->views['PROFILE'];
			$this->data['profile'] = NULL;
			if (isset($this->uriVars['id'])) {
				$userId = -1;
				if (preg_match('/^[0-9]*$/',trim(intval($this->uriVars['id'])))) {
					$userId = $this->uriVars['id'];
				} else if (preg_match('/[A-z0-9]$/',$this->uriVars['id'])) {
					//echo("String id");
					// LOOK UP USER ID
					$userId = $this->user_auth_model->getUserId($this->uriVars['id']);
					//if ($userId === false) $userId = $this->uriVars['id'];
				}
				if ($this->user_meta_model->load($userId,'userId')) {
					$this->data['profile'] = $this->user_meta_model->profile();
					$dates = $this->user_auth_model->getDateDetails($userId);

					$currPeriod = false;
					if (strtotime($this->ootp_league_model->current_date) > strtotime($this->ootp_league_model->start_date)) {
						$currPeriod = $this->params['config']['current_period']-1;
					}
					$this->data['thisItem']['userTeams'] = $this->user_meta_model->getUserTeams(false,$userId,$currPeriod);

					$this->data['dateCreated'] = $dates['dateCreated'];
					$this->data['dateModified'] = $dates['dateModified'];
					$this->data['subTitle'] = 'User Profile';
				} else {
					$this->session->set_flashdata('message', '<span class="error">No matching user profile was found. It\'s possible the record has been renamed, moved or deleted.</span>');
					$this->data['subTitle'] = 'User Profiles';
					$this->data['users'] = loadSimpleDataList('username');
					$this->params['pageType'] = PAGE_FORM;
				$view = $this->views['PROFILE_PICK'];
				}
			} else {
				$this->data['subTitle'] = 'User Profiles';
				$this->data['users'] = loadSimpleDataList('username');
				$this->params['pageType'] = PAGE_FORM;
				$view = $this->views['PROFILE_PICK'];
			}
			$this->params['content'] = $this->load->view($view, $this->data, true);
			$this->makeNav();
			$this->displayView();
		} else {
			$this->session->set_flashdata('message', $this->lang->line('user_register_existing'));
			redirect('user/login');
		}
	}
	/**
	 * 	REGISTER.
	 * 	Registers a new user account on the site.
	 *
	 *	@since			1.0
	 *	@updated		1.0.6
	 * 	@access			public
	 *
	 *	@changeLog		1.0.6 BETA - Added Anti-Spam Security countermeasure support
	 *	@changeLog		1.0.3 PROD - Removed outdated Recaptcha
	 * 
	 **/
	function register() {
	    if (!$this->params['loggedIn']) {
			$this->form_validation->set_rules('email', 'Email Address', 'required|callback_email_check|valid_email');
			$this->form_validation->set_rules('username', 'Username', 'required|min_length[3]|max_length[150]|callback_username_check');
			$this->form_validation->set_rules('password', 'Password', 'required|min_length[8]|max_length[32]|match[passwordConfirm]');			
			$this->form_validation->set_rules('passwordConfirm', 'Confirm Password', 'required|min_length[8]|max_length[32]');			
			if ($this->form_validation->run() == false) {
				$this->data['subTitle'] = $this->lang->line('user_register_title');
				$this->data['theContent'] = $this->lang->line('user_register_instruct');
				
				// EDIT 1.0.6 - SECURITY
				// DEPRECATED 1.0.3 PROD
				//if ($this->params['config']['security_enabled'] != -1 && $this->params['config']['security_class'] >= 1) {
				//	$this->data = $this->data + getSecurityCode($this->views['RECAPTCHA_JS']);
				//} // END if
				
				// END 1.0.6 EDIT
				// ACTIVATION
				if ($this->params['config']['user_activation_method'] != -1) {
					switch ($this->params['config']['user_activation_method']) {
						case 1:
		   					$this->data['activation'] = str_replace('[REQUEST_URL]',anchor('/user/resend_activation','request'),$this->lang->line('user_register_activation_email'));
							$this->data['activation'] = str_replace('[CONTACT_URL]',anchor('/about/contact','contact'),$this->data['activation']);
							
							break;
						case 2:
							$this->data['activation'] = $this->lang->line('user_register_activation_admin');
							break;
						default:
							break;
					} // END switch
				} // END if
				$this->data['input'] = $this->input;
				$this->params['content'] = $this->load->view($this->views['REGISTER'], $this->data, true);
				$this->params['pageType'] = PAGE_FORM;
				$this->displayView();
			} else {       	       
				$this->debug = false;
				if ($this->auth->register($this->input,$this->debug)) {
					$registered = $this->lang->line('user_registered');
					if ($this->params['config']['user_activation_required'] != -1 && $this->params['config']['user_activation_method'] != -1) {
						switch ($this->params['config']['user_activation_method']) {
							case 1:
								$registered .= str_replace('[EMAIL]',$this->input->post('email'),$this->lang->line('user_register_activate_email'));
								break;
							case 2:
								$registered .= $this->lang->line('user_register_activate_admin');
								break;
						} // END switch
					} else {
						$registered .= $this->lang->line('user_register_activate_none');
					} // END if
					$this->session->set_flashdata('message', '<span class="success">'.$registered.'</p>');
					redirect('user/login');
				} else {
					$message = '<span class="error">An error has occured. ';
					if ($this->auth->get_status_code() != 0) {
						$message .= "The server replied with the following status: <b>".$this->auth->get_status_message()."</b> ";
						$message .= 'Please <a rel="back" href="#">go back</a> and try submitting again.';
					}	
					$message .= "</span>";
					$this->data['subTitle'] = $this->lang->line('user_register_title');
					$this->data['theContent'] = $message;
					$this->params['content'] = $this->load->view($this->views['MESSAGE'], $this->data, true);
					$this->params['pageType'] = PAGE_FORM;
					$this->displayView();
				}
			}
		} else {
			$this->session->set_flashdata('message', $this->lang->line('user_register_existing'));
			redirect('home');
		}
	}
	/*------------------------------------------------------
	/
	/	AJAX FUNCTIONS
	/
	/-----------------------------------------------------*/
	/**
	* 	RECAPTCHA TEST
	* 	TEST UPER RECAPPTCHA DATA AGAINST THE EXPECTED VALUE.
	* 	This function is used sitewide by all reCAPTCHA submissions.
	*
	* 	URI VARS DATA PARAMS
	* 	@param	$chlg	{String}	Challenge String
	* 	@param	#resp	{String}	Response String
	* 	@return			{JSON}		JSON Response String
	*
	* 	@since			1.0.6
	* 	@access			public
	*	@deprecated		1.0.3 PROD
	*/
	/*public function captchaTest() {
		$this->getURIData();
		$code = 200;
		$status = $answer = 'OK';
		$priv_key = (isset($this->params['config']['recaptcha_key_private'])) ? $this->params['config']['recaptcha_key_private'] : '';
		if (!empty($priv_key)) {
			if (isset($this->uriVars['chlg']) && !empty($this->uriVars['chlg']) &&
			isset($this->uriVars['resp']) && !empty($this->uriVars['resp'])) {
				$this->load->helper('recaptcha');
				$resp = recaptcha_check_answer($priv_key, $_SERVER['REMOTE_ADDR'],
				$this->uriVars['chlg'],
				$this->uriVars['resp']);
				if (!$resp->is_valid) {
					$code = 301;
					$status = "fail";
					$answer = "reCAPTCHA verification failed. please try again.";
				}
			} else {
				$code = 301;
				$status = "fail";
				$answer = "Required reCAPTCHA parameters were missing.";
			} // END if
		} else {
			$code = 301;
			$status = "fail";
			$answer = "Required reCAPTCHA credentials for this site are missing.";
		} // END if
		$result = '{"result":"'.$answer.'","code":"'.$code.'","status":"'.$status.'"}';
		$this->output->set_header('Content-type: application/json');
		$this->output->set_output($result);
	}*/
}
