<?php
$lang['sim_success'] = '<span class="success_txt">Success</span>';
$lang['sim_error'] = '<span class="error_txt">Error</span>';
$lang['sim_notice'] = '<span class="notice_txt">Notice</span>';

$lang['sim_process_start'] = 'Beginning Sim processing. Started at [TIME_START]<br /><br />';
$lang['sim_period_id'] = 'Sim Period Id = [PERIOD_ID]<br /><br />';
$lang['sim_league_processing'] = '<b>Processing sim scoring results for [LEAGUE_NAME].</b><br />';

$lang['sim_player_scoring'] = '<b>Processing Player Scoring Results.</b><br />';
$lang['sim_player_count'] = '[PLAYER_COUNT] players loaded for stat proicessing.<br />';
$lang['sim_players_processed_result'] = '[PLAYER_COUNT] players scoring stats processed.<br /><br />';

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
$lang['sim_process_trades_count'] = '[COUNT] Expired trades processed.<br />';
$lang['sim_process_waivers'] = 'Processing waiver claims.<br />';
$lang['sim_process_waivers_count'] = '[COUNT] Waiver claims processed.<br />';

$lang['sim_include_errors'] = "<b>Error message(s) recieved:</b><br />";

// SIM ERRORS, display as list items
$lang['sim_no_scoring_rules'] = "<li>No scoring rules were found.</li>";

$lang['sim_process_finished'] = '<h3>Sim processing Completed.</h3><b>Completed at:</b> [TIME_END].<br /><b>Execution Time:</b> [SIM_TIME] seconds.<br /><br />';

$lang['sim_ajax_success'] = 'Sim processing Completed succesffully<br />!';
$lang['sim_ajax_error'] = 'An error occured during Sim processing<br />!';