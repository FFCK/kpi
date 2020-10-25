<?php
include_once('../commun/MyBdd.php');
include_once('create_cache_match.php');

$event = utyGetGet('event', false);
$match = utyGetGet('match', false);
$pitch = utyGetGet('pitch', false);

$db = new MyBdd();

$array['cache'] = 1;
$cache = new CacheMatch($array);
$cache->Pitch($event, $pitch, $match);
$cache->Match($db, $match);

echo "OK Match $match Pitch $pitch Event $event";
