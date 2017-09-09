<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion des Classements

class GestionClassement extends MyPageSecure	 
{	
	function Load()
	{
		$myBdd = new MyBdd();
		
		$codeSaison = utyGetSaison();
		$saisonActive = $myBdd->GetActiveSaison();
		
		$codeCompet = utyGetSession('codeCompet');
		$codeCompet = utyGetPost('codeCompet', $codeCompet);
		
		$codeSaisonTransfert = utyGetSession('codeSaisonTransfert',$saisonActive);
		$codeSaisonTransfert = utyGetPost('codeSaisonTransfert', $codeSaisonTransfert);
		$_SESSION['codeSaisonTransfert'] = $codeSaisonTransfert;
		$this->m_tpl->assign('codeSaisonTransfert', $codeSaisonTransfert);

		$codeCompetTransfert = utyGetSession('codeCompetTransfert',$codeCompet);
		$codeCompetTransfert = utyGetPost('codeCompetTransfert', $codeCompetTransfert);
		$_SESSION['codeCompetTransfert'] = $codeCompetTransfert;
		$this->m_tpl->assign('codeCompetTransfert', $codeCompetTransfert);

		$_SESSION['updatecell_tableName'] = 'gickp_Competitions_Equipes';
		$_SESSION['updatecell_tableName2'] = 'gickp_Competitions_Equipes_Journee';
		$_SESSION['updatecell_where'] = 'Where Id = ';
		$_SESSION['updatecell_where2'] = 'Where Id = ';
		$_SESSION['updatecell_and'] = 'And Id_journee = ';
		$_SESSION['updatecell_document'] = 'formClassement';
		
		// Chargement des Saisons ...
		$sql  = "Select Code, Etat, Nat_debut, Nat_fin, Inter_debut, Inter_fin ";
		$sql .= "From gickp_Saison ";
		$sql .= "Order By Code DESC ";	 
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load");
		$num_results = mysql_num_rows($result);
	
		$arraySaison = array();
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);	
			
			array_push($arraySaison, array('Code' => $row['Code'], 'Etat' => $row['Etat'], 
										'Nat_debut' => utyDateUsToFr($row['Nat_debut']), 'Nat_fin' => utyDateUsToFr($row['Nat_fin']), 
										'Inter_debut' => utyDateUsToFr($row['Inter_debut']), 'Inter_fin' => utyDateUsToFr($row['Inter_fin']) ));
		}
		
		$this->m_tpl->assign('arraySaison', $arraySaison);
		$this->m_tpl->assign('sessionSaison', $codeSaison);

		// Chargement des Compétitions ...
		$sql  = "Select c.Code_niveau, c.Code_ref, c.Code_tour, c.Code, c.Libelle, c.Soustitre, c.Soustitre2, "
                . "c.Titre_actif, g.section, g.ordre "
                . "From gickp_Competitions c, gickp_Competitions_Groupes g "
                . "Where c.Code_saison = '" . $codeSaison . "' "
                . utyGetFiltreCompetition('c.')
                . " And c.Code_niveau Like '".utyGetSession('AfficheNiveau')."%' ";
		if (utyGetSession('AfficheCompet') == 'NCF') {
            $sql .= " And (c.Code Like 'N%' OR c.Code Like 'CF%') ";
        } else {
            $sql .= " And c.Code Like '" . utyGetSession('AfficheCompet') . "%' ";
        }
        $sql .= " And c.Code_ref = g.Groupe "
                . "Order By c.Code_saison, g.section, g.ordre, COALESCE(c.Code_ref, 'z'), c.Code_tour, c.GroupOrder, c.Code";	 
		
		$result = $myBdd->Query($sql);
		$arrayCompetition = array();
        $i = -1;
        $j = '';
        $label = $myBdd->getSections();
		while ($row = $myBdd->FetchArray($result)) {
			// Titre
			if ($row["Titre_actif"] != 'O' && $row["Soustitre"] != '') {
                $Libelle = $row["Soustitre"];
            } else {
                $Libelle = $row["Libelle"];
            }
            if ($row["Soustitre2"] != '') {
                $Libelle .= ' - ' . $row["Soustitre2"];
            }

            if ((strlen($codeCompet) == 0) && (i == 0)) {
                $codeCompet = $row["Code"];
            }

            if($j != $row['section']) {
                $i ++;
                $arrayCompetition[$i]['label'] = $label[$row['section']];
            }
            if($row["Code"] == $codeCompet) {
                $row['selected'] = 'selected';
                $this->m_tpl->assign('Code_niveau', $row["Code_niveau"]);
            } else {
                $row['selected'] = '';
            }
            $j = $row['section'];
            $arrayCompetition[$i]['options'][] = $row;
        }
		$this->m_tpl->assign('arrayCompetition', $arrayCompetition);
		$_SESSION['codeCompet'] = $codeCompet;
		$this->m_tpl->assign('codeCompet', $codeCompet);

		// Chargement des Compétitions pour Transferts...
		$sql  = "Select Code, Libelle ";
		$sql .= "From gickp_Competitions ";
		$sql .= "Where Code_saison = '";
		$sql .= $codeSaisonTransfert;
		$sql .= "' ";
		$sql .= utyGetFiltreCompetition('');
		$sql .= " Order By Code ";	 
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load 7");
		$num_results = mysql_num_rows($result);
	
		$arrayCompetitionTransfert = array();
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);	
			
			if ( (strlen($codeCompet) == 0) && (i == 0) )
 				 $codeCompet = $row["Code"];
			
			if ($row["Code"] == $codeCompet)
				array_push($arrayCompetitionTransfert, array($row["Code"], $row["Code"]." - ".$row["Libelle"], "SELECTED" ) );
			else
				array_push($arrayCompetitionTransfert, array($row["Code"], $row["Code"]." - ".$row["Libelle"], "" ) );
		}
		$this->m_tpl->assign('arrayCompetitionTransfert', $arrayCompetitionTransfert);
		
		// Chargement des Saisons ...
		$sql  = "Select Code From gickp_Saison Order By Code";
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load 66");
		$num_results = mysql_num_rows($result);
	
		$arraySaison = array();
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);	
			array_push($arraySaison, array('Code' => $row['Code'] ));
		}
		$this->m_tpl->assign('arraySaisonTransfert', $arraySaison);
		
		// Chargement des Equipes ...
		$arrayEquipe = array();
		$arrayEquipe_journee = array();
		$arrayEquipe_journee_publi = array();
		$arrayEquipe_publi = array();

		// Par défaut type Championnat ...
		$typeClt = 'CHPT';
		
		if (strlen($codeCompet) > 0)
		{
			if (utyGetPost('ParamCmd', '') == 'changeCompetition')
			{
				$typeClt = $this->GetTypeClt($codeCompet, $codeSaison);
			}
			else
			{
				$typeClt = utyGetPost('orderCompet', '');
				if ($typeClt == '')
					$typeClt = $this->GetTypeClt($codeCompet, $codeSaison);
			}
				
			$recordCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);
			$this->m_tpl->assign('Qualifies', $recordCompetition['Qualifies']);
			$this->m_tpl->assign('Elimines', $recordCompetition['Elimines']);

			$this->m_tpl->assign('Date_calcul', $recordCompetition['Date_calcul']);
			$this->m_tpl->assign('Date_publication', $recordCompetition['Date_publication']);
			$this->m_tpl->assign('Date_publication_calcul', $recordCompetition['Date_publication_calcul']);
			$this->m_tpl->assign('Code_uti_calcul', $recordCompetition['Code_uti_calcul']);
			$this->m_tpl->assign('UserName_calcul', $myBdd->GetUserName($recordCompetition['Code_uti_calcul']));
			$this->m_tpl->assign('Code_uti_publication', $recordCompetition['Code_uti_publication']);
			$this->m_tpl->assign('UserName_publication', $myBdd->GetUserName($recordCompetition['Code_uti_publication']));
			$this->m_tpl->assign('Mode_calcul', $recordCompetition['Mode_calcul']);
			$this->m_tpl->assign('Mode_publication_calcul', $recordCompetition['Mode_publication_calcul']);
			
			
			// Classement actuel				
			$sql  = "Select ce.Id, ce.Libelle, ce.Code_club, ce.Clt, ce.Pts, ce.J, ce.G, ce.N, ce.P, ce.F, ce.Plus, ce.Moins, ce.Diff, ce.PtsNiveau, ce.CltNiveau, c.Code_comite_dep ";
			$sql .= "From gickp_Competitions_Equipes ce, gickp_Club c ";
			$sql .= "Where ce.Code_compet = '";
			$sql .= $codeCompet;
			$sql .= "' And ce.Code_saison = '";
			$sql .= $codeSaison;
			$sql .= "' And ce.Code_club = c.Code ";	 
			
			if ($typeClt == 'CP')
				$sql .= "Order By ce.CltNiveau Asc, ce.Diff Desc, ce.Libelle ";	 
			else
				$sql .= "Order By ce.Clt Asc, ce.Diff Desc, ce.Libelle ";	 
	
			$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load 5");
			$num_results = mysql_num_rows($result);
		
			for ($i=0;$i<$num_results;$i++)
			{
				$row = mysql_fetch_array($result);	  
				if (strlen($row['Code_comite_dep']) > 3)
					$row['Code_comite_dep'] = 'FRA';
				array_push($arrayEquipe, array( 'Id' => $row['Id'], 'Libelle' => $row['Libelle'], 'Code_club' => $row['Code_club'], 'Code_comite_dep' => $row['Code_comite_dep'],
																        'Clt' => $row['Clt'], 'Pts' => $row['Pts'], 
																        'J' => $row['J'], 'G' => $row['G'], 'N' => $row['N'], 
																        'P' => $row['P'], 'F' => $row['F'], 'Plus' => $row['Plus'], 
																        'Moins' => $row['Moins'], 'Diff' => $row['Diff'],
																        'PtsNiveau' => $row['PtsNiveau'], 'CltNiveau' => $row['CltNiveau'] ));
			}
			
			// Classement public				
			$sql  = "Select ce.Id, ce.Libelle, ce.Code_club, ce.Clt_publi, ce.Pts_publi, ce.J_publi, ce.G_publi, ce.N_publi, ce.P_publi, ce.F_publi, ce.Plus_publi, ce.Moins_publi, ce.Diff_publi, ce.PtsNiveau_publi, ce.CltNiveau_publi, c.Code_comite_dep ";
			$sql .= "From gickp_Competitions_Equipes ce, gickp_Club c ";
			$sql .= "Where ce.Code_compet = '";
			$sql .= $codeCompet;
			$sql .= "' And ce.Code_saison = '";
			$sql .= $codeSaison;
			$sql .= "' And ce.Code_club = c.Code ";	 
			
			if ($typeClt == 'CP')
				$sql .= "Order By CltNiveau_publi Asc, Diff_publi Desc ";	 
			else
				$sql .= "Order By Clt_publi Asc, Diff_publi Desc ";	 
	
			$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load 5b");
			$num_results = mysql_num_rows($result);
		
			for ($i=0;$i<$num_results;$i++)
			{
				$row = mysql_fetch_array($result);	  
				if (strlen($row['Code_comite_dep']) > 3)
					$row['Code_comite_dep'] = 'FRA';
				array_push($arrayEquipe_publi, array( 'Id' => $row['Id'], 'Libelle' => $row['Libelle'], 'Code_club' => $row['Code_club'], 'Code_comite_dep' => $row['Code_comite_dep'],
																        'Clt' => $row['Clt_publi'], 'Pts' => $row['Pts_publi'], 
																        'J' => $row['J_publi'], 'G' => $row['G_publi'], 'N' => $row['N_publi'], 
																        'P' => $row['P_publi'], 'F' => $row['F_publi'], 'Plus' => $row['Plus_publi'], 
																        'Moins' => $row['Moins_publi'], 'Diff' => $row['Diff_publi'],
																        'PtsNiveau' => $row['PtsNiveau_publi'], 'CltNiveau' => $row['CltNiveau_publi'] ));
				if (($typeClt == 'CHPT' && $row['Clt_publi'] == 0) || ($typeClt == 'CP' && $row['CltNiveau_publi'] == 0))
				{
					$recordCompetition['Qualifies']	= 0;
					$recordCompetition['Elimines'] = 0;
				}
			}
			
			if ($typeClt == 'CP')
			{
				// Classement actuel par journée/phase
				$sql  = "Select a.Id, a.Libelle, a.Code_club, ";
				$sql .= "b.Id_journee, b.Clt, b.Pts, b.J, b.G, b.N, b.P, b.F, b.Plus, b.Moins, b.Diff, b.PtsNiveau, b.CltNiveau, ";
				$sql .= "c.Phase, c.Niveau, c.Lieu ";
				$sql .= "From gickp_Competitions_Equipes a, ";
				$sql .= "gickp_Competitions_Equipes_Journee b Join gickp_Journees c On (b.Id_journee = c.Id) ";
				$sql .= "Where a.Id = b.Id ";
				$sql .= "And c.Code_competition = '";
				$sql .= $codeCompet;
				$sql .= "' And c.Code_saison = '";
				$sql .= $codeSaison;
				$sql .= "' Order By c.Niveau Desc, c.Phase Asc, c.Date_debut Asc, c.Lieu ASC, b.Clt Asc, b.Diff Desc, b.Plus Asc ";	 
				
				$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load 5c");
				$num_results = mysql_num_rows($result);
			
				for ($i=0;$i<$num_results;$i++)
				{
					$row = mysql_fetch_array($result);	  
							
					array_push($arrayEquipe_journee, array( 'Id' => $row['Id'], 'Libelle' => $row['Libelle'], 'Code_club' => $row['Code_club'], 'Id_journee' => $row['Id_journee'], 
																	        'Phase' => $row['Phase'], 'Niveau' => $row['Niveau'], 'Lieu' => $row['Lieu'], 'Clt' => $row['Clt'], 'Pts' => $row['Pts'], 
																	        'J' => $row['J'], 'G' => $row['G'], 'N' => $row['N'], 
																	        'P' => $row['P'], 'F' => $row['F'], 'Plus' => $row['Plus'], 
																	        'Moins' => $row['Moins'], 'Diff' => $row['Diff'],
																	        'PtsNiveau' => $row['PtsNiveau'], 'CltNiveau' => $row['CltNiveau'] ));
				}

				// Classement public par journée/phase
				$sql  = "Select a.Id, a.Libelle, a.Code_club, ";
				$sql .= "b.Id_journee, b.Clt_publi, b.Pts_publi, b.J_publi, b.G_publi, b.N_publi, b.P_publi, b.F_publi, b.Plus_publi, b.Moins_publi, b.Diff_publi, b.PtsNiveau_publi, b.CltNiveau_publi, ";
				$sql .= "c.Phase, c.Niveau, c.Lieu ";
				$sql .= "From gickp_Competitions_Equipes a, ";
				$sql .= "gickp_Competitions_Equipes_Journee b Join gickp_Journees c On (b.Id_journee = c.Id) ";
				$sql .= "Where a.Id = b.Id ";
				$sql .= "And c.Code_competition = '";
				$sql .= $codeCompet;
				$sql .= "' And c.Code_saison = '";
				$sql .= $codeSaison;
				$sql .= "' Order By c.Niveau Desc, c.Phase Asc, c.Date_debut Asc, c.Lieu ASC, b.Clt_publi Asc, b.Diff_publi Desc, b.Plus_publi Asc ";	 
				
				$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load 5d");
				$num_results = mysql_num_rows($result);
			
				for ($i=0;$i<$num_results;$i++)
				{
					$row = mysql_fetch_array($result);	  
							
					array_push($arrayEquipe_journee_publi, array( 'Id' => $row['Id'], 'Libelle' => $row['Libelle'], 'Code_club' => $row['Code_club'], 'Id_journee' => $row['Id_journee'], 
																	        'Phase' => $row['Phase'], 'Niveau' => $row['Niveau'], 'Lieu' => $row['Lieu'], 'Clt' => $row['Clt_publi'], 'Pts' => $row['Pts_publi'], 
																	        'J' => $row['J_publi'], 'G' => $row['G_publi'], 'N' => $row['N_publi'], 
																	        'P' => $row['P_publi'], 'F' => $row['F_publi'], 'Plus' => $row['Plus_publi'], 
																	        'Moins' => $row['Moins_publi'], 'Diff' => $row['Diff_publi'],
																	        'PtsNiveau' => $row['PtsNiveau_publi'], 'CltNiveau' => $row['CltNiveau_publi'] ));
				}

			}
		}	
		$this->m_tpl->assign('arrayEquipe', $arrayEquipe);
		$this->m_tpl->assign('arrayEquipe_journee', $arrayEquipe_journee);
		$this->m_tpl->assign('arrayEquipe_journee_publi', $arrayEquipe_journee_publi);
		$this->m_tpl->assign('arrayEquipe_publi', $arrayEquipe_publi);
		if(!isset($recordCompetition['Qualifies']))
			$recordCompetition['Qualifies'] = 0;
		if(!isset($recordCompetition['Elimines']))
			$recordCompetition['Elimines'] = 0;
		$this->m_tpl->assign('Qualifies_publi', $recordCompetition['Qualifies']);
		$this->m_tpl->assign('Elimines_publi', $recordCompetition['Elimines']);
		
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
	
	function DoClassement()
	{
		$myBdd = new MyBdd();

		$codeSaison = utyGetSaison();
		$codeCompet = utyGetPost('codeCompet');
		$_SESSION['codeCompet'] = $codeCompet;
		$allMatchs = utyGetPost('allMatchs');
		if($allMatchs == 'ok')
			$tousLesMatchs = 'tous';
		else
			$tousLesMatchs = '';
	
		// Recherche du type de Classement lié à cette compétition
		$typeClt = '';
		
		$sql  = "Select Code_typeclt From gickp_Competitions ";
		$sql .= "Where Code = '";
		$sql .= $codeCompet;
		$sql .= "' And Code_saison = '";
		$sql .= $codeSaison;
		$sql .= "'";	 
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Select 2");
		
		if (mysql_num_rows($result) == 1)
		{
			$row = mysql_fetch_array($result);	  
			$typeClt = $row['Code_typeclt'];
		}
		
		$this->RazClassementCompetitionEquipe($codeCompet, $codeSaison);
		$this->InitClassementCompetitionEquipe($codeCompet, $codeSaison);
		
		$this->RazClassementCompetitionEquipeNiveau($codeCompet, $codeSaison);
		$this->RazClassementCompetitionEquipeJournee($codeCompet, $codeSaison);
		
		$this->CalculClassement($codeCompet,$typeClt, $tousLesMatchs);
		
		$egalites = $this->FinalisationClassementChpt($codeCompet, $codeSaison);
		$this->FinalisationClassementNiveau($codeCompet, $codeSaison);
		
		$this->FinalisationClassementNiveauChpt($codeCompet, $codeSaison);
		$this->FinalisationClassementNiveauNiveau($codeCompet, $codeSaison);
		
		$this->FinalisationClassementJourneeChpt($codeCompet, $codeSaison);
		$this->FinalisationClassementJourneeNiveau($codeCompet, $codeSaison);
	
		$sql  = "Update gickp_Competitions Set Date_calcul = '";
		$sql .= date('Y-m-d H:i:s');
		$sql .= "', Code_uti_calcul = '";
		$sql .= utyGetSession('User');
		$sql .= "', Mode_calcul = '";
		$sql .= $tousLesMatchs;
		$sql .= "' Where Code = '";
		$sql .= $codeCompet;
		$sql .= "' And Code_saison = '";
		$sql .= $codeSaison;
		$sql .= "'";	 

		mysql_query($sql, $myBdd->m_link) or die ("Erreur Update");

		($tousLesMatchs == 'tous') ? $lesMatchs = 'Inclu matchs non verrouillés' : $lesMatchs = 'Uniquement matchs verrouillés';
		$myBdd->utyJournal('Calcul Classement', $codeSaison, $codeCompet, 'NULL', 'NULL', 'NULL', $lesMatchs);
        
        if($egalites > 1 && $typeClt == 'CHPT') {
            return "Attention : $egalites équipes ou plus sont à égalité. Vérifiez si nécessaire la différence de but particulière !";
        } else {
            return '';
        }
	}

	function InitClassement()
	{
		$codeCompet = utyGetPost('codeCompet');
		$_SESSION['codeCompet'] = $codeCompet;
		
		header('Location: GestionClassementInit.php');	
		exit;	
	}

	function RazClassementCompetitionEquipe($codeCompet, $codeSaison)
	{
			$myBdd = new MyBdd();
			
			$sql  = "Update gickp_Competitions_Equipes Set Clt=1, Pts=0, J=0, G=0, N=0, P=0, F=0, Plus=0, Moins=0, Diff=0,";
			$sql .= "CltNiveau = 1, PtsNiveau = 0 ";
			$sql .= "Where Code_compet = '";
			$sql .= $codeCompet;
			$sql .= "' And Code_saison = '";
			$sql .= $codeSaison;
			$sql .= "'";	 
			
			mysql_query($sql, $myBdd->m_link) or die ("Erreur RAZ");
	}
		
	function InitClassementCompetitionEquipe($codeCompet, $codeSaison)
	{
			$myBdd = new MyBdd();
			
			$sql  = "Update gickp_Competitions_Equipes a, gickp_Competitions_Equipes_Init b ";
			$sql .= "Set a.Clt=b.Clt, a.Pts=b.Pts*100, a.J=b.J, a.G=b.G, a.N=b.N, a.P=b.P, a.F=b.F, ";
			$sql .= "a.Plus=b.plus, a.Moins=b.Moins, a.Diff=b.Diff ";
			$sql .= "Where a.Id = b.Id ";
			$sql .= "And a.Code_compet = '";
			$sql .= $codeCompet;
			$sql .= "' And a.Code_saison = '";
			$sql .= $codeSaison;
			$sql .= "'";	 
			
			mysql_query($sql, $myBdd->m_link) or die ("Erreur Update");
	}
	
	function RazClassementCompetitionEquipeNiveau($codeCompet, $codeSaison)
	{
			$myBdd = new MyBdd();
			
			$sql  = "Update gickp_Competitions_Equipes_Niveau a, gickp_Competitions_Equipes b ";
			$sql .= "Set a.Clt=1, a.Pts=0, a.J=0, a.G=0, a.N=0, a.P=0, a.F=0, ";
			$sql .= "a.Plus=0, a.Moins=0, a.Diff=0, a.PtsNiveau=0, a.CltNiveau=0 ";
			$sql .= "Where a.Id = b.Id ";
			$sql .= "And b.Code_compet = '$codeCompet' ";
			$sql .= "And b.Code_saison = '$codeSaison' ";

			mysql_query($sql, $myBdd->m_link) or die ("Erreur Update RAZ Niveau : ".$sql);
	}
	
	function RazClassementCompetitionEquipeJournee($codeCompet, $codeSaison)
	{
			$myBdd = new MyBdd();
			
			$sql  = "Update gickp_Competitions_Equipes_Journee a RIGHT OUTER JOIN gickp_Journees b ON a.Id_journee = b.Id ";
			$sql .= "Set a.Clt=0, a.Pts=0, a.J=0, a.G=0, a.N=0, a.P=0, a.F=0, ";
			$sql .= "a.Plus=0, a.Moins=0, a.Diff=0, a.PtsNiveau=0, a.CltNiveau=0 ";
			//$sql .= "Where a.Id = b.Id ";
			$sql .= "Where b.Code_competition = '$codeCompet' ";
			$sql .= "And b.Code_saison = '$codeSaison' ";

			mysql_query($sql, $myBdd->m_link) or die ("Erreur Update RAZ Journee : ".$sql);
	}

	
	function CalculClassement($codeCompet, $typeClt, $tousLesMatchs=false)
	{
			$this->CalculClassementJournee($codeCompet, $typeClt, $tousLesMatchs);
	}

	function CalculClassementJournee($codeCompet, $typeClt, $tousLesMatchs=false)
	{
			$myBdd = new MyBdd();
			$codeSaison = utyGetSaison();
				
			$sql  = "Select a.Id_equipeA, a.ScoreA, a.Id_equipeB, a.ScoreB, a.CoeffA, a.CoeffB, a.Id, a.Id_journee, b.Niveau, c.Points ";
			$sql .= "From gickp_Matchs a, gickp_Journees b, gickp_Competitions c ";
			$sql .= "Where a.Id_journee = b.Id ";
			$sql .= "And b.Code_competition = '";
			$sql .= $codeCompet;
			$sql .= "' And b.Code_competition = c.Code ";
			$sql .= "And b.Code_saison = '";
			$sql .= $codeSaison;
			$sql .= "' And b.Code_saison = c.Code_saison ";
			if(!$tousLesMatchs) //uniquement les matchs validés (vérouillés)
				$sql .= "And a.Validation = 'O' ";
			$sql .= "Order By b.Id ";	 
			
			$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load 1");
			$num_results = mysql_num_rows($result);
		
			for ($i=0;$i<$num_results;$i++)
			{
				$row = mysql_fetch_array($result);
				$scoreA = $row['ScoreA'];
				$scoreB = $row['ScoreB'];
				
				$coeffA = (double) $row['CoeffA'];
				$coeffB = (double) $row['CoeffB'];
				if ($coeffA == 0) {
                    $coeffA = 1.0;
                }
                if ($coeffB == 0) {
                    $coeffB = 1.0;
                }
//              $coeffA = $coeffA;
//				$coeffB = $coeffB;
				
				$idEquipeA = $row['Id_equipeA'];
				$idEquipeB = $row['Id_equipeB'];
				
				if (!utyIsScoreOk($scoreA, $scoreB))
				{
					// Score Non Valide ...
					if (!utyIsTypeCltCoupe($typeClt)) {
                        continue;
                    }

                    if (!utyIsEquipeOk($idEquipeA, $idEquipeB)) {
                        continue;
                    }

                    // Score non valide mais pris en compte pour les niveaux Coupe ...
					$scoreA = '';
					$scoreB = '';
				}
				
				// Initialisation des tableaux $arrayCltA et $arrayCltB ...
				$arrayCltA = array ('Pts' => 0, 'J' => 0, 'G' => 0, 'N' => 0, 'P' => 0, 'F' => 0, 'Plus' => 0, 'Moins' => 0, 'Diff' => 0, 'PtsNiveau' => 0);
				$arrayCltB = array ('Pts' => 0, 'J' => 0, 'G' => 0, 'N' => 0, 'P' => 0, 'F' => 0, 'Plus' => 0, 'Moins' => 0, 'Diff' => 0, 'PtsNiveau' => 0);
				
				$niveau = $row['Niveau'];
				if (strlen($niveau) == 0) {
                    $niveau = 0;
                }

                $idJournee = $row['Id_journee'];
				$Points = $row['Points'];
				
				$this->SetArrayClt($scoreA, $scoreB, $niveau, $arrayCltA, $arrayCltB, $coeffA, $coeffB, $Points);
				
				// Incrementation gickp_Competitions_Equipes ...
				$this->StepClassementCompetitionEquipe($idEquipeA, $arrayCltA, $idEquipeB, $arrayCltB);

				// Incrementation gickp_Competitions_Equipes_Niveau ...
				$this->StepClassementCompetitionEquipeNiveau($idEquipeA, $arrayCltA, $idEquipeB, $arrayCltB, $niveau);
				
				// Incrementation gickp_Competitions_Equipes_Journee ...
				$this->StepClassementCompetitionEquipeJournee($idEquipeA, $arrayCltA, $idEquipeB, $arrayCltB, $idJournee);
			}			
	}
	
	
	function SetArrayClt($scoreA, $scoreB, $niveau, &$arrayCltA, &$arrayCltB, $coeffA, $coeffB, $Points)
	{
		$niveau = (int) $niveau;
		// Points par victoire, null, défaite, forfait
		
		$ptsV = $Points[0] * 100; // =400 ou 300
		$ptsN = $Points[2] * 100; // =200 ou 100
		$ptsP = $Points[4] * 100; // =100 ou 0
		$ptsF = $Points[6] * 100; // =0
		//die($ptsV.$ptsN.$ptsP.$ptsF);
		// SetArrayClt : On a 4 Etat : Victoire = 4, Equalite = 3, Defaite = 2 et Non Joue = 1 , 16 matchs max pour une equipe par niveau => Base 16x4=64
		$ptsNiveauV = 4;
		$ptsNiveauN = 3;
		$ptsNiveauP = 2;
		$ptsNiveauF = 1;
		$ptsNiveauNonjoue = 0;
		
		if ( ($scoreA != 'F') && ($scoreB != 'F') )
		{
				// Score OK ...
				$bScoreOk = true;
				if ($scoreA == '' || $scoreB == '' || $scoreA == '?' || $scoreB == '?') {
                    //$bScoreOk = false;
                    return;
                }

                if ($bScoreOk) {
					$arrayCltA['J'] = 1;
					$arrayCltB['J'] = 1;
				}
				
				$scoreA = (int) $scoreA;
				$scoreB = (int) $scoreB;

				$arrayCltA['Plus'] = $scoreA;
				$arrayCltA['Moins'] = $scoreB;
				$arrayCltA['Diff'] = $scoreA - $scoreB;
		
				$arrayCltB['Plus'] = $scoreB;
				$arrayCltB['Moins'] = $scoreA;
				$arrayCltB['Diff'] = $scoreB - $scoreA;
								
				if ($scoreA > $scoreB)
				{
					// Victoire Equipe A et Défaite Equipe B ...
					$arrayCltA['Pts'] = (int) ($ptsV * $coeffA);
					$arrayCltA['G'] = 1;
					$arrayCltA['PtsNiveau'] = (double) pow(64, $niveau) * $ptsNiveauV;

					$arrayCltB['Pts'] = (int) ($ptsP * $coeffB);
					$arrayCltB['P'] = 1;
					$arrayCltB['PtsNiveau'] = (double) pow(64, $niveau)*$ptsNiveauP;
					return;
				}
			  
				if ($scoreB > $scoreA)
				{
					// Victoire Equipe B et Défaite Equipe A ...
					$arrayCltA['Pts'] = (int) ($ptsP * $coeffA);
					$arrayCltA['P'] = 1;
					$arrayCltA['PtsNiveau'] = (double) pow(64, $niveau)*$ptsNiveauP;

					$arrayCltB['Pts'] = (int) ($ptsV * $coeffB);
					$arrayCltB['G'] = 1;
					$arrayCltB['PtsNiveau'] = (double) pow(64, $niveau)*$ptsNiveauV;
					return;
				}
			  
			  // Match Null 
				$arrayCltA['Pts'] = (int) ($ptsN * $coeffA); //
				if ($bScoreOk)
				{
					$arrayCltA['N'] = 1;
					$arrayCltA['PtsNiveau'] = (double) pow(64, $niveau)*$ptsNiveauN;
				}
				else
				{
					$arrayCltA['PtsNiveau'] = (double) pow(64, $niveau)*$ptsNiveauNonjoue;
			  }
				
				$arrayCltB['Pts'] = (int) ($ptsN * $coeffB);
				if ($bScoreOk)
				{
					$arrayCltB['PtsNiveau'] = (double) pow(64, $niveau)*$ptsNiveauN;
					$arrayCltB['N'] = 1;
			  }
				else
					$arrayCltB['PtsNiveau'] = (double) pow(64, $niveau)*$ptsNiveauNonjoue;
				return;
		}
		 
		// Au moins une Equipe Forfait ...
		if ( ($scoreA != 'F') && ($scoreB == 'F') )
		{
				// Victoire Equipe A 
				$arrayCltA['Pts'] = (int) ($ptsV * $coeffA);
				$arrayCltA['Plus'] = $scoreA;
				$arrayCltA['Diff'] = $scoreA;
				$arrayCltA['J'] = 1;
				$arrayCltA['G'] = 1;
				$arrayCltA['PtsNiveau'] = (double) pow(64, $niveau)*$ptsNiveauV;

				$arrayCltB['J'] = 1;
				$arrayCltB['F'] = 1;
				$arrayCltB['Moins'] = $scoreA;
				$arrayCltB['Diff'] = 0 - $scoreA;
				$arrayCltB['PtsNiveau'] = (double) pow(64, $niveau)*$ptsNiveauF;
				return;
		}
		 
		if ( ($scoreA == 'F') && ($scoreB != 'F') )
		{
				// Victoire Equipe B 
				$arrayCltA['J'] = 1;
				$arrayCltA['F'] = 1;
				$arrayCltA['Moins'] = $scoreB;
				$arrayCltA['Diff'] = 0 - $scoreB;
				$arrayCltA['PtsNiveau'] = (double) pow(64, $niveau)*$ptsNiveauF;
				
				$arrayCltB['Pts'] = (int) ($ptsV * $coeffB);
				$arrayCltB['Plus'] = $scoreB;
				$arrayCltB['Diff'] = $scoreB;
				$arrayCltB['J'] = 1;
				$arrayCltB['G'] = 1;
				$arrayCltB['PtsNiveau'] = (double) pow(64, $niveau)*$ptsNiveauV;
				return;
		}

		if ( ($scoreA == 'F') && ($scoreB == 'F') )
		{
				// Double forfait
				$arrayCltA['J'] = 1;
				$arrayCltA['F'] = 1;
				$arrayCltA['PtsNiveau'] = (double) pow(64, $niveau)*$ptsNiveauF;
				
				$arrayCltB['J'] = 1;
				$arrayCltB['F'] = 1;
				$arrayCltB['PtsNiveau'] = (double) pow(64, $niveau)*$ptsNiveauF;
				return;
		}

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
	
	// StepClassementCompetitionEquipe
	function StepClassementCompetitionEquipe($idEquipeA, $arrayCltA, $idEquipeB, $arrayCltB)
	{
		$myBdd = new MyBdd();

		// Equipe A ...
		$sql  = "Update gickp_Competitions_Equipes Set Pts=Pts+";
		$sql .= $arrayCltA['Pts'];
		$sql .= ", J=J+";
		$sql .= $arrayCltA['J'];
		$sql .= ", G=G+";
		$sql .= $arrayCltA['G'];
		$sql .= ", N=N+";
		$sql .= $arrayCltA['N'];
		$sql .= ", P=P+";
		$sql .= $arrayCltA['P'];
		$sql .= ", F=F+";
		$sql .= $arrayCltA['F'];
		$sql .= ", Plus=Plus+";
		$sql .= $arrayCltA['Plus'];
		$sql .= ", Moins=Moins+";
		$sql .= $arrayCltA['Moins'];;
		$sql .= ", Diff=Diff+";
		$sql .= $arrayCltA['Diff'];
		$sql .= ", PtsNiveau = PtsNiveau+";
		$sql .= $arrayCltA['PtsNiveau'];
		$sql .= " Where Id = $idEquipeA ";
		
		mysql_query($sql, $myBdd->m_link) or die ("Erreur Update A : ".$sql);
		
		// Equipe B ...
		$sql  = "Update gickp_Competitions_Equipes Set Pts=Pts+";
		$sql .= $arrayCltB['Pts'];
		$sql .= ", J=J+";
		$sql .= $arrayCltB['J'];
		$sql .= ", G=G+";
		$sql .= $arrayCltB['G'];
		$sql .= ", N=N+";
		$sql .= $arrayCltB['N'];
		$sql .= ", P=P+";
		$sql .= $arrayCltB['P'];
		$sql .= ", F=F+";
		$sql .= $arrayCltB['F'];
		$sql .= ", plus=plus+";
		$sql .= $arrayCltB['Plus'];
		$sql .= ", moins=moins+";
		$sql .= $arrayCltB['Moins'];;
		$sql .= ", Diff=Diff+";
		$sql .= $arrayCltB['Diff'];
		$sql .= ", PtsNiveau = PtsNiveau+";
		$sql .= $arrayCltB['PtsNiveau'];
		$sql .= " Where Id = $idEquipeB ";
		
		mysql_query($sql, $myBdd->m_link) or die ("Erreur Update B");
	}
	
	// StepClassementCompetitionEquipeNiveau
	function StepClassementCompetitionEquipeNiveau($idEquipeA, $arrayCltA, $idEquipeB, $arrayCltB, $niveau)
	{
		$myBdd = new MyBdd();
		
		$this->ExistCompetitionEquipeNiveau($idEquipeA, $niveau);
		$this->ExistCompetitionEquipeNiveau($idEquipeB, $niveau);
				
		// Equipe A ...
		$sql  = "Update gickp_Competitions_Equipes_Niveau Set Pts=Pts+";
		$sql .= $arrayCltA['Pts'];
		$sql .= ", J=J+";
		$sql .= $arrayCltA['J'];
		$sql .= ", G=G+";
		$sql .= $arrayCltA['G'];
		$sql .= ", N=N+";
		$sql .= $arrayCltA['N'];
		$sql .= ", P=P+";
		$sql .= $arrayCltA['P'];
		$sql .= ", F=F+";
		$sql .= $arrayCltA['F'];
		$sql .= ", Plus=Plus+";
		$sql .= $arrayCltA['Plus'];
		$sql .= ", Moins=Moins+";
		$sql .= $arrayCltA['Moins'];;
		$sql .= ", Diff=Diff+";
		$sql .= $arrayCltA['Diff'];
		$sql .= ", PtsNiveau = PtsNiveau+";
		$sql .= $arrayCltA['PtsNiveau'];
		$sql .= " Where Id = $idEquipeA ";
		$sql .= " And Niveau = $niveau";
		
		mysql_query($sql, $myBdd->m_link) or die ("Erreur Update A");
		
		// Equipe B ...
		$sql  = "Update gickp_Competitions_Equipes_Niveau Set Pts=Pts+";
		$sql .= $arrayCltB['Pts'];
		$sql .= ", J=J+";
		$sql .= $arrayCltB['J'];
		$sql .= ", G=G+";
		$sql .= $arrayCltB['G'];
		$sql .= ", N=N+";
		$sql .= $arrayCltB['N'];
		$sql .= ", P=P+";
		$sql .= $arrayCltB['P'];
		$sql .= ", F=F+";
		$sql .= $arrayCltB['F'];
		$sql .= ", plus=plus+";
		$sql .= $arrayCltB['Plus'];
		$sql .= ", moins=moins+";
		$sql .= $arrayCltB['Moins'];;
		$sql .= ", Diff=Diff+";
		$sql .= $arrayCltB['Diff'];
		$sql .= ", PtsNiveau = PtsNiveau+";
		$sql .= $arrayCltB['PtsNiveau'];
		$sql .= " Where Id = $idEquipeB ";
		$sql .= " And Niveau = $niveau";
		
		mysql_query($sql, $myBdd->m_link) or die ("Erreur Update B");
	}

	// StepClassementCompetitionEquipeJournee
	function StepClassementCompetitionEquipeJournee($idEquipeA, $arrayCltA, $idEquipeB, $arrayCltB, $idJournee)
	{
		$myBdd = new MyBdd();
		
		$this->ExistCompetitionEquipeJournee($idEquipeA, $idJournee);
		$this->ExistCompetitionEquipeJournee($idEquipeB, $idJournee);
				
		// Equipe A ...
		$sql  = "Update gickp_Competitions_Equipes_Journee Set Pts=Pts+";
		$sql .= $arrayCltA['Pts'];
		$sql .= ", J=J+";
		$sql .= $arrayCltA['J'];
		$sql .= ", G=G+";
		$sql .= $arrayCltA['G'];
		$sql .= ", N=N+";
		$sql .= $arrayCltA['N'];
		$sql .= ", P=P+";
		$sql .= $arrayCltA['P'];
		$sql .= ", F=F+";
		$sql .= $arrayCltA['F'];
		$sql .= ", Plus=Plus+";
		$sql .= $arrayCltA['Plus'];
		$sql .= ", Moins=Moins+";
		$sql .= $arrayCltA['Moins'];;
		$sql .= ", Diff=Diff+";
		$sql .= $arrayCltA['Diff'];
		$sql .= ", PtsNiveau = PtsNiveau+";
		$sql .= $arrayCltA['PtsNiveau'];
		$sql .= " Where Id = $idEquipeA ";
		$sql .= " And Id_journee = $idJournee";
		
		mysql_query($sql, $myBdd->m_link) or die ("Erreur Update A");
		
		// Equipe B ...
		$sql  = "Update gickp_Competitions_Equipes_Journee Set Pts=Pts+";
		$sql .= $arrayCltB['Pts'];
		$sql .= ", J=J+";
		$sql .= $arrayCltB['J'];
		$sql .= ", G=G+";
		$sql .= $arrayCltB['G'];
		$sql .= ", N=N+";
		$sql .= $arrayCltB['N'];
		$sql .= ", P=P+";
		$sql .= $arrayCltB['P'];
		$sql .= ", F=F+";
		$sql .= $arrayCltB['F'];
		$sql .= ", plus=plus+";
		$sql .= $arrayCltB['Plus'];
		$sql .= ", moins=moins+";
		$sql .= $arrayCltB['Moins'];;
		$sql .= ", Diff=Diff+";
		$sql .= $arrayCltB['Diff'];
		$sql .= ", PtsNiveau = PtsNiveau+";
		$sql .= $arrayCltB['PtsNiveau'];
		$sql .= " Where Id = $idEquipeB ";
		$sql .= " And Id_journee = $idJournee";
	
		mysql_query($sql, $myBdd->m_link) or die ("Erreur Update B");
	}
		
	function FinalisationClassementChpt($codeCompet, $codeSaison)
	{
		$myBdd = new MyBdd();
		
		// Chargement des Equipes par ordre de Pts ...
		$sql  = "Select Id, Clt, Pts, J, G, N, P, F, Plus, Moins, Diff ";
		$sql .= "From gickp_Competitions_Equipes ";
		$sql .= "Where Code_compet = '";
		$sql .= $codeCompet;
		$sql .= "' And Code_saison = '";
		$sql .= $codeSaison;
		$sql .= "' Order By Pts DESC, Diff DESC, Plus DESC ";	 

		$oldClt = 0;
		$oldPts = 0;
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load 2");
		$num_results = mysql_num_rows($result);
        $egalites = 1;
		
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);	 
			
			if ($row['Pts'] != $oldPts) {
                $clt = $i + 1;
                $oldClt = $clt;
                $oldPts = $row['Pts'];
            } else {
                $clt = $oldClt;
                $egalites ++;
            }

            $sql  = "Update gickp_Competitions_Equipes Set Clt = ";
			$sql .= $clt;
			$sql .= " Where Id = ";
			$sql .= $row['Id'];
			
			mysql_query($sql, $myBdd->m_link) or die ("Erreur Update");
		}
        return $egalites;
	}
	
	function FinalisationClassementNiveau($codeCompet, $codeSaison)
	{
		$myBdd = new MyBdd();
		
		// Chargement des Equipes par ordre de Pts ...
		$sql  = "Select Id, PtsNiveau, Diff ";
		$sql .= "From gickp_Competitions_Equipes ";
		$sql .= "Where Code_compet = '";
		$sql .= $codeCompet;
		$sql .= "' And Code_saison = '";
		$sql .= $codeSaison;
		$sql .= "' Order By PtsNiveau Desc, Diff Desc ";	 

		$oldClt = 0;
		$oldPts = 0;
		$oldDiff = 9999;
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load 3");
		$num_results = mysql_num_rows($result);
		
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);	 
			
			if ( (abs($row['PtsNiveau']-$oldPts) >= 1) || ($row['Diff'] != $oldDiff) )
			{
				$clt = $i+1;
				
				$oldClt = $clt;
				$oldPts = $row['PtsNiveau'];
				$oldDiff = $row['Diff'];
			}
			else
				$clt = $oldClt;
			
			$sql  = "Update gickp_Competitions_Equipes Set CltNiveau = ";
			$sql .= $clt;
			$sql .= " Where Id = ";
			$sql .= $row['Id'];
			
			mysql_query($sql, $myBdd->m_link) or die ("Erreur Update");
		}
	}
	
	function FinalisationClassementNiveauChpt($codeCompet, $codeSaison)
	{
		$myBdd = new MyBdd();
		
		// Chargement des Equipes par ordre de Pts ...
		$sql  = "Select a.Id, a.Niveau, a.Clt, a.Pts, a.J, a.G, a.N, a.P, a.F, a.Plus, a.Moins, a.Diff ";
		$sql .= "From gickp_Competitions_Equipes_Niveau a, gickp_Competitions_Equipes b ";
		$sql .= "Where a.Id = b.Id ";
		$sql .= "And b.Code_compet = '";
		$sql .= $codeCompet;
		$sql .= "' And b.Code_saison = '";
		$sql .= $codeSaison;
		$sql .= "' Order By a.Niveau, a.Pts Desc, a.Diff Desc ";	 

		$oldClt = 0;
		$oldPts = 0;
		$oldNiveau = -1;
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load 4");
		$num_results = mysql_num_rows($result);
		
		for ($i=0,$j=0;$i<$num_results;$i++,$j++)
		{
			$row = mysql_fetch_array($result);	
			
			if ($row['Niveau'] != $oldNiveau)
			{
				$oldNiveau = $row['Niveau'];
				$clt = 1;
				$oldClt = $clt;
				$oldPts = $row['Pts'];
				$j=0;
			} 
			else
			{		
				if ($row['Pts'] != $oldPts)
				{
					$clt = $j+1;
					$oldClt = $clt;
					$oldPts = $row['Pts'];
				}
				else
					$clt = $oldClt;
			}
					
			$sql  = "Update gickp_Competitions_Equipes_Niveau Set Clt = ";
			$sql .= $clt;
			$sql .= " Where Id = ";
			$sql .= $row['Id'];
			$sql .= " And Niveau = ";
			$sql .= $row['Niveau'];
		
			mysql_query($sql, $myBdd->m_link) or die ("Erreur Update");
		}
	}
	
	function FinalisationClassementNiveauNiveau($codeCompet, $codeSaison)
	{
		$myBdd = new MyBdd();
		
		// Chargement des Equipes par ordre de Pts ...
		$sql  = "Select a.Id, a.Niveau, a.PtsNiveau, a.Diff ";
		$sql .= "From gickp_Competitions_Equipes_Niveau a, gickp_Competitions_Equipes b ";
		$sql .= "Where a.Id = b.Id ";
		$sql .= "And b.Code_compet = '";
		$sql .= $codeCompet;
		$sql .= "' And b.Code_saison = '";
		$sql .= $codeSaison;
		$sql .= "' Order By a.Niveau, a.PtsNiveau Desc, a.Diff Desc ";	 

		$oldClt = 0;
		$oldPts = 0;
		$oldDiff = 9999;
		$oldNiveau = -1;
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load 3");
		$num_results = mysql_num_rows($result);
		
		for ($i=0,$j=0;$i<$num_results;$i++,$j++)
		{
			$row = mysql_fetch_array($result);	 
			
			if ($row['Niveau'] != $oldNiveau)
			{
				$oldNiveau = $row['Niveau'];
				$clt = 1;
				$oldClt = $clt;
				$oldPts = $row['PtsNiveau'];
				$oldDiff =  $row['Diff'];
				$j=0;
			} 
			else
			{
				if ( (abs($row['PtsNiveau']-$oldPts) >= 1) || ($row['Diff'] != $oldDiff) )
				{
					$clt = $j+1;
					
					$oldClt = $clt;
					$oldPts = $row['PtsNiveau'];
					$oldDiff = $row['Diff'];
				}
				else
					$clt = $oldClt;
			}
					
			$sql  = "Update gickp_Competitions_Equipes_Niveau Set CltNiveau = ";
			$sql .= $clt;
			$sql .= " Where Id = ";
			$sql .= $row['Id'];
			$sql .= " And Niveau = ";
			$sql .= $row['Niveau'];
			
			mysql_query($sql, $myBdd->m_link) or die ("Erreur Update");
		}
	}
	
	// FinalisationClassementJourneeChpt
	function FinalisationClassementJourneeChpt($codeCompet, $codeSaison)
	{
		$myBdd = new MyBdd();
		
		// Chargement des Equipes par ordre de Pts ...
		$sql  = "Select a.Id, a.Id_journee, a.Clt, a.Pts, a.J, a.G, a.N, a.P, a.F, a.Plus, a.Moins, a.Diff ";
		$sql .= "From gickp_Competitions_Equipes_Journee a, gickp_Competitions_Equipes b ";
		$sql .= "Where a.Id = b.Id ";
		$sql .= "And b.Code_compet = '";
		$sql .= $codeCompet;
		$sql .= "' And b.Code_saison = '";
		$sql .= $codeSaison;
		$sql .= "' Order By a.Id_journee, a.Pts Desc, a.Diff Desc, a.Plus Desc ";	 

		$oldClt = 0;
		$oldPts = 0;
		$oldIdJournee = 0;
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load 4");
		$num_results = mysql_num_rows($result);
		
		for ($i=0,$j=0;$i<$num_results;$i++,$j++)
		{
			$row = mysql_fetch_array($result);	
			
			if ($row['Id_journee'] != $oldIdJournee)
			{
				$oldIdJournee = $row['Id_journee'];
				$clt = 1;
				$oldClt = $clt;
				$oldPts = $row['Pts'];
				$j=0;
			} 
			else
			{		
				//if ($row['Pts'] != $oldPts)
				//{
					$clt = $j+1;
					$oldClt = $clt;
					$oldPts = $row['Pts'];
				//}
				//else
				//	$clt = $oldClt;
			}
					
			$sql  = "Update gickp_Competitions_Equipes_Journee Set Clt = ";
			$sql .= $clt;
			$sql .= " Where Id = ";
			$sql .= $row['Id'];
			$sql .= " And Id_journee = ";
			$sql .= $row['Id_journee'];
		
			mysql_query($sql, $myBdd->m_link) or die ("Erreur Update");
		}
	}
	
	function FinalisationClassementJourneeNiveau($codeCompet, $codeSaison)
	{
		$myBdd = new MyBdd();
		
		// Chargement des Equipes par ordre de Pts ...
		$sql  = "Select a.Id, a.Id_journee, a.PtsNiveau, a.Diff ";
		$sql .= "From gickp_Competitions_Equipes_Journee a, gickp_Competitions_Equipes b ";
		$sql .= "Where a.Id = b.Id ";
		$sql .= "And b.Code_compet = '";
		$sql .= $codeCompet;
		$sql .= "' And b.Code_saison = '";
		$sql .= $codeSaison;
		$sql .= "' Order By a.Id_journee, a.PtsNiveau Desc, a.Diff Desc ";	 

		$oldClt = 0;
		$oldPts = 0;
		$oldDiff = 9999;
		$oldIdJournee = 0;
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load 3");
		$num_results = mysql_num_rows($result);
		
		for ($i=0,$j=0;$i<$num_results;$i++,$j++)
		{
			$row = mysql_fetch_array($result);	 
			
			if ($row['Id_journee'] != $oldIdJournee)
			{
				$oldIdJournee = $row['Id_journee'];
				$clt = 1;
				$oldClt = $clt;
				$oldPts = $row['PtsNiveau'];
				$oldDiff = $row['Diff'];
				$j=0;
			} 
			else
			{
				if ( (abs($row['PtsNiveau']-$oldPts) >= 1) || ($row['Diff'] != $oldDiff) )
				{
					$clt = $j+1;
					
					$oldClt = $clt;
					$oldPts = $row['PtsNiveau'];
					$oldDiff = $row['Diff'];
				}
				else
					$clt = $oldClt;
			}
					
			$sql  = "Update gickp_Competitions_Equipes_Journee Set CltNiveau = ";
			$sql .= $clt;
			$sql .= " Where Id = ";
			$sql .= $row['Id'];
			$sql .= " And Id_journee = ";
			$sql .= $row['Id_journee'];
			
			mysql_query($sql, $myBdd->m_link) or die ("Erreur Update");
		}
	}

	function PublicationClassement()
	{
		$myBdd = new MyBdd();
	
		$codeSaison = utyGetSaison();
		$codeCompet = utyGetPost('codeCompet');
		$_SESSION['codeCompet'] = $codeCompet;
		
		//Update date publication
		$sql  = "Update gickp_Competitions Set Date_publication = '";
		$sql .= date('Y-m-d H:i:s');
		$sql .= "', Date_publication_calcul = Date_calcul, Code_uti_publication = '";
		$sql .= utyGetSession('User');
		$sql .= "', Mode_publication_calcul = Mode_calcul ";
		$sql .= "Where Code = '";
		$sql .= $codeCompet;
		$sql .= "' And Code_saison = '";
		$sql .= $codeSaison;
		$sql .= "' ";	 
		mysql_query($sql, $myBdd->m_link) or die ("Erreur Update");

		//Update Classement
		$sql  = "Update gickp_Competitions_Equipes ";
		$sql .= "Set Pts_publi = Pts, Clt_publi = Clt, J_publi = J, G_publi = G, N_publi = N, P_publi = P, F_publi = F, ";
		$sql .= "Plus_publi = Plus, Moins_publi = Moins, Diff_publi = Diff, PtsNiveau_publi = PtsNiveau, CltNiveau_publi = CltNiveau ";
		$sql .= "Where Code_compet = '";
		$sql .= $codeCompet;
		$sql .= "' And Code_saison = '";
		$sql .= $codeSaison;
		$sql .= "' ";	 
		mysql_query($sql, $myBdd->m_link) or die ("Erreur Update 2");
		
		//Update Classement journées/phases
		$sql  = "Update gickp_Competitions_Equipes_Journee a, gickp_Competitions_Equipes b, gickp_Journees c  ";
		$sql .= "Set a.Pts_publi = a.Pts, a.Clt_publi = a.Clt, a.J_publi = a.J, a.G_publi = a.G, a.N_publi = a.N, a.P_publi = a.P, a.F_publi = a.F, ";
		$sql .= "a.Plus_publi = a.Plus, a.Moins_publi = a.Moins, a.Diff_publi = a.Diff, a.PtsNiveau_publi = a.PtsNiveau, a.CltNiveau_publi = a.CltNiveau ";
		$sql .= "Where a.Id = b.Id ";
		$sql .= "And c.Id = a.Id_journee ";
		$sql .= "And c.Code_competition = '";
		$sql .= $codeCompet;
		$sql .= "' And c.Code_saison = '";
		$sql .= $codeSaison;
		$sql .= "' ";	 
		mysql_query($sql, $myBdd->m_link) or die ("Erreur Update 3");
		
		//Update Classement niveau
		$sql  = "Update gickp_Competitions_Equipes_Niveau a, gickp_Competitions_Equipes b  ";
		$sql .= "Set a.Pts_publi = a.Pts, a.Clt_publi = a.Clt, a.J_publi = a.J, a.G_publi = a.G, a.N_publi = a.N, a.P_publi = a.P, a.F_publi = a.F, ";
		$sql .= "a.Plus_publi = a.Plus, a.Moins_publi = a.Moins, a.Diff_publi = a.Diff, a.PtsNiveau_publi = a.PtsNiveau, a.CltNiveau_publi = a.CltNiveau ";
		$sql .= "Where a.Id = b.Id ";
		$sql .= "And b.Code_compet = '";
		$sql .= $codeCompet;
		$sql .= "' And b.Code_saison = '";
		$sql .= $codeSaison;
		$sql .= "' ";	 
		mysql_query($sql, $myBdd->m_link) or die ("Erreur Update 4");


		$myBdd->utyJournal('Publication Classement', $codeSaison, $codeCompet);
	}
		
	function DePublicationClassement() // RAZ classement public
	{
		$myBdd = new MyBdd();
	
		$codeSaison = utyGetSaison();
		$codeCompet = utyGetPost('codeCompet');
		$_SESSION['codeCompet'] = $codeCompet;
		
		//Update date publication
		$sql  = "Update gickp_Competitions Set Date_publication = '";
		$sql .= date('Y-m-d H:i:s');
		$sql .= "', Date_publication_calcul = Date_calcul, Code_uti_publication = '";
		$sql .= utyGetSession('User');
		$sql .= "', Mode_publication_calcul = Mode_calcul ";
		$sql .= "Where Code = '";
		$sql .= $codeCompet;
		$sql .= "' And Code_saison = '";
		$sql .= $codeSaison;
		$sql .= "' ";	 
		mysql_query($sql, $myBdd->m_link) or die ("Erreur Update");

		//Update Classement
		$sql  = "Update gickp_Competitions_Equipes ";
		$sql .= "Set Clt_publi = 0, CltNiveau_publi = 0 ";
		$sql .= "Where Code_compet = '";
		$sql .= $codeCompet;
		$sql .= "' And Code_saison = '";
		$sql .= $codeSaison;
		$sql .= "' ";	 
		mysql_query($sql, $myBdd->m_link) or die ("Erreur Update 2");
		
		//Update Classement journées/phases
		$sql  = "Update gickp_Competitions_Equipes_Journee a, gickp_Competitions_Equipes b, gickp_Journees c  ";
		$sql .= "Set a.Clt_publi = 0, a.CltNiveau_publi = 0 ";
		$sql .= "Where a.Id = b.Id ";
		$sql .= "And c.Id = a.Id_journee ";
		$sql .= "And c.Code_competition = '";
		$sql .= $codeCompet;
		$sql .= "' And c.Code_saison = '";
		$sql .= $codeSaison;
		$sql .= "' ";	 
		mysql_query($sql, $myBdd->m_link) or die ("Erreur Update 3");
		
		//Update Classement niveau
		$sql  = "Update gickp_Competitions_Equipes_Niveau a, gickp_Competitions_Equipes b  ";
		$sql .= "Set a.Clt_publi = 0, a.CltNiveau_publi = 0 ";
		$sql .= "Where a.Id = b.Id ";
		$sql .= "And b.Code_compet = '";
		$sql .= $codeCompet;
		$sql .= "' And b.Code_saison = '";
		$sql .= $codeSaison;
		$sql .= "' ";	 
		mysql_query($sql, $myBdd->m_link) or die ("Erreur Update 4");


		$myBdd->utyJournal('Publication Classement RAZ', $codeSaison, $codeCompet);
	}
		
	// Transfert des Equipes séléctionnées 		
	function Transfert()
	{
		$codeCompet = utyGetPost('codeCompet');
		$codeSaison = utyGetSaison();
		
		$codeCompetTransfert = utyGetPost('codeCompetTransfert');
		$codeSaisonTransfert = utyGetPost('codeSaisonTransfert');
		$lstEquipe = utyGetPost('ParamCmd');

		if ( (strlen($codeCompet) > 0) && (strlen($codeCompetTransfert) > 0) && (strlen($codeSaisonTransfert) > 0)&& (strlen($lstEquipe) > 0) )
		{
			if ($codeCompet.$codeSaison != $codeCompetTransfert.$codeSaisonTransfert)
			{
				$myBdd = new MyBdd();
				
				// Raz Id_dupli ... 
				$sql  = "Update gickp_Competitions_Equipes Set Id_dupli = null Where Id_dupli In ($lstEquipe)";
				mysql_query($sql, $myBdd->m_link) or die ("Erreur Update");
							
				// Insertion des Equipes ...
				$sql  = "Insert Into gickp_Competitions_Equipes (Code_compet,Code_saison, Libelle, Code_club, Numero, Id_dupli) ";
				$sql .= "Select '$codeCompetTransfert', '$codeSaisonTransfert', Libelle, Code_club, Numero, Id ";
				$sql .= "From gickp_Competitions_Equipes Where Id In ($lstEquipe) ";
				mysql_query($sql, $myBdd->m_link) or die ("Erreur Insert");
				
				// Insertion des Joueurs Equipes ...
				$sql  = "Insert Into gickp_Competitions_Equipes_Joueurs (Id_equipe, Matric, Nom, Prenom, Sexe, Categ, Numero, Capitaine) ";
				$sql .= "Select b.Id, a.Matric, a.Nom, a.Prenom, a.Sexe, d.Code, a.Numero, a.Capitaine ";
				$sql .= "From gickp_Competitions_Equipes_Joueurs a, gickp_Competitions_Equipes b, gickp_Competitions_Equipes c, gickp_Categorie d, gickp_Liste_Coureur e ";
				$sql .= "Where a.Id_equipe = b.Id_dupli ";
				$sql .= "And a.Matric = e.Matric ";
				$sql .= "And a.Id_equipe = c.Id ";
				$sql .= "And b.Id_dupli In ($lstEquipe) ";
				$sql .= "And c.Code_compet = '$codeCompet' And c.Code_saison = '$codeSaison' ";
				$sql .= "And " ;
				$sql .= $codeSaisonTransfert;
				$sql .= "-Year(e.Naissance) between d.Age_min And d.Age_max ";
				mysql_query($sql, $myBdd->m_link) or die ("Erreur Insert 2 (transmettez cette requête à laurent@poloweb.org) : <br><br>".$sql);

				$myBdd->utyJournal('Transfert Equipes', $codeSaison, $codeCompet, 'NULL', 'NULL', 'NULL', 'Equipes '.$lstEquipe.' vers '.$codeCompetTransfert.'-'.$codeSaisonTransfert);
			}
			else
			{
				die ("Transfert impossible dans la même compétition !");
			}
		}
		else
		{
			die ("Pas d'équipe à transférer !");
		}
		
	}

	function SetSessionSaison()
	{
		$codeSaison = utyGetPost('ParamCmd', '');
		if (strlen($codeSaison) == 0)
			return;
		
		$_SESSION['Saison'] = $codeSaison;
	}

	// GestionClassement 		
	function GestionClassement()
	{			
	  MyPageSecure::MyPageSecure(10);
		
		$alertMessage = '';

		$Cmd = utyGetPost('Cmd');
		if (strlen($Cmd) > 0)
		{
			if ($Cmd == 'DoClassement')
				($_SESSION['Profile'] <= 6) ? $alertMessage = $this->DoClassement() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'PublicationClassement')
				($_SESSION['Profile'] <= 4) ? $this->PublicationClassement() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'DePublicationClassement')
				($_SESSION['Profile'] <= 3) ? $this->DePublicationClassement() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'InitClassement')
				($_SESSION['Profile'] <= 4) ? $this->InitClassement() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'Transfert')
				($_SESSION['Profile'] <= 3) ? $this->Transfert() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'SessionSaison')
				($_SESSION['Profile'] <= 10) ? $this->SetSessionSaison() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($alertMessage == '')
			{
				header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);	
				exit;
			}
		}

		$this->SetTemplate("Gestion des Classements", "Classements", false);
		$this->Load();
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplate('GestionClassement');
	}
}		  	

$page = new GestionClassement();

