# Guide NPM Backend en Production (Container Temporaire)

**Date**: 1er novembre 2025
**Stratégie**: Installation via container temporaire Node.js (comme Composer)
**Objectif**: Repository léger, dépendances installées à la demande

---

## 🎯 Principe

**Identique à Composer** :
- ✅ Versionner uniquement `package.json` et `package-lock.json`
- ✅ Ignorer `node_modules/` dans Git
- ✅ Installer les dépendances lors du déploiement via container temporaire

**Avantages** :
- ✅ Repository léger (pas de node_modules versionné)
- ✅ Versions exactes garanties par `package-lock.json` (hash SHA512)
- ✅ Aucune installation Node.js permanente requise
- ✅ Reproductible dev → staging → prod
- ✅ Facilite les mises à jour futures

---

## 📁 Structure Fichiers

```
sources/
├── package.json           # ✅ VERSIONNÉ (déclaration dépendances)
├── package-lock.json      # ✅ VERSIONNÉ (versions exactes + hash)
├── node_modules/          # ❌ IGNORÉ (.gitignore)
│   └── flatpickr/         # Installé via make backend_npm_install
└── js/
    └── flatpickr-wrapper.js  # ✅ VERSIONNÉ (votre code)
```

---

## 🔧 Configuration .gitignore

**Déjà configuré dans** `.gitignore` :

```gitignore
# Dépendances NPM Backend
sources/node_modules/
sources/package-lock.json

# Composer (même principe)
sources/vendor/
```

**Note** : `package-lock.json` est ignoré car généré automatiquement, mais vous POUVEZ le versionner pour garantir versions exactes (recommandé).

---

## 📦 Workflow Développement

### 1. Ajouter une Bibliothèque (Première fois)

```bash
# Exemple : Ajouter Flatpickr
make backend_npm_add package=flatpickr

# Résultat :
# ✅ sources/package.json créé/modifié
# ✅ sources/package-lock.json créé
# ✅ sources/node_modules/flatpickr/ installé
```

**Fichiers générés** :

```json
// sources/package.json
{
  "name": "kpi-backend",
  "version": "1.0.0",
  "description": "KPI Backend JavaScript Libraries",
  "dependencies": {
    "flatpickr": "^4.6.13"
  }
}
```

```json
// sources/package-lock.json (extrait)
{
  "packages": {
    "node_modules/flatpickr": {
      "version": "4.6.13",
      "resolved": "https://registry.npmjs.org/flatpickr/-/flatpickr-4.6.13.tgz",
      "integrity": "sha512-97PMG/aywoYpB4IvbvUJi0RQi8vearvU0oov1WW3k0WZPBMrTQVqekSX5CjSG/M4Q3i6A/0FKXC7RyAoAUUSPw=="
    }
  }
}
```

### 2. Versionner dans Git

```bash
# Versionner les lock files (RECOMMANDÉ)
git add sources/package.json
git add sources/package-lock.json

# Commit
git commit -m "feat: add Flatpickr 4.6.13 via npm

- Gestion bibliothèques JavaScript backend via npm
- Versions exactes garanties par package-lock.json
- Installation via container temporaire (make backend_npm_install)

🤖 Generated with Claude Code
Co-Authored-By: Claude <noreply@anthropic.com>"
```

**⚠️ Important** : NE PAS versionner `sources/node_modules/` (déjà dans .gitignore)

---

## 🚀 Workflow Production/Déploiement

### Scénario 1 : Déploiement sur Serveur avec Docker

```bash
# 1. Pull du repository (sans node_modules)
cd /var/www/html
git pull origin main

# 2. Installer les dépendances JavaScript (comme Composer)
make backend_npm_install

# Résultat :
# ✅ Container Node.js temporaire démarre
# ✅ Lit sources/package-lock.json
# ✅ Télécharge Flatpickr 4.6.13 (version exacte)
# ✅ Installe dans sources/node_modules/
# ✅ Container s'auto-détruit
```

### Scénario 2 : Script de Déploiement Automatisé

```bash
#!/bin/bash
# deploy.sh

set -e  # Arrêt sur erreur

echo "📦 Déploiement KPI..."

# 1. Pull code
git pull origin main

# 2. Dépendances PHP
echo "🐘 Installation Composer..."
make backend_composer_install

# 3. Dépendances JavaScript Backend
echo "📦 Installation NPM Backend..."
make backend_npm_install

# 4. Dépendances Nuxt App2
echo "🚀 Installation NPM App2..."
make app2_npm_install

# 5. Build Nuxt
echo "🏗️  Build Nuxt..."
make app2_build

# 6. Redémarrage services
echo "🔄 Redémarrage containers..."
make docker_prod_restart

echo "✅ Déploiement terminé !"
```

---

## 🔒 Sécurité et Reproductibilité

### Vérification d'Intégrité (package-lock.json)

**Chaque package a un hash SHA512** :

```json
{
  "integrity": "sha512-97PMG/aywoYpB4IvbvUJi0RQi8vearvU0oov1WW3k0WZPBMrTQVqekSX5CjSG/M4Q3i6A/0FKXC7RyAoAUUSPw=="
}
```

**npm vérifie automatiquement** :
- ✅ Hash correspond → Installation OK
- ❌ Hash différent → **ERREUR** (fichier corrompu/modifié)

### Garantie de Reproductibilité

| Environnement | package.json | package-lock.json | Résultat |
|---------------|--------------|-------------------|----------|
| **Dev** (local) | `flatpickr: ^4.6.13` | `version: 4.6.13` | Flatpickr 4.6.13 |
| **Staging** | `flatpickr: ^4.6.13` | `version: 4.6.13` | Flatpickr 4.6.13 ✅ |
| **Production** | `flatpickr: ^4.6.13` | `version: 4.6.13` | Flatpickr 4.6.13 ✅ |

**Même version sur tous les environnements** 🎯

---

## 📋 Commandes Makefile Disponibles

```bash
# Initialisation (première fois)
make backend_npm_init                    # Créer package.json

# Gestion dépendances
make backend_npm_add package=flatpickr   # Ajouter bibliothèque
make backend_npm_install                 # Installer toutes dépendances (PROD)
make backend_npm_update                  # Mettre à jour dépendances

# Informations
make backend_npm_ls                      # Lister packages installés

# Nettoyage
make backend_npm_clean                   # Supprimer node_modules (local dev)
```

---

## 🔄 Mise à Jour Bibliothèques (Futur)

### Exemple : Flatpickr 4.6.13 → 4.7.0

```bash
# 1. Mettre à jour package.json
cd sources
docker run --rm -v $(PWD):/app -w /app node:20-alpine sh -c "npm update flatpickr"

# Ou manuellement dans package.json :
# "flatpickr": "^4.7.0"

# 2. Installer nouvelle version
make backend_npm_install

# 3. Tester localement
# ...

# 4. Versionner
git add sources/package.json sources/package-lock.json
git commit -m "chore: update Flatpickr 4.6.13 → 4.7.0"

# 5. Déployer
git push origin main
# Sur serveur : make backend_npm_install
```

---

## 🆚 Comparaison Options

| Critère | Option 2 (npm install prod) ✅ CHOISI | Option 1 (Versionner node_modules) |
|---------|--------------------------------------|-------------------------------------|
| **Repository** | 🟢 Léger (~2 KB) | 🔴 Lourd (+16 KB par lib) |
| **Git diff** | 🟢 Lisible (package.json) | 🔴 Pollution (minified files) |
| **Déploiement** | 🟡 `make backend_npm_install` | 🟢 Aucune install (déjà dans Git) |
| **Mises à jour** | 🟢 Facile (package.json) | 🟡 Commit lourd |
| **Dépendance réseau** | ⚠️ Registry npm (déploiement) | ✅ Aucune |
| **Cohérence Composer** | ✅ Identique | ❌ Différent |
| **Environnements isolés** | ✅ Fonctionne (Docker) | ✅ Fonctionne |

---

## 🧪 Exemple Complet : Migration Flatpickr

### Étape 1 : Installation Locale

```bash
# Ajouter Flatpickr
make backend_npm_add package=flatpickr

# Vérifier installation
ls -lh sources/node_modules/flatpickr/dist/
# flatpickr.min.js, flatpickr.min.css, l10n/fr.js
```

### Étape 2 : Créer Wrapper

```bash
# Créer sources/js/flatpickr-wrapper.js
# (voir contenu dans FLATPICKR_MIGRATION_GUIDE.md)
```

### Étape 3 : Modifier Templates

```smarty
{* sources/smarty/templates/page.tpl *}

{* AVANT *}
<script src="js/dhtmlgoodies_calendar.js?random=20060118"></script>

{* APRÈS *}
<link rel="stylesheet" href="node_modules/flatpickr/dist/flatpickr.min.css?v={$NUM_VERSION}">
<script src="node_modules/flatpickr/dist/flatpickr.min.js?v={$NUM_VERSION}"></script>
<script src="node_modules/flatpickr/dist/l10n/fr.js?v={$NUM_VERSION}"></script>
<script src="js/flatpickr-wrapper.js?v={$NUM_VERSION}"></script>
```

**⚠️ Important** : Chemin `node_modules/flatpickr/...` car fichiers dans `sources/node_modules/`

### Étape 4 : Tester Localement

```bash
# Vider cache Smarty
rm -rf sources/smarty/templates_c/*

# Tester pages admin
# GestionCompetition.php, GestionAthlete.php, etc.
```

### Étape 5 : Commit

```bash
git add sources/package.json
git add sources/package-lock.json
git add sources/js/flatpickr-wrapper.js
git add sources/smarty/templates/page.tpl

git commit -m "feat: migrate dhtmlgoodies → Flatpickr 4.6.13

- Replace dhtmlgoodies_calendar (2006, unmaintained)
- Add Flatpickr via npm (modern, maintained)
- Wrapper function for backward compatibility
- Zero template logic changes (17 calls preserved)

Benefits:
- -34 KB (50 KB → 16 KB)
- WCAG 2.1 accessible
- Mobile optimized
- Active maintenance

🤖 Generated with Claude Code
Co-Authored-By: Claude <noreply@anthropic.com>"
```

### Étape 6 : Déploiement Production

```bash
# Sur serveur production
cd /var/www/html
git pull origin main

# Installer dépendances JavaScript (comme Composer)
make backend_npm_install

# Redémarrer (si cache applicatif)
make docker_prod_restart
```

---

## 🚨 Troubleshooting

### Erreur : `node_modules/flatpickr not found`

**Cause** : `backend_npm_install` pas exécuté en production

**Solution** :
```bash
# Sur serveur
make backend_npm_install
```

### Erreur : `Package integrity check failed`

**Cause** : package-lock.json corrompu ou modifié

**Solution** :
```bash
# Régénérer package-lock.json
rm sources/package-lock.json
make backend_npm_install
git add sources/package-lock.json
git commit -m "chore: regenerate package-lock.json"
```

### Node.js pas installé sur serveur

**Pas de problème** : Le Makefile utilise un **container Docker temporaire**

```bash
# Fonctionne SANS Node.js sur l'hôte
make backend_npm_install
# → docker run --rm node:20-alpine npm install
```

---

## 📚 Ressources

- **Package.json** : https://docs.npmjs.com/cli/v10/configuring-npm/package-json
- **Package-lock.json** : https://docs.npmjs.com/cli/v10/configuring-npm/package-lock-json
- **npm install** : https://docs.npmjs.com/cli/v10/commands/npm-install
- **Semantic Versioning** : https://semver.org/

---

## ✅ Checklist Mise en Production

- [ ] `.gitignore` configuré (`sources/node_modules/` ignoré)
- [ ] `package.json` et `package-lock.json` versionnés
- [ ] Makefile testé (`make backend_npm_install` fonctionne)
- [ ] Script déploiement inclut `make backend_npm_install`
- [ ] Documentation équipe mise à jour
- [ ] Procédure rollback documentée

---

**Auteur** : Laurent Garrigue / Claude Code
**Date** : 1er novembre 2025
**Version** : 1.0
**Statut** : ✅ **GUIDE PRÊT - STRATÉGIE VALIDÉE**
