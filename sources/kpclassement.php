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
	
        $recordCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);
		$this->m_tpl->assign('Code_ref', $recordCompetition['Code_ref']);
        
        //Logos
		if($codeCompet != -1)
		{
            $this->m_tpl->assign('visuels', utyGetVisuels($recordCompetition));
		}

		// Chargement des Equipes ...
		$arrayEquipe_journee_publi = array();
		$arrayEquipe_publi = array();
        $arrayJournee = array();
        $arrayMatchs = array();

		// Par défaut type Championnat et compétition non internationale...
		$typeClt = $recordCompetition['Code_typeclt'];
        
        $journee = 0;
		
		if (strlen($codeCompet) > 0)
		{
			// Classement public				
			$sql  = "SELECT ce.*, c.Code_comite_dep "
                    . "FROM gickp_Competitions_Equipes ce, gickp_Club c "
                    . "WHERE ce.Code_compet = '$codeCompet' "
                    . "AND ce.Code_saison = $codeSaison "
                    . "AND ce.Code_club = c.Code ";
            if ($typeClt == 'CP') {
                $sql .= "AND CltNiveau_publi > 0 ";
                $sql .= "ORDER BY CltNiveau_publi Asc, Diff_publi Desc ";	 
            } else {
                $sql .= "AND Clt_publi > 0 ";
                $sql .= "ORDER BY Clt_publi Asc, Diff_publi Desc ";
            }
	
            $result = $myBdd->Query($sql);
            while ($row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC)){ 
                //Logos
                $logo = '';
                $club = $row['Code_club'];
                if(is_file('img/KIP/logo/'.$club.'-logo.png')){
                    $logo = 'img/KIP/logo/'.$club.'-logo.png';
                }elseif(is_file('img/Nations/'.substr($club, 0, 3).'.png')){
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
                        . "ORDER BY c.Niveau DESC, c.Date_debut DESC, b.Id_journee ASC, b.Clt_publi ASC, b.Diff_publi DESC, b.Plus_publi ASC ";
                $result = $myBdd->Query($sql);
                while ($row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC)){
                    if (strlen($row['Code_comite_dep']) > 3) {
                        $row['Code_comite_dep'] = 'FRA';
                    }
                    if($journee != $row['Id_journee']){
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
                $sql  = "SELECT a.Id, a.Id_journee, a.Numero_ordre, a.Date_match, a.Heure_match, a.Libelle, a.Terrain, "
                        . "a.Publication, a.Validation, "
                        ."a.Statut, a.Periode, a.ScoreDetailA, a.ScoreDetailB, a.Id_equipeA, a.Id_equipeB, "
                        ."b.Libelle EquipeA, c.Libelle EquipeB, b.Numero NumA, c.Numero NumB, "
                        ."a.Terrain, a.ScoreA, a.ScoreB, a.CoeffA, a.CoeffB, "
                        ."a.Arbitre_principal, a.Arbitre_secondaire, a.Matric_arbitre_principal, a.Matric_arbitre_secondaire, "
                        ."d.Code_competition, d.Phase, d.Niveau, d.Lieu, d.Libelle LibelleJournee, d.Date_debut "
                        ."FROM gickp_Matchs a "
                        ."LEFT OUTER JOIN gickp_Competitions_Equipes b ON (a.Id_equipeA = b.Id) "
                        ."LEFT OUTER JOIN gickp_Competitions_Equipes c ON (a.Id_equipeB = c.Id) "
                        .", gickp_Journees d "
                        ."WHERE d.Code_competition = '$codeCompet' "
                        ."AND d.Code_saison = $codeSaison "
                        ."AND a.Id_journee = d.Id "
                        ."AND a.Publication = 'O' "
                        ."ORDER BY d.Niveau DESC, d.Id ASC ";
                $result = $myBdd->Query($sql);
                while ($row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC)){
                    $journee = $row['Id_journee'];
                    if($row['Validation'] != 'O'){
                        $row['ScoreA'] = '';
                        $row['ScoreB'] = '';
                    }
                    if($row['Id_equipeA'] > 1 && $row['Id_equipeB'] > 1){
                        $arrayMatchs[$journee][] = $row ;
                    }
                }


		}	
		$this->m_tpl->assign('arrayEquipe_journee_publi', $arrayEquipe_journee_publi);
        $this->m_tpl->assign('arrayJournee', $arrayJournee);
        $this->m_tpl->assign('arrayMatchs', $arrayMatchs);
        $this->m_tpl->assign('recordCompetition', $recordCompetition);
		$this->m_tpl->assign('Qualifies', $recordCompetition['Qualifies']);
		$this->m_tpl->assign('Elimines', $recordCompetition['Elimines']);

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
                . "WHERE Id = $idEquipe "
                . "AND Niveau = $niveau ";
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
		
		$this->SetTemplate("Classement", "Classements", true);
		$this->Load();
//		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplateNew('kpclassement');
	}
}		  	

$page = new Classement();
