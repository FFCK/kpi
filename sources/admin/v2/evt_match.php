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

session_start();

$myBdd = new MyBdd();
$idMatch = (int) utyGetPost('idMatch', 0);
// M1-00:00-V-A-186002-5
// $data = trim(utyGetPost('ligne'));
$data = json_decode(html_entity_decode(utyGetPost('ligne')));
$type = trim(utyGetPost('type'));
$idLigne = trim(utyGetPost('idLigne'));
$idLigne = explode('_', $idLigne);
// $data->number == '' ? $data->number = null : true;
// var_dump($data);
// Contrôle autorisation journée
$myBdd->AutorisationMatch($idMatch);

if ($type == 'insert') {
	$inserted_id = gen_uuid();
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
} elseif ($type == 'update') {
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
	echo 'OK';
} elseif ($type == 'delete') {
	$sql = "DELETE FROM kp_match_detail 
		WHERE Id = ? ";
	$result = $myBdd->pdo->prepare($sql);
	$result->execute(array($idLigne[1]));
	echo 'OK';
}
