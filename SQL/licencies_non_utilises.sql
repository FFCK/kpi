-- ------------------- --
-- EPURATION LICENCIES --
-- ------------------- --

-- Vidage table recherche
DELETE FROM gickp_Recherche_Licence;

-- Epuration journal
DELETE FROM gickp_Journal
WHERE Dates < '2017-01-01';

-- Création table temporaire
CREATE TABLE gickp_Liste_Coureur_Legacy 
    SELECT * FROM gickp_Liste_Coureur;

-- gickp_Arbitre : 297586 -> 295548
DELETE lc 
FROM gickp_Liste_Coureur_Legacy lc
LEFT OUTER JOIN gickp_Arbitre a 
	ON (lc.Matric = a.Matric)
WHERE a.Matric IS NOT NULL;

-- gickp_Competitions_Equipes_Joueurs : 295548 - 4151 => 291397
DELETE lc 
FROM gickp_Liste_Coureur_Legacy lc
LEFT OUTER JOIN gickp_Competitions_Equipes_Joueurs a 
	ON (lc.Matric = a.Matric)
WHERE a.Matric IS NOT NULL;

-- gickp_Matchs_Detail : 291386 - 128 => 291326
DELETE lc 
FROM gickp_Liste_Coureur_Legacy lc
LEFT OUTER JOIN gickp_Matchs_Detail a 
	ON (lc.Matric = a.Competiteur)
WHERE a.Competiteur IS NOT NULL;

-- gickp_Matchs_Joueurs : 291386 - 128 => 291326
DELETE lc 
FROM gickp_Liste_Coureur_Legacy lc
LEFT OUTER JOIN gickp_Matchs_Joueurs a 
	ON (lc.Matric = a.Matric)
WHERE a.Matric IS NOT NULL;

-- gickp_Rc : 291326
DELETE lc 
FROM gickp_Liste_Coureur_Legacy lc
LEFT OUTER JOIN gickp_Rc a 
	ON (lc.Matric = a.Matric)
WHERE a.Matric IS NOT NULL;

-- gickp_Surclassements : 291326 - 5 => 291321
DELETE lc 
FROM gickp_Liste_Coureur_Legacy lc
LEFT OUTER JOIN gickp_Surclassements a 
	ON (lc.Matric = a.Matric)
WHERE a.Matric IS NOT NULL;

-- gickp_Utilisateur : 291321 - 9 => 291312
DELETE lc 
FROM gickp_Liste_Coureur_Legacy lc
LEFT OUTER JOIN gickp_Utilisateur a 
	ON (lc.Matric = a.Code)
WHERE a.Code IS NOT NULL;

-- gickp_Journal : 291397 - 11 => 291386
ALTER TABLE `gickp_Journal` ADD INDEX(`Users`); 
ALTER TABLE `gickp_Liste_Coureur_Legacy` ADD PRIMARY KEY(`Matric`); 

DELETE lc 
FROM gickp_Liste_Coureur_Legacy lc
LEFT OUTER JOIN gickp_Journal a 
	ON (lc.Matric = a.Users)
WHERE a.Users IS NOT NULL;

-- gickp_Liste_Coureur_Legacy : conservation des 3 dernières saisons
-- -121908 lignes => 169276
DELETE  
FROM gickp_Liste_Coureur_Legacy
WHERE Origine >= '2018';





-- Finalisation --
DELETE lc 
FROM gickp_Liste_Coureur lc
LEFT OUTER JOIN gickp_Liste_Coureur_Legacy r 
	ON (lc.Matric = r.Matric)
WHERE r.Matric IS NOT NULL;

-- Optimisation
OPTIMIZE TABLE `gickp_Liste_Coureur`; 

-- Suppression table temporaire
DROP TABLE gickp_Liste_Coureur_Legacy;