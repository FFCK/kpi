<?php
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');
include_once('headers.php');
include_once('controllers.php');

$method = $_SERVER['REQUEST_METHOD'];
$url = trim($_GET['url'], '/');
$route = explode('/', $url);

set_headers($method);

/**
 * Staff routes
 */
if ($route[0] === 'staff') {
	token_verification();
	
	switch ($route[1]) {
		case 'test':
			methods(['GET']);
			StaffTestController($method, $route);
			break;
		default:
			return_404();
			exit;
		}
/**
 * Public routes
 */
} else {
	switch ($route[0]) {
		case 'login':
			methods(['POST']);
			login($method, $route);
			break;
		case 'events':
			methods(['GET']);
			EventController($method, $route);
			break;
		case 'games':
			methods(['GET']);
			GamesController($method, $route);
			break;
		default:
			return_404();
			exit;
	}
}
