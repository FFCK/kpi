# App2 - Architecture Technique Complète

**Application Web Progressive (PWA) pour la Gestion de Compétitions Kayak-Polo**

---

## Vue d'Ensemble

App2 est une Progressive Web Application (PWA) construite avec Nuxt 4, Vue 3 et TypeScript. Elle offre une expérience utilisateur moderne avec fonctionnement offline-first et gestion intelligente des erreurs.

**URL Production** : [https://app.kayak-polo.info](https://app.kayak-polo.info)
**URL Développement** : `https://app.kpi.localhost`

---

## Stack Technique

### Frontend

- **Framework**: Nuxt 4 (SSG - Static Site Generation)
- **Vue**: Vue 3 avec Composition API
- **TypeScript**: Typage strict pour composables et composants
- **Styling**: Tailwind CSS 4.x
- **UI Components**: Nuxt UI (basé sur Headless UI)
- **Toast Notifications**: Sonner (via Nuxt UI)

### État et Données

- **State Management**: Pinia stores
- **Cache Local**: IndexedDB via Dexie.js
- **i18n**: @nuxtjs/i18n (français par défaut, anglais)

### PWA et Offline

- **Service Worker**: Via @vite-pwa/nuxt
- **Stratégie**: Offline-first avec cache fallback
- **Détection Réseau**: navigator.onLine + composables useOnlineStatus / usePwa
- **Mise à jour auto**: Vérification au retour online et visibilitychange

### API Backend

- **API2**: `/api2` (Symfony 7.3 + API Platform 4.2)
- **Base URL Dev**: `https://kpi.localhost/api2`
- **Base URL Prod**: `https://kayak-polo.info/api2`
- **Authentification**: Token Bearer (10 jours de validité)
- **Documentation**: Voir [API2_ENDPOINTS.md](API2_ENDPOINTS.md)

---

## Architecture des Composables

### useApi.js - Couche d'Abstraction API

**Localisation**: [sources/app2/composables/useApi.js](../../sources/app2/composables/useApi.js)

**Responsabilités**:
1. Appels HTTP (GET, POST, PUT, DELETE)
2. Gestion des headers (auth, cache-control)
3. **Interception automatique des erreurs**
4. **Affichage des toasts d'erreur localisés**
5. Timeout de requête (10s)
6. Gestion spéciale 401 (redirection auto)

**Fonctionnalités clés**:
- Détection erreur réseau vs HTTP
- Throttling des 401 (1 toast par 5s max)
- Options: `silentErrors` pour désactiver les toasts

**Exemple d'utilisation**:
```javascript
// Automatique avec toasts
const response = await getApi('/games/123')
const data = await response.json()

// Silent (sans toast)
const response = await getApi('/games/123', { silentErrors: true })
```

### useOnlineStatus.ts - Monitoring Réseau et Notifications

**Localisation**: [sources/app2/composables/useOnlineStatus.ts](../../sources/app2/composables/useOnlineStatus.ts)

**Responsabilités**:
1. Surveillance état online/offline (navigator.onLine + events)
2. Toast automatique lors des changements réseau (avec IDs uniques pour éviter les doublons)
3. Export de `isOnline` et `checkConnection()` pour composants
4. Détection du retour sur l'app (visibilitychange)

**Caractéristiques**:
- Singleton global (initialisé une seule fois)
- Toast "Hors connexion" avec icône signal-slash (orange/warning)
- Toast "Connexion rétablie" (vert/success)
- Timeout uniforme de 3 secondes

**Initialisation**: Automatique via les composables useGames/useCharts

### usePwa.ts - Service Worker et Mises à Jour

**Localisation**: [sources/app2/composables/usePwa.ts](../../sources/app2/composables/usePwa.ts)

**Responsabilités**:
1. Enregistrement du Service Worker (production uniquement)
2. Détection des mises à jour disponibles (needRefresh)
3. Vérification automatique des mises à jour
4. Export de `isOnline`, `needRefresh`, `offlineReady`, `updateApp`, `checkForUpdates`

**Vérification des mises à jour**:
- Au retour online (après perte de connexion)
- Quand l'app redevient visible (visibilitychange)
- Toutes les 60 minutes (si en ligne)

**Pattern Singleton**:
```javascript
let initialized = false
const initializePwa = () => {
  if (initialized || !import.meta.client) return
  initialized = true
  // ...
}
```

### useGames.js / useCharts.js - Données Métier

**Localisation**:
- [sources/app2/composables/useGames.js](../../sources/app2/composables/useGames.js)
- [sources/app2/composables/useCharts.js](../../sources/app2/composables/useCharts.js)

**Stratégie Offline-First**:
1. Chargement IndexedDB (cache)
2. Si cache valide → affichage immédiat
3. Requête API en arrière-plan (si nécessaire)
4. Mise à jour du cache et de l'UI
5. En cas d'échec API → fallback sur cache + toast bleu

**Cache intelligent**:
- Durée de validité: 5 minutes
- Toast "Données en cache" si offline ou cache utilisé

---

## Gestion des Erreurs

### Classification des Erreurs

| Type | Code HTTP | Toast | Couleur | Action Auto |
|------|-----------|-------|---------|-------------|
| Réseau | - | Oui | Rouge | - |
| Offline | - | Oui | Orange | - |
| Timeout | - | Oui | Rouge | - |
| 401 | 401 | Oui | Rouge | Redirect /login |
| 404 | 404 | Oui | Orange | - |
| 4xx | 400-499 | Oui | Orange | - |
| 5xx | 500-599 | Oui | Rouge | - |

### Flow de Gestion d'Erreur

```
Appel API
  ↓
handleApiResponse()
  ↓
Timeout 10s ?
  ↓
Response OK ?
  ├─ Non → detectErrorType()
  │         ↓
  │      showHttpErrorToast() ou showNetworkErrorToast()
  │         ↓
  │      Si 401 → logout() + redirect (après 1.5s)
  │         ↓
  │      throw Error
  │
  └─ Oui → return response
```

### Messages i18n

**Structure dans [sources/app2/i18n/locales/](../../sources/app2/i18n/locales/)**:
```
errors.
  ├─ generic.*
  ├─ http.
  │   ├─ 400.* (Bad Request)
  │   ├─ 401.* (Authentication Required)
  │   ├─ 403.* (Access Denied)
  │   ├─ 404.* (Not Found)
  │   ├─ 4xx.* (Request Error)
  │   ├─ 500.* (Server Error)
  │   ├─ 503.* (Service Unavailable)
  │   └─ 5xx.* (Server Error)
  ├─ network.
  │   ├─ offline.* (No Internet Connection)
  │   ├─ online.* (Connection Restored)
  │   ├─ timeout.* (Request Timeout)
  │   ├─ failed.* (Connection Failed)
  │   └─ cors.* (Connection Blocked)
  ├─ operations.
  │   ├─ loadGames.*
  │   ├─ loadCharts.*
  │   ├─ loadPlayers.*
  │   ├─ savePlayer.*
  │   └─ login.*
  └─ cache.
      └─ usingOfflineData.* (Using Cached Data)
```

---

## PWA - Progressive Web App

### Fonctionnalités PWA

1. **Installation**: Add to Home Screen (iOS, Android)
2. **Offline**: Service Worker avec cache stratégique
3. **Mise à jour**: Auto-détection + prompt utilisateur
4. **Icônes**: Manifest avec icônes 192x192, 512x512

### Stratégie de Cache

**Cache-First** (assets statiques):
- CSS, JS, images, fonts
- Durée: illimitée avec cache busting

**Network-First** (données API):
- Matchs, classements, équipes
- Fallback sur cache si offline
- Toast "Données en cache" si fallback

---

## Gestion de la Connexion

### Détection Réseau

**Mécanisme**:
- `navigator.onLine` (propriété browser)
- Event listeners: `online`, `offline`, `visibilitychange`
- Composables: `useOnlineStatus().isOnline` et `usePwa().isOnline`

**Monitoring Actif** (useOnlineStatus.ts):
```javascript
// Initialisation unique (singleton)
window.addEventListener('online', handleOnline)
window.addEventListener('offline', handleOffline)
document.addEventListener('visibilitychange', handleVisibilityChange)

// Gestion des événements
handleOffline()
  ↓
Toast warning "Hors connexion" (id: 'offline-status')

handleOnline()
  ↓
Toast success "Connexion rétablie" (id: 'online-status')
  ↓
usePwa().checkForUpdates() // Vérifie nouvelle version
```

**IDs uniques des toasts** (évite les doublons):
- `offline-status` - Notification hors ligne
- `online-status` - Notification retour en ligne
- `cache-toast` - Données en cache
- `http-error-{type}` - Erreurs HTTP
- `network-error-{type}` - Erreurs réseau

### Comportement Offline

**Refresh sans réseau**:
1. Détection offline dans `handleApiResponse()`
2. Chargement cache IndexedDB
3. Toast bleu "Données en cache"
4. Affichage badge orange header

**Récupération réseau**:
1. Toast vert automatique
2. Tentative de synchronisation
3. Mise à jour des données
4. Suppression badge offline

---

## Délais et Timeouts

| Opération | Délai | Comportement |
|-----------|-------|--------------|
| Requête API | 10s timeout | Toast erreur + throw |
| Token validité | 10 jours | Auto-logout si expiré |
| Toast (tous types) | 3s affichage | Dismiss auto ou manuel |
| 401 redirect | 1.5s après toast | Navigation /login |
| 401 throttle | 5s minimum | Max 1 toast par 5s |
| Cache toast throttle | 3s minimum | Évite doublons |
| SW update check | 60 minutes | Vérification périodique |

---

## API - Points de Terminaison

### API2 (Symfony 7.3 + API Platform 4.2)

**Base URL**:
- Dev: `https://kpi.localhost/api2`
- Prod: `https://kayak-polo.info/api2`

**Endpoints principaux**:
- `/api2/events/{mode}` - Liste événements
- `/api2/games/{eventId}` - Matchs d'un événement
- `/api2/charts/{eventId}` - Classements
- `/api2/staff/{eventId}/team/{teamId}/players` - Joueurs d'une équipe
- `/api2/staff/scrutineering` - Sauvegarde contrôle matériel

**Documentation complète**: [API2_ENDPOINTS.md](API2_ENDPOINTS.md)

---

## Authentification

### Mécanisme

1. **Login**: POST `/api2/login` → token
2. **Stockage**: Cookie `kpi_app` (10 jours)
3. **Transmission**: Header `X-Auth-Token: {token}`
4. **Expiration**: 10 jours
5. **Renouvellement**: Manuel (re-login)

### Gestion 401

**Flow automatique**:
```
API retourne 401
  ↓
handleApiResponse() détecte
  ↓
Toast rouge "Session expirée"
  ↓
setTimeout(1.5s)
  ↓
router.push('/login')
```

**Throttling**: Max 1 toast 401 par 5 secondes (évite le spam)

---

## Performance

### Optimisations

- **SSG**: Pages pré-générées (build time)
- **Code Splitting**: Routes chargées à la demande
- **Tree Shaking**: Imports sélectifs Tailwind
- **Lazy Loading**: Composants lourds (charts, etc.)
- **Cache Busting**: Hash dans noms de fichiers

### Métriques Cibles

- **First Paint**: < 1s
- **Time to Interactive**: < 2s
- **Bundle Size**: < 300KB (gzipped)
- **Lighthouse Score**: > 90

---

## Déploiement

### Build Process

```bash
# Development
make run_generate_dev  # Uses .env.development

# Pre-production
make run_generate_preprod  # Uses .env.preprod

# Production
make run_generate_prod  # Uses .env.production
```

### Output

- **Fichiers générés**: `sources/app2/.output/public/`
- **Serveur**: Nginx (Docker)
- **Domaine Dev**: `app.kpi.localhost`
- **Domaine Prod**: `app.kayak-polo.info`

### Variables d'Environnement

**`.env.development`**:
```env
API_BASE_URL=https://kpi.localhost/api2
BACKEND_BASE_URL=https://kpi.localhost
```

**`.env.production`**:
```env
API_BASE_URL=https://kayak-polo.info/api2
BACKEND_BASE_URL=https://kayak-polo.info
```

---

## Maintenance

### Logs et Debug

**Console Dev**:
- Erreurs API avec grouping
- État réseau online/offline
- Chargement cache IndexedDB

**Production**:
- Erreurs loguées (pas de console)
- Toast utilisateur uniquement

### Mise à Jour

**Service Worker**:
- Auto-détection nouvelle version
- Prompt utilisateur "Mise à jour disponible"
- Reload page après confirmation

---

## Sécurité

### Mesures

1. **HTTPS**: Obligatoire en production
2. **Token**: Cookie HTTP-only, SameSite=Lax
3. **CORS**: Configuré côté backend
4. **XSS**: Sanitization via Vue (auto)
5. **Secrets**: .env non versionnés

### Bonnes Pratiques

- Pas de données sensibles dans localStorage
- Pas de token dans console/logs prod
- Messages d'erreur génériques (pas de stack traces)
- Validation côté client + serveur

---

## Tests

### Scénarios Clés

1. Perte réseau pendant navigation
2. Refresh en mode offline
3. Token expiré (401)
4. Erreur serveur (500)
5. Cache fallback fonctionnel
6. Toast i18n FR/EN

### Checklist de Test

- [ ] Toasts s'affichent en français
- [ ] Toasts s'affichent en anglais
- [ ] 401 redirect vers /login
- [ ] Cache fallback fonctionne
- [ ] Badge offline visible
- [ ] Service Worker actif
- [ ] PWA installable

---

## Fichiers Critiques

### Composables
- [sources/app2/composables/useApi.js](../../sources/app2/composables/useApi.js) - Core API layer + error toasts
- [sources/app2/composables/useOnlineStatus.ts](../../sources/app2/composables/useOnlineStatus.ts) - Network monitoring + toasts
- [sources/app2/composables/usePwa.ts](../../sources/app2/composables/usePwa.ts) - Service Worker + auto-update
- [sources/app2/composables/useAuth.js](../../sources/app2/composables/useAuth.js) - Authentication
- [sources/app2/composables/useGames.js](../../sources/app2/composables/useGames.js) - Games data + offline
- [sources/app2/composables/useCharts.js](../../sources/app2/composables/useCharts.js) - Rankings data + offline

### Layouts
- [sources/app2/layouts/default.vue](../../sources/app2/layouts/default.vue) - Main layout with toast container

### i18n
- [sources/app2/i18n/locales/fr.json](../../sources/app2/i18n/locales/fr.json) - French translations
- [sources/app2/i18n/locales/en.json](../../sources/app2/i18n/locales/en.json) - English translations

### Configuration
- [sources/app2/nuxt.config.ts](../../sources/app2/nuxt.config.ts) - Nuxt configuration
- `sources/app2/.env.development` - Dev environment
- `sources/app2/.env.production` - Production environment

---

## Références

- [Nuxt 4 Documentation](https://nuxt.com)
- [Vue 3 Composition API](https://vuejs.org/guide/extras/composition-api-faq.html)
- [Tailwind CSS](https://tailwindcss.com)
- [Nuxt UI](https://ui.nuxt.com)
- [Dexie.js](https://dexie.org)
- [API2 Endpoints](API2_ENDPOINTS.md)

---

**Auteur**: Laurent Garrigue / Claude Code
**Version**: 2.0
**Date**: Janvier 2026
**Maintenance**: Developer Team
