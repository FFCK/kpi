<?php
include_once('page.php');

/**
 * Equipes de club
 */
class Score extends MyPage
{
  function Header()
  {
  }
  function Footer()
  {
  }

  function Head()
  {
?>

    <head>
      <title>Game event (clubs)</title>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta name="author" content="F.F.C.K.">
      <meta name="Description" content="KAYAK POLO - LIVE" />
      <meta name="Keywords" content="kayak polo, ffck" />
      <meta name="rating" content="general">
      <meta name="Robots" content="all">

      <meta name="viewport" content="width=device-width, initial-scale=1">

      <!-- CSS styles -->
      <link href="../lib/bootstrap-5.1.3-dist/css/bootstrap.min.css?v=<?= NUM_VERSION ?>" rel="stylesheet">
      <link href="../css/animate/animate.4.1.1.css?v=<?= NUM_VERSION ?>" rel="stylesheet" />
      <link href="./css/score.css?v=<?= NUM_VERSION ?>" rel="stylesheet">
      <?= $this->CheckCss() ?>
      <?php
      if ($this->GetParam('speaker') == '1') {
      ?>
        <link href="./css/score_speaker.css" rel="stylesheet">
      <?php
      }
      ?>

      <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
      <!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->

    </head>
  <?php
  }

  function Content()
  {
  ?>

    <div id="bandeau_goal" class="ban_goal_card_2 animate__animated">
      <div id="goal_card"></div>
      <div id="banner_goal_card" class="text-start">
        <div id="match_event_line2" class="banner_line text-start"></div>
        <div id="match_event_line1" class="banner_line text-start"></div>
        <div id="match_player"><img src="/img/KIP/players/none.png" alt=""></div>
      </div>
    </div>

  <?php
  }

  function Script()
  {
  ?>
    <script type="text/javascript" src="../js/axios/axios.min.js?v=<?= NUM_VERSION ?>"></script>
    <script type="text/javascript" src="../lib/bootstrap-5.1.3-dist/js/bootstrap.min.js?v=<?= NUM_VERSION ?>"></script>
    <?php

    $event = $this->GetParamInt('event', 0);
    $terrain = $this->GetParamInt('terrain', 1);
    $speaker = $this->GetParamInt('speaker', 0);
    $voie = $this->GetParamInt('voie', 0);

    ?>
    <script type="text/javascript" src="./js/match.js?v=<?= NUM_VERSION ?>"></script>
    <script type="text/javascript" src="./js/voie_ax.js?v=<?= NUM_VERSION ?>"></script>
    <script type="text/javascript" src="./js/score_club_s.js?v=<?= NUM_VERSION ?>"></script>
    <script type="text/javascript">
      document.addEventListener('DOMContentLoaded', function() {
        Init(<?php echo "$event, $terrain, $speaker, $voie"; ?>)
      }, false)
    </script>
<?php
  }
}

new Score($_GET);
