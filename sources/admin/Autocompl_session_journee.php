<?php
// prevent direct access *****************************************************
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if(!$isAjax) {
  $user_error = 'Access denied - not an AJAX request...';
  trigger_error($user_error, E_USER_ERROR);
}
// ***************************************************************************
include_once('../commun/MyTools.php');
if(!isset($_SESSION)) {
	session_start(); 
}

// Chargement
$j = trim(utyGetGet('j',''));
if ($j == '*')
	$j = '';
$_SESSION['sessionJournee'] = $j;
echo '"'.$_SESSION['sessionJournee'].'"';
	