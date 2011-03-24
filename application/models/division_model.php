<?php
/**
 *	DIVISION MODEL CLASS.
 *	
 *
 *	@author			Jeff Fox <jfox015 (at) gmail (dot) com>
 *  @copyright   	(c)2009-11 Jeff Fox/Aeolian Digital Studios
 *	@version		1.0
 *
*/
class division_model extends base_model {

	/*--------------------------------
	/	VARIABLES
	/-------------------------------*/
	/**
	 *	SLUG.
	 *	@var $_NAME:Text
	 */
	var $_NAME = 'division_model';
	
	var $division_name = '';
	var $league_id = -1;
	/*---------------------------------------------
	/
	/	C'TOR
	/	Creates a new instance of division_model
	/
	/---------------------------------------------*/
	function division_model() {
		parent::__construct();
		
		$this->tblName = 'fantasy_divisions';
		
		$this->fieldList = array('division_name','league_id');
		$this->conditionList = array();
		$this->readOnlyList = array();  
		
		$this->columns_select = array('id','division_name');
		
		parent::_init();
	}
	/*---------------------------------------
	/	PRIVATE/PROTECTED FUNCTIONS
	/--------------------------------------*/
	public function getDivisionCount($league_id) {
		$this->db->select('id');
		$this->db->where('league_id',$league_id);
		$this->db->from($this->tblName);
		$count = $this->db->count_all_results();
	}
	public function getDivisionList($league_id) {
		
		$divisions = array();
		$this->db->select('id, division_name');
		$this->db->where('league_id',$league_id);
		$this->db->from($this->tblName);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$divisions = $query->result_array();
		}
		$query->free_result();
		return $divisions;
	}
}  