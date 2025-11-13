<?php
include_once('base.php');
include_once('create_cache_match.php');
include_once('page.php');
include_once('../commun/MyTools.php');

if(!isset($_SESSION)) {
	session_start();
}

class Event extends MyPageSecure
{
    function __construct($arrayParam)
    {
        parent::__construct($arrayParam, 1); // Niveau 1 = authentification requise
    }

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
                .config-panel {
                    background: #f9f9f9;
                    padding: 20px;
                    border-radius: 8px;
                    border: 1px solid #ddd;
                    margin: 20px 0;
                }
                .config-panel .form-group {
                    margin-bottom: 15px;
                }
                .config-panel label {
                    font-weight: bold;
                    display: block;
                    margin-bottom: 5px;
                }
                .config-panel input[type="date"],
                .config-panel input[type="time"],
                .config-panel input[type="text"],
                .config-panel select {
                    width: 100%;
                    max-width: 400px;
                    padding: 8px;
                    border: 1px solid #ccc;
                    border-radius: 4px;
                }
                .config-panel input[type="text"][size="1"],
                .config-panel input[type="text"][size="2"] {
                    width: auto;
                }
                .btn_date_evt {
                    margin: 3px;
                    padding: 5px 10px;
                    background: #007bff;
                    color: white;
                    border: none;
                    border-radius: 3px;
                    cursor: pointer;
                }
                .btn_date_evt:hover {
                    background: #0056b3;
                }
                .status-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .modal {
                    display: none;
                    position: fixed;
                    z-index: 1000;
                    left: 0;
                    top: 0;
                    width: 100%;
                    height: 100%;
                    overflow: auto;
                    background-color: rgba(0,0,0,0.4);
                }
                .modal-content {
                    background-color: #fefefe;
                    margin: 5% auto;
                    padding: 20px;
                    border: 1px solid #888;
                    width: 90%;
                    max-width: 900px;
                    border-radius: 8px;
                }
                .close {
                    color: #aaa;
                    float: right;
                    font-size: 28px;
                    font-weight: bold;
                    cursor: pointer;
                }
                .close:hover,
                .close:focus {
                    color: black;
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
                    <div class="status-header">
                        <h3 style="margin: 0;">
                            <span class="status-indicator stopped" id="status-indicator"></span>
                            <span id="status-text">Worker Status: Checking...</span>
                        </h3>
                        <button class="btn btn-danger" id="btn-stop-all" style="display:none;">
                            ⏹ Stop All
                        </button>
                    </div>
                    <div class="worker-info" id="worker-info">
                        Loading worker status...
                    </div>
                </div>
            </div>

            <!-- Configuration Form -->
            <div class="config-panel">
                <h4>Event Configuration</h4>
                <form method='GET' action='#' name='event_form' id='event_form' enctype='multipart/form-data'>

                    <div class="form-group">
                        <label for='idevent'>Event:</label>
                        <select id='idevent' name='idevent' class="form-control" style="max-width: 400px;">
                            <?= $this->Content_Events($evt); ?>
                        </select>
                        <input type="hidden" id='id_event' name='id_event' value="<?= $evt ?>">
                    </div>

                    <div class="form-group">
                        <label>Quick date selection:</label>
                        <div id="event-dates">
                            <?= $this->Btn_Events($evt); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for='date_event'>Date:</label>
                        <input type='date' id='date_event' name='date_event' class="form-control" Value='<?= date('Y-m-d') ?>' required>
                    </div>

                    <div class="form-group">
                        <label for='hour_event'>Start Time:</label>
                        <input type='time' id='hour_event' name='hour_event' class="form-control" Value='<?= date('H:i') ?>' required>
                        <small class="text-muted">(Initial reference time)</small>
                    </div>

                    <div class="form-group">
                        <label for='offset_event'>Warm-up:</label>
                        <input type='text' id='offset_event' name='offset_event' class="form-control" Value='15' size="2"> minutes
                    </div>

                    <div class="form-group">
                        <label for='pitch_event'>Pitches:</label>
                        <input type='text' id='pitch_event' name='pitch_event' class="form-control" Value='4' size="1">
                    </div>

                    <div class="form-group">
                        <label for='delay_event'>Refresh delay:</label>
                        <input type='text' id='delay_event' name='delay_event' class="form-control" Value='10' size="2"> seconds
                    </div>

                    <div class="form-group">
                        <button type="button" class="btn btn-success btn-lg" id="btn-start-worker">
                            ▶ Start Worker
                        </button>
                    </div>

                </form>
            </div>

        </div>

        <!-- Modal pour Live Monitoring -->
        <div id="monitoring-modal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h3>Live Monitoring - Event <span id="modal-event-id"></span></h3>
                <div id="modal-monitoring-content">
                    <p>Loading...</p>
                </div>
            </div>
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
