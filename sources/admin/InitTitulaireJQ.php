<?php
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');
include_once('../live/create_cache_match.php');
session_start();

class initTitulaires
{
	var $myBdd;

	function __construct()
	{
		$champs = utyGetPost('champs', ''); // Compet
		$valeur = utyGetPost('valeur', ''); // N2H
		$valeur3 = utyGetPost('valeur3', ''); // -1
		if ($champs == '' || $valeur == '') {
			echo 'Pas de valeur transmise';
			return;
		}
		if ($valeur == '*' ||  $valeur == '-1') {
			echo 'Selectionnez une ' . $champs;
			return;
		}

		$this->myBdd = new MyBdd();

		switch ($champs) {
			case 'Compet':
				$this->initCompet($valeur);
				break;
			case 'Journee':
				$this->initJournee($valeur);
				break;
			case 'Equipe':
				$this->initEquipe($valeur, $valeur3);
				break;
			default:
				echo 'Erreur Champs';
				break;
		}
	}


	function initCompet($valeur)
	{
		$myBdd = $this->myBdd;
		$codeCompet = $valeur;
		$codeSaison = $myBdd->GetActiveSaison();
		$lstJournee = utyGetSession('lstJournee', -1);

		// Chargement des Matchs en jeux ...
		$sql  = "SELECT a.Id, a.Id_equipeA, a.Id_equipeB 
			FROM kp_match a, kp_journee b 
			WHERE 1 
			AND a.Validation != 'O' 
			AND a.Id_journee = b.Id 
			AND b.Code_competition = ? 
			AND b.Code_saison = ? ";
		$arrayQuery = array($codeCompet, $codeSaison);
		if ($lstJournee != -1) {
			$arrayJournees = explode(',', $lstJournee);
			$in = str_repeat('?,', count($arrayJournees) - 1) . '?';
			$sql .= "AND a.Id_journee IN ($in) ";
			$arrayQuery = array_merge($arrayQuery, $arrayJournees);
		}
		$result = $myBdd->pdo->prepare($sql);
		$result->execute($arrayQuery);
		$num_results = $result->rowCount();
		while ($row = $result->fetch()) {
			$idMatch = $row['Id'];
			$idEquipeA = $row['Id_equipeA'];
			$idEquipeB = $row['Id_equipeB'];
			$this->initMatch($idMatch, $idEquipeA, $idEquipeB);
		}

		$myBdd->utyJournal('MAJ titulaires compétition', $codeSaison, utyGetSession('codeCompet', ''), null, null, null, $num_results . ' m.', utyGetSession('User'));
		if ($_SESSION['lang'] == 'en') {
			$resultGlobal = "Team rosters reassignment done for this competition, $num_results game(s) updated.";
		} else {
			$resultGlobal = "Initialisation des titulaires OK pour la compétition, $num_results match(s) mis à jour.";
		}
		echo $resultGlobal;
	}

	function initJournee($valeur)
	{
		$myBdd = $this->myBdd;
		$idJournee = $valeur;
		$codeSaison = $myBdd->GetActiveSaison();

		// Chargement des Matchs de la journée ...
		$sql = "SELECT Id, Id_equipeA, Id_equipeB 
			FROM kp_match 
			WHERE Id_journee = ? 
			AND Validation != 'O' ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($idJournee));
		$num_results = $result->rowCount();
		while ($row = $result->fetch()) {
			$idMatch = $row['Id'];
			$idEquipeA = $row['Id_equipeA'];
			$idEquipeB = $row['Id_equipeB'];

			$this->initMatch($idMatch, $idEquipeA, $idEquipeB);
		}
		$myBdd->utyJournal('MAJ titulaires journée', $codeSaison, utyGetSession('codeCompet', ''), null, $idJournee, null, $num_results . ' m.', utyGetSession('User'));
		if ($_SESSION['lang'] == 'en') {
			$resultGlobal = "Team rosters reassignment done for this gameday, $num_results game(s) updated.";
		} else {
			$resultGlobal = "Initialisation des titulaires OK pour la journée, $num_results match(s) mis à jour.";
		}
		echo $resultGlobal;
	}

	function initEquipe($valeur, $valeur3)
	{
		$myBdd = $this->myBdd;
		$idEquipe = $valeur;
		if ($valeur3 == '') {
			$idMatch = utyGetSession('idMatch', -1);
		} else {
			$idMatch = $valeur3;
		}
		$lstJournee = utyGetSession('lstJournee', -1);
		$codeSaison = $myBdd->GetActiveSaison();

		// Chargement des Matchs en jeux ...
		$sql = "SELECT Id, Id_equipeA, Id_equipeB 
			FROM kp_match 
			WHERE 1 
			AND (Id_equipeA = ? OR Id_equipeB = ?) 
			AND `Validation` != 'O' ";
		$arrayQuery = array($idEquipe, $idEquipe);
		if ($idMatch < 0) {
			$arrayJournees = explode(',', $lstJournee);
			$in = str_repeat('?,', count($arrayJournees) - 1) . '?';
			$sql .= "AND Id_journee IN ($in) ";
			$arrayQuery = array_merge($arrayQuery, $arrayJournees);
		} else {
			$sql .= "AND Id = ? ";
			$arrayQuery = array_merge($arrayQuery, [$idMatch]);
		}
		$result = $myBdd->pdo->prepare($sql);
		$result->execute($arrayQuery);
		$num_results = $result->rowCount();
		while ($row = $result->fetch()) {
			$idMatch = $row['Id'];
			$idEquipeA = $row['Id_equipeA'];
			$idEquipeB = $row['Id_equipeB'];

			$this->initMatch($idMatch, $idEquipeA, $idEquipeB, $idEquipe);
		}
		$myBdd->utyJournal('MAJ titulaires équipe', $codeSaison, utyGetSession('codeCompet', ''), null, null, null, 'J: ' . $lstJournee . ' - Eq: ' . $idEquipe . ' - ' . $num_results . ' m.', utyGetSession('User'));
		if ($_SESSION['lang'] == 'en') {
			$resultGlobal = "Team rosters reassignment done for this team, $num_results game(s) updated.";
		} else {
			$resultGlobal = "Initialisation des titulaires OK pour cette équipe, $num_results match(s) mis à jour.";
		}
		echo $resultGlobal;
	}

	function initMatch($idMatch, $idEquipeA, $idEquipeB, $idEquipe = 0)
	{
		$myBdd = $this->myBdd;

		try {
			$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$myBdd->pdo->beginTransaction();

			if (($idEquipe == 0 && $idEquipeA != '') || ($idEquipeA == $idEquipe)) {
				$sql = "DELETE FROM kp_match_joueur 
					WHERE Id_match = ? 
					AND Equipe = 'A' ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array($idMatch));

				$sql = "REPLACE INTO kp_match_joueur 
					SELECT ?, Matric, Numero, 'A', Capitaine 
					FROM kp_competition_equipe_joueur 
					WHERE Id_equipe = ? 
					AND Capitaine <> 'X' 
					AND Capitaine <> 'A' ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array($idMatch, $idEquipeA));
			}
			if (($idEquipe == 0 && $idEquipeB != '') || ($idEquipeB == $idEquipe)) {
				$sql = "DELETE FROM kp_match_joueur 
					WHERE Id_match = ? 
					AND Equipe = 'B' ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array($idMatch));

				$sql = "REPLACE INTO kp_match_joueur 
					SELECT ?, Matric, Numero, 'B', Capitaine 
					FROM kp_competition_equipe_joueur 
					WHERE Id_equipe = ? 
					AND Capitaine <> 'X' 
					AND Capitaine <> 'A' ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array($idMatch, $idEquipeB));
			}

			$cMatch = new CacheMatch($_GET);
			$cMatch->MatchGlobal($myBdd, $idMatch);

			$myBdd->pdo->commit();
		} catch (Exception $e) {
			$myBdd->pdo->rollBack();
			utySendMail("[KPI] Erreur SQL", "Init titulaires, $idMatch" . '\r\n' . $e->getMessage());

			return "La requête ne peut pas être exécutée !\\nCannot execute query!";
		}
	}
}

$initTitulaires = new initTitulaires();
