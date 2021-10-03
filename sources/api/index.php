<?php
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');
include_once('headers.php');
include_once('authentication.php');
include_once('publicControllers.php');
include_once('staffControllers.php');

$method = $_SERVER['REQUEST_METHOD'];
$url = $_GET['url']; // After htaccess url rewrite
$path = explode('/', trim($url, '/'));

set_response_headers($method);

/**
 * Routing
 */
if ($path[0] === 'staff') {
	// Staff routes
	$user = token_check($path[1]);

	// Event verifications...
	// $path[1]

	switch ($path[2]) {
		case 'test':
			methods(['GET']);
			StaffTestController($path);
			break;
		case 'teams':
			methods(['GET']);
			StaffTeamsController($path);
			break;
		case 'players':
			methods(['GET']);
			StaffPlayersController($path);
			break;
		case 'player':
			methods(['PUT']);
			StaffPlayerController($path);
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
