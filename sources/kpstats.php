<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

// Stats
	
class Stats extends MyPage	 
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

        $nbLignes = utyGetGet('nbLignes', 20);

        $recordCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);
		$this->m_tpl->assign('Code_ref', $recordCompetition['Code_ref']);
		$this->m_tpl->assign('recordCompetition', $recordCompetition);
        
        //Logos
		if($codeCompet != -1) {
            $this->m_tpl->assign('visuels', utyGetVisuels($recordCompetition));
		}

        // Stats buts
        $sql = "SELECT cej.Matric, cej.Nom, cej.Prenom, cej.Sexe, cej.Categ, cej.Numero, cej.Capitaine,
                    ce.Libelle Equipe, ce.Numero NumEquipe,
                    SUM(IF(md.Id_evt_match = 'B', 1, 0)) buts
                FROM gickp_Competitions_Equipes ce
                LEFT OUTER JOIN gickp_Competitions_Equipes_Joueurs cej ON ce.Id = cej.Id_equipe
                LEFT OUTER JOIN gickp_Journees j ON (ce.Code_compet = j.Code_competition AND ce.Code_saison = j.Code_saison)
                LEFT OUTER JOIN gickp_Matchs m ON j.Id = m.Id_journee
                LEFT OUTER JOIN gickp_Matchs_Detail md ON m.Id = md.Id_match
                WHERE md.Competiteur = cej.Matric
                AND md.Id_evt_match = 'B'
                AND ce.Code_compet = '$codeCompet' 
                AND ce.Code_saison = $codeSaison 
                AND m.Validation = 'O'
                AND m.Publication = 'O'
                GROUP BY cej.Matric
                ORDER BY buts DESC, cej.Nom, cej.Prenom
                LIMIT 0, $nbLignes ";
        $arrayButeurs = array();
        $result = $myBdd->Query($sql);
        while ($row = $myBdd->FetchArray($result)){ 
            array_push($arrayButeurs, array( 'Competition' => $row['Competition'], 
                        'Licence' => $row['Matric'],  
                        'Nom' => $row['Nom'],  
                        'Prenom' => $row['Prenom'],  
                        'Sexe' => $row['Sexe'],  
                        'Numero' => $row['Numero'],  
                        'Equipe' => $row['Equipe'],  
                        'NumEquipe' => $row['NumEquipe'],  
                        'Buts' => $row['buts']));
        }
        $this->m_tpl->assign('arrayButeurs', $arrayButeurs);
        $this->m_tpl->assign('page', 'Stats');
	}
	
	function GetTypeClt($codeCompet,  $codeSaison)
	{
		if (strlen($codeCompet) == 0)
			return 'CHPT';
			
		$myBdd = new MyBdd();
		
		$recordCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);
		$typeClt = $recordCompetition['Code_typeclt'];
		if ($typeClt != 'CP')
			$typeClt = 'CHPT';
		
		return $typeClt;
	}
	
	

	// Stats 		
	function __construct()
	{			
	  MyPage::MyPage();
		
		$this->SetTemplate("Stats", "Classements", true);
		$this->Load();
//		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplateNew('kpstats');
	}
}		  	

$page = new Stats();
