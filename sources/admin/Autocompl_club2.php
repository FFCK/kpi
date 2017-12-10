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
			$sql  = "SELECT c.Code, c.Libelle, c.Code_comite_dep, cd.Code_comite_reg "
                    . "FROM gickp_Club c, gickp_Comite_dep cd "
                    . "WHERE c.Code_comite_dep = cd.Code "
                    . "AND (UPPER(c.Libelle) LIKE UPPER('%".$q."%') "
                    . "OR UPPER(c.Code) LIKE UPPER('".$q."%')) "
                    . "ORDER BY c.Code ";	 
			$result = $myBdd->Query($sql);
			while ($row = $myBdd->FetchAssoc($result))
			{
				$resultGlobal .= $row['Code']." - ".$row['Libelle']."|".$row['Libelle']."|".$row['Code']."|".$row['Code_comite_dep']."|".$row['Code_comite_reg']."\n";
			}
		//echo $resultGlobal;
	echo $resultGlobal;
