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
			GetTeamsController($path);
			break;
		case 'players':
			methods(['GET']);
			GetPlayersController($path);
			break;
		case 'player':
			methods(['PUT']);
			PutPlayerController($path);
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
			GetEventsController($path);
			break;
		case 'event':
			methods(['GET']);
			GetEventController($path);
			break;
		case 'games':
			methods(['GET']);
			GetGamesController($path);
			break;
		case 'charts':
			methods(['GET']);
			GetChartsController($path);
			break;
		case 'stars':
			methods(['POST']);
			PostStarsController($path);
			break;
		default:
			return_404();
			exit;
	}
}
