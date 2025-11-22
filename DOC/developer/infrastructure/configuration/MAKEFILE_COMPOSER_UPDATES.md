# Mises à Jour Makefile et Composer

**Date** : 2025-10-20
**Contexte** : Ajout de Composer pour la migration FPDF → mPDF

## 🎯 Changements Apportés

### 1. Ajout de Composer dans les Dockerfiles

#### Dockerfile.dev.web
```dockerfile
# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
```

#### Dockerfile.prod.web
```dockerfile
# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
```

**Méthode** : Utilisation de `COPY --from=composer:latest` (best practice Docker multi-stage)

### 2. Nouvelles Commandes Makefile

#### Composer Commands (Section complète ajoutée)

| Commande | Description | Usage |
|----------|-------------|-------|
| `make composer_install` | Installe les dépendances Composer | `make composer_install` |
| `make composer_update` | Met à jour les dépendances | `make composer_update` |
| `make composer_require` | Ajoute un package | `make composer_require package=mpdf/mpdf` |
| `make composer_require_dev` | Ajoute un package dev | `make composer_require_dev package=phpunit/phpunit` |
| `make composer_dump` | Regénère l'autoloader | `make composer_dump` |

#### Mise à Jour de `make init`

**Avant** :
```
Prochaines étapes:
  1. Configurez les variables dans docker/.env
  2. Lancez l'environnement: make dev_up
  3. Installez les dépendances: make npm_install_app2
  4. Lancez Nuxt: make run_dev
```

**Après** :
```
Prochaines étapes:
  1. Configurez les variables dans docker/.env
  2. Lancez l'environnement: make dev_up
  3. Installez les dépendances Composer: make composer_install
  4. Installez les dépendances NPM: make npm_install_app2
  5. Lancez Nuxt: make run_dev
```

## 📚 Utilisation

### Installation Initiale du Projet

```bash
# 1. Initialiser le projet
make init

# 2. Configurer docker/.env
nano docker/.env

# 3. Démarrer l'environnement
make dev_up

# 4. Installer Composer (NOUVEAU!)
make composer_install

# 5. Installer NPM
make npm_install_app2

# 6. Lancer Nuxt
make run_dev
```

### Gestion des Dépendances Composer

#### Installer les Dépendances

```bash
make composer_install
```

Équivalent à :
```bash
docker exec -ti kpi_php bash -c "cd /var/www/html && composer install"
```

#### Ajouter un Package

```bash
# Package normal
make composer_require package=mpdf/mpdf

# Package de développement
make composer_require_dev package=phpunit/phpunit
```

#### Mettre à Jour les Dépendances

```bash
make composer_update
```

#### Régénérer l'Autoloader

```bash
make composer_dump
```

## 🔧 Configuration Technique

### Variables Makefile

```makefile
DOCKER_EXEC_PHP = docker exec -ti kpi_php
```

### Contexte d'Exécution

Toutes les commandes Composer s'exécutent :
- **Container** : `kpi_php` (PHP 7.4)
- **Répertoire** : `/var/www/html` (= `sources/`)
- **Fichier** : `composer.json` dans `sources/`
- **Output** : `sources/vendor/`

### Pourquoi PHP 7.4 et pas PHP 8 ?

Les commandes utilisent `kpi_php` (PHP 7.4) car :
1. C'est le container principal de l'application
2. Composer doit être compatible avec la version PHP de production actuelle
3. Les dépendances sont installées pour PHP 7.4 (compatibilité)

Si vous voulez utiliser PHP 8 :
```bash
docker exec -ti kpi_php8 bash -c "cd /var/www/html && composer install"
```

## 📋 Fichiers Modifiés

1. **Makefile** ✅
   - Ajout section `## COMPOSER - PHP`
   - 5 nouvelles commandes
   - Mise à jour `make init`
   - Mise à jour `.PHONY`

2. **docker/config/Dockerfile.dev.web** ✅
   - Ajout `COPY --from=composer:latest`

3. **docker/config/Dockerfile.prod.web** ✅
   - Ajout `COPY --from=composer:latest`

## 🧪 Tests

### Vérifier que Composer est Installé

```bash
# Après rebuild des containers
make php_bash
composer --version

# Résultat attendu:
# Composer version 2.x.x
```

### Vérifier les Commandes Makefile

```bash
# Afficher l'aide (les nouvelles commandes doivent apparaître)
make help

# Résultat attendu (section COMPOSER - PHP):
# composer_install           Installe les dépendances Composer (sources/vendor/)
# composer_update            Met à jour les dépendances Composer
# composer_require           Ajoute un package Composer (usage: make composer_require package=vendor/package)
# composer_require_dev       Ajoute un package Composer de dev
# composer_dump              Regénère l'autoloader Composer
```

### Test d'Installation

```bash
# Test complet
make composer_install

# Vérifier que vendor/ existe
ls -la sources/vendor/

# Vérifier mPDF
ls sources/vendor/mpdf/
```

## ⚠️ Points d'Attention

### 1. Rebuild des Containers Nécessaire

Après modification des Dockerfiles, il faut rebuild :

```bash
# Development
make dev_down
docker compose -f docker/compose.dev.yaml build --no-cache
make dev_up

# Production
docker compose -f docker/compose.prod.yaml build --no-cache
make prod_up
```

### 2. vendor/ dans .gitignore

Le dossier `sources/vendor/` est maintenant dans `.gitignore` :
```gitignore
# Composer dependencies (auto-generated)
sources/vendor/
vendor/
```

**Ne jamais committer `vendor/`** !

### 3. composer.lock

Le fichier `composer.lock` **DOIT** être commité :
- Assure les mêmes versions entre dev/prod
- Important pour la reproductibilité

```bash
# Vérifier le statut
git status sources/composer.lock

# Committer si modifié
git add sources/composer.json sources/composer.lock
git commit -m "Update Composer dependencies"
```

## 📖 Références

- **Composer Documentation** : https://getcomposer.org/doc/
- **Docker Multi-Stage Builds** : https://docs.docker.com/build/building/multi-stage/
- **Make Help** : `make help`

## 🚀 Prochaines Étapes

Pour un nouveau développeur sur le projet :

1. Cloner le repo
2. `make init`
3. Configurer `docker/.env`
4. `make dev_up`
5. **`make composer_install`** ← NOUVEAU
6. `make npm_install_app2`
7. `make run_dev`

---

**Note** : Ces changements sont nécessaires pour la migration FPDF → mPDF, car mPDF est installé via Composer.
