<?php
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');
	
session_start();

$myBdd = new MyBdd();

// Chargement
$q = trim(utyGetGet('q'));
$q = preg_replace('`^[0]*`','',$q);
$resultGlobal = '';

// Referentiel Journees
$sql = "SELECT * 
	FROM kp_journee_ref 
	WHERE UPPER(nom) LIKE UPPER(?) 
	ORDER BY nom ";	 
$result = $myBdd->pdo->prepare($sql);
$result->execute(array('%'.$q.'%'));
while ($row = $result->fetch()) {
	$resultGlobal .= $row['nom']."|".$row['nom']."\n";
}

echo $resultGlobal;
