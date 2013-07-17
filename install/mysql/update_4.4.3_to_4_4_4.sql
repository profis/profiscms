ALTER TABLE `pc_content` ADD `ln_redirect` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '';

ALTER TABLE `pc_pages` ADD `source_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0';