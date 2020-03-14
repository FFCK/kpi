<?php
//include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

session_start();
$myBdd = new MyBdd();
// Chargement
$j = $myBdd->RealEscapeString(trim(utyGetGet('j','')));
if($j == '*')
	$j = '';
$_SESSION['sessionJournee'] = $j;
echo '"'.$_SESSION['sessionJournee'].'"';
	