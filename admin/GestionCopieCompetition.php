<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion des paramètres d'une Journee

class GestionCopieCompetition extends MyPageSecure	 
{	
	function Load()
	{
		$myBdd = new MyBdd();
		
		$codeSaison = utyGetSaison();
		$codeCompet = utyGetSession('codeCompet');
		
		$saisonOrigine = utyGetSession('saisonOrigine',$codeSaison);
		$saisonOrigine = utyGetPost('saisonOrigine',$saisonOrigine);
		$_SESSION['saisonOrigine'] = $saisonOrigine;
		$this->m_tpl->assign('saisonOrigine', $saisonOrigine);
		
		$competOrigine = utyGetSession('competOrigine',$codeCompet);
		$competOrigine = utyGetPost('competOrigine',$competOrigine);
		$_SESSION['competOrigine'] = $competOrigine;
		$this->m_tpl->assign('competOrigine', $competOrigine);

		$saisonDestination = utyGetSession('saisonDestination',$codeSaison);
		$saisonDestination = utyGetPost('saisonDestination',$saisonDestination);
		$_SESSION['saisonDestination'] = $saisonDestination;
		$this->m_tpl->assign('saisonDestination', $saisonDestination);
		
		$competDestination = utyGetSession('competDestination',$codeCompet);
		$competDestination = utyGetPost('competDestination',$competDestination);
		$_SESSION['competDestination'] = $competDestination;
		$this->m_tpl->assign('competDestination', $competDestination);

		// Liste des saisons
		$arraySaisons = array();
		$sql  = "Select distinct Code From gickp_Saison order by Code ";
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load 1");
		$num_results = mysql_num_rows($result);
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);
			array_push($arraySaisons, array( 'Code' => $row['Code']));
		}
		$this->m_tpl->assign('arraySaisons', $arraySaisons);

		//Liste des codes compétition origine
		$arrayCompetitionOrigine = array();
		$sql  = "Select Code, Libelle, Code_typeclt, Nb_equipes, Qualifies, Elimines, Soustitre, Soustitre2, commentairesCompet ";
		$sql .= "From gickp_Competitions Where Code_saison = $saisonOrigine order by Code ";
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load Compet Orig.");
		$num_results = mysql_num_rows($result);
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);
			array_push($arrayCompetitionOrigine, array( 'Code' => $row['Code'], 'Libelle' => $row['Libelle'] ));
			if ($row['Code'] == $competOrigine)
			{
				$this->m_tpl->assign('codeTypeCltOrigine', $row['Code_typeclt']);
				$this->m_tpl->assign('equipesOrigine', $row['Nb_equipes']);
				$this->m_tpl->assign('qualifiesOrigine', $row['Qualifies']);
				$this->m_tpl->assign('eliminesOrigine', $row['Elimines']);
				$this->m_tpl->assign('Soustitre', $row['Soustitre']);
				$this->m_tpl->assign('Soustitre2', $row['Soustitre2']);
				$this->m_tpl->assign('commentairesCompet', $row['commentairesCompet']);
			}
		}
		$this->m_tpl->assign('arrayCompetitionOrigine', $arrayCompetitionOrigine);
		
		//Liste des codes compétition destination
		$arrayCompetitionDestination = array();
		$sql  = "Select Code, Libelle, Code_typeclt, Nb_equipes, Qualifies, Elimines From gickp_Competitions ";
		$sql .= "Where Code_saison = $saisonDestination ";
		$sql .= utyGetFiltreCompetition('');
		$sql .= "order by Code ";
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load Compet Dest. =><br>   ".$sql);
		$num_results = mysql_num_rows($result);
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);
			array_push($arrayCompetitionDestination, array( 'Code' => $row['Code'], 'Libelle' => $row['Libelle'] ));
			if ($row['Code'] == $competDestination)
			{
				$this->m_tpl->assign('codeTypeCltDestination', $row['Code_typeclt']);
				$this->m_tpl->assign('equipesDestination', $row['Nb_equipes']);
				$this->m_tpl->assign('qualifiesDestination', $row['Qualifies']);
				$this->m_tpl->assign('eliminesDestination', $row['Elimines']);
			}
		}
		$this->m_tpl->assign('arrayCompetitionDestination', $arrayCompetitionDestination);
		
		// Journées
		$arrayJournees = array();
		$listJournees = '';
		$sql  = "Select Id, Code_competition, Code_saison, Phase, Niveau, Date_debut, Date_fin, Nom, Libelle, Lieu, Plan_eau, Departement, Responsable_insc, Responsable_R1, Organisateur, Delegue ";
		$sql .= "From gickp_Journees ";
		$sql .= "Where Code_competition = '".$competOrigine;
		$sql .= "' And Code_saison = $saisonOrigine ";
		$sql .= "Order by Niveau, Phase, Lieu ";
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Select 1ère journee");
		$num_results = mysql_num_rows($result);
		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);
			array_push($arrayJournees, array( 'Niveau' => $row['Niveau'], 'Phase' => $row['Phase'], 'Lieu' => $row['Lieu'] ));
			if($listJournees != '')
				$listJournees .= ',';
			$listJournees .= $row['Id'];
		}
		if ($num_results >= 1)
		{
			$sql2 = "Select Count(Id) nbMatchs From gickp_Matchs Where Id_journee in (".$listJournees.") ";
			$result2 = mysql_query($sql2, $myBdd->m_link) or die ("Erreur Select nb matchs");
			$row2 = mysql_fetch_array($result2);
			
			$this->m_tpl->assign('nbMatchs', $row2['nbMatchs']);
			$this->m_tpl->assign('Date_debut', utyDateUsToFr($row['Date_debut']));
			$this->m_tpl->assign('Date_fin', utyDateUsToFr($row['Date_fin']));
			$this->m_tpl->assign('Nom', $row['Nom']);
			$this->m_tpl->assign('Libelle', $row['Libelle']);
			$this->m_tpl->assign('Lieu', $row['Lieu']);
			$this->m_tpl->assign('Plan_eau', $row['Plan_eau']);
			$this->m_tpl->assign('Departement', $row['Departement']);
			$this->m_tpl->assign('Responsable_insc', $row['Responsable_insc']);
			$this->m_tpl->assign('Responsable_R1', $row['Responsable_R1']);
			$this->m_tpl->assign('Organisateur', $row['Organisateur']);
			$this->m_tpl->assign('Delegue', $row['Delegue']);
		}
		$this->m_tpl->assign('arrayJournees', $arrayJournees);
        
        
        // Chargement des schémas
        $recherche_nb_equipes = utyGetSession('recherche_nb_equipes', 0);
		$recherche_nb_equipes = (int) utyGetPost('recherche_nb_equipes',$recherche_nb_equipes);
		$_SESSION['recherche_nb_equipes'] = $recherche_nb_equipes;
		$this->m_tpl->assign('recherche_nb_equipes', $recherche_nb_equipes);
        
        if($recherche_nb_equipes != 0) {
            $arraySchemas = array();
            $sql  = "SELECT c.*, g.id ";
            $sql .= "FROM gickp_Competitions c, gickp_Competitions_Groupes g ";
            $sql .= "WHERE 1=1 ";
            $sql .= "AND c.Code_typeclt = 'CP' ";
            $sql .= "AND c.Nb_equipes > 0 ";
            $sql .= "AND c.Nb_equipes = $recherche_nb_equipes ";
            $sql .= "AND c.Code_ref = g.Groupe ";
            $sql .= "ORDER BY c.Code_saison DESC, g.Id, COALESCE(c.Code_ref, 'z'), c.Code_tour, c.GroupOrder, c.Code ";	 
            $result = $myBdd->Query($sql);
            $num_results = $myBdd->NumRows($result);

            for ($i=0;$i<$num_results;$i++)
            {
                $row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC);

                $sql2  = "Select Count(m.Id) nbMatchs From gickp_Matchs m, gickp_Journees j ";
                $sql2 .= "Where j.Id = m.Id_journee ";
                $sql2 .= "And j.Code_competition = '".$row["Code"]."' ";
                $sql2 .= "And j.Code_saison = ".$row["Code_saison"]." ";
                $result2 = $myBdd->Query($sql2);
                //$row2 = mysql_fetch_row($result2);
                $row2 = $myBdd->FetchRow($result2, $resulttype=MYSQL_ASSOC);
                $nbMatchs = $row2[0];
                
                if($nbMatchs > 0) {
                    array_push($arraySchemas, array( 'Code' => $row["Code"], 'Code_saison' => $row['Code_saison'], 'Code_niveau' => $row["Code_niveau"], 'Libelle' => $row["Libelle"], 'Soustitre' => $row["Soustitre"], 'Soustitre2' => $row["Soustitre2"],
                                                'Code_ref' => $row["Code_ref"], 'GroupOrder' => $row["GroupOrder"], 'codeTypeClt' => $row["Code_typeclt"], 'Web' => $row["Web"], 
                                                'ToutGroup' => $row["ToutGroup"], 'TouteSaisons' => $row["TouteSaisons"],
                                                'En_actif' => $row['En_actif'], 'Titre_actif' => $row['Titre_actif'], 'Logo_actif' => $row['Logo_actif'], 'Sponsor_actif' => $row['Sponsor_actif'], 'Kpi_ffck_actif' => $row['Kpi_ffck_actif'], 
                                                'Age_min' => $row["Age_min"], 'Age_max' => $row["Age_max"], 'Sexe' => $row["Sexe"], 'Points' => $row["Points"], 'Statut' => $row['Statut'],
                                                'Code_tour' => $row["Code_tour"], 'Nb_equipes' => $row["Nb_equipes"], 'Verrou' => $row["Verrou"], 'Qualifies' => $row["Qualifies"], 'Elimines' => $row["Elimines"],
                                                'commentairesCompet' => $row["commentairesCompet"], 'nbMatchs' => $nbMatchs ));
                }
            }
            $this->m_tpl->assign('arraySchemas', $arraySchemas);
        }
	}
	
	function Ok()
	{
		$myBdd = new MyBdd();
	
		$saisonOrigine = utyGetSession('saisonOrigine',$codeSaison);
		$competOrigine = utyGetSession('competOrigine',$codeCompet);
		$saisonDestination = utyGetSession('saisonDestination',$codeSaison);
		$competDestination = utyGetSession('competDestination',$codeCompet);
		
		(utyGetPost('Date_debut') != '%') ? $Date_debut = utyDateFrToUs(utyGetPost('Date_debut')) : $Date_debut = '%';
		(utyGetPost('Date_fin') != '%') ? $Date_fin = utyDateFrToUs(utyGetPost('Date_fin')) : $Date_fin = '%';
		(utyGetPost('Date_origine') != '%') ? $Date_origine = utyDateFrToUs(utyGetPost('Date_origine')) : $Date_origine = '%';
		$Nom = $myBdd->RealEscapeString(utyGetPost('Nom'));
		$Libelle = $myBdd->RealEscapeString(utyGetPost('Libelle'));
		$Lieu = $myBdd->RealEscapeString(utyGetPost('Lieu'));
		$Plan_eau = $myBdd->RealEscapeString(utyGetPost('Plan_eau'));
		$Departement = utyGetPost('Departement');
		$Responsable_insc = utyGetPost('Responsable_insc');
		$Responsable_R1 = utyGetPost('Responsable_R1');
		$Organisateur = $myBdd->RealEscapeString(utyGetPost('Organisateur'));
		$Delegue = $myBdd->RealEscapeString(utyGetPost('Delegue'));
		
		$init1erTour = utyGetPost('init1erTour');

		if($Date_debut != '%' && $Date_origine != '%')
		{
			$d1 = strtotime($Date_debut.' 00:00:00'); 
			$d2 = strtotime($Date_origine.' 00:00:00'); 
			$diffdate = round(($d1-$d2)/60/60/24);
		}
		else
		{
			$diffdate = 0;
		}
		
		$arrayJournees = array();
		$sql  = "Select Id, Code_competition, Code_saison, Phase, Niveau, Date_debut, Date_fin, Nom, Libelle, Type, Lieu, Plan_eau, Departement, Responsable_insc, Responsable_R1, Organisateur, Delegue ";
		$sql .= "From gickp_Journees ";
		$sql .= "Where Code_competition = '".$competOrigine;
		$sql .= "' And Code_saison = $saisonOrigine ";
		$sql .= "Order by Id ";
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Select journees : ".$sql);
		$num_results = mysql_num_rows($result);

			$sql2a  = "CREATE TEMPORARY TABLE gickp_Tmp (Id int(11) AUTO_INCREMENT, Num int(11) default NULL, PRIMARY KEY  (`Id`)); ";
			mysql_query($sql2a, $myBdd->m_link) or die ("Erreur Insert 2a ".$sql2a);
			$sql3a  = "CREATE TEMPORARY TABLE gickp_Tmp2 (Id int(11) AUTO_INCREMENT, Num int(11) default NULL, PRIMARY KEY  (`Id`)); ";
			mysql_query($sql3a, $myBdd->m_link) or die ("Erreur Insert 3a ".$sql3a);

			$sql2  = "INSERT INTO gickp_Tmp (Num) SELECT DISTINCT Id FROM gickp_Competitions_Equipes ";
			$sql2 .= "WHERE Code_compet = '".$competOrigine."' AND Code_saison = $saisonOrigine ORDER BY Poule, Tirage, Libelle; ";
			mysql_query($sql2, $myBdd->m_link) OR die ("Erreur Insert 2 ".$sql2);

			$sql3  = "INSERT INTO gickp_Tmp2 (Num) SELECT DISTINCT Id FROM gickp_Competitions_Equipes ";
			$sql3 .= "WHERE Code_compet = '".$competOrigine."' AND Code_saison = $saisonOrigine ORDER BY Poule, Tirage, Libelle; ";
			mysql_query($sql3, $myBdd->m_link) or die ("Erreur Insert 3 ".$sql3);

		for ($i=0;$i<$num_results;$i++)
		{
			$row = mysql_fetch_array($result);
			$nextIdJournee = $this->GetNextIdJournee();
			
			$sql1  = "Insert Into gickp_Journees (Id, Code_competition, code_saison, Phase, Niveau, Type, Date_debut, Date_fin, Nom, ";
			$sql1 .= "Libelle, Lieu, Plan_eau, Departement, Responsable_insc, Responsable_R1, Organisateur, Delegue) ";
			$sql1 .= "Values ($nextIdJournee, '";
			$sql1 .= $competDestination;
			$sql1 .= "', ";
			$sql1 .= $saisonDestination;
			$sql1 .= ", '";
			$sql1 .= $row['Phase'];
			$sql1 .= "', '";
			$sql1 .= $row['Niveau'];
			$sql1 .= "', '";
			$sql1 .= $row['Type'];
			$sql1 .= "', '";
			($Date_debut == '%') ? $sql1 .= $row['Date_debut'] : $sql1 .= $Date_debut ;
			$sql1 .= "', '";
			($Date_fin == '%') ? $sql1 .= $row['Date_fin'] : $sql1 .= $Date_fin ;
			$sql1 .= "', '";
			($Nom == '%') ? $sql1 .= $row['Nom'] : $sql1 .= $Nom ;
			$sql1 .= "', '";
			($Libelle == '%') ? $sql1 .= $row['Libelle'] : $sql1 .= $Libelle ;
			$sql1 .= "', '";
			($Lieu == '%') ? $sql1 .= $row['Lieu'] : $sql1 .= $Lieu ;
			$sql1 .= "', '";
			($Plan_eau == '%') ? $sql1 .= $row['Plan_eau'] : $sql1 .= $Plan_eau ;
			$sql1 .= "', '";
			($Departement == '%') ? $sql1 .= $row['Departement'] : $sql1 .= $Departement ;
			$sql1 .= "', '";
			($Responsable_insc == '%') ? $sql1 .= $row['Responsable_insc'] : $sql1 .= $Responsable_insc ;
			$sql1 .= "', '";
			($Responsable_R1 == '%') ? $sql1 .= $row['Responsable_R1'] : $sql1 .= $Responsable_R1 ;
			$sql1 .= "', '";
			($Organisateur == '%') ? $sql1 .= $row['Organisateur'] : $sql1 .= $Organisateur ;
			$sql1 .= "', '";
			($Delegue == '%') ? $sql1 .= $row['Delegue'] : $sql1 .= $Delegue ;
			$sql1 .= "') ";
			
			mysql_query($sql1, $myBdd->m_link) or die ("Erreur Insert 1 : ".$sql1);
			
			$sql4  = "Insert Into gickp_Matchs (Id_journee, Libelle, Date_match, Heure_match, Terrain, Numero_ordre, Type) ";
			$sql4 .= "Select $nextIdJournee, ";
			if ($row['Niveau'] <= 1 && $init1erTour == 'init')
				$sql4 .= "CONCAT('[T', ta.Id, '/T', tb.Id, ']'), ";
			else
				$sql4 .= "m.Libelle, ";
			$sql4 .= "DATE_ADD(m.Date_match,INTERVAL +'$diffdate' DAY), m.Heure_match, m.Terrain, m.Numero_ordre, m.Type ";
			$sql4 .= "FROM gickp_Matchs m ";
			if ($row['Niveau'] <= 1 && $init1erTour == 'init')
				$sql4 .= ", gickp_Tmp ta, gickp_Tmp2 tb ";
			$sql4 .= "WHERE m.Id_journee = ".$row['Id']." ";
			if ($row['Niveau'] <= 1 && $init1erTour == 'init')
			{
				$sql4 .= "AND ta.Num=m.Id_equipeA ";
				$sql4 .= "AND tb.Num=m.Id_equipeB ";
			}
			
			mysql_query($sql4, $myBdd->m_link) or die ("Erreur Insert 4 : ".$sql4);
		
			$myBdd->utyJournal('Ajout journee', $codeSaison, $J_competition, '', $nextIdJournee);
		}

			
		if (isset($_SESSION['ParentUrl']))
		{
			$target = $_SESSION['ParentUrl'];
			header("Location: http://".$_SERVER['HTTP_HOST'].$target);	
			exit;	
		}
	}
	
	function GetNextIdJournee()
	{
		$myBdd = new MyBdd();
		$sql  = "Select max(Id) maxId From gickp_Journees Where Id < 19000001 ";
		$result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Select");
		if (mysql_num_rows($result) == 1)
		{
			$row = mysql_fetch_array($result);	  
			return ((int) $row['maxId'])+1;
		}
		else
		{
			return 1;
		}
	}		
	
	function Cancel()
	{
		if (isset($_SESSION['ParentUrl']))
		{
			$target = $_SESSION['ParentUrl'];
			header("Location: http://".$_SERVER['HTTP_HOST'].$target);	
			exit;	
		}
	}
	
	function GestionCopieCompetition()
	{			
	  MyPageSecure::MyPageSecure(4);
		
		$alertMessage = '';
	  
		$Cmd = '';
		if (isset($_POST['Cmd']))
			$Cmd = $_POST['Cmd'];

		if (strlen($Cmd) > 0)
		{
			if ($Cmd == 'Ok')
				($_SESSION['Profile'] <= 4) ? $this->Ok() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($Cmd == 'Cancel')
				($_SESSION['Profile'] <= 10) ? $this->Cancel() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
				
			if ($alertMessage == '')
			{
				header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);	
				exit;
			}
		}
	
		$this->SetTemplate("Copie de compétition", "Competitions", false);
		$this->Load();
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplate('GestionCopieCompetition');
	}
}		  	

$page = new GestionCopieCompetition();

?>
