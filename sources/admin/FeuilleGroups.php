<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');
require_once('../commun/MyPDF.php');

// Gestion de la Feuille de Classement
class FeuilleGroups extends MyPage
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

        // Création PDF
        $pdf = new MyPDF('P');
        $pdf->SetTitle($lang['Poules']);
        $pdf->SetAuthor("Poloweb.org");
        $pdf->SetCreator("Poloweb.org avec mPDF");

        // Pattern 8 : Images décoratives en arrière-plan
        $yStart = 30; // Position de départ du contenu après images

        // Désactiver AutoPageBreak avant images décoratives
        $pdf->SetAutoPageBreak(false);

        // AddPage() AVANT images décoratives
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
        // Sponsor (en bas)
        $hasSponsor = false;
        if (($arrayCompetition['Sponsor_actif'] ?? '') == 'O' && isset($visuels['sponsor'])) {
            $img = redimImage($visuels['sponsor'], 210, 10, 16, 'C');
            $pdf->Image($img['image'], $img['positionX'], 267, 0, $img['newHauteur']);
            $hasSponsor = true;
        }

        // Footer HTML pour numéro de page sous le sponsor
        $footerHTML = '<div style="text-align:center;font-family:Arial;font-size:8pt;font-style:italic;margin-top:2mm;">Page {PAGENO}</div>';
        $pdf->SetHTMLFooter($footerHTML);

        // Réactiver AutoPageBreak avec marge basse adaptée
        if ($hasSponsor) {
            $pdf->SetAutoPageBreak(true, 30);
        } else {
            $pdf->SetAutoPageBreak(true, 15);
        }

        // --- CORRECTION PRINCIPALE ---
        // Ne pas refaire AddPage() ici !
        // Positionner le curseur ABSOLUMENT à $yStart (pas de Ln)
        $pdf->SetY($yStart);
        $pdf->SetLeftMargin(10);
        $pdf->SetRightMargin(10);
        $pdf->SetX(10);

        // titre
        $pdf->SetFont('Arial', 'B', 14);
        if (($arrayCompetition['Titre_actif'] ?? '') == 'O') {
            $pdf->Cell(186, 5, $arrayCompetition['Libelle'], 0, 1, 'C');
        } else {
            $pdf->Cell(186, 5, $arrayCompetition['Soustitre'] ?? '', 0, 1, 'C');
        }
        if (($arrayCompetition['Soustitre2'] ?? '') != '') {
            $pdf->Cell(186, 5, $arrayCompetition['Soustitre2'], 0, 1, 'C');
        } else {
            $pdf->Cell(186, 5, '(' . $codeCompet . ')', 0, 1, 'C');
        }
        $pdf->Ln(4);

        // données
        $sql = "SELECT Id, Libelle, Code_club, Poule, Tirage 
            FROM kp_competition_equipe 
            WHERE Code_compet = ? 
            AND Code_saison = ? 
            ORDER BY Poule, Tirage, Libelle ";
        $result = $myBdd->pdo->prepare($sql);
        $result->execute(array($codeCompet, $codeSaison));
        $num_results = $result->rowCount();

        $poule = '';
        $demi = $num_results / 2;
        $pdf->Ln(6);

        //Colonne 1
        if ($num_results > 20) {
            $x0 = 10;
        } else {
            $x0 = 70;
        }
        $pdf->SetLeftMargin($x0);
        $pdf->SetX($x0);
        $pdf->SetY(50);
        $i = 0;

        while ($row = $result->fetch()) {
            if ($poule != $row['Poule']) {
                if ($i >= $demi && $demi != 0 && $num_results > 20) {
                    //Colonne 2
                    $x0 = 115;
                    $pdf->SetLeftMargin($x0);
                    $pdf->SetX($x0);
                    $pdf->SetY(50);
                    $demi = 0;
                }
                $pdf->SetFont('Arial', 'BI', 13);
                $pdf->Ln(4);
                $pdf->Cell(65, 6, $lang['Equipes'] . ' ' . $lang['Poule'] . ' ' . $row['Poule'], 0, 1, 'C');
                $pdf->Ln(2);
            }
            $poule = $row['Poule'];

            $pdf->SetFont('Arial', 'B', 12);
            if ($row['Tirage'] > 0) {
                $pdf->Cell(10, 6, $row['Tirage'], 0, 0, 'C');
            } else {
                $pdf->Cell(10, 6, '', 0, 0, 'C');
            }
            // drapeaux (Pattern 5 : sauvegarde/restaure X/Y)
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
                $pdf->Cell(10, 6, '', 0, 0, 'C'); //Pays
            } else {
                $pdf->Cell(10, 6, '', 0, 0, 'C');
            }
            $pdf->Cell(65, 6, $row['Libelle'], 0, 1, 'L');
            $i++;
        }

        $pdf->Output($lang['Poules'] . ' ' . $codeCompet . '.pdf', \Mpdf\Output\Destination::INLINE);
    }
}

$page = new FeuilleGroups();
