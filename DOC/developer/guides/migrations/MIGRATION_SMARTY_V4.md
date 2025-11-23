# Analyse et Plan de Migration de Smarty v2 vers v4

Ce document détaille l'analyse du code existant et propose un plan de migration pour mettre à jour la bibliothèque Smarty de la version 2.6.18 vers une version moderne (v4+).

## 1. Analyse du Code PHP

### 1.1. Instanciation de Smarty

**Résultat :** L'instanciation est centralisée, ce qui est une excellente nouvelle.

-   **Fichier :** `sources/commun/MySmarty.php`
-   **Ligne :** `L7: $smarty = new Smarty();`

**Impact :** Seul ce fichier devra être modifié. **Contrairement à ce qui était attendu, Smarty 4 n'utilise PAS de namespace**. La classe reste simplement `Smarty`, mais nécessite l'appel à `parent::__construct()`.

### 1.2. Plugins et Fonctions Personnalisées

**Résultat :** Aucune utilisation des fonctions `register_function`, `register_block`, ou `register_modifier` n'a été détectée dans le code de l'application.

**Impact :** Très positif. Aucune migration de plugin personnalisé ne semble nécessaire, ce qui simplifie considérablement la mise à jour.

### 1.3. Accès aux propriétés Smarty

**Résultat :** L'accès direct aux propriétés de configuration est utilisé dans `MySmarty.php`.

-   **Fichier :** `sources/commun/MySmarty.php`
-   **Lignes :**
    -   `L17: $this->template_dir = PATH_ABS . 'smarty/templates';`
    -   `L18: $this->compile_dir =  PATH_ABS . 'smarty/templates_c';`
    -   `L20: $this->config_dir =  PATH_ABS . 'smarty/configs';`
    -   `L22: $this->caching = false;`

**Impact :** Ces affectations directes provoqueront des erreurs avec Smarty 4. Elles devront être remplacées par les méthodes `setters` correspondantes :
-   `$this->setTemplateDir(...)`
-   `$this->setCompileDir(...)`
-   `$this->setConfigDir(...)`
-   `$this->setCaching(...)`

## 2. Analyse des Templates (`.tpl`)

### 2.1. Utilisation de la balise `{php}`

**Résultat :** Aucune balise `{php}` n'a été trouvée.

**Impact :** Extrêmement positif. Cela signifie que les templates ne contiennent pas de code PHP brut, ce qui élimine le risque de sécurité le plus élevé et la partie la plus complexe de la migration des templates.

### 2.2. Inventaire des templates

**Résultat :** 87 fichiers de template (`.tpl`) ont été trouvés dans `sources/`.

**Impact :** Bien qu'aucune modification de syntaxe majeure ne soit attendue (grâce à l'absence de `{php}`), chaque page correspondante devra être testée manuellement pour s'assurer qu'aucun effet de bord subtil n'apparaît après la mise à jour.

## 3. Plan de Migration Recommandé

1.  **Créer une branche Git dédiée :** `git checkout -b feature/smarty-v4-upgrade`
2.  **Supprimer l'ancienne librairie :** Retirer le dossier `sources/Smarty-Lib`.
3.  **Installer la nouvelle librairie via Composer :** Exécuter `composer require smarty/smarty:^4`.
4.  **Mettre à jour le code PHP (principalement dans `sources/commun/MySmarty.php`) :**
    - **IMPORTANT**: Smarty 4 n'utilise PAS de namespace. La classe reste `Smarty` (pas `Smarty\Smarty`).
    - Ajouter `parent::__construct()` dans le constructeur de MySmarty.
    - Adapter les accès aux propriétés de configuration (remplacer les accès directs comme `$this->template_dir` par les getters/setters : `$this->setTemplateDir(...)`, `$this->getTemplateDir()[0]`).
5.  **Tester l'application :** Naviguer sur toutes les pages pour identifier et corriger les erreurs éventuelles. Une attention particulière devra être portée à la console du navigateur et aux logs d'erreurs PHP.

## 4. Problèmes Rencontrés et Solutions

### 4.1. Namespace Smarty

**Problème rencontré :** La documentation suggérait que Smarty 4 utilisait des namespaces (`Smarty\Smarty`), mais **ce n'est pas le cas**. Smarty 4.5.6 utilise toujours la classe globale `Smarty`.

**Solution appliquée :** Pas de `use` statement nécessaire, la classe hérite directement de `Smarty`.

### 4.2. Accès aux propriétés de configuration

**Problème rencontré :** L'accès direct `$this->m_tpl->template_dir` causait une erreur "Array to string conversion" car `getTemplateDir()` retourne maintenant un tableau en Smarty 4.

**Solution appliquée :** Remplacement par `$templateDirs = $this->m_tpl->getTemplateDir(); $tplFullName = is_array($templateDirs) ? $templateDirs[0] : $templateDirs;` dans `MyPage.php:276`.

### 4.3. Clés de configuration avec tirets (hyphens)

**Problème rencontré :** Smarty 4 est plus strict sur la syntaxe des fichiers de configuration (`.conf`). Les clés contenant des tirets (ex: `T-18`, `T-BREIZH`) provoquent l'erreur :
```
Smarty Compiler: Syntax error in config file on line 535 'T-18 = "..."' - Unexpected "-", expected EQUAL
```

**Contrainte :** Ces clés correspondent à des codes de compétition stockés en base de données et ne peuvent pas être modifiés.

**Solution appliquée :**
1. Ajout d'une méthode `preprocessConfigFile()` dans `MySmarty.php` qui :
   - Lit le fichier source `MyLang.conf`
   - Remplace automatiquement les tirets par des underscores dans les clés (regex : `/^([A-Z0-9]+)-([A-Z0-9]+)\s*=/m`)
   - Génère un fichier prétraité `MyLang_processed.conf`
   - Utilise un cache basé sur `filemtime()` pour ne régénérer que si nécessaire

2. Mise à jour de tous les templates (13 fichiers `.tpl`) pour utiliser `MyLang_processed.conf` au lieu de `MyLang.conf`

3. Les codes avec tirets en base de données sont convertis dynamiquement côté PHP avant d'accéder aux variables de config Smarty (en remplaçant `-` par `_`)

**Fichiers modifiés :**
- `sources/commun/MySmarty.php` - Ajout de la méthode de prétraitement
- Tous les templates dans `sources/smarty/templates/*.tpl` utilisant `config_load`

Cette approche permet de conserver les codes originaux en base de données tout en étant compatible avec les restrictions de syntaxe de Smarty 4.

### 4.4. Variables non définies (Undefined array key warnings)

**Problème rencontré :** Smarty 4 avec PHP 8+ est beaucoup plus strict concernant l'accès aux variables non définies. Les warnings suivants apparaissaient :
```
Undefined array key "idSelJournee"
Attempt to read property "value" on null
Undefined array key "voie"
```

**Cause :** En Smarty 2, l'accès à une variable non assignée retournait silencieusement une valeur vide. Smarty 4 génère maintenant des warnings PHP 8 lorsqu'on tente d'accéder à des variables non définies.

**Solution appliquée :**
1. Pour les variables dans les URLs : Utilisation du modificateur `|default:''`
   ```smarty
   {* Avant *}
   href="page.php?J={$idSelJournee}"

   {* Après *}
   href="page.php?J={$idSelJournee|default:''}"
   ```

2. Pour les tests conditionnels : Utilisation de `isset()` avant le test
   ```smarty
   {* Avant *}
   {if $voie}

   {* Après *}
   {if isset($voie) && $voie}
   ```

**Fichiers modifiés :**
- `sources/smarty/templates/frame_navgroup.tpl` - Ajout de `|default:''` sur toutes les occurrences de `$idSelJournee` (~15 occurrences) et `$group` (28 occurrences)
- `sources/smarty/templates/frame_page.tpl` - Ajout de `isset()` pour la variable `$voie`
- `sources/smarty/templates/GestionCalendrier.tpl` - Ajout de `isset($competition)` avant tous les tests (6 occurrences)
- `sources/smarty/templates/GestionClassement.tpl` - Protection de `$compet.goalaverage` (1), `$compet.Statut` (4), et `$Code_niveau` (4 occurrences)

3. Pour les accès à des clés de tableaux : Vérifier l'existence avant l'accès
   ```smarty
   {* Avant - Accès direct qui peut échouer *}
   {assign var='next' value=$myArray[$i+1]}
   {if $data != $otherArray[$next].field}

   {* Après - Vérification de l'existence *}
   {assign var='next' value=$myArray[$i+1]|default:null}
   {if $next && isset($otherArray[$next]) && $data != $otherArray[$next].field}
   ```

**Fichiers modifiés (accès tableaux) :**
- `sources/smarty/templates/frame_phases.tpl` - Protection contre l'accès au dernier élément+1 d'un tableau et vérification `isset()` avant utilisation de la clé

4. Pour les boucles `{foreach}` sur des variables potentiellement non définies : Utiliser `|default:[]`
   ```smarty
   {* Avant - Erreur si $myArray n'existe pas *}
   {foreach from=$myArray item=item}
       {$item.name}
   {/foreach}

   {* Après - Retourne un tableau vide si non défini *}
   {foreach from=$myArray|default:[] item=item}
       {$item.name}
   {/foreach}
   ```

**Fichiers modifiés (foreach) :**
- `sources/smarty/templates/frame_navgroup.tpl` - Ajout de `|default:[]` sur toutes les boucles `{foreach from=$arrayNavGroup}` (3 occurrences) et `isset()` dans les tests conditionnels (2 occurrences)

5. Pour les accès au premier élément d'un tableau : Vérifier l'existence avec `isset()`
   ```smarty
   {* Avant - Erreur si le tableau est vide *}
   {if $myArray[0].field == 'value'}

   {* Après - Vérifie que l'élément existe *}
   {if isset($myArray[0]) && $myArray[0].field == 'value'}
   ```

**Fichiers modifiés (array[0]) :**
- `sources/smarty/templates/frame_matchs.tpl` - Ajout de `isset($arrayCompetition[0])` avant tous les accès (4 occurrences) et `isset($arrayMatchs[0])` pour le test `is_array()` (1 occurrence)
- `sources/smarty/templates/kpdetails.tpl` - Protection de `$journee[0]` avec `isset()` dans les affichages et tests (5 occurrences)

**Note :** Cette approche peut être appliquée à tous les templates présentant des warnings similaires. Il est recommandé de systématiquement utiliser `|default` ou `isset()` pour toutes les variables optionnelles et tous les accès à des tableaux.

### 4.5. Variables PHP non initialisées

**Problème rencontré :** PHP 8 est également plus strict dans le code PHP lui-même. Le warning suivant apparaissait :
```
Undefined variable $listCompet in frame_categories.php on line 167
```

**Cause :** Le code utilisait l'opérateur `.=` (concaténation) sur une variable `$listCompet` qui n'était pas initialisée avant la boucle. En PHP 7, cela créait automatiquement une chaîne vide, mais PHP 8 génère un warning.

**Solution appliquée :**
```php
// Avant
while ($row = $result->fetch()) {
    if (isset($listCompet) && $listCompet != '') {
        $listCompet .= ',';
    }
    $listCompet .= "'" . $row["Code"] . "'"; // Warning ici
}

// Après
$listCompet = ''; // Initialisation explicite
while ($row = $result->fetch()) {
    if ($listCompet != '') {
        $listCompet .= ',';
    }
    $listCompet .= "'" . $row["Code"] . "'"; // Plus de warning
}
```

**Fichiers modifiés :**
- `sources/frame_categories.php` - Initialisation de `$listCompet` avant la boucle (ligne 161)
- `sources/kpdetails.php` - Initialisation de `$journee = array()` (ligne 127) et protection `isset($arrayListJournees[0])` (2 occurrences)

**Bonnes pratiques PHP 8** :
- Toujours initialiser les variables avant utilisation, même pour les concaténations
- Remplacer `isset($var) && $var != ''` par un simple `$var != ''` après initialisation
- Utiliser l'opérateur null coalescing `??` quand approprié : `$var = $input ?? 'default';`

**Note :** Ce type de warning peut apparaître dans n'importe quel code PHP. La migration vers Smarty 4 et PHP 8 nécessite de revoir toutes les initialisations de variables.

### 4.6. Bugs découverts lors de la migration

La migration vers Smarty 4 et PHP 8 a permis de découvrir des bugs existants qui étaient masqués par le comportement permissif de PHP 7 :

**Bug dans kpdetails.php (lignes 132 et 139) :**

**Problème** : Utilisation de l'opérateur de comparaison `==` au lieu de l'opérateur d'affectation `=`
```php
// Bug - Ne fait rien
$row['Selected'] == true;
$row['Selected'] == false;

// Correction
$row['Selected'] = true;
$row['Selected'] = false;
```

**Impact** : La propriété `Selected` n'était jamais définie, ce qui pouvait causer des problèmes d'affichage avec des données en cache/session persistantes affichant la mauvaise compétition.

**Fichiers modifiés :**
- `sources/kpdetails.php` - Correction des affectations (lignes 132, 139)

**Leçon** : La migration vers des versions plus strictes de PHP permet de détecter ce type d'erreur silencieuse qui aurait pu rester cachée pendant des années.

#### 4.6.2. Bug logique dans la gestion des paramètres (kpdetails.php ligne 68)

**Problème observé** : En accédant à `kpdetails.php?Compet=CF15&Group=CF15&Saison=2025&typ=CP`, la page affichait les informations d'une autre compétition visitée précédemment.

**Cause** : Logique de réinitialisation incorrecte
```php
// Bug - Écrase toujours Compet si event change
if ($event != $_SESSION['event']) {
    $codeCompet = '*'; // ❌ Écrase même si Compet est fourni dans l'URL
    ...
}

// Correction - Ne réinitialise que si Compet n'est pas fourni
if ($event != $_SESSION['event'] && utyGetGet('Compet', '*') == '*') {
    $codeCompet = '*'; // ✓ Réinitialise seulement si Compet='*'
    ...
}
```

**Logique métier correcte** :
- Si l'événement change **ET** que `Compet` n'est pas fourni (`'*'`) → réinitialiser les paramètres
- Si `Compet` est fourni explicitement (ex: `CF15`) → **la compétition prend le dessus**, ne pas réinitialiser

**Fichiers modifiés :**
- `sources/kpdetails.php` - Ajout de la condition `&& utyGetGet('Compet', '*') == '*'` (ligne 68)

**Impact** : Permet de naviguer directement vers une compétition spécifique même si on vient d'un autre événement.