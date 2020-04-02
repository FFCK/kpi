<?php
include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion des Equipes
class GestionEquipe extends MyPageSecure	 
{	
	function Load()
	{
        $myBdd = new MyBdd();

		// Langue
        $langue = parse_ini_file("../commun/MyLang.ini", true);
        if (utyGetSession('lang') == 'en') {
            $lang = $langue['en'];
        } else {
            $lang = $langue['fr'];
        }
        
        $_SESSION['updatecell_tableName'] = 'gickp_Competitions_Equipes';
		$_SESSION['updatecell_where'] = 'Where Id = ';
		$_SESSION['updatecell_document'] = 'formEquipe';
		
		$codeSaison = $myBdd->GetActiveSaison();
		$this->m_tpl->assign('codeSaison', $codeSaison);

		$codeCompet = utyGetSession('codeCompet');
		$codeCompet = utyGetPost('competition', $codeCompet);
		$_SESSION['codeCompet'] = $codeCompet;

		if ($codeCompet == '*') {
            $codeCompet = '';
        }

        //Filtre affichage type compet
		$AfficheCompet = utyGetSession('AfficheCompet','');
		$AfficheCompet = utyGetPost('AfficheCompet', $AfficheCompet);
        $_SESSION['AfficheCompet'] = $AfficheCompet;
		$this->m_tpl->assign('AfficheCompet', $AfficheCompet);
		
		// Chargement des Compétitions ...
		$sql  = "SELECT c.Code_niveau, c.Code_ref, c.Code_tour, c.Code, c.Libelle, c.Soustitre, 
			c.Soustitre2, c.Titre_actif, c.Verrou, c.Statut, g.section, g.ordre 
			FROM gickp_Competitions c, gickp_Competitions_Groupes g 
			WHERE c.Code_saison = '" . $codeSaison . "' ";
		$sql .= utyGetFiltreCompetition('c.');
		$sql .= " AND c.Code_niveau LIKE '".utyGetSession('AfficheNiveau')."%' ";
		if ($AfficheCompet == 'N') {
            $sql .= " AND c.Code LIKE 'N%' ";
        } elseif ($AfficheCompet == 'CF') {
            $sql .= " AND c.Code LIKE 'CF%' ";
        } elseif ($AfficheCompet == 'M') {
            $sql .= " AND c.Code_ref = 'M' ";
        } elseif($AfficheCompet > 0) {
            $sql .= " AND g.section = '" . $AfficheCompet . "' ";
        }
		$sql .= " AND c.Code_ref = g.Groupe 
			ORDER BY c.Code_saison, g.section, g.ordre, COALESCE(c.Code_ref, 'z'), c.Code_tour, c.GroupOrder, c.Code";	 
		$arrayCompetition = array();
        $i = -1;
        $j = '';
        $label = $myBdd->getSections();
		$result = $myBdd->pdo->prepare($sql);
		$result->execute();
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

            // Si $codeCompet Vide on prend le premier de la liste ...
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
                $this->m_tpl->assign('Statut', $row["Statut"]);
                if ($row["Verrou"] == 'O') {
                    $Verrou = 'O';
                } else {
                    $Verrou = 'N';
                }
                $this->m_tpl->assign('Verrou', $Verrou);
            } else {
                $row['selected'] = '';
            }
            $j = $row['section'];
            $arrayCompetition[$i]['options'][] = $row;
        }
		$this->m_tpl->assign('arrayCompetition', $arrayCompetition);
		$this->m_tpl->assign('codeCompet', $codeCompet);
						
		
		// Chargement des Equipes ...
		$arrayEquipe = array();
		
		if (strlen($codeCompet) > 0) { 
			if ($codeCompet != 'POOL') {
				$sql  = "SELECT ce.Id, ce.Libelle, ce.Code_club, ce.Numero, ce.Poule, ce.Tirage, 
					c.Code_comite_dep, c.Libelle Club  
					FROM gickp_Competitions_Equipes ce, gickp_Club c 
					WHERE ce.Code_compet = ? 
					AND ce.Code_saison = ?
					AND ce.Code_club = c.Code 
					ORDER BY ce.Poule, ce.Tirage, ce.Libelle, ce.Id ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array($codeCompet, $codeSaison));
			} else {
				$sql  = "SELECT ce.Id, ce.Libelle, ce.Code_club, ce.Numero, ce.Poule, ce.Tirage, 
					c.Code_comite_dep, c.Libelle Club 
					FROM gickp_Competitions_Equipes ce, gickp_Club c 
					WHERE ce.Code_compet = ? 
					AND ce.Code_club = c.Code 
					ORDER BY ce.Poule, ce.Tirage, ce.Libelle, ce.Id ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array($codeCompet));
			}
			
            $num_results = 0;
			while ($row = $result->fetch()) {
				$num_results ++;
                $nbMatchs = 0;
				$sql2  = "SELECT count(ce.Id) nbMatchs 
					FROM gickp_Competitions_Equipes ce, gickp_Matchs m, gickp_Journees j 
					WHERE ce.Code_compet = :codeCompet 
					AND ce.Code_saison = :codeSaison 
					AND j.Code_competition = :codeCompet 
					AND j.Code_saison = :codeSaison 
					AND j.Id = m.Id_journee 
					AND ce.Id = :idEquipe 
					AND (ce.Id = m.Id_equipeA 
						OR ce.Id = m.Id_equipeB) ";
				$result2 = $myBdd->pdo->prepare($sql2);
				$result2->execute(array(
					':codeCompet' => $codeCompet,
					':codeSaison' => $codeSaison,
					':idEquipe' => $row['Id'],
				));

				$row2 = $result2->fetch();
                $nbMatchs = $row2['nbMatchs'];
                
				if (strlen($row['Code_comite_dep']) > 3) {
                    $row['Code_comite_dep'] = 'FRA';
                }
                if ($row['Tirage'] != 0 or $row['Poule'] != '') {
                    $this->m_tpl->assign('Tirage', 'ok');
                }

                array_push($arrayEquipe, array('Id' => $row['Id'], 'Libelle' => $row['Libelle'], 
                    'Code_club' => $row['Code_club'], 'Numero' => $row['Numero'], 'Club' => $row['Club'], 
                    'Poule' => $row['Poule'], 'Tirage' => $row['Tirage'], 'nbMatchs' => $nbMatchs, 
                    'Code_comite_dep' => $row['Code_comite_dep'] ));
			}
		}	
		$this->m_tpl->assign('arrayEquipe', $arrayEquipe);

		//Mise à jour du nombre d'équipe de la compétition
		$sql  = "UPDATE gickp_Competitions 
			SET Nb_equipes = ?  
			WHERE Code = ?  
			AND Code_saison = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($num_results, $codeCompet, $codeSaison));

		// Les comites et les clubs ...
		$codeComiteReg = utyGetSession('codeComiteReg', '*');
		$codeComiteReg = utyGetPost('comiteReg', $codeComiteReg);
		if (strlen($codeComiteReg) == 0) {
            $codeComiteReg = '*';
        }

        $codeComiteDep = utyGetSession('codeComiteDep', '*');
		$codeComiteDep = utyGetPost('comiteDep', $codeComiteDep);
		if (strlen($codeComiteDep) == 0) {
            $codeComiteDep = '*';
        }

        $codeClub = utyGetSession('codeClub', '*');
		$codeClub = utyGetPost('club', $codeClub);
		if (strlen($codeClub) == 0) {
            $codeClub = '*';
        }

        if (isset($_POST['ParamCmd']))	// @COSANDCO_WAMPSER
		{
			if ($codeComiteReg == '*' && $_POST['ParamCmd'] == 'changeComiteReg')
			{
				$codeComiteDep = '*';
				$codeClub = '*';
			}
			
			if ($codeComiteDep == '*' && $_POST['ParamCmd'] == 'changeComiteDep')
			{
				$codeClub = '*';
			}
		}
			
		$_SESSION['codeComiteReg'] = $codeComiteReg;
		$_SESSION['codeComiteDep'] = $codeComiteDep;
		$_SESSION['codeClub'] = $codeClub;

		// Chargement des Comites Régionaux ...
		$sql  = "SELECT Code, Libelle 
			FROM gickp_Comite_reg 
			ORDER BY Code ";	 
		$arrayComiteReg = array();

		if ('*' == $codeComiteReg) {
            array_push($arrayComiteReg, array('Code' => '*', 'Libelle' => '* - ' . $lang['Tous'], 'Selected' => 'SELECTED'));
        } else {
            array_push($arrayComiteReg, array('Code' => '*', 'Libelle' => '* - ' . $lang['Tous'], 'Selected' => ''));
        }

		foreach ($myBdd->pdo->query($sql) as $row) {
			if ($row["Code"] == $codeComiteReg) {
                array_push($arrayComiteReg, array('Code' => $row["Code"], 'Libelle' => $row["Code"] . " - " . $row["Libelle"], 'Selected' => 'SELECTED'));
            } else {
                array_push($arrayComiteReg, array('Code' => $row["Code"], 'Libelle' => $row["Code"] . " - " . $row["Libelle"], 'Selected' => ''));
            }
        }
		
		$this->m_tpl->assign('arrayComiteReg', $arrayComiteReg);
		
		// Chargement des Comites Departementaux ...
		$arrayComiteDep = array();
		if ('*' == $codeComiteDep) {
            array_push($arrayComiteDep, array('Code' => '*', 'Libelle' => '* - ' . $lang['Tous'], 'Selected' => 'SELECTED'));
            // $bSelectCombo = true;
        } else {
            array_push($arrayComiteDep, array('Code' => '*', 'Libelle' => '* - ' . $lang['Tous'], 'Selected' => ''));
        }

		if ($codeComiteReg != '*') {
			$sql = "SELECT Code, Libelle 
				FROM gickp_Comite_dep 
				WHERE Code_comite_reg = ?
				ORDER BY Code ";	 
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($codeComiteReg));
		} else {
			$sql = "SELECT Code, Libelle 
				FROM gickp_Comite_dep 
				ORDER BY Code ";	 
			$result = $myBdd->pdo->prepare($sql);
			$result->execute();
		}

		while($row = $result->fetch()) {
			if ($row["Code"] == $codeComiteDep) {
                array_push($arrayComiteDep, array('Code' => $row['Code'], 'Libelle' => $row['Code'] . " - " . $row['Libelle'], 'Selected' => 'SELECTED'));
            } else {
                array_push($arrayComiteDep, array('Code' => $row['Code'], 'Libelle' => $row['Code'] . " - " . $row['Libelle'], 'Selected' => ''));
            }
        }
			
		$this->m_tpl->assign('arrayComiteDep', $arrayComiteDep);
		
		// Chargement des Clubs ...
		$arrayClub = array();
		if ('*' == $codeClub) {
            array_push($arrayClub, array('Code' => '*', 'Libelle' => '* - ' . $lang['Tous'], 'Selected' => 'SELECTED'));
        } else {
            array_push($arrayClub, array('Code' => '*', 'Libelle' => '* - ' . $lang['Tous'], 'Selected' => ''));
        }

		$sql  = "SELECT c.Code, c.Libelle 
			FROM gickp_Club c, gickp_Comite_dep cd 
			WHERE c.Code_comite_dep = cd.Code ";
			if ($codeComiteReg != '*')
			{
				$sql .= "AND cd.Code_comite_reg = '";
				$sql .= $codeComiteReg;
				$sql .= "' ";
			}
			if ($codeComiteDep != '*')
			{
				$sql .= "AND c.Code_comite_dep = '";
				$sql .= $codeComiteDep;
				$sql .= "' ";
			}
			$sql .= "ORDER BY c.Code ";	 
			
		$result = $myBdd->pdo->prepare($sql);
		$result->execute();
		while($row = $result->fetch()) {
			if ($row["Code"] == $codeClub) {
                array_push($arrayClub, array('Code' => $row['Code'], 'Libelle' => $row['Code'] . ' - ' . $row['Libelle'], 'Selected' => 'SELECTED'));
            } else {
                array_push($arrayClub, array('Code' => $row['Code'], 'Libelle' => $row['Code'] . ' - ' . $row['Libelle'], 'Selected' => ''));
            }
        }
			
		$this->m_tpl->assign('arrayClub', $arrayClub);
		
		// Chargement des Equipes Historique ...
		$arrayHistoEquipe = array();
		array_push($arrayHistoEquipe, array('Numero' => '', 'Libelle' => '=> ' . $lang['NOUVELLE_EQUIPE'] . '...'));
		array_push($arrayHistoEquipe, array('Numero' => '', 'Libelle' => ''));
		
		if ($codeComiteReg != '98')
		{
			$sql  = "SELECT e.Numero, e.Libelle, e.Code_club 
				FROM gickp_Equipe e, gickp_Club c, gickp_Comite_dep cd 
				WHERE e.Code_club = c.Code 
				AND c.Code_comite_dep = cd.Code 
				AND cd.Code_comite_reg != '98' ";
			if ($codeComiteReg != '*')
			{
				$sql .= "AND cd.Code_comite_reg = '";
				$sql .= $codeComiteReg;
				$sql .= "' ";
			}
			if ($codeComiteDep != '*')
			{
				$sql .= "AND c.Code_comite_dep = '";
				$sql .= $codeComiteDep;
				$sql .= "' ";
			}
			if ($codeClub != '*')
			{
				$sql .= "AND e.Code_club = '";
				$sql .= $codeClub;
				$sql .= "' ";
			}
			$sql .= "ORDER BY e.Libelle ";
			
			$result = $myBdd->pdo->prepare($sql);
			$result->execute();
			$num_results = $result->rowCount();
			array_push($arrayHistoEquipe, array('Numero' => '', 'Libelle' => '==== FRANCE (' . $num_results . ' ' . $lang['equipes'] . ') ====', 'Code_club' => ''));
			while($row = $result->fetch()) {
				array_push($arrayHistoEquipe, array('Numero' => $row['Numero'], 'Libelle' => $row['Libelle'], 'Code_club' => $row['Code_club']));
			}
			array_push($arrayHistoEquipe, array('Numero' => '', 'Libelle' => '', 'Code_club' => ''));
		}
		if ($codeComiteReg == '98' or $codeComiteReg == '*')
		{
			$sql  = "SELECT e.Numero, e.Libelle, e.Code_club 
				FROM gickp_Equipe e, gickp_Club c, gickp_Comite_dep cd 
				WHERE e.Code_club = c.Code 
				AND c.Code_comite_dep = cd.Code 
				AND cd.Code_comite_reg = '98' ";
			if ($codeComiteReg != '*')
			{
				$sql .= "AND cd.Code_comite_reg = '";
				$sql .= $codeComiteReg;
				$sql .= "' ";
			}
			if ($codeComiteDep != '*')
			{
				$sql .= "AND c.Code_comite_dep = '";
				$sql .= $codeComiteDep;
				$sql .= "' ";
			}
			if ($codeClub != '*')
			{
				$sql .= "AND e.Code_club = '";
				$sql .= $codeClub;
				$sql .= "' ";
			}
			$sql .= "ORDER BY e.Libelle ";	 
			
			$result = $myBdd->pdo->prepare($sql);
			$result->execute();
			$num_results = $result->rowCount();
			array_push($arrayHistoEquipe, array('Numero' => '', 'Libelle' => '==== INTERNATIONAL (' . $num_results . ' ' . $lang['equipes'] . ') ====', 'Code_club' => ''));
			while($row = $result->fetch()) {
				array_push($arrayHistoEquipe, array('Numero' => $row['Numero'], 'Libelle' => $row['Libelle'], 'Code_club' => $row['Code_club']));
			}
		}
		$this->m_tpl->assign('arrayHistoEquipe', $arrayHistoEquipe);
	}
	
	function Add()
	{
		$myBdd = new MyBdd();
		
		$codeCompet = utyGetPost('competition');
		$codeSaison = $myBdd->GetActiveSaison();
		$libelleEquipe = utyGetPost('libelleEquipe');
		$histoEquipe = utyGetPost('histoEquipe');
		$codeClub = utyGetPost('club');
		$insertValue = '';

		foreach($histoEquipe as $selectValue) {
			if ($codeCompet == '' or $codeCompet == '*') {
				$alertMessage .= 'Aucune competition sélectionnée';
				return;
			}
            
			if ((int) $selectValue == 0) {
				// Inscription Manuelle ...
				if ((strlen($libelleEquipe) > 0) && (strlen($codeClub) > 0) ) {
					$sql  = "INSERT INTO gickp_Equipe (Libelle, Code_club) 
						VALUES (?, ?) ";
					$result = $myBdd->pdo->prepare($sql);
					$result->execute(array($libelleEquipe, $codeClub));
					$selectValue = $myBdd->pdo->lastInsertId();
				}
			}

            $sql = "INSERT INTO gickp_Competitions_Equipes 
				(Code_compet, Code_saison, Libelle, Code_club, Numero) 
				SELECT ?, ?, Libelle, Code_club, Numero 
				FROM gickp_Equipe 
				WHERE Numero = ? ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($codeCompet, $codeSaison, $selectValue));

			if ($insertValue != '') {
                $insertValue .= ',';
            }
            $insertValue .= $selectValue;
		}	
		$_SESSION['codeCompet'] = $codeCompet;
		$_SESSION['codeComiteReg'] = utyGetPost('comiteReg');
		$_SESSION['codeComiteDep'] = utyGetPost('comiteDep');
		$_SESSION['codeClub'] = $codeClub;

		$myBdd->utyJournal('Ajout equipe', $codeSaison, $codeCompet, 'NULL', 'NULL', 'NULL', $insertValue);
	}
	
	function Add2()
	{
		$myBdd = new MyBdd();
		
		$codeCompet = utyGetPost('competition');
		$codeSaison = $myBdd->GetActiveSaison();
		$EquipeNum = utyGetPost('EquipeNum');
		$EquipeNom = utyGetPost('EquipeNom');
		$checkCompo = utyGetPost('checkCompo');
		$plEquipe = utyGetPost('plEquipe', '');
		$tirEquipe = utyGetPost('tirEquipe', 0);
		$cltChEquipe = utyGetPost('cltChEquipe', 0);
		$cltCpEquipe = utyGetPost('cltCpEquipe', 0);
		
		if ($EquipeNum != '')
		{
			if ($codeCompet == '' or $codeCompet == '*')
			{
				$alertMessage .= 'Aucune competition sélectionnée';
				return;
			}
		
			$sql  = "INSERT INTO gickp_Competitions_Equipes 
				(Code_compet, Code_saison, Libelle, Code_club, Numero, Poule, Tirage, Clt, CltNiveau)
				SELECT ?, ?, Libelle, Code_club, Numero, ?, ?, ?, ? 
				FROM gickp_Equipe 
				WHERE Numero = ? ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(
				array($codeCompet, $codeSaison, $plEquipe, $tirEquipe, 
					$cltChEquipe, $cltCpEquipe, $EquipeNum
			));
			$EquipeId = $myBdd->pdo->lastInsertId();

			if($checkCompo != '') {
				$checkCompo = explode('-', $checkCompo);
				// Insertion des Joueurs Equipes ...
				$sql  = "INSERT INTO gickp_Competitions_Equipes_Joueurs 
					(Id_equipe, Matric, Nom, Prenom, Sexe, Categ, Numero, Capitaine) 
					SELECT :EquipeId, a.Matric, a.Nom, a.Prenom, a.Sexe, d.Code, a.Numero, a.Capitaine 
					FROM gickp_Competitions_Equipes_Joueurs a, gickp_Competitions_Equipes b, 
					gickp_Competitions_Equipes c, gickp_Categorie d, gickp_Liste_Coureur e 
					WHERE a.Id_equipe = b.Id 
					AND a.Matric = e.Matric 
					AND a.Id_equipe = c.Id 
					AND b.Numero = :EquipeNum 
					AND b.Code_compet = :checkCompo1 
					AND b.Code_saison = :checkCompo0 
					AND :checkCompo0 - Year(e.Naissance) between d.Age_min And d.Age_max ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array(
					':EquipeId' => $EquipeId, 
					':EquipeNum' => $EquipeNum, 
					':checkCompo1' => $checkCompo[1], 
					':checkCompo0' => $checkCompo[0]
				));
			}
		}
		
		
		$myBdd->utyJournal('Ajout equipe', $codeSaison, $codeCompet, 'NULL', 'NULL', 'NULL', $EquipeNom);
	}
	
	function Tirage()
	{
		$myBdd = new MyBdd();
		
		$codeCompet = utyGetPost('competition');
		$codeSaison = $myBdd->GetActiveSaison();
		$equipeTirage = utyGetPost('equipeTirage');
		$pouleTirage = utyGetPost('pouleTirage');
		$ordreTirage = utyGetPost('ordreTirage');
		
		$sql = "UPDATE gickp_Competitions_Equipes 
			SET Tirage = :ordreTirage, Poule = :pouleTirage 
			WHERE Code_compet = :codeCompet 
			AND Code_saison = :codeSaison 
			AND Id = :equipeTirage ";	 
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array(
			':ordreTirage' => $ordreTirage, 
			':pouleTirage' => $pouleTirage, 
			':codeCompet' => $codeCompet, 
			':codeSaison' => $codeSaison,
			':equipeTirage' => $equipeTirage
		));

		$myBdd->utyJournal('Tirage au sort', $codeSaison, $codeCompet, 'NULL', 'NULL', 'NULL', $equipeTirage.' -> '.$ordreTirage);
	}

	function Remove()
	{
		$myBdd = new MyBdd();
		$codeSaison = $myBdd->GetActiveSaison();

		$ParamCmd = '';
		if (isset($_POST['ParamCmd'])) {
            $ParamCmd = $_POST['ParamCmd'];
        }

        $arrayParam = explode(',', $ParamCmd);		
		if (count($arrayParam) == 0) {
            return;
        } // Rien à Detruire ...
		
		$sql = "DELETE FROM gickp_Competitions_Equipes 
			WHERE Id IN ($ParamCmd)";
		$myBdd->pdo->exec($sql);

		$sql = "DELETE FROM gickp_Competitions_Equipes_Joueurs 
			WHERE Id_equipe IN ($ParamCmd)";
		$myBdd->pdo->exec($sql);

		$myBdd->utyJournal('Suppression  equipes', $codeSaison, utyGetPost('codeCompet'), 'NULL', 'NULL', 'NULL', $ParamCmd);
	}
	
	function Duplicate($bDelete)
	{
		$myBdd = new MyBdd();
		$codeCompet = utyGetPost('competition');
		$codeCompetRef = utyGetPost('competitionRef');
		$codeSaison = $myBdd->GetActiveSaison();

		if ( (strlen($codeCompet) > 0) && (strlen($codeCompetRef) > 0) ) {
			$myBdd = new MyBdd();
			
			if ($bDelete) {
				// Suppression des Joueurs Equipes 
				$sql  = "DELETE FROM gickp_Competitions_Equipes_Joueurs 
					WHERE Id_equipe In (
						SELECT a.Id 
						FROM gickp_Competitions_Equipes a 
						WHERE a.Code_compet = ? 
						AND a.Code_saison = ? )";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array($codeCompet, $codeSaison));
		
				// Suppression des Equipes 
				$sql = "DELETE FROM gickp_Competitions_Equipes 
					WHERE Code_compet = ? 
					AND Code_saison = ? ";
				$result = $myBdd->pdo->prepare($sql);
				$result->execute(array($codeCompet, $codeSaison));
			}
			
			// Insertion des Equipes ...
			$sql  = "INSERT INTO gickp_Competitions_Equipes 
				(Code_compet,Code_saison, Libelle, Code_club, Numero, Id_dupli) 
				SELECT ?, Code_saison, Libelle, Code_club, Numero, Id 
				FROM gickp_Competitions_Equipes 
				WHERE Code_compet = ? 
				AND Code_saison = ? ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($codeCompet, $codeCompetRef, $codeSaison));
			
			// Insertion des Joueurs Equipes ...
			$sql  = "INSERT INTO gickp_Competitions_Equipes_Joueurs 
				(Id_equipe, Matric, Nom, Prenom, Sexe, Categ, Numero, Capitaine) 
				SELECT b.Id, a.Matric, a.Nom, a.Prenom, a.Sexe, a.Categ, a.Numero, a.Capitaine 
				FROM gickp_Competitions_Equipes_Joueurs a, gickp_Competitions_Equipes b, gickp_Competitions_Equipes c 
				WHERE a.Id_equipe = b.Id_dupli 
				AND a.Id_equipe = c.Id 
				AND c.Code_compet = ? 
				AND c.Code_saison = ? ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($codeCompetRef, $codeSaison));

			$myBdd->utyJournal('Duplication  equipes', $codeSaison, $codeCompet, 'NULL', 'NULL', 'NULL', 'Depuis '.$codeCompetRef);
		}
	}
	
	function __construct()
	{
	  	MyPageSecure::MyPageSecure(10);
		
		$alertMessage = '';
		
		$Cmd = '';
		if (isset($_POST['Cmd'])) {
            $Cmd = $_POST['Cmd'];
        }

        if (strlen($Cmd) > 0) {
			if ($Cmd == 'Add') {
                ($_SESSION['Profile'] <= 3) ? $this->Add() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
            }

            if ($Cmd == 'Add2') {
                ($_SESSION['Profile'] <= 3) ? $this->Add2() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
            }

            if ($Cmd == 'Tirage') {
                ($_SESSION['Profile'] <= 4) ? $this->Tirage() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
            }

            if ($Cmd == 'Remove') {
                ($_SESSION['Profile'] <= 3) ? $this->Remove() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
            }

            if ($Cmd == 'Duplicate') {
                ($_SESSION['Profile'] <= 3) ? $this->Duplicate(false) : $alertMessage = 'Vous n avez pas les droits pour cette action.';
            }

            if ($Cmd == 'RemoveAndDuplicate') {
                ($_SESSION['Profile'] <= 3) ? $this->Duplicate(true) : $alertMessage = 'Vous n avez pas les droits pour cette action.';
            }

            if ($alertMessage == '') {
				header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);	
				exit;
			}
		}

		$this->SetTemplate("Gestion_des_equipes", "Equipes", false);
		$this->Load();
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplate('GestionEquipe');
	}
}		  	

$page = new GestionEquipe();
