<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion des Classements

class GestionClassement extends MyPageSecure
{	
	var $myBdd;

	function Load()
	{
		$myBdd = $this->myBdd;
		
		$codeSaison = $myBdd->GetActiveSaison();
		$saisonActive = $myBdd->GetActiveSaison();
		
		$codeCompet = utyGetSession('codeCompet');
		$codeCompet = utyGetPost('codeCompet', $codeCompet);
        $compet = $myBdd->GetCompetition($codeCompet, $codeSaison);
		$this->m_tpl->assign('compet', $compet);
		
		$codeSaisonTransfert = utyGetSession('codeSaisonTransfert',$saisonActive);
		$codeSaisonTransfert = utyGetPost('codeSaisonTransfert', $codeSaisonTransfert);
		$_SESSION['codeSaisonTransfert'] = $codeSaisonTransfert;
		$this->m_tpl->assign('codeSaisonTransfert', $codeSaisonTransfert);

		$codeCompetTransfert = utyGetSession('codeCompetTransfert',$codeCompet);
		$codeCompetTransfert = utyGetPost('codeCompetTransfert', $codeCompetTransfert);
		$_SESSION['codeCompetTransfert'] = $codeCompetTransfert;
		$this->m_tpl->assign('codeCompetTransfert', $codeCompetTransfert);

        //Filtre affichage type compet
		$AfficheCompet = utyGetSession('AfficheCompet','');
		$AfficheCompet = utyGetPost('AfficheCompet', $AfficheCompet);
        $_SESSION['AfficheCompet'] = $AfficheCompet;
		$this->m_tpl->assign('AfficheCompet', $AfficheCompet);

		$_SESSION['updatecell_tableName'] = 'gickp_Competitions_Equipes';
		$_SESSION['updatecell_tableName2'] = 'gickp_Competitions_Equipes_Journee';
		$_SESSION['updatecell_where'] = 'Where Id = ';
		$_SESSION['updatecell_where2'] = 'Where Id = ';
		$_SESSION['updatecell_and'] = 'And Id_journee = ';
		$_SESSION['updatecell_document'] = 'formClassement';
		
		// Chargement des Saisons ...
		$arraySaison = array();
		$sql = "SELECT Code, Etat, Nat_debut, Nat_fin, Inter_debut, Inter_fin 
			FROM gickp_Saison 
			ORDER BY Code DESC ";
		$result = $myBdd->pdo->query($sql);
		$resultarray = $result->fetchAll(PDO::FETCH_ASSOC);
		foreach ($resultarray as $key => $row) {
			array_push($arraySaison, array('Code' => $row['Code'], 
				'Etat' => $row['Etat'], 
				'Nat_debut' => utyDateUsToFr($row['Nat_debut']), 
				'Nat_fin' => utyDateUsToFr($row['Nat_fin']), 
				'Inter_debut' => utyDateUsToFr($row['Inter_debut']), 
				'Inter_fin' => utyDateUsToFr($row['Inter_fin']) ));
		}
		
		$this->m_tpl->assign('arraySaison', $arraySaison);
		$this->m_tpl->assign('sessionSaison', $codeSaison);

		// Chargement des Compétitions ...
		$sqlFiltreCompetition = utyGetFiltreCompetition('c.');
		if ($AfficheCompet == 'N') {
            $sqlAfficheCompet = " And c.Code Like 'N%' ";
        } elseif ($AfficheCompet == 'CF') {
            $sqlAfficheCompet = " And c.Code Like 'CF%' ";
        } elseif ($AfficheCompet == 'M') {
            $sqlAfficheCompet = " And c.Code_ref = 'M' ";
        } elseif($AfficheCompet > 0) {
            $sqlAfficheCompet = " And g.section = '" . $AfficheCompet . "' ";
		} else {
			$sqlAfficheCompet = '';
		}
		$sql = "SELECT c.Code_niveau, c.Code_ref, c.Code_tour, c.Code, c.Libelle, c.Soustitre, 
			c.Soustitre2, c.Titre_actif, g.section, g.ordre 
			FROM gickp_Competitions c, gickp_Competitions_Groupes g 
			WHERE c.Code_saison = ? 
			$sqlFiltreCompetition 
			$sqlAfficheCompet 
			AND c.Code_niveau LIKE ? 
			AND c.Code_ref = g.Groupe 
			ORDER BY c.Code_saison, g.section, g.ordre, 
			COALESCE(c.Code_ref, 'z'), c.Code_tour, c.GroupOrder, c.Code";	 
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($codeSaison, utyGetSession('AfficheNiveau').'%'));
		$arrayCompetition = array();
		$arrayCompetitionTransfert = array();
        $i = -1;
        $j = '';
        $label = $myBdd->getSections();
		while ($row = $result->fetch()) {
			// Titre
			if ($row["Titre_actif"] != 'O' && $row["Soustitre"] != '') {
                $Libelle = $row["Soustitre"];
            } else {
                $Libelle = $row["Libelle"];
            }
            if ($row["Soustitre2"] != '') {
                $Libelle .= ' - ' . $row["Soustitre2"];
            }

            if ((strlen($codeCompet) == 0) && ($i == 0)) {
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
		$sqlFiltreCompetition = utyGetFiltreCompetition('');
		$sql = "SELECT Code, Libelle 
			FROM gickp_Competitions 
			WHERE Code_saison = ? 
			$sqlFiltreCompetition 
			ORDER BY Code ";	 
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($codeSaisonTransfert));
		while ($row = $result->fetch()) {
			if (strlen($codeCompet) == 0 && $i == 0) {
				$codeCompet = $row["Code"];
			}
			
			if ($row["Code"] == $codeCompet) {
				array_push($arrayCompetitionTransfert, array($row["Code"], $row["Code"]." - ".$row["Libelle"], "SELECTED" ) );
			} else {
				array_push($arrayCompetitionTransfert, array($row["Code"], $row["Code"]." - ".$row["Libelle"], "" ) );
			}
		}
		$this->m_tpl->assign('arrayCompetitionTransfert', $arrayCompetitionTransfert);
		
		// Chargement des Saisons ...
		foreach ($resultarray as $key => $row) {
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
		
		if (strlen($codeCompet) > 0) {
			if (utyGetPost('ParamCmd', '') == 'changeCompetition') {
				$typeClt = $this->GetTypeClt($codeCompet, $codeSaison);
			} else {
				$typeClt = utyGetPost('orderCompet', '');
				if ($typeClt == '') {
					$typeClt = $this->GetTypeClt($codeCompet, $codeSaison);
				}
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
			$sql = "SELECT ce.Id, ce.Libelle, ce.Code_club, ce.Clt, ce.Pts, 
				ce.J, ce.G, ce.N, ce.P, ce.F, ce.Plus, ce.Moins, ce.Diff, ce.PtsNiveau, 
				ce.CltNiveau, c.Code_comite_dep 
				FROM gickp_Competitions_Equipes ce, gickp_Club c 
				WHERE ce.Code_compet = ? 
				AND ce.Code_saison = ? 
				AND ce.Code_club = c.Code ";	 
			if ($typeClt == 'CP') {
				$sql .= "ORDER BY ce.CltNiveau Asc, ce.Diff Desc, ce.Libelle ";	 
			} else {
				$sql .= "ORDER BY ce.Clt Asc, ce.Diff Desc, ce.Libelle ";	 
			}
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($codeCompet, $codeSaison));
			while ($row = $result->fetch()) {
					if (strlen($row['Code_comite_dep']) > 3) {
					$row['Code_comite_dep'] = 'FRA';
				}
				array_push($arrayEquipe, $row);
			}
			
			// Classement public				
			$sql = "SELECT ce.Id, ce.Libelle, ce.Code_club, ce.Clt_publi, ce.Pts_publi, 
				ce.J_publi, ce.G_publi, ce.N_publi, ce.P_publi, ce.F_publi, ce.Plus_publi, 
				ce.Moins_publi, ce.Diff_publi, ce.PtsNiveau_publi, ce.CltNiveau_publi, 
				c.Code_comite_dep 
				FROM gickp_Competitions_Equipes ce, gickp_Club c 
				WHERE ce.Code_compet = ? 
				AND ce.Code_saison = ? 
				AND ce.Code_club = c.Code ";	 
			if ($typeClt == 'CP') {
				$sql .= "Order By CltNiveau_publi Asc, Diff_publi Desc ";	 
			} else {
				$sql .= "Order By Clt_publi Asc, Diff_publi Desc ";	 
			}
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($codeCompet, $codeSaison));
			while ($row = $result->fetch()) {
				if (strlen($row['Code_comite_dep']) > 3) {
					$row['Code_comite_dep'] = 'FRA';
				}
				array_push($arrayEquipe_publi, $row);
                
				if (($typeClt == 'CHPT' && $row['Clt_publi'] == 0) || ($typeClt == 'CP' && $row['CltNiveau_publi'] == 0)) {
					$recordCompetition['Qualifies']	= 0;
					$recordCompetition['Elimines'] = 0;
				}
			}
			
			if ($typeClt == 'CP') {
				// Classement actuel par journée/phase
				$sql = "SELECT a.Id, a.Libelle, a.Code_club, b.Id_journee, b.Clt, b.Pts, 
					b.J, b.G, b.N, b.P, b.F, b.Plus, b.Moins, b.Diff, b.PtsNiveau, b.CltNiveau, 
					c.Phase, c.Niveau, c.Lieu, c.Type 
					FROM gickp_Competitions_Equipes a, gickp_Competitions_Equipes_Journee b 
					JOIN gickp_Journees c ON (b.Id_journee = c.Id) 
					WHERE a.Id = b.Id 
					AND c.Code_competition = ? 
					AND c.Code_saison = ? 
					ORDER BY c.Niveau DESC, c.Phase, c.Date_debut, c.Lieu, b.Clt, b.Diff DESC, b.Plus ";	 
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array($codeCompet, $codeSaison));
				while ($row = $result->fetch()) {
					array_push($arrayEquipe_journee, $row);
				}

				// Classement public par journée/phase
				$sql = "SELECT a.Id, a.Libelle, a.Code_club, b.Id_journee, b.Clt_publi, 
					b.Pts_publi, b.J_publi, b.G_publi, b.N_publi, b.P_publi, b.F_publi, 
					b.Plus_publi, b.Moins_publi, b.Diff_publi, b.PtsNiveau_publi, 
					b.CltNiveau_publi, c.Phase, c.Niveau, c.Lieu, c.Type 
					FROM gickp_Competitions_Equipes a, gickp_Competitions_Equipes_Journee b 
					JOIN gickp_Journees c ON (b.Id_journee = c.Id) 
					WHERE a.Id = b.Id 
					AND c.Code_competition = ? 
					AND c.Code_saison = ? 
					ORDER BY c.Niveau DESC, c.Phase, c.Date_debut, c.Lieu, b.Clt_publi, 
						b.Diff_publi DESC, b.Plus_publi ";	 
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array($codeCompet, $codeSaison));
				while ($row = $result->fetch()) {
					array_push($arrayEquipe_journee_publi, $row);
				}

			}
		}	
		$this->m_tpl->assign('arrayEquipe', $arrayEquipe);
		$this->m_tpl->assign('arrayEquipe_journee', $arrayEquipe_journee);
		$this->m_tpl->assign('arrayEquipe_journee_publi', $arrayEquipe_journee_publi);
		$this->m_tpl->assign('arrayEquipe_publi', $arrayEquipe_publi);
		if (!isset($recordCompetition['Qualifies'])) {
            $recordCompetition['Qualifies'] = 0;
        }
        if (!isset($recordCompetition['Elimines'])) {
            $recordCompetition['Elimines'] = 0;
        }
        $this->m_tpl->assign('Qualifies_publi', $recordCompetition['Qualifies']);
		$this->m_tpl->assign('Elimines_publi', $recordCompetition['Elimines']);
		
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
	
	function GetTypeClt($codeCompet,  $codeSaison)
	{
		if (strlen($codeCompet) == 0) {
            return 'CHPT';
        }

        $myBdd = $this->myBdd;
		
		$recordCompetition = $myBdd->GetCompetition($codeCompet, $codeSaison);
		$typeClt = $recordCompetition['Code_typeclt'];
		if ($typeClt != 'CP') {
            $typeClt = 'CHPT';
        }

        return $typeClt;
	}
	
	function DoClassement()
	{
		$myBdd = $this->myBdd;

		$codeSaison = $myBdd->GetActiveSaison();
		$codeCompet = utyGetPost('codeCompet');
		$_SESSION['codeCompet'] = $codeCompet;
		$allMatchs = utyGetPost('allMatchs');
		if ($allMatchs == 'ok') {
            $tousLesMatchs = 'tous';
        } else {
            $tousLesMatchs = '';
        }

        // Recherche du type de Classement & Goal Average lié à cette compétition
		$typeClt = '';
		$goalaverage = '';
		
		$sql = "SELECT Code_typeclt, goalaverage 
			FROM gickp_Competitions 
			WHERE Code = ? 
			AND Code_saison = ? ";	 
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($codeCompet, $codeSaison));
	
		if ($result->rowCount() == 1) {
			$row = $result->fetch();	  
			$typeClt = $row['Code_typeclt'];
			$goalaverage = $row['goalaverage'];
		}
		
		$this->RazClassementCompetitionEquipe($codeCompet, $codeSaison);
		$this->InitClassementCompetitionEquipe($codeCompet, $codeSaison);
		
		$this->RazClassementCompetitionEquipeNiveau($codeCompet, $codeSaison);
		$this->RazClassementCompetitionEquipeJournee($codeCompet, $codeSaison);
		
		$this->CalculClassement($codeCompet, $typeClt, $tousLesMatchs);
		
		$egalites = $this->FinalisationClassementChpt($codeCompet, $codeSaison, $goalaverage, $tousLesMatchs);
		$this->FinalisationClassementNiveau($codeCompet, $codeSaison);
		
		$this->FinalisationClassementNiveauChpt($codeCompet, $codeSaison);
		$this->FinalisationClassementNiveauNiveau($codeCompet, $codeSaison);
		
		$this->FinalisationClassementJourneeChpt($codeCompet, $codeSaison, $goalaverage, $tousLesMatchs);
		$this->FinalisationClassementJourneeNiveau($codeCompet, $codeSaison);
	
		$sql = "UPDATE gickp_Competitions 
			SET Date_calcul = ?, 
			Code_uti_calcul = ?, 
			Mode_calcul = ? 
			WHERE Code = ? 
			AND Code_saison = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array(date('Y-m-d H:i:s'), utyGetSession('User'), $tousLesMatchs, $codeCompet, $codeSaison));

		($tousLesMatchs == 'tous') ? $lesMatchs = 'Inclu matchs non verrouillés' : $lesMatchs = 'Uniquement matchs verrouillés';
		$myBdd->utyJournal('Calcul Classement', $codeSaison, $codeCompet, 'NULL', 'NULL', 'NULL', $lesMatchs);
        
        if ($egalites > 1 && $typeClt == 'CHPT') {
            return "Attention : $egalites équipes ou plus sont à égalité. Vérifiez si nécessaire la différence de but particulière !";
        } elseif ($egalites > 1) {
			die($egalites);
            return $egalites;
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
		$myBdd = $this->myBdd;
		
		$sql = "UPDATE gickp_Competitions_Equipes 
			SET Clt=1, Pts=0, J=0, G=0, N=0, P=0, F=0, Plus=0, Moins=0, Diff=0, 
			CltNiveau = 1, PtsNiveau = 0 
			WHERE Code_compet = ? 
			AND Code_saison = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($codeCompet, $codeSaison));
	}
		
	function InitClassementCompetitionEquipe($codeCompet, $codeSaison)
	{
		$myBdd = $this->myBdd;
		
		$sql = "UPDATE gickp_Competitions_Equipes a, gickp_Competitions_Equipes_Init b 
			SET a.Clt=b.Clt, a.Pts=b.Pts*100, a.J=b.J, a.G=b.G, a.N=b.N, a.P=b.P, a.F=b.F, 
			a.Plus=b.plus, a.Moins=b.Moins, a.Diff=b.Diff 
			WHERE a.Id = b.Id 
			AND a.Code_compet = ? 
			AND a.Code_saison = ? ";	 
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($codeCompet, $codeSaison));
	}
	
	function RazClassementCompetitionEquipeNiveau($codeCompet, $codeSaison)
	{
		$myBdd = $this->myBdd;
		
		$sql = "UPDATE gickp_Competitions_Equipes_Niveau a, gickp_Competitions_Equipes b 
			SET a.Clt=1, a.Pts=0, a.J=0, a.G=0, a.N=0, a.P=0, a.F=0, 
			a.Plus=0, a.Moins=0, a.Diff=0, a.PtsNiveau=0, a.CltNiveau=0 
			WHERE a.Id = b.Id 
			AND b.Code_compet = ? 
			AND b.Code_saison = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($codeCompet, $codeSaison));
	}
	
	function RazClassementCompetitionEquipeJournee($codeCompet, $codeSaison)
	{
		$myBdd = $this->myBdd;
		
		$sql = "UPDATE gickp_Competitions_Equipes_Journee a 
			RIGHT OUTER JOIN gickp_Journees b ON a.Id_journee = b.Id 
			SET a.Clt=0, a.Pts=0, a.J=0, a.G=0, a.N=0, a.P=0, a.F=0, 
			a.Plus=0, a.Moins=0, a.Diff=0, a.PtsNiveau=0, a.CltNiveau=0 
			WHERE b.Code_competition = ? 
			AND b.Code_saison = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($codeCompet, $codeSaison));
	}

	
	function CalculClassement($codeCompet, $typeClt, $tousLesMatchs=false)
	{
		$this->CalculClassementJournee($codeCompet, $typeClt, $tousLesMatchs);
	}

	function CalculClassementJournee($codeCompet, $typeClt, $tousLesMatchs=false)
	{
		$myBdd = $this->myBdd;
		$codeSaison = $myBdd->GetActiveSaison();
		if (!$tousLesMatchs) { //uniquement les matchs validés (vérouillés)
			$sqlValidation = "AND a.Validation = 'O' ";
		} else {
			$sqlValidation = "";
		}
		$sql = "SELECT a.Id_equipeA, a.ScoreA, a.Id_equipeB, a.ScoreB, a.CoeffA, 
				a.CoeffB, a.Id, a.Id_journee, b.Niveau, c.Points 
				FROM gickp_Matchs a, gickp_Journees b, gickp_Competitions c 
				WHERE a.Id_journee = b.Id 
				AND b.Code_competition = ? 
				AND b.Code_competition = c.Code 
				AND b.Code_saison = ? 
				AND b.Code_saison = c.Code_saison 
				$sqlValidation 
				ORDER BY b.Id ";	 
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($codeCompet, $codeSaison));
		while ($row = $result->fetch()) {
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
			
			$idEquipeA = $row['Id_equipeA'];
			$idEquipeB = $row['Id_equipeB'];
			
			if (!utyIsScoreOk($scoreA, $scoreB)) {
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
		
		if ( ($scoreA != 'F') && ($scoreB != 'F') ) {
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
							
			// Victoire Equipe A et Défaite Equipe B ...
			if ($scoreA > $scoreB) {
				$arrayCltA['Pts'] = (int) ($ptsV * $coeffA);
				$arrayCltA['G'] = 1;
				$arrayCltA['PtsNiveau'] = (double) pow(64, $niveau) * $ptsNiveauV;

				$arrayCltB['Pts'] = (int) ($ptsP * $coeffB);
				$arrayCltB['P'] = 1;
				$arrayCltB['PtsNiveau'] = (double) pow(64, $niveau)*$ptsNiveauP;
				return;
			}
			
			// Victoire Equipe B et Défaite Equipe A ...
			if ($scoreB > $scoreA) {
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
			if ($bScoreOk) {
				$arrayCltA['N'] = 1;
				$arrayCltA['PtsNiveau'] = (double) pow(64, $niveau)*$ptsNiveauN;
			} else {
				$arrayCltA['PtsNiveau'] = (double) pow(64, $niveau)*$ptsNiveauNonjoue;
			}
			
			$arrayCltB['Pts'] = (int) ($ptsN * $coeffB);
			if ($bScoreOk) {
				$arrayCltB['PtsNiveau'] = (double) pow(64, $niveau) * $ptsNiveauN;
				$arrayCltB['N'] = 1;
			} else {
				$arrayCltB['PtsNiveau'] = (double) pow(64, $niveau) * $ptsNiveauNonjoue;
			}

            return;
		}
		 
		// Au moins une Equipe Forfait ...
		if ( ($scoreA != 'F') && ($scoreB == 'F') ) {
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
		 
		if ( ($scoreA == 'F') && ($scoreB != 'F') ) {
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

		if ( ($scoreA == 'F') && ($scoreB == 'F') ) {
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
		$myBdd = $this->myBdd;
	
		$sql = "SELECT COUNT(*) Nb 
			FROM gickp_Competitions_Equipes_Niveau 
			WHERE Id = ? 
			AND Niveau = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($idEquipe, $niveau));
		
		if ($result->rowCount() == 1) {
			$row = $result->fetch();	 
			if ($row['Nb'] == 1) {
				return; // Le record existe ...
			}
		}

		$sql = "INSERT INTO gickp_Competitions_Equipes_Niveau 
			(Id, Niveau, Pts, Clt, J, G, N, P, F, Plus, Moins, Diff, PtsNiveau, CltNiveau) 
			VALUES (?, ?, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0) ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($idEquipe, $niveau));
	}

	// ExistCompetitionEquipeJournee
	function ExistCompetitionEquipeJournee($idEquipe, $idJournee)
	{
		$myBdd = $this->myBdd;
		
		$sql = "SELECT COUNT(*) Nb 
			FROM gickp_Competitions_Equipes_Journee 
			WHERE Id = ? 
			AND Id_journee = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($idEquipe, $idJournee));
		
		if ($result->rowCount() == 1) {
			$row = $result->fetch();	 
			if ($row['Nb'] == 1)
				return; // Le record existe ...
		}

		$sql = "INSERT INTO gickp_Competitions_Equipes_Journee 
			(Id, Id_journee, Pts, Clt, J, G, N, P, F, Plus, Moins, Diff, PtsNiveau, CltNiveau) 
			VALUES (?, ?, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0) ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($idEquipe, $idJournee));
	}
	
	// StepClassementCompetitionEquipe
	function StepClassementCompetitionEquipe($idEquipeA, $arrayCltA, $idEquipeB, $arrayCltB)
	{
		$myBdd = $this->myBdd;

		$sql = "UPDATE gickp_Competitions_Equipes 
			SET Pts = Pts + ?, 
			J = J + ?, 
			G = G + ?, 
			N = N + ?, 
			P = P + ?, 
			F = F + ?, 
			Plus = Plus + ?, 
			Moins = Moins + ?, 
			Diff = Diff + ?, 
			PtsNiveau = PtsNiveau + ? 
			WHERE Id = ? ";
		$result = $myBdd->pdo->prepare($sql);
		
		// Equipe A ...
		$result->execute(array(
			$arrayCltA['Pts'], $arrayCltA['J'], $arrayCltA['G'], $arrayCltA['N'], 
			$arrayCltA['P'], $arrayCltA['F'], $arrayCltA['Plus'], $arrayCltA['Moins'], 
			$arrayCltA['Diff'], $arrayCltA['PtsNiveau'], $idEquipeA
		));

		// Equipe B ...
		$result->execute(array(
			$arrayCltB['Pts'], $arrayCltB['J'], $arrayCltB['G'], $arrayCltB['N'], 
			$arrayCltB['P'], $arrayCltB['F'], $arrayCltB['Plus'], $arrayCltB['Moins'], 
			$arrayCltB['Diff'], $arrayCltB['PtsNiveau'], $idEquipeB
		));

	}
	
	// StepClassementCompetitionEquipeNiveau
	function StepClassementCompetitionEquipeNiveau($idEquipeA, $arrayCltA, $idEquipeB, $arrayCltB, $niveau)
	{
		$myBdd = $this->myBdd;
		
		$this->ExistCompetitionEquipeNiveau($idEquipeA, $niveau);
		$this->ExistCompetitionEquipeNiveau($idEquipeB, $niveau);
				
		$sql = "UPDATE gickp_Competitions_Equipes_Niveau 
			SET Pts = Pts + ?, 
			J = J + ?, 
			G = G + ?, 
			N = N + ?, 
			P = P + ?, 
			F = F + ?, 
			Plus = Plus + ?, 
			Moins = Moins + ?, 
			Diff = Diff + ?, 
			PtsNiveau = PtsNiveau + ? 
			WHERE Id = ? 
			AND Niveau = ? ";
		$result = $myBdd->pdo->prepare($sql);
		
		// Equipe A ...
		$result->execute(array(
			$arrayCltA['Pts'], $arrayCltA['J'], $arrayCltA['G'], $arrayCltA['N'], 
			$arrayCltA['P'], $arrayCltA['F'], $arrayCltA['Plus'], $arrayCltA['Moins'], 
			$arrayCltA['Diff'], $arrayCltA['PtsNiveau'], $idEquipeA, $niveau
		));

		// Equipe B ...
		$result->execute(array(
			$arrayCltB['Pts'], $arrayCltB['J'], $arrayCltB['G'], $arrayCltB['N'], 
			$arrayCltB['P'], $arrayCltB['F'], $arrayCltB['Plus'], $arrayCltB['Moins'], 
			$arrayCltB['Diff'], $arrayCltB['PtsNiveau'], $idEquipeB, $niveau
		));

	}

	// StepClassementCompetitionEquipeJournee
	function StepClassementCompetitionEquipeJournee($idEquipeA, $arrayCltA, $idEquipeB, $arrayCltB, $idJournee)
	{
		$myBdd = $this->myBdd;
		
		$this->ExistCompetitionEquipeJournee($idEquipeA, $idJournee);
		$this->ExistCompetitionEquipeJournee($idEquipeB, $idJournee);
				
		$sql = "UPDATE gickp_Competitions_Equipes_Journee 
			SET Pts = Pts + ?, 
			J = J + ?, 
			G = G + ?, 
			N = N + ?, 
			P = P + ?, 
			F = F + ?, 
			Plus = Plus + ?, 
			Moins = Moins + ?, 
			Diff = Diff + ?, 
			PtsNiveau = PtsNiveau + ? 
			WHERE Id = ? 
			AND Id_journee = ? ";
		$result = $myBdd->pdo->prepare($sql);
		
		// Equipe A ...
		$result->execute(array(
			$arrayCltA['Pts'], $arrayCltA['J'], $arrayCltA['G'], $arrayCltA['N'], 
			$arrayCltA['P'], $arrayCltA['F'], $arrayCltA['Plus'], $arrayCltA['Moins'], 
			$arrayCltA['Diff'], $arrayCltA['PtsNiveau'], $idEquipeA, $idJournee
		));

		// Equipe B ...
		$result->execute(array(
			$arrayCltB['Pts'], $arrayCltB['J'], $arrayCltB['G'], $arrayCltB['N'], 
			$arrayCltB['P'], $arrayCltB['F'], $arrayCltB['Plus'], $arrayCltB['Moins'], 
			$arrayCltB['Diff'], $arrayCltB['PtsNiveau'], $idEquipeB, $idJournee
		));
		
	}
		
	function FinalisationClassementChpt($codeCompet, $codeSaison, $goalaverage, $tousLesMatchs)
	{
		$myBdd = $this->myBdd;
		
		$oldId = 0;
		$oldClt = 0;
		$oldPts = 0;
		$egalites = 1;
		$i = 0;
		$aEgalites = [];
		
		// Chargement des Equipes par ordre de Pts, Diff & Plus
		$sql = "SELECT Id, Clt, Pts, J, G, N, P, F, Plus, Moins, Diff 
				FROM gickp_Competitions_Equipes 
				WHERE Code_compet = ? 
				AND Code_saison = ? 
				ORDER BY Pts DESC, Diff DESC, Plus DESC ";	 
		$result2 = $myBdd->pdo->prepare($sql);
		$result2->execute(array($codeCompet, $codeSaison));
		while ($row = $result2->fetch()) {
			if ($goalaverage == 'gen' && $row['Pts'] == $oldPts) {
					// SI ON VEUT SIGNALER LES EGALITES SANS LES TRAITER (Goal Average Général)
				// $clt = $oldClt;
				// $egalites ++;
					// SINON
				$clt = $i + 1;
			} elseif ($goalaverage != 'gen' && $row['Pts'] == $oldPts) {
				$clt = $oldClt;
				if (!isset($aEgalites[$clt]) || !in_array($oldId, $aEgalites[$clt])) {
					$aEgalites[$clt][$oldId] = $oldId;
				}
				$aEgalites[$clt][$row['Id']] = $row['Id'];
            } else {
                $clt = $i + 1;
            }
			
			$oldClt = $clt;
			$oldId =$row['Id'];
			$oldPts = $row['Pts'];

            $sql = "UPDATE gickp_Competitions_Equipes 
				SET Clt = ?
				WHERE Id = ? ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($clt, $row['Id']));
			$i ++;
		}
		if (count($aEgalites) > 0) {
			$this->GestionEgalitesClassementChpt($aEgalites, $codeCompet, $codeSaison, $tousLesMatchs);
		}

        return $egalites;
	}
	
	/**
	 * Egalités goal average particulier
	 */
	function GestionEgalitesClassementChpt($aEgalites, $codeCompet, $codeSaison, $tousLesMatchs)
	{
		$myBdd = $this->myBdd;
		$rEgalites = [];
		// Pour chaque égalité, sélection des matchs impliquant les équipes concernées
		foreach ($aEgalites as $clt => $teams) {
			$listTeams = implode(',', $teams);
			if (!$tousLesMatchs) { // uniquement les matchs validés (vérouillés)
				$sqlValidation = "AND a.Validation = 'O' ";
			} else {
				$sqlValidation = "";
			}
			$sql = "SELECT a.Id_equipeA, a.ScoreA, a.Id_equipeB, a.ScoreB, a.CoeffA, 
				a.CoeffB, a.Id Idmatch, a.Id_journee, b.Niveau, c.Points 
				FROM gickp_Matchs a, gickp_Journees b, gickp_Competitions c 
				WHERE a.Id_journee = b.Id 
				AND b.Code_competition = c.Code 
				AND b.Code_saison = c.Code_saison 
				AND b.Code_competition = ? 
				AND b.Code_saison = ? 
				AND a.Id_equipeA IN ($listTeams) 
				AND a.Id_equipeB IN ($listTeams) 
				$sqlValidation 
				ORDER BY b.Id, a.Id ";
			
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($codeCompet, $codeSaison));
			while ($row = $result->fetch()) {
				$listmatchs[] = $row['Idmatch'];
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
				
				$idEquipeA = $row['Id_equipeA'];
				$idEquipeB = $row['Id_equipeB'];
				
				if (!utyIsScoreOk($scoreA, $scoreB)) {
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
				
				$rEgalites[$clt][$idEquipeA]['Pts'] = $rEgalites[$clt][$idEquipeA]['Pts'] ?? 0;
				$rEgalites[$clt][$idEquipeA]['J'] = $rEgalites[$clt][$idEquipeA]['J'] ?? 0;
				$rEgalites[$clt][$idEquipeA]['Plus'] = $rEgalites[$clt][$idEquipeA]['Plus'] ?? 0;
				$rEgalites[$clt][$idEquipeA]['Diff'] = $rEgalites[$clt][$idEquipeA]['Diff'] ?? 0;
				$rEgalites[$clt][$idEquipeB]['Pts'] = $rEgalites[$clt][$idEquipeB]['Pts'] ?? 0;
				$rEgalites[$clt][$idEquipeB]['J'] = $rEgalites[$clt][$idEquipeB]['J'] ?? 0;
				$rEgalites[$clt][$idEquipeB]['Plus'] = $rEgalites[$clt][$idEquipeB]['Plus'] ?? 0;
				$rEgalites[$clt][$idEquipeB]['Diff'] = $rEgalites[$clt][$idEquipeB]['Diff'] ?? 0;


				$niveau = $row['Niveau'];
				if (strlen($niveau) == 0) {
					$niveau = 0;
				}

				$Points = $row['Points'];
				
				$this->SetArrayClt($scoreA, $scoreB, $niveau, $arrayCltA, $arrayCltB, $coeffA, $coeffB, $Points);
				
				$rEgalites[$clt][$idEquipeA]['Pts'] = $rEgalites[$clt][$idEquipeA]['Pts'] + $arrayCltA['Pts'];
				$rEgalites[$clt][$idEquipeA]['J'] = $rEgalites[$clt][$idEquipeA]['J'] + $arrayCltA['J'];
				$rEgalites[$clt][$idEquipeA]['Plus'] = $rEgalites[$clt][$idEquipeA]['Plus'] + $arrayCltA['Plus'];
				$rEgalites[$clt][$idEquipeA]['Diff'] = $rEgalites[$clt][$idEquipeA]['Diff'] + $arrayCltA['Diff'];
				$rEgalites[$clt][$idEquipeB]['Pts'] = $rEgalites[$clt][$idEquipeB]['Pts'] + $arrayCltB['Pts'];
				$rEgalites[$clt][$idEquipeB]['J'] = $rEgalites[$clt][$idEquipeB]['J'] + $arrayCltB['J'];
				$rEgalites[$clt][$idEquipeB]['Plus'] = $rEgalites[$clt][$idEquipeB]['Plus'] + $arrayCltB['Plus'];
				$rEgalites[$clt][$idEquipeB]['Diff'] = $rEgalites[$clt][$idEquipeB]['Diff'] + $arrayCltB['Diff'];
			}


			foreach ($rEgalites[$clt] as $team => $team_value) {
				$arrayCltGlobal[$clt][] = [
					'clt' => $clt,
					'team' => $team,
					'Pts' => $team_value['Pts'],
					'Plus' => $team_value['Plus'],
					'Diff' => $team_value['Diff']
				];
			}

			// Tri sur plusieurs colonnes, façon BDD
			// Ajoute $data en tant que dernier paramètre, 
			// pour trier par la clé commune
			array_multisort(
				array_column($arrayCltGlobal[$clt], 'Pts'), SORT_DESC, 
				array_column($arrayCltGlobal[$clt], 'Diff'), SORT_DESC, 
				array_column($arrayCltGlobal[$clt], 'Plus'), SORT_DESC, 
				$arrayCltGlobal[$clt]
			);
			print_r($arrayCltGlobal[$clt]);

			$sql = "UPDATE gickp_Competitions_Equipes 
				SET Clt = ?
				WHERE Id = ? ";
			$result = $myBdd->pdo->prepare($sql);

			// incrémentation de 1 classement à partir du 2ème
			for ($i = 1; $i < count($arrayCltGlobal[$clt]); $i ++) {
				$arrayCltGlobal[$clt][$i]['clt'] += $i;

				// update BDD pour la journée ou global si journée = false
				$result->execute(array(
					$arrayCltGlobal[$clt][$i]['clt'], 
					$arrayCltGlobal[$clt][$i]['team']
				));
			}

		}
		
	}

	/**
	 * Egalités goal average particulier
	 */
	function GestionEgalitesClassementJourneeChpt($aEgalites, $codeCompet, $codeSaison, $tousLesMatchs)
	{
		$myBdd = $this->myBdd;
		$rEgalites = [];
		// Pour chaque poule
		foreach ($aEgalites as $journee => $clts) {
			// Pour chaque égalité, sélection des matchs impliquant les équipes concernées
			foreach ($clts as $clt => $teams) {
				$listTeams = implode(',', $teams);
				if (!$tousLesMatchs) { // uniquement les matchs validés (vérouillés)
					$sqlValidation = "AND a.Validation = 'O' ";
				} else {
					$sqlValidation = "";
				}
				$sql = "SELECT a.Id_equipeA, a.ScoreA, a.Id_equipeB, a.ScoreB, a.CoeffA, 
					a.CoeffB, a.Id Idmatch, a.Id_journee, b.Niveau, c.Points 
					FROM gickp_Matchs a, gickp_Journees b, gickp_Competitions c 
					WHERE a.Id_journee = b.Id 
					AND b.Code_competition = c.Code 
					AND b.Code_saison = c.Code_saison 
					AND b.Id = ?
					AND a.Id_equipeA IN ($listTeams) 
					AND a.Id_equipeB IN ($listTeams) 
					$sqlValidation 
					ORDER BY b.Id, a.Id ";
				
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array($journee));
				while ($row = $result->fetch()) {
					$listmatchs[] = $row['Idmatch'];
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
					
					$idEquipeA = $row['Id_equipeA'];
					$idEquipeB = $row['Id_equipeB'];
					
					if (!utyIsScoreOk($scoreA, $scoreB)) {	
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
					
					$rEgalites[$clt][$idEquipeA]['Pts'] = $rEgalites[$clt][$idEquipeA]['Pts'] ?? 0;
					$rEgalites[$clt][$idEquipeA]['J'] = $rEgalites[$clt][$idEquipeA]['J'] ?? 0;
					$rEgalites[$clt][$idEquipeA]['Plus'] = $rEgalites[$clt][$idEquipeA]['Plus'] ?? 0;
					$rEgalites[$clt][$idEquipeA]['Diff'] = $rEgalites[$clt][$idEquipeA]['Diff'] ?? 0;
					$rEgalites[$clt][$idEquipeB]['Pts'] = $rEgalites[$clt][$idEquipeB]['Pts'] ?? 0;
					$rEgalites[$clt][$idEquipeB]['J'] = $rEgalites[$clt][$idEquipeB]['J'] ?? 0;
					$rEgalites[$clt][$idEquipeB]['Plus'] = $rEgalites[$clt][$idEquipeB]['Plus'] ?? 0;
					$rEgalites[$clt][$idEquipeB]['Diff'] = $rEgalites[$clt][$idEquipeB]['Diff'] ?? 0;
	
	
					$niveau = $row['Niveau'];
					if (strlen($niveau) == 0) {
						$niveau = 0;
					}
	
					$Points = $row['Points'];
					
					$this->SetArrayClt($scoreA, $scoreB, $niveau, $arrayCltA, $arrayCltB, $coeffA, $coeffB, $Points);
					
					$rEgalites[$clt][$idEquipeA]['Pts'] = $rEgalites[$clt][$idEquipeA]['Pts'] + $arrayCltA['Pts'];
					$rEgalites[$clt][$idEquipeA]['J'] = $rEgalites[$clt][$idEquipeA]['J'] + $arrayCltA['J'];
					$rEgalites[$clt][$idEquipeA]['Plus'] = $rEgalites[$clt][$idEquipeA]['Plus'] + $arrayCltA['Plus'];
					$rEgalites[$clt][$idEquipeA]['Diff'] = $rEgalites[$clt][$idEquipeA]['Diff'] + $arrayCltA['Diff'];
					$rEgalites[$clt][$idEquipeB]['Pts'] = $rEgalites[$clt][$idEquipeB]['Pts'] + $arrayCltB['Pts'];
					$rEgalites[$clt][$idEquipeB]['J'] = $rEgalites[$clt][$idEquipeB]['J'] + $arrayCltB['J'];
					$rEgalites[$clt][$idEquipeB]['Plus'] = $rEgalites[$clt][$idEquipeB]['Plus'] + $arrayCltB['Plus'];
					$rEgalites[$clt][$idEquipeB]['Diff'] = $rEgalites[$clt][$idEquipeB]['Diff'] + $arrayCltB['Diff'];
				}
	
	
				foreach ($rEgalites[$clt] as $team => $team_value) {
					$arrayCltGlobal[$clt][] = [
						'clt' => $clt,
						'team' => $team,
						'Pts' => $team_value['Pts'],
						'Plus' => $team_value['Plus'],
						'Diff' => $team_value['Diff']
					];
				}
	
				// Tri sur plusieurs colonnes, façon BDD
				// Ajoute $data en tant que dernier paramètre, 
				// pour trier par la clé commune
				array_multisort(
					array_column($arrayCltGlobal[$clt], 'Pts'), SORT_DESC, 
					array_column($arrayCltGlobal[$clt], 'Diff'), SORT_DESC, 
					array_column($arrayCltGlobal[$clt], 'Plus'), SORT_DESC, 
					$arrayCltGlobal[$clt]
				);
				print_r($arrayCltGlobal[$clt]);
	
				$sql = "UPDATE gickp_Competitions_Equipes_Journee 
					SET Clt = ? 
					WHERE Id = ? 
					AND Id_journee = ? ";
				$result = $myBdd->pdo->prepare($sql);
				
				// incrémentation de 1 classement à partir du 2ème
				for ($i = 1; $i < count($arrayCltGlobal[$clt]); $i ++) {
					$arrayCltGlobal[$clt][$i]['clt'] += $i;
					
					// update BDD pour la journée
					$result->execute(array(
						$arrayCltGlobal[$clt][$i]['clt'], $arrayCltGlobal[$clt][$i]['team'], $journee
					));
				}
	
			}
		}
	}

	function FinalisationClassementNiveau($codeCompet, $codeSaison)
	{
		$myBdd = $this->myBdd;
		
		$oldClt = 0;
		$oldPts = 0;
		$oldDiff = 9999;
		$i = 0;
		
		$sql = "UPDATE gickp_Competitions_Equipes 
		SET CltNiveau = ?
		WHERE Id = ? ";
		$result1 = $myBdd->pdo->prepare($sql);


		// Chargement des Equipes par ordre de Pts ...
		$sql = "SELECT Id, PtsNiveau, Diff 
			FROM gickp_Competitions_Equipes 
			WHERE Code_compet = ? 
			AND Code_saison = ? 
			ORDER BY PtsNiveau DESC, Diff DESC ";	 
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($codeCompet, $codeSaison));
		while ($row = $result->fetch()) {
			if ( (abs($row['PtsNiveau']-$oldPts) >= 1) || ($row['Diff'] != $oldDiff) ) {
				$clt = $i + 1;
				
				$oldClt = $clt;
				$oldPts = $row['PtsNiveau'];
				$oldDiff = $row['Diff'];
			} else {
				$clt = $oldClt;
			}
			
			$result1->execute(array($clt, $row['Id']));
			$i ++;
		}
	}
	
	function FinalisationClassementNiveauChpt($codeCompet, $codeSaison)
	{
		$myBdd = $this->myBdd;
		
		$oldClt = 0;
		$oldPts = 0;
		$oldNiveau = -1;
		$j = 0;

		$sql = "UPDATE gickp_Competitions_Equipes_Niveau 
			SET Clt = ? 
			WHERE Id = ? 
			AND Niveau = ? ";
		$result1 = $myBdd->pdo->prepare($sql);

		
		// Chargement des Equipes par ordre de Pts ...
		$sql = "SELECT a.Id, a.Niveau, a.Clt, a.Pts, a.J, a.G, a.N, a.P, a.F, 
			a.Plus, a.Moins, a.Diff 
			FROM gickp_Competitions_Equipes_Niveau a, gickp_Competitions_Equipes b 
			WHERE a.Id = b.Id 
			AND b.Code_compet = ? 
			AND b.Code_saison = ? 
			ORDER BY a.Niveau, a.Pts DESC, a.Diff DESC ";	 
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($codeCompet, $codeSaison));
		while ($row = $result->fetch()) {
			if ($row['Niveau'] != $oldNiveau) {
				$oldNiveau = $row['Niveau'];
				$clt = 1;
				$oldClt = $clt;
				$oldPts = $row['Pts'];
				$j = 0;
			} else {		
				if ($row['Pts'] != $oldPts) {
					$clt = $j + 1;
					$oldClt = $clt;
					$oldPts = $row['Pts'];
				} else {
					$clt = $oldClt;
				}
			}
					
			$result1->execute(array($clt, $row['Id'], $row['Niveau']));
			$j ++;
		}
	}
	
	function FinalisationClassementNiveauNiveau($codeCompet, $codeSaison)
	{
		$myBdd = $this->myBdd;
		
		$oldClt = 0;
		$oldPts = 0;
		$oldDiff = 9999;
		$oldNiveau = -1;
		$j = 0;
		
		$sql = "UPDATE gickp_Competitions_Equipes_Niveau 
			SET CltNiveau = ?  
			WHERE Id = ? 
			AND Niveau = ? ";
		$result1 = $myBdd->pdo->prepare($sql);
	
		// Chargement des Equipes par ordre de Pts ...
		$sql = "SELECT a.Id, a.Niveau, a.PtsNiveau, a.Diff 
			FROM gickp_Competitions_Equipes_Niveau a, gickp_Competitions_Equipes b 
			WHERE a.Id = b.Id 
			AND b.Code_compet = ? 
			AND b.Code_saison = ? 
			ORDER BY a.Niveau, a.PtsNiveau DESC, a.Diff DESC ";	 
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($codeCompet, $codeSaison));
		while ($row = $result->fetch()) {
			if ($row['Niveau'] != $oldNiveau) {
				$oldNiveau = $row['Niveau'];
				$clt = 1;
				$oldClt = $clt;
				$oldPts = $row['PtsNiveau'];
				$oldDiff =  $row['Diff'];
				$j = 0;
			}  else {
				if ( (abs($row['PtsNiveau']-$oldPts) >= 1) || ($row['Diff'] != $oldDiff) ) {
					$clt = $j+1;
					
					$oldClt = $clt;
					$oldPts = $row['PtsNiveau'];
					$oldDiff = $row['Diff'];
				} else {
					$clt = $oldClt;
				}
			}
					
			$result1->execute(array($clt, $row['Id'], $row['Niveau']));
			$j ++;
		}
	}
	
	// FinalisationClassementJourneeChpt
	function FinalisationClassementJourneeChpt($codeCompet, $codeSaison, $goalaverage, $tousLesMatchs)
	{
		$myBdd = $this->myBdd;
		
		$oldId = 0;
		$oldClt = 0;
		$oldPts = 0;
		$oldIdJournee = 0;
		$i = 0;
		$aEgalites = [];

		$sql = "UPDATE gickp_Competitions_Equipes_Journee 
			SET Clt = ? 
			WHERE Id = ? 
			AND Id_journee = ? ";
		$result1 = $myBdd->pdo->prepare($sql);


		// Chargement des Equipes par ordre de Pts ...
		$sql = "SELECT a.Id, a.Id_journee, a.Clt, a.Pts, a.J, a.G, a.N, a.P, 
			a.F, a.Plus, a.Moins, a.Diff, j.Type 
			FROM gickp_Competitions_Equipes b, gickp_Competitions_Equipes_Journee a
			LEFT OUTER JOIN gickp_Journees j ON a.Id_journee = j.Id
			WHERE a.Id = b.Id 
			AND b.Code_compet = ? 
			AND b.Code_saison = ? 
			ORDER BY a.Id_journee, a.Pts DESC, a.Diff DESC, a.Plus DESC ";	 
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($codeCompet, $codeSaison));
		while ($row = $result->fetch()) {
			$idJournee = $row['Id_journee'];
			if ($idJournee != $oldIdJournee) {
				$oldIdJournee = $idJournee;
				$clt = 1;
				$oldClt = $clt;
				$oldPts = $row['Pts'];
				$i = 0;
			} else {
				// Contrôle goal average général ou particulier
				if ($goalaverage == 'gen' && $row['Pts'] == $oldPts) {
					$clt = $i + 1;
				} elseif ($goalaverage != 'gen' && $row['Pts'] == $oldPts) {
					$clt = $oldClt;
					if ($row['Type'] == 'C') {
						if (!isset($aEgalites[$idJournee][$clt]) || !in_array($oldId, $aEgalites[$idJournee][$clt])) {
							$aEgalites[$idJournee][$clt][$oldId] = $oldId;
						}
						$aEgalites[$idJournee][$clt][$row['Id']] = $row['Id'];
					}
				} else {
					$clt = $i + 1;
				}
			}
				
			$oldClt = $clt;
			$oldId =$row['Id'];
			$oldPts = $row['Pts'];

			$result1->execute(array($clt, $row['Id'], $row['Id_journee']));
			$i ++;
		}
		if (count($aEgalites) > 0) {
			$this->GestionEgalitesClassementJourneeChpt($aEgalites, $codeCompet, $codeSaison, $tousLesMatchs);
		}
	}
	
	function FinalisationClassementJourneeNiveau($codeCompet, $codeSaison)
	{
		$myBdd = $this->myBdd;
		
		$oldClt = 0;
		$oldPts = 0;
		$oldDiff = 9999;
		$oldIdJournee = 0;
		$j = 0;

		$sql = "UPDATE gickp_Competitions_Equipes_Journee 
			SET CltNiveau = ? 
			WHERE Id = ? 
			AND Id_journee = ? ";
		$result1 = $myBdd->pdo->prepare($sql);

		
		// Chargement des Equipes par ordre de Pts ...
		$sql = "SELECT a.Id, a.Id_journee, a.PtsNiveau, a.Diff 
			FROM gickp_Competitions_Equipes_Journee a, gickp_Competitions_Equipes b 
			WHERE a.Id = b.Id 
			AND b.Code_compet = ? 
			AND b.Code_saison = ? 
			ORDER BY a.Id_journee, a.PtsNiveau DESC, a.Diff DESC ";	 
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($codeCompet, $codeSaison));
		while ($row = $result->fetch()) {
			if ($row['Id_journee'] != $oldIdJournee) {
                $oldIdJournee = $row['Id_journee'];
				$clt = 1;
				$oldClt = $clt;
				$oldPts = $row['PtsNiveau'];
				$oldDiff = $row['Diff'];
				$j = 0;
			} else {
				if ( (abs($row['PtsNiveau']-$oldPts) >= 1) || ($row['Diff'] != $oldDiff) ) {
					$clt = $j + 1;
					
					$oldClt = $clt;
					$oldPts = $row['PtsNiveau'];
					$oldDiff = $row['Diff'];
				} else {
					$clt = $oldClt;
				}
			}

			$result1->execute(array($clt, $row['Id'], $row['Id_journee']));
			$j ++;
		}
	}

	function PublicationClassement()
	{
		$myBdd = $this->myBdd;
	
		$codeSaison = $myBdd->GetActiveSaison();
		$codeCompet = utyGetPost('codeCompet');
		$_SESSION['codeCompet'] = $codeCompet;
		
		//Update date publication
		$sql = "UPDATE gickp_Competitions 
			SET Date_publication = DATE(), 
			Date_publication_calcul = Date_calcul, 
			Code_uti_publication = ?, 
			Mode_publication_calcul = Mode_calcul 
			WHERE Code = ?  
			AND Code_saison = ? ";	 
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array(utyGetSession('User'), $codeCompet, $codeSaison));

		//Update Classement
		$sql = "UPDATE gickp_Competitions_Equipes 
			SET Pts_publi = Pts, Clt_publi = Clt, J_publi = J, G_publi = G, 
			N_publi = N, P_publi = P, F_publi = F, Plus_publi = Plus, 
			Moins_publi = Moins, Diff_publi = Diff, PtsNiveau_publi = PtsNiveau, 
			CltNiveau_publi = CltNiveau 
			WHERE Code_compet = ? 
			AND Code_saison = ? ";	 
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($codeCompet, $codeSaison));
		
		//Update Classement journées/phases
		$sql = "UPDATE gickp_Competitions_Equipes_Journee a, gickp_Competitions_Equipes b, gickp_Journees c  
			SET a.Pts_publi = a.Pts, a.Clt_publi = a.Clt, a.J_publi = a.J, a.G_publi = a.G, 
			a.N_publi = a.N, a.P_publi = a.P, a.F_publi = a.F, a.Plus_publi = a.Plus, a.Moins_publi = a.Moins, 
			a.Diff_publi = a.Diff, a.PtsNiveau_publi = a.PtsNiveau, a.CltNiveau_publi = a.CltNiveau 
			WHERE a.Id = b.Id 
			AND c.Id = a.Id_journee 
			AND c.Code_competition = ? 
			AND c.Code_saison = ? ";	 
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($codeCompet, $codeSaison));
		
		//Update Classement niveau
		$sql = "UPDATE gickp_Competitions_Equipes_Niveau a, gickp_Competitions_Equipes b 
			SET a.Pts_publi = a.Pts, a.Clt_publi = a.Clt, a.J_publi = a.J, a.G_publi = a.G, 
			a.N_publi = a.N, a.P_publi = a.P, a.F_publi = a.F, a.Plus_publi = a.Plus, 
			a.Moins_publi = a.Moins, a.Diff_publi = a.Diff, a.PtsNiveau_publi = a.PtsNiveau, 
			a.CltNiveau_publi = a.CltNiveau 
			WHERE a.Id = b.Id 
			AND b.Code_compet = ? 
			AND b.Code_saison = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($codeCompet, $codeSaison));


		$myBdd->utyJournal('Publication Classement', $codeSaison, $codeCompet);
	}
		
	function DePublicationClassement() // RAZ classement public
	{
		$myBdd = $this->myBdd;
	
		$codeSaison = $myBdd->GetActiveSaison();
		$codeCompet = utyGetPost('codeCompet');
		$_SESSION['codeCompet'] = $codeCompet;
		
		//Update date publication
		$sql = "UPDATE gickp_Competitions 
			SET Date_publication = DATE(), 
			Date_publication_calcul = Date_calcul, 
			Code_uti_publication = ?, 
			Mode_publication_calcul = Mode_calcul 
			WHERE Code = ?  
			AND Code_saison = ? ";	 
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array(utyGetSession('User'), $codeCompet, $codeSaison));

		//Update Classement
		$sql = "UPDATE gickp_Competitions_Equipes 
			SET Clt_publi = 0, CltNiveau_publi = 0 
			WHERE Code_compet = ? 
			AND Code_saison = ? ";	 
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($codeCompet, $codeSaison));
		
		//Update Classement journées/phases
		$sql = "UPDATE gickp_Competitions_Equipes_Journee a, gickp_Competitions_Equipes b, gickp_Journees c  
			SET a.Clt_publi = 0, a.CltNiveau_publi = 0 
			WHERE a.Id = b.Id 
			AND c.Id = a.Id_journee 
			AND c.Code_competition = ? 
			AND c.Code_saison = ? ";	 
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($codeCompet, $codeSaison));
		
		//Update Classement niveau
		$sql = "UPDATE gickp_Competitions_Equipes_Niveau a, gickp_Competitions_Equipes b 
			SET a.Clt_publi = 0, a.CltNiveau_publi = 0 
			WHERE a.Id = b.Id 
			AND b.Code_compet = ? 
			AND b.Code_saison = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($codeCompet, $codeSaison));

		$myBdd->utyJournal('Publication Classement RAZ', $codeSaison, $codeCompet);
	}
		
	// Transfert des Equipes séléctionnées 		
	function Transfert()
	{
		$myBdd = $this->myBdd;
		$codeCompet = utyGetPost('codeCompet');
		$codeSaison = $myBdd->GetActiveSaison();
		
		$codeCompetTransfert = utyGetPost('codeCompetTransfert');
		$codeSaisonTransfert = utyGetPost('codeSaisonTransfert');
		$lstEquipe = utyGetPost('ParamCmd');

		if ( (strlen($codeCompet) > 0) 
			&& (strlen($codeCompetTransfert) > 0) 
			&& (strlen($codeSaisonTransfert) > 0) 
			&& (strlen($lstEquipe) > 0) ) {
			if ($codeCompet.$codeSaison != $codeCompetTransfert.$codeSaisonTransfert) {
				
				$arrayEquipes = explode(',', $lstEquipe);
				$in = str_repeat('?,', count($arrayEquipes) - 1) . '?';

				// Raz Id_dupli ... 
				$sql = "UPDATE gickp_Competitions_Equipes 
					SET Id_dupli = null 
					WHERE Id_dupli IN ($in)";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute($arrayEquipes);
									
				// Insertion des Equipes ...
				$sql = "INSERT INTO gickp_Competitions_Equipes 
					(Code_compet,Code_saison, Libelle, Code_club, Numero, Id_dupli) 
					SELECT ?, ?, Libelle, Code_club, Numero, Id 
					FROM gickp_Competitions_Equipes 
					WHERE Id IN ($in) ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array_merge([$codeCompetTransfert], [$codeSaisonTransfert], $arrayEquipes));
				
				// Insertion des Joueurs Equipes ...
				$sql = "INSERT INTO gickp_Competitions_Equipes_Joueurs 
					(Id_equipe, Matric, Nom, Prenom, Sexe, Categ, Numero, Capitaine) 
					SELECT b.Id, a.Matric, a.Nom, a.Prenom, a.Sexe, d.Code, a.Numero, a.Capitaine 
					FROM gickp_Competitions_Equipes_Joueurs a, gickp_Competitions_Equipes b, 
					gickp_Competitions_Equipes c, gickp_Categorie d, gickp_Liste_Coureur e 
					WHERE a.Id_equipe = b.Id_dupli 
					AND a.Matric = e.Matric 
					AND a.Id_equipe = c.Id 
					AND b.Id_dupli IN ($in) 
					AND c.Code_compet = ? 
					AND c.Code_saison = ? 
					AND ? - Year(e.Naissance) BETWEEN d.Age_min AND d.Age_max ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array_merge($arrayEquipes, [$codeCompet], [$codeSaison], [$codeSaisonTransfert]));

				$myBdd->utyJournal('Transfert Equipes', $codeSaison, $codeCompet, 'NULL', 'NULL', 'NULL', 'Equipes '.$lstEquipe.' vers '.$codeCompetTransfert.'-'.$codeSaisonTransfert);
			} else {
				die ("Transfert impossible dans la même compétition !");
			}
		} else {
			die ("Pas d'équipe à transférer !");
		}
		
	}

	function DropTeam()
	{
		$paramCmd = explode(';', utyGetPost('ParamCmd', ''));
        $journee = $paramCmd[0];
        $equipe = $paramCmd[1];
        
        $myBdd = $this->myBdd;

        // Suppression ... 
		$sql = "DELETE FROM gickp_Competitions_Equipes_Journee 
			WHERE Id_journee = ? 
			AND Id = ? 
			AND J = 0 ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($journee, $equipe));
        
        return '';
	}

	function SetSessionSaison()
	{
		$codeSaison = utyGetPost('ParamCmd', '');
		if (strlen($codeSaison) == 0)
			return;
		
		$_SESSION['Saison'] = $codeSaison;
	}

	// GestionClassement 		
	function __construct()
	{			
		MyPageSecure::MyPageSecure(10);
		  
		$this->myBdd = new MyBdd();

		$alertMessage = '';

		$Cmd = utyGetPost('Cmd');
		if (strlen($Cmd) > 0) {
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
				
			if ($Cmd == 'DropTeam')
				($_SESSION['Profile'] <= 3) ? $alertMessage = $this->DropTeam() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'SessionSaison')
				($_SESSION['Profile'] <= 10) ? $this->SetSessionSaison() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($alertMessage == '') {
				header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);	
				exit;
			}
		}

		$this->SetTemplate("Gestion_des_classements", "Classements", false);
		$this->Load();
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplate('GestionClassement');
	}
}		  	

$page = new GestionClassement();

