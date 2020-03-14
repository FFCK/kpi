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
	$idJournee = (int)$_POST['idJournee'];
	$data[] = array('Id' => 0, 'Libelle' => '( indéterminé )');
	$sql  = "SELECT ce.Id, ce.Libelle FROM gickp_Competitions_Equipes ce, gickp_Journees j WHERE ce.Code_compet = j.Code_competition AND ce.Code_saison = j.Code_saison AND j.Id = ".$idJournee;
	$result = $myBdd->Query($sql);
	while ($row = $myBdd->FetchArray($result)) {	
		$data[] = $row;
	}
	$encode_donnees = json_encode($data);
	echo $encode_donnees; 
