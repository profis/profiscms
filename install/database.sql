-- phpMyAdmin SQL Dump
-- version 2.9.1.1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Jul 25, 2012 at 03:34 PM
-- Server version: 5.5.24
-- PHP Version: 5.3.10-1ubuntu3.2
-- 
-- Database: `cms4_stable`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `pc_auth_groups`
-- 

CREATE TABLE `pc_auth_groups` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `groupname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `groupname` (`groupname`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `pc_auth_groups`
-- 

INSERT INTO `pc_auth_groups` (`id`, `groupname`) VALUES 
(1, 'Administration');

-- --------------------------------------------------------

-- 
-- Table structure for table `pc_auth_log`
-- 

CREATE TABLE `pc_auth_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ip` int(11) NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL,
  `success` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ip` (`ip`),
  KEY `success` (`success`),
  KEY `username` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `pc_auth_log`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `pc_auth_permissions`
-- 

CREATE TABLE `pc_auth_permissions` (
  `plugin` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `group_id` tinyint(3) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  KEY `permissions_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `pc_auth_permissions`
-- 

INSERT INTO `pc_auth_permissions` (`plugin`, `name`, `group_id`, `user_id`, `data`) VALUES 
('core', 'access_admin', NULL, 1, '1'),
('core', 'admin', NULL, 1, '1');

-- --------------------------------------------------------

-- 
-- Table structure for table `pc_auth_users`
-- 

CREATE TABLE `pc_auth_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` tinyint(3) unsigned NOT NULL,
  `username` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `pass` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `language` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `pc_auth_users`
-- 

INSERT INTO `pc_auth_users` (`id`, `group_id`, `username`, `pass`, `language`) VALUES 
(1, 1, 'admin', 'admin', '');

-- --------------------------------------------------------

-- 
-- Table structure for table `pc_config`
-- 

CREATE TABLE `pc_config` (
  `plugin` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ckey` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`plugin`,`ckey`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `pc_config`
-- 

INSERT INTO `pc_config` (`plugin`, `ckey`, `value`) VALUES 
('', 'active_plugins', '');

-- --------------------------------------------------------

-- 
-- Table structure for table `pc_content`
-- 

CREATE TABLE `pc_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `ln` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `custom_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `info` text COLLATE utf8_unicode_ci NOT NULL,
  `info2` text COLLATE utf8_unicode_ci NOT NULL,
  `info3` text COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `keywords` text COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `route` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `permalink` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `text` longtext COLLATE utf8_unicode_ci NOT NULL,
  `last_update` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `update_by` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tree_id` (`pid`),
  KEY `ln` (`ln`),
  KEY `route` (`route`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `pc_content`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `pc_content_archive`
-- 

CREATE TABLE `pc_content_archive` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tree_id` int(11) NOT NULL DEFAULT '0',
  `ln` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `idp` int(11) NOT NULL DEFAULT '0',
  `site` tinyint(4) NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL,
  `time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `data` longblob NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tree_id` (`tree_id`),
  KEY `ln` (`ln`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `pc_content_archive`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `pc_domains`
-- 

CREATE TABLE `pc_domains` (
  `mask` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `site` int(11) DEFAULT NULL,
  `ln` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nr` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`mask`),
  KEY `site` (`site`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `pc_domains`
-- 

INSERT INTO `pc_domains` (`mask`, `site`, `ln`, `nr`) VALUES 
('%', 1, 'en', 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `pc_gallery_categories`
-- 

CREATE TABLE `pc_gallery_categories` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `directory` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `lft` int(10) NOT NULL,
  `rgt` int(10) NOT NULL,
  `parent` mediumint(8) unsigned NOT NULL,
  `author` mediumint(8) unsigned NOT NULL,
  `date_created` int(10) unsigned NOT NULL,
  `date_trashed` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent` (`parent`),
  KEY `lft` (`lft`,`rgt`),
  KEY `date_trashed` (`date_trashed`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `pc_gallery_categories`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `pc_gallery_files`
-- 

CREATE TABLE `pc_gallery_files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `extension` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `category_id` mediumint(8) unsigned NOT NULL,
  `size` int(9) unsigned NOT NULL,
  `date_added` int(10) unsigned NOT NULL,
  `date_modified` int(10) unsigned NOT NULL,
  `date_trashed` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `date_trashed` (`date_trashed`),
  KEY `size` (`size`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `pc_gallery_files`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `pc_gallery_files_in_use`
-- 

CREATE TABLE `pc_gallery_files_in_use` (
  `file_id` int(10) unsigned NOT NULL,
  `content_id` int(10) unsigned NOT NULL,
  `content_block` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`file_id`,`content_id`,`content_block`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `pc_gallery_files_in_use`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `pc_gallery_thumbnail_types`
-- 

CREATE TABLE `pc_gallery_thumbnail_types` (
  `thumbnail_type` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `thumbnail_max_w` smallint(5) unsigned NOT NULL,
  `thumbnail_max_h` smallint(5) unsigned NOT NULL,
  `thumbnail_quality` tinyint(3) unsigned NOT NULL,
  `use_adaptive_resize` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`thumbnail_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `pc_gallery_thumbnail_types`
-- 

INSERT INTO `pc_gallery_thumbnail_types` (`thumbnail_type`, `thumbnail_max_w`, `thumbnail_max_h`, `thumbnail_quality`, `use_adaptive_resize`) VALUES 
('thumbnail', 75, 55, 76, 1),
('small', 160, 120, 76, 0),
('large', 640, 480, 76, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `pc_languages`
-- 

CREATE TABLE `pc_languages` (
  `site` tinyint(4) unsigned NOT NULL DEFAULT '1',
  `ln` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `name` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `nr` int(11) NOT NULL DEFAULT '0',
  `disabled` smallint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`site`,`ln`),
  KEY `nr` (`nr`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `pc_languages`
-- 

INSERT INTO `pc_languages` (`site`, `ln`, `name`, `nr`) VALUES 
(1, 'en', 'English', 0),
(1, 'ru', 'Pусский', 2),
(1, 'lt', 'Lietuvių', 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `pc_pages`
-- 

CREATE TABLE `pc_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site` int(11) unsigned NOT NULL DEFAULT '0',
  `idp` int(11) unsigned NOT NULL DEFAULT '0',
  `nr` int(11) unsigned NOT NULL DEFAULT '0',
  `controller` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `front` int(1) NOT NULL DEFAULT '0',
  `route_lock` smallint(1) NOT NULL DEFAULT '0',
  `published` int(1) NOT NULL DEFAULT '0',
  `hot` int(1) NOT NULL DEFAULT '0',
  `nomenu` int(1) NOT NULL DEFAULT '0',
  `deleted` int(1) NOT NULL DEFAULT '0',
  `date_from` int(10) unsigned DEFAULT NULL,
  `date_to` int(10) unsigned DEFAULT NULL,
  `redirect` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date` int(10) unsigned DEFAULT NULL,
  `reference_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idp` (`idp`),
  KEY `nomenu` (`nomenu`),
  KEY `deleted` (`deleted`),
  KEY `front` (`front`),
  KEY `nr` (`nr`),
  KEY `published` (`published`),
  KEY `site` (`site`),
  KEY `date_from` (`date_from`,`date_to`),
  KEY `reference_id` (`reference_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `pc_pages`
-- 

INSERT INTO `pc_pages` (`id`, `site`, `idp`, `nr`, `controller`, `front`, `route_lock`, `published`, `hot`, `nomenu`, `deleted`, `date_from`, `date_to`, `redirect`, `date`, `reference_id`) VALUES 
(1, 1, 0, 0, '', 1, 1, 1, 1, 1, 0, NULL, NULL, NULL, NULL, '');

-- --------------------------------------------------------

-- 
-- Table structure for table `pc_path_index`
-- 

CREATE TABLE `pc_path_index` (
  `pid` int(10) unsigned NOT NULL,
  `ln` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `path` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `pc_path_index`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `pc_site_users`
-- 

CREATE TABLE `pc_site_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `date_registered` int(10) unsigned NOT NULL,
  `last_seen` int(10) unsigned NOT NULL,
  `confirmation` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `flags` int(10) unsigned NOT NULL,
  `login` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `banned` smallint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `pc_site_users`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `pc_sites`
-- 

CREATE TABLE `pc_sites` (
  `id` tinyint(4) unsigned NOT NULL AUTO_INCREMENT,
  `name` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `theme` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `editor_width` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `editor_background` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `pc_sites`
-- 

INSERT INTO `pc_sites` (`id`, `name`, `theme`, `editor_width`, `editor_background`, `active`) VALUES 
(1, 'Default', 'default', '600', 'white', 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `pc_variables`
-- 

CREATE TABLE `pc_variables` (
  `vkey` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `controller` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `site` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ln` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`vkey`,`controller`,`site`,`ln`),
  KEY `site` (`site`,`ln`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `pc_variables`
-- 

