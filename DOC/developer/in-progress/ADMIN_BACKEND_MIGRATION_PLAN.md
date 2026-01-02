# Plan de Migration du Backend Administration vers Nuxt 4

## Objectif

Migrer progressivement le backend d'administration PHP (Smarty 4, jQuery, Bootstrap) vers une stack moderne Nuxt 4 + API2 (Symfony 7.3 / API Platform 4.2).

## Décisions Architecture

| Élément | Choix |
|---------|-------|
| **Application** | Nouvelle app4 dédiée à l'administration |
| **URL** | `kpi.localhost/admin2/...` |
| **API** | API2 avec JWT (Symfony 7.3 + API Platform) |
| **Authentification** | JWT tokens via LexikJWTAuthenticationBundle |
| **Page pilote** | GestionEvenement (gestion des événements) |
| **Accès initial** | Profil 1 uniquement (Super Admin) |
| **Transition** | Lien dans menu PHP vers nouvelle interface |

## Phase 1 : Infrastructure (Fondations)

### 1.1 Créer l'application Nuxt app4

```
sources/app4/
├── nuxt.config.ts          # Configuration Nuxt
├── app.vue                 # Composant racine
├── pages/
│   ├── index.vue           # Dashboard admin
│   ├── login.vue           # Page de connexion
│   └── events/
│       └── index.vue       # Gestion des événements
├── layouts/
│   └── admin.vue           # Layout admin avec sidebar
├── components/
│   └── admin/
│       ├── Sidebar.vue     # Menu latéral
│       ├── Header.vue      # En-tête admin
│       └── DataTable.vue   # Table de données réutilisable
├── composables/
│   ├── useAuth.ts          # Authentification JWT
│   ├── useApi.ts           # Appels API2
│   └── usePermissions.ts   # Gestion des profils
├── middleware/
│   └── auth.ts             # Protection des routes
├── stores/
│   └── authStore.ts        # État authentification
├── types/
│   └── index.ts            # Types TypeScript
└── i18n/
    └── locales/
        ├── fr.json
        └── en.json
```

### 1.2 Configuration Docker

- Ajouter service `node4` dans compose files
- Configurer Traefik pour `/admin2/` → app4
- Variables d'environnement `.env.development`, `.env.production`

### 1.3 Commandes Makefile

```makefile
# NPM - App4
npm_install_app4
npm_clean_app4
run_dev_app4          # Port 3004
run_generate_app4
run_generate_dev_app4
run_generate_preprod_app4
run_generate_prod_app4
```

## Phase 2 : Authentification JWT dans API2

### 2.1 Installation des packages Symfony

```bash
composer require lexik/jwt-authentication-bundle
composer require symfony/security-bundle
```

### 2.2 Configuration JWT

**Fichiers à créer/modifier :**

- `config/packages/lexik_jwt_authentication.yaml`
- `config/packages/security.yaml` (firewall JWT)
- Génération des clés RSA (privée/publique)

### 2.3 Endpoint de login

```
POST /api2/auth/login
Body: { "username": "...", "password": "..." }
Response: { "token": "eyJ...", "user": { "id", "name", "profile" } }
```

### 2.4 Protection des routes admin

```yaml
# security.yaml
firewalls:
  api:
    pattern: ^/api2/admin
    stateless: true
    jwt: ~
access_control:
  - { path: ^/api2/admin, roles: ROLE_ADMIN }
```

## Phase 3 : Endpoints API2 pour GestionEvenement

### 3.1 Controller AdminEventController

```
GET    /api2/admin/events           # Liste des événements
POST   /api2/admin/events           # Créer un événement
GET    /api2/admin/events/{id}      # Détail d'un événement
PUT    /api2/admin/events/{id}      # Modifier un événement
DELETE /api2/admin/events/{id}      # Supprimer un événement
PATCH  /api2/admin/events/{id}/publish   # Toggle publication
PATCH  /api2/admin/events/{id}/app       # Toggle visibilité app
```

### 3.2 Validation des permissions

```php
#[IsGranted('ROLE_ADMIN')]           // Profil <= 2 pour accès
#[IsGranted('ROLE_SUPER_ADMIN')]     // Profil <= 1 pour suppression
```

### 3.3 DTO et Validation

```php
class EventDto {
    #[Assert\NotBlank]
    #[Assert\Length(max: 40)]
    public string $libelle;

    #[Assert\Length(max: 40)]
    public ?string $lieu = null;

    #[Assert\Date]
    public ?string $dateDebut = null;

    #[Assert\Date]
    public ?string $dateFin = null;
}
```

## Phase 4 : Interface Nuxt app4

### 4.1 Page Events (pages/events/index.vue)

**Fonctionnalités :**
- Liste des événements dans une DataTable
- Tri par date (DESC par défaut)
- Actions : Éditer, Supprimer, Toggle Publication, Toggle App
- Formulaire modal pour Ajouter/Modifier
- Confirmation avant suppression
- Messages de succès/erreur (toast)

**Composants utilisés :**
- Nuxt UI : `UTable`, `UButton`, `UModal`, `UForm`, `UInput`, `UNotification`
- Icônes : `@nuxt/icon`

### 4.2 Layout Admin (layouts/admin.vue)

```vue
<template>
  <div class="flex min-h-screen">
    <AdminSidebar />
    <div class="flex-1">
      <AdminHeader />
      <main class="p-6">
        <slot />
      </main>
    </div>
  </div>
</template>
```

### 4.3 Sidebar avec menu

Menu conditionnel selon le profil utilisateur :
- Profil ≤ 1 : Toutes les options + Operations
- Profil ≤ 2 : Events, Users, etc.
- Profil ≤ 3 : Options limitées

## Phase 5 : Intégration Menu PHP

### 5.1 Modification main_menu.tpl

Ajouter un lien conditionnel pour le profil 1 :

```smarty
{if isset($profile) && $profile == 1}
    <li class="menu-item">
        <a href="/admin2/" target="_blank" class="menu-link-new">
            🆕 Nouvelle Admin (Beta)
        </a>
    </li>
{/if}
```

### 5.2 Style visuel distinct

- Badge "BETA" ou icône distinctive
- Couleur différente pour identifier la nouvelle interface

## Phase 6 : Tests de validation

### 6.1 Tests fonctionnels

| Test | Description | Résultat attendu |
|------|-------------|------------------|
| T1 | Connexion profil 1 | Accès autorisé |
| T2 | Connexion profil 2 | Accès refusé (temporaire) |
| T3 | Liste des événements | Affichage correct |
| T4 | Ajouter un événement | Création OK, apparaît dans liste |
| T5 | Modifier un événement | Modification enregistrée |
| T6 | Toggle publication | Icône et valeur BDD mis à jour |
| T7 | Toggle app | Icône et valeur BDD mis à jour |
| T8 | Supprimer un événement | Suppression OK (profil 1 uniquement) |
| T9 | Validation formulaire | Erreur si libellé vide |
| T10 | Déconnexion | Retour page login |
| T11 | Token expiré | Redirection login |
| T12 | Formatage dates FR/EN | Affichage correct selon langue |

### 6.2 Tests de non-régression

- Vérifier que l'ancien GestionEvenement.php fonctionne toujours
- Vérifier que les modifications en BDD sont visibles des deux côtés
- Tester les autres pages admin (pas de régression)

### 6.3 Tests de sécurité

- Tentative d'accès sans token → 401
- Tentative avec token invalide → 401
- Tentative suppression avec profil 2 → 403
- Injection SQL dans les champs → Échec (paramètres préparés)

## Fichiers à créer/modifier

### Nouveaux fichiers

| Chemin | Description |
|--------|-------------|
| `sources/app4/` | Nouvelle application Nuxt admin |
| `sources/api2/src/Controller/AdminEventController.php` | API events admin |
| `sources/api2/src/Dto/EventDto.php` | DTO validation |
| `sources/api2/config/packages/lexik_jwt_authentication.yaml` | Config JWT |
| `docker/compose.dev.yaml` | Service node4 |
| `Makefile` | Commandes app4 |

### Fichiers modifiés

| Chemin | Modification |
|--------|--------------|
| `sources/api2/config/packages/security.yaml` | Firewall JWT |
| `sources/api2/composer.json` | Dépendances JWT |
| `sources/smarty/templates/main_menu.tpl` | Lien nouvelle admin |
| `docker/.env.dist` | Variables app4 |

## Étapes de mise en production

1. **Développement** : Implémenter sur branche `claude/migrate-admin-backend-A1TBN`
2. **Tests locaux** : Valider tous les tests fonctionnels
3. **Déploiement preprod** : Tester en conditions réelles
4. **Déploiement prod** : Activer pour profil 1 uniquement
5. **Validation utilisateur** : Période de test (1-2 semaines)
6. **Migration complète** : Supprimer ancienne page, ouvrir à tous les profils

## Prochaines pages à migrer (ordre suggéré)

1. ✅ GestionEvenement (pilote)
2. GestionDoc (simple CRUD documents)
3. GestionUtilisateur (CRUD utilisateurs)
4. GestionStructure (CRUD clubs)
5. GestionCompetition (plus complexe, autocomplete)
6. GestionEquipe (relations équipes)
7. GestionJournee (matchs, calendrier)
8. GestionClassement (classements)
9. GestionStats (statistiques)
10. GestionOperations (opérations système)

## Estimation effort

| Phase | Complexité | Description |
|-------|------------|-------------|
| Phase 1 | Moyenne | Infrastructure app4, Docker |
| Phase 2 | Moyenne | JWT dans API2 |
| Phase 3 | Faible | Endpoints events |
| Phase 4 | Moyenne | Interface Nuxt |
| Phase 5 | Faible | Lien menu PHP |
| Phase 6 | Faible | Tests |

---

**Document créé le** : 2026-01-02
**Dernière mise à jour** : 2026-01-02
**Statut** : En attente de validation
