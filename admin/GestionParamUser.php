<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion des paramètres de l'utilisateur

class GestionParamUser extends MyPageSecure	 
{	
	function Load()
	{
		$myBdd = new MyBdd();
		
		// Chargement des infos Utilisateur
		$sql  = "SELECT u.*, l.*, c.Libelle Nom_club ";
		$sql .= "FROM gickp_Utilisateur u, gickp_Liste_Coureur l Left Join gickp_Club c on (l.Numero_club = c.Code) ";
		$sql .= "WHERE u.Code = l.Matric ";
		$sql .= "AND l.Matric = '".utyGetSession('User')."' ";	 
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load Param User");
		$num_results = mysql_num_rows($result);
	
		if ($num_results == 1)
		{
			$row = mysql_fetch_array($result);	
		
			$this->m_tpl->assign('UCode', $row['Code']);
			$this->m_tpl->assign('UIdentite', $row['Identite']);
			$this->m_tpl->assign('UNom', $row['Nom']);
			$this->m_tpl->assign('UPrenom', $row['Prenom']);
			$this->m_tpl->assign('USexe', $row['Sexe']);
			$this->m_tpl->assign('UNaissance', $row['Naissance']);
			$this->m_tpl->assign('UClub', $row['Nom_club']);
			$this->m_tpl->assign('UComite_dept', $row['Numero_comite_dept']);
			$this->m_tpl->assign('UComite_reg', $row['Numero_comite_reg']);
			$this->m_tpl->assign('UPagaie_EVI', $row['Pagaie_EVI']);
			$this->m_tpl->assign('UPagaie_MER', $row['Pagaie_MER']);
			$this->m_tpl->assign('UPagaie_ECA', $row['Pagaie_ECA']);
			$this->m_tpl->assign('USaison', $row['Origine']);
			$this->m_tpl->assign('UMail', $row['Mail']);
			$this->m_tpl->assign('UTel', $row['Tel']);
			$this->m_tpl->assign('UFonction', $row['Fonction']);
			$this->m_tpl->assign('UFiltre_competition', $row['Filtre_competition']);
			$this->m_tpl->assign('UFiltre_saison', $row['Filtre_saison']);
			$this->m_tpl->assign('UFiltre_journee', $row['Filtre_journee']);
			$this->m_tpl->assign('UFiltre_equipe', $row['Filtre_equipe']);
		}
	}
	
	
	function UpdateParamUser()
	{
		$myBdd = new MyBdd();

		// Mise à jour des infos Utilisateur
		$sql  = "UPDATE gickp_Utilisateur SET ";
		$sql .= "Mail = '".utyGetPost('Mail1')."', ";
		$sql .= "Fonction = '".utyGetPost('Fonction')."', ";
		$sql .= "Tel = '".utyGetPost('Tel')."' ";
		$sql .= "WHERE Code = '".utyGetSession('User')."' ";	 
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Update Param User");
	}
	
	function UpdatePassword()
	{
		$pass1 = utyGetPost('Pass1');
		$pass2 = utyGetPost('Pass2');
		$pass3 = utyGetPost('Pass3');
		
		if(($pass1 != '') && ($pass2 == $pass3))
		{
			$myBdd = new MyBdd();
			
			$sql  = "SELECT Pwd ";
			$sql .= "FROM gickp_Utilisateur ";
			$sql .= "WHERE Code = '".utyGetSession('User')."' ";	 

			$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Select Password".$sql);
			$row = mysql_fetch_array($result);
			$real_pass = $row['Pwd'];
			
			if(md5($pass1) == $real_pass)
			{
				// Mise à jour du mot de passe
				$sql  = "UPDATE gickp_Utilisateur ";
				$sql .= "SET Pwd = '".md5($pass2)."' ";
				$sql .= "WHERE Code = '".utyGetSession('User')."' ";	 
				
				$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Update Password");
			}
			else
				die("erreur change password");
		}
	}
	
	function GestionParamUser()
	{			
	  MyPageSecure::MyPageSecure(100);
		
		$Cmd = '';
		if (isset($_POST['Cmd']))
			$Cmd = $_POST['Cmd'];

		if (strlen($Cmd) > 0)
		{
			if ($Cmd == 'UpdateParamUser')
				$this->UpdateParamUser();
					
			if ($Cmd == 'UpdatePassword')
				$this->UpdatePassword();
					
			header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);	
			exit;	
		}

		$this->SetTemplate("Paramètres utilisateurs", "Admin", false);
		$this->Load();
		$this->DisplayTemplate('GestionParamUser');
	}
}		  	

$page = new GestionParamUser();

?>
