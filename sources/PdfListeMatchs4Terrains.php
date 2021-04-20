<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

//define('FPDF_FONTPATH','font/');
require('fpdf/fpdf.php');

require_once('qrcode/qrcode.class.php');

// Pieds de page
class PDF extends FPDF
{
	function Footer()
	{
	    //Positionnement à 1,5 cm du bas
	    $this->SetY(-15);
	    //Police Arial italique 8
	    $this->SetFont('Arial','I',8);
	    //Numéro de page centré
		$this->Cell(137,10,'Page '.$this->PageNo(),0,0,'L');
		$this->Cell(136,5,"Edité le ".date("d/m/Y")." à ".date("H:i", strtotime($_SESSION['tzOffset'])),0,1,'R');
	}
}
 
// Liste des Matchs d'une Journee ou d'un Evenement 
class PdfListeMatchs extends MyPage	 
{	
	function __construct()
	{
		MyPage::MyPage();
  	    // Chargement des Matchs des journées ...
		$filtreJour = utyGetSession('filtreJour', '');
		$filtreJour = utyGetPost('filtreJour', $filtreJour);
		$filtreJour = utyGetGet('filtreJour', $filtreJour);
		
		$filtreTerrain = utyGetSession('filtreTerrain', '');
		$filtreTerrain = utyGetPost('filtreTerrain', $filtreTerrain);
		$filtreTerrain = utyGetGet('filtreTerrain', $filtreTerrain);

		$myBdd = new MyBdd();
		$lstJournee = utyGetSession('lstJournee', 0);
        $idEvenement = utyGetSession('idEvenement', -1);
		$idEvenement = utyGetGet('idEvenement', $idEvenement);
		if (utyGetGet('idEvenement', 0) > 0) {
			$lstJournee = [];
			$sql = "SELECT Id_journee 
                FROM kp_evenement_journee 
                WHERE Id_evenement = ? ";
            $result = $myBdd->pdo->prepare($sql);
            $result->execute(array($idEvenement));
            while ($row = $result->fetch()) {
                $lstJournee[] = $row['Id_journee'];
			}
		} else {
            $lstJournee = explode(',', $lstJournee);
        }
        $codeSaison = $myBdd->GetActiveSaison();
		$codeSaison = utyGetGet('S', $codeSaison);
		$orderMatchs = 'ORDER BY a.Date_match, d.Lieu, a.Heure_match, a.Terrain';
		$laCompet = utyGetSession('codeCompet', 0);
		$laCompet = utyGetGet('Compet', $laCompet);
		if ($laCompet != 0) {
			$lstJournee = [];
			$idEvenement = -1;
		}
		$codeCompet = $laCompet;
        if ($lstJournee != []) {
            $in  = str_repeat('?,', count($lstJournee) - 1) . '?';
            $sql = "SELECT a.Id, a.Id_journee, a.Id_equipeA, a.Id_equipeB, a.Numero_ordre, 
                a.Date_match, a.Heure_match, a.Libelle, a.Terrain, b.Libelle EquipeA, 
                c.Libelle EquipeB, a.Terrain, a.ScoreA, a.ScoreB, a.Arbitre_principal, 
                a.Arbitre_secondaire, a.Matric_arbitre_principal, a.Matric_arbitre_secondaire, 
                a.Validation, d.Code_competition, d.Phase, d.Niveau, d.Lieu, d.Libelle LibelleJournee, 
                cp.Soustitre2 
                FROM kp_competition cp, kp_journee d, kp_match a 
                LEFT OUTER JOIN kp_competition_equipe b ON (a.Id_equipeA = b.Id) 
                LEFT OUTER JOIN kp_competition_equipe c ON (a.Id_equipeB = c.Id) 
                WHERE a.Id_journee = d.Id 
                AND d.Code_competition = cp.Code 
                AND d.Code_saison = cp.Code_saison 
                AND a.Id_journee IN ($in) ";
            $merge = $lstJournee;
            if ($filtreJour != '') {
                $sql .= "AND a.Date_match = ? ";
                $merge = array_merge($merge, [$filtreJour]);
            }
            if ($filtreTerrain != '') {
                $sql .= "AND a.Terrain = ? ";
                $merge = array_merge($merge, [$filtreTerrain]);
            }
            $sql .= $orderMatchs ;
            $result = $myBdd->pdo->prepare($sql);
            $result->execute($merge);
        } else {
            $sql = "SELECT a.Id, a.Id_journee, a.Id_equipeA, a.Id_equipeB, a.Numero_ordre, 
                a.Date_match, a.Heure_match, a.Libelle, a.Terrain, b.Libelle EquipeA, 
                c.Libelle EquipeB, a.Terrain, a.ScoreA, a.ScoreB, a.Arbitre_principal, 
                a.Arbitre_secondaire, a.Matric_arbitre_principal, a.Matric_arbitre_secondaire, 
                a.Validation, d.Code_competition, d.Phase, d.Niveau, d.Lieu, d.Libelle LibelleJournee, 
                cp.Soustitre2 
                FROM kp_competition cp, kp_journee d, kp_match a 
                LEFT OUTER JOIN kp_competition_equipe b ON (a.Id_equipeA = b.Id) 
                LEFT OUTER JOIN kp_competition_equipe c ON (a.Id_equipeB = c.Id) 
                WHERE a.Id_journee = d.Id 
                AND d.Code_competition = cp.Code 
                AND d.Code_saison = cp.Code_saison 
                AND d.Code_competition = ? 
                AND d.Code_saison = ? ";
            $merge = array($laCompet, $codeSaison);
            if ($filtreJour != '') {
                $sql .= "AND a.Date_match = ? ";
                $merge = array_merge($merge, [$filtreJour]);
            }
            if ($filtreTerrain != '') {
                $sql .= "AND a.Terrain = ? ";
                $merge = array_merge($merge, [$filtreTerrain]);
            }
            $sql .= $orderMatchs ;
            $result = $myBdd->pdo->prepare($sql);
            $result->execute($merge);
        }
		
        $resultarray = $result->fetchAll(PDO::FETCH_ASSOC);
        foreach ($resultarray as $key => $row1) {
            $lastCompetEvt = $row1['Code_competition'];
		}
		// Chargement des infos de l'évènement ou de la compétition
		$titreEvenementCompet = '';
		if ($idEvenement != -1) {
			$libelleEvenement = $myBdd->GetEvenementLibelle($idEvenement);
			$titreEvenementCompet = 'Evénement : '.$libelleEvenement;
			$arrayCompetition = $myBdd->GetCompetition($lastCompetEvt, $codeSaison);
		} else {
			$arrayCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);
			if ($arrayCompetition['Titre_actif'] == 'O') {
				$titreEvenementCompet = $arrayCompetition['Libelle'];
            } else {
                $titreEvenementCompet = $arrayCompetition['Soustitre'];
            }
			if ($arrayCompetition['Soustitre2'] != '') {
                $titreEvenementCompet .= ' - '.$arrayCompetition['Soustitre2'];
            }
		}
        
        $visuels = utyGetVisuels($arrayCompetition, FALSE);

        // Entête PDF ...	  
 		$pdf = new PDF('L');
		$pdf->Open();
		$pdf->SetTitle("Liste des Matchs");
		$pdf->SetAuthor("Kayak-polo.info");
		$pdf->SetCreator("Kayak-polo.info avec FPDF");
		$pdf->SetTopMargin(30);
        
        // Bandeau
        if ($arrayCompetition['Bandeau_actif'] == 'O' && isset($visuels['bandeau'])) {
            $img = redimImage($visuels['bandeau'], 297, 10, 20, 'C');
            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
            // KPI + Logo    
        } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O' && $arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
            $pdf->Image('img/CNAKPI_small.jpg', 40, 10, 0, 20, 'jpg', "https://www.kayak-polo.info");
            $img = redimImage($visuels['logo'], 297, 10, 20, 'R');
            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
            // KPI
        } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O') {
            $pdf->Image('img/CNAKPI_small.jpg', 125, 10, 0, 20, 'jpg', "https://www.kayak-polo.info");
            // Logo
        } elseif ($arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
            $img = redimImage($visuels['logo'], 297, 10, 20, 'C');
            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
        }
        // Sponsor
        if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($visuels['sponsor'])) {
            $img = redimImage($visuels['sponsor'], 297, 10, 16, 'C');
            $pdf->Image($img['image'], $img['positionX'], 184, 0, $img['newHauteur']);
        }
		// QRCode
		$qrcode = new QRcode('https://www.kayak-polo.info/Journee.php?Compet='.$codeCompet.'&Group='.$arrayCompetition['Code_ref'].'&Saison='.$codeSaison, 'L'); // error level : L, M, Q, H
		//$qrcode->displayFPDF($fpdf, $x, $y, $s, $background, $color);
        $qr_x = 265;
		$qrcode->displayFPDF($pdf, $qr_x, 9, 21);

		$titreDate = "Saison ".$codeSaison;
		// titre
		$pdf->SetFont('Arial','BI',12);
		$pdf->Cell(137,5,$titreEvenementCompet,0,0,'L');
		$pdf->Cell(136,5,$titreDate,0,1,'R');
		$pdf->SetFont('Arial','B',14);
		$pdf->Cell(273,6,"Liste des Matchs",0,1,'C');
		$pdf->Ln(3);
        foreach ($resultarray as $key => $row) {
            if ($row['Soustitre2'] != '') {
                $row['Code_competition'] = $row['Soustitre2'];
            }
            $phase_match = $row['Phase'];
			if ($row['Libelle'] != '') {
				$libelle = explode(']', $row['Libelle']);
				if ($libelle[1] != '') {
                    $phase_match .= "  |  " . $libelle[1];
                }
                //Codes équipes	
				$EquipesAffectAuto = utyEquipesAffectAutoFR($row['Libelle']);
			}
			if ($row['Id_equipeA'] >= 1) {
                $myBdd->InitTitulaireEquipe('A', $row['Id'], $row['Id_equipeA']);
            } elseif (isset($EquipesAffectAuto[0]) && $EquipesAffectAuto[0] != '') {
                $row['EquipeA'] = $EquipesAffectAuto[0];
            }
            if ($row['Id_equipeB'] >= 1) {
                $myBdd->InitTitulaireEquipe('B', $row['Id'], $row['Id_equipeB']);
            } elseif (isset($EquipesAffectAuto[1]) && $EquipesAffectAuto[1] != '') {
                $row['EquipeB'] = $EquipesAffectAuto[1];
            }
            if ($row['Arbitre_principal'] != '' && $row['Arbitre_principal'] != '-1') {
                $row['Arbitre_principal'] = utyArbSansNiveau($row['Arbitre_principal']);
            } elseif (isset($EquipesAffectAuto[2]) && $EquipesAffectAuto[2] != '') {
                $row['Arbitre_principal'] = $EquipesAffectAuto[2];
            }
            if ($row['Arbitre_secondaire'] != '' && $row['Arbitre_secondaire'] != '-1') {
                $row['Arbitre_secondaire'] = utyArbSansNiveau($row['Arbitre_secondaire']);
            } elseif (isset($EquipesAffectAuto[3]) && $EquipesAffectAuto[3] != '') {
                $row['Arbitre_secondaire'] = $EquipesAffectAuto[3];
            }

            $datematch = $row['Date_match'];
            $heure = $row['Heure_match'];
            $terrain = $row['Terrain'];
            
            $tab[$datematch][$heure][$terrain][] = $row;
        }
        
        foreach ($tab as $date => $tab_heure) {
            $pdf->AddPage();
            // Bandeau
            if ($arrayCompetition['Bandeau_actif'] == 'O' && isset($visuels['bandeau'])) {
                $img = redimImage($visuels['bandeau'], 297, 10, 20, 'C');
                $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
                // KPI + Logo    
            } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O' && $arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
                $pdf->Image('img/CNAKPI_small.jpg', 40, 10, 0, 20, 'jpg', "https://www.kayak-polo.info");
                $img = redimImage($visuels['logo'], 297, 10, 20, 'R');
                $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
                // KPI
            } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O') {
                $pdf->Image('img/CNAKPI_small.jpg', 125, 10, 0, 20, 'jpg', "https://www.kayak-polo.info");
                // Logo
            } elseif ($arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
                $img = redimImage($visuels['logo'], 297, 10, 20, 'C');
                $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
            }
            // Sponsor
            if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($visuels['sponsor'])) {
                $img = redimImage($visuels['sponsor'], 297, 10, 16, 'C');
                $pdf->Image($img['image'], $img['positionX'], 184, 0, $img['newHauteur']);
            }
            
            $pdf->SetFillColor(220, 220, 220);
            $pdf->SetFont('Arial','B',7);
            $pdf->Cell(30, 5, utyDateUsToFrLong($date), 0,1,'L');
            
            $pdf->Cell(10, 5, '', 0,0,'L');
            $pdf->Cell(67, 5, 'Terrain 1', 1,0,'C',1);
            $pdf->Cell(67, 5, 'Terrain 2', 1,0,'C',1);
            $pdf->Cell(67, 5, 'Terrain 3', 1,0,'C',1);
            $pdf->Cell(67, 5, 'Terrain 4', 1,1,'C',1);
            
            $pdf->Cell(10,5, 'Heure',1,0,'C',1);
                        
            $pdf->Cell(7,5, 'N°',1,0,'C',1);
            $pdf->Cell(14,5, 'Comp.',1,0,'C',1);
            $pdf->Cell(23,5, 'Equipe A',1,0,'C',1);
            $pdf->Cell(23,5, 'Equipe B',1,0,'C',1);
//            $pdf->Cell(17,5, 'Arbitre',1,0,'C');

            $pdf->Cell(7,5, 'N°',1,0,'C',1);
            $pdf->Cell(14,5, 'Comp.',1,0,'C',1);
            $pdf->Cell(23,5, 'Equipe A',1,0,'C',1);
            $pdf->Cell(23,5, 'Equipe B',1,0,'C',1);
//            $pdf->Cell(17,5, 'Arbitre',1,0,'C');

            $pdf->Cell(7,5, 'N°',1,0,'C',1);
            $pdf->Cell(14,5, 'Comp.',1,0,'C',1);
            $pdf->Cell(23,5, 'Equipe A',1,0,'C',1);
            $pdf->Cell(23,5, 'Equipe B',1,0,'C',1);
//            $pdf->Cell(17,5, 'Arbitre',1,0,'C');

            $pdf->Cell(7,5, 'N°',1,0,'C',1);
            $pdf->Cell(14,5, 'Comp.',1,0,'C',1);
            $pdf->Cell(23,5, 'Equipe A',1,0,'C',1);
            $pdf->Cell(23,5, 'Equipe B',1,1,'C',1);
//            $pdf->Cell(17,5, 'Arbitre',1,1,'C');

            foreach ($tab_heure as $heure => $tab_terrain) {
                
                $pdf->SetFont('Arial','',6);
                $pdf->Cell(10,5, $heure,1,'0','C');

                for ($i = 1; $i <= 4; $i ++) {
                    if($i == 4) {
                        $findeligne = 1;
                    } else {
                        $findeligne = 0;
                    }
//                    echo '<pre>' . var_dump($tab_terrain) . '</pre>';
                    if (isset($tab_terrain[$i])) {
                        $pdf->Cell(7,5, $tab_terrain[$i][0]['Numero_ordre'],1,0,'C',1);
                        $pdf->Cell(14,5, $tab_terrain[$i][0]['Code_competition'],1,0,'C');
                        
                        if(strlen($tab_terrain[$i][0]['EquipeA']) > 18) {
                            $pdf->SetFont('Arial','',4);
                        } elseif(strlen($tab_terrain[$i][0]['EquipeA']) > 10) {
                            $pdf->SetFont('Arial','',5);
                        } else {
                            $pdf->SetFont('Arial','',6);
                        }
                        $pdf->Cell(23,5, $tab_terrain[$i][0]['EquipeA'],1,0,'C');
                        
                        if(strlen($tab_terrain[$i][0]['EquipeB']) > 18) {
                            $pdf->SetFont('Arial','',4);
                        } elseif(strlen($tab_terrain[$i][0]['EquipeB']) > 10) {
                        } else {
                            $pdf->SetFont('Arial','',6);
                        }
                        $pdf->Cell(23,5, $tab_terrain[$i][0]['EquipeB'],1,$findeligne,'C');
                        
                    } else {
                        $pdf->SetFont('Arial', '', 6);
                        $pdf->Cell(67, 5, 'Pause', 1,$findeligne,'C',1);
                    }
                }
            
            }
        }
				

						

		$pdf->Cell(271,3,'','T','1','C');
		$pdf->Output('Liste matchs'.'.pdf','I');
	}
}

$page = new PdfListeMatchs();


