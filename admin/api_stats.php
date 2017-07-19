<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

session_start();
$myBdd = new MyBdd();
$saison = (int) $myBdd->RealEscapeString(trim(utyGetGet('saison')));
$competitions = explode(",", $myBdd->RealEscapeString(trim(utyGetGet('competitions'))));
$competitions = "'" . implode("','", $competitions) . "'";
if($saison > 2000 && $competitions != '') {
    $arrayStats = [];
    $sql = "SELECT d.Code_competition Competition, a.Matric Licence, a.Nom, 
        a.Prenom, a.Sexe, b.Numero, f.Libelle Equipe, 
        SUM(IF(b.Id_evt_match = 'B', 1, 0)) Buts,
        SUM(IF(b.Id_evt_match = 'V', 1, 0)) Vert,
        SUM(IF(b.Id_evt_match = 'J', 1, 0)) Jaune,
        SUM(IF(b.Id_evt_match = 'R', 1, 0)) Rouge,
        SUM(IF(b.Id_evt_match = 'T', 1, 0)) Tirs,
        SUM(IF(b.Id_evt_match = 'A', 1, 0)) Arrets
        FROM gickp_Liste_Coureur a, gickp_Matchs_Detail b, gickp_Matchs c, gickp_Journees d, gickp_Competitions_Equipes f 
        WHERE a.Matric = b.Competiteur 
        AND b.Id_match = c.Id AND c.Id_journee = d.Id 
        AND d.Code_competition = f.Code_compet 
        AND d.Code_saison = f.Code_saison 
        AND f.Id = IF(b.Equipe_A_B='A',c.Id_equipeA, c.Id_equipeB) 
        AND d.Code_competition IN ($competitions) 
        AND d.Code_saison = $saison
        GROUP BY a.Matric 
        ORDER BY Buts DESC, a.Nom ";
        $result = $myBdd->Query($sql);
        while ($row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC)){ 
            array_push($arrayStats, $row);
        }

        header('Content-Type: application/json');
        echo json_encode($arrayStats);
} else {
    echo "Paramètres incorrects (exemple: api_stats.php&saison=20xx&competitions=CODE1,CODE2 )";
}
die();