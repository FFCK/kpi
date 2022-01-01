-- Remplacement Id par uuid
CREATE TABLE `kp_match_detail_copie` LIKE `kp_match_detail`;
INSERT `kp_match_detail_copie` SELECT * FROM `kp_match_detail`;

ALTER TABLE `kp_match_detail_copie` 
  ADD `uuid` VARCHAR(32) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT '' 
  AFTER `Id`;

UPDATE `kp_match_detail_copie` 
  SET `uuid` = REPLACE(UUID(),'-','');

ALTER TABLE `kp_match_detail_copie` 
  CHANGE `Id` `Id` INT(10) UNSIGNED NOT NULL;

ALTER TABLE `kp_match_detail_copie` 
  DROP PRIMARY KEY, ADD PRIMARY KEY(`uuid`);

ALTER TABLE `kp_match_detail_copie` 
  DROP `Id`;

ALTER TABLE `kp_match_detail_copie` 
  CHANGE `uuid` `Id` VARCHAR(32) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT REPLACE(UUID(),'-','');