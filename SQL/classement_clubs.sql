/**
 * Author:  laurent
 * Created: 10 déc. 2018
 */
-- 7.1.1 Clubs N1F...
SELECT ce.Code_club, c.Libelle, COUNT(ce.Code_club) AS '7.1.1', ce.Code_compet, ce.libelle
FROM `gickp_Competitions_Equipes` ce, gickp_Club c
WHERE 1=1
AND ce.Code_club = c.Code
AND (ce.Code_compet LIKE 'N1F%'
    OR ce.Code_compet LIKE 'N15%' -- compte plusieurs fois les qualifiés
    OR ce.Code_compet LIKE 'N18%' -- compte plusieurs fois les qualifiés
    OR ce.Code_compet LIKE 'CFF'
    OR ce.Code_compet LIKE 'CF15%') -- compte plusieurs fois les qualifiés
AND ce.Code_saison LIKE '2020'
AND ce.Code_club NOT LIKE '%CD'
AND ce.Code_club NOT LIKE 'CR%'
GROUP BY ce.Code_club, ce.Libelle, ce.Code_compet

-- 7.1.2 Clubs ayant des joueurs en N1F...
SELECT lc.Numero_club Club, c.Libelle, COUNT(lc.Numero_club) AS '7.1.2', ce.Code_compet, ce.libelle
FROM `gickp_Competitions_Equipes` ce, gickp_Competitions_Equipes_Joueurs cej, gickp_Liste_Coureur lc, gickp_Club c
WHERE 1=1
AND ce.Id = cej.Id_equipe
AND cej.Matric = lc.Matric
AND lc.Numero_club = c.Code
AND ce.Code_club != lc.Numero_club
AND (ce.Code_compet LIKE 'N1F%'
    OR ce.Code_compet LIKE 'N15%' -- compte plusieurs fois les qualifiés
    OR ce.Code_compet LIKE 'N18%' -- compte plusieurs fois les qualifiés
    OR ce.Code_compet LIKE 'CFF'
    OR ce.Code_compet LIKE 'CF15%') -- compte plusieurs fois les qualifiés
AND ce.Code_saison LIKE '2020'
GROUP BY lc.Numero_club, ce.Libelle, ce.Code_compet

-- 7.1.3 Clubs N1H...
SELECT ce.Code_club, ce.Libelle, COUNT(ce.Code_club) AS '7.1.3', ce.Code_compet
FROM `gickp_Competitions_Equipes` ce, gickp_Club c
WHERE 1=1
AND ce.Code_club = c.Code
AND (ce.Code_compet LIKE 'N1H%'
    OR ce.Code_compet LIKE 'N2H%'
    OR ce.Code_compet LIKE 'N3%'
    OR ce.Code_compet LIKE 'N4%'
    OR ce.Code_compet LIKE 'CFH%') -- compte plusieurs fois les équipes qualifiés
AND ce.Code_saison LIKE '2020'
AND ce.Code_club NOT LIKE '%CD'
AND ce.Code_club NOT LIKE 'CR%'
GROUP BY ce.Libelle, ce.Code_compet
ORDER BY ce.Code_club, ce.Libelle, ce.Code_compet


-- 7.3.1 - Déldgués CNA & Chefs Arbitres sur les journées de compétition
SELECT lc.Nom, lc.Prenom, lc.Matric, lc.Numero_club 
FROM `gickp_Journees` j
JOIN gickp_Liste_Coureur lc
ON (
    CONCAT(lc.Nom, ' ', lc.Prenom) = UPPER(j.Delegue)
    OR CONCAT(lc.Prenom, ' ', lc.Nom) = UPPER(j.Delegue)
    OR CONCAT(lc.Nom, ' ', lc.Prenom) = UPPER(j.ChefArbitre)
    OR CONCAT(lc.Prenom, ' ', lc.Nom) = UPPER(j.ChefArbitre)
)
WHERE j.Code_saison = 2020
AND (j.Code_competition LIKE 'N%'
     OR j.Code_competition LIKE 'CF%')
GROUP BY lc.Matric
ORDER BY lc.Numero_club, lc.Nom, lc.Prenom


-- Nombre d'arbitres nationaux par club et par niveau
-- SELECT COUNT( arb.Arb ), arb.niveau, l.Numero_club
-- FROM `gickp_Arbitre` arb, gickp_Liste_Coureur l
-- WHERE Arb = "Nat"
-- AND niveau != ""
-- AND arb.Matric = l.Matric
-- GROUP BY l.Numero_club, arb.niveau
-- ORDER BY l.Numero_club, arb.niveau
-- LIMIT 0 , 150

-- 7.3.5 Nombre d'arbitres A par club
SELECT COUNT( arb.Arb ) "Niveau A", l.Numero_club, c.Libelle
FROM `gickp_Arbitre` arb, gickp_Liste_Coureur l, gickp_Club c
WHERE Arb = "Nat"
AND niveau = "A"
AND arb.Matric = l.Matric
AND l.Numero_club = c.Code
GROUP BY l.Numero_club
ORDER BY l.Numero_club
LIMIT 0, 150


-- 7.3.6 Nombre d'arbitres B ou C par club
SELECT COUNT( arb.Arb ) "Niveau B-C", l.Numero_club, c.Libelle
FROM `gickp_Arbitre` arb, gickp_Liste_Coureur l, gickp_Club c
WHERE Arb = "Nat"
AND (niveau = "B" OR niveau = "C")
AND arb.Matric = l.Matric
AND l.Numero_club = c.Code
GROUP BY l.Numero_club
ORDER BY l.Numero_club
LIMIT 0, 150


-- 7.3.7 Nombre d'arbitres NAT S ou REG ou OTM ou JO par club
SELECT COUNT( arb.Arb ) "Niveau NAT S, REG, OTM, JO", l.Numero_club, c.Libelle
FROM `gickp_Arbitre` arb, gickp_Liste_Coureur l, gickp_Club c
WHERE ((Arb = "Nat" AND niveau = "S") 
OR (Arb = "Reg" AND niveau <> "S")
OR Arb = "OTM"
OR Arb = "JO")
AND arb.Matric = l.Matric
AND l.Numero_club = c.Code
GROUP BY l.Numero_club
ORDER BY l.Numero_club
LIMIT 0, 500
