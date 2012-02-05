<?php
/**
 *	OOTP LEAGUE MODEL CLASS.
 *
 *	@author			Jeff Fox <jfox015 (at) gmail (dot) com>
 *  @copyright   	(c)2009-11 Jeff Fox/Aeolian Digital Studios
 *	@version		1.0
 *
*/
class ootp_league_model extends base_model {

	/*--------------------------------
	/	VARIABLES
	/-------------------------------*/
	/**
	 *	SLUG.
	 *	@var $_NAME:Text
	 */
	var $_NAME = 'ootp_league_model';

	var $league_id  = -1;
	var $name = '';
	var $abbr = '';
	var $logo_file = '';
	var $start_date  = EMPTY_DATE_STR;
	var $current_date  = EMPTY_DATE_STR;
	var $league_state  = -1;
	var $league_level = -1;
	var $background_color_id = '#1E1E9E';
	var $text_color_id = '#DD0101';
	var $requiredTables = array();
    // UPDATE 1.0.1 - Optional tables to determine v12 or earlier
    var $ootp_version = OOTP_CURRENT_VERSION;
	/*---------------------------------------------
	/
	/	C'TOR
	/	Creates a new instance of ootp_league_model
	/
	/---------------------------------------------*/
	function ootp_league_model() {
		parent::__construct();

		$this->tblName = 'leagues';
		$this->tables['LEAGUE_EVENTS'] = 'league_events';

		$this->fieldList = array();
		$this->conditionList = array();
		$this->readOnlyList = array('league_id', 'name', 'abbr', 'logo_file', 'start_date', 'current_date', 'league_state', 'league_level','background_color_id','text_color_id');

	}
	/*--------------------------------------------------
	/
	/	PUBLIC FUNCTIONS
	/
	/-------------------------------------------------*/
	public function init() {
        $this->requiredTables = array('cities','nations','games','league_events','leagues','players_awards','players',
            'players_batting','players_pitching','players_fielding','players_game_batting',
            'players_career_batting_stats','players_game_pitching_stats',
            'players_career_fielding_stats','players_career_pitching_stats','teams',
            'team_history');
        if (intval($this->ootp_version) >= 12) {
            array_push($this->requiredTables,'states');
        }
        parent::_init();
    }

	// SPECIAL QUERIES
	public function writeConfigDates($start_date,$current_date,$adminConfirm = false) {
		if ((defined('ENVIRONMENT') && ENVIRONMENT != "production") && $adminConfirm) {
			$data = array('start_date' => $start_date, 'current_date' => $current_date);
			$this->db->where('league_id',$this->league_id);
			$this->db->update($this->tblName,$data);
			if ($this->db->affected_rows() > 0) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	/**
	 *	Returns a list of required OOTP data tables that are not currently loaded.
	 *	@return	array
	 */
	public function getMissingTables() {
		$missingTables = array();

		if (sizeof($this->requiredTables) > 0) {
			foreach ($this->requiredTables as $tableName) {
				if (!$this->db->table_exists($tableName)) {
					array_push($missingTables,$tableName);
				}
			}
		}
		return $missingTables;
	}
	public function getRequiredSQLFiles() {
		$fileList = array();

		if (sizeof($this->requiredTables) > 0) {
			foreach ($this->requiredTables as $tableName) {
				array_push($fileList,$tableName.'.mysql.sql');
			}
		}
		return $fileList;
	}
	/**
	 *	VALIDATE LOADED SQL FILES
	 *	This function accepts an array of MySQL export files names and determines which if any of the
	 * 	required files are missing. Returns a list of required OOTP sql files that are not
	 *	cfound in the passed file list.
	 *	@param	$fileList	Array of SQL files
	 *	@param	$extention	Custom file extension (if different form OOTP export defualt)
	 *	@return	array
	 */
	public function validateLoadedSQLFiles($fileList = array(), $extension = '.mysql.sql') {
		$missingTables = array();
		$loadedTables = array();
		foreach ($fileList as $file) {
			$ex = '';
			if (strpos($file,".")) {
				$name = explode(".",$file);
				$ex = $name[0];
			} else {
				$ex = $file;
			}
			array_push($loadedTables,$ex);
		}

		if (sizeof($this->requiredTables) > 0) {
			foreach ($this->requiredTables as $tableName) {
                $found = false;
				if (!in_array($tableName,$loadedTables)) {
					array_push($missingTables,$tableName.$extension);
				}
			}
		}
		return $missingTables;
	}
	/**
	 *	Returns a list of public leagues.
	 *	@return	TRUE or FALSE
	 */
	public function in_season() {
		if ($this->league_id != -1) {
			if ($this->league_state > 1 && $this->league_state < 4) {
				return true;
			} else {
				return false;
			}
		} else {
			$this->statusMess = 'Required OOTP database tables have not been loaded.';
			return;
		}
	}
	public function get_state() {
		$state = '';
		if ($this->league_id != -1) {
			switch ($this->league_state) {
				case 4:
					$state = "Off Season";
					break;
				case 3:
					$state = "Playoffs";
					break;
				case 2:
					$state = "Regular Season";
					break;
				case 1:
					$state = "Spring Training";
					break;
				case 0:
					$state = "Preseason";
					break;
			}
		} else {
			$this->errorCode = 1;
			$this->statusMess = 'Required OOTP database tables have not been loaded.';
		}
		return $state;
	}
	public function getNextEvents($count = 3) {
		$events = array();
		if ($this->league_id != -1) {
			if ($this->db->table_exists($this->tables['LEAGUE_EVENTS'])) {
				$this->db->select('start_date,name');
				$this->db->from($this->tables['LEAGUE_EVENTS']);
				$this->db->where('league_id',$this->league_id);
				$this->db->where('start_date >',$this->current_date);
				$this->db->not_like('name','Announcement');
				$this->db->order_by('start_date','asc');
				$query = $this->db->get();
				if ($query->num_rows() > 0) {
					$pushCount = 0;
					foreach($query->result() as $row) {
						array_push($events,array('name'=>$row->name,'start_date'=>$row->start_date));
						$pushCount++;
						if ($pushCount == $count) break;
					}
				}
				$query->free_result();
			} else {
				$this->errorCode = 2;
				$this->statusMess = 'Required OOTP database table '.$this->tables['LEAGUE_EVENTS'].' has not been loaded. No events could be displayed at this time.';
			}
		} else {
			$this->errorCode = 1;
			$this->statusMess = 'Required OOTP database tables have not been loaded.';
		}
		return $events;

	}
	public function getAllSeasons() {
		##### Get Seasons #####
		$years = array();

		$sql="SELECT DISTINCT year FROM players_career_batting_stats WHERE league_id=".$this->league_id." GROUP BY year ORDER BY year DESC;";
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			foreach($query->result_array() as $row) {
			   array_push($years,$row['year']);
			}
		}
		$query->free_result();
		return $years;
	}

	public function load($id,$field = 'id') {
		// TEST IF OOTP DATA FILES HAVE BEEN UPLOADED OR NOT
		$query = $this->db->query("SHOW TABLES LIKE '".$this->tblName."'");
		if ($query->num_rows() > 0) {
			$query->free_result();
			return parent::load($id,$field,false);
		} else {
			$this->errorCode = 1;
			$this->statusMess = 'Required OOTP database tables have not been loaded.';
			return false;
		}
	}
	/*---------------------------------------
	/	PRIVATE/PROTECTED FUNCTIONS
	/--------------------------------------*/

}