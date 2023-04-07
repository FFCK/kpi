<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion des instances

class GestionInstances extends MyPageSecure	 
{	
	function Load()
	{
		$myBdd = new MyBdd();
		$idJournee = utyGetGet('idJournee', 0);
		$idJournee = utyGetPost('idJournee', $idJournee);
		$codeSaison = $myBdd->GetActiveSaison();
				
		//Chargement infos journées
		$sql = "SELECT j.Id, j.Code_competition, j.Type, j.Phase, j.Niveau, j.Date_debut, 
			j.Date_fin, j.Nom, j.Libelle, j.Lieu, j.Plan_eau, j.Departement, j.Responsable_insc, 
			j.Responsable_R1, j.Organisateur, j.Delegue, j.ChefArbitre, 
			j.Rep_athletes, j.Arb_nj1, j.Arb_nj2, j.Arb_nj3, j.Arb_nj4, j.Arb_nj5, j.Publication
			FROM kp_journee j
			WHERE Id = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($idJournee));
		$row = $result->fetch();
		$this->m_tpl->assign('arrayJournee', $row);
		$codecompet = $row['Code_competition'];

		$bAutorisation = utyIsAutorisationJournee($idJournee);
		$this->m_tpl->assign('bAutorisation', $bAutorisation);

		// RC disponibles
		$arrayRC = array();
		$sql = "SELECT rc.Matric, rc.Ordre, lc.Nom, lc.Prenom 
			FROM kp_rc rc 
			LEFT OUTER JOIN kp_licence lc 
				ON (rc.Matric = lc.Matric) 
			WHERE rc.Code_Competition = ?
			AND rc.Code_saison = ? 
			ORDER BY rc.Ordre";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($codecompet, $codeSaison));
		$arrayRC = $result->fetchAll(PDO::FETCH_ASSOC);
		$this->m_tpl->assign('arrayRC', $arrayRC);

	}
	

	function Fonction()
	{
		$ParamCmd = utyGetPost('ParamCmd');
	}
	
	
	function __construct()
	{			
		parent::__construct(8);
		
		$alertMessage = '';

		$Cmd = utyGetPost('Cmd');
		
		if (strlen($Cmd) > 0) {
			if ($Cmd == 'Fonction')
				($_SESSION['Profile'] <= 1) ? $this->Fonction() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($alertMessage == '') {
				header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);	
				exit;
			}
		}
		
		$this->SetTemplate("Instances_de_la_journee", "Journees_phases", false);
		$this->Load();
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplate('GestionInstances');
	}
}		  	

$page = new GestionInstances();
