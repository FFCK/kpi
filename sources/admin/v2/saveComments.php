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
$idMatch = (int) utyGetPost('idMatch');
$value = trim(utyGetPost('value'));
$heure_fin_match = trim(utyGetPost('heure_fin_match', ''));
// Contrôle autorisation journée
$myBdd->AutorisationMatch($idMatch);

if ($heure_fin_match != '') {
	// Expected input format: HH:MM (e.g., "14:32")
	// Validate and store as HH:MM:00
	if (preg_match('/^([01]?[0-9]|2[0-3]):([0-5][0-9])$/', $heure_fin_match)) {
		// Already in HH:MM format, just add :00 for seconds
		$heure_fin_match = $heure_fin_match . ':00';
	}
	$sql = "UPDATE kp_match
		SET Commentaires_officiels = ?,
		Heure_fin = ?
		WHERE Id = ? ";
	$result = $myBdd->pdo->prepare($sql);
	$result->execute(array($value, $heure_fin_match, $idMatch));
} else {
	$sql = "UPDATE kp_match 
		SET Commentaires_officiels = ? 
		WHERE Id = ? ";
	$result = $myBdd->pdo->prepare($sql);
	$result->execute(array($value, $idMatch));
}
echo $value;
