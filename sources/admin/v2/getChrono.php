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
$idMatch = (int) utyGetPost('idMatch', 0);
$sql = "SELECT * 
	FROM kp_chrono 
	WHERE IdMatch = ? ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array($idMatch));
$row = $result->fetch(PDO::FETCH_ASSOC);
$encode_donnees = json_encode($row);
header('Content-Type: application/json');
echo $encode_donnees; 
