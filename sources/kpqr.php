<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

// QrCodes - using endroid/qr-code via Composer autoloader

class Logos extends MyPage
{
        function Load()
        {
                $myBdd = new MyBdd();

                $data = utyGetGet('data', 'https://www.kayak-polo.info/kpchart.php?Group=T-BREIZH&Compet=T-BREIZH&Saison=2019&lang=en');
                $size = utyGetGet('size', '500');
                $level = utyGetGet('level', 'H'); // error level : L, M, Q, H
                $logo = utyGetGet('logo', 'img/CNAKPI_small.jpg');

                $qrcode = new QRcode($data, $level);
                $QR = $qrcode->createPNG($size);
                $QR = $qrcode->addLogo($QR, $logo, .5);

                $dataUrl = $qrcode->getBase64Url($QR);

                echo '<img src="' . $dataUrl . '">';

                // imagepng($QR);
                imagedestroy($QR);
        }


        function __construct()
        {
                parent::__construct();

                $this->SetTemplate("Logos", "Clubs", true);
                $this->Load();
                //$this->m_tpl->assign('AlertMessage', $alertMessage);
                // $this->DisplayTemplateNew('kplogos');
        }
}

$page = new Logos();
