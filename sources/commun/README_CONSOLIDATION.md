# Consolidation des Traductions Backend

Ce répertoire contient les scripts pour consolider les fichiers de traductions `MyLang.conf` et `MyLang.ini` en un seul fichier unifié.

## 🐳 Environnement Docker

Ce projet utilise Docker. Les scripts PHP doivent être exécutés **dans le conteneur PHP**.

### Nom du Conteneur

Le nom du conteneur PHP dépend de la variable `APPLICATION_NAME` dans `docker/.env` :
- **Développement** : `kpi_php_1` (par défaut)
- **Pré-production** : `kpi_preprod_php_1` (si APPLICATION_NAME=kpi_preprod)
- **Production** : `kpi_prod_php_1` (si APPLICATION_NAME=kpi_prod)

**Pour trouver le nom exact de votre conteneur** :
```bash
docker ps | grep php
# ou
make dev_status  # Pour voir les conteneurs de développement
```

**Dans les commandes ci-dessous**, remplacez `kpi_php_1` par le nom de votre conteneur si différent.

## 📋 Scripts Disponibles

### 1. `compare_translations.php`
Analyse et compare les deux fichiers de traduction pour identifier les différences.

**Usage** :
```bash
php compare_translations.php
```

**Sortie** :
- Statistiques par langue
- Liste des clés avec traductions différentes
- Clés uniques à chaque fichier

---

### 2. `merge_translations.php`
Fusionne les deux fichiers en un seul fichier unifié `MyLang_unified.ini`.

**Usage** :
```bash
# Créer le fichier unifié
php merge_translations.php

# Mode aperçu (sans créer le fichier)
php merge_translations.php --preview
```

**Résultat** :
- Crée `MyLang_unified.ini` avec toutes les traductions
- Applique les choix de traduction recommandés pour résoudre les conflits
- Trie les clés alphabétiquement

---

### 3. `patch_mysmarty.php`
Modifie `MySmarty.php` pour utiliser `MyLang.ini` au lieu de `MyLang.conf`.

**Usage** :
```bash
# Appliquer le patch
php patch_mysmarty.php

# Mode aperçu (affiche les modifications sans les appliquer)
php patch_mysmarty.php --preview
```

**Modifications** :
- Remplace `MyLang.conf` par `MyLang.ini`
- Crée une sauvegarde automatique de `MySmarty.php`
- Met à jour les commentaires du code

---

## 🚀 Guide de Migration Rapide

### Option 1 : Migration Complète (Recommandée)

**Depuis l'hôte Docker** :

```bash
# 1. Analyser les différences (optionnel)
docker exec -it kpi_php_1 php /sources/commun/compare_translations.php

# 2. Créer le fichier unifié
docker exec -it kpi_php_1 php /sources/commun/merge_translations.php

# 3. Sauvegarder les fichiers originaux (depuis l'hôte - volumes Docker)
cd sources/commun
cp MyLang.conf MyLang.conf.backup
cp MyLang.ini MyLang.ini.backup
cp MySmarty.php MySmarty.php.backup

# 4. Appliquer les modifications
mv MyLang_unified.ini MyLang.ini
docker exec -it kpi_php_1 php /sources/commun/patch_mysmarty.php

# 5. Redémarrer les conteneurs
cd ../..
make dev_restart

# 6. Tester l'application
# - Générer des PDFs
# - Naviguer dans l'interface Smarty
# - Vérifier les logs : make dev_logs

# 7. Si tout fonctionne, supprimer les anciennes sauvegardes
cd sources/commun
rm MyLang.conf.backup MyLang.ini.backup MySmarty.php.backup
```

### Option 2 : Migration Progressive (Prudente)

**Depuis l'hôte Docker** :

```bash
# 1. Tester en mode preview
docker exec -it kpi_php_1 php /sources/commun/merge_translations.php --preview
docker exec -it kpi_php_1 php /sources/commun/patch_mysmarty.php --preview

# 2. Créer le fichier unifié sans l'activer
docker exec -it kpi_php_1 php /sources/commun/merge_translations.php

# 3. Tester le fichier unifié manuellement
# (renommer temporairement et tester)
cd sources/commun
mv MyLang.ini MyLang.ini.old
mv MyLang_unified.ini MyLang.ini
# Tester...
# Si problème : mv MyLang.ini.old MyLang.ini

# 4. Si OK, appliquer définitivement
docker exec -it kpi_php_1 php /sources/commun/patch_mysmarty.php

# 5. Redémarrer
cd ../..
make dev_restart
```

### Option 3 : Via Shell Interactif (Pour Plus de Contrôle)

```bash
# Entrer dans le conteneur PHP
make php_bash

# Dans le conteneur :
cd /sources/commun

# Analyser les différences
php compare_translations.php

# Créer le fichier unifié
php merge_translations.php

# Vérifier le résultat
head -50 MyLang_unified.ini

# Sortir du conteneur
exit

# Depuis l'hôte : sauvegarder et appliquer
cd sources/commun
cp MyLang.conf MyLang.conf.backup
cp MyLang.ini MyLang.ini.backup
mv MyLang_unified.ini MyLang.ini

# Retourner dans le conteneur pour patcher
make php_bash
cd /sources/commun
php patch_mysmarty.php
exit

# Redémarrer
make dev_restart
```

---

## 📊 Résultats de la Fusion

### Statistiques

| Langue | MyLang.conf | MyLang.ini | Fichier Unifié |
|--------|-------------|------------|----------------|
| **FR** | 573 clés    | 332 clés   | **722 clés**   |
| **EN** | 573 clés    | 333 clés   | **723 clés**   |
| **CN** | 0 clés      | 333 clés   | **333 clés**   |

### Conflits Résolus

**34 clés** avaient des traductions différentes entre les deux fichiers :
- **13 en français**
- **29 en anglais**

Tous les conflits ont été résolus en appliquant les choix recommandés basés sur :
- La précision du terme
- La cohérence avec le reste de l'application
- Les standards internationaux
- Le contexte d'utilisation (PDF vs interface web)

---

## ⚠️ Points d'Attention

### Avant la Migration

1. **Sauvegarder** les fichiers originaux
2. **Tester** en environnement de développement d'abord
3. **Vérifier** que tous les PDFs se génèrent correctement
4. **Contrôler** que toutes les pages Smarty s'affichent correctement

### Après la Migration

1. **Supprimer** les fichiers traités par cache Smarty :
   ```bash
   # Depuis l'hôte
   cd sources/commun
   rm -f MyLang_processed.conf MyLang_processed.ini
   ```

2. **Vider** le cache Smarty si nécessaire :
   ```bash
   # Depuis l'hôte
   cd sources/templates_c
   rm -f *.php

   # Ou via conteneur Docker
   docker exec -it kpi_php_1 rm -rf /sources/templates_c/*.php
   ```

3. **Surveiller** les logs PHP pour détecter d'éventuelles erreurs :
   ```bash
   # Depuis l'hôte - méthode recommandée
   make dev_logs

   # Ou directement avec Docker
   docker logs -f kpi_php_1

   # Ou via fichiers de logs
   tail -f docker/logs/php/error.log
   ```

4. **Redémarrer les conteneurs** après modifications importantes :
   ```bash
   # Redémarrer les conteneurs de développement
   make dev_restart

   # Ou pour production
   make prod_restart
   ```

---

## 📝 Maintenance Future

### Ajouter une Nouvelle Traduction

Éditer uniquement `MyLang.ini` :

```ini
[fr]
Nouvelle_cle = "Nouvelle traduction en français"

[en]
Nouvelle_cle = "New translation in English"
```

### Modifier une Traduction Existante

1. Éditer `MyLang.ini`
2. Supprimer le fichier traité : `rm MyLang_processed.ini`
3. Recharger la page

### Synchronisation

Le fichier `MyLang_processed.ini` est régénéré automatiquement par MySmarty.php si :
- Il n'existe pas
- `MyLang.ini` a été modifié

---

## 🔗 Documentation Complète

Pour plus de détails, consultez :
- [`/home/user/kpi/WORKFLOW_AI/CONSOLIDATION_TRADUCTIONS.md`](../../WORKFLOW_AI/CONSOLIDATION_TRADUCTIONS.md)

---

**Date de création** : 2025-11-22
**Auteur** : Claude Code
**Version** : 1.0
