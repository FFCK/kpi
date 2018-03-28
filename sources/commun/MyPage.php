<?php
header ('Content-type:text/html; charset=utf-8');

// Maintenance
//die ('<h1>Site en cours de maintenance.<br />Merci de patienter quelques instants...</h1>');
//phpinfo();

include_once('MyConfig.php');	 
include_once('MySmarty.php');
include_once('MyTools.php'); 

// Classe de Base pour toutes les Pages ...

class MyPage 		
{		 			 							  
	var $m_arrayMenu;	
	var $m_tpl;

	// Constructeur ...
	function MyPage()
	{
			session_start();
	}

	// Affichage de la Page ...
	function SetTemplate($title, $currentMenu, $bPublic)
	{				 
		$this->m_tpl = new MySmarty();
		
		$this->m_tpl->assign('NUM_VERSION', NUM_VERSION);
        // Utilisateur
		$this->m_tpl->assign('user', utyGetSession('User'));
		$profile = utyGetSession('Profile');
		$profile = utyGetPost('profilTest', $profile);
		$_SESSION['Profile'] = $profile;
		$this->m_tpl->assign('profile', $profile);
		$this->m_tpl->assign('profileOrigine', utyGetSession('ProfileOrigine'));
		$this->m_tpl->assign('Limit_Clubs', utyGetSession('Limit_Clubs'));
		
		$this->m_tpl->assign('userName', ucwords(strtolower(utyGetSession('userName'))));	
		$this->m_tpl->assign('Club', utyGetSession('Club'));
		$this->m_tpl->assign('masquer', utyGetSession('masquer', 0));

		$arrayMenu = array();
		
		if ($bPublic)
		{
			// Menu "Public"
			array_push($arrayMenu, array('name' => 'Accueil', 'href' => 'index.php'));
			array_push($arrayMenu, array('name' => 'Calendrier', 'href' => 'kpcalendrier.php'));
			array_push($arrayMenu, array('name' => 'Matchs', 'href' => 'kpmatchs.php'));
//			array_push($arrayMenu, array('name' => 'Matchs', 'href' => 'Journee.php'));
//			array_push($arrayMenu, array('name' => 'Classement', 'href' => 'Classement.php'));
			array_push($arrayMenu, array('name' => 'Classements', 'href' => 'kpclassements.php'));
			array_push($arrayMenu, array('name' => 'Historique', 'href' => 'kphistorique.php'));
			array_push($arrayMenu, array('name' => 'Equipes', 'href' => 'kpequipes.php'));
			array_push($arrayMenu, array('name' => 'Clubs', 'href' => 'kpclubs.php'));
//			array_push($arrayMenu, array('name' => 'Forum', 'href' => 'http://www.poloweb.org/forum/'));
			array_push($arrayMenu, array('name' => 'Administration', 'href' => 'admin/GestionCompetition.php'));
		}
		else
		{
			// Menu "Administration" ...
			if (PRODUCTION || DEV)
				array_push($arrayMenu, array('name' => 'Accueil_Public', 'href' => '../'));
			if (isset($profile) && $profile<=10)
				array_push($arrayMenu, array('name' => 'Competitions', 'href' => 'GestionCompetition.php'));
			if (isset($profile) && $profile<=9)
				array_push($arrayMenu, array('name' => 'Docs', 'href' => 'GestionDoc.php'));
			if (isset($profile) && $profile<=2 && (PRODUCTION || DEV))
				array_push($arrayMenu, array('name' => 'Evenements', 'href' => 'GestionEvenement.php'));
			if (isset($profile) && $profile<=9)
				array_push($arrayMenu, array('name' => 'Equipes', 'href' => 'GestionEquipe.php'));
			if (isset($profile) && $profile<=9 && (PRODUCTION || DEV))
				array_push($arrayMenu, array('name' => 'Clubs', 'href' => 'GestionStructure.php'));
			if (isset($profile) && $profile<=8)
				array_push($arrayMenu, array('name' => 'Athletes', 'href' => 'GestionAthlete.php'));
			if (isset($profile) && $profile<=9)
				array_push($arrayMenu, array('name' => 'Journees_phases', 'href' => 'GestionCalendrier.php'));
			if (isset($profile) && $profile<=9)
				array_push($arrayMenu, array('name' => 'Matchs', 'href' => 'GestionJournee.php'));
			if (isset($profile) && $profile<=9)
				array_push($arrayMenu, array('name' => 'Classements', 'href' => 'GestionClassement.php'));
			if (isset($profile) && $profile<=9)
				array_push($arrayMenu, array('name' => 'Stats', 'href' => 'GestionStats.php'));
			if (isset($profile) && $profile<=6)
				array_push($arrayMenu, array('name' => 'Import', 'href' => 'ImportPCE.php'));
			if (isset($profile) && $profile<=3 && (PRODUCTION || DEV))
				array_push($arrayMenu, array('name' => 'Utilisateurs', 'href' => 'GestionUtilisateur.php'));
		}
		
		$this->m_arrayMenu = $arrayMenu;
		
		if (PRODUCTION || DEV)
		{
			$this->m_tpl->assign('bProd', True);
			$loc = '';
            if (isset($_SESSION['mirror'])) {
                $this->m_tpl->assign('bMirror', $_SESSION['mirror']);
            }
        }
		else // Mode Local
		{
			$this->m_tpl->assign('bProd', False);
			$loc = 'LOCAL-';
		}

		$this->m_tpl->assign('title', $loc.$title);	
		$this->m_tpl->assign('bPublic', $bPublic);
		
		if (isset($_SESSION['User']))
		{
			// Titre + (Saison)
			$this->m_tpl->assign('headerTitle', $title);
			$this->m_tpl->assign('Saison', utyGetSaison());
		}
		else
		{
			// Titre + (Saison)
			$this->m_tpl->assign('headerTitle', $title);
			$this->m_tpl->assign('Saison', utyGetSaison());
		}

		$this->m_tpl->assign('arraymenu', $this->m_arrayMenu);
		$this->m_tpl->assign('currentmenu',$currentMenu);
		
			// Utilisateur
		$this->m_tpl->assign('user', utyGetSession('User'));
		$profile = utyGetSession('Profile');
		$profile = utyGetPost('profilTest', $profile);
		$_SESSION['Profile'] = $profile;
		$this->m_tpl->assign('profile', $profile);
		$this->m_tpl->assign('profileOrigine', utyGetSession('ProfileOrigine'));
		$this->m_tpl->assign('Limit_Clubs', utyGetSession('Limit_Clubs'));
		$AuthModif = utyAuthModif();
		$this->m_tpl->assign('AuthModif', $AuthModif);
		
		$this->m_tpl->assign('userName', ucwords(strtolower(utyGetSession('userName'))));	
		$this->m_tpl->assign('Club', utyGetSession('Club'));
		
			// Chargement css supplémentaire
		if (isset($_GET['Css'])){
			$css_sup = htmlspecialchars($_GET['Css'], ENT_QUOTES);
			$this->m_tpl->assign('css_supp', $css_sup);
		}
			
        // Langues
		// $smarty->config_dir = 'https://kayak-polo.info/commun/';
		$lang = utyGetSession('lang', 'fr');
		$lang = utyGetGet('lang', $lang);
		$_SESSION['lang'] = $lang;
		$this->m_tpl->assign('lang', $lang);
		
        //Message d'erreur ou d'avertissement
        if (!isset($AlertMessage)) {
            $AlertMessage = '';
        }
        $this->m_tpl->assign('AlertMessage', $AlertMessage);

	}
	
	// DisplayTemplate
	function DisplayTemplate($tplName)
	{
			$this->m_tpl->assign('contenutemplate', $tplName);	
			$this->DisplayTemplateGlobal('page');
	}

	// DisplayTemplateAdm
	function DisplayTemplateAdm($tplName)
	{
			$this->m_tpl->assign('contenutemplate', $tplName);	
			$this->DisplayTemplateGlobal('pageAdm');
	}

	// DisplayTemplateJquery
	function DisplayTemplateJquery($tplName)
	{
			$this->m_tpl->assign('contenutemplate', $tplName);	
			$this->DisplayTemplateGlobal('page_jq');
	}
	
	
	// DisplayTemplateMap
	function DisplayTemplateMap($tplName)
	{
			$this->m_tpl->assign('contenutemplate', $tplName);	
			$this->DisplayTemplateGlobal('pageMap');
	}
	// DisplayTemplateMap2
	function DisplayTemplateMap2($tplName)
	{
			$this->m_tpl->assign('contenutemplate', $tplName);	
			$this->DisplayTemplateGlobal('pageMap2');
	}
	
	// DisplayTemplateNu
	function DisplayTemplateNu($tplName)
	{
			$this->m_tpl->assign('contenutemplate', $tplName);	
			$this->DisplayTemplateGlobal('pageNu');
	}
	
	// DisplayTemplateNu2
	function DisplayTemplateNu2($tplName)
	{
			$this->m_tpl->assign('contenutemplate', $tplName);	
			$this->DisplayTemplateGlobal('pageNu2');
	}
	
	// DisplayTemplateCalendar
	function DisplayTemplateCalendar($tplName)
	{
			$this->m_tpl->assign('contenutemplate', $tplName);	
			$this->DisplayTemplateGlobal('pageCalendar');
	}
	
	// DisplayTemplateWP
	function DisplayTemplateWP($tplName)
	{
			$this->m_tpl->assign('contenutemplate', $tplName);	
			$this->DisplayTemplateGlobal('pageWP');
	}
	
	// DisplayTemplateNew
	function DisplayTemplateNew($tplName)
	{
			$this->m_tpl->assign('contenutemplate', $tplName);	
			$this->DisplayTemplateGlobal('kppage');
	}
    
	// DisplayTemplateNew
	function DisplayTemplateNewWide($tplName)
	{
			$this->m_tpl->assign('contenutemplate', $tplName);	
			$this->DisplayTemplateGlobal('kppagewide');
	}
    
	// DisplayTemplateFrame
	function DisplayTemplateFrame($tplName)
	{
			$this->m_tpl->assign('contenutemplate', $tplName);	
			$this->DisplayTemplateGlobal('frame_page');
	}
    
	// DisplayTemplateFullPage
	function DisplayTemplateFullPage($tplName)
	{
			$this->m_tpl->assign('contenutemplate', $tplName);	
			$this->DisplayTemplateGlobal('fppage');
	}
    
	// DisplayTemplateLogin
	function DisplayTemplateBootstrap($tplName)
	{
			$this->m_tpl->assign('contenutemplate', $tplName);	
			$this->DisplayTemplateGlobal('pagelogin');
	}
    
	// DisplayTemplate
	function DisplayTemplateGlobal($tplName)
	{
		$tplFullName  = $this->m_tpl->template_dir;
		
		$tplFullName .= '/';
		$tplFullName .= $tplName;
		$tplFullName .= $this->GetTemplateExtension();
		
		if (file_exists($tplFullName))
			$tplName .= $this->GetTemplateExtension();
		else
			$tplName .= '.tpl';

		$this->m_tpl->display($tplName);
	}
	
	// GetTemplateExtension 	
	function GetTemplateExtension()
	{
		if (isset($_SESSION['TPL_EXTENSION']))
			return $_SESSION['TPL_EXTENSION'];
			
		return '.tpl';
	}
}

// Classe de Base pour les Pages sécurisées ...

class MyPageSecure extends MyPage
{
	function MyPageSecure($profile)
	{
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Test si Authentification et Niveau suffisant ...
        if (isset($_SESSION['Profile']) && $_SESSION['Profile'] > 0)
        {
            if ($profile >= $_SESSION['Profile'])
                return;
        }

        //header("Location: http://".$_SERVER['HTTP_HOST'].MAIN_DIRECTORY."/admin/Login.php?Src=".$_SERVER['PHP_SELF']);	
        header("Location: Login.php?Src=".$_SERVER['PHP_SELF']);	
        exit;	
	}
}

?>
