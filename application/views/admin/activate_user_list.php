    <div id="column-single">
   	<?php include_once('admin_breadcrumb.php'); ?>
    <h1><?php echo($subTitle); ?></h1>
        <div class="content-form">
            <p style="text-align:left;" />
            Users currently requiring activation.
            
            <div class='tablebox'>
            <table cellspacing="0" cellpadding="3" width="725">
            <tr class='title'><td colspan="4">Pending Activationa</td></tr>
            <tr class='headline' style="text-align:left;">
                <td width="30%">Username</td>
                <td width="30%">E-Mail</td>
                <td width="20%">Date Registered</td>
                <td width="20%" align="center">Tools</td>
            </tr>
            <?php 
            if (isset($activations) && sizeof($activations) > 0) {
               $rowCount = 0;
			   foreach ($activations as $row) {
				$cls="s".($rowCount%2+1);
				?>
 				<tr class="<?php echo($cls); ?>" style="text-align:left;">
					<td><?php echo(anchor('user/profiles/'.$row['id'],$row['username'])); ?></td>
                    <td><?php echo($row['email']); ?></td>
                    <td><?php echo(date('M, j Y h:m A',strtotime($row['dateCreated']))); ?></td>
					<td align="center"><?php echo(anchor('admin/activateUser/user_id/'.$row['id'],'Activate')); ?></td>
    			</tr>
				<?php 
				$rowCount++;
				} 
			} else { ?>
            <tr class='s1_1' align="center">
                <td colspan="4">No pending activations were found.</td>
            </tr>
            <?php } ?>
            </table> 
            </div>      
        </div>
        <p /><br />
    </div>
    <p /><br />