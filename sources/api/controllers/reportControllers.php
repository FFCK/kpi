<?php
include_once('config/cache.php');

function GetGameController($route, $params)
{
  $game_id = (int) $route[3] ?? return_405();
  $force = $route[5] ?? false;
  $cacheArray = ($force !== 'force') ? json_cache_read('game', $game_id, 1) : false;
  if ($cacheArray) {
    return_200($cacheArray);
  }

  // Game params
  $myBdd = new MyBdd();
  $myBdd->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  $sql = "SELECT c.Code c_code, (c.Code_saison *1) c_season, j.Phase d_phase, j.Niveau d_level, j.Type d_type, 
    j.Lieu d_place, j.Libelle d_label, c.Soustitre2 c_label, c.Code_typeclt c_type, 
    m.Id g_id, m.Id_journee d_id, m.Numero_ordre g_number, m.Date_match g_date,
    m.Heure_match g_time, m.Terrain g_pitch, m.Libelle g_code,
    m.Validation g_validation, m.Statut g_status, m.Periode g_period, 
    m.ScoreA g_score_a, m.ScoreB g_score_b, m.CoeffA g_coef_a, m.CoeffB g_coef_b, 
    m.ScoreDetailA g_score_detail_a, m.ScoreDetailB g_score_detail_b, 
    m.Id_equipeA t_a_id, m.Id_equipeB t_b_id,
    cea.Libelle t_a_label, ceb.Libelle t_b_label, cea.Numero t_a_number, ceb.Numero t_b_number,
    cea.Code_club t_a_club, ceb.Code_club t_b_club, 
    cea.color1 t_a_color1, cea.color2 t_a_color2, cea.colortext t_a_colortext, 
    ceb.color1 t_b_color1, ceb.color2 t_b_color2, ceb.colortext t_b_colortext, 
    CASE WHEN cea.logo IS NULL THEN 'KIP/logo/empty-logo.png' ELSE cea.logo END t_a_logo,
    CASE WHEN ceb.logo IS NULL THEN 'KIP/logo/empty-logo.png' ELSE ceb.logo END t_b_logo
    FROM kp_match m 
    LEFT OUTER JOIN kp_competition_equipe cea ON (m.Id_equipeA = cea.Id) 
    LEFT OUTER JOIN kp_competition_equipe ceb ON (m.Id_equipeB = ceb.Id) 
    INNER JOIN kp_journee j ON (m.Id_journee = j.Id) 
    INNER JOIN kp_competition c ON (j.Code_competition = c.Code AND j.Code_saison = c.Code_saison) 
    WHERE m.Id = ? 
    AND c.Publication = 'O'
    AND c.Statut != 'ATT'
    AND j.Publication = 'O'
    AND m.Publication = 'O' ";
  $stmt = $myBdd->pdo->prepare($sql);
  $stmt->execute([$game_id]);
  if ($stmt->rowCount() === 0) {
    return_200([]);
  }
  $result = $stmt->fetch(PDO::FETCH_ASSOC);

  // Events
  $sql = "SELECT md.Id e_id, md.Id_match g_id, md.Equipe_A_B e_team, md.Id_evt_match e_type, 
    md.Periode e_period, md.Temps e_time, md.motif e_motif, 
    l.Matric e_licence, md.Numero e_number, l.Nom e_name, l.Prenom e_firstname, mj.Capitaine e_status
    FROM kp_match_detail md 
    LEFT OUTER JOIN kp_licence l ON (md.Competiteur = l.Matric) 
    LEFT OUTER JOIN kp_match_joueur mj
      ON (md.Competiteur = mj.Matric AND md.Id_match = mj.Id_match) 
    WHERE md.Id_match = ? 
    ORDER BY md.date_insert DESC, md.Periode DESC, md.Temps ASC, md.Id_evt_match DESC ";
  $stmt = $myBdd->pdo->prepare($sql);
  $stmt->execute([$game_id]);
  $result['g_events'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Teams
  $sql = "SELECT mj.Id_match g_id, mj.Equipe team, mj.matric tm_licence, mj.Numero tm_number, mj.Capitaine tm_status, 
    l.Nom tm_name, l.Prenom tm_firstname, l.Sexe tm_gender, l.Naissance tm_birthdate 
    FROM kp_match_joueur mj
    LEFT OUTER JOIN kp_licence l ON (mj.Matric = l.matric)
    WHERE mj.Id_match = ? 
    ORDER BY mj.Equipe, mj.Numero ";
  $stmt = $myBdd->pdo->prepare($sql);
  $stmt->execute([$game_id]);
  $result['t_members'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

  json_cache_write('game', $game_id, $result);

  return_200($result);
}
