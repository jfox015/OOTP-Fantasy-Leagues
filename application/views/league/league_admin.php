    <script type="text/javascript">
    $(document).ready(function(){		   
		
	});
    </script>
    <div id="center-column" class="dashboard">
        <div class="top-bar"> <h1><?php echo $subTitle; ?></h1></div>
        <br class="clear" />
		
        <div class='textbox'>
	    <table cellpadding="0" cellspacing="0" border="0" style="width:625px;">
	    <tr class='title'>
	    	<td style='padding:6px'>Admin Functions</td>
	    </tr>
	    <tr>
	    	<td class="hsc2_l" style='padding:6px'>
			<h3>Pre-Season Functions</h3>
            <ul class="iconmenu">
            	<?php
                if ((isset($league_info) && $league_info->current_date <= $league_info->start_date) || !isset($league_info)) { ?>
				<li><?php echo anchor('/league/submit/mode/edit/id/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/notes_edit.png" width="48" height="48" border="0" />'); ?><br />
            	Edit League Details</li>
                <?php } else { ?>
				<li><?php echo anchor('/league/configInfo/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/window_lock.png" width="48" height="48" border="0" />'); ?><br />
				Review League Settings</li><?php } ?>
					
                <li><?php echo anchor('/league/avatar/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/image_edit.png" width="48" height="48" border="0" />'); ?><br />
            	League Team Avatar</li>
                <?php 
				if ($scoring_type == LEAGUE_SCORING_HEADTOHEAD) { ?>
                <li><?php echo anchor('/divisions/showList/league_id/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/folder_edit.png" width="48" height="48" border="0" />'); ?><br />
            	Edit Divisions</li>
                <?php } ?>
                <li><?php echo anchor('/league/teamAdmin/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/folder_edit.png" width="48" height="48" border="0" />'); ?><br />
            	Edit Teams</li>
            </ul> 
            <br clear="all" /><br />
            <h3>General Functions</h3> 
            <ul class="iconmenu">  
            	<li><?php echo anchor('/league/leagueInvites/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/users.png" width="48" height="48" border="0" />'); ?><br />
            	Pending Owner Invites</li>
                <?php if ($this->params['config']['useWaivers'] == 1) { ?>
                <li><?php echo anchor('/league/waiverClaims/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/user_accept.png" width="48" height="48" border="0" />'); ?><br />
            	Pending Waiver Claims</li>
                <?php } 
                if ($this->params['config']['useTrades'] == 1) { ?>
                <li><?php echo anchor('/league/tradeReview/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/users.png" width="48" height="48" border="0" />'); ?><br />
            	Pending Trades</li>
                <?php }  ?>
             </ul>
             
             <?php if ($draftEnabled && $draftStatus < 5) { ?>
             <br clear="all" /><br />
             <h3>Draft Functions</h3>
             <ul class="iconmenu">
                <?php if ($draftStatus <= 2) { ?>
                <li><?php echo anchor('/draft/admin/league_id/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/calendar.png" width="48" height="48" border="0" />'); ?><br />
            	Draft Admin</li>
				<?php 
				} 
				if ($draftEnabled) {
					if ($draftStatus == 0) { ?>
						<li><?php echo anchor('/league/initlaizeDraft/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/configure.png" width="48" height="48" border="0" />'); ?><br />
						Initialize Draft</li>
	                <?php }
					if ($draftStatus >= 1 && $draftStatus <= 2) { ?>
	                    <li><?php echo anchor('/draft/draftOrder/league_id/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/windows.png" width="48" height="48" border="0" />'); ?><br />
	                    Set Draft Order</li>
	                <?php } // END if
						if ($draftStatus >= 1 && $draftStatus < 4) { ?>
	                        <li><?php echo anchor('/draft/teamSettings/league_id/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/application_edit.png" width="48" height="48" border="0" />'); ?><br />
	                        Team Settings</li>
	                        <li><?php echo anchor('/draft/load/league_id/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/process.png" width="48" height="48" border="0" />',array('rel'=>'sim')); ?><br />
	                        Manage Draft</li>
	                <?php 	} // END if
					if ($draftStatus == 4) { ?>
	                    <li><?php echo anchor('/draft/completeDraft/league_id/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/accept.png" width="48" height="48" border="0" />',array('rel'=>'sim')); ?><br />
	                    Complete Draft</li>
	                <?php }  // END if
				} // END if 
				if (($draftEnabled && $draftStatus > 0 && $draftStatus < 3) ||$accessLevel == ACCESS_ADMINISTRATE) { ?>
                    <li><?php echo anchor('/league/autoDraftLeague/'.$league_id,'<img src="'.$config['fantasy_web_root'].'images/icons/process.png" width="48" height="48" border="0" />',array('rel'=>'sim')); ?><br />
                    Use Auto Draft Bypass</li>
                <?php 
				} // END if
				if (ACCESS_ADMINISTRATE && $draftStatus < 5) { ?>
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
        
        <p>&nbsp;</p>
    </div>
    <p /><br />