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
$startTime = null;
$initialTime = null;

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

        // Récupérer la configuration
        $config = getWorkerConfig($db);

        if (!$config) {
            // Pas de configuration, attendre
            logMessage("No configuration found, waiting...");
            sleep(10);
            continue;
        }

        // Vérifier le statut
        if ($config['status'] === 'stopped') {
            logMessage("Worker stopped via configuration");
            break;
        }

        if ($config['status'] === 'paused') {
            logMessage("Worker paused, waiting...");
            sleep(5);
            continue;
        }

        // Le worker est en mode 'running'
        if ($startTime === null || $config['id'] !== ($lastConfigId ?? null)) {
            // Nouveau démarrage ou changement de config
            $startTime = microtime(true);
            // Combiner date et heure pour strtotime
            $initialTime = strtotime($config['date_event'] . ' ' . $config['hour_event_initial']);
            $lastConfigId = $config['id'];
            logMessage("Worker configuration loaded:");
            logMessage("  Event ID: " . $config['id_event']);
            logMessage("  Date: " . $config['date_event']);
            logMessage("  Initial hour: " . $config['hour_event_initial']);
            logMessage("  Offset: " . $config['offset_event'] . " minutes");
            logMessage("  Pitches: " . $config['pitch_event']);
            logMessage("  Refresh delay: " . $config['delay_event'] . " seconds");
            logMessage("  Initial timestamp: " . date('Y-m-d H:i:s', $initialTime));
        }

        // Calculer l'heure actuelle simulée
        $elapsedSeconds = microtime(true) - $startTime;
        $currentSimulatedTime = $initialTime + $elapsedSeconds;
        $currentHourEvent = date('H:i', $currentSimulatedTime);

        logMessage("Processing event {$config['id_event']} - Current time: {$currentHourEvent}");

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

        logMessage("Generating cache for " . count($arrayPitchs) . " pitches...");

        // Générer les caches
        try {
            // CacheMatch::__construct() requires parameter passed by reference
            $cacheParams = ['cache' => '1'];
            $cache = new CacheMatch($cacheParams);
            logMessage("CacheMatch object created");

            $arrayResult = $cache->Event(
                $db,
                $config['id_event'],
                $config['date_event'],
                $hourEventWork,
                $currentHourEvent,
                $arrayPitchs
            );

            logMessage("Cache generation completed - " . count($arrayResult) . " results returned");
        } catch (Exception $cacheException) {
            logMessage("ERROR in cache generation: " . $cacheException->getMessage());
            logMessage("Stack trace: " . $cacheException->getTraceAsString());
            throw $cacheException; // Re-throw pour être capturé par le catch externe
        }

        // Envoyer un heartbeat à l'API
        sendHeartbeat($config['id'], null);

        logMessage("Heartbeat sent");

        // Log de l'exécution
        $executionInfo = sprintf(
            "Cache generated - Count: %d - Time: %s (Work: %s) - Pitches: %d",
            $config['execution_count'] + 1,
            $currentHourEvent,
            $hourEventWork,
            count($arrayResult)
        );
        logMessage($executionInfo);

        // Log détaillé des terrains
        foreach ($arrayResult as $pitch) {
            $gameInfo = $pitch['game'] ? "Game #{$pitch['num']} (ID: {$pitch['game']})" : "Waiting";
            $nextInfo = $pitch['next']['id'] ? "Next #{$pitch['next']['num']} (ID: {$pitch['next']['id']})" : "No next";
            logMessage("  Pitch {$pitch['pitch']}: {$gameInfo} | {$nextInfo}");
        }

        // Attendre le délai configuré
        $delaySeconds = max(1, intval($config['delay_event']));
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
 * Récupère la configuration actuelle du worker
 */
function getWorkerConfig($db)
{
    $sql = "SELECT * FROM kp_event_worker_config ORDER BY id DESC LIMIT 1";
    $stmt = $db->pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
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
