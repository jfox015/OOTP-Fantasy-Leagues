<link media="screen" rel="stylesheet" href="<?php echo($config['fantasy_web_root']); ?>css/colorbox.css" />
	<script type="text/javascript" src="<?php echo($config['fantasy_web_root']); ?>js/jquery.md5.js"></script>
	<script src="<?php echo($config['fantasy_web_root']); ?>js/jquery.colorbox.js"></script>
	<script type="text/javascript" charset="UTF-8">
	var ajaxWait = '<img src="<?php echo($config['fantasy_web_root']); ?>images/icons/ajax-loader.gif" width="28" height="28" align="absmiddle" />&nbsp;Operation in progress. Please wait...';
	var responseError = '<img src="<?php echo($config['fantasy_web_root']); ?>images/icons/icon_fail.png" width="24" height="24" align="absmiddle" />&nbsp;';
	var fader = null;
	var refreshAfterUpdate = false;
	var redirectAfterUpdate = null;
	var alertAfterUpdate = null;
	var fileWarning = <?php print((isset($missingFiles) && sizeof($missingFiles) > 0) ? "true" : "false"); ?>;

	$(document).ready(function(){
		$('a[rel=avail]').click(function (event) {
			refreshAfterUpdate = true;
			runAjax("<?php echo($config['fantasy_web_root']); ?>admin/availablePlayers"+cacheBuster());
			event.preventDefault();
		});
		$('a[rel=upavail]').click(function (event) {
			runAjax("<?php echo($config['fantasy_web_root']); ?>admin/updatePlayers"+cacheBuster());
			event.preventDefault();
		});
		$('a[rel=elidg]').click(function (event) {
			runAjax("<?php echo($config['fantasy_web_root']); ?>admin/elidgibility"+cacheBuster());
			event.preventDefault();
		});
		$('a[rel=rating]').click(function (event) {
			runAjax("<?php echo($config['fantasy_web_root']); ?>admin/playerRatings"+cacheBuster());
			event.preventDefault();
		});
		$('a[rel=sched]').click(function (event) {
			runAjax("<?php echo($config['fantasy_web_root']); ?>admin/scoringSchedule"+cacheBuster());
			event.preventDefault();
		});
		$('a[rel=games]').click(function (event) {
			runAjax("<?php echo($config['fantasy_web_root']); ?>admin/generateSchedules"+cacheBuster());
			event.preventDefault();
		});
		$('a[rel=runWaivers]').click(function (event) {
			refreshAfterUpdate = true;
			runAjax("<?php echo($config['fantasy_web_root']); ?>admin/manualWaivers"+cacheBuster());
			event.preventDefault();
		});
		$('a[rel=sql]').click(function (event) {
			var proceed = true;
			if (fileWarning) {
				proceed = confirm("There are MySQL data files missing. Are you sure you want to continue with loading the OOTP game data? This will result in an incomplete load and potenitlaly cause error on the web site.");
			}
			if (proceed) {
				refreshAfterUpdate = true;
				runAjax("<?php echo($config['fantasy_web_root']); ?>admin/loadSQLFiles"+cacheBuster());
			} else {
				$('div#activeStatus').addClass('notice');
				$('div#activeStatus').html('Operation Cancelled.');
				setTimeout('fadeStatus("active")',5000);
			}
			event.preventDefault();
		});
		$('a[rel=dataUpdate]').click(function (event) {
			refreshAfterUpdate = true;
			runAjax("<?php echo($config['fantasy_web_root']); ?>admin/dataUpdate"+cacheBuster());
			event.preventDefault();
		});
		$('a[rel=configUpdate]').click(function (event) {
			refreshAfterUpdate = true;
			runAjax("<?php echo($config['fantasy_web_root']); ?>admin/configUpdate"+cacheBuster());
			event.preventDefault();
		});
		$('a[rel=reset]').click(function (event) {
			refreshAfterUpdate = true;
			if (confirm("Are you sure you want to perform this operation? This will reset the entire season to it's starting point and wipe out ALL season stats, transactions and fantasy data.")) {
				if (confirm("Are you sure you want to do this? This operation will wipe out your season and CANNOT be undone."))
					runAjax("<?php echo($config['fantasy_web_root']); ?>admin/resetSeason"+cacheBuster());
			}
			event.preventDefault();
		});
        $('a[rel=remove]').click(function (event) {
            refreshAfterUpdate = true;
			alertAfterUpdate = "All fantasy tables have been deleted. Thank you for using the mod. You will redirected to a new page in 5 second.";
            redirectAfterUpdate = "<?php echo($config['fantasy_web_root']); ?>media/nodb.php";
		    if (confirm("Are you sure you want to perform this operation? This will remove ALL the tables in the database and render the mod unusable unless you reinstall.")) {
                if (confirm("Are you sure you want to do this? This operation will wipe out the entire set of fantasy database tables and CANNOT be undone."))
                    runAjax("<?php echo($config['fantasy_web_root']); ?>admin/uninstall"+cacheBuster());
            }
            event.preventDefault();
        });
		$('a[rel=resetSim]').click(function (event) {
			refreshAfterUpdate = true;
			if (confirm("Are you sure you want to perform this operation? This will reset the entire season to it's starting point and wipe out ALL season stats, transactions and fantasy data.")) {
				runAjax("<?php echo($config['fantasy_web_root']); ?>admin/resetSim"+cacheBuster());
			}
			event.preventDefault();
		});
		$('a[rel=sim]').click(function (event) {
			refreshAfterUpdate = true;
			runAjax("<?php echo($config['fantasy_web_root']); ?>admin/processSim"+cacheBuster());
			event.preventDefault();
		});
	});
	function runAjax (url) {
		$.colorbox({html:ajaxWait});
		$.getJSON(url, function(data){
			error = false;
			if (data.status.indexOf(":") != -1) {
				var status = data.status.split(":");
				$('div#activeStatus').addClass(status[0].toLowerCase());
				var response = status[1];
				if (status[0].toLowerCase() == "error") {
					response = responseError + response;
					error = true;
				}
				$('div#activeStatus').html(response);
			} else {
				$.colorbox.close();
				$('div#activeStatus').addClass('success');
				$('div#activeStatus').html('Operation Completed Successfully');
				$('div#activeStatusBox').fadeIn("slow");
			}
			if (!error) {
				if (alertAfterUpdate != null)
					alert(alertAfterUpdate);
				if (refreshAfterUpdate)
					setTimeout('refreshPage()',3000);
			}
		});
	}
	function cacheBuster() {
		var date = new Date();
		var hash = $.md5(Math.floor(Math.random())+date.toUTCString()).toString();
		return "/uid/"+hash.substr(0,16);
	}
	function fadeStatus(type) {
		$('div#'+type+'StatusBox').fadeOut("normal",function() { clearTimeout(fader); $('div#'+type+'StatusBox').hide(); });
	}
	function refreshPage() {
		if (redirectAfterUpdate == null) url = '<?php echo($_SERVER['PHP_SELF']); ?>'; else url = redirectAfterUpdate;
		document.location.href = url;
	}
</script>
<div id="single-column" class="dashboard">
<h1>Welcome to the Dashboard, <?php echo($name); ?></h1>
    <?php if (isset($email) && !empty($email)) { ?>
    Your are currently logged in as: <b><?php echo($email); ?></b>
    <?php } ?>
    <br />
	<?php
    if (isset($message) && !empty($message)) { ?>
    <?php echo($message); ?>
    <br />
    <?php } ?>
</div>

<div id="center-column" class="dashboard">
<?php if (isset($installWarning)) { ?>
<span class="error"><?php echo($install_message); ?></span>
<br />
<?php } else { ?>

    <div class='textbox'>
    <table cellpadding="0" cellspacing="0" style="width:625px;">
    <tr class='title'>
    	<td style='padding:3px'>Admin Functions</td>
    </tr>
    <tr>
    	<td class="hsc2_l" style='padding:3px'>
		<div id="activeStatusBox"><div id="activeStatus"></div></div>
        <?php
        // IN-SEASON FUNCTIONS
		if (isset($league_info)) {
			$currDate = strtotime($league_info->current_date);
			$startDate = strtotime($league_info->start_date);
		}
		$last_process_time = 1;
		$last_sql_load_time = 1;
		if (isset($config['last_process_time']) && isset($config['last_sql_load_time'])) {
			$last_process_time = new DateTime($config['last_process_time']);
			$last_sql_load_time = new DateTime($config['last_sql_load_time']);
		}

		if ($in_season && isset($league_info) && $currDate > $startDate && ((isset($configCurrPeriodStart) && $currDate>=$configCurrPeriodStart) && (isset($configCurrPeriodStart) && $currDate<=$configCurrPeriodEnd))) { ?>
		<h3>Regular Season Functions</h3>
		<b class="error_txt"><i>NOTE:</i></b> Depending on the size of your league, some of the following functions may consume a good deal of time and server resources as all players in the OOTP game will be processed. Please be
		patient when running these operations and refer to the Sim Summary log for explanation of and help with any errors.
		<br clear="all" /><br />
		<ul class="iconmenu">
			<?php if ($last_process_time < $last_sql_load_time && $league_info->current_date > $league_info->start_date && (isset($leagues) && sizeof($leagues) > 0)) { ?>
			<li><?php echo anchor('#','<img src="'.$config['fantasy_web_root'].'images/icons/process.png" width="48" height="48" />',array('rel'=>'sim')); ?><br />
			Process Current Sim Results</li><?php } ?>
			<?php
			// LEAGUE SETTINGS
			if (isset($leagues) && sizeof($leagues) > 0) { ?>
			<li><?php echo anchor('#','<img src="'.$config['fantasy_web_root'].'images/icons/repeat.png" width="48" height="48" />',array('rel'=>'upavail')); ?><br />
			Update Players</li>
			<?php
			} else { ?>
			<li><img src="<?php echo(PATH_IMAGES); ?>icons/stock_new-appointment.png"
			width="24" height="24" alt="" title="" class="floated_icon" /><div
			class="floated_caption"><?php print($this->lang->line('dash_seasonfuncs_pre_add_leagues')); ?></div>
			</li>
			<?php } // END if
			?>
			<li><?php echo anchor('#','<img src="'.$config['fantasy_web_root'].'images/icons/baseball-icon.png" width="48" height="48" />',array('rel'=>'elidg')); ?><br />
			Update Player Elidgibility</li>
			<li><?php echo anchor('#','<img src="'.$config['fantasy_web_root'].'images/icons/baseball-icon.png" width="48" height="48" />',array('rel'=>'rating')); ?><br />
			Update Player Ratings</li>
			<?php
			 if ($config['last_process_time'] > $config['last_sql_load_time'] && $league_info->current_date > $league_info->start_date && $summary_size > 0) { ?>
			<li><?php echo anchor('#','<img src="'.$config['fantasy_web_root'].'images/icons/database_remove.png" width="48" height="48" />',array('rel'=>'resetSim')); ?><br />
			Undo last Sim</li>
			<?php
			}
			if($currPeriod['manual_waivers'] == -1) { ?>
			<li><?php echo anchor('#','<img src="'.$config['fantasy_web_root'].'images/icons/page_swap.png" width="48" height="48" />',array('rel'=>'runWaivers')); ?><br />
			Process Waivers</li>
			<?php
			}
			?>
		</ul>
        <br clear="all" /><br />
		<?php }


        if (isset($league_info) && $currDate <= $startDate) { ?>
        <h3>Pre-Season Functions</h3>
        <?php if ($playerCount == 0) {
		echo('<br /><span class="error" style="margin:0px; width:90%;">'.$this->lang->line('dash_error_no_players').'</span><br />'); } ?>
        <ul class="iconmenu">
           	<?php
			$hidden_funcs = false;
			?>
            <li><?php echo anchor('#','<img src="'.$config['fantasy_web_root'].'images/icons/database_remove.png" width="48" height="48" />',array('rel'=>'reset')); ?><br />
            Reset game to Pre-season</li>
            <li><?php echo anchor('#','<img src="'.$config['fantasy_web_root'].'images/icons/users.png" width="48" height="48" />',array('rel'=>'avail')); ?><br />
            Import Available Players</li>
            <li><?php echo anchor('#','<img src="'.$config['fantasy_web_root'].'images/icons/calendar_empty.png" width="48" height="48" />',array('rel'=>'sched')); ?><br />
            Generate Scoring Schedule</li>
			<li><?php echo anchor('#','<img src="'.$config['fantasy_web_root'].'images/icons/baseball-icon.png" width="48" height="48" />',array('rel'=>'elidg')); ?><br />
			Update Player Elidgibility</li>
			<li><?php echo anchor('#','<img src="'.$config['fantasy_web_root'].'images/icons/baseball-icon.png" width="48" height="48" />',array('rel'=>'rating')); ?><br />
			Update Player Ratings</li>
            <?php
			// LEAGUE SETTINGS
			if (isset($leagues) && sizeof($leagues) > 0) { ?>
            <li><?php echo anchor('#','<img src="'.$config['fantasy_web_root'].'images/icons/calendar.png" width="48" height="48" />',array('rel'=>'games')); ?><br />
            Generate Head-to-Head Schedules</li>
            <?php
			} else { ?>
            <li><img src="<?php echo(PATH_IMAGES); ?>icons/stock_new-appointment.png"
            width="24" height="24" alt="" title="" class="floated_icon" /><div
            class="floated_caption"><?php print($this->lang->line('dash_settings_pre_add_leagues')); ?></div>
            </li>
			<?php } // END if
			?>
        </ul>
        <br clear="all" /><br />
        <?php
		}  // END if

		// DATABASE FUNCTIONS
        ?>

        <h3>File/Database Functions</h3>
        <?php
		if (!file_exists($this->params['config']['sql_file_path']) || !is_readable($this->params['config']['sql_file_path'])) {
			echo('<span class="error"><strong>Warning:</strong> The SQL file path on record either does not exist or cannot be read. The SQL file folder must be setup correctly before you can proceed with uploading OOTP data to the site.</span>');
		} else { ?>
        Using <strong>Load All SQL Data Files</strong> below may take serveral minutes to load all your OOTP data files. Please be patient when running this function. For more precise control over what files are loaded, use the <strong>Load Individual SQL Files</strong> option instead.
        <ul class="iconmenu">
            <!--li><?php echo anchor('admin/uploadFiles','<img src="'.$config['fantasy_web_root'].'images/icons/database_up.png" width="48" height="48" />'); ?><br />
            Upload SQL Files</li-->
			<li><?php echo anchor('#','<img src="'.$config['fantasy_web_root'].'images/icons/database_up.png" width="48" height="48" />',array('rel'=>'sql')); ?><br />
            Load All SQL Data Files</li>
			<li><?php echo anchor('admin/listSQLFiles','<img src="'.$config['fantasy_web_root'].'images/icons/database_search.png" width="48" height="48" />'); ?><br />
            Load Individual SQL Files</li>

        </ul>
        <?php } ?>
        <br clear="all" /><br />
        <h3>Site/Fantasy Management</h3>
        <?php if (isset($settingsError) && !empty($settingsError)) { ?>
        <span class="error"><?php echo($settingsError); ?></span><br />
        <?php } ?>
		<ul class="iconmenu">
			<li><?php echo anchor('admin/configGame','<img src="'.$config['fantasy_web_root'].'images/icons/window_edit.png" width="48" height="48" />'); ?><br />
            Global Settings</li>
			<?php 
			// SETTING OPTIONS (PRE-SEASON ONLY)
			if ((isset($league_info) && $currDate <= $startDate) || !isset($league_info)) { ?>
            <li><?php echo anchor('admin/configFantasy','<img src="'.$config['fantasy_web_root'].'images/icons/window_edit.png" width="48" height="48" />'); ?><br />
            Fantasy Settings</li>
            <li><?php echo anchor('admin/configRosters','<img src="'.$config['fantasy_web_root'].'images/icons/users.png" width="48" height="48" />'); ?><br />
            Rosters Rules Settings</li>
			<li><?php echo anchor('admin/configScoringRules','<img src="'.$config['fantasy_web_root'].'images/icons/application_edit.png" width="48" height="48" />'); ?><br />
            Scoring Rules Settings</li>
            <?php
			} else {
			// SETTING REVIEW (REGULAR SEASON)
			?>
            <li><?php echo anchor('admin/configInfo','<img src="'.$config['fantasy_web_root'].'images/icons/window_lock.png" width="48" height="48" />'); ?><br />
            Review Fantasy Settings</li>
            <?php
			} // END if
			?>
			<li><?php echo anchor('admin/configSocial','<img src="'.$config['fantasy_web_root'].'images/icons/facebook-64x64.png" width="48" height="48" />'); ?><br />
            Social Media Settings</li>
            <?php
			if (defined('ENVIRONMENT') && ENVIRONMENT != "production") { ?>
			<li><?php echo anchor('admin/configOOTP','<img src="'.$config['fantasy_web_root'].'images/icons/window_edit.png" width="48" height="48" />'); ?><br />
            Date/Scoring Period Settings</li>
			<?php
			} // END if
			?>
			<li><?php echo anchor('/search/doSearch/leagues','<img src="'.$config['fantasy_web_root'].'images/icons/notes_edit.png" width="48" height="48" />'); ?><br />
			Manage Leagues</li>
			<li><?php echo anchor('/search/doSearch/news','<img src="'.$config['fantasy_web_root'].'images/icons/icon_news_globe.png" width="48" height="48" />'); ?><br />
			Manage News</li>
			
        </ul>
        <br clear="all" /><br />
        <h3>Tools and Utilities</h3>
        <ul class="iconmenu">
        	<li><?php echo anchor('member','<img src="'.$config['fantasy_web_root'].'images/icons/user.png" width="48" height="48" />'); ?><br />
            Manage Members</li>
            <li><?php echo anchor('admin/userActivations','<img src="'.$config['fantasy_web_root'].'images/icons/users.png" width="48" height="48" />'); ?><br />
			Users requiring activation
			<?php
			if (isset($requiring_activation) && $requiring_activation > 0) { ?>
				<span class="badge"><?php echo($requiring_activation); ?></span>
			<?php
			}
			?>
			</li>
            <li><?php echo anchor('admin/configAbout','<img src="'.$config['fantasy_web_root'].'images/icons/window_edit.png" width="48" height="48" />'); ?><br />
            Edit &quot;About&quot; Page</li>
            <li><?php echo anchor('#','<img src="'.$config['fantasy_web_root'].'images/icons/database_remove.png" width="48" height="48" />',array('rel'=>'remove')); ?><br />
            Uninstall Database</li>
            <li></li>
            <li></li>
            <li></li>
        </ul>

        <br clear="all" /><br />
        <?php
        if (isset($config['bug_db'])) { ?>
        <br clear="all" /><br />
        <h3>Project/Bug Tracking Database</h3>
        <ul class="iconmenu">
            <li><?php echo anchor('/search/projects','<img src="'.$config['fantasy_web_root'].'images/icons/database_search.png" width="48" height="48" />'); ?><br />
            View Project List</li>
            <li><?php echo anchor('/search/bugs','<img src="'.$config['fantasy_web_root'].'images/icons/database_search.png" width="48" height="48" />'); ?><br />
            View Bug Database</li>
            <li><?php echo anchor('/bug_resolve','<img src="'.$config['fantasy_web_root'].'images/icons/process_accept.png" width="48" height="48" />'); ?><br />
            Resolve a bug</li>
        </ul>
        <?php } ?>
        </td>
    </tr>
    </table>
    </div>
    <?php } ?>
</div>

<div id="right-column">

	<?php if (isset($dataUpdate) || isset($configUpdate)) { ?>
	<div class='textbox'>
    <table cellpadding="0" cellspacing="0" style="width:235px;">
    <tr class='title'>
    	<td style='padding:3px; color:#FF0'>UPDATE NOTICE</td>
    </tr>
    <tr>
		<td class="hsc2_l" style='padding:6px'>
        <?php if (isset($dataUpdate) || isset($configUpdate)) { ?>
        <span class="error" style="margin:0px; width:90%;"><b>Update Required</b>
        <br /><br />
        An update is required to your Fantasy League Web Site. The following updates are currently required:
        <ul>
        <?php if (isset($dataUpdate)) { ?>
        	<li><?php echo anchor('#','MySQL database',array('rel'=>'dataUpdate')); ?>.</li>
        <?php } ?>
        <?php if (isset($configUpdate)) { ?>
        	<li><?php echo anchor('#','Site config files',array('rel'=>'configUpdate')); ?>.</li>
        <?php } ?>
        </ul>
        </span><br /><br />
		<?php
		}
		?>
       </td>
    </tr>
    </table>
    </div>
	<?php
	}

	/*------------------------------------------------
	/	LEAGUE STATUS BOX
	/
	/	Added 1.0.3 PROD
	/-----------------------------------------------*/
	$icons = array();
	$icons['error'] = '<img src="'.PATH_IMAGES.'icons/icon_fail.png" width="20" height="20" align="absmiddle" />';
	$icons['success'] = '<img src="'.PATH_IMAGES.'icons/icon_pass.png" width="20" height="20" align="absmiddle" />';
	$icons['info'] = '<img src="'.PATH_IMAGES.'icons/icon_info.gif" width="20" height="20" align="absmiddle" />';
	$class = "success";
	?>
	<div class='textbox statusBox'>
    <table cellpadding="0" cellspacing="0">
    <tr class='title'>
    	<td><?php if ($currDate <= $startDate) { echo("Pre-Season"); } ?> Leagues Status</td>
    </tr>
	<tr>
    	<td>
			<?php 
			// PRE_SEASON CHECKS
			if ($currDate <= $startDate) { ?>
				<!-- TABLES FILES-->
				<?php 
				if ((isset($missingTables) && sizeof($missingTables) != 0) && (isset($missingFiles) && sizeof($missingFiles) > 0)) { 
					$class = "error";
					$message = anchor('admin/listSQLFiles','Required SQL Files missing!');
				} else { 
					$class = "success";
					$message = "All Required SQL Files Present";
				} 
				echo('<span class="'.$class.'">'.$icons[$class].' '.$message.'</span>');
				?>
				<!-- MISSING LOADED  -->
				<?php 
				if (!isset($league_info) && (isset($missingTables) && sizeof($missingTables) > 0)) { 
					$class = "error";
					$message = anchor('admin/listSQLFiles','Required tables missing!');
				} else { 
					$class = "success";
					$message = "All tables loaded";
				} 
				echo('<span class="'.$class.'">'.$icons[$class].' '.$message.'</span>');
				?>
				<!-- PLAYERS LOADED -->
				<?php
				if ($playerCount == 0) {
					$class = "error";
					$message = $this->lang->line('dash_error_no_players_short');
				} else { 
					$class = "success";
					$message = $playerCount." Players Loaded.";
				} 
				echo('<span class="'.$class.'">'.$icons[$class].' '.$message.'</span>');
				?>
				<!-- SCORING PERIODS LOADED -->
				<?php
				if (isset($periodCount) && $periodCount > 0) {
					$class = "success";
					$message = $periodCount." Scoring Periods Loaded.";
				} else { 
					$class = "error";
					$message = "No scoring periods were found";
				} 
				echo('<span class="'.$class.'">'.$icons[$class].' '.$message.'</span>');
				?>
				<!-- LEAGUES ON SITE -->
				<?php
				if (isset($activeleagueCount) && $activeleagueCount > 0) {
					$class = "success";
					$message = $activeleagueCount." Active Fantasy Leagues";
				} else { 
					$class = "error";
					$message = "No active fantasy leagues were found";
				} 
				echo('<span class="'.$class.'">'.$icons[$class].' '.$message.'</span>');
				?>
				<!-- DRAFT STATUSES -->
				<?php 
				if ($activeleagueCount > 0) {
					if ($draftsCompleted < $activeleagueCount) { 
						echo('<span><strong>League Draft Status</strong></span><br />');
						if (isset($draftsNotSet) && count($draftsNotSet) > 0) {
							$class = "error";
							$message = count($draftsNotSet)." Drafts Not Started";
							foreach ($draftsNotSet as $id => $leagueName) {
								$message .= '<br />'.anchor('/league/admin/'.$id, $leagueName);
							}
							echo('<span class="'.$class.'">'.$icons[$class].' '.$message.'</span>');
						}
						if (isset($draftsInProgress) && count($draftsInProgress) > 0) {
							$class = "info";
							$message = count($draftsInProgress)." Drafts in Progress";
							foreach ($draftsInProgress as $id => $leagueName) {
								$message .= '<br />'.anchor('/league/admin/'.$id, $leagueName);
							}
							echo('<span class="'.$class.'">'.$icons[$class].' '.$message.'</span>');
						}
						if (isset($draftsNotFinished) && count($draftsNotFinished) > 0) {
							$class = "error";
							$message = count($draftsNotFinished)." Drafts Run and not Completed";
							foreach ($draftsNotFinished as $id => $leagueName) {
								$message .= '<br />'.anchor('/league/admin/'.$id, $leagueName);
							}
							echo('<span class="'.$class.'">'.$icons[$class].' '.$message.'</span>');
						}
						if ($draftsCompleted > 0) {
							$class = "success";
							$message = $draftsCompleted." Drafts Completed";
							echo('<span class="'.$class.'">'.$icons[$class].' '.$message.'</span>');
						}
						echo('<span style="margin:0; padding: 0; height:2px; background-color:#606060; width:96%;display: block;"></span><br />');
					} else { 
						$class = "success";
						$message = "All Drafts Completed.";
						echo('<span class="'.$class.'">'.$icons[$class].' '.$message.'</span>');
					} 
				}
				?>
				<!-- GAMES GENERATED -->
				<?php
				if ($activeleagueCount > 0) {
					if (isset($missingGames) && $missingGames > 0) {
						$class = "error";
						$message = $missingGames." Leagues missing schedules";
					} else { 
						$class = "success";
						$message = "All League Schedules Created";
					} 
					echo('<span class="'.$class.'">'.$icons[$class].' '.$message.'</span>');
				}
			} else { 
				
			}
			// ROSTERS CHECK
			if (isset($draftsCompleted) && $draftsCompleted > 0 && $draftsCompleted == $activeleagueCount) { 
				if (isset($invalidRosters) && count($invalidRosters) > 0) {
					$class = "error";
					$message = count($invalidRosters)." Leagues have Invalid Rosters";
					foreach ($invalidRosters as $id => $leagueName) {
						$message .= '<br />'.anchor('/league/rosters/'.$id, $leagueName);
					}
				} else {
					$class = "success";
					$message = "All League Rosters are Valid!";
				}
				echo('<span class="'.$class.'">'.$icons[$class].' '.$message.'</span>');
			}
			// RATINGS CHECK
			if (isset($rotisserieCount) && $rotisserieCount > 0) { 
				if (isset($ratingsCount) && $ratingsCount == 0) {
					$class = "error";
					$message = "Player ratings missing";
				} else {
					$class = "success";
					$message = "Player ratings run";
				}
				echo('<span class="'.$class.'">'.$icons[$class].' '.$message.'</span>');
			}
			?>
		</td>
	</tr>
    </table>
    </div>


	<div class='textbox statusBox'>
    <table cellpadding="0" cellspacing="0">
    <tr class='title'>
    	<td style='padding:3px'>Game Stats</td>
    </tr>
    <tr>
    	<td class="hsc2_l" style='padding:6px'>
        <?php if (isset($league_info)) { ?>
        <b>Current League Date:</b><br /> <?php echo(date('m/d/Y',strtotime($league_info->current_date))); ?><br />

        <br /><b>Current Scoring period:</b><br />
		<?php if (isset($currPeriodConfig['id']) && $currPeriodConfig['id'] != -1) {
		echo($currPeriodConfig['id']." ".date('m/d',strtotime($currPeriodConfig['date_start']))." - ".date('m/d',strtotime($currPeriodConfig['date_end']))); ?><br />
      	 <?php } else if (!isset($currPeriodConfig['id']) && $league_info->current_date == $league_info->start_date) {
        echo("1 ".date('m/d',strtotime($league_info->current_date))); ?><br />
        <?php } else { ?>
        The current scoring period will appear once the OOTP season begins.<br />
        <?php } ?>

        <br /><b>Total Scoring periods:</b>
        <?php if (isset($periodCount) && $periodCount != 0) {
			echo($periodCount."<br />");
			echo anchor('admin/configScoringPeriods','View/Edit Scoring Period Schedule')."<br />";
		} ?>
		<?php
		// 	EDIT 1.0.4
		//	SIM SUMMARIES
		if ($summary_size) { ?>
        <br /><b>Sim Processing Summaries:</b> <?php print($summary_size); ?> available.<br />
        <?php echo anchor('admin/simSummaries','View Sim Summaries')."<br />";
		} ?>

        <br /><b>Last SQL Data Upload:</b><br /> <?php echo(date('m/d/Y h:m:s A',strtotime($config['last_sql_load_time']))); ?> <br />
        <br /><b>Last Sim Processed:</b><br /> <?php if ($config['last_process_time'] != EMPTY_DATE_TIME_STR) { echo(date('m/d/Y h:m:s A',strtotime($config['last_process_time']))); }
		else { echo("No scoring periods processed yet."); } ?>
        <br /><br /><b>OOTP Players Loaded:</b> <?php echo($playerCount); ?>
        
        <?php
		} else { 
			// NO DB FILES LOADED YET 
			?>
        <span class="error" style="margin:0px; width:90%;"><strong>League Files not loaded</strong>
        <br /><br />
		You need to load your game's MySQL database files using the <strong>Database Functions</strong> tools on this page to view stats based on your game.</span>
        <?php } ?>
        </td>
    </tr>
    </table>
    </div>
    <br clear="all" />
	<div class='textbox'>
    <table cellpadding="0" cellspacing="0">
    <tr class='title'>
    	<td style='padding:3px'>News</td>
    </tr>
    <tr>
    	<td class="hsc2_l" style='padding:6px'>
        <img src="<?php echo($config['fantasy_web_root']); ?>images/icons/icon_add.gif" width="16" height="16" alt="Add" title="add" align="absmiddle" />
		<?php echo anchor('/news/submit/mode/add/type_id/'.NEWS_FANTASY_GAME, 'Add News for Site'); ?>
        <p>
        <img src="<?php echo($config['fantasy_web_root']); ?>images/icons/icon_add.gif" width="16" height="16" alt="Add" title="add" align="absmiddle" />
		<?php echo anchor('/news/submit/mode/add/type_id/'.NEWS_PLAYER, 'Add Player News'); ?>
       	<p>
        <img src="<?php echo($config['fantasy_web_root']); ?>images/icons/icon_search.gif" width="16" height="16" alt="Add" title="add" align="absmiddle" />
		<?php echo anchor('/search/news/', 'Browse All News'); ?>

        </td>
    </tr>
    </table>
    </div>
    <br clear="all" />
	<div class='textbox statusBox'>
    <table cellpadding="0" cellspacing="0">
    <tr class='title'>
    	<td style='padding:3px'>Fantasy Leagues Overview</td>
    </tr>
    <tr>
    	<td class="hsc2_l">
		<?php if (isset($leagues) && sizeof($leagues) > 0) { ?>
		<table cellpadding="1" cellspacing="0">
		<?php foreach($leagues as $id => $details) { ?>	
		<tr>
			<td>
			<?php
			$icon = 'icon_pass.png';
			$alt = 'Active';
			if ($details['league_status'] == -1) { 
				$icon = 'icon_fail.png';
				$alt = 'Inactive';
			} else if ($details['league_status'] ==2) {
				$icon = 'icon_fail_minor.png';
				$alt = 'Suspended';
			} else if ($details['league_status'] == 3) {
				$icon = 'img_icon_garbage.gif';
				$alt = 'Removed';
			}
			echo('<img src="'.PATH_IMAGES.'icons/'.$icon.'" width="16" height="16" alt="'.$alt.'" title="'.$alt.'"/>'); ?>
			</td>
			<td>
			<?php if ($details['access_type'] != 1) { echo('<img src="'.$config['fantasy_web_root'].'images/icons/lock.png" width="16" height="16" alt="Private" title="Private" />'); } ?>
			</td>
			<td><?php if (isset($details['avatar']) && !empty($details['avatar'])) { ?>
            <img align="absmiddle" width="24" height="24" src="<?php echo(PATH_LEAGUES_AVATARS.$details['avatar']); ?>" alt="<?php echo($details['league_name']); ?>" title="<?php echo($details['league_name']); ?>" />
			<?php } ?></td>
			<td><?php echo(anchor('/league/home/'.$id, $details['league_name'])); ?></td>
		</tr>
		<?php } ?>
		</table>
        <?php } else { ?>
        	<span class="error">No Leagues are available at this time.</span>
        <?php } ?>
        </td>
    </tr>
    </table>
    </div>
	<br clear="all" /><br />
    <div class='textbox statusBox'>
    <table cellpadding="0" cellspacing="0">
    <tr class='title'>
    	<td style='padding:3px'>Fantasy Mod Info</td>
    </tr>
    <tr class='headline'>
    	<td style='padding:3px'>Version Information</td>
    </tr>
    <tr>
    	<td class="hsc2_l" style='padding:6px'>
        <strong>Your Version:</strong>  <?php echo(SITE_VERSION); ?>
        <br />
        <?php
		if (isset($version_check) && is_array($version_check) && sizeof($version_check) > 0) { ?>
        <span class="<?php echo($version_check[0]); ?>">
        <?php echo($version_check[1]); ?>
        </span>
        <?php } ?>
        </td>
    </tr>
    </table>
    </div>
	<br clear="all" /><br />
</div>

<br class="clear" />
