<?php
// prevent direct access *****************************************************
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) and
  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if (!$isAjax) {
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
$myBdd->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

$idMatch = (int) utyGetPost('idMatch');
$numTarget = (int) utyGetPost('numTarget');

if ($idMatch <= 0 || $numTarget <= 0) {
    echo '';
    return;
}

$idTarget = null;

/* Même journée/phase/groupe */
$sql = "SELECT matchs1.Id
FROM kp_match matchs1
JOIN kp_match matchs2 ON matchs1.Id_journee = matchs2.Id_journee
WHERE matchs2.Id = :idMatch
  AND matchs1.Numero_ordre = :numTarget
  AND matchs1.Id != matchs2.Id;
";
$stmt = $myBdd->pdo->prepare($sql);
$stmt->execute([
  ':idMatch' => $idMatch,
  ':numTarget' => $numTarget
]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($stmt->rowCount() === 1) {
  $idTarget = $row['Id'];
  return_200($row);
}
if ($stmt->rowCount() > 1) {
  return_404('Too many games found');
}

/* Même compétition */
$sql = "SELECT matchs1.Id
  FROM kp_match matchs1
  JOIN kp_journee j1 ON matchs1.Id_journee = j1.Id
  JOIN kp_competition c1 ON j1.Code_competition = c1.Code AND j1.Code_saison = c1.Code_saison
  JOIN kp_journee j2 ON j2.Code_competition = c1.Code AND j2.Code_saison = c1.Code_saison
  JOIN kp_match matchs2 ON matchs2.Id_journee = j2.Id
  WHERE matchs2.Id = :idMatch
    AND matchs1.Numero_ordre = :numTarget
    AND matchs1.Id != matchs2.Id;
";
$stmt = $myBdd->pdo->prepare($sql);
$stmt->execute([
  ':idMatch' => $idMatch,
  ':numTarget' => $numTarget
]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($stmt->rowCount() === 1) {
  $idTarget = $row['Id'];
  return_200($row);
}
if ($stmt->rowCount() > 1) {
  return_404('Too many games found');
}

/* Même événement */
$sql = "SELECT matchs1.Id
  FROM kp_match matchs1
  JOIN kp_journee j1 ON matchs1.Id_journee = j1.Id
  JOIN kp_evenement_journee ej1 ON matchs1.Id_journee = ej1.Id_journee
  JOIN kp_evenement_journee ej2 ON ej1.Id_evenement = ej2.Id_evenement
  JOIN kp_match matchs2 ON matchs2.Id_journee = ej2.Id_journee
  WHERE matchs2.Id = :idMatch
    AND matchs1.Numero_ordre = :numTarget
    AND matchs1.Id != matchs2.Id;
";
$stmt = $myBdd->pdo->prepare($sql);
$stmt->execute([
  ':idMatch' => $idMatch,
  ':numTarget' => $numTarget
]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($stmt->rowCount() === 1) {
  $idTarget = $row['Id'];
  return_200($row);
}
if ($stmt->rowCount() > 1) {
  return_404('Too many games found');
}

/* Par défaut */
if ($idTarget === null) {
  return_404();
}
