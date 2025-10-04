# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

KPI is a sports management system with multiple Vue.js/Nuxt applications, PHP backend, and Docker infrastructure. The project manages competitions, teams, matches, and player statistics.

## Development Commands

### Environment Configuration
- `make init_env_app2` - Initialize .env.development and .env.production for app2 from templates
- `.env.development` - Used automatically in development mode (`npm run dev`)
- `.env.production` - Used automatically for build/generate (`npm run build`, `npm run generate`)
- Variables: API_BASE_URL and BACKEND_BASE_URL

### Docker Environment
- `make dev_up` - Start development containers
- `make run_dev` - Run Nuxt development server for app2
- `make npm_install_app2` - Install dependencies for app2
- `make npm_clean_app2` - Clean node_modules and package-lock for app2
- `make npm_add_app2 package=<name>` - Add npm package to app2

### App2 (Nuxt 4 Application)
- `docker exec kpi_node_app2 sh -c "npm run dev"` - Development mode on port 3000
- `docker exec kpi_node_app2 sh -c "npm run build"` - Production build
- `docker exec kpi_node_app2 sh -c "npm run lint"` - ESLint check

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