    
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
            echo(form_open_multipart($config['fantasy_web_root']."league/teamAdmin",array("id"=>"detailsForm","name"=>"detailsForm")));
			echo(form_fieldset());
			?>
            <div class='textbox'>
                <table cellpadding="5" cellspacing="0">
                <?php 
                $division_options = array();
				if (isset($thisItem['divisions']) && sizeof($thisItem['divisions']) > 0) { 
					foreach($thisItem['divisions'] as $id=>$divisionData) { 
						$division_options = $division_options + array($id=>$divisionData['division_name']);
					}
				foreach($thisItem['divisions'] as $id=>$divisionData) { 
				
				?>
                <tr class='title'>
                    <td colspan='6' class='lhl'><?php echo($divisionData['division_name']); ?></td></tr>
                <tr class='headline'>
                    <td class='hsc2_c'>&nbsp;</td>
                    <td class='hsc2_c'>Team Name</td>
                    <td class='hsc2_c'>Team Nick</td>
                    <td class='hsc2_c'>Division</td>
                    <td class='hsc2_c'>Owner</td>
                    <td class='hsc2_c'>Tools</td>
                </tr>
                <?php
				$teamcount = 0;
                $rowcount = 0;
                if (isset($divisionData['teams']) && sizeof($divisionData['teams']) > 0) { 
                    foreach($divisionData['teams'] as $teamId => $teamData) { 
                    if (($rowcount %2) == 0) { $color = "#EAEAEA"; } else { $color = "#FFFFFF"; } 
                    // END if
					?> 
                <tr style="background-color:<?php echo($color); ?>">
                    <?php
                    if (isset($teamData['avatar']) && !empty($teamData['avatar'])) { 
                        $avatar = PATH_TEAMS_AVATARS.$teamData['avatar'];
                    } else {
                        $avatar = PATH_TEAMS_AVATARS.DEFAULT_AVATAR;
                    } // END if
                    ?>
                    <td class='hsc2_l'><img src="<?php echo($avatar); ?>" width="24" height="24"\ /></td>
                    <td class='hsc2_l'><?php echo(form_input(array('name'=>$teamId."_teamname",'id'=>"team".$teamId."_teamname",'value'=>($input->post($teamId."_teamname")) ? $input->post($teamId."_teamname") : $teamData['teamname']))); ?></td>
                    <td class='hsc2_l'><?php echo(form_input(array('name'=>$teamId."_teamnick",'id'=>"team".$teamId."_teamnick",'value'=>($input->post($teamId."_teamnick")) ? $input->post($teamId."_teamnick") : $teamData['teamnick']))); ?></td>
                    <td class='hsc2_l'><?php echo form_dropdown($teamId."_division_id", $division_options, ($input->post($teamId."_division_id")) ? $input->post($teamId."_division_id") : $id); ?></td>
                    <td class='hsc2_l'><?php 
                    if (isset($teamData['owner_id']) && $teamData['owner_id'] != -1) {
                        echo(anchor('/user/profiles/'.$teamData['owner_id'], $teamData['owner_name'])); 
                    } else {
                        echo($teamData['owner_name']); 
                    }
                    ?></td>
                    <td class='hsc2_c' nowrap="nowrap">
                    <?php
					//echo("Owner id = ".$teamData['owner_id']."<br />");
					if (isset($teamData['owner_id']) && $teamData['owner_id'] > 0) {
						echo('<a href="javascript:removeOwner('.$teamId.')"><img src="'.$config['fantasy_web_root'].'images/icons/img_icon_garbage.gif" align="absmiddle" width="16" height="16" alt="Remove" title="Remove" /></a>');
						echo('&nbsp;');
						if ($accessLevel == ACCESS_ADMINISTRATE || $currUser == $commish_id) {
							if ($teamData['owner_id'] != $commish_id) { 
								echo('<a href="javascript:changeCommisioner('.$teamData['owner_id'].')"><img src="'.$config['fantasy_web_root'].'images/icons/stock_new-meeting.png" align="absmiddle" width="16" height="16" alt="Set as Commissioner" title="Set as Commissioner" /></a>');
							} // END if
						} // END if
					} else { 
                        $inviteFound = false;
                        $requestFound = false;
                        if (isset($invites) && sizeof($invites) > 0 ){
                            foreach($invites as $invite) {
                                if ($invite['team_id'] == $teamId) {
                                    $inviteFound = true;
                                    break;
                                } // END if
                            } // END foreach
                        } // END if
                        if (isset($requests) && sizeof($requests) > 0 ){
                            foreach($requests as $request) {
                                if ($request['team_id'] == $teamId) {
                                    $requestFound = true;
                                    break;
                                } // END if
                            } // END foreach
                        } // END if
                        if ($requestFound) {
                            echo(anchor('/league/leagueInvites/'.$league_id,'Request Pending'));
                        } else if ($inviteFound) {
                            echo(anchor('/league/leagueInvites/'.$league_id,'Invite Pending'));
                        } else {
                            echo(anchor('league/inviteOwner/id/'.$league_id.'/team_id/'.$teamId,'<img src="'.$config['fantasy_web_root'].'images/icons/stock_mail.png" align="absmiddle" width="16" height="16" alt="Invite Owner" title="Invite Owner" /> Invite Owner'));
                        } // END if
					} // END if
					?>
                    </td>
                </tr>
                    <?php
                    $rowcount++;
                    } // END foreach
                } else { ?>
                <tr>
                    <td class="hsc2_l" colspan="4">No Teams were Found</td>
                </tr>
                <?php 
				} // END if
				} // END foreach
				} else {
				?>
				<tr class='title'>
                    <td colspan='6' class='lhl'>Team List</td></tr>
                <tr class='headline'>
                    <td class='hsc2_c'>&nbsp;</td>
                    <td class='hsc2_c'>Team Name</td>
                    <td class='hsc2_c'>Team Nick</td>
                    <td class='hsc2_c'>Owner</td>
                    <td class='hsc2_c'>Tools</td>
                </tr>
                <?php
				$teamcount = 0;
                $rowcount = 0;
                if (isset($thisItem['teams']) && sizeof($thisItem['teams']) > 0) { 
                    foreach($thisItem['teams'] as $teamId => $teamData) { 
                    if (($rowcount %2) == 0) { $color = "#EAEAEA"; } else { $color = "#FFFFFF"; } 
                    ?>
                <tr style="background-color:<?php echo($color); ?>">
                    <?php
                    if (isset($teamData['avatar']) && !empty($teamData['avatar'])) { 
                        $avatar = PATH_TEAMS_AVATARS.$teamData['avatar'];
                    } else {
                        $avatar = PATH_TEAMS_AVATARS.DEFAULT_AVATAR;
                    } // END if
                    ?>
                    <td class='hsc2_l'><img src="<?php echo($avatar); ?>" width="24" height="24" /></td>
                    <td class='hsc2_l'><?php echo(form_input(array('name'=>$teamId."_teamname",'id'=>"team".$teamId."_teamname",'value'=>($input->post($teamId."_teamname")) ? $input->post($teamId."_teamname") : $teamData['teamname']))); ?></td>
                    <td class='hsc2_l'><?php echo(form_input(array('name'=>$teamId."_teamnick",'id'=>"team".$teamId."_teamnick",'value'=>($input->post($teamId."_teamnick")) ? $input->post($teamId."_teamnick") : $teamData['teamnick']))); ?></td>
                    <td class='hsc2_l'><?php 
                    if (isset($teamData['owner_id']) && $teamData['owner_id'] != -1) {
                        echo(anchor('/user/profiles/'.$teamData['owner_id'], $teamData['owner_name'])); 
                    } else {
                        echo($teamData['owner_name']); 
                    }
                    ?></td>
                    <td class='hsc2_c' nowrap="nowrap">
                    <?php
					//ho("Owner id = ".$teamData['owner_id']."<br />");
					if (isset($teamData['owner_id']) && $teamData['owner_id'] != -1) {
						echo('<a href="javascript:removeOwner('.$teamId.')"><img src="'.$config['fantasy_web_root'].'images/icons/img_icon_garbage.gif" align="absmiddle" width="16" height="16" alt="Remove" title="Remove" /></a>');
						echo('&nbsp;');
						if ($accessLevel == ACCESS_ADMINISTRATE || $currUser == $commish_id) {
							if ($teamData['owner_id'] != $commish_id) { 
								echo('<a href="javascript:changeCommisioner('.$teamData['owner_id'].')"><img src="'.$config['fantasy_web_root'].'images/icons/stock_new-meeting.png" align="absmiddle" width="16" height="16" alt="Set as Commissioner" title="Set as Commissioner" /></a>');
							} // END if
						} // END if
					} else { 
					    $inviteFound = false;
                        $requestFound = false;
                        if (isset($invites) && sizeof($invites) > 0 ){
                            foreach($invites as $invite) {
                                if ($invite['team_id'] == $teamId) {
                                    $inviteFound = true;
                                    break;
                                } // END if
                            } // END foreach
                        } // END if
                        if (isset($requests) && sizeof($requests) > 0 ){
                            foreach($requests as $request) {
                                if ($request['team_id'] == $teamId) {
                                    $requestFound = true;
                                    break;
                                } // END if
                            } // END foreach
                        } // END if
                        if ($requestFound) {
                            echo(anchor('/league/leagueInvites/'.$league_id,'Request Pending'));
                        } else if ($inviteFound) {
                            echo(anchor('/league/leagueInvites/'.$league_id,'Invite Pending'));
                        } else {
                            echo(anchor('league/inviteOwner/id/'.$league_id.'/team_id/'.$teamId,'<img src="'.$config['fantasy_web_root'].'images/icons/stock_mail.png" align="absmiddle" width="16" height="16" alt="Invite Owner" title="Invite Owner" /> Invite Owner'));
                        } // END if
					} // END if
					?>
                    </td>
                </tr>
                    <?php
                    $rowcount++;
                    } // END foreach
                } else { ?>
                <tr>
                    <td class="hsc2_l" colspan="4">No Teams were Found</td>
                </tr>
                <?php 
				}  // END if
                	
				} // END if isset($divisions) 
                ?>
                </table>
            </div>  <!-- end batting stat div -->
            <?php
			echo(form_fieldset_close());
			echo(form_fieldset('',array('class'=>"button_bar")));
			echo(form_submit('submit',"Submit"));
			echo(form_hidden('id',$league_id));
			echo(form_hidden('submitted',"1"));
			echo(form_fieldset_close());
			echo(form_close()); ?>
            <p><br />          
    </div>
    <p><br />
    <script type="text/javascript" encoding="UTF-8">
	function removeOwner(teamId) {
		if (confirm('Are you sure you wish to remove this owner from this team? This action CANNOT be undone.')) {
			document.location.href=" <?php echo($config['fantasy_web_root'].'/league/removeOwner/id/'.$league_id.'/team_id/"+teamId+"'); ?>";
		}
	}
	function changeCommisioner(ownerId) {
		if (confirm('Are you sure you wish to assign a different user as the league commisioner? This will remove your rights to manage this league! This action CANNOT be undone.')) {
			document.location.href=" <?php echo($config['fantasy_web_root'].'/league/changeCommissioner/id/'.$league_id.'/owner_id/"+ownerId+"'); ?>";
		}
	}
	</script>