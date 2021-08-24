<?php
include_once('headers.php');

function StaffTestController($route)
{
  return_200(['result' => 'OK']);
}

function EventsController($route)
{
  $myBdd = new MyBdd();
  $sql = "SELECT Id id, Libelle libelle, Lieu place
    FROM kp_evenement
    WHERE Publication = 'O'
    ORDER BY Id DESC ";
  $result = $myBdd->pdo->query($sql);
  $row = $result->fetchAll();

  return_200($row);
}

function GamesController($route)
{
  $event = (int) $route[1];
  $force = $route[2] ?? false;
  $array = ($force !== 'force') ? json_cache_read('games', $event, 5) : false;
  if ($array) {
    return_200($array);
  }

  $myBdd = new MyBdd();
  $sql  = "SELECT j.Code_competition c_code, c.Code_saison c_season, j.Phase d_phase, j.Niveau d_level, 
    j.Lieu d_place, j.Libelle d_label, c.Soustitre2 c_label, 
    m.Id g_id, m.Id_journee d_id, m.Numero_ordre g_number, m.Date_match g_date,
    m.Heure_match g_time, m.Terrain g_pitch, m.Libelle g_code,
    m.Validation g_validation, m.Statut g_status, m.Periode g_period, 
    m.ScoreA g_score_a, m.ScoreB g_score_b, m.CoeffA g_coef_a, m.CoeffB g_coef_b, 
    m.ScoreDetailA g_score_detail_a, m.ScoreDetailB g_score_detail_b, 
    m.Id_equipeA t_a_id, m.Id_equipeB t_b_id,
    cea.Libelle t_a_label, ceb.Libelle t_b_label, cea.Numero t_a_number, ceb.Numero t_b_number,
    cea.Code_club t_a_club, ceb.Code_club t_b_club, 
    CASE WHEN cea.logo IS NULL THEN 'KIP/logo/empty-logo.png' ELSE cea.logo END t_a_logo,
    CASE WHEN ceb.logo IS NULL THEN 'KIP/logo/empty-logo.png' ELSE ceb.logo END t_b_logo,
    m.Arbitre_principal r_1, m.Arbitre_secondaire r_2, 
    m.Matric_arbitre_principal r_1_id, m.Matric_arbitre_secondaire r_2_id, 
    CONCAT(lcp.Nom, ' ', lcp.Prenom) r_1_name, 
    CONCAT(lcs.Nom, ' ', lcs.Prenom) r_2_name
    FROM kp_match m 
    LEFT OUTER JOIN kp_competition_equipe cea ON (m.Id_equipeA = cea.Id) 
    LEFT OUTER JOIN kp_competition_equipe ceb ON (m.Id_equipeB = ceb.Id) 
    LEFT OUTER JOIN kp_licence lcp ON (m.Matric_arbitre_principal = lcp.Matric) 
    LEFT OUTER JOIN kp_licence lcs ON (m.Matric_arbitre_secondaire = lcs.Matric) 
    INNER JOIN kp_journee j ON (m.Id_journee = j.Id) 
    INNER JOIN kp_evenement_journee ej ON (j.Id = ej.Id_journee)
    INNER JOIN kp_competition c ON (j.Code_competition = c.Code AND j.Code_saison = c.Code_saison) 
    WHERE ej.Id_evenement = ? 
    AND c.Publication = 'O'
    AND j.Publication = 'O'
    AND m.Publication = 'O'
    ORDER BY m.Date_match, m.Heure_match, m.Terrain";
  $result = $myBdd->pdo->prepare($sql);
  $result->execute(array($event));
  $array = $result->fetchAll(PDO::FETCH_ASSOC);

  json_cache_write('games', $event, $array);

  return_200($array);
}

function ChartsController($route)
{
  $event = (int) $route[1];
  $force = $route[2] ?? false;
  $charts = ($force !== 'force') ? json_cache_read('charts', $event, 5) : false;
  if ($charts) {
    return_200($charts);
  }

  $myBdd = new MyBdd();
  $sql  = "SELECT j.Phase d_phase, j.Niveau d_level, j.Type d_type, 
    j.Lieu d_place, j.Libelle d_label, c.Soustitre2 c_label, c.Code_typeclt c_type, 
    m.Id g_id, m.Id_journee d_id, m.Numero_ordre g_number, m.Date_match g_date,
    m.Heure_match g_time, m.Terrain g_pitch, m.Libelle g_code,
    m.Validation g_validation, m.Statut g_status, m.Periode g_period, 
    m.ScoreA g_score_a, m.ScoreB g_score_b, m.CoeffA g_coef_a, m.CoeffB g_coef_b, 
    m.ScoreDetailA g_score_detail_a, m.ScoreDetailB g_score_detail_b, 
    m.Id_equipeA t_a_id, m.Id_equipeB t_b_id,
    cea.Libelle t_a_label, ceb.Libelle t_b_label, cea.Numero t_a_number, ceb.Numero t_b_number,
    cea.Code_club t_a_club, ceb.Code_club t_b_club, 
    CASE WHEN cea.logo IS NULL THEN 'KIP/logo/empty-logo.png' ELSE cea.logo END t_a_logo,
    CASE WHEN ceb.logo IS NULL THEN 'KIP/logo/empty-logo.png' ELSE ceb.logo END t_b_logo
    FROM kp_match m 
    LEFT OUTER JOIN kp_competition_equipe cea ON (m.Id_equipeA = cea.Id) 
    LEFT OUTER JOIN kp_competition_equipe ceb ON (m.Id_equipeB = ceb.Id) 
    INNER JOIN kp_journee j ON (m.Id_journee = j.Id) 
    INNER JOIN kp_evenement_journee ej ON (j.Id = ej.Id_journee)
    INNER JOIN kp_competition c ON (j.Code_competition = c.Code AND j.Code_saison = c.Code_saison) 
    WHERE ej.Id_evenement = ? 
    AND c.Publication = 'O'
    AND j.Publication = 'O'
    AND m.Publication = 'O'
    ORDER BY m.Id_journee, m.Date_match, m.Heure_match, m.Terrain";
  $result = $myBdd->pdo->prepare($sql);
  $result->execute(array($event));
  while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    if ($row['d_type'] === 'E' || $row['c_type'] === 'CHPT') {
      $games[$row['d_id']][] = $row;
    } elseif ($row['d_type'] === 'C' && $row['c_type'] === 'CP') {
      $games[$row['d_id']][] = $row['g_code'];
    }
  }

  $sql  = "SELECT j.Code_saison c_season, j.Code_competition c_code, c.Code_typeclt c_type,
    c.Soustitre2 c_category,
    ej.Id_journee d_id, j.Phase d_phase, j.Etape d_round, j.Nbequipes t_count,
    j.Niveau d_level, j.Type d_type, j.Date_debut d_start, j.Date_fin d_end,
    j.Lieu d_place, j.Departement d_dpt,
    ce.Id t_id, ce.Numero t_number, ce.Libelle t_label, ce.Code_club t_club,
    cej.Clt_publi t_clt, cej.Pts_publi t_pts, cej.J_publi t_pld, cej.G_publi t_won,
    cej.N_publi t_draw, cej.P_publi t_lost, cej.F_publi t_f, cej.Plus_publi t_plus, cej.Moins_publi t_minus, 
    cej.Diff_publi t_diff, cej.PtsNiveau_publi t_ptslv, cej.CltNiveau_publi t_cltlv,
    CASE WHEN ce.logo IS NULL THEN 'KIP/logo/empty-logo.png' ELSE ce.logo END t_logo 
    FROM kp_journee j
    LEFT JOIN kp_competition c ON (j.Code_saison = c.Code_saison AND j.Code_competition = c.Code)
    LEFT JOIN kp_evenement_journee ej ON ej.Id_journee = j.Id
    LEFT OUTER JOIN kp_competition_equipe_journee cej ON cej.Id_journee = j.Id
    LEFT OUTER JOIN kp_competition_equipe ce ON ce.Id = cej.Id
    WHERE ej.Id_evenement = ? 
    ORDER BY c_season, c_code, d_round, d_level DESC, d_phase, d_start DESC, 
    t_clt ASC, t_diff DESC, t_plus ASC ";
  $result = $myBdd->pdo->prepare($sql);
  $result->execute(array($event));
  while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $charts[$row['c_code']]['type'] = $row['c_type'];
    $charts[$row['c_code']]['code'] = $row['c_code'];
    $charts[$row['c_code']]['libelle'] = $row['c_category'];
    $charts[$row['c_code']]['rounds'][$row['d_round']]['type'] = $row['d_type'];
    $charts[$row['c_code']]['rounds'][$row['d_round']]['phases'][$row['d_id']]['teams'][] = $row;
    $charts[$row['c_code']]['rounds'][$row['d_round']]['phases'][$row['d_id']]['type'] = $row['d_type'];
    $charts[$row['c_code']]['rounds'][$row['d_round']]['phases'][$row['d_id']]['libelle'] = $row['d_phase'];
    $charts[$row['c_code']]['rounds'][$row['d_round']]['phases'][$row['d_id']]['level'] = $row['d_level'];
    $charts[$row['c_code']]['rounds'][$row['d_round']]['phases'][$row['d_id']]['t_count'] = $row['t_count'];
    $charts[$row['c_code']]['rounds'][$row['d_round']]['phases'][$row['d_id']]['games'] = $games[$row['d_id']];
  }

  $charts = array_values($charts);

  json_cache_write('charts', $event, $charts);

  return_200($charts);
}
