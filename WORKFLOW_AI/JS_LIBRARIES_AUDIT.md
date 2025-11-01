# Audit des Bibliothèques JavaScript - Projet KPI

**Date**: 31 octobre 2025
**Objectif**: Identifier bibliothèques obsolètes, redondantes, à mettre à jour
**Statut**: 📋 **AUDIT COMPLET**

---

## 📊 Vue d'Ensemble

### Résumé Exécutif

| Catégorie | Nombre | Statut |
|-----------|--------|--------|
| **jQuery (versions)** | 6 versions | 🔴 Redondance critique |
| **jQuery UI (versions)** | 3 versions | 🔴 Redondance élevée |
| **Plugins jQuery** | 10+ plugins | 🟡 À moderniser |
| **Bibliothèques cartographie** | 2 (Leaflet + OSM) | ✅ OK |
| **Bibliothèques utilitaires** | 6 | 🟡 Mix OK/obsolète |
| **Bibliothèques à supprimer** | 2 (Ably, dhtmlgoodies) | 🔴 Action requise |

**Total estimé** : **35+ fichiers JavaScript** (hors node_modules)

---

## 🔴 Problèmes Critiques Identifiés

### 1. Redondance jQuery (6 versions !)

**Versions détectées** :
- ❌ **jquery.js** - v1.3.2 (2009) - EOL
- ❌ **jquery-1.5.2.min.js** - v1.5.2 (2011) - EOL
- ❌ **jquery-1.11.0.min.js** - v1.11.0 (2014) - EOL
- ❌ **jquery-1.11.2.min.js** - v1.11.2 (2014) - EOL
- ⚠️ **jquery-3.5.1.min.js** - v3.5.1 (2020) - Sécurité OK mais obsolète

**Version moderne recommandée** : **jQuery 3.7.1** (2023, dernière version stable)

**Problèmes** :
- Multiples versions chargées = conflits potentiels
- Versions 1.x **très vulnérables** (XSS, failles de sécurité)
- Taille cumulée importante (~300 KB)
- Code legacy incompatible versions 1.x → 3.x

**Impact Sécurité** : 🔴 **CRITIQUE**
- jQuery 1.3.2 : **50+ CVE** (failles de sécurité)
- jQuery 1.5.2 : **40+ CVE**
- jQuery 1.11.x : **10+ CVE**

---

### 2. Redondance jQuery UI (3 versions)

**Versions détectées** :
- ❌ **jquery-ui-1.10.4.custom.min.js** - v1.10.4 (2014) - EOL
- ❌ **jquery-ui-1.11.4.min.js** + .js - v1.11.4 (2015) - EOL
- ⚠️ **jquery-ui-1.12.1.min.js** - v1.12.1 (2016) - Dernière version stable

**Version moderne recommandée** : **jQuery UI 1.13.2** (2022)

**Problèmes** :
- 3 versions = confusion, bugs
- Incompatibilités entre versions
- Taille cumulée : ~250 KB

**Impact** : 🟡 **MOYEN** (moins critique que jQuery core)

---

### 3. Bibliothèques Obsolètes/Inutilisées

#### ❌ Ably (WebSocket/Messaging)
**Fichiers** :
- `sources/live/event_ably.php` (charge CDN Ably)
- `sources/js/ably.js` (référence dans event_ably.php)
- `sources/live/js/event_ably.js` (référence dans event_ably.php)

**Statut** : 🔴 **Test sans suite, à supprimer**

**Action** :
- Supprimer `event_ably.php`
- Supprimer `js/ably.js` (si existe)
- Supprimer `live/js/event_ably.js` (si existe)
- Utiliser WebSocket natif ou autre solution

---

#### ❌ dhtmlgoodies_calendar.js
**Fichier** : `sources/js/dhtmlgoodies_calendar.js`

**Statut** : 🔴 **Obsolète** (2006-2010)

**Problèmes** :
- Librairie très ancienne (pré-HTML5)
- Pas de maintenance depuis 10+ ans
- Alternative moderne : HTML5 `<input type="date">` ou Flatpickr

**Alternative recommandée** :
- **HTML5 native** : `<input type="date">` (zéro dépendance)
- **Flatpickr** : Moderne, léger (15 KB), accessible
- **jQuery UI Datepicker** : Si jQuery UI conservé

**Action** : 🔴 **SUPPRIMER** et migrer vers HTML5 ou Flatpickr

---

## 🟡 Bibliothèques à Moderniser

### 1. jQuery Plugins

#### jQuery DataTables
**Fichiers** :
- `jquery.dataTables.min.js` (version inconnue)
- `jquery.dataTables-1.10.21.min.js` (2020)

**Statut** : 🟡 **À mettre à jour**

**Version actuelle** : 1.10.21 (2020)
**Version moderne** : **DataTables 2.1.8** (2024)

**Gestion via npm recommandée** :
```bash
npm install datatables.net
npm install datatables.net-bs5  # Bootstrap 5 support
```

**Migration** :
- DataTables 2.x **non rétrocompatible** avec 1.x
- Breaking changes API
- Meilleure performance, support Bootstrap 5

**Action** : 🟡 **PLANIFIER** migration v2 (2-3 jours)

---

#### jQuery File Upload
**Fichiers** :
- `jquery.fileupload.js`
- `jquery.fileupload-process.js`
- `jquery.fileupload-validate.js`
- `jquery.fileupload-image.js`
- `jquery.fileupload-ui.js`

**Statut** : 🟡 **Fonctionnel mais obsolète**

**Alternative moderne** :
- **Uppy** : Moderne, modulaire, extensible (Dropbox, Google Drive)
- **FilePond** : Léger, élégant, drag&drop
- **HTML5 Drag&Drop API** : Natif (zéro dépendance)

**Action** : 🟢 **CONSERVER** court terme, planifier migration long terme

---

#### jQuery Autocomplete
**Fichiers** :
- `jquery.autocomplete.js`
- `jquery.autocomplete.min.js`

**Statut** : 🟡 **Fonctionnel mais obsolète**

**Alternatives** :
- **jQuery UI Autocomplete** : Si jQuery UI conservé
- **Autocomplete.js** : Vanille JS, moderne
- **HTML5 datalist** : `<datalist>` natif (simple)

**Action** : 🟢 **CONSERVER** court terme

---

#### jQuery Jeditable (Inline Editing)
**Fichier** : `jquery.jeditable.min.js`

**Statut** : 🟡 **Obsolète** (2012, pas maintenu)

**Alternative moderne** :
- **X-editable** : Bootstrap 5 support
- **Jeditable fork moderne** (si existe)
- **ContentEditable API** : Natif HTML5

**Action** : 🟡 **ÉVALUER** remplacement (peu critique)

---

#### jQuery Masked Input
**Fichier** : `jquery.maskedinput.min.js`

**Statut** : 🟡 **Obsolète** (2014)

**Alternative moderne** :
- **IMask.js** : Moderne, performant, vanille JS
- **Inputmask** : Robuste, populaire
- **Cleave.js** : Formatage intelligent

**Action** : 🟡 **ÉVALUER** remplacement

---

#### jQuery Sticky Table Headers
**Fichier** : `jquery.stickytableheaders.min.js`

**Statut** : 🟡 **Fonctionnel mais remplaçable**

**Alternative moderne** :
- **CSS `position: sticky`** : Natif CSS (zéro JS)
- **DataTables FixedHeader** : Si DataTables utilisé

**Action** : 🟢 **MIGRER** vers CSS natif (simple)

---

#### jQuery Tooltip
**Fichier** : `jquery.tooltip.min.js`

**Statut** : 🟡 **Obsolète**

**Alternative moderne** :
- **Bootstrap 5 Tooltips** : Déjà disponible (zéro dépendance)
- **Tippy.js** : Moderne, performant
- **Popper.js** : Base de Bootstrap tooltips

**Action** : ✅ **MIGRER** vers Bootstrap 5 tooltips (déjà inclus)

---

#### jQuery FixedHeaderTable
**Fichier** : `jquery.fixedheadertable.min.js`

**Statut** : 🟡 **Redondant** avec Sticky Table Headers

**Action** : 🔴 **SUPPRIMER** (doublon) et utiliser `position: sticky`

---

### 2. FullPage.js

**Fichiers** :
- `js/fullPage/jquery.fullpage.js`
- `js/fullPage/jquery.fullpage.min.js`
- `js/fullPage/jquery.fullpage.extensions.min.js`

**Statut** : ✅ **OK** (si utilisé)

**Usage** : Sections plein écran (scroll animations)

**Action** : 🟢 **VÉRIFIER** usage réel, supprimer si inutilisé

---

## ✅ Bibliothèques OK (À Conserver)

### 1. Leaflet (Cartographie)

**Localisation** : `sources/js/leaflet/`

**Statut** : ✅ **OK**

**Usage** : Cartes interactives (kppageleaflet.tpl, etc.)

**Version actuelle** : À vérifier (probablement 1.7-1.9)
**Version moderne** : **Leaflet 1.9.4** (2023)

**Gestion via npm recommandée** :
```bash
npm install leaflet
```

**Action** : ✅ **VÉRIFIER** version, mettre à jour si < 1.9

---

### 2. OSM (OpenStreetMap)

**Localisation** : `sources/js/osm/`

**Statut** : ✅ **OK** (complément Leaflet)

**Usage** : Tiles OpenStreetMap pour Leaflet

**Action** : ✅ **CONSERVER**

---

### 3. Axios (HTTP Client)

**Localisation** : `sources/js/axios/`

**Statut** : ✅ **OK**

**Version détectée** : **0.24.0** (2021)
**Version moderne** : **Axios 1.7.9** (2024)

**Problèmes** :
- Version 0.24.0 a **3 CVE** (failles de sécurité)
- Axios 1.x **non rétrocompatible** avec 0.x

**Gestion via npm recommandée** :
```bash
npm install axios
```

**Action** : �� **METTRE À JOUR** vers 1.7.x (breaking changes)

---

### 4. Day.js (Date/Time)

**Localisation** : `sources/lib/dayjs-1.11.1/`

**Statut** : ✅ **OK**

**Version actuelle** : 1.11.1 (2022)
**Version moderne** : **Day.js 1.11.13** (2024)

**Gestion via npm recommandée** :
```bash
npm install dayjs
```

**Action** : 🟢 **METTRE À JOUR** vers 1.11.13 (rétrocompatible)

---

### 5. EasyTimer (Chronomètres)

**Localisation** : `sources/lib/easytimer-4.6.0/`

**Statut** : ✅ **OK**

**Version actuelle** : 4.6.0 (2022)
**Version moderne** : **EasyTimer 4.6.0** (dernière version stable)

**Usage** : Chronomètres FeuilleMarque3, shotclock

**Gestion via npm recommandée** :
```bash
npm install easytimer.js
```

**Action** : ✅ **OK** - Déjà à jour

---

### 6. QRCode

**Localisation** : `sources/lib/qrcode/`

**Statut** : ✅ **OK**

**Usage** : Génération QR codes (apps mobiles, PDFs)

**Gestion via npm recommandée** :
```bash
npm install qrcode
# ou
npm install qrcode-generator
```

**Action** : ✅ **VÉRIFIER** version, mise à jour possible

---

### 7. Moment.js

**Localisation** : `sources/js/moment.min.js`

**Statut** : ⚠️ **DEPRECATED** (maintenance mode)

**Problème** :
- Moment.js **officiellement déprécié** depuis 2020
- Projet recommande **Day.js** (déjà utilisé !) ou Luxon

**Migration** : 🟡 **REMPLACER** par Day.js (déjà présent)

**Action** : 🟡 **PLANIFIER** migration Moment → Day.js

---

### 8. FullCalendar

**Localisation** : `sources/js/fullcalendar.min.js`

**Statut** : 🟡 **Obsolète**

**Version détectée** : 2.3.1 (2015)
**Version moderne** : **FullCalendar 6.1** (2024)

**Problèmes** :
- Version très obsolète (9 ans)
- FullCalendar 6.x **complètement réécrit** (breaking changes majeurs)
- Licence : MIT (v2) vs Commerciale (v5+) pour certaines features

**Gestion via npm recommandée** :
```bash
npm install @fullcalendar/core
npm install @fullcalendar/daygrid
npm install @fullcalendar/timegrid
```

**Action** : 🟡 **ÉVALUER** migration v6 (effort important)

---

### 9. HTMLPurifier

**Localisation** : `sources/lib/htmlpurifier/`

**Statut** : ✅ **OK** (PHP, pas JS)

**Usage** : Sanitisation HTML (sécurité)

**Action** : ✅ **CONSERVER** (critique pour sécurité)

---

## 📋 Plan d'Action Recommandé

### Phase 1 : Nettoyage Immédiat (1 jour)

#### 🔴 Actions Critiques

1. **Supprimer Ably**
   ```bash
   rm -f sources/live/event_ably.php
   # Vérifier si js/ably.js existe et supprimer
   # Vérifier si live/js/event_ably.js existe et supprimer
   ```

2. **Supprimer dhtmlgoodies_calendar.js**
   ```bash
   rm -f sources/js/dhtmlgoodies_calendar.js
   # Migrer vers HTML5 <input type="date"> ou Flatpickr
   ```

3. **Supprimer jQuery FixedHeaderTable** (doublon)
   ```bash
   rm -f sources/js/jquery.fixedheadertable.min.js
   ```

4. **Consolider jQuery vers version unique**
   - Identifier pages utilisant jQuery 1.x
   - Tester migration vers jQuery 3.7.1
   - Supprimer versions obsolètes :
     ```bash
     # APRÈS MIGRATION VALIDÉE
     rm -f sources/js/jquery.js  # v1.3.2
     rm -f sources/js/jquery-1.5.2.min.js
     rm -f sources/js/jquery-1.11.0.min.js
     rm -f sources/js/jquery-1.11.2.min.js
     # Garder temporairement jquery-3.5.1.min.js puis migrer vers 3.7.1
     ```

**Gain** : -5 fichiers, -500 KB, sécurité améliorée

---

### Phase 2 : Mise à Jour Sécurité (1 semaine)

#### 🟡 Actions Importantes

1. **Mettre à jour Axios 0.24 → 1.7.9**
   - **Sécurité** : 3 CVE corrigées
   - **Breaking changes** : Vérifier API calls
   - Test sur container PHP 8

2. **Mettre à jour Day.js 1.11.1 → 1.11.13**
   - Rétrocompatible
   - Faible risque

3. **Consolider jQuery UI vers 1.13.2**
   - Supprimer versions 1.10.4 et 1.11.4
   - Tester composants (autocomplete, datepicker, dialog)

**Gain** : Sécurité, stabilité

---

### Phase 3 : Modernisation (2-4 semaines)

#### 🟢 Actions Planifiées

1. **Migrer Moment.js → Day.js**
   - Audit usages Moment.js
   - Remplacement progressif
   - Tests

2. **Migrer jQuery Tooltip → Bootstrap 5 Tooltips**
   - Bootstrap 5 déjà présent
   - Zéro dépendance additionnelle

3. **Migrer Sticky Headers → CSS `position: sticky`**
   - Code natif CSS
   - Suppression plugin jQuery

4. **Évaluer DataTables 1.10 → 2.1**
   - Tests compatibilité
   - Migration progressive (non urgent)

5. **Évaluer FullCalendar 2.3 → 6.1**
   - Gros effort (réecriture)
   - À planifier selon besoin

**Gain** : Performance, maintenabilité

---

### Phase 4 : Gestion Moderne (Optionnel)

#### Migration npm/Composer

**Bibliothèques à gérer via npm** (app2 uniquement) :
- ✅ Axios (déjà dans app2)
- ✅ Leaflet
- ✅ Day.js
- ✅ EasyTimer
- ✅ QRCode
- ✅ DataTables (si migration v2)
- ✅ FullCalendar (si migration v6)

**Bibliothèques à conserver statiques** (backend legacy) :
- jQuery 3.7.1 (unique version)
- jQuery UI 1.13.2 (si vraiment nécessaire)
- Plugins jQuery legacy (transition)

**Stratégie** :
1. **App2 (Nuxt)** : npm exclusivement
2. **Backend legacy** : Mix statique + CDN
3. **Migration progressive** : App2 remplace legacy

---

## 📊 Comparatif Versions

### jQuery

| Version | Date | Statut | CVE | Taille | Action |
|---------|------|--------|-----|--------|--------|
| 1.3.2 | 2009 | ❌ EOL | 50+ | 57 KB | 🔴 SUPPRIMER |
| 1.5.2 | 2011 | ❌ EOL | 40+ | 83 KB | 🔴 SUPPRIMER |
| 1.11.0 | 2014 | ❌ EOL | 10+ | 96 KB | 🔴 SUPPRIMER |
| 1.11.2 | 2014 | ❌ EOL | 10+ | 96 KB | 🔴 SUPPRIMER |
| 3.5.1 | 2020 | ⚠️ Obsolète | 2 | 88 KB | 🟡 MIGRER |
| **3.7.1** | **2023** | ✅ **Stable** | **0** | **85 KB** | ✅ **ADOPTER** |

---

### Axios

| Version | Date | Statut | CVE | Action |
|---------|------|--------|-----|--------|
| 0.24.0 | 2021 | ⚠️ Vulnérable | 3 | 🔴 MIGRER |
| **1.7.9** | **2024** | ✅ **Stable** | **0** | ✅ **ADOPTER** |

---

### Day.js

| Version | Date | Statut | Action |
|---------|------|--------|--------|
| 1.11.1 | 2022 | ✅ OK | 🟢 UPDATE |
| **1.11.13** | **2024** | ✅ **Stable** | ✅ **ADOPTER** |

---

## 🎯 Résumé Actions

### Immédiat (Cette semaine)

- [ ] Supprimer event_ably.php et références Ably
- [ ] Supprimer dhtmlgoodies_calendar.js
- [ ] Supprimer jquery.fixedheadertable.min.js
- [ ] Documenter usage jQuery 1.x (préparation migration)

### Court terme (Mois prochain)

- [ ] Mettre à jour Axios 0.24 → 1.7.9
- [ ] Mettre à jour Day.js 1.11.1 → 1.11.13
- [ ] Consolider jQuery UI vers 1.13.2
- [ ] Migrer jQuery Tooltip → Bootstrap 5

### Moyen terme (Trimestre)

- [ ] Migrer Moment.js → Day.js
- [ ] Consolider jQuery vers 3.7.1 unique
- [ ] Migrer Sticky Headers → CSS natif
- [ ] Évaluer DataTables 2.x

### Long terme (Année)

- [ ] Évaluer migration FullCalendar 6.x
- [ ] Évaluer remplacement plugins jQuery obsolètes
- [ ] Migration progressive vers npm/app2

---

## 📚 Documentation à Mettre à Jour

### AUDIT_PHASE_0.md

**Section à modifier** : Dépendances Node/JavaScript

**Ajouts** :
- Mention suppression Ably (test abandonné)
- Inventaire complet bibliothèques JS
- Plan de migration jQuery

### README_MIGRATION.md

**Ajouts** :
- Section migration JavaScript libraries
- Lien vers JS_LIBRARIES_AUDIT.md

---

## 🔗 Ressources

### Alternatives Modernes

- **jQuery** : Vanilla JS, Vue 3, Alpine.js
- **Moment.js** : Day.js, Luxon, date-fns
- **DataTables** : TanStack Table, AG Grid
- **FullCalendar** : FullCalendar v6, Toast UI Calendar
- **File Upload** : Uppy, FilePond, Dropzone.js
- **Autocomplete** : Autocomplete.js, Choices.js

### Documentation

- [jQuery Migration Guide](https://jquery.com/upgrade-guide/)
- [Axios Migration 0.x → 1.x](https://github.com/axios/axios/blob/master/MIGRATION_GUIDE.md)
- [Day.js Documentation](https://day.js.org/)
- [You Might Not Need jQuery](http://youmightnotneedjquery.com/)

---

## ✅ Conclusion

### Points Clés

1. **6 versions jQuery** = problème critique (sécurité + performance)
2. **Ably** = test abandonné, à supprimer
3. **Axios 0.24** = 3 CVE, mettre à jour en priorité
4. **Moment.js** = déprécié, migrer vers Day.js (déjà présent)
5. **dhtmlgoodies** = obsolète, migrer vers HTML5

### Bénéfices Migration

- ✅ **Sécurité** : Correction 60+ CVE (jQuery 1.x)
- ✅ **Performance** : -500 KB JS, moins de fichiers
- ✅ **Maintenabilité** : Versions modernes, documentées
- ✅ **Compatibilité** : PHP 8, Bootstrap 5

### Effort Estimé

| Phase | Effort | Priorité |
|-------|--------|----------|
| **Nettoyage** | 1 jour | 🔴 Critique |
| **Mise à jour sécurité** | 1 semaine | 🟡 Important |
| **Modernisation** | 2-4 semaines | 🟢 Planifié |
| **Migration npm** | Optionnel | 🔵 Long terme |

---

**Auteur** : Laurent Garrigue / Claude Code
**Date** : 31 octobre 2025
**Dernière mise à jour** : 1er novembre 2025
**Version** : 1.1
**Statut** : ✅ **PHASE 1 TERMINÉE** (voir [JS_LIBRARIES_CLEANUP_PLAN.md](JS_LIBRARIES_CLEANUP_PLAN.md))
