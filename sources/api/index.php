<?php
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');
include_once('headers.php');
include_once('controllers.php');

$method = $_SERVER['REQUEST_METHOD'];
$url = $_GET['url']; // After htaccess url rewrite
$path = explode('/', trim($url, '/'));

set_response_headers($method);

/**
 * Routing
 */
if ($path[0] === 'staff') {
	// Staff routes
	token_verification();

	switch ($path[1]) {
		case 'test':
			methods(['GET']);
			StaffTestController($path);
			break;
		default:
			return_404();
			exit;
	}
} else {
	// Public routes
	switch ($path[0]) {
		case 'login':
			methods(['POST']);
			login($path);
			break;
		case 'events':
			methods(['GET']);
			EventsController($path);
			break;
		case 'event':
			methods(['GET']);
			EventController($path);
			break;
		case 'games':
			methods(['GET']);
			GamesController($path);
			break;
		case 'charts':
			methods(['GET']);
			ChartsController($path);
			break;
		case 'stars':
			methods(['POST']);
			StarsController($path);
			break;
		default:
			return_404();
			exit;
	}
}
