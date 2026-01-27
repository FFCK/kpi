# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## 📚 Documentation

**Complete documentation** is organized in the [`DOC/`](DOC/) directory:

### User Documentation
- **[DOC/user/](DOC/user/)** - User guides and functionality descriptions
  - [Event Cache Manager](DOC/user/EVENT_CACHE_MANAGER.md) - Background worker for video overlays
  - [Image Upload Management](DOC/user/IMAGE_UPLOAD_MANAGEMENT.md) - Logo and photo management
  - [Team Composition Copy](DOC/user/TEAM_COMPOSITION_COPY.md) - Copy player lists between teams
  - [Match Day Bulk Operations](DOC/user/MATCH_DAY_BULK_OPERATIONS.md) - Bulk actions on matches
  - [Match Consistency Stats](DOC/user/MATCH_CONSISTENCY_STATS.md) - Match scheduling consistency
  - [Multi-Competition Type](DOC/user/MULTI_COMPETITION_TYPE.md) - Multi-competition aggregation
  - [Consolidation Phases](DOC/user/CONSOLIDATION_PHASES_CLASSEMENT.md) - Lock ranking phases

### Developer Documentation
- **[DOC/developer/](DOC/developer/)** - Technical documentation for developers
  - **[Reference](DOC/developer/reference/)** - Complete reference documentation (KPI Functionality Inventory)
  - **[Guides](DOC/developer/guides/)** - Migration guides and technical documentation
  - **[In Progress](DOC/developer/in-progress/)** - Current migrations and ongoing work
  - **[Archive](DOC/developer/archive/)** - Completed migrations (PHP 8.4, mPDF, etc.)
  - **[Fixes](DOC/developer/fixes/)** - Bug fixes and corrections
  - **[Audits](DOC/developer/audits/)** - Code audits and analyses
  - **[Infrastructure](DOC/developer/infrastructure/)** - Docker, WordPress, configuration

**Key documents:**
- **[Makefile Multi-Environment Support](DOC/developer/guides/infrastructure/MAKEFILE_MULTI_ENVIRONMENT.md)** - Running multiple instances (dev, preprod, prod) on the same server
- **[PHP 8.4 Migration Complete](DOC/developer/archive/completed-migrations/PHP8_MIGRATION_COMPLETE.md)** - ✅ Final migration report

See [DOC/README.md](DOC/README.md) for the complete index.

## Project Overview

KPI is a sports management system with multiple Vue.js/Nuxt applications, PHP backend, and Docker infrastructure. The project manages competitions, teams, matches, and player statistics.

## Development Commands

Use `make help` to see all available commands.

### Quick Start
- `make init` - Complete project initialization (creates .env files and Docker networks)
- `make docker_dev_up` - Start development environment
- `make backend_composer_install` - Install PHP/Composer dependencies (mPDF, etc.)
- `make app2_npm_install` - Install NPM dependencies for app2
- `make app3_npm_install` - Install NPM dependencies for app3
- `make app2_dev` - Run Nuxt development server for app2 (port 3002)
- `make app3_dev` - Run Nuxt development server for app3 (port 3003)

### Initialization
- `make init` - Complete initialization (env files + networks)
- `make init_env` - Initialize docker/.env from docker/.env.dist
- `make init_env_app2` - Initialize .env.development and .env.production for app2
- `make init_env_app3` - Initialize .env files for app3
- `make init_env_api2` - Initialize .env for API2 from .env.dist
- `make init_networks` - Create required Docker networks

### Docker - Development
- `make docker_dev_up` - Start development containers
- `make docker_dev_down` - Stop development containers
- `make docker_dev_restart` - Restart development containers
- `make docker_dev_rebuild` - Rebuild images and restart containers (after Dockerfile changes)
- `make docker_dev_logs` - Show development logs (follow mode)
- `make docker_dev_status` - Show development containers status

### Docker - Pre-production
- `make docker_preprod_up` - Start pre-production containers
- `make docker_preprod_down` - Stop pre-production containers
- `make docker_preprod_restart` - Restart pre-production containers
- `make docker_preprod_rebuild` - Rebuild images and restart containers (after Dockerfile changes)
- `make docker_preprod_logs` - Show pre-production logs
- `make docker_preprod_status` - Show pre-production status

### Docker - Production
- `make docker_prod_up` - Start production containers
- `make docker_prod_down` - Stop production containers
- `make docker_prod_restart` - Restart production containers
- `make docker_prod_rebuild` - Rebuild images and restart containers (after Dockerfile changes)
- `make docker_prod_logs` - Show production logs
- `make docker_prod_status` - Show production status

### App2 - Nuxt (Scrutineering/Charts)
- `make app2_dev` - Run Nuxt development server (port 3002)
- `make app2_build` - Build Nuxt for production
- `make app2_generate_dev` - Generate static Nuxt site for development (uses .env.development, requires Node container)
- `make app2_generate_preprod` - Generate static Nuxt site for pre-production (uses .env.preprod, temporary container)
- `make app2_generate_production` - Generate static Nuxt site for production (uses .env.production, temporary container)
- `make app2_generate_prod` - Alias for app2_generate_production
- `make app2_lint` - Run ESLint on app2

**Note**: `app2_generate_preprod` and `app2_generate_production` use a temporary Node.js container, so they work even without a permanent Node container (ideal for preprod/production servers).

### App3 - Nuxt (Match Sheet)
- `make app3_dev` - Run Nuxt development server (port 3003)
- `make app3_build` - Build Nuxt for production
- `make app3_generate_dev` - Generate static Nuxt site for development (uses .env.development, requires Node container)
- `make app3_generate_preprod` - Generate static Nuxt site for pre-production (uses .env.preprod, temporary container)
- `make app3_generate_prod` - Generate static Nuxt site for production (uses .env.production, temporary container)
- `make app3_lint` - Run ESLint on app3

**Note**: `app3_generate_preprod` and `app3_generate_prod` use a temporary Node.js container, so they work even without a permanent Node container (ideal for preprod/production servers).

### App2 - NPM
- `make app2_npm_install` - Install all npm dependencies
- `make app2_npm_clean` - Remove node_modules and package-lock.json
- `make app2_npm_update` - Update all npm dependencies
- `make app2_npm_add package=<name>` - Add npm package
- `make app2_npm_add_dev package=<name>` - Add npm dev package
- `make app2_npm_ls` - List installed npm modules

### App3 - NPM
- `make app3_npm_install` - Install all npm dependencies
- `make app3_npm_clean` - Remove node_modules and package-lock.json
- `make app3_npm_update` - Update all npm dependencies
- `make app3_npm_add package=<name>` - Add npm package
- `make app3_npm_add_dev package=<name>` - Add npm dev package
- `make app3_npm_ls` - List installed npm modules

### Backend - NPM (JavaScript Libraries)
Manage JavaScript libraries (Flatpickr, Day.js, etc.) in the PHP backend via temporary Node.js container:
- `make backend_npm_init` - Initialize package.json in sources/ (if absent)
- `make backend_npm_add package=<name>` - Add a JavaScript package (e.g., `make backend_npm_add package=flatpickr`)
- `make backend_npm_install` - Install all backend dependencies from sources/package.json
- `make backend_npm_update` - Update all backend JavaScript dependencies
- `make backend_npm_ls` - List installed backend packages
- `make backend_npm_clean` - Remove sources/node_modules (WARNING: removes all JS libraries)

**Example usage**:
```bash
# Install Flatpickr for datepicker migration
make backend_npm_add package=flatpickr

# Files will be in sources/node_modules/flatpickr/
# Copy to sources/lib/ or reference directly in templates
```

**Note**: These commands use a temporary Node.js container (`node:20-alpine`), so no permanent Node.js installation is needed on the host.

### Backend - Composer (PHP)
- `make backend_composer_install` - Install Composer dependencies (sources/vendor/)
- `make backend_composer_update` - Update Composer dependencies
- `make backend_composer_require package=<vendor/package>` - Add Composer package
- `make backend_composer_require_dev package=<vendor/package>` - Add Composer dev package
- `make backend_composer_dump` - Regenerate Composer autoloader

### API2 - Symfony (Symfony 7.3 + API Platform 4.2)
- `make api2_composer_install` - Install Composer dependencies for API2
- `make api2_composer_update` - Update Composer dependencies for API2
- `make api2_composer_require package=<vendor/package>` - Add package to API2
- `make api2_cache_clear` - Clear Symfony cache for API2
- `make api2_cache_warmup` - Warmup Symfony cache for API2
- `make api2_migrations_diff` - Generate Doctrine migration (detect changes)
- `make api2_migrations_migrate` - Execute Doctrine migrations

**Location**: `sources/api2/` - Modern REST API with Symfony 7.3 and API Platform 4.2
**Documentation**: See [sources/api2/README.md](sources/api2/README.md) and [sources/api2/API_ENDPOINTS.md](sources/api2/API_ENDPOINTS.md)
**Base URL**: `https://kpi.localhost/api2/`
**API Documentation**: `https://kpi.localhost/api2/api` (API Platform interface)

### Shell Access
- `make backend_bash` - Open bash in PHP 8.4 container
- `make app2_bash` - Open shell in Node container (app2)
- `make app3_bash` - Open shell in Node container (app3)
- `make app4_bash` - Open shell in Node container (app4)
- `make db_bash` - Open shell in MySQL container

### Docker Networks
- `make docker_networks_create` - Create required Docker networks (network_${APPLICATION_NAME}, pma_network, traefiknetwork)
- `make docker_networks_list` - List project Docker networks
- `make docker_networks_clean` - Remove project Docker networks (if not in use)

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
- `docker/.env` - Main Docker environment configuration (not versioned, use docker/.env.dist as template)
  - **Important**: `APPLICATION_NAME` determines container names (e.g., `kpi`, `kpi_preprod`, `kpi_prod`)
  - Makefile automatically detects container names from this variable
  - Supports multiple instances on the same server (see [MAKEFILE_MULTI_ENVIRONMENT.md](WORKFLOW_AI/MAKEFILE_MULTI_ENVIRONMENT.md))
- `sources/app2/.env.development` - Nuxt dev environment (API_BASE_URL, BACKEND_BASE_URL)
- `sources/app2/.env.production` - Nuxt production environment
- `sources/api2/.env` - Symfony/API Platform configuration (not versioned, use sources/api2/.env.dist as template)

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
  - `api/` - Legacy PHP REST API endpoints
  - `api2/` - **NEW**: Modern REST API (Symfony 7.3 + API Platform 4.2)
  - `wordpress/` - WordPress integration
- `docker/` - Docker configuration and compose files
- `SQL/` - Database scripts

### App2 (Nuxt Application - Scrutineering/Charts)
- **Framework**: Nuxt 4 with Vue 3, TypeScript, Tailwind CSS
- **Modules**: Pinia for state management, i18n for internationalization, Nuxt UI components
- **Domain**:
  - Dev: `kpi_node.localhost` (Node dev server via Traefik)
  - Dev static: `app.kpi.localhost` (Nginx serving .output/public/)
  - Prod: `app.kayak-polo.info` (Nginx serving .output/public/)
- **Development**: Runs on port 3000 inside container, accessible via port 3002 on host
- **API Integration**: Configured via .env.development (dev: `https://kpi.localhost/api`) and .env.production (prod: `https://kayak-polo.info/api`)
- **Deployment**:
  - Generated files (`.output/public/`) are NOT committed to Git
  - Dev: `make app2_generate_dev` (requires Node container)
  - Preprod: `make app2_generate_preprod` (uses temporary Docker container)
  - Prod: `make app2_generate_production` (uses temporary Docker container, works without permanent Node.js setup)
  - After build: `make docker_dev_restart`, `make docker_preprod_restart`, or `make docker_prod_restart` to restart Nginx

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

### API2 (Modern REST API - Symfony 7.3 + API Platform 4.2)
- **Framework**: Symfony 7.3 with API Platform 4.2
- **Purpose**: Modern REST API replacing legacy PHP API with same functionality
- **Location**: `sources/api2/`
- **Base URL**: `https://kpi.localhost/api2/`
- **API Documentation**: `https://kpi.localhost/api2/api` (OpenAPI/Swagger UI)
- **Features**:
  - REST API with automatic OpenAPI documentation
  - Doctrine ORM for database abstraction
  - CORS support for cross-origin requests
  - Same database as legacy API (MariaDB 11.5)
  - All endpoints from legacy API (`/api/`) replicated
- **Endpoints**:
  - Public: events, games, charts, team stats, ratings
  - Staff: teams, players, scrutineering management
  - Report: game details with events and players
  - WSM: live score management, game events, timer, statistics
- **Documentation**: See [sources/api2/README.md](sources/api2/README.md) and [sources/api2/API_ENDPOINTS.md](sources/api2/API_ENDPOINTS.md)
- **Status**: ✅ Fully implemented - ready for testing and migration from legacy API

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
- **API2 (NEW)**: Modern REST API built with Symfony 7.3 + API Platform 4.2 in `sources/api2/`
  - Replicates all functionalities from legacy API (`sources/api/`)
  - Provides automatic OpenAPI documentation
  - Ready for testing and gradual migration
  - See [sources/api2/README.md](sources/api2/README.md) for details
- App2 is the primary modern frontend application being actively developed
- App3 provides live match sheet management with real-time features
- Legacy Vue.js applications in `app_dev/`, `app_live_dev/`, `app_wsm_dev/` are maintained but not primary focus
- Configuration files are mounted from Docker directory to avoid committing sensitive data
- The project uses Traefik for reverse proxy in production environments
- ESLint configuration is managed by Nuxt for app2 and app3

## File Patterns

- Vue/Nuxt components use PascalCase naming
- PHP files follow legacy naming conventions
- Database configuration is environment-specific and mounted via Docker volumes