# Corrections Docker Production

**Date** : 2025-10-20
**Contexte** : Migration FPDF → mPDF nécessite des extensions PHP supplémentaires

## 🔧 Problèmes Corrigés

### 1. Dockerfile.prod.web Incomplet

**Problème** : Le Dockerfile de production n'incluait pas les extensions PHP nécessaires pour mPDF :
- Extensions manquantes : `gd`, `zip`, `mysqli`
- Librairies manquantes : `libpng-dev`, `libjpeg-dev`, `libfreetype6-dev`, `libzip-dev`

**Impact** : mPDF ne peut pas générer de PDF avec images (erreur sur GD) ou compresser les fichiers

**Solution** : Ajout des extensions dans `docker/config/Dockerfile.prod.web`

```dockerfile
# Avant (incomplet)
RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y libonig-dev mailutils msmtp nano \
    && docker-php-ext-install pdo pdo_mysql mbstring \
    ...

# Après (complet)
RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y \
    libonig-dev \
    mailutils \
    msmtp \
    nano \
    libpng-dev \          # Pour GD (images)
    libjpeg-dev \         # Pour GD (JPEG)
    libfreetype6-dev \    # Pour GD (fonts)
    libzip-dev \          # Pour ZIP
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring mysqli gd zip \
    && docker-php-ext-enable mysqli \
    ...
```

### 2. compose.prod.yaml Utilisait Dockerfile.dev.web

**Problème** : Le fichier `docker/compose.prod.yaml` ligne 7 référençait le Dockerfile de développement

```yaml
# Avant (incorrect)
kpi:
    build:
        context: ./config
        dockerfile: Dockerfile.dev.web  # ❌ Mauvais fichier!
```

**Impact** :
- Configuration PHP de développement en production (`php.ini-development`)
- Outils de debug inutiles en production
- Pas d'optimisation pour production

**Solution** : Utiliser le bon Dockerfile

```yaml
# Après (correct)
kpi:
    build:
        context: ./config
        dockerfile: Dockerfile.prod.web  # ✅ Correct!
```

### 3. vendor/ Pas dans .gitignore

**Problème** : Le dossier `sources/vendor/` (dépendances Composer) n'était pas ignoré par Git

**Impact** :
- Risque de commit de 30+ MB de librairies tierces
- Conflits potentiels entre développeurs
- Taille du repository gonflée inutilement

**Solution** : Ajout dans `.gitignore`

```gitignore
# ==========================================
# Composer dependencies (auto-generated)
# ==========================================
sources/vendor/
vendor/
```

## 📋 Fichiers Modifiés

1. **docker/config/Dockerfile.prod.web** ✅
   - Ajout extensions GD, ZIP, MySQLi
   - Ajout librairies image (PNG, JPEG, FreeType)
   - Nettoyage apt cache pour réduire taille image
   - Conservation du PID Apache cleanup

2. **docker/compose.prod.yaml** ✅
   - Ligne 7 : `Dockerfile.dev.web` → `Dockerfile.prod.web`

3. **.gitignore** ✅
   - Ajout `sources/vendor/` et `vendor/`

## 🔍 Vérifications

### Dockerfile.prod.web

```bash
# Vérifier les extensions installées
docker compose -f compose.prod.yaml run --rm kpi php -m | grep -E "(gd|zip|mysqli)"

# Résultat attendu :
# gd
# mysqli
# zip
```

### compose.prod.yaml

```bash
# Vérifier le Dockerfile référencé
grep "dockerfile:" docker/compose.prod.yaml

# Résultat attendu :
# dockerfile: Dockerfile.prod.web
```

### .gitignore

```bash
# Vérifier que vendor/ est ignoré
git status sources/vendor/

# Résultat attendu :
# (rien, car ignoré)
```

## 📊 Comparaison dev vs prod

| Élément | Development | Pre-Production | Production |
|---------|------------|----------------|------------|
| **Dockerfile** | `Dockerfile.dev.web` | `Dockerfile.dev.web` | `Dockerfile.prod.web` |
| **PHP Config** | `php.ini-development` | `php.ini-development` | `php.ini-production` |
| **Extensions** | Toutes + Git | Toutes + Git | Production uniquement |
| **Optimisation** | Non | Non | Oui (apt clean) |
| **Usage** | Local dev | Test avant prod | Production live |

## ⚠️ Notes Importantes

### Pourquoi preprod garde Dockerfile.dev.web ?

La pré-production est un environnement de **test avant production**. On garde :
- Configuration de développement pour faciliter le debug
- Git pour pouvoir faire des hotfix si nécessaire
- Outils de développement pour identifier les problèmes

### Extensions PHP Essentielles pour mPDF

| Extension | Usage | Obligatoire ? |
|-----------|-------|---------------|
| **gd** | Génération d'images, QRCode, logos | ✅ OUI |
| **zip** | Compression, subsetting de fonts | ✅ OUI |
| **mbstring** | Support UTF-8, encodage texte | ✅ OUI |
| **mysqli** | Connexion MySQL (application) | ✅ OUI |
| **pdo_mysql** | PDO MySQL (application) | ✅ OUI |

Sans ces extensions, mPDF génère des erreurs :
- Sans GD : Impossible d'insérer des images
- Sans ZIP : Impossible de générer des PDFs optimisés
- Sans mbstring : Problèmes d'encodage UTF-8

## 🚀 Déploiement Production

### 1. Rebuild de l'image Docker

```bash
cd docker
docker compose -f compose.prod.yaml build --no-cache kpi
```

### 2. Vérification des extensions

```bash
docker compose -f compose.prod.yaml run --rm kpi php -v
docker compose -f compose.prod.yaml run --rm kpi php -m | grep -E "(gd|zip|mysqli)"
```

### 3. Test de génération PDF

```bash
docker compose -f compose.prod.yaml run --rm kpi php -l /var/www/html/PdfListeMatchs.php
```

### 4. Redémarrage des services

```bash
docker compose -f compose.prod.yaml down
docker compose -f compose.prod.yaml up -d
```

## 📚 Références

- **Dockerfile.dev.web** : Configuration développement (complète, avec outils)
- **Dockerfile.prod.web** : Configuration production (optimisée, minimale)
- **mPDF Requirements** : https://mpdf.github.io/installation-setup/requirements.html
- **PHP GD Extension** : https://www.php.net/manual/en/book.image.php

---

**Auteur** : Claude Code
**Date** : 2025-10-20
**Contexte** : Migration FPDF → mPDF v8.2.6
