<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

require('../fpdf/fpdf.php');

// Pieds de page
class PDF extends FPDF
{

    function Footer()
    {
        //Positionnement à 1,5 cm du bas
        $this->SetY(-15);
        //Police Arial italique 8
        $this->SetFont('Arial', 'I', 8);
        //Numéro de page centré
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

// Liste des Matchs d'une Journee ou d'un Evenement 
class FeuilleStats extends MyPage
{

    function __construct()
    {
        MyPage::MyPage();

        $myBdd = new MyBdd();

        //Saison
        $codeSaison = utyGetSession('codeSaison', $myBdd->GetActiveSaison());

        //CompetitionS selectionnées
        $Compets = utyGetSession('Compets', '');
        $CompetsList = @implode(',', $Compets);
        $CompetsList = utyGetGet('Compets', $CompetsList);
        $Compets = explode(',', $CompetsList);
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

        $arrayStats = array();
        $in = str_repeat('?,', count($Compets) - 1) . '?';
        // Type Stats
        switch ($AfficheStat) {
            case 'Buteurs':
            default:
                $sql  = "SELECT d.Code_competition Competition, a.Matric Licence, a.Nom, a.Prenom, 
                    a.Sexe, b.Numero, f.Libelle Equipe, COUNT(*) Buts 
                    FROM kp_licence a, kp_match_detail b, kp_match c, 
                    kp_journee d, kp_competition_equipe f 
                    WHERE a.Matric = b.Competiteur 
                    AND b.Id_match = c.Id 
                    AND c.Id_journee = d.Id 
                    AND d.Code_competition = f.Code_compet 
                    AND d.Code_saison = f.Code_saison 
                    AND f.Id = IF(b.Equipe_A_B='A',c.Id_equipeA, c.Id_equipeB) 
                    AND d.Code_competition IN ($in) 
                    AND d.Code_saison = ? 
                    AND b.Id_evt_match = 'B' 
                    GROUP BY a.Matric 
                    ORDER BY Buts DESC, a.Nom 
                    LIMIT 0, $nbLignes ";
                $result = $myBdd->pdo->prepare($sql);
                $result->execute(array_merge($Compets, [$codeSaison]));
                while ($row = $result->fetch()) {
                    array_push($arrayStats, array(
                        'Competition' => $row['Competition'],
                        'Licence' => $row['Licence'],
                        'Nom' => mb_strtoupper($row['Nom']),
                        'Prenom' => mb_convert_case(strtolower($row['Prenom']), MB_CASE_TITLE, "UTF-8"),
                        'Sexe' => $row['Sexe'],
                        'Numero' => $row['Numero'],
                        'Equipe' => $row['Equipe'],
                        'Buts' => $row['Buts']
                    ));
                }
                break;
            case 'Attaque':
                $sql  = "SELECT d.Code_competition Competition, f.Libelle Equipe, COUNT(*) Buts 
                    FROM kp_match_detail b, kp_match c, kp_journee d, 
                    kp_competition_equipe f 
                    WHERE b.Id_match = c.Id 
                    AND c.Id_journee = d.Id 
                    AND d.Code_competition = f.Code_compet 
                    AND d.Code_saison = f.Code_saison 
                    AND f.Id = IF(b.Equipe_A_B='A',c.Id_equipeA, c.Id_equipeB) 
                    AND d.Code_competition IN ($in) 
                    AND d.Code_saison = ? 
                    AND b.Id_evt_match = 'B' 
                    GROUP BY Equipe 
                    ORDER BY Buts DESC, Equipe 
                    LIMIT 0,$nbLignes ";
                $result = $myBdd->pdo->prepare($sql);
                $result->execute(array_merge($Compets, [$codeSaison]));
                while ($row = $result->fetch()) {
                    array_push($arrayStats, array(
                        'Competition' => $row['Competition'],
                        'Equipe' => $row['Equipe'],
                        'Buts' => $row['Buts']
                    ));
                }
                break;
            case 'Defense':
                $sql = "SELECT d.Code_competition Competition, f.Libelle Equipe, COUNT(*) Buts 
                    FROM kp_match_detail b, kp_match c, kp_journee d, 
                    kp_competition_equipe f 
                    WHERE b.Id_match = c.Id 
                    AND c.Id_journee = d.Id 
                    AND d.Code_competition = f.Code_compet 
                    AND d.Code_saison = f.Code_saison 
                    AND f.Id = IF(b.Equipe_A_B='B',c.Id_equipeA, c.Id_equipeB) 
                    AND d.Code_competition IN ($in) 
                    AND d.Code_saison = ? 
                    AND b.Id_evt_match = 'B' 
                    GROUP BY Equipe 
                    ORDER BY Buts ASC, Equipe 
                    LIMIT 0,$nbLignes ";
                $result = $myBdd->pdo->prepare($sql);
                $result->execute(array_merge($Compets, [$codeSaison]));
                while ($row = $result->fetch()) {
                    array_push($arrayStats, array(
                        'Competition' => $row['Competition'],
                        'Equipe' => $row['Equipe'],
                        'Buts' => $row['Buts']
                    ));
                }
                break;
            case 'Cartons':
                $sql = "SELECT d.Code_competition Competition, a.Matric Licence, a.Nom, 
                    a.Prenom, a.Sexe, b.Numero, f.Libelle Equipe, 
                    SUM(IF(b.Id_evt_match='V',1,0)) Vert, 
                    SUM(IF(b.Id_evt_match='J',1,0)) Jaune, 
                    SUM(IF(b.Id_evt_match='R',1,0)) Rouge 
                    FROM kp_licence a, kp_match_detail b, kp_match c, 
                    kp_journee d, kp_competition_equipe f 
                    WHERE a.Matric = b.Competiteur 
                    AND b.Id_match = c.Id 
                    AND c.Id_journee = d.Id 
                    AND d.Code_competition = f.Code_compet 
                    AND d.Code_saison = f.Code_saison 
                    AND f.Id = IF(b.Equipe_A_B='A',c.Id_equipeA, c.Id_equipeB) 
                    AND d.Code_competition IN ($in) 
                    AND d.Code_saison = ? 
                    AND b.Id_evt_match IN ('V','J','R') 
                    GROUP BY a.Matric 
                    ORDER BY Rouge DESC, Jaune DESC, Vert DESC, Equipe, a.Nom 
                    LIMIT 0,$nbLignes ";
                $result = $myBdd->pdo->prepare($sql);
                $result->execute(array_merge($Compets, [$codeSaison]));
                while ($row = $result->fetch()) {
                    array_push($arrayStats, array(
                        'Competition' => $row['Competition'],
                        'Licence' => $row['Licence'],
                        'Nom' => mb_strtoupper($row['Nom']),
                        'Prenom' => mb_convert_case(strtolower($row['Prenom']), MB_CASE_TITLE, "UTF-8"),
                        'Sexe' => $row['Sexe'],
                        'Numero' => $row['Numero'],
                        'Equipe' => $row['Equipe'],
                        'Vert' => $row['Vert'],
                        'Jaune' => $row['Jaune'],
                        'Rouge' => $row['Rouge']
                    ));
                }
                break;
            case 'CartonsEquipe':
                $sql = "SELECT d.Code_competition Competition, f.Libelle Equipe, 
                    SUM(IF(b.Id_evt_match='V',1,0)) Vert, 
                    SUM(IF(b.Id_evt_match='J',1,0)) Jaune, 
                    SUM(IF(b.Id_evt_match='R',1,0)) Rouge 
                    FROM kp_match_detail b, kp_match c, kp_journee d, 
                    kp_competition_equipe f 
                    WHERE b.Id_match = c.Id 
                    AND c.Id_journee = d.Id 
                    AND d.Code_competition = f.Code_compet 
                    AND d.Code_saison = f.Code_saison 
                    AND d.Code_competition IN ($in) 
                    AND d.Code_saison = ? 
                    AND f.Id = IF(b.Equipe_A_B='A',c.Id_equipeA, c.Id_equipeB) 
                    AND b.Id_evt_match IN ('V','J','R') 
                    GROUP BY Equipe 
                    ORDER BY Rouge Desc, Jaune Desc, Vert Desc, Equipe 
                    LIMIT 0,$nbLignes ";
                $result = $myBdd->pdo->prepare($sql);
                $result->execute(array_merge($Compets, [$codeSaison]));
                while ($row = $result->fetch()) {
                    array_push($arrayStats, array(
                        'Competition' => $row['Competition'],
                        'Equipe' => $row['Equipe'],
                        'Vert' => $row['Vert'],
                        'Jaune' => $row['Jaune'],
                        'Rouge' => $row['Rouge']
                    ));
                }
                break;
            case 'CartonsCompetition':
                $sql = "SELECT d.Code_competition Competition, 
                    SUM(IF(b.Id_evt_match='B',1,0)) Buts, 
                    SUM(IF(b.Id_evt_match='V',1,0)) Vert, 
                    SUM(IF(b.Id_evt_match='J',1,0)) Jaune, 
                    SUM(IF(b.Id_evt_match='R',1,0)) Rouge 
                    FROM kp_match_detail b, kp_match c, kp_journee d, 
                    kp_competition_equipe f 
                    WHERE b.Id_match = c.Id 
                    AND c.Id_journee = d.Id 
                    AND d.Code_competition = f.Code_compet 
                    AND d.Code_saison = f.Code_saison 
                    AND d.Code_competition IN ($in) 
                    AND d.Code_saison = ? 
                    AND f.Id = IF(b.Equipe_A_B='A', c.Id_equipeA, c.Id_equipeB) 
                    AND b.Id_evt_match IN ('B','V','J','R') 
                    GROUP BY Code_competition 
                    ORDER BY Rouge Desc, Jaune Desc, Vert Desc, Code_competition 
                    LIMIT 0,$nbLignes ";
                $result = $myBdd->pdo->prepare($sql);
                $result->execute(array_merge($Compets, [$codeSaison]));
                while ($row = $result->fetch()) {
                    array_push($arrayStats, array(
                        'Competition' => $row['Competition'],
                        'Buts' => $row['Buts'],
                        'Vert' => $row['Vert'],
                        'Jaune' => $row['Jaune'],
                        'Rouge' => $row['Rouge']
                    ));
                }
                break;
            case 'Fairplay':
                $sql = "SELECT d.Code_competition Competition, a.Matric Licence, a.Nom, 
                    a.Prenom, a.Sexe, b.Numero, f.Libelle Equipe, 
                    SUM(IF(b.Id_evt_match='V',1, IF(b.Id_evt_match='J',2, 
                        IF(b.Id_evt_match='R',4,0)))) Fairplay 
                    FROM kp_licence a, kp_match_detail b, 
                    kp_match c, kp_journee d, kp_competition_equipe f 
                    WHERE a.Matric = b.Competiteur 
                    AND b.Id_match = c.Id 
                    AND c.Id_journee = d.Id 
                    AND d.Code_competition = f.Code_compet 
                    AND d.Code_saison = f.Code_saison 
                    AND d.Code_competition IN ($in) 
                    AND d.Code_saison = ? 
                    AND f.Id = IF(b.Equipe_A_B='A',c.Id_equipeA, c.Id_equipeB) 
                    AND b.Id_evt_match IN ('V','J','R') 
                    GROUP BY a.Matric 
                    ORDER BY Fairplay Desc, a.Nom 
                    LIMIT 0,$nbLignes ";
                $result = $myBdd->pdo->prepare($sql);
                $result->execute(array_merge($Compets, [$codeSaison]));
                while ($row = $result->fetch()) {
                    array_push($arrayStats, array(
                        'Competition' => $row['Competition'],
                        'Licence' => $row['Licence'],
                        'Nom' => mb_strtoupper($row['Nom']),
                        'Prenom' => mb_convert_case(strtolower($row['Prenom']), MB_CASE_TITLE, "UTF-8"),
                        'Sexe' => $row['Sexe'],
                        'Numero' => $row['Numero'],
                        'Equipe' => $row['Equipe'],
                        'Fairplay' => $row['Fairplay']
                    ));
                }
                break;
            case 'FairplayEquipe':
                $sql = "SELECT d.Code_competition Competition, f.Libelle Equipe, 
                    SUM(IF(b.Id_evt_match='V',1, IF(b.Id_evt_match='J',2, 
                        IF(b.Id_evt_match='R',4,0)))) Fairplay 
                    FROM kp_match_detail b, kp_match c, kp_journee d, 
                    kp_competition_equipe f 
                    WHERE b.Id_match = c.Id 
                    AND c.Id_journee = d.Id 
                    AND d.Code_competition = f.Code_compet 
                    AND d.Code_saison = f.Code_saison 
                    AND d.Code_competition IN ($in) 
                    AND d.Code_saison = ? 
                    AND f.Id = IF(b.Equipe_A_B='A',c.Id_equipeA, c.Id_equipeB) 
                    AND b.Id_evt_match IN ('V','J','R') 
                    GROUP BY Equipe 
                    ORDER BY Fairplay Desc, Equipe 
                    LIMIT 0,$nbLignes ";
                $result = $myBdd->pdo->prepare($sql);
                $result->execute(array_merge($Compets, [$codeSaison]));
                while ($row = $result->fetch()) {
                    array_push($arrayStats, array(
                        'Competition' => $row['Competition'],
                        'Equipe' => $row['Equipe'],
                        'Fairplay' => $row['Fairplay']
                    ));
                }
                break;
            case 'Arbitrage':
                $sql = "SELECT j.Code_competition Competition, a.Matric Licence, lc.Nom, 
                    lc.Prenom, lc.Sexe, c.Code Code_club, c.Libelle Club, a.arbitre, a.niveau, 
                    a.saison, a.livret, 
                    SUM(IF(m.Matric_arbitre_principal=a.Matric,1,0)) principal, 
                    SUM(IF(m.Matric_arbitre_secondaire=a.Matric,1,0)) secondaire, 
                    COUNT(*) Total 
                    FROM kp_licence lc, kp_arbitre a, kp_club c, 
                    kp_match m, kp_journee j 
                    WHERE 1 
                    AND a.Matric = lc.Matric 
                    AND c.Code = lc.Numero_club 
                    AND m.Id_journee = j.Id 
                    AND j.Code_competition IN ($in) 
                    AND j.Code_saison = ? 
                    AND (m.Matric_arbitre_principal = a.Matric 
                        OR m.Matric_arbitre_secondaire = a.Matric) 
                    GROUP BY Licence 
                    ORDER BY Total DESC, principal DESC, lc.Nom 
                    LIMIT 0,$nbLignes ";
                $result = $myBdd->pdo->prepare($sql);
                $result->execute(array_merge($Compets, [$codeSaison]));
                while ($row = $result->fetch()) {
                    array_push($arrayStats, array(
                        'Competition' => $row['Competition'],
                        'Licence' => $row['Licence'],
                        'Nom' => mb_strtoupper($row['Nom']),
                        'Prenom' => mb_convert_case(strtolower($row['Prenom']), MB_CASE_TITLE, "UTF-8"),
                        'Sexe' => $row['Sexe'],
                        'Principal' => $row['principal'],
                        'Secondaire' => $row['secondaire'],
                        'Total' => $row['Total']
                    ));
                }
                break;
            case 'ArbitrageEquipe':
                $sql = "SELECT d.Code_competition Competition, f.Libelle Equipe, 
                    SUM(IF((c.Arbitre_principal=f.Libelle) 
                        OR (c.Arbitre_principal LIKE CONCAT('%',f.Libelle,')%')),1,0)) principal, 
                    SUM(IF((c.Arbitre_secondaire=f.Libelle) 
                        OR (c.Arbitre_secondaire LIKE CONCAT('%',f.Libelle,')%')),1,0)) secondaire 
                    FROM kp_match c, kp_journee d, kp_competition_equipe f 
                    WHERE c.Id_journee = d.Id 
                    AND d.Code_competition = f.Code_compet 
                    AND d.Code_saison = f.Code_saison 
                    AND d.Code_competition IN ($in) 
                    AND d.Code_saison = ? 
                    GROUP BY Equipe 
                    ORDER BY principal DESC, secondaire DESC, Equipe 
                    LIMIT 0,$nbLignes ";
                $result = $myBdd->pdo->prepare($sql);
                $result->execute(array_merge($Compets, [$codeSaison]));
                while ($row = $result->fetch()) {
                    array_push($arrayStats, array(
                        'Competition' => $row['Competition'],
                        'Equipe' => $row['Equipe'],
                        'Principal' => $row['principal'],
                        'Secondaire' => $row['secondaire'],
                        'Total' => $row['principal'] + $row['secondaire']
                    ));
                }
                //array_multisort($arrayArbitrageEquipe[3], SORT_DESC, $arrayArbitrageEquipe);
                break;
            case 'CJouees': // Compétitions jouées dans la saison en cours (par clubs)
                $sql = "SELECT lc.Matric, lc.Nom, lc.Prenom, lc.Numero_club, clubs.Libelle Nom_club, 
                    j.Code_competition Competition, COUNT(DISTINCT mj.Id_match) Nb_matchs 
                    FROM kp_match_joueur mj, kp_match m, kp_journee j, 
                    kp_licence lc, kp_club clubs 
                    WHERE lc.Matric = mj.Matric 
                    AND mj.Capitaine NOT IN ('E','A','X') 
                    AND lc.Numero_club = clubs.Code 
                    AND mj.Id_match = m.Id 
                    AND m.Id_journee = j.Id 
                    AND j.Code_competition IN ($in) 
                    AND j.Code_saison = ? 
                    AND m.Date_match <= CURDATE() 
                    AND m.Validation = 'O' 
                    GROUP BY mj.Matric, j.Code_competition 
                    ORDER BY lc.Numero_club, lc.Nom, lc.Prenom, Competition DESC 
                    LIMIT 0,$nbLignes ";
                $result = $myBdd->pdo->prepare($sql);
                $result->execute(array_merge($Compets, [$codeSaison]));
                while ($row = $result->fetch()) {
                    array_push($arrayStats, array(
                        'Competition' => $row['Competition'],
                        'Matric' => $row['Matric'],
                        'Nom' => mb_strtoupper($row['Nom']),
                        'Prenom' => mb_convert_case(strtolower($row['Prenom']), MB_CASE_TITLE, "UTF-8"),
                        'Numero_club' => $row['Numero_club'],
                        'Nom_club' => $row['Nom_club'],
                        'Nb_matchs' => $row['Nb_matchs']
                    ));
                }
                break;
            case 'CJouees2': // Compétitions jouées dans la saison en cours (par équipe)
                $sql = "SELECT ce.Libelle nomEquipe, lc.Matric, lc.Nom, lc.Prenom, 
                    j.Code_competition Competition, COUNT(DISTINCT mj.Id_match) Nb_matchs 
                    FROM kp_match_joueur mj, kp_match m, kp_journee j, 
                    kp_licence lc, kp_competition_equipe ce 
                    WHERE lc.Matric = mj.Matric 
                    AND mj.Capitaine NOT IN ('E','A','X') 
                    AND mj.Id_match = m.Id 
                    AND IF(mj.Equipe = 'A', m.Id_equipeA, m.Id_equipeB) = ce.Id 
                    AND m.Id_journee = j.Id 
                    AND j.Code_competition IN ($in) 
                    AND j.Code_saison = ? 
                    AND m.Date_match <= CURDATE() 
                    AND m.Validation = 'O' 
                    GROUP BY nomEquipe, mj.Matric, j.Code_competition 
                    ORDER BY lc.Nom, lc.Prenom, Competition 
                    LIMIT 0,$nbLignes ";
                $result = $myBdd->pdo->prepare($sql);
                $result->execute(array_merge($Compets, [$codeSaison]));
                while ($row = $result->fetch()) {
                    array_push($arrayStats, array(
                        'Competition' => $row['Competition'],
                        'Matric' => $row['Matric'],
                        'Nom' => mb_strtoupper($row['Nom']),
                        'Prenom' => mb_convert_case(strtolower($row['Prenom']), MB_CASE_TITLE, "UTF-8"),
                        'nomEquipe' => $row['nomEquipe'],
                        'Nb_matchs' => $row['Nb_matchs']
                    ));
                }
                break;
            case 'CJouees3': // Irrégularités
                $sql = "SELECT ce.Libelle nomEquipe, lc.Matric, lc.Nom, lc.Prenom, 
                    j.Code_competition Competition, COUNT(DISTINCT mj.Id_match) Nb_matchs, 
                    lc.Origine, lc.Pagaie_ECA, lc.Etat_certificat_CK, lc.Etat_certificat_APS 
                    FROM kp_match_joueur mj, kp_match m, kp_journee j, 
                    kp_licence lc, kp_competition_equipe ce 
                    WHERE lc.Matric = mj.Matric 
                    AND mj.Capitaine NOT IN ('E','A','X') 
                    AND mj.Id_match = m.Id 
                    AND IF(mj.Equipe = 'A', m.Id_equipeA, m.Id_equipeB) = ce.Id 
                    AND m.Id_journee = j.Id 
                    AND j.Code_competition IN ($in) 
                    AND j.Code_saison = ? 
                    AND m.Date_match <= CURDATE() 
                    AND m.Validation = 'O' 
                    AND (lc.Origine <> ? 
                        OR lc.Pagaie_ECA = '' OR lc.Pagaie_ECA = 'PAGJ' 
                        OR lc.Pagaie_ECA = 'PAGB' OR lc.Etat_certificat_CK = 'NON') 
                    GROUP BY nomEquipe, mj.Matric, j.Code_competition 
                    ORDER BY lc.Nom, lc.Prenom, Competition 
                    LIMIT 0,$nbLignes ";
                $result = $myBdd->pdo->prepare($sql);
                $result->execute(array_merge($Compets, [$codeSaison], [$codeSaison]));
                while ($row = $result->fetch()) {
                    $row['Irreg'] = '';
                    if ($row['Origine'] != $codeSaison) {
                        $row['Irreg'] = 'Licence ' . $row['Origine'];
                    }
                    if ($row['Pagaie_ECA'] == '' or $row['Pagaie_ECA'] == 'PAGJ' or $row['Pagaie_ECA'] == 'PAGB') {
                        if ($row['Irreg'] != '') {
                            $row['Irreg'] .= '<br>';
                        }
                        if ($row['Pagaie_ECA'] != '') {
                            $row['Irreg'] .= $row['Pagaie_ECA'];
                        } else {
                            $row['Irreg'] .= 'PAG ?';
                        }
                    }
                    if ($row['Etat_certificat_CK'] == 'NON') {
                        if ($row['Irreg'] != '') {
                            $row['Irreg'] .= '<br>';
                        }
                        $row['Irreg'] .= 'Certif CK';
                    }
                    $row['Nom'] = mb_strtoupper($row['Nom']);
                    $row['Prenom'] =  mb_convert_case(strtolower($row['Prenom']), MB_CASE_TITLE, "UTF-8");
                    array_push($arrayStats, $row);
                }
                break;
            case 'CJoueesN': // Compétitions jouées dans la saison en cours (par équipe)
                $sql = "SELECT ce.Libelle nomEquipe, lc.Matric, lc.Nom, lc.Prenom, 
                    j.Code_competition Competition, COUNT(DISTINCT mj.Id_match) Nb_matchs 
                    FROM kp_match_joueur mj, kp_match m, kp_journee j, 
                    kp_licence lc, kp_competition_equipe ce 
                    WHERE lc.Matric = mj.Matric 
                    AND mj.Capitaine NOT IN ('E','A','X') 
                    AND mj.Id_match = m.Id 
                    AND IF(mj.Equipe = 'A', m.Id_equipeA, m.Id_equipeB) = ce.Id 
                    AND m.Id_journee = j.Id 
                    AND j.Code_competition LIKE 'N%' 
                    AND j.Code_saison = ? 
                    AND m.Date_match <= CURDATE() 
                    AND m.Validation = 'O' 
                    GROUP BY nomEquipe, mj.Matric, j.Code_competition 
                    ORDER BY lc.Nom, lc.Prenom, Competition 
                    LIMIT 0,$nbLignes ";
                $result = $myBdd->pdo->prepare($sql);
                $result->execute([$codeSaison]);
                while ($row = $result->fetch()) {
                    array_push($arrayStats, array(
                        'Competition' => $row['Competition'],
                        'Matric' => $row['Matric'],
                        'Nom' => mb_strtoupper($row['Nom']),
                        'Prenom' => mb_convert_case(strtolower($row['Prenom']), MB_CASE_TITLE, "UTF-8"),
                        'nomEquipe' => $row['nomEquipe'],
                        'Nb_matchs' => $row['Nb_matchs']
                    ));
                }
                break;
            case 'CJoueesCF': // Compétitions jouées dans la saison en cours (par équipe)
                $sql = "SELECT ce.Libelle nomEquipe, lc.Matric, lc.Nom, lc.Prenom, 
                    j.Code_competition Competition, COUNT(DISTINCT mj.Id_match) Nb_matchs 
                    FROM kp_match_joueur mj, kp_match m, kp_journee j, 
                    kp_licence lc, kp_competition_equipe ce 
                    WHERE lc.Matric = mj.Matric 
                    AND mj.Capitaine NOT IN ('E','A','X') 
                    AND mj.Id_match = m.Id 
                    AND IF(mj.Equipe = 'A', m.Id_equipeA, m.Id_equipeB) = ce.Id 
                    AND m.Id_journee = j.Id 
                    AND j.Code_competition LIKE 'CF%' 
                    AND j.Code_saison = ? 
                    AND m.Date_match <= CURDATE() 
                    AND m.Validation = 'O' 
                    GROUP BY nomEquipe, mj.Matric, j.Code_competition 
                    ORDER BY lc.Nom, lc.Prenom, Competition 
                    LIMIT 0,$nbLignes ";
                $result = $myBdd->pdo->prepare($sql);
                $result->execute([$codeSaison]);
                while ($row = $result->fetch()) {
                    array_push($arrayStats, array(
                        'Competition' => $row['Competition'],
                        'Matric' => $row['Matric'],
                        'Nom' => mb_strtoupper($row['Nom']),
                        'Prenom' => mb_convert_case(strtolower($row['Prenom']), MB_CASE_TITLE, "UTF-8"),
                        'nomEquipe' => $row['nomEquipe'],
                        'Nb_matchs' => $row['Nb_matchs']
                    ));
                }
                break;
            case 'OfficielsJournees': // OfficielsJournees
                $sql = "SELECT j.* 
                    FROM kp_journee j 
                    WHERE 1 
                    AND j.Code_competition IN ($in) 
                    AND j.Code_saison = ? 
                    GROUP BY j.Code_competition, j.Date_debut, j.Lieu 
                    ORDER BY j.Code_competition, j.Date_debut, j.Lieu 
                    LIMIT 0,$nbLignes ";
                $nbOfficiels = 0;
                $result = $myBdd->pdo->prepare($sql);
                $result->execute(array_merge($Compets, [$codeSaison]));
                $num_results = $result->rowCount();
                while ($row = $result->fetch()) {
                    array_push($arrayStats, $row);
                    if ($row['Delegue'] != '' or $row['ChefArbitre'] != '') {
                        $nbOfficiels++;
                    }
                }
                break;
            case 'OfficielsMatchs': // OfficielsMatchs
                $sql = "SELECT j.Code_competition, j.Lieu, j.Departement, m.Id, m.Numero_ordre, 
                    m.Date_match, m.Heure_match, a.Libelle equipeA, b.Libelle equipeB, 
                    m.Arbitre_principal, m.Arbitre_secondaire, m.Ligne1, m.Ligne2, m.Secretaire, 
                    m.Chronometre, m.Timeshoot 
                    FROM kp_journee j, kp_match m, kp_competition_equipe a, 
                    kp_competition_equipe b 
                    WHERE 1 
                    AND j.Code_competition IN ($in) 
                    AND j.Code_saison = ? 
                    AND j.Id = m.Id_journee 
                    AND m.Id_equipeA = a.Id 
                    AND m.Id_equipeB = b.Id 
                    ORDER BY j.Code_competition, m.Date_match, m.Heure_match, m.Numero_ordre 
                    LIMIT 0,$nbLignes ";
                $result = $myBdd->pdo->prepare($sql);
                $result->execute(array_merge($Compets, [$codeSaison]));
                while ($row = $result->fetch()) {
                    array_push($arrayStats, $row);
                }
                break;
            case 'ListeArbitres': // ListeArbitres
                $sql = "SELECT lc.Matric, lc.Nom, lc.Prenom, lc.Sexe, c.Code Code_club, 
                    c.Libelle Club, a.arbitre, a.niveau, a.saison, a.livret 
                    FROM kp_arbitre a, kp_licence lc, kp_club c 
                    WHERE 1 
                    AND a.Matric = lc.Matric 
                    AND c.Code = lc.Numero_club 
                    AND a.Matric < 2000000 
                    AND a.arbitre != '' 
                    ORDER BY a.arbitre, a.niveau, a.saison, lc.Nom, lc.Prenom 
                    LIMIT 0,$nbLignes ";
                $result = $myBdd->pdo->prepare($sql);
                $result->execute();
                while ($row = $result->fetch()) {
                    $row['Nom'] = mb_strtoupper($row['Nom']);
                    $row['Prenom'] =  mb_convert_case(strtolower($row['Prenom']), MB_CASE_TITLE, "UTF-8");
                    array_push($arrayStats, $row);
                }
                break;
            case 'ListeEquipes': // ListeEquipes
                $sql = "SELECT ce.Libelle equipe, ce.Code_club club, c.Code_comite_dep cd, 
                    kp_cd.Code_comite_reg cr, GROUP_CONCAT(DISTINCT l.Numero_club) Club_actuel_joueurs
                    FROM kp_competition_equipe ce
                    LEFT JOIN kp_club c ON ce.Code_club = c.Code
                    LEFT JOIN kp_cd ON c.Code_comite_dep = kp_cd.Code
                    LEFT JOIN kp_competition_equipe_joueur cej ON cej.Id_equipe = ce.Id
                    LEFT JOIN kp_licence l ON cej.Matric = l.Matric
                    WHERE 1 
                    AND ce.Code_compet IN ($in) 
                    AND ce.Code_saison = ? 
                    AND cej.Capitaine != 'E'
                    GROUP BY ce.Numero
                    ORDER BY ce.Code_club, ce.Libelle 
                    LIMIT 0, $nbLignes ";
                $result = $myBdd->pdo->prepare($sql);
                $result->execute(array_merge($Compets, [$codeSaison]));
                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    array_push($arrayStats, $row);
                }
                break;
            case 'ListeJoueurs': // ListeJoueurs
                $sql = "SELECT l.Matric, l.Nom, l.Prenom, l.Sexe, l.Naissance, l.Numero_club Club_actuel, cej.Categ Categorie, ce.Code_club Club
                    FROM kp_competition_equipe_joueur cej
                    LEFT JOIN kp_competition_equipe ce ON cej.Id_equipe = ce.Id
                    LEFT JOIN kp_licence l ON cej.Matric = l.Matric
                    WHERE 1
                    AND cej.Capitaine != 'A'
                    AND cej.Capitaine != 'E'
                    AND ce.Code_compet IN ($in) 
                    AND ce.Code_saison = ? 
                    GROUP BY cej.Matric
                    ORDER BY ce.Code_club, ce.Libelle 
                    LIMIT 0, $nbLignes ";
                $result = $myBdd->pdo->prepare($sql);
                $result->execute(array_merge($Compets, [$codeSaison]));
                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    array_push($arrayStats, $row);
                }
                break;
            case 'ListeJoueurs2': // ListeJoueurs&Coachs
                $sql = "SELECT l.Matric, l.Nom, l.Prenom, l.Sexe, l.Naissance, l.Numero_club Club_actuel, cej.Categ Categorie, ce.Code_club Club
                    FROM kp_competition_equipe_joueur cej
                    LEFT JOIN kp_competition_equipe ce ON cej.Id_equipe = ce.Id
                    LEFT JOIN kp_licence l ON cej.Matric = l.Matric
                    WHERE 1
                    AND ce.Code_compet IN ($in) 
                    AND ce.Code_saison = ? 
                    GROUP BY cej.Matric
                    ORDER BY ce.Code_club, ce.Libelle 
                    LIMIT 0, $nbLignes ";
                $result = $myBdd->pdo->prepare($sql);
                $result->execute(array_merge($Compets, [$codeSaison]));
                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    array_push($arrayStats, $row);
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
            $pdf->Image('../img/CNAKPI_small.jpg', 10, 10, 0, 20, 'jpg', "https://www.kayak-polo.info");
            $img = redimImage($visuels['logo'], 210, 10, 20, 'R');
            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
            // KPI
        } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O') {
            $pdf->Image('../img/CNAKPI_small.jpg', 84, 10, 0, 20, 'jpg', "https://www.kayak-polo.info");
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


        switch ($AfficheStat) {
            case 'Buteurs':
            default:
                $pdf->Cell(190, 7, "Stats : Meilleur butteur", 0, 1, 'C');
                $pdf->Ln(3);
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(10, 7, '#', 'B', 0, 'C');
                $pdf->Cell(18, 7, 'Compet', 'B', 0, 'C');
                $pdf->Cell(8, 7, 'Num', 'B', 0, 'C');
                $pdf->Cell(40, 7, 'Nom', 'B', 0, 'C');
                $pdf->Cell(40, 7, 'Prénom', 'B', 0, 'C');
                $pdf->Cell(62, 7, 'Equipe', 'B', 0, 'C');
                $pdf->Cell(12, 7, 'Buts', 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 8);

                for ($i = 0; $i < count($arrayStats); $i++) {
                    $pdf->Cell(10, 7, $i + 1, 'B', 0, 'C');
                    $pdf->Cell(18, 7, $arrayStats[$i]['Competition'], 'B', 0, 'C');
                    $pdf->Cell(8, 7, $arrayStats[$i]['Numero'], 'B', 0, 'C');
                    $pdf->Cell(40, 7, $arrayStats[$i]['Nom'], 'B', 0, 'C');
                    $pdf->Cell(40, 7, $arrayStats[$i]['Prenom'], 'B', 0, 'C');
                    $pdf->Cell(62, 7, $arrayStats[$i]['Equipe'], 'B', 0, 'C');
                    $pdf->Cell(12, 7, $arrayStats[$i]['Buts'], 'B', 1, 'C');
                }
                break;
            case 'Attaque':
                $pdf->Cell(190, 7, "Stats : Meilleure attaque", 0, 1, 'C');
                $pdf->Ln(3);
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(30, 7, '', '', 0, 'C');
                $pdf->Cell(10, 7, '#', 'B', 0, 'C');
                $pdf->Cell(28, 7, 'Compétition', 'B', 0, 'C');
                $pdf->Cell(70, 7, 'Equipe', 'B', 0, 'C');
                $pdf->Cell(17, 7, 'Marqués', 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 8);

                for ($i = 0; $i < count($arrayStats); $i++) {
                    $pdf->Cell(30, 7, '', '', 0, 'C');
                    $pdf->Cell(10, 7, $i + 1, 'B', 0, 'C');
                    $pdf->Cell(28, 7, $arrayStats[$i]['Competition'], 'B', 0, 'C');
                    $pdf->Cell(70, 7, $arrayStats[$i]['Equipe'], 'B', 0, 'C');
                    $pdf->Cell(17, 7, $arrayStats[$i]['Buts'], 'B', 1, 'C');
                }
                break;
            case 'Defense':
                $pdf->Cell(190, 7, "Stats : Meilleure défense", 0, 1, 'C');
                $pdf->Ln(3);
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(30, 7, '', '', 0, 'C');
                $pdf->Cell(10, 7, '#', 'B', 0, 'C');
                $pdf->Cell(28, 7, 'Compétition', 'B', 0, 'C');
                $pdf->Cell(70, 7, 'Equipe', 'B', 0, 'C');
                $pdf->Cell(17, 7, 'Concédés', 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 8);

                for ($i = 0; $i < count($arrayStats); $i++) {
                    $pdf->Cell(30, 7, '', '', 0, 'C');
                    $pdf->Cell(10, 7, $i + 1, 'B', 0, 'C');
                    $pdf->Cell(28, 7, $arrayStats[$i]['Competition'], 'B', 0, 'C');
                    $pdf->Cell(70, 7, $arrayStats[$i]['Equipe'], 'B', 0, 'C');
                    $pdf->Cell(17, 7, $arrayStats[$i]['Buts'], 'B', 1, 'C');
                }
                break;
            case 'Cartons':
                $pdf->Cell(190, 7, "Stats : Cartons", 0, 1, 'C');
                $pdf->Ln(3);
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

                for ($i = 0; $i < count($arrayStats); $i++) {
                    $pdf->Cell(10, 7, $i + 1, 'B', 0, 'C');
                    $pdf->Cell(19, 7, $arrayStats[$i]['Competition'], 'B', 0, 'C');
                    $pdf->Cell(8, 7, $arrayStats[$i]['Numero'], 'B', 0, 'C');
                    $pdf->Cell(37, 7, $arrayStats[$i]['Nom'], 'B', 0, 'C');
                    $pdf->Cell(37, 7, $arrayStats[$i]['Prenom'], 'B', 0, 'C');
                    $pdf->Cell(52, 7, $arrayStats[$i]['Equipe'], 'B', 0, 'C');
                    $pdf->Cell(9, 7, $arrayStats[$i]['Vert'], 'B', 0, 'C');
                    $pdf->Cell(9, 7, $arrayStats[$i]['Jaune'], 'B', 0, 'C');
                    $pdf->Cell(9, 7, $arrayStats[$i]['Rouge'], 'B', 1, 'C');
                }
                break;
            case 'CartonsEquipe':
                $pdf->Cell(190, 7, "Stats : Cartons par équipe", 0, 1, 'C');
                $pdf->Ln(3);
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(15, 7, '#', 'B', 0, 'C');
                $pdf->Cell(28, 7, 'Compet', 'B', 0, 'C');
                $pdf->Cell(90, 7, 'Equipe', 'B', 0, 'C');
                $pdf->Cell(19, 7, 'V', 'B', 0, 'C');
                $pdf->Cell(19, 7, 'J', 'B', 0, 'C');
                $pdf->Cell(19, 7, 'R', 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 8);

                for ($i = 0; $i < count($arrayStats); $i++) {
                    $pdf->Cell(15, 7, $i + 1, 'B', 0, 'C');
                    $pdf->Cell(28, 7, $arrayStats[$i]['Competition'], 'B', 0, 'C');
                    $pdf->Cell(90, 7, $arrayStats[$i]['Equipe'], 'B', 0, 'C');
                    $pdf->Cell(19, 7, $arrayStats[$i]['Vert'], 'B', 0, 'C');
                    $pdf->Cell(19, 7, $arrayStats[$i]['Jaune'], 'B', 0, 'C');
                    $pdf->Cell(19, 7, $arrayStats[$i]['Rouge'], 'B', 1, 'C');
                }
                break;
            case 'Fairplay':
                $pdf->Cell(190, 7, "Stats : Classement disciplinaire", 0, 1, 'C');
                $pdf->Ln(3);
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(10, 7, '#', 'B', 0, 'C');
                $pdf->Cell(18, 7, 'Compet', 'B', 0, 'C');
                $pdf->Cell(8, 7, 'Num', 'B', 0, 'C');
                $pdf->Cell(40, 7, 'Nom', 'B', 0, 'C');
                $pdf->Cell(40, 7, 'Prénom', 'B', 0, 'C');
                $pdf->Cell(62, 7, 'Equipe', 'B', 0, 'C');
                $pdf->Cell(12, 7, 'Pts', 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 8);

                for ($i = 0; $i < count($arrayStats); $i++) {
                    $pdf->Cell(10, 7, $i + 1, 'B', 0, 'C');
                    $pdf->Cell(18, 7, $arrayStats[$i]['Competition'], 'B', 0, 'C');
                    $pdf->Cell(8, 7, $arrayStats[$i]['Numero'], 'B', 0, 'C');
                    $pdf->Cell(40, 7, $arrayStats[$i]['Nom'], 'B', 0, 'C');
                    $pdf->Cell(40, 7, $arrayStats[$i]['Prenom'], 'B', 0, 'C');
                    $pdf->Cell(62, 7, $arrayStats[$i]['Equipe'], 'B', 0, 'C');
                    $pdf->Cell(12, 7, $arrayStats[$i]['Fairplay'], 'B', 1, 'C');
                }
                break;
            case 'FairplayEquipe':
                $pdf->Cell(190, 7, "Stats : Classement disciplinaire par équipe", 0, 1, 'C');
                $pdf->Ln(3);
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(30, 7, '', '', 0, 'C');
                $pdf->Cell(10, 7, '#', 'B', 0, 'C');
                $pdf->Cell(28, 7, 'Compétition', 'B', 0, 'C');
                $pdf->Cell(70, 7, 'Equipe', 'B', 0, 'C');
                $pdf->Cell(12, 7, 'Pts', 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 8);

                for ($i = 0; $i < count($arrayStats); $i++) {
                    $pdf->Cell(30, 7, '', '', 0, 'C');
                    $pdf->Cell(10, 7, $i + 1, 'B', 0, 'C');
                    $pdf->Cell(28, 7, $arrayStats[$i]['Competition'], 'B', 0, 'C');
                    $pdf->Cell(70, 7, $arrayStats[$i]['Equipe'], 'B', 0, 'C');
                    $pdf->Cell(12, 7, $arrayStats[$i]['Fairplay'], 'B', 1, 'C');
                }
                break;
            case 'Arbitrage':
                $pdf->Cell(190, 7, "Stats : Arbitrages", 0, 1, 'C');
                $pdf->Ln(3);
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

                for ($i = 0; $i < count($arrayStats); $i++) {
                    $pdf->Cell(21, 7, '', '', 0, 'C');
                    $pdf->Cell(10, 7, $i + 1, 'B', 0, 'C');
                    $pdf->Cell(18, 7, $arrayStats[$i]['Competition'], 'B', 0, 'C');
                    $pdf->Cell(40, 7, $arrayStats[$i]['Nom'], 'B', 0, 'C');
                    $pdf->Cell(40, 7, $arrayStats[$i]['Prenom'], 'B', 0, 'C');
                    //					$pdf->Cell(51,7,$arrayArbitrage[$i]['Equipe'],'B',0,'C');
                    $pdf->Cell(13, 7, $arrayStats[$i]['Principal'], 'B', 0, 'C');
                    $pdf->Cell(13, 7, $arrayStats[$i]['Secondaire'], 'B', 0, 'C');
                    $pdf->Cell(13, 7, $arrayStats[$i]['Total'], 'B', 1, 'C');
                }
                break;
            case 'ArbitrageEquipe':
                $pdf->Cell(190, 7, "Stats : Arbitrages par équipe", 0, 1, 'C');
                $pdf->Ln(3);
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(30, 7, '', '', 0, 'C');
                $pdf->Cell(10, 7, '#', 'B', 0, 'C');
                $pdf->Cell(19, 7, 'Compet', 'B', 0, 'C');
                $pdf->Cell(60, 7, 'Equipe', 'B', 0, 'C');
                $pdf->Cell(13, 7, 'Princ.', 'B', 0, 'C');
                $pdf->Cell(13, 7, 'Sec.', 'B', 0, 'C');
                $pdf->Cell(13, 7, 'Total', 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 8);

                for ($i = 0; $i < count($arrayStats); $i++) {
                    $pdf->Cell(30, 7, '', '', 0, 'C');
                    $pdf->Cell(10, 7, $i + 1, 'B', 0, 'C');
                    $pdf->Cell(19, 7, $arrayStats[$i]['Competition'], 'B', 0, 'C');
                    $pdf->Cell(60, 7, $arrayStats[$i]['Equipe'], 'B', 0, 'C');
                    $pdf->Cell(13, 7, $arrayStats[$i]['Principal'], 'B', 0, 'C');
                    $pdf->Cell(13, 7, $arrayStats[$i]['Secondaire'], 'B', 0, 'C');
                    $pdf->Cell(13, 7, $arrayStats[$i]['Total'], 'B', 1, 'C');
                }
                break;
            case 'CJouees':
                $pdf->Cell(190, 7, "Stats : Compétitions jouées", 0, 1, 'C');
                $pdf->Ln(3);
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(10, 7, '#', 'B', 0, 'C');
                $pdf->Cell(18, 7, 'Compet', 'B', 0, 'C');
                $pdf->Cell(40, 7, 'Nom', 'B', 0, 'C');
                $pdf->Cell(40, 7, 'Prénom', 'B', 0, 'C');
                $pdf->Cell(70, 7, 'Equipe', 'B', 0, 'C');
                $pdf->Cell(12, 7, 'Nb', 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 8);

                for ($i = 0; $i < count($arrayStats); $i++) {
                    $pdf->Cell(10, 7, $i + 1, 'B', 0, 'C');
                    $pdf->Cell(18, 7, $arrayStats[$i]['Competition'], 'B', 0, 'C');
                    $pdf->Cell(40, 7, $arrayStats[$i]['Nom'], 'B', 0, 'C');
                    $pdf->Cell(40, 7, $arrayStats[$i]['Prenom'], 'B', 0, 'C');
                    $pdf->Cell(70, 7, $arrayStats[$i]['Nom_club'], 'B', 0, 'C');
                    $pdf->Cell(12, 7, $arrayStats[$i]['Nb_matchs'], 'B', 1, 'C');
                }
                break;
            case 'CJouees2':
                $pdf->Cell(190, 7, "Stats : Matchs joués par équipe", 0, 1, 'C');
                $pdf->Ln(3);
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(10, 7, '#', 'B', 0, 'C');
                $pdf->Cell(18, 7, 'Compet', 'B', 0, 'C');
                $pdf->Cell(40, 7, 'Nom', 'B', 0, 'C');
                $pdf->Cell(40, 7, 'Prénom', 'B', 0, 'C');
                $pdf->Cell(70, 7, 'Equipe', 'B', 0, 'C');
                $pdf->Cell(12, 7, 'Nb', 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 8);

                for ($i = 0; $i < count($arrayStats); $i++) {
                    $pdf->Cell(10, 7, $i + 1, 'B', 0, 'C');
                    $pdf->Cell(18, 7, $arrayStats[$i]['Competition'], 'B', 0, 'C');
                    $pdf->Cell(40, 7, $arrayStats[$i]['Nom'], 'B', 0, 'C');
                    $pdf->Cell(40, 7, $arrayStats[$i]['Prenom'], 'B', 0, 'C');
                    $pdf->Cell(70, 7, $arrayStats[$i]['nomEquipe'], 'B', 0, 'C');
                    $pdf->Cell(12, 7, $arrayStats[$i]['Nb_matchs'], 'B', 1, 'C');
                }
                break;
            case 'CJouees3':
                $pdf->Cell(190, 7, "Stats : Irrégularités", 0, 1, 'C');
                $pdf->Ln(3);
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(8, 7, '#', 'B', 0, 'C');
                $pdf->Cell(15, 7, 'Compet', 'B', 0, 'C');
                $pdf->Cell(35, 7, 'Nom', 'B', 0, 'C');
                $pdf->Cell(35, 7, 'Prénom', 'B', 0, 'C');
                $pdf->Cell(60, 7, 'Equipe', 'B', 0, 'C');
                $pdf->Cell(35, 7, 'Irréglarité', 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 8);

                for ($i = 0; $i < count($arrayStats); $i++) {
                    $pdf->Cell(8, 7, $i + 1, 'B', 0, 'C');
                    $pdf->Cell(15, 7, $arrayStats[$i]['Competition'], 'B', 0, 'C');
                    $pdf->Cell(35, 7, $arrayStats[$i]['Nom'], 'B', 0, 'C');
                    $pdf->Cell(35, 7, $arrayStats[$i]['Prenom'], 'B', 0, 'C');
                    $pdf->Cell(60, 7, $arrayStats[$i]['nomEquipe'], 'B', 0, 'C');
                    $pdf->Cell(35, 7, $arrayStats[$i]['Irreg'], 'B', 1, 'C');
                }
                break;
            case 'CJoueesN':
                $pdf->Cell(190, 7, "Stats : Matchs joués en championnat de France", 0, 1, 'C');
                $pdf->Ln(3);
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(10, 7, '#', 'B', 0, 'C');
                $pdf->Cell(18, 7, 'Compet', 'B', 0, 'C');
                $pdf->Cell(40, 7, 'Nom', 'B', 0, 'C');
                $pdf->Cell(40, 7, 'Prénom', 'B', 0, 'C');
                $pdf->Cell(70, 7, 'Equipe', 'B', 0, 'C');
                $pdf->Cell(12, 7, 'Nb', 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 8);
                $k = 0;
                $matric = 0;
                for ($i = 0; $i < count($arrayStats); $i++) {
                    $h = $i - 1;
                    $j = $i + 1;
                    $encadrement = 'T';
                    if (($h >= 0
                            && $arrayStats[$i]['Matric'] == $arrayStats[$h]['Matric']
                            && $arrayStats[$i]['nomEquipe'] != $arrayStats[$h]['nomEquipe'])
                        || ($j < count($arrayStats)
                            && $arrayStats[$i]['Matric'] == $arrayStats[$j]['Matric']
                            && $arrayStats[$i]['nomEquipe'] != $arrayStats[$j]['nomEquipe'])
                    ) {
                        $k++;
                        if ($arrayStats[$i]['Matric'] == $matric) {
                            $encadrement = '';
                            $arrayStats[$i]['Nom'] = '';
                            $arrayStats[$i]['Prenom'] = '';
                        }
                        $matric = $arrayStats[$i]['Matric'];
                        $pdf->Cell(10, 7, $k, 'B', 0, 'C');
                        $pdf->Cell(18, 7, $arrayStats[$i]['Competition'], 'B', 0, 'C');
                        $pdf->Cell(40, 7, $arrayStats[$i]['Nom'], $encadrement, 0, 'C');
                        $pdf->Cell(40, 7, $arrayStats[$i]['Prenom'], $encadrement, 0, 'C');
                        $pdf->Cell(70, 7, $arrayStats[$i]['nomEquipe'], 'B', 0, 'C');
                        $pdf->Cell(12, 7, $arrayStats[$i]['Nb_matchs'], 'B', 1, 'C');
                    }
                }
                break;
            case 'CJoueesCF':
                $pdf->Cell(190, 7, "Stats : Matchs joués en coupe de France", 0, 1, 'C');
                $pdf->Ln(3);
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(10, 7, '#', 'B', 0, 'C');
                $pdf->Cell(18, 7, 'Compet', 'B', 0, 'C');
                $pdf->Cell(40, 7, 'Nom', 'B', 0, 'C');
                $pdf->Cell(40, 7, 'Prénom', 'B', 0, 'C');
                $pdf->Cell(70, 7, 'Equipe', 'B', 0, 'C');
                $pdf->Cell(12, 7, 'Nb', 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 8);
                $k = 0;
                $matric = 0;
                for ($i = 0; $i < count($arrayStats); $i++) {
                    $h = $i - 1;
                    $j = $i + 1;
                    $encadrement = 'T';
                    if (($h >= 0
                            && $arrayStats[$i]['Matric'] == $arrayStats[$h]['Matric']
                            && $arrayStats[$i]['nomEquipe'] != $arrayStats[$h]['nomEquipe'])
                        || ($j < count($arrayStats)
                            && $arrayStats[$i]['Matric'] == $arrayStats[$j]['Matric']
                            && $arrayStats[$i]['nomEquipe'] != $arrayStats[$j]['nomEquipe'])
                    ) {
                        $k++;
                        if ($arrayStats[$i]['Matric'] == $matric) {
                            $encadrement = '';
                            $arrayStats[$i]['Nom'] = '';
                            $arrayStats[$i]['Prenom'] = '';
                        }
                        $matric = $arrayStats[$i]['Matric'];
                        $pdf->Cell(10, 7, $k, 'B', 0, 'C');
                        $pdf->Cell(18, 7, $arrayStats[$i]['Competition'], 'B', 0, 'C');
                        $pdf->Cell(40, 7, $arrayStats[$i]['Nom'], $encadrement, 0, 'C');
                        $pdf->Cell(40, 7, $arrayStats[$i]['Prenom'], $encadrement, 0, 'C');
                        $pdf->Cell(70, 7, $arrayStats[$i]['nomEquipe'], 'B', 0, 'C');
                        $pdf->Cell(12, 7, $arrayStats[$i]['Nb_matchs'], 'B', 1, 'C');
                    }
                }
                break;
            case 'OfficielsJournees':
                $pdf->Cell(190, 7, "Stats : Officiels par journée", 0, 1, 'C');
                $pdf->Ln(3);
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
                for ($i = 0; $i < count($arrayStats); $i++) {
                    $pdf->Cell(10, 7, $arrayStats[$i]['Code_competition'], 'B', 0, 'C');
                    $pdf->Cell(20, 7, $arrayStats[$i]['Date_debut'], 'B', 0, 'C');
                    $pdf->Cell(30, 7, $arrayStats[$i]['Lieu'], 'B', 0, 'C');
                    $pdf->Cell(30, 7, utyGetNomPrenom($arrayStats[$i]['Responsable_insc']), 'B', 0, 'C');
                    $pdf->Cell(30, 7, utyGetNomPrenom($arrayStats[$i]['Responsable_R1']), 'B', 0, 'C');
                    $pdf->Cell(30, 7, utyGetNomPrenom($arrayStats[$i]['Delegue']), 'B', 0, 'C');
                    $pdf->Cell(30, 7, utyGetNomPrenom($arrayStats[$i]['ChefArbitre']), 'B', 1, 'C');
                }
                break;
            case 'OfficielsMatchs':
                $pdf->Cell(190, 12, "Stats : Officiels par match", 0, 1, 'C');
                $pdf->Ln(3);
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(25, 7, 'Compet/Lieu', 'B', 0, 'C');
                $pdf->Cell(15, 7, 'Match', 'B', 0, 'C');
                $pdf->Cell(40, 7, 'Arbitres', 'B', 0, 'C');
                $pdf->Cell(40, 7, 'Lignes', 'B', 0, 'C');
                $pdf->Cell(75, 7, 'Table', 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 6);
                $k = 0;
                for ($i = 0; $i < count($arrayStats); $i++) {
                    $pdf->Cell(25, 4, $arrayStats[$i]['Code_competition'], 0, 0, 'C');
                    $pdf->Cell(15, 4, utyDateUsToFr($arrayStats[$i]['Date_match']), 0, 0, 'C');
                    $pdf->Cell(40, 4, $arrayStats[$i]['Arbitre_principal'], 0, 0, 'C');
                    $pdf->Cell(40, 4, $arrayStats[$i]['Ligne1'], 0, 0, 'C');
                    $pdf->Cell(38, 4, $arrayStats[$i]['Secretaire'], 0, 0, 'C');
                    $pdf->Cell(37, 4, $arrayStats[$i]['Chronometre'], 0, 1, 'C');

                    $pdf->Cell(25, 4, $arrayStats[$i]['Lieu'] . ' (' . $arrayStats[$i]['Departement'] . ')', 'B', 0, 'C');
                    $pdf->Cell(15, 4, 'n°' . $arrayStats[$i]['Numero_ordre'] . ' - ' . $arrayStats[$i]['Heure_match'], 'B', 0, 'C');
                    $pdf->Cell(40, 4, $arrayStats[$i]['Arbitre_secondaire'], 'B', 0, 'C');
                    $pdf->Cell(40, 4, $arrayStats[$i]['Ligne2'], 'B', 0, 'C');
                    $pdf->Cell(38, 4, '', 'B', 0, 'C');
                    $pdf->Cell(37, 4, $arrayStats[$i]['Timeshoot'], 'B', 1, 'C');
                }
                break;
            case 'ListeArbitres':
                $pdf->Cell(190, 12, "Stats : Arbitres", 0, 1, 'C');
                $pdf->Ln(3);
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(60, 7, 'Arbitre', 'B', 0, 'C');
                $pdf->Cell(60, 7, 'Club', 'B', 0, 'C');
                $pdf->Cell(20, 7, 'Niveau', 'B', 0, 'C');
                $pdf->Cell(20, 7, 'Saison', 'B', 0, 'C');
                $pdf->Cell(30, 7, 'Livret', 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 6);
                $k = 0;
                for ($i = 0; $i < count($arrayStats); $i++) {
                    $pdf->Cell(60, 4, $arrayStats[$i]['Nom'] . ' ' . $arrayStats[$i]['Prenom'] . ' (' . $arrayStats[$i]['Matric'] . ')', 0, 0, 'C');
                    $pdf->Cell(60, 4, $arrayStats[$i]['Club'], 0, 0, 'C');
                    $pdf->Cell(20, 4, $arrayStats[$i]['arbitre'] . ' ' . $arrayStats[$i]['niveau'], 0, 0, 'C');
                    $pdf->Cell(20, 4, $arrayStats[$i]['saison'], 0, 0, 'C');
                    $pdf->Cell(30, 4, $arrayStats[$i]['livret'], 0, 1, 'C');
                }
                break;
            case 'ListeEquipes':
                $pdf->Cell(190, 12, "Stats : Equipes", 0, 1, 'C');
                $pdf->Ln(3);
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(70, 7, 'Equipe', 'B', 0, 'C');
                $pdf->Cell(20, 7, 'Club', 'B', 0, 'C');
                $pdf->Cell(20, 7, 'CD', 'B', 0, 'C');
                $pdf->Cell(20, 7, 'CR', 'B', 0, 'C');
                $pdf->Cell(60, 7, 'Club actuel des joueurs', 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 6);
                $k = 0;
                for ($i = 0; $i < count($arrayStats); $i++) {
                    $pdf->Cell(70, 4, $arrayStats[$i]['equipe'], 0, 0, 'C');
                    $pdf->Cell(20, 4, $arrayStats[$i]['club'], 0, 0, 'C');
                    $pdf->Cell(20, 4, $arrayStats[$i]['cd'] . ' ' . $arrayStats[$i]['niveau'], 0, 0, 'C');
                    $pdf->Cell(20, 4, $arrayStats[$i]['cr'], 0, 0, 'C');
                    $pdf->Cell(60, 4, $arrayStats[$i]['Club_actuel_joueurs'], 0, 1, 'C');
                }
                break;
            case 'ListeJoueurs':
                $pdf->Cell(190, 12, "Stats : Joueurs", 0, 1, 'C');
                $pdf->Ln(3);
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(70, 7, 'Nom', 'B', 0, 'L');
                $pdf->Cell(10, 7, 'Sexe', 'B', 0, 'C');
                $pdf->Cell(30, 7, 'Naissance', 'B', 0, 'C');
                $pdf->Cell(20, 7, 'Club', 'B', 0, 'C');
                $pdf->Cell(30, 7, 'Catégorie ' . $codeSaison, 'B', 0, 'C');
                $pdf->Cell(30, 7, 'Club ' . $codeSaison, 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 6);
                $k = 0;
                for ($i = 0; $i < count($arrayStats); $i++) {
                    $pdf->Cell(70, 4, $arrayStats[$i]['Nom'] . ' ' . $arrayStats[$i]['Prenom'] . ' (' . $arrayStats[$i]['Matric'] . ')', 0, 0, 'L');
                    $pdf->Cell(10, 4, $arrayStats[$i]['Sexe'], 0, 0, 'C');
                    $pdf->Cell(30, 4, $arrayStats[$i]['Naissance'], 0, 0, 'C');
                    $pdf->Cell(20, 4, $arrayStats[$i]['Club_actuel'], 0, 0, 'C');
                    $pdf->Cell(30, 4, $arrayStats[$i]['Categorie'], 0, 0, 'C');
                    $pdf->Cell(30, 4, $arrayStats[$i]['Club'], 0, 1, 'C');
                }
                break;
            case 'ListeJoueurs2':
                $pdf->Cell(190, 12, "Stats : Joueurs & Entraîneurs", 0, 1, 'C');
                $pdf->Ln(3);
                $pdf->SetFont('Arial', 'BI', 10);
                $pdf->Cell(70, 7, 'Nom', 'B', 0, 'L');
                $pdf->Cell(10, 7, 'Sexe', 'B', 0, 'C');
                $pdf->Cell(30, 7, 'Naissance', 'B', 0, 'C');
                $pdf->Cell(20, 7, 'Club', 'B', 0, 'C');
                $pdf->Cell(30, 7, 'Catégorie ' . $codeSaison, 'B', 0, 'C');
                $pdf->Cell(30, 7, 'Club ' . $codeSaison, 'B', 1, 'C');
                $pdf->SetFont('Arial', '', 6);
                $k = 0;
                for ($i = 0; $i < count($arrayStats); $i++) {
                    $pdf->Cell(70, 4, $arrayStats[$i]['Nom'] . ' ' . $arrayStats[$i]['Prenom'] . ' (' . $arrayStats[$i]['Matric'] . ')', 0, 0, 'L');
                    $pdf->Cell(10, 4, $arrayStats[$i]['Sexe'], 0, 0, 'C');
                    $pdf->Cell(30, 4, $arrayStats[$i]['Naissance'], 0, 0, 'C');
                    $pdf->Cell(20, 4, $arrayStats[$i]['Club_actuel'], 0, 0, 'C');
                    $pdf->Cell(30, 4, $arrayStats[$i]['Categorie'], 0, 0, 'C');
                    $pdf->Cell(30, 4, $arrayStats[$i]['Club'], 0, 1, 'C');
                }
                break;
        }

        $pdf->Output('Statistiques_' . $AfficheStat . '.pdf', 'I');
    }
}

$page = new FeuilleStats();
