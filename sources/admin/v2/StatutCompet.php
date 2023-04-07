<?php 
// prevent direct access *****************************************************
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if(!$isAjax) {
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
$idCompet = trim(utyGetPost('Id_Compet'));
$Valeur = trim(utyGetPost('Valeur'));
$TypeUpdate = trim(utyGetPost('TypeUpdate'));
$idSaison = trim(utyGetPost('idSaison'));
if (!in_array($TypeUpdate, ['Statut', 'Verrou', 'Publication'])) {
	header('HTTP/1.0 401 Unauthorized');
	die('Action non autorisée !');
}

$sql = "UPDATE kp_competition 
	SET $TypeUpdate = ? 
	WHERE Code = ? 
	AND Code_saison = ? ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array($Valeur, $idCompet, $idSaison));

echo 'OK';
