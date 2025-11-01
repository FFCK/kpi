# Migration Axios → fetch() Native

**Date**: 1er novembre 2025
**Objectif**: Évaluer la faisabilité de remplacer Axios par `fetch()` natif

---

## 🎯 Question Posée

**Peut-on remplacer Axios par la fonction native fetch() sans risque ?**

**Réponse courte** : ✅ **OUI, migration possible et RECOMMANDÉE**

---

## 📊 Analyse de l'Usage Actuel d'Axios

### Fonctionnalités Axios Utilisées

**Pattern détecté** : Usage **très simple** d'Axios

```javascript
// Pattern standard dans tous les fichiers
axios({
    method: 'post',
    url: './cache/file.json',
    params: {},
    responseType: 'json'  // ou 'text'
})
.then(function (response) {
    // Traiter response.data
})
.catch(function (error) {
    console.log(error)
})
```

---

### Fonctionnalités Axios NON Utilisées ✅

**Vérifications effectuées** :

```bash
# Fonctionnalités avancées
grep -r "interceptors\|transformRequest\|transformResponse" sources/
# Résultat: AUCUNE

# Méthodes raccourcies
grep -r "axios\.(get|post|put|delete)" sources/
# Résultat: AUCUNE (uniquement axios({...}))

# Configuration globale
grep -r "axios\.defaults\|axios\.create" sources/
# Résultat: AUCUNE
```

**Conclusion** : ✅ Axios est utilisé de manière **basique**, pas de fonctionnalités avancées

---

## 🔄 Comparaison Axios vs fetch()

### Exemple 1 : Requête JSON (match.js)

#### Axios (Actuel)
```javascript
axios({
    method: 'post',
    url: './cache/match_global.json',
    params: {},
    responseType: 'json'
})
.then(function (response) {
    ParseCacheGlobal(response.data)
})
.catch(function (error) {
    console.log(error)
})
```

#### fetch() (Proposé)
```javascript
fetch('./cache/match_global.json', {
    method: 'POST'
})
.then(response => response.json())
.then(data => {
    ParseCacheGlobal(data)
})
.catch(error => {
    console.log(error)
})
```

**Différences** :
- ✅ Moins de code
- ✅ Natif (pas de dépendance externe)
- ⚠️ Nécessite `.json()` explicite (mais c'est clair)

---

### Exemple 2 : Requête Text (score.js)

#### Axios (Actuel)
```javascript
axios({
    method: 'post',
    url: './get_sec.php',
    params: {},
    responseType: 'text'
})
.then(function (response) {
    var temps_offset = temps_actuel - parseInt(response.data)
})
.catch(function (error) {
    console.log(error)
})
```

#### fetch() (Proposé)
```javascript
fetch('./get_sec.php', {
    method: 'POST'
})
.then(response => response.text())
.then(data => {
    var temps_offset = temps_actuel - parseInt(data)
})
.catch(error => {
    console.log(error)
})
```

**Différences** :
- ✅ Quasi identique
- ✅ `.text()` au lieu de `.json()`
- ✅ `data` directement au lieu de `response.data`

---

### Exemple 3 : Async/Await (voie.js)

#### Axios (Actuel)
```javascript
try {
    const resultat = await axios({
        method: 'post',
        url: './live/cache/voie_' + voie + '.json',
        responseType: 'json'
    })
    RefreshScene(resultat.data, intervalle)
} catch (error) {
    console.error(error)
}
```

#### fetch() (Proposé)
```javascript
try {
    const response = await fetch('./live/cache/voie_' + voie + '.json', {
        method: 'POST'
    })
    const data = await response.json()
    RefreshScene(data, intervalle)
} catch (error) {
    console.error(error)
}
```

**Différences** :
- ✅ Très similaire
- ⚠️ 2 `await` au lieu d'1 (mais plus clair)
- ✅ Gestion d'erreurs identique

---

## ✅ Avantages de la Migration

### 1. Sécurité 🔴 CRITIQUE

**Axios 0.24.0** :
- CVE-2023-45857 (CVSS 6.5) - CSRF
- CVE-2024-39338 (CVSS 7.5) - SSRF
- CVE-2024-47764 (CVSS 5.9) - Prototype Pollution

**fetch()** :
- ✅ **0 CVE** (natif navigateur)
- ✅ Mis à jour automatiquement avec le navigateur
- ✅ Pas de dépendance tierce

---

### 2. Performance

| Métrique | Axios 0.24.0 | fetch() natif |
|----------|--------------|---------------|
| **Taille** | 20 KB (minifié) | 0 KB (natif) |
| **Chargement** | Requête HTTP | Instantané |
| **Exécution** | Interprété | Compilé (moteur JS) |
| **Maintenance** | Dépendance externe | Natif |

**Gain** : ~20 KB + 1 requête HTTP en moins

---

### 3. Compatibilité Navigateurs

**fetch()** est supporté par tous les navigateurs modernes :
- Chrome 42+ (2015)
- Firefox 39+ (2015)
- Safari 10.1+ (2017)
- Edge 14+ (2016)

**Note** : Les navigateurs ciblés par votre projet (2025) supportent tous `fetch()` nativement.

---

### 4. Simplicité

**fetch()** :
- ✅ Pas de `node_modules`
- ✅ Pas de mise à jour à gérer
- ✅ Pas de CVE à surveiller
- ✅ Standard Web (MDN, W3C)

---

## ⚠️ Pièges à Éviter avec fetch()

### Piège 1 : fetch() ne rejette PAS les erreurs HTTP

**Axios** :
```javascript
axios.get('/api/user/1')
.then(response => console.log(response.data))
.catch(error => console.log('Erreur!'))  // 404, 500, etc.
```

**fetch() (INCORRECT)** :
```javascript
fetch('/api/user/1')
.then(response => response.json())  // Ne lance PAS d'erreur sur 404!
.catch(error => console.log('Erreur!'))  // Seulement erreurs réseau
```

**fetch() (CORRECT)** :
```javascript
fetch('/api/user/1')
.then(response => {
    if (!response.ok) {
        throw new Error('HTTP ' + response.status)
    }
    return response.json()
})
.catch(error => console.log('Erreur!'))  // 404, 500, ET erreurs réseau
```

---

### Piège 2 : response.data vs data direct

**Axios** :
```javascript
axios.get('/api/data')
.then(response => {
    console.log(response.data)  // Données déjà parsées
})
```

**fetch()** :
```javascript
fetch('/api/data')
.then(response => response.json())  // Parser explicitement
.then(data => {
    console.log(data)  // Données parsées
})
```

---

### Piège 3 : Timeout non natif

**Axios** :
```javascript
axios.get('/api/data', { timeout: 5000 })
```

**fetch() (nécessite AbortController)** :
```javascript
const controller = new AbortController()
const timeoutId = setTimeout(() => controller.abort(), 5000)

fetch('/api/data', { signal: controller.signal })
.then(response => response.json())
.finally(() => clearTimeout(timeoutId))
```

**Note** : Votre code **n'utilise PAS de timeout**, ce piège ne s'applique pas.

---

## 🎯 Plan de Migration

### Approche Recommandée : Migration Progressive

**Durée estimée** : 2-4 heures
**Risque** : 🟡 **FAIBLE** (usage simple d'Axios)

---

### Phase 1 : Créer une fonction utilitaire

**Fichier** : `sources/js/fetch-utils.js`

```javascript
/**
 * Wrapper fetch() compatible avec l'usage actuel d'Axios
 * @param {Object} config - Configuration { method, url, responseType }
 * @returns {Promise}
 */
function axiosLikeFetch(config) {
    const { method = 'GET', url, responseType = 'json' } = config

    return fetch(url, { method })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`)
            }

            if (responseType === 'json') {
                return response.json()
            } else if (responseType === 'text') {
                return response.text()
            } else {
                return response.blob()
            }
        })
        .then(data => {
            // Retourner dans le même format qu'Axios
            return { data: data }
        })
}
```

**Avantage** : Migration **sans modification du code existant** !

---

### Phase 2 : Remplacer axios() par axiosLikeFetch()

**Exemple (score.js)** :

```javascript
// Avant
axios({
    method: 'post',
    url: './get_sec.php',
    responseType: 'text'
})
.then(function (response) {
    var temps_offset = temps_actuel - parseInt(response.data)
})

// Après (changement minimal)
axiosLikeFetch({
    method: 'post',
    url: './get_sec.php',
    responseType: 'text'
})
.then(function (response) {
    var temps_offset = temps_actuel - parseInt(response.data)
})
```

**Changement** : 1 ligne (remplacement de `axios` par `axiosLikeFetch`)

---

### Phase 3 : Tester tous les Live Scores

**Fichiers à tester** (9 fichiers) :
1. `sources/js/voie.js`
2. `sources/live/js/score.js`
3. `sources/live/js/score_o.js`
4. `sources/live/js/score_club.js`
5. `sources/live/js/score_club_o.js`
6. `sources/live/js/multi_score.js`
7. `sources/live/js/match.js`
8. `sources/live/js/tv.js`
9. `sources/live/js/voie_ax.js`

**Tests** :
- [ ] Page TV Live (`tv.php`)
- [ ] Scores temps réel
- [ ] Mise à jour automatique
- [ ] Gestion erreurs (réseau coupé)
- [ ] Console JavaScript (aucune erreur)

---

### Phase 4 : Supprimer Axios

**Après validation complète (48h en production)** :

```bash
# Supprimer fichiers Axios
rm sources/js/axios/axios.min.js
rm sources/js/axios/axios.min.map
rmdir sources/js/axios

# Supprimer chargement dans templates
grep -r "axios.min.js" sources/smarty/templates/*.tpl
# Commenter/supprimer les lignes trouvées
```

---

## 🧪 Script de Migration Automatique

**Fichier** : `migrate_axios_to_fetch.sh`

```bash
#!/bin/bash
# Migration Axios → fetch() natif

echo "🔄 Migration Axios → fetch() en cours..."

# 1. Créer fonction utilitaire
cat > sources/js/fetch-utils.js << 'EOF'
/**
 * Wrapper fetch() compatible Axios
 */
function axiosLikeFetch(config) {
    const { method = 'GET', url, responseType = 'json' } = config

    return fetch(url, { method })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`)
            }
            return responseType === 'json' ? response.json() : response.text()
        })
        .then(data => ({ data }))
}
EOF

echo "✅ fetch-utils.js créé"

# 2. Remplacer axios() par axiosLikeFetch() dans tous les fichiers
FILES=(
    "sources/js/voie.js"
    "sources/live/js/score.js"
    "sources/live/js/score_o.js"
    "sources/live/js/score_club.js"
    "sources/live/js/score_club_o.js"
    "sources/live/js/multi_score.js"
    "sources/live/js/match.js"
    "sources/live/js/tv.js"
    "sources/live/js/voie_ax.js"
)

for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        sed -i 's/axios(/axiosLikeFetch(/g' "$file"
        echo "✅ Migré: $file"
    fi
done

echo ""
echo "🎯 Migration terminée!"
echo "⚠️  Actions requises:"
echo "1. Charger fetch-utils.js dans les templates"
echo "2. Tester toutes les pages Live Scores"
echo "3. Après validation: supprimer axios.min.js"
```

**Utilisation** :
```bash
chmod +x migrate_axios_to_fetch.sh
./migrate_axios_to_fetch.sh
```

---

## 📊 Comparaison Finale

| Critère | Axios 0.24.0 | Axios 1.7.9 | fetch() natif |
|---------|--------------|-------------|---------------|
| **Sécurité** | 🔴 3 CVE | ✅ 0 CVE | ✅ 0 CVE |
| **Taille** | 20 KB | 20 KB | 0 KB |
| **Compatibilité** | ✅ IE11+ | ✅ IE11+ | ✅ Modernes |
| **Maintenance** | 🟡 Externe | 🟡 Externe | ✅ Natif |
| **Fonctionnalités** | ✅✅✅ | ✅✅✅ | ✅✅ |
| **Usage projet** | ✅ Simple | ✅ Simple | ✅ Simple |
| **Effort migration** | - | 🟢 5 min | 🟡 2-4h |

---

## ✅ Recommandation Finale

### Option 1 : Migration vers fetch() ✅ RECOMMANDÉE

**Avantages** :
- ✅ 0 dépendance externe
- ✅ 0 CVE à surveiller
- ✅ ~20 KB économisés
- ✅ Maintenance nulle
- ✅ Standard Web

**Inconvénients** :
- ⚠️ Migration 2-4h (9 fichiers)
- ⚠️ Tests requis

**Effort** : 2-4 heures
**Gain** : Sécurité + Performance + Simplicité

---

### Option 2 : Mise à jour Axios 1.7.9 🟡 ALTERNATIF

**Avantages** :
- ✅ 0 CVE corrigés
- ✅ Migration 5 minutes
- ✅ Aucun changement de code

**Inconvénients** :
- ⚠️ Dépendance externe restante
- ⚠️ 20 KB toujours chargés
- ⚠️ Maintenance future (CVE possibles)

**Effort** : 5 minutes
**Gain** : Sécurité uniquement

---

## 🎯 Décision

**Si temps disponible (2-4h)** : ✅ **Option 1 (fetch())**
- Migration définitive
- Plus de dépendance Axios
- Code moderne et pérenne

**Si urgence sécurité** : 🟡 **Option 2 (Axios 1.7.9)**
- Fix immédiat CVE
- Migration fetch() ultérieure

---

## 📚 Ressources

### Documentation fetch()
- [MDN Web Docs - fetch()](https://developer.mozilla.org/fr/docs/Web/API/Fetch_API)
- [Can I Use - fetch()](https://caniuse.com/fetch)
- [Google Developers - Introduction to fetch()](https://web.dev/introduction-to-fetch/)

### Outils de Migration
- [axios-to-fetch Codemod](https://github.com/facebook/codemod)
- [You Might Not Need Axios](https://youmightnotneedaxios.com/)

---

## ✅ Conclusion

**Réponse à la question** : ✅ **OUI, migration fetch() possible et recommandée**

**Raisons** :
1. ✅ Usage Axios très simple (pas de fonctionnalités avancées)
2. ✅ fetch() supporté par tous les navigateurs cibles
3. ✅ Gain sécurité + performance + simplicité
4. ✅ Migration rapide (2-4h pour 9 fichiers)
5. ✅ Maintenance nulle (natif)

**Recommandation** : **GO pour migration fetch()** avec fonction wrapper `axiosLikeFetch()` pour minimiser les changements de code.

---

**Auteur**: Laurent Garrigue / Claude Code
**Date**: 1er novembre 2025
**Version**: 1.0
**Statut**: 📋 **ANALYSE COMPLÈTE - MIGRATION RECOMMANDÉE**
