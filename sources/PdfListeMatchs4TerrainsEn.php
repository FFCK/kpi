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
		$this->Cell(136,5,"Edité le ".date("d/m/Y")." à ".date("H:i"),0,1,'R');
	}
}
 
// Liste des Matchs d'une Journee ou d'un Evenement 
class PdfListeMatchs extends MyPage	 
{	
 	function InitTitulaireEquipe($numEquipe, $idMatch, $idEquipe, $bdd)
	{
		$sql = "Select Count(*) Nb From gickp_Matchs_Joueurs Where Id_match = $idMatch And Equipe = '$numEquipe' ";
		$result = mysql_query($sql, $bdd->m_link) or die ("Erreur Select ".$sql);

		if (mysql_num_rows($result) != 1)
			return;
			
		$row = mysql_fetch_array($result);
		if ((int) $row['Nb'] > 0)
			return;
			
		$sql  = "Replace Into gickp_Matchs_Joueurs ";
		$sql .= "Select $idMatch, Matric, Numero, '$numEquipe', Capitaine From gickp_Competitions_Equipes_Joueurs ";
		$sql .= "Where Id_equipe = $idEquipe ";
		$sql .= "AND Capitaine <> 'X' ";
		$sql .= "AND Capitaine <> 'A' ";
		mysql_query($sql, $bdd->m_link) or die ("Erreur Replace InitTitulaireEquipe");
 	}
	   
	function PdfListeMatchs()
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
		if(isset($_GET['idEvenement'])){
			$lstJournee = '';
			$sql = "SELECT Id_journee FROM gickp_Evenement_Journees WHERE Id_evenement = ".$_GET['idEvenement'];
			$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load =>  ".$sql);
			$num_results = mysql_num_rows($result);
			for ($j=0;$j<$num_results;$j++)
			{
				$row = mysql_fetch_array($result);
				if ($lstJournee != '')
					$lstJournee .= ',';
				$lstJournee .= $row['Id_journee'];
			}
		}
		$codeSaison = utyGetSaison();
		$codeSaison = utyGetGet('S', $codeSaison);
		$orderMatchs = 'Order By a.Date_match, d.Lieu, a.Heure_match, a.Terrain';
		$laCompet = utyGetSession('codeCompet', 0);
		$laCompet = utyGetGet('Compet', $laCompet);
		if($laCompet != 0)
		{
			$lstJournee = 0;
			$idEvenement = -1;
		}
		$codeCompet = $laCompet;
		$sql  = "Select a.Id, a.Id_journee, a.Id_equipeA, a.Id_equipeB, a.Numero_ordre, a.Date_match, a.Heure_match, ";
		$sql .= "a.Libelle, a.Terrain, b.Libelle EquipeA, c.Libelle EquipeB, a.Terrain, a.ScoreA, a.ScoreB, ";
		$sql .= "a.Arbitre_principal, a.Arbitre_secondaire, a.Matric_arbitre_principal, a.Matric_arbitre_secondaire, a.Validation, ";
		$sql .= "d.Code_competition, d.Phase, d.Niveau, d.Lieu, d.Libelle LibelleJournee ";
		$sql .= "From gickp_Matchs a ";
		$sql .= "Left Outer Join gickp_Competitions_Equipes b On (a.Id_equipeA = b.Id) "; 
		$sql .= "Left Outer Join gickp_Competitions_Equipes c On (a.Id_equipeB = c.Id) ";
		$sql .= ", gickp_Journees d ";
		if($lstJournee == 0)
			$sql .= "Where d.Code_competition = '".$laCompet."' And d.Code_saison = $codeSaison ";
		else
			$sql .= "Where a.Id_journee In ($lstJournee) ";
		if($filtreJour != '')
		{
			$sql .= "And a.Date_match = '".$filtreJour."' ";
		}
		if($filtreTerrain != '')
		{
			$sql .= "And a.Terrain = '".$filtreTerrain."' ";
		}
		$sql .= "And a.Id_journee = d.Id ";
//		$sql .= "And a.Publication = 'O' ";
		$sql .= $orderMatchs;

        $orderMatchsKey1 = utyKeyOrder($orderMatchs, 0);
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load =>  ".$sql);
		$num_results = mysql_num_rows($result);
		
		$PhaseLibelle = 0;
		for ($j=0;$j<$num_results;$j++)
		{
			$row1 = mysql_fetch_array($result);	  
			if (trim($row1['Phase']) != '')
				$PhaseLibelle = 1;
			$lastCompetEvt = $row1['Code_competition'];
		}
		$Oldrupture = "";
		// Chargement des infos de l'évènement ou de la compétition
		$titreEvenementCompet = '';
		if ($idEvenement != -1)
		{
			$libelleEvenement = $myBdd->GetEvenementLibelle($idEvenement);
			$titreEvenementCompet = 'Evénement : '.$libelleEvenement;
			$arrayCompetition = $myBdd->GetCompetition($lastCompetEvt, $codeSaison);
		}
		else
		{
			$arrayCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);
			if($arrayCompetition['Titre_actif'] == 'O')
				$titreEvenementCompet = $arrayCompetition['Libelle'];
			else
				$titreEvenementCompet = $arrayCompetition['Soustitre'];
			if($arrayCompetition['Soustitre2'] != '')
				$titreEvenementCompet .= ' - '.$arrayCompetition['Soustitre2'];
			//$titreEvenementCompet = 'Compétition : '.$arrayCompetition['Libelle'].' ('.$codeCompet.')';
		}
        
        $visuels = utyGetVisuels($arrayCompetition, FALSE);


        // Entête PDF ...	  
 		$pdf = new PDF('L');
		$pdf->Open();
		$pdf->SetTitle("Game list");
		$pdf->SetAuthor("Kayak-polo.info");
		$pdf->SetCreator("Kayak-polo.info with FPDF");
		$pdf->SetTopMargin(30);
        
        // Bandeau
        if ($arrayCompetition['Bandeau_actif'] == 'O' && isset($visuels['bandeau'])) {
            $img = redimImage($visuels['bandeau'], 297, 10, 20, 'C');
            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
            // KPI + Logo    
        } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O' && $arrayCompetition['Logo_actif'] == 'O' && isset($visuels['logo'])) {
            $pdf->Image('img/logoKPI-small.jpg', 40, 10, 0, 20, 'jpg', "https://www.kayak-polo.info");
            $img = redimImage($visuels['logo'], 297, 10, 20, 'R');
            $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
            // KPI
        } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O') {
            $pdf->Image('img/logoKPI-small.jpg', 125, 10, 0, 20, 'jpg', "https://www.kayak-polo.info");
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
		$qrcode->displayFPDF($pdf, $qr_x, 9, 21);

		$titreDate = "Season ".$codeSaison;
		// titre
		$pdf->SetFont('Arial','BI',12);
		$pdf->Cell(137,5,$titreEvenementCompet,0,0,'L');
		$pdf->Cell(136,5,$titreDate,0,1,'R');
		$pdf->SetFont('Arial','B',14);
		$pdf->Cell(273,6,"Game list",0,1,'C');
		$pdf->Ln(3);
		$heure1 = '';
		if($num_results > 0)
			mysql_data_seek($result,0);
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);
			
			$row['Soustitre2'] = $myBdd->GetSoustitre2Competition($row['Code_competition'], $codeSaison);
			if($row['Soustitre2'] != '')
				$row['Code_competition'] = $row['Soustitre2'];
			$phase_match = $row['Phase'];
			if ($row['Libelle'] != '')
			{
				$libelle = explode(']', $row['Libelle']);
				if($libelle[1] != '')
					$phase_match .= "  |  ".$libelle[1];
				//Codes équipes	
				$EquipesAffectAuto = utyEquipesAffectAuto($row['Libelle']);
			}
			if ($row['Id_equipeA'] >= 1)
				$this->InitTitulaireEquipe('A', $row['Id'], $row['Id_equipeA'], $myBdd);
			elseif (isset($EquipesAffectAuto[0]) && $EquipesAffectAuto[0] != '')
				$row['EquipeA'] = $EquipesAffectAuto[0];
			if ($row['Id_equipeB'] >= 1)
				$this->InitTitulaireEquipe('B', $row['Id'], $row['Id_equipeB'], $myBdd);
			elseif (isset($EquipesAffectAuto[1]) && $EquipesAffectAuto[1] != '')
				$row['EquipeB'] = $EquipesAffectAuto[1];
			if($row['Arbitre_principal'] != '' && $row['Arbitre_principal'] != '-1')
				$row['Arbitre_principal'] = utyArbSansNiveau($row['Arbitre_principal']);
			elseif (isset($EquipesAffectAuto[2]) && $EquipesAffectAuto[2] != '')
				$row['Arbitre_principal'] = $EquipesAffectAuto[2];
			if($row['Arbitre_secondaire'] != '' && $row['Arbitre_secondaire'] != '-1')
				$row['Arbitre_secondaire'] = utyArbSansNiveau($row['Arbitre_secondaire']);
			elseif (isset($EquipesAffectAuto[3]) && $EquipesAffectAuto[3] != '')
				$row['Arbitre_secondaire'] = $EquipesAffectAuto[3];
            
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
                $pdf->Image('img/logoKPI-small.jpg', 40, 10, 0, 20, 'jpg', "https://www.kayak-polo.info");
                $img = redimImage($visuels['logo'], 297, 10, 20, 'R');
                $pdf->Image($img['image'], $img['positionX'], 8, 0, $img['newHauteur']);
                // KPI
            } elseif ($arrayCompetition['Kpi_ffck_actif'] == 'O') {
                $pdf->Image('img/logoKPI-small.jpg', 125, 10, 0, 20, 'jpg', "https://www.kayak-polo.info");
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
            $date = date_create($date);
            $date = date_format($date, 'l d/m/Y');
            $pdf->Cell(30, 5, $date, 0,1,'L');
            
            $pdf->Cell(10, 5, '', 0,0,'L');
            $pdf->Cell(67, 5, 'Pitch 1', 1,0,'C',1);
            $pdf->Cell(67, 5, 'Pitch 2', 1,0,'C',1);
            $pdf->Cell(67, 5, 'Pitch 3', 1,0,'C',1);
            $pdf->Cell(67, 5, 'Pitch 4', 1,1,'C',1);
            
            $pdf->Cell(10,5, 'Time',1,0,'C',1);
                        
            $pdf->Cell(7,5, '#',1,0,'C',1);
            $pdf->Cell(14,5, 'Cat.',1,0,'C',1);
            $pdf->Cell(23,5, 'Team A',1,0,'C',1);
            $pdf->Cell(23,5, 'Team B',1,0,'C',1);
//            $pdf->Cell(17,5, 'Arbitre',1,0,'C');

            $pdf->Cell(7,5, '#',1,0,'C',1);
            $pdf->Cell(14,5, 'Cat.',1,0,'C',1);
            $pdf->Cell(23,5, 'Team A',1,0,'C',1);
            $pdf->Cell(23,5, 'Team B',1,0,'C',1);
//            $pdf->Cell(17,5, 'Arbitre',1,0,'C');

            $pdf->Cell(7,5, '#',1,0,'C',1);
            $pdf->Cell(14,5, 'Cat.',1,0,'C',1);
            $pdf->Cell(23,5, 'Team A',1,0,'C',1);
            $pdf->Cell(23,5, 'Team B',1,0,'C',1);
//            $pdf->Cell(17,5, 'Arbitre',1,0,'C');

            $pdf->Cell(7,5, '#',1,0,'C',1);
            $pdf->Cell(14,5, 'Cat.',1,0,'C',1);
            $pdf->Cell(23,5, 'Team A',1,0,'C',1);
            $pdf->Cell(23,5, 'Team B',1,1,'C',1);
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
                        $pdf->Cell(7,5, $tab_terrain[$i][0]['Numero_ordre'],1,0,'C', 1);
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
                        
//                        if(strlen($tab_terrain[$i][0]['Arbitre_principal']) > 18) {
//                            $pdf->SetFont('Arial','',4);
//                        } elseif(strlen($tab_terrain[$i][0]['Arbitre_principal']) > 10) {
//                            $pdf->SetFont('Arial','',5);
//                        } else {
//                            $pdf->SetFont('Arial','',6);
//                        }
//                        $pdf->Cell(17,5, str_replace('-1', '', $tab_terrain[$i][0]['Arbitre_principal']),1,$findeligne,'C');
                    } else {
                        $pdf->SetFont('Arial', '', 6);
                        $pdf->Cell(67, 5, 'Break', 1, $findeligne, 'C', 1);
                    }
                }
            
            }
        }
				

						
//                        
						
//				$heure2 = $row['Heure_match'];
//				if ($heure1 != $heure2) {
//                    $terrain = 1;
//                    
//                    
//                } else {
//                    $terrain ++;
//                    switch ($terrain) {
//                        case 2:
//                            if($row['Terrain'] == 2) {
//                                $pdf->Cell(7,5, $row['Numero_ordre'],1,0,'C');
//                                $pdf->Cell(9,5, $row['Code_competition'],1,0,'C');
//                                $pdf->Cell(17,5, $row['EquipeA'],1,0,'C');
//                                $pdf->Cell(17,5, $row['EquipeB'],1,0,'C');
//                                $pdf->Cell(17,5, $row['Arbitre_principal'],1,0,'C');
//                            } else {
//                                $pdf->Cell(67, 5, 'Pause', 1,0,'C');
//                            }
//                        case 3:
//                            if($row['Terrain'] == 3) {
//                                $pdf->Cell(7,5, $row['Numero_ordre'],1,0,'C');
//                                $pdf->Cell(9,5, $row['Code_competition'],1,0,'C');
//                                $pdf->Cell(17,5, $row['EquipeA'],1,0,'C');
//                                $pdf->Cell(17,5, $row['EquipeB'],1,0,'C');
//                                $pdf->Cell(17,5, $row['Arbitre_principal'],1,0,'C');
//                            } else {
//                                $pdf->Cell(67, 5, 'Pause', 1,0,'C');
//                            }
//                        case 4:
//                            if($row['Terrain'] == 4) {
//                                $pdf->Cell(7,5, $row['Numero_ordre'],1,0,'C');
//                                $pdf->Cell(9,5, $row['Code_competition'],1,0,'C');
//                                $pdf->Cell(17,5, $row['EquipeA'],1,0,'C');
//                                $pdf->Cell(17,5, $row['EquipeB'],1,0,'C');
//                                $pdf->Cell(17,5, $row['Arbitre_principal'],1,1,'C');
//                            } else {
//                                $pdf->Cell(67, 5, 'Pause', 1,1,'C');
//                            }
//                            break;
//                    }
//                        
//                }
//                $heure1 = $heure2;
								
//		}
		//$pdf->Cell(22,3, '',0,0,'C');
		$pdf->Cell(271,3,'','T','1','C');
		$pdf->Output('Game list'.'.pdf','I');
	}
}

$page = new PdfListeMatchs();

