<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

require_once('commun/MyPDF.php');

require_once('lib/qrcode/qrcode.class.php');

// Gestion de la Feuille de Classement par Journée - Migré vers mPDF
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
        $pdf = new MyPDF('P');
        $pdf->SetTitle("Classement par journee");
        $pdf->SetAuthor("kayak-polo.info");
        $pdf->SetCreator("kayak-polo.info avec mPDF");

        // Construire le header HTML pour affichage sur toutes les pages
        $headerHTML = '<div style="text-align: center;">';

        // Bandeau
        if ($arrayCompetition['Bandeau_actif'] == 'O' && isset($visuels['bandeau'])) {
            $img = redimImage($visuels['bandeau'], 210, 10, 16, 'C');
            $headerHTML .= '<img src="' . $img['image'] . '" style="height: ' . $img['newHauteur'] . 'mm;" />';
        } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O' && $arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
            // KPI + Logo
            $img = redimImage($visuels['logo'], 210, 10, 16, 'R');
            $headerHTML .= '<table width="100%"><tr>';
            $headerHTML .= '<td width="33%" align="left"><img src="img/CNAKPI_small.jpg" style="height: 16mm;" /></td>';
            $headerHTML .= '<td width="34%"></td>';
            $headerHTML .= '<td width="33%" align="right"><img src="' . $img['image'] . '" style="height: ' . $img['newHauteur'] . 'mm;" /></td>';
            $headerHTML .= '</tr></table>';
        } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O') {
            // KPI seul
            $headerHTML .= '<img src="img/CNAKPI_small.jpg" style="height: 16mm;" />';
        } elseif ($arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
            // Logo seul
            $img = redimImage($visuels['logo'], 210, 10, 16, 'C');
            $headerHTML .= '<img src="' . $img['image'] . '" style="height: ' . $img['newHauteur'] . 'mm;" />';
        }

        $headerHTML .= '</div>';
        $pdf->SetHTMLHeader($headerHTML);

        // Construire le footer HTML pour affichage sur toutes les pages
        if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($visuels['sponsor'])) {
            $img = redimImage($visuels['sponsor'], 210, 10, 16, 'C');
            $footerHTML = '<div style="text-align: center;"><img src="' . $img['image'] . '" style="height: ' . $img['newHauteur'] . 'mm;" /></div>';
            $pdf->SetHTMLFooter($footerHTML);
        }

        // Configurer les marges pour éviter chevauchement avec header/footer
        $pdf->SetTopMargin(30);  // Marge haute pour laisser place au bandeau/logo

        $pdf->AddPage();

        // Pattern 8: Désactiver AutoPageBreak temporairement pour QRCode
        $pdf->SetAutoPageBreak(false);

        // QRCode en bas à droite - displayFPDF fonctionne avec MyPDF !
        $qrcode = new QRcode('https://www.kayak-polo.info/Classements.php?Compet=' . $codeCompet . '&Group=' . $arrayCompetition['Code_ref'] . '&Saison=' . $codeSaison, 'L');
        $qrcode->displayFPDF($pdf, 177, 240, 24);

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
                $pdf->Cell(61, 4, utyDateUsToFr($row['Date_debut']) . ' - ' . $row['Lieu'], 'B', 0, 'L');
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

        $pdf->Output('Classement par journee ' . $codeCompet . '.pdf', \Mpdf\Output\Destination::INLINE);
    }
}

$page = new FeuilleCltNiveauJournee();
