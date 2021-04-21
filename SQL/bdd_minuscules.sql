DROP TABLE `gickp_News`;

RENAME TABLE `gickp_Arbitre` TO `kp_arbitre`; 
RENAME TABLE `gickp_Categorie` TO `kp_categorie`; 
RENAME TABLE `gickp_Chrono` TO `kp_chrono`; 
RENAME TABLE `gickp_Club` TO `kp_club`; 
RENAME TABLE `gickp_Comite_dep` TO `kp_cd`; 
RENAME TABLE `gickp_Comite_reg` TO `kp_cr`; 
RENAME TABLE `gickp_Competitions_Groupes` TO `kp_groupe`; 
RENAME TABLE `gickp_Competitions_Equipes_Niveau` TO `kp_competition_equipe_niveau`; 
RENAME TABLE `gickp_Competitions_Equipes_Journee` TO `kp_competition_equipe_journee`; 
RENAME TABLE `gickp_Competitions_Equipes_Joueurs` TO `kp_competition_equipe_joueur`; 
RENAME TABLE `gickp_Competitions_Equipes_Init` TO `kp_competition_equipe_init`; 
RENAME TABLE `gickp_Competitions_Equipes` TO `kp_competition_equipe`; 
RENAME TABLE `gickp_Competitions` TO `kp_competition`; 
RENAME TABLE `gickp_Equipe` TO `kp_equipe`; 
RENAME TABLE `gickp_Evenement_Journees` TO `kp_evenement_journee`; 
RENAME TABLE `gickp_Evenement_Export` TO `kp_evenement_export`; 
RENAME TABLE `gickp_Evenement` TO `kp_evenement`; 
RENAME TABLE `gickp_Journal` TO `kp_journal`; 
RENAME TABLE `gickp_Journees` TO `kp_journee`; 
RENAME TABLE `gickp_Liste_Coureur` TO `kp_licence`; 
RENAME TABLE `gickp_Matchs_Joueurs` TO `kp_match_joueur`; 
RENAME TABLE `gickp_Matchs_Detail` TO `kp_match_detail`; 
RENAME TABLE `gickp_Matchs` TO `kp_match`; 
RENAME TABLE `gickp_Rc` TO `kp_rc`; 
RENAME TABLE `gickp_Recherche_Licence` TO `kp_recherche_licence`; 
RENAME TABLE `gickp_Ref_Journee` TO `kp_journee_ref`; 
RENAME TABLE `gickp_Saison` TO `kp_saison`; 
RENAME TABLE `gickp_Surclassements` TO `kp_surclassement`; 
RENAME TABLE `gickp_Tv` TO `kp_tv`; 
RENAME TABLE `gickp_Utilisateur` TO `kp_user`; 

ALTER TABLE `kp_chrono` DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;
ALTER TABLE `kp_chrono` CHANGE `action` `action` VARCHAR(5) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 
ALTER TABLE `kp_chrono` CHANGE `start_time` `start_time` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 
ALTER TABLE `kp_chrono` CHANGE `run_time` `run_time` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 
ALTER TABLE `kp_chrono` CHANGE `max_time` `max_time` VARCHAR(5) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 

ALTER TABLE `kp_journee_ref` DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;
UPDATE kp_journee_ref SET nom = REPLACE(nom, 'Trophée', 'Trophee');
ALTER TABLE `kp_journee_ref` CHANGE `nom` `nom` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 

ALTER TABLE `kp_surclassement` DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;
ALTER TABLE `kp_surclassement` CHANGE `Saison` `Saison` VARCHAR(6) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 
ALTER TABLE `kp_surclassement` CHANGE `Cat` `Cat` VARCHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 

ALTER TABLE `kp_tv` DEFAULT CHARSET=utf8 COLLATE utf8_general_ci 
ALTER TABLE `kp_tv` CHANGE `Url` `Url` VARCHAR(1024) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 

-- MyBDD => import_calendrier, importCalendrier_competitions, duppliJournees, GetEvenementJournees


ALTER TABLE `kp_arbitre` CHANGE `Regional` `regional` CHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 
ALTER TABLE `kp_arbitre` CHANGE `InterRegional` `interregional` CHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 
ALTER TABLE `kp_arbitre` CHANGE `National` `national` CHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 
ALTER TABLE `kp_arbitre` CHANGE `International` `international` CHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 
ALTER TABLE `kp_arbitre` CHANGE `Arb` `arbitre` CHAR(3) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 
ALTER TABLE `kp_arbitre` CHANGE `Livret` `livret` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''; 

-- ALTER TABLE `kp_arbitre` CHANGE `regional` `Regional` CHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 
-- ALTER TABLE `kp_arbitre` CHANGE `inter_regional` `InterRegional` CHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 
-- ALTER TABLE `kp_arbitre` CHANGE `national` `National` CHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 
-- ALTER TABLE `kp_arbitre` CHANGE `international` `International` CHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 
-- ALTER TABLE `kp_arbitre` CHANGE `arbitre` `Arb` CHAR(3) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 
-- ALTER TABLE `kp_arbitre` CHANGE `livret` `Livret` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''; 

