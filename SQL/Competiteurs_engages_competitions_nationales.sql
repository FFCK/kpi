/**
 * Compétiteurs FFCK engagés dans des compétitions nationales
 * sur une saison
 *
 * Author:  Laurent
 * Created: 30 oct. 2016
 * Updated: 13 dec. 2019
 */

SELECT gickp_Journees.Code_competition, gickp_Liste_Coureur.Matric, gickp_Liste_Coureur.Sexe, gickp_Liste_Coureur.Naissance, gickp_Liste_Coureur.Numero_club,gickp_Liste_Coureur.Numero_comite_dept, gickp_Liste_Coureur.Numero_comite_reg, gickp_Matchs_Joueurs.Capitaine 
FROM gickp_Journees, gickp_Matchs, gickp_Matchs_Joueurs, gickp_Liste_Coureur
WHERE (
    gickp_Journees.Code_competition LIKE 'N%' 
       OR gickp_Journees.Code_competition LIKE 'CF%'
--       OR gickp_Journees.Code_competition LIKE 'OPEN%'
--       OR gickp_Journees.Code_competition LIKE 'REG%'
--       OR gickp_Journees.Code_competition LIKE 'T-R%'
      )
AND gickp_Liste_Coureur.Matric < 2000000
-- AND gickp_Liste_Coureur.Sexe = "F"
AND gickp_Journees.Code_saison = 2019
AND gickp_Matchs.Id_journee = gickp_Journees.Id
AND gickp_Matchs_Joueurs.Id_match = gickp_Matchs.Id
AND gickp_Liste_Coureur.Matric = gickp_Matchs_Joueurs.Matric
GROUP BY gickp_Liste_Coureur.Matric

/**
 * Compétiteurs FFCK engagés dans des compétitions nationales
 * par club, sur une saison
 *
 * Author:  Laurent
 * Created: 8 jan. 2020
 * Updated: 16 feb. 2020
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
AND gickp_Journees.Code_saison = 2019
AND gickp_Matchs.Id_journee = gickp_Journees.Id
AND gickp_Matchs_Joueurs.Id_match = gickp_Matchs.Id
AND gickp_Liste_Coureur.Matric = gickp_Matchs_Joueurs.Matric
-- AND gickp_Liste_Coureur.Sexe = "F"
GROUP BY gickp_Liste_Coureur.Numero_comite_dept, gickp_Liste_Coureur.Numero_club
ORDER BY gickp_Liste_Coureur.Numero_comite_dept, gickp_Liste_Coureur.Numero_club

/**
 * Compétiteurs FFCK engagés dans des compétitions nationales
 * par département, sur une saison
 *
 * Author:  Laurent
 * Created: 16 feb. 2020
 * Updated: 16 feb. 2020
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
AND gickp_Journees.Code_saison = 2019
AND gickp_Matchs.Id_journee = gickp_Journees.Id
AND gickp_Matchs_Joueurs.Id_match = gickp_Matchs.Id
AND gickp_Liste_Coureur.Matric = gickp_Matchs_Joueurs.Matric
-- AND gickp_Liste_Coureur.Sexe = "F"
GROUP BY gickp_Liste_Coureur.Numero_comite_dept
ORDER BY gickp_Liste_Coureur.Numero_comite_dept