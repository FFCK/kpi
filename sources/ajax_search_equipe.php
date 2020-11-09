<?php
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

$myBdd = new MyBdd();
// Chargement
$term = $myBdd->RealEscapeString(trim(utyGetGet('q', '')));
// replace multiple spaces with one
$term = preg_replace('/\s+/', ' ', $term);
// supprime les 0 devant les numéros
$term = preg_replace('`^[0]*`','',$term);

$a_json = array();
$jRow = array();

$sql = "SELECT e.Numero, e.Libelle, e.Code_club, c.Libelle nomClub 
    FROM gickp_Equipe e, gickp_Club c, gickp_Comite_dep cd 
    WHERE (UPPER(e.Libelle) LIKE UPPER(:term) 
    OR UPPER(e.Code_club) LIKE UPPER(:term) 
    OR UPPER(c.Libelle) LIKE UPPER(:term)) 
    AND e.Code_club = c.Code 
    AND c.Code_comite_dep = cd.Code 
    AND cd.Code_comite_reg != '98' 
    ORDER BY e.Libelle ";

$result = $myBdd->pdo->prepare($sql);
$result->execute([':term' => '%'.$term.'%']);
while ($row = $result->fetch()) {
    $jRow["id"] = $row["Numero"];
    $jRow["Libelle"] = $row["Libelle"];
    $jRow["Club"] = $row["Code_club"];
    array_push($a_json, $jRow);
}

header('Content-Type: application/json');
echo json_encode($a_json);