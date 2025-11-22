<?php
/**
 * Script de comparaison des fichiers de traduction
 * Compare MyLang.conf et MyLang.ini pour identifier les différences
 */

// Fonction pour parser un fichier .ini/.conf
function parseTranslationFile($filepath) {
    $content = file_get_contents($filepath);
    $lines = explode("\n", $content);

    $translations = [];
    $currentLang = null;

    foreach ($lines as $line) {
        $line = trim($line);

        // Ignorer les commentaires et lignes vides
        if (empty($line) || $line[0] === '#' || $line[0] === ';') {
            continue;
        }

        // Détection de section de langue
        if (preg_match('/^\[(\w+)\]$/', $line, $matches)) {
            $currentLang = $matches[1];
            if (!isset($translations[$currentLang])) {
                $translations[$currentLang] = [];
            }
            continue;
        }

        // Extraction des paires clé=valeur
        if ($currentLang && strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Retirer les guillemets au début et à la fin
            $value = trim($value, '"');
            $value = trim($value, '\'');

            $translations[$currentLang][$key] = $value;
        }
    }

    return $translations;
}

// Charger les deux fichiers
$conf = parseTranslationFile(__DIR__ . '/MyLang.conf');
$ini = parseTranslationFile(__DIR__ . '/MyLang.ini');

// Langues à comparer
$languages = ['fr', 'en'];

echo "===========================================\n";
echo "ANALYSE DES FICHIERS DE TRADUCTION\n";
echo "===========================================\n\n";

// Statistiques générales
echo "STATISTIQUES GENERALES :\n";
echo "------------------------\n";
foreach ($languages as $lang) {
    $confCount = isset($conf[$lang]) ? count($conf[$lang]) : 0;
    $iniCount = isset($ini[$lang]) ? count($ini[$lang]) : 0;
    echo "Langue [$lang] :\n";
    echo "  - MyLang.conf : $confCount clés\n";
    echo "  - MyLang.ini  : $iniCount clés\n";
    echo "\n";
}

// Clés communes aux deux fichiers
echo "\n===========================================\n";
echo "CLES COMMUNES AVEC TRADUCTIONS DIFFERENTES\n";
echo "===========================================\n\n";

$differences = [];
foreach ($languages as $lang) {
    echo "Langue : [$lang]\n";
    echo "------------------------\n";

    if (!isset($conf[$lang]) || !isset($ini[$lang])) {
        echo "  Langue non trouvée dans l'un des fichiers\n\n";
        continue;
    }

    $commonKeys = array_intersect(array_keys($conf[$lang]), array_keys($ini[$lang]));

    $diffCount = 0;
    foreach ($commonKeys as $key) {
        $confValue = $conf[$lang][$key];
        $iniValue = $ini[$lang][$key];

        // Comparer les valeurs (normaliser les espaces)
        $confNorm = preg_replace('/\s+/', ' ', trim($confValue));
        $iniNorm = preg_replace('/\s+/', ' ', trim($iniValue));

        if ($confNorm !== $iniNorm) {
            $diffCount++;
            if (!isset($differences[$key])) {
                $differences[$key] = [];
            }
            $differences[$key][$lang] = [
                'conf' => $confValue,
                'ini' => $iniValue
            ];
        }
    }

    echo "  Clés communes : " . count($commonKeys) . "\n";
    echo "  Traductions différentes : $diffCount\n\n";
}

// Afficher les différences détaillées
if (!empty($differences)) {
    echo "\n===========================================\n";
    echo "DETAIL DES DIFFERENCES PAR CLE\n";
    echo "===========================================\n\n";

    foreach ($differences as $key => $langs) {
        echo "Clé: $key\n";
        echo str_repeat("-", 60) . "\n";

        foreach ($langs as $lang => $values) {
            echo "  [$lang]\n";
            echo "    MyLang.conf : \"{$values['conf']}\"\n";
            echo "    MyLang.ini  : \"{$values['ini']}\"\n";
        }
        echo "\n";
    }
}

// Clés uniquement dans MyLang.conf
echo "\n===========================================\n";
echo "CLES UNIQUEMENT DANS MyLang.conf\n";
echo "===========================================\n\n";

foreach ($languages as $lang) {
    if (!isset($conf[$lang]) || !isset($ini[$lang])) {
        continue;
    }

    $onlyInConf = array_diff(array_keys($conf[$lang]), array_keys($ini[$lang]));

    echo "Langue [$lang] : " . count($onlyInConf) . " clés\n";
    if (count($onlyInConf) > 0) {
        echo "Exemples : " . implode(', ', array_slice($onlyInConf, 0, 10)) . "\n";
    }
    echo "\n";
}

// Clés uniquement dans MyLang.ini
echo "\n===========================================\n";
echo "CLES UNIQUEMENT DANS MyLang.ini\n";
echo "===========================================\n\n";

foreach ($languages as $lang) {
    if (!isset($conf[$lang]) || !isset($ini[$lang])) {
        continue;
    }

    $onlyInIni = array_diff(array_keys($ini[$lang]), array_keys($conf[$lang]));

    echo "Langue [$lang] : " . count($onlyInIni) . " clés\n";
    if (count($onlyInIni) > 0) {
        echo "Exemples : " . implode(', ', array_slice($onlyInIni, 0, 10)) . "\n";
    }
    echo "\n";
}

echo "\n===========================================\n";
echo "RESUME\n";
echo "===========================================\n\n";
echo "Nombre total de clés avec traductions différentes : " . count($differences) . "\n";
echo "\nFichier d'analyse terminé.\n";
