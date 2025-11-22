<?php
/**
 * Script de modification de MySmarty.php
 * Modifie MySmarty.php pour utiliser MyLang.ini au lieu de MyLang.conf
 *
 * Usage: php patch_mysmarty.php [--preview]
 *   --preview : Affiche les modifications sans les appliquer
 */

$mySmartyFile = __DIR__ . '/../sources/commun/MySmarty.php';

// Vérifier que le fichier existe
if (!file_exists($mySmartyFile)) {
    die("Erreur: MySmarty.php n'existe pas à l'emplacement: $mySmartyFile\n");
}

// Lire le contenu actuel
$content = file_get_contents($mySmartyFile);

// Créer une sauvegarde
$backupFile = $mySmartyFile . '.backup_' . date('Y-m-d_His');

// Vérifier le mode
$previewMode = in_array('--preview', $argv ?? []);

echo "===========================================\n";
echo "PATCH MySmarty.php\n";
echo "===========================================\n\n";

if ($previewMode) {
    echo "MODE PREVIEW - Aucune modification ne sera appliquée\n\n";
}

// Modifications à appliquer
$modifications = [
    [
        'description' => 'Remplacer MyLang.conf par MyLang.ini dans preprocessConfigFile()',
        'search' => "PATH_ABS . 'commun/MyLang.conf'",
        'replace' => "PATH_ABS . 'commun/MyLang.ini'"
    ],
    [
        'description' => 'Remplacer MyLang_processed.conf par MyLang_processed.ini',
        'search' => "PATH_ABS . 'commun/MyLang_processed.conf'",
        'replace' => "PATH_ABS . 'commun/MyLang_processed.ini'"
    ],
    [
        'description' => 'Mettre à jour le commentaire de la méthode (1/2)',
        'search' => '* Prétraite le fichier MyLang.conf pour remplacer les tirets par des underscores',
        'replace' => '* Prétraite le fichier MyLang.ini pour remplacer les tirets par des underscores'
    ]
];

$newContent = $content;
$changesApplied = 0;

foreach ($modifications as $i => $mod) {
    $num = $i + 1;
    echo "Modification $num: {$mod['description']}\n";

    if (strpos($newContent, $mod['search']) !== false) {
        echo "  ✓ Trouvé : {$mod['search']}\n";
        echo "  → Remplacé par : {$mod['replace']}\n";
        $newContent = str_replace($mod['search'], $mod['replace'], $newContent);
        $changesApplied++;
    } else {
        echo "  ✗ Pattern non trouvé : {$mod['search']}\n";
    }

    echo "\n";
}

echo "===========================================\n";
echo "RÉSUMÉ\n";
echo "===========================================\n\n";
echo "Modifications appliquées : $changesApplied / " . count($modifications) . "\n";

if ($previewMode) {
    echo "\nMode preview activé - Aucun fichier n'a été modifié.\n";
    echo "Pour appliquer les modifications, exécutez :\n";
    echo "  php patch_mysmarty.php\n";
} else {
    if ($changesApplied > 0) {
        // Créer une sauvegarde
        copy($mySmartyFile, $backupFile);
        echo "\nSauvegarde créée : $backupFile\n";

        // Écrire le nouveau contenu
        file_put_contents($mySmartyFile, $newContent);
        echo "MySmarty.php modifié avec succès !\n";

        echo "\nPROCHAINES ÉTAPES :\n";
        echo "1. Vérifier que MySmarty.php fonctionne correctement\n";
        echo "2. Tester les templates Smarty avec le nouveau fichier de traduction\n";
        echo "3. Si tout fonctionne, supprimer la sauvegarde : rm $backupFile\n";
    } else {
        echo "\nAucune modification n'a été appliquée.\n";
        echo "Le fichier MySmarty.php n'a pas été modifié.\n";
    }
}

echo "\nTerminé !\n";
