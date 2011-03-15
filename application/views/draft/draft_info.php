<script type="text/javascript" charset="UTF-8">
	
	$(document).ready(function(){
		$('a[rel=makePick]').live('click',function () {					   
			var params = this.id.split("|");
			$('input#league_id').val(params[0]);
			$('input#pick_id').val(params[2]);
			$('input#team_id').val(params[2]);
			$('div#manualForm').css('display','block');
			return false;
		});				   
	});
    </script>
    <div id="manualForm" class="dialog">
		<form method='post' action="<?php echo($config['fantasy_web_root']); ?>draft/processDraft" name='manpick' id="manpick">
        <input type='hidden' id="action" name='action' value='manualpick'></input>
        <input type='hidden' id="team_id" name='team_id' value=''></input>
        <input type='hidden' id="pick_id" name='pick_id' value=''></input>
        <input type='hidden' id="league_id" name='league_id' value=''></input>
        <div class='textbox'>
         <table cellpadding=2 cellspacing=0 cellborder=0>
          <tr class='title'><td colspan=3>Enter Manual Draft Pick</td></tr>
          <tr>
           <td><label for='selection'>Player ID:</label></td>
           <td><input type='text' id="Player_id" name='pick' value=''></input></td>
           <td><input type='submit' class="button" value='Draft Player'></input></td>
          </tr>
         </table>
        </div>
        </form>
		
	
	
	</div>
	
    <div id="center-column">
   	<div id="subPage">
    	<?php 
		// EDIT 1.0.2
		// ADMIN MESSAGING FOR DRAFT CONTROLS
		$message = "";
		$messageType = "info";
		if ($accessLevel == ACCESS_ADMINISTRATE || $thisItem['isCommish']) {
			if ($thisItem['draftStatus'] < 2  || time() < strtotime($thisItem['draftDate'])) {
				$message = "<b>NOTE</b>: Your draft has not started yet. More controls will be available once the draft date and time are reached.";
			} else if ($thisItem['draftStatus'] == 4) {
				$message = "Your draft is complete! Return to the ".anchor('/league/admin/'.$thisItem['league_id'],'legaue admin screen')." to finalize your draft and set your league rosters in place!.";
				$messageType = "notice";
			}
		}
		if (!empty($message)) {
			echo '<div class="'.$messageType .'">'.$message.'</div>';
		}
		?>
       	<div class="top-bar"><h1>Draft Results</h1></div>
       	<div id="content">
		<?php $cols = 6; 
		$width='';
		if (($accessLevel == ACCESS_ADMINISTRATE || $thisItem['isCommish']) && $thisItem['draftStatus'] < 4) {
			$width=' style="width:800px;"';
		} ?>
		<div class='tablebox'<?php echo($width); ?>>
        <table cellspacing="0" cellpadding="3"<?php echo($width); ?>>
        <tr class='title'><td colspan="8">Draft Results</td></tr>
        <tr class='headline'>
            <td class='hsc2'>Rnd</td>
            <td class='hsc2'>Pick</td>
            <td class='hsc2'>Ovr</td>
            <td class='hsc2_l'>Team</td>
            <td class='hsc2'>Pos</td>
            <td class='hsc2_l'>Player</td>
            <?php
            if ($thisItem['draftStatus'] < 4 && ($accessLevel == ACCESS_ADMINISTRATE || $thisItem['isCommish'])) { ?>
            <td class='hsc2'>Action</td><td class='hsc2' nowrap="nowrap">Picked At</td>
            <?php
            $cols=8;
            } ?>
        </tr>
        <?php 
		
		
		if (isset($thisItem['draftResults']) && sizeof($thisItem['draftResults']) > 0) {
			$first = '';
			$pidList = "";
			$prevRnd=0;
			$tzone=date("T");
			$totCnt=1;
			$thisItem['teamList'] = $thisItem['teamList'];
			foreach ($thisItem['draftResults'] as $row) {
				$tid=$row['team_id'];
				$round=$row['round'];
				$pid=$row['player_id'];
				$pick=$row['pick_overall'];
				$dueDt=$row['due_date'];
				$dueTm=$row['due_time'];
				$dueText=$dueDt." ".$dueTm;
				
				if (($pid==-999)&&($thisItem['isCommish']!=1)) {continue;}

				if ($round!=$prevRnd) { ?>
                <tr class='title'><td colspan="<?php echo($cols); ?>">Round <?php echo($round); ?></td></tr>
                <?php $pcnt=0;
				} 
				$cls="s".($pcnt%2+1);
				?>
 				<tr class="<?php echo($cls); ?>">
					<td><?php echo($round); ?></td>
                    <td><?php echo($row['pick_round']); ?></td>
					<?php if ($pid == -999) { ?>
                    <td>&nbsp;</td>
                    <?php } else { ?>
                    <td><a name="<?php echo($totCnt); ?>>"><?php echo($totCnt); ?></a></td>
                    <?php } ?>
					<td class="<?php echo($cls); ?>_l"><?php echo($thisItem['teamList'][$tid]['teamname']." ".$thisItem['teamList'][$tid]['teamnick']); ?></a></td>
					<?php
                    if ($pid=="") {
                        if ($thisItem['timerEnable']==1) {
                            echo "<td colspan=2>Pick is due no later than $dueText $tzone</td>";
                        } else {
                            echo "<td colspan=2>&nbsp;</td>";
                        }
                    } elseif ($pid ==-999) {
						echo "<td align='center' colspan='2'>----- Skipped -----</td>";
					} else { ?>
                    	
						<td><?php echo(get_pos($thisItem['playersInfo'][$pid]['position'])); ?></td>
						<td class="<?php echo($cls); ?>_l"><?php echo anchor('/players/info/league_id/'.$thisItem['league_id'].'/player_id/'.$pid, $thisItem['playersInfo'][$pid]['first_name']." ".$thisItem['playersInfo'][$pid]['last_name']); ?></a></td>
					<?php }
					if ($accessLevel == ACCESS_ADMINISTRATE || $thisItem['isCommish']) { 
					 if ($thisItem['draftStatus'] > 0 && $thisItem['draftStatus'] < 4 && time() > strtotime($thisItem['draftDate'])) {
            		if ($pid=="") {
							if ($first=="") { ?>
						<td><?php echo anchor('/draft/processDraft/league_id/'.$thisItem['league_id'].'/action/auto/pick_id/'.$pick.'/team_id/'.$tid,'Auto Pick'); ?>
						/<?php echo anchor('#','Manual Pick',array('rel'=>'makePick','id'=>$thisItem['league_id'].'|'.$pick.'|'.$tid)); ?></td>
							<?php $first=1;
							} else { ?>
                        <td><?php echo anchor('/draft/processDraft/league_id/'.$thisItem['league_id'].'/action/skip/pick_id/'.$pick.'/team_id/'.$tid,'Skip Pick'); ?>
                        <?php } ?>
						<td>&nbsp;</td>
                    <?php }  else {
						if ($pid!=-999) { ?>
                        <td>
                        <?php echo anchor('/draft/processDraft/league_id/'.$thisItem['league_id'].'/action/clear/pick_id/'.$pick,'Clear'); ?>/
                        <?php echo anchor('/draft/processDraft/league_id/'.$thisItem['league_id'].'/action/edit/pick_id/'.$pick,'Edit'); ?>/
                        <?php echo anchor('/draft/processDraft/league_id/'.$thisItem['league_id'].'/action/rollback/pick_id/'.$pick,'Rollback'); ?></td>
                        <td><?php echo $dueText; ?></td>
						<?php } else { ?>
						<td><?php echo anchor('/draft/processDraft/league_id/'.$thisItem['league_id'].'/action/clear/pick_id/'.$pick,'Restore'); ?></td>
						<td>&nbsp;</td>
						<?php  
						}
					}      
				} else {
					echo("<td></td><td></td>");
				}
					}
					
				?>
				</tr>
<?php
			$prevRnd=$round;
			$pcnt++;
			if ($pid!=-999) {$totCnt++;}
			}
		}
		?>

            	</table>
                </div>
            </div>
        </div>
	</div>
