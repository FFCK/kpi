ALTER TABLE `kp_competition_equipe`  ADD `logo` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL  AFTER `Code_club`;

UPDATE kp_competition_equipe 
SET logo = (
  CASE 
    WHEN LENGTH(Code_club) = 4 
    THEN CONCAT('KIP/logo/', Code_club, '-logo.png') 
    ELSE CONCAT('Nations/', SUBSTRING(Code_club, 1, 3), '.png')
  END
)
WHERE LENGTH(Code_club) > 3