/**
 * Liste compétiteurs FFCK engagés dans des compétitions nationales
 * sur une saison
 *
 * Author:  Laurent
 * Created: 30 oct. 2016
 * Updated: 25 jan. 2021
 */

SELECT j.Code_competition, l.Matric, l.Sexe, 
      l.Naissance, l.Numero_club,
      l.Numero_comite_dept, l.Numero_comite_reg, 
      mj.Capitaine 
FROM kp_journee j, kp_match m, kp_match_joueur mj, kp_licence l
WHERE (
    j.Code_competition LIKE 'N%' 
       OR j.Code_competition LIKE 'CF%'
--       OR gickp_Journees.Code_competition LIKE 'OPEN%'
--       OR gickp_Journees.Code_competition LIKE 'REG%'
--       OR gickp_Journees.Code_competition LIKE 'T-R%'
      )
AND l.Matric < 2000000
-- AND gickp_Liste_Coureur.Sexe = "F"
AND j.Code_saison = 2021
AND m.Id_journee = j.Id
AND mj.Id_match = m.Id
AND l.Matric = mj.Matric
GROUP BY l.Matric;

/**
 * Nombre compétiteurs FFCK engagés dans des compétitions nationales
 * par club, sur une saison
 *
 * Author:  Laurent
 * Created: 8 jan. 2020
 * Updated: 25 jan. 2021
 */
SELECT LEFT(gickp_Liste_Coureur.Numero_comite_dept, 2) CD, gickp_Liste_Coureur.Numero_club Club, gickp_Club.Libelle, 
COUNT(DISTINCT gickp_Liste_Coureur.Matric) Nb_joueurs
FROM gickp_Journees, gickp_Matchs, gickp_Matchs_Joueurs, gickp_Liste_Coureur, gickp_Club
WHERE (
    gickp_Journees.Code_competition LIKE 'N%' 
       OR gickp_Journees.Code_competition LIKE 'CF%'
       OR gickp_Journees.Code_competition LIKE 'OPEN%'
       OR gickp_Journees.Code_competition LIKE 'REG%'
       OR gickp_Journees.Code_competition LIKE 'T-R%'
      )
AND gickp_Liste_Coureur.Matric < 2000000
AND gickp_Liste_Coureur.Numero_club = gickp_Club.Code
AND gickp_Journees.Code_saison = 2020
AND gickp_Matchs.Id_journee = gickp_Journees.Id
AND gickp_Matchs_Joueurs.Id_match = gickp_Matchs.Id
AND gickp_Liste_Coureur.Matric = gickp_Matchs_Joueurs.Matric
-- AND gickp_Liste_Coureur.Sexe = "F"
GROUP BY gickp_Liste_Coureur.Numero_comite_dept, gickp_Liste_Coureur.Numero_club
ORDER BY gickp_Liste_Coureur.Numero_comite_dept, gickp_Liste_Coureur.Numero_club

/**
 * Nombre compétiteurs FFCK engagés dans des compétitions nationales
 * par département, sur une saison
 *
 * Author:  Laurent
 * Created: 16 feb. 2020
 * Updated: 25 jan. 2021
 */
SELECT LEFT(gickp_Liste_Coureur.Numero_comite_dept, 2) CD, 
COUNT(DISTINCT gickp_Liste_Coureur.Matric) Nb_joueurs
FROM gickp_Journees, gickp_Matchs, gickp_Matchs_Joueurs, gickp_Liste_Coureur, gickp_Club
WHERE (
    gickp_Journees.Code_competition LIKE 'N%' 
       OR gickp_Journees.Code_competition LIKE 'CF%'
       OR gickp_Journees.Code_competition LIKE 'OPEN%'
       OR gickp_Journees.Code_competition LIKE 'REG%'
       OR gickp_Journees.Code_competition LIKE 'T-R%'
      )
AND gickp_Liste_Coureur.Matric < 2000000
AND gickp_Liste_Coureur.Numero_club = gickp_Club.Code
AND gickp_Journees.Code_saison = 2020
AND gickp_Matchs.Id_journee = gickp_Journees.Id
AND gickp_Matchs_Joueurs.Id_match = gickp_Matchs.Id
AND gickp_Liste_Coureur.Matric = gickp_Matchs_Joueurs.Matric
-- AND gickp_Liste_Coureur.Sexe = "F"
GROUP BY gickp_Liste_Coureur.Numero_comite_dept
ORDER BY gickp_Liste_Coureur.Numero_comite_dept