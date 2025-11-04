<?php
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');
	
if(!isset($_SESSION)) {
	session_start(); 
}

$myBdd = new MyBdd();

// Chargement
$q = trim(utyGetGet('q'));
$q = preg_replace('`^[0]*`','',$q);
$format = utyGetGet('format', 'legacy'); // Support both legacy and JSON format

// Referentiel Journees
$sql = "SELECT *
	FROM kp_journee_ref
	WHERE UPPER(nom) LIKE UPPER(?)
	ORDER BY nom ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array('%'.$q.'%'));

if ($format === 'json') {
	// Modern JSON format
	$results = [];
	while ($row = $result->fetch()) {
		$results[] = [
			'nom' => $row['nom'],
			'label' => $row['nom'],
			'value' => $row['nom']
		];
	}
	header('Content-Type: application/json');
	echo json_encode($results);
} else {
	// Legacy format
	$resultGlobal = '';
	while ($row = $result->fetch()) {
		$resultGlobal .= $row['nom']."|".$row['nom']."\n";
	}
	echo $resultGlobal;
}
