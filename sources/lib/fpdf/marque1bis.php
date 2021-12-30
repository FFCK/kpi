<?php
require('fpdf.php');
require('../MyTools.php'); 

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

$pdf->SetFont('Arial','B',14);
$pdf->Cell(135,6,"FEUILLE DE MARQUE",'1','1','C');

$pdf->SetFont('Arial','I',8);
$pdf->Cell(135,4,"A remplir par le secr�tariat avant le d�but de match",'LR','1','C');
$pdf->Cell(135,2,"",'LR','1','C');

$pdf->SetFont('Arial','',10);
$pdf->Cell(100,4,"Comp�tition : ".utyGetPDF('competition'),'L','0','L');
$pdf->Cell(35,4,"Code : ".utyGetPDF('categorie'),'R','1','C');

$pdf->Cell(110,4,"Organisateur : ".ucwords(strtolower(utyGetPDF('organisateur'))),'L','0','L');
$pdf->Cell(25,4,"Saison : ".utyGetPDF('saison'),'R','1','L');

$pdf->Cell(68,4,"R1 : ".ucwords(strtolower(utyGetPDF('responsable'))),'L','0','L');
$pdf->Cell(67,4,"D�l�gu� : ".ucwords(strtolower(utyGetPDF('delegue'))),'R','1','L');

$pdf->Cell(135,1,"",'LR','1','C');
$pdf->Cell(135,1,"",'LTR','1','C');
$pdf->Cell(60,4,"Lieu : ".utyGetPDF('lieu'),'L','0','L');
$pdf->Cell(25,4,"Le  ".utyGetPDF('date'),'0','0','L');
$pdf->Cell(20,4," �  ".utyGetPDF('heure'),'0','0','L');
$pdf->Cell(30,4,"Terrain : ".utyGetPDF('terrain'),'R','1','L');

$pdf->Cell(60,4,"Phase : ".utyGetPDF('phase'),'L','0','L');
$pdf->SetFont('Arial','B',10);
$pdf->Cell(22,4,"Match n� ".utyGetPDF('no'),'0','0','L');
$pdf->SetFont('Arial','',10);
$pdf->Cell(53,4,utyGetPDF('intitule'),'R','1','C');

$pdf->Cell(135,1,"",'LR','1','C');
$pdf->Cell(135,1,"",'LTR','1','L');
$pdf->Cell(135,4,"Arbitre Principal : ".utyGetPDF('principal'),'LR','1','L');
$pdf->Cell(135,4,"Arbitre secondaire : ".utyGetPDF('secondaire'),'LR','1','L');
$pdf->Cell(70,4,"Secr�taire : ".utyGetPDF('secretaire'),'L','0','L');
$pdf->Cell(65,4,"Chronom�tre : ".utyGetPDF('chrono'),'R','1','L');
$pdf->Cell(135,1,"",'LBR','1','C');

//Equipe A

$pdf->Ln(2);

$pdf->Cell(45,5,"EQUIPE A :",'LTB','0','C');
$pdf->SetFont('Arial','B',14);
$pdf->Cell(90,5,$equipeA,'TRB','1','C');
$pdf->SetFont('Arial','',10);

$pdf->Cell(6,6,"N�",'1','0','C');
$pdf->Cell(45,6,"Nom",'1','0','C');
$pdf->Cell(45,6,"Pr�nom",'1','0','C');
$pdf->Cell(27,6,"Licence",'1','0','C');
$pdf->Cell(12,6,"Cat.",'1','1','C');

$pdf->SetFont('Arial','',8);
$pdf->Cell(6,4,utyGetPDF('na1'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('noma1'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenoma1'))),'LRB','0','C');
$pdf->Cell(27,4,utyGetPDF('licencea1'),'LRB','0','C');
$pdf->Cell(12,4,utyGetPDF('diva1'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('na2'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('noma2'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenoma2'))),'LRB','0','C');
$pdf->Cell(27,4,utyGetPDF('licencea2'),'LRB','0','C');
$pdf->Cell(12,4,utyGetPDF('diva2'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('na3'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('noma3'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenoma3'))),'LRB','0','C');
$pdf->Cell(27,4,utyGetPDF('licencea3'),'LRB','0','C');
$pdf->Cell(12,4,utyGetPDF('diva3'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('na4'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('noma4'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenoma4'))),'LRB','0','C');
$pdf->Cell(27,4,utyGetPDF('licencea4'),'LRB','0','C');
$pdf->Cell(12,4,utyGetPDF('diva4'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('na5'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('noma5'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenoma5'))),'LRB','0','C');
$pdf->Cell(27,4,utyGetPDF('licencea5'),'LRB','0','C');
$pdf->Cell(12,4,utyGetPDF('diva5'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('na6'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('noma6'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenoma6'))),'LRB','0','C');
$pdf->Cell(27,4,utyGetPDF('licencea6'),'LRB','0','C');
$pdf->Cell(12,4,utyGetPDF('diva6'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('na7'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('noma7'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenoma7'))),'LRB','0','C');
$pdf->Cell(27,4,utyGetPDF('licencea7'),'LRB','0','C');
$pdf->Cell(12,4,utyGetPDF('diva7'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('na8'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('noma8'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenoma8'))),'LRB','0','C');
$pdf->Cell(27,4,utyGetPDF('licencea8'),'LRB','0','C');
$pdf->Cell(12,4,utyGetPDF('diva8'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('na9'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('noma9'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenoma9'))),'LRB','0','C');
$pdf->Cell(27,4,utyGetPDF('licencea9'),'LRB','0','C');
$pdf->Cell(12,4,utyGetPDF('diva9'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('na10'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('noma10'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenoma10'))),'LRB','0','C');
$pdf->Cell(27,4,utyGetPDF('licencea10'),'LRB','0','C');
$pdf->Cell(12,4,utyGetPDF('diva10'),'LRB','1','C');

//Equipe B

$pdf->Ln(2);

$pdf->SetFont('Arial','',10);
$pdf->Cell(45,5,"EQUIPE B :",'LTB','0','C');
$pdf->SetFont('Arial','B',14);
$pdf->Cell(90,5,$equipeB,'TRB','1','C');
$pdf->SetFont('Arial','',10);

$pdf->Cell(6,6,"N�",'1','0','C');
$pdf->Cell(45,6,"Nom",'1','0','C');
$pdf->Cell(45,6,"Pr�nom",'1','0','C');
$pdf->Cell(27,6,"Licence",'1','0','C');
$pdf->Cell(12,6,"Cat",'1','1','C');

$pdf->SetFont('Arial','',8);
$pdf->Cell(6,4,utyGetPDF('nb1'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('nomb1'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenomb1'))),'LRB','0','C');
$pdf->Cell(27,4,utyGetPDF('licenceb1'),'LRB','0','C');
$pdf->Cell(12,4,utyGetPDF('divb1'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('nb2'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('nomb2'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenomb2'))),'LRB','0','C');
$pdf->Cell(27,4,utyGetPDF('licenceb2'),'LRB','0','C');
$pdf->Cell(12,4,utyGetPDF('divb2'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('nb3'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('nomb3'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenomb3'))),'LRB','0','C');
$pdf->Cell(27,4,utyGetPDF('licenceb3'),'LRB','0','C');
$pdf->Cell(12,4,utyGetPDF('divb3'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('nb4'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('nomb4'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenomb4'))),'LRB','0','C');
$pdf->Cell(27,4,utyGetPDF('licenceb4'),'LRB','0','C');
$pdf->Cell(12,4,utyGetPDF('divb4'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('nb5'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('nomb5'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenomb5'))),'LRB','0','C');
$pdf->Cell(27,4,utyGetPDF('licenceb5'),'LRB','0','C');
$pdf->Cell(12,4,utyGetPDF('divb5'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('nb6'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('nomb6'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenomb6'))),'LRB','0','C');
$pdf->Cell(27,4,utyGetPDF('licenceb6'),'LRB','0','C');
$pdf->Cell(12,4,utyGetPDF('divb6'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('nb7'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('nomb7'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenomb7'))),'LRB','0','C');
$pdf->Cell(27,4,utyGetPDF('licenceb7'),'LRB','0','C');
$pdf->Cell(12,4,utyGetPDF('divb7'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('nb8'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('nomb8'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenomb8'))),'LRB','0','C');
$pdf->Cell(27,4,utyGetPDF('licenceb8'),'LRB','0','C');
$pdf->Cell(12,4,utyGetPDF('divb8'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('nb9'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('nomb9'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenomb9'))),'LRB','0','C');
$pdf->Cell(27,4,utyGetPDF('licenceb9'),'LRB','0','C');
$pdf->Cell(12,4,utyGetPDF('divb9'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('nb10'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('nomb10'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenomb10'))),'LRB','0','C');
$pdf->Cell(27,4,utyGetPDF('licenceb10'),'LRB','0','C');
$pdf->Cell(12,4,utyGetPDF('divb10'),'LRB','1','C');

//signatures
$pdf->Ln(1);
$pdf->SetFont('Arial','',10);
$pdf->Cell(21,10,"Signatures",'LRT','0','C');
$pdf->Cell(38,10,"",'1','0','C');
$pdf->Cell(38,10,"",'1','0','C');
$pdf->Cell(38,10,"",'1','1','C');
$pdf->Cell(21,5,"avant match",'LRB','0','C');
$pdf->Cell(38,5,"Capitaine A",'1','0','C');
$pdf->Cell(38,5,"Capitaine B",'1','0','C');
$pdf->Cell(38,5,"Arbitre Principal",'1','1','C');

//Colonne 2

$x0=150;
$pdf->SetLeftMargin($x0);
$pdf->SetX($x0);
$pdf->SetY(10);

$pdf->SetFont('Arial','B',14);
$pdf->Cell(135,6,"DEROULEMENT DU MATCH",'1','1','C');

$pdf->Cell(19,5,"",'LTR','0','C');
$pdf->SetFont('Arial','',10);
$pdf->Cell(58,5,"Equ.A: ".$equipeA,'1','0','L');
$pdf->Cell(58,5,"Equ.B: ".$equipeB,'1','1','L');

$pdf->Cell(19,5,"Temps",'LRB','0','C');
$pdf->Cell(5,5,"N�",'1','0','C');
$pdf->Cell(31,5,"Nom/pr�nom",'1','0','C');
$pdf->Cell(7,5,"But",'1','0','C');
$pdf->SetFillColor(140,255,140);
$pdf->Cell(5,5,"V",1,0,'C',1);
$pdf->SetFillColor(255,255,140);
$pdf->Cell(5,5,"J",1,0,'C',1);
$pdf->SetFillColor(255,140,140);
$pdf->Cell(5,5,"R",1,0,'C',1);
$pdf->Cell(5,5,"N�",'1','0','C');
$pdf->Cell(31,5,"Nom/pr�nom",'1','0','C');
$pdf->Cell(7,5,"But",'1','0','C');
$pdf->SetFillColor(140,255,140);
$pdf->Cell(5,5,"V",1,0,'C',1);
$pdf->SetFillColor(255,255,140);
$pdf->Cell(5,5,"J",1,0,'C',1);
$pdf->SetFillColor(255,140,140);
$pdf->Cell(5,5,"R",1,1,'C',1);

for($i=0;$i<24;$i++)
	{
	$pdf->Cell(19,4,"",1,0,'C');
	$pdf->Cell(5,4,"",'1','0','C');
	$pdf->Cell(31,4,"",'1','0','C');
	$pdf->Cell(7,4,"",'1','0','C');
	$pdf->SetFillColor(140,255,140);
	$pdf->Cell(5,4,"",1,0,'C',1);
	$pdf->SetFillColor(255,255,140);
	$pdf->Cell(5,4,"",1,0,'C',1);
	$pdf->SetFillColor(255,140,140);
	$pdf->Cell(5,4,"",1,0,'C',1);
	$pdf->Cell(5,4,"",'1','0','C');
	$pdf->Cell(31,4,"",'1','0','C');
	$pdf->Cell(7,4,"",'1','0','C');
	$pdf->SetFillColor(140,255,140);
	$pdf->Cell(5,4,"",1,0,'C',1);
	$pdf->SetFillColor(255,255,140);
	$pdf->Cell(5,4,"",1,0,'C',1);
	$pdf->SetFillColor(255,140,140);
	$pdf->Cell(5,4,"",1,1,'C',1);
	}
$pdf->Ln(2);

$pdf->Cell(30,8,"Equipe",0,0,'C');
$pdf->Cell(18,8,"",1,0);
$pdf->Cell(40,8,"Score mi-temps",0,0,'C');
$pdf->Cell(18,8,"",1,0);
$pdf->Cell(30,8,"Equipe",0,1,'C');
$pdf->Ln(2);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(30,8,"A",0,0,'C');
$pdf->SetLineWidth(1);
$pdf->Cell(18,8,"",1,0);
$pdf->Cell(40,8,"Score final",0,0,'C');
$pdf->Cell(18,8,"",1,0);
$pdf->Cell(30,8,"B",0,1,'C');
$pdf->Ln(2);

$pdf->SetLineWidth(0.2);
$pdf->SetFont('Arial','',10);
$pdf->Cell(135,5,"Remarques g�n�rales (Rapport au dos si besoin)*",'LRT',1,'L');
$pdf->SetFont('Arial','',7);
$pdf->Cell(135,3,"Si aucune observation n'est formul�e,",'LR','1','L');
$pdf->Cell(135,3,"barrer la case et porter la mention R.A.S.",'LR','1','L');
$pdf->SetFont('Arial','',10);
$pdf->Cell(135,17,"",'LRB',1,'L');

//signatures
$pdf->Ln(1);
$pdf->SetFont('Arial','',10);
$pdf->Cell(21,10,"Signatures",'LRT','0','C');
$pdf->Cell(38,10,"",'1','0','C');
$pdf->Cell(38,10,"",'1','0','C');
$pdf->Cell(38,10,"",'1','1','C');
$pdf->Cell(21,5,"apr�s match",'LRB','0','C');
$pdf->Cell(38,5,"Capitaine A",'1','0','C');
$pdf->Cell(38,5,"Capitaine B",'1','0','C');
$pdf->Cell(38,5,"Arbitre Principal",'1','1','C');

$pdf->SetX(10);
$pdf->SetFont('Arial','',7);
$pdf->Cell(177,3,"* Tout rapport ou observation doit �tre manuscrit et sign� par le demandeur et contre-sign� par l'arbitre.",'0','1','L');
$pdf->SetX(10);
$pdf->Cell(177,3,"La feuille doit �tre renvoy�e au plus tard dans les 5 jours � l'autorit� comp�tente.",'0','0','L');
$pdf->Cell(98,3,"Edit� le ".date("d/m/Y")." � ".date("H:i"),'0','1','R');

$pdf->Output();

?>
