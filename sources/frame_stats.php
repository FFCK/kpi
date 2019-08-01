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

        $sql  = "SELECT d.Code_competition Competition, a.Matric Licence, a.Nom, a.Prenom, a.Sexe, b.Numero, f.Libelle Equipe, COUNT(*) Buts "
            . "FROM gickp_Liste_Coureur a, gickp_Matchs_Detail b, gickp_Matchs c, gickp_Journees d, gickp_Competitions_Equipes f "
            . "WHERE a.Matric = b.Competiteur "
            . "AND b.Id_match = c.Id "
            . "AND c.Id_journee = d.Id "
            . "AND d.Code_competition = f.Code_compet "
            . "AND d.Code_saison = f.Code_saison "
            . "AND f.Id = IF(b.Equipe_A_B='A',c.Id_equipeA, c.Id_equipeB) "
            . "AND d.Code_competition = '$codeCompet' "
            . "AND d.Code_saison = $codeSaison "
            . "AND b.Id_evt_match = 'B' "
            . "GROUP BY a.Matric "
            . "ORDER BY Buts DESC, a.Nom "
            . "LIMIT 0,$nbLignes ";
        $arrayButeurs = array();
        $result = $myBdd->Query($sql);
        while ($row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC)){ 
            array_push($arrayButeurs, array( 'Competition' => $row['Competition'], 
                        'Licence' => $row['Licence'],  
                        'Nom' => $row['Nom'],  
                        'Prenom' => $row['Prenom'],  
                        'Sexe' => $row['Sexe'],  
                        'Numero' => $row['Numero'],  
                        'Equipe' => $row['Equipe'],  
                        'Buts' => $row['Buts']));
        }
        $this->m_tpl->assign('arrayButeurs', $arrayButeurs);
		$this->m_tpl->assign('page', 'stats');
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
	function Stats()
	{			
	  MyPage::MyPage();
		
		$this->SetTemplate("Stats", "Classements", true);
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

		$this->DisplayTemplateFrame('frame_stats');
	}
}		  	

$page = new Stats();
