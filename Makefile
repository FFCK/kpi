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

# stop: ## stoppe les containers
#	 $(DOCKER_COMPOSE) -f ./compose.yaml stop

# docker_exec_php: ## connexion au container php
#	 docker exec -ti $(PHP_CONTAINER) bash

# docker_exec_db: ## connexion au container db
#	 docker exec -ti $(POSTGRES_CONTAINER) sh

# docker_logs: ## affiche les logs php
#	 docker logs -f $(PHP_CONTAINER)

