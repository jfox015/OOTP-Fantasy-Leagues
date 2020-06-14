	<script type="text/javascript" charset="UTF-8">
	$(document).ready(function(){
    	/*$("input[name='useTrades']").change(function() {
			testTradeStatus($('.tradesRadio:checked').val());
		});*/
		$("input[name='autoLockSyncwithSims']").change(function(){

			showAutoDates($('.syncSims:checked').val());
		});
		showAutoDates(<?php echo($config['autoLockSyncwithSims']); ?>);
		//testTradeStatus(<?php echo($config['useTrades']); ?>);

	});
	/*function testTradeStatus(val) {
		$('.tradeDetails').css('display',(val == 1 ? "block" : "none") );
	};*/
	function showAutoDates(val) {
		$('#autoDateOpts').css('display',(val == 1 ? "none" : "block") );
	}
	</script>
    	<!-- BEGIN REGISTRATION FORM -->
	<div id="subPage">
        <?php include_once('admin_breadcrumb.php'); ?>

        <h1><?php echo($subTitle); ?></h1>
        <br />
        <div class='textbox'>
        <table cellpadding="0" cellspacing="0" style="width:825px;">
        <tr class='title'>
            <td style='padding:3px' colspan="2">Enter settings information below</td>
        </tr>
        <tr>
            <td>
			<?php

			// DATA VARS FOR FORM FIELDS
			$timeArr = array();
			$interval = '+30 minutes';
			$current = strtotime( '00:00' );
			$end = strtotime( '23:59' );
		
			while( $current <= $end ) {
				$timeArr[date( 'H:i', $current )] = date( 'h.i A', $current );
				$current = strtotime( $interval, $current );
			}

			$days = array(1=>'Sunday',2=>'Monday',3=>'Tuesday',4=>'Wednesday',5=>'Thursday',6=>'Friday',7=>'Saturday');
			
			$simResponses[] = array('1','Daily');
			$simResponses[] = array('-1','Weekly');

			$responses[] = array('1','Yes');
			$responses[] = array('-1','No');

            $errors = validation_errors();
            if ($errors) {
                echo '<span class="error">The following errors were found with your submission:<br/ ><b>'.$errors.'</b><br clear="all" /></span><br /><br />';
            }
			if ($outMess) {
                echo $outMess;
			}
			
            $form = new Form();
            $form->open('admin/configFantasy','configFantasy');
            $form->br();
           	$form->fieldset('General');
            $form->text('season_start','Season Start Date:','required',$season_start);
            $form->nobr();
			$form->span("This is the real world calendar date when you will begin running sims for this OOTP season. Be sure to choose a date that is far enough out to allow for leagues to be populated and owners to scout available players for the draft.",array('class'=>'field_caption'));
			$form->space();
			$form->fieldset('Sim Details');
			$form->fieldset('',array('class'=>'radioGroup'));
			$form->radiogroup ('simType',$simResponses,'Sim Type',($this->input->post('simType') ? $this->input->post('simType') : $config['simType']),'required');
			$form->nobr();
			$form->fieldset('',array('class'=>'radioGroup'));
			$form->text('sim_length','Sim Length <span class="normal">(Days in OOTP Game)</span>','required|trim|number',($input->post('sim_length') ? $input->post('sim_length') : $config['sim_length']),array('class'=>'shorttext'));
			$form->nobr();
			$form->span("This is the default number of days simmed in the game per Sim. The mod uses this number to build the scoring period schedule. That schedule can be adjusted but it's recommended to let the mod calculate the scoring periods.",array('class'=>'field_caption'));
			$form->br();
			$form->space();
			$form->fieldset('');
            $form->select('default_scoring_periods|default_scoring_periods',array(27=>27,26=>26,25=>25,24=>24,23=>23,22=>22,21=>21,20=>20,19=>19,18=>18),'Default No. Scoring Periods',($this->input->post('default_scoring_periods')) ? $this->input->post('default_scoring_periods') : $config['default_scoring_periods'],'required');
			$form->br();
			//echo("simUploadTimeStart = ".$simUploadTimeStart."<br />");
			$form->fieldset('');
            $form->select('simUploadTimeStart|simUploadTimeStart',$timeArr,'Upload Start Time',($this->input->post('simUploadTimeStart')) ? $this->input->post('simUploadTimeStart') : $simUploadTimeStart,'required');
			$form->fieldset('');
			//echo("simUploadTimeEnd = ".$simUploadTimeEnd."<br />");
			$form->select('simUploadTimeEnd|simUploadTimeEnd',$timeArr,'Upload End Time',($this->input->post('simUploadTimeEnd')) ? $this->input->post('simUploadTimeEnd') : $simUploadTimeEnd,'required');
			$form->br();
			$form->fieldset('');
			$form->select('simDays[]|simDays[]',$days,'Sim Occur On',($this->input->post('simDays')) ? $this->input->post('simDays') : $simDays, 'required', array('multiple'=>true));
			$form->br();

			$form->fieldset('Leagues');
			$form->nobr();
			$form->fieldset('',array('class'=>'radioGroup'));
			$form->radiogroup ('restrict_admin_leagues',$responses,'Restrict # of Admin Leages?',($this->input->post('restrict_admin_leagues') ? $this->input->post('restrict_admin_leagues') : $config['restrict_admin_leagues']),'required');
			$form->nobr();
			$form->fieldset('');
			$form->fieldset('',array('class'=>'radioGroup'));
			$form->radiogroup ('users_create_leagues',$responses,'Users can create leagues?',($this->input->post('users_create_leagues') ? $this->input->post('users_create_leagues') : $config['users_create_leagues']),'required');
			$form->fieldset();
			$form->text('max_user_leagues','Max # of leagues per user','required|trim|number',($input->post('max_user_leagues') ? $input->post('max_user_leagues') : $config['max_user_leagues']),array('class'=>'shorttext'));
            $form->space();
            $form->fieldset('Draft');
			$form->nobr();
            $form->fieldset('',array('class'=>'dateLists'));
            $form->text('draft_start','Draft Period:','required',$draft_start);
            $form->nobr();
            $form->text('draft_end','','required',$draft_end);
           	$form->fieldset('');
			$form->text('draft_rounds_min','Minimum Draft Rounds','required|trim|number',($input->post('draft_rounds_min') ? $input->post('draft_rounds_min') : $config['draft_rounds_min']),array('class'=>'shorttext'));
            $form->space();
           	$form->text('draft_rounds_max','Maximum Draft Rounds','required|trim|number',($input->post('draft_rounds_max') ? $input->post('draft_rounds_max') : $config['draft_rounds_max']),array('class'=>'shorttext'));
            $form->space();
			$form->fieldset('Auto Roster Locking');
			$form->br();
			$form->span("The following options allow the site to automatrically lock all Roster transactions during a Sim Upload Window when stats are being uploaded and updated and transactions should not be allowed.");
			$form->nobr();
			$form->fieldset('',array('class'=>'radioGroup'));
			$form->radiogroup ('autoLockRosters',$responses,'Auto Lock Rosters',($this->input->post('autoLockRosters') ? $this->input->post('autoLockRosters') : $config['autoLockRosters']),'required');
			$form->nobr();
			$form->fieldset('',array('class'=>'radioGroup'));
			$form->radiogroup ('autoLockSyncwithSims',$responses,'Lock only during Sim Uploads',($this->input->post('autoLockSyncwithSims') ? $this->input->post('autoLockSyncwithSims') : $config['autoLockSyncwithSims']),'required', array('class'=>'syncSims'));
			$form->space();
			$form->fieldset('Configure Lock Times and Days',array('id'=>'autoDateOpts'));
            $form->select('autoLockStart|autoLockStart',$timeArr,'Start Time',($this->input->post('autoLockStart')) ? $this->input->post('autoLockStart') : $autoLockStart);
			$form->br();
			$form->select('autoLockEnd|autoLockEnd',$timeArr,'End Time',($this->input->post('autoLockEnd')) ? $this->input->post('autoLockEnd') : $autoLockEnd,);
			$form->space();
			$form->select('autoLockDays[]|autoLockDays[]',$days,'Select Days for Lock',($this->input->post('autoLockDays')) ? $this->input->post('autoLockDays') : $lockDays, false, array('multiple'=>true));
			$form->br();
			$form->space();
			/* ROSTER, WAIVERS AND TRADE SETTINGS MOVED TO LEAGUE ADMIN
			$form->fieldset('Roster Rules');
			$form->br();
			$form->span("Minimum Game Played to be elidigble at position:");
			$form->br();
			$form->fieldset('',array('class'=>'dateLists'));
            $form->text('min_game_current','This Season','required|trim|number',($input->post('min_game_current') ? $input->post('min_game_current') : $config['min_game_current']),array('class'=>'shorttext'));
            $form->nobr();
           	$form->text('min_game_last','Last Season','required|trim|number',($input->post('min_game_last') ? $input->post('min_game_last') : $config['min_game_last']),array('class'=>'shorttext'));
			$form->br();
           	$form->fieldset('',array('class'=>'radioGroup'));
			$form->radiogroup ('useWaivers',$responses,'Waivers Enabled?',($this->input->post('useWaivers') ? $this->input->post('useWaivers') : $config['useWaivers']),'required');
			$form->space();
			$expireList = array("X"=>"Select One",-1=>"No Expiration",500 =>"Next Sim Period");
			for($d = 1; $d < TRADE_MAX_EXPIRATION_DAYS + 1; $d++) {
				$expireList = $expireList  + array($d =>$d." Days");
			}
			$form->fieldset('Trading');
			$form->fieldset('',array('class'=>'radioGroup'));
			$form->radiogroup ('useTrades',$responses,'Trading Enabled?',($this->input->post('useTrades') ? $this->input->post('useTrades') : $config['useTrades']),'required', array('class'=>'tradesRadio'));
			$form->fieldset('');
			$form->html('<div class="tradeDetails">');
			//$form->fieldset('',array('class'=>'radioGroup'));
            $form->select('approvalType|approvalType',loadSimpleDataList('tradeApprovalType'),'Trade Approval',($this->input->post('approvalType')) ? $this->input->post('approvalType') : $config['approvalType'],'required');
			$form->select('minProtests|minProtests',array(1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8),'League Protests to void trade',($this->input->post('minProtests')) ? $this->input->post('minProtests') : $config['minProtests']);
			$form->select('protestPeriodDays|protestPeriodDays',$expireList,'Trade Review Period',($this->input->post('protestPeriodDays')) ? $this->input->post('protestPeriodDays') : $config['protestPeriodDays']);
			//$form->radiogroup ('commishApproveTrades',$responses,'Commissioner Approval Required?',($this->input->post('commishApproveTrades') ? $this->input->post('commishApproveTrades') : $config['commishApproveTrades']),'required');
			$form->space();
			$form->fieldset('',array('class'=>'radioGroup, tradeDetails'));
			$form->radiogroup ('tradesExpire',$responses,'Trade offers can expire?',($this->input->post('tradesExpire') ? $this->input->post('tradesExpire') : $config['tradesExpire']),'required');
			$form->space();
			$form->fieldset('',array('class'=>'tradeDetails'));
            $form->select('defaultExpiration|defaultExpiration',$expireList,'Default Expiration (Days)',($this->input->post('defaultExpiration')) ? $this->input->post('defaultExpiration') : $config['defaultExpiration']);
			$form->html('</div>');
			// END SETTING DEPRECATION
			*/
			$form->fieldset('',array('class'=>'button_bar'));
            $form->submit('Submit');
            echo($form->get());
            ?>
            </td>
        </tr>
        </table>
        </div>
    </div>
    <p /><br />
<script type="text/javascript" src="<?php echo($config['fantasy_web_root']); ?>js/jquery.ui.core.js"></script>
<script type="text/javascript" src="<?php echo($config['fantasy_web_root']); ?>js/jquery.ui.datepicker.js"></script>
<script type="text/javascript">
$(function() {
	var today = new Date();
	$("#season_start").datepicker({ minDate: new Date(today.getFullYear(), today.getMonth(), today.getDate()) });
	$("#draft_start").datepicker();
	$("#draft_end").datepicker();
});
</script>