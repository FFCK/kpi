<?php
include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion des Equipes
class GestionEquipe extends MyPageSecure
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

		$_SESSION['updatecell_tableName'] = 'kp_competition_equipe';
		$_SESSION['updatecell_where'] = 'Where Id = ';
		$_SESSION['updatecell_document'] = 'formEquipe';

		$codeCompet = utyGetSession('codeCompet');
		$codeCompet = utyGetPost('competition', $codeCompet);
		$_SESSION['codeCompet'] = $codeCompet;

		if ($codeCompet == '*') {
			$codeCompet = '';
		}

		$codeSaison = $codeCompet === 'POOL' ? 1000 : $myBdd->GetActiveSaison();
		$this->m_tpl->assign('codeSaison', $codeSaison);

		//Filtre affichage type compet
		$AfficheCompet = utyGetSession('AfficheCompet', '');
		$AfficheCompet = utyGetPost('AfficheCompet', $AfficheCompet);
		$_SESSION['AfficheCompet'] = $AfficheCompet;
		$this->m_tpl->assign('AfficheCompet', $AfficheCompet);

		// Chargement des Compétitions ...
		$label = $myBdd->getSections();
		$arrayCompetition = array();
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
		$sql = "SELECT c.*, g.section, g.ordre, g.id 
			FROM kp_competition c, kp_groupe g 
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
		$i = -1;
		$j = '';
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

			// Si $codeCompet Vide on prend le premier de la liste ...
			if ((strlen($codeCompet) == 0) && ($i == 0)) {
				$codeCompet = $row["Code"];
			}

			if ($j != $row['section']) {
				$i++;
				$arrayCompetition[$i]['label'] = $label[$row['section']];
			}
			if ($row["Code"] == $codeCompet) {
				$row['selected'] = 'selected';
				$this->m_tpl->assign('Code_niveau', $row["Code_niveau"]);
				$this->m_tpl->assign('Statut', $row["Statut"]);
				if ($row["Verrou"] == 'O') {
					$Verrou = 'O';
				} else {
					$Verrou = 'N';
				}
				$this->m_tpl->assign('Verrou', $Verrou);
			} else {
				$row['selected'] = '';
			}
			$j = $row['section'];
			$arrayCompetition[$i]['options'][] = $row;
		}
		$this->m_tpl->assign('arrayCompetition', $arrayCompetition);
		$this->m_tpl->assign('codeCompet', $codeCompet);


		// Chargement des Equipes ...
		$arrayEquipe = array();

		if (strlen($codeCompet) > 0) {
			$sql = "SELECT ce.Id, ce.Libelle, ce.Code_club, ce.Numero, ce.Poule, ce.Tirage, 
				c.Code_comite_dep, c.Libelle Club, ce.logo, ce.color1, ce.color2, ce.colortext
				FROM kp_competition_equipe ce, kp_club c 
				WHERE ce.Code_compet = ? 
				AND ce.Code_saison = ?
				AND ce.Code_club = c.Code 
				ORDER BY ce.Poule, ce.Tirage, ce.Libelle, ce.Id ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($codeCompet, $codeSaison));

			$num_results = 0;
			while ($row = $result->fetch()) {
				$num_results++;
				$nbMatchs = 0;
				$sql2  = "SELECT count(ce.Id) nbMatchs 
					FROM kp_competition_equipe ce, kp_match m, kp_journee j 
					WHERE ce.Code_compet = :codeCompet 
					AND ce.Code_saison = :codeSaison 
					AND j.Code_competition = ce.Code_compet 
					AND j.Code_saison = ce.Code_saison 
					AND j.Id = m.Id_journee 
					AND ce.Id = :idEquipe 
					AND (ce.Id = m.Id_equipeA 
						OR ce.Id = m.Id_equipeB) ";
				$result2 = $myBdd->pdo->prepare($sql2);
				$result2->execute(array(
					':codeCompet' => $codeCompet,
					':codeSaison' => $codeSaison,
					':idEquipe' => $row['Id']
				));

				$row2 = $result2->fetch();
				$nbMatchs = $row2['nbMatchs'];

				if (strlen($row['Code_comite_dep']) > 3) {
					$row['Code_comite_dep'] = 'FRA';
				}
				if ($row['Tirage'] != 0 or $row['Poule'] != '') {
					$this->m_tpl->assign('Tirage', 'ok');
				}

				array_push($arrayEquipe, array(
					'Id' => $row['Id'], 'Libelle' => $row['Libelle'],
					'Code_club' => $row['Code_club'], 'Numero' => $row['Numero'], 'Club' => $row['Club'],
					'Poule' => $row['Poule'], 'Tirage' => $row['Tirage'], 'nbMatchs' => $nbMatchs,
					'Code_comite_dep' => $row['Code_comite_dep'], 'logo' => $row['logo'],
					'color1' => $row['color1'], 'color2' => $row['color2'], 'colortext' => $row['colortext']
				));
			}
		}
		$this->m_tpl->assign('arrayEquipe', $arrayEquipe);

		//Mise à jour du nombre d'équipe de la compétition
		$sql  = "UPDATE kp_competition 
			SET Nb_equipes = ?  
			WHERE Code = ?  
			AND Code_saison = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($num_results, $codeCompet, $codeSaison));

		// Les comites et les clubs ...
		$codeComiteReg = utyGetSession('codeComiteReg', '*');
		$codeComiteReg = utyGetPost('comiteReg', $codeComiteReg);
		if (strlen($codeComiteReg) == 0) {
			$codeComiteReg = '*';
		}

		$codeComiteDep = utyGetSession('codeComiteDep', '*');
		$codeComiteDep = utyGetPost('comiteDep', $codeComiteDep);
		if (strlen($codeComiteDep) == 0) {
			$codeComiteDep = '*';
		}

		$codeClub = utyGetSession('codeClub', '*');
		$codeClub = utyGetPost('club', $codeClub);
		if (strlen($codeClub) == 0) {
			$codeClub = '*';
		}

		$ParamCmd = utyGetPost('ParamCmd', false);
		if ($ParamCmd)	// @COSANDCO_WAMPSER
		{
			if ($codeComiteReg == '*' && $ParamCmd == 'changeComiteReg') {
				$codeComiteDep = '*';
				$codeClub = '*';
			}

			if ($codeComiteDep == '*' && $ParamCmd == 'changeComiteDep') {
				$codeClub = '*';
			}
		}

		$_SESSION['codeComiteReg'] = $codeComiteReg;
		$_SESSION['codeComiteDep'] = $codeComiteDep;
		$_SESSION['codeClub'] = $codeClub;

		// Chargement des Comites Régionaux ...
		$sql  = "SELECT Code, Libelle 
			FROM kp_cr 
			ORDER BY Code ";
		$arrayComiteReg = array();

		if ('*' == $codeComiteReg) {
			array_push($arrayComiteReg, array('Code' => '*', 'Libelle' => '* - ' . $lang['Tous'], 'Selected' => 'SELECTED'));
		} else {
			array_push($arrayComiteReg, array('Code' => '*', 'Libelle' => '* - ' . $lang['Tous'], 'Selected' => ''));
		}

		foreach ($myBdd->pdo->query($sql) as $row) {
			if ($row["Code"] == $codeComiteReg) {
				array_push($arrayComiteReg, array('Code' => $row["Code"], 'Libelle' => $row["Code"] . " - " . $row["Libelle"], 'Selected' => 'SELECTED'));
			} else {
				array_push($arrayComiteReg, array('Code' => $row["Code"], 'Libelle' => $row["Code"] . " - " . $row["Libelle"], 'Selected' => ''));
			}
		}

		$this->m_tpl->assign('arrayComiteReg', $arrayComiteReg);

		// Chargement des Comites Departementaux ...
		$arrayComiteDep = array();
		if ('*' == $codeComiteDep) {
			array_push($arrayComiteDep, array('Code' => '*', 'Libelle' => '* - ' . $lang['Tous'], 'Selected' => 'SELECTED'));
			// $bSelectCombo = true;
		} else {
			array_push($arrayComiteDep, array('Code' => '*', 'Libelle' => '* - ' . $lang['Tous'], 'Selected' => ''));
		}

		if ($codeComiteReg != '*') {
			$sql = "SELECT Code, Libelle 
				FROM kp_cd 
				WHERE Code_comite_reg = ?
				ORDER BY Code ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($codeComiteReg));
		} else {
			$sql = "SELECT Code, Libelle 
				FROM kp_cd 
				ORDER BY Code ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute();
		}

		while ($row = $result->fetch()) {
			if ($row["Code"] == $codeComiteDep) {
				array_push($arrayComiteDep, array('Code' => $row['Code'], 'Libelle' => $row['Code'] . " - " . $row['Libelle'], 'Selected' => 'SELECTED'));
			} else {
				array_push($arrayComiteDep, array('Code' => $row['Code'], 'Libelle' => $row['Code'] . " - " . $row['Libelle'], 'Selected' => ''));
			}
		}

		$this->m_tpl->assign('arrayComiteDep', $arrayComiteDep);

		// Chargement des Clubs ...
		$arrayClub = array();
		if ('*' == $codeClub) {
			array_push($arrayClub, array('Code' => '*', 'Libelle' => '* - ' . $lang['Tous'], 'Selected' => 'SELECTED'));
		} else {
			array_push($arrayClub, array('Code' => '*', 'Libelle' => '* - ' . $lang['Tous'], 'Selected' => ''));
		}

		$arrayM = [];
		$sql = "SELECT c.Code, c.Libelle 
			FROM kp_club c, kp_cd cd 
			WHERE c.Code_comite_dep = cd.Code ";
		if ($codeComiteReg != '*') {
			$sql .= "AND cd.Code_comite_reg = ? ";
			$arrayM = array_merge($arrayM, [$codeComiteReg]);
		}
		if ($codeComiteDep != '*') {
			$sql .= "AND c.Code_comite_dep = ? ";
			$arrayM = array_merge($arrayM, [$codeComiteDep]);
		}
		$sql .= "ORDER BY c.Code ";

		$result = $myBdd->pdo->prepare($sql);
		$result->execute($arrayM);
		while ($row = $result->fetch()) {
			if ($row["Code"] == $codeClub) {
				array_push($arrayClub, array('Code' => $row['Code'], 'Libelle' => $row['Code'] . ' - ' . $row['Libelle'], 'Selected' => 'SELECTED'));
			} else {
				array_push($arrayClub, array('Code' => $row['Code'], 'Libelle' => $row['Code'] . ' - ' . $row['Libelle'], 'Selected' => ''));
			}
		}

		$this->m_tpl->assign('arrayClub', $arrayClub);

		// Chargement des Equipes Historique ...
		$arrayHistoEquipe = array();
		array_push($arrayHistoEquipe, array('Numero' => 0, 'Libelle' => '=> ' . $lang['NOUVELLE_EQUIPE'] . '...'));
		array_push($arrayHistoEquipe, array('Numero' => '', 'Libelle' => ''));

		$arrayM = [];
		if ($codeComiteReg != '98') {
			$sql = "SELECT e.Numero, e.Libelle, e.Code_club 
				FROM kp_equipe e, kp_club c, kp_cd cd 
				WHERE e.Code_club = c.Code 
				AND c.Code_comite_dep = cd.Code 
				AND cd.Code_comite_reg != '98' ";
			if ($codeComiteReg != '*') {
				$sql .= "AND cd.Code_comite_reg = ? ";
				$arrayM = array_merge($arrayM, [$codeComiteReg]);
			}
			if ($codeComiteDep != '*') {
				$sql .= "AND c.Code_comite_dep = ? ";
				$arrayM = array_merge($arrayM, [$codeComiteDep]);
			}
			if ($codeClub != '*') {
				$sql .= "AND e.Code_club = ? ";
				$arrayM = array_merge($arrayM, [$codeClub]);
			}
			$sql .= "ORDER BY e.Libelle ";

			$result = $myBdd->pdo->prepare($sql);
			$result->execute($arrayM);
			$num_results = $result->rowCount();
			array_push($arrayHistoEquipe, array('Numero' => '', 'Libelle' => '==== FRANCE (' . $num_results . ' ' . $lang['equipes'] . ') ====', 'Code_club' => ''));
			while ($row = $result->fetch()) {
				array_push($arrayHistoEquipe, array('Numero' => $row['Numero'], 'Libelle' => $row['Libelle'], 'Code_club' => $row['Code_club']));
			}
			array_push($arrayHistoEquipe, array('Numero' => '', 'Libelle' => '', 'Code_club' => ''));
		}

		$arrayM = [];
		if ($codeComiteReg == '98' or $codeComiteReg == '*') {
			$sql = "SELECT e.Numero, e.Libelle, e.Code_club 
				FROM kp_equipe e, kp_club c, kp_cd cd 
				WHERE e.Code_club = c.Code 
				AND c.Code_comite_dep = cd.Code 
				AND cd.Code_comite_reg = '98' ";
			if ($codeComiteReg != '*') {
				$sql .= "AND cd.Code_comite_reg = ? ";
				$arrayM = array_merge($arrayM, [$codeComiteReg]);
			}
			if ($codeComiteDep != '*') {
				$sql .= "AND c.Code_comite_dep = ? ";
				$arrayM = array_merge($arrayM, [$codeComiteDep]);
			}
			if ($codeClub != '*') {
				$sql .= "AND e.Code_club = ? ";
				$arrayM = array_merge($arrayM, [$codeClub]);
			}
			$sql .= "ORDER BY e.Libelle ";

			$result = $myBdd->pdo->prepare($sql);
			$result->execute($arrayM);
			$num_results = $result->rowCount();
			array_push($arrayHistoEquipe, array('Numero' => '', 'Libelle' => '==== INTERNATIONAL (' . $num_results . ' ' . $lang['equipes'] . ') ====', 'Code_club' => ''));
			while ($row = $result->fetch()) {
				array_push($arrayHistoEquipe, array('Numero' => $row['Numero'], 'Libelle' => $row['Libelle'], 'Code_club' => $row['Code_club']));
			}
		}
		$this->m_tpl->assign('arrayHistoEquipe', $arrayHistoEquipe);
	}

	function Add()
	{
		$myBdd = $this->myBdd;

		$codeCompet = utyGetPost('competition');
		$codeSaison = $codeCompet === 'POOL' ? 1000 : $myBdd->GetActiveSaison();
		$libelleEquipe = utyGetPost('libelleEquipe');
		$histoEquipe = utyGetPost('histoEquipe');
		$codeClub = utyGetPost('club');
		$insertValue = '';

		if ($codeCompet == '' or $codeCompet == '*') {
			return 'Aucune competition sélectionnée';
		}

		if (is_array($histoEquipe)) {
			foreach ($histoEquipe as $selectValue) {
				try {
					$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					$myBdd->pdo->beginTransaction();

					if ((int) $selectValue == 0) {
						// Inscription Manuelle ...
						if (strlen($libelleEquipe) > 0 && strlen($codeClub) > 0 && $codeClub !== '*') {
							$sql  = "INSERT INTO kp_equipe (Libelle, Code_club) 
								VALUES (?, ?) ";
							$result = $myBdd->pdo->prepare($sql);
							$result->execute(array($libelleEquipe, $codeClub));
							$selectValue = $myBdd->pdo->lastInsertId();
						}
					}

					// if (strlen($codeClub) === 0 || $codeClub === '*') {
					// 	$sql = "SELECT Code_club
					// 	FROM kp_equipe 
					// 	WHERE Numero = ? ";
					// 	$result = $myBdd->pdo->prepare($sql);
					// 	$result->execute(array($selectValue));
					// 	$row = $result->fetch();
					// 	$club = $row['Code_club'];
					// } else {
					// 	$club = $codeClub;
					// }

					// $logo = utySearchLogoFile($club);

					$sql = "INSERT INTO kp_competition_equipe 
						(Code_compet, Code_saison, Libelle, Code_club, Numero, logo, color1, color2, colortext) 
						SELECT ?, ?, Libelle, Code_club, Numero, logo, color1, color2, colortext
						FROM kp_equipe 
						WHERE Numero = ? ";
					$result = $myBdd->pdo->prepare($sql);
					$result->execute(array($codeCompet, $codeSaison, $selectValue));

					if ($insertValue != '') {
						$insertValue .= ',';
					}
					$insertValue .= $selectValue;

					$myBdd->pdo->commit();
				} catch (Exception $e) {
					$myBdd->pdo->rollBack();
					utySendMail("[KPI] Erreur SQL", "Ajout equipe $codeSaison, $codeCompet, $insertValue, "
						. '\r\n' . $e->getMessage());

					return "La requête SQL ne peut pas être exécutée !\\nCannot execute query!";
				}
				$_SESSION['codeCompet'] = $codeCompet;
				$_SESSION['codeComiteReg'] = utyGetPost('comiteReg');
				$_SESSION['codeComiteDep'] = utyGetPost('comiteDep');
				$_SESSION['codeClub'] = $codeClub;
			}
		}

		$myBdd->utyJournal('Ajout equipe', $codeSaison, $codeCompet, null, null, null, $insertValue);
		return;
	}

	function UpdateLogos()
	{
		$myBdd = $this->myBdd;

		$codeCompet = utyGetPost('competition');
		$codeSaison = $codeCompet === 'POOL' ? 1000 : $myBdd->GetActiveSaison();

		$sql = "SELECT Id, Numero, Code_club, logo
			FROM kp_competition_equipe 
			WHERE Code_compet = ?
			AND Code_saison = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($codeCompet, $codeSaison));

		try {
			$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$myBdd->pdo->beginTransaction();

			while ($row = $result->fetch()) {

				if ($row['logo'] === null) {

					if (strlen($row['Code_club']) === 4 && is_file('../img/KIP/logo/' . $row['Code_club'] . '-logo.png')) {
						$logo = 'KIP/logo/' . $row['Code_club'] . '-logo.png';
					} elseif (strlen($row['Code_club']) !== 4 && is_file('../img/Nations/' . substr($row['Code_club'], 0, 3) . '.png')) {
						$logo = 'Nations/' . substr($row['Code_club'], 0, 3) . '.png';
					} else {
						continue;
					}

					$sql2 = "UPDATE kp_competition_equipe 
						SET logo = ?
						WHERE Id = ? ";
					$result2 = $myBdd->pdo->prepare($sql2);
					$result2->execute(array($logo, $row['Id']));

					$sql2 = "UPDATE kp_equipe 
						SET logo = ?
						WHERE Numero = ? ";
					$result2 = $myBdd->pdo->prepare($sql2);
					$result2->execute(array($logo, $row['Numero']));
				} else {
					$logo = $row['logo'];
				}

				$data = file_get_contents('../img/' . $logo);
				$jsonBase64 = json_encode([
					'team' => $row['Numero'],
					'club' => $row['Code_club'],
					'logo' => base64_encode($data)
				], JSON_UNESCAPED_SLASHES);
				file_put_contents('../live/cache/logos/logo_' . $row['Id'] . '.json', $jsonBase64);
			}

			$myBdd->pdo->commit();
		} catch (Exception $e) {
			$myBdd->pdo->rollBack();
			utySendMail("[KPI] Erreur SQL", "Update logo equipes $codeSaison, $codeCompet, "
				. '\r\n' . $e->getMessage());

			return "La requête SQL ne peut pas être exécutée !\\nCannot execute query!";
		}

		$myBdd->utyJournal('Update logo equipes', $codeSaison, $codeCompet, null, null, null);
		return;
	}

	function Add2()
	{
		$myBdd = $this->myBdd;

		$codeCompet = utyGetPost('competition');
		$codeSaison = $codeCompet === 'POOL' ? 1000 : $myBdd->GetActiveSaison();
		$EquipeNum = utyGetPost('EquipeNum');
		$EquipeNom = utyGetPost('EquipeNom');
		$checkCompo = utyGetPost('checkCompo');
		$plEquipe = utyGetPost('plEquipe', '');
		$tirEquipe = utyGetPost('tirEquipe', 0);
		$cltChEquipe = utyGetPost('cltChEquipe', 0);
		$cltCpEquipe = utyGetPost('cltCpEquipe', 0);

		if ($EquipeNum != '') {
			if ($codeCompet == '' or $codeCompet == '*') {
				return 'Aucune competition sélectionnée';
			}

			try {
				$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$myBdd->pdo->beginTransaction();

				$sql  = "INSERT INTO kp_competition_equipe 
					(Code_compet, Code_saison, Libelle, Code_club, Numero, Poule, Tirage, Clt, CltNiveau, logo, color1, color2, colortext)
					SELECT ?, ?, Libelle, Code_club, Numero, ?, ?, ?, ?, logo, color1, color2, colortext 
					FROM kp_equipe 
					WHERE Numero = ? ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(
					array(
						$codeCompet, $codeSaison, $plEquipe, $tirEquipe,
						$cltChEquipe, $cltCpEquipe, $EquipeNum
					)
				);
				$EquipeId = $myBdd->pdo->lastInsertId();

				if ($checkCompo != '') {
					$checkCompo = explode('-', $checkCompo);
					// Insertion des Joueurs Equipes ...
					$sql  = "INSERT INTO kp_competition_equipe_joueur 
						(Id_equipe, Matric, Nom, Prenom, Sexe, Categ, Numero, Capitaine) 
						SELECT :EquipeId, a.Matric, a.Nom, a.Prenom, a.Sexe, d.id, a.Numero, a.Capitaine 
						FROM kp_competition_equipe_joueur a, kp_competition_equipe b, 
						kp_competition_equipe c, kp_categorie d, kp_licence e 
						WHERE a.Id_equipe = b.Id 
						AND a.Matric = e.Matric 
						AND a.Id_equipe = c.Id 
						AND b.Numero = :EquipeNum 
						AND b.Code_compet = :checkCompo1 
						AND b.Code_saison = :checkCompo0 
						AND :checkCompo2 - Year(e.Naissance) BETWEEN d.age_min AND d.age_max ";
					$result = $myBdd->pdo->prepare($sql);
					$result->execute(array(
						':EquipeId' => $EquipeId,
						':EquipeNum' => $EquipeNum,
						':checkCompo1' => $checkCompo[1],
						':checkCompo2' => $checkCompo[0],
						':checkCompo0' => $checkCompo[0]
					));
				}

				$myBdd->pdo->commit();
			} catch (Exception $e) {
				$myBdd->pdo->rollBack();
				utySendMail("[KPI] Erreur SQL", "Ajout equipe $codeSaison, $codeCompet, $EquipeNom" . '\r\n' . $e->getMessage());

				return "La requête SQL ne peut pas être exécutée !\\nCannot execute query!";
			}
		}

		$myBdd->utyJournal('Ajout equipe', $codeSaison, $codeCompet, null, null, null, $EquipeNom);
		return;
	}

	function Tirage()
	{
		$myBdd = $this->myBdd;

		$codeCompet = utyGetPost('competition');
		$codeSaison = $codeCompet === 'POOL' ? 1000 : $myBdd->GetActiveSaison();
		$equipeTirage = utyGetPost('equipeTirage');
		$pouleTirage = utyGetPost('pouleTirage');
		$ordreTirage = utyGetPost('ordreTirage');

		$sql = "UPDATE kp_competition_equipe 
			SET Tirage = :ordreTirage, Poule = :pouleTirage 
			WHERE Code_compet = :codeCompet 
			AND Code_saison = :codeSaison 
			AND Id = :equipeTirage ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array(
			':ordreTirage' => $ordreTirage,
			':pouleTirage' => $pouleTirage,
			':codeCompet' => $codeCompet,
			':codeSaison' => $codeSaison,
			':equipeTirage' => $equipeTirage
		));

		$myBdd->utyJournal('Tirage au sort', $codeSaison, $codeCompet, null, null, null, $equipeTirage . ' -> ' . $ordreTirage);
		return;
	}

	function Remove()
	{
		$myBdd = $this->myBdd;
		$codeSaison = $myBdd->GetActiveSaison();

		$ParamCmd = utyGetPost('ParamCmd', '');

		$arrayParam = explode(',', $ParamCmd);
		if (count($arrayParam) == 0) {
			return;
		} // Rien à Detruire ...

		$in = str_repeat('?,', count($arrayParam) - 1) . '?';

		try {
			$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$myBdd->pdo->beginTransaction();

			$sql = "DELETE FROM kp_competition_equipe_joueur 
				WHERE Id_equipe IN ($in)";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute($arrayParam);

			$sql = "DELETE FROM kp_competition_equipe 
				WHERE Id IN ($in)";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute($arrayParam);

			$myBdd->pdo->commit();
		} catch (Exception $e) {
			$myBdd->pdo->rollBack();
			utySendMail("[KPI] Erreur SQL", 'Suppression  equipe' . '\r\n' . $e->getMessage());

			return "La requête SQL ne peut pas être exécutée !\\nCannot execute query!";
		}

		$myBdd->utyJournal('Suppression  equipes', $codeSaison, utyGetPost('codeCompet'), null, null, null, $ParamCmd);
		return 'Suppression effectuée';
	}

	function Duplicate($bDelete)
	{
		$myBdd = $this->myBdd;
		$codeCompet = utyGetPost('competition');
		$codeCompetRef = utyGetPost('competitionRef');
		$codeSaison = $myBdd->GetActiveSaison();

		if ((strlen($codeCompet) > 0) && (strlen($codeCompetRef) > 0)) {
			$myBdd = $this->myBdd;

			try {
				$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$myBdd->pdo->beginTransaction();

				if ($bDelete) {
					// Suppression des Joueurs Equipes 
					$sql  = "DELETE FROM kp_competition_equipe_joueur 
						WHERE Id_equipe IN (
							SELECT a.Id 
							FROM kp_competition_equipe a 
							WHERE a.Code_compet = ? 
							AND a.Code_saison = ? )";
					$result = $myBdd->pdo->prepare($sql);
					$result->execute(array($codeCompet, $codeSaison));

					// Suppression des Equipes 
					$sql = "DELETE FROM kp_competition_equipe 
						WHERE Code_compet = ? 
						AND Code_saison = ? ";
					$result = $myBdd->pdo->prepare($sql);
					$result->execute(array($codeCompet, $codeSaison));
				}

				// Insertion des Equipes ...
				$sql  = "INSERT INTO kp_competition_equipe 
					(Code_compet,Code_saison, Libelle, Code_club, Numero, Id_dupli) 
					SELECT ?, Code_saison, Libelle, Code_club, Numero, Id 
					FROM kp_competition_equipe 
					WHERE Code_compet = ? 
					AND Code_saison = ? ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array($codeCompet, $codeCompetRef, $codeSaison));

				// Insertion des Joueurs Equipes ...
				$sql  = "INSERT INTO kp_competition_equipe_joueur 
					(Id_equipe, Matric, Nom, Prenom, Sexe, Categ, Numero, Capitaine) 
					SELECT b.Id, a.Matric, a.Nom, a.Prenom, a.Sexe, a.Categ, a.Numero, a.Capitaine 
					FROM kp_competition_equipe_joueur a, kp_competition_equipe b, kp_competition_equipe c 
					WHERE a.Id_equipe = b.Id_dupli 
					AND a.Id_equipe = c.Id 
					AND c.Code_compet = ? 
					AND c.Code_saison = ? ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array($codeCompetRef, $codeSaison));

				$myBdd->pdo->commit();
			} catch (Exception $e) {
				$myBdd->pdo->rollBack();
				utySendMail("[KPI] Erreur SQL", "Duplication  equipes, $codeSaison, $codeCompet, Depuis $codeCompetRef" . '\r\n' . $e->getMessage());

				return "La requête SQL ne peut pas être exécutée !\\nCannot execute query!";
			}

			$myBdd->utyJournal('Duplication  equipes', $codeSaison, $codeCompet, null, null, null, 'Depuis ' . $codeCompetRef);
			return 'Duplication effectuée';
		}
	}

	function __construct()
	{
		parent::__construct(10);

		$this->myBdd = new MyBdd();

		$alertMessage = '';

		$Cmd = utyGetPost('Cmd', '');

		if (strlen($Cmd) > 0) {
			if ($Cmd == 'Add') {
				($_SESSION['Profile'] <= 3) ? $alertMessage = $this->Add() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
			}

			if ($Cmd == 'Add2') {
				($_SESSION['Profile'] <= 3) ? $alertMessage = $this->Add2() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
			}

			if ($Cmd == 'Tirage') {
				($_SESSION['Profile'] <= 4) ? $alertMessage = $this->Tirage() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
			}

			if ($Cmd == 'Remove') {
				($_SESSION['Profile'] <= 3) ? $alertMessage = $this->Remove() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
			}

			if ($Cmd == 'Duplicate') {
				($_SESSION['Profile'] <= 3) ? $alertMessage = $this->Duplicate(false) : $alertMessage = 'Vous n avez pas les droits pour cette action.';
			}

			if ($Cmd == 'RemoveAndDuplicate') {
				($_SESSION['Profile'] <= 3) ? $alertMessage = $this->Duplicate(true) : $alertMessage = 'Vous n avez pas les droits pour cette action.';
			}

			if ($Cmd == 'updateLogos') {
				($_SESSION['Profile'] <= 2) ? $alertMessage = $this->UpdateLogos() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
			}

			if ($alertMessage == '') {
				header("Location: http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
				exit;
			}
		}

		$this->SetTemplate("Gestion_des_equipes", "Equipes", false);
		$this->Load();
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplate('GestionEquipe');
	}
}

$page = new GestionEquipe();
