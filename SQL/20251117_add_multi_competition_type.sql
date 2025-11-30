-- Migration SQL pour ajouter le support du type de compétition MULTI
-- Date: 2025-11-23 (Updated)
-- Description: Ajoute le support des compétitions multi-compétition avec grille de points personnalisable
--              et sélection explicite des compétitions sources

-- Ajouter le champ points_grid à la table kp_competition
-- Ce champ stockera la grille de points au format JSON
-- Exemple: {"1":10,"2":6,"3":4,"4":3,"5":2,"6":1,"default":0}
ALTER TABLE kp_competition
ADD COLUMN IF NOT EXISTS points_grid TEXT DEFAULT NULL
COMMENT 'Grille de points pour les compétitions MULTI (format JSON)';

-- Ajouter le champ multi_competitions à la table kp_competition
-- Ce champ stockera la liste des codes de compétitions sources au format JSON
-- Exemple: ["REG1","REG2","REG3"]
ALTER TABLE kp_competition
ADD COLUMN IF NOT EXISTS multi_competitions TEXT DEFAULT NULL
COMMENT 'Liste des codes de compétitions sources pour MULTI (format JSON array)';

-- Note: Le champ Code_typeclt (varchar(8)) peut déjà accepter la valeur 'MULTI'
-- Aucune modification structurelle n'est nécessaire pour ce champ

-- Exemple d'insertion d'une compétition MULTI avec grille de points et compétitions sources
-- INSERT INTO kp_competition (Code, Code_saison, Code_typeclt, Libelle, Code_ref, points_grid, multi_competitions, ...)
-- VALUES ('MULTI1', '2025', 'MULTI', 'Championnat Multi-Compétition', 'M',
--         '{"1":10,"2":6,"3":4,"4":3,"5":2,"6":1,"default":0}',
--         '["REG1","REG2","REG3"]', ...);

-- Documentation:
-- Les compétitions de type MULTI:
-- 1. N'ont pas de matchs, seulement un classement
-- 2. Le classement est calculé en fonction des résultats d'autres compétitions sélectionnées explicitement
-- 3. Les compétitions sources sont définies dans le champ multi_competitions (format JSON array)
-- 4. Pour chaque équipe, les points sont attribués selon son classement dans chaque compétition source
-- 5. Le classement final est la somme de tous les points obtenus
-- 6. Les classements publiés sont utilisés : Clt_publi (CHPT) et CltNiveau_publi (CP)
