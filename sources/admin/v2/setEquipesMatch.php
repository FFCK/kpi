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
	$idEquipe = (int)$_POST['idEquipe'];
	$Equipe = $myBdd->RealEscapeString(trim($_POST['equipe'])); // A / B
	$sql  = "UPDATE gickp_Matchs "
            . "SET Id_equipe".$Equipe." = ".$idEquipe." "
            . "WHERE Id = ".$idMatch;
	$result = $myBdd->Query($sql);
	// Vidage compo
	$sql  = "DELETE FROM gickp_Matchs_Joueurs "
            . "WHERE Id_match = $idMatch "
            . "AND Equipe = '".$Equipe."' ";
			$myBdd->Query($sql);	
	echo "OK"; 


