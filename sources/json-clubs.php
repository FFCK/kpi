<?php
include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

$myBdd = new MyBdd();

// Chargement des Clubs ayant une équipe inscrite dans une compétition de polo ...
$arrayClub = array();
$sql  = "SELECT DISTINCT c.Code, c.Libelle, c.Coord, c.Postal, c.www, c.email, 
    cd.Libelle comitedep, cr.Libelle comitereg, 
    GROUP_CONCAT(CONCAT_WS('|', e.Numero, e.Libelle) 
        ORDER BY e.Libelle ASC SEPARATOR ';') Equipes 
    FROM kp_club c, kp_equipe e, 
    kp_cd cd, kp_cr cr 
    WHERE c.Code = e.Code_club 
    AND c.Code_comite_dep = cd.Code 
    AND cd.Code_comite_reg = cr.Code 
    GROUP BY c.Code 
    ORDER BY c.Officiel DESC, c.Code, c.Libelle ";
$result = $myBdd->pdo->query($sql);
while ($row = $result->fetch(PDO::FETCH_ASSOC)){ 
    if($row['Coord'] == NULL) {
        $row['lat'] = "";
        $row['lon'] = "";
    } else {
        $coord = explode(',', $row['Coord']);
        $row['lat'] = trim($coord[0]);
        $row['lon'] = trim($coord[1]);
    }
    $row['Equipes'] = explode(';', $row['Equipes']);
    array_push($arrayClub, $row);
}

// Mise à jour du ficher json
file_put_contents('clubs.json', json_encode($arrayClub));

// Affichage du contenu pour contrôle
header('content-type:application/json');
echo json_encode($arrayClub);
	

