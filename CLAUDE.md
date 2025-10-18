# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

KPI is a sports management system with multiple Vue.js/Nuxt applications, PHP backend, and Docker infrastructure. The project manages competitions, teams, matches, and player statistics.

## Development Commands

Use `make help` to see all available commands.

### Quick Start
- `make init` - Complete project initialization (creates .env files and Docker networks)
- `make dev_up` - Start development environment
- `make npm_install_app2` - Install dependencies for app2
- `make run_dev` - Run Nuxt development server (port 3002)

### Initialization
- `make init` - Complete initialization (env files + networks)
- `make init_env` - Initialize docker/.env from docker/.env.dist
- `make init_env_app2` - Initialize .env.development and .env.production for app2
- `make init_networks` - Create required Docker networks

### Docker - Development
- `make dev_up` - Start development containers
- `make dev_down` - Stop development containers
- `make dev_restart` - Restart development containers
- `make dev_logs` - Show development logs (follow mode)
- `make dev_status` - Show development containers status

### Docker - Pre-production
- `make preprod_up` - Start pre-production containers
- `make preprod_down` - Stop pre-production containers
- `make preprod_restart` - Restart pre-production containers
- `make preprod_logs` - Show pre-production logs
- `make preprod_status` - Show pre-production status

### Docker - Production
- `make prod_up` - Start production containers
- `make prod_down` - Stop production containers
- `make prod_restart` - Restart production containers
- `make prod_logs` - Show production logs
- `make prod_status` - Show production status

### Nuxt - App2
- `make run_dev` - Run Nuxt development server (port 3002)
- `make run_build` - Build Nuxt for production
- `make run_generate` - Generate static Nuxt site
- `make run_lint` - Run ESLint on app2

### NPM - App2
- `make npm_install_app2` - Install all npm dependencies
- `make npm_clean_app2` - Remove node_modules and package-lock.json
- `make npm_update_app2` - Update all npm dependencies
- `make npm_add_app2 package=<name>` - Add npm package
- `make npm_add_dev_app2 package=<name>` - Add npm dev package
- `make npm_ls_app2` - List installed npm modules

### Shell Access
- `make php_bash` - Open bash in PHP 7.4 container
- `make php8_bash` - Open bash in PHP 8 container
- `make node_bash` - Open shell in Node/app2 container
- `make db_bash` - Open shell in MySQL container

### Docker Networks
- `make networks_create` - Create required Docker networks (network_${APPLICATION_NAME}, pma_network, traefiknetwork)
- `make networks_list` - List project Docker networks
- `make networks_clean` - Remove project Docker networks (if not in use)

**Important**: Network names depend on `APPLICATION_NAME` in `docker/.env`:
- KPI network: `network_${APPLICATION_NAME}` (e.g., `network_kpi`, `network_kpi_preprod`)
- phpMyAdmin network: `pma_network` (shared)
- Traefik network: `traefiknetwork` (shared)

For multiple environments on the same server, use different `APPLICATION_NAME` values in each `.env` file.

### WordPress
- `make wordpress_backup` - Create WordPress backup (stored in docker/)
- WordPress content is stored in `docker/wordpress/` (excluded from Git)
- WordPress path is configured via `HOST_WORDPRESS_PATH` in docker/.env

### Environment Files
- `docker/.env` - Main Docker environment configuration (not versioned)
- `sources/app2/.env.development` - Nuxt dev environment (API_BASE_URL, BACKEND_BASE_URL)
- `sources/app2/.env.production` - Nuxt production environment

### Database
- Access via phpMyAdmin at configured domain
- Two databases: main KPI database and WordPress database

## Architecture

### Core Structure
- `sources/` - Main application code
  - `app2/` - Nuxt 4 application (primary frontend)
  - `app_dev/`, `app_live_dev/`, `app_wsm_dev/` - Legacy Vue.js applications
  - `commun/` - Shared PHP utilities and database classes
  - `api/` - PHP REST API endpoints
  - `wordpress/` - WordPress integration
- `docker/` - Docker configuration and compose files
- `SQL/` - Database scripts

### App2 (Nuxt Application)
- **Framework**: Nuxt 4 with Vue 3, TypeScript, Tailwind CSS
- **Modules**: Pinia for state management, i18n for internationalization, Nuxt UI components
- **Base URL**: `/app2` - configured for production deployment
- **Development**: Runs on port 3000 inside container, accessible via port 3002 on host
- **API Integration**: Configured via .env.development (dev: `https://kpi.local/api`) and .env.production (prod: `https://kayak-polo.info/api`)

### PHP Backend
- Legacy PHP codebase with custom database abstraction layer
- Database classes in `commun/MyBdd.php` and `commun/Bdd_PDO.php`
- Configuration through `commun/MyConfig.php` and `commun/MyParams.php`
- API endpoints for JSON data exchange

### Database
- MySQL database with custom schema for sports management
- Automated cron jobs for license updates and presence sheet locking
- Multi-database setup (main + WordPress)

## Docker Configuration

The project uses Docker Compose with multiple environments:
- **Development**: `docker/compose.dev.yaml` - includes hot reload and development tools
- **Production**: `docker/compose.prod.yaml` - optimized for production deployment

Services include PHP (two versions), MySQL databases, phpMyAdmin, and Node.js containers for frontend applications.

## Important Notes

- App2 is the primary modern frontend application being actively developed
- Legacy Vue.js applications in `app_dev/`, `app_live_dev/`, `app_wsm_dev/` are maintained but not primary focus
- Configuration files are mounted from Docker directory to avoid committing sensitive data
- The project uses Traefik for reverse proxy in production environments
- ESLint configuration is managed by Nuxt for app2

## File Patterns

- Vue/Nuxt components use PascalCase naming
- PHP files follow legacy naming conventions
- Database configuration is environment-specific and mounted via Docker volumes