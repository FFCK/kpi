<?php
/* 
 * Parser un fichier XML
 * 
 */
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

/**
 * Constantes
 */
const SAISON = 2022;
const COMPETITION_CODE = 1507; // WC 2022
const COMPETITION_LABEL = "Championnats du monde 2022"; // WC 2022
// Event 1 = U21 M
// Event 2 = U21 W
// Event 3 = Senior M
// Event 4 = Senior W
$catArray = ['CMH21' => 'U21 MEN', 'CMF21' => 'U21 WOMEN', 'CMH' => 'SENIOR MEN', 'CMF' => 'SENIOR WOMEN'];

/**
 * Traitement fichier
 */
header('Content-Type: text/html; charset=utf-8');

$result = [];
$myBdd = new MyBdd();
$sql = "SELECT ce.Code_club, ce.Code_compet, cej.Matric, cej.Numero, cej.Nom, cej.Prenom
    FROM kp_competition_equipe_joueur cej
    LEFT JOIN kp_competition_equipe ce ON ce.Id = cej.Id_equipe
    WHERE ce.Code_saison = 2022 
    AND ce.Code_compet IN ('CMH', 'CMF', 'CMH21', 'CMF21') ";
$stmt = $myBdd->pdo->prepare($sql);
$stmt->execute();
while ($listing = $stmt->fetch()) {
    $result[] = [
        substr($listing['Code_club'], 0, 3),
        $listing['Code_compet'],
        $catArray[$listing['Code_compet']],
        '',
        $listing['Matric'],
        '',
        $listing['Nom'],
        $listing['Prenom'],
        '',
        '',
        '',
        '',
        $listing['Numero'],
        '',
        '',
        ''
    ];
}

$fp = fopen('./uploads/players_' . date('Ymd_His') . '.csv', 'w');
fputcsv($fp, array(
    'Country',
    'KPICat',
    'Cat',
    'KPITeamId',
    'KPIId',
    'IcfId',
    'FamilyName',
    'FirstName',
    'Gender',
    'Birthdate',
    'Height',
    'Weigth',
    'Number',
    'KPILicenceDB',
    'KPIRosterDB',
    'Fonction'
), ';');

foreach ($result as $row) {
    fputcsv($fp, $row, ';');
}

echo 'Fichier csv : ';
echo '<a href="./uploads/players_' . date('Ymd_His') . '.csv">Import</a>';
echo '<br><br>';
echo '<hr>';

echo '<a href="">Back</a>';

unlink($fileName);
