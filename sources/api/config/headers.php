<?php

function set_response_headers()
{
	$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

	// Allow specific origins or any .local domain in development
	if (
		$origin === "https://kayak-polo.info" ||
		$origin === "https://www.kayak-polo.info" ||
		$origin === "https://app.kayak-polo.info" ||
		$origin === "https://app.preprod.kayak-polo.info" ||
		// $origin === "http://localhost:8080" ||
		// $origin === "https://app.kpi.localhost" || // Nginx static app
		($origin && preg_match('/^https?:\/\/.*\.localhost$/', $origin)) // Allow all .localhost domains in dev
	) {
		header("Access-Control-Allow-Origin: $origin");
	}

	header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
	header('Access-Control-Allow-Credentials: true');
	header('Access-Control-Max-Age: 1000');
	header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, X-Requested-With, Authorization, Cache-Control, Pragma, Expires');
	header('Content-Type: application/json');

	if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
		http_response_code(200);
		exit;
	}
}
