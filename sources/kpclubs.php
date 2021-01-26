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
	}
		

	function __construct()
	{			
	  	MyPage::MyPage();
		
		$alertMessage = '';
		
		$Cmd = utyGetPost('Cmd', '');

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
