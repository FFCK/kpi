<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion des Utilisateurs

class GestionUtilisateur extends MyPageSecure	 
{	
	function IsSelected($code, $arraySelection)
	{
     foreach($arraySelection as $selectValue)
     {
	     if ($selectValue == $code)
	     	return 'SELECTED';
   	 }
   	 return '';
	}

	function IsSaisonSelectedPost($code)
	{
		if (isset($_POST['comboSaison']) && !empty($_POST['comboSaison']))
		{
			return $this->IsSelected($code, $_POST['comboSaison']);
		}
		return '';
	}

	function IsCompetitionSelectedPost($code)
	{
		if (isset($_POST['comboCompetition']) && !empty($_POST['comboCompetition']))
		{
			return $this->IsSelected($code, $_POST['comboCompetition']);
		}
		return '';
	}
	
	function IsEvenementSelectedPost($code)
	{
		if (isset($_POST['comboEvenement']) && !empty($_POST['comboEvenement']))
		{
			return $this->IsSelected($code, $_POST['comboEvenement']);
		}
		return '';
	}
	
	function IsStringSelected($code, $string)
	{
		$key = '|'.$code.'|';
		if (strstr($string, $key) == FALSE)
			return '';
		return 'SELECTED';
	}

	function GetFiltreSaison()
	{
		if (isset($_POST['comboSaison']) && !empty($_POST['comboSaison']))
		{
			return $this->SetFiltreSaison($_POST['comboSaison']);
		}
		return '';
	}
	
	function SetFiltreSaison($arraySaison)
	{
		$lstSaison = '';	
		foreach($arraySaison as $selectValue)
		{
			if ($selectValue == '*')
				return '';
			if ($lstSaison != '')
				$lstSaison .= '|';
			$lstSaison .= $selectValue;
		}
		if ($lstSaison == '')
			return '';
		return '|'.$lstSaison.'|';
	}
	
	function GetFiltreCompetition()
	{
		if (isset($_POST['comboCompetition']) && !empty($_POST['comboCompetition']))
		{
			return $this->SetFiltreCompetition($_POST['comboCompetition']);
		}
		return '';
	}
	
	function SetFiltreCompetition($arrayCompetition)
	{
		$lstCompetition = '';	
		foreach($arrayCompetition as $selectValue)
		{
			if ($selectValue == '*')
				return '';
			if ($lstCompetition != '')
				$lstCompetition .= '|';
			$lstCompetition .= $selectValue;
		}
	 	if ($lstCompetition == '')
			return '';
		return '|'.$lstCompetition.'|';
	}
		
	function GetFiltreEvenement()
	{
		if (isset($_POST['comboEvenement']) && !empty($_POST['comboEvenement']))
		{
			return $this->SetFiltreEvenement($_POST['comboEvenement']);
		}
		return '';
	}
	
	function SetFiltreEvenement($arrayEvenement)
	{
		$lstEvenement = '';	
		foreach($arrayEvenement as $selectValue)
		{
			if ($selectValue == '*')
				return '';
			if ($lstEvenement != '')
				$lstEvenement .= '|';
			$lstEvenement .= $selectValue;
		}
	 	if ($lstEvenement == '')
			return '';
		return '|'.$lstEvenement.'|';
	}
		
	function Load($selectUser)
	{
		$user = $_SESSION['User'];
		$profile = $_SESSION['Profile'];
		$limitProfils = utyGetPost('limitProfils','%');
		$this->m_tpl->assign('limitProfils', $limitProfils);
		$limitSaisons = utyGetPost('limitSaisons','%');
		$this->m_tpl->assign('limitSaisons', $limitSaisons);
		$Saison = utyGetSaison();
		$this->m_tpl->assign('Saison', $Saison);
		
		$myBdd = new MyBdd();
		
		// Chargement des Utilisateurs ...
		$sql  = "Select u.* "; //, e.Libelle, e.Lieu ";
		$sql .= "From gickp_Utilisateur u "; // left outer join gickp_Evenement e on u.Id_Evenement = e.Id ";
		$sql .= "Where u.Niveau >= ";
		$sql .= $profile;
		$sql .= " And u.Niveau like '";
		$sql .= $limitProfils;
		$sql .= "' Order by u.Niveau Asc, u.Identite Asc, u.Fonction Asc ";

		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load : ".$sql);
		$num_results = mysql_num_rows($result);

		$typeFiltreCompetition = '2';
		$filtreSaison = '*';
		$filtreCompetition = '*';
		$filtreEvenement = '';
		
		$arrayUser = array();
		$emails = '';
		
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);	  

			$StdOrSelected = '';
			if ($selectUser == $row["Code"]) {
                $StdOrSelected = 'selected';
            }

            $filtreSaisons = explode('|',$row['Filtre_saison']);
			$filtreSaisons = array_slice ($filtreSaisons, 1);
			$filtreSaisons = implode(', ',$filtreSaisons);

			$filtreCompets = explode('|',$row['Filtre_competition']);
			$filtreCompets = array_slice ($filtreCompets, 1);
			$filtreCompets = implode(', ',$filtreCompets);
			
			$row["Date_debut"] = utyDateUsToFr($row["Date_debut"]);
			$row["Date_fin"] = utyDateUsToFr($row["Date_fin"]);
			
			if(mb_ereg('('.$limitSaisons.')',$filtreSaisons) || $limitSaisons == '%' || $filtreSaisons == '')
			{
				array_push($arrayUser, array('Code' => $row["Code"], 'Identite' => $row["Identite"], 'StdOrSelected' => $StdOrSelected,
													'filtreSaisons' => $filtreSaisons, 'Limitation_equipe_club' => $row['Limitation_equipe_club'],
													'filtreCompets' => $filtreCompets, 'Mail' => $row["Mail"], 'Tel' => $row["Tel"], 'Fonction' => $row["Fonction"], 'Niveau' => $row['Niveau'],
													'Id_Evenement' => $row["Id_Evenement"], 'Date_debut' => $row["Date_debut"], 'Date_fin' => $row["Date_fin"],
													// 'Libelle' => $row['Libelle'], 'Lieu' => $row['Lieu']
													));
				$emails .= $row["Mail"].',';
			}

			if ($selectUser == $row["Code"])
			{
					$this->m_tpl->assign('action', 'Update');
					
					$this->m_tpl->assign('guser', $selectUser);
					$this->m_tpl->assign('gidentite', $row['Identite']);
					$this->m_tpl->assign('gmail', $row['Mail']);
					$this->m_tpl->assign('gtel', $row['Tel']);
					$this->m_tpl->assign('gfonction', $row['Fonction']);
					$this->m_tpl->assign('gniveau', $row['Niveau']);

					$this->m_tpl->assign('limitclub', $row['Limitation_equipe_club']);
					$this->m_tpl->assign('filtre_journee', $row['Filtre_journee']);
					$this->m_tpl->assign('filtre_competition_special', $row['Filtre_competition_sql']);
					
					$typeFiltreCompetition = $row['Type_filtre_competition'];
					$filtreSaison = $row['Filtre_saison'];
					$filtreCompetition = $row['Filtre_competition'];
					$filtreEvenement = $row['Id_Evenement'];
					
					$this->m_tpl->assign('filtreEvenement', $row['Id_Evenement']);
					$this->m_tpl->assign('Date_debut', $row['Date_debut']);
					$this->m_tpl->assign('Date_fin', $row['Date_fin']);

			}																	 
		}
	
		$this->m_tpl->assign('arrayUser', $arrayUser);
		
		$emails .= 'laurent@poloweb.org';
		$this->m_tpl->assign('emails', $emails);

		if ($selectUser == '')
		{
			$this->m_tpl->assign('action', utyGetPost('Action'));
			
			$this->m_tpl->assign('guser', utyGetPost('user'));
			$this->m_tpl->assign('gidentite', utyGetPost('identite'));
			$this->m_tpl->assign('gmail', utyGetPost('mail'));
			$this->m_tpl->assign('gtel', utyGetPost('tel'));
			$this->m_tpl->assign('gfonction', utyGetPost('fonction'));
			$this->m_tpl->assign('gniveau', utyGetPost('niveau'));

			$this->m_tpl->assign('limitclub', utyGetPost('limitclub'));
			$this->m_tpl->assign('filtre_journee', utyGetPost('filtre_journee'));
			$this->m_tpl->assign('filtre_competition_special', utyGetPost('filtre_competition_special'));
						
			$typeFiltreCompetition = utyGetPost('filtre_competition', $typeFiltreCompetition);
		}
		
		// Type de Filtre Compétitions ...
		$this->m_tpl->assign('typeFiltreCompetition', $typeFiltreCompetition);
		
		// Chargement des Saisons ...
		$sql  = "Select Code ";
		$sql .= "From gickp_Saison ";
		$sql .= "Order By Code DESC ";
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load");
		$num_results = mysql_num_rows($result);
		
		$arraySaison = array();
		
		if ($selectUser == '')
			$select = $this->IsSaisonSelectedPost('*');
		else
			$select = $this->IsStringSelected('*', $filtreSaison);
			
		array_push($arraySaison, array('Code' => '*', 'Libelle' => '* - Toutes les Saisons', 'Selection' => $select));
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);	  
					
			if ($selectUser == '')
				$select = $this->IsSaisonSelectedPost($row["Code"]);
			else
				$select = $this->IsStringSelected($row["Code"], $filtreSaison);
				
			array_push($arraySaison, array('Code' => $row["Code"], 'Libelle' => $row['Code']. ' - Saison '.$row['Code'], 'Selection' => $select));
		}
		$this->m_tpl->assign('arraySaison', $arraySaison);
		
		// Chargement des Compétitions ...
		$sql  = "Select Distinct c.Code, c.Libelle, c.Code_niveau, g.id, g.section, g.ordre "
                . "From gickp_Competitions c, gickp_Competitions_Groupes g "
                . "WHERE c.Code_ref = g.Groupe "
                . "Group By c.Code "
                . "Order By g.section, g.ordre, COALESCE(c.Code_ref, 'z'), c.Code_tour, c.GroupOrder, c.Code ";
		
		$arrayCompetition = array();
		$result = $myBdd->Query($sql);
        
        $arrayCompetitionsSelected = explode('|', trim($filtreCompetition, '|'));
        if (in_array('*', $arrayCompetitionsSelected)) {
            $selected = 'selected';
        } else {
            $selected = '';
        }
        $arrayCompetition[0]['label'] = "Toutes les compétitions";
        $arrayCompetition[0]['options'][] = array('Code' => '*', 'Libelle' => 'Toutes les compétitions', 'selected' => $selected );
            
        $i = 0;
        $j = '';
        $label = $myBdd->getSections();
		while ($row = $myBdd->FetchArray($result)){ 
           if($j != $row['section']) {
                $i ++;
                $arrayCompetition[$i]['label'] = $label[$row['section']];
            }
            if(in_array($row["Code"], $arrayCompetitionsSelected)) {
                $row['selected'] = 'selected';
            } else {
                $row['selected'] = '';
            }
            $j = $row['section'];
            $arrayCompetition[$i]['options'][] = $row;
		
		}
		$this->m_tpl->assign('arrayCompetition', $arrayCompetition);
		
		// Chargement des évènements
		
		$sql  = "Select * ";
		$sql .= "From gickp_Evenement ";
		$sql .= "Order By Id Desc ";
		  
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load Evenements => ".$sql);
		$num_results = mysql_num_rows($result);
		
		$arrayEvenements = array();

		if ($selectUser == '')
			$select = $this->IsEvenementSelectedPost('*');
		else
			$select = $this->IsStringSelected('*', $filtreEvenement);
			
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);	  
		
			if ($selectUser == '')
				$select = $this->IsEvenementSelectedPost($row["Id"]);
			else
				$select = $this->IsStringSelected($row["Id"], $filtreEvenement);

			array_push($arrayEvenements, array('Id' => $row["Id"], 'Libelle' => $row['Libelle'], 'Lieu' => $row["Lieu"], 'Selection' => $select ));
		}
		$this->m_tpl->assign('arrayEvenements', $arrayEvenements);
		
		
		
	}
	
	function Replace($bNew)
	{
		$guser = utyGetPost('guser');
		$gpwd = utyGetPost('gpwd');
		$generepwd = utyGetPost('generepwd');
		if($generepwd == 'O')
		{
			$gpwd = Genere_Password(10);
		}
		$gidentite = utyGetPost('gidentite');
		$gmail = utyGetPost('gmail');
		$gtel = utyGetPost('gtel');
		$gfonction = utyGetPost('gfonction');
		
		$gniveau = utyGetPost('gniveau', 100);
		if ($gniveau < utyGetSession('Profile'))
			$gniveau = utyGetSession('Profile');

		$typeFiltreCompetition = utyGetPost('filtre_competition', "1");
		
		$filtreCompetitionSql = '';
		$filtreCompetition = '';
		$filtreSaison = '';
		
		$filtreEvenement = $this->GetFiltreEvenement(); // COSANDCO 
		
		if ($typeFiltreCompetition == "2") // Filtre Classique...
		{
			$filtreSaison = $this->GetFiltreSaison();
			$filtreCompetition = $this->GetFiltreCompetition();
			// $filtreEvenement = $this->GetFiltreEvenement();// COSANDCO 
			
			if (strlen($filtreSaison) > 0)
			{
				$txt = substr($filtreSaison, 1);
				$txt = substr($txt, 0, -1);
				$txt = str_replace("|","','", $txt);
				$filtreCompetitionSql .= " And a.Code_saison In ('".$txt."')";
			}
			
			if (strlen($filtreCompetition) > 0)
			{
				$txt = substr($filtreCompetition, 1);
				$txt = substr($txt, 0, -1);
				$txt = str_replace("|","','", $txt);
				$filtreCompetitionSql .= " And a.Code In ('".$txt."')";
		  }
		}
		elseif ($typeFiltreCompetition == "3")	 // Filtre Spécial
		{
			$filtreCompetitionSql = utyGetPost('filtre_competition_special');
		}
		
		$filtreJournee = utyGetPost('filtre_journee');
		$limitclub = utyGetPost('limitclub');
		
		$comboEvenement = utyGetPost('comboEvenement');
		$Date_debut = utyDateFrToUs(utyGetPost('Date_debut'));
		$Date_fin = utyDateFrToUs(utyGetPost('Date_fin'));
								
		$plusmail = utyGetPost('plusmail');
		$plusPJ = utyGetPost('plusPJ');
		$message_complementaire = utyGetPost('message_complementaire');
		if (strlen($guser) > 0)
		{
			$myBdd = new MyBdd();

			if ($bNew)
			{
				$sql  = "Insert Into gickp_Utilisateur (Code, Identite, Mail, Tel, Fonction, Niveau, Pwd, ";
				$sql .=	"Type_filtre_competition, Filtre_competition, Filtre_saison, Filtre_competition_sql,";
				$sql .=	"Filtre_journee, Limitation_equipe_club, Id_Evenement, Date_debut, Date_fin) Values ('";
				$sql .= mysql_real_escape_string($guser);
				$sql .= "','";
				$sql .= mysql_real_escape_string($gidentite);
				$sql .= "','";
				$sql .= mysql_real_escape_string($gmail);
				$sql .= "','";
				$sql .= mysql_real_escape_string($gtel);
				$sql .= "','";
				$sql .= mysql_real_escape_string($gfonction);
				$sql .= "',";
				$sql .= $gniveau;
				$sql .= ",";
				$sql .= "'".md5($gpwd)."',";
				$sql .= $typeFiltreCompetition;
				$sql .= ",'";
				$sql .= mysql_real_escape_string($filtreCompetition);
				$sql .= "','";
				$sql .= mysql_real_escape_string($filtreSaison);
				$sql .= "','";
				$sql .= mysql_real_escape_string($filtreCompetitionSql);
				$sql .= "','";
				$sql .= mysql_real_escape_string($filtreJournee);
				$sql .= "','";
				$sql .= $limitclub;
				$sql .= "','";
				$sql .= mysql_real_escape_string($filtreEvenement);
				$sql .= "','";
				$sql .= $Date_debut;
				$sql .= "','";
				$sql .= $Date_fin;
				$sql .= "')";
				$action = "Création ";
			}
			else
			{
				$sql  = "Update gickp_Utilisateur Set Mail = '";
				$sql .= mysql_real_escape_string($gmail);
				$sql .= "', Tel = '";
				$sql .= mysql_real_escape_string($gtel);
				$sql .= "', Fonction = '";
				$sql .= mysql_real_escape_string($gfonction);
				$sql .= "', Niveau = '";
				$sql .= $gniveau;
				$sql .= "', Identite = '";
				$sql .= mysql_real_escape_string($gidentite);
				$sql .= "', Type_filtre_competition = ";
				$sql .= $typeFiltreCompetition;
				$sql .= ", Filtre_competition = '";
				$sql .= mysql_real_escape_string($filtreCompetition);
				$sql .= "', Filtre_saison = '";
				$sql .= mysql_real_escape_string($filtreSaison);
				$sql .= "', Filtre_competition_sql = '";
				$sql .= mysql_real_escape_string($filtreCompetitionSql);
				$sql .= "', Filtre_journee = '";
				$sql .= mysql_real_escape_string($filtreJournee);
				$sql .= "', Limitation_equipe_club = '";
				$sql .= $limitclub;
				$sql .= "', Id_Evenement = '";
				$sql .= mysql_real_escape_string($filtreEvenement);
				$sql .= "', Date_debut = '";
				$sql .= $Date_debut;
				$sql .= "', Date_fin = '";
				$sql .= $Date_fin;
				$sql .= "'";
				if( $gpwd != '' )
				{
					$sql .= ", Pwd = '".md5($gpwd)."'";
				}
				$sql .= " Where Code = '";
				$sql .= $guser;
				$sql .= "' ";
				$action = "Modification ";
			}
			
			echo $sql."<br>";
			mysql_query($sql, $myBdd->m_link) or die ("Erreur Replace    ".$sql);

			$myBdd->utyJournal('Ajout Modif Utilisateur', '', '', 'NULL', 'NULL', 'NULL', $guser);
			
			 //MAIL 
			$sujet = $action.'de votre accès à kayak-polo.info (KPI)';
			$email_expediteur = 'laurent@poloweb.org';
			$email_reply = 'laurent@poloweb.org'; 
			$message_texte  = 'Bonjour '.$gidentite.','."\n\n".'Nous vous confirmons la '.$action.'de votre accès à www.kayak-polo.info'; 
			$message_texte .= "\n\n".'Votre identifiant : '.$guser; 
			$message_texte .= "\n".'Votre mot de passe ';
			if($gpwd != '')
				$message_texte .= ' : '.$gpwd;
			else
				$message_texte .= 'est inchangé';
			$message_texte .= "\n".'Vos fonctions : '.$gfonction; 
			$message_texte .= "\n\n".'Connectez-vous sur https://www.kayak-polo.info onglet Administration.'; 
			$message_texte .= "\n".'Lors de votre prochaine connexion, n\'oubliez pas de changer votre mot de passe en cliquant sur Mes Paramètres.'; 
			if($message_complementaire != '')
				$message_texte .= "\n\n".$message_complementaire;
			$message_texte .= "\n\n".'A bientôt.'; 
			$message_texte .= "\n\n".'Laurent,'; 
			$message_texte .= "\n".'Administrateur.'; 
			$message_texte .= "\n".'---------------------------------------'; 
			/*
			$message_html = '<html> 
			<head> 
				 <title>'.$action.'de votre accès à www.kayak-polo.info</title> 
			</head> 
				 <body>
					<p>Bonjour '.$gidentite.'</p>
					<p>Nous vous confirmons la '.$action.'de votre accès à <b>kayak-polo.info</b></p>
					<p>Votre identifiant : '.$guser.'<br>
					Votre mot de passe ';
			if($gpwd != '')
				$message_html .= ' :'.$gpwd;
			else
				$message_html .= 'est inchangé';
			$message_html .= '<br>
					Vos fonctions : '.$gfonction.'</p>
					<p>Connectez-vous sur https://www.kayak-polo.info onglet <b>Administration</b>.<br>
					Par mesure de sécurité, n\'oubliez pas de changer régulièrement votre mot de passe en cliquant sur <b>Mes Paramètres</b>.</p>
					<p>A bientôt.</p>
					<p>Laurent,<br>Administrateur</p>			 
				 </body> 
			</html>'; 
			*/
			//GENERE LA FRONTIERE DU MAIL ENTRE TEXTE ET HTML 
			$frontiere = '-----=' . md5(uniqid(mt_rand())); 
			//HEADERS DU MAIL 
			$headers  = 'From: "KPI " <'.$email_expediteur.'>'."\n"; 
			//$headers .= 'Bcc: '.$email_bcc."\n";
			$headers .= 'Return-Path: <'.$email_reply.'>'."\n"; 
			$headers .= 'MIME-Version: 1.0'."\n"; 
			$headers .= 'Content-Type: multipart/mixed; boundary="'.$frontiere.'"'; 
			//MESSAGE TEXTE 
			$message = 'This is a multi-part message in MIME format.'."\n\n"; 
			$message .= '--'.$frontiere."\n"; 
			$message .= 'Content-Type: text/plain; charset="UTF-8"'."\n"; 
			$message .= 'Content-Transfer-Encoding: 8bit'."\n\n"; 
			//$message .= $message_texte."\n\n"; 
			//MESSAGE HTML 
			$message2 = '--'.$frontiere."\n"; 
			$message2 .= 'Content-Type: text/html; charset="UTF-8"'."\n"; 
			$message2 .= 'Content-Transfer-Encoding: 8bit'."\n\n"; 
		//	$message2 .= $message_html."\n\n"; 
			$message2 .= '--'.$frontiere."\n"; 
			//PIECE JOINTE
			if($plusPJ == 'Manuel7.pdf')
			{
				$messagePJ = 'Content-Type: application/pdf; name="Manuel7.pdf"'."\n"; 
				$messagePJ .= 'Content-Transfer-Encoding: base64'."\n"; 
				$messagePJ .= 'Content-Disposition:attachement; filename="../Manuel7.pdf"'."\n\n"; 
				$messagePJ .= chunk_split(base64_encode(file_get_contents('../Manuel7.pdf')))."\n";
			}
			//ENVOI
			$messageComplet = $message.$message_texte."\n\n".$message2.$messagePJ;
			if($plusmail == 'O')
				mail($gmail,$sujet,$messageComplet,$headers);

			// MAIL ADMINISTRATEUR
			$sujet = $action.'accès KPI : '.$gidentite.' (par '.ucwords(strtolower(utyGetSession('userName'))).')';
			$message_texte  = 'Bonjour, '."\n\n".'Nous vous confirmons la '.$action.'d\'un accès à www.kayak-polo.info.'; 
			$message_texte .= "\n\n".'Identité : '.$gidentite; 
			$message_texte .= "\n".'Email : '.$gmail; 
			$message_texte .= "\n".'Tel : '.$gtel; 
			$message_texte .= "\n".'Identifiant : '.$guser; 
			$message_texte .= "\n".'Pwd : '.$gpwd; 
			$message_texte .= "\n".'Fonctions : '.$gfonction; 
			$message_texte .= "\n".'Profil : '.$gniveau; 
			$message_texte .= "\n".'Clubs : '.$limitclub; 
			$message_texte .= "\n".'Journées : '.$filtreJournee; 
			$message_texte .= "\n".'Evénements : '.implode(',',$comboEvenement); 
			$message_texte .= "\n".'Saisons : '.$filtreSaison; 
			$message_texte .= "\n".'Competitions : '.$filtreCompetition; 
			$message_texte .= "\n".'Message complémentaire : '.$message_complementaire; 
			$message_texte .= "\n".'Envoi email : '.$plusmail; 
			$message_texte .= "\n".'Envoi pièce jointe : '.$plusPJ; 
			$message_texte .= "\n\n".'Modification effectuée par : '.ucwords(strtolower(utyGetSession('userName'))); 
			$message_texte .= "\n\n".'A bientôt.'; 
			$message_texte .= "\n\n".'Laurent,'; 
			$message_texte .= "\n".'Administrateur.'; 
			$message_texte .= "\n".'---------------------------------------'; 
			$email_admin = 'lgarrigue@gmail.com';
			//ENVOI
			$messageComplet = $message.$message_texte;
			mail($email_admin,$sujet,$messageComplet,$headers);

		}
	}
	
	function Remove()
	{
		$ParamCmd = utyGetPost('ParamCmd');
			
		$arrayParam = split ('[,]', $ParamCmd);		
		if (count($arrayParam) == 0)
			return; // Rien à Detruire ...
			
		$myBdd = new MyBdd();
		$sql  = "Delete From gickp_Utilisateur Where Code In ('";

		for ($i=0;$i<count($arrayParam);$i++)
		{
			if ($i > 0)
				$sql .= "','";
			
			$sql .= $arrayParam[$i];
			$myBdd->utyJournal('Suppression utilisateur', '', '', 'NULL', 'NULL', 'NULL', $arrayParam[$i]);
		}
		$sql .= "')";
	
		mysql_query($sql, $myBdd->m_link) or die ("Erreur Delete");
			
	}

	function GestionUtilisateur()
	{			
	  MyPageSecure::MyPageSecure(3);
		
		$alertMessage = '';
	
		$Cmd = utyGetPost('Cmd');
		$ParamCmd = utyGetPost('ParamCmd');
		
		$selectUser = '';
		if ($Cmd == 'Edit')
		{
			$selectUser = utyGetPost('ParamCmd');
			$Cmd = '';
		}

		if (strlen($Cmd) > 0)
		{
			if ($Cmd == 'Add')
				($_SESSION['Profile'] <= 3) ? $this->Replace(true) : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'Update')
				($_SESSION['Profile'] <= 3) ? $this->Replace(false) : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'Remove')
				($_SESSION['Profile'] <= 2) ? $this->Remove() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($alertMessage == '')
			{
				header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);	
				exit;
			}
		}

		$this->SetTemplate("Gestion des Utilisateurs", "Utilisateurs", false);
		$this->Load($selectUser);
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplate('GestionUtilisateur');
	}
}		  	

$page = new GestionUtilisateur();
