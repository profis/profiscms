CREATE TABLE IF NOT EXISTS `{prefix}db_version` (
  `plugin` VARCHAR(255) NOT NULL,
  `version` VARCHAR(32) NOT NULL,
  PRIMARY KEY (`plugin`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8  COLLATE = utf8_general_ci;

INSERT INTO `{prefix}db_version` (`plugin`, `version`) VALUES('', '4.5.0');
