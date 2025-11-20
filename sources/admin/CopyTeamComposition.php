<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Vérification de la session
session_start();
if (!isset($_SESSION['Profile']) || $_SESSION['Profile'] > 7) {
	die(json_encode(['error' => 'Accès refusé']));
}

$myBdd = new MyBdd();

// Récupération des paramètres
$idEquipeSource = (int) utyGetPost('idEquipeSource', 0);
$idEquipeCible = (int) utyGetPost('idEquipeCible', 0);

header('Content-Type: application/json; charset=utf-8');

try {
	if ($idEquipeSource <= 0 || $idEquipeCible <= 0) {
		die(json_encode(['error' => 'Paramètres manquants']));
	}

	// Vérifier que l'équipe cible n'est pas verrouillée
	$codeSaison = $myBdd->GetActiveSaison();
	$sql = "SELECT eq.Code_club, cp.Verrou
		FROM kp_competition_equipe eq
		INNER JOIN kp_competition cp ON eq.Code_compet = cp.Code AND eq.Code_saison = cp.Code_saison
		WHERE eq.Id = ?
		AND eq.Code_saison = ?";
	$result = $myBdd->pdo->prepare($sql);
	$result->execute([$idEquipeCible, $codeSaison]);
	$equipeCible = $result->fetch();

	if (!$equipeCible) {
		die(json_encode(['error' => 'Équipe cible non trouvée']));
	}

	// Vérifier le verrou et les droits
	$Limit_Clubs = utyGetSession('Limit_Clubs', '');
	$Limit_Clubs = explode(',', $Limit_Clubs);

	$verrou = $equipeCible['Verrou'];
	if ((count($Limit_Clubs) > 0 && $verrou != 'O') || utyGetSession('Profile', 99) <= 2) {
		$verrou = 'O';
		foreach ($Limit_Clubs as $value) {
			if (mb_eregi('(^' . $value . ')', $equipeCible['Code_club']))
				$verrou = '';
		}
	}

	if ($verrou == 'O') {
		die(json_encode(['error' => 'Compétition verrouillée ou vous n\'avez pas les droits sur ce club']));
	}

	// Commencer la transaction
	$myBdd->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$myBdd->pdo->beginTransaction();

	// Supprimer les joueurs existants de l'équipe cible
	$sql = "DELETE FROM kp_competition_equipe_joueur
		WHERE Id_equipe = ?";
	$result = $myBdd->pdo->prepare($sql);
	$result->execute([$idEquipeCible]);

	// Copier les joueurs de l'équipe source vers l'équipe cible
	$sql = "INSERT INTO kp_competition_equipe_joueur
		(Id_equipe, Matric, Nom, Prenom, Sexe, Categ, Numero, Capitaine)
		SELECT ?, Matric, Nom, Prenom, Sexe, Categ, Numero, Capitaine
		FROM kp_competition_equipe_joueur
		WHERE Id_equipe = ?";
	$result = $myBdd->pdo->prepare($sql);
	$result->execute([$idEquipeCible, $idEquipeSource]);

	$nbJoueurs = $result->rowCount();

	// Valider la transaction
	$myBdd->pdo->commit();

	// Enregistrer dans le journal
	$myBdd->utyJournal(
		'Copie composition équipe',
		'',
		'',
		null,
		null,
		null,
		"Équipe cible : $idEquipeCible - Équipe source : $idEquipeSource - $nbJoueurs joueur(s) copié(s)"
	);

	echo json_encode([
		'success' => true,
		'message' => "$nbJoueurs joueur(s) copié(s) avec succès",
		'nbJoueurs' => $nbJoueurs
	]);
} catch (Exception $e) {
	if ($myBdd->pdo->inTransaction()) {
		$myBdd->pdo->rollBack();
	}
	utySendMail("[KPI] Erreur SQL", "Copie composition équipe, $idEquipeCible, $idEquipeSource" . '\r\n' . $e->getMessage());
	echo json_encode(['error' => 'Erreur lors de la copie : ' . $e->getMessage()]);
}
