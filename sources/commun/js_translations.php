<?php
/**
 * Fichier PHP générant dynamiquement les traductions JavaScript
 * basées sur la langue de session de l'utilisateur
 *
 * Ce fichier charge le fichier JSON approprié (fr ou en) et génère
 * un objet JavaScript 'langue' accessible globalement
 *
 * Usage dans les templates Smarty:
 * <script src="commun/js_translations.php"></script>
 */

// Démarrer la session si elle n'est pas déjà démarrée
if (!isset($_SESSION)) {
    session_start();
}

// Définir l'en-tête pour indiquer qu'il s'agit de JavaScript
header('Content-Type: application/javascript; charset=utf-8');

// Récupérer la langue de session (par défaut: français)
$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';

// Vérifier que la langue est valide (fr ou en uniquement)
if (!in_array($lang, ['fr', 'en'])) {
    $lang = 'fr';
}

// Chemin vers le fichier JSON de traductions
$jsonFile = __DIR__ . '/js_translations_' . $lang . '.json';

// Vérifier que le fichier existe
if (!file_exists($jsonFile)) {
    // Fichier non trouvé, utiliser un objet vide
    echo "var langue = {};\n";
    echo "console.error('Translation file not found: " . basename($jsonFile) . "');\n";
    exit;
}

// Charger le contenu du fichier JSON
$translations = file_get_contents($jsonFile);

// Vérifier que le JSON est valide
$decodedTranslations = json_decode($translations, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "var langue = {};\n";
    echo "console.error('Invalid JSON in translation file: " . json_last_error_msg() . "');\n";
    exit;
}

// Générer le code JavaScript
// Créer un objet JavaScript avec toutes les traductions
echo "/**\n";
echo " * Traductions JavaScript - Langue: " . strtoupper($lang) . "\n";
echo " * Généré automatiquement par js_translations.php\n";
echo " * Ne pas modifier directement ce fichier\n";
echo " */\n\n";

echo "// Variable globale contenant toutes les traductions\n";
echo "var langue = " . json_encode($decodedTranslations, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . ";\n\n";

echo "// Variable langue courante (pour compatibilité avec l'ancien système)\n";
echo "var lang = '" . $lang . "';\n";
