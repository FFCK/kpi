<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Recherche des Licenciés 

class RechercheLicence extends MyPageSecure	 
{	
	function Load()
	{
		$myBdd = new MyBdd();
		
		// Sous-Titre ...
		if (isset($_SESSION['infoEquipe']))
			$this->m_tpl->assign('headerSubTitle', 'Recherche Licences pour Equipe : '.$_SESSION['infoEquipe']);
			
		// Chargement des Coureurs de la Recherche ...
		$arrayJoueur = array();
		
		$signature = '';
		if (isset($_SESSION['Signature']))
			$signature = $_SESSION['Signature'];
		
		$sql = "SELECT a.Matric, a.Nom, a.Prenom, a.Sexe, a.Naissance, a.Numero_club, 
			a.Club, a.Origine, c.international, c.national, c.interregional, c.regional 
			FROM kp_recherche_licence b, kp_licence a 
			LEFT OUTER JOIN kp_arbitre c ON (a.Matric = c.Matric) 
			WHERE a.Matric = b.Matric 
			AND b.Signature = ? 
			ORDER BY a.Nom, a.Prenom ";	 
		$arrayCoureur = array();
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($signature));
		while ($row = $result->fetch()) {
			array_push($arrayCoureur, array( 'Matric' => $row['Matric'], 
				'Nom' => ucwords(strtolower($row['Nom'])), 
				'Prenom' => ucwords(strtolower($row['Prenom'])), 
				'Sexe' => $row['Sexe'], 'Numero_club' => $row['Numero_club'], 
				'Club' => $row['Club'],
				'Naissance' => utyDateUsToFr($row['Naissance']) , 
				'Categ' => utyCodeCategorie2($row['Naissance']) ,
				'Saison' => $row['Origine'] , 
				'International' => $row['international'] ,
				'National' =>  $row['national'] , 
				'InterRegional' =>  $row['interregional'] , 
				'Regional' =>  $row['regional'] ));
		}
		$this->m_tpl->assign('arrayCoureur', $arrayCoureur);
		
		// Les comites et les clubs ...
		$codeComiteReg = utyGetSession('codeComiteReg', '*');
		$codeComiteReg = utyGetPost('codeComiteReg', $codeComiteReg);

		$codeComiteDep = utyGetSession('codeComiteDep', '*');
		$codeComiteDep = utyGetPost('codeComiteDep', $codeComiteDep);

		$codeClub = utyGetSession('codeClub', '*');
        $codeClub = utyGetPost('codeClub', $codeClub);

		// Chargement des Comites Régionnaux ...
		$sql = "SELECT Code, Libelle 
			FROM kp_cr 
			ORDER BY Code ";	 
		$result = $myBdd->pdo->prepare($sql);
		$result->execute();
		$arrayComiteReg = array();
		$i = 0;
		
		if ('*' == $codeComiteReg) {
			array_push($arrayComiteReg, array('Code' => '*', 'Libelle'=> '* - Tous les Comités Régionaux', 'Selection' => 'SELECTED' ) );
		} else {
			array_push($arrayComiteReg, array('Code' => '*', 'Libelle'=> '* - Tous les Comités Régionaux', 'Selection' => '' ) );
		}

		while ($row = $result->fetch()) {
			// if ($i == 0 && strlen($codeComiteReg) == 0) {
			// 	$codeComiteReg = $row["Code"];
			// }

			if ($row["Code"] == $codeComiteReg) {
				array_push($arrayComiteReg, array('Code' => $row['Code'], 'Libelle'=> $row['Code']." - ".$row['Libelle'], 'Selection' => 'SELECTED' ) );
			} else {
				array_push($arrayComiteReg, array('Code' => $row['Code'], 'Libelle'=> $row['Code']." - ".$row['Libelle'], 'Selection' => '' ) );
			}
			$i ++;
		}
		
		$this->m_tpl->assign('arrayComiteReg', $arrayComiteReg);
		
		// Chargement des Comites Departementaux ...
		if (strlen($codeComiteReg) == 0)
			return;
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
		$i = 0;

		$arrayComiteDep = array();
	
		if ('*' == $codeComiteDep) {
			array_push($arrayComiteDep, array('Code' => '*', 'Libelle'=> '* - Tous les Comités Départementaux', 'Selection' => 'SELECTED' ) );
		} else {
			array_push($arrayComiteDep, array('Code' => '*', 'Libelle'=> '* - Tous les Comités Départementaux', 'Selection' => '' ) );
		}

		while ($row = $result->fetch()) {
			// if ($i == 0 && strlen($codeComiteDep) == 0) {
			// 	$codeComiteDep = $row["Code"];
			// }
			
			if ($row["Code"] == $codeComiteDep) {
				array_push($arrayComiteDep, array('Code' => $row['Code'], 'Libelle'=> $row['Code'].' - '.$row['Libelle'], 'Selection' => 'SELECTED' ) );
			} else {
				array_push($arrayComiteDep, array('Code' => $row['Code'], 'Libelle'=> $row['Code'].' - '.$row['Libelle'], 'Selection' => '' ) );
			}
			$i ++;
		}
		$this->m_tpl->assign('arrayComiteDep', $arrayComiteDep);
		
		// Chargement des Clubs ...
		// if (strlen($codeComiteDep) == 0)
		// 	return;

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
		$i = 0;
		
		$arrayClub = array();
	
		if ('*' == $codeClub) {
			array_push($arrayClub, array('Code' => '*', 'Libelle'=> '* - Tous les Clubs', 'Selection' => 'SELECTED' ) );
		} else {
			array_push($arrayClub, array('Code' => '*', 'Libelle'=> '* - Tous les Clubs', 'Selection' => '' ) );
		}
		
		while ($row = $result->fetch()) {
			// if ($i == 0 && strlen($codeClub) == 0) {
			// 	$codeClub = $row["Code"];
			// }
			
			if ($row["Code"] == $codeClub) {
				array_push($arrayClub, array('Code' => $row['Code'], 'Libelle'=> $row['Code'].' - '.$row['Libelle'], 'Selection' => 'SELECTED' ) );
			} else {
				array_push($arrayClub, array('Code' => $row['Code'], 'Libelle'=> $row['Code'].' - '.$row['Libelle'], 'Selection' => '' ) );
			}
			$i ++;
		}
		$this->m_tpl->assign('arrayClub', $arrayClub);
	}
	
	function Remove()
	{
		$ParamCmd = utyGetPost('ParamCmd', '');
		
		$arrayParam = explode(',', $ParamCmd);		
		if (count($arrayParam) == 0)
		return; // Rien à Detruire ...
		
		$myBdd = new MyBdd();
		$signature = $_SESSION['Signature'];
			
		$in = str_repeat('?,', count($arrayParam) - 1) . '?';
		$sql = "DELETE FROM kp_recherche_licence 
			WHERE `Signature` = ? 
			AND Matric IN ($in) ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array_merge([$signature], $arrayParam));
	}
	
	function Find()
	{
		$myBdd = new MyBdd();
		$signature = $_SESSION['Signature'];
			
		$sql = "DELETE FROM kp_recherche_licence 
			WHERE `Signature` = ? ";
		$result = $myBdd->pdo->prepare($sql);
		$result->execute(array($signature));
		
		$sql = "INSERT INTO kp_recherche_licence (`Signature`, Matric) 
			SELECT ?, lc.Matric 
			FROM kp_licence lc 
			LEFT OUTER JOIN kp_arbitre a ON (lc.Matric = a.Matric)
			WHERE lc.Matric IS NOT NULL ";
		$arrayQuery = [$signature];

		$matricJoueur = utyGetPost('matricJoueur', '-1');
		if (strlen($matricJoueur) > 0) {
			$sql .= "AND lc.Matric = ? ";
			$arrayQuery = array_merge($arrayQuery, [$matricJoueur]);
		}
		
		$nomJoueur = utyGetPost('nomJoueur', '');
		if (strlen($nomJoueur) > 0) {
			$sql .= "AND lc.Nom LIKE ? ";
			$arrayQuery = array_merge($arrayQuery, [$nomJoueur.'%']);
		}
		
		$prenomJoueur = utyGetPost('prenomJoueur', '');
		if (strlen($prenomJoueur) > 0) {
			$sql .= "AND lc.Prenom LIKE ? ";
			$arrayQuery = array_merge($arrayQuery, [$prenomJoueur.'%']);
		}
		
		$sexeJoueur = utyGetPost('sexeJoueur', '');
		if (strlen($sexeJoueur) > 0) {
			$sql .= "AND lc.Sexe = ? ";
			$arrayQuery = array_merge($arrayQuery, [$sexeJoueur]);
		}
		
		$codeComiteReg = utyGetPost('codeComiteReg', '');
		if (strlen($codeComiteReg) > 0 && $codeComiteReg != '*') {
			$sql .= "AND lc.Numero_comite_reg = ? ";
			$arrayQuery = array_merge($arrayQuery, [$codeComiteReg]);
		}
		
		$codeComiteDep = utyGetPost('codeComiteDep', '');
		if (strlen($codeComiteDep) > 0 && $codeComiteDep != '*') {
			$sql .= "AND lc.Numero_comite_dept = ? ";
			$arrayQuery = array_merge($arrayQuery, [$codeComiteDep]);
		}
		
		$codeClub = utyGetPost('codeClub', '');
		if (strlen($codeClub) > 0 && $codeClub != '*') {
			$sql .= "AND lc.Numero_club = ? ";
			$arrayQuery = array_merge($arrayQuery, [$codeClub]);
		}
		
		$_SESSION['CheckJugeInter'] = false;
		$_SESSION['CheckJugeNational'] = false;
		$_SESSION['CheckJugeInterReg'] = false;
		$_SESSION['CheckJugeReg'] = false;

		$CheckJugeInter = utyGetPost('CheckJugeInter', '');
		if (strlen($CheckJugeInter) > 0) {
			$sql .= "AND a.international = 'O' ";
			$_SESSION['CheckJugeInter'] = true;
		}
		
		$CheckJugeNational = utyGetPost('CheckJugeNational', '');
		if (strlen($CheckJugeNational) > 0) {
			$sql .= "AND a.national = 'O' ";
			$_SESSION['CheckJugeNational'] = true;
		}
		
		$CheckJugeInterReg = utyGetPost('CheckJugeInterReg', '');
		if (strlen($CheckJugeInterReg) > 0) {
			$sql .= "AND a.interregional = 'O' ";
			$_SESSION['CheckJugeInterReg'] = true;
		}
		
		$CheckJugeReg = utyGetPost('CheckJugeReg', '');
		if (strlen($CheckJugeReg) > 0) {
			$sql .= "AND a.regional = 'O' ";
			$_SESSION['CheckJugeReg'] = true;
		}
		
		$result = $myBdd->pdo->prepare($sql);
		$result->execute($arrayQuery);
		
		$_SESSION['matricJoueur'] = $matricJoueur;
		$_SESSION['nomJoueur'] = $nomJoueur;
		$_SESSION['prenomJoueur'] = $prenomJoueur;
		$_SESSION['sexeJoueur'] = $sexeJoueur;

		$_SESSION['codeComiteReg'] = $codeComiteReg;
		$_SESSION['codeComiteDep'] = $codeComiteDep;
		$_SESSION['codeClub'] = $codeClub;
	}
	
	function Ok()
	{
		$parentUrl = utyGetSession('parentUrl');
		if (strlen($parentUrl) > 0) {
			$signature = utyGetSession('Signature');
			$ParamCmd = utyGetPost('ParamCmd');
			$arrayParam = explode(',', $ParamCmd);
			
			$myBdd = new MyBdd();

			$in = str_repeat('?,', count($arrayParam) - 1) . '?';
			$sql = "DELETE FROM kp_recherche_licence 
				WHERE `Signature` = ? 
				AND Matric NOT IN ($in) ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array_merge([$signature], $arrayParam));
			
			$sql = "UPDATE kp_recherche_licence 
				SET `Validation` = 'O' ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute();
					
			header("Location: ".$parentUrl);	
			exit;	
		}
	}
	
	function Cancel()
	{
		$parentUrl = utyGetSession('parentUrl');
		if (strlen($parentUrl) > 0) {
			$signature = utyGetSession('Signature');
			
			$myBdd = new MyBdd();

			$sql = "DELETE FROM kp_recherche_licence 
				WHERE `Signature` = ? ";
			$result = $myBdd->pdo->prepare($sql);
			$result->execute(array($signature));
			
			header("Location: http://".$_SERVER['HTTP_HOST'].$parentUrl);	
			exit;	
		}
	}
	
	function __construct()
	{			
	  	MyPageSecure::MyPageSecure(10);
		
		$Cmd = utyGetPost('Cmd');
		$ParamCmd = utyGetPost('ParamCmd');

		if (strlen($Cmd) > 0) {
			if ($Cmd == 'Ok')
				$this->Ok();
				
			if ($Cmd == 'Cancel')
				$this->Cancel();
				
			if ($Cmd == 'Remove')
				$this->Remove();
				
			if ($Cmd == 'Find')
				$this->Find();
				
			header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);	
			exit;	
		}

		$this->SetTemplate("Recherche des Licenciés", "", false);
	
		$this->Load();
		
		if (isset($_SESSION['matricJoueur']))
			$this->m_tpl->assign('matricJoueur', $_SESSION['matricJoueur']);
			
		if (isset($_SESSION['nomJoueur']))
			$this->m_tpl->assign('nomJoueur', $_SESSION['nomJoueur']);
			
		if (isset($_SESSION['prenomJoueur']))
			$this->m_tpl->assign('prenomJoueur', $_SESSION['prenomJoueur']);
		
		if (isset($_SESSION['sexeJoueur']))
			$this->m_tpl->assign('sexeJoueur', $_SESSION['sexeJoueur']);
			
		if (utyGetSession('CheckJugeInter', false) == true)
			$this->m_tpl->assign('CheckJugeInter', 'checked');
			
		if (utyGetSession('CheckJugeNational', false) == true)
			$this->m_tpl->assign('CheckJugeNational', 'checked');
			
		if (utyGetSession('CheckJugeInterReg', false) == true)
			$this->m_tpl->assign('CheckJugeInterReg', 'checked');
			
		if (utyGetSession('CheckJugeReg', false) == true)
			$this->m_tpl->assign('CheckJugeReg', 'checked');
			
		$this->DisplayTemplate('RechercheLicence');
	}
}		  	

$page = new RechercheLicence();
