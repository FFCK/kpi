-- Migration SQL pour ajouter le support du type de compétition MULTI
-- Date: 2025-11-17
-- Description: Ajoute le support des compétitions multi-compétition avec grille de points personnalisable

-- Ajouter le champ points_grid à la table kp_competition
-- Ce champ stockera la grille de points au format JSON
-- Exemple: {"1":10,"2":6,"3":4,"4":3,"5":2,"6":1,"default":0}
ALTER TABLE kp_competition
ADD COLUMN points_grid TEXT DEFAULT NULL
COMMENT 'Grille de points pour les compétitions MULTI (format JSON)';

-- Note: Le champ Code_typeclt (varchar(8)) peut déjà accepter la valeur 'MULTI'
-- Aucune modification structurelle n'est nécessaire pour ce champ

-- Exemple d'insertion d'une compétition MULTI avec grille de points
-- INSERT INTO kp_competition (Code, Code_saison, Code_typeclt, Libelle, Code_ref, points_grid, ...)
-- VALUES ('MULTI1', '2025', 'MULTI', 'Championnat Multi-Compétition', 'M',
--         '{"1":10,"2":6,"3":4,"4":3,"5":2,"6":1,"default":0}', ...);

-- Documentation:
-- Les compétitions de type MULTI:
-- 1. N'ont pas de matchs, seulement un classement
-- 2. Le classement est calculé en fonction des résultats des autres compétitions du même groupe
-- 3. Les compétitions avec Code_tour = 10 (Unique/Finale) sont exclues du calcul
-- 4. Pour chaque équipe, les points sont attribués selon son classement dans chaque compétition précédente
-- 5. Le classement final est la somme de tous les points obtenus
