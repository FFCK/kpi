<?php
include_once('config/cache.php');

function StaffTestController($route, $params)
{
  return_200(['result' => 'OK']);
}

function GetTeamsController($route, $params)
{
  $event_id = (int) $route[1] ?? return_405();
  $force = $route[3] ?? false;
  $cacheArray = ($force !== 'force') ? json_cache_read('teams', false, 10) : false;
  if ($cacheArray) {
    return_200($cacheArray);
  }

  $myBdd = new MyBdd();
  $sql = "SELECT ce.Id team_id, ce.Libelle label, ce.Code_club club, ce.logo
    FROM kp_competition_equipe ce
    INNER JOIN kp_journee j ON (ce.Code_compet = j.Code_competition AND ce.Code_saison = j.Code_saison)
    INNER JOIN kp_evenement_journee ej ON (j.Id = ej.Id_journee)
    WHERE ej.Id_evenement = ?
    GROUP BY team_id
    ORDER BY club, label ";
  $stmt = $myBdd->pdo->prepare($sql);
  $stmt->execute([$event_id]);
  $resultArray = $stmt->fetchAll(PDO::FETCH_ASSOC);

  json_cache_write('teams', 0, $resultArray);

  return_200($resultArray);
}

function GetPlayersController($route, $params)
{
  // $event_id = (int) $route[1] ?? return_405();
  $team_id = (int) $route[3] ?? return_405();
  $force = $route[4] ?? false;
  $cacheArray = ($force !== 'force') ? json_cache_read('players', $team_id, 1) : false;
  if ($cacheArray) {
    return_200($cacheArray);
  }

  $myBdd = new MyBdd();
  $sql = "SELECT cej.Matric player_id, cej.Nom last_name, cej.Prenom first_name,
    cej.Sexe gender, cej.Numero num, cej.Capitaine cap,
    sc.kayak_status, sc.kayak_print, sc.vest_status, sc.vest_print, sc.helmet_status,
    sc.helmet_print, sc.paddle_count, sc.paddle_print, sc.comment
    FROM kp_competition_equipe_joueur cej
    LEFT OUTER JOIN kp_scrutineering sc ON (cej.Id_equipe = sc.id_equipe AND cej.Matric = sc.matric)
    WHERE cej.Id_equipe = ?
    AND cej.Capitaine != 'A'
    AND cej.Capitaine != 'X'
    ORDER BY FIELD(IF(cej.Capitaine='C', '-', IF(cej.Capitaine='', '-', cej.Capitaine)), '-', 'E', 'A', 'X'), num, last_name, first_name ";
  $stmt = $myBdd->pdo->prepare($sql);
  $stmt->execute([$team_id]);
  $resultArray = $stmt->fetchAll(PDO::FETCH_ASSOC);

  json_cache_write('players', $team_id, $resultArray);

  return_200($resultArray);
}

function PutPlayerController($route, $params)
{
  // $event_id = (int) $route[1] ?? return_405();
  $player_id = (int) $route[3] ?? return_405();
  $team_id = (int) $route[5] ?? return_405();
  $parameter = $route[6] ?? return_405();

  // Handle comment separately
  if ($parameter === 'comment') {
    return PutPlayerCommentController($route, $params);
  }

  if (!in_array($parameter, [
    'kayak_status',
    'vest_status',
    'helmet_status',
    'paddle_count'
  ])) {
    return_405();
  }

  $value = (int) $route[7] ?? return_405();
  // $force = $route[8] ?? false;
  $myBdd = new MyBdd();
  $sql = "INSERT INTO kp_scrutineering (id_equipe, matric, $parameter)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE $parameter = ? ";
  $stmt = $myBdd->pdo->prepare($sql);
  if ($stmt->execute([$team_id, $player_id, $value, $value])) {
    // TODO: log user action
    return_200($value);
  }
  return_401();
}

function PutPlayerCommentController($route, $params)
{
  // $event_id = (int) $route[1] ?? return_405();
  $player_id = (int) $route[3] ?? return_405();
  $team_id = (int) $route[5] ?? return_405();

  // Get comment from request body
  $input = json_decode(file_get_contents('php://input'), true);
  $comment = isset($input['comment']) ? htmlspecialchars(substr($input['comment'], 0, 255), ENT_QUOTES, 'UTF-8') : '';

  $myBdd = new MyBdd();
  $sql = "INSERT INTO kp_scrutineering (id_equipe, matric, comment)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE comment = ? ";
  $stmt = $myBdd->pdo->prepare($sql);
  if ($stmt->execute([$team_id, $player_id, $comment, $comment])) {
    // TODO: log user action
    return_200(['comment' => $comment]);
  }
  return_401();
}
