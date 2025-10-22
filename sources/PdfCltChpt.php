<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

require_once('commun/MyPDF.php');

require_once('lib/qrcode/qrcode.class.php');

// Gestion de la Feuille de Classement - Migré vers mPDF via MyPDF

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
        // PHP 8 fix: Type casting pour éviter "Unsupported operand types: int - string"
        $qualif = (int)($arrayCompetition['Qualifies'] ?? 0);
        $elim = (int)($arrayCompetition['Elimines'] ?? 0);

        $visuels = utyGetVisuels($arrayCompetition, FALSE);

        // Langue
        $langue = parse_ini_file("commun/MyLang.ini", true);
        // PHP 8 fix: Initialize En_actif to avoid undefined array key
        $arrayCompetition['En_actif'] = '';
        if (utyGetGet('lang') == 'en') {
            $arrayCompetition['En_actif'] = 'O';
        }

        if ($arrayCompetition['En_actif'] == 'O') {
            $lang = $langue['en'];
        } else {
            $lang = $langue['fr'];
        }

        //Création avec MyPDF (wrapper mPDF compatible FPDF)
        $pdf = new MyPDF('L');
        $pdf->SetTitle("Classement general");
        $pdf->SetAuthor("kayak-polo.info");
        $pdf->SetCreator("kayak-polo.info avec mPDF");

        // Construire le header HTML pour affichage sur toutes les pages
        $headerHTML = '<div style="text-align: center;">';

        // Bandeau
        if ($arrayCompetition['Bandeau_actif'] == 'O' && isset($visuels['bandeau'])) {
            $img = redimImage($visuels['bandeau'], 265, 10, 20, 'C');
            $headerHTML .= '<img src="' . $img['image'] . '" style="height: ' . $img['newHauteur'] . 'mm;" />';
        } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O' && $arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
            // KPI + Logo
            $img = redimImage($visuels['logo'], 265, 10, 20, 'R');
            $headerHTML .= '<table width="100%"><tr>';
            $headerHTML .= '<td width="33%" align="left"><img src="img/CNAKPI_small.jpg" style="height: 20mm;" /></td>';
            $headerHTML .= '<td width="34%"></td>';
            $headerHTML .= '<td width="33%" align="right"><img src="' . $img['image'] . '" style="height: ' . $img['newHauteur'] . 'mm;" /></td>';
            $headerHTML .= '</tr></table>';
        } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O') {
            // KPI seul
            $headerHTML .= '<img src="img/CNAKPI_small.jpg" style="height: 20mm;" />';
        } elseif ($arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
            // Logo seul
            $img = redimImage($visuels['logo'], 265, 10, 20, 'C');
            $headerHTML .= '<img src="' . $img['image'] . '" style="height: ' . $img['newHauteur'] . 'mm;" />';
        }

        $headerHTML .= '</div>';
        $pdf->SetHTMLHeader($headerHTML);

        // Construire le footer HTML pour affichage sur toutes les pages
        if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($visuels['sponsor'])) {
            $img = redimImage($visuels['sponsor'], 297, 10, 16, 'C');
            $footerHTML = '<div style="text-align: center;"><img src="' . $img['image'] . '" style="height: ' . $img['newHauteur'] . 'mm;" /></div>';
            $pdf->SetHTMLFooter($footerHTML);
        }

        // Configurer les marges pour éviter chevauchement avec header/footer
        $pdf->SetTopMargin(35);  // Marge haute pour laisser place au bandeau/logo

        $pdf->AddPage();

        // Pattern 8: Désactiver AutoPageBreak temporairement pour QRCode
        $pdf->SetAutoPageBreak(false);

        // QRCode en haut à droite - displayFPDF fonctionne avec MyPDF !
        $qr_x = 265;
        $qrcode = new QRcode('https://www.kayak-polo.info/Classements.php?Compet=' . $codeCompet . '&Group=' . $arrayCompetition['Code_ref'] . '&Saison=' . $codeSaison, 'L');
        $qrcode->displayFPDF($pdf, $qr_x, 9, 21);

        // Pattern 8: Réactiver AutoPageBreak avec marges appropriées
        if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($visuels['sponsor'])) {
            $pdf->SetAutoPageBreak(true, 30);  // Marge basse pour footer sponsor
        } else {
            $pdf->SetAutoPageBreak(true, 15);
        }

        // titre - le curseur est déjà positionné par TopMargin
        $pdf->Ln(2);

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

        $pdf->Output('Classement ' . $codeCompet . '.pdf', \Mpdf\Output\Destination::INLINE);
    }
}

$page = new FeuilleCltNiveau();
