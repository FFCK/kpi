--
-- Structure de la table `gickp_Rc`
--
CREATE TABLE `gickp_Rc` (
  `Id` int(11) NOT NULL,
  `Code_competition` varchar(10) DEFAULT NULL,
  `Code_saison` varchar(8) NOT NULL,
  `Ordre` int(11) NOT NULL,
  `Matric` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Index pour la table `gickp_Rc`
--
ALTER TABLE `gickp_Rc`
  ADD PRIMARY KEY (`Id`);

--
-- AUTO_INCREMENT pour la table `gickp_Rc`
--
ALTER TABLE `gickp_Rc`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;