    <div id="subPage">
        <div class="top-bar"> <h1><?php echo $subTitle; ?></h1></div>
        <div id="content">
			<?php
			/*------------------------------------------------
            /
            /	BEGIN STATS TABLE
            /
            /-----------------------------------------------*/
            ?>
            <div class='textbox'>
                <table cellpadding="2" cellspacing="0" border="0" width="325">
                <tr class='title'>
                    <td>Projected Starters</td>
                </tr>
				<tr class='headline'>
                    <table width=100% cellpadding=2 cellspacing=1 border=0>
					<tr align=center class=bg4>
                        <td><strong>Team</strong></td>
                        <td><strong>Game 1</strong></td>
                        <td><strong>Game 2</strong></td>
                        <td><strong>Game 3</strong></td>
                        <td><strong>Game 4</strong></td>
                        <td><strong>Game 5</strong></td>
                        <td><strong>Game 6</strong></td>
                        <td><strong>Game 7</strong></td>
                    </tr>
                    <?php
                    	echo("<p><br />");
						if (isset($starters) && sizeof($starters) > 0) {
							$rowCount = 0;
							foreach($starters as $team_data) { 
								$bg = (($rowCount % 2) == 0) ? '#E0E0E0' : '#fff';
							?>
                       <tr height="17" bgcolor="<?php echo($bg); ?>" style="background:<?php echo($bg); ?>" align="center" valign="middle">

                        <td><?php echo('<a href="'.$config['ootp_html_report_path'].'teams/team_'.$team_data['team_id'].'.html">'.$team_data['name'].' '.$team_data['nickname'].'</a>'); ?></td>
                        <td><?php echo('<a href="'.$config['fantasy_web_root'].'players/info/player_id/'.$team_data['starter_0'].'/league_id/'.$league_id.'">'.$team_data['starter_0'].'</a>'); ?></td>
                        <td><?php echo($team_data['starter_1']); ?></td>
                        <td><?php echo($team_data['starter_2']); ?></td>
                        <td><?php echo($team_data['starter_3']); ?></td>
                        <td><?php echo($team_data['starter_4']); ?></td>
                        <td><?php echo($team_data['starter_5']); ?></td>
                        <td><?php echo($team_data['starter_6']); ?></td>
                    </tr>
					<?php 		
							$rowCount++;
							}
						} ?>

                    </table>
                	</td>
                </tr>
                </table>
            </div>
		</div>
    </div>
    <p /><br />