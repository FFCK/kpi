<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion d'une Journee

class GestionJournee extends MyPageSecure	 
{	
	var $myBdd;

	function Load()
	{
		$myBdd = $this->myBdd;
		
        // Langue
        $langue = parse_ini_file("../commun/MyLang.ini", true);
        if (utyGetSession('lang') == 'en') {
            $lang = $langue['en'];
        } else {
            $lang = $langue['fr'];
        }
        
		$codeSaison = $myBdd->GetActiveSaison();

        //Filtre affichage type compet
		$AfficheCompet = utyGetSession('AfficheCompet','');
		$AfficheCompet = utyGetPost('AfficheCompet', $AfficheCompet);
        $_SESSION['AfficheCompet'] = $AfficheCompet;
		$this->m_tpl->assign('AfficheCompet', $AfficheCompet);

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
		
		$sql = "SELECT Id, Libelle, Date_debut, Publication 
			FROM gickp_Evenement 
			ORDER BY Date_debut DESC, Libelle ";	 
		$result = $myBdd->pdo->prepare($sql);
		$result->execute();
		$arrayEvenement = array();
		if (-1 == $idEvenement) {
            array_push($arrayEvenement, array('Id' => -1, 'Libelle' => 'Tous_les_evenements', 'Selection' => 'SELECTED'));
        } else {
            array_push($arrayEvenement, array('Id' => -1, 'Libelle' => 'Tous_les_evenements', 'Selection' => ''));
        }

		while ($row = $result->fetch()) {
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
		
		$codeCompet = utyGetSession('codeCompet', '*');
		$codeCompet = utyGetPost('comboCompet', $codeCompet);
		$codeCompet = utyGetGet('Compet', $codeCompet);
		if ($codeCompet != $_SESSION['codeCompet'] || utyGetGet('Compet', false)) {
			$this->Raz();
			$idSelJournee = '*';
			$idMatch = -1;
		} else {
			$idSelJournee = utyGetSession('idSelJournee', '*');  //ATTENTION : Comportement à surveiller
			$idSelJournee = utyGetPost('comboJournee2', $idSelJournee);
			$idSelJournee = utyGetGet('idJournee', $idSelJournee);
				
			if (!isset($_SESSION['idSelJournee'])) {
				$_SESSION['idSelJournee'] = '';
			}
			if ($idSelJournee != $_SESSION['idSelJournee']) {
				$idMatch = -1;
			} else {
				$idMatch = utyGetSession('idMatch', -1);
			}

		}
		$_SESSION['idMatch'] = $idMatch;
		$_SESSION['idSelJournee'] = $idSelJournee;
		$_SESSION['codeCompet'] = $codeCompet;
		$this->m_tpl->assign('idMatch', $idMatch);
		$this->m_tpl->assign('idSelJournee', $idSelJournee);
		$this->m_tpl->assign('codeCompet', $codeCompet);
		
			
		// Chargement des Informations relatives aux Journées ...
		if ($idSelJournee != '*') {
			$sql = "SELECT DISTINCT b.Id, b.Code_competition, b.Phase, b.Niveau, b.Libelle, 
				b.Lieu, b.Date_debut, b.Type, a.Code_typeclt 
				FROM gickp_Journees b, gickp_Competitions a 
				WHERE b.Id = ? 
				AND a.Code = b.Code_competition ";
			$sql .= utyGetFiltreCompetition('a.');			
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($idSelJournee));				
			$idEvenement = -1;
		} else {
			if ($idEvenement != -1) {
				$sql = "SELECT DISTINCT a.Id, a.Code_competition, a.Phase, a.Niveau, a.Libelle, 
					a.Lieu, a.Date_debut, a.Type, c.Code_typeclt 
					FROM gickp_Journees a, gickp_Evenement_Journees b, gickp_Competitions c 
					WHERE a.Id = b.Id_journee 
					AND a.Code_competition = c.Code 
					AND a.Code_saison = c.Code_saison 
					AND b.Id_evenement = ? ";
				$arrayQuery = [$idEvenement];
				if ($codeCompet != '*') {
					$sql .= "AND a.Code_competition = ? ";
					$arrayQuery = array_merge($arrayQuery, [$codeCompet]);
                }
                $sql .= "ORDER BY a.Code_competition, a.Date_debut, a.Niveau, a.Id ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute($arrayQuery);
			} else {
				$arrayQuery = [$codeSaison];
                $sql = "SELECT DISTINCT j.Id, j.Code_competition, j.Phase, j.Niveau, j.Libelle, 
					j.Lieu, j.Date_debut, j.Type, c.Code_typeclt 
					FROM gickp_Journees j, gickp_Competitions c, gickp_Competitions_Groupes g 
					WHERE j.Code_saison = ? ";
                if ($codeCompet != '*') {
                    $sql .= "AND j.Code_competition = ? ";
					$arrayQuery = array_merge($arrayQuery, [$codeCompet]);
                }
				if ($filtreMois > 0) {
                    $sql .= "AND (MONTH(j.Date_debut) = ? 
						OR MONTH(j.Date_fin) = ?) ";
					$arrayQuery = array_merge($arrayQuery, [$filtreMois], [$filtreMois]);
                }
                $sql .= "AND c.Code_ref = g.Groupe 
					AND j.Code_competition = c.Code 
					AND j.Code_saison = c.Code_saison ";
                $sql .= utyGetFiltreCompetition('c.');			
                $sql .= "AND c.Code_niveau LIKE ? ";
				$arrayQuery = array_merge($arrayQuery, [utyGetSession('AfficheNiveau').'%']);
                if ($AfficheCompet == 'N') {
                    $sql .= "AND c.Code LIKE 'N%' ";
                } elseif ($AfficheCompet == 'CF') {
                    $sql .= "AND c.Code LIKE 'CF%' ";
                } elseif ($AfficheCompet == 'M') {
                    $sql .= "AND c.Code_ref = 'M' ";
                } elseif($AfficheCompet > 0) {
                    $sql .= "AND g.section = ? ";
					$arrayQuery = array_merge($arrayQuery, [$AfficheCompet]);
                }
                $sql .= " ORDER BY j.Code_competition, j.Date_debut, j.Niveau, j.Id ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute($arrayQuery);
			}
		}
		
		$arrayJournees = array();
		$arrayJourneesAutorisees = array();
		$i = 0;
		$lstJournee = '';
		while ($row = $result->fetch()) {
			if ($_SESSION['lang'] == 'fr') {
				$row['Date_debut'] = utyDateUsToFr($row['Date_debut']);
			}
			array_push($arrayJournees, array( 'Id' => $row['Id'], 
				'Code_competition' => $row['Code_competition'], 'Code_typeclt' => $row['Code_typeclt'], 
				'Phase' => $row['Phase'], 'Niveau' => $row['Niveau'], 'Type' => $row['Type'], 
				'Libelle' => $row['Libelle'], 'Lieu' => $row['Lieu'], 
				'Date_debut' => $row['Date_debut'] ));
			if ($i > 0) {
				$lstJournee .= ',';
			}
			$lstJournee .= $row['Id'];
			
			// Journees autorisées seulement :
			if (utyIsAutorisationJournee($row['Id']))
			{
				array_push($arrayJourneesAutorisees, array( 'Id' => $row['Id'], 
					'Code_competition' => $row['Code_competition'], 'Lieu' => $row['Lieu'], 
					'Code_typeclt' => $row['Code_typeclt'], 'Type' => $row['Type'], 
					'Phase' => $row['Phase'], 'Niveau' => $row['Niveau'], 
					'Date_debut' => $row['Date_debut'] ));
			}
			$i ++;
		}
		
		$_SESSION['lstJournee'] = $lstJournee;


		// Chargement des Informations relatives aux Journées pour le filtre...
		if ($idEvenement2 != -1) {
            $sql = "SELECT DISTINCT a.Id, a.Code_competition, a.Phase, a.Niveau, a.Lieu, 
				a.Date_debut, c.Code_typeclt 
				FROM gickp_Journees a, gickp_Evenement_Journees b, gickp_Competitions c 
				WHERE a.Id = b.Id_journee 
				AND a.Code_competition = c.Code 
				AND a.Code_saison = c.Code_saison 
				AND b.Id_evenement = ? ";
			$arrayQuery = [$idEvenement2];
			if ($codeCompet != '*') {
                $sql .= "AND a.Code_competition = ? ";
				$arrayQuery = array_merge($arrayQuery, [$codeCompet]);
            }
            $sql .= "ORDER BY a.Code_competition, a.Date_debut, a.Niveau, a.Id ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute($arrayQuery);
		} else {
            $sql  = "SELECT DISTINCT j.Id, j.Code_competition, j.Phase, j.Niveau, j.Lieu, 
				j.Date_debut, c.Code_typeclt 
				FROM gickp_Journees j, gickp_Competitions c, gickp_Competitions_Groupes g 
				WHERE j.Code_saison = ? ";
			$arrayQuery = [$codeSaison];
            if ($codeCompet != '*') {
                $sql .= "AND j.Code_competition = ? ";
				$arrayQuery = array_merge($arrayQuery, [$codeCompet]);
            }
            if ($filtreMois > 0) {
                $sql .= "AND (MONTH(j.Date_debut) = $filtreMois 
					OR MONTH(j.Date_fin) = $filtreMois) ";
				$arrayQuery = array_merge($arrayQuery, [$filtreMois], [$filtreMois]);
            }
			$sql .= "AND j.Code_competition = c.Code 
				AND j.Code_saison = c.Code_saison 
				AND c.Code_ref = g.Groupe ";
            $sql .= utyGetFiltreCompetition('c.');			
            $sql .= "AND c.Code_niveau LIKE ? ";
			$arrayQuery = array_merge($arrayQuery, [utyGetSession('AfficheNiveau').'%']);
            if ($AfficheCompet == 'N') {
                $sql .= " AND c.Code LIKE 'N%' ";
            } elseif ($AfficheCompet == 'CF') {
                $sql .= " AND c.Code LIKE 'CF%' ";
            } elseif ($AfficheCompet == 'M') {
                $sql .= " AND c.Code_ref = 'M' ";
            } elseif($AfficheCompet > 0) {
                $sql .= " AND g.section = ? ";
				$arrayQuery = array_merge($arrayQuery, [$AfficheCompet]);
            }
            $sql .= "ORDER BY j.Code_competition, j.Date_debut, j.Niveau, j.Id ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute($arrayQuery);
		}
		
		$arrayJourneesAutoriseesFiltre = array();
		$PhaseLibelle = 0;
		while ($row = $result->fetch()) {
			// S'il n'y a qu'une seule compétition et de type CP, on affichera les phases
			if ($codeCompet != '*' && $row['Code_typeclt'] == 'CP') {
				$PhaseLibelle = 1;
			}
			if (utyGetSession('lang') == 'fr') {
				$row['Date_debut'] = utyDateUsToFr($row['Date_debut']);
			}
			// Journees autorisées seulement :
			if (utyIsAutorisationJournee($row['Id'])) {
				array_push($arrayJourneesAutoriseesFiltre, array( 'Id' => $row['Id'], 'Code_competition' => $row['Code_competition'], 'Lieu' => $row['Lieu'], 'Code_typeclt' => $row['Code_typeclt'],
																				'Phase' => $row['Phase'], 'Niveau' => $row['Niveau'], 'Date_debut' => $row['Date_debut'] ));
			}
		}
		
		// Chargement des Competitions relatives à l'Evenement ...
		$arrayCompetition = array();
			
		if ($idEvenement != -1) {
            $arrayCompetition[0]['label'] = "Evenement";
            $arrayCompetition[0]['options'][] = array('Code' => '*', 'Libelle' => 'Toutes_les_competitions_de_l_evenement', 'selected' => 'selected' );
			$i = 0;
			$sqlFiltreCompetition = utyGetFiltreCompetition('c.');
			$sql = "SELECT DISTINCT c.GroupOrder, c.Code, c.Libelle, c.Soustitre, c.Soustitre2, 
				c.Titre_actif, g.id, g.section, g.ordre 
				FROM gickp_Evenement_Journees a, gickp_Journees b, gickp_Competitions c, 
				gickp_Competitions_Groupes g 
				WHERE a.Id_evenement = ? 
				AND a.Id_journee = b.Id 
				$sqlFiltreCompetition 
				AND b.Code_competition = c.Code 
				AND b.Code_saison = c.Code_saison 
				AND c.Code_ref = g.Groupe 
				ORDER BY g.section, g.ordre, c.Code_tour, c.GroupOrder, c.Code ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($idEvenement));
		} else {
            $arrayCompetition[0]['label'] = "Evenement";
            $i = -1;
			$arrayCompetEvt = array( 'Code' => '*', 'Libelle' => 'Toutes les compétitions sélectionnées');
			$sqlFiltreCompetition = utyGetFiltreCompetition('c.');
			$sql = "SELECT DISTINCT c.GroupOrder, c.Code, c.Libelle, c.Code_niveau, c.Code_ref, 
				c.Code_tour, c.Soustitre, c.Soustitre2, c.Titre_actif, g.id, g.section, g.ordre 
				FROM gickp_Competitions c, gickp_Competitions_Groupes g 
				WHERE 1 = 1 
				AND c.Code_saison = ? 
				$sqlFiltreCompetition 
                AND c.Code_niveau LIKE ? ";
				$arrayQuery = array($codeSaison, utyGetSession('AfficheNiveau').'%');
			if ($AfficheCompet == 'N') {
                $sql .= "AND c.Code LIKE 'N%' ";
            } elseif ($AfficheCompet == 'CF') {
                $sql .= "AND c.Code LIKE 'CF%' ";
            } elseif ($AfficheCompet == 'M') {
                $sql .= "AND c.Code_ref = 'M' ";
            } elseif($AfficheCompet > 0) {
                $sql .= "AND g.section = ? ";
				$arrayQuery = array_merge($arrayQuery, [$AfficheCompet]);
            }
			$sql .= "AND c.Code_ref = g.Groupe 
				ORDER BY c.Code_saison, g.section, g.ordre, c.Code_tour, c.GroupOrder, c.Code ";	 
			$result = $myBdd->pdo->prepare($sql);
			$result->execute($arrayQuery);
		}

        $j = '';
        $label = $myBdd->getSections();
		while ($row = $result->fetch()) {
			// Titre
			if ($row["Titre_actif"] != 'O' && $row["Soustitre"] != '') {
                $Libelle = $row["Soustitre"];
            } else {
                $Libelle = $row["Libelle"];
            }
            if ($row["Soustitre2"] != '') {
                $Libelle .= ' - ' . $row["Soustitre2"];
            }

            if ($j != $row['section']) {
                $i ++;
                $arrayCompetition[$i]['label'] = $label[$row['section']];
            }
            if ($row["Code"] == $codeCompet) {
                $row['selected'] = 'selected';
            } else {
                $row['selected'] = '';
            }
            $j = $row['section'];
            $arrayCompetition[$i]['options'][] = $row;
		}

		// Initialisation Date du match ...
		if (utyGetPost('Date_match', false)) {
            if (strlen(utyGetSession('Date_match')) == 0) {
                if (count($arrayJournees) >= 1) {
                    $_SESSION['Date_match'] = $arrayJournees[0]['Date_debut'];
                }
            }
		}

		// Sous-titre
		$headerSubTitle = '';
		if ( (count($arrayJournees) == 1)) {
			$headerSubTitle = $arrayJournees[0]['Code_competition'];
			if (strlen($arrayJournees[0]['Phase']) > 0) {
                $headerSubTitle .= '/' . $arrayJournees[0]['Phase'] . ' (Niveau ' . $arrayJournees[0]['Niveau'] . ')';
            }
            $headerSubTitle .= ' - '.$arrayJournees[0]['Libelle'].' - '.$arrayJournees[0]['Date_debut'];
		} else {
			// Chargement Evenement ...
			$sql = "SELECT Libelle, Lieu, Date_debut, Date_fin 
				FROM gickp_Evenement 
				WHERE Id = ? ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($idEvenement));
			if ($result->rowCount() == 1) {
				$row = $result->fetch();	  	
				$headerSubTitle = '<span class="highlight4">'.$row['Libelle'].'</span>&nbsp;>&nbsp;';
			}	
			if ($codeCompet != '*') {
                $headerSubTitle .= '<span class="highlight3">' . $codeCompet . '</span>';
            } else {
                if(utyGetSession('lang') == 'en') {
                    $headerSubTitle .= '<span>All event competitions</span>';
                } else {
                    $headerSubTitle .= '<span>Toutes les competitions</span>';
                }
            }
        }
		
		
		$this->m_tpl->assign('headerSubTitle', $headerSubTitle);
		
		// Ordre des Matchs 
		$orderMatchs = utyGetSession('orderMatchs', 'Order By a.Date_match, a.Heure_match, a.Terrain');
		$orderMatchs = utyGetPost('orderMatchs', $orderMatchs);
		$_SESSION['orderMatchs'] = $orderMatchs;
		
		$arrayOrderMatchs = array();
		
		// variable à initialiser dans tous les cas : @COSANDCO_WAMPSERVER
		if (!isset($selected)) {
            $selected = '';
        }

        array_push($arrayOrderMatchs, array( 'Key' => 'Order By a.Date_match, a.Heure_match, a.Terrain, a.Numero_ordre', 'Value' => 'Par_Date_Heure_et_Terrain'));
		array_push($arrayOrderMatchs, array( 'Key' => 'Order By d.Code_competition, a.Date_match, a.Heure_match, a.Terrain, a.Numero_ordre', 'Value' => 'Par_Competition_et_Date'));
		array_push($arrayOrderMatchs, array( 'Key' => 'Order By d.Code_competition, d.Niveau, d.Phase, a.Heure_match, a.Terrain, a.Numero_ordre', 'Value' => 'Par_Competition_Phase'));
		array_push($arrayOrderMatchs, array( 'Key' => 'Order By a.Terrain, a.Date_match, a.Heure_match, a.Numero_ordre', 'Value' => 'Par_Terrain_et_Date'));
		array_push($arrayOrderMatchs, array( 'Key' => 'Order By a.Numero_ordre, a.Date_match, a.Heure_match, a.Terrain', 'Value' => 'Par_Numero', 'Selected' => $selected ));

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
		$arrayListJournees = explode(',', $lstJournee);
		
		if ($lstJournee != '') {
			$arrayQuery = $arrayListJournees;
			// Chargement des Matchs des journées ...
			$in  = str_repeat('?,', count($arrayListJournees) - 1) . '?';
			$sql = "SELECT a.Id, a.Id_journee, a.Numero_ordre, a.Date_match, a.Heure_match, 
				a.Libelle, a.Terrain, a.Publication, a.Validation, a.Statut, a.Type, a.Periode, 
				a.ScoreDetailA, a.ScoreDetailB, b.Libelle EquipeA, c.Libelle EquipeB, 
				a.Id_equipeA, a.Id_equipeB, a.Terrain, a.ScoreA, a.ScoreB, a.CoeffA, a.CoeffB, 
				a.Arbitre_principal, a.Arbitre_secondaire, a.Matric_arbitre_principal, 
				a.Matric_arbitre_secondaire, d.Code_competition, d.Phase, d.Niveau, d.Lieu, 
				d.Libelle LibelleJournee, e.Soustitre2 
				FROM gickp_Journees d, gickp_Competitions e, gickp_Matchs a 
				LEFT OUTER JOIN gickp_Competitions_Equipes b ON (a.Id_equipeA = b.Id) 
				LEFT OUTER JOIN gickp_Competitions_Equipes c ON (a.Id_equipeB = c.Id) 
				WHERE a.Id_journee IN ($in) 
				AND a.Id_journee = d.Id 
				AND d.Code_competition = e.Code 
				AND d.Code_saison = e.Code_saison ";
			if ($filtreTerrain != '') {
				$sql .= "AND a.Terrain = ? ";
				$arrayQuery = array_merge($arrayQuery, [$filtreTerrain]);
			}
			$sql .= $orderMatchs;
			
			$result = $myBdd->pdo->prepare($sql);
			$result->execute($arrayQuery);
			
			// Variables à initialiser : @COSANDCO_WAMPSERVER
			$listMatch = '';
			$jourmatch = '';
			
			$listeJours = array();
			
			while ($row = $result->fetch()) {				
				$jour = $row['Date_match'];
                if (utyGetSession('lang') == 'fr') {
    				$listeJours[$jour] = utyDateUsToFr($jour);
					$row['Date_match'] = utyDateUsToFr($row['Date_match']);
                } else {
    				$listeJours[$jour] = $jour;
                }
				if ($filtreJour == '' || $jour == $filtreJour) {
                    if ($row['Libelle'] != '') {
                        if (utyGetSession('lang') == 'en') {
                            $EquipesAffectAuto = utyEquipesAffectAuto($row['Libelle']);
                        } else {
                            $EquipesAffectAuto = utyEquipesAffectAutoFR($row['Libelle']);
                        }
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
						'ScoreDetailA' => $row['ScoreDetailA'], 'ScoreDetailB' => $row['ScoreDetailB'], 
						'Statut' => $row['Statut'], 'Periode' => $row['Periode'], 'Type' => $row['Type'],
						'Date_match' => $row['Date_match'], 'Heure_match' => $row['Heure_match'],
						'Libelle' => $row['Libelle'], 'Terrain' => $row['Terrain'], 
						'EquipeA' => $row['EquipeA'], 'EquipeB' => $row['EquipeB'],
						'Id_equipeA' => $row['Id_equipeA'], 'Id_equipeB' => $row['Id_equipeB'], 
						'ScoreA' => $row['ScoreA'], 'ScoreB' => $row['ScoreB'], 
						'CoeffA' => $row['CoeffA'], 'CoeffB' => $row['CoeffB'],
						'Arbitre_principal' => $row['Arbitre_principal'], 
						'Arbitre_secondaire' => $row['Arbitre_secondaire'],
						'Matric_arbitre_principal' => $row['Matric_arbitre_principal'],
						'Matric_arbitre_secondaire' => $row['Matric_arbitre_secondaire'],
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
					
					if ($listMatch != '')
						$listMatch .= ',';
					$listMatch .= $row['Id'];
								
					if ($row['Phase'] != '' && $row['Libelle'] != '')
						$PhaseLibelle = 1;
																					
					if ($i == 0) {
						$dateDebut = $row['Date_match'];
						$dateFin = $row['Date_match'];
					} else {
						if (utyDateCmpFr($dateDebut, $row['Date_match']) > 0)
							$dateDebut = $row['Date_match'];
							
						if (utyDateCmpFr($dateFin, $row['Date_match']) < 0)
							$dateFin = $row['Date_match'];
					}
					
					if ($jourmatch != $row['Date_match'])
						array_push($arrayJours, $row['Date_match']);
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
		
		// Chargement des Equipes A et B ...
		if ($idMatch < 0 && $lstJournee != '') {
			$in  = str_repeat('?,', count($arrayListJournees) - 1) . '?';
			$sql = "SELECT DISTINCT a.Id, a.Libelle, a.Poule, a.Tirage, a.Code_compet 
				FROM gickp_Competitions_Equipes a, gickp_Journees b 
				WHERE a.Code_compet = b.Code_competition 
				AND a.Code_saison = b.Code_saison 
				AND b.Id IN ($in) 
				ORDER BY a.Poule, a.Tirage, a.Libelle ";	 
			$result = $myBdd->pdo->prepare($sql);
			$result->execute($arrayListJournees);
		} elseif ($idMatch >= 0) {
			$sql = "SELECT a.Id, a.Libelle, a.Poule, a.Tirage, a.Code_compet 
				FROM gickp_Competitions_Equipes a, gickp_Journees b, gickp_Matchs c 
				WHERE a.Code_compet = b.Code_competition 
				AND a.Code_saison = b.Code_saison 
				AND b.Id = c.Id_journee 
				AND c.Id = ? 
				ORDER BY a.Poule, a.Tirage, a.Libelle ";	 
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($idMatch));
		}
			
		if ($lstJournee != '') {
			$Id_equipeA = utyGetSession('Id_equipeA', -1);
			$Id_equipeB = utyGetSession('Id_equipeB', -1);

			$arrayEquipeA = array();
			$arrayEquipeB = array();
			$arrayArbitre = array();
			$arrayArbitreEquipes = array();
			
			//ARBITRES
			// Les arbitres peuvent être des équipes	
			array_push($arrayArbitre, array('Matric' => '-1', 'Identite' => $lang['Pool_fin_de_liste']));
			array_push($arrayArbitreEquipes, array('Matric' => '-1', 'Identite' => '---------- ' . $lang['Equipes'] . ' ----------'));
		
			$arrayEquipes = [-1];
            while ($row = $result->fetch()) {
				$libelleEquipe = $row['Libelle'];
				$codeCompetition = $row['Code_compet'];
				
				if ($row['Id'] == $Id_equipeA) {
					array_push($arrayEquipeA, array('Id' => $row['Id'], 'Libelle' => $libelleEquipe, 'Poule' => $row['Poule'], 'Code_compet'=> $codeCompetition, 'Selection' => 'SELECTED'));
				} else {
					array_push($arrayEquipeA, array('Id' => $row['Id'], 'Libelle' => $libelleEquipe, 'Poule' => $row['Poule'], 'Code_compet'=> $codeCompetition, 'Selection' => ''));
				}

				if ($row['Id'] == $Id_equipeB) {
					array_push($arrayEquipeB, array('Id' => $row['Id'], 'Libelle' => $libelleEquipe, 'Poule' => $row['Poule'], 'Code_compet'=> $codeCompetition, 'Selection' => 'SELECTED'));
				} else {
					array_push($arrayEquipeB, array('Id' => $row['Id'], 'Libelle' => $libelleEquipe, 'Poule' => $row['Poule'], 'Code_compet'=> $codeCompetition, 'Selection' => ''));
				}
				array_push($arrayArbitreEquipes, array('Matric' => '', 'Identite' => $libelleEquipe));
			}

			// Les arbitres potentiels peuvent aussi être les joueurs des Equipes ...
			array_push($arrayArbitre, array('Matric' => '-1', 'Identite' => '---------- ' . $lang['Joueurs'] . ' ----------'));
			$in  = str_repeat('?,', count($arrayEquipes) - 1) . '?';
			$sql = "SELECT a.Matric, a.Nom, a.Prenom, b.Libelle, c.Arb, c.niveau, 
				(c.Arb IS NULL) AS sortCol 
				FROM gickp_Competitions_Equipes b, gickp_Competitions_Equipes_Joueurs a 
				LEFT OUTER JOIN gickp_Arbitre c ON a.Matric = c.Matric 
				WHERE a.Id_equipe = b.Id 
				AND b.Id IN ($in) 
				AND a.Capitaine <> 'X' 
				ORDER BY b.Libelle, sortCol, c.Arb, a.Nom, a.Prenom ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute($arrayEquipes);
			
			$libelleTemp = '';
            while ($row = $result->fetch()) {
				if ($row['Libelle'] != $libelleTemp) {
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
			array_push($arrayArbitre, array('Matric' => '-1', 'Identite' => '---------- ' . $lang['Pool_Arbitres'] . ' ----------'));
			$sql2 = "SELECT a.Matric, a.Nom, a.Prenom, b.Libelle, c.Arb, c.niveau 
				FROM gickp_Competitions_Equipes b, gickp_Competitions_Equipes_Joueurs a 
				LEFT OUTER JOIN gickp_Arbitre c ON a.Matric = c.Matric 
				WHERE a.Id_equipe = b.Id 
				AND a.Capitaine = 'A' 
				AND b.Code_compet = 'POOL' 
				ORDER BY a.Nom, a.Prenom ";
			$result2 = $myBdd->pdo->prepare($sql2);
			$result2->execute();
            while ($row2 = $result2->fetch()) {
				if (strlen($row2['Arb']) > 0) {
                    $arb = ' ' . strtoupper($row2['Arb']);
                } else {
                    $arb = '';
                }
                if ($row2['niveau'] != '') {
                    $arb .= '-' . $row2['niveau'];
                }
                $row2['Libelle'] = substr($row2['Libelle'],0,3);
				$row2['Libelle'] = str_replace('Poo', 'Pool', $row2['Libelle']);
				array_push($arrayArbitre, array('Matric' => $row2['Matric'], 'Identite' => ucwords(strtolower($row2['Nom'])).' '.ucwords(strtolower($row2['Prenom'])).' ('.$row2['Libelle'].')'.$arb));
			}
			
			$this->m_tpl->assign('arrayEquipeA', $arrayEquipeA);
			$this->m_tpl->assign('arrayEquipeB', $arrayEquipeB);
			$this->m_tpl->assign('arrayArbitre', $arrayArbitre);
			$this->m_tpl->assign('arrayArbitreEquipes', $arrayArbitreEquipes);

			$this->m_tpl->assign('idCurrentJournee', $idJournee);
			$this->m_tpl->assign('arrayJournees', $arrayJournees);
			$this->m_tpl->assign('arrayJourneesAutorisees', $arrayJourneesAutorisees);
			$this->m_tpl->assign('arrayJourneesAutoriseesFiltre', $arrayJourneesAutoriseesFiltre);
		}

		$this->m_tpl->assign('codeCurrentCompet', $codeCompet);
		$this->m_tpl->assign('arrayCompetition', $arrayCompetition);
		
		if(($idEvenement == -1) && ($codeCompet == '*') && ($idSelJournee == '*') && ($_SESSION['Profile'] < 4)) {
			$TropDeMatchs = 'disabled';
			$TropDeMatchsMsg = ' (TROP DE MATCHS SELECTIONNES)';
		} else {
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
		
		$_SESSION['Intervalle_match'] = utyGetPost('Intervalle_match', utyGetSession('Intervalle_match', 40));
		
		$myBdd = $this->myBdd;
		// Chargement des Matchs des journées ...
		$sql = "SELECT Numero_ordre, Date_match, Heure_match, Terrain, `Type` 
			FROM gickp_Matchs 
			WHERE Id_journee = ? 
			ORDER BY Date_match, Heure_match, Numero_ordre ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($idJournee));
		while ($row = $result->fetch()) {
			$lastNumOrdre = $row['Numero_ordre'];
			$lastDate = $row['Date_match'];
			$lastHeure = $row['Heure_match'];
			$lastTerrain = $row['Terrain'];
			$lastType = $row['Type'];
		}
		if ($result->rowCount() > 0) {
			if ($_SESSION['lang'] == 'fr') {
				$_SESSION['Date_match'] = utyDateUsToFr($lastDate);
			} else {
				$_SESSION['Date_match'] = $lastDate;
			}
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
		$myBdd = $this->myBdd;
        
        $idMatch = utyGetSession('idMatch', 0);
		
		$_SESSION['Intervalle_match'] = utyGetPost('Intervalle_match', utyGetSession('Intervalle_match', 40));
		
		$idJournee = (int) utyGetPost('comboJournee', 0);

		$numMatch = (int) utyGetPost('Num_match', '');
		$dateMatch = trim(utyGetPost('Date_match', ''));
		$heureMatch = trim(utyGetPost('Heure_match', ''));
		$Libelle = trim(utyGetPost('Libelle', ''));
		$Terrain = trim(utyGetPost('Terrain', ''));
		$Type = trim(utyGetPost('Type', ''));
		
		$idEquipeA = (int) utyGetPost('idEquipeA', -1);
		$idEquipeB = (int) utyGetPost('idEquipeB', -1);
	
		$arbitre1 = trim(utyGetPost('arbitre1', ''));
		if (strlen($arbitre1) == 0)
			$arbitre1 = trim(utyGetPost('comboarbitre1', ''));
		$arbitre1_matric = (int) utyGetPost('arbitre1_matric', '');
			
		$arbitre2 = trim(utyGetPost('arbitre2', ''));
		if (strlen($arbitre2) == 0)
			$arbitre2 = trim(utyGetPost('comboarbitre2', ''));
		$arbitre2_matric = (int) utyGetPost('arbitre2_matric', '');
			
		$coeffA = (float) utyGetPost('coeffA', 1);
		if (strlen($coeffA) == 0 || $coeffA == 0)
			$coeffA = 1.0;
			
		$coeffB = (float) utyGetPost('coeffB', 1);
		if (strlen($coeffB) == 0 || $coeffB == 0)
			$coeffB = 1.0;
		
		if ( $idMatch > 0 && $idJournee != 0 ) {
			if (strlen($numMatch) == 0)
				$numMatch = $this->LastNumeroOrdre($idJournee) + 1;
			
			$sql = "SELECT Id_equipeA, Id_equipeB 
				FROM gickp_Matchs 
				WHERE Id = ? ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($idMatch));
			$row = $result->fetch();
			// if (isset($row[0]["Id_equipeA"]))
				$anciene_equipeA = $row["Id_equipeA"];
			// if (isset($row[0]["Id_equipeB"]))
				$anciene_equipeB = $row["Id_equipeB"];

			$sql = "UPDATE gickp_Matchs 
				SET Id_journee = ?, Numero_ordre = ?, 
				Date_match = ?, Heure_match = ?, 
				Libelle = ?, Terrain = ?, `Type` = ?, 
				Id_equipeA = ?, Id_equipeB = ?, Arbitre_principal = ?, 
				Arbitre_secondaire = ?, Matric_arbitre_principal = ?, 
				Matric_arbitre_secondaire = ?, CoeffA = ?, CoeffB = ? 
				WHERE Id = ? 
				AND `Validation` != 'O' ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array(
				$idJournee, $numMatch, utyDateFrToUs($dateMatch), $heureMatch, $Libelle, $Terrain, 
				$Type, $idEquipeA, $idEquipeB, $arbitre1, $arbitre2, $arbitre1_matric, 
				$arbitre2_matric, $coeffA, $coeffB, $idMatch
			));
			
			//Vidage des joueurs si l'équipe est vide ou modifiée
			if ($idEquipeA == -1 or $idEquipeA != $anciene_equipeA) {
				$sql = "DELETE FROM gickp_Matchs_Joueurs 
					WHERE Id_match = ? 
					AND Equipe = 'A' ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array($idMatch));
			}
			if ($idEquipeB == -1 or $idEquipeB != $anciene_equipeB) {
				$sql = "DELETE FROM gickp_Matchs_Joueurs 
					WHERE Id_match = ? 
					AND Equipe = 'B' ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array($idMatch));
			}
			
			$this->Raz();
			
		}
		
		$myBdd->utyJournal('Modification match', '', '', null, $idJournee, $idMatch);
	}
			
	function Add()
	{
		$myBdd = $this->myBdd;
		$idJournee = (int) utyGetPost('comboJournee', 0);
		
		$numMatch = (int) utyGetPost('Num_match', '');
		$dateMatch = trim(utyGetPost('Date_match', ''));
		$heureMatch = trim(utyGetPost('Heure_match', ''));
		$Libelle = trim(utyGetPost('Libelle', ''));
		$Terrain = trim(utyGetPost('Terrain', ''));
		$Type = trim(utyGetPost('Type', ''));
				
		$idEquipeA = (int) utyGetPost('idEquipeA', -1);
		$idEquipeB = (int) utyGetPost('idEquipeB', -1);
		
		$arbitre1 = trim(utyGetPost('arbitre1', ''));
		if (strlen($arbitre1) == 0) {
            $arbitre1 = trim(utyGetPost('comboarbitre1', ''));
        }
        $arbitre1_matric = (int) utyGetPost('arbitre1_matric', -1);
					
		$arbitre2 = trim(utyGetPost('arbitre2', ''));
		if (strlen($arbitre2) == 0) {
            $arbitre2 = trim(utyGetPost('comboarbitre2', ''));
        }
        $arbitre2_matric = (int) utyGetPost('arbitre2_matric', -1);
		
		$coeffA = (float) utyGetPost('coeffA', 1);
		if (strlen($coeffA) == 0) {
            $coeffA = 1.0;
        }

        $coeffB = (float) utyGetPost('coeffB', 1);
		if (strlen($coeffB) == 0) {
            $coeffB = 1.0;
        }

        if ($idJournee != 0) {
			if (strlen($numMatch) == 0) {
                $numMatch = $this->LastNumeroOrdre($idJournee) + 1;
            }

			$sql = "INSERT INTO gickp_Matchs (Id_journee, Numero_ordre, Date_match, Heure_match, 
				Libelle, Terrain, `Type`, Id_equipeA, Id_equipeB, Arbitre_principal, 
				Arbitre_secondaire, Matric_arbitre_principal, Matric_arbitre_secondaire, CoeffA, CoeffB) 
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array(
				$idJournee, $numMatch, utyDateFrToUs($dateMatch), $heureMatch, $Libelle, $Terrain, 
				$Type, $idEquipeA, $idEquipeB, $arbitre1, $arbitre2, $arbitre1_matric, 
				$arbitre2_matric, $coeffA, $coeffB
			));
		}
		
		$_SESSION['Intervalle_match'] = utyGetPost('Intervalle_match', utyGetSession('Intervalle_match', 40));
		
		$myBdd->utyJournal('Ajout match', '', '', null, $idJournee, $numMatch, $dateMatch.' '.$heureMatch);
		
		$_SESSION['idJournee'] = $idJournee;
		$this->Raz();		
	}
	
	function Remove()
	{
		$ParamCmd = utyGetPost('ParamCmd', '');
			
		$arrayParam = explode(',', $ParamCmd);
		if (count($arrayParam) == 0)
			return; // Rien à Detruire ...
		
		$myBdd = $this->myBdd;
		
		//Contrôle suppression possible
		$in  = str_repeat('?,', count($arrayParam) - 1) . '?';
		$sql = "SELECT Id 
			FROM gickp_Matchs_Detail 
			WHERE Id_match IN ($in) ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute($arrayParam);
		if ($result->rowCount() > 0)
			return "Il reste des évènements dans ces matchs ! Suppression impossible ";
		
		//Vidage des joueurs du match
		$sql = "DELETE FROM gickp_Matchs_Joueurs 
			USING gickp_Matchs_Joueurs, gickp_Matchs 
			WHERE gickp_Matchs_Joueurs.Id_match = gickp_Matchs.Id 
			AND gickp_Matchs_Joueurs.Id_match IN ($in) 
			AND gickp_Matchs.Validation != 'O'; ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute($arrayParam);
        
		// Suppression
		$sql  = "DELETE FROM gickp_Matchs 
			WHERE Id IN ($in) 
			AND `Validation` != 'O' ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute($arrayParam);

		$myBdd->utyJournal('Suppression matchs', '', '', null, null, $ParamCmd);
		return;
	}
	
	function LastNumeroOrdre($idJournee)
	{
		$myBdd = $this->myBdd;
		
		$sql = "SELECT Code_competition, Code_saison, Date_debut 
			FROM gickp_Journees 
			WHERE Id = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($idJournee));
		if ($result->rowCount() == 1) {
			$row = $result->fetch();
			
			$codeCompet = $row['Code_competition'];
			$codeSaison = $row['Code_saison'];
			$dateDebut = $row['Date_debut'];
		}
		
		$sql = "SELECT MAX(Numero_ordre) MaxNumeroOrdre 
			FROM gickp_Matchs 
			WHERE Id_journee IN (
				SELECT Id 
				FROM gickp_Journees 
				WHERE Code_competition = ? 
				AND Code_saison = ? 
				AND Date_debut <= ? 
			) ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($codeCompet, $codeSaison, $dateDebut));
		if ($result->rowCount() == 1) {
			$row = $result->fetch();
			return $row['MaxNumeroOrdre'];
		}
	
		return 0;
	}

	function ParamMatch()
	{
		$idMatch = (int) utyGetPost('ParamCmd', -1);
		$_SESSION['idMatch'] = $idMatch;
		
		$_POST['comboJournee2'] = '';
		
		$myBdd = $this->myBdd;

		$sql = "SELECT Id_journee, Numero_ordre, Date_match, Heure_match, Libelle, 
			Terrain, `Type`, Id_equipeA, Id_equipeB, Arbitre_principal, Arbitre_secondaire, 
			Matric_arbitre_principal, Matric_arbitre_secondaire, CoeffA, CoeffB 
			FROM gickp_Matchs 
			WHERE Id = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($idMatch));
		if ($result->rowCount() == 1) {
			$row = $result->fetch();			
			$_SESSION['idJournee'] = $row['Id_journee'];
			$_SESSION['Num_match'] = $row['Numero_ordre'];
			if ($_SESSION['lang'] == 'fr') {
				$row['Date_match'] = utyDateUsToFr($row['Date_match']);
			}
			$_SESSION['Date_match'] = $row['Date_match'];
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
		$myBdd = $this->myBdd;
		
		$idJournee = (int)utyGetPost('comboJournee', 0);
		
		$myBdd = $this->myBdd;
		
  		// Chargement des Matchs de la journée ...
		$sql = "SELECT Id, Id_equipeA, Id_equipeB 
			FROM gickp_Matchs 
			WHERE Id_journee = ?
			AND `Validation` <> 'O' ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($idJournee));
		while ($row = $result->fetch()) {		
			$idMatch = $row['Id'];
			$idEquipeA = $row['Id_equipeA'];
			$idEquipeB = $row['Id_equipeB'];
	
			$sql = "DELETE FROM gickp_Matchs_Joueurs 
				WHERE Id_match = ? 
				AND Equipe = 'A'";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($idMatch));
					
			$sql = "REPLACE INTO gickp_Matchs_Joueurs 
				SELECT ?, Matric, Numero, 'A', Capitaine 
				FROM gickp_Competitions_Equipes_Joueurs 
				WHERE Id_equipe = ? 
				AND Capitaine <> 'X' 
				AND Capitaine <> 'A' ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($idMatch, $idEquipeA));
						
			$sql = "DELETE FROM gickp_Matchs_Joueurs 
				WHERE Id_match = ? 
				AND Equipe = 'B'";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($idMatch));
					
			$sql  = "REPLACE INTO gickp_Matchs_Joueurs 
				SELECT ?, Matric, Numero, 'B', Capitaine 
				FROM gickp_Competitions_Equipes_Joueurs 
				WHERE Id_equipe = ? 
				AND Capitaine <> 'X' 
				AND Capitaine <> 'A' ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($idMatch, $idEquipeB));
		}
		
		$myBdd->utyJournal('Initialisation titulaires', '', '', null, $idJournee);
	}
	
	function PubliMatch()
	{
		$myBdd = $this->myBdd;
		$codeSaison = $myBdd->GetActiveSaison();
		$idMatch = (int) utyGetPost('ParamCmd', 0);
		(utyGetPost('Pub', '') != 'O') ? $changePub = 'O' : $changePub = 'N';
		
		$sql = "UPDATE gickp_Matchs 
			SET Publication = ? 
			WHERE Id = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($changePub, $idMatch));
		
		$myBdd->utyJournal('Publication match', $codeSaison, '', null, null, $idMatch, $changePub);
	}
	
	function PubliMultiMatchs()
	{
		$ParamCmd = utyGetPost('ParamCmd', '');
			
		$arrayParam = explode(',', $ParamCmd);
		if (count($arrayParam) == 0)
			return; // Rien à changer ...

		$myBdd = $this->myBdd;
		$codeSaison = $myBdd->GetActiveSaison();
		
		$sql = "SELECT Publication 
			FROM gickp_Matchs 
			WHERE Id = ? ";
		$result = $myBdd->pdo->prepare($sql);

		$sql2 = "UPDATE gickp_Matchs 
			SET Publication = ? 
			WHERE Id = ? ";
		$result2 = $myBdd->pdo->prepare($sql2);

		// Change Publication	
		for ($i=0;$i<count($arrayParam);$i++) {
			$result->execute(array($arrayParam[$i]));
			if ($result->rowCount() != 1)
				continue;
			$row = $result->fetch();
			($row['Publication']=='O') ? $changePub = 'N' : $changePub = 'O';
			$result2->execute(array($changePub, $arrayParam[$i]));
			$myBdd->utyJournal('Publication match', $codeSaison, '', null, null, $arrayParam[$i], $changePub);
		}
	}
	
	function VerrouPubliMultiMatchs()
	{
		$ParamCmd = utyGetPost('ParamCmd', '');
			
		$arrayParam = explode(',', $ParamCmd);		
		if (count($arrayParam) == 0)
			return; // Rien à changer ...

		$myBdd = $this->myBdd;
		$codeSaison = $myBdd->GetActiveSaison();
		
		$in  = str_repeat('?,', count($arrayParam) - 1) . '?';
		$sql = "UPDATE gickp_Matchs 
			SET Publication = 'O', Validation = 'O' 
			WHERE Id IN ($in) ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute($arrayParam);
		// Change Publication	
		for ($i=0;$i<count($arrayParam);$i++) {
			$myBdd->utyJournal('Verrou-Publi match', $codeSaison, '', null, null, $arrayParam[$i], 'O');
		}
	}
	
	function VerrouMatch()
	{
		$idMatch = (int) utyGetPost('ParamCmd', 0);
		(utyGetPost('Verrou', '') != 'O') ? $changeVerrou = 'O' : $changeVerrou = 'N';
		
		$myBdd = $this->myBdd;
		$codeSaison = $myBdd->GetActiveSaison();

		$sql = "UPDATE gickp_Matchs 
			SET Validation = ? 
			WHERE Id = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($changeVerrou, $idMatch));
		
		$myBdd->utyJournal('Verrouillage match', $codeSaison, '', null, null, $idMatch, $changeVerrou);
	}
	
	
	function VerrouMultiMatchs()
	{
		$ParamCmd = utyGetPost('ParamCmd', '');
			
		$arrayParam = explode(',', $ParamCmd);		
		if (count($arrayParam) == 0)
			return; // Rien à changer ...

		$myBdd = $this->myBdd;
		$codeSaison = $myBdd->GetActiveSaison();
		
		$sql = "SELECT `Validation` 
			FROM gickp_Matchs 
			WHERE Id = ? ";
		$result = $myBdd->pdo->prepare($sql);

		$sql2 = "UPDATE gickp_Matchs 
			SET `Validation` = ? 
			WHERE Id = ? ";
		$result2 = $myBdd->pdo->prepare($sql2);

		// Change Publication	
		for ($i=0;$i<count($arrayParam);$i++) {
			$result->execute(array($arrayParam[$i]));
			if ($result->rowCount() != 1)
				continue;
			$row = $result->fetch();	
			($row['Validation']=='O') ? $changeVerrou = 'N' : $changeVerrou = 'O';
			$result2->execute(array($changeVerrou, $arrayParam[$i]));
			$myBdd->utyJournal('Verrouillage match', $codeSaison, '', null, null, $arrayParam[$i], $changeVerrou);
		}
	}
	
	function AffectMultiMatchs() // Affect. Auto
	{
		// Affectation auto des équipes	dans les matchs
		$ParamCmd = utyGetPost('ParamCmd', '');
			
		$arrayParam = explode(',', $ParamCmd);		
		if (count($arrayParam) == 0)
			return; // Rien à affecter ...

		$myBdd = $this->myBdd;

		// $texte = '';
		
		$sql1 = "SELECT m.Libelle, m.Id_journee, m.Id_equipeA, m.Id_equipeB, 
			m.Matric_arbitre_principal, m.Matric_arbitre_secondaire, j.Code_competition, 
			j.Code_saison 
			FROM gickp_Matchs m, gickp_Journees j 
			WHERE m.Id = ? 
			AND m.Id_journee = j.Id 
			AND m.Validation <> 'O' 
			AND (m.ScoreA = '' 
				OR m.ScoreA = '?' 
				OR m.ScoreA IS NULL) "; 
		$result1 = $myBdd->pdo->prepare($sql1);

		$sql2 = "SELECT ce.Id, ce.Libelle Nom_equipe 
			FROM gickp_Competitions_Equipes ce 
			WHERE ce.Tirage = ? 
			AND ce.Code_compet = ? 
			AND ce.Code_saison = ? ";
		$result2 = $myBdd->pdo->prepare($sql2);

		$sql3 = "SELECT m.Id_equipeA, m.Id_equipeB, ce.Libelle Nom_equipeA, 
			ce2.Libelle Nom_equipeB, m.ScoreA, m.ScoreB 
			FROM gickp_Matchs m, gickp_Journees j, gickp_Competitions_Equipes ce, 
			gickp_Competitions_Equipes ce2 
			WHERE m.Numero_ordre = ? 
			AND m.Id_journee = j.Id 
			AND m.Id_equipeA = ce.Id 
			AND m.Id_equipeB = ce2.Id 
			AND m.ScoreA <> m.ScoreB 
			AND j.Code_competition = ? 
			AND j.Code_saison = ? ";
		$result3 = $myBdd->pdo->prepare($sql3);

		$sql4 = "SELECT m.Libelle, m.Id_journee, m.Id_equipeA, m.Id_equipeB, 
			ce.Libelle Nom_equipeA, ce2.Libelle Nom_equipeB, m.ScoreA, m.ScoreB 
			FROM gickp_Matchs m, gickp_Journees j, gickp_Competitions_Equipes ce, 
			gickp_Competitions_Equipes ce2 
			WHERE m.Numero_ordre = ? 
			AND m.Id_journee = j.Id 
			AND m.Id_equipeA = ce.Id 
			AND m.Id_equipeB = ce2.Id 
			AND m.ScoreA <> m.ScoreB 
			AND j.Code_competition = ? 
			AND j.Code_saison = ? ";
		$result4 = $myBdd->pdo->prepare($sql4);

		$sql5  = "SELECT cej.Id, ce.Libelle Nom_equipe 
			FROM gickp_Competitions_Equipes_Journee cej, gickp_Journees j, 
			gickp_Competitions_Equipes ce 
			WHERE cej.Clt = :codeNumero
			AND cej.Id_journee = j.Id 
			AND cej.Id = ce.Id 
			AND (j.Phase LIKE :codePoule 
				OR j.Phase LIKE CONCAT('%poule ', :codePoule, '%') 
				OR j.Phase LIKE CONCAT('%Poule ', :codePoule, '%') 
				OR j.Phase LIKE CONCAT('%Groupe ', :codePoule, '%') 
				OR j.Phase LIKE CONCAT('%Group ', :codePoule, '%') )
			AND j.Code_competition = :codeCompetition 
			AND j.Code_saison = :codeSaison ";
		$result5 = $myBdd->pdo->prepare($sql5);

		// pour chaque match coché
		for ($i=0; $i<count($arrayParam); $i++) {
			$id = $arrayParam[$i];
			$result1->execute(array($id));
			if ($result1->rowCount() != 1)
				die("Erreur : L\'un des matchs a déjà un score ou est verrouillé !  (<a href='javascript:history.back()'>Retour</a>)");
			$row = $result1->fetch();
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
			// $texte .= '<br>'.$libelle[0].'<br>';
			// On sépare par tiret, slash, étoile, virgule ou point-virgule.
			$libelle = preg_split("/[\-\/*,;]/",$libelle[0]);
			// On analyse le contenu
			for ($j=0; $j<4; $j++) {
				// déjà un arbitre principal désigné nominativement
                if ($j == 2 && $row['Matric_arbitre_principal'] != 0) {
                    $selectNom[2] = '';
                    continue;
                }
				// déjà un arbitre secondaire désigné nominativement
                if($j == 3 && $row['Matric_arbitre_secondaire'] != 0) {
                    $selectNom[3] = '';
                    continue;
                }
                
                $codeTirage = '';
				$codeVainqueur = '';
				$codePerdant = '';
				$codePoule = '';
				if (isset($libelle[$j])) {
					preg_match("/([A-Z]+)/",$libelle[$j],$codeLettres); // lettre
					preg_match("/([0-9]+)/",$libelle[$j],$codeNumero); // numero... de match ou classement de poule ou tirage
					$posNumero = strpos($libelle[$j], $codeNumero[1]);
					$posLettres = strpos($libelle[$j], $codeLettres[1]);
					if ($posNumero > $posLettres) { // tirage ou match
						switch ($codeLettres[1]) {
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
					} else { // poule
						$codePoule = $codeLettres[1];
					}
				}
				if ($codeTirage != '') { // Tirage
					$result2->execute(array($codeNumero[1], $row['Code_competition'], $row['Code_saison']));
					if ($result2->rowCount() != 1) {
						$selectNum[$j] = 0;
						$selectNom[$j] = '';
						$clst = 'erreur10';
					} else {
						$row2 = $result2->fetch();
						$selectNum[$j] = $row2['Id'];
						$selectNom[$j] = addslashes($row2['Nom_equipe']);
						$clst = $row2['Nom_equipe'];
					}
					// $texte .= $codeNumero[1].'e poule '.$codePoule[1].' : '.$clst.'<br>';
				} elseif($codeVainqueur != '') {
					$result3->execute(array($codeNumero[1], $row['Code_competition'], $row['Code_saison']));
					if ($result3->rowCount() != 1)	{
						$selectNum[$j] = 0;
						$selectNom[$j] = '';
						$vainqueur = 'erreur11';
					} else {
						$row3 = $result3->fetch();
						if (($row3['ScoreA'] > $row3['ScoreB'] && $row3['ScoreA'] != 'F') 
							|| $row3['ScoreB'] == 'F') {
							$selectNum[$j] = $row3['Id_equipeA'];
							$selectNom[$j] = addslashes($row3['Nom_equipeA']);
							$vainqueur = $row3['Nom_equipeA'];
						} else {
							$selectNum[$j] = $row3['Id_equipeB'];
							$selectNom[$j] = addslashes($row3['Nom_equipeB']);
							$vainqueur = $row3['Nom_equipeB'];
						}
					}
					// $texte .= 'Vainqueur match '.$codeNumero[1].' : '.$vainqueur.'<br>';
				} elseif($codePerdant != '') {
					$result4->execute(array($codeNumero[1], $row['Code_competition'], $row['Code_saison']));
					if ($result4->rowCount() != 1) {
						$selectNum[$j] = 0;
						$selectNom[$j] = '';
						$perdant = 'erreur12';
					} else {
						$row4 = $result4->fetch();
						if (($row4['ScoreA'] < $row4['ScoreB'] && $row4['ScoreB'] != 'F') 
							|| $row4['ScoreA'] == 'F') {
							$selectNum[$j] = $row4['Id_equipeA'];
							$selectNom[$j] = addslashes($row4['Nom_equipeA']);
							$perdant = $row4['Nom_equipeA'];
						} else {
							$selectNum[$j] = $row4['Id_equipeB'];
							$selectNom[$j] = addslashes($row4['Nom_equipeB']);
							$perdant = $row4['Nom_equipeB'];
						}
					}
					// $texte .= 'Perdant match '.$codeNumero[1].' : '.$perdant.'<br>';
				} elseif($codePoule != '') {
					$result5->execute(array(
						':codeNumero' => $codeNumero[1],
						':codePoule' => $codePoule,
						':codeCompetition' => $row['Code_competition'], 
						':codeSaison' => $row['Code_saison']
					));
					if ($result5->rowCount() != 1) {
						$selectNum[$j] = 0;
						$selectNom[$j] = '';
						$clst = 'erreur13';
					} else {
						$row5 = $result5->fetch();
						$selectNum[$j] = $row5['Id'];
						$selectNom[$j] = addslashes($row5['Nom_equipe']);
						$clst = $row5['Nom_equipe'];
					}
					// $texte .= $codeNumero[1].'e poule '.$codePoule.' : '.$clst.'<br>';
				} else {
					$selectNum[$j]=0;
					$selectNom[$j]='';
				}
			}

			// Affectation
			$sql = "UPDATE gickp_Matchs 
				SET Id_equipeA = ?, Id_equipeB = ? ";
			$arrayQuery = array($selectNum[0], $selectNum[1]);
			if ($selectNom[2] != '') {
				$sql .= ", Arbitre_principal = ? ";
				$arrayQuery = array_merge($arrayQuery, [$selectNom[2]]);
			}
			if ($selectNom[3] != '') {
				$sql .= ", Arbitre_secondaire = ? ";
				$arrayQuery = array_merge($arrayQuery, [$selectNom[3]]);
			}
			$sql .= " WHERE Id = ? ";
			$arrayQuery = array_merge($arrayQuery, [$id]);
			$result = $myBdd->pdo->prepare($sql);
			$result->execute($arrayQuery);
			//Suppression des joueurs existants si changements d'équipes
			if ($selectNum[0] != $anciene_equipeA) {
				$sql = "DELETE FROM gickp_Matchs_Joueurs 
					WHERE Id_match = ? 
					AND Equipe = 'A' ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array($id));
			}
			if ($selectNum[1] != $anciene_equipeB) {
				$sql = "DELETE FROM gickp_Matchs_Joueurs 
					WHERE Id_match = ? 
					AND Equipe = 'B' ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array($id));
			}
			//Journal
			$myBdd->utyJournal('Affect auto équipes', $row['Code_saison'], $row['Code_competition'], null, $row['Id_journee'], $id, '');
		}
        return implode(',', $arrayParam);
	}

	
	function AnnulMultiMatchs() // Annul. Auto
	{
		// Annulation des affectations d'équipes dans les matchs
		$ParamCmd = utyGetPost('ParamCmd', '');
			
		$arrayParam = explode(',', $ParamCmd);		
		if (count($arrayParam) == 0)
			return; // Rien à affecter ...

		$myBdd = $this->myBdd;

		// $texte = '';
		
		$sql1 = "SELECT m.Libelle, m.Id_journee, j.Code_competition, j.Code_saison 
			FROM gickp_Matchs m, gickp_Journees j 
			WHERE m.Id = ? 
			AND m.Id_journee = j.Id 
			AND m.Validation <> 'O' 
			AND (m.ScoreA = '' 
				OR m.ScoreA = '?' 
				OR m.ScoreA IS NULL) "; 
		$result1 = $myBdd->pdo->prepare($sql1);

		$sql2 = "UPDATE gickp_Matchs 
			SET Id_equipeA = 0, Id_equipeB = 0, 
			Arbitre_principal = -1, Arbitre_secondaire = -1 
			WHERE Id = ? ";
		$result2 = $myBdd->pdo->prepare($sql2);

		$sql3 = "DELETE FROM gickp_Matchs_Joueurs 
		WHERE Id_match = ? ";
		$result3 = $myBdd->pdo->prepare($sql3);


		// pour chaque match coché
		for ($i=0;$i<count($arrayParam);$i++) {
			$id = $arrayParam[$i];
			$result1->execute(array($id));
			if ($result1->rowCount() != 1)
				die("Erreur : L\'un des matchs a déjà un score ou est verrouillé !  (<a href='javascript:history.back()'>Retour</a>)");
			$row = $result1->fetch();

			$result2->execute(array($id));
		
			$myBdd->utyJournal('Annul auto équipes', $row['Code_saison'], $row['Code_competition'], null, $row['Id_journee'], $id, '');
			//Suppression des joueurs existants
			$result3->execute(array($id));
		}
		
	}

	function ChangeMultiMatchs()
	{
		$ParamCmd = utyGetPost('ParamCmd', '');
			
		$arrayParam = explode(',', $ParamCmd);		
		if (count($arrayParam) == 0)
			return; // Rien à changer ...

		$idJournee = (int) utyGetPost('comboJournee', 0);

		$myBdd = $this->myBdd;
		$codeSaison = $myBdd->GetActiveSaison();
		
		$sql = "UPDATE gickp_Matchs 
			SET Id_journee = ? 
			WHERE Id = ? 
			AND Validation != 'O' ";
		$result = $myBdd->pdo->prepare($sql);

		// Change Journee	
		for ($i=0;$i<count($arrayParam);$i++) {
			$result->execute(array($idJournee, $arrayParam[$i]));
			$myBdd->utyJournal('Change Journee match', $codeSaison, '', null, null, $arrayParam[$i], $idJournee);
		}
	}
	
	function __construct()
	{			
	  	MyPageSecure::MyPageSecure(10);
		
		$this->myBdd = new MyBdd();

		$alertMessage = '';
	  		
		$Cmd = utyGetPost('Cmd', '');

		$ParamCmd = utyGetPost('ParamCmd', '');
        
       	$arrayCheck = '';

		if (strlen($Cmd) > 0) {
			if ($Cmd == 'Add')
				($_SESSION['Profile'] <= 6) ? $this->Add() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'Update')
				($_SESSION['Profile'] <= 6) ? $this->Update() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'Raz')
				($_SESSION['Profile'] <= 6) ? $this->Raz() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'Remove')
				($_SESSION['Profile'] <= 6) ? $alertMessage = $this->Remove() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
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
				
			if ($Cmd == 'AffectMultiMatchs') {
				if ($_SESSION['Profile'] <= 6) {
                    $arrayCheck = $this->AffectMultiMatchs();
                    $alertMessage = 'Affectation OK';
                } else { 
                    $alertMessage = 'Vous n avez pas les droits pour cette action.';
                }
            }

			if ($Cmd == 'AnnulMultiMatchs')
				($_SESSION['Profile'] <= 6) ? $this->AnnulMultiMatchs() : $alertMessage = 'Vous n avez pas les droits pour cette action.';

			if ($Cmd == 'ChangeMultiMatchs')
				($_SESSION['Profile'] <= 6) ? $this->ChangeMultiMatchs() : $alertMessage = 'Vous n avez pas les droits pour cette action.';

            
			if ($alertMessage == '') {
				header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);	
				exit;
			}
		}
        
		if ($ParamCmd == 'changeCompet')
			$_SESSION['idMatch'] = -1; // La Combo Compétition a changé => Plus aucun match n'est sélectionné ...
		
		$this->SetTemplate("Gestion_des_matchs", "Matchs", false);
		$this->Load();
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		
		$this->m_tpl->assign('idMatch', utyGetSession('idMatch', 0));
//		$this->m_tpl->assign('idJournee', utyGetSession('idJournee', 0));
		
		$this->m_tpl->assign('Intervalle_match', utyGetSession('Intervalle_match', '40'));
		$this->m_tpl->assign('Num_match', utyGetSession('Num_match', ''));
		if ($_SESSION['lang'] == 'fr') {
			$this->m_tpl->assign('Date_match', utyDateUsToFr(utyGetSession('Date_match', '')));
		} else {
			$this->m_tpl->assign('Date_match', utyDateFrToUs(utyGetSession('Date_match', '')));
		}
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
        
		$this->m_tpl->assign('arrayCheck', $arrayCheck);
		
		$this->DisplayTemplate('GestionJournee');
	}
}		  	

$page = new GestionJournee();

