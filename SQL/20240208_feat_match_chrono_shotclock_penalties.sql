ALTER TABLE `kp_chrono` 
	ADD `shotclock` TEXT NULL DEFAULT NULL AFTER `max_time`;

ALTER TABLE `kp_chrono` 
	ADD `penalties` TEXT NULL DEFAULT NULL AFTER `shotclock`;