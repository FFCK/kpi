<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

require('../fpdf/fpdf.php');

// Gestion de la Feuille de Match
class PDF extends FPDF
{
var $x0;
}

class FeuilleMatch extends MyPage	 
{	
	
	function FeuilleMatch()
	{
		MyPage::MyPage();

		//Création du PDF de base
		$pdf=new PDF('L');
		$pdf->Open();
		$pdf->SetTitle("Feuille de Marque");

		$pdf->SetAuthor("FFCK - Kayak-polo.info");
		$pdf->SetCreator("FFCK - Kayak-polo.info avec FPDF");
		
		// Langue
		$langue = parse_ini_file("../commun/MyLang.ini", true);
		if (utyGetGet('lang') == 'en') {
			$lang = $langue['en'];
			$date = '';
			$dateprint = date('Y-m-d');
		} else {
			$lang = $langue['fr'];
			$date = '';
			$dateprint = date('d/m/Y');
		}

		$responsableT = $lang['R1'].': ';
		$delegueT = $lang['Delegue'].': ';
		
		// Production de la feuille de match PDF suivante
		$pdf->AddPage();
		$pdf->SetAutoPageBreak(true, 1);

		//variables :
		$pdf->SetSubject("Match ");
		$pdf->SetKeywords("kayak-polo, canoe-polo, match, canoe, kayak");
		
		//Colonne 1
		$x0=10;
		$pdf->SetLeftMargin($x0);
		$pdf->SetX($x0);
		$pdf->SetY(9);

		// Logos
        $pdf->Image('../img/logoKPI-small.jpg',65,10,0,11,'jpg',"http://www.ffck.org");

		$pdf->Ln(11);

		$pdf->SetFillColor(200,200,200);
		$pdf->SetFont('Arial','B',14);
//		$pdf->Cell(135,6,$lang['FEUILLE_DE_MARQUE'],'B','1','C');
        $pdf->Cell(135,2,'','B','1','C');
        
		$pdf->SetFont('Arial','I',7);
//		$pdf->Cell(135,4,$lang['A_remplir'],'LR','1','C');
		$pdf->Cell(135,1,"",'LR','1','C');

		$pdf->SetFont('Arial','',10);
		$pdf->Cell(111,4,'','L','0','L');
		$pdf->Cell(24,4,'','R','1','R');

		$pdf->Cell(111,4,$lang['Organisateur'].": ",'L','0','L');
		$pdf->Cell(24,4,$lang['Saison'].": ",'R','1','L');

		$pdf->Cell(68,4,$responsableT,'L','0','L');
		$pdf->Cell(67,4,$delegueT,'R','1','L');

		$pdf->Cell(135,1,"",'LR','1','C');
		$pdf->Cell(135,1,"",'LTR','1','C',1);
		$pdf->Cell(60,4,$lang['Lieu'].": ",'L','0','L',1);
		$pdf->SetFont('Arial','B',12);
		$pdf->Cell(40,4,"Date/Heure",'0','0','L',1);
		$pdf->Cell(35,4,$lang['Terrain'].": ",'R','1','R',1);

		$pdf->SetFont('Arial','',10);
		$pdf->Cell(60,4,$lang['Phase'].": ",'L','0','L',1);
		$pdf->SetFont('Arial','B',12);
		$pdf->Cell(30,4,$lang['Match_no'],'0','0','L',1);
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(45,4,'','R','1','R',1);

		$pdf->Cell(135,1,"",'LR','1','C',1);
		$pdf->Cell(135,1,"",'LTR','1','L');
		$pdf->Cell(135,4,$lang['Arbitre_1'].": ",'LR','1','L');
		$pdf->Cell(135,4,$lang['Arbitre_2'].": ",'LR','1','L');
		$pdf->Cell(135,4,$lang['Secretaire'].": ",'LR','1','L');
		$pdf->Cell(135,4,$lang['Chronometre'].": ",'LR','1','L');
		$pdf->Cell(135,4,$lang['Time_shoot2'].": ",'LR','1','L');
		$pdf->Cell(135,1,"",'LBR','1','C');

		//Equipe A

		$pdf->Ln(1);

		$pdf->Cell(45,5,$lang['Equipe_A'].":",'LTB','0','C',1);
		$pdf->SetFont('Arial','B',14);
		$pdf->Cell(90,5,'','TRB','1','C',1);
		$pdf->SetFont('Arial','',10);

		$pdf->Cell(6,6,$lang['Num'],'1','0','C');
		$pdf->Cell(45,6,$lang['Nom'],'1','0','C');
		$pdf->Cell(45,6,$lang['Prenom'],'1','0','C');
		$pdf->Cell(24,6,"Licence",'1','0','C');
		$pdf->Cell(15,6,"Cat.",'1','1','C');

		for($i=1 ; $i<=10 ; $i++)
			{
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(6,4,'','LRB','0','C');
			$pdf->Cell(45,4,'','LRB','0','C');
			$pdf->Cell(45,4,'','LRB','0','C');
			$pdf->Cell(24,4,'','LRB','0','C');
			$pdf->Cell(15,4,'','LRB','1','C');
			$indiqsaison = '';
			}

		//Equipe B

		$pdf->Ln(1);

		$pdf->SetFont('Arial','',10);
		$pdf->Cell(45,5,$lang['Equipe_B'].":",'LTB','0','C',1);
		$pdf->SetFont('Arial','B',14);
		$pdf->Cell(90,5,'','TRB','1','C',1);
		$pdf->SetFont('Arial','',10);

		$pdf->Cell(6,6,$lang['Num'],'1','0','C');
		$pdf->Cell(45,6,$lang['Nom'],'1','0','C');
		$pdf->Cell(45,6,$lang['Prenom'],'1','0','C');
		$pdf->Cell(24,6,"Licence",'1','0','C');
		$pdf->Cell(15,6,"Cat",'1','1','C');

		for($i=1 ; $i<=10 ; $i++)
			{
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(6,4,'','LRB','0','C');
			$pdf->Cell(45,4,'','LRB','0','C');
			$pdf->Cell(45,4,'','LRB','0','C');
			$pdf->Cell(24,4,'','LRB','0','C');
			$pdf->Cell(15,4,'','LRB','1','C');
			$indiqsaison == '';
			}

		//signatures
		$pdf->Ln(1);
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(21,12,$lang['Signatures'],'LRT','0','C');
		$pdf->Cell(38,12,"",'1','0','C');
		$pdf->Cell(38,12,"",'1','0','C');
		$pdf->Cell(38,12,"",'1','1','C');
		$pdf->Cell(21,4,$lang['avant_match'],'LRB','0','C');
		$pdf->Cell(38,4,$lang['Capitaine']." A",'1','0','C');
		$pdf->Cell(38,4,$lang['Capitaine']." B",'1','0','C');
		$pdf->Cell(38,4,$lang['Arbitre_1'],'1','1','C');

		//Colonne 2

		$x0=150;
		$pdf->SetLeftMargin($x0);
		$pdf->SetX($x0);
		$pdf->SetY(8);

		$pdf->SetFont('Arial','B',14);
		$pdf->Cell(135,6,$lang['FEUILLE_DE_MARQUE'],0,'1','C');

		$pdf->SetFont('Arial','',10);
		$pdf->Cell(15,5,$lang['Equ_A'].": ",'LT',0,'L',1);
		$pdf->SetFont('Arial','B',11);
		$pdf->Cell(42,5,'','TR',0,'C',1);
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(1,5,"",0,0,'C');
		$pdf->Cell(19,5,'','LTR',0,'C');
		$pdf->Cell(1,5,"",0,0,'C');
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(15,5,$lang['Equ_B'].": ",'LT',0,'L',1);
		$pdf->SetFont('Arial','B',11);
		$pdf->Cell(42,5,'','TR',1,'C',1);

		$pdf->SetFont('Arial','I',8);
		$pdf->Cell(15,3,'','LB',0,'C',1);
		$pdf->Cell(42,3,'','RB',0,'C',1);
		$pdf->Cell(1,3,"",0,0,'C');
		$pdf->Cell(19,3,$lang['Periode'],'LR',0,'C');
		$pdf->Cell(1,3,"",0,0,'C');
		$pdf->Cell(15,3,'','LB',0,'C',1);
		$pdf->Cell(42,3,'','RB',1,'C',1);

		$pdf->SetFont('Arial','',8);
		$pdf->SetFillColor(170,255,170);
		$pdf->Cell(5,5,$lang['V'],1,0,'C',1);
		$pdf->SetFillColor(255,255,170);
		$pdf->Cell(5,5,$lang['J'],1,0,'C',1);
		$pdf->SetFillColor(255,170,170);
		$pdf->Cell(5,5,$lang['R'],1,0,'C',1);
		$pdf->Cell(36,5,$lang['Num']." - ".$lang['Nom'],1,0,'C');
		$pdf->Cell(6,5,$lang['But'],1,0,'C');
		$pdf->Cell(1,5,"",0,0,'C');
		$pdf->Cell(19,5,"+ ".$lang['Temps'],'LRB','0','C');
		$pdf->Cell(1,5,"",0,0,'C');
		$pdf->Cell(6,5,$lang['But'],1,0,'C');
		$pdf->Cell(36,5,$lang['Num']." - ".$lang['Nom'],1,0,'C');
		$pdf->SetFillColor(170,255,170);
		$pdf->Cell(5,5,$lang['V'],1,0,'C',1);
		$pdf->SetFillColor(255,255,170);
		$pdf->Cell(5,5,$lang['J'],1,0,'C',1);
		$pdf->SetFillColor(255,170,170);
		$pdf->Cell(5,5,$lang['R'],1,1,'C',1);

		for($i=0;$i<26;$i++)
//			for($i=0;$i<23;$i++)	// @COSANDCO_WAMPSER
		{
			$pdf->SetFillColor(170,255,170);
			$pdf->Cell(5,4,'',1,0,'C',1);
			$pdf->SetFillColor(255,255,170);
			$pdf->Cell(5,4,'',1,0,'C',1);
			$pdf->SetFillColor(255,170,170);
			$pdf->Cell(5,4,'',1,0,'C',1);
			$pdf->Cell(36,4,'',1,0,'L');
			$pdf->Cell(6,4,'',1,0,'C');
			$pdf->Cell(1,4,"",0,0,'C');
			$pdf->Cell(19,4,'',1,0,'C');
			$pdf->Cell(1,4,"",0,0,'C');
			$pdf->Cell(6,4,'',1,0,'C');
			$pdf->Cell(36,4,'',1,0,'L');
			$pdf->SetFillColor(170,255,170);
			$pdf->Cell(5,4,'',1,0,'C',1);
			$pdf->SetFillColor(255,255,170);
			$pdf->Cell(5,4,'',1,0,'C',1);
			$pdf->SetFillColor(255,170,170);
			$pdf->Cell(5,4,'',1,1,'C',1);
			}
		$pdf->Ln(1);

		$pdf->SetFont('Arial','I',10);
		$pdf->Cell(44,8,$lang['Equipe_A'],0,0,'C');
		$pdf->Cell(15,8,'',1,0,'C');
		$pdf->Cell(17,8,$lang['mi-temps'],0,0,'C');
		$pdf->Cell(15,8,'',1,0,'C');
		$pdf->Cell(44,8,$lang['Equipe_A'],0,1,'C');

		$pdf->SetFont('Arial','B',13);
		$pdf->Cell(57,3,"",0,0);
		$pdf->Cell(21,3,$lang['Score'],0,0,'C');
		$pdf->Cell(57,3,"",0,1);

		$pdf->SetLineWidth(1);
		$pdf->SetFont('Arial','B',9);
		$pdf->Cell(41,8,'',0,0,'C');
		$pdf->SetFont('Arial','B',12);
		$pdf->Cell(15,8,'',1,0,'C');
		$pdf->Cell(23,8,'',0,0,'C');
		$pdf->Cell(15,8,'',1,0,'C');
		$pdf->SetFont('Arial','B',9);
		$pdf->Cell(41,8,'',0,1,'C');
		$pdf->Ln(2);

        $pdf->SetLineWidth(0.2);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(93,4,$lang['Remarques'],'LRT',0,'L');
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(21,4,$lang['Heure_debut'],'LRT',0,'C');
        $pdf->Cell(21,4,$lang['Heure_fin'],'LRT',1,'C');
        $pdf->SetFont('Arial','',7);
        $pdf->Cell(93,3,$lang['A_defaut'],'LR',0,'L');
        $pdf->Cell(21,3,'','LR',0,'L');
        $pdf->Cell(21,3,'','LR',1,'L');
        $pdf->SetFont('Arial','I',9);
        $pdf->Cell(93,4,'','LR',0,'L');
        $pdf->Cell(21,4,'','LRB',0,'C');
        $pdf->Cell(21,4,'','LRB',1,'C');
        $pdf->Cell(114,4,'','LR',0,'L');
        $pdf->Cell(21,4,'','LR',1,'L');
        $pdf->Cell(114,4,'','LRB',0,'L');
        $pdf->Cell(21,4,'','LR',1,'L');

            
        //signatures
        $pdf->Ln(1);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(21,12,$lang['Signatures'],'LRT','0','C');
        $pdf->Cell(31,12,"",'1','0','C');
        $pdf->Cell(31,12,"",'1','0','C');
        $pdf->Cell(31,12,"",'1','0','C');
        $pdf->Cell(21,12,"",'LRB','1','C');
        $pdf->Cell(21,4,$lang['apres_match'],'LRB',0,'C');
        $pdf->Cell(31,4,$lang['Capitaine']." A",1,0,'C');
        //$pdf->Cell(31,4,$lang['Entraineur']." A",1,0,'C');
        $pdf->Cell(31,4,$lang['Capitaine']." B",1,0,'C');
        //$pdf->Cell(31,4,$lang['Entraineur']." B",1,0,'C');
        $pdf->Cell(31,4,$lang['Arbitre_1'],1,0,'C');
        $pdf->SetFont('Arial','',9);
        $pdf->Cell(21,4,"ID #",1,1,'C');
        $pdf->SetX(10);
        $pdf->SetFont('Arial','',6);
        $pdf->Cell(140,3,$lang['observation'],0,0,'L');
            
        		
		$pdf->Cell(135,3,"ID #"."    - ".$lang['impression'].": ".$dateprint." ".date("H:i"),0,1,'R');

		$pdf->SetX(10);
		//	$pdf->Cell(135,3,"Renvoyer cette feuille au plus tard dans les 5 jours à l'autorité compétente.",0,0,'L');
		
		$pdf->Output('Match.pdf','I');
	}
}

//Création des feuilles
$page = new FeuilleMatch();

