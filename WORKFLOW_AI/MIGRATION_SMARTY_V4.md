# Analyse et Plan de Migration de Smarty v2 vers v4

Ce document détaille l'analyse du code existant et propose un plan de migration pour mettre à jour la bibliothèque Smarty de la version 2.6.18 vers une version moderne (v4+).

## 1. Analyse du Code PHP

### 1.1. Instanciation de Smarty

**Résultat :** L'instanciation est centralisée, ce qui est une excellente nouvelle.

-   **Fichier :** `sources/commun/MySmarty.php`
-   **Ligne :** `L7: $smarty = new Smarty();`

**Impact :** Seul ce fichier devra être modifié pour utiliser la nouvelle instanciation avec namespace : `new \Smarty\Smarty()`.

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
    - Remplacer `new Smarty()` par `new \Smarty\Smarty()`.
    - Adapter les chemins de configuration (`template_dir`, `compile_dir`) avec les nouvelles méthodes (ex: `$smarty->setTemplateDir(...)`).
5.  **Tester l'application :** Naviguer sur toutes les pages pour identifier et corriger les erreurs éventuelles. Une attention particulière devra être portée à la console du navigateur et aux logs d'erreurs PHP.