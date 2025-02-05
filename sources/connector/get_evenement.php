<?php	

// Exemple : http://localhost/connector/get_evenement.php?lst=2,5
//           https://kayak-polo.info/connector/get_evenement.php?lst=2,5

include_once('../commun/MyConfig.php');
include_once('../commun/MyBdd.php');

function load( & $arrayJson, $sql,  & $bdd) 
{
	$lstId = '-1';
	$table = array();
	$stmt = $bdd->pdo->query($sql);
	while ($row = $stmt->fetch()) {
		
		$record = array();
		foreach($row as $key => $value) {
			if ($key == 'Id') {
				$lstId.= ','.$value;
			}
			
			array_push($record, htmlspecialchars($value));
		}
		
		array_push($table, $record);
	}
	
	$arrayJson = array('count' => count($table), 'rows' => $table);
	
	return $lstId;
}

// Parsing des paramètres ...
$lstEvenement = '-1';
if (utyGetGet('lst', false))
	$lstEvenement = utyGetGet('lst', false);
	
$userpwd = '';
if (utyGetGet('session', false))
	$userpwd = utyGetGet('session', false);

// Connexion BDD
$myBdd = new MyBdd();

if (PRODUCTION) {
	// Vérification user - pwd ...
	$sql = "SELECT * 
		FROM kp_user 
		WHERE md5(concat(code,pwd)) = ? ";
	$result = $myBdd->pdo->prepare($sql);
	$result->execute(array($userpwd));
	$num_results = $result->rowCount();

	$bKo = true;
	$bUserPwd = false;
	if ($num_results >= 1) {
		$bUserPwd = true;
		$row = $result->fetch();
		$userEvenement = $row['Id_Evenement'];
		$Date_debut = $row["Date_debut"];
		$Date_fin = $row["Date_fin"];
		$UserCode = $row["Code"];
		$UserIdentite = $row["Identite"];

		$arraySrc = explode(',', $lstEvenement);
		$arrayUser = explode('|', $userEvenement);
		
		for ($i=0;$i<count($arraySrc);$i++) {
			$bKo = true;
			for ($j=0;$j<count($arrayUser);$j++) {
				if ($arraySrc[$i] == $arrayUser[$j]) {
					$bKo = false;
					break;
				}
			}
		}
	}

	
	if ($bKo) {
		if (utyGetGet('callback', false)) {
			$callback = utyGetGet('callback', false);
			if ($bUserPwd)
				echo $callback."('ERREUR Evènement ou date expirée...');";
			else
				echo $callback."('ERREUR Login ...');";
		} else {
			echo 'ERREUR Login ...';
		}
		return;
	}
}

// Table Evenement ...
$sql = "Select * ";
$sql.= "From kp_evenement ";
$sql.= "Where Id In ($lstEvenement) ";

$arrayEvenement = array();
load($arrayEvenement, $sql, $myBdd);

// Table Evenement_Journees ...
$sql = "Select * ";
$sql.= "From kp_evenement_journee ";
$sql.= "Where Id_evenement In ($lstEvenement) ";

$arrayEvenementJournees = array();
load($arrayEvenementJournees, $sql, $myBdd);

// Table Journees ...
$sql = "Select a.* ";
$sql.= "From kp_journee a, kp_evenement_journee b ";
$sql.= "Where b.Id_evenement In ($lstEvenement) ";
$sql.= "And a.Id = b.Id_journee ";

$arrayJournees = array();
$lstJournees = load($arrayJournees, $sql, $myBdd);

$lstCompetition = "'null'";
$lstSaison = "'null'";
$rowsJournees = $arrayJournees['rows'];

$arrayColumnsJournees = array();
$myBdd->ShowColumnsSQL('kp_journee', $arrayColumnsJournees);

for ($i = 0; $i < count($rowsJournees); $i++) {
	$lstCompetition.= ",'".$rowsJournees[$i][$myBdd->GetIndexColumnByArray('Code_competition', $arrayColumnsJournees)]."'"; // => Code_competition
	$lstSaison.= ",'".$rowsJournees[$i][$myBdd->GetIndexColumnByArray('Code_saison', $arrayColumnsJournees)]."'"; // => Code_saison
}

// Table Saison ... 
$sql = "Select * ";
$sql.= "From kp_saison ";
$sql.= "Where Code In ($lstSaison) Or Etat = 'A' ";
$arraySaison = array();
load($arraySaison, $sql, $myBdd);

// Table Competitions ...
$sql = "Select * ";
$sql.= "From kp_competition ";
$sql.= "Where Code In ($lstCompetition) And Code_saison In ($lstSaison)";
$arrayCompetitions = array();
load($arrayCompetitions, $sql, $myBdd);

$lstCodeRef = "'null'";
$rowsCompetitions = $arrayCompetitions['rows'];
for ($i = 0; $i < count($rowsCompetitions); $i++) 
{
	$lstCodeRef.= ",'".$rowsCompetitions[$i][$myBdd->GetIndexColumn('kp_competition', 'Code_ref')]."'"; // => Code_ref
}


// Table Competitions_Groupes ...
$sql = "Select * ";
$sql.= "From kp_groupe ";
$sql.= "Where Groupe In ($lstCodeRef) ";
$arrayCompetitionsGroupes = array();
$lstCompetitionsGroupes = load($arrayCompetitionsGroupes, $sql, $myBdd);

// Table Competitions_Equipes ...
$sql = "Select * ";
$sql.= "From kp_competition_equipe ";
$sql.= "Where Code_compet In ($lstCompetition) And Code_saison In ($lstSaison)";
$arrayCompetitionsEquipes = array();
$lstCompetitionsEquipes = load($arrayCompetitionsEquipes, $sql, $myBdd);

// Prise des numeros des Equipes ...
$lstEquipe = '-1';
$rowsCompetitionsEquipes = $arrayCompetitionsEquipes['rows'];
for ($i = 0; $i < count($rowsCompetitionsEquipes); $i++)
{
	$lstEquipe .= ','.$rowsCompetitionsEquipes[$i][$myBdd->GetIndexColumn('kp_competition_equipe', 'Numero')]; // => Numero
}

// Table Competitions_Equipes_Init ...
$sql = "Select * ";
$sql.= "From kp_competition_equipe_init ";
$sql.= "Where Id In ($lstCompetitionsEquipes) ";
$arrayCompetitionsEquipesInit = array();
load($arrayCompetitionsEquipesInit, $sql, $myBdd);

// Table Competitions_Equipes_Joueurs ...
$sql = "Select * ";
$sql.= "From kp_competition_equipe_joueur ";
$sql.= "Where Id_equipe In ($lstCompetitionsEquipes) ";
$arrayCompetitionsEquipesJoueurs = array();
load($arrayCompetitionsEquipesJoueurs, $sql, $myBdd);

// Table Competitions_Equipes_Journee ...
$sql = "Select * ";
$sql.= "From kp_competition_equipe_journee ";
$sql.= "Where Id In ($lstCompetitionsEquipes) And Id_journee In ($lstJournees) ";
$arrayCompetitionsEquipesJournee = array();
load($arrayCompetitionsEquipesJournee, $sql, $myBdd);

// Table Competitions_Equipes_Niveau ...
$sql = "Select * ";
$sql.= "From kp_competition_equipe_niveau ";
$sql.= "Where Id In ($lstCompetitionsEquipes) ";
$arrayCompetitionsEquipesNiveau = array();
load($arrayCompetitionsEquipesNiveau, $sql, $myBdd);

// Table Matchs ...
$sql = "Select * ";
$sql.= "From kp_match ";
$sql.= "Where Id_journee In ($lstJournees) ";
$arrayMatchs = array();
$lstMatchs = load($arrayMatchs, $sql, $myBdd);

// Table Matchs_Detail ...
$sql = "Select * ";
$sql.= "From kp_match_detail ";
$sql.= "Where Id_match In ($lstMatchs) ";
$arrayMatchsDetail = array();
load($arrayMatchsDetail, $sql, $myBdd);

// Table Matchs_Joueurs ...
$sql = "Select * ";
$sql.= "From kp_match_joueur ";
$sql.= "Where Id_match In ($lstMatchs) ";
$arrayMatchsJoueurs = array();
load($arrayMatchsJoueurs, $sql, $myBdd);

// Table Equipe ...
$sql = "Select * ";
$sql.= "From kp_equipe ";
$sql.= "Where Numero In ($lstEquipe) ";
$arrayEquipe = array();
load($arrayEquipe, $sql, $myBdd);

$lstClub = "'null'";
$rowsEquipes = $arrayEquipe['rows'];
for ($i = 0; $i < count($rowsEquipes); $i++)
{
	$lstClub .= ','.$rowsEquipes[$i][$myBdd->GetIndexColumn('kp_equipe', 'Code_club')]; // => Code_club
}

// Table Club ...
$sql = "Select distinct * ";
$sql.= "From kp_club ";
$sql.= "Where Code In ($lstClub) ";
$arrayClub = array();
load($arrayClub, $sql, $myBdd);

// Table kp_cd
$sql = "Select distinct a.* ";
$sql.= "From kp_cd a, kp_club b ";
$sql.= "Where a.Code = b.Code_comite_dep ";
$sql.= "And b.Code In ($lstClub) ";
$arrayComiteDep = array();
load($arrayComiteDep, $sql, $myBdd);

// Table kp_cr
$sql = "Select distinct a.* ";
$sql.= "From kp_cr a, kp_cd b, kp_club c ";
$sql.= "Where a.Code = b.Code_comite_reg ";
$sql.= "And b.Code = c.Code_comite_dep ";
$sql.= "And c.Code In ($lstClub) ";
$arrayComiteReg = array();
load($arrayComiteReg, $sql, $myBdd);


/*
$lstCD = "'null'";
$rowsEquipes = $arrayEquipe['rows'];
for ($i = 0; $i < count($rowsEquipes); $i++)
{
	$lstClub .= ','.$rowsEquipes[$i][$myBdd->GetIndexColumn('kp_equipe', 'Code_club')]; // => Code_club
}
*/
// JSON Final ...

$arrayJson = array(

		'Saison' => $arraySaison,
		
		'Competitions' => $arrayCompetitions, 
		'Competitions_Groupes' => $arrayCompetitionsGroupes,
		'Competitions_Equipes' => $arrayCompetitionsEquipes,

		'Competitions_Equipes_Init' => $arrayCompetitionsEquipesInit,
		'Competitions_Equipes_Joueurs' => $arrayCompetitionsEquipesJoueurs,
		'Competitions_Equipes_Journee' => $arrayCompetitionsEquipesJournee,
		'Competitions_Equipes_Niveau' => $arrayCompetitionsEquipesNiveau,
	
		'Evenement' => $arrayEvenement,
		'Journees' => $arrayJournees,
		'Evenement_Journees' => $arrayEvenementJournees,

		'Equipe' => $arrayEquipe,
		'Club' => $arrayClub,
		'Comite_dep' => $arrayComiteDep,
		'Comite_reg' => $arrayComiteReg,

		'Matchs' => $arrayMatchs,
		'Matchs_Detail' => $arrayMatchsDetail,
		'Matchs_Joueurs' => $arrayMatchsJoueurs
		);
		
//$jsondata = html_entity_decode(htmlspecialchars_decode(stripcslashes(json_encode($arrayJson)), ENT_COMPAT, 'UTF-8');
//$jsondata = htmlspecialchars_decode(stripcslashes(json_encode($arrayJson)));
//$jsondata = str_replace("\'","'",$jsondata);
$jsondata = json_encode($arrayJson);
if (utyGetGet('callback', false)) {
	//Fonction EvtExport
	if (PRODUCTION)
		$myBdd->EvtExport($UserCode, $lstEvenement, 'Export', $UserIdentite, '');
	$callback = utyGetGet('callback', false);
	echo $callback.'('.$jsondata.');';
} else {
	//Fonction EvtExport
	if (PRODUCTION)
		$myBdd->EvtExport($UserCode, $lstEvenement, '?', $UserIdentite, '');
	
	echo $jsondata;
}

