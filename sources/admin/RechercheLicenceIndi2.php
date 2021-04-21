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
        
        $arrayQuery = [];
	
		if ($this->OkRecherche()) {
			$sql = "SELECT a.Matric, a.Nom, a.Prenom, a.Sexe, a.Naissance, a.Numero_club, 
                a.Club, b.arbitre 
                FROM kp_licence a 
                LEFT OUTER JOIN kp_arbitre b ON (a.Matric = b.Matric) 
                WHERE a.Matric IS NOT NULL ";
			if (strlen($matricJoueur) > 0) {
                $sql .= " AND a.Matric = ? ";
                $arrayQuery = array_merge($arrayQuery, [$matricJoueur]);
			}
			if (strlen($nomJoueur) > 0) {
				$sql .= " AND a.Nom LIKE ? ";
                $arrayQuery = array_merge($arrayQuery, [$nomJoueur.'%']);
			}
			if (strlen($prenomJoueur) > 0) {
				$sql .= " AND a.Prenom LIKE ? ";
                $arrayQuery = array_merge($arrayQuery, [$prenomJoueur.'%']);
			}
			if (strlen($sexeJoueur) > 0) {
				$sql .= " AND a.Sexe = ? ";
                $arrayQuery = array_merge($arrayQuery, [$sexeJoueur]);
			}
			if ( (strlen($codeComiteReg) > 0) && ($codeComiteReg != '*') ) {
				$sql .= " AND a.Numero_comite_reg = ? ";
                $arrayQuery = array_merge($arrayQuery, [$codeComiteReg]);
			}
			if ( (strlen($codeComiteDep) > 0) && ($codeComiteDep != '*') ) {
				$sql .= " AND a.Numero_comite_dept = ? ";
                $arrayQuery = array_merge($arrayQuery, [$codeComiteDep]);
			}
			if ( (strlen($codeClub) > 0) && ($codeClub != '*') ) {
				$sql .= " AND a.Numero_club = ? ";
                $arrayQuery = array_merge($arrayQuery, [$codeClub]);
			}
			if (utyGetPost('CheckJugeInter', false)) {
                $sql .= " AND b.arbitre = 'Int' ";
			}
			if (utyGetPost('CheckJugeNational', false)) {
                $sql .= " AND b.arbitre = 'Nat' ";
			}
			if (utyGetPost('CheckJugeReg', false)) {
                $sql .= " AND b.arbitre = 'Reg' ";
			}
			if (utyGetPost('CheckJugeOTM', false)) {
                $sql .= " AND b.arbitre = 'OTM' ";
			}
			if (utyGetPost('CheckJugeJO', false)) {
                $sql .= " AND b.arbitre = 'JO' ";
			}
			$sql .= " ORDER BY a.Nom, a.Prenom, a.Matric ";
		
			$arrayCoureur = array();
            $result = $myBdd->pdo->prepare($sql);
            $result->execute($arrayQuery);
            while ($row = $result->fetch()) {
                $int = 'N';
                $nat = 'N';
                $reg = 'N';
                $otm = 'N';
                $jo = 'N';
                switch ($row['arbitre']) {
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
                array_push($arrayCoureur, array( 'Matric' => $row['Matric'], 
                    'Nom' => ucwords(strtolower($row['Nom'])), 
                    'Prenom' => ucwords(strtolower($row['Prenom'])), 
                    'Sexe' => $row['Sexe'], 'Numero_club' => $row['Numero_club'], 'Club' => $row['Club'],
                    'Naissance' => utyDateUsToFr($row['Naissance']) , 
                    'Categ' => utyCodeCategorie2($row['Naissance']) ,
                    'International' => $int ,
                    'National' =>  $nat , 
                    'Regional' =>  $reg ,
                    'OTM' =>  $otm ,
                    'JO' =>  $jo ,
                    'Arbitre' => $row['arbitre'] ));
																				 
			}
		}
		
		$this->m_tpl->assign('arrayCoureur', $arrayCoureur);
				
		// Chargement des Comites Régionnaux ...
        $sql = "SELECT Code, Libelle 
            FROM kp_cr 
            ORDER BY Code ";	 
        $result = $myBdd->pdo->prepare($sql);
        $result->execute();
        $num_results = $result->rowCount();
	
		$arrayComiteReg = array();
		if ('*' == $codeComiteReg) {
            array_push($arrayComiteReg, array('Code' => '*', 'Libelle' => '* - Tous les Comités Régionaux', 'Selection' => 'SELECTED'));
        } else {
            array_push($arrayComiteReg, array('Code' => '*', 'Libelle' => '* - Tous les Comités Régionaux', 'Selection' => ''));
        }

        for ($i=0;$i<$num_results;$i++) {
			$row = $result->fetch();	  
			
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

		if ('*' != $codeComiteReg) {
            $sql = "SELECT Code, Libelle 
                FROM kp_cd 
                WHERE Code_comite_reg = ? 
                ORDER BY Code ";
            $result = $myBdd->pdo->prepare($sql);
            $result->execute(array($codeComiteReg));
        } else {
            $sql = "SELECT Code, Libelle 
                FROM kp_cd 
                ORDER BY Code ";
            $result = $myBdd->pdo->prepare($sql);
            $result->execute();
        }
        $num_results = $result->rowCount();
			
		$arrayComiteDep = array();
		if ('*' == $codeComiteDep) {
            array_push($arrayComiteDep, array('Code' => '*', 'Libelle' => '* - Tous les Comités Départementaux', 'Selection' => 'SELECTED'));
        } else {
            array_push($arrayComiteDep, array('Code' => '*', 'Libelle' => '* - Tous les Comités Départementaux', 'Selection' => ''));
        }

        for ($i=0;$i<$num_results;$i++) {
			$row = $result->fetch();	  
			
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

		
		if ('*' != $codeComiteDep) {
            $sql = "SELECT Code, Libelle 
                FROM kp_club 
                WHERE Code_comite_dep = ?
                ORDER BY Code ";
            $result = $myBdd->pdo->prepare($sql);
            $result->execute(array($codeComiteDep));
		} else {
            $sql = "SELECT Code, Libelle 
                FROM kp_club 
                ORDER BY Code ";
            $result = $myBdd->pdo->prepare($sql);
            $result->execute();
        }
        $num_results = $result->rowCount();
	
		$arrayClub = array();
		if ('*' == $codeClub) {
            array_push($arrayClub, array('Code' => '*', 'Libelle' => '* - Tous les Clubs', 'Selection' => 'SELECTED'));
        } else {
            array_push($arrayClub, array('Code' => '*', 'Libelle' => '* - Tous les Clubs', 'Selection' => ''));
        }

        for ($i=0;$i<$num_results;$i++) {
			$row = $result->fetch();	  
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

        if (utyGetPost('CheckJugeInter', false)) {
            return true;
        }
        if (utyGetPost('CheckJugeNational', false)) {
            return true;
        }
        if (utyGetPost('CheckJugeInterReg', false)) {
            return true;
        }
        if (utyGetPost('CheckJugeReg', false)) {
            return true;
        }
        if (utyGetPost('CheckJugeOTM', false)) {
            return true;
        }
        if (utyGetPost('CheckJugeJO', false)) {
            return true;
        }

        return false; // Tout est vide => on n'autorise pas la recherche ...
	}
		
	function __construct()
	{			
	    MyPageSecure::MyPageSecure(10);
	
		$this->SetTemplate("Recherche Licenciés", "", false);
		$this->Load();
		
		if (utyGetPost('matricJoueur', false)) {
            $this->m_tpl->assign('matricJoueur', utyGetPost('matricJoueur', false));
        }

        if (utyGetPost('nomJoueur', false)) {
            $this->m_tpl->assign('nomJoueur', utyGetPost('nomJoueur', false));
        }

        if (utyGetPost('prenomJoueur', false)) {
            $this->m_tpl->assign('prenomJoueur', utyGetPost('prenomJoueur', false));
        }

        if (utyGetPost('sexeJoueur', false)) {
            $this->m_tpl->assign('sexeJoueur', utyGetPost('sexeJoueur', false));
        }

        if (utyGetPost('CheckJugeInter', false)) {
            $this->m_tpl->assign('CheckJugeInter', 'checked');
        }

        if (utyGetPost('CheckJugeNational', false)) {
            $this->m_tpl->assign('CheckJugeNational', 'checked');
        }

        if (utyGetPost('CheckJugeReg', false)) {
            $this->m_tpl->assign('CheckJugeReg', 'checked');
        }

        if (utyGetPost('CheckJugeOTM', false)) {
            $this->m_tpl->assign('CheckJugeOTM', 'checked');
        }

        if (utyGetPost('CheckJugeJO', false)) {
            $this->m_tpl->assign('CheckJugeJO', 'checked');
        }

        $this->DisplayTemplateGlobal('RechercheLicenceIndi2');
	}
}		  	

$page = new RechercheLicenceIndi2();


