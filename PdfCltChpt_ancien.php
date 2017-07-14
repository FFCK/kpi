<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

define('FPDF_FONTPATH','font/');
require('fpdf/fpdf.php');

// Gestion de la Feuille de Classement

class FeuilleCltNiveau extends MyPage	 
{	
	function FeuilleCltNiveau()
	{
	  MyPage::MyPage();
	  
		$myBdd = new MyBdd();
		
		$codeCompet = utyGetSession('codeCompet', '');
		$codeSaison = utyGetSaison();
		$arrayCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);

		$titreCompet = 'Compétition : '.$arrayCompetition['Libelle'].' ('.$codeCompet.')';
		$qualif = $arrayCompetition['Qualifies'];
		$elim = $arrayCompetition['Elimines'];
		
		//Saison
			$titreDate = "Saison ".$codeSaison;

		//Création
		$pdf = new FPDF('L');
		$pdf->Open();
		$pdf->SetTitle("Classement général officiel");
		
		$pdf->SetAuthor("kayak-polo.info");
		$pdf->SetCreator("kayak-polo.info avec FPDF");
		$pdf->AddPage();
		$pdf->SetAutoPageBreak(true, 15);
		
		// logo
		$pdf->Image($_SERVER['DOCUMENT_ROOT'].'/css/banniere1.jpg',10,8,72,15,'jpg',"http://www.kayak-polo.info");
		$pdf->Image($_SERVER['DOCUMENT_ROOT'].'/css/logo_ffck4.png',258,8,24,15,'png',"http://www.ffck.org");
		$pdf->Ln(14);
		// titre
		$pdf->SetFont('Arial','BI',10);
		$pdf->Cell(137,5,"Classement type championnat",'LT','0','L');
		$pdf->Cell(136,5,$titreDate,'TR','1','R');
		$pdf->SetFont('Arial','B',14);
		$pdf->Cell(273,5,$titreCompet,'LR','1','C');
		$pdf->Cell(273,5,"Classement général",'LRB','1','C');

		$pdf->SetFont('Arial','BI',9);
		$pdf->Cell(137,5,"Edité le ".date("d/m/Y")." à ".date("H:i"),'0','0','L');
		$pdf->Cell(136,5,"Classement officiel",'0','1','R');
		$pdf->Ln(4);
		
		//données
		
		$sql  = "Select Id, Libelle, Code_club, Clt_publi, Pts_publi, J_publi, G_publi, N_publi, P_publi, F_publi, Plus_publi, Moins_publi, Diff_publi, PtsNiveau_publi, CltNiveau_publi ";
		$sql .= "From gickp_Competitions_Equipes ";
		$sql .= "Where Code_compet = '";
		$sql .= $codeCompet;
		$sql .= "' And Code_saison = '";
		$sql .= $codeSaison;
		$sql .= "' Order By Clt_publi Asc, Diff_publi Desc ";	 
	
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load");
		$num_results = mysql_num_rows($result);
		
		// recalcul des éliminés
		$elim = $num_results - $elim;
		
		
		$pdf->SetFont('Arial','B',10);
	
		$pdf->Cell(16,5, '', '0','0');
		$pdf->Cell(20,5, 'Class.', 'LTR','0','C');
		$pdf->Cell(55,5, 'Equipe','TR','0','C');
		$pdf->Cell(20,5, 'Points','TR','0','C');
		$pdf->Cell(18,5, 'Joués','TR','0','C');
		$pdf->Cell(18,5, 'Gagnés','TR','0','C');
		$pdf->Cell(18,5, 'Nuls','TR','0','C');
		$pdf->Cell(18,5, 'Perdus','TR','0','C');
		$pdf->Cell(18,5, 'Forfaits','TR','0','C');
		$pdf->Cell(18,5, 'Plus','TR','0','C');
		$pdf->Cell(18,5, 'Moins','TR','0','C');
		$pdf->Cell(20,5, 'Diff','TR','1','C');
		
		$pdf->Cell(16,5, '', '0','0');
		$pdf->Cell(241,3, '', 'TB','1');

		for ($i=0;$i<$num_results;$i++)
		{
				$row = mysql_fetch_array($result);	
				$separation = 0;
				//Séparation qualifiés
				if (($i+1) > $qualif && $qualif != 0)
				{
					$pdf->Cell(16,5, '', 0,0);
					$pdf->Cell(241,1, '', 0,1);
					$qualif =0;
					$separation = 1;
				}
				//Séparation éliminés
				if (($i+1) > $elim && $elim != 0)
				{
					if ($separation != 1)
					{
						$pdf->Cell(16,5, '', 0,0);
						$pdf->Cell(241,1, '', 0,1);
					}
					$elim =0;
				}
					
				
				$pts = $row['Pts_publi'];
				$len = strlen($pts);
				if ($len > 2)
				{
					if (substr($pts, $len-2, 2) == '00')
						$pts = substr($pts, 0, $len-2);
					else
						$pts = substr($pts, 0, $len-2).'.'.substr($pts, $len-2, 2);
				}
				
				$pdf->Cell(16,5, '', '0','0');
				$pdf->Cell(20, 5, $row['Clt_publi'], 'LTBR','0','C');
				$pdf->Cell(55, 5, $row['Libelle'],'LTBR','0','L');
				$pdf->Cell(20, 5, $pts,'LTBR','0','C');
				$pdf->Cell(18, 5, $row['J_publi'],'LTBR','0','C');
				$pdf->Cell(18, 5, $row['G_publi'],'LTBR','0','C');
				$pdf->Cell(18, 5, $row['N_publi'],'LTBR','0','C');
				$pdf->Cell(18, 5, $row['P_publi'],'LTBR','0','C');
				$pdf->Cell(18, 5, $row['F_publi'],'LTBR','0','C');
				$pdf->Cell(18, 5, $row['Plus_publi'],'LTBR','0','C');
				$pdf->Cell(18, 5, $row['Moins_publi'],'LTBR','0','C');
				$pdf->Cell(20, 5, $row['Diff_publi'],'LTBR','1','C');
		}
			
		$pdf->Output('Classement '.$codeCompet.'.pdf','I');
	}
}

$page = new FeuilleCltNiveau();
