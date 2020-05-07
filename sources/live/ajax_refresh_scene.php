<?php
include_once('base.php');

$voie = utyGetInt($_GET, 'voie', 1);

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
$rTV = $result->fetch();

if (!isset($rTV['Url']) || $rTV['Url'] == '') {
    $sql = "SELECT * 
        FROM gickp_Tv 
        WHERE Voie = ?
        ORDER BY Voie DESC
        LIMIT 1 ";
    $result = $myBdd->pdo->prepare($sql);
    $result->execute(array($voie_max));
    $rTV = $result->fetch();
}

header('Content-Type: application/json');
if (isset($rTV['Url'])) {
	echo json_encode($rTV);
} else {
	echo '';
}