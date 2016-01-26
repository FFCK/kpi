<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

session_start();

$tableName = utyGetGet('AjTableName');
$where = utyGetGet('AjWhere');
$and = utyGetGet('AjAnd', '');
$typeValeur = utyGetGet('AjTypeValeur');
$valeur = utyGetGet('AjValeur');
$key = utyGetGet('AjId');
$key2 = utyGetGet('AjId2', '');
$ok = utyGetGet('AjOk');
$user = utyGetGet('AjUser');
if($and != '' && $key2 != '')
	$andText = $and."'".$key2."'";
else
	$andText = '';

if($ok == 'OK' && $tableName != '' && $where != '' && $typeValeur != '' && $key != '')
{
		$sql  = "UPDATE $tableName SET $typeValeur = '$valeur' $where '$key' ";
		if($and != '' && $key2 != '')
			$sql .= $and.$key2." ";
		
		$myBdd = new MyBdd();
		mysql_query($sql, $myBdd->m_link) or die ("Erreur ".$sql);
		$myBdd->utyJournal('Modification '.$tableName, utyGetSaison(), '', 'NULL', 'NULL', 'NULL', $key.'-'.$typeValeur.'->'.$valeur, $user);
		echo 'OK!';
}
else echo 'Erreur : '.$tableName.'-where:'.$where.'-and:'.$and.'-type:'.$typeValeur.'-val:'.$valeur.'-key:'.$key.'-key2:'.$key2;
		
?>
