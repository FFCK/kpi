<?php
// prevent direct access *****************************************************
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if(!$isAjax) {
  $user_error = 'Access denied - not an AJAX request...';
  trigger_error($user_error, E_USER_ERROR);
}
// ***************************************************************************
	
//include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
//include_once('../commun/MyTools.php');
	
	// Chargement
	$term = trim($_GET['term']);
	// replace multiple spaces with one
	$term = preg_replace('/\s+/', ' ', $term);
	// supprime les 0 devant les numéros de licence
	$term = preg_replace('`^[0]*`','',$term);
	$idMatch = trim($_GET['idMatch']);
 
	$jRow = array();
	$a_json = array();
/*	// SECURITY HOLE ***************************************************************
	$a_json_invalid = array(array("id" => "#", "value" => $term, "label" => "Only letters and digits are permitted..."));
	$json_invalid = json_encode($a_json_invalid);
	// allow space, any unicode letter and digit, underscore and dash
	if(preg_match("/[^\040\pL\pN_-]/u", $term)) {
	  print $json_invalid;
	  exit;
	}
	// *****************************************************************************
*/ 
	$myBdd = new MyBdd();
	$idEquipes = '';
	//Equipes
	$sql  = "SELECT j.Id, j.Code_saison, j.Code_competition, ce.Libelle, ce.Poule, ce.Tirage, ce.Id idEquipe ";
	$sql .= "FROM gickp_Journees j, gickp_Matchs m, gickp_Competitions_Equipes ce ";
	$sql .= "WHERE m.Id_journee = j.Id ";
	$sql .= "AND m.Id = ".$idMatch." ";
	$sql .= "AND j.Code_saison = ce.Code_saison ";
	$sql .= "AND j.Code_competition = ce.Code_compet ";
	$sql .= "Group By ce.Libelle ";	 
	$sql .= "Order By ce.Poule, ce.Tirage, ce.Libelle ";	 
	$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load Autocomplet_joueur : ".$sql);
	while($row = mysql_fetch_assoc($result)) {
		$codeCompet = $row['Code_competition'];
		$codeSaison = $row['Code_saison'];
		$codeJournee = $row['Id'];
		$pos = stripos(strtoupper($row["Libelle"]), strtoupper($term));
		if($pos !== false) {
			$jRow["label"] = $row["Libelle"];
			$jRow["value"] = $row["Libelle"];
			$jRow["category"] = 'Equipes';
			array_push($a_json, $jRow);
		}
		//toutes les équipes de la compétition
		if ($idEquipes != '')
			$idEquipes .= ',';
		$idEquipes .= $row["idEquipe"];
	}
	//Joueurs
	$sql  = "SELECT distinct a.Matric, a.Nom, a.Prenom, b.Libelle, c.Arb, c.niveau, (c.Arb IS NULL) AS sortCol ";
	$sql .= "FROM gickp_Competitions_Equipes_Joueurs a LEFT OUTER JOIN gickp_Arbitre c ON a.Matric = c.Matric, ";
	$sql .= "gickp_Competitions_Equipes b ";
	$sql .= "WHERE a.Id_equipe = b.Id ";
	$sql .= "AND a.Capitaine <> 'X' ";
	$sql .= "AND b.Id IN (".$idEquipes.") ";
	$sql .= "AND (a.Matric Like '%".ltrim($term, '0')."%' ";
	$sql .= "Or UPPER(CONCAT_WS(' ', a.Nom, a.Prenom)) LIKE UPPER('%".$term."%') ";
	$sql .= "Or UPPER(CONCAT_WS(' ', a.Prenom, a.Nom)) LIKE UPPER('%".$term."%') ";
	$sql .= "Or UPPER(b.Libelle) LIKE UPPER ('%".$term."%') ) ";
	$sql .= "Order by b.Libelle, sortCol, c.Arb, a.Nom, a.Prenom ";
	$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load Autocomplet_joueur : <br />".$sql);
	while($row = mysql_fetch_assoc($result)) {
		$arb = strtoupper($row['Arb']);
		$nom = mb_convert_case(strtolower($row['Nom']), MB_CASE_TITLE, "UTF-8");
		$prenom = mb_convert_case(strtolower($row['Prenom']), MB_CASE_TITLE, "UTF-8");
		$jRow["label"] = $row["Libelle"].' - '.$nom.' '.$prenom.' ('.$arb.' '.$row["niveau"].')';
		$jRow["value"] = $nom.' '.$prenom.' ('.$row["Libelle"].') | '.$row["Matric"];
		$jRow["matric"] = $row["Matric"];
		$jRow["category"] = 'Joueurs';
		array_push($a_json, $jRow);
	}
	//Pool
	$sql  = "Select a.Matric, a.Nom, a.Prenom, b.Libelle, c.Arb, c.niveau ";
	$sql .= "From gickp_Competitions_Equipes_Joueurs a left outer join gickp_Arbitre c on a.Matric = c.Matric, gickp_Competitions_Equipes b  ";
	$sql .= "Where a.Id_equipe = b.Id ";
	$sql .= "And b.Code_compet = 'POOL' ";
	$sql .= "And (a.Matric Like '%".ltrim($term, '0')."%' ";
	$sql .= "Or UPPER(CONCAT_WS(' ', a.Nom, a.Prenom)) LIKE UPPER('%".$term."%') ";
	$sql .= "Or UPPER(CONCAT_WS(' ', a.Prenom, a.Nom)) LIKE UPPER('%".$term."%') ";
	$sql .= "Or UPPER(b.Libelle) LIKE UPPER('%".$term."%') ";
	$sql .= ") ";
	$sql .= "Order By a.Nom, a.Prenom ";
	$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load Pool arbitres : ".$sql);
	while ($row = mysql_fetch_assoc($result))
	{
		//$libelle = 'Pool Arbitres 1';
		$libelle = substr($row['Libelle'],0,3);
		$arb = strtoupper($row['Arb']);
		if($row['niveau'] != '')
			$arb .= '-'.$row['niveau'];
		$matric = $row['Matric'];
		$nom = mb_convert_case(strtolower($row['Nom']), MB_CASE_TITLE, "UTF-8");
		$prenom = mb_convert_case(strtolower($row['Prenom']), MB_CASE_TITLE, "UTF-8");
		$jRow["label"] = $nom.' '.$prenom.' ('.$libelle.') - '.$arb;
		$jRow["value"] = $nom.' '.$prenom.' ('.$libelle.') | '.$row["Matric"];
		$jRow["matric"] = $row["Matric"];
		$jRow["category"] = 'Pool arbitres';
		array_push($a_json, $jRow);
	}
	//Autres arbitres
	$sql  = "Select lc.*, c.Libelle, b.Arb, b.niveau ";
	$sql .= "From gickp_Liste_Coureur lc, gickp_Arbitre b, gickp_Club c ";
	$sql .= "Where lc.Matric = b.Matric ";
	$sql .= "And (lc.Matric Like '%".ltrim($term, '0')."%' ";
	$sql .= "Or UPPER(CONCAT_WS(' ', lc.Nom, lc.Prenom)) LIKE UPPER('%".$term."%') ";
	$sql .= "Or UPPER(CONCAT_WS(' ', lc.Prenom, lc.Nom)) LIKE UPPER('%".$term."%') ";
	$sql .= ") And lc.Numero_club = c.Code ";
	$sql .= "Order by lc.Nom, lc.Prenom ";
	$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load Autocomplet_joueur : <br />".$sql);
	while($row = mysql_fetch_assoc($result)) {
		$libelle = mb_convert_case(strtolower($row['Libelle']), MB_CASE_TITLE, "UTF-8");
		$arb = strtoupper($row['Arb']);
		if($row['niveau'] != '')
			$arb .= '-'.$row['niveau'];
		$nom  = mb_convert_case(strtolower($row['Nom']), MB_CASE_TITLE, "UTF-8");
		$prenom = mb_convert_case(strtolower($row['Prenom']), MB_CASE_TITLE, "UTF-8");
		$jRow["label"] = $nom .' '.$prenom.' ('.$arb.') - '.$libelle;
		$jRow["value"] = $nom .' '.$prenom.' ('.$arb.') | '.$row["Matric"];
		$jRow["matric"] = $row["Matric"];
		$jRow["category"] = 'Autres arbitres';
		array_push($a_json, $jRow);
	}

	$json = json_encode($a_json);
	print $json;
?>