<?php
include_once('../commun/MyBdd.php');
session_start();

function GetOffset($tableName, &$bdd)
{
	$myBdd = new MyBdd();
	$sql  = "Select max(Id) maxId From gickp_".$tableName;
	$result = $myBdd->Query($sql);
	$num_results = $myBdd->NumRows($result);
		
	if ($num_results >= 1)
	{
		$row = $myBdd->FetchArray($result);	 
		$max = (int) $row["maxId"];
		if ($max > 0) return $max + 1;
	}
	
	return 1;
}



function Replace_Table($tableName, &$jsonArray, &$bdd, $indexCol1=-1, $offset1=0, $indexCol2=-1, $offset2=0, $indexCol3=-1, $offset3=0 )
{
	$myBdd = new MyBdd();
	$jsonTable = $jsonArray[$tableName];
	
	$nbRows = $jsonTable['count'];
	$rowsTable = $jsonTable['rows'];
	
	for ($i=0;$i<$nbRows;$i++)
	{
		$recTable = $rowsTable[$i];
		
		if ($indexCol1 >= 0) $recTable[$indexCol1] = ((int) $recTable[$indexCol1])+$offset1;
		if ($indexCol2 >= 0) $recTable[$indexCol2] = ((int) $recTable[$indexCol2])+$offset2;
		if ($indexCol3 >= 0) $recTable[$indexCol3] = ((int) $recTable[$indexCol3])+$offset3;
				
		$sql  = "Replace Into gickp_".$tableName;
		$sql .= " Values (";
		for ($j=0;$j<count($recTable);$j++)
		{
			if ($j > 0) 
				$sql .= ',';
			$sql .= "'";
//			$sql .= mysql_real_escape_string(utf8_decode($recTable[$j]));
			$sql .= $myBdd->RealEscapeString($recTable[$j]);
			//$sql .= mysql_real_escape_string(mb_convert_encoding($recTable[$j], "ISO-8859-1", "UTF-8"));
			$sql .= "'";
		}
		$sql .= ')';
		
		$myBdd->Query($sql);
	}

	return 'Table '.$tableName.' = '.$nbRows.' <br>';
}

function Replace_Evenement(& $jsonData)
{
	$jsonData = str_replace("\\\"", "\"", $jsonData);
	if(!PRODUCTION){
		// Wamp a du mal avec les guillemets simples !
		$jsonData = str_replace("\'","'",$jsonData);
	}
	$jsonArray = json_decode($jsonData, true);
	$msg = '<br>size jsonArray = '.count($jsonArray).'<br>';
	
	$mirror = false;
	if (isset($_SESSION['mirror'])){
		if ($_SESSION['mirror'] == '1'){
			$mirror = true;
		}
	}
	
	$bdd = new MyBdd($mirror);	// True = Connexion sur le site Mirroir (poloweb5)
	
	// On fait place nette ...
	Delete_Evenement($jsonArray, $bdd);
	
	$offsetJournees = GetOffset('Journees', $bdd);
	$offsetMatch = GetOffset('Matchs', $bdd);
	$offsetMatchDetail = GetOffset('Matchs_Detail', $bdd);
	
	$msg .= '<br>offsetJournees = '.$offsetJournees;
	$msg .= '<br>offsetMatch = '.$offsetMatch;
	$msg .= '<br>offsetMatchDetail = '.$offsetMatchDetail;
	$msg .= '<br>';		
	
	if(!PRODUCTION){
		$msg .= Replace_Table('Saison', $jsonArray, $bdd);
		$msg .= Replace_Table('Competitions_Groupes', $jsonArray, $bdd);
		$msg .= Replace_Table('Club', $jsonArray, $bdd);
		$msg .= Replace_Table('Comite_dep', $jsonArray, $bdd);
		$msg .= Replace_Table('Comite_reg', $jsonArray, $bdd);
	}

	$msg .= Replace_Table('Competitions_Equipes', $jsonArray, $bdd);
	$msg .= Replace_Table('Competitions_Equipes_Init', $jsonArray, $bdd);

	$msg .= Replace_Table('Competitions', $jsonArray, $bdd);
	$msg .= Replace_Table('Competitions_Equipes_Joueurs', $jsonArray, $bdd);
	$msg .= Replace_Table('Competitions_Equipes_Niveau', $jsonArray, $bdd);
	$msg .= Replace_Table('Equipe', $jsonArray, $bdd);
	
	$msg .= Replace_Table('Evenement', $jsonArray, $bdd);
	$msg .= Replace_Table('Journees', $jsonArray, $bdd, 0, $offsetJournees);
	$msg .= Replace_Table('Evenement_Journees', $jsonArray, $bdd, 1, $offsetJournees);
	$msg .= Replace_Table('Competitions_Equipes_Journee', $jsonArray, $bdd, 1, $offsetJournees);
	
	$msg .= Replace_Table('Matchs', $jsonArray, $bdd, 0, $offsetMatch, 1, $offsetJournees );
	$msg .= Replace_Table('Matchs_Detail', $jsonArray, $bdd, 0, $offsetMatchDetail, 1, $offsetMatch);
	$msg .= Replace_Table('Matchs_Joueurs', $jsonArray, $bdd, 0, $offsetMatch);

	$msg .= '<br><br>*** TRAITEMENT TERMINE ***';	
	return $msg;
}

function Delete_Evenement(&$jsonArray, &$bdd)
{
	$myBdd = new MyBdd();
	$jsonTable = $jsonArray['Evenement'];
	$nbRows = $jsonTable['count'];
	if ($nbRows == 0) return;
	
	$lstEvenement = '-1';
	$rowsTable = $jsonTable['rows'];
	for ($i=0;$i<$nbRows;$i++)
	{
		$recTable = $rowsTable[$i];
		$lstEvenement .= ','.$recTable[0];
	}
	
	// Prise des Id Journees ...
	$sql = "Select a.Id ";
	$sql.= "From gickp_Journees a, gickp_Evenement_Journees b ";
	$sql.= "Where b.Id_evenement In ($lstEvenement) ";
	$sql.= "And a.Id = b.Id_journee ";
	
	$result = $myBdd->Query($sql);
	$num_results = $myBdd->NumRows($result);
	
	$lstJournees = '-1';
	for ($i = 0; $i < $num_results; $i++) 
	{
		$row = $myBdd->FetchArray($result);	 
		$lstJournees .= ','.$row[0];
	}
	
	// Prise des Id Match ..
	$sql = "Select Id ";
	$sql.= "From gickp_Matchs ";
	$sql.= "Where Id_journee In ($lstJournees) ";
	$result = $myBdd->Query($sql);
	$num_results = $myBdd->NumRows($result);
	
	$lstMatchs = '-1';
	for ($i = 0; $i < $num_results; $i++) 
	{
		$row = $myBdd->FetchArray($result);	 
		$lstMatchs .= ','.$row[0];
	}

	// Suppression Matchs_Detail ...
	$sql = "Delete From gickp_Matchs_Detail ";
	$sql.= "Where Id_match In ($lstMatchs) ";
	$myBdd->Query($sql);

	// Suppression Matchs_Joueurs ...
	$sql = "Delete From gickp_Matchs_Joueurs ";
	$sql.= "Where Id_match In ($lstMatchs) ";
	$myBdd->Query($sql);
	
	// Suppression Matchs ...
	$sql = "Delete From gickp_Matchs ";
	$sql.= "Where Id In ($lstMatchs) ";
	$myBdd->Query($sql);
	
	// Suppression Journees ...
	$sql = "Delete From gickp_Journees ";
	$sql.= "Where Id In ($lstJournees) ";
	$myBdd->Query($sql);
	
	// Suppression Evenement_Journees ...
	$sql = "Delete From gickp_Evenement_Journees ";
	$sql.= "Where Id_evenement In ($lstEvenement) ";
	$myBdd->Query($sql);
	
	// Suppression Evenement ...
	$sql = "Delete From gickp_Evenement ";
	$sql.= "Where Id In ($lstEvenement) ";
	$myBdd->Query($sql);
}
