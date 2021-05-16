<?php
$origin = $_SERVER['HTTP_ORIGIN'];

if ($origin === "https://kayak-polo.info" || 
	$origin === "https://www.kayak-polo.info" || 
	$origin === "http://localhost:9000")
{  
	header("Access-Control-Allow-Origin: $origin");
}

header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization');
