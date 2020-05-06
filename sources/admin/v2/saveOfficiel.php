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
$idMatch = (int)$_POST['idMatch'];
$value = trim(utyGetPost('value'));
$id = utyGetPost('id');
if (!in_array($id, ['Secretaire', 'Chronometre', 'Timeshoot', 'Ligne1', 'Ligne2'])) {
	header('HTTP/1.0 401 Unauthorized');
	die('Action non autorisée !');
}

// Contrôle autorisation journée
$myBdd->AutorisationMatch($idMatch);

$sql = "UPDATE gickp_Matchs 
	SET $id = ? 
	WHERE Id = ? ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array($value, $idMatch));
echo $value;
