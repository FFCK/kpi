-- Migration SQL pour ajouter le support des différents types de classement pour les compétitions MULTI
-- Date: 2025-12-04
-- Description: Ajoute le champ ranking_structure_type pour permettre les classements par équipe, club, CD, CR ou nation

-- Ajouter le champ ranking_structure_type à la table kp_competition
-- Ce champ détermine le type de structure utilisé pour le classement des compétitions MULTI
-- Valeurs possibles: 'team' (équipe), 'club', 'cd' (comité départemental), 'cr' (comité régional), 'nation'
ALTER TABLE kp_competition
ADD COLUMN IF NOT EXISTS ranking_structure_type VARCHAR(10) DEFAULT 'team'
COMMENT 'Type de classement pour MULTI: team, club, cd, cr, nation';

-- Index pour améliorer les performances de recherche
ALTER TABLE kp_competition
ADD INDEX IF NOT EXISTS idx_ranking_structure_type (ranking_structure_type);

-- Documentation:
-- Types de classement pour compétitions MULTI:
--
-- 1. 'team' (par défaut): Classement des équipes individuelles
--    - Recherche par Numero (identifiant unique de l'équipe)
--    - Affiche le nom de l'équipe (Libelle)
--
-- 2. 'club': Classement des clubs
--    - Regroupe toutes les équipes ayant le même code_club
--    - Cumule les points de toutes les équipes du club
--    - Affiche le nom du club (depuis kp_club.Libelle)
--
-- 3. 'cd': Classement des comités départementaux
--    - Regroupe par code_comite_dep (via kp_club.Code_comite_dep)
--    - Cumule les points de toutes les équipes du CD
--    - Affiche le nom du CD (depuis kp_cd.Libelle)
--
-- 4. 'cr': Classement des comités régionaux
--    - Regroupe par code_comite_reg (via kp_club → kp_cd → kp_cr)
--    - Cumule les points de toutes les équipes du CR
--    - Affiche le nom du CR (depuis kp_cr.Libelle)
--
-- 5. 'nation': Classement des nations
--    - Identifie les nations: code_comite_reg = '98' OU équipes nationales (ex: code_comite_dep = 'FRA')
--    - Regroupe par code_comite_dep (= code CIO pour les nations)
--    - Cumule les points de toutes les équipes de la nation
--    - Affiche le nom de la nation (depuis kp_cd.Libelle)
--    - EXCEPTION: Les équipes françaises avec code_comite_reg != '98' sont comptées pour la France
--                 (équipes de club participant à des tournois internationaux)
--
-- Exemple de configuration:
-- UPDATE kp_competition SET ranking_structure_type = 'nation'
-- WHERE Code = 'WORLD_CUP' AND Code_saison = '2025';
