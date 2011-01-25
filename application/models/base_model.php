<?php
/**
 *	BASE MODEL.
 *	An extension of the base CodeIgnighter Model class.
 *	It adds numerous basic properties and methods for working with database 
 *	data models like loading, saving and applying form data, search, file 
 *	handling and serilazation.
 * 
 *	@author			Jeff Fox <jfox015@gmail.com>
 *  @copyright   	(c)2009-10 Jeff Fox/Aeolian Digital Studios
 *	@version		1.5
 *	@created		08/01/2009
 *	@lastModified	07/20/2010
 *	@phpVersion		5.2+
 *	@framework		CodeIgnighter 1.7x
 *
 *
*/
require_once('./application/libraries/SearchFilter.php');
class base_model extends Model implements Serializable {
	/**
	 *	_NAME
	 *	String identifier of the model.
	 *	@var $_NAME:String
	 */
	var $_NAME = 'base_model';
	/**
	 *	ID
	 *	Record ID. -1 if no db record yet exists.
	 *	@var $id:Number
	 */
	var $id = -1;
	/**
	 *	TABLES.
	 *	List of tables in the database.
	 *	@var $tables:Array
	 */
	var $tables = array();
	/**
	 *	TABLE NAME
	 *	@var $tblName:String
	 */
	var $tblName = '';
	/**
	 *	ALL FIELDs LIST
	 *	Read Only L
	 *	@var $fieldList:array
	 */
	var $allFields = array();
	/**
	 *	FIELD LIST
	 *	List of fields that will have a form element and post data applied
	 *	to them
	 *	@var $fieldList:array
	 */
	var $fieldList = array();
	/**
	 *	READ ONLY FIELD LIST
	 *	List of fields that are available as read only data and cannot be 
	 *	manipulated via form posts
	 *	@var $readOnlyList:array
	 */
	var $readOnlyList = array();
	/**
	 *	CONDITIONAL FIELD LIST
	 *	List of fields that are not part of the models database structure,
	 *	but serve conditional function for database fields (such as only 
	 *	updating a db field if user input is present in the data post object
	 *	@var $conditionList:array
	 */
	var $conditionList = array();
	/**
	 *	TEXT FIELD LIST
	 *	The text field array stores all text input or textarea fields used in the 
	 *	models form. Because these fields return false when they are empty, they 
	 *	should be set to blank in that case. Any fields that should only be modified if a 
	 *	a value is actually recieved should be left out of this list.
	 *	@var 	$textList:array
	 *	@since	1.5
	 */
	var $textList = array();
	/**
	 *	UNIQUE FIELD
	 *	Used to attain the recoird ID after adding a new record
	 *	@var $uniqueField:String
	 */
	var $uniqueField = '';
	/**
	 *	DEBUG.
	 *	Flag wether to debug or not.
	 *	@var $debug:Boolean
	 */
	var $debug = false;
	/**
	 *	ERROR CODE.
	 *	Status error code. Default is -1.
	 *	@var $errorCode:int
	 */
	var $errorCode = -1;
	/**
	 *	STATUS MESSAGE.
	 *	A message with feedback on model status, issues and results.
	 *	@var $statusMess:String
	 */
	var $statusMess = '';
	/**
	 *	STATUS MESSAGE.
	 *	A message with feedback on model status, issues and results.
	 *	@var $statusMess:String
	 *	@deprecated	Not currently in use
	 */
	var $joinCode = '';
	
	/*------------------------------
	/	SEARCH SPECIFIC VARS
	/-----------------------------*/ 
	/**
	 *	COLUMNS SELECT.
	 *	The columns to be returned in a basic search query.
	 *	@var $columns_select:Array
	 */
	var $columns_select = array();
	/**
	 *	COLUMNS TEXT SEARCH.
	 *	The columns to be searched in a FULLTEXT search query.
	 *	NOTE all corresponsing database fields must be index via FULLTEXT.
	 *	@var $columns_text_search:Array
	 */
	var $columns_text_search = array();
	/**
	 *	COLUMNS ALPHA SEARCH.
	 *	The columns to be searched in an alphabetic search query. Alphabetic queries 
	 *	match a specific db rows starting twith the assigned character
	 *	@var $columns_alpha_search:Array
	 */
	var $columns_alpha_search = array();
	/**
	 *	LIMIT.
	 *	SQL query limit value
	 *	@var $limit:Int
	 */
	var $limit = DEFAULT_RESULTS_COUNT;
	/**
	 *	OFFSET.
	 *	SQL query limit offset value
	 *	@var $offset:Int
	 */
	var $offset = 0;
	/**
	 *	SORT FIELDS.
	 *	Array of fields to sort search results by
	 *	@var $sortFields:Array
	 */
	var $sortFields = array();
	/**
	 *	SORT ORDER.
	 *	ORDER BY sort order param
	 *	@var $sortOrder:String
	 */
	var $sortOrder = 'asc';
	
	/**
	 *	SEARCH TERM.
	 *	Text string to be used in FULLTEXT where matches
	 *	@var $searchTerm:String
	 */
	var $searchTerm = '';
	var $startsWithAlpha = '';
	var $query = NULL;
	var $seachResults = array();
	var $resultCount = 0;
	
	var $filters = array();
	var $metaFilters = array();
	var $filterVars = array();
	var $filterStr = '';
	/**
	 *	FORCE FILE NAME HASH.
	 * 	Allows child models to force the file upload process to use a filename 
	 *  rather than choosing to do so only when a hash value is passed.
	 */
	var $forceFileNameHash = false;
	/*----------------------------------
	/	C'TOR
	/---------------------------------*/
	/**
	 *	CONSTRUCTOR
	 *	Creates a new instance of base_model.
	 */
	public function __construct() {
		parent::__construct();
		$this->load->config('auth');
		$this->tables = $this->config->item('tables');
		$this->load->helper('datalist');
	}
	/*----------------------------------
	/	PUBLIC FUNCTIONS
	/---------------------------------*/
	/**
	 *	ADD SEARCH FILTER.
	 *	Adds a new SearchFilter object to the $filters array.
	 *	@param		$filterId	The ID of the filter. Should matcht eh correspondiing
	 *							database column.
	 *	@param		$label		Text Label of filter
	 *	@param		$listId		dataList_helper list ID
	 *	@param		$column		(Optional) Column to sort dataList_helper list by
	 *	@return		void
	 */
	public function addSearchFilter($filterId,$label,$listId,$column = '') {
		$tmpFilter = new SearchFilter($filterId,$label);
		$tmpFilter->loadData($listId,$column);
		$this->filters = $this->filters + array($filterId => $tmpFilter);
	}
	/**
	 *	APPLY DATA
	 *	Applies data in the public fieldList array to the local instance variables.
	 *	If an exception occurs it is caught and the method returns FALSE. Otherwise 
	 *	it returns TRUE.
	 *	@param	$input		CodeIgnighter Form input array object
	 *	@param	$userData	User Data object.
	 *	@return	Boolean		TRUE on success, FALSE on Exception
	 */
	public function applyData($input,$userId = -1) {
		$success = true;
		try {
			//echo("Form Summary:<br />");
			foreach($this->fieldList as $field) {
				if ($input->post($field)) {
					//echo($field." = ".$input->post($field)."<br />");
				    $this->$field = $input->post($field);
				} // END if
			} // END foreach
			foreach($this->textList as $field) {
				$this->$field = ($input->post($field) ? $input->post($field) : "");
			} // END foreach
		} catch (Exception $e) {
			$success = false;
			$this->errorCode = 1;
			$this->statusMess = 'Error occured applying data. Error: '.$e;
		}// END try/catch
		return $success;
	}
	/**
	 *	DELETE.
	 *	Deletes the current record associated with the $id param.
	 *	@return	Boolean		TRUE on success, FALSE on error
	 */
	public function delete() {
		$success = false;
		if ($this->id != -1) { 
			$this->db->where('id', $this->id);
			$this->db->delete($this->tblName); 
			if ($this->db->affected_rows() > 0) {
				$this->id = -1;
				$success = true;
			} else {
				$this->errorCode = 4;
				$this->statusMess = "Record delete operation failed. It's possible the record doesnot exist.";	
			} // END if
		}	 else {
			$this->errorCode = 5;
			$this->statusMess = 'Record delete operation failed. No id parameter present. This could mean the record did not properly load or does not exist.';	
		} // END if
		return $success;
	}
	/**
	 *	DELETE FILE.
	 *	Deletes the current file record associated with the $type and $path params.
	 *	@param	$type		The type of file being uploaded.
	 *	@param	$path		The files write path on the server
	 *	@param	$updateDB	TRUE to update, FALSE to not
	 *	@return	Boolean		TRUE on success, FALSE on error
	 */
	public function deleteFile($type,$path,$updateDB = true) {
		$success = false;
		$prevAttach = '';
		// GET Current attachment name		
		$query = $this->db->select($type)
               	      ->where('id', $this->id)
               	      ->limit(1)
               	      ->get($this->tblName);
               	      
		$result = $query->row();
		if ($query->num_rows > 0) { 
			$prevAttach = $query->row()->$type;
		}
		$query->free_result();
		if (!empty($prevAttach)) {
			$target_file_name = dirname(FCPATH).$path.$prevAttach;
			//echo("target_file_name = ".$target_file_name."<br />");
			if (file_exists($target_file_name)) {
				if (unlink($target_file_name)) {
					if ($updateDB) {
						$sql = "UPDATE ".$this->tblName." SET ".$type." = '' WHERE id = ?";
						if (!$this->db->query($sql,$this->id)) {
							$this->errorCode = 1;
							$this->statusMess = 'The file was deleted but the record could not be updated in the database.';
						} else {
							$success = true;
							$this->statusMess = "The requested operation was successfully completed.";
							$this->avatar = '';
						}
					}
				} else {
					$this->errorCode = 2;
					$this->statusMess = 'The file '.$prevAttach.' could not be deleted. This may be due to a permission or file reference error. Please contact the <a href="'.SITE_CONTACT_EMAIL.'">site admin</a> for assistance.';
					clearstatcache();
					$this->statusMess .= '<br /><b>Error For admin usage:</b><br />';
					$this->statusMess .= 'File path: '.$target_file_name.'<br />';
					$this->statusMess .= 'File Exists: '.file_exists($target_file_name).'<br />';
					$this->statusMess .= 'Perms for file: '.substr(sprintf('%o', fileperms($target_file_name)), -4).'<br />';
				}
			} else {
				$this->errorCode = 3;
				$this->statusMess = 'The file '.$prevAttach.' could not be found. Assure the file has not already been deleted, renamed or moved.';
			}
		} else {
			$this->errorCode = 4;
			$this->statusMess = 'No attachment name was found. Assure the file has not already been deleted, renamed or moved.';
		}
		return $success;
	}
	public function dumpData() {
		$outHTML = "";
		foreach($this->allFields as $field) {
			if ($field != 'dateCreated') {
				$outHTML .= "<b>".$field."</b> = ".$this->$field."<br />";
			}
		} // END foreach
		return $outHTML;
	}
	/**
	 *	GET SEARCH FILTERS.
	 *	Returns the SearchFilter objects applied to this model
	 *	@param		void
	 *	@return		Array of SearchFilter objects or NULL if none are loaded.
	 */
	public function getSearchFilters() {
		return $this->filters;
	}
	
	/**
	 *	LOAD.
	 *	Loads a record from the db using the field param as the column indicator. In 
	 *	the event a different field than ID needs to be used, simply plass that column 
	 *	name as the second argument.
	 *	@param	id			The record identifier value
	 *	@param	field		Column argument. Defaults to 'id'
	 *	@return	Boolean		TRUE on success, FALSE on error
	 */
	public function load($id,$field = 'id',$includeId = true) {
		$success = false;
		if (!empty($id)) {
			//echo("Field = '".$field."'<br />");
			if ($field != 'id' && $includeId) $select = "id,"; else $select = ""; 
			$this->db->select($select.$this->fieldsToSQL());
			$this->db->where($field,$id);
			$query = $this->db->get($this->tblName);
			//echo($this->db->last_query()."<br />");
			//echo("num rows = ".$query->num_rows()."<br />");
			if ($query->num_rows() == 0) {
				$this->errorCode = 2;
				$this->statusMess = 'No results were returned. The record does not exist or may have been moved or deleted.';
				$this->statusMess .= "<br />".$this->db->last_query();
			} else {
				$row = $query->row();
				$this->fieldsFromSQL($row);
				if ($field == 'id') {
					//echo("used passed param");
					$this->id = intval($id);
				} else {
					if ($includeId && isset($row->id)) {
						$this->id = $row->id;	
					}
				}
				$success = true;
			} // END if
			$query->free_result();
		} else {
			$this->errorCode = 3;
			$this->statusMess = 'Record identifier argument missing.';	
		} // END if
		//echo("load success = ".(($success) ? 'true' : 'false')."<br />");
		
		//echo($this->id."<br />");
		return $success;
	}
	/**
	 *	PROFILE.
	 *	Returns a profile of the major fields available and their data for the 
	 *	current instance.
	 *	@return $db->query->row
	 **/
	public function profile() {
		$this->db->select($this->fieldsToSQL(false,false));
		$this->db->from($this->tblName);
		$this->db->where('id', $this->id);
		$this->db->limit(1);
		$i = $this->db->get();
		return ($i->num_rows() > 0 ? $i->row() : false);
	}
	/**
	 *	SAVE.
	 *	Saves the current record values to the db. If the id parameter is -1,
	 *	this is done via an INSERT operation, otherwise, the function executes 
	 *	an UPDATE query.
	 *	@return	Boolean		TRUE on success, FALSE on error
	 */
	public function save() {
		$data = array();
		foreach($this->allFields as $field) {
			if ($field != 'dateCreated') {
				//echo($field ." = ".$this->$field."<br />");
				$data = $data + array($field =>$this->$field);
			}
		} // END foreach
		
		if ($this->id != -1) {
			$this->db->where('id', $this->id);
			$this->db->update($this->tblName,$data);
		} else {
			$this->db->insert($this->tblName,$data);
		} // END if
		//echo($this->db->last_query()."<br />");
		//echo($this->db->affected_rows()."<br />");
		if ($this->db->affected_rows() == 0) {
			$this->errorCode = 6;
			$this->statusMess = 'Notice: No rows were affected by this update.';
		} // END if
		if ($this->id == -1) {
			$this->id = $this->db->insert_id();
			if ($this->load($this->id)) {
				return TRUE;
			} else {
				return FALSE;
			} // END if
		} else {
			return TRUE;
		} // END if
	}
	/**
	 *	SEARCH.
	 *	Constructs a basic search query and executes it.
	 
	 *	@return		void
	 */
	public function search() {
		$selectStr = '';
		if (is_array($this->columns_select) && sizeof($this->columns_select) > 0) {
			foreach($this->columns_select as $column) {
				if (!empty($selectStr)) $selectStr .= ',';
				$selectStr .= $column;
			}
		}
		$this->db->select($selectStr);
		$this->db->from($this->tblName);
		/* APPLY FILTERS */
		if (is_array($this->filterVars) && sizeof($this->filterVars) > 0) {
			foreach($this->filterVars as $filterId => $filterValue) {
				if (isset($this->filters[$filterId])) {
					$condition = $this->filters[$filterId]->getData($filterValue);
					if ($condition) {
						$this->db->where($filterId, $filterValue);
						if (!empty($this->filterStr)) { $this->filterStr .= ', '; }
						$this->filterStr .= '<b>'.$this->filters[$filterId]->label."</b> = ".$condition;
						$this->filters[$filterId]->selectedIndex = $filterValue;
					}
				}
			}
			$this->filterStr = "Filtering Results By: ".$this->filterStr;
		}
		/* APPLY META FITLERING */
		// META Filter differ from regualr filters in that the user is not 
		// shown that they are being applied.
		if (is_array($this->metaFilters) && sizeof($this->metaFilters) > 0) {
			foreach($this->metaFilters as $filterId => $filterValue) {
				$this->db->where($filterId, $filterValue);
			}
		}
		/* APPLY TEXT SEARCH TERM FITLERING */
		if (!empty($this->searchTerm)) {
			$strCols = '';
			if (is_array($this->columns_text_search) && sizeof($this->columns_text_search) > 0) {
				foreach($this->columns_text_search as $column) {
					//$this->db->or_like($column,$this->searchTerm,'both');	
					if (!empty($strCols)) { $strCols .= ','; }
					$strCols .= $column;
				}
				$matchStr = "MATCH (".$strCols.") AGAINST ('".$this->searchTerm."')";
				/*if (sizeof($this->filterVars) > 0 || sizeof($this->metaFilters) > 0) {
					$this->db->or_where($matchStr,'',FALSE);
				} else { */
					$this->db->where($matchStr,'',FALSE);
				//}
				if (!empty($this->filterStr)) { $this->filterStr .= ','; }
				$this->filterStr .= ' <b>Text Search:</b> <span class="highlight">'.$this->searchTerm.'</span>';
			}
		}
		/* APPLY ALPHABETIC FILTERING */
		if (!empty($this->startsWithAlpha)) {
			if (is_array($this->columns_alpha_search) && sizeof($this->columns_alpha_search) > 0) {
				foreach($this->columns_alpha_search as $column) {
					$this->db->or_like($column,$this->startsWithAlpha,'after');		
				}
				if (!empty($this->filterStr)) { $this->filterStr .= ','; }
				$this->filterStr .= ' <b>Results Beginning With: </b>&quot;'.$this->startsWithAlpha.'&quot;';
			}
		}
		if (is_array($this->sortFields) && sizeof($this->sortFields) > 0) {
			foreach($this->sortFields as $field) {
				$this->db->order_by($field,$this->sortOrder);
			} // END foreach
		} // END if
		
		// GET FULL # of rows for this result BEFORE applying pagination offsets
		$query = $this->db->get();
		$this->resultCount = $query->num_rows();
		$queryStr = $this->db->last_query();
		
		// APLLY THE QUERY WITH LIMITS
		//$this->query = $this->db->query($queryStr);
		$this->query = $this->db->query($queryStr. ' LIMIT '.$this->offset.', '.$this->limit);
		
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
						if (!empty($this->searchTerm) && in_array($field,$this->columns_text_search)) {
						    if (!function_exists('highlightWords'))
						        $this->load->helper('display');
							$value = highlightWords($row->$field,$this->searchTerm);
						} else {
							$value = $row->$field;
						} // END if
					} // END if
					$dataRow = $dataRow + array($field=>$value);
				} // END foreach
				array_push($dataRows,$dataRow);
			} // END foreach
			$this->seachResults = $dataRows;
		} //else {
			//$this->seachResults = array(array('noResults'=>'No results matching the specified criteria were found.'));
		//} // END if
		$this->query->free_result();
	}
	/**
	 *	SERIALZE.
	 *	Serializes the current object (based on the $allFields array) to a 
	 *	string array for serialization (either to a the szession or a written file)
	 *	@return	String	Serialzed data string
	 */
	public function serialize() {
		$serialList = array();
		foreach ($this->allFields as $fieldName) {
			$serialList = $serialList + array($fieldName=>$this->$fieldName);
		}
		return serialize($serialList);
	}
	/**
	 *	SET SEARCH FILTER VARS.
	 *	Sets the actual filter values chosen and received by the controller 
	 *	via uriVars
	 *	@param		$vars	Array of vars in ($filterId => $value) format
	 *	@return		void
	 */
	public function setSearchFilterVars($vars) {
		$this->filterVars = $vars;
	}
	public function setMetaFilters($filters) {
		$this->metaFilters = $filters;
	}
	public function setSearchResults($results = false) {
		if ($results === false) {
			return false;
		}
		$this->seachResults = $results;
		return true;
	}
	/**
	 *	UNSERIALZE.
	 *	Unserializes a serialized version of the current object.
	 *	@return	String	Serialzed data string
	 */
	public function unserialize($serialized) {
		$success = true;
		try {
			$raw = unserialize($serialized);
			if ($raw) {
				foreach ($raw as $key => $value) {
					$this->$key = $value;
				} // END foreach
			} // END if
		} catch (Exception $e) {
			$success = false;
			$this->errorCode = 1;
			$this->statusMess = 'Error occured unserializing data. Error: '.$e;
		} // END try/catch
		return $success;
	}
	/*--------------------------------------
	/	PROTECTED/PRIVATE FUNCTIONS
	/-------------------------------------*/
	/**
	 *	FIELDS TO SQL.
	 *	Converts the public and read only field lists into a escaped sql query 
	 *	string.
	 *	@param	values	Set to TRUE to include values in the query, FALSE to just return field names
	 *	@return	String	SQL query string
	 */
	protected function fieldsToSQL($values = false,$joined = false) {
		$fieldSQL = '';
		foreach($this->fieldList as $field) {
			if ($fieldSQL != '') { $fieldSQL .= ","; } // END if
			if ($joined) { $fieldSQL .= $this->joinCode."."; } // END if
			$fieldSQL .= $field;
			if ($values) {
				$fieldSQL .= ' = '.$this->db->escape($this->$field);
			} // END if
		} // END foreach
		foreach($this->readOnlyList as $field) {
			if ($values) {
				if ($this->$field != '' && $this->$field != -1) {
					$fieldSQL .= ",".$field.' = '.$this->db->escape($this->$field);
				} // END if
			} else {
				$fieldSQL .= ",";
				if ($joined) { $fieldSQL .= $this->joinCode."."; } // END if
				$fieldSQL .= $field;
			} // END if
		} // END foreach
		return $fieldSQL;
	}
	/**
	 *	FIELDS FROM SQL.
	 *	Applys data values from a database rwo object to local instance paraeters if they
	 *  have values.
	 *	@param	row		Database row object
	 */
	protected function fieldsFromSQL($row) {
		foreach($this->allFields as $field) {
			if ($field != "id" && isset($row->$field)) {
				$this->$field = $row->$field;
			} // END if
		} // END foreach
	}
	protected function _init() {
		$this->allFields = array_merge($this->allFields,$this->fieldList);
		$this->allFields = array_merge($this->allFields,$this->readOnlyList);
	}
	/**
	 *	UPLOAD FILE.
	 *	Manages uploading files for the current record.
	 *	@param	$type	The type of file being uploaded.
	 *	@param	$path	The files write path on the server
	 *	@param	$input	CodeIgninghter input object
	 *	@param	$hash	(Optional) Value to be used as a hashed file name
	 */
	protected function uploadFile($type,$path,$input,$dataField,$hash = '') {
		//echo("uploadFile = ".$type."<br />");
			
		if (empty($hash) && $this->forceFileNameHash) { $hash = $this->id; }
		$success = false;
		//$target_path = dirname(FCPATH).$path;
		$target_path = DIR_WRITE_PATH.$path;
		//echo("target_path = ".$target_path."<br />");
		
		if (!file_exists($target_path)) {
			$this->errorCode = 5;
			$this->statusMess.= "The directory ".$target_path." does not exist. Please contact the site adminitrator for assitance.";
		} else if (!is_writable($target_path)) {
			$this->errorCode = 6;
			$this->statusMess.= "The directory ".$target_path." cannot be written to. Please contact the site adminitrator for assitance.";
		} else {
			$_FILES[$type.'File']['name'] = str_replace(" ","_",$_FILES[$type.'File']['name']);
			$fileExtArr = explode(".",basename( $_FILES[$type.'File']['name']));
			$savedFileName = '';
			if ($hash != ''){
				$savedFileName = substr(md5($hash),0,24).".".$fileExtArr[1];
			} else {
				$savedFileName = $_FILES[$type.'File']['name'];
			}
			$target_file_name = $target_path.$savedFileName;
			//echo("target_file_name = ".$target_file_name."<br />");
				
			if (file_exists($target_file_name)) {
				if (unlink($target_file_name)) {
					$delSuccess = true;
				} else {
					$delSuccess = false;
				} // END if
			} else {
				$delSuccess = true;
			} // END if
			if ($delSuccess) {
				//echo("_FILES['".$type."File']['tmp_name'] = ".$_FILES[$type.'File']['tmp_name']."<br />");
				//echo("target_file_name = ".$target_file_name."<br />");
				if (move_uploaded_file($_FILES[$type.'File']['tmp_name'], $target_file_name)) {
					chmod($target_file_name,0755);
					$success = true;
					$this->$dataField = $savedFileName;
					$this->statusMess = "File upload completed successfully.";
				} else {
					$this->errorCode = 3;
					$this->statusMess .= "The file upload process did not complete successfully. The file ".basename( $_FILES['attachment']['name'])." could not be saved on the server.";
				} // END if
			} else {
				$this->errorCode = 4;
				$this->statusMess.= "The file upload process did not complete successfully. A file with the same name already exists and could not be removed. Please contact the site adminitrator for assitance.";
			} // END if
		}  // END if
		return $success;
	}
}
?>