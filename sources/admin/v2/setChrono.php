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
include_once('../../live/create_cache_match.php');

if(!isset($_SESSION)) {
	session_start(); 
}

$myBdd = new MyBdd();
$idMatch = (int) utyGetPost('idMatch');
$action = trim(utyGetPost('action'));
if ($action == 'RAZ') {
	$sql = "DELETE FROM kp_chrono 
		WHERE IdMatch = ? ";
	$result = $myBdd->pdo->prepare($sql);
	$result->execute(array($idMatch));
} else {
	$start_time = utyGetPost('start_time');
	$run_time = utyGetPost('run_time');
	$max_time = utyGetPost('max_time');
	$shotclock = trim(utyGetPost('shotclock', null));
	$penalties = trim(utyGetJsonPost('penalties', null));
	$start_time_server = time()%86400; 	// COSANDCO : Prise en compte de l'heure du Serveur ...

	$sql = "REPLACE kp_chrono 
		SET IdMatch = ?, 
		`action` = ?, 
		start_time = ?, 
		start_time_server = ?, 
		run_time = ?, 
		max_time = ?,
		shotclock = ?,
		penalties = ?
		 ";
	$result = $myBdd->pdo->prepare($sql);
	$result->execute(array($idMatch, $action, $start_time, $start_time_server, $run_time, $max_time, $shotclock, $penalties));
}

// COSANDCO : Creation du Cache ...
$cMatch = new CacheMatch($_GET);
$cMatch->MatchChrono($myBdd, $idMatch);

echo "OK"; 
