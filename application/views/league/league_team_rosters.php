    <div id="column-single">
	<?php include_once('admin_breadcrumb.php'); ?>
   		<h1><?php echo($subTitle); ?></h1>
            <!-- BEGIN MAIN COLUMN -->
			<?php if (isset($statusMessage) && !empty($statusMessage)) { ?>
			<div style="display:block;width:98%;position:relative; clear:right; float:left;">
			<?php
				if (strpos($statusMessage, "|") != false) {
					$statusMessageParts = explode("|",$statusMessage);
					$statusMessageType = $statusMessageParts[0];
					$message = $statusMessageParts[1];
				} else {
					$statusMessageType = "info";
					$message = $statusMessage;
				}
				echo('<span class="'.$statusMessageType.'">'.$message.'</span>');
			?>
			</div>
			<?php
			}
			?>
            <div class='textbox statusBox' style="width:900px">
                <table style="margin:6px;" cellpadding="5" cellspacing="0">
				<tr class='title'>
					<td colspan='6' class='lhl'>Teams and Rosters</td>
				</tr>
				<tr class='headline'>
					<td class='hsc2_c' width="32"></td>
					<td class='hsc2_c'>Team</td>
					<td class='hsc2_c'>Status</td>
					<td class='hsc2_c'>Issues</td>
					<td class='hsc2_c' width="50%">Details</td>
				</tr>
				<?php 
				if (isset($rosterStatus) && sizeof($rosterStatus > 0)) { 
					$rowcount = 0;
					foreach ($rosterStatus as $status) {
						
						if (($rowcount %2) == 0) { $color = "#EAEAEA"; } else { $color = "#FFFFFF"; } // END if
						?> 
						<tr style="background-color:<?php echo($color); ?>">
							<?php
							$teamData = $status['details'];
							if (isset($teamData['avatar']) && !empty($teamData['avatar'])) { 
								$avatar = PATH_TEAMS_AVATARS.$teamData['avatar'];
							} else {
								$avatar = PATH_TEAMS_AVATARS.DEFAULT_AVATAR;
							} // END if
							?>
							<td class='cell_m_np'><img src="<?php echo($avatar); ?>" width="32" height="32" /></td>
							<td class='cell_m'><?php echo(anchor("/team/info/".$status['team_id'],$teamData['teamname']." ".$teamData['teamnick'])); ?></td>
							<?php 
							if ($status['rosterValid'] == -1) {
								$class = "negative";
								$img = "icon_fail.png";
								$label = "FAIL";
							} else if ($status['rosterValid'] == 100) {
								$class = "message";
								$img = "icon_info.gif";
								$label = "SKIPPED";
							} else {
								$class = "positive";
								$img = "icon_pass.png";
								$label = "VALID";
							}
								echo('<td class="'.$class.'"><img src="'.PATH_IMAGES.'icons/'.$img.'" width="32" height="32" align="absmiddle" /> &nbsp; '.$label.'</td>'); 
							?>
							<td <?php if ($status['rosterValid'] == -1) { echo("class='".$class." cell_c'"); } else { echo('class="cell_c"'); } ?>><?php echo($status['issueCount']); ?></td>
							<td <?php if ($status['rosterValid'] == -1) echo("class='".$class."'"); ?>><?php echo($status['validationDetails']); ?></td>
						</tr>
						<?php
						$rowcount++;
					} // END foreach
				} else { ?>
						<tr>
							<td class="hsc2_l" colspan="4">No Teams were Found in this League</td>
						</tr>
						<?php 
				} // END if
						?>
                </tr>
                </table>
            </div>  <!-- end batting stat div -->
            <p><br />          
    </div>
    <p><br />