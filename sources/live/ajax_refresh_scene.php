<?php
include_once('base.php');

$voie = utyGetInt($_POST, 'voie', 1);

$myBdd = new MyBdd();

$scene = $voie % 100;
$voie_min = $voie - $scene + 1;
$voie_max = $voie_min + 9;

$rTV = null;
$sql = "SELECT * 
    FROM gickp_Tv 
    WHERE Voie > ? AND Voie <= ?
    AND Url != ''
    ORDER BY Voie
    LIMIT 1 ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array($voie, $voie_max));
$rTV = $result->fetch(PDO::FETCH_ASSOC);

if (!isset($rTV['Url']) || $rTV['Url'] == '') {
    $sql = "SELECT * 
        FROM gickp_Tv 
        WHERE Voie = ?
        ORDER BY Voie DESC
        LIMIT 1 ";
    $result = $myBdd->pdo->prepare($sql);
    $result->execute(array($voie_min));
    $rTV = $result->fetch(PDO::FETCH_ASSOC);
}

header('Content-Type: application/json');
if (isset($rTV['Url'])) {
	echo json_encode($rTV);
} else {
	echo '';
}