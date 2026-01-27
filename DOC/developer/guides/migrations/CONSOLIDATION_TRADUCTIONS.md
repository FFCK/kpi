# Consolidation des Traductions Backend

## 📋 Contexte

Le backend de l'application KPI utilise actuellement **deux fichiers de traductions séparés** :

1. **`sources/commun/MyLang.conf`** - Traductions pour les templates Smarty
2. **`sources/commun/MyLang.ini`** - Traductions pour les fichiers PDF générés avec MyPDF.php

Cette duplication complique la maintenance et peut créer des incohérences.

## 📊 Analyse des Fichiers

### Statistiques

| Langue | MyLang.conf | MyLang.ini | Clés communes | Traductions différentes |
|--------|-------------|------------|---------------|------------------------|
| **FR** | 573 clés    | 332 clés   | 183           | 13                     |
| **EN** | 573 clés    | 333 clés   | 183           | 29                     |

### Répartition des Clés

- **Clés uniquement dans MyLang.conf** : ~390 clés (utilisées pour Smarty templates)
- **Clés uniquement dans MyLang.ini** : ~150 clés (utilisées pour les PDFs)
- **Clés communes** : 183 clés
- **Total de clés avec traductions différentes** : 34

## 🎯 Solution Proposée

### Option 1 : Fichier Unifié (RECOMMANDÉE)

Créer un **fichier unique `MyLang.ini`** contenant toutes les traductions, utilisé à la fois par Smarty et MyPDF.

**Avantages** :
- ✅ Un seul fichier à maintenir
- ✅ Cohérence garantie des traductions
- ✅ Réduction des erreurs de synchronisation
- ✅ Format .ini standard et bien supporté

**Actions à réaliser** :
1. Fusionner les deux fichiers en résolvant les conflits
2. Modifier `MyPDF.php` pour utiliser le fichier unifié (si nécessaire)
3. Supprimer l'ancien `MyLang.conf`

### Option 2 : Synchronisation Automatique

Garder les deux fichiers mais créer un script de synchronisation automatique.

**Inconvénients** :
- ❌ Complexité supplémentaire
- ❌ Risque de désynchronisation
- ❌ Maintenance plus lourde

## ⚠️ Clés avec Traductions Différentes

### Français (13 différences)

Les clés suivantes ont des traductions différentes entre les deux fichiers. **Vous devez choisir** quelle version conserver :

| Clé | MyLang.conf (Smarty) | MyLang.ini (PDF) | **Recommandation** |
|-----|----------------------|------------------|-------------------|
| `Arbitre_1` | "Arbitre 1" | "Arbitre principal" | **PDF** (plus explicite) |
| `Arbitre_2` | "Arbitre 2" | "Arbitre secondaire" | **PDF** (plus explicite) |
| `Deroulement` | "Progression" | "Déroulement" | **PDF** (plus précis pour les PDFs) |
| `Diff` | "+/-" | "Diff" | **CONF** (symbole universel) |
| `Evenements` | "Evénements" | "Evènements" | **CONF** (orthographe correcte) |
| `MAJ` | "Mis à jour" | "Mis à jour le" | **PDF** (plus complet) |
| `Num` | "Num" | "N°" | **PDF** (symbole standard) |
| `Par_Numero` | "Par Numéro" | "Par numéro" | **PDF** (minuscule cohérent) |
| `R1` | "Resp. Organisation" | "R1" | **CONF** (plus explicite) |
| `RC` | "Resp. Compétition" | "Responsable de compétition (RC)" | **PDF** (complet avec acronyme) |
| `REG18` | "Championnat Régional Auvergne - Rhône-Alpes" | "Championnat Régional Rhône Alpes" | **CONF** (nom complet officiel) |
| `T-18` | "Tournoi régional amical Auvergne - Rhône-Alpes" | "Tournoi régional amical Rhône Alpes" | **CONF** (nom complet officiel) |
| `Verrouille` | "Verrouillés" | "Verrouillé" | **PDF** (singulier par défaut) |

### Anglais (29 différences)

| Clé | MyLang.conf (Smarty) | MyLang.ini (PDF) | **Recommandation** |
|-----|----------------------|------------------|-------------------|
| `Acces_direct` | "Direct access to the competitions games" | "Direct access to the competitions matchs" | **CONF** ("games" correct) |
| `Arbitre_1` | "Referee 1" | "First referee" | **PDF** (plus explicite) |
| `Arbitre_2` | "Referee 2" | "Second referee" | **PDF** (plus explicite) |
| `CFH1N` | "French Cup Men 1st tour Nord" | "French Cup Men 1st round Nord" | **PDF** ("round" plus correct) |
| `CFH1NO` | "French Cup Men 1st tour Nord-West" | "French Cup Men 1st round Nord-West" | **PDF** ("round" plus correct) |
| `CFH1O` | "French Cup Men 1st tour West" | "French Cup Men 1st round West" | **PDF** ("round" plus correct) |
| `CFH1S` | "French Cup Men 1st tour South" | "French Cup Men 1st round South" | **PDF** ("round" plus correct) |
| `CFH2A` | "French Cup Men 2nd tour group A" | "French Cup Men 2nd round group A" | **PDF** ("round" plus correct) |
| `CFH2B` | "French Cup Men 2nd tour group B" | "French Cup Men 2nd round group B" | **PDF** ("round" plus correct) |
| `CFH2C` | "French Cup Men 2nd tour group C" | "French Cup Men 2nd round group C" | **PDF** ("round" plus correct) |
| `Classements` | "Rankings" | "Ranking" | **CONF** (pluriel cohérent) |
| `Clt` | "Pos" | "Rank" | **CONF** ("Pos" pour Position) |
| `Delegue` | "Techn. Delegate" | "Technical Delegate" | **PDF** (terme complet) |
| `Diff` | "+/-" | "GD" | **PDF** ("GD" = Goal Difference) |
| `En_attente` | "Awaiting" | "Waiting" | **CONF** ("Awaiting" plus formel) |
| `En_cours` | "Running" | "In progress" | **PDF** (plus explicite) |
| `J` | "Pld" | "Yc" | **CONF** ("Pld" = Played) |
| `Journee` | "Gameday" | "Matchday" | **PDF** ("Matchday" plus usuel) |
| `Liste_des_Matchs` | "Games list" | "Games program" | **PDF** (plus descriptif) |
| `MAJ` | "Update" | "Update :" | **PDF** (avec ponctuation) |
| `N4H2A` | "National 4 Men 2nd tour group A" | "National 4 Men 2nd round group A" | **PDF** ("round" plus correct) |
| `N4H2B` | "National 4 Men 2nd tour group B" | "National 4 Men 2nd round group B" | **PDF** ("round" plus correct) |
| `NASH` | "Men Aces Tournament" | "Men As Tournament" | **CONF** ("Aces" correct) |
| `NASF` | "Women Aces Tournament" | "Women As Tournament" | **CONF** ("Aces" correct) |
| `Num` | "#" | "#" | *Identique* |
| `Par_Numero` | "Number" | "ID" | **PDF** (plus précis) |
| `RC` | "Compet. manager" | "Competition manager" | **PDF** (terme complet) |
| `REG18` | "Auvergne - Rhône-Alpes Regional Championship" | "Rhône-Alpes Regional Championship" | **CONF** (nom complet) |
| `T-18` | "Auvergne - Rhône-Alpes Regional Tournament" | "Rhône-Alpes Regional Tournament" | **CONF** (nom complet) |
| `Termine` | "Completed" | "Ended" | **CONF** ("Completed" plus formel) |

## 🔧 Implémentation de la Solution

### Scripts Créés

Trois scripts PHP ont été créés pour automatiser la consolidation :

1. **`compare_translations.php`** - Analyse les différences entre les deux fichiers
2. **`merge_translations.php`** - Fusionne les deux fichiers en un seul fichier unifié
3. **`patch_mysmarty.php`** - Modifie MySmarty.php pour utiliser MyLang.ini

### Étape 1 : Analyser les Différences (Optionnel)

**Depuis l'hôte** :
```bash
# Méthode 1 : Utiliser make backend_bash puis exécuter dans le conteneur
make backend_bash
# Dans le conteneur :
cd /sources/scripts
php compare_translations.php
exit

# Méthode 2 : Commande directe avec docker exec
docker exec -it kpi_php_1 php /sources/scripts/compare_translations.php
```

Ce script affiche :
- Les statistiques de chaque fichier
- Les clés avec traductions différentes
- Les clés uniques à chaque fichier

### Étape 2 : Créer le Fichier Unifié

**Depuis l'hôte** :
```bash
# Méthode 1 : Utiliser make backend_bash
make backend_bash
# Dans le conteneur :
cd /sources/scripts
php merge_translations.php
exit

# Méthode 2 : Commande directe
docker exec -it kpi_php_1 php /sources/scripts/merge_translations.php
```

Ce script :
- ✅ Fusionne MyLang.conf et MyLang.ini
- ✅ Applique les choix de traduction recommandés
- ✅ Crée `MyLang_unified.ini` avec toutes les clés
- ✅ Trie les clés alphabétiquement

**Résultat** :
- Français : 722+ clés (augmenté avec les nouvelles traductions)
- Anglais : 723+ clés
- Chinois : 333+ clés (uniquement dans MyLang.ini)

**Mode Preview** :
```bash
docker exec -it kpi_php_1 php /sources/scripts/merge_translations.php --preview
```
Affiche un aperçu sans créer le fichier.

### Étape 3 : Modifier MySmarty.php

**Depuis l'hôte** :
```bash
# Méthode 1 : Via make backend_bash
make backend_bash
# Dans le conteneur :
cd /sources/scripts
php patch_mysmarty.php
exit

# Méthode 2 : Commande directe
docker exec -it kpi_php_1 php /sources/scripts/patch_mysmarty.php
```

Ce script :
- ✅ Crée une sauvegarde automatique de MySmarty.php
- ✅ Remplace les références à MyLang.conf par MyLang.ini
- ✅ Met à jour les commentaires du code

**Mode Preview** :
```bash
docker exec -it kpi_php_1 php /sources/scripts/patch_mysmarty.php --preview
```
Affiche les modifications sans les appliquer.

### Étape 4 : Tests en Environnement de Développement

1. **Sauvegarder les fichiers originaux** :
   ```bash
   # Depuis l'hôte (les fichiers sont montés en volume)
   cd sources/commun
   cp MyLang.conf MyLang.conf.backup
   cp MyLang.ini MyLang.ini.backup
   ```

2. **Activer le fichier unifié** :
   ```bash
   # Depuis l'hôte
   cd sources/commun
   mv MyLang_unified.ini MyLang.ini
   ```

3. **Tester la génération de PDFs** :
   - Générer un PDF de classement
   - Générer une feuille de marque
   - Vérifier que toutes les traductions s'affichent correctement

4. **Tester les templates Smarty** :
   - Naviguer dans l'interface d'administration
   - Vérifier que toutes les pages affichent correctement les traductions
   - Tester en français et en anglais

5. **Vérifier les logs PHP** :
   ```bash
   # Depuis l'hôte - suivre les logs en temps réel
   make docker_dev_logs

   # Ou spécifiquement pour le conteneur PHP
   docker logs -f kpi_php_1
   ```

6. **Redémarrer les conteneurs si nécessaire** :
   ```bash
   # Redémarrer l'environnement de développement
   make docker_dev_restart
   ```

### Étape 5 : Migration en Production

Si tous les tests sont OK en développement :

1. **Sauvegarder les anciens fichiers** :
   ```bash
   # Depuis l'hôte
   cd sources/commun
   mv MyLang.conf MyLang.conf.backup_$(date +%Y%m%d)
   mv MyLang.ini MyLang.ini.backup_$(date +%Y%m%d)
   ```

2. **Déployer le fichier unifié** :
   ```bash
   # Le fichier MyLang.ini est déjà en place (depuis l'hôte)
   cd sources/commun

   # Supprimer le fichier de configuration traité par Smarty (sera régénéré)
   rm -f MyLang_processed.conf MyLang_processed.ini
   ```

3. **Redémarrer les conteneurs de production** :
   ```bash
   # Depuis la racine du projet
   make docker_prod_restart
   ```

4. **Vérifier en production** :
   - Tester quelques pages
   - Générer quelques PDFs
   - Vérifier les logs : `make docker_prod_logs`

5. **Nettoyer les fichiers de backup** (après quelques jours) :
   ```bash
   # Depuis l'hôte
   cd sources/commun
   rm MyLang.conf.backup* MyLang.ini.backup*
   rm MySmarty.php.backup*
   ```

## 📝 Recommandations

### Choix des Traductions

**Critères de décision** :
1. **Cohérence** : Privilégier les termes cohérents avec le reste de l'application
2. **Précision** : Choisir les traductions les plus explicites
3. **Standards** : Respecter les conventions (ex: "round" plutôt que "tour" en anglais)
4. **Contexte** : Tenir compte du contexte d'utilisation (PDF vs interface web)

### Après la Consolidation

1. **Documentation** : Documenter les conventions de traduction
2. **Process** : Établir un processus pour ajouter de nouvelles traductions
3. **Validation** : Mettre en place une validation des traductions avant déploiement

## 🚀 Prochaines Étapes

1. **Décider des traductions** pour les 34 clés en conflit (voir tableaux ci-dessus)
2. **Créer le script de fusion** avec vos choix
3. **Générer le fichier unifié** `MyLang.ini`
4. **Tester en environnement de développement**
5. **Déployer en production** après validation

## 📎 Fichiers et Scripts

- `scripts/compare_translations.php` - Script d'analyse des différences entre MyLang.conf et MyLang.ini
- `scripts/merge_translations.php` - Script de fusion des fichiers de traduction
- `scripts/patch_mysmarty.php` - Script de modification de MySmarty.php
- Ce document - Documentation complète de la consolidation

---

**Date de création** : 2025-11-22
**Auteur** : Claude Code
**Version** : 1.0
