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
include_once('../../live/create_cache_match.php');

session_start();

$myBdd = new MyBdd();
$myBdd->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

$idMatch = (int) utyGetPost('idMatch');
if ($idMatch <= 0) {
  echo '';
  return;
}
$sql = "SELECT m.Id idMatch, m.Date_match, m.Heure_match, m.Terrain,
    j.Code_competition, j.Code_saison, ej.Id_evenement 
  FROM kp_match m
  LEFT JOIN kp_journee j ON m.Id_journee = j.Id
  LEFT OUTER JOIN kp_evenement_journee ej ON j.Id = ej.Id_journee
  WHERE m.Id = ? ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array($idMatch));
$row = $result->fetch(PDO::FETCH_ASSOC);

if ($row['Id_evenement'] != null) {
  $sql2 = "SELECT m.Id idMatch, m.Date_match, m.Heure_match, m.Terrain, m.Numero_ordre,
      j.Code_competition, j.Code_saison, ej.Id_evenement,
      a.Libelle equipeA, b.Libelle equipeB
    FROM kp_match m
    LEFT JOIN kp_journee j ON m.Id_journee = j.Id
    LEFT JOIN kp_evenement_journee ej ON j.Id = ej.Id_journee
    LEFT JOIN kp_competition_equipe a ON m.Id_equipeA = a.Id
    LEFT JOIN kp_competition_equipe b ON m.Id_equipeB = b.Id
    WHERE ej.Id_evenement = ?
    AND m.Terrain = ?
    AND CONCAT(m.Date_match, ' ', m.Heure_match) > CONCAT(?, ' ', ?)
    ORDER BY m.Date_match, m.Heure_match
    LIMIT 1";
  $result2 = $myBdd->pdo->prepare($sql2);
  $result2->execute(array(
    $row['Id_evenement'], $row['Terrain'], $row['Date_match'], $row['Heure_match']
  ));
} else {
  $sql2 = "SELECT m.Id idMatch, m.Date_match, m.Heure_match, m.Terrain, m.Numero_ordre,
      j.Code_competition, j.Code_saison,
      a.Libelle equipeA, b.Libelle equipeB
    FROM kp_match m
    LEFT JOIN kp_journee j ON m.Id_journee = j.Id
    LEFT JOIN kp_competition_equipe a ON m.Id_equipeA = a.Id
    LEFT JOIN kp_competition_equipe b ON m.Id_equipeB = b.Id
    WHERE j.Code_competition = ?
    AND j.Code_saison = ?
    AND m.Terrain = ?
    AND CONCAT(m.Date_match, ' ', m.Heure_match) > CONCAT(?, ' ', ?)
    ORDER BY m.Date_match, m.Heure_match
    LIMIT 1";
  $result2 = $myBdd->pdo->prepare($sql2);
  $result2->execute(array(
    $row['Code_competition'], $row['Code_saison'], $row['Terrain'], $row['Date_match'], $row['Heure_match']
  ));
}
$row2 = $result2->fetch(PDO::FETCH_ASSOC);

return_200($row2);
