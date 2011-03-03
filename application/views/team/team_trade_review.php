   	<script type="text/javascript" charset="UTF-8">
	var queryStr = '';
	var teamId = <?php print($team_id); ?>;
	var teamId2 = <?php print($team_id2); ?>;
	$(document).ready(function(){
		$('#btnEdit').click(function(){
			var proceed = true;
			if ($('#comments').val() != "" || $('#expiresIn').val() != -1) {
				proceed = confirm("Are you sure you want to edit this trade? Any information entered on this page will be lost and you will have to click 'Review' on the trade page and reenter your information to submit it. Do you want to proceed?");
			} if (proceed) {
				if ($('input#trade_id').val() && $('input#trade_id').val() != -1) {
					queryStr = 'trade_id/'+$('input#trade_id').val();
				} else {
					queryStr = 'tradeFrom/'+ $('#tradeFrom').val() + '/tradeTo/'+ $('#tradeTo').val()+ '/id/'+teamId+'/team_id2/'+teamId2;
				}
				document.location.href = '<?php echo($config['fantasy_web_root']); ?>team/trade/'+queryStr;
			}
		});
		$('#btnCounter').click(function(){

		});
		$('#btnReject').click(function(){

		});
		$('#btnCancel').click(function(){
			document.location.href = history.back(-1);
		});
		$('#btnSubmit').click(function(){
			if ($('#comments').val() == "") {
				showMessage('error',"Comments are required.");
			} else {
				$('#tradeForm').submit();
			}
		});
		$('#comments').change(function() { 
			$('div#activeStatus').empty();
			$('div#activeStatusBox').hide();
		});
	});
	function showMessage(type, message) {
		$('div#activeStatus').addClass(type.toLowerCase());
		$('div#activeStatus').html(message);
		$('div#activeStatusBox').show();
	}
	</script>
   	<div id="subPage">
        <div class="top-bar"> <div class="top-bar"><h1><?php echo($subTitle); ?></h1></div>
		<?php 
		$errors = validation_errors();
		if ($errors) {
			echo '<span class="error">The following errors were found with your submission:<br/ ><b>'.$errors.'</b></span><p />';
		}
		if (isset($message) && !empty($message)) {
			print("<p><span>".$message."</span></p>");
		}
		?>
		<div id="content">
			
            <?php
			if (isset($formatted_stats) && sizeof($formatted_stats) > 0) { ?>
            
            <?php
				$lists = array('team_id2','team_id');
				// If this is recipient viewing the trade, swap the order
				if (isset($trans_type) && $trans_type == 2) {
					$lists = array_reverse($lists);
				}
				$types = array('batters','pitchers');
				foreach($lists as $team) {
					if (isset($formatted_stats[$team]) && !empty($formatted_stats[$team])) { 
					$theAvatar = "";
					$theName = "";
					if ($team == 'team_id2') {
						$theAvatar = $team_avatar2;
						$theName = $team_name2;
					} else {
						$theAvatar = $avatar;
						$theName = $teamname." ".$teamnick;
					}
					?>
					<h2 style="float:left; display:inline-block"><?php
					if (isset($theAvatar) && !empty($theAvatar)) { 
						$tmpavatar = PATH_TEAMS_AVATARS.$theAvatar;
					} else {
						$tmpavatar = PATH_TEAMS_AVATARS.DEFAULT_AVATAR;
					}
					?>
					<img src="<?php echo($tmpavatar); ?>" width="48" height="48" border="0" align="absmiddle" />
					&nbsp;&nbsp;<?php print(trim($theName)); ?></h2>
                        <div class="textbox" style="width:915px;">
                        <?php
                        foreach($types as $player_type) { 
							if (isset($formatted_stats[$team][$player_type]) && sizeof($formatted_stats[$team][$player_type])>0){ ?>
                                <!-- HEADER -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr class="title">
                                <td height="17" style="padding:6px;"><?php echo($title[$player_type]); ?> Stats</td>
                             </tr>
                             </table>
							<?php
                            echo($formatted_stats[$team][$player_type]);						 
							}
						}
                         ?>
                        </div>
                        <?php
					}
				}?>
				<div class="space_large"></div>
				<?php
				/*------------------------------------
				/	RESPONSE FORM
				/-----------------------------------*/
				// ONLY SHOW BUTTONS IF THE USER IS THE OWNER OR RECIPIENT. COMMISIOENRS AND ADMINS
				// CAN MANAGE TRADES VIA THE LEAGUE->ADMIN->Trade tools
				if (isset($trans_type)) {
				?>
                    <div style="display:block;width:90%;position:relative; text-align:center; float:left;">
                    <div id="activeStatusBox"><div id="activeStatus"></div></div>
                    <form action="<?php echo($config['fantasy_web_root']); ?>team/tradeOffer/" method="post" id="tradeForm" id="tradeForm">
                 
                    <label for="comments">Comments: </label> <textarea id="comments" name="comments" cols="54" rows="8"><?php if (isset($comments) && !empty($comments) && $comments != "") { print(trim($comments)); } ?></textarea><br />
                    
                    <label for="expires">Expires in: </label> 
                    <?php 
                    $expireList = array(-1=>"Select One",0=>"No Expiration",1=>"1 Days",2=>"2 Days",3=>"3 Days",4=>"4 Days",5=>"5 Days"); 
                    ?>
                    <select id="expiresIn" name="expiresIn">
                    <?php 
                    	foreach($expireList as $days => $label) { 
                    		print('<option value="'.$days.'"');
                    		if (isset($expiresIn) && $expiresIn == $days){
                    			print(' selected="selected"');
                    		}
                    		print('>'.$label.'</option>');
                    	}
                  	?>
                    </select><br />
					<br class="clear" clear="all" /><br />
					<?php
					if (isset($trade_id) && !empty($trade_id) && $trade_id != -1) { ?>
                    <input type="hidden" name="trade_id" id="trade_id"  value="<?php print($trade_id); ?>" />
                   <? } else { ?>
                   <input type="hidden" name="id" value="<?php print($team_id); ?>" />
                   <input type="hidden" name="team_id2" value="<?php print($team_id2); ?>" />
                   <input type="hidden" name="tradeFrom" id="tradeFrom" value="<?php print($tradeFrom); ?>" />
                   <input type="hidden" name="tradeTo" id="tradeTo" value="<?php print($tradeTo); ?>" />
                   <?php } ?>
					<div class="button_bar">
					<input type="button" class="button" id="btnCancel" name="btnCancel" value="Cancel" />
                   <?php
                    // TRADE OWNER
					if ($trans_type == 1) { ?>
						<input type="button" class="button" id="btnEdit" name="btnEdit" value="Edit Trade" />
                        <input type="button" class="button" id="btnSubmit" name="btnSubmit" value="Make Offer" />
					<?php 
					}
					// TRADE RECIPIENT
					if ($trans_type == 2) { ?>
                        <input type="button" class="button" id="btnReject" name="btnReject" value="Reject Offer" />
                        <input type="button" class="button" id="btnCounter" name="btnCounter" value="Make Counter Offer" />
                        <input type="button" class="button" id="btnAccept" name="btnAccept" value="Accept Offer" />
                        
                   	<? } ?>
                   	<input type="hidden" name="submitted" value="1" />
                    
                    </div>
                    </form>
                    </div>
                 <?php 
				}
				
			}
			?>
      		</div>
       </div>
	</div>