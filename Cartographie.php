<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

// Gestion des Equipes

class Cartographie extends MyPage	 
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
				$label = $row['Code'];
				$post = $row['Postal'];
				$web = $row['www'];
				$mail = $row['email'];
				if (file_exists('img/logo/club'.$row['Code'].'.jpg'))
					$logo = $row['Code'];
				else
					$logo = 0;
				$mapParam2  .= "\n					var point = new GLatLng(".$row['Coord'].");";
				$mapParam2  .= "\n					var point2 = new GLatLng(".$row['Coord2'].");";
				$mapParam2  .= "\n					var marker = createMarker2(point,'$label','$html','$web','$mail',point2,'$post','$logo');";
				$mapParam2  .= "\n					map.addOverlay(marker);";
			}
		}
		
		$this->m_tpl->assign('arrayClub', $arrayClub);
		

		//Chargement paramètres carte ...
		
		$mapParam  = "map.setCenter(new GLatLng(46.85, 1.73), 6);";
        $mapParam .= $mapParam2;

	
		$this->m_tpl->assign('mapParam', $mapParam);
	}
		

	function Cartographie()
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

		$this->SetTemplate("Cartographie", "Clubs", true);
		$this->Load();
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplateMap('Cartographie');
	}
}		  	

$page = new Cartographie();

?>
