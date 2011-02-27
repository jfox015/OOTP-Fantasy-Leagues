<?php
/**
 *	GENERAL HELPER
 *
 *	This helper includes a general set of functions used by the site to run common 
 * 	helper functions such as reslving positon names and numbers, building common 
 *	stat queries and display lists and more.
 *
 * 	@author 	All functions are written by Frank Holmes unless otherwise noted.
 */
function getUsername($userId, $access = false) {
	$ci =& get_instance();
	if (!isset($ci->user_auth_model)) {
		$ci->load->model('user_auth_model');
	}
	return $ci->user_auth_model->getUsername($userId, $access);
}
function sendEmail($to,$fromEmail, $fromName,$subject,$message,$headers) {
	$ci =& get_instance();
	$this->ci->email->clear();
	$ci->email->set_newline("\r\n");
	$ci->email->from($fromEmail,$fromName);
	$ci->email->to($to);
	$tradeTypes = loadSimpleDataList('tradeStatus');
	$ci->email->subject($subject);
	$ci->email->message($message);
	$ci->email->headers($headers);
	if ((!defined('ENV') || (defined('ENV') && ENV != 'dev'))) {
		if ($this->email->send()) {
			return true;
		} else {
			return false;
		}
	} else {
		return $message;
	}
}
/**
 *	PLAYERS STAT QUERY BUILDER
 *
 *	Builds the standard SELECT statements for stat queries sitewide.
 *
 * 	@author	Jeff Fox
 *	@since	1.0
 */ 
function player_stat_query_builder($player_type = 1, $query_type = QUERY_STANDARD, $rules = array(),$sumTotals = true) {
	$sql = '';
	$sqlOperator = 'SUM';
	if (!$sumTotals) { $sqlOperator = 'AVG'; }
	if ($player_type == 1) {
		// BATTERS
		switch ($query_type) {
			case QUERY_BASIC:
				$sql .= 'if('.$sqlOperator.'(ab)=0,0,'.$sqlOperator.'(h)/'.$sqlOperator.'(ab)) as avg,'.$sqlOperator.'(hr) as hr,'.$sqlOperator.'(rbi) as rbi,'.$sqlOperator.'(bb) as bb,'.$sqlOperator.'(k) as k,'.$sqlOperator.'(sb) as sb, if(('.$sqlOperator.'(ab)+'.$sqlOperator.'(bb)+'.$sqlOperator.'(hp)+'.$sqlOperator.'(sf))=0,0,('.$sqlOperator.'(h)+'.$sqlOperator.'(bb)+'.$sqlOperator.'(hp))/('.$sqlOperator.'(ab)+'.$sqlOperator.'(bb)+'.$sqlOperator.'(hp)+'.$sqlOperator.'(sf)))+if('.$sqlOperator.'(ab)=0,0,('.$sqlOperator.'(h)+'.$sqlOperator.'(d)+2*'.$sqlOperator.'(t)+3*'.$sqlOperator.'(hr))/'.$sqlOperator.'(ab)) as ops';
				break;
			case QUERY_EXTENDED:
				$sql .= ''.$sqlOperator.'(g) as g,'.$sqlOperator.'(ab) as ab,'.$sqlOperator.'(r) as r,'.$sqlOperator.'(h) as h,'.$sqlOperator.'(d) as d,'.$sqlOperator.'(t) as t,'.$sqlOperator.'(hr) as hr,'.$sqlOperator.'(rbi) as rbi,'.$sqlOperator.'(bb) as bb,'.$sqlOperator.'(k) as k,'.$sqlOperator.'(sb) as sb,'.$sqlOperator.'(cs) as cs,if('.$sqlOperator.'(ab)=0,0,'.$sqlOperator.'(h)/'.$sqlOperator.'(ab)) as avg,if(('.$sqlOperator.'(ab)+'.$sqlOperator.'(bb)+'.$sqlOperator.'(hp)+'.$sqlOperator.'(sf))=0,0,('.$sqlOperator.'(h)+'.$sqlOperator.'(bb)+'.$sqlOperator.'(hp))/('.$sqlOperator.'(ab)+'.$sqlOperator.'(bb)+'.$sqlOperator.'(hp)+'.$sqlOperator.'(sf))) as obp,if('.$sqlOperator.'(ab)=0,0,('.$sqlOperator.'(h)+'.$sqlOperator.'(d)+2*'.$sqlOperator.'(t)+3*'.$sqlOperator.'(hr))/'.$sqlOperator.'(ab)) as slg,if(('.$sqlOperator.'(ab)+'.$sqlOperator.'(bb)+'.$sqlOperator.'(hp)+'.$sqlOperator.'(sf))=0,0,('.$sqlOperator.'(h)+'.$sqlOperator.'(bb)+'.$sqlOperator.'(hp))/('.$sqlOperator.'(ab)+'.$sqlOperator.'(bb)+'.$sqlOperator.'(hp)+'.$sqlOperator.'(sf)))+if('.$sqlOperator.'(ab)=0,0,('.$sqlOperator.'(h)+'.$sqlOperator.'(d)+2*'.$sqlOperator.'(t)+3*'.$sqlOperator.'(hr))/'.$sqlOperator.'(ab)) as ops,if('.$sqlOperator.'(pa)=0,0,(0.72*'.$sqlOperator.'(bb)+0.75*'.$sqlOperator.'(hp)+0.9*('.$sqlOperator.'(h)-'.$sqlOperator.'(d)-'.$sqlOperator.'(t)-'.$sqlOperator.'(hr))+0.92*0+1.24*'.$sqlOperator.'(d)+1.56*'.$sqlOperator.'(t)+1.95*'.$sqlOperator.'(hr))/'.$sqlOperator.'(pa)) as wOBA,'.$sqlOperator.'(pa) as pa, if (('.$sqlOperator.'(k)/'.$sqlOperator.'(ab))*100=0,0,'.$sqlOperator.'(k)/'.$sqlOperator.'(ab)*100) as wiff, if (('.$sqlOperator.'(bb)/('.$sqlOperator.'(ab)+'.$sqlOperator.'(bb)))*100=0,0,'.$sqlOperator.'(bb)/('.$sqlOperator.'(ab)+'.$sqlOperator.'(bb))*100) as walk';
				break;
			case QUERY_STANDARD:
			default:
				$sql .= ''.$sqlOperator.'(g) as g,'.$sqlOperator.'(ab) as ab,'.$sqlOperator.'(r) as r,'.$sqlOperator.'(h) as h,'.$sqlOperator.'(d) as d,'.$sqlOperator.'(t) as t,'.$sqlOperator.'(hr) as hr,'.$sqlOperator.'(rbi) as rbi,'.$sqlOperator.'(bb) as bb,'.$sqlOperator.'(k) as k,'.$sqlOperator.'(sb) as sb,'.$sqlOperator.'(cs) as cs,if('.$sqlOperator.'(ab)=0,0,'.$sqlOperator.'(h)/'.$sqlOperator.'(ab)) as avg,if(('.$sqlOperator.'(ab)+'.$sqlOperator.'(bb)+'.$sqlOperator.'(hp)+'.$sqlOperator.'(sf))=0,0,('.$sqlOperator.'(h)+'.$sqlOperator.'(bb)+'.$sqlOperator.'(hp))/('.$sqlOperator.'(ab)+'.$sqlOperator.'(bb)+'.$sqlOperator.'(hp)+'.$sqlOperator.'(sf))) as obp,if('.$sqlOperator.'(ab)=0,0,('.$sqlOperator.'(h)+'.$sqlOperator.'(d)+2*'.$sqlOperator.'(t)+3*'.$sqlOperator.'(hr))/'.$sqlOperator.'(ab)) as slg,if(('.$sqlOperator.'(ab)+'.$sqlOperator.'(bb)+'.$sqlOperator.'(hp)+'.$sqlOperator.'(sf))=0,0,('.$sqlOperator.'(h)+'.$sqlOperator.'(bb)+'.$sqlOperator.'(hp))/('.$sqlOperator.'(ab)+'.$sqlOperator.'(bb)+'.$sqlOperator.'(hp)+'.$sqlOperator.'(sf)))+if('.$sqlOperator.'(ab)=0,0,('.$sqlOperator.'(h)+'.$sqlOperator.'(d)+2*'.$sqlOperator.'(t)+3*'.$sqlOperator.'(hr))/'.$sqlOperator.'(ab)) as ops,if('.$sqlOperator.'(pa)=0,0,(0.72*'.$sqlOperator.'(bb)+0.75*'.$sqlOperator.'(hp)+0.9*('.$sqlOperator.'(h)-'.$sqlOperator.'(d)-'.$sqlOperator.'(t)-'.$sqlOperator.'(hr))+0.92*0+1.24*'.$sqlOperator.'(d)+1.56*'.$sqlOperator.'(t)+1.95*'.$sqlOperator.'(hr))/'.$sqlOperator.'(pa)) as wOBA, '.$sqlOperator.'(vorp) as vorp,'.$sqlOperator.'(pa) as pa, if (('.$sqlOperator.'(k)/'.$sqlOperator.'(ab))*100=0,0,'.$sqlOperator.'(k)/'.$sqlOperator.'(ab)*100) as wiff, if (('.$sqlOperator.'(bb)/('.$sqlOperator.'(ab)+'.$sqlOperator.'(bb)))*100=0,0,'.$sqlOperator.'(bb)/('.$sqlOperator.'(ab)+'.$sqlOperator.'(bb))*100) as walk,'.$sqlOperator.'(hp) as hp,'.$sqlOperator.'(sf) as sf,('.$sqlOperator.'(d)+'.$sqlOperator.'(t)+'.$sqlOperator.'(hr)) as xbh';
				break;
		} // END switch
		$rulesType = 'batting';
	} else {
		// PITCHERS
		switch ($query_type) {
			case QUERY_BASIC:
				$sql .= 'if(w=0,0,'.$sqlOperator.'(w)) as w,'.$sqlOperator.'(l) as l,if(('.$sqlOperator.'(ip)+('.$sqlOperator.'(ipf)/3))=0,0,9*'.$sqlOperator.'(er)/('.$sqlOperator.'(ip)+('.$sqlOperator.'(ipf)/3))) as era,('.$sqlOperator.'(ip)+('.$sqlOperator.'(ipf)/3)) as ip,'.$sqlOperator.'(bb) as pbb,'.$sqlOperator.'(k) as pk, if(('.$sqlOperator.'(ip)+('.$sqlOperator.'(ipf)/3))=0,0,('.$sqlOperator.'(ha)+'.$sqlOperator.'(bb))/('.$sqlOperator.'(ip)+('.$sqlOperator.'(ipf)/3))) as whip,'.$sqlOperator.'(s) as s';
				break;
			case QUERY_EXTENDED:
				$sql .= ''.$sqlOperator.'(w) as w,'.$sqlOperator.'(l) as l,'.$sqlOperator.'(s) as s,'.$sqlOperator.'(cg) as cg,'.$sqlOperator.'(sho) as sho,if(('.$sqlOperator.'(ip)+('.$sqlOperator.'(ipf)/3))=0,0,9*'.$sqlOperator.'(er)/('.$sqlOperator.'(ip)+('.$sqlOperator.'(ipf)/3))) as era,'.$sqlOperator.'(g) as pg,'.$sqlOperator.'(gs) as gs,('.$sqlOperator.'(ip)+('.$sqlOperator.'(ipf)/3)) as ip,'.$sqlOperator.'(ha) as ha,'.$sqlOperator.'(r) as pr,'.$sqlOperator.'(er) as er,'.$sqlOperator.'(hra) as hra,'.$sqlOperator.'(bb) as pbb,'.$sqlOperator.'(k) as pk, if (('.$sqlOperator.'(k)*9)/'.$sqlOperator.'(ip)=0,0,('.$sqlOperator.'(k)*9)/'.$sqlOperator.'(ip)) as k9, if (('.$sqlOperator.'(bb)*9)/'.$sqlOperator.'(ip)=0,0,('.$sqlOperator.'(bb)*9)/'.$sqlOperator.'(ip)) as bb9, if (('.$sqlOperator.'(hra)*9)/'.$sqlOperator.'(ip)=0,0,('.$sqlOperator.'(hra)*9)/'.$sqlOperator.'(ip)) as hr9,if(('.$sqlOperator.'(ip)+('.$sqlOperator.'(ipf)/3))=0,0,('.$sqlOperator.'(ha)+'.$sqlOperator.'(bb))/('.$sqlOperator.'(ip)+('.$sqlOperator.'(ipf)/3))) as whip,if('.$sqlOperator.'(ab)=0,0,'.$sqlOperator.'(ha)/'.$sqlOperator.'(ab)) as oavg,if(('.$sqlOperator.'(ab)-'.$sqlOperator.'(k)-'.$sqlOperator.'(hra)+'.$sqlOperator.'(sf))=0,0,('.$sqlOperator.'(ha)-'.$sqlOperator.'(hra))/('.$sqlOperator.'(ab)-'.$sqlOperator.'(k)-'.$sqlOperator.'(hra)+'.$sqlOperator.'(sf))) as babip';
				break;
			case QUERY_STANDARD:
			default:
				$sql .= ''.$sqlOperator.'(w) as w,'.$sqlOperator.'(l) as l,'.$sqlOperator.'(s) as s,'.$sqlOperator.'(cg) as cg,'.$sqlOperator.'(sho) as sho,if(('.$sqlOperator.'(ip)+('.$sqlOperator.'(ipf)/3))=0,0,9*'.$sqlOperator.'(er)/('.$sqlOperator.'(ip)+('.$sqlOperator.'(ipf)/3))) as era,'.$sqlOperator.'(g) as pg,'.$sqlOperator.'(gs) as gs,('.$sqlOperator.'(ip)+('.$sqlOperator.'(ipf)/3)) as ip,'.$sqlOperator.'(ha) as ha,'.$sqlOperator.'(r) as pr,'.$sqlOperator.'(er) as er,'.$sqlOperator.'(hra) as hra,'.$sqlOperator.'(bb) as pbb,'.$sqlOperator.'(k) as pk, if (('.$sqlOperator.'(k)*9)/'.$sqlOperator.'(ip)=0,0,('.$sqlOperator.'(k)*9)/'.$sqlOperator.'(ip)) as k9, if (('.$sqlOperator.'(bb)*9)/'.$sqlOperator.'(ip)=0,0,('.$sqlOperator.'(bb)*9)/'.$sqlOperator.'(ip)) as bb9, if (('.$sqlOperator.'(hra)*9)/'.$sqlOperator.'(ip)=0,0,('.$sqlOperator.'(hra)*9)/'.$sqlOperator.'(ip)) as hr9,if(('.$sqlOperator.'(ip)+('.$sqlOperator.'(ipf)/3))=0,0,('.$sqlOperator.'(ha)+'.$sqlOperator.'(bb))/('.$sqlOperator.'(ip)+('.$sqlOperator.'(ipf)/3))) as whip, '.$sqlOperator.'(vorp) as vorp';
				break;
		} // END switch
		$rulesType = 'pitching';
	}
	if (sizeof($rules) > 0) {
		$ptsSQL = ',';
		foreach($rules[$rulesType] as $cat => $val) {
			if ($ptsSQL != ',') { $ptsSQL .= '+'; }
			$ptsSQL .= '('.$sqlOperator.'('.strtolower(get_ll_cat($cat, true)).') * '.$val.')';
		}
		$ptsSQL .= ' as fpts ';
		$sql .= $ptsSQL;
	}
	return $sql;
}
/**
 *	PLAYERS STAT COLUMN BUILDER
 *
 *	Builds the standard column lists for stat table display sitewide.
 *
 * 	@author	Jeff Fox
 *	@since	1.0
 */ 
function player_stat_column_headers($player_type = 1, $query_type = QUERY_STANDARD, $showFpts = true, 
									$statsOnly = false, $showTrans = false, $showDraft = false, $showGenInfo = false) {
	$colnames = "";
	if (!$statsOnly) {
		if ($showTrans === true) {
			$colnames .= "Add|";
		}
		if ($showDraft === true) {
			$colnames .= "Draft|";
		}
		$colnames .= "Player|Team|POS|";
		if ($showGenInfo === true) {
			$colnames .= "Age|Throw|Bat|";
		}
	}
	if ($player_type == 1) {
		// BATTERS
		switch ($query_type) {
			case QUERY_BASIC:
				$colnames .= "AVG|HR|RBI|BB|K|SB|OPS";
				break;
			case QUERY_EXTENDED:
				$colnames .= "G|AB|R|H|2B|3B|HR|RBI|BB|K|SB|CS|AVG|OBP|SLG|OPS|WOBA|XBH|K%|BB%";
				break;
			case QUERY_STANDARD:
			default:
				$colnames .= "G|AB|R|H|2B|3B|HR|RBI|BB|K|SB|CS|AVG|OBP|SLG|OPS|K%|BB%";
				break;
		} // END switch
	} else {
		switch ($query_type) {
			case QUERY_BASIC:
				$colnames .= "W|L|ERA|IP|BB|K|SV|WHIP";
				break;
			case QUERY_EXTENDED:
				$colnames .= "W|L|SV|ERA|G|GS|IP|CG|SHO|HA|R|ER|HR|BB|K|K/9|BB/9|HR/9|WHIP";
				break;
			case QUERY_STANDARD:
			default:
				$colnames .= "W|L|SV|ERA|G|GS|IP|CG|SHO|HA|R|ER|HR|BB|K|K/9|BB/9|HR/9|WHIP";
				break;
		} // END switch
	}
	if ($showFpts) {
		$colnames .= "|FPTS";
	}
	return $colnames;
}
/**
 *	PLAYERS STAT FIELDS LIST
 *
 *	Builds the standard column lists for stat retireval sitewide.
 *
 * 	@author	Jeff Fox
 *	@since	1.0
 */ 
function player_stat_fields_list($player_type = 1, $query_type = QUERY_STANDARD, $showFpts = true, $statsOnly = false, 
								 $showTrans = false, $showDraft = false, $showGenInfo = false) {
	
	$defaultFields = array('player_name','teamname','pos','positions');
	$genInfoFields = array('age','throws','bats');
	$fieldList = array();
	if ($player_type == 1) {
		// BATTERS
		switch ($query_type) {
			case QUERY_BASIC:
				$fieldList = array('avg','hr','rbi','bb','k','sb','ops');
				break;
			case QUERY_EXTENDED:
				$fieldList = array('g','ab','r','h','d','t','hr','rbi','bb','k','sb','cs','avg','obp','slg','ops','wOBA','xbh','wiff','walk');
				break;
			case QUERY_STANDARD:
			default:
				$fieldList = array('g','ab','r','h','d','t','hr','rbi','bb','k','sb','cs','avg','obp','slg','ops','wiff','walk');
				break;
		} // END switch
	} else {
		switch ($query_type) {
			case QUERY_BASIC:
				$fieldList = array('w','l','era','ip','pbb','pk','s','whip');
				break;
			case QUERY_EXTENDED:
				$fieldList = array('w','l','s','era','pg','gs','ip','cg','sho','ha','pr','er','hra','pbb','pk','k9','bb9','hr9','whip');;
				break;
			case QUERY_STANDARD:
			default:
				$fieldList = array('w','l','s','era','pg','gs','ip','cg','sho','ha','pr','er','hra','pbb','pk','k9','bb9','hr9','whip');;
				break;
		} // END switch
	}
	$fields = array();
	if ($showTrans) {
		array_push($fields, 'add');
	}
	if ($showDraft) {
		array_push($fields, 'draft');
	}
	if (!$statsOnly) {
		foreach($defaultFields as $field) {
			array_push($fields,$field);
		}
	}
	if ($showGenInfo) {
		foreach($genInfoFields as $field) {
			array_push($fields,$field);
		}
	}
	foreach($fieldList as $field) {
		array_push($fields,$field);
	}
	if ($showFpts) {
		array_push($fields, 'fpts');
	}
	return $fields;
}
/**
 *	FORMAT STATS FOR DISPLAY
 *
 *	Based on the stat selected, handles converting the raw data output to display ready HTML.
 *
 * 	@author	Frank Holmes
 * 	@author	Jeff Fox
 *	@since	1.0
 */ 
function formatStatsForDisplay($player_stats = array(), $fields = array(), $config = array(), $league_id = -1, $player_teams = array(), $team_list = array(), $statsOnly = false, $showTrans = false, $showDraft = false,
								$pick_team_id = false,  $user_team_id = false, $draftStatus = false, $accessLevel = false, $isCommish = false, $draftDate = EMPTY_DATE_TIME_STR) {
	$count = 10;
	$newStats = array();
	foreach($player_stats as $row) {
		$newRow = array();
		foreach ($fields as $col) { 
			if (isset($row[$col]) && !empty($row[$col])) {
				$newRow['id'] = $id = $row['id'];
				switch ($col) {
					case 'add':
						if ($showTrans === true) {
							$newRow[$col] = '<a href="#" rel="itemPick" id="'.$row['id'].'"><img src="'.$config['fantasy_web_root'].'images/icons/add.png" width="16" height="16" alt="Add" title="Add" /></td>';
						}
						break;
					case 'draft':
						if ($showDraft === true) {
							if (($pick_team_id == $user_team_id && ($draftStatus >= 2 && $draftStatus < 4)) || (($accessLevel == ACCESS_ADMINISTRATE || $isCommish) && ($draftDate != EMPTY_DATE_TIME_STR && time() > strtotime($draftDate)))) {
								$newRow[$col] = '<a href="#" rel="draft" id="'.$row['id'].'"><img src="'.$config['fantasy_web_root'].'images/icons/next.png" width="16" height="16" alt="Draft Player" title="Draft Player" /></a>';
							} else { 
								$newRow[$col] = '- -';
							}
						}
						break;
					case 'player_name':
						
						if ($statsOnly === false) {
							$link = '/players/info/';
							if (isset($league_id) && !empty($league_id) && $league_id != -1) {
								$link .= 'player_id/'.$id.'/league_id/'.$league_id;
							} else {
								$link .= $id;
							}
							$val = anchor($link,$row['first_name']." ".$row['last_name'],array('target'=>'_blank')).' <span style="font-size:smaller;">'.makeElidgibilityString($row['positions']).'</span>';
							
							// INJURY STATUS
							$injStatus = "";
							if ($row['injury_is_injured'] == 1) {
								$injStatus = makeInjuryStatusString($row);
							}
							if (!empty($injStatus)){ 
								$val .= '&nbsp;<img src="'.$config['fantasy_web_root'].'images/icons/red_cross.gif" width="7" height="7" align="absmiddle" alt="'.$injStatus.'" title="'.$injStatus.'" />&nbsp; ';
							}
							if (isset($row['on_waivers']) && $row['on_waivers'] == 1) {
								$val .= '&nbsp;<b style="color:#ff6600;">W</b>&nbsp; ';
							}
							$newRow[$col] = $val;
						} else {
							$newRow[$col] = $row[$col];
							if (isset($row['on_waivers']) && $row['on_waivers'] == 1) {
								$newRow['on_waivers'] = 1;
							}
							if (isset($row['injury_dl_left']) && $row['injury_dl_left'] > 0) {
								$newRow['injury_dl_left'] = $row['injury_dl_left'];
							}
							if (isset($row['injury_left']) && $row['injury_left'] > 0) {
								$newRow['injury_left'] = $row['injury_left'];
							}
							if (isset($row['injury_id'])) {
								$newRow['injury_id'] = $row['injury_id'];
							}
							if (isset($row['injury_is_injured'])) {
								$newRow['injury_is_injured'] = $row['injury_is_injured'];
							}
							if (isset($row['injury_career_ending'])) {
								$newRow['injury_career_ending'] = $row['injury_career_ending'];
							}
							if (isset($row['injury_dtd_injury'])) {
								$newRow['injury_dtd_injury'] = $row['injury_dtd_injury'];
							}
						}
						break;
					case 'teamname':
						if ($statsOnly === false) {
							if ($league_id != -1) {
									if (isset($player_teams[$id])) {
									$team_obj = $team_list[$player_teams[$id]];
									$val = anchor('/team/info/'.$player_teams[$id],$team_obj['teamname']." ".$team_obj['teamnick'])."</td>";
								} else {
									$val = "Free Agent"; 
								}
							} else {
								$val = '';
							}
							$newRow[$col] = $val;
						}
						break;
					case 'bats': 
					case 'throws': 
						$newRow[$col] = get_hand($row[$col]);
						break;
					case 'pos': 
					case 'positions': 
						if (strpos($row[$col],":")) {
							$newRow[$col] = makeElidgibilityString($row[$col]);
						} else {
							$newRow[$col] = get_pos($row[$col]);
						}
						break;
					case 'position': 
					case 'role': 
						$newRow[$col] = get_pos($row[$col]);
						break;
					case 'level_id': 
						$newRow[$col] = get_level($row[$col]);
						break;
					case 'avg': 
					case 'obp': 
					case 'slg': 
					case 'ops': 
					case 'wOBA': 
					case 'oavg': 
					case 'babip': 
						$val=sprintf("%.3f",$row[$col]);
						if ($val<1) {$val=strstr($val,".");}
						$newRow[$col] = $val;
						break;
					case 'era': 
					case 'whip':
					case 'k9':
					case 'bb9':
					case 'hr9':
						$val=sprintf("%.2f",$row[$col]);
						if (($val<1)&&($col=='whip')) {$val=strstr($val,".");}
						$newRow[$col] = $val;
						break;
					case 'ip':
					case 'vorp':
						$val=sprintf("%.1f",$row[$col]);
						$newRow[$col] = $val;
						break;
					case 'walk':
					case 'wiff':
						$newRow[$col] = intval($row[$col])."%";
						break;
					default: 
						$newRow[$col] = intval($row[$col]);
						break;
				} // END switch
				
				// DEBUGGING
				if ($count < 5) {
					if (isset($newRow[$col])) {
						echo($col." = ".$newRow[$col]."<br />");
					}
				}
				
			} else {
				$newRow[$col] = 0;
			}
		} // END foreach
		array_push($newStats, $newRow);
		$count++;
	} // END foreach
	return $newStats;
}
/**
 *	MAKE INJURY STATUS STRING
 *
 *	Converts standard OOTP injury data (found in the player profile data object and injuries 
										in the database) into a human readbale string.
 *
 * 	@author	Jeff Fox
 *	@since	1.0.2
 */ 
function makeInjuryStatusString($row) {
	$injStatus = '';
	if (isset($row['injury_dtd_injury']) && $row['injury_dtd_injury'] == 1) {
		$injStatus .= "Questionable - ";
	} else if (isset($row['injury_career_ending']) && $row['injury_career_ending'] == 1) {
		$injStatus .= "Career Ending Injury! ";
	} else {
		$injStatus .= "Injured - ";
	}
	// GET injury name
	$injury_name = "Unknown Injury";
	if (isset($row['injury_id'])) {
		$injury_name = getInjuryName($row['injury_id']);
	}
	$injStatus .= $injury_name;
	if ((isset($row['injury_dl_left']) && $row['injury_dl_left'] > 0)) {
		$injStatus .= ", on DL - ".$row['injury_dl_left']." Days Left";
	}
	if (isset($row['injury_left']) && ($row['injury_left'] > 0 || (isset($row['injury_dl_left']) && $row['injury_left'] > $row['injury_dl_left']))) {
		$injStatus .= ", ".$row['injury_left']." Total Days Left";
	}
	return $injStatus;
}
/**
 *	MAKE ELIDGIBILUITY STRING
 *
 *	Converts an array of positions into a readable list of position acroymns.
 *
 * 	@author	Jeff Fox
 *	@since	1.0
 */ 
function makeElidgibilityString($positions) {
	$gmPos = "";
	if (strpos($positions,":")) {
		$pos = unserialize($positions);
		foreach($pos as $tmpPos) {
			if ($tmpPos != 25) {
				if (!empty($gmPos)) $gmPos .= ",";
				$gmPos .= get_pos($tmpPos);
			}
		}
	}
	return $gmPos;	
}
function calc_rating($rating,$ratOrTal=0,$max="")
 {
   if ($rating==0) {return 0;}

   if ((file_exists("./settings/lgSettings.txt"))&&(($_SESSION['ratings']=="")||($_SESSION['talents']=="")||($_SESSION['others']==""))) {
      $f = fopen("./settings/lgSettings.txt",'r');
      if ($f)
       {
         while (!feof($f))
          {
            $text=fgets($f);
            $split=explode("|",$text);
            switch ($split[0])
             {
	        case 'RATINGS'  : $e=explode("\n",$split[1]);$ratings=$e[0];     break;
	        case 'TALENTS'  : $e=explode("\n",$split[1]);$talents=$e[0];     break;
	        case 'OTHERS'   : $e=explode("\n",$split[1]);$others=$e[0];      break;	    
	     }
          }
	 fclose($f);
	 $_SESSION['ratings']=$ratings;
	 $_SESSION['talents']=$talents;
	 $_SESSION['others']=$others;
       }
    }	   
   $scale=$_SESSION['ratings'];
   if ($ratOrTal==1) {$scale=$_SESSION['talents'];}
   if ($ratOrTal==2) {$scale=$_SESSION['others'];}
   if ($scale=="") {$scale='Hidden';}

   if ($max!="") {$rating=max($rating,$max);}

   switch ($scale)
    {
      case "2-8":
	$rat=intval(($rating/31) + 2);
	$rat=min(8,$rat);
	$rat=max(2,$rat);
        break;
      case "20-80":
        $rat=intval(($rating+10)/15);
	$rat=min(13,$rat);
	$rat=max(1,$rat);
	$rat=5*$rat+15;
	break;
      case "Hidden": $rat=0; break;
      default:
        switch ($scale)
	 {
	   case "1-5":   $maxRat=5;   break; 
           case "1-10":  $maxRat=10;  break;
           case "1-20":  $maxRat=20;  break;
	   case "1-100"; $maxRat=100; break;
	 }
	$sc=200/$maxRat;
	$rat=intval(($rating+$sc)/$sc);
	$rat=min($maxRat,$rat);
	$rat=max(1,$rat);
    }

   return $rat;    
 }

function formatBytes($bytes, $precision = 1) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
   
    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 
   
    $bytes /= pow(1024, $pow); 
   
    return round($bytes, $precision) . ' ' . $units[$pow]; 
} 

function calc_movement($mvmnt,$gb)
 {
   $rat=200.5-(5*((18+(54-$gb)*.6)+((200-$mvmnt)/6))/2);
   return $rat;
 }

function get_pitch_rating($pitch,$ir,$gb,$mvmnt,$velo)
{
   $velo=$velo*10;
   switch ($pitch)
    {
      case 'fastball':
        $rat=($velo*0.6) + ($ir*0.4);
        break;
      case 'slider':
        $rat=($velo*0.3) + ($ir*0.7);
	break;
      case 'forkball':
      case 'splitter':
      case 'cutter':
        $rat=($velo*0.4) + ($ir*0.4) + ($mvmnt*0.2);
        break;
      case 'sinker':
        $rat=($velo*0.3) + ($ir*0.3) + ($mvmnt*0.4);
        break;
      case 'changeup':
      case 'knuckleball':
      case 'circlechange':
        $rat=$ir;
        break;
      case 'curveball':
      case 'screwball':
      case 'knucklecurve':
        $rat=($velo*0.2) + ($ir*0.8);
        break;
    }
   return $rat;
 }

function get_level($lvl)
 {
   switch ($lvl)
    {
      case 1: $txt="ML"; break;
      case 2: $txt="AAA"; break;
      case 3: $txt="AA"; break;
      case 4: $txt="A"; break;
      case 5: $txt="SS"; break;
      case 6: $txt="R"; break;
      case 7: $txt="INT"; break;
      case 8: $txt="WL"; break;
      case 9: $txt="COL"; break;
      case 10: $txt="HS"; break;
      default: $txt=$lvl; break;
      
    }
   return $txt;
 }

function get_award($awid)
 {
    switch ($awid)
     {
       case 0:
         $txt="Player of the Week";
	 break;
       case 1:
         $txt="Pitcher of the Month";
         break;
       case 2:
         $txt="Batter of the Month";
         break;
       case 3:
         $txt="Rookie of the Month";
         break;
       case 4:
         $txt="Oustanding Pitcher";
         break;
       case 5:
         $txt="Oustanding Hitter";
         break;
       case 6:
         $txt="Oustanding Rookie";
         break;
       case 7:
         $txt="Gold Glove";
         break;
       case 8:
         $txt=$awid;
         break;
       case 9:
         $txt="All-Star";
         break;
       default:
         $txt=$awid." not found";
	 break;
     }
    return $txt;
 }
 
function get_pos($pos)
 {
   switch ($pos)
    {
      case -1:
	  	$txt="All";
	break;
	  case 0:
        $txt="PH";
	break;
      case 1:
        $txt="P";
	break;
      case 2:
        $txt="C";
	break;
      case 3:
        $txt="1B";
	break;
      case 4:
        $txt="2B";
	break;
      case 5:
        $txt="3B";
	break;
      case 6:
        $txt="SS";
	break;
      case 7:
        $txt="LF";
	break;
      case 8:
        $txt="CF";
	break;
      case 9:
        $txt="RF";
	break;
      case 10:
        $txt="DH";
	break;
      case 11:
        $txt="SP";
	break;
      case 12:
        $txt="MR";
	break;
      case 13:
        $txt="CL";
	break;
	  case 20:
	     $txt="OF";
	break;
	  case 21:
	     $txt="RP";
	break;
	  case 22:
	     $txt="IF";
	break;
	  case 23:
	     $txt="MI";
	break;
	case 24:
	     $txt="CI";
	break;
	case 25:
	     $txt="U";
	break;
      default:
        $txt="-";
	break;
    }
   return $txt;
 }
/**
 * 	GET POSITIONS FOR ROSTERS
 *
 * 	This function returns an array of positons that are available for use
 * 	in setting up rosters rules.
 *
 *	@return					Array of stat indexes and positions
 *
 *	@since	1.0.3
 *	@see /application/controllers/admin:configRosters
 * 	@author	Jeff Fox
 */
function get_pos_for_rosters() {
	return array(2=>"C",3=>"1B",4=>"2B",5=>"3B",6=>"SS",10=>"DH",11=>"SP",12=>"MR",20=>"OF",
	//22=>"IF",23=>"MI",24=>"CI",
	25=>"U");
}
function get_pos_num($pos)
 {
   switch ($pos)
    {
	  case "PH":
        $txt=0;
	break;
      case "P":
        $txt=1;
	break;
      case "C":
        $txt=2;
	break;
      case "1B":
        $txt=3;
	break;
      case "2B":
        $txt=4;
	break;
      case "3B":
        $txt=5;
	break;
      case "SS":
        $txt=6;
	break;
      case "LF":
        $txt=7;
	break;
      case "CF":
        $txt=8;
	break;
      case "RF":
        $txt=9;
	break;
      case "DH":
        $txt=10;
	break;
      case "SP":
        $txt=11;
	break;
      case "MR":
        $txt=12;
	break;
      case "CL":
        $txt=13;
	break;
	  case "OF":
	     $txt=20;
	break;
	  case "RP":
	     $txt=21;
	break;
	  case "IF":
	     $txt=22;
	break;
	  case "MI":
	     $txt=23;
	break;
	case "CI":
	     $txt=24;
	break;
	case "U":
	     $txt=25;
	break;
      default:
	case "All":
	  	$txt=-1;
		break;
    }
   return $txt;
 }
/**
 * 	GET STATS FOR SCORING EDITOR
 *	This function returns an array of stats by OOTP stat index and Label. 
 *
 *	@param	$type			1 = Batters, 2 = Pitchers
 *	@param	$scoring_type	1 = Head2Head, 2 = Basic Roto, 3 = Roto 5X5, 4 = Super Roto
 *	@return					Array of stat indexes and labels
 *
 *	@since	1.0.3
 *	@see 	/application/controllers/admin:configScoring
 *	@todo	Specify what stats fall under the associated Roto categories
 * 	@author	Jeff Fox
 */
function get_stats_for_scoring($type=1,$scoring_type = 1) {
	$stats = array();
	switch ($type) {
		case 1:
			$stats = array(
			2=>"AB",
			1=>"PA",
			3=>"1B",
			6=>"2B",
			7=>"3B",
			8=>"HR",
			4=>"K",
			5=>"TB",
			9=>"SB",
			10=>"RBI",
			11=>"R",
			12=>"BB",
			13=>"IBB",
			14=>"HBP",
			15=>"SH",
			16=>"SF",
			17=>"EBH",
			21=>"RC",
			22=>"RC/27",
			58=>"CS"
			//,0=>"GS",
			//18=>"AVG",
			//19=>"OBP",
			//20=>"SLG",
			//23=>"ISO",
			//24=>"TAVG",
			//25=>"OPS",
			//26=>"VORP"
			);
			break;
		case 2:
			// PITCHING STATS
			$stats = array(
			27=>"G",
			29=>"W",
			30=>"L",
			32=>"SV",
			33=>"HLD",
			34=>"IP",
			35=>"BF",
			36=>"HRA",
			37=>"BB",
			38=>"K",
			39=>"WP",
			50=>"RA",
			51=>"GF",
			52=>"QS",
			54=>"CG",
			56=>"SHO",
			59=>"HA",
			60=>"ER",
			61=>"BS"
			//,31=>"Win%",
			//28=>"GS",
			//40=>"ERA",
			//41=>"BABIP",
			//42=>"WHIP",
			//43=>"K/BB",
			//44=>"RA/9IP",
			//45=>"HR/9IP",
			//46=>"H/9IP",
			//47=>"BB/9IP",
			//48=>"K/9IP",
			//49=>"VORP",
			//53=>"QS%",
			//55=>"CG%",
			//57=>"GB%"
			);
			break;
	}
	return $stats;
}
 
function get_velo($velo)
 {
  switch ($velo)
   {
     case 1:  $txt="<75 Mph";    break;
     case 2:  $txt="81-83 Mph";  break;
     case 3:  $txt="82-84 Mph";  break;
     case 4:  $txt="83-85 Mph";  break;
     case 5:  $txt="84-86 Mph";  break;
     case 6:  $txt="85-87 Mph";  break;
     case 7:  $txt="86-88 Mph";  break;
     case 8:  $txt="87-89 Mph";  break;
     case 9:  $txt="89-90 Mph";  break;
     case 10: $txt="90-92 Mph";  break;
     case 11: $txt="91-93 Mph";  break;
     case 12: $txt="92-94 Mph";  break;
     case 13: $txt="93-95 Mph";  break;
     case 14: $txt="94-96 Mph";  break;
     case 15: $txt="95-97 Mph";  break;
     case 16: $txt="96-98 Mph";  break;
     case 17: $txt="97-99 Mph";  break;
     case 18: $txt="98-100 Mph"; break;
     case 19: $txt="99-101 Mph"; break;
     case 20: $txt="101+ Mph";   break;
   }
  return $txt;
 }

function hof_pos($pos)
 {
   switch ($pos)
    {
      case 2: $val=20;break;
      case 3: $val=1;break;
      case 4: $val=14;break;
      case 5: $val=13;break;
      case 6: $val=16;break;
      case 7: $val=3;break;
      case 8: $val=12;break;
      case 9: $val=6;break;
      default: $val=0;break;      
    }
   return $val;
 }
 
function ss_pos($pos)
 {
   switch ($pos)
    {
      case 2: $val=20;break;
      case 3: $val=1;break;
      case 4: $val=11;break;
      case 5: $val=7;break;
      case 6: $val=14;break;
      case 7: $val=3;break;
      case 8: $val=5;break;
      case 9: $val=4;break;
      default: $val=0;break;
    }
   return $val;
 }

function datediff($start,$end)
 {
   $start_ts = strtotime($start);
   $end_ts = strtotime($end);

   $diff = $end_ts - $start_ts;
   return round($diff / 86400);
 }

function get_ll_cat($catID,$forSQL = false)
 {
   switch ($catID)
    {
      ## Batter Stats
      case 0: $txt="GS"; break;
      case 1: $txt="PA"; break;
      case 2: $txt="AB"; break;
      case 3: $txt="H"; break;
      case 4: $txt="K"; break;
      case 5: $txt="TB"; break;
      case 6: if($forSQL) $txt="d"; else $txt="2B"; break;
      case 7: if($forSQL) $txt="t"; else $txt="3B"; break;
      case 8: $txt="HR"; break;
      case 9: $txt="SB"; break;
      case 10: $txt="RBI"; break;
      case 11: $txt="R"; break;
      case 12: $txt="BB"; break;
      case 13: $txt="IBB"; break;
      case 14: if($forSQL) $txt="hp"; else $txt="HBP"; break;
      case 15: $txt="SH"; break;
      case 16: $txt="SF"; break;
      case 17: $txt="EBH"; break;
      case 18: $txt="AVG"; break;
      case 19: $txt="OBP"; break;
      case 20: $txt="SLG"; break;
      case 21: $txt="RC"; break;
      case 22: $txt="RC/27"; break;
      case 23: $txt="ISO"; break;
      case 24: $txt="TAVG"; break;
      case 25: $txt="OPS"; break;
      case 26: $txt="VORP"; break;

      ## Pitcher Stats
      case 27: $txt="G"; break;
      case 28: $txt="GS"; break;
      case 29: $txt="W"; break;
      case 30: $txt="L"; break;
      case 31: $txt="Win%"; break;
      case 32: if($forSQL) $txt="s"; else $txt="SV"; break;
      case 33: if($forSQL) $txt="h"; else $txt="HLD"; break;
      case 34: $txt="IP"; break;
      case 35: $txt="BF"; break;
      case 36: $txt="HRA"; break;
      case 37: $txt="BB"; break;
      case 38: $txt="K"; break;
      case 39: $txt="WP"; break;
      case 40: $txt="ERA"; break;
      case 41: $txt="BABIP"; break;
      case 42: $txt="WHIP"; break;
      case 43: $txt="K/BB"; break;
      case 44: $txt="RA/9IP"; break;
      case 45: $txt="HR/9IP"; break;
      case 46: $txt="H/9IP"; break;
      case 47: $txt="BB/9IP"; break;
      case 48: $txt="K/9IP"; break;
      case 49: $txt="VORP"; break;
      case 50: $txt="RA"; break;
      case 51: $txt="GF"; break;
      case 52: $txt="QS"; break;
      case 53: $txt="QS%"; break;
      case 54: $txt="CG"; break;
      case 55: $txt="CG%"; break;
      case 56: $txt="SHO"; break;
      case 57: $txt="GB%"; break;

	  case 58: $txt="CS"; break;
	  
	  case 59: $txt="HA"; break;
	  case 60: $txt="ER"; break;
	  case 61: $txt="BS"; break;
      default: $txt=$catID; break;
    }	    
   return $txt;
 }

function ordinal_suffix($value, $sup = 0) {
    if(substr($value, -2, 2) == 11 || substr($value, -2, 2) == 12 || substr($value, -2, 2) == 13){
        $suffix = "th";
    }
    else if (substr($value, -1, 1) == 1){
        $suffix = "st";
    }
    else if (substr($value, -1, 1) == 2){
        $suffix = "nd";
    }
    else if (substr($value, -1, 1) == 3){
        $suffix = "rd";
    }
    else {
        $suffix = "th";
    }
    if($sup){
        $suffix = "<sup>" . $suffix . "</sup>";
    }
    return $value . $suffix;
}

function get_hand($handID)
 {
  switch ($handID)
   {
     case 3: $hand="S"; break;
     case 2: $hand="L"; break;
     case 1: $hand="R"; break;
     default: $hand="U"; break;
   }
  return $hand;
 }

function cm_to_ft_in($len)
 {
   $in=$len/2.54;
   $ft=floor($in/12);
   $in=$in%12;
   $in=round($in);
   $txt=$ft."' ".$in."\"";
   return $txt;
 }
 function average($array)
 {
   $sum   = array_sum($array);
   $count = count($array);
   return $sum/$count;
 }

function deviation($array) 
 {
   $avg = average($array);
   foreach ($array as $value)
    {
      $variance[] = pow($value-$avg, 2);
     }
   $deviation = sqrt(average($variance));
   return $deviation;
 }

?>
