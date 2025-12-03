# Bonnes Pratiques JavaScript & Smarty

## 📋 Vue d'ensemble

Ce document rassemble les bonnes pratiques identifiées lors du développement de l'éditeur de grille de points pour compétitions MULTI. Ces pratiques évitent les erreurs courantes lors de l'intégration de Smarty avec JavaScript.

## 🚨 Règles critiques

### 1. Traductions dans JavaScript : Toujours utiliser des guillemets doubles

**❌ INCORRECT** (cause des erreurs de syntaxe)
```javascript
alert('{#Erreur_application#}');
// Si la traduction contient : "impossible d'appliquer"
// Génère : alert('impossible d'appliquer');
//                            ^ casse la chaîne
```

**✅ CORRECT**
```javascript
alert("{#Erreur_application#}");
// Génère : alert("impossible d'appliquer");
// Fonctionne correctement avec les apostrophes
```

**Contexte** : Les traductions françaises contiennent fréquemment des apostrophes (`d'équipe`, `l'application`, etc.). Utiliser des guillemets simples `'` pour encadrer ces traductions cause des erreurs de syntaxe JavaScript.

**Fichiers concernés** : Tous les templates Smarty contenant du JavaScript

### 2. Variables Smarty dans JavaScript : Syntaxe correcte

**❌ INCORRECT**
```javascript
var lang = '{$lang|default:"fr"}';  // Les guillemets doubles causent des problèmes
```

**✅ CORRECT - Option 1 : Opérateur JavaScript**
```javascript
var lang = '{$lang}' || 'fr';  // Préféré : utilise JavaScript pour la valeur par défaut
```

**✅ CORRECT - Option 2 : Guillemets simples Smarty**
```javascript
var lang = "{$lang|default:'fr'}";  // Alternative : guillemets simples dans Smarty
```

### 3. JSON encoding : Ne pas utiliser le préfixe @

**❌ INCORRECT**
```javascript
var gridData = {$gridData|@json_encode};  // Le @ est invalide
```

**✅ CORRECT**
```javascript
var gridData = {$gridData|json_encode};  // Pas de @ pour les modificateurs
```

**Note** : Le `@` est utilisé en PHP pour supprimer les erreurs, pas dans les modificateurs Smarty.

## 🏗️ Patterns de code recommandés

### Pattern 1 : Initialisation d'objets JSON vides

**Problème** : Un tableau vide PHP `[]` devient `[]` en JSON au lieu de `{}`

**❌ INCORRECT**
```php
$gridData = [];  // Génère [] (tableau) en JavaScript
```

**✅ CORRECT**
```php
$gridData = new stdClass();  // Génère {} (objet) en JavaScript
```

**En JavaScript (template)** :
```javascript
var gridData = {$gridData|json_encode};
if (!gridData || typeof gridData !== 'object' || Array.isArray(gridData)) {
    gridData = {};  // Fallback robuste
}
```

### Pattern 2 : Constructeur de classe admin héritant de MyPageSecure

**✅ Pattern standard**
```php
class MaPageAdmin extends MyPageSecure
{
    var $myBdd;

    function __construct()
    {
        parent::__construct(10);  // Profil requis (10 = tous les admins)

        $this->myBdd = new MyBdd();

        $this->SetTemplate("Titre_page", "Menu_actif", false);
        $this->Load();
        $this->DisplayTemplate('NomTemplate');
    }

    function Load()
    {
        $myBdd = $this->myBdd;
        // Logique de chargement des données
    }
}

$page = new MaPageAdmin();
```

**Points importants** :
- Toujours appeler `parent::__construct(profil_requis)`
- Initialiser `MyBdd` dans le constructeur
- Appeler `SetTemplate`, `Load`, `DisplayTemplate` dans cet ordre
- Instancier la classe à la fin du fichier

### Pattern 3 : Assignation des variables de langue au template

**✅ Pattern complet**
```php
// Dans la fonction Load()
$langue = parse_ini_file("../commun/MyLang.ini", true);
$langCode = utyGetSession('lang', 'fr');
if ($langCode == 'en') {
    $lang = $langue['en'];
} else {
    $lang = $langue['fr'];
    $langCode = 'fr';
}
$this->m_tpl->assign('lang', $langCode);  // Important : assigner au template
```

**Usage dans le template** :
```javascript
var lang = '{$lang}' || 'fr';
```

### Pattern 4 : Récupération de paramètres GET/POST

**✅ Utiliser les fonctions utilitaires**
```php
// Paramètres GET
$value = utyGetGet('param_name', 'default_value');

// Paramètres POST
$value = utyGetPost('param_name', 'default_value');

// Session
$value = utyGetSession('param_name', 'default_value');
```

**❌ Ne pas utiliser**
```php
$value = utyGetRequest('param_name');  // N'existe pas !
```

## 🔍 Validation et sécurité

### Validation JSON en JavaScript

```javascript
// Valider qu'une variable est un objet JSON valide (pas un tableau)
if (!gridData || typeof gridData !== 'object' || Array.isArray(gridData)) {
    gridData = {};
}
```

### Échappement des valeurs dans les templates

```smarty
{* Pour afficher du HTML échappé *}
<input value='{$pointsGrid|escape:"html"}' />

{* Pour du JSON dans un attribut data *}
<div data-config='{$config|json_encode|escape:"html"}'>
```

## 📝 Conventions de nommage

### Variables JavaScript depuis Smarty

```javascript
// ✅ Bon : Variable locale avec fallback
var lang = '{$lang}' || 'fr';

// ✅ Bon : Variable assignée depuis PHP
var gridData = {$gridData|json_encode};

// ❌ Mauvais : Utilisation directe sans validation
var data = {$someVar};  // Peut être undefined
```

### Noms de fonctions JavaScript

```javascript
// ✅ Bon : camelCase, noms descriptifs
function generateFields() { }
function copyToClipboard() { }
function applyToParent() { }

// ❌ Mauvais : snake_case ou noms courts
function gen_fields() { }
function copy() { }
```

## 🐛 Erreurs courantes à éviter

### 1. Cache Smarty non vidé

**Symptôme** : Les modifications du template ne sont pas prises en compte

**Solution** :
```bash
rm -rf /path/to/smarty/templates_c/*
```

### 2. Traductions non régénérées

**Symptôme** : Les nouvelles clés de traduction ne fonctionnent pas

**Solution** :
```bash
# Supprimer le fichier processed pour forcer la régénération
rm /path/to/commun/MyLang_processed.ini
# Le fichier sera régénéré automatiquement au prochain chargement de page
```

### 3. Parenthèses manquantes dans les fonctions

**❌ Erreur courante**
```javascript
onclick="maFonction"  // Manque ()
```

**✅ Correct**
```javascript
onclick="maFonction()"
```

### 4. Modificateur Smarty mal placé

**❌ Erreur**
```smarty
{$var@json_encode}  // @ avant le nom du modificateur
```

**✅ Correct**
```smarty
{$var|json_encode}  // Pipe | avant le modificateur
```

## 🎯 Checklist avant commit

Avant de commiter du code mêlant Smarty et JavaScript :

- [ ] Toutes les traductions dans JavaScript utilisent des guillemets doubles `"`
- [ ] Les modificateurs Smarty utilisent `|` et non `@`
- [ ] Les variables Smarty utilisées en JavaScript ont des fallbacks
- [ ] Le cache Smarty a été vidé pour tester
- [ ] Les traductions ont été ajoutées dans MyLang.ini (FR, EN, CN)
- [ ] Le constructeur appelle bien `parent::__construct(profil)`
- [ ] Les fonctions utilitaires (`utyGetGet`, etc.) sont utilisées correctement
- [ ] Le JSON généré est bien un objet `{}` et non un tableau `[]`

## 📚 Références

### Fichiers d'exemple

- **Bon exemple complet** : `sources/admin/GestionGrillePoints.php`
- **Template avec JavaScript** : `sources/smarty/templates/GestionGrillePoints.tpl`
- **Intégration Smarty/JS** : `sources/smarty/templates/GestionCompetition.tpl`

### Documentation connexe

- [COMPETITION_TYPE_MULTI.md](features/COMPETITION_TYPE_MULTI.md) - Documentation technique MULTI
- [DOC/user/MULTI_COMPETITION_SCORING_GRID.md](../../user/MULTI_COMPETITION_SCORING_GRID.md) - Documentation utilisateur

## 🔄 Mise à jour de ce document

Ce document doit être mis à jour chaque fois qu'une nouvelle bonne pratique est identifiée lors du développement.

**Dernière mise à jour** : Décembre 2024
**Version** : 1.0
**Contributeurs** : Développement éditeur grille de points MULTI
