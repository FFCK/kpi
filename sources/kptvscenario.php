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
            FROM kp_tv 
            WHERE Voie > :scenario 
            AND Voie < :scenario2 + 100 
            ORDER BY Voie ";
        $result = $myBdd->pdo->prepare($sql);
        $result->execute(array(
            ':scenario' => $scenario,
            ':scenario2' => $scenario
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
            $Url = $_POST['Url-' . $i];
            $intervalle = utyGetPost('intervalle-' . $i, '');
            $Voie = utyGetPost('Voie-' . $i, '');

            $sql = "UPDATE kp_tv
                SET `Url` = :Url, 
                intervalle = :intervalle 
                WHERE Voie = :Voie ";
            $result = $myBdd->pdo->prepare($sql);
            $result->execute(array(
                ':Url' => $Url,
                ':intervalle' => $intervalle,
                ':Voie' => $Voie
            ));

            $filename = $_SERVER['DOCUMENT_ROOT'] . "/live/cache/voie_$Voie.json";
            $content = json_encode([
                'voie' => $Voie,
                'url' => urlencode($Url),
                'intervalle' => $intervalle,
                'timestamp' => date('Ymdhis')
            ]);
            file_put_contents($filename, $content);
        }
        return "Scenario " . utyGetPost('scenario', 'Unknown') . " updated";
    }

    // Scenario 		
    function __construct()
    {
        parent::__construct(2);
        $alertMessage = '';

        if (utyGetPost('update', false) == 'Update') {
            ($_SESSION['Profile'] <= 2) ? $alertMessage = $this->Update() : $alertMessage = 'Vous n avez pas les droits pour cette action.';

            if ($alertMessage == '') {
                header("Location: http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
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
