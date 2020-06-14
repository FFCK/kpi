UPDATE `gickp_Competitions` SET `Code` = REPLACE(`Code`, 'N_21', 'N21') WHERE `Code` LIKE 'N_21%';
UPDATE `gickp_Competitions` SET `Code` = REPLACE(`Code`, 'N_15', 'N15') WHERE `Code` LIKE 'N_18%';
UPDATE `gickp_Competitions` SET `Code` = REPLACE(`Code`, 'N_15', 'N15') WHERE `Code` LIKE 'N_15%';

UPDATE `gickp_Competitions_Equipes` SET `Code_compet` = REPLACE(`Code_compet`, 'N_21', 'N21') WHERE `Code_compet` LIKE 'N_21%';
UPDATE `gickp_Competitions_Equipes` SET `Code_compet` = REPLACE(`Code_compet`, 'N_18', 'N18') WHERE `Code_compet` LIKE 'N_18%';
UPDATE `gickp_Competitions_Equipes` SET `Code_compet` = REPLACE(`Code_compet`, 'N_15', 'N15') WHERE `Code_compet` LIKE 'N_15%';

UPDATE `gickp_Journees` SET `Code_competition` = REPLACE(`Code_competition`, 'N_21', 'N21') WHERE `Code_competition` LIKE 'N_21%';
UPDATE `gickp_Journees` SET `Code_competition` = REPLACE(`Code_competition`, 'N_18', 'N18') WHERE `Code_competition` LIKE 'N_18%';
UPDATE `gickp_Journees` SET `Code_competition` = REPLACE(`Code_competition`, 'N_15', 'N15') WHERE `Code_competition` LIKE 'N_15%';

UPDATE `gickp_Competitions_Groupes` SET `Groupe` = REPLACE(`Groupe`, 'N_21', 'N21') WHERE `Groupe` LIKE 'N_21';
UPDATE `gickp_Competitions` SET `Code_ref` = REPLACE(`Code_ref`, 'N_21', 'N21') WHERE `Code_ref` LIKE 'N_21%';

