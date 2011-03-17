<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Adminstration Helpers
 *
 * @package		CodeIgniter
 * @subpackage	Helpers
 * @category	Helpers
 * @author		Jeff Fox
 * @description	Various helpers used by the admin dashboard
 */
// ------------------------------------------------------------------------

if ( ! function_exists('getCurrentScoringPeriod')) {
	function getCurrentScoringPeriod($league_date = false) { 
		$period = "";
		if ($league_date === false) {
			$period= "error: No league date provided";
		} else {
			$ci =& get_instance(); 
			$ci->db->select("id, date_start, date_end");
			$ci->db->where("DATEDIFF('".$league_date."',date_start)>=",0);
			$ci->db->where("DATEDIFF('".$league_date."',date_end)<=",0);
			$query = $ci->db->get("fantasy_scoring_periods");
			if ($query->num_rows() > 0) {
				$row = $query->row();
				$period = array('id'=>$row->id, 'date_start'=>$row->date_start, 'date_end'=>$row->date_end);
			} else {
				$period = array('id'=>-1, 'date_start'=>NULL, 'date_end'=>NULL);
			}
			$query->free_result();
		}
		return $period;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists('getScoringPeriod')) {
	function getScoringPeriod($scoring_period_id = false) { 
		$period = "";
		if ($scoring_period_id === false) {
			$period= "error: No id was provided.";
		} else {
			$ci =& get_instance(); 
			$ci->db->select("id, date_start, date_end");
			$ci->db->where("id",$scoring_period_id);
			$query = $ci->db->get("fantasy_scoring_periods");
			if ($query->num_rows() > 0) {
				$row = $query->row();
				$period = array('id'=>$row->id, 'date_start'=>$row->date_start, 'date_end'=>$row->date_end);
			}
			$query->free_result();
		}
		return $period;
	}
}

if ( ! function_exists('getScoringPeriodByDate')) {
	function getScoringPeriodByDate($date = false) { 
		$period = "";
		if ($date === false) {
			$period= "Error: No date was provided.";
		} else {
			$ci =& get_instance(); 
			$ci->db->select("id, date_start, date_end");
			$ci->db->where("DATEDIFF('".$date."',date_start)>=",0);
			$ci->db->where("DATEDIFF('".$date."',date_end)<=",0);
			$query = $ci->db->get("fantasy_scoring_periods");
			if ($query->num_rows() > 0) {
				$row = $query->row();
				$period = array('id'=>$row->id, 'date_start'=>$row->date_start, 'date_end'=>$row->date_end);
			}
			$query->free_result();
		}
		return $period;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists('getScoringPeriods')) {
	function getScoringPeriods() { 
		$periods = array();
		$ci =& get_instance(); 
		$ci->db->select("id, date_start, date_end");
		$query = $ci->db->get("fantasy_scoring_periods");
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				array_push($periods,array('id'=>$row->id, 'date_start'=>$row->date_start, 'date_end'=>$row->date_end));
			}
		}
		$query->free_result();
		return $periods;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists('getAvailableScoringPeriods')) {
	function getAvailableScoringPeriods($league_id = 0) { 
		
		$periods = array();
		$ci =& get_instance(); 
		$ci->db->select("scoring_period_id");
		$ci->db->where("league_id", $league_id);
		$ci->db->group_by("scoring_period_id");
		$query = $ci->db->get("fantasy_players_scoring");
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				$period = getScoringPeriod($row->scoring_period_id);
				array_push($periods,array('id'=>$period['id'], 'date_start'=>$period['date_start'], 'date_end'=>$period['date_end']));
			}
		}
		$query->free_result();
		return $periods;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('getSimSummaries')) {
	function getSimSummaries($countOnly = false) { 
		
		$summaries = array();
		$ci =& get_instance(); 
		$ci->db->select("id,scoring_period_id,sim_date,process_time,sim_result,sim_summary,comments");
		$ci->db->from("fantasy_sim_summary");
		if ($countOnly === false) {
			$ci->db->order_by("scoring_period_id","desc");
			$query = $ci->db->get();
			if ($query->num_rows() > 0) {
				foreach($query->result() as $row) {
					array_push($summaries,array('id'=>$row->id, 'sim_date'=>$row->sim_date, 'scoring_period_id'=>$row->scoring_period_id,
												'sim_result'=>$row->sim_result,'process_time'=>$row->process_time,'sim_summary'=>$row->sim_summary,'comments'=>$row->comments));
				}
			}
			$query->free_result();
		} else {
			 $summaries = $ci->db->count_all_results();
		}
		return $summaries;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists('loadSimSummary')) {
	function loadSimSummary($sumId = false) { 
		
		if ($sumId === false) { return false; }
		
		$summary = array();
		$ci =& get_instance(); 
		$ci->db->select("id,scoring_period_id,sim_date,process_time,sim_result,sim_summary,comments");
		$ci->db->where("id",$sumId);
		$query = $ci->db->get("fantasy_sim_summary");
		if ($query->num_rows() > 0) {
			$summary = $query->row();
		}
		$query->free_result();
		return $summary;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists('getScoringPeriodCount')) {
	function getScoringPeriodCount() { 
		$count = 0;
		$ci =& get_instance(); 
		$ci->db->select("id");
		$ci->db->from("fantasy_scoring_periods");
		return $ci->db->count_all_results();
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists('createScoringSchedule')) {
	function createScoringSchedule($league_id = false,$sim_length = 7) {
		
		if ($league_id === false) return false;
		$errors = "";
		$ci =& get_instance(); 
		$start_date = '';
		$league_games = 162;
		
		$ci->db->select("start_date, rules_schedule_games_per_team");
		$ci->db->where("league_id",$league_id);
		$query = $ci->db->get("leagues");
		if ($query->num_rows() > 0) {
			$row = $query->row();
			$league_start = $row->start_date;
			$league_games = $row->rules_schedule_games_per_team;
		}
		$ci->db->flush_cache();
		$ci->db->select("start_date, rules_schedule_games_per_team");
		$ci->db->where("league_id",$league_id);
		$query = $ci->db->get("leagues");
		if ($query->num_rows() > 0) {
			$row = $query->row();
			$league_start = $row->start_date;
			$league_games = $row->rules_schedule_games_per_team;
		}
		$query->free_result();
		$periodCount = intval($league_games / ($sim_length-1));
		
		$ci->db->flush_cache();
		$ci->db->query('TRUNCATE TABLE fantasy_scoring_periods'); 
		
		$periods = array();
		$date_start = strtotime($league_start);
		for ($i = 0; $i < $periodCount; $i++) {
			$date_end = $date_start + (60*60*24*($sim_length-1));
			
			$ci->db->insert('fantasy_scoring_periods',array('id'=>($i+1),'date_start'=>date('Y-m-d',$date_start),'date_end'=>date('Y-m-d',$date_end)));
			$date_start = $date_end + (60*60*24);
		}
		if (empty($errors)) $errors = "OK"; else  $errors = $errors;
		return $errors;
	}	
}
// ------------------------------------------------------------------------

if ( ! function_exists('reset_league_data')) {
	function reset_league_data() {
		$ci =& get_instance(); 
		$ci->db->flush_cache();
		$ci->db->query('TRUNCATE TABLE fantasy_leagues_games'); 
		return true;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists('reset_draft')) {
	function reset_draft() {
		$ci =& get_instance(); 
		$ci->db->flush_cache();
		$ci->db->query('TRUNCATE TABLE fantasy_draft'); 
		$ci->db->query('TRUNCATE TABLE fantasy_draft_list'); 
		$ci->db->flush_cache();
		$ci->db->set('completed',-1);
		$ci->db->update('fantasy_draft_config'); 
		if ($ci->db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists('reset_team_data')) {
	function reset_team_data() {
		$ci =& get_instance(); 
		$ci->db->flush_cache();
		$ci->db->query('TRUNCATE TABLE fantasy_teams_record'); 
		$ci->db->query('TRUNCATE TABLE fantasy_rosters'); 
		$ci->db->query('TRUNCATE TABLE fantasy_teams_waiver_claims'); 
		return true;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists('reset_player_data')) {
	function reset_player_data() {
		$ci =& get_instance(); 
		$ci->db->flush_cache();
		$ci->db->query('TRUNCATE TABLE fantasy_players_scoring'); 
		$ci->db->query('TRUNCATE TABLE fantasy_players');
		$ci->db->query('TRUNCATE TABLE fantasy_players_waivers'); 
		return true;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('reset_transactions')) {
	function reset_transactions() {
		$ci =& get_instance(); 
		$ci->db->flush_cache();
		$ci->db->query('TRUNCATE TABLE fantasy_transactions');
		return true;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists('reset_sim_summary')) {
	function reset_sim_summary() {
		$ci =& get_instance(); 
		$ci->db->flush_cache();
		$ci->db->query('TRUNCATE TABLE fantasy_sim_summary');
		return true;
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists('save_sim_summary')) {
	function save_sim_summary($result,$process_time,$summary,$comments = "") {
		$ci =& get_instance(); 
		$ci->db->flush_cache();
		$ci->db->set('sim_result',$result);
		$ci->db->set('sim_summary',$summary);
		$ci->db->set('process_time',$process_time);
		$ci->db->set('comments',$comments);
		$ci->db->insert('fantasy_sim_summary'); 
		if ($ci->db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists('reset_ootp_league')) {
	function reset_ootp_league($league_id) {
		$ci =& get_instance(); 
		$ci->db->flush_cache();
		$ci->db->select('start_date');
		$ci->db->from('leagues');
		$ci->db->where('league_id',$league_id);
		$query = $ci->db->get();
		if ($query->num_rows() > 0) {
			$row = $query->row();
			$start_date = $row->start_date;
		}
		$query->free_result();
		$ci->db->flush_cache();
		$ci->db->set('current_date',$start_date);
		$ci->db->where('league_id',$league_id);
		$ci->db->update('leagues'); 
		if ($ci->db->affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}
}
// ------------------------------------------------------------------------

if ( ! function_exists('createLeagueSchedules')) {
	function createLeagueSchedules() {
		
		$errors = "";
		$ci =& get_instance(); 
		// LOAD scoring period list
		$s_periods = array();
		$leagues = array();
		$ci->db->flush_cache();
		$ci->db->select("id");
		$query = $ci->db->get("fantasy_scoring_periods");
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				array_push($s_periods,$row->id);
			}
		}
		$query->free_result();
		// LOAD all Fantasy leagues
		$ci->db->flush_cache();
		$ci->db->select("id,league_name");
		$query = $ci->db->get("fantasy_leagues");
		if ($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				$team_list = array();
				// GET TEAM LIST FOR THIS LEAGUE
				$ci->db->flush_cache();
				$ci->db->select("id");
				$ci->db->where("league_id",$row->id);
				$tQuery = $ci->db->get("fantasy_teams");
				if ($tQuery->num_rows() > 0) {
					foreach($tQuery->result() as $tRow) {
						array_push($team_list,$tRow->id);
					}
				}
				$tQuery->free_result();
				$leagues = $leagues + array($row->id=>array('league_name'=>$row->league_name,'team_list'=>$team_list));
			}
		}
		$query->free_result();
		
		if (sizeof($s_periods) > 0 && sizeof($leagues) > 0) {
			$ci->db->flush_cache();
			$ci->db->query('TRUNCATE TABLE fantasy_leagues_games'); 
			
			foreach($leagues as $league_id => $data) {
				$teamCount = sizeof($data['team_list']);
				//echo("Teams in league ".$data['league_name']." = ".$teamCount."<br />");
				if ($teamCount == 0 || ($teamCount % 2) != 0) {
					$errors = "The <b>".$data['league_name']."</b> league has an illegal number of teams. A schedule could not be generated for this league.";
				} else {
					// LOAD THE APPROPRIATE SCHEDULE TEMPLATE FOR THIS COUNT
					
					// LOOP THROUGH EACH SCORING PERIOD AND CREATE GAMES
					foreach($s_periods as $pId) {
						
					}
				}
			}
		}
		if (empty($errors)) $errors = "OK"; else  $errors = $errors;
		return $errors;
	}	
}
// ------------------------------------------------------------------------
/**
 *	GET LATEST MOD VERSION
 *
 *	Opens a Curl connection to the internal UPDATE_URL value and downloads and 
 *	[arses the latest public mod version.
 *
 *	@param	$debug		TRUE to display debug messaging, FALSE to bypass
 @	return				(Array - CSS Class, Status Message)
 *
 *	@since	1.0.3
 */
if ( ! function_exists('getLatestModVersion')) {
	function getLatestModVersion($debug = false) {
		
		$version_check = array();
		$ci =& get_instance();
		$ci->load->library('curl');
		if (defined('UPDATE_URL')) {
			$raw_version = $ci->curl->simple_get(UPDATE_URL);
		} else {
			$raw_version = false;
		}
		if ($raw_version !== false && !empty($raw_version)) {
			$web_version = explode("=",$raw_version);
			$version_check = checkModVersion($web_version[1]);
		} else {
			$version_check = array("warn",$ci->lang->line('admin_version_check_error'));
		}
		if ($debug) {
			echo($raw_version."<br />");
			echo($ci->curl->debug()."<br />");
		}
		return $version_check;
	}
}

// ------------------------------------------------------------------------
/**
 *	CHECK MOD VERSION
 *
 *	Tests the passed version against the mods local version constant and return 
 *	the appropriate status message.
 *
 *	@param	$version	The remote version string
 @	return				(Array - CSS Class, Status Message
 *
 *	@since	1.0.3
 */
if ( ! function_exists('checkModVersion')) {
	function checkModVersion($version = false) {
		$ci =& get_instance();
		$result = array();
		if ($version !== false) {
			$current = true;
			$local_version = str_replace(" Beta","",SITE_VERSION);
			$version = str_replace(" Beta","",$version);
			
			$mod_version = explode(".",$local_version);
			$web_version = explode(".",$version);
			for ($i = 0; $i < sizeof($mod_version); $i++) {
				if (isset($mod_version[$i]) && isset($web_version[$i])) {
					if (intval($mod_version[$i]) < intval($web_version[$i])) {
						$current = false;
					}
				}
			}
			if (!$current) {
				return array('error',str_replace('[NEW_VERSION]',$version,$ci->lang->line('admin_version_outdated')));
			} else {
				return array('success',$ci->lang->line('admin_version_current'));
			}
		} else {
			return array('error',$ci->lang->line('admin_version_no_value'));
		}
	}
}

// ------------------------------------------------------------------------
/**
 *	UPDATE DB CONNECTION FILE
 *
 *	This function searches for an instance of the old statslab db connection 
 * 	file in the SQL uploads directory and if possibler, renames it to the new 
 *	named used from 1.0.3 onwards.
 *
 *	@param	$statsLabIgnore	Bypass this function if this mod is being run alongside StatsLab
 *	@param	$sqlPath		Path to the MySQL Upload Directory
 @	return					TRUE on update, FALSE on no action
 *
 *	@since	1.0.3
 */
if ( ! function_exists('updateDBFile')) {
	function updateDBFile($statsLabIgnore,$sqlPath) {
		$updated = false;
		if (!$statsLabIgnore) {
			$ci =& get_instance();
			$delSLFile = false;
			if (defined('DB_CONNECTION_FILE') && defined('SL_CONNECTION_FILE')) {
				if (file_exists($sqlPath."/".SL_CONNECTION_FILE) && !file_exists($sqlPath."/".DB_CONNECTION_FILE)) {
					copy($sqlPath."/".SL_CONNECTION_FILE, $sqlPath."/".DB_CONNECTION_FILE);
					$delSLFile = true;
					$updated = true;
				} else if (file_exists($sqlPath."/".SL_CONNECTION_FILE) && file_exists($sqlPath."/".DB_CONNECTION_FILE)) {
					$delSLFile = true;
				} // END if 
			} // END if 
			if ($delSLFile) {
				unlink($sqlPath."/".SL_CONNECTION_FILE);
			} // END if 
		}
		return $updated;
	}
}

/* End of file display_helper.php */
/* Location: ./system/helpers/display_helper.php */