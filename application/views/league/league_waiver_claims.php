    <div id="column-single">
   	<?php include_once('admin_breadcrumb.php'); ?>
    <h1><?php echo($subTitle); ?></h1>
        <div class="content-form">
            <p style="text-align:left;" />
            Waiver Claims currently pending processing for this league.
            
            <div class='tablebox'>
            <table cellspacing="0" cellpadding="3" width="625">
            <tr class='title'><td colspan="4">Pending Waiver Claims</td></tr>
            <tr class='headline'>
                <td width="40%">Team</td>
                <td width="30%">Players</td>
                <td align="center" width="20%">Period</td>
                <td align="center" width="10%">Tools</td>
            </tr>
            <?php 
            if (isset($thisItem['claims']) && sizeof($thisItem['claims']) > 0) {
               $rowCount = 0;
			   foreach ($thisItem['claims'] as $row) {
				$cls="s".($rowCount%2);
				?>
 				<tr class="<?php echo($cls); ?>" style="text-align:left;">
					<td><?php echo(anchor('/team/info/'.$row['team_id'],$row['teamname'])); ?></td>
                    <td><?php echo(anchor('/players/info/league_id/'.$league_id.'/player_id/'.$row['player_id'],$row['player_name'])); ?></td>
                    <td align="center"><?php echo($row['waiver_period']); ?></td>
                    <td align="center" class="last" nowrap="nowrap">
					<?php 
                     echo( anchor('/league/removeClaim/league_id/'.$league_id.'/claim_id/'.$row['id'],'<img src="'.$config['fantasy_web_root'].'images/icons/hr.gif" width="16" height="16" align+absmiddle" alt="Deny Claim" title="Deny Claim" /> Deny Claim')); ?></td>
    			</tr>
				<?php 
				$rowCount++;
				} 
			} else { ?>
            <tr class='s1_1' align="center">
                <td colspan="4">No pending claims were found.</td>
            </tr>
            <?php } ?>
            </table> 
            </div>      
        </div>
        <p /><br />
    </div>
    <p /><br />