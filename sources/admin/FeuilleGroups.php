<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

require('../lib/fpdf/fpdf.php');

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

        if ($arrayCompetition['En_actif'] == 'O') {
            $lang = $langue['en'];
        } else {
            $lang = $langue['fr'];
        }

        //Création
        $pdf = new FPDF('P');
        $pdf->Open();
        $pdf->SetTitle($lang['Poules']);

        $pdf->SetAuthor("Poloweb.org");
        $pdf->SetCreator("Poloweb.org avec FPDF");
        $pdf->AddPage();
        if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($sponsor)) {
            $pdf->SetAutoPageBreak(true, 40);
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
            $pdf->Cell(186, 5, $arrayCompetition['Libelle'], 0, 1, 'C');
        } else {
            $pdf->Cell(186, 5, $arrayCompetition['Soustitre'], 0, 1, 'C');
        }
        //		$pdf->Ln(4);
        if ($arrayCompetition['Soustitre2'] != '') {
            $pdf->Cell(186, 5, $arrayCompetition['Soustitre2'], 0, 1, 'C');
        } else {
            $pdf->Cell(186, 5, '(' . $codeCompet . ')', 0, 1, 'C');
        }
        $pdf->Ln(4);
        //		$pdf->SetFont('Arial','BI',10);
        //		$pdf->Cell(186,5,$lang['POULES'],0,0,'C');
        //		$pdf->Ln(10);
        //données

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
            //$pdf->Cell(5, 6, '',1,0,'L');
            if ($row['Tirage'] > 0) {
                $pdf->Cell(10, 6, $row['Tirage'], 0, 0, 'C');
            } else {
                $pdf->Cell(10, 6, '', 0, 0, 'C');
            }
            // drapeaux
            if ($arrayCompetition['Code_niveau'] == 'INT') {
                $pays = substr($row['Code_club'], 0, 3);
                if (is_numeric($pays[0]) || is_numeric($pays[1]) || is_numeric($pays[2])) {
                    $pays = 'FRA';
                }
                $pdf->image('../img/Pays/' . $pays . '.png', $pdf->GetX(), $pdf->GetY() + 1, 7, 4);
                $pdf->Cell(10, 6, '', 0, 0, 'C'); //Pays
            } else {
                $pdf->Cell(10, 6, '', 0, 0, 'C');
            }
            $pdf->Cell(65, 6, $row['Libelle'], 0, 1, 'L');
            $i++;
        }

        $pdf->Output($lang['Poules'] . ' ' . $codeCompet . '.pdf', 'I');
    }
}

$page = new FeuilleGroups();
