<?php
include_once('../commun/MyBdd.php');

$voie = $_GET['voie'];

$db = new MyBdd();

$rTV = null;
$db->LoadRecord("Select * from gickp_Tv Where Voie = $voie", $rTV);

if (isset($rTV['Url']))
	echo $rTV['Url'];
else
	echo '';
