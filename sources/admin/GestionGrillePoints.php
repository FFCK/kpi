<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion de la Grille de Points pour compétitions MULTI
class GestionGrillePoints extends MyPageSecure
{
	var $myBdd;

	function Load()
	{
		$myBdd = $this->myBdd;

		// Langue
		$langue = parse_ini_file("../commun/MyLang.ini", true);
		if (utyGetSession('lang') == 'en') {
			$lang = $langue['en'];
		} else {
			$lang = $langue['fr'];
		}

		// Récupération du JSON existant (si modification)
		$existingJson = utyGetRequest('pointsGrid', '');
		$this->m_tpl->assign('existingJson', $existingJson);

		// Parser le JSON pour alimenter le formulaire
		$gridData = [];
		$defaultValue = 0;
		$maxPosition = 10; // Valeur par défaut

		if (!empty($existingJson)) {
			$decoded = json_decode($existingJson, true);
			if (is_array($decoded)) {
				if (isset($decoded['default'])) {
					$defaultValue = $decoded['default'];
					unset($decoded['default']);
				}
				$gridData = $decoded;
				// Déterminer le nombre maximum de positions
				if (!empty($gridData)) {
					$maxPosition = max(max(array_keys($gridData)), 10);
				}
			}
		}

		$this->m_tpl->assign('gridData', $gridData);
		$this->m_tpl->assign('defaultValue', $defaultValue);
		$this->m_tpl->assign('maxPosition', $maxPosition);

		// Traitement de la soumission du formulaire
		$cmd = utyGetPost('Cmd', '');

		if ($cmd == 'GenerateJson') {
			$numPositions = intval(utyGetPost('numPositions', 10));
			$pointsData = [];

			// Récupérer les points pour chaque position
			for ($i = 1; $i <= $numPositions; $i++) {
				$points = utyGetPost('points_' . $i, '');
				if ($points !== '' && $points !== null) {
					$pointsData[strval($i)] = intval($points);
				}
			}

			// Récupérer la valeur par défaut
			$defaultPoints = utyGetPost('defaultPoints', '0');
			if ($defaultPoints !== '' && $defaultPoints !== null) {
				$pointsData['default'] = intval($defaultPoints);
			}

			// Générer le JSON
			$jsonOutput = json_encode($pointsData, JSON_UNESCAPED_UNICODE);
			$this->m_tpl->assign('generatedJson', $jsonOutput);
			$this->m_tpl->assign('showResult', true);

			// Mettre à jour les données du formulaire
			$this->m_tpl->assign('gridData', $pointsData);
			$this->m_tpl->assign('defaultValue', $defaultPoints);
			$this->m_tpl->assign('maxPosition', $numPositions);
		} else {
			$this->m_tpl->assign('generatedJson', '');
			$this->m_tpl->assign('showResult', false);
		}
	}

	function Header()
	{
	}
}


$page = new GestionGrillePoints();
$page->InitPage();
$page->Load();
$page->DisplayPage();

