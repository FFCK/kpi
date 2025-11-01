# Guide de Migration dhtmlgoodies_calendar → Flatpickr

**Date**: 1er novembre 2025
**Objectif**: Remplacer dhtmlgoodies_calendar (2006, obsolète) par Flatpickr (moderne, maintenue)
**Durée estimée**: 1-2 heures
**Complexité**: 🟢 Faible (migration transparente)

---

## 📊 Contexte

### Situation Actuelle

- **Bibliothèque**: dhtmlgoodies_calendar (2006, non maintenue depuis 19 ans)
- **Usage**: 17 appels `displayCalendar()` dans 10 templates Smarty
- **Pages impactées**: GestionCompetition, GestionAthlete, GestionJournee, GestionUtilisateur, etc.
- **Taille**: ~50 KB (JS + CSS)

### Objectif

- **Nouvelle bibliothèque**: Flatpickr 4.6.13 (janvier 2024)
- **Avantages**:
  - ✅ Migration transparente (wrapper function)
  - ✅ Aucun changement dans les 10 templates
  - ✅ Format français conservé (`dd/mm/yyyy`)
  - ✅ Moderne, maintenue activement
  - ✅ Plus légère (16 KB vs 50 KB)
  - ✅ Accessible WCAG 2.1, optimisée mobile

---

## 🎯 Plan de Migration

### Étape 1 : Installation de Flatpickr

**Option A : Via npm (RECOMMANDÉ)**

```bash
# Installation via container Node.js temporaire
make npm_add_backend package=flatpickr

# Vérifier l'installation
ls -l sources/node_modules/flatpickr/dist/
```

**Fichiers disponibles après installation**:
```
sources/node_modules/flatpickr/
├── dist/
│   ├── flatpickr.min.js      # 13 KB gzipped
│   ├── flatpickr.min.css     # 3 KB gzipped
│   ├── flatpickr.min.js.map
│   └── l10n/
│       └── fr.js             # Localisation française
```

**Option B : Via CDN (alternative)**

Utiliser directement les liens CDN dans les templates (voir Étape 3).

---

### Étape 2 : Copier les Fichiers dans sources/lib/

**Si installation npm (Option A)**:

```bash
# Créer le répertoire Flatpickr
mkdir -p sources/lib/flatpickr-4.6.13

# Copier les fichiers nécessaires
cp sources/node_modules/flatpickr/dist/flatpickr.min.js sources/lib/flatpickr-4.6.13/
cp sources/node_modules/flatpickr/dist/flatpickr.min.css sources/lib/flatpickr-4.6.13/
cp sources/node_modules/flatpickr/dist/l10n/fr.js sources/lib/flatpickr-4.6.13/flatpickr-fr.js

# Vérifier
ls -lh sources/lib/flatpickr-4.6.13/
```

**Structure attendue**:
```
sources/lib/flatpickr-4.6.13/
├── flatpickr.min.js
├── flatpickr.min.css
└── flatpickr-fr.js
```

---

### Étape 3 : Créer le Wrapper Function

**Créer le fichier** `sources/js/flatpickr-wrapper.js` :

```javascript
/**
 * Wrapper function pour compatibilité dhtmlgoodies_calendar → Flatpickr
 *
 * Usage (identique à dhtmlgoodies):
 *   displayCalendar(inputField, 'dd/mm/yyyy', this)
 *
 * @param {HTMLInputElement} inputField - Champ input à transformer en datepicker
 * @param {string} formatString - Format de date ('dd/mm/yyyy' ou 'yyyy-mm-dd')
 * @param {object} context - Contexte d'appel (généralement 'this')
 */
function displayCalendar(inputField, formatString, context) {
    // Convertir format dhtmlgoodies → flatpickr
    const flatpickrFormat = formatString
        .replace('dd', 'd')      // dd → d
        .replace('mm', 'm')      // mm → m
        .replace('yyyy', 'Y');   // yyyy → Y

    // Détecter format ISO (anglais)
    const isISO = formatString === 'yyyy-mm-dd';

    // Initialiser Flatpickr sur le champ
    flatpickr(inputField, {
        dateFormat: isISO ? 'Y-m-d' : 'd/m/Y',
        locale: 'fr',               // Localisation française
        allowInput: true,           // Autoriser saisie manuelle
        altInput: false,            // Pas de champ alternatif
        disableMobile: false,       // UX mobile native
        clickOpens: true,           // Ouvrir au clic

        // Événements compatibles dhtmlgoodies
        onChange: function(selectedDates, dateStr, instance) {
            // Trigger onchange natif si défini
            if (inputField.onchange) {
                inputField.onchange();
            }

            // Trigger événement change natif
            const event = new Event('change', { bubbles: true });
            inputField.dispatchEvent(event);
        },

        onReady: function(selectedDates, dateStr, instance) {
            // Supprimer l'attribut onfocus pour éviter les boucles
            if (inputField.hasAttribute('onfocus')) {
                inputField.removeAttribute('onfocus');
            }
        }
    });
}

/**
 * Initialisation automatique des datepickers au chargement de la page
 * (optionnel - si vous voulez initialiser via classe CSS)
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser tous les inputs avec classe 'datepicker'
    const datepickers = document.querySelectorAll('input.datepicker');
    datepickers.forEach(function(input) {
        const format = input.getAttribute('data-date-format') || 'dd/mm/yyyy';
        displayCalendar(input, format, null);
    });
});
```

---

### Étape 4 : Modifier les Templates

#### 4.1. Modifier `sources/smarty/templates/page.tpl`

**Localiser les lignes** (environ lignes 28 et 46):

```smarty
{* AVANT *}
<link type="text/css" rel="stylesheet" href="css/dhtmlgoodies_calendar.css?random=20051112" media="screen" />
<script src="js/dhtmlgoodies_calendar.js?random=20060118"></script>
```

**Remplacer par** (Option A - fichiers locaux):

```smarty
{* Flatpickr - Datepicker moderne *}
<link rel="stylesheet" href="lib/flatpickr-4.6.13/flatpickr.min.css?v={$NUM_VERSION}">
<script src="lib/flatpickr-4.6.13/flatpickr.min.js?v={$NUM_VERSION}"></script>
<script src="lib/flatpickr-4.6.13/flatpickr-fr.js?v={$NUM_VERSION}"></script>
<script src="js/flatpickr-wrapper.js?v={$NUM_VERSION}"></script>
```

**Ou Option B** (CDN - recommandé pour tester):

```smarty
{* Flatpickr - Datepicker moderne (CDN) *}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/fr.js"></script>
<script src="js/flatpickr-wrapper.js?v={$NUM_VERSION}"></script>
```

**⚠️ Important**: Répéter cette modification pour les **deux sections** dans `page.tpl` :
- Ligne ~28 (section 1)
- Ligne ~46 (section 2)

#### 4.2. Vérifier les autres templates

Les templates suivants chargent déjà Flatpickr via `page.tpl` (héritage), **aucune modification nécessaire** :

- ✅ `page_jq.tpl` - dhtmlgoodies déjà commentée
- ✅ `pageMap.tpl` - charge seulement le CSS (peut être supprimé)

---

### Étape 5 : Tester la Migration

#### 5.1. Vider les caches Smarty

```bash
# Supprimer les templates compilés
rm -rf sources/smarty/templates_c/*

# Redémarrer PHP (si nécessaire)
make dev_restart
```

#### 5.2. Tester les 10 Pages Admin

**Liste des pages à tester** :

1. **GestionUtilisateur.php** (2 champs)
   - Date début / Date fin utilisateur

2. **GestionCompetition.php** (6 champs)
   - Date début/fin compétition
   - Dates saison nationale (début/fin)
   - Dates saison internationale (début/fin)

3. **GestionJournee.php** (1 champ)
   - Date match

4. **GestionEquipeJoueur.php** (1 champ)
   - Date naissance joueur

5. **GestionParamJournee.php** (2 champs)
   - Date début/fin journée

6. **GestionEvenement.php** (2 champs)
   - Date début/fin événement

7. **GestionAthlete.php** (1 champ)
   - Date naissance athlète

8. **GestionCopieCompetition.php** (2 champs)
   - Date début/fin copie compétition

#### 5.3. Checklist de Tests

Pour chaque page :

- [ ] Le datepicker s'ouvre au focus/clic
- [ ] L'interface est en français (mois, jours)
- [ ] Le format est `dd/mm/yyyy` (ex: 01/11/2025)
- [ ] La saisie manuelle fonctionne
- [ ] La sélection d'une date remplit le champ
- [ ] Le formulaire se soumet correctement
- [ ] Aucune erreur dans la console JavaScript (F12)
- [ ] UX mobile correcte (si applicable)

#### 5.4. Vérifications Console JavaScript

Ouvrir la console (F12) et vérifier :

```javascript
// Aucune erreur "displayCalendar is not defined"
// Aucune erreur "flatpickr is not defined"

// Vérifier que flatpickr est chargé
console.log(typeof flatpickr); // "function"

// Vérifier locale française
console.log(flatpickr.l10ns.fr); // Object { weekdays: {…}, months: {…}, … }
```

---

## 🔄 Rollback (en cas de problème)

### Option 1 : Rollback Git

```bash
# Annuler les modifications
git checkout sources/smarty/templates/page.tpl
git checkout sources/js/flatpickr-wrapper.js

# Vider cache Smarty
rm -rf sources/smarty/templates_c/*
```

### Option 2 : Rollback Manuel

**Restaurer dhtmlgoodies dans `page.tpl`** :

```smarty
{* RESTAURER *}
<link type="text/css" rel="stylesheet" href="css/dhtmlgoodies_calendar.css?random=20051112" media="screen" />
<script src="js/dhtmlgoodies_calendar.js?random=20060118"></script>
```

**Commenter Flatpickr** :

```smarty
{* TEMPORAIREMENT DÉSACTIVÉ
<link rel="stylesheet" href="lib/flatpickr-4.6.13/flatpickr.min.css">
<script src="lib/flatpickr-4.6.13/flatpickr.min.js"></script>
<script src="lib/flatpickr-4.6.13/flatpickr-fr.js"></script>
<script src="js/flatpickr-wrapper.js"></script>
*}
```

---

## ✅ Finalisation

### Après 48h de Tests en Production

Si aucun problème détecté :

1. **Supprimer dhtmlgoodies_calendar**

```bash
# Supprimer les fichiers obsolètes
rm sources/js/dhtmlgoodies_calendar.js
rm sources/css/dhtmlgoodies_calendar.css

# Vérifier aucune référence restante
grep -r "dhtmlgoodies" sources/smarty/templates/ sources/js/ sources/css/
```

2. **Nettoyer node_modules (si npm utilisé)**

```bash
# Optionnel : supprimer node_modules après copie dans lib/
make npm_clean_backend
```

3. **Commit final**

```bash
git add sources/lib/flatpickr-4.6.13/
git add sources/js/flatpickr-wrapper.js
git add sources/smarty/templates/page.tpl
git rm sources/js/dhtmlgoodies_calendar.js
git rm sources/css/dhtmlgoodies_calendar.css

git commit -m "feat: migrate dhtmlgoodies_calendar to Flatpickr 4.6.13

- Replace dhtmlgoodies_calendar (2006, unmaintained) with Flatpickr
- Create wrapper function for backward compatibility
- Zero template changes required (17 calls preserved)
- Gain: -34 KB, modern library, WCAG 2.1 accessible
- Tested on 10 admin pages (GestionCompetition, GestionAthlete, etc.)

🤖 Generated with Claude Code
Co-Authored-By: Claude <noreply@anthropic.com>"
```

4. **Mettre à jour la documentation**

```bash
# Mettre à jour JS_LIBRARIES_AUDIT.md
# Marquer dhtmlgoodies_calendar comme "REMPLACÉE par Flatpickr"
```

---

## 📋 Résumé des Gains

| Critère | dhtmlgoodies (avant) | Flatpickr (après) | Gain |
|---------|---------------------|-------------------|------|
| **Taille** | 50 KB | 16 KB | **-68%** (-34 KB) |
| **Maintenance** | ❌ Abandonnée (2006) | ✅ Active (2024) | Sécurité |
| **Accessibilité** | ❌ Limitée | ✅ WCAG 2.1 | UX |
| **Mobile** | ❌ Desktop only | ✅ Optimisé | UX Mobile |
| **Changements templates** | - | ✅ Aucun (wrapper) | Effort 0 |
| **Changements PHP** | - | ✅ Aucun (formats conservés) | Effort 0 |

---

## 🆘 Problèmes Courants

### Problème 1 : Datepicker ne s'ouvre pas

**Symptôme** : Clic sur input ne déclenche rien

**Solutions** :
1. Vérifier console JavaScript (F12) : erreur `flatpickr is not defined` ?
2. Vérifier ordre de chargement scripts dans `page.tpl` :
   - Flatpickr AVANT flatpickr-wrapper.js
3. Vérifier cache Smarty vidé : `rm -rf sources/smarty/templates_c/*`

### Problème 2 : Format de date incorrect

**Symptôme** : Affichage en format anglais (mm/dd/yyyy)

**Solutions** :
1. Vérifier locale chargée : `flatpickr-fr.js` ou `/l10n/fr.js`
2. Vérifier wrapper : `locale: 'fr'` dans options Flatpickr
3. Console : `flatpickr.l10ns.default` doit être `fr`

### Problème 3 : Formulaire ne se soumet pas

**Symptôme** : Validation échoue, champ vide

**Solutions** :
1. Vérifier `allowInput: true` dans wrapper
2. Vérifier événement `change` déclenché : voir wrapper `onChange()`
3. Tester saisie manuelle : `01/11/2025` doit être accepté

### Problème 4 : Boucle infinie (datepicker s'ouvre en boucle)

**Symptôme** : Datepicker se réouvre constamment

**Solutions** :
1. Vérifier wrapper supprime `onfocus` : voir `onReady()` dans wrapper
2. Vérifier pas de double initialisation (classe + onfocus)

---

## 📚 Ressources

- **Flatpickr Documentation** : https://flatpickr.js.org/
- **Options complètes** : https://flatpickr.js.org/options/
- **Exemples** : https://flatpickr.js.org/examples/
- **Localisation** : https://flatpickr.js.org/localization/

---

**Auteur** : Laurent Garrigue / Claude Code
**Date** : 1er novembre 2025
**Version** : 1.0
**Statut** : ✅ **PRÊT POUR MIGRATION**
