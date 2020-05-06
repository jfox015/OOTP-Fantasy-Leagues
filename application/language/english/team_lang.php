<?php
/*---------------------------------------
/	TRADES
/--------------------------------------*/
$lang['team_lineup_adjust'] = 'adjust your lineup';
//-------------------------------
// TRADE WORKFLOW MESSAGING
// OFFER SUBMITTED
//-------------------------------
$lang['team_trade_offer_submitted'] = '<p>Your trade offer was submitted sucessfully. The owner of the [ACCEPTING_TEAM_NAME] has been notified of your offer.</p>';
// ERROR - ROSTER WARNINGS/ERRORS
$lang['team_trade_offer_roster_error'] = "Problems were found with this trade offer:<br />[ROSTER_MESSAGES]";
// INVALID
$lang['team_trade_invalid'] = 'The effective scoring period for this trade is passed the current period id. The trade has expired.';
// INVALID
$lang['team_trade_not_offered'] = 'The status of this trade has changed. The trade is no longer valid because it is now in [STATUS].';
// COMPLETED
$lang['team_trade_processed'] = 'The trade has been successfully completed. All players have been assigned to their new rosters.';
// PENING APPROVAL
$lang['team_trade_pendsing_approval'] = 'The trade has been accepted, but requires approval to be finalized by the [APPROVER_TYPE]. You will be notified if the trade has been approved or rejected.';

//-------------------------------
// EMAIL SUBJECT LINES
//-------------------------------
$lang['team_trade_email_subject_offer_to'] = '[LEAGUE_NAME] Fantasy League - A Trade Offer has been submitted for you';
$lang['team_trade_email_subject_offer_from'] = '[LEAGUE_NAME] Fantasy League - Your Trade Offer has been submitted';

//-------------------------------
//	EMAIL MESSAGING
//-------------------------------
// TITLES
$lang['team_email_title_trade_offer'] = 'Trade Offer';
$lang['team_email_title_trade_response'] = 'Trade Response';

// OFFER SUBMITTED
$lang['team_trade_offer'] = 'To [USERNAME],
<p>[OFFERING_TEAM_NAME] has submitted the following trade proposal to you.</p>
<p>
[OFFERING_TEAM_NAME] offers:<br />
[SEND_PLAYERS]</p>
<p>for:</p>
<p>
[RECEIVE_PLAYERS]</p>
<p><i>&quot;[COMMENTS]&quot;</i></p>
<p>[EXPIRES]</p>
<p>You can [TRADE_REVIEW_URL] to view the details of this trade and proviode the owner with a response.</p>
';

// EXPIRATION MESSAGING
$lang['team_trade_expires_message_to'] = 'This trade offer expires in [EXPIRES] days from the time of this offer.';
$lang['team_trade_expires_message_to_next_sim'] = 'This trade offer expires when the next sim is processed.';
$lang['team_trade_expires_message_to_none'] = 'This trade offer is good until accepted or retracted by the offering team.';

$lang['team_trade_expires_message_from'] = 'You have given this owner [EXPIRES] days to accept or reject your offer.';
$lang['team_trade_expires_message_from_next_sim'] = 'You have indicated that this trade offer expires when the next sim is processed.';
$lang['team_trade_expires_message_from_none'] = 'This trade offer is good until accepted by the offering team or you retract it.';
// OFFER CONFIRMAION
$lang['team_trade_offer_confirm'] = 'To [USERNAME],
<p>Your trade offer to the [ACCEPTING_TEAM_NAME] has been submitted. You can review the details of your trade and retract your offer if you feel necessary on the offers [TRADE_REVIEW_URL].</p>
<p><b>Trade Details:</p>
You will send:<br />
[SEND_PLAYERS]</p>
<p>for:</p>
<p>
[RECEIVE_PLAYERS]</p>
<p>[EXPIRES]</p>';
// ACCEPTED
$lang['team_trade_accepted_no_approvals'] = 'To [USERNAME],
<p>Your trade offer to the [ACCEPTING_TEAM_NAME] has been accepted. All players have been assigned to their new rosters.</p>
<p><i>&quot;[COMMENTS]&quot;</i></p>
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
<p>Your trade offer to the [ACCEPTING_TEAM_NAME] has been rejected by the team owner. No players have been assigned to new rosters. The owner gave the following reason for rejeecting the trade:</p>
<p><i>&quot;[COMMENTS]&quot;</i></p>';

// REJECTED WITH COUNTER
$lang['team_trade_rejected_counter'] = 'To [USERNAME],
<p>Your trade offer to the [ACCEPTING_TEAM_NAME] has been rejected by the team owner. No players have been assigned to new rosters. The owner gave the following reason for rejeecting the trade:</p>
<p><i>&quot;[COMMENTS]&quot;</i></p>.
<p>A counter offer appears to be in the works so stay tuned for an additional response.';

// TRADE PROTEST LOGGED
$lang['team_trade_protest_logged'] = 'To [USERNAME],
<p>Your trade offer to the [ACCEPTING_TEAM_NAME] has received a protest from the league. The reason given for this protest was:</p>
<p><i>&quot;[COMMENTS]&quot;</i></p>';

// REJECTED BY LEAGUE
$lang['team_trade_rejected_league'] = 'To [USERNAME],
<p>Your trade offer to the [ACCEPTING_TEAM_NAME] has been rejected by the league. The minimum number of trade protests were received by the deadline. No players have been assigned to new rosters.</p>';

// REJECTED BY COMMISH
$lang['team_trade_rejected_commish'] = 'To [USERNAME],
<p>Your trade offer to the [ACCEPTING_TEAM_NAME] has been rejected by the league commissioner. No players have been assigned to new rosters. The reason given for the rejection was:</p>
<p><i>&quot;[COMMENTS]&quot;</i></p>';

// RETRACTED
$lang['team_trade_retracted'] = 'To [USERNAME],
<p>The trade offer from the owner of the [OFFERING_TEAM_NAME] has been retracted. The owner gave the following reason:</p>
<p><i>&quot;[COMMENTS]&quot;</i></p>
<p>';

// REMOVED
$lang['team_trade_removed'] = 'To [USERNAME],
<p>The trade offer from the owner of the [OFFERING_TEAM_NAME] has been removed by the [TRANSACTION_OWNER] for the following reason:</p>
<p><i>&quot;[COMMENTS]&quot;</i></p>
<p>';

// EXPIRED
// For RECEIVING TEAM
$lang['team_trade_expired'] = 'To [USERNAME],
<p>Sorry to say, but the trade offer from the owner of the [OFFERING_TEAM_NAME] has expired.</p>
<p>';
// FOR OFFERING TEAM
$lang['team_trade_expired_offering_team'] = 'To [USERNAME],
<p>Sorry to say, but your trade offer from to owner of the [ACCEPTING_TEAM_NAME] has expired.</p>
<p>';
$lang['team_trade_auto_expired'] = 'Trade past scoring period date';

$lang['no_owner'] = 'No Owner';