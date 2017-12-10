<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/commun/MyBdd.php');

if (isset($_GET['json']))
{
	$json = $_GET['json'];
	
	$user = '';
	$pwd = '';
	if (isset($_GET['user'])) $user = $_GET['user'];
	if (isset($_GET['pwd'])) $pwd = $_GET['pwd'];

	$sql  = "Select Pwd From gickp_Utilisateur ";
	$sql .= "Where Code = '";
	$sql .= $user;
	$sql .= "' ";
	
	echo "PHP Insertion ...<br>";
		
	$myBdd = new MyBdd(true);	// Connexion sur le site Mirroir (poloweb5)

	$result = mysql_query($sql, $myBdd->m_link) or die ("Error SQL : ".$sql);
	if (mysql_num_rows($result) == 1)
	{
		$row = mysql_fetch_array($result);	  
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
						$sql .= mysql_real_escape_string($recEvenement[1]);
						$sql .= "','";
						$sql .= mysql_real_escape_string($recEvenement[2]);
						$sql .= "','";
						$sql .= $recEvenement[3];
						$sql .= "','";
						$sql .= $recEvenement[4];
						$sql .= "')";
						
						$result = mysql_query($sql, $myBdd->m_link) or die ("Error SQL : ".$sql);
					}
				}
				
				// Evenement_Journees ...
				$jsonEvenementJournees = $jsonArray['Evenement'];
//				echo 'size jsonEvenement = '.count($jsonEvenement).' !';
				$nbEvenement = $jsonEvenement['count'];
//				echo 'count jsonEvenement = '.$nbEvenement.' !';
				$rowsEvenement =  $jsonEvenement['rows'];
				
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
?>