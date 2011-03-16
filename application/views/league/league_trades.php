    <div id="column-single">
   	<?php include_once('admin_breadcrumb.php'); ?>
    <h1><?php echo($subTitle); ?></h1>
        <div class="content-form">
            <p style="text-align:left;" />
            <?php if (isset($type) && $type == 2) { ?>
            Complete Trade history for this league. View <?php print(anchor('/league/tradeReview/league_id/'.$league_id.'/type/1','trades currently pending processing')); ?> for this league.
            <?php } else { ?>
            Trades currently pending processing for this league. View <?php print(anchor('/league/tradeReview/league_id/'.$league_id.'/type/2','complete trade history')); ?> for this league.
            <?php } ?>
            <div class='tablebox'>
            <table cellspacing="0" cellpadding="3" width="900px">
            <tr class='title'><td colspan="9">Trade Details</td></tr>
            <tr class='headline'>
                <td width="8%">Date</td>
                <td width="10%">From</td>
                <td width="10%">To</td>
                <td width="15%">Offered</td>
                <td width="15%">Requested</td>
                <td align="center" width="8%">Effective</td>
                <td align="center" width="8%">Protests</td>
                <td align="center" width="20%">Status</td>
                <td align="center" width="8%">Tools</td>
            </tr>
            <?php 
            if (isset($thisItem['trades']) && sizeof($thisItem['trades']) > 0) {
               $rowCount = 0;
			   foreach ($thisItem['trades'] as $row) {
				$cls="s".($rowCount%2);
				?>
 				<tr class="<?php echo($cls); ?>" style="text-align:left;">
					<td><?php print(date('m/d/Y',strtotime($row['offer_date']))); ?></td>
                    <td><?php print(anchor('/team/info/'.$row['team_1_id'],$row['team_1_name'])); ?></td>
                    <td><?php print(anchor('/team/info/'.$row['team_2_id'],$row['team_3_name'])); ?></td>
                    <td><?php print($row['send_players']); ?></td>
                    <td><?php print($row['receive_players']); ?></td>
                    <td align="center"><?php echo($row['in_period']); ?></td>
                    <td align="center"><?php echo($row['protest_count']); ?></td>
                    <td align="center"><?php echo($row['status']); ?></td>
                    <td align="center" class="last" nowrap="nowrap">
					<?php 
                     echo( anchor('/team/tradeReview/league_id/'.$league_id.'/team_id/'.$row['id'].'/trans_type/5','<img src="'.$config['fantasy_web_root'].'images/icons/search.png" width="16" height="16" alt="Review" border="0" title="Review" />')); ?></td>
    			</tr>
				<?php 
				$rowCount++;
				} 
			} else { ?>
            <tr class='s1_1' align="center">
                <td colspan="9">No pending trades were found.</td>
            </tr>
            <?php } ?>
            </table> 
            </div>      
        </div>
        <p /><br />
    </div>
    <p /><br />