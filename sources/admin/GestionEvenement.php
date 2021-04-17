<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion des Evenements

class GestionEvenement extends MyPageSecure	 
{	
	var $myBdd;

	function Load()
	{
		$myBdd = $this->myBdd;

		$idEvenement = (int) utyGetSession('idEvenement', -1);
		
		// Informations pour SelectionOuiNon ...
		$_SESSION['tableOuiNon'] = 'gickp_Evenement';
		$_SESSION['whereOuiNon'] = "Where Id = ";

		// Chargement des Evenements
		$arrayEvenement = array();
		
		$sql  = "SELECT Id, Libelle, Lieu, Date_debut, Date_fin, Publication 
			FROM gickp_Evenement 
			ORDER BY Date_debut DESC, Libelle DESC ";	 
	
		$arrayEvenement = array();
		foreach ($myBdd->pdo->query($sql) as $row) {
			$StdOrSelected = 'Std';
			if ($idEvenement == $row['Id'])
				$StdOrSelected = 'Selected';

			$Publication = 'O';
			if ($row['Publication'] != 'O')
				$Publication = 'N';
			
			if ($_SESSION['lang'] == 'fr') {
				$row['Date_debut'] = utyDateUsToFr($row['Date_debut']);
				$row['Date_fin'] = utyDateUsToFr($row['Date_fin']);
			}
			
			array_push($arrayEvenement, array( 
				'Id' => $row['Id'], 
				'Libelle' => $row['Libelle'],  
				'Lieu' => $row['Lieu'],  
				'Date_debut' => $row['Date_debut'], 
				'Date_fin' => $row['Date_fin'],
				'StdOrSelected' => $StdOrSelected,
				'Publication' => $Publication 
			));
		}
		$_SESSION['Libelle'] = utyGetSession('Libelle','');
		$_SESSION['Lieu'] = utyGetSession('Lieu','');
		$_SESSION['Date_debut'] = utyGetSession('Date_debut','');
		$_SESSION['Date_fin'] = utyGetSession('Date_fin','');
		$this->m_tpl->assign('arrayEvenement', $arrayEvenement);
		$this->m_tpl->assign('idEvenement', $idEvenement);
		$this->m_tpl->assign('Libelle', $_SESSION['Libelle']);
		$this->m_tpl->assign('Lieu', $_SESSION['Lieu']);
		$this->m_tpl->assign('Date_debut', $_SESSION['Date_debut']);
		$this->m_tpl->assign('Date_fin', $_SESSION['Date_fin']);
	}
	
	function Add()
	{
		$libelle = utyGetPost('Libelle');
		$lieu = utyGetPost('Lieu');
		$datedebut = utyGetPost('Date_debut');
		$datefin = utyGetPost('Date_fin');

		$myBdd = $this->myBdd;

		$sql  = "INSERT INTO gickp_Evenement (Libelle, Lieu, Date_debut, Date_fin) 
			VALUES (?,?,?,?) ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($libelle, $lieu, utyDateFrToUs($datedebut), utyDateFrToUs($datefin)));
		
		$myBdd->utyJournal('Ajout Evenement', '', '', null, null, null, $libelle);
	}
	
	function Remove()
	{
		$ParamCmd = utyGetPost('ParamCmd');
		$arrayParam = explode(',', $ParamCmd);
		if (count($arrayParam) == 0)
			return; // Rien à Detruire ...
		
		$myBdd = $this->myBdd;
		$in = str_repeat('?,', count($arrayParam) - 1) . '?';

		try {  
			$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$myBdd->pdo->beginTransaction();

			$sql = "DELETE FROM gickp_Evenement 
				WHERE Id IN ($in) ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute($arrayParam);

			$myBdd->pdo->commit();
		} catch (Exception $e) {
			$myBdd->pdo->rollBack();
			utySendMail("[KPI] Erreur SQL", "Suppression Evenements, $ParamCmd" . '\r\n' . $e->getMessage());

			return "La requête ne peut pas être exécutée !\\nCannot execute query!";
		}
		
		$myBdd->utyJournal('Suppression Evenements', '', '', $ParamCmd);
	}
	
	function PubliEvt()
	{
		$idEvt = (int) utyGetPost('ParamCmd', -1);
		(utyGetPost('Pub', '') != 'O') ? $changePub = 'O' : $changePub = 'N';
		
		$myBdd = $this->myBdd;
		$sql = "UPDATE gickp_Evenement 
			SET Publication = ? 
			WHERE Id = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($changePub, $idEvt));
		
		$myBdd->utyJournal('Publication evenement', $myBdd->GetActiveSaison(), '', $idEvt, null, null, $changePub);
	}
	
	function RazEvt()
	{
			$_SESSION['idEvenement'] = -1;
			$_SESSION['Libelle'] = '';
			$_SESSION['Lieu'] = '';
			$_SESSION['Date_debut'] = '';
			$_SESSION['Date_fin'] = '';
	}

	function ParamEvt()
	{
		$idEvenement = utyGetPost('ParamCmd', -1);
		$_SESSION['idEvenement'] = $idEvenement;
		
		$myBdd = $this->myBdd;

		$sql  = "SELECT Libelle, Lieu, Date_debut, Date_fin, Publication 
			FROM gickp_Evenement 
			WHERE Id = ? ";		
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($idEvenement));
		if ($row = $result->fetch()) {
			$_SESSION['idEvenement'] = $idEvenement;
			$_SESSION['Libelle'] = $row['Libelle'];
			$_SESSION['Lieu'] = $row['Lieu'];
			if ($_SESSION['lang'] == 'fr') {
				$row['Date_debut'] = utyDateUsToFr($row['Date_debut']);
				$row['Date_fin'] = utyDateUsToFr($row['Date_fin']);
			}
			$_SESSION['Date_debut'] = $row['Date_debut'];
			$_SESSION['Date_fin'] = $row['Date_fin'];
		}
	}

	function UpdateEvt()
	{
		$idEvenement = utyGetPost('idEvenement');
		$libelle = utyGetPost('Libelle');
		$lieu = utyGetPost('Lieu');
		$datedebut = utyGetPost('Date_debut');
		$datefin = utyGetPost('Date_fin');
		
		$myBdd = $this->myBdd;

		$sql = "UPDATE gickp_Evenement 
			SET Libelle = ?, Lieu = ?, Date_debut = ?, Date_fin = ?
			WHERE Id = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($libelle, $lieu, utyDateFrToUs($datedebut), utyDateFrToUs($datefin), $idEvenement));

		$this->RazEvt();
		$myBdd->utyJournal('Modif Evenement', '', '', $idEvenement);
	}

	function __construct()
	{
	  	MyPageSecure::MyPageSecure(2);
		
		$this->myBdd = new MyBdd();

		$alertMessage = '';
		
		$Cmd = utyGetPost('Cmd', '');

		if (strlen($Cmd) > 0)
		{
			if ($Cmd == 'Add')
				($_SESSION['Profile'] <= 2) ? $this->Add() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'Remove')
				($_SESSION['Profile'] <= 1) ? $alertMessage = $this->Remove() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'PubliEvt')
				($_SESSION['Profile'] <= 2) ? $this->PubliEvt() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'ParamEvt')
				($_SESSION['Profile'] <= 2) ? $this->ParamEvt() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'UpdateEvt')
				($_SESSION['Profile'] <= 2) ? $this->UpdateEvt() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'RazEvt')
				($_SESSION['Profile'] <= 2) ? $this->RazEvt() : $alertMessage = 'Vous n avez pas les droits pour cette action.';

				if ($alertMessage == '')
			{
				header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);	
				exit;
			}
		}

		$this->SetTemplate("Gestion_des_evenements", "Evenements", false);
		$this->Load();
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplate('GestionEvenement');
	}
}		  	

$page = new GestionEvenement();
