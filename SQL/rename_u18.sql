UPDATE `gickp_Competitions` SET `Code` = REPLACE(`Code`, 'N_21', 'N21') WHERE `Code` LIKE 'N_21%';
UPDATE `gickp_Competitions` SET `Code` = REPLACE(`Code`, 'N_18', 'N18') WHERE `Code` LIKE 'N_18%';
UPDATE `gickp_Competitions` SET `Code` = REPLACE(`Code`, 'N_15', 'N15') WHERE `Code` LIKE 'N_15%';

UPDATE `gickp_Competitions_Equipes` SET `Code_compet` = REPLACE(`Code_compet`, 'N_21', 'N21') WHERE `Code_compet` LIKE 'N_21%';
UPDATE `gickp_Competitions_Equipes` SET `Code_compet` = REPLACE(`Code_compet`, 'N_18', 'N18') WHERE `Code_compet` LIKE 'N_18%';
UPDATE `gickp_Competitions_Equipes` SET `Code_compet` = REPLACE(`Code_compet`, 'N_15', 'N15') WHERE `Code_compet` LIKE 'N_15%';

UPDATE `gickp_Journees` SET `Code_competition` = REPLACE(`Code_competition`, 'N_21', 'N21') WHERE `Code_competition` LIKE 'N_21%';
UPDATE `gickp_Journees` SET `Code_competition` = REPLACE(`Code_competition`, 'N_18', 'N18') WHERE `Code_competition` LIKE 'N_18%';
UPDATE `gickp_Journees` SET `Code_competition` = REPLACE(`Code_competition`, 'N_15', 'N15') WHERE `Code_competition` LIKE 'N_15%';

UPDATE `gickp_Competitions_Groupes` SET `Groupe` = REPLACE(`Groupe`, 'N_21', 'N21') WHERE `Groupe` LIKE 'N_21';
UPDATE `gickp_Competitions` SET `Code_ref` = REPLACE(`Code_ref`, 'N_21', 'N21') WHERE `Code_ref` LIKE 'N_21%';

UPDATE `gickp_Competitions` SET `Code` = REPLACE(`Code`, 'N18_2', 'N18A') WHERE `Code` LIKE 'N18%';
UPDATE `gickp_Competitions` SET `Code` = REPLACE(`Code`, 'N15_2', 'N15A') WHERE `Code` LIKE 'N15%';


UPDATE `gickp_Utilisateur` SET `Filtre_competition` = REPLACE(`Filtre_competition`, 'N_21', 'N21') WHERE `Filtre_competition` LIKE '%N_21%';
UPDATE `gickp_Utilisateur` SET `Filtre_competition` = REPLACE(`Filtre_competition`, 'N_18', 'N18') WHERE `Filtre_competition` LIKE '%N_18%';
UPDATE `gickp_Utilisateur` SET `Filtre_competition` = REPLACE(`Filtre_competition`, 'N_15', 'N15') WHERE `Filtre_competition` LIKE '%N_15%';
UPDATE `gickp_Utilisateur` SET `Filtre_competition` = REPLACE(`Filtre_competition`, 'N18_2', 'N18A') WHERE `Filtre_competition` LIKE '%N18_2%';
UPDATE `gickp_Utilisateur` SET `Filtre_competition` = REPLACE(`Filtre_competition`, 'N15_2', 'N15A') WHERE `Filtre_competition` LIKE '%N15_2%';

UPDATE `gickp_Utilisateur` SET `Filtre_competition_sql` = REPLACE(`Filtre_competition_sql`, 'N_21', 'N21') WHERE `Filtre_competition_sql` LIKE '%N_21%';
UPDATE `gickp_Utilisateur` SET `Filtre_competition_sql` = REPLACE(`Filtre_competition_sql`, 'N_18', 'N18') WHERE `Filtre_competition_sql` LIKE '%N_18%';
UPDATE `gickp_Utilisateur` SET `Filtre_competition_sql` = REPLACE(`Filtre_competition_sql`, 'N_15', 'N15') WHERE `Filtre_competition_sql` LIKE '%N_15%';
UPDATE `gickp_Utilisateur` SET `Filtre_competition_sql` = REPLACE(`Filtre_competition_sql`, 'N18_2', 'N18A') WHERE `Filtre_competition_sql` LIKE '%N18_2%';
UPDATE `gickp_Utilisateur` SET `Filtre_competition_sql` = REPLACE(`Filtre_competition_sql`, 'N15_2', 'N15A') WHERE `Filtre_competition_sql` LIKE '%N15_2%';