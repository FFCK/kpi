-- Trop long
-- SELECT * FROM `gickp_Liste_Coureur` lc
-- LEFT OUTER JOIN gickp_Surclassements s ON (lc.Matric = s.Matric)
-- LEFT OUTER JOIN gickp_Rc rc ON (lc.Matric = rc.Matric)
-- LEFT OUTER JOIN gickp_Matchs_Joueurs mj ON (lc.Matric = mj.Matric)
-- LEFT OUTER JOIN gickp_Matchs_Detail md ON (lc.Matric = md.Competiteur)
-- LEFT OUTER JOIN gickp_Competitions_Equipes_Joueurs cej ON (lc.Matric = cej.Matric)
-- LEFT OUTER JOIN gickp_Arbitre a ON (lc.Matric = a.Matric)
-- LEFT OUTER JOIN gickp_Utilisateur u ON (lc.Matric = u.Code)
-- WHERE s.Matric IS NULL
-- AND rc.Matric IS NULL
-- AND mj.Matric IS NULL
-- AND md.Competiteur IS NULL
-- AND cej.Matric IS NULL
-- AND a.Matric IS NULL
-- AND u.Code IS NULL
-- AND lc.Origine = 2007


-- Mieux (en plusieurs fois)
DELETE lc 
FROM gickp_Liste_Coureur_reduite lc
LEFT OUTER JOIN gickp_Arbitres a 
	ON (lc.Matric = a.Matric)
WHERE a.Matric IS NOT NULL
-- (...)

DELETE lc 
FROM gickp_Liste_Coureur lc
LEFT OUTER JOIN gickp_Liste_Coureur_reduite r 
	ON (lc.Matric = r.Matric)
WHERE r.Matric IS NOT NULL