	<script type="text/javascript" charset="UTF-8">
	$(document).ready(function(){		   

		$('select#teams').change(function(){
			document.location.href = '<?php echo($config['fantasy_web_root']); ?>team/lineup/' + $('select#teams').val();
		});

		/*---------------------------------------------------
		/	EDIT 1.0.3
		/	Bug #36, Add FF point total to lineup screen
		/--------------------------------------------------*/
		$('a[rel=listPick]').live('click',function () {
			//var params = this.id.split("|");
			//console.log("Link clicked. Option = " + this.id);
			var options = ['years_stat', 'team_stat', 'sched'];
			for (var i = 0; i < options.length; i++) {
				if (this.id == options[i]) {
					$('td.'+options[i]).removeClass('field_hide');
					$('td.'+options[i]).addClass('field_show');
				} else {
					$('td.'+options[i]).removeClass('field_show');
					$('td.'+options[i]).addClass('field_hide');
				}
			}
			if (this.id.indexOf("stat") != -1) {
				$('td.stat').removeClass('field_hide');
				$('td.stat').addClass('field_show');
			} else {
				$('td.stat').removeClass('field_show');
				$('td.stat').addClass('field_hide');
			}
			return false;
		});
		$('div[rel=datePick]').live('click',function () {
			document.location.href = '<?php echo($config['fantasy_web_root']); ?>team/lineup/id/' + <?php echo($thisItem['team_id']); ?> + '/display_date/' + this.id ;
		});
	});
	</script>
	<style>
	.field_show { display:table-cell; }
	.field_hide { display:none; }
	</style>
   	<?php 
	$schedules = array();
	if (isset($thisItem['schedules']) && sizeof($thisItem['schedules']) > 0) {
		$schedules = $thisItem['schedules'];
	}
	?>
   		<div id="subPage">
            <div class="top-bar"><h1><?php
			if (isset($thisItem['avatar']) && !empty($thisItem['avatar'])) { 
				$avatar = PATH_TEAMS_AVATARS.$thisItem['avatar'];
			} else {
				$avatar = PATH_TEAMS_AVATARS.DEFAULT_AVATAR;
			}
			?>
			<img src="<?php echo($avatar); ?>" width="75" height="75" align="absmiddle" />
			&nbsp;&nbsp;<?php echo($thisItem['teamname']." ".$thisItem['teamnick']); ?> Lineup</h1></div>

                    <div id="content">
                    
                    <div style="display:block;width:98%;position:relative; clear:right;">
						<?php if (isset($avail_periods) && sizeof($avail_periods) > 0) {  ?>
                        <div style="width:45%;text-align:left;float:left;">
							<?php echo("<b>Period: </b>");
                            foreach($avail_periods as $period) { 
                                if ($period != $curr_period) {
                                    echo(anchor('/team/lineup/id/'.$thisItem['team_id']."/period_id/".$period,$period));
                                } else {
                                    echo($period);
                                }
                                echo("&nbsp;");
							}
							// EDIT 1.2 PROD - GAME DATE DISPLAY
							if ($simType == SIM_TYPE_DAILY && $leagueTransFreq == SIM_TYPE_DAILY) {
								//$dateStart = ;
								//$dateEnd = strtotime($curr_period['date_end']);
								?>
								<div class="dateCal">
									<?php 
									$day = 60*60*24;
									$count = 0;
									$date = strtotime($currPeriod['date_start']." ".EMPTY_TIME_STR);
									$gameDate = strtotime($game_date);
									while ($count < $sim_length) { 
										?>
										<div class="dateCal-item<?php if ($date == $gameDate) { echo(' active'); } ?>"
										<?php if ($date <= $gameDate) { echo(' rel="datePick" id="'.date('Y-n-j', $date).'"'); } ?>>
											<p><?php echo(date('M', $date)); ?>
											<h4><?php echo(date('d', $date)); ?></h4>
											<?php echo(date('Y', $date)); ?></p>
										</div>
									<?php 
										$date += $day;
										$count++;
									} // END while($count < $sim_length)
									?>
								</div>
								<?php 
							} // END if ($simType == SIM_TYPE_DAILY)
							?>
                        </div>
						<?php
						} 
						?>
						
						<div style="float:left; width:54%;">
							<div style="float:left; text-align:left; width:70%; padding-top:5px;">
								<label for="display" style="min-width:55px; margin-top:0;">Show:</label> 
								<a href="#" id="years_stat" rel="listPick">Season Stats</a> | <a href="#" id="team_stat" rel="listPick">Team Stats</a> | 
								<a href="#" id="sched" rel="listPick">Schedule</a> 
							</div>
							<?php if (isset($thisItem['fantasy_teams']) && sizeof($thisItem['fantasy_teams']) > 0 ) { ?>
							<div style="width:30%;text-align:right;float:left;">
							
								<label for="teams" style="min-width:95px; margin-right: 5px; margin-top:5px;display: inline-block;">Fantasy Teams:</label> 
								<select id="teams" style="clear:none;display: inline-block;">
									<?php  
									foreach($thisItem['fantasy_teams'] as $id => $teamName) {
										echo('<option value="'.$id.'"');
										if ($id == $thisItem['team_id']) { echo(' selected="selected"'); }
											echo('>'.$teamName.'</option>');
									}
									?>
								</select>
							</div>
							<?php } ?>
						</div>
						<?php 
						/*------------------------------------------------
						/	LEAGUE PLAYOFF ALERT BOX FOR H2H
						/-----------------------------------------------*/
						if (isset($thisItem['playoffsNext']) && $thisItem['playoffsNext'] == 1) { ?>
                        <div style="display:block;width:98%;position:relative; clear:right; float:left;">
							<span class="notice"><h3>Get Ready for the Playoffs</h3>
							<?php
							if ($thisItem['playoffsTrans'] == -1 || $thisItem['playoffsTrades'] == -1) { ?>
							<b>NOTE:</b> The following transactions are disabled in your League during the Playoffs:
							<ul>
							<?php if ($thisItem['playoffsTrans'] == -1 ) { ?>
								<li>Add/Drops</li>
							<?php
							}
							if ($thisItem['playoffsTrades'] == -1 ) { ?>
								<li>Trades</li>
							<?php
							}
							?>
							</ul>
							<?php
							} // END  if ($thisItem['playoffsTrans'] == -1
							echo("Be sure to make all approriate roster transactions before the next scoring period.");
							?>
							</span>
                        </div>
                        <?php
                        } // END if (isset($thisItem['playoffsNext']) 
                        ?>
						<?php 
						/*------------------------------------------------
						/	MESSAGE ALERT BOX
						/-----------------------------------------------*/
						if (isset($message) && !empty($message)) { ?>
                        <div style="display:block;width:98%;position:relative; clear:right; float:left;">
                        <?php
                            $messageType = isset($messageType) ? $messageType : "";
                            echo('<span class="'.$messageType.'">'.$message.'</span>');
                        ?>
                        </div>
                        <?php
                        }
                        ?>
                    </div>
                    <?php
					//$showAdmin = false;
					$infoColumns = 5;
					if ($showAdmin) {
						$infoColumns = 7;
					}	
					/*----------------------------------------------
					/	STATS DISPLAY CHANGE. CREATE HEADINGS
					/	DYNAMICALLY TO HANDLE SWITCHING BETWEEN 
					/ 	STATS TYPES DURING RENDERING OF THE 
					/	ROSTER STATUS TYPES
					/----------------------------------------------*/
					$headlines = array();
					$headlineL = "<tr class='headline'>\n
					<td class='hsc2_c'>POS</td>\n
					<td class='hsc2_c'>Player</td>\n
					<td class='hsc2_c'>Status</td>\n";
					$headlineBats = '';
					if (isset($colnames['batters'])) { 
						$colNames = explode("|", $colnames['batters']);
						foreach ($colNames as $name) {
							$headlineBats .= "<td class='hsc2_r stat field_show'>".$name."</td>\n";
						}
					}
					$headlinePitch = '';
					if (isset($colnames['pitchers'])) { 
						$colNames = explode("|", $colnames['pitchers']);
						foreach ($colNames as $name) {
							$headlinePitch .= "<td class='hsc2_r stat field_show'>".$name."</td>\n";
						}
					}
					$headlineSched = '';
					if (isset($thisItem['visible_week']) && sizeof($thisItem['visible_week']) > 0) { 
						foreach ($thisItem['visible_week'] as $day) {
							$headlineSched .= "<td class='hsc2_c sched field_hide'>".date('m/d',strtotime($day))."</td>\n";
						}
					}
					$headlineR = "<td class='hsc2_c'>Own%</td>\n
					<td class='hsc2_c'>Start%</td>\n";
					if ($showAdmin) { 
						$headlineR .= "<td class='hsc2_c' colspan='2' align='center'>Actions</td>\n";
					} 
					$headlineR .= "</tr>\n";
					$headlines['batting'] = $headlineL.$headlineBats.$headlineSched.$headlineR;
					$headlines['pitching'] = $headlineL.$headlinePitch.$headlineSched.$headlineR;
					?>
                	<form action="<?php echo($config['fantasy_web_root']); ?>team/setLineup/id/<?php echo($thisItem['team_id']); ?>" method="post" id="lineupForm" name="lineupForm">
                    <div class='textbox toolbox'>
                    	<table style="margin:6px" cellpadding="2" cellspacing="0" width="895px">
						<?php 
						$roster_types = array('active','reserve','injured');
						foreach ($roster_types as $type) { 
							$batHeaderDrawn = false;
							$pitchHeaderDrawn = false;
							?>
                        <tr class='title'>
                        	<td colspan='<?php 
							$rosterTitle = '';
							switch($type) {
								case 'active': $rosterTitle = "Active"; break;
								case 'reserve': $rosterTitle = "Reserve"; break;
								case 'injured': $rosterTitle = "Injured"; break;
							}
							echo($infoColumns+$config['sim_length']); ?>' class='lhl'><?php echo($rosterTitle); ?> Roster</td></tr> 
						<?php
						$statsTypes = array("years","team");
						if ((isset($thisItem['players_'.$type]) && sizeof($thisItem['players_'.$type]) > 0)) {
						$rowcount = 0;
						//echo($headlines['batting']);
						foreach($thisItem['players_'.$type] as $id => $playerData) {

							if ($playerData['position'] != 1) {
								$ftyPos = get_pos($playerData['player_position']);
							} else {
								if (isset($playerData['player_role']) && !EMPTY($playerData['player_role'])) {
									$ftyPos = get_pos($playerData['player_role']);
								} else {
									$ftyPos = "";
								}
							}
							$pos = unserialize($playerData['positions']);
							$gmPos = '';
							if (is_array($pos) && sizeof($pos) > 0) {
								foreach($pos as $position) {
									if ($position != 25) {
										if (!empty($gmPos)) $gmPos .= ",";
										$gmPos .= get_pos($position);
									}
								}
							}
							if (!$batHeaderDrawn && $playerData['position'] != 1) {
								echo($headlines['batting']);
								$batHeaderDrawn = true;
							}
							if (!$pitchHeaderDrawn && $playerData['position'] == 1) {
								echo($headlines['pitching']);
								$pitchHeaderDrawn = true;
							}
							if (($rowcount %2) == 0) { $color = "#EAEAEA"; } else { $color = "#FFFFFF"; } 
							?>
                        <tr style="background-color:<?php echo($color); ?>">
                            <td class='hsc2_l'><?php echo($ftyPos); ?></td>
                            <td class='hsc2_l'><?php 
							// PLAYER NAME AND BIO LINK
							echo anchor('/players/info/player_id/'.$playerData['id'].'/league_id/'.$thisItem['league_id'],$playerData['first_name']." ".$playerData['last_name']).' <span style="font-size:smaller;">'.$gmPos." ".$playerData['team_abbr'].'</span>'; 
							// INJURY STATUS
							$injStatus = "";
							if ($playerData['injury_is_injured'] == 1) {
								$injStatus = makeInjuryStatusString($playerData);
							}
							if (!empty($injStatus)) { ?>
                            <img src="<?php echo($config['fantasy_web_root']); ?>images/icons/red_cross.gif" width="7" height="7" align="absmiddle" 
                            alt="<?php echo($injStatus); ?> " title="<?php echo($injStatus); ?>" />
                            <?php } ?></td>
                            <?php 
							$statusCode = "";
							switch($playerData['player_status']) {
								case 1: $statusCode = "A"; break;
								case 2: $statusCode = "M"; break;
								case 3: $statusCode = "DL"; break;
								case 4: $statusCode = "R"; break;
								case 5: $statusCode = "I"; break;
							}
							?>
                            <td class='hsc2_c'><?php echo($statusCode); ?></td>
							<?php
							/*----------------------------------------------
							/	PLAYER STATS
							/---------------------------------------------*/
							// 1.1.1 UPDATED TO DISPLAY BOTH TEAM ONLY AND SEASON STATS
							// LOOP THROUGH AND DRAW BOTH TYPES OF STATS FIRST
							$playerType = 'batters';
							if ($playerData['position'] == 1) $playerType = 'pitchers';
							foreach ($statsTypes as $statType) {
								
								$classStr = "hsc2_r ".$statType."_stat field_";
								if ($statType == "team") $classStr .= "hide"; else $classStr .= "show";
								$theseStats = $playerStats['stats_'.$statType.'_'.$playerType][$playerData['id']];
								//echo("Size of theseStats for player ".$playerData['id']." = ".sizeof($theseStats));
								if (isset($theseStats) && sizeof($theseStats)> 0) {
									foreach($theseStats as $key=>$val) {
										if ($key != "id" && $key != "player_id") {
											$color = "#000";
											if ($key == 'rating' || $key == "fpts") {
												if ($val > 0) {
													$color = "#080";
												} else if ($val < 0) {
													$color = "#C00";
												} // END if
											} // END if
									?>
									<td class="<?php echo($classStr); ?>"><?php echo('<span style="color:'.$color.';">'.$val.'</span>'); ?></td>
									<?php
										} // END if
									} // END foreach
								} else {
									for ($i = 0; $i < 7; $i++) { ?>
										<td class="<?php echo($classStr); ?>">0</td>
									<?php
									}
								}// END if (isset($playerData['stats'])
							}
							
							$playerSched = $schedules['players_'.$type][$id];
							if (isset($playerSched) && sizeof($playerSched) > 0) {
								//$drawn = 0;
								//$limit = $config['sim_length'];
								$iconStr = $config['ootp_html_report_path'].'images/dot1.gif';
								$dateStr = '';
								$startStr = '';
								$gameDate = EMPTY_DATE_STR;
								foreach ($playerSched as $game_id => $game_data) { 
									if ($gameDate != strtotime($game_data['game_date']." 00:00:00")) {
										if ($gameDate != EMPTY_DATE_STR) {
											echo($dateStr.$startStr."</td>\n");
											$drawn++;
										}
										$dateStr = "<td class='hsc2_l sched field_hide'>";
										$startStr = '';
										$gameDate = strtotime($game_data['game_date']." 00:00:00");
										if ($game_id > 0) {
											if ($playerData['team_id'] == $game_data['home_team']) {
												if (isset($thisItem['team_list'][$game_data['away_team']])) {
													$dateStr .= strtoupper($thisItem['team_list'][$game_data['away_team']]['abbr']);
												}
											} else {
												if (isset($thisItem['team_list'][$game_data['home_team']])) {
													$dateStr .= "@".strtoupper($thisItem['team_list'][$game_data['home_team']]['abbr']);
												}
											}
											if ($game_data['start'] != -1)
												$startStr = '&nbsp;<img src="'.$iconStr.'" />';
										}
									} else {
										$dateStr .= "(2)";
									}
								}
								echo($dateStr.$startStr."</td>\n");
								?>
								<?php 
							} else {
								for ($i = 0; $i < $config['sim_length']; $i++) {
									echo("<td class='hsc2_l sched field_hide'></td>\n");
								}
							} ?>
                            <td class='hsc2_c'><?php echo($playerData['own']); ?></td>
                            <td class='hsc2_c'><?php echo($playerData['start']); ?></td> 
							<?php
							if ($showAdmin) {
							?>
                            <td class='hsc2_l'>
                            <?php if ($playerData['position'] == 1) { $pos_type = 'role'; } else { $pos_type = 'position'; } ?>
                            <select name="<?php echo($pos_type); ?>_<?php echo($playerData['id']); ?>" id="<?php echo($pos_type); ?>_<?php echo($playerData['id']); ?>">
                            	<?php 
								$pos = unserialize($playerData['positions']);
								if (is_array($pos) && sizeof($pos) > 0) {
									foreach($pos as $position) {
										
										echo('<option value="'.$position.'"');
										if ($playerData['position'] == 1) {
											$cmpPos = $playerData['player_role'];
										} else {
											$cmpPos = $playerData['player_position'];
										}
										if ($position == $cmpPos) {
											echo(' selected="selected"');
										}
										echo('>'.get_pos($position).'</option>\n');
									}
								}
								?>
                            </select>
                            </td>
                            <td class='hsc2_l'>
                            <select name="status_<?php echo($playerData['id']); ?>" id="status_<?php echo($playerData['id']); ?>">
                            	<?php if ($type == 'active') { ?>
                                
                                    <option value="1">Active</option>
                                    <option value="-1">Reserve</option>
                                    <?php if ($playerData['injury_is_injured'] == 1 && $playerData['injury_dl_left'] > 0) { ?>
                                    <option value="2">Injured</option>
                                    <?php } ?>
                                <?php } else if ($type == "reserve") { ?>
                                	<option value="-1">Reserve</option>
                                    <option value="1">Active</option>
                                    <?php if ($playerData['injury_is_injured'] == 1 && $playerData['injury_dl_left'] > 0) { ?>
                                    <option value="2">Injured</option>
                                    <?php } ?>
                                <?php } else if ($type == "injured") { ?>
                                	<?php if ($playerData['injury_is_injured'] == 1 && $playerData['injury_dl_left'] > 0) { ?>
                                    <option value="2">Injured</option>
                                    <?php } ?>
                                    <option value="1">Active</option>
                                    <option value="-1">Reserve</option>
                                <?php } ?>
                            </select>
                            </td>
                            <?php } ?>
                        </tr>
							<?php
							$rowcount++;
							}
						} else { ?>
                        <tr>
                            <td class="hsc2_l" colspan="2">No Players were Found</td>
                        </tr>
						<?php 
						} // END if 
						} // END foreach
						?>
                        </table>
            		</div>  <!-- end batting stat div -->
                    <?php if ($showAdmin) { ?>
                    <div class="roster-actions-box">
					<input type="hidden" name="id" value="<?php echo($thisItem['team_id']); ?>" />
					<?php
					// EDIT 1.2 PROD - DAILY ROSTER SUPPORT
					if ($simType == SIM_TYPE_DAILY && $leagueTransFreq == SIM_TYPE_DAILY) {
						?>
					<input type="hidden" name="game_date" value="<?php echo($game_date); ?>" />
					<?php
					}
					?>
                    <button class="sitebtn lineup" onclick="document.lineupForm.submit();">Set Lineup</button></div>
                    <?php } ?>
                    </td>
                    </form>
                </div>
            </div>
        