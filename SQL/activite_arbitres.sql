SELECT lc.Matric, lc.Nom, lc.Prenom, lc.Sexe, lc.Naissance, lc.Numero_club Club,
a.Arb, a.niveau, a.saison annee, j.Code_competition Competition, j.Code_saison Saison, m.Id as 'Match', 'Principal' as arbitre
FROM `gickp_Liste_Coureur` lc, `gickp_Journees` j, `gickp_Arbitre` a
LEFT OUTER JOIN `gickp_Matchs` m ON a.Matric = m.Matric_arbitre_principal
WHERE 1=1
AND m.Id_journee = j.Id
AND a.Matric = lc.Matric
AND lc.Numero_comite_reg != 98
AND j.Code_competition NOT LIKE 'M%'
AND a.saison = 2018

UNION

SELECT lc.Matric, lc.Nom, lc.Prenom, lc.Sexe, lc.Naissance, lc.Numero_club Club,
a.Arb, a.niveau, a.saison annee, j.Code_competition Competition, j.Code_saison Saison, m.Id as 'Match', 'Secondaire' as arbitre
FROM `gickp_Liste_Coureur` lc, `gickp_Journees` j, `gickp_Arbitre` a
LEFT OUTER JOIN `gickp_Matchs` m ON a.Matric = m.Matric_arbitre_secondaire
WHERE 1=1
AND m.Id_journee = j.Id
AND a.Matric = lc.Matric
AND lc.Numero_comite_reg != 98
AND j.Code_competition NOT LIKE 'M%'
AND a.saison = 2018

ORDER BY Nom, Prenom, Matric, Saison, Competition


/**
 * Liste arbitres ayant officié sur une compétition nationale 
 * (arbitre principal ou secondaire)
 *
 * Author:  Laurent
 * Created: 25 jan. 2021
 * Updated: 27 jan. 2021
 */
SELECT lc.Numero_club Club, c.Libelle, lc.Matric, lc.Nom, lc.Prenom, lc.Sexe, lc.Naissance, 
	a.Arb, a.niveau, a.saison annee, 
	SUM(IF(a.Matric = m.Matric_arbitre_principal,1,0)) Principal, 
    SUM(IF(a.Matric = m.Matric_arbitre_secondaire,1,0)) Secondaire, 
    COUNT(a.Matric) Total 
FROM `gickp_Liste_Coureur` lc, `gickp_Journees` j, `gickp_Arbitre` a, 
    `gickp_Matchs` m, `gickp_Club` c 
WHERE 1=1
AND m.Id_journee = j.Id
AND a.Matric = lc.Matric
AND lc.Numero_club = c.Code
AND lc.Numero_comite_reg != 98

AND (j.Code_competition LIKE 'N%' 
       OR j.Code_competition LIKE 'CF%')
-- AND j.Code_competition NOT LIKE 'M%'

AND j.Code_saison = 2020
AND (a.Matric = m.Matric_arbitre_principal
	OR a.Matric = m.Matric_arbitre_secondaire)
GROUP BY lc.Matric
ORDER BY Club, Nom, Prenom, Matric

/**
 * Nombre d'arbitres ayant officié sur une compétition nationale 
 * (arbitre principal ou secondaire) 
 *
 * Author:  Laurent
 * Created: 25 jan. 2021
 * Updated: 27 jan. 2021
 */
SELECT lc.Numero_club Club, c.Libelle, 
	SUM(IF(a.Matric = m.Matric_arbitre_principal,1,0)) Principal, 
    SUM(IF(a.Matric = m.Matric_arbitre_secondaire,1,0)) Secondaire, 
    COUNT(lc.Numero_club) Total 
FROM `gickp_Liste_Coureur` lc, `gickp_Journees` j, `gickp_Arbitre` a,
    `gickp_Matchs` m, `gickp_Club` c
WHERE 1=1
AND m.Id_journee = j.Id
AND a.Matric = lc.Matric
AND lc.Numero_club = c.Code
AND lc.Numero_comite_reg != 98

AND (j.Code_competition LIKE 'N%' 
       OR j.Code_competition LIKE 'CF%')
-- AND j.Code_competition NOT LIKE 'M%'

AND j.Code_saison = 2020
AND (a.Matric = m.Matric_arbitre_principal
	OR a.Matric = m.Matric_arbitre_secondaire)
GROUP BY lc.Numero_club
ORDER BY Club