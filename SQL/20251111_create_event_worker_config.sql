-- Table de configuration pour le worker d'événements
-- Permet de gérer la génération automatique des fichiers cache pour les événements
-- sans dépendre du navigateur

CREATE TABLE IF NOT EXISTS `kp_event_worker_config` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_event` INT(11) NOT NULL,
  `date_event` DATE NOT NULL,
  `hour_event` TIME NOT NULL,
  `hour_event_initial` TIME NOT NULL COMMENT 'Heure de départ initiale (référence)',
  `offset_event` INT(11) NOT NULL DEFAULT 15 COMMENT 'Warm-up en minutes',
  `pitch_event` INT(11) NOT NULL DEFAULT 4 COMMENT 'Nombre de terrains',
  `delay_event` INT(11) NOT NULL DEFAULT 10 COMMENT 'Délai de rafraîchissement en secondes',
  `status` ENUM('stopped', 'running', 'paused') NOT NULL DEFAULT 'stopped',
  `last_execution` DATETIME NULL DEFAULT NULL COMMENT 'Timestamp de la dernière exécution',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `error_message` TEXT NULL DEFAULT NULL COMMENT 'Dernier message d\'erreur',
  `execution_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'Nombre d\'exécutions effectuées',
  PRIMARY KEY (`id`),
  KEY `idx_id_event` (`id_event`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Configuration du worker pour génération automatique des caches d\'événements';

-- Index pour optimiser les requêtes
CREATE INDEX `idx_date_event` ON `kp_event_worker_config` (`date_event`);
CREATE INDEX `idx_last_execution` ON `kp_event_worker_config` (`last_execution`);
