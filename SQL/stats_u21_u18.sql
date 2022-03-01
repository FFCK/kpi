-- Stats U21 (par joueur / par saison / par compétition)
SELECT lc.Naissance, mj.Matric Licence, lc.Sexe, lc.Nom, lc.Prenom, c.Libelle Club,
	j.Code_saison Saison, j.Code_competition Compet, ce.Libelle Equipe, mj.Numero, mj.Capitaine Statut,
    COUNT(DISTINCT(mj.Id_match)) Matchs, 
    SUM(IF(md.Id_evt_match='B', 1, 0)) Buts, 
    SUM(IF(md.Id_evt_match='V', 1, 0)) Vert, 
    SUM(IF(md.Id_evt_match='J', 1, 0)) Jaune, 
    SUM(IF(md.Id_evt_match='R', 1, 0)) Rouge, 
    SUM(IF(md.Id_evt_match='D', 1, 0)) Rouge_definitif 
FROM gickp_Matchs_Joueurs mj
JOIN gickp_Liste_Coureur lc ON (mj.Matric = lc.Matric)
JOIN gickp_Club c ON (lc.Numero_club = c.Code)
JOIN gickp_Matchs m ON (mj.Id_match = m.Id)
JOIN gickp_Competitions_Equipes ce ON ce.Id = (CASE WHEN mj.Equipe = 'A' 
                                   THEN m.Id_equipeA
                                   ELSE m.Id_equipeB
                               END)
JOIN gickp_Journees j ON (m.Id_journee = j.Id)
LEFT OUTER JOIN gickp_Matchs_Detail md ON (m.Id = md.Id_match AND mj.Matric = md.Competiteur)
WHERE j.Code_saison >= 2016
AND j.Code_saison < 2020
-- AND j.Code_competition IN ('N1H','N2H')
AND (j.Code_competition LIKE 'N%' OR j.Code_competition LIKE 'CF%' OR j.Code_competition LIKE 'OPEN%')
AND lc.Naissance > '1999-01-01'
AND mj.Matric < 2000000
GROUP BY j.Code_saison, j.Code_competition, ce.Libelle, mj.Matric  
ORDER BY lc.Naissance DESC, lc.Nom ASC, j.Code_saison ASC, j.Code_competition ASC;


-- Stats U21 (Total par joueur par saison)
SELECT lc.Naissance, mj.Matric Licence, lc.Sexe, lc.Nom, lc.Prenom, c.Libelle Club,
	j.Code_saison Saison, 
    COUNT(DISTINCT(mj.Id_match)) Matchs, 
    SUM(IF(md.Id_evt_match='B', 1, 0)) Buts, 
    SUM(IF(md.Id_evt_match='V', 1, 0)) Vert, 
    SUM(IF(md.Id_evt_match='J', 1, 0)) Jaune, 
    SUM(IF(md.Id_evt_match='R', 1, 0)) Rouge, 
    SUM(IF(md.Id_evt_match='D', 1, 0)) Rouge_definitif 
FROM gickp_Matchs_Joueurs mj
JOIN gickp_Liste_Coureur lc ON (mj.Matric = lc.Matric)
JOIN gickp_Club c ON (lc.Numero_club = c.Code)
JOIN gickp_Matchs m ON (mj.Id_match = m.Id)
JOIN gickp_Competitions_Equipes ce ON ce.Id = (CASE WHEN mj.Equipe = 'A' 
                                   THEN m.Id_equipeA
                                   ELSE m.Id_equipeB
                               END)
JOIN gickp_Journees j ON (m.Id_journee = j.Id)
LEFT OUTER JOIN gickp_Matchs_Detail md ON (m.Id = md.Id_match AND mj.Matric = md.Competiteur)
WHERE j.Code_saison >= 2016
AND j.Code_saison < 2020
-- AND j.Code_competition IN ('N1H','N2H')
AND (j.Code_competition LIKE 'N%' OR j.Code_competition LIKE 'CF%' OR j.Code_competition LIKE 'OPEN%')
AND lc.Naissance > '1999-01-01'
AND mj.Matric < 2000000
GROUP BY j.Code_saison, mj.Matric  
ORDER BY lc.Naissance DESC, lc.Nom ASC, j.Code_saison ASC;
