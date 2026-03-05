-- Create kp_tv_label table for TV channel and scenario labels
-- Related to: TV Control Panel migration (app4)

CREATE TABLE IF NOT EXISTS `kp_tv_label` (
  `id`     int(11)                    NOT NULL AUTO_INCREMENT,
  `type`   enum('channel','scenario') NOT NULL,
  `number` int(11)                    NOT NULL,
  `label`  varchar(100)               NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `type_number` (`type`, `number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
