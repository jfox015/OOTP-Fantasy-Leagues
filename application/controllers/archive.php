<?php
/**
 *	Archive.
 *	The primary controller for the Archive Section.
 *	@author			Jeff Fox
 *	@dateCreated	11/13/09
 *
 */
class archive extends MY_Controller {

	/*--------------------------------
	/	VARIABLES
	/-------------------------------*/
	
	var $_NAME = 'archive';
	/*--------------------------------
	/	C'TOR
	/-------------------------------*/
	/**
	 *	Creates a new instance of archive.
	 */
	public function archive() {
		parent::MY_Controller();	
		$this->views['PENDING'] = 'content_pending';
		
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
		$this->params['content'] = $this->load->view($this->views['PENDING'], $this->data, true);
	    $this->displayView();
	}
}
/* End of file archive.php */
/* Location: ./application/controllers/archive.php */