# Guide d'utilisation : Extraction des structures fédérales

## Vue d'ensemble

Ce guide explique comment utiliser les scripts d'extraction des structures fédérales (clubs, comités départementaux et régionaux) depuis le fichier de licenciés FFCK.

## Pour commencer

### Méthode 1 : Script interactif (recommandé pour les débutants)

```bash
./sources/commun/USAGE_EXTRACTION_STRUCTURES.sh
```

Un menu s'affiche avec 5 options :
- **Option 1** : Générer un fichier HTML interactif
- **Option 2** : Générer des fichiers CSV
- **Option 3** : Générer HTML + CSV
- **Option 4** : Afficher les statistiques du fichier source
- **Option 5** : Nettoyer les fichiers générés

### Méthode 2 : Commandes Docker directes

```bash
# Générer le fichier HTML
docker exec kpi_php php /var/www/html/commun/extract_structures.php

# Générer les fichiers CSV
docker exec kpi_php php /var/www/html/commun/extract_structures_csv.php
```

## Scénarios d'utilisation

### Scénario 1 : Consultation rapide des structures

**Objectif** : Visualiser rapidement la liste des clubs, CD et CR.

```bash
# Lancer le script interactif
./sources/commun/USAGE_EXTRACTION_STRUCTURES.sh

# Choisir l'option 1 (HTML)
# Ouvrir le fichier généré dans sources/commun/structures_federales_*.html
```

**Avantages** :
- Interface web moderne
- Recherche en temps réel
- Séparateurs visuels par région/département

### Scénario 2 : Import dans une base de données

**Objectif** : Alimenter des tables de référence dans MySQL.

```bash
# Générer les fichiers CSV
./sources/commun/USAGE_EXTRACTION_STRUCTURES.sh
# Choisir l'option 2 (CSV)

# Importer dans MySQL
mysql -u root -p kpi_db << EOF
LOAD DATA LOCAL INFILE 'sources/commun/comites_regionaux.csv'
INTO TABLE ref_comites_regionaux
FIELDS TERMINATED BY ';'
ENCLOSED BY '"'
IGNORE 1 ROWS;
EOF
```

### Scénario 3 : Validation des données

**Objectif** : Comparer les données FFCK avec la base KPI.

```bash
# 1. Extraire les structures depuis pce1.pce
docker exec kpi_php php /var/www/html/commun/extract_structures_csv.php

# 2. Extraire les structures depuis la base KPI
mysql -u root -p kpi_db -e "SELECT code, libelle FROM clubs ORDER BY code" > clubs_kpi.csv

# 3. Comparer les fichiers
diff sources/commun/clubs.csv clubs_kpi.csv
```

### Scénario 4 : Création de sélecteurs HTML

**Objectif** : Générer une liste déroulante de clubs pour un formulaire.

```php
<?php
// Charger le CSV des clubs
$clubs = [];
if (($handle = fopen('sources/commun/clubs.csv', 'r')) !== false) {
    // Ignorer l'en-tête
    fgetcsv($handle, 1000, ';');

    while (($data = fgetcsv($handle, 1000, ';')) !== false) {
        $clubs[] = [
            'code' => $data[0],
            'nom' => $data[1],
            'cd_code' => $data[2],
            'cr_code' => $data[3]
        ];
    }
    fclose($handle);
}

// Générer le HTML
echo '<select name="club_code">';
echo '<option value="">-- Sélectionner un club --</option>';

$current_cr = '';
$current_cd = '';

foreach ($clubs as $club) {
    // Optgroup pour les régions
    if ($club['cr_code'] !== $current_cr) {
        if ($current_cr !== '') echo '</optgroup>';
        echo '<optgroup label="' . htmlspecialchars($club['cr_code']) . '">';
        $current_cr = $club['cr_code'];
    }

    // Option pour le club
    echo '<option value="' . htmlspecialchars($club['code']) . '">';
    echo htmlspecialchars($club['nom']);
    echo '</option>';
}

if ($current_cr !== '') echo '</optgroup>';
echo '</select>';
?>
```

### Scénario 5 : Analyse statistique

**Objectif** : Analyser la répartition géographique des clubs.

```python
import pandas as pd

# Charger les données
clubs = pd.read_csv('sources/commun/clubs.csv', sep=';')
cd = pd.read_csv('sources/commun/comites_departementaux.csv', sep=';')
cr = pd.read_csv('sources/commun/comites_regionaux.csv', sep=';')

# Analyse par région
clubs_par_region = clubs.groupby('Code CR').size()
print(clubs_par_region.sort_values(ascending=False))

# Analyse par département
clubs_par_departement = clubs.groupby('Code CD').size()
print(clubs_par_departement.head(10))

# Régions avec le plus de clubs
top_regions = clubs_par_region.nlargest(5)
print("Top 5 régions :", top_regions)
```

## Automatisation

### Mise à jour automatique lors du remplacement de pce1.pce

Créer un script `update_structures.sh` :

```bash
#!/bin/bash
# Script de mise à jour automatique des structures

PCE_FILE="sources/commun/pce1.pce"
LAST_UPDATE_FILE="sources/commun/.last_extraction"

# Vérifier si pce1.pce a été modifié
if [ ! -f "$LAST_UPDATE_FILE" ] || [ "$PCE_FILE" -nt "$LAST_UPDATE_FILE" ]; then
    echo "Mise à jour détectée, extraction en cours..."

    # Générer les fichiers
    docker exec kpi_php php /var/www/html/commun/extract_structures.php
    docker exec kpi_php php /var/www/html/commun/extract_structures_csv.php

    # Marquer la date de dernière extraction
    touch "$LAST_UPDATE_FILE"

    echo "Extraction terminée avec succès"
else
    echo "Aucune mise à jour nécessaire"
fi
```

Rendre le script exécutable :
```bash
chmod +x update_structures.sh
```

### Intégration dans un cron

```bash
# Éditer le crontab
crontab -e

# Ajouter une ligne pour extraire les structures tous les lundis à 6h
0 6 * * 1 /chemin/vers/kpi/update_structures.sh >> /var/log/extraction_structures.log 2>&1
```

## Dépannage

### Problème : Conteneur Docker non démarré

**Erreur** :
```
❌ Erreur : Le conteneur kpi_php n'est pas démarré
```

**Solution** :
```bash
# Démarrer le conteneur
make docker_dev_up

# Vérifier qu'il est actif
docker ps | grep kpi_php
```

### Problème : Fichier pce1.pce introuvable

**Erreur** :
```
❌ Erreur : Le fichier pce1.pce n'existe pas dans sources/commun/
```

**Solution** :
1. Vérifier que le fichier existe :
   ```bash
   ls -lh sources/commun/pce1.pce
   ```
2. Si absent, le placer dans `sources/commun/`
3. Vérifier les permissions :
   ```bash
   chmod 644 sources/commun/pce1.pce
   ```

### Problème : Avertissements PHP 8.4

**Message** :
```
Deprecated: fputcsv(): the $escape parameter must be provided...
```

**Solution** :
Ces avertissements n'empêchent pas la génération des fichiers. Ils sont automatiquement filtrés dans le script `USAGE_EXTRACTION_STRUCTURES.sh`.

Pour les supprimer manuellement :
```bash
docker exec kpi_php php /var/www/html/commun/extract_structures_csv.php 2>&1 | grep -v "Deprecated"
```

### Problème : Encodage des caractères dans Excel

**Symptôme** : Les accents s'affichent mal dans Excel.

**Solution** :
Les fichiers CSV sont générés avec BOM UTF-8 pour Excel. Si le problème persiste :
1. Ouvrir Excel
2. Menu "Données" → "Obtenir des données externes" → "À partir d'un fichier texte"
3. Sélectionner "UTF-8" comme encodage
4. Choisir ";" comme séparateur

## Bonnes pratiques

### 1. Versionner le fichier pce1.pce ?

**Non recommandé** : Le fichier est volumineux (~7 Mo) et change fréquemment.

**Alternative** :
- Ajouter `pce1.pce` au `.gitignore`
- Documenter où récupérer la dernière version
- Versionner uniquement les fichiers générés si nécessaire

### 2. Fréquence d'extraction

**Recommandation** :
- Extraire après chaque mise à jour du fichier FFCK
- Ou au minimum une fois par mois
- Automatiser avec un cron

### 3. Nettoyage des anciens fichiers

```bash
# Garder uniquement les 3 dernières extractions HTML
cd sources/commun/
ls -t structures_federales_*.html | tail -n +4 | xargs rm -f

# Ou utiliser le script d'aide (option 5)
./USAGE_EXTRACTION_STRUCTURES.sh
```

### 4. Backup avant import en base

```bash
# Sauvegarder les tables avant import
mysqldump -u root -p kpi_db ref_comites_regionaux ref_comites_departementaux ref_clubs > backup_structures.sql

# Importer les nouvelles données
# ... import ...

# En cas de problème, restaurer
mysql -u root -p kpi_db < backup_structures.sql
```

## Documentation complémentaire

- **Documentation technique** : [sources/commun/README_EXTRACTION_STRUCTURES.md](../../sources/commun/README_EXTRACTION_STRUCTURES.md)
- **Référence développeur** : [DOC/developer/reference/EXTRACTION_STRUCTURES_FFCK.md](../reference/EXTRACTION_STRUCTURES_FFCK.md)
- **Scripts** : [sources/commun/](../../sources/commun/)

## Questions fréquentes

### Q : Puis-je extraire d'autres informations du fichier pce1.pce ?

**R** : Oui, le fichier contient de nombreuses informations :
- Informations des licenciés (nom, prénom, date de naissance, etc.)
- Catégories d'âge
- Type de licence
- Pratiques

Il suffit d'adapter les scripts pour extraire les champs souhaités (voir positions des champs dans le README technique).

### Q : Les fichiers générés peuvent-ils être versionnés dans Git ?

**R** : Généralement non recommandé car :
- Ils sont volumineux
- Ils changent fréquemment
- Ils peuvent être régénérés à partir de pce1.pce

**Exception** : Versionner un snapshot de référence pour comparer les évolutions.

### Q : Comment intégrer ces données dans l'application KPI ?

**R** : Plusieurs approches :
1. **Import direct** : Charger les CSV dans des tables MySQL
2. **API** : Créer des endpoints API pour exposer les structures
3. **Cache** : Générer les fichiers et les servir statiquement
4. **Hybride** : Synchroniser périodiquement avec la base de données

### Q : Que faire si le format du fichier pce1.pce change ?

**R** :
1. Identifier les changements (positions des champs, séparateurs, etc.)
2. Adapter les scripts dans `sources/commun/extract_structures*.php`
3. Tester avec le nouveau format
4. Mettre à jour la documentation

## Support

En cas de problème :
1. Consulter la documentation technique
2. Vérifier les logs Docker : `docker logs kpi_php`
3. Tester manuellement dans le conteneur :
   ```bash
   docker exec -it kpi_php bash
   cd /var/www/html/commun
   php extract_structures.php
   ```
