    <script type="text/javascript">
    $(document).ready(function(){		   
		$('#deleteConfirm').click(function(){
			$('#confirmForm').submit();
		});	
		$('#deleteCancel').click(function(){
			history.back(-1);
		});
	});
    </script>
    
    <div id="center-column">
   	<div class="textual_content">
            <?php include_once('admin_breadcrumb.php'); ?>
    		<div class="top-bar"><h1><?php echo($subTitle); ?></h1></div>
            <p /><br />
			<div class='textbox'>
				<table style="margin:6px" class="sortable" cellpadding="5" cellspacing="0" border="0" width="565px">
				<tr class='title'>
                	<td colspan="5" style="padding:3px;">Owner Draft Settings</td>
                </tr>
                <tr class='headline'>
					<td class='hsc2_c' width="10%">&nbsp;</td>
					<td class='hsc2_c' width="20%">Team</td>
                    <td class='hsc2_c' width="25%">Owner</td>
					<td class='hsc2_c' width="20%">Auto Draft</td>
                    <td class='hsc2_c' width="15%">Auto List</td>
				</tr>
				<?php 
				$teamList = "";
				if (isset($thisItem['divisions']) && sizeof($thisItem['divisions']) > 0) { 
				foreach($thisItem['divisions'] as $id=>$divisionData) { ?>
				<?php 
				$rowcount = 0;
				if (isset($divisionData['teams']) && sizeof($divisionData['teams']) > 0) { 
					asort($divisionData['teams']);
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
					<td class='hsc2_l'><?php if(isset($teamData['owner_id']) && isset($teamData['owner_name'])) {echo(anchor('/user/profile/'.$teamData['owner_id'],$teamData['owner_name'])); } ?></td>
					<?php
					$auto = "OFF";
					$autoList = "OFF";
					if ($teamData['auto_draft'] == 1) {
						$auto='ON';
						$autoRnd=$teamData['auto_round_x'];
						if ($teamData['auto_round_x'] > 0) {
							$auto.=" (after round ".$teamData['auto_round_x'].")";
						}
						$auto.=' (<a href="'.$config['fantasy_web_root'].'draft/processDraft/league_id/'.$league_id.'/action/auto_off/team_id/'.$teamId.'">disable</a>)';
					}
					if ($teamData['auto_list'] == 1) {
						$autoList ='ON (<a href="'.$config['fantasy_web_root'].'draft/processDraft/league_id/'.$league_id.'/action/auto_list_off/team_id/'.$teamId.'">disable</a>)';
					}
					if (!empty($teamList)) { $teamList .= " "; }
					$teamList .= $teamId;
                    ?>
                    <td class='hsc2_l'><?php echo($auto); ?></td>
					<td class='hsc2_l'><?php echo($autoList); ?></td>
				</tr>
					<?php
					$rowcount++;
					}
				} else { ?>
				<tr>
					<td class="hsc2_l" colspan="5">No Teams were Found</td>
				</tr>
				<?php } ?>

				
				<?php } // END foreach($divisions)
				} 
				$teamList = trim($teamList," ");
				?>
                <tr class='headline'>
                	<td class='hsc2' colspan="2">&nbsp;</td>
                    <td class='hsc2' colspan="2" nowrap="nowrap">(<a href="<?php echo($config['fantasy_web_root']); ?>draft/processDraft/league_id/<?php echo($league_id); ?>/action/auto_on_all">enable all</a>/<a href="<?php echo($config['fantasy_web_root']); ?>draft/processDraft/league_id/<?php echo($league_id); ?>/action/auto_off_all">disable all</a>)</td>
                    <td class='hsc2' nowrap="nowrap">(<a href="<?php echo($config['fantasy_web_root']); ?>draft/processDraft/league_id/<?php echo($league_id); ?>/action/auto_list_off_all">disable all</a>)</td>
				</tr>
				</table>
			</div>  <!-- end batting stat div -->

			
            <p /><br />&nbsp;<br />
        </div>
	</div>