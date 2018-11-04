<?php
include_once('commun/MyBdd.php');

$myBdd = new MyBdd();
// Chargement
$term = $myBdd->RealEscapeString(trim($_GET['q']));
// replace multiple spaces with one
$term = preg_replace('/\s+/', ' ', $term);
// supprime les 0 devant les numéros
$term = preg_replace('`^[0]*`','',$term);

$a_json = array();
$jRow = array();
    
$sql  = "Select e.Numero, e.Libelle, e.Code_club, c.Libelle nomClub ";
$sql .= "From gickp_Equipe e, gickp_Club c, gickp_Comite_dep cd ";
$sql .= "Where (UPPER(e.Libelle) LIKE UPPER('%".$term."%') ";
$sql .= "Or UPPER(e.Code_club) LIKE UPPER('%".$term."%') ";
$sql .= "Or UPPER(c.Libelle) LIKE UPPER('%".$term."%')) ";
$sql .= "And e.Code_club = c.Code ";
$sql .= "And c.Code_comite_dep = cd.Code ";
$sql .= "And cd.Code_comite_reg != '98' ";
$sql .= "Order by e.Libelle ";

$result = $myBdd->Query($sql);
while($row = $myBdd->FetchAssoc($result)) {
    $jRow["id"] = $row["Numero"];
    $jRow["Libelle"] = $row["Libelle"];
    $jRow["Club"] = $row["Code_club"];
    array_push($a_json, $jRow);
}

header('Content-Type: application/json');
echo json_encode($a_json);
