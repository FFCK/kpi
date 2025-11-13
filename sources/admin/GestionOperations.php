<?php

/**/
include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');
include_once('../commun/MyConfig.php');


class GestionOperations extends MyPageSecure
{
	var $myBdd;

	function Load()
	{
		$myBdd = $this->myBdd;

		$idEvenement = (int) utyGetSession('idEvenement', -1);

		// Chargement des Evenements
		$arrayEvenement = array();

		$sql  = "SELECT Id, Libelle, Lieu, Date_debut, Date_fin, Publication, app 
			FROM kp_evenement 
			ORDER BY Date_debut DESC, Libelle DESC ";

		$arrayEvenement = array();
		foreach ($myBdd->pdo->query($sql) as $row) {
			$StdOrSelected = 'Std';
			if ($idEvenement == $row['Id'])
				$StdOrSelected = 'Selected';

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
			));
		}
		$this->m_tpl->assign('arrayEvenement', $arrayEvenement);

	}

	function ExportEvt($idEvenement) {

		$_SESSION['idEvenement'] = $idEvenement;

		$myBdd = $this->myBdd;

		$export = array();

		$sql  = "SELECT *
			FROM kp_evenement 
			WHERE Id = ?";
		$stmt = $myBdd->pdo->prepare($sql);
		$stmt->execute(array($idEvenement));
		$row_evenement = $stmt->fetch( PDO::FETCH_ASSOC );
		$export['kp_evenement'] = $row_evenement;

		$sql  = "SELECT *
			FROM kp_evenement_journee
			WHERE Id_evenement = ?";
		$stmt = $myBdd->pdo->prepare($sql);
		$stmt->execute(array($idEvenement));
		$rows_evenement_journee = $stmt->fetchAll( PDO::FETCH_ASSOC );
		$export['kp_evenement_journee'] = $rows_evenement_journee;

		// récupérer la liste des journées dans un tableau
		$array_journees = array();
		foreach ($rows_evenement_journee as $row) {
			$array_journees[] = $row['Id_journee'];
		}

		$rows_journee = array();
		if (!empty($array_journees)) {
			$placeholders = str_repeat('?,', count($array_journees) - 1) . '?';
			$sql  = "SELECT *
				FROM kp_journee
				WHERE Id IN ($placeholders)";
			$stmt = $myBdd->pdo->prepare($sql);
			$stmt->execute($array_journees);
			$rows_journee = $stmt->fetchAll( PDO::FETCH_ASSOC );
		}
		$export['kp_journee'] = $rows_journee;

		$arrayCompetitions = [];
		foreach ($rows_journee as $row) {
			if (!in_array($row['Code_competition'], $arrayCompetitions)) {
				$arrayCompetitions[] = $row['Code_competition'];
			}
		}
		$evt_saison = isset($rows_journee[0]['Code_saison']) ? $rows_journee[0]['Code_saison'] : null;

		$rows_competition = array();
		$rows_competition_equipe = array();
		if (!empty($arrayCompetitions) && $evt_saison) {
			$placeholders = str_repeat('?,', count($arrayCompetitions) - 1) . '?';
			$sql = "SELECT *
				FROM kp_competition
				WHERE Code_saison = ?
				AND Code IN ($placeholders)";
			$stmt = $myBdd->pdo->prepare($sql);
			$params = array_merge(array($evt_saison), $arrayCompetitions);
			$stmt->execute($params);
			$rows_competition = $stmt->fetchAll( PDO::FETCH_ASSOC );

			$sql = "SELECT *
				FROM kp_competition_equipe
				WHERE Code_saison = ?
				AND Code_compet IN ($placeholders)";
			$stmt = $myBdd->pdo->prepare($sql);
			$params = array_merge(array($evt_saison), $arrayCompetitions);
			$stmt->execute($params);
			$rows_competition_equipe = $stmt->fetchAll( PDO::FETCH_ASSOC );
		}
		$export['kp_competition'] = $rows_competition;
		$export['kp_competition_equipe'] = $rows_competition_equipe;

		$arrayCompetitionsEquipes = [];
		foreach ($rows_competition_equipe as $row) {
			$arrayCompetitionsEquipes[] = $row['Id'];
		}

		$rows_competition_equipe_init = array();
		$rows_competition_equipe_joueur = array();
		$rows_competition_equipe_niveau = array();
		if (!empty($arrayCompetitionsEquipes)) {
			$placeholders = str_repeat('?,', count($arrayCompetitionsEquipes) - 1) . '?';

			$sql = "SELECT *
				FROM kp_competition_equipe_init
				WHERE Id IN ($placeholders)";
			$stmt = $myBdd->pdo->prepare($sql);
			$stmt->execute($arrayCompetitionsEquipes);
			$rows_competition_equipe_init = $stmt->fetchAll( PDO::FETCH_ASSOC );

			$sql = "SELECT *
				FROM kp_competition_equipe_joueur
				WHERE Id_equipe IN ($placeholders)";
			$stmt = $myBdd->pdo->prepare($sql);
			$stmt->execute($arrayCompetitionsEquipes);
			$rows_competition_equipe_joueur = $stmt->fetchAll( PDO::FETCH_ASSOC );

			$sql = "SELECT *
				FROM kp_competition_equipe_niveau
				WHERE Id IN ($placeholders)";
			$stmt = $myBdd->pdo->prepare($sql);
			$stmt->execute($arrayCompetitionsEquipes);
			$rows_competition_equipe_niveau = $stmt->fetchAll( PDO::FETCH_ASSOC );
		}
		$export['kp_competition_equipe_init'] = $rows_competition_equipe_init;
		$export['kp_competition_equipe_joueur'] = $rows_competition_equipe_joueur;
		$export['kp_competition_equipe_niveau'] = $rows_competition_equipe_niveau;

		$rows_competition_equipe_journee = array();
		if (!empty($array_journees)) {
			$placeholders = str_repeat('?,', count($array_journees) - 1) . '?';
			$sql = "SELECT *
				FROM kp_competition_equipe_journee
				WHERE Id_journee IN ($placeholders)";
			$stmt = $myBdd->pdo->prepare($sql);
			$stmt->execute($array_journees);
			$rows_competition_equipe_journee = $stmt->fetchAll( PDO::FETCH_ASSOC );
		}
		$export['kp_competition_equipe_journee'] = $rows_competition_equipe_journee;
		$export['kp_competition_equipe_niveau'] = $rows_competition_equipe_niveau;

		$rows_match = array();
		if (!empty($array_journees)) {
			$placeholders = str_repeat('?,', count($array_journees) - 1) . '?';
			$sql = "SELECT *
				FROM kp_match
				WHERE Id_journee IN ($placeholders)";
			$stmt = $myBdd->pdo->prepare($sql);
			$stmt->execute($array_journees);
			$rows_match = $stmt->fetchAll( PDO::FETCH_ASSOC );
		}
		$export['kp_match'] = $rows_match;

		$arrayMatchs = [];
		foreach ($rows_match as $row) {
			$arrayMatchs[] = $row['Id'];
		}

		$rows_match_detail = array();
		$rows_match_joueur = array();
		$rows_chrono = array();
		if (!empty($arrayMatchs)) {
			$placeholders = str_repeat('?,', count($arrayMatchs) - 1) . '?';

			$sql = "SELECT *
				FROM kp_match_detail
				WHERE Id_match IN ($placeholders)";
			$stmt = $myBdd->pdo->prepare($sql);
			$stmt->execute($arrayMatchs);
			$rows_match_detail = $stmt->fetchAll( PDO::FETCH_ASSOC );

			$sql = "SELECT *
				FROM kp_match_joueur
				WHERE Id_match IN ($placeholders)";
			$stmt = $myBdd->pdo->prepare($sql);
			$stmt->execute($arrayMatchs);
			$rows_match_joueur = $stmt->fetchAll( PDO::FETCH_ASSOC );

			$sql = "SELECT *
				FROM kp_chrono
				WHERE IdMatch IN ($placeholders)";
			$stmt = $myBdd->pdo->prepare($sql);
			$stmt->execute($arrayMatchs);
			$rows_chrono = $stmt->fetchAll( PDO::FETCH_ASSOC );
		}
		$export['kp_match_detail'] = $rows_match_detail;
		$export['kp_match_joueur'] = $rows_match_joueur;
		$export['kp_chrono'] = $rows_chrono;


		// exporter le résultat de la requête sql en json
		$json = json_encode($export);

		// générer un fichier à télécharger
		$filename = 'kp_evenement_' . $idEvenement . '.json';
		header('Content-type: application/json');
		header('Content-Disposition: attachment; filename=' . $filename);
		echo $json;
		exit;
	}

	function ImportEvt($idEvenement) {

		$_SESSION['idEvenement'] = $idEvenement;

		// importer le contenu du fichier json reçu en POST depuis un formulaire
		if (!isset($_FILES['jsonUpload']) || $_FILES['jsonUpload']['error'] != 0) {
			return "Le fichier n\'est pas valide";
		}

		$file = $_FILES['jsonUpload']['tmp_name'];
		$import = json_decode(file_get_contents($file), true);
		if ($import === null && json_last_error() !== JSON_ERROR_NONE) {
			return "Erreur lors de la conversion du JSON : " . json_last_error_msg();
		}

		if ($import['kp_evenement']['Id'] != $idEvenement) {
			return "L\'événement n\'est pas valide : " . $import['kp_evenement']['Id'] . " - " . $idEvenement;
		}

		if (!isset($import['kp_evenement']) 
			|| !isset($import['kp_evenement_journee'])
			|| !isset($import['kp_journee'])
			|| !isset($import['kp_competition'])
			|| !isset($import['kp_competition_equipe'])
			|| !isset($import['kp_competition_equipe_init'])
			|| !isset($import['kp_competition_equipe_joueur'])
			|| !isset($import['kp_competition_equipe_journee'])
			|| !isset($import['kp_competition_equipe_niveau'])
			|| !isset($import['kp_match'])
			|| !isset($import['kp_match_detail'])
			|| !isset($import['kp_match_joueur'])
			|| !isset($import['kp_chrono'])
			) {
			return "L\'export JSON n\'est pas complet";
		}

		$myBdd = $this->myBdd;
		$myBdd->pdo->beginTransaction();
		$myBdd->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

		// import kp_evenement
		$sql = "INSERT INTO kp_evenement (Id, Libelle, Lieu, Date_debut, Date_fin, Publication, Date_publi, Code_uti_publi, logo, app)
			VALUES (:Id, :Libelle, :Lieu, :Date_debut, :Date_fin, :Publication, :Date_publi, :Code_uti_publi, :logo, :app)
			ON DUPLICATE KEY UPDATE Libelle = :Libelle_update, Lieu = :Lieu_update, Date_debut = :Date_debut_update, 
				Date_fin = :Date_fin_update, Publication = :Publication_update, Date_publi = :Date_publi_update, 
				Code_uti_publi = :Code_uti_publi_update, logo = :logo_update, app = :app_update";
		$stmt  = $myBdd->pdo->prepare($sql);
		$params = [
			':Id' => $import['kp_evenement']['Id'],
			':Libelle' => $import['kp_evenement']['Libelle'],
			':Lieu' => $import['kp_evenement']['Lieu'],
			':Date_debut' => $import['kp_evenement']['Date_debut'],
			':Date_fin' => $import['kp_evenement']['Date_fin'],
			':Publication' => $import['kp_evenement']['Publication'],
			':Date_publi' => $import['kp_evenement']['Date_publi'],
			':Code_uti_publi' => $import['kp_evenement']['Code_uti_publi'],
			':logo' => $import['kp_evenement']['logo'],
			':app' => $import['kp_evenement']['app'],
			':Libelle_update' => $import['kp_evenement']['Libelle'],
			':Lieu_update' => $import['kp_evenement']['Lieu'],
			':Date_debut_update' => $import['kp_evenement']['Date_debut'],
			':Date_fin_update' => $import['kp_evenement']['Date_fin'],
			':Publication_update' => $import['kp_evenement']['Publication'],
			':Date_publi_update' => $import['kp_evenement']['Date_publi'],
			':Code_uti_publi_update' => $import['kp_evenement']['Code_uti_publi'],
			':logo_update' => $import['kp_evenement']['logo'],
			':app_update' => $import['kp_evenement']['app'],
		];
		$stmt ->execute($params);

		// import kp_evenement_journee
		$arrayJournees = [];
		$myBdd->pdo->query('DELETE FROM kp_evenement_journee WHERE Id_evenement = ' . $import['kp_evenement']['Id']);

		$sql = "INSERT INTO kp_evenement_journee (Id_evenement, Id_journee)
			VALUES (?, ?)";
		$stmt = $myBdd->pdo->prepare($sql);
		foreach ($import['kp_evenement_journee'] as $row) {
			$stmt->execute(array(
				$import['kp_evenement']['Id'],
				$row['Id_journee']
			));
			$arrayJournees[] = $row['Id_journee'];
		}

		// import kp_journee
		$sql = "INSERT INTO kp_journee (
			Id, Code_competition, Code_saison, Date_debut, Date_fin, Nom, Libelle, Lieu, Departement, Plan_eau,
			Responsable_insc, Responsable_insc_adr, Responsable_insc_cp, Responsable_insc_ville, Responsable_R1,
			Etat, Type, Code_organisateur, Organisateur, Organisateur_adr, Organisateur_cp, Organisateur_ville,
			Delegue, ChefArbitre, Rep_athletes, Arb_nj1, Arb_nj2, Arb_nj3, Arb_nj4, Arb_nj5, Validation,
			Code_uti, Phase, Niveau, Etape, Nbequipes, Publication, Id_dupli, Public_prin, Public_sec
		)
		VALUES (
			:Id, :Code_competition, :Code_saison, :Date_debut, :Date_fin,
			:Nom, :Libelle, :Lieu, :Departement, :Plan_eau,
			:Responsable_insc, :Responsable_insc_adr, :Responsable_insc_cp, :Responsable_insc_ville,
			:Responsable_R1, :Etat, :Type, :Code_organisateur, :Organisateur,
			:Organisateur_adr, :Organisateur_cp, :Organisateur_ville, :Delegue, :ChefArbitre, :Rep_athletes,
			:Arb_nj1, :Arb_nj2, :Arb_nj3, :Arb_nj4, :Arb_nj5, :Validation,
			:Code_uti, :Phase, :Niveau, :Etape, :Nbequipes, :Publication,
			:Id_dupli, :Public_prin, :Public_sec
		)
		ON DUPLICATE KEY UPDATE
			Code_competition = :Code_competition_update,
			Code_saison = :Code_saison_update, 
			Date_debut = :Date_debut_update, 
			Date_fin = :Date_fin_update, 
			Nom = :Nom_update, 
			Libelle = :Libelle_update, 
			Lieu = :Lieu_update, 
			Departement = :Departement_update, 
			Plan_eau = :Plan_eau_update, 
			Responsable_insc = :Responsable_insc_update, 
			Responsable_insc_adr = :Responsable_insc_adr_update, 
			Responsable_insc_cp = :Responsable_insc_cp_update, 
			Responsable_insc_ville = :Responsable_insc_ville_update, 
			Responsable_R1 = :Responsable_R1_update, 
			Etat = :Etat_update, 
			Type = :Type_update, 
			Code_organisateur = :Code_organisateur_update, 
			Organisateur = :Organisateur_update, 
			Organisateur_adr = :Organisateur_adr_update, 
			Organisateur_cp = :Organisateur_cp_update, 
			Organisateur_ville = :Organisateur_ville_update, 
			Delegue = :Delegue_update, 
			ChefArbitre = :ChefArbitre_update, 
			Rep_athletes = :Rep_athletes_update, 
			Arb_nj1 = :Arb_nj1_update, 
			Arb_nj2 = :Arb_nj2_update, 
			Arb_nj3 = :Arb_nj3_update, 
			Arb_nj4 = :Arb_nj4_update, 
			Arb_nj5 = :Arb_nj5_update, 
			Validation = :Validation_update, 
			Code_uti = :Code_uti_update, 
			Phase = :Phase_update, 
			Niveau = :Niveau_update, 
			Etape = :Etape_update, 
			Nbequipes = :Nbequipes_update, 
			Publication = :Publication_update, 
			Id_dupli = :Id_dupli_update, 
			Public_prin = :Public_prin_update, 
			Public_sec = :Public_sec_update			";
		$stmt = $myBdd->pdo->prepare($sql);

		foreach ($import['kp_journee'] as $row) {
			$params = array(
				':Id' => $row['Id'],
				':Code_competition' => $row['Code_competition'],
				':Code_saison' => $row['Code_saison'],
				':Date_debut' => $row['Date_debut'],
				':Date_fin' => $row['Date_fin'],
				':Nom' => $row['Nom'],
				':Libelle' => $row['Libelle'],
				':Lieu' => $row['Lieu'],
				':Departement' => $row['Departement'],
				':Plan_eau' => $row['Plan_eau'],
				':Responsable_insc' => $row['Responsable_insc'],
				':Responsable_insc_adr' => $row['Responsable_insc_adr'],
				':Responsable_insc_cp' => $row['Responsable_insc_cp'],
				':Responsable_insc_ville' => $row['Responsable_insc_ville'],
				':Responsable_R1' => $row['Responsable_R1'],
				':Etat' => $row['Etat'],
				':Type' => $row['Type'],
				':Code_organisateur' => $row['Code_organisateur'],
				':Organisateur' => $row['Organisateur'],
				':Organisateur_adr' => $row['Organisateur_adr'],
				':Organisateur_cp' => $row['Organisateur_cp'],
				':Organisateur_ville' => $row['Organisateur_ville'],
				':Delegue' => $row['Delegue'],
				':ChefArbitre' => $row['ChefArbitre'],
				':Rep_athletes' => $row['Rep_athletes'],
				':Arb_nj1' => $row['Arb_nj1'],
				':Arb_nj2' => $row['Arb_nj2'],
				':Arb_nj3' => $row['Arb_nj3'],
				':Arb_nj4' => $row['Arb_nj4'],
				':Arb_nj5' => $row['Arb_nj5'],
				':Validation' => $row['Validation'],
				':Code_uti' => $row['Code_uti'],
				':Phase' => $row['Phase'],
				':Niveau' => $row['Niveau'],
				':Etape' => $row['Etape'],
				':Nbequipes' => $row['Nbequipes'],
				':Publication' => $row['Publication'],
				':Id_dupli' => $row['Id_dupli'],
				':Public_prin' => $row['Public_prin'],
				':Public_sec' => $row['Public_sec'],
				':Code_competition_update' => $row['Code_competition'],
				':Code_saison_update' => $row['Code_saison'],
				':Date_debut_update' => $row['Date_debut'],
				':Date_fin_update' => $row['Date_fin'],
				':Nom_update' => $row['Nom'],
				':Libelle_update' => $row['Libelle'],
				':Lieu_update' => $row['Lieu'],
				':Departement_update' => $row['Departement'],
				':Plan_eau_update' => $row['Plan_eau'],
				':Responsable_insc_update' => $row['Responsable_insc'],
				':Responsable_insc_adr_update' => $row['Responsable_insc_adr'],
				':Responsable_insc_cp_update' => $row['Responsable_insc_cp'],
				':Responsable_insc_ville_update' => $row['Responsable_insc_ville'],
				':Responsable_R1_update' => $row['Responsable_R1'],
				':Etat_update' => $row['Etat'],
				':Type_update' => $row['Type'],
				':Code_organisateur_update' => $row['Code_organisateur'],
				':Organisateur_update' => $row['Organisateur'],
				':Organisateur_adr_update' => $row['Organisateur_adr'],
				':Organisateur_cp_update' => $row['Organisateur_cp'],
				':Organisateur_ville_update' => $row['Organisateur_ville'],
				':Delegue_update' => $row['Delegue'],
				':ChefArbitre_update' => $row['ChefArbitre'],
				':Rep_athletes_update' => $row['Rep_athletes'],
				':Arb_nj1_update' => $row['Arb_nj1'],
				':Arb_nj2_update' => $row['Arb_nj2'],
				':Arb_nj3_update' => $row['Arb_nj3'],
				':Arb_nj4_update' => $row['Arb_nj4'],
				':Arb_nj5_update' => $row['Arb_nj5'],
				':Validation_update' => $row['Validation'],
				':Code_uti_update' => $row['Code_uti'],
				':Phase_update' => $row['Phase'],
				':Niveau_update' => $row['Niveau'],
				':Etape_update' => $row['Etape'],
				':Nbequipes_update' => $row['Nbequipes'],
				':Publication_update' => $row['Publication'],
				':Id_dupli_update' => $row['Id_dupli'],
				':Public_prin_update' => $row['Public_prin'],
				':Public_sec_update' => $row['Public_sec'],
			);
			$stmt->execute($params);

		}

		// import kp_competition
		$arrayCompetitions = [];
		$sql = "INSERT INTO kp_competition (`Code`, `Code_saison`, `Code_niveau`, `Libelle`, `Soustitre`, `Soustitre2`, 
				`Web`, `BandeauLink`, `LogoLink`, `SponsorLink`, `En_actif`, `Titre_actif`, `Bandeau_actif`, `Logo_actif`, 
				`Sponsor_actif`, `Kpi_ffck_actif`, `ToutGroup`, `TouteSaisons`, `Code_ref`, `GroupOrder`, `Code_typeclt`, 
				`Age_min`, `Age_max`, `Sexe`, `Code_tour`, `Nb_equipes`, `Verrou`, `Statut`, `Qualifies`, `Elimines`, `Points`, 
				`goalaverage`, `Date_calcul`, `Mode_calcul`, `Date_publication`, `Date_publication_calcul`, `Mode_publication_calcul`, 
				`Code_uti_calcul`, `Code_uti_publication`, `Publication`, `Date_publi`, `Code_uti_publi`, `commentairesCompet`) 
			VALUES (:Code, :Code_saison, :Code_niveau, :Libelle, :Soustitre, :Soustitre2, :Web, :BandeauLink, :LogoLink,
				:SponsorLink, :En_actif, :Titre_actif, :Bandeau_actif, :Logo_actif, :Sponsor_actif, :Kpi_ffck_actif, :ToutGroup,
				:TouteSaisons, :Code_ref, :GroupOrder, :Code_typeclt, :Age_min, :Age_max, :Sexe, :Code_tour, :Nb_equipes, :Verrou,
				:Statut, :Qualifies, :Elimines, :Points, :goalaverage, :Date_calcul, :Mode_calcul, :Date_publication, :Date_publication_calcul, 
				:Mode_publication_calcul, :Code_uti_calcul, :Code_uti_publication, :Publication, :Date_publi, :Code_uti_publi, :commentairesCompet)
			ON DUPLICATE KEY UPDATE Code=:Code_update, Code_saison=:Code_saison_update, Code_niveau=:Code_niveau_update,
				Libelle=:Libelle_update, Soustitre=:Soustitre_update, Soustitre2=:Soustitre2_update, Web=:Web_update, 
				BandeauLink=:BandeauLink_update, LogoLink=:LogoLink_update, SponsorLink=:SponsorLink_update, En_actif=:En_actif_update, 
				Titre_actif=:Titre_actif_update, Bandeau_actif=:Bandeau_actif_update, Logo_actif=:Logo_actif_update, 
				Sponsor_actif=:Sponsor_actif_update, Kpi_ffck_actif=:Kpi_ffck_actif_update, ToutGroup=:ToutGroup_update, 
				TouteSaisons=:TouteSaisons_update, Code_ref=:Code_ref_update, GroupOrder=:GroupOrder_update, 
				Code_typeclt=:Code_typeclt_update, Age_min=:Age_min_update, Age_max=:Age_max_update, Sexe=:Sexe_update, 
				Code_tour=:Code_tour_update, Nb_equipes=:Nb_equipes_update, Verrou=:Verrou_update, Statut=:Statut_update,
				Qualifies=:Qualifies_update, Elimines=:Elimines_update, Points=:Points_update, goalaverage=:goalaverage_update,
				Date_calcul=:Date_calcul_update, Mode_calcul=:Mode_calcul_update, Date_publication=:Date_publication_update, 
				Date_publication_calcul=:Date_publication_calcul_update, Mode_publication_calcul=:Mode_publication_calcul_update, 
				Code_uti_calcul=:Code_uti_calcul_update, Code_uti_publication=:Code_uti_publication_update, 
				Publication=:Publication_update, Date_publi=:Date_publi_update, Code_uti_publi=:Code_uti_publi_update, 
				commentairesCompet=:commentairesCompet_update
			";
		$stmt = $myBdd->pdo->prepare($sql);
		foreach ($import['kp_competition'] as $row) {
			$params = array(
				'Code' => $row['Code'], 'Code_saison' => $row['Code_saison'], 'Code_niveau' => $row['Code_niveau'], 'Libelle' => $row['Libelle'], 'Soustitre' => $row['Soustitre'],
				'Soustitre2' => $row['Soustitre2'], 'Web' => $row['Web'], 'BandeauLink' => $row['BandeauLink'], 'LogoLink' => $row['LogoLink'], 'SponsorLink' => $row['SponsorLink'],
				'En_actif' => $row['En_actif'], 'Titre_actif' => $row['Titre_actif'], 'Bandeau_actif' => $row['Bandeau_actif'], 'Logo_actif' => $row['Logo_actif'],
				'Sponsor_actif' => $row['Sponsor_actif'], 'Kpi_ffck_actif' => $row['Kpi_ffck_actif'], 'ToutGroup' => $row['ToutGroup'], 'TouteSaisons' => $row['TouteSaisons'],
				'Code_ref' => $row['Code_ref'], 'GroupOrder' => $row['GroupOrder'], 'Code_typeclt' => $row['Code_typeclt'], 'Age_min' => $row['Age_min'], 'Age_max' => $row['Age_max'],
				'Sexe' => $row['Sexe'], 'Code_tour' => $row['Code_tour'], 'Nb_equipes' => $row['Nb_equipes'], 'Verrou' => $row['Verrou'], 'Statut' => $row['Statut'], 'Qualifies' => $row['Qualifies'],
				'Elimines' => $row['Elimines'], 'Points' => $row['Points'], 'goalaverage' => $row['goalaverage'], 'Date_calcul' => $row['Date_calcul'], 'Mode_calcul' => $row['Mode_calcul'],
				'Date_publication' => $row['Date_publication'], 'Date_publication_calcul' => $row['Date_publication_calcul'], 'Mode_publication_calcul' => $row['Mode_publication_calcul'],
				'Code_uti_calcul' => $row['Code_uti_calcul'], 'Code_uti_publication' => $row['Code_uti_publication'], 'Publication' => $row['Publication'], 'Date_publi' => $row['Date_publi'],
				'Code_uti_publi' => $row['Code_uti_publi'], 'commentairesCompet' => $row['commentairesCompet'],
				'Code_update' => $row['Code'], 'Code_saison_update' => $row['Code_saison'], 'Code_niveau_update' => $row['Code_niveau'], 'Libelle_update' => $row['Libelle'], 'Soustitre_update' => $row['Soustitre'],
				'Soustitre2_update' => $row['Soustitre2'], 'Web_update' => $row['Web'], 'BandeauLink_update' => $row['BandeauLink'], 'LogoLink_update' => $row['LogoLink'], 'SponsorLink_update' => $row['SponsorLink'],
				'En_actif_update' => $row['En_actif'], 'Titre_actif_update' => $row['Titre_actif'], 'Bandeau_actif_update' => $row['Bandeau_actif'], 'Logo_actif_update' => $row['Logo_actif'],
				'Sponsor_actif_update' => $row['Sponsor_actif'], 'Kpi_ffck_actif_update' => $row['Kpi_ffck_actif'], 'ToutGroup_update' => $row['ToutGroup'], 'TouteSaisons_update' => $row['TouteSaisons'],
				'Code_ref_update' => $row['Code_ref'], 'GroupOrder_update' => $row['GroupOrder'], 'Code_typeclt_update' => $row['Code_typeclt'], 'Age_min_update' => $row['Age_min'], 'Age_max_update' => $row['Age_max'],
				'Sexe_update' => $row['Sexe'], 'Code_tour_update' => $row['Code_tour'], 'Nb_equipes_update' => $row['Nb_equipes'], 'Verrou_update' => $row['Verrou'], 'Statut_update' => $row['Statut'], 'Qualifies_update' => $row['Qualifies'],
				'Elimines_update' => $row['Elimines'], 'Points_update' => $row['Points'], 'goalaverage_update' => $row['goalaverage'], 'Date_calcul_update' => $row['Date_calcul'], 'Mode_calcul_update' => $row['Mode_calcul'],
				'Date_publication_update' => $row['Date_publication'], 'Date_publication_calcul_update' => $row['Date_publication_calcul'], 'Mode_publication_calcul_update' => $row['Mode_publication_calcul'],
				'Code_uti_calcul_update' => $row['Code_uti_calcul'], 'Code_uti_publication_update' => $row['Code_uti_publication'], 'Publication_update' => $row['Publication'], 'Date_publi_update' => $row['Date_publi'],
				'Code_uti_publi_update' => $row['Code_uti_publi'], 'commentairesCompet_update' => $row['commentairesCompet']
			);
			$stmt->execute($params);

			if (!in_array($row['Code'], $arrayCompetitions)) {
				$arrayCompetitions[] = $row['Code'];
			}
			
			$evt_saison = $row['Code_saison'];
		}

		// import kp_competition_equipe
		$arrayCompetitionsEquipes = array();
		if (!empty($arrayCompetitions)) {
			$placeholders = str_repeat('?,', count($arrayCompetitions) - 1) . '?';
			$stmt = $myBdd->pdo->prepare("DELETE FROM kp_competition_equipe WHERE Code_compet IN ($placeholders)");
			$stmt->execute($arrayCompetitions);
		}

		$sql = "INSERT INTO kp_competition_equipe (`Id`, `Code_compet`, `Code_saison`, `Libelle`, `Code_club`, `logo`, `color1`, 
				`color2`, `colortext`, `Numero`, `Poule`, `Tirage`, `Pts`, `Clt`, `J`, `G`, `N`, `P`, `F`, `Plus`, `Moins`, `Diff`, 
				`PtsNiveau`, `CltNiveau`, `Id_dupli`, `Pts_publi`, `Clt_publi`, `J_publi`, `G_publi`, `N_publi`, `P_publi`, `F_publi`, 
				`Plus_publi`, `Moins_publi`, `Diff_publi`, `PtsNiveau_publi`, `CltNiveau_publi`)
			VALUES (:Id, :Code_compet, :Code_saison, :Libelle, :Code_club, :logo, :color1, :color2, :colortext, :Numero, :Poule, 
				:Tirage, :Pts, :Clt, :J, :G, :N, :P, :F, :Plus, :Moins, :Diff, :PtsNiveau, :CltNiveau, :Id_dupli, :Pts_publi, :Clt_publi,
				:J_publi, :G_publi, :N_publi, :P_publi, :F_publi, :Plus_publi, :Moins_publi, :Diff_publi, :PtsNiveau_publi, :CltNiveau_publi)";
		$stmt = $myBdd->pdo->prepare($sql);
		foreach ($import['kp_competition_equipe'] as $row) {
			$params = array(
				'Id' => $row['Id'], 'Code_compet' => $row['Code_compet'], 'Code_saison' => $row['Code_saison'], 'Libelle' => $row['Libelle'],
				'Code_club' => $row['Code_club'], 'logo' => $row['logo'], 'color1' => $row['color1'], 'color2' => $row['color2'], 'colortext' => $row['colortext'],
				'Numero' => $row['Numero'], 'Poule' => $row['Poule'], 'Tirage' => $row['Tirage'], 'Pts' => $row['Pts'], 'Clt' => $row['Clt'], 'J' => $row['J'],
				'G' => $row['G'], 'N' => $row['N'], 'P' => $row['P'], 'F' => $row['F'], 'Plus' => $row['Plus'], 'Moins' => $row['Moins'], 'Diff' => $row['Diff'],
				'PtsNiveau' => $row['PtsNiveau'], 'CltNiveau' => $row['CltNiveau'], 'Id_dupli' => $row['Id_dupli'], 'Pts_publi' => $row['Pts_publi'], 'Clt_publi' => $row['Clt_publi'],
				'J_publi' => $row['J_publi'], 'G_publi' => $row['G_publi'], 'N_publi' => $row['N_publi'], 'P_publi' => $row['P_publi'], 'F_publi' => $row['F_publi'],
				'Plus_publi' => $row['Plus_publi'], 'Moins_publi' => $row['Moins_publi'], 'Diff_publi' => $row['Diff_publi'], 'PtsNiveau_publi' => $row['PtsNiveau_publi'],
				'CltNiveau_publi' => $row['CltNiveau_publi']
			);
			$stmt->execute($params);
			$arrayCompetitionsEquipes[] = $row['Id'];
		}

		// import kp_competition_equipe_init
		if (!empty($arrayCompetitionsEquipes)) {
			$placeholders = str_repeat('?,', count($arrayCompetitionsEquipes) - 1) . '?';
			$stmt = $myBdd->pdo->prepare("DELETE FROM kp_competition_equipe_init WHERE Id IN ($placeholders)");
			$stmt->execute($arrayCompetitionsEquipes);
		}

		$sql = "INSERT INTO `kp_competition_equipe_init` (`Id`, `Pts`, `Clt`, `J`, `G`, `N`, `P`, `F`, `Plus`, `Moins`, `Diff`) 
			VALUES (:Id, :Pts, :Clt, :J, :G, :N, :P, :F, :Plus, :Moins, :Diff)";
		$stmt = $myBdd->pdo->prepare($sql);
		foreach ($import['kp_competition_equipe_init'] as $row) {
			$params = array(
				'Id' => $row['Id'], 'Pts' => $row['Pts'], 'Clt' => $row['Clt'], 'J' => $row['J'], 'G' => $row['G'], 'N' => $row['N'],
				'P' => $row['P'], 'F' => $row['F'], 'Plus' => $row['Plus'], 'Moins' => $row['Moins'], 'Diff' => $row['Diff']
			);
			$stmt->execute($params);
		}

		// import kp_competition_equipe_joueur
		if (!empty($arrayCompetitionsEquipes)) {
			$placeholders = str_repeat('?,', count($arrayCompetitionsEquipes) - 1) . '?';
			$stmt = $myBdd->pdo->prepare("DELETE FROM kp_competition_equipe_joueur WHERE Id_equipe IN ($placeholders)");
			$stmt->execute($arrayCompetitionsEquipes);
		}

		$sql = "INSERT INTO `kp_competition_equipe_joueur` (`Id_equipe`, `Matric`, `Nom`, `Prenom`, `Sexe`, `Categ`, `Numero`, `Capitaine`) 
			VALUES (:Id_equipe, :Matric, :Nom, :Prenom, :Sexe, :Categ, :Numero, :Capitaine)";
		$stmt = $myBdd->pdo->prepare($sql);
		foreach ($import['kp_competition_equipe_joueur'] as $row) {
			$params = array(
				'Id_equipe' => $row['Id_equipe'], 'Matric' => $row['Matric'], 'Nom' => $row['Nom'], 'Prenom' => $row['Prenom'], 'Sexe' => $row['Sexe'],
				'Categ' => $row['Categ'], 'Numero' => $row['Numero'], 'Capitaine' => $row['Capitaine']
			);
			$stmt->execute($params);
		}

		// import kp_competition_equipe_journee
		if (!empty($arrayCompetitionsEquipes)) {
			$placeholders = str_repeat('?,', count($arrayCompetitionsEquipes) - 1) . '?';
			$stmt = $myBdd->pdo->prepare("DELETE FROM kp_competition_equipe_journee WHERE Id IN ($placeholders)");
			$stmt->execute($arrayCompetitionsEquipes);
		}

		$sql = "INSERT INTO `kp_competition_equipe_journee` (`Id`, `Id_journee`, `Pts`, `Clt`, `J`, `G`, `N`, `P`, `F`, `Plus`, `Moins`, `Diff`, 
				`PtsNiveau`, `CltNiveau`, `Pts_publi`, `Clt_publi`, `J_publi`, `G_publi`, `N_publi`, `P_publi`, `F_publi`, `Plus_publi`, `Moins_publi`, 
				`Diff_publi`, `PtsNiveau_publi`, `CltNiveau_publi`)
			VALUES (:Id, :Id_journee, :Pts, :Clt, :J, :G, :N, :P, :F, :Plus, :Moins, :Diff, :PtsNiveau, :CltNiveau, :Pts_publi, :Clt_publi,
				:J_publi, :G_publi, :N_publi, :P_publi, :F_publi, :Plus_publi, :Moins_publi, :Diff_publi, :PtsNiveau_publi, :CltNiveau_publi)";
		$stmt = $myBdd->pdo->prepare($sql);
		foreach ($import['kp_competition_equipe_journee'] as $row) {
			$params = array(
				'Id' => $row['Id'], 'Id_journee' => $row['Id_journee'], 'Pts' => $row['Pts'], 'Clt' => $row['Clt'], 'J' => $row['J'],
				'G' => $row['G'], 'N' => $row['N'], 'P' => $row['P'], 'F' => $row['F'], 'Plus' => $row['Plus'],
				'Moins' => $row['Moins'], 'Diff' => $row['Diff'], 'PtsNiveau' => $row['PtsNiveau'], 'CltNiveau' => $row['CltNiveau'], 
				'Pts_publi' => $row['Pts_publi'], 'Clt_publi' => $row['Clt_publi'], 'J_publi' => $row['J_publi'], 'G_publi' => $row['G_publi'], 
				'N_publi' => $row['N_publi'], 'P_publi' => $row['P_publi'], 'F_publi' => $row['F_publi'], 'Plus_publi' => $row['Plus_publi'], 
				'Moins_publi' => $row['Moins_publi'], 'Diff_publi' => $row['Diff_publi'], 'PtsNiveau_publi' => $row['PtsNiveau_publi'], 
				'CltNiveau_publi' => $row['CltNiveau_publi'], 
			);
			$stmt->execute($params);
		}

		// import kp_competition_equipe_niveau
		if (!empty($arrayCompetitionsEquipes)) {
			$placeholders = str_repeat('?,', count($arrayCompetitionsEquipes) - 1) . '?';
			$stmt = $myBdd->pdo->prepare("DELETE FROM kp_competition_equipe_niveau WHERE Id IN ($placeholders)");
			$stmt->execute($arrayCompetitionsEquipes);
		}

		$sql = "INSERT INTO `kp_competition_equipe_niveau` (`Id`, `Niveau`, `Pts`, `Clt`, `J`, `G`, `N`, `P`, `F`, `Plus`, `Moins`, `Diff`, 
				`PtsNiveau`, `CltNiveau`, `Pts_publi`, `Clt_publi`, `J_publi`, `G_publi`, `N_publi`, `P_publi`, `F_publi`, `Plus_publi`, `Moins_publi`, 
				`Diff_publi`, `PtsNiveau_publi`, `CltNiveau_publi`)
			VALUES (:Id, :Niveau, :Pts, :Clt, :J, :G, :N, :P, :F, :Plus, :Moins, :Diff, :PtsNiveau, :CltNiveau, :Pts_publi, :Clt_publi,
				:J_publi, :G_publi, :N_publi, :P_publi, :F_publi, :Plus_publi, :Moins_publi, :Diff_publi, :PtsNiveau_publi, :CltNiveau_publi)";
		$stmt = $myBdd->pdo->prepare($sql);
		foreach ($import['kp_competition_equipe_niveau'] as $row) {
			$params = array(
				'Id' => $row['Id'], 'Niveau' => $row['Niveau'], 'Pts' => $row['Pts'], 'Clt' => $row['Clt'], 'J' => $row['J'],
				'G' => $row['G'], 'N' => $row['N'], 'P' => $row['P'], 'F' => $row['F'], 'Plus' => $row['Plus'],
				'Moins' => $row['Moins'], 'Diff' => $row['Diff'], 'PtsNiveau' => $row['PtsNiveau'], 'CltNiveau' => $row['CltNiveau'], 
				'Pts_publi' => $row['Pts_publi'], 'Clt_publi' => $row['Clt_publi'], 'J_publi' => $row['J_publi'], 'G_publi' => $row['G_publi'], 
				'N_publi' => $row['N_publi'], 'P_publi' => $row['P_publi'], 'F_publi' => $row['F_publi'], 'Plus_publi' => $row['Plus_publi'], 
				'Moins_publi' => $row['Moins_publi'], 'Diff_publi' => $row['Diff_publi'], 'PtsNiveau_publi' => $row['PtsNiveau_publi'], 
				'CltNiveau_publi' => $row['CltNiveau_publi'], 
			);
			$stmt->execute($params);
		}

		// import kp_match
		$arrayMatchs = array();
		if (!empty($arrayJournees)) {
			$placeholders = str_repeat('?,', count($arrayJournees) - 1) . '?';
			$stmt = $myBdd->pdo->prepare("DELETE FROM kp_match WHERE Id_journee IN ($placeholders)");
			$stmt->execute($arrayJournees);
		}

		$sql = "INSERT INTO `kp_match` (`Id`, `Id_journee`, `Libelle`, `Type`, `Statut`, `Date_match`, `Heure_match`, `Heure_fin`, `Terrain`, 
				`Numero_ordre`, `Periode`, `Id_equipeA`, `Id_equipeB`, `ColorA`, `ColorB`, `ScoreA`, `ScoreB`, `ScoreDetailA`, `ScoreDetailB`, 
				`CoeffA`, `CoeffB`, `Commentaires_officiels`, `Commentaires`, `Arbitre_principal`, `Arbitre_secondaire`, 
				`Matric_arbitre_principal`, `Matric_arbitre_secondaire`, `Secretaire`, `Chronometre`, `Timeshoot`, `Ligne1`, `Ligne2`, 
				`Publication`, `Code_uti`, `Validation`)
			VALUES (:Id, :Id_journee, :Libelle, :Type, :Statut, :Date_match, :Heure_match, :Heure_fin, :Terrain, :Numero_ordre, :Periode,
				:Id_equipeA, :Id_equipeB, :ColorA, :ColorB, :ScoreA, :ScoreB, :ScoreDetailA, :ScoreDetailB, :CoeffA, :CoeffB,
				:Commentaires_officiels, :Commentaires, :Arbitre_principal, :Arbitre_secondaire, :Matric_arbitre_principal,
				:Matric_arbitre_secondaire, :Secretaire, :Chronometre, :Timeshoot, :Ligne1, :Ligne2, :Publication, :Code_uti, :Validation)";
		$stmt = $myBdd->pdo->prepare($sql);
		foreach ($import['kp_match'] as $row) {
			$params = array(
				'Id' => $row['Id'], 'Id_journee' => $row['Id_journee'], 'Libelle' => $row['Libelle'], 'Type' => $row['Type'],
				'Statut' => $row['Statut'], 'Date_match' => $row['Date_match'], 'Heure_match' => $row['Heure_match'],
				'Heure_fin' => $row['Heure_fin'], 'Terrain' => $row['Terrain'], 'Numero_ordre' => $row['Numero_ordre'],
				'Periode' => $row['Periode'], 'Id_equipeA' => $row['Id_equipeA'], 'Id_equipeB' => $row['Id_equipeB'],
				'ColorA' => $row['ColorA'], 'ColorB' => $row['ColorB'], 'ScoreA' => $row['ScoreA'], 'ScoreB' => $row['ScoreB'],
				'ScoreDetailA' => $row['ScoreDetailA'], 'ScoreDetailB' => $row['ScoreDetailB'], 'CoeffA' => $row['CoeffA'],
				'CoeffB' => $row['CoeffB'], 'Commentaires_officiels' => $row['Commentaires_officiels'],
				'Commentaires' => $row['Commentaires'], 'Arbitre_principal' => $row['Arbitre_principal'],
				'Arbitre_secondaire' => $row['Arbitre_secondaire'], 'Matric_arbitre_principal' => $row['Matric_arbitre_principal'],
				'Matric_arbitre_secondaire' => $row['Matric_arbitre_secondaire'], 'Secretaire' => $row['Secretaire'],
				'Chronometre' => $row['Chronometre'], 'Timeshoot' => $row['Timeshoot'], 'Ligne1' => $row['Ligne1'],
				'Ligne2' => $row['Ligne2'], 'Publication' => $row['Publication'], 'Code_uti' => $row['Code_uti'],
				'Validation' => $row['Validation']
			);
			$stmt->execute($params);
			$arrayMatchs[] = $row['Id'];
		}

		// import kp_match_detail
		if (!empty($arrayMatchs)) {
			$placeholders = str_repeat('?,', count($arrayMatchs) - 1) . '?';
			$stmt = $myBdd->pdo->prepare("DELETE FROM kp_match_detail WHERE Id_match IN ($placeholders)");
			$stmt->execute($arrayMatchs);
		}

		$sql = "INSERT INTO `kp_match_detail` (`Id`, `Id_match`, `Periode`, `Temps`, `Id_evt_match`, `motif`, 
				`Competiteur`, `Numero`, `Equipe_A_B`, `date_insert`)
			VALUES (:Id, :Id_match, :Periode, :Temps, :Id_evt_match, :motif, :Competiteur, :Numero, :Equipe_A_B, :date_insert)";
		$stmt = $myBdd->pdo->prepare($sql);
		foreach ($import['kp_match_detail'] as $row) {
			$params = array(
				'Id' => $row['Id'], 'Id_match' => $row['Id_match'], 'Periode' => $row['Periode'], 'Temps' => $row['Temps'],
				'Id_evt_match' => $row['Id_evt_match'], 'motif' => $row['motif'], 'Competiteur' => $row['Competiteur'],
				'Numero' => $row['Numero'], 'Equipe_A_B' => $row['Equipe_A_B'], 'date_insert' => $row['date_insert']
			);
			$stmt->execute($params);
		}

		// import kp_match_joueur
		if (!empty($arrayMatchs)) {
			$placeholders = str_repeat('?,', count($arrayMatchs) - 1) . '?';
			$stmt = $myBdd->pdo->prepare("DELETE FROM kp_match_joueur WHERE Id_match IN ($placeholders)");
			$stmt->execute($arrayMatchs);
		}

		$sql = "INSERT INTO `kp_match_joueur` (`Id_match`, `Matric`, `Numero`, `Equipe`, `Capitaine`)
		VALUES (:Id_match, :Matric, :Numero, :Equipe, :Capitaine)";
		$stmt = $myBdd->pdo->prepare($sql);
		foreach ($import['kp_match_joueur'] as $row) {
			$params = array(
				'Id_match' => $row['Id_match'], 'Matric' => $row['Matric'],
				'Numero' => $row['Numero'], 'Equipe' => $row['Equipe'], 'Capitaine' => $row['Capitaine']
			);
			$stmt->execute($params);
		}

		// import kp_chrono
		if (!empty($arrayMatchs)) {
			$placeholders = str_repeat('?,', count($arrayMatchs) - 1) . '?';
			$stmt = $myBdd->pdo->prepare("DELETE FROM kp_chrono WHERE IdMatch IN ($placeholders)");
			$stmt->execute($arrayMatchs);
		}

		$sql = "INSERT INTO `kp_chrono` (`IdMatch`, `action`, `start_time`, `start_time_server`, `run_time`, `max_time`)
			VALUES (:IdMatch, :action, :start_time, :start_time_server, :run_time, :max_time)";
		$stmt = $myBdd->pdo->prepare($sql);
		foreach ($import['kp_chrono'] as $row) {
			$params = array(
				'IdMatch'=> $row['IdMatch'], 'action' => $row['action'], 'start_time' => $row['start_time'],
				'start_time_server' => $row['start_time_server'], 'run_time' => $row['run_time'], 'max_time' => $row['max_time']
			);
			$stmt->execute($params);
		}

		$myBdd->pdo->exec('SET FOREIGN_KEY_CHECKS = 1;');
		$myBdd->pdo->commit();
		return "Import réussi";
	}

	function __construct()
	{
		parent::__construct(1);

		$this->myBdd = new MyBdd();

		$alertMessage = '';

		$Cmd = utyGetPost('Cmd');
		$ParamCmd = utyGetPost('ParamCmd');

		if (strlen($Cmd) > 0) {
			if ($Cmd == 'ExportEvt') {
				($_SESSION['Profile'] <= 1) ? $alertMessage = $this->ExportEvt($ParamCmd) : $alertMessage = 'Vous n avez pas les droits pour cette action.';
			}

			if ($Cmd == 'ImportEvt') {
				($_SESSION['Profile'] <= 1) ? $alertMessage = $this->ImportEvt($ParamCmd) : $alertMessage = 'Vous n avez pas les droits pour cette action.';
			}

			if ($alertMessage == '') {
				header("Location: http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
				exit;
			}
		}

		$this->SetTemplate("Operations_d_administration", "Operations", false);
		$this->Load();
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplate('GestionOperations');


	}

}

$page = new GestionOperations();
