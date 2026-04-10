-- Migration: Users page - Extend Pwd column + Create kp_user_mandat table
-- Date: 2026-02-20

-- Elargir la colonne Pwd pour supporter bcrypt (60 car.) avec marge
ALTER TABLE kp_user MODIFY COLUMN Pwd varchar(255) NOT NULL DEFAULT '';

-- Table des mandats (profils supplementaires par perimetre)
CREATE TABLE IF NOT EXISTS kp_user_mandat (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_code varchar(8) NOT NULL,
  libelle varchar(100) NOT NULL,
  niveau smallint(6) NOT NULL,
  filtre_saison mediumtext NOT NULL DEFAULT '',
  filtre_competition mediumtext NOT NULL DEFAULT '',
  limitation_equipe_club varchar(50) DEFAULT NULL,
  filtre_journee mediumtext NOT NULL DEFAULT '',
  id_evenement varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  KEY fk_mandat_user (user_code),
  CONSTRAINT fk_mandat_user FOREIGN KEY (user_code) REFERENCES kp_user (Code) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
