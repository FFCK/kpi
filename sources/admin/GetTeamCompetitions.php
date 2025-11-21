<?php

if (!isset($_SESSION)) {
	session_start();
}

include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

$myBdd = new MyBdd();

// Vérification de la session
if (!isset($_SESSION['Profile']) || $_SESSION['Profile'] > 7) {
	die(json_encode(['error' => 'Accès refusé']));
}

// Récupération des paramètres
$action = utyGetGet('action', '');
$saison = utyGetGet('saison', '');
$idEquipe = (int) utyGetGet('idEquipe', 0);

header('Content-Type: application/json; charset=utf-8');

try {
	if ($action === 'getSaisons') {
		// Récupérer la saison active et les 2 précédentes
		$codeSaison = $myBdd->GetActiveSaison();

		$sql = "SELECT Code
			FROM kp_saison
			WHERE Code <= ?
			ORDER BY Code DESC
			LIMIT 3";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute([$codeSaison]);
		$saisons = [];
		while ($row = $result->fetch()) {
			$saisons[] = [
				'code' => $row['Code'],
				'libelle' => $row['Code'] // Utiliser Code comme libellé
			];
		}
		echo json_encode($saisons);
	} elseif ($action === 'getCompetitions' && $saison && $idEquipe > 0) {
		// Récupérer le code du club de l'équipe actuelle
		$sql = "SELECT Code_club
			FROM kp_competition_equipe
			WHERE Id = ?";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute([$idEquipe]);
		$equipeActuelle = $result->fetch();

		if (!$equipeActuelle) {
			die(json_encode(['error' => 'Équipe non trouvée']));
		}

		$codeClub = $equipeActuelle['Code_club'];

		// Récupérer les compétitions de ce club pour la saison sélectionnée
		// Exclure l'équipe actuelle
		$sql = "SELECT ce.Id, ce.Libelle, ce.Code_compet, cp.Libelle as Libelle_compet
			FROM kp_competition_equipe ce
			INNER JOIN kp_competition cp ON ce.Code_compet = cp.Code AND ce.Code_saison = cp.Code_saison
			WHERE ce.Code_club = ?
			AND ce.Code_saison = ?
			AND ce.Id != ?
			ORDER BY ce.Libelle";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute([$codeClub, $saison, $idEquipe]);

		$competitions = [];
		while ($row = $result->fetch()) {
			$competitions[] = [
				'id' => $row['Id'],
				'libelle' => $row['Libelle'],
				'code_compet' => $row['Code_compet'],
				'libelle_compet' => $row['Libelle_compet']
			];
		}
		echo json_encode($competitions);
	} else {
		echo json_encode(['error' => 'Paramètres manquants']);
	}
} catch (Exception $e) {
	echo json_encode(['error' => 'Erreur : ' . $e->getMessage()]);
}
