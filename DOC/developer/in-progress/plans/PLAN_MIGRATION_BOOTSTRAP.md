# Plan de Migration Bootstrap vers 5.3.8

**Date**: 29 octobre 2025
**Objectif**: Unifier toutes les versions de Bootstrap vers 5.3.8 (backend uniquement)
**Statut**: 📋 PLANIFIÉ

---

## Table des matières

1. [État actuel](#état-actuel)
2. [Inventaire détaillé](#inventaire-détaillé)
3. [Plan de migration](#plan-de-migration)
4. [Phase 1: Installation Bootstrap 5.3.8](#phase-1-installation-bootstrap-538)
5. [Phase 2: Migration Bootstrap 5.x → 5.3.8](#phase-2-migration-bootstrap-5x--538)
6. [Phase 3: Migration Bootstrap 3.x → 5.3.8](#phase-3-migration-bootstrap-3x--538)
7. [Tests et validation](#tests-et-validation)
8. [Breaking changes majeurs](#breaking-changes-majeurs)
9. [Checklist migration](#checklist-migration)

---

## État actuel

### Versions présentes (backend uniquement)

| Version | Localisation | Statut | Nb fichiers utilisant |
|---------|--------------|--------|----------------------|
| **3.4.1** | `sources/js/bootstrap/` | ❌ Obsolète | 9 fichiers |
| **3.3.0** | `sources/js/bootstrap-3.3.1/` | ❌ Obsolète | 0 fichiers (non utilisé) |
| **5.0.2** | `sources/js/bootstrap-5.0.2-dist/` | ⚠️ Ancien | 2 fichiers |
| **5.1.3** | `sources/lib/bootstrap-5.1.3-dist/` | ⚠️ Ancien | 13 fichiers (live/) |

### Objectif cible

**Bootstrap 5.3.8** via Composer
- ✅ Version stable actuelle
- ✅ Compatible PHP 8.4+
- ✅ Pas de dépendance jQuery
- ✅ Dark mode natif
- ✅ Nouvelles utilities CSS

---

## Inventaire détaillé

### 1. Bootstrap 3.4.1 (`js/bootstrap/`) - 9 fichiers

**Templates Smarty (backend admin)**:
```
sources/smarty/templates/
├── pagelogin.tpl          → Login page
├── fppage.tpl             → Page générique
├── kppageleaflet.tpl      → Page avec Leaflet (cartes)
├── frame_page.tpl         → Frame générique
├── kppage.tpl             → Page KP standard
├── kppagewide.tpl         → Page KP large
└── tv.php                 → Page TV (affichage live)
```

**Caractéristiques**:
- ⚠️ **Dépend de jQuery**
- Utilisé principalement dans templates admin
- Composants: modals, dropdowns, tooltips, tabs
- CSS Grid non supporté

### 2. Bootstrap 3.3.0 (`js/bootstrap-3.3.1/`) - NON UTILISÉ

**Statut**: ❌ Peut être supprimé immédiatement
- Aucun fichier ne référence ce répertoire
- Version intermédiaire jamais utilisée

### 3. Bootstrap 5.0.2 (`js/bootstrap-5.0.2-dist/`) - 2 fichiers

**Fichiers**:
```
sources/admin/
├── shotclock.php          → Chronomètre temps d'action (commenté)
└── scoreboard.php         → Tableau de scores
```

**Statut**:
- shotclock.php: Bootstrap 5.0.2 en commentaire (n'utilise pas réellement)
- scoreboard.php: Utilise activement Bootstrap 5.0.2

### 4. Bootstrap 5.1.3 (`lib/bootstrap-5.1.3-dist/`) - 13 fichiers

**Fichiers (tous dans `sources/live/`)**:
```
sources/live/
├── score_e.php            → Score événement
├── tv2.php                → TV display v2
├── next_game_club.php     → Prochain match club
├── teams_club.php         → Équipes club
├── teams.php              → Équipes
├── next_game.php          → Prochain match
├── score_club_e.php       → Score club événement
├── score_o.php            → Score officiel
├── score.php              → Score principal
├── score_s.php            → Score simple
├── score_club.php         → Score club
├── score_club_s.php       → Score club simple
└── score_club_o.php       → Score club officiel
```

**Caractéristiques**:
- ✅ Pas de dépendance jQuery
- Version relativement récente
- Utilisé pour affichages publics (live scores)

---

## Plan de migration

### Priorités

1. **PHASE 1** - Installation Bootstrap 5.3.8 via Composer ⭐ **PRIORITAIRE**
2. **PHASE 2** - Migration Bootstrap 5.x → 5.3.8 (facile, peu de breaking changes)
3. **PHASE 3** - Migration Bootstrap 3.x → 5.3.8 (complexe, breaking changes majeurs)

### Stratégie

```
┌─────────────────────────────────────────────────────────────┐
│  STRATÉGIE: Progressive avec tests à chaque étape          │
├─────────────────────────────────────────────────────────────┤
│  ✅ Installer 5.3.8 (nouvelle source unique)                │
│  ✅ Migrer 5.1.3 → 5.3.8 (13 fichiers, faible risque)      │
│  ✅ Migrer 5.0.2 → 5.3.8 (2 fichiers, faible risque)       │
│  ⚠️  Migrer 3.4.1 → 5.3.8 (9 fichiers, PRUDENCE)           │
│  ❌ Supprimer anciennes versions                            │
└─────────────────────────────────────────────────────────────┘
```

---

## Phase 1: Installation Bootstrap 5.3.8

### 1.1 Installation via Composer

```bash
# Via Makefile (recommandé)
make backend_composer_require package=twbs/bootstrap:^5.3

# Ou directement
docker exec kpi_php8 composer require twbs/bootstrap:^5.3
```

**Résultat attendu**:
```
sources/vendor/twbs/bootstrap/
├── dist/
│   ├── css/
│   │   ├── bootstrap.min.css
│   │   ├── bootstrap.css
│   │   └── bootstrap.rtl.min.css
│   └── js/
│       ├── bootstrap.bundle.min.js  (inclut Popper.js)
│       ├── bootstrap.bundle.js
│       ├── bootstrap.min.js
│       └── bootstrap.js
├── scss/                            (sources Sass)
└── package.json
```

### 1.2 Avantages Composer vs fichiers statiques

| Aspect | Fichiers statiques | Composer |
|--------|-------------------|----------|
| **Mises à jour** | ❌ Manuel | ✅ `composer update` |
| **Version tracking** | ❌ Commentaires | ✅ composer.lock |
| **Intégrité** | ⚠️ Risque modification | ✅ Checksums |
| **Taille repo** | ❌ +500 KB par version | ✅ Ignoré (.gitignore) |
| **Compatibilité** | ⚠️ À vérifier | ✅ Gérée par Composer |

### 1.3 Configuration recommandée

**Créer**: `sources/commun/BootstrapHelper.php`

```php
<?php
/**
 * Helper Bootstrap 5.3.8 - Chemins standardisés
 */
class BootstrapHelper
{
    const VERSION = '5.3.8';
    const VENDOR_PATH = '../vendor/twbs/bootstrap/dist';

    public static function getCssPath($minified = true)
    {
        $file = $minified ? 'bootstrap.min.css' : 'bootstrap.css';
        return self::VENDOR_PATH . '/css/' . $file;
    }

    public static function getJsPath($bundle = true, $minified = true)
    {
        $prefix = $bundle ? 'bootstrap.bundle' : 'bootstrap';
        $suffix = $minified ? '.min.js' : '.js';
        return self::VENDOR_PATH . '/js/' . $prefix . $suffix;
    }

    public static function getCssTag($minified = true)
    {
        $path = self::getCssPath($minified);
        $version = self::VERSION;
        return "<link href=\"{$path}?v={$version}\" rel=\"stylesheet\">";
    }

    public static function getJsTag($bundle = true, $minified = true)
    {
        $path = self::getJsPath($bundle, $minified);
        $version = self::VERSION;
        return "<script src=\"{$path}?v={$version}\"></script>";
    }
}
```

**Usage dans templates Smarty**:

```smarty
{* Au lieu de hardcoder les chemins *}
{php}
    require_once('../commun/BootstrapHelper.php');
    echo BootstrapHelper::getCssTag();
{/php}
```

---

## Phase 2: Migration Bootstrap 5.x → 5.3.8

### 2.1 Fichiers concernés (15 fichiers)

**Groupe A: Bootstrap 5.1.3 (13 fichiers - live/)**
- Changements mineurs 5.1.3 → 5.3.8
- Principalement nouvelles features, peu de breaking changes

**Groupe B: Bootstrap 5.0.2 (2 fichiers - admin/)**
- scoreboard.php
- shotclock.php (commenté, à vérifier)

### 2.2 Breaking changes 5.1.3 → 5.3.8

#### Changements CSS mineurs

**1. Nouvelles classes utilitaires**
```css
/* Nouvelles: */
.text-bg-*          /* Texte + background combinés */
.link-opacity-*     /* Opacité des liens */
.link-offset-*      /* Offset des underlines */
.z-*                /* Z-index utilities */
```

**2. Dark mode amélioré**
```html
<!-- Nouveau: support data-bs-theme -->
<html data-bs-theme="dark">
<div data-bs-theme="light">  <!-- Override local -->
```

**3. Deprecations mineures**
- ⚠️ `.text-muted` → `.text-body-secondary` (ancien fonctionne encore)
- ⚠️ `.bg-gradient` → Styles améliorés

#### Changements JavaScript mineurs

**Aucun breaking change majeur** entre 5.1.3 et 5.3.8 au niveau JS.

Nouvelles features optionnelles:
- Color modes API
- Floating labels améliorés
- Focus ring utilities

### 2.3 Plan d'action Phase 2

**Étape 2.1: Migration Groupe A (Bootstrap 5.1.3 → 5.3.8)**

```bash
# 13 fichiers dans sources/live/
```

**Modifications type** (exemple: score.php):

```diff
- <link href="../lib/bootstrap-5.1.3-dist/css/bootstrap.min.css?v=<?= NUM_VERSION ?>" rel="stylesheet">
+ <link href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css?v=5.3.8" rel="stylesheet">

- <script src="../lib/bootstrap-5.1.3-dist/js/bootstrap.min.js?v=<?= NUM_VERSION ?>"></script>
+ <script src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js?v=5.3.8"></script>
```

**Note**: Utiliser `bootstrap.bundle.min.js` (inclut Popper.js)

**Étape 2.2: Migration Groupe B (Bootstrap 5.0.2 → 5.3.8)**

Fichier: `scoreboard.php`

```diff
- <link href="../js/bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet">
+ <link href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css?v=5.3.8" rel="stylesheet">

- <script src="../js/bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
+ <script src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js?v=5.3.8"></script>
```

### 2.4 Tests Phase 2

**Tests critiques après migration**:

1. **Live scores** (13 fichiers):
   ```
   ✓ Affichage scores en temps réel
   ✓ Animations (transitions)
   ✓ Responsive (mobile/desktop)
   ✓ Thèmes (si utilisés)
   ```

2. **Scoreboard**:
   ```
   ✓ Tableau de scores fonctionnel
   ✓ Interactions utilisateur (clics, hover)
   ✓ Layout responsive
   ```

**Régression attendue**: ❌ AUCUNE (compatibilité forte)

---

## Phase 3: Migration Bootstrap 3.x → 5.3.8

### ⚠️ ATTENTION: Migration complexe

Bootstrap 3 → 5 contient des **breaking changes majeurs**:
- Suppression dépendance jQuery
- Grille CSS complètement refaite (flexbox → CSS Grid)
- Composants JS réécrits
- Classes CSS renommées

### 3.1 Fichiers concernés (9 fichiers)

**Templates Smarty (backend admin)**:
```
1. pagelogin.tpl          → Page login (CRITIQUE)
2. fppage.tpl             → Page générique
3. kppageleaflet.tpl      → Page avec cartes Leaflet
4. frame_page.tpl         → Frame générique
5. kppage.tpl             → Page standard (CRITIQUE)
6. kppagewide.tpl         → Page large
7. tv.php                 → Page TV (affichage live)
```

### 3.2 Breaking changes Bootstrap 3 → 5

#### 3.2.1 jQuery → Vanilla JS

**Problème**: Bootstrap 5 ne dépend plus de jQuery

**Impact**:
```javascript
// Bootstrap 3 (avec jQuery)
$('#myModal').modal('show');
$('[data-toggle="tooltip"]').tooltip();

// Bootstrap 5 (Vanilla JS)
const myModal = new bootstrap.Modal(document.getElementById('myModal'));
myModal.show();

const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl)
});
```

**Action**: Vérifier si le code utilise jQuery pour contrôler Bootstrap

#### 3.2.2 Classes CSS renommées

**Grid System**:
```css
/* Bootstrap 3 */
.col-xs-*   →  Supprimé (mobile-first par défaut)
.col-sm-*   →  .col-sm-*  (inchangé)
.col-md-*   →  .col-md-*  (inchangé)
.col-lg-*   →  .col-lg-*  (inchangé)
.col-*-offset-*  →  .offset-*

/* Bootstrap 5 */
.col-xxl-*  (nouveau breakpoint)
```

**Utilities**:
```css
/* Bootstrap 3 → Bootstrap 5 */
.hidden-*         →  .d-none .d-*-block
.visible-*        →  .d-*-block
.pull-left        →  .float-start
.pull-right       →  .float-end
.text-left        →  .text-start
.text-right       →  .text-end
.center-block     →  .mx-auto
.label            →  .badge
.well             →  .card / .alert
.panel            →  .card
.panel-heading    →  .card-header
.panel-body       →  .card-body
.panel-footer     →  .card-footer
.thumbnail        →  .card
.navbar-right     →  .ms-auto
.navbar-left      →  (supprimé, utiliser flexbox)
```

**Forms**:
```css
/* Bootstrap 3 → Bootstrap 5 */
.form-group       →  .mb-3 (margin-bottom)
.control-label    →  .form-label
.input-group-addon  →  .input-group-text
.input-group-btn  →  (structure modifiée)
.form-horizontal  →  (utiliser grid classes)
.help-block       →  .form-text
.has-error        →  .is-invalid
.has-success      →  .is-valid
.has-warning      →  .is-invalid (pas d'équivalent warning)
```

**Components**:
```css
/* Bootstrap 3 → Bootstrap 5 */
.btn-default      →  .btn-secondary
.btn-xs           →  .btn-sm (xs supprimé)
.alert-dismissable  →  .alert-dismissible
.modal-sm / modal-lg  →  (inchangé)
.close            →  .btn-close
.glyphicon-*      →  ❌ Supprimé (utiliser icons externes)
```

#### 3.2.3 Attributs data-* renommés

```html
<!-- Bootstrap 3 -->
data-toggle="modal"
data-target="#myModal"
data-dismiss="modal"

<!-- Bootstrap 5 -->
data-bs-toggle="modal"
data-bs-target="#myModal"
data-bs-dismiss="modal"
```

**Tous les attributs `data-*` deviennent `data-bs-*`**

#### 3.2.4 Composants supprimés

**Glyphicons**: ❌ Supprimés
- **Remplacement**: Font Awesome, Bootstrap Icons, ou autre icon font

**Affix**: ❌ Supprimé
- **Remplacement**: Utiliser `position: sticky` CSS

**Wells & Panels**: ❌ Supprimés
- **Remplacement**: `.card` component

### 3.3 Plan d'action Phase 3 (PRUDENT)

#### Étape 3.1: Audit détaillé pré-migration

**Fichier par fichier**:

```bash
# Pour chaque template Smarty
1. Lister toutes les classes Bootstrap 3 utilisées
2. Identifier code jQuery dépendant de Bootstrap
3. Vérifier utilisation Glyphicons
4. Documenter layout complexes
```

**Script d'aide** (`audit_bootstrap3.sh`):

```bash
#!/bin/bash
# Rechercher classes Bootstrap 3 courantes
for file in sources/smarty/templates/*.tpl; do
    echo "=== $file ==="
    grep -o "\(col-xs\|pull-left\|pull-right\|glyphicon\|panel\|well\|hidden-\|visible-\)" "$file" | sort | uniq -c
done
```

#### Étape 3.2: Migration template par template

**Ordre recommandé** (du moins critique au plus critique):

1. ✅ `tv.php` (affichage simple)
2. ✅ `fppage.tpl` (page générique)
3. ✅ `frame_page.tpl` (frame)
4. ✅ `kppagewide.tpl` (page large)
5. ✅ `kppageleaflet.tpl` (avec Leaflet)
6. ⚠️ `kppage.tpl` (page standard - **CRITIQUE**)
7. ⚠️ `pagelogin.tpl` (login - **TRÈS CRITIQUE**)

**Stratégie par fichier**:

```
1. Créer backup (.bak)
2. Remplacer chemins Bootstrap 3 → 5.3.8
3. Remplacer classes CSS (regex)
4. Mettre à jour data-* → data-bs-*
5. Vérifier code jQuery
6. Tester visuellement
7. Tester fonctionnellement
```

#### Étape 3.3: Migration automatisée (partielle)

**Script de remplacement** (`migrate_bs3_to_bs5.php`):

```php
<?php
/**
 * Script de migration automatique Bootstrap 3 → 5
 * ATTENTION: Vérifier manuellement après exécution
 */

$replacements = [
    // Classes CSS
    '/\bpull-left\b/' => 'float-start',
    '/\bpull-right\b/' => 'float-end',
    '/\btext-left\b/' => 'text-start',
    '/\btext-right\b/' => 'text-end',
    '/\bhidden-xs\b/' => 'd-none',
    '/\bhidden-sm\b/' => 'd-sm-none',
    '/\bhidden-md\b/' => 'd-md-none',
    '/\bhidden-lg\b/' => 'd-lg-none',
    '/\blabel\s/' => 'badge ',
    '/\bpanel\b/' => 'card',
    '/\bpanel-heading\b/' => 'card-header',
    '/\bpanel-body\b/' => 'card-body',
    '/\bpanel-footer\b/' => 'card-footer',
    '/\bbtn-default\b/' => 'btn-secondary',
    '/\bbtn-xs\b/' => 'btn-sm',
    '/\bform-group\b/' => 'mb-3',
    '/\bcontrol-label\b/' => 'form-label',
    '/\bhelp-block\b/' => 'form-text',
    '/\bhas-error\b/' => 'is-invalid',
    '/\bhas-success\b/' => 'is-valid',

    // Attributs data-*
    '/data-toggle=/' => 'data-bs-toggle=',
    '/data-target=/' => 'data-bs-target=',
    '/data-dismiss=/' => 'data-bs-dismiss=',
    '/data-placement=/' => 'data-bs-placement=',
    '/data-content=/' => 'data-bs-content=',

    // Chemins fichiers
    '/js\/bootstrap\/js\/bootstrap\.min\.js/' => 'vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js',
    '/js\/bootstrap\/css\/bootstrap\.min\.css/' => 'vendor/twbs/bootstrap/dist/css/bootstrap.min.css',
];

function migrateFile($filepath, $replacements, $dryRun = true)
{
    $content = file_get_contents($filepath);
    $originalContent = $content;

    foreach ($replacements as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }

    if ($content !== $originalContent) {
        if (!$dryRun) {
            // Backup
            copy($filepath, $filepath . '.bs3.bak');
            file_put_contents($filepath, $content);
        }
        return true; // Modified
    }

    return false; // No changes
}

// Usage
$files = [
    'sources/smarty/templates/tv.php',
    'sources/smarty/templates/fppage.tpl',
    // ... autres fichiers
];

foreach ($files as $file) {
    $changed = migrateFile($file, $replacements, $dryRun = true);
    echo ($changed ? '✓' : '○') . " $file\n";
}
```

#### Étape 3.4: Gestion des Glyphicons

**Problème**: Glyphicons supprimés dans Bootstrap 5

**Solutions**:

**Option A: Bootstrap Icons** (recommandé)
```bash
# Installation
composer require twbs/bootstrap-icons

# Utilisation
<i class="bi bi-check"></i>
```

**Option B: Font Awesome**
```bash
# Déjà dans le projet ?
grep -r "font-awesome" sources/
```

**Option C: Remplacement inline SVG**
```html
<!-- Au lieu de -->
<span class="glyphicon glyphicon-search"></span>

<!-- Utiliser -->
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search">
  <path d="..."/>
</svg>
```

#### Étape 3.5: Tests Phase 3

**Tests critiques** (après chaque migration):

1. **Page login** (`pagelogin.tpl`):
   ```
   ✓ Affichage formulaire login
   ✓ Validation client-side
   ✓ Submit formulaire
   ✓ Messages d'erreur
   ✓ Responsive mobile/desktop
   ```

2. **Pages admin** (`kppage.tpl`, `kppagewide.tpl`):
   ```
   ✓ Navigation (menu, breadcrumb)
   ✓ Modals (ouverture, fermeture)
   ✓ Dropdowns
   ✓ Tooltips / Popovers
   ✓ Tables (tri, pagination)
   ✓ Formulaires (validation)
   ✓ Alerts / Notifications
   ```

3. **Compatibilité navigateurs**:
   ```
   ✓ Chrome/Edge (moderne)
   ✓ Firefox
   ✓ Safari
   ✓ Mobile (iOS/Android)
   ```

### 3.6 Rollback plan

**En cas de problème critique**:

```bash
# 1. Restaurer backups
for file in sources/smarty/templates/*.bs3.bak; do
    cp "$file" "${file%.bs3.bak}"
done

# 2. Revenir aux anciens chemins Bootstrap
# (Les fichiers Bootstrap 3 ne seront pas supprimés avant validation complète)

# 3. Vider cache Smarty
rm -rf sources/smarty/templates_c/*
```

---

## Tests et validation

### Tests automatisés (optionnel)

**Script de test visuel** (`test_bootstrap_pages.sh`):

```bash
#!/bin/bash
# Test d'accès à toutes les pages migrées

PAGES=(
    "pagelogin.php"
    "kppage.php"
    # ... autres
)

for page in "${PAGES[@]}"; do
    echo "Testing $page..."
    curl -s "https://kpi.localhost/admin/$page" | grep -q "bootstrap" && echo "✓ OK" || echo "✗ FAIL"
done
```

### Checklist validation

**Avant de supprimer anciennes versions**:

- [ ] Tous les fichiers migrés (24 fichiers au total)
- [ ] Tests manuels effectués sur chaque page
- [ ] Aucune régression visuelle
- [ ] Composants JS fonctionnels (modals, dropdowns, etc.)
- [ ] Responsive testé (mobile + desktop)
- [ ] Compatibilité navigateurs validée
- [ ] Performance acceptable (temps de chargement)
- [ ] Cache Smarty régénéré
- [ ] Documentation mise à jour

---

## Breaking changes majeurs

### Résumé Bootstrap 3 → 5

| Catégorie | Bootstrap 3 | Bootstrap 5 | Migration |
|-----------|-------------|-------------|-----------|
| **jQuery** | ✅ Requis | ❌ Pas de dépendance | Réécrire code JS |
| **Grid** | Float-based | Flexbox | Classes identiques |
| **Icons** | Glyphicons inclus | ❌ Supprimés | Ajouter icon library |
| **Data attrs** | `data-toggle` | `data-bs-toggle` | Regex replace |
| **Classes** | `.pull-*`, `.text-left` | `.float-*`, `.text-start` | Regex replace |
| **Components** | Panels, Wells | Cards | Restructurer HTML |
| **Buttons** | `.btn-default` | `.btn-secondary` | Rename |
| **Forms** | `.form-group` | `.mb-3` | Restructurer |
| **IE support** | IE 8+ | IE 11+ (v5.3: aucun) | ⚠️ Vérifier stats |

---

## Checklist migration

### Phase 1: Installation

- [ ] Installer Bootstrap 5.3.8 via Composer
- [ ] Vérifier installation (`sources/vendor/twbs/bootstrap/`)
- [ ] Créer BootstrapHelper.php (optionnel)
- [ ] Tester chargement CSS/JS basique

### Phase 2: Migration Bootstrap 5.x

- [ ] **Groupe A (5.1.3 → 5.3.8)** - 13 fichiers live/
  - [ ] score_e.php
  - [ ] tv2.php
  - [ ] next_game_club.php
  - [ ] teams_club.php
  - [ ] teams.php
  - [ ] next_game.php
  - [ ] score_club_e.php
  - [ ] score_o.php
  - [ ] score.php
  - [ ] score_s.php
  - [ ] score_club.php
  - [ ] score_club_s.php
  - [ ] score_club_o.php
- [ ] **Groupe B (5.0.2 → 5.3.8)** - 2 fichiers admin/
  - [ ] scoreboard.php
  - [ ] shotclock.php (vérifier si utilisé)
- [ ] Tests Groupe A (live scores)
- [ ] Tests Groupe B (scoreboards)

### Phase 3: Migration Bootstrap 3.x

- [ ] Audit pré-migration (classes, jQuery, icons)
- [ ] Installer Bootstrap Icons (si nécessaire)
- [ ] **Migration templates** - 9 fichiers
  - [ ] tv.php
  - [ ] fppage.tpl
  - [ ] frame_page.tpl
  - [ ] kppagewide.tpl
  - [ ] kppageleaflet.tpl
  - [ ] kppage.tpl ⚠️ CRITIQUE
  - [ ] pagelogin.tpl ⚠️ TRÈS CRITIQUE
- [ ] Tests fonctionnels complets
- [ ] Validation navigateurs
- [ ] Tests responsive

### Nettoyage final

- [ ] Supprimer `sources/js/bootstrap/` (Bootstrap 3.4.1)
- [ ] Supprimer `sources/js/bootstrap-3.3.1/` (Bootstrap 3.3.0)
- [ ] Supprimer `sources/js/bootstrap-5.0.2-dist/` (Bootstrap 5.0.2)
- [ ] Supprimer `sources/lib/bootstrap-5.1.3-dist/` (Bootstrap 5.1.3)
- [ ] Supprimer backups .bs3.bak (après validation)
- [ ] Mettre à jour documentation
- [ ] Commit git

---

## Ressources

### Documentation officielle

- **Bootstrap 5.3**: https://getbootstrap.com/docs/5.3/getting-started/introduction/
- **Migration 3→5**: https://getbootstrap.com/docs/5.3/migration/
- **Changelog 5.1→5.3**: https://getbootstrap.com/docs/5.3/migration/#v513
- **Bootstrap Icons**: https://icons.getbootstrap.com/

### Outils migration

- **Bootlint**: https://github.com/twbs/bootlint (linter Bootstrap)
- **Bootstrap 3 to 4 migration tool**: Adaptable pour 3→5

### Support navigateurs Bootstrap 5.3

- Chrome >= 60
- Firefox >= 60
- Safari >= 12
- Edge >= 79
- Opera >= 47
- ❌ IE 11 non supporté

---

## Estimation effort

| Phase | Fichiers | Complexité | Temps estimé | Risque |
|-------|----------|------------|--------------|--------|
| **Phase 1** | Installation | ⭐ Facile | 30 min | ✅ Faible |
| **Phase 2** | 15 fichiers | ⭐⭐ Moyenne | 2-3 heures | ✅ Faible |
| **Phase 3** | 9 fichiers | ⭐⭐⭐⭐ Complexe | 1-2 jours | ⚠️ Moyen-Élevé |
| **Tests** | Tous | ⭐⭐⭐ Importante | 1 jour | - |
| **TOTAL** | 24 fichiers | - | **2-3 jours** | ⚠️ Moyen |

**Recommandation**: Planifier 1 semaine complète (inclut tests, corrections, documentation)

---

## Notes importantes

### ⚠️ Avant de commencer

1. **Backup complet** de la base de données et du code
2. **Tests en preprod** obligatoires avant production
3. **Informer les utilisateurs** (maintenance prévue)
4. **Rollback plan** prêt en cas de problème critique

### ✅ Bonnes pratiques

1. **Ne pas supprimer** les anciennes versions avant validation complète
2. **Migrer progressivement** (Phase 2 avant Phase 3)
3. **Tester après chaque fichier** migré
4. **Documenter** les problèmes rencontrés
5. **Commits Git réguliers** avec messages clairs

### 🔍 Points de vigilance

1. **jQuery**: Vérifier si code custom dépend de Bootstrap 3 + jQuery
2. **Glyphicons**: Prévoir remplacement (Bootstrap Icons recommandé)
3. **Layouts complexes**: Panels/Wells à convertir en Cards
4. **IE 11**: Bootstrap 5.3 ne supporte plus (vérifier analytics)
5. **Performance**: Charger `bootstrap.bundle.min.js` (inclut Popper)

---

**Auteur**: Claude Code / Laurent Garrigue
**Date**: 29 octobre 2025
**Version**: 1.0 - Plan initial
**Statut**: 📋 PLANIFIÉ - Prêt pour exécution
