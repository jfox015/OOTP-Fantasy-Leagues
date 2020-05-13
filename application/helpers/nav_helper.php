<?php
function top_nav($logged_in = false, $show_admin = false, $userTeams = array()) {
	$nav = array(array('url'=>'/home','label'=>'Home'));
	/*$leagueSubMenu = '<div class="drop">
						<span class="t">&nbsp;</span>
						<ul>';
	$leagueSubMenu .= '<li>'.anchor('/search/leagues','Search Leagues').'</li>';
	$leagueSubMenu .= '<li>'.anchor('/league/joinleague','Join a League').'</li>';
	$leagueSubMenu .= '</ul>
					<span class="b">&nbsp;</span>
				</div>';
	array_push($nav,array('url'=>'','label'=>'<span class="opener">Leagues</span>','menu'=>$leagueSubMenu));*/
	array_push($nav,array('url'=>'/league/leagueList','label'=>'Leagues'));
	array_push($nav,array('url'=>'/news/articles','label'=>'News'));
	$ctrlr = 'user';
	if ($logged_in) {
		if ($show_admin) {
			$ctrlr = 'admin';
			array_push($nav,array('url'=>'/admin','label'=>'Admin Dashboard'));
		}
		if (sizeof($userTeams) > 0) {
			$userSubMenu = '<div class="drop">
								<span class="t">&nbsp;</span>
								<ul>';
			$userSubMenu .= '<li>'.anchor('/user/profile','<img src="'.PATH_IMAGES.'icons/user.png" width="16" align="absmiddle" height="16" border="0" /> My Profile').'</li>';
			$userSubMenu .= '<li class="label first">My Teams</li>';
			foreach($userTeams as $teamdata) {
				$userSubMenu .= '<li>'.anchor('/team/info/'.$teamdata['id'],'<img src="'.PATH_TEAMS_AVATARS.$teamdata['avatar'].'"width="16" align="absmiddle" height="16" border="0" />'.$teamdata['teamname']." ".$teamdata['teamnick']).'</li>';
			}
			$userSubMenu .= '</ul>
							<span class="b">&nbsp;</span>
						</div>';
			array_push($nav,array('url'=>'/user/profile','label'=>'<span class="opener">My Fantasy</span>','menu'=>$userSubMenu));
			
		} else {
			array_push($nav,array('url'=>'/user/profile','label'=>'My Profile'));
		}
		array_push($nav,array('url'=>'/user/logout','label'=>'Logout'));
	} else {
		array_push($nav,array('url'=>'/user/login','label'=>'Login'));
		array_push($nav,array('url'=>'/user/register','label'=>'Register'));
	}
	array_push($nav,array('url'=>'/about','label'=>'Help/About'));
	return $nav;
}
function about_nav($bug_link = "") {
	$nav = array(array('url'=>'/about','label'=>'About This Site'));
	array_push($nav,array('url'=>'/about/about_mod','label'=>'About OOTP Fantasy Leagues'));
	array_push($nav,array('url'=>'/about/contact','label'=>'Contact'));
	if ($bug_link == "") {
		$bug_url = BUG_URL;
	} else {
		$bug_url = '/about/bug_report';
	}
	array_push($nav,array('url'=>$bug_url,'label'=>'Report a Bug', 'target'=>'_new'));
	return $nav;
}

function news_nav($loggedIn = false, $accessLevel = false) {
	$nav = array(array('url'=>'/news/articles','label'=>'News Home'));
	if ($loggedIn && $accessLevel >= ACCESS_WRITE)
		array_push($nav,array('url'=>'/news/submit/mode/add/','label'=>'Add News'));
	return $nav;
}

function league_nav($league_id = false, $league_name = false, $show_admin = false, $show_draft = false, $scoring_type = LEAGUE_SCORING_ROTO,$private = false) {
    if ($league_id === false || $league_id == -1) { return; }
	if ($private) {
		$nav = array(array('url'=>''.$league_id,'label'=>$league_name." (Private)"));
	} else {
		$nav = array(array('url'=>'/league/home/id/'.$league_id,'label'=>$league_name));
		if ($show_admin) {
			array_push($nav,array('url'=>'/league/admin/id/'.$league_id,'label'=>'Admin'));
		}
		array_push($nav,array('url'=>'/league/rules/id/'.$league_id,'label'=>'Rules'));
		array_push($nav,array('url'=>'/league/standings/id/'.$league_id,'label'=>'Standings'));
		if ($scoring_type == LEAGUE_SCORING_HEADTOHEAD) {
			array_push($nav,array('url'=>'/league/results/id/'.$league_id,'label'=>'Results'));
		}
		array_push($nav,array('url'=>'/players/stats/league_id/'.$league_id,'label'=>'Players'));
		array_push($nav,array('url'=>'/league/info/'.$league_id,'label'=>'Teams & Owners'));
		//if ($show_draft) {
		array_push($nav,array('url'=>'/draft/load/league_id/'.$league_id,'label'=>'Draft'));
		//}
		//array_push($nav,array('url'=>'/league/messages/id/'.$league_id,'label'=>'League Message Board'));
		if ($scoring_type == LEAGUE_SCORING_HEADTOHEAD) {
			array_push($nav,array('url'=>'/league/schedule/id/'.$league_id,'label'=>'Schedule'));
		}
		array_push($nav,array('url'=>'/league/transactions/id/'.$league_id,'label'=>'Transactions'));
	}
	return $nav;
}
 
function team_nav($team_id = false, $teamName = false, $show_admin = false, $showTrades = false) {
    
	if ($team_id === false || $team_id == -1) { return; }
	$nav = array(array('url'=>'/team/info/'.$team_id,'label'=>$teamName));
	if ($show_admin) {
		array_push($nav,array('url'=>'/team/admin/id/'.$team_id,'label'=>'Admin'));
	}
	array_push($nav,array('url'=>'/team/lineup/'.$team_id,'label'=>'Lineup'));
	array_push($nav,array('url'=>'/team/eligibility/'.$team_id,'label'=>'Eligibility'));
	array_push($nav,array('url'=>'/team/stats/'.$team_id,'label'=>'Stats'));
	if ($show_admin) {
		array_push($nav,array('url'=>'/team/adddrop/id/'.$team_id,'label'=>'Add/Drop'));
		if ($showTrades) {
			array_push($nav,array('url'=>'/team/trade/id/'.$team_id,'label'=>'Trade'));
		}
		/*array_push($nav,array('url'=>'/team/serviceTime/id/'.$team_id,'label'=>'Service Time'));
		array_push($nav,array('url'=>'/team/warnings/id/'.$team_id,'label'=>'Warnings'));*/
	}
	array_push($nav,array('url'=>'/team/transactions/team_id/'.$team_id,'label'=>'Transactions'));
	return $nav;
}
function player_nav($league_id = false) {
    
	$nav = array();
	$url = '/players/stats/';
	if ($league_id !== false && $league_id != -1) { 
		$url = '/players/stats/league_id/'.$league_id;
	}
	array_push($nav,array('url'=>$url,'label'=>"Players"));
	array_push($nav,array('url'=>$url,'label'=>"View Stats"));
	return $nav;
}
function draft_nav($league_id = false) {
    
	$nav = array(array('url'=>'','label'=>"Draft"));
	array_push($nav,array('url'=>'/draft/selection/league_id/'.$league_id,'label'=>"Draft Prep/Selection"));
	array_push($nav,array('url'=>'/draft/load/'.$league_id,'label'=>"Draft Results"));
	return $nav;
}
function user_nav($loggedIn = false, $name = false, $userId = false) {
	$nav = array();
    if ($loggedIn) {
    	if ($name !== false) {
        	array_push($nav,array('label'=>$name));
        }
        array_push($nav,array('url'=>'/user','label'=>"Profile"));
        array_push($nav,array('url'=>'/user/account','label'=>"Account Details"));
        array_push($nav,array('url'=>'/user/change_password','label'=>"Change Password"));
	} else {
		array_push($nav,array('url'=>'/user/forgotten_password','label'=>"Forgot Password"));
	}
	if ($loggedIn) {
		array_push($nav,array('url'=>'/search/users','label'=>"Browse User Profiles"));
	}
	return $nav;
}

function bugdb_nav($show_admin = false) {
    
	$nav = array();
	if ($show_admin) {
		array_push($nav,array('url'=>'/search/projects/','label'=>'Project List'));
		array_push($nav,array('url'=>'/search/bugs/','label'=>'Bug Database'));
		array_push($nav,array('url'=>'/bug/','label'=>'Add new Bug'));
	}
	return $nav;
}

?>
