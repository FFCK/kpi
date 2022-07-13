<?php
include_once('config/cache.php');
include_once('../live/create_cache_match.php');
include_once('../commun/MyTools.php');

function login($route)
{
  include_once('config/authentication.php');
  $authentication_result = user_authentication();
  return_200($authentication_result);
}

function PutEventNetworkController($route, $params)
{
  $event_id = (int) $route[2];
  $network = file_get_contents('php://input');

  $fileName = 'event' . $event_id . '_network.json';

  if (!file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/live/cache/" . $fileName, $network)) {
    return_405();
  }

  return_200(true);
}

function PutGameParamController($route, $params)
{
  $idMatch = (int) $route[2];
  $data = json_decode(file_get_contents('php://input'));
  if (!in_array(
    $data->param,
    ['Statut', 'Periode', 'ScoreA', 'ScoreB', 'ScoreDetailA', 'ScoreDetailB', 'Heure_fin']
  )) {
    return_401();
  }

  $myBdd = new MyBdd();
  $sql = "UPDATE kp_match 
		SET $data->param = ? 
		WHERE Id = ? 
    AND Validation != 'O' ";
  $stmt = $myBdd->pdo->prepare($sql);
  $result = $stmt->execute([$data->value, $idMatch]);
  if ($result) {
    // Creation du Cache...
    $cMatch = new CacheMatch($_GET);
    if (in_array($data->param, ['Statut'])) {
      $cMatch->MatchGlobal($myBdd, $idMatch);
    }
    if (in_array($data->param, ['ScoreA', 'ScoreB', 'ScoreDetailA', 'ScoreDetailB', 'Periode'])) {
      $cMatch->MatchScore($myBdd, $idMatch);
    }
    return_200();
  } else {
    return_400();
  }
}

function PutGameEventController($route, $params)
{
  $idMatch = (int) $route[2];
  $data = json_decode(file_get_contents('php://input'));

  $myBdd = new MyBdd();
  $sql = "SELECT COUNT(Id)
    FROM kp_match 
    WHERE Id = ? 
    AND Validation != 'O' ";
  $stmt = $myBdd->pdo->prepare($sql);
  $stmt->execute([$idMatch]);
  $result = $stmt->fetchColumn();
  if ($result != 1) {
    return_400('Game locked : ' . $result);
  }

  if ($data->params->action === 'add') {

    if (!$data->params->uid) {
      $data->params->uid = str_replace('-', '', gen_uuid());
    }

    $sql = "INSERT INTO kp_match_detail (Id, Id_match, Periode, Temps, Id_evt_match,
      Competiteur, Numero, Equipe_A_B, motif)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ";
    $stmt = $myBdd->pdo->prepare($sql);
    $result = $stmt->execute([
      $data->params->uid, $idMatch, $data->params->period, $data->params->tpsJeu, $data->params->code,
      $data->params->player, $data->params->number, $data->params->team, $data->params->reason
    ]);
  } elseif ($data->params->action === 'remove') {
    $myBdd = new MyBdd();
    $sql = "DELETE FROM kp_match_detail 
      WHERE Id_match = ?
      AND Periode = ?
      AND Competiteur = ?
      AND Id_evt_match = ?
      ORDER BY date_insert DESC
      LIMIT 1 ";
    $stmt = $myBdd->pdo->prepare($sql);
    $result = $stmt->execute([
      $idMatch, $data->params->period, $data->params->player, $data->params->code
    ]);
  }
  if ($result) {
    // Creation du Cache...
    $cMatch = new CacheMatch($_GET);
    $cMatch->MatchScore($myBdd, $idMatch);
    return_200();
  } else {
    return_400();
  }
}

function PutPlayerStatusController($route, $params)
{
  $idMatch = (int) $route[2];
  $data = json_decode(file_get_contents('php://input'));

  $myBdd = new MyBdd();
  $sql = "SELECT COUNT(Id)
    FROM kp_match 
    WHERE Id = ? 
    AND Validation != 'O' ";
  $stmt = $myBdd->pdo->prepare($sql);
  $stmt->execute([$idMatch]);
  $result = $stmt->fetchColumn();
  if ($result != 1) {
    return_400('Game locked : ' . $result);
  }

  if ($data->params->team && $data->params->player && $data->params->status) {
    $sql = "UPDATE kp_match_joueur
      SET Capitaine = ?
      WHERE Id_match = ?
      AND Equipe = ?
      AND Matric = ? ";
    $stmt = $myBdd->pdo->prepare($sql);
    $result = $stmt->execute([
      $data->params->status, $idMatch, $data->params->team, $data->params->player
    ]);
  }
  if (isset($result) && $result) {
    // Creation du Cache...
    $cMatch = new CacheMatch($_GET);
    $cMatch->MatchGlobal($myBdd, $idMatch);
    return_200();
  } else {
    return_400();
  }
}

function PutGameTimerController($route, $params)
{
  $idMatch = (int) $route[2];
  $data = json_decode(file_get_contents('php://input'));

  if (!in_array($data->params->action, ['run', 'stop', 'RAZ'])) {
    return_401();
  }

  $myBdd = new MyBdd();
  $sql = "SELECT COUNT(Id)
    FROM kp_match 
    WHERE Id = ? 
    AND Validation != 'O' ";
  $stmt = $myBdd->pdo->prepare($sql);
  $stmt->execute([$idMatch]);
  $result = $stmt->fetchColumn();
  if ($result != 1) {
    return_400('Game locked : ' . $result);
  }

  if ($data->params->action === 'RAZ') {
    $sql = "DELETE FROM kp_chrono 
      WHERE IdMatch = ? ";
    $stmt = $myBdd->pdo->prepare($sql);
    $result = $stmt->execute(array($idMatch));
  } else {
    $data->params->startTimeServer = time() % 86400;
    $sql = "REPLACE kp_chrono 
      SET IdMatch = ?, 
      `action` = ?, 
      start_time = ?, 
      start_time_server = ?, 
      run_time = ?, 
      max_time = ?  ";
    $stmt = $myBdd->pdo->prepare($sql);
    $result = $stmt->execute([
      $idMatch, $data->params->action, $data->params->startTime, $data->params->startTimeServer,
      $data->params->runTime, $data->params->maxTime
    ]);
  }
  if ($result) {
    // Creation du Cache...
    $cMatch = new CacheMatch($_GET);
    $cMatch->MatchChrono($myBdd, $idMatch);
    return_200();
  } else {
    return_400();
  }
}

function PutStatsController($route, $params)
{
  $data = json_decode(file_get_contents('php://input'));

  if (!in_array($data->action, ['pass', 'kickoff', 'kickoff-ko', 'shot-in', 'shot-out'])) {
    return_401();
  }

  $myBdd = new MyBdd();
  $sql = "SELECT COUNT(Id)
  FROM kp_match 
  WHERE Id = ? 
  AND Validation != 'O' ";
  $stmt = $myBdd->pdo->prepare($sql);
  $stmt->execute([$data->game]);
  $result = $stmt->fetchColumn();
  if ($result != 1) {
    return_400('Game locked : ' . $result);
  }

  $sql = "INSERT INTO kp_stats 
    SET user = ?, 
    game = ?,
    team = ?,
    player = ?,
    `action` = ?, 
    timer = ? ";
  $stmt = $myBdd->pdo->prepare($sql);
  $result = $stmt->execute([
    $data->user, $data->game, $data->team, $data->player,
    $data->action, $data->timer
  ]);
  return_200();
}
