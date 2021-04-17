<?php
include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion des Classements Initiaux

class GestionClassementInit extends MyPageSecure	 
{	
	function Load()
	{
		$myBdd = new MyBdd();
		$codeCompet = utyGetSession('codeCompet');
		$this->m_tpl->assign('codeCompet', $codeCompet);
		
		$codeSaison = $myBdd->GetActiveSaison();

		$_SESSION['updatecell_tableName'] = 'gickp_Competitions_Equipes_Init';
		$_SESSION['updatecell_where'] = 'Where Id = ';
		$_SESSION['updatecell_document'] = 'formClassementInit';
		
		
		$sql = "SELECT Id 
			FROM gickp_Competitions_Equipes 
			WHERE Code_compet = ? 
			AND Code_saison = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($codeCompet, $codeSaison));
		while ($row = $result->fetch()) {
			$sql2 = "SELECT Id 
				FROM gickp_Competitions_Equipes_Init 
				WHERE Id = ? ";
			$result2 = $myBdd->pdo->prepare($sql2);
			$result2->execute(array($row['Id']));
			if ($result2->rowCount() == 0) {
				// Insertion
				$sql3 = "INSERT INTO gickp_Competitions_Equipes_Init (Id,Clt,Pts,J,G,N,P,F,Plus,Moins,Diff) 
					VALUES (?, 0,0,0,0,0,0,0,0,0,0) ";
				$result3 = $myBdd->pdo->prepare($sql3);
				$result3->execute(array($row['Id']));
			}
	  	}
	  
	 	// Chargement des Equipes avec leurs valeurs initiales ...
		$sql = "SELECT a.Id, a.Libelle, a.Code_club, b.Clt, b.Pts, b.J, b.G, b.N, 
			b.P, b.F, b.Plus, b.Moins, b.Diff 
			FROM gickp_Competitions_Equipes a, gickp_Competitions_Equipes_Init b 
			WHERE a.Id = b.Id 
			AND a.Code_compet = ? 
			AND a.Code_saison = ? 
			ORDER BY b.Clt, b.Pts, b.Diff DESC ";
		$arrayEquipe = array();
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($codeCompet, $codeSaison));
		while ($row = $result->fetch()) {
			array_push($arrayEquipe, array( 'Id' => $row['Id'], 'Libelle' => $row['Libelle'], 
				'Code_club' => $row['Code_club'],
				'Clt' => $row['Clt'], 'Pts' => $row['Pts'], 
				'J' => $row['J'], 'G' => $row['G'], 'N' => $row['N'], 
				'P' => $row['P'], 'F' => $row['F'], 'Plus' => $row['Plus'], 
				'Moins' => $row['Moins'], 'Diff' => $row['Diff'] ));
		}	
		$this->m_tpl->assign('arrayEquipe', $arrayEquipe);
	}
		
	function __construct()
	{			
		MyPageSecure::MyPageSecure(4);
		
		$this->SetTemplate("Classement_initial", "Classements", false);
		$this->Load();
		$this->DisplayTemplate('GestionClassementInit');
	}
}		  	

$page = new GestionClassementInit();
