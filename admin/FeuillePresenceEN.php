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
	    //Numéro de page à gauche
	    $this->Cell(135,10,'Page '.$this->PageNo(),0,0,'L');
		//Date à droite
	    $this->Cell(135,10,date('d/m/Y - H:i'),0,0,'R');
	}
}

// liste des présents par équipe EN
class FeuillePresence extends MyPage	 
{	
	function FeuillePresence()
	{
		MyPage::MyPage();

		$myBdd = new MyBdd();
		

		$codeCompet = utyGetSession('codeCompet');
		$codeSaison = utyGetSaison();

		// Chargement des équipes ...
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
				die ('Aucune équipe dans cette compétition');
		
			for ($i=0;$i<$num_results;$i++)
			{
				$row = mysql_fetch_array($result);
				$idEquipe = $row['Id'];

				// Chargement des Coureurs ...
				if ($idEquipe != '')
				{
					$sql2  = "Select a.Matric, a.Nom, a.Prenom, a.Sexe, a.Categ, a.Numero, a.Capitaine, ";
					$sql2 .= "b.Origine, b.Numero_club, b.Pagaie_ECA, b.Etat_certificat_CK CertifCK, b.Etat_certificat_APS CertifAPS, c.Arb, c.niveau ";
					$sql2 .= "From gickp_Competitions_Equipes_Joueurs a ";
					$sql2 .= "Left Outer Join gickp_Liste_Coureur b On (a.Matric = b.Matric) ";
					$sql2 .= "Left Outer Join gickp_Arbitre c On (a.Matric = c.Matric) ";
					$sql2 .= "Where Id_Equipe = ";
					$sql2 .= $idEquipe;
					$sql2 .= " Order By Field(if(a.Capitaine='C','-',if(a.Capitaine='','-',a.Capitaine)), '-', 'E', 'A', 'X'), Numero, Nom, Prenom ";	 
					//$sql2 .= " Order By Field(if(a.Capitaine='C','-',a.Capitaine), '-', 'E', 'A', 'X'), Numero, Nom, Prenom ";	 

					$result2 = mysql_query($sql2, $myBdd->m_link) or die ("Erreur Load Titulaires : ".$sql2.' - '.$codeCompet.' - '.$row['Id'].' ! ');
					$num_results2 = mysql_num_rows($result2);
				
					$arrayJoueur{$idEquipe} = array();

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
							
/*						// Pour décaler l'entraineur à la fin de la liste
						if ($capitaine == 'E' or $capitaine == 'A')
							$clefEntraineur = $i;
*/							
						if ($row2['Origine'] != $codeSaison)
							$row2['Origine'] = ' ('.$row2['Origine'].')';
						else
							$row2['Origine'] = '';

						array_push($arrayJoueur{$idEquipe}, array( 'Matric' => $row2['Matric'], 'Nom' => ucwords(strtolower($row2['Nom'])), 'Prenom' => ucwords(strtolower($row2['Prenom'])), 
																						'Sexe' => $row2['Sexe'], 'Categ' => $row2['Categ'], 'Pagaie' => $pagaie, 'CertifCK' => $row2['CertifCK'],  
																						'CertifAPS' => $row2['CertifAPS'], 'Numero' => $numero, 'Capitaine' => $capitaine , 'Arbitre' => $row2['Arb'] , 
																						'Saison' => $row2['Origine'], 'Numero_club' => $row2['Numero_club'],
																						'nbJoueurs' => $num_results2));
					}
/*					if($clefEntraineur != '')
					{
						// Prélève l'entraineur de la liste
						$decaleEntraineur = array_splice($arrayJoueur[$idEquipe], $clefEntraineur, 1);
						// Replace l'entraine à la fin
						array_splice($arrayJoueur[$idEquipe], 9, 0, $decaleEntraineur);
					}
*/
				array_push($arrayEquipe, array('Id' => $row['Id'], 'Libelle' => $row['Libelle'], 
												'Code_club' => $row['Code_club'], 'Numero' => $row['Numero'] ));
				}
			}
		}	
		else
			die ('Aucune compétition sélectionnée');

		// Chargement des infos de la compétition
		$arrayCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);
		if($arrayCompetition['Titre_actif'] == 'O')
			$titreCompet = $arrayCompetition['Libelle'];
		else
			$titreCompet = $arrayCompetition['Soustitre'];
		if($arrayCompetition['Soustitre2'] != '')
			$titreCompet .= ' - '.$arrayCompetition['Soustitre2'];
//		$titreCompet = 'Compétition : '.$arrayCompetition['Libelle'].' ('.$codeCompet.')';

		$logo = str_replace('http://www.kayak-polo.info/','../',$arrayCompetition['LogoLink']);
		$sponsor = str_replace('http://www.kayak-polo.info/','../',$arrayCompetition['SponsorLink']);
//		print_r ($arrayJoueur{$idEquipe});
		// Entête PDF ...	  
 		$pdf = new PDF('L');
		$pdf->Open();
		$pdf->SetTitle("Presence sheets");
		
		$pdf->SetAuthor("Kayak-polo.info");
		$pdf->SetCreator("Kayak-polo.info avec FPDF");
		if($arrayCompetition['Sponsor_actif'] == 'O' && $sponsor != '')
			$pdf->SetAutoPageBreak(true, 30);
		else
			$pdf->SetAutoPageBreak(true, 15);
		mysql_data_seek($result,0);
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);	  
			
			$pdf->AddPage();
			// Affichage
			// logo
			if($arrayCompetition['Kpi_ffck_actif'] == 'O')
			{
				$pdf->Image('../css/banniere1.jpg',10,8,72,15,'jpg',"http://www.kayak-polo.info");
				$pdf->Image('../img/ffck2.jpg',252,8,0,15,'jpg',"http://www.ffck.org");
			}

			if($arrayCompetition['Logo_actif'] == 'O' && $logo != '')  //&& file_exists($logo)
			{
				$size = getimagesize($logo);
				$largeur=$size[0];
				$hauteur=$size[1];
				$ratio=20/$hauteur;	//hauteur imposée de 20mm
				$newlargeur=$largeur*$ratio;
				$posi=149-($newlargeur/2);	//297mm = largeur de page
				$pdf->image($logo, $posi, 8, 0,20);
			}

			if($arrayCompetition['Sponsor_actif'] == 'O' && $sponsor != '')  //&& file_exists($sponsor)
			{
				$size = getimagesize($sponsor);
				$largeur=$size[0];
				$hauteur=$size[1];
				$ratio=16/$hauteur;	//hauteur imposée de 16mm
				$newlargeur=$largeur*$ratio;
				$posi=149-($newlargeur/2);	//297mm = largeur de page
				$pdf->image($sponsor, $posi, 180, 0,16);
			}
			// titre
			$pdf->Ln(20);
			$pdf->SetFont('Arial','BI',12);
			$pdf->Cell(137,8,$titreCompet,0 ,0 ,'L');
			$pdf->Cell(136,8,'Season '.$codeSaison,0 ,1 ,'R');
			$pdf->SetFont('Arial','B',14);
			$pdf->Cell(273,8,"Presence sheet - ".$row['Libelle'],0 ,1 ,'C');
			$pdf->Ln(10);

			$idEquipe = $row['Id'];
			
			$pdf->SetFont('Arial','BI',10);
			$pdf->Cell(35,7,'','',0,'C');
			$pdf->Cell(18,7,'#','B',0,'C');
			$pdf->Cell(18,7,'Cap.','B',0,'C');
			$pdf->Cell(55,7,'Name','B',0,'C');
			$pdf->Cell(55,7,'First name','B',0,'C');
			$pdf->Cell(20,7,'Cat.','B',0,'C');
			$pdf->Cell(20,7,'Club/Nation','B',0,'C');
			$pdf->Cell(20,7,'Arb.','B',1,'C');
			$pdf->SetFont('Arial','',10);
			
			// Mini 12 lignes par équipe
			if($arrayJoueur{$idEquipe}[0]['nbJoueurs'] > 10)
				$nbJoueurs = $arrayJoueur{$idEquipe}[0]['nbJoueurs'] + 2;
			else
				$nbJoueurs = 12;
				
			for ($j=0;$j<$nbJoueurs;$j++)
			{
				if($arrayJoueur{$idEquipe}[$j]['Matric'] != '')
				{
					$pdf->Cell(35,7,'','',0,'C');
					if($arrayJoueur{$idEquipe}[$j]['Numero'] == '0')
						$arrayJoueur{$idEquipe}[$j]['Numero'] = '';
					$pdf->Cell(18,7,$arrayJoueur{$idEquipe}[$j]['Numero'],'B',0,'C');
					$pdf->Cell(18,7,$arrayJoueur{$idEquipe}[$j]['Capitaine'],'B',0,'C');
					$pdf->Cell(55,7,$arrayJoueur{$idEquipe}[$j]['Nom'],'B',0,'C');
					$pdf->Cell(55,7,$arrayJoueur{$idEquipe}[$j]['Prenom'],'B',0,'C');
					$pdf->Cell(20,7,$arrayJoueur{$idEquipe}[$j]['Categ'],'B',0,'C');
					$pdf->Cell(20,7,rtrim($arrayJoueur{$idEquipe}[$j]['Numero_club'], '00'),'B',0,'C');
					$pdf->Cell(20,7,$arrayJoueur{$idEquipe}[$j]['Arbitre'],'B',1,'C');
				}
			}
		}
		$pdf->Output('Presence sheets'.'.pdf','I');
	}
}

$page = new FeuillePresence();
