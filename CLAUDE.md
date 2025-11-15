# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## 📚 Documentation

**Extended documentation** is available in the [`WORKFLOW_AI/`](WORKFLOW_AI/) directory, including:
- Migration guides (PHP 8, FPDF → mPDF, etc.)
- Technical fixes and optimizations
- Audit reports and cleanup recommendations
- Docker and infrastructure documentation
- **[Makefile Multi-Environment Support](WORKFLOW_AI/MAKEFILE_MULTI_ENVIRONMENT.md)** - Running multiple instances (dev, preprod, prod) on the same server

See [WORKFLOW_AI/README.md](WORKFLOW_AI/README.md) for the complete list.

## Project Overview

KPI is a sports management system with multiple Vue.js/Nuxt applications, PHP backend, and Docker infrastructure. The project manages competitions, teams, matches, and player statistics.

## Development Commands

Use `make help` to see all available commands.

### Quick Start
- `make init` - Complete project initialization (creates .env files and Docker networks)
- `make dev_up` - Start development environment
- `make composer_install` - Install PHP/Composer dependencies (mPDF, etc.)
- `make npm_install_app2` - Install NPM dependencies for app2
- `make npm_install_app3` - Install NPM dependencies for app3
- `make run_dev` - Run Nuxt development server for app2 (port 3002)
- `make run_dev_app3` - Run Nuxt development server for app3 (port 3003)

### Initialization
- `make init` - Complete initialization (env files + networks)
- `make init_env` - Initialize docker/.env from docker/.env.dist
- `make init_env_app2` - Initialize .env.development and .env.production for app2
- `make init_networks` - Create required Docker networks

### Docker - Development
- `make dev_up` - Start development containers
- `make dev_down` - Stop development containers
- `make dev_restart` - Restart development containers
- `make dev_rebuild` - Rebuild images and restart containers (after Dockerfile changes)
- `make dev_logs` - Show development logs (follow mode)
- `make dev_status` - Show development containers status

### Docker - Pre-production
- `make preprod_up` - Start pre-production containers
- `make preprod_down` - Stop pre-production containers
- `make preprod_restart` - Restart pre-production containers
- `make preprod_rebuild` - Rebuild images and restart containers (after Dockerfile changes)
- `make preprod_logs` - Show pre-production logs
- `make preprod_status` - Show pre-production status

### Docker - Production
- `make prod_up` - Start production containers
- `make prod_down` - Stop production containers
- `make prod_restart` - Restart production containers
- `make prod_rebuild` - Rebuild images and restart containers (after Dockerfile changes)
- `make prod_logs` - Show production logs
- `make prod_status` - Show production status

### Nuxt - App2
- `make run_dev` - Run Nuxt development server (port 3002)
- `make run_build` - Build Nuxt for production
- `make run_generate` - Generate static Nuxt site
- `make run_lint` - Run ESLint on app2

### Nuxt - App3 (Match Sheet)
- `make run_dev_app3` - Run Nuxt development server (port 3003)
- `make run_build_app3` - Build Nuxt for production
- `make run_generate_app3` - Generate static Nuxt site
- `make run_lint_app3` - Run ESLint on app3

### NPM - App2 (Nuxt Application)
- `make npm_install_app2` - Install all npm dependencies
- `make npm_clean_app2` - Remove node_modules and package-lock.json
- `make npm_update_app2` - Update all npm dependencies
- `make npm_add_app2 package=<name>` - Add npm package
- `make npm_add_dev_app2 package=<name>` - Add npm dev package
- `make npm_ls_app2` - List installed npm modules

### NPM - App3 (Match Sheet)
- `make npm_install_app3` - Install all npm dependencies
- `make npm_clean_app3` - Remove node_modules and package-lock.json
- `make npm_update_app3` - Update all npm dependencies
- `make npm_add_app3 package=<name>` - Add npm package
- `make npm_add_dev_app3 package=<name>` - Add npm dev package
- `make npm_ls_app3` - List installed npm modules

### NPM - Backend (JavaScript Libraries)
Manage JavaScript libraries (Flatpickr, Day.js, etc.) in the PHP backend via temporary Node.js container:
- `make npm_init_backend` - Initialize package.json in sources/ (if absent)
- `make npm_add_backend package=<name>` - Add a JavaScript package (e.g., `make npm_add_backend package=flatpickr`)
- `make npm_install_backend` - Install all backend dependencies from sources/package.json
- `make npm_update_backend` - Update all backend JavaScript dependencies
- `make npm_ls_backend` - List installed backend packages
- `make npm_clean_backend` - Remove sources/node_modules (WARNING: removes all JS libraries)

**Example usage**:
```bash
# Install Flatpickr for datepicker migration
make npm_add_backend package=flatpickr

# Files will be in sources/node_modules/flatpickr/
# Copy to sources/lib/ or reference directly in templates
```

**Note**: These commands use a temporary Node.js container (`node:20-alpine`), so no permanent Node.js installation is needed on the host.

### Composer - PHP
- `make composer_install` - Install Composer dependencies (sources/vendor/)
- `make composer_update` - Update Composer dependencies
- `make composer_require package=<vendor/package>` - Add Composer package
- `make composer_require_dev package=<vendor/package>` - Add Composer dev package
- `make composer_dump` - Regenerate Composer autoloader

### Shell Access
- `make php_bash` - Open bash in PHP 8.4 container
- `make node_bash` - Open shell in Node container (app2)
- `make node3_bash` - Open shell in Node container (app3)
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
  - **Important**: `APPLICATION_NAME` determines container names (e.g., `kpi`, `kpi_preprod`, `kpi_prod`)
  - Makefile automatically detects container names from this variable
  - Supports multiple instances on the same server (see [MAKEFILE_MULTI_ENVIRONMENT.md](WORKFLOW_AI/MAKEFILE_MULTI_ENVIRONMENT.md))
- `sources/app2/.env.development` - Nuxt dev environment (API_BASE_URL, BACKEND_BASE_URL)
- `sources/app2/.env.production` - Nuxt production environment

### Database
- Access via phpMyAdmin at configured domain
- Two databases: main KPI database and WordPress database

## Architecture

### Core Structure
- `sources/` - Main application code
  - `app2/` - Nuxt 4 application (primary frontend - scrutineering/charts)
  - `app3/` - Nuxt 4 application (match sheet management)
  - `app_dev/`, `app_live_dev/`, `app_wsm_dev/` - Legacy Vue.js applications
  - `commun/` - Shared PHP utilities and database classes
  - `api/` - PHP REST API endpoints
  - `wordpress/` - WordPress integration
- `docker/` - Docker configuration and compose files
- `SQL/` - Database scripts

### App2 (Nuxt Application - Scrutineering/Charts)
- **Framework**: Nuxt 4 with Vue 3, TypeScript, Tailwind CSS
- **Modules**: Pinia for state management, i18n for internationalization, Nuxt UI components
- **Domain**: `kpi_node.localhost` (via Traefik)
- **Development**: Runs on port 3000 inside container, accessible via port 3002 on host
- **API Integration**: Configured via .env.development (dev: `https://kpi.localhost/api`) and .env.production (prod: `https://kayak-polo.info/api`)

### App3 (Nuxt Application - Match Sheet)
- **Framework**: Nuxt 4 with Vue 3, TypeScript, Tailwind CSS
- **Purpose**: Live match management with real-time scoring, timer, and broadcasting
- **Modules**: Pinia, Dexie (IndexedDB), i18n, PWA, easytimer.js
- **Domain**: `app3.localhost` (via Traefik)
- **Development**: Runs on port 3003 inside container
- **Features**:
  - Create/load matches with teams and players
  - Real-time match timer and shot clock
  - Event tracking (goals, cards, penalties)
  - BroadcastChannel API for scoreboard/shotclock synchronization
  - WebSocket support (optional)
  - Offline-first with IndexedDB storage
  - Progressive Web App (PWA)

### PHP Backend
- **PHP Version**: PHP 8.4 in all environments (dev, preprod, prod)
- **Migration Status**: ✅ PHP 8 migration completed - PHP 7.4 fully deprecated
- Legacy PHP codebase with custom database abstraction layer
- Database classes in `commun/MyBdd.php` and `commun/Bdd_PDO.php`
- Configuration through `commun/MyConfig.php` and `commun/MyParams.php`
- API endpoints for JSON data exchange
- Modern libraries: mPDF v8.2+, OpenSpout v4.32.0, Smarty v4

### Database
- MySQL database with custom schema for sports management
- Automated cron jobs for license updates and presence sheet locking
- Multi-database setup (main + WordPress)

## Docker Configuration

The project uses Docker Compose with multiple environments:
- **Development**: `docker/compose.dev.yaml` - includes hot reload and development tools
- **Pre-production**: `docker/compose.preprod.yaml` - staging environment for testing
- **Production**: `docker/compose.prod.yaml` - optimized for production deployment

Services include PHP 8.4, MySQL databases, phpMyAdmin, and Node.js containers for frontend applications.

## Important Notes

- **PHP 8.4**: All environments now run PHP 8.4 - migration from PHP 7.4 is complete
- **Libraries Modernization**: Successfully migrated to modern PHP 8+ compatible libraries (mPDF, OpenSpout, Smarty v4)
- **JavaScript Migration**: jQuery elimination and library modernization is ongoing (Flatpickr, Axios→fetch, etc.)
- App2 is the primary modern frontend application being actively developed
- Legacy Vue.js applications in `app_dev/`, `app_live_dev/`, `app_wsm_dev/` are maintained but not primary focus
- Configuration files are mounted from Docker directory to avoid committing sensitive data
- The project uses Traefik for reverse proxy in production environments
- ESLint configuration is managed by Nuxt for app2

## File Patterns

- Vue/Nuxt components use PascalCase naming
- PHP files follow legacy naming conventions
- Database configuration is environment-specific and mounted via Docker volumes