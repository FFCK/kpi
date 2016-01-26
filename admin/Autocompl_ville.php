<?php
//include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');
	
	session_start();

	$myBdd = new MyBdd();
	
	// Chargement
		$q = utyGetGet('q');
		$q = preg_replace('`^[0]*`','',$q);
		$resultGlobal = '';
		
			// Referentiel Villes
			$sql  = "Select ville_nom, ville_nom_reel, ville_departement, ville_code_postal ";
			$sql .= "From villes_france_free ";
			$sql .= "Where UPPER(ville_nom) LIKE UPPER('%".$q."%') ";
			$sql .= "or ville_code_postal LIKE UPPER('".$q."%') ";
			$sql .= "Order By ville_departement, ville_code_postal, ville_nom_reel ";	 
			$result = $myBdd->Query($sql);
			while ($row = $myBdd->FetchAssoc($result))
			{
				//$libelle = __encode($row['Libelle']);
				$resultGlobal .= $row['ville_code_postal']." ".$row['ville_nom_reel']."|".$row['ville_nom_reel']."|".$row['ville_departement']."\n";
			}
		//echo $resultGlobal;
	echo $resultGlobal;


?>