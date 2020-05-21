    <link media="screen" rel="stylesheet" href="<?php echo($config['fantasy_web_root']); ?>css/colorbox.css" />
	<script src="<?php echo($config['fantasy_web_root']); ?>js/jquery.colorbox.js"></script>
	<script type="text/javascript" charset="UTF-8">
	var charLimit = 1000;
	$(document).ready(function(){
		$('a[rel=denyResponse]').click(function (e) {					   
			e.preventDefault();
			openDialog(e, 'responseDialog', this.id);
		});
		$('a[rel=retractInvite]').click(function (e) {					   
			e.preventDefault();
			openDialog(e, 'retractDialog', this.id);
		});
		 
	});
	function init() {
		/* Character Counter for inputs and text areas 
		 */  
		$('.word_count').each(function(){  
		    // get current number of characters  
		    var length = $(this).val().length;  
		    // get current number of words  
		    //var length = $(this).val().split(/\b[\s,\.-:;]*/).length;  
		    // update characters  
		    $(this).parent().find('.counter').html('<span style="color:#'+getColor(length)+';">' + length + ' characters</span>');  
		    // bind on key up event  
		    $(this).keyup(function(){  
				 // get new length of characters  
		        var new_length = $(this).val().length;  
		        // get new length of words  
		        //var new_length = $(this).val().split(/\b[\s,\.-:;]*/).length;  
		        // update  
		        $(this).parent().find('.counter').html('<span style="color:#'+getColor(new_length)+';">' + new_length + ' characters</span>');  
		    });  
		}); 
		$('#btnCancel').click(function (e) {					   
			e.preventDefault();
			$.colorbox.close();
		});
		 $('#btnSendResponse').click(function (e) {					   
			e.preventDefault();
			if ($('#message').val() != '') {
				$('#responseForm').submit();
			 } else {
				alert("A message to the user is required to complete this team denial.");
				$('#message').focus();
			 }
		});
		 $('#btnSendWithdrawl').click(function (e) {					   
			e.preventDefault();
			if ($('#invMessage').val() != '') {
				$('#withdrawlForm').submit();
			 } else {
				alert("A message to the user is required to withdraw an invitation.");
				$('#invMessage').focus();
			 }
		});
	}
	function getColor(new_length) {
		var color = "060";
	    var critLen = (charLimit - parseInt(charLimit * .05)),
        warnLen = (charLimit - parseInt(charLimit * .15));
        if (new_length >= warnLen && new_length < critLen) {
			color = "f60";
        } else if (new_length >= critLen) {
	        color = "c00";
        }
        return color;
	}
	function openDialog(e,id, params) {
		if (id ==  'responseDialog') {
			$('#'+id + ' input#request_id').val(params);
		} else {
			var paramList = params.split("|");
			$('#'+id + ' input#invite_id').val(paramList[0]);
			$('#'+id + ' input#league_id').val(paramList[1]);
		}
		
		$.colorbox({html:$('div#'+id).html()});
		init();
	}
	</script>
	<div id="responseDialog" class="dialog" style="position:absolute;visibility:hidden;top:-5000px;left:-5000px">
		<form method='post' action="<?php echo($config['fantasy_web_root']); ?>league/requestResponse" name='responseForm' id="responseForm">
        <input type='hidden' id="submitted" name='submitted' value='1'></input>
        <input type='hidden' id="request_id" name='request_id' value='-1'></input>
        <input type='hidden' id="type" name='type' value='<?php echo(REQUEST_STATUS_DENIED); ?>'></input>
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
              'class'		=> 'word_count',
              'rows'        => '5',
              'cols'		=> '45'
			);
			echo(form_textarea($data));
			?><br clear="all" />
			<span class="counter"></span>, Limit 1000.
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

	<div id="retractDialog" class="dialog" style="position:absolute;visibility:hidden;top:-5000px;left:-5000px">
		<form method='post' action="<?php echo($config['fantasy_web_root']); ?>league/withdrawInvitation" name='withdrawlForm' id="withdrawlForm">
        <input type='hidden' id="invite_id" name='invite_id' value=''></input>
        <input type='hidden' id="type_id" name='type_id' value='1'></input>
    	<input type='hidden' id="league_id" name='league_id' value='<?php print($league_id); ?>'></input>
        <div class='textbox'>
         <table cellpadding="2" cellspacing="0" cellborder="0">
          <tr class='title'><td>Message to User</td></tr>
          <tr>
          <tr class='highlight'>
          <td>Provide a message to this user why you're withdrawing your invitation.<br />
		      You should really only withdraw an invitation when you may have<br /> multiple
			  invitations out for a single team and someone has already accepted.
		  </td>
          </tr>
          <tr>
			<td>
  			<?php 
			$data = array(
              'name'        => 'invMessage',
              'id'          => 'invMessage',
              'value'       => '',
              'maxlength'   => '1000',
              'class'		=> 'word_count',
              'rows'        => '5',
              'cols'		=> '45'
			);
			echo(form_textarea($data));
			?><br clear="all" />
			<span class="counter"></span>, Limit 1000.
			</td>
			</tr>
           <tr>
           <td>
           <input type='button' id="btnCancel" class="button" value='Cancel' />
           <input type='button' class="button" id="btnSendWithdrawl" value='Withdraw Invitation' /></td>
          </tr>
         </table>
        </div>
        </form>
	</div>
    
    <div id="column-single">
   	<?php include_once('admin_breadcrumb.php'); ?>
	    
		<h1><?php echo($subTitle); ?></h1>

	    <p style="text-align:left;">
		<?php 
		$linkTxt = ($show_all == -1 ? 'All Invites and Requests':'Only Pending Invites and Requests');
		$linkStr = anchor('league/leagueInvites/league_id/'.$league_id.'/show_all/'.($show_all == -1 ? '1':'-1'), $linkTxt);
		$typeStr = '';
		$introStr = '[STATUS]Invites and Team Requests for this league.';
		if ($show_all == -1) {
			$typeStr = 'Pending ';
		} else {
			$typeStr = 'All ';
		}
		$introStr = str_replace('[STATUS]', $statusStr, $introStr);
		echo($introStr." Show: ".$linkStr);
		?>
		</p>
		<h3><?php echo($typeStr); ?>Owner Invitations</h3>
        
        <div class="content-form">
            
            <div class='textbox'>
            <table cellspacing="0" cellpadding="6">
            <tr class='title'><td colspan="3">Invitations</td></tr>
            <tr class='headline' class='hsc2_c'>
                <td class='hsc2_l' width="20%">E-Mail</td>
                <td class='hsc2_c' width="20%">Date Sent</td>
				<td class='hsc2_l' width="20%">Team</td>
				<td class='hsc2_c' width="15%">Status</td>
				<td class='hsc2_c' width="25%">Options</td>
            </tr>
            <?php 
            if (isset($thisItem['invites']) && sizeof($thisItem['invites']) > 0) {
            	$rowCount = 0;
				foreach ($thisItem['invites'] as $row) {
					if (($rowCount %2) == 0) { $color = "#EAEAEA"; } else { $color = "#FFFFFF"; }
            	?>
                <tr style="background-color:<?php echo($color); ?>">
					<td class='hsc2_l'><?php echo($row['to_email']); ?></td>
                    <td class='hsc2_c'><?php echo(date('M, j Y h:i:s A',strtotime($row['send_date']))); ?></td>
					<td class='hsc2_l'><?php echo(anchor('team/info/'.$row['team_id'],$row['team'])); ?></td>
					<?php
					switch ($row['status_id']) {
						case INVITE_STATUS_ACCEPTED:
							$class = 'positive';
							break;
						case INVITE_STATUS_DECLINED:
							$class = 'negative';
							break;
						case INVITE_STATUS_WITHDRAWN:
						case INVITE_STATUS_REMOVED:
							$class = 'warning';
							break;
						case INVITE_STATUS_PENDING:
							$class = 'alert';
							break;
						default:
							$class = 'message';
					}
					?>
					<td class="hsc2_c <?php echo($class); ?>"><?php print($row['inviteStatus']); ?></td>
					<td class='hsc2_c'><?php 
					if ($row['status_id'] == INVITE_STATUS_PENDING) {
						print(anchor('#','<img src="'.PATH_IMAGES.'/icons/icon_fail_major.png" width="16" height="16" border="0" alt="Retract" align="absmiddle" /> Withdraw',array('id'=>$row['id']."|".$row['league_id'],'rel'=>'retractInvite')));
						echo('&nbsp;&nbsp;');
						print(anchor('/league/resendInvitation/invite_id/'.$row['id'].'/league_id/'.$row['league_id'],'<img src="'.PATH_IMAGES.'/icons/next.png" width="16" height="16" border="0" alt="Resend" align="absmiddle" /> Resend')); 
					}
					?>  
    			</tr>
				<?php 
				$rowCount++;
				} 
			} else { ?>
            <tr class='s1_1'>
                <td colspan="5" class='hsc2_c'>No <?php echo(($show_all == -1) ? 'pending' : ''); ?> invitations were found.</td>
            </tr>
            <?php } ?>
            </table> 
            </div>
            
            <br clear="all" />
            <p>
         </div>
            
        <h3><?php echo($typeStr); ?>Team Requests</h3>
        <div class="content-form">
            <div class='textbox'>
            <table cellspacing="0" cellpadding="6">
            <tr class='title'><td colspan="4">Team Requests</td></tr>
            <tr class='headline'>
                <td class='hsc2_1' width="20%">Username</td>
                <td class='hsc2_c' width="20%">Date Requested</td>
                <td class='hsc2_c' width="20%">Team</td>
				<td class='hsc2_c' width="15%">Status</td>
                <td class='hsc2_c' width="25%">Options</td>
            </tr>
            <?php 
            if (isset($thisItem['requests']) && sizeof($thisItem['requests']) > 0) {
               $rowCount = 0;
			   foreach ($thisItem['requests'] as $row) {
					if (($rowCount %2) == 0) { $color = "#EAEAEA"; } else { $color = "#FFFFFF"; }
            	?>
                <tr style="background-color:<?php echo($color); ?>">
					<td class='hsc2_l'><?php echo(anchor('/user/profiles/'.$row['user_id'],$row['username'])); ?></td>
                    <td class='hsc2_c'><?php echo(date('M, j Y h:i:s A',strtotime($row['date_requested']))); ?></td>
					<td class='hsc2_l'><?php echo(anchor('team/info/'.$row['team_id'],$row['team'])); ?></td>
					<?php
					switch ($row['status_id']) {
						case REQUEST_STATUS_ACCEPTED:
							$class = 'positive';
							break;
						case REQUEST_STATUS_DENIED:
							$class = 'negative';
							break;
						case REQUEST_STATUS_WITHDRAWN:
						case REQUEST_STATUS_REMOVED:
							$class = 'warning';
							break;
						case REQUEST_STATUS_PENDING:
							$class = 'alert';
							break;
						default:
							$class = 'message';
					}
					?>
					<td class="hsc2_c <?php echo($class); ?>"><?php print($row['requestStatus']); ?></td>
                    <td class='hsc2_c'><?php 
					if ($row['status_id'] == REQUEST_STATUS_PENDING) {
						print(anchor('/league/requestResponse/request_id/'.$row['id'].'/type/'.REQUEST_STATUS_ACCEPTED.'/league_id/'.$league_id,'<img src="'.PATH_IMAGES.'/icons/accept.png" width="16" height="16" border="0" alt="Accept" align="absmiddle" /> Accept').' &nbsp;'.
							  anchor('#','<img src="'.PATH_IMAGES.'/icons/icon_fail.png" width="16" height="16" border="0" alt="Reject" align="absmiddle" /> Reject',array('id'=>$row['id'],'rel'=>'denyResponse'))	); 
					}		  
					?>  
                    
    			</tr>
				<?php 
				$rowCount++;
				} 
			} else { ?>
            <tr class='s1_1'>
                <td colspan="5" class='hsc2_c'>No <?php echo(($show_all == -1) ? 'pending' : ''); ?> requests were found.</td>
            </tr>
            <?php } ?>
            </table> 
            </div> 
        </div>
		<br clear="all" />
		<?php 
		if ($show_all == -1 && isset($thisItem['requests']) && sizeof($thisItem['requests']) > 0) { ?>
		<div style="width:100%; text-align:right;"><br />
		<?php print(anchor('league/clearRequestQueue/'.$league_id,'<button class="sitebtn lineup">Clear Request Queue</button>'))?>
		</div>
        <p><br />
		<?php
		}
		?>
    </div>
    <p><br />