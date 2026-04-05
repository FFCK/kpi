-- Add Type_licence column to kp_licence table
-- This field stores the licence type imported from PCE file (token[17])
-- Examples: "Carte 1 an Loisir", "Carte 1 an Compétition"

ALTER TABLE `kp_licence`
  ADD COLUMN `Type_licence` varchar(50) DEFAULT NULL AFTER `Etat_certificat_CK`;
