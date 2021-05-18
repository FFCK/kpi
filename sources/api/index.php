<?php
include_once('headers.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

set_headers();

$token_verification = token_verification();

if ($token_verification) {
	http_response_code(200);
	echo json_encode('OK');
	exit;
} else {
    http_response_code(200);
	echo json_encode('KO');
	exit;
}

