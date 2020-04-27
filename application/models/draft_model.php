<?php
/**
 *	DRAFT MODEL CLASS.
 *	This class interfaces with the draft config table specifically, but also 
 *	interacts with and manipulates the fantasy draft and draft list tables.
 *
 *	@author			Jeff Fox (Github ID: jfox015)
 *	@author			Frank Esselink	(fhommes) Where noted
  *	@version		1.0.5
 *  @lastModified	04/17/20
  */
class draft_model extends base_model {
	/*--------------------------------
	/	VARIABLES
	/-------------------------------*/
	/**
	 *	SLUG.
	 *	@var $_NAME:Text
	 */
	var $_NAME = 'draft_model';
	
	var $league_id = -1;
	var $draftEnable = -1;
	var $draftDate = EMPTY_DATE_TIME_STR;
	var $nRounds = -1;
	var $pauseAuto = -1;
	var $setAuto = -1;
	var $autoOpen = -1;
	var $timerEnable = -1;
	var $flexTimer = -1;
	var $timePick1 = EMPTY_TIME_STR;
	var $enforceTimer = -1;
	var $emailDraftSummary = -1;
	var $emailOwnersForPick = -1;
	var $completed = -1;
	var $draftBy = 1;
	var $debug = false;
	
	/*---------------------------------------------
	/
	/	C'TOR
	/	Creates a new instance of draft_model
	/
	/---------------------------------------------*/
	function draft_model() {
		parent::__construct();
		
		$this->tblName = 'fantasy_draft_config';
		$this->tables['DRAFT_LIST'] = 'fantasy_draft_list';
		$this->tables['DRAFT'] = 'fantasy_draft';
		$this->tables['TEAMS'] = 'fantasy_teams';
		
		$this->fieldList = array('league_id','draftEnable','nRounds','dispLimit','pauseAuto','setAuto','autoOpen','timerEnable','flexTimer','enforceTimer','dStartDt','timePick1','timePick2','rndSwitch','timeStart','timeStop','pauseWkEnd','emailDraftSummary','emailOwnersForPick','draftInjured','draftBy');
		$this->conditionList = array('whenDraft');
		$this->readOnlyList = array('draftDate','completed','dStartTm');  
		
		parent::_init();
	}
	/*---------------------------------------
	/	PUBLIC FUNCTIONS
	/--------------------------------------*/
	/**
	 * 	APPLY DATA.
	 *
	 *	Applies custom data values to the object. 
	 *
	 * 	@return 	TRUE on success, FALSE on failure
	 *
	 */
	public function applyData($input,$userId = -1) {
		if (parent::applyData($input,$userId)) {
			if ($input->post('whenDraft'))
				$this->draftDate = date('Y-m-d',strtotime($this->input->post('whenDraft')));
			// END if
			if ($input->post('startTimeH') && $input->post('startTimeM') && $input->post('startTimeA')) {
				$timeStart = $input->post('startTimeH');
				if ($input->post('startTimeA') != "AM" && $input->post('startTimeH') < 12) { $timeStart = $input->post('startTimeH') + 12; }
				$this->draftDate .= " ".$timeStart.':'.$input->post('startTimeM').':00';
			}
			/*$this->timeStop = EMPTY_DATE_STR;
			if ($input->post('stopTimeH') && $input->post('stopTimeM') && $input->post('stopTimeA')) {
				$timeStop = $input->post('startTimeH');
				if ($input->post('stopTimeA') != "AM" && $input->post('stopTimeH') < 12) { $timeStart = $input->post('stopTimeH') + 12; }
				$this->timeStop .= " ".$timeStop.':'.$input->post('stopTimeM').':00';
			}*/
			return true;
		} else {
			return false;
		} // END if
	}
	/**
	 *	SAVED DRAFT SETTINGS.
	 *
	 *	Captures and saves draft settings using the codeigniter $input object 
	 *	as it's main data source
	 *
	 *	@param	$input		CodeIgniter Input Object
	 *	@param	$league_id	Optional league Id param. If not passed, the internal league id is used
	 *	@param	$year 		The draft year. If not specified, the current year is used.
	 *	@param	$debug		TRUE or FALSE
	 *	@return				TRUE on success, FALSE on error
	 */
	public function savedDraftSettings($input, $league_id = false, $year = false, $debug = false) {
		
		if ($league_id === false) { $league_id = $this->league_id; }
		if ($year === false) { $year = date('Y'); }
		
		$updText="";
		$teamCount = $input->post('teamCount');
		$round=$input->post('round');
		$applyToRem=$input->post('applyToRem');
		$applySerp=$input->post('applySerp');
		if ($debug==1) {
			echo "In draft order adjuster<br />\n";
			echo "teamCount: $teamCount<br />\n";
			echo "round: $round<br />\n";
			echo "applyToRem: $applyToRem<br />\n";
			echo "applySerp: $applySerp<br />\n";
		}
		for ($i=1;$i<=$teamCount;$i++) {
			$tkey='pick_'.$i;
			$tid=$input->post($tkey);
			$ovrPick=$i+$teamCount*($round-1);
			if ($applySerp==1)  {
				$rndRem=$round%2;
				$sqlUpdate="UPDATE ".$this->tables['DRAFT']." SET team_id=$tid WHERE league_id=".$this->input->post('league_id')." AND year=$year AND pick_round=$i AND round>=$round AND MOD(round,2)=$rndRem;\n";
				$sqlUpdate.="UPDATE ".$this->tables['DRAFT']." SET team_id=$tid WHERE league_id=".$this->input->post('league_id')." AND year=$year AND pick_round=".($teamCount-$i+1)." AND round>=$round AND MOD(round,2)!=$rndRem;\n";
				if ($debug==1) {
					echo("Round = ".$round."<br />");
					echo("Round remainder = ".$rndRem."<br />");
					echo("sqlUpdate = ".$sqlUpdate."<br />");
				}
				$updText.= $sqlUpdate;
			} else if ($applyToRem==1) {
				$updText.="UPDATE ".$this->tables['DRAFT']." SET team_id=$tid WHERE league_id=".$this->input->post('league_id')." AND year=$year AND pick_round=$i AND round>=$round;\n";
			} else {
				$updText.="UPDATE ".$this->tables['DRAFT']." SET team_id=$tid WHERE league_id=".$this->input->post('league_id')." AND year=$year AND pick_overall=$ovrPick;\n";
			}
		}
		// Save Draft Changes
		if ($updText!="") {
			$this->saveDraftOrder($updText);
		}
		return true;
	}
	/**
	 *	DELETE DRAFT SETTINGS.
	 *
	 *	Deletes the settings record for the passed league. Used mainly for league deletion operations.
	 *
	 *  Rewritten for PROD 1.0.3 due to issues with the query builder deleting the record and causing an error.
	 *
	 *	@param	$league_id	Optional league Id param. If not passed, the internal league id is used
	 *	@return				TRUE on success, FALSE on error
	 */
	public function deleteDraftSettings($league_id = false) {
		
		if ($league_id === false) { $league_id = $this->league_id; };
		$sql = "SELECT id FROM ".$this->tblName." WHERE league_id = ".$league_id;
		$query = $this->db->query($sql);
		$id = -1;
		if ($query->num_rows() > 0) {
			$row = $query->result();
			$id = $row[0]->id;
		}
		$sql2 = "DELETE FROM ".$this->tblName." WHERE id = ".$id;
		$this->db->query($sql2);
		return true;
	}
	/**
	 *	DELETE CURRENT DRAFT.
	 *
	 *	Deletes all records associated with the current leagues draft for the 
	 *	specified year.
	 *
	 *	@param	$league_id	Optional league Id param. If not passed, the internal league id is used
	 *	@param	$year 		The draft year. If not specified, the current year is used.
	 *	@return				TRUE on success, FALSE on error
	 */
	public function deleteCurrentDraft($league_id = false, $year = false) {
		
		if ($league_id === false) { $league_id = $this->league_id; }
		if ($year === false) { $year = date('Y'); }
		
		$this->db->where('league_id',$league_id);
		$this->db->where('year',$year);
		$this->db->delete($this->tables['DRAFT']);
		
		//if ($this->id == -1) {
		//	$this->load($league_id,'league_id');
		//}
		//$this->completed = -1;
		//$this->save();
		
		return true;
	}
	/**
	 *	SAVE DRAFT ORDER.
	 *
	 *	Saves the SQL output of the schedule draft function.
	 *
	 *	@see	#sheduleDraft()
	 *	
	 *	@param	$sql		SQL Query String
	 *	@param	$league_id	Optional league Id param. If not passed, the internal league id is used
	 *	@param	$year 		The draft year. If not specified, the current year is used.
	 *	@return				<void>
	 */
	public function saveDraftOrder($sql, $league_id = false, $year = false) {
		
		if ($league_id === false) { $league_id = $this->league_id; }
		if ($year === false) { $year = date('Y'); }
		
		$e=explode("\n",$sql);
		foreach ($e as $key => $query) {
			if (!empty($query)) {
				$this->db->query($query);
			}
		}
	}
	/*-----------------------------------------------------------------------------
	/
	/	GET FUNCTIONS
	/
	/----------------------------------------------------------------------------*/
	/**
	 *	GET DRAFT ENABLED.
	 *
	 *	Returns whether the draft is enabled or not.
	 *	
	 *	@param	$league_id	Optional league Id param. If not passed, the internal league id is used
	 *	@return				TRUE if enabled, FALSE if not
	 */
	public function getDraftEnabled($league_id = false) {
		
		if ($league_id === false) { $league_id = $this->league_id; }
		
		if ($this->id = -1) {
			$this->load($league_id,'league_id');
		}
		return $this->draftEnable;
	}
	/**
	 *	GET DRAFT STATUS.
	 *
	 *	Returns the current status of the draft. The meanings of each returned code are:
	 *	<ul>
	 *		<li><b>-1, 0</b> - Draft not yet initalized or configured</li>
	 *		<li><b>1</b> - Draft Configured, start date not yet reached</li>
	 *		<li><b>2</b> - Start Date reached, no picks made yet</li>
	 *		<li><b>3</b> - Draft in progress. At least one pick made, last pick not yet made</li>
	 *		<li><b>4</b> - All picks are made. Waiting for completion by commmisioner.</li>
	 *		<li><b>5</b> - Draft completed. Draft picvks applied to team rosters.</li>
	 *	</ul>
	 *
	 *	@param	$league_id	Optional league Id param. If not passed, the internal league id is used
	 *	@param	$year 		The draft year. If not specified, the current year is used.
	 *	@return				Draft Status Code
	 *
	 */
	public function getDraftStatus($league_id = false, $year = false) {
		
		if ($league_id === false) { $league_id = $this->league_id; }
		if ($year === false) { $year = date('Y'); }	
		
		if ($this->id = -1) {
			$this->load($league_id,'league_id');
		}
		$status = -1;
		if ($this->completed == 1) {
			$status = 5;
		} else {
			$max = $this->getDraftMax($league_id, $year);
			if ($max == 0) {
				// DRAFT NOT YET SET UP, NO SCHEUDLE CREATED YET
				$status = 0;
			} else {
				$today = time()+(60*60*3);
				
				$draftArray = explode(" ",$this->draftDate);
				$draftStartDate = $draftArray[0];
				//echo("this->draftDate = ".$this->draftDate."<br />");
				//echo("draftStartDate = ".$draftStartDate."<br />");
				//echo("Server Time = ".date('m/d/y h:m:s A',$today)."<br />");
				//echo("draft starts Time = ".date('m/d/y h:m:s A',strtotime($draftStartDate." ".$this->dStartTm))."<br />");
				if ($this->draftDate != EMPTY_DATE_TIME_STR && $today < strtotime($this->draftDate)) {
					$status = 1;
				} else {
					$hasFirst = false;
					$hasLast = false;
					// SCHEDULE CREATED, TEST IF PICKS HAVE BEEN MADE
					$first_pick = $this->getPickInfo(1,$league_id, $year);
					$last_pick = $this->getPickInfo($max,$league_id, $year);
					if (sizeof($first_pick) > 0 && isset($first_pick['player_id']) && !empty($first_pick['player_id'])) {
						$hasFirst = true;
					}
					if (sizeof($last_pick) > 0 && isset($last_pick['player_id']) && !empty($last_pick['player_id'])) {
						$hasLast = true;
					}
					//echo("Has first = ".(($hasFirst) ? 'true' : 'false')."<br />");
					//echo("Has last = ".(($hasLast) ? 'true' : 'false')."<br />");
					if (!$hasFirst && !$hasLast) {
						$status = 2;
					} else if ($hasFirst && !$hasLast) {
						$status = 3;
					} else if ($hasFirst && $hasLast) {
						$status = 4;
					}
					
				}
			}
		}
		//echo("Draft status = ".$status."<br />");
		return $status;
	}
	/**
	 *	GET CURRENT PICK.
	 *
	 *	Captures and saves draft settings using the codeigniter $input object 
	 *	as it's main data source
	 *
	 *	@param	$input		CodeIgniter Input Object
	 *	@param	$league_id	Optional league Id param. If not passed, the internal league id is used
	 *	@param	$year 		The draft year. If not specified, the current year is used.
	 *	@param	$debug		TRUE or FALSE
	 *	@return				TRUE on success, FALSE on error
	 */
	public function getCurrentPick($league_id = false, $year = false) {
		if ($league_id === false) { $league_id = $this->league_id; }
		if ($year === false) { $year = date('Y'); }	
		
		$pick = false;
		$results = $this->getDraftResults(false, $league_id, $year);
		$count = 1;
		if (sizeof($results > 0)) {
			foreach($results as $row) {
				if (!isset($row['player_id']) || empty($row['player_id']) || ($row['player_id'] < 1 && $row['player_id'] != -999)) {
					$pick = $row;
					break;
				}
			}
		}
		return $pick;
	}
	/**
	 *	GET DRAFT MAX.
	 *
	 *	Returns the highest overall pick from the specified leagues draft.
	 *
	 *	@param	$league_id	Optional league Id param. If not passed, the internal league id is used
	 *	@param	$year 		The draft year. If not specified, the current year is used.
	 *	@return				The highest overall pick value
	 */
	public function getDraftMax($league_id = false, $year = false) {
		if ($league_id === false) { $league_id = $this->league_id; }
		if ($year === false) { $year = date('Y'); }	
		
		$max = 0;
		$sql = "SELECT MAX(pick_overall) as maxPick FROM ".$this->tables['DRAFT']." WHERE league_id= ".$league_id." AND year=".$year;
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			$row = $query->row();
			$max = (!isset($row->maxPick) || $row->maxPick == NULL || empty($row->maxPick) || $row->maxPick == '1') ? 0 : $row->maxPick;
		}
		return $max;
	}
	/**
	 *	GET DRAFT DATE.
	 *
	 *	Returns the date of the specified leagues draft or -1 if it is not yet set.
	 *
	 *	@param	$league_id	Optional league Id param. If not passed, the internal league id is used
	 *	@return				The highest overall pick value
	 */
	public function getDraftDate($league_id = false) {
		if ($league_id === false) { $league_id = $this->league_id; }
		
		$draft_date = EMPTY_DATE_TIME_STR;
		$sql = "SELECT draftDate, dStartTm FROM ".$this->tblName." WHERE league_id= ".$league_id;
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			$row = $query->row();
			if (isset($row->draftDate) && $row->draftDate != EMPTY_DATE_TIME_STR) {
				$draft_date = date('Y-m-d h:i:s A',strtotime($row->draftDate));
			}
		}
		return $draft_date;
	}
	/**
	 *	GET DRAFT Elidgibility.
	 *
	 *	Returns the date of the specified leagues draft or -1 if it is not yet set.
	 *
	 *	@param	$league_id	Optional league Id param. If not passed, the internal league id is used
	 *	@return				The highest overall pick value
	 */
	public function getDraftElidgibility($player_id = false, $league_id = false) {
		
		if ($player_id === false || $player_id == -1) { return; }
		
		if ($league_id === false) { $league_id = $this->league_id; }
		
		$this->db->select('id');
		$this->db->from($this->tables['DRAFT']);
		$this->db->where('league_id',$league_id);
		$this->db->where('player_id',$player_id);
		return ($this->db->count_all_results()== 0);
	}
	/**
	 *	GET PICK INFO.
	 *
	 *	Returns player and team IDs for a leagues draft. To get information for one 
	 *	specific pick, simply pass a $pick_overall value.
	 *
	 *	@param	$pick_overall	Optional overall pick ID to limit results to a single pick record.
	 *	@param	$league_id		Optional league Id param. If not passed, the internal league id is used
	 *	@param	$year 			The draft year. If not specified, the current year is used.
	 *	@return					Array with player and team ID values.
	 */
	public function getPickInfo($pick_overall = false,$league_id = false, $year = false) {
		
		if ($league_id === false) { $league_id = $this->league_id; }
		if ($year === false) { $year = date('Y'); }		
		
		$pick = array();
		$this->db->select('player_id,team_id');
		$this->db->from($this->tables['DRAFT']);
		$this->db->where('league_id',$league_id);
		$this->db->where('year',$year);
		$this->db->where('pick_overall',$pick_overall);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				$pick = array('player_id'=>$row->player_id,'team_id'=>$row->team_id);
			}
		}
		return $pick;
	}
	/**
	 *	GET TEAM DRAFT PICKS.
	 *
	 *	Returns the round ID and the team ID for a particular team.
	 *
	 *	@params	$round			the round ID
	 *	@param	$league_id		Optional league Id param. If not passed, the internal league id is used
	 *	@param	$year 			The draft year. If not specified, the current year is used.
	 *	@return					Array with the round pick and team ID values.
	 */
	public function getTeamPicks($round = false, $league_id = false, $year = false) {
		
		if ($league_id === false) $league_id = $this->league_id;
		if ($year === false) $year = date('Y');
		if ($round === false) $curRound = 1;
		
		$picks = array();
		$this->db->select('pick_round,team_id');
		$this->db->from($this->tables['DRAFT']);
		$this->db->where('league_id',$league_id);
		$this->db->where('year',$year);
		$this->db->where('round',$round);
		$this->db->order_by('pick_round');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				$picks = $picks + array($row->pick_round=>$row->team_id);
				
			}
		}
		return $picks;
	}	
	/**
	 *	GET TAKEN PICKS.
	 *
	 *	Returns all pick rows in which the player ID is not null or > 0.
	 *
	 *	@param	$league_id		Optional league Id param. If not passed, the internal league id is used
	 *	@param	$year 			The draft year. If not specified, the current year is used.
	 *	@return					Array with player and overall pick ID values.
	 */
	public function getTakenPicks($league_id = false, $year = false) {
		
		if ($league_id === false) $league_id = $this->league_id;
		if ($year === false) $year = date('Y');
		
		$results = array();
		$this->db->select('pick_overall, player_id');
		$this->db->from($this->tables['DRAFT']);
		$this->db->where('league_id',$league_id);
		$this->db->where('year',$year);
		$this->db->where('player_id > 0');
		$query =  $this->db->get();
		if ($query->num_rows() > 0) {
			foreach($query->result_array() as $row) {
				array_push($results, $row);
			}
		}
		return $results;
	}
	/**
	 *	GET DRAFT RESULTS.
	 *
	 *	Returns all pick information for a leagues draft. The range can be narrowed by passing in 
	 *	a round or team ID.
	 *
	 *	@param	$round			Optional round parameter
	 *	@param	$league_id		Optional league Id param. If not passed, the internal league id is used
	 *	@param	$year 			Optional draft year. If not specified, the current year is used.
	 *	@param	$team_id 		Optional team ID parameter.
	 *	@return					Array with all draft result data.
	 */
	public function getDraftResults($round = false, $league_id = false, $year = false, $team_id = false) {
		
		if ($league_id === false) $league_id = $this->league_id;
		if ($year === false) $year = date('Y');
		
		$results = array();
		$this->db->select('round,pick_round,pick_overall,due_date,due_time,team_id,player_id');
		$this->db->from($this->tables['DRAFT']);
		$this->db->where('league_id',intval($league_id));
		$this->db->where('year',intval($year));
		if ($team_id !== false) {
			$this->db->where('team_id',intval($team_id));
		}
		if ($round !== false) {
			$this->db->where('round',intval($round));
		}
		$this->db->order_by('pick_overall');
		$query =  $this->db->get();
		//echo($this->db->last_query()."<br />");
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				array_push($results,array('round'=>$row->round,'pick_round'=>$row->pick_round,
											'pick_overall'=>$row->pick_overall,'due_date'=>$row->due_date,
											'due_time'=>$row->due_time,'team_id'=>$row->team_id,
											'player_id'=>$row->player_id));
			}
		}
		return $results;
	}
	
	/*-----------------------------------------------------------------------------
	/
	/	DRAFT OPERATIONS
	/
	/----------------------------------------------------------------------------*/
	/**
	 *	SET WAIVER ORDER.
	 *
	 *	Sets the initial order of waivers following the draft. This uses the reverse of the team order 
	 *	of the first round of the draft.
	 *
	 *	@param	$waiverOrder	(Array)		The draft order of teams 
	 *	@param	$league_id		(integer)	League Id param. If not passed, the internal league id is used
	 *	@return					(Boolean}	TRUE on success, FALSE on failure
	 *	@since	1.0
	 */
	public function setWaiverOrder($waiverOrder = array(), $league_id = false) {
		
		if (!isset($waiverOrder) || sizeof($waiverOrder) == 0) { return; }
		if ($league_id === false) { $league_id = $this->league_id; }
	
		$teamsUpdated = 0;
		$rank =1;
		for($i=(sizeof($waiverOrder)-1); $i > -1; $i--) {
			$this->db->set('waiver_rank',$rank);
			$this->db->where('id',$waiverOrder[$i]);
			$this->db->update($this->tables['TEAMS']);
			if ($this->db->affected_rows() == 1) {
				$teamsUpdated++;
			}
			$rank++;
		}
		if ($teamsUpdated == sizeof($waiverOrder)) {
			return true;
		} else {
			return false;
		}
	}
	/**
	 * 	CREATE DRAFT ORDER.
	 * 	This function analyses the number of teams and generates a random draft order. 
	 * 	
	 * EDIT 1.0.5: Broke apart draft order and scheduling functions.
	 *
	 *	@params	$teams			Array of teams for the current league. Has to be passed since we can't invoke the team_modle class within this model.
	 *	@param	$league_id		Optional league Id param. If not passed, the internal league id is used
	 *	@param	$year 			The draft year. If not specified, the current year is used.
	 *	@param	$debug			TRUE or FALSE
	 *	@return					Array of drafted player IDs.
	 *	@since	1.0.5
	 */
	public function createDraftOrder($teams = array(),$league_id = false, $year = false, $debug = false) {
		
		if ($league_id === false) { $league_id = $this->league_id; }
		if ($year === false) { $year = date('Y'); }		
		
		$chkYr=2;
		## Get Default Draft Order
		## Check If were are in the draft period yet or not
		$currDate = time();
		if ($this->draftDate != EMPTY_DATE_TIME_STR && $currDate < strtotime($this->draftDate)){
			$chkYr=1;
		}
		$teamPicks = array();
		$drafted = array();
		$order = array();
		##### Get Teams #####
		$pick = 1;
		foreach($teams as $teamId => $val) {
			if ($debug===true) {
				echo("team ID = ".$teamId."<br />");
				echo("Pick = ".$pick."<br />"); 
			}
			$teamPicks[$pick]=$teamId;
			$order[$teamId]=0;
			$pick++;
		}
		$nTeams=sizeof($teams);
		
		##### Use Last Year's Win Pct #####W
		if ($chkYr != 2) {
			// TODO: CODE THIS FUNCTIONALITY OUT LATER
		} //else {
			// CREATE RANDOM DRAFT ORDER
			//$pick = 1;
			//foreach($teams as $team) {
			//	$teamPicks[$pick]=$team;
			//	$pick++;
			//}
		//}
		shuffle($teamPicks);
		
		if ($debug===true) {
			echo "In ".$this->_NAME." draftSchedule<br />";
			echo "league_id: $league_id<br />";
			echo "nTeams: $nTeams<br />\n";
		}
		
		$updText = "";
		$firstPick= 0;
		$lastPick = intval($nTeams*$this->nRounds);
		$maxPick = intval($this->getDraftMax());
			
		## Create Pick Structure
		if ($lastPick>$maxPick) {
			for ($i=$maxPick+1;$i<=$lastPick;$i++) {
				$pick=($i+$nTeams-1)%$nTeams + 1;
				if ($debug==1) {
					echo("Curr pick = ".$pick."<br />");
					echo("picks array $pick set? = ".(isset($teamPicks[$pick-1]) ? 'true' : 'false')."<br />"); 
				}
				$round=($i-$pick)/$nTeams + 1;
				$updText.="INSERT INTO ".$this->tables['DRAFT']." (league_id,year,pick_overall,round,pick_round,team_id) VALUES (".$league_id.",$year,$i,$round,$pick,".$teamPicks[$pick-1].")\n";
			}
		}
		if ($lastPick<$maxPick) {
			$updText.="DELETE FROM ".$this->tables['DRAFT']." WHERE league_id=".$league_id." AND year=$year AND pick_overall>$lastPick;\n";
		}
		if ($debug===true) {
			echo("Update text size = ".strlen($updText)."<br />");
			echo("----------------------------<br />");
			echo($updText."<br />");
			echo("----------------------------<br />");
			echo "timerEnable: ".$this->timerEnable."<br />\n";
		}
		if (!empty($updText)) {
			return $this->saveDraftOrder($updText);
		} else {
			return false;
		}
	}
	/**
	 *	SCHEDULE DRAFT PICKS.
	 *
	 *	If the $timerEanbled property of the current league is enabled, this function will add pick due dates and 
	 *	times to each pick entry as well.
	 *
	 *	EDIT 1.0.5: Broke apart draft order and scheduling functions.
	 *
	 *	@params	$teams			Array of teams for the current league. Has to be passed since we can't invoke the team_modle class within this model.
	 *	@param	$league_id		Optional league Id param. If not passed, the internal league id is used
	 *	@param	$year 			The draft year. If not specified, the current year is used.
	 *	@param	$debug			TRUE or FALSE
	 *	@return					Array of drafted player IDs.
	 *	@since	1.0
	 */
	public function draftSchedule($teams = array(),$league_id = false, $year = false, $debug = false) {
		
		if ($league_id === false) { $league_id = $this->league_id; } // END if
		if ($year === false) { $year = date('Y'); }	 // END if	
		
		// CREATE SCHEDULE ONLY IF THE TIMER IS ENABLED
		if ($this->timerEnable == 1) { 
			// GET CURRENT SETTINGS
			$timePerPick = $this->timePick1;

			## Determine and schedule first pick
			$firstPick=99999;
			$nTeams = sizeof($teams);
			$lastPick = intval($nTeams*$this->nRounds);
			$firstPickRnd = -1;
			$result = $this->getDraftResults();
			
			foreach($result as $row) {
				$pick_overall = $row['pick_overall'];
				$player_id = $row['player_id'];
				$round = $row['round'];
				if (($player_id=="") && ($firstPick > $pick_overall)) {
					$firstPick= $pick_overall;
					$firstPickRnd = $round;
				} // END if
				$picks[$pick_overall] = $player_id;
			} // END foreach
			if ($firstPick==99999) {
				$firstPick=1;
				$firstPickRnd=1;
			} // END if
			if ($debug === true) {
				echo "Start at: ".date("Y-m-d H:m:s A",strtotime($this->draftDate))."<br />\n";
				echo "Size of getDraftResults = ".sizeof($result)."<br />\n"; 
				echo "firstPick: $firstPick<br />\n";
				echo "firstPickRnd: $firstPickRnd<br />\n";
			} // END if

			$startTime = strtotime($this->draftDate);
			$now = time();
			
			if ($startTime > $now) {
				$pickTime = $startTime + $timePerPick;
			} else {
				$pickTime = $now + $timePerPick;
			}
			
			
			/*$startInst = mktime(date("H",strtotime($this->draftDate)),date("i",strtotime($this->draftDate)),0,date("m",strtotime($this->draftDate)),date("d",strtotime($this->draftDate)),date("Y",strtotime($this->draftDate)));
			$pickInst =  mktime(date("H",strtotime($this->draftDate)),date("i",strtotime($this->draftDate)),0,date("m",strtotime($this->draftDate)),date("d",strtotime($this->draftDate)),date("Y",strtotime($this->draftDate)));
			
			$timeStart = $startInst;
			$now = time();
			
			$timeStop = strtotime($this->timeStop);
			
			if (($pickInst < $now)||($pickInst < $startInst)) {
				$nowDate=date("Y-m-d",$now);
				$nowTime = strtotime(date("H:i:s",$now)." + $timePerPick minutes");
				if ($nowTime > $timeStop) {
					$now=date("Y-m-d",strtotime($nowDate." + 1 day"));
					$now=strtotime($now." ".date("H:i:00",strtotime($this->draftDate)));
					//echo date("Y-m-d H:i:s",$now)."::".date("H:i:00",strtotime($this->draftDate))." - too late<br/>";
				} // END if
			} else if (
				$nowTime < strtotime($this->draftDate)) {
				$now=strtotime($nowDate." ".date("H:i:00",strtotime($this->draftDate)));
				//echo date("Y-m-d H:i:s",$now)."::".date("H:i:00",strtotime($this->draftDate))." - too early<br/>";
			} // END if
			$dStartDt = date("Y-m-d",$now);
			$dStartTm = date("H:i:00",$now);
		
			$pickDt = strtotime($dStartDt);
			$pickTm = strtotime($dStartTm." + $timePerPick minutes");*/
			
			if ($debug===true) {
				echo("-------------------------------------------------<br />");
				echo("Draft Schcule for league id = ".$league_id."<br />");
				echo("-------------------------------------------------<br />");
				echo "Pick 1.1.1 Start: ".date("Y-m-d",$pickTime)." ".date("H:i:s",$pickTime)."<br/>";
			} // END if
			$this->draftPlayer(date("Y-m-d",$pickTime),date("H:i",$pickTime), NULL, false, $firstPick, $league_id);
			
			## Process Remaining Picks
			for ($i = $firstPick + 1;$i <= $lastPick; $i++) {
				if (isset($picks[$i])) {continue;}
				$pick = ($i+$nTeams-1)%$nTeams+1;
				$round = ($i-$pick)/$nTeams+1;
				//$pickDt = strtotime(strftime("%x",$pickDt)." + 1 day");
				$pickTime = $pickTime + $timePerPick;
				
				/*if ($adjTm > $this->timeStop) {
					$pickTm = $adjTm - $timeStop + $timeStart;
					$pickDt = strtotime(strftime("%x",$pickDt)." + 1 day");
					 $dow = date("w",$pickDt);
					if (($dow > 5) && ($this->pauseWkEnd == 1)) {
						$pickDt = strtotime(strftime("%x",$pickDt)." + ".(8-$dow)." days");
					} // END if
				} else {
					 $pickTm = $adjTm;
				} // END if*/
				$this->draftPlayer(date("Y-m-d",$pickTime),date("H:i",$pickTime), NULL, false, $i, $league_id);
				if ($debug===true) {
					echo "Pick $i.$round.$pick ".date("w l M d, Y",$pickTime)." ".date("H:i:s",$pickTime)."<br/>";
					echo($this->db->last_query()."<br />");
				} // END if
			} // END for
		} // END if ($this->timerEnable == 1)
		return true;
	}
	/**
	 *	SAVE DRAFT DEFAULTS.
	 *
	 *	Clones the default draft config settings and applies them to a new league record.
	 *
	 *	@param	$league_id		Optional league Id param. If not passed, the internal league id is used
	 *	@param	$year 			The draft year. If not specified, the current year is used.
	 *	@return					TRUE on success, FALSE on error.
	 */
	public function setDraftDefaults($league_id = false, $year = false) {
				
		if ($league_id === false) $league_id = $this->league_id;
		
		if ($year === false) $year = date('Y');
		
		$this->load(1);
		$this->id = -1;
		$this->league_id = $league_id;
		$this->draftDate = date("Y-m-d h:i:s");

		if ($this->save()) {
			return true;
		} else {
			return false;
		}
	}
	/**
	 *	DRAFT PLAYER.
	 *	Submits a player to be drafted. This option can also be used to perform maintenance (such as voiding 
	 *	picks, or managing pick due date/time) as well. 
	 *	
	 *	@param	$due_date		Optional round parameter
	 *	@param	$due_time		Optional league Id param. If not passed, the internal league id is used
	 *	@param	$player_id 		Optional draft year. If not specified, the current year is used.
	 *	@param	$team_id 		Optional team ID parameter.
	 *	@param	$pick_overall 	Optional team ID parameter.
	 *	@param	$league_id 		Optional team ID parameter.
	 *	@return					Array with all draft result data.
	 *	@since	1.0
	 */
	public function draftPlayer($due_date = false, $due_time = false, $player_id = false, $team_id = false, $pick_overall = false, $league_id = false
								) {
		
		if ($league_id === false) $league_id = $this->league_id;
		if ($due_date === false && $due_time === false && $player_id === false && $team_id === false && $pick_overall === false) { return; }
		
		if ($player_id !== false) {
			$this->db->set('player_id',$player_id);
		}
		if ($due_date !== false) {
			$this->db->set('due_date',$due_date);
		}
		if ($due_time !== false) {
			$this->db->set('due_time',$due_time);
		}
		if ($team_id !== false) {
			$this->db->where('team_id',$team_id);
		}
		if ($pick_overall !== false) {
			$this->db->where('pick_overall',$pick_overall);
		}
		if ($league_id !== false) {
			$this->db->where('league_id',$league_id);
		}
		$this->db->update($this->tables['DRAFT']);
		
		if ($player_id !== false && $player_id !== NULL) {
			$this->removeFromDraftLists($player_id, $league_id);
		}
		return true;
		
	}
	/**
	 *	ROLLBACK PICK.
	 *
	 *	Sets all player_id values to NULL from the highest pick made, back to and including the value of <code>pick_overall</code>.
	 *
	 *	@param		$pick_overall	{int}		Overall pick ID
	 *	@param		$league_id		{int}		Optional league Id param. If not passed, the internal league id is used
	 *	@param		$year			{int}		Draft year
	 *	@return						{Boolean}	TRUE on success, FALSE on error
	 *
	 *	@since		1.0
	 *	@access		public
	 */
	public function rollbackPick($pick_overall = false, $league_id = false, $year = false) {
		
		if ($pick_overall === false) { return; }
		
		if ($league_id === false) $league_id = $this->league_id;
		if ($year === false) $year = date('Y');
		$this->db->set('player_id',NULL);
		$this->db->where('league_id',$league_id);
		$this->db->where('year',$year);
		$this->db->where('pick_overall >= '.$pick_overall);
		$this->db->where('player_id !=-999');
		$this->db->update($this->tables['DRAFT']);
		return true;
	}
	/**
	 *	SKIP PICK.
	 *
	 *	Sets the player id for the passed pick to -999, which effectively by passes it in the draft..
	 *
	 *	@param		$pick_overall	{int}		Overall pick ID
	 *	@param		$league_id		{int}		Optional league Id param. If not passed, the internal league id is used
	 *	@param		$year			{int}		Draft year
	 *	@return						{Boolean}	TRUE on success, FALSE on error
	 *
	 *	@since		1.0
	 *	@access		public
	 */
	public function skipPick($pick_overall = false, $league_id = false, $year = false) {
		
		if ($pick_overall === false) { return; }
		
		if ($league_id === false) $league_id = $this->league_id;
		if ($year === false) $year = date('Y');
		$this->resetPick($pick_overall,$league_id = false, $year = false, -999);
		return true;
	}	
	/**
	 *	RESET PICK.
	 *
	 *	Resets or overwrites an idividual picks player ID information. To reset a pick, pass NULL. To edit, pass a player_id value.
	 *
	 *	@param		$pick_overall	{int}		Overall pick ID
	 *	@param		$league_id		{int}		Optional league Id param. If not passed, the internal league id is used
	 *	@param		$year			{int}		Draft year
	 *	@param		$player_id		{int}		The update player id. Pass reset to void pick
	 *	@return						{Boolean}	TRUE on success, FALSE on error
	 *
	 *	@since		1.0
	 *	@access		public
	 */
	public function resetPick($pick_overall = false, $league_id = false, $year = false, $player_id = false ) {
		
		if ($pick_overall === false) { return; }
		
		if ($league_id === false) $league_id = $this->league_id;
		if ($year === false) $year = date('Y');
		
		if ($player_id === false) {
			$player_id = NULL;
		}
		$this->db->set('player_id',$player_id);
		$this->db->where('league_id',$league_id);
		$this->db->where('pick_overall',$pick_overall);
		$this->db->where('year',$year);
		$this->db->update($this->tables['DRAFT']);
		return true;
	}
	/**
	 *	RESET DRAFT.
	 *
	 *	Resets the draft to it's inital state wiping out all player picks. it does not destroy the structure of 
	 *	schedule of a draft, however
	 *
	 *	@param		$league_id		{int}		Optional league Id param. If not passed, the internal league id is used
	 *	@param		$year			{int}		Draft year
	 *	@param		$team_id		{int}		Optional team Id param. If not passed, no team filtering is performed
	 *	@return						{Boolean}	TRUE on success, FALSE on error
	 *
	 *	@since		1.0
	 *	@access		public
	 */
	public function draftReset($league_id = false, $year = false, $team_id = false ) {
		if ($league_id === false) $league_id = $this->league_id;
		if ($year === false) $year = date('Y');
		
		$this->db->set('player_id',NULL);
		$this->db->where('league_id',$league_id);
		if ($team_id !== false) {
			$this->db->where('team_id',$team_id);
		}
		$this->db->where('year',$year);
		$this->db->update($this->tables['DRAFT']);
		return true;
	}
	/**
	 *	GET USER RESULTS.
	 *
	 *	Returns a list of the passed users picks for the draft.
	 *
	 *	@param		$userId			{int}	The user ID
	 *	@param		$league_id		{int}	Optional league Id param. If not passed, the internal league id is used
	 *	@return					{Array}	Array of pick information
	 *
	 *	@since		1.0
	 *	@access		public
	 */
	public function getUserResults($userId = false, $league_id = false) {
		
		if ($userId === false) return;
		if ($league_id === false) $league_id = $this->league_id;
		$picks = array();
		
		$this->db->select($this->tables['DRAFT'].'.player_id, round as draft_round, pick_round as pick, first_name, last_name, position, role');
		$this->db->where($this->tables['DRAFT'].'.player_id > 0');
		$this->db->where('fantasy_teams.owner_id',$userId);
		$this->db->where($this->tables['DRAFT'].'.league_id',$league_id);
		$this->db->join('fantasy_teams','fantasy_teams.id = '.$this->tables['DRAFT'].'.team_id');
		$this->db->join('fantasy_players','fantasy_players.id = '.$this->tables['DRAFT'].'.player_id','left');
		$this->db->join('players','fantasy_players.player_id = players.player_id','right outer');
		$this->db->order_by('round, pick','asc');
		$query = $this->db->get($this->tables['DRAFT']);
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				$pos = 0;
				if ($row->position == 1) {
					if ($row->role == 13) {
						$pos = 12;
					} else {
						$pos = $row->role;
					}
				} else {
					$pos = $row->position;
				}
				$picks = $picks + array($row->draft_round=>array('pick'=>$row->pick,'player_id'=>$row->player_id,'player_name'=>$row->first_name." ".$row->last_name,'position'=>get_pos($pos)));
				
			}
		}
		return $picks;
	}
	/*------------------------------------------------------------------------------
	/
	/	DRAFT LISTS
	/	Revised in 1.0.5 Beta to allow users to make and arrange draft list order 
	/	on client side, then save, via AJAX, the draft list to the DB.
	/
	/-----------------------------------------------------------------------------*/
	/**
	 * GET USER PICKS.
	 * Returns a list of draft list choices by the specified team owner for the specified league.
	 * 
	 * EDIT 1.0.5: Added league_id to where clause to prevent list returns from multiple owned leagues.
	 * 
	 * @param	$userId				Integrer	Team Owner ID
	 * @param 	$league_id			Integrer	Fantasy League ID
	 * @return						Array		Array of player info
	 * @since	1.0
	 * 
	 */
	public function getUserPicks($userId = false, $league_id = false) {
		
		if ($userId === false) return;
		if ($league_id === false) $league_id = $this->league_id;
		$picks = array();
		
		$this->db->select($this->tables['DRAFT_LIST'].'.rank,'.$this->tables['DRAFT_LIST'].'.player_id, first_name, last_name, position, role');
		$this->db->where('owner_id',$userId);
		$this->db->where($this->tables['DRAFT_LIST'].'.league_id',$league_id);
		$this->db->join('fantasy_players','fantasy_players.id = '.$this->tables['DRAFT_LIST'].'.player_id','left');
		$this->db->join('players','fantasy_players.player_id = players.player_id','right outer');
		$this->db->order_by($this->tables['DRAFT_LIST'].'.rank','asc');
		$query = $this->db->get($this->tables['DRAFT_LIST']);
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				$pos = 0;
				if ($row->position == 1) {
					if ($row->role == 13) {
						$pos = 12;
					} else {
						$pos = $row->role;
					}
				} else {
					$pos = $row->position;
				}
				$picks = $picks + array($row->rank=>array('player_id'=>$row->player_id,'player_name'=>htmlentities($row->first_name." ".$row->last_name,ENT_NOQUOTES,"UTF-8"),'position'=>get_pos($pos)));
			}
		}
		return $picks;
	}
	/**
	 * SAVE DRAFT LIST.
	 * Saves a users drasft list selections to the DB. This function replaces the individal add, remove, move
	 * and clear functions which were all moved to the UI.
	 * 
	 * @param 	$player_id_list		Array		Player ID array
	 * @param	$userId				Integrer	Team Owner ID
	 * @param 	$league_id			Integrer	Fantasy League ID
	 * @return						Boolean		TRUE on success, FALSE on error
	 * @since	1.0.5
	 * 
	 */
	public function saveDraftList($player_id_list = false, $userId = false, $league_id = false) {
		
		if ($league_id === false) $league_id = $this->league_id;
		if ($player_id_list === false || $userId === false) return;
		
		// CLEAR EXISTING LIST
		$this->clearDraftList($userId,$league_id);
		$rank = 1;
		$saved = 0;
		// SAVE PLAYER IDs PASSED
		if (is_array($player_id_list) && sizeof($player_id_list) > 0) {
			foreach($player_id_list as $player_id) {
				$data = array('owner_id'=>$userId, 'league_id'=>$league_id, 'rank'=>$rank,
							  'player_id'=>$player_id);
				$this->db->insert($this->tables['DRAFT_LIST'],$data);
				$rank++;
				$saved++;
			}
		}
		$this->statusMess = $saved . " players saved to draft list.";
		return true;
	}
	/**
	 * CLEAR DRAFT LIST.
	 * Removes all IDs from a users draft list.
	 * EDIT 1.0.5: Added league_id to where clause to prevent list clearing across multiple owned leagues.
	 * 
	 * @param	$userId				Integrer	Team Owner ID
	 * @param 	$league_id			Integrer	Fantasy League ID
	 * @return						Boolean		TRUE on success, FALSE on error
	 * @since	1.0
	 * 
	 */
	public function clearDraftList($userId = false, $league_id = false) {
		
		if ($league_id === false) $league_id = $this->league_id;
		if ($userId === false) return;
		
		$this->db->where('owner_id',$userId);
		$this->db->where('league_id',$league_id);
		$this->db->delete($this->tables['DRAFT_LIST']);
		
		return true;
	}
	/**
	* 	DELETE ALL DRAFT LISTS.
	* 	Removes all draft lists for a given league.
	*
	* 	@param 	$league_id			Integrer	Fantasy League ID
	* 	@return						Boolean		TRUE on success, FALSE on error
	*	
	*	@since	1.0.6
	*	@access	public
	*
	*/
	public function deleteAllDraftLists($league_id = false) {
	
		if ($league_id === false) $league_id = $this->league_id;
	
		$this->db->where('league_id',$league_id);
		$this->db->delete($this->tables['DRAFT_LIST']);
	
		return true;
	}
	/**
	 * REMOVE FROM DRAFT LISTS.
	 * Removes a drafted player from all draft lists they appear on.
	 * 
	 * @param	$player_id			Integrer	Fantasy Player ID
	 * @param 	$league_id			Integrer	Fantasy League ID
	 * @return						Boolean		TRUE on success, FALSE on error
	 * @since	1.0
	 * 
	 */
	public function removeFromDraftLists($player_id = false, $league_id = false) {
		if ($player_id === false) return false;
		
		if ($league_id === false) $league_id = $this->league_id;
		
		// GET OWNER IDS FOR LEAGUE
		$ownerIds = array();
		$this->db->select('owner_id');
		$this->db->from('fantasy_teams');
		$this->db->where('league_id',$league_id);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				array_push($ownerIds,$row->owner_id);
			}
		}
		$query->free_result();
		// GET DRAFT LISTS
		foreach($ownerIds as $id) {
			$picks = $this->getUserPicks($id);
			if (is_array($picks) && sizeof($picks) > 0) {
				foreach($picks as $pickData) {	
					if ($pickData['player_id'] == $player_id) {
						$this->removePick($player_id, $id, $league_id);
						break;
					}
				}
			} else { 
				continue; 
			}
		}
		return true;
	}
	/**
	 * REMOVE DRAFT LIST PICK.
	 * Removes a player from team owner list (usually when drafted by another team).
	 * 
	 * EDIT 1.0.5: Function changed from public to protected.
	 * 
	 * @param 	$player_id		Integrer	Fantasy Player ID
	 * @param	$userId			Integrer	Team Owner ID
	 * @param 	$league_id		Integrer	Fantasy League ID
	 * @return					Boolean		TRUE on success, FALSE on error
	 * @since	1.0
	 * @access	protected		As of 1.0.5
	 * 
	 */
	protected function removePick($player_id = false, $userId = false, $league_id = false) {
		
		if ($userId === false) return false; // END if
		if ($league_id === false) $league_id = $this->league_id; // END if
		// GET CURRENT PICKS
		$picks = $this->getUserPicks($userId,$league_id);
		$pickData = array();
		foreach($picks as $rank => $data) {
			if ($data['player_id'] != $player_id) {
				array_push($pickData,$data['player_id']);
			} // END if
		} // END foreach
		$this->clearDraftList($userId,$league_id);
		if ($this->saveDraftList($pickData,$userId,$league_id)) {
			return true;
		} else {
			$this->statusMess = "Draft list update was not saved.";
			return false;
		} // END if
	}
	/*---------------------------------------------
	/
	/	PLAYERS STATS AND LISTS
	/
	/--------------------------------------------*/
	/**
	 * 	GET PLAYER POOL.
	 * 
	 * 	Retrieves the list of draftable players for the specified OOTP and Fantasy League. 
	 * 
	 *  This function allows for the return of the full stats table or just a listing of player names
	 *  @param	$countOnly 			(Boolean)	Deprecated
	 *  @param	$ootp_league_id		(Interger)	The ID of the OOTP league (usually 100)
	 *  @param	$player_type		(Interger)	1 = batter, 2 = Pitcher	
	 *  @param	$position_type		(Interger)	Fantasy Position Type
	 *  @param	$role_type			(Interger)	Fantasy Role Type
	 *  @param	$stats_range		(Integrer)	# of years to display stats for
	 *  @param	$min_var			(Integrer)	Minimum value for limiting stats
	 *  @param	$limit				(Integrer)	# of records to return
	 *  @param	$startIndex			(Integrer)	Index of first record (used with $limit)
	 *  @param	$league_id			(Integrer)	Fantasy League ID
	 *  @param	$ootp_league_date	(Date/Time)	[1.0.2] OOTP League Date (Used to determine starting point for stats ranges)
	 *  @param	$rules				(Array)		[1.0.2] Fantasy Rules Object (used by stats processing for positions)
	 *  @param	$searchType			{String)	[1.0.2] Deprecated
	 *  @param	$searchParam		(Integrer)	[1.0.2] Deprecated
	 *  @param	$noStats 			(Boolean)	[1.0.5]	Omits ststas from return array
	 *  @return						(Array)		List of draftable players
	 *  @since						1.0
	 *  @see						Helpers -> roster_helper
	 *  @see						Helpers -> general_helper
	 */
	public function getPlayerPool($countOnly = false, $ootp_league_id, $player_type=1, $position_type = -1,  
									$role_type = -1, $stats_range = 1, $min_var = 0, $limit = -1, $startIndex = 0, 
									$league_id = false, $ootp_league_date = false, $rules = array(),
									$searchType = 'all', $searchParam = -1, $noStats = false) {	
		$stats = array();
		$players = array();
		if ($league_id === false) $league_id = $this->league_id;
		if (!function_exists('getDraftedPlayersByLeague')) {
			$this->load->helper('roster');
		}
		$alreadyDrafted = getDraftedPlayersByLeague($league_id);
		$order = "DESC";
		
		$this->db->flush_cache();
		$sql = '';
		$where = '';
		if (!$noStats) {
			$sql = 'SELECT fantasy_players.id, "add", "draft", age, throws, bats, fantasy_players.id,fantasy_players.positions, players.player_id, players.position as position, players.role as role, players.first_name, players.last_name, players.injury_is_injured, players.injury_dtd_injury, players.injury_career_ending, players.injury_dl_left, players.injury_left, players.injury_id,';		
			if ($player_type == 1) {
				if ($stats_range == 4) {
					$sql .= player_stat_query_builder(1, QUERY_STANDARD, $rules, false)." ";
				} else {
					$sql .= player_stat_query_builder(1, QUERY_STANDARD, $rules)." ";
				}
				$tblName = "players_career_batting_stats";
				$where = "AND players.position <> 1 AND players_career_batting_stats.ab > 0 ";
				if (!empty($position_type) && $position_type != -1) {
					if ($position_type == 20) {
						$where.="AND (players.position = 7 OR players.position = 8 OR players.position = 9) ";
					} else {
						$where.="AND players.position = ".$position_type." ";
					}
				}
				$orderBy = 'ab';
				if ($min_var != 0) {
					$where .= 'AND '.$tblName.'.ab >= '.$min_var." ";
				}
			} else {
				if ($stats_range == 4) {
					$sql .= player_stat_query_builder(2, QUERY_STANDARD, $rules, false)." ";
				} else {
					$sql .= player_stat_query_builder(2, QUERY_STANDARD, $rules)." ";
				}
				$tblName = "players_career_pitching_stats";
				$where = "AND players_career_pitching_stats.ip > 0 ";
				if (!empty($role_type) && $role_type != -1) {
					$where.="AND players.role = ".$role_type." ";
				}
				$orderBy = 'ip';
				if ($min_var != 0) {
					$where .= 'AND '.$tblName.'.ip >= '.$min_var." ";
				}
			}	
		} else {
			$sql = 'SELECT fantasy_players.id, players.player_id, players.position as position, players.role as role, players.first_name, players.last_name';		
			$tblName = 'players';
		}
		$sql .= ' FROM '.$tblName;
		$sql .= ' LEFT JOIN fantasy_players ON fantasy_players.player_id = '.$tblName.'.player_id';
		if (!$noStats) {
			$sql .= ' LEFT JOIN players ON players.player_id = '.$tblName.'.player_id';
		}
		$sql .= ' WHERE '.$tblName.'.league_id = '.$ootp_league_id.' AND players.retired = 0';
		if (!$noStats) {
			$sql .= ' AND '.$tblName.'.split_id = 1 AND '.$tblName.'.level_id = 1';
		}
		$notDraftableStr = "(";
		if (sizeof($alreadyDrafted) > 0) {
			foreach ($alreadyDrafted as $id) {
				if ($notDraftableStr != "(") { $notDraftableStr .= ","; }
				$notDraftableStr .= $id;
			}
		}
		$notDraftableStr .= ")";
		//echo("notFAStr = ".$notFAStr."<br />");
		if ($notDraftableStr != "()") {
		//$this->db->where_not_in('player_id',$notAFreeAgent);
			$sql .= ' AND fantasy_players.id NOT IN '.$notDraftableStr;
		}
		if (!$noStats) {
			$year_time = (60*60*24*365);
			if ($ootp_league_date === false || $ootp_league_date == EMPTY_DATE_STR) {
				$base_year = time();
			} else {
				$base_year = strtotime($ootp_league_date);
			}
			if ($stats_range != 4) {
				$sql .= ' AND '.$tblName.'.year = '.date('Y',$base_year-($year_time * $stats_range));
			} else {
				$sql .= ' AND ('.$tblName.'.year = '.date('Y',$base_year-($year_time))." OR ".$tblName.'.year = '.date('Y',time()-($year_time * 2))." OR ".$tblName.'.year = '.date('Y',time()-($year_time * 3)).")";
			}
		} // END if (!$noStats)
		if (!empty($where)) {
			$sql .= " ".$where;
		} // END if (!empty($where))
		if ($noStats) {
			$orderBy = 'players.last_name';
			$order = "ASC";
		} else {
			$sql.=' GROUP BY '.$tblName.'.player_id';
			if (sizeof($rules) > 0 && isset($rules['scoring_type']) && $rules['scoring_type'] == LEAGUE_SCORING_HEADTOHEAD) {
				$orderBy = 'fpts';	
			}
		} // END if (!$noStats)
		$sql.=" ORDER BY ".$orderBy." ".$order." ";
		if ($limit != -1 && $startIndex == 0) {
			$sql.="LIMIT ".$limit;
		} else if ($limit != -1 && $startIndex > 0) {
			$sql.="LIMIT ".$startIndex.", ".$limit;
		}
		//echo("sql = ".$sql."<br />");
		$query = $this->db->query($sql);
		$fields = $query->list_fields();
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$player = array();
				foreach($fields as $field) {
					$player[$field] = $row->$field;
				}
				$player['player_name'] = $row->first_name." ".$row->last_name;
				if ($row->position == 1) {
					$player['pos'] = $row->role;
				} else {
					$player['pos'] = $row->position;
				}
				array_push($stats,$player);
			}
		}
		return $stats;
	}
	/**
	 * 	GET PLAYER RATINGS.
	 * 	Returns a list if player IDs sorted accoring to their ranking against hitt/pitching and fielding ratings categories.
	 * 	@param	$ootp_league_id		(Integer)	OPTIONAL, usually 100
	 *  @param 	$draftInjured		(Boolean)	TRUE to draft injured players, FALSE to not
	 *  @return						(Array)		An array of player IDs and rankings
	 *  @see						controllers -> draft -> selection()		
	 *  @since 						1.0.3 PROD
	 *  @author 					jfox015
	 */
	public function getPlayerRatings($ootp_league_id = 100, $draftInjured = false) {

		echo("<b>Draft Model -> getPlayerRatings</b><br />");
								
		if ($draftInjured === false) $draftInjured = $this->draftInjured;
	
		$teamStr = "";
		$teams = getOOTPTeams($ootp_league_id, false);
		foreach($teams as $id => $data) {
			if (!empty($teamStr)) { $teamStr .= ","; }
			$teamStr .= $id;
		}
		$values = array();
		$sql="SELECT fp.id, fp.rating ";
		$sql.="FROM players as p, fantasy_players as fp ";
		$sql.="WHERE p.player_id = fp.player_id AND p.retired=0 ";
		if ($draftInjured == -1) {
			$sql.="AND p.injury_is_injured=0 ";
		}
		$sql.="AND p.team_id IN (".$teamStr.") ";
		$sql.="ORDER BY fp.rating DESC";
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				$values[$row->id] = $row->rating;
			}
		}
		$query->free_result();
		return $values;
	}
	/**
	 * 	GET PLAYER VALUES.
	 * 	Returns a list if player IDs sorted accoring to their ranking against hitt/pitching and fielding ratings categories.
	 * 	@param	$ootp_league_id		(Integer)	OPTIONAL, usually 100
	 *  @param 	$league_id			(Integer)	OPTIONAL< Fantasy league ID. $this->league_id is used if ommited
	 *  @return						(Array)		A array of player IDs and rankings
	 *  @see						controllers -> draft -> selection()		
	 *  @since 						1.0
	 *  @author						fhommes, Adapted from StatsLab X
	 *  @author 					jfox015
	 */
	public function getPlayerValues($ootp_league_id = 100, $league_id = false, $draftInjured = false) {
		
		if ($league_id === false) $league_id = $this->league_id;
		if ($draftInjured === false) $draftInjured = $this->draftInjured;
		
		if (!function_exists('getDraftedPlayersByLeague')) {
			$this->load->helper('roster');
		}
		$notAFreeAgent = getDraftedPlayersByLeague($league_id);
		$taken = "";
		foreach($notAFreeAgent as $player_id) {
			if (!empty($taken)) { $taken .= ","; }
			$taken .= $player_id;
		}
		
		$teamStr = "";
		$teams = getOOTPTeams($ootp_league_id, false);
		foreach($teams as $id => $data) {
			if (!empty($teamStr)) { $teamStr .= ","; }
			$teamStr .= $id;
		}
		
		$values = array();
		

		## Batters
		$sql="SELECT fp.id, p.player_id,(";
		$sql.="6*pb.batting_ratings_overall_contact+";
		$sql.="2*pb.batting_ratings_overall_gap+";
		$sql.="4*pb.batting_ratings_overall_power+";
		$sql.="3*pb.batting_ratings_overall_eye+";
		$sql.="1*pb.batting_ratings_overall_strikeouts+";
		$sql.="20*pb.batting_ratings_talent_contact+";
		$sql.="10*pb.batting_ratings_talent_gap+";
		$sql.="17*pb.batting_ratings_talent_power+";
		$sql.="15*pb.batting_ratings_talent_eye+";
		$sql.="8*pb.batting_ratings_talent_strikeouts+";
		$sql.="3*p.running_ratings_speed+";
		$sql.="3*p.running_ratings_stealing+";
		$sql.="1*p.running_ratings_baserunning";
		$sql.=")/68 as value FROM players as p,players_batting as pb, fantasy_players as fp ";
		//$sql.="LEFT JOIN  ON  ";
		$sql.="WHERE p.player_id=pb.player_id AND p.player_id = fp.player_id AND p.retired=0 AND p.league_id=$ootp_league_id ";
		if ($draftInjured == -1) {
			$sql.="AND p.injury_is_injured=0 ";
		}
		$sql.="AND p.team_id IN (".$teamStr.")";
		//$sql.="AND fp.id NOT IN (".$taken.")";
		$query = $this->db->query($sql);
		foreach($query->result_array() as $row) {
			$pid=$row['id'];
			$value=$row['value'];
			$bvalues[$pid]=$value;
		}
		$query->free_result();
		
		## Fielding
		$sql="SELECT fp.id, p.player_id";
		$sql.=",pf.fielding_ratings_infield_arm";
		$sql.=",pf.fielding_ratings_outfield_arm";
		$sql.=",pf.fielding_ratings_catcher_arm";
		$sql.=",pf.fielding_rating_pos2";
		$sql.=",pf.fielding_rating_pos4";
		$sql.=",pf.fielding_rating_pos5";
		$sql.=",pf.fielding_rating_pos6";
		$sql.=",pf.fielding_rating_pos8";
		$sql.=",pf.fielding_rating_pos9";
		$sql.=" FROM players as p,players_fielding as pf, fantasy_players as fp ";
		//$sql.="LEFT JOIN fantasy_players as fp ON p.player_id = fp.player_id ";
		$sql.="WHERE p.player_id=pf.player_id AND p.player_id = fp.player_id AND p.retired=0 AND p.league_id=$ootp_league_id ";
		if ($draftInjured == -1) {
			$sql.="AND p.injury_is_injured=0 ";
		}
		//$sql.="AND fp.id NOT IN (".$taken.")";	
		$sql.="AND p.team_id IN (".$teamStr.")";
		$query = $this->db->query($sql);
		foreach($query->result_array() as $row) {
			$pid=$row['id'];
			$mod=0;
			$bcnt=0;
			if ($row['fielding_rating_pos2']>1) {$mod+=.01;$bcnt+=1;}
			if ($row['fielding_ratings_catcher_arm']>100) {$mod+=.011;$bcnt+=1;}
			if ($row['fielding_rating_pos4']>100) {$mod+=.01;$bcnt+=1;}
			if ($row['fielding_rating_pos5']>120) {$mod+=.008;$bcnt+=1;}
			if (($row['fielding_rating_pos5']>60)&&($row['fielding_ratings_infield_arm']>100)) {$mod+=.005;$bcnt+=1;}
			if ($row['fielding_rating_pos6']>100) {$mod+=.011;$bcnt+=1;}
			if ($row['fielding_rating_pos8']>100) {$mod+=.0075;$bcnt+=1;}
			if (($row['fielding_rating_pos9']>60)&&($row['fielding_ratings_outfield_arm']>100)) {$mod+=.005;$bcnt+=1;}
			switch ($bcnt) {
				case 0: 
				case 1: $mod=$mod*1.00; break;
				case 2: $mod=$mod*.800; break;
				case 3: $mod=$mod*.650; break;
				case 4: $mod=$mod*.600; break;
				case 5: $mod=$mod*.575; break;
				case 6: 
				case 7: 
				case 8: $mod=$mod*.550; break;
				default: break;
			}
			$bvalues[$pid]=$bvalues[$pid]*(1+$mod);
		}
		
		$pvalues = array();
		## Pitchers
		$pitches=array("fastball","slider","curveball","screwball","forkball","changeup","sinker","splitter","knuckleball","cutter","circlechange","knucklecurve");
		$sql="SELECT fp.id, p.player_id,";
		foreach ($pitches as $key => $pitch) {
			$sql.="pitching_ratings_pitches_$pitch,";
			$sql.="pitching_ratings_pitches_talent_$pitch,";
		}
		$sql.="pitching_ratings_misc_stamina,";
		$sql.="(";
		$sql.="2*pp.pitching_ratings_overall_stuff+";
		$sql.="2*pp.pitching_ratings_overall_control+";
		$sql.="2*pp.pitching_ratings_overall_movement+";
		$sql.="8*pp.pitching_ratings_talent_stuff+";
		$sql.="8*pp.pitching_ratings_talent_control+";
		$sql.="8*pp.pitching_ratings_talent_movement+";
		$sql.="2*pp.pitching_ratings_misc_velocity+";
		$sql.="2*pp.pitching_ratings_misc_stamina";
		$sql.=")/90 as value FROM players as p,players_pitching as pp, fantasy_players as fp ";
		//$sql.="LEFT JOIN fantasy_players as fp ON p.player_id = fp.player_id ";
		$sql.="WHERE p.player_id=pp.player_id AND p.player_id = fp.player_id AND p.position=1 AND p.retired=0 AND p.league_id=$ootp_league_id ";
		if ($draftInjured == -1) {
			$sql.="AND p.injury_is_injured=0 ";
		}
		//$sql.="AND fp.id NOT IN (".$taken.")";	
		$sql.="AND p.team_id IN (".$teamStr.")";
		$query = $this->db->query($sql);
		foreach($query->result_array() as $row) {
			$pid=$row['id'];
			$value=$row['value'];
			$mod=1;
			$pitchCnt=0;
			$qpc=0;
			$end=$row['pitching_ratings_misc_stamina'];
			if ($end<120) {$mod-=0.025;}
			if ($end<100) {$mod-=0.025;}
			if ($end<80) {$mod-=0.025;}
			foreach ($pitches as $key => $pitch) {
				$field="pitching_ratings_pitches_".$pitch;
				$pvalue=$row[$field];
				if ($pvalue>180) {$mod+=0.001;}
				if ($pvalue>160) {$mod+=0.001;}
				if ($pvalue>130) {$mod+=0.001;}
				if ($pvalue>100) {$mod+=0.001;}
				if ($pvalue>0) {$mod+=0.001;}
				$field="pitching_ratings_pitches_talent_".$pitch;
				$pvalue=$row[$field];
				if ($pvalue>180) {$mod+=0.02;}
				if ($pvalue>160) {$mod+=0.02;}
				if ($pvalue>130) {$mod+=0.02;$qpc+=1;}
				if ($pvalue>100) {$mod+=0.02;}
				if ($pvalue>0) {$mod+=0.02;$pitchCnt+=1;}
			}
			if ($qpc>0) {$mod+=(($qpc-1)*0.05);}
			
			if ($pitchCnt<3) {$mod=$mod*0.75;}
			if ($pitchCnt>3) {$mod=$mod*1.05;}
			$value=$mod*$value;
			$pvalues[$pid]=$value;
		}
		
		## Calculate avg
		$btot=0;
		$bcnt=0;
		foreach ($bvalues as $pid => $val) {
			if (isset($pvalues[$pid])) {
				if ($pvalues[$pid]>$bvalues[$pid]) {unset($bvalues[$pid]);continue;} else {unset($pvalues[$pid]);}
			}
			$btot+=$bvalues[$pid];
			$bcnt++;
		}
		$ptot=0;
		$pcnt=0;
		foreach ($pvalues as $pid => $val) {
			$ptot+=$pvalues[$pid];
			$pcnt++;
		}
		$bavg=$btot/$bcnt;
		$pavg=$ptot/$pcnt;
		
		## Calculate Deviation
		$bstd=deviation($bvalues);
		$pstd=deviation($pvalues);
		
		## Compute z-Score
		foreach ($bvalues as $pid => $val) {$values[$pid]=($bvalues[$pid]-$bavg)/$bstd;}
		foreach ($pvalues as $pid => $val) {$values[$pid]=($pvalues[$pid]-$pavg)/$pstd;}
		
		arsort($values);

		if ($this->debug) {
			echo("------------------------------------------<br/>");
			echo("     PLAYER VALUE DUMP <br/>");
			echo("------------------------------------------<br/>");
			foreach ($values as $pid => $val) {
				echo("player ".$pid.", value = ".$val."<br/>");
			}
		}
		return $values;
	}
	
	/*---------------------------------------------------------------------
	 *
	 * DEPRECATED VARS AND FUCNTIONS
	 *
	 * ------------------------------------------------------------------*/
	/* DEPRECATED */
	var $dStartDt = EMPTY_DATE_TIME_STR; 	//deprecated, use draftDate
	var $dStartTm = EMPTY_TIME_STR; 		//deprecated, use draftDate
	var $rndSwitch = -1; 					//deprecated
	var $timePick2 = EMPTY_TIME_STR; 		//deprecated
	var $timeStart = EMPTY_DATE_TIME_STR; 	//deprecated, use draftDate
	var $timeStop = EMPTY_DATE_TIME_STR; 	//deprecated
	var $dispLimit = -1; 	//deprecated
	var $emailList = '';	//deprecated
	var $replyList = '';	//deprecated
	var $pauseWkEnd = -1;   //deprecated
	var $timePerPick = 0;   //deprecated
	/**
	 * @deprecated
	 */
	public function addUserPick($player_id = false, $userId = false,$league_id = false) {
		
		if ($league_id === false) $league_id = $this->league_id;
		if ($player_id === false || $userId === false) return;
		
		// GET CURRENT PICKS
		$picks = $this->getUserPicks($userId);
		$found = $this->playerInUserList($player_id, $picks);
		
		if (!$found) {
			$data = array('owner_id'=>$userId, 'league_id'=>$league_id, 'rank'=>sizeof($picks)+1,
						  'player_id'=>$player_id);
			$this->db->insert($this->tables['DRAFT_LIST'],$data);
			return true;
		} else {
			$this->statusMess = "Player is already on draft list.";
			return false;
		}
		
	}
	/**
	 * @deprecated
	 */
	/*public function removePick($player_id = false, $userId = false,$league_id = false) {
		
		if ($league_id === false) $league_id = $this->league_id;
		if ($userId === false) return;
		// GET CURRENT PICKS
		$picks = $this->getUserPicks($userId);
		
		$pickData = array();
		foreach($picks as $rank => $data) {
			if ($data['player_id'] != $player_id) {
				array_push($pickData,$data['player_id']);
			}
		}
		$this->clearDraftList($userId);
		$rank = 1;
		if (sizeof($pickData) > 0) {
			foreach($pickData as $id) {
				$data = array('owner_id'=>$userId, 'league_id'=>$league_id, 'rank'=>$rank,
							  'player_id'=>$id);
				$this->db->insert($this->tables['DRAFT_LIST'],$data);
				$rank++;
			}
			return true;
		} else {
			$this->statusMess = "No picks found on users draft list.";
			return false;
		}
		
	}*/
	/**
	 * @deprecated
	 */
	public function movePick($direction, $player_id = false, $userId = false,$league_id = false) {
		
		if ($league_id === false) $league_id = $this->league_id;
		if ($userId === false) return;
		// GET CURRENT PICKS
		$picks = $this->getUserPicks($userId);
		
		$oldRank = 0;
		$newRank = 0;
		$swapPlayerId = -1;
		$thisPlayerId = -1;
		foreach($picks as $rank => $data) {
			if ($data['player_id'] == $player_id) {
				$oldRank = $rank;
				$thisPlayerId = $data['player_id'];
				if ($direction == 1) {
					$newRank = $rank - 1;
					$swapPlayerId = $picks[$rank - 1]['player_id'];
				} else {
					$newRank = $rank + 1;
					$swapPlayerId = $picks[$rank + 1]['player_id'];
				}
			}
		}
		$this->db->where('owner_id',$userId);
		$this->db->where('rank',$oldRank);
		$this->db->set('player_id',$swapPlayerId);
		$this->db->update($this->tables['DRAFT_LIST']);
		
		$this->db->where('owner_id',$userId);
		$this->db->where('rank',$newRank);
		$this->db->set('player_id',$thisPlayerId);
		$this->db->update($this->tables['DRAFT_LIST']);
		return true;
	}
	/**
	 * @deprecated
	 */
	public function playerInUserList($player_id = false, $picks = array()) {
		
		if ($player_id === false || sizeof($picks) == 0) return false;

		$found = false;
		foreach($picks as $rank => $data) {
			if ($data['player_id'] == $player_id) {
				$found = true;
				break;
			}
		}
		return $found;
	}
	
}