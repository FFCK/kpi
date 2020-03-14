<?php 
// prevent direct access *****************************************************
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if(!$isAjax) {
  $user_error = 'Access denied - not an AJAX request...';
  trigger_error($user_error, E_USER_ERROR);
}
// ***************************************************************************
	
include_once('../../commun/MyBdd.php');
include_once('../../commun/MyTools.php');
include_once('../../live/create_cache_match.php');

	session_start();

	$myBdd = new MyBdd();
	$idMatch = (int)$_POST['Id_Match'];
	$Valeur = $myBdd->RealEscapeString(trim($_POST['Valeur']));
	$TypeUpdate = $myBdd->RealEscapeString(trim($_POST['TypeUpdate']));

	// Contrôle autorisation journée
	$sql  = "SELECT Id_journee, Validation "
            . "FROM gickp_Matchs "
            . "WHERE Id = ".$idMatch;
	$result = $myBdd->Query($sql);
	$row = $myBdd->FetchArray($result);
	if (!utyIsAutorisationJournee($row['Id_journee'])) {
        die("Vous n'avez pas l'autorisation de modifier les matchs de cette journée !");
    }
    if ($TypeUpdate != 'Validation' && $TypeUpdate != 'Publication' && $row['Validation'] == 'O') {
        die("Ce match est verrouillé !");
    }

    if($TypeUpdate == 'ValidScore'){
		$Valeur = explode('-', $Valeur);
		$sql  = "UPDATE gickp_Matchs "
                . "SET ScoreA = '".$Valeur[0]."', ScoreB = '".$Valeur[1]."' "
                . "WHERE Id = ".$idMatch;
		$result = $myBdd->Query($sql);
		echo 'OK';
	}elseif($TypeUpdate == 'ValidScoreDetail'){
		$Valeur = explode('-', $Valeur);
		$sql  = "UPDATE gickp_Matchs "
                . "SET ScoreDetailA = '".$Valeur[0]."', ScoreDetailB = '".$Valeur[1]."', "
                . "ScoreA = '".$Valeur[0]."', ScoreB = '".$Valeur[1]."' "
                . "WHERE Id = ".$idMatch;
		$result = $myBdd->Query($sql);
		echo 'OK';
	}else{
		$sql  = "UPDATE gickp_Matchs "
                . "SET ".$TypeUpdate." = '".$Valeur."' "
                . "WHERE Id = ".$idMatch;
		$result = $myBdd->Query($sql);
		echo 'OK';
	}
	
	// COSANDCO : Creation du Cache ...
	$cMatch = new CacheMatch($_GET);
	$cMatch->MatchGlobal($myBdd, $idMatch);
	$cMatch->MatchScore($myBdd, $idMatch);
