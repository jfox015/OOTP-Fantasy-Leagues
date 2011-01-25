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
class MY_Controller extends Controller {

	/*--------------------------------
	/	VARIABLES
	/-------------------------------*/
	/**
	 *	SLUG alias for file.
	 *	@var	$_NAME:string
	 */
	var $_NAME = 'MY_Controller';
	/**
	 *	Templates array.
	 *	@var	$templates:Array
	 */
	var $templates = array();
	/**
	 *	Templates array.
	 *	@var	$templates:Array
	 */
	var $views = array();
	/**
	 *	Data array.
	 *	Array of data values that is passed to child views
	 *	before displayView() is called.
	 *	@var	$data:Array
	 */
	var $data = array();
	/**
	 *	Params array.
	 *	Array of parameters passed to the main display 
	 *	template via the displayView() function
	 *	@var	$params:Array
	 */
	var $params = array();
	/**
	 *	Themes array.
	 *	Stores a list of the available themes.
	 *	@var	$themes:Array
	 */
	var $themes = array();
	/**
	 *	URI Vars array.
	 *	Ann array of query string values passed via the uri string
	 *	@var	$uriVars:Array
	 */
	var $uriVars = array();
	/**
	 *	RESTRICT ACCESS.
	 *	Set this value to TRUE to invoke access verification routines
	 *	@var	$restrictAccess:Boolean
	 */
	var $restrictAccess = false;
	/**
	 *	MINIMUM ACCESS LEVEL.
	 *	Use in conjunction with $restrictAccess. Set the minimum level a 
	 *	user must be set to in order to gain access to restricted content.
	 *	@var	$minAccessLevel:Int
	 */
	var $minAccessLevel = ACCESS_READ;
	/*--------------------------------
	/	C'TOR
	/-------------------------------*/
	/**
	 *	Creates a new instance of MT_Portal.
	 */
	function MY_Controller() {
		parent::Controller();
		$this->init();
	}
	/*--------------------------------
	/	PROTECTED FUNCTIONS
	/-------------------------------*/
	/**
	 *	INIT.
	 *	Tests for a valid login session auth ID (User Id).
	 *	If not found, the user is redirected to the 
	 */
	protected function init() {
		$this->params['scripts'] = array();
		$this->params['styles'] = array();
		
		$this->templates['MAIN'] = TEMPLATE_DEFAULT;
		$this->templates['FORM'] = '';
		$this->templates['DATA_TABLE'] = '';
		
		$this->views['PENDING'] = 'content_pending';
		$this->views['STATS_TABLE'] = 'stats_table';
		$this->views['TRANSACTION_SUMMARY'] = 'transaction_summary';
		
		// GET USER DATA IF LOGGED IN
		$this->params['loggedIn'] = $this->auth->logged_in();
		$this->params['name'] = '';
		$this->params['currUser'] = -1;
		$this->params['accessLevel'] = 1;
		$this->params['userTeams'] = array();
		if ($this->params['loggedIn']) {
			if ($this->auth->load_user()) {
				$this->params['name'] = $this->user_auth_model->username;
				$this->params['currUser'] = (!empty($this->user_auth_model->id)) ? $this->user_auth_model->id : -1;
				$this->params['accessLevel'] = $this->user_auth_model->accessId;
				$this->params['userTeams'] = $this->user_meta_model->getUserTeams(false, $this->params['currUser']);
			} // END if
		} // END if
		// APPLY GLOBAL USER VARS TO VIEW DATA VARS
		$this->data['name'] = $this->params['name'];
		$this->data['accessLevel'] = $this->params['accessLevel'];
		$this->data['loggedIn'] = $this->params['loggedIn'];
		$this->data['currUser'] = $this->params['currUser'];
		$this->data['userTeams'] = $this->params['userTeams'];
		
		// LOAD theme support
		$this->themes = $this->config->item('themes');
		$this->params['theme'] = $this->themes['current'];
		
		$this->data['message'] = '';
		
		$this->params['title'] = $this->lang->line('site_name');
		$this->params['tag_line'] = $this->lang->line('tag_line');
		
		$this->load->helper('config');
		$this->params['config'] = $this->data['config'] = load_config();
		
		if (!isset($this->ootp_league_model)) {
			$this->load->model('ootp_league_model');
		} // END if
		if ($this->ootp_league_model->load($this->params['config']['ootp_league_id'],'league_id')) {
			$this->params['league_info'] = $this->ootp_league_model;
			$this->data['league_info'] = $this->ootp_league_model;
		}  // END if//else {
			//$this->data['message'] = "OOTP League load error. Code: ".$this->ootp_league_model->errorCode.", ".$this->ootp_league_model->statusMess;
		//}
		$this->params['subNavSection'] = array(top_nav($this->params['loggedIn'],$this->data['accessLevel'] == ACCESS_ADMINISTRATE,$this->params['userTeams']));
		
		$this->params['pageType'] = PAGE_NORMAL;
		$this->params['update_message'] = '';
		if (strpos(current_url(),'dashboard') === false) { 
			if (((!defined('ENV') || (defined('ENV') && ENV != 'dev')) && 
				defined('PATH_INSTALL')) && $this->params['accessLevel'] == ACCESS_ADMINISTRATE) {
				
				if (defined('MAIN_INSTALL_FILE') && file_exists(PATH_INSTALL.MAIN_INSTALL_FILE)) {
					$this->params['installWarning'] = true;
					$this->params['install_message'] = $this->lang->line('install_warning');
				} // END if
				if (defined('DB_CONNECTION_FILE') && !file_exists($this->params['config']['sql_file_path']."/".DB_CONNECTION_FILE)) {
					$this->params['dbConnectError'] = true;
					$this->params['dbConnect_message'] = $this->lang->line('dbConnect_message');
				} // END if
				if (defined('DB_UPDATE_FILE') && file_exists(PATH_INSTALL.DB_UPDATE_FILE)) {
					$this->params['dataUpdate'] = true;
				} // END if
				if ((defined('CONFIG_UPDATE_FILE') && file_exists(PATH_INSTALL.CONFIG_UPDATE_FILE)) || 
					(defined('CONSTANTS_UPDATE_FILE') && file_exists(PATH_INSTALL.CONSTANTS_UPDATE_FILE)) ||
					(defined('DATA_CONFIG_UPDATE_FILE') && file_exists(DATA_CONFIG_UPDATE_FILE))) {
					$this->params['configUpdate'] = true;
				} // END if
				if (isset($this->params['$dataUpdate']) || isset($this->params['configUpdate'])) {
					$this->params['update_message'] = $this->lang->line('update_required');
				} // END if
				
			} // END if
		} // END if
	}
	/**
	 *	DISPLAY VIEW.
	 *	Sets the most common view data values and then calls the 
	 *	view template specified by $this->_MAIN_TEMPLATE.
	 *
	 */
	protected function displayView($templateName = '') {
		//echo("Sub nav size = ".sizeof($this->params['subNavSection'])."<br />");
		$viewTpl = (!empty($templateName)) ? $templateName : $this->templates['MAIN'];
		$this->load->view($viewTpl,$this->params,false);
	}
	/**
	 *	ENQUE SCRIPT.
	 *	Sets a script to the queue to be rendered when the tempate displays
	 *
	 */
	protected function enqueScript($script) {
		if ( isset($this->params['scripts'][$script]) )
			return false;
		array_push($this->params['scripts'],$script);
	}
	/**
	 *	ENQUE STYLE.
	 *	Sets a csss tylesheet to the queue to be rendered when the tempate displays
	 *
	 */
	protected function enqueStyle($style) {
		if ( isset($this->params['styles'][$style]) )
			return false;
		array_push($this->params['styles'],$style);
	}
	/**
	 *	GET URI DATA.
	 *	Parses out an id or other parameters from the uri string
	 *
	 */
	protected function getURIData() {
		if ($this->uri->total_segments() == 3) {
			$this->uriVars['id'] = $this->uri->segment(3);
		} else {
			$this->uriVars = $this->uri->uri_to_assoc(3);
		}
	}
	/**
	 *	IS AJAX.
	 *	Determines if a request is coming from HTTP or AJAX 
	 *
	 */
	public function isAjax() {
   		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']=="XMLHttpRequest");
	} 
}
/* End of file BaseController.php */
/* Location: ./application/controllers/BaseController.php */