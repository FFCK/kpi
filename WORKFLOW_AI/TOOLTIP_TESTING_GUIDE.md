# Guide de Test des Tooltips Bootstrap 5

**Date** : 6 novembre 2025
**Objectif** : Identifier et tester les tooltips Bootstrap 5 dans l'application

---

## 📍 Pages utilisant chaque template

### 1. **kppagewide.tpl** ✅ Migré (Bootstrap 5 + script tooltip)

**Pages utilisant ce template** (via `DisplayTemplateNewWide()`) :
- **kptv.php** - Affichage TV des matchs
- **kptvscenario.php** - Scénarios TV

**Accès** :
```
https://kpi.local/kptv.php
https://kpi.local/kptvscenario.php
```

### 2. **kppage.tpl** ⏳ Bootstrap 5 présent, script tooltip à ajouter

**Pages utilisant ce template** (via `DisplayTemplateNew()`) :
- **kpphases.php** - Gestion des phases
- **kpterrains.php** - Gestion des terrains
- **kpchart.php** - Graphiques
- **kpdetails.php** - Détails des matchs
- **admin/GestionSchema.php** - Schémas (admin)
- **index_dev.php** - Page d'accueil développement
- **kpclassement.php** - Classement
- **kpclassements.php** - Classements multiples
- **kpequipes.php** - Équipes

**Accès exemples** :
```
https://kpi.local/kpequipes.php
https://kpi.local/kpclassement.php?Saison=2024&Compet=N1H
https://kpi.local/admin/GestionSchema.php
```

### 3. **kppageleaflet.tpl** ⏳ Bootstrap 5 présent, script tooltip à ajouter

**Pages utilisant ce template** (via `DisplayTemplateLeaflet()`) :
- **admin/GestionStructure.php** - Gestion des structures avec carte (admin)
- **kpclubs.php** - Clubs avec carte

**Accès exemples** :
```
https://kpi.local/kpclubs.php
https://kpi.local/admin/GestionStructure.php
```

---

## 🔍 Comment identifier si les tooltips sont Bootstrap 5

### Méthode 1 : Console JavaScript (F12)

1. **Ouvrir la console** JavaScript (F12)
2. **Taper cette commande** :
```javascript
console.log('Bootstrap:', typeof bootstrap !== 'undefined' ? bootstrap.Tooltip.VERSION : 'non trouvé');
console.log('Tooltip init script:', typeof reinitializeTooltips !== 'undefined' ? 'Chargé' : 'Non chargé');
```

**Résultat attendu** :
```
Bootstrap: 5.3.3 (ou version 5.x)
Tooltip init script: Chargé
```

### Méthode 2 : Inspecter l'élément tooltip

1. **Faire apparaître un tooltip** (survoler un élément avec title)
2. **Inspecter l'élément** (clic droit → Inspecter)
3. **Chercher dans le DOM** :

**Bootstrap 5 Tooltip** (nouveau) :
```html
<div class="tooltip bs-tooltip-top" role="tooltip">
  <div class="tooltip-arrow"></div>
  <div class="tooltip-inner">Texte du tooltip</div>
</div>
```
- Classes : `tooltip`, `bs-tooltip-*`, `tooltip-arrow`, `tooltip-inner`
- Attribut `role="tooltip"`

**jQuery Tooltip** (ancien) :
```html
<div id="tooltip" style="...">
  Texte du tooltip
</div>
```
- ID simple `#tooltip`
- Style inline
- Pas de structure interne

### Méthode 3 : Vérifier les scripts chargés

**Dans l'onglet Sources/Network de F12** :

**Pages avec Bootstrap 5 Tooltip** :
```
✅ bootstrap.bundle.min.js (v5.x)
✅ bootstrap-tooltip-init.js ← Script créé aujourd'hui
❌ jquery.tooltip.min.js (ne doit PAS être chargé)
```

**Pages avec jQuery Tooltip** (ancien) :
```
❌ jquery-1.5.2.min.js
✅ jquery.tooltip.min.js
❌ bootstrap-tooltip-init.js (absent)
```

### Méthode 4 : Tester l'API Bootstrap

**Console JavaScript** :
```javascript
// Test 1: Vérifier Bootstrap 5
console.log('Bootstrap 5:', typeof bootstrap !== 'undefined');

// Test 2: Créer un tooltip dynamiquement
const testBtn = document.createElement('button');
testBtn.setAttribute('data-bs-toggle', 'tooltip');
testBtn.setAttribute('title', 'Test Bootstrap 5');
testBtn.textContent = 'Test';
document.body.appendChild(testBtn);

try {
  const tooltip = new bootstrap.Tooltip(testBtn);
  console.log('✅ Bootstrap 5 Tooltip fonctionne');
  tooltip.dispose();
  testBtn.remove();
} catch(e) {
  console.log('❌ Bootstrap 5 Tooltip ne fonctionne pas:', e.message);
}
```

---

## ✅ Checklist de test par template

### kppagewide.tpl (déjà migré)

- [x] Bootstrap 5 présent : `bootstrap.bundle.min.js` ligne 79
- [x] Script tooltip ajouté : `bootstrap-tooltip-init.js` ligne 54
- [ ] **À tester** :
  - [ ] Ouvrir kptv.php
  - [ ] Vérifier console : Bootstrap 5.x.x
  - [ ] Survoler un élément avec `title` attribute
  - [ ] Inspecter : doit avoir classe `bs-tooltip-*`
  - [ ] Vérifier absence de `jquery.tooltip.min.js`

### kppage.tpl (à migrer)

- [x] Bootstrap 5 présent : `bootstrap.bundle.min.js` ligne 79
- [ ] Script tooltip à ajouter : `bootstrap-tooltip-init.js`
- [ ] **À faire** :
  - [ ] Ajouter script bootstrap-tooltip-init.js après bootstrap.bundle.min.js
  - [ ] Vider cache Smarty
  - [ ] Tester sur kpequipes.php

### kppageleaflet.tpl (à migrer)

- [x] Bootstrap 5 présent : `bootstrap.bundle.min.js` ligne 89
- [ ] Script tooltip à ajouter : `bootstrap-tooltip-init.js`
- [ ] **À faire** :
  - [ ] Ajouter script bootstrap-tooltip-init.js après bootstrap.bundle.min.js
  - [ ] Vider cache Smarty
  - [ ] Tester sur kpclubs.php

---

## 🧪 Pages de test recommandées

### Test 1 : kptv.php (kppagewide.tpl) ✅ Déjà migré
```bash
# URL
https://kpi.local/kptv.php

# Éléments à tester
- Boutons avec title=""
- Liens avec title=""
- Icons avec title=""
```

### Test 2 : kpequipes.php (kppage.tpl)
```bash
# URL
https://kpi.local/kpequipes.php

# Vérifier
- Autocomplete (déjà migré Vanilla JS)
- Tooltips sur les éléments interactifs
```

### Test 3 : kpclubs.php (kppageleaflet.tpl)
```bash
# URL
https://kpi.local/kpclubs.php

# Vérifier
- Carte Leaflet
- Tooltips sur les marqueurs de carte
- Tooltips sur les éléments de formulaire
```

---

## 🐛 Problèmes courants et solutions

### Problème 1 : Tooltip n'apparaît pas

**Causes possibles** :
1. Script `bootstrap-tooltip-init.js` non chargé
2. Bootstrap 5 non chargé
3. Élément sans `title` attribute ou `data-bs-toggle="tooltip"`

**Solution** :
```javascript
// Console JavaScript
document.querySelectorAll('[title]').forEach(el => {
    console.log('Element avec title:', el, 'title:', el.getAttribute('title'));
});
```

### Problème 2 : Ancien style de tooltip (jQuery)

**Cause** : `jquery.tooltip.min.js` encore chargé

**Solution** :
1. Vérifier l'onglet Network (F12)
2. Chercher `jquery.tooltip.min.js`
3. Si présent : supprimer du template

### Problème 3 : Erreur "bootstrap is not defined"

**Cause** : Bootstrap 5 non chargé ou chargé après script tooltip

**Solution** :
1. Vérifier ordre des scripts dans le template
2. Bootstrap doit être chargé AVANT bootstrap-tooltip-init.js

```smarty
{* Ordre correct *}
<script src='vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js'></script>
<script src="js/bootstrap-tooltip-init.js"></script>
```

---

## 📊 Résumé visuel

```
┌─────────────────────────────────────────────────────┐
│         Template              │ Bootstrap 5 │ Script │
├─────────────────────────────────────────────────────┤
│ kppagewide.tpl (TV)           │     ✅      │   ✅   │
│ kppage.tpl (Équipes, etc.)    │     ✅      │   ❌   │
│ kppageleaflet.tpl (Clubs)     │     ✅      │   ❌   │
└─────────────────────────────────────────────────────┘
```

**Prochaine action** : Migrer kppage.tpl et kppageleaflet.tpl (ajouter script tooltip)

---

**Auteur** : Claude Code
**Date** : 6 novembre 2025
**Version** : 1.0
