-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : db
-- Généré le : sam. 29 nov. 2025 à 16:59
-- Version du serveur : 11.4.9-MariaDB-ubu2404
-- Version de PHP : 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `db`
--

-- --------------------------------------------------------

--
-- Structure de la table `kp_app_rating`
--

CREATE TABLE `kp_app_rating` (
  `id` int(11) NOT NULL,
  `rating_date` datetime NOT NULL DEFAULT current_timestamp(),
  `uid` varchar(36) NOT NULL,
  `stars` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_arbitre`
--

CREATE TABLE `kp_arbitre` (
  `Matric` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `regional` char(1) DEFAULT NULL,
  `interregional` char(1) DEFAULT NULL,
  `national` char(1) DEFAULT NULL,
  `international` char(1) DEFAULT NULL,
  `arbitre` char(3) DEFAULT NULL,
  `livret` varchar(25) NOT NULL DEFAULT '',
  `niveau` char(1) NOT NULL DEFAULT '',
  `saison` varchar(4) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_arbitre_old_2020_05_03`
--

CREATE TABLE `kp_arbitre_old_2020_05_03` (
  `Matric` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `regional` char(1) DEFAULT NULL,
  `interregional` char(1) DEFAULT NULL,
  `national` char(1) DEFAULT NULL,
  `international` char(1) DEFAULT NULL,
  `arbitre` char(3) DEFAULT NULL,
  `livret` varchar(25) NOT NULL DEFAULT '',
  `niveau` char(1) NOT NULL DEFAULT '',
  `saison` varchar(4) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_arbitre_old_2025_01_14`
--

CREATE TABLE `kp_arbitre_old_2025_01_14` (
  `Matric` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `regional` char(1) DEFAULT NULL,
  `interregional` char(1) DEFAULT NULL,
  `national` char(1) DEFAULT NULL,
  `international` char(1) DEFAULT NULL,
  `arbitre` char(3) DEFAULT NULL,
  `livret` varchar(25) NOT NULL DEFAULT '',
  `niveau` char(1) NOT NULL DEFAULT '',
  `saison` varchar(4) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_arbitre_old_2025_02_13`
--

CREATE TABLE `kp_arbitre_old_2025_02_13` (
  `Matric` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `regional` char(1) DEFAULT NULL,
  `interregional` char(1) DEFAULT NULL,
  `national` char(1) DEFAULT NULL,
  `international` char(1) DEFAULT NULL,
  `arbitre` char(3) DEFAULT NULL,
  `livret` varchar(25) NOT NULL DEFAULT '',
  `niveau` char(1) NOT NULL DEFAULT '',
  `saison` varchar(4) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_categorie`
--

CREATE TABLE `kp_categorie` (
  `id` varchar(8) NOT NULL DEFAULT '',
  `libelle` varchar(30) NOT NULL DEFAULT '',
  `age_min` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `age_max` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `sexe` char(1) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_cd`
--

CREATE TABLE `kp_cd` (
  `Code` varchar(6) NOT NULL DEFAULT '',
  `Libelle` varchar(100) NOT NULL DEFAULT '',
  `Officiel` char(1) DEFAULT 'O',
  `Reserve` varchar(20) DEFAULT NULL,
  `Code_comite_reg` varchar(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_chrono`
--

CREATE TABLE `kp_chrono` (
  `IdMatch` int(10) UNSIGNED NOT NULL,
  `action` varchar(5) NOT NULL,
  `start_time` varchar(20) NOT NULL,
  `start_time_server` int(11) DEFAULT NULL,
  `run_time` varchar(20) NOT NULL,
  `max_time` varchar(5) NOT NULL,
  `shotclock` text DEFAULT NULL,
  `penalties` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_club`
--

CREATE TABLE `kp_club` (
  `Code` varchar(6) NOT NULL DEFAULT '',
  `Libelle` varchar(100) NOT NULL DEFAULT '',
  `Officiel` char(1) DEFAULT NULL,
  `Reserve` varchar(20) DEFAULT NULL,
  `Code_comite_dep` varchar(6) NOT NULL,
  `Coord` varchar(50) DEFAULT NULL,
  `Postal` varchar(100) DEFAULT NULL,
  `Coord2` varchar(60) DEFAULT NULL,
  `www` varchar(60) DEFAULT NULL,
  `email` varchar(60) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_competition`
--

CREATE TABLE `kp_competition` (
  `Code` varchar(12) NOT NULL DEFAULT '',
  `Code_saison` char(4) NOT NULL DEFAULT '',
  `Code_niveau` char(3) DEFAULT NULL,
  `Libelle` varchar(80) DEFAULT NULL,
  `Soustitre` varchar(80) DEFAULT NULL,
  `Soustitre2` varchar(80) DEFAULT NULL,
  `Web` mediumtext DEFAULT NULL,
  `BandeauLink` mediumtext NOT NULL,
  `LogoLink` mediumtext NOT NULL,
  `SponsorLink` mediumtext NOT NULL,
  `En_actif` char(1) NOT NULL DEFAULT '',
  `Titre_actif` char(1) NOT NULL DEFAULT 'O',
  `Bandeau_actif` char(1) NOT NULL,
  `Logo_actif` char(1) NOT NULL DEFAULT '',
  `Sponsor_actif` char(1) NOT NULL DEFAULT '',
  `Kpi_ffck_actif` char(1) NOT NULL DEFAULT 'O',
  `ToutGroup` char(1) NOT NULL DEFAULT '',
  `TouteSaisons` char(1) NOT NULL DEFAULT '',
  `Code_ref` varchar(10) NOT NULL,
  `GroupOrder` tinyint(4) NOT NULL DEFAULT 0,
  `Code_typeclt` varchar(8) DEFAULT NULL,
  `Age_min` smallint(6) DEFAULT NULL,
  `Age_max` smallint(6) DEFAULT NULL,
  `Sexe` char(1) DEFAULT NULL,
  `Code_tour` smallint(6) DEFAULT NULL,
  `Nb_equipes` tinyint(2) DEFAULT NULL,
  `Verrou` char(1) DEFAULT NULL,
  `Statut` varchar(3) NOT NULL DEFAULT 'ATT',
  `Qualifies` int(11) NOT NULL DEFAULT 3,
  `Elimines` int(11) NOT NULL DEFAULT 0,
  `Points` varchar(7) NOT NULL DEFAULT '4-2-1-0',
  `goalaverage` varchar(4) NOT NULL DEFAULT 'gen',
  `Date_calcul` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Mode_calcul` varchar(4) DEFAULT NULL,
  `Date_publication` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Date_publication_calcul` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Mode_publication_calcul` varchar(4) DEFAULT NULL,
  `Code_uti_calcul` varchar(8) NOT NULL DEFAULT '',
  `Code_uti_publication` varchar(8) NOT NULL DEFAULT '',
  `Publication` char(1) NOT NULL DEFAULT '',
  `Date_publi` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Code_uti_publi` varchar(8) NOT NULL DEFAULT '',
  `commentairesCompet` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_competition_equipe`
--

CREATE TABLE `kp_competition_equipe` (
  `Id` int(10) UNSIGNED NOT NULL,
  `Code_compet` varchar(12) NOT NULL DEFAULT '',
  `Code_saison` char(4) NOT NULL DEFAULT '',
  `Libelle` varchar(40) DEFAULT NULL,
  `Code_club` varchar(6) DEFAULT NULL,
  `logo` varchar(50) DEFAULT NULL,
  `color1` varchar(30) DEFAULT NULL,
  `color2` varchar(30) DEFAULT NULL,
  `colortext` varchar(30) DEFAULT NULL,
  `Numero` smallint(6) DEFAULT NULL,
  `Poule` varchar(3) NOT NULL DEFAULT '',
  `Tirage` tinyint(4) NOT NULL DEFAULT 0,
  `Pts` smallint(6) NOT NULL DEFAULT 0,
  `Clt` smallint(6) NOT NULL DEFAULT 0,
  `J` smallint(6) NOT NULL DEFAULT 0,
  `G` smallint(6) NOT NULL DEFAULT 0,
  `N` smallint(6) NOT NULL DEFAULT 0,
  `P` smallint(6) NOT NULL DEFAULT 0,
  `F` smallint(6) NOT NULL DEFAULT 0,
  `Plus` smallint(6) NOT NULL DEFAULT 0,
  `Moins` smallint(6) NOT NULL DEFAULT 0,
  `Diff` smallint(6) NOT NULL DEFAULT 0,
  `PtsNiveau` double NOT NULL DEFAULT 0,
  `CltNiveau` smallint(6) NOT NULL DEFAULT 0,
  `Id_dupli` int(11) DEFAULT NULL,
  `Pts_publi` smallint(6) NOT NULL DEFAULT 0,
  `Clt_publi` smallint(6) NOT NULL DEFAULT 0,
  `J_publi` smallint(6) NOT NULL DEFAULT 0,
  `G_publi` smallint(6) NOT NULL DEFAULT 0,
  `N_publi` smallint(6) NOT NULL DEFAULT 0,
  `P_publi` smallint(6) NOT NULL DEFAULT 0,
  `F_publi` smallint(6) NOT NULL DEFAULT 0,
  `Plus_publi` smallint(6) NOT NULL DEFAULT 0,
  `Moins_publi` smallint(6) NOT NULL DEFAULT 0,
  `Diff_publi` smallint(6) NOT NULL DEFAULT 0,
  `PtsNiveau_publi` double NOT NULL DEFAULT 0,
  `CltNiveau_publi` smallint(6) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_competition_equipe_init`
--

CREATE TABLE `kp_competition_equipe_init` (
  `Id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `Pts` smallint(6) DEFAULT NULL,
  `Clt` smallint(6) DEFAULT NULL,
  `J` smallint(6) DEFAULT NULL,
  `G` smallint(6) DEFAULT NULL,
  `N` smallint(6) DEFAULT NULL,
  `P` smallint(6) DEFAULT NULL,
  `F` smallint(6) DEFAULT NULL,
  `Plus` smallint(6) DEFAULT NULL,
  `Moins` smallint(6) DEFAULT NULL,
  `Diff` smallint(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_competition_equipe_joueur`
--

CREATE TABLE `kp_competition_equipe_joueur` (
  `Id_equipe` int(10) UNSIGNED NOT NULL,
  `Matric` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `Nom` varchar(30) DEFAULT NULL,
  `Prenom` varchar(30) DEFAULT NULL,
  `Sexe` char(1) DEFAULT NULL,
  `Categ` varchar(8) DEFAULT NULL,
  `Numero` smallint(6) DEFAULT NULL,
  `Capitaine` char(1) DEFAULT '-'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_competition_equipe_journee`
--

CREATE TABLE `kp_competition_equipe_journee` (
  `Id` int(10) UNSIGNED NOT NULL,
  `Id_journee` int(11) NOT NULL DEFAULT 0,
  `Pts` smallint(6) DEFAULT NULL,
  `Clt` smallint(6) DEFAULT NULL,
  `J` smallint(6) DEFAULT NULL,
  `G` smallint(6) DEFAULT NULL,
  `N` smallint(6) DEFAULT NULL,
  `P` smallint(6) DEFAULT NULL,
  `F` smallint(6) DEFAULT NULL,
  `Plus` smallint(6) DEFAULT NULL,
  `Moins` smallint(6) DEFAULT NULL,
  `Diff` smallint(6) DEFAULT NULL,
  `PtsNiveau` double UNSIGNED DEFAULT NULL,
  `CltNiveau` smallint(6) DEFAULT NULL,
  `Pts_publi` smallint(6) DEFAULT NULL,
  `Clt_publi` smallint(6) DEFAULT NULL,
  `J_publi` smallint(6) DEFAULT NULL,
  `G_publi` smallint(6) DEFAULT NULL,
  `N_publi` smallint(6) DEFAULT NULL,
  `P_publi` smallint(6) DEFAULT NULL,
  `F_publi` smallint(6) DEFAULT NULL,
  `Plus_publi` smallint(6) DEFAULT NULL,
  `Moins_publi` smallint(6) DEFAULT NULL,
  `Diff_publi` smallint(6) DEFAULT NULL,
  `PtsNiveau_publi` double DEFAULT NULL,
  `CltNiveau_publi` smallint(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_competition_equipe_niveau`
--

CREATE TABLE `kp_competition_equipe_niveau` (
  `Id` int(10) UNSIGNED NOT NULL,
  `Niveau` smallint(6) NOT NULL DEFAULT 0,
  `Pts` smallint(6) DEFAULT NULL,
  `Clt` smallint(6) DEFAULT NULL,
  `J` smallint(6) DEFAULT NULL,
  `G` smallint(6) DEFAULT NULL,
  `N` smallint(6) DEFAULT NULL,
  `P` smallint(6) DEFAULT NULL,
  `F` smallint(6) DEFAULT NULL,
  `Plus` smallint(6) DEFAULT NULL,
  `Moins` smallint(6) DEFAULT NULL,
  `Diff` smallint(6) DEFAULT NULL,
  `PtsNiveau` double UNSIGNED DEFAULT NULL,
  `CltNiveau` smallint(6) DEFAULT NULL,
  `Pts_publi` smallint(6) DEFAULT NULL,
  `Clt_publi` smallint(6) DEFAULT NULL,
  `J_publi` smallint(6) DEFAULT NULL,
  `G_publi` smallint(6) DEFAULT NULL,
  `N_publi` smallint(6) DEFAULT NULL,
  `P_publi` smallint(6) DEFAULT NULL,
  `F_publi` smallint(6) DEFAULT NULL,
  `Plus_publi` smallint(6) DEFAULT NULL,
  `Moins_publi` smallint(6) DEFAULT NULL,
  `Diff_publi` smallint(6) DEFAULT NULL,
  `PtsNiveau_publi` double DEFAULT NULL,
  `CltNiveau_publi` smallint(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_cr`
--

CREATE TABLE `kp_cr` (
  `Code` varchar(6) NOT NULL DEFAULT '',
  `Libelle` varchar(100) NOT NULL DEFAULT '',
  `Officiel` char(1) DEFAULT NULL,
  `Reserve` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_equipe`
--

CREATE TABLE `kp_equipe` (
  `Numero` smallint(6) NOT NULL,
  `Libelle` varchar(30) NOT NULL DEFAULT '',
  `Code_club` varchar(6) NOT NULL DEFAULT '',
  `color1` varchar(30) DEFAULT NULL,
  `color2` varchar(30) DEFAULT NULL,
  `colortext` varchar(30) DEFAULT NULL,
  `logo` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_evenement`
--

CREATE TABLE `kp_evenement` (
  `Id` int(11) NOT NULL,
  `Libelle` varchar(50) DEFAULT NULL,
  `Lieu` varchar(50) DEFAULT NULL,
  `Date_debut` date DEFAULT NULL,
  `Date_fin` date DEFAULT NULL,
  `Publication` char(1) NOT NULL DEFAULT '',
  `Date_publi` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Code_uti_publi` varchar(8) NOT NULL DEFAULT '',
  `logo` varchar(50) DEFAULT NULL,
  `app` char(1) NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_evenement_export`
--

CREATE TABLE `kp_evenement_export` (
  `Id` int(11) NOT NULL,
  `Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Utilisateur` varchar(8) NOT NULL DEFAULT '',
  `Evenement` varchar(15) NOT NULL DEFAULT '',
  `Mouvement` varchar(10) NOT NULL DEFAULT '' COMMENT 'Export, Import',
  `Parametres` mediumtext DEFAULT NULL COMMENT 'Journees modifiees...',
  `Erreurs` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_evenement_journee`
--

CREATE TABLE `kp_evenement_journee` (
  `Id_evenement` int(11) NOT NULL,
  `Id_journee` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_event_worker_config`
--

CREATE TABLE `kp_event_worker_config` (
  `id` int(11) NOT NULL,
  `id_event` int(11) NOT NULL,
  `date_event` date NOT NULL,
  `hour_event` time NOT NULL,
  `hour_event_initial` time NOT NULL COMMENT 'Heure de départ initiale (référence)',
  `offset_event` int(11) NOT NULL DEFAULT 15 COMMENT 'Warm-up en minutes',
  `pitch_event` int(11) NOT NULL DEFAULT 4 COMMENT 'Nombre de terrains',
  `delay_event` int(11) NOT NULL DEFAULT 10 COMMENT 'Délai de rafraîchissement en secondes',
  `status` enum('stopped','running','paused') NOT NULL DEFAULT 'stopped',
  `last_execution` datetime DEFAULT NULL COMMENT 'Timestamp de la dernière exécution',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `error_message` text DEFAULT NULL COMMENT 'Dernier message d''erreur',
  `execution_count` int(11) NOT NULL DEFAULT 0 COMMENT 'Nombre d''exécutions effectuées'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='Configuration du worker pour génération automatique des caches d''événements';

-- --------------------------------------------------------

--
-- Structure de la table `kp_groupe`
--

CREATE TABLE `kp_groupe` (
  `id` int(11) NOT NULL,
  `section` int(11) NOT NULL,
  `ordre` int(11) NOT NULL,
  `Code_niveau` char(3) NOT NULL DEFAULT 'NAT',
  `Groupe` varchar(10) NOT NULL DEFAULT '',
  `Libelle` mediumtext NOT NULL,
  `Calendar` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_journal`
--

CREATE TABLE `kp_journal` (
  `Id` int(11) NOT NULL,
  `Dates` timestamp NULL DEFAULT NULL,
  `Users` varchar(8) NOT NULL DEFAULT 'INCONNU',
  `Actions` varchar(40) NOT NULL DEFAULT 'IGNORE',
  `Saisons` varchar(4) DEFAULT NULL,
  `Competitions` varchar(8) DEFAULT NULL,
  `Evenements` int(11) DEFAULT NULL,
  `Journees` int(11) DEFAULT NULL,
  `Matchs` text DEFAULT NULL,
  `Journal` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_journee`
--

CREATE TABLE `kp_journee` (
  `Id` int(11) NOT NULL DEFAULT 0,
  `Code_competition` varchar(12) NOT NULL DEFAULT '',
  `Code_saison` char(4) NOT NULL DEFAULT '',
  `Date_debut` date DEFAULT NULL,
  `Date_fin` date DEFAULT NULL,
  `Nom` varchar(80) DEFAULT NULL,
  `Libelle` varchar(80) DEFAULT NULL,
  `Lieu` varchar(40) DEFAULT NULL,
  `Departement` varchar(3) DEFAULT NULL,
  `Plan_eau` varchar(80) DEFAULT NULL,
  `Responsable_insc` varchar(80) DEFAULT NULL,
  `Responsable_insc_adr` varchar(40) DEFAULT NULL,
  `Responsable_insc_cp` varchar(5) DEFAULT NULL,
  `Responsable_insc_ville` varchar(40) DEFAULT NULL,
  `Responsable_R1` varchar(80) DEFAULT NULL,
  `Etat` char(1) DEFAULT NULL,
  `Type` char(1) NOT NULL DEFAULT 'C',
  `Code_organisateur` varchar(5) DEFAULT NULL,
  `Organisateur` varchar(40) DEFAULT NULL,
  `Organisateur_adr` varchar(40) DEFAULT NULL,
  `Organisateur_cp` varchar(5) DEFAULT NULL,
  `Organisateur_ville` varchar(40) DEFAULT NULL,
  `Delegue` varchar(80) DEFAULT NULL,
  `ChefArbitre` varchar(80) DEFAULT NULL,
  `Rep_athletes` varchar(80) DEFAULT NULL,
  `Arb_nj1` varchar(80) DEFAULT NULL,
  `Arb_nj2` varchar(80) DEFAULT NULL,
  `Arb_nj3` varchar(80) DEFAULT NULL,
  `Arb_nj4` varchar(80) DEFAULT NULL,
  `Arb_nj5` varchar(80) DEFAULT NULL,
  `Validation` char(1) DEFAULT NULL,
  `Code_uti` varchar(8) DEFAULT NULL,
  `Phase` varchar(30) DEFAULT NULL,
  `Niveau` smallint(6) DEFAULT NULL,
  `Etape` smallint(6) NOT NULL DEFAULT 1,
  `Nbequipes` smallint(6) NOT NULL DEFAULT 1,
  `Publication` char(1) DEFAULT NULL,
  `Id_dupli` int(11) DEFAULT NULL,
  `Public_prin` char(1) NOT NULL DEFAULT 'O',
  `Public_sec` char(1) NOT NULL DEFAULT 'O'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_journee_ref`
--

CREATE TABLE `kp_journee_ref` (
  `id` int(11) NOT NULL,
  `nom` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_licence`
--

CREATE TABLE `kp_licence` (
  `Matric` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `Origine` varchar(6) NOT NULL DEFAULT '',
  `Nom` varchar(30) DEFAULT NULL,
  `Prenom` varchar(30) DEFAULT NULL,
  `Sexe` char(1) DEFAULT NULL,
  `Naissance` date DEFAULT NULL,
  `Club` varchar(100) DEFAULT NULL,
  `Numero_club` varchar(6) DEFAULT NULL,
  `Comite_dept` varchar(100) DEFAULT NULL,
  `Numero_comite_dept` varchar(6) DEFAULT NULL,
  `Comite_reg` varchar(100) DEFAULT NULL,
  `Numero_comite_reg` varchar(6) DEFAULT NULL,
  `Etat` varchar(20) DEFAULT NULL,
  `Pagaie_EVI` varchar(10) DEFAULT NULL,
  `Pagaie_MER` varchar(10) DEFAULT NULL,
  `Pagaie_ECA` varchar(10) DEFAULT NULL,
  `Date_certificat_CK` date DEFAULT NULL,
  `Date_certificat_APS` date DEFAULT NULL,
  `Reserve` int(11) DEFAULT NULL,
  `Etat_certificat_APS` char(3) DEFAULT NULL,
  `Etat_certificat_CK` char(3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_match`
--

CREATE TABLE `kp_match` (
  `Id` int(10) UNSIGNED NOT NULL,
  `Id_journee` int(11) NOT NULL DEFAULT 0,
  `Libelle` varchar(30) DEFAULT NULL,
  `Type` char(1) NOT NULL DEFAULT 'C',
  `Statut` varchar(3) NOT NULL DEFAULT 'ATT',
  `Date_match` date DEFAULT NULL,
  `Heure_match` varchar(6) DEFAULT NULL,
  `Heure_fin` time DEFAULT NULL,
  `Terrain` varchar(30) DEFAULT NULL,
  `Numero_ordre` int(10) UNSIGNED DEFAULT NULL,
  `Periode` varchar(3) DEFAULT NULL,
  `Id_equipeA` int(10) UNSIGNED DEFAULT NULL,
  `Id_equipeB` int(10) UNSIGNED DEFAULT NULL,
  `ColorA` varchar(20) DEFAULT NULL,
  `ColorB` varchar(20) DEFAULT NULL,
  `ScoreA` varchar(4) DEFAULT NULL,
  `ScoreB` varchar(4) DEFAULT NULL,
  `ScoreDetailA` int(11) DEFAULT NULL,
  `ScoreDetailB` int(11) DEFAULT NULL,
  `CoeffA` double NOT NULL DEFAULT 1,
  `CoeffB` double NOT NULL DEFAULT 1,
  `Commentaires_officiels` mediumtext DEFAULT NULL,
  `Commentaires` mediumtext DEFAULT NULL,
  `Arbitre_principal` varchar(50) DEFAULT NULL,
  `Arbitre_secondaire` varchar(50) DEFAULT NULL,
  `Matric_arbitre_principal` int(10) DEFAULT NULL,
  `Matric_arbitre_secondaire` int(10) DEFAULT NULL,
  `Secretaire` varchar(50) DEFAULT NULL,
  `Chronometre` varchar(50) DEFAULT NULL,
  `Timeshoot` varchar(50) DEFAULT NULL,
  `Ligne1` varchar(50) DEFAULT NULL,
  `Ligne2` varchar(50) DEFAULT NULL,
  `Publication` char(1) DEFAULT NULL,
  `Code_uti` varchar(8) DEFAULT NULL,
  `Validation` char(1) NOT NULL DEFAULT '',
  `Imprime` char(1) NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_match_detail`
--

CREATE TABLE `kp_match_detail` (
  `Id` varchar(32) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT replace(uuid(),'-',''),
  `Id_match` int(10) UNSIGNED NOT NULL,
  `Periode` char(3) DEFAULT NULL,
  `Temps` time DEFAULT NULL,
  `Id_evt_match` varchar(2) DEFAULT NULL,
  `motif` varchar(10) DEFAULT NULL,
  `Competiteur` int(11) UNSIGNED DEFAULT NULL,
  `Numero` varchar(6) DEFAULT NULL,
  `Equipe_A_B` char(1) NOT NULL DEFAULT '',
  `date_insert` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_match_detail_old`
--

CREATE TABLE `kp_match_detail_old` (
  `Id` int(10) UNSIGNED NOT NULL,
  `Id_match` int(10) UNSIGNED NOT NULL,
  `Periode` char(3) DEFAULT NULL,
  `Temps` time DEFAULT NULL,
  `Id_evt_match` varchar(2) DEFAULT NULL,
  `motif` varchar(10) DEFAULT NULL,
  `Competiteur` int(11) UNSIGNED DEFAULT NULL,
  `Numero` varchar(6) DEFAULT NULL,
  `Equipe_A_B` char(1) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_match_joueur`
--

CREATE TABLE `kp_match_joueur` (
  `Id_match` int(10) UNSIGNED NOT NULL,
  `Matric` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `Numero` smallint(6) DEFAULT NULL,
  `Equipe` char(1) DEFAULT NULL,
  `Capitaine` char(1) DEFAULT '-'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_rc`
--

CREATE TABLE `kp_rc` (
  `Id` int(11) NOT NULL,
  `Code_competition` varchar(10) DEFAULT NULL,
  `Code_saison` varchar(8) NOT NULL,
  `Ordre` int(11) NOT NULL,
  `Matric` int(11) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_recherche_licence`
--

CREATE TABLE `kp_recherche_licence` (
  `Signature` varchar(20) NOT NULL DEFAULT '',
  `Matric` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `Horaire` timestamp NOT NULL DEFAULT current_timestamp(),
  `Validation` char(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_saison`
--

CREATE TABLE `kp_saison` (
  `Code` char(4) NOT NULL DEFAULT '',
  `Etat` char(1) NOT NULL DEFAULT '',
  `Nat_debut` date NOT NULL DEFAULT '0000-00-00',
  `Nat_fin` date NOT NULL DEFAULT '0000-00-00',
  `Inter_debut` date NOT NULL DEFAULT '0000-00-00',
  `Inter_fin` date NOT NULL DEFAULT '0000-00-00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_scrutineering`
--

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
  `paddle_print` smallint(6) DEFAULT NULL,
  `comment` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_stats`
--

CREATE TABLE `kp_stats` (
  `id` int(11) NOT NULL,
  `date_stat` datetime NOT NULL DEFAULT current_timestamp(),
  `user` varchar(8) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `game` int(10) UNSIGNED NOT NULL,
  `team` int(10) UNSIGNED NOT NULL,
  `player` int(11) UNSIGNED NOT NULL,
  `action` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `period` varchar(5) NOT NULL,
  `timer` varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_surclassement`
--

CREATE TABLE `kp_surclassement` (
  `Matric` int(11) NOT NULL,
  `Saison` varchar(6) NOT NULL,
  `Cat` varchar(5) NOT NULL,
  `Date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_tv`
--

CREATE TABLE `kp_tv` (
  `Voie` int(11) NOT NULL,
  `Url` varchar(1024) NOT NULL,
  `intervalle` int(11) NOT NULL DEFAULT 10000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_user`
--

CREATE TABLE `kp_user` (
  `Code` varchar(8) NOT NULL DEFAULT '',
  `Pwd` varchar(40) NOT NULL DEFAULT '',
  `Identite` varchar(80) DEFAULT NULL,
  `Mail` varchar(100) NOT NULL DEFAULT '',
  `Tel` varchar(15) NOT NULL,
  `Fonction` varchar(100) NOT NULL DEFAULT '',
  `Niveau` smallint(6) NOT NULL DEFAULT 0,
  `Type_filtre_competition` smallint(6) NOT NULL DEFAULT 0,
  `Filtre_competition` mediumtext NOT NULL,
  `Filtre_saison` mediumtext NOT NULL,
  `Filtre_competition_sql` mediumtext NOT NULL,
  `Filtre_journee` mediumtext NOT NULL,
  `Limitation_equipe_club` varchar(50) DEFAULT NULL,
  `Id_Evenement` varchar(20) NOT NULL DEFAULT '',
  `Date_debut` date DEFAULT NULL,
  `Date_fin` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `kp_user_token`
--

CREATE TABLE `kp_user_token` (
  `user` varchar(8) NOT NULL,
  `token` varchar(32) NOT NULL,
  `generated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `villes_france_free`
--

CREATE TABLE `villes_france_free` (
  `ville_id` mediumint(8) UNSIGNED NOT NULL,
  `ville_departement` varchar(3) DEFAULT NULL,
  `ville_slug` varchar(255) DEFAULT NULL,
  `ville_nom` varchar(45) DEFAULT NULL,
  `ville_nom_simple` varchar(45) DEFAULT NULL,
  `ville_nom_reel` varchar(45) DEFAULT NULL,
  `ville_nom_soundex` varchar(20) DEFAULT NULL,
  `ville_nom_metaphone` varchar(22) DEFAULT NULL,
  `ville_code_postal` varchar(255) DEFAULT NULL,
  `ville_commune` varchar(3) DEFAULT NULL,
  `ville_code_commune` varchar(5) NOT NULL,
  `ville_arrondissement` smallint(3) UNSIGNED DEFAULT NULL,
  `ville_canton` varchar(4) DEFAULT NULL,
  `ville_amdi` smallint(5) UNSIGNED DEFAULT NULL,
  `ville_population_2010` mediumint(11) UNSIGNED DEFAULT NULL,
  `ville_population_1999` mediumint(11) UNSIGNED DEFAULT NULL,
  `ville_population_2012` mediumint(10) UNSIGNED DEFAULT NULL COMMENT 'approximatif',
  `ville_densite_2010` int(11) DEFAULT NULL,
  `ville_surface` float DEFAULT NULL,
  `ville_longitude_deg` float DEFAULT NULL,
  `ville_latitude_deg` float DEFAULT NULL,
  `ville_longitude_grd` varchar(9) DEFAULT NULL,
  `ville_latitude_grd` varchar(8) DEFAULT NULL,
  `ville_longitude_dms` varchar(9) DEFAULT NULL,
  `ville_latitude_dms` varchar(8) DEFAULT NULL,
  `ville_zmin` mediumint(4) DEFAULT NULL,
  `ville_zmax` mediumint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `kp_app_rating`
--
ALTER TABLE `kp_app_rating`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `kp_arbitre`
--
ALTER TABLE `kp_arbitre`
  ADD PRIMARY KEY (`Matric`);

--
-- Index pour la table `kp_arbitre_old_2020_05_03`
--
ALTER TABLE `kp_arbitre_old_2020_05_03`
  ADD PRIMARY KEY (`Matric`);

--
-- Index pour la table `kp_arbitre_old_2025_01_14`
--
ALTER TABLE `kp_arbitre_old_2025_01_14`
  ADD PRIMARY KEY (`Matric`);

--
-- Index pour la table `kp_arbitre_old_2025_02_13`
--
ALTER TABLE `kp_arbitre_old_2025_02_13`
  ADD PRIMARY KEY (`Matric`);

--
-- Index pour la table `kp_categorie`
--
ALTER TABLE `kp_categorie`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `kp_cd`
--
ALTER TABLE `kp_cd`
  ADD PRIMARY KEY (`Code`),
  ADD KEY `fk_cd_cr` (`Code_comite_reg`);

--
-- Index pour la table `kp_chrono`
--
ALTER TABLE `kp_chrono`
  ADD PRIMARY KEY (`IdMatch`);

--
-- Index pour la table `kp_club`
--
ALTER TABLE `kp_club`
  ADD PRIMARY KEY (`Code`),
  ADD KEY `fk_club_cd` (`Code_comite_dep`);

--
-- Index pour la table `kp_competition`
--
ALTER TABLE `kp_competition`
  ADD PRIMARY KEY (`Code`,`Code_saison`),
  ADD KEY `fk_competitions_saison` (`Code_saison`),
  ADD KEY `fk_competitions_groupes` (`Code_ref`);

--
-- Index pour la table `kp_competition_equipe`
--
ALTER TABLE `kp_competition_equipe`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `Code_compet` (`Code_compet`),
  ADD KEY `fk_competitions_equipes` (`Code_compet`,`Code_saison`),
  ADD KEY `fk_compet_equipes_club` (`Code_club`),
  ADD KEY `fk_equipes_numero` (`Numero`);

--
-- Index pour la table `kp_competition_equipe_init`
--
ALTER TABLE `kp_competition_equipe_init`
  ADD PRIMARY KEY (`Id`);

--
-- Index pour la table `kp_competition_equipe_joueur`
--
ALTER TABLE `kp_competition_equipe_joueur`
  ADD PRIMARY KEY (`Id_equipe`,`Matric`),
  ADD KEY `Matric` (`Matric`);

--
-- Index pour la table `kp_competition_equipe_journee`
--
ALTER TABLE `kp_competition_equipe_journee`
  ADD PRIMARY KEY (`Id`,`Id_journee`),
  ADD KEY `Id_journee` (`Id_journee`);

--
-- Index pour la table `kp_competition_equipe_niveau`
--
ALTER TABLE `kp_competition_equipe_niveau`
  ADD PRIMARY KEY (`Id`,`Niveau`);

--
-- Index pour la table `kp_cr`
--
ALTER TABLE `kp_cr`
  ADD PRIMARY KEY (`Code`);

--
-- Index pour la table `kp_equipe`
--
ALTER TABLE `kp_equipe`
  ADD PRIMARY KEY (`Numero`),
  ADD KEY `fk_equipe_club` (`Code_club`);

--
-- Index pour la table `kp_evenement`
--
ALTER TABLE `kp_evenement`
  ADD PRIMARY KEY (`Id`);

--
-- Index pour la table `kp_evenement_export`
--
ALTER TABLE `kp_evenement_export`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `Utilisateur` (`Utilisateur`);

--
-- Index pour la table `kp_evenement_journee`
--
ALTER TABLE `kp_evenement_journee`
  ADD PRIMARY KEY (`Id_evenement`,`Id_journee`),
  ADD KEY `Id_evenement` (`Id_evenement`),
  ADD KEY `fk_evenements_journee` (`Id_journee`);

--
-- Index pour la table `kp_event_worker_config`
--
ALTER TABLE `kp_event_worker_config`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_id_event` (`id_event`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_date_event` (`date_event`),
  ADD KEY `idx_last_execution` (`last_execution`);

--
-- Index pour la table `kp_groupe`
--
ALTER TABLE `kp_groupe`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `Groupe` (`Groupe`),
  ADD KEY `ordre` (`ordre`),
  ADD KEY `section` (`section`),
  ADD KEY `Groupe_2` (`Groupe`);

--
-- Index pour la table `kp_journal`
--
ALTER TABLE `kp_journal`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `Users` (`Users`);

--
-- Index pour la table `kp_journee`
--
ALTER TABLE `kp_journee`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `Code_saison` (`Code_saison`),
  ADD KEY `Code_competition` (`Code_competition`),
  ADD KEY `fk_journees_competitions` (`Code_competition`,`Code_saison`);

--
-- Index pour la table `kp_journee_ref`
--
ALTER TABLE `kp_journee_ref`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `kp_licence`
--
ALTER TABLE `kp_licence`
  ADD PRIMARY KEY (`Matric`),
  ADD KEY `Numero_club` (`Numero_club`),
  ADD KEY `Reserve` (`Reserve`);

--
-- Index pour la table `kp_match`
--
ALTER TABLE `kp_match`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `Id_journee` (`Id_journee`),
  ADD KEY `Id_equipeA` (`Id_equipeA`),
  ADD KEY `Id_equipeB` (`Id_equipeB`);

--
-- Index pour la table `kp_match_detail`
--
ALTER TABLE `kp_match_detail`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `Competiteur` (`Competiteur`),
  ADD KEY `Id_match` (`Id_match`),
  ADD KEY `Id_evt_match` (`Id_evt_match`),
  ADD KEY `date_insert` (`date_insert`);

--
-- Index pour la table `kp_match_detail_old`
--
ALTER TABLE `kp_match_detail_old`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `Competiteur` (`Competiteur`),
  ADD KEY `Id_match` (`Id_match`),
  ADD KEY `Id_evt_match` (`Id_evt_match`);

--
-- Index pour la table `kp_match_joueur`
--
ALTER TABLE `kp_match_joueur`
  ADD PRIMARY KEY (`Id_match`,`Matric`),
  ADD KEY `fk_matchs_matric` (`Matric`);

--
-- Index pour la table `kp_rc`
--
ALTER TABLE `kp_rc`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `fk_rc_matric` (`Matric`);

--
-- Index pour la table `kp_recherche_licence`
--
ALTER TABLE `kp_recherche_licence`
  ADD PRIMARY KEY (`Signature`,`Matric`),
  ADD KEY `fk_recherche_matric` (`Matric`);

--
-- Index pour la table `kp_saison`
--
ALTER TABLE `kp_saison`
  ADD PRIMARY KEY (`Code`),
  ADD KEY `Etat` (`Etat`);

--
-- Index pour la table `kp_scrutineering`
--
ALTER TABLE `kp_scrutineering`
  ADD PRIMARY KEY (`id_equipe`,`matric`);

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
-- Index pour la table `kp_surclassement`
--
ALTER TABLE `kp_surclassement`
  ADD PRIMARY KEY (`Matric`,`Saison`);

--
-- Index pour la table `kp_tv`
--
ALTER TABLE `kp_tv`
  ADD PRIMARY KEY (`Voie`);

--
-- Index pour la table `kp_user`
--
ALTER TABLE `kp_user`
  ADD PRIMARY KEY (`Code`);

--
-- Index pour la table `kp_user_token`
--
ALTER TABLE `kp_user_token`
  ADD PRIMARY KEY (`user`);

--
-- Index pour la table `villes_france_free`
--
ALTER TABLE `villes_france_free`
  ADD PRIMARY KEY (`ville_id`),
  ADD UNIQUE KEY `ville_code_commune_2` (`ville_code_commune`),
  ADD UNIQUE KEY `ville_slug` (`ville_slug`),
  ADD KEY `ville_departement` (`ville_departement`),
  ADD KEY `ville_nom` (`ville_nom`),
  ADD KEY `ville_nom_reel` (`ville_nom_reel`),
  ADD KEY `ville_code_commune` (`ville_code_commune`),
  ADD KEY `ville_code_postal` (`ville_code_postal`),
  ADD KEY `ville_longitude_latitude_deg` (`ville_longitude_deg`,`ville_latitude_deg`),
  ADD KEY `ville_nom_soundex` (`ville_nom_soundex`),
  ADD KEY `ville_nom_metaphone` (`ville_nom_metaphone`),
  ADD KEY `ville_population_2010` (`ville_population_2010`),
  ADD KEY `ville_nom_simple` (`ville_nom_simple`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `kp_app_rating`
--
ALTER TABLE `kp_app_rating`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `kp_competition_equipe`
--
ALTER TABLE `kp_competition_equipe`
  MODIFY `Id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `kp_equipe`
--
ALTER TABLE `kp_equipe`
  MODIFY `Numero` smallint(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `kp_evenement`
--
ALTER TABLE `kp_evenement`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `kp_evenement_export`
--
ALTER TABLE `kp_evenement_export`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `kp_event_worker_config`
--
ALTER TABLE `kp_event_worker_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `kp_groupe`
--
ALTER TABLE `kp_groupe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `kp_journal`
--
ALTER TABLE `kp_journal`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `kp_journee_ref`
--
ALTER TABLE `kp_journee_ref`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `kp_match`
--
ALTER TABLE `kp_match`
  MODIFY `Id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `kp_match_detail_old`
--
ALTER TABLE `kp_match_detail_old`
  MODIFY `Id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `kp_rc`
--
ALTER TABLE `kp_rc`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `kp_stats`
--
ALTER TABLE `kp_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `villes_france_free`
--
ALTER TABLE `villes_france_free`
  MODIFY `ville_id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `kp_arbitre`
--
ALTER TABLE `kp_arbitre`
  ADD CONSTRAINT `fk_lc_matric` FOREIGN KEY (`Matric`) REFERENCES `kp_licence` (`Matric`);

--
-- Contraintes pour la table `kp_arbitre_old_2025_02_13`
--
ALTER TABLE `kp_arbitre_old_2025_02_13`
  ADD CONSTRAINT `fk_lc_matric_old` FOREIGN KEY (`Matric`) REFERENCES `kp_licence` (`Matric`);

--
-- Contraintes pour la table `kp_cd`
--
ALTER TABLE `kp_cd`
  ADD CONSTRAINT `fk_cd_cr` FOREIGN KEY (`Code_comite_reg`) REFERENCES `kp_cr` (`Code`);

--
-- Contraintes pour la table `kp_chrono`
--
ALTER TABLE `kp_chrono`
  ADD CONSTRAINT `fk_chrono_match` FOREIGN KEY (`IdMatch`) REFERENCES `kp_match` (`Id`);

--
-- Contraintes pour la table `kp_club`
--
ALTER TABLE `kp_club`
  ADD CONSTRAINT `fk_club_cd` FOREIGN KEY (`Code_comite_dep`) REFERENCES `kp_cd` (`Code`);

--
-- Contraintes pour la table `kp_competition`
--
ALTER TABLE `kp_competition`
  ADD CONSTRAINT `fk_competitions_groupes` FOREIGN KEY (`Code_ref`) REFERENCES `kp_groupe` (`Groupe`),
  ADD CONSTRAINT `fk_competitions_saison` FOREIGN KEY (`Code_saison`) REFERENCES `kp_saison` (`Code`);

--
-- Contraintes pour la table `kp_competition_equipe`
--
ALTER TABLE `kp_competition_equipe`
  ADD CONSTRAINT `fk_compet_equipes_club` FOREIGN KEY (`Code_club`) REFERENCES `kp_club` (`Code`),
  ADD CONSTRAINT `fk_competitions_equipes` FOREIGN KEY (`Code_compet`,`Code_saison`) REFERENCES `kp_competition` (`Code`, `Code_saison`),
  ADD CONSTRAINT `fk_equipes_numero` FOREIGN KEY (`Numero`) REFERENCES `kp_equipe` (`Numero`);

--
-- Contraintes pour la table `kp_competition_equipe_init`
--
ALTER TABLE `kp_competition_equipe_init`
  ADD CONSTRAINT `fk_init_id` FOREIGN KEY (`Id`) REFERENCES `kp_competition_equipe` (`Id`);

--
-- Contraintes pour la table `kp_competition_equipe_joueur`
--
ALTER TABLE `kp_competition_equipe_joueur`
  ADD CONSTRAINT `fk_equipes_joueurs` FOREIGN KEY (`Id_equipe`) REFERENCES `kp_competition_equipe` (`Id`),
  ADD CONSTRAINT `fk_equipes_joueurs_matric` FOREIGN KEY (`Matric`) REFERENCES `kp_licence` (`Matric`);

--
-- Contraintes pour la table `kp_competition_equipe_journee`
--
ALTER TABLE `kp_competition_equipe_journee`
  ADD CONSTRAINT `fk_equipes` FOREIGN KEY (`Id`) REFERENCES `kp_competition_equipe` (`Id`),
  ADD CONSTRAINT `fk_equipes_journee` FOREIGN KEY (`Id_journee`) REFERENCES `kp_journee` (`Id`);

--
-- Contraintes pour la table `kp_competition_equipe_niveau`
--
ALTER TABLE `kp_competition_equipe_niveau`
  ADD CONSTRAINT `fk_equipes_niveau` FOREIGN KEY (`Id`) REFERENCES `kp_competition_equipe` (`Id`);

--
-- Contraintes pour la table `kp_equipe`
--
ALTER TABLE `kp_equipe`
  ADD CONSTRAINT `fk_equipe_club` FOREIGN KEY (`Code_club`) REFERENCES `kp_club` (`Code`);

--
-- Contraintes pour la table `kp_evenement_journee`
--
ALTER TABLE `kp_evenement_journee`
  ADD CONSTRAINT `fk_evenements_evenement` FOREIGN KEY (`Id_evenement`) REFERENCES `kp_evenement` (`Id`),
  ADD CONSTRAINT `fk_evenements_journee` FOREIGN KEY (`Id_journee`) REFERENCES `kp_journee` (`Id`);

--
-- Contraintes pour la table `kp_journee`
--
ALTER TABLE `kp_journee`
  ADD CONSTRAINT `fk_journees_competitions` FOREIGN KEY (`Code_competition`,`Code_saison`) REFERENCES `kp_competition` (`Code`, `Code_saison`);

--
-- Contraintes pour la table `kp_match`
--
ALTER TABLE `kp_match`
  ADD CONSTRAINT `fk_matchs_equipe_a` FOREIGN KEY (`Id_equipeA`) REFERENCES `kp_competition_equipe` (`Id`),
  ADD CONSTRAINT `fk_matchs_equipe_b` FOREIGN KEY (`Id_equipeB`) REFERENCES `kp_competition_equipe` (`Id`),
  ADD CONSTRAINT `fk_matchs_journee` FOREIGN KEY (`Id_journee`) REFERENCES `kp_journee` (`Id`);

--
-- Contraintes pour la table `kp_match_detail`
--
ALTER TABLE `kp_match_detail`
  ADD CONSTRAINT `fk_matchs_detail` FOREIGN KEY (`Id_match`) REFERENCES `kp_match` (`Id`),
  ADD CONSTRAINT `fk_matchs_details_competiteur` FOREIGN KEY (`Competiteur`) REFERENCES `kp_licence` (`Matric`);

--
-- Contraintes pour la table `kp_match_joueur`
--
ALTER TABLE `kp_match_joueur`
  ADD CONSTRAINT `fk_matchs_joueur` FOREIGN KEY (`Id_match`) REFERENCES `kp_match` (`Id`),
  ADD CONSTRAINT `fk_matchs_matric` FOREIGN KEY (`Matric`) REFERENCES `kp_licence` (`Matric`);

--
-- Contraintes pour la table `kp_rc`
--
ALTER TABLE `kp_rc`
  ADD CONSTRAINT `fk_rc_matric` FOREIGN KEY (`Matric`) REFERENCES `kp_licence` (`Matric`);

--
-- Contraintes pour la table `kp_recherche_licence`
--
ALTER TABLE `kp_recherche_licence`
  ADD CONSTRAINT `fk_recherche_matric` FOREIGN KEY (`Matric`) REFERENCES `kp_licence` (`Matric`);

--
-- Contraintes pour la table `kp_scrutineering`
--
ALTER TABLE `kp_scrutineering`
  ADD CONSTRAINT `kp_scrutineering_ibfk_1` FOREIGN KEY (`id_equipe`,`matric`) REFERENCES `kp_competition_equipe_joueur` (`Id_equipe`, `Matric`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
