# Actions de nettoyage pré-migration - Quick Wins

**Date**: 19 octobre 2025
**Projet**: KPI Migration
**Objectif**: Nettoyage rapide et sans risque avant migration

---

## ✅ Actions immédiate s - Risque ZÉRO

### 1. Suppression code MySQLi commenté

**Fichier**: `sources/commun/MyBdd.php`
**Lignes à supprimer**: 76-165

#### Commandes
```bash
cd /home/laurent/Documents/dev/kpi

# Backup de sécurité
cp sources/commun/MyBdd.php sources/commun/MyBdd.php.backup

# Éditer le fichier et supprimer les lignes 76-165
# (fonctions Connect, Query, Error, etc. toutes commentées)
```

#### Détail des lignes
```php
// Lignes 76-82:   function Connect() {...}
// Lignes 84-95:   function Query() {...}
// Lignes 97-105:  function Error() {...}
// Lignes 107-111: function AffectedRows() {...}
// Lignes 113-117: function InsertId() {...}
// Lignes 119-123: function NumRows() {...}
// Lignes 125-129: function NumFields() {...}
// Lignes 131-135: function FieldName() {...}
// Lignes 137-141: function FetchArray() {...}
// Lignes 143-147: function FetchAssoc() {...}
// Lignes 149-153: function FetchRow() {...}
// Lignes 155-159: function DataSeek() {...}
// Lignes 161-165: mysqli_real_escape_string (commentaire)
```

**Gain**: -90 lignes de code mort
**Risque**: Aucun (code PDO équivalent actif)
**Temps**: 5 minutes

---

### 2. Suppression anciennes versions FPDF

**Répertoires à supprimer**:
- `sources/lib/fpdf/`
- `sources/lib/fpdf-1.7/`

**À conserver**: `sources/lib/fpdf-1.8.4/` (version latest)

#### Commandes
```bash
cd /home/laurent/Documents/dev/kpi/sources/lib

# Vérifier les versions présentes
ls -la | grep fpdf

# Backup (optionnel)
tar -czf fpdf-old-versions.tar.gz fpdf/ fpdf-1.7/

# Suppression
rm -rf fpdf/
rm -rf fpdf-1.7/

# Vérification
ls -la | grep fpdf
# Doit afficher uniquement: fpdf-1.8.4/
```

**Gain**: ~500 KB
**Risque**: Aucun (43 fichiers utilisent déjà FPDF, pointeront vers 1.8.4)
**Temps**: 2 minutes

---

### 3. Reclassification dépendances Node.js (app2)

**Fichier**: `sources/app2/package.json`

**Problème**: Packages en `dependencies` alors que build est statique (pas de Node.js en prod)

#### Modification
```bash
cd /home/laurent/Documents/dev/kpi/sources/app2

# Backup
cp package.json package.json.backup
```

Éditer `package.json`:

**AVANT**:
```json
{
  "dependencies": {
    "@types/node": "^24.5.2",
    "@vite-pwa/nuxt": "^1.0.4",
    "buffer": "^6.0.3",
    "dayjs": "^1.11.18"
  },
  "devDependencies": {
    "@nuxt/eslint": "^1.9.0",
    "@nuxt/kit": "^4.1.2",
    // ... etc
  }
}
```

**APRÈS**:
```json
{
  "dependencies": {},
  "devDependencies": {
    "@nuxt/eslint": "^1.9.0",
    "@nuxt/kit": "^4.1.2",
    "@nuxt/ui": "^4.0.0",
    "@nuxtjs/i18n": "^10.1.0",
    "@nuxtjs/tailwindcss": "^6.14.0",
    "@pinia/nuxt": "^0.11.2",
    "@tailwindcss/postcss": "^4.1.13",
    "@tailwindcss/vite": "^4.1.13",
    "@types/node": "^24.5.2",
    "@vite-pwa/nuxt": "^1.0.4",
    "buffer": "^6.0.3",
    "dayjs": "^1.11.18",
    "dexie": "^4.2.0",
    "dotenv-cli": "^8.0.0",
    "eslint": "^9.36.0",
    "idb": "^8.0.3",
    "nuxt": "^4.1.2",
    "pinia": "^3.0.3",
    "postcss": "^8.5.6",
    "tailwindcss": "^4.1.13",
    "uuid": "^13.0.0",
    "vue": "^3.5.17",
    "vue-router": "^4.5.1"
  }
}
```

#### Réinstallation
```bash
# Supprimer node_modules et lock file
rm -rf node_modules package-lock.json

# Réinstaller
npm install

# Tester build
npm run build
```

**Gain**: Conformité best practices, npm ci optimisé
**Risque**: Aucun (génère du static HTML/CSS/JS)
**Temps**: 5 minutes

---

### 4. Proposition de commit Git (optionnel)

**IMPORTANT**: Ne jamais exécuter automatiquement. Vous décidez si/quand committer.

**Proposition de message de commit**:
```
chore: cleanup code - remove mysqli comments, old FPDF, reclassify node deps

- Remove 90 lines of commented MySQLi code (MyBdd.php)
- Remove old FPDF versions (keep 1.8.4 only)
- Move all app2 dependencies to devDependencies (static build)

Pre-migration cleanup - Phase 0
```

**Commandes suggérées** (à exécuter manuellement si vous le souhaitez):
```bash
# 1. Vérifier les changements
git status
git diff

# 2. Ajouter les fichiers (si vous validez les changements)
git add sources/commun/MyBdd.php
git add sources/app2/package.json

# 3. Commit (si vous le souhaitez)
git commit -m "chore: cleanup code - remove mysqli comments, old FPDF, reclassify node deps

- Remove 90 lines of commented MySQLi code (MyBdd.php)
- Remove old FPDF versions (keep 1.8.4 only)
- Move all app2 dependencies to devDependencies (static build)

Pre-migration cleanup - Phase 0"

# 4. Tag (optionnel, si vous voulez marquer ce point)
git tag -a v1.0-pre-migration -m "État du code avant migration PHP 8 / Backend moderne"

# 5. Push (JAMAIS automatique - vous décidez)
# git push origin php8
# git push origin v1.0-pre-migration
```

**Temps**: 5 minutes (selon votre validation)

---

## ⚠️ Actions à valider (tests requis)

### 5. Validation usage OpenTBS

**Test**:
```bash
cd /home/laurent/Documents/dev/kpi

# Rechercher usage dans code actif
grep -r "opentbs\|OpenTBS" sources/ \
  --include="*.php" \
  --exclude-dir=wordpress_archive \
  --exclude-dir=node_modules
```

**Si aucun résultat** → Supprimer:
```bash
rm -rf sources/lib/opentbs/
```

**Gain potentiel**: ~200 KB
**Temps validation**: 2 minutes

---

### 6. Validation usage EasyTimer

**Test**:
```bash
cd /home/laurent/Documents/dev/kpi

# Rechercher usage
grep -r "easytimer\|EasyTimer" sources/ \
  --include="*.php" \
  --include="*.js" \
  --include="*.html" \
  --exclude-dir=wordpress_archive \
  --exclude-dir=node_modules
```

**Si aucun résultat** → Supprimer:
```bash
rm -rf sources/lib/easytimer-4.6.0/
```

**Gain potentiel**: ~50 KB
**Temps validation**: 2 minutes

---

## Résumé

### Gains totaux (actions 1-3)
- **Code**: -90 lignes PHP
- **Taille**: ~500 KB libérés
- **Qualité**: Conformité best practices Node.js
- **Temps total**: **~20 minutes**

### Gains potentiels (actions 5-6)
- **Taille supplémentaire**: ~250 KB
- **Temps validation**: **~5 minutes**

### Risques
- ✅ **Actions 1-3**: ZÉRO (backups + code actif intact)
- ⚠️ **Actions 5-6**: FAIBLE (validation préalable requise)

---

## Checklist d'exécution

- [ ] **Backup complet** avant toute action
  ```bash
  cd /home/laurent/Documents/dev/kpi
  tar -czf ../kpi-backup-$(date +%Y%m%d).tar.gz .
  ```

- [ ] **Action 1**: Supprimer MySQLi commenté (MyBdd.php)
- [ ] **Action 2**: Supprimer anciennes versions FPDF
- [ ] **Action 3**: Reclassifier dépendances Node (app2)
- [ ] **Action 4**: Git commit + tag v1.0-pre-migration
- [ ] **Action 5** (optionnel): Valider + supprimer OpenTBS
- [ ] **Action 6** (optionnel): Valider + supprimer EasyTimer

- [ ] **Tests post-nettoyage**
  - [ ] App2 build OK (`npm run build`)
  - [ ] Génération PDF OK (tester 1 feuille match)
  - [ ] Applications Vue fonctionnelles

---

**Prochaine étape**: Phase 1 - Tests PHP 8.x et sécurisation API

**Référence**: Voir [AUDIT_PHASE_0.md](AUDIT_PHASE_0.md) pour le plan complet
