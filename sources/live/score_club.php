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
            <title>Score (clubs)</title>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="author" content="F.F.C.K.">
            <meta name="Description" content="KAYAK POLO - LIVE" />
            <meta name="Keywords" content="kayak polo, ffck" />
            <meta name="rating" content="general">
            <meta name="Robots" content="all">

            <meta name="viewport" content="width=device-width, initial-scale=1">

            <!-- CSS styles -->
            <link href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css?v=5.3.8" rel="stylesheet">
            <link href="../css/animate/animate.4.1.1.css?v=5.3.8" rel="stylesheet" />
            <link href="./css/score.css?v=5.3.8" rel="stylesheet">
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
        <!--<div id="match_horloge_etat"></div>-->

        <div id="ban_score_club" class="container-fluid animate__animated animate__fadeInDown">
            <div id="bandeau_score">
                <div id="match_horloge"></div>
                <div id="match_periode"></div>

                <div id="equipe1"></div>
                <div id="equipe2"></div>

                <div id="nation1"></div>
                <div id="nation2"></div>

                <div id="score1"></div>
                <div id="score_separation">-</div>
                <div id="score2"></div>

            </div>
        </div>

        <?php
        if ($this->GetParam('speaker') == '1') {
        ?>
            <div id="lien_pdf"></div>
            <div id="terrain" class="btn btn-secondary disabled"></div>
        <?php
        }
        ?>
        <div id="categorie" class="animate__animated animate__fadeInUp"></div>

        <div id="bandeau_goal" class="ban_goal_card_2 animate__animated">
            <div id="goal_card"><img id="goal_card_img" class="evt center" src="img/ball.png"></div>
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
        <script type="text/javascript" src="../js/axios/axios.min.js?v=5.3.8"></script>
        <script type="text/javascript" src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js?v=5.3.8"></script>
        <?php

        $event = $this->GetParamInt('event', 0);
        $terrain = $this->GetParamInt('terrain', 1);
        $speaker = $this->GetParamInt('speaker', 0);
        $voie = $this->GetParamInt('voie', 0);

        ?>
        <script type="text/javascript" src="./js/match.js?v=5.3.8"></script>
        <script type="text/javascript" src="./js/voie_ax.js?v=5.3.8"></script>
        <script type="text/javascript" src="./js/score_club.js?v=5.3.8"></script>
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                Init(<?php echo "$event, $terrain, $speaker, $voie"; ?>)
            }, false)
        </script>
<?php
    }
}

new Score($_GET);
