SET FOREIGN_KEY_CHECKS=0;

UPDATE kp_competition
SET Code = 'N18A'
WHERE Code = 'N18Z'
AND Code_saison = 2021;

UPDATE kp_competition_equipe
SET Code_compet = 'N18A'
WHERE Code_compet = 'N18Z'
AND Code_saison = 2021;

UPDATE kp_journee
SET Code_competition = 'N18A'
WHERE Code_competition = 'N18Z'
AND Code_saison = 2021;

SET FOREIGN_KEY_CHECKS=1;