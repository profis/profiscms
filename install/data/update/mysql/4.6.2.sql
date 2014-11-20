DELETE FROM `{prefix}site_users_meta` WHERE `id` NOT IN (SELECT `id` FROM `{prefix}site_users`);

ALTER TABLE `{prefix}site_users_meta`
	ADD CONSTRAINT `{prefix}site_users_meta_ibfk_1` FOREIGN KEY (`id`) REFERENCES `{prefix}site_users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
