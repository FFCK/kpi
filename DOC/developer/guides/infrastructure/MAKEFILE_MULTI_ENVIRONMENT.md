# Makefile Multi-Environment Support

**Date**: 11 novembre 2025
**Statut**: ✅ **COMPLÉTÉ**
**Contexte**: Support de multiples environnements (dev, preprod, prod) sur le même serveur

---

## 📊 Vue d'ensemble

Le Makefile a été modifié pour supporter **plusieurs environnements en parallèle** sur le même serveur (ex: préprod et prod sur le même VPS). Les noms de containers sont désormais **détectés dynamiquement** depuis le fichier `docker/.env`.

---

## 🔧 Modifications apportées

### Variables dynamiques (lignes 8-13)

**Avant** (noms hardcodés):
```makefile
DOCKER_EXEC_PHP = docker exec -ti kpi_php
DOCKER_EXEC_PHP_NON_INTERACTIVE = docker exec kpi_php
DOCKER_EXEC_NODE = docker exec -ti kpi_node_app2
```

**Après** (noms dynamiques):
```makefile
# Variables pour les noms des réseaux et containers
APPLICATION_NAME ?= kpi
NETWORK_KPI_NAME = network_$(APPLICATION_NAME)
PHP_CONTAINER_NAME = $(APPLICATION_NAME)_php
NODE_CONTAINER_NAME = $(APPLICATION_NAME)_node_app2
DB_CONTAINER_NAME = $(APPLICATION_NAME)_db

DOCKER_EXEC_PHP = docker exec -ti $(PHP_CONTAINER_NAME)
DOCKER_EXEC_PHP_NON_INTERACTIVE = docker exec $(PHP_CONTAINER_NAME)
DOCKER_EXEC_NODE = docker exec -ti $(NODE_CONTAINER_NAME)
```

### Commandes mises à jour

#### 1. Composer (PHP)
Toutes les commandes Composer utilisent maintenant `$(PHP_CONTAINER_NAME)`:
- `backend_composer_install` - Affiche "container: kpi_php" ou "container: kpi_preprod_php"
- `backend_composer_update`
- `backend_composer_require`
- `backend_composer_require_dev`
- `backend_composer_dump`

**Exemple**:
```makefile
backend_composer_install:
	@echo "Installation des dépendances Composer (container: $(PHP_CONTAINER_NAME))..."
	$(DOCKER_EXEC_PHP_NON_INTERACTIVE) bash -c "cd /var/www/html && composer install"
```

#### 2. NPM - App2 (Node)
Toutes les commandes NPM pour app2 utilisent maintenant `$(NODE_CONTAINER_NAME)`:
- `app2_npm_install`
- `app2_npm_update`
- `app2_npm_add`
- `app2_npm_add_dev`
- `app2_npm_clean`
- `app2_npm_ls`

**Exemple**:
```makefile
app2_npm_install:
	@echo "Installation des dépendances npm pour app2 (container: $(NODE_CONTAINER_NAME))..."
	$(DOCKER_EXEC_NODE) sh -c "npm install"
```

#### 3. Shell Access
Les commandes d'accès shell sont également dynamiques:
- `backend_bash` - Utilise `$(PHP_CONTAINER_NAME)`
- `app2_bash` - Utilise `$(NODE_CONTAINER_NAME)`
- `db_bash` - Utilise `$(DB_CONTAINER_NAME)`

**Exemple**:
```makefile
db_bash:
	docker exec -ti $(DB_CONTAINER_NAME) sh
```

#### 4. Init (affichage configuration)
La commande `make init` affiche désormais tous les noms de containers détectés:
```
Configuration actuelle:
  - APPLICATION_NAME: kpi
  - Réseau KPI: network_kpi
  - Container PHP: kpi_php
  - Container Node: kpi_node_app2
  - Container DB: kpi_db
```

---

## 🎯 Utilisation

### Scénario 1: Développement local

**Fichier**: `docker/.env`
```env
APPLICATION_NAME=kpi
```

**Résultat**:
- Container PHP: `kpi_php`
- Container Node: `kpi_node_app2`
- Container DB: `kpi_db`
- Réseau: `network_kpi`

**Commandes**:
```bash
make backend_composer_install  # Utilise kpi_php
make app2_npm_install  # Utilise kpi_node_app2
```

---

### Scénario 2: Pré-production sur VPS

**Fichier**: `docker/.env`
```env
APPLICATION_NAME=kpi_preprod
```

**Résultat**:
- Container PHP: `kpi_preprod_php`
- Container Node: `kpi_preprod_node_app2`
- Container DB: `kpi_preprod_db`
- Réseau: `network_kpi_preprod`

**Commandes**:
```bash
make backend_composer_install  # Utilise kpi_preprod_php
make app2_npm_install  # Utilise kpi_preprod_node_app2
```

---

### Scénario 3: Production sur VPS

**Fichier**: `docker/.env`
```env
APPLICATION_NAME=kpi_prod
```

**Résultat**:
- Container PHP: `kpi_prod_php`
- Container Node: `kpi_prod_node_app2`
- Container DB: `kpi_prod_db`
- Réseau: `network_kpi_prod`

**Commandes**:
```bash
make backend_composer_install  # Utilise kpi_prod_php
make app2_npm_install  # Utilise kpi_prod_node_app2
```

---

### Scénario 4: Multiples instances en parallèle (VPS)

**Sur le même serveur**, vous pouvez avoir:

1. **Préprod**: `/var/www/kpi_preprod/` avec `APPLICATION_NAME=kpi_preprod`
2. **Prod**: `/var/www/kpi_prod/` avec `APPLICATION_NAME=kpi_prod`

**Workflow**:
```bash
# Dans /var/www/kpi_preprod/
cd /var/www/kpi_preprod
make backend_composer_update   # ✅ Utilise kpi_preprod_php

# Dans /var/www/kpi_prod/
cd /var/www/kpi_prod
make backend_composer_update   # ✅ Utilise kpi_prod_php
```

**Aucune interférence** entre les deux environnements car les noms de containers sont différents.

---

## 📋 Vérification de configuration

### Vérifier les noms de containers

```bash
# Afficher la configuration actuelle
make init

# Ou directement lire le .env
grep APPLICATION_NAME docker/.env
```

### Vérifier les containers en cours d'exécution

```bash
# Lister tous les containers KPI
docker ps | grep kpi

# Exemples de résultats:
# kpi_php               (dev local)
# kpi_preprod_php       (preprod VPS)
# kpi_prod_php          (prod VPS)
```

### Tester une commande

```bash
# Affichera le nom du container utilisé
make backend_composer_install

# Output attendu:
# Installation des dépendances Composer (container: kpi_php)...
# ou
# Installation des dépendances Composer (container: kpi_preprod_php)...
```

---

## ⚠️ Précautions

### 1. Toujours vérifier docker/.env

Avant d'exécuter des commandes Composer/NPM sur un VPS avec plusieurs environnements:

```bash
# Vérifier dans quel répertoire vous êtes
pwd

# Vérifier quel APPLICATION_NAME est configuré
grep APPLICATION_NAME docker/.env
```

### 2. Ne pas mélanger les environnements

**❌ INCORRECT**:
```bash
cd /var/www/kpi_preprod
docker exec -ti kpi_prod_php bash  # Utilise le mauvais container
```

**✅ CORRECT**:
```bash
cd /var/www/kpi_preprod
make backend_bash  # Utilise automatiquement kpi_preprod_php
```

### 3. Composer et versions PHP

Si vos environnements utilisent des versions PHP différentes, les fichiers `composer.lock` doivent être régénérés après changement d'environnement.

**Exemple**:
```bash
# Dev local (PHP 7.4) → Preprod (PHP 8.4)
cd /var/www/kpi_preprod
make backend_composer_update  # Régénère le lock file avec PHP 8.4
```

---

## 🔍 Résolution de problèmes

### Erreur: "Cannot find container kpi_php"

**Cause**: Le container n'est pas démarré ou le nom ne correspond pas.

**Solution**:
```bash
# 1. Vérifier docker/.env
grep APPLICATION_NAME docker/.env

# 2. Vérifier que les containers sont démarrés
docker ps | grep kpi

# 3. Démarrer les containers
make docker_dev_up  # ou docker_preprod_up / docker_prod_up
```

---

### Erreur: "Cannot connect to Docker daemon"

**Cause**: Docker n'est pas démarré ou permissions insuffisantes.

**Solution**:
```bash
# Vérifier le service Docker
sudo systemctl status docker

# Démarrer Docker si nécessaire
sudo systemctl start docker

# Ajouter votre utilisateur au groupe docker (puis reloguer)
sudo usermod -aG docker $USER
```

---

## 📚 Références

### Fichiers modifiés

- **Makefile** (lignes 8-13, 260-302) - Variables et commandes dynamiques
- **docker/.env** - Configuration `APPLICATION_NAME`

### Commits liés

- Makefile: Dynamic container detection for multi-environment support

### Documentation associée

- [CLAUDE.md](../CLAUDE.md) - Instructions projet
- [docker/.env.dist](../docker/.env.dist) - Exemple de configuration

---

**Auteur**: Claude Code
**Date de finalisation**: 11 novembre 2025
**Version**: 1.0
