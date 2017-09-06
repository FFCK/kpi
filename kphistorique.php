<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

// Historique
	
class Historique extends MyPage	 
{	
	function Load()
	{
		$myBdd = new MyBdd();
		
		$codeCompetGroup = utyGetSession('codeCompetGroup', 'N1H');
		$codeCompetGroup = utyGetPost('Group', $codeCompetGroup);
		$codeCompetGroup = utyGetGet('Group', $codeCompetGroup);
		$this->m_tpl->assign('codeCompetGroup', $codeCompetGroup);
		$_SESSION['codeCompetGroup'] = $codeCompetGroup;

		$codeCompet = utyGetSession('codeCompet', 'N1H');
		$codeCompet = utyGetPost('comboCompet', $codeCompet);
		$codeCompet = utyGetGet('Compet', $codeCompet);
		$_SESSION['codeCompet'] = $codeCompet;

		$activeSaison = $myBdd->GetActiveSaison();
		$codeSaison = utyGetSaison();
		$codeSaison = utyGetPost('saisonTravail', $codeSaison);
		$codeSaison = utyGetGet('Saison', $codeSaison);
		$_SESSION['Saison'] = $codeSaison;
		$this->m_tpl->assign('Saison', $codeSaison);

		// Chargement des Saisons ...
		$sql  = "Select Code, Etat, Nat_debut, Nat_fin, Inter_debut, Inter_fin "
                . "From gickp_Saison "
                . "Order By Code DESC ";	 
		$result = $myBdd->Query($sql);
		$arraySaison = array();
        while ($row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC)){
            array_push($arraySaison, array('Code' => $row['Code'], 'Etat' => $row['Etat'], 
											'Nat_debut' => utyDateUsToFr($row['Nat_debut']), 'Nat_fin' => utyDateUsToFr($row['Nat_fin']), 
											'Inter_debut' => utyDateUsToFr($row['Inter_debut']), 'Inter_fin' => utyDateUsToFr($row['Inter_fin']) ));
		}
		$this->m_tpl->assign('arraySaison', $arraySaison);
		$this->m_tpl->assign('sessionSaison', $codeSaison);
		
		// Chargement des Groupes
			//Compétitions internationales
            $arrayCompetitionGroupe = array();
            array_push($arrayCompetitionGroupe, array('', 'CI', '=== COMPETITIONS INTERNATIONALES ===', '' ) );
            $sql  = "SELECT * FROM gickp_Competitions_Groupes WHERE id > 0 AND id < 10 ORDER BY id";
            $result = $myBdd->Query($sql);
            while ($row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC)){ 
                if ($row["Groupe"] == $codeCompetGroup) {
                    array_push($arrayCompetitionGroupe, array($row["id"], $row["Groupe"], $row["Libelle"], "SELECTED"));
                } else {
                    array_push($arrayCompetitionGroupe, array($row["id"], $row["Groupe"], $row["Libelle"], ""));
                }
            }
			//Compétitions nationales
            array_push($arrayCompetitionGroupe, array('', 'CN', '=== COMPETITIONS NATIONALES ===', '' ) );
            $sql  = "SELECT * FROM gickp_Competitions_Groupes WHERE id >= 10 AND id < 40 ORDER BY id";
            $result = $myBdd->Query($sql);
            while ($row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC)){ 
                if ($row["Groupe"] == $codeCompetGroup) {
                    array_push($arrayCompetitionGroupe, array($row["id"], $row["Groupe"], $row["Libelle"], "SELECTED"));
                } else {
                    array_push($arrayCompetitionGroupe, array($row["id"], $row["Groupe"], $row["Libelle"], ""));
                }
            }
			//Compétitions régionales
            array_push($arrayCompetitionGroupe, array('', 'CR', '=== COMPETITIONS REGIONALES ===', '' ) );
            $sql  = "SELECT * FROM gickp_Competitions_Groupes WHERE id >= 40 AND id < 60 ORDER BY id";
            $result = $myBdd->Query($sql);
            while ($row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC)){ 
                if ($row["Groupe"] == $codeCompetGroup) {
                    array_push($arrayCompetitionGroupe, array($row["id"], $row["Groupe"], $row["Libelle"], "SELECTED"));
                } else {
                    array_push($arrayCompetitionGroupe, array($row["id"], $row["Groupe"], $row["Libelle"], ""));
                }
            }
			//Tournois
            array_push($arrayCompetitionGroupe, array('', 'T', '=== TOURNOIS ===', '' ) );
            $sql  = "SELECT * FROM gickp_Competitions_Groupes WHERE id >= 60 AND id < 100 ORDER BY id";
            $result = $myBdd->Query($sql);
            while ($row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC)){ 
                if ($row["Groupe"] == $codeCompetGroup) {
                    array_push($arrayCompetitionGroupe, array($row["id"], $row["Groupe"], $row["Libelle"], "SELECTED"));
                } else {
                    array_push($arrayCompetitionGroupe, array($row["id"], $row["Groupe"], $row["Libelle"], ""));
                }
            }
		
		// Chargement des Compétitions ...
		$arraySaisons = array();
        $arrayCompets = array();
        $arrayClts = array();
        
		$arrayCompetition = array();
		$recordCompetition = array();
		$arrayEquipe_publi = array();
        $saison = 0;
		$existMatch = 0;
		$typeClt = array();
		$sql  = "SELECT c.*, g.Groupe "
                . "FROM gickp_Competitions c, gickp_Competitions_Groupes g "
                . "WHERE c.Publication='O' " . utyGetFiltreCompetition('c.') . " "
				. "AND c.Code_tour = 10 "
				. "AND c.Statut = 'END' "
                . "AND c.Code_ref = g.Groupe ";
                if ($codeCompetGroup[0] == 'N') {
                    $sql .= "AND c.Code_ref Like 'N%' ";
                } elseif ($codeCompetGroup[0] == 'C' && $codeCompetGroup[1] == 'F') {
                    $sql .= "AND c.Code_ref Like 'CF%' ";
                } else {
                    $sql .= "AND c.Code_ref = '$codeCompetGroup' ";
                }
                $sql .= "ORDER BY c.Code_saison DESC, c.Code_niveau, g.Id, COALESCE(c.Code_ref, 'z'), c.GroupOrder, c.Code_tour, c.Code";	 
        $result = $myBdd->Query($sql);
        while ($row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC)){ 
            if($saison != $row['Code_saison']) {
                array_push($arraySaisons, array('saison' => $row['Code_saison']));
            }
			$saison = $row['Code_saison'];
            if($row['LogoLink'] != '' && strpos($row['LogoLink'], 'http') === FALSE ){
                $row['LogoLink'] = 'img/logo/' . $row['LogoLink'];
                if(!is_file($row['LogoLink'])) {
                    $row['LogoLink'] = '';
                }
            }
            $arrayCompets[$saison][] = array('code' => $row["Code"], 'libelle' => $row["Libelle"], 
                                        'Soustitre' => $row["Soustitre"], 'Soustitre2' => $row["Soustitre2"], 
                                        'Web' => $row['Web'], 'LogoLink' => $row['LogoLink'],
                                        'Titre_actif' => $row["Titre_actif"], 'selected' => 'SELECTED' ) ;

			array_push($arrayCompetition, array($row["Code"], $row["Libelle"], "SELECTED" ) );
			$codeCompet1 = $row['Code'];
			$codeGroupe = $row['Groupe'];
			$typeClt = $row['Code_typeclt'];
			if ($row['ToutGroup'] == 'O' && $row['TouteSaisons'] == 'O' && ($row['Web'] != '' || $row['LogoLink'] != ''))
			{
				$recordCompetition[] = array( 'ToutGroup' => $row['ToutGroup'], 'Web' => $row['Web'], 'LogoLink' => $row['LogoLink'] );
			}
			//Existence de matchs
			$sql2  = "SELECT m.Id "
                    . "FROM gickp_Matchs m, gickp_Journees j "
                    . "WHERE m.Id_journee = j.Id "
                    . "AND m.Publication = 'O' "
                    . "AND j.Publication = 'O' "
                    . "AND j.Code_competition = '$codeCompet1' "
                    . "AND j.Code_saison = '$saison' ";
            $result2 = $myBdd->Query($sql2);
			if ($myBdd->NumRows($result2) > 0) {
                $existMatch = 1;
            }
		
			// Classement public				
			$sql2  = "SELECT ce.*, c.Code_comite_dep "
                    . "FROM gickp_Competitions_Equipes ce, gickp_Club c "
                    . "WHERE ce.Code_compet = '$codeCompet1' "
                    . "AND ce.Code_saison = '$saison' "
                    . "AND ce.Code_club = c.Code ";	 
                    if ($typeClt == 'CP') {
                        $sql2 .= "ORDER BY CltNiveau_publi ASC, Diff_publi DESC ";
                    } else {
                        $sql2 .= "ORDER BY Clt_publi ASC, Diff_publi DESC ";
                    }

            $result2 = $myBdd->Query($sql2);
            while ($row2 = $myBdd->FetchArray($result2, $resulttype=MYSQL_ASSOC)){ 
                //Logos
                $logo = '';
                $club = $row2['Code_club'];
                if(is_file('img/KIP/logo/'.$club.'-logo.png')){
                    $logo = 'img/KIP/logo/'.$club.'-logo.png';
                }elseif(is_file('img/Nations/'.substr($club, 0, 3).'.png')){
                    $club = substr($club, 0, 3);
                    $logo = 'img/Nations/'.$club.'.png';
                }
				if (strlen($row2['Code_comite_dep']) > 3) {
                    $row2['Code_comite_dep'] = 'FRA';
                }
                if ($row2['Clt_publi'] != 0 || $row2['CltNiveau_publi']) {
					$arrayEquipe_publi[] = array( 'CodeGroupe' => $codeGroupe, 'CodeCompet' => $row['Code'], 'CodeSaison' => $row['Code_saison'], 'LibelleCompet' => $row['Libelle'], 'Code_typeclt' => $row['Code_typeclt'],
												'Code_tour' => $row['Code_tour'], 'Qualifies' => $row['Qualifies'], 'Elimines' => $row['Elimines'], 'Nb_equipes' => $row['Nb_equipes'],
												'Code_niveau' => $row['Code_niveau'], 'Titre_actif' => $row['Titre_actif'], 'Soustitre' => $row['Soustitre'], 'Soustitre2' => $row['Soustitre2'], 'Web' => $row['Web'], 'LogoLink' => $row['LogoLink'], 'ToutGroup' => $row['ToutGroup'], /*'LogoOK' => $row['LogoOK'],*/
												'Numero' => $row2['Numero'], 'Id' => $row2['Id'], 'Libelle' => $row2['Libelle'], 'Code_club' => $row2['Code_club'], 'Code_comite_dep' => $row2['Code_comite_dep'],
												'Clt' => $row2['Clt_publi'], 'Pts' => $row2['Pts_publi'], 'existMatch' => $existMatch,
												'J' => $row2['J_publi'], 'G' => $row2['G_publi'], 'N' => $row2['N_publi'], 
												'P' => $row2['P_publi'], 'F' => $row2['F_publi'], 'Plus' => $row2['Plus_publi'], 
												'Moins' => $row2['Moins_publi'], 'Diff' => $row2['Diff_publi'],
												'PtsNiveau' => $row2['PtsNiveau_publi'], 'CltNiveau' => $row2['CltNiveau_publi'], 
                                                'logo' => $logo, 'club' => $club );
					$arrayClts[$saison][$codeCompet1][] = array( 'CodeGroupe' => $codeGroupe, 'CodeCompet' => $row['Code'], 'CodeSaison' => $row['Code_saison'], 'LibelleCompet' => $row['Libelle'], 'Code_typeclt' => $row['Code_typeclt'],
												'Code_tour' => $row['Code_tour'], 'Qualifies' => $row['Qualifies'], 'Elimines' => $row['Elimines'], 'Nb_equipes' => $row['Nb_equipes'],
												'Code_niveau' => $row['Code_niveau'], 'Titre_actif' => $row['Titre_actif'], 'Soustitre' => $row['Soustitre'], 'Soustitre2' => $row['Soustitre2'], 'Web' => $row['Web'], 'LogoLink' => $row['LogoLink'], 'ToutGroup' => $row['ToutGroup'], /*'LogoOK' => $row['LogoOK'],*/
												'Numero' => $row2['Numero'], 'Id' => $row2['Id'], 'Libelle' => $row2['Libelle'], 'Code_club' => $row2['Code_club'], 'Code_comite_dep' => $row2['Code_comite_dep'],
												'Clt' => $row2['Clt_publi'], 'Pts' => $row2['Pts_publi'], 'existMatch' => $existMatch,
												'J' => $row2['J_publi'], 'G' => $row2['G_publi'], 'N' => $row2['N_publi'], 
												'P' => $row2['P_publi'], 'F' => $row2['F_publi'], 'Plus' => $row2['Plus_publi'], 
												'Moins' => $row2['Moins_publi'], 'Diff' => $row2['Diff_publi'],
												'PtsNiveau' => $row2['PtsNiveau_publi'], 'CltNiveau' => $row2['CltNiveau_publi'], 
												'logo' => $logo, 'club' => $club);
                    
				}
			}
		
		}
		$this->m_tpl->assign('arrayCompetition', $arrayCompetition);
		$this->m_tpl->assign('arrayCompetitionGroupe', $arrayCompetitionGroupe);
		$this->m_tpl->assign('arrayEquipe_publi', $arrayEquipe_publi);
		$this->m_tpl->assign('recordCompetition', $recordCompetition);
		$this->m_tpl->assign('typeClt', $typeClt);
//		print_r($recordCompetition);
//		print_r($arrayEquipe_publi);
//		print_r($arrayCompetition);
		$this->m_tpl->assign('arraySaisons', $arraySaisons);
		$this->m_tpl->assign('arrayCompets', $arrayCompets);
		$this->m_tpl->assign('arrayClts', $arrayClts);

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
		$sql  = "SELECT COUNT(*) Nb FROM gickp_Competitions_Equipes_Niveau WHERE Id = $idEquipe AND Niveau = $niveau ";
        $result = $myBdd->Query($sql);
		if ($myBdd->NumRows($result) == 1) {
			$row = $myBdd->FetchRow($result);	 
			if ($row['Nb'] == 1) {
                return;
            } // Le record existe ...
		}
		$sql  = "INSERT INTO gickp_Competitions_Equipes_Niveau (Id, Niveau, Pts, Clt, J, G, N, P, F, Plus, Moins, Diff, PtsNiveau, CltNiveau) "
                . "VALUES ($idEquipe, $niveau, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0) ";
		$myBdd->Query($sql);
	}

	// ExistCompetitionEquipeJournee
	function ExistCompetitionEquipeJournee($idEquipe, $idJournee)
	{
		$myBdd = new MyBdd();
		$sql  = "SELECT COUNT(*) Nb FROM gickp_Competitions_Equipes_Journee WHERE Id = $idEquipe AND Id_journee = $idJournee";
		$result = $myBdd->Query($sql);
		if ($myBdd->NumRows($result) == 1) {
			$row = $myBdd->FetchRow($result);	 
			if ($row['Nb'] == 1)
				return; // Le record existe ...
		}
		$sql  = "INSERT INTO gickp_Competitions_Equipes_Journee (Id, Id_journee, Pts, Clt, J, G, N, P, F, Plus, Moins, Diff, PtsNiveau, CltNiveau) "
                . "VALUES ($idEquipe, $idJournee, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0) ";
		$myBdd->Query($sql);
	}
	

	// Historique		
	function Historique()
	{			
		MyPage::MyPage();
		
		$this->SetTemplate("Historique", "Historique", true);
		$this->Load();
		//$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplateNew('kphistorique');
	}
}		  	

$page = new Historique();

?>
