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
$idJournee = (int) utyGetPost('Id_Journee', 0);
$Valeur = $myBdd->RealEscapeString(trim(utyGetPost('Valeur')));
$TypeUpdate = $myBdd->RealEscapeString(trim(utyGetPost('TypeUpdate')));
if (!in_array($TypeUpdate, ['Publication', 'Type'])) {
	header('HTTP/1.0 401 Unauthorized');
	die('Action non autorisée !');
}
// Contrôle autorisation journée
if (!utyIsAutorisationJournee($idJournee)) {
	header('HTTP/1.0 401 Unauthorized');
	die("Vous n'avez pas l'autorisation de modifier les matchs de cette journée !");
}

$sql = "UPDATE gickp_Journees 
	SET $TypeUpdate = ? 
	WHERE Id = ? ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array($Valeur, $idJournee));
echo 'OK';
