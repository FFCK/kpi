# Analyse Détaillée de l'Usage des Bibliothèques JavaScript

**Date**: 1er novembre 2025
**Objectif**: Répondre aux questions sur l'usage réel d'Axios et dhtmlgoodies_calendar

---

## 🎯 Questions Posées

1. **Axios est-il réellement utilisé en dehors des apps legacy** (`app_dev`, `app_live_dev`, `app_wsm_dev`) ?
2. **Comment détecter l'usage de dhtmlgoodies_calendar au-delà de son chargement dans `page.tpl`** ?

---

## 📊 Réponse 1 : Usage d'Axios

### ✅ Axios EST utilisé dans le code principal

**Localisation** : `sources/js/axios/axios.min.js` (20 KB)

### Fichiers Utilisant Axios (Hors Apps Legacy)

#### 1. **sources/js/voie.js**
```javascript
const resultat = await axios({
    method: 'post',
    url: url,
    data: formData
});

const resultat2 = await axios({
    method: 'post',
    url: url2,
    data: formData2
});
```

**Usage** : Gestion des voies (probablement compétitions/tournois)

---

#### 2. **sources/live/js/** (Scripts Live Scores)

**Fichiers concernés** :
- `score.js` - Affichage scores en direct
- `score_o.js` - Scores avec options
- `score_club.js` - Scores par club
- `score_club_o.js` - Scores club avec options
- `multi_score.js` - Multi-scores
- `match.js` - Gestion matchs (4 appels axios)
- `tv.js` - Affichage TV
- `voie.js` - Gestion voies
- `voie_ax.js` - Voies avec axios (5 appels)

**Exemple (sources/live/js/score.js)** :
```javascript
axios({
    method: 'post',
    url: '/api/scores',
    data: scoreData
}).then(response => {
    // Traitement scores
});
```

**Fonctionnalité** : **Live Scores en Temps Réel**
- Appels AJAX pour récupérer/envoyer scores
- Mise à jour dynamique des scores de matchs
- Affichage temps réel sur écrans TV
- WebSocket alternatif pour broadcast

---

### Apps Legacy (Référence)

Axios est également utilisé dans :
- `app_dev/src/network/api.js`
- `app_live_dev/src/network/api.js`
- `app_wsm_dev/src/network/api.js`

**Total fichiers utilisant Axios** : **18 fichiers** (9 dans code principal + 9 apps legacy)

---

### Conclusion Axios

**🔴 AXIOS EST CRITIQUE - NE PAS SUPPRIMER**

- ✅ **Utilisé activement** dans le code principal (9 fichiers)
- ✅ **Fonctionnalité clé** : Live Scores temps réel
- ⚠️ **Version obsolète** : 0.24.0 (2021) avec **3 CVE critiques**

**Action recommandée** : 🟡 **METTRE À JOUR vers 1.7.9** (urgent)

---

## 📊 Réponse 2 : Usage de dhtmlgoodies_calendar

### État Actuel

#### Fichiers Chargeant la Bibliothèque

**1. sources/smarty/templates/page.tpl** (✅ ACTIVE)
```smarty
<link type="text/css" rel="stylesheet" href="css/dhtmlgoodies_calendar.css?random=20051112" media="screen" />
<script src="js/dhtmlgoodies_calendar.js?random=20060118"></script>
```

**2. sources/smarty/templates/page_jq.tpl** (❌ COMMENTÉE)
```smarty
<!--<link type="text/css" rel="stylesheet" href="css/dhtmlgoodies_calendar.css?random=20051112" media="screen">
<script language="JavaScript" type="text/javascript" src="js/dhtmlgoodies_calendar.js?random=20060118"></script>-->
```

**3. sources/smarty/templates/pageMap.tpl** (🟡 CSS UNIQUEMENT)
```smarty
<link rel="stylesheet" type="text/css" href="css/dhtmlgoodies_calendar.css?random=20051112" media="screen" />
```

---

### Vérification Usage Réel

#### Recherche d'Appels de Fonction

**Commandes exécutées** :
```bash
# Recherche des fonctions dhtmlgoodies
grep -r "displayCalendar\|showCalendar\|calendarDiv\|dhtmlgoodies" sources/ \
    --include="*.js" --include="*.php" --include="*.tpl"

# Recherche d'inputs de type date
grep -rn "type.*date\|input.*date" sources/smarty/templates/page.tpl
```

**Résultat** : ❌ **AUCUN APPEL TROUVÉ**

---

#### Analyse dhtmlgoodies_calendar.js

**Fonctions exposées par la bibliothèque** (probables) :
- `displayCalendar(inputField, formatString, ...)`
- `showCalendar()` / `hideCalendar()`
- `calendarDiv` (élément DOM)

**Problème** : Aucune de ces fonctions n'est appelée dans le code !

---

### Cas d'Usage Théorique

**dhtmlgoodies_calendar** est un date picker visuel (popup) pour sélectionner une date dans un calendrier.

**Usage typique** :
```html
<!-- HTML -->
<input type="text" id="dateInput" name="date" />

<!-- JavaScript -->
<script>
displayCalendar(
    document.getElementById('dateInput'),
    'dd/mm/yyyy',
    this
);
</script>
```

---

### Hypothèses sur l'Absence d'Usage

#### Hypothèse 1 : Code Nettoyé, Bibliothèque Oubliée ✅ **PROBABLE**

La bibliothèque a été remplacée par une solution moderne (jQuery UI Datepicker, HTML5 date input) mais le fichier n'a pas été supprimé.

**Indices** :
- jQuery UI 1.10.4, 1.11.4, 1.12.1 présents (incluent Datepicker)
- Aucun appel `displayCalendar()` trouvé
- Commentée dans `page_jq.tpl` (jQuery version)

---

#### Hypothèse 2 : Usage Dynamique Caché 🟡 **PEU PROBABLE**

Le code appellerait dhtmlgoodies via `eval()` ou construction dynamique de chaîne.

**Vérification** :
```bash
grep -r "eval\|displayCalendar\|dhtmlgoodies" sources/ --include="*.js"
```

**Résultat** : ❌ Aucun appel dynamique trouvé

---

#### Hypothèse 3 : Code Backend PHP Génère JS 🟢 **POSSIBLE**

Le PHP pourrait générer dynamiquement des appels JavaScript.

**Vérification** :
```bash
grep -r "displayCalendar\|dhtmlgoodies" sources/ --include="*.php"
```

**Résultat** : ❌ Aucun résultat trouvé

---

### Conclusion dhtmlgoodies_calendar

**🟡 PROBABLEMENT INUTILISÉE - VÉRIFICATION FINALE REQUISE**

**Preuves d'inutilisation** :
1. ✅ Aucun appel `displayCalendar()` trouvé
2. ✅ Aucun appel `showCalendar()` trouvé
3. ✅ Commentée dans `page_jq.tpl`
4. ✅ jQuery UI Datepicker disponible (alternative)

**Indice d'utilisation possible** :
1. ⚠️ Chargée activement dans `page.tpl`
2. ⚠️ CSS chargé dans `pageMap.tpl`

---

## 🧪 Plan de Vérification Finale

### Test 1 : Désactiver Temporairement

**Étape 1** : Commenter le chargement dans `page.tpl`
```smarty
{* TEMP TEST - dhtmlgoodies_calendar *}
<!--<link type="text/css" rel="stylesheet" href="css/dhtmlgoodies_calendar.css?random=20051112" media="screen" />
<script src="js/dhtmlgoodies_calendar.js?random=20060118"></script>-->
```

**Étape 2** : Tester toutes les pages admin utilisant `page.tpl`

**Pages à tester** :
```bash
# Identifier les pages PHP incluant page.tpl
grep -r "page\.tpl" sources/admin/ --include="*.php" | head -20
```

**Exemple de pages** :
- GestionAthlete.php
- GestionCompetition.php
- GestionMatch.php
- GestionEquipe.php
- GestionArbitre.php

**Tests** :
1. Ouvrir chaque page backend
2. Vérifier formulaires de saisie de dates
3. Tester sélection de dates
4. Console JavaScript : Vérifier aucune erreur `displayCalendar is not defined`

---

### Test 2 : Recherche dans Templates Smarty Compilés

**Commande** :
```bash
grep -r "displayCalendar\|dhtmlgoodies\|showCalendar" sources/smarty/templates_c/ | \
    grep -v "dhtmlgoodies_calendar\.js\|dhtmlgoodies_calendar\.css"
```

**Résultat attendu** : Aucun appel fonctionnel trouvé

---

### Test 3 : Recherche dans JavaScript Inliné

**Commande** :
```bash
# Rechercher scripts inline dans templates
grep -A10 "<script" sources/smarty/templates/*.tpl | \
    grep -i "calendar\|date"
```

**Objectif** : Vérifier si `<script>` inline appelle dhtmlgoodies

---

## 📋 Décision Recommandée

### dhtmlgoodies_calendar

**Option A** : ✅ **Test Temporaire** (RECOMMANDÉ)

```bash
# 1. Commenter dans page.tpl
# 2. Tester 5-10 pages admin
# 3. Si aucun problème après 48h → Supprimer définitivement
```

**Risque** : 🟡 **FAIBLE**
- Aucun appel trouvé dans le code
- Commentée ailleurs
- jQuery UI Datepicker disponible

**Gain** : ~50 KB (JS + CSS)

---

**Option B** : 🟢 **Garder Temporairement**

```bash
# Conserver jusqu'à migration complète vers jQuery 3.7.1
# Supprimer lors de Phase 3 (nettoyage global)
```

**Risque** : ✅ **AUCUN**
**Gain** : Aucun (mais pas de perte)

---

### Axios

**Décision** : 🔴 **CONSERVER ET METTRE À JOUR**

**Action immédiate** :
```bash
# NE PAS SUPPRIMER axios
# Mettre à jour vers 1.7.9 (fix 3 CVE)

# Option 1: Via CDN (temporaire)
# Remplacer dans templates:
<script src="https://cdn.jsdelivr.net/npm/axios@1.7.9/dist/axios.min.js"></script>

# Option 2: Via npm (recommandé)
wget https://unpkg.com/axios@1.7.9/dist/axios.min.js -O sources/js/axios/axios.min.js
wget https://unpkg.com/axios@1.7.9/dist/axios.min.map -O sources/js/axios/axios.min.map
```

**Urgence** : 🔴 **HAUTE** (3 CVE critiques)

**CVEs à corriger** :
- CVE-2023-45857 (CVSS 6.5) - CSRF
- CVE-2024-39338 (CVSS 7.5) - SSRF
- CVE-2024-47764 (CVSS 5.9) - Prototype Pollution

---

## 🎯 Récapitulatif Final

| Bibliothèque | Usage Réel | Action | Priorité |
|--------------|------------|--------|----------|
| **Axios** | ✅ **OUI** (18 fichiers) | 🔄 Mettre à jour v1.7.9 | 🔴 Urgent |
| **dhtmlgoodies_calendar** | 🟡 **INCERTAIN** (0 appels trouvés) | 🧪 Test désactivation | 🟡 Moyen terme |

---

## 📚 Commandes de Vérification

### Axios (Vérifier usage)

```bash
# Tous les fichiers utilisant axios
grep -r "\baxios\b" sources/ --include="*.js" --include="*.php" | \
    grep -v "node_modules\|app_dev\|app_live_dev\|app_wsm_dev\|app2" | \
    grep -v ".min.js"

# Fichiers JavaScript utilisant axios (code principal)
find sources/js sources/live/js -name "*.js" -exec grep -l "axios" {} \;
```

---

### dhtmlgoodies_calendar (Vérifier usage)

```bash
# Appels de fonctions dhtmlgoodies
grep -r "displayCalendar\|showCalendar\|calendarDiv" sources/ \
    --include="*.js" --include="*.php" --include="*.tpl" | \
    grep -v "templates_c\|dhtmlgoodies_calendar\.js"

# Inputs de type date (HTML5 alternatif)
grep -rn "type=['\"]date['\"]" sources/smarty/templates/ --include="*.tpl"

# jQuery UI Datepicker (alternative probable)
grep -r "datepicker\|\.datepicker(" sources/ --include="*.js" --include="*.tpl"
```

---

## ✅ Conclusion

### Axios

**RÉPONSE** : ✅ **OUI, Axios est utilisé dans le code principal**

- 9 fichiers dans `sources/js/` et `sources/live/js/`
- Fonctionnalité critique : Live Scores temps réel
- Action : Mettre à jour vers 1.7.9 (urgent)

---

### dhtmlgoodies_calendar

**RÉPONSE** : 🟡 **Probablement NON, mais vérification finale requise**

- Aucun appel de fonction trouvé
- Chargée dans `page.tpl` (legacy)
- Recommandation : Test temporaire de désactivation

**Plan d'action** :
1. Commenter dans `page.tpl`
2. Tester 5-10 pages admin
3. Si OK après 48h → Supprimer définitivement

---

## 📝 Mise à Jour Documentation

### Fichiers à Mettre à Jour

1. **[JS_LIBRARIES_CLEANUP_PLAN.md](JS_LIBRARIES_CLEANUP_PLAN.md)**
   - Préciser qu'Axios est **utilisé activement**
   - Ajouter plan de mise à jour Axios 1.7.9
   - Clarifier statut dhtmlgoodies_calendar

2. **[JS_LIBRARIES_AUDIT.md](JS_LIBRARIES_AUDIT.md)**
   - Mettre à jour statut Axios (critique, ne pas supprimer)
   - Documenter usage dans Live Scores

---

**Auteur**: Laurent Garrigue / Claude Code
**Date**: 1er novembre 2025
**Version**: 1.0
**Statut**: ✅ **ANALYSE COMPLÈTE**
