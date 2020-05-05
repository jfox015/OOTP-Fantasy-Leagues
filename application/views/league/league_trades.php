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
            <div class='textbox'>
            <table cellspacing="0" cellpadding="3">
            <tr class='title'><td colspan="9">Trade Details</td></tr>
            <tr class='headline'>
                <td width="8%">Date</td>
                <td width="15%">From</td>
                <td width="15%">To</td>
                <td width="18%">Offered</td>
                <td width="17%">Requested</td>
                <td class='hsc2_c' width="5%">Effective</td>
                <td class='hsc2_c' width="5%">Protests</td>
                <td class='hsc2_c' width="10%">Status</td>
                <td class='hsc2_c' width="8%">Tools</td>
            </tr>
            <?php
                //print("Size of trades = ".sizeof($thisItem['trades'])."<br />");
            if (isset($trades) && sizeof($trades) > 0) {
                $rowcount = 0;
			   foreach ($trades as $row) {
                if (($rowcount %2) == 0) { $color = "#EAEAEA"; } else { $color = "#FFFFFF"; }
                ?>
                <tr style="background-color:<?php echo($color); ?>">
					<td><?php print(date('m/d/Y',strtotime($row['offer_date']))); ?></td>
                    <td><?php print(anchor('/team/info/'.$row['team_1_id'],$row['team_1_name'])); ?></td>
                    <td><?php print(anchor('/team/info/'.$row['team_2_id'],$row['team_2_name'])); ?></td>
                    <td><?php //print($row['send_players']);
                    if (isset($row['send_players']) && sizeof($row['send_players']) > 0) {
                        $numDrawn = 0;
                        foreach ($row['send_players'] as $playerInfo) {
                            if ($numDrawn != 0 && $numDrawn != sizeof($row['send_players'])) { echo("<br />"); }
                            echo($playerInfo);
                            $numDrawn++;
                        } // END foreach
                    } // END if
                    ?>
                    </td>
                    <td><?php //print($row['receive_players']);

                        if (isset($row['receive_players']) && sizeof($row['receive_players']) > 0) {
                        $numDrawn = 0;
                        foreach ($row['receive_players'] as $playerInfo) {
                            if ($numDrawn != 0 && $numDrawn != sizeof($row['receive_players'])) { echo("<br />"); }
                            echo($playerInfo);
                            $numDrawn++;
                        } // END foreach
                    } // END if
                    ?></td>
                    <td class='hsc2_c'><?php echo($row['in_period']); ?></td>
                    <td class='hsc2_c'><?php echo($row['protest_count']); ?></td>
                    <?php
					switch ($row['tradeStatus']) {
						case 'Offered':
						case 'Accepted':
						case 'Completed':
							$class = 'positive';
							break;
						case 'Rejected by Owner':
						case 'Rejected by League':
						case 'Rejected by Commissioner':
						case 'Rejected by Admin':
						case 'Rejected with Counter':
						case 'Invalid Trade':
							$class = 'negative';
							break;
						case 'Removed':
						case 'Retracted':
							$class = 'warning';
							break;
						case 'Pending League Approval':
						case 'Pending Commissioner Approval':
							$class = 'alert';
							break;
						default:
							$class = 'message';
					}
					?>
					<td class="hsc2_c <?php echo($class); ?>"><?php print($row['tradeStatus']); ?></td>
                    <td class='hsc2_c' class="last" nowrap="nowrap">
					<?php 
                     echo( anchor('/team/tradeReview/league_id/'.$league_id.'/team_id/'.$row['team_1_id'].'/trade_id/'.$row['trade_id'].'/trans_type/5','<img src="'.$config['fantasy_web_root'].'images/icons/search.png" width="16" height="16" alt="Review" border="0" title="Review" />')); ?></td>
    			</tr>
				<?php 
				$rowcount++;
				} 
			} else { ?>
            <tr class='s1_1' class='hsc2_c'>
                <td colspan="9">No pending trades were found.</td>
            </tr>
            <?php } ?>
            </table> 
            </div>      
        </div>
        <p /><br />
    </div>
    <p /><br />