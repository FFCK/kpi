/**
 * Author:  laurent
 * Created: 8 avr. 2019
 *
 * Etat des cartons des joueurs engagés sur une journée de championnat
 *
 */

-- Joueurs engagés sur une journée
SELECT DISTINCT ce.Libelle, cej.Capitaine Statut, cej.Matric, cej.Numero, cej.Nom, cej.Prenom
FROM gickp_Competitions_Equipes_Joueurs cej
JOIN gickp_Competitions_Equipes ce ON (ce.Id = cej.Id_equipe)
JOIN gickp_Matchs m ON (ce.Id = m.Id_equipeA OR ce.Id = m.Id_equipeB)
WHERE m.Id_journee = 4852
ORDER BY ce.Libelle, FIELD(cej.Capitaine, 'E', 'A', 'X'), cej.Numero, cej.Nom, cej.Prenom;

-- Cartons sur une journée
SELECT ce.Libelle, cej.Capitaine Statut, cej.Matric, cej.Numero, cej.Nom, cej.Prenom,
    SUM(IF(md.Id_evt_match='V', 1, 0)) Vert, 
    SUM(IF(md.Id_evt_match='J', 1, 0)) Jaune, 
    SUM(IF(md.Id_evt_match='R', 1, 0)) Rouge, 
    SUM(IF(md.Id_evt_match='D', 1, 0)) Rouge_definitif 
FROM gickp_Competitions_Equipes_Joueurs cej
JOIN gickp_Competitions_Equipes ce ON (ce.Id = cej.Id_equipe)
JOIN gickp_Matchs m ON (ce.Id = m.Id_equipeA OR ce.Id = m.Id_equipeB)
LEFT OUTER JOIN gickp_Matchs_Detail md ON (m.Id = md.Id_match AND cej.Matric = md.Competiteur)
WHERE m.Id_journee = 4852
GROUP BY ce.Libelle, cej.Matric
ORDER BY ce.Libelle, FIELD(cej.Capitaine, 'E', 'A', 'X'), cej.Numero, cej.Nom, cej.Prenom;

-- Cartons depuis le début de saison
SELECT ce.Libelle, cej.Capitaine Statut, cej.Matric, cej.Numero, cej.Nom, cej.Prenom,
    SUM(IF(md.Id_evt_match='V', 1, 0)) Vert, 
    SUM(IF(md.Id_evt_match='J', 1, 0)) Jaune, 
    SUM(IF(md.Id_evt_match='R', 1, 0)) Rouge, 
    SUM(IF(md.Id_evt_match='D', 1, 0)) Rouge_definitif 
FROM gickp_Competitions_Equipes_Joueurs cej
JOIN gickp_Competitions_Equipes ce ON (ce.Id = cej.Id_equipe)
JOIN gickp_Matchs m ON (ce.Id = m.Id_equipeA OR ce.Id = m.Id_equipeB)
JOIN gickp_Journees j ON (m.Id_journee = j.Id)
LEFT OUTER JOIN gickp_Matchs_Detail md ON (m.Id = md.Id_match AND cej.Matric = md.Competiteur)
WHERE j.Code_saison = 2019
AND j.Code_competition = 'N1H'
GROUP BY ce.Libelle, cej.Matric
ORDER BY ce.Libelle, FIELD(cej.Capitaine, 'E', 'A', 'X'), cej.Numero, cej.Nom, cej.Prenom;