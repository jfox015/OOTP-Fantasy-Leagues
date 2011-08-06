<?php
/**
 *	SEARCH.
 *	The primary search controller for the site.
 *	
 *	All search requests are handled by this controller. It assembles the search data and 
 *	conditions provided by the user, then hands off that data to the query processing 
 *	capabilities built into the base_model class. The output of the base model search query 
 *	is then returned and routed to the approriate search view class (specified in this classes 
 *	$this->views Array).
 *
 *	@author			Jeff Fox
 *	@dateCreated	09/03/09
 *	@lastModified	08/09/10
 *	@see			models/base_model.php
 */

class search extends MY_Controller {

	/*--------------------------------
	/	VARIABLES
	/-------------------------------*/
	var $_TARGET_WEB = 0;
	var $_TARGET_DATA_OUT = 1;
	/**
	 *	SLUG.
	 *	@var $_NAME:Text
	 */
	var $_NAME = 'search';
	/**
	 *	Search Object.
	 *	@var $searchObj:Object
	 */
	var $searchObj = NULL;
	/**
	 *	Text Search.
	 *	Speccified a textbased search.
	 *	@var $textSearch:Boolean
	 */
	var $textSearch = false;
	/**
	 *	Alpha Search.
	 *	Speccified an alphabet letter based search.
	 *	@var $alphaSearch:Boolean
	 */
	var $alphaSearch = false;
	/**
	 *	Default Sort.
	 *	The default sort column in the db
	 *	@var $defaultSort:Text
	 */
	var $defaultSort = 'id';
	/**
	 *	Default Sort Order.
	 *	'asc' or 'desc'
	 *	@var $defaultSortOrder:Text
	 */
	var $defaultSortOrder = 'asc';
	/**
	 *	META FILTERS.
	 *	Array of code-only filter variables.
	 *	@var $metaFilters:Array
	 */
	var $metaFilters = array();
	/**
	 *	FILTER VARS.
	 *	Array of user defined search filter variables.
	 *	@var $filterVars:Array
	 */
	var $filterVars = array();
	/**
	*	OUTPUT TARGET.
	*	Determines to display results in search results page or export format.
	*	@var $outTarget:String
	*	@since 1.0.6
	*/
	var $outTarget = -1;
	/**
	*	OUTPUT TEMPLATE.
	*	Accepts a template for output the data from search results.
	*	@var $outTemplate:String
	*	@since 1.0.6
	*/
	var $outTemplate = '';
	/**
	 *	TABLE JOINS.
	 *	Array of table to join into the current result set.
	 *	@var $tableJoins:Array
	 *	@since 1.0.6
	 */
	var $tableJoins = array();
	/*---------------------------------------
	/
	/	SITE SPECIFIC METHODS
	/
	/--------------------------------------*/
	// 	Change the data in the following methods to match the setup and search requirements of the site that
	// 	that is using this workflow.
	
	/**
	 *	DEFINE SEARCH RESULTS VIEW
	 *	List all views for searches in this site
	 */
	function defineSearchResultsViews() {
		$this->views['RESULTS_BUGS'] = DIR_VIEWS_BUGS.'search_results_bug';
		$this->views['RESULTS_PROJECTS'] = DIR_VIEWS_PROJECTS.'search_results_project';
		$this->views['RESULTS_LEAGUES'] = DIR_VIEWS_LEAGUES.'search_results_leagues';
		$this->views['RESULTS_USERS'] = DIR_VIEWS_USERS.'search_results_users';
		$this->views['RESULTS_NEWS'] = DIR_VIEWS_NEWS.'search_results_news';
		$this->views['RESULTS_MEMBERS'] = DIR_VIEWS_MEMBERS.'search_results_member';
	}
	/**
	 *	QUERY DATA BUILDER
	 *	This method switches on the passed search ID and constructs all the relevant data objects 
	 *	needed by the base_model to construct and execute the search.
	 */
	function queryDataBuilder() {
		switch ($this->uriVars['id']) {
			// LEAGUES
			case 'league':
			case 'leagues':
				$this->load->model('league_model','dataModel');
				$this->data['subTitle'] = 'Leagues';
				$this->data['searchType'] = 'leagues';
				if (isset($this->uriVars['filterAction']) && $this->uriVars['filterAction'] == 'search') {
					if ($this->input->post('league_type'))
						$this->uriVars['league_type'] = $this->input->post('league_type'); // END if
					if (isset($this->uriVars['league_type']) && !empty($this->uriVars['league_type'])) 
						$this->filterVars = $this->filterVars + array('league_type'=>$this->uriVars['league_type']); // END if
					if ($this->input->post('access_type'))
						$this->uriVars['access_type'] = $this->input->post('access_type'); // END if
					if (isset($this->uriVars['access_type']) && !empty($this->uriVars['access_type'])) 
						$this->filterVars = $this->filterVars + array('access_type'=>$this->uriVars['access_type']); // END if
				} // END if
				$this->defaultSort = 'league_name';
				$this->defaultSortOrder = "desc";
				$this->data['sortFields'] = array('league_name'=>'League Name',				  
							   'league_type'=>'Scoring Type',
							   'max_teams'=>'Teams',
							   'access_type'=>"Public/Private");
				break;
			// USERS
			case 'user':
			case 'users':
				if (!function_exists('timespan')) {
					$this->load->helper('date');
				}
				$this->load->model('user_meta_model','dataModel');
				$this->data['subTitle'] = 'Users';
				$this->data['searchType'] = 'users';
				$this->defaultSort = 'lastName';
				array_push($this->tableJoins,array('table'=>USER_CORE_TABLE,'field'=>'id','joinField'=>'userId'));
				$this->metaFilters = $this->metaFilters + array('users_core.active'=>1); // END if
				if (isset($this->uriVars['filterAction']) && $this->uriVars['filterAction'] == 'search') {
					if ($this->input->post('country'))
						$this->uriVars['country'] = $this->input->post('country'); // END if
					if (isset($this->uriVars['country']) && !empty($this->uriVars['country'])) 
						$this->filterVars = $this->filterVars + array('country'=>$this->uriVars['country']); // END if
				} // END if
				$this->data['sortFields'] = array('lastName'=>'Name',				  
							   'dateCreated'=>'Date Added',
							   'dateModified'=>'Latest Updated');
				$this->textSearch = true;
				$this->alphaSearch = true;
				break;
			// MEMBERS
				case 'member':
				case 'members':
				   	$this->load->model('user_auth_model','dataModel');
					$this->data['subTitle'] = 'Members';
					$this->data['searchType'] = 'members';
					if (isset($this->uriVars['filterAction']) && $this->uriVars['filterAction'] == 'search') {
						if ($this->input->post('accessId'))
							$this->uriVars['accessId'] = $this->input->post('accessId'); // END if
						if (isset($this->uriVars['accessId']) && !empty($this->uriVars['accessId'])) 
							$filterVars = $filterVars + array('accessId'=>$this->uriVars['accessId']); // END if
						if ($this->input->post('levelId'))
							$this->uriVars['levelId'] = $this->input->post('levelId'); // END if
						if (isset($this->uriVars['levelId']) && !empty($this->uriVars['levelId'])) 
							$filterVars = $filterVars + array('levelId'=>$this->uriVars['levelId']); // END if
						if ($this->input->post('typeId'))
							$this->uriVars['typeId'] = $this->input->post('typeId'); // END if
						if (isset($this->uriVars['typeId']) && !empty($this->uriVars['typeId'])) 
							$filterVars = $filterVars + array('typeId'=>$this->uriVars['typeId']); // END if
					} // END if
					$defaultSort = 'dateCreated';
					$defaultSortOrder = "asc";
					$this->data['sortFields'] = array('dateCreated'=>'Sign-Up Date',	
								  'username'=>'Username',	
								  'email'=>'E-Mail Address',	
								   'typeId'=>'Type Id',	
								   'levelId'=>'Level Id',
								   'accessId'=>'Access Level');
					$this->alphaSearch = true;
					$this->textSearch = true;
					break;
			// NEWS
			case 'news':
				$this->load->model('news_model','dataModel');
				$this->data['subTitle'] = 'News';
				$this->data['searchType'] = 'news';
				if (isset($this->uriVars['filterAction']) && $this->uriVars['filterAction'] == 'search') {
					if ($this->input->post('type_id'))
						$this->uriVars['type_id'] = $this->input->post('type_id'); // END if
					if (isset($this->uriVars['type_id']) && !empty($this->uriVars['type_id'])) 
						$this->filterVars = $this->filterVars + array('type_id'=>$this->uriVars['type_id']); // END if
				} // END if
				$this->defaultSort = 'news_date';
				$this->data['sortFields'] = array('news_date'=>'Date',				  
							   'news_subject'=>'Title',
							   'author_id'=>'Author');
				$this->defaultSortOrder = "desc";
				$this->textSearch = true;
				break;
			// PROJECTS
			case 'projects':
				$this->load->model('project_model','dataModel');
				$this->data['subTitle'] = 'Projects';
				$this->data['searchType'] = 'projects';
				$this->defaultSort = 'name';
				$this->data['sortFields'] = array('name'=>'Project Name',				  
							   'startDate'=>'Start Date',
							   'dueDate'=>'Due Date',
							   'active'=>'Status');
				$this->defaultSortOrder = "asc";
				$this->textSearch = true;
				$this->alphaSearch = true;
				break;
			// BUGS
			case 'bug':
			case 'bugs':
				$this->restrictAccess = true;
				$this->minAccessLevel = ACCESS_WRITE;
				$this->load->model('bug_model','dataModel');
				$this->data['subTitle'] = 'Bugs';
				$this->data['searchType'] = 'bugs';
				if (isset($this->uriVars['filterAction']) && $this->uriVars['filterAction'] == 'search') {
					if ($this->input->post('priorityId'))
						$this->uriVars['priorityId'] = $this->input->post('priorityId'); // END if
					if (isset($this->uriVars['priorityId']) && !empty($this->uriVars['priorityId'])) 
						$this->filterVars = $this->filterVars + array('priorityId'=>$this->uriVars['priorityId']); // END if
					if ($this->input->post('severityId'))
						$this->uriVars['severityId'] = $this->input->post('severityId'); // END if
					if (isset($this->uriVars['severityId']) && !empty($this->uriVars['severityId'])) 
						$this->filterVars = $this->filterVars + array('severityId'=>$this->uriVars['severityId']); // END if
					if ($this->input->post('bugStatusId'))
						$this->uriVars['bugStatusId'] = $this->input->post('bugStatusId'); // END if
					if (isset($this->uriVars['bugStatusId']) && !empty($this->uriVars['bugStatusId'])) 
						$this->filterVars = $this->filterVars + array('bugStatusId'=>$this->uriVars['bugStatusId']); // END if
					if ($this->input->post('createdById'))
						$this->uriVars['createdById'] = $this->input->post('createdById'); // END if
					if (isset($this->uriVars['createdById']) && !empty($this->uriVars['createdById'])) 
						$this->filterVars = $this->filterVars + array('createdById'=>$this->uriVars['createdById']); // END if
					if ($this->input->post('projectId'))
						$this->uriVars['projectId'] = $this->input->post('projectId'); // END if
					if (isset($this->uriVars['projectId']) && !empty($this->uriVars['projectId'])) 
						$this->filterVars = $this->filterVars + array('projectId'=>$this->uriVars['projectId']); // END if
					if ($this->input->post('assignmentId'))
						$this->uriVars['assignmentId'] = $this->input->post('assignmentId'); // END if
					if (isset($this->uriVars['assignmentId']) && !empty($this->uriVars['assignmentId'])) 
						$this->filterVars = $this->filterVars + array('assignmentId'=>$this->uriVars['assignmentId']); // END if
				} // END if
				$this->defaultSort = 'dateCreated';
				$this->defaultSortOrder = "desc";
				$this->data['sortFields'] = array('summary'=>'Summary',				  
							   'projectId'=>'Project',
							   'createdById'=>'Creator Name',
							   'priorityId'=>'Priority',
							   'bugStatusId'=>'Status',
							   'severityId'=>'Severity',
							   'assignmentId'=>'Assignment',
							   'dateCreated'=>'Date Entered',
							   'dateModified'=>'Latest Updated');
				$this->textSearch = true;
				break;
		}  // END switch	
	}
	// SITE SPECIFIC SEARCH REDIRECTS
	function bug() { redirect('search/doSearch/bugs'); }
	function bugs() { redirect('search/doSearch/bugs'); }
	
	function project() { redirect('search/doSearch/projects'); }
	function projects() { redirect('search/doSearch/projects'); }
	
	function member() { redirect('search/doSearch/members'); }
	function members() { redirect('search/doSearch/members'); }
	
	function league() { redirect('search/doSearch/leaguea'); }
	function leagues() { redirect('search/doSearch/leagues'); }
	
	function user() { redirect('search/doSearch/user'); }
	function users() { redirect('search/doSearch/users'); }
	
	function news() { redirect('search/doSearch/news'); }
	
	/*---------------------------------------
	/
	/	STATIC PUBLIC METHODS
	/
	/--------------------------------------*/
	// The following methods are required to run the site search engine and NOTHING BELOW SHOULD BE CHANGED
	// UNLESS YOU KNOW WHAT YOU ARE DOING.
	
	/*--------------------------------
	/	C'TOR
	/-------------------------------*/
	/**
	 *	Creates a new instance of search.
	 *	Define all custom search results views within this method.
	 */
	function search() {
		parent::MY_Controller();
		$this->outTarget = $this->_TARGET_WEB;
		$this->defineSearchResultsViews();
	}
	/**
	 *	INDEX.
	 *	The default handler when the controller is called.
	 *	Checks for an existing auth session, and if found,
	 *	redirects to the dashboard. Otherwise, it redirects 
	 *	to the login.
	 */
	function export() {
		$this->$outTarget = $this->TARGET_DATA_OUT;
		$this->doSearch();
	}
	/**
	 *	INDEX.
	 *	The default handler when the controller is called.
	 *	Checks for an existing auth session, and if found,
	 *	redirects to the dashboard. Otherwise, it redirects 
	 *	to the login.
	 */
	function index() {
		redirect('search/doSearch');
	}
	
	/**
	 *	MENU.
	 *	The default handler that is called when no search id type has been passed. This displays a 
	 *	generic site search form.
	 */
	function menu() {
		$this->params['content'] = $this->load->view($this->views['MENU'], $this->data, true);
		parent::displayView($this->templates['FORM']);
	}
	/**
	 *	DO SEARCH.
	 *	This is the master search function that takes the search type, loads the 
	 *	appropriate data model, applies filters and filter arguments passed from the 
	 *	appropriate search view and executes the search.
	 *	
	 */
	function doSearch() {
		$this->getURIData();
		if (!isset($this->uriVars['id']) || empty($this->uriVars['id'])) {
			redirect('search/menu');	
		} else { 
			$this->filterVars = array();
			$this->metaFilters = array();
			$defaultSort = '';
			
			$this->queryDataBuilder();
			
			if (!$this->restrictAccess || ($this->restrictAccess && $this->params['accessLevel'] >= $this->minAccessLevel)) {
				if (isset($this->dataModel) && $this->dataModel->_NAME != 'generic_data_model') {
					if (isset($this->uriVars['filterAction']) && $this->uriVars['filterAction'] == 'search') {
						if (isset($this->uriVars['searchTerm'])) {
							$this->dataModel->searchTerm = $this->uriVars['searchTerm'];
						} // END if
						if (isset($this->uriVars['searchAlpha']) && !empty($this->uriVars['searchAlpha'])) {
							$this->dataModel->startsWithAlpha = $this->uriVars['searchAlpha'];
						} // END if
						if (isset($this->uriVars['sortBy'])) {
							$this->dataModel->sortFields = array($this->uriVars['sortBy']);
						} // END if
					} else {
						$this->uriVars['searchTerm'] = '';
						$this->uriVars['searchAlpha'] = '';
						$this->uriVars['sortBy'] = (!empty($this->defaultSort) ? $this->defaultSort : '');
						$this->dataModel->sortFields = array($this->uriVars['sortBy']);
					} // END if
					
					if (isset($this->uriVars['sortOrder'])) {
						$this->dataModel->sortOrder = $this->uriVars['sortOrder'];
					} else if (isset($this->defaultSortOrder)) {
						$this->dataModel->sortOrder = $this->uriVars['sortOrder'] = $this->defaultSortOrder;
					} // END if
					
					$this->dataModel->setMetaFilters($this->metaFilters);
					$this->dataModel->setSearchFilterVars($this->filterVars);
					$this->dataModel->setTableJoins($this->tableJoins);
					if (isset($this->uriVars['itemsPerPage'])) {
						$this->dataModel->limit = $this->uriVars['itemsPerPage'];
					} // END if
					
					if (isset($this->uriVars['pageNumber']) && isset($this->uriVars['itemsPerPage']) && $this->uriVars['pageNumber'] > 1) { 
						$this->dataModel->offset = (($this->uriVars['pageNumber'] - 1) * $this->uriVars['itemsPerPage']);
					} // END if
					$this->dataModel->search();
				} // END if
			} // END if
			if ($this->outTarget == $this->_TARGET_DATA_OUT) {
				$this->exportResults();
			} else {
				$this->displayView();
			}
		} // END if
	}
	/**
	* 	EXPORT SEARCH RESULTS.
	* 	Output a list of search data in one of four types of formats.
	*
	* 	Required URI Vars Properties
	* 	@param	$type		(int)	Export type (1 => SQL, 2 =>  HTML, 3 => JSON, 4 => XML, 5 => CSV)
	*
	* 	@since	1.0.6 Beta
	*
	*/
	public function exportResults() {
		
		$dataOut = "";
		if (isset($this->dataModel)) {
			$results = $this->dataModel->seachResults;
			$resultCount = $this->dataModel->resultCount;
			$this->form_validation->set_rules('type', 'Export Type', 'required');
			$outputType = $this->input->post('type');
			switch(intval($outputType)) {
				// SQL
				case 1:
					$this->output->set_header('Content-type: text/sql');
					
					break;
				// HTML
				case 2:
					$this->output->set_header('Content-type: text/html');
					
					break;
				// JSON
				case 3:
					$this->output->set_header('Content-type: application/json');
					
					break;
				// XML
				case 4:
					$this->output->set_header('Content-type: text/xml');
					
					break;
				// CSV
				case 5:
				default:
					$this->output->set_header('Content-type: application/csv');
					
					break;
			}
		}
		$this->output->set_output($dataOut);
		
	} // END function
	/*--------------------------------
	/	PROTECTED FUNCTIONS
	/-------------------------------*/
	/**
	 *	DISPLAY VIEW
	 *
	 */
	protected function displayView() {
		$this->data['totalPages'] = 1;
		$this->data['resultCount'] = 0;
		$this->data['accessAllowed'] = ($this->restrictAccess) ? ($this->params['accessLevel'] >= $this->minAccessLevel): true;
		if (isset($this->dataModel)) {
			$this->data['filterStr'] = $this->dataModel->filterStr;
			$this->data['filters'] = $this->dataModel->getSearchFilters();
			$this->data['searchResults'] = $this->dataModel->seachResults;
			$this->data['resultCount'] = $this->dataModel->resultCount;
			$this->data['startIndex'] = ($this->dataModel->offset == 0 ? ($this->data['resultCount'] != 0 ? 1 : 0) : $this->dataModel->offset + 1);
			
			if (isset($this->uriVars['debug'])) {
				$this->data['debug'] = $this->uriVars['debug'];
				$this->data['queryStr'] = $this->db->last_query();
			} // END if
		} // END if
		$this->data['textSearch'] = $this->textSearch;
		$this->data['alphaSearch'] = $this->alphaSearch;
		$this->data['searchAlpha'] = (isset($this->uriVars['searchAlpha']) && !empty($this->uriVars['searchAlpha']) ? $this->uriVars['searchAlpha'] : '');
		$this->data['searchTerm'] = (isset($this->uriVars['searchTerm']) ? $this->uriVars['searchTerm'] : '');
		
		$this->data['sortBy'] = (isset($this->uriVars['sortBy']) ? $this->uriVars['sortBy'] : '');
		$this->data['sortOrder'] = (isset($this->uriVars['sortOrder']) ? $this->uriVars['sortOrder'] : 'asc');
		$this->data['pageNumber'] = (isset($this->uriVars['pageNumber']) ? $this->uriVars['pageNumber'] : 1);
		$this->data['itemsPerPage'] = (isset($this->uriVars['itemsPerPage']) ? $this->uriVars['itemsPerPage'] : DEFAULT_RESULTS_COUNT);
		if (isset($this->data['resultCount']) && $this->data['resultCount'] != 0) {
			if ($this->data['resultCount'] > $this->data['itemsPerPage']) {
				$this->data['totalPages'] = (round(($this->data['resultCount'] / $this->data['itemsPerPage']) + 0.5));
			}
		}
		$this->data['loggedIn'] = $this->params['loggedIn'];
		if (empty($this->data['subTitle'])) { $this->data['subTitle'] = ''; }
		if (empty($this->data['sortFields'])) { $this->data['sortFields'] = array(); }

		$this->data['search'] = $this->load->view($this->views['RESULTS_'.strtoupper($this->data['searchType'])],$this->data,true);
		$this->params['content'] = $this->load->view('search/search_results',$this->data,true);
		if (!empty($this->outMess))
			$this->session->set_flashdata('message', '<p class="'.$this->messageType.'">'.$this->outMess.'</p>'); // END if
		$this->params['pageType'] = PAGE_SEARCH;
		parent::displayView();
	}
	/**
	 *	GET URI DATA.
	 *	Parses out an id and other parameters from the uri string
	 *
	 */
	protected function getURIData() {
		parent::getURIData();
		// GET GENERAL PAGE VARS FROM POST ACTION
		if ($this->input->post('id')) {
			$this->uriVars['id'] = $this->input->post('id');
		} // END if
		if ($this->input->post('debug')) {
			$this->uriVars['debug'] = $this->input->post('debug');
		} // END if
		if ($this->input->post('sortBy')) {
			$this->uriVars['sortBy'] = $this->input->post('sortBy');
		} // END if
		if ($this->input->post('sortOrder')) {
			$this->uriVars['sortOrder'] = $this->input->post('sortOrder');
		} // END if
		if ($this->input->post('searchTerm')) {
			$this->uriVars['searchTerm'] = $this->input->post('searchTerm');
		} // END if
		if ($this->input->post('searchAlpha')) {
			$this->uriVars['searchAlpha'] = $this->input->post('searchAlpha');
		} // END if
		if ($this->input->post('itemsPerPage')) {
			$this->uriVars['itemsPerPage'] = $this->input->post('itemsPerPage');
		} // END if
		if ($this->input->post('pageNumber')) {
			$this->uriVars['pageNumber'] = $this->input->post('pageNumber');
		} // END if
		if ($this->input->post('filter')) {
			$this->uriVars['filter'] = $this->input->post('filter');
		} // END if
		if ($this->input->post('filterAction')) {
			$this->uriVars['filterAction'] = $this->input->post('filterAction');
		} // END if
	}
}
/* End of file search.php */
/* Location: ./html/application/controllers/search.php */