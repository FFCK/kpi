--
-- Structure de la table `kp_stats`
--

CREATE TABLE `kp_stats` (
  `id` int(11) NOT NULL,
  `date_stat` datetime NOT NULL DEFAULT current_timestamp(),
  `user` varchar(8) CHARACTER SET utf8 NOT NULL,
  `game` int(10) UNSIGNED NOT NULL,
  `team` int(10) UNSIGNED NOT NULL,
  `player` int(11) UNSIGNED NOT NULL,
  `action` varchar(30) CHARACTER SET utf8 NOT NULL,
  `period` varchar(5) CHARACTER SET utf8 NOT NULL,
  `timer` varchar(5) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `kp_stats` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT;

--
-- Index pour la table `kp_stats`
--
ALTER TABLE `kp_stats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `date_stat_index` (`date_stat`),
  ADD KEY `user_index` (`user`),
  ADD KEY `game_index` (`game`),
  ADD KEY `team_index` (`team`),
  ADD KEY `player_index` (`player`),
  ADD KEY `action_index` (`action`),
  ADD KEY `period_index` (`period`),
  ADD KEY `timer_index` (`timer`);

--