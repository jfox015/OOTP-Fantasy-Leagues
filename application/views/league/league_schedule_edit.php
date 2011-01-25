    
    <div id="column-single">
   	<?php include_once('admin_breadcrumb.php'); ?>
    <h1><?php echo($subTitle); ?></h1>
            <?php 
			if ( ! function_exists('form_open')) {
				$this->load>helper('form');
			}
			$errors = validation_errors();
			if ($errors) {
				echo '<span class="error">The following errors were found with your submission:<br/ ><b>'.$errors.'</b></span><p />';
			}
			if (isset($message) && !empty($message)) {
				echo '<span><b>'.$message.'</b><p />';
			}
			?>
            <!-- BEGIN MAIN COLUMN -->
            <?php 
            echo(form_open_multipart($config['fantasy_web_root']."league/scheduleEdit",array("id"=>"detailsForm","name"=>"detailsForm")));
			echo(form_fieldset());
			?>
            <div class='textbox'>
                <table style="margin:2px; width:525px" cellpadding="2" cellspacing="0" border="0">
	            <tr class='title'>
	                <td style='padding:6px' colspan="3">Games for Scoring Period <?php echo($period_id); ?></td>
	            </tr>
				<tr class='headline'>
	                <td>Game ID</td>
					<td>Home Team</td>
					<td>Away Team</td>
	            </tr>
             <?php 
                $rowcount = 0;
				if (isset($gameIds) && sizeof($gameIds) > 0) { 
				foreach($gameIds as $game_id) { 
				if (strpos($game_id,"n_") === false) {
					if (($rowcount %2) == 0) { $color = "#EAEAEA"; } else { $color = "#FFFFFF"; }?>
					<tr style="background-color:<?php echo($color); ?>">
						<td class='hsc2_l' width="20%"><?php echo($game_id); ?><input type="hidden" name="game_id" value="<?php echo($game_id); ?>" /> </td>
						<td class='hsc2_l' width="40%"><?php echo form_dropdown($game_id."_home", $teamList, ($input->post($game_id."_home")) ? $input->post($game_id."_home") : (isset($gameList[$game_id]) ? $gameList[$game_id]['home_team_id'] : "-1")); ?></td>
						<td class='hsc2_l' width="40%"><?php echo form_dropdown($game_id."_away", $teamList, ($input->post($game_id."_away")) ? $input->post($game_id."_away") : (isset($gameList[$game_id]) ? $gameList[$game_id]['away_team_id'] : "-1")); ?></td>
						
					</tr>
						<?php
						$rowcount++;
					}
                } 
			}
			if ($rowcount < $max_games) {
				for ($i = $rowcount+1; $i < $max_games; $i++) { 
					if (($rowcount %2) == 0) { $color = "#EAEAEA"; } else { $color = "#FFFFFF"; }?>
                <tr style="background-color:<?php echo($color); ?>">
                    <td class='hsc2_l' width="20%">TBD<input type="hidden" name="game_id" value="-1" /> </td>
					<td class='hsc2_l' width="40%"><?php echo form_dropdown("n_".$i."_home", $teamList, ($input->post("n_".$i."_home")) ? $input->post("n_".$i."_home") : "-1"); ?></td>
                    <td class='hsc2_l' width="40%"><?php echo form_dropdown("n_".$i."_away", $teamList, ($input->post("n_".$i."_away")) ? $input->post("n_".$i."_away") : "-1"); ?></td>
                </tr>
				<?php 
				$rowcount++;
				}
			}
			?>
		</table>
        </div>
		<?php 
			echo(form_fieldset_close());
			echo(form_fieldset('',array('class'=>"button_bar")));
			echo(form_submit('submit',"Submit"));
			echo(form_hidden('league_id',$league_id));
			echo(form_hidden('period_id',$period_id));
			echo(form_hidden('submitted',"1"));
			echo(form_fieldset_close());
			echo(form_close()); ?>
            <p /><br />          
    </div>
    <p /><br />