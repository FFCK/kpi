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

	session_start();

	$myBdd = new MyBdd();
	$idMatch = (int)$_POST['idMatch'];
	$action = $myBdd->RealEscapeString(trim($_POST['action']));
	$start_time = $_POST['start_time'];
	$run_time = $_POST['run_time'];
	$max_time = $_POST['max_time'];
	if($action == 'RAZ'){
		$sql  = "DELETE FROM gickp_Chrono "
                . "WHERE IdMatch = '".$idMatch."' ";
	}else{
		$start_time_server = time()%86400; 	// COSANDCO : Prise en compte de l'heure du Serveur ...
		$sql  = "REPLACE gickp_Chrono "
                . "SET IdMatch = '".$idMatch."', "
                . "action = '".$action."', "
                . "start_time = '".$start_time."', "
                . "start_time_server = ".$start_time_server.", "
                . "run_time = '".$run_time."', "
                . "max_time = '".$max_time."' ";
	}
	$myBdd->Query($sql);
	
	// COSANDCO : Creation du Cache ...
	$cMatch = new CacheMatch($_GET);
	$cMatch->MatchChrono($myBdd, $idMatch);
	
	echo "OK"; 
