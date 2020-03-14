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
	$idMatch = (int)$_POST['Id_Match'];
	$Matric = (int)$_POST['Matric'];
	$Equipe = $myBdd->RealEscapeString(trim($_POST['Equipe']));
	// Contrôle autorisation journée
	$sql  = "Select Id_journee, Validation from gickp_Matchs where Id = ".$idMatch;
	$result = $myBdd->Query($sql);
	$row = $myBdd->FetchArray($result);
	if (!utyIsAutorisationJournee($row['Id_journee']))
		die ("Vous n'avez pas l'autorisation de modifier les matchs de cette journée !");
	if ($row['Validation']=='O')
		die ("Ce match est verrouillé !");
	
	$sql  = "DELETE FROM gickp_Matchs_Joueurs "
            . "WHERE Id_match = ".$idMatch." "
            . "AND Matric = ".$Matric." "
            . "AND Equipe = '".$Equipe."' ";
	$result = $myBdd->Query($sql);
	echo 'OK';
