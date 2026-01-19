-- Add English translation column to kp_groupe table
-- This allows group names to be displayed in English in app2

ALTER TABLE kp_groupe
ADD COLUMN Libelle_en VARCHAR(255) NULL AFTER Libelle;

-- Example usage: populate translations for international competitions
-- UPDATE kp_groupe SET Libelle_en = 'World Championships' WHERE Groupe = 'WC';
-- UPDATE kp_groupe SET Libelle_en = 'European Championships' WHERE Groupe = 'EC';
