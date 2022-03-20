ALTER TABLE `kp_match_detail_old` DROP FOREIGN KEY `fk_matchs_detail`; 
ALTER TABLE `kp_match_detail_old` 
  ADD CONSTRAINT `fk_matchs_detail_old` FOREIGN KEY (`Id_match`) 
  REFERENCES `kp_match`(`Id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 
ALTER TABLE `kp_match_detail_old` DROP FOREIGN KEY `fk_matchs_details_competiteur`; 
ALTER TABLE `kp_match_detail_old` 
  ADD CONSTRAINT `fk_matchs_details_competiteur_old` FOREIGN KEY (`Competiteur`) 
  REFERENCES `kp_licence`(`Matric`) ON DELETE RESTRICT ON UPDATE RESTRICT; 

ALTER TABLE `kp_match_detail` 
  ADD CONSTRAINT `fk_matchs_detail` FOREIGN KEY (`Id_match`) 
  REFERENCES `kp_match`(`Id`) ON DELETE RESTRICT ON UPDATE RESTRICT; 
ALTER TABLE `kp_match_detail` 
  ADD CONSTRAINT `fk_matchs_details_competiteur` FOREIGN KEY (`Competiteur`) 
  REFERENCES `kp_licence`(`Matric`) ON DELETE RESTRICT ON UPDATE RESTRICT; 

ALTER TABLE `kp_match_detail_old` DROP FOREIGN KEY `fk_matchs_detail_old`;
ALTER TABLE `kp_match_detail_old` DROP FOREIGN KEY `fk_matchs_details_competiteur_old`;