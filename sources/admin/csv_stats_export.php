<?php

include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

$event = (int) utyGetGet('evt', 0);

if ($event === 0) {
  die('No event selected!');
}


$myBdd = new MyBdd();
$sql = "SELECT CONCAT(l.Nom, ' ', l.Prenom) 'user_name', s.`date_stat`,
    ce.Code_saison season, ce.Code_compet compet, j.Phase 'phase',
    s.game, m.Numero_ordre num_game, 
    ce.Code_club club, ce.Libelle 'team_name', 
    CONCAT(l2.Nom, ' ', l2.Prenom) 'player_name',
    s.period, s.timer, s.action
  FROM `kp_stats` s
  LEFT JOIN kp_licence l ON s.user = l.Matric
  LEFT JOIN kp_licence l2 ON s.player = l2.Matric
  LEFT JOIN kp_competition_equipe ce ON s.team = ce.Id
    LEFT JOIN kp_match m ON s.game = m.Id
    LEFT JOiN kp_journee j ON m.Id_journee = j.Id
    LEFT JOIN kp_evenement_journee ej ON j.Id = ej.Id_journee
  WHERE ej.Id_evenement = ?
  ORDER BY s.`date_stat` DESC ";
$stmt = $myBdd->pdo->prepare($sql);
$stmt->execute([$event]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);


$delimiter = ';';
$filename = 'Stats_' . date('Ymd_His') . '.csv';
$headline = [
  'user_name', 'date_stat', 'season', 'compet', 'phase', 'game', 'num_game',
  'club', 'team_name', 'player_name', 'period', 'timer', 'action'
];

// open raw memory as file so no temp files needed, you might run out of memory though
$f = fopen('php://memory', 'w');
// loop over the input array
fputcsv($f, $headline, $delimiter);

foreach ($result as $line) {
  // generate csv lines from the inner arrays
  fputcsv($f, $line, $delimiter);
}
// reset the file pointer to the start of the file
fseek($f, 0);
// tell the browser it's going to be a csv file
header('Content-Type: text/csv');
// tell the browser we want to save it instead of displaying it
header('Content-Disposition: attachment; filename="' . $filename . '";');
// make php send the generated csv lines to the browser
fpassthru($f);
