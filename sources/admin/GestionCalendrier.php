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
		$_SESSION['filtreMois'] = utyGetPost('filtreMois', '');
		$filtreMois = $_SESSION['filtreMois'];
		$this->m_tpl->assign('filtreMois', $_SESSION['filtreMois']);

		$codeSaison = $myBdd->GetActiveSaison();

		// Chargement des Evenements ...
		$idEvenement = utyGetSession('idEvenement', -1);
		$idEvenement = utyGetPost('evenement', $idEvenement);

		//Filtre affichage type compet
		$AfficheCompet = utyGetSession('AfficheCompet', '');
		$AfficheCompet = utyGetPost('AfficheCompet', $AfficheCompet);
		$_SESSION['AfficheCompet'] = $AfficheCompet;
		$this->m_tpl->assign('AfficheCompet', $AfficheCompet);

		$_SESSION['idEvenement'] = $idEvenement;
		$this->m_tpl->assign('idEvenement', $idEvenement);

		$sql = "SELECT Id, Libelle, Date_debut 
			FROM kp_evenement 
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
		if (utyGetPost('codeCompet', false)) {	// @COSANDCO_WAMPSER
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
		if (($modeEvenement == 1) && ($idEvenement != -1)) {
			$sqlAfficheCompet = " AND ej.Id_evenement  = ? ";
			$arrayAfficheCompet = array_merge($arrayAfficheCompet, [$idEvenement]);
		}
		$sql = "SELECT DISTINCT c.*, g.section, g.ordre, g.id 
			FROM kp_groupe g, kp_competition c 
			LEFT OUTER JOIN kp_journee j ON (c.Code_saison = j.Code_saison AND c.Code = j.Code_competition)
			LEFT OUTER JOIN kp_evenement_journee ej ON (j.Id = ej.Id_journee) 
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
			[utyGetSession('AfficheNiveau') . '%'],
			$arrayAfficheCompet
		));

		$arrayCompetition = array();

		if (-1 != $idEvenement) {
			if ('*' == $codeCompet) {
				$selected = 'selected';
			} else {
				$selected = '';
			}
			if (utyGetSession('lang') == 'en') {
				$arrayCompetition[0]['label'] = "All competitions";
				$arrayCompetition[0]['options'][] = array('Code' => '*', 'Libelle' => 'All competitions', 'selected' => $selected);
			} else {
				$arrayCompetition[0]['label'] = "Toutes les compétitions";
				$arrayCompetition[0]['options'][] = array('Code' => '*', 'Libelle' => 'Toutes les compétitions', 'selected' => $selected);
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
				$Libelle .= ' - ' . $row["Soustitre2"];
			}

			$listeCompet .= $row["Code"] . "','";
			$arrayCompets[] = $row["Code"];

			if ($j != $row['section']) {
				$i++;
				$arrayCompetition[$i]['label'] = $label[$row['section']];
			}
			if ($row["Code"] == $codeCompet) {
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
			array_push($arrayCompetitionOrder, array('Code' => 'Date_debut, Niveau, Phase, Lieu, Libelle, Id', 'Libelle' => $lang['Par_date_croissante'], 'Selection' => 'SELECTED'));
		else
			array_push($arrayCompetitionOrder, array('Code' => 'Date_debut, Niveau, Phase, Lieu, Libelle, Id', 'Libelle' => $lang['Par_date_croissante'], 'Selection' => ''));

		if ("Date_debut Desc, Niveau, Phase, Lieu, Libelle" == $orderCompet)
			array_push($arrayCompetitionOrder, array('Code' => 'Date_debut Desc, Niveau, Phase, Lieu, Libelle', 'Libelle' => $lang['Par_date_decroissante'], 'Selection' => 'SELECTED'));
		else
			array_push($arrayCompetitionOrder, array('Code' => 'Date_debut Desc, Niveau, Phase, Lieu, Libelle', 'Libelle' => $lang['Par_date_decroissante'], 'Selection' => ''));

		if ("Libelle, Niveau, Phase" == $orderCompet)
			array_push($arrayCompetitionOrder, array('Code' => 'Libelle, Niveau, Phase', 'Libelle' => $lang['Par_Nom'], 'Selection' => 'SELECTED'));
		else
			array_push($arrayCompetitionOrder, array('Code' => 'Libelle, Niveau, Phase', 'Libelle' => $lang['Par_Nom'], 'Selection' => ''));

		if ("Id, Niveau, Phase" == $orderCompet)
			array_push($arrayCompetitionOrder, array('Code' => 'Id, Niveau, Phase', 'Libelle' => $lang['Par_Numero'], 'Selection' => 'SELECTED'));
		else
			array_push($arrayCompetitionOrder, array('Code' => 'Id, Niveau, Phase', 'Libelle' => $lang['Par_Numero'], 'Selection' => ''));

		if ("Niveau, Phase, Date_debut" == $orderCompet)
			array_push($arrayCompetitionOrder, array('Code' => 'Niveau, Phase, Date_debut', 'Libelle' => $lang['Par_Niveau'], 'Selection' => 'SELECTED'));
		else
			array_push($arrayCompetitionOrder, array('Code' => 'Niveau, Phase, Date_debut', 'Libelle' => $lang['Par_Niveau'], 'Selection' => ''));

		$this->m_tpl->assign('arrayCompetitionOrder', $arrayCompetitionOrder);

		$arrayEvenementJournees = array();
		if ($modeEvenement == '2' && $idEvenement != -1) {
			// Mode Association ... => Chargement des Journées de l'Evenement ...
			$sql = "SELECT Id_journee 
				FROM kp_evenement_journee 
				WHERE Id_evenement = ? ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($idEvenement));
			while ($row = $result->fetch()) {
				array_push($arrayEvenementJournees, $row['Id_journee']);
			}
		}

		// Chargement des Journees ...
		$arrayJournees = array();
		$arrayQuery = array();
		$sql = "SELECT Id, Code_competition, `Type`, Phase, Niveau, Etape, Nbequipes, 
			Date_debut, Date_fin, Nom, Libelle, Lieu, Plan_eau, Departement, Responsable_insc, 
			Responsable_R1, Organisateur, Delegue, ChefArbitre, 
			Rep_athletes, Arb_nj1, Arb_nj2, Arb_nj3, Arb_nj4, Arb_nj5, Publication 
			FROM kp_journee 
			WHERE Code_competition IS NOT NULL ";
		if (count($arrayCompets) > 0) {
			$in = str_repeat('?,', count($arrayCompets) - 1) . '?';
			$sql .= "AND Code_Competition IN ($in) 
				AND Code_saison = ? ";
			$arrayQuery = array_merge($arrayCompets, [$codeSaison]);
		}
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
				FROM kp_evenement_journee 
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

		// var_dump($sql, $arrayQuery);
		$result = $myBdd->pdo->prepare($sql);
		$result->execute($arrayQuery);
		while ($row = $result->fetch()) {
			$Checked = '';
			if ($modeEvenement == '2') {
				// Mode Association ...
				for ($j = 0; $j < Count($arrayEvenementJournees); $j++) {
					if ($row['Id'] == $arrayEvenementJournees[$j]) {
						$Checked = 'checked';
						break;
					}
				}
			}
			$row['Date_debut_gcal'] = date("Ymd", strtotime($row['Date_debut']));
			$row['Date_fin_gcal'] = date("Ymd", strtotime($row['Date_fin']));
			if (utyGetSession('lang') == 'fr') {
				$row['Date_debut'] = utyDateUsToFr($row['Date_debut']);
				$row['Date_fin'] = utyDateUsToFr($row['Date_fin']);
			}

			$bAutorisation = utyIsAutorisationJournee($row['Id']);
			array_push($arrayJournees, array(
				'Id' => $row['Id'],
				'Autorisation' => $bAutorisation,
				'Code_competition' => $row['Code_competition'],
				'Phase' => $row['Phase'],
				'Niveau' => $row['Niveau'],
				'Etape' => $row['Etape'],
				'Nbequipes' => $row['Nbequipes'],
				'Date_debut' => $row['Date_debut'],
				'Date_fin' => $row['Date_fin'],
				'Date_debut_gcal' => $row['Date_debut_gcal'],
				'Date_fin_gcal' => $row['Date_fin_gcal'],
				'Nom' => $row['Nom'],
				'Libelle' => $row['Libelle'],
				'Type' => $row['Type'],
				'Lieu' => $row['Lieu'],
				'Plan_eau' => $row['Plan_eau'],
				'Departement' => $row['Departement'],
				'Responsable_insc' => utyGetNomPrenom($row['Responsable_insc']),
				'Responsable_R1' => utyGetNomPrenom($row['Responsable_R1']),
				'Delegue' => utyGetNomPrenom($row['Delegue']),
				'ChefArbitre' => utyGetNomPrenom($row['ChefArbitre']),
				'Rep_athletes' => utyGetNomPrenom($row['Rep_athletes']),
				'Arb_nj1' => utyGetNomPrenom($row['Arb_nj1']),
				'Arb_nj2' => utyGetNomPrenom($row['Arb_nj2']),
				'Arb_nj3' => utyGetNomPrenom($row['Arb_nj3']),
				'Arb_nj4' => utyGetNomPrenom($row['Arb_nj4']),
				'Arb_nj5' => utyGetNomPrenom($row['Arb_nj5']),
				'Organisateur' => $row['Organisateur'],
				'Publication' => $row['Publication'],
				'Checked' => $Checked
			));
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
			FROM kp_match 
			WHERE Id_journee IN ($in) ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute($arrayParam);
		if ($result->rowCount() > 0) {
			die("Il reste des matchs dans ces journées ! Suppression impossible (<a href='javascript:history.back()'>Retour</a>)");
		}

		$sql = "SELECT Id_evenement 
			FROM kp_evenement_journee 
			WHERE Id_journee IN ($in) ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute($arrayParam);
		if ($result->rowCount() > 0) {
			die("Des journées restent associées à un événement ! Suppression impossible (<a href='javascript:history.back()'>Retour</a>)");
		}

		try {
			$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$myBdd->pdo->beginTransaction();

			// Suppression	
			$sql = "DELETE FROM kp_journee 
				WHERE Id IN ($in) ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute($arrayParam);

			$myBdd->pdo->commit();
		} catch (Exception $e) {
			$myBdd->pdo->rollBack();
			utySendMail("[KPI] Erreur SQL", "Suppression journee, $in" . '\r\n' . $e->getMessage());

			return "La requête ne peut pas être exécutée !\\nCannot execute query!";
		}

		for ($i = 0; $i < count($arrayParam); $i++) {
			$myBdd->utyJournal('Suppression journee', '', '', null, null, $arrayParam[$i]);
		}
		return;
	}

	function Duplicate()
	{
		$idJournee = (int) utyGetPost('ParamCmd');
		if ($idJournee != 0) {
			$myBdd = $this->myBdd;
			$nextIdJournee = $myBdd->GetNextIdJournee();
			try {
				$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$myBdd->pdo->beginTransaction();

				$sql = "INSERT INTO kp_journee 
					(Id, Code_competition, code_saison, Phase, Niveau, Etape, Nbequipes, 
					Date_debut, Date_fin, Nom, Libelle, `Type`, Lieu, Plan_eau, Departement, 
					Responsable_insc, Responsable_R1, Organisateur, Delegue, ChefArbitre, 
					Rep_athletes, Arb_nj1, Arb_nj2, Arb_nj3, Arb_nj4, Arb_nj5) 
					SELECT ?, Code_competition, code_saison, Phase, Niveau, Etape, 
					Nbequipes, Date_debut, Date_fin, Nom, Libelle, `Type`, Lieu, Plan_eau, 
					Departement, Responsable_insc, Responsable_R1, Organisateur, Delegue, ChefArbitre, 
					Rep_athletes, Arb_nj1, Arb_nj2, Arb_nj3, Arb_nj4, Arb_nj5 
					FROM kp_journee 
					WHERE Id = ? ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array($nextIdJournee, $idJournee));

				$myBdd->pdo->commit();
			} catch (Exception $e) {
				$myBdd->pdo->rollBack();
				utySendMail("[KPI] Erreur SQL", "Dupplication journee, $idJournee" . '\r\n' . $e->getMessage());

				return "La requête ne peut pas être exécutée !\\nCannot execute query!";
			}
		}

		if (isset($_SESSION['ParentUrl'])) {
			$target = $_SESSION['ParentUrl'];
			header("Location: http://" . $_SERVER['HTTP_HOST'] . $target);
			exit;
		}

		$myBdd->utyJournal('Dupplication journee', $myBdd->GetActiveSaison(), '', null, $nextIdJournee); // A compléter (saison, compétition, options)
		return;
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

		try {
			$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$myBdd->pdo->beginTransaction();

			$sql = "REPLACE INTO kp_evenement_journee (Id_Evenement, Id_Journee) 
				VALUES (?, ?)";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($idEvenement, $idJournee));

			$myBdd->pdo->commit();
		} catch (Exception $e) {
			$myBdd->pdo->rollBack();
			utySendMail("[KPI] Erreur SQL", "Evenement +journee, $idEvenement, $idJournee" . '\r\n' . $e->getMessage());

			return "La requête ne peut pas être exécutée !\\nCannot execute query!";
		}

		$myBdd->utyJournal('Evenement +journee', '', '', null, $idEvenement, $idJournee);
		return;
	}

	function RemoveEvenementJournee()
	{
		$idJournee = (int) utyGetPost('ParamCmd', 0);

		$idEvenement = (int) utyGetSession('idEvenement', -1);
		if ($idEvenement == -1)
			return;

		$myBdd = $this->myBdd;

		try {
			$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$myBdd->pdo->beginTransaction();

			$sql = "DELETE FROM kp_evenement_journee 
				WHERE Id_Evenement = ? 
				AND Id_Journee = ? ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($idEvenement, $idJournee));

			$myBdd->pdo->commit();
		} catch (Exception $e) {
			$myBdd->pdo->rollBack();
			utySendMail("[KPI] Erreur SQL", "Evenement -journee, $idEvenement, $idJournee" . '\r\n' . $e->getMessage());

			return "La requête ne peut pas être exécutée !\\nCannot execute query!";
		}

		$myBdd->utyJournal('Evenement -journee', '', '', null, $idEvenement, $idJournee);
		return;
	}

	function PubliJournee()
	{
		$idJournee = (int) utyGetPost('ParamCmd', 0);
		(utyGetPost('Pub', '') != 'O') ? $changePub = 'O' : $changePub = 'N';

		$myBdd = $this->myBdd;
		$sql = "UPDATE kp_journee 
			SET Publication = ? 
			WHERE Id = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($changePub, $idJournee));

		$myBdd->utyJournal('Publication journee', '', '', null, null, $idJournee, $changePub);
	}

	function PubliMultiJournees()
	{
		$ParamCmd = utyGetPost('ParamCmd', '');

		$arrayParam = explode(',', $ParamCmd);
		if (count($arrayParam) == 0)
			return; // Rien à changer ...

		$myBdd = $this->myBdd;

		$in = str_repeat('?,', count($arrayParam) - 1) . '?';
		$sql = "UPDATE kp_journee 
			SET Publication = IF(Publication = 'O', 'N', 'O')
			WHERE Id IN ($in) ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute($arrayParam);
		// Change Publication	
		for ($i = 0; $i < count($arrayParam); $i++) {
			$myBdd->utyJournal('Publication journee', $myBdd->GetActiveSaison(), '', null, null, $arrayParam[$i], '-');
		}
	}

	function __construct()
	{
		parent::__construct(10);

		$this->myBdd = new MyBdd();

		$alertMessage = '';

		$Cmd = utyGetPost('Cmd');

		if (strlen($Cmd) > 0) {
			if ($Cmd == 'Duplicate') ($_SESSION['Profile'] <= 4) ? $alertMessage = $this->Duplicate() : $alertMessage = 'Vous n avez pas les droits pour cette action.';

			if ($Cmd == 'Remove') ($_SESSION['Profile'] <= 4) ? $alertMessage = $this->Remove() : $alertMessage = 'Vous n avez pas les droits pour cette action.';

			if ($Cmd == 'ParamJournee') ($_SESSION['Profile'] <= 10) ? $this->ParamJournee() : $alertMessage = 'Vous n avez pas les droits pour cette action.';

			if ($Cmd == 'AddEvenementJournee') ($_SESSION['Profile'] <= 3) ? $alertMessage = $this->AddEvenementJournee() : $alertMessage = 'Vous n avez pas les droits pour cette action.';

			if ($Cmd == 'RemoveEvenementJournee') ($_SESSION['Profile'] <= 3) ? $alertMessage = $this->RemoveEvenementJournee() : $alertMessage = 'Vous n avez pas les droits pour cette action.';

			if ($Cmd == 'PubliJournee') ($_SESSION['Profile'] <= 4) ? $this->PubliJournee() : $alertMessage = 'Vous n avez pas les droits pour cette action.';

			if ($Cmd == 'PubliMultiJournees') ($_SESSION['Profile'] <= 4) ? $this->PubliMultiJournees() : $alertMessage = 'Vous n avez pas les droits pour cette action.';

			if ($alertMessage == '') {
				header("Location: http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
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
