<?php
// prevent direct access *****************************************************
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) and
	strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if (!$isAjax) {
	$user_error = 'Access denied - not an AJAX request...';
	trigger_error($user_error, E_USER_ERROR);
}
// ***************************************************************************

include_once('../../commun/MyBdd.php');
include_once('../../commun/MyTools.php');

if(!isset($_SESSION)) {
	session_start();
}

$myBdd = new MyBdd();
$idJournee = (int) utyGetPost('Id_Journee');
$Valeur = trim(utyGetPost('Valeur'));

// Contrôle des droits : seuls les profiles <= 4 peuvent consolider une phase
if (!isset($_SESSION['Profile']) || $_SESSION['Profile'] > 4) {
	header('HTTP/1.0 401 Unauthorized');
	die('Droits insuffisants !');
}

// Contrôle autorisation journée
if (!utyIsAutorisationJournee($idJournee)) {
	header('HTTP/1.0 401 Unauthorized');
	die("Vous n'avez pas l'autorisation de modifier cette journée !");
}

// Mise à jour de la consolidation
$sql = "UPDATE kp_journee
	SET Consolidation = ?
	WHERE Id = ? ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array($Valeur, $idJournee));

if ($result) {
	echo 'OK';
} else {
	echo 'Erreur lors de la mise à jour';
}
