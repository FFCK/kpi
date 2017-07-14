<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

define('FPDF_FONTPATH','font/');
require('../fpdf/fpdf.php');

// Gestion de la Feuille de Classement

class FeuilleCltNiveau extends MyPageSecure	 
{	
	function FeuilleCltNiveau()
	{
	  MyPageSecure::MyPageSecure(2);
	  
	  $codeCompet = utyGetSession('codeCompet', '');
	  
		$pdf = new FPDF('P');
		$pdf->Open();
		$pdf->SetTitle("Classement");
		
		$pdf->SetAuthor("Poloweb.org");
		$pdf->SetCreator("Poloweb.org avec FPDF");
		$pdf->AddPage();
		$pdf->SetAutoPageBreak(true, 1);
		$pdf->Image('./fpdf/poloweb.png',90,8,40,8,'png',"http://www.poloweb.org");
		$pdf->Ln(7);
		
		$pdf->SetFont('Arial','B',14);
		$pdf->Cell(190,6,"FEUILLE DE CLASSEMENT : ".$codeCompet,'1','1','C');
		
		$myBdd = new MyBdd();
		
		$sql  = "Select Id, Libelle, Code_club, Clt, Pts, J, G, N, P, F, Plus, Moins, Diff, PtsNiveau, CltNiveau ";
		$sql .= "From gickp_Competitions_Equipes ";
		$sql .= "Where Code_compet = '";
		$sql .= $codeCompet;
		$sql .= "' And Code_saison = '";
		$sql .= ACTIVE_SAISON;
		$sql .= "' Order By CltNiveau Asc ";	 
	
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load");
		$num_results = mysql_num_rows($result);
		
		$pdf->Ln(7);
		
		$pdf->Cell(30,6, '','','0','L');
		$pdf->Cell(30, 6, 'Clt', 'LTR','0','L');
		$pdf->Cell(100,6, 'Equipe','RT','1','L');
		

		for ($i=0;$i<$num_results;$i++)
		{
				$row = mysql_fetch_array($result);	
				
				$pdf->SetFont('Arial','B',13);

				$pdf->Cell(30,6, '','','0','L');
				$pdf->Cell(30, 5, $row['CltNiveau'], 'LTBR','0','L');
				$pdf->Cell(100,5, $row['Libelle'],'RTB','1','L');
		}
			
		$pdf->SetFont('Arial','I',8);
		$pdf->SetXY(94, 258);
		if($lang == $langue['en'])
			$pdf->Write(4, date('Y-m-d H:i'));
		else
			$pdf->Write(4, date('d/m/Y à H:i'));
		$pdf->Output();
	}
}

$page = new FeuilleCltNiveau();
