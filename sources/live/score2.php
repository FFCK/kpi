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
            <link href="./css/bootstrap.min.css" rel="stylesheet">
            <link href="./css/score.css?v=<?= NUM_VERSION ?>" rel="stylesheet">
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

        <div class="container-fluid ban_score_club">
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
            <div id="terrain" class="btn btn-default disabled"></div>
        <?php
        }
        ?>
        <div id="categorie"></div>

        <div id="bandeau_goal" class="ban_goal_card_2">
            <div id="goal_card"></div>
            <div id="banner_goal_card" class="text-left">
                <div id="match_event_line2" class="banner_line text-left"></div>
                <div id="match_event_line1" class="banner_line text-left"></div>
            </div>
        </div>

    <?php
    }

    function Script()
    {
        parent::Script();

        $event = $this->GetParamInt('event', 0);
        $terrain = $this->GetParamInt('terrain', 1);
        $speaker = $this->GetParamInt('speaker', 0);
        $voie = $this->GetParamInt('voie', 0);

    ?>
        <script type="text/javascript" src="./js/match.js?v=<?= NUM_VERSION ?>"></script>
        <script type="text/javascript" src="./js/voie.js?v=<?= NUM_VERSION ?>"></script>
        <script type="text/javascript" src="./js/score2.js?v=<?= NUM_VERSION ?>"></script>
        <script type="text/javascript">
            $(document).ready(function() {
                Init(<?php echo "$event,$terrain,$speaker,$voie"; ?>);
            });
        </script>
<?php
    }
}

new Score($_GET);
