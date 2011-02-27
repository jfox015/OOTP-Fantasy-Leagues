   	<div id="subPage">
        <div class="top-bar"> <div class="top-bar"><h1><?php echo($subTitle); ?></h1></div>

		<div id="content">
			
            <?php
			if (isset($formatted_stats) && sizeof($formatted_stats) > 0) { ?>
            
            <?php
				$lists = array('team_id2','team_id');
				$types = array('batters','pitchers');
				foreach($lists as $team) {
					if (isset($formatted_stats[$team]) && !empty($formatted_stats[$team])) { 
					if ($team == 'team_id2') {?>
						<h2 style="float:left; display:inline-block"><?php
						if (isset($thisItem['team_avatar2']) && !empty($thisItem['team_avatar2'])) { 
							$avatar = PATH_TEAMS_AVATARS.$thisItem['team_avatar2'];
						} else {
							$avatar = PATH_TEAMS_AVATARS.DEFAULT_AVATAR;
						}
						?>
						<img src="<?php echo($avatar); ?>" width="48" height="48" border="0" align="absmiddle" />
						&nbsp;&nbsp;<?php print($team_name2); ?></h2>
					<?php } else { ?>
						<h2 style="float:left; display:inline-block">
						<?php if (isset($thisItem['team_avatar']) && !empty($thisItem['team_avatar'])) { 
							$avatar = PATH_TEAMS_AVATARS.$thisItem['team_avatar'];
						} else {
							$avatar = PATH_TEAMS_AVATARS.DEFAULT_AVATAR;
						}
						?>
						<img src="<?php echo($avatar); ?>" width="48" height="48" border="0" align="absmiddle" />
						&nbsp;&nbsp;<?php print($teamname." ".$teamnick); ?></h2>
					<?php } ?>
                        <div class="textbox" style="width:915px;">
                        <?php
                        foreach($types as $player_type) { 
							if (isset($formatted_stats[$team][$player_type]) && sizeof($formatted_stats[$team][$player_type])>0){ ?>
                                <!-- HEADER -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr class="title">
                                <td height="17" style="padding:6px;"><?php echo($title[$player_type]); ?> Stats</td>
                             </tr>
                             </table>
							<?php
                            echo($formatted_stats[$team][$player_type]);						 
							}
						}
                         ?>
                        </div>
                        <?php
					}
				}
			}
			?>
       </div>
	</div>