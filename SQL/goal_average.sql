ALTER TABLE `gickp_Competitions` 
ADD `goalaverage` 
VARCHAR(4) 
CHARACTER SET utf8 
COLLATE utf8_general_ci 
NOT NULL 
DEFAULT 'gen' 
AFTER `Points`;
