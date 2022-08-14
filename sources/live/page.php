<?php

require_once('../commun/MyConfig.php');

define("VERSION", NUM_VERSION);

// Classe de Base pour toutes les Pages ...
class MyPage
{
  var $m_arrayParams;    // Tableau des Paramètres
  var $purifier;

  var $translate;

  // Constructeur ...
  function __construct(&$arrayParams)
  {
    // htmlpurifier
    if (is_file('lib/htmlpurifier/HTMLPurifier.auto.php')) {
      require_once 'lib/htmlpurifier/HTMLPurifier.auto.php';
    } else {
      require_once '../lib/htmlpurifier/HTMLPurifier.auto.php';
    }
    $config = HTMLPurifier_Config::createDefault();
    $config->set('Core.Encoding', 'UTF-8'); // replace with your encoding
    $config->set('HTML.Doctype', 'XHTML 1.0 Transitional'); // replace with your doctype
    $this->purifier = new HTMLPurifier($config);

    // Translation
    $this->translate['en'] = [
      'ARBITRES' => 'REFEREES',
      'CLASSEMENT FINAL' => 'FINAL RANKING',
      'KAYAK-POLO' => 'CANOE POLO'
    ];
    $this->translate['fr'] = [
      'ARBITRES' => 'ARBITRES',
      'CLASSEMENT FINAL' => 'CLASSEMENT FINAL',
      'KAYAK-POLO' => 'KAYAK-POLO'
    ];


    $this->m_arrayParams = &$arrayParams;
    $this->Display();
  }

  // GetParam ...
  function GetParam($key, $defaultValue = '')
  {
    if (isset($this->m_arrayParams[$key]))
      return $this->purifier->purify($this->m_arrayParams[$key]);
    else
      return $defaultValue;
  }

  function GetParamBool($key, $defaultValue = false)
  {
    if (isset($this->m_arrayParams[$key])) {
      if (((int) $this->m_arrayParams[$key]) == 0)
        return false;
      else
        return true;
    }
    return $defaultValue;
  }

  function GetParamInt($key, $defaultValue = -1)
  {
    if (isset($this->m_arrayParams[$key]))
      return (int) $this->m_arrayParams[$key];
    else
      return $defaultValue;
  }

  function GetParamDouble($key, $defaultValue = 0.0)
  {
    if (isset($this->m_arrayParams[$key]))
      return (float) $this->m_arrayParams[$key];
    else
      return $defaultValue;
  }

  // Affichage Classique de la Page ...
  function Display()
  {
    $this->Html();
    $this->Head();

    echo "<body>\n";
    $this->Body();
    echo "</body>\n";
    echo "</html>";
  }

  // Tag HTML ...
  function Html()
  {
?>
    <!DOCTYPE html>
    <html lang="fr">
  <?php
  }

  // Tag HEAD
  function Head()
  {
  ?>

    <head>
      <title>F.F.C.K.</title>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta name="author" content="F.F.C.K.">
      <meta name="Description" content="KAYAK POLO - LIVE" />
      <meta name="Keywords" content="kayak polo, ffck" />
      <meta name="rating" content="general">
      <meta name="Robots" content="all">

      <meta name="viewport" content="width=device-width, initial-scale=1">

      <!-- CSS styles -->
      <link href="./css/bootstrap.min.css" rel="stylesheet">
      <link href="./css/global.css" rel="stylesheet">
      <?= $this->CheckCss ?>

      <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
      <!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->

    </head>
  <?php
  }


  function CheckCss()
  {
    $css = $this->GetParam('css', '');
    if (in_array($css, [
      'simply',
      'cna',
      'usnational',
      'thury2014',
      'saintomer2017',
      'welland2018',
      'saintomer2022',
      'saintomer2022b'
    ])) {
      return '<link href="./css/' . $css . '.css?v=' . NUM_VERSION . '" rel="stylesheet">';
    }
    return;
  }

  function Lang($text)
  {
    $lang = $this->GetParam('lang', 'en');
    if (!in_array($lang, [
      'en',
      'fr'
    ])) {
      $lang = 'en';
    }
    if (isset($this->translate[$lang][$text])) {
      return $this->translate[$lang][$text];
    };
    return $text;
  }

  static function IsNavigatorIE()
  {
    $pos = strpos($_SERVER['HTTP_USER_AGENT'], 'Trident/');
    if ($pos === false) return false;
    return true;
  }

  // BODY ...
  function Body()
  {
    //		echo "<div class='container-fluid'>\n";

    $this->Header();
    $this->Menu();
    $this->Content();
    $this->Footer();

    //        echo "</div>\n";	// div container ...

    $this->Script();
  }

  // Header ...
  function Header()
  {
    /*
		<img class="centre" src="./img/THURY2014_Bandeau entete.jpg" />
		<div class="row">
			<div class="col-md-12"><p class="text-center">Thury-Harcourt (FRA) - 2014</p></div>
		</div>	
*/

    /*
	<div class="row">
			<div class="col-md-12" id="logo_header"></div>
	</div>	
*/
  ?>
    <img class="centre" src="./img/THURY2014_Bandeau entete.jpg" height="125" width="1240" />
  <?php
  }

  // Section Menu ...
  function Menu()
  {
  }

  // Section Content ...
  function Content()
  {
  }

  // Section Footer ...
  function Footer()
  {
  ?>
    <div class="row footer">
      <img class="centre" src="./img/THURY2014_Bandeau bas.jpg" />
    </div>
  <?php
  }

  function Script()
  {
  ?>
    <script src="js/jquery-1.11.2.min.js"></script>
    <script type="text/javascript" src="../js/axios/axios.min.js?v=<?= NUM_VERSION ?>"></script>
    <script type="text/javascript" src="./js/bootstrap.min.js"></script>
<?php
  }
}
