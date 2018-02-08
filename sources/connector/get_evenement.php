<?php	

// Exemple : http://localhost/connector/get_evenement.php?lst=2,5
//           https://kayak-polo.info/connector/get_evenement.php?lst=2,5

include_once('../commun/MyConfig.php');
include_once('../commun/MyBdd.php');

function load( & $arrayJson, $sql,  & $bdd) 
{
	
	$lstId = '-1';
	$table = array();
//	$result = mysql_query($sql, $bdd-> m_link)or die("SQL-Error :".$sql);
//	$num_results = mysql_num_rows($result);
//	for ($i = 0; $i < $num_results; $i++) {
//		$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$result = $bdd->Query($sql);
	while ($row = $bdd->FetchArray($result, $resulttype=MYSQL_ASSOC)){ 
		
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
if (isset($_GET['lst']))
	$lstEvenement = $_GET['lst'];
	
$userpwd = '';
if (isset($_GET['session']))
	$userpwd = $_GET['session'];

// Connexion BDD
$myBdd = new MyBdd();

if (PRODUCTION)
{
	// Vérification user - pwd ...
	$sql  = "Select * From gickp_Utilisateur ";
	$sql .= "Where md5(concat(code,pwd)) = '$userpwd'";

//	$result = mysql_query($sql, $myBdd-> m_link) or die("SQL-Error :".$sql);
	$result = $myBdd->Query($sql);
//	$num_results = mysql_num_rows($result);
	$num_results = $myBdd->NumRows($result);
	$bKo = true;
	$bUserPwd = false;
	if ($num_results >= 1)
	{
		$bUserPwd = true;
//		$row = mysql_fetch_array($result);	 
		$row = $myBdd->FetchRow($result);
		$userEvenement = $row['Id_Evenement'];
		$Date_debut = $row["Date_debut"];
		$Date_fin = $row["Date_fin"];
		$UserCode = $row["Code"];
		$UserIdentite = $row["Identite"];

		$arraySrc = explode(',', $lstEvenement);
		$arrayUser = explode('|', $userEvenement);
		
		for ($i=0;$i<count($arraySrc);$i++)
		{
			$bKo = true;
			for ($j=0;$j<count($arrayUser);$j++)
			{
				if ($arraySrc[$i] == $arrayUser[$j])
				{
					$bKo = false;
					break;
				}
			}
		}
/*		if(date() > $Date_fin)
		{
			alert('Date Expirée');
			$bKo = true;
		}
*/
	}

	
	if ($bKo)
	{
		if (isset($_GET['callback'])) 
		{
			$callback = $_GET['callback'];
			if ($bUserPwd)
				echo $callback."('ERREUR Evènement ou date expirée...');";
			else
				echo $callback."('ERREUR Login ...');";
		} 
		else 
		{
			echo 'ERREUR Login ...';
		}
		return;
	}
}

// Table Evenement ...
$sql = "Select * ";
$sql.= "From gickp_Evenement ";
$sql.= "Where Id In ($lstEvenement) ";

$arrayEvenement = array();
load($arrayEvenement, $sql, $myBdd);

// Table Evenement_Journees ...
$sql = "Select * ";
$sql.= "From gickp_Evenement_Journees ";
$sql.= "Where Id_evenement In ($lstEvenement) ";

$arrayEvenementJournees = array();
load($arrayEvenementJournees, $sql, $myBdd);

// Table Journees ...
$sql = "Select a.* ";
$sql.= "From gickp_Journees a, gickp_Evenement_Journees b ";
$sql.= "Where b.Id_evenement In ($lstEvenement) ";
$sql.= "And a.Id = b.Id_journee ";

$arrayJournees = array();
$lstJournees = load($arrayJournees, $sql, $myBdd);

$lstCompetition = "'null'";
$lstSaison = "'null'";
$rowsJournees = $arrayJournees['rows'];

$arrayColumnsJournees = array();
$myBdd->ShowColumnsSQL('gickp_Journees', $arrayColumnsJournees);

for ($i = 0; $i < count($rowsJournees); $i++) {
	$lstCompetition.= ",'".$rowsJournees[$i][$myBdd->GetIndexColumnByArray('Code_competition', $arrayColumnsJournees)]."'"; // => Code_competition
	$lstSaison.= ",'".$rowsJournees[$i][$myBdd->GetIndexColumnByArray('Code_saison', $arrayColumnsJournees)]."'"; // => Code_saison
}

// Table Saison ... 
$sql = "Select * ";
$sql.= "From gickp_Saison ";
$sql.= "Where Code In ($lstSaison) Or Etat = 'A' ";
$arraySaison = array();
load($arraySaison, $sql, $myBdd);

// Table Competitions ...
$sql = "Select * ";
$sql.= "From gickp_Competitions ";
$sql.= "Where Code In ($lstCompetition) And Code_saison In ($lstSaison)";
$arrayCompetitions = array();
load($arrayCompetitions, $sql, $myBdd);

$lstCodeRef = "'null'";
$rowsCompetitions = $arrayCompetitions['rows'];
for ($i = 0; $i < count($rowsCompetitions); $i++) 
{
	$lstCodeRef.= ",'".$rowsCompetitions[$i][$myBdd->GetIndexColumn('gickp_Competitions', 'Code_ref')]."'"; // => Code_ref
}


// Table Competitions_Groupes ...
$sql = "Select * ";
$sql.= "From gickp_Competitions_Groupes ";
$sql.= "Where Groupe In ($lstCodeRef) ";
$arrayCompetitionsGroupes = array();
$lstCompetitionsGroupes = load($arrayCompetitionsGroupes, $sql, $myBdd);

// Table Competitions_Equipes ...
$sql = "Select * ";
$sql.= "From gickp_Competitions_Equipes ";
$sql.= "Where Code_compet In ($lstCompetition) And Code_saison In ($lstSaison)";
$arrayCompetitionsEquipes = array();
$lstCompetitionsEquipes = load($arrayCompetitionsEquipes, $sql, $myBdd);

// Prise des numeros des Equipes ...
$lstEquipe = '-1';
$rowsCompetitionsEquipes = $arrayCompetitionsEquipes['rows'];
for ($i = 0; $i < count($rowsCompetitionsEquipes); $i++)
{
	$lstEquipe .= ','.$rowsCompetitionsEquipes[$i][$myBdd->GetIndexColumn('gickp_Competitions_Equipes', 'Numero')]; // => Numero
}

// Table Competitions_Equipes_Init ...
$sql = "Select * ";
$sql.= "From gickp_Competitions_Equipes_Init ";
$sql.= "Where Id In ($lstCompetitionsEquipes) ";
$arrayCompetitionsEquipesInit = array();
load($arrayCompetitionsEquipesInit, $sql, $myBdd);

// Table Competitions_Equipes_Joueurs ...
$sql = "Select * ";
$sql.= "From gickp_Competitions_Equipes_Joueurs ";
$sql.= "Where Id_equipe In ($lstCompetitionsEquipes) ";
$arrayCompetitionsEquipesJoueurs = array();
load($arrayCompetitionsEquipesJoueurs, $sql, $myBdd);

// Table Competitions_Equipes_Journee ...
$sql = "Select * ";
$sql.= "From gickp_Competitions_Equipes_Journee ";
$sql.= "Where Id In ($lstCompetitionsEquipes) And Id_journee In ($lstJournees) ";
$arrayCompetitionsEquipesJournee = array();
load($arrayCompetitionsEquipesJournee, $sql, $myBdd);

// Table Competitions_Equipes_Niveau ...
$sql = "Select * ";
$sql.= "From gickp_Competitions_Equipes_Niveau ";
$sql.= "Where Id In ($lstCompetitionsEquipes) ";
$arrayCompetitionsEquipesNiveau = array();
load($arrayCompetitionsEquipesNiveau, $sql, $myBdd);

// Table Matchs ...
$sql = "Select * ";
$sql.= "From gickp_Matchs ";
$sql.= "Where Id_journee In ($lstJournees) ";
$arrayMatchs = array();
$lstMatchs = load($arrayMatchs, $sql, $myBdd);

// Table Matchs_Detail ...
$sql = "Select * ";
$sql.= "From gickp_Matchs_Detail ";
$sql.= "Where Id_match In ($lstMatchs) ";
$arrayMatchsDetail = array();
load($arrayMatchsDetail, $sql, $myBdd);

// Table Matchs_Joueurs ...
$sql = "Select * ";
$sql.= "From gickp_Matchs_Joueurs ";
$sql.= "Where Id_match In ($lstMatchs) ";
$arrayMatchsJoueurs = array();
load($arrayMatchsJoueurs, $sql, $myBdd);

// Table Equipe ...
$sql = "Select * ";
$sql.= "From gickp_Equipe ";
$sql.= "Where Numero In ($lstEquipe) ";
$arrayEquipe = array();
load($arrayEquipe, $sql, $myBdd);

$lstClub = "'null'";
$rowsEquipes = $arrayEquipe['rows'];
for ($i = 0; $i < count($rowsEquipes); $i++)
{
	$lstClub .= ','.$rowsEquipes[$i][$myBdd->GetIndexColumn('gickp_Equipe', 'Code_club')]; // => Code_club
}

// Table Club ...
$sql = "Select distinct * ";
$sql.= "From gickp_Club ";
$sql.= "Where Code In ($lstClub) ";
$arrayClub = array();
load($arrayClub, $sql, $myBdd);

// Table gickp_comite_dep
$sql = "Select distinct a.* ";
$sql.= "From gickp_Comite_dep a, gickp_Club b ";
$sql.= "Where a.Code = b.Code_comite_dep ";
$sql.= "And b.Code In ($lstClub) ";
$arrayComiteDep = array();
load($arrayComiteDep, $sql, $myBdd);

// Table gickp_comite_reg
$sql = "Select distinct a.* ";
$sql.= "From gickp_Comite_reg a, gickp_Comite_dep b, gickp_Club c ";
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
	$lstClub .= ','.$rowsEquipes[$i][$myBdd->GetIndexColumn('gickp_Equipe', 'Code_club')]; // => Code_club
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
if (isset($_GET['callback'])) 
{
	//Fonction EvtExport
	if (PRODUCTION)
		$myBdd->EvtExport($UserCode, $lstEvenement, 'Export', $UserIdentite, '');
	$callback = $_GET['callback'];
	echo $callback.'('.$jsondata.');';
} 
else 
{
	//Fonction EvtExport
	if (PRODUCTION)
		$myBdd->EvtExport($UserCode, $lstEvenement, '?', $UserIdentite, '');
	
	echo $jsondata;
}

?>  