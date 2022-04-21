<?php
include_once('page.php');

class Teams extends MyPage
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
      <title>Teams</title>
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
      <link href="./css/tv2.css?v=<?= NUM_VERSION ?>" rel="stylesheet">
      <?= $this->CheckCss() ?>

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
    $anime = $this->GetParamInt('anime', 0);
    $anime_css = $anime ? ' animate__animated animate__slower animate__infinite animate__pulse' : '';
  ?>
    <div class="container-fluid ban_info_1_lines<?= $anime_css ?>">
      <div id="ban_info_1_lines" class="text-center">
        <div class="logo_sm"></div>
        <div id="banner_line1" class="h2 text-end"></div>
        <div id="banner_line2" class="h2 text-end"></div>

        <div class="row banner_line row">
          <div class="col-md-5 text-start">
            <span id="nation1"></span>
            &nbsp;
            <span id="equipe1"></span>
          </div>
          <div class="col-md-2 text-center">
            <span id="score1" class="badge bg-primary numero"></span>
            &nbsp;
            <span id="score2" class="badge bg-primary numero"></span>
          </div>
          <div class="col-md-5 text-end">
            <span id="equipe2"></span>
            &nbsp;
            <span id="nation2"></span>
          </div>
        </div>
      </div>
    </div>
  <?php
  }

  function Script()
  {
    $event = $this->GetParamInt('event', 0);
    $terrain = $this->GetParamInt('terrain', 1);
    $voie = $this->GetParamInt('voie', 0);
  ?>
    <script type="text/javascript" src="../js/axios/axios.min.js?v=<?= NUM_VERSION ?>"></script>
    <script type="text/javascript" src="../lib/bootstrap-5.1.3-dist/js/bootstrap.min.js?v=<?= NUM_VERSION ?>"></script>

    <script type="text/javascript" src="./js/match.js?v=<?= NUM_VERSION ?>"></script>
    <script type="text/javascript" src="./js/voie_ax.js?v=<?= NUM_VERSION ?>"></script>
    <script type="text/javascript" src="./js/teams.js?v=<?= NUM_VERSION ?>"></script>
    <script type="text/javascript">
      document.addEventListener('DOMContentLoaded', function() {
        Init(<?php echo "$event, $terrain, 0, $voie"; ?>)
      }, false)
    </script>
<?php
  }
}

new Teams($_GET);
