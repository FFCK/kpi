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

if(!isset($_SESSION)) {
	session_start(); 
}

$myBdd = new MyBdd();
$idMatch = (int) utyGetPost('idMatch', 0);
$action = trim(utyGetPost('action'));
$idLigne = trim(utyGetPost('idLigne'));
$idLigne = explode('_', $idLigne);
// M1-00:00-V-A-186002-5
$data = json_decode(html_entity_decode(utyGetPost('ligne')));
if (!isset($data->player)) {
	$data->player = null;
}
if (!isset($data->number)) {
	$data->number = null;
}
// $data->number == '' ? $data->number = null : true;
// var_dump($data);
// Contrôle autorisation journée
$myBdd->AutorisationMatch($idMatch);

if ($action == 'insert') {
	$inserted_id = $idLigne[1] != '0' ? $idLigne[1] : str_replace('-', '', gen_uuid());
	$sql = "INSERT INTO kp_match_detail 
		SET Id = ?, Id_match = ?, Periode = ?, Temps = ?, Id_evt_match = ?, 
		Competiteur = ?, Numero = ?, Equipe_A_B = ?, motif = ? ";
	$result = $myBdd->pdo->prepare($sql);
	$result->execute(array(
		$inserted_id, $idMatch, $data->period, '00:' . $data->time, $data->evt,
		$data->player, $data->number, $data->team, $data->cause
	));
	$myBdd->CheckCardCumulation($data->player, $idMatch, $data->evt, $data->cause);
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode(['id' => $inserted_id]);
} elseif ($action == 'update') {
	$sql = "UPDATE kp_match_detail 
		SET Id_match = ?, Periode = ?, 
		Temps = ?, Id_evt_match = ?, Competiteur = ?, 
		Numero = ?, Equipe_A_B = ?, motif = ? 
		WHERE Id = ? ";
	$result = $myBdd->pdo->prepare($sql);
	$result->execute(array(
		$idMatch, $data->period, '00:' . $data->time, $data->evt,
		$data->player, $data->number, $data->team, $data->cause, $idLigne[1]
	));
	$myBdd->CheckCardCumulation($data->player, $idMatch, $data->evt, $data->cause);
	echo json_encode(['id' => $idLigne[1]]);
} elseif ($action == 'delete') {
	$sql = "DELETE FROM kp_match_detail 
		WHERE Id = ? ";
	$result = $myBdd->pdo->prepare($sql);
	$result->execute(array($idLigne[1]));
	echo json_encode(['id' => $idLigne[1]]);
}
