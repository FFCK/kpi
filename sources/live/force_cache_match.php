<?php
include_once('base.php');
include_once('create_cache_match.php');

// Exemple 
// https://www.kayak-polo.info/live/force_cache_event.php?match=79262229

$idMatch = $_GET['match'];

$db = new MyBase();
$cache = new CacheMatch($_GET);
$cache->Match($db, $idMatch);
