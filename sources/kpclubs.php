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
		
		$clubId = utyGetSession('clubId', '');
        $clubId = utyGetPost('clubId',$clubId);
        $clubId = utyGetGet('clubId',$clubId);
		$_SESSION['clubId'] = $clubId;
        $this->m_tpl->assign('clubId', $clubId);

//		// Chargement des Clubs ayant une équipe inscrite dans une compétition de polo ...
//		$arrayClub = array();
//		$sql  = "Select distinct c.Code, c.Libelle, c.Coord "
//                ."From gickp_Club c "
//                ."Order By c.Officiel Desc, c.Code, c.Libelle ";
//		$mapParam2 = '';
//        $result = $myBdd->Query($sql);
//        while ($row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC)){ 
//			if($row['Coord'] != "")
//			{
//				$html = htmlspecialchars(addslashes($row['Libelle']));
//				$code = $row['Code'];
//                $coord = $row['Coord'];
//				$mapParam2  .= "\n	
//					var contentString = '<p id=\"infoWindowContent\" data-html=\"$html\" data-code=\"$code\" >$html</p>';
//					var marker = new google.maps.Marker({ 
//						position: new google.maps.LatLng($coord),
//						map: carte,
//						title: '$html',
//						icon: image,
//					});
//                    markers['$code']=marker;
//					bindInfoWindow(marker, carte, infoWindow, contentString);
//				";
//			}
//		}
//		$this->m_tpl->assign('arrayClub', $arrayClub);
//
//		//Chargement paramètres carte ...
//		$mapParam  = "var image = {url: 'img/Map-Marker-Ball-Right-Azure-icon.png'};\n";
//		$mapParam .= "markers = [];";
//        $mapParam .= $mapParam2;
//		$this->m_tpl->assign('mapParam', $mapParam);
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
		$this->DisplayTemplateLeaflet('kpclubs');
	}
}		  	

$page = new Clubs();
