<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

// Clubs

class Clubs extends MyPage	 
{	
	function Load()
	{
		$myBdd = new MyBdd();
		
		// Chargement des Clubs ayant une équipe inscrite dans une compétition de polo ...
		$arrayClub = array();

		$sql  = "Select distinct c.Code, c.Libelle, c.Coord, c.Postal, c.Coord2, c.www, c.email ";
		$sql .= "From gickp_Club c, gickp_Equipe e ";
		$sql .= "Where c.Code = e.Code_club ";
		$sql .= "Order By c.Officiel Desc, c.Code, c.Libelle ";
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load Club ".$sql);
		$num_results = mysql_num_rows($result);
		
		$mapParam2 = '';
		
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);	
			array_push($arrayClub, array('Code' => $row['Code'], 'Libelle' => $row['Code'].' - '.$row['Libelle'], 'Selected' => '', 'Coord2' =>  $row['Coord2'], 'Postal' =>  $row['Postal'], 'Coord' =>  $row['Coord']) );
			if($row['Coord'] != "")
			{
				$html = htmlspecialchars(addslashes($row['Libelle']));
				$code = $row['Code'];
				$post = $row['Postal'];
				$www = $row['www'];
				$mail = $row['email'];
				if (file_exists('img/KIP/CouleursClub'.$code.'.png'))
					$logo = 'img/KIP/CouleursClub'.$code.'.png';
				else
					$logo = 'img/KIP/CouleursClub0.png';
				$coord = $row['Coord'];
				$mapParam2  .= "\n	
					var contentString = '<div id=\"infoWindowContent\" data-html=\"".$html."\" data-code=\"".$code."\" data-www=\"".$www."\" data-post=\"".$post."\" data-coord=\"".$coord."\">'+
							'<h3>".$html."</h3>' +	
							'<img id=\"lettrineImage\" width=\"120\" src=\"".$logo."\" title=\"Couleurs du club\" />' +
							'<p>".$post."</p>' +
							'<p><a href=\"".$www."\" target=\"_blank\">".$www."</a></p>' +
							'</div>';
					var marker = new google.maps.Marker({ 
						position: new google.maps.LatLng($coord),
						map: carte,
						title: '$html',
						icon: 'http://maps.google.com/mapfiles/marker_green.png', //image,
					});
					bindInfoWindow(marker, carte, infoWindow, contentString);
				";
			}
		}
		
		$this->m_tpl->assign('arrayClub', $arrayClub);
		

		//Chargement paramètres carte ...
		
		$mapParam  = "var image = 'img/ffck_mappoint2.png';";
        $mapParam .= $mapParam2;

	
		$this->m_tpl->assign('mapParam', $mapParam);
	}
		

	function Clubs()
	{			
	  MyPage::MyPage();
		
		$alertMessage = '';
		
		$Cmd = '';
		if (isset($_POST['Cmd']))
			$Cmd = $_POST['Cmd'];

		if (strlen($Cmd) > 0)
		{
			if ($alertMessage == '')
			{
				header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);	
				exit;
			}
		}

		$this->SetTemplate("Clubs", "Clubs", true);
		$this->Load();
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplateMap2('Clubs');
	}
}		  	

$page = new Clubs();

?>
