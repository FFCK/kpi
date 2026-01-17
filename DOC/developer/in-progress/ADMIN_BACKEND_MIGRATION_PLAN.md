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
| **Clés JWT** | Générées via commande Makefile (reproductible preprod/prod) |
| **Page pilote** | GestionEvenement (gestion des événements) |
| **Accès initial** | Profil 1 uniquement (Super Admin) |
| **Transition** | Lien dans menu PHP vers nouvelle interface |

## Principes de migration

### Analyse fonctionnelle par page

Pour chaque page migrée :
1. **Inventaire** : Lister toutes les fonctionnalités existantes
2. **Évaluation** : Pour chaque fonctionnalité, décider si elle doit être :
   - ✅ Conservée telle quelle
   - 🔧 Améliorée (UX, performance)
   - 📦 Simplifiée (réduire la complexité)
   - ❌ Supprimée (obsolète, jamais utilisée)
3. **Validation** : S'assurer qu'aucune fonctionnalité n'est oubliée

### Design de l'interface

| Élément | Spécification |
|---------|---------------|
| **Liste des données** | Pleine largeur, icônes d'action par ligne |
| **Actions bulk** | Barre d'outils au-dessus (sélection multiple ou tous) |
| **Formulaire ajout/modif** | Fenêtre modale |
| **Pagination** | 20 items par page par défaut |
| **Responsive** | Mobile-first, adaptatif desktop |

### Ordre de migration

1. **GestionEvenement** (pilote) - CRUD événements
2. **GestionDoc** - CRUD documents
3. **GestionStats** - Statistiques
4. **GestionOperations** - Opérations système (profil 1)

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

### 2.3 Commande Makefile pour génération des clés

```makefile
# JWT - Génération des clés RSA
jwt_generate_keys:
	@echo "Génération des clés JWT..."
	docker compose -f docker/compose.dev.yaml exec php \
		sh -c "cd /var/www/html/api2 && \
		mkdir -p config/jwt && \
		openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:\$${JWT_PASSPHRASE} && \
		openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout -passin pass:\$${JWT_PASSPHRASE}"
	@echo "Clés JWT générées dans sources/api2/config/jwt/"
```

**Usage :**
```bash
# En développement
make jwt_generate_keys

# En préprod/prod (même commande, fichiers .env différents)
make jwt_generate_keys
```

**Note** : Les clés sont dans `.gitignore` et doivent être générées sur chaque environnement.

### 2.4 Endpoint de login

```
POST /api2/auth/login
Body: { "username": "...", "password": "..." }
Response: { "token": "eyJ...", "user": { "id", "name", "profile" } }
```

### 2.5 Protection des routes admin

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

**Structure de la page :**

```
┌─────────────────────────────────────────────────────────────┐
│  [+ Ajouter]  [⬜ Tout sélectionner]  [🗑️ Supprimer (0)]    │  ← Barre d'actions bulk
├─────────────────────────────────────────────────────────────┤
│ ⬜ │ 👁️ │ 📱 │ ID  │ Libellé        │ Lieu    │ Début │ Fin │ ← En-têtes triables
├─────────────────────────────────────────────────────────────┤
│ ⬜ │ 🟢 │ 🟢 │ 123 │ Championnat... │ Paris   │ 01/03 │ ... │ ✏️ 🗑️
│ ⬜ │ 🔴 │ 🟢 │ 122 │ Tournoi...     │ Lyon    │ 15/02 │ ... │ ✏️ 🗑️
│ ...                                                         │
├─────────────────────────────────────────────────────────────┤
│ ◀ Page 1 sur 5 ▶                    20 items/page ▼         │  ← Pagination
└─────────────────────────────────────────────────────────────┘
```

**Fonctionnalités :**
- Liste pleine largeur avec colonnes triables
- Pagination : 20 items par page (configurable)
- Sélection multiple avec checkbox
- Actions bulk : supprimer sélection, tout sélectionner
- Actions par ligne : Éditer (✏️), Supprimer (🗑️), Toggle Publication (👁️), Toggle App (📱)
- Formulaire ajout/modification en **modal**
- Confirmation avant suppression
- Messages de succès/erreur (toast notifications)
- **Responsive** : colonnes masquées sur mobile, actions en menu déroulant

**Composants utilisés :**
- Nuxt UI : `UTable`, `UButton`, `UModal`, `UForm`, `UInput`, `UPagination`, `UCheckbox`, `UDropdown`
- Icônes : `@nuxt/icon`

### 4.2 Modal Ajout/Modification

```
┌─────────────────────────────────────┐
│  Ajouter un événement          [X] │
├─────────────────────────────────────┤
│  Libellé *                          │
│  ┌─────────────────────────────┐    │
│  │                             │    │
│  └─────────────────────────────┘    │
│                                     │
│  Lieu                               │
│  ┌─────────────────────────────┐    │
│  │                             │    │
│  └─────────────────────────────┘    │
│                                     │
│  Date début        Date fin         │
│  ┌────────────┐    ┌────────────┐   │
│  │ 📅         │    │ 📅         │   │
│  └────────────┘    └────────────┘   │
│                                     │
│         [Annuler]  [Enregistrer]    │
└─────────────────────────────────────┘
```

### 4.3 Layout Admin (layouts/admin.vue)

```vue
<template>
  <div class="flex min-h-screen bg-gray-50">
    <!-- Sidebar (masquée sur mobile, toggle via hamburger) -->
    <AdminSidebar :collapsed="sidebarCollapsed" />

    <div class="flex-1 flex flex-col">
      <!-- Header avec hamburger menu mobile -->
      <AdminHeader @toggle-sidebar="sidebarCollapsed = !sidebarCollapsed" />

      <!-- Contenu principal -->
      <main class="flex-1 p-4 md:p-6 overflow-auto">
        <slot />
      </main>
    </div>
  </div>
</template>
```

### 4.4 Sidebar avec menu

Menu conditionnel selon le profil utilisateur (pour les 4 pages prévues) :

| Page | Profil requis | Icône |
|------|---------------|-------|
| Events | ≤ 2 | 📅 |
| Documents | ≤ 9 | 📄 |
| Statistics | ≤ 9 | 📊 |
| Operations | = 1 | ⚙️ |

### 4.5 Responsive breakpoints

| Breakpoint | Comportement |
|------------|--------------|
| **Mobile** (< 640px) | Sidebar masquée, colonnes réduites, actions en dropdown |
| **Tablet** (640-1024px) | Sidebar rétractable, colonnes principales visibles |
| **Desktop** (> 1024px) | Sidebar déployée, toutes colonnes visibles |

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

## Statut d'implémentation

| Phase | Description | Statut |
|-------|-------------|--------|
| Phase 1.1 | Créer l'application Nuxt app4 | ✅ Terminé |
| Phase 1.2 | Configurer Docker (compose files, Traefik) | ✅ Terminé |
| Phase 1.3 | Ajouter commandes Makefile | ✅ Terminé |
| Phase 2 | Implémenter JWT dans API2 | ✅ Terminé |
| Phase 3 | Créer endpoints API2 pour events | ✅ Terminé |
| Phase 4 | Créer interface Nuxt events | ✅ Terminé |
| Phase 5 | Ajouter lien menu PHP | ✅ Terminé |
| Phase 6 | Préparer tests de validation | ✅ Terminé |

## Guide de test rapide

### Prérequis

1. **Démarrer les conteneurs Docker** :
   ```bash
   make dev_up
   ```

2. **Générer les clés JWT** (première fois seulement) :
   ```bash
   make jwt_generate_keys
   ```
   Entrer un passphrase quand demandé (et le noter dans `.env` de API2).

3. **Installer les dépendances app4** :
   ```bash
   make npm_install_app4
   ```

4. **Démarrer le serveur de développement app4** :
   ```bash
   make run_dev_app4
   ```

### Tests manuels

1. **Accéder à la nouvelle interface** :
   - URL : `https://kpi.localhost/admin2/`
   - Ou via le menu PHP : cliquer sur "Admin2 (Beta)" (visible profil 1 uniquement)

2. **Se connecter** :
   - Utiliser un compte profil 1 (Super Admin)
   - Les autres profils sont bloqués pendant la phase beta

3. **Tester les fonctionnalités Events** :
   - Liste des événements avec pagination
   - Tri des colonnes (cliquer sur les en-têtes)
   - Recherche par libellé/lieu
   - Ajouter un événement (bouton + modal)
   - Modifier un événement (icône crayon)
   - Toggle publication (icône check vert/rouge)
   - Toggle app (icône téléphone)
   - Supprimer un événement (icône poubelle, profil 1 uniquement)
   - Sélection multiple et suppression bulk

4. **Vérifier la synchronisation** :
   - Les modifications doivent être visibles dans l'ancien `GestionEvenement.php`
   - Et inversement

### Configuration API2 (.env)

S'assurer que le fichier `sources/api2/.env` contient :
```env
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=votre_passphrase
```

## Étapes de mise en production

1. **Développement** : ✅ Implémenté sur branche `claude/migrate-admin-backend-A1TBN`
2. **Tests locaux** : Valider tous les tests fonctionnels
3. **Déploiement preprod** : Tester en conditions réelles
4. **Déploiement prod** : Activer pour profil 1 uniquement
5. **Validation utilisateur** : Période de test (1-2 semaines)
6. **Migration complète** : Supprimer ancienne page, ouvrir à tous les profils

## Pages à migrer (ordre validé)

| # | Page PHP | Page Nuxt | Profil | Statut |
|---|----------|-----------|--------|--------|
| 1 | GestionEvenement | `/events` | ≤ 2 | ✅ Implémenté (en test) |
| 2 | GestionDoc | `/documents` | ≤ 9 | ⏳ À faire |
| 3 | GestionStats | `/statistics` | ≤ 9 | ⏳ À faire |
| 4 | GestionOperations | `/operations` | = 1 | ⏳ À faire |

Pour chaque page, une analyse fonctionnelle détaillée sera produite avant migration.

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

## Annexe A : Analyse fonctionnelle - GestionEvenement

### Fonctionnalités existantes

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Liste des événements (tri date DESC) | ≤ 2 | Essentielle | ✅ Conserver |
| 2 | Afficher ID | ≤ 2 | Utile debug | ✅ Conserver |
| 3 | Toggle Publication (O/N) | ≤ 2 | Essentielle | ✅ Conserver |
| 4 | Toggle App (O/N) | ≤ 2 | Essentielle | ✅ Conserver |
| 5 | Éditer un événement | ≤ 2 | Essentielle | ✅ Conserver |
| 6 | Supprimer un événement | ≤ 1 | Essentielle | ✅ Conserver |
| 7 | Ajouter un événement | ≤ 2 | Essentielle | ✅ Conserver |
| 8 | Formulaire à droite (fixe) | - | UX ancienne | 🔧 → Modal |
| 9 | Confirmation JS avant action | - | Bonne pratique | ✅ Conserver |
| 10 | Formatage dates FR/EN | - | i18n | ✅ Conserver |
| 11 | Journalisation des actions | - | Audit | ✅ Conserver (API) |

### Améliorations prévues

| # | Amélioration | Description |
|---|--------------|-------------|
| 1 | Pagination | 20 items/page (actuellement: tout sur une page) |
| 2 | Sélection multiple | Checkbox pour actions bulk |
| 3 | Suppression bulk | Supprimer plusieurs événements à la fois |
| 4 | Recherche/Filtre | Filtrer par libellé, lieu, dates |
| 5 | Tri colonnes | Clic sur en-tête pour trier |
| 6 | Responsive | Interface adaptée mobile/tablet |
| 7 | Feedback visuel | Toast notifications succès/erreur |
| 8 | Validation temps réel | Erreurs affichées dans le formulaire |

### Champs de l'entité Event

| Champ | Type | Requis | Validation |
|-------|------|--------|------------|
| id | int | Auto | PK |
| libelle | string(40) | Oui | NotBlank, MaxLength(40) |
| lieu | string(40) | Non | MaxLength(40) |
| date_debut | date | Non | Format date valide |
| date_fin | date | Non | Format date valide, >= date_debut |
| publication | char(1) | Non | Enum: O/N, défaut: N |
| app | char(1) | Non | Enum: O/N, défaut: N |

---

**Document créé le** : 2026-01-02
**Dernière mise à jour** : 2026-01-02
**Statut** : ✅ Phase 1-6 implémentées - Prêt pour tests
