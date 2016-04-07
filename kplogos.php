<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

// Affichage des logos
	
class Logos extends MyPage	 
{	
	function Load()
	{
		$myBdd = new MyBdd();
        $arrayLogos = array();
        $dir    = 'img/KIP/logo';
        $files = scandir($dir);
        foreach ($files as $file) {
            $part = explode('-',$file);
            if(isset($part[1])){
                $part2 = explode('.',$part[1]);
                if(is_file($dir.'/'.$file) && $part2[1] == 'png'){
                    $arrayLogos[] = $part[0];
                }
            }
        }
        $this->m_tpl->assign('arrayLogos', $arrayLogos);
        
	}
	

	function Logos()
	{			
        MyPage::MyPage();
		
		$this->SetTemplate("Logos", "Clubs", true);
		$this->Load();
		//$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplateNew('kplogos');
	}
}		  	

$page = new Logos();

