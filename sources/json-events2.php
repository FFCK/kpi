<?php
include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

		$myBdd = new MyBdd();

		$sql  = "SELECT j.*, c.* ";
		$sql .= "From gickp_Journees j, gickp_Competitions c ";
		$sql .= "Where j.Code_competition = c.Code ";	 
		$sql .= "And j.Code_saison = c.Code_saison ";	 
		$sql .= "And c.Publication = 'O' ";	 
		$sql .= "And j.Publication = 'O' ";
		//groupe les journées à date identique (format coupe)
		$sql .= "group by j.Code_saison, j.Code_competition, j.Date_debut, j.Date_fin, j.Lieu ";	 
		$sql .= "order by j.Date_debut, c.Code_niveau, c.GroupOrder, c.Code_tour, j.Nom ";	 

		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load => ".$sql);
		$num_results = mysql_num_rows($result);
	
		$arrayCalendrier = array();
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);
			
			$title = utf8_encode(html_entity_decode($row['Nom']).' ('.html_entity_decode($row['Lieu']).'-'.$row['Departement'].')');
			$compet = utf8_encode($row['Code_competition']);
			//Couleurs selon le type de compétition (championnat, coupe, tournoi, compétition internationale, régionale)
				$class = utf8_encode($compet[0].'class '.$compet[0].$compet[1].'class');
			//Si c'est un mode championnat, on dirige vers la journée demandée, sinon toute la compétition
				($row['Code_typeclt'] != 'CP') ? ($typ = '&J='.$row['Id']) : $typ = '';
			//Si la compétition est passée, on dirige vers le classement, sinon les matchs
				$datedebut = explode("-", $row['Date_debut']);
	    		$datedebut = $datedebut[0].$datedebut[1].$datedebut[2];
	        	$datejour = date('Ymd');
				if($datedebut < $datejour)
//					$url = utf8_encode('https://kayak-polo.info/Classement.php?Compet='.$row['Code_competition'].'&Saison='.$row['Code_saison'].'&Dat='.$row['Date_debut'].$typ);
					$url = utf8_encode('https://kayak-polo.info/Classements.php?Compet='.$row['Code_competition'].'&Group='.$row['Code_ref'].'&Saison='.$row['Code_saison'].'&Dat='.$row['Date_debut'].$typ);
				else
					$url = utf8_encode('https://kayak-polo.info/Journee.php?Compet='.$row['Code_competition'].'&Group='.$row['Code_ref'].'&Saison='.$row['Code_saison'].$typ);
				
			array_push($arrayCalendrier, array(	'id' => $row['Id'],
												'title' => $title,
												'start' => $row['Date_debut'],
												'end' => $row['Date_fin'],
												'url' => $url,
												'className' => $class
											));
		}

	echo json_encode($arrayCalendrier);
	//echo json_encode(mb_convert_encoding($arrayCalendrier, "UTF-8", "ISO-8859-1"));
	

