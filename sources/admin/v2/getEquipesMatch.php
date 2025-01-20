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

if(!isset($_SESSION)) {
	session_start(); 
}

$myBdd = new MyBdd();
$idJournee = (int) utyGetPost('idJournee');
$data[] = array('Id' => 0, 'Libelle' => '( indéterminé )');
$sql = "SELECT ce.Id, ce.Libelle 
	FROM kp_competition_equipe ce, kp_journee j 
	WHERE ce.Code_compet = j.Code_competition 
	AND ce.Code_saison = j.Code_saison 
	AND j.Id = ? 
	ORDER BY ce.Libelle ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array($idJournee));
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {	
	$data[] = $row;
}
$encode_donnees = json_encode($data);
header('Content-Type: application/json');
echo $encode_donnees; 
