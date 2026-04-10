# Plan de Migration - KPI

Ce document décrit la stratégie de migration du projet KPI vers des technologies modernes.

## 📋 Vue d'ensemble

### Objectifs
- Migrer vers PHP 8
- Mettre à jour FPDF (ou migrer vers TCPDF/mPDF)
- Mettre à jour Smarty
- Migrer vers Bootstrap 5
- Évaluer et moderniser l'usage de jQuery

### Durée estimée
**6 à 10 semaines** au total

---

## 🎯 Ordre de migration recommandé

| Phase | Composant | Durée | Risque | Priorité |
|-------|-----------|-------|--------|----------|
| 0 | **Audit complet** | 1 jour | - | 🔴 Critique |
| 1 | **PHP 8** | 2-4 sem | ⚠️⚠️⚠️ Élevé | 🔴 Critique |
| 2 | **FPDF → TCPDF** | 1 sem | ⚠️ Moyen | 🟡 Important |
| 3 | **Smarty** | 1-2 sem | ⚠️ Moyen | 🟡 Important |
| 4 | **Bootstrap 5** | 2-3 sem | ⚠️ Faible | 🟢 Normal |
| 5 | **jQuery** | À définir | ⚠️ Variable | 🟢 À évaluer |

---

## 📊 Phase 0 : Audit complet

### Objectif
Évaluer l'ampleur de chaque migration et identifier les points bloquants.

### Commandes d'audit

#### Audit PHP 8
```bash
# Fonctions deprecated à remplacer
grep -r "mysql_\|each(\|create_function\|__autoload" sources/ --include="*.php"

# Vérifier compatibilité stricte des types
grep -r "function.*:.*void" sources/ --include="*.php"

# Lister tous les fichiers PHP
find sources/ -name "*.php" | wc -l
```

#### Audit FPDF
```bash
# Localiser l'utilisation de FPDF
grep -r "new FPDF\|extends FPDF\|require.*fpdf" sources/ --include="*.php"

# Compter les fichiers concernés
find sources/ -name "*.php" -exec grep -l "FPDF" {} \; | wc -l

# Trouver la version
find sources/ -name "fpdf.php" -exec head -20 {} \;
grep -r "FPDF_VERSION" sources/ --include="*.php"
```

#### Audit Smarty
```bash
# Localiser l'installation Smarty
find sources/ -name "Smarty.class.php"

# Vérifier la version
find sources/ -name "Smarty.class.php" -exec grep -H "SMARTY_VERSION" {} \;

# Compter les templates
find sources/ -name "*.tpl" | wc -l
```

#### Audit Bootstrap
```bash
# Version Bootstrap actuelle
grep -r "bootstrap.*\.css\|bootstrap.*\.js" sources/ --include="*.html" --include="*.php" | head -10

# Localiser les CDN
grep -ri "cdn.*bootstrap\|maxcdn.*bootstrap" sources/
```

#### Audit jQuery
```bash
# Utilisation de jQuery
grep -r "\$(\|jQuery(" sources/ --include="*.js" --include="*.html" | wc -l

# Fichiers JavaScript utilisant jQuery
grep -rl "\$(" sources/ --include="*.js" | wc -l

# Fichiers HTML inline utilisant jQuery
grep -rl "\$(" sources/ --include="*.html" --include="*.php" | wc -l
```

### Livrables de l'audit
- [ ] Rapport des incompatibilités PHP 8
- [ ] Liste des fichiers utilisant FPDF
- [ ] Version de Smarty identifiée
- [ ] Version de Bootstrap identifiée
- [ ] Quantification de l'usage de jQuery
- [ ] Estimation précise du temps par phase

---

## 🔴 Phase 1 : Migration PHP 8 (2-4 semaines)

### Pourquoi en premier ?
- C'est la base : tout le reste dépend de PHP
- PHP 7.4 est en fin de vie (EOL novembre 2022)
- PHP 8.x apporte des améliorations de performance significatives (~20-30%)
- Permet de détecter immédiatement les incompatibilités

### Avantages de PHP 8
- Performance améliorée (JIT compiler)
- Named arguments
- Union types
- Match expression
- Nullsafe operator (`?->`)
- Meilleure sécurité

### Breaking changes à gérer

#### 1. Fonctions MySQL deprecated
```php
// ❌ Avant (PHP 5.x)
mysql_connect($host, $user, $pass);
mysql_query("SELECT * FROM users");

// ✅ Après (PHP 8) - Option 1 : MySQLi
$mysqli = new mysqli($host, $user, $pass, $db);
$result = $mysqli->query("SELECT * FROM users");

// ✅ Après (PHP 8) - Option 2 : PDO (recommandé)
$pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
$stmt = $pdo->query("SELECT * FROM users");
```

#### 2. Fonction each() supprimée
```php
// ❌ Avant
while (list($key, $val) = each($array)) {
    echo "$key => $val\n";
}

// ✅ Après
foreach ($array as $key => $val) {
    echo "$key => $val\n";
}
```

#### 3. create_function() supprimée
```php
// ❌ Avant
$func = create_function('$a,$b', 'return $a + $b;');

// ✅ Après
$func = function($a, $b) { return $a + $b; };
// ou
$func = fn($a, $b) => $a + $b;
```

#### 4. Typage strict
```php
// PHP 8 est plus strict sur les types
// ❌ Erreur potentielle
function sum($a, $b): int {
    return $a + $b;  // Si $a ou $b sont string, erreur possible
}

// ✅ Meilleure pratique
function sum(int $a, int $b): int {
    return $a + $b;
}
```

### Plan d'action

#### Étape 1 : Préparation (1 jour)
```bash
# Créer une branche de migration
git checkout -b migration/php8

# Tag de sauvegarde
git tag v-before-php8-migration

# Vérifier que le container PHP 8 fonctionne
make php8_bash
php -v  # Doit afficher PHP 8.x
```

#### Étape 2 : Tests initiaux (2-3 jours)
```bash
# Démarrer l'application sur PHP 8
# Via le container kpi8 (port 8803)
curl http://localhost:8803

# Activer tous les warnings
# Dans php.ini ou .htaccess
error_reporting(E_ALL);
display_errors = On;
```

#### Étape 3 : Corrections (1-3 semaines)
- Corriger les erreurs critiques (fatal errors)
- Corriger les warnings deprecated
- Adapter les types
- Mettre à jour les bibliothèques tierces

#### Étape 4 : Tests fonctionnels (3-5 jours)
- Tester toutes les fonctionnalités principales
- Vérifier les formulaires
- Tester les rapports PDF
- Vérifier les exports
- Tester l'API

#### Étape 5 : Bascule (1 jour)
```bash
# Mettre à jour compose.dev.yaml pour utiliser PHP 8 par défaut
# Redémarrer les containers
make docker_dev_restart

# Surveiller les logs
make docker_dev_logs
```

### Checklist PHP 8

**Code PHP :**
- [ ] Remplacer `mysql_*` par `mysqli_*` ou PDO
- [ ] Corriger `each()` → `foreach()`
- [ ] Remplacer `create_function()` par closures
- [ ] Supprimer `__autoload()` → utiliser `spl_autoload_register()`
- [ ] Adapter les expressions régulières PCRE
- [ ] Vérifier les comparaisons de types (`==` vs `===`)
- [ ] Corriger les signatures de fonctions
- [ ] Adapter le code aux union types si nécessaire

**Tests :**
- [ ] Tester avec `error_reporting(E_ALL)`
- [ ] Vérifier tous les formulaires
- [ ] Tester la génération de PDFs
- [ ] Vérifier les sessions
- [ ] Tester l'authentification
- [ ] Vérifier les exports (CSV, Excel, etc.)
- [ ] Tester l'API REST

**Performance :**
- [ ] Activer OPcache
- [ ] Configurer le JIT compiler
- [ ] Benchmarker les performances

### Ressources
- [Guide de migration PHP 8](https://www.php.net/manual/fr/migration80.php)
- [PHP 8 Breaking Changes](https://www.php.net/manual/fr/migration80.incompatible.php)
- [Rector - Outil de migration automatique](https://github.com/rectorphp/rector)

---

## 📄 Phase 2 : Migration FPDF → TCPDF (1 semaine)

### Pourquoi migrer ?
- FPDF n'est plus activement maintenu (dernière version 1.86 en 2021)
- Incompatibilités possibles avec PHP 8
- Limitations : pas de support UTF-8 natif, API limitée

### Options de migration

#### Option A : TCPDF ⭐ (Recommandé)
**Avantages :**
- ✅ Compatible PHP 8
- ✅ Support UTF-8 natif
- ✅ API similaire à FPDF (migration facile)
- ✅ Plus de fonctionnalités (HTML to PDF, barcodes, etc.)
- ✅ Activement maintenu
- ✅ Bonne documentation

**Exemple de migration :**
```php
// Avant (FPDF)
require('fpdf/fpdf.php');
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(40, 10, 'Hello World!');
$pdf->Output();

// Après (TCPDF)
require_once('tcpdf/tcpdf.php');
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 16);  // helvetica au lieu d'Arial
$pdf->Cell(40, 10, 'Hello World!');
$pdf->Output();
```

#### Option B : mPDF
**Avantages :**
- ✅ Conversion HTML/CSS → PDF directe
- ✅ Support complet CSS
- ✅ UTF-8 natif
- ✅ Plus simple pour du contenu riche

**Inconvénient :**
- ❌ API complètement différente (refactoring important)

**Exemple :**
```php
require_once 'vendor/autoload.php';
$mpdf = new \Mpdf\Mpdf();
$mpdf->WriteHTML('<h1>Hello World</h1>');
$mpdf->Output();
```

#### Option C : Dompdf
**Avantages :**
- Support HTML/CSS
- Facile à utiliser

**Inconvénients :**
- Plus lent que TCPDF/mPDF
- Moins de fonctionnalités

### Plan d'action (TCPDF recommandé)

#### Étape 1 : Installation (1 jour)
```bash
# Via Composer
composer require tecnickcom/tcpdf

# Ou téléchargement manuel
# https://github.com/tecnickcom/tcpdf
```

#### Étape 2 : Créer une classe wrapper (1 jour)
```php
/**
 * Classe de transition FPDF → TCPDF
 * Permet une migration progressive
 */
class PdfGenerator extends TCPDF {

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4') {
        parent::__construct($orientation, $unit, $format, true, 'UTF-8', false);

        // Configuration par défaut
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor('KPI');
        $this->SetTitle('Document');

        // Marges
        $this->SetMargins(15, 15, 15);
        $this->SetAutoPageBreak(true, 25);
    }

    // Ajouter des méthodes custom si nécessaire
    // pour faciliter la migration
}
```

#### Étape 3 : Migration progressive (3-4 jours)
1. Lister tous les fichiers utilisant FPDF
2. Migrer fichier par fichier
3. Tester chaque PDF généré
4. Comparer visuellement avec l'ancien

#### Étape 4 : Tests (1 jour)
- Générer tous les types de PDFs
- Vérifier l'encodage UTF-8
- Tester les polices
- Vérifier les images
- Tester sur différents navigateurs

### Checklist FPDF → TCPDF

**Installation :**
- [ ] Installer TCPDF via Composer ou manuellement
- [ ] Vérifier la compatibilité PHP 8
- [ ] Configurer les chemins des polices

**Migration :**
- [ ] Localiser tous les fichiers utilisant FPDF
- [ ] Créer une classe wrapper si nécessaire
- [ ] Migrer les fichiers un par un
- [ ] Remplacer `Arial` par `helvetica` ou autre police
- [ ] Adapter les méthodes spécifiques si nécessaire

**Tests :**
- [ ] Tester tous les PDFs générés
- [ ] Vérifier encodage UTF-8
- [ ] Tester avec caractères spéciaux (é, è, ê, etc.)
- [ ] Vérifier les images
- [ ] Tester les tableaux
- [ ] Vérifier la mise en page

**Performance :**
- [ ] Comparer temps de génération
- [ ] Optimiser si nécessaire

### Différences FPDF → TCPDF

| FPDF | TCPDF | Notes |
|------|-------|-------|
| `Arial` | `helvetica` | Noms de polices différents |
| `AddPage()` | `AddPage()` | Identique |
| `Cell()` | `Cell()` | Identique |
| Pas d'UTF-8 natif | UTF-8 natif | Encodage simplifié |
| - | `writeHTML()` | Nouvelle fonctionnalité |

### Ressources
- [TCPDF Documentation](https://tcpdf.org/)
- [TCPDF Examples](https://tcpdf.org/examples/)
- [GitHub TCPDF](https://github.com/tecnickcom/tcpdf)

---

## 🔧 Phase 3 : Mise à jour Smarty (1-2 semaines)

### Pourquoi mettre à jour ?
- Compatibilité PHP 8
- Nouvelles fonctionnalités
- Meilleures performances
- Support actif

### Versions Smarty

| Version | PHP requis | Status | Notes |
|---------|------------|--------|-------|
| Smarty 2.x | PHP 5.2+ | ⚠️ EOL | Obsolète |
| Smarty 3.x | PHP 5.3+ | ⚠️ Maintenance | Legacy |
| Smarty 4.x | PHP 7.1+ | ✅ Stable | Recommandé |
| Smarty 5.x | PHP 8.0+ | 🚀 Latest | Moderne |

### Migration recommandée

**Si actuellement en Smarty 2.x :** → Smarty 4.x
**Si actuellement en Smarty 3.x :** → Smarty 4.x ou 5.x

### Plan d'action

#### Étape 1 : Identifier la version actuelle (1 heure)
```bash
# Localiser Smarty
find sources/ -name "Smarty.class.php"

# Vérifier la version
grep "SMARTY_VERSION" sources/*/Smarty.class.php
```

#### Étape 2 : Installation Smarty 4.x (1 jour)
```bash
# Via Composer (recommandé)
composer require smarty/smarty:^4.0

# Ou téléchargement manuel
# https://github.com/smarty-php/smarty
```

#### Étape 3 : Configuration (1 jour)
```php
require_once 'vendor/smarty/smarty/libs/Smarty.class.php';

$smarty = new Smarty();
$smarty->setTemplateDir('templates/');
$smarty->setCompileDir('templates_c/');
$smarty->setConfigDir('configs/');
$smarty->setCacheDir('cache/');
```

#### Étape 4 : Tests des templates (3-5 jours)
- Tester tous les templates .tpl
- Vérifier la syntaxe
- Corriger les incompatibilités

#### Étape 5 : Migration progressive (2-3 jours)
- Page par page
- Vérifier le rendu
- Corriger les bugs

### Breaking changes Smarty 3.x → 4.x

#### 1. Délimiteurs par défaut
```smarty
{* Smarty 3 et 4 utilisent les mêmes délimiteurs *}
{$variable}
{if $condition}...{/if}
```

#### 2. Plugins
```php
// Enregistrer un plugin
$smarty->registerPlugin('modifier', 'mymodifier', 'my_modifier_function');
```

#### 3. Syntaxe des templates
La plupart des templates restent compatibles.

### Checklist Smarty

**Installation :**
- [ ] Installer Smarty 4.x via Composer
- [ ] Configurer les chemins (templates, cache, compile)
- [ ] Vérifier les permissions des dossiers

**Migration :**
- [ ] Lister tous les templates .tpl
- [ ] Tester chaque template
- [ ] Vérifier les plugins personnalisés
- [ ] Adapter les fonctions deprecated

**Tests :**
- [ ] Tester toutes les pages
- [ ] Vérifier le cache
- [ ] Tester les boucles et conditions
- [ ] Vérifier l'affichage des variables
- [ ] Tester les filtres/modificateurs

**Performance :**
- [ ] Activer le cache Smarty
- [ ] Vérifier temps de compilation
- [ ] Optimiser si nécessaire

### Ressources
- [Smarty 4 Documentation](https://www.smarty.net/docs/en/)
- [Migration Guide](https://www.smarty.net/docs/en/upgrading.tpl)
- [GitHub Smarty](https://github.com/smarty-php/smarty)

---

## 🎨 Phase 4 : Migration Bootstrap 5 (2-3 semaines)

### Pourquoi Bootstrap 5 ?
- Design moderne
- Plus léger (pas de dépendance jQuery)
- Meilleur support responsive
- Nouvelles fonctionnalités (offcanvas, etc.)
- Support actif

### Différences majeures

| Bootstrap 3/4 | Bootstrap 5 | Impact |
|---------------|-------------|--------|
| Dépend de jQuery | Vanilla JS | ⚠️ Breaking |
| `.pull-right` | `.float-end` | Classes renommées |
| `.ml-*`, `.mr-*` | `.ms-*`, `.me-*` | Start/End au lieu de Left/Right |
| `.panel` | `.card` | Composant renommé |
| `data-toggle` | `data-bs-toggle` | Attributs préfixés |

### Stratégies de migration

#### Option A : Migration progressive (Recommandé)
- Page par page
- Permet de tester au fur et à mesure
- Moins de risque de régression

#### Option B : Migration complète
- Tout en une fois
- Plus rapide mais plus risqué
- Nécessite tests intensifs

### Plan d'action (migration progressive)

#### Étape 1 : Préparation (1 jour)
```bash
# Identifier la version Bootstrap actuelle
grep -ri "bootstrap.*css\|bootstrap.*js" sources/ | head -10

# Créer une branche
git checkout -b migration/bootstrap5
```

#### Étape 2 : Installation Bootstrap 5 (1 jour)
```html
<!-- CDN Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Ou local -->
<!-- Télécharger depuis https://getbootstrap.com/ -->
```

#### Étape 3 : Migration des classes CSS (1-2 semaines)

**Principales modifications :**

```html
<!-- Alignement -->
<!-- Avant -->
<div class="pull-right">...</div>
<div class="pull-left">...</div>

<!-- Après -->
<div class="float-end">...</div>
<div class="float-start">...</div>

<!-- Marges et padding -->
<!-- Avant -->
<div class="ml-3 mr-2">...</div>

<!-- Après -->
<div class="ms-3 me-2">...</div>

<!-- Panels → Cards -->
<!-- Avant -->
<div class="panel panel-default">
    <div class="panel-heading">Titre</div>
    <div class="panel-body">Contenu</div>
</div>

<!-- Après -->
<div class="card">
    <div class="card-header">Titre</div>
    <div class="card-body">Contenu</div>
</div>

<!-- Attributs data -->
<!-- Avant -->
<button data-toggle="modal" data-target="#myModal">

<!-- Après -->
<button data-bs-toggle="modal" data-bs-target="#myModal">
```

#### Étape 4 : Migration JavaScript (3-5 jours)
```javascript
// Avant (Bootstrap 3/4 avec jQuery)
$('#myModal').modal('show');
$('.tooltip').tooltip();

// Après (Bootstrap 5 Vanilla JS)
var myModal = new bootstrap.Modal(document.getElementById('myModal'));
myModal.show();

var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});
```

#### Étape 5 : Tests visuels (3-5 jours)
- Tester sur tous les navigateurs
- Vérifier le responsive (mobile, tablette, desktop)
- Tester tous les composants interactifs

### Checklist Bootstrap 5

**Installation :**
- [ ] Installer Bootstrap 5 (CDN ou local)
- [ ] Supprimer références à Bootstrap 3/4
- [ ] Vérifier compatibilité avec thème custom

**Migration CSS :**
- [ ] `.pull-*` → `.float-*`
- [ ] `.ml-*`, `.mr-*` → `.ms-*`, `.me-*`
- [ ] `.pl-*`, `.pr-*` → `.ps-*`, `.pe-*`
- [ ] `.panel` → `.card`
- [ ] `.label` → `.badge`
- [ ] Adapter les breakpoints si nécessaire

**Migration JavaScript :**
- [ ] `data-toggle` → `data-bs-toggle`
- [ ] `data-target` → `data-bs-target`
- [ ] Remplacer jQuery par Vanilla JS
- [ ] Adapter les événements Bootstrap

**Composants à tester :**
- [ ] Navigation (navbar)
- [ ] Modals
- [ ] Tooltips
- [ ] Popovers
- [ ] Dropdowns
- [ ] Accordions
- [ ] Carousels
- [ ] Formulaires
- [ ] Tableaux
- [ ] Alertes

**Tests :**
- [ ] Chrome
- [ ] Firefox
- [ ] Safari
- [ ] Edge
- [ ] Mobile (iOS/Android)
- [ ] Tablette
- [ ] Tests responsive

### Outils de migration

**Bootstrap Migration Tool :**
- [Bootstrap 3 to 5 Migration Guide](https://getbootstrap.com/docs/5.3/migration/)
- Regex pour remplacement automatique

### Ressources
- [Bootstrap 5 Documentation](https://getbootstrap.com/docs/5.3/)
- [Migration from v4](https://getbootstrap.com/docs/5.3/migration/)
- [Bootstrap 5 Examples](https://getbootstrap.com/docs/5.3/examples/)

---

## 🔄 Phase 5 : jQuery - Évaluation et décision

### Contexte
Bootstrap 5 ne nécessite plus jQuery. C'est l'occasion d'évaluer si jQuery est encore nécessaire dans le projet.

### Options

#### Option A : Garder jQuery (Solution rapide)
**Avantages :**
- ✅ Pas de refactoring
- ✅ Compatible avec code existant
- ✅ Rapidité de mise en œuvre

**Inconvénients :**
- ❌ Dépendance supplémentaire (~30 KB gzipped)
- ❌ Technologie vieillissante
- ❌ Performance inférieure au Vanilla JS moderne

**Quand choisir :**
- Si beaucoup de code métier dépend de jQuery
- Si budget/temps limité
- Si équipe non familière avec Vanilla JS moderne

#### Option B : Migrer vers Vanilla JS (Solution moderne)
**Avantages :**
- ✅ Pas de dépendance
- ✅ Performance améliorée
- ✅ Code moderne
- ✅ Meilleures pratiques

**Inconvénients :**
- ❌ Refactoring complet nécessaire
- ❌ Temps de développement important
- ❌ Courbe d'apprentissage

**Quand choisir :**
- Si peu de code dépend de jQuery
- Si budget permet le refactoring
- Si vision long terme

#### Option C : Solution hybride ⭐ (Recommandé)
**Stratégie :**
- Garder jQuery pour le code legacy
- Nouveau code en Vanilla JS / ES6+
- Migration progressive quand opportun

**Avantages :**
- ✅ Migration progressive
- ✅ Pas de rush
- ✅ Apprentissage progressif

### Équivalences jQuery → Vanilla JS

#### Sélecteurs
```javascript
// jQuery
$('.my-class')
$('#my-id')
$('div')

// Vanilla JS
document.querySelectorAll('.my-class')
document.getElementById('my-id')
document.querySelectorAll('div')
```

#### Manipulation DOM
```javascript
// jQuery
$('.my-class').addClass('active')
$('.my-class').removeClass('active')
$('.my-class').toggleClass('active')
$('.my-class').html('New content')
$('.my-class').text('New text')

// Vanilla JS
document.querySelectorAll('.my-class').forEach(el => el.classList.add('active'))
document.querySelectorAll('.my-class').forEach(el => el.classList.remove('active'))
document.querySelectorAll('.my-class').forEach(el => el.classList.toggle('active'))
document.querySelectorAll('.my-class').forEach(el => el.innerHTML = 'New content')
document.querySelectorAll('.my-class').forEach(el => el.textContent = 'New text')
```

#### Événements
```javascript
// jQuery
$('.my-class').on('click', function() {
    console.log('clicked');
});

// Vanilla JS
document.querySelectorAll('.my-class').forEach(el => {
    el.addEventListener('click', () => {
        console.log('clicked');
    });
});
```

#### AJAX
```javascript
// jQuery
$.ajax({
    url: '/api/data',
    method: 'GET',
    success: function(data) {
        console.log(data);
    }
});

// Vanilla JS (Fetch API)
fetch('/api/data')
    .then(response => response.json())
    .then(data => console.log(data));

// Vanilla JS (Async/Await)
async function getData() {
    const response = await fetch('/api/data');
    const data = await response.json();
    console.log(data);
}
```

### Plan d'action (si migration choisie)

#### Étape 1 : Audit (2-3 jours)
```bash
# Compter les usages jQuery
grep -r "\$(" sources/ --include="*.js" --include="*.html" | wc -l

# Lister les fichiers concernés
grep -rl "\$(" sources/ --include="*.js" > jquery_files.txt
```

#### Étape 2 : Priorisation (1 jour)
- Identifier les fichiers critiques
- Évaluer l'effort de migration
- Planifier l'ordre de migration

#### Étape 3 : Migration progressive (selon volume)
- Fichier par fichier
- Tester chaque modification
- Utiliser des polyfills si nécessaire

### Checklist jQuery

**Audit :**
- [ ] Compter les usages jQuery
- [ ] Lister tous les fichiers concernés
- [ ] Identifier les plugins jQuery utilisés
- [ ] Évaluer l'effort de migration

**Décision :**
- [ ] Choix de stratégie : garder / migrer / hybride
- [ ] Validation équipe
- [ ] Planning si migration

**Si migration :**
- [ ] Créer guide de migration interne
- [ ] Former l'équipe Vanilla JS / ES6+
- [ ] Configurer ESLint pour ES6+
- [ ] Migrer fichier par fichier
- [ ] Tester systématiquement

### Ressources
- [You Might Not Need jQuery](https://youmightnotneedjquery.com/)
- [Vanilla JS Toolkit](https://vanillajstoolkit.com/)
- [MDN JavaScript Guide](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide)

---

## 📝 Suivi de la migration

### Checklist globale

#### Phase 0 : Audit ✅
- [ ] Audit PHP 8 effectué
- [ ] Audit FPDF effectué
- [ ] Audit Smarty effectué
- [ ] Audit Bootstrap effectué
- [ ] Audit jQuery effectué
- [ ] Rapport d'audit finalisé
- [ ] Estimations validées

#### Phase 1 : PHP 8 🔴
- [ ] Branche créée
- [ ] Container PHP 8 testé
- [ ] Incompatibilités identifiées
- [ ] Fonctions deprecated corrigées
- [ ] Types adaptés
- [ ] Tests fonctionnels OK
- [ ] Performance validée
- [ ] Documentation mise à jour
- [ ] Merge en master

#### Phase 2 : FPDF → TCPDF 📄
- [ ] TCPDF installé
- [ ] Classe wrapper créée
- [ ] Liste fichiers FPDF établie
- [ ] Migration effectuée
- [ ] Tests visuels OK
- [ ] Encodage UTF-8 validé
- [ ] Performance acceptable
- [ ] Documentation mise à jour

#### Phase 3 : Smarty 🔧
- [ ] Version actuelle identifiée
- [ ] Smarty 4.x installé
- [ ] Configuration effectuée
- [ ] Templates testés
- [ ] Plugins vérifiés
- [ ] Tests fonctionnels OK
- [ ] Cache optimisé
- [ ] Documentation mise à jour

#### Phase 4 : Bootstrap 5 🎨
- [ ] Bootstrap 5 installé
- [ ] Classes CSS migrées
- [ ] JavaScript migré
- [ ] Tests visuels OK
- [ ] Responsive validé
- [ ] Cross-browser testé
- [ ] Performance acceptable
- [ ] Documentation mise à jour

#### Phase 5 : jQuery 🔄
- [ ] Audit effectué
- [ ] Décision prise
- [ ] Plan établi (si migration)
- [ ] Migration effectuée (si applicable)
- [ ] Tests OK
- [ ] Documentation mise à jour

---

## 🚨 Gestion des risques

### Risques identifiés

| Risque | Impact | Probabilité | Mitigation |
|--------|--------|-------------|------------|
| Breaking changes PHP 8 | ⚠️⚠️⚠️ Élevé | Haute | Tests intensifs, migration progressive |
| Régression FPDF | ⚠️ Moyen | Moyenne | Tests visuels systématiques |
| Incompatibilité templates Smarty | ⚠️ Moyen | Faible | Tests unitaires templates |
| Régression visuelle Bootstrap | ⚠️ Faible | Moyenne | Screenshots avant/après |
| Bugs JavaScript sans jQuery | ⚠️ Moyen | Moyenne | Tests fonctionnels intensifs |

### Stratégie de rollback

Pour chaque phase :
```bash
# Créer un tag avant migration
git tag v-before-[phase]

# Si problème, revenir en arrière
git checkout v-before-[phase]

# Ou annuler les derniers commits
git revert HEAD~[n]
```

### Tests de non-régression

**Avant chaque phase :**
- [ ] Créer un snapshot de la base de données
- [ ] Documenter l'état actuel
- [ ] Créer des tests automatisés si possible

**Pendant la phase :**
- [ ] Tests unitaires
- [ ] Tests fonctionnels
- [ ] Tests visuels (screenshots)

**Après la phase :**
- [ ] Validation utilisateur
- [ ] Tests de performance
- [ ] Monitoring erreurs

---

## 📊 Métriques de succès

### Objectifs quantifiables

#### Performance
- [ ] Temps de chargement réduit de 20%
- [ ] Temps de génération PDF stable ou amélioré
- [ ] Utilisation mémoire optimisée

#### Qualité
- [ ] Zéro erreur PHP en production
- [ ] Zéro warning deprecated
- [ ] Code coverage tests > 50%

#### Modernité
- [ ] PHP 8+ en production
- [ ] Bibliothèques à jour
- [ ] Code conforme standards 2024

---

## 🎓 Formation et documentation

### Documentation à créer/mettre à jour

- [ ] Guide développeur PHP 8
- [ ] Guide TCPDF
- [ ] Guide Smarty 4
- [ ] Guide Bootstrap 5
- [ ] Guide Vanilla JS (si migration jQuery)
- [ ] Standards de code
- [ ] Guide de déploiement

### Formation équipe

- [ ] Session PHP 8 nouveautés
- [ ] Workshop TCPDF
- [ ] Formation Bootstrap 5
- [ ] Formation JavaScript moderne (si applicable)

---

## 📅 Timeline suggérée

### Planning optimiste (6 semaines)
```
Semaine 1 : Audit + PHP 8 (début)
Semaine 2-3 : PHP 8 (suite et fin)
Semaine 4 : FPDF → TCPDF + Smarty (début)
Semaine 5 : Smarty (fin) + Bootstrap 5 (début)
Semaine 6 : Bootstrap 5 (fin) + jQuery (décision)
```

### Planning réaliste (10 semaines)
```
Semaine 1 : Audit complet
Semaine 2-4 : PHP 8
Semaine 5 : FPDF → TCPDF
Semaine 6-7 : Smarty
Semaine 8-9 : Bootstrap 5
Semaine 10 : jQuery (audit et décision)
```

### Planning avec imprévus (12-14 semaines)
Prévoir 20% de buffer pour les imprévus.

---

## 🔗 Ressources utiles

### Documentation officielle
- [PHP 8 Documentation](https://www.php.net/manual/fr/migration80.php)
- [TCPDF](https://tcpdf.org/)
- [Smarty](https://www.smarty.net/)
- [Bootstrap 5](https://getbootstrap.com/)
- [MDN Web Docs](https://developer.mozilla.org/)

### Outils
- [Rector](https://github.com/rectorphp/rector) - Migration automatique PHP
- [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) - Standards de code
- [PHPStan](https://phpstan.org/) - Analyse statique
- [Composer](https://getcomposer.org/) - Gestionnaire de dépendances

### Communauté
- [Stack Overflow](https://stackoverflow.com/)
- [PHP.net Forums](https://www.php.net/)
- [Reddit r/PHP](https://www.reddit.com/r/PHP/)

---

## 📞 Support

Pour toute question sur cette migration :
- Consulter ce document
- Vérifier la documentation officielle
- Ouvrir une issue GitHub
- Contacter l'équipe technique

---

**Dernière mise à jour :** 2025-01-19
**Version :** 1.0
**Auteur :** Équipe KPI
