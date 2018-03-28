<?php

header('Content-type:text/html; charset=utf-8');
$time1 = time();
include_once('../commun/MyBdd.php');

$myBdd = new MyBdd();
$saison = $myBdd->GetActiveSaison();
$sql = "SELECT DISTINCT(Code_competition) ";
$sql .= "FROM `gickp_Journees` ";
$sql .= "WHERE 1 ";
$sql .= "AND Code_saison = $saison ";
$sql .= "AND Date_debut > CURDATE() ";
$sql .= "AND DATEDIFF(Date_debut, CURDATE()) < 6 ; ";
//$sql .= "AND (Code_competition LIKE 'N%' OR Code_competition LIKE 'CF%') ";
$result = $myBdd->Query($sql);
while ($row = $myBdd->FetchArray($result, $resulttype = MYSQL_ASSOC)) {
    if (isset($codeCompet)) {
        $codeCompet .= ',';
    }
    $codeCompet .= '"' . $row['Code_competition'] . '"';
}
if (isset($codeCompet)) {
    $sql = "UPDATE gickp_Competitions SET Verrou = 'O' WHERE Code_saison = $saison AND Code IN ($codeCompet) ";
    $myBdd->Query($sql);
}

$sql = "SELECT DISTINCT(Code_competition) ";
$sql .= "FROM `gickp_Journees` ";
$sql .= "WHERE 1 ";
$sql .= "AND Code_saison = $saison ";
$sql .= "AND Date_fin < CURDATE() ";
$sql .= "AND DATEDIFF(CURDATE(), Date_fin) < 3 ; ";
//$sql .= "AND (Code_competition LIKE 'N%' OR Code_competition LIKE 'CF%') ";
$result = $myBdd->Query($sql);
while ($row = $myBdd->FetchArray($result, $resulttype = MYSQL_ASSOC)) {
    if (isset($codeCompet2)) {
        $codeCompet2 .= ',';
    }
    $codeCompet2 .= '"' . $row['Code_competition'] . '"';
}
if (isset($codeCompet2)) {
    $sql = "UPDATE gickp_Competitions SET Verrou = 'N' WHERE Code_saison = $saison AND Code IN ($codeCompet2) ";
    $myBdd->Query($sql);
}

$fp = fopen("log_cron.txt", "a");
fputs($fp, "\n"); // on va a la ligne
fputs($fp, date('Y-m-d H:s') . " - " . "Verrou competitions : $codeCompet, deverrou competitions : $codeCompet2"); // on ecrit la ligne
fclose($fp);
