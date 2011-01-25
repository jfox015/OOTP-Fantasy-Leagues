CREATE TABLE IF NOT EXISTS `dbtest` (`id` int(11) NOT NULL auto_increment,`strCode` tinytext collate utf8_unicode_ci NOT NULL,`strName` longtext collate utf8_unicode_ci NOT NULL,PRIMARY KEY  (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
INSERT INTO `dbtest` VALUES(1, 'abc', 'Testing');
ALTER TABLE `dbtest` ADD `intValue` TINYINT NOT NULL;
DELETE FROM `dbtest`;
DROP TABLE IF EXISTS `dbtest`;