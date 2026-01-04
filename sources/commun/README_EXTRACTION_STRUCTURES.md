# Scripts d'extraction des structures fédérales

## Description

Ces scripts permettent d'extraire et d'organiser les informations sur les structures fédérales (clubs, comités départementaux et comités régionaux) depuis le fichier de licenciés FFCK (`pce1.pce`).

## Fichiers

### `extract_structures.php`
Script principal qui génère un **fichier HTML interactif** avec trois tableaux :
- Comités Régionaux (CR)
- Comités Départementaux (CD)
- Clubs (triés par CR → CD → Club)

**Fonctionnalités** :
- Interface web moderne et responsive
- Recherche en temps réel sur chaque tableau
- Séparateurs visuels pour faciliter la lecture
- Statistiques complètes

### `extract_structures_csv.php`
Script qui génère **trois fichiers CSV** séparés :
- `comites_regionaux.csv`
- `comites_departementaux.csv`
- `clubs.csv`

**Fonctionnalités** :
- Format CSV compatible Excel (séparateur `;`, BOM UTF-8)
- Facilite l'import dans des tableurs
- Idéal pour l'analyse de données

## Prérequis

- PHP 8.4+
- Fichier `pce1.pce` présent dans `sources/commun/`
- Conteneur Docker PHP (recommandé)

## Utilisation

### Via Docker (recommandé)

```bash
# Génération du fichier HTML
docker exec kpi_php php /var/www/html/commun/extract_structures.php

# Génération des fichiers CSV
docker exec kpi_php php /var/www/html/commun/extract_structures_csv.php
```

### En ligne de commande directe

```bash
# Génération du fichier HTML
php sources/commun/extract_structures.php

# Génération des fichiers CSV
php sources/commun/extract_structures_csv.php
```

## Fichiers générés

### Fichier HTML
- **Nom** : `structures_federales_AAAAMMJJ_HHMMSS.html`
- **Emplacement** : `sources/commun/`
- **Taille approximative** : ~115 Ko
- **Utilisation** : Ouvrir dans un navigateur web

### Fichiers CSV
- **Noms** :
  - `comites_regionaux.csv` (~900 octets)
  - `comites_departementaux.csv` (~5 Ko)
  - `clubs.csv` (~31 Ko)
- **Emplacement** : `sources/commun/`
- **Utilisation** : Import dans Excel, LibreOffice Calc, etc.

## Structure des données

### Fichier source : `pce1.pce`

Le fichier contient une section `[licencies]` avec des lignes au format CSV :
```
num_licence;nom;prenom;sexe;date_naissance;club_nom;club_code;cd_nom;cd_code;cr_nom;cr_code;...
```

**Positions des champs extraits** :
- `[5]` : Nom du club
- `[6]` : Code du club (ex: `044005`)
- `[7]` : Nom du comité départemental
- `[8]` : Code du comité départemental (ex: `CD044`)
- `[9]` : Nom du comité régional
- `[10]` : Code du comité régional (ex: `CR11`)

### Données extraites

#### Comités Régionaux (CR)
| Champ | Description | Exemple |
|-------|-------------|---------|
| code  | Code CR     | `CR01`  |
| libelle | Nom complet | `COMITE REGIONAL CANOE KAYAK AUVERGNE RHÔNE ALPES` |

#### Comités Départementaux (CD)
| Champ | Description | Exemple |
|-------|-------------|---------|
| code  | Code CD     | `CD044` |
| libelle | Nom complet | `COMITE DEPARTEMENTAL CK DE LOIRE ATLANTIQUE` |
| cr_code | Code CR parent | `CR11` |

#### Clubs
| Champ | Description | Exemple |
|-------|-------------|---------|
| code  | Code club   | `044005` |
| libelle | Nom complet | `CANOE KAYAK CLISSON` |
| cd_code | Code CD parent | `CD044` |
| cr_code | Code CR parent | `CR11` |

## Tri des données

### Comités Régionaux
- Tri : **par code CR** (ordre alphanumérique)
- Exemple : `CR01`, `CR02`, ..., `CR24`, `CR26`

### Comités Départementaux
- Tri : **par code CD** (ordre alphanumérique)
- Exemple : `CD001`, `CD002`, ..., `CD995`

### Clubs
- Tri hiérarchique : **CR → CD → Club**
- Les clubs sont regroupés visuellement par région, puis par département
- Exemple :
  ```
  CR01 (Auvergne-Rhône-Alpes)
    CD001 (Ain)
      001007 - EAUX VIVES OYONNAX C.K.
      001008 - CKC VALLEE DE L'AIN
    CD003 (Allier)
      003003 - A.S.P.T.T. MOULINS
      003006 - CANOE KAYAK CLUB DE VICHY
  ```

## Statistiques typiques

D'après l'extraction du 2026-01-03 :
- **21 Comités Régionaux**
- **104 Comités Départementaux**
- **702 Clubs**

## Maintenance

### Mise à jour du fichier source
Le fichier `pce1.pce` est mis à jour par la FFCK. Pour générer de nouvelles extractions :
1. Remplacer `sources/commun/pce1.pce` par la nouvelle version
2. Relancer les scripts d'extraction
3. Les nouveaux fichiers seront horodatés

### Modification des scripts

#### Ajouter des champs
Pour extraire des champs supplémentaires, modifier :
1. La structure des tableaux (`$clubs`, `$comites_departementaux`, etc.)
2. Les en-têtes HTML/CSV
3. Les boucles d'affichage

#### Changer le format de sortie
- **HTML** : Modifier la section génération du code HTML (balises `<table>`, CSS)
- **CSV** : Modifier les appels à `fputcsv()`

### Compatibilité PHP 8.4

Les scripts utilisent des fonctions compatibles PHP 8.4. Les avertissements de dépréciation sur `fputcsv()` peuvent être ignorés (les fichiers CSV sont correctement générés).

Pour supprimer ces avertissements, ajouter le paramètre `$escape` :
```php
fputcsv($fp, $data, ';', '"', '\\');
```

## Cas d'usage

### 1. Consultation rapide des structures
```bash
docker exec kpi_php php /var/www/html/commun/extract_structures.php
# Ouvrir le fichier HTML généré dans un navigateur
```

### 2. Import dans une base de données
```bash
docker exec kpi_php php /var/www/html/commun/extract_structures_csv.php
# Importer les fichiers CSV dans MySQL, PostgreSQL, etc.
```

### 3. Analyse statistique
```bash
# Générer les CSV et les analyser avec pandas, R, etc.
docker exec kpi_php php /var/www/html/commun/extract_structures_csv.php
```

### 4. Création de sélecteurs pour formulaires
```php
// Exemple : liste déroulante des clubs par région
require_once 'extract_structures_csv.php';
// Utiliser les données pour générer un <select> HTML
```

## Intégration avec MyBdd.php

Les scripts peuvent être intégrés avec les méthodes existantes de `MyBdd.php`.

**Exemple d'utilisation** :
```php
// Dans MyBdd.php, on trouve déjà des requêtes similaires :
// REPLACE(REPLACE(lc.Comite_reg, 'COMITE REGIONAL', 'CR'), 'CANOE KAYAK', 'CK')

// Ces scripts offrent une alternative sans dépendance à la base de données
// Utile pour :
// - Vérifier la cohérence des données
// - Initialiser une nouvelle installation
// - Exporter les structures pour d'autres systèmes
```

## Problèmes connus

### Fichier pce1.pce introuvable
**Erreur** : `Erreur : fichier /var/www/html/commun/pce1.pce introuvable`

**Solution** : Vérifier que le fichier existe dans `sources/commun/pce1.pce`

### Encodage des caractères
Les fichiers CSV utilisent le BOM UTF-8 pour garantir la compatibilité Excel :
```php
fprintf($fp, "\xEF\xBB\xBF"); // BOM UTF-8
```

### Performances
Pour un fichier `pce1.pce` de ~7 Mo :
- Temps d'exécution : < 2 secondes
- Mémoire utilisée : < 50 Mo

## Historique

- **2026-01-03** : Création initiale des scripts
  - Version HTML avec recherche interactive
  - Version CSV avec BOM UTF-8
  - Documentation complète

## Auteur

Scripts générés pour le projet KPI - Système de gestion sportive
