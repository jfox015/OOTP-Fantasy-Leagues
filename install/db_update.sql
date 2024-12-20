
#----------------------------------------------------
#	UPDATE SQL QUERY
#	Version 0.1  0.2
#	REMOVE ALL COMMENTS FOR DIST

ALTER TABLE `fantasy_leagues` ADD `playoff_rounds` TINYINT NOT NULL;
INSERT INTO `fantasy_config` (`cfg_key`, `cfg_value`) VALUES('sharing_enabled', '1');
INSERT INTO `fantasy_config` (`cfg_key`, `cfg_value`) VALUES('share_facebook', '1');
INSERT INTO `fantasy_config` (`cfg_key`, `cfg_value`) VALUES('share_twitter', '1');
INSERT INTO `fantasy_config` (`cfg_key`, `cfg_value`) VALUES('share_digg', '1');
INSERT INTO `fantasy_config` (`cfg_key`, `cfg_value`) VALUES('share_stumble', '1');
INSERT INTO `fantasy_config` (`cfg_key`, `cfg_value`) VALUES('share_addtoany', '1');

#	UPDATE SQL QUERY
#	Version 0.2 to 0.3
#	REMOVE ALL COMMENTS FOR DIST

INSERT INTO `fantasy_config` (`cfg_key`, `cfg_value`) VALUES('google_analytics_enable', '-1');
INSERT INTO `fantasy_config` (`cfg_key`, `cfg_value`) VALUES('google_analytics_tracking_id', '');
INSERT INTO `fantasy_config` (`cfg_key`, `cfg_value`) VALUES('stats_lab_compatible', '1');
INSERT INTO `fantasy_config` (`cfg_key`, `cfg_value`) VALUES('restrict_admin_leagues', '-1');
INSERT INTO `fantasy_config` (`cfg_key`, `cfg_value`) VALUES('users_create_leagues', '1');
INSERT INTO `fantasy_config` (`cfg_key`, `cfg_value`) VALUES('max_user_leagues', '1');
INSERT INTO `fantasy_config` (`cfg_key`, `cfg_value`) VALUES('primary_contact', '1');

#	UPDATE SQL QUERY
#	Version 0.3 to 0.5
#	REMOVE ALL COMMENTS FOR DIST
DROP TABLE IF EXISTS `fantasy_leagues_requests_status`;
DROP TABLE IF EXISTS `fantasy_leagues_requests`;
DROP TABLE IF EXISTS `fantasy_players_scoring`;
DROP TABLE IF EXISTS `fantasy_sim_summary`;
DROP TABLE IF EXISTS `fantasy_teams_scoring`;
DROP TABLE IF EXISTS `fantasy_teams_trades`;
DROP TABLE IF EXISTS `fantasy_teams_trades_approvals`;
DROP TABLE IF EXISTS `fantasy_teams_trades_status`;
DROP TABLE IF EXISTS `fantasy_teams_trade_protests`;
DROP TABLE IF EXISTS `fantasy_players_compiled_batting`;
DROP TABLE IF EXISTS `fantasy_players_compiled_pitching`;
DROP TABLE IF EXISTS `users_activation_types`;
ALTER TABLE `fantasy_draft_config` ADD `enforceTimer` TINYINT NOT NULL DEFAULT '-1' AFTER `flexTimer` ;
ALTER TABLE `fantasy_draft_config` CHANGE `timePick1` `timePick1` INT( 10 ) NOT NULL;
ALTER TABLE `fantasy_draft_config` CHANGE `timePick2` `timePick2` INT( 10 ) NOT NULL;
ALTER TABLE `fantasy_scoring_periods` ADD `manual_waivers` TINYINT NOT NULL DEFAULT '-1';
ALTER TABLE `fantasy_invites` ADD `status_id` TINYINT NOT NULL DEFAULT '1';
ALTER TABLE `fantasy_leagues` ADD `accept_requests` TINYINT NOT NULL DEFAULT '1';
ALTER TABLE `fantasy_transactions` ADD `trade_team_id` INT NOT NULL AFTER `dropped`;
ALTER TABLE `fantasy_teams_record` ADD `scoring_period_id` TINYINT NOT NULL AFTER `year`;
CREATE TABLE IF NOT EXISTS `fantasy_leagues_requests` ( `id` int(11) NOT NULL auto_increment, `league_id` int(11) NOT NULL, `team_id` int(11) NOT NULL, `user_id` int(11) NOT NULL, `date_requested` timestamp NOT NULL default CURRENT_TIMESTAMP, `status_Id` tinyint(4) NOT NULL default '1', PRIMARY KEY  (`id`), KEY `league_id` (`league_id`,`team_id`,`user_id`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
CREATE TABLE IF NOT EXISTS `fantasy_leagues_requests_status` (`id` tinyint(4) NOT NULL auto_increment,`requestStatus` varchar(100) collate utf8_unicode_ci NOT NULL,PRIMARY KEY  (`id`),KEY `requestStatus` (`requestStatus`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;
INSERT INTO `fantasy_leagues_requests_status` (`id`, `requestStatus`) VALUES(1, 'Pending');
INSERT INTO `fantasy_leagues_requests_status` (`id`, `requestStatus`) VALUES(2, 'Accepted');
INSERT INTO `fantasy_leagues_requests_status` (`id`, `requestStatus`) VALUES(3, 'Denied');
INSERT INTO `fantasy_leagues_requests_status` (`id`, `requestStatus`) VALUES(4, 'Withdrawn');
INSERT INTO `fantasy_leagues_requests_status` (`id`, `requestStatus`) VALUES(5, 'Removed');
INSERT INTO `fantasy_leagues_requests_status` (`id`, `requestStatus`) VALUES(-1, 'Unknown');
ALTER TABLE `fantasy_leagues_scoring_batting` ADD `scoring_type` TINYINT NOT NULL AFTER `league_id`;
ALTER TABLE `fantasy_leagues_scoring_pitching` ADD `scoring_type` TINYINT NOT NULL AFTER `league_id`;
TRUNCATE TABLE `fantasy_leagues_scoring_batting`;
INSERT INTO `fantasy_leagues_scoring_batting` (`id`, `league_id`, `scoring_type`, `type_0`, `value_0`, `type_1`, `value_1`, `type_2`, `value_2`, `type_3`, `value_3`, `type_4`, `value_4`, `type_5`, `value_5`, `type_6`, `value_6`, `type_7`, `value_7`, `type_8`, `value_8`, `type_9`, `value_9`, `type_10`, `value_10`, `type_11`, `value_11`) VALUES(1, 0, 1, 18, 0, 8, 0, 11, 0, 9, 0, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1);
INSERT INTO `fantasy_leagues_scoring_batting` (`id`, `league_id`, `scoring_type`, `type_0`, `value_0`, `type_1`, `value_1`, `type_2`, `value_2`, `type_3`, `value_3`, `type_4`, `value_4`, `type_5`, `value_5`, `type_6`, `value_6`, `type_7`, `value_7`, `type_8`, `value_8`, `type_9`, `value_9`, `type_10`, `value_10`, `type_11`, `value_11`) VALUES(2, 0, 2, 18, 0, 8, 0, 11, 0, 9, 0, 10, 0, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1);
INSERT INTO `fantasy_leagues_scoring_batting` (`id`, `league_id`, `scoring_type`, `type_0`, `value_0`, `type_1`, `value_1`, `type_2`, `value_2`, `type_3`, `value_3`, `type_4`, `value_4`, `type_5`, `value_5`, `type_6`, `value_6`, `type_7`, `value_7`, `type_8`, `value_8`, `type_9`, `value_9`, `type_10`, `value_10`, `type_11`, `value_11`) VALUES(4, 0, 4, 3, 1, 6, 2, 7, 3, 8, 4, 10, 1, 11, 1, 12, 1, 9, 2, 4, -1, 14, 1, 58, -1, -1, -1);
INSERT INTO `fantasy_leagues_scoring_batting` (`id`, `league_id`, `scoring_type`, `type_0`, `value_0`, `type_1`, `value_1`, `type_2`, `value_2`, `type_3`, `value_3`, `type_4`, `value_4`, `type_5`, `value_5`, `type_6`, `value_6`, `type_7`, `value_7`, `type_8`, `value_8`, `type_9`, `value_9`, `type_10`, `value_10`, `type_11`, `value_11`) VALUES(3, 0, 3, 18, 0, 11, 0, 8, 0, 10, 0, 9, 0, 25, 0, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1);
TRUNCATE TABLE `fantasy_leagues_scoring_pitching`;
INSERT INTO `fantasy_leagues_scoring_pitching` (`id`, `league_id`, `scoring_type`, `type_0`, `value_0`, `type_1`, `value_1`, `type_2`, `value_2`, `type_3`, `value_3`, `type_4`, `value_4`, `type_5`, `value_5`, `type_6`, `value_6`, `type_7`, `value_7`, `type_8`, `value_8`, `type_9`, `value_9`, `type_10`, `value_10`, `type_11`, `value_11`) VALUES(1, 0, 1, 40, 0, 38, 0, 42, 0, 32, 0, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1);
INSERT INTO `fantasy_leagues_scoring_pitching` (`id`, `league_id`, `scoring_type`, `type_0`, `value_0`, `type_1`, `value_1`, `type_2`, `value_2`, `type_3`, `value_3`, `type_4`, `value_4`, `type_5`, `value_5`, `type_6`, `value_6`, `type_7`, `value_7`, `type_8`, `value_8`, `type_9`, `value_9`, `type_10`, `value_10`, `type_11`, `value_11`) VALUES(2, 0, 2, 40, 0, 38, 0, 42, 0, 32, 0, 42, 0, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1);
INSERT INTO `fantasy_leagues_scoring_pitching` (`id`, `league_id`, `scoring_type`, `type_0`, `value_0`, `type_1`, `value_1`, `type_2`, `value_2`, `type_3`, `value_3`, `type_4`, `value_4`, `type_5`, `value_5`, `type_6`, `value_6`, `type_7`, `value_7`, `type_8`, `value_8`, `type_9`, `value_9`, `type_10`, `value_10`, `type_11`, `value_11`) VALUES(3, 0, 3, 40, 0, 29, 0, 38, 0, 32, 0, 42, 0, 52, 0, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1);
INSERT INTO `fantasy_leagues_scoring_pitching` (`id`, `league_id`, `scoring_type`, `type_0`, `value_0`, `type_1`, `value_1`, `type_2`, `value_2`, `type_3`, `value_3`, `type_4`, `value_4`, `type_5`, `value_5`, `type_6`, `value_6`, `type_7`, `value_7`, `type_8`, `value_8`, `type_9`, `value_9`, `type_10`, `value_10`, `type_11`, `value_11`) VALUES(4, 0, 4, 29, 7, 30, -5, 38, 1, 34, 1, 37, -1, 60, -1, 54, 10, 56, 10, 32, 5, 61, -3, -1, -1, -1, -1);
ALTER TABLE `fantasy_scoring_periods` ADD `manual_waivers` TINYINT NOT NULL DEFAULT '-1';
ALTER TABLE `fantasy_players` ADD `rating` FLOAT NOT NULL DEFAULT '0', ADD `rank` SMALLINT NOT NULL DEFAULT '0', ADD `last_rank` SMALLINT NOT NULL DEFAULT '0', ADD `2nd_last_rank` SMALLINT NOT NULL DEFAULT '0';
ALTER TABLE `fantasy_draft_config` ADD `emailOwnersForPick` TINYINT NOT NULL DEFAULT '1' AFTER `replyList` , ADD `emailDraftSummary` TINYINT NOT NULL DEFAULT '1' AFTER `emailOwnersForPick` ;
UPDATE `fantasy_draft_config` SET `emailOwnersForPick` = 1, `emailDraftSummary` = 1;
CREATE TABLE IF NOT EXISTS `fantasy_players_compiled_batting` ( `id` int(11) NOT NULL auto_increment, `player_id` int(11) NOT NULL default '-1', `scoring_period_id` tinyint(4) NOT NULL default '-1', `ab` smallint(6) default NULL, `h` smallint(6) default NULL, `k` smallint(6) default NULL, `pa` smallint(6) default NULL, `g` smallint(6) default NULL, `gs` smallint(6) default NULL, `d` smallint(6) default NULL, `t` smallint(6) default NULL, `hr` smallint(6) default NULL, `r` smallint(6) default NULL, `rbi` smallint(6) default NULL, `sb` smallint(6) default NULL, `cs` smallint(6) default NULL, `bb` smallint(6) default NULL, `ibb` smallint(6) default NULL, `gdp` smallint(6) default NULL, `sh` smallint(6) default NULL, `sf` smallint(6) default NULL, `hp` smallint(6) default NULL, `ci` smallint(6) default NULL, `avg` float default NULL, `obp` float default NULL, `slg` float default NULL, `ops` float default NULL, PRIMARY KEY  (`id`), KEY `player_id` (`player_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
CREATE TABLE IF NOT EXISTS `fantasy_players_compiled_pitching` ( `id` int(11) NOT NULL auto_increment, `player_id` int(11) NOT NULL default '0', `scoring_period_id` tinyint(4) NOT NULL default '-1', `ip` smallint(6) default NULL, `ab` smallint(6) default NULL, `tb` smallint(6) default NULL, `ha` smallint(6) default NULL, `k` smallint(6) default NULL, `bf` smallint(6) default NULL, `rs` smallint(6) default NULL, `bb` smallint(6) default NULL, `r` smallint(6) default NULL, `er` smallint(6) default NULL, `gb` smallint(6) default NULL, `fb` smallint(6) default NULL, `pi` smallint(6) default NULL, `ipf` smallint(6) default NULL, `g` smallint(6) default NULL, `gs` smallint(6) default NULL, `w` smallint(6) default NULL, `l` smallint(6) default NULL, `s` smallint(6) default NULL, `sa` smallint(6) default NULL, `da` smallint(6) default NULL, `sh` smallint(6) default NULL, `sf` smallint(6) default NULL, `ta` smallint(6) default NULL, `hra` smallint(6) default NULL, `bk` smallint(6) default NULL, `ci` smallint(6) default NULL, `iw` smallint(6) default NULL, `wp` smallint(6) default NULL, `hp` smallint(6) default NULL, `gf` smallint(6) default NULL, `dp` smallint(6) default NULL, `qs` smallint(6) default NULL, `svo` smallint(6) default NULL, `bs` smallint(6) default NULL, `ra` smallint(6) default NULL, `cg` smallint(6) default NULL, `sho` smallint(6) default NULL, `sb` smallint(6) default NULL, `cs` smallint(6) default NULL, `hld` smallint(6) default NULL, `era` float default NULL, `whip` float default NULL, PRIMARY KEY  (`id`), KEY `player_id` (`player_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
CREATE TABLE IF NOT EXISTS `fantasy_players_scoring` ( `id` int(11) NOT NULL auto_increment, `player_id` int(11) NOT NULL, `league_id` int(11) NOT NULL default '0', `scoring_period_id` tinyint(4) NOT NULL default '1', `scoring_type` tinyint(4) NOT NULL default '1', `total` varchar(15) collate utf8_unicode_ci NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
CREATE TABLE IF NOT EXISTS `fantasy_sim_summary` (`id` int(11) NOT NULL auto_increment,`scoring_period_id` tinyint(4) NOT NULL,`sim_date` timestamp NOT NULL default CURRENT_TIMESTAMP,`process_time` smallint(6) NOT NULL,`sim_result` int(11) NOT NULL,`sim_summary` longtext collate utf8_unicode_ci NOT NULL,`comments` varchar(10000) collate utf8_unicode_ci NOT NULL,PRIMARY KEY  (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
CREATE TABLE IF NOT EXISTS `fantasy_teams_scoring` ( `id` int(11) NOT NULL auto_increment, `team_id` int(11) NOT NULL default '-1', `league_id` int(11) NOT NULL default '-1', `scoring_period_id` tinyint(4) NOT NULL default '1', `value_0` varchar(15) collate utf8_unicode_ci NOT NULL default '-1', `value_1` varchar(15) collate utf8_unicode_ci NOT NULL default '-1', `value_2` varchar(15) collate utf8_unicode_ci NOT NULL default '-1', `value_3` varchar(15) collate utf8_unicode_ci NOT NULL default '-1', `value_4` varchar(15) collate utf8_unicode_ci NOT NULL default '-1', `value_5` varchar(15) collate utf8_unicode_ci NOT NULL default '-1', `value_6` varchar(15) collate utf8_unicode_ci NOT NULL default '-1', `value_7` varchar(15) collate utf8_unicode_ci NOT NULL default '-1', `value_8` varchar(15) collate utf8_unicode_ci NOT NULL default '-1', `value_9` varchar(15) collate utf8_unicode_ci NOT NULL default '-1', `value_10` varchar(15) collate utf8_unicode_ci NOT NULL default '-1', `value_11` varchar(15) collate utf8_unicode_ci NOT NULL default '-1', `total` varchar(15) collate utf8_unicode_ci NOT NULL default '0', PRIMARY KEY  (`id`), KEY `team_id` (`team_id`), KEY `league_id` (`league_id`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
CREATE TABLE IF NOT EXISTS `fantasy_teams_trades` (  `id` int(11) NOT NULL auto_increment,  `league_id` int(11) NOT NULL,  `team_1_id` int(11) NOT NULL,  `send_players` mediumtext collate utf8_unicode_ci NOT NULL,  `team_2_id` int(11) NOT NULL,  `receive_players` mediumtext collate utf8_unicode_ci NOT NULL,  `offer_date` timestamp NOT NULL default CURRENT_TIMESTAMP,  `expiration_date` datetime NOT NULL,  `comments` mediumtext collate utf8_unicode_ci NOT NULL,  `status` tinyint(4) NOT NULL,  `response` mediumtext collate utf8_unicode_ci NOT NULL,  `response_date` datetime NOT NULL,  `admin_notes` mediumtext collate utf8_unicode_ci NOT NULL,  `in_period` tinyint(4) NOT NULL,  `previous_trade_id` int(11) NOT NULL,  PRIMARY KEY  (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
CREATE TABLE IF NOT EXISTS `fantasy_teams_trades_approvals` (`id` tinyint(4) NOT NULL auto_increment,`tradeApprovalType` tinytext collate utf8_unicode_ci NOT NULL, PRIMARY KEY  (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;
INSERT INTO `fantasy_teams_trades_approvals` VALUES(-1, 'None');
INSERT INTO `fantasy_teams_trades_approvals` VALUES(1, 'Commissioner');
INSERT INTO `fantasy_teams_trades_approvals` VALUES(2, 'League');
CREATE TABLE IF NOT EXISTS `fantasy_teams_trades_status` (  `id` int(11) NOT NULL auto_increment,  `tradeStatus` tinytext collate utf8_unicode_ci NOT NULL,  PRIMARY KEY  (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=13 ;
INSERT INTO `fantasy_teams_trades_status` VALUES(1, 'Offered');
INSERT INTO `fantasy_teams_trades_status` VALUES(2, 'Accepted');
INSERT INTO `fantasy_teams_trades_status` VALUES(3, 'Completed');
INSERT INTO `fantasy_teams_trades_status` VALUES(4, 'Rejected by Owner');
INSERT INTO `fantasy_teams_trades_status` VALUES(5, 'Rejected by League');
INSERT INTO `fantasy_teams_trades_status` VALUES(6, 'Rejected by Commissioner');
INSERT INTO `fantasy_teams_trades_status` VALUES(7, 'Rejected by Admin');
INSERT INTO `fantasy_teams_trades_status` VALUES(8, 'Rejected with Counter');
INSERT INTO `fantasy_teams_trades_status` VALUES(9, 'Retracted');
INSERT INTO `fantasy_teams_trades_status` VALUES(10, 'Removed');
INSERT INTO `fantasy_teams_trades_status` VALUES(11, 'Expired');
INSERT INTO `fantasy_teams_trades_status` VALUES(12, 'Invalid Trade');
INSERT INTO `fantasy_teams_trades_status` VALUES(13, 'Pending League Approval');
INSERT INTO `fantasy_teams_trades_status` VALUES(14, 'Pending Commissioner Approval');
CREATE TABLE IF NOT EXISTS `fantasy_teams_trade_protests` (  `id` int(11) NOT NULL auto_increment,  `league_id` int(11) NOT NULL,  `trade_id` int(11) NOT NULL,  `protest_team_id` int(11) NOT NULL,  `protest_date` timestamp NOT NULL default CURRENT_TIMESTAMP,  `comments` longtext collate utf8_unicode_ci NOT NULL,  PRIMARY KEY  (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
INSERT INTO `fantasy_config` ( `cfg_key`, `cfg_value`) VALUES('useTrades', '1');
INSERT INTO `fantasy_config` ( `cfg_key`, `cfg_value`) VALUES('approvalType', '-1');
INSERT INTO `fantasy_config` ( `cfg_key`, `cfg_value`) VALUES('tradesExpire', '1');
INSERT INTO `fantasy_config` ( `cfg_key`, `cfg_value`) VALUES('defaultExpiration', '500');
INSERT INTO `fantasy_config` ( `cfg_key`, `cfg_value`) VALUES('minProtests', '3');
INSERT INTO `fantasy_config` ( `cfg_key`, `cfg_value`) VALUES('protestPeriodDays', '3');
INSERT INTO `fantasy_config` ( `cfg_key`, `cfg_value`) VALUES('limit_load_all_sql', '1');
INSERT INTO `fantasy_config` ( `cfg_key`, `cfg_value`) VALUES('user_activation_required', '1');
INSERT INTO `fantasy_config` ( `cfg_key`, `cfg_value`) VALUES('user_activation_method', '1');
INSERT INTO `fantasy_config` ( `cfg_key`, `cfg_value`) VALUES('ootp_html_report_links', '1');
INSERT INTO `fantasy_config` ( `cfg_key`, `cfg_value`) VALUES('stats_lab_url', '');
UPDATE `fantasy_leagues_types` SET `active` = 1 WHERE id = 1 OR id = 2 OR id = 3;
CREATE TABLE IF NOT EXISTS `users_activation_types` (`id` tinyint(4) NOT NULL auto_increment,`activationType` varchar(50) collate utf8_unicode_ci NOT NULL,PRIMARY KEY  (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;
INSERT INTO `users_activation_types` (`id`, `activationType`) VALUES(-1, 'None');
INSERT INTO `users_activation_types` (`id`, `activationType`) VALUES(1, 'Email');
INSERT INTO `users_activation_types` (`id`, `activationType`) VALUES(2, 'Administrator');

#	UPDATE SQL QUERY
#	Version 0.5 to 0.6
#	REMOVE ALL COMMENTS FOR DIST
ALTER TABLE `users_core` ADD `password_salt` varchar(50) NOT NULL AFTER `password`;
ALTER TABLE `users_meta` ADD `timezone` TEXT NOT NULL DEFAULT '';
ALTER TABLE `fantasy_teams_scoring` ADD `stats_compiled` TEXT NOT NULL AFTER `scoring_period_id`;
ALTER TABLE `fantasy_teams_trades` CHANGE `expiration_date` `expiration_days` VARCHAR( 10 ) NOT NULL;
INSERT INTO `fantasy_config` ( `cfg_key`, `cfg_value`) VALUES('timezone', '');
INSERT INTO `fantasy_config` ( `cfg_key`, `cfg_value`) VALUES('security_enabled', '-1');
INSERT INTO `fantasy_config` ( `cfg_key`, `cfg_value`) VALUES('security_type', '');
INSERT INTO `fantasy_config` ( `cfg_key`, `cfg_value`) VALUES('security_class', '1');
INSERT INTO `fantasy_config` ( `cfg_key`, `cfg_value`) VALUES('recaptcha_key_public', '');
INSERT INTO `fantasy_config` ( `cfg_key`, `cfg_value`) VALUES('recaptcha_key_private', '');
INSERT INTO `fantasy_config` ( `cfg_key`, `cfg_value`) VALUES('recaptcha_theme', '');
INSERT INTO `fantasy_config` ( `cfg_key`, `cfg_value`) VALUES('recaptcha_lang', '');
INSERT INTO `fantasy_config` ( `cfg_key`, `cfg_value`) VALUES('recaptcha_compliant', '');
UPDATE `fantasy_config` SET `cfg_value`= '500' WHERE `cfg_key` = 'defaultExpiration';

#	UPDATE SQL QUERY
#	Version 0.6 and 1.0 to 1.0.1
#	REMOVE ALL COMMENTS FOR DIST
INSERT INTO `fantasy_config` ( `cfg_key`, `cfg_value`) VALUES('ootp_version', 12);

#	UPDATE SQL QUERY
#	Version 1.1 TO 1.1.1
#	REMOVE ALL COMMENTS FOR DIST
UPDATE `fantasy_config` SET `cfg_value` = 25 WHERE `cfg_key` = 'ootp_version';
CREATE TABLE IF NOT EXISTS `fantasy_teams_players_compiled_batting` (`id` int NOT NULL AUTO_INCREMENT,`player_id` int NOT NULL DEFAULT '-1',`ootp_player_id` int NOT NULL DEFAULT '-1',`team_id` int NOT NULL DEFAULT '-1',`league_id` int NOT NULL DEFAULT '-1',`scoring_period_id` int NOT NULL DEFAULT '-1',`ab` smallint DEFAULT NULL,`h` smallint DEFAULT NULL,`k` smallint DEFAULT NULL,`pa` smallint DEFAULT NULL,`g` smallint DEFAULT NULL,`gs` smallint DEFAULT NULL,`d` smallint DEFAULT NULL,`t` smallint DEFAULT NULL,`hr` smallint DEFAULT NULL,`r` smallint DEFAULT NULL,`rbi` smallint DEFAULT NULL,`sb` smallint DEFAULT NULL,`cs` smallint DEFAULT NULL,`bb` smallint DEFAULT NULL,`ibb` smallint DEFAULT NULL,`gdp` smallint DEFAULT NULL,`sh` smallint DEFAULT NULL,`sf` smallint DEFAULT NULL,`hp` smallint DEFAULT NULL,`ci` smallint DEFAULT NULL,`avg` float DEFAULT NULL,`obp` float DEFAULT NULL,`slg` float DEFAULT NULL,`ops` float DEFAULT NULL,PRIMARY KEY (`id`),KEY `player_id` (`player_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
CREATE TABLE IF NOT EXISTS `fantasy_teams_players_compiled_pitching` (`id` int NOT NULL AUTO_INCREMENT,`player_id` int NOT NULL DEFAULT '-1',`ootp_player_id` int NOT NULL DEFAULT '-1',`team_id` int NOT NULL DEFAULT '-1',`league_id` int NOT NULL DEFAULT '-1',`scoring_period_id` int NOT NULL DEFAULT '-1',`ip` smallint DEFAULT NULL,`ab` smallint DEFAULT NULL,`tb` smallint DEFAULT NULL,`ha` smallint DEFAULT NULL,`k` smallint DEFAULT NULL,`bf` smallint DEFAULT NULL,`rs` smallint DEFAULT NULL,`bb` smallint DEFAULT NULL,`r` smallint DEFAULT NULL,`er` smallint DEFAULT NULL,`gb` smallint DEFAULT NULL,`fb` smallint DEFAULT NULL,`pi` smallint DEFAULT NULL,`ipf` smallint DEFAULT NULL,`g` smallint DEFAULT NULL,`gs` smallint DEFAULT NULL,`w` smallint DEFAULT NULL,`l` smallint DEFAULT NULL,`s` smallint DEFAULT NULL,`sa` smallint DEFAULT NULL,`da` smallint DEFAULT NULL,`sh` smallint DEFAULT NULL,`sf` smallint DEFAULT NULL,`ta` smallint DEFAULT NULL,`hra` smallint DEFAULT NULL,`bk` smallint DEFAULT NULL,`ci` smallint DEFAULT NULL,`iw` smallint DEFAULT NULL,`wp` smallint DEFAULT NULL,`hp` smallint DEFAULT NULL,`gf` smallint DEFAULT NULL,`dp` smallint DEFAULT NULL,`qs` smallint DEFAULT NULL,`svo` smallint DEFAULT NULL,`bs` smallint DEFAULT NULL,`ra` smallint DEFAULT NULL,`cg` smallint DEFAULT NULL,`sho` smallint DEFAULT NULL,`sb` smallint DEFAULT NULL,`cs` smallint DEFAULT NULL,`hld` smallint DEFAULT NULL,`era` float DEFAULT NULL,`whip` float DEFAULT NULL,PRIMARY KEY (`id`),KEY `player_id` (`player_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
