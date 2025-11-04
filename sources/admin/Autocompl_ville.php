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

// Referentiel Villes
$sql = "SELECT ville_nom, ville_nom_reel, ville_departement, ville_code_postal
	FROM villes_france_free
	WHERE UPPER(ville_nom) LIKE UPPER(?)
	OR ville_code_postal LIKE UPPER(?)
	ORDER BY ville_departement, ville_code_postal, ville_nom_reel ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array('%'.$q.'%', $q.'%'));

if ($format === 'json') {
	// Modern JSON format
	$results = [];
	while ($row = $result->fetch()) {
		$results[] = [
			'nom' => $row['ville_nom_reel'],
			'departement' => $row['ville_departement'],
			'codePostal' => $row['ville_code_postal'],
			'label' => $row['ville_code_postal'] . " " . $row['ville_nom_reel'],
			'value' => $row['ville_nom_reel']
		];
	}
	header('Content-Type: application/json');
	echo json_encode($results);
} else {
	// Legacy format
	$resultGlobal = '';
	while ($row = $result->fetch()) {
		$resultGlobal .= $row['ville_code_postal']." ".$row['ville_nom_reel']."|".$row['ville_nom_reel']."|".$row['ville_departement']."\n";
	}
	echo $resultGlobal;
}
