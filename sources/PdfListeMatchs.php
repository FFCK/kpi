<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

require_once('commun/MyPDF.php');

require_once('lib/qrcode/qrcode.class.php');

// Liste des Matchs d'une Journee ou d'un Evenement 
class PdfListeMatchs extends MyPage
{
    function __construct()
    {
        parent::__construct();
        // Chargement des Matchs des journées ...
        $filtreJour = utyGetSession('filtreJour', '');
        $filtreJour = utyGetPost('filtreJour', $filtreJour);
        $filtreJour = utyGetGet('filtreJour', $filtreJour);

        $filtreTerrain = utyGetSession('filtreTerrain', '');
        $filtreTerrain = utyGetPost('filtreTerrain', $filtreTerrain);
        $filtreTerrain = utyGetGet('filtreTerrain', $filtreTerrain);

        $myBdd = new MyBdd();
        $lstJournee = utyGetSession('lstJournee', 0);
        $arrayJournees = explode(',', $lstJournee);
        $idEvenement = utyGetSession('idEvenement', -1);
        $idEvenement = utyGetGet('idEvenement', $idEvenement);
        if (utyGetGet('idEvenement', 0) > 0) {
            $arrayJournees = [];
            $sql = "SELECT Id_journee 
                FROM kp_evenement_journee 
                WHERE Id_evenement = ? ";
            $result = $myBdd->pdo->prepare($sql);
            $result->execute(array($idEvenement));
            while ($row = $result->fetch()) {
                $arrayJournees[] = $row['Id_journee'];
            }
        }
        $codeSaison = $myBdd->GetActiveSaison();
        $codeSaison = utyGetGet('S', $codeSaison);

        $orderMatchs = utyGetSession('orderMatchs', 'ORDER BY a.Date_match, d.Lieu, a.Heure_match, a.Terrain');
        $laCompet = utyGetSession('codeCompet', 0);
        $laCompet = utyGetGet('Compet', $laCompet);
        // Ne vider $arrayJournees que si $laCompet est une vraie compétition (pas *, 0, ou vide)
        if ($laCompet != 0 && $laCompet != '*' && $laCompet != '') {
            $arrayJournees = [];
            $idEvenement = -1;
        }
        $codeCompet = $laCompet;
        $sql = "SELECT a.Id, a.Id_journee, a.Id_equipeA, a.Id_equipeB, a.Numero_ordre, 
            a.Date_match, a.Heure_match, a.Libelle, a.Terrain, b.Libelle EquipeA, 
            c.Libelle EquipeB, a.Terrain, a.ScoreA, a.ScoreB, a.Arbitre_principal, 
            a.Arbitre_secondaire, a.Matric_arbitre_principal, a.Matric_arbitre_secondaire, 
            d.Code_competition, d.Phase, d.Niveau, d.Lieu, d.Libelle LibelleJournee, 
            e.Nom Nom_arb_prin, e.Prenom Prenom_arb_prin, f.Nom Nom_arb_sec, 
            f.Prenom Prenom_arb_sec, cp.Soustitre2, a.Validation  
            FROM kp_competition cp, kp_journee d, kp_match a 
            LEFT OUTER JOIN kp_competition_equipe b ON (a.Id_equipeA = b.Id) 
            LEFT OUTER JOIN kp_competition_equipe c ON (a.Id_equipeB = c.Id) 
            LEFT OUTER JOIN kp_licence e ON (a.Matric_arbitre_principal = e.Matric) 
            LEFT OUTER JOIN kp_licence f ON (a.Matric_arbitre_secondaire = f.Matric) 
            WHERE a.Id_journee = d.Id 
            AND d.Code_competition = cp.Code 
            AND a.Publication = 'O' 
            AND d.Code_saison = cp.Code_saison ";
        if (count($arrayJournees) == 0) {
            $sql .= "AND d.Code_competition = ? 
                AND d.Code_saison = ? ";
            $arrayQuery = array($laCompet, $codeSaison);
        } else {
            $in = str_repeat('?,', count($arrayJournees) - 1) . '?';
            $sql .= "AND a.Id_journee IN ($in) ";
            $arrayQuery = $arrayJournees;
        }
        if ($filtreJour != '') {
            $sql .= "AND a.Date_match = ? ";
            $arrayQuery = array_merge($arrayQuery, [$filtreJour]);
        }
        if ($filtreTerrain != '') {
            $sql .= "AND a.Terrain = ? ";
            $arrayQuery = array_merge($arrayQuery, [$filtreTerrain]);
        }
        $sql .= $orderMatchs;

        $orderMatchsKey1 = utyKeyOrder($orderMatchs, 0);

        $result = $myBdd->pdo->prepare($sql);
        $result->execute($arrayQuery);

        $PhaseLibelle = 0;
        $resultarray = $result->fetchAll(PDO::FETCH_ASSOC);
        foreach ($resultarray as $key => $row1) {
            if (trim($row1['Phase']) != '') {
                $PhaseLibelle = 1;
            }
            $lastCompetEvt = $row1['Code_competition'];
        }

        $Oldrupture = "";
        // Chargement des infos de l'évènement ou de la compétition
        $titreEvenementCompet = '';
        if ($idEvenement != -1) {
            $libelleEvenement = $myBdd->GetEvenementLibelle($idEvenement);
            $titreEvenementCompet = 'Evènement : ' . $libelleEvenement;
            $arrayCompetition = $myBdd->GetCompetition($lastCompetEvt, $codeSaison);
        } else {
            $arrayCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);
            if ($arrayCompetition['Titre_actif'] == 'O') {
                $titreEvenementCompet = $arrayCompetition['Libelle'];
            } else {
                $titreEvenementCompet = $arrayCompetition['Soustitre'];
            }
            if ($arrayCompetition['Soustitre2'] != '') {
                $titreEvenementCompet .= ' - ' . $arrayCompetition['Soustitre2'];
            }
        }

        $visuels = utyGetVisuels($arrayCompetition, FALSE);

        // Entête PDF ...
        $pdf = new MyPDF('L');
        // Open() removed - causes buffer corruption with mPDF

        $pdf->SetTitle("Liste des Matchs");
        $pdf->SetAuthor("Kayak-polo.info");
        $pdf->SetCreator("Kayak-polo.info avec mPDF");

        // Construire le footer HTML pour affichage sur toutes les pages
        $footerHTML = '';

        // Sponsor d'abord (en haut du footer)
        if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($visuels['sponsor'])) {
            if (is_file($visuels['sponsor'])) {
                $img = redimImage($visuels['sponsor'], 297, 10, 16, 'C');
                $footerHTML .= '<div style="text-align: center;"><img src="' . $img['image'] . '" style="height: ' . $img['newHauteur'] . 'mm;" /></div>';
            }
        }

        // Page number et date en dessous (plus près du bord bas)
        $footerHTML .= '<table width="100%" style="font-family: Arial; font-size: 8pt; font-style: italic;"><tr>';
        $footerHTML .= '<td width="50%" align="left">Page {PAGENO}</td>';
        $footerHTML .= '<td width="50%" align="right">Edité le ' . date("d/m/Y") . ' à ' . date("H:i") . '</td>';
        $footerHTML .= '</tr></table>';

        $pdf->SetHTMLFooter($footerHTML);

        if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($visuels['sponsor'])) {
            $pdf->SetAutoPageBreak(true, 30);  // Marge basse pour footer sponsor + page/date
        } else {
            $pdf->SetAutoPageBreak(true, 20);  // Marge basse pour footer simple (page/date uniquement)
        }

        $pdf->SetTopMargin(30);
        $pdf->AddPage();

        // Affichage
        $qr_x = 262;

        // mPDF: Define where content should start (after top margin)
        $yStart = 30;

        // mPDF: Disable AutoPageBreak temporarily to prevent images from triggering page breaks
        $pdf->SetAutoPageBreak(false);

        // Bandeau
        if ($arrayCompetition['Bandeau_actif'] == 'O' && isset($visuels['bandeau'])) {
            if (is_file($visuels['bandeau'])) {
                $img = redimImage($visuels['bandeau'], 262, 10, 20, 'C');
                $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
            }
            // KPI + Logo
        } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O' && $arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
            $pdf->Image('img/CNAKPI_small.jpg', 40, 10, 0, 20, 'jpg', "https://www.kayak-polo.info");
            if (is_file($visuels['logo'])) {
                $img = redimImage($visuels['logo'], 262, 10, 20, 'R');
                $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
            }
            // KPI
        } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O') {
            $pdf->Image('img/CNAKPI_small.jpg', 125, 10, 0, 20, 'jpg', "https://www.kayak-polo.info");
            // Logo
        } elseif ($arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
            if (is_file($visuels['logo'])) {
                $img = redimImage($visuels['logo'], 262, 10, 20, 'C');
                $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
            }
        }

        // QRCode
        $qrcode = new QRcode('https://www.kayak-polo.info/kpmatchs.php?Compet=' . $codeCompet . '&Group=' . $arrayCompetition['Code_ref'] . '&Saison=' . $codeSaison, 'L'); // error level : L, M, Q, H
        $qrcode->displayFPDF($pdf, $qr_x, 9, 21);

        // mPDF: Re-enable AutoPageBreak after images (already configured before AddPage)
        // mPDF: Reset all margins and cursor position
        $pdf->SetY($yStart);
        $pdf->SetLeftMargin(15);
        $pdf->SetRightMargin(15);
        $pdf->SetX(15);

        $titreDate = "Saison " . $codeSaison;
        // titre
        $pdf->SetFont('Arial', 'BI', 12);
        $pdf->Cell(137, 5, $titreEvenementCompet, 0, 0, 'L');
        $pdf->Cell(136, 5, $titreDate, 0, 1, 'R');
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(273, 6, "Liste des Matchs", 0, 1, 'C');
        $pdf->Ln(3);

        // mPDF: Ensure cursor is at left margin before loop
        $pdf->SetX(15);

        $heure1 = '';

        foreach ($resultarray as $key => $row) {
            if ($row['Soustitre2'] != '') {
                $row['Code_competition'] = $row['Soustitre2'];
            }
            $phase_match = $row['Phase'];
            if ($row['Libelle'] != '') {
                $libelle = explode(']', $row['Libelle']);
                if ($libelle[1] != '') {
                    $phase_match .= "  |  " . $libelle[1];
                }
                //Codes équipes	
                $EquipesAffectAuto = utyEquipesAffectAutoFR($row['Libelle']);
            }
            if ($row['Id_equipeA'] < 1 && isset($EquipesAffectAuto[0]) && $EquipesAffectAuto[0] != '') {
                $row['EquipeA'] = $EquipesAffectAuto[0];
            }
            if ($row['Id_equipeB'] < 1 && isset($EquipesAffectAuto[1]) && $EquipesAffectAuto[1] != '') {
                $row['EquipeB'] = $EquipesAffectAuto[1];
            }
            if ($row['Arbitre_principal'] != '' && $row['Arbitre_principal'] != '-1') {
                $row['Arbitre_principal'] = utyArbSansNiveau(
                    utyInitialesPrenomArbitre(
                        $row['Arbitre_principal'],
                        $row['Nom_arb_prin'],
                        $row['Prenom_arb_prin'],
                        $row['Matric_arbitre_principal']
                    )
                );
            } elseif (isset($EquipesAffectAuto[2]) && $EquipesAffectAuto[2] != '') {
                $row['Arbitre_principal'] = $EquipesAffectAuto[2];
            }
            if ($row['Arbitre_secondaire'] != '' && $row['Arbitre_secondaire'] != '-1') {
                $row['Arbitre_secondaire'] = utyArbSansNiveau(
                    utyInitialesPrenomArbitre(
                        $row['Arbitre_secondaire'],
                        $row['Nom_arb_sec'],
                        $row['Prenom_arb_sec'],
                        $row['Matric_arbitre_secondaire']
                    )
                );
            } elseif (isset($EquipesAffectAuto[3]) && $EquipesAffectAuto[3] != '') {
                $row['Arbitre_secondaire'] = $EquipesAffectAuto[3];
            }
            // rupture ligne
            if ($orderMatchsKey1 == "Numero_ordre") {
                $rupture = $row['Date_match'];
            } else {
                $rupture = $row[$orderMatchsKey1];
            }

            if ($rupture != $Oldrupture) {
                if ($Oldrupture != '') {
                    $pdf->Cell(273, 3, '', 'T', '1', 'C');
                    $pdf->AddPage();

                    // mPDF: Disable AutoPageBreak temporarily to prevent images from triggering page breaks
                    $pdf->SetAutoPageBreak(false);

                    // Bandeau
                    if ($arrayCompetition['Bandeau_actif'] == 'O' && isset($visuels['bandeau'])) {
                        if (is_file($visuels['bandeau'])) {
                            $img = redimImage($visuels['bandeau'], 297, 10, 20, 'C');
                            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
                        }
                        // KPI + Logo
                    } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O' && $arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
                        $pdf->Image('img/CNAKPI_small.jpg', 10, 10, 0, 20, 'jpg', "https://www.kayak-polo.info");
                        if (is_file($visuels['logo'])) {
                            $img = redimImage($visuels['logo'], 297, 10, 20, 'R');
                            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
                        }
                        // KPI
                    } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O') {
                        $pdf->Image('img/CNAKPI_small.jpg', 125, 10, 0, 20, 'jpg', "https://www.kayak-polo.info");
                        // Logo
                    } elseif ($arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
                        if (is_file($visuels['logo'])) {
                            $img = redimImage($visuels['logo'], 297, 10, 20, 'C');
                            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
                        }
                    }

                    $pdf->SetY($yStart);
                    $pdf->SetLeftMargin(15);  // Ensure left margin is set
                    $pdf->SetX(15);  // Position cursor at left margin
                }
                $Oldrupture = $rupture;
                $pdf->Cell(60, 5, '', '', '0', 'L');
                switch ($orderMatchsKey1) {
                    case "Code_competition":
                        $pdf->SetFont('Arial', 'B', 9);
                        $pdf->Cell(150, 5, utyGetLabelCompetition($rupture) . " (" . $rupture . ")", 'LTBR', '1', 'C');
                        //$pdf->Cell(22,5, '',0,0,'C');
                        $pdf->Cell(8, 5, 'N°', 'LTRB', '0', 'C');
                        $pdf->Cell(16, 5, 'Date', 'TRB', '0', 'C');
                        $pdf->Cell(10, 5, 'Heure', 'TRB', '0', 'C');
                        if ($PhaseLibelle == 1) {
                            $pdf->Cell(52, 5, 'Phase | Match', 'TRB', '0', 'C');
                        } else {
                            $pdf->Cell(52, 5, 'Lieu', 'TRB', '0', 'C');
                        }
                        $pdf->Cell(11, 5, 'Terr.', 'TRB', '0', 'C');
                        $pdf->Cell(35, 5, 'Equipe A', 'TRB', '0', 'C');
                        $pdf->Cell(14, 5, 'Scores', 'TRB', '0', 'C');
                        $pdf->Cell(35, 5, 'Equipe B', 'TRB', 0, 'C');
                        $pdf->Cell(45, 5, 'Arbitre principal', 'TRB', '0', 'C');
                        $pdf->Cell(45, 5, 'Arbitre secondaire', 'TRB', '1', 'C');
                        $pdf->SetFont('Arial', '', 8);
                        break;
                    case "Terrain":
                        $pdf->SetFont('Arial', 'B', 9);
                        $pdf->Cell(150, 5, "Terrain : " . $rupture, 'LTBR', 1, 'C');
                        //$pdf->Cell(22,5, '',0,0,'C');
                        $pdf->Cell(8, 5, 'N°', 'LTRB', '0', 'C');
                        $pdf->Cell(16, 5, 'Date', 'TRB', '0', 'C');
                        $pdf->Cell(10, 5, 'Heure', 'TRB', '0', 'C');
                        $pdf->Cell(17, 5, 'Compét.', 'TRB', '0', 'C');
                        if ($PhaseLibelle == 1) {
                            $pdf->Cell(50, 5, 'Phase | Match', 'TRB', '0', 'C');
                        } else {
                            $pdf->Cell(50, 5, 'Lieu', 'TRB', '0', 'C');
                        }
                        $pdf->Cell(35, 5, 'Equipe A', 'TRB', '0', 'C');
                        $pdf->Cell(14, 5, 'Scores', 'TRB', '0', 'C');
                        $pdf->Cell(35, 5, 'Equipe B', 'TRB', 0, 'C');
                        $pdf->Cell(45, 5, 'Arbitre principal', 'TRB', '0', 'C');
                        $pdf->Cell(45, 5, 'Arbitre secondaire', 'TRB', '1', 'C');
                        $pdf->SetFont('Arial', '', 8);
                        break;
                    default:
                        $pdf->SetFont('Arial', 'B', 9);
                        $pdf->Cell(150, 5, utyDateUsToFrLong($rupture) . ' - ' . html_entity_decode($row['Lieu']), 'LTBR', '1', 'C');
                        //$pdf->Cell(22,5, '',0,0,'C');
                        $pdf->Cell(8, 5, 'N°', 'LTRB', '0', 'C');
                        $pdf->Cell(10, 5, 'Heure', 'TRB', '0', 'C');
                        $pdf->Cell(22, 5, 'Compét.', 'TRB', '0', 'C');
                        if ($PhaseLibelle == 1) {
                            $pdf->Cell(45, 5, 'Phase | Match', 'TRB', '0', 'C');
                        } else {
                            $pdf->Cell(45, 5, 'Lieu', 'TRB', '0', 'C');
                        }
                        $pdf->Cell(12, 5, 'Terrain', 'TRB', '0', 'C');
                        $pdf->Cell(35, 5, 'Equipe A', 'TRB', '0', 'C');
                        $pdf->Cell(14, 5, 'Scores', 'TRB', '0', 'C');
                        $pdf->Cell(35, 5, 'Equipe B', 'TRB', 0, 'C');
                        $pdf->Cell(46, 5, 'Arbitre principal', 'TRB', '0', 'C');
                        $pdf->Cell(46, 5, 'Arbitre secondaire', 'TRB', '1', 'C');
                        break;
                }
            }

            switch ($orderMatchsKey1) {
                case "Code_competition":
                    $pdf->SetFont('Arial', '', 8);
                    //$pdf->Cell(22,5, '',0,0,'C');
                    if ($phase_match === 'Break' || $phase_match === 'Pause') {
                        $pdf->Cell(8, 5, '', 'LTBR', '0', 'C');
                    } else {
                        $pdf->Cell(8, 5, $row['Numero_ordre'], 'LTBR', '0', 'C');
                    }
                    $pdf->Cell(16, 5, utyDateUsToFr($row['Date_match']), 'TBR', '0', 'C');
                    $pdf->Cell(10, 5, $row['Heure_match'], 'TBR', '0', 'C');
                    if ($PhaseLibelle == 1) {
                        $pdf->Cell(52, 5, $phase_match, 'TBR', '0', 'C');
                    } else {
                        $pdf->Cell(52, 5, html_entity_decode($row['Lieu']), 'TRB', '0', 'C');
                    }
                    $pdf->Cell(11, 5, $row['Terrain'], 'TBR', '0', 'C');
                    if ($phase_match === 'Break' || $phase_match === 'Pause') {
                        $pdf->Cell(35, 5, '', 'TBR', '0', 'C');
                    } else {
                        $pdf->Cell(35, 5, $row['EquipeA'], 'TBR', '0', 'C');
                    }
                    if ($row['ScoreA'] != '?' && $row['Validation'] == 'O') {
                        $pdf->Cell(7, 5, $row['ScoreA'], 'TBR', '0', 'C');
                    } else {
                        $pdf->Cell(7, 5, "", 'TBR', '0', 'C');
                    }
                    if ($row['ScoreB'] != '?' && $row['Validation'] == 'O') {
                        $pdf->Cell(7, 5, $row['ScoreB'], 'TBR', '0', 'C');
                    } else {
                        $pdf->Cell(7, 5, "", 'TBR', '0', 'C');
                    }
                    if ($phase_match === 'Break' || $phase_match === 'Pause') {
                        $pdf->Cell(35, 5, '', 'TBR', '0', 'C');
                    } else {
                        $pdf->Cell(35, 5, $row['EquipeB'], 'TBR', 0, 'C');
                    }
                    $pdf->SetFont('Arial', 'I', 6);
                    if ($row['Arbitre_principal'] == '-1' || $phase_match === 'Break' || $phase_match === 'Pause') {
                        $pdf->Cell(45, 5, '', 'TBR', 0, 'C');
                    } else {
                        $pdf->Cell(45, 5, $row['Arbitre_principal'], 'TBR', '0', 'C');
                    }
                    if ($row['Arbitre_secondaire'] == '-1' || $phase_match === 'Break' || $phase_match === 'Pause') {
                        $pdf->Cell(45, 5, '', 'TBR', 1, 'C');
                    } else {
                        $pdf->Cell(45, 5, $row['Arbitre_secondaire'], 'TBR', 1, 'C');
                    }
                    break;
                case "Terrain":
                    $pdf->SetFont('Arial', '', 8);
                    //$pdf->Cell(22,5, '',0,0,'C');
                    if ($phase_match === 'Break' || $phase_match === 'Pause') {
                        $pdf->Cell(8, 5, '', 'LTBR', '0', 'C');
                        $pdf->Cell(16, 5, utyDateUsToFr($row['Date_match']), 'TBR', '0', 'C');
                        $pdf->Cell(10, 5, $row['Heure_match'], 'TBR', '0', 'C');
                        $pdf->Cell(17, 5, '', 'TBR', '0', 'C');
                    } else {
                        $pdf->Cell(8, 5, $row['Numero_ordre'], 'LTBR', '0', 'C');
                        $pdf->Cell(16, 5, utyDateUsToFr($row['Date_match']), 'TBR', '0', 'C');
                        $pdf->Cell(10, 5, $row['Heure_match'], 'TBR', '0', 'C');
                        $pdf->Cell(17, 5, $row['Code_competition'], 'TBR', '0', 'C');
                    }
                    if ($PhaseLibelle == 1) {
                        $pdf->Cell(50, 5, $phase_match, 'TBR', '0', 'C');
                    } else {
                        $pdf->Cell(50, 5, html_entity_decode($row['Lieu']), 'TRB', '0', 'C');
                    }
                    if ($phase_match === 'Break' || $phase_match === 'Pause') {
                        $pdf->Cell(35, 5, '', 'TBR', '0', 'C');
                    } else {
                        $pdf->Cell(35, 5, $row['EquipeA'], 'TBR', '0', 'C');
                    }

                    if ($row['ScoreA'] != '?' && $row['Validation'] == 'O') {
                        $pdf->Cell(7, 5, $row['ScoreA'], 'TBR', '0', 'C');
                    } else {
                        $pdf->Cell(7, 5, "", 'TBR', '0', 'C');
                    }
                    if ($row['ScoreB'] != '?' && $row['Validation'] == 'O') {
                        $pdf->Cell(7, 5, $row['ScoreB'], 'TBR', '0', 'C');
                    } else {
                        $pdf->Cell(7, 5, "", 'TBR', '0', 'C');
                    }
                    if ($phase_match === 'Break' || $phase_match === 'Pause') {
                        $pdf->Cell(35, 5, '', 'TBR', '0', 'C');
                    } else {
                        $pdf->Cell(35, 5, $row['EquipeB'], 'TBR', '0', 'C');
                    }
                    $pdf->SetFont('Arial', 'I', 6);
                    if ($row['Arbitre_principal'] == '-1' || $phase_match === 'Break' || $phase_match === 'Pause') {
                        $pdf->Cell(45, 5, '', 'TBR', 0, 'C');
                    } else {
                        $pdf->Cell(45, 5, $row['Arbitre_principal'], 'TBR', 0, 'C');
                    }
                    if ($row['Arbitre_secondaire'] == '-1' || $phase_match === 'Break' || $phase_match === 'Pause') {
                        $pdf->Cell(45, 5, '', 'TBR', 1, 'C');
                    } else {
                        $pdf->Cell(45, 5, $row['Arbitre_secondaire'], 'TBR', 1, 'C');
                    }
                    break;
                default:
                    $heure2 = $row['Heure_match'];
                    if ($heure1 == $heure2) {
                        $ltbr = '';
                    } else {
                        $ltbr = 'T';
                    }
                    $heure1 = $heure2;
                    $pdf->SetFont('Arial', '', 8);
                    //$pdf->Cell(22,5, '',0,0,'C');
                    if ($phase_match === 'Break' || $phase_match === 'Pause') {
                        $pdf->Cell(8, 5, '', 'LR' . $ltbr, '0', 'C');
                        $pdf->Cell(10, 5, $row['Heure_match'], 'R' . $ltbr, '0', 'C');
                        $pdf->Cell(22, 5, '', 'R' . $ltbr, '0', 'C');
                    } else {
                        $pdf->Cell(8, 5, $row['Numero_ordre'], 'LR' . $ltbr, '0', 'C');
                        $pdf->Cell(10, 5, $row['Heure_match'], 'R' . $ltbr, '0', 'C');
                        $pdf->Cell(22, 5, $row['Code_competition'], 'R' . $ltbr, '0', 'C');
                    }
                    if ($PhaseLibelle == 1) {
                        $pdf->Cell(45, 5, $phase_match, 'R' . $ltbr, '0', 'C');
                    } else {
                        $pdf->Cell(45, 5, html_entity_decode($row['Lieu']), 'R' . $ltbr, '0', 'C');
                    }
                    $pdf->Cell(12, 5, $row['Terrain'], 'R' . $ltbr, '0', 'C');
                    if ($phase_match === 'Break' || $phase_match === 'Pause') {
                        $pdf->Cell(35, 5, '', 'R' . $ltbr, '0', 'C');
                    } else {
                        $pdf->Cell(35, 5, $row['EquipeA'], 'R' . $ltbr, '0', 'C');
                    }
                    if ($row['ScoreA'] != '?' && $row['Validation'] == 'O') {
                        $pdf->Cell(7, 5, $row['ScoreA'], 'R' . $ltbr, '0', 'C');
                    } else {
                        $pdf->Cell(7, 5, "", 'R' . $ltbr, '0', 'C');
                    }
                    if ($row['ScoreB'] != '?' && $row['Validation'] == 'O') {
                        $pdf->Cell(7, 5, $row['ScoreB'], 'R' . $ltbr, '0', 'C');
                    } else {
                        $pdf->Cell(7, 5, "", 'R' . $ltbr, '0', 'C');
                    }
                    if ($phase_match === 'Break' || $phase_match === 'Pause') {
                        $pdf->Cell(35, 5, '', 'R' . $ltbr, '0', 'C');
                    } else {
                        $pdf->Cell(35, 5, $row['EquipeB'], 'R' . $ltbr, '0', 'C');
                    }
                    $pdf->SetFont('Arial', 'I', 6);
                    if ($row['Arbitre_principal'] == '-1' || $phase_match === 'Break' || $phase_match === 'Pause') {
                        $pdf->Cell(46, 5, '', 'R' . $ltbr, 0, 'C');
                    } else {
                        $pdf->Cell(46, 5, $row['Arbitre_principal'], 'R' . $ltbr, 0, 'C');
                    }
                    if ($row['Arbitre_secondaire'] == '-1' || $phase_match === 'Break' || $phase_match === 'Pause') {
                        $pdf->Cell(46, 5, '', 'R' . $ltbr, 1, 'C');
                    } else {
                        $pdf->Cell(46, 5, $row['Arbitre_secondaire'], 'R' . $ltbr, 1, 'C');
                    }
                    break;
            }
        }
        $pdf->Cell(273, 3, '', 'T', '1', 'C');
        $pdf->Output('Liste matchs' . '.pdf', \Mpdf\Output\Destination::INLINE);
    }
}

$page = new PdfListeMatchs();
