RENAME TABLE `{prefix}user_groups` TO `{prefix}auth_groups` ;

RENAME TABLE `{prefix}users` TO `{prefix}auth_users` ;

ALTER TABLE `{prefix}sites` CHANGE `dir` `theme` TINYTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;


/* Create table in target */
CREATE TABLE `{prefix}auth_permissions`(
	`plugin` varchar(50) COLLATE utf8_unicode_ci NOT NULL  , 
	`name` varchar(100) COLLATE utf8_unicode_ci NOT NULL  , 
	`group_id` tinyint(3) unsigned NULL  , 
	`user_id` int(10) unsigned NULL  , 
	`data` text COLLATE utf8_unicode_ci NOT NULL  , 
	KEY `permissions_id`(`user_id`) 
) ENGINE=MyISAM DEFAULT CHARSET='utf8' COLLATE='utf8_unicode_ci';

INSERT INTO `{prefix}auth_permissions` (`plugin`, `name`, `group_id`, `user_id`, `data`) VALUES 
('core', 'access_admin', NULL, 1, '1'),
('core', 'admin', NULL, 1, '1');


/* Alter table in target */
ALTER TABLE `{prefix}auth_users` 
	ADD UNIQUE KEY `username`(`username`) ;

/* Alter table in target */
ALTER TABLE `{prefix}content_archive` 
	CHANGE `site` `site` tinyint(4)   NOT NULL DEFAULT 0 after `idp` ;

/* Alter table in target */
ALTER TABLE `{prefix}gallery_thumbnail_types` 
	ADD COLUMN `use_adaptive_resize` tinyint(1) unsigned   NOT NULL DEFAULT 1 after `thumbnail_quality` ;

/* Alter table in target */
ALTER TABLE `{prefix}languages` 
	CHANGE `site` `site` tinyint(4) unsigned   NOT NULL DEFAULT 1 first ;

/* Alter table in target */
ALTER TABLE `{prefix}pages` 
	CHANGE `site` `site` int(11) unsigned   NOT NULL DEFAULT 0 after `id` , 
	CHANGE `idp` `idp` int(11) unsigned   NOT NULL DEFAULT 0 after `site` , 
	CHANGE `nr` `nr` int(11) unsigned   NOT NULL DEFAULT 0 after `idp` , 
	CHANGE `nomenu` `nomenu` int(1)   NOT NULL DEFAULT 0 after `hot` ;

/* Create table in target */
CREATE TABLE `{prefix}path_index`(
	`pid` int(10) unsigned NOT NULL  , 
	`ln` varchar(2) COLLATE utf8_unicode_ci NOT NULL  , 
	`path` text COLLATE utf8_unicode_ci NOT NULL  
) ENGINE=MyISAM DEFAULT CHARSET='utf8' COLLATE='utf8_unicode_ci';


/* Alter table in target */
ALTER TABLE `{prefix}sites` 
	CHANGE `id` `id` tinyint(4) unsigned   NOT NULL auto_increment first , 
	CHANGE `editor_background` `editor_background` varchar(10)  COLLATE utf8_unicode_ci NOT NULL after `editor_width` , 
	ADD COLUMN `active` tinyint(1) unsigned   NOT NULL DEFAULT 1 after `editor_background` ;

/* Alter table in target */
ALTER TABLE `{prefix}variables` 
	CHANGE `vkey` `vkey` varchar(64)  COLLATE utf8_unicode_ci NOT NULL first , 
	CHANGE `site` `site` tinyint(3) unsigned   NOT NULL DEFAULT 0 after `controller` ;

	
/* If vkey collation is needed to be utf8_bin, alter only 'site' column
ALTER TABLE `{prefix}variables`
CHANGE `site` `site` tinyint(3) unsigned   NOT NULL DEFAULT 0 after `controller`; */
