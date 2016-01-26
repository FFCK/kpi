<?php
include_once('../commun/MyBdd.php');

$voie = $_GET['voie'];
$show = $_GET['show'];

$competition = '';
if (isset($_GET['competition'])) $competition = $_GET['competition'];

$match = '';
if (isset($_GET['match'])) $match = $_GET['match'];

$team = '';
if (isset($_GET['team'])) $team = $_GET['team'];

$number = '';
if (isset($_GET['number'])) $number = $_GET['number'];

$medal = '';
if (isset($_GET['medal'])) $medal = $_GET['medal'];

$db = new MyBdd();

$url  = "show=$show";
$url .= "&competition=$competition";
$url .= "&match=$match";
$url .= "&team=$team";
$url .= "&number=$number";
$url .= "&medal=$medal";

$cmd = "Update gickp_Tv Set Url = '$url' Where Voie = $voie ";
$db->Query($cmd);

echo "OK Voie $voie : ".$url;
?>