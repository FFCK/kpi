<?php
//include_once('base.php');
include_once('../commun/MyParams.php');
include_once('../commun/MyTools.php');
include_once('../commun/MyBdd.php');

$voie = utyGetInt($_POST, 'voie', 1);
$show = utyGetString($_POST, 'show');

$competition = utyGetString($_POST, 'competition');
$saison = utyGetString($_POST, 'saison');
$match = utyGetString($_POST, 'match');
$team = utyGetString($_POST, 'team');
$number = utyGetString($_POST, 'number');
$start = utyGetString($_POST, 'start');
$medal = utyGetString($_POST, 'medal');

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

$sql = "UPDATE gickp_Tv 
    SET `Url` = ? 
    WHERE Voie = ? ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array($url, $voie));

echo "OK Voie $voie : ".$url;
