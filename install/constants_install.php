<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');

/*
|--------------------------------------------------------------------------
| Basic Templating Values
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
//LOGIN
define("MESSAGE_NOTICE",'notice');
define("MESSAGE_FAIL",'error');
define("MESSAGE_SUCCESS",'success');

define("TEMPLATE_DEFAULT",'template');
define("TIMEZONE_DEFAULT",'Americas/New_York');

define('EMPTY_DATE_STR','0000-00-00');
define('EMPTY_DATE_TIME_STR','0000-00-00 00:00:00');

define('EMPTY_TIME_STR','00:00:00');

define('PAGE_NORMAL','normal');
define('PAGE_SEARCH','search');
define('PAGE_FORM','form');

define('LEAGUE_SCORING_ROTO',1);
define('LEAGUE_SCORING_ROTO_5X5',2);
define('LEAGUE_SCORING_ROTO_PLUS',3);
define('LEAGUE_SCORING_HEADTOHEAD',4);

define('NEWS_FANTASY_GAME',1);
define('NEWS_LEAGUE',2);
define('NEWS_PLAYER',3);
define('NEWS_TEAM',4);

define('REQUEST_STATUS_UNKNOWN',-1);
define('REQUEST_STATUS_PENDING',1);
define('REQUEST_STATUS_ACCEPTED',2);
define('REQUEST_STATUS_DENIED',3);
define('REQUEST_STATUS_WITHDRAWN',4);
define('REQUEST_STATUS_REMOVED',5);

define('RESTRICT_NONE',0);
define('RESCTRICT_ALL',1);
define('RESTRICT_EDIT',2);
define('RESTRICT_INFO',3);
define('RESTRICT_CUSTOM',4);

define('TRANS_OWNER_OWNER',1);
define('TRANS_OWNER_COMMISH',2);
define('TRANS_OWNER_ADMIN',3);
define('TRANS_OWNER_OTHER',4);

define('TRANS_TYPE_ADD',1);
define('TRANS_TYPE_DROP',2);
define('TRANS_TYPE_TRADE_TO',3);
define('TRANS_TYPE_TRADE_FROM',4);
define('TRANS_TYPE_OTHER',100);

define('TRADE_OFFERED',1);
define('TRADE_ACCEPTED',2);
define('TRADE_COMPLETED',3);
define('TRADE_REJECTED_OWNER',4);
define('TRADE_REJECTED_LEAGUE',5);
define('TRADE_REJECTED_COMMISH',6);
define('TRADE_REJECTED_ADMIN',7);
define('TRADE_REJECTED_COUNTER',8);
define('TRADE_RETRACTED',9);
define('TRADE_REMOVED',10);
define('TRADE_EXPIRED',11);
define('TRADE_INVALID',12);
define('TRADE_PENDING_LEAGUE_APPROVAL',13);
define('TRADE_PENDING_COMMISH_APPROVAL',14);
define('TRADE_PROTEST',15);
define('TRADE_APPROVED',16);

define('TRADE_MAX_EXPIRATION_DAYS',7);

define('SQL_OPERATOR_NONE',0);
define('SQL_OPERATOR_SUM',1);
define('SQL_OPERATOR_AVG',2);

// SET THE DEFAULT PATH SEPERATOR
if (substr(PHP_OS, 0, 3) == 'WIN') {
    define("URL_PATH_SEPERATOR","\\");
    define("PATH_SEPERATOR",";");
} else {
    define("URL_PATH_SEPERATOR","/");
    define("PATH_SEPERATOR",":");
}
define("JS_JQUERY","jquery.min.js");

define("MAIN_INSTALL_FILE","install.php");
define("DB_UPDATE_FILE","db_update.sql");
define("CONFIG_UPDATE_FILE","config_update.php");
define("CONSTANTS_UPDATE_FILE","constants_update.php");
define("DATA_CONFIG_UPDATE_FILE","database_update.php");
define("SL_CONNECTION_FILE","dbopen.php");
define("DB_CONNECTION_FILE","ootpfl_db.php");

define('QUERY_BASIC',1);
define('QUERY_STANDARD',2);
define('QUERY_EXTENDED',3);
define('QUERY_COMPACT',4);

define('SECURITY_RECAPTHCA',1);

define('OOTP_CURRENT_VERSION',21);
/*
|--------------------------------------------------------------------------
| File/Path Defaults
|--------------------------------------------------------------------------
|
| Default include files and paths
|
*/
define("SITE_URL",",[WEB_SITE_URL]");
define("DIR_APP_ROOT","[SITE_DIRECTORY]");
define("DIR_APP_WRITE_ROOT","[SITE_DIRECTORY]");
define("DIR_WRITE_PATH","[HTML_ROOT]");

define("SITE_URL_SHORT",SITE_URL);
define("DIR_WEB_ROOT",SITE_URL);

define("ABOUT_HTML_FILE","about.php");

define("DIR_VIEWS_USERS","users".URL_PATH_SEPERATOR);
define("DIR_VIEWS_INCLUDES","includes".URL_PATH_SEPERATOR);
define("DIR_VIEWS_SEARCH","search".URL_PATH_SEPERATOR);
define("DIR_VIEWS_BUGS","bug".URL_PATH_SEPERATOR);
define("DIR_VIEWS_PROJECTS","project".URL_PATH_SEPERATOR);
define("DIR_VIEWS_LEAGUES","league".URL_PATH_SEPERATOR);
define("DIR_VIEWS_NEWS","news".URL_PATH_SEPERATOR);
define("DIR_VIEWS_MEMBERS","member".URL_PATH_SEPERATOR);

define("PATH_INSTALL",DIR_WRITE_PATH.URL_PATH_SEPERATOR."install".URL_PATH_SEPERATOR);

define("PATH_IMAGES",DIR_APP_ROOT."images/");
define("PATH_IMAGES_WRITE","images".URL_PATH_SEPERATOR);

define("PATH_MEDIA",DIR_APP_ROOT."media/");
define("PATH_MEDIA_WRITE","media".URL_PATH_SEPERATOR);

define("PATH_ATTACHMENTS",PATH_MEDIA."uploads/");
define("PATH_ATTACHMENTS_WRITE",PATH_MEDIA_WRITE."uploads".URL_PATH_SEPERATOR);

define("DEFAULT_AVATAR",'avatar_default.jpg');
define("PATH_AVATARS_WRITE",PATH_IMAGES_WRITE."avatars".URL_PATH_SEPERATOR);
define("PATH_AVATARS",PATH_IMAGES."avatars/");

define("PATH_USERS_AVATAR_WRITE",PATH_AVATARS_WRITE."users".URL_PATH_SEPERATOR);
define("PATH_USERS_AVATARS",PATH_AVATARS."users/");

define("PATH_TEAMS_AVATAR_WRITE",PATH_AVATARS_WRITE."teams".URL_PATH_SEPERATOR);
define("PATH_TEAMS_AVATARS",PATH_AVATARS."teams/");

define("PATH_LEAGUES_AVATAR_WRITE",PATH_AVATARS_WRITE."leagues".URL_PATH_SEPERATOR);
define("PATH_LEAGUES_AVATARS",PATH_AVATARS."leagues/");

define("PATH_NEWS_IMAGES_WRITE",PATH_IMAGES_WRITE."news".URL_PATH_SEPERATOR);
define("PATH_NEWS_IMAGES",PATH_IMAGES."news/");

define("PATH_NEWS_IMAGES_PREV_WRITE",PATH_NEWS_IMAGES_WRITE."preview".URL_PATH_SEPERATOR);
define("PATH_NEWS_IMAGES_PREV",PATH_NEWS_IMAGES."preview/");

define("ACCESS_READ",1);
define("ACCESS_WRITE",2);
define("ACCESS_MODERATE",3);
define("ACCESS_MANAGE",4);
define("ACCESS_DEVELOP",5);
define("ACCESS_ADMINISTRATE",6);
/*
|--------------------------------------------------------------------------
| MOD DETAILS
|--------------------------------------------------------------------------
*/
define('SITE_NAME','OOTP Fantasy Baseball Leagues');
define('SITE_VERSION','1.0.3');
define('MOD_SITE_URL','http://ootpfantasyleagues.jfox015.com/');
define("BUG_URL",'https://github.com/jfox015/OOTP-Fantasy-Leagues/issues');
define("UPDATE_URL",'http://www.jfox015.com/ootp_fantasy/curr_version.txt');
/*
|--------------------------------------------------------------------------
| TABLE Defaults
|--------------------------------------------------------------------------
|
| Default include files and paths
|
*/
define("USER_CORE_TABLE","users_core");

define("FANTASY_CONFIG","fantasy_config");
define("FANTASY_LEAGUE_CONFIG","fantasy_leagues_config");

define("DEFAULT_RESULTS_COUNT",20);

/* End of file constants.php */
/* Location: ./system/application/config/constants.php */
