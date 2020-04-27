   
   		<div id="center-column-med">
            <div class="top-bar"><h1><?php 
		if (isset($thisItem['avatar']) && !empty($thisItem['avatar'])) { 
			$avatar = PATH_TEAMS_AVATARS.$thisItem['avatar']; 
		} else {
			$avatar = PATH_TEAMS_AVATARS.DEFAULT_AVATAR;
		} ?>
		<img src="<?php echo($avatar); ?>" 
        border="0" width="75" height="75" alt="<?php echo($thisItem['teamname']); ?>" 
        title="<?php echo($thisItem['teamname']); ?>" align="absmiddle" /> 
		<?php echo($thisItem['teamname']); ?></h1></div>
            <div id="content">
                
			</div>

			<div class="news_title teams">
            <h3>Latest Team News</h3>
			</div>
			<div class="rule"></div>

			<?php 
			if (isset($newsTitle) && !empty($newsTitle)) { 
				echo("<h2>".$newsTitle."</h2>");
			}
			if (isset($newsImage) && !empty($newsImage)) { 
			// GET IMAGE DIMENSIONS
			$size = getimagesize(FCPATH.'images\news\\'.$newsImage);
			if (isset($size) && sizeof($size) > 0) {
				if ($size[0] > $size[1]) {
					$class = "wide";
				} else {
					$class = "tall";
				}
			}
			?>
			<img src="<?php echo(PATH_NEWS_IMAGES.$newsImage); ?>" align="left" border="0" class="league_news_<?php echo($class); ?>" />
			<?php } ?>
			
			<?php 
			if (isset($newsDate) && !empty($newsDate)) { 
				echo('<span class="league_date">'.date('l, M d',strtotime($newsDate)).'</span>');
			} 
			if (isset($newsDate) && !empty($newsDate) && isset($author) && !empty($author)) { 
				echo(", ");
			}
			if (isset($author) && !empty($author)) { 
				echo('<span class="news_author">'.$author.'</span>');
			}
			?>
			<br />

			<?php if (isset($newsBody) && !empty($newsBody)) { 
				$maxChars = 500;
				if (strlen($newsBody) > $maxChars) {
					$dispNews = substr($newsBody,0,$maxChars);
				} else {
					$dispNews = $newsBody;
				}
				echo('<span class="news_body">'.$dispNews);
				if (strlen($newsBody) > $maxChars) {
					echo('&nbsp;&nbsp;'.anchor('/news/info/'.$newsId,'Read more...').'</span>');
				}
			} else {
				echo("No news is available at this time.");
			} ?>
			<p>
			<br clear="all" />
			<img src="<?php echo($config['fantasy_web_root']); ?>images/icons/icon_search.gif" width="16" height="16" border="0" alt="Add" title="add" align="absmiddle" /> 
			<?php echo anchor('/search/news/', 'More League News'); ?><br />
			<?php 
			if ($loggedIn && $isOwner) {
				echo('<img src="'.$config['fantasy_web_root'].'images/icons/icon_add.gif" width="16" height="16" border="0" alt="Add" title="add" align="absmiddle" /> '.anchor('/news/submit/mode/add/type_id/'.NEWS_LEAGUE.'/var_id/'.$league_id,'Add News Article'));
			}
			?>
			<?php
			/*------------------------------------------------
			/	TEAM TRANSACTIONS MODULE
			/-----------------------------------------------*/
			?>
			<div class='textbox'>
				<table style="margin:6px" class="sortable" cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr>
					<td>
					<?php if (isset($transaction_summary) && sizeof($transaction_summary) > 0) { 
						echo($transaction_summary); 
					} else {
						echo('<table><tr class="title"><td>Recent Transactions</td></tr><tr><td>No Transactions were found.</td></tr></table>'); 
					}
					?>
					</td>
				</tr>
				</table>
				<div style="width:98%; text-align:right;">
				<?php echo anchor('/team/transactions/'.$thisItem['team_id'],'See all transactions'); ?>
				</div>
				
			</div>
		</div>

		<div id="right-column-wide">
			<div class="teamDetails">
				<h3>Team Owner: 
					<span class="ownerName"><?php 
					if (isset($thisItem['owner_name']) && !empty($thisItem['owner_name'])) { 
						echo($thisItem['owner_name']);
					} else { 
						echo("No owner");
					} 
					?></span>
					</h3>
				<div class="record">
					<?php 
					$teamRecordStr = "Tied, 1st Place";
					if (isset($standings) && sizeof($standings) > 0) { 
						$found = false;
						if ($scoring_type == LEAGUE_SCORING_HEADTOHEAD) {
							$teamRecordStr = "0-0, 1st Place";
							foreach($standings as $id=>$divisionData) { 
								if (isset($divisionData['teams']) && sizeof($divisionData['teams']) > 0) { 
									$count = 1; 
									foreach($divisionData['teams'] as $teamId => $teamData) { 
										if ($thisItem['team_id'] == $teamId) {
											$teamRecordStr = $teamData['w']."-".$teamData['l'].", ".ordinal_suffix($count)." in ".$divisionData['division_name'];
											break;
										}
										$count++;
									}
								}
								if ($found) break;
							}
						} else {
							$count = 1;
							foreach($standings  as $id=>$teamData) {
								if ($thisItem['team_id'] == $id) {
									$teamRecordStr = ordinal_suffix($count)." Place";
									break;
								}
								$count++;
							}
						}
					}
					echo($teamRecordStr);
					?>
				</div>
			</div>
			<?php
			/*----------------------------------------
			/	Roster Satus Alert
			/---------------------------------------*/
			// GET AND DISPLAY USER TRADES
			if (isset($rostMessage) && !empty($rostMessage)) { ?>
				<div class='textbox right-column'>
					<table cellpadding="0" cellspacing="0">
						<thead>	
						<tr class="title">
							<td class='hsc2_l'>Roster Status</td>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
							<?php
							$rostMessageType = isset($rostMessageType) ? $rostMessageType : "";
							echo('<span class="'.$rostMessageType.'">'.$rostMessage.'</span>');
							?>
							</td>
						</tr>
					</tbody>
					</table>
				</div>
			<?php
			}
			/*----------------------------------------
			/	USER TRADE OFFERS
			/---------------------------------------*/
			// GET AND DISPLAY USER TRADES
			if (isset($userTrades) && isset($userTrades[$league_id])) {
				$tradeInfo = $userTrades[$league_id];
			?>
			<div class="info">
				<img src="<?php echo(PATH_IMAGES."icons/icon_info.gif");?>" width="32" height="32" align="absmiddle" /> <b >Trade Alert!</b>
			</div>
			<div class='textbox right-column tradesbox'>
				<table cellpadding="0" cellspacing="0">
				<thead>	
				<tr class="title">
					<th colspan="2">
					<span style="color:#C9F0C7;font-weight:bold;">You have <?php print(sizeof($tradeInfo)); ?> trade offers pending!</span>
					</th>
			</tr>
				</thead>
				<tbody>
				<?php
				foreach($tradeInfo as $tradeOffer) {
				?>
				<tr class="sl_2">
					<td><b>Offer from:</b></td>
					<td colspan="2"><?php print(anchor('/team/info/'.$tradeOffer['team_1_id'],$tradeOffer['teamname'].' '.$tradeOffer['teamnick'])); ?></td>
				</tr>
				<tr class="sl_1">
					<td><b>Received:</b></td>
					<td>
					 <?php print(date('m/d/Y h:i A',strtotime($tradeOffer['offer_date'])));
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
					}
					?>
					</td>
				</tr>
				<tr class="sl_2">
					<td><b>Expires:</b></td>
					<td><?php echo($expireStr); ?></td>
				</tr>
				<tr class="sl_1">
					<td colspan="2" class="review">
					<?php 
					print anchor('/team/tradeReview/trade_id/'.$tradeOffer['trade_id'].'/league_id/'.$league_id.'/team_id/'.$tradeOffer['team_2_id'].'/trans_type/2','Review Offer');
					?>
					</td>
				</tr>
				<?php
				} // END foreach
				?>
					</tbody>
				</table>
			</div>
			<?php
			} // if (isset($userTrades)
			/*----------------------------------------
			/	TRADES FOR REVIEW
			/	(If applicable)
			/---------------------------------------*/
			// GET AND DISPLAY LEAGUE TRADES FOR REVIEW
			if (isset($tradesForReview) && sizeof($tradesForReview) > 0 && isset($tradesForReview[$league_id])) {
				$tradeInfo = $tradesForReview[$league_id];
			?>
			<div class='textbox right-column statsbox'>
				<table cellpadding="0" cellspacing="0">
				<thead>	
					<tr style="background-color:#C9F0C7;">
						<td class='hsc2_l' colspan="3">
						<span style="color:#0C4A09;font-weight:bold;">There are <?php print(sizeof($tradeInfo)); ?> trades pending owner approval in your league!</span>
						</td>
					</tr>
				</thead>
				<tbody>
				<?php
				foreach($tradeInfo as $tradeOffer) {
				?>
				<tr style="background-color:#C9F0C7;" align="left" valign="top">
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
					<?php 
					print anchor('/team/tradeReview/trade_id/'.$tradeOffer['trade_id'].'/league_id/'.$league_id.'/team_id/'.$data['id'].'/trans_type/4','Review Trade');
					?>
					</td>
				</tr>
			<?php
				} // END foreach
                //$rowcount++;
            ?>
                </tbody>
                </table>
            </div>

			<?php
			} // if (isset($tradesForReview)
			?>
			
			<div class='textbox right-column statsbox'>
				<table cellpadding="0" cellspacing="0">
				<thead>	
				<tr class='title'>
					<td  colspan='5' style='padding:3px'>Top Players</td>
				</tr>
				<tr class='headline'>
					<td colspan='5' class="hsn2">Batters</td>
				</tr>
				<tr class='statscol'>
					<th >Player</th>
					<th >AVG</th>
					<th >HR</th>
					<th >OPS</th>
					<th ><?php if ($scoring_type == LEAGUE_SCORING_HEADTOHEAD) { ?>FPTS<?php } else { ?>PR15<?php } ?></th>
				</tr>
				</thead>
				<tbody>
				<?php
				$htmlpath=$config['ootp_html_report_path'];
				$filepath=$config['ootp_html_report_root'];
				if (isset($batter_stats) && count($batter_stats) > 0) {
					$rowCount = 0;
					foreach ($batter_stats as $batter) {
						## Check for photo by player ID
						echo("<tr class='statsrow");
						if (($rowCount % 2) != 0) { echo (" even"); }
						echo("'>\n");
						echo("<td>".$batter['player_name']."</td>\n");
						echo("<td>".$batter['avg']."</td>\n");
						echo("<td>".$batter['hr']."</td>\n");
						echo("<td>".$batter['ops']."</td>\n");
						echo("<td>\n");
						if ($scoring_type == LEAGUE_SCORING_HEADTOHEAD) { echo($batter['fpts']); } else { echo($batter['rating']); }
						echo("</td>\n");
						echo("</tr>\n");
						$rowCount++;
					} // END foreach
				} // END if
				?>
				</tbody>
				</table>
				<table cellpadding="0" cellspacing="0" border="0">
				<thead>	
				<tr class='headline'>
					<td colspan='4' class="hsn2">Pitchers</td>
				</tr>
				<thead>
					<tr class='statscol'>
						<th >Player</th>
						<th >W</th>
						<th >K</th>
						<th >ERA</th>
						<th ><?php if ($scoring_type == LEAGUE_SCORING_HEADTOHEAD) { ?>FPTS<?php } else { ?>PR15<?php } ?></th>
					</tr>
				</thead>	
				<tbody>	
				<?php
				if (isset($pitcher_stats) && count($pitcher_stats) > 0) {
					$rowCount = 0;
					foreach ($pitcher_stats as $pitcher) {
						echo("<tr class='statsrow");
						if (($rowCount % 2) != 0) { echo (" even"); }
						echo("'>\n");
						echo("<td>".$pitcher['player_name']."</td>\n");
						echo("<td>".$pitcher['w']."</td>\n");
						echo("<td>".$pitcher['pk']."</td>\n");
						echo("<td>".$pitcher['era']."</td>\n");
						echo("<td>\n");
						if ($scoring_type == LEAGUE_SCORING_HEADTOHEAD) { echo($pitcher['fpts']); } else { echo($pitcher['rating']); }
						echo("</td>\n");
						echo("</tr>\n");
						$rowCount++;
					} // END foreach
				} // END if
				?>
				</tbody>
				</table>
			</div>
			<?php 
			/*---------------------------------------------------------
			/
			/	HEAD to HEAD GAMES BOX
			/
			/-------------------------------------------------------*/
			if ($scoring_type == LEAGUE_SCORING_HEADTOHEAD) { 
			?>
			<div class='textbox right-column gamebox'>
				<table cellpadding="0" cellspacing="0">
				<thead>
				<tr class='title'>
					<td>Recent and Upcoming Games</td>
				</tr>
				<?php 
				if (isset($recentGames) && sizeof($recentGames) > 0) {
				?>
				<tr class='headline'>
					<th colspan="3">Period <?php echo($gamePeriod); ?> Result</th>
				</tr>
				<tr>
					<th>Opponent</th>
					<th>Score</th>
					<th>Result</th>
				</tr>
				</thead>
				<tbody>
					<?php
					foreach($recentGames as $game) {	
				?>
				<tr>
					<td><?php 
					if (isset($game['avatar']) && !empty($game['avatar'])) { 
						$oppAvatar = PATH_TEAMS_AVATARS.$game['avatar']; 
					} else {
						$oppAvatar = PATH_TEAMS_AVATARS.DEFAULT_AVATAR;
					} ?>
					<img src="<?php echo($oppAvatar); ?>" width="35" height="35" alt="<?php echo($game['teamname']); ?>" 
					title="<?php echo($game['teamname']); ?>" align="absmiddle" /> 
					<?php echo anchor('/team/info/'.$game['opp_team_id'], $game['teamname']); ?></td>
					<td>
						<?php 
						$awayScore = ''; 
						$homeScore = '';
						if ($game['team_loc'] == 'away' && $game['outcome'] == 1) { $awayScore = '<span class="winner">'; }
						$awayScore .= $game['away_team_score'];
						if ($game['team_loc'] == 'away' && $game['outcome'] == 1) { $awayScore .= '</span>'; }
						
						if ($game['team_loc'] == 'home' && $game['outcome'] == 1) { $homeScore = '<span class="winner">'; }
						$homeScore .= $game['home_team_score'];
						if ($game['team_loc'] == 'home' && $game['outcome'] == 1) { $homeScore .= '</span>'; }

						echo(anchor('/league/results/id/'.$league_id.'/period_id/'.$gamePeriod.'/game_id/'.$game['game_id'], $awayScore."&nbsp;-&nbsp;".$homeScore));
						?>
					</td>
					<?php
					$outStr = '';
					switch($game['outcome']) {
						case 1:
							$outStr = '<td class="result positive"><strong>W</strong></td>';
							break;
						case -1:
						default:
							$outStr = '<td class="result negative">L</td>';
							break;
					} // END switch
					echo($outStr);
					?>					
				</tr>
				<?php
					} // END foreach
				?>
				</tbody>
				<thead>
				<?php
				} // END if
				if (isset($upcomingOpponent) && sizeof($upcomingOpponent) > 0) {
				?>
				<tr class='headline'>
					<th colspan="3">Next Games in Period <?php echo($curr_period); ?></th>
				</tr>
				<?php
					foreach($upcomingOpponent as $opponent) {
				?>
				</thead>
				<tbody>
				<tr>
					<td colspan="3">
					<?php 
					if (isset($opponent['avatar']) && !empty($opponent['avatar'])) { 
						$oppAvatar = PATH_TEAMS_AVATARS.$opponent['avatar']; 
					} else {
						$oppAvatar = PATH_TEAMS_AVATARS.DEFAULT_AVATAR;
					} ?>
					<img src="<?php echo($oppAvatar); ?>" width="35" height="35" alt="<?php echo($opponent['teamname']); ?>" 
					title="<?php echo($opponent['teamname']); ?>" align="absmiddle" /> 
					<?php echo anchor('/team/info/'.$opponent['opp_team_id'], $opponent['teamname']); ?>
					</td>
				</tr>
				<?php
					} // END foreach
				} // END if
				?>
				</tbody>
				</table>
			</div>
			<?php
			} // END if ($scoring_type == LEAGUE_SCORING_HEADTOHEAD)
			?>
		</div>