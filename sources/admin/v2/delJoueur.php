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
$idMatch = (int) utyGetPost('Id_Match');
$Matric = (int) utyGetPost('Matric');
$Equipe = trim(utyGetPost('Equipe'));
// Contrôle autorisation journée
$myBdd->AutorisationMatch($idMatch);

$sql = "DELETE FROM kp_match_joueur 
	WHERE Id_match = ? 
	AND Matric = ? 
	AND Equipe = ? ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array($idMatch, $Matric, $Equipe));

echo 'OK';
