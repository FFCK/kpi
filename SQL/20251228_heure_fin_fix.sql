-- Migration: Fix Heure_fin format in kp_matchs table
-- Converts minutes:seconds (00:MM:SS) to hours:minutes (HH:MM:00)
-- Date: 2025-12-28
-- Author: Claude Code
--
-- Problem: The Heure_fin field was incorrectly storing times as 00:MM:SS
-- where MM represents hours and SS represents minutes, instead of the
-- correct format HH:MM:00 (hours:minutes:00)
--
-- This migration identifies and fixes all affected records

-- Identify problematic records (hours = 00 and minutes/seconds not both 00)
-- These are stored as 00:MM:SS where MM should be hours and SS should be minutes

UPDATE kp_match
SET Heure_fin = CONCAT(
    LPAD(TIME_FORMAT(Heure_fin, '%i'), 2, '0'),  -- Extract minutes as hours
    ':',
    LPAD(TIME_FORMAT(Heure_fin, '%s'), 2, '0'),  -- Extract seconds as minutes
    ':00'                                          -- Set seconds to 00
)
WHERE Heure_fin IS NOT NULL
  AND TIME_FORMAT(Heure_fin, '%H') = '00'         -- Hours are 00 (bug indicator)
  AND NOT (TIME_FORMAT(Heure_fin, '%i') = '00' AND TIME_FORMAT(Heure_fin, '%s') = '00'); -- But not 00:00:00

-- Example transformations:
-- 00:14:32 → 14:32:00 (14h32 was stored as 00:14:32, now corrected)
-- 00:09:45 → 09:45:00 (09h45 was stored as 00:09:45, now corrected)
-- 00:23:15 → 23:15:00 (23h15 was stored as 00:23:15, now corrected)
-- 00:00:00 → unchanged (legitimate empty value)
-- 14:32:00 → unchanged (already correct - not affected by WHERE clause)
