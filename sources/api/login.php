<?php
include_once('headers.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

set_headers();

$authentication_result = user_authentication($purifier);

if ($authentication_result) {
	http_response_code(200);
	echo json_encode($authentication_result);
	exit;
// } else {
// 	header('HTTP/1.0 401 Unauthorized');
// 	echo json_encode('Unauthorized');
// 	exit;
}

