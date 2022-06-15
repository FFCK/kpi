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

$resultGlobal = '';
// Chargement
$q = trim(utyGetGet('q'));
$query = '%' . strtoupper($q) . '%';
$sql = "SELECT e.Numero, e.Libelle, e.Code_club, c.Libelle nomClub 
	FROM kp_equipe e, kp_club c 
	WHERE (UPPER(e.Libelle) LIKE :query1
		OR UPPER(e.Code_club) LIKE :query2
		OR UPPER(c.Libelle) LIKE :query3 )
	AND e.Code_club = c.Code 
	ORDER BY e.Libelle ";
$result = $myBdd->pdo->prepare($sql);
$result->execute([
	':query1' => $query,
	':query2' => $query,
	':query3' => $query
]);
while ($row = $result->fetch()) {
	$Code_club = $row['Code_club'];
	$nomClub = $row['nomClub'];
	$Numero = $row['Numero'];
	$Libelle = $row['Libelle'];
	$resultGlobal .= "$Code_club - $Libelle ______($nomClub)|$Numero|$Libelle\n";
}
echo $resultGlobal;
