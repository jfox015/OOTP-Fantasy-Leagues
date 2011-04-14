<?php
$lang['email_footer'] = "<p>The Commish</p>
<p>[LEAGUE_NAME] Fantasy League</p>";
// ACCESS MESSAGING
$lang['access_heading_not_authorized'] = "403 Error - Not authorized.";
$lang['access_not_authorized'] = "We're sorry, but you are not authorized to view this content.";

$lang['private_league_access'] = "We're sorry, but the league you have selected is private and accessible only to members.<p />If you are a member of this league, please log in or contact the league commissioer or site adminitrator if you believe you are seeing this message in error.";
// LEAGUE INVITE
$lang['league_invite_default'] = '<br>Hi, This is the commissioner of the OOTP Fantasy League, [LEAGUE_NAME]. 
<br>
I would like to extend this invitation for you to join my league as a team owner. Our league is based on the baseball text sim <a href="http://www.ootpdevelopments.com/ootp11/" target="_blank">Out of the Park Baseball</a>. 
<br><br>
Please click the &quot;Accept the invitation&quot; link provided below to accept my invitation and join the league as a team owner. You can likewise decline my invitation by clicking the &quot;Decline the invitation&quot; link instead.';

$lang['about_report_bug_title'] = "Report a bug";
$lang['about_report_bug_body'] = "Use the following form to submit a bug or issue you have found with the site to the developer. This form can also be used to submit 
request and/or suggestions for new features and enhancents.<br /><br />Please be as detailed as possible when submitting issues and please try to 
include all steps that led up to the issue as well as any results that occured afterward.";

$lang['players_stats_no_players_error'] = '<b>Error</b><br>No players were found in the system. Assure that players have been imported into the fantasy database.';
/*--------------------
/	ADMIN STRINGS
/-------------------*/
// INSTALL DIR AND MISSING FILES
$lang['install_warning'] = '<b>WARNING</b><br />You have not deleted your installation files yet. All files located in <b>&quot;[SITE_URL]install/&quot;</b> should be removed immediately before you begin running your league to ensure its security.';
$lang['dbConnect_message'] = '<b>WARNING</b><br />A required database connection file is missing from your OOTP MySQL Files directory. If you are upgrading from a perious version of OOTP Fantasy leagues, you may need to create this file yourself. See the &quot;Upgrading to 1.0.3&quot; article in the the <b>Technical Support</b> on the <a href="[OOTP_FANTASY_URL]forum" target="_blank">OOTP Fantasy league Forums</a> for update instructions.';
$lang['update_required'] = '<b>Update Required</b><br />Updates are required to your MySQL database and/or site config files. Perform this update in the <a href="[ADMIN_URL]">Admin Dashboard</a> now.';
$lang['admin_error_fantasy_settings'] = '<b>Warning</b><br />You have not yet reviewed and completed your sites fantasy leagues setup. League commissioners will not be able to set their draft dates until this is done. Review and update your sites <a href="[FANTASY_SETTINGS_URL]">fantasy settings</a> now.';

// 1.0.3 - VERSION CHECK LANGUAGE
$lang['admin_version_no_value'] = 'No version information was passed.';
$lang['admin_version_check_error'] = 'Version information could not be accessed at this time.';
$lang['admin_version_current'] = 'Your fantasy league mod is currently up to date.';
$lang['admin_version_outdated'] = 'Your fantasy league mod is currently out of date. A newer version, <strong>[NEW_VERSION]</strong>, is currently available.';

/*---------------------------
/ USER MESSAGING
/---------------------------*/
$lang['user_too_many_leagues'] = 'We&#039;re sorry, but you are only allowed to create and own [MAX_LEAGUES] league[PLURAL] at a time.<p />If you have a league that is no longer active and you&#039;d like to close it, please [CONTACT_URL] for assitance.';
$lang['no_user_leagues'] = 'We&#039;re sorry, but you are not allowed to create a league at a time.';
/*--------------------------------
/	FORM EDITOR MESSAGING
/-------------------------------*/
$lang['form_complete_success'] = "The operation was sucessfully completed.";
$lang['form_complete_success_delete'] = "The record was sucessfully deleted.";
$lang['form_complete_fail'] = "The operation failed. Error: [ERROR_MESSAGE]";

// DELETE RECORD
$lang['form_confirm_delete'] = 'Are you sure you wish to continue with deleting the current [ITEM_TYPE]?
<br /><br />
Please note that any and all dependant information associated with this record may also be deleted if you choose to continue.
<br /><br />
<span class="error"><b>WARNING:</b> This action CANNOT be undone!</span>
<br />
<form id="confirmForm" name="confirmForm" action="'.DIR_APP_ROOT.'[ITEM_TYPE]/submit" method="post">
<input type="hidden" name="id" value="[RECORD_ID]" />
<input type="hidden" name="mode" value="delete" />
<input type="hidden" name="confirm" value="1" />
<fieldset class="button_bar align-left">
<input type="button" class="button" value="No, Cancel Delete" id="deleteCancel" />
<span style="margin-right:2px;display:inline;">&nbsp;</span>
<input type="button" class="button" value="Yes" id="deleteConfirm" />
</fieldset>
</form>';
/*------------------------------------
/	USER REGISTRATION/ ACCOUNT
/-----------------------------------*/
// REGISTER PAGE
$lang['user_register_title'] = 'Register';
$lang['user_register_instruct'] = 'Are you a part of this OOTP Online Fantasy community yet? If not here\'s now\'s the time to join!
<p /><br />
Already a member? <a href="./user/login">Login Now</a>!';
$lang['user_register_activation_email'] = 'Shortly after registering, you will recieve an e-mail containing an <b>activation code</b>. if you do not recieve this e-mail, you 
may [REQUEST_URL] it to be sent again or [CONTACT_URL] the site adminsitrator for help.';
$lang['user_register_activation_admin'] = '<b class="error_txt">NOTE: Your membership requires approval of the site administrator.</b> Once registered, you will recieve a confirmation if your membership is approved.';

// RESPONSE MESSAGING
$lang['user_registered'] = 'You have now been successfully registered. ';
$lang['user_register_activate_email'] = 'An email containing your activation code has been sent to [EMAIL].';
$lang['user_register_activate_admin'] = 'You will be notified when the site administrator has approved your membership.';
$lang['user_register_activate_none'] = 'Please login to begin using the site.';
$lang['user_register_existing'] = "You are already registered for this site.";

// LOGIN PAGE
$lang['user_login_title'] = 'Login';
$lang['user_login_inst'] = 'Please enter your login information to continue.';
$lang['user_login_errors'] = 'The following errors were found with your submission:';
$lang['user_login_activate_title'] = 'Need to activate your account?';
$lang['user_login_activate_email'] = '<b>Have an activation code to enter to activate your membership?</b><br /><br />Enter it on the [ACCOUNT_ACTIVATE_URL] page.<br /><br /><b>Need your code again?</b><br /><br />If you need your activation code sent again, you can request it on the [ACTIVATE_RESEND_URL] page.';

$lang['user_login_register_title'] = 'Not a member yet?';
$lang['user_login_register_body'] = 'What are you waiting for? 
            <br /><br />
            Jump in and test your skills at running an Out of the Park 
            baseball Fantasy League Team.';

// FORGOT PASSWORD
$lang['user_forgotpass_title'] = 'Forgotten Password';
$lang['user_forgotpass_instruct'] = 'Use the following form to <b>reset</b> your password. You will recieve 
a <b>confirmation code</b> by e- mail shortly after submitting this 
form to  confirm your identity. You must take the actions specified in this e-mail 
before comepleting your password reset request.
<br /><br />
If you have already received a reset code, <a href="[SITE_URL]user/forgotten_password_verify">enter it now</a>.
';
$lang['league_start_standings'] = "The [GAME_YEAR] season begins on <b>[START_DATE]</b>. Check back after the season begins for up to date league standings.";
$lang['leeague_admin_intro_str'] = "<b>Welcome to the League Admin Screen.</b><br /><p>Here you can review youe league settings, invite owners to join your league, manage invites and tranactions in progress and prior to the start oif the season, 
begin and manage your draft.";

// MISSING ACTIVATION CODE
$lang['user_missing_activation_title'] = 'Request Activation Code';
$lang['user_missing_activation'] = 'Use the following form to request that your activation code be rssent to the your email address. You will recieve 
the <b>activation code</b> by e- mail shortly after submitting this 
form.
<br /><br />
Once you receive your reset code, you can proceed to <a href="[SITE_URL]user/activate">activate your membership</a>.
';

/* End of file global_lang.php */
/* Location: ./application/language/english/global_lang.phpp */