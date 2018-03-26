<?php
include_once('base.php');

$voie = utyGetInt($_GET, 'voie', 1);

$db = new MyBdd();

$rTV = null;
$db->LoadRecord("Select * from gickp_Tv Where Voie = $voie", $rTV);

if (isset($rTV['Url']))
	echo $rTV['Url'];
else
	echo '';
