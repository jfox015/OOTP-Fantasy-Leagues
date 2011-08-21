<?php
/**
 * 	BUG MODEL.
 * 	A model for tracking bugs within a site
 *	@author			Jeff Fox <jfox015 (at) gmail (dot) com>
 *  @copyright   	(c)2009-11 Jeff Fox/Aeolian Digital Studios
 * 
 */

class bug_model extends base_model {

	/*--------------------------------
	/	VARIABLES
	/-------------------------------*/
	/**
	 *	SLUG.
	 *	@var $_NAME:Text
	 */
	var $_NAME = 'bug_model';
	var $projectId =  -1;
	var $selectedProjectId =  -1;
	
	var $createdById =  -1;
	var $assignmentId =  -1;
	var $bugStatusId =  -1;
	var $priorityId =  -1;
	var $severityId =  -1;
	
	var $summary =  '';
	var $description =  '';
	var $url =  '';
	var $attachment =  '';
	var $comments =  '';
	var $tags =  '';
	
	var $category =  -1;
	var $subCategory =  -1;
	var $component =  '';
	
	var $browser =  -1;
	var $os =  -1;
	var $browVersion =  '';
	
	var $minAccessLevel =  -1;
	var $dateCreated =  EMPTY_DATE_TIME_STR;
	var $dateModified =  EMPTY_DATE_TIME_STR;
	var $lastModifiedBy =  -1;
	/*---------------------------------------------
	/
	/	C'TOR
	/	Creates a new instance of bug_model
	/
	/---------------------------------------------*/
	function bug_model() {
		parent::__construct();
		
		$this->tblName = 'admin_bugs';
		$this->tables['PROJECTS'] = 'admin_projects';
		
		$this->fieldList = array('projectId','bugStatusId','priorityId', 'severityId', 
						   'summary','description', 'url', 'assignmentId', 
						   'category', 'subCategory',  
						   'component', 'browser', 'os', 'browVersion','tags');
		$this->conditionList = array('attachmentFile','newComments');
		$this->readOnlyList = array('dateCreated','dateModified','createdById','comments',
									'lastModifiedBy', 'attachment','minAccessLevel');  
		$this->uniqueField = 'summary';
		$this->textList = array('browVersion','tags');
		
		$this->columns_select = array('id', 'description', 'dateCreated','projectId', 'summary','assignmentId','createdById', 'tags', 'bugStatusId', 'priorityId','severityId');
		$this->columns_text_search = array('summary', 'description', 'tags');
		$this->columns_alpha_search = array();
		
		$this->addSearchFilter('bugStatusId','Status','bugStatus');
		$this->addSearchFilter('priorityId','Priority','priority');
		$this->addSearchFilter('severityId','Severity','severity');
		$this->addSearchFilter('projectId','Project','project');
		$this->addSearchFilter('createdById','Entered By','username');
		$this->addSearchFilter('assignmentId','Assigned To','username');
		
		parent::_init();
	}
	/**
	 * 	APPLY DATA.
	 *
	 *	Applies custom data values to the object. 
	 *
	 * 	@return 	TRUE on success, FALSE on failure
	 *
	 */
	public function applyData($input,$userId = false) {
		$success = parent::applyData($input,$userId);
		if ($success) {
			if ($this->id == -1)
				$this->createdById = $userId;
			$this->dateModified = date('Y-m-d h:m:s');
			if ($userId) 
			    $this->lastModifiedBy = $userId;
			if ($input->post('newComments'))
				$this->appendComments($input,$userId);
			if (isset($_FILES['attachmentFile']['name']) && !empty($_FILES['attachmentFile']['name'])) 
				$success = $this->uploadFile('attachment',PATH_BUGS_ATTACHMENT_WRITE,$input);
		}
		return $success;
	}
	public function getProjectList($selectBox = true) {
		// PROPJECT LIST
		$currProjs = array();
		if ($selectBox)
		    $projOptions = array(''=>'Choose a project');
		else 
		    $projOptions = array();
		
		$projsWithBugs = $this->loadProjectsWithBugs();
		if ($selectBox)
		    $projsWithBugs = array(''=>' - - - Projects with bug records - - - ') + $projsWithBugs;
		foreach($projsWithBugs as $id => $value) {
			array_push($currProjs,$id);
			if ($this->projectId != -1 && $this->projectId == $id) {
				$this->selectedProjectId = $id;	
				break;
			}
		}
		$projOptions = $projOptions + $projsWithBugs;
			
		$normProjs = $this->loadProjects($currProjs);
		if ($selectBox)
		    $normProjs = array(''=>' - - - Active Projects  - - - ') + $normProjs;
		
		foreach($normProjs as $id => $value) {
			if ($this->dataModel->projectId != -1 && $this->dataModel->projectId == $id) {
				$this->selectedProjectId = $id;	
				break;
			}
		}
		$projOptions = $projOptions + $normProjs;
		return $projOptions;
	}
	private function appendComments($input,$userId = false) {
		//echo('Add user comment');
		$this->lang->load('bugtrack');
		$commentTag = $this->lang->line('comment_header');
		$commentTag = str_replace('[UPDATE_DATE]',date('m/j/Y H:i:s'),$commentTag);  
		$commentTag = str_replace('[UPDATER_NAME]',resolveUsername($userId),$commentTag);
		$comments = $commentTag.'<br /><br />'.$input->post('newComments');
		$comments = str_replace('\n\r','<br />',$comments);
		$comments = str_replace('\n','<br />',$comments);
		$sql = "SELECT comments FROM ".$this->tblName." WHERE id = ?";
		$query = $this->db->query($sql,$this->id);
		if ($query->num_rows > 0) {
			$row = $query->row();
			$comments = $comments.'<br /><br />'.$row->comments;
		}	
		$this->comments = $comments;
		$query->free_result();
	}
	private function loadProjects($usedProjs) {
		$projs = array();
		$sql = 'SELECT id, name,jobCode FROM '.$this->tables['PROJECTS'].' WHERE active <> 0 ORDER BY jobCode, name ASC';
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				if (!in_array($row->id,$usedProjs)) {
					$projs = $projs + array($row->id=>$row->jobCode." - ".$row->name);
				}
			}
		}
		$query->free_result();
		return $projs;			
	}
	public function loadProjectsWithBugs() {
		$projsWithBugs = array();
		$this->db->select($this->tblName.'.projectId, '.$this->tables['PROJECTS'].'.name as projName, '.$this->tables['PROJECTS'].'.jobCode')
		         ->from($this->tblName)
		         ->join($this->tables['PROJECTS'],$this->tables['PROJECTS'].'.id = '.$this->tblName.'.projectId','left')
		         ->group_by($this->tblName.'.projectId')
		         ->order_by($this->tables['PROJECTS'].'.jobCode', 'asc');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$projsWithBugs = $projsWithBugs + array($row->projectId => $row->jobCode.' - '.$row->projName);
			}
		}	
		$query->free_result();
		return $projsWithBugs;
	}
}