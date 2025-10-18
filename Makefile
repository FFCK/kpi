-include docker/.env

UID := $(shell id -u)
GID := $(shell id -g)
export USER_ID := $(UID)
export GROUP_ID := $(GID)

DOCKER_COMPOSE = docker compose
DOCKER_EXEC = docker exec -ti
DOCKER_EXEC_PHP = docker exec -ti kpi_php
DOCKER_EXEC_PHP8 = docker exec -ti kpi_php8
DOCKER_EXEC_NODE = docker exec -ti kpi_node_app2
.DEFAULT_GOAL = help

.PHONY: help init_env init_env_app2 \
dev_up dev_down dev_restart dev_logs dev_status \
prod_up prod_down prod_restart prod_logs prod_status \
run_dev run_build run_generate run_lint \
npm_install_app2 npm_ls_app2 npm_clean_app2 npm_update_app2 npm_add_app2 npm_add_dev_app2 \
php_bash php8_bash node_bash db_bash \
wordpress_backup wordpress_restore



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
			printf "  \033[36m%-28s\033[0m %s\n", $$1, $$2; \
		}' $(MAKEFILE_LIST)


## INITIALISATION
init_env: ## Initialise le fichier docker/.env depuis docker/.env.dist
	@if [ ! -f docker/.env ]; then \
		cp docker/.env.dist docker/.env; \
		echo "✅ Fichier docker/.env créé"; \
		echo "⚠️  N'oubliez pas de configurer les variables dans docker/.env"; \
	else \
		echo "⚠️  Le fichier docker/.env existe déjà"; \
	fi

init_env_app2: ## Initialise les fichiers .env.development et .env.production pour app2
	@if [ ! -f sources/app2/.env.development ]; then \
		cp sources/app2/.env.development.example sources/app2/.env.development; \
		echo "✅ Fichier .env.development créé pour app2"; \
	else \
		echo "⚠️  Le fichier .env.development existe déjà pour app2"; \
	fi
	@if [ ! -f sources/app2/.env.production ]; then \
		cp sources/app2/.env.production.example sources/app2/.env.production; \
		echo "✅ Fichier .env.production créé pour app2"; \
	else \
		echo "⚠️  Le fichier .env.production existe déjà pour app2"; \
	fi


## DOCKER - DÉVELOPPEMENT
dev_up: ## Lance les containers Docker en mode développement
	$(DOCKER_COMPOSE) -f docker/compose.dev.yaml up -d

dev_down: ## Arrête les containers Docker de développement
	$(DOCKER_COMPOSE) -f docker/compose.dev.yaml down

dev_restart: ## Redémarre les containers Docker de développement
	$(DOCKER_COMPOSE) -f docker/compose.dev.yaml restart

dev_logs: ## Affiche les logs des containers de développement
	$(DOCKER_COMPOSE) -f docker/compose.dev.yaml logs -f

dev_status: ## Affiche le statut des containers de développement
	$(DOCKER_COMPOSE) -f docker/compose.dev.yaml ps


## DOCKER - PRODUCTION
prod_up: ## Lance les containers Docker en mode production
	$(DOCKER_COMPOSE) -f docker/compose.prod.yaml up -d

prod_down: ## Arrête les containers Docker de production
	$(DOCKER_COMPOSE) -f docker/compose.prod.yaml down

prod_restart: ## Redémarre les containers Docker de production
	$(DOCKER_COMPOSE) -f docker/compose.prod.yaml restart

prod_logs: ## Affiche les logs des containers de production
	$(DOCKER_COMPOSE) -f docker/compose.prod.yaml logs -f

prod_status: ## Affiche le statut des containers de production
	$(DOCKER_COMPOSE) -f docker/compose.prod.yaml ps


## NUXT - APP2
run_dev: ## Lance le serveur Nuxt (app2) en mode développement (port 3002)
	$(DOCKER_EXEC_NODE) sh -c "npm run dev"

run_build: ## Build l'application Nuxt (app2) pour la production
	$(DOCKER_EXEC_NODE) sh -c "npm run build"

run_generate: ## Génère l'application Nuxt (app2) en mode statique
	$(DOCKER_EXEC_NODE) sh -c "npm run generate"

run_lint: ## Exécute ESLint sur app2
	$(DOCKER_EXEC_NODE) sh -c "npm run lint"


## NPM - APP2
npm_install_app2: ## Installe toutes les dépendances npm pour app2
	$(DOCKER_EXEC_NODE) sh -c "npm install"

npm_ls_app2: ## Liste les modules npm installés dans app2
	$(DOCKER_EXEC_NODE) sh -c "ls -l node_modules/@nuxtjs"

npm_clean_app2: ## Supprime node_modules et package-lock.json de app2
	$(DOCKER_EXEC_NODE) sh -c "rm -rf node_modules package-lock.json"

npm_update_app2: ## Met à jour toutes les dépendances npm de app2
	$(DOCKER_EXEC_NODE) sh -c "npm update"

npm_add_app2: ## Ajoute un package npm à app2 (usage: make npm_add_app2 package=uuid)
	$(DOCKER_EXEC_NODE) sh -c "npm install $(package)"

npm_add_dev_app2: ## Ajoute un package npm de dev à app2 (usage: make npm_add_dev_app2 package=eslint)
	$(DOCKER_EXEC_NODE) sh -c "npm install -D $(package)"


## ACCÈS SHELLS
php_bash: ## Ouvre un shell bash dans le container PHP 7.4
	$(DOCKER_EXEC_PHP) bash

php8_bash: ## Ouvre un shell bash dans le container PHP 8
	$(DOCKER_EXEC_PHP8) bash

node_bash: ## Ouvre un shell bash dans le container Node (app2)
	$(DOCKER_EXEC_NODE) sh

db_bash: ## Ouvre un shell dans le container MySQL
	docker exec -ti kpi_db sh


## WORDPRESS
wordpress_backup: ## Crée une sauvegarde du dossier WordPress
	@echo "Création d'une sauvegarde de WordPress..."
	@tar -czf docker/wordpress_backup_$$(date +%Y%m%d_%H%M%S).tar.gz -C docker/wordpress . 2>/dev/null || \
		(echo "⚠️  Échec: le dossier docker/wordpress n'existe pas ou est vide" && exit 1)
	@echo "✅ Sauvegarde créée dans docker/"

wordpress_restore: ## Restaure WordPress depuis /tmp/wordpress_backup (usage interne)
	@if [ -d /tmp/wordpress_backup ]; then \
		echo "Restauration de WordPress..."; \
		mkdir -p docker/wordpress; \
		cp -r /tmp/wordpress_backup/* docker/wordpress/; \
		echo "✅ WordPress restauré dans docker/wordpress/"; \
	else \
		echo "❌ Erreur: /tmp/wordpress_backup n'existe pas"; \
		exit 1; \
	fi
