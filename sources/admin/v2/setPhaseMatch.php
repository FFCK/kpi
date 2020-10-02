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
$idPhase = (int) utyGetPost('idPhase');
$sql = "UPDATE gickp_Matchs 
	SET Id_journee = ? 
	WHERE Id = ? 
	AND Validation != 'O' ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array($idPhase, $idMatch));

echo "OK"; 
