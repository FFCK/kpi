# Mise à Jour des Templates - Migration Axios → fetch()

**Date**: 1er novembre 2025
**Statut**: ✅ Migration code terminée - Templates à mettre à jour

---

## ✅ Migration Terminée

**9 fichiers JavaScript migrés avec succès** :
- `sources/js/voie.js` - 2 appels
- `sources/live/js/score.js` - 1 appel
- `sources/live/js/score_o.js` - 1 appel
- `sources/live/js/score_club.js` - 1 appel
- `sources/live/js/score_club_o.js` - 1 appel
- `sources/live/js/multi_score.js` - 1 appel
- `sources/live/js/match.js` - 4 appels
- `sources/live/js/tv.php` - 1 appel
- `sources/live/js/voie_ax.js` - 4 appels

**Total** : 16 appels `axios()` → `axiosLikeFetch()` ✅

**Backup créé** : `backups/axios_migration_20251101_121859/`

---

## 📝 Fichiers Templates à Mettre à Jour

### Fichiers Chargeant Axios (10 fichiers)

1. `sources/smarty/templates/frame_page.tpl:82`
2. `sources/smarty/templates/kppagewide.tpl:71`
3. `sources/smarty/templates/kpterrains.tpl:707`
4. `sources/live/next_game_club.php:86`
5. `sources/live/next_game.php:86`
6. `sources/live/page.php:239`
7. `sources/live/score_club_e.php:74`
8. `sources/live/score_club_o.php:94`
9. `sources/live/score_club.php:103`
10. `sources/live/score_club_s.php:74`

---

## 🔧 Modifications à Effectuer

### Principe

**Remplacer** :
```html
<script type="text/javascript" src="js/axios/axios.min.js?v={$NUM_VERSION}"></script>
```

**Par** :
```html
<script type="text/javascript" src="js/fetch-utils.js?v={$NUM_VERSION}"></script>
```

---

### Modification 1 : frame_page.tpl (ligne 82)

**Fichier** : `sources/smarty/templates/frame_page.tpl`

**Avant** :
```smarty
<script type="text/javascript" src="js/axios/axios.min.js?v={$NUM_VERSION}"></script>
```

**Après** :
```smarty
<script type="text/javascript" src="js/fetch-utils.js?v={$NUM_VERSION}"></script>
```

---

### Modification 2 : kppagewide.tpl (ligne 71)

**Fichier** : `sources/smarty/templates/kppagewide.tpl`

**Avant** :
```smarty
<script type="text/javascript" src="js/axios/axios.min.js?v={$NUM_VERSION}"></script>
```

**Après** :
```smarty
<script type="text/javascript" src="js/fetch-utils.js?v={$NUM_VERSION}"></script>
```

---

###Modification 3 : kpterrains.tpl (ligne 707)

**Fichier** : `sources/smarty/templates/kpterrains.tpl`

**Avant** :
```smarty
<script type="text/javascript" src="js/axios/axios.min.js?v={$NUM_VERSION}"></script>
```

**Après** :
```smarty
<script type="text/javascript" src="js/fetch-utils.js?v={$NUM_VERSION}"></script>
```

---

### Modifications 4-10 : Fichiers Live PHP

**Fichiers concernés** :
- `sources/live/next_game_club.php` (ligne 86)
- `sources/live/next_game.php` (ligne 86)
- `sources/live/page.php` (ligne 239)
- `sources/live/score_club_e.php` (ligne 74)
- `sources/live/score_club_o.php` (ligne 94)
- `sources/live/score_club.php` (ligne 103)
- `sources/live/score_club_s.php` (ligne 74)

**Avant** :
```php
<script type="text/javascript" src="../js/axios/axios.min.js?v=5.3.8"></script>
```

**Après** :
```php
<script type="text/javascript" src="../js/fetch-utils.js?v=5.3.8"></script>
```

---

### Modification Spéciale : tv.php

**Fichier** : `sources/live/tv.php`

**Ajouter AVANT les scripts existants** (après Bootstrap/jQuery) :
```php
<script type="text/javascript" src="./js/fetch-utils.js"></script>
```

**Exemple complet** :
```php
<!-- Charger fetch-utils AVANT les scripts utilisant Axios -->
<script type="text/javascript" src="./js/fetch-utils.js"></script>
<script type="text/javascript" src="./js/voie.js"></script>
<script type="text/javascript" src="./js/tv.js"></script>
```

---

## 🧪 Tests Après Mise à Jour

### Checklist de Tests

#### 1. Page TV Live
- [ ] URL : `https://kpi.localhost/live/tv.php`
- [ ] Affichage des scores
- [ ] Mise à jour automatique (toutes les 3 secondes)
- [ ] Aucune erreur console (F12)

#### 2. Pages frame_page
- [ ] URL : Toutes pages utilisant `frame_page.tpl`
- [ ] Fonctionnalités utilisant voie.js
- [ ] Aucune erreur `axiosLikeFetch is not defined`

#### 3. Pages kppagewide
- [ ] URL : Pages larges (kppagewide.tpl)
- [ ] Fonctionnalités AJAX
- [ ] Console JavaScript propre

#### 4. Pages Live Scores
- [ ] `score_club.php`
- [ ] `score_club_o.php`
- [ ] `score_club_e.php`
- [ ] `score_club_s.php`
- [ ] Affichage temps réel
- [ ] Mise à jour automatique

#### 5. Vérifications Générales
- [ ] Aucune erreur JavaScript (F12 > Console)
- [ ] Aucun 404 sur `axios.min.js` (normal après migration)
- [ ] Aucun 404 sur `fetch-utils.js` (vérifier chargement)
- [ ] Fonctionnalités Live Scores fonctionnelles

---

## 🔄 Rollback (Si Problème)

### Restaurer les Fichiers JavaScript

```bash
# Restaurer depuis backup
cp -r backups/axios_migration_20251101_121859/* .

# Vérifier
git status
```

### Restaurer les Templates

Si vous avez modifié les templates et que vous devez revenir en arrière :

```bash
# Annuler modifications templates (si non commitées)
git checkout sources/smarty/templates/frame_page.tpl
git checkout sources/smarty/templates/kppagewide.tpl
git checkout sources/smarty/templates/kpterrains.tpl
git checkout sources/live/*.php
```

---

## 🗑️ Nettoyage Final (Après Validation 48h)

### Supprimer Axios (Ne Plus Revenir en Arrière)

```bash
# 1. Supprimer fichiers Axios
rm sources/js/axios/axios.min.js
rm sources/js/axios/axios.min.map
rmdir sources/js/axios

# 2. Vérifier qu'aucune référence ne reste
grep -r "axios\.min\.js" sources/

# 3. Supprimer backup (optionnel, après plusieurs jours)
# rm -rf backups/axios_migration_20251101_121859

# 4. Commit
git add -A
git commit -m "feat: Migration complète Axios → fetch() natif

- Suppression dépendance Axios (20 KB économisés)
- Migration 9 fichiers JS vers fetch() natif
- Correction 3 CVE critiques (CVSS 5.9-7.5)
- 16 appels axios() → axiosLikeFetch()
- 0 dépendance externe pour HTTP requests
- fetch-utils.js: wrapper compatible Axios

🤖 Generated with Claude Code
Co-Authored-By: Claude <noreply@anthropic.com>"
```

---

## 📊 Résumé Migration

### Avant Migration
- **Dépendance** : Axios 0.24.0 (20 KB)
- **CVE** : 3 critiques (CVSS 5.9-7.5)
- **Maintenance** : Externe, à surveiller

### Après Migration
- **Dépendance** : fetch() natif (0 KB)
- **CVE** : 0
- **Maintenance** : Aucune (natif navigateur)

### Gains
- ✅ **Sécurité** : 3 CVE éliminées
- ✅ **Performance** : 20 KB + 1 requête HTTP économisés
- ✅ **Simplicité** : 0 dépendance externe
- ✅ **Pérennité** : Standard Web (fetch API)

---

**Auteur**: Laurent Garrigue / Claude Code
**Date**: 1er novembre 2025
**Version**: 1.0
**Statut**: ✅ **PRÊT POUR MISE À JOUR TEMPLATES**
