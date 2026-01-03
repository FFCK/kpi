#!/usr/bin/env php
<?php
/**
 * Script d'extraction des structures fédérales - Version CSV
 *
 * Extrait depuis le fichier pce1.pce les informations sur :
 * - Les comités régionaux (CR)
 * - Les comités départementaux (CD)
 * - Les clubs
 *
 * Génère trois fichiers CSV séparés compatibles Excel (BOM UTF-8, séparateur ;).
 *
 * @usage
 *   Via Docker : docker exec kpi_php php /var/www/html/commun/extract_structures_csv.php
 *   Direct     : php sources/commun/extract_structures_csv.php
 *
 * @output
 *   - sources/commun/comites_regionaux.csv (~900 octets)
 *   - sources/commun/comites_departementaux.csv (~5 Ko)
 *   - sources/commun/clubs.csv (~31 Ko)
 *
 * @see README_EXTRACTION_STRUCTURES.md pour la documentation complète
 *
 * @version 1.0
 * @date 2026-01-03
 */

$fichier = __DIR__ . '/pce1.pce';

if (!file_exists($fichier)) {
    die("Erreur : fichier $fichier introuvable\n");
}

$handle = fopen($fichier, 'r');
if (!$handle) {
    die("Erreur : impossible d'ouvrir le fichier $fichier\n");
}

// Initialisation des tableaux pour stocker les structures uniques
$clubs = [];
$comites_departementaux = [];
$comites_regionaux = [];

// Indicateur pour savoir si on est dans la section [licencies]
$in_licencies = false;

echo "Lecture du fichier en cours...\n";

while (($ligne = fgets($handle)) !== false) {
    $ligne = trim($ligne);

    // Détection de la section [licencies]
    if ($ligne === '[licencies]') {
        $in_licencies = true;
        continue;
    }

    // Ignorer les lignes avant la section [licencies]
    if (!$in_licencies || empty($ligne)) {
        continue;
    }

    // Découpage de la ligne CSV (séparateur : point-virgule)
    $champs = explode(';', $ligne);

    // Vérifier qu'on a assez de champs
    if (count($champs) < 12) {
        continue;
    }

    // Extraction des informations
    $club_nom = trim($champs[5]);
    $club_code = trim($champs[6]);
    $cd_nom = trim($champs[7]);
    $cd_code = trim($champs[8]);
    $cr_nom = trim($champs[9]);
    $cr_code = trim($champs[10]);

    // Stockage des comités régionaux
    if (!empty($cr_code) && !isset($comites_regionaux[$cr_code])) {
        $comites_regionaux[$cr_code] = [
            'code' => $cr_code,
            'libelle' => $cr_nom
        ];
    }

    // Stockage des comités départementaux
    if (!empty($cd_code) && !isset($comites_departementaux[$cd_code])) {
        $comites_departementaux[$cd_code] = [
            'code' => $cd_code,
            'libelle' => $cd_nom,
            'cr_code' => $cr_code
        ];
    }

    // Stockage des clubs
    if (!empty($club_code) && !isset($clubs[$club_code])) {
        $clubs[$club_code] = [
            'code' => $club_code,
            'libelle' => $club_nom,
            'cd_code' => $cd_code,
            'cr_code' => $cr_code
        ];
    }
}

fclose($handle);

echo "Extraction terminée.\n\n";
echo "Statistiques :\n";
echo "- Comités régionaux : " . count($comites_regionaux) . "\n";
echo "- Comités départementaux : " . count($comites_departementaux) . "\n";
echo "- Clubs : " . count($clubs) . "\n\n";

// Tri des données
echo "Tri des données...\n";

// Tri des comités régionaux par code
ksort($comites_regionaux);

// Tri des comités départementaux par code
ksort($comites_departementaux);

// Tri des clubs : par CR, puis CD, puis code club
usort($clubs, function($a, $b) {
    $cmp_cr = strcmp($a['cr_code'], $b['cr_code']);
    if ($cmp_cr !== 0) return $cmp_cr;
    $cmp_cd = strcmp($a['cd_code'], $b['cd_code']);
    if ($cmp_cd !== 0) return $cmp_cd;
    return strcmp($a['code'], $b['code']);
});

// Génération du fichier CSV des comités régionaux
$fichier_cr = __DIR__ . '/comites_regionaux.csv';
$fp_cr = fopen($fichier_cr, 'w');
fprintf($fp_cr, "\xEF\xBB\xBF"); // BOM UTF-8 pour Excel
fputcsv($fp_cr, ['Code CR', 'Libellé'], ';');
foreach ($comites_regionaux as $cr) {
    fputcsv($fp_cr, [$cr['code'], $cr['libelle']], ';');
}
fclose($fp_cr);

// Génération du fichier CSV des comités départementaux
$fichier_cd = __DIR__ . '/comites_departementaux.csv';
$fp_cd = fopen($fichier_cd, 'w');
fprintf($fp_cd, "\xEF\xBB\xBF"); // BOM UTF-8 pour Excel
fputcsv($fp_cd, ['Code CD', 'Libellé', 'Code CR'], ';');
foreach ($comites_departementaux as $cd) {
    fputcsv($fp_cd, [$cd['code'], $cd['libelle'], $cd['cr_code']], ';');
}
fclose($fp_cd);

// Génération du fichier CSV des clubs
$fichier_clubs = __DIR__ . '/clubs.csv';
$fp_clubs = fopen($fichier_clubs, 'w');
fprintf($fp_clubs, "\xEF\xBB\xBF"); // BOM UTF-8 pour Excel
fputcsv($fp_clubs, ['Code Club', 'Libellé', 'Code CD', 'Code CR'], ';');
foreach ($clubs as $club) {
    fputcsv($fp_clubs, [$club['code'], $club['libelle'], $club['cd_code'], $club['cr_code']], ';');
}
fclose($fp_clubs);

echo "\n✅ Fichiers CSV générés avec succès :\n";
echo "   - $fichier_cr\n";
echo "   - $fichier_cd\n";
echo "   - $fichier_clubs\n\n";
