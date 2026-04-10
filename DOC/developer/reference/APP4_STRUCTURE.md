# App4 - Structure et Architecture

**Application** : Administration KPI (Nuxt 4)
**Port de dev** : 3004
**Base URL** : `/admin2`

---

## 1. Stack Technologique

- **Framework** : Nuxt 4 + Vue 3 + TypeScript
- **UI Components** : Nuxt UI (headless components)
- **State Management** : Pinia
- **i18n** : @nuxtjs/i18n (FR/EN)
- **Styling** : Tailwind CSS
- **Icons** : Heroicons (via Iconify)
- **Maps** : Leaflet (client-side, pour page clubs)
- **API Client** : Composable `useApi()` personnalisé

---

## 2. Structure des Dossiers

```
sources/app4/
├── components/
│   ├── admin/                          # Composants réutilisables
│   │   ├── ActionButton.vue            # Boutons d'action
│   │   ├── AthleteAutocomplete.vue     # Autocomplete recherche athlète
│   │   ├── AthleteEditModal.vue        # Modal modification athlète
│   │   ├── Card.vue                    # Carte mobile
│   │   ├── CardList.vue               # Liste de cartes
│   │   ├── ClubMap.client.vue         # Carte Leaflet des clubs (client-only)
│   │   ├── CompetitionAutocomplete.vue # Autocomplete compétition
│   │   ├── CompetitionGroupedSelect.vue # Sélecteur compétitions groupées par section
│   │   ├── CompetitionMultiSelect.vue  # Sélecteur multi-compétitions
│   │   ├── CompetitionSingleSelect.vue # Sélecteur compétition unique
│   │   ├── ConfirmModal.vue           # Modal de confirmation
│   │   ├── ContextBadge.vue           # Badge contextuel
│   │   ├── EventGroupSelect.vue       # Sélecteur événement-groupe
│   │   ├── Header.vue                 # En-tête admin
│   │   ├── LegacyRedirect.vue        # Redirection vers legacy PHP
│   │   ├── Modal.vue                  # Modal générique
│   │   ├── PageHeader.vue             # En-tête de page avec titre et bouton retour
│   │   ├── Pagination.vue             # Pagination
│   │   ├── PlayerAutocomplete.vue     # Autocomplete recherche joueur
│   │   ├── PointsGridEditor.vue       # Éditeur de grille de points
│   │   ├── RefereeAutocomplete.vue    # Autocomplete recherche arbitre
│   │   ├── ScrollToTop.vue            # Bouton scroll to top
│   │   ├── TextAutocomplete.vue       # Autocomplete texte générique
│   │   ├── ToggleButton.vue           # Toggle on/off
│   │   ├── Toolbar.vue                # Barre d'outils (search, add, bulk delete)
│   │   ├── UserEditModal.vue          # Modal édition utilisateur
│   │   ├── UserMandateForm.vue        # Formulaire mandat utilisateur
│   │   ├── WorkContextSelector.vue    # Sélecteur de contexte de travail
│   │   ├── WorkContextSummary.vue     # Résumé du contexte de travail
│   │   └── tv/                        # Composants TV Control
│   │       ├── ChannelPanel.vue       # Panneau de canal TV
│   │       ├── ChannelSelector.vue    # Sélecteur de canal
│   │       ├── ConditionalParams.vue  # Paramètres conditionnels
│   │       ├── GlobalBar.vue          # Barre globale TV
│   │       ├── LabelsModal.vue        # Modal labels personnalisés
│   │       ├── PlayerNumberGrid.vue   # Grille numéros joueurs
│   │       ├── PresentationPreview.vue # Aperçu présentation
│   │       ├── PresentationSelector.vue # Sélecteur de présentation
│   │       └── ScenarioEditor.vue     # Éditeur de scénarios
│   ├── documents/
│   │   └── DocumentsCompetitionSummary.vue # Résumé compétition pour documents
│   ├── operations/                     # Onglets page opérations
│   │   ├── CodesTab.vue               # Changement codes
│   │   ├── ImagesTab.vue              # Gestion images
│   │   ├── ImportExportTab.vue        # Import/Export événements
│   │   ├── PlayersTab.vue             # Fusion joueurs
│   │   ├── SeasonsTab.vue             # Gestion saisons
│   │   ├── SystemTab.vue              # Cache système
│   │   └── TeamsTab.vue               # Opérations équipes
│   └── schema/                         # Composants visualisation schéma compétition
│       ├── SchemaChptGameday.vue      # Journée championnat
│       ├── SchemaChptLayout.vue       # Layout championnat
│       ├── SchemaCpBracketMatch.vue   # Match bracket coupe
│       ├── SchemaCpLayout.vue         # Layout coupe
│       ├── SchemaCpPoolTable.vue      # Tableau poule coupe
│       ├── SchemaHeader.vue           # En-tête schéma
│       └── SchemaMatchResult.vue      # Résultat match
├── composables/
│   ├── useApi.ts                       # Client API avec auth et gestion erreurs
│   ├── useAuth.ts                      # Gestion authentification (login, logout, token)
│   ├── useBracketDisplay.ts           # Logique d'affichage brackets (coupe)
│   ├── useCompetitionCopyApi.ts       # API copie de compétition
│   ├── useCompetitionsApi.ts          # API compétitions
│   ├── useLegacyRedirect.ts          # Redirection vers pages legacy
│   ├── useOnlineStatus.ts            # Détection état réseau
│   ├── usePresencePermissions.ts     # Permissions page présence
│   └── useTvUrl.ts                    # Construction URL TV
├── i18n/
│   └── locales/
│       ├── fr.json                     # Traductions françaises
│       └── en.json                     # Traductions anglaises
├── layouts/
│   └── admin.vue                       # Layout principal (header, menu, footer)
├── middleware/
│   └── auth.ts                         # Vérification JWT token
├── pages/                              # Routes (file-based routing)
│   ├── index.vue                       # Page d'accueil (/)
│   ├── login.vue                       # Connexion (/login)
│   ├── reset-password.vue              # Réinitialisation mot de passe (/reset-password)
│   ├── select-mandate.vue              # Sélection mandat (/select-mandate)
│   ├── athletes/index.vue             # Athlètes (/athletes)
│   ├── clubs/
│   │   ├── index.vue                  # Clubs (/clubs)
│   │   └── team/[numero].vue          # Détail équipe club (/clubs/team/:numero)
│   ├── competitions/
│   │   ├── index.vue                  # Gestion compétitions (/competitions)
│   │   └── copy.vue                   # Copie système de jeu (/competitions/copy)
│   ├── documents/index.vue            # Documents (/documents)
│   ├── events/index.vue               # Événements (/events)
│   ├── gamedays/
│   │   ├── index.vue                  # Journées/Phases (/gamedays)
│   │   └── schema.vue                 # Schéma compétition (/gamedays/schema)
│   ├── games/index.vue                # Matchs (/games)
│   ├── groups/index.vue               # Groupes (/groups)
│   ├── journal/index.vue              # Journal des actions (/journal)
│   ├── operations/index.vue           # Opérations système (/operations)
│   ├── presence/
│   │   ├── team/[teamId].vue          # Composition équipe (/presence/team/:teamId)
│   │   └── match/[matchId]/team/[teamCode].vue  # Composition match (/presence/match/:matchId/team/:teamCode)
│   ├── rankings/
│   │   ├── index.vue                  # Classements (/rankings)
│   │   └── initial.vue                # Classement initial (/rankings/initial)
│   ├── rc/index.vue                   # Responsables de compétition (/rc)
│   ├── stats/
│   │   ├── index.vue                  # Statistiques (/stats)
│   │   └── [type]/[saison]/[competition].vue  # Stats détaillées
│   ├── teams/index.vue                # Équipes (/teams)
│   ├── tv/index.vue                   # Contrôle TV (/tv)
│   └── users/index.vue                # Utilisateurs (/users)
├── stores/                             # Pinia stores
│   ├── authStore.ts                    # Authentification utilisateur
│   ├── filtersStore.ts                # Filtres legacy (utilisé uniquement par stats dynamique)
│   ├── presenceStore.ts               # État page présence (composition)
│   ├── statsStore.ts                  # État page statistiques
│   └── workContextStore.ts            # Contexte de travail (saison + périmètre)
├── types/                              # Types TypeScript
│   ├── index.ts                        # Types généraux (Season, Competition, etc.)
│   ├── athletes.ts                    # Types athlètes
│   ├── clubs.ts                       # Types clubs
│   ├── competition-copy.ts            # Types copie compétition
│   ├── competitions.ts               # Types compétitions
│   ├── gamedays.ts                    # Types journées
│   ├── games.ts                       # Types matchs
│   ├── operations.ts                  # Types opérations
│   ├── presence.ts                    # Types présence/composition
│   ├── rankings.ts                    # Types classements
│   ├── rc.ts                          # Types responsables compétition
│   ├── schema.ts                      # Types schéma compétition
│   ├── teams.ts                       # Types équipes
│   ├── tv.ts                          # Types contrôle TV
│   └── users.ts                       # Types utilisateurs
├── nuxt.config.ts                      # Configuration Nuxt
└── tailwind.config.ts                  # Configuration Tailwind
```

---

## 3. Stores Pinia

### 3.1 authStore

Gestion de l'authentification et du profil utilisateur.

```typescript
interface AuthState {
  token: string | null
  user: User | null
}

interface User {
  id: number
  name: string
  firstname: string
  profile: number
  roles: string[]
}

// Getters
authStore.isAuthenticated  // boolean
authStore.profile          // number (niveau de profil)
authStore.hasProfile(n)    // boolean (profile <= n)

// Actions
authStore.login(username, password)
authStore.logout()
authStore.loadFromStorage()
```

### 3.2 workContextStore

Contexte de travail partagé entre toutes les pages.

```typescript
interface WorkContextState {
  season: string                    // Code saison (ex: "2026")
  selectionType: 'section' | 'group' | 'competition' | 'event' | null
  sectionId: number | null          // ID section (1-5, 100)
  groupCode: string | null          // Code groupe (ex: "N1H")
  competitionCode: string | null    // Code compétition unique
  eventId: number | null            // ID événement
  competitionCodes: string[]        // Compétitions résultantes
  seasons: Season[]                 // Liste des saisons
  groups: CompetitionGroup[]        // Groupes de compétitions
  competitions: Competition[]       // Toutes les compétitions
  events: FilterEvent[]             // Liste des événements
  initialized: boolean
  loading: boolean
}

// Getters
workContext.hasValidContext        // boolean (saison + sélection valide)
workContext.activeSeason           // Season | undefined
workContext.contextLabel           // string (description du contexte)
workContext.competitionCount       // number
workContext.contextCompetitions    // Competition[] (filtrées)
workContext.availableSections      // Section[]
workContext.uniqueGroups           // Group[]
workContext.groupsBySection(id)    // Group[]

// Actions
workContext.initContext()          // Charge depuis localStorage + API
workContext.setSeason(code)        // Change la saison
workContext.selectSection(id)      // Sélectionne une section
workContext.selectGroup(code)      // Sélectionne un groupe
workContext.selectCompetition(code)// Sélectionne une compétition
workContext.selectEvent(id)        // Sélectionne un événement
workContext.clearSelection()       // Efface la sélection
workContext.clearContext()         // Efface tout
```

**Clés localStorage** :
- `kpi_admin_work_season`
- `kpi_admin_work_type`
- `kpi_admin_work_section`
- `kpi_admin_work_group`
- `kpi_admin_work_competition`
- `kpi_admin_work_event`

### 3.3 presenceStore

Gestion de l'état de la page composition d'équipe/match.

---

## 4. Profils Utilisateurs

| Profil | Droits |
|--------|--------|
| 1 | Super Admin - Tous les droits |
| 2 | Admin - Suppression, options avancées |
| 3 | Éditeur - Ajout/modification |
| 4 | Modérateur - Toggle publication |
| 9 | Utilisateur - Lecture documents/matchs |
| 10 | Lecteur - Lecture seule |

Vérification des droits :
```typescript
authStore.hasProfile(3)  // true si profile <= 3
```

---

## 5. Composants Admin Réutilisables

### AdminToolbar
Barre d'outils avec recherche, bouton ajouter, suppression en masse.

```vue
<AdminToolbar
  v-model:search="search"
  :search-placeholder="t('common.search')"
  :add-label="t('add')"
  :show-add="canEdit"
  :show-bulk-delete="canDelete"
  :selected-count="selectedCodes.length"
  @add="openAddModal"
  @bulk-delete="openBulkDeleteModal"
/>
```

### AdminModal
Modal avec titre et contenu personnalisable.

```vue
<AdminModal
  :open="isOpen"
  :title="t('modal.title')"
  max-width="xl"
  @close="close"
>
  <template #default>...</template>
</AdminModal>
```

### AdminConfirmModal
Modal de confirmation avec loading.

```vue
<AdminConfirmModal
  :open="isOpen"
  :title="t('confirm.title')"
  :message="t('confirm.message')"
  :item-name="item.name"
  :loading="isDeleting"
  @close="close"
  @confirm="confirm"
/>
```

### AdminToggleButton
Bouton toggle avec icônes personnalisables.

```vue
<AdminToggleButton
  :active="item.published"
  active-icon="heroicons:eye-solid"
  inactive-icon="heroicons:eye-slash-solid"
  active-color="green"
  @toggle="togglePublish(item)"
/>
```

### AdminPagination
Pagination avec sélection du nombre d'éléments.

```vue
<AdminPagination
  v-model:page="page"
  v-model:limit="limit"
  :total="total"
  :total-pages="totalPages"
/>
```

### AdminPageHeader
En-tête de page avec titre, breadcrumb et bouton retour.

```vue
<AdminPageHeader
  :title="t('page.title')"
  :back-link="'/competitions'"
  :back-label="t('common.back')"
/>
```

### AdminLegacyRedirect
Composant de redirection vers page PHP legacy.

```vue
<AdminLegacyRedirect
  php-page="GestionCalendrier"
  :title="t('page.title')"
/>
```

### AdminWorkContextSummary
Rappel du contexte (saison/périmètre) en haut de page.

### AdminTextAutocomplete
Champ texte avec suggestions dynamiques (communes, noms, etc.).

### AdminRefereeAutocomplete
Autocomplete spécialisé pour la recherche d'arbitres.

### AdminPlayerAutocomplete
Autocomplete spécialisé pour la recherche de joueurs.

### AdminAthleteAutocomplete
Autocomplete spécialisé pour la recherche d'athlètes.

### AdminCompetitionSingleSelect / MultiSelect / GroupedSelect
Sélecteurs de compétitions avec différents modes.

### AdminPointsGridEditor
Éditeur de grille de points pour compétitions.

### AdminUserEditModal / UserMandateForm
Modals et formulaires pour la gestion des utilisateurs et de leurs mandats.

---

## 6. API Client (useApi)

```typescript
const api = useApi()

// GET
const data = await api.get<ResponseType>('/endpoint', { param: 'value' })

// POST
const result = await api.post<ResponseType>('/endpoint', { data })

// PUT
await api.put('/endpoint/id', { data })

// PATCH
await api.patch('/endpoint/id', { data })

// DELETE
await api.del('/endpoint/id')
```

**Configuration** :
- Base URL : `https://kpi.localhost/api2`
- Headers : `Authorization: Bearer {token}`
- Gestion automatique des erreurs (toast notifications)

---

## 7. Sections et Groupes

### Sections (ID)
| ID | Libellé |
|----|---------|
| 1 | Compétitions Internationales |
| 2 | Compétitions Nationales |
| 3 | Compétitions Régionales |
| 4 | Tournois Internationaux |
| 5 | Continents |
| 100 | Divers |

### Clés i18n
- `context.sections.1` → "Compétitions Internationales"
- `context.sections.2` → "Compétitions Nationales"
- etc.

---

## 8. Traductions i18n

Structure des fichiers de traduction :

```json
{
  "app": {},           // Titre application
  "menu": {},          // Menu principal
  "dashboard": {},     // Page d'accueil
  "context": {},       // Contexte de travail
  "competitions": {},  // Page compétitions
  "events": {},        // Page événements
  "documents": {},     // Page documents
  "stats": {},         // Page statistiques
  "operations": {},    // Page opérations
  "games": {},         // Page matchs
  "gamedays": {},      // Page journées
  "teams": {},         // Page équipes
  "rankings": {},      // Page classements
  "athletes": {},      // Page athlètes
  "clubs": {},         // Page clubs
  "users": {},         // Page utilisateurs
  "tv": {},            // Page contrôle TV
  "journal": {},       // Page journal
  "schema": {},        // Schéma compétition
  "presence": {},      // Page présence/composition
  "rc": {},            // Page responsables compétition
  "common": {},        // Textes communs (yes, no, save, etc.)
  "errors": {}         // Messages d'erreur
}
```

Usage :
```typescript
const { t } = useI18n()
t('competitions.title')
t('context.competitions_count', { count: 5 })
```

---

## 9. Patterns de Code

### Page standard avec contexte de travail

```typescript
<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: 'auth'
})

const { t } = useI18n()
const api = useApi()
const authStore = useAuthStore()
const workContext = useWorkContextStore()

// State
const loading = ref(false)
const items = ref<Item[]>([])

// Load data
const loadItems = async () => {
  if (!workContext.initialized) return
  loading.value = true
  try {
    const response = await api.get<Response>('/endpoint', {
      season: workContext.season,
      // Filter by context if needed
      ...(workContext.hasValidContext && {
        codes: workContext.competitionCodes.join(',')
      })
    })
    items.value = response.items
  } finally {
    loading.value = false
  }
}

// Watch context changes
watch(
  () => [workContext.initialized, workContext.competitionCodes],
  () => { if (workContext.initialized) loadItems() },
  { deep: true }
)

onMounted(async () => {
  await workContext.initContext()
})
</script>
```

### Cellules éditables inline (.editable-cell)

Pour les champs modifiables directement dans un tableau (click-to-edit), utiliser la classe CSS `.editable-cell` définie dans `assets/css/admin.css`. Elle fournit un indicateur visuel subtil et moderne signalant que le champ est cliquable et éditable.

**Style** : fond gris-bleu très clair + bordure inférieure pointillée. Au survol : fond bleu léger + bordure bleue.

### Dropdown Teleport (overflow-safe)

Pour les menus déroulants à l'intérieur de conteneurs `overflow-hidden` (tables, groupes avec scroll), utiliser `<Teleport to="body">` avec `position: fixed` et calcul de position via `getBoundingClientRect()`.

---

## 10. Pages Implémentées vs Legacy

| Page | Route | Statut |
|------|-------|--------|
| Accueil | `/` | Implémentée |
| Connexion | `/login` | Implémentée |
| Reset mot de passe | `/reset-password` | Implémentée |
| Sélection mandat | `/select-mandate` | Implémentée |
| Compétitions | `/competitions` | Implémentée (contexte) |
| Copie compétition | `/competitions/copy` | Implémentée (recherche schémas, copie structure) |
| Documents | `/documents` | Implémentée (contexte) |
| Événements | `/events` | Implémentée |
| Statistiques | `/stats` | Implémentée (contexte) |
| Opérations | `/operations` | Implémentée |
| Groupes | `/groups` | Implémentée |
| Matchs | `/games` | Implémentée (contexte, CRUD, bulk actions, arbitres) |
| Journées | `/gamedays` | Implémentée (contexte, CRUD, bulk, schéma, officiels) |
| Schéma compétition | `/gamedays/schema` | Implémentée (visualisation CHPT/CP) |
| Équipes | `/teams` | Implémentée (contexte, CRUD, couleurs, logos) |
| Classements | `/rankings` | Implémentée (contexte, calcul, publication, phases CP, transfert) |
| Classement initial | `/rankings/initial` | Implémentée (CHPT uniquement, édition inline, RAZ) |
| Athlètes | `/athletes` | Implémentée (recherche + fiche + participations) |
| Clubs | `/clubs` | Implémentée (carte Leaflet, détail, équipes) |
| Détail équipe club | `/clubs/team/:numero` | Implémentée |
| Utilisateurs | `/users` | Implémentée (CRUD, mandats, reset password) |
| Responsables compétition | `/rc` | Implémentée |
| Présence (équipe) | `/presence/team/:teamId` | Implémentée |
| Présence (match) | `/presence/match/:matchId/team/:teamCode` | Implémentée |
| Contrôle TV | `/tv` | Implémentée (canaux, présentations, scénarios, labels) |
| Journal | `/journal` | Implémentée (consultation logs, filtres utilisateur/action) |

---

**Document créé le** : 2026-02-03
**Dernière mise à jour** : 2026-03-11
