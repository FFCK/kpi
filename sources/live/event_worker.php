#!/usr/bin/env php
<?php
/**
 * Worker d'événements - Processus en arrière-plan
 *
 * Ce script tourne en continu et génère automatiquement les fichiers cache
 * pour les événements sans dépendre du navigateur.
 *
 * Usage:
 *   php event_worker.php
 *
 * Le worker lit sa configuration depuis la table kp_event_worker_config
 * et peut être contrôlé via l'API api_worker.php
 */

// Permet l'exécution en ligne de commande
if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

// Configuration
error_reporting(E_ALL);
ini_set('display_errors', 1); // Afficher les erreurs dans les logs
ini_set('log_errors', 1);
ini_set('max_execution_time', 0); // Pas de limite de temps
ini_set('memory_limit', '256M');

// Gestionnaire d'erreurs personnalisé
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    $msg = "[" . date('Y-m-d H:i:s') . "] PHP Error [$errno]: $errstr in $errfile:$errline\n";
    error_log($msg, 3, __DIR__ . '/logs/event_worker.log');
    return false; // Laisser le gestionnaire par défaut s'exécuter aussi
});

// Gestionnaire d'erreurs fatales
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $msg = "[" . date('Y-m-d H:i:s') . "] FATAL ERROR: {$error['message']} in {$error['file']}:{$error['line']}\n";
        error_log($msg, 3, __DIR__ . '/logs/event_worker.log');
    }
});

// Chemin du script
$scriptPath = __DIR__;
chdir($scriptPath);

// Inclure les dépendances
require_once('../commun/MyBdd.php');
require_once('../commun/MyTools.php');
require_once('create_cache_match.php');

// Variables globales
$running = true;
$lastConfigCheck = 0;
$configCheckInterval = 5; // Vérifier la config toutes les 5 secondes
$eventStates = []; // Stocke le state de chaque événement (startTime, initialTime)

// Gestionnaire de signaux pour arrêt propre
if (function_exists('pcntl_signal')) {
    pcntl_signal(SIGTERM, function () use (&$running) {
        logMessage("SIGTERM received, shutting down gracefully...");
        $running = false;
    });
    pcntl_signal(SIGINT, function () use (&$running) {
        logMessage("SIGINT received, shutting down gracefully...");
        $running = false;
    });
}

logMessage("Event Worker started");
logMessage("PID: " . getmypid());

// Boucle principale
while ($running) {
    try {
        // Traiter les signaux
        if (function_exists('pcntl_signal_dispatch')) {
            pcntl_signal_dispatch();
        }

        $db = new MyBdd();

        // Récupérer tous les événements actifs
        $configs = getWorkerConfigs($db);

        if (empty($configs)) {
            // Pas de configuration, attendre
            if (time() % 60 == 0) { // Log toutes les minutes seulement
                logMessage("No active events, waiting...");
            }
            sleep(10);
            continue;
        }

        // Traiter chaque événement actif
        foreach ($configs as $config) {
            // Initialiser le state de l'événement si nécessaire
            if (!isset($eventStates[$config['id']])) {
                $eventStates[$config['id']] = [
                    'startTime' => microtime(true),
                    'initialTime' => strtotime($config['date_event'] . ' ' . $config['hour_event_initial']),
                    'id_event' => $config['id_event']
                ];
                logMessage("Event #{$config['id_event']} started - Date: {$config['date_event']} - Initial: {$config['hour_event_initial']}");
            }

            $state = $eventStates[$config['id']];

            // Calculer l'heure actuelle simulée
            $elapsedSeconds = microtime(true) - $state['startTime'];
            $currentSimulatedTime = $state['initialTime'] + $elapsedSeconds;
            $currentHourEvent = date('H:i', $currentSimulatedTime);

            // Ajouter l'offset (warm-up)
            $time = utyHHMM_To_MM($currentHourEvent);
            $time += $config['offset_event'];
            $hourEventWork = utyMM_To_HHMM($time);

            // Préparer le tableau des terrains
            $arrayPitchs = [];
            if ($config['pitch_event'] > 0) {
                for ($i = 1; $i <= $config['pitch_event']; $i++) {
                    $arrayPitchs[] = $i;
                }
            }

            // Générer les caches
            try {
                $cacheParams = ['cache' => '1'];
                $cache = new CacheMatch($cacheParams);

                $arrayResult = $cache->Event(
                    $db,
                    $config['id_event'],
                    $config['date_event'],
                    $hourEventWork,
                    $currentHourEvent,
                    $arrayPitchs
                );

                // Envoyer un heartbeat
                sendHeartbeat($config['id'], null);

                // Log résumé (toutes les 10 exécutions)
                if ($config['execution_count'] % 10 == 0) {
                    logMessage("Event #{$config['id_event']} - Execution #{$config['execution_count']} - Time: {$currentHourEvent}");
                }
            } catch (Exception $cacheException) {
                logMessage("ERROR for Event #{$config['id_event']}: " . $cacheException->getMessage());
                sendHeartbeat($config['id'], $cacheException->getMessage());
            }
        }

        // Nettoyer les states des événements qui ne sont plus actifs
        $activeIds = array_column($configs, 'id');
        foreach (array_keys($eventStates) as $stateId) {
            if (!in_array($stateId, $activeIds)) {
                logMessage("Event #{$eventStates[$stateId]['id_event']} stopped - Cleaning state");
                unset($eventStates[$stateId]);
            }
        }

        // Attendre un délai (utiliser le plus petit délai parmi tous les événements)
        $minDelay = min(array_column($configs, 'delay_event'));
        $delaySeconds = max(1, intval($minDelay));
        sleep($delaySeconds);

    } catch (Exception $e) {
        $errorMessage = "Error: " . $e->getMessage();
        logMessage($errorMessage);

        // Envoyer l'erreur via heartbeat
        if (isset($config['id'])) {
            sendHeartbeat($config['id'], $errorMessage);
        }

        // Attendre avant de réessayer
        sleep(10);
    }
}

logMessage("Event Worker stopped");

/**
 * Récupère toutes les configurations actives du worker
 */
function getWorkerConfigs($db)
{
    $sql = "SELECT * FROM kp_event_worker_config
            WHERE status IN ('running', 'paused')
            ORDER BY id_event ASC";
    $stmt = $db->pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Envoie un heartbeat à l'API pour indiquer que le worker est actif
 */
function sendHeartbeat($configId, $errorMessage = null)
{
    try {
        $db = new MyBdd();
        $sql = "UPDATE kp_event_worker_config
                SET last_execution = NOW(),
                    execution_count = execution_count + 1,
                    error_message = ?,
                    updated_at = NOW()
                WHERE id = ?";
        $stmt = $db->pdo->prepare($sql);
        $stmt->execute([$errorMessage, $configId]);
    } catch (Exception $e) {
        logMessage("Failed to send heartbeat: " . $e->getMessage());
    }
}

/**
 * Fonction de logging
 */
function logMessage($message)
{
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] {$message}\n";

    // Afficher dans la console
    echo $logMessage;

    // Écrire dans un fichier de log
    $logFile = __DIR__ . '/logs/event_worker.log';
    $logDir = dirname($logFile);

    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }

    @file_put_contents($logFile, $logMessage, FILE_APPEND);
}
