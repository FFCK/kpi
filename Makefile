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


.PHONY: help init start clean bundle up stop docker_exec_php docker_exec_db docker_logs \
composer_install composer_update composer_show composer_validate composer_require composer_require_dev composer_remove dodacre dodadrop domakemi domimi dofilo assets_install cache_clear entity crud assets_install dbdump



# AUTODOC # analyse automatiquement le Makefile, chaque commande et ses commentaires sur la même ligne après deux hashtags + espace
help: ## cette aide 
	@grep -E '(^[a-zA-Z_-]+:.*?##|^##).*$$' Makefile | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'


########## PROJECT ##########
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

# init: ## Initialise le projet en créant le fichier .env
#	 ./init_project.sh

# start: start1 composer_install domimi dofilo assets_install urlexpose ## vérifie .env, docker, network, lance les containers, composer install, assets install
# start1:
#	 -./start.sh
# urlexpose: ## Expose les url du projet
#	 - ./urlexpose.sh


# clean: dodadrop clean1 ## nettoie le projet (var, vendor, public_bundles, gitbundle)
# clean1:
#	# $(eval CONFIRM := $(shell read -p "Are you sure you want to reset the database? [y/N] " CONFIRM && echo $${CONFIRM:-N}))
#	# ifeq ("$(CONFIRM)", "y")
#	 -rm -rf var
#	 -rm -rf vendor
#	endif
#	-rm -rf gitbundle/*


########## GIT ##########
# bundle: ## création d'un gitbundle normalisé
#	 ./create_bundle.sh


########## DOCKER ##########
dev_up: ## (construit et) lance les containers
	$(DOCKER_COMPOSE) -f docker/compose.dev.yaml up -d

run_dev: ## lance le serveur Nuxt en mode dev
	docker exec kpi_node_app2 sh -c "npm run dev"

npm_install_app2: ## Installe les dépendances npm pour app2
	docker exec kpi_node_app2 sh -c "npm install"

npm_ls_app2: ## Liste les modules npm pour app2
	docker exec kpi_node_app2 sh -c "ls -l node_modules/@nuxtjs"

npm_clean_app2: ## Supprime les node_modules et le package-lock de app2
	docker exec kpi_node_app2 sh -c "rm -rf node_modules package-lock.json"

npm_update_app2: ## Met à jour les dépendances npm pour app2
	docker exec kpi_node_app2 sh -c "npm update"

npm_add_app2: ## Installe un paquet npm specifique pour app2 (ex: make npm_add_app2 package=uuid)
	docker exec kpi_node_app2 sh -c "npm install $(package)"

npm_add_dev_app2: ## Installe un paquet npm de dev pour app2 (ex: make npm_add_dev_app2 package=uuid)
	docker exec kpi_node_app2 sh -c "npm install -D $(package)"





# stop: ## stoppe les containers
#	 $(DOCKER_COMPOSE) -f ./compose.yaml stop

# docker_exec_php: ## connexion au container php
#	 docker exec -ti $(PHP_CONTAINER) bash

# docker_exec_db: ## connexion au container db
#	 docker exec -ti $(POSTGRES_CONTAINER) sh

# docker_logs: ## affiche les logs php
#	 docker logs -f $(PHP_CONTAINER)

