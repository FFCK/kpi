-- Migration: Add comment field to scrutineering table
-- Date: 2025-10-03
-- Description: Adds a comment field (VARCHAR 255) to the kp_scrutineering table

ALTER TABLE `kp_scrutineering`
ADD COLUMN `comment` VARCHAR(255) NULL DEFAULT NULL AFTER `paddle_print`;
