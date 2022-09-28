<?php
// prevent direct access *****************************************************
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) and
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if (!$isAjax) {
    $user_error = 'Access denied !';
    die($user_error);
    //trigger_error($user_error, E_USER_ERROR);
}
// ***************************************************************************

include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

$myBdd = new MyBdd();

// Chargement des Clubs ayant une équipe inscrite dans une compétition de polo ...
$a_json = array();
$term = trim(utyGetGet('term'));
// replace multiple spaces with one
$term = preg_replace('/\s+/', ' ', $term);
$term2 = preg_replace('/\s/', '-', $term);
$sql = "SELECT c.Code, c.Libelle, c.Coord, c.Postal, c.Coord2, c.www, c.email, 
        GROUP_CONCAT(CONCAT_WS(',', e.Numero, e.Libelle) ORDER BY e.Libelle ASC SEPARATOR ';') 
        FROM kp_club c LEFT OUTER JOIN kp_equipe e ON (c.Code = e.Code_club) 
        WHERE 1=1 
        AND (c.Code LIKE :term 
        OR c.Libelle LIKE :term1 
        OR c.Libelle LIKE :term2 
        OR e.Libelle LIKE :term3 
        OR e.Libelle LIKE :term4) 
        GROUP BY c.Code 
        ORDER BY c.Officiel DESC, c.Code, c.Libelle ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array(
    ':term' => $term . '%',
    ':term1' => '%' . $term . '%',
    ':term2' => '%' . $term2 . '%',
    ':term3' => '%' . $term . '%',
    ':term4' => '%' . $term2 . '%'
));
while ($row = $result->fetch()) {
    $jRow["value"] = $row['Libelle'];
    $jRow["idClub"] = $row['Code'];
    $jRow["label"] = $row['Code'] . ' - ' . $row['Libelle'];
    $jRow["www"] = $row['www'];
    $jRow["email"] = $row['email'];
    $jRow["postal"] = $row['Postal'];
    $jRow["coord"] = $row['Coord'];
    array_push($a_json, $jRow);
}
$json = json_encode($a_json);
print $json;
