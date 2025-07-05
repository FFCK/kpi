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
include_once('../../live/create_cache_match.php');

if(!isset($_SESSION)) {
	session_start(); 
}

$myBdd = new MyBdd();
$idMatch = (int) utyGetPost('Id_Match');
$Valeur = trim(utyGetPost('Valeur'));
$TypeUpdate = trim(utyGetPost('TypeUpdate'));
if (!in_array(
	$TypeUpdate,
	['Score', 'ProvisionnalScore', 'Validation', 'Publication', 'Type', 'Statut', 'Periode', 'Imprime']
)) {
	header('HTTP/1.0 401 Unauthorized');
	die('Action non autorisée !');
}

// Contrôle autorisation journée
$myBdd->AutorisationMatch($idMatch, true);

if ($TypeUpdate == 'Score') {
	$Valeur = explode('-', $Valeur);
	$sql = "UPDATE kp_match 
		SET ScoreA = ?, ScoreB = ? 
		WHERE Id = ? ";
	$result = $myBdd->pdo->prepare($sql);
	$result->execute(array($Valeur[0], $Valeur[1], $idMatch));
	echo 'OK';
} elseif ($TypeUpdate == 'ProvisionnalScore') {
	$Valeur = explode('-', $Valeur);
	$sql = "UPDATE kp_match 
		SET ScoreDetailA = :Valeur0, ScoreDetailB = :Valeur1, 
		ScoreA = :Valeur2, ScoreB = :Valeur3 
		WHERE Id = :idMatch ";
	$result = $myBdd->pdo->prepare($sql);
	$result->execute(array(
		':Valeur0' => $Valeur[0],
		':Valeur1' => $Valeur[1],
		':Valeur2' => $Valeur[0],
		':Valeur3' => $Valeur[1],
		':idMatch' => $idMatch
	));
	echo 'OK';
} else {
	$sql = "UPDATE kp_match 
		SET $TypeUpdate = ? 
		WHERE Id = ? ";
	$result = $myBdd->pdo->prepare($sql);
	$result->execute(array($Valeur, $idMatch));
	echo 'OK';
}

// COSANDCO : Creation du Cache ...
$cMatch = new CacheMatch($_GET);
$cMatch->MatchGlobal($myBdd, $idMatch);
$cMatch->MatchScore($myBdd, $idMatch);
