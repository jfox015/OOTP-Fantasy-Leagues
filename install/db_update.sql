#	UPDATE SQL QUERY
#	Version 1.0 and 1.0.1 TO 1.0.2
#	REMOVE ALL COMMENTS FOR DIST
ALTER TABLE `fantasy_leagues` ADD `playoff_rounds` TINYINT NOT NULL;
INSERT INTO `fantasy_config` (`cfg_key`, `cfg_value`) VALUES('sharing_enabled', '1');
INSERT INTO `fantasy_config` (`cfg_key`, `cfg_value`) VALUES('share_facebook', '1');
INSERT INTO `fantasy_config` (`cfg_key`, `cfg_value`) VALUES('share_twitter', '1');
INSERT INTO `fantasy_config` (`cfg_key`, `cfg_value`) VALUES('share_digg', '1');
INSERT INTO `fantasy_config` (`cfg_key`, `cfg_value`) VALUES('share_stumble', '1');
INSERT INTO `fantasy_config` (`cfg_key`, `cfg_value`) VALUES('share_addtoany', '1');
#	UPDATE SQL QUERY
#	Version 1.0.2 to 1.0.3
#	REMOVE ALL COMMENTS FOR DIST
INSERT INTO `fantasy_config` (`cfg_key`, `cfg_value`) VALUES('google_analytics_enable', '-1');
INSERT INTO `fantasy_config` (`cfg_key`, `cfg_value`) VALUES('google_analytics_tracking_id', '');
INSERT INTO `fantasy_config` (`cfg_key`, `cfg_value`) VALUES('stats_lab_compatible', '1');
INSERT INTO `fantasy_config` (`cfg_key`, `cfg_value`) VALUES('restrict_admin_leagues', '-1');
INSERT INTO `fantasy_config` (`cfg_key`, `cfg_value`) VALUES('users_create_leagues', '1');
INSERT INTO `fantasy_config` (`cfg_key`, `cfg_value`) VALUES('max_user_leagues', '1');
INSERT INTO `fantasy_config` (`cfg_key`, `cfg_value`) VALUES('primary_contact', '1');
#	UPDATE SQL QUERY
#	Version 1.0.3 to 1.0.4
#	REMOVE ALL COMMENTS FOR DIST
ALTER TABLE `fantasy_transactions` ADD `trade_team_id` INT NOT NULL AFTER `dropped`;
ALTER TABLE `fantasy_teams_record` ADD `scoring_period_id` TINYINT NOT NULL AFTER `year`;
ALTER TABLE `fantasy_leagues_scoring_batting` ADD `scoring_type` TINYINT NOT NULL AFTER `league_id`;
ALTER TABLE `fantasy_leagues_scoring_pitching` ADD `scoring_type` TINYINT NOT NULL AFTER `league_id`;
TRUNCATE `fantasy_leagues_scoring_batting`;
INSERT INTO `fantasy_leagues_scoring_batting` (`league_id`, `scoring_type`, `type_0`, `value_0`, `type_1`, `value_1`, `type_2`, `value_2`, `type_3`, `value_3`, `type_4`, `value_4`, `type_5`, `value_5`, `type_6`, `value_6`, `type_7`, `value_7`, `type_8`, `value_8`, `type_9`, `value_9`, `type_10`, `value_10`, `type_11`, `value_11`) VALUES(0, 1, 3, 0, 8, 0, 11, 0, 9, 0, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1);
INSERT INTO `fantasy_leagues_scoring_batting` (`league_id`, `scoring_type`, `type_0`, `value_0`, `type_1`, `value_1`, `type_2`, `value_2`, `type_3`, `value_3`, `type_4`, `value_4`, `type_5`, `value_5`, `type_6`, `value_6`, `type_7`, `value_7`, `type_8`, `value_8`, `type_9`, `value_9`, `type_10`, `value_10`, `type_11`, `value_11`) VALUES(0, 2, 3, 0, 7, 0, 8, 0, 9, 0, 12, 0, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1);
INSERT INTO `fantasy_leagues_scoring_batting` (`league_id`, `scoring_type`, `type_0`, `value_0`, `type_1`, `value_1`, `type_2`, `value_2`, `type_3`, `value_3`, `type_4`, `value_4`, `type_5`, `value_5`, `type_6`, `value_6`, `type_7`, `value_7`, `type_8`, `value_8`, `type_9`, `value_9`, `type_10`, `value_10`, `type_11`, `value_11`) VALUES(0, 4, 3, 1, 6, 2, 7, 3, 8, 4, 10, 1, 11, 1, 12, 1, 9, 2, 4, -1, 14, 1, 58, -1, -1, -1);
INSERT INTO `fantasy_leagues_scoring_batting` (`league_id`, `scoring_type`, `type_0`, `value_0`, `type_1`, `value_1`, `type_2`, `value_2`, `type_3`, `value_3`, `type_4`, `value_4`, `type_5`, `value_5`, `type_6`, `value_6`, `type_7`, `value_7`, `type_8`, `value_8`, `type_9`, `value_9`, `type_10`, `value_10`, `type_11`, `value_11`) VALUES(0, 3, 3, 0, 6, 0, 7, 0, 8, 0, 12, 0, 9, 0, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1);
TRUNCATE `fantasy_leagues_scoring_pitching`;
INSERT INTO `fantasy_leagues_scoring_pitching` (`league_id`, `scoring_type`, `type_0`, `value_0`, `type_1`, `value_1`, `type_2`, `value_2`, `type_3`, `value_3`, `type_4`, `value_4`, `type_5`, `value_5`, `type_6`, `value_6`, `type_7`, `value_7`, `type_8`, `value_8`, `type_9`, `value_9`, `type_10`, `value_10`, `type_11`, `value_11`) VALUES(0, 1, 29, 0, 38, 0, 34, 0, 32, 0, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1);
INSERT INTO `fantasy_leagues_scoring_pitching` (`league_id`, `scoring_type`, `type_0`, `value_0`, `type_1`, `value_1`, `type_2`, `value_2`, `type_3`, `value_3`, `type_4`, `value_4`, `type_5`, `value_5`, `type_6`, `value_6`, `type_7`, `value_7`, `type_8`, `value_8`, `type_9`, `value_9`, `type_10`, `value_10`, `type_11`, `value_11`) VALUES(0, 2, 29, 0, 32, 0, 34, 0, 38, 0, 52, 0, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1);
INSERT INTO `fantasy_leagues_scoring_pitching` (`league_id`, `scoring_type`, `type_0`, `value_0`, `type_1`, `value_1`, `type_2`, `value_2`, `type_3`, `value_3`, `type_4`, `value_4`, `type_5`, `value_5`, `type_6`, `value_6`, `type_7`, `value_7`, `type_8`, `value_8`, `type_9`, `value_9`, `type_10`, `value_10`, `type_11`, `value_11`) VALUES(0, 3, 29, 0, 34, 0, 38, 0, 52, 0, 54, 0, 32, 0, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1);
INSERT INTO `fantasy_leagues_scoring_pitching` (`league_id`, `scoring_type`, `type_0`, `value_0`, `type_1`, `value_1`, `type_2`, `value_2`, `type_3`, `value_3`, `type_4`, `value_4`, `type_5`, `value_5`, `type_6`, `value_6`, `type_7`, `value_7`, `type_8`, `value_8`, `type_9`, `value_9`, `type_10`, `value_10`, `type_11`, `value_11`) VALUES(0, 4, 29, 7, 30, -5, 38, 1, 34, 1, 37, -1, 60, -1, 54, 10, 56, 10, 32, 5, 61, -3, -1, -1, -1, -1);
DROP TABLE IF EXISTS `fantasy_sim_summary`;
DROP TABLE IF EXISTS `fantasy_teams_trades`;
DROP TABLE IF EXISTS `fantasy_teams_trades_approvals`;
DROP TABLE IF EXISTS `fantasy_teams_trades_status`;
DROP TABLE IF EXISTS `fantasy_teams_trade_protests`;
CREATE TABLE IF NOT EXISTS `fantasy_sim_summary` (`id` int(11) NOT NULL auto_increment,`scoring_period_id` tinyint(4) NOT NULL,`sim_date` timestamp NOT NULL default CURRENT_TIMESTAMP,`process_time` smallint(6) NOT NULL,`sim_result` int(11) NOT NULL,`sim_summary` longtext collate utf8_unicode_ci NOT NULL,`comments` varchar(10000) collate utf8_unicode_ci NOT NULL,PRIMARY KEY  (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
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
INSERT INTO `fantasy_config` ( `cfg_key`, `cfg_value`) VALUES('defaultExpiration', '100');
INSERT INTO `fantasy_config` ( `cfg_key`, `cfg_value`) VALUES('minProtests', '3');
INSERT INTO `fantasy_config` ( `cfg_key`, `cfg_value`) VALUES('protestPeriodDays', '3');
INSERT INTO `fantasy_config` ( `cfg_key`, `cfg_value`) VALUES('limit_load_all_sql', '1');