<?php
include_once('../commun/MyPage.php');

class SelectFeuille extends MyPageSecure 
{	
	function Load($target)
	{
        $this->SetTemplate("Match", "Accueil", false);
		$this->m_tpl->assign('target', $target);
		$this->DisplayTemplateBootstrap('SelectFeuille');
	}

    function SelectFeuille()
    {			
        MyPageSecure::MyPageSecure(10);
        
        $target = utyGetGet('target', 'FeuilleMarque2.php');
        switch ($target) {
            case 'FeuilleMarque3.php':
            case 'FeuilleMarque3stats.php':
                $targetRemastered = $target;
                break;
            default:
                $targetRemastered = 'FeuilleMarque2.php';
                break;
        }
        
        $this->Load($targetRemastered);
    }
}	
    
$page = new SelectFeuille();

