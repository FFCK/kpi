<?php
// prevent direct access *****************************************************
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) and
	strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if (!$isAjax) {
	$user_error = 'Access denied - not an AJAX request...';
	trigger_error($user_error, E_USER_ERROR);
}
// ***************************************************************************
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

$myBdd = new MyBdd();

// Chargement
$q = trim(utyGetGet('q'));
$format = utyGetGet('format', 'legacy'); // Support both legacy and JSON format

$query = '%' . strtoupper($q) . '%';
$sql = "SELECT e.Numero, e.Libelle, e.Code_club, c.Libelle nomClub
	FROM kp_equipe e, kp_club c
	WHERE (UPPER(e.Libelle) LIKE :query1
		OR UPPER(e.Code_club) LIKE :query2
		OR UPPER(c.Libelle) LIKE :query3 )
	AND e.Code_club = c.Code
	ORDER BY e.Libelle
	LIMIT 50";
$result = $myBdd->pdo->prepare($sql);
$result->execute([
	':query1' => $query,
	':query2' => $query,
	':query3' => $query
]);

if ($format === 'json') {
	// Modern JSON format
	$results = [];
	while ($row = $result->fetch()) {
		$results[] = [
			'numero' => $row['Numero'],
			'libelle' => $row['Libelle'],
			'codeClub' => $row['Code_club'],
			'nomClub' => $row['nomClub'],
			'label' => $row['Code_club'] . ' - ' . $row['Libelle'] . ' (' . $row['nomClub'] . ')',
			'value' => $row['Libelle']
		];
	}
	header('Content-Type: application/json');
	echo json_encode($results);
} else {
	// Legacy format for backward compatibility
	$resultGlobal = '';
	while ($row = $result->fetch()) {
		$Code_club = $row['Code_club'];
		$nomClub = $row['nomClub'];
		$Numero = $row['Numero'];
		$Libelle = $row['Libelle'];
		$resultGlobal .= "$Code_club - $Libelle ______($nomClub)|$Numero|$Libelle\n";
	}
	echo $resultGlobal;
}
