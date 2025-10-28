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
			
        $codeSaison = $myBdd->GetActiveSaison();
		$codeSaison = utyGetPost('saisonTravail', $codeSaison);
		$codeSaison = utyGetGet('Saison', $codeSaison);
		$_SESSION['Saison'] = $codeSaison;
		$this->m_tpl->assign('Saison', $codeSaison);

        $event = utyGetGet('event', '0');
		$this->m_tpl->assign('event', $event);
        
        $navGroup = 0;
        $arrayNavGroup = array();

        if (utyGetGet('navGroup', false)) {
            $arrayNavGroup = $myBdd->GetOtherCompetitions($codeCompet, $codeSaison, true, $event);
            $navGroup = 1;
            $group = utyGetGet('Group', $arrayNavGroup[0]['Code_ref']);
            $this->m_tpl->assign('group', $group);
        }
        $this->m_tpl->assign('navGroup', $navGroup);

                
        $Round = utyGetGet('Round', '*');
		$this->m_tpl->assign('Round', $Round);
        
        $this->m_tpl->assign('Css', utyGetGet('Css', ''));

		$nbLignes = utyGetGet('nbLignes', 20);

        $recordCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);
		$this->m_tpl->assign('Code_ref', $recordCompetition['Code_ref']);
		$this->m_tpl->assign('recordCompetition', $recordCompetition);
        
        //Logo
		if($codeCompet != -1)
		{
            if($recordCompetition['BandeauLink'] != '' && strpos($recordCompetition['BandeauLink'], 'http') === FALSE ){
                $recordCompetition['BandeauLink'] = 'img/logo/' . $recordCompetition['BandeauLink'];
                if(is_file($recordCompetition['BandeauLink'])) {
                    $this->m_tpl->assign('bandeau', $recordCompetition['BandeauLink']);
                }
            } elseif($recordCompetition['BandeauLink'] != '') {
                $this->m_tpl->assign('bandeau', $recordCompetition['BandeauLink']);
            }
            if($recordCompetition['LogoLink'] != '' && strpos($recordCompetition['LogoLink'], 'http') === FALSE ){
                $recordCompetition['LogoLink'] = 'img/logo/' . $recordCompetition['LogoLink'];
                if(is_file($recordCompetition['LogoLink'])) {
                    $this->m_tpl->assign('logo', $recordCompetition['LogoLink']);
                }
            } elseif($recordCompetition['LogoLink'] != '') {
                $this->m_tpl->assign('logo', $recordCompetition['LogoLink']);
            }
		}

        // Stats buts
        $sql = "SELECT cej.Matric, cej.Nom, cej.Prenom, cej.Sexe, cej.Categ, cej.Numero, cej.Capitaine,
                    ce.Libelle Equipe, ce.Numero NumEquipe, ce.Id Id_equipe, 
                    SUM(IF(md.Id_evt_match = 'B', 1, 0)) buts
                FROM kp_competition_equipe ce
                LEFT OUTER JOIN kp_competition_equipe_joueur cej ON ce.Id = cej.Id_equipe
                LEFT OUTER JOIN kp_journee j ON (ce.Code_compet = j.Code_competition AND ce.Code_saison = j.Code_saison)
                LEFT OUTER JOIN kp_match m ON j.Id = m.Id_journee
                LEFT OUTER JOIN kp_match_detail md ON m.Id = md.Id_match
                WHERE md.Competiteur = cej.Matric
                AND md.Id_evt_match = 'B'
                AND ce.Code_compet = ? 
                AND ce.Code_saison = ? 
                AND m.Validation = 'O'
                AND m.Publication = 'O'
                GROUP BY cej.Matric
                ORDER BY buts DESC, cej.Nom, cej.Prenom
                LIMIT 0, $nbLignes ";
        $arrayButeurs = array();
        $result = $myBdd->pdo->prepare($sql);
        $result->execute(array($codeCompet, $codeSaison));
        while ($row = $result->fetch()) {
            array_push($arrayButeurs, array( 
                'Licence' => $row['Matric'],  
                'Nom' => $row['Nom'],  
                'Prenom' => $row['Prenom'],  
                'Sexe' => $row['Sexe'],  
                'Numero' => $row['Numero'],  
                'Equipe' => $row['Equipe'],  
                'Id_equipe' => $row['Id_equipe'],  
                'NumEquipe' => $row['NumEquipe'],  
                'Buts' => $row['buts']));
        }
        $this->m_tpl->assign('arrayButeurs', $arrayButeurs);
		$this->m_tpl->assign('page', 'Stats');
	}
	

	// Stats 		
	function __construct()
	{			
        parent::__construct();
		
		$this->SetTemplate("Stats", "Classements", true);
		$this->Load();
        
		// COSANDCO : Gestion Param Voie ...
        if (utyGetGet('voie', false)) {
            $voie = (int) utyGetGet('voie', 0);
            $intervalle = (int) utyGetGet('intervalle', 0);

            if ($voie > 0) {
                $this->m_tpl->assign('voie', $voie);
                // Toujours assigner intervalle si voie est défini, même si 0
                $this->m_tpl->assign('intervalle', $intervalle);
            }
        }

		$this->DisplayTemplateFrame('frame_stats');
	}
}		  	

$page = new Stats();
