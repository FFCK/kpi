<?php
include_once('headers.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

$method = $_SERVER['REQUEST_METHOD'];
$url = trim($_GET['url'], '/');
$route = explode('/', $url);

set_headers($method);

if ($route[0] === 'staff') {
	token_verification();
	
	switch ($route[1]) {
		case 'test':
			methods(['GET']);
			staff_test($method, $route);
			break;
		default:
			return_404();
			exit;
		}
} else {
	switch ($route[0]) {
		case 'login':
			methods(['POST']);
			include_once('login.php');
			login($method, $route);
			break;
		default:
			return_404();
			exit;
	}
}

function staff_test ($method, $route) {
	return_200(['Result' => 'OK']);
}

