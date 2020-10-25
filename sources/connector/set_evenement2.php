<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/commun/MyBdd.php');

if (utyGetGet('json', false)) {
	$json = utyGetGet('json', false);
	
	$user = '';
	$pwd = '';
	if (utyGetGet('user', false)) $user = utyGetGet('user', false);
	if (utyGetGet('pwd', false)) $user = utyGetGet('pwd', false);

	$sql  = "SELECT Pwd 
		FROM gickp_Utilisateur 
		WHERE Code = '" . $user . "' ";
		
	$myBdd = new MyBdd(true);	// Connexion sur le site Mirroir (poloweb5)

	$result = $myBdd->Query($sql);
	if ($myBdd->NumRows($result) == 1)
	{
		$row = $myBdd->FetchArray($result);  
		if ( ($row["Pwd"] === md5($pwd)) )
		{
			// Ici on peut intégrer les données ...
			if ($myBdd->m_database == 'poloweb5')
			{
				$json = str_replace("\\\"", "\"", $json);
				
				$jsonArray = json_decode($json, true);
				
				echo 'size jsonArray = '.count($jsonArray).' !';
	
				// Evenement ...
				$jsonEvenement = $jsonArray['Evenement'];
//				echo 'size jsonEvenement = '.count($jsonEvenement).' !';
				$nbEvenement = $jsonEvenement['count'];
//				echo 'count jsonEvenement = '.$nbEvenement.' !';
				$rowsEvenement =  $jsonEvenement['rows'];
//				echo 'size rowsEvenement = '.count($rowsEvenement).' !';

				for ($i=0;$i<$nbEvenement;$i++)
				{
					$recEvenement = $rowsEvenement[$i];
//					echo 'count recEvenement = '.count($recEvenement). '!';
					if (count($recEvenement) == 5)
					{
						$sql  = "Replace Into gickp_Evenement (Id, Libelle, Lieu, Date_debut, Date_fin) ";
						$sql .= "Values (";
						$sql .= $recEvenement[0];
						$sql .= ",'";
						$sql .= $myBdd->RealEscapeString($recEvenement[1]);
						$sql .= "','";
						$sql .= $myBdd->RealEscapeString($recEvenement[2]);
						$sql .= "','";
						$sql .= $recEvenement[3];
						$sql .= "','";
						$sql .= $recEvenement[4];
						$sql .= "')";
						
						$myBdd->Query($sql);
					}
				}
				
				// Evenement_Journees ...

				
				echo '<h2>Succes Integration des donnees sur '.$myBdd->m_database.' ... </h2><div>'.$json.'</div>';
				return;
			}
		}
	}
	
	echo 'Error : User et Pwd incorrect ! ';
}
else
{
	echo 'Error Integration ...';
}

