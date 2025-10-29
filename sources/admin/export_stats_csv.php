<?php
/**
 * Export des statistiques en CSV via OpenSpout
 *
 * Remplace upload_csv.php (obsolète avec warnings PHP 8.4)
 * Utilise OpenSpout v4.32.0 pour génération CSV propre
 *
 * @author Laurent Garrigue / Claude Code
 * @date 2025-10-29
 */

require_once('../vendor/autoload.php');
include_once('../commun/MyTools.php');

use OpenSpout\Writer\CSV\Writer;
use OpenSpout\Common\Entity\Row;

// Démarrage session
if(!isset($_SESSION)) {
    session_start();
}

// Vérification action
if (utyGetGet('action') !== 'export') {
    http_response_code(400);
    exit('Action invalide');
}

// Récupération données depuis session
$arrayStats = utyGetSession('arrayStats');

if (empty($arrayStats) || !is_array($arrayStats)) {
    http_response_code(404);
    exit('Aucune statistique disponible pour export');
}

try {
    // Création fichier temporaire
    $temp_file = tempnam(sys_get_temp_dir(), 'stats_') . '.csv';

    // Configuration writer CSV OpenSpout
    $writer = new Writer();
    $writer->openToFile($temp_file);

    // En-têtes (première ligne du tableau de stats)
    $headers = array_keys($arrayStats[0]);
    $headerRow = Row::fromValues($headers);
    $writer->addRow($headerRow);

    // Données
    foreach ($arrayStats as $stat) {
        $dataRow = Row::fromValues(array_values($stat));
        $writer->addRow($dataRow);
    }

    $writer->close();

    // Envoi du fichier au navigateur (headers propres, pas de warnings)
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="stats_' . date('Y-m-d_His') . '.csv"');
    header('Content-Length: ' . filesize($temp_file));
    header('Pragma: no-cache');
    header('Expires: 0');

    // Envoi du contenu
    readfile($temp_file);

    // Nettoyage
    unlink($temp_file);

} catch (Exception $e) {
    http_response_code(500);
    error_log('Erreur export CSV stats: ' . $e->getMessage());
    exit('Erreur lors de la génération du fichier CSV');
}
