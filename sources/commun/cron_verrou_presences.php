<?php
include_once('MyBdd.php');
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

// Log de l'exécution
$msg = date('Y-m-d H:s') . " - Verrou competitions : $codeCompet - Déverrou competitions : $codeCompet2";
error_log($msg);

// Envoi du mail uniquement s'il y a des compétitions verrouillées ou déverrouillées
if (!empty($codeCompet) || !empty($codeCompet2)) {
    $headers = 'From: KPI <contact@kayak-polo.info>' . "\r\n";
    mail('contact@kayak-polo.info', '[KPI-CRON] Verrou présences', $msg, $headers);
}
