<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

// Mode Grand-Public

class Accueil extends MyPage 
{	
	function Accueil()
	{			
		MyPage::MyPage();
		$this->SetTemplate("Accueil", "Accueil", true);
		$this->DisplayTemplateNew('Accueil');
	}
}		  	

$page = new Accueil();
