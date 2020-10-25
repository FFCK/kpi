<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion des Evenements

class GestionGroupe extends MyPageSecure	 
{	
	function Load()
	{
		$idGroupe = (int) utyGetSession('idGroupe', -1);
		
		// Chargement des Groupes
		$myBdd = new MyBdd();
		$arrayGroupes = array();
		
		$sql = "SELECT * 
			FROM gickp_Competitions_Groupes 
			ORDER BY section, ordre ";	 
		$arrayGroupes = array();
		$result = $myBdd->pdo->prepare($sql);
		$result->execute();
		while ($row = $result->fetch()) {
			if ($idGroupe == $row['id']) {
                $row['selected'] = 'selected';
                $groupe = $row;
            } else {
                $row['selected'] = '';
            }
			array_push($arrayGroupes, $row);
		}
		
		$this->m_tpl->assign('groupe', $groupe);
		$this->m_tpl->assign('arrayGroupes', $arrayGroupes);
	}
	
	function Add()
	{
		$libelle = utyGetPost('Libelle');
		$section = utyGetPost('section');
		$ordre = utyGetPost('ordre');
		$Code_niveau = utyGetPost('Code_niveau');
		$Groupe = utyGetPost('Groupe');
		
		$myBdd = new MyBdd();

		$sql = "INSERT INTO gickp_Competitions_Groupes 
			SET Libelle = ?, section = ?, ordre = ?, Code_niveau = ?, Groupe = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array(
			$libelle, $section, $ordre, $Code_niveau, $Groupe
		));
		
		$this->Raz();
		$myBdd->utyJournal('Ahout Groupe', '', '', $Groupe);
        return "Ajout effectué.";
	}
	
	function Remove($idGroupe)
	{
        $myBdd = new MyBdd();
		$sql = "SELECT c.Code_saison, c.Code 
			FROM gickp_Competitions c, gickp_Competitions_Groupes g 
			WHERE c.Code_ref = g.Groupe 
			AND g.id = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($idGroupe));
        $num_results = $result->rowCount();
        
        if ($num_results > 0) {
            $conflict = '';
            while ($row = $result->fetch()){ 
                $conflict .= ' ' . $row['Code_saison'] . '-' . $row['Code'];
            }
            return "Il existe des compétitions dans ce groupe :$conflict. Suppression impossible !";
        }
        
		$sql = "DELETE FROM gickp_Competitions_Groupes 
			WHERE id = $idGroupe ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($idGroupe));
        return "Suppression effectuée.";
    }
	
	function Edit($idGroupe)
	{
        $_SESSION['idGroupe'] = $idGroupe;
	}

	function Raz()
	{
        $_SESSION['idGroupe'] = -1;
	}

	function Update()
	{
		$idGroupe = utyGetPost('idGroupe');
		$libelle = utyGetPost('Libelle');
		$section = utyGetPost('section');
		$ordre = utyGetPost('ordre');
		$Code_niveau = utyGetPost('Code_niveau');
		$Groupe = utyGetPost('Groupe');
		
		$myBdd = new MyBdd();

		$sql = "UPDATE gickp_Competitions_Groupes 
			SET Libelle = ?, section = ?, ordre = ?, Code_niveau = ?, Groupe = ? 
			WHERE Id = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array(
			$libelle, $section, $ordre, $Code_niveau, $Groupe, $idGroupe
		));

		$this->Raz();
		$myBdd->utyJournal('Modif Groupe', '', '', $idGroupe);
        return "Mise à jour effectuée.";
	}

	function __construct()
	{			
        MyPageSecure::MyPageSecure(1);
		
		$alertMessage = '';
		
		$Cmd = utyGetPost('Cmd', '');

		$ParamCmd = utyGetPost('ParamCmd', '');

		if (strlen($Cmd) > 0)
		{
			if ($Cmd == 'Add')
				($_SESSION['Profile'] <= 2) ? $alertMessage = $this->Add() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'Remove')
				($_SESSION['Profile'] <= 1) ? $alertMessage = $this->Remove($ParamCmd) : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'Edit')
				($_SESSION['Profile'] <= 2) ? $this->Edit($ParamCmd) : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'Update')
				($_SESSION['Profile'] <= 2) ? $alertMessage = $this->Update() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'Raz')
				($_SESSION['Profile'] <= 2) ? $this->Raz() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($alertMessage == '')
			{
				header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);	
				exit;
			}
		}

		$this->SetTemplate("Gestion_des_groupes", "Competitions", false);
		$this->Load();
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplate('GestionGroupe');
	}
}		  	

$page = new GestionGroupe();
