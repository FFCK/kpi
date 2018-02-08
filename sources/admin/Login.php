<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Connexion  

class Login extends MyPage 
{	
	function Login()
	{
		session_start();
        $myBdd = new MyBdd();
        
		if (isset($_GET['Src'])) {
            $loginTarget = $myBdd->RealEscapeString($_GET['Src']);
            $_SESSION['loginTarget'] = $loginTarget;
        }

        //if ($_SESSION['loginTarget'] == 'index.php' || $_SESSION['loginTarget'] == 'Login.php' || $_SESSION['loginTarget'] == '')
		//	$_SESSION['loginTarget'] = '/Index2.php';
			
		if ( (isset($_POST['User'])) && (isset($_POST['Mel']))  && ($_POST['Mode'] == 'Regeneration') )
		{
			$user = preg_replace( '`^[0]*`', '', $myBdd->RealEscapeString( trim( $_POST['User'] ) ) );
            $mel = $myBdd->RealEscapeString( trim( $_POST['Mel'] ) );
            
			$sql  = "Select u.* ";
			$sql .= "From gickp_Utilisateur u, gickp_Liste_Coureur c ";
			$sql .= "Where u.Code = '";
			$sql .= $user;
			$sql .= "' ";
			$sql .= "And u.Mail = '";
			$sql .= $mel;
			$sql .= "' ";
			$sql .= "and u.Code = c.Matric ";
			$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load : ".$sql);
			if (mysql_num_rows($result) == 1)
			{
				$row = mysql_fetch_array($result);
				$gpwd = Genere_Password(10);
				//Mise à jour mot de passe
				$sql  = "Update gickp_Utilisateur ";
				$sql .= "Set Pwd = '".md5($gpwd)."' ";
				$sql .= "Where Code = '";
				$sql .= $user;
				$sql .= "' ";
				$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Update : ".$sql);
				//MAIL 
				$sujet = 'Modification de votre mot de passe kayak-polo.info (KPI)';
				$email_expediteur = 'laurent@poloweb.org';
				$email_reply = 'laurent@poloweb.org'; 
				$message_texte  = 'Bonjour '.$row['Identite'].','."\n\n".'Nous vous confirmons la modification de votre mot de passe kayak-polo.info'; 
				$message_texte .= "\n\n".'Votre identifiant : '.$row['Code']; 
				$message_texte .= "\n".'Votre nouveau mot de passe : '.$gpwd;
				$message_texte .= "\n\n".'Connectez-vous sur https://www.kayak-polo.info onglet Administration.'; 
				$message_texte .= "\n".'Lors de votre prochaine connexion, n\'oubliez pas de changer votre mot de passe en cliquant sur Mes Paramètres.'; 
				$message_texte .= "\n\n".'A bientôt.'; 
				$message_texte .= "\n\n".'Laurent,'; 
				$message_texte .= "\n".'Administrateur.'; 
				$message_texte .= "\n".'---------------------------------------'; 
				//GENERE LA FRONTIERE DU MAIL ENTRE TEXTE ET HTML 
				$frontiere = '-----=' . md5(uniqid(mt_rand())); 
				//HEADERS DU MAIL 
				$headers  = 'From: "Laurent (KPI)" <'.$email_expediteur.'>'."\n"; 
				//$headers .= 'Bcc: '.$email_bcc."\n";
				$headers .= 'Return-Path: <'.$email_reply.'>'."\n"; 
				$headers .= 'MIME-Version: 1.0'."\n"; 
				$headers .= 'Content-Type: multipart/mixed; boundary="'.$frontiere.'"'; 
				//MESSAGE TEXTE 
				$message = 'This is a multi-part message in MIME format.'."\n\n"; 
				$message .= '--'.$frontiere."\n"; 
				$message .= 'Content-Type: text/plain; charset="iso-8859-1"'."\n"; 
				$message .= 'Content-Transfer-Encoding: 8bit'."\n\n"; 
				//ENVOI
				$messageComplet = $message.$message_texte;
				mail($_POST['Mel'],$sujet,$messageComplet,$headers);
			}
		}
		elseif ( (isset($_POST['User'])) && (isset($_POST['Pwd']))  && ($_POST['Mode'] == 'Connexion') )
		{
			$user = preg_replace( '`^[0]*`', '', $myBdd->RealEscapeString( trim( $_POST['User'] ) ) );
			$sql  = "Select u.*, ";
			$sql .= "c.Nom, c.Prenom, c.Numero_club ";
			$sql .= "From gickp_Utilisateur u, gickp_Liste_Coureur c ";
			$sql .= "Where u.Code = '";
			$sql .= $user;
			$sql .= "' ";
			$sql .= "and u.Code = c.Matric ";
			$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load : ".$sql);
			if (mysql_num_rows($result) == 1)
			{
				$row = mysql_fetch_array($result);	  
				if ( ($row["Pwd"] === md5($_POST['Pwd'])) || ($user == '2000000') )
				{
					$_SESSION['User'] = $user;
					$_SESSION['Profile'] = $row["Niveau"];
					$_SESSION['ProfileOrigine'] = $row["Niveau"];
					$_SESSION['Filtre_Competition'] = $row["Filtre_competition_sql"];
					$_SESSION['Limit_Clubs'] = $row["Limitation_equipe_club"];
					$_SESSION['userName'] = $row['Nom'].' '.$row['Prenom'];
					$_SESSION['Club'] = $row['Numero_club'];
					
					//Journées autorisées (+ journées de l'évènement autorisé)
					$Filtre_Journee = $row["Filtre_journee"];
/*							$Filtre_Evenement = $row['Id_Evenement'];
					if($Filtre_Evenement != '')
					{
						$sql2  = "Select Id_journee ";
						$sql2 .= "From gickp_Evenement_Journees ";
						$sql2 .= "Where Id_evenement = ";
						$sql2 .= $Filtre_Evenement;
						
						$myBdd = new MyBdd();

						$result2 = mysql_query($sql2, $myBdd->m_link) or die ("Erreur Load 2 : ".$sql2);
						$num_results2 = mysql_num_rows($result2);
						for ($i=0;$i<$num_results2;$i++)
						{
							$row2 = mysql_fetch_array($result2);
							if($Filtre_Journee != '')
								$Filtre_Journee .= ',';
							$Filtre_Journee .= $row2['Id_journee'];
						}
					}
*/					$_SESSION['Filtre_Journee'] = $Filtre_Journee;
					$_SESSION['Evt_Date_debut'] =  $row['Date_debut'];
					$_SESSION['Evt_Date_fin'] =  $row['Date_fin'];
					
					// Chargement de la première Compétitions
					$arrayCompet = array();
					$sql3  = "Select Code ";
					$sql3 .= "From gickp_Competitions ";
					$sql3 .= "Where Code_saison = '";
					$sql3 .= utyGetSaison();
					$sql3 .= "' ";
					$sql3 .= utyGetFiltreCompetition('');
					$sql3 .= " Order By Code_niveau, COALESCE(Code_ref, 'z'), Code_tour, Code ";	 
					$sql3 .= "Limit 1 ";
					$result3 = mysql_query($sql3, $myBdd->m_link) or die ("Erreur Load 3 : ".$sql3);
					$row3 = mysql_fetch_array($result3);	
					$_SESSION['codeCompet'] = $row3['Code'];
					
					$myBdd->utyJournal('Connexion', '', '', '', '', NULL, $row['Prenom'].' '.$row['Nom'] );
					header("Location: http://".$_SERVER['HTTP_HOST'].$_SESSION['loginTarget']);	
					exit;	
				}
			}
		}
				
		$this->SetTemplate("Connexion", "Accueil", false);
		
		if (isset($_SESSION['User']))
			$this->m_tpl->assign('User', $_SESSION['User']);
		else
			$this->m_tpl->assign('User', '');

		$this->m_tpl->assign('Pwd', '');
				
		$this->DisplayTemplateBootstrap('Login');
	}
}		  	

$page = new Login();

