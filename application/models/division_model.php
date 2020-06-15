<?php
/**
 *	DIVISION MODEL CLASS.
 *	
 *
 *	@author			Jeff Fox (Github ID: jfox015)
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
	/	PUBLIC FUNCTIONS
	/--------------------------------------*/
	
	/*---------------------------------------
	/	Add/EDIT/DELETE DIVISIONS
	/--------------------------------------*/
	/**
	* 	CLEAR DIVISIONS.
	*  	Deletes the divison for the specified league id.
	*
	*  @param	$league_id			{int}		League ID
	*  @return						{Boolean}	TRUE on success, FALSE on error
	*
	*  @since 	1.0.6
	*  @access	public
	*/
	public function clearDivisions($league_id = false) {
		
		if ($league_id === false) $league_id = $this->league_id;  // END if
		
		$this->db->where('league_id',$league_id);
		$this->db->delete($this->tblName);
		
		return true;
	}
	/**
	* 	CREATE DIVISION.
	*  Creates a new division for the specified league id.  This function returns the ID of the newly 
	*  	created division on success and false if division name is empty or league_id is -1.
	*
	*  @param	$division_name		{String} 	The Division Name
	*  @param	$league_id			{int}		League ID
	*  @return						{int}		The newly created division ID
	*
	*  @since 	1.0.6
	*  @access	public
	*/
	public function createDivision($division_name = false, $league_id = false) {
		
		if ($division_name === false) {
			$this->errorCode = 1;
			$this->statusMess = "A division name is required but was not recieved.";
			return false;
		} // END if
		
		if ($league_id === false) $league_id = $this->league_id;  // END if
		
		if ($league_id == -1) {
			$this->errorCode = 2;
			$this->statusMess = "A league ID is required but was not recieved.";
			return false;
		} // END if
		
		$this->db->insert($this->tblName,array('division_name'=>$division_name,'league_id'=>$league_id));
		
		return $this->db->insert_id();
		
	}
	/**
	 * 	CREATE DIVISIONS BY ARRAY.
	 *  Accepts and array with up to two params:
	 *  <ul>
	 *  	<li>division_name (Required)</li>
	 *  	<li>league_id (Optional)</li>
	 *  </ul>
	 *  The league_id param can either be passed as an aegument to the function or as a property of the array. The function 
	 *  defaults to the league ID arg if no league_id is passed int eh array.
	 *  
	 *  This function returns false if all league_id are -1.
	 *  
	 *  @param	$divison_list		{Array} 	Array of division data (See format)
	 *  @param	$league_id			{int}		League ID (Optional, can be passed per division item)
	 *  @return						{Array} 	Array of created division Ids
	 *  
	 *  @since 	1.0.6
	 *  @access	public
	 */
	public function createDivisionsByArray($divison_list = false, $league_id = false) {
		
		if ($league_id === false) $league_id = $this->league_id;  // END if
		
		if ($divison_list === false) {
			$this->errorCode = 1;
			$this->statusMess = "No division list was recieved.";
			return false;
		} // END if
		
		if (!is_array($divison_list) || sizeof($divison_list) < 1) {
			$this->errorCode = 2;
			$this->statusMess = "The divisions argument received was not a valid array or contained no items.";
			return false;
		} // END if
		$divIds = array();
		$division_name = '';
		// Loop through array and add each division
		foreach($divison_list as $div_array) {
			if (isset($div_array['division_name'])) { 
				$division_name = $div_array['division_name']; 
			} // END if
			if (isset($div_array['league_id'])) { 
				$league_id = $div_array['league_id']; 
			} // END if
			
			$thisDivId = $this->createDivision($division_name,$league_id);
			if ($thisDivId && $thisDivId != -1) {
				array_push($divIds, $thisDivId);
			} else {
				$this->errorCode = 4;
				$this->statusMess = "The division ".$division_name." was not added successfully. Error: ".$this->statusMess;
				break;
			} // END if
		} // END foreach
		return $divIds;
	}
	/*---------------------------------------
	/	GET DIVISION DATA
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