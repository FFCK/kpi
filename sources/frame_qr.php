<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');
require_once('lib/qrcode/qrcode.class.php');

class Qr extends MyPage
{
    function Load()
    {
        $myBdd = new MyBdd();

        $lang = utyGetSession('lang');

        $codeCompet = utyGetGet('Compet',  'N1H');
        $this->m_tpl->assign('codeCompet', $codeCompet);

        $codeSaison = utyGetGet('Saison', $myBdd->GetActiveSaison());
        $this->m_tpl->assign('Saison', $codeSaison);

        $event = utyGetGet('event', '0');
        $this->m_tpl->assign('event', $event);

        if (utyGetGet('navGroup', false)) {
            $arrayNavGroup = $myBdd->GetOtherCompetitions($codeCompet, $codeSaison, true, $event);
            $this->m_tpl->assign('arrayNavGroup', $arrayNavGroup);
            $navgroup = "&navGroup=1";
            $this->m_tpl->assign('navGroup', 1);
            $group = utyGetGet('Group', $arrayNavGroup[0]['Code_ref']);
            $this->m_tpl->assign('group', $group);
        }

        $Round = utyGetGet('Round', '*');
        $this->m_tpl->assign('Round', $Round);

        $recordCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);
        $Group = $recordCompetition['Code_ref'];
        $this->m_tpl->assign('Code_ref', $Group);
        $typeClt = $recordCompetition['Code_typeclt'];

        $Css = utyGetGet('Css', '');
        $this->m_tpl->assign('Css', $Css);
        if ($Css != '') {
            $Css = '&Css=' . $Css;
        }

        //Logo
        if ($codeCompet != -1) {
            $logo = "img/logo/L-" . $Group . '-' . $codeSaison . '.jpg';
        }
        if (!file_exists($logo)) {
            $logo =  'img/CNAKPI_small.jpg';
        }

        $data = "https://www.kayak-polo.info/kpmatchs.php?lang=$lang&event=$event&Saison=$codeSaison&Group=$Group&Compet=$codeCompet&Round=$Round" . $Css . $navgroup;
        $size = '500';
        $level = 'H';

        $qrcode = new QRcode($data, $level);
        $QR = $qrcode->createPNG($size);
        $QR = $qrcode->addLogo($QR, $logo, .3);
        $dataUrl = $qrcode->getBase64Url($QR);
        imagedestroy($QR);
        $this->m_tpl->assign('dataUrl', $dataUrl);


        $data2 = "https://www.kayak-polo.info/kpchart.php?lang=$lang&event=$event&Saison=$codeSaison&Group=$Group&Compet=$codeCompet&Round=$Round" . $Css . $navgroup;

        $qrcode = new QRcode($data2, $level);
        $QR = $qrcode->createPNG($size);
        $QR = $qrcode->addLogo($QR, $logo, .3);
        $dataUrl2 = $qrcode->getBase64Url($QR);
        imagedestroy($QR);
        $this->m_tpl->assign('dataUrl2', $dataUrl2);

        $this->m_tpl->assign('page', 'Qr');
    }


    // Classement 		
    function __construct()
    {
        MyPage::MyPage();

        $this->SetTemplate("QrCodes", "Matchs", true);
        $this->Load();

        // COSANDCO : Gestion Param Voie ...
        if (utyGetGet('voie', false)) {
            $voie = (int) utyGetGet('voie', 0);
            if ($voie > 0) {
                $this->m_tpl->assign('voie', $voie);
            }

            $intervalle = (int) utyGetGet('intervalle', 0);
            if ($intervalle > 0) {
                $this->m_tpl->assign('intervalle', $intervalle);
            }
        }

        $this->DisplayTemplateFrame('frame_qr');
    }
}

$page = new Qr();
