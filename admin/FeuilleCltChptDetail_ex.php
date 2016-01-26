<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

define('FPDF_FONTPATH','font/');
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

// Gestion de la Feuille de Classement
class FeuilleCltNiveau extends MyPage	 
{	
	function FeuilleCltNiveau()
	{
	  MyPage::MyPage();
	  
		$codeCompet = utyGetSession('codeCompet', '');
		//Saison
			$titreDate = "Saison ".utyGetSaison();

		//Création
		$pdf = new PDF('P');
		$pdf->Open();
		$pdf->SetTitle("Classement détaillé");
		
		$pdf->SetAuthor("kayak-polo.info");
		$pdf->SetCreator("kayak-polo.info");
		$pdf->AddPage();
		$pdf->SetAutoPageBreak(true, 15);

		// logo
		$pdf->Image($_SERVER['DOCUMENT_ROOT'].'/css/banniere1.jpg',10,8,72,15,'jpg',"http://www.kayak-polo.info");
		$pdf->Image($_SERVER['DOCUMENT_ROOT'].'/css/logo_ffck4.png',176,8,24,15,'png',"http://www.ffck.org");
		$pdf->Ln(14);
		// titre
		$pdf->SetFont('Arial','BI',9);
		$pdf->Cell(95,5,"Compétition type championnat",'LT','0','L');
		$pdf->Cell(95,5,$titreDate,'TR','1','R');
		$pdf->SetFont('Arial','B',12);
		$pdf->Cell(190,5,utyGetLabelCompetition($codeCompet),'LR','1','C');
		$pdf->Cell(190,5,"Détail par équipe",'LRB','1','C');

		$pdf->SetFont('Arial','BI',8);
		$pdf->Cell(95,5,"Edité le ".date("d/m/Y")." à ".date("H:i"),'0','0','L');
		$pdf->Cell(95,5,"Classement provisoire",'0','1','R');
		$pdf->Ln(4);


		//données
		$myBdd = new MyBdd();
		
		$sql  = "Select Id, Libelle, Code_club, Clt, Pts, J, G, N, P, F, Plus, Moins, Diff, PtsNiveau, CltNiveau ";
		$sql .= "From gickp_Competitions_Equipes ";
		$sql .= "Where Code_compet = '";
		$sql .= $codeCompet;
		$sql .= "' And Code_saison = '";
		$sql .= utyGetSaison();
		$sql .= "' Order By Clt Asc, Diff Desc ";	 
	
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load");
		$num_results = mysql_num_rows($result);
		

		for ($i=0;$i<$num_results;$i++)
		{
				$row = mysql_fetch_array($result);	
				$idEquipe = $row['Id'];
				
				$pts = $row['Pts'];
				$len = strlen($pts);
				if ($len > 2)
				{
					if (substr($pts, $len-2, 2) == '00')
						$pts = substr($pts, 0, $len-2);
					else
						$pts = substr($pts, 0, $len-2).'.'.substr($pts, $len-2, 2);
				}
				
				$pdf->SetFont('Arial','B',9);

				$pdf->Cell(10,5, 'Clt', 'LTR','0','L');
				$pdf->Cell(45,5, 'Equipe','TR','0','L');
				$pdf->Cell(15,5, 'Pts','TR','0','C');
				$pdf->Cell(15,5, 'J','TR','0','C');
				$pdf->Cell(15,5, 'G','TR','0','C');
				$pdf->Cell(15,5, 'N','TR','0','C');
				$pdf->Cell(15,5, 'P','TR','0','C');
				$pdf->Cell(15,5, 'F','TR','0','C');
				$pdf->Cell(15,5, 'Plus','TR','0','C');
				$pdf->Cell(15,5, 'Moins','TR','0','C');
				$pdf->Cell(15,5, 'Diff','TR','1','C');
				
				$pdf->Cell(10, 5, $row['Clt'], 'LTBR','0','R');
				$pdf->Cell(45, 5, $row['Libelle'],'LTBR','0','L');
				$pdf->Cell(15, 5, $pts,'LTBR','0','C');
				$pdf->Cell(15, 5, $row['J'],'LTBR','0','C');
				$pdf->Cell(15, 5, $row['G'],'LTBR','0','C');
				$pdf->Cell(15, 5, $row['N'],'LTBR','0','C');
				$pdf->Cell(15, 5, $row['P'],'LTBR','0','C');
				$pdf->Cell(15, 5, $row['F'],'LTBR','0','C');
				$pdf->Cell(15, 5, $row['Plus'],'LTBR','0','C');
				$pdf->Cell(15, 5, $row['Moins'],'LTBR','0','C');
				$pdf->Cell(15, 5, $row['Diff'],'LTBR','1','C');
				
				$sql  = "Select a.Id_equipeA, a.ScoreA, c.Libelle LibelleA, ";
				$sql .= "       a.Id_equipeB, a.ScoreB, d.Libelle LibelleB, ";
				$sql .= " a.Id, a.Id_journee, a.Date_match, a.Heure_match, a.Terrain, b.Lieu ";
				$sql .= "From gickp_Journees b, gickp_Matchs a ";
				$sql .= "Left Outer Join gickp_Competitions_Equipes c On (c.Id = a.Id_equipeA) ";
				$sql .= "Left Outer Join gickp_Competitions_Equipes d On (d.Id = a.Id_equipeB) ";
				$sql .= "Where a.Id_journee = b.Id ";
				$sql .= "And b.Code_competition = '";
				$sql .= $codeCompet;
				$sql .= "' And b.Code_saison = '";
				$sql .= utyGetSaison();
				$sql .= "' And (a.Id_equipeA = $idEquipe Or a.Id_equipeB = $idEquipe) ";	 
				$sql .= "Order by b.Niveau, b.Phase, a.Date_match, a.Heure_match ";
			
				$result2 = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load 2");
				$num_results2 = mysql_num_rows($result2);
			
				$pdf->SetFont('Arial','B',8);
				
				for ($j=0;$j<$num_results2;$j++)
				{
						$row2 = mysql_fetch_array($result2);	
						
						if ( ($row2['ScoreA'] == '') || ($row2['ScoreA'] == '?') )
							continue; // Score non valide ...
						
						if ( ($row2['ScoreB'] == '') || ($row2['ScoreB'] == '?') )
							continue; // Score non valide ...

						$txt = $row2['Lieu'];
						$txt .= " - le ";
						$txt .= utyDateUsToFr($row2['Date_match']);
						$txt .= " à ";
						$txt .= $row2['Heure_match'];
						//$txt .= ", terrain ";
						//$txt .= $row2['Terrain'];
						$txt .= "   -   ";					
						$txt .= $row2['LibelleA'].' contre '.$row2['LibelleB']. ' : '.$row2['ScoreA'].' - '.$row2['ScoreB'];

						$pdf->Cell(190, 4, $txt, '0','1','C');
				}
								
				$pdf->Ln(4);
		}
			
		$pdf->Output('Classement par équipe '.$codeCompet,'I');
	}
}

$page = new FeuilleCltNiveau();

?>
