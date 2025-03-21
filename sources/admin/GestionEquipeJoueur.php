<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion des Joueurs d'une Equipe

class GestionEquipeJoueur extends MyPageSecure
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

		$idEquipe = utyGetSession('idEquipe', -1);
		$idEquipe = utyGetGet('idEquipe', $idEquipe);
		$_SESSION['idEquipe'] = $idEquipe;

		$codeSaison = $myBdd->GetActiveSaison();
		$codeCompet = utyGetSession('codeCompet');

		$Limit_Clubs = utyGetSession('Limit_Clubs', '');
		$Limit_Clubs = explode(',', $Limit_Clubs);

		$_SESSION['parentUrl'] = $_SERVER['PHP_SELF'];

		$_SESSION['updatecell_tableName'] = 'kp_competition_equipe_joueur';
		$_SESSION['updatecell_where'] = 'Where Matric = ';
		$_SESSION['updatecell_document'] = 'formEquipeJoueur';


		// Chargement des Equipes ...
		$arrayEquipe = array();

		if (strlen($codeCompet) > 0) {
			if ($codeCompet != 'POOL') {
				$sql = "SELECT ce.Id, ce.Libelle, ce.Code_club, ce.Numero, ce.Poule, 
					ce.Tirage, c.Code_comite_dep  
					FROM kp_competition_equipe ce, kp_club c 
					WHERE ce.Code_compet = ? 
					AND ce.Code_saison = ? 
					AND ce.Code_club = c.Code 
					ORDER BY ce.Libelle, ce.Id ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array($codeCompet, $codeSaison));
			} else {
				$sql = "SELECT ce.Id, ce.Libelle, ce.Code_club, ce.Numero, ce.Poule, 
					ce.Tirage, c.Code_comite_dep 
					FROM kp_competition_equipe ce, kp_club c 
					WHERE ce.Code_compet = ? 
					AND ce.Code_club = c.Code 
					ORDER BY ce.Libelle, ce.Id ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array($codeCompet));
			}

			while ($row = $result->fetch()) {
				if (strlen($row['Code_comite_dep']) > 3)
					$row['Code_comite_dep'] = 'FRA';
				if ($row['Tirage'] != 0 or $row['Poule'] != '')
					$this->m_tpl->assign('Tirage', 'ok');
				array_push($arrayEquipe, array('Id' => $row['Id'], 'Libelle' => $row['Libelle'], 'Code_club' => $row['Code_club'], 'Numero' => $row['Numero'], 'Poule' => $row['Poule'], 'Tirage' => $row['Tirage'], 'Code_comite_dep' => $row['Code_comite_dep']));
			}
		}
		$this->m_tpl->assign('arrayEquipe', $arrayEquipe);

		// Chargement des Joueurs ...
		$arrayJoueur = array();

		$clefEntraineur = '';

		if ($idEquipe > 0) {
			// Nom de l'Equipe et de la Compétition ...
			$sql = "SELECT eq.Code_compet, eq.Code_club, eq.Code_saison, eq.Libelle, 
				cp.Verrou, cp.Statut, cp.Code_niveau 
				FROM kp_competition_equipe eq, kp_competition cp 
				WHERE eq.Code_compet = cp.Code 
				AND cp.Code_saison = ? 
				AND eq.Id = ? ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($codeSaison, $idEquipe));
			$num_results = $result->rowCount();
			if ($num_results == 1) {
				$row = $result->fetch();
				$infoEquipe = $lang['Equipe'] . ' : ' . $row['Libelle'] . ' (' . $row['Code_compet'] . '-' . $row['Code_saison'] . ')';
				$infoEquipe2 = $row['Libelle'] . ' (' . $row['Code_compet'] . '-' . $row['Code_saison'] . ')';
				$_SESSION['infoEquipe'] = $infoEquipe;
				$_SESSION['codeClub'] = $row['Code_club'];

				if (count($Limit_Clubs) > 0 && $row['Verrou'] != 'O') {
					$row['Verrou'] = 'O';
					foreach ($Limit_Clubs as $value) {
						if (mb_eregi('(^' . $value . ')', $row['Code_club']))
							$row['Verrou'] = '';
					}
				}
				if (substr($row['Code_compet'], 0, 1) == 'N')
					$typeCompet = 'CH';
				elseif (substr($row['Code_compet'], 0, 2) == 'CF')
					$typeCompet = 'CF';
				else
					$typeCompet = '';
				$surcl_necess = 0;
				$array_surcl_neccessaire = array('N1D', 'N1F', 'N1H', 'N2', 'N2H', 'N3H', 'N4H', 'NQH', 'CFF', 'CFH', 'MCP');
				$array_surcl_neccessaire2 = array('N3', 'N4');
				$codeCompetReduit = substr($row['Code_compet'], 0, 3);
				$codeCompetReduit2 = substr($row['Code_compet'], 0, 2);
				if (in_array($codeCompetReduit, $array_surcl_neccessaire) || in_array($codeCompetReduit2, $array_surcl_neccessaire2)) {
					$surcl_necess = 1;
				}
				$this->m_tpl->assign('typeCompet', $typeCompet);
				$this->m_tpl->assign('headerSubTitle', $infoEquipe);
				$this->m_tpl->assign('infoEquipe2', $infoEquipe2);
				$this->m_tpl->assign('Verrou', $row['Verrou']);
				$this->m_tpl->assign('Statut', $row['Statut']);
				$this->m_tpl->assign('Code_niveau', $row['Code_niveau']);
				$this->m_tpl->assign('surcl_necess', $surcl_necess);
			}

			// Intégrer les coureurs de la recherche Licence ...
			if (isset($_SESSION['Signature'])) {
				$sql = "REPLACE INTO kp_competition_equipe_joueur 
					(Id_equipe, Matric, Nom, Prenom, Sexe, Categ) 
					SELECT ?, a.Matric, a.Nom, a.Prenom, a.Sexe, c.id 
					FROM kp_recherche_licence b, kp_licence a 
					LEFT OUTER JOIN kp_categorie c 
						ON (? - Year(a.Naissance) BETWEEN c.age_min AND c.age_max) 
					WHERE a.Matric = b.Matric 
					AND b.Signature = ? 
					AND b.Validation = 'O' ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array($idEquipe, $codeSaison, $_SESSION['Signature']));

				// TODO : Journal d'insertion ! 
				// $myBdd->utyJournal($action, $saison='', $competition='', $evenement=null, $journee=null, $match=null, $journal='', $user='')


				// Vidage kp_recherche_licence ...				
				$sql = "DELETE FROM kp_recherche_licence 
					WHERE Signature = ? ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array($_SESSION['Signature']));

				unset($_SESSION['Signature']);
			}

			// Chargement des Coureurs ...
			$sql = "SELECT a.Matric, a.Nom, a.Prenom, a.Sexe, a.Categ, a.Numero, a.Capitaine, 
				b.Origine, b.Numero_club, b.Pagaie_ECA, b.Pagaie_EVI, b.Pagaie_MER, 
				b.Etat_certificat_CK CertifCK, b.Etat_certificat_APS CertifAPS, 
				b.Reserve icf, c.arbitre, c.niveau, s.Date date_surclassement 
				FROM kp_competition_equipe_joueur a 
				LEFT OUTER JOIN kp_licence b ON (a.Matric = b.Matric) 
				LEFT OUTER JOIN kp_arbitre c ON (a.Matric = c.Matric) 
				LEFT OUTER JOIN kp_surclassement s 
					ON (a.Matric = s.Matric AND s.Saison = ?) 
				WHERE Id_Equipe = ? 
				ORDER BY Field(if(a.Capitaine='C','-',if(a.Capitaine='','-',a.Capitaine)), '-', 'E', 'A', 'X'), 
					Numero, Nom, Prenom ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($codeSaison, $idEquipe));
			$i = 0;
			while ($row = $result->fetch()) {
				if ($row['niveau'] != '')
					$row['arbitre'] .= '-' . $row['niveau'];

				$numero = $row['Numero'];
				if (strlen($numero) == 0)
					$numero = 0;

				$controlePagaie = controle_pagaie($row['Pagaie_ECA'], $row['Pagaie_EVI'], $row['Pagaie_MER']);
				$pagaie = $controlePagaie['pagaie'];
				$PagaieValide = $controlePagaie['PagaieValide'];

				$capitaine = $row['Capitaine'];
				if (strlen($capitaine) == 0)
					$capitaine = '-';
				$row['date_surclassement'] = utyDateUsToFr($row['date_surclassement']);
				// Pour décaler l'entraineur à la fin de la liste
				if ($capitaine == 'E' or $capitaine == 'A' or $capitaine == 'X')
					$clefEntraineur = $i;

				array_push($arrayJoueur, array(
					'Matric' => $row['Matric'],
					'Nom' => mb_strtoupper($row['Nom']),
					'Prenom' => mb_convert_case($row['Prenom'], MB_CASE_TITLE, "UTF-8"),
					'Sexe' => $row['Sexe'], 'Categ' => $row['Categ'], 'Pagaie' => $pagaie,
					'CertifCK' => $row['CertifCK'], 'CertifAPS' => $row['CertifAPS'],
					'Numero' => $numero, 'Capitaine' => $capitaine, 'Pagaie_ECA' => $row['Pagaie_ECA'],
					'Pagaie_EVI' => $row['Pagaie_EVI'],  'Pagaie_MER' => $row['Pagaie_MER'],
					'PagaieValide' => $PagaieValide,
					'Arbitre' => $row['arbitre'], 'Saison' => $row['Origine'],
					'Numero_club' => $row['Numero_club'],
					'date_surclassement' => $row['date_surclassement'], 'icf' => $row['icf']
				));
				$i++;
			}
		}
		/*		if($clefEntraineur != '')
		{
			// Prélève les non joueurs de la liste
			$decaleEntraineur = array_splice($arrayJoueur, $clefEntraineur, 1);
			// Replace les non joueurs à la fin
			array_splice($arrayJoueur, 9, 0, $decaleEntraineur);
		}
*/
		// Affichage dernière modification
		$sql = "SELECT j.Dates, j.Users, j.Journal, u.Identite 
			FROM kp_journal j, kp_user u 
			WHERE j.Users = u.Code 
			AND ( j.Actions = 'Ajout titulaire' 
				OR j.Actions = 'Suppression titulaire' 
				OR j.Actions = 'Modification kp_competition_equipe_' ) 
			AND j.Journal LIKE ? 
			ORDER BY j.Dates DESC 
			LIMIT 1 ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array('Equipe : ' . $idEquipe . ' -%'));
		while ($row = $result->fetch()) {
			$this->m_tpl->assign('LastUpdate', utyDateUsToFr(substr($row['Dates'], 0, 10)));
			$this->m_tpl->assign('LastUpdater', $row['Identite']);
			$this->m_tpl->assign('LastUpd', $row['Journal']);
		}
		$this->m_tpl->assign('arrayJoueur', $arrayJoueur);
		$this->m_tpl->assign('idEquipe', $idEquipe);
		$this->m_tpl->assign('sSaison', $myBdd->GetActiveSaison());
	}

	function Add()
	{
		$myBdd = $this->myBdd;
		$idEquipe = utyGetPost('idEquipe', '');

		$matricJoueur = utyGetPost('matricJoueur', '');
		$nomJoueur = mb_strtoupper(trim(utyGetPost('nomJoueur', '')), 'UTF-8');
		$prenomJoueur = mb_strtoupper(trim(utyGetPost('prenomJoueur', '')), 'UTF-8');
		$sexeJoueur = strtoupper(trim(utyGetPost('sexeJoueur', '')));
		$naissanceJoueur = utyDateFrToUs(utyGetPost('naissanceJoueur', ''));
		$capitaineJoueur = trim(utyGetPost('capitaineJoueur', '-'));
		$numeroJoueur = trim(utyGetPost('numeroJoueur', ''));
		$arbitreJoueur = trim(utyGetPost('arbitreJoueur', ''));
		$niveauJoueur = trim(utyGetPost('niveauJoueur', ''));
		$numicfJoueur = (int) trim(utyGetPost('numicfJoueur', ''));
		$saisonJoueur = $myBdd->GetActiveSaison();

		if (strlen($idEquipe) > 0) {
			$categJoueur = utyCodeCategorie2($naissanceJoueur);

			if (strlen($matricJoueur) == 0) {
				$matricJoueur = $myBdd->GetNextMatricLicence();
			}

			try {
				$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$myBdd->pdo->beginTransaction();

				$codeClub = $myBdd->GetCodeClubEquipe($idEquipe);
				$myBdd->InsertIfNotExistLicence($matricJoueur, $nomJoueur, $prenomJoueur, $sexeJoueur, $naissanceJoueur, $codeClub, $numicfJoueur);

				$sql = "INSERT INTO kp_competition_equipe_joueur 
					(Id_equipe, Matric, Nom, Prenom, Sexe, Categ, Numero, Capitaine) 
					VALUES (?, ?, ?, ?, ?, ?, ?, ?) ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array(
					$idEquipe, $matricJoueur, $nomJoueur, $prenomJoueur,
					$sexeJoueur, $categJoueur, $numeroJoueur, $capitaineJoueur
				));

				if (($matricJoueur >= 2000000) && ($arbitreJoueur != '')) {
					$sql = "INSERT INTO kp_arbitre 
						(Matric, regional, interregional, national, international, arbitre, livret, niveau, saison) 
						VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ";
					switch ($arbitreJoueur) {
						case 'REG':
							$arrayParam = [$matricJoueur, 'O', 'N', 'N', 'N', 'Reg', '', $niveauJoueur, $saisonJoueur];
							break;
						case 'IR':
							$arrayParam = [$matricJoueur, 'N', 'O', 'N', 'N', 'IR', '', $niveauJoueur, $saisonJoueur];
							break;
						case 'NAT':
							$arrayParam = [$matricJoueur, 'N', 'N', 'O', 'N', 'Nat', '', $niveauJoueur, $saisonJoueur];
							break;
						case 'INT':
							$arrayParam = [$matricJoueur, 'N', 'N', 'O', 'O', 'Int', '', $niveauJoueur, $saisonJoueur];
							break;
						case 'OTM':
							$arrayParam = [$matricJoueur, 'N', 'N', 'O', 'N', 'OTM', '', $niveauJoueur, $saisonJoueur];
							break;
						case 'JO':
							$arrayParam = [$matricJoueur, 'N', 'N', 'O', 'N', 'JO', '', $niveauJoueur, $saisonJoueur];
							break;
						default:
							$arrayParam = [$matricJoueur, 'N', 'N', 'N', 'N', '', '', '', ''];
							break;
					}
					$result = $myBdd->pdo->prepare($sql);
					$result->execute($arrayParam);
				}

				$myBdd->pdo->commit();
			} catch (Exception $e) {
				$myBdd->pdo->rollBack();
				utySendMail("[KPI] Erreur SQL", "Ajout titulaire, $idEquipe, $matricJoueur" . '\r\n' . $e->getMessage());

				return "La requête ne peut pas être exécutée !\\nCannot execute query!";
			}

			$myBdd->utyJournal('Ajout titulaire', '', '', null, null, null, 'Equipe : ' . $idEquipe . ' - Joueur : ' . $matricJoueur);
			return;
		}
	}

	function Add2()
	{
		$idEquipe = utyGetPost('idEquipe', '');

		$matricJoueur = utyGetPost('matricJoueur2', '');
		$nomJoueur = utyGetPost('nomJoueur2', '');
		$prenomJoueur = utyGetPost('prenomJoueur2', '');
		$sexeJoueur = utyGetPost('sexeJoueur2', '');
		$categJoueur = utyGetPost('categJoueur2', '');
		$capitaineJoueur = utyGetPost('capitaineJoueur2', '-');
		$numeroJoueur = utyGetPost('numeroJoueur2', '');

		if ($idEquipe > 0) {
			$myBdd = $this->myBdd;
			if (strlen($matricJoueur) == 0)
				$matricJoueur = $myBdd->GetNextMatricLicence();

			try {
				$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$myBdd->pdo->beginTransaction();

				$sql = "INSERT INTO kp_competition_equipe_joueur 
					(Id_equipe, Matric, Nom, Prenom, Sexe, Categ, Numero, Capitaine) 
					VALUES (?, ?, ?, ?, ?, ?, ?, ?) ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array(
					$idEquipe, $matricJoueur, $nomJoueur, $prenomJoueur,
					$sexeJoueur, $categJoueur, $numeroJoueur, $capitaineJoueur
				));

				if ($matricJoueur >= 2000000) {
					$saisonJoueur = $myBdd->GetActiveSaison();
					$sql = "UPDATE kp_licence
						SET Origine = ?
						WHERE Matric = ?";
					$result = $myBdd->pdo->prepare($sql);
					$result->execute([$saisonJoueur, $matricJoueur]);
				}

				$myBdd->pdo->commit();
			} catch (Exception $e) {
				$myBdd->pdo->rollBack();
				utySendMail("[KPI] Erreur SQL", "Ajout titulaire, $idEquipe, $matricJoueur" . '\r\n' . $e->getMessage());

				return "La requête ne peut pas être exécutée !\\nCannot execute query!";
			}

			$myBdd->utyJournal('Ajout titulaire', '', '', null, null, null, 'Equipe : ' . $idEquipe . ' - Joueur : ' . $matricJoueur);
			return;
		}
	}

	/* AddCoureur :  Fct Obsolète */
	// function AddCoureur()
	// {
	// 	$idEquipe = utyGetPost('idEquipe', '');

	// 	$ParamCmd = utyGetPost('ParamCmd', '');

	// 	if (strlen($idEquipe) > 0) {
	// 		$data = explode('|',$ParamCmd);
	// 		$Matric = $data[0];
	// 		$Categ = $data[1];

	// 		$myBdd = $this->myBdd;

	// 		$sql = "REPLACE INTO kp_competition_equipe_joueur 
	// 			(Id_equipe, Matric, Nom, Prenom, Sexe, Categ) 
	// 			SELECT ?, Matric, Nom, Prenom, Sexe, ? 
	// 			FROM kp_licence 
	// 			WHERE Matric = ? ";
	// 		$result = $myBdd->pdo->prepare($sql);
	// 		$result->execute(array($idEquipe, $Categ, $Matric));

	// 		$myBdd->utyJournal('Ajout coureur', '', '', null, null, null, 'Equipe : '.$idEquipe.' - Joueur : '.$Matric);
	// 	}
	// }

	function Remove()
	{
		$idEquipe = utyGetPost('idEquipe', '');
		$ParamCmd = utyGetPost('ParamCmd', '');

		$arrayParam = explode(',', $ParamCmd);
		if (count($arrayParam) == 0)
			return; // Rien à Detruire ...

		$myBdd = $this->myBdd;
		$in = str_repeat('?,', count($arrayParam) - 1) . '?';

		try {
			$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$myBdd->pdo->beginTransaction();

			$sql = "DELETE FROM kp_competition_equipe_joueur 
				WHERE Id_Equipe = ? 
				AND Matric IN ($in) ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array_merge([$idEquipe], $arrayParam));

			for ($i = 0; $i < count($arrayParam); $i++) {
				$myBdd->utyJournal('Suppression titulaire', '', '', null, null, null, 'Equipe : ' . $idEquipe . ' - Joueur : ' . $arrayParam[$i]);
			}

			$myBdd->pdo->commit();
		} catch (Exception $e) {
			$myBdd->pdo->rollBack();
			utySendMail("[KPI] Erreur SQL", "Suppression titulaire, $idEquipe, $ParamCmd" . '\r\n' . $e->getMessage());

			return "La requête ne peut pas être exécutée !\\nCannot execute query!";
		}
	}

	function Find()
	{
		$myBdd = $this->myBdd;
		$_SESSION['Signature'] = uniqid('GEJ-');

		if (isset($_SESSION['codeClub'])) {
			$_SESSION['codeComiteDep'] = $myBdd->GetCodeComiteDept($_SESSION['codeClub']);
			$_SESSION['codeComiteReg'] = $myBdd->GetCodeComiteReg($_SESSION['codeComiteDep']);
		}

		header("Location: RechercheLicence.php");
		exit;
	}

	function __construct()
	{
		parent::__construct(10);

		$this->myBdd = new MyBdd();

		$alertMessage = '';

		$Cmd = utyGetPost('Cmd', '');

		if (strlen($Cmd) > 0) {
			if ($Cmd == 'Add') ($_SESSION['Profile'] <= 4) ? $alertMessage = $this->Add() : $alertMessage = 'Vous n avez pas les droits pour cette action.';

			if ($Cmd == 'Add2') ($_SESSION['Profile'] <= 8) ? $alertMessage = $this->Add2() : $alertMessage = 'Vous n avez pas les droits pour cette action.';

			// if ($Cmd == 'AddCoureur') // Obsolète ...
			// 	($_SESSION['Profile'] <= 4) ? $this->AddCoureur() : $alertMessage = 'Vous n avez pas les droits pour cette action.';

			if ($Cmd == 'Remove') ($_SESSION['Profile'] <= 8) ? $alertMessage = $this->Remove() : $alertMessage = 'Vous n avez pas les droits pour cette action.';

			if ($Cmd == 'Find') ($_SESSION['Profile'] <= 8) ? $this->Find() : $alertMessage = 'Vous n avez pas les droits pour cette action.';

			if ($alertMessage == '') {
				header("Location: http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
				exit;
			}
		}

		$this->SetTemplate("Feuilles_de_presence", "Equipes", false);
		$this->Load();
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplate('GestionEquipeJoueur');
	}
}

$page = new GestionEquipeJoueur();
