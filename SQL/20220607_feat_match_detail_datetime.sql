ALTER TABLE `kp_match_detail` ADD `date_insert` DATETIME NULL DEFAULT CURRENT_TIMESTAMP AFTER `Equipe_A_B`;
ALTER TABLE `kp_match_detail` ADD INDEX(`date_insert`);