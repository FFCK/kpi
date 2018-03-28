<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

require('../fpdf/fpdf.php');

// Pieds de page
class PDF extends FPDF {

    function Footer() {
        //Positionnement à 1,5 cm du bas
        $this->SetY(-15);
        //Police Arial italique 8
        $this->SetFont('Arial', 'I', 8);
        //Numéro de page centré
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

}

// Liste des Matchs d'une Journee ou d'un Evenement 
class FeuilleStats extends MyPage {

    function FeuilleStats() {
        MyPage::MyPage();

        $myBdd = new MyBdd();

        //Saison
        $codeSaison = utyGetSession('codeSaison', utyGetSaison());

        //CompetitionS selectionnées
        $Compets = utyGetSession('Compets', '');
        $CompetsList = @implode(',', $Compets);
        $CompetsList = utyGetGet('Compets', $CompetsList);
        $CompetsList2 = $CompetsList;
        $premiereCompet = explode(',', $CompetsList);
        $CompetsList = str_replace(',', '","', $CompetsList);
        $CompetsList = '("' . $CompetsList . '")';

        //Données de la première compétition (pour logo, sponsor...)
        $arrayCompetition = $myBdd->GetCompetition($premiereCompet[0], $codeSaison);
        if (count($Compets) == 1) {
            if ($arrayCompetition['Titre_actif'] == 'O') {
                $CompetsList2 = $arrayCompetition['Libelle'];
            } else {
                $CompetsList2 = $arrayCompetition['Soustitre'];
            }
            if ($arrayCompetition['Soustitre2'] != '') {
                $CompetsList2 .= ' - ' . $arrayCompetition['Soustitre2'];
            }
        }

        $visuels = utyGetVisuels($arrayCompetition, TRUE);

        //Filtre stat
        $AfficheStat = utyGetSession('AfficheStat', 'Buteurs');
        $AfficheStat = utyGetGet('Stat', $AfficheStat);
        $nbLignes = utyGetSession('nbLignes', 30);
        $nbLignes = utyGetGet('nbLignes', $nbLignes);

        // Type Stats
        switch ($AfficheStat) {
            case 'Buteurs' :
            default :
                $NomStat = 'Meilleurs buteurs';
                $sql = "SELECT d.Code_competition Competition, a.Matric Licence, a.Nom, a.Prenom, a.Sexe, b.Numero, f.Libelle Equipe, COUNT(*) Buts ";
                $sql .= "FROM gickp_Liste_Coureur a, gickp_Matchs_Detail b, gickp_Matchs c, gickp_Journees d, gickp_Competitions_Equipes f ";
                $sql .= "WHERE a.Matric = b.Competiteur ";
                $sql .= "AND b.Id_match = c.Id ";
                $sql .= "AND c.Id_journee = d.Id ";
                $sql .= "AND d.Code_competition = f.Code_compet ";
                $sql .= "AND d.Code_saison = f.Code_saison ";
                $sql .= "AND f.Id = IF(b.Equipe_A_B='A',c.Id_equipeA, c.Id_equipeB) ";
                $sql .= "AND d.Code_competition IN $CompetsList ";
                $sql .= "AND d.Code_saison = '$codeSaison' ";
                $sql .= "AND b.Id_evt_match = 'B' ";
                $sql .= "GROUP BY a.Matric ";
                $sql .= "ORDER BY Buts DESC, a.Nom ";
                $sql .= "LIMIT 0,$nbLignes ";
                $result = mysql_query($sql, $myBdd->m_link) or die("Erreur Load");
                $num_results = mysql_num_rows($result);

                $arrayButeurs = array();
                for ($i = 0; $i < $num_results; $i++) {
                    $row = mysql_fetch_array($result);
                    array_push($arrayButeurs, array('Competition' => $row['Competition'],
                        'Licence' => $row['Licence'],
                        'Nom' => $row['Nom'],
                        'Prenom' => $row['Prenom'],
                        'Sexe' => $row['Sexe'],
                        'Numero' => $row['Numero'],
                        'Equipe' => $row['Equipe'],
                        'Buts' => $row['Buts']));
                }
                break;
            case 'Attaque' :
                $NomStat = 'Meilleure attaque (buts des feuilles de match uniquement)';
                $sql = "SELECT d.Code_competition Competition, f.Libelle Equipe, COUNT(*) Buts ";
                $sql .= "FROM gickp_Matchs_Detail b, gickp_Matchs c, gickp_Journees d, gickp_Competitions_Equipes f ";
                $sql .= "WHERE b.Id_match = c.Id ";
                $sql .= "AND c.Id_journee = d.Id ";
                $sql .= "AND d.Code_competition = f.Code_compet ";
                $sql .= "AND d.Code_saison = f.Code_saison ";
                $sql .= "AND f.Id = IF(b.Equipe_A_B='A',c.Id_equipeA, c.Id_equipeB) ";
                $sql .= "AND d.Code_competition IN $CompetsList ";
                $sql .= "AND d.Code_saison = '$codeSaison' ";
                $sql .= "AND b.Id_evt_match = 'B' ";
                $sql .= "GROUP BY Equipe ";
                $sql .= "ORDER BY Buts DESC, Equipe ";
                $sql .= "LIMIT 0,$nbLignes ";
                $result = mysql_query($sql, $myBdd->m_link) or die("Erreur Load : " . $sql);
                $num_results = mysql_num_rows($result);

                $arrayAttaque = array();
                for ($i = 0; $i < $num_results; $i++) {
                    $row = mysql_fetch_array($result);
                    array_push($arrayAttaque, array('Competition' => $row['Competition'],
                        'Equipe' => $row['Equipe'],
                        'Buts' => $row['Buts']));
                }
                break;
            case 'Defense' :
                $NomStat = 'Meilleure défense (buts des feuilles de match uniquement)';
                $sql = "SELECT d.Code_competition Competition, f.Libelle Equipe, COUNT(*) Buts ";
                $sql .= "FROM gickp_Matchs_Detail b, gickp_Matchs c, gickp_Journees d, gickp_Competitions_Equipes f ";
                $sql .= "WHERE b.Id_match = c.Id ";
                $sql .= "AND c.Id_journee = d.Id ";
                $sql .= "AND d.Code_competition = f.Code_compet ";
                $sql .= "AND d.Code_saison = f.Code_saison ";
                $sql .= "AND f.Id = IF(b.Equipe_A_B='B',c.Id_equipeA, c.Id_equipeB) ";
                $sql .= "AND d.Code_competition IN $CompetsList ";
                $sql .= "AND d.Code_saison = '$codeSaison' ";
                $sql .= "AND b.Id_evt_match = 'B' ";
                $sql .= "GROUP BY Equipe ";
                $sql .= "ORDER BY Buts ASC, Equipe ";
                $sql .= "LIMIT 0,$nbLignes ";
                $result = mysql_query($sql, $myBdd->m_link) or die("Erreur Load");
                $num_results = mysql_num_rows($result);

                $arrayDefense = array();
                for ($i = 0; $i < $num_results; $i++) {
                    $row = mysql_fetch_array($result);
                    array_push($arrayDefense, array('Competition' => $row['Competition'],
                        'Equipe' => $row['Equipe'],
                        'Buts' => $row['Buts']));
                }
                break;
            case 'Cartons' :
                $NomStat = 'Cartons';
                $sql = "SELECT d.Code_competition Competition, a.Matric Licence, a.Nom, a.Prenom, a.Sexe, b.Numero, f.Libelle Equipe, ";
                $sql .= "SUM(IF(b.Id_evt_match='V',1,0)) Vert, ";
                $sql .= "SUM(IF(b.Id_evt_match='J',1,0)) Jaune, ";
                $sql .= "SUM(IF(b.Id_evt_match='R',1,0)) Rouge ";
                $sql .= "FROM gickp_Liste_Coureur a, gickp_Matchs_Detail b, gickp_Matchs c, gickp_Journees d, gickp_Competitions_Equipes f ";
                $sql .= "WHERE a.Matric = b.Competiteur ";
                $sql .= "AND b.Id_match = c.Id ";
                $sql .= "AND c.Id_journee = d.Id ";
                $sql .= "AND d.Code_competition = f.Code_compet ";
                $sql .= "AND d.Code_saison = f.Code_saison ";
                $sql .= "AND f.Id = IF(b.Equipe_A_B='A',c.Id_equipeA, c.Id_equipeB) ";
                $sql .= "AND d.Code_competition IN $CompetsList ";
                $sql .= "AND d.Code_saison = '$codeSaison' ";
                $sql .= "AND b.Id_evt_match IN ('V','J','R') ";
                $sql .= "GROUP BY a.Matric ";
                $sql .= "ORDER BY Rouge Desc, Jaune Desc, Vert Desc, Equipe, a.Nom ";
                $sql .= "LIMIT 0,$nbLignes ";
                $result = mysql_query($sql, $myBdd->m_link) or die("Erreur Load");
                $num_results = mysql_num_rows($result);

                $arrayCartons = array();
                for ($i = 0; $i < $num_results; $i++) {
                    $row = mysql_fetch_array($result);
                    array_push($arrayCartons, array('Competition' => $row['Competition'],
                        'Licence' => $row['Licence'],
                        'Nom' => $row['Nom'],
                        'Prenom' => $row['Prenom'],
                        'Sexe' => $row['Sexe'],
                        'Numero' => $row['Numero'],
                        'Equipe' => $row['Equipe'],
                        'Num' => $row['Num'],
                        'Date' => $row['Date'],
                        'Heure' => $row['Heure'],
                        'Vert' => $row['Vert'],
                        'Jaune' => $row['Jaune'],
                        'Rouge' => $row['Rouge']));
                }
                break;
            case 'CartonsEquipe' :
                $NomStat = 'Cartons par équipe';
                $sql = "SELECT d.Code_competition Competition, f.Libelle Equipe, ";
                $sql .= "SUM(IF(b.Id_evt_match='V',1,0)) Vert, ";
                $sql .= "SUM(IF(b.Id_evt_match='J',1,0)) Jaune, ";
                $sql .= "SUM(IF(b.Id_evt_match='R',1,0)) Rouge ";
                $sql .= "FROM gickp_Matchs_Detail b, gickp_Matchs c, gickp_Journees d, gickp_Competitions_Equipes f ";
                $sql .= "WHERE b.Id_match = c.Id ";
                $sql .= "AND c.Id_journee = d.Id ";
                $sql .= "AND d.Code_competition = f.Code_compet ";
                $sql .= "AND d.Code_saison = f.Code_saison ";
                $sql .= "AND d.Code_competition IN $CompetsList ";
                $sql .= "AND d.Code_saison = '$codeSaison' ";
                $sql .= "AND f.Id = IF(b.Equipe_A_B='A',c.Id_equipeA, c.Id_equipeB) ";
                $sql .= "AND b.Id_evt_match IN ('V','J','R') ";
                $sql .= "GROUP BY Equipe ";
                $sql .= "ORDER BY Rouge Desc, Jaune Desc, Vert Desc, Equipe ";
                $sql .= "LIMIT 0,$nbLignes ";
                $result = mysql_query($sql, $myBdd->m_link) or die("Erreur Load");
                $num_results = mysql_num_rows($result);

                $arrayCartonsEquipe = array();
                for ($i = 0; $i < $num_results; $i++) {
                    $row = mysql_fetch_array($result);
                    array_push($arrayCartonsEquipe, array('Competition' => $row['Competition'],
                        'Licence' => $row['Licence'],
                        'Equipe' => $row['Equipe'],
                        'Vert' => $row['Vert'],
                        'Jaune' => $row['Jaune'],
                        'Rouge' => $row['Rouge']));
                }
                break;
            case 'Fairplay' :
                $NomStat = 'Classement disciplinaire individuel (rouge=4, jaune=2, vert=1)';
                $sql = "SELECT d.Code_competition Competition, a.Matric Licence, a.Nom, a.Prenom, a.Sexe, b.Numero, f.Libelle Equipe, ";
                $sql .= "SUM(IF(b.Id_evt_match='V',1, IF(b.Id_evt_match='J',2, IF(b.Id_evt_match='R',4,0)))) Fairplay ";
                $sql .= "FROM gickp_Liste_Coureur a, gickp_Matchs_Detail b, gickp_Matchs c, gickp_Journees d, gickp_Competitions_Equipes f ";
                $sql .= "WHERE a.Matric = b.Competiteur ";
                $sql .= "AND b.Id_match = c.Id ";
                $sql .= "AND c.Id_journee = d.Id ";
                $sql .= "AND d.Code_competition = f.Code_compet ";
                $sql .= "AND d.Code_saison = f.Code_saison ";
                $sql .= "AND d.Code_competition IN $CompetsList ";
                $sql .= "AND d.Code_saison = '$codeSaison' ";
                $sql .= "AND f.Id = IF(b.Equipe_A_B='A',c.Id_equipeA, c.Id_equipeB) ";
                $sql .= "AND b.Id_evt_match IN ('V','J','R') ";
                $sql .= "GROUP BY a.Matric ";
                $sql .= "ORDER BY Fairplay Desc, a.Nom ";
                $sql .= "LIMIT 0,$nbLignes ";
                $result = mysql_query($sql, $myBdd->m_link) or die("Erreur Load");
                $num_results = mysql_num_rows($result);

                $arrayFairplay = array();
                for ($i = 0; $i < $num_results; $i++) {
                    $row = mysql_fetch_array($result);
                    array_push($arrayFairplay, array('Competition' => $row['Competition'],
                        'Licence' => $row['Licence'],
                        'Nom' => $row['Nom'],
                        'Prenom' => $row['Prenom'],
                        'Sexe' => $row['Sexe'],
                        'Numero' => $row['Numero'],
                        'Equipe' => $row['Equipe'],
                        'Fairplay' => $row['Fairplay']));
                }
                break;
            case 'FairplayEquipe' :
                $NomStat = 'Classement disciplinaire par équipe (rouge=4, jaune=2, vert=1)';
                $sql = "SELECT d.Code_competition Competition, f.Libelle Equipe, ";
                $sql .= "SUM(IF(b.Id_evt_match='V',1, IF(b.Id_evt_match='J',2, IF(b.Id_evt_match='R',4,0)))) Fairplay ";
                $sql .= "FROM gickp_Matchs_Detail b, gickp_Matchs c, gickp_Journees d, gickp_Competitions_Equipes f ";
                $sql .= "WHERE b.Id_match = c.Id ";
                $sql .= "AND c.Id_journee = d.Id ";
                $sql .= "AND d.Code_competition = f.Code_compet ";
                $sql .= "AND d.Code_saison = f.Code_saison ";
                $sql .= "AND d.Code_competition IN $CompetsList ";
                $sql .= "AND d.Code_saison = '$codeSaison' ";
                $sql .= "AND f.Id = IF(b.Equipe_A_B='A',c.Id_equipeA, c.Id_equipeB) ";
                $sql .= "AND b.Id_evt_match IN ('V','J','R') ";
                $sql .= "GROUP BY Equipe ";
                $sql .= "ORDER BY Fairplay Desc, Equipe ";
                $sql .= "LIMIT 0,$nbLignes ";
                $result = mysql_query($sql, $myBdd->m_link) or die("Erreur Load");
                $num_results = mysql_num_rows($result);

                $arrayFairplayEquipe = array();
                for ($i = 0; $i < $num_results; $i++) {
                    $row = mysql_fetch_array($result);
                    array_push($arrayFairplayEquipe, array('Competition' => $row['Competition'],
                        'Equipe' => $row['Equipe'],
                        'Fairplay' => $row['Fairplay']));
                }
                break;
            case 'Arbitrage' :
                $NomStat = 'Arbitrages individuels';
                $sql = "SELECT d.Code_competition Competition, a.Matric Licence, a.Nom, a.Prenom, a.Sexe, ";
                $sql .= "SUM(IF(c.Matric_arbitre_principal=a.Matric,1,0)) principal, ";
                $sql .= "SUM(IF(c.Matric_arbitre_secondaire=a.Matric,1,0)) secondaire, ";
                $sql .= "COUNT(*) Total ";
                $sql .= "FROM gickp_Liste_Coureur a, gickp_Matchs c, gickp_Journees d ";
                $sql .= "WHERE c.Id_journee = d.Id ";
                $sql .= "AND d.Code_competition IN $CompetsList ";
                $sql .= "AND d.Code_saison = '$codeSaison' ";
                $sql .= "AND (c.Matric_arbitre_principal = a.Matric OR c.Matric_arbitre_secondaire = a.Matric) ";
                $sql .= "GROUP BY a.Matric ";
                $sql .= "ORDER BY Total DESC, principal DESC, a.Nom ";
                $sql .= "LIMIT 0,$nbLignes ";
                $result = mysql_query($sql, $myBdd->m_link) or die("Erreur Load");
                $num_results = mysql_num_rows($result);

                $arrayArbitrage = array();
                for ($i = 0; $i < $num_results; $i++) {
                    $row = mysql_fetch_array($result);
                    array_push($arrayArbitrage, array('Competition' => $row['Competition'],
                        'Licence' => $row['Licence'],
                        'Nom' => $row['Nom'],
                        'Prenom' => $row['Prenom'],
                        'Sexe' => $row['Sexe'],
                        'Principal' => $row['principal'],
                        'Secondaire' => $row['secondaire'],
                        'Total' => $row['Total']));
                }
                break;
            case 'ArbitrageEquipe' :
                $NomStat = 'Arbitrages par équipe';
                $sql = "SELECT d.Code_competition Competition, f.Libelle Equipe, ";
                $sql .= "SUM(IF((c.Arbitre_principal=f.Libelle) OR (c.Arbitre_principal LIKE CONCAT('%',f.Libelle,')%')),1,0)) principal, ";
                $sql .= "SUM(IF((c.Arbitre_secondaire=f.Libelle) OR (c.Arbitre_secondaire LIKE CONCAT('%',f.Libelle,')%')),1,0)) secondaire ";
                $sql .= "FROM gickp_Matchs c, gickp_Journees d, gickp_Competitions_Equipes f ";
                $sql .= "WHERE c.Id_journee = d.Id ";
                $sql .= "AND d.Code_competition = f.Code_compet ";
                $sql .= "AND d.Code_saison = f.Code_saison ";
                $sql .= "AND d.Code_competition IN $CompetsList ";
                $sql .= "AND d.Code_saison = '$codeSaison' ";
                $sql .= "GROUP BY Equipe ";
                $sql .= "ORDER BY principal DESC, secondaire DESC, Equipe ";
                $sql .= "LIMIT 0,$nbLignes ";
                $result = mysql_query($sql, $myBdd->m_link) or die("Erreur Load : " . $sql);
                $num_results = mysql_num_rows($result);

                $arrayArbitrageEquipe = array();
                for ($i = 0; $i < $num_results; $i++) {
                    $row = mysql_fetch_array($result);
                    array_push($arrayArbitrageEquipe, array('Competition' => $row['Competition'],
                        'Equipe' => $row['Equipe'],
                        'Principal' => $row['principal'],
                        'Secondaire' => $row['secondaire'],
                        'Total' => $row['principal'] + $row['secondaire']));
                }
                //array_multisort($arrayArbitrageEquipe[3], SORT_DESC, $arrayArbitrageEquipe);
                break;
            case 'CJouees' : // Compétitions jouées dans la saison en cours
                $NomStat = 'Compétitions jouées dans la saison en cours';
                $sql = "SELECT lc.Matric, lc.Nom, lc.Prenom, lc.Numero_club, clubs.Libelle Nom_club, j.Code_competition Competition, COUNT(DISTINCT mj.Id_match) Nb_matchs ";
                $sql .= "FROM gickp_Matchs_Joueurs mj, gickp_Matchs m, gickp_Journees j, gickp_Liste_Coureur lc, gickp_Club clubs ";
                $sql .= "WHERE lc.Matric = mj.Matric ";
                $sql .= "AND mj.Capitaine NOT IN ('E','A','X') ";
                $sql .= "AND lc.Numero_club = clubs.Code ";
                $sql .= "AND mj.Id_match = m.Id ";
                $sql .= "AND m.Id_journee = j.Id ";
                $sql .= "AND j.Code_competition IN $CompetsList ";
                $sql .= "AND j.Code_saison = '$codeSaison' ";
                $sql .= "AND m.Date_match <= CURDATE() ";
                $sql .= "AND m.Validation = 'O' ";
                $sql .= "GROUP BY mj.Matric, j.Code_competition ";
                $sql .= "ORDER BY lc.Numero_club, lc.Nom, lc.Prenom, Competition DESC  ";
                $sql .= "LIMIT 0,$nbLignes ";

                $result = mysql_query($sql, $myBdd->m_link) or die("Erreur Load : " . $sql);
                $num_results = mysql_num_rows($result);

                $arrayCJouees = array();
                for ($i = 0; $i < $num_results; $i++) {
                    $row = mysql_fetch_array($result);
                    array_push($arrayCJouees, array('Competition' => $row['Competition'],
                        'Matric' => $row['Matric'],
                        'Nom' => $row['Nom'],
                        'Prenom' => $row['Prenom'],
                        'Numero_club' => $row['Numero_club'],
                        'Nom_club' => $row['Nom_club'],
                        'Nb_matchs' => $row['Nb_matchs']));
                }
                break;
            case 'CJouees2' : // Matchs joués dans la saison en cours (par équipe)
                $NomStat = 'Matchs joués par équipe';
                $sql = "SELECT ce.Libelle nomEquipe, lc.Matric, lc.Nom, lc.Prenom, j.Code_competition Competition, COUNT(DISTINCT mj.Id_match) Nb_matchs ";
                $sql .= "FROM gickp_Matchs_Joueurs mj, gickp_Matchs m, gickp_Journees j, gickp_Liste_Coureur lc, gickp_Competitions_Equipes ce ";
                $sql .= "WHERE lc.Matric = mj.Matric ";
                $sql .= "AND mj.Capitaine NOT IN ('E','A','X') ";
                $sql .= "AND mj.Id_match = m.Id ";
                $sql .= "AND IF(mj.Equipe = 'A', m.Id_equipeA, m.Id_equipeB) = ce.Id ";
                $sql .= "AND m.Id_journee = j.Id ";
                $sql .= "AND j.Code_competition IN $CompetsList ";
                $sql .= "AND j.Code_saison = '$codeSaison' ";
                $sql .= "AND m.Date_match <= CURDATE() ";
                $sql .= "AND m.Validation = 'O' ";
                $sql .= "GROUP BY nomEquipe, mj.Matric, j.Code_competition ";
                $sql .= "ORDER BY lc.Nom, lc.Prenom, Competition ";
                $sql .= "LIMIT 0,$nbLignes ";
                $result = mysql_query($sql, $myBdd->m_link) or die("Erreur Load : " . $sql);
                $num_results = mysql_num_rows($result);

                $arrayCJouees2 = array();
                for ($i = 0; $i < $num_results; $i++) {
                    $row = mysql_fetch_array($result);
                    array_push($arrayCJouees2, array('Competition' => $row['Competition'],
                        'Matric' => $row['Matric'],
                        'Nom' => $row['Nom'],
                        'Prenom' => $row['Prenom'],
                        'nomEquipe' => $row['nomEquipe'],
                        'Nb_matchs' => $row['Nb_matchs']));
                }
                break;
            case 'CJouees3' : // Irrégularités
                $NomStat = 'Irrégularités à contrôler (matchs joués)';
                $sql = "SELECT ce.Libelle nomEquipe, lc.Matric, lc.Nom, lc.Prenom, j.Code_competition Competition, COUNT(DISTINCT mj.Id_match) Nb_matchs, ";
                $sql .= "lc.Origine, lc.Pagaie_ECA, lc.Etat_certificat_CK, lc.Etat_certificat_APS ";
                $sql .= "FROM gickp_Matchs_Joueurs mj, gickp_Matchs m, gickp_Journees j, gickp_Liste_Coureur lc, gickp_Competitions_Equipes ce ";
                $sql .= "WHERE lc.Matric = mj.Matric ";
                $sql .= "AND mj.Capitaine NOT IN ('E','A','X') ";
                $sql .= "AND mj.Id_match = m.Id ";
                $sql .= "AND IF(mj.Equipe = 'A', m.Id_equipeA, m.Id_equipeB) = ce.Id ";
                $sql .= "AND m.Id_journee = j.Id ";
                $sql .= "AND j.Code_competition IN $CompetsList ";
                $sql .= "AND j.Code_saison = '$codeSaison' ";
                $sql .= "AND m.Date_match <= CURDATE() ";
                $sql .= "AND m.Validation = 'O' ";
                $sql .= "AND (lc.Origine <> '$codeSaison' OR lc.Pagaie_ECA = '' OR lc.Pagaie_ECA = 'PAGJ' OR lc.Pagaie_ECA = 'PAGB' ";
                $sql .= " OR lc.Etat_certificat_CK <> 'OUI') ";
                $sql .= "GROUP BY nomEquipe, mj.Matric, j.Code_competition ";
                $sql .= "ORDER BY lc.Nom, lc.Prenom, Competition ";
                $sql .= "LIMIT 0,$nbLignes ";
                $result = mysql_query($sql, $myBdd->m_link) or die("Erreur Load : " . $sql);
                $num_results = mysql_num_rows($result);

                $arrayCJouees3 = array();
                for ($i = 0; $i < $num_results; $i++) {
                    $row = mysql_fetch_array($result);
                    $row['Irreg'] = '';
                    if ($row['Origine'] != $codeSaison) {
                        $row['Irreg'] = 'Licence ' . $row['Origine'];
                    }
                    if ($row['Pagaie_ECA'] == '' or $row['Pagaie_ECA'] == 'PAGJ' or $row['Pagaie_ECA'] == 'PAGB') {
                        if ($row['Irreg'] != '') {
                            $row['Irreg'] .= ', ';
                        }
                        if ($row['Pagaie_ECA'] != '') {
                            $row['Irreg'] .= $row['Pagaie_ECA'];
                        } else {
                            $row['Irreg'] .= 'PAG ?';
                        }
                    }
                    if ($row['Etat_certificat_CK'] == 'NON') {
                        if ($row['Irreg'] != '') {
                            $row['Irreg'] .= ', ';
                        }
                        $row['Irreg'] .= 'Certif CK';
                    }
                    if ($row['Etat_certificat_APS'] == 'NON') {
                        if ($row['Irreg'] != '') {
                            $row['Irreg'] .= ', ';
                        }
                        $row['Irreg'] .= 'Certif APS';
                    }
                    array_push($arrayCJouees3, $row);
                }
                break;
            case 'CJoueesN' : // Matchs joués en Championnat de France (par équipe)
                $NomStat = 'Compétiteurs participants dans plusieurs équipes';
                $sql = "SELECT ce.Libelle nomEquipe, lc.Matric, lc.Nom, lc.Prenom, j.Code_competition Competition, COUNT(DISTINCT mj.Id_match) Nb_matchs ";
                $sql .= "FROM gickp_Matchs_Joueurs mj, gickp_Matchs m, gickp_Journees j, gickp_Liste_Coureur lc, gickp_Competitions_Equipes ce ";
                $sql .= "WHERE lc.Matric = mj.Matric ";
                $sql .= "AND mj.Capitaine NOT IN ('E','A','X') ";
                $sql .= "AND mj.Id_match = m.Id ";
                $sql .= "AND IF(mj.Equipe = 'A', m.Id_equipeA, m.Id_equipeB) = ce.Id ";
                $sql .= "AND m.Id_journee = j.Id ";
                $sql .= "AND j.Code_competition LIKE 'N%' ";
                $sql .= "AND j.Code_competition NOT LIKE 'NAS%' ";
                $sql .= "AND j.Code_saison = '$codeSaison' ";
                $sql .= "AND m.Date_match <= CURDATE() ";
                //$sql .= "AND m.Validation = 'O' ";
                $sql .= "GROUP BY nomEquipe, lc.Matric, j.Code_competition ";
                $sql .= "ORDER BY lc.Nom, lc.Prenom, Competition ";
                $sql .= "LIMIT 0,$nbLignes ";
                $result = mysql_query($sql, $myBdd->m_link) or die("Erreur Load : " . $sql);
                $num_results = mysql_num_rows($result);

                $arrayCJouees2 = array();
                for ($i = 0; $i < $num_results; $i++) {
                    $row = mysql_fetch_array($result);
                    array_push($arrayCJouees2, array('Competition' => $row['Competition'],
                        'Matric' => $row['Matric'],
                        'Nom' => $row['Nom'],
                        'Prenom' => $row['Prenom'],
                        'Numero_club' => $row['Numero_club'],
                        'nomEquipe' => $row['nomEquipe'],
                        'Nb_matchs' => $row['Nb_matchs']));
                }
                break;
            case 'CJoueesCF' : // Matchs joués en Coupe de France (par équipe)
                $NomStat = 'Compétiteurs participants dans plusieurs équipes';
                $sql = "SELECT ce.Libelle nomEquipe, lc.Matric, lc.Nom, lc.Prenom, j.Code_competition Competition, COUNT(DISTINCT mj.Id_match) Nb_matchs ";
                $sql .= "FROM gickp_Matchs_Joueurs mj, gickp_Matchs m, gickp_Journees j, gickp_Liste_Coureur lc, gickp_Competitions_Equipes ce ";
                $sql .= "WHERE lc.Matric = mj.Matric ";
                $sql .= "AND mj.Capitaine NOT IN ('E','A','X') ";
                $sql .= "AND mj.Id_match = m.Id ";
                $sql .= "AND IF(mj.Equipe = 'A', m.Id_equipeA, m.Id_equipeB) = ce.Id ";
                $sql .= "AND m.Id_journee = j.Id ";
                $sql .= "AND j.Code_competition LIKE 'CF%' ";
                $sql .= "AND j.Code_saison = '$codeSaison' ";
                $sql .= "AND m.Date_match <= CURDATE() ";
                //$sql .= "AND m.Validation = 'O' ";
                $sql .= "GROUP BY nomEquipe, mj.Matric, j.Code_competition ";
                $sql .= "ORDER BY lc.Nom, lc.Prenom, Competition ";
                $sql .= "LIMIT 0,$nbLignes ";
                $result = mysql_query($sql, $myBdd->m_link) or die("Erreur Load : " . $sql);
                $num_results = mysql_num_rows($result);

                $arrayCJouees2 = array();
                for ($i = 0; $i < $num_results; $i++) {
                    $row = mysql_fetch_array($result);
                    array_push($arrayCJouees2, array('Competition' => $row['Competition'],
                        'Matric' => $row['Matric'],
                        'Nom' => $row['Nom'],
                        'Prenom' => $row['Prenom'],
//								'Numero_club' => $row['Numero_club'],  
                        'nomEquipe' => $row['nomEquipe'],
                        'Nb_matchs' => $row['Nb_matchs']));
                }
                break;
            case 'OfficielsJournees' : // Officiels Journees
                $NomStat = 'Officiels par journée';
                $sql = "SELECT j.* ";
                $sql .= "FROM gickp_Journees j ";
                $sql .= "WHERE 1 ";
                $sql .= "AND j.Code_competition IN $CompetsList ";
                $sql .= "AND j.Code_saison = '$codeSaison' ";
                $sql .= "GROUP BY j.Code_competition, j.Date_debut, j.Lieu ";
                $sql .= "ORDER BY j.Code_competition, j.Date_debut, j.Lieu ";
                $sql .= "LIMIT 0,$nbLignes ";
                $result = mysql_query($sql, $myBdd->m_link) or die("Erreur Load : " . $sql);
                $num_results = mysql_num_rows($result);

                $arrayOfficielsJournees = array();
                for ($i = 0; $i < $num_results; $i++) {
                    $row = mysql_fetch_array($result);
                    array_push($arrayOfficielsJournees, array('Code_competition' => $row['Code_competition'],
                        'Date_debut' => $row['Date_debut'],
                        'Date_fin' => $row['Date_fin'],
                        'Lieu' => $row['Lieu'],
                        'Responsable_insc' => $row['Responsable_insc'],
                        'Responsable_R1' => $row['Responsable_R1'],
                        'Delegue' => $row['Delegue'],
                        'ChefArbitre' => $row['ChefArbitre']));
                }
                break;
        }

        // Entête PDF ...
        $pdf = new PDF('P');
        $pdf->Open();
        $pdf->SetTitle("Statistiques");

        $pdf->SetAuthor("Kayak-polo.info");
        $pdf->SetCreator("Kayak-polo.info avec FPDF");
        $pdf->SetAutoPageBreak(true, 15);
        if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($visuels['sponsor'])) {
            $pdf->SetAutoPageBreak(true, 30);
        } else {
            $pdf->SetAutoPageBreak(true, 15);
        }
        $pdf->AddPage();
        // Affichage
        // Bandeau
        if ($arrayCompetition['Bandeau_actif'] == 'O' && isset($visuels['bandeau'])) {
            $img = redimImage($visuels['bandeau'], 210, 10, 20, 'C');
            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
            // KPI + Logo    
        } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O' && $arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
            $pdf->Image('../img/logoKPI-small.jpg', 10, 10, 0, 20, 'jpg', "https://www.kayak-polo.info");
            $img = redimImage($visuels['logo'], 210, 10, 20, 'R');
            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
            // KPI
        } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O') {
            $pdf->Image('../img/logoKPI-small.jpg', 84, 10, 0, 20, 'jpg', "https://www.kayak-polo.info");
            // Logo
        } elseif ($arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
            $img = redimImage($visuels['logo'], 210, 10, 20, 'C');
            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
        }
        // Sponsor
        if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($visuels['sponsor'])) {
            $img = redimImage($visuels['sponsor'], 210, 10, 16, 'C');
            $pdf->Image($img['image'], $img['positionX'], 267, 0, $img['newHauteur']);
        }

        // titre
        if ($AfficheStat == 'CJoueesN') {
            $CompetsList2 = 'Championnat de France';
        } elseif ($AfficheStat == 'CJoueesCF') {
            $CompetsList2 = 'Coupe de France';
        }
        $pdf->Ln(19);
        $pdf->SetFont('Arial', 'BI', 11);
        $pdf->Cell(95, 6, $CompetsList2, 0, 0, 'L');
        $pdf->Cell(95, 6, 'Saison ' . $codeSaison, 0, 1, 'R');
        $pdf->SetFont('Arial', 'B', 14);

        $pdf->Cell(190, 7, "Stats : " . $NomStat, 0, 1, 'C');
        $pdf->Ln(3);

        switch ($AfficheStat) {
            case 'Buteurs' :
            default :
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(10, 7, '#', 'B', 0, 'C');
                $pdf->Cell(18, 7, 'Compet', 'B', 0, 'C');
                $pdf->Cell(8, 7, 'Num', 'B', 0, 'C');
                $pdf->Cell(40, 7, 'Nom', 'B', 0, 'C');
                $pdf->Cell(40, 7, 'Prénom', 'B', 0, 'C');
                $pdf->Cell(62, 7, 'Equipe', 'B', 0, 'C');
                $pdf->Cell(12, 7, 'Buts', 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 8);

                for ($i = 0; $i < count($arrayButeurs); $i++) {
                    $pdf->Cell(10, 7, $i + 1, 'B', 0, 'C');
                    $pdf->Cell(18, 7, $arrayButeurs[$i]['Competition'], 'B', 0, 'C');
                    $pdf->Cell(8, 7, $arrayButeurs[$i]['Numero'], 'B', 0, 'C');
                    $pdf->Cell(40, 7, $arrayButeurs[$i]['Nom'], 'B', 0, 'C');
                    $pdf->Cell(40, 7, $arrayButeurs[$i]['Prenom'], 'B', 0, 'C');
                    $pdf->Cell(62, 7, $arrayButeurs[$i]['Equipe'], 'B', 0, 'C');
                    $pdf->Cell(12, 7, $arrayButeurs[$i]['Buts'], 'B', 1, 'C');
                }
                break;
            case 'Attaque' :
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(30, 7, '', '', 0, 'C');
                $pdf->Cell(10, 7, '#', 'B', 0, 'C');
                $pdf->Cell(28, 7, 'Compétition', 'B', 0, 'C');
                $pdf->Cell(70, 7, 'Equipe', 'B', 0, 'C');
                $pdf->Cell(17, 7, 'Marqués', 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 8);

                for ($i = 0; $i < count($arrayAttaque); $i++) {
                    $pdf->Cell(30, 7, '', '', 0, 'C');
                    $pdf->Cell(10, 7, $i + 1, 'B', 0, 'C');
                    $pdf->Cell(28, 7, $arrayAttaque[$i]['Competition'], 'B', 0, 'C');
                    $pdf->Cell(70, 7, $arrayAttaque[$i]['Equipe'], 'B', 0, 'C');
                    $pdf->Cell(17, 7, $arrayAttaque[$i]['Buts'], 'B', 1, 'C');
                }
                break;
            case 'Defense' :
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(30, 7, '', '', 0, 'C');
                $pdf->Cell(10, 7, '#', 'B', 0, 'C');
                $pdf->Cell(28, 7, 'Compétition', 'B', 0, 'C');
                $pdf->Cell(70, 7, 'Equipe', 'B', 0, 'C');
                $pdf->Cell(17, 7, 'Concédés', 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 8);

                for ($i = 0; $i < count($arrayDefense); $i++) {
                    $pdf->Cell(30, 7, '', '', 0, 'C');
                    $pdf->Cell(10, 7, $i + 1, 'B', 0, 'C');
                    $pdf->Cell(28, 7, $arrayDefense[$i]['Competition'], 'B', 0, 'C');
                    $pdf->Cell(70, 7, $arrayDefense[$i]['Equipe'], 'B', 0, 'C');
                    $pdf->Cell(17, 7, $arrayDefense[$i]['Buts'], 'B', 1, 'C');
                }
                break;
            case 'Cartons' :
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(10, 7, '#', 'B', 0, 'C');
                $pdf->Cell(19, 7, 'Compet', 'B', 0, 'C');
                $pdf->Cell(8, 7, 'Num', 'B', 0, 'C');
                $pdf->Cell(37, 7, 'Nom', 'B', 0, 'C');
                $pdf->Cell(37, 7, 'Prénom', 'B', 0, 'C');
                $pdf->Cell(52, 7, 'Equipe', 'B', 0, 'C');
                $pdf->Cell(9, 7, 'V', 'B', 0, 'C');
                $pdf->Cell(9, 7, 'J', 'B', 0, 'C');
                $pdf->Cell(9, 7, 'R', 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 8);

                for ($i = 0; $i < count($arrayCartons); $i++) {
                    $pdf->Cell(10, 7, $i + 1, 'B', 0, 'C');
                    $pdf->Cell(19, 7, $arrayCartons[$i]['Competition'], 'B', 0, 'C');
                    $pdf->Cell(8, 7, $arrayCartons[$i]['Numero'], 'B', 0, 'C');
                    $pdf->Cell(37, 7, $arrayCartons[$i]['Nom'], 'B', 0, 'C');
                    $pdf->Cell(37, 7, $arrayCartons[$i]['Prenom'], 'B', 0, 'C');
                    $pdf->Cell(52, 7, $arrayCartons[$i]['Equipe'], 'B', 0, 'C');
                    $pdf->Cell(9, 7, $arrayCartons[$i]['Vert'], 'B', 0, 'C');
                    $pdf->Cell(9, 7, $arrayCartons[$i]['Jaune'], 'B', 0, 'C');
                    $pdf->Cell(9, 7, $arrayCartons[$i]['Rouge'], 'B', 1, 'C');
                }
                break;
            case 'CartonsEquipe' :
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(15, 7, '#', 'B', 0, 'C');
                $pdf->Cell(28, 7, 'Compet', 'B', 0, 'C');
                $pdf->Cell(90, 7, 'Equipe', 'B', 0, 'C');
                $pdf->Cell(19, 7, 'V', 'B', 0, 'C');
                $pdf->Cell(19, 7, 'J', 'B', 0, 'C');
                $pdf->Cell(19, 7, 'R', 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 8);

                for ($i = 0; $i < count($arrayCartonsEquipe); $i++) {
                    $pdf->Cell(15, 7, $i + 1, 'B', 0, 'C');
                    $pdf->Cell(28, 7, $arrayCartonsEquipe[$i]['Competition'], 'B', 0, 'C');
                    $pdf->Cell(90, 7, $arrayCartonsEquipe[$i]['Equipe'], 'B', 0, 'C');
                    $pdf->Cell(19, 7, $arrayCartonsEquipe[$i]['Vert'], 'B', 0, 'C');
                    $pdf->Cell(19, 7, $arrayCartonsEquipe[$i]['Jaune'], 'B', 0, 'C');
                    $pdf->Cell(19, 7, $arrayCartonsEquipe[$i]['Rouge'], 'B', 1, 'C');
                }
                break;
            case 'Fairplay' :
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(10, 7, '#', 'B', 0, 'C');
                $pdf->Cell(18, 7, 'Compet', 'B', 0, 'C');
                $pdf->Cell(8, 7, 'Num', 'B', 0, 'C');
                $pdf->Cell(40, 7, 'Nom', 'B', 0, 'C');
                $pdf->Cell(40, 7, 'Prénom', 'B', 0, 'C');
                $pdf->Cell(62, 7, 'Equipe', 'B', 0, 'C');
                $pdf->Cell(12, 7, 'Pts', 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 8);

                for ($i = 0; $i < count($arrayFairplay); $i++) {
                    $pdf->Cell(10, 7, $i + 1, 'B', 0, 'C');
                    $pdf->Cell(18, 7, $arrayFairplay[$i]['Competition'], 'B', 0, 'C');
                    $pdf->Cell(8, 7, $arrayFairplay[$i]['Numero'], 'B', 0, 'C');
                    $pdf->Cell(40, 7, $arrayFairplay[$i]['Nom'], 'B', 0, 'C');
                    $pdf->Cell(40, 7, $arrayFairplay[$i]['Prenom'], 'B', 0, 'C');
                    $pdf->Cell(62, 7, $arrayFairplay[$i]['Equipe'], 'B', 0, 'C');
                    $pdf->Cell(12, 7, $arrayFairplay[$i]['Fairplay'], 'B', 1, 'C');
                }
                break;
            case 'FairplayEquipe' :
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(30, 7, '', '', 0, 'C');
                $pdf->Cell(10, 7, '#', 'B', 0, 'C');
                $pdf->Cell(28, 7, 'Compétition', 'B', 0, 'C');
                $pdf->Cell(70, 7, 'Equipe', 'B', 0, 'C');
                $pdf->Cell(12, 7, 'Pts', 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 8);

                for ($i = 0; $i < count($arrayFairplayEquipe); $i++) {
                    $pdf->Cell(30, 7, '', '', 0, 'C');
                    $pdf->Cell(10, 7, $i + 1, 'B', 0, 'C');
                    $pdf->Cell(28, 7, $arrayFairplayEquipe[$i]['Competition'], 'B', 0, 'C');
                    $pdf->Cell(70, 7, $arrayFairplayEquipe[$i]['Equipe'], 'B', 0, 'C');
                    $pdf->Cell(12, 7, $arrayFairplayEquipe[$i]['Fairplay'], 'B', 1, 'C');
                }
                break;
            case 'Arbitrage' :
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(21, 7, '', '', 0, 'C');
                $pdf->Cell(10, 7, '#', 'B', 0, 'C');
                $pdf->Cell(18, 7, 'Compet', 'B', 0, 'C');
                $pdf->Cell(40, 7, 'Nom', 'B', 0, 'C');
                $pdf->Cell(40, 7, 'Prénom', 'B', 0, 'C');
//				$pdf->Cell(51,7,'Equipe','B',0,'C');
                $pdf->Cell(13, 7, 'Princ.', 'B', 0, 'C');
                $pdf->Cell(13, 7, 'Sec.', 'B', 0, 'C');
                $pdf->Cell(13, 7, 'Total', 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 8);

                for ($i = 0; $i < count($arrayArbitrage); $i++) {
                    $pdf->Cell(21, 7, '', '', 0, 'C');
                    $pdf->Cell(10, 7, $i + 1, 'B', 0, 'C');
                    $pdf->Cell(18, 7, $arrayArbitrage[$i]['Competition'], 'B', 0, 'C');
                    $pdf->Cell(40, 7, $arrayArbitrage[$i]['Nom'], 'B', 0, 'C');
                    $pdf->Cell(40, 7, $arrayArbitrage[$i]['Prenom'], 'B', 0, 'C');
//					$pdf->Cell(51,7,$arrayArbitrage[$i]['Equipe'],'B',0,'C');
                    $pdf->Cell(13, 7, $arrayArbitrage[$i]['Principal'], 'B', 0, 'C');
                    $pdf->Cell(13, 7, $arrayArbitrage[$i]['Secondaire'], 'B', 0, 'C');
                    $pdf->Cell(13, 7, $arrayArbitrage[$i]['Total'], 'B', 1, 'C');
                }
                break;
            case 'ArbitrageEquipe' :
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(30, 7, '', '', 0, 'C');
                $pdf->Cell(10, 7, '#', 'B', 0, 'C');
                $pdf->Cell(19, 7, 'Compet', 'B', 0, 'C');
                $pdf->Cell(60, 7, 'Equipe', 'B', 0, 'C');
                $pdf->Cell(13, 7, 'Princ.', 'B', 0, 'C');
                $pdf->Cell(13, 7, 'Sec.', 'B', 0, 'C');
                $pdf->Cell(13, 7, 'Total', 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 8);

                for ($i = 0; $i < count($arrayArbitrageEquipe); $i++) {
                    $pdf->Cell(30, 7, '', '', 0, 'C');
                    $pdf->Cell(10, 7, $i + 1, 'B', 0, 'C');
                    $pdf->Cell(19, 7, $arrayArbitrageEquipe[$i]['Competition'], 'B', 0, 'C');
                    $pdf->Cell(60, 7, $arrayArbitrageEquipe[$i]['Equipe'], 'B', 0, 'C');
                    $pdf->Cell(13, 7, $arrayArbitrageEquipe[$i]['Principal'], 'B', 0, 'C');
                    $pdf->Cell(13, 7, $arrayArbitrageEquipe[$i]['Secondaire'], 'B', 0, 'C');
                    $pdf->Cell(13, 7, $arrayArbitrageEquipe[$i]['Total'], 'B', 1, 'C');
                }
                break;
            case 'CJouees' :
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(10, 7, '#', 'B', 0, 'C');
                $pdf->Cell(18, 7, 'Compet', 'B', 0, 'C');
                $pdf->Cell(40, 7, 'Nom', 'B', 0, 'C');
                $pdf->Cell(40, 7, 'Prénom', 'B', 0, 'C');
                $pdf->Cell(70, 7, 'Equipe', 'B', 0, 'C');
                $pdf->Cell(12, 7, 'Nb', 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 8);

                for ($i = 0; $i < count($arrayCJouees); $i++) {
                    $pdf->Cell(10, 7, $i + 1, 'B', 0, 'C');
                    $pdf->Cell(18, 7, $arrayCJouees[$i]['Competition'], 'B', 0, 'C');
                    $pdf->Cell(40, 7, $arrayCJouees[$i]['Nom'], 'B', 0, 'C');
                    $pdf->Cell(40, 7, $arrayCJouees[$i]['Prenom'], 'B', 0, 'C');
                    $pdf->Cell(70, 7, $arrayCJouees[$i]['Nom_club'], 'B', 0, 'C');
                    $pdf->Cell(12, 7, $arrayCJouees[$i]['Nb_matchs'], 'B', 1, 'C');
                }
                break;
            case 'CJouees2' :
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(10, 7, '#', 'B', 0, 'C');
                $pdf->Cell(18, 7, 'Compet', 'B', 0, 'C');
                $pdf->Cell(40, 7, 'Nom', 'B', 0, 'C');
                $pdf->Cell(40, 7, 'Prénom', 'B', 0, 'C');
                $pdf->Cell(70, 7, 'Equipe', 'B', 0, 'C');
                $pdf->Cell(12, 7, 'Nb', 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 8);

                for ($i = 0; $i < count($arrayCJouees2); $i++) {
                    $pdf->Cell(10, 7, $i + 1, 'B', 0, 'C');
                    $pdf->Cell(18, 7, $arrayCJouees2[$i]['Competition'], 'B', 0, 'C');
                    $pdf->Cell(40, 7, $arrayCJouees2[$i]['Nom'], 'B', 0, 'C');
                    $pdf->Cell(40, 7, $arrayCJouees2[$i]['Prenom'], 'B', 0, 'C');
                    $pdf->Cell(70, 7, $arrayCJouees2[$i]['nomEquipe'], 'B', 0, 'C');
                    $pdf->Cell(12, 7, $arrayCJouees2[$i]['Nb_matchs'], 'B', 1, 'C');
                }
                break;
            case 'CJouees3' :
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(8, 7, '#', 'B', 0, 'C');
                $pdf->Cell(15, 7, 'Compet', 'B', 0, 'C');
                $pdf->Cell(35, 7, 'Nom', 'B', 0, 'C');
                $pdf->Cell(35, 7, 'Prénom', 'B', 0, 'C');
                $pdf->Cell(60, 7, 'Equipe', 'B', 0, 'C');
                $pdf->Cell(35, 7, 'Irréglarité', 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 8);

                for ($i = 0; $i < count($arrayCJouees3); $i++) {
                    $pdf->Cell(8, 7, $i + 1, 'B', 0, 'C');
                    $pdf->Cell(15, 7, $arrayCJouees3[$i]['Competition'], 'B', 0, 'C');
                    $pdf->Cell(35, 7, $arrayCJouees3[$i]['Nom'], 'B', 0, 'C');
                    $pdf->Cell(35, 7, $arrayCJouees3[$i]['Prenom'], 'B', 0, 'C');
                    $pdf->Cell(60, 7, $arrayCJouees3[$i]['nomEquipe'], 'B', 0, 'C');
                    $pdf->Cell(35, 7, $arrayCJouees3[$i]['Irreg'], 'B', 1, 'C');
                }
                break;
            case 'CJoueesN' :
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(10, 7, '#', 'B', 0, 'C');
                $pdf->Cell(18, 7, 'Compet', 'B', 0, 'C');
                $pdf->Cell(40, 7, 'Nom', 'B', 0, 'C');
                $pdf->Cell(40, 7, 'Prénom', 'B', 0, 'C');
                $pdf->Cell(70, 7, 'Equipe', 'B', 0, 'C');
                $pdf->Cell(12, 7, 'Nb', 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 8);
                $k = 0;
                for ($i = 0; $i < count($arrayCJouees2); $i++) {
                    $h = $i - 1;
                    $j = $i + 1;
                    $encadrement = 'T';
                    if (($arrayCJouees2[$i]['Matric'] == $arrayCJouees2[$h]['Matric'] && $arrayCJouees2[$i]['nomEquipe'] != $arrayCJouees2[$h]['nomEquipe']) || ($arrayCJouees2[$i]['Matric'] == $arrayCJouees2[$j]['Matric'] && $arrayCJouees2[$i]['nomEquipe'] != $arrayCJouees2[$j]['nomEquipe'])) {
                        $k++;
                        if ($arrayCJouees2[$i]['Matric'] == $matric) {
                            $encadrement = '';
                            $arrayCJouees2[$i]['Nom'] = '';
                            $arrayCJouees2[$i]['Prenom'] = '';
                        }
                        $matric = $arrayCJouees2[$i]['Matric'];
                        $pdf->Cell(10, 7, $k, 'B', 0, 'C');
                        $pdf->Cell(18, 7, $arrayCJouees2[$i]['Competition'], 'B', 0, 'C');
                        $pdf->Cell(40, 7, $arrayCJouees2[$i]['Nom'], $encadrement, 0, 'C');
                        $pdf->Cell(40, 7, $arrayCJouees2[$i]['Prenom'], $encadrement, 0, 'C');
                        $pdf->Cell(70, 7, $arrayCJouees2[$i]['nomEquipe'], 'B', 0, 'C');
                        $pdf->Cell(12, 7, $arrayCJouees2[$i]['Nb_matchs'], 'B', 1, 'C');
                    }
                }
                break;
            case 'CJoueesCF' :
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(10, 7, '#', 'B', 0, 'C');
                $pdf->Cell(18, 7, 'Compet', 'B', 0, 'C');
                $pdf->Cell(40, 7, 'Nom', 'B', 0, 'C');
                $pdf->Cell(40, 7, 'Prénom', 'B', 0, 'C');
                $pdf->Cell(70, 7, 'Equipe', 'B', 0, 'C');
                $pdf->Cell(12, 7, 'Nb', 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 8);
                $k = 0;
                for ($i = 0; $i < count($arrayCJouees2); $i++) {
                    $h = $i - 1;
                    $j = $i + 1;
                    $encadrement = 'T';
                    if (($arrayCJouees2[$i]['Matric'] == $arrayCJouees2[$h]['Matric'] && $arrayCJouees2[$i]['nomEquipe'] != $arrayCJouees2[$h]['nomEquipe']) || ($arrayCJouees2[$i]['Matric'] == $arrayCJouees2[$j]['Matric'] && $arrayCJouees2[$i]['nomEquipe'] != $arrayCJouees2[$j]['nomEquipe'])) {
                        $k++;
                        if ($arrayCJouees2[$i]['Matric'] == $matric) {
                            $encadrement = '';
                            $arrayCJouees2[$i]['Nom'] = '';
                            $arrayCJouees2[$i]['Prenom'] = '';
                        }
                        $matric = $arrayCJouees2[$i]['Matric'];
                        $pdf->Cell(10, 7, $k, 'B', 0, 'C');
                        $pdf->Cell(18, 7, $arrayCJouees2[$i]['Competition'], 'B', 0, 'C');
                        $pdf->Cell(40, 7, $arrayCJouees2[$i]['Nom'], $encadrement, 0, 'C');
                        $pdf->Cell(40, 7, $arrayCJouees2[$i]['Prenom'], $encadrement, 0, 'C');
                        $pdf->Cell(70, 7, $arrayCJouees2[$i]['nomEquipe'], 'B', 0, 'C');
                        $pdf->Cell(12, 7, $arrayCJouees2[$i]['Nb_matchs'], 'B', 1, 'C');
                    }
                }
                break;
            case 'OfficielsJournees' :
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(10, 7, 'Compet', 'B', 0, 'C');
                $pdf->Cell(20, 7, 'Date', 'B', 0, 'C');
                $pdf->Cell(30, 7, 'Lieu', 'B', 0, 'C');
                $pdf->Cell(30, 7, 'RC', 'B', 0, 'C');
                $pdf->Cell(30, 7, 'R1', 'B', 0, 'C');
                $pdf->Cell(30, 7, 'Délégué', 'B', 0, 'C');
                $pdf->Cell(30, 7, 'Chef arb.', 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 8);
                $k = 0;
                for ($i = 0; $i < count($arrayOfficielsJournees); $i++) {
                    $pdf->Cell(10, 7, $arrayOfficielsJournees[$i]['Code_competition'], 'B', 0, 'C');
                    $pdf->Cell(20, 7, $arrayOfficielsJournees[$i]['Date_debut'], 'B', 0, 'C');
                    $pdf->Cell(30, 7, $arrayOfficielsJournees[$i]['Lieu'], 'B', 0, 'C');
                    $pdf->Cell(30, 7, $arrayOfficielsJournees[$i]['Responsable_insc'], 'B', 0, 'C');
                    $pdf->Cell(30, 7, $arrayOfficielsJournees[$i]['Responsable_R1'], 'B', 0, 'C');
                    $pdf->Cell(30, 7, $arrayOfficielsJournees[$i]['Delegue'], 'B', 0, 'C');
                    $pdf->Cell(30, 7, $arrayOfficielsJournees[$i]['ChefArbitre'], 'B', 1, 'C');
                }
                break;
        }

        $pdf->Output('Statistiques_' . $AfficheStat . '.pdf', 'I');
    }

}

$page = new FeuilleStats();
