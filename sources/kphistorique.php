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

		$codeSaison = $myBdd->GetActiveSaison();
		$codeSaison = utyGetPost('saisonTravail', $codeSaison);
		$codeSaison = utyGetGet('Saison', $codeSaison);
		$_SESSION['Saison'] = $codeSaison;
		$this->m_tpl->assign('Saison', $codeSaison);

		// Chargement des Saisons ...
		$sql = "SELECT Code, Etat, Nat_debut, Nat_fin, Inter_debut, Inter_fin 
			FROM gickp_Saison 
			ORDER BY Code DESC ";
		$arraySaison = array();
        $result = $myBdd->pdo->prepare($sql);
        $result->execute();
        while ($row = $result->fetch()) {
            array_push($arraySaison, array('Code' => $row['Code'], 'Etat' => $row['Etat'], 
				'Nat_debut' => utyDateUsToFr($row['Nat_debut']), 'Nat_fin' => utyDateUsToFr($row['Nat_fin']), 
				'Inter_debut' => utyDateUsToFr($row['Inter_debut']), 'Inter_fin' => utyDateUsToFr($row['Inter_fin']) ));
		}
		$this->m_tpl->assign('arraySaison', $arraySaison);
		$this->m_tpl->assign('sessionSaison', $codeSaison);
		
		// Chargement des Groupes
        $arrayCompetitionGroupe = $myBdd->GetGroups('public', $codeCompetGroup);
		$this->m_tpl->assign('arrayCompetitionGroupe', $arrayCompetitionGroupe);
		
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
		$filtreCompetition = utyGetFiltreCompetition('c.');
		$sql = "SELECT c.*, g.Groupe 
			FROM gickp_Competitions c, gickp_Competitions_Groupes g 
			WHERE c.Publication='O' 
			$filtreCompetition 
			AND c.Code_tour = 10 
			AND c.Statut = 'END' 
			AND c.Code_ref = g.Groupe 
			AND c.Code_ref Like ? 
			ORDER BY c.Code_saison DESC, c.Code_niveau, g.Id, COALESCE(c.Code_ref, 'z'), 
				c.GroupOrder, c.Code_tour, c.Code";
			if ($codeCompetGroup[0] == 'N') {
				$bindparam = 'N%';
			} elseif ($codeCompetGroup[0] == 'C' && $codeCompetGroup[1] == 'F') {
				$bindparam = 'CF%';
			} else {
				$bindparam = $codeCompetGroup;
			}
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($bindparam));
			while ($row = $result->fetch()) {
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
			$sql2 = "SELECT m.Id 
				FROM gickp_Matchs m, gickp_Journees j 
				WHERE m.Id_journee = j.Id 
				AND m.Publication = 'O' 
				AND j.Publication = 'O' 
				AND j.Code_competition = ? 
				AND j.Code_saison = ? ";
			$result2 = $myBdd->pdo->prepare($sql2);
			$result2->execute(array($codeCompet1, $codeSaison));
			if ($result2->rowCount() > 0) {
                $existMatch = 1;
            }
		
			// Classement public				
			$sql2 = "SELECT ce.Id, ce.Numero, ce.Libelle, ce.Code_club, ce.Clt_publi, ce.Pts_publi, 
				ce.J_publi, ce.G_publi, ce.N_publi, ce.P_publi, ce.F_publi, ce.Plus_publi, 
				ce.Moins_publi, ce.Diff_publi, ce.PtsNiveau_publi, ce.CltNiveau_publi, c.Code_comite_dep 
				FROM gickp_Competitions_Equipes ce, gickp_Club c 
				WHERE ce.Code_compet = ? 
				AND ce.Code_saison = ? 
				AND ce.Code_club = c.Code ";	 
				if ($typeClt == 'CP') {
					$sql2 .= "ORDER BY CltNiveau_publi Asc, Diff_publi Desc ";
				} else {
					$sql2 .= "ORDER BY Clt_publi Asc, Diff_publi Desc ";
				}
			$result2 = $myBdd->pdo->prepare($sql2);
			$result2->execute(array($codeCompet1, $codeSaison));
			while ($row2 = $result2->fetch()) {
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
		$this->m_tpl->assign('arrayEquipe_publi', $arrayEquipe_publi);
		$this->m_tpl->assign('recordCompetition', $recordCompetition);
		$this->m_tpl->assign('typeClt', $typeClt);
		$this->m_tpl->assign('arraySaisons', $arraySaisons);
		$this->m_tpl->assign('arrayCompets', $arrayCompets);
		$this->m_tpl->assign('arrayClts', $arrayClts);

	}
	
	
	// Historique		
	function __construct()
	{			
		MyPage::MyPage();
		
		$this->SetTemplate("Historique", "Historique", true);
		$this->Load();
		//$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplateNew('kphistorique');
	}
}		  	

$page = new Historique();

