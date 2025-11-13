<?php
/**
 * API REST pour contrôler le worker d'événements
 * Permet de démarrer/arrêter/configurer le worker qui génère les caches automatiquement
 */

include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

header('Content-Type: application/json');

if (!isset($_SESSION)) {
    session_start();
}

$db = new MyBdd();
$action = utyGetGet('action', utyGetPost('action', 'status'));

try {
    switch ($action) {
        case 'status':
            // Récupère l'état actuel du worker
            $config = getWorkerConfig($db);
            return_200([
                'status' => 'success',
                'data' => $config
            ]);
            break;

        case 'start':
            // Démarre ou redémarre le worker avec les paramètres fournis
            $id_event = utyGetPost('id_event', false);
            $date_event = utyGetPost('date_event', false);
            $hour_event = utyGetPost('hour_event', false);
            $offset_event = utyGetPost('offset_event', 15);
            $pitch_event = utyGetPost('pitch_event', 4);
            $delay_event = utyGetPost('delay_event', 10);

            if (!$id_event || !$date_event || !$hour_event) {
                return_400(['status' => 'error', 'message' => 'Missing required parameters: id_event, date_event, hour_event']);
            }

            // Vérifier si une config existe déjà
            $existing = getWorkerConfig($db);

            if ($existing) {
                // Mettre à jour la config existante
                $sql = "UPDATE kp_event_worker_config
                        SET id_event = ?,
                            date_event = ?,
                            hour_event = ?,
                            hour_event_initial = ?,
                            offset_event = ?,
                            pitch_event = ?,
                            delay_event = ?,
                            status = 'running',
                            error_message = NULL,
                            updated_at = NOW()
                        WHERE id = ?";
                $stmt = $db->pdo->prepare($sql);
                $stmt->execute([
                    $id_event,
                    $date_event,
                    $hour_event,
                    $hour_event,
                    $offset_event,
                    $pitch_event,
                    $delay_event,
                    $existing['id']
                ]);
            } else {
                // Créer une nouvelle config
                $sql = "INSERT INTO kp_event_worker_config
                        (id_event, date_event, hour_event, hour_event_initial, offset_event, pitch_event, delay_event, status)
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'running')";
                $stmt = $db->pdo->prepare($sql);
                $stmt->execute([
                    $id_event,
                    $date_event,
                    $hour_event,
                    $hour_event,
                    $offset_event,
                    $pitch_event,
                    $delay_event
                ]);
            }

            $config = getWorkerConfig($db);
            return_200([
                'status' => 'success',
                'message' => 'Worker started successfully',
                'data' => $config
            ]);
            break;

        case 'stop':
            // Arrête le worker
            $sql = "UPDATE kp_event_worker_config
                    SET status = 'stopped',
                        updated_at = NOW()
                    WHERE status IN ('running', 'paused')";
            $stmt = $db->pdo->prepare($sql);
            $stmt->execute();

            $config = getWorkerConfig($db);
            return_200([
                'status' => 'success',
                'message' => 'Worker stopped successfully',
                'data' => $config
            ]);
            break;

        case 'pause':
            // Met en pause le worker
            $sql = "UPDATE kp_event_worker_config
                    SET status = 'paused',
                        updated_at = NOW()
                    WHERE status = 'running'";
            $stmt = $db->pdo->prepare($sql);
            $stmt->execute();

            $config = getWorkerConfig($db);
            return_200([
                'status' => 'success',
                'message' => 'Worker paused successfully',
                'data' => $config
            ]);
            break;

        case 'resume':
            // Reprend le worker après une pause
            $sql = "UPDATE kp_event_worker_config
                    SET status = 'running',
                        updated_at = NOW()
                    WHERE status = 'paused'";
            $stmt = $db->pdo->prepare($sql);
            $stmt->execute();

            $config = getWorkerConfig($db);
            return_200([
                'status' => 'success',
                'message' => 'Worker resumed successfully',
                'data' => $config
            ]);
            break;

        case 'update':
            // Met à jour les paramètres sans redémarrer
            $offset_event = utyGetPost('offset_event', null);
            $pitch_event = utyGetPost('pitch_event', null);
            $delay_event = utyGetPost('delay_event', null);

            $updates = [];
            $params = [];

            if ($offset_event !== null) {
                $updates[] = "offset_event = ?";
                $params[] = $offset_event;
            }
            if ($pitch_event !== null) {
                $updates[] = "pitch_event = ?";
                $params[] = $pitch_event;
            }
            if ($delay_event !== null) {
                $updates[] = "delay_event = ?";
                $params[] = $delay_event;
            }

            if (count($updates) > 0) {
                $sql = "UPDATE kp_event_worker_config
                        SET " . implode(', ', $updates) . ", updated_at = NOW()
                        WHERE status IN ('running', 'paused')";
                $stmt = $db->pdo->prepare($sql);
                $stmt->execute($params);

                $config = getWorkerConfig($db);
                return_200([
                    'status' => 'success',
                    'message' => 'Configuration updated successfully',
                    'data' => $config
                ]);
            } else {
                return_400([
                    'status' => 'error',
                    'message' => 'No parameters to update'
                ]);
            }
            break;

        case 'heartbeat':
            // Met à jour le timestamp de dernière exécution et incrémente le compteur
            $error_message = utyGetPost('error_message', null);

            $sql = "UPDATE kp_event_worker_config
                    SET last_execution = NOW(),
                        execution_count = execution_count + 1,
                        error_message = ?,
                        updated_at = NOW()
                    WHERE status = 'running'";
            $stmt = $db->pdo->prepare($sql);
            $stmt->execute([$error_message]);

            return_200([
                'status' => 'success',
                'message' => 'Heartbeat recorded'
            ]);
            break;

        default:
            return_400([
                'status' => 'error',
                'message' => 'Invalid action: ' . $action
            ]);
    }
} catch (Exception $e) {
    return_500([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

/**
 * Récupère la configuration actuelle du worker
 */
function getWorkerConfig($db)
{
    $sql = "SELECT * FROM kp_event_worker_config ORDER BY id DESC LIMIT 1";
    $stmt = $db->pdo->prepare($sql);
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$config) {
        return null;
    }

    // Ajouter des informations calculées
    $config['is_running'] = $config['status'] === 'running';
    $config['is_paused'] = $config['status'] === 'paused';
    $config['is_stopped'] = $config['status'] === 'stopped';

    // Calculer l'heure actuelle simulée (si running)
    if ($config['status'] === 'running' && $config['created_at']) {
        $startTime = strtotime($config['created_at']);
        $initialTime = strtotime($config['date_event'] . ' ' . $config['hour_event_initial']);
        $elapsedSeconds = time() - $startTime;
        $currentSimulatedTime = $initialTime + $elapsedSeconds;
        $config['current_simulated_time'] = date('H:i:s', $currentSimulatedTime);
    } else {
        $config['current_simulated_time'] = $config['hour_event'];
    }

    // Calculer le temps depuis la dernière exécution (en UTC)
    if ($config['last_execution']) {
        $last = new DateTime($config['last_execution'], new DateTimeZone('UTC'));
        $now = new DateTime('now', new DateTimeZone('UTC'));
        $diff = $now->getTimestamp() - $last->getTimestamp();
        $config['seconds_since_last_execution'] = $diff;
        $config['is_healthy'] = $diff < ($config['delay_event'] * 3); // Considéré sain si < 3x le délai
    } else {
        $config['seconds_since_last_execution'] = null;
        $config['is_healthy'] = true;
    }

    return $config;
}

/**
 * Helper function for 500 errors (not in MyTools.php)
 */
function return_500($data)
{
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}
