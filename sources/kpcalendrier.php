<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

class Calendrier extends MyPage
{	
	function Load()
	{
		if($_SESSION['lang'] == 'EN')
			$this->m_tpl->assign('Lang', 'EN');
	}
	
	
	function __construct()
	{			
		parent::__construct();
		
		$this->SetTemplate("Calendrier", "Calendrier", true);
		$this->Load();
		//$this->m_tpl->assign('AlertMessage', $alertMessage);
		
		$this->DisplayTemplateNew('kpcalendrier');
	}
}		  	

$page = new Calendrier();

