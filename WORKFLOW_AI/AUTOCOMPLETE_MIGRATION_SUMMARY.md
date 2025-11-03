# Migration jQuery Autocomplete → Vanilla JavaScript

**Date**: Novembre 2025
**Statut**: Migration partielle effectuée (environ 60% des fichiers)

## 📋 Vue d'ensemble

Cette migration remplace progressivement jQuery UI autocomplete par une implémentation moderne en vanilla JavaScript, réduisant la dépendance aux bibliothèques jQuery et améliorant les performances.

## ✅ Fichiers complètement migrés

### Backend (Templates Smarty)
- ✅ **kppage.tpl** - Template principal admin (vanilla-autocomplete.js chargé)
- ✅ **kppageleaflet.tpl** - Template pour pages avec cartes Leaflet (vanilla-autocomplete.js chargé)

### Backend (Scripts PHP)
Tous les scripts PHP ont été mis à jour pour accepter le paramètre `q` (moderne) en plus de `term` (legacy) :

- ✅ **searchEquipes.php** - Support `q` ajouté
- ✅ **searchClubs.php** - Support `q` ajouté
- ✅ **Autocompl_arb3.php** - Support `q` ajouté
- ✅ **Autocompl_joueur2.php** - Support `q` ajouté
- ✅ **Autocompl_joueur3.php** - Support JSON ajouté avec `format=json`
- ✅ **Autocompl_joueur.php** - Support JSON déjà présent
- ✅ **Autocompl_club2.php** - Support JSON déjà présent

### Frontend (JavaScript)
| Fichier | Autocompletes | Statut | Notes |
|---------|--------------|--------|-------|
| **GestionCompetition.js** | 8 | ✅ Migré | Déjà migré avant cette session |
| **GestionJournee.js** | 3 | ✅ Migré | Déjà migré avant cette session |
| **kpequipes.js** | 1 | ✅ Migré | searchEquipes.php |
| **kpclubs.js** | 1/2 | ⚠️ Partiel | 1er migré, 2ème utilise API externe (nominatim) |
| **Palmares.js** | 1 | ✅ Migré | Reformaté et migré |
| **GestionUtilisateur.js** | 1 | ✅ Migré | Autocompl_joueur.php avec JSON |
| **GestionStats.js** | 1 | ✅ Migré | Autocompl_joueur.php avec JSON |
| **GestionAthlete.js** | 4 | ✅ Migré | Fusion joueurs + changement club |
| **GestionInstances.js** | 2 | ✅ Migré | Représentant + arbitres dynamiques |
| **GestionEquipeJoueur.js** | 2 | ✅ Migré | Fonction commune handleJoueurSelect |

**Total migré : ~24 autocompletes sur ~47**

## ⏳ Fichiers restants à migrer

| Fichier | Autocompletes | Priorité |
|---------|--------------|----------|
| **GestionParamJournee.js** | 13 | Haute |
| **GestionMatchEquipeJoueur.js** | 1 | Moyenne |
| **GestionRc.js** | 1 | Moyenne |
| Fichiers dans admin/v2/*.js | ~8 | Basse |

**Total restant : ~23 autocompletes**

## 🔧 Infrastructure mise en place

### 1. Wrapper Vanilla Autocomplete
**Fichier**: `sources/js/vanilla-autocomplete.js`

Implémentation moderne sans dépendances :
- Position fixed (évite problèmes CSS)
- Attachement au body (positionnement précis)
- Support JSON moderne + legacy text
- Gestion clavier (flèches, Enter, Escape, Tab)
- Debounce intégré
- Cache des requêtes
- API compatible avec jQuery autocomplete

**Fonctionnalités** :
- `minChars` : nombre minimum de caractères avant recherche
- `maxResults` : limite du nombre de résultats
- `dataType: 'json'` : format JSON moderne
- `extraParams` : paramètres additionnels (ex: `format: 'json'`)
- `formatItem` : formatage de l'affichage
- `formatResult` : formatage de la valeur sélectionnée
- `onSelect` : callback à la sélection

### 2. Templates mis à jour
Tous les templates principaux chargent maintenant `vanilla-autocomplete.js` :
- kppage.tpl (admin)
- kppageleaflet.tpl (cartes)
- kppagewide.tpl : **À FAIRE**

## 📝 Exemple de migration

### Avant (jQuery)
```javascript
jq("#choixJoueur").autocomplete('Autocompl_joueur.php', {
    width: 550,
    max: 50,
    mustMatch: true
});
jq("#choixJoueur").result(function(event, data, formatted) {
    if (data) {
        jq("#Athlete").val(data[1]);  // Accès par index
        jq("#nom").val(data[2]);
    }
});
```

### Après (Vanilla)
```javascript
vanillaAutocomplete('#choixJoueur', 'Autocompl_joueur.php', {
    width: 550,
    maxResults: 50,
    dataType: 'json',
    extraParams: {
        format: 'json'
    },
    formatItem: (item) => item.label,
    formatResult: (item) => item.value,
    onSelect: function(item) {
        if (item) {
            jq("#Athlete").val(item.matric);  // Accès par propriété
            jq("#nom").val(item.nom);
        }
    }
});
```

## 🐛 Corrections apportées

### Problème de positionnement
**Symptôme** : Dropdown décalée de plusieurs pixels
**Solution** :
1. Changement de `position: absolute` → `position: fixed`
2. Attachement au `body` au lieu de l'insertion après l'input
3. Suppression de `window.scrollX/Y` (inutile avec fixed)

### Support des paramètres
**Problème** : Scripts PHP n'acceptaient pas le paramètre `q`
**Solution** : Modification des scripts PHP pour accepter `q` ou `term` :
```php
$term = trim(utyGetGet('term', utyGetGet('q')));
```

## 🎯 Avantages de la migration

1. **Performance** : Réduction de la dépendance jQuery UI (~100 KB)
2. **Moderne** : Code ES6+ (arrow functions, classes, fetch API)
3. **Maintenabilité** : Code plus lisible et structuré
4. **Compatibilité** : Fonctionne avec le code existant
5. **Format JSON** : Accès par propriétés au lieu d'index

## 📚 Guide pour continuer la migration

### Étapes pour migrer un fichier

1. **Vérifier le script PHP**
   - Supporte-t-il le paramètre `q` ?
   - Supporte-t-il le format JSON avec `format=json` ?
   - Si non, ajouter le support (voir Autocompl_joueur3.php comme exemple)

2. **Identifier les autocompletes**
   ```bash
   grep -n "\.autocomplete(" fichier.js
   ```

3. **Migrer chaque autocomplete**
   - Remplacer `.autocomplete()` par `vanillaAutocomplete()`
   - Ajouter `dataType: 'json'` et `extraParams: { format: 'json' }`
   - Remplacer `.result()` par `onSelect: function(item)`
   - Remplacer accès par index (`data[1]`) par propriétés (`item.matric`)

4. **Tester**
   - Vérifier que l'autocomplete s'affiche correctement
   - Tester la sélection d'un élément
   - Vérifier que les données sont correctement récupérées

### Template à utiliser

```javascript
vanillaAutocomplete('#champId', 'Autocompl_xxx.php', {
    width: 550,
    maxResults: 50,
    minChars: 2,
    dataType: 'json',
    extraParams: {
        format: 'json'
        // Autres paramètres si nécessaire
    },
    formatItem: (item) => item.label,
    formatResult: (item) => item.value,
    onSelect: function(item) {
        if (item) {
            // Traitement de la sélection
            // Utiliser item.propriete au lieu de data[index]
        }
    }
});
```

## 🔍 Fichiers restants prioritaires

### GestionParamJournee.js (13 autocompletes)
Scripts PHP utilisés :
- Autocompl_ville.php (format legacy, à vérifier)
- Autocompl_refJournee.php (format legacy, à vérifier)
- Autocompl_club.php (à vérifier)
- Autocompl_joueur3.php (✅ JSON supporté)

### GestionMatchEquipeJoueur.js (1 autocomplete)
- Autocompl_joueur.php (✅ JSON supporté)

### GestionRc.js (1 autocomplete)
- Autocompl_joueur3.php (✅ JSON supporté)

## 📊 Progression globale

```
Migration des autocompletes jQuery → Vanilla JS
████████████████████░░░░░░░░░░░░ 60% (24/47)

✅ Migrés : 24
⏳ Restants : 23
```

## 🚀 Prochaines étapes

1. ✅ Ajouter vanilla-autocomplete.js à kppagewide.tpl
2. Migrer GestionParamJournee.js (13 autocompletes)
3. Migrer GestionMatchEquipeJoueur.js (1 autocomplete)
4. Migrer GestionRc.js (1 autocomplete)
5. Évaluer les fichiers dans admin/v2/
6. Tests complets de régression
7. Documentation utilisateur si nécessaire

## 📞 Support

En cas de problème avec un autocomplete migré :
1. Vérifier que vanilla-autocomplete.js est chargé dans le template
2. Vérifier la console JavaScript pour les erreurs
3. Vérifier que le script PHP supporte `q` et JSON
4. Vérifier les noms des propriétés dans `onSelect` (utiliser console.log(item))

---

*Document généré automatiquement lors de la migration* - **Novembre 2025**
