<?php
/**
 * Script de fusion des fichiers de traduction
 * Fusionne MyLang.conf et MyLang.ini en un seul fichier MyLang.ini unifié
 *
 * Usage: php merge_translations.php [--preview]
 *   --preview : Affiche le résultat sans créer le fichier
 */

// Fonction pour parser un fichier .ini/.conf
function parseTranslationFile($filepath) {
    $content = file_get_contents($filepath);
    $lines = explode("\n", $content);

    $translations = [];
    $currentLang = null;
    $comments = [];

    foreach ($lines as $lineNum => $line) {
        $trimmedLine = trim($line);

        // Conserver les commentaires de début de fichier
        if (empty($currentLang) && (empty($trimmedLine) || $trimmedLine[0] === '#' || $trimmedLine[0] === ';')) {
            if ($lineNum < 10) { // Garder seulement les premiers commentaires
                $comments[] = $line;
            }
            continue;
        }

        // Ignorer les commentaires et lignes vides
        if (empty($trimmedLine) || $trimmedLine[0] === '#' || $trimmedLine[0] === ';') {
            continue;
        }

        // Détection de section de langue
        if (preg_match('/^\[(\w+)\]$/', $trimmedLine, $matches)) {
            $currentLang = $matches[1];
            if (!isset($translations[$currentLang])) {
                $translations[$currentLang] = [];
            }
            continue;
        }

        // Extraction des paires clé=valeur
        if ($currentLang && strpos($trimmedLine, '=') !== false) {
            list($key, $value) = explode('=', $trimmedLine, 2);
            $key = trim($key);
            $value = trim($value);

            // Retirer les guillemets au début et à la fin
            $value = trim($value, '"');
            $value = trim($value, '\'');

            $translations[$currentLang][$key] = $value;
        }
    }

    return ['comments' => $comments, 'translations' => $translations];
}

// Choix de traductions (basés sur les recommandations du document)
$translationChoices = [
    // Français
    'fr' => [
        'Arbitre_1' => 'ini',          // "Arbitre principal" plus explicite
        'Arbitre_2' => 'ini',          // "Arbitre secondaire" plus explicite
        'Deroulement' => 'ini',        // "Déroulement" plus précis pour les PDFs
        'Diff' => 'conf',              // "+/-" symbole universel
        'Evenements' => 'conf',        // "Evénements" orthographe correcte
        'MAJ' => 'ini',                // "Mis à jour le" plus complet
        'Num' => 'ini',                // "N°" symbole standard
        'Par_Numero' => 'ini',         // "Par numéro" minuscule cohérent
        'R1' => 'conf',                // "Resp. Organisation" plus explicite
        'RC' => 'ini',                 // "Responsable de compétition (RC)" complet
        'REG18' => 'conf',             // Nom complet officiel avec "Auvergne"
        'T-18' => 'conf',              // Nom complet officiel avec "Auvergne"
        'Verrouille' => 'ini',         // "Verrouillé" singulier par défaut
    ],
    // Anglais
    'en' => [
        'Acces_direct' => 'conf',      // "games" correct
        'Arbitre_1' => 'ini',          // "First referee" plus explicite
        'Arbitre_2' => 'ini',          // "Second referee" plus explicite
        'CFH1N' => 'ini',              // "round" plus correct
        'CFH1NO' => 'ini',             // "round" plus correct
        'CFH1O' => 'ini',              // "round" plus correct
        'CFH1S' => 'ini',              // "round" plus correct
        'CFH2A' => 'ini',              // "round" plus correct
        'CFH2B' => 'ini',              // "round" plus correct
        'CFH2C' => 'ini',              // "round" plus correct
        'Classements' => 'conf',       // "Rankings" pluriel cohérent
        'Clt' => 'conf',               // "Pos" pour Position
        'Delegue' => 'ini',            // "Technical Delegate" terme complet
        'Diff' => 'ini',               // "GD" = Goal Difference
        'En_attente' => 'conf',        // "Awaiting" plus formel
        'En_cours' => 'ini',           // "In progress" plus explicite
        'J' => 'conf',                 // "Pld" = Played
        'Journee' => 'ini',            // "Matchday" plus usuel
        'Liste_des_Matchs' => 'ini',   // "Games program" plus descriptif
        'MAJ' => 'ini',                // "Update :" avec ponctuation
        'N4H2A' => 'ini',              // "round" plus correct
        'N4H2B' => 'ini',              // "round" plus correct
        'NASH' => 'conf',              // "Men Aces Tournament" correct
        'NASF' => 'conf',              // "Women Aces Tournament" correct
        'Par_Numero' => 'ini',         // "ID" plus précis
        'RC' => 'ini',                 // "Competition manager" terme complet
        'REG18' => 'conf',             // Nom complet avec "Auvergne"
        'T-18' => 'conf',              // Nom complet avec "Auvergne"
        'Termine' => 'conf',           // "Completed" plus formel
    ]
];

// Parser les deux fichiers
echo "Chargement des fichiers de traduction...\n";
$confData = parseTranslationFile(__DIR__ . '/../sources/commun/MyLang.conf');
$iniData = parseTranslationFile(__DIR__ . '/../sources/commun/MyLang.ini');

$conf = $confData['translations'];
$ini = $iniData['translations'];

// Langues supportées
$languages = ['fr', 'en', 'cn'];

// Construire le fichier unifié
$unified = [];
foreach ($languages as $lang) {
    $unified[$lang] = [];

    // Ajouter toutes les clés de .conf
    if (isset($conf[$lang])) {
        foreach ($conf[$lang] as $key => $value) {
            $unified[$lang][$key] = $value;
        }
    }

    // Ajouter les clés de .ini qui ne sont pas dans .conf
    if (isset($ini[$lang])) {
        foreach ($ini[$lang] as $key => $value) {
            if (!isset($unified[$lang][$key])) {
                $unified[$lang][$key] = $value;
            }
        }
    }

    // Appliquer les choix de traduction pour les conflits
    if (isset($translationChoices[$lang])) {
        foreach ($translationChoices[$lang] as $key => $choice) {
            if ($choice === 'ini' && isset($ini[$lang][$key])) {
                $unified[$lang][$key] = $ini[$lang][$key];
            } elseif ($choice === 'conf' && isset($conf[$lang][$key])) {
                $unified[$lang][$key] = $conf[$lang][$key];
            }
        }
    }

    // Trier les clés par ordre alphabétique pour faciliter la maintenance
    ksort($unified[$lang]);
}

// Générer le contenu du fichier unifié
$output = ";Fichier de langues unifié\n";
$output .= ";Généré automatiquement par merge_translations.php\n";
$output .= ";Date: " . date('Y-m-d H:i:s') . "\n";
$output .= "\n;Variables communes\n\n";

foreach ($languages as $lang) {
    $langName = [
        'fr' => 'Français',
        'en' => 'Anglais',
        'cn' => 'Chinois'
    ];

    $output .= "\n;{$langName[$lang]}\n";
    $output .= "[$lang]\n";

    if (isset($unified[$lang])) {
        foreach ($unified[$lang] as $key => $value) {
            // Échapper les guillemets dans les valeurs
            $escapedValue = str_replace('"', '\"', $value);
            $output .= "$key = \"$escapedValue\"\n";
        }
    }

    $output .= "\n";
}

// Statistiques
echo "\n===========================================\n";
echo "STATISTIQUES DE FUSION\n";
echo "===========================================\n\n";

foreach ($languages as $lang) {
    $confCount = isset($conf[$lang]) ? count($conf[$lang]) : 0;
    $iniCount = isset($ini[$lang]) ? count($ini[$lang]) : 0;
    $unifiedCount = isset($unified[$lang]) ? count($unified[$lang]) : 0;

    echo "Langue [$lang] :\n";
    echo "  - MyLang.conf : $confCount clés\n";
    echo "  - MyLang.ini  : $iniCount clés\n";
    echo "  - Fichier unifié : $unifiedCount clés\n";
    echo "\n";
}

// Vérifier le mode
$previewMode = in_array('--preview', $argv ?? []);

if ($previewMode) {
    echo "\n===========================================\n";
    echo "MODE PREVIEW - Aperçu du fichier unifié\n";
    echo "===========================================\n\n";
    echo substr($output, 0, 2000) . "\n...\n[Tronqué pour l'aperçu]\n";
} else {
    // Sauvegarder le fichier unifié
    $outputFile = __DIR__ . '/../sources/commun/MyLang_unified.ini';
    file_put_contents($outputFile, $output);

    echo "\n===========================================\n";
    echo "FICHIER UNIFIÉ CRÉÉ\n";
    echo "===========================================\n\n";
    echo "Fichier créé : $outputFile\n";
    echo "Taille : " . number_format(strlen($output)) . " octets\n";
    echo "\nPROCHAINES ÉTAPES :\n";
    echo "1. Vérifier le contenu de MyLang_unified.ini\n";
    echo "2. Tester avec les fichiers PDF et les templates Smarty\n";
    echo "3. Sauvegarder les anciens fichiers :\n";
    echo "   mv MyLang.conf MyLang.conf.backup\n";
    echo "   mv MyLang.ini MyLang.ini.backup\n";
    echo "4. Renommer le fichier unifié :\n";
    echo "   mv MyLang_unified.ini MyLang.ini\n";
    echo "5. Modifier MySmarty.php pour utiliser MyLang.ini au lieu de MyLang.conf\n";
}

echo "\nFusion terminée avec succès !\n";
