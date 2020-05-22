<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion du Calendrier

class GestionCalendrier extends MyPageSecure	 
{	
	var $myBdd;

	function Load()
	{
		$myBdd = $this->myBdd;
		
        // Langue
        $langue = parse_ini_file("../commun/MyLang.ini", true);
        if (utyGetSession('lang') == 'en') {
            $lang = $langue['en'];
        } else {
            $lang = $langue['fr'];
        }
        
        //Filtre mois
		if (isset($_POST['filtreMois'])) {
            $_SESSION['filtreMois'] = $_POST['filtreMois'];
        } else {
            $_SESSION['filtreMois'] = '';
        }
        $filtreMois = $_SESSION['filtreMois'];
		$this->m_tpl->assign('filtreMois', $_SESSION['filtreMois']);

		$codeSaison = $myBdd->GetActiveSaison();

		// Chargement des Evenements ...
		$idEvenement = utyGetSession('idEvenement', -1);
		$idEvenement = utyGetPost('evenement', $idEvenement);

		//Filtre affichage type compet
		$AfficheCompet = utyGetSession('AfficheCompet','');
		$AfficheCompet = utyGetPost('AfficheCompet', $AfficheCompet);
        $_SESSION['AfficheCompet'] = $AfficheCompet;
		$this->m_tpl->assign('AfficheCompet', $AfficheCompet);

        $_SESSION['idEvenement'] = $idEvenement;
		$this->m_tpl->assign('idEvenement', $idEvenement);
		
		$sql = "SELECT Id, Libelle, Date_debut 
			FROM gickp_Evenement 
			ORDER BY Date_debut DESC, Libelle ";	 
		$result = $myBdd->pdo->prepare($sql);
		$result->execute();

		$arrayEvenement = array();
		if (-1 == $idEvenement) {
            $selected1 = 'SELECTED';
        } else {
            $selected1 = '';
        }
        if (utyGetSession('lang') == 'en') {
            array_push($arrayEvenement, array('Id' => -1, 'Libelle' => '* - All events', 'Selection' => $selected1));
        } else {
            array_push($arrayEvenement, array('Id' => -1, 'Libelle' => '* - Tous les événements', 'Selection' => ''));
        }

		while ($row = $result->fetch()) {
            $PublicEvt = '';

            if ($row["Id"] == $idEvenement) {
                array_push($arrayEvenement, array('Id' => $row['Id'], 'Libelle' => $row['Id'] . ' - ' . $row['Libelle'] . $PublicEvt, 'Selection' => 'SELECTED'));
            } else {
                array_push($arrayEvenement, array('Id' => $row['Id'], 'Libelle' => $row['Id'] . ' - ' . $row['Libelle'] . $PublicEvt, 'Selection' => ''));
            }
        }
		$this->m_tpl->assign('arrayEvenement', $arrayEvenement);

		// Mode Evenement 
		$modeEvenement = utyGetSession('modeEvenement', '1');
		$modeEvenement = utyGetPost('choixModeEvenement', $modeEvenement);
		$_SESSION['modeEvenement'] = $modeEvenement;
		$this->m_tpl->assign('modeEvenement', $modeEvenement);
				
		// Chargement des Compétitions ...
		$codeCompet = utyGetSession('codeCompet', '*');
		// si changement de compétition, RAZ journée sélectionnée
		if (isset($_POST['codeCompet'])) {	// @COSANDCO_WAMPSER
			if ($codeCompet != utyGetPost('codeCompet')) {
                $_SESSION['idSelJournee'] = '*';
            }
        }
		$codeCompet = utyGetPost('competition', $codeCompet);
		$_SESSION['codeCompet'] = $codeCompet;
		
		
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
			$arrayAfficheCompet = array_merge($arrayAfficheCompet, [$AfficheCompet]);
		}
		// Mode Filtrage => La Combo Competition est chargée avec uniquement les compétitions de l'Evenement ...
		if ( ($modeEvenement == 1) && ($idEvenement != -1) ) {
			$sqlAfficheCompet = " AND ej.Id_evenement  = ? ";
			$arrayAfficheCompet = array_merge($arrayAfficheCompet, [$idEvenement]);
		}
		$sql = "SELECT DISTINCT c.*, g.section, g.ordre, g.id 
			FROM gickp_Competitions c, gickp_Competitions_Groupes g,
			gickp_Journees j 
			LEFT OUTER JOIN gickp_Evenement_Journees ej ON (j.Id = ej.Id_journee) 
			WHERE c.Code_saison = j.Code_saison
			AND c.Code = j.Code_competition
			AND c.Code_saison = ?  
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
		
		$arrayCompetition = array();
        
		if (-1 != $idEvenement) {
			if('*' == $codeCompet) {
				$selected = 'selected';
            } else {
				$selected = '';
            }
            if (utyGetSession('lang') == 'en') {
				$arrayCompetition[0]['label'] = "All competitions";
                $arrayCompetition[0]['options'][] = array('Code' => '*', 'Libelle' => 'All competitions', 'selected' => $selected );
            } else {
				$arrayCompetition[0]['label'] = "Toutes les compétitions";
                $arrayCompetition[0]['options'][] = array('Code' => '*', 'Libelle' => 'Toutes les compétitions', 'selected' => $selected );
            }
			
            $i = 0;
        } else {
			$i = -1;
        }
        
		$listeCompet = "('";
		$arrayCompets = [];
        $j = '';
        $label = $myBdd->getSections();
		while ($row = $result->fetch()) {
			// Titre
			if ($row["Titre_actif"] != 'O' && $row["Soustitre"] != '') {
				$Libelle = $row["Soustitre"];
			} else {
				$Libelle = $row["Libelle"];
			}
			if ($row["Soustitre2"] != '') {
				$Libelle .= ' - '.$row["Soustitre2"];
			}

			$listeCompet .= $row["Code"]."','";
			$arrayCompets[] = $row["Code"];
			
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
		
		$listeCompet .= "')";

		// Les différents tris de compétition ...
		$orderCompet = utyGetSession('orderCompet', 'Date_debut, Niveau, Phase, Lieu, Libelle, Id');
		$orderCompet = utyGetPost('competitionOrder', $orderCompet);
		$_SESSION['orderCompet'] = $orderCompet;
			
		$arrayCompetitionOrder = array();
		if ("Date_debut, Niveau, Phase, Lieu, Libelle" == $orderCompet)
			array_push($arrayCompetitionOrder, array('Code' => 'Date_debut, Niveau, Phase, Lieu, Libelle, Id', 'Libelle' => $lang['Par_date_croissante'], 'Selection' => 'SELECTED' ) );
		else
			array_push($arrayCompetitionOrder, array('Code' => 'Date_debut, Niveau, Phase, Lieu, Libelle, Id', 'Libelle' => $lang['Par_date_croissante'], 'Selection' => '' ) );
			
		if ("Date_debut Desc, Niveau, Phase, Lieu, Libelle" == $orderCompet)
			array_push($arrayCompetitionOrder, array('Code' => 'Date_debut Desc, Niveau, Phase, Lieu, Libelle', 'Libelle' => $lang['Par_date_decroissante'], 'Selection' => 'SELECTED' ) );
		else
			array_push($arrayCompetitionOrder, array('Code' => 'Date_debut Desc, Niveau, Phase, Lieu, Libelle', 'Libelle' => $lang['Par_date_decroissante'], 'Selection' => '' ) );
			
		if ("Libelle, Niveau, Phase" == $orderCompet)
			array_push($arrayCompetitionOrder, array('Code' => 'Libelle, Niveau, Phase', 'Libelle' => $lang['Par_Nom'], 'Selection' => 'SELECTED' ) );
		else
			array_push($arrayCompetitionOrder, array('Code' => 'Libelle, Niveau, Phase', 'Libelle' => $lang['Par_Nom'], 'Selection' => '' ) );
			
		if ("Id, Niveau, Phase" == $orderCompet)
			array_push($arrayCompetitionOrder, array('Code' => 'Id, Niveau, Phase', 'Libelle' => $lang['Par_Numero'], 'Selection' => 'SELECTED' ) );
		else
			array_push($arrayCompetitionOrder, array('Code' => 'Id, Niveau, Phase', 'Libelle' => $lang['Par_Numero'], 'Selection' => '' ) );
			
		if ("Niveau, Phase, Date_debut" == $orderCompet)
			array_push($arrayCompetitionOrder, array('Code' => 'Niveau, Phase, Date_debut', 'Libelle' => $lang['Par_Niveau'], 'Selection' => 'SELECTED' ) );
		else
			array_push($arrayCompetitionOrder, array('Code' => 'Niveau, Phase, Date_debut', 'Libelle' => $lang['Par_Niveau'], 'Selection' => '' ) );

		$this->m_tpl->assign('arrayCompetitionOrder', $arrayCompetitionOrder);
		
	
		$arrayEvenementJournees = array();
		if ($modeEvenement == '2' && $idEvenement != -1) {
			// Mode Association ... => Chargement des Journées de l'Evenement ...
			$sql = "SELECT Id_journee 
				FROM gickp_Evenement_Journees 
				WHERE Id_evenement = ? "; 
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($idEvenement));
            while($row = $result->fetch()) {
				array_push($arrayEvenementJournees, $row['Id_journee']);
			}
		}
	
		// Chargement des Journees ...
		$arrayJournees = array();
		$in = str_repeat('?,', count($arrayCompets) - 1) . '?';
		$sql = "SELECT Id, Code_competition, `Type`, Phase, Niveau, Etape, Nbequipes, 
			Date_debut, Date_fin, Nom, Libelle, Lieu, Plan_eau, Departement, Responsable_insc, 
			Responsable_R1, Organisateur, Delegue, ChefArbitre, Publication 
			FROM gickp_Journees 
			WHERE Code_competition IS NOT NULL 
			AND Code_Competition IN ($in) 
			AND Code_saison = ? ";
		$arrayQuery = array_merge($arrayCompets, [$codeSaison]);
		if ($codeCompet != "*") {
			$sql .= "AND Code_competition = ? ";
			$arrayQuery = array_merge($arrayQuery, [$codeCompet]);
            $competition = $myBdd->GetCompetition($codeCompet, $myBdd->GetActiveSaison());
            $this->m_tpl->assign('competition', $competition);
		}
		if ($filtreMois > 0) {
			$sql .= "AND (MONTH(Date_debut) = ? OR MONTH(Date_fin) = ?) ";
			$arrayQuery = array_merge($arrayQuery, [$filtreMois], [$filtreMois]);
		}
		if ($idEvenement != -1 && $modeEvenement == '1') {
			$sql .= "AND Id IN 
				(SELECT Id_Journee 
				FROM gickp_Evenement_Journees 
				WHERE Id_evenement = ?) ";
			$arrayQuery = array_merge($arrayQuery, [$idEvenement]);
		}
		// Limite l'affichage
		if ($idEvenement == -1 && $codeCompet == '*') {
			$sql .= "AND Code_competition != 'POUBELLE' 
				AND (Date_fin - Date_debut) < 20 ";
			
		}
		if (strlen($orderCompet) > 0) {
			$sql .= "ORDER BY $orderCompet";
		}

		$result = $myBdd->pdo->prepare($sql);
		$result->execute($arrayQuery);
		while($row = $result->fetch()) {
			$Checked = '';
			if ($modeEvenement == '2') {
				// Mode Association ...
				for ($j=0;$j<Count($arrayEvenementJournees);$j++) {
					if ($row['Id'] == $arrayEvenementJournees[$j]) {
						$Checked = 'checked';
						break;
					}
				}
			}
			$bAutorisation = utyIsAutorisationJournee($row['Id']);
			array_push($arrayJournees, array( 'Id' => $row['Id'], 
				'Autorisation' => $bAutorisation,	
				'Code_competition' => $row['Code_competition'],
				'Phase' => $row['Phase'],
				'Niveau' => $row['Niveau'],
				'Etape' => $row['Etape'],
				'Nbequipes' => $row['Nbequipes'],
				'Date_debut' => utyDateUsToFr($row['Date_debut']), 
				'Date_fin' => utyDateUsToFr($row['Date_fin']), 
				'Nom' => $row['Nom'], 
				'Libelle' => $row['Libelle'], 
				'Type' => $row['Type'], 
				'Lieu' => $row['Lieu'], 
				'Plan_eau' => $row['Plan_eau'], 
				'Departement' => $row['Departement'], 
				'Responsable_insc' => $row['Responsable_insc'], 
				'Responsable_R1' => $row['Responsable_R1'], 
				'Delegue' => $row['Delegue'], 
				'ChefArbitre' => $row['ChefArbitre'], 
				'Organisateur' => $row['Organisateur'],
				'Publication' => $row['Publication'],
				'Checked' => $Checked ) );
		}
				
		$this->m_tpl->assign('arrayJournees', $arrayJournees);
	}
	
	function Remove()
	{
		$ParamCmd = utyGetPost('ParamCmd');
			
		$arrayParam = explode(',', $ParamCmd);		
		if (count($arrayParam) == 0)
			return; // Rien à Detruire ...
			
		$myBdd = $this->myBdd;

		//Contrôle suppression possible
		$in = str_repeat('?,', count($arrayParam) - 1) . '?';
		$sql = "SELECT Id 
			FROM gickp_Matchs 
			WHERE Id_journee IN ($in) ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute($arrayParam);
		if ($result->rowCount() > 0) {
			die ("Il reste des matchs dans cette journée ! Suppression impossible (<a href='javascript:history.back()'>Retour</a>)");
		}

		// Suppression	
		$sql = "DELETE FROM gickp_Journees 
			WHERE Id IN ($in) ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute($arrayParam);
		for ($i=0;$i<count($arrayParam);$i++) {
			$myBdd->utyJournal('Suppression journee', '', '', 'NULL', 'NULL', $arrayParam[$i]);
		}
	}

	function Duplicate()
	{
		$idJournee = (int) utyGetPost('ParamCmd');
		if ($idJournee != 0) {
			$myBdd = $this->myBdd;
			$nextIdJournee = $myBdd->GetNextIdJournee();
			$sql = "INSERT INTO gickp_Journees 
				(Id, Code_competition, code_saison, Phase, Niveau, Etape, Nbequipes, 
				Date_debut, Date_fin, Nom, Libelle, `Type`, Lieu, Plan_eau, Departement, 
				Responsable_insc, Responsable_R1, Organisateur, Delegue, ChefArbitre) 
				SELECT ?, Code_competition, code_saison, Phase, Niveau, Etape, 
				Nbequipes, Date_debut, Date_fin, Nom, Libelle, `Type`, Lieu, Plan_eau, 
				Departement, Responsable_insc, Responsable_R1, Organisateur, Delegue, ChefArbitre 
				FROM gickp_Journees 
				WHERE Id = ? ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($nextIdJournee, $idJournee));
		}			
		
		if (isset($_SESSION['ParentUrl'])) {
			$target = $_SESSION['ParentUrl'];
			header("Location: http://".$_SERVER['HTTP_HOST'].$target);	
			exit;	
		}

		$myBdd->utyJournal('Dupplication journee', $myBdd->GetActiveSaison(), '', '', $nextIdJournee); // A compléter (saison, compétition, options)
	}
	
	function ParamJournee()
	{
		$_SESSION['ParentUrl'] = $_SERVER['PHP_SELF'];

		$idJournee = (int) utyGetPost('ParamCmd', 0);
		$_SESSION['idJournee'] = $idJournee;
		
		header("Location: GestionParamJournee.php");	
		exit;	
	}	
	
	function AddEvenementJournee()
	{
		$idJournee = (int) utyGetPost('ParamCmd', 0);
		
		$idEvenement = (int) utyGetPost('idEvenement', -1);
		if ($idEvenement == -1)
			return;
		$myBdd = $this->myBdd;
		
		$sql = "REPLACE INTO gickp_Evenement_Journees (Id_Evenement, Id_Journee) 
			VALUES (?, ?)";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($idEvenement, $idJournee));
	
		$myBdd->utyJournal('Evenement +journee', '', '', 'NULL', $idEvenement, $idJournee);
	}
	
	function RemoveEvenementJournee()
	{
		$idJournee = (int) utyGetPost('ParamCmd', 0);
		
		$idEvenement = (int) utyGetSession('idEvenement', -1);
		if ($idEvenement == -1)
			return;
		
		$myBdd = $this->myBdd;
		$sql = "DELETE FROM gickp_Evenement_Journees 
			WHERE Id_Evenement = ? 
			AND Id_Journee = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($idEvenement, $idJournee));
		
		$myBdd->utyJournal('Evenement -journee', '', '', 'NULL', $idEvenement, $idJournee);
	}
	
	function PubliJournee()
	{
		$idJournee = (int) utyGetPost('ParamCmd', 0);
		(utyGetPost('Pub', '') != 'O') ? $changePub = 'O' : $changePub = 'N';
		
		$myBdd = $this->myBdd;
		$sql = "UPDATE gickp_Journees 
			SET Publication = ? 
			WHERE Id = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($changePub, $idJournee));
		
		$myBdd->utyJournal('Publication journee', '', '', 'NULL', 'NULL', $idJournee, $changePub);
	}

	function PubliMultiJournees()
	{
		$ParamCmd = '';
		if (isset($_POST['ParamCmd']))
			$ParamCmd = $_POST['ParamCmd'];
			
		$arrayParam = explode(',', $ParamCmd);		
		if (count($arrayParam) == 0)
			return; // Rien à changer ...

		$myBdd = $this->myBdd;
		
		$in = str_repeat('?,', count($arrayParam) - 1) . '?';
		$sql = "UPDATE gickp_Journees 
			SET Publication = IF(Publication = 'O'; 'N'; 'O')
			WHERE Id IN ($in) ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute($arrayParam);
		// Change Publication	
		for ($i=0;$i<count($arrayParam);$i++) {
			$myBdd->utyJournal('Publication journee', $myBdd->GetActiveSaison(), '', 'NULL', 'NULL', $arrayParam[$i], '-');
		}
	}
	
	function __construct()
	{			
	  	MyPageSecure::MyPageSecure(10);
		
		$this->myBdd = new MyBdd();
		
		$alertMessage = '';
	  
		$Cmd = utyGetPost('Cmd');
		
		if (strlen($Cmd) > 0)
		{
			if ($Cmd == 'Duplicate')
				($_SESSION['Profile'] <= 4) ? $this->Duplicate() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'Remove')
				($_SESSION['Profile'] <= 4) ? $this->Remove() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'ParamJournee')
				($_SESSION['Profile'] <= 10) ? $this->ParamJournee() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'AddEvenementJournee')
				($_SESSION['Profile'] <= 3) ? $this->AddEvenementJournee() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'RemoveEvenementJournee')
				($_SESSION['Profile'] <= 3) ? $this->RemoveEvenementJournee() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'PubliJournee')
				($_SESSION['Profile'] <= 4) ? $this->PubliJournee() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'PubliMultiJournees')
				($_SESSION['Profile'] <= 4) ? $this->PubliMultiJournees() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($alertMessage == '')
			{
				header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);	
				exit;
			}
		}
		
		$this->SetTemplate("Gestion_journees_phases_poules", "Journees_phases", false);
		$this->Load();
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplate('GestionCalendrier');
	}
}		  	

$page = new GestionCalendrier();
