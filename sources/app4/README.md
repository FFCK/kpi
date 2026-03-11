# KPI Admin (app4)

Application Nuxt 4 pour l'administration de KPI.

## Stack technique

- **Framework**: Nuxt 4 + Vue 3 + TypeScript
- **UI**: Nuxt UI + Tailwind CSS
- **State**: Pinia
- **i18n**: @nuxtjs/i18n (FR/EN)
- **Maps**: Leaflet (page clubs)
- **API**: API2 (Symfony 7.3 + API Platform)
- **Auth**: JWT (mandats multi-rôles)

## Installation

```bash
# Installer les dépendances
make app4_npm_install

# Lancer en développement
make app4_dev
```

## URLs

- **Développement**: `https://kpi.localhost/admin2/`
- **Production**: `https://kayak-polo.info/admin2/`

## Structure

```
app4/
├── pages/           # Pages (file-based routing)
├── layouts/         # Layouts (admin)
├── components/      # Composants Vue
│   ├── admin/       # Composants admin réutilisables (Header, Toolbar, Modal, etc.)
│   │   └── tv/      # Composants contrôle TV
│   ├── documents/   # Composants page documents
│   ├── operations/  # Onglets page opérations
│   └── schema/      # Composants visualisation schéma compétition
├── composables/     # Composables (useApi, useAuth, useBracketDisplay, etc.)
├── stores/          # Pinia stores (auth, workContext, presence, filters, stats)
├── middleware/      # Route middleware (auth)
├── types/           # Types TypeScript (15 fichiers)
├── i18n/locales/    # Traductions FR/EN
└── assets/          # CSS, images
```

## Pages implémentées

| Route | Description | Profil |
|-------|-------------|--------|
| `/` | Dashboard | tous |
| `/login` | Connexion | - |
| `/reset-password` | Réinitialisation mot de passe | - |
| `/select-mandate` | Sélection mandat | - |
| `/events` | Gestion événements | ≤ 2 |
| `/competitions` | Gestion compétitions | ≤ 10 |
| `/competitions/copy` | Copie structure compétition | ≤ 3 |
| `/documents` | Gestion documents | ≤ 9 |
| `/stats` | Statistiques (22 types, exports PDF/XLSX) | ≤ 9 |
| `/operations` | Opérations système | = 1 |
| `/groups` | Gestion groupes | ≤ 2 |
| `/games` | Gestion matchs | ≤ 4 |
| `/gamedays` | Journées/Phases | ≤ 4 |
| `/gamedays/schema` | Visualisation schéma compétition | ≤ 4 |
| `/teams` | Gestion équipes | ≤ 3 |
| `/rankings` | Classements (calcul, publication, CP) | ≤ 4 |
| `/rankings/initial` | Classement initial | ≤ 6 |
| `/athletes` | Recherche athlètes | ≤ 8 |
| `/clubs` | Clubs (carte Leaflet) | ≤ 2 |
| `/clubs/team/:numero` | Détail équipe club | ≤ 2 |
| `/users` | Gestion utilisateurs & mandats | ≤ 2 |
| `/rc` | Responsables de compétition | ≤ 4 |
| `/presence/team/:teamId` | Composition équipe | ≤ 10 |
| `/presence/match/:matchId/team/:teamCode` | Composition match | ≤ 10 |
| `/tv` | Contrôle TV (canaux, présentations, scénarios) | ≤ 2 |
| `/journal` | Journal des actions | = 1 |

## Documentation complète

- **[APP4_STRUCTURE.md](../../DOC/developer/reference/APP4_STRUCTURE.md)** - Architecture détaillée, stores, composants, patterns
- **[API2_ENDPOINTS.md](../../DOC/developer/reference/API2_ENDPOINTS.md)** - Référence complète des endpoints API
