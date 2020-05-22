<?php
// include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Display this code source is asked.
if (isset($_GET['source'])) exit(highlight_file(__FILE__,true));

// load the TinyButStrong libraries
include_once('../opentbs/tbs_class-3.11.0.php'); 

// load the OpenTBS plugin
include_once('../opentbs/tbs_plugin_opentbs-1.10.0.php');

$TBS = new clsTinyButStrong; // new instance of TBS
$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load OpenTBS plugin
$TBS->NoErr = true;

// Retrieve the template to use
$template = 'matchs.ods';

if (!file_exists($template)) exit("File does not exist.");

// Prepare some data for the sheet
		session_start(); 
		$myBdd = new MyBdd();
		//	echo $_SESSION['listMatch'];
		$listMatch = utyGetSession('listMatch',0);
		
		$arrayMatchs = array();
		$in = str_repeat('?,', count($listMatch) - 1) . '?';
		$sql = "SELECT a.*, b.Libelle EquipeA, c.Libelle EquipeB, d.Code_competition, 
			d.Phase, d.Niveau, d.Lieu, d.Nom LibelleJournee 
			FROM gickp_Journees d, gickp_Matchs a 
			LEFT OUTER JOIN gickp_Competitions_Equipes b ON (a.Id_equipeA = b.Id) 
			LEFT OUTER JOIN gickp_Competitions_Equipes c ON (a.Id_equipeB = c.Id) 
			WHERE a.Id IN ($in) 
			AND a.Id_journee = d.Id ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute($listMatch);
		while ($aRow = $result->fetch()) {
			$aRow['Date_match'] = utyDateUsToFr($aRow['Date_match']);
			if ($aRow['Libelle'] != '') {
				$EquipesAffectAuto = utyEquipesAffectAutoFR($aRow['Libelle']);
			}

			if (($aRow['EquipeA'] == '') && $EquipesAffectAuto[0] != '')
				$aRow['EquipeA'] = $EquipesAffectAuto[0];
			if ($aRow['EquipeB'] == '' && $EquipesAffectAuto[1] != '')
				$aRow['EquipeB'] = $EquipesAffectAuto[1];
			if ($aRow['Arbitre_principal'] != '' && $aRow['Arbitre_principal'] != '-1')
				$aRow['Arbitre_principal'] = utyArbSansNiveau($aRow['Arbitre_principal']);
			elseif ($EquipesAffectAuto[2] != '')
				$aRow['Arbitre_principal'] = $EquipesAffectAuto[2];
			if ($aRow['Arbitre_secondaire'] != '' && $aRow['Arbitre_secondaire'] != '-1')
				$aRow['Arbitre_secondaire'] = utyArbSansNiveau($aRow['Arbitre_secondaire']);
			elseif ($EquipesAffectAuto[3] != '')
				$aRow['Arbitre_secondaire'] = $EquipesAffectAuto[3];
				
			array_push($arrayMatchs, $aRow);
		}
		// Load the template
		$TBS->LoadTemplate($template, OPENTBS_ALREADY_UTF8);
		$TBS->MergeBlock('a', $arrayMatchs);
		
// Define the name of the output file
	$file_name = 'Matchs_'.date('d-m-Y').'.ods';

// Output as a download file (some automatic fields are merged here)
	$TBS->Show(OPENTBS_DOWNLOAD+TBS_EXIT, $file_name);

// Save as file on the disk (code example)
//$TBS->Show(OPENTBS_FILE+TBS_EXIT, $file_name);
