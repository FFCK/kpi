# Documentation Infrastructure - KPI

Documentation technique sur l'infrastructure Docker, Nginx, CORS et déploiement.

## 📚 Documents Disponibles

### Déploiement et Architecture

#### [NGINX_STATIC_APP_DEPLOYMENT.md](NGINX_STATIC_APP_DEPLOYMENT.md)
**Status**: ✅ Implémenté (2025-12-21)

Documentation complète sur le déploiement des applications Nuxt (app2 & app3) via Nginx en mode SSG (Static Site Generation).

**Sujets couverts**:
- Architecture Nginx + Nuxt SSG
- Configuration Nginx pour SPA routing
- Workflow de build dev vs prod
- Containers Node.js temporaires pour builds prod
- Variables d'environnement (.env.development, .env.production)
- Intégration Docker Compose et Traefik
- Commandes Makefile (`run_generate_dev`, `run_generate_prod`)
- Troubleshooting (403, Service Worker, URLs incorrectes)

**Pour qui**: Développeurs, DevOps
**Pré-requis**: Connaissances Docker, Nuxt.js

---

#### [CORS_CONFIGURATION.md](CORS_CONFIGURATION.md)
**Status**: ✅ Implémenté (2025-12-21)

Documentation sur la configuration CORS globale via PHP auto-prepend pour tous les endpoints PHP.

**Sujets couverts**:
- Mécanisme PHP `auto_prepend_file`
- Configuration CORS globale pour tous les endpoints (API, custom files, api2)
- Gestion des origines autorisées (production & développement)
- Headers CORS et leur signification
- Gestion des requêtes preflight (OPTIONS)
- Migration depuis configuration Apache statique
- Troubleshooting headers dupliqués
- Tests CORS (curl, browser DevTools)

**Pour qui**: Développeurs backend, DevOps
**Pré-requis**: Connaissances PHP, HTTP/CORS

---

### Multi-Environnements

#### [MAKEFILE_MULTI_ENVIRONMENT.md](../guides/infrastructure/MAKEFILE_MULTI_ENVIRONMENT.md)
**Status**: ✅ Implémenté

Support multi-environnements (dev, preprod, prod) sur le même serveur.

**Sujets couverts**:
- Configuration `APPLICATION_NAME` dans `.env`
- Détection automatique des containers par le Makefile
- Commandes `make dev_*`, `make preprod_*`, `make prod_*`
- Réseaux Docker par environnement

**Pour qui**: DevOps, administrateurs système

---

### Gestion des Dépendances

#### [NPM_BACKEND_PRODUCTION_GUIDE.md](../guides/infrastructure/NPM_BACKEND_PRODUCTION_GUIDE.md)
**Status**: ✅ Implémenté

Gestion des dépendances JavaScript (Flatpickr, Day.js, etc.) dans le backend PHP.

**Sujets couverts**:
- Commandes Makefile NPM pour backend
- Installation via container Node.js temporaire
- Copie des fichiers dans `sources/lib/`
- Intégration dans templates Smarty

**Pour qui**: Développeurs backend

---

## 🔗 Liens Rapides

### Commandes Courantes

```bash
# Build app2 pour développement
make run_generate_dev

# Build app2 pour production (sans container Node.js permanent)
make run_generate_prod

# Redémarrer nginx
docker restart kpi_nginx_app2

# Vérifier headers CORS
curl -k -I -H "Origin: https://app.kpi.localhost" https://kpi.localhost/api/test

# Rebuild Docker après changement Dockerfile
make dev_rebuild
make prod_rebuild
```

### Fichiers de Configuration Clés

- `docker/config/nginx-app2.conf` - Configuration Nginx pour app2
- `docker/config/auto-prepend-cors.php` - Logique CORS globale
- `docker/config/php-auto-prepend.ini` - Configuration PHP auto-prepend
- `docker/config/000-default.conf` - Configuration Apache (sans headers CORS statiques)
- `sources/app2/.env.development` - Variables env pour build dev
- `sources/app2/.env.production` - Variables env pour build prod

### Dockerfiles

- `docker/config/Dockerfile.dev.web` - Image PHP dev (avec CORS auto-prepend)
- `docker/config/Dockerfile.prod.web` - Image PHP prod (avec CORS auto-prepend)

### Docker Compose

- `docker/compose.dev.yaml` - Service `nginx_app2` dev
- `docker/compose.prod.yaml` - Service `nginx_app2` prod

## 📖 Guides Associés

### Migration et Bonnes Pratiques
- [BEST_PRACTICES_JAVASCRIPT_SMARTY.md](../guides/BEST_PRACTICES_JAVASCRIPT_SMARTY.md) - Bonnes pratiques JS & Smarty

### Migrations JavaScript
- [FLATPICKR_MIGRATION_GUIDE.md](../guides/migrations/FLATPICKR_MIGRATION_GUIDE.md) - Migration datepicker
- [MIGRATION_AXIOS_FETCH_GUIDE.md](../guides/migrations/MIGRATION_AXIOS_FETCH_GUIDE.md) - Migration Axios → fetch()

## 🐛 Troubleshooting

### Problèmes Courants

#### 1. App2 retourne 403 Forbidden
**Solution**: Vérifier que `ssr: false` dans `nuxt.config.ts` et régénérer avec `make run_generate_dev`

#### 2. Headers CORS dupliqués
**Solution**:
- Vérifier que `000-default.conf` n'a pas de `Header always set Access-Control-*`
- Rebuild image: `make dev_rebuild`

#### 3. Service Worker cache old URLs
**Solution**: Désactiver temporairement PWA dans `nuxt.config.ts`:
```typescript
pwa: { disable: true }
```

#### 4. Build prod échoue (pas de Node.js)
**Solution**: Utiliser `make run_generate_prod` qui crée un container temporaire

## 📞 Support

Pour toute question technique:
1. Consulter la documentation associée
2. Vérifier les logs: `make dev_logs` ou `make prod_logs`
3. Tester avec curl pour CORS
4. Inspecter Network tab dans DevTools

## 🔄 Historique

| Date | Document | Changement |
|------|----------|------------|
| 2025-12-21 | NGINX_STATIC_APP_DEPLOYMENT.md | ✅ Création - Infrastructure Nginx pour app2/app3 |
| 2025-12-21 | CORS_CONFIGURATION.md | ✅ Création - CORS global via PHP auto-prepend |
| 2024-xx-xx | MAKEFILE_MULTI_ENVIRONMENT.md | ✅ Support multi-environnements |
| 2024-xx-xx | NPM_BACKEND_PRODUCTION_GUIDE.md | ✅ NPM pour backend PHP |

## 📝 Notes

- Les fichiers générés (`.output/public/`) ne sont **jamais** commités dans Git
- Les builds se font à la demande via Makefile
- En production, pas besoin de container Node.js permanent
- CORS géré de manière centralisée pour tous les endpoints PHP
