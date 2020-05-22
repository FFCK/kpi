<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion des Classements

class GestionDoc extends MyPageSecure	 
{	
	function Load()
	{
		$myBdd = new MyBdd();
		
		$codeSaison = $myBdd->GetActiveSaison();
		$codeSaison = utyGetPost('saisonTravail', $codeSaison);
        $codeSaison = utyGetGet('Saison', $codeSaison);
		$_SESSION['Saison'] = $codeSaison;
		
		$saisonActive = $myBdd->GetActiveSaison();
		
		$codeCompet = utyGetSession('codeCompet');
		$codeCompet = utyGetPost('codeCompet', $codeCompet);
		$codeCompet = utyGetGet('Compet', $codeCompet);

        //Filtre affichage type compet
		$AfficheCompet = utyGetSession('AfficheCompet','');
		$AfficheCompet = utyGetPost('AfficheCompet', $AfficheCompet);
        $_SESSION['AfficheCompet'] = $AfficheCompet;
		$this->m_tpl->assign('AfficheCompet', $AfficheCompet);

		// Chargement des Saisons ...
		$sql  = "SELECT Code, Etat, Nat_debut, Nat_fin, Inter_debut, Inter_fin 
			FROM gickp_Saison 
			ORDER BY Code DESC ";	 
		$arraySaison = array();
        foreach ($myBdd->pdo->query($sql) as $row) { 
			array_push($arraySaison, array('Code' => $row['Code'], 'Etat' => $row['Etat'], 
				'Nat_debut' => utyDateUsToFr($row['Nat_debut']), 'Nat_fin' => utyDateUsToFr($row['Nat_fin']), 
				'Inter_debut' => utyDateUsToFr($row['Inter_debut']), 'Inter_fin' => utyDateUsToFr($row['Inter_fin']) ));
		}
		
		$this->m_tpl->assign('arraySaison', $arraySaison);
		$this->m_tpl->assign('sessionSaison', $codeSaison);

		// Chargement des Evenements ...
		$idEvenement = utyGetSession('idEvenement', -1);
		$idEvenement = utyGetPost('evenement', $idEvenement);
		$arrayEvenement = array();
		if (-1 == $idEvenement) {
			array_push($arrayEvenement, array( 'Id' => -1, 'Libelle' => 'Sélectionnez l\'événement', 'Selection' => 'SELECTED' ) );
		} else {
			array_push($arrayEvenement, array( 'Id' => -1, 'Libelle' => 'Sélectionnez l\'événement', 'Selection' => '' ) );
		}

		$sql = "SELECT Id, Libelle, Date_debut, Publication 
			FROM gickp_Evenement 
			ORDER BY Date_debut DESC, Libelle ";
		foreach ($myBdd->pdo->query($sql) as $row) { 
			if ($row["Id"] == $idEvenement) {
				array_push($arrayEvenement, array( 'Id' => $row['Id'], 'Libelle' => $row['Id'].' - '.$row['Libelle'], 'Selection' => 'SELECTED' ) );
			} else {
				array_push($arrayEvenement, array( 'Id' => $row['Id'], 'Libelle' => $row['Id'].' - '.$row['Libelle'], 'Selection' => '' ) );
			}
		}
		$this->m_tpl->assign('arrayEvenement', $arrayEvenement);

		// Chargement des Compétitions ...
		$arrayCompetition = array();
		$i = -1;
		$j = '';
		$label = $myBdd->getSections();
		$sqlFiltreCompetition = utyGetFiltreCompetition('c.');
		$sqlAfficheCompet = '';
		$arrayAfficheCompet = [];
		if ($AfficheCompet == 'N') {
			$sqlAfficheCompet = " AND c.Code LIKE 'N%' ";
		} elseif ($AfficheCompet == 'CF') {
			$sqlAfficheCompet = " AND c.Code LIKE 'CF%' ";
		} elseif ($AfficheCompet == 'M') {
			$sqlAfficheCompet = " AND c.Code_ref = 'M' ";
		} elseif ($AfficheCompet > 0) {
			$sqlAfficheCompet = " AND g.section = ? ";
			$arrayAfficheCompet = [$AfficheCompet];
		}
		$sql = "SELECT c.Code_niveau, c.Code_ref, c.Code_tour, c.Code, c.Libelle, 
			c.Soustitre, c.Soustitre2, c.Titre_actif, g.section, g.ordre 
			FROM gickp_Competitions c, gickp_Competitions_Groupes g 
			WHERE c.Code_saison = ?  
			$sqlFiltreCompetition 
			AND c.Code_niveau LIKE ? 
			AND c.Code_ref = g.Groupe 
			$sqlAfficheCompet 
			ORDER BY c.Code_saison, g.section, g.ordre, 
				COALESCE(c.Code_ref, 'z'), c.Code_tour, c.GroupOrder, c.Code";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array_merge(
			[$codeSaison], 
			[utyGetSession('AfficheNiveau').'%'], 
			$arrayAfficheCompet
		));
		while ($row = $result->fetch()) { 
			// Titre
			if ($row["Titre_actif"] != 'O' && $row["Soustitre"] != '') {
                $Libelle = $row["Soustitre"];
            } else {
                $Libelle = $row["Libelle"];
            }
            if ($row["Soustitre2"] != '') {
                $Libelle .= ' - ' . $row["Soustitre2"];
            }

            if ((strlen($codeCompet) == 0) && ($i == 0)) {
                $codeCompet = $row["Code"];
            }
            if($j != $row['section']) {
                $i ++;
                $arrayCompetition[$i]['label'] = $label[$row['section']];
            }
            if($row["Code"] == $codeCompet) {
                $row['selected'] = 'selected';
            } else {
                $row['selected'] = '';
            }
            $j = $row['section'];
            $arrayCompetition[$i]['options'][] = $row;
            
        }
		$this->m_tpl->assign('arrayCompetition', $arrayCompetition);
		$_SESSION['codeCompet'] = $codeCompet;
		$this->m_tpl->assign('codeCompet', $codeCompet);
		
		//Détails Compet
        $detailsCompet = $myBdd->GetCompetition($codeCompet, $codeSaison);
        if( $detailsCompet['BandeauLink'] != '' && strpos($detailsCompet['BandeauLink'], 'http') === FALSE ) {
            $detailsCompet['BandeauLink'] = '../img/logo/' . $detailsCompet['BandeauLink'];
        }
        if( $detailsCompet['LogoLink'] != '' && strpos($detailsCompet['LogoLink'], 'http') === FALSE ) {
            $detailsCompet['LogoLink'] = '../img/logo/' . $detailsCompet['LogoLink'];
        }
        if( $detailsCompet['SponsorLink'] != '' && strpos($detailsCompet['SponsorLink'], 'http') === FALSE ) {
            $detailsCompet['SponsorLink'] = '../img/logo/' . $detailsCompet['SponsorLink'];
        }
		$this->m_tpl->assign('detailsCompet', $detailsCompet);
		
		//Equipes
		$nbEquipes = 'X';
		$sql = "SELECT COUNT(Id) nbEquipes 
			FROM gickp_Competitions_Equipes 
			WHERE Code_saison = ? 
			AND Code_compet = ? "; 
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($codeSaison, $codeCompet));
		if ($row = $result->fetch()) {
			$nbEquipes = $row['nbEquipes'];
		}
		$this->m_tpl->assign('nbEquipes', $nbEquipes);
		
		//Journees/Phases
		$listJournees = '';
		$nbJourneesPubli = 0;
		$num_results = 0;
		$arrayJournees = array();
		$sql = "SELECT j.* 
			FROM gickp_Journees j 
			WHERE j.Code_saison = :Code_saison 
			AND j.Code_competition = :Code_competition 
			ORDER BY j.Niveau, j.Phase, j.Date_debut, j.Lieu ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array(
			':Code_competition' => $codeCompet,
			':Code_saison' => $codeSaison
		));
		while ($row = $result->fetch()) {
			if($row['Publication'] == 'O')
				$nbJourneesPubli ++;
			if($listJournees != '')
				$listJournees .= ',';
			$listJournees .= $row['Id'];
			$row['Date_debut'] = utyDateUsToFr($row['Date_debut']);
			$row['Date_fin'] = utyDateUsToFr($row['Date_fin']);
			array_push($arrayJournees, array( 'Niveau' => $row['Niveau'], 'Phase' => $row['Phase'], 'Date_debut' => $row['Date_debut'], 'Date_fin' => $row['Date_fin'], 'Lieu' => $row['Lieu'] ));
			$num_results ++;
		}
		$this->m_tpl->assign('nbJournees', $num_results);
		$this->m_tpl->assign('nbJourneesPubli', $nbJourneesPubli);
		$this->m_tpl->assign('listJournees', $listJournees);
		$this->m_tpl->assign('arrayJournees', $arrayJournees);

		//Matchs
		$num_results = 0;
		$listMatchs = '';
		$nbMatchsValid = 0;
		$nbMatchsPubli = 0;
		$sql = "SELECT m.Id, m.Numero_ordre, m.Validation, m.Publication 
			FROM gickp_Journees j, gickp_Matchs m 
			WHERE j.Code_saison = '".$codeSaison."' 
			AND j.Code_competition = '".$codeCompet."' 
			AND j.Id = m.Id_journee 
			ORDER BY m.Numero_ordre ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array(
			':Code_competition' => $codeCompet,
			':Code_saison' => $codeSaison
		));
		while ($row = $result->fetch()) {
			if($row['Publication'] == 'O')
				$nbMatchsPubli ++;
			if($row['Validation'] == 'O')
				$nbMatchsValid ++;
			if($listMatchs != '')
				$listMatchs .= ',';
			$listMatchs .= $row['Id'];
			$num_results ++;
		}
		$this->m_tpl->assign('nbMatchs', $num_results);
		$this->m_tpl->assign('nbMatchsValid', $nbMatchsValid);
		$this->m_tpl->assign('nbMatchsPubli', $nbMatchsPubli);
		$this->m_tpl->assign('listMatchs', $listMatchs);
	}
	
	function SetSessionSaison()
	{
		$codeSaison = utyGetPost('ParamCmd', '');
		if (strlen($codeSaison) == 0)
			return;
		
		$_SESSION['Saison'] = $codeSaison;
	}

	// GestionDoc 		
	function __construct()
	{
	  	MyPageSecure::MyPageSecure(10);
		
		$alertMessage = '';

		$Cmd = utyGetPost('Cmd');
		if (strlen($Cmd) > 0)
		{
			if ($Cmd == 'SessionSaison')
				($_SESSION['Profile'] <= 10) ? $this->SetSessionSaison() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($alertMessage == '')
			{
				header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);	
				exit;
			}
		}

		$this->SetTemplate("Gestion_docs", "Docs", false);
		$this->Load();
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplate('GestionDoc');
	}
}		  	

$page = new GestionDoc();

