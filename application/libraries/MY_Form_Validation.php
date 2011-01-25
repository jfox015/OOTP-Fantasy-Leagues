<?php
/**
 *	MY_Controller.
 *	Static base class for all Conrollers.
 *	Provides a minimum of services and necessary functionality.
 *	This includes base template support, script and style queing,
 *	storage of URL passed variable data and basic user authentication
 *	checking.
 *	
 *	@author			Jeff Fox
 *	@version		1.0.2
 *	@dateCreated	10/4/09
 *	@lastModified	10/11/09
 *  @copyright   	(c)2009-10 Jeff Fox/Aeolian Digital Studios
 */
class MY_Form_validation extends CI_Form_validation {

	/*--------------------------------
	/	VARIABLES
	/-------------------------------*/
	
	/*--------------------------------
	/	C'TOR
	/-------------------------------*/
	/**
	 *	Creates a new instance of MT_Portal.
	 */
	public function MY_Form_validation() {
		parent::CI_Form_validation();
	}
	/*--------------------------------
	/	PROTECTED FUNCTIONS
	/-------------------------------*/
	/**
	 *	INIT.
	 *	Tests for a valid login session auth ID (User Id).
	 *	If not found, the user is redirected to the 
	 */
	public function setError($field,$message,$prefix='',$postfix='') {
		$this->_error_array[$field] = $prefix.$message.$postfix;
	}
}
/* End of file BaseController.php */
/* Location: ./application/controllers/BaseController.php */