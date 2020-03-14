<?php
// TODO

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion d'une Journee

class GestionOfficiels extends MyPageSecure	 
{	
	function Load()
	{
		$myBdd = new MyBdd();
		
		// Informations pour SelectionOuiNon ...
		$_SESSION['tableOuiNon'] = 'gickp_Matchs';
		$_SESSION['columnOuiNon'] = 'Publication';
		$_SESSION['columnOuiNon2'] = 'Validation';
		$_SESSION['whereOuiNon'] = 'Where Id = ';
		
		// Chargement des Evenements ...
		$idEvenement = utyGetSession('idEvenement', -1);
		$idEvenement = utyGetPost('evenement', $idEvenement);
		$idEvenement2 = $idEvenement;
		
		$filtreJour = utyGetSession('filtreJour', '');
		$filtreJour = utyGetPost('filtreJour', $filtreJour);
		$filtreJour = utyGetGet('filtreJour', $filtreJour);
		$_SESSION['filtreJour'] = $filtreJour;
		$this->m_tpl->assign('filtreJour', $filtreJour);
		
		$filtreTerrain = utyGetSession('filtreTerrain', '');
		$filtreTerrain = utyGetPost('filtreTerrain', $filtreTerrain);
		$filtreTerrain = utyGetGet('filtreTerrain', $filtreTerrain);
		$_SESSION['filtreTerrain'] = $filtreTerrain;
		$this->m_tpl->assign('filtreTerrain', $filtreTerrain);

		$_SESSION['idEvenement'] = $idEvenement;
		$this->m_tpl->assign('idEvenement', $idEvenement);
		
		$sql  = "Select Id, Libelle, Date_debut, Publication ";
		$sql .= "From gickp_Evenement ";
		//$sql .= "Where Publication = 'O' ";
		$sql .= "Order By Date_debut DESC, Libelle ";	 
		
		$result = $myBdd->Query($sql);
		$num_results = $myBdd->NumRows($result);

		$arrayEvenement = array();
		if (-1 == $idEvenement)
			array_push($arrayEvenement, array( 'Id' => -1, 'Libelle' => '* - Tous les événements', 'Selection' => 'SELECTED' ) );
		else
			array_push($arrayEvenement, array( 'Id' => -1, 'Libelle' => '* - Tous les événements', 'Selection' => '' ) );

		while($row = $myBdd->FetchArray($result)) {
			if ($row["Publication"] == 'O')
				$PublicEvt = ' (PUBLIC)';
			else
				$PublicEvt = '';
			
			if ($row["Id"] == $idEvenement)
				array_push($arrayEvenement, array( 'Id' => $row['Id'], 'Libelle' => $row['Id'].' - '.$row['Libelle'].$PublicEvt, 'Selection' => 'SELECTED' ) );
			else
				array_push($arrayEvenement, array( 'Id' => $row['Id'], 'Libelle' => $row['Id'].' - '.$row['Libelle'].$PublicEvt, 'Selection' => '' ) );
		}
		$this->m_tpl->assign('arrayEvenement', $arrayEvenement);

		//Filtre mois
		$filtreMois = utyGetSession('filtreMois', '');
		$filtreMois = utyGetPost('filtreMois', $filtreMois);
		$_SESSION['filtreMois'] = $filtreMois;
		$this->m_tpl->assign('filtreMois', $filtreMois);
		
        //Filtre affichage type compet
		$AfficheCompet = utyGetSession('AfficheCompet','');
		$AfficheCompet = utyGetPost('AfficheCompet', $AfficheCompet);
        $_SESSION['AfficheCompet'] = $AfficheCompet;
		$this->m_tpl->assign('AfficheCompet', $AfficheCompet);

        $codeCompet = utyGetSession('codeCompet', '*');
		$codeCompet = utyGetPost('comboCompet', $codeCompet);
		$codeCompet = utyGetGet('Compet', $codeCompet);
		if($codeCompet != $_SESSION['codeCompet'] || isset($_GET['Compet']))
		{
			$this->Raz();
			$idSelJournee = '*';
			$idMatch = -1;
		}
		else
		{
			$idSelJournee = utyGetSession('idSelJournee', '*');  //ATTENTION : Comportement à surveiller
			$idSelJournee = utyGetPost('comboJournee2', $idSelJournee);
			$idSelJournee = utyGetGet('idJournee', $idSelJournee);
		//	if ($_GET['idJournee'] == '')
		//		$idSelJournee = utyGetPost('comboJournee2', $idSelJournee);  // PAS CONVAINCU !
				
			if(!isset($_SESSION['idSelJournee'])) $_SESSION['idSelJournee'] = '';
			if ($idSelJournee != $_SESSION['idSelJournee'])
				$idMatch = -1;
			else
				$idMatch = utyGetSession('idMatch', -1);

		//	if ($idSelJournee == '*')
		//		$idSelJournee = '*';//utyGetSession('idSelJournee', '*');
		//	else
		//	{
				// Chargement Initial de la page ..
		//	}
		}
		$_SESSION['idMatch'] = $idMatch;
		$_SESSION['idSelJournee'] = $idSelJournee;
		$_SESSION['codeCompet'] = $codeCompet;
		$this->m_tpl->assign('idMatch', $idMatch);
		$this->m_tpl->assign('idSelJournee', $idSelJournee);
		$this->m_tpl->assign('codeCompet', $codeCompet);
		
			
		// Chargement des Informations relatives aux Journées ...
		if ($idSelJournee != '*')
		{
			$sql  = "Select Distinct b.Id, b.Code_competition, b.Phase, b.Niveau, b.Libelle, b.Lieu, b.Date_debut, b.Type, a.Code_typeclt ";
			$sql .= "From gickp_Journees b, gickp_Competitions a ";
			$sql .= "Where b.Id = $idSelJournee ";
			$sql .= "And a.Code = b.Code_competition ";
			$sql .= utyGetFiltreCompetition('a.');			
			
			$idEvenement = -1;
		}
		else
		{
			if ($idEvenement != -1)
			{
					$sql  = "Select Distinct a.Id, a.Code_competition, a.Phase, a.Niveau, a.Libelle, a.Lieu, a.Date_debut, a.Type, c.Code_typeclt ";
					$sql .= "From gickp_Journees a, gickp_Evenement_Journees b, gickp_Competitions c ";
					$sql .= "Where a.Id = b.Id_journee ";
					$sql .= "And b.Id_evenement = $idEvenement ";
					$sql .= "And a.Code_competition = c.Code ";
					$sql .= "And a.Code_saison = c.Code_saison ";
					if ($codeCompet != '*')
						$sql .= "And a.Code_competition = '$codeCompet' ";
					$sql .= " Order by a.Code_competition, a.Date_debut, a.Niveau, a.Id ";
			}
			else
			{
					$sql  = "Select Distinct j.Id, j.Code_competition, j.Phase, j.Niveau, j.Libelle, j.Lieu, j.Date_debut, j.Type, c.Code_typeclt ";
					$sql .= "From gickp_Journees j, gickp_Competitions c ";
					if ($codeCompet != '*')
						$sql .= "Where j.Code_competition = '$codeCompet' And j.Code_saison = '";
					else
						$sql .= "Where j.Code_saison = '";
					$sql .= utyGetSaison();
					$sql .= "' ";
					if($filtreMois > 0)
						$sql .= " And (MONTH(j.Date_debut) = $filtreMois OR MONTH(j.Date_fin) = $filtreMois) ";
					$sql .= " And j.Code_competition = c.Code ";
					$sql .= " And j.Code_saison = c.Code_saison ";
					$sql .= utyGetFiltreCompetition('c.');			
					$sql .= " And c.Code_niveau Like '".utyGetSession('AfficheNiveau')."%' ";
                    if ($AfficheCompet == 'N') {
                        $sql .= " And c.Code Like 'N%' ";
                    } elseif ($AfficheCompet == 'CF') {
                        $sql .= " And c.Code Like 'CF%' ";
                    } elseif ($AfficheCompet == 'M') {
                        $sql .= " And c.Code_ref = 'M' ";
                    } elseif($AfficheCompet > 0) {
                        $sql .= " And g.section = '" . $AfficheCompet . "' ";
                    }
					$sql .= " Order by j.Code_competition, j.Date_debut, j.Niveau, j.Id ";
			}
		}
		
		$arrayJournees = array();
		$arrayJourneesAutorisees = array();
		$result = $myBdd->Query($sql);
		$num_results = $myBdd->NumRows($result);
	
		$lstJournee = '';
		if ($num_results != 0)
		{
			for ($i=0;$i<$num_results;$i++)
			{
				$row = $myBdd->FetchArray($result);	  
				array_push($arrayJournees, array( 'Id' => $row['Id'], 'Code_competition' => $row['Code_competition'], 'Code_typeclt' => $row['Code_typeclt'], 
																				'Phase' => $row['Phase'], 'Niveau' => $row['Niveau'], 'Type' => $row['Type'], 
																				'Libelle' => $row['Libelle'], 'Lieu' => $row['Lieu'], 
																				'Date_debut' => utyDateUsToFr($row['Date_debut']) ));
				if ($i > 0)
					$lstJournee .= ',';																				
				$lstJournee .= $row['Id'];
				
				// Journees autorisées seulement :
				if (utyIsAutorisationJournee($row['Id']))
				{
					array_push($arrayJourneesAutorisees, array( 'Id' => $row['Id'], 'Code_competition' => $row['Code_competition'], 'Lieu' => $row['Lieu'], 'Code_typeclt' => $row['Code_typeclt'],
																					'Type' => $row['Type'], 'Phase' => $row['Phase'], 'Niveau' => $row['Niveau'], 'Date_debut' => utyDateUsToFr($row['Date_debut']) ));
				}
				
			}
		}
		
		$_SESSION['lstJournee'] = $lstJournee;


		// Chargement des Informations relatives aux Journées pour le filtre...
		if ($idEvenement2 != -1)
		{
				$sql  = "Select Distinct a.Id, a.Code_competition, a.Phase, a.Niveau, a.Lieu, a.Date_debut, c.Code_typeclt ";
				$sql .= "From gickp_Journees a, gickp_Evenement_Journees b, gickp_Competitions c ";
				$sql .= "Where a.Id = b.Id_journee ";
				$sql .= "And a.Code_competition = c.Code ";
				$sql .= "And a.Code_saison = c.Code_saison ";
				$sql .= "And b.Id_evenement = $idEvenement2 ";
				if ($codeCompet != '*')
					$sql .= "And a.Code_competition = '$codeCompet' ";
				$sql .= " Order by a.Code_competition, a.Date_debut, a.Niveau, a.Id ";
		}
		else
		{
				$sql  = "Select Distinct j.Id, j.Code_competition, j.Phase, j.Niveau, j.Lieu, j.Date_debut, c.Code_typeclt ";
				$sql .= "From gickp_Journees j, gickp_Competitions c ";
				if ($codeCompet != '*')
					$sql .= "Where j.Code_competition = '$codeCompet' And j.Code_saison = '";
				else
					$sql .= "Where j.Code_saison = '";
				$sql .= utyGetSaison();
				$sql .= "' ";
				if($filtreMois > 0)
					$sql .= " And (MONTH(j.Date_debut) = $filtreMois OR MONTH(j.Date_fin) = $filtreMois) ";
				$sql .= " And j.Code_competition = c.Code ";
				$sql .= " And j.Code_saison = c.Code_saison ";
				$sql .= utyGetFiltreCompetition('c.');			
				$sql .= " And c.Code_niveau Like '".utyGetSession('AfficheNiveau')."%' ";
				if(utyGetSession('AfficheCompet') == 'NCF')
					$sql .= " And (c.Code Like 'N%' OR c.Code Like 'CF%') ";
				else
					$sql .= " And c.Code Like '".utyGetSession('AfficheCompet')."%' ";
				$sql .= " Order by j.Code_competition, j.Date_debut, j.Niveau, j.Id ";
		}
		
		$arrayJourneesAutoriseesFiltre = array();
		$result = $myBdd->Query($sql);
		$num_results = $myBdd->NumRows($result);
		$PhaseLibelle = 0;
		
		if ($num_results != 0)
		{
			for ($i=0;$i<$num_results;$i++)
			{
				$row = $myBdd->FetchArray($result);
				// S'il n'y a qu'une seule compétition et de type CP, on affichera les phases
				if ($codeCompet != '*' && $row['Code_typeclt'] == 'CP')
					$PhaseLibelle = 1;
				// Journees autorisées seulement :
				if (utyIsAutorisationJournee($row['Id']))
				{
					array_push($arrayJourneesAutoriseesFiltre, array( 'Id' => $row['Id'], 'Code_competition' => $row['Code_competition'], 'Lieu' => $row['Lieu'], 'Code_typeclt' => $row['Code_typeclt'],
																					'Phase' => $row['Phase'], 'Niveau' => $row['Niveau'], 'Date_debut' => utyDateUsToFr($row['Date_debut']) ));
				}
				
			}
		}
		
		// Chargement des Competitions relatives à l'Evenement ...
		$arrayCompet = array();
			
		if ($idEvenement != -1)
		{
			array_push($arrayCompet, array( 'Code' => '*', 'Libelle' => 'Toutes les compétitions de l\'événement'));
			$sql  = "Select Distinct c.GroupOrder, c.Code, c.Libelle, c.Soustitre, c.Soustitre2, c.Titre_actif ";
			$sql .= "From gickp_Evenement_Journees a, gickp_Journees b, gickp_Competitions c "; 
			$sql .= "Where a.Id_evenement = $idEvenement ";
			$sql .= "And a.Id_journee = b.Id  ";
			$sql .= "And b.Code_competition = c.Code ";
			$sql .= "And b.Code_saison = c.Code_saison ";
			$sql .= "Order By c.GroupOrder, c.Code ";
			
		}
		else
		{
			array_push($arrayCompet, array( 'Code' => '*', 'Libelle' => 'Toutes les compétitions sélectionnées'));
			$sql  = "Select Distinct c.GroupOrder, c.Code, c.Libelle, c.Code_niveau, c.Code_ref, c.Code_tour, c.Soustitre, c.Soustitre2, c.Titre_actif ";
			$sql .= "From gickp_Competitions c, gickp_Competitions_Groupes g ";
			$sql .= "Where c.Code_saison = '";
			$sql .= utyGetSaison();
			$sql .= "' ";
			$sql .= utyGetFiltreCompetition('');
			$sql .= " And c.Code_niveau Like '".utyGetSession('AfficheNiveau')."%' ";
            if ($AfficheCompet == 'N') {
                $sql .= " And c.Code Like 'N%' ";
            } elseif ($AfficheCompet == 'CF') {
                $sql .= " And c.Code Like 'CF%' ";
            } elseif ($AfficheCompet == 'M') {
                $sql .= " And c.Code_ref = 'M' ";
            } elseif($AfficheCompet > 0) {
                $sql .= " And g.section = '" . $AfficheCompet . "' ";
            }
			$sql .= " And c.Code_ref = g.Groupe ";
			$sql .= " Order By c.Code_saison, c.Code_niveau, g.Id, COALESCE(c.Code_ref, 'z'), c.Code_tour, c.GroupOrder, c.Code ";	 
		}
		$result = $myBdd->Query($sql);
		$num_results = $myBdd->NumRows($result);
		while($row = $myBdd->FetchArray($result)) {
		// Titre
			if($row["Titre_actif"] != 'O' && $row["Soustitre"] != '')
				$Libelle = $row["Soustitre"];
			else
				$Libelle = $row["Libelle"];
			if($row["Soustitre2"] != '')
				$Libelle .= ' - '.$row["Soustitre2"];

			array_push($arrayCompet, array( 'Code' => $row['Code'], 'Libelle' => $Libelle ));
		}

		// Initialisation Date du match ...
		if (!isset($_POST['Date_match']))
		{
				if (strlen(utyGetSession('Date_match')) == 0)
				{
					if (count($arrayJournees) >= 1)
						$_SESSION['Date_match'] = $arrayJournees[0]['Date_debut'];
				}
		}

		// Sous-titre
		$headerSubTitle = '';
		if ( (count($arrayJournees) == 1))// || ($idEvenement == -1) )
		{
			$headerSubTitle = $arrayJournees[0]['Code_competition'];
			if (strlen($arrayJournees[0]['Phase']) > 0)
				$headerSubTitle .= '/'.$arrayJournees[0]['Phase'].' (Niveau '.$arrayJournees[0]['Niveau'].')';
			$headerSubTitle .= ' - '.$arrayJournees[0]['Libelle'].' - '.$arrayJournees[0]['Date_debut'];
		}
		else  //if ($idEvenement != -1)
		{
			// Chargement Evenement ...
			$sql  = "Select Libelle, Lieu, Date_debut, Date_fin ";
			$sql .= "From gickp_Evenement ";
			$sql .= "Where Id = $idEvenement";
			$result = $myBdd->Query($sql);
			if ($myBdd->NumRows($result) == 1)
			{
				$row = $myBdd->FetchArray($result);	  	
				$headerSubTitle = '<span class="highlight4">'.$row['Libelle'].'</span>&nbsp;>&nbsp;';
			}	
			if ($codeCompet != '*')
				$headerSubTitle .= '<span class="highlight3">'.$codeCompet.'</span>';
			else
				$headerSubTitle .= '<span>Toutes les compétitions</span>';
		}
		
		
		$this->m_tpl->assign('headerSubTitle', $headerSubTitle);
		
		// Ordre des Matchs 
		$orderMatchs = utyGetSession('orderMatchs', 'Order By a.Date_match, a.Heure_match, a.Terrain');
		$orderMatchs = utyGetPost('orderMatchs', $orderMatchs);
		$_SESSION['orderMatchs'] = $orderMatchs;
		
		$arrayOrderMatchs = array();
		
		// variable à initialiser dans tous les cas : @COSANDCO_WAMPSERVER
		if (!isset($selected))
			$selected = '';
		
		array_push($arrayOrderMatchs, array( 'Key' => 'Order By a.Date_match, a.Heure_match, a.Terrain, a.Numero_ordre', 'Value' => 'Par Date, Heure et Terrain'));
		array_push($arrayOrderMatchs, array( 'Key' => 'Order By d.Code_competition, a.Date_match, a.Heure_match, a.Terrain, a.Numero_ordre', 'Value' => 'Par Compétittion et Date'));
		array_push($arrayOrderMatchs, array( 'Key' => 'Order By d.Code_competition, d.Niveau, d.Phase, a.Heure_match, a.Terrain, a.Numero_ordre', 'Value' => 'Par Compétittion et Phase'));
		array_push($arrayOrderMatchs, array( 'Key' => 'Order By a.Terrain, a.Date_match, a.Heure_match, a.Numero_ordre', 'Value' => 'Par Terrain et Date'));
		array_push($arrayOrderMatchs, array( 'Key' => 'Order By a.Numero_ordre, a.Date_match, a.Heure_match, a.Terrain', 'Value' => 'Par Numéro', 'Selected' => $selected ));

		$this->m_tpl->assign('orderMatchs', $orderMatchs);
		$this->m_tpl->assign('arrayOrderMatchs', $arrayOrderMatchs);

		$orderMatchsKey1 = utyKeyOrder($orderMatchs, 0);
		$this->m_tpl->assign('orderMatchsKey1', $orderMatchsKey1);
		
		// Prise du Match "Selection"
//		$idMatch = utyGetSession('idMatch', -1);
		$idJournee = utyGetSession('idJournee', 0);
		
		$dateDebut = '';
		$dateFin = '';
		$arrayMatchs = array();
		$arrayJours = array();
		
		if ($lstJournee != '')
		{
			// Chargement des Matchs des journées ...
			$sql  = "Select a.Id, a.Id_journee, a.Numero_ordre, a.Date_match, a.Heure_match, a.Libelle, a.Terrain, a.Publication, a.Validation, ";
			$sql .= "a.Statut, a.Type, a.Periode, a.ScoreDetailA, a.ScoreDetailB, ";
			$sql .= "b.Libelle EquipeA, c.Libelle EquipeB, a.Id_equipeA, a.Id_equipeB, ";
			$sql .= "a.Terrain, a.ScoreA, a.ScoreB, a.CoeffA, a.CoeffB, ";
			$sql .= "a.Arbitre_principal, a.Arbitre_secondaire, a.Matric_arbitre_principal, a.Matric_arbitre_secondaire, ";
			$sql .= "a.Secretaire, a.Chronometre, a.Timeshoot, a.Ligne1, a.Ligne2, ";
			$sql .= "d.Code_competition, d.Phase, d.Niveau, d.Lieu, d.Libelle LibelleJournee, ";
			$sql .= "e.Soustitre2 ";
			$sql .= "From gickp_Matchs a ";
			$sql .= "Left Outer Join gickp_Competitions_Equipes b On (a.Id_equipeA = b.Id) "; 
			$sql .= "Left Outer Join gickp_Competitions_Equipes c On (a.Id_equipeB = c.Id) ";
			$sql .= ", gickp_Journees d, gickp_Competitions e ";
			$sql .= "Where a.Id_journee In ($lstJournee) ";
			$sql .= "And a.Id_journee = d.Id ";
			$sql .= "And d.Code_competition = e.Code ";
			$sql .= "And d.Code_saison = e.Code_saison ";
			if($filtreTerrain != '')
			{
				$sql .= "And a.Terrain = '".$filtreTerrain."' ";
			}
			$sql .= $orderMatchs;
			
			$result = $myBdd->Query($sql);
            $num_results = $myBdd->NumRows($result);
			
			// Variables à initialiser : @COSANDCO_WAMPSERVER
			$listMatch = '';
			$jourmatch = '';
			
			$listeJours = array();
			
			for ($i=0;$i<$num_results;$i++)
			{
				$row = $myBdd->FetchArray($result);	  
				
				$jour = $row['Date_match'];
				$listeJours[$jour] = utyDateUsToFr($jour);
				if($filtreJour == '' || $jour == $filtreJour)
				{
					if ($row['Libelle'] != '')
					{
						$EquipesAffectAuto = utyEquipesAffectAutoFR($row['Libelle']);
					}

					if (($row['EquipeA'] == '') && isset($EquipesAffectAuto[0]) && $EquipesAffectAuto[0] != '')
						$row['EquipeA'] = $EquipesAffectAuto[0];
					if ($row['EquipeB'] == '' && isset($EquipesAffectAuto[1]) && $EquipesAffectAuto[1] != '')
						$row['EquipeB'] = $EquipesAffectAuto[1];
					$arbsup = array(" (Pool Arbitres 1)", " (Pool Arbitres 2)");//   , " REG", " NAT", " INT", "-A", "-B", "-C"
					if($row['Arbitre_principal'] != '' && $row['Arbitre_principal'] != '-1')
						$row['Arbitre_principal'] = str_replace($arbsup, '', $row['Arbitre_principal']);
					elseif (isset($EquipesAffectAuto[2]) && $EquipesAffectAuto[2] != '')
						$row['Arbitre_principal'] = $EquipesAffectAuto[2];
					if($row['Arbitre_secondaire'] != '' && $row['Arbitre_secondaire'] != '-1')
						$row['Arbitre_secondaire'] = str_replace($arbsup, '', $row['Arbitre_secondaire']);
					elseif (isset($EquipesAffectAuto[3]) && $EquipesAffectAuto[3] != '')
						$row['Arbitre_secondaire'] = $EquipesAffectAuto[3];
					
					$StdOrSelected = 'Std';
					if ($idMatch == $row['Id'])
						$StdOrSelected = 'Selected';
						
					$Publication = 'O';
					if ($row['Publication'] != 'O')
						$Publication = 'N';
						
					$Validation = 'O';
					if ($row['Validation'] != 'O')
						$Validation = 'N';
					
					$MatchAutorisation = 'O';
					if (!utyIsAutorisationJournee($row['Id_journee']))
						$MatchAutorisation = 'N';

					array_push($arrayMatchs, array( 'Id' => $row['Id'], 'Id_journee' => $row['Id_journee'], 'Numero_ordre' => $row['Numero_ordre'],
								//'Periode' => $Periode, 'PeriodeTitle' => $PeriodeTitle, 
								'ScoreDetailA' => $row['ScoreDetailA'], 'ScoreDetailB' => $row['ScoreDetailB'], 
								'Statut' => $row['Statut'], 'Periode' => $row['Periode'], 'Type' => $row['Type'],
								'Date_match' => utyDateUsToFr($row['Date_match']), 'Heure_match' => $row['Heure_match'],
								'Libelle' => $row['Libelle'], 'Terrain' => $row['Terrain'], 
								'EquipeA' => $row['EquipeA'], 'EquipeB' => $row['EquipeB'],
								'Id_equipeA' => $row['Id_equipeA'], 'Id_equipeB' => $row['Id_equipeB'], 
								'ScoreA' => $row['ScoreA'], 'ScoreB' => $row['ScoreB'], 
								'CoeffA' => $row['CoeffA'], 'CoeffB' => $row['CoeffB'],
								'Arbitre_principal' => $row['Arbitre_principal'], 
								'Arbitre_secondaire' => $row['Arbitre_secondaire'],
								'Matric_arbitre_principal' => $row['Matric_arbitre_principal'],
								'Matric_arbitre_secondaire' => $row['Matric_arbitre_secondaire'],
								'Secretaire' => $row['Secretaire'],
								'Chronometre' => $row['Chronometre'],
								'Timeshoot' => $row['Timeshoot'],
								'Ligne1' => $row['Ligne1'],
								'Ligne2' => $row['Ligne2'],
								'Code_competition' => $row['Code_competition'],
								'Soustitre2' => $row['Soustitre2'],
								'Phase' => $row['Phase'],
								'Niveau' => $row['Niveau'],
								'Lieu' => $row['Lieu'],
								'LibelleJournee' => $row['LibelleJournee'],
								'StdOrSelected' => $StdOrSelected,
								'MatchAutorisation' => $MatchAutorisation,
								'Publication' => $Publication,
								'Validation' => $Validation	));
					
					if($listMatch != '')
						$listMatch .= ',';
					$listMatch .= $row['Id'];
								
					if ($row['Phase'] != '' && $row['Libelle'] != '')
						$PhaseLibelle = 1;
																					
					if ($i == 0)
					{
						$dateDebut = utyDateUsToFr($row['Date_match']);
						$dateFin = utyDateUsToFr($row['Date_match']);
					}																				
					else
					{
						if (utyDateCmpFr($dateDebut, utyDateUsToFr($row['Date_match'])) > 0)
							$dateDebut = utyDateUsToFr($row['Date_match']);
							
						if (utyDateCmpFr($dateFin, utyDateUsToFr($row['Date_match'])) < 0)
							$dateFin = utyDateUsToFr($row['Date_match']);
					}
					
					if($jourmatch != $row['Date_match'])
						array_push($arrayJours, utyDateUsToFr($row['Date_match']));
					$jourmatch = $row['Date_match'];
				}
			}
			$this->m_tpl->assign('listeJours', $listeJours);
			$this->m_tpl->assign('listMatch', $listMatch);
			$_SESSION['listMatch'] = $listMatch; 
			$this->m_tpl->assign('arrayMatchs', $arrayMatchs);
			$this->m_tpl->assign('arrayJours', $arrayJours);
		}
		
		$this->m_tpl->assign('PhaseLibelle', $PhaseLibelle);
		
		$_SESSION['dateDebutEvenement'] = $dateDebut;
		$_SESSION['dateFinEvenement'] = $dateFin;
		
		
		if ( ($idMatch < 0) && (count($arrayMatchs) >= 1) )
		{
//			$idMatch = $arrayMatchs[0]['Id'];
//			$idJournee = $arrayMatchs[0]['Id_journee'];
		}			

		// Chargement des Equipes A et B ...
		if ($idMatch < 0 && $lstJournee != '')
		{
			$sql  = "Select Distinct a.Id, a.Libelle, a.Poule, a.Tirage, a.Code_compet ";
			$sql .= "From gickp_Competitions_Equipes a, gickp_Journees b ";
			$sql .= "Where a.Code_compet = b.Code_competition ";
			$sql .= "And a.Code_saison = b.Code_saison ";
			$sql .= "And b.Id In ($lstJournee) ";
			$sql .= " Order By a.Poule, a.Tirage, a.Libelle ";	 
		}
		elseif ($idMatch >= 0)
		{
			$sql  = "Select a.Id, a.Libelle, a.Poule, a.Tirage, a.Code_compet ";
			$sql .= "From gickp_Competitions_Equipes a, gickp_Journees b, gickp_Matchs c ";
			$sql .= "Where a.Code_compet = b.Code_competition ";
			$sql .= "And a.Code_saison = b.Code_saison ";
			$sql .= "And b.Id = c.Id_journee ";
			$sql .= "And c.Id = $idMatch ";
			$sql .= " Order By a.Poule, a.Tirage, a.Libelle ";	 
		}
			
		if ($lstJournee != '')
		{
			$result = $myBdd->Query($sql);
            $num_results = $myBdd->NumRows($result);

			$Id_equipeA = utyGetSession('Id_equipeA', -1);
			$Id_equipeB = utyGetSession('Id_equipeB', -1);

			$arrayEquipeA = array();
			$arrayEquipeB = array();
			$arrayArbitre = array();
			
			//ARBITRES
			// Les arbitres peuvent être des équipes	
			array_push($arrayArbitre, array('Matric' => '-1', 'Identite' => '       (Pool Arbitres en fin de liste)'));
			array_push($arrayArbitre, array('Matric' => '-1', 'Identite' => '---------- Equipes ----------'));
		
			for ($i=0;$i<$num_results;$i++)
			{
				$row = $myBdd->FetchArray($result);	  
				
				$libelleEquipe = $row['Libelle'];
				$codeCompetition = $row['Code_compet'];
				
				if ($row['Id'] == $Id_equipeA)
					array_push($arrayEquipeA, array('Id' => $row['Id'], 'Libelle' => $libelleEquipe, 'Poule' => $row['Poule'], 'Code_compet'=> $codeCompetition, 'Selection' => 'SELECTED'));
				else
					array_push($arrayEquipeA, array('Id' => $row['Id'], 'Libelle' => $libelleEquipe, 'Poule' => $row['Poule'], 'Code_compet'=> $codeCompetition, 'Selection' => ''));

				if ($row['Id'] == $Id_equipeB)
					array_push($arrayEquipeB, array('Id' => $row['Id'], 'Libelle' => $libelleEquipe, 'Poule' => $row['Poule'], 'Code_compet'=> $codeCompetition, 'Selection' => 'SELECTED'));
				else
					array_push($arrayEquipeB, array('Id' => $row['Id'], 'Libelle' => $libelleEquipe, 'Poule' => $row['Poule'], 'Code_compet'=> $codeCompetition, 'Selection' => ''));
				array_push($arrayArbitre, array('Matric' => '', 'Identite' => $libelleEquipe));
			}

			// Les arbitres potentiels peuvent aussi être les joueurs des Equipes ...
			array_push($arrayArbitre, array('Matric' => '-1', 'Identite' => ''));
			array_push($arrayArbitre, array('Matric' => '-1', 'Identite' => '---------- Joueurs ----------'));
			$sql  = "Select a.Matric, a.Nom, a.Prenom, b.Libelle, c.Arb, c.niveau, (c.Arb IS NULL) AS sortCol ";
			$sql .= "From gickp_Competitions_Equipes_Joueurs a left outer join gickp_Arbitre c on a.Matric = c.Matric, gickp_Competitions_Equipes b  ";
			$sql .= "Where a.Id_equipe = b.Id ";

			$sql .= "AND b.Id In (-1";
			for ($i=0;$i<count($arrayEquipeA);$i++)
			{
				$sql .= ",";
				$sql .= $arrayEquipeA[$i]['Id'];
			}
			$sql .= ") AND a.Capitaine <> 'X' ";
			$sql .= "Order By b.Libelle, sortCol, c.Arb, a.Nom, a.Prenom ";
		
			$result = $myBdd->Query($sql);
            $num_results = $myBdd->NumRows($result);
			
			$libelleTemp = '';
			
			for ($i=0;$i<$num_results;$i++)
			{
				$row = $myBdd->FetchArray($result);
				if ($row['Libelle'] != $libelleTemp)
				{
					array_push($arrayArbitre, array('Matric' => '-1', 'Identite' => '---'));
					$libelleTemp = $row['Libelle'];
				}
				if (strlen($row['Arb'])>0)
					$arb = ' '.strtoupper($row['Arb']);
				else
					$arb = '';
				if($row['niveau'] != '')
					$arb .= '-'.$row['niveau'];
				array_push($arrayArbitre, array('Matric' => $row['Matric'], 'Identite' => ucwords(strtolower($row['Nom'])).' '.ucwords(strtolower($row['Prenom'])).' ('.$row['Libelle'].')'.$arb));
			}
			
			// Les arbitres potentiels font partie du Pool ...
			array_push($arrayArbitre, array('Matric' => '-1', 'Identite' => ''));
			array_push($arrayArbitre, array('Matric' => '-1', 'Identite' => '---------- Pool Arbitres ----------'));
			$sql2  = "Select a.Matric, a.Nom, a.Prenom, b.Libelle, c.Arb, c.niveau ";
			$sql2 .= "From gickp_Competitions_Equipes_Joueurs a left outer join gickp_Arbitre c on a.Matric = c.Matric, gickp_Competitions_Equipes b  ";
			$sql2 .= "Where a.Id_equipe = b.Id ";
			$sql2 .= "And b.Code_compet = 'POOL' ";
			$sql2 .= "Order By a.Nom, a.Prenom ";
			$result2 = $myBdd->Query($sql2);
            while($row2 = $myBdd->FetchArray($result2)) {
				if (strlen($row2['Arb'])>0)
					$arb = ' '.strtoupper($row2['Arb']);
				else
					$arb = '';
				if($row2['niveau'] != '')
					$arb .= '-'.$row2['niveau'];
				$row2['Libelle'] = substr($row2['Libelle'],0,3);
				array_push($arrayArbitre, array('Matric' => $row2['Matric'], 'Identite' => ucwords(strtolower($row2['Nom'])).' '.ucwords(strtolower($row2['Prenom'])).' ('.$row2['Libelle'].')'.$arb));
			}
			
			$this->m_tpl->assign('arrayEquipeA', $arrayEquipeA);
			$this->m_tpl->assign('arrayEquipeB', $arrayEquipeB);
			$this->m_tpl->assign('arrayArbitre', $arrayArbitre);

			$this->m_tpl->assign('idCurrentJournee', $idJournee);
			$this->m_tpl->assign('arrayJournees', $arrayJournees);
			$this->m_tpl->assign('arrayJourneesAutorisees', $arrayJourneesAutorisees);
			$this->m_tpl->assign('arrayJourneesAutoriseesFiltre', $arrayJourneesAutoriseesFiltre);
		}

		$this->m_tpl->assign('codeCurrentCompet', $codeCompet);
		$this->m_tpl->assign('arrayCompet', $arrayCompet);
		
		if(($idEvenement == -1) && ($codeCompet == '*') && ($idSelJournee == '*') && ($_SESSION['Profile'] < 4))
		{
			$TropDeMatchs = 'disabled';
			$TropDeMatchsMsg = ' (TROP DE MATCHS SELECTIONNES)';
		}
		else
		{
			$TropDeMatchs = '' ;
			$TropDeMatchsMsg = '';
		}
		$this->m_tpl->assign('TropDeMatchs', $TropDeMatchs);
		$this->m_tpl->assign('TropDeMatchsMsg', $TropDeMatchsMsg);
	}
	
	function Raz()
	{
		$_SESSION['idMatch'] = -1;
		$idJournee = utyGetSession('idJournee', '*');
		//$idJournee = utyGetPost('idJournee', $idJournee);
		
		$_SESSION['Intervalle_match'] = utyGetSession('Intervalle_match',40);
		if (isset($_POST['Intervalle_match']))
			$_SESSION['Intervalle_match'] = $_POST['Intervalle_match'];
		
		$myBdd = new MyBdd();
		// Chargement des Matchs des journées ...
		$sql  = "Select Numero_ordre, Date_match, Heure_match, Terrain, Type ";
		$sql .= "From gickp_Matchs ";
		$sql .= "Where Id_journee = '$idJournee' ";
		$sql .= "Order by Date_match, Heure_match, Numero_ordre ";
		$result = $myBdd->Query($sql);
		$num_results = $myBdd->NumRows($result);
		while($row = $myBdd->FetchArray($result)) {
			$lastNumOrdre = $row['Numero_ordre'];
			$lastDate = $row['Date_match'];
			$lastHeure = $row['Heure_match'];
			$lastTerrain = $row['Terrain'];
			$lastType = $row['Type'];
		}
		if($num_results > 0)
		{
			$_SESSION['Date_match'] = utyDateUsToFr($lastDate);
			$_SESSION['Heure_match'] = utyTimeInterval($lastHeure, utyGetSession('Intervalle_match'));
			$_SESSION['Num_match'] = $lastNumOrdre+1;
			$_SESSION['Terrain'] = $lastTerrain;
			$_SESSION['Type'] = $lastType;
		}
		$_SESSION['Libelle'] = '';
		$_SESSION['Id_equipeA'] = -1;
		$_SESSION['Id_equipeB'] = -1;
		$_SESSION['arbitre1'] = '';
		$_SESSION['arbitre2'] = '';
		$_SESSION['arbitre1_matric'] = '';
		$_SESSION['arbitre2_matric'] = '';
		$_SESSION['coeffA'] = '';
		$_SESSION['coeffB'] = '';
	}
	
	function Update()
	{
		$myBdd = new MyBdd();
        
        $idMatch = utyGetSession('idMatch', 0);
		
		$_SESSION['Intervalle_match'] = utyGetSession('Intervalle_match','40');
		if (isset($_POST['Intervalle_match']))
			$_SESSION['Intervalle_match'] = $_POST['Intervalle_match'];
		
		$idJournee = (int)utyGetPost('comboJournee', 0);

		$numMatch = (int)utyGetPost('Num_match', '');
		$dateMatch = $myBdd->RealEscapeString(trim(utyGetPost('Date_match', '')));
		$heureMatch = $myBdd->RealEscapeString(trim(utyGetPost('Heure_match', '')));
		$Libelle = $myBdd->RealEscapeString(trim(utyGetPost('Libelle', '')));
		$Terrain = $myBdd->RealEscapeString(trim(utyGetPost('Terrain', '')));
		$Type = $myBdd->RealEscapeString(trim(utyGetPost('Type', '')));
		
		$idEquipeA = (int)utyGetPost('idEquipeA', -1);
		$idEquipeB = (int)utyGetPost('idEquipeB', -1);
	
		$arbitre1 = $myBdd->RealEscapeString(trim(utyGetPost('arbitre1', '')));
		if (strlen($arbitre1) == 0)
			$arbitre1 = $myBdd->RealEscapeString(trim(utyGetPost('comboarbitre1', '')));
		$arbitre1_matric = (int)utyGetPost('arbitre1_matric', '');
			
		$arbitre2 = $myBdd->RealEscapeString(trim(utyGetPost('arbitre2', '')));
		if (strlen($arbitre2) == 0)
			$arbitre2 = $myBdd->RealEscapeString(trim(utyGetPost('comboarbitre2', '')));
		$arbitre2_matric = (int)utyGetPost('arbitre2_matric', '');
			
		$coeffA = (float)utyGetPost('coeffA', 1);
		if (strlen($coeffA) == 0 || $coeffA == 0)
			$coeffA = 1.0;
			
		$coeffB = (float)utyGetPost('coeffB', 1);
		if (strlen($coeffB) == 0 || $coeffB == 0)
			$coeffB = 1.0;
		
		if ( ($idMatch > 0) && ($idJournee != 0) )
		{
			if (strlen($numMatch) == 0)
				$numMatch = $this->LastNumeroOrdre($idJournee) + 1;
			
			$sql  = "Select Id_equipeA, Id_equipeB From gickp_Matchs Where Id = $idMatch ";
			$result = $myBdd->Query($sql);
			$anciene_equipeA = mysql_result($result , 0 , "Id_equipeA");
			$anciene_equipeB = mysql_result($result , 0 , "Id_equipeB");

			$sql  = "Update gickp_Matchs Set Id_journee = $idJournee, Numero_ordre = $numMatch, Date_match='";
			$sql .= utyDateFrToUs($dateMatch);
			$sql .= "', Heure_match='$heureMatch', Libelle='$Libelle', Terrain='$Terrain', Type='$Type', Id_equipeA=$idEquipeA, Id_equipeB=$idEquipeB, ";
			$sql .= "Arbitre_principal='";
			$sql .= $arbitre1;
			$sql .= "', Arbitre_secondaire='";
			$sql .= $arbitre2;
			$sql .= "',Matric_arbitre_principal='$arbitre1_matric', Matric_arbitre_secondaire='$arbitre2_matric', ";
			$sql .= "CoeffA = $coeffA, CoeffB = $coeffB ";
			$sql .= "Where Id = $idMatch ";
			$sql .= "And Validation != 'O' ";
	
			mysql_query($sql, $myBdd->m_link) or die ("Erreur Update");
			
			//Vidage des joueurs si l'équipe est vide ou modifiée
			if($idEquipeA == -1 or $idEquipeA != $anciene_equipeA)
			{
				$sql  = "Delete From gickp_Matchs_Joueurs ";
				$sql .= "Where Id_match = $idMatch ";
				$sql .= "And Equipe = 'A' ";
				mysql_query($sql, $myBdd->m_link) or die ("Erreur Delete => ".$sql);
			}
			if($idEquipeB == -1 or $idEquipeB != $anciene_equipeB)
			{
				$sql  = "Delete From gickp_Matchs_Joueurs ";
				$sql .= "Where Id_match = $idMatch ";
				$sql .= "And Equipe = 'B' ";
				mysql_query($sql, $myBdd->m_link) or die ("Erreur Delete => ".$sql);
			}
			
			$this->Raz();
			
		}
		
		$myBdd->utyJournal('Modification match', '', '', 'NULL', $idJournee, $idMatch);
	}
			
	function Add()
	{
		$myBdd = new MyBdd();
		$idJournee = (int)utyGetPost('comboJournee', 0);
		
		$numMatch = (int)utyGetPost('Num_match', '');
		$dateMatch = $myBdd->RealEscapeString(trim(utyGetPost('Date_match', '')));
		$heureMatch = $myBdd->RealEscapeString(trim(utyGetPost('Heure_match', '')));
		$Libelle = $myBdd->RealEscapeString(trim(utyGetPost('Libelle', '')));
		$Terrain = $myBdd->RealEscapeString(trim(utyGetPost('Terrain', '')));
		$Type = $myBdd->RealEscapeString(trim(utyGetPost('Type', '')));
				
		$idEquipeA = (int)utyGetPost('idEquipeA', -1);
		$idEquipeB = (int)utyGetPost('idEquipeB', -1);
		
		$arbitre1 = $myBdd->RealEscapeString(trim(utyGetPost('arbitre1', '')));
		if (strlen($arbitre1) == 0) {
            $arbitre1 = $myBdd->RealEscapeString(trim(utyGetPost('comboarbitre1', '')));
        }
        $arbitre1_matric = $myBdd->RealEscapeString(trim(utyGetPost('arbitre1_matric', '')));
					
		$arbitre2 = $myBdd->RealEscapeString(trim(utyGetPost('arbitre2', '')));
		if (strlen($arbitre2) == 0) {
            $arbitre2 = $myBdd->RealEscapeString(trim(utyGetPost('comboarbitre2', '')));
        }
        $arbitre2_matric = $myBdd->RealEscapeString(trim(utyGetPost('arbitre2_matric', '')));
		
		$coeffA = (float)utyGetPost('coeffA', 1);
		if (strlen($coeffA) == 0) {
            $coeffA = 1.0;
        }

        $coeffB = (float)utyGetPost('coeffB', 1);
		if (strlen($coeffB) == 0) {
            $coeffB = 1.0;
        }

        if ($idJournee != 0)
		{
			if (strlen($numMatch) == 0) {
                $numMatch = $this->LastNumeroOrdre($idJournee) + 1;
            }

            $sql  = "Insert Into gickp_Matchs (Id_journee, Numero_ordre, Date_match, Heure_match, Libelle, Terrain, Type, ";
			$sql .= "Id_equipeA, Id_equipeB, ScoreA, ScoreB, Arbitre_principal, Arbitre_secondaire, Matric_arbitre_principal, Matric_arbitre_secondaire, CoeffA, CoeffB) Values (";
			$sql .= $idJournee;
			$sql .= ",";
			$sql .= $numMatch;
			$sql .= ",'";
			$sql .=  utyDateFrToUs($dateMatch);
			$sql .= "','";
			$sql .= $heureMatch;
			$sql .= "','";
			$sql .= $Libelle;
			$sql .= "','";
			$sql .= $Terrain;
			$sql .= "','";
			$sql .= $Type;
			$sql .= "',";
			$sql .= $idEquipeA;
			$sql .= ",";
			$sql .= $idEquipeB;
			$sql .= ",'?','?','";
			$sql .= $arbitre1;
			$sql .= "','";
			$sql .= $arbitre2;
			$sql .= "','";
			$sql .= $arbitre1_matric;
			$sql .= "','";
			$sql .= $arbitre2_matric;
			$sql .= "',";
			$sql .= $coeffA;
			$sql .= ",";
			$sql .= $coeffB;
			$sql .= ")";
		
			mysql_query($sql, $myBdd->m_link) or die ("Erreur insert <br /><br />".$sql);
		}
		
		$_SESSION['Intervalle_match'] = utyGetSession('Intervalle_match','40');
		if (isset($_POST['Intervalle_match']))
			$_SESSION['Intervalle_match'] = $_POST['Intervalle_match'];
		
		$myBdd->utyJournal('Ajout match', '', '', 'NULL', $idJournee, $numMatch, $dateMatch.' '.$heureMatch);
		
		$_SESSION['idJournee'] = $idJournee;
		$this->Raz();		
	}
	
	function Remove()
	{
		$ParamCmd = '';
		if (isset($_POST['ParamCmd']))
			$ParamCmd = $_POST['ParamCmd'];
			
		$arrayParam = split ('[,]', $ParamCmd);		
		if (count($arrayParam) == 0)
			return; // Rien à Detruire ...
		
		$myBdd = new MyBdd();
		
		//Contrôle suppression possible
		$sql = "Select Id From gickp_Matchs_Detail Where Id_match In ($ParamCmd) ";

		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Select");
		if (mysql_num_rows($result) != 0 && $_SESSION['Profile'] != 1)
			die ("Il reste des évènements dans ces matchs ! Suppression impossible (<a href='javascript:history.back()'>Retour</a>)");
		
		//Vidage des joueurs du match
		$sql  = "DELETE FROM gickp_Matchs_Joueurs USING gickp_Matchs_Joueurs, gickp_Matchs "
                . "WHERE gickp_Matchs_Joueurs.Id_match = gickp_Matchs.Id "
                . "AND gickp_Matchs_Joueurs.Id_match In ($ParamCmd) "
                . "AND gickp_Matchs.Validation != 'O'; ";
		mysql_query($sql, $myBdd->m_link) or die ("Erreur Delete joueurs matchs<br />".$sql);
        
		// Suppression
		$sql  = "Delete From gickp_Matchs Where Id In ($ParamCmd) ";
		$sql .= "And Validation != 'O' ";
		mysql_query($sql, $myBdd->m_link) or die ("Erreur Delete matchs<br />".$sql);
		
		$myBdd->utyJournal('Suppression matchs', '', '', 'NULL', 'NULL', $ParamCmd);
	}
	
	function LastNumeroOrdre($idJournee)
	{
		$myBdd = new MyBdd();
		
		$sql  = "Select Code_competition, Code_saison, Date_debut ";
		$sql .= "From gickp_Journees ";
		$sql .= "Where Id = ";
		$sql .= $idJournee;
			
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Select");
		if (mysql_num_rows($result) == 1)
		{
				$row = mysql_fetch_array($result);	
				
				$codeCompet = $row['Code_competition'];
				$codeSaison = $row['Code_saison'];
				$dateDebut = $row['Date_debut'];
		}
		
		$sql  = "Select Max(Numero_ordre) MaxNumeroOrdre From gickp_Matchs Where Id_journee In (";
		$sql .= "Select Id From gickp_Journees Where Code_competition = '$codeCompet' ";
		$sql .= "And Code_saison = '$codeSaison'";
		$sql .= "And Date_debut <= '$dateDebut') ";
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Select");
		if (mysql_num_rows($result) == 1)
		{
			$row = mysql_fetch_array($result);	
			return $row['MaxNumeroOrdre'];
		}
	
		return 0;
	}

	function ParamMatch()
	{
		$idMatch = (int)utyGetPost('ParamCmd', -1);
		$_SESSION['idMatch'] = $idMatch;
		
		$_POST['comboJournee2'] = '';
		
		$myBdd = new MyBdd();

		$sql  = "Select Id_journee, Numero_ordre, Date_match, Heure_match, Libelle, Terrain, Type, Id_equipeA, Id_equipeB, Arbitre_principal, Arbitre_secondaire, Matric_arbitre_principal, Matric_arbitre_secondaire, CoeffA, CoeffB ";
		$sql .= "From gickp_Matchs ";
		$sql .= "Where Id = ";
		$sql .= $idMatch;
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Select");
		if (mysql_num_rows($result) == 1)
		{
			$row = mysql_fetch_array($result);	  
			
			$_SESSION['idJournee'] = $row['Id_journee'];
			$_SESSION['Num_match'] = $row['Numero_ordre'];
			$_SESSION['Date_match'] = utyDateUsToFr($row['Date_match']);
			$_SESSION['Heure_match'] = $row['Heure_match'];
			$_SESSION['Libelle'] = $row['Libelle'];
			$_SESSION['Terrain'] = $row['Terrain'];
			$_SESSION['Type'] = $row['Type'];
			
			$_SESSION['Id_equipeA'] = $row['Id_equipeA'];
			$_SESSION['Id_equipeB'] = $row['Id_equipeB'];
			
			$_SESSION['arbitre1'] = $row['Arbitre_principal'];
			$_SESSION['arbitre2'] = $row['Arbitre_secondaire'];
			
			$_SESSION['arbitre1_matric'] = $row['Matric_arbitre_principal'];
			$_SESSION['arbitre2_matric'] = $row['Matric_arbitre_secondaire'];
			
			$_SESSION['coeffA'] = $row['CoeffA'];
			$_SESSION['coeffB'] = $row['CoeffB'];
		}
	}
		
	function InitTitulaire()
	{
		$myBdd = new MyBdd();
		
		$idJournee = (int)utyGetPost('comboJournee', 0);
		
		$myBdd = new MyBdd();
		
  	// Chargement des Matchs de la journée ...
		$sql  = "Select Id, Id_equipeA, Id_equipeB ";
		$sql .= "From gickp_Matchs ";
		$sql .= "Where Id_journee = ";
		$sql .= $idJournee;
		$sql .= " And Validation <> 'O' ";
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load 6");
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
		
		$myBdd->utyJournal('Initialisation titulaires', '', '', 'NULL', $idJournee);
	}
	
	function PubliMatch()
	{
		$idMatch = (int) utyGetPost('ParamCmd', 0);
		(utyGetPost('Pub', '') != 'O') ? $changePub = 'O' : $changePub = 'N';
		
		$sql = "Update gickp_Matchs Set Publication = '$changePub' Where Id = '$idMatch' ";
		$myBdd = new MyBdd();
		mysql_query($sql, $myBdd->m_link) or die ("Erreur Update ".$sql);
		
		$myBdd->utyJournal('Publication match', utyGetSaison(), '', 'NULL', 'NULL', $idMatch, $changePub);
	}
	
	function PubliMultiMatchs()
	{
		$ParamCmd = '';
		if (isset($_POST['ParamCmd']))
			$ParamCmd = $_POST['ParamCmd'];
			
		$arrayParam = split ('[,]', $ParamCmd);		
		if (count($arrayParam) == 0)
			return; // Rien à changer ...

		$myBdd = new MyBdd();
		
		// Change Publication	
		for ($i=0;$i<count($arrayParam);$i++)
		{
			$sql = "Select Publication From gickp_Matchs Where Id = ".$arrayParam[$i]." ";
			//$sql .= "And Validation != 'O' ";
			$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Select : ".$sql);
			if (mysql_num_rows($result) != 1)
				continue;
			$row = mysql_fetch_array($result);	
			($row['Publication']=='O') ? $changePub = 'N' : $changePub = 'O';
			$sql = "Update gickp_Matchs Set Publication = '$changePub' Where Id = '".$arrayParam[$i]."' ";
			mysql_query($sql, $myBdd->m_link) or die ("Erreur Update ".$sql);
			$myBdd->utyJournal('Publication match', utyGetSaison(), '', 'NULL', 'NULL', $arrayParam[$i], $changePub);
		}
	}
	
	function VerrouPubliMultiMatchs()
	{
		$ParamCmd = '';
		if (isset($_POST['ParamCmd']))
			$ParamCmd = $_POST['ParamCmd'];
			
		$arrayParam = split ('[,]', $ParamCmd);		
		if (count($arrayParam) == 0)
			return; // Rien à changer ...

		$myBdd = new MyBdd();
		
		// Change Publication	
		for ($i=0;$i<count($arrayParam);$i++)
		{
			//$sql = "Select Validation, Publication From gickp_Matchs Where Id = ".$arrayParam[$i]." ";
			//$sql .= "And Validation != 'O' And Publication != 'O' ";
			//$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Select");
			//if (mysql_num_rows($result) != 1)
			//	continue;
			//$row = mysql_fetch_array($result);	
			$sql = "Update gickp_Matchs Set Publication = 'O', Validation = 'O' Where Id = '".$arrayParam[$i]."' ";
			mysql_query($sql, $myBdd->m_link) or die ("Erreur Update ".$sql);
			$myBdd->utyJournal('Verrou-Publi match', utyGetSaison(), '', 'NULL', 'NULL', $arrayParam[$i], 'O');
		}
	}
	
	function VerrouMatch()
	{
		$idMatch = (int) utyGetPost('ParamCmd', 0);
		(utyGetPost('Verrou', '') != 'O') ? $changeVerrou = 'O' : $changeVerrou = 'N';
		
		$sql = "Update gickp_Matchs Set Validation = '$changeVerrou' Where Id = '$idMatch' ";
		$myBdd = new MyBdd();
		mysql_query($sql, $myBdd->m_link) or die ("Erreur Update ".$sql);
		
		$myBdd->utyJournal('Verrouillage match', utyGetSaison(), '', 'NULL', 'NULL', $idMatch, $changeVerrou);
	}
	
	
	function VerrouMultiMatchs()
	{
		$ParamCmd = '';
		if (isset($_POST['ParamCmd']))
			$ParamCmd = $_POST['ParamCmd'];
			
		$arrayParam = split ('[,]', $ParamCmd);		
		if (count($arrayParam) == 0)
			return; // Rien à changer ...

		$myBdd = new MyBdd();
		
		// Change Publication	
		for ($i=0;$i<count($arrayParam);$i++)
		{
			$sql = "Select Validation From gickp_Matchs Where Id = ".$arrayParam[$i]." ";
			$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Select");
			if (mysql_num_rows($result) != 1)
				continue;
			$row = mysql_fetch_array($result);	
			($row['Validation']=='O') ? $changeVerrou = 'N' : $changeVerrou = 'O';
			$sql = "Update gickp_Matchs Set Validation = '$changeVerrou' Where Id = '".$arrayParam[$i]."' ";
			mysql_query($sql, $myBdd->m_link) or die ("Erreur Update ".$sql);
			$myBdd->utyJournal('Verrouillage match', utyGetSaison(), '', 'NULL', 'NULL', $arrayParam[$i], $changePub);
		}
	}
	
	function AffectMultiMatchs() // Affect. Auto
	{
		// Affectation auto des équipes	dans les matchs
		$ParamCmd = '';
		if (isset($_POST['ParamCmd']))
			$ParamCmd = $_POST['ParamCmd'];
			
		$arrayParam = split ('[,]', $ParamCmd);		
		if (count($arrayParam) == 0)
			return; // Rien à affecter ...

		$myBdd = new MyBdd();

		$texte = '';
		
		// pour chaque match coché
		for ($i=0;$i<count($arrayParam);$i++)
		{
			$id = $arrayParam[$i];
			$sql  = "Select m.Libelle, m.Id_journee, m.Id_equipeA, m.Id_equipeB, j.Code_competition, j.Code_saison ";
			$sql .= "From gickp_Matchs m, gickp_Journees j ";
			$sql .= "Where m.Id = ".$id." ";
			$sql .= "AND m.Id_journee = j.Id ";
			$sql .= "AND m.Validation <> 'O' "; 
			$sql .= "AND (m.ScoreA = '' ";
			$sql .= "OR m.ScoreA = '?' "; 
			$sql .= "OR m.ScoreA IS NULL) "; 
			$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Select : ".$sql);
			if (mysql_num_rows($result) != 1)
				die("Erreur : L\'un des matchs a déjà un score ou est verrouillé !  (<a href='javascript:history.back()'>Retour</a>)");
			$row = mysql_fetch_array($result);
			$anciene_equipeA = $row['Id_equipeA'];
			$anciene_equipeB = $row['Id_equipeB'];
			
			$libelle = preg_replace("/\s/","",$row['Libelle']);
			// On contrôle qu'il y a un crochet ouvrant et un fermant, et on prend le contenu.
			$libelle = preg_split("/[\[]/",$libelle);
			if($libelle[1] == "")
				die("Placez votre code AffectAuto entre crochets [ ]. (<a href='javascript:history.back()'>Retour</a>)");
			$libelle = preg_split("/[\]]/",$libelle[1]);
			if($libelle[0] == "")
				die("Placez votre code AffectAuto entre crochets [ ]. (<a href='javascript:history.back()'>Retour</a>)");
			$texte .= '<br>'.$libelle[0].'<br>';
			// On sépare par tiret, slash, étoile, virgule ou point-virgule.
			$libelle = preg_split("/[\-\/*,;]/",$libelle[0]);
			// On analyse le contenu
			for ($j=0;$j<4;$j++)
			{
				$codeTirage = '';
				$codeVainqueur = '';
				$codePerdant = '';
				$codePoule = '';
				//preg_match("/([T])/",$libelle[$j],$codeTirage); // tirage au sort
				//preg_match("/([V])/",$libelle[$j],$codeVainqueur); // vainqueur
				//preg_match("/([P])/",$libelle[$j],$codePerdant); // perdant
				//preg_match("/([A-O])/",$libelle[$j],$codePoule); // lettre de poule
				preg_match("/([A-Z]+)/",$libelle[$j],$codeLettres); // lettre
				preg_match("/([0-9]+)/",$libelle[$j],$codeNumero); // numero... de match ou classement de poule ou tirage
				$posNumero = strpos($libelle[$j], $codeNumero[1]);
				$posLettres = strpos($libelle[$j], $codeLettres[1]);
				if($posNumero > $posLettres){
					switch($codeLettres[1]){
						case 'T' : // tirage
						case 'D' : // draw
							$codeTirage = $codeLettres[1];
							break;
						case 'V' : // vainqueur
						case 'G' : // gagnant
						case 'W' : // winner
							$codeVainqueur = $codeLettres[1];
							break;
						case 'P' : // Perdant
						case 'L' : // Loser
							$codePerdant = $codeLettres[1];
							break;
						default :
							die("Code incorrect sur le match ".$id.". (<a href='javascript:history.back()'>Retour</a>)");
							break;
					}
				}else{ // poule
					$codePoule = $codeLettres[1];
				}
				if($codeTirage != '')
				{
					$sql2  = "Select ce.Id, ce.Libelle Nom_equipe ";
					$sql2 .= "From gickp_Competitions_Equipes ce ";
					$sql2 .= "Where ce.Tirage = ".$codeNumero[1]." ";
					$sql2 .= "AND ce.Code_compet = '".$row['Code_competition']."' ";
					$sql2 .= "AND ce.Code_saison = ".$row['Code_saison']." ";
					$result2 = mysql_query($sql2, $myBdd->m_link) or die ("Erreur Select 2a : ".$sql2);
					if (mysql_num_rows($result2) != 1)
					{
						$selectNum[$j]=0;
						$selectNom[$j]='';
						$clst = 'erreur10';
					}
					else
					{
						$row2 = mysql_fetch_array($result2);
						$selectNum[$j]=$row2['Id'];
						$selectNom[$j]=addslashes($row2['Nom_equipe']);
						$clst = $row2['Nom_equipe'];
					}
					$texte .=$codeNumero[1].'e poule '.$codePoule[1].' : '.$clst.'<br>';
				}
				elseif($codeVainqueur != '')
				{
					$sql2  = "Select m.Id_equipeA, m.Id_equipeB, ce.Libelle Nom_equipeA, ce2.Libelle Nom_equipeB, m.ScoreA, m.ScoreB ";
					$sql2 .= "From gickp_Matchs m, gickp_Journees j, gickp_Competitions_Equipes ce, gickp_Competitions_Equipes ce2 ";
					$sql2 .= "Where m.Numero_ordre = ".$codeNumero[1]." ";
					$sql2 .= "AND m.Id_journee = j.Id ";
					$sql2 .= "AND m.Id_equipeA = ce.Id ";
					$sql2 .= "AND m.Id_equipeB = ce2.Id ";
					$sql2 .= "AND m.ScoreA <> m.ScoreB ";
					$sql2 .= "AND j.Code_competition = '".$row['Code_competition']."' ";
					$sql2 .= "AND j.Code_saison = ".$row['Code_saison']." ";
					$result2 = mysql_query($sql2, $myBdd->m_link) or die ("Erreur Select 2");
					if (mysql_num_rows($result2) != 1)
					{
						$selectNum[$j]=0;
						$selectNom[$j]='';
						$vainqueur = 'erreur11';
					}
					else
					{
						$row2 = mysql_fetch_array($result2);
						if(($row2['ScoreA'] > $row2['ScoreB'] && $row2['ScoreA'] != 'F') || $row2['ScoreB'] == 'F')
						{
							$selectNum[$j]=$row2['Id_equipeA'];
							$selectNom[$j]=addslashes($row2['Nom_equipeA']);
							$vainqueur = $row2['Nom_equipeA'];
						}
						else
						{
							$selectNum[$j]=$row2['Id_equipeB'];
							$selectNom[$j]=addslashes($row2['Nom_equipeB']);
							$vainqueur = $row2['Nom_equipeB'];
						}
					}
					$texte .='Vainqueur match '.$codeNumero[1].' : '.$vainqueur.'<br>';
				}
				elseif($codePerdant != '')
				{
					$sql2  = "Select m.Libelle, m.Id_journee, m.Id_equipeA, m.Id_equipeB, ce.Libelle Nom_equipeA, ce2.Libelle Nom_equipeB, m.ScoreA, m.ScoreB ";
					$sql2 .= "From gickp_Matchs m, gickp_Journees j, gickp_Competitions_Equipes ce, gickp_Competitions_Equipes ce2 ";
					$sql2 .= "Where m.Numero_ordre = ".$codeNumero[1]." ";
					$sql2 .= "AND m.Id_journee = j.Id ";
					$sql2 .= "AND m.Id_equipeA = ce.Id ";
					$sql2 .= "AND m.Id_equipeB = ce2.Id ";
					$sql2 .= "AND m.ScoreA <> m.ScoreB ";
					$sql2 .= "AND j.Code_competition = '".$row['Code_competition']."' ";
					$sql2 .= "AND j.Code_saison = ".$row['Code_saison']." ";
					$result2 = mysql_query($sql2, $myBdd->m_link) or die ("Erreur Select 2");
					if (mysql_num_rows($result2) != 1)
					{
						$selectNum[$j]=0;
						$selectNom[$j]='';
						$perdant = 'erreur12';
					}
					else
					{
						$row2 = mysql_fetch_array($result2);
						if(($row2['ScoreA'] < $row2['ScoreB'] && $row2['ScoreB'] != 'F') || $row2['ScoreA'] == 'F')
						{
							$selectNum[$j]=$row2['Id_equipeA'];
							$selectNom[$j]=addslashes($row2['Nom_equipeA']);
							$perdant = $row2['Nom_equipeA'];
						}
						else
						{
							$selectNum[$j]=$row2['Id_equipeB'];
							$selectNom[$j]=addslashes($row2['Nom_equipeB']);
							$perdant = $row2['Nom_equipeB'];
						}
					}
					$texte .='Perdant match '.$codeNumero[1].' : '.$perdant.'<br>';
				}
				elseif($codePoule != '')
				{
					$sql2  = "Select cej.Id, ce.Libelle Nom_equipe ";
					$sql2 .= "From gickp_Competitions_Equipes_Journee cej, gickp_Journees j, gickp_Competitions_Equipes ce ";
					$sql2 .= "Where cej.Clt = ".$codeNumero[1]." ";
					$sql2 .= "AND cej.Id_journee = j.Id ";
					$sql2 .= "AND cej.Id = ce.Id ";
					$sql2 .= "AND (j.Phase LIKE '".$codePoule."' ";
					$sql2 .= "OR j.Phase LIKE '%poule ".$codePoule."' ";
					$sql2 .= "OR j.Phase LIKE '%Poule ".$codePoule."' ";
					$sql2 .= "OR j.Phase LIKE '%Groupe ".$codePoule."' ";
					$sql2 .= "OR j.Phase LIKE '%Group ".$codePoule."' ";
					$sql2 .= "OR j.Phase LIKE '%poule ".$codePoule." %' ";
					$sql2 .= "OR j.Phase LIKE '%Poule ".$codePoule." %' ";
					$sql2 .= "OR j.Phase LIKE '%Groupe ".$codePoule." %' ";
					$sql2 .= "OR j.Phase LIKE '%Group ".$codePoule." %') ";
					$sql2 .= "AND j.Code_competition = '".$row['Code_competition']."' ";
					$sql2 .= "AND j.Code_saison = ".$row['Code_saison']." ";
					$result2 = mysql_query($sql2, $myBdd->m_link) or die ("Erreur Select 2 : ".$sql2);
					if (mysql_num_rows($result2) != 1)
					{
						$selectNum[$j]=0;
						$selectNom[$j]='';
						$clst = 'erreur13';
					}
					else
					{
						$row2 = mysql_fetch_array($result2);
						$selectNum[$j]=$row2['Id'];
						$selectNom[$j]=addslashes($row2['Nom_equipe']);
						$clst = $row2['Nom_equipe'];
					}
					$texte .=$codeNumero[1].'e poule '.$codePoule.' : '.$clst.'<br>';
				}
				else
				{
					$selectNum[$j]=0;
					$selectNom[$j]='';
				}
			}
			// Affectation
			$sql3  = "Update gickp_Matchs Set Id_equipeA = '$selectNum[0]', Id_equipeB = '$selectNum[1]'";
			if($selectNom[2] != '')
				$sql3 .= ", Arbitre_principal = '$selectNom[2]'";
			if($selectNom[3] != '')
				$sql3 .= ", Arbitre_secondaire = '$selectNom[3]' ";
			$sql3 .= " Where Id = '$id' ";
			mysql_query($sql3, $myBdd->m_link) or die ("Erreur Update ".$sql3);
			//Suppression des joueurs existants si changements d'équipes
			if($selectNum[0] != $anciene_equipeA)
			{
				$sql  = "Delete From gickp_Matchs_Joueurs ";
				$sql .= "Where Id_match = $id ";
				$sql .= "And Equipe = 'A' ";
				mysql_query($sql, $myBdd->m_link) or die ("Erreur Delete => ".$sql);
			}
			if($selectNum[1] != $anciene_equipeB)
			{
				$sql  = "Delete From gickp_Matchs_Joueurs ";
				$sql .= "Where Id_match = $id ";
				$sql .= "And Equipe = 'B' ";
				mysql_query($sql, $myBdd->m_link) or die ("Erreur Delete => ".$sql);
			}
			//Journal
			$myBdd->utyJournal('Affect auto équipes', $row['Code_saison'], $row['Code_competition'], 'NULL', $row['Id_journee'], $id, '');
		}
	}

	
	function AnnulMultiMatchs() // Annul. Auto
	{
		// Annulation des affectations d'équipes dans les matchs
		$ParamCmd = '';
		if (isset($_POST['ParamCmd']))
			$ParamCmd = $_POST['ParamCmd'];
			
		$arrayParam = split ('[,]', $ParamCmd);		
		if (count($arrayParam) == 0)
			return; // Rien à affecter ...

		$myBdd = new MyBdd();

		$texte = '';
		
		// pour chaque match coché
		for ($i=0;$i<count($arrayParam);$i++)
		{
			$id = $arrayParam[$i];
			$sql  = "Select m.Libelle, m.Id_journee, j.Code_competition, j.Code_saison ";
			$sql .= "From gickp_Matchs m, gickp_Journees j ";
			$sql .= "Where m.Id = ".$id." ";
			$sql .= "AND m.Id_journee = j.Id ";
			$sql .= "AND m.Validation <> 'O' "; 
			$sql .= "AND (m.ScoreA = '' ";
			$sql .= "OR m.ScoreA = '?' "; 
			$sql .= "OR m.ScoreA IS NULL) "; 
			$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Select : ".$sql);
			if (mysql_num_rows($result) != 1)
				die("Erreur : L\'un des matchs a déjà un score ou est verrouillé !  (<a href='javascript:history.back()'>Retour</a>)");
			$row = mysql_fetch_array($result);
			$sql3  = "Update gickp_Matchs Set Id_equipeA = 0, Id_equipeB = 0, Arbitre_principal = -1, Arbitre_secondaire = -1 ";
			$sql3 .= "Where Id = '$id' ";
			mysql_query($sql3, $myBdd->m_link) or die ("Erreur Update ".$sql3);
		
			$myBdd->utyJournal('Annul auto équipes', $row['Code_saison'], $row['Code_competition'], 'NULL', $row['Id_journee'], $id, '');
			//Suppression des joueurs existants
			$sql = "Delete From gickp_Matchs_Joueurs Where Id_match = $id ";
			mysql_query($sql, $myBdd->m_link) or die ("Erreur Delete");

		}
		
	}

	function ChangeMultiMatchs()
	{
		$ParamCmd = '';
		if (isset($_POST['ParamCmd']))
			$ParamCmd = $_POST['ParamCmd'];
			
		$arrayParam = split ('[,]', $ParamCmd);		
		if (count($arrayParam) == 0)
			return; // Rien à changer ...

		$idJournee = (int)utyGetPost('comboJournee', 0);

		$myBdd = new MyBdd();
		
		// Change Journee	
		for ($i=0;$i<count($arrayParam);$i++)
		{
			$sql = "Update gickp_Matchs Set Id_journee = $idJournee Where Id = ".$arrayParam[$i]." And Validation != 'O' ";
			$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Update ".$sql);
			$myBdd->utyJournal('Change Journee match', utyGetSaison(), '', 'NULL', 'NULL', $arrayParam[$i], $idJournee);
		}
	}
	
	function __construct()
	{			
	  	MyPageSecure::MyPageSecure(6);
		
		$alertMessage = '';
	  
		$Cmd = '';
		if (isset($_POST['Cmd']))
			$Cmd = $_POST['Cmd'];

		$ParamCmd = '';
		if (isset($_POST['ParamCmd']))
			$ParamCmd = $_POST['ParamCmd'];

		if (strlen($Cmd) > 0)
		{
			if ($Cmd == 'Add')
				($_SESSION['Profile'] <= 6) ? $this->Add() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'Update')
				($_SESSION['Profile'] <= 6) ? $this->Update() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'Raz')
				($_SESSION['Profile'] <= 6) ? $this->Raz() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'Remove')
				($_SESSION['Profile'] <= 6) ? $this->Remove() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'ParamMatch')
				($_SESSION['Profile'] <= 6) ? $this->ParamMatch() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'InitTitulaire')
				($_SESSION['Profile'] <= 6) ? $this->InitTitulaire() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'PubliMatch')
				($_SESSION['Profile'] <= 6) ? $this->PubliMatch() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'PubliMultiMatchs')
				($_SESSION['Profile'] <= 6) ? $this->PubliMultiMatchs() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'VerrouMatch')
				($_SESSION['Profile'] <= 4) ? $this->VerrouMatch() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'VerrouMultiMatchs')
				($_SESSION['Profile'] <= 4) ? $this->VerrouMultiMatchs() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'VerrouPubliMultiMatchs')
				($_SESSION['Profile'] <= 4) ? $this->VerrouPubliMultiMatchs() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'AffectMultiMatchs')
				($_SESSION['Profile'] <= 6) ? $this->AffectMultiMatchs() : $alertMessage = 'Vous n avez pas les droits pour cette action.';

			if ($Cmd == 'AnnulMultiMatchs')
				($_SESSION['Profile'] <= 6) ? $this->AnnulMultiMatchs() : $alertMessage = 'Vous n avez pas les droits pour cette action.';

			if ($Cmd == 'ChangeMultiMatchs')
				($_SESSION['Profile'] <= 6) ? $this->ChangeMultiMatchs() : $alertMessage = 'Vous n avez pas les droits pour cette action.';

			if ($alertMessage == '')
			{
				header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);	
				exit;
			}
		}
	
		if ($ParamCmd == 'changeCompet')
			$_SESSION['idMatch'] = -1; // La Combo Compétition a changé => Plus aucun match n'est sélectionné ...
		
		$this->SetTemplate("Gestion des officiels", "Matchs", false);
		$this->Load();
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		
		$this->m_tpl->assign('idMatch', utyGetSession('idMatch', 0));
//		$this->m_tpl->assign('idJournee', utyGetSession('idJournee', 0));
		
		$this->m_tpl->assign('Intervalle_match', utyGetSession('Intervalle_match', '40'));
		$this->m_tpl->assign('Num_match', utyGetSession('Num_match', ''));
		$this->m_tpl->assign('Date_match', utyGetSession('Date_match', ''));
		$this->m_tpl->assign('Heure_match', utyGetSession('Heure_match', ''));
		$this->m_tpl->assign('Libelle', utyGetSession('Libelle', ''));
		$this->m_tpl->assign('Terrain', utyGetSession('Terrain', ''));
		$this->m_tpl->assign('Type', utyGetSession('Type', ''));
		$this->m_tpl->assign('arbitre1', utyGetSession('arbitre1', ''));
		$this->m_tpl->assign('arbitre2', utyGetSession('arbitre2', ''));
		$this->m_tpl->assign('arbitre1_matric', utyGetSession('arbitre1_matric', ''));
		$this->m_tpl->assign('arbitre2_matric', utyGetSession('arbitre2_matric', ''));
		$this->m_tpl->assign('coeffA', utyGetSession('coeffA', 1));
		$this->m_tpl->assign('coeffB', utyGetSession('coeffB', 1));
		
		$this->DisplayTemplate('GestionOfficiels');
	}
}		  	

$page = new GestionOfficiels();

