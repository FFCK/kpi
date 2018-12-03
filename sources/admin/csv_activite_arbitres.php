<?php
include_once('../commun/MyBdd.php');

$myBdd = new MyBdd();
// Chargement
$sql  = "SELECT lc.Matric, lc.Nom, lc.Prenom, lc.Sexe, lc.Naissance, lc.Numero_club Club,
    a.Arb, a.niveau, a.saison annee, j.Code_competition Competition, j.Code_saison Saison, m.Id as 'Match', 'Principal' as arbitre
    FROM `gickp_Liste_Coureur` lc, `gickp_Journees` j, `gickp_Arbitre` a
    LEFT OUTER JOIN `gickp_Matchs` m ON a.Matric = m.Matric_arbitre_principal
    WHERE 1=1
    AND m.Id_journee = j.Id
    AND a.Matric = lc.Matric
    AND lc.Numero_comite_reg != 98
    AND j.Code_competition NOT LIKE 'M%'
    AND a.saison = 2018

    UNION

    SELECT lc.Matric, lc.Nom, lc.Prenom, lc.Sexe, lc.Naissance, lc.Numero_club Club,
    a.Arb, a.niveau, a.saison annee, j.Code_competition Competition, j.Code_saison Saison, m.Id as 'Match', 'Secondaire' as arbitre
    FROM `gickp_Liste_Coureur` lc, `gickp_Journees` j, `gickp_Arbitre` a
    LEFT OUTER JOIN `gickp_Matchs` m ON a.Matric = m.Matric_arbitre_secondaire
    WHERE 1=1
    AND m.Id_journee = j.Id
    AND a.Matric = lc.Matric
    AND lc.Numero_comite_reg != 98
    AND j.Code_competition NOT LIKE 'M%'
    AND a.saison = 2018

    ORDER BY Nom, Prenom, Matric, Saison, Competition";

// Creates a new csv file and store it in tmp directory
$new_csv = fopen('/tmp/report.csv', 'w');
fputcsv($new_csv, array(
    'Matric', 'Nom', 'Prenom', 'Sexe', 'Naissance', 'Club', 'Arb', 'Niveau', 'Annee', 'Competition', 'Saison', 'Match', 'Arbitre'
));

$result = $myBdd->Query($sql);
while ($row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC)){
    fputcsv($new_csv, $row);
}

fclose($new_csv);

// output headers so that the file is downloaded rather than displayed
header("Content-type: text/csv");
header("Content-disposition: attachment; filename = activite_arbitres.csv");
readfile("/tmp/report.csv");

