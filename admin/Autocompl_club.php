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
		
			// Clubs
			$sql  = "SELECT * ";
			$sql .= "FROM gickp_Club ";
			$sql .= "WHERE (UPPER(Libelle) LIKE UPPER('%".$q."%') ";
			$sql .= "OR UPPER(Code) LIKE UPPER('".$q."%')) ";
			$sql .= "ORDER BY Code ";	 
			$result = $myBdd->Query($sql);
			while ($row = $myBdd->FetchAssoc($result))
			{
				//$libelle = __encode($row['Libelle']);
				$resultGlobal .= $row['Code']." - ".$row['Libelle']."|".$row['Libelle']."\n";
			}
		//echo $resultGlobal;
	echo $resultGlobal;
