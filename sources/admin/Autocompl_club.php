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

$myBdd = new MyBdd();

// Chargement
$q = trim(utyGetGet('q'));
$q = preg_replace('`^[0]*`','',$q);
$resultGlobal = '';

// Clubs
$sql = "SELECT * 
	FROM gickp_Club 
	WHERE (UPPER(Libelle) LIKE UPPER(?) 
		OR UPPER(Code) LIKE UPPER(?)) 
	ORDER BY Code ";	 
$result = $myBdd->pdo->prepare($sql);
$result->execute(array('%'.$q.'%', $q.'%'));
while ($row = $result->fetch()) {
	$resultGlobal .= $row['Code']." - ".$row['Libelle']."|".$row['Libelle']."\n";
}

echo $resultGlobal;
