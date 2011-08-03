    <link media="screen" rel="stylesheet" href="<?php echo($config['fantasy_web_root']); ?>css/colorbox.css" />
	<script src="<?php echo($config['fantasy_web_root']); ?>js/jquery.colorbox.js"></script>
	<script type="text/javascript" charset="UTF-8">
	$(document).ready(function(){
		$('a[rel=denyResponse]').click(function (e) {					   
			e.preventDefault();
			openDialog(e, 'responseDialog', this.id);
		});
		 $('#btnCancel').click(function (e) {					   
			e.preventDefault();
			$.colorbox.close();
		});
		 $('#btnSendResponse').click(function (e) {					   
			e.preventDefault();
			$('#responseForm').submit();
		});
	});
	function openDialog(e,id, params) {
		$('#'+id + ' input#request_id').val(params);
		$.colorbox({html:$('div#'+id).html()});
	}
	</script>
	<div id="responseDialog" class="dialog">
		<form method='post' action="<?php echo($config['fantasy_web_root']); ?>league/requestResponse" name='responseForm' id="responseForm">
        <input type='hidden' id="request_id" name='request_id' value='-1'></input>
        <input type='hidden' id="type" name='type' value='-1'></input>
        <input type='hidden' id="league_id" name='league_id' value='<?php print($league_id); ?>'></input>
        <div class='textbox'>
         <table cellpadding="2" cellspacing="0" cellborder="0">
          <tr class='title'><td>Message to User</td></tr>
          <tr>
          <tr class='highlight'>
          <td>Provide a response as to why you're denying this team request</td>
          </tr>
          <tr>
			<td>
  			<?php 
			$data = array(
              'name'        => 'message',
              'id'          => 'message',
              'value'       => '',
              'maxlength'   => '1000',
              'rows'        => '5',
              'cols'		=> '30'
			);
			echo(form_textarea($data;);
			?>
			</td>
			</tr>
           <tr>
           <td>
           <input type='button' id="btnCancel" class="button" value='Cancel' />
           <input type='button' class="button" id="btnSendResponse" value='Send Response' /></td>
          </tr>
         </table>
        </div>
        </form>
	</div>
    
    <div id="column-single">
   	<?php include_once('admin_breadcrumb.php'); ?>
    <h1><?php echo($subTitle); ?></h1>
        <div class="content-form">
            <p style="text-align:left;" />
            Invites currently outstanding for this league.
            
            <div class='tablebox'>
            <table cellspacing="0" cellpadding="3" width="625">
            <tr class='title'><td colspan="3">Pending Invites</td></tr>
            <tr class='headline' align="center">
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
            <tr class='headline' align="center">
                <td width="25%">Username</td>
                <td width="25%">Date Requested</td>
                <td width="25%">Team</td>
                <td width="25%">Options</td>
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
                    <td><?php print(anchor('/league/requestResponse/id/'.$league_id.'/request_id/'.$row['id'].'/type/1','<img src="'.PATH_IMAGES.'/icons/accept.png" width="16" height="16" border="0" alt="Accept" align="absmiddle" /> Accept').' &nbsp;'.
									anchor('#','<img src="'.PATH_IMAGES.'/icons/icon_fail.png" width="16" height="16" border="0" alt="Reject" align="absmiddle" /> Reject',array('id'=>$row['id'],'rel'=>'denyResponse'))	); ?>  
                    
    			</tr>
				<?php 
				$rowCount++;
				} 
			} else { ?>
            <tr class='s1_1' align="center">
                <td colspan="4">No pending invitations were found.</td>
            </tr>
            <?php } ?>
            </table> 
            </div> 
        </div>
        <p /><br />
    </div>
    <p /><br />