-include docker/.env

UID := $(shell id -u)
GID := $(shell id -g)
export USER_ID := $(UID)
export GROUP_ID := $(GID)

# Variables pour les noms des réseaux
APPLICATION_NAME ?= kpi
NETWORK_KPI_NAME = network_$(APPLICATION_NAME)

DOCKER_COMPOSE = docker compose
DOCKER_EXEC = docker exec -ti
DOCKER_EXEC_PHP = docker exec -ti kpi_php
DOCKER_EXEC_PHP8 = docker exec -ti kpi_php8
DOCKER_EXEC_NODE = docker exec -ti kpi_node_app2
.DEFAULT_GOAL = help

.PHONY: help init init_env init_env_app2 init_networks \
dev_up dev_down dev_restart dev_rebuild dev_logs dev_status \
preprod_up preprod_down preprod_restart preprod_rebuild preprod_logs preprod_status \
prod_up prod_down prod_restart prod_rebuild prod_logs prod_status \
run_dev run_build run_generate run_lint \
npm_install_app2 npm_ls_app2 npm_clean_app2 npm_update_app2 npm_add_app2 npm_add_dev_app2 \
composer_install composer_update composer_require composer_require_dev composer_dump \
php_bash php8_bash node_bash db_bash \
wordpress_backup wordpress_restore \
networks_create networks_list networks_clean



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
init: init_env init_env_app2 init_networks ## Initialisation complète du projet (env, réseaux)
	@echo ""
	@echo "✅ Initialisation complète terminée!"
	@echo ""
	@echo "Configuration actuelle:"
	@echo "  - APPLICATION_NAME: $(APPLICATION_NAME)"
	@echo "  - Réseau KPI: $(NETWORK_KPI_NAME)"
	@echo ""
	@echo "Prochaines étapes:"
	@echo "  1. Configurez les variables dans docker/.env"
	@echo "  2. Lancez l'environnement: make dev_up (ou preprod_up/prod_up)"
	@echo "  3. Installez les dépendances Composer: make composer_install"
	@echo "  4. Installez les dépendances NPM: make npm_install_app2"
	@echo "  5. Lancez Nuxt: make run_dev"
	@echo ""
	@echo "Note: Pour une préprod/prod, vérifiez APPLICATION_NAME dans docker/.env"

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

init_networks: networks_create ## Alias pour networks_create (crée les réseaux Docker)


## DOCKER - DÉVELOPPEMENT
dev_up: ## Lance les containers Docker en mode développement
	$(DOCKER_COMPOSE) -f docker/compose.dev.yaml up -d

dev_down: ## Arrête les containers Docker de développement
	$(DOCKER_COMPOSE) -f docker/compose.dev.yaml down

dev_restart: ## Redémarre les containers Docker de développement
	$(DOCKER_COMPOSE) -f docker/compose.dev.yaml restart

dev_rebuild: ## Reconstruit et relance les containers de développement (après modif Dockerfile)
	@echo "🔄 Reconstruction des images Docker (développement)..."
	$(DOCKER_COMPOSE) -f docker/compose.dev.yaml down
	$(DOCKER_COMPOSE) -f docker/compose.dev.yaml build --no-cache
	$(DOCKER_COMPOSE) -f docker/compose.dev.yaml up -d
	@echo "✅ Containers reconstruits et relancés"

dev_logs: ## Affiche les logs des containers de développement
	$(DOCKER_COMPOSE) -f docker/compose.dev.yaml logs -f

dev_status: ## Affiche le statut des containers de développement
	$(DOCKER_COMPOSE) -f docker/compose.dev.yaml ps


## DOCKER - PRÉ-PRODUCTION
preprod_up: ## Lance les containers Docker en mode pré-production
	$(DOCKER_COMPOSE) -f docker/compose.preprod.yaml up -d

preprod_down: ## Arrête les containers Docker de pré-production
	$(DOCKER_COMPOSE) -f docker/compose.preprod.yaml down

preprod_restart: ## Redémarre les containers Docker de pré-production
	$(DOCKER_COMPOSE) -f docker/compose.preprod.yaml restart

preprod_rebuild: ## Reconstruit et relance les containers de pré-production (après modif Dockerfile)
	@echo "🔄 Reconstruction des images Docker (pré-production)..."
	$(DOCKER_COMPOSE) -f docker/compose.preprod.yaml down
	$(DOCKER_COMPOSE) -f docker/compose.preprod.yaml build --no-cache
	$(DOCKER_COMPOSE) -f docker/compose.preprod.yaml up -d
	@echo "✅ Containers reconstruits et relancés"

preprod_logs: ## Affiche les logs des containers de pré-production
	$(DOCKER_COMPOSE) -f docker/compose.preprod.yaml logs -f

preprod_status: ## Affiche le statut des containers de pré-production
	$(DOCKER_COMPOSE) -f docker/compose.preprod.yaml ps


## DOCKER - PRODUCTION
prod_up: ## Lance les containers Docker en mode production
	$(DOCKER_COMPOSE) -f docker/compose.prod.yaml up -d

prod_down: ## Arrête les containers Docker de production
	$(DOCKER_COMPOSE) -f docker/compose.prod.yaml down

prod_restart: ## Redémarre les containers Docker de production
	$(DOCKER_COMPOSE) -f docker/compose.prod.yaml restart

prod_rebuild: ## Reconstruit et relance les containers de production (après modif Dockerfile)
	@echo "🔄 Reconstruction des images Docker (production)..."
	$(DOCKER_COMPOSE) -f docker/compose.prod.yaml down
	$(DOCKER_COMPOSE) -f docker/compose.prod.yaml build --no-cache
	$(DOCKER_COMPOSE) -f docker/compose.prod.yaml up -d
	@echo "✅ Containers reconstruits et relancés"

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


## COMPOSER - PHP
composer_install: ## Installe les dépendances Composer (sources/vendor/)
	@echo "Installation des dépendances Composer..."
	$(DOCKER_EXEC_PHP) bash -c "cd /var/www/html && composer install"
	@echo "✅ Dépendances Composer installées"

composer_update: ## Met à jour les dépendances Composer
	@echo "Mise à jour des dépendances Composer..."
	$(DOCKER_EXEC_PHP) bash -c "cd /var/www/html && composer update"
	@echo "✅ Dépendances Composer mises à jour"

composer_require: ## Ajoute un package Composer (usage: make composer_require package=vendor/package)
	@if [ -z "$(package)" ]; then \
		echo "❌ Erreur: spécifiez un package (make composer_require package=vendor/package)"; \
		exit 1; \
	fi
	$(DOCKER_EXEC_PHP) bash -c "cd /var/www/html && composer require $(package)"
	@echo "✅ Package $(package) ajouté"

composer_require_dev: ## Ajoute un package Composer de dev (usage: make composer_require_dev package=vendor/package)
	@if [ -z "$(package)" ]; then \
		echo "❌ Erreur: spécifiez un package (make composer_require_dev package=vendor/package)"; \
		exit 1; \
	fi
	$(DOCKER_EXEC_PHP) bash -c "cd /var/www/html && composer require --dev $(package)"
	@echo "✅ Package de dev $(package) ajouté"

composer_dump: ## Regénère l'autoloader Composer
	$(DOCKER_EXEC_PHP) bash -c "cd /var/www/html && composer dump-autoload"
	@echo "✅ Autoloader Composer regénéré"


## ACCÈS SHELLS
php_bash: ## Ouvre un shell bash dans le container PHP 7.4
	$(DOCKER_EXEC_PHP) bash

php8_bash: ## Ouvre un shell bash dans le container PHP 8
	$(DOCKER_EXEC_PHP8) bash

node_bash: ## Ouvre un shell bash dans le container Node (app2)
	$(DOCKER_EXEC_NODE) sh

db_bash: ## Ouvre un shell dans le container MySQL
	docker exec -ti kpi_db sh


## RÉSEAUX DOCKER
networks_create: ## Crée les réseaux Docker nécessaires (network_${APPLICATION_NAME}, pma_network, traefiknetwork)
	@echo "Création des réseaux Docker..."
	@echo "Nom du réseau KPI: $(NETWORK_KPI_NAME)"
	@docker network inspect $(NETWORK_KPI_NAME) >/dev/null 2>&1 || \
		(docker network create $(NETWORK_KPI_NAME) && echo "✅ Réseau $(NETWORK_KPI_NAME) créé") || \
		echo "⚠️  Le réseau $(NETWORK_KPI_NAME) existe déjà"
	@docker network inspect pma_network >/dev/null 2>&1 || \
		(docker network create pma_network && echo "✅ Réseau pma_network créé") || \
		echo "⚠️  Le réseau pma_network existe déjà"
	@docker network inspect traefiknetwork >/dev/null 2>&1 || \
		(docker network create traefiknetwork && echo "✅ Réseau traefiknetwork créé") || \
		echo "⚠️  Le réseau traefiknetwork existe déjà"
	@echo "✅ Tous les réseaux sont prêts"

networks_list: ## Liste tous les réseaux Docker du projet
	@echo "Réseaux Docker du projet KPI (APPLICATION_NAME=$(APPLICATION_NAME)):"
	@docker network ls | grep -E "$(NETWORK_KPI_NAME)|pma_network|traefiknetwork" || echo "Aucun réseau trouvé"

networks_clean: ## Supprime les réseaux Docker du projet (attention: seulement si non utilisés)
	@echo "⚠️  Suppression des réseaux Docker..."
	@docker network rm $(NETWORK_KPI_NAME) 2>/dev/null && echo "✅ Réseau $(NETWORK_KPI_NAME) supprimé" || echo "⚠️  $(NETWORK_KPI_NAME) n'existe pas ou est utilisé"
	@docker network rm pma_network 2>/dev/null && echo "✅ Réseau pma_network supprimé" || echo "⚠️  pma_network n'existe pas ou est utilisé"
	@docker network rm traefiknetwork 2>/dev/null && echo "✅ Réseau traefiknetwork supprimé" || echo "⚠️  traefiknetwork n'existe pas ou est utilisé"


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
