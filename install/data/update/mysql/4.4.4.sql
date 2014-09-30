ALTER TABLE `{prefix}content` ADD `ln_redirect` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' AFTER `update_by`;

ALTER TABLE `{prefix}pages` ADD `source_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `reference_id`;