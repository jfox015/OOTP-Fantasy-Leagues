    <div id="column-single">
   	<?php include_once('admin_breadcrumb.php'); ?>
    <h1><?php echo($subTitle); ?></h1>
        <div class="content-form">
            <p style="text-align:left;" />
            Invites currently outstanding for this league.
            
            <div class='tablebox'>
            <table cellspacing="0" cellpadding="3" width="625">
            <tr class='title'><td colspan="3">Pending Invites</td></tr>
            <tr class='headline'>
                <td width="40%">E-Mail</td>
                <td width="30%">Date Sent</td>
                <td width="30%">Team</td>
            </tr>
            <?php 
            if (isset($thisItem['invites']) && sizeof($thisItem['invites']) > 0) {
               $rowCount = 0;
			   foreach ($thisItem['invites'] as $row) {
				$cls="s".($rowCount%2+1);
				?>
 				<tr class="<?php echo($cls); ?>" style="text-align:left;">
					<td><?php echo($row['to_email']); ?></td>
                    <td><?php echo(date('M, j Y h:m A',strtotime($row['send_date']))); ?></td>
					<td><?php echo(anchor('team/info/'.$row['team_id'],$row['team'])); ?></td>
    			</tr>
				<?php 
				$rowCount++;
				} 
			} else { ?>
            <tr class='s1_1' align="center">
                <td colspan="3">No pending invitations were found.</td>
            </tr>
            <?php } ?>
            </table> 
            </div>
            
            <br clear="all" />
            <p/>
            
            <h1>Pending Requests</h1>
            
            <div class='tablebox'>
            <table cellspacing="0" cellpadding="3" width="625">
            <tr class='title'><td colspan="4">Pending Team Requests</td></tr>
            <tr class='headline'>
                <td width="40%">Username</td>
                <td width="30%">Date Requested</td>
                <td width="30%">Team</td>
                <td width="30%">Options</td>
            </tr>
            <?php 
            if (isset($thisItem['requests']) && sizeof($thisItem['requests']) > 0) {
               $rowCount = 0;
			   foreach ($thisItem['requests'] as $row) {
				$cls="s".($rowCount%2+1);
				?>
 				<tr class="<?php echo($cls); ?>" style="text-align:left;">
					<td><?php echo(anchor('/user/profiles/'.$row['user_id'],$row['username'])); ?></td>
                    <td><?php echo(date('M, j Y h:m A',strtotime($row['date_requested']))); ?></td>
					<td><?php echo(anchor('team/info/'.$row['team_id'],$row['team'])); ?></td>
                    <td><?php print(anchor('/league/requestResponse/id/'.$league_id.'/request_id/'.$row['id'].'/type/1','Accept').' &nbsp;'.
									anchor('/league/requestResponse/id/'.$league_id.'/request_id/'.$row['id'].'/type/-1','Reject')	); ?>  
                    
    			</tr>
				<?php 
				$rowCount++;
				} 
			} else { ?>
            <tr class='s1_1' align="center">
                <td colspan="3">No pending invitations were found.</td>
            </tr>
            <?php } ?>
            </table> 
            </div> 
        </div>
        <p /><br />
    </div>
    <p /><br />