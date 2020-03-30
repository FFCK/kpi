<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

session_start();
$myBdd = new MyBdd();
$format = $myBdd->RealEscapeString(trim(utyGetGet('format', 'csv')));
$saison = (int) $myBdd->RealEscapeString(trim(utyGetGet('saison')));
$competitions = explode(",", $myBdd->RealEscapeString(trim(utyGetGet('competitions'))));
$competitions = "'" . implode("','", $competitions) . "'";
//$all = (int) $myBdd->RealEscapeString(trim(utyGetGet('all', 2)));
if($saison > 2000 && $competitions != '') {
    $result_array = [];
    $sql = "SELECT lc.Matric, lc.Nom, lc.Prenom, j.Code_competition, m.Numero_ordre, m.Date_match, m.Heure_match 
            FROM `gickp_Matchs_Joueurs` mj, gickp_Matchs m, gickp_Journees j, gickp_Liste_Coureur lc
            WHERE mj.Matric = lc.Matric
            AND mj.Id_match = m.Id
            AND m.Id_journee = j.Id
            AND j.Code_saison = $saison
            AND j.Code_competition IN ($competitions)
            ORDER BY lc.Matric, m.Date_match, m.Heure_match";

    $result = $myBdd->Query($sql);
    // echo "Licence,Nom,Prenom,Competition,NumMatch,Date,Heure<br>";
    while ($row = $myBdd->FetchArray($result)){
        $result_array[] = $row;
    }

    if ($format == 'json') {
        header('Content-Type: application/json');
        echo json_encode($result_array);
    } else { // CSV
        header("Content-Type: text/csv");
        $out = fopen('php://output', 'w');
        fputcsv($out, array('Licence', 'Name', 'First name', 'Compet', 'Game', 'Date', 'Time'));
        foreach ($result_array as $row) {
            fputcsv($out, $row);
        }
        fclose($out);
    }

} else {
    echo "Incorrect parameters (example: api_players.php?saison=20xx&competitions=CODE1,CODE2&all=0 )";
}
die();