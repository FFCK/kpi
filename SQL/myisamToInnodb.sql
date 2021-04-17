ALTER TABLE villes_france_free ENGINE=InnoDB;
ALTER TABLE gickp_Arbitre ENGINE=InnoDB;
ALTER TABLE gickp_Categorie ENGINE=InnoDB;
ALTER TABLE gickp_Chrono ENGINE=InnoDB;
ALTER TABLE gickp_Club ENGINE=InnoDB;
ALTER TABLE gickp_Comite_dep ENGINE=InnoDB;
ALTER TABLE gickp_Comite_reg ENGINE=InnoDB;
ALTER TABLE gickp_Competitions ENGINE=InnoDB;
ALTER TABLE gickp_Competitions_Equipes ENGINE=InnoDB;
ALTER TABLE gickp_Competitions_Equipes_Init ENGINE=InnoDB;
ALTER TABLE gickp_Competitions_Equipes_Joueurs ENGINE=InnoDB;
ALTER TABLE gickp_Competitions_Equipes_Journee ENGINE=InnoDB;
ALTER TABLE gickp_Competitions_Equipes_Niveau ENGINE=InnoDB;
ALTER TABLE gickp_Competitions_Groupes ENGINE=InnoDB;
ALTER TABLE gickp_Equipe ENGINE=InnoDB;
ALTER TABLE gickp_Evenement ENGINE=InnoDB;
ALTER TABLE gickp_Evenement_Export ENGINE=InnoDB;
ALTER TABLE gickp_Evenement_Journees ENGINE=InnoDB;
ALTER TABLE gickp_Journal ENGINE=InnoDB;
ALTER TABLE gickp_Journees ENGINE=InnoDB;
ALTER TABLE gickp_Liste_Coureur ENGINE=InnoDB;
ALTER TABLE gickp_Liste_Coureur_Legacy ENGINE=InnoDB;
ALTER TABLE gickp_Matchs ENGINE=InnoDB;
ALTER TABLE gickp_Matchs_Detail ENGINE=InnoDB;
ALTER TABLE gickp_Matchs_Joueurs ENGINE=InnoDB;
ALTER TABLE gickp_News ENGINE=InnoDB;
-- ALTER TABLE gickp_Rc ENGINE=InnoDB;
ALTER TABLE gickp_Recherche_Licence ENGINE=InnoDB;
ALTER TABLE gickp_Ref_Journee ENGINE=InnoDB;
ALTER TABLE gickp_Saison ENGINE=InnoDB;
ALTER TABLE gickp_Surclassements ENGINE=InnoDB;
ALTER TABLE gickp_Tv ENGINE=InnoDB;
ALTER TABLE gickp_Utilisateur ENGINE=InnoDB;

-- Suppression table inutile
DROP TABLE `gickp_Club1`;

-- Préparation relations --

-- arbitres --
DELETE a 
FROM gickp_Arbitre a
LEFT OUTER JOIN gickp_Liste_Coureur lc 
	ON (lc.Matric = a.Matric)
WHERE lc.Matric IS NULL;

ALTER TABLE `gickp_Liste_Coureur` CHANGE `Matric` `Matric` INT(11) UNSIGNED NOT NULL DEFAULT '0'; 

ALTER TABLE gickp_Arbitre
    ADD CONSTRAINT fk_lc_matric FOREIGN KEY (Matric) REFERENCES gickp_Liste_Coureur(Matric);

-- gickp_Club -- 
ALTER TABLE `gickp_Club` CHANGE `Code_comite_dep` `Code_comite_dep` VARCHAR(6) 
    CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 

ALTER TABLE gickp_Club
    ADD CONSTRAINT fk_club_cd 
    FOREIGN KEY (Code_comite_dep) 
    REFERENCES gickp_Comite_dep(Code);

-- gickp_Comite_dep -- 
ALTER TABLE `gickp_Comite_dep` CHANGE `Code_comite_reg` `Code_comite_reg` 
    VARCHAR(6) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;  

ALTER TABLE gickp_Comite_dep
    ADD CONSTRAINT fk_cd_cr 
    FOREIGN KEY (Code_comite_reg) 
    REFERENCES gickp_Comite_reg(Code);

-- gickp_Competitions --
DELETE a 
FROM gickp_Competitions a
LEFT OUTER JOIN gickp_Saison s 
	ON (s.Code = a.Code_saison)
WHERE s.Code IS NULL;

ALTER TABLE `gickp_Competitions` CHANGE `Code_saison` `Code_saison` CHAR(4) 
    CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''; 

INSERT INTO `gickp_Saison` (`Code`, `Etat`, `Nat_debut`, `Nat_fin`, `Inter_debut`, `Inter_fin`) 
    VALUES ('1000', 'I', '1900-01-01', '1900-12-31', '1900-01-01', '1900-12-31');

ALTER TABLE gickp_Competitions
    ADD CONSTRAINT fk_competitions_saison 
    FOREIGN KEY (Code_saison) 
    REFERENCES gickp_Saison(Code);

-- gickp_Competitions (2) --
ALTER TABLE `gickp_Competitions` CHANGE `Code_ref` `Code_ref` VARCHAR(10) 
    CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 

ALTER TABLE `gickp_Competitions_Groupes` ADD UNIQUE(`Groupe`);
ALTER TABLE `gickp_Competitions_Groupes` ADD INDEX(`Groupe`); 

ALTER TABLE gickp_Competitions
    ADD CONSTRAINT fk_competitions_groupes 
    FOREIGN KEY (Code_ref) 
    REFERENCES gickp_Competitions_Groupes(Groupe);

-- gickp_Competitions_Equipes -- 
UPDATE `gickp_Journees` SET `Code_competition` = 'N18A' WHERE `Code_competition` = 'N18_2';
UPDATE `gickp_Journees` SET `Code_competition` = 'N15A' WHERE `Code_competition` = 'N15_2' ;
UPDATE `gickp_Competitions_Equipes` SET `Code_compet` = 'N18A' WHERE `Code_compet` = 'N18_2' ;
UPDATE `gickp_Competitions_Equipes` SET `Code_compet` = 'N15A' WHERE `Code_compet` = 'N15_2' ;
UPDATE `gickp_Competitions_Equipes` SET Code_compet = 'CMH21' WHERE Code_compet = 'CJMH' ;
UPDATE `gickp_Competitions_Equipes` SET Code_compet = 'CMF21' WHERE Code_compet = 'CJMF' ;

DELETE a 
FROM gickp_Competitions_Equipes a
LEFT OUTER JOIN gickp_Competitions b 
	ON (a.Code_compet = b.Code AND a.Code_saison = b.Code_saison)
WHERE b.Code IS NULL;

ALTER TABLE `gickp_Competitions_Equipes` CHANGE `Code_compet` `Code_compet` VARCHAR(12) 
    CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';

ALTER TABLE gickp_Competitions_Equipes
    ADD CONSTRAINT fk_competitions_equipes 
    FOREIGN KEY (Code_compet, Code_saison) 
    REFERENCES gickp_Competitions(Code, Code_saison);

-- gickp_Competitions_Equipes (2) --
ALTER TABLE `gickp_Competitions_Equipes` CHANGE `Code_club` `Code_club` VARCHAR(6) 
    CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE gickp_Competitions_Equipes
    ADD CONSTRAINT fk_compet_equipes_club 
    FOREIGN KEY (Code_club) 
    REFERENCES gickp_Club(Code);

-- gickp_Competitions_Equipes_Init --
DELETE a 
FROM gickp_Competitions_Equipes_Init a
LEFT OUTER JOIN gickp_Competitions_Equipes b 
	ON (a.Id = b.Id)
WHERE b.Id IS NULL;

ALTER TABLE gickp_Competitions_Equipes_Init
    ADD CONSTRAINT fk_init_id 
    FOREIGN KEY (Id) 
    REFERENCES gickp_Competitions_Equipes(Id);

-- gickp_Competitions_Equipes_Joueurs --
CREATE TABLE equipes_joueurs_legacy 
    SELECT a.* FROM gickp_Competitions_Equipes_Joueurs a 
    LEFT OUTER JOIN gickp_Competitions_Equipes b 
    ON (a.Id_equipe = b.Id) 
    WHERE b.Id IS NULL ;

ALTER TABLE `gickp_Competitions_Equipes_Joueurs` CHANGE `Id_equipe` `Id_equipe` 
    INT(10) UNSIGNED NOT NULL;

INSERT INTO gickp_Liste_Coureur
SELECT a.Matric, ce.Code_saison Origine, a.Nom, a.Prenom,
    a.Sexe, '0000-00-00' Naissance, '' Club, ce.Code_club Numero_club,
    '' Comite_dept, cl.Code_comite_dep Numero_comite_dept,
    '' Comite_reg, cd.Code_comite_reg Numero_comite_reg,
    NULL Etat, NULL Pagaie_EVI, NULL Pagaie_MER, NULL Pagaie_ECA,
    NULL Date_certificat_CK, NULL Date_certificat_APS, NULL Reserve,
    NULL Etat_certificat_APS, NULL Etat_certificat_CK
    FROM gickp_Competitions_Equipes_Joueurs a 
    LEFT OUTER JOIN gickp_Liste_Coureur b 
        ON (a.Matric = b.Matric) 
    LEFT OUTER JOIN gickp_Competitions_Equipes ce
        ON (a.Id_equipe = ce.Id)
    LEFT OUTER JOIN gickp_Club cl
        ON (ce.Code_club = cl.Code)
    LEFT OUTER JOIN gickp_Comite_dep cd
        ON (cl.Code_comite_dep = cd.Code)
    WHERE b.Matric IS NULL
    GROUP BY a.Matric ;

DELETE a
FROM gickp_Competitions_Equipes_Joueurs a
LEFT OUTER JOIN gickp_Competitions_Equipes b 
	ON (a.Id_equipe = b.Id)
WHERE b.Id IS NULL ;

ALTER TABLE gickp_Competitions_Equipes_Joueurs
    ADD CONSTRAINT fk_equipes_joueurs 
    FOREIGN KEY (Id_equipe)
    REFERENCES gickp_Competitions_Equipes(Id) ;

ALTER TABLE `gickp_Competitions_Equipes_Joueurs` CHANGE `Matric` `Matric` 
    INT(11) UNSIGNED NOT NULL DEFAULT '0'; 

ALTER TABLE gickp_Competitions_Equipes_Joueurs
    ADD CONSTRAINT fk_equipes_joueurs_matric
    FOREIGN KEY (Matric)
    REFERENCES gickp_Liste_Coureur(Matric) ;

-- gickp_Competitions_Equipes_Journee --
DELETE a
FROM gickp_Competitions_Equipes_Journee a
LEFT OUTER JOIN gickp_Journees b 
	ON (a.Id_journee = b.Id)
WHERE b.Id IS NULL ;

ALTER TABLE `gickp_Competitions_Equipes_Journee` CHANGE `Id_journee` `Id_journee` 
    INT(11) NOT NULL DEFAULT '0'; 

ALTER TABLE gickp_Competitions_Equipes_Journee
    ADD CONSTRAINT fk_equipes_journee
    FOREIGN KEY (Id_journee)
    REFERENCES gickp_Journees(Id) ;
    
DELETE a
FROM gickp_Competitions_Equipes_Journee a
LEFT OUTER JOIN gickp_Competitions_Equipes b 
	ON (a.Id = b.Id)
WHERE b.Id IS NULL ;

ALTER TABLE `gickp_Competitions_Equipes_Journee` CHANGE `Id` `Id` 
    INT(10) UNSIGNED NOT NULL; 

ALTER TABLE gickp_Competitions_Equipes_Journee
    ADD CONSTRAINT fk_equipes
    FOREIGN KEY (Id)
    REFERENCES gickp_Competitions_Equipes(Id) ;

-- gickp_Competitions_Equipes_Niveau --
DELETE a
FROM gickp_Competitions_Equipes_Niveau a
LEFT OUTER JOIN gickp_Competitions_Equipes b 
	ON (a.Id = b.Id)
WHERE b.Id IS NULL ;

ALTER TABLE `gickp_Competitions_Equipes_Niveau` CHANGE `Id` `Id` 
    INT(10) UNSIGNED NOT NULL; 

ALTER TABLE gickp_Competitions_Equipes_Niveau
    ADD CONSTRAINT fk_equipes_niveau
    FOREIGN KEY (Id)
    REFERENCES gickp_Competitions_Equipes(Id) ;

-- gickp_Equipe --
ALTER TABLE gickp_Equipe
    ADD CONSTRAINT fk_equipe_club
    FOREIGN KEY (Code_club)
    REFERENCES gickp_Club(Code) ;

-- gickp_Evenement_Journees --
DELETE a
FROM gickp_Evenement_Journees a
LEFT OUTER JOIN gickp_Journees b 
	ON (a.Id_journee = b.Id)
WHERE b.Id IS NULL ;

DELETE a
FROM gickp_Evenement_Journees a
LEFT OUTER JOIN gickp_Evenement b 
	ON (a.Id_evenement = b.Id)
WHERE b.Id IS NULL ;

ALTER TABLE `gickp_Evenement_Journees` CHANGE `Id_evenement` `Id_evenement` 
    INT(11) NOT NULL; 

ALTER TABLE gickp_Evenement_Journees
    ADD CONSTRAINT fk_evenements_evenement
    FOREIGN KEY (Id_evenement)
    REFERENCES gickp_Evenement(Id) ;

ALTER TABLE gickp_Evenement_Journees
    ADD CONSTRAINT fk_evenements_journee
    FOREIGN KEY (Id_journee)
    REFERENCES gickp_Journees(Id) ;

-- gickp_Journees --
DELETE a 
FROM gickp_Journees a
LEFT OUTER JOIN gickp_Competitions b 
	ON (a.Code_competition = b.Code AND a.Code_saison = b.Code_saison)
WHERE b.Code IS NULL;

ALTER TABLE `gickp_Journees` CHANGE `Code_competition` `Code_competition` 
    VARCHAR(12) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''; 

ALTER TABLE `gickp_Journees` CHANGE `Code_saison` `Code_saison` 
    CHAR(4) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''; 

ALTER TABLE gickp_Journees
    ADD CONSTRAINT fk_journees_competitions 
    FOREIGN KEY (Code_competition, Code_saison) 
    REFERENCES gickp_Competitions(Code, Code_saison);

-- gickp_Matchs --
DELETE a 
FROM gickp_Matchs a
LEFT OUTER JOIN gickp_Journees b 
	ON (a.Id_journee = b.Id)
WHERE b.Id IS NULL;

ALTER TABLE `gickp_Matchs` CHANGE `Id_journee` `Id_journee` 
    INT(11) NOT NULL DEFAULT '0'; 

ALTER TABLE gickp_Matchs
    ADD CONSTRAINT fk_matchs_journee
    FOREIGN KEY (Id_journee)
    REFERENCES gickp_Journees(Id) ;

-- gickp_Matchs_Detail --
DELETE a
FROM gickp_Matchs_Detail a
LEFT OUTER JOIN gickp_Matchs b 
	ON (a.Id_match = b.Id)
WHERE b.Id IS NULL;

ALTER TABLE `gickp_Matchs_Detail` CHANGE `Id_match` `Id_match` 
    INT(10) UNSIGNED NOT NULL; 

ALTER TABLE gickp_Matchs_Detail
    ADD CONSTRAINT fk_matchs_detail
    FOREIGN KEY (Id_match)
    REFERENCES gickp_Matchs(Id) ;

DELETE FROM `gickp_Matchs_Detail`  
    WHERE Id_evt_match = '';

-- gickp_Matchs_Joueurs --
DELETE a
FROM gickp_Matchs_Joueurs a
LEFT OUTER JOIN gickp_Matchs b 
	ON (a.Id_match = b.Id)
WHERE b.Id IS NULL;

ALTER TABLE `gickp_Matchs_Joueurs` CHANGE `Id_match` `Id_match` 
    INT(10) UNSIGNED NOT NULL; 

ALTER TABLE gickp_Matchs_Joueurs
    ADD CONSTRAINT fk_matchs_joueur
    FOREIGN KEY (Id_match)
    REFERENCES gickp_Matchs(Id) ;

DELETE a
FROM gickp_Matchs_Joueurs a
LEFT OUTER JOIN gickp_Liste_Coureur b 
	ON (a.Matric = b.Matric)
WHERE b.Matric IS NULL;

ALTER TABLE `gickp_Matchs_Joueurs` CHANGE `Matric` `Matric` 
    INT(11) UNSIGNED NOT NULL DEFAULT '0'; 

ALTER TABLE gickp_Matchs_Joueurs
    ADD CONSTRAINT fk_matchs_matric
    FOREIGN KEY (Matric)
    REFERENCES gickp_Liste_Coureur(Matric) ;  

-- gickp_Rc --
ALTER TABLE `gickp_Rc` CHANGE `Matric` `Matric` 
    INT(11) UNSIGNED NOT NULL DEFAULT '0'; 

ALTER TABLE gickp_Rc
    ADD CONSTRAINT fk_rc_matric
    FOREIGN KEY (Matric)
    REFERENCES gickp_Liste_Coureur(Matric) ;  

-- gickp_Recherche_Licence --
ALTER TABLE `gickp_Recherche_Licence` CHANGE `Matric` `Matric` 
    INT(11) UNSIGNED NOT NULL DEFAULT '0'; 

ALTER TABLE gickp_Recherche_Licence
    ADD CONSTRAINT fk_recherche_matric
    FOREIGN KEY (Matric)
    REFERENCES gickp_Liste_Coureur(Matric) ;  

-- gickp_Equipe.Numero --
ALTER TABLE `gickp_Equipe` CHANGE `Numero` `Numero` 
    SMALLINT(6) NULL DEFAULT NULL AUTO_INCREMENT; 
ALTER TABLE `gickp_Competitions_Equipes` CHANGE `Numero` `Numero` 
    SMALLINT(6) NULL DEFAULT NULL; 

UPDATE `gickp_Competitions_Equipes` 
    SET `Numero` = NULL 
    WHERE `gickp_Competitions_Equipes`.`Id` = 1; 

ALTER TABLE gickp_Competitions_Equipes
    ADD CONSTRAINT fk_equipes_numero
    FOREIGN KEY (Numero)
    REFERENCES gickp_Equipe(Numero);


-- gickp_Matchs.Id_equipeA && gickp_Matchs.Id_equipeB --
UPDATE `gickp_Matchs`
SET Id_equipeA = NULL
WHERE Id_equipeA = -1
OR Id_equipeA = 0 ;

UPDATE `gickp_Matchs`
SET Id_equipeB = NULL
WHERE Id_equipeB = -1
OR Id_equipeB = 0 ;

DELETE d
FROM gickp_Matchs a
LEFT OUTER JOIN gickp_Competitions_Equipes b 
	ON (a.Id_equipeA = b.Id)
LEFT OUTER JOIN gickp_Journees c 
	ON (a.Id_journee = c.Id)
LEFT JOIN gickp_Matchs_Detail d
    ON (a.Id = d.Id_match)
WHERE b.Id IS NULL AND a.Id_equipeA IS NOT NULL ;

DELETE d
FROM gickp_Matchs a
LEFT OUTER JOIN gickp_Competitions_Equipes b 
	ON (a.Id_equipeA = b.Id)
LEFT OUTER JOIN gickp_Journees c 
	ON (a.Id_journee = c.Id)
LEFT OUTER JOIN gickp_Matchs_Joueurs d
	ON (a.Id = d.Id_match)
WHERE b.Id IS NULL 
AND a.Id_equipeA IS NOT NULL
AND d.Id_match IS NOT NULL ;

DELETE d
FROM gickp_Matchs a
LEFT OUTER JOIN gickp_Competitions_Equipes b 
	ON (a.Id_equipeB = b.Id)
LEFT OUTER JOIN gickp_Journees c 
	ON (a.Id_journee = c.Id)
LEFT OUTER JOIN gickp_Matchs_Joueurs d
	ON (a.Id = d.Id_match)
WHERE b.Id IS NULL 
AND a.Id_equipeB IS NOT NULL
AND d.Id_match IS NOT NULL ;

DELETE a
FROM gickp_Matchs a
LEFT OUTER JOIN gickp_Competitions_Equipes b 
	ON (a.Id_equipeA = b.Id)
LEFT OUTER JOIN gickp_Journees c 
	ON (a.Id_journee = c.Id)
WHERE b.Id IS NULL AND a.Id_equipeA IS NOT NULL ;

DELETE a
FROM gickp_Matchs a
LEFT OUTER JOIN gickp_Competitions_Equipes b 
	ON (a.Id_equipeB = b.Id)
LEFT OUTER JOIN gickp_Journees c 
	ON (a.Id_journee = c.Id)
WHERE b.Id IS NULL AND a.Id_equipeB IS NOT NULL ;

ALTER TABLE `gickp_Matchs` CHANGE `Id_equipeA` `Id_equipeA` 
    INT(10) UNSIGNED NULL DEFAULT NULL; 
ALTER TABLE `gickp_Matchs` CHANGE `Id_equipeB` `Id_equipeB` 
    INT(10) UNSIGNED NULL DEFAULT NULL; 

ALTER TABLE gickp_Matchs
    ADD CONSTRAINT fk_matchs_equipe_a
    FOREIGN KEY (Id_equipeA)
    REFERENCES gickp_Competitions_Equipes(Id);

ALTER TABLE gickp_Matchs
    ADD CONSTRAINT fk_matchs_equipe_b
    FOREIGN KEY (Id_equipeB)
    REFERENCES gickp_Competitions_Equipes(Id);



-- gickp_Matchs_Detail.Competiteur --
UPDATE `gickp_Matchs_Detail`
SET Competiteur = NULL
WHERE Competiteur = 0 ;

DELETE a
FROM gickp_Matchs_Detail a
LEFT OUTER JOIN gickp_Liste_Coureur b 
	ON (a.Competiteur = b.Matric)
WHERE b.Matric IS NULL
AND a.Competiteur IS NOT NULL;

ALTER TABLE `gickp_Matchs_Detail` CHANGE `Competiteur` `Competiteur` 
    INT(11) UNSIGNED NULL DEFAULT NULL; 

ALTER TABLE gickp_Matchs_Detail
    ADD CONSTRAINT fk_matchs_details_competiteur
    FOREIGN KEY (Competiteur)
    REFERENCES gickp_Liste_Coureur(Matric);

UPDATE `gickp_Matchs_Detail` 
SET Numero = '' 
WHERE Numero = 'undefi' ;

-- TODO : try...catch sur les suppressions
-- TODO : gickp_Matchs_Detail.Numero : default=NULL (remplacer 'undefi' et '')
-- Puis foreign key sur Liste_coureur
-- TODO : fk gickp_Matchs.Matric_arbitre_principal <=> gickp_Liste_coureur
-- TODO : fk gickp_Matchs.Matric_arbitre_secondaire <=> gickp_Liste_coureur

-- gickp_Chrono --
ALTER TABLE `gickp_Chrono` CHANGE `IdMatch` `IdMatch` 
    INT(10) UNSIGNED NOT NULL; 

DELETE a
FROM gickp_Chrono a
LEFT OUTER JOIN gickp_Matchs b 
	ON (a.IdMatch = b.Id)
WHERE b.Id IS NULL;

ALTER TABLE gickp_Chrono
    ADD CONSTRAINT fk_chrono_match
    FOREIGN KEY (IdMatch)
    REFERENCES gickp_Matchs(Id);

