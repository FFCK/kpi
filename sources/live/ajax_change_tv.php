<?php
//include_once('base.php');
include_once('../commun/MyParams.php');
include_once('../commun/MyTools.php');
include_once('../commun/MyBdd.php');


$voie = utyGetInt($_GET, 'voie', 1);
$show = utyGetString($_GET, 'show');

$competition = utyGetString($_GET, 'competition');
$match = utyGetString($_GET, 'match');
$team = utyGetString($_GET, 'team');
$number = utyGetString($_GET, 'number');
$medal = utyGetString($_GET, 'medal');

$db = new MyBdd();

$url  = "live/tv.php";
$url .= "?show=$show";
$url .= "&competition=$competition";
$url .= "&match=$match";
$url .= "&team=$team";
$url .= "&number=$number";
$url .= "&medal=$medal";

$cmd = "Update gickp_Tv Set Url = '$url' Where Voie = $voie ";
$db->Query($cmd);

echo "OK Voie $voie : ".$url;
