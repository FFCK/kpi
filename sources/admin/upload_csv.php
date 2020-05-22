<?php
include_once('../commun/MyTools.php');
session_start();

// Export to CSV
if (utyGetGet('action') == 'export') {
    $arrayStats = utyGetSession('arrayStats');
    $headers = array_keys($arrayStats[0]);
    $fp = fopen('php://output', 'w');

    if ($fp) {     
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="stats.csv"');
        header('Pragma: no-cache');    
        header('Expires: 0');
        fputcsv($fp, $headers); 
        foreach ($arrayStats as $stat) {
           fputcsv($fp, array_values($stat)); 
        }
        die();
    }     
}