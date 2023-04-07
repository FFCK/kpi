<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

require('../lib/fpdf/fpdf.php');

// Gestion de la Feuille des Cartons
class FeuilleCards extends MyPage
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

        if ($arrayCompetition['En_actif'] == 'O') {
            $dateprint = date('Y-m-d H:i');
        } else {
            $dateprint = date('d/m/Y H:i');
        }

        //Création
        $pdf = new FPDF('P');
        $pdf->Open();
        $pdf->SetTitle("Cards");
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
        if ($arrayCompetition['Soustitre2'] != '') {
            $pdf->Cell(190, 5, $arrayCompetition['Soustitre2'], 0, 1, 'C');
        }
        $pdf->Ln(4);

        //données
        $sql = "SELECT ce.Libelle, cej.Capitaine Statut, cej.Matric, cej.Numero, cej.Nom, cej.Prenom,
                SUM(IF(md.Id_evt_match='V', 1, 0)) Vert, 
                SUM(IF(md.Id_evt_match='J', 1, 0)) Jaune, 
                SUM(IF(md.Id_evt_match='R', 1, 0)) Rouge, 
                SUM(IF(md.Id_evt_match='D', 1, 0)) Rouge_definitif 
            FROM kp_competition_equipe_joueur cej
            JOIN kp_competition_equipe ce ON (ce.Id = cej.Id_equipe)
            JOIN kp_match m ON (ce.Id = m.Id_equipeA OR ce.Id = m.Id_equipeB)
            JOIN kp_journee j ON (m.Id_journee = j.Id)
            LEFT OUTER JOIN kp_match_detail md ON (m.Id = md.Id_match AND cej.Matric = md.Competiteur)
            WHERE j.Code_saison = ? 
            AND j.Code_competition = ? 
            GROUP BY ce.Libelle, cej.Matric
            ORDER BY ce.Libelle, FIELD(cej.Capitaine, 'E', 'A', 'X'), cej.Numero, cej.Nom, cej.Prenom;";
        $result = $myBdd->pdo->prepare($sql);
        $result->execute(array($codeSaison, $codeCompet));
        $equipe = '';

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(100, 5, $lang['Suivi_cartons'], 0, 0, 'L');
        $pdf->Cell(90, 5, $lang['Situation_au'] . $dateprint, 0, 1, 'R');


        $pdf->Cell(74, 5, '', 1, 0, 'C');
        $pdf->Cell(32, 5, $lang['Cumul_saison'], 1, 0, 'C');
        $pdf->Cell(16, 5, '', 'TBL', 0, 'C');
        $pdf->Cell(68, 5, $lang['Journee'] . ' :', 'TBR', 1, 'L');

        $pdf->SetFont('Arial', 'I', 6);
        $pdf->Cell(8, 4, '-', 1, 0, 'C');
        $pdf->Cell(66, 4, $lang['Exemple'], 1, 0, 'C');
        $pdf->SetFillColor(170, 255, 170);
        $pdf->Cell(8, 4, '2', 1, 0, 'C', 1);
        $pdf->SetFillColor(255, 255, 170);
        $pdf->Cell(8, 4, '1', 1, 0, 'C', 1);
        $pdf->SetFillColor(255, 170, 170);
        $pdf->Cell(8, 4, '', 1, 0, 'C', 1);
        $pdf->Cell(8, 4, '', 1, 0, 'C', 1);
        $pdf->Cell(36, 4, '', 1, 0, 'L');
        $pdf->SetFillColor(170, 255, 170);
        $pdf->Cell(12, 4, 'I I I', 1, 0, 'L', 1);
        $pdf->SetFillColor(255, 255, 170);
        $pdf->Cell(12, 4, 'I', 1, 0, 'L', 1);
        $pdf->SetFillColor(255, 170, 170);
        $pdf->Cell(12, 4, '', 1, 0, 'L', 1);
        $pdf->Cell(12, 4, '', 1, 1, 'L', 1);

        while ($row = $result->fetch()) {
            if ($equipe != $row['Libelle']) {
                $pdf->Ln(6);
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(74, 5, $row['Libelle'], 1, 0, 'C');
                $pdf->SetFillColor(170, 255, 170);
                $pdf->Cell(8, 5, $lang['V'], 1, 0, 'C', 1);
                $pdf->SetFillColor(255, 255, 170);
                $pdf->Cell(8, 5, $lang['J'], 1, 0, 'C', 1);
                $pdf->SetFillColor(255, 170, 170);
                $pdf->Cell(8, 5, $lang['R'], 1, 0, 'C', 1);
                $pdf->Cell(8, 5, $lang['RD'], 1, 0, 'C', 1);
                $pdf->Cell(36, 5, 'Notes', 1, 0, 'C');
                $pdf->SetFillColor(170, 255, 170);
                $pdf->Cell(12, 5, $lang['V'], 1, 0, 'C', 1);
                $pdf->SetFillColor(255, 255, 170);
                $pdf->Cell(12, 5, $lang['J'], 1, 0, 'C', 1);
                $pdf->SetFillColor(255, 170, 170);
                $pdf->Cell(12, 5, $lang['R'], 1, 0, 'C', 1);
                $pdf->Cell(12, 5, $lang['RD'], 1, 1, 'C', 1);
            }

            if ($row['Vert'] == 0) {
                $row['Vert'] = '';
            }
            if ($row['Jaune'] == 0) {
                $row['Jaune'] = '';
            }
            if ($row['Rouge'] == 0) {
                $row['Rouge'] = '';
            }
            $pdf->SetFont('Arial', 'I', 8);
            $pdf->Cell(8, 4, $row['Statut'], 1, 0, 'C');
            $pdf->Cell(66, 4, $row['Numero'] . ' - ' . mb_strtoupper($row['Nom']) . ' ' . mb_convert_case(strtolower($row['Prenom']), MB_CASE_TITLE, "UTF-8"), 1, 0, 'L');
            $pdf->SetFillColor(170, 255, 170);
            $pdf->Cell(8, 4, $row['Vert'], 1, 0, 'C', 1);
            $pdf->SetFillColor(255, 255, 170);
            $pdf->Cell(8, 4, $row['Jaune'], 1, 0, 'C', 1);
            $pdf->SetFillColor(255, 170, 170);
            $pdf->Cell(8, 4, $row['Rouge'], 1, 0, 'C', 1);
            $pdf->Cell(8, 4, $row['Rouge_definitif'], 1, 0, 'C', 1);
            $pdf->Cell(36, 4, '', 1, 0, 'L');
            $pdf->SetFillColor(170, 255, 170);
            $pdf->Cell(12, 4, '', 1, 0, 'L', 1);
            $pdf->SetFillColor(255, 255, 170);
            $pdf->Cell(12, 4, '', 1, 0, 'L', 1);
            $pdf->SetFillColor(255, 170, 170);
            $pdf->Cell(12, 4, '', 1, 0, 'L', 1);
            $pdf->Cell(12, 4, '', 1, 1, 'L', 1);

            $equipe = $row['Libelle'];
        }

        $pdf->Output('Cards_' . $codeCompet . '.pdf', 'I');
    }
}

$page = new FeuilleCards();
