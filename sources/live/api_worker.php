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
            // Récupère l'état de tous les événements actifs
            $configs = getWorkerConfigs($db);
            return_200([
                'status' => 'success',
                'data' => $configs,
                'count' => count($configs)
            ]);
            break;

        case 'start':
            // Démarre un nouvel événement dans le worker
            $id_event = utyGetPost('id_event', false);
            $date_event = utyGetPost('date_event', false);
            $hour_event = utyGetPost('hour_event', false);
            $offset_event = utyGetPost('offset_event', 15);
            $pitch_event = utyGetPost('pitch_event', 4);
            $delay_event = utyGetPost('delay_event', 10);

            if (!$id_event || !$date_event || !$hour_event) {
                return_400(['status' => 'error', 'message' => 'Missing required parameters: id_event, date_event, hour_event']);
            }

            // Vérifier si cet événement est déjà actif
            $sql = "SELECT * FROM kp_event_worker_config
                    WHERE id_event = ? AND status IN ('running', 'paused')";
            $stmt = $db->pdo->prepare($sql);
            $stmt->execute([$id_event]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                // Mettre à jour l'événement existant
                $sql = "UPDATE kp_event_worker_config
                        SET date_event = ?,
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
                    $date_event,
                    $hour_event,
                    $hour_event,
                    $offset_event,
                    $pitch_event,
                    $delay_event,
                    $existing['id']
                ]);
                $configId = $existing['id'];
            } else {
                // Créer une nouvelle config pour cet événement
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
                $configId = $db->pdo->lastInsertId();
            }

            // Récupérer la config créée/mise à jour
            $sql = "SELECT * FROM kp_event_worker_config WHERE id = ?";
            $stmt = $db->pdo->prepare($sql);
            $stmt->execute([$configId]);
            $config = $stmt->fetch(PDO::FETCH_ASSOC);

            return_200([
                'status' => 'success',
                'message' => 'Event worker started successfully',
                'data' => enrichConfig($config)
            ]);
            break;

        case 'stop':
            // Arrête un ou tous les événements
            $id_event = utyGetPost('id_event', null);

            if ($id_event) {
                $sql = "UPDATE kp_event_worker_config
                        SET status = 'stopped', updated_at = NOW()
                        WHERE id_event = ? AND status IN ('running', 'paused')";
                $stmt = $db->pdo->prepare($sql);
                $stmt->execute([$id_event]);
                $message = "Event #$id_event stopped successfully";
            } else {
                $sql = "UPDATE kp_event_worker_config
                        SET status = 'stopped', updated_at = NOW()
                        WHERE status IN ('running', 'paused')";
                $stmt = $db->pdo->prepare($sql);
                $stmt->execute();
                $message = "All events stopped successfully";
            }

            $configs = getWorkerConfigs($db);
            return_200([
                'status' => 'success',
                'message' => $message,
                'data' => $configs
            ]);
            break;

        case 'pause':
            // Met en pause un ou tous les événements
            $id_event = utyGetPost('id_event', null);

            if ($id_event) {
                $sql = "UPDATE kp_event_worker_config
                        SET status = 'paused', updated_at = NOW()
                        WHERE id_event = ? AND status = 'running'";
                $stmt = $db->pdo->prepare($sql);
                $stmt->execute([$id_event]);
                $message = "Event #$id_event paused successfully";
            } else {
                $sql = "UPDATE kp_event_worker_config
                        SET status = 'paused', updated_at = NOW()
                        WHERE status = 'running'";
                $stmt = $db->pdo->prepare($sql);
                $stmt->execute();
                $message = "All events paused successfully";
            }

            $configs = getWorkerConfigs($db);
            return_200([
                'status' => 'success',
                'message' => $message,
                'data' => $configs
            ]);
            break;

        case 'resume':
            // Reprend un ou tous les événements
            $id_event = utyGetPost('id_event', null);

            if ($id_event) {
                $sql = "UPDATE kp_event_worker_config
                        SET status = 'running', updated_at = NOW()
                        WHERE id_event = ? AND status = 'paused'";
                $stmt = $db->pdo->prepare($sql);
                $stmt->execute([$id_event]);
                $message = "Event #$id_event resumed successfully";
            } else {
                $sql = "UPDATE kp_event_worker_config
                        SET status = 'running', updated_at = NOW()
                        WHERE status = 'paused'";
                $stmt = $db->pdo->prepare($sql);
                $stmt->execute();
                $message = "All events resumed successfully";
            }

            $configs = getWorkerConfigs($db);
            return_200([
                'status' => 'success',
                'message' => $message,
                'data' => $configs
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
 * Récupère toutes les configurations du worker (tous les événements)
 */
function getWorkerConfigs($db)
{
    $sql = "SELECT * FROM kp_event_worker_config
            WHERE status IN ('running', 'paused')
            ORDER BY id_event ASC";
    $stmt = $db->pdo->prepare($sql);
    $stmt->execute();
    $configs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Enrichir chaque config avec les informations calculées
    $enrichedConfigs = [];
    foreach ($configs as $config) {
        $enrichedConfigs[] = enrichConfig($config);
    }

    return $enrichedConfigs;
}

/**
 * Enrichit une configuration avec des informations calculées
 */
function enrichConfig($config)
{
    if (!$config) {
        return null;
    }

    // Ajouter des informations calculées
    $config['is_running'] = $config['status'] === 'running';
    $config['is_paused'] = $config['status'] === 'paused';
    $config['is_stopped'] = $config['status'] === 'stopped';

    // Calculer l'heure actuelle simulée à partir du nombre d'exécutions
    // Plus fiable que d'utiliser created_at car le worker peut redémarrer
    if ($config['status'] === 'running' || $config['status'] === 'paused') {
        $initialTime = strtotime($config['date_event'] . ' ' . $config['hour_event_initial']);
        $elapsedSeconds = $config['execution_count'] * $config['delay_event'];
        $config['current_simulated_time'] = date('H:i:s', $initialTime + $elapsedSeconds);
    } else {
        $config['current_simulated_time'] = $config['hour_event'];
    }

    // Calculer le temps depuis la dernière exécution (timezone local du serveur)
    if ($config['last_execution']) {
        $last = new DateTime($config['last_execution']);
        $now = new DateTime('now');
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
