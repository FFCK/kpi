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
/*	// SECURITY HOLE ***************************************************************
	$a_json_invalid = array(array("id" => "#", "value" => $term, "label" => "Only letters and digits are permitted..."));
	$json_invalid = json_encode($a_json_invalid);
	// allow space, any unicode letter and digit, underscore and dash
	if(preg_match("/[^\040\pL\pN_-]/u", $value)) {
	  print $json_invalid;
	  exit;
	}
	// *****************************************************************************
*/
	// Contrôle autorisation journée
	$sql  = "SELECT Id_journee, Validation "
            . "FROM gickp_Matchs "
            . "WHERE Id = ".$idMatch;
	$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Select<br />".$sql);
	$row = mysql_fetch_array($result);
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
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur UPDATE<br />".$sql);
		echo 'OK';
	}elseif($TypeUpdate == 'ValidScoreDetail'){
		$Valeur = explode('-', $Valeur);
		$sql  = "UPDATE gickp_Matchs "
                . "SET ScoreDetailA = '".$Valeur[0]."', ScoreDetailB = '".$Valeur[1]."', "
                . "ScoreA = '".$Valeur[0]."', ScoreB = '".$Valeur[1]."' "
                . "WHERE Id = ".$idMatch;
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur UPDATE<br />".$sql);
		echo 'OK';
	}else{
		$sql  = "UPDATE gickp_Matchs "
                . "SET ".$TypeUpdate." = '".$Valeur."' "
                . "WHERE Id = ".$idMatch;
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur UPDATE<br />".$sql);
		echo 'OK';
	}
	
	// COSANDCO : Creation du Cache ...
	$cMatch = new CacheMatch($_GET);
	$cMatch->MatchGlobal($myBdd, $idMatch);
	$cMatch->MatchScore($myBdd, $idMatch);
