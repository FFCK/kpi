-include .env

UID := $(shell id -u)
GID := $(shell id -g)
export USER_ID := $(UID)
export GROUP_ID := $(GID)

DOCKER_COMPOSE = docker compose
DOCKER_EXEC = docker exec -ti
COMPOSER_FILE = composer.$(PC_TYPE).json 
DOCKER_EXEC_PHP = docker exec -ti $(PHP_CONTAINER)
SYMFONY_CONSOLE = $(SYMFONY) symfony console
.DEFAULT_GOAL = help
HORDODATAGE_DUMP = $(shell date +%Y%m%d%H%M)


.PHONY: help init_env_app2 dev_up run_dev npm_install_app2 npm_ls_app2 npm_clean_app2 \
npm_update_app2 npm_add_app2 npm_add_dev_app2



help: ## Affiche cette aide
	@echo "Usage: make [commande]"
	@echo ""
	@grep -E '^[a-zA-Z0-9_-]+:.*##|^##[^#]' $(MAKEFILE_LIST) | \
		awk 'BEGIN {FS = ":.*## "}; \
		/^##/ {printf "\n\033[1;33m%s\033[0m\n", substr($$0, 4); next} \
		{printf "  \033[36m%-28s\033[0m %s\n", $$1, $$2}'


## PROJECT
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

## DOCKER
dev_up: ## Construit et lance les containers Docker en mode développement
	$(DOCKER_COMPOSE) -f docker/compose.dev.yaml up -d

run_dev: ## Lance le serveur Nuxt (app2) en mode développement (port 3002)
	docker exec kpi_node_app2 sh -c "npm run dev"

## NPM - APP2
npm_install_app2: ## Installe toutes les dépendances npm pour app2
	docker exec kpi_node_app2 sh -c "npm install"

npm_ls_app2: ## Liste les modules npm installés dans app2
	docker exec kpi_node_app2 sh -c "ls -l node_modules/@nuxtjs"

npm_clean_app2: ## Supprime node_modules et package-lock.json de app2
	docker exec kpi_node_app2 sh -c "rm -rf node_modules package-lock.json"

npm_update_app2: ## Met à jour toutes les dépendances npm de app2
	docker exec kpi_node_app2 sh -c "npm update"

npm_add_app2: ## Ajoute un package npm à app2 (usage: make npm_add_app2 package=uuid)
	docker exec kpi_node_app2 sh -c "npm install $(package)"

npm_add_dev_app2: ## Ajoute un package npm de dev à app2 (usage: make npm_add_dev_app2 package=eslint)
	docker exec kpi_node_app2 sh -c "npm install -D $(package)"

# stop: ## stoppe les containers
#	 $(DOCKER_COMPOSE) -f ./compose.yaml stop

# docker_exec_php: ## connexion au container php
#	 docker exec -ti $(PHP_CONTAINER) bash

# docker_exec_db: ## connexion au container db
#	 docker exec -ti $(POSTGRES_CONTAINER) sh

# docker_logs: ## affiche les logs php
#	 docker logs -f $(PHP_CONTAINER)

