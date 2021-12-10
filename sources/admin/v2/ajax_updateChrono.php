<?php
// prevent direct access *****************************************************
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) and
	strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if (!$isAjax) {
	$user_error = 'Access denied - not an AJAX request...';
	trigger_error($user_error, E_USER_ERROR);
}
// ***************************************************************************

include_once('../../commun/MyBdd.php');
include_once('../../commun/MyTools.php');
include_once('../../live/create_cache_match.php');

session_start();

$myBdd = new MyBdd();
$idMatch = (int) utyGetPost('idMatch');
$start_time = trim(utyGetPost('start_time'));
$run_time = trim(utyGetPost('run_time'));

$start_time_server = time() % 86400; 	// COSANDCO : Prise en compte de l'heure du Serveur ...
$sql = "UPDATE kp_chrono 
	SET start_time = ?, 
	start_time_server = ?, 
	run_time = ? 
	WHERE IdMatch = ? ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array($start_time, $start_time_server, $run_time, $idMatch));

// COSANDCO : Creation du Cache ...
$cMatch = new CacheMatch($_GET);
$cMatch->MatchChrono($myBdd, $idMatch);

echo "OK";
