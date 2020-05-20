   	<script type="text/javascript" charset="UTF-8">
	$(document).ready(function(){	
		$('input[rel=inviteRespond]').click(function(){
			var params = this.id.split("|");
			document.location.href = '<?php echo($config['fantasy_web_root']); ?>user/inviteResponse/id/'+params[0]+'/ct/'+params[1]+'/ck/'+params[2];
		});
		$('input[rel=requestRespond]').click(function(){
			var params = this.id.split("|");
			document.location.href = '<?php echo($config['fantasy_web_root']); ?>league/requestResponse/request_id/'+params[0]+'/type/'+params[1]+'/league_id/'+params[2];
		});
	});
	</script>
    <div id="subPage">
       	<div id="content">
			<!-- BEGIN MAIN COLUMN -->
			<div id="center-column-med">
				<h1><?php 
				if (isset($profile->avatar) && !empty($profile->avatar)) { 
					$avatar = $profile->avatar;
				} else {
					$avatar = DEFAULT_AVATAR;
				} 
				?>
				<img src="<?php echo(PATH_USERS_AVATARS.$avatar) ?>" width="50" height="50" alt="avatar" title="avatar" align="absmiddle" />
				<?php if (isset($profile->firstName) && isset($profile->lastName)) {
					echo($profile->firstName." ".$profile->lastName); 
				} else {
					echo("No Name Provided"); 
				}?></h1>
				<b>Nickname:</b> <?php if (isset($profile->nickName)) { echo($profile->nickName); } ?><br />
				<br />
				<strong>Title:</strong>
				<?php echo((!empty($profile->title) ? $profile->title : "No Title provided.")); ?><br />
				<br />
				<strong>Bio:</strong>
				<?php echo((!empty($profile->bio) ? $profile->bio : "No Bio provided. ".anchor('/user/profile/edit','Add a bio').".")); ?>
				<br /><br />  
				<b>Joined:</b> <?php print(date('m/d/Y',strtotime($dateCreated))); ?><br />    
				<br />     
				<b>Last Updated:</b> <?php print(date('m/d/Y',strtotime($dateModified))); ?><br />    
			</div>

           	<!-- BEGIN RIGHT COLUMN -->
           	<div id="right-column-wide">
			<?php 
			if ($loggedIn && $currUser == $profile->userId) { 
				/*------------------------------------------------------
				/
				/	LEAGUE INVITES
				/
				/-----------------------------------------------------*/
				if (isset($invites) && sizeof($invites) > 0) { ?>
				<div class='textbox right-column'>
				<table cellpadding="0" cellspacing="0" class="dashboard">
				<tr class='title'>
					<td style="padding:3px">League Invitations</td>
				</tr>
				<tr>
					<td style="padding:12px; line-height:1.5;">
					<?php 
					foreach($invites as $invite) { 
						if (isset($invite['avatar']) && !empty($invite['avatar'])) { 
							$avatar = $invite['avatar'];
						} else {
							$avatar = DEFAULT_AVATAR;
						} // END if
						?>
						<img src="<?php echo(PATH_LEAGUES_AVATARS.$avatar) ?>" width="18" height="18" alt="avatar" title="avatar" align="absmiddle" />
						<b><?php echo(anchor('/league/info/'.$invite['league_id'],'<b>'.$invite['league_name'].'</b>')); ?></b><br />
						Invite from: <?php echo($invite['username']); ?><br />
						Sent On: <?php echo(date('m/d/Y h:m A',strtotime($invite['send_date ']))); ?><br />
						<input type='button' rel="inviteRespond" id="<?php echo($invite['id']); ?>|<?php echo(INVITE_STATUS_ACCEPTED); ?>|<?php echo($invite['ck']); ?>" class="button" value='Accept' style="float:left;margin-right:8px;" />
						<input type='button' rel="inviteRespond" id="<?php echo($invite['id']); ?>|<?php echo(INVITE_STATUS_DECLINED); ?>|<?php echo($invite['ck']); ?>" class="button" value='Decline' style="float:left;margin-right:8px;" />
					<div class="rule"></div>
					<?php
					} // END if
					?></td>
				</tr>
				</table>
				</div>
				<?php
				} // END if
				/*------------------------------------------------------
				/
				/	TEAM REQUESTS
				/
				/-----------------------------------------------------*/
				if (isset($requests) && sizeof($requests) > 0) { ?>
				<div class='textbox right-column'>
				<table cellpadding="0" cellspacing="0" class="dashboard">
				<tr class='title'>
					<td style="padding:3px">Team Requests</td>
				</tr>
				<tr>
					<td style="padding:12px; line-height:1.5;">
					<?php 
					$first = true;
					foreach($requests as $request) { 
						if (!$first) { ?>
						<br clear="all" />
						<div class="rule"></div>
						<?php
						}
						$first = false;
						if (isset($request['avatar']) && !empty($request['avatar'])) { 
							$avatar = $request['avatar'];
						} else {
							$avatar = DEFAULT_AVATAR;
						} // END if
						?>
						<img src="<?php echo(PATH_TEAMS_AVATARS.$avatar) ?>" width="18" height="18" alt="avatar" title="avatar" align="absmiddle" />
						<?php echo(anchor('/team/info/'.$request['team_id'],'<b>'.$request['team'].'</b>')); ?> 
						of the <?php echo(anchor('/league/info/'.$request['league_id'],$request['league_name'])); ?> league.<br />
						Requested On: <?php echo(date('m/d/Y h:m A',strtotime($request['date_requested']))); ?><br />
						<input type='button' rel="requestRespond" id="<?php echo($request['id']); ?>|2|<?php print($request['league_id']); ?>" class="button" value='Withdraw' style="float:left;margin-right:8px;" />
					<?php
					} // END if
					?></td>
				</tr>
				</table>
				</div>
				<?php
				} // END if
				/*------------------------------------------------------
				/
				/	USEER TOOL BOX
				/
				/-----------------------------------------------------*/
				?>
				<!-- Tool Box -->
				<div class='textbox right-column'>
				<table cellpadding="0" cellspacing="0" class="dashboard">
				<tr class='title'>
					<td style="padding:3px">My Options</td>
				</tr>
				<tr>
					<td style="padding:3px">
					<ul class="iconmenu">
					<li><?php echo anchor('/user/profile/edit','<img src="'.$config['fantasy_web_root'].'images/icons/notes_edit.png" width="48" height="48" />'); ?><br />
					Edit My Profile</li>
					<li><?php echo anchor('/user/avatar','<img src="'.$config['fantasy_web_root'].'images/icons/image_edit.png" width="48" height="48" />'); ?><br />
					Change My Avatar</li>
					<li><?php echo anchor('/league/leagueList','<img src="'.$config['fantasy_web_root'].'images/icons/search.png" width="48" height="48" />'); ?><br />
					Find a team</li><br clear="all" />
					<?php 
					if ($config['users_create_leagues'] == 1) { ?>
					<li><?php echo anchor('/user/createLeague','<img src="'.$config['fantasy_web_root'].'images/icons/window_add.png" width="48" height="48" />'); ?><br />
					Create a new League</li>
					<?php } ?>
					</ul></td>
				</tr>
				</table>
				</div>
				<?php } ?>
           	</div>
			
			<div id="single-column">
				<h3>Fantasy Teams</h3>
				<?php
				if (isset($thisItem['userTeams']) && sizeof($thisItem['userTeams']) > 0) {
				$teamList = array('rot'=>array(),'h2h'=>array());
				if (sizeof($thisItem['userTeams']) > 0) { 
					foreach($thisItem['userTeams'] as $data) {
						$type = "";
						if ($data['league_type'] == LEAGUE_SCORING_HEADTOHEAD) {
							$type = "h2h";
						} else {
							$type = "rot";
						}
						array_push($teamList[$type], $data);	
					}
				} 
				foreach($teamList as $type => $teams) {
					if (sizeof($teams) > 0) {
				?>
				<div class='textbox'>
				<table style="margin:6px" class="sortable-table" cellpadding="5" cellspacing="0" width="560px">
				<tr class='title'>
					<?php 
					$cols = 6;
					if ($loggedIn && $currUser == $profile->userId) { $cols = 7; } ?>
					<td colspan='<?php print($cols); ?>' class='lhl'><?php print((($type == 'rot') ? 'Rotisserie' : "Head to Head")." Leagues"); ?></td>
				</tr>
				<tr class='headline'>
					<td class='hsc2_l'></td>
					<td class='hsc2_l'>Team</td>
					<td class='hsc2_c'>League</td>
					<?php
					if ($type == 'rot') { ?>
					<td class='hsc2_c'>Total</td>
					<!--td class='hsc2_c'>Rank</td-->
					<?php } else { ?>
					<td class='hsc2_c'>W</td>
					<td class='hsc2_c'>L</td>
					<td class='hsc2_c'>%</td>
					<!--td class='hsc2_c'>GB</td-->
					<?php } ?>
					<?php if ($loggedIn && $currUser == $profile->userId) { ?>
					<td class='hsc2_c'>Options</td>
					<?php } ?>
				</tr>
				
				<?php 
				foreach($teams as $data) {
					$rowcount = 0;
					$leadW = 0;
					$leadG = 0;
					if (($rowcount %2) == 0) { $color = "#EAEAEA"; } else { $color = "#FFFFFF"; } 
					?>
				<tr style="background-color:<?php echo($color); ?>">
					<td class='hsc2_l'>
					<?php 
					if (isset($data['avatar']) && !empty($data['avatar'])) { 
						$avatar = $data['avatar'];
					} else {
						$avatar = DEFAULT_AVATAR;
					}
					?>
					<img src="<?php echo(PATH_TEAMS_AVATARS.$avatar); ?>" width="24" height="24" align="absmiddle" />
					</td>
					<td class='hsc2_l'><?php echo(anchor('/team/info/'.$data['id'],$data['teamname']." ".$data['teamnick'])); ?></td>
					<td class='hsc2_c'><?php echo(anchor('/league/info/'.$data['league_id'],$data['league_name'])); ?></td>
					<?php
					if ($type == 'rot') { ?>
					<td class='hsc2_c'><?php echo($data['total']); ?></td>
					<!--td class='hsc2_l'><?php ?></td-->
					<?php } else { ?>
					<td class='hsc2_c'><?php echo($data['w']); ?></td>
					<td class='hsc2_c'><?php echo($data['l']); ?></td>
					<td class='hsc2_c'><?php echo(sprintf("%.3f",$data['pct'])); ?></td>
					<!--td class='hsc2_r'><?php echo($data['gb']); ?></td-->
					<?php } ?>
					<?php if ($loggedIn && $currUser == $profile->userId) { ?>
					<td class='hsc2_c'><?php echo anchor('/league/select/id/'.$data['league_id'].'/team_id/'.$data['id'],'Select'); ?></td>
					<?php } ?>
				</tr>
				<?php
					/*----------------------------------------
					/	USER DRAFTS INFORMATION
					/---------------------------------------*/
					// GET AND DISPLAY USER DRAFTS
					if (isset($userDrafts) && isset($userDrafts[$data['league_id']])) {
					$draftInfo = $userDrafts[$data['league_id']];
					if ($draftInfo['draftStatus'] > 0 && $draftInfo['draftStatus'] < 5) {
				?>
				<tr style="background-color:#FFC">
					<td class='hsc2_l' colspan="3">
					<span style="color:#c00;font-weight:bold;">
					<?php 
						if ($draftInfo['draftStatus'] < 2) {
							if (isset($draftInfo['draftDate']) && $draftInfo['draftDate'] != EMPTY_DATE_TIME_STR) {
								$statusMess = 'Your league draft begins '.date('m/d/Y',strtotime($draftInfo['draftDate']))." at ".date('h:i A',strtotime($draftInfo['draftDate']));
							} else {
								$statusMess = 'Your league draft is coming up soon.';
							}
							$statusMess .= '<br /><br />'.anchor('/team/info/'.$draftInfo['team_id'],$draftInfo['teamname']." ".$draftInfo['teamnick']).' is the first team to pick.';
						} else if ($draftInfo['draftStatus'] >= 2 && $draftInfo['draftStatus'] < 5) {
							$statusMess = 'Your league is currently drafting.</span><br /><br /> '.anchor('/team/info/'.$draftInfo['team_id'],$draftInfo['teamname']." ".$draftInfo['teamnick']).' is the next team to pick.';
						} // END if ($draftInfo['draftStatus'] > 0
						echo($statusMess); ?>
						</td>
						<td class='hsc2_l' colspan="5"> <?php
						$outText = "Set up your draft list or review the player pool.";
						if ($draftInfo['draftStatus'] >= 2 && $draftInfo['draftStatus'] < 5) {
							if ($draftInfo['team_id'] == $data['id']) { 
								$outText = "Pick now!";
							} else {
								echo "<strong>In the meantime:</strong><br /><br />";
							}
						}
						echo anchor('/draft/selection/league_id/'.$data['league_id'].'/team_id/'.$data['id'],$outText,array('style'=>'font-weight:bold;')); 
					} // END if ($draftInfo['draftStatus'] > 0 
				?></td>
				</tr>
				<?php
				} // if (isset($userDrafts)
				$col_size_a_1 = 8;
				$col_size_b_1 = 2;
				$col_size_b_2 = 4;
				$col_size_b_3 = 2;
				if ($type == 'rot') {
					$col_size_a_1 = 7;
					$col_size_b_1 = 2;
					$col_size_b_2 = 3;
					$col_size_b_3 = 2;
				}
				/*----------------------------------------
				/	USER TRADE OFFERS
				/---------------------------------------*/
				// GET AND DISPLAY USER TRADES
				if (isset($userTrades) && isset($userTrades[$data['league_id']])) {
					$tradeInfo = $userTrades[$data['league_id']];
				?>
				<tr style="background-color:#C9F0C7;">
					<td class='hsc2_l' colspan="<?php print($col_size_a_1); ?>">
					<span style="color:#0C4A09;font-weight:bold;">You have <?php print(sizeof($tradeInfo)); ?> trade offers pending!</span>
					</td>
				</tr>
				<?php
					foreach($tradeInfo as $tradeOffer) {
				?>
				<tr style="background-color:#C9F0C7 ;">
					<td class='hsc2_l' colspan="<?php print($col_size_b_1); ?>">
					<b>Offer from:</b> <?php print(anchor('/team/info/'.$tradeOffer['team_1_id'],$tradeOffer['teamname'].' '.$tradeOffer['teamnick'])); ?>
						</td>
					<td class='hsc2_l' colspan="<?php print($col_size_b_2); ?>">
					<b>Received:</b> <?php print(date('m/d/Y h:i A',strtotime($tradeOffer['offer_date'])));
					if ($config['tradesExpire'] == 1 && (isset($tradeOffer['expiration_days']) && !empty($tradeOffer['expiration_days']))) {
						$expireStr = "";
							switch(intval($tradeOffer['expiration_days'])) {
								case -1:
									$expireStr = "No expiration";
									break;
								case 500:
									$expireStr = "Next Sim";
									break;
								default:
									$expireStr = date('m/d/Y h:i A', (strtotime($tradeOffer['offer_date']) + ((60*60*24) * $tradeOffer['expiration_days'])));
									break;
							}
						print(',<br /><b>Expires:</b> '.$expireStr);
					}
					?>
					</td>
					<td class='hsc2_l' colspan="<?php print($col_size_b_3); ?>">
					<?php print anchor('/team/tradeReview/trade_id/'.$tradeOffer['trade_id'].'/league_id/'.$data['league_id'].'/team_id/'.$tradeOffer['team_2_id'].'/trans_type/2','Review Offer');
				?></td>
				</tr>
				<?php
					} // END foreach
					$rowcount++;
				} // if (isset($userTrades)
				/*----------------------------------------
				/	TRADES FOR REVIEW
				/	(If applicable)
				/---------------------------------------*/
				// GET AND DISPLAY LEAGUE TRADES FOR REVIEW
				if (isset($tradesForReview) && sizeof($tradesForReview) > 0 && isset($tradesForReview[$data['league_id']])) {
					$tradeInfo = $tradesForReview[$data['league_id']];
				?>
				<tr style="background-color:#C9F0C7;">
					<td class='hsc2_l' colspan="<?php print($col_size_a_1); ?>">
					<span style="color:#0C4A09;font-weight:bold;">There are <?php print(sizeof($tradeInfo)); ?> trades pending owner approval in your league!</span>
					</td>
				</tr>
				<?php
					foreach($tradeInfo as $tradeOffer) {
				?>
				<tr style="background-color:#C9F0C7 ;" valign="top">
					<td class='hsc2_l' colspan="<?php print($col_size_b_1); ?>">
					Trade between: <?php print(anchor('/team/info/'.$tradeOffer['team_1_id'],$tradeOffer['team_1_name'])); ?> and <?php print(anchor('/team/info/'.$tradeOffer['team_2_id'],$tradeOffer['team_2_name'])); ?>
					</td>
					<td class='hsc2_l' colspan="<?php print($col_size_b_2); ?>">
					<b>Accepted:</b> <?php print(date('m/d/Y h:i A',strtotime($tradeOffer['response_date'])));
					$expireStr = date('m/d/Y h:m A', (strtotime($tradeOffer['response_date']) + ((60*60*24) * $this->params['config']['protestPeriodDays'])));
					print(',<br /><b>Review Period Ends:</b> '.$expireStr);
					
					?>
					</td>
					<td class='hsc2_l' colspan="<?php print($col_size_b_3); ?>">
					<?php print anchor('/team/tradeReview/trade_id/'.$tradeOffer['trade_id'].'/league_id/'.$data['league_id'].'/team_id/'.$data['id'].'/trans_type/4','Review Trade');
				?></td>
				</tr>
				<?php
					} // END foreach
					$rowcount++;
				} // if (isset($tradesForReview)
					/*---------------------------------------------------------------
					/	PLAYOFFS ALERT
					/--------------------------------------------------------------*/
					if ($type != 'rot') { 
						if (isset($playoffs) && sizeof($playoffs) > 0) {
							foreach($playoffs as $playoff_league_id => $playoffData) {
								if ($playoff_league_id == $data['league_id']) { 
									if (isset($playoffData['playoffsNext']) && $playoffData['playoffsNext'] == 1) { ?>
									<tr>
										<td colspan="<?php print($col_size_a_1); ?>" style="background-color:#FFF6BF;" class='hsc2_l'>
										<span style="color:#FFD324;font-weight:bold;">
										<h4 style="color:#514721;">Playoffs start next period! Get Ready.</h4></span>
										<?php
										if ($playoffData['playoffsTrans'] == -1 || $playoffData['playoffsTrades'] == -1) { ?>
										<b>NOTE:</b> The following transactions are disabled in your League during the Playoffs:
										<ul>
											<?php if ($playoffData['playoffsTrans'] == -1 ) { ?>
												<li>Add/Drops</li>
											<?php
											}
											if ($playoffData['playoffsTrades'] == -1 ) { ?>
												<li>Trades</li>
											<?php
											}
											?>
											</ul>
										<?php
										}
										?>
										Be sure to make all approriate roster transactions to be ready before the next scoring period.
										</td>
									</tr>
									<?php
									} else if (isset($playoffData['inPlayoffs']) && $playoffData['inPlayoffs'] == 1) { ?>
									<tr>
										<td colspan="<?php print($col_size_a_1); ?>" class='hsc2_l' style="background-color:#E6EFC2;color:#264409;font-weight:bold;">
										<?php echo($playoffData['league_year']." ".$playoffData['league_name']); ?> Playoffs in Progress!
										</td>
									</tr>
									<?php
									}
								}
							}
						}
					} // END if ($type == 'rot')
					/*---------------------------------------------------------------
					/	END PLAYOFFS ALERT
					/--------------------------------------------------------------*/
				} // ENd foreach
				?>
				</table>
				</div>
				<?php 
					} // END if
				// } else { ?>
				<!--div class="textbox">
				<table style="margin:6px" class="sortable" cellpadding="5" cellspacing="0" width="560px">
				<tr class='title'>
					<td class="lhl"><?php print((($type == 'rot') ? 'Rotisserie' : "Head to Head")." Leagues"); ?></td>
				</tr>
				<tr>
					<td align="center">No teams were found.</td>
				</tr>
				</table>
				</div-->
				<?php //} // END if
				} // ENd foreach
				}
				?>
				<br clear="all" class="clear" />

			</div> 	
        </div>
    </div>