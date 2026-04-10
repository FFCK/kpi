# Guide de Migration jQuery Autocomplete → Vanilla JS

**Date**: 3 novembre 2025
**Objectif**: Remplacer jquery.autocomplete.js par vanilla-autocomplete.js (zéro dépendance)
**Durée estimée**: 2-4 heures (17 fichiers)
**Complexité**: 🟢 Faible (API compatible)

---

## 📊 Contexte

### Situation Actuelle

- **Bibliothèque**: jquery.autocomplete.js (dépend de jQuery 1.5.2)
- **Usage**: **17 fichiers** JavaScript utilisent `.autocomplete()`
- **Pages impactées**: GestionCompetition, GestionEquipe, GestionJournee, etc.
- **Taille**: ~15 KB (autocomplete.js) + jQuery 1.5.2 (90 KB) = **105 KB**

### Objectif

- **Nouvelle bibliothèque**: vanilla-autocomplete.js (Vanilla JS pur)
- **Avantages**:
  - ✅ Zéro dépendance (pas de jQuery requis)
  - ✅ API compatible (migration transparente)
  - ✅ Moderne (fetch, async/await, debounce)
  - ✅ Performance (+100 KB économisés après suppression jQuery)
  - ✅ Accessible (navigation clavier, ARIA)
  - ✅ Cache intégré (requêtes limitées)

---

## 🎯 Plan de Migration

### Étape 1 : Installation vanilla-autocomplete.js

**Fichier déjà créé** : `sources/js/vanilla-autocomplete.js` ✅

**Charger dans templates** :

#### Modifier `sources/smarty/templates/page.tpl`

**Section 1 (pages publiques, ligne ~50)** :

```smarty
{* AVANT *}
<script src="js/jquery-1.5.2.min.js"></script>
<script src="js/jquery.autocomplete.min.js"></script>

{* APRÈS *}
<script src="js/vanilla-autocomplete.js?v={$NUM_VERSION}"></script>
<script src="js/jquery-1.5.2.min.js"></script>  {* Temporaire - à supprimer après migration complète *}
```

**Section 2 (pages admin, ligne ~97)** :

```smarty
{* AVANT *}
<script src="../js/jquery-1.5.2.min.js"></script>
<script src="../js/jquery.autocomplete.min.js"></script>

{* APRÈS *}
<script src="../js/vanilla-autocomplete.js?v={$NUM_VERSION}"></script>
<script src="../js/jquery-1.5.2.min.js"></script>  {* Temporaire - à supprimer après migration complète *}
```

**⚠️ Important** : Garder jQuery temporairement car certains plugins en dépendent encore (tooltip, maskedinput)

---

### Étape 2 : Migration Pattern de Code

#### 2.1. Pattern jQuery Autocomplete (AVANT)

```javascript
// jQuery 1.5.2 alias
var jq = $;

jq("#choixEquipe").autocomplete('Autocompl_equipe.php', {
    width: 550,
    max: 50,
    matchSubset: true,
    cacheLength: 10,
    formatItem: function(row, i, n) {
        return row[0] + ' - ' + row[1];
    },
    formatMatch: function(row, i, n) {
        return row[0];
    },
    formatResult: function(row) {
        return row[0];
    }
}).result(function(event, data, formatted) {
    // Callback sélection
    document.forms[0].Id_Equipe.value = data[1];
});
```

#### 2.2. Pattern Vanilla JS (APRÈS)

```javascript
// Vanilla JS (pas de jQuery requis)
vanillaAutocomplete('#choixEquipe', 'Autocompl_equipe.php', {
    width: 550,
    maxResults: 50,
    matchSubset: true,
    cacheLength: 10,
    formatItem: function(row, i, n) {
        return row[0] + ' - ' + row[1];
    },
    formatMatch: function(row, i, n) {
        return row[0];
    },
    formatResult: function(row) {
        return row[0];
    },
    onSelect: function(data, index) {
        // Callback sélection (identique à .result())
        document.forms[0].Id_Equipe.value = data[1];
    }
});
```

#### 2.3. Tableau de Correspondance API

| jQuery Autocomplete | Vanilla Autocomplete | Notes |
|---------------------|----------------------|-------|
| `max` | `maxResults` | Nombre max résultats |
| `.result(fn)` | `onSelect: fn` | Callback sélection |
| `delay` | `delay` | Délai debounce (défaut: 300ms) |
| `formatItem` | `formatItem` | Formatter affichage |
| `formatMatch` | `formatMatch` | Formatter matching |
| `formatResult` | `formatResult` | Formatter valeur finale |
| `extraParams` | `extraParams` | Paramètres additionnels |
| `width` | `width` | Largeur dropdown |
| `cacheLength` | `cacheLength` | Taille cache |
| `matchSubset` | `matchSubset` | Matching sous-chaînes |

---

### Étape 3 : Exemples de Migration par Fichier

#### 3.1. GestionEquipe.js (1 autocomplete)

**Ligne 323 - Autocomplete Équipe**

**AVANT** :
```javascript
jq("#choixEquipe").autocomplete('Autocompl_equipe.php', {
    width: 550,
    max: 50,
    matchSubset: true,
    cacheLength: 10,
    formatItem: function(row, i, n) {
        return "<table width=100%><tr><td width=20%>" + row[0] + "</td><td>" + row[1] + "</td></tr></table>";
    },
    formatMatch: function(row, i, n) {
        return row[1] + row[0];
    },
    formatResult: function(row) {
        return row[1];
    }
}).result(function(event, data, formatted) {
    document.forms[0].Id_Equipe.value = data[0];
    SelectEquipe(data[0]);
});
```

**APRÈS** :
```javascript
vanillaAutocomplete('#choixEquipe', 'Autocompl_equipe.php', {
    width: 550,
    maxResults: 50,
    matchSubset: true,
    cacheLength: 10,
    formatItem: function(row, i, n) {
        return "<table width=100%><tr><td width=20%>" + row[0] + "</td><td>" + row[1] + "</td></tr></table>";
    },
    formatMatch: function(row, i, n) {
        return row[1] + row[0];
    },
    formatResult: function(row) {
        return row[1];
    },
    onSelect: function(data, index) {
        document.forms[0].Id_Equipe.value = data[0];
        SelectEquipe(data[0]);
    }
});
```

**Changements** :
1. ✅ `jq("#choixEquipe")` → `vanillaAutocomplete('#choixEquipe')`
2. ✅ `max: 50` → `maxResults: 50`
3. ✅ `.result(fn)` → `onSelect: fn`

---

#### 3.2. GestionCompetition.js (8 autocompletes)

**Ligne 178 - Autocomplete Compétition**

**AVANT** :
```javascript
jq("#choixCompet").autocomplete('Autocompl_compet.php', {
    width: 350,
    max: 30,
    // ...
}).result(function(event, data, formatted) {
    // ...
});
```

**APRÈS** :
```javascript
vanillaAutocomplete('#choixCompet', 'Autocompl_compet.php', {
    width: 350,
    maxResults: 30,
    // ...
    onSelect: function(data, index) {
        // ...
    }
});
```

**Répéter pour les 7 autres autocompletes** (lignes 278, 290, 313, 337, 349, 372)

---

#### 3.3. Autres Fichiers (Pattern Identique)

**GestionJournee.js**, **GestionEquipeJoueur.js**, **GestionAthlete.js**, etc. :

Tous suivent le même pattern :
1. Remplacer `jq(selector).autocomplete()` par `vanillaAutocomplete(selector)`
2. Remplacer `max` par `maxResults`
3. Remplacer `.result(fn)` par `onSelect: fn`

---

### Étape 4 : Tester la Migration

#### 4.1. Vider Cache et Redémarrer

```bash
# Vider cache Smarty
rm -rf sources/smarty/templates_c/*

# Redémarrer containers
make docker_dev_restart
```

#### 4.2. Pages Admin à Tester

**Liste complète (17 fichiers)** :

1. **GestionEquipe.php** (1 autocomplete) - Recherche équipe
2. **GestionCompetition.php** (8 autocompletes) - Compétition, fusion joueurs/équipes, déplacement
3. **GestionJournee.php** - Recherche journée
4. **GestionEquipeJoueur.php** - Gestion joueurs équipe
5. **GestionAthlete.php** - Recherche athlète
6. **GestionUtilisateur.php** - Recherche utilisateur
7. **GestionStats.php** - Statistiques
8. **Palmares.php** - Palmarès
9. **GestionInstances.php** - Instances
10. **GestionMatchEquipeJoueur.php** - Match équipe joueur
11. **GestionParamJournee.php** - Paramètres journée
12. **GestionRc.php** - Recherche RC
13. **kpclubs.js** - Clubs (page publique)
14. **kpequipes.js** - Équipes (page publique)

#### 4.3. Checklist Tests par Page

Pour chaque autocomplete :

- [ ] Saisir 2+ caractères déclenche recherche
- [ ] Résultats s'affichent dans dropdown
- [ ] Navigation clavier fonctionne (↑↓)
- [ ] Sélection par clic remplit input
- [ ] Sélection par Enter remplit input
- [ ] Callback `onSelect` exécuté (valeurs remplies)
- [ ] Format affichage correct (HTML/texte)
- [ ] Aucune erreur console JavaScript (F12)
- [ ] Performance acceptable (debounce 300ms)
- [ ] Cache fonctionne (requêtes limitées)

#### 4.4. Vérifications Console JavaScript

Ouvrir console (F12) et vérifier :

```javascript
// Aucune erreur "$ is not defined"
// Aucune erreur "vanillaAutocomplete is not defined"

// Vérifier fonction disponible
console.log(typeof vanillaAutocomplete); // "function"

// Tester instance
const ac = vanillaAutocomplete('#test', { url: 'test.php' });
console.log(ac); // VanillaAutocomplete instance
```

---

## 📋 Checklist Migration Complète

### Phase 1 : Préparation
- [x] Créer `vanilla-autocomplete.js`
- [ ] Charger dans `page.tpl` (2 sections)
- [ ] Vider cache Smarty
- [ ] Tester page test (ex: GestionEquipe)

### Phase 2 : Migration Fichiers (17 fichiers)

**Priorité Haute** :
- [ ] GestionCompetition.js (8 autocompletes) - 🔴 Page critique
- [ ] GestionEquipe.js (1 autocomplete)
- [ ] GestionJournee.js
- [ ] GestionEquipeJoueur.js

**Priorité Moyenne** :
- [ ] GestionAthlete.js
- [ ] GestionUtilisateur.js
- [ ] GestionStats.js
- [ ] Palmares.js
- [ ] GestionInstances.js
- [ ] GestionMatchEquipeJoueur.js
- [ ] GestionParamJournee.js
- [ ] GestionRc.js

**Priorité Basse** :
- [ ] kpclubs.js (page publique)
- [ ] kpequipes.js (page publique)
- [ ] fm2_A.js, fm3_A.js, fm4_A.js (admin v2)

### Phase 3 : Nettoyage
- [ ] Tester toutes pages migrées
- [ ] Supprimer `jquery.autocomplete.min.js` de `page.tpl`
- [ ] Supprimer `jquery.autocomplete.css` de `page.tpl`
- [ ] Supprimer fichiers obsolètes (`sources/js/jquery.autocomplete.*`)
- [ ] (Futur) Supprimer jQuery 1.5.2 après migration autres plugins

---

## 🆘 Problèmes Courants

### Problème 1 : `vanillaAutocomplete is not defined`

**Symptôme** : Erreur console `Uncaught ReferenceError: vanillaAutocomplete is not defined`

**Causes possibles** :
1. ❌ `vanilla-autocomplete.js` pas chargé dans `page.tpl`
2. ❌ Chemin fichier incorrect
3. ❌ Cache Smarty pas vidé

**Solutions** :
```bash
# 1. Vérifier chargement dans page.tpl
grep "vanilla-autocomplete" sources/smarty/templates/page.tpl

# 2. Vérifier fichier existe
ls -lh sources/js/vanilla-autocomplete.js

# 3. Vider cache Smarty
rm -rf sources/smarty/templates_c/*

# 4. Redémarrer
make docker_dev_restart
```

---

### Problème 2 : Dropdown ne s'affiche pas

**Symptôme** : Saisie texte, aucun résultat affiché

**Solutions** :
1. **Vérifier console** : Erreur réseau ? (F12 → Network)
2. **Vérifier URL API** : `url: 'Autocompl_xxx.php'` correct ?
3. **Vérifier minChars** : Par défaut 2 caractères minimum
4. **Vérifier format réponse API** :
   - Format attendu : `ligne1\nligne2\nligne3` (texte brut, séparé par `\n`)
   - Si JSON, adapter `vanilla-autocomplete.js` (ligne 245)

**Debug** :
```javascript
vanillaAutocomplete('#test', 'Autocompl_equipe.php', {
    onSelect: function(data, index) {
        console.log('Sélectionné:', data, index);
    }
});

// Console → Network → Vérifier requête API
```

---

### Problème 3 : Callback `onSelect` pas appelé

**Symptôme** : Sélection résultat ne déclenche pas action

**Causes** :
1. ❌ Oublié de remplacer `.result(fn)` par `onSelect: fn`
2. ❌ Erreur JavaScript dans callback

**Solution** :
```javascript
// AVANT (jQuery)
.result(function(event, data, formatted) {
    // ...
})

// APRÈS (Vanilla)
onSelect: function(data, index) {
    // ...
}
```

**⚠️ Important** : Paramètres différents :
- jQuery : `(event, data, formatted)`
- Vanilla : `(data, index)`

---

### Problème 4 : Formatage HTML cassé

**Symptôme** : Résultats affichés sans style/layout

**Cause** : HTML injecté via `formatItem` s'affiche mal

**Solution** :
```javascript
formatItem: function(row, i, n) {
    // HTML complexe OK (tables, spans, etc.)
    return "<table width=100%><tr><td>" + row[0] + "</td></tr></table>";
}
```

**Si problème persiste** : Vérifier CSS `vanilla-autocomplete-item` appliqué

---

### Problème 5 : Données array vs string

**Symptôme** : `data[0]` ou `data[1]` undefined

**Cause** : API retourne string simple, pas array

**Solution API PHP** :

```php
// AVANT (si simple string)
echo "Équipe1\nÉquipe2\n";

// APRÈS (si besoin array - séparateur |)
echo "id1|Équipe1\nid2|Équipe2\n";

// Dans vanilla-autocomplete.js, parser :
const results = data.split('\n').map(line => {
    return line.split('|'); // Retourne array [id, nom]
}).filter(arr => arr.length > 0);
```

**Ou modifier format** dans JavaScript (ligne 245 de `vanilla-autocomplete.js`) :

```javascript
// Parser résultats custom
const results = data.split('\n').map(line => {
    const parts = line.split('|');
    return { id: parts[0], name: parts[1] };
}).filter(item => item.id);
```

---

## 🔧 Options Avancées

### Personnalisation CSS

Ajouter dans `sources/css/GestionStyle.css` :

```css
/* Vanilla Autocomplete Custom Styles */
.vanilla-autocomplete-dropdown {
    border-radius: 4px;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.vanilla-autocomplete-item {
    transition: background-color 0.2s;
}

.vanilla-autocomplete-item:hover {
    background-color: #f5f5f5 !important;
}

.vanilla-autocomplete-item.selected {
    background-color: #e0e0e0 !important;
    font-weight: bold;
}
```

---

### Debounce Custom

Par défaut : 300ms. Ajuster si nécessaire :

```javascript
vanillaAutocomplete('#input', 'api.php', {
    delay: 500 // 500ms (plus lent, moins de requêtes)
});
```

---

### Paramètres Additionnels API

```javascript
vanillaAutocomplete('#input', 'Autocompl_equipe.php', {
    extraParams: {
        saison: '2024-2025',
        categorie: 'senior'
    }
});

// Requête : Autocompl_equipe.php?q=text&limit=50&saison=2024-2025&categorie=senior
```

---

### Cache Désactivé

```javascript
vanillaAutocomplete('#input', 'api.php', {
    cacheLength: 0 // Désactiver cache
});
```

---

## 📊 Comparaison Performance

| Critère | jQuery Autocomplete | Vanilla Autocomplete |
|---------|---------------------|----------------------|
| **Taille JS** | 15 KB (autocomplete) + 90 KB (jQuery) = **105 KB** | **12 KB** (standalone) |
| **Dépendances** | jQuery 1.5.2 requis | ✅ Aucune |
| **API moderne** | ❌ $.ajax (obsolète) | ✅ fetch() (natif) |
| **Debounce** | ❌ Non (requêtes excessives) | ✅ Oui (300ms) |
| **Cache** | ✅ Oui | ✅ Oui (Map moderne) |
| **Abort requêtes** | ❌ Non | ✅ AbortController |
| **Navigation clavier** | ✅ Oui | ✅ Oui (↑↓ Enter Escape) |
| **Accessible** | 🟡 Limité | ✅ ARIA-ready |
| **Mobile** | 🟡 Acceptable | ✅ Touch-optimisé |

**Gain total** : **-93 KB** (-88%)

---

## 📚 Ressources

- **Vanilla Autocomplete Source** : `sources/js/vanilla-autocomplete.js`
- **API Fetch MDN** : https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API
- **Debounce Pattern** : https://davidwalsh.name/javascript-debounce-function
- **AbortController** : https://developer.mozilla.org/en-US/docs/Web/API/AbortController

---

## ✅ Exemple Complet : Migration GestionEquipe.js

### AVANT (jQuery Autocomplete)

```javascript
var jq = $; // jQuery 1.5.2 alias

jq(document).ready(function() {
    jq("#choixEquipe").autocomplete('Autocompl_equipe.php', {
        width: 550,
        max: 50,
        matchSubset: true,
        cacheLength: 10,
        formatItem: function(row, i, n) {
            return "<table width=100%><tr><td width=20%>" + row[0] + "</td><td>" + row[1] + "</td></tr></table>";
        },
        formatMatch: function(row, i, n) {
            return row[1] + row[0];
        },
        formatResult: function(row) {
            return row[1];
        }
    }).result(function(event, data, formatted) {
        document.forms[0].Id_Equipe.value = data[0];
        SelectEquipe(data[0]);
    });
});
```

### APRÈS (Vanilla JS)

```javascript
// Pas de jQuery requis !

document.addEventListener('DOMContentLoaded', function() {
    vanillaAutocomplete('#choixEquipe', 'Autocompl_equipe.php', {
        width: 550,
        maxResults: 50,
        matchSubset: true,
        cacheLength: 10,
        formatItem: function(row, i, n) {
            return "<table width=100%><tr><td width=20%>" + row[0] + "</td><td>" + row[1] + "</td></tr></table>";
        },
        formatMatch: function(row, i, n) {
            return row[1] + row[0];
        },
        formatResult: function(row) {
            return row[1];
        },
        onSelect: function(data, index) {
            document.forms[0].Id_Equipe.value = data[0];
            SelectEquipe(data[0]);
        }
    });
});
```

**Changements** :
1. ✅ Supprimer `var jq = $;`
2. ✅ `jq(document).ready()` → `document.addEventListener('DOMContentLoaded')`
3. ✅ `jq("#choixEquipe").autocomplete()` → `vanillaAutocomplete('#choixEquipe')`
4. ✅ `max: 50` → `maxResults: 50`
5. ✅ `.result(fn)` → `onSelect: fn`

---

**Auteur** : Laurent Garrigue / Claude Code
**Date** : 3 novembre 2025
**Version** : 1.0
**Statut** : ✅ **PRÊT POUR MIGRATION**
