<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

session_start();
$myBdd = new MyBdd();
$tableName = $myBdd->RealEscapeString(trim(utyGetGet('AjTableName')));
$where = $myBdd->RealEscapeString(trim(utyGetGet('AjWhere')));
$and = $myBdd->RealEscapeString(trim(utyGetGet('AjAnd', '')));
$typeValeur = $myBdd->RealEscapeString(trim(utyGetGet('AjTypeValeur')));
$valeur = $myBdd->RealEscapeString(trim(utyGetGet('AjValeur')));
$key = $myBdd->RealEscapeString(trim(utyGetGet('AjId')));
$key2 = $myBdd->RealEscapeString(trim(utyGetGet('AjId2', '')));
$ok = $myBdd->RealEscapeString(trim(utyGetGet('AjOk')));
$user = $myBdd->RealEscapeString(trim(utyGetGet('AjUser')));
if($and != '' && $key2 != '')
	$andText = $and."'".$key2."'";
else
	$andText = '';

if($ok == 'OK' && $tableName != '' && $where != '' && $typeValeur != '' && $key != '')
{
		$sql  = "UPDATE $tableName SET $typeValeur = '$valeur' $where '$key' ";
		if($and != '' && $key2 != '')
			$sql .= $and.$key2." ";
		
		mysql_query($sql, $myBdd->m_link) or die ("Erreur ".$sql);
		$myBdd->utyJournal('Modification '.$tableName, utyGetSaison(), '', 'NULL', 'NULL', 'NULL', $key.'-'.$typeValeur.'->'.$valeur, $user);
		echo 'OK!';
}
else echo 'Erreur : '.$tableName.'-where:'.$where.'-and:'.$and.'-type:'.$typeValeur.'-val:'.$valeur.'-key:'.$key.'-key2:'.$key2;
		
?>
