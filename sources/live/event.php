<?php
include_once('base.php');
include_once('create_cache_match.php');
include_once('page.php');
include_once('../commun/MyTools.php');

session_start();

class Event extends MyPage
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
            <title>Event cache generator</title>
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

            <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
            <!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->

        </head>
    <?php
    }

    function Content_Events($evt)
    {
        $db = new MyBdd();

        // Chargement Evenements  
        $sql = "SELECT e.* 
            FROM kp_evenement e 
            WHERE e.Publication = 'O' 
            ORDER BY e.Date_debut DESC ";
        $rEvents = null;
        $result = $db->pdo->query($sql);
        $rEvents = $result->fetchAll(PDO::FETCH_ASSOC);
        $retour = '';
        foreach ($rEvents as $key => $event) {
            if ($event['Id'] == $evt) {
                $selected = 'selected';
            } else {
                $selected = '';
            }
            $retour .= '<option value="' . $event['Id'] . '" ' . $selected . '>'
                . $event['Id'] . ' - ' . $event['Libelle'] . ' (' . $event['Lieu'] . ')';
            $retour .= '
                ';
        }

        return $retour;
    }

    function Btn_Events($evt)
    {
        $db = new MyBdd();

        $sql2 = "SELECT DISTINCT m.Date_match, m.Heure_match 
            FROM kp_match m
            LEFT JOIN kp_journee j ON (m.Id_journee = j.Id) 
            LEFT JOIN kp_evenement_journee ej ON (j.Id = ej.Id_journee)
            WHERE ej.Id_evenement = ? 
            GROUP BY m.Date_match
            ORDER BY m.Date_match, m.Heure_match ";
        $rDates = null;
        $result2 = $db->pdo->prepare($sql2);
        $result2->execute([$evt]);
        $rDates = $result2->fetchAll(PDO::FETCH_ASSOC);
        $retour = '';
        foreach ($rDates as $key => $date_evt) {
            $retour .= '<button class="btn_date_evt" data-date="' . $date_evt['Date_match'] . '" data-heure="' . $date_evt['Heure_match'] . '">
                ' . $date_evt['Date_match'] . ' ' . $date_evt['Heure_match'] . '</button>&nbsp;';
        }

        return $retour;
    }

    function Content()
    {
        $evt = utyGetInt($_GET, 'evt', utyGetSession('codeEvt'));
    ?>
        <div class="container">
            <br>
            <form method='GET' action='#' name='event_form' id='event_form' enctype='multipart/form-data'>
                <label for='idevent'>Event:</label>
                <select id='idevent' name='idevent'>
                    <?= $this->Content_Events($evt); ?>
                </select>
                <input type="hidden" id='id_event' name='id_event' value="<?= $evt ?>">
                <br>
                <?= $this->Btn_Events($evt); ?>
                <br>
                <br>

                <label for='date_event'>Date</label>
                <input type='date' id='date_event' name='date_event' Value='<?= date('Y-m-d') ?>' required>
                <br>

                <label for='hour_event'>Time</label>
                <input type='time' id='hour_event' name='hour_event' Value='<?= date('H:i') ?>' required>
                <br>

                <label for='offset_event'>Warm-up</label>
                <input type='text' id='offset_event' name='offset_event' Value='10' size="2"> minutes
                <br>

                <label for='pitch_event'>Pitches</label>
                <input type='text' id='pitch_event' name='pitch_event' Value='4' size="1">
                <br>

                <label for='delay_event'>Refresh delay</label>
                <input type='text' id='delay_event' name='delay_event' Value='10' size="2"> seconds
                <br>

                <button id='btn_go'>Generate cache</button>

                <h1 id='info_titre'></h1>
                <div id='info'></div>

            </form>
        </div>
    <?php
    }

    function Script()
    {
        parent::Script();
    ?>
        <script type="text/javascript" src="./js/event.js?v=<?= NUM_VERSION ?>"></script>
        <script type="text/javascript">
            $(document).ready(function() {
                Init();
            });
        </script>
<?php
    }
}

new Event($_GET);
