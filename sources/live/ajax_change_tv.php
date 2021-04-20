<?php
//include_once('base.php');
include_once('../commun/MyParams.php');
include_once('../commun/MyTools.php');
include_once('../commun/MyBdd.php');

$voie = utyGetInt($_GET, 'voie', 1);
$show = utyGetString($_GET, 'show');

$competition = utyGetString($_GET, 'competition');
$saison = utyGetString($_GET, 'saison');
$match = utyGetString($_GET, 'match');
$team = utyGetString($_GET, 'team');
$number = utyGetString($_GET, 'number');
$start = utyGetString($_GET, 'start');
$medal = utyGetString($_GET, 'medal');

$myBdd = new MyBdd();

$url  = "live/tv2.php";
$url .= "?show=$show";
$url .= "&saison=$saison";
$url .= "&competition=$competition";
$url .= "&match=$match";
$url .= "&team=$team";
$url .= "&number=$number";
$url .= "&start=$start";
$url .= "&medal=$medal";

$sql = "UPDATE kp_tv 
    SET `Url` = ? 
    WHERE Voie = ? ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array($url, $voie));

echo "OK Voie $voie : ".$url;
