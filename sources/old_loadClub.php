<?php
 //prevent direct access *****************************************************
//$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
//strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
//if(!$isAjax) {
//    $user_error = 'Access denied !';
//    die($user_error);
//    //trigger_error($user_error, E_USER_ERROR);
//}
 //***************************************************************************

include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

$myBdd = new MyBdd();

// Chargement des Clubs ayant une équipe inscrite dans une compétition de polo ...
$a_json = array();
$term = trim(utyGetGet('term'));
// replace multiple spaces with one
$term = preg_replace('/\s+/', ' ', $term);
$sql  = "SELECT distinct c.Code, c.Libelle, c.Coord, c.Postal, c.Coord2, c.www, c.email, cd.Libelle comitedep, cr.Libelle comitereg "
        . "FROM gickp_Club c, gickp_Equipe e, gickp_Comite_dep cd, gickp_Comite_reg cr "
        . "WHERE c.Code = e.Code_club "
        . "AND c.Code_comite_dep = cd.Code "
        . "AND cd.Code_comite_reg = cr.Code "
        . "AND c.Code = '".$term."' ";
$result = $myBdd->Query($sql);
$row = $myBdd->FetchAssoc($result);
$jRow["value"] = $row['Libelle'];
$jRow["idClub"] = $row['Code'];
$jRow["label"] = $row['Libelle'];
$jRow["www"] = $row['www'];
$jRow["email"] = $row['email'];
$jRow["postal"] = $row['Postal'];
$jRow["coord"] = $row['Coord'];
$jRow["comitedep"] = $row['comitedep'];
$jRow["comitereg"] = $row['comitereg'];

//Logos
$logo = '';
$club = $row['Code'];
if(is_file('img/KIP/logo/'.$club.'-logo.png')){
    $logo = 'img/KIP/logo/'.$club.'-logo.png';
}elseif(is_file('img/Nations/'.substr($club, 0, 3).'.png')){
    $club = substr($club, 0, 3);
    $logo = 'img/Nations/'.$club.'.png';
}
$jRow["logo"] = $logo;
$jRow["club"] = $club;

//Equipes
$sql2  = "SELECT Numero, Libelle "
        . "FROM gickp_Equipe "
        . "WHERE Code_club = '".$row['Code']."' "
        . "ORDER BY Libelle ";
$result2 = $myBdd->Query($sql2);
while ($row2 = $myBdd->FetchArray($result2)){ 
    $jRow["equipes"][] = $row2;
}
array_push($a_json, $jRow);
$json = json_encode($a_json);
print $json;
