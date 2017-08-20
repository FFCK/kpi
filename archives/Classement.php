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
		
		$activeSaison = $myBdd->GetActiveSaison();
		$codeSaison = utyGetSaison();
		$codeSaison = utyGetPost('saisonTravail', $codeSaison);
		$codeSaison = utyGetGet('Saison', $codeSaison);
		$_SESSION['Saison'] = $codeSaison;
		$this->m_tpl->assign('Saison', $codeSaison);
		$codeSaison2 = $codeSaison;
//		if (date("n") >= 9 && date("n") <= 12 && $codeSaison == $activeSaison)
//		{
//			$codeSaison2 = $codeSaison - 1; //Année en cours
//		}
		$codeSaison3 = $codeSaison;
	
		//Logo
		if($codeCompet != -1)
		{
			$logo = "img/logo/".$codeSaison.'-'.$codeCompet.'.jpg';
			if(file_exists($logo))
				$this->m_tpl->assign('logo', $logo);
		}

		// Chargement des Saisons ...
		$sql  = "Select Code, Etat, Nat_debut, Nat_fin, Inter_debut, Inter_fin ";
		$sql .= "From gickp_Saison ";
		$sql .= "Order By Code ";	 
		
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
		$arrayCompetition = array();
			//Compétitions internationales
/*		$sql  = "Select Code_niveau, Code_ref, Code_tour, Code, Libelle ";
		$sql .= "From gickp_Competitions ";
		$sql .= "Where Code_saison = '";
		$sql .= $codeSaison2;
		$sql .= "' ";
		$sql .= utyGetFiltreCompetition('');
		$sql .= " And Code_niveau Like '".utyGetSession('AfficheNiveau')."%' ";
		$sql .= " And Code Like '".utyGetSession('AfficheCompet')."%' ";
		$sql .= " And Publication='O' ";
		$sql .= " And Code_niveau = 'INT' ";
		$sql .= " Order By Code_niveau, COALESCE(Code_ref, 'z'), Code_tour, Code";	 
*/
		$sql  = "Select c.* ";
		$sql .= "From gickp_Competitions c, gickp_Competitions_Groupes g ";
		$sql .= "Where c.Code_saison = '";
		$sql .= $codeSaison2;
		$sql .= "' ";
		$sql .= utyGetFiltreCompetition('c.');
		$sql .= " And c.Code_niveau Like '".utyGetSession('AfficheNiveau')."%' ";
		if(utyGetSession('AfficheCompet') == 'NCF')
			$sql .= " And (c.Code Like 'N%' OR c.Code Like 'CF%') ";
		else
			$sql .= " And c.Code Like '".utyGetSession('AfficheCompet')."%' ";
		$sql .= " And c.Publication='O' ";
		$sql .= " And c.Code_ref = g.Groupe ";
		$sql .= " And c.Code_niveau = 'INT' ";
		$sql .= " Order By c.Code_niveau, g.Id, COALESCE(c.Code_ref, 'z'), c.Code_tour, c.GroupOrder, c.Code";	 
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load 6a");
		$num_results = mysql_num_rows($result);
		if ($num_results > 0)
		{
			array_push($arrayCompetition, array('', '', '' ) );
			array_push($arrayCompetition, array('', '.....COMPETITIONS INTERNATIONALES '.$codeSaison2.'.....', '' ) );
		}
		
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);	
			
			if ( (strlen($codeCompet) == 0) && (i == 0) )
 				 $codeCompet = $row["Code"];
			
			if ($row["Code"] == $codeCompet)
			{
				array_push($arrayCompetition, array($row["Code"], /*$row["Code"]." - ".*/$row["Libelle"], "SELECTED" ) );
				$this->m_tpl->assign('Code_niveau', $row["Code_niveau"]);
				$codeSaison3 = $codeSaison2;
			}
			else
				array_push($arrayCompetition, array($row["Code"], /*$row["Code"]." - ".*/$row["Libelle"], "" ) );
		}
			//Compétitions nationales
		$sql  = "Select Code_niveau, Code_ref, Code_tour, Code, Libelle ";
		$sql .= "From gickp_Competitions ";
		$sql .= "Where Code_saison = '";
		$sql .= $codeSaison;
		$sql .= "' ";
		$sql .= utyGetFiltreCompetition('');
		$sql .= " And Code_niveau Like '".utyGetSession('AfficheNiveau')."%' ";
		if(utyGetSession('AfficheCompet') == 'NCF')
			$sql .= " And (Code Like 'N%' OR Code Like 'CF%') ";
		else
			$sql .= " And Code Like '".utyGetSession('AfficheCompet')."%' ";
		$sql .= " And Publication='O' ";
		$sql .= " And Code_niveau = 'NAT' ";
		$sql .= " Order By Code_niveau, COALESCE(Code_ref, 'z'), Code_tour, Code";	 
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load 6b");
		$num_results = mysql_num_rows($result);
		if ($num_results > 0)
		{
			array_push($arrayCompetition, array('', '', '' ) );
			array_push($arrayCompetition, array('', '.....COMPETITIONS NATIONALES '.$codeSaison.'.....', '' ) );
		}
		
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);	
			
			if ( (strlen($codeCompet) == 0) && (i == 0) )
 				 $codeCompet = $row["Code"];
			
			if ($row["Code"] == $codeCompet)
			{
				array_push($arrayCompetition, array($row["Code"], /*$row["Code"]." - ".*/$row["Libelle"], "SELECTED" ) );
				$this->m_tpl->assign('Code_niveau', $row["Code_niveau"]);
			}
			else
				array_push($arrayCompetition, array($row["Code"], /*$row["Code"]." - ".*/$row["Libelle"], "" ) );
		}

			//Compétitions régionales
		$sql  = "Select Code_niveau, Code_ref, Code_tour, Code, Libelle ";
		$sql .= "From gickp_Competitions ";
		$sql .= "Where Code_saison = '";
		$sql .= $codeSaison;
		$sql .= "' ";
		$sql .= utyGetFiltreCompetition('');
		$sql .= " And Code_niveau Like '".utyGetSession('AfficheNiveau')."%' ";
		if(utyGetSession('AfficheCompet') == 'NCF')
			$sql .= " And (Code Like 'N%' OR Code Like 'CF%') ";
		else
			$sql .= " And Code Like '".utyGetSession('AfficheCompet')."%' ";
		$sql .= " And Publication='O' ";
		$sql .= " And Code_niveau = 'REG' ";
		$sql .= " Order By Code_niveau, COALESCE(Code_ref, 'z'), Code_tour, Code";	 
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load 6b");
		$num_results = mysql_num_rows($result);
		if ($num_results > 0)
		{
			array_push($arrayCompetition, array('', '', '' ) );
			array_push($arrayCompetition, array('', '.....COMPETITIONS REGIONALES '.$codeSaison.'.....', '' ) );
		}

		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);	
			
			if ( (strlen($codeCompet) == 0) && (i == 0) )
 				 $codeCompet = $row["Code"];
			
			if ($row["Code"] == $codeCompet)
			{
				array_push($arrayCompetition, array($row["Code"], /*$row["Code"]." - ".*/$row["Libelle"], "SELECTED" ) );
				$this->m_tpl->assign('Code_niveau', $row["Code_niveau"]);
			}
			else
				array_push($arrayCompetition, array($row["Code"], /*$row["Code"]." - ".*/$row["Libelle"], "" ) );
		}

		$this->m_tpl->assign('arrayCompetition', $arrayCompetition);
		$_SESSION['codeCompet'] = $codeCompet;
		$this->m_tpl->assign('codeCompet', $codeCompet);
		$this->m_tpl->assign('codeSaison2', $codeSaison2);
		$this->m_tpl->assign('codeSaison3', $codeSaison3);

		// Chargement des Equipes ...
		$arrayEquipe = array();
		$arrayEquipe_journee = array();
		$arrayEquipe_journee_publi = array();
		$arrayEquipe_publi = array();

		// Par défaut type Championnat et compétition non internationale...
		$typeClt = 'CHPT';
		
		if (strlen($codeCompet) > 0)
		{
			if (utyGetPost('ParamCmd', '') == 'changeCompetition')
			{
				$typeClt = $this->GetTypeClt($codeCompet, $codeSaison3);
			}
			else
			{
				$typeClt = utyGetPost('orderCompet', '');
				if ($typeClt == '')
					$typeClt = $this->GetTypeClt($codeCompet, $codeSaison3);
			}
				
			$recordCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison3);

			$this->m_tpl->assign('Statut', $recordCompetition['Statut']);
			$this->m_tpl->assign('Date_calcul', $recordCompetition['Date_calcul']);
			$this->m_tpl->assign('Date_publication', $recordCompetition['Date_publication']);
			$this->m_tpl->assign('Date_publication_calcul', $recordCompetition['Date_publication_calcul']);
			$this->m_tpl->assign('Code_uti_calcul', $recordCompetition['Code_uti_calcul']);
			$this->m_tpl->assign('Code_uti_publication', $recordCompetition['Code_uti_publication']);
			$this->m_tpl->assign('Mode_calcul', $recordCompetition['Mode_calcul']);
			$this->m_tpl->assign('Mode_publication_calcul', $recordCompetition['Mode_publication_calcul']);
			
			
			
			// Classement public				
			$sql  = "Select ce.Id, ce.Numero, ce.Libelle, ce.Code_club, ce.Clt_publi, ce.Pts_publi, ce.J_publi, ce.G_publi, ce.N_publi, ce.P_publi, ce.F_publi, ce.Plus_publi, ce.Moins_publi, ce.Diff_publi, ce.PtsNiveau_publi, ce.CltNiveau_publi, c.Code_comite_dep ";
			$sql .= "From gickp_Competitions_Equipes ce, gickp_Club c ";
			$sql .= "Where ce.Code_compet = '";
			$sql .= $codeCompet;
			$sql .= "' And ce.Code_saison = '";
			$sql .= $codeSaison3;
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
				array_push($arrayEquipe_publi, array( 'Id' => $row['Id'], 'Numero' => $row['Numero'], 'Libelle' => $row['Libelle'], 'Code_club' => $row['Code_club'], 'Code_comite_dep' => $row['Code_comite_dep'],
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
				// Classement public par journée/phase
				$sql  = "Select a.Id, a.Numero, a.Libelle, a.Code_club, ";
				$sql .= "b.Id_journee, b.Clt_publi, b.Pts_publi, b.J_publi, b.G_publi, b.N_publi, b.P_publi, b.F_publi, b.Plus_publi, b.Moins_publi, b.Diff_publi, b.PtsNiveau_publi, b.CltNiveau_publi, ";
				$sql .= "c.Phase, c.Niveau ";
				$sql .= "From gickp_Competitions_Equipes a, ";
				$sql .= "gickp_Competitions_Equipes_Journee b Join gickp_Journees c On (b.Id_journee = c.Id) ";
				$sql .= "Where a.Id = b.Id ";
				$sql .= "And c.Code_competition = '";
				$sql .= $codeCompet;
				$sql .= "' And c.Code_saison = '";
				$sql .= $codeSaison3;
				$sql .= "' Order By c.Niveau Desc, b.Id_journee Asc, b.Clt_publi Asc, b.Diff_publi Desc, b.Plus_publi Asc ";	 
				
				$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load 5d");
				$num_results = mysql_num_rows($result);
			
				for ($i=0;$i<$num_results;$i++)
				{
					$row = mysql_fetch_array($result);	  
							
					array_push($arrayEquipe_journee_publi, array( 'Id' => $row['Id'], 'Numero' => $row['Numero'], 'Libelle' => $row['Libelle'], 'Code_club' => $row['Code_club'], 'Id_journee' => $row['Id_journee'], 
																	        'Phase' => $row['Phase'], 'Niveau' => $row['Niveau'], 'Clt' => $row['Clt_publi'], 'Pts' => $row['Pts_publi'], 
																	        'J' => $row['J_publi'], 'G' => $row['G_publi'], 'N' => $row['N_publi'], 
																	        'P' => $row['P_publi'], 'F' => $row['F_publi'], 'Plus' => $row['Plus_publi'], 
																	        'Moins' => $row['Moins_publi'], 'Diff' => $row['Diff_publi'],
																	        'PtsNiveau' => $row['PtsNiveau_publi'], 'CltNiveau' => $row['CltNiveau_publi'] ));
				}

			}
		}	
		$this->m_tpl->assign('arrayEquipe_journee_publi', $arrayEquipe_journee_publi);
		$this->m_tpl->assign('arrayEquipe_publi', $arrayEquipe_publi);

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
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplate('Classement');
	}
}		  	

$page = new Classement();

?>
