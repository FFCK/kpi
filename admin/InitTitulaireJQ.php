<?php
include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

session_start();

$champs = utyGetPost('champs', ''); // Compet
$valeur = utyGetPost('valeur', ''); // N2H
$valeur3 = utyGetPost('valeur3', ''); // -1
if($champs == '' || $valeur == '')
{
	echo 'Pas de valeur transmise';
	return;
}
if($valeur == '*' ||  $valeur == '-1')
{
	echo 'Selectionnez une '.$champs;
	return;
}

switch($champs)
{
	case 'Compet':
		initCompet($valeur);
		break;
	case 'Journee':
		initJournee($valeur);
		break;
	case 'Equipe':
		initEquipe($valeur, $valeur3);
		break;
	default:
		echo 'Erreur Champs';
		break;
}
		
function initCompet($valeur)
{
	$codeCompet = $valeur;
	$codeSaison = utyGetSaison();
	//$idMatch = utyGetSession('idMatch', -1);
	$lstJournee = utyGetSession('lstJournee', -1);

	// Chargement des Matchs en jeux ...
	$sql  = "SELECT a.Id, a.Id_equipeA, a.Id_equipeB ";
	$sql .= "FROM gickp_Matchs a, gickp_Journees b ";
//	if ($idMatch > 0) {
//		$sql .= "WHERE a.Id = $idMatch ";
//    } else 
    if ($lstJournee != -1) {
		$sql .= "WHERE a.Id_journee In ($lstJournee) ";
    } else {
        $sql .= 'WHERE 1 ';
    }
	$sql .= "AND a.Validation != 'O' ";
	$sql .= "AND a.Id_journee = b.Id ";
	$sql .= "AND b.Code_competition = '$codeCompet' ";
	$sql .= "AND b.Code_saison = '$codeSaison' ";

	$myBdd = new MyBdd();

	$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load :".$sql);
	$num_results = mysql_num_rows($result);

	for ($i=0;$i<$num_results;$i++)
	{
		$row = mysql_fetch_array($result);	
		
		$idMatch = $row['Id'];
		$idEquipeA = $row['Id_equipeA'];
		$idEquipeB = $row['Id_equipeB'];
		if($idEquipeA != '')
		{
			$sql = "Delete From gickp_Matchs_Joueurs Where Id_match = $idMatch And Equipe = 'A'";
			mysql_query($sql, $myBdd->m_link) or die ("Erreur Delete :".$sql);
					
			$sql  = "Replace Into gickp_Matchs_Joueurs ";
			$sql .= "Select $idMatch, Matric, Numero, 'A', Capitaine From gickp_Competitions_Equipes_Joueurs ";
			$sql .= "Where Id_equipe = $idEquipeA ";
			$sql .= "AND Capitaine <> 'X' ";
			$sql .= "AND Capitaine <> 'A' ";
			mysql_query($sql, $myBdd->m_link) or die ("Erreur Replace :".$sql);
		}
		if($idEquipeB != '')
		{
			$sql = "Delete From gickp_Matchs_Joueurs Where Id_match = $idMatch And Equipe = 'B'";
			mysql_query($sql, $myBdd->m_link) or die ("Erreur Delete :".$sql);
					
			$sql  = "Replace Into gickp_Matchs_Joueurs ";
			$sql .= "Select $idMatch, Matric, Numero, 'B', Capitaine From gickp_Competitions_Equipes_Joueurs ";
			$sql .= "Where Id_equipe = $idEquipeB ";
			$sql .= "AND Capitaine <> 'X' ";
			$sql .= "AND Capitaine <> 'A' ";
			mysql_query($sql, $myBdd->m_link) or die ("Erreur Replace :".$sql);
		}
	}
	$myBdd->utyJournal('MAJ titulaires compétition', utyGetSaison(), utyGetSession('codeCompet', ''), '', '', '', $num_results.' m.', utyGetSession('User') );
	$resultGlobal = "Initialisation des titulaires OK pour la compétition, $num_results match(s) mis à jour.";
	echo $resultGlobal;
}

function initJournee($valeur)
{
	$idJournee = $valeur;

	$myBdd = new MyBdd();

	// Chargement des Matchs de la journée ...
	$sql  = "Select Id, Id_equipeA, Id_equipeB ";
	$sql .= "From gickp_Matchs ";
	$sql .= "Where Id_journee = ";
	$sql .= $idJournee.' ';
	$sql .= "And Validation != 'O' ";

	$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load :".$sql);
	$num_results = mysql_num_rows($result);

	for ($i=0;$i<$num_results;$i++)
	{
		$row = mysql_fetch_array($result);	
		
		$idMatch = $row['Id'];
		$idEquipeA = $row['Id_equipeA'];
		$idEquipeB = $row['Id_equipeB'];

		if($idEquipeA != '')
		{
			$sql = "Delete From gickp_Matchs_Joueurs Where Id_match = $idMatch And Equipe = 'A'";
			mysql_query($sql, $myBdd->m_link) or die ("Erreur Delete :".$sql);
					
			$sql  = "Replace Into gickp_Matchs_Joueurs ";
			$sql .= "Select $idMatch, Matric, Numero, 'A', Capitaine From gickp_Competitions_Equipes_Joueurs ";
			$sql .= "Where Id_equipe = $idEquipeA ";
			$sql .= "AND Capitaine <> 'X' ";
			$sql .= "AND Capitaine <> 'A' ";
			mysql_query($sql, $myBdd->m_link) or die ("Erreur Replace :".$sql);
		}
		if($idEquipeB != '')
		{
			$sql = "Delete From gickp_Matchs_Joueurs Where Id_match = $idMatch And Equipe = 'B'";
			mysql_query($sql, $myBdd->m_link) or die ("Erreur Delete :".$sql);
					
			$sql  = "Replace Into gickp_Matchs_Joueurs ";
			$sql .= "Select $idMatch, Matric, Numero, 'B', Capitaine From gickp_Competitions_Equipes_Joueurs ";
			$sql .= "Where Id_equipe = $idEquipeB ";
			$sql .= "AND Capitaine <> 'X' ";
			$sql .= "AND Capitaine <> 'A' ";
			mysql_query($sql, $myBdd->m_link) or die ("Erreur Replace :".$sql);
		}
	}
	$myBdd->utyJournal('MAJ titulaires journée', utyGetSaison(), utyGetSession('codeCompet', ''), '', $idJournee, '', $num_results.' m.', utyGetSession('User') );
	$resultGlobal = "Initialisation des titulaires OK pour la journée, $num_results match(s) mis à jour.";
	echo $resultGlobal;
}

function initEquipe($valeur, $valeur3)
{
	$idEquipe = $valeur;
	if($valeur3 == '')
		$idMatch = utyGetSession('idMatch', -1);
	else
		$idMatch = $valeur3;
	$lstJournee = utyGetSession('lstJournee', -1);
	$myBdd = new MyBdd();

	// Chargement des Matchs en jeux ...
	$sql  = "Select Id, Id_equipeA, Id_equipeB ";
	$sql .= "From gickp_Matchs ";
	if ($idMatch < 0)
		$sql .= "Where Id_journee In ($lstJournee) ";
	else
		$sql .= "Where Id = $idMatch ";
	$sql .= "And (Id_equipeA = $idEquipe Or Id_equipeB = $idEquipe) ";
	$sql .= "And Validation != 'O' ";
	$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load :".$sql);
	$num_results = mysql_num_rows($result);

	for ($i=0;$i<$num_results;$i++)
	{
		$row = mysql_fetch_array($result);	
		
		$idMatch = $row['Id'];
		$idEquipeA = $row['Id_equipeA'];
		$idEquipeB = $row['Id_equipeB'];
		
		if ($idEquipeA == $idEquipe)
		{
			$sql = "Delete From gickp_Matchs_Joueurs Where Id_match = $idMatch And Equipe = 'A'";
			mysql_query($sql, $myBdd->m_link) or die ("Erreur Delete :".$sql);
					
			$sql  = "Replace Into gickp_Matchs_Joueurs ";
			$sql .= "Select $idMatch, Matric, Numero, 'A', Capitaine From gickp_Competitions_Equipes_Joueurs ";
			$sql .= "Where Id_equipe = $idEquipeA ";
			$sql .= "AND Capitaine <> 'X' ";
			$sql .= "AND Capitaine <> 'A' ";
			mysql_query($sql, $myBdd->m_link) or die ("Erreur Replace :".$sql);
		}

		if ($idEquipeB == $idEquipe)
		{
			$sql = "Delete From gickp_Matchs_Joueurs Where Id_match = $idMatch And Equipe = 'B'";
			mysql_query($sql, $myBdd->m_link) or die ("Erreur Delete :".$sql);
					
			$sql  = "Replace Into gickp_Matchs_Joueurs ";
			$sql .= "Select $idMatch, Matric, Numero, 'B', Capitaine From gickp_Competitions_Equipes_Joueurs ";
			$sql .= "Where Id_equipe = $idEquipeB ";
			$sql .= "AND Capitaine <> 'X' ";
			$sql .= "AND Capitaine <> 'A' ";
			mysql_query($sql, $myBdd->m_link) or die ("Erreur Replace :".$sql);
		}
	}
	$myBdd->utyJournal('MAJ titulaires équipe', utyGetSaison(), utyGetSession('codeCompet', ''), '', '', '', 'J: '.$lstJournee.' - Eq: '.$idEquipe.' - '.$num_results.' m.', utyGetSession('User') );
	$resultGlobal = "Initialisation des titulaires OK pour cette équipe, $num_results match(s) mis à jour.";
	echo $resultGlobal;
}

/*
$debug  = "Insert Into gickp_mouchard (Valeur) Values ('";
$debug .= mysql_real_escape_string($sql);
$debug .= "')";
mysql_query($debug, $myBdd->m_link) or die ("Erreur Insert");
*/ 


//header('X-JSON:' . json_encode('success'));

?>
