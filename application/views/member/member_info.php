    <div id="left-column">
   	<?php include_once('nav_members.php'); ?>
    </div>
    <div id="center-column">
   	<div id="subPage">
       	<div class="top-bar"><h1><?php echo($thisItem['username']); ?></h1></div>
       	<div id="content">
           	<!-- BEGIN RIGHT COLUMN -->
           	<div id="metaColumn">
               <div id="contentBox">
                    <div class="title">Registration Details</div>
                    <div id="row">
                   	<label>Access Level:</label>
                    <span><?php echo($thisItem['accessStr']); ?></span><br />
                    <label>Member Level:</label>
                    <span><?php echo($thisItem['levelStr']); ?></span><br />
                    <label>User Type:</label>
                    <span><?php echo($thisItem['typeStr']); ?></span><br />
                    <br />
                    <label>Registration Date:</label>
                    <span><?php echo($thisItem['dateCreated']);; ?></span><br />
                    <label>Lock Status:</label>
                    <span><?php echo($thisItem['locked']); ?></span><br />
                    <label>Activation Status:</label>
                    <span><?php echo($thisItem['active']); ?></span><br />
                    <?php 
					if ($thisItem['active_id'] != 1) {
						echo( anchor('/admin/activateUser/user_id/'.$thisItem['id'].'/returnPage/member_info_'.$thisItem['user_id'],'<img src="'.$config['fantasy_web_root'].'images/icons/accept.png" align="absmiddle" width="16" height="16" alt="Activate" title="Activate" /> Activate'));
					} else {
						echo( anchor('/admin/deactivateUser/user_id/'.$thisItem['user_id'].'/returnPage/member_info_'.$thisItem['user_id'],'<img src="'.$config['fantasy_web_root'].'images/icons/icon_fail_major.png" align="absmiddle" width="16" height="16" alt="Deactivate" title="Deactivate" /> Deactivate'));
					}
					?>
                    </div>
                </div>
           	</div>
               	<!-- BEGIN MAIN COLUMN -->
            <div id="detailColumn">
 				<b>E-Mail Address:</b>
                <span><?php echo($thisItem['email']); ?></span>
                <br /><br />
				<b>Last Updated:</b> <?php print(date('m/d/Y',strtotime($thisItem['dateModified']))); ?><br /> 

                <br clear="all" class="clear" />
                &nbsp;<p/>
				<br />     
                    &nbsp;<p/>
				<br />&nbsp;<p/>
				<br />   
                    <br />                
                  	<h3>Members Fantasy Teams</h3>
                   	<div class='textbox'>
                   	<?php  if (isset($thisItem['userTeams']) && sizeof($thisItem['userTeams']) > 0) {
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
                    <table style="margin:6px" class="sortable" cellpadding="5" cellspacing="0" border="0" width="560px">
                    <tr class='title'>
                    	<?php $cols = 3; ?>
                        <td colspan='<?php print($cols); ?>' class='lhl'><?php print((($type == 'rot') ? 'Rotisserie' : "Head to Head")." Leagues"); ?></td>
                    </tr>
                    <tr class='headline'>
                        <td class='hsc2_c' colspan="2">Team</td>
                        <td class='hsc2_c'>League</td>
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
						<img src="<?php echo(PATH_TEAMS_AVATARS.$avatar); ?>" width="24" height="24" border="0" align="absmiddle" />
						</td>
                        <td class='hsc2_r'><?php echo(anchor('/team/info/'.$data['id'],$data['teamname']." ".$data['teamnick'])); ?></td>
						<td class='hsc2_r'><?php echo(anchor('/league/info/'.$data['league_id'],$data['league_name'])); ?></td>
					</tr>
					<?php
						$rowcount++;
						} // END for
					} // END if (sizeof($userTeams)
					}
					?>
                    </table>
                    </div>
					<?php } else { ?>
					<table style="margin:6px" class="sortable" cellpadding="5" cellspacing="0" border="0" width="560px">
                    <tr class='title'>
                        <td colspan='8' class='lhl'>No Teams Found</td>
                    </tr>
					<tr>
						<td align="center">No teams were found.</td>
					</tr>
					</table>
					</div>
					<?php } ?>
            </div>
       		<div id="foot">

            </div>
        </div>
	</div>
