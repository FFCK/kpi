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
$Id_Evenement = (int) utyGetPost('Id_Evenement');
$Id_Journee = (int) utyGetPost('Id_Journee');
$Valeur = trim( utyGetPost('Valeur'));

if ($Valeur == 'true') {
	$sql = "REPLACE INTO kp_evenement_journee (Id_Evenement, Id_Journee) 
		VALUES (?, ?)";
	$result = $myBdd->pdo->prepare($sql);
	$result->execute(array($Id_Evenement, $Id_Journee));
} elseif ($Valeur == 'false') {
	$sql = "DELETE FROM kp_evenement_journee 
		WHERE Id_Evenement = ? 
		AND Id_Journee = ? ";
	$result = $myBdd->pdo->prepare($sql);
	$result->execute(array($Id_Evenement, $Id_Journee));
}
echo 'OK';
