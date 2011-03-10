<?php
/*---------------------------------------
/	TRADES
/--------------------------------------*/
$lang['team_lineup_adjust'] = 'adjust your lineup';
// OOFER SUBMITTED
$lang['team_trade_offer'] = 'To [USERNAME],
<p>[ACCEPTING_TEAM_NAME] has submitted the following trade proposal to you.</p>
<p>
[SEND_PLAYERS]</p>
<p>for:</p>
<p>
[RECEIVE_PLAYERS]</p>
<p>[COMMENTS]</p>
<p>[EXPIRES]</p>
';
// ACCEPTED
$lang['team_trade_accepted_no_approvals'] = 'To [USERNAME],
<p>Your trade offer to the [ACCEPTING_TEAM_NAME] has been accepted. All players have been assigned to their new rosters.</p>
<p>
[COMMENTS]</p>
<p>
You can should view and [URL_LINEUP] immediately to assure your rosters are still legal before the next sim occurs. This trade will now appear in the league transactions list as well.</p>';
// PENDING LEAGUE APPROVAL
$lang['team_trade_pending_league_approval'] = 'To [USERNAME],
<p>Your trade offer to the owner of the [ACCEPTING_TEAM_NAME] has been accepted but is now contringent uppon league approval to be finalized. Other team owners now have [PROTEST_DAYS] to log a protest against this trade. If it recieves the minimum number required, it will be vetoed.</p>
<p>';
// PENDING COMMISSIONER APPROVAL
$lang['team_trade_pending_league_approval'] = 'To [USERNAME],
<p>Your trade offer to the owner of the [ACCEPTING_TEAM_NAME] has been accepted but is now contringent uppon approval by rhe league commissioner to be finalized. You will receive an email regarding the commissioners decision shortly.</p>
<p>';
// REJECTED BY OWNER
$lang['team_trade_rejected_owner'] = 'To [USERNAME],
<p>Your trade offer to the [ACCEPTING_TEAM_NAME] has been rejected by the team owner. No players have been assigned to new rosters. The owner gave the following reason for rejeecting the trade:/p>
<p>
[COMMENTS]</p>';
$lang['team_trade_protest_logged'] = 'To [USERNAME],
<p>Your trade offer to the [ACCEPTING_TEAM_NAME] has received a protest from the league. The reason given for this protest was:</p>
<p>
[COMMENTS]</p>';
// REJECTED BY LEAGUE
$lang['team_trade_rejected_league'] = 'To [USERNAME],
<p>Your trade offer to the [ACCEPTING_TEAM_NAME] has been rejected by the league. The minimum number of trade protests were received by the deadline. No players have been assigned to new rosters.</p>';
// REJECTED BY COMMISH
$lang['team_trade_rejected_commish'] = 'To [USERNAME],
<p>Your trade offer to the [ACCEPTING_TEAM_NAME] has been rejected by the league commissioner. No players have been assigned to new rosters. The reason given for the rejection was:</p>
<p>
[COMMENTS]</p>';
// RETRACTED
$lang['team_trade_retracted'] = 'To [USERNAME],
<p>The trade offer from the owner of the [OFFERING_TEAM_NAME] has been retracted. The owner gave the following reason:</p>
<p>
[COMMENTS]</p>
<p>';
// REMOVED
$lang['team_trade_removed'] = 'To [USERNAME],
<p>The trade offer from the owner of the [OFFERING_TEAM_NAME] has been removed by the [TRANSACTION_OWNER] for the following reason:</p>
<p>
[COMMENTS]</p>
<p>';
// EXPIRED
$lang['team_trade_expired'] = 'To [USERNAME],
<p>Sorry to say, but the trade offer from the owner of the [OFFERING_TEAM_NAME] has expired.</p>
<p>';
// INVALID
$lang['team_trade_invalid'] = 'The effective scoring period for this trade is passed the current period id. The trade has expired.';
// COMPLETED
$lang['team_trade_processed'] = 'The trade has been successfully completed. All players have been assigned to their new rosters.';

// PENING APPROVAL
$lang['team_trade_pendsing_approval'] = 'The trade has been accepted, but requires approval to be finalized by the [APPROVER_TYPE]. You will be notified if the trade has been approved or rejected.';
