<?php
/*-------------------------------------------------------------------------------------
/   Filename: SearchFilter.php
/   Date Created: 10/15/09
/   Last modified: 10/15/09
/   
/   Change Log:
/-----------------------------------------------------------------------------------*/
/**
 * SearchFilter object.
 *
 * @author      Jeff Fox <jfox@NOSPAMaeoliandigital.com>
 * @version     $Revision: 1.0 $
 * @since       1.0
 * @copyright   (c)2009-10 Jeff Fox/Aeolian Digital Studios
 * 
 */
class SearchFilter {
	
	var $_NAME = 'SearchFilter';
	var $ci = NULL;
	
	var $id = '';
	var $label = '';
	var $datalist = array();
	/**
	 *	SELECTED INDEX.
	 *	The index ID of the currentl;y selected item
	 *	@var	$selectedIndex:Int
	 */
	var $selectedIndex = -1;
	
	function SearchFilter($id = '',$label = '') {
		$this->ci = & get_instance();
		$this->ci->load->helper('datalist');
		if (!empty($id) && $id != '') { $this->id = $id; }
		if (!empty($label) && $label != '') { $this->label = $label; }
	}
	
	public function loadData($listId,$column = '') {
		$col = 'id';
		if (isset($column) && !empty($column)) { $col = $column; }
		$this->datalist = loadSimpleDataList($listId,$col,"ASC",$this->label);
		if (empty($this->id)) { $this->id = $listId; } 
		if (empty($this->label)) { $this->label = $listId; } 
	}
	
	public function getData($idx = false) {
		if ($idx === false) {
			return false;
		}
		if (isset($this->datalist[$idx])) {
			return $this->datalist[$idx];
		} else {
			return false;
		}
	}
	
	public function getList() {
		return $this->datalist;
	}

}