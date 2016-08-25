<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

// Details
class Details extends MyPage	 
{	
	function Load()
	{
		$myBdd = new MyBdd();
		$codeCompetGroup = utyGetSession('codeCompetGroup', 'N1H');
		$codeCompetGroup = utyGetPost('Group', $codeCompetGroup);
		$codeCompetGroup = utyGetGet('Group', $codeCompetGroup);
		$this->m_tpl->assign('codeCompetGroup', $codeCompetGroup);
		$_SESSION['codeCompetGroup'] = $codeCompetGroup;

		$codeSaison = utyGetSaison();
		$codeSaison = utyGetPost('Saison', $codeSaison);
		$codeSaison = utyGetGet('Saison', $codeSaison);
		$this->m_tpl->assign('Saison', $codeSaison);
		$_SESSION['Saison'] = $codeSaison;
        
		$idSelJournee = utyGetSession('idSelJournee', '*');
		$idSelJournee = utyGetPost('J', $idSelJournee);
		$idSelJournee = utyGetGet('J', $idSelJournee);
		$_SESSION['idSelJournee'] = $idSelJournee;
		$this->m_tpl->assign('idSelJournee', $idSelJournee);

		$idSelCompet = utyGetSession('idSelCompet', '*');
		$idSelCompet = utyGetPost('Compet', $idSelCompet);
		$idSelCompet = utyGetGet('Compet', $idSelCompet);
		$_SESSION['idSelCompet'] = $idSelCompet;
		$this->m_tpl->assign('idSelCompet', $idSelCompet);
        
        $type = utyGetGet('typ','CHPT');
        $this->m_tpl->assign('type', $type);

        if($type == 'CHPT'){
            // Chargement des journées
            $sql  = "SELECT j.Id Id_journee, j.Libelle Libelle_journee, j.*, c.Libelle Libelle_compet, c.* "
                    . "FROM gickp_Journees j, gickp_Competitions c "
                    . "WHERE j.Code_competition = '$idSelCompet' "
                    . "AND j.Code_saison = $codeSaison "
                    . "AND j.Code_competition = c.Code "
                    . "AND j.Code_saison = c.Code_saison "
                    . "AND j.Publication = 'O' "
                    . "AND c.Publication = 'O' "
                    . "ORDER BY j.Code_competition, j.Date_debut, j.Lieu ";
            $arrayListJournees = array();
            $journee = array();
            $result = $myBdd->Query($sql);
            while ($row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC)){
                if($row['Id_journee'] == $idSelJournee || $idSelJournee == '*'){
                    $row['Selected'] = true;
                    $journee[] = $row;
                }else{
                    $row['Selected'] = false;
                }
                array_push($arrayListJournees, $row);            
            }
            $this->m_tpl->assign('journee', $journee);
            $this->m_tpl->assign('arrayListJournees', $arrayListJournees);
        }else{
            // Chargement des Compétitions ...
            $sql  = "SELECT j.Id Id_journee, j.Libelle Libelle_journee, j.*, c.Libelle Libelle_compet, c.*, 0 Selected "
                    . "FROM gickp_Journees j, gickp_Competitions c "
                    . "WHERE 1 "
                    . "AND j.Code_saison = $codeSaison "
                    . "AND j.Code_competition = c.Code "
                    . "AND j.Code_saison = c.Code_saison "
                    . "AND j.Publication = 'O' "
                    . "AND c.Publication = 'O' "
                    . "AND c.Code_ref = '$codeCompetGroup' "
                    . "GROUP BY c.Code "
                    . "ORDER BY c.Code_niveau, COALESCE(c.Code_ref, 'z'), c.GroupOrder, c.Code_tour, c.Code ";	 
            $arrayListJournees = array();
            $result = $myBdd->Query($sql);
            while ($row = $myBdd->FetchArray($result, $resulttype=MYSQL_ASSOC)){
                if($row['Code_competition'] == $idSelCompet){
                    $row['Selected'] == true;
                    $journee[] = $row;
                }else{
                    $row['Selected'] == false;
                }
                array_push($arrayListJournees, $row);            
            }
            $this->m_tpl->assign('journee', $journee);
            $this->m_tpl->assign('arrayListJournees', $arrayListJournees);
            
            // Chargement des Equipes ...
            $arrayEquipe = array();
            if (strlen($idSelCompet) > 0 && $idSelCompet != '*')
            { 
                $sql  = "Select ce.Id, ce.Libelle, ce.Code_club, ce.Numero, ce.Poule, ce.Tirage, c.Code_comite_dep  ";
                $sql .= "From gickp_Competitions_Equipes ce, gickp_Club c ";
                $sql .= "Where ce.Code_compet = '";
                $sql .= $idSelCompet;
                $sql .= "' And ce.Code_saison = '";
                $sql .= $codeSaison;
                $sql .= "' And ce.Code_club = c.Code ";	 
                $sql .= " Order By ce.Poule, ce.Tirage, ce.Libelle, ce.Id ";

                $result = mysql_query($sql, $myBdd->m_link) or die ("Erreur Load => ".$sql);
                $num_results = mysql_num_rows($result);

                for ($i=0;$i<$num_results;$i++)
                {
                    $row = mysql_fetch_array($result);	  
                    if (strlen($row['Code_comite_dep']) > 3)
                        $row['Code_comite_dep'] = 'FRA';
                    if ($row['Tirage'] != 0 or $row['Poule'] != '')
                        $this->m_tpl->assign('Tirage', 'ok');
                    array_push($arrayEquipe, array('Id' => $row['Id'], 'Libelle' => $row['Libelle'], 'Code_club' => $row['Code_club'], 'Numero' => $row['Numero'], 'Poule' => $row['Poule'], 'Tirage' => $row['Tirage'], 'Code_comite_dep' => $row['Code_comite_dep'] ));
                }
            }	
            $this->m_tpl->assign('arrayEquipe', $arrayEquipe);
        }

	}
		

	function Details()
	{			
	  MyPage::MyPage();
		
		$alertMessage = '';
		
		$Cmd = '';
		if (isset($_POST['Cmd']))
			$Cmd = $_POST['Cmd'];

		if (strlen($Cmd) > 0)
		{
			if ($alertMessage == '')
			{
				header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);	
				exit;
			}
		}

		$this->SetTemplate("Details", "Calendrier", true);
		$this->Load();
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplateNew('kpdetails');
	}
}		  	

$page = new Details();
