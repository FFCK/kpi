-- Migration: Allow NULL values for Code_competition in kp_rc
-- Date: 2026-02-09
-- Purpose: Replace legacy "- CNA -" value with NULL for national RC without specific competition

-- Step 1: Make Code_competition nullable
ALTER TABLE kp_rc
  MODIFY COLUMN Code_competition varchar(10) DEFAULT NULL;

-- Step 2: Migrate existing "- CNA -" values to NULL
UPDATE kp_rc
  SET Code_competition = NULL
  WHERE Code_competition = '- CNA -';

-- Step 3: Add comment for documentation
ALTER TABLE kp_rc
  MODIFY COLUMN Code_competition varchar(10) DEFAULT NULL
  COMMENT 'Code de la compétition (NULL = RC national sans compétition spécifique)';

-- Verification: Count affected rows
SELECT
  COUNT(*) as total_rc,
  SUM(CASE WHEN Code_competition IS NULL THEN 1 ELSE 0 END) as national_rc,
  SUM(CASE WHEN Code_competition IS NOT NULL THEN 1 ELSE 0 END) as competition_rc
FROM kp_rc;
