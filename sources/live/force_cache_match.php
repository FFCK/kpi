<?php
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');
include_once('create_cache_match.php');

// Exemple 
// https://www.kayak-polo.info/live/force_cache_event.php?match=79262229

$idMatch = utyGetGet('match', 0);

$db = new MyBdd();
$cache = new CacheMatch($_GET);
if($cache->Match($db, $idMatch)) {
    echo "OK";
} else {
    echo "Hey !";
}
