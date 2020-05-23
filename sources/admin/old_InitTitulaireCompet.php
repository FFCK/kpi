<?php
include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

session_start();

$codeCompet = utyGetPost('codeCompet', '');

$codeSaison = $myBdd->GetActiveSaison();
$idMatch = utyGetSession('idMatch', -1);
$lstJournee = utyGetSession('lstJournee', -1);

// Chargement des Matchs en jeux ...
$sql  = "Select a.Id, a.Id_equipeA, a.Id_equipeB ";
$sql .= "From gickp_Matchs a, gickp_Journees b ";
if ($idMatch < 0)
	$sql .= "Where a.Id_journee In ($lstJournee) ";
else
	$sql .= "Where a.Id = $idMatch ";

$sql .= "And a.Id_journee = b.Id ";
$sql .= "And b.Code_competition = '$codeCompet' ";
$sql .= "And b.Code_saison = '$codeSaison' ";

$myBdd = new MyBdd();

$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load");
$num_results = mysql_num_rows($result);

for ($i=0;$i<$num_results;$i++)
{
	$row = mysql_fetch_array($result);	
	
	$idMatch = $row['Id'];
	$idEquipeA = $row['Id_equipeA'];
	$idEquipeB = $row['Id_equipeB'];
	
	$sql = "Delete From gickp_Matchs_Joueurs Where Id_match = $idMatch And Equipe = 'A'";
	mysql_query($sql, $myBdd->m_link) or die ("Erreur Delete");
			
	$sql  = "Replace Into gickp_Matchs_Joueurs ";
	$sql .= "Select $idMatch, Matric, Numero, 'A', Capitaine From gickp_Competitions_Equipes_Joueurs ";
	$sql .= "Where Id_equipe = $idEquipeA ";
	$sql .= "AND Capitaine <> 'X' ";
	$sql .= "AND Capitaine <> 'A' ";
	mysql_query($sql, $myBdd->m_link) or die ("Erreur Replace");

	$sql = "Delete From gickp_Matchs_Joueurs Where Id_match = $idMatch And Equipe = 'B'";
	mysql_query($sql, $myBdd->m_link) or die ("Erreur Delete");
			
	$sql  = "Replace Into gickp_Matchs_Joueurs ";
	$sql .= "Select $idMatch, Matric, Numero, 'B', Capitaine From gickp_Competitions_Equipes_Joueurs ";
	$sql .= "Where Id_equipe = $idEquipeB ";
	$sql .= "AND Capitaine <> 'X' ";
	$sql .= "AND Capitaine <> 'A' ";
	mysql_query($sql, $myBdd->m_link) or die ("Erreur Replace");
}

/*
$debug  = "Insert Into gickp_mouchard (Valeur) Values ('";
$debug .= mysql_real_escape_string($sql);
$debug .= "')";
mysql_query($debug, $myBdd->m_link) or die ("Erreur Insert");
*/ 


header('X-JSON:' . json_encode('success'));

?>
