<?php
include_once('base.php');

$voie = utyGetInt($_GET, 'voie', 1);

$db = new MyBdd();

$scene = $voie % 100;
$voie_min = $voie - $scene + 1;
$voie_max = $voie_min + 9;

$rTV = null;
$db->LoadRecord("SELECT * 
    FROM gickp_Tv 
    WHERE Voie > $voie AND Voie <= $voie_max
    AND Url != ''
    ORDER BY Voie
    LIMIT 1 ", $rTV);

if (!isset($rTV['Url']) || $rTV['Url'] == '') {
    $db->LoadRecord("SELECT * 
        FROM gickp_Tv 
        WHERE Voie = $voie_min
        ORDER BY Voie DESC
        LIMIT 1 ", $rTV);
}

header('Content-Type: application/json');
if (isset($rTV['Url'])) {
	echo json_encode($rTV);
} else {
	echo '';
}