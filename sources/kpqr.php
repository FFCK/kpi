<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

require_once('qrcode/qrcode.class.php');

// QrCodes
	
class Logos extends MyPage	 
{	
	function Load()
	{
        $myBdd = new MyBdd();
        
        $data = isset($_GET['data']) ? $_GET['data'] : 'https://www.kayak-polo.info/kpchart.php?Group=T-BREIZH&Compet=T-BREIZH&Saison=2019&lang=en';
        $size = isset($_GET['size']) ? $_GET['size'] : '500';
        $level = isset($_GET['level']) ? $_GET['level'] : 'H'; // error level : L, M, Q, H
        $logo = isset($_GET['logo']) ? $_GET['logo'] : 'img/CNAKPI_small.jpg';

        $qrcode = new QRcode($data, $level);
        $QR = $qrcode->createPNG($size);
        $QR = $qrcode->addLogo($QR, $logo, .5);
        
        $dataUrl = $qrcode->getBase64Url($QR);

        echo '<img src="'.$dataUrl.'">';

        // imagepng($QR);
        imagedestroy($QR);

        
	}
	

	function __construct()
	{			
        MyPage::MyPage();
		
		$this->SetTemplate("Logos", "Clubs", true);
		$this->Load();
		//$this->m_tpl->assign('AlertMessage', $alertMessage);
		// $this->DisplayTemplateNew('kplogos');
	}
}		  	

$page = new Logos();

