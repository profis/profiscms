ALTER TABLE `{prefix}pages` 
	ADD COLUMN `nositemap` INT(1) NOT NULL DEFAULT 0 AFTER `nomenu`;
