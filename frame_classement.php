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
			
		$codeSaison = utyGetGet('Saison', utyGetSaison());
		$this->m_tpl->assign('Saison', $codeSaison);
	
        $recordCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);
		$this->m_tpl->assign('Code_ref', $recordCompetition['Code_ref']);
        
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
			$sql  = "SELECT ce.*, c.Code_comite_dep "
                    . "FROM gickp_Competitions_Equipes ce, gickp_Club c "
                    . "WHERE ce.Code_compet = '$codeCompet' "
                    . "AND ce.Code_saison = $codeSaison "
                    . "AND ce.Code_club = c.Code ";
                    if ($typeClt == 'CP')
                        $sql .= "ORDER BY CltNiveau_publi Asc, Diff_publi Desc ";	 
                    else
                        $sql .= "ORDER BY Clt_publi Asc, Diff_publi Desc ";	 
	
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
		}	
		$this->m_tpl->assign('arrayEquipe_journee_publi', $arrayEquipe_journee_publi);
        $this->m_tpl->assign('arrayJournee', $arrayJournee);
        $this->m_tpl->assign('arrayMatchs', $arrayMatchs);
        $this->m_tpl->assign('recordCompetition', $recordCompetition);
		$this->m_tpl->assign('Qualifies', $recordCompetition['Qualifies']);
		$this->m_tpl->assign('Elimines', $recordCompetition['Elimines']);

		// Combo "CHPT" - "CP"		
		$arrayOrderCompetition = array();
		if ('CHPT' == $typeClt)
			array_push($arrayOrderCompetition, array('CHPT', 'Championnat', 'SELECTED') );
		else
			array_push($arrayOrderCompetition, array('CHPT', 'Championnat', '') );
			
		if ('CP' == $typeClt)
			array_push($arrayOrderCompetition, array('CP', 'Coupe', 'SELECTED') );
		else
			array_push($arrayOrderCompetition, array('CP', 'Coupe', '') );
		$this->m_tpl->assign('arrayOrderCompetition', $arrayOrderCompetition);
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
	
	
	// ExistCompetitionEquipeNiveau
	function ExistCompetitionEquipeNiveau($idEquipe, $niveau)
	{
		$myBdd = new MyBdd();
	
		$sql  = "Select Count(*) Nb From gickp_Competitions_Equipes_Niveau Where Id = $idEquipe And Niveau = $niveau ";
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Select 3");
		if (mysql_num_rows($result) == 1)
		{
			$row = mysql_fetch_array($result);	 
			if ($row['Nb'] == 1)
				return; // Le record existe ...
		}

		$sql  = "Insert Into gickp_Competitions_Equipes_Niveau (Id, Niveau, Pts, Clt, J, G, N, P, F, Plus, Moins, Diff, PtsNiveau, CltNiveau) ";
		$sql .= "Values ($idEquipe, $niveau, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0) ";
		mysql_query($sql, $myBdd->m_link) or die ("Erreur Insert 1");
	}

	// ExistCompetitionEquipeJournee
	function ExistCompetitionEquipeJournee($idEquipe, $idJournee)
	{
		$myBdd = new MyBdd();
		
		$sql  = "Select count(*) Nb From gickp_Competitions_Equipes_Journee Where Id = $idEquipe And Id_journee = $idJournee";
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Select 4");
		if (mysql_num_rows($result) == 1)
		{
			$row = mysql_fetch_array($result);	 
			if ($row['Nb'] == 1)
				return; // Le record existe ...
		}

		$sql  = "Insert Into gickp_Competitions_Equipes_Journee (Id, Id_journee, Pts, Clt, J, G, N, P, F, Plus, Moins, Diff, PtsNiveau, CltNiveau) ";
		$sql .= "Values ($idEquipe, $idJournee, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0) ";
		
		mysql_query($sql, $myBdd->m_link) or die ("Erreur Insert 2");
	}
	

	// GestionClassement 		
	function Classement()
	{			
	  MyPage::MyPage();
		
		$this->SetTemplate("Classement", "Classements", true);
		$this->Load();
//		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplateFrame('frame_classement');
	}
}		  	

$page = new Classement();