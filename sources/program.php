<?php
// prevent direct access *****************************************************
//$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
//strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
//if(!$isAjax) {
//    $user_error = 'Access denied !';
//    die($user_error);
    //trigger_error($user_error, E_USER_ERROR);
//}
// ***************************************************************************

include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

$myBdd = new MyBdd();

$season = trim(utyGetGet('season'));
$event = trim(utyGetGet('event'));
$compet = trim(utyGetGet('compet'));

// Saisons ...
$a_json = array();
$sql = "SELECT Code, Etat, Nat_debut, Nat_fin, Inter_debut, Inter_fin 
    FROM kp_saison 
    WHERE Code > '1900' 
    ORDER BY Code DESC ";
$result = $myBdd->pdo->prepare($sql);
$result->execute();
while ($row = $result->fetch()) {
    if($jRow['Code'] == $season) {
        $jRow['selected'] = 'selected';
    } else {
        $jRow['selected'] = '';
    }
    array_push($a_json, $jRow);
}
    
header("Content-type: application/json; charset=utf-8");
//header('Access-Control-Allow-Origin: *');
//header('Access-Control-Allow-Methods: GET, POST');
echo utyGetGet('callback') . '(' . json_encode($a_json) . ')';
exit();