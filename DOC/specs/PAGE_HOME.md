# Spécification - Page d'Accueil avec Contexte de Travail

## 1. Vue d'ensemble

La page d'accueil permet de sélectionner un contexte de travail (saison + périmètre de compétitions) qui sera utilisé comme filtre par défaut dans toutes les pages de gestion. Ce contexte est persisté en localStorage.

**Route** : `/`

**Accès** : Authentifié (tous profils, filtré selon droits utilisateur)

---

## 2. Modèle de Sélection Multi-Niveaux

### 2.1 Hiérarchie des Sélections

L'utilisateur peut sélectionner un périmètre de travail à différents niveaux de granularité :

```
Saison
  └── Section (ex: "Compétitions Nationales")
        └── Groupe (ex: "N1H" → N1H + NPOH)
              └── Compétition individuelle (ex: "N1H")
  └── Événement (ex: "Championnat de France" → toutes compétitions liées)
```

### 2.2 Types de Sélection

| Type | Description | Exemple | Compétitions résultantes |
|------|-------------|---------|--------------------------|
| `all` | Toutes les compétitions (auxquelles l'utilisateur a droit) | Toutes | Toutes les compétitions autorisées pour la saison |
| `selection` | Sélection multiple de compétitions | Choix de N1H, N1F, N2H | N1H, N1F, N2H |
| `section` | Toute une section complète | Section 2 (Nationales) | N1H, NPOH, N1F, NPOF, N2H, N2F, etc. |
| `group` | Tout un groupe | Groupe "N1H" | N1H, NPOH |
| `event` | Un événement | Événement 123 | Toutes les compétitions dont les journées sont liées à cet événement |

**Ordre d'affichage** : Toutes les compétitions → Sélection → Section complète → Groupe → Événement

### 2.3 Structure de Données du Contexte

```typescript
interface WorkContext {
  season: string              // Code saison (ex: "2026")

  // Type de sélection
  selectionType: 'all' | 'selection' | 'section' | 'group' | 'event' | null

  // Valeur selon le type
  sectionId: number | null            // ID de section (1, 2, 3...)
  groupCode: string | null            // Code groupe (ex: "N1H")
  selectedCompetitionCodes: string[]  // Sélection multiple de compétitions
  eventId: number | null              // ID événement

  // Compétitions effectives (calculées)
  competitionCodes: string[]  // Liste des codes compétitions résultants

  // Sélection page-level (persistée, partagée entre pages)
  pageCompetitionCode: string // Compétition unique pour pages Documents/Équipes/Classements
}
```

---

## 3. Utilisation selon les Pages

### 3.1 Principe Général

La page d'accueil définit un **périmètre de compétitions disponibles**. Les autres pages permettent ensuite de sélectionner parmi ce périmètre :
- Soit toutes les compétitions du périmètre
- Soit une sélection partielle (multi-select)
- Soit une seule compétition (pour les pages qui l'exigent)

### 3.2 Page Compétitions

La page Compétitions affiche **directement** toutes les compétitions du périmètre défini sur la page d'accueil. Pas besoin de filtre supplémentaire puisque cette page liste déjà les compétitions.

| Page | Comportement |
|------|--------------|
| **Compétitions** | Liste automatiquement toutes les compétitions du périmètre |

### 3.3 Pages avec Sélection Multiple

Ces pages permettent de filtrer sur une ou plusieurs compétitions du périmètre :

| Page | Composant | Comportement |
|------|-----------|--------------|
| **Journées/Phases** | Multi-select + "Toutes" | Liste les journées des compétitions sélectionnées |
| **Matchs** | Multi-select + "Toutes" | Liste les matchs des compétitions sélectionnées |
| **Statistiques** | Multi-select + "Toutes" | Agrège les stats sur les compétitions sélectionnées |

**UI du sélecteur :**
```
┌─────────────────────────────────────────────────┐
│ Compétitions (du contexte)                      │
│ ┌─────────────────────────────────────────────┐ │
│ │ ☑ Toutes (2 compétitions)                   │ │
│ │ ─────────────────────────────               │ │
│ │ ☑ N1H - Nationale 1 Hommes                  │ │
│ │ ☑ NPOH - Play-offs N1 Hommes                │ │
│ └─────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────┘
```

### 3.4 Pages Mono-Compétition

Ces pages ne peuvent travailler qu'avec une seule compétition à la fois :

| Page | Comportement |
|------|--------------|
| **Documents** | Dropdown simple pour choisir UNE compétition |
| **Équipes** | Dropdown simple pour choisir UNE compétition |
| **Classements** | Dropdown simple pour choisir UNE compétition |

### 3.5 Exemple Concret

**Contexte de travail** : Saison 2026, Groupe "N1H" → Périmètre = [N1H, NPOH]

- Sur la page **Compétitions** :
  - Affiche automatiquement N1H et NPOH (pas de filtre supplémentaire)
- Sur la page **Matchs** :
  - Multi-select avec N1H et NPOH disponibles
  - Par défaut "Toutes" coché → affiche tous les matchs
  - Peut décocher pour ne voir que N1H ou NPOH
- Sur la page **Documents** :
  - Dropdown simple avec N1H et NPOH
  - Doit choisir UNE compétition pour générer les PDFs

---

## 4. Structure de la Page d'Accueil

### 4.1 Vue Desktop (2 colonnes)

```
┌─────────────────────────────────────────────────────────────────┐
│  Tableau de bord                                                │
│  Bienvenue, {prénom}                                            │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌─────────────────────────────────────────────────────────────┐│
│  │  ⚙️  Contexte de travail                                    ││
│  │                                                             ││
│  │  ┌───────────────────────┬────────────────────────────────┐ ││
│  │  │ Saison                │ Périmètre                      │ ││
│  │  ├───────────────────────┼────────────────────────────────┤ ││
│  │  │ ▼ 2026 *              │ ● Toutes les compétitions      │ ││
│  │  │                       │                                │ ││
│  │  │                       │ ○ Sélection                    │ ││
│  │  │                       │   [Checkbox list multi-select] │ ││
│  │  │                       │                                │ ││
│  │  │                       │ ○ Section complète             │ ││
│  │  │                       │   [▼ Compétitions Nationales]  │ ││
│  │  │                       │                                │ ││
│  │  │                       │ ○ Groupe                       │ ││
│  │  │                       │   [▼ — Comp. Int. —]           │ ││
│  │  │                       │   [    ECCM (2) / N1H (2) ...] │ ││
│  │  │                       │                                │ ││
│  │  │                       │ ○ Événement                    │ ││
│  │  │                       │   [▼ Championnat de France]    │ ││
│  │  └───────────────────────┴────────────────────────────────┘ ││
│  │                                                             ││
│  │  ✅ Contexte : 2026 / Toutes les compétitions (12 compét.) ││
│  │     → N1H - Nationale 1 Hommes                              ││
│  │     → NPOH - Play-offs N1 Hommes                            ││
│  │     → ... (10 autres)                                       ││
│  └─────────────────────────────────────────────────────────────┘│
│                                                                 │
│  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐ ┌──────────┐│
│  │ 🏆 Compétit. │ │ 👥 Équipes   │ │ 📅 J./Phases │ │ 📊 Class.││
│  └──────────────┘ └──────────────┘ └──────────────┘ └──────────┘│
│  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐              │
│  │ 📄 Documents │ │ ⚽ Matchs    │ │ 📈 Stats     │              │
│  └──────────────┘ └──────────────┘ └──────────────┘              │
│                                                                 │
│  ⚠️ Version Beta                                                │
└─────────────────────────────────────────────────────────────────┘
```

### 4.2 Vue Mobile (1 colonne)

```
┌─────────────────────────────────────────────────┐
│  Tableau de bord                                │
│  Bienvenue, {prénom}                            │
├─────────────────────────────────────────────────┤
│                                                 │
│  ⚙️  Contexte de travail                        │
│                                                 │
│  Saison                                         │
│  ▼ 2026 *                                       │
│                                                 │
│  Périmètre                                      │
│  ● Toutes les compétitions                      │
│  ○ Sélection (checkbox list)                    │
│  ○ Section complète                             │
│  ○ Groupe                                       │
│  ○ Événement                                    │
│                                                 │
│  ✅ 2026 / Toutes (12 compétitions)             │
│                                                 │
│  [Cartes de navigation empilées]                │
│                                                 │
└─────────────────────────────────────────────────┘
```

---

## 5. Store Pinia : workContextStore

### 5.1 État (State)

```typescript
interface WorkContextState {
  // Saison
  season: string

  // Type de sélection
  selectionType: 'all' | 'selection' | 'section' | 'group' | 'event' | null

  // Valeurs de sélection (une seule remplie selon le type)
  sectionId: number | null
  groupCode: string | null
  selectedCompetitionCodes: string[]  // Sélection multiple (mode "Sélection")
  eventId: number | null

  // Compétitions résultantes (calculées lors de la sélection)
  competitionCodes: string[]

  // Page-level single competition selection (persisté, partagé entre pages)
  pageCompetitionCode: string

  // Données de référence (chargées depuis API)
  seasons: Season[]
  groups: CompetitionGroup[]  // Groupé par section
  competitions: Competition[]
  events: FilterEvent[]

  // État
  initialized: boolean
  initializing: boolean  // Garde contre concurrent initialization
  loading: boolean
}

interface Section {
  id: number
  label: string
  labelKey: string  // Clé i18n
}

interface Group {
  code: string
  libelle: string
  section: number
  competitions: string[]  // Codes des compétitions du groupe
}

interface Competition {
  code: string
  libelle: string
  soustitre: string | null
  codeRef: string | null  // Groupe parent
  section: number
  codeTypeclt: string
}
```

### 5.2 Getters

| Getter | Type | Description |
|--------|------|-------------|
| `hasValidContext` | boolean | true si saison + sélectionType défini |
| `activeSeason` | Season \| undefined | La saison active |
| `sections` | Section[] | Liste des sections (hardcodée) |
| `availableSections` | Section[] | Sections qui ont des compétitions |
| `uniqueGroups` | Group[] | Groupes uniques (par codeRef) |
| `groupsBySection(id)` | (id: number) => Group[] | Groupes filtrés par section |
| `contextLabel` | string | Description du contexte (ex: "Groupe N1H") |
| `competitionCount` | number | Nombre de compétitions sélectionnées |
| `isSingleCompetition` | boolean | true si une seule compétition |
| `firstCompetition` | Competition \| undefined | Première compétition (pour pages mono) |
| `contextCompetitions` | Competition[] | Compétitions du contexte (objets complets) |
| `pageCompetition` | Competition \| undefined | Compétition sélectionnée pour pages mono |

### 5.3 Actions

| Action | Description |
|--------|-------------|
| `initContext()` | Charge depuis localStorage et API, migration de l'ancien type 'competition' |
| `setSeason(code)` | Change la saison, recharge les données, défaut à 'all' |
| `selectAll()` | Sélectionne toutes les compétitions (défaut) |
| `selectCompetitions(codes)` | Sélection multiple de compétitions (mode "Sélection") |
| `selectSection(sectionId)` | Sélectionne toute une section complète |
| `selectGroup(groupCode)` | Sélectionne tout un groupe |
| `selectEvent(eventId)` | Sélectionne un événement (charge les compétitions via API) |
| `setPageCompetition(code)` | Définit la compétition page-level (persisté) |
| `resetPageCompetition()` | Réinitialise la sélection page-level (appelé lors du changement de contexte) |
| `clearSelection()` | Réinitialise la sélection (garde la saison) |
| `clearContext()` | Réinitialise tout |
| `loadSeasonData(api?)` | Charge groupes et compétitions pour la saison |
| `loadEvents(api?)` | Charge la liste des événements (filtrés par saison et droits) |
| `loadEventCompetitions(api?)` | Charge les compétitions d'un événement |
| `computeCompetitionCodes()` | Calcule competitionCodes selon selectionType |
| `saveToStorage()` | Sauvegarde en localStorage |

### 5.4 Persistance localStorage

```typescript
// Clés localStorage
const STORAGE_KEYS = {
  season: 'kpi_admin_work_season',
  selectionType: 'kpi_admin_work_type',
  sectionId: 'kpi_admin_work_section',
  groupCode: 'kpi_admin_work_group',
  selectedCompetitionCodes: 'kpi_admin_work_selections',  // Multi-select (mode "Sélection")
  eventId: 'kpi_admin_work_event',
  pageCompetitionCode: 'kpi_admin_work_page_competition',  // Page-level single competition
}
```

**Migration** : Lors de l'initialisation, l'ancien type `'competition'` est migré vers `'selection'` avec la compétition stockée dans `selectedCompetitionCodes`.

---

## 6. API Endpoints

### 6.1 Endpoints Existants

```
GET /api2/admin/filters/seasons
→ { seasons: [...], activeSeason: "2026" }

GET /api2/admin/filters/competitions?season=2026
→ { season: "2026", groups: [...] }
# groups = CompetitionGroup[] avec { section: number, competitions: Competition[] }

GET /api2/admin/filters/events?season=2026
→ { events: [...] }
# Filtre les événements par saison ET par compétitions auxquelles l'utilisateur a accès
```

### 6.2 Endpoint : Compétitions par Événement

```
GET /api2/admin/filters/event-competitions?eventId=123
Authorization: Bearer {token}
```

**Réponse :**
```json
{
  "eventId": 123,
  "competitions": [
    {
      "code": "N1H",
      "libelle": "Nationale 1 Hommes",
      "codeRef": "N1H"
    },
    {
      "code": "N1F",
      "libelle": "Nationale 1 Femmes",
      "codeRef": "N1F"
    }
  ]
}
```

**Implémentation :**
```sql
SELECT DISTINCT c.Code, c.Libelle, c.Code_ref
FROM kp_competition c
INNER JOIN kp_journee j ON j.Code_competition = c.Code AND j.Code_saison = c.Code_saison
INNER JOIN kp_evenement_journee ej ON ej.Id_journee = j.Id
WHERE ej.Id_evenement = ?
AND c.Code IN (...)  -- Filtre par compétitions accessibles à l'utilisateur
ORDER BY c.Code
```

**Note** : Cet endpoint est utilisé par `selectEvent()` dans le store pour charger les compétitions d'un événement.

---

## 7. Composants Vue

### 7.1 Structure des Fichiers

```
stores/
└── workContextStore.ts

components/admin/
├── WorkContextSelector.vue       # Sélecteur principal (page accueil)
├── CompetitionMultiSelect.vue    # Multi-select pour pages multi-compétition
└── CompetitionSingleSelect.vue   # Select simple pour pages mono-compétition

pages/
└── index.vue
```

### 7.2 WorkContextSelector.vue

Composant pour la page d'accueil avec les 5 modes de sélection :
1. **Toutes les compétitions** (défaut) : radio button simple
2. **Sélection** : radio button + checkbox list multi-select (groupé par section)
3. **Section complète** : radio button + dropdown des sections disponibles
4. **Groupe** : radio button + dropdown des groupes (optgroups par section)
5. **Événement** : radio button + dropdown des événements (filtrés par saison)

**Layout Desktop** : 2 colonnes (Saison à gauche | Périmètre à droite)
**Layout Mobile** : 1 colonne (Saison puis Périmètre empilés)

Le composant affiche également un résumé du contexte sélectionné avec la liste des compétitions résultantes.

### 7.3 CompetitionMultiSelect.vue

Composant multi-select pour les pages Compétitions, Journées, Matchs, Statistiques :

```vue
<template>
  <div class="mb-4">
    <label class="block text-sm font-medium text-gray-700 mb-1">
      {{ t('context.competitions_from_context') }}
    </label>
    <div class="border rounded-md p-2 max-h-48 overflow-y-auto">
      <!-- Option "Toutes" -->
      <label class="flex items-center gap-2 p-1 hover:bg-gray-50 cursor-pointer">
        <input
          type="checkbox"
          :checked="allSelected"
          :indeterminate="someSelected && !allSelected"
          @change="toggleAll"
        />
        <span class="font-medium">
          {{ t('context.all_competitions') }} ({{ availableCompetitions.length }})
        </span>
      </label>

      <hr class="my-1" />

      <!-- Compétitions individuelles -->
      <label
        v-for="comp in availableCompetitions"
        :key="comp.code"
        class="flex items-center gap-2 p-1 hover:bg-gray-50 cursor-pointer"
      >
        <input
          type="checkbox"
          :value="comp.code"
          v-model="selectedCodes"
        />
        <span>{{ comp.code }} - {{ comp.libelle }}</span>
      </label>
    </div>
  </div>
</template>

<script setup lang="ts">
const props = defineProps<{
  modelValue: string[]
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: string[]): void
}>()

const workContext = useWorkContextStore()

const availableCompetitions = computed(() =>
  workContext.competitions.filter(c =>
    workContext.competitionCodes.includes(c.code)
  )
)

const selectedCodes = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

const allSelected = computed(() =>
  selectedCodes.value.length === availableCompetitions.value.length
)

const someSelected = computed(() =>
  selectedCodes.value.length > 0
)

function toggleAll() {
  if (allSelected.value) {
    selectedCodes.value = []
  } else {
    selectedCodes.value = availableCompetitions.value.map(c => c.code)
  }
}
</script>
```

### 7.4 CompetitionSingleSelect.vue

Composant select simple pour les pages Documents, Équipes, Classements :

```vue
<template>
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-1">
      {{ t('context.competition_from_context') }}
    </label>

    <div v-if="availableCompetitions.length === 0" class="text-sm text-gray-500 italic">
      {{ t('context.no_competitions') }}
    </div>

    <select
      v-else
      :value="workContext.pageCompetitionCode"
      class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
      @change="onSelect(($event.target as HTMLSelectElement).value)"
    >
      <option
        v-for="comp in availableCompetitions"
        :key="comp.code"
        :value="comp.code"
      >
        {{ formatCompetitionLabel(comp) }}
      </option>
    </select>
  </div>
</template>

<script setup lang="ts">
const { t } = useI18n()
const workContext = useWorkContextStore()

const emit = defineEmits<{
  (e: 'change', code: string): void
}>()

// Available competitions from context
const availableCompetitions = computed(() =>
  workContext.competitions.filter(c =>
    workContext.competitionCodes.includes(c.code),
  ),
)

// Handle selection change
function onSelect(code: string) {
  workContext.setPageCompetition(code)
  emit('change', code)
}

// Auto-select: when competitions change, ensure we have a valid selection
watch(
  () => workContext.competitionCodes,
  (codes) => {
    if (!codes.length) {
      if (workContext.pageCompetitionCode) {
        workContext.setPageCompetition('')
        emit('change', '')
      }
      return
    }
    // If current selection is still valid, keep it
    if (workContext.pageCompetitionCode && codes.includes(workContext.pageCompetitionCode)) {
      return
    }
    // Auto-select first
    workContext.setPageCompetition(codes[0])
    emit('change', codes[0])
  },
  { immediate: true },
)

// Format competition label
function formatCompetitionLabel(comp: { code: string; libelle: string; soustitre?: string | null }): string {
  return comp.soustitre ? `${comp.code} - ${comp.libelle} (${comp.soustitre})` : `${comp.code} - ${comp.libelle}`
}
</script>
```

**Fonctionnalités** :
- Auto-sélectionne automatiquement la première compétition disponible
- Persiste la sélection via `workContext.setPageCompetition(code)`
- Émet un événement `@change` lors de la sélection
- Partage la sélection avec Documents et Classements via le store

---

## 8. Intégration dans les Pages

### 8.1 Page Compétitions

La page Compétitions utilise directement le périmètre du contexte sans filtre supplémentaire :

```typescript
// pages/competitions/index.vue
const workContext = useWorkContextStore()

onMounted(() => {
  workContext.initContext()
})

// Charger les compétitions du périmètre
const competitions = computed(() => {
  if (!workContext.hasValidContext) return []
  // Le périmètre est déjà défini dans workContext.competitionCodes
  return workContext.competitionCodes
})

// Template - affiche uniquement la saison en lecture seule
// Pas de CompetitionMultiSelect nécessaire
```

### 8.2 Pages avec Multi-Select (Journées, Matchs, Stats)

```typescript
// pages/gamedays/index.vue, pages/matches/index.vue, pages/stats/index.vue
const workContext = useWorkContextStore()

// Compétitions sélectionnées parmi le périmètre
const selectedCompetitions = ref<string[]>([])

onMounted(() => {
  workContext.initContext()
  if (workContext.hasValidContext) {
    // Par défaut, toutes les compétitions du périmètre sont sélectionnées
    selectedCompetitions.value = [...workContext.competitionCodes]
  }
})

// Template
<CompetitionMultiSelect v-model="selectedCompetitions" />

// Utilisation pour filtrer les données
const filteredData = computed(() =>
  allData.filter(item =>
    selectedCompetitions.value.includes(item.competitionCode)
  )
)
```

### 8.3 Pages avec Single-Select (Documents, Équipes, Classements)

```typescript
// pages/documents/index.vue, pages/teams/index.vue, pages/rankings/index.vue
const workContext = useWorkContextStore()

onMounted(() => {
  workContext.initContext()
  if (workContext.hasValidContext) {
    loadData()
  }
})

// Watch page competition changes
watch(
  () => workContext.pageCompetitionCode,
  (code) => {
    if (code) {
      loadData()
    }
    else {
      // Clear data when no competition selected
      clearData()
    }
  },
)

// Template
<AdminCompetitionSingleSelect @change="onCompetitionChange" />

// Handler (optionnel si le watch suffit)
function onCompetitionChange(code: string) {
  // Optionnel : actions supplémentaires lors du changement
  console.log('Competition changed:', code)
}
```

**Notes** :
- La sélection est automatiquement persistée par le composant `AdminCompetitionSingleSelect`
- Utiliser `workContext.pageCompetitionCode` pour lire la compétition sélectionnée
- Utiliser `workContext.pageCompetition` pour l'objet complet de la compétition
- Le composant auto-sélectionne la première compétition disponible
- La sélection est partagée entre Documents, Équipes et Classements

### 8.4 Comportement de la Saison

**La saison de travail ne peut être changée que depuis la page d'accueil**, sauf exception.

Sur les autres pages :
- La saison est affichée en lecture seule (badge ou texte)
- Pas de sélecteur de saison modifiable
- La saison vient toujours du contexte de travail

**Exception - Page Statistiques** : la saison peut être changée dans la modale de paramétrage pour consulter des stats d'une autre saison. Les compétitions restent filtrées par le périmètre du contexte.

```vue
<!-- Affichage de la saison sur les autres pages -->
<div class="flex items-center gap-2 text-sm text-gray-600 mb-4">
  <UIcon name="i-heroicons-calendar" class="w-4 h-4" />
  <span>{{ t('context.season') }}: <strong>{{ workContext.season }}</strong></span>
  <NuxtLink to="/" class="text-blue-600 hover:underline text-xs">
    {{ t('context.change') }}
  </NuxtLink>
</div>
```

---

## 9. Traductions i18n

### 9.1 Français (fr.json)

```json
{
  "context": {
    "title": "Contexte de travail",
    "season": "Saison",
    "scope": "Périmètre",
    "type_all": "Toutes les compétitions",
    "type_selection": "Sélection",
    "type_section": "Section complète",
    "type_group": "Groupe",
    "type_event": "Événement",
    "select_season": "Sélectionnez une saison",
    "select_section": "Sélectionnez une section",
    "select_group": "Sélectionnez un groupe",
    "select_competition": "Sélectionnez une compétition",
    "select_event": "Sélectionnez un événement",
    "competitions_from_context": "Compétitions (du contexte)",
    "competition_from_context": "Compétition (du contexte)",
    "all_competitions": "Toutes",
    "current": "Contexte",
    "competitions_count": "{count} compétition(s)",
    "no_context": "Aucun contexte sélectionné",
    "no_competitions": "Aucune compétition disponible",
    "active_season": "Saison active",
    "change": "Modifier",
    "sections": {
      "1": "Compétitions Internationales",
      "2": "Compétitions Nationales",
      "3": "Compétitions Régionales",
      "4": "Tournois Internationaux",
      "5": "Continents",
      "100": "Divers"
    }
  }
}
```

### 9.2 Anglais (en.json)

```json
{
  "context": {
    "title": "Working Context",
    "season": "Season",
    "scope": "Scope",
    "type_all": "All competitions",
    "type_selection": "Selection",
    "type_section": "Entire Section",
    "type_group": "Group",
    "type_event": "Event",
    "select_season": "Select a season",
    "select_section": "Select a section",
    "select_group": "Select a group",
    "select_competition": "Select a competition",
    "select_event": "Select an event",
    "competitions_from_context": "Competitions (from context)",
    "competition_from_context": "Competition (from context)",
    "all_competitions": "All",
    "current": "Context",
    "competitions_count": "{count} competition(s)",
    "no_context": "No context selected",
    "no_competitions": "No competitions available",
    "active_season": "Active season",
    "change": "Change",
    "sections": {
      "1": "International Competitions",
      "2": "National Competitions",
      "3": "Regional Competitions",
      "4": "International Tournaments",
      "5": "Continents",
      "100": "Other"
    }
  }
}
```

---

## 10. Comportements Spécifiques

### 10.1 Calcul des Compétitions Résultantes

```typescript
function computeCompetitionCodes(): string[] {
  switch (selectionType) {
    case 'all':
      // Toutes les compétitions de la saison auxquelles l'utilisateur a accès
      return competitions.map(c => c.code)

    case 'selection':
      // Multi-select : compétitions cochées
      return selectedCompetitionCodes.filter(code =>
        competitions.some(c => c.code === code)
      )

    case 'section':
      // Toutes les compétitions de la section
      return competitions
        .filter(c => {
          const group = groups.find(g => g.competitions.some(gc => gc.code === c.code))
          return group?.section === sectionId
        })
        .map(c => c.code)

    case 'group':
      // Toutes les compétitions du groupe (même codeRef)
      return competitions
        .filter(c => (c.codeRef || c.code) === groupCode)
        .map(c => c.code)

    case 'event':
      // Chargé depuis l'API /admin/filters/event-competitions
      // Déjà stocké dans competitionCodes lors de loadEventCompetitions()
      break

    default:
      return []
  }
}
```

### 10.2 Changement de Saison

Lors du changement de saison via `setSeason(seasonCode)` :

1. Vider la sélection courante (`clearSelection()`)
2. Recharger les groupes et compétitions pour la nouvelle saison (`loadSeasonData()`)
3. Recharger les événements filtrés par la nouvelle saison (`loadEvents()`)
4. Réinitialiser automatiquement le type de sélection à `'all'` (toutes les compétitions)
5. Calculer les codes de compétitions résultants
6. Sauvegarder en localStorage

```typescript
async setSeason(seasonCode: string, apiInstance?: ReturnType<typeof useApi>) {
  if (this.season === seasonCode) return

  this.season = seasonCode
  localStorage.setItem(STORAGE_KEYS.season, seasonCode)

  // Clear current selection
  this.clearSelection()

  // Reload season data and events
  this.loading = true
  try {
    await Promise.all([
      this.loadSeasonData(apiInstance),
      this.loadEvents(apiInstance),
    ])
    // Default to 'all' after season change
    this.selectionType = 'all'
    this.computeCompetitionCodes()
    this.saveToStorage()
  }
  finally {
    this.loading = false
  }
}
```

### 10.3 Permissions

- Les API filtrent automatiquement selon les droits utilisateur
- Un contexte sauvegardé non accessible est ignoré
- L'interface n'affiche que les éléments autorisés

---

## 11. Checklist d'Implémentation

### Phase 1 : Backend API

- [x] Créer endpoint `/admin/filters/event-competitions`
- [x] Ajouter les compétitions groupées par `codeRef` dans `/admin/filters/competitions`
- [x] Ajouter paramètre `codes` dans `GET /admin/competitions` pour filtrage par contexte

### Phase 2 : Store

- [x] Créer `stores/workContextStore.ts`
- [x] Implémenter les 5 modes de sélection (all, selection, section, group, event)
- [x] Gérer la persistance localStorage
- [x] Calculer les compétitions résultantes
- [x] Migration de l'ancien type 'competition' vers 'selection'
- [x] Ajouter pageCompetitionCode pour persistance cross-pages
- [x] Ajouter resetPageCompetition() appelé lors du changement de contexte

### Phase 3 : Composants

- [x] Créer `WorkContextSelector.vue` avec radio buttons et dropdowns (optgroups par section)
  - [x] Layout 2 colonnes (desktop) : Saison | Périmètre
  - [x] Layout 1 colonne (mobile) : Saison puis Périmètre
  - [x] Type "Toutes les compétitions" (par défaut)
  - [x] Type "Sélection" avec checkbox list multi-select
  - [x] Type "Section complète" avec dropdown des sections
  - [x] Type "Groupe" avec dropdown des groupes
  - [x] Type "Événement" avec dropdown des événements
  - [x] Résumé du contexte avec liste des compétitions résultantes
- [x] Créer `CompetitionSingleSelect.vue` pour pages mono-compétition (dropdown simple)
  - [x] Auto-sélection de la première compétition
  - [x] Persistance via workContext.pageCompetitionCode
  - [x] Émission d'événement @change
  - [x] Watcher pour maintenir une sélection valide
- [x] Créer `WorkContextSummary.vue` pour afficher le contexte en haut des autres pages
- [ ] Créer `CompetitionMultiSelect.vue` pour pages multi-compétition (checkbox list avec "Toutes")
- [x] Ajouter traductions i18n (type_all, type_selection, etc.)

### Phase 4 : Page d'Accueil

- [x] Modifier `pages/index.vue`
- [x] Intégrer WorkContextSelector
- [x] Afficher le récapitulatif du contexte
- [x] Ajouter les cartes de navigation manquantes (Équipes, Journées, Classements)

### Phase 5 : Intégration Pages

- [x] `pages/competitions/index.vue` - contexte de travail (barre de contexte, filtrage par codes)
- [ ] `pages/gamedays/index.vue` - legacy redirect (à migrer)
- [x] `pages/games/index.vue` - legacy redirect (renommé depuis /matches)
- [x] `pages/stats/index.vue` - contexte de travail (barre de contexte, compétitions filtrées dans modale, saison modifiable)
- [x] `pages/documents/index.vue` - contexte de travail (barre de contexte, mono-select persisté)
- [x] `pages/teams/index.vue` - contexte de travail (barre de contexte, mono-select persisté)
- [x] `pages/rankings/index.vue` - legacy redirect (à migrer avec mono-select persisté)

### Phase 6 : Tests

- [ ] Tester chaque type de sélection (all, selection, section, group, event)
- [ ] Tester persistance localStorage
- [ ] Tester changement de saison (doit réinitialiser à 'all')
- [ ] Tester navigation entre pages
- [ ] Tester persistance de pageCompetitionCode entre Documents/Équipes/Classements
- [ ] Tester auto-sélection de la première compétition
- [ ] Tester réinitialisation de pageCompetitionCode lors du changement de contexte
- [ ] Tester avec différents profils utilisateur
- [ ] Tester migration de l'ancien type 'competition'

---

**Document créé le** : 2026-01-15
**Dernière mise à jour** : 2026-02-08
**Statut** : ✅ Implémenté — Layout 2 colonnes, 5 types de sélection (all, selection, section, group, event), sélection persistée cross-pages
