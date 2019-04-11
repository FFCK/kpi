<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

require('../fpdf/fpdf.php');

// Gestion de la Feuille des Cartons
class FeuilleCards extends MyPage 
{	
	function FeuilleCards()
	{
		MyPage::MyPage();
		$myBdd = new MyBdd();
		
		$codeCompet = utyGetSession('codeCompet', '');
		//Saison
		$codeSaison = utyGetSaison();
        $titreDate = "Saison ".$codeSaison;
		$arrayCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);
		$titreCompet = 'Compétition : '.$arrayCompetition['Libelle'].' ('.$codeCompet.')';

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
            $date = $row['Date_match'];
            $dateprint = date('Y-m-d H:i');
        } else {
            $date = utyDateUsToFr($row['Date_match']);
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
            $pdf->Image('../img/logoKPI-small.jpg', 10, 10, 0, 16, 'jpg', "https://www.kayak-polo.info");
            $img = redimImage($visuels['logo'], 210, 10, 16, 'R');
            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
            // KPI
        } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O') {
            $pdf->Image('../img/logoKPI-small.jpg', 84, 10, 0, 16, 'jpg', "https://www.kayak-polo.info");
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
		$pdf->SetFont('Arial','B',14);
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
                SUM(IF(md.Id_evt_match='R', 1, 0)) Rouge 
            FROM gickp_Competitions_Equipes_Joueurs cej
            JOIN gickp_Competitions_Equipes ce ON (ce.Id = cej.Id_equipe)
            JOIN gickp_Matchs m ON (ce.Id = m.Id_equipeA OR ce.Id = m.Id_equipeB)
            JOIN gickp_Journees j ON (m.Id_journee = j.Id)
            LEFT OUTER JOIN gickp_Matchs_Detail md ON (m.Id = md.Id_match AND cej.Matric = md.Competiteur)
            WHERE j.Code_saison = $codeSaison
            AND j.Code_competition = '" . $codeCompet . "'
            GROUP BY ce.Libelle, cej.Matric
            ORDER BY ce.Libelle, FIELD(cej.Capitaine, 'E', 'A', 'X'), cej.Numero, cej.Nom, cej.Prenom;";
        
		$result = $myBdd->Query($sql);
        $equipe = '';
        
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(100, 5, $lang['Suivi_cartons'], 0, 0, 'L');
        $pdf->Cell(90, 5, $lang['Situation_au'] . $dateprint, 0, 1, 'R');

        
        $pdf->Cell(78, 5, '', 1, 0, 'C');
        $pdf->Cell(24, 5, $lang['Cumul_saison'], 1, 0, 'C');
        $pdf->Cell(20, 5, '', 'TBL', 0, 'C');
        $pdf->Cell(68, 5, $lang['Journee'] . ' :', 'TBR', 1, 'L');
        
        $pdf->SetFont('Arial','I',6);
        $pdf->Cell(8, 4, '-', 1, 0, 'C');
        $pdf->Cell(70, 4, $lang['Exemple'], 1, 0, 'C');
        $pdf->SetFillColor(170, 255, 170);
        $pdf->Cell(8, 4, '2', 1, 0, 'C', 1);
        $pdf->SetFillColor(255, 255, 170);
        $pdf->Cell(8, 4, '1', 1, 0, 'C', 1);
        $pdf->SetFillColor(255, 170, 170);
        $pdf->Cell(8, 4, '', 1, 0, 'C', 1);
        $pdf->Cell(40, 4, '', 1, 0, 'L');
        $pdf->SetFillColor(170, 255, 170);
        $pdf->Cell(16, 4, 'I I I', 1, 0, 'L', 1);
        $pdf->SetFillColor(255, 255, 170);
        $pdf->Cell(16, 4, 'I', 1, 0, 'L', 1);
        $pdf->SetFillColor(255, 170, 170);
        $pdf->Cell(16, 4, '', 1, 1, 'L', 1);

        while ($row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC)){
			if($equipe != $row['Libelle']) {
                $pdf->Ln(6);
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(78, 5, $row['Libelle'], 1, 0, 'C');
                $pdf->SetFillColor(170, 255, 170);
                $pdf->Cell(8, 5, $lang['V'], 1, 0, 'C', 1);
                $pdf->SetFillColor(255, 255, 170);
                $pdf->Cell(8, 5, $lang['J'], 1, 0, 'C', 1);
                $pdf->SetFillColor(255, 170, 170);
                $pdf->Cell(8, 5, $lang['R'], 1, 0, 'C', 1);
                $pdf->Cell(40, 5, 'Notes', 1, 0, 'C');
                $pdf->SetFillColor(170, 255, 170);
                $pdf->Cell(16, 5, $lang['V'], 1, 0, 'C', 1);
                $pdf->SetFillColor(255, 255, 170);
                $pdf->Cell(16, 5, $lang['J'], 1, 0, 'C', 1);
                $pdf->SetFillColor(255, 170, 170);
                $pdf->Cell(16, 5, $lang['R'], 1, 1, 'C', 1);
            }
            
            if ($row['Vert'] == 0) { $row['Vert'] = ''; }
            if ($row['Jaune'] == 0) { $row['Jaune'] = ''; }
            if ($row['Rouge'] == 0) { $row['Rouge'] = ''; }
            $pdf->SetFont('Arial','I',8);
            $pdf->Cell(8, 4, $row['Statut'], 1, 0, 'C');
            $pdf->Cell(70, 4, $row['Numero'] . ' - ' . $row['Nom'] . ' ' . $row['Prenom'], 1, 0, 'L');
            $pdf->SetFillColor(170, 255, 170);
            $pdf->Cell(8, 4, $row['Vert'], 1, 0, 'C', 1);
            $pdf->SetFillColor(255, 255, 170);
            $pdf->Cell(8, 4, $row['Jaune'], 1, 0, 'C', 1);
            $pdf->SetFillColor(255, 170, 170);
            $pdf->Cell(8, 4, $row['Rouge'], 1, 0, 'C', 1);
            $pdf->Cell(40, 4, '', 1, 0, 'L');
            $pdf->SetFillColor(170, 255, 170);
            $pdf->Cell(16, 4, '', 1, 0, 'L', 1);
            $pdf->SetFillColor(255, 255, 170);
            $pdf->Cell(16, 4, '', 1, 0, 'L', 1);
            $pdf->SetFillColor(255, 170, 170);
            $pdf->Cell(16, 4, '', 1, 1, 'L', 1);

            $equipe = $row['Libelle'];
            
            
//			$idEquipe = $row['Id'];
//			$pdf->SetFont('Arial','B',12);
//			$pdf->Cell(55, 6, '',0,'0','L');
//			// médailles
//			if ($row['Clt'] <= 3 && $row['Clt'] != 0 && $arrayCompetition['Code_tour'] == 'F') {
//                $pdf->image('../img/medal' . $row['Clt'] . '.gif', $pdf->GetX(), $pdf->GetY() + 1, 3, 3);
//            }
//            $pdf->Cell(30, 6, $row['Clt'].'.', 0,'0','C');
//			// drapeaux
//			if ($arrayCompetition['Code_niveau'] == 'INT') {
//                $pays = substr($row['Code_club'], 0, 3);
//                if (is_numeric($pays[0]) || is_numeric($pays[1]) || is_numeric($pays[2])) {
//                    $pays = 'FRA';
//                }
//                $pdf->image('../img/Pays/' . $pays . '.png', $pdf->GetX(), $pdf->GetY() + 1, 7, 4);
//                $pdf->Cell(10, 6, '', 0, '0', 'C'); //Pays
//            } else {
//                $pdf->Cell(10, 6, '', 0, '0', 'C');
//            }
//
//            $pdf->Cell(60,6, $row['Libelle'],0,1,'L');
			
            
		}
			
		$pdf->Output('Cards_'.$codeCompet.'.pdf','I');
	}
}

$page = new FeuilleCards();
