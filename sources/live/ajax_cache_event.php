<?php
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');
include_once('create_cache_match.php');

// Exemple 
// https://www.kayak-polo.info/live/ajax_cache_event.php?id_event=86&date_event=2017-08-24&hour_event=&offset_event=10&pitch_event=&delay_event=10

$idEvent = utyGetGet('id_event', false);
$dateEvent = utyGetGet('date_event', false);
$hourEvent = utyGetGet('hour_event', false);
$offset_event = utyGetGet('offset_event', false);
$pitch_event = utyGetGet('pitch_event', false);
$arrayPitchs = [];

if ($pitch_event > 0) {
    for ($i = 1; $i <= $pitch_event; $i++) {
        $arrayPitchs[] = $i;
    }
}

// Ajuster selon le fuseau horaire
if ($hourEvent == '') {
    $hourEvent = date('H:i', strtotime(DECALAGE_HORAIRE));
}

$time = utyHHMM_To_MM($hourEvent);
$time += $offset_event;
$hourEventWork = utyMM_To_HHMM($time);

$db = new MyBdd();
$cache = new CacheMatch($_GET);
$arrayResult['pitches'] = $cache->Event($db, $idEvent, $dateEvent, $hourEventWork, $arrayPitchs);
$arrayResult['time'] = [
    'currentTime' => $hourEvent,
    'workingTime' => $hourEventWork
];
return_200($arrayResult);
