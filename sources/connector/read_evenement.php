<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/commun/MyBdd.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/commun/MyTools.php');
include_once('replace_evenement.php');

if ( utyGetGet('url', false) && utyGetGet('evt', false) ) {
	$url = utyGetGet('url', false);
	$evt = utyGetGet('evt', false);
	$contents = file_get_contents($url.'?lst='.$evt);
	Replace_Evenement($contents);
} else {
	echo '{ error }';
}
