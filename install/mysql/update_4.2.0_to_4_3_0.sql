ALTER TABLE `pc_sites` CHANGE `dir` `theme` TINYTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

/* Alter table in target */
ALTER TABLE `pc_gallery_thumbnail_types` 
	ADD COLUMN `use_adaptive_resize` tinyint(1) unsigned   NOT NULL DEFAULT 1 after `thumbnail_quality` ;

/* Alter table in target */
ALTER TABLE `pc_sites` 
	CHANGE `editor_width` `editor_width` varchar(4)  COLLATE utf8_unicode_ci NOT NULL after `theme` , 
	ADD COLUMN `active` tinyint(1) unsigned   NOT NULL DEFAULT 1 after `editor_background` ;
