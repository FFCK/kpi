/**
 * Author:  laurent
 * Created: 24 avr. 2018
 */

ALTER TABLE `gickp_Matchs_Detail` CHANGE `Id_evt_match` `Id_evt_match` VARCHAR(2) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE `gickp_Matchs_Detail` ADD `motif` VARCHAR(10) NULL DEFAULT NULL AFTER `Id_evt_match`;