-- Add consolidation column to kp_journee table
-- This column allows phases to be consolidated, preventing recalculation of their rankings
-- NULL = not consolidated (default), 'O' = consolidated

ALTER TABLE `kp_journee` ADD COLUMN `Consolidation` VARCHAR(1) DEFAULT NULL AFTER `Lieu`;

-- Index for faster queries on consolidated phases
CREATE INDEX `idx_consolidation` ON `kp_journee` (`Consolidation`);
