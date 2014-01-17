ALTER TABLE `pc_pages` ADD `target` SMALLINT( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE `pc_content` ADD `info_mobile` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL AFTER `info3`;