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
