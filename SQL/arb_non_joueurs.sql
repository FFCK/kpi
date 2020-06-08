ALTER TABLE `gickp_Journees` 
ADD `Rep_athletes` VARCHAR(80) NULL AFTER `ChefArbitre`, 
ADD `Arb_nj1` VARCHAR(80) NULL AFTER `Rep_athletes`, 
ADD `Arb_nj2` VARCHAR(80) NULL AFTER `Arb_nj1`, 
ADD `Arb_nj3` VARCHAR(80) NULL AFTER `Arb_nj2`, 
ADD `Arb_nj4` VARCHAR(80) NULL AFTER `Arb_nj3`, 
ADD `Arb_nj5` VARCHAR(80) NULL AFTER `Arb_nj4`; 