<?php

include_once($_SERVER['DOCUMENT_ROOT'].'/commun/MyPage.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/commun/MyBdd.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/commun/MyTools.php');

//define('FPDF_FONTPATH','font/');
require($_SERVER['DOCUMENT_ROOT'].'/fpdf/fpdf.php');

// Pieds de page
class PDF extends FPDF
{
	function Footer()
	{
	    //Positionnement � 1,5 cm du bas
	    $this->SetY(-15);
	    //Police Arial italique 8
	    $this->SetFont('Arial','I',8);
	    //Num�ro de page centr�
	    $this->Cell(0,10,'Page '.$this->PageNo(),0,0,'C');
	}
}
 
// Liste des pr�sents par cat�gorie 
class FeuillePresenceParCat extends MyPage	 
{	
	function FeuillePresenceParCat()
	{
		MyPage::MyPage();

		$myBdd = new MyBdd();
		

		$codeCompet = utyGetSession('codeCompet');
		$codeSaison = utyGetSaison();

		// Chargement des �quipes ...
		$arrayEquipe = array();
		$arrayJoueur = array();
		$arrayCompetition = array();
		
		if (strlen($codeCompet) > 0)
		{
			$sql  = "Select Id, Libelle, Code_club, Numero ";
			$sql .= "From gickp_Competitions_Equipes ";
			$sql .= "Where Code_compet = '";

			$sql .= $codeCompet;
			$sql .= "' And Code_saison = '";
			$sql .= $codeSaison;
			$sql .= "' Order By Libelle, Id ";	 
	
			$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load Equipes");
			$num_results = mysql_num_rows($result);
			if ($num_results == 0)
				die ('Aucune �quipe dans cette comp�tition');
			
			$listEquipes = '';
			for ($i=0;$i<$num_results;$i++)
			{
				$row = mysql_fetch_array($result);
				$idEquipe = $row['Id'];
				if($listEquipes != '')
					$listEquipes .= ',';
				$listEquipes .= $idEquipe;
			}

			// Chargement des Coureurs ...
			if ($idEquipe != '')
			{
				$sql2  = "Select a.Matric, a.Nom, a.Prenom, a.Sexe, a.Categ, a.Numero, a.Capitaine, ce.Libelle NomEquipe, ";
				$sql2 .= "b.Origine, b.Numero_club, b.Pagaie_ECA, b.Etat_certificat_CK CertifCK, b.Etat_certificat_APS CertifAPS, c.Arb, c.niveau ";
				$sql2 .= "From gickp_Competitions_Equipes_Joueurs a ";
				$sql2 .= "Left Outer Join gickp_Liste_Coureur b On (a.Matric = b.Matric) ";
				$sql2 .= "Left Outer Join gickp_Arbitre c On (a.Matric = c.Matric), ";
				$sql2 .= "gickp_Competitions_Equipes ce ";
				$sql2 .= "Where a.Id_Equipe in (";
				$sql2 .= $listEquipes;
				$sql2 .= ") ";
				$sql2 .= "And ce.Id = a.Id_Equipe ";
				$sql2 .= "Order By a.Categ, Id_Equipe, Field(if(a.Capitaine='C','-',if(a.Capitaine='','-',a.Capitaine)), '-', 'E', 'A', 'X'), Numero, Nom, Prenom ";	 

				$result2 = mysql_query($sql2, $myBdd->m_link) or die ("Erreur Load Titulaires : ".$sql2.' - '.$codeCompet.' - '.$row['Id'].' ! ');
				$num_results2 = mysql_num_rows($result2);
			
				$arrayJoueur = array();

				for ($j=0;$j<$num_results2;$j++)
				{
					$row2 = mysql_fetch_array($result2);	  
					
					$numero = $row2['Numero'];
					if (strlen($numero) == 0)
						$numero = 0;
					if($row2['niveau'] != '')
						$row2['Arb'] .= '-'.$row2['niveau'];
					
					Switch ($row2['Pagaie_ECA'])
					{
						case 'PAGR' :
							$pagaie = 'Rouge';
							break;
						case 'PAGN' :
							$pagaie = 'Noire';
							break;
						case 'PAGBL' :
							$pagaie = 'Bleue';
							break;
						case 'PAGB' :
							$pagaie = 'Blanche';
							break;
						case 'PAGJ' :
							$pagaie = 'Jaune';
							break;
						case 'PAGV' :
							$pagaie = 'Verte';
							break;
						default :
							$pagaie = '';
					}
						
					$capitaine = $row2['Capitaine'];
					if (strlen($capitaine) == 0)
						$capitaine = '-';
						
					if (is_null($row2['Arb']))
						$row2['Arb'] = '';
						
					if ($row2['Origine'] != $codeSaison)
						$row2['Origine'] = ' ('.$row2['Origine'].')';
					else
						$row2['Origine'] = '';

					array_push($arrayJoueur, array( 'Matric' => $row2['Matric'], 'Nom' => ucwords(strtolower($row2['Nom'])), 'Prenom' => ucwords(strtolower($row2['Prenom'])), 
																					'Sexe' => $row2['Sexe'], 'Categ' => $row2['Categ'], 'Pagaie' => $pagaie, 'CertifCK' => $row2['CertifCK'],  
																					'CertifAPS' => $row2['CertifAPS'], 'Numero' => $numero, 'Capitaine' => $capitaine , 'Arbitre' => $row2['Arb'] , 
																					'Saison' => $row2['Origine'], 'Numero_club' => $row2['Numero_club'],
																					'nbJoueurs' => $num_results2, 'NomEquipe' => $row2['NomEquipe']));
				}

			}
			else
				die ('Aucune �quipe');
		}	
		else
			die ('Aucune comp�tition s�lectionn�e');

		// Chargement des infos de la comp�tition
		$arrayCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);
		if($arrayCompetition['Titre_actif'] == 'O')
			$titreCompet = $arrayCompetition['Libelle'];
		else
			$titreCompet = $arrayCompetition['Soustitre'];
		if($arrayCompetition['Soustitre2'] != '')
			$titreCompet .= ' - '.$arrayCompetition['Soustitre2'];
//		$titreCompet = 'Comp�tition : '.$arrayCompetition['Libelle'].' ('.$codeCompet.')';

		$logo = str_replace('http://www.kayak-polo.info/','../',$arrayCompetition['LogoLink']);
		$sponsor = str_replace('http://www.kayak-polo.info/','../',$arrayCompetition['SponsorLink']);
//		print_r ($arrayJoueur);
		// Ent�te PDF ...	  
 		$pdf = new PDF('L');
		$pdf->Open();
		$pdf->SetTitle("Feuilles de pr�sence");
		
		$pdf->SetAuthor("Kayak-polo.info");
		$pdf->SetCreator("Kayak-polo.info avec FPDF");
		if($arrayCompetition['Sponsor_actif'] == 'O' && $sponsor != '')
			$pdf->SetAutoPageBreak(true, 30);
		else
			$pdf->SetAutoPageBreak(true, 15);

		$lastCat = '';
		for ($i=0;$i<$num_results2;$i++)
		{
			if($lastCat != $arrayJoueur[$i]['Categ'])
			{
				
				$pdf->AddPage();
				// Affichage
				// logo
				if($arrayCompetition['Kpi_ffck_actif'] == 'O')
				{
					$pdf->Image('../css/banniere1.jpg',10,8,72,15,'jpg',"http://www.kayak-polo.info");
					$pdf->Image('../img/ffck2.jpg',242,8,0,15,'jpg',"http://www.ffck.org");
				}

				if($arrayCompetition['Logo_actif'] == 'O' && $logo != '')  //&& file_exists($logo)
				{
					$size = getimagesize($logo);
					$largeur=$size[0];
					$hauteur=$size[1];
					$ratio=20/$hauteur;	//hauteur impos�e de 20mm
					$newlargeur=$largeur*$ratio;
					$posi=149-($newlargeur/2);	//297mm = largeur de page
					$pdf->image($logo, $posi, 8, 0,20);
				}

				if($arrayCompetition['Sponsor_actif'] == 'O' && $sponsor != '')  //&& file_exists($sponsor)
				{
					$size = getimagesize($sponsor);
					$largeur=$size[0];
					$hauteur=$size[1];
					$ratio=16/$hauteur;	//hauteur impos�e de 16mm
					$newlargeur=$largeur*$ratio;
					$posi=149-($newlargeur/2);	//297mm = largeur de page
					$pdf->image($sponsor, $posi, 180, 0,16);
				}
				// titre
				$pdf->Ln(20);
				$pdf->SetFont('Arial','BI',12);
				$pdf->Cell(137,8,$titreCompet,'LT',0,'L');
				$pdf->Cell(136,8,'Saison '.$codeSaison,'TR',1,'R');
				$pdf->SetFont('Arial','B',14);
				$pdf->Cell(273,8,"Feuille de pr�sence - ".$arrayJoueur[$i]['Categ'],'LRB','1','C');
				$pdf->Ln(10);
				
				$lastCat = $arrayJoueur[$i]['Categ'];
				$idEquipe = $arrayJoueur[$i]['Id'];
				
				$pdf->SetFont('Arial','B',10);
				$pdf->Cell(12,7,'Num','LTR',0,'C');
				$pdf->Cell(8,7,'Cap','LTR',0,'C');
				$pdf->Cell(25,7,'Licence','LTR',0,'C');
				$pdf->Cell(45,7,'Nom','LTR',0,'C');
				$pdf->Cell(45,7,'Prenom','LTR',0,'C');
				$pdf->Cell(62,7,'Equipe','LTR',0,'C');
				$pdf->Cell(15,7,'Pag. EC','LTR',0,'C');
				$pdf->Cell(18,7,'CertifCK','LTR',0,'C');
				$pdf->Cell(18,7,'CertifAPS','LTR',0,'C');
				$pdf->Cell(12,7,'Club','LTR',0,'C');
				$pdf->Cell(12,7,'Arb','LTR',1,'C');
				$pdf->SetFont('Arial','',10);
			}
			
			$pdf->Cell(12,7,$arrayJoueur[$i]['Numero'],1,0,'C');
			$pdf->Cell(8,7,$arrayJoueur[$i]['Capitaine'],'LTRB',0,'C');
			$pdf->Cell(25,7,$arrayJoueur[$i]['Matric'].$arrayJoueur[$i]['Saison'],1,0,'C');
			$pdf->Cell(45,7,$arrayJoueur[$i]['Nom'],1,0,'C');
			$pdf->Cell(45,7,$arrayJoueur[$i]['Prenom'],1,0,'C');
			$pdf->Cell(62,7,$arrayJoueur[$i]['NomEquipe'],1,0,'C');
			$pdf->Cell(15,7,$arrayJoueur[$i]['Pagaie'],'LTRB',0,'C');
			$pdf->Cell(18,7,$arrayJoueur[$i]['CertifCK'],'LTRB',0,'C');
			$pdf->Cell(18,7,$arrayJoueur[$i]['CertifAPS'],'LTRB',0,'C');
			$pdf->Cell(12,7,$arrayJoueur[$i]['Numero_club'],'LTRB',0,'C');
			$pdf->Cell(12,7,$arrayJoueur[$i]['Arbitre'],'LTRB',1,'C');
		}
		$pdf->Output('Pr�sences par cat�gorie','I');
	}
}

$page = new FeuillePresenceParCat();

?>
