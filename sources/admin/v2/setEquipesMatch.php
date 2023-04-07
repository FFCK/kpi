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
$idMatch = (int) utyGetPost('idMatch');
$idEquipe = (int) utyGetPost('idEquipe');
if ($idEquipe < 1) {
	$idEquipe = null;
}
$Equipe = trim(utyGetPost('equipe')); // A / B
$EquipeAB = 'Id_equipe' . $Equipe;
$sql = "UPDATE kp_match 
	SET $EquipeAB = ? 
	WHERE Id = ? 
	AND Validation != 'O' ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array($idEquipe, $idMatch));

// Vidage compo
$sql = "DELETE FROM kp_match_joueur 
	WHERE Equipe = ? 
	AND Id_match = ? ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array($Equipe, $idMatch));

$cMatch = new CacheMatch($_GET);
$cMatch->MatchGlobal($myBdd, $idMatch);

echo "OK";
