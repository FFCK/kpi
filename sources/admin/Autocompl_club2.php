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
	
if(!isset($_SESSION)) {
	session_start(); 
}

$myBdd = new MyBdd();

// Chargement
$q = trim(utyGetGet('q'));
$q = preg_replace('`^[0]*`','',$q);
$resultGlobal = '';

// Clubs
$sql = "SELECT c.Code, c.Libelle, c.Code_comite_dep, cd.Code_comite_reg 
	FROM kp_club c, kp_cd cd 
	WHERE c.Code_comite_dep = cd.Code 
	AND (UPPER(c.Libelle) LIKE UPPER(?) 
		OR UPPER(c.Code) LIKE UPPER(?)) 
	ORDER BY c.Code ";	 
$result = $myBdd->pdo->prepare($sql);
$result->execute(array('%'.$q.'%', $q.'%'));
while ($row = $result->fetch()) {
	$resultGlobal .= $row['Code']." - ".$row['Libelle']."|".$row['Libelle']."|".$row['Code']."|".$row['Code_comite_dep']."|".$row['Code_comite_reg']."\n";
}

echo $resultGlobal;
