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
$Id_Evenement = (int)$_POST['Id_Evenement'];
$Id_Journee = (int)$_POST['Id_Journee'];
$Valeur = $myBdd->RealEscapeString(trim($_POST['Valeur']));

if ($Valeur == 'true') {
	$sql  = "REPLACE INTO gickp_Evenement_Journees (Id_Evenement, Id_Journee) "
			. "VALUES ($Id_Evenement, $Id_Journee)";
} elseif($Valeur == 'false') {
	$sql  = "DELETE FROM gickp_Evenement_Journees "
			. "WHERE Id_Evenement = $Id_Evenement "
			. "AND Id_Journee = $Id_Journee ";
}
$result = $myBdd->Query($sql);
echo 'OK';
