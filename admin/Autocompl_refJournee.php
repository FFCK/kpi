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
		
			// Referentiel Journees
			$sql  = "Select * ";
			$sql .= "From gickp_Ref_Journee ";
			$sql .= "Where UPPER(nom) LIKE UPPER('%".$q."%') ";
			$sql .= "Order By nom ";	 
			$result = $myBdd->Query($sql);
			while ($row = $myBdd->FetchAssoc($result))
			{
				//$libelle = __encode($row['Libelle']);
				$resultGlobal .= $row['nom']."|".$row['nom']."\n";
			}
		//echo $resultGlobal;
	echo $resultGlobal;


?>