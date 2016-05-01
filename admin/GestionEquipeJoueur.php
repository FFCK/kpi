<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion des Joueurs d'une Equipe

class GestionEquipeJoueur extends MyPageSecure	 
{	
	function Load()
	{
		$idEquipe = utyGetSession('idEquipe', -1);
		$idEquipe = utyGetGet('idEquipe', $idEquipe);	
		$_SESSION['idEquipe'] = $idEquipe;
		
		$codeSaison = utyGetSaison();
		$codeCompet = utyGetSession('codeCompet');

		$Limit_Clubs = utyGetSession('Limit_Clubs', '');
		$Limit_Clubs = explode(',',$Limit_Clubs);
		
		$_SESSION['parentUrl'] = $_SERVER['PHP_SELF'];
		
		$_SESSION['updatecell_tableName'] = 'gickp_Competitions_Equipes_Joueurs';
		$_SESSION['updatecell_where'] = 'Where Matric = ';
		$_SESSION['updatecell_document'] = 'formEquipeJoueur';
		
		$myBdd = new MyBdd();
		
		// Chargement des Equipes ...
		$arrayEquipe = array();
		
		if (strlen($codeCompet) > 0)
		{ 
			if ($codeCompet != 'POOL')
			{
				$sql  = "Select ce.Id, ce.Libelle, ce.Code_club, ce.Numero, ce.Poule, ce.Tirage, c.Code_comite_dep  ";
				$sql .= "From gickp_Competitions_Equipes ce, gickp_Club c ";
				$sql .= "Where ce.Code_compet = '";
				$sql .= $codeCompet;
				$sql .= "' And ce.Code_saison = '";
				$sql .= $codeSaison;
				$sql .= "' And ce.Code_club = c.Code ";	 
				$sql .= " Order By ce.Poule, ce.Tirage, ce.Libelle, ce.Id ";
			} else {
				$sql  = "Select ce.Id, ce.Libelle, ce.Code_club, ce.Numero, ce.Poule, ce.Tirage ";
				$sql .= "From gickp_Competitions_Equipes ce ";
				$sql .= "Where ce.Code_compet = '";
				$sql .= $codeCompet;
				$sql .= "' Order By ce.Poule, ce.Tirage, ce.Libelle, ce.Id ";
			}
			
			$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load => ".$sql);
			$num_results = mysql_num_rows($result);
		 
			for ($i=0;$i<$num_results;$i++)
			{
				$row = mysql_fetch_array($result);	  
				if (strlen($row['Code_comite_dep']) > 3)
					$row['Code_comite_dep'] = 'FRA';
				if ($row['Tirage'] != 0 or $row['Poule'] != '')
					$this->m_tpl->assign('Tirage', 'ok');
				array_push($arrayEquipe, array('Id' => $row['Id'], 'Libelle' => $row['Libelle'], 'Code_club' => $row['Code_club'], 'Numero' => $row['Numero'], 'Poule' => $row['Poule'], 'Tirage' => $row['Tirage'], 'Code_comite_dep' => $row['Code_comite_dep'] ));
			}
		}	
		$this->m_tpl->assign('arrayEquipe', $arrayEquipe);
		
		// Chargement des Joueurs ...
		$arrayJoueur = array();

		$clefEntraineur = '';
		
		if ($idEquipe > 0)
		{
			// Nom de l'Equipe et de la Compétition ...
			$sql  = "Select eq.Code_compet, eq.Code_club, eq.Code_saison, eq.Libelle, cp.Verrou, cp.Statut ";
			$sql .= "From gickp_Competitions_Equipes eq, gickp_Competitions cp ";
			$sql .= "Where eq.Code_compet = cp.Code And cp.Code_saison = '";
			$sql .= utyGetSaison();
			$sql .= "' And eq.Id = ";
			$sql .= $idEquipe;
			
			$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Select");
			$num_results = mysql_num_rows($result);
			if ($num_results == 1)
			{
				$row = mysql_fetch_array($result);	  
				$infoEquipe = 'Equipe : '.$row['Libelle'].' ('.$row['Code_compet'].'-'.$row['Code_saison'].')';
				$infoEquipe2 = $row['Libelle'].' ('.$row['Code_compet'].'-'.$row['Code_saison'].')';
				$_SESSION['infoEquipe'] = $infoEquipe;
				$_SESSION['codeClub'] = $row['Code_club'];
				
				if(count($Limit_Clubs) > 0 && $row['Verrou'] != 'O')
				{
					$row['Verrou'] = 'O';
					foreach ($Limit_Clubs as $value)
					{
						if(mb_eregi('(^'.$value.')',$row['Code_club']))
							$row['Verrou'] = '';
					}
				}
				if(substr($row['Code_compet'],0,1) == 'N')
					$typeCompet = 'CH';
				elseif(substr($row['Code_compet'],0,2) == 'CF')
					$typeCompet = 'CF';
				else
					$typeCompet = '';
				$this->m_tpl->assign('typeCompet', $typeCompet);	
				$this->m_tpl->assign('headerSubTitle', $infoEquipe);	
				$this->m_tpl->assign('infoEquipe2', $infoEquipe2);	
				$this->m_tpl->assign('Verrou', $row['Verrou']);
				$this->m_tpl->assign('Statut', $row['Statut']);
			}
			
			// Intégrer les coureurs de la recherche Licence ...
			if (isset($_SESSION['Signature']))
			{
				$sql  = "Replace Into gickp_Competitions_Equipes_Joueurs (Id_equipe, Matric, Nom, Prenom, Sexe, Categ) ";
				$sql .= "Select $idEquipe, a.Matric, a.Nom, a.Prenom, a.Sexe, c.Code ";
				$sql .= "From gickp_Liste_Coureur a, gickp_Recherche_Licence b, gickp_Categorie c ";
				$sql .= "Where a.Matric = b.Matric ";
				$sql .= "And b.Signature = '";
				$sql .= $_SESSION['Signature'];
				$sql .= "' And b.Validation = 'O' ";
				$sql .= "And " ;
				$sql .= utyGetSaison();
				$sql .= "-Year(a.Naissance) between c.Age_min And c.Age_max ";
				
				mysql_query($sql, $myBdd->m_link) or die ("Erreur Replace");

				// Vidage gickp_Recherche_Licence ...				
				$sql = "Delete From gickp_Recherche_Licence Where Signature = '";
				$sql .= $_SESSION['Signature'];
				$sql .= "'";
				mysql_query($sql, $myBdd->m_link) or die ("Erreur Delete");
				
				unset($_SESSION['Signature']);
			}			
			
			// Chargement des Coureurs ...
			$sql  = "SELECT a.Matric, a.Nom, a.Prenom, a.Sexe, a.Categ, a.Numero, a.Capitaine, b.Origine, b.Numero_club, "
                    . "b.Pagaie_ECA, b.Pagaie_EVI, b.Pagaie_MER, b.Etat_certificat_CK CertifCK, "
                    . "b.Etat_certificat_APS CertifAPS, c.Arb, c.niveau, s.Date date_surclassement "
                    . "FROM gickp_Competitions_Equipes_Joueurs a "
                    . "LEFT OUTER JOIN gickp_Liste_Coureur b On (a.Matric = b.Matric) "
                    . "LEFT OUTER JOIN gickp_Arbitre c On (a.Matric = c.Matric) "
                    . "LEFT OUTER JOIN gickp_Surclassements s ON (a.Matric = s.Matric AND s.Saison = ".utyGetSaison().") "
                    . "WHERE Id_Equipe = ";
			$sql .= $idEquipe;
			$sql .= " ORDER BY Field(if(a.Capitaine='C','-',if(a.Capitaine='','-',a.Capitaine)), '-', 'E', 'A', 'X'), Numero, Nom, Prenom ";	 //  

			$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load <br>".$sql);
			$num_results = mysql_num_rows($result);
		
			for ($i=0;$i<$num_results;$i++)
			{
				$row = mysql_fetch_array($result);
				if($row['niveau'] != '')
					$row['Arb'] .= '-'.$row['niveau'];
				
				$numero = $row['Numero'];
				if (strlen($numero) == 0)
					$numero = 0;
				
				Switch ($row['Pagaie_ECA'])
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
					
				$capitaine = $row['Capitaine'];
				if (strlen($capitaine) == 0)
					$capitaine = '-';
                $row['date_surclassement'] = utyDateUsToFr($row['date_surclassement']);
				// Pour décaler l'entraineur à la fin de la liste
				if ($capitaine == 'E' or $capitaine == 'A' or $capitaine == 'X')
					$clefEntraineur = $i;
					
				array_push($arrayJoueur, array( 'Matric' => $row['Matric'], 'Nom' => ucwords(strtolower($row['Nom'])), 'Prenom' => ucwords(strtolower($row['Prenom'])), 
																				'Sexe' => $row['Sexe'], 'Categ' => $row['Categ'], 'Pagaie' => $pagaie, 'CertifCK' => $row['CertifCK'],  
																				'CertifAPS' => $row['CertifAPS'], 'Numero' => $numero, 'Capitaine' => $capitaine, 'Pagaie_ECA' => $row['Pagaie_ECA'], 
																				'Pagaie_EVI' => $row['Pagaie_EVI'] ,  'Pagaie_MER' => $row['Pagaie_MER'], 'Arbitre' => $row['Arb'],
																				'Saison' => $row['Origine'], 'Numero_club' => $row['Numero_club'],
                                                                                'date_surclassement' => $row['date_surclassement'] ));
			}
		}
/*		if($clefEntraineur != '')
		{
			// Prélève les non joueurs de la liste
			$decaleEntraineur = array_splice($arrayJoueur, $clefEntraineur, 1);
			// Replace les non joueurs à la fin
			array_splice($arrayJoueur, 9, 0, $decaleEntraineur);
		}
*/		

		$sql  = "Select j.Dates, j.Users, j.Journal, u.Identite ";
		$sql .= "From gickp_Journal j, gickp_Utilisateur u ";
		$sql .= "Where j.Users = u.Code ";
		$sql .= "And (j.Actions = 'Ajout titulaire' Or j.Actions = 'Suppression titulaire') ";
		$sql .= "And j.Journal Like 'Equipe : ".$idEquipe." -%' ";
		$sql .= "Order by j.Dates Desc ";
		$sql .= "Limit 1 ";
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load Last Update<br>".$sql);
		$num_results = mysql_num_rows($result);
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);	  
			$this->m_tpl->assign('LastUpdate', utyDateUsToFr(substr($row['Dates'],0,10)));
			$this->m_tpl->assign('LastUpdater', $row['Identite']);
			$this->m_tpl->assign('LastUpd', $row['Journal']);
		}
		$this->m_tpl->assign('arrayJoueur', $arrayJoueur);
		$this->m_tpl->assign('idEquipe', $idEquipe);
		$this->m_tpl->assign('sSaison', utyGetSaison());
	}
	
	function Add()
	{
		
		$idEquipe = utyGetPost('idEquipe', '');
			
		$matricJoueur = utyGetPost('matricJoueur', '');
		$nomJoueur = utyGetPost('nomJoueur', '');
		$prenomJoueur = utyGetPost('prenomJoueur', '');
		$sexeJoueur = utyGetPost('sexeJoueur', '');
		$naissanceJoueur = utyGetPost('naissanceJoueur', '');
			$naissanceJoueur = utyDateFrToUs($naissanceJoueur);
		$capitaineJoueur = utyGetPost('capitaineJoueur', '-');
		$numeroJoueur = utyGetPost('numeroJoueur', '');
		$arbitreJoueur = utyGetPost('arbitreJoueur', '');
		
		if (strlen($idEquipe) > 0)
		{
			$myBdd = new MyBdd();
	
			$categJoueur = utyCodeCategorie2($naissanceJoueur);
					
			if (strlen($matricJoueur) == 0)
				$matricJoueur = $myBdd->GetNextMatricLicence();

			$codeClub = $myBdd->GetCodeClubEquipe($idEquipe);
			$myBdd->InsertIfNotExistLicence($matricJoueur, $nomJoueur, $prenomJoueur, $sexeJoueur, $naissanceJoueur, $codeClub);
			
			$sql  = "Insert Into gickp_Competitions_Equipes_Joueurs (Id_equipe, Matric, Nom, Prenom, Sexe, Categ, Numero, Capitaine) Values (";
			$sql .= $idEquipe;
			$sql .= ",";
			$sql .= mysql_real_escape_string($matricJoueur);
			$sql .= ",'";
			$sql .= mysql_real_escape_string($nomJoueur);
			$sql .= "','";
			$sql .= mysql_real_escape_string($prenomJoueur);
			$sql .= "','";
			$sql .= mysql_real_escape_string($sexeJoueur);
			$sql .= "','";
			$sql .= mysql_real_escape_string($categJoueur);
			$sql .= "','";
			$sql .= mysql_real_escape_string($numeroJoueur);
			$sql .= "','";
			$sql .= mysql_real_escape_string($capitaineJoueur);
			$sql .= "') ";
			
			mysql_query($sql, $myBdd->m_link) or die ("Erreur insert");
			
			if (($matricJoueur >= 2000000) && ($arbitreJoueur != ''))
			{
				$sql  = "Insert Into gickp_Arbitre (Matric, Regional, InterRegional, National, International, Arb) Values (";
				$sql .= mysql_real_escape_string($matricJoueur);
				switch ($arbitreJoueur)
				{
				case 'REG' :
					$sql .= ",'O','N','N','N','Reg') ";
					break;
				case 'IR' :
					$sql .= ",'N','O','N','N','IR') ";
					break;
				case 'NAT' :
					$sql .= ",'N','N','O','N','Nat') ";
					break;
				case 'INT' :
					$sql .= ",'N','N','O','O','Int') ";
					break;
				default :
					$sql .= ",'N','N','N','N','') ";
					break;
				}
				mysql_query($sql, $myBdd->m_link) or die ("Erreur insert arb.<br />".$sql);
			}
			
			$myBdd->utyJournal('Ajout titulaire', '', '', 'NULL', 'NULL', 'NULL', 'Equipe : '.$idEquipe.' - Joueur : '.$matricJoueur);
		}
	}
	
	function Add2()
	{
		$idEquipe = utyGetPost('idEquipe', '');
			
		$matricJoueur = utyGetPost('matricJoueur2', '');
		$nomJoueur = utyGetPost('nomJoueur2', '');
		$prenomJoueur = utyGetPost('prenomJoueur2', '');
		$sexeJoueur = utyGetPost('sexeJoueur2', '');
		$naissanceJoueur = utyGetPost('naissanceJoueur2', '');
			//$naissanceJoueur = utyDateFrToUs($naissanceJoueur);
		$categJoueur = utyGetPost('categJoueur2', '');
		$capitaineJoueur = utyGetPost('capitaineJoueur2', '-');
		$numeroJoueur = utyGetPost('numeroJoueur2', '');
		
		if ($idEquipe > 0)
		{
			$myBdd = new MyBdd();
			if (strlen($matricJoueur) == 0)
				$matricJoueur = $myBdd->GetNextMatricLicence();

			$sql  = "Insert Into gickp_Competitions_Equipes_Joueurs (Id_equipe, Matric, Nom, Prenom, Sexe, Categ, Numero, Capitaine) Values (";
			$sql .= $idEquipe;
			$sql .= ",";
			$sql .= mysql_real_escape_string($matricJoueur);
			$sql .= ",'";
			$sql .= mysql_real_escape_string($nomJoueur);
			$sql .= "','";
			$sql .= mysql_real_escape_string($prenomJoueur);
			$sql .= "','";
			$sql .= mysql_real_escape_string($sexeJoueur);
			$sql .= "','";
			$sql .= mysql_real_escape_string($categJoueur);
			$sql .= "','";
			$sql .= mysql_real_escape_string($numeroJoueur);
			$sql .= "','";
			$sql .= mysql_real_escape_string($capitaineJoueur);
			$sql .= "') ";
			mysql_query($sql, $myBdd->m_link) or die ("Erreur insert");
			
			$myBdd->utyJournal('Ajout titulaire', '', '', 'NULL', 'NULL', 'NULL', 'Equipe : '.$idEquipe.' - Joueur : '.$matricJoueur);
		}
	}
	
	/* AddCoureur :  Fct Obsolète */
	function AddCoureur()
	{
		$idEquipe = utyGetPost('idEquipe', '');
			
		$ParamCmd = utyGetPost('ParamCmd', '');
	
		if (strlen($idEquipe) > 0)
		{
			$data = explode('|',$ParamCmd);
			$Matric = $data[0];
			$Categ = "'".$data[1]."'";
			
			$myBdd = new MyBdd();

			$sql  = "Replace Into gickp_Competitions_Equipes_Joueurs (Id_equipe, Matric, Nom, Prenom, Sexe, Categ) ";
			$sql .= "Select $idEquipe, Matric, Nom, Prenom, Sexe, $Categ From gickp_Liste_Coureur ";
			$sql .= "Where Matric = ";
			$sql .= $Matric;
		
			mysql_query($sql, $myBdd->m_link) or die ("Erreur insert");
			
			$myBdd->utyJournal('Ajout coureur', '', '', 'NULL', 'NULL', 'NULL', 'Equipe : '.$idEquipe.' - Joueur : '.$Matric);
		}
	}
	
	function Remove()
	{
		$idEquipe = utyGetPost('idEquipe', '');
		$ParamCmd = utyGetPost('ParamCmd', '');
			
		$arrayParam = split ('[,]', $ParamCmd);		
		if (count($arrayParam) == 0)
			return; // Rien à Detruire ...
			
		$myBdd = new MyBdd();
		$sql  = "Delete From gickp_Competitions_Equipes_Joueurs Where Id_Equipe = ";
		$sql .= $_SESSION['idEquipe'];
		$sql .= " And Matric In (";
		for ($i=0;$i<count($arrayParam);$i++)
		{
			if ($i > 0)
				$sql .= ",";
			
			$sql .= $arrayParam[$i];
			$myBdd->utyJournal('Suppression titulaire', '', '', 'NULL', 'NULL', 'NULL', 'Equipe : '.$idEquipe.' - Joueur : '.$arrayParam[$i]);
		}
		$sql .= ")";
		
		mysql_query($sql, $myBdd->m_link) or die ("Erreur Delete");

	}
	
	function Find()
	{
		$_SESSION['Signature'] = uniqid('GEJ-');

		if (isset($_SESSION['codeClub']))
		{
			$_SESSION['codeComiteDep'] = utyCodeComiteDept($_SESSION['codeClub']);
			$_SESSION['codeComiteReg'] = utyCodeComiteReg($_SESSION['codeComiteDep']);
		}
		
		header("Location: http://".$_SERVER['HTTP_HOST'].MAIN_DIRECTORY.'/admin/RechercheLicence.php');	
		exit;	
	}
	
	function GestionEquipeJoueur()
	{			
	  MyPageSecure::MyPageSecure(10);
		
		$alertMessage = '';
		
		$Cmd = '';
		if (isset($_POST['Cmd']))
			$Cmd = $_POST['Cmd'];

		$ParamCmd = '';
		if (isset($_POST['ParamCmd']))
			$ParamCmd = $_POST['ParamCmd'];

		if (strlen($Cmd) > 0)
		{
			if ($Cmd == 'Add')
				($_SESSION['Profile'] <= 4) ? $this->Add() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'Add2')
				($_SESSION['Profile'] <= 8) ? $this->Add2() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'AddCoureur') // Obsolète ...
				($_SESSION['Profile'] <= 4) ? $this->AddCoureur() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'Remove')
				($_SESSION['Profile'] <= 8) ? $this->Remove() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'Find')
				($_SESSION['Profile'] <= 8) ? $this->Find() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($alertMessage == '')
			{
				header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);	
				exit;
			}
		}

		$this->SetTemplate("Feuilles de présence", "Equipes", false);
		$this->Load();
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplate('GestionEquipeJoueur');
	}
}		  	

$page = new GestionEquipeJoueur();

?>

