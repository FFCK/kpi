<?php
// prevent direct access *****************************************************
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if(!$isAjax) {
    $user_error = 'Access denied !';
    die($user_error);
    //trigger_error($user_error, E_USER_ERROR);
}
// ***************************************************************************

include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

$myBdd = new MyBdd();

// Chargement des Equipes correspondantes à la recherche
$a_json = array();
$term = trim(utyGetGet('term'));
// replace multiple spaces with one
$term = preg_replace('/\s+/', ' ', $term);
$term2 = preg_replace('/\s/', '-', $term);
$sql = "SELECT * 
    FROM kp_equipe 
    WHERE Code_club LIKE ? 
    OR Libelle LIKE ? 
    OR Libelle LIKE ? 
    ORDER BY Libelle ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array($term.'%', '%'.$term.'%', '%'.$term2.'%'));
while ($row = $result->fetch()) { 
    $jRow['value'] = $row['Libelle'];
    $jRow['idEquipe'] = $row['Numero'];
    $jRow['label'] = $row['Code_club'].' - '.$row['Libelle'].' ('.$row['Numero'].')';
    $jRow['nomEquipe'] = $row['Numero'];
    array_push($a_json, $jRow);
}
$json = json_encode($a_json);
print $json;
