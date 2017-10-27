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
		
		$sql  = "Select a.Matric, a.Nom, a.Prenom, a.Sexe, a.Naissance, a.Numero_club, a.Club, a.Origine, ";
		$sql .= "c.International, c.National, c.InterRegional, c.Regional ";
		$sql .= "From gickp_Liste_Coureur a Left Outer Join gickp_Arbitre c On (a.Matric = c.Matric), gickp_Recherche_Licence b ";
		$sql .= "Where a.Matric = b.Matric ";
		$sql .= "And b.Signature = '";
		$sql .= $signature;
		$sql .= "' Order By a.Nom, a.Prenom ";	 
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load");
		$num_results = mysql_num_rows($result);
		
		$arrayCoureur = array();
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);	  
					
			array_push($arrayCoureur, array( 'Matric' => $row['Matric'], 'Nom' => ucwords(strtolower($row['Nom'])), 'Prenom' => ucwords(strtolower($row['Prenom'])), 
																			 'Sexe' => $row['Sexe'], 'Numero_club' => $row['Numero_club'], 'Club' => $row['Club'],
																			 'Naissance' => utyDateUsToFr($row['Naissance']) , 
																			 'Categ' => utyCodeCategorie2($row['Naissance']) ,
																			 'Saison' => $row['Origine'] , 
																			 'International' => $row['International'] ,
																			 'National' =>  $row['National'] , 
																			 'InterRegional' =>  $row['InterRegional'] , 
																			 'Regional' =>  $row['Regional'] ));
																			 
		}
		$this->m_tpl->assign('arrayCoureur', $arrayCoureur);
		
		// Les comites et les clubs ...
		$codeComiteReg = '';
		if (isset($_SESSION['codeComiteReg']))
			$codeComiteReg = $_SESSION['codeComiteReg'];
		if (isset($_POST['codeComiteReg']))
			$codeComiteReg = $_POST['codeComiteReg'];
			
		$codeComiteDep = '';
		if (isset($_SESSION['codeComiteDep']))
			$codeComiteDep = $_SESSION['codeComiteDep'];
		if (isset($_POST['codeComiteDep']))
			$codeComiteDep = $_POST['codeComiteDep'];
			
		$codeClub = '';
		if (isset($_SESSION['codeClub']))
			$codeClub = $_SESSION['codeClub'];
		if (isset($_POST['codeClub']))
			$codeClub = $_POST['codeClub'];

		// Chargement des Comites Régionnaux ...
		$sql  = "Select Code, Libelle ";
		$sql .= "From gickp_Comite_reg ";
		$sql .= "Order By Code ";	 
		
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load");

		$num_results = mysql_num_rows($result);
	
		$arrayComiteReg = array();
		if ('*' == $codeComiteReg)
			array_push($arrayComiteReg, array('Code' => '*', 'Libelle'=> '* - Tous les Comités Régionaux', 'Selection' => 'SELECTED' ) );
		else
			array_push($arrayComiteReg, array('Code' => '*', 'Libelle'=> '* - Tous les Comités Régionaux', 'Selection' => '' ) );
		
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);	  
			
			if ( ($i == 0) && (strlen($codeComiteReg) == 0) )
				$codeComiteReg = $row["Code"];
			
			if ($row["Code"] == $codeComiteReg)
				array_push($arrayComiteReg, array('Code' => $row['Code'], 'Libelle'=> $row['Code']." - ".$row['Libelle'], 'Selection' => 'SELECTED' ) );
			else
				array_push($arrayComiteReg, array('Code' => $row['Code'], 'Libelle'=> $row['Code']." - ".$row['Libelle'], 'Selection' => '' ) );
		}
		
		$this->m_tpl->assign('arrayComiteReg', $arrayComiteReg);
		
		// Chargement des Comites Departementaux ...
		if (strlen($codeComiteReg) == 0)
			return;
			
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
		if ('*' == $codeComiteDep)
			array_push($arrayComiteDep, array('Code' => '*', 'Libelle'=> '* - Tous les Comités Départementaux', 'Selection' => 'SELECTED' ) );
		else
			array_push($arrayComiteDep, array('Code' => '*', 'Libelle'=> '* - Tous les Comités Départementaux', 'Selection' => '' ) );
		
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);	
			
			if ( ($i == 0) && (strlen($codeComiteDep) == 0) )
				$codeComiteDep = $row["Code"];
			
			if ($row["Code"] == $codeComiteDep)
				array_push($arrayComiteDep, array('Code' => $row['Code'], 'Libelle'=> $row['Code'].' - '.$row['Libelle'], 'Selection' => 'SELECTED' ) );
			else
				array_push($arrayComiteDep, array('Code' => $row['Code'], 'Libelle'=> $row['Code'].' - '.$row['Libelle'], 'Selection' => '' ) );
		}
		$this->m_tpl->assign('arrayComiteDep', $arrayComiteDep);
		
		// Chargement des Clubs ...
		if (strlen($codeComiteDep) == 0)
			return;

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
		if ('*' == $codeClub)
			array_push($arrayClub, array('Code' => '*', 'Libelle'=> '* - Tous les Clubs', 'Selection' => 'SELECTED' ) );
		else
			array_push($arrayClub, array('Code' => '*', 'Libelle'=> '* - Tous les Clubs', 'Selection' => '' ) );
		
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);	
			
			if ( ($i == 0) && (strlen($codeClub) == 0) )
				$codeClub = $row["Code"];
			
			if ($row["Code"] == $codeClub)
				array_push($arrayClub, array('Code' => $row['Code'], 'Libelle'=> $row['Code'].' - '.$row['Libelle'], 'Selection' => 'SELECTED' ) );
			else
				array_push($arrayClub, array('Code' => $row['Code'], 'Libelle'=> $row['Code'].' - '.$row['Libelle'], 'Selection' => '' ) );
		}
		$this->m_tpl->assign('arrayClub', $arrayClub);
	}
	
	function Remove()
	{
		$ParamCmd = '';
		if (isset($_POST['ParamCmd']))
			$ParamCmd = $_POST['ParamCmd'];
			
		$arrayParam = split ('[,]', $ParamCmd);		
		if (count($arrayParam) == 0)
			return; // Rien à Detruire ...
			
		$signature = $_SESSION['Signature'];
			
		$sql  = "Delete From gickp_Recherche_Licence Where Signature = '";
		$sql .= $signature;
		$sql .= "' And Matric In (";
		for ($i=0;$i<count($arrayParam);$i++)
		{
			if ($i > 0)
				$sql .= ",";
			
			$sql .= $arrayParam[$i];
		}
		$sql .= ")";
	
		$myBdd = new MyBdd();
		mysql_query($sql, $myBdd->m_link) or die ("Erreur Delete");
	}
	
	function Find()
	{
		$signature = $_SESSION['Signature'];
			
		$sql = "Delete From gickp_Recherche_Licence Where Signature = '";
		$sql .= $signature;
		$sql .= "'";

		$myBdd = new MyBdd();
		$res = mysql_query($sql, $myBdd->m_link) or die ("Erreur Delete");
		
		$sql  = "Insert Into gickp_Recherche_Licence (Signature, Matric) ";
		$sql .= "Select '";
		$sql .= $signature;
		$sql .= "', Matric ";
		$sql .= "From gickp_Liste_Coureur ";
		$sql .= "Where Matric Is Not Null ";
		
		$matricJoueur = '-1';
		if (isset($_POST['matricJoueur']))
			$matricJoueur = $_POST['matricJoueur'];
			
		if (strlen($matricJoueur) > 0)
		{
			$sql .= " And Matric = ";
			$sql .= $matricJoueur;
		}
		
		$nomJoueur = '';
		if (isset($_POST['nomJoueur']))
			$nomJoueur = $_POST['nomJoueur'];
			
		if (strlen($nomJoueur) > 0)
		{
			$sql .= " And Nom Like '";
			$sql .= $nomJoueur;
			$sql .= "%' ";
		}
		
		$prenomJoueur = '';
		if (isset($_POST['prenomJoueur']))
			$prenomJoueur = $_POST['prenomJoueur'];
	
		if (strlen($prenomJoueur) > 0)
		{
			$sql .= " And Prenom Like '";
			$sql .= $prenomJoueur;
			$sql .= "%' ";
		}
					
		$sexeJoueur = '';
		if (isset($_POST['sexeJoueur']))
			$sexeJoueur = $_POST['sexeJoueur'];
			
		if (strlen($sexeJoueur) > 0)
		{
			$sql .= " And Sexe ='";
			$sql .= $sexeJoueur;
			$sql .= "' ";
		}
		
		$codeComiteReg = '';
		if (isset($_POST['codeComiteReg']))
			$codeComiteReg = $_POST['codeComiteReg'];
		if ( (strlen($codeComiteReg) > 0) && ($codeComiteReg != '*') )
		{
			$sql .= " And Numero_comite_reg = '";
			$sql .= $codeComiteReg;
			$sql .= "'";
		}
			
		$codeComiteDep = '';
		if (isset($_POST['codeComiteDep']))
			$codeComiteDep = $_POST['codeComiteDep'];
		if ( (strlen($codeComiteDep) > 0) && ($codeComiteDep != '*') )
		{
			$sql .= " And Numero_comite_dept = '";
			$sql .= $codeComiteDep;
			$sql .= "'";
	}
					
		$codeClub = '';
		if (isset($_POST['codeClub']))
			$codeClub = $_POST['codeClub'];
		if ( (strlen($codeClub) > 0) && ($codeClub != '*') )
		{
			$sql .= " And Numero_club = '";
			$sql .= $codeClub;
			$sql .= "'";
		}
		
		$_SESSION['CheckJugeInter'] = false;
		$_SESSION['CheckJugeNational'] = false;
		$_SESSION['CheckJugeInterReg'] = false;
		$_SESSION['CheckJugeReg'] = false;

		$filterJuge = '';
		if (isset($_POST['CheckJugeInter']))
		{
			if (strlen($filterJuge) == 0)
				$filterJuge .= ' Where ';
			else
				$filterJuge .= ' Or ';
			$filterJuge .= "gickp_Arbitre.International = 'O'";

			$_SESSION['CheckJugeInter'] = true;
		}
		
		if (isset($_POST['CheckJugeNational']))
		{
			if (strlen($filterJuge) == 0)
				$filterJuge .= ' Where ';
			else
				$filterJuge .= ' Or ';
			$filterJuge .= "gickp_Arbitre.National = 'O'";
			
			$_SESSION['CheckJugeNational'] = true;
		}
		
		if (isset($_POST['CheckJugeInterReg']))
		{
			if (strlen($filterJuge) == 0)
				$filterJuge .= ' Where ';
			else
				$filterJuge .= ' Or ';
			$filterJuge .= "gickp_Arbitre.InterRegional = 'O'";
			
			$_SESSION['CheckJugeInterReg'] = true;
		}
		
		if (isset($_POST['CheckJugeReg']))
		{
			if (strlen($filterJuge) == 0)
				$filterJuge .= ' Where ';
			else
				$filterJuge .= ' Or ';
			$filterJuge .= "gickp_Arbitre.Regional = 'O'";
			
			$_SESSION['CheckJugeReg'] = true;
		}
				 
		if (strlen($filterJuge) > 0)
		{
			$sql .= " And Matric In (Select gickp_Arbitre.Matric From gickp_Arbitre $filterJuge) ";
		}
		
		echo $sql;
		$res = mysql_query($sql, $myBdd->m_link) or die ("Erreur Insert !!!");
		
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
		if (strlen($parentUrl) > 0)
		{
			$signature = utyGetSession('Signature');
			$ParamCmd = utyGetPost('ParamCmd');
			
			$sql = "Delete From gickp_Recherche_Licence Where Signature = '";
			$sql .= $signature;
			$sql .= "' And Matric Not In (";
			$sql .= $ParamCmd;
			$sql .= ") ";

			$myBdd = new MyBdd();
			$res = mysql_query($sql, $myBdd->m_link) or die ("Erreur Delete");
			
			$sql = "Update gickp_Recherche_Licence Set Validation = 'O'";
			$res = mysql_query($sql, $myBdd->m_link) or die ("Erreur Update");
					
			header("Location: ".$parentUrl);	
			exit;	
		}
	}
	
	function Cancel()
	{
		$parentUrl = utyGetSession('parentUrl');
		if (strlen($parentUrl) > 0)
		{
			$signature = utyGetSession('Signature');
			
			$sql = "Delete From gickp_Recherche_Licence Where Signature = '";
			$sql .= $signature;
			$sql .= "'";

			$myBdd = new MyBdd();
			$res = mysql_query($sql, $myBdd->m_link) or die ("Erreur Delete");
			
			header("Location: http://".$_SERVER['HTTP_HOST'].$parentUrl);	
			exit;	
		}
	}
	
	function RechercheLicence()
	{			
	  MyPageSecure::MyPageSecure(10);
		
		$Cmd = utyGetPost('Cmd');
		$ParamCmd = utyGetPost('ParamCmd');

		if (strlen($Cmd) > 0)
		{
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

?>
