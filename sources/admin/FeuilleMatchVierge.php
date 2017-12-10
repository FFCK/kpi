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
		if($_GET['lang'] == 'en')
		{
			$lang = $langue[en];
			$date =  $row['Date_match'];
			$dateprint = date('Y-m-d');
		}
		else
		{
			$lang = $langue[fr];
			$date =  utyDateUsToFr($row['Date_match']);
			$dateprint = date('d/m/Y');
		}

		$responsableT = $lang['R1'].': ';
		$delegueT = $lang['Delegue'].': ';
		
		// Production de la feuille de match PDF suivante
		$pdf->AddPage();
		$pdf->SetAutoPageBreak(true, 1);

		//variables :
		$pdf->SetSubject("Match ".$equipea."/".$equipeb);
		$pdf->SetKeywords("kayak-polo, canoe-polo, match, canoe, kayak, ".$equipea.", ".$equipeb);
		
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
		if($row6['Titre_actif'] == 'O')
			$pdf->Cell(111,4,$competition,'L','0','L');
		else
			$pdf->Cell(111,4,$row6['Soustitre'],'L','0','L');
		if($row6['Soustitre2'] != '')	
			$pdf->Cell(24,4,$row6['Soustitre2'],'R','1','R');
		else	
			$pdf->Cell(24,4,$categorie,'R','1','R');

		$pdf->Cell(111,4,$lang['Organisateur'].": ".ucwords(strtolower($organisateur)),'L','0','L');
		$pdf->Cell(24,4,$lang['Saison'].": ".$saison,'R','1','L');

		$pdf->Cell(68,4,$responsableT.ucwords(strtolower($responsable)),'L','0','L');
		$pdf->Cell(67,4,$delegueT.ucwords(strtolower($delegue)),'R','1','L');

		$pdf->Cell(135,1,"",'LR','1','C');
		$pdf->Cell(135,1,"",'LTR','1','C',1);
		$pdf->Cell(60,4,$lang['Lieu'].": ".$lieu." (".$dpt.")",'L','0','L',1);
		$pdf->SetFont('Arial','B',12);
		$pdf->Cell(40,4,$date."   ".$heure,'0','0','L',1);
		$pdf->Cell(35,4,$lang['Terrain'].": ".$terrain,'R','1','R',1);

		$pdf->SetFont('Arial','',10);
		$pdf->Cell(60,4,$lang['Phase'].": ".$phase,'L','0','L',1);
		$pdf->SetFont('Arial','B',12);
		$pdf->Cell(30,4,$lang['Match_no'].$no,'0','0','L',1);
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(45,4,$intitule,'R','1','R',1);

		$pdf->Cell(135,1,"",'LR','1','C',1);
		$pdf->Cell(135,1,"",'LTR','1','L');
		$pdf->Cell(135,4,$lang['Arbitre_1'].": ".$principal,'LR','1','L');
		$pdf->Cell(135,4,$lang['Arbitre_2'].": ".$secondaire,'LR','1','L');
		$pdf->Cell(135,4,$lang['Secretaire'].": ".$secretaire,'LR','1','L');
		$pdf->Cell(135,4,$lang['Chronometre'].": ".$chronometre,'LR','1','L');
		$pdf->Cell(135,4,$lang['Time_shoot2'].": ".$timeshoot,'LR','1','L');
		$pdf->Cell(135,1,"",'LBR','1','C');

		//Equipe A

		$pdf->Ln(1);

		$pdf->Cell(45,5,$lang['Equipe_A'].":",'LTB','0','C',1);
		$pdf->SetFont('Arial','B',14);
		$pdf->Cell(90,5,$equipea,'TRB','1','C',1);
		$pdf->SetFont('Arial','',10);

		$pdf->Cell(6,6,$lang['Num'],'1','0','C');
		$pdf->Cell(45,6,$lang['Nom'],'1','0','C');
		$pdf->Cell(45,6,$lang['Prenom'],'1','0','C');
		$pdf->Cell(24,6,"Licence",'1','0','C');
		$pdf->Cell(15,6,"Cat.",'1','1','C');

		for($i=1 ; $i<=10 ; $i++)
			{
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(6,4,$na[$i],'LRB','0','C');
			$pdf->Cell(45,4,$noma[$i],'LRB','0','C');
			$pdf->Cell(45,4,ucwords(strtolower($prenoma[$i])),'LRB','0','C');
			$pdf->Cell(24,4,$licencea[$i].$saisona[$i],'LRB','0','C');
			$pdf->Cell(15,4,$diva[$i],'LRB','1','C');
			$indiqsaison = '';
			}

		//Equipe B

		$pdf->Ln(1);

		$pdf->SetFont('Arial','',10);
		$pdf->Cell(45,5,$lang['Equipe_B'].":",'LTB','0','C',1);
		$pdf->SetFont('Arial','B',14);
		$pdf->Cell(90,5,$equipeb,'TRB','1','C',1);
		$pdf->SetFont('Arial','',10);

		$pdf->Cell(6,6,$lang['Num'],'1','0','C');
		$pdf->Cell(45,6,$lang['Nom'],'1','0','C');
		$pdf->Cell(45,6,$lang['Prenom'],'1','0','C');
		$pdf->Cell(24,6,"Licence",'1','0','C');
		$pdf->Cell(15,6,"Cat",'1','1','C');

		for($i=1 ; $i<=10 ; $i++)
			{
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(6,4,$nb[$i],'LRB','0','C');
			$pdf->Cell(45,4,$nomb[$i],'LRB','0','C');
			$pdf->Cell(45,4,ucwords(strtolower($prenomb[$i])),'LRB','0','C');
			$pdf->Cell(24,4,$licenceb[$i].$saisonb[$i],'LRB','0','C');
			$pdf->Cell(15,4,$divb[$i],'LRB','1','C');
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
		$pdf->Cell(42,5,$equipea,'TR',0,'C',1);
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(1,5,"",0,0,'C');
		$pdf->Cell(19,5,'','LTR',0,'C');
		$pdf->Cell(1,5,"",0,0,'C');
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(15,5,$lang['Equ_B'].": ",'LT',0,'L',1);
		$pdf->SetFont('Arial','B',11);
		$pdf->Cell(42,5,$equipeb,'TR',1,'C',1);

		$pdf->SetFont('Arial','I',8);
		$pdf->Cell(15,3,'','LB',0,'C',1);
		$pdf->Cell(42,3,$colorA,'RB',0,'C',1);
		$pdf->Cell(1,3,"",0,0,'C');
		$pdf->Cell(19,3,$lang['Periode'],'LR',0,'C');
		$pdf->Cell(1,3,"",0,0,'C');
		$pdf->Cell(15,3,'','LB',0,'C',1);
		$pdf->Cell(42,3,$colorB,'RB',1,'C',1);

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
			$pdf->Cell(5,4,$detail[$i]['d2'],1,0,'C',1);
			$pdf->SetFillColor(255,255,170);
			$pdf->Cell(5,4,$detail[$i]['d3'],1,0,'C',1);
			$pdf->SetFillColor(255,170,170);
			$pdf->Cell(5,4,$detail[$i]['d4'],1,0,'C',1);
			$pdf->Cell(36,4,$detail[$i]['d1'],1,0,'L');
			$pdf->Cell(6,4,$detail[$i]['d5'],1,0,'C');
			$pdf->Cell(1,4,"",0,0,'C');
			$pdf->Cell(19,4,$detail[$i]['d6'],1,0,'C');
			$pdf->Cell(1,4,"",0,0,'C');
			$pdf->Cell(6,4,$detail[$i]['d7'],1,0,'C');
			$pdf->Cell(36,4,$detail[$i]['d11'],1,0,'L');
			$pdf->SetFillColor(170,255,170);
			$pdf->Cell(5,4,$detail[$i]['d8'],1,0,'C',1);
			$pdf->SetFillColor(255,255,170);
			$pdf->Cell(5,4,$detail[$i]['d9'],1,0,'C',1);
			$pdf->SetFillColor(255,170,170);
			$pdf->Cell(5,4,$detail[$i]['d10'],1,1,'C',1);
			}
		$pdf->Ln(1);

		$pdf->SetFont('Arial','I',10);
		$pdf->Cell(44,8,$lang['Equipe_A'],0,0,'C');
		$pdf->Cell(15,8,$scoreMitempsA,1,0,'C');
		$pdf->Cell(17,8,$lang['mi-temps'],0,0,'C');
		$pdf->Cell(15,8,$scoreMitempsB,1,0,'C');
		$pdf->Cell(44,8,$lang['Equipe_A'],0,1,'C');

		$pdf->SetFont('Arial','B',13);
		$pdf->Cell(57,3,"",0,0);
		$pdf->Cell(21,3,$lang['Score'],0,0,'C');
		$pdf->Cell(57,3,"",0,1);

		$pdf->SetLineWidth(1);
		$pdf->SetFont('Arial','B',9);
		$pdf->Cell(41,8,$equipea,0,0,'C');
		$pdf->SetFont('Arial','B',12);
		$pdf->Cell(15,8,$ScoreA,1,0,'C');
		$pdf->Cell(23,8,$typeScore,0,0,'C');
		$pdf->Cell(15,8,$ScoreB,1,0,'C');
		$pdf->SetFont('Arial','B',9);
		$pdf->Cell(41,8,$equipeb,0,1,'C');
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
        $pdf->Cell(21,4,$heure_debut,'LRB',0,'C');
        $pdf->Cell(21,4,$heure_fin,'LRB',1,'C');
        $pdf->Cell(114,4,$Commentaires1,'LR',0,'L');
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
        $pdf->Cell(21,4,"ID #".$idMatch,1,1,'C');
        $pdf->SetX(10);
        $pdf->SetFont('Arial','',6);
        $pdf->Cell(140,3,$lang['observation'],0,0,'L');
            
        		
		$pdf->Cell(135,3,"ID #".$idMatch." - ".$lang['impression'].": ".$dateprint." ".date("H:i"),0,1,'R');

		$pdf->SetX(10);
		//	$pdf->Cell(135,3,"Renvoyer cette feuille au plus tard dans les 5 jours à l'autorité compétente.",0,0,'L');
		
		$pdf->Output('Match(s) '.$listMatch.'.pdf','I');
	}
}

//Création des feuilles
$page = new FeuilleMatch();

