<?php

function set_response_headers($method)
{
	$origin = &$_SERVER['HTTP_ORIGIN'];

	if (
		$origin === "https://kayak-polo.info" ||
		$origin === "https://www.kayak-polo.info" ||
		$origin === "http://localhost:9000"
	) {
		header("Access-Control-Allow-Origin: $origin");
	}

	header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
	header('Access-Control-Allow-Credentials: true');
	header('Access-Control-Max-Age: 1000');
	header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, X-Requested-With, Authorization, Cache-Control, Pragma, Expires');
	header('Content-Type: application/json');

	if ($method === 'OPTIONS') {
		http_response_code(200);
		exit;
	}
}

function methods($methods_array)
{
	if (!in_array($_SERVER['REQUEST_METHOD'], $methods_array)) {
		return_405();
		exit;
	}
	return;
}

function return_401()
{
	header('HTTP/1.0 401 Unauthorized');
	echo json_encode('Unauthorized');
	exit;
}

function return_404()
{
	header('HTTP/1.0 404 Not Found');
	echo json_encode('Not Found');
	exit;
}

function return_405()
{
	header('HTTP/1.0 405 Method Not Allowed');
	echo json_encode('Method Not Allowed');
	exit;
}

function return_200($result, $convert_to_json = true)
{
	http_response_code(200);
	$result = ($convert_to_json) ? json_encode($result) : $result;
	echo $result;
	exit;
}

/**
 * Renvoie la data du fichier json s'il a moins de $cache_duration minutes d'ancienneté
 */
function json_cache_read($cache_type, $cache_id, $cache_duration = 5)
{
	$cache_id = $cache_id > 0 ? '_' . $cache_id : '';
	$file_name = 'files/' . $cache_type . $cache_id . '.json';
	try {
		$file_content = json_decode(file_get_contents($file_name), false);
	} catch (Exception $e) {
		return false;
	}
	if ($file_content) {
		$file_date = $file_content->created_at;
		$file_date = DateTime::createFromFormat('Y-m-d H:i:s', $file_date);
		$file_date->add(new DateInterval('PT' . $cache_duration . 'M'));
		$now = new DateTime("now");
		if ($now <= $file_date) {
			return $file_content->data;
		}
	}

	return false;
}

/**
 * Ecrit la data et la date de création dans un fichier json
 */
function json_cache_write($cache_type, $cache_id, $cache_content)
{
	$cache_id = $cache_id > 0 ? '_' . $cache_id : '';
	$file_name = 'files/' . $cache_type . $cache_id . '.json';
	$file_content = json_encode([
		'created_at' => date('Y-m-d H:i:s'),
		'data' => $cache_content
	]);
	file_put_contents($file_name, $file_content);
}
