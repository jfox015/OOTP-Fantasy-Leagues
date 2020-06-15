   		<script type="text/javascript" charset="UTF-8">
		$(document).ready(function(){		   

			$('select#teams').change(function(){
				document.location.href = '<?php echo($config['fantasy_web_root']); ?>team/eligibility/' + $('select#teams').val();
			});
		});
		</script>
	   
	   <div id="subPage">
            <div class="top-bar"><h1><?php echo($subTitle); ?></h1>
        
			<h2><?php
			if (isset($thisItem['avatar']) && !empty($thisItem['avatar'])) { 
				$avatar = PATH_TEAMS_AVATARS.$thisItem['avatar'];
			} else {
				$avatar = PATH_TEAMS_AVATARS.DEFAULT_AVATAR;
			}
			?>
			<img src="<?php echo($avatar); ?>" width="48" height="48" align="absmiddle" />
			&nbsp;&nbsp;<?php echo($teamname." ".$teamnick); ?></h2></div>

				<!-- TEAM NAVIGATOR -->
            <?php 
			if (isset($thisItem['fantasy_teams']) && sizeof($thisItem['fantasy_teams']) > 0 ) {?>
				<div style="width:48%;text-align:right;float:left;">
				<label for="teams" style="min-width:225px;">Fantasy Teams:</label> 
				<select id="teams" style="clear:none;">
					<?php  
					foreach($thisItem['fantasy_teams'] as $id => $teamName) {
						echo('<option value="'.$id.'"');
						if ($id == $team_id	) { echo(' selected="selected"'); }
						echo('>'.$teamName.'</option>');
					}
					?>
				</select>
				</div>
			<?php 
			}  // END if
			?>
			
				<!-- TEAM ELIGIBILITY BOX -->
            <div class='textbox'>
                <table style="margin:6px" class="sortable-table" cellpadding="5" cellspacing="1" width="915px">
                <tr class="title">
				<td colspan="<?php print(((isset($roster_rules)) ? sizeof($roster_rules)+1: 1)); ?>">Games Played By Position for the <?php print($lgyear); ?> season</td>
                </tr>
				<?php
					if (isset($roster_rules) && sizeof($roster_rules) > 0) { ?>
				<tr class="headline" class='hsc2_c' valign="top">
					<th>Player</th>
					<?php
					foreach($roster_rules as $ruleId => $ruleData) { ?>
						<th><?php print(get_pos($ruleId)); ?></th>
					<?php
					} // END foreach
					?>
				</tr>
				<?php
				if (isset($player_eligibility) && sizeof($player_eligibility) > 0) {
					$rowNum = 0;
					foreach($player_eligibility as $player_data) { ?>
					
				<tr class='hsc2_c' valign="top" style="background-color:<?php print((($rowNum % 2) == 0) ? '#fff' : '#E0E0E0'); ?>">
					<td class='hsc2_l'><?php print(anchor('/players/info/player_id/'.$player_data['player_id'].'/league_id/'.$league_id,$player_data['player_name'])); 
					$playerPos = -1;
					if ($player_data['position'] == 1) { 
						$playerPos = $player_data['role'];
					} else {
						$playerPos = $player_data['position'];
					}
					print(" ".get_pos($playerPos));
					?></td>
					<?php
					foreach($roster_rules as $ruleId => $ruleData) { 
						?>
					<td><?php 
					$posGames = 0;
					$style = '';
					if (isset($player_data[$ruleId])) {
						$posGames = $player_data[$ruleId];
					} // END if
					// EDIT 1.2 PROD, SET DISPLAY TO MATCH APPROPRIATE SEETIGNS BASED ON TIME
					$lvl = $min_game_current;
					if ($curr_period <= 1) {
						$lvl = $min_game_last;
					}
					if ($ruleId == $playerPos || $posGames > $lvl || $ruleId == 25) {
						$style = 'style="font-weight:bold;"';
					} // END if
					print ('<span '.$style.'>'.$posGames.'</span>');
					?>
					</td>
					<?php 
					} // END foreach
					?>
				</tr>
				<?php
					$rowNum++;
					} // END foreach
				} else { ?>
				<tr>
					<td>No player were found for this team.</td>
				</tr>
				<?php
					}
				} else { ?>
				<tr>
					<td>Position information could not be found for this league.</td>
				</tr>
				<?php
				} // END if
				?>
                </table>
            </div>  <!-- end batting stat div -->
        </div>