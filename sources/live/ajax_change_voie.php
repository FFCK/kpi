<?php
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

$voie = utyGetInt($_GET, 'voie', 0);
$url = utyGetString($_GET, 'url');

$url = str_replace("|QU|", "?", $url);
$url = str_replace("|AM|", "&", $url);
$url = str_replace("|HA|", "#", $url);

$myBdd = new MyBdd();

$sql = "UPDATE kp_tv 
    SET `Url` = ? 
    WHERE Voie = ? ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array($url, $voie));

$filename = $_SERVER['DOCUMENT_ROOT'] . "/live/cache/voie_$voie.json";
$content = json_encode([
    'voie' => $voie,
    'url' => urlencode($url),
    'intervalle' => 10000,
    'timestamp' => date('Ymdhis')
]);
file_put_contents($filename, $content);

echo "OK Voie $voie : " . $url;
