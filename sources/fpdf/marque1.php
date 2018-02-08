<?php
require('fpdf.php');

include_once($_SERVER['DOCUMENT_ROOT'].'/commun/MyTools.php');

class PDF extends FPDF
{
var $x0;
}

session_start();

$pdf=new PDF('L');
$pdf->Open();
$pdf->SetTitle("Feuille de Marque");

$pdf->SetAuthor("Poloweb.org");
$pdf->SetCreator("Poloweb.org avec FPDF");
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 1);
$pdf->Image('../css/banniere1.jpg',10,8,38,8,'jpg',"https://www.kayak-polo.info");
$pdf->Image('../css/logo_ffck4.png',130,8,13,8,'png',"http://www.ffck.org");
$pdf->Ln(7);

//variables :
$equipeA=utyGetPDF('equipea');
$equipeB=utyGetPDF('equipeb');

$pdf->SetSubject("Match ".$equipeA."/".$equipeB);
$pdf->SetKeywords("kayak-Polo, match, cano�, kayak, ".$equipeA.", ".$equipeB);
//Colonne 1

$pdf->SetFillColor(200,200,200);
$pdf->SetFont('Arial','B',14);
$pdf->Cell(135,6,"FEUILLE DE MARQUE",'1','1','C');

$pdf->SetFont('Arial','I',8);
$pdf->Cell(135,4,"A remplir par le secr�tariat avant le d�but de match",'LR','1','C');
$pdf->Cell(135,2,"",'LR','1','C');

$pdf->SetFont('Arial','',10);
$pdf->Cell(111,4,"Comp�tition : ".utyGetPDF('competition'),'L','0','L');
$pdf->Cell(24,4,utyGetPDF('categorie'),'R','1','C');

$pdf->Cell(111,4,"Organisateur : ".ucwords(strtolower(utyGetPDF('organisateur'))),'L','0','L');
$pdf->Cell(24,4,"Saison : ".utyGetPDF('saison'),'R','1','L');

$pdf->Cell(68,4,"R1 : ".ucwords(strtolower(utyGetPDF('responsable'))),'L','0','L');
$pdf->Cell(67,4,"D�l�gu� : ".ucwords(strtolower(utyGetPDF('delegue'))),'R','1','L');

$pdf->Cell(135,1,"",'LR','1','C');
$pdf->Cell(135,1,"",'LTR','1','C',1);
$pdf->Cell(60,4,"Lieu : ".utyGetPDF('lieu'),'L','0','L',1);
$pdf->Cell(25,4,"Le  ".utyGetPDF('date'),'0','0','L',1);
$pdf->Cell(20,4," �  ".utyGetPDF('heure'),'0','0','L',1);
$pdf->Cell(30,4,"Terrain : ".utyGetPDF('terrain'),'R','1','L',1);

$pdf->Cell(60,4,"Phase : ".utyGetPDF('phase'),'L','0','L',1);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(25,4,"Match n� ".utyGetPDF('no'),'0','0','L',1);
$pdf->SetFont('Arial','',10);
$pdf->Cell(50,4,utyGetPDF('intitule'),'R','1','C',1);

$pdf->Cell(135,1,"",'LR','1','C',1);
$pdf->Cell(135,1,"",'LTR','1','L');
if (utyGetPDF('principal') == '-1')
	$principal = '';
	else $principal = utyGetPDF('principal');
$pdf->Cell(135,4,"Arbitre principal : ".utyGetPDF('principal'),'LR','1','L');
if (utyGetPDF('secondaire') == '-1')
	$secondaire = '';
	else $secondaire = utyGetPDF('secondaire');
$pdf->Cell(135,4,"Arbitre secondaire : ".utyGetPDF('secondaire'),'LR','1','L');
$pdf->Cell(68,4,"Secr�taire : ".utyGetPDF('secretaire'),'L','0','L');
$pdf->Cell(67,4,"Chronom�tre : ".utyGetPDF('chronometre'),'R','1','L');
$pdf->Cell(135,1,"",'LBR','1','C');

//Equipe A

$pdf->Ln(2);

$pdf->Cell(45,5,"EQUIPE A :",'LTB','0','C',1);
$pdf->SetFont('Arial','B',14);
$pdf->Cell(90,5,$equipeA,'TRB','1','C',1);
$pdf->SetFont('Arial','',10);

$pdf->Cell(6,6,"N�",'1','0','C');
$pdf->Cell(45,6,"Nom",'1','0','C');
$pdf->Cell(45,6,"Pr�nom",'1','0','C');
$pdf->Cell(24,6,"Licence",'1','0','C');
$pdf->Cell(15,6,"Cat.",'1','1','C');

for($i=1 ; $i<=10 ; $i++)
	{
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(6,4,utyGetPDF('na'.$i),'LRB','0','C');
	$pdf->Cell(45,4,utyGetPDF('noma'.$i),'LRB','0','C');
	$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenoma'.$i))),'LRB','0','C');
	//if (utyGetPDF('saisona'.$i) != '' && utyGetPDF('saisona'.$i) < utyGetPDF('saison')) $indiqsaison = ' ('.utyGetPDF('saisona'.$i).')';
	$pdf->Cell(24,4,utyGetPDF('licencea'.$i).utyGetPDF('saisona'.$i),'LRB','0','C');
	$pdf->Cell(15,4,utyGetPDF('diva'.$i),'LRB','1','C');
	$indiqsaison == '';
	}

//Equipe B

$pdf->Ln(2);

$pdf->SetFont('Arial','',10);
$pdf->Cell(45,5,"EQUIPE B :",'LTB','0','C',1);
$pdf->SetFont('Arial','B',14);
$pdf->Cell(90,5,$equipeB,'TRB','1','C',1);
$pdf->SetFont('Arial','',10);

$pdf->Cell(6,6,"N�",'1','0','C');
$pdf->Cell(45,6,"Nom",'1','0','C');
$pdf->Cell(45,6,"Pr�nom",'1','0','C');
$pdf->Cell(24,6,"Licence",'1','0','C');
$pdf->Cell(15,6,"Cat",'1','1','C');

for($i=1 ; $i<=10 ; $i++)
	{
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(6,4,utyGetPDF('nb'.$i),'LRB','0','C');
	$pdf->Cell(45,4,utyGetPDF('nomb'.$i),'LRB','0','C');
	$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenomb'.$i))),'LRB','0','C');
	//if (utyGetPDF('saisonb'.$i) != '' && utyGetPDF('saisonb'.$i) < utyGetPDF('saison')) $indiqsaison = ' ('.utyGetPDF('saisonb'.$i).')';
	$pdf->Cell(24,4,utyGetPDF('licenceb'.$i).utyGetPDF('saisonb'.$i),'LRB','0','C');
	$pdf->Cell(15,4,utyGetPDF('divb'.$i),'LRB','1','C');
	$indiqsaison == '';
	}

//signatures
$pdf->Ln(1);
$pdf->SetFont('Arial','',10);
$pdf->Cell(21,12,"Signatures",'LRT','0','C');
$pdf->Cell(38,12,"",'1','0','C');
$pdf->Cell(38,12,"",'1','0','C');
$pdf->Cell(38,12,"",'1','1','C');
$pdf->Cell(21,4,"avant match",'LRB','0','C');
$pdf->Cell(38,4,"Capitaine A",'1','0','C');
$pdf->Cell(38,4,"Capitaine B",'1','0','C');
$pdf->Cell(38,4,"Arbitre Principal",'1','1','C');

//Colonne 2
$detail = utyGetPDF('detail');

$x0=150;
$pdf->SetLeftMargin($x0);
$pdf->SetX($x0);
$pdf->SetY(8);

$pdf->SetFont('Arial','B',14);
$pdf->Cell(135,6,"DEROULEMENT DU MATCH",'1','1','C');

$pdf->SetFont('Arial','',10);
$pdf->Cell(12,6,"Equ.A: ",'LTB',0,'L',1);
$pdf->SetFont('Arial','B',11);
$pdf->Cell(45,6,$equipeA,'TRB',0,'C',1);
$pdf->SetFont('Arial','',10);
$pdf->Cell(1,6,"",0,0,'C');
$pdf->Cell(19,6,"Mi-temps",'LTR',0,'C');
$pdf->Cell(1,6,"",0,0,'C');
$pdf->SetFont('Arial','',10);
$pdf->Cell(12,6,"Equ.B: ",'LTB',0,'L',1);
$pdf->SetFont('Arial','B',11);
$pdf->Cell(45,6,$equipeB,'TRB',1,'C',1);

$pdf->SetFont('Arial','',10);
//$pdf->Cell(5,5,"N�",'1','0','C');
$pdf->Cell(36,5,"N� - Nom",1,0,'C');
$pdf->SetFillColor(170,255,170);
$pdf->Cell(5,5,"V",1,0,'C',1);
$pdf->SetFillColor(255,255,170);
$pdf->Cell(5,5,"J",1,0,'C',1);
$pdf->SetFillColor(255,170,170);
$pdf->Cell(5,5,"R",1,0,'C',1);
$pdf->Cell(6,5,"But",1,0,'C');
$pdf->Cell(1,5,"",0,0,'C');
$pdf->Cell(19,5,"+ chrono",'LRB','0','C');
$pdf->Cell(1,5,"",0,0,'C');
$pdf->Cell(6,5,"But",1,0,'C');
$pdf->SetFillColor(170,255,170);
$pdf->Cell(5,5,"V",1,0,'C',1);
$pdf->SetFillColor(255,255,170);
$pdf->Cell(5,5,"J",1,0,'C',1);
$pdf->SetFillColor(255,170,170);
$pdf->Cell(5,5,"R",1,0,'C',1);
//$pdf->Cell(5,5,"N�",'1','0','C');
$pdf->Cell(36,5,"N� - Nom",1,1,'C');

for($i=0;$i<24;$i++)
	{
	//$pdf->Cell(5,4,"",'1','0','C');
	$pdf->Cell(36,4,$detail[$i]['d1'],1,0,'L');
	$pdf->SetFillColor(170,255,170);
	$pdf->Cell(5,4,$detail[$i]['d2'],1,0,'C',1);
	$pdf->SetFillColor(255,255,170);
	$pdf->Cell(5,4,$detail[$i]['d3'],1,0,'C',1);
	$pdf->SetFillColor(255,170,170);
	$pdf->Cell(5,4,$detail[$i]['d4'],1,0,'C',1);
	$pdf->Cell(6,4,$detail[$i]['d5'],1,0,'C');
	$pdf->Cell(1,4,"",0,0,'C');
	$pdf->Cell(19,4,$detail[$i]['d6'],1,0,'C');
	$pdf->Cell(1,4,"",0,0,'C');
	$pdf->Cell(6,4,$detail[$i]['d7'],1,0,'C');
	$pdf->SetFillColor(170,255,170);
	$pdf->Cell(5,4,$detail[$i]['d8'],1,0,'C',1);
	$pdf->SetFillColor(255,255,170);
	$pdf->Cell(5,4,$detail[$i]['d9'],1,0,'C',1);
	$pdf->SetFillColor(255,170,170);
	$pdf->Cell(5,4,$detail[$i]['d10'],1,0,'C',1);
	//$pdf->Cell(5,4,"",'1','0','C');
	$pdf->Cell(36,4,$detail[$i]['d11'],1,1,'L');
	}
$pdf->Ln(1);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(42,8,"Equipe A",0,0,'C');
$pdf->Cell(15,8,"",1,0);
$pdf->Cell(21,8,"mi-temps",0,0,'C');
$pdf->Cell(15,8,"",1,0);
$pdf->Cell(42,8,"Equipe B",0,1,'C');

$pdf->Cell(57,3,"",0,0);
$pdf->Cell(21,3,"Score",0,0,'C');
$pdf->Cell(57,3,"",0,1);

$pdf->SetLineWidth(1);
$pdf->SetFont('Arial','B',9);
$pdf->Cell(42,8,$equipeA,0,0,'C');
$pdf->SetFont('Arial','B',12);
$pdf->Cell(15,8,utyGetPDF('ScoreA'),1,0,'C');
$pdf->Cell(21,8,"final",0,0,'C');
$pdf->Cell(15,8,utyGetPDF('ScoreB'),1,0,'C');
$pdf->SetFont('Arial','B',9);
$pdf->Cell(42,8,$equipeB,0,1,'C');
$pdf->Ln(2);

$pdf->SetLineWidth(0.2);
$pdf->SetFont('Arial','',10);
$pdf->Cell(135,4,"Remarques / incidents / retards (rapport au dos si besoin)*",'LRT',1,'L');
$pdf->SetFont('Arial','',7);
//$pdf->Cell(135,3,"Si aucune observation n'est formul�e,",'LR','1','L');
$pdf->Cell(135,3,"A d�faut, barrer la case et porter la mention R.A.S.",'LR',1,'L');
$pdf->SetFont('Arial','I',9);
$pdf->MultiCell(135,22,utyGetPDF('Commentaires'),'LRB','L');

//signatures
$pdf->Ln(1);
$pdf->SetFont('Arial','',10);
$pdf->Cell(21,12,"Signatures",'LRT',0,'C');
$pdf->Cell(38,12,"",1,0,'C');
$pdf->Cell(38,12,"",1,0,'C');
$pdf->Cell(38,12,"",1,1,'C');
$pdf->Cell(21,4,"apr�s match",'LRB',0,'C');
$pdf->Cell(38,4,"Capitaine A",1,0,'C');
$pdf->Cell(38,4,"Capitaine B",1,0,'C');
$pdf->Cell(38,4,"Arbitre Principal",1,1,'C');

$pdf->SetX(10);
$pdf->SetFont('Arial','',7);
$pdf->Cell(177,3,"* Tout rapport ou observation doit �tre manuscrit et sign� par le demandeur et contre-sign� par l'arbitre.",0,1,'L');
$pdf->SetX(10);
$pdf->Cell(177,3,"La feuille doit �tre renvoy�e au plus tard dans les 5 jours � l'autorit� comp�tente.",0,0,'L');
$pdf->Cell(98,3,"Edit� le ".date("d/m/Y")." � ".date("H:i"),0,1,'R');

$pdf->Output();

?>
