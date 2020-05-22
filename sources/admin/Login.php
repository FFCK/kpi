<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Connexion  

class Login extends MyPage 
{	
	function __construct()
	{
		session_start();
        $myBdd = new MyBdd();
        
		if (isset($_GET['Src'])) {
            $loginTarget = $myBdd->RealEscapeString($_GET['Src']);
            $_SESSION['loginTarget'] = $loginTarget;
            $target = str_replace('&lang=en', '', $loginTarget);
            $target = str_replace('&lang=fr', '', $loginTarget);
            $target = '/admin/Login.php?Src=' . $target;
        }

        //if ($_SESSION['loginTarget'] == 'index.php' || $_SESSION['loginTarget'] == 'Login.php' || $_SESSION['loginTarget'] == '')
		//	$_SESSION['loginTarget'] = '/Index2.php';
			
		if ( (isset($_POST['User'])) && (isset($_POST['Mel']))  && ($_POST['Mode'] == 'Regeneration') ) {
			$user = preg_replace('`^[0]*`', '', trim($_POST['User']));
            $mel = trim(utyGetPost('Mel'));
            
			$sql = "SELECT u.* 
				FROM gickp_Utilisateur u, gickp_Liste_Coureur c 
				WHERE u.Code = ? 
				AND u.Mail = ? 
				AND u.Code = c.Matric ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($user, $mel));
			if ($result->rowCount() == 1) {
				$row = $result->fetch();
				$gpwd = Genere_Password(10);
				//Mise à jour mot de passe
				$sql = "UPDATE gickp_Utilisateur 
					SET Pwd = ? 
					WHERE Code = ? ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array(md5($gpwd), $user));
				//MAIL 
				$sujet = 'Modification de votre mot de passe kayak-polo.info (KPI)';
				$email_expediteur = 'contact@kayak-polo.info';
				$email_reply = 'contact@kayak-polo.info'; 
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
		} elseif (isset($_POST['User']) && isset($_POST['Pwd'])  && $_POST['Mode'] == 'Connexion') {
			$user = preg_replace( '`^[0]*`', '', $myBdd->RealEscapeString( trim( $_POST['User'] ) ) );
			$sql = "SELECT u.*, c.Nom, c.Prenom, c.Numero_club 
				FROM gickp_Utilisateur u, gickp_Liste_Coureur c 
				WHERE u.Code = ? 
				AND u.Code = c.Matric ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($user));
			if ($result->rowCount() == 1) {
				$row = $result->fetch();	  
				if ($row["Pwd"] === md5($_POST['Pwd'])) {
					$_SESSION['User'] = $user;
					$_SESSION['Profile'] = $row["Niveau"];
					$_SESSION['ProfileOrigine'] = $row["Niveau"];
					$_SESSION['Filtre_Competition'] = $row["Filtre_competition_sql"];
					$_SESSION['Limit_Clubs'] = $row["Limitation_equipe_club"];
					$_SESSION['userName'] = $row['Nom'].' '.$row['Prenom'];
					$_SESSION['Club'] = $row['Numero_club'];
                    
                    // Timezone Offset in minutes - server timezone offset
					$_SESSION['tzOffset'] = ((int) $myBdd->RealEscapeString( $_POST['tzOffset'] ) - 120) . ' minutes';
                    
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
					$Saison = $myBdd->GetActiveSaison();
					$sqlFiltreCompetition = utyGetFiltreCompetition('');
					$sql3 = "SELECT Code 
						FROM gickp_Competitions 
						WHERE Code_saison = ? 
						$sqlFiltreCompetition
						ORDER BY Code_niveau, COALESCE(Code_ref, 'z'), Code_tour, Code 
						LIMIT 1 ";
					$result3 = $myBdd->pdo->prepare($sql3);
					$result3->execute(array($Saison));

					$row3 = $result3->fetch();	
					$_SESSION['codeCompet'] = $row3['Code'];
					
					$myBdd->utyJournal('Connexion', '', '', '', '', NULL, $row['Prenom'].' '.$row['Nom'] );
					header("Location: http://".$_SERVER['HTTP_HOST'].$_SESSION['loginTarget']);	
					exit;	
				}
			}
		}
				
		$this->SetTemplate("Connexion", "Accueil", false);
        $this->m_tpl->assign('target', $target);

		if (isset($_SESSION['User']))
			$this->m_tpl->assign('User', $_SESSION['User']);
		else
			$this->m_tpl->assign('User', '');

		$this->m_tpl->assign('Pwd', '');
				
		$this->DisplayTemplateBootstrap('Login');
	}
}		  	

$page = new Login();

