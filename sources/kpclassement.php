<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

// Classements

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

		$codeSaison = $myBdd->GetActiveSaison();
		$codeSaison = utyGetPost('saisonTravail', $codeSaison);
		$codeSaison = utyGetGet('Saison', $codeSaison);
		$_SESSION['Saison'] = $codeSaison;
		$this->m_tpl->assign('Saison', $codeSaison);

		$idSelJournee = utyGetGet('J', 0);
		$this->m_tpl->assign('idSelJournee', $idSelJournee);

		$event = utyGetGet('event', '0');
		$this->m_tpl->assign('event', $event);
		if ($event > 0) {
			$eventTitle = $myBdd->GetEvenementLibelle($event);
			$this->m_tpl->assign('eventTitle', $eventTitle);
		}

		$arrayNavGroup = $myBdd->GetOtherCompetitions($codeCompet, $codeSaison, true, $event);
		$this->m_tpl->assign('arrayNavGroup', $arrayNavGroup);
		$this->m_tpl->assign('navGroup', 1);

		$group = utyGetGet('Group', $arrayNavGroup[0]['Code_ref']);
		$this->m_tpl->assign('group', $group);

		if ($event > 0 && $codeCompet == '*') {
			$codeCompet = $arrayNavGroup[0]['Code'];
			$_SESSION['codeCompet'] = $codeCompet;
			$this->m_tpl->assign('codeCompet', $codeCompet);
		}

		$Round = utyGetGet('Round', '*');
		$this->m_tpl->assign('Round', $Round);
		$Round = str_replace('*', '%', $Round);

		$this->m_tpl->assign('Css', utyGetGet('Css', ''));

		$recordCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);
		$this->m_tpl->assign('Code_ref', $recordCompetition['Code_ref']);

		//Logos
		if ($codeCompet != -1) {
			$this->m_tpl->assign('visuels', utyGetVisuels($recordCompetition));
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
			$sql = "SELECT ce.*, c.Code_comite_dep 
				FROM kp_competition_equipe ce, kp_club c 
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
				if (is_file('img/KIP/logo/' . $club . '-logo.png')) {
					$logo = 'img/KIP/logo/' . $club . '-logo.png';
				} elseif (is_file('img/Nations/' . substr($club, 0, 3) . '.png')) {
					$club = substr($club, 0, 3);
					$logo = 'img/Nations/' . $club . '.png';
				}
				if (strlen($row['Code_comite_dep']) > 3) {
					$row['Code_comite_dep'] = 'FRA';
				}
				array_push($arrayEquipe_publi, array(
					'Id' => $row['Id'], 'Numero' => $row['Numero'], 'Libelle' => $row['Libelle'],
					'Code_club' => $row['Code_club'], 'Code_comite_dep' => $row['Code_comite_dep'],
					'Clt' => $row['Clt_publi'], 'Pts' => $row['Pts_publi'],
					'J' => $row['J_publi'], 'G' => $row['G_publi'], 'N' => $row['N_publi'],
					'P' => $row['P_publi'], 'F' => $row['F_publi'], 'Plus' => $row['Plus_publi'],
					'Moins' => $row['Moins_publi'], 'Diff' => $row['Diff_publi'],
					'PtsNiveau' => $row['PtsNiveau_publi'], 'CltNiveau' => $row['CltNiveau_publi'],
					'logo' => $logo, 'club' => $club
				));
				if (($typeClt == 'CHPT' && $row['Clt_publi'] == 0) || ($typeClt == 'CP' && $row['CltNiveau_publi'] == 0)) {
					$recordCompetition['Qualifies']	= 0;
					$recordCompetition['Elimines'] = 0;
				}
			}
			$this->m_tpl->assign('arrayEquipe_publi', $arrayEquipe_publi);
		}

		$this->m_tpl->assign('recordCompetition', $recordCompetition);
		$this->m_tpl->assign('Qualifies', $recordCompetition['Qualifies']);
		$this->m_tpl->assign('Elimines', $recordCompetition['Elimines']);
		$this->m_tpl->assign('page', 'Classement');

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


	// Classement 		
	function __construct()
	{
		MyPage::MyPage();

		$this->SetTemplate("Classement", "Classements", true);
		$this->Load();
		//		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplateNew('kpclassement');
	}
}

$page = new Classement();
