<?php

function routing($path)
{
  $params = [];
  $routes = [
    'public' => [
      'login' => ['POST', 'login'],
      'events' => ['GET', 'GetEventsController'],
      'event' => ['GET', 'GetEventController'],
      'games' => ['GET', 'GetGamesController'],
      'charts' => ['GET', 'GetChartsController'],
      'stars' => ['GET', 'GetStarsController'],
      'rating' => ['POST', 'PostRatingController']
    ],
    'staff' => [
      'test' => ['GET', 'StaffTestController'],
      'teams' => ['GET', 'GetTeamsController'],
      'players' => ['GET', 'GetPlayersController'],
      'player' => ['PUT', 'PutPlayerController']
    ],
    'report' => [
      'game' => ['GET', 'GetGameController']
    ]
  ];

  if (in_array($path[0], ['staff'])) {
    include_once('config/authentication.php');
    include_once('controllers/staffControllers.php');
    $params['user'] = get_user($path[1]);
    $route = $routes[$path[0]];
    $path_name = $path[2];
  } elseif (in_array($path[0], ['report'])) {
    include_once('config/authentication.php');
    include_once('controllers/reportControllers.php');
    $params['user'] = get_user($path[1]);
    $route = $routes[$path[0]];
    $path_name = $path[2];
  } else {
    include_once('controllers/publicControllers.php');
    $route = $routes['public'];
    $path_name = $path[0];
  }

  if (array_key_exists($path_name, $route)) {
    methods([$route[$path_name][0]]);
    $controller = $route[$path_name][1];
    if (function_exists($controller)) {
      $controller($path, $params);
    } else {
      return_404();
    }
  } else {
    return_404();
  }
}
