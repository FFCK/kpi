<?php
/**
 * Auto-prepend file for PHP
 * Automatically included before every PHP script execution
 * Handles CORS headers for all endpoints (API, custom files, api2)
 */

// Only set CORS headers for requests with Origin header (AJAX/fetch requests)
if (isset($_SERVER['HTTP_ORIGIN'])) {
    $origin = $_SERVER['HTTP_ORIGIN'];

    // Allow specific origins or any .localhost domain in development
    if (
        $origin === "https://kayak-polo.info" ||
        $origin === "https://www.kayak-polo.info" ||
        $origin === "https://app2.kayak-polo.info" ||
        $origin === "http://localhost:9000" ||
        $origin === "http://localhost:9001" ||
        $origin === "http://localhost:9002" ||
        $origin === "http://localhost:3002" ||
        $origin === "https://kpi-node.localhost" ||
        $origin === "https://app.kpi.localhost" || // Nginx static app
        ($origin && preg_match('/^https?:\/\/.*\.localhost$/', $origin)) // Allow all .localhost domains in dev
    ) {
        header("Access-Control-Allow-Origin: $origin");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 1000');
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS, PATCH');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, X-Requested-With, Authorization, Cache-Control, Pragma, Expires');
    }

    // Handle preflight OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}
