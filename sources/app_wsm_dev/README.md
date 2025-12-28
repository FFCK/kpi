# KPI WSM APP (Vue 3 + Vue CLI)

Application de gestion du websocket (WebSocket Manager) pour KPI (Kayak Polo Info).

## 🚀 Démarrage rapide

### Développement

```bash
# Dans le conteneur Docker
docker exec kpi_node_wsm npm run serve:dev

# Accès
https://wsm.kpi.localhost (via Traefik)
http://localhost:8080 (direct)
```

### Installation des dépendances

```bash
docker exec kpi_node_wsm npm install --legacy-peer-deps
```

## 📦 Scripts disponibles

### Serveur de développement

```bash
# Mode development (défaut) - utilise .env.development
npm run serve
npm run serve:dev

# Mode production - utilise .env.production
npm run serve:prod
```

### Build pour déploiement

```bash
# Development build
npm run build:dev

# Pre-production build
npm run build:preprod

# Production build (optimisé)
npm run build:prod
```

### Autres commandes

```bash
# Linter
npm run lint

# i18n report
npm run i18n:report

# Serveur HTTP statique (après build)
npm run http-server
```

## 🔧 Configuration

Voir [ENV_USAGE.md](ENV_USAGE.md) pour la documentation complète sur les environnements.

### Fichiers d'environnement

- `.env.development` - Développement local
- `.env.production` - Production
- `.env.preprod` - Pré-production
- `.env.local` - Surcharges locales (non committé)

### Variables d'environnement

```env
VUE_APP_TITLE=KPI WSM
VUE_APP_API_BASE_URL=http://kpi.localhost
VUE_APP_BASE_URL=http://wsm.kpi.localhost
VUE_APP_I18N_LOCALE=en
VUE_APP_I18N_FALLBACK_LOCALE=en
```

## 🛠️ Stack technique

- **Vue 3** - Framework JavaScript
- **Vue CLI 5** - Tooling et build
- **Vuex 4** - State management
- **Vue Router 4** - Routing
- **Vue i18n 9** - Internationalisation
- **Bootstrap 5** - UI Framework
- **Element Plus** - Composants UI
- **Axios** - HTTP client
- **Day.js** - Date manipulation
- **IndexedDB (idb)** - Storage local

## 📱 PWA

L'application est une Progressive Web App avec :
- Service Worker personnalisé
- Manifest configuré
- Support offline
- Installation possible

## 🌐 Accès

### Développement
- **Traefik** : `https://wsm.kpi.localhost`
- **Direct** : `http://localhost:8080`

### Production
- `https://wsm.kayak-polo.info`

## 🔄 Migration vers Nuxt 4

Cette application Vue 3 avec Vue CLI est prévue pour être migrée progressivement vers Nuxt 4.

## 📝 Notes

- Utiliser `--legacy-peer-deps` pour l'installation npm (conflits de peer dependencies)
- Le HMR fonctionne via WebSocket (wss:// en HTTPS)
- Buffer polyfill ajouté pour compatibilité webpack 5

---

## Ancienne documentation

### Apache ActiveMQ
```bash
cd ~/Documents/dev/activemq/apache-activemq-5.17.1/bin
./activemq console

# Interface admin
http://localhost:8161/admin/
# Credentials: admin / admin

# WebSocket
ws://localhost:61614
# Credentials: admin / password
```
