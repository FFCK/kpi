-include docker/.env

UID := $(shell id -u)
GID := $(shell id -g)
export USER_ID := $(UID)
export GROUP_ID := $(GID)

# Variables pour les noms des réseaux et containers
APPLICATION_NAME ?= kpi
NETWORK_KPI_NAME = network_$(APPLICATION_NAME)
PHP_CONTAINER_NAME = $(APPLICATION_NAME)_php
NODE_CONTAINER_NAME = $(APPLICATION_NAME)_node_app2
NODE3_CONTAINER_NAME = $(APPLICATION_NAME)_node_app3
NODE4_CONTAINER_NAME = kpi_node_app4
DB_CONTAINER_NAME = $(APPLICATION_NAME)_db

DOCKER_COMPOSE = docker compose
DOCKER_EXEC = docker exec -ti
DOCKER_EXEC_PHP = docker exec -ti $(PHP_CONTAINER_NAME)
DOCKER_EXEC_PHP_NON_INTERACTIVE = docker exec $(PHP_CONTAINER_NAME)
DOCKER_EXEC_NODE = docker exec -ti $(NODE_CONTAINER_NAME)
DOCKER_EXEC_NODE_NON_INTERACTIVE = docker exec $(NODE_CONTAINER_NAME)
DOCKER_EXEC_NODE3 = docker exec -ti $(NODE3_CONTAINER_NAME)
DOCKER_EXEC_NODE3_NON_INTERACTIVE = docker exec $(NODE3_CONTAINER_NAME)
DOCKER_EXEC_NODE4 = docker exec -ti $(NODE4_CONTAINER_NAME)
DOCKER_EXEC_NODE4_NON_INTERACTIVE = docker exec $(NODE4_CONTAINER_NAME)
.DEFAULT_GOAL = help

.PHONY: help init init_env init_env_app2 init_env_app3 init_env_app4 init_env_api2 init_networks \
docker_dev_up docker_dev_down docker_dev_restart docker_dev_rebuild docker_dev_logs docker_dev_status \
docker_preprod_up docker_preprod_down docker_preprod_restart docker_preprod_rebuild docker_preprod_logs docker_preprod_status \
docker_prod_up docker_prod_down docker_prod_restart docker_prod_rebuild docker_prod_logs docker_prod_status \
app2_dev app2_build app2_generate_dev app2_generate_preprod app2_generate_production app2_generate_prod app2_lint \
app2_npm_install app2_npm_ls app2_npm_clean app2_npm_update app2_npm_add app2_npm_add_dev app2_bash \
app3_dev app3_build app3_generate_dev app3_generate_preprod app3_generate_prod app3_lint \
app3_npm_install app3_npm_ls app3_npm_clean app3_npm_update app3_npm_add app3_npm_add_dev app3_bash \
app4_dev app4_build app4_generate_dev app4_generate_preprod app4_generate_prod app4_lint \
app4_npm_install app4_npm_ls app4_npm_clean app4_npm_update app4_npm_add app4_npm_add_dev app4_bash \
backend_npm_install backend_npm_add backend_npm_update backend_npm_ls backend_npm_clean backend_npm_init \
backend_composer_install backend_composer_update backend_composer_require backend_composer_require_dev backend_composer_dump backend_bash \
api2_composer_install api2_composer_update api2_composer_require api2_cache_clear api2_cache_warmup api2_migrations_diff api2_migrations_migrate \
api2_jwt_generate_keys \
db_bash \
backend_worker_start backend_worker_stop backend_worker_status backend_worker_logs backend_worker_restart \
wordpress_backup wordpress_restore \
docker_networks_create docker_networks_list docker_networks_clean



help: ## Affiche cette aide
	@echo "Usage: make [commande]"
	@echo ""
	@awk 'BEGIN {FS = ":.*?## "; section = ""} \
		/^##/ { \
			if ($$0 !~ /^####/) { \
				section = substr($$0, 4); \
				printf "\n\033[1;33m%s\033[0m\n", section; \
			} \
			next; \
		} \
		/^[a-zA-Z0-9_-]+:.*?##/ { \
			printf "  \033[36m%-35s\033[0m %s\n", $$1, $$2; \
		}' $(MAKEFILE_LIST)


## INITIALISATION
init: init_env init_env_app2 init_env_app3 init_env_app4 init_env_api2 init_networks ## Initialisation complète du projet (env, réseaux)
	@echo ""
	@echo "Initialisation complète terminée!"
	@echo ""
	@echo "Configuration actuelle:"
	@echo "  - APPLICATION_NAME: $(APPLICATION_NAME)"
	@echo "  - Réseau KPI: $(NETWORK_KPI_NAME)"
	@echo "  - Container PHP: $(PHP_CONTAINER_NAME)"
	@echo "  - Container Node: $(NODE_CONTAINER_NAME)"
	@echo "  - Container DB: $(DB_CONTAINER_NAME)"
	@echo ""
	@echo "Prochaines étapes:"
	@echo "  1. Configurez les variables dans docker/.env"
	@echo "  2. Lancez l'environnement: make docker_dev_up (ou docker_preprod_up/docker_prod_up)"
	@echo "  3. Installez les dépendances Composer: make backend_composer_install"
	@echo "  4. Installez les dépendances Composer pour API2: make api2_composer_install"
	@echo "  5. Installez les dépendances NPM: make app2_npm_install"
	@echo "  6. Lancez Nuxt: make app2_dev"
	@echo ""
	@echo "Note: Pour une préprod/prod, vérifiez APPLICATION_NAME dans docker/.env"

init_env: ## Initialise le fichier docker/.env depuis docker/.env.dist
	@if [ ! -f docker/.env ]; then \
		cp docker/.env.dist docker/.env; \
		echo "Fichier docker/.env créé"; \
		echo "N'oubliez pas de configurer les variables dans docker/.env"; \
	else \
		echo "Le fichier docker/.env existe déjà"; \
	fi

init_env_app2: ## Initialise les fichiers .env.development, .env.preprod et .env.production pour app2
	@if [ ! -f sources/app2/.env.development ]; then \
		cp sources/app2/.env.development.example sources/app2/.env.development; \
		echo "Fichier .env.development créé pour app2"; \
		cp sources/app2/.env.dist sources/app2/.env.development; \
		echo "✅ Fichier .env.development créé pour app2"; \
		echo "⚠️  N'oubliez pas de configurer les variables dans .env.development"; \
	else \
		echo "Le fichier .env.development existe déjà pour app2"; \
	fi
	@if [ ! -f sources/app2/.env.preprod ]; then \
		cp sources/app2/.env.dist sources/app2/.env.preprod; \
		echo "✅ Fichier .env.preprod créé pour app2"; \
		echo "⚠️  N'oubliez pas de configurer les variables dans .env.preprod"; \
	else \
		echo "Le fichier .env.preprod existe déjà pour app2"; \
	fi
	@if [ ! -f sources/app2/.env.production ]; then \
		cp sources/app2/.env.dist sources/app2/.env.production; \
		echo "✅ Fichier .env.production créé pour app2"; \
		echo "⚠️  N'oubliez pas de configurer les variables dans .env.production"; \
	else \
		echo "Le fichier .env.production existe déjà pour app2"; \
	fi

init_env_app3: ## Initialise les fichiers .env.development, .env.preprod et .env.production pour app3
	@if [ ! -f sources/app3/.env.preprod ]; then \
		cp sources/app3/.env.preprod.dist sources/app3/.env.preprod; \
		echo "Fichier .env.preprod créé pour app3"; \
		echo "N'oubliez pas de configurer le domaine de préproduction dans .env.preprod"; \
	else \
		echo "Le fichier .env.preprod existe déjà pour app3"; \
	fi
	@echo "Les autres fichiers .env pour app3 sont déjà créés dans sources/app3/"

init_env_app4: ## Initialise le fichier .env pour app4 (admin) depuis .env.dist
	@if [ ! -f sources/app4/.env ]; then \
		cp sources/app4/.env.dist sources/app4/.env; \
		echo "Fichier .env créé pour app4"; \
		echo "N'oubliez pas de configurer les variables dans sources/app4/.env selon l'environnement"; \
	else \
		echo "Le fichier .env existe déjà pour app4"; \
	fi

init_env_api2: ## Initialise le fichier .env pour API2 depuis .env.dist
	@if [ ! -f sources/api2/.env ]; then \
		cp sources/api2/.env.dist sources/api2/.env; \
		echo "Fichier .env créé pour API2"; \
		echo "N'oubliez pas de configurer les variables dans sources/api2/.env si nécessaire"; \
	else \
		echo "Le fichier sources/api2/.env existe déjà"; \
	fi

init_networks: docker_networks_create ## Alias pour docker_networks_create (crée les réseaux Docker)

check_env: ## Vérifie que les fichiers d'environnement existent et sont configurés
	@echo "🔍 Vérification des fichiers d'environnement..."
	@echo ""
	@errors=0; \
	\
	echo "📁 docker/.env (depuis docker/.env.dist)"; \
	if [ ! -f docker/.env ]; then \
		echo "  ❌ Fichier manquant: docker/.env"; \
		echo "     → Exécutez: make init_env"; \
		errors=$$((errors + 1)); \
	else \
		echo "  ✅ Fichier existe"; \
		missing=""; \
		for var in USER_ID GROUP_ID DB_ROOT_PASSWORD DB_USER DB_PASSWORD DB_NAME; do \
			val=$$(grep "^$$var=" docker/.env 2>/dev/null | cut -d'=' -f2-); \
			if [ -z "$$val" ]; then \
				missing="$$missing $$var"; \
			fi; \
		done; \
		if [ -n "$$missing" ]; then \
			echo "  ⚠️  Variables vides:$$missing"; \
			errors=$$((errors + 1)); \
		else \
			echo "  ✅ Variables principales configurées"; \
		fi; \
	fi; \
	echo ""; \
	\
	echo "📁 sources/commun/MyParams.php (depuis sources/commun/MyParams.php.modele)"; \
	if [ ! -f sources/commun/MyParams.php ]; then \
		echo "  ❌ Fichier manquant: sources/commun/MyParams.php"; \
		echo "     → Copiez MyParams.php.modele vers MyParams.php et configurez-le"; \
		errors=$$((errors + 1)); \
	elif [ ! -s sources/commun/MyParams.php ]; then \
		echo "  ❌ Fichier vide: sources/commun/MyParams.php"; \
		echo "     → Copiez MyParams.php.modele vers MyParams.php et configurez-le"; \
		errors=$$((errors + 1)); \
	else \
		echo "  ✅ Fichier existe et non vide"; \
		missing=""; \
		for var in PARAM_LOCAL_LOGIN PARAM_LOCAL_PASSWORD PARAM_LOCAL_DB PARAM_LOCAL_SERVER; do \
			if ! grep -q "define('$$var'" sources/commun/MyParams.php 2>/dev/null; then \
				missing="$$missing $$var"; \
			else \
				val=$$(grep "define('$$var'" sources/commun/MyParams.php | sed "s/.*define('$$var', *'\\([^']*\\)'.*/\\1/"); \
				if [ -z "$$val" ]; then \
					missing="$$missing $$var"; \
				fi; \
			fi; \
		done; \
		if [ -n "$$missing" ]; then \
			echo "  ⚠️  Variables vides ou manquantes:$$missing"; \
			errors=$$((errors + 1)); \
		else \
			echo "  ✅ Variables principales configurées"; \
		fi; \
	fi; \
	echo ""; \
	\
	echo "📁 sources/app2/.env.* (depuis sources/app2/.env.dist)"; \
	if [ ! -f sources/app2/.env.dist ]; then \
		echo "  ❌ Fichier modèle manquant: sources/app2/.env.dist"; \
		errors=$$((errors + 1)); \
	else \
		echo "  ✅ Fichier modèle existe"; \
	fi; \
	for env in development preprod production; do \
		if [ ! -f sources/app2/.env.$$env ]; then \
			echo "  ❌ Fichier manquant: sources/app2/.env.$$env"; \
			echo "     → Exécutez: make init_env_app2"; \
			errors=$$((errors + 1)); \
		else \
			missing=""; \
			for var in API_BASE_URL BACKEND_BASE_URL; do \
				val=$$(grep "^$$var=" sources/app2/.env.$$env 2>/dev/null | cut -d'=' -f2-); \
				if [ -z "$$val" ]; then \
					missing="$$missing $$var"; \
				fi; \
			done; \
			if [ -n "$$missing" ]; then \
				echo "  ⚠️  .env.$$env - Variables vides:$$missing"; \
				errors=$$((errors + 1)); \
			else \
				echo "  ✅ .env.$$env configuré"; \
			fi; \
		fi; \
	done; \
	echo ""; \
	\
	echo "📁 sources/app4/.env.* (depuis sources/app4/.env.dist)"; \
	if [ ! -f sources/app4/.env.dist ]; then \
		echo "  ❌ Fichier modèle manquant: sources/app4/.env.dist"; \
		errors=$$((errors + 1)); \
	else \
		echo "  ✅ Fichier modèle existe"; \
	fi; \
	for env in development preprod production; do \
		if [ ! -f sources/app4/.env.$$env ]; then \
			echo "  ❌ Fichier manquant: sources/app4/.env.$$env"; \
			echo "     → Exécutez: make init_env_app4"; \
			errors=$$((errors + 1)); \
		else \
			missing=""; \
			for var in API2_BASE_URL; do \
				val=$$(grep "^$$var=" sources/app4/.env.$$env 2>/dev/null | cut -d'=' -f2-); \
				if [ -z "$$val" ]; then \
					missing="$$missing $$var"; \
				fi; \
			done; \
			if [ -n "$$missing" ]; then \
				echo "  ⚠️  .env.$$env - Variables vides:$$missing"; \
				errors=$$((errors + 1)); \
			else \
				echo "  ✅ .env.$$env configuré"; \
			fi; \
		fi; \
	done; \
	echo ""; \
	\
	if [ $$errors -gt 0 ]; then \
		echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"; \
		echo "❌ $$errors problème(s) détecté(s)"; \
		echo ""; \
		echo "💡 Pour initialiser les fichiers manquants: make init"; \
		exit 1; \
	else \
		echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"; \
		echo "✅ Tous les fichiers d'environnement sont configurés!"; \
	fi

check_env: ## Vérifie que les fichiers d'environnement existent et sont configurés
	@echo "🔍 Vérification des fichiers d'environnement..."
	@echo ""
	@errors=0; \
	\
	echo "📁 docker/.env (depuis docker/.env.dist)"; \
	if [ ! -f docker/.env ]; then \
		echo "  ❌ Fichier manquant: docker/.env"; \
		echo "     → Exécutez: make init_env"; \
		errors=$$((errors + 1)); \
	else \
		echo "  ✅ Fichier existe"; \
		missing=""; \
		for var in USER_ID GROUP_ID DB_ROOT_PASSWORD DB_USER DB_PASSWORD DB_NAME; do \
			val=$$(grep "^$$var=" docker/.env 2>/dev/null | cut -d'=' -f2-); \
			if [ -z "$$val" ]; then \
				missing="$$missing $$var"; \
			fi; \
		done; \
		if [ -n "$$missing" ]; then \
			echo "  ⚠️  Variables vides:$$missing"; \
			errors=$$((errors + 1)); \
		else \
			echo "  ✅ Variables principales configurées"; \
		fi; \
	fi; \
	echo ""; \
	\
	echo "📁 sources/commun/MyParams.php (depuis sources/commun/MyParams.php.modele)"; \
	if [ ! -f sources/commun/MyParams.php ]; then \
		echo "  ❌ Fichier manquant: sources/commun/MyParams.php"; \
		echo "     → Copiez MyParams.php.modele vers MyParams.php et configurez-le"; \
		errors=$$((errors + 1)); \
	elif [ ! -s sources/commun/MyParams.php ]; then \
		echo "  ❌ Fichier vide: sources/commun/MyParams.php"; \
		echo "     → Copiez MyParams.php.modele vers MyParams.php et configurez-le"; \
		errors=$$((errors + 1)); \
	else \
		echo "  ✅ Fichier existe et non vide"; \
		missing=""; \
		for var in PARAM_LOCAL_LOGIN PARAM_LOCAL_PASSWORD PARAM_LOCAL_DB PARAM_LOCAL_SERVER; do \
			if ! grep -q "define('$$var'" sources/commun/MyParams.php 2>/dev/null; then \
				missing="$$missing $$var"; \
			else \
				val=$$(grep "define('$$var'" sources/commun/MyParams.php | sed "s/.*define('$$var', *'\\([^']*\\)'.*/\\1/"); \
				if [ -z "$$val" ]; then \
					missing="$$missing $$var"; \
				fi; \
			fi; \
		done; \
		if [ -n "$$missing" ]; then \
			echo "  ⚠️  Variables vides ou manquantes:$$missing"; \
			errors=$$((errors + 1)); \
		else \
			echo "  ✅ Variables principales configurées"; \
		fi; \
	fi; \
	echo ""; \
	\
	echo "📁 sources/app2/.env.* (depuis sources/app2/.env.dist)"; \
	if [ ! -f sources/app2/.env.dist ]; then \
		echo "  ❌ Fichier modèle manquant: sources/app2/.env.dist"; \
		errors=$$((errors + 1)); \
	else \
		echo "  ✅ Fichier modèle existe"; \
	fi; \
	for env in development preprod production; do \
		if [ ! -f sources/app2/.env.$$env ]; then \
			echo "  ❌ Fichier manquant: sources/app2/.env.$$env"; \
			echo "     → Exécutez: make init_env_app2"; \
			errors=$$((errors + 1)); \
		else \
			missing=""; \
			for var in API_BASE_URL BACKEND_BASE_URL; do \
				val=$$(grep "^$$var=" sources/app2/.env.$$env 2>/dev/null | cut -d'=' -f2-); \
				if [ -z "$$val" ]; then \
					missing="$$missing $$var"; \
				fi; \
			done; \
			if [ -n "$$missing" ]; then \
				echo "  ⚠️  .env.$$env - Variables vides:$$missing"; \
				errors=$$((errors + 1)); \
			else \
				echo "  ✅ .env.$$env configuré"; \
			fi; \
		fi; \
	done; \
	echo ""; \
	\
	echo "📁 sources/app4/.env.* (depuis sources/app4/.env.dist)"; \
	if [ ! -f sources/app4/.env.dist ]; then \
		echo "  ❌ Fichier modèle manquant: sources/app4/.env.dist"; \
		errors=$$((errors + 1)); \
	else \
		echo "  ✅ Fichier modèle existe"; \
	fi; \
	for env in development preprod production; do \
		if [ ! -f sources/app4/.env.$$env ]; then \
			echo "  ❌ Fichier manquant: sources/app4/.env.$$env"; \
			echo "     → Exécutez: make init_env_app4"; \
			errors=$$((errors + 1)); \
		else \
			missing=""; \
			for var in API2_BASE_URL; do \
				val=$$(grep "^$$var=" sources/app4/.env.$$env 2>/dev/null | cut -d'=' -f2-); \
				if [ -z "$$val" ]; then \
					missing="$$missing $$var"; \
				fi; \
			done; \
			if [ -n "$$missing" ]; then \
				echo "  ⚠️  .env.$$env - Variables vides:$$missing"; \
				errors=$$((errors + 1)); \
			else \
				echo "  ✅ .env.$$env configuré"; \
			fi; \
		fi; \
	done; \
	echo ""; \
	\
	if [ $$errors -gt 0 ]; then \
		echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"; \
		echo "❌ $$errors problème(s) détecté(s)"; \
		echo ""; \
		echo "💡 Pour initialiser les fichiers manquants: make init"; \
		exit 1; \
	else \
		echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"; \
		echo "✅ Tous les fichiers d'environnement sont configurés!"; \
	fi


## DOCKER - DÉVELOPPEMENT
docker_dev_up: ## Lance les containers Docker en mode développement
	$(DOCKER_COMPOSE) -f docker/compose.dev.yaml up -d

docker_dev_down: ## Arrête les containers Docker de développement
	$(DOCKER_COMPOSE) -f docker/compose.dev.yaml down

docker_dev_restart: ## Redémarre les containers Docker de développement
	$(DOCKER_COMPOSE) -f docker/compose.dev.yaml restart

docker_dev_rebuild: ## Reconstruit et relance les containers de développement (après modif Dockerfile)
	@echo "Reconstruction des images Docker (développement)..."
	$(DOCKER_COMPOSE) -f docker/compose.dev.yaml down
	$(DOCKER_COMPOSE) -f docker/compose.dev.yaml build --no-cache
	$(DOCKER_COMPOSE) -f docker/compose.dev.yaml up -d
	@echo "Containers reconstruits et relancés"

docker_dev_logs: ## Affiche les logs des containers de développement
	$(DOCKER_COMPOSE) -f docker/compose.dev.yaml logs -f

docker_dev_status: ## Affiche le statut des containers de développement
	$(DOCKER_COMPOSE) -f docker/compose.dev.yaml ps


## DOCKER - PRÉ-PRODUCTION
docker_preprod_up: ## Lance les containers Docker en mode pré-production
	$(DOCKER_COMPOSE) -f docker/compose.preprod.yaml up -d

docker_preprod_down: ## Arrête les containers Docker de pré-production
	$(DOCKER_COMPOSE) -f docker/compose.preprod.yaml down

docker_preprod_restart: ## Redémarre les containers Docker de pré-production
	$(DOCKER_COMPOSE) -f docker/compose.preprod.yaml restart

docker_preprod_rebuild: ## Reconstruit et relance les containers de pré-production (après modif Dockerfile)
	@echo "Reconstruction des images Docker (pré-production)..."
	$(DOCKER_COMPOSE) -f docker/compose.preprod.yaml down
	$(DOCKER_COMPOSE) -f docker/compose.preprod.yaml build --no-cache
	$(DOCKER_COMPOSE) -f docker/compose.preprod.yaml up -d
	@echo "Containers reconstruits et relancés"

docker_preprod_logs: ## Affiche les logs des containers de pré-production
	$(DOCKER_COMPOSE) -f docker/compose.preprod.yaml logs -f

docker_preprod_status: ## Affiche le statut des containers de pré-production
	$(DOCKER_COMPOSE) -f docker/compose.preprod.yaml ps


## DOCKER - PRODUCTION
docker_prod_up: ## Lance les containers Docker en mode production
	$(DOCKER_COMPOSE) -f docker/compose.prod.yaml up -d

docker_prod_down: ## Arrête les containers Docker de production
	$(DOCKER_COMPOSE) -f docker/compose.prod.yaml down

docker_prod_restart: ## Redémarre les containers Docker de production
	$(DOCKER_COMPOSE) -f docker/compose.prod.yaml restart

docker_prod_rebuild: ## Reconstruit et relance les containers de production (après modif Dockerfile)
	@echo "Reconstruction des images Docker (production)..."
	$(DOCKER_COMPOSE) -f docker/compose.prod.yaml down
	$(DOCKER_COMPOSE) -f docker/compose.prod.yaml build --no-cache
	$(DOCKER_COMPOSE) -f docker/compose.prod.yaml up -d
	@echo "Containers reconstruits et relancés"

docker_prod_logs: ## Affiche les logs des containers de production
	$(DOCKER_COMPOSE) -f docker/compose.prod.yaml logs -f

docker_prod_status: ## Affiche le statut des containers de production
	$(DOCKER_COMPOSE) -f docker/compose.prod.yaml ps


## APP2 - NUXT (Scrutineering/Charts)
app2_dev: ## Lance le serveur Nuxt (app2) en mode développement (port 3002)
	$(DOCKER_EXEC_NODE) sh -c "npm run dev"

app2_build: ## Build l'application Nuxt (app2) pour la production
	$(DOCKER_EXEC_NODE_NON_INTERACTIVE) sh -c "npm run build"

app2_generate_dev: ## Génère l'application Nuxt (app2) en mode statique pour développement
	$(DOCKER_EXEC_NODE_NON_INTERACTIVE) sh -c "npx dotenv-cli -e .env.development -- nuxt generate"
	@echo "Restarting nginx to remount volume..."
	docker restart $(APPLICATION_NAME)_nginx_app2 > /dev/null
	@echo "App2 generated and nginx restarted!"

app2_generate_preprod: ## Génère l'application Nuxt (app2) en mode statique pour pré-production (utilise container temporaire)
	@echo "Building app2 for pre-production using temporary Node.js container..."
	docker run --rm -v "$(CURDIR)/sources/app2:/app" -w /app node:20-alpine sh -c "npm ci && npx dotenv-cli -e .env.preprod -- nuxt generate"
	@echo "Restarting nginx to remount volume..."
	docker restart $(APPLICATION_NAME)_nginx_app2 > /dev/null
	@echo "App2 generated and nginx restarted!"

app2_generate_production: ## Génère l'application Nuxt (app2) en mode statique pour production (utilise container temporaire)
	@echo "Building app2 for production using temporary Node.js container..."
	docker run --rm -v "$(CURDIR)/sources/app2:/app" -w /app node:20-alpine sh -c "npm ci && npx dotenv-cli -e .env.production -- nuxt generate"
	@echo "Restarting nginx to remount volume..."
	docker restart $(APPLICATION_NAME)_nginx_app2 > /dev/null
	@echo "App2 generated and nginx restarted!"

app2_generate_prod: app2_generate_production ## Alias pour app2_generate_production

app2_lint: ## Exécute ESLint sur app2
	$(DOCKER_EXEC_NODE) sh -c "npm run lint"

app2_bash: ## Ouvre un shell dans le container Node (app2)
	$(DOCKER_EXEC_NODE) sh


## APP2 - NPM
app2_npm_install: ## Installe toutes les dépendances npm pour app2
	@echo "Installation des dépendances npm pour app2 (container: $(NODE_CONTAINER_NAME))..."
	$(DOCKER_EXEC_NODE) sh -c "npm install"

app2_npm_ls: ## Liste les modules npm installés dans app2
	@echo "Modules npm dans app2 (container: $(NODE_CONTAINER_NAME)):"
	$(DOCKER_EXEC_NODE) sh -c "ls -l node_modules/@nuxtjs"

app2_npm_clean: ## Supprime node_modules et package-lock.json de app2
	@echo "Nettoyage de node_modules pour app2 (container: $(NODE_CONTAINER_NAME))..."
	$(DOCKER_EXEC_NODE) sh -c "rm -rf node_modules package-lock.json"

app2_npm_update: ## Met à jour toutes les dépendances npm de app2
	@echo "Mise à jour des dépendances npm pour app2 (container: $(NODE_CONTAINER_NAME))..."
	$(DOCKER_EXEC_NODE) sh -c "npm update"

app2_npm_add: ## Ajoute un package npm à app2 (usage: make app2_npm_add package=uuid)
	@echo "Ajout du package $(package) pour app2 (container: $(NODE_CONTAINER_NAME))..."
	$(DOCKER_EXEC_NODE) sh -c "npm install $(package)"

app2_npm_add_dev: ## Ajoute un package npm de dev à app2 (usage: make app2_npm_add_dev package=eslint)
	@echo "Ajout du package de dev $(package) pour app2 (container: $(NODE_CONTAINER_NAME))..."
	$(DOCKER_EXEC_NODE) sh -c "npm install -D $(package)"


## APP3 - NUXT (Match Sheet)
app3_dev: ## Lance le serveur Nuxt (app3) en mode développement (port 3003)
	$(DOCKER_EXEC_NODE3) sh -c "npm run dev"

app3_build: ## Build l'application Nuxt (app3) pour la production
	$(DOCKER_EXEC_NODE3_NON_INTERACTIVE) sh -c "npm run build"

app3_generate_dev: ## Génère l'application Nuxt (app3) en mode statique pour développement
	$(DOCKER_EXEC_NODE3_NON_INTERACTIVE) sh -c "npx dotenv-cli -e .env.development -- nuxt generate"

app3_generate_preprod: ## Génère l'application Nuxt (app3) en mode statique pour pré-production (utilise container temporaire)
	@echo "Building app3 for pre-production using temporary Node.js container..."
	docker run --rm -v "$(CURDIR)/sources/app3:/app" -w /app node:20-alpine sh -c "npm ci && npx dotenv-cli -e .env.preprod -- nuxt generate"
	@echo "Build complete! Files are in sources/app3/.output/public/"

app3_generate_prod: ## Génère l'application Nuxt (app3) en mode statique pour production (utilise container temporaire)
	@echo "Building app3 for production using temporary Node.js container..."
	docker run --rm -v "$(CURDIR)/sources/app3:/app" -w /app node:20-alpine sh -c "npm ci && npx dotenv-cli -e .env.production -- nuxt generate"
	@echo "Build complete! Files are in sources/app3/.output/public/"

app3_lint: ## Exécute ESLint sur app3
	$(DOCKER_EXEC_NODE3) sh -c "npm run lint"

app3_bash: ## Ouvre un shell dans le container Node (app3)
	$(DOCKER_EXEC_NODE3) sh


## APP3 - NPM
app3_npm_install: ## Installe toutes les dépendances npm pour app3
	@echo "Installation des dépendances npm pour app3 (container: $(NODE3_CONTAINER_NAME))..."
	$(DOCKER_EXEC_NODE3) sh -c "npm install"

app3_npm_ls: ## Liste les modules npm installés dans app3
	@echo "Modules npm dans app3 (container: $(NODE3_CONTAINER_NAME)):"
	$(DOCKER_EXEC_NODE3) sh -c "ls -l node_modules/@nuxtjs"

app3_npm_clean: ## Supprime node_modules et package-lock.json de app3
	@echo "Nettoyage de node_modules pour app3 (container: $(NODE3_CONTAINER_NAME))..."
	$(DOCKER_EXEC_NODE3) sh -c "rm -rf node_modules package-lock.json"

app3_npm_update: ## Met à jour toutes les dépendances npm de app3
	@echo "Mise à jour des dépendances npm pour app3 (container: $(NODE3_CONTAINER_NAME))..."
	$(DOCKER_EXEC_NODE3) sh -c "npm update"

app3_npm_add: ## Ajoute un package npm à app3 (usage: make app3_npm_add package=uuid)
	@echo "Ajout du package $(package) pour app3 (container: $(NODE3_CONTAINER_NAME))..."
	$(DOCKER_EXEC_NODE3) sh -c "npm install $(package)"

app3_npm_add_dev: ## Ajoute un package npm de dev à app3 (usage: make app3_npm_add_dev package=eslint)
	@echo "Ajout du package de dev $(package) pour app3 (container: $(NODE3_CONTAINER_NAME))..."
	$(DOCKER_EXEC_NODE3) sh -c "npm install -D $(package)"


## APP4 - NUXT (Admin)
app4_dev: ## Lance le serveur Nuxt (app4 admin) en mode développement (port 3004)
	$(DOCKER_EXEC_NODE4) sh -c "npm run dev"

app4_build: ## Build l'application Nuxt (app4 admin) pour la production
	$(DOCKER_EXEC_NODE4_NON_INTERACTIVE) sh -c "npm run build"

app4_generate: ## Génère l'application Nuxt (app4 admin) en mode statique (utilise .env)
	$(DOCKER_EXEC_NODE4_NON_INTERACTIVE) sh -c "nuxt generate"
	@echo "Restarting nginx to remount volume..."
	docker restart $(APPLICATION_NAME)_nginx_app4 > /dev/null
	@echo "App4 generated and nginx restarted!"

app4_generate_tmp: ## Génère l'application Nuxt (app4 admin) via container temporaire (utilise .env, pour preprod/prod)
	@echo "Building app4 using temporary Node.js container..."
	docker run --rm -v "$(CURDIR)/sources/app4:/app" -w /app node:20-alpine sh -c "npm ci && npx nuxt generate"
	@echo "Restarting nginx to remount volume..."
	docker restart $(APPLICATION_NAME)_nginx_app4 > /dev/null
	@echo "App4 generated and nginx restarted!"

app4_lint: ## Exécute ESLint sur app4
	$(DOCKER_EXEC_NODE4) sh -c "npm run lint"

app4_bash: ## Ouvre un shell dans le container Node (app4 admin)
	$(DOCKER_EXEC_NODE4) sh


## APP4 - NPM
app4_npm_install: ## Installe toutes les dépendances npm pour app4
	@echo "Installation des dépendances npm pour app4 (container: $(NODE4_CONTAINER_NAME))..."
	$(DOCKER_EXEC_NODE4) sh -c "npm install"

app4_npm_ls: ## Liste les modules npm installés dans app4
	@echo "Modules npm dans app4 (container: $(NODE4_CONTAINER_NAME)):"
	$(DOCKER_EXEC_NODE4) sh -c "ls -l node_modules/@nuxtjs"

app4_npm_clean: ## Supprime node_modules et package-lock.json de app4
	@echo "Nettoyage de node_modules pour app4 (container: $(NODE4_CONTAINER_NAME))..."
	$(DOCKER_EXEC_NODE4) sh -c "rm -rf node_modules package-lock.json"

app4_npm_update: ## Met à jour toutes les dépendances npm de app4
	@echo "Mise à jour des dépendances npm pour app4 (container: $(NODE4_CONTAINER_NAME))..."
	$(DOCKER_EXEC_NODE4) sh -c "npm update"

app4_npm_add: ## Ajoute un package npm à app4 (usage: make app4_npm_add package=uuid)
	@echo "Ajout du package $(package) pour app4 (container: $(NODE4_CONTAINER_NAME))..."
	$(DOCKER_EXEC_NODE4) sh -c "npm install $(package)"

app4_npm_add_dev: ## Ajoute un package npm de dev à app4 (usage: make app4_npm_add_dev package=eslint)
	@echo "Ajout du package de dev $(package) pour app4 (container: $(NODE4_CONTAINER_NAME))..."
	$(DOCKER_EXEC_NODE4) sh -c "npm install -D $(package)"


## BACKEND - COMPOSER (PHP)
backend_composer_install: ## Installe les dépendances Composer (sources/vendor/)
	@echo "Installation des dépendances Composer (container: $(PHP_CONTAINER_NAME))..."
	$(DOCKER_EXEC_PHP_NON_INTERACTIVE) bash -c "cd /var/www/html && composer install"
	@echo "Dépendances Composer installées"

backend_composer_update: ## Met à jour les dépendances Composer
	@echo "Mise à jour des dépendances Composer (container: $(PHP_CONTAINER_NAME))..."
	$(DOCKER_EXEC_PHP_NON_INTERACTIVE) bash -c "cd /var/www/html && composer update"
	@echo "Dépendances Composer mises à jour"

backend_composer_require: ## Ajoute un package Composer (usage: make backend_composer_require package=vendor/package)
	@if [ -z "$(package)" ]; then \
		echo "Erreur: spécifiez un package (make backend_composer_require package=vendor/package)"; \
		exit 1; \
	fi
	@echo "Ajout du package $(package) (container: $(PHP_CONTAINER_NAME))..."
	$(DOCKER_EXEC_PHP_NON_INTERACTIVE) bash -c "cd /var/www/html && composer require $(package)"
	@echo "Package $(package) ajouté"

backend_composer_require_dev: ## Ajoute un package Composer de dev (usage: make backend_composer_require_dev package=vendor/package)
	@if [ -z "$(package)" ]; then \
		echo "Erreur: spécifiez un package (make backend_composer_require_dev package=vendor/package)"; \
		exit 1; \
	fi
	@echo "Ajout du package de dev $(package) (container: $(PHP_CONTAINER_NAME))..."
	$(DOCKER_EXEC_PHP_NON_INTERACTIVE) bash -c "cd /var/www/html && composer require --dev $(package)"
	@echo "Package de dev $(package) ajouté"

backend_composer_dump: ## Regénère l'autoloader Composer
	@echo "Regénération de l'autoloader Composer (container: $(PHP_CONTAINER_NAME))..."
	$(DOCKER_EXEC_PHP_NON_INTERACTIVE) bash -c "cd /var/www/html && composer dump-autoload"
	@echo "Autoloader Composer regénéré"

backend_bash: ## Ouvre un shell bash dans le container PHP
	$(DOCKER_EXEC_PHP) bash


## BACKEND - NPM (JavaScript Libraries)
backend_npm_install: ## Installe les dépendances npm du backend (sources/package.json) via container temporaire
	@if [ ! -f sources/package.json ]; then \
		echo "Aucun package.json trouvé dans sources/"; \
		echo "Créez d'abord sources/package.json avec: make backend_npm_init"; \
		exit 1; \
	fi
	@echo "Installation des dépendances JavaScript du backend..."
	@docker run --rm -v $(PWD)/sources:/app -w /app node:20-alpine sh -c "npm install"
	@echo "Dépendances installées dans sources/node_modules/"

backend_npm_init: ## Initialise package.json dans sources/ (si absent)
	@if [ -f sources/package.json ]; then \
		echo "Le fichier sources/package.json existe déjà"; \
		exit 1; \
	fi
	@echo "Création de package.json dans sources/..."
	@docker run --rm -v $(PWD)/sources:/app -w /app node:20-alpine sh -c "npm init -y"
	@echo "Fichier package.json créé dans sources/"
	@echo "Modifiez sources/package.json puis lancez: make backend_npm_install"

backend_npm_add: ## Ajoute un package npm au backend (usage: make backend_npm_add package=flatpickr)
	@if [ -z "$(package)" ]; then \
		echo "Erreur: spécifiez un package (make backend_npm_add package=flatpickr)"; \
		exit 1; \
	fi
	@if [ ! -f sources/package.json ]; then \
		echo "Aucun package.json trouvé. Initialisation..."; \
		$(MAKE) backend_npm_init; \
	fi
	@echo "Installation de $(package)..."
	@docker run --rm -v $(PWD)/sources:/app -w /app node:20-alpine sh -c "npm install $(package)"
	@echo "Package $(package) installé"
	@echo "Fichiers disponibles dans sources/node_modules/$(package)/"

backend_npm_update: ## Met à jour les dépendances npm du backend
	@if [ ! -f sources/package.json ]; then \
		echo "Aucun package.json trouvé dans sources/"; \
		exit 1; \
	fi
	@echo "Mise à jour des dépendances JavaScript du backend..."
	@docker run --rm -v $(PWD)/sources:/app -w /app node:20-alpine sh -c "npm update"
	@echo "Dépendances mises à jour"

backend_npm_ls: ## Liste les packages npm installés dans le backend
	@if [ ! -d sources/node_modules ]; then \
		echo "Aucun node_modules trouvé dans sources/"; \
		echo "Lancez d'abord: make backend_npm_install"; \
		exit 1; \
	fi
	@echo "Packages npm installés dans sources/:"
	@docker run --rm -v $(PWD)/sources:/app -w /app node:20-alpine sh -c "npm list --depth=0"

backend_npm_clean: ## Supprime node_modules du backend (attention: supprime toutes les libs JS)
	@echo "Suppression de sources/node_modules..."
	@rm -rf sources/node_modules
	@rm -f sources/package-lock.json
	@echo "node_modules et package-lock.json supprimés"


## API2 - SYMFONY (Symfony 7.3 + API Platform 4.2)
api2_composer_install: ## Installe les dépendances Composer pour API2 (Symfony)
	@echo "Installation des dépendances Composer pour API2 (container: $(PHP_CONTAINER_NAME))..."
	$(DOCKER_EXEC_PHP_NON_INTERACTIVE) bash -c "cd /var/www/html/api2 && composer install --no-interaction --prefer-dist --optimize-autoloader"
	@echo "Dépendances Composer installées pour API2"

api2_composer_update: ## Met à jour les dépendances Composer pour API2
	@echo "Mise à jour des dépendances Composer pour API2 (container: $(PHP_CONTAINER_NAME))..."
	$(DOCKER_EXEC_PHP_NON_INTERACTIVE) bash -c "cd /var/www/html/api2 && composer update --no-interaction"
	@echo "Dépendances Composer mises à jour pour API2"

api2_composer_require: ## Ajoute un package Composer à API2 (usage: make api2_composer_require package=vendor/package)
	@if [ -z "$(package)" ]; then \
		echo "Erreur: spécifiez un package (make api2_composer_require package=vendor/package)"; \
		exit 1; \
	fi
	@echo "Ajout du package $(package) à API2 (container: $(PHP_CONTAINER_NAME))..."
	$(DOCKER_EXEC_PHP_NON_INTERACTIVE) bash -c "cd /var/www/html/api2 && composer require $(package) --no-interaction"
	@echo "Package $(package) ajouté à API2"

api2_cache_clear: ## Vide le cache Symfony de API2
	@echo "Vidage du cache Symfony pour API2 (container: $(PHP_CONTAINER_NAME))..."
	$(DOCKER_EXEC_PHP_NON_INTERACTIVE) bash -c "cd /var/www/html/api2 && php bin/console cache:clear"
	@echo "Cache Symfony vidé pour API2"

api2_cache_warmup: ## Préchauffe le cache Symfony de API2
	@echo "Préchauffage du cache Symfony pour API2 (container: $(PHP_CONTAINER_NAME))..."
	$(DOCKER_EXEC_PHP_NON_INTERACTIVE) bash -c "cd /var/www/html/api2 && php bin/console cache:warmup"
	@echo "Cache Symfony préchauffé pour API2"

api2_migrations_diff: ## Génère une migration Doctrine pour API2 (détecte les changements)
	@echo "Génération d'une migration Doctrine pour API2 (container: $(PHP_CONTAINER_NAME))..."
	$(DOCKER_EXEC_PHP_NON_INTERACTIVE) bash -c "cd /var/www/html/api2 && php bin/console doctrine:migrations:diff"
	@echo "Migration générée pour API2"

api2_migrations_migrate: ## Exécute les migrations Doctrine pour API2
	@echo "Exécution des migrations Doctrine pour API2 (container: $(PHP_CONTAINER_NAME))..."
	$(DOCKER_EXEC_PHP_NON_INTERACTIVE) bash -c "cd /var/www/html/api2 && php bin/console doctrine:migrations:migrate --no-interaction"
	@echo "Migrations exécutées pour API2"

api2_jwt_generate_keys: ## Génère les clés RSA pour JWT (API2) - reproductible sur chaque environnement
	@echo "Génération des clés JWT pour API2..."
	@if [ ! -f sources/api2/.env ]; then \
		echo "Erreur: sources/api2/.env n'existe pas"; \
		echo "   Exécutez d'abord: make init_env_api2"; \
		exit 1; \
	fi
	$(DOCKER_EXEC_PHP_NON_INTERACTIVE) bash -c "cd /var/www/html/api2 && \
		if ! grep -q '^JWT_PASSPHRASE=' .env; then \
			echo 'Erreur: JWT_PASSPHRASE non défini dans sources/api2/.env'; \
			echo '   Exemple: JWT_PASSPHRASE=votre_passphrase_secrete'; \
			exit 1; \
		fi && \
		export \$$(grep -v '^#' .env | grep JWT_PASSPHRASE | xargs) && \
		mkdir -p config/jwt && \
		openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:\$$JWT_PASSPHRASE && \
		openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout -passin pass:\$$JWT_PASSPHRASE && \
		chmod 644 config/jwt/public.pem && \
		chmod 600 config/jwt/private.pem"
	@echo "Clés JWT générées dans sources/api2/config/jwt/"
	@echo "   - private.pem (clé privée, chmod 600)"
	@echo "   - public.pem (clé publique, chmod 644)"
	@echo ""
	@echo "Commande à exécuter aussi sur preprod/prod avec le même JWT_PASSPHRASE"


## ACCÈS SHELLS
db_bash: ## Ouvre un shell dans le container MySQL
	docker exec -ti $(DB_CONTAINER_NAME) sh


## BACKEND - EVENT WORKER
backend_worker_start: ## Démarre le worker d'événements en arrière-plan
	@echo "Démarrage du worker d'événements..."
	@echo "Création du dossier de logs si nécessaire..."
	@$(DOCKER_EXEC_PHP_NON_INTERACTIVE) bash -c "mkdir -p /var/www/html/live/logs && chmod 755 /var/www/html/live/logs"
	@echo "Lancement du processus worker..."
	@$(DOCKER_EXEC_PHP_NON_INTERACTIVE) bash -c "nohup php /var/www/html/live/event_worker.php > /var/www/html/live/logs/event_worker.log 2>&1 &"
	@sleep 2
	@echo "Worker démarré en arrière-plan"
	@echo "Vérifiez le statut avec: make backend_worker_status"
	@echo "Consultez les logs avec: make backend_worker_logs"

backend_worker_stop: ## Arrête le worker d'événements
	@echo "Arrêt du worker d'événements..."
	-@$(DOCKER_EXEC_PHP_NON_INTERACTIVE) bash -c "pkill -f event_worker.php" 2>/dev/null || true
	@echo "Worker arrêté"
	@echo "Note: Vous pouvez aussi arrêter via l'interface web (sources/live/event.php)"

backend_worker_status: ## Affiche le statut du worker d'événements
	@echo "Statut du worker d'événements:"
	@$(DOCKER_EXEC_PHP_NON_INTERACTIVE) bash -c 'if pgrep -f event_worker.php > /dev/null; then \
		echo "  Worker en cours d'"'"'exécution"; \
		echo "  PID: $$(pgrep -f event_worker.php)"; \
	else \
		echo "  Worker arrêté"; \
	fi'
	@echo "Pour plus de détails, accédez à l'interface web: sources/live/event.php"

backend_worker_logs: ## Affiche les logs du worker d'événements
	@echo "Logs du worker d'événements (Ctrl+C pour quitter):"
	@echo "---"
	@$(DOCKER_EXEC_PHP_NON_INTERACTIVE) bash -c 'tail -f /var/www/html/live/logs/event_worker.log 2>/dev/null || echo "Aucun log disponible. Le worker n'"'"'a peut-être pas encore été démarré."'

backend_worker_restart: ## Redémarre le worker d'événements
	@echo "Redémarrage du worker d'événements..."
	@$(MAKE) backend_worker_stop
	@sleep 2
	@$(MAKE) backend_worker_start


## DOCKER - RÉSEAUX
docker_networks_create: ## Crée les réseaux Docker nécessaires (network_${APPLICATION_NAME}, pma_network, traefiknetwork)
	@echo "Création des réseaux Docker..."
	@echo "Nom du réseau KPI: $(NETWORK_KPI_NAME)"
	@docker network inspect $(NETWORK_KPI_NAME) >/dev/null 2>&1 || \
		(docker network create $(NETWORK_KPI_NAME) && echo "Réseau $(NETWORK_KPI_NAME) créé") || \
		echo "Le réseau $(NETWORK_KPI_NAME) existe déjà"
	@docker network inspect pma_network >/dev/null 2>&1 || \
		(docker network create pma_network && echo "Réseau pma_network créé") || \
		echo "Le réseau pma_network existe déjà"
	@docker network inspect traefiknetwork >/dev/null 2>&1 || \
		(docker network create traefiknetwork && echo "Réseau traefiknetwork créé") || \
		echo "Le réseau traefiknetwork existe déjà"
	@echo "Tous les réseaux sont prêts"

docker_networks_list: ## Liste tous les réseaux Docker du projet
	@echo "Réseaux Docker du projet KPI (APPLICATION_NAME=$(APPLICATION_NAME)):"
	@docker network ls | grep -E "$(NETWORK_KPI_NAME)|pma_network|traefiknetwork" || echo "Aucun réseau trouvé"

docker_networks_clean: ## Supprime les réseaux Docker du projet (attention: seulement si non utilisés)
	@echo "Suppression des réseaux Docker..."
	@docker network rm $(NETWORK_KPI_NAME) 2>/dev/null && echo "Réseau $(NETWORK_KPI_NAME) supprimé" || echo "$(NETWORK_KPI_NAME) n'existe pas ou est utilisé"
	@docker network rm pma_network 2>/dev/null && echo "Réseau pma_network supprimé" || echo "pma_network n'existe pas ou est utilisé"
	@docker network rm traefiknetwork 2>/dev/null && echo "Réseau traefiknetwork supprimé" || echo "traefiknetwork n'existe pas ou est utilisé"


## WORDPRESS
wordpress_backup: ## Crée une sauvegarde du dossier WordPress
	@echo "Création d'une sauvegarde de WordPress..."
	@tar -czf docker/wordpress_backup_$$(date +%Y%m%d_%H%M%S).tar.gz -C docker/wordpress . 2>/dev/null || \
		(echo "Échec: le dossier docker/wordpress n'existe pas ou est vide" && exit 1)
	@echo "Sauvegarde créée dans docker/"

wordpress_restore: ## Restaure WordPress depuis /tmp/wordpress_backup (usage interne)
	@if [ -d /tmp/wordpress_backup ]; then \
		echo "Restauration de WordPress..."; \
		mkdir -p docker/wordpress; \
		cp -r /tmp/wordpress_backup/* docker/wordpress/; \
		echo "WordPress restauré dans docker/wordpress/"; \
	else \
		echo "Erreur: /tmp/wordpress_backup n'existe pas"; \
		exit 1; \
	fi
