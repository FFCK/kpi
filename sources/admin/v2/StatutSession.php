<?php 
// prevent direct access *****************************************************
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if(!$isAjax) {
  $user_error = 'Access denied - not an AJAX request...';
  trigger_error($user_error, E_USER_ERROR);
}
// ***************************************************************************
	
include_once('../../commun/MyBdd.php');
include_once('../../commun/MyTools.php');

if(!isset($_SESSION)) {
	session_start(); 
}

$myBdd = new MyBdd();
$TypeUpdate = trim(utyGetPost('TypeUpdate'));
if (!in_array($TypeUpdate, ['MatchsNonVerrouilles'])) {
	header('HTTP/1.0 401 Unauthorized');
	die('Action non autorisée !');
}

if ($TypeUpdate == 'MatchsNonVerrouilles') {
    $_SESSION['filtreMatchsNonVerrouilles'] = utyGetPost('Valeur') == 'on' ? 'on' : '';
    echo 'OK';
}

