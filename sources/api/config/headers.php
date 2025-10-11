<?php

function set_response_headers()
{
	$origin = &$_SERVER['HTTP_ORIGIN'];

	// Allow specific origins or any .local domain in development
	if (
		$origin === "https://kayak-polo.info" ||
		$origin === "https://www.kayak-polo.info" ||
		$origin === "https://app2.kayak-polo.info" ||
		$origin === "http://localhost:9000" ||
		$origin === "http://localhost:9001" ||
		$origin === "http://localhost:9002" ||
		$origin === "http://localhost:3002" ||
		$origin === "https://kpi-node.local" ||
		preg_match('/^https?:\/\/.*\.local$/', $origin) // Allow all .local domains in dev
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
