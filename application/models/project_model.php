<?php
/**
 * PROJECT MODEL.
 * A model for manipulating project data
 * 
 *	@author			Jeff Fox <jfox015 (at) gmail (dot) com>
 *  @copyright   	(c)2009-11 Jeff Fox/Aeolian Digital Studios
 *	@version		1.0
 */

class project_model extends base_model {

	/*--------------------------------
	/	VARIABLES
	/-------------------------------*/
	/**
	 *	SLUG.
	 *	@var $_NAME:Text
	 */
	var $_NAME = 'project_model';
	
	var $name = '';
	var $jobCode = '';
	var $startDate = EMPTY_DATE_TIME_STR;
	var $dueDate = EMPTY_DATE_TIME_STR;
	var $closeDate = EMPTY_DATE_TIME_STR;
	var $summary = '';
	var $active = -1;
	var $description = '';

	var $dateCreated =  EMPTY_DATE_TIME_STR;
	var $lastModified =  EMPTY_DATE_TIME_STR;
	var $lastModifiedBy =  -1;
	/*---------------------------------------------
	/
	/	C'TOR
	/	Creates a new instance of project_model
	/
	/---------------------------------------------*/
	function project_model() {
		parent::__construct();
		
		$this->tblName = 'admin_projects';
		
		$this->fieldList = array('name','jobCode','summary','active','description');
		$this->conditionList = array('startMonth','startDay','startYear','dueMonth','dueDay','dueYear','closeMonth','closeDay','closeYear');
		$this->readOnlyList = array('startDate','dueDate','closeDate','dateCreated','lastModified','lastModifiedBy');  
		$this->uniqueField = 'jobCode';
		
		$this->columns_select = array('id', 'jobCode','name', 'summary','active');
		$this->columns_text_search = array('summary', 'description', 'name');
		$this->columns_alpha_search = array('name');
		
		parent::_init();
	}
	
	public function applyData($input,$userId = false) {
		$success = parent::applyData($input,$userId);
		if ($success) {
			$this->lastModified = date('Y-m-d h:m:s');
			if ($userId) $this->lastModifiedBy = $userId;
			
			if ($input->post('startMonth') && $input->post('startDay') && $input->post('startYear')) {
				$this->startDate = $input->post('startYear')."-".$input->post('startMonth')."-".$input->post('startDay');
			}
			if ($input->post('dueMonth') && $input->post('dueDay') && $input->post('dueYear')) {
				$this->dueDate = $input->post('dueYear')."-".$input->post('dueMonth')."-".$input->post('dueDay');
			}
			if ($input->post('closeMonth') && $input->post('closeDay') && $input->post('closeYear')) {
				$this->closeDate = $input->post('closeYear')."-".$input->post('closeMonth')."-".$input->post('closeDay');
			}
		}
		return $success;
	}
}