<?php
include_once('page.php');

class MultiScore extends MyPage
{
    function Header()
    {
    }
    function Footer()
    {
    }

    function Head()
    {
        $speaker = $this->GetParamInt('speaker', 0);
?>

        <head>
            <title>Multi-Scores</title>
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
            <link href="./css/multi_score.css?tick=<?php echo uniqid() ?>" rel="stylesheet">
            <?php if ($speaker == 1) { ?>
                <link href="./css/multi_score_speaker.css?tick=<?php echo uniqid() ?>" rel="stylesheet">
            <?php } elseif ($speaker == 2) { ?>
                <link href="./css/multi_score_phone.css?tick=<?php echo uniqid() ?>" rel="stylesheet">
            <?php } ?>

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
        $count = $this->GetParamInt('count', 4);

        for ($i = 1; $i <= $count; $i++) {
        ?>
            <div class='col-sm-6 quart'>
                <div id="ban_score">
                    <div class="terrain btn btn-default disabled" id="terrain_<?= $i ?>">Pitch <?= $i ?></div>

                    <div class="bandeau_score" id="bandeau_score_<?= $i ?>">
                        <div class="match_horloge" id="match_horloge_<?= $i ?>"></div>
                        <div class="match_periode" id="match_periode_<?= $i ?>"></div>

                        <div class="equipe1" id="equipe1_<?= $i ?>"></div>
                        <div class="equipe2" id="equipe2_<?= $i ?>"></div>

                        <div class="nation1" id="nation1_<?= $i ?>"></div>
                        <div class="nation2" id="nation2_<?= $i ?>"></div>

                        <div class="score1" id="score1_<?= $i ?>"></div>
                        <div class="score_separation" id="score_separation_<?= $i ?>">-</div>
                        <div class="score2" id="score2_<?= $i ?>"></div>

                        <!--<div class="categorie" id="categorie_<?= $i ?>"></div>-->
                    </div>
                </div>
                <?php
                if ($this->GetParam('speaker') == 1) {
                ?>
                    <div class="lien_pdf" id="lien_pdf_<?= $i ?>"></div>
                <?php
                }
                ?>

                <div id="bandeau_goal_<?= $i ?>" class="ban_goal_card">
                    <div id="goal_card_<?= $i ?>" class="goal_card"></div>
                    <div id="banner_goal_card_<?= $i ?>" class="ban_name text-left">
                        <div id="match_event_line2_<?= $i ?>" class="banner_line2 text-left"></div>
                        <div id="match_event_line1_<?= $i ?>" class="banner_line1 text-left clair"></div>
                    </div>
                </div>
            </div>
        <?php
        }
        ?>
            <div id="refresh_frequency"></div>
        <?php
    }

    function Script()
    {
        parent::Script();

        $id_event = $this->GetParamInt('event', 85);
        $count = $this->GetParamInt('count', 4);
        $voie = $this->GetParamInt('voie', 0);
        $refresh = $this->GetParamInt('refresh', 10);

        ?>
        <script type="text/javascript" src="./js/match.js"></script>
        <script type="text/javascript" src="./js/voie.js"></script>
        <script type="text/javascript" src="./js/multi_score.js"></script>
        <script type="text/javascript">
            $(document).ready(function() {
                Init(<?php echo "$id_event, $count, $voie, $refresh"; ?>);
            });
        </script>
<?php
    }
}

new MultiScore($_GET);
