<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

require('lib/fpdf/fpdf.php');

require_once('lib/qrcode/qrcode.class.php');

// Gestion de la Feuille de Classement par Journee
class FeuilleCltNiveauJournee extends MyPage
{

    function __construct()
    {
        parent::__construct();
        $myBdd = new MyBdd();

        $codeCompet = utyGetSession('codeCompet', '');
        $codeSaison = $myBdd->GetActiveSaison();
        //Saison

        $arrayCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);
        $visuels = utyGetVisuels($arrayCompetition, FALSE);

        // Langue
        $langue = parse_ini_file("commun/MyLang.ini", true);
        if (utyGetGet('lang') == 'en') {
            $arrayCompetition['En_actif'] = 'O';
        } elseif (utyGetGet('lang') == 'fr') {
            $arrayCompetition['En_actif'] = '';
        }

        if ($arrayCompetition['En_actif'] == 'O') {
            $lang = $langue['en'];
        } else {
            $lang = $langue['fr'];
        }

        //Création
        $pdf = new FPDF('P');
        $pdf->Open();
        $pdf->SetTitle("Classement par journee");

        $pdf->SetAuthor("kayak-polo.info");
        $pdf->SetCreator("kayak-polo.info");
        $pdf->AddPage();
        if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($visuels['sponsor'])) {
            $pdf->SetAutoPageBreak(true, 30);
        } else {
            $pdf->SetAutoPageBreak(true, 15);
        }

        // Bandeau
        if ($arrayCompetition['Bandeau_actif'] == 'O' && isset($visuels['bandeau'])) {
            $img = redimImage($visuels['bandeau'], 210, 10, 16, 'C');
            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
            // KPI + Logo    
        } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O' && $arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
            $pdf->Image('img/CNAKPI_small.jpg', 10, 10, 0, 16, 'jpg', "https://www.kayak-polo.info");
            $img = redimImage($visuels['logo'], 210, 10, 16, 'R');
            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
            // KPI
        } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O') {
            $pdf->Image('img/CNAKPI_small.jpg', 84, 10, 0, 16, 'jpg', "https://www.kayak-polo.info");
            // Logo
        } elseif ($arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
            $img = redimImage($visuels['logo'], 210, 10, 16, 'C');
            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
        }
        // Sponsor
        if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($visuels['sponsor'])) {
            $img = redimImage($visuels['sponsor'], 210, 10, 16, 'C');
            $pdf->Image($img['image'], $img['positionX'], 267, 0, $img['newHauteur']);
        }

        // QRCode
        $qrcode = new QRcode('https://www.kayak-polo.info/Classements.php?Compet=' . $codeCompet . '&Group=' . $arrayCompetition['Code_ref'] . '&Saison=' . $codeSaison, 'L'); // error level : L, M, Q, H
        $qrcode->displayFPDF($pdf, 177, 240, 24);

        // titre
        $pdf->Ln(22);
        $pdf->SetFont('Arial', 'B', 14);
        if ($arrayCompetition['Titre_actif'] == 'O') {
            $pdf->Cell(190, 5, $arrayCompetition['Libelle'], 0, 1, 'C');
        } else {
            $pdf->Cell(190, 5, $arrayCompetition['Soustitre'], 0, 1, 'C');
        }

        if ($arrayCompetition['Soustitre2'] != '') {
            $pdf->Cell(190, 5, $arrayCompetition['Soustitre2'], 0, 1, 'C');
        }

        $pdf->Ln(4);

        $pdf->SetFont('Arial', 'BI', 10);
        $pdf->Cell(190, 5, $lang['CLASSEMENT_PAR_JOURNEE'], 0, 0, 'C');

        $pdf->Ln(4);

        // données
        $myBdd = new MyBdd();

        $sql = "SELECT a.Id, a.Libelle, a.Code_club, b.Id_journee, b.Clt_publi, 
            b.Pts_publi, b.J_publi, b.G_publi, b.N_publi, b.P_publi, b.F_publi, 
            b.Plus_publi, b.Moins_publi, b.Diff_publi, b.PtsNiveau_publi, 
            b.CltNiveau_publi, c.Date_debut, c.Lieu 
            FROM kp_competition_equipe a, 
            kp_competition_equipe_journee b 
            JOIN kp_journee c ON (b.Id_journee = c.Id) 
            WHERE a.Id = b.Id 
            AND c.Code_competition = ? 
            AND c.Code_saison = ? 
            ORDER BY c.Date_debut, c.Lieu, b.Clt_publi, b.Diff_publi, b.Plus_publi ";
        $result = $myBdd->pdo->prepare($sql);
        $result->execute(array($codeCompet, $codeSaison));

        $idJournee = 0;
        while ($row = $result->fetch()) {
            if ($row['Id_journee'] != $idJournee) {
                $idJournee = $row['Id_journee'];

                $pdf->Ln(5);
                $pdf->Cell(26, 4, '', 0, 0, 'C');
                $pdf->SetFont('Arial', 'BI', 9);
                $pdf->Cell(61, 4, utyDateUsToFr($row['Date_debut']) . ' - ' . $row['Lieu'], 'B', 0, 'L'); //     "JOURNEE ".$codeCompet.'/'.$idJournee.'/'.
                $pdf->SetFont('Arial', 'BI', 9);
                $pdf->Cell(8, 4, "Pts", 'B', 0, 'C');
                $pdf->Cell(7, 4, "J", 'B', 0, 'C');
                $pdf->Cell(7, 4, "G", 'B', 0, 'C');
                $pdf->Cell(7, 4, "N", 'B', 0, 'C');
                $pdf->Cell(7, 4, "P", 'B', 0, 'C');
                $pdf->Cell(7, 4, "F", 'B', 0, 'C');
                $pdf->Cell(8, 4, "+", 'B', 0, 'C');
                $pdf->Cell(8, 4, "-", 'B', 0, 'C');
                $pdf->Cell(8, 4, "+/-", 'B', 1, 'C');
            }

            $pts = $row['Pts_publi'];
            $len = strlen($pts);
            if ($len > 2) {
                if (substr($pts, $len - 2, 2) == '00') {
                    $pts = substr($pts, 0, $len - 2);
                } else {
                    $pts = substr($pts, 0, $len - 2) . '.' . substr($pts, $len - 2, 2);
                }
            }

            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(26, 4, '', 0, 0, 'C');
            $pdf->Cell(61, 4, $row['Clt_publi'] . '. ' . $row['Libelle'], 'B', 0, 'L');
            $pdf->Cell(8, 4, $pts, 'B', 0, 'C');
            $pdf->Cell(7, 4, $row['J_publi'], 'B', 0, 'C');
            $pdf->Cell(7, 4, $row['G_publi'], 'B', 0, 'C');
            $pdf->Cell(7, 4, $row['N_publi'], 'B', 0, 'C');
            $pdf->Cell(7, 4, $row['P_publi'], 'B', 0, 'C');
            $pdf->Cell(7, 4, $row['F_publi'], 'B', 0, 'C');
            $pdf->Cell(8, 4, $row['Plus_publi'], 'B', 0, 'C');
            $pdf->Cell(8, 4, $row['Moins_publi'], 'B', 0, 'C');
            $pdf->Cell(8, 4, $row['Diff_publi'], 'B', 1, 'C');
        }

        $pdf->Output('Classement par journee ' . $codeCompet . '.pdf', 'I');
    }
}

$page = new FeuilleCltNiveauJournee();
