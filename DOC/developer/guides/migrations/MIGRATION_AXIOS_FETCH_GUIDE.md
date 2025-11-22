# Guide Rapide : Migration Axios → fetch()

**Date**: 1er novembre 2025
**Durée estimée**: 2-4 heures (migration + tests)
**Risque**: 🟡 FAIBLE

---

## ⚡ Installation Rapide (5 minutes)

### Étape 1 : Exécuter le Script de Migration

```bash
# Depuis la racine du projet
./migrate_axios_to_fetch.sh
```

Le script va :
1. ✅ Créer des backups de tous les fichiers
2. ✅ Remplacer `axios(` par `axiosLikeFetch(` dans 9 fichiers
3. ✅ Afficher un résumé de la migration

---

### Étape 2 : Charger fetch-utils.js dans les Templates

#### A. Page Admin (page.tpl)

**Fichier** : `sources/smarty/templates/page.tpl`

```smarty
{* Ajouter AVANT les autres scripts JS *}
<script src="js/fetch-utils.js"></script>
<script src="js/jquery-1.5.2.min.js"></script>
```

**Ligne** : Après les CSS, avant les autres scripts

---

#### B. Page TV Live (tv.php)

**Fichier** : `sources/live/tv.php`

```html
<!-- Ajouter dans la section <head> -->
<script src="js/fetch-utils.js"></script>
```

**Ligne** : Avant les autres scripts live

---

### Étape 3 : Tester (30 minutes)

**Checklist de tests** :

- [ ] Page TV Live : `https://kpi.localhost/live/tv.php`
  - Affichage des scores
  - Mise à jour automatique (toutes les 3 secondes)
  - Aucune erreur console JavaScript (F12)

- [ ] Pages Admin utilisant Live Scores
  - Gestion Match
  - Tableau des scores
  - Chronomètre en direct

- [ ] Console JavaScript
  - F12 > Console
  - Vérifier : aucune erreur `axiosLikeFetch is not defined`
  - Vérifier : aucune erreur `ReferenceError`

---

### Étape 4 : Validation Production (48h)

Après déploiement en production :

1. **Surveiller les logs** (48 heures)
2. **Vérifier les fonctionnalités Live Scores**
3. **Pas d'erreur remontée** → Migration réussie ✅

---

## 🔧 Rollback (Si Problème)

### Restaurer les Backups

```bash
# Trouver le backup
ls -la backups/axios_migration_*

# Restaurer (remplacer YYYYMMDD_HHMMSS par la date du backup)
cp -r backups/axios_migration_YYYYMMDD_HHMMSS/* .

# Vérifier
git status
```

**Durée rollback** : ~2 minutes

---

## 🗑️ Nettoyage Final (Après Validation)

### Supprimer Axios (Ne plus revenir en arrière)

```bash
# Supprimer fichiers Axios
rm sources/js/axios/axios.min.js
rm sources/js/axios/axios.min.map
rmdir sources/js/axios

# Supprimer chargement Axios dans templates
grep -r "axios.min.js" sources/smarty/templates/*.tpl
# Commenter/supprimer les lignes trouvées

# Commit
git add -A
git commit -m "feat: Migration Axios → fetch() natif

- Suppression dépendance Axios (20 KB)
- Migration vers fetch() natif
- Correction 3 CVE critiques
- 9 fichiers migrés (Live Scores)

🤖 Generated with Claude Code
Co-Authored-By: Claude <noreply@anthropic.com>"
```

---

## 📊 Avant/Après

### Avant Migration

```javascript
// sources/live/js/score.js
axios({
    method: 'post',
    url: './get_sec.php',
    responseType: 'text'
})
.then(function (response) {
    var temps = parseInt(response.data)
})
```

**Dépendances** :
- Axios 0.24.0 (20 KB)
- 3 CVE critiques

---

### Après Migration

```javascript
// sources/live/js/score.js
axiosLikeFetch({
    method: 'post',
    url: './get_sec.php',
    responseType: 'text'
})
.then(function (response) {
    var temps = parseInt(response.data)
})
```

**Dépendances** :
- fetch() natif (0 KB)
- 0 CVE

**Changement** : 1 mot (`axios` → `axiosLikeFetch`)

---

## 🎯 Checklist Complète

### Avant Migration

- [ ] Lecture de AXIOS_TO_FETCH_MIGRATION.md
- [ ] Backup manuel effectué (optionnel, script le fait)
- [ ] Environnement de test disponible

### Pendant Migration

- [ ] Script `migrate_axios_to_fetch.sh` exécuté
- [ ] Aucune erreur dans l'output du script
- [ ] Backup créé automatiquement
- [ ] fetch-utils.js chargé dans page.tpl
- [ ] fetch-utils.js chargé dans tv.php

### Après Migration

- [ ] Page TV Live testée
- [ ] Scores temps réel fonctionnels
- [ ] Console JavaScript propre (F12)
- [ ] Aucune erreur `axiosLikeFetch is not defined`
- [ ] Mise à jour automatique fonctionne

### Validation Production

- [ ] Déploiement production réussi
- [ ] Monitoring 48h sans erreur
- [ ] Fonctionnalités Live Scores validées
- [ ] Axios supprimé (nettoyage final)

---

## 🚨 Problèmes Courants

### Problème 1 : `axiosLikeFetch is not defined`

**Symptôme** :
```
Uncaught ReferenceError: axiosLikeFetch is not defined
```

**Cause** : `fetch-utils.js` non chargé ou chargé après les scripts utilisant la fonction

**Solution** :
```html
<!-- Charger fetch-utils.js EN PREMIER -->
<script src="js/fetch-utils.js"></script>
<script src="js/voie.js"></script>
```

---

### Problème 2 : Scores ne se mettent plus à jour

**Symptôme** : Affichage figé, pas de mise à jour automatique

**Cause** : Erreur JavaScript bloquant l'exécution

**Solution** :
1. Ouvrir console (F12)
2. Identifier l'erreur
3. Vérifier que fetch-utils.js est bien chargé
4. Si nécessaire, rollback (voir section Rollback)

---

### Problème 3 : Erreur HTTP non gérée

**Symptôme** : Erreur `HTTP 404` ou `HTTP 500` dans la console

**Cause** : Gestion d'erreur HTTP maintenant active (fetch vérifie `response.ok`)

**Solution** : C'est **normal et souhaitable** ! Axios 0.24.0 masquait ces erreurs.

Si besoin de les ignorer temporairement :
```javascript
// Dans fetch-utils.js, commenter temporairement:
// if (!response.ok) {
//     throw new Error('HTTP ' + response.status)
// }
```

---

## 📚 Ressources

### Documentation Complète
- [AXIOS_TO_FETCH_MIGRATION.md](WORKFLOW_AI/AXIOS_TO_FETCH_MIGRATION.md) - Analyse détaillée

### Code Source
- `sources/js/fetch-utils.js` - Fonction wrapper
- `migrate_axios_to_fetch.sh` - Script de migration

### Tests
- `sources/live/tv.php` - Page principale à tester
- `sources/live/js/*.js` - 8 fichiers migrés

---

## ✅ Résumé

**Migration en 4 étapes** :
1. ⚡ Exécuter `migrate_axios_to_fetch.sh` (5 min)
2. 📝 Charger `fetch-utils.js` dans templates (5 min)
3. 🧪 Tester Live Scores (30 min)
4. ✅ Valider production (48h)

**Gains** :
- ✅ 0 CVE (vs 3 CVE critiques)
- ✅ 20 KB économisés
- ✅ 0 maintenance future
- ✅ Code plus moderne

**Risque** : 🟡 FAIBLE (usage simple, rollback rapide)

---

**Auteur**: Laurent Garrigue / Claude Code
**Date**: 1er novembre 2025
**Version**: 1.0
**Statut**: ✅ **PRÊT POUR EXÉCUTION**
