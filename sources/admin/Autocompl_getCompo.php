<?php
// prevent direct access *****************************************************
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if(!$isAjax) {
  $user_error = 'Access denied - not an AJAX request...';
  trigger_error($user_error, E_USER_ERROR);
}
// ***************************************************************************
session_start();
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Langue
$langue = parse_ini_file("../commun/MyLang.ini", true);
if (utyGetSession('lang') == 'en') {
    $lang = $langue['en'];
} else {
    $lang = $langue['fr'];
}
        
$myBdd = new MyBdd();

// Chargement
$codeCompet = utyGetSession('codeCompet');
if (substr($codeCompet, 0, 1) != 'N' && substr($codeCompet, 0, 2) != 'CF') {
    $codeSaison = (int) trim(utyGetGet('s'));
    $codeSaison2 = $codeSaison - 1;
    $q = (int) trim(utyGetGet('q'));

    $sql = "SELECT ce.Code_saison, ce.Code_compet 
        FROM `kp_competition_equipe` ce, `kp_competition_equipe_joueur` cej 
        WHERE ce.Id = cej.Id_equipe 
        AND (ce.Code_saison = ? OR ce.Code_saison = ?) 
        AND ce.Numero = ? 
        GROUP BY ce.Code_compet, ce.Code_saison 
        ORDER BY ce.Code_saison DESC, ce.Code_compet ";
    $result = $myBdd->pdo->prepare($sql);
    $result->execute(array($codeSaison, $codeSaison2, $q));
    echo "<input type='radio' name='checkCompo' value='' checked /><i>" . $lang['Aucune_reprise'] . "</i><br>";
    while ($row = $result->fetch()) {
        $Code_saison = $row['Code_saison'];
        $Code_compet = $row['Code_compet'];
        echo "<input type='radio' name='checkCompo' value='$Code_saison-$Code_compet'/>$Code_saison - $Code_compet<br />";
    }
} else {
    echo '<br>-> Pas de reprise des feuilles de présences sur Championnat de France et Coupe de France<br>';
}