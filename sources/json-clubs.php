<?php
include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

$myBdd = new MyBdd();

// Chargement des Clubs ayant une équipe inscrite dans une compétition de polo ...
$arrayClub = array();
$sql  = "SELECT DISTINCT c.Code, c.Libelle, c.Coord, c.Postal, c.www, c.email, "
        . "cd.Libelle comitedep, cr.Libelle comitereg, "
        . "GROUP_CONCAT(CONCAT_WS('|', e.Numero, e.Libelle) ORDER BY e.Libelle ASC SEPARATOR ';') Equipes "
//        . "GROUP_CONCAT(CONCAT_WS(',', e.Numero, e.Libelle) ORDER BY e.Libelle ASC SEPARATOR ';') Equipes "
        ."FROM gickp_Club c, gickp_Equipe e, "
        . "gickp_Comite_dep cd, gickp_Comite_reg cr "
//        . "FROM gickp_Club c "
//        . "LEFT OUTER JOIN gickp_Equipe e ON (c.Code = e.Code_club), "
        ."WHERE c.Code = e.Code_club "
        . "AND c.Coord IS NOT NULL "
        . "AND c.Coord != '' "
        . "AND c.Code_comite_dep = cd.Code "
        . "AND cd.Code_comite_reg = cr.Code "
        . "GROUP BY c.Code "
        . "ORDER BY c.Officiel DESC, c.Code, c.Libelle ";
$result = $myBdd->Query($sql);
while ($row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC)){ 
    $coord = explode(',', $row['Coord']);
    $row['lat'] = $coord[0];
    $row['lon'] = $coord[1];
    $row['Equipes'] = explode(';', $row['Equipes']);
    array_push($arrayClub, $row);
}

header('content-type:application/json');
echo json_encode($arrayClub);
	

