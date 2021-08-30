ALTER TABLE `kp_competition_equipe` 
ADD `logo` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL  
AFTER `Code_club`;

UPDATE kp_competition_equipe 
SET logo = (
  CASE 
    WHEN LENGTH(Code_club) = 4 
    THEN CONCAT('KIP/logo/', Code_club, '-logo.png') 
    ELSE CONCAT('Nations/', SUBSTRING(Code_club, 1, 3), '.png')
  END
)
WHERE LENGTH(Code_club) > 3;

UPDATE `kp_competition_equipe` SET logo = null WHERE `Code_club` LIKE 'CR18';
UPDATE `kp_competition_equipe` SET logo = null WHERE `Code_club` LIKE '4213';
UPDATE `kp_competition_equipe` SET logo = null WHERE `Code_club` LIKE '0111';
UPDATE `kp_competition_equipe` SET logo = null WHERE `Code_club` LIKE '0704';
UPDATE `kp_competition_equipe` SET logo = null WHERE `Code_club` LIKE '6221';
UPDATE `kp_competition_equipe` SET logo = null WHERE `Code_club` LIKE '5915';
UPDATE `kp_competition_equipe` SET logo = null WHERE `Code_club` LIKE '7304';
UPDATE `kp_competition_equipe` SET logo = null WHERE `Code_club` LIKE '0506';
UPDATE `kp_competition_equipe` SET logo = null WHERE `Code_club` LIKE '59CD';
UPDATE `kp_competition_equipe` SET logo = null WHERE `Code_club` LIKE '5410';
UPDATE `kp_competition_equipe` SET logo = null WHERE `Code_club` LIKE '62CD';
UPDATE `kp_competition_equipe` SET logo = null WHERE `Code_club` LIKE '0162';
