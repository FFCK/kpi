<?php

include_once($_SERVER['DOCUMENT_ROOT'].'/commun/MyConfig.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/commun/MyBdd.php');
session_start();

$user = $_SESSION['User'];

$lstEvenement = '-1';
if (utyGetGet('lst', false))
	$lstEvenement = utyGetGet('lst', false);

// Connexion BDD
$myBdd = new MyBdd();

$sql = "SELECT * 
	FROM gickp_Utilisateur 
	WHERE code = ? ";
$result = $myBdd->pdo->prepare($sql);
$result->execute(array($user));
$num_results = $result->rowCount();

if ($num_results != 1) {
	echo 'KO : Incorrect Login !';
	return;
}
	
$row = $result->fetch();	 
$userEvenement = $row['Id_Evenement'];
$UserIdentite = $row["Identite"];
		
$arraySrc = explode(',', $lstEvenement);
$arrayUser = explode('|', $userEvenement);
		
for ($i=0;$i<count($arraySrc);$i++) {
	$bKo = true;
	for ($j=0;$j<count($arrayUser);$j++) {
		if ($arraySrc[$i] == $arrayUser[$j]) {
			$bKo = false;
			break;
		}
	}

	if ($bKo) {
		echo 'KO : Evenement incorrect !';
		return;
	}
}

$myBdd->EvtExport($user, $lstEvenement, 'Import', $UserIdentite, '');

echo 'OK';

