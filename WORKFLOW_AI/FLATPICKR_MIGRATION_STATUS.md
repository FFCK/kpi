# Statut Migration Flatpickr

**Date**: 4 novembre 2025
**Statut**: ✅ **MIGRATION COMPLÈTE - EN ATTENTE DE TESTS**

---

## 📊 Progression

```
Migration dhtmlgoodies → Flatpickr
████████████████████████████████████████ 100% (3/3 templates)

✅ Installation : Flatpickr 4.6.13 + wrapper JS
✅ Templates migrés : page.tpl, pageMap.tpl, page_jq.tpl
✅ Caches vidés : templates_c/
⏳ Tests : À réaliser sur 10 pages admin
```

---

## ✅ Templates migrés

| Template | Statut | Sections | Notes |
|----------|--------|----------|-------|
| **page.tpl** | ✅ Migré | Public + Admin | Déjà fait (2/11) |
| **pageMap.tpl** | ✅ Migré | Public + Admin | Migré aujourd'hui (4/11) |
| **page_jq.tpl** | ✅ Migré | Public + Admin | dhtmlgoodies commenté |

---

## 🎯 Pages à tester (17 datepickers)

| Page | Champs datepicker | Statut | Notes |
|------|------------------|--------|-------|
| **GestionUtilisateur.php** | 2 (Date début/fin) | ⏳ À tester | |
| **GestionCompetition.php** | 6 (Dates compét/saison) | ⏳ À tester | |
| **GestionJournee.php** | 1 (Date match) | ⏳ À tester | |
| **GestionEquipeJoueur.php** | 1 (Date naissance) | ⏳ À tester | |
| **GestionParamJournee.php** | 2 (Date début/fin) | ⏳ À tester | |
| **GestionEvenement.php** | 2 (Date début/fin) | ⏳ À tester | |
| **GestionAthlete.php** | 1 (Date naissance) | ⏳ À tester | |
| **GestionCopieCompetition.php** | 2 (Date début/fin) | ⏳ À tester | |

**Total : 17 datepickers sur 8 pages**

---

## 🧪 Checklist de tests

Pour chaque page ci-dessus :

- [ ] Le datepicker s'ouvre au focus/clic
- [ ] L'interface est en français (mois, jours)
- [ ] Le format est `dd/mm/yyyy` (ex: 04/11/2025)
- [ ] La saisie manuelle fonctionne
- [ ] La sélection d'une date remplit le champ
- [ ] Le formulaire se soumet correctement
- [ ] Aucune erreur dans la console JavaScript (F12)

---

## 📝 Actions réalisées aujourd'hui

### 1. Vérification infrastructure
- ✅ Flatpickr installé : `sources/node_modules/flatpickr/dist/`
- ✅ Wrapper existant : `sources/js/flatpickr-wrapper.js`
- ✅ Template page.tpl déjà migré

### 2. Migration pageMap.tpl
- ✅ Remplacé CSS dhtmlgoodies par Flatpickr (lignes 13, 22)
- ✅ Ajouté scripts Flatpickr (lignes 41-43, 51-53)
- ✅ Sections public et admin migrées

### 3. Nettoyage
- ✅ Cache Smarty vidé (`sources/smarty/templates_c/`)
- ✅ Vérification : aucune référence active dhtmlgoodies (tout commenté)

---

## 🚀 Prochaines étapes

### 1. Tests (aujourd'hui)
- [ ] Tester les 8 pages admin listées ci-dessus
- [ ] Vérifier console JavaScript (F12) sur chaque page
- [ ] Valider le format français dd/mm/yyyy
- [ ] Tester la saisie manuelle

### 2. Validation (48h)
- [ ] Monitoring en production
- [ ] Recueillir feedback utilisateurs
- [ ] Vérifier logs d'erreurs

### 3. Nettoyage final (après validation)
- [ ] Supprimer `sources/js/dhtmlgoodies_calendar.js`
- [ ] Supprimer `sources/css/dhtmlgoodies_calendar.css`
- [ ] Mettre à jour `JS_LIBRARIES_AUDIT.md`
- [ ] Commit final de migration

---

## 📞 En cas de problème

### Rollback rapide
```bash
# 1. Restaurer page.tpl et pageMap.tpl depuis Git
git checkout sources/smarty/templates/page.tpl
git checkout sources/smarty/templates/pageMap.tpl

# 2. Vider cache Smarty
rm -rf sources/smarty/templates_c/*

# 3. Redémarrer PHP
make dev_restart
```

### Vérifications console
```javascript
// Console JavaScript (F12)
console.log(typeof flatpickr);          // "function"
console.log(typeof displayCalendar);    // "function"
console.log(flatpickr.l10ns.default);   // "fr"
```

---

## 📚 Documentation

- Guide complet : [FLATPICKR_MIGRATION_GUIDE.md](FLATPICKR_MIGRATION_GUIDE.md)
- Wrapper source : [sources/js/flatpickr-wrapper.js](../sources/js/flatpickr-wrapper.js)
- Flatpickr docs : https://flatpickr.js.org/

---

## 🆕 Intégration directInput (4 novembre 2025, 14:00)

### Problème
Dans GestionCalendrier, les spans `directInput` avec `data-type="date"` ou `data-type="dateEN"` se transformaient en champs texte avec simple masque (`99/99/9999` ou `9999-99-99`), sans datepicker interactif.

### Solution
Modification de [sources/js/GestionCalendrier.js](../sources/js/GestionCalendrier.js) (lignes 139-160) pour initialiser Flatpickr sur les champs date créés dynamiquement.

**Code avant** (simple masque) :
```javascript
case 'date':
    jq(this).before('<input type="text" id="inputZone" class="directInputSpan" size="8" value="' + valeur + '" >')
    jq('#inputZone').mask("99/99/9999")
    break
```

**Code après** (Flatpickr) :
```javascript
case 'date':
    jq(this).before('<input type="text" id="inputZone" class="directInputSpan" size="8" value="' + valeur + '" >')
    // Initialiser Flatpickr avec format français
    flatpickr('#inputZone', {
        dateFormat: 'd/m/Y',
        locale: 'fr',
        allowInput: true,
        clickOpens: true,
        defaultDate: valeur || null
    })
    break
```

### Bénéfices
- ✅ Datepicker interactif au clic sur les dates (Date_debut, Date_fin)
- ✅ Format français (dd/mm/yyyy) ou ISO (yyyy-mm-dd) selon langue
- ✅ Saisie manuelle toujours possible
- ✅ Cohérence avec le reste de l'application
- ✅ Gestion correcte du blur lors de la sélection de date

### Fichiers modifiés
- [sources/js/GestionCalendrier.js](../sources/js/GestionCalendrier.js) - Intégration Flatpickr dans directInput

### Corrections apportées

**Problème 1** : Le champ disparaissait lors du clic sur une date dans le calendrier
- **Cause** : L'événement `blur` se déclenchait immédiatement au clic
- **Solution** :
  1. Ajout de `data-anciennevaleur` aux inputs date
  2. Utilisation de `onClose` callback pour déclencher le blur après fermeture
  3. Vérification de la présence du calendrier ouvert (`.flatpickr-calendar.open`)
  4. Refactorisation du code blur dans fonction `processBlur()`

**Problème 2** : Paramètres AjId et AjTypeValeur non transmis à UpdateCellJQ.php
- **Cause** : Les valeurs dépendent de `thisSpan` (attributs `data-id` et `data-target`)
- **Solution** :
  1. Ajout de vérification `thisSpan.length` avec early return
  2. Ajout de logs debug pour tracer numJournee et typeValeur
  3. Extraction correcte des attributs depuis thisSpan

**Problème 3** : "thisSpan not found" lors de modification manuelle de la date
- **Cause** : L'input `#inputZone` peut être détaché du DOM par Flatpickr, et les données jQuery peuvent être perdues
- **Solution finale** (lignes 127, 143, 162, 206-221) :
  1. Stockage d'une référence au span AVANT de le cacher (ligne 127) : `var spanRef = jq(this)`
  2. Stockage comme **propriété DOM native** (lignes 143, 162) : `document.getElementById('inputZone')._spanRef = spanRef[0]`
  3. Récupération de la référence DOM dans `processBlur()` (lignes 208-211) : `if (element._spanRef) { thisSpan = jq(element._spanRef) }`
  4. Fallback sur `.nextAll('span.directInput').first()` si pas de référence (ligne 215)
  5. Fallback final sur `.next('span')` pour les autres types de champs (ligne 220)
- **Note technique** : L'utilisation d'une propriété DOM native (`_spanRef`) au lieu de jQuery `.data()` garantit que la référence persiste même si Flatpickr manipule l'élément

### Templates concernés
- [GestionCalendrier.tpl](../sources/smarty/templates/GestionCalendrier.tpl) - 2 champs date (Date_debut, Date_fin)
- [GestionJournee.tpl](../sources/smarty/templates/GestionJournee.tpl) - 1 champ date (Date match)

---

## 🆕 Intégration GestionJournee (4 novembre 2025, 16:00)

### Application du même pattern
Le même système d'intégration Flatpickr a été appliqué à [sources/js/GestionJournee.js](../sources/js/GestionJournee.js) avec succès.

**Modifications apportées** :
1. **Ligne 534** : Ajout de `var spanRef = jq(this)` pour stocker la référence au span
2. **Lignes 539-560** : Case 'date' avec Flatpickr (format dd/mm/yyyy)
   - Stockage de la référence span : `inputElement._spanRef = spanRef[0]`
   - Callback `onClose` : capture de la valeur et appel direct de `validationDonnee()`
3. **Lignes 561-582** : Case 'dateEN' avec Flatpickr (format yyyy-mm-dd)
4. **Lignes 735-746** : Modification du blur handler
   - Ignore complètement les inputs Flatpickr (validation gérée par `onClose`)
5. **Lignes 825-898** : Refactorisation de `validationDonnee(Classe, element, valueOverride)`
   - Nouveau paramètre `valueOverride` pour passer la valeur explicitement
   - Récupération de `thisSpan` depuis `element._spanRef`
   - Fallback sur sélecteur DOM classique si pas de référence

### Problèmes résolus

**Problème 1** : "thisSpan not found" lors de modification manuelle
- **Cause** : Contexte `this` perdu lors de l'appel `jq('#inputZone').blur()` depuis `onClose`
- **Solution** : Appel direct de `validationDonnee()` avec l'élément fourni par Flatpickr (`instance.input`)

**Problème 2** : Paramètre AjValeur non transmis
- **Cause** : Valeur de l'input inaccessible après manipulation par Flatpickr
- **Solution** : Capture de la valeur dans `onClose` et passage via paramètre `valueOverride`

**Bénéfices** :
- ✅ Datepicker interactif sur les dates de matchs dans GestionJournee
- ✅ Modification par clic : mise à jour immédiate en base de données
- ✅ Modification manuelle : validation correcte avec tous les paramètres

---

**Mise à jour** : 4 novembre 2025, 17:00
**Par** : Claude Code
**Statut** : ✅ **MIGRATION DIRECTINPUT COMPLÈTE ET TESTÉE (GestionCalendrier + GestionJournee)**
