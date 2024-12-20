<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Data List Helpers
 *
 * @package		CodeIgniter
 * @subpackage	Helpers
 * @category	Helpers
 * @author		Jeff Fox
 * @description	Various helpers for handling data lists
 * @version		1.1.1 Prod Bug Fix
 * @lastModified	11/26/24
 */
// ------------------------------------------------------------------------
/**
 * loadSimpleDataList.
 * Provides a simple method for retrieving the index and values of the
 * many basic data lists used by the site.
 * @param	$list	The list id. See <b>usage for tips</b>
 * @param	$column	(OPTIONAL) Specifiy a column (other than id) to sort the list on
 * @param	$order	(OPTIONAL) Specify a SQL sort order other than ASC
 * @return			A key/value pair array of data items
 * @since			1.0
 */
if ( ! function_exists('loadSimpleDataList')) {
	function loadSimpleDataList($list,$column = '', $order = "ASC", $label = '') {
		$ci =& get_instance();
		$table = '';
		switch ($list) {
			case 'username':
				$table = USER_CORE_TABLE;
				break;
			case 'StateName':
				$table = 'states_us';
				break;
			case 'cntryName':
				$table = 'countries';
				break;
			case 'userLevel':
				$table = 'list_user_levels';
				break;
			case 'accessLevel':
				$table = 'list_access_levels';
				break;
			case 'userType':
				$table = 'list_user_types';
				break;
			case 'availableOOTPLeagues':
				$table = 'leagues';
				$list = "name";
				$label = "OOTP League";
				$identifier = 'league_id';
				break;
			case 'availableOOTPLeaguesAbbr':
				$table = 'leagues';
				$list = "abbr";
				$label = "OOTP League";
				$identifier = 'league_id';
				break;
			case 'accessType':
				$table = 'fantasy_leagues_access';
				break;
			case 'leagueStatus':
				$table = 'fantasy_leagues_status';
				break;
			case 'leagueType':
				$table = 'fantasy_leagues_types';
				break;
			case 'bugCategory':
				$table = 'admin_list_bug_categories';
				break;
			case 'bugStatus':
				$table = 'admin_list_status';
				break;
			case 'priority':
				$table = 'admin_list_priorities';
				break;
			case 'severity':
				$table = 'admin_list_severities';
				break;
			case 'os':
				$table = 'qa_list_os';
				break;
			case 'browser':
				$table = 'qa_list_browsers';
				break;
			case 'project':
				$table = 'admin_projects';
				$list = 'name';
				break;
			case 'newsType':
				$table = 'fantasy_news_type';
				break;
			case 'tradeStatus':
				$table = 'fantasy_teams_trades_status';
				break;
			case 'tradeApprovalType':
				$table = 'fantasy_teams_trades_approvals';
				break;
			case 'activationType':
				$table = 'users_activation_types';
				break;
			default:
				break;
		} // END switch

		// ADD Default FIRST ITEM
		if (empty($identifier)) { $identifier = 'id'; }
		if (empty($column)) { $column = $identifier; }
		if (empty($label)) { $label = $list; }

		$datalist = array(''=>'Choose '.$label);

		// ADD LIST RESULTS
		if ($ci->db->table_exists($table) && !empty($table)) {
            $query = $ci->db->query('SELECT '.$identifier.', '.$list.' FROM '.$table.' ORDER BY '.$column .' '.$order);
            if ($query->num_rows() > 0) {
                foreach ($query->result() as $row) {
                    $datalist = $datalist + array($row->$identifier=>$row->$list);
                } // END foreach
            } // END if
            $query->free_result();
		} else {
            redirect('media/nodb.php');
        }
		return $datalist;
	} // END function
} // END if

// ------------------------------------------------------------------------

if ( ! function_exists('loadLimitedBugStatusList')) {
	function loadLimitedBugStatusList($threshold = 2) {
		$retrunList = array();
		$dataList = loadSimpleDataList('bugStatus');
		foreach($dataList as $key => $value) {
			if ($key < $threshold) {
				$retrunList = $retrunList + array($key => $value);
			} // END if
		} // END foreach
		return $retrunList;
	} // END function
} // END if

// ------------------------------------------------------------------------

if ( ! function_exists('resolveUsername')) {
	function resolveUsername($userId = false) {
	    if ($userId === false) {
	        return false;
	    }
	    $username = '';
	    $userList = loadSimpleDataList('username');
	    foreach($userList as $key => $value) {
	        if ($key == $userId) {
	           $username = $value;
	           break;
	        } // END if
		} // END foreach
	    return $username;
	} // END function
} // END if
// ------------------------------------------------------------------------

if ( ! function_exists('resolveOwnerName')) {
	function resolveOwnerName($ownerId = false) {
	  	if ($ownerId === false) {
	        return false;
	    }
	    $ci =& get_instance();
	  	$ownerName = '';
		$query = $ci->db->query('SELECT firstName, lastName FROM users_meta WHERE userId = '.$ownerId);
		if ($query->num_rows() > 0) {
			$row = $query->row();
			$ownerName = $row->firstName." ".$row->lastName;
		} // END if
		$query->free_result();

	    return $ownerName;
	} // END function
} // END if
// ------------------------------------------------------------------------

if ( ! function_exists('getInjuryName')) {
	function getInjuryName($injuryId = false) {
	  	if ($injuryId === false) {
	        return false;
	    }
	    $ci =& get_instance();
	  	$injuryName = '';
		$query = $ci->db->query('SELECT injury_text FROM list_injuries WHERE id = '.$injuryId);
		if ($query->num_rows() > 0) {
			$row = $query->row();
			$injuryName = $row->injury_text;
		} // END if
		$query->free_result();

	    return $injuryName;
	} // END function
} // END if
// ------------------------------------------------------------------------

if ( ! function_exists('getRandomTeamNickname')) {
	function getRandomTeamNickname() {

		$nick = "";
	    $ci =& get_instance();

	    $query = $ci->db->query('SELECT nickname FROM list_team_nicknames');
		if ($query->num_rows() > 0) {

			$randId = rand(1, $query->num_rows());
			$query->free_result();
			$query = $ci->db->query('SELECT nickname FROM list_team_nicknames WHERE id = '.$randId);
			$row = $query->row();
			$nick = $row->nickname;
		} // END if
		$query->free_result();

	    return $nick;
	} // END function
} // END if

// ------------------------------------------------------------------------

if ( ! function_exists('resolveTeamName')) {
	function resolveTeamName($teamId = false) {
	  	if ($teamId === false) {
	        return false;
	    }
	    $ci =& get_instance();
	  	$name = '';
	    $query = $ci->db->query('SELECT teamname,teamnick FROM fantasy_teams WHERE id = '.$teamId);
		if ($query->num_rows() > 0) {
			$row = $query->row();
			$name = $row->teamname." ". $row->teamnick;
		} // END if
		$query->free_result();

	    return $name;
	} // END function
} // END if

// ------------------------------------------------------------------------

if ( ! function_exists('listLeagueDivisions')) {
	function listLeagueDivisions($league_id,$selectBox = true) {
		if ($selectBox)
			$result = array(' '=>'Select Division');
		else
			$result = array();

		$ci =& get_instance();
		// LOAD CHARACTERS
		$ci->db->select('id, division_name')
				 ->from('fantasy_divisions')
				 ->where('league_id',$league_id)
				 ->order_by('division_name','asc');
		$query = $ci->db->get();
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$result = $result + array($row->id=>$row->division_name);
			}
		} // END if
		$query->free_result();
		if (($selectBox && sizeof($result) == 1)) {
			$result = $result + array(' '=>'No divisions found');
		}
		return $result;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists('listLeagueTypes')) {
	function listLeagueTypes($onlyActive = false, $selectBox = true) {
		if ($selectBox)
			$result = array(' '=>'Select Type');
		else
			$result = array();

		$ci =& get_instance();
		// LOAD CHARACTERS
		$ci->db->select('id, leagueType');
		$ci->db->from('fantasy_leagues_types');
		if ($onlyActive) {
			$ci->db->where('active',1);
		}
		$ci->db->order_by('id','asc');
		$query = $ci->db->get();
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$result = $result + array($row->id=>$row->leagueType);
			}
		} // END if
		$query->free_result();
		if (($selectBox && sizeof($result) == 1)) {
			$result = $result + array(' '=>'No league types found');
		}
		return $result;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists('getOOTPLeagueDetails')) {
	function getOOTPLeagueDetails($league_id = -1) {
		//$date = date('Y-m-d');
		if ($league_id == -1) return;
		$details = false;
		$ci =& get_instance();
		// LOAD CHARACTERS
		$ci->db->select('start_date, current_date, league_state, league_level');
		$ci->db->from('leagues');
		$ci->db->where('league_id',$league_id);
		$query = $ci->db->get();
		if ($query->num_rows() > 0) {
			$details = $query->row();
		} // END if
		$query->free_result();
		return $details;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('getLeagueStartDate')) {
	function getLeagueStartDate($league_id = -1) {
		//$date = date('Y-m-d');
		if ($league_id == -1) return;

		$ci =& get_instance();
		// LOAD CHARACTERS
		$ci->db->select('start_date');
		$ci->db->from('leagues');
		$ci->db->where('league_id',$league_id);
		$query = $ci->db->get();
		if ($query->num_rows() > 0) {
			$row = $query->row();
			$date = $row->start_date;
		} // END if
		$query->free_result();
		return $date;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('getVisibleDays')) {
	function getVisibleDays($league_date, $sim_length) {

		//echo("getVisibleDays: league_date = ".$league_date."<br />");
		//echo("sim_length = ".$sim_length."<br />");
		if (!isset($league_date)) return;
		$days = array($league_date);
		$d = strtotime($league_date);
		$days_added = 1;
		while ($days_added < $sim_length) {
			array_push($days,date('Y-m-d',$d+($days_added*60*60*24)));
			$days_added++;
		}
		//echo("days_added = ".$days_added."<br />");
		//echo("days = ".sizeof($days)."<br />");
		return $days;
	}
}
if ( ! function_exists('getOOTPTeamAbbrs')) {
	function getOOTPTeamAbbrs($ootp_league_id,$year) {
		$teams = array();
		$ci =& get_instance();
		$query=$ci->db->query("SELECT team_id,year,abbr,name,nickname FROM team_history UNION SELECT team_id,'$year',abbr,name,nickname FROM teams ORDER BY team_id,year;");
		$prevTid=-1;
		if ($query->num_rows() > 0) {
			foreach($query->result_array() as $row) {
				$tid=$row['team_id'];
				$yr=$row['year'];
				$teams[$tid][$yr]=$row['abbr'];
				$tname=$row['name']." ".$row['nickname'];
				$teamnames[$tid][$yr]=$tname;
				if ($prevTid!=$tid) {$teamnames[$tid]['ing']=$tname;}
				$prevTid=$tid;
			}
		}
		return $teams;
	}
}


// ------------------------------------------------------------------------

if ( ! function_exists('getLeagueAwardsNames')) {
	function getLeagueAwardsNames($ootp_league_id) {

		$awardName = array();
		$ci =& get_instance();
		$ci->db->select('mvp_award_name,pitcher_award_name,rookie_award_name,defense_award_name');
		$ci->db->where("league_id",$ootp_league_id);
		$query = $ci->db->get('leagues');
		if ($query->num_rows() > 0) {
			$row = $query->row_array();
			$awardName[5]=$row['mvp_award_name'];
			$awardName[4]=$row['pitcher_award_name'];
			$awardName[6]=$row['rookie_award_name'];
			$awardName[7]=$row['defense_award_name'];
			$awardName[9]='All-Star';
		}
		$query->free_result();
		return $awardName;
	}
}
/**----------------------------------------------------------
/
/	GET PLAYERS OOTP TEAM
/	Returns the OOTP Team ID for the passed player_id
/
/	@param	$player_id 		(int)	The OOTP Player_id
/	@return					(int)	Team id or -1
/
/	@since	1.0.3 PROD
/
/-----------------------------------------------------------*/
if ( ! function_exists('getPlayerOOTPTeam')) {
	function getPlayerOOTPTeam($player_id) {
		
		if (!isset($ci)) $ci =& get_instance();
		/**
		 * GET PROJECTED PITCHING  STARTS
		 */
		$team_id = -1;
		$ci->db->flush_cache();
		$ci->db->select('team_id');
		$ci->db->where('player_id', $player_id);
		$query = $ci->db->get('players');
		if ($query->num_rows() > 0) {
			$row = $query->row();
			$team_id = $row->team_id;
		}
		$query->free_result();
		return $team_id;
	}
}

/**----------------------------------------------------------
/
/	GET PITCHERS STARTS
/	Returns an array of starters for an OOTP team if the passed
/	player id is present in that teams starters list.
/
/	@param	$player_id 		(int)	The OOTP Player_id
/	@return					(Array)	Array of player ids
/
/	@since	1.0.3 PROD
/
/-----------------------------------------------------------*/
if ( ! function_exists('getPitcherStarts')) {
	function getPitcherStarts($player_id) {

		if (!isset($ci)) $ci =& get_instance();
		/**
		 * GET PROJECTED PITCHING  STARTS
		 */
		$startsArr = array();
		$ci->db->flush_cache();
		$ci->db->select();
		$where =  '(starter_0 = '.$player_id.' OR starter_1 = '.$player_id;
		$where .= ' OR starter_2 = '.$player_id.' OR starter_3 = '.$player_id;
		$where .= ' OR starter_4 = '.$player_id.' OR starter_5 = '.$player_id;
		$where .= ' OR starter_6 = '.$player_id.' OR starter_7 = '.$player_id.')';
		$ci->db->where($where);
		$query = $ci->db->get('projected_starting_pitchers');
		//echo($ci->db->last_query()."<br />");
		if ($query->num_rows() > 0) {
			$startsArr = $query->result_array();
		}
		$query->free_result();
		return $startsArr;
	}
}
/**----------------------------------------------------------
/
/	GET PITCHERS SCHEDULE
/	Returns an array of upcomiong games for the passed 
/	player based ont he teams schedule. If the player 
/	is a pitcher, the function calls getPitcherStarts() and
/	will set 'start' to 1 if the pitcher is prohjected to start
/	for a given game.
/
/	@param	$players 		(Array)			Array of layer information
/	@param	$start_date		(Date String)	Starting date for scheudle
/	@param	$simLen			(int)			Number of days of games to return
/	@return					(Array)			Array of schedule for Team->Lineup page
/
/	@since	0.1 BETA
/	@changelog				1.0.3 PROD - Added call to getPitcherStarts()
/							Also fixed handling of off days and doubleheaders in schedule
/
/-----------------------------------------------------------*/
if ( ! function_exists('getPlayerSchedules')) {
	function getPlayerSchedules($players,$start_date,$simLen) {

		$daysInPeriod = getVisibleDays($start_date,$simLen);
		$schedules = array('players_active'=>array(),'players_reserve'=>array(),'players_injured'=>array());
		$ci =& get_instance();
		// LOAD PLAYERS
		if (is_array($players) && sizeof($players) > 0) {
			foreach ($players as $arr_id => $players_arr) {
				if ($arr_id == 0) { $list = 'players_active'; } else if ($arr_id == 1) { $list = 'players_reserve'; } else { $list = 'players_injured'; }
				if (is_array($players_arr) && sizeof($players_arr) > 0) {
					foreach ($players_arr as $id => $data) {
						// GET START PROJECTIONS for pitchers
						$projStarts = array();
						if ($data['position'] == 1 && ($data['role'] == 11 || $data['role'] == 12)) {
							$projStarts = getPitcherStarts($id);
						}
						$ci->db->flush_cache();
						$ci->db->select('game_id,home_team,away_team,games.date AS game_date,time AS game_time');
						$ci->db->where("DATEDIFF('".$start_date."',games.date)<=",0);
						$ci->db->where("DATEDIFF('".$start_date."',games.date)>-",$simLen);
						$ci->db->where('(home_team = '.$data['team_id'].' OR away_team = '.$data['team_id'].')');
						$ci->db->order_by('games.date','asc');
						$query = $ci->db->get('games');
						//echo($ci->db->last_query()."<br />");
						$player_schedule = array();
						$offDay = 0;
						$startDay = 0;
						if ($query->num_rows() > 0) {
							$dateCount = 0;
							$prevDate = -1;
							$results = $query->result_array();
							while ($dateCount < 7) {
							//foreach ($query->result() as $row) {
								$start = -1;
								$calendar_date = strtotime($daysInPeriod[$dateCount]);
								$games = array();
								foreach($results as $game_data) {
									$game_date = strtotime($game_data['game_date']);
									if ($game_date == $calendar_date) {
										array_push($games, $game_data);
									}
								}
								if (sizeof($games) > 0) {
									if ($data['position'] == 1 && ($data['role'] == 11 || $data['role'] == 12)) 
										if (isset($projStarts) && isset($projStarts[0]) && $projStarts[0]['starter_'.$startDay] == $id) $start = 1;
									foreach($games as $game_data) {
										$player_schedule = $player_schedule + array($game_data['game_id']=>array('home_team'=>$game_data['home_team'],
															   'away_team'=>$game_data['away_team'],'game_date'=>$game_data['game_date'],
															   'game_time'=>$game_data['game_time'],'start'=>$start));
									}
									$startDay++;
								} else {
									$player_schedule = $player_schedule + array(($offDay-=1)=>array('game_date'=>$daysInPeriod[$dateCount],'home_team'=>-1,
															   'away_team'=>-1));
								}
								$dateCount++;
							} // END foreach
							if (sizeof($player_schedule) < intval($simLen)) {
								$player_schedule = $player_schedule + array(($offDay-=1)=>array('home_team'=>-1,
												   'away_team'=>-1));
							}
						} // END if
						$query->free_result();
						$schedules[$list] = $schedules[$list] + array($id=>$player_schedule);
					} // END foreach
				} // END if
			} // END foreach
		} // END if
		return $schedules;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists('getOOTPTeams')) {
	function getOOTPTeams($league_id,$selectBox = true) {
		if ($selectBox)
			$result = array(' '=>'Select Team');
		else
			$result = array();

		$ci =& get_instance();
		// LOAD TEAMS

		$ci->db->select('team_id,abbr,name,nickname,logo_file_name')
				 ->from('teams')
				 ->where('league_id',$league_id)
				 ->where('allstar_team',0)
				 ->order_by('name,nickname','asc');
		$query = $ci->db->get();
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$tname=$row->name." ".$row->nickname;
      			$tname=str_replace(".","",$tname);
				if (!$selectBox) {
					$result = $result + array($row->team_id=>array('name'=>$tname,'nickname'=>$row->nickname,
					'abbr'=>$row->abbr,'logo_file_name'=>$row->logo_file_name));
				} else {
					$result = $result + array($row->team_id=>$tname);
				}
			}
		} // END if
		$query->free_result();
		if (($selectBox && sizeof($result) == 1)) {
			$result = $result + array(' '=>'No teams found');
		}
		return $result;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists('getDaysInBetween')) {
	function getDaysInBetween($start, $end) {
		// Vars
		$day = 86400; // Day in seconds
		$format = 'Y-m-d'; // Output format (see PHP date funciton)
		$sTime = strtotime($start); // Start as time
		$eTime = strtotime($end); // End as time
		$numDays = round(($eTime - $sTime) / $day) + 1;
		$days = array();

		// Get days
		for ($d = 0; $d < $numDays; $d++) {
			$days[] = date($format, ($sTime + ($d * $day)));
		}

		// Return days
		return $days;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists('getFantasyTeams')) {
	function getFantasyTeams($league_id,$selectBox = true) {
		if ($selectBox)
			$result = array(' '=>'Select Team');
		else
			$result = array();
		$ci =& get_instance();
		// LOAD TEAMS
		$ci->db->select('id,teamname, teamnick')
				 ->from('fantasy_teams')
				 ->where('league_id',$league_id)
				 ->order_by('teamname,teamnick','asc');
		$query = $ci->db->get();
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$teamname=$row->teamname." ".$row->teamnick;
      			$teamname=str_replace(".","",$teamname);
				$result = $result + array($row->id=>$teamname);
			}
		} // END if
		$query->free_result();
		if (($selectBox && sizeof($result) == 1)) {
			$result = $result + array(' '=>'No teams found');
		}
		return $result;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists('getMonths')) {
	function getMonths() {
		return array('0'=>'Select Month',
			  '1'=>'January',
			  '2'=>'February',
			  '3'=>'March',
			  '4'=>'April',
			  '5'=>'May',
			  '6'=>'June',
			  '7'=>'July',
			  '8'=>'August',
			  '9'=>'September',
			  '10'=>'October',
			  '11'=>'Novemeber',
			  '12'=>'December');
	} // END function
} // END if

// ------------------------------------------------------------------------

if ( ! function_exists('getDays')) {
	function getDays() {
		$days = array('0'=>'Select Day');
		for ($i = 1; $i < 32; $i++) {
			$days = $days + array($i=>$i);
		}
		return $days;
	} // END function
} // END if


// ------------------------------------------------------------------------

if ( ! function_exists('getYears')) {
	function getYears($startYear = false,$endYear = false) {
		if ($startYear === false)
			$startYear = date('Y');
		if ($endYear === false)
			$endYear = $startYear - 100;
		$years = array('0'=>'Select Year');
		for ($j = $startYear ; $j >= $endYear; $j--) {
			$years = $years + array($j=>$j);
		}
		return $years;
	} // END function
} // END if

// ------------------------------------------------------------------------

if ( ! function_exists('getHours')) {
	function getHours() {
		$retArray = array();
		for ($c=1;$c<=12;$c++) {
			$x = str_pad( strval($c), 2, '0', STR_PAD_LEFT);
			$retArray = $retArray + array($x=>$x);
		}
		return $retArray;
	} // END function
} // END if

// ------------------------------------------------------------------------

if ( ! function_exists('getMinutes')) {
	function getMinutes($limit = false) {
		$retArray = array();
		if ($limit) {
			$retArray = $retArray + array('00'=>'00');
			$retArray = $retArray + array('15'=>'15');
			$retArray = $retArray + array('30'=>'30');
			$retArray = $retArray + array('45'=>'45');
		} else {
			for ($c=0;$c<=60;$c++) {
				$x = str_pad( strval($c), 2, '0', STR_PAD_LEFT);
				$retArray = $retArray + array($x=>$x);
			}
		}
		return $retArray;
	} // END function
} // END if

// ------------------------------------------------------------------------

if ( ! function_exists('getAMPM')) {
	function getAMPM() {
		return array('AM'=>'AM','PM'=>'PM');
	} // END function
} // END if

// ------------------------------------------------------------------------

if ( ! function_exists('loadStates')) {
	function loadStates() {
		$states = array();
		$ci =& get_instance();
		$ci->db->select('stateCode, stateName')
						->from('states_us')
						->order_by('stateName','asc');

		$query = $ci->db->get();
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$states = $states + array($row->stateCode=>$row->stateName);
			}
		}
		$query->free_result();
		return $states;

	}
}
// ------------------------------------------------------------------------

if ( ! function_exists('loadCountries')) {
    function loadCountries($selectBox = true) {

        if ($selectBox) {
            $result = array(' '=>'Select Country');
        } else {
            $result = array();
        }
        $ci =& get_instance();
        $ci->db->select('id, cntryName')
            ->from('countries')
            ->order_by('id','asc');
        $query = $ci->db->get();
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $result = $result + array($row->id=>$row->cntryName);
            }
        }
        $query->free_result();
        return $result;

    }
}

// ------------------------------------------------------------------------
/**
 * 	LOAD TIMEZONES.
 * 	Loads a list of timezone identifers for selection by the user.
 *
 * 	@param	$selectBox	(Boolean)	TRUE if the list will populate a select box, FALSE otherwise
 * 	@return				array		Array of timezones
 * 	@since				1.0.6
 */
if ( ! function_exists('loadTimezones')) {
	function loadTimezones($selectBox = true) {

		if ($selectBox) {
			$result = array(' '=>'Select Timezone');
		} else {
			$result = array();
		}
		$continent = '';
	    $timezone_identifiers = DateTimeZone::listIdentifiers();
	    foreach( $timezone_identifiers as $value ){
	        if ( preg_match( '/^(America|Antartica|Arctic|Asia|Atlantic|Europe|Indian|Pacific)\//', $value ) ){
	            $ex=explode("/",$value);//obtain continent,city
	            //if ($continent!=$ex[0]){
	            //    $result = $result + array('X'=>$ex[0]);
	           // }
	            $city=$ex[1];
	            $continent=$ex[0];
	            $result = $result + array($value=>$continent."/".$city);
	        }
	    }
		return $result;
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('loadProjectsBugs')) {
	function loadProjectsBugs($projectId,$selectBox = true) {
		if ($selectBox)
			$result = array(' '=>'Select Bug');
		else
			$result = array();

		$ci =& get_instance();
		// LOAD CHARACTERS
		//echo("Event ID = ".$eventId."<br />");
		$ci->db->select('admin_bugs.id, admin_bugs.summary, bugStatus,severity, priority')
				 ->from('admin_bugs')
				 ->join('admin_list_status','admin_list_status.id = admin_bugs.bugStatusId','right outer')
				 ->join('admin_list_severities','admin_list_severities.id = admin_bugs.severityId','right outer')
				 ->join('admin_list_priorities','admin_list_priorities.id = admin_bugs.priorityId','right outer')
				 ->where('projectId',$projectId)
				 //->where('regStatus',1)
				 ->order_by('admin_bugs.bugStatusId, admin_bugs.priorityId','asc');
		$query = $ci->db->get();
		//echo("Num rows = ".$query->num_rows()."<br />");
		//echo("Query = ".$ci->db->last_query()."<br />");
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$result = $result + array($row->id=>array('summary'=>$row->summary,
														  'bugStatus'=>$row->bugStatus,
														  'severity'=>$row->severity,
														  'priority'=>$row->priority));
			}
		} // END if
		$query->free_result();
		if (($selectBox && sizeof($result) == 1)) {
			$result = $result + array(' '=>'No bugs were found');
		}
		return $result;
	}
}
// ------------------------------------------------------------------------
if ( ! function_exists('loadOOTPVersions')) {
	function loadOOTPVersions($selectBox = true) {

		return array("17"=>"OOTP 17",
					 "18"=>"OOTP 18",
					 "19"=>"OOTP 19",
					 "20"=>"OOTP 20",
					 "21"=>"OOTP 21",
					 "22"=>"OOTP 22",
					 "23"=>"OOTP 23",
					 "24"=>"OOTP 24",
					 "25"=>"OOTP 25",
					 "26"=>"OOTP 26"
					 );
	}
}
// ------------------------------------------------------------------------
if ( ! function_exists('getOOTPGameVersion')) {
	function getOOTPGameVersion($version = -1) {

		$outVer = -1;
		$versions = loadOOTPVersions();
		foreach ($versions as $ver => $label) {
			if ($ver == $version) {
				$outVer = $label;
				brek;
			}
		}
		return $outVer;
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('loadSecurityClasses')) {
	function loadSecurityClasses($selectBox = true) {

		return array("=1"=>"None",
					 "1"=>"Critical (Registration, Contact Pages Only)",
					 "2"=>"Public (All Public Forms)",
					 "3"=>"Complete (All Public Forms, Login, Team Requests)");
	}
}
// ------------------------------------------------------------------------
if ( ! function_exists('loadRecaptchaThemes')) {
	function loadRecaptchaThemes($selectBox = true) {

		return array("red"=>"Red",
					 "white"=>"White",
					 "blackglass"=>"Black Glass",
					 "clean"=>"Clean");
	}
}

// ------------------------------------------------------------------------
if ( ! function_exists('loadRecaptchaLangs')) {
	function loadRecaptchaLangs($selectBox = true) {

		return array("en"=>"English",
					 "nl"=>"Dutch",
					 "fr"=>"French",
					 "de"=>"German",
					 "pt"=>"Portuguese",
					 "ru"=>"Russian",
					 "es"=>"Spanish",
					 "tr"=>"Turkish");
	}
}
/* End of file dataList_helper.php */
/* Location: ./system/helpers/dataList_helper.php */
