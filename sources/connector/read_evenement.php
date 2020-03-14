<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/commun/MyBdd.php');
include_once('replace_evenement.php');

if ( (isset($_GET['url'])) && (isset($_GET['evt'])) )
{
	$url = $_GET['url'];
	$evt = $_GET['evt'];
	$contents = file_get_contents($url.'?lst='.$evt);
	Replace_Evenement($contents);
}
else
{
	echo '{ error }';
}
