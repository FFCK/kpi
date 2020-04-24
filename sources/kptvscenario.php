<?php

include_once('commun/MyPage.php');
include_once('commun/MyBdd.php');
include_once('commun/MyTools.php');

// Scenario
	
class Scenario extends MyPageSecure	 
{	
	function Load()
	{
		$myBdd = new MyBdd();
		
		$scenario = utyGetSession('scenario', 100);
		$scenario = utyGetGet('scenario', $scenario);
        $_SESSION['scenario'] = $scenario;
        $this->m_tpl->assign('scenario', $scenario);

		// Matchs
        $sql  = "SELECT * 
            FROM gickp_Tv 
            WHERE Voie > :scenario 
            AND Voie < :scenario + 100 
            ORDER BY Voie ";
        $result = $myBdd->pdo->prepare($sql);
        $result->execute(array(
            ':scenario' => $scenario
        ));
        while ($row = $result->fetch()) {
            $arrayScenes[] = $row;
        }
        $this->m_tpl->assign('arrayScenes', $arrayScenes);
	}
	
	function Update()
	{
        $myBdd = new MyBdd();
        
		for ($i = 1; $i < 10; $i++) {
            $Url = utyGetPost('Url-' . $i, '');
            $intervalle = utyGetPost('intervalle-' . $i, '');
            $Voie = utyGetPost('Voie-' . $i, '');

            $sql = "UPDATE gickp_Tv
                SET `Url` = :Url, 
                intervalle = :intervalle 
                WHERE Voie = :Voie ";
            $result = $myBdd->pdo->prepare($sql);
            $result->execute(array(
                ':Url' => $Url,
                ':intervalle' => $intervalle,
                ':Voie' => $Voie
            ));
        }
		return "Scenario " . utyGetPost('scenario', 'Unknown') . " updated";	
	}    

	// Scenario 		
	function __construct()
    {
        MyPageSecure::MyPageSecure(1);
		
        if (isset($_POST['update']))
		{
			if ($_POST['update'] == 'Update')
				($_SESSION['Profile'] <= 1) ? $alertMessage = $this->Update() : $alertMessage = 'Vous n avez pas les droits pour cette action.';
								
			if ($alertMessage == '')
			{
				header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);	
				exit;
			}
		}

        $this->SetTemplate("KPI Scenario control", "Matchs", true);
		$this->Load();
		$this->m_tpl->assign('AlertMessage', $alertMessage);
		$this->DisplayTemplateNewWide('kptvscenario');
	}
}		  	

$page = new Scenario();
