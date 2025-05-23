<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

require('../lib/fpdf/fpdf.php');

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

        $visuels = utyGetVisuels($arrayCompetition, TRUE);

        // Langue
        $langue = parse_ini_file("../commun/MyLang.ini", true);
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
        $pdf->SetTitle("Classement");

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
            $pdf->Image('../img/CNAKPI_small.jpg', 10, 10, 0, 16, 'jpg', "https://www.kayak-polo.info");
            $img = redimImage($visuels['logo'], 210, 10, 16, 'R');
            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
            // KPI
        } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O') {
            $pdf->Image('../img/CNAKPI_small.jpg', 84, 10, 0, 16, 'jpg', "https://www.kayak-polo.info");
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

        // titre
        $pdf->Ln(22);
        $pdf->SetFont('Arial', 'B', 14);
        if ($arrayCompetition['Titre_actif'] == 'O') {
            $pdf->Cell(190, 5, $arrayCompetition['Libelle'], 0, 1, 'C');
        } else {
            $pdf->Cell(190, 5, $arrayCompetition['Soustitre'], 0, 1, 'C');
        }
        //		$pdf->Ln(4);
        if ($arrayCompetition['Soustitre2'] != '') {
            $pdf->Cell(190, 5, $arrayCompetition['Soustitre2'], 0, 1, 'C');
        }
        $pdf->Ln(4);
        $pdf->SetFont('Arial', 'BI', 10);
        $pdf->Cell(190, 5, $lang['CLASSEMENT_PROVISOIRE'], 0, 0, 'C');
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

        for ($i = 0; $i < $num_results; $i++) {
            $row = $result->fetch();
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
            // médailles
            if ($row['CltNiveau'] <= 3 && $row['CltNiveau'] != 0 && $arrayCompetition['Code_tour'] == 'F') {
                $pdf->image('../img/medal' . $row['CltNiveau'] . '.gif', $pdf->GetX(), $pdf->GetY() + 1, 3, 3);
            }
            $pdf->Cell(30, 6, $row['CltNiveau'], 0, '0', 'C');
            // drapeaux
            if ($arrayCompetition['Code_niveau'] == 'INT') {
                $pays = substr($row['Code_club'], 0, 3);
                if (is_numeric($pays[0]) || is_numeric($pays[1]) || is_numeric($pays[2])) {
                    $pays = 'FRA';
                }
                $pdf->image('../img/Pays/' . $pays . '.png', $pdf->GetX(), $pdf->GetY() + 1, 7, 4);
                $pdf->Cell(10, 6, '', 0, '0', 'C'); //Pays
            } else {
                $pdf->Cell(10, 6, '', 0, '0', 'C');
            }

            $pdf->Cell(60, 6, $row['Libelle'], 0, 1, 'L');
        }

        $pdf->Output('Classement ' . $codeCompet . '.pdf', 'I');
    }
}

$page = new FeuilleCltNiveau();
