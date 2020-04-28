<?php

// DASHBOARD
$lang['dash_settings_pre_add_leagues'] = 'General league functions are available once one or more fantasy leagues have been created.';
$lang['dash_seasonfuncs_pre_add_leagues'] = 'More functions will be available once one or more fantasy leagues have been created.';
// DASH ERRORS
$lang['dash_error_no_players'] = '<strong>Warning:</strong> No players have been loaded into the database. Many site functions will not work correctly until this is done.';
$lang['dash_error_no_players_short'] = 'No Players loaded';

$lang['sim_success'] = '<span class="success_txt">Success</span>';
$lang['sim_error'] = '<span class="error_txt">Error</span>';
$lang['sim_notice'] = '<span class="notice_txt">Notice</span>';

$lang['sim_process_start'] = 'Beginning Sim processing. Started at [TIME_START]<br /><br />';
$lang['sim_period_id'] = 'Sim Period Id = [PERIOD_ID]<br /><br />';
$lang['sim_league_processing'] = '<b>Processing sim scoring results for [LEAGUE_NAME].</b><br />';

$lang['sim_player_scoring'] = '<b>Processing Player Scoring Results.</b><br />';
$lang['sim_player_count'] = '[PLAYER_COUNT] players loaded for stat proicessing.<br />';
$lang['sim_players_processed_result'] = '[PLAYER_COUNT] players scoring stats processed.<br /><br />';

$lang['sim_player_ratings'] = '<b>Processing Player Rating Update.</b><br />';
$lang['sim_player_rating_count'] = '[PLAYER_COUNT] players loaded for ratings update.<br />';
$lang['sim_player_rating_period'] = 'Ratings start on [START_DATE] and end on [END_DATE] and enocmass [DAYS] days.<br />';

$lang['sim_player_rating_statload'] = 'Loading Stats for compiling AVG and STDDEV values.<br />';
$lang['sim_player_rating_statcount'] = 'Loaded [BATTING_STAT_COUNT] Stats for batters and [PITCHING_STAT_COUNT] Stats for pitchers.<br />';

$lang['sim_players_rating_processing'] = 'Processing Individual player ratings.<br /><br />';

$lang['sim_players_rating_result'] = '[PLAYER_COUNT] players ratings processed.<br /><br />';
$lang['sim_players_rating_no_players'] = 'No players ratings were processed.<br /><br />';

$lang['sim_rule_count'] = '[RULES_COUNT] scoring rules loaded for sim.<br /><br />';

$lang['sim_no_teams'] = '<b>Error:</b> No teams were found.<br /><br />';
$lang['sim_team_count'] = '[TEAM_COUNT] teams were found<br />';

$lang['sim_roster_validation_title'] = 'Roster Validation Errors:<ul>';
$lang['sim_roster_validation_error'] = "<li><b>[TEAM_NAME]</b> of the <i>[LEAGUE_NAME]</i> had an invalid roster. No results will be recorded for tjhis team.</li>";
$lang['sim_roster_validation_postfix'] = '</ul><br />';

$lang['sim_process_scoring'] = 'Processing team scoring results<br />';
$lang['sim_process_scoring_teams'] = '[TEAM_COUNT] Teams loaded for league [LEAGUE_ID].<br />';

$lang['sim_process_records'] = 'Processing team records<br />';

$lang['sim_process_copy_rosters'] = 'Copying rosters to the next scoring period.<br />';

$lang['sim_process_trades'] = 'Processing Expiring trade offers.<br />';
$lang['sim_process_trades_error'] = "Errors occured during trade expirations processing.<br />";
$lang['sim_increment_trades'] = 'Incrementing trade offers currently in &quot;Offered&quot; status to next scoring period.<br />';
$lang['sim_increment_trades_error'] = "Errors occured during trade incremtnal processing.<br />";
$lang['sim_process_trades_to_expire_count'] = '[COUNT] trade records returned to be expired.<br />';
$lang['sim_process_trades_to_increment_count'] = '[COUNT] trade records returned to be incremented.<br />';
$lang['sim_process_trades_count'] = '[COUNT] Expired trades processed.<br />';
$lang['sim_process_trades_emails'] = '[COUNT] Trade Expiration notice emails sent.<br />';
$lang['sim_increment_trades_count'] = '[COUNT] Trades inremented to scoring period [PERIOD_ID].<br />';
$lang['sim_process_waivers'] = 'Processing waiver claims.<br />';
$lang['sim_process_waivers_count'] = '[COUNT] Waiver claims processed.<br />';

$lang['sim_include_errors'] = "<b>Error message(s) recieved:</b><br />";

// SIM ERRORS, display as list items
$lang['sim_no_scoring_rules'] = "<li>No scoring rules were found.</li>";

$lang['sim_process_finished'] = '<h3>Sim processing Completed.</h3><b>Completed at:</b> [TIME_END].<br /><b>Execution Time:</b> [SIM_TIME] seconds.<br /><br />';

$lang['sim_ajax_success'] = 'Sim processing Completed succesffully<br />!';
$lang['sim_ajax_error'] = 'An error occured during Sim processing<br />!';