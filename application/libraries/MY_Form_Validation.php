<?php
/**
 *	MY_Form_validation.
 *	Custom override to the standard cideigniter form validation class.
 *	Add custom error message setting and date validation.
 *	
 *	@author			Jeff Fox
 *	@version		1.0.3
 *	@dateCreated	10/4/09
 *	@lastModified	5/10/11
 *  @copyright   	(c)2009-11 Jeff Fox/Aeolian Digital Studios
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
	/	PUBLIC FUNCTIONS
	/-------------------------------*/
	/**
	 *	INIT.
	 *	Tests for a valid login session auth ID (User Id).
	 *	If not found, the user is redirected to the 
	 */
	public function setError($field,$message,$prefix='',$postfix='') {
		$this->_error_array[$field] = $prefix.$message.$postfix;
	}
	
	/**
     * 	Valid Date (Short mm/dd/yyyy format)
     *
     * 	@access    public
     * 	@param    string
     * 	@return    bool
	 *	@author		wdm* - CodeIgnighter Forums
     */
    public function valid_short_date($str) {
        if (substr_count($str, '/') == 2) {
            $arr = explode("/", $str);    // splitting the array
            $yyyy = $arr[2];            // first element of the array is year
            $mm = $arr[0];              // second element is month
            $dd = $arr[1];              // third element is days
            return ( checkdate($mm, $dd, $yyyy) );
        } 
        else 
        {
            return FALSE;
        }
    }
}
/* End of file BaseController.php */
/* Location: ./application/controllers/BaseController.php */