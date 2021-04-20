<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

require('../fpdf/fpdf.php');

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
	    $this->Cell(0,10,'Page '.$this->PageNo(),0,0,'C');
	}
}

// Gestion de la Feuille de Classement par Niveau
class FeuilleCltNiveauNiveau extends MyPage	 
{	
	function FeuilleCltNiveauNiveau()
	{
		MyPage::MyPage();
	  
		$myBdd = new MyBdd();
		$codeCompet = utyGetSession('codeCompet', '');
		//Saison
        $codeSaison = $myBdd->GetActiveSaison();
        $titreDate = "Saison ".$myBdd->GetActiveSaison();
        
        $arrayCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);
        if ($arrayCompetition['BandeauLink'] != '' && strpos($arrayCompetition['BandeauLink'], 'http') === FALSE ){
            $arrayCompetition['BandeauLink'] = '../img/logo/' . $arrayCompetition['BandeauLink'];
            if (is_file($arrayCompetition['BandeauLink'])) {
                $bandeau = $arrayCompetition['BandeauLink'];
            }
        } elseif ($arrayCompetition['BandeauLink'] != '') {
            $bandeau = $arrayCompetition['BandeauLink'];
        } 
        if ($arrayCompetition['LogoLink'] != '' && strpos($arrayCompetition['LogoLink'], 'http') === FALSE ){
            $arrayCompetition['LogoLink'] = '../img/logo/' . $arrayCompetition['LogoLink'];
            if (is_file($arrayCompetition['LogoLink'])) {
                $logo = $arrayCompetition['LogoLink'];
            }
        } elseif ($arrayCompetition['LogoLink'] != '') {
            $logo = $arrayCompetition['LogoLink'];
        }
        
        if ($arrayCompetition['SponsorLink'] != '' && strpos($arrayCompetition['SponsorLink'], 'http') === FALSE ){
            $arrayCompetition['SponsorLink'] = '../img/logo/' . $arrayCompetition['SponsorLink'];
            if(is_file($arrayCompetition['SponsorLink'])) {
                $sponsor = $arrayCompetition['SponsorLink'];
            }
        } elseif ($arrayCompetition['SponsorLink'] != '') {
            $sponsor = $arrayCompetition['SponsorLink'];
        }

		//Création
		$pdf = new PDF('P');
		$pdf->Open();
		$pdf->SetTitle("Classement par niveau");
		
		$pdf->SetAuthor("kayak-polo.info");
		$pdf->SetCreator("kayak-polo.info");
		$pdf->AddPage();
		$pdf->SetAutoPageBreak(true, 15);

		// logo
		if ($arrayCompetition['Kpi_ffck_actif'] == 'O') {
			$pdf->Image('../img/CNAKPI_small.jpg',84,10,0,20,'jpg',"http://www.ffck.org");
		}

		if ($arrayCompetition['Bandeau_actif'] == 'O' && isset($bandeau)) {
			$size = getimagesize($bandeau);
			$largeur=$size[0];
			$hauteur=$size[1];
			$ratio=20/$hauteur;	//hauteur imposée de 20mm
			$newlargeur=$largeur*$ratio;
			$posi=105-($newlargeur/2);	//210mm = largeur de page
			$pdf->image($bandeau, $posi, 8, 0,20);
		} elseif ($arrayCompetition['Logo_actif'] == 'O' && isset($logo)) {
			$size = getimagesize($logo);
			$largeur=$size[0];
			$hauteur=$size[1];
			$ratio=20/$hauteur;	//hauteur imposée de 20mm
			$newlargeur=$largeur*$ratio;
			$posi=105-($newlargeur/2);	//210mm = largeur de page
			$pdf->image($logo, $posi, 8, 0,20);
		}

		if ($arrayCompetition['Sponsor_actif'] == 'O' && isset($sponsor)) {
			$size = getimagesize($sponsor);
			$largeur=$size[0];
			$hauteur=$size[1];
			$ratio=16/$hauteur;	//hauteur imposée de 16mm
			$newlargeur=$largeur*$ratio;
			$posi=105-($newlargeur/2);	//210mm = largeur de page
			$pdf->image($sponsor, $posi, 267, 0,16);
		}

        $pdf->Ln(14);
		// titre
		$pdf->SetFont('Arial','BI',9);
		$pdf->Cell(95,5,"Compétition à élimination",'LT','0','L');
		$pdf->Cell(95,5,$titreDate,'TR','1','R');
		$pdf->SetFont('Arial','B',12);
		$pdf->Cell(190,5,utyGetLabelCompetition($codeCompet),'LR','1','C');
		$pdf->Cell(190,5,"Classement par niveau",'LRB','1','C');

		$pdf->SetFont('Arial','BI',8);
		$pdf->Cell(95,5,"Edité le ".date("d/m/Y")." à ".date("H:i", strtotime($_SESSION['tzOffset'])),'0','0','L');
		$pdf->Cell(95,5,"Classement provisoire",'0','1','R');

		// données
		
		$sql = "SELECT a.Id, a.Libelle, a.Code_club, b.Niveau, b.Clt, b.Pts, b.J, b.G, 
			b.N, b.P, b.F, b.Plus, b.Moins, b.Diff, b.PtsNiveau, b.CltNiveau 
			FROM kp_competition_equipe a, kp_competition_equipe_niveau b 
			WHERE a.Id = b.Id 
			AND a.Code_compet = ? 
			AND a.Code_saison = ? 
			ORDER BY b.Niveau, b.CltNiveau, b.Diff DESC ";	 
        $result = $myBdd->pdo->prepare($sql);
        $result->execute(array($codeCompet, $codeSaison));
	
		$niveau = -1;
        while ($row = $result->fetch()){
			$idEquipe = $row['Id'];
			if ($row['Niveau'] != $niveau) {
				$niveau = $row['Niveau'];
				
				$pdf->Ln(5);
				$pdf->SetFont('Arial','B',12);
				$pdf->Cell(100,5,"Niveau ".$niveau, 'LTBR','0','C');
				$pdf->SetFont('Arial','B',9);
				$pdf->Cell(10,5, "Pts", 'LTBR','0','C');
				$pdf->Cell(10,5, "J", 'LTBR','0','C');
				$pdf->Cell(10,5, "G", 'LTBR','0','C');
				$pdf->Cell(10,5, "N", 'LTBR','0','C');
				$pdf->Cell(10,5, "P", 'LTBR','0','C');
				$pdf->Cell(10,5, "F", 'LTBR','0','C');
				$pdf->Cell(10,5, "Plus", 'LTBR','0','C');
				$pdf->Cell(10,5, "Moins", 'LTBR','0','C');
				$pdf->Cell(10,5, "Diff", 'LTBR','1','C');
			}
			
			$pts = $row['Pts'];
			$len = strlen($pts);
			if ($len > 2) {
				if (substr($pts, $len-2, 2) == '00')
					$pts = substr($pts, 0, $len-2);
				else
					$pts = substr($pts, 0, $len-2).'.'.substr($pts, $len-2, 2);
			}

			$pdf->SetFont('Arial','B',9);
			$pdf->Cell(20, 5, $row['CltNiveau'], 'LTBR','0','C');
			$pdf->Cell(80,5, $row['Libelle'],'RTB','0','C');
			$pdf->Cell(10,5, $pts, 'LTBR','0','C');
			$pdf->Cell(10,5, $row['J'], 'LTBR','0','C');
			$pdf->Cell(10,5, $row['G'], 'LTBR','0','C');
			$pdf->Cell(10,5, $row['N'], 'LTBR','0','C');
			$pdf->Cell(10,5, $row['P'], 'LTBR','0','C');
			$pdf->Cell(10,5, $row['F'], 'LTBR','0','C');
			$pdf->Cell(10,5, $row['Plus'], 'LTBR','0','C');
			$pdf->Cell(10,5, $row['Moins'], 'LTBR','0','C');
			$pdf->Cell(10,5, $row['Diff'], 'LTBR','1','C');
		}
			
		$pdf->Output('Classement par niveau '.$codeCompet.'.pdf','I');
	}
}

$page = new FeuilleCltNiveauNiveau();
