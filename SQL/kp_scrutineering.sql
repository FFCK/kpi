CREATE TABLE `kp_scrutineering` (
  `id_equipe` int(10) UNSIGNED NOT NULL,
  `matric` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `kayak_status` smallint(6) DEFAULT NULL,
  `kayak_print` smallint(6) DEFAULT NULL,
  `vest_status` smallint(6) DEFAULT NULL,
  `vest_print` smallint(6) DEFAULT NULL,
  `helmet_status` smallint(6) DEFAULT NULL,
  `helmet_print` smallint(6) DEFAULT NULL,
  `paddle_count` smallint(6) DEFAULT NULL,
  `paddle_print` smallint(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `kp_scrutineering`
  ADD PRIMARY KEY (`id_equipe`,`matric`);

ALTER TABLE `kp_scrutineering`
  ADD FOREIGN KEY (id_equipe, matric) 
  REFERENCES kp_competition_equipe_joueur(Id_equipe, Matric) 
  ON DELETE CASCADE;