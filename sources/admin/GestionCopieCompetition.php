<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion des paramètres d'une Journee

class GestionCopieCompetition extends MyPageSecure
{
	function Load()
	{
		$myBdd = new MyBdd();

		$codeSaison = $myBdd->GetActiveSaison();
		$codeCompet = utyGetSession('codeCompet');

		$saisonOrigine = utyGetSession('saisonOrigine', $codeSaison);
		$saisonOrigine = utyGetPost('saisonOrigine', $saisonOrigine);
		$_SESSION['saisonOrigine'] = $saisonOrigine;
		$this->m_tpl->assign('saisonOrigine', $saisonOrigine);

		$competOrigine = utyGetSession('competOrigine', $codeCompet);
		$competOrigine = utyGetPost('competOrigine', $competOrigine);
		$_SESSION['competOrigine'] = $competOrigine;
		$this->m_tpl->assign('competOrigine', $competOrigine);

		$saisonDestination = utyGetSession('saisonDestination', $codeSaison);
		$saisonDestination = utyGetPost('saisonDestination', $saisonDestination);
		$_SESSION['saisonDestination'] = $saisonDestination;
		$this->m_tpl->assign('saisonDestination', $saisonDestination);

		$competDestination = utyGetSession('competDestination', $codeCompet);
		$competDestination = utyGetPost('competDestination', $competDestination);
		$_SESSION['competDestination'] = $competDestination;
		$this->m_tpl->assign('competDestination', $competDestination);

		// Liste des saisons
		$arraySaisons = array();
		$sql = "SELECT DISTINCT Code 
			FROM kp_saison 
            WHERE Code > '1900' 
			ORDER BY Code ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute();
		while ($row = $result->fetch()) {
			array_push($arraySaisons, array('Code' => $row['Code']));
		}
		$this->m_tpl->assign('arraySaisons', $arraySaisons);

		//Liste des codes compétition origine
		$label = $myBdd->getSections();
		$arrayCompetitionOrigine = array();
		$sql = "SELECT c.Code, c.Libelle, c.Code_typeclt, c.Nb_equipes, c.Qualifies, c.Elimines, 
			c.Soustitre, c.Soustitre2, c.commentairesCompet, g.section, g.ordre 
			FROM kp_competition c, kp_groupe g 
			WHERE c.Code_saison = ? 
			AND c.Code_ref = g.Groupe 
			ORDER BY g.section, g.ordre, c.Code_tour, c.GroupOrder, c.Code ";
		$i = -1;
		$j = '';
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($saisonOrigine));
		while ($row = $result->fetch()) {
			$row['sectionLabel'] = $label[$row['section']];
			if ($j != $row['section']) {
				$i++;
				$arrayCompetitionOrigine[$i]['label'] = $label[$row['section']];
			}
			if ($row["Code"] == $competOrigine) {
				$row['selected'] = 'selected';
			} else {
				$row['selected'] = '';
			}
			$j = $row['section'];
			$arrayCompetitionOrigine[$i]['options'][] = $row;

			if ($row['Code'] == $competOrigine) {
				$this->m_tpl->assign('codeTypeCltOrigine', $row['Code_typeclt']);
				$this->m_tpl->assign('equipesOrigine', $row['Nb_equipes']);
				$this->m_tpl->assign('qualifiesOrigine', $row['Qualifies']);
				$this->m_tpl->assign('eliminesOrigine', $row['Elimines']);
				$this->m_tpl->assign('Soustitre', $row['Soustitre']);
				$this->m_tpl->assign('Soustitre2', $row['Soustitre2']);
				$this->m_tpl->assign('commentairesCompet', $row['commentairesCompet']);
			}
		}
		$this->m_tpl->assign('arrayCompetitionOrigine', $arrayCompetitionOrigine);

		//Liste des codes compétition destination
		$arrayCompetitionDestination = array();
		$sqlFiltreCompetition = utyGetFiltreCompetition('');
		$sql = "SELECT c.Code, c.Libelle, c.Code_typeclt, c.Nb_equipes, c.Qualifies, c.Elimines, 
			c.Soustitre, c.Soustitre2, c.commentairesCompet, g.section, g.ordre 
			FROM kp_competition c, kp_groupe g 
			WHERE c.Code_saison = ? 
			AND c.Code_ref = g.Groupe 
            $sqlFiltreCompetition
            ORDER BY g.section, g.ordre, c.Code_tour, c.GroupOrder, c.Code ";
		$i = -1;
		$j = '';
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($saisonDestination));
		while ($row = $result->fetch()) {
			$row['sectionLabel'] = $label[$row['section']];
			if ($j != $row['section']) {
				$i++;
				$arrayCompetitionDestination[$i]['label'] = $label[$row['section']];
			}
			if ($row["Code"] == $competDestination) {
				$row['selected'] = 'selected';
			} else {
				$row['selected'] = '';
			}
			$j = $row['section'];
			$arrayCompetitionDestination[$i]['options'][] = $row;

			if ($row['Code'] == $competDestination) {
				$this->m_tpl->assign('codeTypeCltDestination', $row['Code_typeclt']);
				$this->m_tpl->assign('equipesDestination', $row['Nb_equipes']);
				$this->m_tpl->assign('qualifiesDestination', $row['Qualifies']);
				$this->m_tpl->assign('eliminesDestination', $row['Elimines']);
			}
		}
		$this->m_tpl->assign('arrayCompetitionDestination', $arrayCompetitionDestination);

		// Journées
		$arrayJournees = array();
		$listJournees = [];
		$sql = "SELECT j.Id, j.Code_competition, j.Code_saison, j.Phase, j.Niveau, 
			j.Date_debut, j.Date_fin, j.Nom, j.Libelle, j.Lieu, j.Plan_eau, j.Departement, 
			j.Responsable_insc, j.Responsable_R1, j.Organisateur, j.Delegue
			FROM kp_journee j
			-- LEFT OUTER JOIN kp_match m ON j.Id = m.Id_journee
			WHERE j.Code_competition = ? 
			AND j.Code_saison = ? 
			ORDER By j.Niveau, j.Phase, j.Lieu ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($competOrigine, $saisonOrigine));
		while ($row = $result->fetch()) {
			array_push(
				$arrayJournees,
				array(
					'Niveau' => $row['Niveau'],
					'Phase' => $row['Phase'],
					'Lieu' => $row['Lieu']
				)
			);
			array_push($listJournees, $row['Id']);
			if (!isset($Date_debut) && isset($row['Date_debut'])) {
				$Date_debut = $row['Date_debut'];
				$this->m_tpl->assign('Date_debut', utyDateUsToFr($row['Date_debut'] ?: null));
				$this->m_tpl->assign('Date_fin', utyDateUsToFr($row['Date_fin']));
				$this->m_tpl->assign('Nom', $row['Nom']);
				$this->m_tpl->assign('Libelle', $row['Libelle']);
				$this->m_tpl->assign('Lieu', $row['Lieu']);
				$this->m_tpl->assign('Plan_eau', $row['Plan_eau']);
				$this->m_tpl->assign('Departement', $row['Departement']);
				$this->m_tpl->assign('Responsable_insc', $row['Responsable_insc']);
				$this->m_tpl->assign('Responsable_R1', $row['Responsable_R1']);
				$this->m_tpl->assign('Organisateur', $row['Organisateur']);
				$this->m_tpl->assign('Delegue', $row['Delegue']);
			}
		}
		if (count($listJournees) > 0) {
			$in = str_repeat('?,', count($listJournees) - 1) . '?';
			$sql2 = "SELECT COUNT(Id) nbMatchs 
				FROM kp_match 
				WHERE Id_journee IN ($in) ";
			$result2 = $myBdd->pdo->prepare($sql2);
			$result2->execute($listJournees);
			$row2 = $result2->fetch();

			$this->m_tpl->assign('nbMatchs', $row2['nbMatchs']);
		}
		$this->m_tpl->assign('arrayJournees', $arrayJournees);


		// Chargement des schémas
		$recherche_nb_equipes = utyGetSession('recherche_nb_equipes', 0);
		$recherche_nb_equipes = (int) utyGetPost('recherche_nb_equipes', $recherche_nb_equipes);
		$_SESSION['recherche_nb_equipes'] = $recherche_nb_equipes;
		$this->m_tpl->assign('recherche_nb_equipes', $recherche_nb_equipes);

		$recherche_tri = utyGetSession('recherche_tri', 'saison');
		$recherche_tri = utyGetPost('recherche_tri', $recherche_tri);
		$_SESSION['recherche_tri'] = $recherche_tri;
		$this->m_tpl->assign('recherche_tri', $recherche_tri);

		if ($recherche_nb_equipes != 0) {
			$arraySchemas = array();
			$sql = "SELECT c.*, g.id 
				FROM kp_competition c, kp_groupe g 
				WHERE 1=1 
				AND c.Code_typeclt = 'CP' 
				AND c.Nb_equipes > 0 
				AND c.Nb_equipes = ? 
				AND c.Code_ref = g.Groupe 
				ORDER BY c.Code_saison DESC, g.Id, COALESCE(c.Code_ref, 'z'), 
					c.Code_tour, c.GroupOrder, c.Code ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($recherche_nb_equipes));
			while ($row = $result->fetch()) {
				$sql2 = "SELECT COUNT(m.Id) nbMatchs 
					FROM kp_match m, kp_journee j 
					WHERE j.Id = m.Id_journee 
					AND j.Code_competition = ? 
					AND j.Code_saison = ? ";
				$result2 = $myBdd->pdo->prepare($sql2);
				$result2->execute(array($row["Code"], $row["Code_saison"]));
				$row2 = $result2->fetch();
				$nbMatchs = $row2['nbMatchs'];

				if ($nbMatchs > 0) {
					array_push($arraySchemas, array(
						'Code' => $row["Code"],
						'Code_saison' => $row['Code_saison'], 'Code_niveau' => $row["Code_niveau"],
						'Libelle' => $row["Libelle"], 'Soustitre' => $row["Soustitre"],
						'Soustitre2' => $row["Soustitre2"],
						'Code_ref' => $row["Code_ref"], 'GroupOrder' => $row["GroupOrder"],
						'codeTypeClt' => $row["Code_typeclt"], 'Web' => $row["Web"],
						'ToutGroup' => $row["ToutGroup"], 'TouteSaisons' => $row["TouteSaisons"],
						'En_actif' => $row['En_actif'], 'Titre_actif' => $row['Titre_actif'],
						'Logo_actif' => $row['Logo_actif'], 'Sponsor_actif' => $row['Sponsor_actif'],
						'Kpi_ffck_actif' => $row['Kpi_ffck_actif'],
						'Age_min' => $row["Age_min"], 'Age_max' => $row["Age_max"],
						'Sexe' => $row["Sexe"], 'Points' => $row["Points"], 'Statut' => $row['Statut'],
						'Code_tour' => $row["Code_tour"], 'Nb_equipes' => $row["Nb_equipes"],
						'Verrou' => $row["Verrou"], 'Qualifies' => $row["Qualifies"],
						'Elimines' => $row["Elimines"],
						'commentairesCompet' => $row["commentairesCompet"],
						'nbMatchs' => $nbMatchs
					));
				}
			}
			if ($recherche_tri !== 'saison') {
				$column_nbMatchs = array_column($arraySchemas, 'nbMatchs');
				$column_code_saison  = array_column($arraySchemas, 'Code_saison');
				array_multisort($column_nbMatchs, SORT_DESC, $column_code_saison, SORT_DESC, $arraySchemas);
			}
			$this->m_tpl->assign('arraySchemas', $arraySchemas);
		}
	}

	function Ok()
	{
		$myBdd = new MyBdd();

		$codeSaison = $myBdd->GetActiveSaison();
		$codeCompet = utyGetSession('codeCompet');
		$saisonOrigine = utyGetSession('saisonOrigine', $codeSaison);
		$competOrigine = utyGetSession('competOrigine', $codeCompet);
		$saisonDestination = utyGetSession('saisonDestination', $codeSaison);
		$competDestination = utyGetSession('competDestination', $codeCompet);

		(utyGetPost('Date_debut') != '%') ? $Date_debut = utyDateFrToUs(utyGetPost('Date_debut')) : $Date_debut = '%';
		(utyGetPost('Date_fin') != '%') ? $Date_fin = utyDateFrToUs(utyGetPost('Date_fin')) : $Date_fin = '%';
		(utyGetPost('Date_origine') != '%') ? $Date_origine = utyDateFrToUs(utyGetPost('Date_origine')) : $Date_origine = '%';
		$Nom = $myBdd->RealEscapeString(utyGetPost('Nom'));
		$Libelle = $myBdd->RealEscapeString(utyGetPost('Libelle'));
		$Lieu = $myBdd->RealEscapeString(utyGetPost('Lieu'));
		$Plan_eau = $myBdd->RealEscapeString(utyGetPost('Plan_eau'));
		$Departement = utyGetPost('Departement');
		$Responsable_insc = utyGetPost('Responsable_insc');
		$Responsable_R1 = utyGetPost('Responsable_R1');
		$Organisateur = $myBdd->RealEscapeString(utyGetPost('Organisateur'));
		$Delegue = $myBdd->RealEscapeString(utyGetPost('Delegue'));

		$init1erTour = utyGetPost('init1erTour');

		if ($Date_debut != '%' && $Date_origine != '%') {
			$d1 = strtotime($Date_debut . ' 00:00:00');
			$d2 = strtotime($Date_origine . ' 00:00:00');
			$diffdate = round(($d1 - $d2) / 60 / 60 / 24);
		} else {
			$diffdate = 0;
		}

		$arrayJournees = array();
		$sql = "SELECT Id, Code_competition, Code_saison, Phase, Niveau, Etape, Nbequipes, 
			Date_debut, Date_fin, Nom, Libelle, `Type`, Lieu, Plan_eau, Departement, 
			Responsable_insc, Responsable_R1, Organisateur, Delegue 
			FROM kp_journee 
			WHERE Code_competition = ? 
			AND Code_saison = ? 
			ORDER BY Id ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($competOrigine, $saisonOrigine));

		$sql2a = "CREATE TEMPORARY TABLE kp_tmp (Id int(11) AUTO_INCREMENT, Num int(11) default NULL, PRIMARY KEY  (`Id`)); ";
		$myBdd->pdo->query($sql2a);
		$sql3a = "CREATE TEMPORARY TABLE kp_tmp2 (Id int(11) AUTO_INCREMENT, Num int(11) default NULL, PRIMARY KEY  (`Id`)); ";
		$myBdd->pdo->query($sql3a);

		$sql2 = "INSERT INTO kp_tmp (Num) 
			SELECT DISTINCT Id 
			FROM kp_competition_equipe 
			WHERE Code_compet = ? 
			AND Code_saison = ? 
			ORDER BY Poule, Tirage, Libelle; ";
		$result2 = $myBdd->pdo->prepare($sql2);
		$result2->execute(array($competOrigine, $saisonOrigine));

		$sql3 = "INSERT INTO kp_tmp2 (Num) 
			SELECT DISTINCT Id 
			FROM kp_competition_equipe 
			WHERE Code_compet = ? 
			AND Code_saison = ? 
			ORDER BY Poule, Tirage, Libelle; ";
		$result3 = $myBdd->pdo->prepare($sql3);
		$result3->execute(array($competOrigine, $saisonOrigine));


		try {
			$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$myBdd->pdo->beginTransaction();

			while ($row = $result->fetch()) {
				if ($Date_debut == '%') $Date_debut = $row['Date_debut'];
				if ($Date_fin == '%') $Date_fin = $row['Date_fin'];
				if ($Nom == '%') $Nom = $row['Nom'];
				if ($Libelle == '%') $Libelle = $row['Libelle'];
				if ($Lieu == '%') $Lieu = $row['Lieu'];
				if ($Plan_eau == '%') $Plan_eau = $row['Plan_eau'];
				if ($Departement == '%') $Departement = $row['Departement'];
				if ($Responsable_insc == '%') $Responsable_insc = $row['Responsable_insc'];
				if ($Responsable_R1 == '%') $Responsable_R1 = $row['Responsable_R1'];
				if ($Organisateur == '%') $Organisateur = $row['Organisateur'];
				if ($Delegue == '%') $Delegue = $row['Delegue'];

				$nextIdJournee = $myBdd->GetNextIdJournee();
				$sql1 = "INSERT INTO kp_journee 
					(Id, Code_competition, code_saison, Phase, Niveau, Etape, Nbequipes, `Type`, 
					Date_debut, Date_fin, Nom, Libelle, Lieu, Plan_eau, Departement, Responsable_insc, 
					Responsable_R1, Organisateur, Delegue) 
					VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ";
				$result1 = $myBdd->pdo->prepare($sql1);
				$result1->execute(array(
					$nextIdJournee, $competDestination, $saisonDestination, $row['Phase'],
					$row['Niveau'], $row['Etape'], $row['Nbequipes'], $row['Type'], $Date_debut,
					$Date_fin, $Nom, $Libelle, $Lieu, $Plan_eau, $Departement, $Responsable_insc,
					$Responsable_R1, $Organisateur, $Delegue
				));

				$sql4 = "INSERT INTO kp_match 
					(Id_journee, Libelle, Date_match, Heure_match, Terrain, Numero_ordre, `Type`) 
					SELECT ?, ";
				if ($row['Niveau'] <= 1 && $init1erTour == 'init') {
					$sql4 .= "CONCAT('[T', ta.Id, '/T', tb.Id, ']'), ";
				} else {
					$sql4 .= "m.Libelle, ";
				}
				$sql4 .= "DATE_ADD(m.Date_match, INTERVAL + ? DAY), m.Heure_match, 
					m.Terrain, m.Numero_ordre, m.Type 
					FROM kp_match m ";
				if ($row['Niveau'] <= 1 && $init1erTour == 'init') {
					$sql4 .= ", kp_tmp ta, kp_tmp2 tb ";
				}
				$sql4 .= "WHERE m.Id_journee = ? ";
				if ($row['Niveau'] <= 1 && $init1erTour == 'init') {
					$sql4 .= "AND ta.Num=m.Id_equipeA 
						AND tb.Num=m.Id_equipeB ";
				}
				$result4 = $myBdd->pdo->prepare($sql4);
				$result4->execute(array($nextIdJournee, $diffdate, $row['Id']));

				$myBdd->utyJournal('Ajout journee', $codeSaison, $competDestination, null, $nextIdJournee);
			}

			$myBdd->pdo->commit();
		} catch (Exception $e) {
			$myBdd->pdo->rollBack();
			utySendMail("[KPI] Erreur SQL", "Ajout journee, $codeSaison, $competDestination, $nextIdJournee" . '\r\n' . $e->getMessage());

			return "La requête ne peut pas être exécutée !\\nCannot execute query!";
		}

		if (isset($_SESSION['ParentUrl'])) {
			$target = $_SESSION['ParentUrl'];
			header("Location: http://" . $_SERVER['HTTP_HOST'] . $target);
			exit;
		}
		return;
	}

	function Cancel()
	{
		if (isset($_SESSION['ParentUrl'])) {
			$target = $_SESSION['ParentUrl'];
			header("Location: http://" . $_SERVER['HTTP_HOST'] . $target);
			exit;
		}
	}

	function __construct()
	{
		MyPageSecure::MyPageSecure(3);

		$alertMessage = '';

		$Cmd = utyGetPost('Cmd');

		if (strlen($Cmd) > 0) {
			if ($Cmd == 'Ok') {
				($_SESSION['Profile'] <= 3) ? $alertMessage = $this->Ok() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
			}

			if ($Cmd == 'Cancel') {
				($_SESSION['Profile'] <= 10) ? $this->Cancel() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
			}

			if ($alertMessage == '') {
				header("Location: http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
				exit;
			}
		}

		$this->SetTemplate("Copie de compétition", "Competitions", false);
		$this->Load();
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplate('GestionCopieCompetition');
	}
}

$page = new GestionCopieCompetition();
