    
    <div id="column-single">
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
            
			?>
            <div class='textbox' style="width:435px">
                <table style="margin:6px; width:425px" cellpadding="5" cellspacing="0" border="0">
                <?php 
                $drawn = false;
				echo(form_open_multipart($config['fantasy_web_root']."league/requestTeam",array("id"=>"requestForm","name"=>"requestForm")));
				echo(form_fieldset());
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
                    <td class='hsc2_c'>Request</td>
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
                    <td class='hsc2_l'><img src="<?php echo($avatar); ?>" width="24" height="24" border="0" /></td>
                    <td class='hsc2_l'><?php echo($teamData['teamname']); ?></td>
                    <td class='hsc2_l'><?php echo($teamData['teamnick']); ?></td>
                    <td class='hsc2_l'><?php echo ($divisions['id']); ?></td>
                    <td class='hsc2_l' align='center'>
                    <input type="radio" name="team_id" value="<?php echo($teamId); ?>" />
                    </td>
                </tr>
                    <?php
                    $rowcount++;
					if (!$drawn) { $drawn = true; }
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
                    <td class='hsc2_c'>Request</td>
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
                    <td class='hsc2_l'><img src="<?php echo($avatar); ?>" width="24" height="24" border="0" /></td>
                    <td class='hsc2_l'><?php echo($teamData['teamname']); ?></td>
                    <td class='hsc2_l'><?php echo($teamData['teamnick']); ?></td>
                    <td class='hsc2_l' align='center'>
                    <input type="radio" name="team_id" value="<?php echo($teamId); ?>" />
                    </td>
                </tr>
                    <?php
                    $rowcount++;
					if (!$drawn) { $drawn = true; }
                    } // END foreach
                } else { ?>
                <tr>
                    <td class="hsc2_l" colspan="4">No Teams were Found</td>
                </tr>
                <?php 
				}  // END if
                	
				} // END if isset($divisions) 
				
				
				if ($drawn) {
				echo(form_fieldset_close());
				?>
                <tr>
                    <td class="hsc2_l" colspan="4">
				<?php 	
                
				echo(form_fieldset('',array('class'=>"button_bar")));
				echo(form_submit('submit',"Request"));
				echo(form_hidden('id',$league_id));
				echo(form_hidden('submitted',"1"));
				echo(form_fieldset_close());
				?>
                	</td>
                </tr>
                <?php
                }
				echo(form_close()); ?>
                </table>
            </div>  <!-- end batting stat div -->
            <p /><br />          
    </div>
    <p /><br />