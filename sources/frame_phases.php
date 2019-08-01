<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

// Gestion des Classements
	
class Classement extends MyPage	 
{	
	function Load()
	{
		$myBdd = new MyBdd();
		
		$codeCompet = utyGetSession('codeCompet', 'N1H');
		$codeCompet = utyGetPost('codeCompet', $codeCompet);
		$codeCompet = utyGetGet('Compet', $codeCompet);
		$_SESSION['codeCompet'] = $codeCompet;
		$this->m_tpl->assign('codeCompet', $codeCompet);
			
		$codeSaison = utyGetSaison();
		$codeSaison = utyGetPost('saisonTravail', $codeSaison);
		$codeSaison = utyGetGet('Saison', $codeSaison);
		$_SESSION['Saison'] = $codeSaison;
		$this->m_tpl->assign('Saison', $codeSaison);
        
        $event = utyGetGet('event', '0');
		$this->m_tpl->assign('event', $event);
        
        if (utyGetGet('navGroup', false)) {
            $arrayNavGroup = $myBdd->GetOtherCompetitions($codeCompet, $codeSaison, true, $event);
            $this->m_tpl->assign('arrayNavGroup', $arrayNavGroup);
            $this->m_tpl->assign('navGroup', 1);
        }

        $group = utyGetGet('Group', $arrayNavGroup[0]['Code_ref']);
		$this->m_tpl->assign('group', $group);
        
        $Round = utyGetGet('Round', '*');
		$this->m_tpl->assign('Round', $Round);
        $Round = str_replace('*', '%', $Round);
        
        $this->m_tpl->assign('Css', utyGetGet('Css', ''));

        $recordCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);
		$this->m_tpl->assign('Code_ref', $recordCompetition['Code_ref']);
        
        //Logo
		if($codeCompet != -1) {
			$logo = "img/logo/".$codeSaison.'-'.$codeCompet.'.jpg';
			if (file_exists($logo)) {
                $this->m_tpl->assign('logo', $logo);
            }
        }

		// Chargement des Equipes ...
		$arrayEquipe = array();
		$arrayEquipe_journee = array();
		$arrayEquipe_journee_publi = array();
		$arrayEquipe_publi = array();
        $arrayJournee = array();
        $arrayMatchs = array();

		// Par défaut type Championnat et compétition non internationale...
		$typeClt = $recordCompetition['Code_typeclt'];
        
        $journee = 0;
		
		if (strlen($codeCompet) > 0) {
			// Classement public				
			$sql  = "SELECT ce.*, c.Code_comite_dep "
                    . "FROM gickp_Competitions_Equipes ce, gickp_Club c "
                    . "WHERE ce.Code_compet = '$codeCompet' "
                    . "AND ce.Code_saison = $codeSaison "
                    . "AND ce.Code_club = c.Code ";
            if ($typeClt == 'CP') {
                $sql .= "ORDER BY CltNiveau_publi Asc, Diff_publi Desc ";
            } else {
                $sql .= "ORDER BY Clt_publi Asc, Diff_publi Desc ";
            }

            $result = $myBdd->Query($sql);
            while ($row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC)) { 
                //Logos
                $logo = '';
                $club = $row['Code_club'];
                if (is_file('img/KIP/logo/'.$club.'-logo.png')) {
                    $logo = 'img/KIP/logo/'.$club.'-logo.png';
                } elseif (is_file('img/Nations/'.substr($club, 0, 3).'.png')) {
                    $club = substr($club, 0, 3);
                    $logo = 'img/Nations/'.$club.'.png';
                }
				if (strlen($row['Code_comite_dep']) > 3) {
                    $row['Code_comite_dep'] = 'FRA';
                }
                array_push($arrayEquipe_publi, array( 'Id' => $row['Id'], 'Numero' => $row['Numero'], 'Libelle' => $row['Libelle'], 'Code_club' => $row['Code_club'], 'Code_comite_dep' => $row['Code_comite_dep'],
																        'Clt' => $row['Clt_publi'], 'Pts' => $row['Pts_publi'], 
																        'J' => $row['J_publi'], 'G' => $row['G_publi'], 'N' => $row['N_publi'], 
																        'P' => $row['P_publi'], 'F' => $row['F_publi'], 'Plus' => $row['Plus_publi'], 
																        'Moins' => $row['Moins_publi'], 'Diff' => $row['Diff_publi'],
																        'PtsNiveau' => $row['PtsNiveau_publi'], 'CltNiveau' => $row['CltNiveau_publi'], 
                                                                        'logo' => $logo, 'club' => $club ));
				if (($typeClt == 'CHPT' && $row['Clt_publi'] == 0) || ($typeClt == 'CP' && $row['CltNiveau_publi'] == 0)) {
					$recordCompetition['Qualifies']	= 0;
					$recordCompetition['Elimines'] = 0;
				}
			}
            $this->m_tpl->assign('arrayEquipe_publi', $arrayEquipe_publi);
			
            // Classement public par journée/phase
            if ($event > 0) {
                $sql  = "SELECT ce.Id, ce.Numero, ce.Libelle, ce.Code_club, "
                    . "cej.Id_journee, cej.Clt_publi, cej.Pts_publi, cej.J_publi, cej.G_publi, cej.N_publi, cej.P_publi, "
                    . "cej.F_publi, cej.Plus_publi, cej.Moins_publi, cej.Diff_publi, cej.PtsNiveau_publi, cej.CltNiveau_publi, "
                    . "j.Phase, j.Etape, j.Nbequipes, j.Niveau, j.Type, c.Code_comite_dep, "
                    . "j.Date_debut, j.Date_fin, j.Lieu, j.Departement "
                    . "FROM gickp_Competitions_Equipes ce, gickp_Competitions_Equipes_Journee cej, "
                    . "gickp_Journees j, gickp_Evenement_Journees ej, gickp_Club c "
                    . "WHERE ej.Id_journee = j.Id "
                    . "AND ej.Id_evenement = $event "
                    . "AND ce.Id = cej.Id "
                    . "AND cej.Id_journee = j.Id "
                    . "AND ce.Code_club = c.Code "
                    . "AND j.Code_competition = '$codeCompet' "
                    . "AND j.Code_saison = $codeSaison "
                    . "AND j.Etape LIKE '$Round' "
                    . "ORDER BY j.Etape, j.Niveau DESC, j.Date_debut DESC, j.Phase, "
                    . "cej.Clt_publi ASC, cej.Diff_publi DESC, cej.Plus_publi ASC ";
            } else {
                $sql  = "SELECT a.Id, a.Numero, a.Libelle, a.Code_club, "
                    . "b.Id_journee, b.Clt_publi, b.Pts_publi, b.J_publi, b.G_publi, b.N_publi, b.P_publi, "
                    . "b.F_publi, b.Plus_publi, b.Moins_publi, b.Diff_publi, b.PtsNiveau_publi, b.CltNiveau_publi, "
                    . "c.Phase, c.Niveau, c.Type, d.Code_comite_dep, c.Date_debut, c.Date_fin, c.Lieu, c.Departement "
                    . "FROM gickp_Competitions_Equipes a, "
                    . "gickp_Competitions_Equipes_Journee b, gickp_Journees c, gickp_Club d "
                    . "WHERE a.Id = b.Id "
                    . "AND b.Id_journee = c.Id "
                    . "AND a.Code_club = d.Code "
                    . "AND c.Code_competition = '$codeCompet' "
                    . "AND c.Code_saison = $codeSaison "
                    . "AND c.Etape LIKE '$Round' "
                    . "ORDER BY c.Niveau DESC, c.Date_debut DESC, b.Id_journee ASC, b.Clt_publi ASC, b.Diff_publi DESC, "
                        . "b.Plus_publi ASC ";
            }
            $result = $myBdd->Query($sql);
            while ($row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC)) {
                if (strlen($row['Code_comite_dep']) > 3) {
                    $row['Code_comite_dep'] = 'FRA';
                }
                if ($journee != $row['Id_journee']) {
                    $arrayJournee[] = array('Id_journee' => $row['Id_journee'], 'Phase' => $row['Phase'], 'Niveau' => $row['Niveau'], 'Type' => $row['Type'],
                                                'Date_debut' => $row['Date_debut'], 'Date_fin' => $row['Date_fin'],
                                                'Lieu' => $row['Lieu'], 'Departement' => $row['Departement'] );
                    $journee = $row['Id_journee'];
                }
                $arrayEquipe_journee_publi[$journee][] = array( 'Id' => $row['Id'], 'Numero' => $row['Numero'], 'Libelle' => $row['Libelle'], 'Code_club' => $row['Code_club'], 'Id_journee' => $row['Id_journee'], 
                                                                        'Phase' => $row['Phase'], 'Niveau' => $row['Niveau'], 'Type' => $row['Type'], 'Clt' => $row['Clt_publi'], 'Pts' => $row['Pts_publi'], 
                                                                        'J' => $row['J_publi'], 'G' => $row['G_publi'], 'N' => $row['N_publi'], 
                                                                        'P' => $row['P_publi'], 'F' => $row['F_publi'], 'Plus' => $row['Plus_publi'], 
                                                                        'Moins' => $row['Moins_publi'], 'Diff' => $row['Diff_publi'],
                                                                        'PtsNiveau' => $row['PtsNiveau_publi'], 'CltNiveau' => $row['CltNiveau_publi'],
                                                                        'Code_comite_dep' => $row['Code_comite_dep']  );
            }
            // Matchs publics par journée / phase
            if ($event > 0) {
                $sql  = "SELECT m.Id, m.Id_journee, m.Numero_ordre, m.Date_match, m.Heure_match, m.Libelle, m.Terrain, m.Publication, m.Validation, "
                    ."m.Statut, m.Periode, m.ScoreDetailA, m.ScoreDetailB, m.Id_equipeA, m.Id_equipeB, "
                    ."ce1.Libelle EquipeA, ce2.Libelle EquipeB, ce1.Numero NumA, ce2.Numero NumB, "
                    ."m.Terrain, m.ScoreA, m.ScoreB, m.CoeffA, m.CoeffB, "
                    ."m.Arbitre_principal, m.Arbitre_secondaire, m.Matric_arbitre_principal, m.Matric_arbitre_secondaire, "
                    ."j.Code_competition, j.Phase, j.Niveau, j.Lieu, j.Libelle LibelleJournee, j.Date_debut "
                    ."FROM gickp_Matchs m "
                    ."LEFT OUTER JOIN gickp_Competitions_Equipes ce1 ON (m.Id_equipeA = ce1.Id) "
                    ."LEFT OUTER JOIN gickp_Competitions_Equipes ce2 ON (m.Id_equipeB = ce2.Id) "
                    .", gickp_Journees j, gickp_Evenement_Journees ej "
                    ."WHERE ej.Id_journee = j.Id "
                    ."AND ej.Id_evenement = $event "
                    ."AND j.Code_competition = '$codeCompet' "
                    ."AND j.Code_saison = $codeSaison "
                    ."AND m.Id_journee = j.Id "
                    ."AND m.Publication = 'O' "
                    ."AND j.Etape LIKE '$Round' "
                    ."ORDER BY j.Etape, j.Niveau DESC, j.Id ASC ";
            } else {
                $sql  = "SELECT m.Id, m.Id_journee, m.Numero_ordre, m.Date_match, m.Heure_match, m.Libelle, m.Terrain, m.Publication, m.Validation, "
                    ."m.Statut, m.Periode, m.ScoreDetailA, m.ScoreDetailB, m.Id_equipeA, m.Id_equipeB, "
                    ."ce1.Libelle EquipeA, ce2.Libelle EquipeB, ce1.Numero NumA, ce2.Numero NumB, "
                    ."m.Terrain, m.ScoreA, m.ScoreB, m.CoeffA, m.CoeffB, "
                    ."m.Arbitre_principal, m.Arbitre_secondaire, m.Matric_arbitre_principal, m.Matric_arbitre_secondaire, "
                    ."j.Code_competition, j.Phase, j.Niveau, j.Lieu, j.Libelle LibelleJournee, j.Date_debut "
                    ."FROM gickp_Matchs m "
                    ."LEFT OUTER JOIN gickp_Competitions_Equipes ce1 ON (m.Id_equipeA = ce1.Id) "
                    ."LEFT OUTER JOIN gickp_Competitions_Equipes ce2 ON (m.Id_equipeB = ce2.Id) "
                    .", gickp_Journees j "
                    ."WHERE j.Code_competition = '$codeCompet' "
                    ."AND j.Code_saison = $codeSaison "
                    ."AND m.Id_journee = j.Id "
                    ."AND m.Publication = 'O' "
                    ."AND j.Etape LIKE '$Round' "
                    ."ORDER BY j.Etape, j.Niveau DESC, j.Id ASC ";
            }
            $result = $myBdd->Query($sql);
            while ($row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC)) {
                $journee = $row['Id_journee'];
                if ($row['Validation'] != 'O') {
                    $row['ScoreA'] = '';
                    $row['ScoreB'] = '';
                }
                if ($row['Id_equipeA'] <= 1 || $row['Id_equipeB'] <= 1) {
                    if ($_SESSION['lang'] == 'en' ) {
                        $intitule = utyEquipesAffectAuto($row['Libelle']);
                    } else {
                        $intitule = utyEquipesAffectAutoFR($row['Libelle']);
                    }
                }
                if ($row['Id_equipeA'] <= 1) {
                    $row['EquipeA'] = str_replace('(', '<i>', str_replace(')', '</i>', $intitule[0]));
                }
                if ($row['Id_equipeB'] <= 1) {
                    $row['EquipeB'] = str_replace('(', '<i>', str_replace(')', '</i>', $intitule[1]));
                }
                $arrayMatchs[$journee][] = $row ;            }
		}	
		$this->m_tpl->assign('arrayEquipe_journee_publi', $arrayEquipe_journee_publi);
        $this->m_tpl->assign('arrayJournee', $arrayJournee);
        $this->m_tpl->assign('arrayMatchs', $arrayMatchs);
        $this->m_tpl->assign('recordCompetition', $recordCompetition);
		$this->m_tpl->assign('Qualifies', $recordCompetition['Qualifies']);
		$this->m_tpl->assign('Elimines', $recordCompetition['Elimines']);
		$this->m_tpl->assign('page', 'phases');

		// Combo "CHPT" - "CP"		
		$arrayOrderCompetition = array();
		if ('CHPT' == $typeClt) {
            array_push($arrayOrderCompetition, array('CHPT', 'Championnat', 'SELECTED'));
        } else {
            array_push($arrayOrderCompetition, array('CHPT', 'Championnat', ''));
        }

        if ('CP' == $typeClt) {
            array_push($arrayOrderCompetition, array('CP', 'Coupe', 'SELECTED'));
        } else {
            array_push($arrayOrderCompetition, array('CP', 'Coupe', ''));
        }
        $this->m_tpl->assign('arrayOrderCompetition', $arrayOrderCompetition);
	}
	
	function GetTypeClt($codeCompet,  $codeSaison)
	{
		if (strlen($codeCompet) == 0) {
            return 'CHPT';
        }

        $myBdd = new MyBdd();
		
		$recordCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);
		$typeClt = $recordCompetition['Code_typeclt'];
		if ($typeClt != 'CP') {
            $typeClt = 'CHPT';
        }

        return $typeClt;
	}
	
	
	// ExistCompetitionEquipeNiveau
	function ExistCompetitionEquipeNiveau($idEquipe, $niveau)
	{
		$myBdd = new MyBdd();
	
		$sql  = "SELECT COUNT(*) Nb "
                . "FROM gickp_Competitions_Equipes_Niveau "
                . "WHERE Id = $idEquipe AND Niveau = $niveau ";
		$result = $myBdd->Query($sql);
		if ($myBdd->NumRows($result) == 1) {
			$row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC);	 
			if ($row['Nb'] == 1) {
                return;
            } // Le record existe ...
		}

		$sql  = "INSERT INTO gickp_Competitions_Equipes_Niveau (Id, Niveau, Pts, Clt, J, G, N, P, F, "
                . "Plus, Moins, Diff, PtsNiveau, CltNiveau) ";
		$sql .= "VALUES ($idEquipe, $niveau, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0) ";
		$myBdd->Query($sql);
	}

	// ExistCompetitionEquipeJournee
	function ExistCompetitionEquipeJournee($idEquipe, $idJournee)
	{
		$myBdd = new MyBdd();
		$sql  = "SELECT COUNT(*) Nb "
                . "FROM gickp_Competitions_Equipes_Journee "
                . "WHERE Id = $idEquipe "
                . "AND Id_journee = $idJournee";
		$result = $myBdd->Query($sql);
		if ($myBdd->NumRows($result) == 1) {
			$row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC);	 
			if ($row['Nb'] == 1)
				return; // Le record existe ...
		}

		$sql  = "INSERT INTO gickp_Competitions_Equipes_Journee (Id, Id_journee, Pts, Clt, J, G, N, P, F, "
                . "Plus, Moins, Diff, PtsNiveau, CltNiveau) ";
		$sql .= "VALUES ($idEquipe, $idJournee, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0) ";
		$myBdd->Query($sql);
	}
	

	// GestionClassement 		
	function Classement()
	{			
        MyPage::MyPage();

		$this->SetTemplate("Phases", "Classements", true);
		$this->Load();
        
		// COSANDCO : Gestion Param Voie ...
		if (isset($_GET['voie']))
		{
			$voie = (int) $_GET['voie'];
			if ($voie > 0)
			{
                $this->m_tpl->assign('voie', $voie);
			}
		}        

        $this->DisplayTemplateFrame('frame_phases');
	}
}		  	

$page = new Classement();
