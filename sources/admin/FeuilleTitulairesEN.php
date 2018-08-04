<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

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
	    $this->Cell(135,10,date('d/m/Y - H:i', strtotime('-6 hours')),0,0,'R');
	}
}
 
// Liste des Matchs d'une Journee ou d'un Evenement 
class FeuillePresenceEquipe extends MyPage	 
{	
	function FeuillePresenceEquipe()
	{
		MyPage::MyPage();

		$myBdd = new MyBdd();
		
		$codeEquipe = utyGetGet('equipe');
		$codeCompet = utyGetSession('codeCompet');
		$codeSaison = utyGetSaison();

		// Chargement des équipes ...
		$arrayEquipe = array();
		$arrayJoueur = array();
		$arrayCompetition = array();
		
		if (strlen($codeEquipe) > 0)
		{
			$sql  = "Select Id, Libelle, Code_club, Numero ";
			$sql .= "From gickp_Competitions_Equipes ";
			$sql .= "Where Id = '";
			$sql .= $codeEquipe;
			$sql .= "' Order By Libelle, Id ";	 
	
			$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load Equipes");
			$num_results = mysql_num_rows($result);
			if ($num_results == 0)
				die ('Aucune équipe sélectionnée');
		
			for ($i=0;$i<$num_results;$i++)
			{
				$row = mysql_fetch_array($result);
				$idEquipe = $row['Id'];

				// Chargement des Coureurs ...
				if ($idEquipe != '')
				{
					$sql2  = "Select a.Matric, a.Nom, a.Prenom, a.Sexe, a.Categ, a.Numero, a.Capitaine, "
                            . "b.Origine, b.Numero_club, b.Pagaie_ECA, b.Etat_certificat_CK CertifCK, "
                            . "b.Etat_certificat_APS CertifAPS, b.Reserve icf, c.Arb, c.niveau "
                            . "From gickp_Competitions_Equipes_Joueurs a "
                            . "Left Outer Join gickp_Liste_Coureur b On (a.Matric = b.Matric) "
                            . "Left Outer Join gickp_Arbitre c On (a.Matric = c.Matric) "
                            . "Where Id_Equipe = ";
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
                        $capitaine = str_replace('E', 'Coach', $capitaine);
							
						if (is_null($row2['Arb']))
							$row2['Arb'] = '';
							
/*						// Pour décaler l'entraineur à la fin de la liste
						if ($capitaine == 'E' or $capitaine == 'A')
							$clefEntraineur = $i;
*/
                        if ($row2['Matric'] > 2000000 && $row2['icf'] != NULL && $row2['icf'] != 0) {
                            $row2['Matric'] = 'Icf:' . $row2['icf'];
                        }				
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

        if($arrayCompetition['BandeauLink'] != '' && strpos($arrayCompetition['BandeauLink'], 'http') === FALSE ){
            $arrayCompetition['BandeauLink'] = '../img/logo/' . $arrayCompetition['BandeauLink'];
            if(is_file($arrayCompetition['BandeauLink'])) {
                $bandeau = $arrayCompetition['BandeauLink'];
            }
        } elseif($arrayCompetition['BandeauLink'] != '') {
            $bandeau = $arrayCompetition['BandeauLink'];
        } 
        if($arrayCompetition['LogoLink'] != '' && strpos($arrayCompetition['LogoLink'], 'http') === FALSE ){
            $arrayCompetition['LogoLink'] = '../img/logo/' . $arrayCompetition['LogoLink'];
            if(is_file($arrayCompetition['LogoLink'])) {
                $logo = $arrayCompetition['LogoLink'];
            }
        } elseif($arrayCompetition['LogoLink'] != '') {
            $logo = $arrayCompetition['LogoLink'];
        }
        
        if($arrayCompetition['SponsorLink'] != '' && strpos($arrayCompetition['SponsorLink'], 'http') === FALSE ){
            $arrayCompetition['SponsorLink'] = '../img/logo/' . $arrayCompetition['SponsorLink'];
            if(is_file($arrayCompetition['SponsorLink'])) {
                $sponsor = $arrayCompetition['SponsorLink'];
            }
        } elseif($arrayCompetition['SponsorLink'] != '') {
            $sponsor = $arrayCompetition['SponsorLink'];
        }

        // Entête PDF ...	  
 		$pdf = new PDF('L');
		$pdf->Open();
		$pdf->SetTitle("Team roster");
		
		$pdf->SetAuthor("Kayak-polo.info");
		$pdf->SetCreator("Kayak-polo.info width FPDF");
		if($arrayCompetition['Sponsor_actif'] == 'O' && isset($sponsor))
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
                $pdf->Image('../img/logoKPI-small.jpg',125,10,0,20,'jpg',"http://www.ffck.org");
            }
            if($arrayCompetition['Bandeau_actif'] == 'O' && isset($bandeau)){
                $size = getimagesize($bandeau);
                $largeur=$size[0];
                $hauteur=$size[1];
                $ratio=20/$hauteur;	//hauteur imposée de 20mm
                $newlargeur=$largeur*$ratio;
                $posi=149-($newlargeur/2);	//210mm = largeur de page
                $pdf->image($bandeau, $posi, 8, 0,20);
            } elseif($arrayCompetition['Logo_actif'] == 'O' && isset($logo)){
                $size = getimagesize($logo);
                $largeur=$size[0];
                $hauteur=$size[1];
                $ratio=20/$hauteur;	//hauteur imposée de 20mm
                $newlargeur=$largeur*$ratio;
                $posi=149-($newlargeur/2);	//210mm = largeur de page
                $pdf->image($logo, $posi, 8, 0,20);
            }

            if($arrayCompetition['Sponsor_actif'] == 'O' && isset($sponsor)){
                $size = getimagesize($sponsor);
                $largeur=$size[0];
                $hauteur=$size[1];
                $ratio=16/$hauteur;	//hauteur imposée de 16mm
                $newlargeur=$largeur*$ratio;
                $posi=149-($newlargeur/2);	//210mm = largeur de page
                $pdf->image($sponsor, $posi, 180, 0,16);
            }

            // titre
			$pdf->Ln(20);
			$pdf->SetFont('Arial','BI',12);
			$pdf->Cell(137,8,$titreCompet,0,0,'L');
			$pdf->Cell(136,8,'Season : '.$codeSaison,0,1,'R');
			$pdf->SetFont('Arial','B',14);
			$pdf->Cell(273,8,"Team roster - ".$row['Libelle'],0,'1','C');
			$pdf->Ln(10);

			$idEquipe = $row['Id'];
			
			$pdf->SetFont('Arial','B',10);
			$pdf->Cell(25,7,'','',0,'C');
			$pdf->Cell(16,7,'#','B',0,'C');
			$pdf->Cell(18,7,'Cap','B',0,'C');
			$pdf->Cell(30,7,'ID','B',0,'C');
			$pdf->Cell(60,7,'Name','B',0,'C');
			$pdf->Cell(60,7,'First name','B',0,'C');
//			$pdf->Cell(28,7,'Categ','B',0,'C');
			//$pdf->Cell(18,7,'Pag. EC','LTR',0,'C');
			//$pdf->Cell(27,7,'Certif. Comp.','LTR',0,'C');
			// $pdf->Cell(18,7,'CertifAPS','LTR',0,'C');
			$pdf->Cell(25,7,'Club','B',0,'C');
			$pdf->Cell(17,7,'Ref.','B',1,'C');
			$pdf->SetFont('Arial','',10);
			
			// Mini 12 lignes par équipe
			if($arrayJoueur{$idEquipe}[0]['nbJoueurs'] > 10)
				$nbJoueurs = $arrayJoueur{$idEquipe}[0]['nbJoueurs'] + 2;
			else
				$nbJoueurs = 12;
				
			for ($j=0;$j<$nbJoueurs;$j++)
			{
				if(isset($arrayJoueur{$idEquipe}[$j])){
                    $pdf->Cell(25,8,'','',0,'C');
                    $pdf->Cell(16,8,$arrayJoueur{$idEquipe}[$j]['Numero'],'B',0,'C');
                    $pdf->Cell(18,8,$arrayJoueur{$idEquipe}[$j]['Capitaine'],'B',0,'C');
                    $pdf->Cell(30,8,$arrayJoueur{$idEquipe}[$j]['Matric'].$arrayJoueur{$idEquipe}[$j]['Saison'],'B',0,'C');
                    $pdf->Cell(60,8,$arrayJoueur{$idEquipe}[$j]['Nom'],'B',0,'C');
                    $pdf->Cell(60,8,$arrayJoueur{$idEquipe}[$j]['Prenom'],'B',0,'C');
//                    $pdf->Cell(28,8,$arrayJoueur{$idEquipe}[$j]['Categ'],'B',0,'C');
                    //$pdf->Cell(18,7,$arrayJoueur{$idEquipe}[$j]['Pagaie'],'LTRB',0,'C');
                    //$pdf->Cell(27,7,$arrayJoueur{$idEquipe}[$j]['CertifCK'],'LTRB',0,'C');
                    // $pdf->Cell(18,7,$arrayJoueur{$idEquipe}[$j]['CertifAPS'],'LTRB',0,'C');
                    $pdf->Cell(25,8,rtrim($arrayJoueur{$idEquipe}[$j]['Numero_club'], '00'),'B',0,'C');
                    $pdf->Cell(17,8,$arrayJoueur{$idEquipe}[$j]['Arbitre'],'B',1,'C');
                } else {
                    $pdf->Cell(25,8,'','',0,'C');
                    $pdf->Cell(16,8,'','B',0,'C');
                    $pdf->Cell(18,8,'','B',0,'C');
                    $pdf->Cell(30,8,'','B',0,'C');
                    $pdf->Cell(50,8,'','B',0,'C');
                    $pdf->Cell(50,8,'','B',0,'C');
                    $pdf->Cell(28,8,'','B',0,'C');
//                    $pdf->Cell(18,8,'','B',0,'C');
//                    $pdf->Cell(27,8,'','B',0,'C');
//                    $pdf->Cell(18,7,'','LTRB',0,'C');
                    $pdf->Cell(17,8,'','B',0,'C');
                    $pdf->Cell(17,8,'','B',1,'C');                }
            }
		}
		$pdf->Output("Team roster".'.pdf','I');
	}
}

$page = new FeuillePresenceEquipe();
