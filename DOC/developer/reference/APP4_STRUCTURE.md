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
- **API Client** : Composable `useApi()` personnalisé

---

## 2. Structure des Dossiers

```
sources/app4/
├── components/
│   └── admin/                      # Composants réutilisables
│       ├── WorkContextSelector.vue # Sélecteur de contexte de travail
│       ├── ActionButton.vue        # Boutons d'action
│       ├── Card.vue                # Carte mobile
│       ├── CardList.vue            # Liste de cartes
│       ├── ConfirmModal.vue        # Modal de confirmation
│       ├── LegacyRedirect.vue      # Redirection vers legacy PHP
│       ├── Modal.vue               # Modal générique
│       ├── Pagination.vue          # Pagination
│       ├── ScrollToTop.vue         # Bouton scroll to top
│       ├── ToggleButton.vue        # Toggle on/off
│       └── Toolbar.vue             # Barre d'outils (search, add, bulk delete)
├── composables/
│   └── useApi.ts                   # Client API avec auth et gestion erreurs
├── i18n/
│   └── locales/
│       ├── fr.json                 # Traductions françaises (~750 lignes)
│       └── en.json                 # Traductions anglaises (~750 lignes)
├── layouts/
│   └── admin.vue                   # Layout principal (header, menu, footer)
├── middleware/
│   └── auth.ts                     # Vérification JWT token
├── pages/                          # Routes (file-based routing)
│   ├── index.vue                   # Page d'accueil (/)
│   ├── login.vue                   # Connexion (/login)
│   ├── competitions/index.vue      # Gestion compétitions (/competitions)
│   ├── documents/index.vue         # Documents (/documents)
│   ├── events/index.vue            # Événements (/events)
│   ├── games/index.vue             # Matchs (/games)
│   ├── gamedays/index.vue          # Journées/Phases (/gamedays)
│   ├── groups/index.vue            # Groupes (/groups)
│   ├── operations/index.vue        # Opérations système (/operations)
│   ├── rankings/
│   │   ├── index.vue               # Classements (/rankings)
│   │   └── initial.vue             # Classement initial (/rankings/initial)
│   ├── stats/
│   │   ├── index.vue               # Statistiques (/stats)
│   │   └── [type]/[saison]/[competition].vue
│   ├── teams/index.vue             # Équipes (/teams)
│   ├── athletes/index.vue          # Athlètes (/athletes)
│   ├── clubs/index.vue             # Clubs (/clubs)
│   └── users/index.vue             # Utilisateurs (/users)
├── stores/                         # Pinia stores
│   ├── authStore.ts                # Authentification utilisateur
│   ├── workContextStore.ts         # Contexte de travail (saison + périmètre)
│   ├── filtersStore.ts             # Filtres legacy (utilisé uniquement par la route stats dynamique)
│   └── statsStore.ts               # État page statistiques
├── types/                          # Types TypeScript
│   ├── index.ts                    # Types généraux (Season, Competition, etc.)
│   ├── competitions.ts             # Types spécifiques compétitions
│   └── teams.ts                    # Types spécifiques équipes
├── nuxt.config.ts                  # Configuration Nuxt
└── tailwind.config.ts              # Configuration Tailwind
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

### AdminLegacyRedirect
Composant de redirection vers page PHP legacy.

```vue
<AdminLegacyRedirect
  php-page="GestionCalendrier"
  :title="t('page.title')"
/>
```

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

### Rappel du contexte en haut de page

Placé **au-dessus du titre** de chaque page utilisant le contexte. En mobile, saison/périmètre/bouton s'empilent sur 3 lignes. En desktop, tout est sur une ligne.

```vue
<!-- Work Context Summary -->
<div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
  <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center sm:justify-between gap-2 sm:gap-4">
    <div class="flex items-center gap-2">
      <UIcon name="i-heroicons-calendar" class="w-5 h-5 text-blue-600 shrink-0" />
      <span class="text-sm text-gray-600">{{ t('context.season') }}:</span>
      <span class="font-semibold text-gray-900">{{ workContext.season || '-' }}</span>
    </div>
    <div v-if="workContext.hasValidContext" class="flex items-center gap-2">
      <UIcon name="i-heroicons-funnel" class="w-5 h-5 text-blue-600 shrink-0" />
      <span class="text-sm text-gray-600">{{ t('context.scope') }}:</span>
      <span class="font-semibold text-gray-900">{{ workContext.contextLabel }}</span>
      <span class="text-sm text-gray-500">
        ({{ t('context.competitions_count', { count: workContext.competitionCount }) }})
      </span>
    </div>
    <div v-else class="flex items-center gap-2 text-sm text-amber-600">
      <UIcon name="i-heroicons-exclamation-triangle" class="w-4 h-4 shrink-0" />
      {{ t('context.no_context') }}
    </div>
    <NuxtLink
      to="/"
      class="inline-flex items-center gap-1 self-start px-3 py-1.5 text-sm font-medium text-blue-700 bg-blue-100 hover:bg-blue-200 rounded-lg transition-colors"
    >
      <UIcon name="i-heroicons-pencil-square" class="w-4 h-4" />
      {{ t('context.change') }}
    </NuxtLink>
  </div>
</div>
```

### Cellules éditables inline (.editable-cell)

Pour les champs modifiables directement dans un tableau (click-to-edit), utiliser la classe CSS `.editable-cell` définie dans `assets/css/admin.css`. Elle fournit un indicateur visuel subtil et moderne signalant que le champ est cliquable et éditable.

**Style** : fond gris-bleu très clair + bordure inférieure pointillée. Au survol : fond bleu léger + bordure bleue.

```vue
<!-- Affichage normal (non éditable) -->
<span>{{ value }}</span>

<!-- Champ éditable inline -->
<span
  :class="canEdit ? 'editable-cell' : ''"
  @click="startEdit(item, 'field')"
>
  {{ value }}
</span>

<!-- Mode édition (input remplace le span) -->
<template v-if="editingCell?.id === item.id && editingCell.field === 'field'">
  <input
    :id="`inline-edit-${item.id}-field`"
    v-model="editingValue"
    class="w-14 px-1 py-0.5 border border-blue-400 rounded text-center text-sm focus:ring-2 focus:ring-blue-500"
    @keydown="handleInlineKeydown"
    @blur="saveInlineEdit"
  />
</template>
```

**Appliquer systématiquement** sur toutes les pages app4 où des champs sont éditables inline dans un tableau ou une liste (poule, tirage, statut, etc.).

### Dropdown Teleport (overflow-safe)

Pour les menus déroulants à l'intérieur de conteneurs `overflow-hidden` (tables, groupes avec scroll), utiliser `<Teleport to="body">` avec `position: fixed` et calcul de position via `getBoundingClientRect()`.

```vue
<script setup>
const openDropdownId = ref(null)
const dropdownStyle = ref({ top: '0px', left: '0px' })

const toggleDropdown = (id, event) => {
  if (openDropdownId.value === id) { openDropdownId.value = null; return }
  const rect = (event.currentTarget as HTMLElement).getBoundingClientRect()
  dropdownStyle.value = {
    top: `${rect.bottom + 4}px`,
    left: `${Math.min(rect.left, window.innerWidth - 200)}px`
  }
  openDropdownId.value = id
}
</script>

<template>
  <!-- Trigger -->
  <button class="dropdown-trigger" @click="toggleDropdown(item.id, $event)">...</button>

  <!-- Menu téléporté -->
  <Teleport to="body">
    <div v-if="openDropdownId === item.id" class="dropdown-menu fixed ..." :style="dropdownStyle">
      ...
    </div>
  </Teleport>
</template>
```

Ajouter un listener `click` global pour fermer le dropdown lors d'un clic en dehors.

---

## 10. Pages Implémentées vs Legacy

| Page | Route | Statut |
|------|-------|--------|
| Accueil | `/` | Implémentée |
| Connexion | `/login` | Implémentée |
| Compétitions | `/competitions` | Implémentée (contexte) |
| Documents | `/documents` | Implémentée (contexte) |
| Événements | `/events` | Implémentée |
| Statistiques | `/stats` | Implémentée (contexte) |
| Opérations | `/operations` | Implémentée |
| Groupes | `/groups` | Implémentée |
| Matchs | `/games` | Legacy redirect |
| Équipes | `/teams` | Implémentée (contexte) |
| Journées | `/gamedays` | Legacy redirect |
| Classements | `/rankings` | Legacy redirect |
| Athlètes | `/athletes` | Legacy redirect |
| Clubs | `/clubs` | Implémentée (carte Leaflet) |
| Utilisateurs | `/users` | Legacy redirect |

---

**Document créé le** : 2026-02-03
**Dernière mise à jour** : 2026-02-09
