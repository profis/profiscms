ALTER TABLE `{prefix}content` ADD `info_mobile` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL AFTER `info3`;

ALTER TABLE `{prefix}gallery_files` CHANGE `filename` `filename` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
