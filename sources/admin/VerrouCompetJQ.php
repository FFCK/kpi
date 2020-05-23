<?php
// prevent direct access *****************************************************
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if(!$isAjax) {
  $user_error = 'Access denied - not an AJAX request...';
  trigger_error($user_error, E_USER_ERROR);
}
// ***************************************************************************
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');
session_start();

$verrouCompet = utyGetPost('compet');
$Verrou = utyGetPost('verrou');
($Verrou == 'O') ? $Verrou = 'N' : $Verrou = 'O';

if (strlen($verrouCompet) > 0 && $_SESSION['profile'] <= 4) {
	$myBdd = new MyBdd();
	$saison = $myBdd->GetActiveSaison();

	$sql = "UPDATE gickp_Competitions 
		SET Verrou = ? 
		WHERE Code_saison = ? 
		AND Code = ? ";
    $result = $myBdd->pdo->prepare($sql);
    $result->execute(array($Verrou, $saison, $verrouCompet));
	
	$myBdd->utyJournal('Verrou Compet', $myBdd->GetActiveSaison(), $verrouCompet);
	
	echo $Verrou;
} else {
	trigger_error('Erreur', E_USER_ERROR);
}
