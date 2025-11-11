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
DOCKER_EXEC_PHP_NON_INTERACTIVE = docker exec kpi_php
DOCKER_EXEC_PHP8_NON_INTERACTIVE = docker exec -u www-data kpi_php8
DOCKER_EXEC_NODE = docker exec -ti kpi_node_app2
.DEFAULT_GOAL = help

.PHONY: help init init_env init_env_app2 init_networks \
dev_up dev_down dev_restart dev_rebuild dev_logs dev_status \
preprod_up preprod_down preprod_restart preprod_rebuild preprod_logs preprod_status \
prod_up prod_down prod_restart prod_rebuild prod_logs prod_status \
run_dev run_build run_generate run_lint \
npm_install_app2 npm_ls_app2 npm_clean_app2 npm_update_app2 npm_add_app2 npm_add_dev_app2 \
npm_install_backend npm_add_backend npm_update_backend npm_ls_backend npm_clean_backend npm_init_backend \
composer_install composer_update composer_require composer_require_dev composer_dump \
php_bash php8_bash node_bash db_bash \
event_worker_start event_worker_stop event_worker_status event_worker_logs event_worker_restart \
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


## NPM - BACKEND (JavaScript Libraries)
npm_install_backend: ## Installe les dépendances npm du backend (sources/package.json) via container temporaire
	@if [ ! -f sources/package.json ]; then \
		echo "⚠️  Aucun package.json trouvé dans sources/"; \
		echo "💡 Créez d'abord sources/package.json avec: make npm_init_backend"; \
		exit 1; \
	fi
	@echo "📦 Installation des dépendances JavaScript du backend..."
	@docker run --rm -v $(PWD)/sources:/app -w /app node:20-alpine sh -c "npm install"
	@echo "✅ Dépendances installées dans sources/node_modules/"

npm_init_backend: ## Initialise package.json dans sources/ (si absent)
	@if [ -f sources/package.json ]; then \
		echo "⚠️  Le fichier sources/package.json existe déjà"; \
		exit 1; \
	fi
	@echo "📝 Création de package.json dans sources/..."
	@docker run --rm -v $(PWD)/sources:/app -w /app node:20-alpine sh -c "npm init -y"
	@echo "✅ Fichier package.json créé dans sources/"
	@echo "💡 Modifiez sources/package.json puis lancez: make npm_install_backend"

npm_add_backend: ## Ajoute un package npm au backend (usage: make npm_add_backend package=flatpickr)
	@if [ -z "$(package)" ]; then \
		echo "❌ Erreur: spécifiez un package (make npm_add_backend package=flatpickr)"; \
		exit 1; \
	fi
	@if [ ! -f sources/package.json ]; then \
		echo "⚠️  Aucun package.json trouvé. Initialisation..."; \
		$(MAKE) npm_init_backend; \
	fi
	@echo "📦 Installation de $(package)..."
	@docker run --rm -v $(PWD)/sources:/app -w /app node:20-alpine sh -c "npm install $(package)"
	@echo "✅ Package $(package) installé"
	@echo "💡 Fichiers disponibles dans sources/node_modules/$(package)/"

npm_update_backend: ## Met à jour les dépendances npm du backend
	@if [ ! -f sources/package.json ]; then \
		echo "❌ Aucun package.json trouvé dans sources/"; \
		exit 1; \
	fi
	@echo "🔄 Mise à jour des dépendances JavaScript du backend..."
	@docker run --rm -v $(PWD)/sources:/app -w /app node:20-alpine sh -c "npm update"
	@echo "✅ Dépendances mises à jour"

npm_ls_backend: ## Liste les packages npm installés dans le backend
	@if [ ! -d sources/node_modules ]; then \
		echo "⚠️  Aucun node_modules trouvé dans sources/"; \
		echo "💡 Lancez d'abord: make npm_install_backend"; \
		exit 1; \
	fi
	@echo "📦 Packages npm installés dans sources/:"
	@docker run --rm -v $(PWD)/sources:/app -w /app node:20-alpine sh -c "npm list --depth=0"

npm_clean_backend: ## Supprime node_modules du backend (attention: supprime toutes les libs JS)
	@echo "⚠️  Suppression de sources/node_modules..."
	@rm -rf sources/node_modules
	@rm -f sources/package-lock.json
	@echo "✅ node_modules et package-lock.json supprimés"


## COMPOSER - PHP
composer_install: ## Installe les dépendances Composer (sources/vendor/) - PHP 8
	@echo "Installation des dépendances Composer avec PHP 8..."
	$(DOCKER_EXEC_PHP8_NON_INTERACTIVE) bash -c "cd /var/www/html && composer install"
	@echo "✅ Dépendances Composer installées"

composer_update: ## Met à jour les dépendances Composer - PHP 8
	@echo "Mise à jour des dépendances Composer avec PHP 8..."
	$(DOCKER_EXEC_PHP8_NON_INTERACTIVE) bash -c "cd /var/www/html && composer update"
	@echo "✅ Dépendances Composer mises à jour"

composer_require: ## Ajoute un package Composer - PHP 8 (usage: make composer_require package=vendor/package)
	@if [ -z "$(package)" ]; then \
		echo "❌ Erreur: spécifiez un package (make composer_require package=vendor/package)"; \
		exit 1; \
	fi
	$(DOCKER_EXEC_PHP8_NON_INTERACTIVE) bash -c "cd /var/www/html && composer require $(package)"
	@echo "✅ Package $(package) ajouté"

composer_require_dev: ## Ajoute un package Composer de dev - PHP 8 (usage: make composer_require_dev package=vendor/package)
	@if [ -z "$(package)" ]; then \
		echo "❌ Erreur: spécifiez un package (make composer_require_dev package=vendor/package)"; \
		exit 1; \
	fi
	$(DOCKER_EXEC_PHP8_NON_INTERACTIVE) bash -c "cd /var/www/html && composer require --dev $(package)"
	@echo "✅ Package de dev $(package) ajouté"

composer_dump: ## Regénère l'autoloader Composer - PHP 8
	$(DOCKER_EXEC_PHP8_NON_INTERACTIVE) bash -c "cd /var/www/html && composer dump-autoload"
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


## EVENT WORKER - Génération automatique des caches d'événements
event_worker_start: ## Démarre le worker d'événements en arrière-plan
	@echo "🚀 Démarrage du worker d'événements..."
	@$(DOCKER_EXEC_PHP_NON_INTERACTIVE) bash -c "php /var/www/html/live/event_worker.php > /var/www/html/live/logs/event_worker.log 2>&1 &"
	@sleep 2
	@echo "✅ Worker démarré en arrière-plan"
	@echo "💡 Vérifiez le statut avec: make event_worker_status"
	@echo "💡 Consultez les logs avec: make event_worker_logs"

event_worker_stop: ## Arrête le worker d'événements
	@echo "🛑 Arrêt du worker d'événements..."
	@$(DOCKER_EXEC_PHP_NON_INTERACTIVE) bash -c "pkill -f 'event_worker.php' || true"
	@echo "✅ Worker arrêté"
	@echo "💡 Note: Vous pouvez aussi arrêter via l'interface web (sources/live/event.php)"

event_worker_status: ## Affiche le statut du worker d'événements
	@echo "📊 Statut du worker d'événements:"
	@$(DOCKER_EXEC_PHP_NON_INTERACTIVE) bash -c "if pgrep -f 'event_worker.php' > /dev/null; then \
		echo '  ✅ Worker en cours d\'exécution'; \
		echo '  PID: '`pgrep -f 'event_worker.php'`; \
	else \
		echo '  ❌ Worker arrêté'; \
	fi"
	@echo ""
	@echo "💡 Pour plus de détails, accédez à l'interface web: sources/live/event.php"

event_worker_logs: ## Affiche les logs du worker d'événements
	@echo "📋 Logs du worker d'événements (Ctrl+C pour quitter):"
	@echo "─────────────────────────────────────────────────────────"
	@$(DOCKER_EXEC_PHP_NON_INTERACTIVE) bash -c "tail -f /var/www/html/live/logs/event_worker.log 2>/dev/null || echo '⚠️  Aucun log disponible. Le worker n\'a peut-être pas encore été démarré.'"

event_worker_restart: ## Redémarre le worker d'événements
	@echo "🔄 Redémarrage du worker d'événements..."
	@$(MAKE) event_worker_stop
	@sleep 2
	@$(MAKE) event_worker_start


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
