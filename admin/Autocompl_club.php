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
		
			// Clubs
			$sql  = "Select * ";
			$sql .= "From gickp_Club ";
			$sql .= "Where (UPPER(Libelle) LIKE UPPER('%".$q."%') ";
			$sql .= "Or UPPER(Code) LIKE UPPER('".$q."%')) ";
			$sql .= "Order By Code ";	 
			$result = $myBdd->Query($sql);
			while ($row = $myBdd->FetchAssoc($result))
			{
				//$libelle = __encode($row['Libelle']);
				$resultGlobal .= $row['Code']." - ".$row['Libelle']."|".$row['Libelle']."\n";
			}
		//echo $resultGlobal;
	echo $resultGlobal;


?>