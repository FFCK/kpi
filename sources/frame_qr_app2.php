<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');
// QRcode class is now autoloaded via Composer

class QrApp2 extends MyPage
{
    function Load()
    {
        $myBdd = new MyBdd();

        $event = utyGetGet('event', '0');
        $this->m_tpl->assign('event', $event);

        $Css = utyGetGet('Css', '');
        $this->m_tpl->assign('Css', $Css);

        // Build target URL: app2 event page
        $data = URL_APP . '/event/' . $event;
        $this->m_tpl->assign('targetUrl', $data);

        $size = '500';
        $level = 'H';

        // Logo
        $logo = 'img/CNAKPI_small.jpg';

        $qrcode = new QRcode($data, $level);
        $QR = $qrcode->createPNG($size);
        if ($QR !== null) {
            $QR = $qrcode->addLogo($QR, $logo, .3);
            if ($QR !== null) {
                $dataUrl = $qrcode->getBase64Url($QR);
                imagedestroy($QR);
                $this->m_tpl->assign('dataUrl', $dataUrl);
            }
        }
    }

    function __construct()
    {
        parent::__construct();

        $this->SetTemplate("QrApp2", "App2", true);
        $this->Load();

        // COSANDCO : Gestion Param Voie
        if (utyGetGet('voie', false)) {
            $voie = (int) utyGetGet('voie', 0);
            $intervalle = (int) utyGetGet('intervalle', 0);

            if ($voie > 0) {
                $this->m_tpl->assign('voie', $voie);
                $this->m_tpl->assign('intervalle', $intervalle);
            }
        }

        $this->DisplayTemplateFrame('frame_qr_app2');
    }
}

$page = new QrApp2();
