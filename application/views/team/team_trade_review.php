   	<script type="text/javascript" charset="UTF-8">
	var queryStr = '';
	var teamId = <?php print($team_id); ?>;
	var teamId1 = <?php print($team_id1); ?>;
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
				document.location.href = '<?php print($config['fantasy_web_root']); ?>team/trade/'+queryStr;
			}
		});
		$('#btnCounter').click(function(){
			$('#type').val(<?php print(TRADE_REJECTED_COUNTER); ?>);
			$('#tradeForm').submit();
		});
		$('input[rel=protestBtn]').click(function(){
			if ($('#comments').val() == "") {
				showMessage('error',"Comments are required.");
			} else {
				$('#type').val(<?php print(TRADE_PROTEST); ?>);
				$('#tradeForm').submit();
			}
		});
		$('input[rel=commishRejectBtn]').click(function(){
			$('#type').val(<?php print(TRADE_REJECTED_COMMISH); ?>);
			$('#tradeForm').submit();
		});
		$('input[rel=commishApproveBtn]').click(function(){
			$('#type').val(<?php print(TRADE_APPROVED); ?>);
			$('#tradeForm').submit();
		});
		$('#btnReject').click(function(){
			$('#type').val(<?php print(TRADE_REJECTED_OWNER); ?>);
			$('#tradeForm').submit();
		});
		$('#btnAccept').click(function(){
			$('#type').val(<?php print(TRADE_ACCEPTED); ?>);
			//alert($('#type').val());
			$('#tradeForm').submit();
		});
		$('#btnRetract').click(function(){
			$('#type').val(<?php print(TRADE_RETRACTED); ?>);
			$('#tradeForm').submit();
		});
		
		$('#btnCancel').click(function(){
			history.back(-1);
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
        <div class="top-bar"> <div class="top-bar"><h1><?php print($subTitle); ?></h1></div>
		<?php 
		$errors = validation_errors();
		if ($errors) {
			print '<span class="error">The following errors were found with your submission:<br/ ><b>'.$errors.'</b></span><p />';
		}
		if (isset($message) && !empty($message)) {
			print("<p><span>".$message."</span></p>");
		}
		?>
		<div id="content">
			<?php
			$msgClass = "notice";
			$outMess = "";
			if (isset($status) && $status > TRADE_OFFERED && ($status != TRADE_PENDING_LEAGUE_APPROVAL && $status != TRADE_PENDING_COMMISH_APPROVAL)) {
				// Override transaction type to read only
				$trans_type = 50;
				$msgClass = "info";
				$outMess = "This trade is no longer active. You are viewing a read only version.";
			}
			if (isset($trans_type) && $trans_type > 1 && $trans_type <= 3) {
				if ($status == TRADE_OFFERED) {
					if ($trans_type == 2) {
						$outMess = "This trade requires a response from you.";
					} else if ($trans_type == 3) {
						$outMess = "You are waiting for a response to this offer.";
					}
				}
				if ($config['approvalType'] != -1) {
					if ($status == TRADE_PENDING_LEAGUE_APPROVAL || $status == TRADE_PENDING_COMMISH_APPROVAL) {
						$actionType = "is pending";
					} else {
						$actionType = "requires";
					}
					$approvalType = loadSimpleDataList('tradeApprovalType'); 
					if (!empty($outMess)) { $outMess.="<br />"; }
					$outMess.="This trade ".$actionType." ".$approvalType[$config['approvalType']]." approval.";
				}
			}
			if (!empty($outMess)) { print('<span class="'.$msgClass.'">'.$outMess.'</span>'); }

			if (isset($formatted_stats) && sizeof($formatted_stats) > 0) { ?>
            
            <?php
				$lists = array('team_id2','team_id1');
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
                        $theAvatar = $team_avatar1;
						$theName = $team_name1;
					}
					?>
					<h2 style="float:left; display:inline-block"><?php
					if (isset($theAvatar) && !empty($theAvatar)) { 
						$tmpavatar = PATH_TEAMS_AVATARS.$theAvatar;
					} else {
						$tmpavatar = PATH_TEAMS_AVATARS.DEFAULT_AVATAR;
					}
					?>
					<img src="<?php print($tmpavatar); ?>" width="48" height="48" border="0" align="absmiddle" />
					&nbsp;&nbsp;<?php print(trim($theName)); ?></h2>
                        <div class="textbox" style="width:915px;">
                        <?php
                        foreach($types as $player_type) { 
							if (isset($formatted_stats[$team][$player_type]) && sizeof($formatted_stats[$team][$player_type])>0){ ?>
                                <!-- HEADER -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr class="title">
                                <td height="17" style="padding:6px;"><?php print($title[$player_type]); ?> Stats</td>
                             </tr>
                             </table>
							<?php
                            print($formatted_stats[$team][$player_type]);						 
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
                    <?php 
                    $action = "tradeResponse";
                    if ($trans_type == 1) {
                    	$action = "tradeOffer";
                    }
                    ?>
                    <form action="<?php print($config['fantasy_web_root']); ?>team/<?php print($action); ?>/" method="post" id="tradeForm" id="tradeForm">
                 	<?php 
					if ($config['tradesExpire'] == 1) { ?>
                        
                        <?php 
                        if ($trans_type == 1) {
                            $expireList = array("X"=>"Select One",-1=>"No Expiration",500 =>"Next Sim Period");
                            ?>
                            <label for="expiresIn">Expires in: </label>
                            <select id="expiresIn" name="expiresIn">
                            <?php
							foreach($expireList as $days => $label) {
								print('<option value="'.$days.'"');
								if (isset($expiresIn) && $expiresIn == $days){
									print(' selected="selected"');
								}
								print('>'.$label.'</option>');
							}
							for($d = 1; $d < TRADE_MAX_EXPIRATION_DAYS; $d++) {
								print('<option value="'.$d.'"');
								if (isset($expiresIn) && $expiresIn == $d){
									print(' selected="selected"');
								}
								print('>'.$d.' Days</option>');
							}
							print('</select>');
                        } else { 
                            if (isset($expiration_days) && isset($offer_date)) {
                                $expireStr = "";
                                $expireLabel = "Expires";
                                switch(intval($expiration_days)) {
                                    case -1:
                                        $expireStr = "No expiration";
                                        break;
                                    case 500:
                                        $expireStr = "Next Sim";
                                        break;
                                    default:
                                        $expireDate = (strtotime($offer_date) + ((60*60*24) * $expiration_days));
                                        if ($expireDate < time()) {
                                            $expireLabel = "Expired";
                                        }
                                        $expireStr = date('m/d/Y h:m A', $expireDate);
                                        break;
                                }
                            ?>
                            <label for="txt_ref"><?php print($expireLabel); ?></label>
                            <div id="txt_ref" class="textAreaForDisplay"><?php print($expireStr); ?></div>
                            <?php
                            }
						}
                  	?>
                    <br class="clear" clear="all" />
					<?php
                    }  // END if tradesExpire == 1
					if ($trans_type != 1 && $trans_type != 4) {
						if (isset($comments) && !empty($comments)) { ?>
						<label for="comments">Comments: </label> 	
                        <div class="textAreaForDisplay"><?php print(trim($comments)); ?></div><br />
					<?php }
						if (isset($response) && !empty($response)) { ?>
						<label for="comments">Response: </label> 	
                        <div class="textAreaForDisplay"><?php print(trim($response)); ?></div><br />
					<?php }
					}
					$showProtestBtn = true;
					if ($trans_type == 4) { 
						$showProtestBtn = true;
						if (isset($protests) && sizeof($protests) > 0) {
							foreach ($protests as $tmpProtest) {
								if ($tmpProtest['trade_id'] == $trade_id && $tmpProtest['team_id'] == $team_id) {
									$showProtestBtn = false;
									break;
								}
							}
						}
					}
					if  ($trans_type == 1 || ($trans_type == 4 && $showProtestBtn) || ($trans_type == 2 && $status == TRADE_OFFERED) || ($trans_type == 5 && ($status == TRADE_OFFERED || $status == TRADE_PENDING_LEAGUE_APPROVAL || $status == TRADE_PENDING_COMMISH_APPROVAL))) { ?>
                    <label for="comments">Add Comments: </label> <textarea id="comments" name="comments" cols="54" rows="8"></textarea>
                    <br class="clear" clear="all" />
                    <?php 
					}
					?>
                    <br />
                    <input type="hidden" name="id" value="<?php print($team_id); ?>" />
                    <input type="hidden" name="referrer" value="team/team_trade_review" />
					<?php
                    if (isset($trade_id) && !empty($trade_id) && $trade_id != -1) { ?>
                    <input type="hidden" name="trade_id" id="trade_id"  value="<?php print($trade_id); ?>" />
                    <?php } else { ?>
                    <input type="hidden" name="team_id2" value="<?php print($team_id2); ?>" />
                    <input type="hidden" name="tradeFrom" id="tradeFrom" value="<?php print($tradeFrom); ?>" />
                    <input type="hidden" name="tradeTo" id="tradeTo" value="<?php print($tradeTo); ?>" />
                    <?php } ?>
                    <div class="button_bar">
                    <input type="button" class="button" id="btnCancel" name="btnCancel" value="Cancel" />
                    <?php
                    /* 
                    / TRANS TYPE DICTIONARY
                    /	1:	Trade initator submitting offer
                    /	2:  Trade Recipient Reviewing offer
                    /	3:	Trade initator Reviewing Trade (read only)
                    /	4:	Other League Owner Reviewing offer
                    /	5:	League Commisioner/Admin Review
                    /	50: Read Only (Archived) View
                    */
					// TRADE OWNER
					if ($trans_type == 1) { ?>
						<input type="button" class="button" id="btnEdit" name="btnEdit" value="Edit Trade" />
                        <input type="button" class="button" id="btnSubmit" name="btnSubmit" value="Make Offer" />
					<?php 
					}
					// TRADE RECIPIENT
					 else if ($trans_type == 2) { 
					 	if ($status == TRADE_OFFERED) { ?>
                        <input type="button" class="button" id="btnReject" name="btnReject" value="Reject Offer" />
                        <input type="button" class="button" id="btnCounter" name="btnCounter" value="Make Counter Offer" />
                        <input type="button" class="button" id="btnAccept" name="btnAccept" value="Accept Offer" />
                        <input type="hidden" name="prevTradeId" value="<?php print($trade_id); ?>" />
                   	<?php } 
					 }
                   	// TRADE OWNER REVIEW (READ ONLY)
                   	else if ($trans_type == 3) { 
                    	if ($status == TRADE_OFFERED) { ?>
                        <input type="button" class="button" id="btnRetract" name="btnReject" value="Retract Offer" />
                   	<?php } 
					}
                   	// LEAGUE OWNER REVIEW (PROTEST ONLY)
                   	else if ($trans_type == 4) { 
						if ($showProtestBtn) {?>
                        <input type="button" class="button" rel="protestBtn" id="<?php print($trade_id); ?>" name="btnProtest" value="Protest Trade" />
                   	<?php } 
					}
					// LEAGUE COMMISIONER REVIEW
                   	else if ($trans_type == 5 && ($status == TRADE_OFFERED || $status == TRADE_PENDING_LEAGUE_APPROVAL || $status == TRADE_PENDING_COMMISH_APPROVAL)) { ?>
                        <input type="button" class="button" rel="commishRejectBtn" id="<?php print($trade_id); ?>" name="btnReject" value="Reject Trade" />
                        <?php if ($config['approvalType'] == 1) { ?>
                        <input type="button" class="button" rel="commishApproveBtn" id="<?php print($trade_id); ?>" name="btnApprove" value="Approve Trade" />
                   	<?php }
					}
                   	if ($trans_type != 1) { 
                   	?>
                   		<input type="hidden" name="type" id="type" value="1" />
                   	<?php } ?>
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