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
		
		$codeSaison = utyGetSaison();
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
		$sql  = "Select Code, Etat, Nat_debut, Nat_fin, Inter_debut, Inter_fin ";
		$sql .= "From gickp_Saison ";
		$sql .= "Order By Code DESC ";	 
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load 1");
		$num_results = mysql_num_rows($result);
		$arraySaison = array();
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);	
			array_push($arraySaison, array('Code' => $row['Code'], 'Etat' => $row['Etat'], 
										'Nat_debut' => utyDateUsToFr($row['Nat_debut']), 'Nat_fin' => utyDateUsToFr($row['Nat_fin']), 
										'Inter_debut' => utyDateUsToFr($row['Inter_debut']), 'Inter_fin' => utyDateUsToFr($row['Inter_fin']) ));
		}
		
		$this->m_tpl->assign('arraySaison', $arraySaison);
		$this->m_tpl->assign('sessionSaison', $codeSaison);

		// Chargement des Evenements ...
		$idEvenement = utyGetSession('idEvenement', -1);
		$idEvenement = utyGetPost('evenement', $idEvenement);
		$sql  = "Select Id, Libelle, Date_debut, Publication ";
		$sql .= "From gickp_Evenement ";
		$sql .= "Order By Date_debut DESC, Libelle ";	 
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load");
		$num_results = mysql_num_rows($result);
		$arrayEvenement = array();
		if (-1 == $idEvenement)
			array_push($arrayEvenement, array( 'Id' => -1, 'Libelle' => 'Sélectionnez l\'événement', 'Selection' => 'SELECTED' ) );
		else
			array_push($arrayEvenement, array( 'Id' => -1, 'Libelle' => 'Sélectionnez l\'événement', 'Selection' => '' ) );
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);	  
			if ($row["Id"] == $idEvenement)
				array_push($arrayEvenement, array( 'Id' => $row['Id'], 'Libelle' => $row['Id'].' - '.$row['Libelle'], 'Selection' => 'SELECTED' ) );
			else
				array_push($arrayEvenement, array( 'Id' => $row['Id'], 'Libelle' => $row['Id'].' - '.$row['Libelle'], 'Selection' => '' ) );
		}
		$this->m_tpl->assign('arrayEvenement', $arrayEvenement);

		// Chargement des Compétitions ...
		$sql  = "Select c.Code_niveau, c.Code_ref, c.Code_tour, c.Code, c.Libelle, c.Soustitre, c.Soustitre2, c.Titre_actif, "
                . "g.section, g.ordre "
                . "From gickp_Competitions c, gickp_Competitions_Groupes g "
                . "Where c.Code_saison = '"
                . $codeSaison
                . "' "
                . utyGetFiltreCompetition('c.')
                . " And c.Code_niveau Like '".utyGetSession('AfficheNiveau')."%' ";
		if ($AfficheCompet == 'N') {
            $sql .= " And c.Code Like 'N%' ";
        } elseif ($AfficheCompet == 'CF') {
            $sql .= " And c.Code Like 'CF%' ";
        } elseif ($AfficheCompet == 'M') {
            $sql .= " And c.Code_ref = 'M' ";
        } elseif($AfficheCompet > 0) {
            $sql .= " And g.section = '" . $AfficheCompet . "' ";
        }
        $sql .= " And c.Code_ref = g.Groupe ";
		$sql .= " Order By c.Code_saison, g.section, g.ordre, COALESCE(c.Code_ref, 'z'), c.Code_tour, c.GroupOrder, c.Code";	 
		$result = $myBdd->Query($sql);
		$arrayCompetition = array();
        $i = -1;
        $j = '';
        $label = $myBdd->getSections();
		while ($row = $myBdd->FetchArray($result)) {
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
		$sql  = "Select count(Id) nbEquipes ";
		$sql .= "From gickp_Competitions_Equipes ";
		$sql .= "Where Code_saison = '".$codeSaison."' ";
		$sql .= "And Code_compet = '".$codeCompet."' "; 
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load 3<br>".$sql);
		$num_results = mysql_num_rows($result);
		$nbEquipes = 'X';
		if($num_results == 1)
		{
			$row = mysql_fetch_array($result);
			$nbEquipes = $row['nbEquipes'];
		}
		$this->m_tpl->assign('nbEquipes', $nbEquipes);
		
		//Journees/Phases
		$sql  = "Select j.* ";
		$sql .= "From gickp_Journees j ";
		$sql .= "Where j.Code_saison = '".$codeSaison."' ";
		$sql .= "And j.Code_competition = '".$codeCompet."' "; 
		$sql .= "Order By j.Niveau, j.Phase, j.Date_debut, j.Lieu ";
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load 4<br>".$sql);
		$num_results = mysql_num_rows($result);
		$listJournees = '';
		$nbJourneesPubli = 0;
		$arrayJournees = array();
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);
			if($row['Publication'] == 'O')
				$nbJourneesPubli ++;
			if($listJournees != '')
				$listJournees .= ',';
			$listJournees .= $row['Id'];
			$row['Date_debut'] = utyDateUsToFr($row['Date_debut']);
			$row['Date_fin'] = utyDateUsToFr($row['Date_fin']);
			array_push($arrayJournees, array( 'Niveau' => $row['Niveau'], 'Phase' => $row['Phase'], 'Date_debut' => $row['Date_debut'], 'Date_fin' => $row['Date_fin'], 'Lieu' => $row['Lieu'] ));
		}
		$this->m_tpl->assign('nbJournees', $num_results);
		$this->m_tpl->assign('nbJourneesPubli', $nbJourneesPubli);
		$this->m_tpl->assign('listJournees', $listJournees);
		$this->m_tpl->assign('arrayJournees', $arrayJournees);

		//Matchs
		$sql  = "Select m.Id, m.Numero_ordre, m.Validation, m.Publication ";
		$sql .= "From gickp_Journees j, gickp_Matchs m ";
		$sql .= "Where j.Code_saison = '".$codeSaison."' ";
		$sql .= "And j.Code_competition = '".$codeCompet."' "; 
		$sql .= "And j.Id = m.Id_journee ";
		$sql .= "Order By m.Numero_ordre ";
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load 5<br>".$sql);
		$num_results = mysql_num_rows($result);
		$listMatchs = '';
		$nbMatchsValid = 0;
		$nbMatchsPubli = 0;
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);
			if($row['Publication'] == 'O')
				$nbMatchsPubli ++;
			if($row['Validation'] == 'O')
				$nbMatchsValid ++;
			if($listMatchs != '')
				$listMatchs .= ',';
			$listMatchs .= $row['Id'];
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
	function GestionDoc()
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

