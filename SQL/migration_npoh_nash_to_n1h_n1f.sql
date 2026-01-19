-- ============================================================================
-- Migration : Regroupement des compétitions nationales sous N1H et N1F
-- ============================================================================
--
-- Ce script effectue les modifications suivantes :
--
-- HOMMES (pour chaque saison où NPOH ou NASH existe) :
--   - N1H : Code_tour = 1 (devient la compétition principale du groupe)
--   - NPOH et NASH : Code_ref = 'N1H' (rattachés au groupe N1H)
--
-- FEMMES (pour chaque saison où NPOF ou NASF existe) :
--   - N1F (ou N1D) : Code_tour = 1 (devient la compétition principale du groupe)
--   - NPOF et NASF : Code_ref = 'N1F' (rattachés au groupe N1F)
--
-- ============================================================================

-- Début de la transaction
START TRANSACTION;

-- ============================================================================
-- PARTIE 1 : HOMMES - N1H, NPOH, NASH
-- ============================================================================

-- 1a. Passer N1H à Code_tour = 1 pour chaque saison où NPOH ou NASH existe
UPDATE kp_competition c
SET c.Code_tour = 1
WHERE c.Code = 'N1H'
  AND c.Code_saison IN (
    SELECT DISTINCT Code_saison
    FROM kp_competition
    WHERE Code IN ('NPOH', 'NASH')
  );

-- 1b. Passer NPOH à Code_ref = 'N1H' (pour toutes les saisons où NPOH existe)
UPDATE kp_competition
SET Code_ref = 'N1H'
WHERE Code = 'NPOH';

-- 1c. Passer NASH à Code_ref = 'N1H' (pour toutes les saisons où NASH existe)
UPDATE kp_competition
SET Code_ref = 'N1H'
WHERE Code = 'NASH';

-- ============================================================================
-- PARTIE 2 : FEMMES - N1F (ou N1D), NPOF, NASF
-- ============================================================================

-- 2a. Passer N1F à Code_tour = 1 pour chaque saison où NPOF ou NASF existe
UPDATE kp_competition c
SET c.Code_tour = 1
WHERE c.Code = 'N1F'
  AND c.Code_saison IN (
    SELECT DISTINCT Code_saison
    FROM kp_competition
    WHERE Code IN ('NPOF', 'NASF')
  );

-- 2b. Passer N1D à Code_tour = 1 pour les saisons où NPOF ou NASF existe
--     mais où N1F n'existe pas (fallback)
UPDATE kp_competition c
SET c.Code_tour = 1
WHERE c.Code = 'N1D'
  AND c.Code_saison IN (
    SELECT DISTINCT Code_saison
    FROM kp_competition
    WHERE Code IN ('NPOF', 'NASF')
  )
  AND c.Code_saison NOT IN (
    SELECT DISTINCT Code_saison
    FROM kp_competition
    WHERE Code = 'N1F'
  );

-- 2c. Passer NPOF à Code_ref = 'N1F' (pour toutes les saisons où NPOF existe)
UPDATE kp_competition
SET Code_ref = 'N1F'
WHERE Code = 'NPOF';

-- 2d. Passer NASF à Code_ref = 'N1F' (pour toutes les saisons où NASF existe)
UPDATE kp_competition
SET Code_ref = 'N1F'
WHERE Code = 'NASF';

-- ============================================================================
-- Validation de la transaction
-- ============================================================================

COMMIT;

-- ============================================================================
-- Requête de vérification (à exécuter après la migration)
-- ============================================================================
--
-- SELECT Code_saison, Code, Code_ref, Code_tour
-- FROM kp_competition
-- WHERE Code IN ('N1H', 'NPOH', 'NASH', 'N1F', 'N1D', 'NPOF', 'NASF')
-- ORDER BY Code_saison DESC, Code;
--
-- ============================================================================
