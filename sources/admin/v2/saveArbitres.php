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
$id = trim(utyGetPost('id'));
$value = explode('|',utyGetPost('value'));
$value[0] = trim($value[0]);
if (isset($value[1])) {
	$value[1] = (int) trim($value[1]);
} else {
	$value[1] = 0;
}
if (!in_array($id, ['Arbitre_principal', 'Arbitre_secondaire'])) {
	header('HTTP/1.0 401 Unauthorized');
	die('Action non autorisée !');
}

// Contrôle autorisation journée
$myBdd->AutorisationMatch($idMatch);


if ($id == 'Arbitre_principal') {
$sql = "UPDATE kp_match 
	SET Arbitre_principal = ?,
	Matric_arbitre_principal = ? 
	WHERE Id = ? ";
} else {
	$sql = "UPDATE kp_match 
	SET Arbitre_secondaire = ?,
	Matric_arbitre_secondaire = ? 
	WHERE Id = ? ";
}
$result = $myBdd->pdo->prepare($sql);
$result->execute(array($value[0], $value[1], $idMatch));

if($value[0] != '') {
	echo $value[0];
} else {
	echo $value[1];
}
