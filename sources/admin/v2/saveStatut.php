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
	$id = explode('-', $_POST['id']);
    $matric = (int)$id[1];
	$value = $myBdd->RealEscapeString(trim($_POST['value']));
	// Contrôle autorisation journée
	$sql  = "Select Id_journee, Validation from gickp_Matchs where Id = ".$idMatch;
	$result = $myBdd->Query($sql);
	$row = $myBdd->FetchArray($result);
	if (!utyIsAutorisationJournee($row['Id_journee']))
		die ("Vous n'avez pas l'autorisation de modifier les matchs de cette journée !");
	if ($row['Validation']=='O')
		die ("Ce match est verrouillé !");
	
	$sql  = "UPDATE gickp_Matchs_Joueurs "
            . "SET Capitaine = '" . $value . "' "
            . "WHERE Id_match = " . $idMatch . " "
            . "AND Matric = " . $matric;
	$result = $myBdd->Query($sql);
	echo $value; 

