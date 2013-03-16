ALTER TABLE `pc_languages` ADD `disabled` SMALLINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `pc_content` ADD `permalink` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci  NOT NULL AFTER `route` ;


ALTER TABLE `pc_content` ADD `custom_name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL AFTER `name` ;

ALTER TABLE `pc_content` CHANGE `keywords` `keywords` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `description` `description` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL 