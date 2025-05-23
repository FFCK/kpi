<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

// Gestion des Classements
	
class Classements extends MyPage	 
{	
	function Load()
	{
		$myBdd = new MyBdd();
		
		$codeCompetGroup = utyGetSession('codeCompetGroup', 'N1H');
		$codeCompetGroup = utyGetPost('codeCompetGroup', $codeCompetGroup);
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
			FROM kp_saison 
            WHERE Code > '1900' 
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
		$recordCompetition = array();
		$arrayEquipe_publi = array();
		$existMatch = 0;
		$typeClt = array();
		$filtreCompetition = utyGetFiltreCompetition('');
		$sql = "SELECT * 
			FROM kp_competition 
			WHERE Code_saison = ? 
			$filtreCompetition 
			AND Publication='O' 
			AND Code_ref = ? 
			ORDER BY Code_niveau, COALESCE(Code_ref, 'z'), Code_tour DESC, GroupOrder, Code";	 
        $arrayCompetition = array();
        $result = $myBdd->pdo->prepare($sql);
        $result->execute(array($codeSaison, $codeCompetGroup));
        while ($row = $result->fetch()) {
            $row['codeCompet'] = $row["Code"];
            $row['libelleCompet'] = $row["Libelle"];
			array_push($arrayCompetition, $row );
			$codeCompet1 = $row['Code'];
			$codeSaison = $row['Code_saison'];
			$typeClt = $row['Code_typeclt'];
			$Statut = $row['Statut'];
            if($row['BandeauLink'] != '' && strpos($row['BandeauLink'], 'http') === FALSE ){
                $row['BandeauLink'] = 'img/logo/' . $row['BandeauLink'];
                if(!is_file($row['BandeauLink'])) {
                    $row['BandeauLink'] = '';
                }
            }
            if($row['LogoLink'] != '' && strpos($row['LogoLink'], 'http') === FALSE ){
                $row['LogoLink'] = 'img/logo/' . $row['LogoLink'];
                if(!is_file($row['LogoLink'])) {
                    $row['LogoLink'] = '';
                }
            }
			if ($row['Web'] != '' || $row['LogoLink'] != '' || $row['BandeauLink'] != '')
			{
				$recordCompetition[] = array( 'ToutGroup' => $row['ToutGroup'], 'Web' => $row['Web'], 'BandeauLink' => $row['BandeauLink'], 'LogoLink' => $row['LogoLink'] );
			}
			//Existence de matchs
			$sql2 = "SELECT m.Id 
				FROM kp_match m, kp_journee j 
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
				FROM kp_competition_equipe ce, kp_club c 
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
                    $arrayEquipe_publi[$codeCompet1][] = array( 'CodeCompet' => $row['Code'], 'CodeSaison' => $row['Code_saison'], 'LibelleCompet' => $row['Libelle'], 'Code_typeclt' => $row['Code_typeclt'],
						'Code_tour' => $row['Code_tour'], 'Qualifies' => $row['Qualifies'], 'Elimines' => $row['Elimines'], 'Nb_equipes' => $row['Nb_equipes'],
						'Statut' => $Statut, 'Code_niveau' => $row['Code_niveau'], 'Soustitre' => $row['Soustitre'], 'Soustitre2' => $row['Soustitre2'], 'Web' => $row['Web'], 'LogoLink' => $row['LogoLink'], 'ToutGroup' => $row['ToutGroup'],
						'Id' => $row2['Id'], 'Numero' => $row2['Numero'], 'Libelle' => $row2['Libelle'], 'Code_club' => $row2['Code_club'], 'Code_comite_dep' => $row2['Code_comite_dep'],
						'Clt' => $row2['Clt_publi'], 'Pts' => $row2['Pts_publi'], 'existMatch' => $existMatch,
						'J' => $row2['J_publi'], 'G' => $row2['G_publi'], 'N' => $row2['N_publi'], 
						'P' => $row2['P_publi'], 'F' => $row2['F_publi'], 'Plus' => $row2['Plus_publi'], 
						'Moins' => $row2['Moins_publi'], 'Diff' => $row2['Diff_publi'],
						'PtsNiveau' => $row2['PtsNiveau_publi'], 'CltNiveau' => $row2['CltNiveau_publi'],
						'logo' => $logo, 'club' => $club );
				}
			}
		
		}
		$this->m_tpl->assign('arrayCompetition', $arrayCompetition);
		$this->m_tpl->assign('arrayEquipe_publi', $arrayEquipe_publi);
		$this->m_tpl->assign('recordCompetition', $recordCompetition);
		$this->m_tpl->assign('typeClt', $typeClt);

	}
	

	// Classements		
	function __construct()
	{			
		parent::__construct();
		
		$this->SetTemplate("Classements", "Classements", true);
		$this->Load();
		//$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplateNew('kpclassements');
	}
}		  	

$page = new Classements();
