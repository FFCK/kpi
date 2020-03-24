<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion des Equipes

class GestionEquipe extends MyPageSecure	 
{	
	function Load()
	{
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
		
		$codeSaison = utyGetSaison();
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

        $myBdd = new MyBdd();
		
		// Chargement des Compétitions ...
		$sql  = "Select c.Code_niveau, c.Code_ref, c.Code_tour, c.Code, c.Libelle, c.Soustitre, c.Soustitre2, c.Titre_actif,"
                . "c.Verrou, c.Statut, g.section, g.ordre "
                . "From gickp_Competitions c, gickp_Competitions_Groupes g "
                . "Where c.Code_saison = '" . $codeSaison . "' ";
		$sql .= utyGetFiltreCompetition('c.');
		$sql .= " And c.Code_niveau Like '".utyGetSession('AfficheNiveau')."%' ";
		if ($AfficheCompet == 'N') {
            $sql .= " And c.Code Like 'N%' ";
        } elseif ($AfficheCompet == 'CF') {
            $sql .= " And c.Code Like 'CF%' ";
        } elseif ($AfficheCompet == 'M') {
            $sql .= " And c.Code_ref = 'M' ";
        } elseif($AfficheCompet > 0) {
            $sql .= " And g.section = '" . $AfficheCompet . "' ";
        }
        $sql .= " And c.Code_ref = g.Groupe ";
		$sql .= " Order By c.Code_saison, g.section, g.ordre, COALESCE(c.Code_ref, 'z'), c.Code_tour, c.GroupOrder, c.Code";	 
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
		
		if (strlen($codeCompet) > 0)
		{ 
			if ($codeCompet != 'POOL')
			{
				$sql  = "SELECT ce.Id, ce.Libelle, ce.Code_club, ce.Numero, ce.Poule, ce.Tirage, c.Code_comite_dep, c.Libelle Club  "
                        . "FROM gickp_Competitions_Equipes ce, gickp_Club c "
                        . "WHERE ce.Code_compet = '";
				$sql .= $codeCompet;
				$sql .= "' AND ce.Code_saison = '";
				$sql .= $codeSaison;
				$sql .= "' AND ce.Code_club = c.Code "
                        . " ORDER BY ce.Poule, ce.Tirage, ce.Libelle, ce.Id ";
			} else {
				$sql  = "SELECT ce.Id, ce.Libelle, ce.Code_club, ce.Numero, ce.Poule, ce.Tirage, c.Code_comite_dep, c.Libelle Club "
                        . "FROM gickp_Competitions_Equipes ce, gickp_Club c "
                        . "WHERE ce.Code_compet = '";
				$sql .= $codeCompet;
				$sql .= "' AND ce.Code_club = c.Code "
                        . "ORDER BY ce.Poule, ce.Tirage, ce.Libelle, ce.Id ";
			}
			
			$result = $myBdd->Query($sql);
            $num_results = $myBdd->NumRows($result);
            while($row = $myBdd->FetchArray($result)) {
                $nbMatchs = 0;
                $sql2  = "Select count(ce.Id) nbMatchs "
                        . "FROM gickp_Competitions_Equipes ce, gickp_Matchs m, gickp_Journees j "
                        . "WHERE ce.Code_compet = '" . $codeCompet . "' "
                        . "AND ce.Code_saison = '" . $codeSaison . "' "
                        . "AND j.Code_competition = '" . $codeCompet . "' "
                        . "AND j.Code_saison = '" . $codeSaison . "' "
                        . "AND j.Id = m.Id_journee "
                        . "AND ce.Id = " . $row['Id'] . " "
                        . "AND (ce.Id = m.Id_equipeA "
                        . "OR ce.Id = m.Id_equipeB) ";
                $result2 = $myBdd->Query($sql2);
                $row2 = $myBdd->FetchArray($result2);
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
		$sql  = "Update gickp_Competitions set Nb_equipes = '";
		$sql .= $num_results;
		$sql .= "' Where Code = '";
		$sql .= $codeCompet;
		$sql .= "' And Code_saison = '";
		$sql .= $codeSaison;
		$sql .= "' ";
		$myBdd->Query($sql);

		
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
		$sql  = "Select Code, Libelle ";
		$sql .= "From gickp_Comite_reg ";
		$sql .= "Order By Code ";	 
		$result = $myBdd->Query($sql);	
		$arrayComiteReg = array();

		if ('*' == $codeComiteReg) {
            array_push($arrayComiteReg, array('Code' => '*', 'Libelle' => '* - ' . $lang['Tous'], 'Selected' => 'SELECTED'));
        } else {
            array_push($arrayComiteReg, array('Code' => '*', 'Libelle' => '* - ' . $lang['Tous'], 'Selected' => ''));
        }

		while($row = $myBdd->FetchArray($result)) {
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

        $sql  = "Select Code, Libelle ";
		$sql .= "From gickp_Comite_dep ";
		if ($codeComiteReg != '*') {
			$sql .= "Where Code_comite_reg = '";
			$sql .= $codeComiteReg;
			$sql .= "' ";
		}
		$sql .= "Order By Code ";	 
		
		// $bSelectCombo = false;	
		$result = $myBdd->Query($sql);	
		while($row = $myBdd->FetchArray($result)) {
			if ($row["Code"] == $codeComiteDep) {
                array_push($arrayComiteDep, array('Code' => $row['Code'], 'Libelle' => $row['Code'] . " - " . $row['Libelle'], 'Selected' => 'SELECTED'));
                // $bSelectCombo = true;
            } else {
                array_push($arrayComiteDep, array('Code' => $row['Code'], 'Libelle' => $row['Code'] . " - " . $row['Libelle'], 'Selected' => ''));
            }
        }
			
//			if (!$bSelectCombo )
//				$codeComiteDep = '*';
//		}
		
		$this->m_tpl->assign('arrayComiteDep', $arrayComiteDep);
		
		// Chargement des Clubs ...
		$arrayClub = array();
		if ('*' == $codeClub) {
            array_push($arrayClub, array('Code' => '*', 'Libelle' => '* - ' . $lang['Tous'], 'Selected' => 'SELECTED'));
        } else {
            array_push($arrayClub, array('Code' => '*', 'Libelle' => '* - ' . $lang['Tous'], 'Selected' => ''));
        }

        $sql  = "Select c.Code, c.Libelle ";
			$sql .= "From gickp_Club c, gickp_Comite_dep cd ";
			$sql .= "Where c.Code_comite_dep = cd.Code ";
			if ($codeComiteReg != '*')
			{
				$sql .= "And cd.Code_comite_reg = '";
				$sql .= $codeComiteReg;
				$sql .= "' ";
			}
			if ($codeComiteDep != '*')
			{
				$sql .= "And c.Code_comite_dep = '";
				$sql .= $codeComiteDep;
				$sql .= "' ";
			}
			$sql .= "Order By c.Code ";	 
			
			// $bSelectCombo = false;	
			$result = $myBdd->Query($sql);	
			while($row = $myBdd->FetchArray($result)) {
					
				if ($row["Code"] == $codeClub) {
                array_push($arrayClub, array('Code' => $row['Code'], 'Libelle' => $row['Code'] . ' - ' . $row['Libelle'], 'Selected' => 'SELECTED'));
                // $bSelectCombo = true;
            } else {
                array_push($arrayClub, array('Code' => $row['Code'], 'Libelle' => $row['Code'] . ' - ' . $row['Libelle'], 'Selected' => ''));
            }
        }
			
//			if (!$bSelectCombo )
//				$codeClub = '*';
//		}
		$this->m_tpl->assign('arrayClub', $arrayClub);
		
		//Filtres
//		$filtreH = utyGetPost('filtreH', false);
//		$filtreF = utyGetPost('filtreF', false);
//		$filtreH = utyGetPost('filtreH', 1);
//		if($filtreH == 1)
//		{
//			$filtreH = true;
//			$filtreF = false;
//		}else{
//			$filtreH = false;
//			$filtreF = true;
//		}
//		$filtreJ = utyGetPost('filtreJ', false);
//		$filtre21 = utyGetPost('filtre21', false);
//		$filtreTous = utyGetPost('filtreTous', false);
//		if($filtreTous)
//		{
//			$filtreH = false;
//			$filtreF = false;
//			$filtreJ = false;
//			$filtre21 = false;
//		}
//		if($filtreTous) $this->m_tpl->assign('filtreTous', 'checked');
//		if($filtreH) $this->m_tpl->assign('filtreH', 'checked');
//		if($filtreF) $this->m_tpl->assign('filtreF', 'checked');
//		if($filtreJ) $this->m_tpl->assign('filtreJ', 'checked');
//		if($filtre21) $this->m_tpl->assign('filtre21', 'checked');
		
		// Chargement des Equipes Historique ...
		$arrayHistoEquipe = array();
		array_push($arrayHistoEquipe, array('Numero' => '', 'Libelle' => '=> ' . $lang['NOUVELLE_EQUIPE'] . '...'));
		array_push($arrayHistoEquipe, array('Numero' => '', 'Libelle' => ''));
		
		if ($codeComiteReg != '98')
		{
			$sql  = "Select e.Numero, e.Libelle, e.Code_club ";
			$sql .= "From gickp_Equipe e, gickp_Club c, gickp_Comite_dep cd ";
			$sql .= "Where e.Code_club = c.Code ";
			$sql .= "And c.Code_comite_dep = cd.Code ";
			$sql .= "And cd.Code_comite_reg != '98' ";
			if ($codeComiteReg != '*')
			{
				$sql .= "And cd.Code_comite_reg = '";
				$sql .= $codeComiteReg;
				$sql .= "' ";
			}
			if ($codeComiteDep != '*')
			{
				$sql .= "And c.Code_comite_dep = '";
				$sql .= $codeComiteDep;
				$sql .= "' ";
			}
			if ($codeClub != '*')
			{
				$sql .= "And e.Code_club = '";
				$sql .= $codeClub;
				$sql .= "' ";
			}
//			if(!$filtreTous)
//			{
//				$sql .= "And (";
//				($filtreH) ? $sql .= "e.Libelle Not Like '%F' And " : $sql .= "e.Libelle Like '%' And ";
//				($filtreF) ? $sql .= "e.Libelle Like '%F' And " : $sql .= "e.Libelle Not Like '%F' And ";
//				($filtreJ) ? $sql .= "(e.Libelle Like '%JH' Or e.Libelle Like '%JF') And " : $sql .= "e.Libelle Not Like '%JH' And e.Libelle Not Like '%JF' And ";
//				($filtre21) ? $sql .= "e.Libelle Like '%21%'" : $sql .= "e.Libelle Not Like '%21%'";
//				$sql .= ") ";
//			}
			$sql .= "Order By e.Libelle ";
			
			$result = $myBdd->Query($sql);	
			$num_results = $myBdd->NumRows($result);
			array_push($arrayHistoEquipe, array('Numero' => '', 'Libelle' => '==== FRANCE (' . $num_results . ' ' . $lang['equipes'] . ') ====', 'Code_club' => ''));
			while($row = $myBdd->FetchArray($result)) {
				array_push($arrayHistoEquipe, array('Numero' => $row['Numero'], 'Libelle' => $row['Libelle'], 'Code_club' => $row['Code_club']));
			}
			array_push($arrayHistoEquipe, array('Numero' => '', 'Libelle' => '', 'Code_club' => ''));
		}
		if ($codeComiteReg == '98' or $codeComiteReg == '*')
		{
			$sql  = "Select e.Numero, e.Libelle, e.Code_club ";
			$sql .= "From gickp_Equipe e, gickp_Club c, gickp_Comite_dep cd ";
			$sql .= "Where e.Code_club = c.Code ";
			$sql .= "And c.Code_comite_dep = cd.Code ";
			$sql .= "And cd.Code_comite_reg = '98' ";
			if ($codeComiteReg != '*')
			{
				$sql .= "And cd.Code_comite_reg = '";
				$sql .= $codeComiteReg;
				$sql .= "' ";
			}
			if ($codeComiteDep != '*')
			{
				$sql .= "And c.Code_comite_dep = '";
				$sql .= $codeComiteDep;
				$sql .= "' ";
			}
			if ($codeClub != '*')
			{
				$sql .= "And e.Code_club = '";
				$sql .= $codeClub;
				$sql .= "' ";
			}
//			if(!$filtreTous)
//			{
//				$sql .= "And (";
//				($filtreH) ? $sql .= "e.Libelle Not Like '%Women%' And e.Libelle Not Like '%Ladies%' And e.Libelle Not Like '%Dames%' And " : $sql .= "e.Libelle Like '%' And ";
//				($filtreF) ? $sql .= "(e.Libelle Like '%Women%' Or e.Libelle Like '%Ladies%' Or e.Libelle Like '%Dames%') And " : $sql .= "e.Libelle Not Like '%Women%' And e.Libelle Not Like '%Ladies' And e.Libelle Not Like '%Dames%' And ";
//				($filtreJ) ? $sql .= "(e.Libelle Like '%JH%' Or e.Libelle Like '%JF%') And " : $sql .= "e.Libelle Not Like '%JH%' And e.Libelle Not Like '%JF%' And ";
//				($filtre21) ? $sql .= "e.Libelle Like '%21%'" : $sql .= "e.Libelle Not Like '%21%'";
//				$sql .= ") ";
//			}
			$sql .= "Order By e.Libelle ";	 
			
			$result = $myBdd->Query($sql);	
			$num_results = $myBdd->NumRows($result);
			array_push($arrayHistoEquipe, array('Numero' => '', 'Libelle' => '==== INTERNATIONAL (' . $num_results . ' ' . $lang['equipes'] . ') ====', 'Code_club' => ''));
			while($row = $myBdd->FetchArray($result)) {
				array_push($arrayHistoEquipe, array('Numero' => $row['Numero'], 'Libelle' => $row['Libelle'], 'Code_club' => $row['Code_club']));
			}
		}
		$this->m_tpl->assign('arrayHistoEquipe', $arrayHistoEquipe);
	}
	
	function Add()
	{
		$myBdd = new MyBdd();
		
		$codeCompet = $myBdd->RealEscapeString(utyGetPost('competition'));
		$codeSaison = utyGetSaison();
		$libelleEquipe = $myBdd->RealEscapeString(utyGetPost('libelleEquipe'));
		$histoEquipe = utyGetPost('histoEquipe');
		$codeClub = $myBdd->RealEscapeString(utyGetPost('club'));
		$insertValue = '';

		foreach($histoEquipe as $selectValue)
		{
			if ($codeCompet == '' or $codeCompet == '*') {
				$alertMessage .= 'Aucune competition sélectionnée';
				return;
			}
            
			if ((int) $selectValue == 0) {
				// Inscription Manuelle ...
				if ((strlen($libelleEquipe) > 0) && (strlen($codeClub) > 0) ) {
					$sql  = "Insert Into gickp_Equipe (Libelle, Code_club) Values ('";
					$sql .= $libelleEquipe;
					$sql .= "','";
					$sql .= $codeClub;
					$sql .= "') ";
					$myBdd->Query($sql);

					$sql  = "Select LAST_INSERT_ID() Numero ";
					$result = $myBdd->Query($sql);
					if ($myBdd->NumRows($result) == 1) {
							$myBdd->FetchArray($result);
							$selectValue = $myBdd->InsertId();
					}
				}
			}
			
			if ((int) $selectValue == 0) {
                return;
            }

            $sql  = "Insert Into gickp_Competitions_Equipes (Code_compet, Code_saison, Libelle, Code_club, Numero) Select '";
			$sql .= $codeCompet;
			$sql .= "','";
			$sql .= $codeSaison;
			$sql .= "', Libelle, Code_club, Numero ";
			$sql .= "From gickp_Equipe Where Numero = $selectValue";
			
			$myBdd->Query($sql);
			if ($insertValue != '') {
                $insertValue .= ',';
            }
            $insertValue .= $selectValue;
		}	
		$_SESSION['codeCompet'] = $codeCompet;
		$_SESSION['codeComiteReg'] = $myBdd->RealEscapeString(utyGetPost('comiteReg'));
		$_SESSION['codeComiteDep'] = $myBdd->RealEscapeString(utyGetPost('comiteDep'));
		$_SESSION['codeClub'] = $codeClub;

		$myBdd->utyJournal('Ajout equipe', $codeSaison, $codeCompet, 'NULL', 'NULL', 'NULL', $insertValue);
	}
	
	function Add2()
	{
		$myBdd = new MyBdd();
		
		$codeCompet = $myBdd->RealEscapeString(utyGetPost('competition'));
		$codeSaison = utyGetSaison();
		$EquipeNum = $myBdd->RealEscapeString(utyGetPost('EquipeNum'));
		$EquipeNom = $myBdd->RealEscapeString(utyGetPost('EquipeNom'));
		$checkCompo = $myBdd->RealEscapeString(utyGetPost('checkCompo'));
		$plEquipe = $myBdd->RealEscapeString(utyGetPost('plEquipe', ''));
		$tirEquipe = $myBdd->RealEscapeString(utyGetPost('tirEquipe', 0));
		$cltChEquipe = $myBdd->RealEscapeString(utyGetPost('cltChEquipe', 0));
		$cltCpEquipe = $myBdd->RealEscapeString(utyGetPost('cltCpEquipe', 0));
		
		if ($EquipeNum != '')
		{
			if ($codeCompet == '' or $codeCompet == '*')
			{
				$alertMessage .= 'Aucune competition sélectionnée';
				return;
			}
		
			$sql  = "Insert Into gickp_Competitions_Equipes (Code_compet, Code_saison, Libelle, Code_club, Numero, Poule, Tirage, Clt, CltNiveau) Select '";
			$sql .= $codeCompet;
			$sql .= "','";
			$sql .= $codeSaison;
			$sql .= "', Libelle, Code_club, Numero, '".$plEquipe."', '".$tirEquipe."', '".$cltChEquipe."', '".$cltCpEquipe."' ";
			$sql .= "From gickp_Equipe Where Numero = $EquipeNum";
			$myBdd->Query($sql);
			
			$EquipeId = $myBdd->InsertId();
			if($checkCompo != '')
			{
				$checkCompo = explode('-', $checkCompo);
				// Insertion des Joueurs Equipes ...
				$sql  = "Insert Into gickp_Competitions_Equipes_Joueurs (Id_equipe, Matric, Nom, Prenom, Sexe, Categ, Numero, Capitaine) ";
				$sql .= "Select $EquipeId, a.Matric, a.Nom, a.Prenom, a.Sexe, d.Code, a.Numero, a.Capitaine ";
				$sql .= "From gickp_Competitions_Equipes_Joueurs a, gickp_Competitions_Equipes b, gickp_Competitions_Equipes c, gickp_Categorie d, gickp_Liste_Coureur e ";
				$sql .= "Where a.Id_equipe = b.Id ";
				$sql .= "And a.Matric = e.Matric ";
				$sql .= "And a.Id_equipe = c.Id ";
				$sql .= "And b.Numero = $EquipeNum ";
				$sql .= "And b.Code_compet = '".$checkCompo[1]."' And b.Code_saison = '".$checkCompo[0]."' ";
				$sql .= "And " ;
				$sql .= $checkCompo[0];
				$sql .= "-Year(e.Naissance) between d.Age_min And d.Age_max ";
				$myBdd->Query($sql);
			}
		}
		
		
		$myBdd->utyJournal('Ajout equipe', $codeSaison, $codeCompet, 'NULL', 'NULL', 'NULL', $EquipeNom);
	}
	
	function Tirage()
	{
		$myBdd = new MyBdd();
		
		$codeCompet = $myBdd->RealEscapeString(utyGetPost('competition'));
		$codeSaison = utyGetSaison();
		$equipeTirage = $myBdd->RealEscapeString(utyGetPost('equipeTirage'));
		$pouleTirage = $myBdd->RealEscapeString(utyGetPost('pouleTirage'));
		$ordreTirage = $myBdd->RealEscapeString(utyGetPost('ordreTirage'));
		
		$sql  = "Update gickp_Competitions_Equipes ";
		$sql .= "Set Tirage = $ordreTirage, ";
		$sql .= "Poule = '".$pouleTirage."' ";
		$sql .= "Where Code_compet = '";
		$sql .= $codeCompet;
		$sql .= "' And Code_saison = '";
		$sql .= $codeSaison;
		$sql .= "' And Id = $equipeTirage ";	 
	
		$myBdd->Query($sql);
		$myBdd->utyJournal('Tirage au sort', $codeSaison, $codeCompet, 'NULL', 'NULL', 'NULL', $equipeTirage.' -> '.$ordreTirage);
	}

	function Remove()
	{
		$ParamCmd = '';
		if (isset($_POST['ParamCmd'])) {
            $ParamCmd = $_POST['ParamCmd'];
        }

        $arrayParam = explode(',', $ParamCmd);		
		if (count($arrayParam) == 0) {
            return;
        } // Rien à Detruire ...
		
        for ($i=0;$i<count($arrayParam);$i++) {
			if ($i > 0) {
                $listEquipes .= ",";
            }

            $listEquipes .= $arrayParam[$i];
		}

		$myBdd = new MyBdd();
			
		$sql = "Delete From gickp_Competitions_Equipes Where Id In (";
		$sql .= $listEquipes;
		$sql .= ")";
		$myBdd->Query($sql);

		$sql = "Delete From gickp_Competitions_Equipes_Joueurs Where Id_equipe In (";
		$sql .= $listEquipes;
		$sql .= ")";
		$myBdd->Query($sql);

		$myBdd->utyJournal('Suppression  equipes', utyGetSaison(), utyGetPost('codeCompet'), 'NULL', 'NULL', 'NULL', $ParamCmd);
	}
	
	function Duplicate($bDelete)
	{
		$myBdd = new MyBdd();
		$codeCompet = $myBdd->RealEscapeString(utyGetPost('competition'));
		$codeCompetRef = $myBdd->RealEscapeString(utyGetPost('competitionRef'));
		$codeSaison = utyGetSaison();

		if ( (strlen($codeCompet) > 0) && (strlen($codeCompetRef) > 0) ) {
			$myBdd = new MyBdd();
			
			if ($bDelete) {
				// Suppression des Joueurs Equipes 
				$sql  = "Delete FROM gickp_Competitions_Equipes_Joueurs Where Id_equipe In (";
				$sql .= "Select a.Id From gickp_Competitions_Equipes a Where a.Code_compet = '$codeCompet' And a.Code_saison = '$codeSaison' )";
				$myBdd->Query($sql);
		
				// Suppression des Equipes 
				$sql = "Delete From gickp_Competitions_Equipes Where Code_compet = '$codeCompet' And Code_saison = '$codeSaison'  ";
				$myBdd->Query($sql);
			}
			
			// Insertion des Equipes ...
			$sql  = "Insert Into gickp_Competitions_Equipes (Code_compet,Code_saison, Libelle, Code_club, Numero, Id_dupli) ";
			$sql .= "Select '$codeCompet', Code_saison, Libelle, Code_club, Numero, Id ";
			$sql .= "From gickp_Competitions_Equipes Where Code_compet = '$codeCompetRef' And Code_saison = '$codeSaison' ";
			$myBdd->Query($sql);
			
			// Insertion des Joueurs Equipes ...
			$sql  = "Insert Into gickp_Competitions_Equipes_Joueurs (Id_equipe, Matric, Nom, Prenom, Sexe, Categ, Numero, Capitaine) ";
			$sql .= "Select b.Id, a.Matric, a.Nom, a.Prenom, a.Sexe, a.Categ, a.Numero, a.Capitaine ";
			$sql .= "From gickp_Competitions_Equipes_Joueurs a, gickp_Competitions_Equipes b, gickp_Competitions_Equipes c ";
			$sql .= "Where a.Id_equipe = b.Id_dupli ";
			$sql .= "And a.Id_equipe = c.Id ";
			$sql .= "And c.Code_compet = '$codeCompetRef' And c.Code_saison = '$codeSaison' ";
			$myBdd->Query($sql);

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
