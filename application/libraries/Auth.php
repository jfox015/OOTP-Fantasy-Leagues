<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *	Authentication Library
 *	Based on Redux Authentication 2
 * ----------------------------------------------------------------------------
 * "THE BEER-WARE LICENSE" :
 * <thepixeldeveloper@googlemail.com> wrote this file. As long as you retain this notice you
 * can do whatever you want with this stuff. If we meet some day, and you think
 * this stuff is worth it, you can buy me a beer in return Mathew Davies
 * ----------------------------------------------------------------------------
 */
class Auth
{
	/**
	 * CodeIgniter global object
	 *
	 * @var string
	 **/
	protected $ci;

	/**
	 * account status ('not_activated', etc ...)
	 *
	 * @var string
	 **/
	protected $status;

	/*---------------------------------------------
	/
	/	C'TOR
	/	Creates a new instance of Auth
	/
	/---------------------------------------------*/
	public function __construct() {
		$this->ci =& get_instance();
		$email = $this->ci->config->item('email');
		$this->ci->load->library('email', $email);
	}
	/**
	 * update account feature
	 *
	 * @return void
	 **/
	public function account_update($input,$userId) {
		$this->ci->user_auth_model->applyData($input,$userId);
		return $this->ci->user_auth_model->save();
	}
	
	/**
	 * Activate user.
	 *
	 * @return TRUE on sucess, FALSE on error
	 **/
	public function activate($code) {
		if (($id = $this->ci->user_auth_model->activate($code)) !== false) {
			return $this->confirmationEmail($this->ci->user_auth_model->getEmail($id),$this->ci->user_auth_model->getUsername($id));
		} else {
			return false;
		}
	}
	/**
	 * Administrator Activate user.
	 *
	 * @return TRUE on sucess, FALSE on error
	 **/
	public function adminActivate($user_id, $approverId) {
		if ($this->ci->user_auth_model->adminActivation($user_id, $approverId) !== false) {
			return $this->confirmationEmail($this->ci->user_auth_model->getEmail($user_id),$this->ci->user_auth_model->getUsername($user_id));
		} else {
			return false;
		}
	}
	/**
	 * Change password.
	 *
	 * @return TRUE on sucess, FALSE on error
	 **/
	public function change_password($identity, $old, $new) {
        return $this->ci->user_auth_model->change_password($identity, $old, $new);
	}
	
	/**
	 * Deactivate user.
	 *
	 * @return TRUE on sucess, FALSE on error
	 **/
	public function deactivate($code) {
	    return $this->ci->user_auth_model->deactivate($code);
	}
	/**
	 * Administrator Activate user.
	 *
	 * @return TRUE on sucess, FALSE on error
	 **/
	public function adminDeactivate($user_id, $approverId) {
		if ($this->ci->user_auth_model->adminDeactivation($user_id, $approverId) !== false) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * forgotten password feature
	 *
	 * @return TRUE on sucess, FALSE on error
	 **/
	public function forgotten_password($email,$debug) {
		
		$forgotten_password = $this->ci->user_auth_model->forgotten_password($email);
		
		if ($forgotten_password) {
			// Get user information.
			if ($this->ci->user_auth_model->checkEmail($email)) {
				$this->ci->user_auth_model->load($email,'email',true);
	
				$data = array('forgotten_password_code' => $this->ci->user_auth_model->passConfirmKey,
							  'siteName'=>$this->ci->params['config']['site_name'],
							  'verify_url'=>anchor('/user/forgotten_password_verify/', 'Verify this code to get your new password now'));
					
				$message = $this->ci->load->view($this->ci->config->item('email_templates').'forgotten_password', $data, true);
				
				return sendEmail($this->ci->user_auth_model->email,
							 $this->ci->user_auth_model->getEmail($this->ci->params['config']['primary_contact']),
							 $this->ci->params['config']['site_name']." Administrator",
				             $this->ci->params['config']['site_name'].' Forgotten Password - Verification',
							 $message,'','email_get_passrd_');
				
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	/**
	 * undocumented function
	 *
	 * @return TRUE on sucess, FALSE on error
	 **/
	public function forgotten_password_complete($code) {
	    $identity = $this->ci->config->item('session_auth');
	    //$profile = $this->ci->user_auth_model->profile($code);
		$fpc = $this->ci->user_auth_model->forgotten_password_complete($code);

		if ($fpc) {
			$this->ci->user_auth_model->load($fpc);
			$data = array('new_password' => $this->ci->user_auth_model->newPassword,'siteName'=>$this->ci->params['config']['site_name']);
            
			$message = $this->ci->load->view($this->ci->config->item('email_templates').'new_password', $data, true);
			
			return sendEmail($this->ci->user_auth_model->email,
							 $this->ci->user_auth_model->getEmail($this->ci->params['config']['primary_contact']),
							 $this->ci->params['config']['site_name']." Administrator",
				             $this->ci->params['config']['site_name'].', New Password',
							 $message,'','email_get_pass_verify_');
			
		} else {
			return false;
		}
	}
	public function get_auth_value($key = false) {
		echo("get_auth_value, key = ".$key."<br />");
		if ($key === false) {
	        return false;
	    }
		echo("this->ci->user_auth_model->$key = ".$this->ci->user_auth_model->$key."<br />");
		return $this->ci->user_auth_model->$key;
	}
	public function get_status_code() {
		if ($this->ci->user_auth_model->errorCode != -1) {
			return $this->ci->user_auth_model->errorCode;
		} else if ($this->ci->user_meta_model->errorCode != -1) {
			return $this->ci->user_meta_model->errorCode;
		}
	}
	public function get_status_message() {
		if ($this->ci->user_auth_model->statusMess != '') {
			return $this->ci->user_auth_model->statusMess;
		} else if ($this->ci->user_meta_model->statusMess != '') {
			return $this->ci->user_meta_model->statusMess;
		}
	}
	/**
	 * register
	 *
	 * @return TRUE on sucess, FALSE on error
	 **/
	public function register($formInput,$debug = false) {
	    
		$register = false;
		if ($formInput->post('firstName') && $formInput->post('lastName')) {
			$this->ci->user_auth_model->name = $formInput->post('firstName')." ".$formInput->post('lastName');
		}
		if ($register = $this->ci->user_auth_model->register($formInput)) {
			$this->ci->user_meta_model->userId = $this->ci->user_auth_model->id;
			if ($this->ci->user_meta_model->applyData($formInput)) {
				$register = $this->ci->user_meta_model->save();
			} 
			$register = true;
		} else {
			$register = false;
		} // END if
		
		if (!$register) { return false; } // END if
		
		$deactivate = $this->ci->user_auth_model->deactivate($formInput->post('username'));

		if (!$deactivate) { return false; } // END if

		$activation_code = $this->ci->user_auth_model->emailConfirmKey;
		
		$email_activation = ($this->ci->params['config']['user_activation_method'] != -1) ? true : false;
		
		if (!$email_activation) {
			if ($this->ci->user_auth_model->activate($activation_code)) {
				return $this->confirmationEmail($formInput->post('email'),$formInput->post('username'),$debug);
			} else {
				return false;
			}
		} else {
			$email_folder = $this->ci->config->item('email_templates');
			$this->ci->email->clear();
			$this->ci->email->set_newline("\r\n");
			
			if ($this->ci->params['config']['user_activation_method']== 1) {
				$activation_code = $this->ci->user_auth_model->emailConfirmKey;
	
				$data = array('siteName'=>$this->ci->params['config']['site_name'],
						'username' => $formInput->post('username'),
						'password'   => $formInput->post('password'),
						'email'      => $formInput->post('email'),
						'activation' => $activation_code);
				
				$message = $this->ci->load->view($email_folder.'activation', $data, true);
				$to = $formInput->post('email');
				$subject = $this->ci->params['config']['site_name'].' Registration - Activation Required';
			} else {
				$this->ci->user_auth_model->activate($activation_code, true);
				$data = array('siteName'=>$this->ci->params['config']['site_name'],
						'username' => $formInput->post('username'),
						'email'    => $formInput->post('email'));
				$message = $this->ci->load->view($email_folder.'admin_activation', $data, true);
				$to = $this->ci->user_auth_model->getEmail($this->ci->params['config']['primary_contact']);
				$subject = $this->ci->params['config']['site_name'].' User Activation Required';
			} // END if
			
			return sendEmail($to,$this->ci->user_auth_model->getEmail($this->ci->params['config']['primary_contact']), $this->ci->params['config']['site_name']." Administrator",
				             $subject,$message,'','email_register_');
			
		} // END if
	}
	public function resend_activation($email,$debug = false) {
		
		$forgotten_activation = $this->ci->user_auth_model->forgotten_activation($email);
		
		if (!empty($forgotten_activation)) {
			$data = array('siteName'=>$this->ci->params['config']['site_name'],
					'username' => $this->ci->user_auth_model->username,
       				'activation' => $forgotten_activation);
            
			$message = $this->ci->load->view($this->ci->config->item('email_templates').'activation', $data, true);
			$subject = $this->ci->params['config']['site_name'].' Activation Code Request';
			
			return sendEmail($email,$this->ci->user_auth_model->getEmail($this->ci->params['config']['primary_contact']), $this->ci->params['config']['site_name']." Administrator",
				  $subject,$message,'','email_get_activate_resend_');
			
		} else {
			return false;
		}
		
	}
	protected function confirmationEmail($email, $username,$debug = false) {

		$data = array('siteName'=>$this->ci->params['config']['site_name'],
					  'username' => $username,
					  'email'=> $email);
		$message = $this->ci->load->view($this->ci->config->item('email_templates').'reg_confirmation', $data, true);
		
		return sendEmail($email,$this->ci->user_auth_model->getEmail($this->ci->params['config']['primary_contact']), $this->ci->params['config']['site_name']." Administrator",
				  $this->ci->params['config']['site_name'].' Registration Confirmation',$message,$username,'email_reg_confirm_');
	}
	
	/**
	 * load user
	 *
	 * @return TRUE on sucess, FALSE on error
	 **/
	public function load_user() {
		$session  = $this->ci->config->item('session_auth');
	    $identity = $this->ci->session->userdata($session);
		$field = '';
		if ($session != 'id') { 
			return $this->ci->user_auth_model->load($identity,$session);
		} else {
			return $this->ci->user_auth_model->load($identity);
		}
	}
	
	/**
	 * login
	 *
	 * @return TRUE on sucess, FALSE on error
	 **/
	public function login($identity, $password) {
		return $this->ci->user_auth_model->login($identity, $password);
	}
	
	/**
	 * logout
	 *
	 * @return TRUE on sucess, FALSE on error
	 **/
	public function logout($endSession = false) {
	    $identity = $this->ci->config->item('session_auth');
	    $this->ci->session->unset_userdata($identity);
		$this->ci->user_auth_model->logout();
		if ($endSession) {
			$this->ci->session->sess_destroy();
		}
	}
	
	/**
	 * logged_in
	 *
	 * @return TRUE on sucess, FALSE on error
	 **/
	public function logged_in(){
	    $identity = $this->ci->config->item('session_auth');
		return ($this->ci->session->userdata($identity)) ? true : false;
	}
	
	/**
	 * Account Details
	 *
	 * @return TRUE on sucess, FALSE on error
	 **/
	public function accountDetails() {
	    $session  = $this->ci->config->item('session_auth');
	    $identity = $this->ci->session->userdata($session);
	    return $this->ci->user_auth_model->accountDetails($identity);
	}
	
	/**
	 * Profile
	 *
	 * @return TRUE on sucess, FALSE on error
	 **/
	public function profile() {
	    $session  = $this->ci->config->item('session_auth');
	    $identity = $this->ci->session->userdata($session);
	    return $this->ci->user_auth_model->profile($identity);
	}
	
}
