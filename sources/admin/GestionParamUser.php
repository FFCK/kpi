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
		$sql = "SELECT u.*, l.*, c.Libelle Nom_club 
			FROM gickp_Utilisateur u, gickp_Liste_Coureur l 
			LEFT JOIN gickp_Club c ON (l.Numero_club = c.Code) 
			WHERE u.Code = l.Matric 
			AND l.Matric = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array(utyGetSession('User')));
		$num_results = $result->rowCount();
		if ($num_results == 1) {
			$row = $result->fetch();	
		
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
			$this->m_tpl->assign('UFiltre_equipe', $row['Limitation_equipe_club']);
		}
	}
	
	
	function UpdateParamUser()
	{
		$myBdd = new MyBdd();

		// Mise à jour des infos Utilisateur
		$sql = "UPDATE gickp_Utilisateur 
			SET Mail = ?, 
			Fonction = ?, 
			Tel = ? 
			WHERE Code = ? ";	 
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array(
			utyGetPost('Mail1'), utyGetPost('Fonction'), 
			utyGetPost('Tel'), utyGetSession('User')
		));
	}
	
	function UpdatePassword()
	{
		$pass1 = utyGetPost('Pass1');
		$pass2 = utyGetPost('Pass2');
		$pass3 = utyGetPost('Pass3');
		
		if ($pass1 != '' && $pass2 == $pass3) {
			$myBdd = new MyBdd();
			
			$sql = "SELECT Pwd 
				FROM gickp_Utilisateur 
				WHERE Code = ? ";	 
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array(utyGetSession('User')));

			$row = $result->fetch();
			$real_pass = $row['Pwd'];
			
			if (md5($pass1) == $real_pass) {
				// Mise à jour du mot de passe
				$sql = "UPDATE gickp_Utilisateur 
					SET Pwd = ? 
					WHERE Code = ? ";	 
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array(md5($pass2), utyGetSession('User')));
			}
			else
				die("error change password");
		}
	}
	
	function __construct()
	{			
	  	MyPageSecure::MyPageSecure(100);
		
		$Cmd = utyGetPost('Cmd', '');

		if (strlen($Cmd) > 0)
		{
			if ($Cmd == 'UpdateParamUser')
				$this->UpdateParamUser();
					
			if ($Cmd == 'UpdatePassword')
				$this->UpdatePassword();
					
			header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);	
			exit;	
		}

		$this->SetTemplate("Mes_parametres", "Utilisateur", false);
		$this->Load();
		$this->DisplayTemplate('GestionParamUser');
	}
}		  	

$page = new GestionParamUser();
