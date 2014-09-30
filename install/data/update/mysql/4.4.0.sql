ALTER TABLE `{prefix}languages` ADD `disabled` SMALLINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `nr`;

ALTER TABLE `{prefix}content` ADD `permalink` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci  NOT NULL AFTER `route` ;


ALTER TABLE `{prefix}content` ADD `custom_name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL AFTER `name` ;

ALTER TABLE `{prefix}content` CHANGE `keywords` `keywords` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `description` `description` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL 