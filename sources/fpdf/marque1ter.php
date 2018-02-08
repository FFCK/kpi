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

$pdf->SetFillColor(200,200,200);
$pdf->SetFont('Arial','B',14);
$pdf->Cell(135,6,"FEUILLE DE MARQUE",'1','1','C');

$pdf->SetFont('Arial','I',8);
$pdf->Cell(135,4,"A remplir par le secr�tariat avant le d�but de match",'LR','1','C');
$pdf->Cell(135,2,"",'LR','1','C');

$pdf->SetFont('Arial','',10);
$pdf->Cell(100,4,"Comp�tition : ".utyGetPDF('competition'),'L','0','L');
$pdf->Cell(35,4,utyGetPDF('categorie'),'R','1','R');

$pdf->Cell(110,4,"Organisateur : ".ucwords(strtolower(utyGetPDF('organisateur'))),'L','0','L');
$pdf->Cell(25,4,"Saison : ".utyGetPDF('saison'),'R','1','L');

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
$pdf->Cell(67,4,"Chronom�tre : ".utyGetPDF('chrono'),'R','1','L');
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

$pdf->SetFont('Arial','',8);
$pdf->Cell(6,4,utyGetPDF('na1'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('noma1'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenoma1'))),'LRB','0','C');
if (utyGetPDF('saisona1') != '' && utyGetPDF('saisona1') != utyGetPDF('saison')) $indiqsaisona1 = ' ('.utyGetPDF('saisona1').')';
$pdf->Cell(24,4,utyGetPDF('licencea1').$indiqsaisona1,'LRB','0','C');
$pdf->Cell(15,4,utyGetPDF('diva1'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('na2'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('noma2'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenoma2'))),'LRB','0','C');
if (utyGetPDF('saisona2') != '' && utyGetPDF('saisona2') != utyGetPDF('saison')) $indiqsaisona2 = ' ('.utyGetPDF('saisona2').')';
$pdf->Cell(24,4,utyGetPDF('licencea2').$indiqsaisona2,'LRB','0','C');
$pdf->Cell(15,4,utyGetPDF('diva2'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('na3'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('noma3'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenoma3'))),'LRB','0','C');
if (utyGetPDF('saisona3') != '' && utyGetPDF('saisona3') != utyGetPDF('saison')) $indiqsaisona3 = ' ('.utyGetPDF('saisona3').')';
$pdf->Cell(24,4,utyGetPDF('licencea3').$indiqsaisona3,'LRB','0','C');
$pdf->Cell(15,4,utyGetPDF('diva3'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('na4'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('noma4'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenoma4'))),'LRB','0','C');
if (utyGetPDF('saisona4') != '' && utyGetPDF('saisona4') != utyGetPDF('saison')) $indiqsaisona4 = ' ('.utyGetPDF('saisona4').')';
$pdf->Cell(24,4,utyGetPDF('licencea4').$indiqsaisona4,'LRB','0','C');
$pdf->Cell(15,4,utyGetPDF('diva4'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('na5'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('noma5'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenoma5'))),'LRB','0','C');
if (utyGetPDF('saisona5') != '' && utyGetPDF('saisona5') != utyGetPDF('saison')) $indiqsaisona5 = ' ('.utyGetPDF('saisona5').')';
$pdf->Cell(24,4,utyGetPDF('licencea5').$indiqsaisona5,'LRB','0','C');
$pdf->Cell(15,4,utyGetPDF('diva5'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('na6'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('noma6'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenoma6'))),'LRB','0','C');
if (utyGetPDF('saisona6') != '' && utyGetPDF('saisona6') != utyGetPDF('saison')) $indiqsaisona6 = ' ('.utyGetPDF('saisona6').')';
$pdf->Cell(24,4,utyGetPDF('licencea6').$indiqsaisona6,'LRB','0','C');
$pdf->Cell(15,4,utyGetPDF('diva6'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('na7'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('noma7'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenoma7'))),'LRB','0','C');
if (utyGetPDF('saisona7') != '' && utyGetPDF('saisona7') != utyGetPDF('saison')) $indiqsaisona7 = ' ('.utyGetPDF('saisona7').')';
$pdf->Cell(24,4,utyGetPDF('licencea7').$indiqsaisona7,'LRB','0','C');
$pdf->Cell(15,4,utyGetPDF('diva7'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('na8'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('noma8'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenoma8'))),'LRB','0','C');
if (utyGetPDF('saisona8') != '' && utyGetPDF('saisona8') != utyGetPDF('saison')) $indiqsaisona8 = ' ('.utyGetPDF('saisona8').')';
$pdf->Cell(24,4,utyGetPDF('licencea8').$indiqsaisona8,'LRB','0','C');
$pdf->Cell(15,4,utyGetPDF('diva8'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('na9'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('noma9'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenoma9'))),'LRB','0','C');
if (utyGetPDF('saisona9') != '' && utyGetPDF('saisona9') != utyGetPDF('saison')) $indiqsaisona9 = ' ('.utyGetPDF('saisona9').')';
$pdf->Cell(24,4,utyGetPDF('licencea9').$indiqsaisona9,'LRB','0','C');
$pdf->Cell(15,4,utyGetPDF('diva9'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('na10'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('noma10'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenoma10'))),'LRB','0','C');
if (utyGetPDF('saisona10') != '' && utyGetPDF('saisona10') != utyGetPDF('saison')) $indiqsaisona10 = ' ('.utyGetPDF('saisona10').')';
$pdf->Cell(24,4,utyGetPDF('licencea10').$indiqsaisona10,'LRB','0','C');
$pdf->Cell(15,4,utyGetPDF('diva10'),'LRB','1','C');

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

$pdf->SetFont('Arial','',8);
$pdf->Cell(6,4,utyGetPDF('nb1'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('nomb1'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenomb1'))),'LRB','0','C');
if (utyGetPDF('saisonb1') != '' && utyGetPDF('saisonb1') != utyGetPDF('saison')) $indiqsaisonb1 = ' ('.utyGetPDF('saisonb1').')';
$pdf->Cell(24,4,utyGetPDF('licenceb1').$indiqsaisonb1,'LRB','0','C');
$pdf->Cell(15,4,utyGetPDF('divb1'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('nb2'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('nomb2'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenomb2'))),'LRB','0','C');
if (utyGetPDF('saisonb2') != '' && utyGetPDF('saisonb2') != utyGetPDF('saison')) $indiqsaisonb2 = ' ('.utyGetPDF('saisonb2').')';
$pdf->Cell(24,4,utyGetPDF('licenceb2').$indiqsaisonb2,'LRB','0','C');
$pdf->Cell(15,4,utyGetPDF('divb2'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('nb3'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('nomb3'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenomb3'))),'LRB','0','C');
if (utyGetPDF('saisonb3') != '' && utyGetPDF('saisonb3') != utyGetPDF('saison')) $indiqsaisonb3 = ' ('.utyGetPDF('saisonb3').')';
$pdf->Cell(24,4,utyGetPDF('licenceb3').$indiqsaisonb3,'LRB','0','C');
$pdf->Cell(15,4,utyGetPDF('divb3'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('nb4'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('nomb4'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenomb4'))),'LRB','0','C');
if (utyGetPDF('saisonb4') != '' && utyGetPDF('saisonb4') != utyGetPDF('saison')) $indiqsaisonb4 = ' ('.utyGetPDF('saisonb4').')';
$pdf->Cell(24,4,utyGetPDF('licenceb4').$indiqsaisonb4,'LRB','0','C');
$pdf->Cell(15,4,utyGetPDF('divb4'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('nb5'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('nomb5'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenomb5'))),'LRB','0','C');
if (utyGetPDF('saisonb5') != '' && utyGetPDF('saisonb5') != utyGetPDF('saison')) $indiqsaisonb5 = ' ('.utyGetPDF('saisonb5').')';
$pdf->Cell(24,4,utyGetPDF('licenceb5').$indiqsaisonb5,'LRB','0','C');
$pdf->Cell(15,4,utyGetPDF('divb5'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('nb6'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('nomb6'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenomb6'))),'LRB','0','C');
if (utyGetPDF('saisonb6') != '' && utyGetPDF('saisonb6') != utyGetPDF('saison')) $indiqsaisonb6 = ' ('.utyGetPDF('saisonb6').')';
$pdf->Cell(24,4,utyGetPDF('licenceb6').$indiqsaisonb6,'LRB','0','C');
$pdf->Cell(15,4,utyGetPDF('divb6'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('nb7'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('nomb7'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenomb7'))),'LRB','0','C');
if (utyGetPDF('saisonb7') != '' && utyGetPDF('saisonb7') != utyGetPDF('saison')) $indiqsaisonb7 = ' ('.utyGetPDF('saisonb7').')';
$pdf->Cell(24,4,utyGetPDF('licenceb7').$indiqsaisonb7,'LRB','0','C');
$pdf->Cell(15,4,utyGetPDF('divb7'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('nb8'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('nomb8'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenomb8'))),'LRB','0','C');
if (utyGetPDF('saisonb8') != '' && utyGetPDF('saisonb8') != utyGetPDF('saison')) $indiqsaisonb8 = ' ('.utyGetPDF('saisonb8').')';
$pdf->Cell(24,4,utyGetPDF('licenceb8').$indiqsaisonb8,'LRB','0','C');
$pdf->Cell(15,4,utyGetPDF('divb8'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('nb9'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('nomb9'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenomb9'))),'LRB','0','C');
if (utyGetPDF('saisonb9') != '' && utyGetPDF('saisonb9') != utyGetPDF('saison')) $indiqsaisonb9 = ' ('.utyGetPDF('saisonb9').')';
$pdf->Cell(24,4,utyGetPDF('licenceb9').$indiqsaisonb9,'LRB','0','C');
$pdf->Cell(15,4,utyGetPDF('divb9'),'LRB','1','C');

$pdf->Cell(6,4,utyGetPDF('nb10'),'LRB','0','C');
$pdf->Cell(45,4,utyGetPDF('nomb10'),'LRB','0','C');
$pdf->Cell(45,4,ucwords(strtolower(utyGetPDF('prenomb10'))),'LRB','0','C');
if (utyGetPDF('saisonb10') != '' && utyGetPDF('saisonb10') != utyGetPDF('saison')) $indiqsaisonb10 = ' ('.utyGetPDF('saisonb10').')';
$pdf->Cell(24,4,utyGetPDF('licenceb10').$indiqsaisonb10,'LRB','0','C');
$pdf->Cell(15,4,utyGetPDF('divb10'),'LRB','1','C');

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
	$pdf->Cell(36,4,"",1,0,'L');
	$pdf->SetFillColor(170,255,170);
	$pdf->Cell(5,4,"",1,0,'C',1);
	$pdf->SetFillColor(255,255,170);
	$pdf->Cell(5,4,"",1,0,'C',1);
	$pdf->SetFillColor(255,170,170);
	$pdf->Cell(5,4,"",1,0,'C',1);
	$pdf->Cell(6,4,"",1,0,'C');
	$pdf->Cell(1,4,"",0,0,'C');
	$pdf->Cell(19,4,"",1,0,'C');
	$pdf->Cell(1,4,"",0,0,'C');
	$pdf->Cell(6,4,"",1,0,'C');
	$pdf->SetFillColor(170,255,170);
	$pdf->Cell(5,4,"",1,0,'C',1);
	$pdf->SetFillColor(255,255,170);
	$pdf->Cell(5,4,"",1,0,'C',1);
	$pdf->SetFillColor(255,170,170);
	$pdf->Cell(5,4,"",1,0,'C',1);
	//$pdf->Cell(5,4,"",'1','0','C');
	$pdf->Cell(36,4,"",1,1,'L');
	}
$pdf->Ln(2);

$pdf->Cell(36,8,"",0,0,'C');
$pdf->Cell(15,8,"",1,0);
$pdf->Cell(33,8,"Score mi-temps",0,0,'C');
$pdf->Cell(15,8,"",1,0);
$pdf->Cell(36,8,"",0,1,'C');
$pdf->Ln(2);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(36,8,"Equipe A",0,0,'C');
$pdf->SetLineWidth(1);
$pdf->Cell(15,8,"",1,0);
$pdf->Cell(33,8,"Score final",0,0,'C');
$pdf->Cell(15,8,"",1,0);
$pdf->Cell(36,8,"Equipe B",0,1,'C');
$pdf->Ln(2);

$pdf->SetLineWidth(0.2);
$pdf->SetFont('Arial','',10);
$pdf->Cell(135,4,"Remarques / incidents / retards (rapport au dos si besoin)*",'LRT',1,'L');
$pdf->SetFont('Arial','',7);
//$pdf->Cell(135,3,"Si aucune observation n'est formul�e,",'LR','1','L');
$pdf->Cell(135,3,"A d�faut, barrer la case et porter la mention R.A.S.",'LR','1','L');
$pdf->SetFont('Arial','',10);
$pdf->Cell(135,22,"",'LRB',1,'L');

//signatures
$pdf->Ln(1);
$pdf->SetFont('Arial','',10);
$pdf->Cell(21,12,"Signatures",'LRT','0','C');
$pdf->Cell(38,12,"",'1','0','C');
$pdf->Cell(38,12,"",'1','0','C');
$pdf->Cell(38,12,"",'1','1','C');
$pdf->Cell(21,4,"apr�s match",'LRB','0','C');
$pdf->Cell(38,4,"Capitaine A",'1','0','C');
$pdf->Cell(38,4,"Capitaine B",'1','0','C');
$pdf->Cell(38,4,"Arbitre Principal",'1','1','C');

$pdf->SetX(10);
$pdf->SetFont('Arial','',7);
$pdf->Cell(177,3,"* Tout rapport ou observation doit �tre manuscrit et sign� par le demandeur et contre-sign� par l'arbitre.",'0','1','L');
$pdf->SetX(10);
$pdf->Cell(177,3,"La feuille doit �tre renvoy�e au plus tard dans les 5 jours � l'autorit� comp�tente.",'0','0','L');
$pdf->Cell(98,3,"Edit� le ".date("d/m/Y")." � ".date("H:i"),'0','1','R');

$pdf->Output();

?>
