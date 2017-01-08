/* 
 * Compétiteurs engagés dans des compétitions nationales
 * sur une saison
 */
/**
 * Author:  Laurent
 * Created: 30 oct. 2016
 */

SELECT gickp_Journees.Code_competition, gickp_Liste_Coureur.Matric, gickp_Liste_Coureur.Sexe, gickp_Liste_Coureur.Naissance, gickp_Liste_Coureur.Numero_club,gickp_Liste_Coureur.Numero_comite_dept, gickp_Liste_Coureur.Numero_comite_reg, gickp_Matchs_Joueurs.Capitaine 
FROM gickp_Journees, gickp_Matchs, gickp_Matchs_Joueurs, gickp_Liste_Coureur
WHERE (gickp_Journees.Code_competition LIKE 'N%' OR gickp_Journees.Code_competition LIKE 'CF%')
AND gickp_Journees.Code_saison = 2016
AND gickp_Matchs.Id_journee = gickp_Journees.Id
AND gickp_Matchs_Joueurs.Id_match = gickp_Matchs.Id
AND gickp_Liste_Coureur.Matric = gickp_Matchs_Joueurs.Matric
GROUP BY gickp_Journees.Code_competition, gickp_Liste_Coureur.Matric