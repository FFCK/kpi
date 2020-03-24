<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion des Joueurs d'une Equipe pour un Match 

class GestionMatchEquipeJoueur extends MyPageSecure	 
{	
	function Load()
	{
		$idMatch = utyGetSession('idMatch',-1);
		$idMatch = utyGetGet('idMatch',$idMatch);
		$_SESSION['idMatch'] = $idMatch;	
		
		$codeEquipe = utyGetSession('codeEquipe','?');
		$codeEquipe = utyGetGet('codeEquipe',$codeEquipe);
		$_SESSION['codeEquipe'] = $codeEquipe;	
		
		$_SESSION['parentUrl'] = $_SERVER['PHP_SELF'];
		
		$_SESSION['updatecell_tableName'] = 'gickp_Matchs_Joueurs';
		$_SESSION['updatecell_where'] = "Where Id_match = $idMatch And Matric = ";
		$_SESSION['updatecell_document'] = 'formMatchEquipeJoueur';
				
		$idEquipe = -1;
				
		$myBdd = new MyBdd();
		
		// Chargement des Joueurs provenant de la Recherche ...
		if (isset($_SESSION['Signature']))
		{
				$sql  = "Replace Into gickp_Matchs_Joueurs (Id_match, Matric, Equipe) ";
				$sql .= "Select $idMatch, a.Matric, '$codeEquipe' ";
				$sql .= "From gickp_Liste_Coureur a, gickp_Recherche_Licence b ";
				$sql .= "Where a.Matric = b.Matric ";
				$sql .= "And b.Signature = '";
				$sql .= $_SESSION['Signature'];
				$sql .= "' And b.Validation = 'O' ";
				
				$myBdd->Query($sql);

				// Vidage gickp_Recherche_Licence ...				
				$sql = "Delete From gickp_Recherche_Licence Where Signature = '";
				$sql .= $_SESSION['Signature'];
				$sql .= "'";
				$myBdd->Query($sql);
				
				unset($_SESSION['Signature']);
		}

		$clefEntraineur = '';
		
		if ($idMatch > 0)
		{
			// Prise des Informations sur le Match ...
			$sql  = "Select Date_match, Heure_match, Libelle, Terrain, Numero_ordre, Validation, Id_journee ";
			$sql .= "From gickp_Matchs ";
			$sql .= "Where Id = ";
			$sql .= $idMatch;
			
			$result = $myBdd->Query($sql);
			if ($myBdd->NumRows($result) == 1)
			{
					$row = $myBdd->FetchArray($result);	 
					$Numero_ordre = $row['Numero_ordre'];
						// Titre ...
					$this->m_tpl->assign('headerTitle', '(Saison '.utyGetSaison().') Match n°'.$row['Numero_ordre'].' du '.utyDateUsToFr($row['Date_match']).' à '.$row['Heure_match'].' Terrain '.$row['Terrain'].' ('.$idMatch.')');	
					$this->m_tpl->assign('idJournee', $row['Id_journee']);
					$this->m_tpl->assign('Validation', $row['Validation']);
			}
			
			// Prise des Informations sur l'Equipe ...
			$sql  = "Select a.Id, a.Code_compet, a.Code_saison, a.Libelle, a.Code_club ";
			$sql .= "From gickp_Competitions_Equipes a,	gickp_Matchs b ";
			if ($codeEquipe == 'A')
				$sql .= "Where a.Id  = b.Id_EquipeA ";
			else
				$sql .= "Where a.Id  = b.Id_EquipeB ";
			$sql .= "And b.Id = ";
			$sql .= $idMatch;
				
			$result = $myBdd->Query($sql);
			if ($myBdd->NumRows($result) == 1)
			{
					$row = $myBdd->FetchArray($result);	 
					$idEquipe = $row['Id'];
			
					$infoEquipe = $row['Libelle'].' ('.$row['Code_compet'].'-'.$row['Code_saison'].')';
					$_SESSION['idEquipe'] = $idEquipe;
					$_SESSION['infoEquipe'] = $infoEquipe;
					$_SESSION['codeClub'] = $row['Code_club'];
					
					// Sous-Titre ...
					$this->m_tpl->assign('headerSubTitle', 'Joueurs Equipe '.$codeEquipe.' : '.$infoEquipe);
			}
			
			// Chargement des Joueurs de l'Equipe déja inscrit ...
			$arrayJoueur = array();
			
			$sql  = "Select a.Matric, a.Numero, a.Capitaine, b.Matric, b.Nom, b.Prenom, b.Sexe, b.Naissance, "
                    . "b.Origine, b.Numero_club, b.Pagaie_ECA, b.Pagaie_EVI, b.Pagaie_MER, "
                    . "b.Etat_certificat_CK CertifCK, b.Etat_certificat_APS CertifAPS, b.Reserve icf, c.Arb, c.niveau "
                    . "From gickp_Matchs_Joueurs a Left Outer Join gickp_Arbitre c On (a.Matric = c.Matric), "
                    . "gickp_Liste_Coureur b "
                    . "Where a.Matric = b.Matric "
                    . "And a.Id_match = ";
			$sql .= $idMatch;
			$sql .= " And a.Equipe = '";
			$sql .= $codeEquipe;
			$sql .= "' Order By Field(if(a.Capitaine='C','-',if(a.Capitaine='','-',a.Capitaine)), '-', 'E', 'A', 'X'), Numero, Nom, Prenom ";	 
		
			$result = $myBdd->Query($sql);
            while($row = $myBdd->FetchArray($result)) {
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
					
/*				// Pour décaler l'entraineur à la fin de la liste
				if ($capitaine == 'E' or $capitaine == 'A')
					$clefEntraineur = $i;
*/						
				array_push($arrayJoueur, array( 'Matric' => $row['Matric'], 'Numero' => $numero, 'Capitaine' => $capitaine,
                    'Nom' => ucwords(strtolower($row['Nom'])), 'Prenom' => ucwords(strtolower($row['Prenom'])),  
                    'Pagaie' => $pagaie, 'CertifCK' => $row['CertifCK'],  'CertifAPS' => $row['CertifAPS'], 
                    'Sexe' => $row['Sexe'], 'Categ' => utyCodeCategorie2($row['Naissance']), 'Pagaie_ECA' => $row['Pagaie_ECA'], 
                    'Pagaie_EVI' => $row['Pagaie_EVI'] ,  'Pagaie_MER' => $row['Pagaie_MER'], 'Arbitre' => $row['Arb'], 
                    'Saison' => $row['Origine'], 'Numero_club' => $row['Numero_club'], 'icf' => $row['icf']) );
			}
		}	
		
/*		if($clefEntraineur != '')
		{
			// Prélève l'entraineur de la liste
			$decaleEntraineur = array_splice($arrayJoueur, $clefEntraineur, 1);
			// Replace l'entraine à la fin
			array_splice($arrayJoueur, 9, 0, $decaleEntraineur);
		}
*/		
		$this->m_tpl->assign('arrayJoueur', $arrayJoueur);
		$this->m_tpl->assign('idMatch', $idMatch);
		$this->m_tpl->assign('sSaison', utyGetSaison());
		$this->m_tpl->assign('Numero_ordre', $Numero_ordre);
		$this->m_tpl->assign('infoEquipe', $infoEquipe);
	}
	
	function AddJoueurTitulaire()
	{
		$codeEquipe = utyGetSession('codeEquipe','?');
		$idEquipe = utyGetSession('idEquipe',-1);
		$idMatch = utyGetSession('idMatch',-1);
			
		$myBdd = new MyBdd();

		// Contrôle verrouillage Match ...
		$sql  = "Select Validation From gickp_Matchs Where Id = ".$idMatch;
		$result = $myBdd->Query($sql);
		$row = $myBdd->FetchArray($result);
		if ($row['Validation'] == 'O')
			return;

		$sql  = "Replace Into gickp_Matchs_Joueurs ";
		$sql .= "Select $idMatch, Matric, Numero, '$codeEquipe', Capitaine From gickp_Competitions_Equipes_Joueurs ";
		$sql .= "Where Id_equipe = $idEquipe ";
		$sql .= "AND Capitaine <> 'X' ";
		$sql .= "AND Capitaine <> 'A' ";
	
		$myBdd->Query($sql, $myBdd->m_link) or die ("Erreur Replace");

		$myBdd->utyJournal('Ajout titulaires match', '', '', '', '', $idMatch, 'Equipe : '.$idEquipe);
	}
	
	function Add2()
	{
		$codeEquipe = utyGetSession('codeEquipe','?');
		$idEquipe = utyGetSession('idEquipe',-1);
		$idMatch = utyGetSession('idMatch',-1);

		$myBdd = new MyBdd();

		// Contrôle verrouillage Match ...
		$sql  = "Select Validation From gickp_Matchs Where Id = ".$idMatch;
		$result = $myBdd->Query($sql);
		$row = $myBdd->FetchArray($result);
		if ($row['Validation'] == 'O')
			return;

		$matricJoueur = utyGetPost('matricJoueur2', '');
		$capitaineJoueur = utyGetPost('capitaineJoueur2', '-');
		$numeroJoueur = utyGetPost('numeroJoueur2', '');
		
		if (strlen($idEquipe) > 0)
		{
			$myBdd = new MyBdd();
			// $categJoueur = utyCodeCategorie2($naissanceJoueur);
			if (strlen($matricJoueur) == 0)
				$matricJoueur = $myBdd->GetNextMatricLicence();
			//$idMatch, Matric, Numero, '$codeEquipe', Capitaine
			$sql  = "Replace Into gickp_Matchs_Joueurs (Id_match, Matric, Numero, Equipe, Capitaine) Values (";
			$sql .= $idMatch;
			$sql .= ",";
			$sql .= $myBdd->RealEscapeString($matricJoueur);
			$sql .= ",'";
			$sql .= $myBdd->RealEscapeString($numeroJoueur);
			$sql .= "','";
			$sql .= $codeEquipe;
			$sql .= "','";
			$sql .= $myBdd->RealEscapeString($capitaineJoueur);
			$sql .= "') ";
			$myBdd->Query($sql);
			
			$myBdd->utyJournal('Ajout joueur', '', '', 'NULL', 'NULL', 'NULL', 'Match:'.$idMatch.' - Equipe:'.$codeEquipe.' - Joueur:'.$matricJoueur);
		}
	}
	
	function DelJoueurs()
	{
		$codeEquipe = utyGetSession('codeEquipe','?');
		$idEquipe = utyGetSession('idEquipe',-1);
		$idMatch = utyGetSession('idMatch',-1);

		$myBdd = new MyBdd();

		// Contrôle verrouillage Match ...
		$sql  = "Select Validation From gickp_Matchs Where Id = ".$idMatch;
		$result = $myBdd->Query($sql);
		$row = $myBdd->FetchArray($result);
		if ($row['Validation'] == 'O')
			return;

		$sql  = "Delete From gickp_Matchs_Joueurs ";
		$sql .= "Where Id_match = $idMatch ";
		$sql .= "And Equipe = '$codeEquipe' ";
	
		$myBdd->Query($sql);

		$myBdd->utyJournal('Suppression joueurs match', '', '', '', '', $idMatch, 'Equipe : '.$idEquipe);
	}
	
	function copieCompoEquipeJournee()
	{
		$codeEquipe = utyGetSession('codeEquipe','?');
		$idEquipe = utyGetSession('idEquipe',-1);
		$idMatch = utyGetSession('idMatch',-1);
			
		$idJournee = utyGetPost('ParamCmd');

		$myBdd = new MyBdd();
			
		//Sélection des matchs de destination
		$sql  = "Select Id, Id_equipeA, Id_equipeB ";
		$sql .= "From gickp_Matchs ";
		$sql .= "Where Id_journee = ";
		$sql .= $idJournee;
		$sql .= " And Validation !=  'O' ";
		$sql .= " And Id !=  $idMatch ";
		$sql .= "And (Id_equipeA =  '";
		$sql .= $idEquipe;
		$sql .= "' or Id_equipeB =  '";
		$sql .= $idEquipe;
		$sql .= "') ";
		
		$result = $myBdd->Query($sql);
		while($row = $myBdd->FetchArray($result)) {
			($row['Id_equipeA'] == $idEquipe) ? $AB = 'A' : $AB = 'B';
			// Vidage
			$sql2  = "Delete From gickp_Matchs_Joueurs Where Id_match = ";
			$sql2 .= $row['Id'];
			$sql2 .= " And Equipe = '$AB' ";
			
			$myBdd->Query($sql2);
			
			//Selection de la compo à copier
			$sql3  = "Select Matric, Numero, Capitaine From gickp_Matchs_Joueurs ";
			$sql3 .= "Where Id_match = ";
			$sql3 .= $idMatch;
			$sql3 .= " And Equipe = '";
			$sql3 .= $codeEquipe;
			$sql3 .= "' ";
			$result3 = $myBdd->Query($sql3);
			while($row3 = $myBdd->FetchArray($result3)) {
				//Insertion ligne par ligne compo à copier
				$sql4  = "Insert Into gickp_Matchs_Joueurs (Id_match, Matric, Numero, Equipe, Capitaine) Values (";
				$sql4 .= $row['Id'];
				$sql4 .= ", ";
				$sql4 .= $row3['Matric'];
				$sql4 .= ", '";
				$sql4 .= $row3['Numero'];
				$sql4 .= "', '";
				$sql4 .= $AB;
				$sql4 .= "', '";
				$sql4 .= $row3['Capitaine'];
				$sql4 .= "') ";

				$myBdd->Query($sql4);
			}
		}

		$myBdd->utyJournal('Copie Compo sur Journée', utyGetSaison(), utyGetSession('Compet'), '', '', $idMatch, 'Equipe : '.$idEquipe);
	}

	function copieCompoEquipeCompet()
	{
		$codeEquipe = utyGetSession('codeEquipe','?');
		$idEquipe = utyGetSession('idEquipe',-1);
		$idMatch = utyGetSession('idMatch',-1);
			
		$idJournee = utyGetPost('ParamCmd');
		$list = '';

		$myBdd = new MyBdd();
		//Sélection de la compétition et des journées concernées
		$sql  = "SELECT Id ";
		$sql .= "FROM gickp_Journees ";
		$sql .= "WHERE Code_competition = ( ";
		$sql .= "SELECT Code_competition ";
		$sql .= "FROM gickp_Journees ";
		$sql .= "WHERE Id = ";
		$sql .= $idJournee;
		$sql .= ") ";
		$result = $myBdd->Query($sql);
		while($row = $myBdd->FetchArray($result)) {
			if($list != '')
				$list .= ',';
			$list .= $row['Id'];
		}
		
		//Sélection des matchs de destination
		$sql  = "Select Id, Id_equipeA, Id_equipeB ";
		$sql .= "From gickp_Matchs ";
		$sql .= "Where Id_journee In (";
		$sql .= $list;
		$sql .= ") And Validation !=  'O' ";
		$sql .= " And Id !=  $idMatch ";
		$sql .= "And (Id_equipeA =  '";
		$sql .= $idEquipe;
		$sql .= "' or Id_equipeB =  '";
		$sql .= $idEquipe;
		$sql .= "') ";
		
		$result = $myBdd->Query($sql);
		while($row = $myBdd->FetchArray($result)) {
			($row['Id_equipeA'] == $idEquipe) ? $AB = 'A' : $AB = 'B';
			// Vidage
			$sql2  = "Delete From gickp_Matchs_Joueurs Where Id_match = ";
			$sql2 .= $row['Id'];
			$sql2 .= " And Equipe = '$AB' ";
			
			$myBdd->Query($sql2);
			
			//Selection de la compo à copier
			$sql3  = "Select Matric, Numero, Capitaine From gickp_Matchs_Joueurs ";
			$sql3 .= "Where Id_match = ";
			$sql3 .= $idMatch;
			$sql3 .= " And Equipe = '";
			$sql3 .= $codeEquipe;
			$sql3 .= "' ";
			$result3 = $myBdd->Query($sql3);
			while($row3 = $myBdd->FetchArray($result3)) {
				//Insertion ligne par ligne compo à copier
				$sql4  = "Insert Into gickp_Matchs_Joueurs (Id_match, Matric, Numero, Equipe, Capitaine) Values (";
				$sql4 .= $row['Id'];
				$sql4 .= ", ";
				$sql4 .= $row3['Matric'];
				$sql4 .= ", '";
				$sql4 .= $row3['Numero'];
				$sql4 .= "', '";
				$sql4 .= $AB;
				$sql4 .= "', '";
				$sql4 .= $row3['Capitaine'];
				$sql4 .= "') ";

				$myBdd->Query($sql4);
			}
		}
				
		$myBdd->utyJournal('Copie Compo sur Compet', utyGetSaison(), utyGetSession('Compet'), '', '', $idMatch, 'Equipe : '.$idEquipe);
	}

	function Remove()
	{
		$ParamCmd = utyGetPost('ParamCmd');
		$arrayParam = explode(',', $ParamCmd);		
		if (count($arrayParam) == 0)
			return; // Rien à Detruire ...
	
		$idMatch = utyGetSession('idMatch');

		$myBdd = new MyBdd();
		// Contrôle verrouillage Match ...
		$sql  = "Select Validation From gickp_Matchs Where Id = ".$idMatch;
		$result = $myBdd->Query($sql);
		$row = $myBdd->FetchArray($result);
		if ($row['Validation'] == 'O')
			return;
			
		$sql = "Delete From gickp_Matchs_Joueurs Where Id_match = $idMatch And Matric In (";
		for ($i=0;$i<count($arrayParam);$i++)
		{
			if ($i > 0)
				$sql .= ",";
			
			$sql .= $arrayParam[$i];
		}
		$sql .= ")";
	
		$myBdd->Query($sql);
		
		$myBdd->utyJournal('Suppression joueurs match', '', '', '', '', $idMatch, 'joueurs : '.$ParamCmd);
	}
	
	function FindLicence()
	{
		$_SESSION['Signature'] = uniqid('GMEJ-');

		if (isset($_SESSION['codeClub']))
		{
			$_SESSION['codeComiteDep'] = utyCodeComiteDept($_SESSION['codeClub']);
			$_SESSION['codeComiteReg'] = utyCodeComiteReg($_SESSION['codeComiteDep']);
		}
		
		header("Location: RechercheLicence.php");	
		exit;	
	}
	
	function __construct()
	{			
	  	MyPageSecure::MyPageSecure(9);
		
		$alertMessage = '';
		
		$Cmd = utyGetPost('Cmd','');
		$ParamCmd = utyGetPost('ParamCmd','');

		if (strlen($Cmd) > 0)
		{
			if ($Cmd == 'AddJoueurTitulaire')
				($_SESSION['Profile'] <= 6 || $_SESSION['Profile'] == 9) ? $this->AddJoueurTitulaire() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'Add2')
				($_SESSION['Profile'] <= 6 || $_SESSION['Profile'] == 9) ? $this->Add2() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'DelJoueurs')
				($_SESSION['Profile'] <= 6 || $_SESSION['Profile'] == 9) ? $this->DelJoueurs() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'FindLicence')
				($_SESSION['Profile'] <= 6 || $_SESSION['Profile'] == 9) ? $this->FindLicence() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
			
			if ($Cmd == 'Remove')
				($_SESSION['Profile'] <= 6 || $_SESSION['Profile'] == 9) ? $this->Remove() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'copieCompoEquipeJournee')
				($_SESSION['Profile'] <= 6) ? $this->copieCompoEquipeJournee($ParamCmd) : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'copieCompoEquipeCompet')
				($_SESSION['Profile'] <= 4) ? $this->copieCompoEquipeCompet($ParamCmd) : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($alertMessage == '')
			{
				header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);	
				exit;
			}
		}

		$this->SetTemplate("Gestion des joueurs d'une Equipe pour un Match", "Matchs", false);
		$this->Load();
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplate('GestionMatchEquipeJoueur');
	}
}		  	

$page = new GestionMatchEquipeJoueur();
