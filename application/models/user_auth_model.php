<?php
/**
 *	USER AUTH MODEL CLASS.
 *	This class is the primary user identity and authorization model. 
 *	It provides functions to register, login, logout, activate and reset 
 *	passwords.
 *
 *	@author			Jeff Fox <jfox015 (at) gmail (dot) com>
 *  @copyright   	(c)2009-11 Jeff Fox/Aeolian Digital Studios
 *	@version		1.0
 *
*/
class user_auth_model extends base_model {

	/*--------------------------------
	/	VARIABLES
	/-------------------------------*/
	/**
	 *	SLUG.
	 *	@var $_NAME:Text
	 */
	var $_NAME = 'user_auth_model';
	
	var $username = '';
	var $password = '';
	var $newPassword = '';
	var $email = '';
	var $dateCreated = EMPTY_DATE_TIME_STR;
	var $dateModified = EMPTY_DATE_TIME_STR;
	var $lastModifiedBy = -1;
	var $active = 0;
	var $accessId = 1;
	var $levelId = 1;
	var $typeId = 0;
	var $locked = 0;
	var $loginAttempts = 0;
	var $loggedIn = 0;
	/**
	 * activation code
	 *
	 * @var string
	 **/
	public $emailConfirmKey;
	
	/**
	 * forgotten password key
	 *
	 * @var string
	 **/
	public $passConfirmKey;
	/**
	 * Table access setting
	 *
	 * @var tblAccess
	 **/
	var $tblAccess = '';
	
	/*--------------------------------------
	/	C'TOR
	/	Creates a new instance of user_meta_model
	/-------------------------------------*/
	function user_auth_model() {
		parent::__construct();
		
		$this->tblName = $this->tables['users_core'];
		$this->tblAccess = $this->tables['access_log'];

		$this->fieldList = array('username','email','typeId','levelId','accessId');
		$this->conditionList = array('newPassword','lockStatus','loginAttemptCount');
		$this->readOnlyList = array('password','dateCreated','dateModified','lastModifiedBy',
									'locked','loginAttempts','loggedIn','active');  
		
		$this->uniqueField = $this->config->item('unique_field');
		$this->joinCode = "A";
		parent::_init();
	}
	/**
	 * account details
	 *
	 * @return void
	 **/
	public function accountDetails($identity = false) {
	    if ($identity === false) {
	        return false;
	    }
	    $meta_table  = $this->tables['users_meta'];
	    $meta_join   = $this->config->item('join');
	    
		$this->db->select($this->fieldsToSQL(false,false));
		$this->db->from($this->tblName);
		$this->db->where($this->tblName.'.id', $identity);
		$this->db->limit(1);
		//echo($this->db->last_query()."<br />");
		$i = $this->db->get();
		
		return ($i->num_rows > 0) ? $i->row() : false;
	}
	/**
	 * activate
	 *
	 * @return void
	 *
	 */
	public function activate($code = false, $leaveInactive = false) {
	    
	    if ($code === false) {
	        $this->errorCode = 1;
			$this->statusMess = "A required validation code was missing.";
	        return false;
	    }
	    $query = $this->db->select($this->uniqueField)
               	      ->where('emailConfirmKey', $code)
               	      ->limit(1)
               	      ->get($this->tblName);
               	      
		$result = $query->row();
		if ($query->num_rows() !== 1) {
		    $this->errorCode = 2;
			$this->statusMess = "No matching activation code was found in the system.";
	        return false;
		}
	    
		$identity = $result->{$this->uniqueField};
		$active = ($leaveInactive === false) ? 1 : 0;
		$data = array('emailConfirmKey' => '','active' => $active, 'dateModified' => date('Y-m-d h:m:s'));
		$this->db->update($this->tblName, $data, array($this->uniqueField => $identity));
		return ($this->db->affected_rows() == 1) ? true : false;
	}
	
	public function adminActivation($userId = false, $approvedBy = false) {
		
		if ($userId === false) {
			$this->errorCode = 1;
			$this->statusMess = "No user id was recieved.";
	        return false;
	    }
		if ($approvedBy === false) {
			$this->errorCode = 1;
			$this->statusMess = "An approver ID is required but none was recieved.";
	        return false;
	    }
		$query = $this->db->select($this->uniqueField)
               	      ->where('id', $userId)
               	      ->limit(1)
               	      ->get($this->tblName);
               	      
		$result = $query->row();
		if ($query->num_rows() !== 1) {
		    $this->errorCode = 3;
			$this->statusMess = "No matching user id was found in the system.";
	        return false;
		}
		$identity = $result->{$this->uniqueField};
		$data = array('active' => 1, 'dateModified' => date('Y-m-d h:m:s'), 'lastModifiedBy' => $approvedBy);
		$this->db->update($this->tblName, $data, array($this->uniqueField => $identity));
		return ($this->db->affected_rows() == 1) ? true : false;
	}
	/**
	 * applyData
	 *
	 * @return void
	 *
	 */
	public function applyData($input,$userId = -1) {
		if (parent::applyData($input,$userId)) {
			$this->dateModified = date('Y-m-d h:m:s');
			if ($userId != -1) {
				$this->lastModifiedBy = $userId;
			} // END if
			if ($this->id == -1 && $input->post('password')) {
				$this->password = $this->hashPassword($input->post('password'));
			} else {
				if ($input->post('newPassword')) {
					$this->password = $this->hashPassword($input->post('newPassword'));
				} // END if
			} // END if
			if ($input->post('lockStatus')) {
				$this->accountLockStatus = $input->post('lockStatus');
			} // END if
			if ($input->post('loginAttemptCount')) {
				$this->loginAttempts = $input->post('loginAttemptCount');
			} // END if
			return true;
		} else {
			return false;
		} // END if
	}
	/**
	 * change password
	 *
	 * @return void
	 **/
	public function change_password($identity = false, $old = false, $new = false) {
	    
	    if ($identity === false || $old === false || $new === false) {
	        $this->errorCode = 1;
			$this->statusMess = "Required fields were missing values.";
	        return false;
	    }

	    $query  = $this->db->select('password')
                   	   ->where('id', $identity)
                   	   ->limit(1)
                   	   ->get($this->tblName);
                   	   
	    $result = $query->row();
		if ($result) {
			
			$db_password = $result->password; 
			$old         = $this->hashPassword($old);
			$new         = $this->hashPassword($new);
			
			if ($db_password === $old) {
				$data = array('password' => $new);
				
				$this->db->update($this->tblName, $data, array('id' => $identity));
				
				return ($this->db->affected_rows() == 1) ? true : false;
			} else {
				$this->errorCode = 2;
				$this->statusMess = "The current password entered does not match the password on record.";
				return false;
			}
		} else {
			$this->errorCode = 3;
			$this->statusMess = "The current password entered does not match the password on record.";
			return false;
		}
	}
	/**
	 * Checks username.
	 *
	 * @return void
	 * @author Mathew
	 **/
	public function checkUsername($username = false) {
	    if ($username === false) {
	        return false;
	    }
	    $query = $this->db->select('id')
                           ->where('username', $username)
                           ->limit(1)
                           ->get($this->tblName);
		
		if ($query->num_rows() == 1) {
			return true;
		}
		return false;
	}
	
	/**
	 * Checks email.
	 *
	 * @return void
	 * @author Mathew
	 **/
	public function checkEmail($email = false) {
	    if ($email === false) {
	        return false;
	    }
	    $query = $this->db->select('id')
                           ->where('email', $email)
                           ->limit(1)
                           ->get($this->tblName);
		
		if ($query->num_rows() == 1) {
			return $query->row()->id;
		}
		return false;
	}
	/**
	 * Deactivate
	 *
	 * @return void
	 * @author Mathew
	 **/
	public function deactivate($username = false) {
	    if ($username === false) {
	        return false;
	    }
	    
		$emailConfirmKey = sha1(md5(microtime()));
		$this->emailConfirmKey = $emailConfirmKey;
		
		$data = array('emailConfirmKey' => $emailConfirmKey);
        
		$this->db->update($this->tblName, $data, array('username' => $username));
		
		return ($this->db->affected_rows() == 1) ? true : false;
	}
	public function forgotten_activation($email = false) {
	 	
		
		$activation_code = '';
		if ($email === false) {
	        $this->errorCode = 1;
			$this->statusMess = "A required email address was missing.";
	        return false;
	    }
	    $query = $this->db->select('active, emailConfirmKey')
               	      ->where('email', $email)
               	      ->limit(1)
               	      ->get($this->tblName);		 
		if ($query->num_rows() == 0) {
		    $this->errorCode = 2;
			$this->statusMess = "No matching email address was found in the system.";
	        return false;
		} else {
			$row = $query->row();
			if ($row->active == 1) {
				$this->errorCode = 3;
				$this->statusMess = "This account has already been activated.";
				return false; 
			} else {
				$activation_code = $row->emailConfirmKey;
				if (empty($activation_code)) {
					$this->errorCode = 4;
					$this->statusMess = "No activation code is pending for this account.";
					return false;  
				}
			}
		}
		$query->free_result();
		return $activation_code;
	}
	/**
	 * Insert a forgotten password key.
	 *
	 * @return void
	 * @author Mathew
	 **/
	public function forgotten_password($email = false) {
	    
	    if ($email === false) {
	        return false;
	    }
	    $query = $this->db->select('passConfirmKey')
                   	   ->where('email', $email)
                   	   ->limit(1)
                   	   ->get($this->tblName);
            
        $result = $query->row();
		
		$code = '';
		if ($result) {
			$code = $result->passConfirmKey;
		} else {
			$this->errorCode = 1;
			$this->statusMess = "No record was found for the email ".$email.".";
			return false;
		}
		//echo('code = '.$code."<br />");
		if (empty($code)) {
			$key = substr($this->hashPassword(microtime().$email),0,16);
			
			$this->passConfirmKey = $key;
		
			$data = array('passConfirmKey' => $key);
			
			$this->db->update($this->tblName, $data, array('email' => $email));		
			return ($this->db->affected_rows() == 1) ? true : false;
		} else {
			$this->errorCode = 2;
			$this->statusMess = "A password reset request has already been sent to ".$email.".";
			return false;
		}
	}
	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Mathew
	 **/
	public function forgotten_password_complete($code = false, $debug = false) {
	      
	    if ($code === false) {
			$this->errorCode = 1;
			$this->statusMess = "The required password reset confirmation code was missing.";
	        return false;
	    }
	    $this->db->select('id');
        $this->db->where('passConfirmKey', $code);
        $this->db->limit(1);
        $query = $this->db->get($this->tblName);
        
        $result = $query->row();

        if ($query->num_rows() > 0) {
			$this->load($result->id);
			$clearPw = substr($this->hashPassword(microtime().$this->email),0,12);
		    $password   = $this->hashPassword($clearPw);
            $data = array('password' => $password);
            
			$this->newPassword = $clearPw;
            $this->db->update($this->tblName, $data, array('id' => $result->id));
			
            return $result->id;
        } else {
			$this->errorCode = 2;
			$this->statusMess = "No password reset code matching the one entered were found in the system.";
			return false;
		}
	}
	public function getUsername($userId = false) {
		
		if ($userId === false) { $userId = $this->id; }
		$query = $this->db->select('username')
                   	   ->where('id', $userId)
                       ->limit(1)
                   	   ->get($this->tblName);
		$result = $query->row();

        if ($query->num_rows() > 0) {
			return $result->username;
		} else {
			$this->errorCode = 1;
			$this->statusMess = "No user matching id passeded was found in the system.";
			return false;
		}
	}
	public function getEmail($userId = false) {
		
		if ($userId === false) { $userId = $this->id; }
		$query = $this->db->select('email')
                   	   ->where('id', $userId)
                       ->limit(1)
                   	   ->get($this->tblName);
		$result = $query->row();

        if ($query->num_rows() > 0) {
			return $result->email;
		} else {
			$this->errorCode = 1;
			$this->statusMess = "No user matching id pass was found in the system.";
			return false;
		}
	}
	public function getAdmninUsers() {
		
		$query = $this->db->select('id,username')
                   	   ->where('accessId', ACCESS_ADMINISTRATE)
                   	   ->get($this->tblName);
		$result = $query->row();
		$users = array();
        if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$users = $users + array($row->id=>$row->username);
			}
			return $users;
			
		} else {
			$this->errorCode = 1;
			$this->statusMess = "No user matching id pass was found in the system.";
			return false;
		}
	}
	public function getAdminActivations() {
		
		$query = $this->db->select('id,username,email,dateCreated')
                   	   ->where('active', 0)
					   ->where("emailConfirmKey = ''")
                   	   ->get($this->tblName);
		$users = array();
		//print($this->db->last_query()."<br />");
        if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				array_push($users,array('id'=>$row->id,'username'=>$row->username,'email'=>$row->email,
										'dateCreated'=>$row->dateCreated));
			}
		} else {
			$this->errorCode = 1;
			$this->statusMess = "No users requiring admin activation were found in the system.";
		}
		return $users;
	}
	
	
	/**
	 *	LOGIN
	 *	Starts a new user session.
	 */
	public function login($login,$password) {
		$success = false;
		// Check the supplied email and password against database to see 
		// if the user exists.
		$this->db->select('id, password, active');
		$this->db->where($this->uniqueField,$login);
		$rsLogin = $this->db->get($this->tblName);
		if ($rsLogin->num_rows() > 0) {
			$row = $rsLogin->row();
			$hashPass = $this->hashPassword($password);
			//echo($hashPass."<br />");
			//echo($row->password."<br />");
			//echo($row->password === $hashPass ? "true" : "false"."<br />");
			if ($row->password === $hashPass) {
				//echo("Password OK"."<br />");
				if ($row->active == 1) {
					$success = $this->load($row->id);
					if (!$success) {
						$this->errorCode = 4;
						$err = "Load of user infomation failed.";
						$this->statusMess = $err." ".$this->statusMess;
					} else {					
						$this->statusMess = "Login Successful.";
						$session = $this->config->item('session_auth');
						$this->session->set_userdata($session,$row->$session);
						$this->setLoginStatus(1);
					} // END if
				} else {
					$this->errorCode = 3;
					$this->statusMess = "User account is not active.";
					$this->logFailedAccess($login);	
				}
			} else {
				//echo("Password failed"."<br />");
				$this->errorCode = 2;
				$this->statusMess = "Password incorrect.";
				$this->logFailedAccess($login);
			} // END if
		} else {
			$this->errorCode = 1;
			$this->statusMess = "Username not found.";
			$this->logFailedAccess($login);
		} // END if
		if ($this->config->item('log_auth')) {
			$this->logAccess($login,$success);
		}
		return $success;
	}
	/**
	 *	LOGOUT
	 *	Logs the user out of their session.
	 */
	public function logout() {
		$this->setLoginStatus(0);
	}
	
	
	/**
	 * profile
	 *
	 * @return void
	 * @author Mathew
	 **/
	public function profile($identity = false) {
	    if ($identity === false) {
	        return false;
	    }
	    $meta_table  = $this->tables['user_meta'];
	    $meta_join   = $this->config->item('join');
	    
		$this->db->select($this->fieldsToSQL(false,false));
		$columns = $this->config->item('columns');
		if (!empty($columns)) {
		    foreach ($columns as $value) {
   			$this->db->select($meta_table.'.'.$value);
   		}
		}
		$this->db->from($this->tblName);
		$this->db->join($meta_table, $this->tblName.'.id = '.$meta_table.'.'.$meta_join, 'left');
		
		if (strlen($identity) === 12) {
	        $this->db->where('passConfirmKey', $identity);
	    } else {
	        $this->db->where($this->tblName.'.id', $identity);
	    }
	    
		$this->db->limit(1);
		//echo($this->db->last_query()."<br />");
		$i = $this->db->get();
		
		return ($i->num_rows > 0) ? $i->row() : false;
	}
	/**
	 *	REGISRER
	 *	Registers a new user into the system.
	 *
	 */
	public function register($authInput) {

	    if ($authInput->post($this->uniqueField) === false || 
			$authInput->post('password') === false || 
			$authInput->post('email')    === false) {
	        $this->errorCode = 1;
			$this->statusMess = "Required fields were missing values.";
			return false;
	    }
        // Users table.
		if ($this->checkUsername($authInput->post('username'))) {
			$this->errorCode = 2;
			$this->statusMess = "The username ".$authInput->post('username')." is already in use.";
			return false;
		} else if ($this->checkEmail($authInput->post('email'))) {
			$this->errorCode = 3;
			$this->statusMess = "The email address ".$authInput->post('email')." is already in use.";
			return false;
		} else {
			$this->applyData($authInput);
			return $this->save();
		}
		return false;
	}
	public function setLoginStatus($status) {
		$this->db->set('loggedIn',$status);
		$this->db->where('id',$this->id);
		$this->db->update($this->tblName);
		$this->loggedIn = $status;
	}
	
	public function setLockStatus($username,$status=0,$attempts=0) {		
		$this->db->set('locked',$status);
		$this->db->set('loginAttempts',$attempts+1);
		$this->db->where('username',$username);
		$this->db->update($this->tblName);
		$this->accountLockStatus = $status;
	}
	
	/*---------------------------------------
	/	PRIVATE/PROTECTED FUNCTIONS
	/--------------------------------------*/
	/**
	 * 	Hash Password
	 *	Hashes the password to be stored in the database.
	 *
	 * 	@return	Hashed Password
	 *
	 **/
	protected function hashPassword($password = false) {
	    if (!function_exists('__hash')) {
			$this->load->helper('auth');
		}
		if ($password === false) {
	        return false;
	    }
		return __hash($password,$this->config->item('password_crypt'));
	}
	
	protected function logFailedAccess($username) {
		$attempts = 0;
		$locked = 0;
		$this->db->select('loginAttempts');
		$this->db->where('username',$username);
		$query = $this->db->get($this->tblName);
		if ($query->num_rows() > 0) {
			$attempts = $query->row()->loginAttempts;
		}
		$query->free_result();
		$attempts += 1;
		if ($attempts >= $this->config->item('login_attempt_max')) {
			$locked = 1;
		}
		$this->setLockStatus($username,$locked,$attempts);
	}
	
	protected function logAccess($username,$success = true,$source='loginBox') {
		
		$this->db->set('username',$username);
		$this->db->set('login',date('Y-m-d h:m:s',$this->session->userdata('last_activity')));
		$this->db->set('ipAddress',$this->session->userdata('ip_address'));
		$this->db->set('failedLogin',($success) ? 0 : 1);
		$this->db->set('sessionId',$this->session->userdata('session_id'));
		$this->db->set('loginSource',$source);
		$this->db->insert($this->tblAccess);

	}
	/**
	 * Generates a random salt value.
	 *
	 * @return void
	 * @author Mathew
	 **/
	protected function salt() {
		return substr(md5(uniqid(rand(), true)), 0, $this->config->item('salt_length'));
	}
}