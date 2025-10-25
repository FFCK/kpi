<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');
require_once('../commun/MyPDF.php');

// Gestion de la Feuille de Classement par Niveau - Migration mPDF
class FeuilleCltNiveauNiveau extends MyPage
{
    function __construct()
    {
        parent::__construct();

        $myBdd = new MyBdd();
        $codeCompet = utyGetSession('codeCompet', '');
        $codeSaison = $myBdd->GetActiveSaison();
        $codeSaison = utyGetGet('S', $codeSaison);
        $titreDate = "Saison " . $codeSaison;
        $arrayCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);
        $visuels = utyGetVisuels($arrayCompetition, FALSE);

        // Création PDF avec MyPDF (mPDF wrapper)
        $pdf = new MyPDF('P');
        $pdf->SetTitle("Classement par niveau");
        $pdf->SetAuthor("kayak-polo.info");
        $pdf->SetCreator("kayak-polo.info avec mPDF");

        // Pattern 8: Images décoratives en arrière-plan
        $yStart = 30;

        // Désactiver AutoPageBreak avant images décoratives
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();

        // Bandeau
        if (($arrayCompetition['Bandeau_actif'] ?? '') == 'O' && isset($visuels['bandeau'])) {
            $img = redimImage($visuels['bandeau'], 210, 10, 16, 'C');
            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
        } elseif (($arrayCompetition['Kpi_ffck_actif'] ?? '') == 'O' && ($arrayCompetition['Logo_actif'] ?? '') == 'O' && isset($visuels['logo'])) {
            $pdf->Image('../img/CNAKPI_small.jpg', 10, 10, 0, 16, 'jpg', "https://www.kayak-polo.info");
            $img = redimImage($visuels['logo'], 210, 10, 16, 'R');
            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
        } elseif (($arrayCompetition['Kpi_ffck_actif'] ?? '') == 'O') {
            $pdf->Image('../img/CNAKPI_small.jpg', 84, 10, 0, 16, 'jpg', "https://www.kayak-polo.info");
        } elseif (($arrayCompetition['Logo_actif'] ?? '') == 'O' && isset($visuels['logo'])) {
            $img = redimImage($visuels['logo'], 210, 10, 16, 'C');
            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
        }
        // Sponsor
        if (($arrayCompetition['Sponsor_actif'] ?? '') == 'O' && isset($visuels['sponsor'])) {
            $img = redimImage($visuels['sponsor'], 210, 10, 16, 'C');
            $pdf->Image($img['image'], $img['positionX'], 267, 0, $img['newHauteur']);
        }

        // Footer HTML pour numéro de page
        $footerHTML = '<div style="text-align:center;font-family:Arial;font-size:8pt;font-style:italic;margin-top:2mm;">Page {PAGENO}</div>';
        $pdf->SetHTMLFooter($footerHTML);

        // Réactiver AutoPageBreak avec marge basse adaptée
        $pdf->SetAutoPageBreak(true, 15);

        // Positionner le curseur pour le contenu (Pattern 8)
        $pdf->SetY($yStart);
        $pdf->SetLeftMargin(10);
        $pdf->SetRightMargin(10);
        $pdf->SetX(10);

        // titre
        $pdf->SetFont('Arial', 'BI', 9);
        $pdf->Cell(95, 5, "Compétition à élimination", 0, 0, 'L');
        $pdf->Cell(95, 5, $titreDate, 0, 1, 'R');
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(190, 5, utyGetLabelCompetition($codeCompet), 0, 1, 'C');
        $pdf->Cell(190, 5, "Classement par niveau", 0, 1, 'C');

        $pdf->SetFont('Arial', 'BI', 8);
        $pdf->Cell(95, 5, "Edité le " . date("d/m/Y") . " à " . date("H:i", strtotime($_SESSION['tzOffset'] ?? '')), 0, 0, 'L');
        $pdf->Cell(95, 5, "Classement provisoire", 0, 1, 'R');

        // données

        $sql = "SELECT a.Id, a.Libelle, a.Code_club, b.Niveau, b.CltNiveau, b.Pts, b.J, b.G, 
            b.N, b.P, b.F, b.Plus, b.Moins, b.Diff, b.PtsNiveau, b.CltNiveau 
            FROM kp_competition_equipe a, kp_competition_equipe_niveau b 
            WHERE a.Id = b.Id 
            AND a.Code_compet = ? 
            AND a.Code_saison = ? 
            ORDER BY b.Niveau, b.CltNiveau, b.Diff DESC ";
        $result = $myBdd->pdo->prepare($sql);
        $result->execute(array($codeCompet, $codeSaison));

        $niveau = -1;
        while ($row = $result->fetch()) {
            if ($row['Niveau'] != $niveau) {
                $niveau = $row['Niveau'];

                $pdf->Ln(5);
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->Cell(100, 5, "Niveau " . $niveau, 'LTBR', 0, 'C');
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(10, 5, "Pts", 'LTBR', 0, 'C');
                $pdf->Cell(10, 5, "J", 'LTBR', 0, 'C');
                $pdf->Cell(10, 5, "G", 'LTBR', 0, 'C');
                $pdf->Cell(10, 5, "N", 'LTBR', 0, 'C');
                $pdf->Cell(10, 5, "P", 'LTBR', 0, 'C');
                $pdf->Cell(10, 5, "F", 'LTBR', 0, 'C');
                $pdf->Cell(10, 5, "Plus", 'LTBR', 0, 'C');
                $pdf->Cell(10, 5, "Moins", 'LTBR', 0, 'C');
                $pdf->Cell(10, 5, "Diff", 'LTBR', 1, 'C');
            }

            $pts = $row['Pts'];
            $len = strlen($pts);
            if ($len > 2) {
                if (substr($pts, $len - 2, 2) == '00')
                    $pts = substr($pts, 0, $len - 2);
                else
                    $pts = substr($pts, 0, $len - 2) . '.' . substr($pts, $len - 2, 2);
            }

            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(20, 5, $row['CltNiveau'], 'LTBR', 0, 'C');
            $pdf->Cell(80, 5, $row['Libelle'], 'RTB', 0, 'C');
            $pdf->Cell(10, 5, $pts, 'LTBR', 0, 'C');
            $pdf->Cell(10, 5, $row['J'], 'LTBR', 0, 'C');
            $pdf->Cell(10, 5, $row['G'], 'LTBR', 0, 'C');
            $pdf->Cell(10, 5, $row['N'], 'LTBR', 0, 'C');
            $pdf->Cell(10, 5, $row['P'], 'LTBR', 0, 'C');
            $pdf->Cell(10, 5, $row['F'], 'LTBR', 0, 'C');
            $pdf->Cell(10, 5, $row['Plus'], 'LTBR', 0, 'C');
            $pdf->Cell(10, 5, $row['Moins'], 'LTBR', 0, 'C');
            $pdf->Cell(10, 5, $row['Diff'], 'LTBR', 1, 'C');
        }

        $pdf->Output('Classement par niveau ' . $codeCompet . '.pdf', \Mpdf\Output\Destination::INLINE);
    }
}

$page = new FeuilleCltNiveauNiveau();
