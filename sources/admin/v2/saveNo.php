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
$idMatch = (int) utyGetPost('idMatch');
$id = explode('-', trim(utyGetPost('id')));
$value = trim(utyGetPost('value'));

// Contrôle autorisation journée
$myBdd->AutorisationMatch($idMatch);

$sql = "UPDATE kp_match_joueur 
	SET Numero = ? 
	WHERE Id_match = ? 
	AND Matric = ? ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array($value, $idMatch, $id[1]));

echo $value; 
