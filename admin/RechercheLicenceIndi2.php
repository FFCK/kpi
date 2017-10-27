<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Recherche d'un Licencié ...

class RechercheLicenceIndi2 extends MyPageSecure	 
{	
	function Load()
	{
		$myBdd = new MyBdd();
		
		$zoneMatric = utyGetSession('zoneMatric');
		$zoneMatric = utyGetGet('zoneMatric', $zoneMatric);
		$_SESSION['zoneMatric'] = $zoneMatric;
		
		$zoneIdentite = utyGetSession('zoneIdentite');
		$zoneIdentite = utyGetGet('zoneIdentite', $zoneIdentite);
		$_SESSION['zoneIdentite'] = $zoneIdentite;
		
		$this->m_tpl->assign('zoneMatric', $zoneMatric);
		$this->m_tpl->assign('zoneIdentite', $zoneIdentite);
		
		$arrayCoureur = array();
		
		$matricJoueur = utyGetPost('matricJoueur', '');
		$nomJoueur = utyGetPost('nomJoueur', '');
		$prenomJoueur = utyGetPost('prenomJoueur', '');
		$sexeJoueur = utyGetPost('sexeJoueur', '');
		
		$codeComiteReg = utyGetPost('comiteReg', '*');
		$codeComiteDep = utyGetPost('comiteDep', '*');
		$codeClub = utyGetPost('club', '*');
	
		if ($this->OkRecherche()) {
			$sql  = "SELECT a.Matric, a.Nom, a.Prenom, a.Sexe, a.Naissance, a.Numero_club, a.Club, b.Arb "
                    . "FROM gickp_Liste_Coureur a Left Outer Join gickp_Arbitre b On (a.Matric = b.Matric) "
                    . "WHERE a.Matric Is Not Null ";

			if (strlen($matricJoueur) > 0) {
				$sql .= " AND a.Matric = " . $matricJoueur . " ";
			}
			
			if (strlen($nomJoueur) > 0) {
				$sql .= " And a.Nom Like '" . $nomJoueur . "%' ";
			}
			
			if (strlen($prenomJoueur) > 0) {
				$sql .= " And a.Prenom Like '" . $prenomJoueur . "%' ";
			}
				
			if (strlen($sexeJoueur) > 0) {
				$sql .= " And a.Sexe = '" . $sexeJoueur . "' ";
			}
			
			if ( (strlen($codeComiteReg) > 0) && ($codeComiteReg != '*') ) {
				$sql .= " And a.Numero_comite_reg = '" . $codeComiteReg . "' ";
			}
			
			if ( (strlen($codeComiteDep) > 0) && ($codeComiteDep != '*') ) {
				$sql .= " And a.Numero_comite_dept = '" . $codeComiteDep . "' ";
			}
					
			if ( (strlen($codeClub) > 0) && ($codeClub != '*') ) {
				$sql .= " And a.Numero_club = '" . $codeClub . "' ";
			}

			if (isset($_POST['CheckJugeInter'])) {
                $sql .= " And b.Arb = 'Int' ";
			}
			if (isset($_POST['CheckJugeNational'])) {
                $sql .= " And b.Arb = 'Nat' ";
			}
			if (isset($_POST['CheckJugeReg'])) {
                $sql .= " And b.Arb = 'Reg' ";
			}
			if (isset($_POST['CheckJugeOTM'])) {
                $sql .= " And b.Arb = 'OTM' ";
			}
			if (isset($_POST['CheckJugeJO'])) {
                $sql .= " And b.Arb = 'JO' ";
			}
			
			$sql .= " ORDER BY a.Nom, a.Prenom, a.Matric ";
		
			$arrayCoureur = array();
			$result = $myBdd->Query($sql);
			while ($row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC)) {
                $int = 'N';
                $nat = 'N';
                $reg = 'N';
                $otm = 'N';
                $jo = 'N';
                switch ($row['Arb']) {
                    case 'Int' :
                        $int = 'O';
                        break;
                    case 'Nat' :
                        $nat = 'O';
                        break;
                    case 'Reg' :
                        $reg = 'O';
                        break;
                    case 'OTM' :
                        $otm = 'O';
                        break;
                    case 'JO' :
                        $jo = 'O';
                        break;
                }
				array_push($arrayCoureur, array( 'Matric' => $row['Matric'], 'Nom' => ucwords(strtolower($row['Nom'])), 
																				 'Prenom' => ucwords(strtolower($row['Prenom'])), 
																				 'Sexe' => $row['Sexe'], 'Numero_club' => $row['Numero_club'], 'Club' => $row['Club'],
																				 'Naissance' => utyDateUsToFr($row['Naissance']) , 
																				 'Categ' => utyCodeCategorie2($row['Naissance']) ,
																				 'International' => $int ,
																				 'National' =>  $nat , 
																				 'Regional' =>  $reg ,
																				 'OTM' =>  $otm ,
																				 'JO' =>  $jo ,
																				 'Arbitre' => $row['Arb'] ));
																				 
			}
		}
		
		$this->m_tpl->assign('arrayCoureur', $arrayCoureur);
				
		// Chargement des Comites Régionnaux ...
		$sql  = "Select Code, Libelle ";
		$sql .= "From gickp_Comite_reg ";
		$sql .= "Order By Code ";	 
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load");

		$num_results = mysql_num_rows($result);
	
		$arrayComiteReg = array();
		if ('*' == $codeComiteReg) {
            array_push($arrayComiteReg, array('Code' => '*', 'Libelle' => '* - Tous les Comités Régionaux', 'Selection' => 'SELECTED'));
        } else {
            array_push($arrayComiteReg, array('Code' => '*', 'Libelle' => '* - Tous les Comités Régionaux', 'Selection' => ''));
        }

        for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);	  
			
			if (($i == 0) && (strlen($codeComiteReg) == 0)) {
                $codeComiteReg = $row["Code"];
            }

            if ($row["Code"] == $codeComiteReg) {
                array_push($arrayComiteReg, array('Code' => $row['Code'], 'Libelle' => $row['Code'] . " - " . $row['Libelle'], 'Selection' => 'SELECTED'));
            } else {
                array_push($arrayComiteReg, array('Code' => $row['Code'], 'Libelle' => $row['Code'] . " - " . $row['Libelle'], 'Selection' => ''));
            }
        }
		
		$this->m_tpl->assign('arrayComiteReg', $arrayComiteReg);
		
		// Chargement des Comites Departementaux ...
		if (strlen($codeComiteReg) == 0) {
            return;
        }

        $sql  = "Select Code, Libelle ";
		$sql .= "From gickp_Comite_dep ";
		if ('*' != $codeComiteReg)
		{
			$sql .= "Where Code_comite_reg = '";
			$sql .= $codeComiteReg;
			$sql .= "'";
		}	
		$sql .= "Order By Code ";	
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load");
		$num_results = mysql_num_rows($result);
	
		$arrayComiteDep = array();
		if ('*' == $codeComiteDep) {
            array_push($arrayComiteDep, array('Code' => '*', 'Libelle' => '* - Tous les Comités Départementaux', 'Selection' => 'SELECTED'));
        } else {
            array_push($arrayComiteDep, array('Code' => '*', 'Libelle' => '* - Tous les Comités Départementaux', 'Selection' => ''));
        }

        for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);	
			
			if (($i == 0) && (strlen($codeComiteDep) == 0)) {
                $codeComiteDep = $row["Code"];
            }

            if ($row["Code"] == $codeComiteDep) {
                array_push($arrayComiteDep, array('Code' => $row['Code'], 'Libelle' => $row['Code'] . ' - ' . $row['Libelle'], 'Selection' => 'SELECTED'));
            } else {
                array_push($arrayComiteDep, array('Code' => $row['Code'], 'Libelle' => $row['Code'] . ' - ' . $row['Libelle'], 'Selection' => ''));
            }
        }
		$this->m_tpl->assign('arrayComiteDep', $arrayComiteDep);
		
		// Chargement des Clubs ...
		if (strlen($codeComiteDep) == 0) {
            return;
        }

        $sql  = "Select Code, Libelle ";
		$sql .= "From gickp_Club ";
		
		if ('*' != $codeComiteDep)
		{
			$sql .= "Where Code_comite_dep = '";
			$sql .= $codeComiteDep;
			$sql .= "'";
		}
		$sql .= " Order By Code ";	 
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load Club");
		$num_results = mysql_num_rows($result);
	
		$arrayClub = array();
		if ('*' == $codeClub) {
            array_push($arrayClub, array('Code' => '*', 'Libelle' => '* - Tous les Clubs', 'Selection' => 'SELECTED'));
        } else {
            array_push($arrayClub, array('Code' => '*', 'Libelle' => '* - Tous les Clubs', 'Selection' => ''));
        }

        for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);	
			
			if (($i == 0) && (strlen($codeClub) == 0)) {
                $codeClub = $row["Code"];
            }

            if ($row["Code"] == $codeClub) {
                array_push($arrayClub, array('Code' => $row['Code'], 'Libelle' => $row['Code'] . ' - ' . $row['Libelle'], 'Selection' => 'SELECTED'));
            } else {
                array_push($arrayClub, array('Code' => $row['Code'], 'Libelle' => $row['Code'] . ' - ' . $row['Libelle'], 'Selection' => ''));
            }
        }
		$this->m_tpl->assign('arrayClub', $arrayClub);
	}
	
	function OkRecherche()
	{
			$cmd = utyGetPost('Cmd');
			if ($cmd != 'Find') {
            return false;
        }

        if (strlen(utyGetPost('matricJoueur', '')) > 0) {
            return true;
        }
        if (strlen(utyGetPost('nomJoueur', '')) > 0) {
            return true;
        }
        if (strlen(utyGetPost('prenomJoueur', '')) > 0) {
            return true;
        }
        if (strlen(utyGetPost('sexeJoueur', '')) > 0) {
            return true;
        }

        $codeComiteReg = utyGetPost('comiteReg', '');
			$codeComiteDep = utyGetPost('comiteDep', '');
			$codeClub = utyGetPost('club', '');

			if ((strlen($codeComiteReg) > 0) && ($codeComiteReg != '*')) {
            return true;
        }
        if ((strlen($codeComiteDep) > 0) && ($codeComiteDep != '*')) {
            return true;
        }
        if ((strlen($codeClub) > 0) && ($codeClub != '*')) {
            return true;
        }

        if (isset($_POST['CheckJugeInter'])) {
            return true;
        }
        if (isset($_POST['CheckJugeNational'])) {
            return true;
        }
        if (isset($_POST['CheckJugeInterReg'])) {
            return true;
        }
        if (isset($_POST['CheckJugeReg'])) {
            return true;
        }
        if (isset($_POST['CheckJugeOTM'])) {
            return true;
        }
        if (isset($_POST['CheckJugeJO'])) {
            return true;
        }

        return false; // Tout est vide => on n'autorise pas la recherche ...
	}
		
	function RechercheLicenceIndi2()
	{			
	  MyPageSecure::MyPageSecure(10);
	
		$this->SetTemplate("Recherche Licenciés", "", false);
		$this->Load();
		
		if (isset($_POST['matricJoueur'])) {
            $this->m_tpl->assign('matricJoueur', $_POST['matricJoueur']);
        }

        if (isset($_POST['nomJoueur'])) {
            $this->m_tpl->assign('nomJoueur', $_POST['nomJoueur']);
        }

        if (isset($_POST['prenomJoueur'])) {
            $this->m_tpl->assign('prenomJoueur', $_POST['prenomJoueur']);
        }

        if (isset($_POST['sexeJoueur'])) {
            $this->m_tpl->assign('sexeJoueur', $_POST['sexeJoueur']);
        }

        if (utyGetPost('CheckJugeInter', false) == true) {
            $this->m_tpl->assign('CheckJugeInter', 'checked');
        }

        if (utyGetPost('CheckJugeNational', false) == true) {
            $this->m_tpl->assign('CheckJugeNational', 'checked');
        }

        if (utyGetPost('CheckJugeReg', false) == true) {
            $this->m_tpl->assign('CheckJugeReg', 'checked');
        }

        if (utyGetPost('CheckJugeOTM', false) == true) {
            $this->m_tpl->assign('CheckJugeOTM', 'checked');
        }

        if (utyGetPost('CheckJugeJO', false) == true) {
            $this->m_tpl->assign('CheckJugeJO', 'checked');
        }

        $this->DisplayTemplateGlobal('RechercheLicenceIndi2');
	}
}		  	

$page = new RechercheLicenceIndi2();


