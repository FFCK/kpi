#!/usr/bin/env php
<?php
/**
 * Script d'extraction des structures fédérales - Version HTML
 *
 * Extrait depuis le fichier pce1.pce les informations sur :
 * - Les comités régionaux (CR)
 * - Les comités départementaux (CD)
 * - Les clubs
 *
 * Génère un fichier HTML interactif avec fonction de recherche en temps réel.
 *
 * @usage
 *   Via Docker : docker exec kpi_php php /var/www/html/commun/extract_structures.php
 *   Direct     : php sources/commun/extract_structures.php
 *
 * @output
 *   Fichier : sources/commun/structures_federales_AAAAMMJJ_HHMMSS.html (~115 Ko)
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
    // Format : [0]=num_licence, [1]=nom, [2]=prenom, [3]=sexe, [4]=date_naissance,
    //          [5]=club_nom, [6]=club_code, [7]=cd_nom, [8]=cd_code, [9]=cr_nom, [10]=cr_code

    $club_nom = trim($champs[5]);
    $club_code = trim($champs[6]);
    $cd_nom = trim($champs[7]);
    $cd_code = trim($champs[8]);
    $cr_nom = trim($champs[9]);
    $cr_code = trim($champs[10]);

    // Stockage des comités régionaux (clé = code)
    if (!empty($cr_code) && !isset($comites_regionaux[$cr_code])) {
        $comites_regionaux[$cr_code] = [
            'code' => $cr_code,
            'libelle' => $cr_nom
        ];
    }

    // Stockage des comités départementaux (clé = code)
    if (!empty($cd_code) && !isset($comites_departementaux[$cd_code])) {
        $comites_departementaux[$cd_code] = [
            'code' => $cd_code,
            'libelle' => $cd_nom,
            'cr_code' => $cr_code
        ];
    }

    // Stockage des clubs (clé = code)
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
    // Tri par code CR
    $cmp_cr = strcmp($a['cr_code'], $b['cr_code']);
    if ($cmp_cr !== 0) return $cmp_cr;

    // Puis par code CD
    $cmp_cd = strcmp($a['cd_code'], $b['cd_code']);
    if ($cmp_cd !== 0) return $cmp_cd;

    // Puis par code club
    return strcmp($a['code'], $b['code']);
});

// Génération du fichier de sortie HTML
$html = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Structures Fédérales - Extraction ' . date('Y-m-d H:i:s') . '</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #0066cc;
            padding-bottom: 10px;
        }
        h2 {
            color: #0066cc;
            margin-top: 30px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 5px;
        }
        .stats {
            background: white;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stats span {
            display: inline-block;
            margin-right: 30px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
        thead {
            background-color: #0066cc;
            color: white;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            font-weight: 600;
            position: sticky;
            top: 0;
        }
        tr:hover {
            background-color: #f8f9fa;
        }
        tr:nth-child(even) {
            background-color: #fafafa;
        }
        .code {
            font-family: "Courier New", monospace;
            font-weight: bold;
            color: #0066cc;
        }
        .cr-group {
            background-color: #e3f2fd;
            font-weight: bold;
        }
        .cd-group {
            background-color: #f3f4f6;
            font-weight: 600;
        }
        .filter-box {
            background: white;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .filter-box input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
    </style>
    <script>
        function filterTable(inputId, tableId) {
            const input = document.getElementById(inputId);
            const filter = input.value.toUpperCase();
            const table = document.getElementById(tableId);
            const tr = table.getElementsByTagName("tr");

            for (let i = 1; i < tr.length; i++) {
                const td = tr[i].getElementsByTagName("td");
                let found = false;

                for (let j = 0; j < td.length; j++) {
                    if (td[j]) {
                        const txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }

                tr[i].style.display = found ? "" : "none";
            }
        }
    </script>
</head>
<body>
    <h1>📊 Structures Fédérales - Canoë-Kayak</h1>

    <div class="stats">
        <span>🏢 Comités Régionaux: ' . count($comites_regionaux) . '</span>
        <span>🏛️ Comités Départementaux: ' . count($comites_departementaux) . '</span>
        <span>⛵ Clubs: ' . count($clubs) . '</span>
        <span>📅 Extraction: ' . date('d/m/Y H:i:s') . '</span>
    </div>
';

// TABLE 1 : COMITÉS RÉGIONAUX
$html .= '
    <h2>🏢 Comités Régionaux</h2>
    <div class="filter-box">
        <input type="text" id="filterCR" onkeyup="filterTable(\'filterCR\', \'tableCR\')" placeholder="🔍 Rechercher un comité régional...">
    </div>
    <table id="tableCR">
        <thead>
            <tr>
                <th>Code</th>
                <th>Libellé</th>
            </tr>
        </thead>
        <tbody>
';

foreach ($comites_regionaux as $cr) {
    $html .= sprintf(
        '<tr><td class="code">%s</td><td>%s</td></tr>',
        htmlspecialchars($cr['code']),
        htmlspecialchars($cr['libelle'])
    );
}

$html .= '
        </tbody>
    </table>
';

// TABLE 2 : COMITÉS DÉPARTEMENTAUX
$html .= '
    <h2>🏛️ Comités Départementaux</h2>
    <div class="filter-box">
        <input type="text" id="filterCD" onkeyup="filterTable(\'filterCD\', \'tableCD\')" placeholder="🔍 Rechercher un comité départemental...">
    </div>
    <table id="tableCD">
        <thead>
            <tr>
                <th>Code CD</th>
                <th>Libellé</th>
                <th>Code CR</th>
            </tr>
        </thead>
        <tbody>
';

foreach ($comites_departementaux as $cd) {
    $html .= sprintf(
        '<tr><td class="code">%s</td><td>%s</td><td class="code">%s</td></tr>',
        htmlspecialchars($cd['code']),
        htmlspecialchars($cd['libelle']),
        htmlspecialchars($cd['cr_code'])
    );
}

$html .= '
        </tbody>
    </table>
';

// TABLE 3 : CLUBS (triés par CR > CD > Club)
$html .= '
    <h2>⛵ Clubs (triés par Comité Régional → Comité Départemental → Club)</h2>
    <div class="filter-box">
        <input type="text" id="filterClubs" onkeyup="filterTable(\'filterClubs\', \'tableClubs\')" placeholder="🔍 Rechercher un club...">
    </div>
    <table id="tableClubs">
        <thead>
            <tr>
                <th>Code Club</th>
                <th>Libellé Club</th>
                <th>Code CD</th>
                <th>Code CR</th>
            </tr>
        </thead>
        <tbody>
';

$current_cr = '';
$current_cd = '';

foreach ($clubs as $club) {
    // Affichage d\'un séparateur visuel pour chaque nouveau CR
    if ($club['cr_code'] !== $current_cr) {
        $current_cr = $club['cr_code'];
        $cr_nom = $comites_regionaux[$current_cr]['libelle'] ?? $current_cr;
        $html .= sprintf(
            '<tr class="cr-group"><td colspan="4">🏢 %s (%s)</td></tr>',
            htmlspecialchars($cr_nom),
            htmlspecialchars($current_cr)
        );
        $current_cd = ''; // Réinitialiser le CD lors d'un changement de CR
    }

    // Affichage d\'un séparateur visuel pour chaque nouveau CD
    if ($club['cd_code'] !== $current_cd) {
        $current_cd = $club['cd_code'];
        $cd_nom = $comites_departementaux[$current_cd]['libelle'] ?? $current_cd;
        $html .= sprintf(
            '<tr class="cd-group"><td colspan="4">  🏛️ %s (%s)</td></tr>',
            htmlspecialchars($cd_nom),
            htmlspecialchars($current_cd)
        );
    }

    // Ligne du club
    $html .= sprintf(
        '<tr><td class="code">%s</td><td>%s</td><td class="code">%s</td><td class="code">%s</td></tr>',
        htmlspecialchars($club['code']),
        htmlspecialchars($club['libelle']),
        htmlspecialchars($club['cd_code']),
        htmlspecialchars($club['cr_code'])
    );
}

$html .= '
        </tbody>
    </table>
</body>
</html>
';

// Sauvegarde du fichier HTML
$fichier_sortie = __DIR__ . '/structures_federales_' . date('Ymd_His') . '.html';
file_put_contents($fichier_sortie, $html);

echo "\n✅ Fichier généré avec succès : $fichier_sortie\n";
echo "\nOuvrez ce fichier dans votre navigateur pour consulter les tableaux.\n";
echo "Les tableaux disposent d'un système de recherche en temps réel.\n\n";
