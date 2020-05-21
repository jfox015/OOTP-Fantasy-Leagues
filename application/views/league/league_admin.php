    <script type="text/javascript">
    $(document).ready(function(){		   
		
	});
    </script>
       
    <div id="single-column">
        <div class="top-bar"> <h1><?php echo $subTitle; ?></h1></div>
		<?php if ($preseason) { ?>
		<span class="notice">
		<?php print($leeague_admin_intro_str); ?>
		</span>
		<?php
		} // END if ($preseason)
		?>
	</div>
	<div id="center-column" class="dashboard">
        <div class='textbox'>
	    <table cellpadding="0" cellspacing="0">
	    <tr class='title'>
	    	<td style='padding:6px'>Admin Functions</td>
	    </tr>
	    <tr>
	    	<td class="hsc2_l" style='padding:6px'>
			<h3>League Settings</h3>
            <ul class="iconmenu">
            	<li><?php echo anchor('/league/submit/mode/edit/id/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/notes_edit.png" width="48" height="48" border="0" />'); ?><br />
            	Edit League Details</li>
                <li><?php echo anchor('/league/configInfo/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/window_lock.png" width="48" height="48" border="0" />'); ?><br />
				Review League Settings</li>
                <li><?php echo anchor('/league/avatar/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/image_edit.png" width="48" height="48" border="0" />'); ?><br />
            	League Avatar</li>
                <?php 
				if ($scoring_type == LEAGUE_SCORING_HEADTOHEAD) { ?>
                <li><?php echo anchor('/divisions/showList/league_id/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/folder_edit.png" width="48" height="48" border="0" />'); ?><br />
            	Edit Divisions</li>
                <?php } ?>
                <li><?php echo anchor('/league/teamAdmin/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/folder_edit.png" width="48" height="48" border="0" />'); ?><br />
				Edit Teams/Owners</li>
				<?php if ($draftEnabled && $draftStatus >= 5) { ?>
				<li><?php echo anchor('/league/rosters/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/database_search.png" width="48" height="48" border="0" />'); ?><br />
				Check Teams Roster Status
					<?php if (isset($rosterIssues) && $rosterIssues > 0) { ?>
						<span class="badge"><?php echo($rosterIssues); ?></span>
					<?php }	?>
				</li></li>
				<?php } ?>
            </ul> 
            <br clear="all" /><br />
            <h3>General Functions</h3> 
            <ul class="iconmenu">  
            	<li><?php echo anchor('/league/leagueInvites/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/users.png" width="48" height="48" border="0" />'); ?><br />
            	Pending Owner Invites/Requests
				<?php if (isset($invites_requets) && $invites_requets > 0) { ?>
					<span class="badge"><?php echo($invites_requets); ?></span>
				<?php }	?>
				</li>
                <?php if ($this->params['config']['useWaivers'] == 1) { ?>
                <li><?php echo anchor('/league/waiverClaims/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/user_accept.png" width="48" height="48" border="0" />'); ?><br />
            	Pending Waiver Claims
				<?php if (isset($waiver_claims) && $waiver_claims > 0) { ?>
					<span class="badge"><?php echo($waiver_claims); ?></span>
				<?php }	?>
				</li>
                <?php } 
                if ($this->params['config']['useTrades'] == 1) { ?>
                <li><?php echo anchor('/league/tradeReview/id/'.$league_id.'/type/1','<img src="'.$config['fantasy_web_root'].'images/icons/users.png" width="48" height="48" border="0" />'); ?><br />
            	Pending Trades
				<?php if (isset($trades) && $trades > 0) { ?>
					<span class="badge"><?php echo($trades); ?></span>
				<?php }	?>
				</li>
                <?php }  ?>
             </ul>
             
             <?php if ($draftEnabled && $draftStatus < 5) { ?>
             <br clear="all" /><br />
			 
             <h3>Draft Functions</h3>
             <ul class="iconmenu">
                <?php if ($draftStatus <= 2) { ?>
                <li><?php echo anchor('/draft/admin/league_id/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/calendar.png" width="48" height="48" border="0" />'); ?><br />
            	Draft Settings</li>
				<?php 
				} 
				if ($draftEnabled) {
					if ($draftTimer && ($draftStatus >= 1 && $draftStatus < 4)) { ?>
						<li><?php echo anchor('/league/updateDraftSchedule/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/page_process.png" width="48" height="48" border="0" />'); ?><br />
						Update Draft Schedule</li>
	                <?php 
					}
					if ($draftStatus == -1) { ?>
						<li><?php echo anchor('/league/initlaizeDraft/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/configure.png" width="48" height="48" border="0" />'); ?><br />
						Initialize Draft</li>
	                <?php }
					if ($draftStatus >= 1 && $draftStatus <= 2) { ?>
	                    <li><?php echo anchor('/draft/draftOrder/league_id/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/windows.png" width="48" height="48" border="0" />'); ?><br />
	                    Edit Draft Order</li>
	                <?php } // END if
						if ($draftStatus >= 1 && $draftStatus < 4) { ?>
	                        <li><?php echo anchor('/draft/teamSettings/league_id/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/application_edit.png" width="48" height="48" border="0" />'); ?><br />
	                        Edit Team Settings</li>
	                        <li><?php echo anchor('/draft/load/league_id/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/process.png" width="48" height="48" border="0" />',array('rel'=>'sim')); ?><br />
	                        Manage Draft</li>
	                <?php 	} // END if
					if ($draftStatus == 4) { ?>
	                    <li><?php echo anchor('/draft/completeDraft/league_id/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/accept.png" width="48" height="48" border="0" />',array('rel'=>'sim')); ?><br />
	                    Complete Draft</li>
	                <?php }  // END if
				} // END if 
				//if (($draftEnabled && $draftStatus > 0 && $draftStatus < 3) ||$accessLevel == ACCESS_ADMINISTRATE) { ?>
                    <!--li><?php //echo anchor('/league/autoDraftLeague/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/process.png" width="48" height="48" border="0" />',array('rel'=>'sim')); ?><br />
                    Use Auto Draft Bypass</li-->
                <?php 
				//} // END if
				if (ACCESS_ADMINISTRATE && $draftStatus >= 1 && $draftStatus < 5) { ?>
                <li><?php echo anchor('/league/resetDraft/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/repeat.png" width="48" height="48" border="0" />'); ?><br />
            	Reset Draft</li><br />
                <?php }
			?>
			</ul></b>
            <?php } ?>
			</td>
		</tr>
		</table>
		</div>
	</div>
        
	<div id="right-column">
		<?php
		if (isset($tradeLists) && sizeof($tradeLists) > 0) {
		?>
		<div class='textbox right-column' style="margin-left:10px;">
		<table cellpadding="3" cellspacing="0">
		<tr class='title'>
			<td>League Transaction Information</td>
		</tr>
		<tr>
			<td>
			<b>Trades</b><br />
				<?php
			$drawn = false;
			if (isset($tradeLists['forAppproval']) && sizeof($tradeLists['forAppproval']) > 0) {
				print('<span class="warn">Approval NOTICE</span><br />There are currently <b>'.sizeof($tradeLists['forAppproval']).'</b> trades requiring commissioner approval.<br />');
				$drawn = true;
			} // END if
			if (isset($tradeLists['inLeagueReview']) && sizeof($tradeLists['inLeagueReview']) > 0) {
				print('There are currently <b>'.sizeof($tradeLists['inLeagueReview']).'</b> trades waiting under review by the league .<br />');
				$drawn = true;
			} // END if
			if ($drawn) {
				print('<br />You can review, approve and/or reject trades on the '.anchor('/league/tradeReview/'.$league_id,'Trade Review Page').'<br />');
			} else {
				print("There are no trades pending league or admin review at this time.");
			}
			?>
			</td>
		</tr>
		</table>
		</div>
		<br clear="all" /><br />
		<?php
		} // END if
		/*-----------------------------------------------------
		/	WAIVER ORDER BOX
		/	Displays if Waivers are enabled
		/---------------------------------------------------*/
		if ((isset($useWaivers) && $useWaivers == 1) && ($draftEnabled && $draftStatus >= 5)) {
		?>
		<div class='textbox right-column' style="margin-left:10px;">
		<table cellpadding="3" cellspacing="0">
		<thead>
		<tr class='title'>
			<td colspan="2">Period <?php echo($current_period); ?> Waiver Order</td>
		</tr>
		<tr class="headline">
			<td>Rank</td>
			<td>Team</td>
		</tr>
		</thead>
		<tbody>
		<?php
		$rowcount = 0;
			foreach ($waiverOrder as $teamInfo) {
			if (($rowcount %2) == 0) { $color = "#EAEAEA"; } else { $color = "#FFFFFF"; } // END if
		?>
		<tr style="background-color:<?php echo($color); ?>">
			<td><?php echo($teamInfo['waiver_rank']); ?></td>
			<td><?php echo($teamInfo['teamname']." ".$teamInfo['teamnick']); ?></td>
		</tr>
		<?php
				$rowcount++;
			} // END foreach ($waiverOrder as $teamInfo)
		?>
		</tbody>
		</table>
		</div>
		<br clear="all" />
		<div style="float:left;margin-left:10px;">Waiver Order is updated following each Sim.</div>
		<?php
		} // END if ((isset($useWaivers) && $useWaivers == 1))
		?>
	</div>
    <p>&nbsp;</p>