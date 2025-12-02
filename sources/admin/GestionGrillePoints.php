<?php

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

// Gestion de la Grille de Points pour compétitions MULTI
class GestionGrillePoints extends MyPageSecure
{
	var $myBdd;

	function __construct()
	{
		parent::__construct(10);

		$this->myBdd = new MyBdd();

		$this->SetTemplate("Editeur_grille_points_MULTI", "Competitions", false);
		$this->Load();
		$this->DisplayTemplate('GestionGrillePoints');
	}

	function Load()
	{
		$myBdd = $this->myBdd;

		// Langue
		$langue = parse_ini_file("../commun/MyLang.ini", true);
		$langCode = utyGetSession('lang', 'fr');
		if ($langCode == 'en') {
			$lang = $langue['en'];
		} else {
			$lang = $langue['fr'];
			$langCode = 'fr';
		}
		$this->m_tpl->assign('lang', $langCode);

		// Récupération du JSON existant (si modification)
		$existingJson = utyGetGet('pointsGrid', '');

		// Décoder les entités HTML (les guillemets sont encodés en &quot;)
		$existingJson = html_entity_decode($existingJson, ENT_QUOTES, 'UTF-8');

		$this->m_tpl->assign('existingJson', $existingJson);

		// Parser le JSON pour alimenter le formulaire
		$gridData = new stdClass(); // Objet vide au lieu de tableau
		$defaultValue = 0;
		$maxPosition = 10; // Valeur par défaut

		if (!empty($existingJson)) {
			$decoded = json_decode($existingJson, false); // false pour obtenir un objet
			if (is_object($decoded)) {
				if (isset($decoded->default)) {
					$defaultValue = $decoded->default;
					unset($decoded->default);
				}
				$gridData = $decoded;
				// Déterminer le nombre maximum de positions
				$keys = array_keys((array)$gridData);
				if (!empty($keys)) {
					$maxPosition = max(max($keys), 10);
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
}

$page = new GestionGrillePoints();

