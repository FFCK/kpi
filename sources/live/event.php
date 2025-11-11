<?php
include_once('base.php');
include_once('create_cache_match.php');
include_once('page.php');
include_once('../commun/MyTools.php');

if(!isset($_SESSION)) {
	session_start(); 
}

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
            <title>Event cache generator - Worker Mode</title>
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

            <style>
                .worker-controls {
                    background: #f5f5f5;
                    padding: 20px;
                    border-radius: 8px;
                    margin: 20px 0;
                }
                .worker-status {
                    padding: 15px;
                    margin: 15px 0;
                    border-radius: 5px;
                    border-left: 5px solid #ccc;
                }
                .worker-status.running {
                    background: #d4edda;
                    border-color: #28a745;
                }
                .worker-status.stopped {
                    background: #f8d7da;
                    border-color: #dc3545;
                }
                .worker-status.paused {
                    background: #fff3cd;
                    border-color: #ffc107;
                }
                .status-indicator {
                    display: inline-block;
                    width: 12px;
                    height: 12px;
                    border-radius: 50%;
                    margin-right: 8px;
                    animation: pulse 2s infinite;
                }
                .status-indicator.running {
                    background: #28a745;
                }
                .status-indicator.stopped {
                    background: #dc3545;
                    animation: none;
                }
                .status-indicator.paused {
                    background: #ffc107;
                    animation: none;
                }
                @keyframes pulse {
                    0%, 100% { opacity: 1; }
                    50% { opacity: 0.5; }
                }
                .btn-worker {
                    margin: 5px;
                    padding: 10px 20px;
                    font-size: 14px;
                }
                .worker-info {
                    margin-top: 10px;
                    font-size: 13px;
                }
                .mode-toggle {
                    background: #007bff;
                    color: white;
                    padding: 10px;
                    border-radius: 5px;
                    margin-bottom: 20px;
                    text-align: center;
                }
            </style>

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

        $sql2 = "SELECT m.Date_match, m.Heure_match
            FROM kp_match m 
            LEFT JOIN kp_evenement_journee ej ON (m.Id_journee = ej.Id_journee)
            WHERE ej.Id_evenement = ? 
            AND m.Heure_match = (
                SELECT MIN(m2.Heure_match)
                FROM kp_match m2
                LEFT JOIN kp_evenement_journee ej2 ON (m2.Id_journee = ej2.Id_journee)
                WHERE ej2.Id_evenement = ? 
                AND m2.Date_match = m.Date_match
            )
            GROUP BY m.Date_match
            ORDER BY m.Date_match; ";
        $rDates = null;
        $result2 = $db->pdo->prepare($sql2);
        $result2->execute([$evt, $evt]);
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
            <div class="mode-toggle">
                <strong>Worker Mode</strong> - Background process for automatic cache generation
            </div>

            <!-- Worker Status Display -->
            <div id="worker-status-container">
                <div class="worker-status stopped" id="worker-status">
                    <h3>
                        <span class="status-indicator stopped" id="status-indicator"></span>
                        <span id="status-text">Worker Status: Checking...</span>
                    </h3>
                    <div class="worker-info" id="worker-info">
                        Loading worker status...
                    </div>
                </div>
            </div>

            <!-- Worker Controls -->
            <div class="worker-controls">
                <h4>Worker Controls</h4>
                <button class="btn btn-success btn-worker" id="btn-start-worker">
                    ▶ Start Worker
                </button>
                <button class="btn btn-warning btn-worker" id="btn-pause-worker" style="display:none;">
                    ⏸ Pause Worker
                </button>
                <button class="btn btn-info btn-worker" id="btn-resume-worker" style="display:none;">
                    ▶ Resume Worker
                </button>
                <button class="btn btn-danger btn-worker" id="btn-stop-worker" style="display:none;">
                    ⏹ Stop Worker
                </button>
            </div>

            <!-- Configuration Form -->
            <form method='GET' action='#' name='event_form' id='event_form' enctype='multipart/form-data'>
                <h4>Event Configuration</h4>

                <label for='idevent'>Event:</label>
                <select id='idevent' name='idevent'>
                    <?= $this->Content_Events($evt); ?>
                </select>
                <input type="hidden" id='id_event' name='id_event' value="<?= $evt ?>">
                <br>
                <div id="event-dates">
                    <?= $this->Btn_Events($evt); ?>
                </div>
                <br>

                <label for='date_event'>Date</label>
                <input type='date' id='date_event' name='date_event' Value='<?= date('Y-m-d') ?>' required>
                <br>

                <label for='hour_event'>Start Time</label>
                <input type='time' id='hour_event' name='hour_event' Value='<?= date('H:i') ?>' required>
                <small class="text-muted">(Initial reference time)</small>
                <br>

                <label for='offset_event'>Warm-up</label>
                <input type='text' id='offset_event' name='offset_event' Value='15' size="2"> minutes
                <br>

                <label for='pitch_event'>Pitches</label>
                <input type='text' id='pitch_event' name='pitch_event' Value='4' size="1">
                <br>

                <label for='delay_event'>Refresh delay</label>
                <input type='text' id='delay_event' name='delay_event' Value='10' size="2"> seconds
                <br>
                <br>

                <hr>
                <h4>Live Monitoring</h4>
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
                Init()
                
                const now = new Date()
                const hours = String(now.getHours()).padStart(2, '0')
                const minutes = String(now.getMinutes()).padStart(2, '0')
                document.getElementById('hour_event').value = `${hours}:${minutes}`
            });
        </script>
<?php
    }
}

new Event($_GET);
