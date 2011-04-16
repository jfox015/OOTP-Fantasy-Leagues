   
   		<div id="single-column">
            <div class="top-bar"><h1><?php 
		if (isset($thisItem['avatar']) && !empty($thisItem['avatar'])) { 
			$avatar = PATH_LEAGUES_AVATARS.$thisItem['avatar']; 
		} else {
			$avatar = PATH_LEAGUES_AVATARS.DEFAULT_AVATAR;
		} ?>
		<img src="<?php echo($avatar); ?>" 
        border="0" width="24" height="24" alt="<?php echo($thisItem['league_name']); ?>" 
        title="<?php echo($thisItem['league_name']); ?>" /> 
		<?php echo($thisItem['league_name']); ?></h1></div>
            <div id="content">
                	<?php 
					if (isset($thisItem['description']) && !empty($thisItem['description'])) { 
						echo($thisItem['description']."<br /><br />"); 
					}
					?>
                    <div class='textbox'>
                    	<table style="margin:6px" class="sortable" cellpadding="5" cellspacing="0" border="0" width="725px">
                    	<?php 
						$cols = ($hasAccess) ? 5 : 4;
						if ($scoring_type == LEAGUE_SCORING_HEADTOHEAD) {
							if (isset($thisItem['divisions']) && sizeof($thisItem['divisions']) > 0) { 
						foreach($thisItem['divisions'] as $id=>$divisionData) { ?>
                    	<tr class='title'>
                        	<td colspan='<?php print($cols); ?>' class='lhl'><?php echo($divisionData['division_name']); ?></td></tr>
              			<tr class='headline'>
                       		<td class='hsc2_c'>&nbsp;</td>
                        	<td class='hsc2_c'>Team</td>
                            <td class='hsc2_c'>Owner</td>
                            <td class='hsc2_c'>Contact</td>
                            <?php if ($hasAccess) { ?>
                            <td class='hsc2_c'>E-Mail</td>
                            <?php } ?>
                        </tr>
              			<?php 
						$rowcount = 0;
						if (isset($divisionData['teams']) && sizeof($divisionData['teams']) > 0) { 
							foreach($divisionData['teams'] as $teamId => $teamData) { 
							if (($rowcount %2) == 0) { $color = "#EAEAEA"; } else { $color = "#FFFFFF"; } 
							?>
                        <tr style="background-color:<?php echo($color); ?>">
                            <?php
							if (isset($teamData['avatar']) && !empty($teamData['avatar'])) { 
								$avatar = PATH_TEAMS_AVATARS.$teamData['avatar'];
							} else {
								$avatar = PATH_TEAMS_AVATARS.DEFAULT_AVATAR;
							}
							?>
                            <td class='hsc2_l'><img src="<?php echo($avatar); ?>" width="24" height="24" border="0" /></td>
                            <td class='hsc2_l'><?php echo(anchor('/team/info/'.$teamId,$teamData['teamname']." ".$teamData['teamnick'])); ?></td>
                            <td class='hsc2_l'><?php if(isset($teamData['owner_id']) && isset($teamData['owner_name'])) {echo(anchor('/user/profiles/'.$teamData['owner_id'],$teamData['owner_name'])); } ?></td>
                            <td class='hsc2_l'><?php //echo($teamData['owner_aim']); ?></td>
                            <?php if ($hasAccess) { ?>
                            <td class='hsc2_l'><?php echo($teamData['owner_email']); ?></td>
                            <?php } ?>
                        </tr>
							<?php
							$rowcount++;
							}
						} else { ?>
                        <tr>
                            <td class="hsc2_l" colspan="<?php print($cols); ?>">No Teams were Found</td>
                        </tr>
						<?php } ?>

            			
						<?php } // END foreach($divisions)
                        } else { ?>
                        <tr class='title'>
                            <td class="lhl">No divisions were found for this league.</td>
                        </tr>
                        <?php } 
						}
							// STANDARD SCORING LEAGUES
						if ($scoring_type != LEAGUE_SCORING_HEADTOHEAD) {
							if (isset($thisItem['teams']) && sizeof($thisItem['teams']) > 0) { 
						 
						?>
                    	<tr class='title'>
                        	<td colspan='<?php print($cols); ?>' class='lhl'>Team List</td></tr>
              			<tr class='headline'>
                       		<td class='hsc2_c'>&nbsp;</td>
                        	<td class='hsc2_c'>Team</td>
                            <td class='hsc2_c'>Owner</td>
                            <td class='hsc2_c'>Contact</td>
                            <?php if ($hasAccess) { ?>
                            <td class='hsc2_c'>E-Mail</td>
                            <?php } ?>
                        </tr>
              			<?php 
						$rowcount = 0;
						foreach($thisItem['teams'] as $teamId=>$teamData) { 
							if (($rowcount %2) == 0) { $color = "#EAEAEA"; } else { $color = "#FFFFFF"; } 
							?>
                        <tr style="background-color:<?php echo($color); ?>">
                            <?php
							if (isset($teamData['avatar']) && !empty($teamData['avatar'])) { 
								$avatar = PATH_TEAMS_AVATARS.$teamData['avatar'];
							} else {
								$avatar = PATH_TEAMS_AVATARS.DEFAULT_AVATAR;
							}
							?>
                            <td class='hsc2_l'><img src="<?php echo($avatar); ?>" width="24" height="24" border="0" /></td>
                            <td class='hsc2_l'><?php echo(anchor('/team/info/'.$teamId,$teamData['teamname']." ".$teamData['teamnick'])); ?></td>
                            <td class='hsc2_l'><?php if(isset($teamData['owner_id']) && isset($teamData['owner_name'])) {echo(anchor('/user/profiles/'.$teamData['owner_id'],$teamData['owner_name'])); } ?></td>
                            <td class='hsc2_l'><?php //echo($teamData['owner_aim']); ?></td>
                            <?php if ($hasAccess) { ?>
                            <td class='hsc2_l'><?php echo($teamData['owner_email']); ?></td>
                            <?php } ?>
                        </tr>
							<?php
							$rowcount++;
							}
						} else { ?>
                        <tr>
                            <td class="hsc2_l" colspan="<?php print($cols); ?>">No Teams were Found</td>
                        </tr>
						<?php } 
						}
                        ?>
                        </table>
            		</div>  <!-- end batting stat div -->
            </div>
        </div>
