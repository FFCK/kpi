<?php
//include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');
	
session_start();

$myBdd = new MyBdd();

// Chargement
$q = $myBdd->RealEscapeString(trim(utyGetGet('q')));
$q = preg_replace('`^[0]*`','',$q);
$resultGlobal = '';

// Referentiel Villes
$sql  = "SELECT ville_nom, ville_nom_reel, ville_departement, ville_code_postal "
		. "FROM villes_france_free "
		. "WHERE UPPER(ville_nom) LIKE UPPER('%".$q."%') "
		. "OR ville_code_postal LIKE UPPER('".$q."%') "
		. "ORDER BY ville_departement, ville_code_postal, ville_nom_reel ";	 
$result = $myBdd->Query($sql);
while ($row = $myBdd->FetchAssoc($result))
{
	//$libelle = __encode($row['Libelle']);
	$resultGlobal .= $row['ville_code_postal']." ".$row['ville_nom_reel']."|".$row['ville_nom_reel']."|".$row['ville_departement']."\n";
}

echo $resultGlobal;
