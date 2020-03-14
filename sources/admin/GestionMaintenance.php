<?php
// TODO

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Maintenance
class GestionMaintenance extends MyPageSecure	 
{	
	function Load()
	{

		$codeSaison = utyGetSaison();
		
		$AuthSaison = utyGetSession('AuthSaison','');
		$this->m_tpl->assign('AuthSaison', $AuthSaison);

		$myBdd = new MyBdd();

		// Chargement des Saisons ...
		$sql  = "Select Code, Etat, Nat_debut, Nat_fin, Inter_debut, Inter_fin ";
		$sql .= "From gickp_Saison ";
		$sql .= "Order By Code DESC ";	 
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load");
		$num_results = mysql_num_rows($result);
	
		$arraySaison = array();
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);
			if($row['Etat'] == 'A')
				$saisonActive = $row['Code'];
			array_push($arraySaison, array('Code' => $row['Code'], 'Etat' => $row['Etat'], 
										'Nat_debut' => utyDateUsToFr($row['Nat_debut']), 'Nat_fin' => utyDateUsToFr($row['Nat_fin']), 
										'Inter_debut' => utyDateUsToFr($row['Inter_debut']), 'Inter_fin' => utyDateUsToFr($row['Inter_fin']) ));
		}
		
		$this->m_tpl->assign('arraySaison', $arraySaison);
		$this->m_tpl->assign('sessionSaison', $codeSaison);
		$this->m_tpl->assign('saisonActive', $saisonActive);
		
		// Chargement des groupes competitions
		
		$arrayGroupCompet = array();
		$sql  = "Select * ";
		$sql .= "From gickp_Competitions_Groupes ";
		$sql .= "Order by id ";
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load Groups : ".$sql);
		$num_results = mysql_num_rows($result);
		
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);	

			array_push($arrayGroupCompet, $row );
		}
		$this->m_tpl->assign('arrayGroupCompet', $arrayGroupCompet);

		//Logo uploaded
		if($codeCompet != -1)
		{
			$logoLink = $_SESSION['logoLink'];
			if($logoLink != '')
				$this->m_tpl->assign('logo', $logoLink);
			else
			{
				$logo = "../img/logo/".$codeSaison.'-'.$codeCompet.'.jpg';
				if(file_exists($logo))
					$this->m_tpl->assign('logo', $logo);
			}
			$sponsorLink = $_SESSION['sponsorLink'];
			if($sponsorLink != '')
				$this->m_tpl->assign('sponsor', $sponsorLink);
			else
			{
				$sponsor = "../img/logo/".$codeSaison.'-'.$codeCompet.'-S.jpg';
				if(file_exists($sponsor))
					$this->m_tpl->assign('sponsor', $sponsor);
			}
		}
	}
	
	function SetActiveSaison()
	{
		$codeSaison = utyGetPost('ParamCmd', '');
		if (strlen($codeSaison) == 0)
			return;

		$myBdd = new MyBdd();

		$sql  = "Update gickp_Saison Set Etat = 'I' Where Etat = 'A' ";
		mysql_query($sql, $myBdd->m_link) or die ("Erreur update saison active1");

		$sql = "Update gickp_Saison Set Etat = 'A' Where Code = '".$codeSaison."' ";
		mysql_query($sql, $myBdd->m_link) or die ("Erreur update saison active");
		
		$myBdd->utyJournal('Change Saison Active', $codeSaison);
	}

	function AddSaison()
	{
		$newSaison = utyGetPost('newSaison', '');
		$newSaisonDN = utyDateFrToUs(utyGetPost('newSaisonDN', ''));
		$newSaisonFN = utyDateFrToUs(utyGetPost('newSaisonFN', ''));
		$newSaisonDI = utyDateFrToUs(utyGetPost('newSaisonDI', ''));
		$newSaisonFI = utyDateFrToUs(utyGetPost('newSaisonFI', ''));

		if (strlen($newSaison) == 0)
			return;

		$myBdd = new MyBdd();

		$sql  = "INSERT INTO gickp_Saison (Code ,Etat ,Nat_debut ,Nat_fin ,Inter_debut ,Inter_fin) VALUES (";
		$sql .= "'".$newSaison."', ";
		$sql .= "'I', ";
		$sql .= "'".$newSaisonDN."', ";
		$sql .= "'".$newSaisonFN."', ";
		$sql .= "'".$newSaisonDI."', ";
		$sql .= "'".$newSaisonFI."') ";
		
		mysql_query($sql, $myBdd->m_link) or die ("Erreur add saison ".$sql);
		
		$myBdd->utyJournal('Ajout Saison', $newSaison);
	}
	
	function UploadLogo()
	{
		if(empty($_FILES['logo1']['tmp_name']))
			$texte = " Pas de fichier reçu - erreur ".$_FILES['logo1']['error'];
		$myBdd = new MyBdd();
		$codeSaison = utyGetSaison();
		$codeCompet = utyGetSession('codeCompet');
		$dossier = '/home/users2-new/p/poloweb/www/agil/img/logo/';
		$fichier = $codeSaison.'-'.$codeCompet.'.jpg';
		$taille_maxi = 500000;
		$taille = filesize($_FILES['logo1']['tmp_name']);
		//$erreur = $taille;
		$extensions = array('.png', '.gif', '.jpg', '.jpeg');
		$extension = strrchr($_FILES['logo1']['name'], '.'); 
		//Début des vérifications de sécurité...
		if(!in_array($extension, $extensions)) //Si l'extension n'est pas dans le tableau
		{
			 $erreur .= 'Vous devez uploader un fichier de type png, gif, jpg, jpeg, txt ou doc...';
		}
		if($taille>$taille_maxi)
		{
			$erreur .= 'Le fichier est trop gros...';
		}
		if(!isset($erreur)) //S'il n'y a pas d'erreur, on upload
		{
			if(move_uploaded_file($_FILES['logo1']['tmp_name'], $dossier . $fichier)) //Si la fonction renvoie TRUE, c'est que ça a fonctionné...
			{
				$erreur .= 'Upload effectué avec succès !';
				$logo = "../img/logo/".$fichier;
				$sql = "Update gickp_Competitions Set LogoLink = '$logo' Where Code = '$codeCompet' And Code_saison = '$codeSaison' ";
				$myBdd = new MyBdd();
				mysql_query($sql, $myBdd->m_link) or die ("Erreur Update ".$sql);
				$myBdd->utyJournal('Insertion Logo', utyGetSaison(), $codeCompet, 'NULL', 'NULL', 'NULL', '');
			}
			else //Sinon (la fonction renvoie FALSE).
			{
				$erreur .= "Echec de l\'upload ! ".$texte;
			}
		}
		else
		{
			 echo $erreur;
		}		
		return($erreur);
	}

	function DropLogo()
	{
		$myBdd = new MyBdd();
		$codeSaison = utyGetSaison();
		$codeCompet = utyGetSession('codeCompet');
		$dossier = '/home/users2-new/p/poloweb/www/agil/img/logo/';
		$fichier = $codeSaison.'-'.$codeCompet.'.jpg';
		$fichier2 = 'ex-'.$codeSaison.'-'.$codeCompet.'.jpg';
		rename($dossier.$fichier, $dossier.$fichier2);
		$myBdd->utyJournal('Suppression Logo', utyGetSaison(), $codeCompet, 'NULL', 'NULL', 'NULL', '');
		return('Logo supprimé');
	}

	function FusionJoueurs()
	{
		$myBdd = new MyBdd();
		$numFusionSource = utyGetPost('numFusionSource', 0);
		$numFusionCible = utyGetPost('numFusionCible', 0);
		$sql  = "UPDATE `gickp_Matchs_Detail` ";
		$sql .= "SET `Competiteur` = $numFusionCible "; 
		$sql .= "WHERE `Competiteur` = $numFusionSource; ";
		mysql_query($sql, $myBdd->m_link) or die ("Erreur Fusion 1 ".$sql);
		$requete = $sql;
		$sql  = "UPDATE `gickp_Matchs_Joueurs` ";
		$sql .= "SET `Matric` = $numFusionCible ";
		$sql .= "WHERE `Matric` = $numFusionSource; ";
		mysql_query($sql, $myBdd->m_link) or die ("Erreur Fusion 2 ".$sql);
		$requete .= '   '.$sql;
		$sql  = "UPDATE `gickp_Competitions_Equipes_Joueurs` ";
		$sql .= "SET `Matric` = $numFusionCible ";
		$sql .= "WHERE `Matric` = $numFusionSource; ";
		mysql_query($sql, $myBdd->m_link) or die ("Erreur Fusion 3 ".$sql);
		$requete .= '   '.$sql;
		$sql  = "DELETE FROM `gickp_Liste_Coureur` ";
		$sql .= "WHERE `Matric` = $numFusionSource; ";
		mysql_query($sql, $myBdd->m_link) or die ("Erreur Fusion 4 ".$sql);
		$requete .= '   '.$sql;
		$myBdd->utyJournal('Fusion Joueurs', utyGetSaison(), $codeCompet, 'NULL', 'NULL', 'NULL', $numFusionSource.' => '.$numFusionCible);
		return('Joueurs fusionnés : '.$requete);
	}

	function RenomEquipe()
	{
		$myBdd = new MyBdd();
		$numRenomSource = utyGetPost('numRenomSource', 0);
		$RenomSource = utyGetPost('RenomSource', 0);
		$RenomCible = utyGetPost('RenomCible', 0);
		$sql  = "UPDATE gickp_Equipe ";
		$sql .= "SET Libelle = '".$RenomCible."' "; 
		$sql .= "WHERE Numero = '".$numRenomSource."'; ";
		mysql_query($sql, $myBdd->m_link) or die ("Erreur Rename 1 ".$sql);
		$requete = $sql;
		$sql  = "UPDATE gickp_Competitions_Equipes ";
		$sql .= "SET Libelle = '".$RenomCible."' "; 
		$sql .= "WHERE Numero = '".$numRenomSource."'; ";
		mysql_query($sql, $myBdd->m_link) or die ("Erreur Rename 2 ".$sql);
		$requete .= '   '.$sql;
		$myBdd->utyJournal('Rename Equipe', utyGetSaison(), $codeCompet, 'NULL', 'NULL', 'NULL', $RenomSource.' => '.$RenomCible);
		return('Joueurs fusionnés : '.$requete);
	}

	function ChangeAuthSaison()
	{
		$AuthSaison = utyGetSession('AuthSaison');
		if($AuthSaison == 'O')
			$AuthSaison = '';
		else
			$AuthSaison = 'O';
		$_SESSION['AuthSaison'] = $AuthSaison;
	}

	function GestionMaintenance()
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
				($_SESSION['Profile'] <= 3) ? $this->Add() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'UploadLogo')
				($_SESSION['Profile'] <= 3) ? $alertMessage = $this->UploadLogo() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'DropLogo')
				($_SESSION['Profile'] <= 3) ? $alertMessage = $this->DropLogo() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'ActiveSaison')
				($_SESSION['Profile'] <= 2) ? $this->SetActiveSaison() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'AddSaison')
				($_SESSION['Profile'] <= 2) ? $this->AddSaison() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'FusionJoueurs')
				($_SESSION['Profile'] == 1) ? $this->FusionJoueurs() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'RenomEquipe')
				($_SESSION['Profile'] == 1) ? $this->RenomEquipe() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'ChangeAuthSaison')
				($_SESSION['Profile'] <= 2) ? $this->ChangeAuthSaison() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($alertMessage == '')
			{
				header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);	
				exit;
			}
		}

		$this->SetTemplate("Maintenance", "Maintenance", false);
		$this->Load();
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplate('GestionMaintenance');
	}
}		  	

$page = new GestionMaintenance();

?>
