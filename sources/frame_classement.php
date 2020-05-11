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
		
		$codeCompet = utyGetGet('Compet',  'N1H');
		$this->m_tpl->assign('codeCompet', $codeCompet);
			
		$codeSaison = utyGetGet('Saison', $myBdd->GetActiveSaison());
		$this->m_tpl->assign('Saison', $codeSaison);
        
        $event = utyGetGet('event', '0');
		$this->m_tpl->assign('event', $event);
        
        if (utyGetGet('navGroup', false)) {
            $arrayNavGroup = $myBdd->GetOtherCompetitions($codeCompet, $codeSaison, true, $event);
            $this->m_tpl->assign('arrayNavGroup', $arrayNavGroup);
            $this->m_tpl->assign('navGroup', 1);
			$group = utyGetGet('Group', $arrayNavGroup[0]['Code_ref']);
			$this->m_tpl->assign('group', $group);
        }

        
        $Round = utyGetGet('Round', '*');
		$this->m_tpl->assign('Round', $Round);

        $recordCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);
		$this->m_tpl->assign('Code_ref', $recordCompetition['Code_ref']);
        
        $this->m_tpl->assign('Css', utyGetGet('Css', ''));
        
        //Logo
		if($codeCompet != -1)
		{
			$logo = "img/logo/".$codeSaison.'-'.$codeCompet.'.jpg';
			if(file_exists($logo))
				$this->m_tpl->assign('logo', $logo);
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
		
		if (strlen($codeCompet) > 0)
		{
			// Classement public				
			$sql = "SELECT ce.*, c.Code_comite_dep 
				FROM gickp_Competitions_Equipes ce, gickp_Club c 
				WHERE ce.Code_compet = ? 
				AND ce.Code_saison = ? 
				AND ce.Code_club = c.Code ";
			if ($typeClt == 'CP') {
				$sql .= "AND CltNiveau_publi > 0 
					ORDER BY CltNiveau_publi Asc, Diff_publi Desc ";	 
			} else {
				$sql .= "AND Clt_publi > 0 
					ORDER BY Clt_publi Asc, Diff_publi Desc ";
			}

            $result = $myBdd->pdo->prepare($sql);
            $result->execute(array($codeCompet, $codeSaison));
            while ($row = $result->fetch()) {
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
                array_push($arrayEquipe_publi, array( 'Id' => $row['Id'], 'Numero' => $row['Numero'], 'Libelle' => $row['Libelle'], 
                    'Code_club' => $row['Code_club'], 'Code_comite_dep' => $row['Code_comite_dep'],
                    'Clt_publi' => $row['Clt_publi'], 'Pts_publi' => $row['Pts_publi'], 
                    'J_publi' => $row['J_publi'], 'G_publi' => $row['G_publi'], 'N_publi' => $row['N_publi'], 
                    'P_publi' => $row['P_publi'], 'F_publi' => $row['F_publi'], 'Plus_publi' => $row['Plus_publi'], 
                    'Moins_publi' => $row['Moins_publi'], 'Diff_publi' => $row['Diff_publi'],
                    'PtsNiveau_publi' => $row['PtsNiveau_publi'], 'CltNiveau_publi' => $row['CltNiveau_publi'], 
                    'logo' => $logo, 'club' => $club ));
				if (($typeClt == 'CHPT' && $row['Clt_publi'] == 0) || ($typeClt == 'CP' && $row['CltNiveau_publi'] == 0)) {
					$recordCompetition['Qualifies']	= 0;
					$recordCompetition['Elimines'] = 0;
				}
			}
            $this->m_tpl->assign('arrayEquipe_publi', $arrayEquipe_publi);
		}	
		$this->m_tpl->assign('arrayEquipe_journee_publi', $arrayEquipe_journee_publi);
        $this->m_tpl->assign('arrayJournee', $arrayJournee);
        $this->m_tpl->assign('arrayMatchs', $arrayMatchs);
        $this->m_tpl->assign('recordCompetition', $recordCompetition);
		$this->m_tpl->assign('Qualifies', $recordCompetition['Qualifies']);
		$this->m_tpl->assign('Elimines', $recordCompetition['Elimines']);
		$this->m_tpl->assign('page', 'Classement');

		// Combo "CHPT" - "CP"		
		$arrayOrderCompetition = array();
		if ('CHPT' == $typeClt) {
			array_push($arrayOrderCompetition, array('CHPT', 'Championnat', 'SELECTED') );
		} else {
			array_push($arrayOrderCompetition, array('CHPT', 'Championnat', '') );
		}

		if ('CP' == $typeClt) {
			array_push($arrayOrderCompetition, array('CP', 'Coupe', 'SELECTED') );
		} else {
			array_push($arrayOrderCompetition, array('CP', 'Coupe', '') );
		}
		$this->m_tpl->assign('arrayOrderCompetition', $arrayOrderCompetition);
	}
	
	
	// Classement 		
	function __construct()
	{			
	  	MyPage::MyPage();
		
		$this->SetTemplate("Classement", "Classements", true);
		$this->Load();
        
		// COSANDCO : Gestion Param Voie ...
		if (isset($_GET['voie'])) {
			$voie = (int) $_GET['voie'];
			if ($voie > 0) {
                $this->m_tpl->assign('voie', $voie);
            }
            
			$intervalle = (int) utyGetGet('intervalle', 0);
			if ($intervalle > 0) {
                $this->m_tpl->assign('intervalle', $intervalle);
			}
		}        

		$this->DisplayTemplateFrame('frame_classement');
	}
}		  	

$page = new Classement();
