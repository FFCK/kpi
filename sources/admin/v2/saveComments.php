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

session_start();

$myBdd = new MyBdd();
$idMatch = (int) utyGetPost('idMatch');
$value = trim(utyGetPost('value'));
$heure_fin_match = trim(utyGetPost('heure_fin_match', ''));
// Contrôle autorisation journée
$myBdd->AutorisationMatch($idMatch);

if ($heure_fin_match != '') {
	$heure_fin_match = '00:'.substr($heure_fin_match,-5,2).':'.substr($heure_fin_match,-2);
	$sql = "UPDATE kp_match 
		SET Commentaires_officiels = ?, 
		Heure_fin = ? 
		WHERE Id = ? ";
	$result = $myBdd->pdo->prepare($sql);
	$result->execute(array($value, $heure_fin_match, $idMatch));
} else {
	$sql = "UPDATE kp_match 
		SET Commentaires_officiels = ?, 
		WHERE Id = ? ";
	$result = $myBdd->pdo->prepare($sql);
	$result->execute(array($value, $idMatch));
}
echo $value;

