<?php
include_once('../commun/MyBdd.php');
// header('Content-type:text/html; charset=utf-8');

$time1 = time();
$codeCompet = '';
$codeCompet2 = '';

$myBdd = new MyBdd();
$saison = $myBdd->GetActiveSaison();
$sql = "SELECT DISTINCT(Code_competition) 
    FROM `kp_journee` 
    WHERE 1 
    AND Code_saison = ? 
    AND Date_debut > CURDATE() 
    AND DATEDIFF(Date_debut, CURDATE()) < 6 
    AND (Code_competition LIKE 'N%' 
        OR Code_competition LIKE 'CF%') ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array($saison));
while ($row = $result->fetch()) {
    if (isset($codeCompet)) {
        $codeCompet .= ',';
    }
    $codeCompet .= '"' . $row['Code_competition'] . '"';
}
if (isset($codeCompet)) {
    $sql = "UPDATE kp_competition 
        SET Verrou = 'O' 
        WHERE Code_saison = ? 
        AND Code IN (?) ";
    $result = $myBdd->pdo->prepare($sql);
    $result->execute(array($saison, $codeCompet));
}

$sql = "SELECT DISTINCT(Code_competition) 
    FROM `kp_journee` 
    WHERE 1 
    AND Code_saison = ?
    AND Date_fin < CURDATE() 
    AND DATEDIFF(CURDATE(), Date_fin) < 3 
    AND (Code_competition LIKE 'N%' 
        OR Code_competition LIKE 'CF%') ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array($saison));
while ($row = $result->fetch()) {
    if (isset($codeCompet2)) {
        $codeCompet2 .= ',';
    }
    $codeCompet2 .= '"' . $row['Code_competition'] . '"';
}
if (isset($codeCompet2)) {
    $sql = "UPDATE kp_competition 
        SET Verrou = 'N' 
        WHERE Code_saison = ? 
        AND Code IN (?) ";
    $result = $myBdd->pdo->prepare($sql);
    $result->execute(array($saison, $codeCompet2));
}

$fp = fopen("log_cron.txt", "a");
fputs($fp, "\n"); // on va a la ligne
fputs($fp, date('Y-m-d H:s') . " - " 
    . "Verrou competitions : $codeCompet, deverrou competitions : $codeCompet2"); // on ecrit la ligne
fclose($fp);
