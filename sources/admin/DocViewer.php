<?php

/**
 * DocViewer - Visualiseur de documentation markdown
 *
 * Permet de lister et afficher les fichiers markdown de documentation
 * depuis les dossiers DOC/user/ et DOC/developer/
 */

include_once('../commun/MyPage.php');
include_once('../commun/MyBdd.php');
include_once('../commun/MyTools.php');

class DocViewer extends MyPage
{
	var $myBdd;

	function __construct()
	{
		parent::__construct(); // Page publique, pas d'authentification requise
		$this->myBdd = new MyBdd();
	}

	function Load()
	{
		$action = utyGetPost('action', utyGetGet('action', ''));
		$file = utyGetPost('file', utyGetGet('file', ''));
		$category = utyGetPost('category', utyGetGet('category', 'user'));

		// Nettoyer le paramètre file pour éviter les attaques par traversée de répertoire
		$file = str_replace(['..', '\\'], ['', '/'], $file);
		$file = ltrim($file, '/');

		// Valider la catégorie
		if (!in_array($category, ['user', 'developer'])) {
			$category = 'user';
		}

		// Vérifier les droits d'accès à la documentation développeur (profil 1 uniquement)
		$profile = utyGetSession('Profile', 99);
		$canAccessDeveloperDocs = ($profile == 1);

		// Forcer la catégorie 'user' si l'utilisateur n'a pas accès à la doc développeur
		if ($category == 'developer' && !$canAccessDeveloperDocs) {
			$category = 'user';
		}

		// Charger la liste des fichiers markdown
		$userDocs = $this->scanMarkdownFiles('user');
		$devDocs = $canAccessDeveloperDocs ? $this->scanMarkdownFiles('developer') : [];

		$this->m_tpl->assign('userDocs', $userDocs);
		$this->m_tpl->assign('devDocs', $devDocs);
		$this->m_tpl->assign('category', $category);
		$this->m_tpl->assign('currentFile', $file);
		$this->m_tpl->assign('canAccessDeveloperDocs', $canAccessDeveloperDocs);

		// Si un fichier est demandé, charger son contenu
		if (!empty($file)) {
			// Vérifier à nouveau les droits pour le chargement du fichier
			if ($category == 'developer' && !$canAccessDeveloperDocs) {
				$this->m_tpl->assign('markdownContent', '<div class="alert alert-danger">Accès refusé : vous devez être connecté avec le profil Webmaster/Président (profil 1) pour accéder à la documentation développeur.</div>');
				$this->m_tpl->assign('markdownTitle', 'Accès refusé');
				$this->m_tpl->assign('fileExists', false);
			} else {
				$content = $this->loadMarkdownFile($category, $file);
				$this->m_tpl->assign('markdownContent', $content['html']);
				$this->m_tpl->assign('markdownTitle', $content['title']);
				$this->m_tpl->assign('fileExists', $content['exists']);
			}
		} else {
			$this->m_tpl->assign('markdownContent', '');
			$this->m_tpl->assign('markdownTitle', '');
			$this->m_tpl->assign('fileExists', false);
		}
	}

	/**
	 * Scanner un dossier pour trouver tous les fichiers markdown
	 *
	 * @param string $category 'user' ou 'developer'
	 * @return array Liste organisée des fichiers markdown
	 */
	function scanMarkdownFiles($category)
	{
		// DOC est monté directement via volume Docker: ../DOC:/var/www/html/DOC
		$basePath = dirname(__DIR__) . '/DOC/' . $category;

		if (!is_dir($basePath)) {
			return [];
		}

		$files = [];
		$this->scanDirectory($basePath, $basePath, $files);

		// Organiser les fichiers par dossier
		$organized = [];
		foreach ($files as $file) {
			$parts = explode('/', $file['relative']);
			$folder = count($parts) > 1 ? $parts[0] : '_root';

			if (!isset($organized[$folder])) {
				$organized[$folder] = [];
			}
			$organized[$folder][] = $file;
		}

		// Trier les dossiers et les fichiers
		ksort($organized);
		foreach ($organized as &$folderFiles) {
			usort($folderFiles, function($a, $b) {
				// Ordre de priorité pour les fichiers importants
				$priority = [
					'README.md' => 1,
					'NOUVEAUTES.md' => 2,
					'DOCVIEWER_GUIDE.md' => 3,
					'KPI_FUNCTIONALITY_INVENTORY.md' => 4,
				];

				$priorityA = $priority[$a['name']] ?? 999;
				$priorityB = $priority[$b['name']] ?? 999;

				if ($priorityA !== $priorityB) {
					return $priorityA - $priorityB;
				}

				// Si même priorité, tri alphabétique
				return strcasecmp($a['name'], $b['name']);
			});
		}

		return $organized;
	}

	/**
	 * Scanner récursivement un dossier pour trouver les fichiers .md
	 */
	private function scanDirectory($path, $basePath, &$files)
	{
		if (!is_dir($path)) {
			return;
		}

		$items = scandir($path);
		foreach ($items as $item) {
			if ($item === '.' || $item === '..') {
				continue;
			}

			$fullPath = $path . '/' . $item;

			if (is_dir($fullPath)) {
				$this->scanDirectory($fullPath, $basePath, $files);
			} elseif (is_file($fullPath) && strtolower(pathinfo($fullPath, PATHINFO_EXTENSION)) === 'md') {
				$relativePath = str_replace($basePath . '/', '', $fullPath);
				$files[] = [
					'name' => basename($fullPath),
					'path' => $fullPath,
					'relative' => $relativePath,
					'title' => $this->extractTitle($fullPath)
				];
			}
		}
	}

	/**
	 * Extraire le titre d'un fichier markdown (première ligne # ou nom du fichier)
	 */
	private function extractTitle($filePath)
	{
		$handle = fopen($filePath, 'r');
		if (!$handle) {
			return basename($filePath, '.md');
		}

		// Chercher la première ligne de titre
		while (($line = fgets($handle)) !== false) {
			$line = trim($line);
			if (preg_match('/^#\s+(.+)$/', $line, $matches)) {
				fclose($handle);
				return $matches[1];
			}
			// Si on trouve du contenu non vide qui n'est pas un titre, arrêter
			if (!empty($line) && $line[0] !== '#') {
				break;
			}
		}

		fclose($handle);
		return basename($filePath, '.md');
	}

	/**
	 * Charger et convertir un fichier markdown en HTML
	 *
	 * @param string $category 'user' ou 'developer'
	 * @param string $file Chemin relatif du fichier
	 * @return array ['html' => contenu HTML, 'title' => titre, 'exists' => bool]
	 */
	function loadMarkdownFile($category, $file)
	{
		// DOC est monté directement via volume Docker: ../DOC:/var/www/html/DOC
		$basePath = dirname(__DIR__) . '/DOC/' . $category;
		$filePath = $basePath . '/' . $file;

		// Vérifier que le fichier existe et est dans le bon dossier (sécurité)
		$realBase = realpath($basePath);
		$realFile = realpath($filePath);

		if (!$realFile || !$realBase || strpos($realFile, $realBase) !== 0) {
			return [
				'html' => '<div class="alert alert-danger">Fichier non trouvé ou accès refusé.</div>',
				'title' => 'Erreur',
				'exists' => false
			];
		}

		if (!file_exists($realFile)) {
			return [
				'html' => '<div class="alert alert-danger">Fichier non trouvé.</div>',
				'title' => 'Erreur',
				'exists' => false
			];
		}

		$content = file_get_contents($realFile);
		$title = $this->extractTitle($realFile);

		// Convertir le markdown en HTML
		$html = $this->markdownToHtml($content);

		return [
			'html' => $html,
			'title' => $title,
			'exists' => true
		];
	}

	/**
	 * Convertir du markdown en HTML
	 * Utilise Parsedown si disponible, sinon un convertisseur basique
	 */
	private function markdownToHtml($markdown)
	{
		// Essayer d'utiliser Parsedown si disponible
		$parsedownPath = dirname(__DIR__) . '/vendor/erusev/parsedown/Parsedown.php';
		if (file_exists($parsedownPath)) {
			require_once $parsedownPath;
			$parsedown = new Parsedown();
			$parsedown->setSafeMode(true); // Sécurité: échapper le HTML
			return $parsedown->text($markdown);
		}

		// Fallback: convertisseur markdown basique
		return $this->basicMarkdownToHtml($markdown);
	}

	/**
	 * Convertisseur markdown basique (fallback si Parsedown non disponible)
	 */
	private function basicMarkdownToHtml($markdown)
	{
		$html = htmlspecialchars($markdown, ENT_QUOTES, 'UTF-8');

		// Titres
		$html = preg_replace('/^######\s+(.+)$/m', '<h6>$1</h6>', $html);
		$html = preg_replace('/^#####\s+(.+)$/m', '<h5>$1</h5>', $html);
		$html = preg_replace('/^####\s+(.+)$/m', '<h4>$1</h4>', $html);
		$html = preg_replace('/^###\s+(.+)$/m', '<h3>$1</h3>', $html);
		$html = preg_replace('/^##\s+(.+)$/m', '<h2>$1</h2>', $html);
		$html = preg_replace('/^#\s+(.+)$/m', '<h1>$1</h1>', $html);

		// Gras et italique
		$html = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $html);
		$html = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $html);

		// Liens
		$html = preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/', '<a href="$2">$1</a>', $html);

		// Code inline
		$html = preg_replace('/`([^`]+)`/', '<code>$1</code>', $html);

		// Listes
		$html = preg_replace('/^\* (.+)$/m', '<li>$1</li>', $html);
		$html = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $html);

		// Paragraphes
		$html = preg_replace('/\n\n/', '</p><p>', $html);
		$html = '<p>' . $html . '</p>';

		// Nettoyer les paragraphes vides
		$html = preg_replace('/<p>\s*<\/p>/', '', $html);

		return $html;
	}

}

$page = new DocViewer();
$page->SetTemplate("Documentation KPI", "Operations", false);
$page->Load();
$page->DisplayTemplate('DocViewer');
?>
