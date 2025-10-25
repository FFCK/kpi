<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');
require_once('../commun/MyPDF.php');

// Gestion de la Feuille de Classement
class FeuilleCltNiveau extends MyPage
{
    function __construct()
    {
        parent::__construct();
        $myBdd = new MyBdd();

        $codeCompet = utyGetSession('codeCompet', '');
        $codeCompet = utyGetGet('codeCompet', $codeCompet);
        //Saison
        $codeSaison = $myBdd->GetActiveSaison();
        $codeSaison = utyGetGet('S', $codeSaison);
        $arrayCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);

        $qualif = (int)($arrayCompetition['Qualifies'] ?? 0);
        $elim = (int)($arrayCompetition['Elimines'] ?? 0);

        $visuels = utyGetVisuels($arrayCompetition, TRUE);

        // Langue
        $langue = parse_ini_file("../commun/MyLang.ini", true);
        if (utyGetGet('lang') == 'en') {
            $arrayCompetition['En_actif'] = 'O';
        } elseif (utyGetGet('lang') == 'fr') {
            $arrayCompetition['En_actif'] = '';
        }

        if (($arrayCompetition['En_actif'] ?? '') == 'O') {
            $lang = $langue['en'];
        } else {
            $lang = $langue['fr'];
        }

        // Création PDF avec MyPDF (mPDF wrapper)
        $pdf = new MyPDF('P');
        $pdf->SetTitle("Classement");
        $pdf->SetAuthor("kayak-polo.info");
        $pdf->SetCreator("kayak-polo.info");

        // Pattern 8: Images décoratives en arrière-plan
        $yStart = 44;

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
        $hasSponsor = false;
        if (($arrayCompetition['Sponsor_actif'] ?? '') == 'O' && isset($visuels['sponsor'])) {
            $img = redimImage($visuels['sponsor'], 210, 10, 16, 'C');
            $pdf->Image($img['image'], $img['positionX'], 267, 0, $img['newHauteur']);
            $hasSponsor = true;
        }

        // QRCode (optionnel, voir PdfCltNiveau.php)
        // $qrcode = new QRcode('https://www.kayak-polo.info/kpclassements.php?Compet=' . $codeCompet . '&Group=' . $arrayCompetition['Code_ref'] . '&Saison=' . $codeSaison, 'L');
        // $qrcode->displayFPDF($pdf, 177, 238, 24);

        // Footer HTML pour sponsor + date/heure en dessous
        if ($hasSponsor) {
            $footerHTML = '<div style="text-align: center;">'
                . '<img src="' . $img['image'] . '" style="height: ' . $img['newHauteur'] . 'mm;" /><br/>'
                . '<span style="font-family:Arial;font-size:8pt;font-style:italic;">'
                . (($lang == $langue['en'])
                    ? date('Y-m-d H:i', strtotime($_SESSION['tzOffset'] ?? ''))
                    : date('d/m/Y à H:i', strtotime($_SESSION['tzOffset'] ?? '')))
                . '</span></div>';
            $pdf->SetHTMLFooter($footerHTML);
            $pdf->SetAutoPageBreak(true, 30);
        } else {
            $footerHTML = '<div style="text-align:center;font-family:Arial;font-size:8pt;font-style:italic;margin-top:2mm;">'
                . (($lang == $langue['en'])
                    ? date('Y-m-d H:i', strtotime($_SESSION['tzOffset'] ?? ''))
                    : date('d/m/Y à H:i', strtotime($_SESSION['tzOffset'] ?? '')))
                . '</div>';
            $pdf->SetHTMLFooter($footerHTML);
            $pdf->SetAutoPageBreak(true, 15);
        }

        // --- Correction Pattern 8 pour toutes les pages ---
        // Utiliser SetTopMargin($yStart) AVANT AddPage() pour toutes les pages
        $pdf->SetTopMargin($yStart);

        // Positionner le curseur pour le contenu (Pattern 8)
        $pdf->SetY($yStart);
        $pdf->SetLeftMargin(10);
        $pdf->SetRightMargin(10);
        $pdf->SetX(10);

        // titre
        $pdf->Ln(0);
        $pdf->SetFont('Arial', 'B', 14);
        if (($arrayCompetition['Titre_actif'] ?? '') == 'O') {
            $pdf->Cell(190, 5, $arrayCompetition['Libelle'], 0, 1, 'C');
        } else {
            $pdf->Cell(190, 5, $arrayCompetition['Soustitre'] ?? '', 0, 1, 'C');
        }
        if (($arrayCompetition['Soustitre2'] ?? '') != '') {
            $pdf->Cell(190, 5, $arrayCompetition['Soustitre2'], 0, 1, 'C');
        }
        $pdf->Ln(4);
        $pdf->SetFont('Arial', 'BI', 10);
        $pdf->Cell(190, 5, $lang['CLASSEMENT_PROVISOIRE'] ?? 'Classement provisoire', 0, 0, 'C');
        $pdf->Ln(10);

        //données
        $sql = "SELECT Id, Libelle, Code_club, Clt, Pts, J, G, N, P, F, Plus, Moins, 
            Diff, PtsNiveau, CltNiveau 
            FROM kp_competition_equipe 
            WHERE Code_compet = ? 
            AND Code_saison = ? 
            ORDER BY CltNiveau, Diff DESC ";
        $result = $myBdd->pdo->prepare($sql);
        $result->execute(array($codeCompet, $codeSaison));
        $num_results = $result->rowCount();

        // recalcul des éliminés
        $elim = $num_results - $elim;
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->Cell(55, 6, '', '', 0, 'L');
        $pdf->Cell(30, 6, '#', 0, 0, 'C');
        $pdf->Cell(10, 5, '', 0, '0', 'C'); //Pays
        $pdf->Cell(60, 6, $lang['Equipe'], 0, 1, 'L');
        $pdf->Ln(4);

        $i = 0;
        while ($row = $result->fetch()) {
            $separation = 0;
            //Séparation qualifiés
            if (($i + 1) > $qualif && $qualif != 0) {
                $pdf->Ln(2);
                $qualif = 0;
                $separation = 1;
            }
            //Séparation éliminés
            if (($i + 1) > $elim && $elim != 0) {
                if ($separation != 1) {
                    $pdf->Ln(2);
                }
                $elim = 0;
            }
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(55, 6, '', 0, '0', 'L');
            // médailles - Pattern 5: Sauvegarder position
            if (($row['CltNiveau'] ?? 0) <= 3 && ($row['CltNiveau'] ?? 0) != 0 && (($arrayCompetition['Code_tour'] ?? '') == 'F')) {
                $savedY = $pdf->y;
                $savedX = $pdf->x;
                $pdf->image('../img/medal' . $row['CltNiveau'] . '.gif', $pdf->x, $pdf->y + 1, 3, 3);
                $pdf->SetY($savedY);
                $pdf->SetX($savedX);
            }
            $pdf->Cell(30, 6, $row['CltNiveau'], 0, '0', 'C');
            // drapeaux - Pattern 5: Sauvegarder position
            if (($arrayCompetition['Code_niveau'] ?? '') == 'INT') {
                $pays = substr($row['Code_club'], 0, 3);
                if (is_numeric($pays[0]) || is_numeric($pays[1]) || is_numeric($pays[2])) {
                    $pays = 'FRA';
                }
                $savedY = $pdf->y;
                $savedX = $pdf->x;
                $pdf->image('../img/Pays/' . $pays . '.png', $pdf->x, $pdf->y + 1, 7, 4);
                $pdf->SetY($savedY);
                $pdf->SetX($savedX);
                $pdf->Cell(10, 6, '', 0, '0', 'C'); //Pays
            } else {
                $pdf->Cell(10, 6, '', 0, '0', 'C');
            }

            $pdf->Cell(60, 6, $row['Libelle'], 0, 1, 'L');
            $i++;

            // Correction décalage tableau : réinitialiser marges et X à chaque saut de page
            if ($pdf->y > ($pdf->h - ($hasSponsor ? 30 : 15))) {
                // Désactiver AutoPageBreak pour images décoratives
                $pdf->SetAutoPageBreak(false);
                $pdf->AddPage();

                // Réinsérer images décoratives sur la nouvelle page
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
                if ($hasSponsor) {
                    $img = redimImage($visuels['sponsor'], 210, 10, 16, 'C');
                    $pdf->Image($img['image'], $img['positionX'], 267, 0, $img['newHauteur']);
                }

                // Réactiver AutoPageBreak et marges
                $pdf->SetAutoPageBreak(true, $hasSponsor ? 30 : 15);
                $pdf->SetTopMargin($yStart);
                $pdf->SetLeftMargin(10);
                $pdf->SetRightMargin(10);
                $pdf->SetY($yStart);
                $pdf->SetX(10);
            }
        }

        $pdf->Output('Classement ' . $codeCompet . '.pdf', \Mpdf\Output\Destination::INLINE);
    }
}

$page = new FeuilleCltNiveau();
