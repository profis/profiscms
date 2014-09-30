ALTER TABLE `{prefix}site_users` CHANGE `email` `email` VARCHAR(255) COLLATE utf8_unicode_ci NULL;
ALTER TABLE `{prefix}site_users` CHANGE `password` `password` VARCHAR(32) COLLATE utf8_unicode_ci NULL;
ALTER TABLE `{prefix}site_users` CHANGE `login` `login` VARCHAR(100) COLLATE utf8_unicode_ci NULL;
ALTER TABLE `{prefix}site_users` CHANGE `confirmation` `confirmation` VARCHAR(32) COLLATE utf8_unicode_ci NULL;

CREATE TABLE IF NOT EXISTS `{prefix}site_users_external` (
  `user_id` int(10) unsigned NOT NULL,
  `provider` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `uid` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`provider`,`uid`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `{prefix}site_users_external`
  ADD CONSTRAINT `{prefix}site_users_external_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `{prefix}site_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
