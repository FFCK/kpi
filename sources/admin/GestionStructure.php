<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion des Equipes

class GestionStructure extends MyPageSecure	 
{	
	function Load()
	{
		$myBdd = new MyBdd();

		$codeCompet = utyGetSession('codeCompet');
		$_SESSION['codeCompet'] = $codeCompet;
		if ($codeCompet == '*')  
			$codeCompet = '';
		$this->m_tpl->assign('codeCompet', $codeCompet);
		
		// Chargement des Comites Régionnaux ...
		$arrayComiteReg = array();

		$sql  = "Select Code, Libelle ";
		$sql .= "From gickp_Comite_reg ";
		$sql .= "Order By Code ";	 
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load CR");

		$num_results = mysql_num_rows($result);
	
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);	  
			array_push($arrayComiteReg, array('Code' => $row["Code"], 'Libelle' => $row["Code"]." - ".$row["Libelle"], 'Selected' => '' ) );
		}
		
		$this->m_tpl->assign('arrayComiteReg', $arrayComiteReg);
		
		// Chargement des Comites Departementaux ...
		$arrayComiteDep = array();

		$sql  = "Select Code, Libelle ";
		$sql .= "From gickp_Comite_dep ";
		$sql .= "Order By Code ";	 
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load CD");
		$num_results = mysql_num_rows($result);

		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);	
			array_push($arrayComiteDep, array('Code' => $row['Code'], 'Libelle' => $row['Code']." - ".$row['Libelle'], 'Selected' => '' ) );
		}
			
		$this->m_tpl->assign('arrayComiteDep', $arrayComiteDep);
		
		// Chargement des Clubs ayant une équipe inscrite dans une compétition de polo ...
		$arrayClub = array();

		$sql  = "Select distinct c.Code, c.Libelle, c.Coord, c.Postal, c.Coord2, c.www, c.email ";
		$sql .= "From gickp_Club c, gickp_Equipe e ";
		$sql .= "Where c.Code = e.Code_club ";	 
		$sql .= "Order By c.Code ";	 
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load Club ".$sql);
		$num_results = mysql_num_rows($result);
		
		$mapParam2 = '';
		
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);	
			array_push($arrayClub, array('Code' => $row['Code'], 
                'Libelle' => $row['Code'].' - '.$row['Libelle'], 
                'Selected' => '', 
                'Coord2' =>  $row['Coord2'], 
                'Postal' =>  $row['Postal'], 
                'Coord' =>  $row['Coord'],
                    ));
			if($row['Coord'] != "")
			{
				$html = htmlspecialchars(addslashes($row['Libelle']));
				$code = $row['Code'];
                $coord = $row['Coord'];
				$postal = $row['Postal'];
				$www = $row['www'];
				$email = $row['email'];
                $mapParam2  .= "\n	
					var contentString = '<p id=\"infoWindowContent\" data-html=\"$html\" data-code=\"$code\" >$html</p>';
					var marker = new google.maps.Marker({ 
						position: new google.maps.LatLng($coord),
						map: carte,
						title: '$html',
						icon: image,
					});
                    markers['$code'] = marker;
                    coord['$code'] = \"$coord\";
                    postal['$code'] = \"$postal\";
                    www['$code'] = \"$www\";
                    email['$code'] = \"$email\";
                    
                    google.maps.event.addListener(marker,'click', (function(marker, contentString, infowindow){ 
                        return function() {
                            infowindow.setContent(contentString);
                            infowindow.open(carte, marker);
                            jq('#club').val(jq('#infoWindowContent').attr('data-code'));
                            handleSelected(false);
                        };
                    })(marker,contentString,infowindow));

				";
//                $html = htmlspecialchars(addslashes($row['Libelle']));
//				$label = $row['Code'];
//				$post = $row['Postal'];
//				$web = $row['www'];
//				$mail = $row['email'];
//				if (file_exists('img/logo/club'.$row['Code'].'.jpg'))
//					$logo = $row['Code'];
//				else
//					$logo = 0;
//				$mapParam2  .= "\n					var point = new GLatLng(".$row['Coord'].");";
//				$mapParam2  .= "\n					var point2 = new GLatLng(".$row['Coord2'].");";
//				$mapParam2  .= "\n					var marker = createMarker2(point,'$label','$html','$web','$mail',point2,'$post','$logo');";
//				$mapParam2  .= "\n					map.addOverlay(marker);";
			}
		}
		$this->m_tpl->assign('arrayClub', $arrayClub);
        //Chargement paramètres carte ...
		$mapParam  = "image = {url: '../img/Map-Marker-Ball-Right-Azure-icon.png'};\n";
        $mapParam .= "infowindow = new google.maps.InfoWindow({});\n";
        $mapParam .= "markers = [];";
        $mapParam .= $mapParam2;
		$this->m_tpl->assign('mapParam', $mapParam);
        
		// Chargement des Clubs internationaux...
		$arrayClubInt = array();

		$sql  = "Select distinct c.Code, c.Libelle ";
		$sql .= "From gickp_Club c, gickp_Comite_dep cd ";
		$sql .= "Where c.Code_comite_dep = cd.Code ";	 
		$sql .= "And cd.Code_comite_reg = '98' ";	 
		$sql .= "Order By c.Code_comite_dep, c.Code ";	 
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load Club 2 : ".$sql);
		$num_results = mysql_num_rows($result);
		
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);	
			array_push($arrayClubInt, array('Code' => $row['Code'], 'Libelle' => $row['Code'].' - '.$row['Libelle']) );
		}
		
		$this->m_tpl->assign('arrayClubInt', $arrayClubInt);

		//Chargement paramètres carte ...
		
//		$mapParam  = "map.setCenter(new GLatLng(46.85, 1.75), 5);";
//        $mapParam .= $mapParam2;

	
//		$this->m_tpl->assign('mapParam', $mapParam);
	}
		
	function AddCD()
	{
		$comiteReg = utyGetPost('comiteReg');
		$codeCD = utyGetPost('codeCD');
		$libelleCD = utyGetPost('libelleCD');

		$myBdd = new MyBdd();
			
				$sql  = "Insert Into gickp_Comite_dep (Code, Libelle, Code_comite_reg) Values ('";
				$sql .= $codeCD;
				$sql .= "','";
				$sql .= $libelleCD;
				$sql .= "','";
				$sql .= $comiteReg;
				$sql .= "') ";
				mysql_query($sql, $myBdd->m_link) or die ("Erreur insert 1");
			
		$myBdd->utyJournal('Ajout CD', '', '', 'NULL', 'NULL', 'NULL', $codeCD);
	}
	
	function AddClub()
	{
		$comiteDep = utyGetPost('comiteDep');
		$codeClub = utyGetPost('codeClub');
		$libelleClub = utyGetPost('libelleClub');
		$coord2 = utyGetPost('coord2');
		$postal2 = utyGetPost('postal2');
		$www2 = utyGetPost('www2');
		$email2 = utyGetPost('email2');
		$libelleEquipe2 = utyGetPost('libelleEquipe2');
		$affectEquipe = utyGetPost('affectEquipe');

		$myBdd = new MyBdd();
			
		$sql  = "Insert Into gickp_Club (Code, Libelle, Code_comite_dep, Coord, Postal, www, email) Values ('";
		$sql .= $codeClub;
		$sql .= "','";
		$sql .= $libelleClub;
		$sql .= "','";
		$sql .= $comiteDep;
		$sql .= "','";
		$sql .= $coord2;
		$sql .= "','";
		$sql .= $postal2;
		$sql .= "','";
		$sql .= $www2;
		$sql .= "','";
		$sql .= $email2;
		$sql .= "') ";
		mysql_query($sql, $myBdd->m_link) or die ("Erreur insert 1");
		
		$myBdd->utyJournal('Ajout Club', '', '', 'NULL', 'NULL', 'NULL', $codeClub);
		
		if($libelleEquipe2 != '')
		{
			$sql  = "Insert Into gickp_Equipe (Code_club, Libelle) Values ('".$codeClub."', '".$libelleEquipe2."')";
			mysql_query($sql, $myBdd->m_link) or die ("Erreur insert 2");
			$selectValue = mysql_insert_id();
			$myBdd->utyJournal('Ajout Equipe', '', '', 'NULL', 'NULL', 'NULL', $libelleEquipe2);
			
			if($affectEquipe != '')
			{
				//echo $selectValue.' !';
				if ((int) $selectValue == 0)
					return;
				$sql  = "Insert Into gickp_Competitions_Equipes (Code_compet, Code_saison, Libelle, Code_club, Numero) Select '";
				$sql .= $affectEquipe;
				$sql .= "','";
				$sql .= utyGetSaison();
				$sql .= "', Libelle, Code_club, Numero ";
				$sql .= "From gickp_Equipe Where Numero = $selectValue";
				
				mysql_query($sql, $myBdd->m_link) or die ("<br>Erreur insert 2<br>".$sql);
			}
		}
			
	}
	
	function UpdateClub()
	{
		$club = utyGetPost('club');
		$coord = utyGetPost('coord');
		$www = utyGetPost('www');
		$email = utyGetPost('email');
		$coord2 = utyGetPost('coord2');
		$postal = utyGetPost('postal');

		$myBdd = new MyBdd();
			
		$sql  = "Update gickp_Club set Coord = '";
		$sql .= $coord;
		$sql .= "', Coord2 = '";
		$sql .= $coord2;
		$sql .= "', Postal = '";
		$sql .= mysql_real_escape_string($postal);
		$sql .= "', www = '";
		$sql .= $www;
		$sql .= "', email = '";
		$sql .= $email;
		$sql .= "' ";
		$sql .= "where Code = '";
		$sql .= $club;
		$sql .= "' ";
		mysql_query($sql, $myBdd->m_link) or die ("Erreur insert 1 : ".$sql);
			
		$myBdd->utyJournal('Modification Club', '', '', 'NULL', 'NULL', 'NULL', $club);
	}

	function GestionStructure()
	{			
	  MyPageSecure::MyPageSecure(10);
		
		$alertMessage = '';
		
		$Cmd = '';
		if (isset($_POST['Cmd']))
			$Cmd = $_POST['Cmd'];

		if (strlen($Cmd) > 0)
		{
			if ($Cmd == 'AddCD')
				($_SESSION['Profile'] <= 2) ? $this->AddCD() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'AddClub')
				($_SESSION['Profile'] <= 2) ? $this->AddClub() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'UpdateClub')
				($_SESSION['Profile'] <= 3 or $_SESSION['User'] == '229824' or $_SESSION['User'] == '115989') ? $this->UpdateClub() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($alertMessage == '')
			{
				header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);	
				exit;
			}
		}

		$this->SetTemplate("Gestion_des_structures", "Clubs", false);
		$this->Load();
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplateMap('GestionStructure');
	}
}		  	

$page = new GestionStructure();
