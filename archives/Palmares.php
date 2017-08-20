<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

// Gestion des Palmares
	
class Palmares extends MyPage	 
{	
	function Load()
	{
		$myBdd = new MyBdd();
		
		$Equipe = utyGetSession('Equipe', '90');
		$Equipe = utyGetPost('Equipe', $Equipe);
		$Equipe = utyGetGet('Equipe', $Equipe);
		$this->m_tpl->assign('Equipe', $Equipe);
		$_SESSION['Equipe'] = $Equipe;
		
		$sql  = "SELECT g.id, e.Libelle Equipes, c.Libelle Competitions, c.Code, c.Code_ref, c.Code_tour, c.Code_saison Saisons, IF(c.Code_typeclt = 'CHPT', e.Clt_publi, e.CltNiveau_publi) Classt 
				FROM gickp_Competitions_Equipes e, gickp_Competitions c, gickp_Competitions_Groupes g 
				WHERE c.Code = e.Code_compet 
				AND c.Code_ref = g.Groupe 
				AND g.Id > 0 
				AND c.Code_saison = e.Code_saison 
				AND e.Numero = $Equipe 
				AND c.Publication = 'O' 
				AND c.Statut = 'END'
				ORDER BY Saisons DESC, g.id, c.Code_tour, c.Code ";
			
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load 5b <br>".$sql);
		$num_results = mysql_num_rows($result);
	
		$arrayPalmares = array();
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);
			if($row['Classt'] != 0)
				array_push($arrayPalmares, array('Code' => $row['Code'], 'Code_tour' => $row['Code_tour'], 'Code_ref' => $row['Code_ref'], 'Competitions' => $row['Competitions'], 'Saisons' => $row['Saisons'], 'Classt' => $row['Classt'] ));
			$this->m_tpl->assign('Equipe', $row['Equipes']);
		}
			
		$this->m_tpl->assign('arrayPalmares', $arrayPalmares);
	}
	

	// GestionPalmares
	function Palmares()
	{			
	  MyPage::MyPage();
		
		$this->SetTemplate("Palmares", "Palmares", true);
		$this->Load();
		//$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplate('Palmares');
	}
}		  	

$page = new Palmares();

?>
