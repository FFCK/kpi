<?php
include_once('headers.php');

function StaffTestController ($method, $route) {
	return_200(['result' => 'OK']);
}

function EventController ($method, $route) {
  $myBdd = new MyBdd();
  $sql = "SELECT Id id, Libelle libelle, Lieu place
    FROM kp_evenement
    WHERE Publication = 'O'
    ORDER BY Id DESC ";
  $result = $myBdd->pdo->query($sql);
  $row = $result->fetchAll();

	return_200($row);
}

function GamesController ($method, $route) {
  $event = (int) $route[1];
  $force = $route[2] ?? false;
  $array = ($force !== 'force') ? json_cache_read('games', $event, 5) : false;
  if ($array) {
    return_200($array);
  }

  $myBdd = new MyBdd();
  $sql  = "SELECT  j.Code_competition c_code, c.Code_saison c_season, j.Phase d_phase, j.Niveau d_level, 
    j.Lieu d_place, j.Libelle d_label, c.Soustitre2 c_label, 
    m.Id g_id, m.Id_journee d_id, m.Numero_ordre g_number, m.Date_match g_date,
    m.Heure_match g_time, m.Terrain g_pitch, 
    m.Validation g_validation, m.Statut g_status, m.Periode g_period, 
    m.ScoreA g_score_a, m.ScoreB g_score_b, m.CoeffA g_coef_a, m.CoeffB g_coef_b, 
    m.ScoreDetailA g_score_detail_a, m.ScoreDetailB g_score_detail_b, 
    m.Id_equipeA t_a_id, m.Id_equipeB t_b_id,
    cea.Libelle t_a_label, ceb.Libelle t_b_label, cea.Numero t_a_number, ceb.Numero t_b_number,
    cea.Code_club t_a_club, ceb.Code_club t_b_club, 
    m.Arbitre_principal r_1, m.Arbitre_secondaire r_2, 
    m.Matric_arbitre_principal r_1_id, m.Matric_arbitre_secondaire r_2_id, 
    lcp.Nom r_1_name, lcp.Prenom r_1_firstname, 
    lcs.Nom r_2_name, lcs.Prenom r_2_firstname 
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