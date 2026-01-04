# Extraction des structures fédérales (FFCK)

## Vue d'ensemble

Scripts PHP permettant d'extraire et d'organiser les structures fédérales (clubs, comités départementaux et régionaux) depuis le fichier de licenciés FFCK (`pce1.pce`).

## Localisation

- **Scripts** : `sources/commun/`
  - `extract_structures.php` - Génère un fichier HTML interactif
  - `extract_structures_csv.php` - Génère trois fichiers CSV séparés
  - `USAGE_EXTRACTION_STRUCTURES.sh` - Script d'aide interactif

- **Documentation** : `sources/commun/README_EXTRACTION_STRUCTURES.md`

## Utilisation rapide

### Via le script interactif (recommandé)

```bash
./sources/commun/USAGE_EXTRACTION_STRUCTURES.sh
```

Menu interactif avec 5 options :
1. Générer le fichier HTML
2. Générer les fichiers CSV
3. Générer HTML + CSV
4. Afficher les statistiques
5. Nettoyer les fichiers générés

### Via Docker (commandes directes)

```bash
# Fichier HTML interactif
docker exec kpi_php php /var/www/html/commun/extract_structures.php

# Fichiers CSV
docker exec kpi_php php /var/www/html/commun/extract_structures_csv.php
```

## Fichiers générés

### HTML
- **Nom** : `structures_federales_AAAAMMJJ_HHMMSS.html`
- **Taille** : ~115 Ko
- **Fonctionnalités** :
  - Recherche en temps réel
  - Interface responsive
  - Séparateurs visuels par région/département

### CSV
- `comites_regionaux.csv` (~900 octets)
- `comites_departementaux.csv` (~5 Ko)
- `clubs.csv` (~31 Ko)
- Format : BOM UTF-8, séparateur `;` (compatible Excel)

## Données extraites

### Statistiques (extraction du 2026-01-03)
- **21 Comités Régionaux**
- **104 Comités Départementaux**
- **702 Clubs**

### Structure

#### Comités Régionaux
| Colonne | Description | Exemple |
|---------|-------------|---------|
| Code CR | Code région | `CR01` |
| Libellé | Nom complet | `COMITE REGIONAL CANOE KAYAK AUVERGNE RHÔNE ALPES` |

#### Comités Départementaux
| Colonne | Description | Exemple |
|---------|-------------|---------|
| Code CD | Code département | `CD044` |
| Libellé | Nom complet | `COMITE DEPARTEMENTAL CK DE LOIRE ATLANTIQUE` |
| Code CR | Région parente | `CR11` |

#### Clubs
| Colonne | Description | Exemple |
|---------|-------------|---------|
| Code Club | Code club | `044005` |
| Libellé | Nom complet | `CANOE KAYAK CLISSON` |
| Code CD | Département parent | `CD044` |
| Code CR | Région parente | `CR11` |

## Tri des données

Les données sont triées hiérarchiquement :
1. **Comités Régionaux** : par code CR (alphanumérique)
2. **Comités Départementaux** : par code CD (alphanumérique)
3. **Clubs** : par CR → CD → Code Club

Le fichier HTML affiche des séparateurs visuels pour faciliter la navigation.

## Cas d'usage

### 1. Consultation rapide
Générer le fichier HTML pour une visualisation interactive des structures.

### 2. Import en base de données
Générer les CSV et les importer dans MySQL/PostgreSQL pour alimenter des tables de référence.

### 3. Validation des données
Comparer les données extraites avec celles présentes dans la base de données KPI.

### 4. Création de sélecteurs
Utiliser les CSV pour générer des listes déroulantes dans les formulaires.

### 5. Statistiques
Analyser la répartition géographique des clubs avec pandas, R, etc.

## Intégration avec le système KPI

### Lien avec MyBdd.php

Le système KPI utilise déjà des transformations similaires dans `MyBdd.php` :

```php
// Exemple trouvé dans MyBdd.php ligne 897-898
REPLACE(REPLACE(lc.Comite_reg, 'COMITE REGIONAL', 'CR'), 'CANOE KAYAK', 'CK')
```

Les scripts d'extraction offrent une alternative **sans dépendance à la base de données**, utile pour :
- Vérifier la cohérence des données
- Initialiser une nouvelle installation
- Exporter les structures pour d'autres systèmes
- Créer des snapshots de référence

### Automatisation

Pour automatiser l'extraction lors de la mise à jour du fichier `pce1.pce` :

```bash
#!/bin/bash
# Script à placer dans un cron ou un hook git

# Vérifier si pce1.pce a été modifié
if [ sources/commun/pce1.pce -nt sources/commun/structures_federales_latest.html ]; then
    echo "Mise à jour détectée, extraction en cours..."
    docker exec kpi_php php /var/www/html/commun/extract_structures.php
    docker exec kpi_php php /var/www/html/commun/extract_structures_csv.php
    echo "Extraction terminée"
fi
```

## Maintenance

### Mise à jour du fichier source
1. Remplacer `sources/commun/pce1.pce` par la nouvelle version
2. Relancer les scripts d'extraction
3. Les nouveaux fichiers seront horodatés

### Nettoyage
```bash
# Via le script interactif
./sources/commun/USAGE_EXTRACTION_STRUCTURES.sh
# Choisir l'option 5

# Ou manuellement
rm -f sources/commun/structures_federales_*.html
rm -f sources/commun/*.csv
```

## Dépendances

- PHP 8.4+
- Fichier `pce1.pce` dans `sources/commun/`
- Conteneur Docker `kpi_php` (recommandé)

## Limitations connues

### Avertissements PHP 8.4
Les scripts génèrent des avertissements de dépréciation sur `fputcsv()` en PHP 8.4. Ces avertissements n'empêchent pas la génération correcte des fichiers.

**Solution** : Les avertissements sont filtrés dans le script `USAGE_EXTRACTION_STRUCTURES.sh`

### Encodage
Les fichiers CSV utilisent le BOM UTF-8 pour garantir la compatibilité Excel. Si vous utilisez un autre système, vous pouvez avoir besoin de convertir l'encodage.

### Performances
Pour un fichier `pce1.pce` de ~7 Mo avec ~30000 licenciés :
- Temps d'exécution : < 2 secondes
- Mémoire utilisée : < 50 Mo

## Historique

| Date | Version | Description |
|------|---------|-------------|
| 2026-01-03 | 1.0 | Création initiale des scripts<br>- Version HTML avec recherche interactive<br>- Version CSV avec BOM UTF-8<br>- Documentation complète<br>- Script d'aide interactif |

## Voir aussi

- [README_EXTRACTION_STRUCTURES.md](../../sources/commun/README_EXTRACTION_STRUCTURES.md) - Documentation technique détaillée
- [USAGE_EXTRACTION_STRUCTURES.sh](../../sources/commun/USAGE_EXTRACTION_STRUCTURES.sh) - Script d'aide interactif
- [MyBdd.php](../../sources/commun/MyBdd.php) - Classe de gestion BDD avec transformations similaires

## Support

Pour toute question ou problème :
1. Consulter la documentation technique : `sources/commun/README_EXTRACTION_STRUCTURES.md`
2. Vérifier que le fichier `pce1.pce` existe et est à jour
3. Vérifier que le conteneur Docker `kpi_php` est démarré : `docker ps | grep kpi_php`
