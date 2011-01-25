<?php
/*-------------------------------------------------------------------------------------
/   Filename: SearchEngine.php
/   Date Created: 10/15/09
/   Last modified: 10/15/09
/   
/   Change Log:
/-----------------------------------------------------------------------------------*/
require_once('SearchFilter.php');
/**
 * SearchEngine object.
 *
 * @author      Jeff Fox <jfox@NOSPAMaeoliandigital.com>
 * @version     $Revision: 1.0 $
 * @since       1.0
 * @copyright   (c)2009-10 Jeff Fox/Aeolian Digital Studios
 * 
 */
class SearchEngine {
	
	var $_NAME = 'SearchEngine';
	var $ci = NULL;

	var $outMess = '';
	var $messageType = '';
	var $debug = -1;
	
	var $columns_select = array();
	var $columns_text_search = array();
	var $tblName = array();
	var $limit = DEFAULT_RESULTS_COUNT;
	
	var $searchTerm = '';
	var $query = NULL;
	var $seachResults = array();
	
	var $filters = array();
	var $filterVars = array();
	var $filterStr = '';
	
	function SearchEngine() {
		$this->init();
	}
	
	protected function init() {
		$this->ci = & get_instance();
		$this->ci->load->helper('datalist');
	}
	
	public function addSearchFilter($filterId,$label,$listId,$column = '') {
		$tmpFilter = new SearchFilter($label);
		$tmpFilter->loadData($listId,$column);
		$this->filters = $this->filters + array($filterId => $tmpFilter);
	}
	
	public function getSearchFilters() {
		return $this->filters;
	}
	public function setSearchFilterVars($vars) {
		$this->filterVars = $vars;
	}
	
	public function search() {
		$selectStr = '';
		if (is_array($this->columns_select) && sizeof($this->columns_select) > 0) {
			foreach($this->columns_select as $column) {
				if (!empty($selectStr)) $selectStr .= ',';
				$selectStr .= $column;
			}
		}
		$this->ci->db->select($selectStr);
		$this->ci->db->from($this->tblName);
		if (is_array($this->filterVars) && sizeof($this->filterVars) > 0) {
			foreach($this->filterVars as $filterId => $filterValue) {
				if (isset($this->filters[$filterId])) {
					$condition = $this->filters[$filterId]->getData($filterValue);
					if ($condition) {
						$this->ci->db->where($filterId, $filterValue);
						if (!empty($this->filterStr)) { $this->filterStr .= ', '; }
						$this->filterStr .= $this->filters[$filterId]->label." = ".$condition;
						$this->filters[$filterId]->selectedIndex = $filterValue;
					}
				}
			}
		}
		if (!empty($this->searchTerm)) {
			if (is_array($this->columns_text_search) && sizeof($this->columns_text_search) > 0) {
				foreach($this->columns_text_search as $column) {
					$this->ci->db->where($column," LIKE '%".$this->searchTerm."%'");		
				}
				$filterStr .= ' <b>Text Search: </b>&quot;'.$this->searchTerm.'&quot;';
			}
		}
		$this->ci->db->limit($this->limit);
		$this->query = $this->ci->db->get();
		
		if ($this->query->num_rows() > 0) {
			$dataRows = array();
			$fields = $this->query->list_fields();
			foreach ($this->query->result() as $row) {
				$dataRow = array();
				foreach ($fields as $field) {
					$value = '';
					if (isset($this->filters[$field])) {
						$value = $this->filters[$field]->getData($row->$field);
					} else {
						$value = $row->$field;
					}
					$dataRow =  $dataRow + array($field=>$value);
				}
				array_push($dataRows,$dataRow);
			}
			$this->seachResults =  $dataRows;
		} else {
			$this->seachResults = array('noResults'=>'No results matching the specified criteria were found.');
		} // END if
		$this->query->free_result();
	}
}