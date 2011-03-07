	   
   
   <script type="text/javascript" charset="UTF-8">
	$(document).ready(function(){		   

		$('select#teams').change(function(){
			document.location.href = '<?php echo($config['fantasy_web_root']); ?>team/info/' + $('select#teams').val();
		});
	});
	</script>
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
			<img src="<?php echo($avatar); ?>" width="48" height="48" border="0" align="absmiddle" />
			&nbsp;&nbsp;<?php echo($thisItem['teamname']." ".$thisItem['teamnick']); ?></h1></div>

                    <div id="content">
                    
                    <div style="display:block;width:98%;position:relative; clear:right;">
						<?php if (isset($avail_periods) && sizeof($avail_periods) > 0) {  ?>
                        <div style="width:48%;text-align:left;float:left;">
							<?php echo("<b>Period: </b>");
                            foreach($avail_periods as $period) { 
                                if ($period != $curr_period) {
                                    echo(anchor('/team/info/id/'.$thisItem['team_id']."/period_id/".$period,$period));
                                } else {
                                    echo($period);
                                }
                                echo("&nbsp;");
                            } 
                        	?>
                        </div>
                        <?php } ?>
						<?php if (isset($thisItem['fantasy_teams']) && sizeof($thisItem['fantasy_teams']) > 0 ) {?>
                        <div style="width:48%;text-align:right;float:left;">
                        <label for="teams" style="min-width:225px;">Fantasy Teams:</label> 
                        <select id="teams" style="clear:none;">
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
                    
                    
						<?php if (isset($message) && !empty($message)) { ?>
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
					?>
                	<form action="<?php echo($config['fantasy_web_root']); ?>team/setLineup/id/<?php echo($thisItem['team_id']); ?>" method="post" id="lineupForm" name="lineupForm">
                    <div class='textbox'>
                    	<table style="margin:6px" cellpadding="2" cellspacing="0" border="0" width="895px">
						<?php 
						$roster_types = array('active','reserve','injured');
						foreach ($roster_types as $type) { ?>
                        <tr class='title'>
                        	<td colspan='<?php 
							$rosterTitle = '';
							switch($type) {
								case 'active': $rosterTitle = "Active"; break;
								case 'reserve': $rosterTitle = "Reserve"; break;
								case 'injured': $rosterTitle = "Injured"; break;
							}
							echo($infoColumns+$config['sim_length']); ?>' class='lhl'><?php echo($rosterTitle); ?> Roster</td></tr> 
              			<tr class='headline'>
                        	<td class='hsc2_c'>POS</td>
                            <td class='hsc2_c'>Player</td>
                            <td class='hsc2_c'>Status</td>
                            <?php
							if (isset($thisItem['visible_week']) && sizeof($thisItem['visible_week']) > 0) { 
								foreach ($thisItem['visible_week'] as $day) {
							?>
							
							<td class='hsc2_c'><?php echo(date('m/d',strtotime($day))); ?></td>
							
							<?php 	}
							}   ?>
                            <td class='hsc2_c'>Own%</td>
                            <td class='hsc2_c'>Start%</td>
                            <?php
							if ($showAdmin) { ?>
                            <td class='hsc2_c' colspan="2" align="center">Actions</td>
                            <?php } ?>
                        </tr>
						
						<?php
						if (isset($thisItem['players_'.$type]) && sizeof($thisItem['players_'.$type]) > 0) { 
						$rowcount = 0;
						foreach($thisItem['players_'.$type] as $id => $playerData) {
							if (($rowcount %2) == 0) { $color = "#EAEAEA"; } else { $color = "#FFFFFF"; } 
							?>
                        <tr style="background-color:<?php echo($color); ?>">
                            <?php 
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
							?>
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
							}
							?>
                            <td class='hsc2_c' align="center"><?php echo($statusCode); ?></td>
                            <?php $playerSched = $schedules['players_'.$type][$id];
							if (isset($playerSched) && sizeof($playerSched) > 0) {
								$drawn = 0;
								$limit = $config['sim_length'];
								foreach ($playerSched as $game_id => $game_data) { ?>
									<td class='hsc2_l'><?php
									if ($game_id > 0) {//if($game_data['game_date'] > $thisItem['visible_week'][$drawn]) {
										//	echo("</td><td class='hsc2_l'>");
										//	$drawn++;
										//}
										if ($playerData['team_id'] == $game_data['home_team']) {
											if (isset($thisItem['team_list'][$game_data['away_team']])) {
												echo(strtoupper($thisItem['team_list'][$game_data['away_team']]['abbr']));
											}
										} else {
											if (isset($thisItem['team_list'][$game_data['home_team']])) {
												echo("@".strtoupper($thisItem['team_list'][$game_data['home_team']]['abbr']));
											}
										}
									}
									$drawn++;
									if ($drawn == $limit) break;
								} ?></td>
								<?php 
							} else {
								for ($i = 0; $i < $config['sim_length']; $i++) {
									echo("<td class='hsc2_l'></td>\n");
								}
							} ?>
                            <td class='hsc2_r' align="right"><?php echo($playerData['own']); ?></td>
                            <td class='hsc2_r' align="right"><?php echo($playerData['start']); ?></td> 
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
                                    <?php if ($playerData['is_injured'] == 1 && $playerData['dl_left'] > 0) { ?>
                                    <option value="2">Injured</option>
                                    <?php } ?>
                                <?php } else if ($type == "reserve") { ?>
                                	<option value="-1">Reserve</option>
                                    <option value="1">Active</option>
                                    <?php if ($playerData['is_injured'] == 1 && $playerData['dl_left'] > 0) { ?>
                                    <option value="2">Injured</option>
                                    <?php } ?>
                                <?php } else if ($type == "injured") { ?>
                                	<?php if ($playerData['is_injured'] == 1 && $playerData['dl_left'] > 0) { ?>
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
                    <div style="display:block;width:90%;position:relative; text-align:center; clear:right; margin-bottom:6px;float:right;">
                    <input type="hidden" name="id" value="<?php echo($thisItem['team_id']); ?>" />
                    <input type="submit" class="button" value="Set Lineup" /></div>
                    <?php } ?>
                    </td>
                    </form>
                </div>
            </div>
        