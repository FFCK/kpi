<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

define('FPDF_FONTPATH','font/');
require('../fpdf/fpdf.php');

// Gestion de la Feuille de Match

class FeuilleMatch extends MyPage	 
{	
	function InitTitulaireEquipe($numEquipe, $idMatch, $idEquipe, $bdd)
	{
		$sql = "Select Count(*) Nb From gickp_Matchs_Joueurs Where Id_match = $idMatch And Equipe = '$numEquipe' ";
		$result = mysql_query($sql, $bdd->m_link) or die ("Erreur Select");

		if (mysql_num_rows($result) != 1)
			return;
			
		$row = mysql_fetch_array($result);
		if ((int) $row['Nb'] > 0)
			return;
			
		$sql  = "Replace Into gickp_Matchs_Joueurs ";
		$sql .= "Select $idMatch, Matric, Numero, '$numEquipe', Capitaine From gickp_Competitions_Equipes_Joueurs ";
		$sql .= "Where Id_equipe = $idEquipe ";
		$sql .= "AND Capitaine <> 'X' ";
		mysql_query($sql, $bdd->m_link) or die ("Erreur Replace InitTitulaireEquipe");
 	}
	
	function FeuilleMatch()
	{
	  MyPage::MyPage();

		$idMatch = utyGetGet('idMatch', -1);
		$_SESSION['idMatch'] = $idMatch;
		
		// Infos match
		$myBdd = new MyBdd();

		$sql  = "Select a.Numero_ordre, a.Date_match, a.Heure_match, a.Libelle Intitule, a.Terrain, a.Secretaire, a.Chronometre, ";
		$sql .= "a.Id_equipeA, a.Id_equipeB, a.Arbitre_principal, a.Arbitre_secondaire, a.ScoreA, a.ScoreB, a.Commentaires_officiels, ";
		$sql .= "b.Nom, b.Phase, b.Libelle, b.Lieu, b.Organisateur, b.Responsable_R1, b.Delegue, b.Code_competition, b.Code_saison ";
		$sql .= "From gickp_Matchs a, gickp_Journees b ";
		$sql .= "Where a.Id = $idMatch ";
		$sql .= "And a.Id_journee = b.Id ";
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Select");
		if (mysql_num_rows($result) == 1)
		{
			$row = mysql_fetch_array($result);	  
	
			$_SESSION['saison'] = $row['Code_saison'];
			$_SESSION['competition'] = $row['Nom'];
			$_SESSION['lieu'] = $row['Lieu'];
			$_SESSION['terrain'] = $row['Terrain'];

			$_SESSION['principal'] = $row['Arbitre_principal'];
			$_SESSION['secondaire'] = $row['Arbitre_secondaire'];
			$_SESSION['organisateur'] = $row['Organisateur'];
			$_SESSION['responsable'] = substr($row['Responsable_R1'],0, 25);
			$_SESSION['delegue'] = $row['Delegue'];
			$_SESSION['secretaire'] = $row['Secretaire'];
			$_SESSION['chronometre'] = $row['Chronometre'];
			$_SESSION['phase'] = $row['Phase'];
			$_SESSION['intitule'] = $row['Intitule'];
	
			$_SESSION['categorie'] = $row['Code_competition'];
			$_SESSION['date'] =  utyDateUsToFr($row['Date_match']);
			$_SESSION['heure'] =  $row['Heure_match'];
			$_SESSION['no'] =  $row['Numero_ordre'];
			
			$_SESSION['ScoreA'] =  $row['ScoreA'];
			$_SESSION['ScoreB'] =  $row['ScoreB'];
			
			$_SESSION['Commentaires'] = $row['Commentaires_officiels'];

			$idEquipeA = $row['Id_equipeA'];
			$idEquipeB = $row['Id_equipeB'];
			
			// Nom Equipe A
			$_SESSION['equipea'] =  '';
			if ($idEquipeA >= 1)
			{
				$sql2  = "Select Libelle From gickp_Competitions_Equipes Where Id = $idEquipeA";
				$result2 = mysql_query($sql2, $myBdd->m_link) or die ("Erreur LoadA : ".$sql2);
				if (mysql_num_rows($result2) == 1)
				{
					$row2 = mysql_fetch_array($result2);	  
					$_SESSION['equipea'] = $row2['Libelle'];
				}
			}
			else
				$_SESSION['equipea'] = '';
			
			// Nom Equipe B
			$_SESSION['equipeb'] =  '';
			if ($idEquipeA >= 1)
			{
				$sql2  = "Select Libelle From gickp_Competitions_Equipes Where Id = $idEquipeB";
				$result2 = mysql_query($sql2, $myBdd->m_link) or die ("Erreur LoadB : ".$sql2);
				if (mysql_num_rows($result2) == 1)
				{
					$row2 = mysql_fetch_array($result2);	  
					$_SESSION['equipeb'] = $row2['Libelle'];
				}
			}
			else
				$_SESSION['equipeb'] = '';
			
			// Info Equipe A
			for ($i=1;$i<=10;$i++)
			{
				$_SESSION['na'.$i] =  '';
				$_SESSION['noma'.$i] =  '';
				$_SESSION['prenoma'.$i] =  '';
				$_SESSION['licencea'.$i] =  '';
				$_SESSION['saisona'.$i] = '';
				$_SESSION['diva'.$i] =  '';
			}
			
			if ($row['Id_equipeA'] >= 1)
				$this->InitTitulaireEquipe('A', $idMatch, $idEquipeA, $myBdd);
			
			$sql  = "Select a.Matric, a.Numero, a.Capitaine, b.Matric, b.Nom, b.Prenom, b.Sexe, b.Naissance, b.Origine, c.Matric Matric_titulaire ";
			$sql .= "From gickp_Matchs_Joueurs a ";
			$sql .= "Left Outer Join gickp_Competitions_Equipes_Joueurs c On (c.Id_equipe = $idEquipeA And c.Matric = a.Matric), "; 
			$sql .= "gickp_Liste_Coureur b ";
			$sql .= "Where a.Matric = b.Matric ";
			$sql .= "And a.Id_match = $idMatch ";
			$sql .= "And a.Equipe = 'A' ";
			$sql .= "Order By a.Numero ";	 
			
			$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load");
			$num_results = mysql_num_rows($result);

			$j=0;
			for ($i=1;$i<=$num_results;$i++)
			{
				$j++;
				$row = mysql_fetch_array($result);	  
				if ($row["Capitaine"] == 'E')
				{
					$j=10;
					$_SESSION['noma'.$j] = $row['Nom'].' (Entraineur)';
					$_SESSION['na'.$j] =  'E';
				}
				elseif ($row["Capitaine"] == 'A')
				{
					$_SESSION['noma'.$j] = $row['Nom'].' (Arbitre)';
					$_SESSION['na'.$j] =  'A';
				}
				elseif ($row["Capitaine"] == 'C')
				{
					$_SESSION['noma'.$j] = $row['Nom'].' (Cap)';
					$_SESSION['na'.$j] =  $row['Numero'];
				}
				else
				{
					$_SESSION['noma'.$j] = $row['Nom'];
					$_SESSION['na'.$j] =  $row['Numero'];
				}
				
				$_SESSION['prenoma'.$j] = $row['Prenom'];
				$_SESSION['licencea'.$j] = $row['Matric'];
				if($row['Nom'] != '' && $row['Origine'] != '' && $row['Origine'] < $_SESSION['saison'])
					$_SESSION['saisona'.$j] = ' ('.$row['Origine'].')';
				
				if ($row['Matric_titulaire'] != $row['Matric'])
					$_SESSION['diva'.$j] = utyCodeCategorie2($row['Naissance']).'(sup)';
				else
					$_SESSION['diva'.$j] = utyCodeCategorie2($row['Naissance']);

				if ($row["Capitaine"] == 'E' or $capitaine == 'A')
					$j=$i-2;
				}
			
			// Info Equipe B
			for ($i=1;$i<=10;$i++)
			{
				$_SESSION['nb'.$i] =  '';
				$_SESSION['nomb'.$i] =  '';
				$_SESSION['prenomb'.$i] =  '';
				$_SESSION['licenceb'.$i] =  '';
				$_SESSION['saisonb'.$i] = '';
				$_SESSION['divb'.$i] =  '';
			}
			
			if ($row['Id_equipeB'] >= 1)
				$this->InitTitulaireEquipe('B', $idMatch, $idEquipeB, $myBdd);
			
			$sql  = "Select a.Matric, a.Numero, a.Capitaine, b.Matric, b.Nom, b.Prenom, b.Sexe, b.Naissance, b.Origine, c.Matric Matric_titulaire ";
			$sql .= "From gickp_Matchs_Joueurs a ";
			$sql .= "Left Outer Join gickp_Competitions_Equipes_Joueurs c On (c.Id_equipe = $idEquipeB And c.Matric = a.Matric), "; 
			$sql .= "gickp_Liste_Coureur b ";
			$sql .= "Where a.Matric = b.Matric ";
			$sql .= "And a.Id_match = $idMatch ";
			$sql .= "And a.Equipe = 'B' ";
			$sql .= "Order By a.Numero ";	 
				
			$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load");
			$num_results = mysql_num_rows($result);

			$j=0;
			for ($i=1;$i<=$num_results;$i++)
			{
				$j++;
				$row = mysql_fetch_array($result);	
				
				if ($row["Capitaine"] == 'E')
				{
					$j=10;
					$_SESSION['nomb'.$j] = $row['Nom'].' (Entraineur)';
					$_SESSION['nb'.$j] =  'E';
				}
				elseif ($row["Capitaine"] == 'A')
				{
					$j=10;
					$_SESSION['nomb'.$j] = $row['Nom'].' (Arbitre)';
					$_SESSION['nb'.$j] =  'A';
				}
				elseif ($row["Capitaine"] == 'C')
				{
					$_SESSION['nomb'.$j] = $row['Nom'].' (Cap)';
					$_SESSION['nb'.$j] =  $row['Numero'];
				}
				else
				{
					$_SESSION['nomb'.$j] = $row['Nom'];
					$_SESSION['nb'.$j] =  $row['Numero'];
				}
				
				$_SESSION['prenomb'.$j] = $row['Prenom'];
				$_SESSION['licenceb'.$j] = $row['Matric'];
				if($row['Nom'] != '' && $row['Origine'] != '' && $row['Origine'] < $_SESSION['saison'])
					$_SESSION['saisonb'.$j] = ' ('.$row['Origine'].')';
				
				if ($row['Matric_titulaire'] != $row['Matric'])
					$_SESSION['divb'.$j] = utyCodeCategorie2($row['Naissance']).'(sup)';
				else
					$_SESSION['divb'.$j] = utyCodeCategorie2($row['Naissance']);

				if ($row["Capitaine"] == 'E' or $row["Capitaine"] == 'A')
					$j=$i-2;
			}
			
			//Détail Match
			$detail = array();
			$scoreDetailA = 0;
			$scoreDetailB = 0;
			
			$sql  = "Select d.Id, d.Id_match, d.Periode, d.Temps, d.Id_evt_match, d.Competiteur, d.Numero, d.Equipe_A_B, ";
			$sql .= "c.Nom, c.Prenom ";
			$sql .= "From gickp_Matchs_Detail d Left Outer Join gickp_Liste_Coureur c On d.Competiteur = c.Matric ";
			$sql .= "Where d.Id_match = $idMatch ";
			$sql .= "Order By d.Periode, d.Temps, d.Id ";	 
			
			$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load ".$sql);
			$num_results = mysql_num_rows($result);


			for ($i=1;$i<=23;$i++)
			{
				$row = mysql_fetch_array($result);
				for($j=1;$j<=11;$j++) { $d[$j] = ''; }
				if($row['Id'])
				{
					if($row['Equipe_A_B'] == 'A')
					{
						if($row['Nom']!='')
							$d[1] = $row['Numero'].'-'.ucwords(strtolower($row['Nom'])).' '.$row['Prenom']{0}.'.';
						else
							$d[1] = 'EQUIPE A';
						switch ($row['Id_evt_match']) {
							case 'B':
								$d[5] = 'X';
								$scoreDetailA++;
							    break;
							case 'V':
							    $d[2] = 'X';
							    break;
							case 'J':
								$d[3] = 'X';
							    break;
							case 'R':
							    $d[4] = 'X';
							    break;
						}
					} else {
						if($row['Nom']!='')
							$d[11] = $row['Numero'].'-'.ucwords(strtolower($row['Nom'])).' '.$row['Prenom']{0}.'.';
						else
							$d[11] = 'EQUIPE B';
						switch ($row['Id_evt_match']) {
							case 'B':
								$d[7] = 'X';
								$scoreDetailB++;
							    break;
							case 'V':
							    $d[8] = 'X';
							    break;
							case 'J':
								$d[9] = 'X';
							    break;
							case 'R':
							    $d[10] = 'X';
							    break;
						}
					}
					$d[6] = $row['Periode'].' - ';
					if(strftime("%M:%S",strtotime($row['Temps']))!='00:00')
						$d[6] .= strftime("%M:%S",strtotime($row['Temps']));
				}
				array_push($detail, array('d1' => $d[1], 'd2' => $d[2], 'd3' => $d[3], 'd4' => $d[4], 'd5' => $d[5], 'd6' => $d[6],
											'd7' => $d[7], 'd8' => $d[8], 'd9' => $d[9], 'd10' => $d[10], 'd11' => $d[11]));
			}
			$_SESSION['detail'] =  array();
			$_SESSION['detail'] =  $detail;
//			$this->m_tpl->assign(scoreDetailA, $scoreDetailA);
//			$this->m_tpl->assign(scoreDetailB, $scoreDetailB);
/*			if($scoreDetailA == $score_A && $scoreDetailB == $score_B)
				$scoreEq = 'O';
			else
				$scoreEq = 'N';
			$this->m_tpl->assign(scoreEq, $scoreEq);
*/			// FIN Détail Match
			
			
   		header('Location: http://'.$_SERVER['HTTP_HOST'].MAIN_DIRECTORY.'/fpdf/marque1.php');	
			exit;	
		}
	}
}

$page = new FeuilleMatch();
