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
		
		$codeSaison = utyGetSaison();
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

		$codeSaison = utyGetSaison();
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
		
		$this->FinalisationClassementJourneeChpt($codeCompet, $codeSaison);
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
			AND b.Code_competition = ? 
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
		$codeSaison = utyGetSaison();
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
		
		$myBdd->Query($sql);
		
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
		
		$myBdd->Query($sql);
	}
	
	// StepClassementCompetitionEquipeNiveau
	function StepClassementCompetitionEquipeNiveau($idEquipeA, $arrayCltA, $idEquipeB, $arrayCltB, $niveau)
	{
		$myBdd = $this->myBdd;
		
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
		
		$myBdd->Query($sql);
		
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
		
		$myBdd->Query($sql);
	}

	// StepClassementCompetitionEquipeJournee
	function StepClassementCompetitionEquipeJournee($idEquipeA, $arrayCltA, $idEquipeB, $arrayCltB, $idJournee)
	{
		$myBdd = $this->myBdd;
		
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
		
		$myBdd->Query($sql);
		
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
	
		$myBdd->Query($sql);
	}
		
	function FinalisationClassementChpt($codeCompet, $codeSaison, $goalaverage, $tousLesMatchs)
	{
		$myBdd = $this->myBdd;
		
		$oldId = 0;
		$oldClt = 0;
		$oldPts = 0;
		$oldDiff = 0;
		$oldPlus = 0;
		$egalites = 1;
		$i = 0;
		$aEgalites = [];
		
		// Chargement des Equipes par ordre de Pts, Diff & Plus
		$sql  = "SELECT Id, Clt, Pts, J, G, N, P, F, Plus, Moins, Diff 
				FROM gickp_Competitions_Equipes 
				WHERE Code_compet = '" . $codeCompet . "' 
				AND Code_saison = '" . $codeSaison . "' 
				ORDER BY Pts DESC, Diff DESC, Plus DESC ";	 

		$result = $myBdd->Query($sql);
		while($row = $myBdd->FetchArray($result)) {
			// TODO: Contrôle goal average général ou particulier
			
			// if ($goalaverage == 'gen' && $row['Pts'] == $oldPts && $row['Diff'] == $oldDiff && $row['Plus'] == $oldPlus) {
			if ($goalaverage == 'gen' && $row['Pts'] == $oldPts) {
                $clt = $oldClt;
				$egalites ++;
			} elseif ($goalaverage != 'gen' && $row['Pts'] == $oldPts) {
				$clt = $oldClt;
				if (!in_array($oldId, $aEgalites[$row['Pts']])) {
					$aEgalites[$clt][] = $oldId;
				}
				$aEgalites[$clt][] = $row['Id'];
            } else {
                $clt = $i + 1;
            }
			
			$oldClt = $clt;
			$oldId =$row['Id'];
			$oldPts = $row['Pts'];
			$oldDiff = $row['Diff'];
			$oldPlus = $row['Plus'];

            $sql  = "UPDATE gickp_Competitions_Equipes 
					SET Clt = " . $clt . "
					WHERE Id = " . $row['Id'];
			
			$myBdd->Query($sql);
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
		// echo '<pre>';

		// TODO: Contrôle goal average particulier
		foreach ($aEgalites as $clt => $teams) {
			$listTeams = implode(',', $teams);
			$sql  = "SELECT a.Id_equipeA, a.ScoreA, a.Id_equipeB, a.ScoreB, a.CoeffA, 
					a.CoeffB, a.Id, a.Id_journee, b.Niveau, c.Points 
					FROM gickp_Matchs a, gickp_Journees b, gickp_Competitions c 
					WHERE a.Id_journee = b.Id 
					AND b.Code_competition = '" . $codeCompet . "' 
					AND b.Code_competition = c.Code 
					AND b.Code_saison = '" . $codeSaison . "' 
					AND b.Code_saison = c.Code_saison 
					AND a.Id_equipeA IN (" . $listTeams . ") 
					AND a.Id_equipeB IN (" . $listTeams . ") ";
			if (!$tousLesMatchs) { //uniquement les matchs validés (vérouillés)
				$sql .= "AND a.Validation = 'O' ";
			}
			$sql .= "ORDER BY b.Id ";	 
			
			$result = $myBdd->Query($sql);
			while($row = $myBdd->FetchArray($result)) {
				// var_dump($row);
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
					// if (!utyIsTypeCltCoupe($typeClt)) {
					// 	continue;
					// }

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
				
				// $this->SetArrayClt($scoreA, $scoreB, $niveau, $arrayCltA, $arrayCltB, $coeffA, $coeffB, $Points);
				
				// // Incrementation gickp_Competitions_Equipes ...
				// $this->StepClassementCompetitionEquipe($idEquipeA, $arrayCltA, $idEquipeB, $arrayCltB);

				// // Incrementation gickp_Competitions_Equipes_Niveau ...
				// $this->StepClassementCompetitionEquipeNiveau($idEquipeA, $arrayCltA, $idEquipeB, $arrayCltB, $niveau);
				
				// // Incrementation gickp_Competitions_Equipes_Journee ...
				// $this->StepClassementCompetitionEquipeJournee($idEquipeA, $arrayCltA, $idEquipeB, $arrayCltB, $idJournee);
			}	


		}
		// var_dump($aEgalites);
		// echo '</pre>';
		// die;	
	}

	function FinalisationClassementNiveau($codeCompet, $codeSaison)
	{
		$myBdd = $this->myBdd;
		
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
		$i = 0;
		
		$result = $myBdd->Query($sql);
		while($row = $myBdd->FetchArray($result)) {
			if ( (abs($row['PtsNiveau']-$oldPts) >= 1) || ($row['Diff'] != $oldDiff) ) {
				$clt = $i + 1;
				
				$oldClt = $clt;
				$oldPts = $row['PtsNiveau'];
				$oldDiff = $row['Diff'];
			} else {
				$clt = $oldClt;
			}
			
			$sql  = "Update gickp_Competitions_Equipes Set CltNiveau = ";
			$sql .= $clt;
			$sql .= " Where Id = ";
			$sql .= $row['Id'];
			
			$myBdd->Query($sql);
			$i ++;
		}
	}
	
	function FinalisationClassementNiveauChpt($codeCompet, $codeSaison)
	{
		$myBdd = $this->myBdd;
		
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
		$j = 0;
		
		$result = $myBdd->Query($sql);
		while($row = $myBdd->FetchArray($result)) {			
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
					
			$sql  = "Update gickp_Competitions_Equipes_Niveau Set Clt = ";
			$sql .= $clt;
			$sql .= " Where Id = ";
			$sql .= $row['Id'];
			$sql .= " And Niveau = ";
			$sql .= $row['Niveau'];
		
			$myBdd->Query($sql);
			$j ++;
		}
	}
	
	function FinalisationClassementNiveauNiveau($codeCompet, $codeSaison)
	{
		$myBdd = $this->myBdd;
		
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
		$j = 0;
		
		$result = $myBdd->Query($sql);
		while($row = $myBdd->FetchArray($result)) {			
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
					
			$sql  = "Update gickp_Competitions_Equipes_Niveau Set CltNiveau = ";
			$sql .= $clt;
			$sql .= " Where Id = ";
			$sql .= $row['Id'];
			$sql .= " And Niveau = ";
			$sql .= $row['Niveau'];
			
			$myBdd->Query($sql);
			$j ++;
		}
	}
	
	// FinalisationClassementJourneeChpt
	function FinalisationClassementJourneeChpt($codeCompet, $codeSaison)
	{
		$myBdd = $this->myBdd;
		
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
		$j = 0;
		
		$result = $myBdd->Query($sql);
		while($row = $myBdd->FetchArray($result)) {
			if ($row['Id_journee'] != $oldIdJournee) {
				$oldIdJournee = $row['Id_journee'];
				$clt = 1;
				$oldClt = $clt;
				$oldPts = $row['Pts'];
				$j = 0;
			} else {
				// TODO: Contrôle goal average général ou particulier

				//if ($row['Pts'] != $oldPts)
				//{
					$clt = $j + 1;
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
		
			$myBdd->Query($sql);
			$j ++;
		}
	}
	
	function FinalisationClassementJourneeNiveau($codeCompet, $codeSaison)
	{
		$myBdd = $this->myBdd;
		
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
		$j = 0;
		
		$result = $myBdd->Query($sql);
		while($row = $myBdd->FetchArray($result)) {
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
					
			$sql  = "UPDATE gickp_Competitions_Equipes_Journee Set CltNiveau = ";
			$sql .= $clt;
			$sql .= " Where Id = ";
			$sql .= $row['Id'];
			$sql .= " And Id_journee = ";
			$sql .= $row['Id_journee'];

			$myBdd->Query($sql);
			$j ++;
		}
	}

	function PublicationClassement()
	{
		$myBdd = $this->myBdd;
	
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
		$myBdd->Query($sql);

		//Update Classement
		$sql  = "Update gickp_Competitions_Equipes ";
		$sql .= "Set Pts_publi = Pts, Clt_publi = Clt, J_publi = J, G_publi = G, N_publi = N, P_publi = P, F_publi = F, ";
		$sql .= "Plus_publi = Plus, Moins_publi = Moins, Diff_publi = Diff, PtsNiveau_publi = PtsNiveau, CltNiveau_publi = CltNiveau ";
		$sql .= "Where Code_compet = '";
		$sql .= $codeCompet;
		$sql .= "' And Code_saison = '";
		$sql .= $codeSaison;
		$sql .= "' ";	 
		$myBdd->Query($sql);
		
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
		$myBdd->Query($sql);
		
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
		$myBdd->Query($sql);


		$myBdd->utyJournal('Publication Classement', $codeSaison, $codeCompet);
	}
		
	function DePublicationClassement() // RAZ classement public
	{
		$myBdd = $this->myBdd;
	
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
		$myBdd->Query($sql);

		//Update Classement
		$sql  = "Update gickp_Competitions_Equipes ";
		$sql .= "Set Clt_publi = 0, CltNiveau_publi = 0 ";
		$sql .= "Where Code_compet = '";
		$sql .= $codeCompet;
		$sql .= "' And Code_saison = '";
		$sql .= $codeSaison;
		$sql .= "' ";	 
		$myBdd->Query($sql);
		
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
		$myBdd->Query($sql);
		
		//Update Classement niveau
		$sql  = "Update gickp_Competitions_Equipes_Niveau a, gickp_Competitions_Equipes b  ";
		$sql .= "Set a.Clt_publi = 0, a.CltNiveau_publi = 0 ";
		$sql .= "Where a.Id = b.Id ";
		$sql .= "And b.Code_compet = '";
		$sql .= $codeCompet;
		$sql .= "' And b.Code_saison = '";
		$sql .= $codeSaison;
		$sql .= "' ";	 
		$myBdd->Query($sql);


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

		if ( (strlen($codeCompet) > 0) 
			&& (strlen($codeCompetTransfert) > 0) 
			&& (strlen($codeSaisonTransfert) > 0) 
			&& (strlen($lstEquipe) > 0) ) {
			if ($codeCompet.$codeSaison != $codeCompetTransfert.$codeSaisonTransfert) {
				$myBdd = $this->myBdd;
				
				// Raz Id_dupli ... 
				$sql  = "Update gickp_Competitions_Equipes Set Id_dupli = null Where Id_dupli In ($lstEquipe)";
				$myBdd->Query($sql);
							
				// Insertion des Equipes ...
				$sql  = "Insert Into gickp_Competitions_Equipes (Code_compet,Code_saison, Libelle, Code_club, Numero, Id_dupli) ";
				$sql .= "Select '$codeCompetTransfert', '$codeSaisonTransfert', Libelle, Code_club, Numero, Id ";
				$sql .= "From gickp_Competitions_Equipes Where Id In ($lstEquipe) ";
				$myBdd->Query($sql);
				
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
				$myBdd->Query($sql);

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
        $sql  = "DELETE FROM gickp_Competitions_Equipes_Journee "
                . "WHERE Id_journee = $journee "
                . "AND Id = $equipe "
                . "AND J = 0 ";
		$myBdd->Query($sql);
        
//        return 'Suppression effectuée';
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

