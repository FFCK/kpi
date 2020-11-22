<?php
// prevent direct access *****************************************************
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if(!$isAjax) {
  $user_error = 'Access denied - not an AJAX request...';
  trigger_error($user_error, E_USER_ERROR);
}
// ***************************************************************************
session_start();
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

$myBdd = new MyBdd();
$codeSaison = $myBdd->GetActiveSaison();
$user = trim(utyGetGet('AjUser'));

$tableName = trim(utyGetGet('AjTableName'));
$where = trim(utyGetGet('AjWhere'));
$and = trim(utyGetGet('AjAnd', ''));
$typeValeur = trim(utyGetGet('AjTypeValeur'));
$valeur = trim(utyGetGet('AjValeur'));
$key = trim(utyGetGet('AjId'));
$key2 = trim(utyGetGet('AjId2', ''));
$ok = trim(utyGetGet('AjOk'));
if ($and != '' && $key2 != '') {
	$andText = $and."'".$key2."'";
} else {
	$andText = '';
}
if (!in_array($tableName, 
	['gickp_Journees', 'gickp_Competitions_Equipes', 'gickp_Competitions_Equipes_Init', 
	'gickp_Competitions_Equipes_Joueurs', 'gickp_Matchs', 'gickp_Matchs_Joueurs', 'gickp_Competitions_Equipes_Journee']
	)) {
		error_log("Erreur 400a : UPDATE $tableName SET $typeValeur = $valeur $where $key $andText", 0);
		die ('Error 400');
	}
if (!in_array($where, 
	['Where Id =', 'Where Matric =']
	)) {
		error_log("Erreur 400b : UPDATE $tableName SET $typeValeur = $valeur $where $key $andText", 0);
		die ('Error 400');
	}
if ($and != '' && !in_array($and, 
	['And Id_journee =', 'And Id_equipe =', 'And Id_match =']
	)) {
		error_log("Erreur 400c : UPDATE $tableName SET $typeValeur = $valeur $where $key $andText", 0);
		die ('Error 400');
	}

if (in_array($typeValeur, ['Date_debut', 'Date_fin', 'Date_match'])) {
	$valeur = utyDateFrToUs($valeur);
}

$sql = "UPDATE $tableName 
	SET $typeValeur = ? $where ? ";
if ($ok == 'OK' && $tableName != '' && $where != '' && $typeValeur != '' && $key != '') {
		$arrayQuery = array($valeur, $key);
		if ($and != '' && $key2 != '') {
			$sql .= $and."?";
			$arrayQuery = array_merge($arrayQuery, [$key2]);
		}
		$result = $myBdd->pdo->prepare($sql);
		$result->execute($arrayQuery);
		if ($result->rowCount() == 1) {
			$myBdd->utyJournal('Modification '.$tableName, $codeSaison, '', null, null, null, $key.'-'.$typeValeur.'->'.$valeur, $user);
			echo 'OK!';
		} else {
			trigger_error("Aucune ligne modifiée : " . $sql . ',' . $valeur . ',' . $key, E_USER_ERROR);
			echo 'Error 400';
		}
} else {
	trigger_error("Requete incorrecte : " . $sql, E_USER_ERROR);
	echo 'Error 400';
}

