# Gestion des environnements - KPI WSM

## Fichiers d'environnement

Le projet utilise plusieurs fichiers d'environnement :

- **`.env.development`** - Environnement de développement (utilisé par défaut avec `serve`)
- **`.env.production`** - Environnement de production
- **`.env.preprod`** - Environnement de pré-production
- **`.env.dist`** - Template de configuration (référence)
- **`.env.local`** - Surcharges locales (ignoré par git)

## Scripts disponibles

### Mode développement (serve)

```bash
# Utilise .env.development (par défaut)
npm run serve
# ou explicitement
npm run serve:dev

# Utilise .env.production
npm run serve:prod
```

**Dans Docker :**
```bash
docker exec kpi_node_wsm npm run serve:dev
```

### Mode build (génération pour déploiement)

```bash
# Build avec .env.development
npm run build:dev

# Build avec .env.preprod
npm run build:preprod

# Build avec .env.production (optimisé avec --modern)
npm run build:prod
```

**Dans Docker :**
```bash
# Development
docker exec kpi_node_wsm npm run build:dev

# Preprod
docker exec kpi_node_wsm npm run build:preprod

# Production
docker exec kpi_node_wsm npm run build:prod
```

## Variables d'environnement

Toutes les variables d'environnement doivent commencer par `VUE_APP_` pour être accessibles dans le code :

```javascript
// Dans votre code Vue
console.log(process.env.VUE_APP_API_BASE_URL)
console.log(process.env.VUE_APP_TITLE)
```

### Variables disponibles

- `VUE_APP_TITLE` - Titre de l'application
- `VUE_APP_I18N_LOCALE` - Locale par défaut (en, fr)
- `VUE_APP_I18N_FALLBACK_LOCALE` - Locale de secours
- `VUE_APP_API_BASE_URL` - URL de base de l'API backend
- `VUE_APP_BASE_URL` - URL de base de l'application

## Exemples de configuration

### Development ([.env.development](sources/app_wsm_dev/.env.development))
```env
VUE_APP_API_BASE_URL=http://kpi.localhost
VUE_APP_BASE_URL=http://wsm.kpi.localhost
```

### Production ([.env.production](sources/app_wsm_dev/.env.production))
```env
VUE_APP_API_BASE_URL=https://kayak-polo.info
VUE_APP_BASE_URL=https://wsm.kayak-polo.info
```

### Preprod ([.env.preprod](sources/app_wsm_dev/.env.preprod))
```env
VUE_APP_API_BASE_URL=https://preprod.kayak-polo.info
VUE_APP_BASE_URL=https://wsm.preprod.kayak-polo.info
```

## Surcharges locales

Vous pouvez créer un fichier `.env.local` pour surcharger des variables sans les commiter :

```bash
# .env.local (ignoré par git)
VUE_APP_API_BASE_URL=http://localhost:8000
```

## Mode custom

Pour créer un mode personnalisé (ex: `staging`) :

1. Créer le fichier `.env.staging`
2. Ajouter le script dans package.json :
   ```json
   "build:staging": "vue-cli-service build --mode staging"
   ```

## Notes importantes

- Les fichiers `.env.*` sont chargés automatiquement par Vue CLI selon le mode
- Les variables sont injectées au moment du build
- Pour les modifier, vous devez redémarrer le serveur de développement
- `NODE_ENV` est automatiquement défini par Vue CLI (`development` ou `production`)
