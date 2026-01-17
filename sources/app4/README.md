# KPI Admin (app4)

Application Nuxt 4 pour l'administration de KPI.

## Stack technique

- **Framework**: Nuxt 4 + Vue 3
- **UI**: Nuxt UI + Tailwind CSS
- **State**: Pinia
- **i18n**: @nuxtjs/i18n (FR/EN)
- **API**: API2 (Symfony 7.3 + API Platform)
- **Auth**: JWT

## Installation

```bash
# Installer les dépendances
make npm_install_app4

# Lancer en développement
make run_dev_app4
```

## URLs

- **Développement**: `https://kpi.localhost/admin2/`
- **Production**: `https://kayak-polo.info/admin2/`

## Structure

```
app4/
├── pages/           # Pages (file-based routing)
├── layouts/         # Layouts (default, admin)
├── components/      # Composants Vue
│   └── admin/       # Composants admin (Sidebar, Header)
├── composables/     # Composables (useApi, useAuth)
├── stores/          # Pinia stores
├── middleware/      # Route middleware (auth)
├── types/           # Types TypeScript
├── locales/         # Traductions i18n
└── assets/          # CSS, images
```

## Accès

**Phase Beta**: Accès limité au profil 1 (Super Admin) uniquement.

## Pages prévues

| Route | Description | Profil |
|-------|-------------|--------|
| `/` | Dashboard | ≤ 2 |
| `/login` | Connexion | - |
| `/events` | Gestion événements | ≤ 2 |
| `/documents` | Gestion documents | ≤ 9 |
| `/statistics` | Statistiques | ≤ 9 |
| `/operations` | Opérations système | = 1 |
