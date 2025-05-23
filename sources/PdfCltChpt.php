<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

require('lib/fpdf/fpdf.php');

require_once('lib/qrcode/qrcode.class.php');

// Gestion de la Feuille de Classement

class FeuilleCltNiveau extends MyPage
{

    function __construct()
    {
        parent::__construct();

        $myBdd = new MyBdd();

        $codeCompet = utyGetSession('codeCompet', '');
        //Saison
        $codeSaison = $myBdd->GetActiveSaison();
        $titreDate = "Saison " . $codeSaison;

        $arrayCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);

        $titreCompet = 'Compétition : ' . $arrayCompetition['Libelle'] . ' (' . $codeCompet . ')';
        $qualif = $arrayCompetition['Qualifies'];
        $elim = $arrayCompetition['Elimines'];

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
        $pdf = new FPDF('L');
        $pdf->Open();
        $pdf->SetTitle("Classement general");

        $pdf->SetAuthor("kayak-polo.info");
        $pdf->SetCreator("kayak-polo.info avec FPDF");
        $pdf->AddPage();
        if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($visuels['sponsor'])) {
            $pdf->SetAutoPageBreak(true, 30);
        } else {
            $pdf->SetAutoPageBreak(true, 15);
        }

        // Affichage
        $qr_x = 265;
        // Bandeau
        if ($arrayCompetition['Bandeau_actif'] == 'O' && isset($visuels['bandeau'])) {
            $img = redimImage($visuels['bandeau'], 265, 10, 20, 'C');
            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
            // KPI + Logo    
        } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O' && $arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
            $pdf->Image('img/CNAKPI_small.jpg', 40, 10, 0, 20, 'jpg', "https://www.kayak-polo.info");
            $img = redimImage($visuels['logo'], 265, 10, 20, 'R');
            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
            // KPI
        } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O') {
            $pdf->Image('img/CNAKPI_small.jpg', 125, 10, 0, 20, 'jpg', "https://www.kayak-polo.info");
            // Logo
        } elseif ($arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
            $img = redimImage($visuels['logo'], 265, 10, 20, 'C');
            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
        }
        // Sponsor
        if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($visuels['sponsor'])) {
            $img = redimImage($visuels['sponsor'], 297, 10, 16, 'C');
            $pdf->Image($img['image'], $img['positionX'], 184, 0, $img['newHauteur']);
        }

        // QRCode
        $qrcode = new QRcode('https://www.kayak-polo.info/Classements.php?Compet=' . $codeCompet . '&Group=' . $arrayCompetition['Code_ref'] . '&Saison=' . $codeSaison, 'L'); // error level : L, M, Q, H
        $qrcode->displayFPDF($pdf, $qr_x, 9, 21);

        // titre
        $pdf->Ln(22);

        $pdf->SetFont('Arial', 'B', 14);
        if ($arrayCompetition['Titre_actif'] == 'O') {
            $pdf->Cell(273, 5, $arrayCompetition['Libelle'], 0, 1, 'C');
        } else {
            $pdf->Cell(273, 5, $arrayCompetition['Soustitre'], 0, 1, 'C');
        }

        $pdf->Ln(4);
        if ($arrayCompetition['Soustitre2'] != '') {
            $pdf->Cell(273, 5, $arrayCompetition['Soustitre2'], 0, 1, 'C');
        }
        $pdf->Ln(4);
        $pdf->SetFont('Arial', 'BI', 10);
        $pdf->Cell(273, 5, $lang['CLASSEMENT_GENERAL'], 0, 0, 'C');
        $pdf->Ln(10);

        //données

        $sql = "SELECT Id, Libelle, Code_club, Clt_publi, Pts_publi, J_publi, 
            G_publi, N_publi, P_publi, F_publi, Plus_publi, Moins_publi, Diff_publi, 
            PtsNiveau_publi, CltNiveau_publi 
            FROM kp_competition_equipe 
            WHERE Code_compet = ? 
            AND Code_saison = ? 
            AND Clt_publi != 0 
            ORDER BY Clt_publi ASC, Diff_publi DESC ";
        $result = $myBdd->pdo->prepare($sql);
        $result->execute(array($codeCompet, $codeSaison));
        $num_results = $result->rowCount();

        // recalcul des éliminés
        $elim = $num_results - $elim;

        $pdf->SetFont('Arial', 'BI', 11);

        $pdf->Cell(16, 5, '', 0, 0);
        $pdf->Cell(20, 5, $lang['Clt'], 'B', 0, 'C');
        $pdf->Cell(55, 5, $lang['Equipe'], 'B', 0, 'L');
        $pdf->Cell(20, 5, $lang['Pts'], 'B', 0, 'C');
        $pdf->Cell(18, 5, $lang['J'], 'B', 0, 'C');
        $pdf->Cell(18, 5, $lang['G'], 'B', 0, 'C');
        $pdf->Cell(18, 5, $lang['N'], 'B', 0, 'C');
        $pdf->Cell(18, 5, $lang['P'], 'B', 0, 'C');
        $pdf->Cell(18, 5, $lang['F'], 'B', 0, 'C');
        $pdf->Cell(18, 5, '+', 'B', 0, 'C');
        $pdf->Cell(18, 5, '-', 'B', 0, 'C');
        $pdf->Cell(20, 5, '+/-', 'B', 1, 'C');

        $i = 0;
        while ($row = $result->fetch()) {
            $separation = 0;
            //Séparation qualifiés
            if (($i + 1) > $qualif && $qualif != 0) {
                $pdf->Cell(16, 5, '', 0, 0);
                $pdf->Cell(241, 1, '', 0, 1);
                $qualif = 0;
                $separation = 1;
            }
            //Séparation éliminés
            if (($i + 1) > $elim && $elim != 0) {
                if ($separation != 1) {
                    $pdf->Cell(16, 5, '', 0, 0);
                    $pdf->Cell(241, 1, '', 0, 1);
                }
                $elim = 0;
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

            $pdf->Cell(16, 6, '', 0, 0);
            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(20, 6, $row['Clt_publi'] . '.', 'B', 0, 'C');
            $pdf->Cell(55, 6, $row['Libelle'], 'B', 0, 'L');
            $pdf->Cell(20, 6, $pts, 'B', 0, 'C');
            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(18, 6, $row['J_publi'], 'B', 0, 'C');
            $pdf->Cell(18, 6, $row['G_publi'], 'B', 0, 'C');
            $pdf->Cell(18, 6, $row['N_publi'], 'B', 0, 'C');
            $pdf->Cell(18, 6, $row['P_publi'], 'B', 0, 'C');
            $pdf->Cell(18, 6, $row['F_publi'], 'B', 0, 'C');
            $pdf->Cell(18, 6, $row['Plus_publi'], 'B', 0, 'C');
            $pdf->Cell(18, 6, $row['Moins_publi'], 'B', 0, 'C');
            $pdf->Cell(20, 6, $row['Diff_publi'], 'B', 1, 'C');
            $i++;
        }
        $pdf->SetFont('Arial', 'I', 8);
        if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($visuels['sponsor'])) {
            $pdf->SetXY(250, 175);
        } else {
            $pdf->SetXY(250, 185);
        }
        if ($lang == $langue['en']) {
            $pdf->Write(4, date('Y-m-d H:i', strtotime($_SESSION['tzOffset'])));
        } else {
            $pdf->Write(4, date('d/m/Y à H:i', strtotime($_SESSION['tzOffset'])));
        }
        $pdf->Output('Classement ' . $codeCompet . '.pdf', 'I');
    }
}

$page = new FeuilleCltNiveau();
